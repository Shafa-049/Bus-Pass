<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Check if user is logged in and is admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'admin') {
    header("location: ../auth/login.php");
    exit;
}

$page_title = 'Manage Bus Passes - SEUSL Bus Pass Management System';
include '../includes/admin_header.php';

// Handle pass status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['pass_id'])) {
    $pass_id = trim($_POST['pass_id']);
    $status = ($_POST['action'] == 'approve') ? 'active' : 'rejected';
    $admin_notes = trim($_POST['admin_notes'] ?? '');
    
    try {
        $sql = "UPDATE bus_passes SET status = :status, admin_notes = :admin_notes, processed_at = NOW() WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':admin_notes', $admin_notes, PDO::PARAM_STR);
        $stmt->bindParam(':id', $pass_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Pass " . ucfirst($status) . " successfully!";
        header("location: passes.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error updating pass status: " . $e->getMessage();
    }
}

// Fetch all bus passes with related user and route information
$passes = [];
try {
    $sql = "SELECT 
                bp.*, 
                u.username, 
                CONCAT(s.first_name, ' ', s.last_name) as student_name,
                s.registration_no,
                CONCAT(r.start_point, ' - ', r.end_point) as route_name,
                d.depot_name
            FROM bus_passes bp
            JOIN users u ON bp.user_id = u.id
            JOIN students s ON u.id = s.user_id
            LEFT JOIN routes r ON bp.route_id = r.id
            LEFT JOIN depots d ON s.depot_id = d.id
            ORDER BY 
                CASE bp.status 
                    WHEN 'pending' THEN 1 
                    WHEN 'active' THEN 2 
                    WHEN 'rejected' THEN 3 
                    WHEN 'expired' THEN 4 
                    ELSE 5 
                END,
                bp.applied_date DESC";
    
    $stmt = $pdo->query($sql);
    $passes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Error fetching bus passes: " . $e->getMessage();
}
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Bus Passes</h1>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
                echo $_SESSION['success_message']; 
                unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
                echo $_SESSION['error_message']; 
                unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Bus Passes</h6>
            <div class="btn-group" role="group">
                <a href="?status=all" class="btn btn-outline-primary btn-sm <?php echo (!isset($_GET['status']) || $_GET['status'] == 'all') ? 'active' : ''; ?>">
                    All
                </a>
                <a href="?status=pending" class="btn btn-outline-warning btn-sm <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'active' : ''; ?>">
                    Pending
                </a>
                <a href="?status=active" class="btn btn-outline-success btn-sm <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'active' : ''; ?>">
                    Active
                </a>
                <a href="?status=rejected" class="btn btn-outline-danger btn-sm <?php echo (isset($_GET['status']) && $_GET['status'] == 'rejected') ? 'active' : ''; ?>">
                    Rejected
                </a>
                <a href="?status=expired" class="btn btn-outline-secondary btn-sm <?php echo (isset($_GET['status']) && $_GET['status'] == 'expired') ? 'active' : ''; ?>">
                    Expired
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($passes)): ?>
                <div class="alert alert-info mb-0">No bus passes found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="passesTable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>Pass ID</th>
                                <th>Student</th>
                                <th>Registration No</th>
                                <th>Route</th>
                                <th>Depot</th>
                                <th>Applied Date</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($passes as $pass): 
                                // Skip if status filter doesn't match
                                if (isset($_GET['status']) && $_GET['status'] != 'all' && $pass['status'] != $_GET['status']) {
                                    continue;
                                }
                                
                                // Determine status badge class
                                $status_class = '';
                                switch ($pass['status']) {
                                    case 'pending':
                                        $status_class = 'bg-warning text-dark';
                                        break;
                                    case 'active':
                                        $status_class = 'bg-success text-white';
                                        break;
                                    case 'rejected':
                                        $status_class = 'bg-danger text-white';
                                        break;
                                    case 'expired':
                                        $status_class = 'bg-secondary text-white';
                                        break;
                                    default:
                                        $status_class = 'bg-secondary text-white';
                                }
                                
                                // Check if pass is expired
                                $is_expired = (strtotime($pass['expiry_date']) < time());
                                if ($pass['status'] == 'active' && $is_expired) {
                                    $status_class = 'bg-secondary text-white';
                                    $pass['status'] = 'expired';
                                }
                            ?>
                                <tr>
                                    <td>#<?php echo str_pad($pass['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($pass['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($pass['registration_no']); ?></td>
                                    <td><?php echo htmlspecialchars($pass['route_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($pass['depot_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($pass['applied_date'])); ?></td>
                                    <td class="<?php echo $is_expired ? 'text-danger fw-bold' : ''; ?>">
                                        <?php echo date('M d, Y', strtotime($pass['expiry_date'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $status_class; ?> p-2">
                                            <?php echo ucfirst($pass['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-info btn-view-pass" 
                                                    data-bs-toggle="modal" data-bs-target="#viewPassModal"
                                                    data-pass='<?php echo json_encode($pass); ?>'>
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($pass['status'] == 'pending'): ?>
                                                <button type="button" class="btn btn-success btn-approve-pass" 
                                                        data-bs-toggle="modal" data-bs-target="#approvePassModal"
                                                        data-id="<?php echo $pass['id']; ?>"
                                                        data-student="<?php echo htmlspecialchars($pass['student_name']); ?>">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-reject-pass" 
                                                        data-bs-toggle="modal" data-bs-target="#rejectPassModal"
                                                        data-id="<?php echo $pass['id']; ?>"
                                                        data-student="<?php echo htmlspecialchars($pass['student_name']); ?>">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- View Pass Modal -->
<div class="modal fade" id="viewPassModal" tabindex="-1" aria-labelledby="viewPassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewPassModalLabel">Bus Pass Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="passDetails">
                <!-- Pass details will be loaded here via JavaScript -->
                <div class="text-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading pass details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printPassBtn">
                    <i class="fas fa-print me-1"></i> Print Pass
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Pass Modal -->
<div class="modal fade" id="approvePassModal" tabindex="-1" aria-labelledby="approvePassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="passes.php" method="post">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="pass_id" id="approvePassId">
                
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="approvePassModalLabel">Approve Bus Pass</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve the bus pass for <strong id="approveStudentName"></strong>?</p>
                    <div class="mb-3">
                        <label for="approveNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="approveNotes" name="admin_notes" rows="3" placeholder="Add any notes here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Pass Modal -->
<div class="modal fade" id="rejectPassModal" tabindex="-1" aria-labelledby="rejectPassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="passes.php" method="post">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="pass_id" id="rejectPassId">
                
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectPassModalLabel">Reject Bus Pass</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reject the bus pass for <strong id="rejectStudentName"></strong>?</p>
                    <div class="mb-3">
                        <label for="rejectNotes" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectNotes" name="admin_notes" rows="3" required placeholder="Please provide a reason for rejection..."></textarea>
                        <div class="invalid-feedback">Please provide a reason for rejection.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>

<!-- DataTables JavaScript -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#passesTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 25,
        "responsive": true,
        "columnDefs": [
            { "orderable": false, "targets": [8] } // Disable sorting on actions column
        ]
    });
    
    // Handle view pass button click
    $('.btn-view-pass').on('click', function() {
        const passData = $(this).data('pass');
        const passDetails = $('#passDetails');
        
        // Format the pass details HTML
        const html = `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Pass ID</h6>
                        <p class="mb-0">#${String(passData.id).padStart(6, '0')}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Student Name</h6>
                        <p class="mb-0">${passData.student_name || 'N/A'}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Registration No</h6>
                        <p class="mb-0">${passData.registration_no || 'N/A'}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Route</h6>
                        <p class="mb-0">${passData.route_name || 'N/A'}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Depot</h6>
                        <p class="mb-0">${passData.depot_name || 'N/A'}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Applied Date</h6>
                        <p class="mb-0">${new Date(passData.applied_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Expiry Date</h6>
                        <p class="mb-0 ${new Date(passData.expiry_date) < new Date() ? 'text-danger fw-bold' : ''}">
                            ${new Date(passData.expiry_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
                            ${new Date(passData.expiry_date) < new Date() ? '(Expired)' : ''}
                        </p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Status</h6>
                        <span class="badge ${getStatusBadgeClass(passData.status)}">
                            ${passData.status.charAt(0).toUpperCase() + passData.status.slice(1)}
                        </span>
                    </div>
                </div>
            </div>
            ${passData.admin_notes ? `
                <div class="alert alert-info mt-3">
                    <h6 class="alert-heading">Admin Notes</h6>
                    <p class="mb-0">${passData.admin_notes}</p>
                </div>
            ` : ''}
        `;
        
        // Update the modal content
        passDetails.html(html);
    });
    
    // Handle approve pass button click
    $('.btn-approve-pass').on('click', function() {
        const passId = $(this).data('id');
        const studentName = $(this).data('student');
        
        $('#approvePassId').val(passId);
        $('#approveStudentName').text(studentName);
    });
    
    // Handle reject pass button click
    $('.btn-reject-pass').on('click', function() {
        const passId = $(this).data('id');
        const studentName = $(this).data('student');
        
        $('#rejectPassId').val(passId);
        $('#rejectStudentName').text(studentName);
    });
    
    // Handle print pass button click
    $('#printPassBtn').on('click', function() {
        // This would be implemented based on your print template
        alert('Print functionality would be implemented here');
    });
    
    // Helper function to get status badge class
    function getStatusBadgeClass(status) {
        switch (status) {
            case 'pending': return 'bg-warning text-dark';
            case 'active': return 'bg-success text-white';
            case 'rejected': return 'bg-danger text-white';
            case 'expired': return 'bg-secondary text-white';
            default: return 'bg-secondary text-white';
        }
    }
    
    // Initialize form validation for reject form
    const rejectForm = document.querySelector('form[action="passes.php"]');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function(event) {
            if (!rejectForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            rejectForm.classList.add('was-validated');
        }, false);
    }
});
</script>
