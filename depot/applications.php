<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Check if user is logged in and is a depot user
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'depot') {
    header("location: ../auth/login.php");
    exit;
}

$page_title = 'Pass Applications - Depot Dashboard';
include '../includes/depot_header.php';

// Debug: Check session data
error_log("Session data: " . print_r($_SESSION, true));

// Get all depot IDs for the logged-in user
$depot_ids = [];
$depot_names = [];
$user_id = $_SESSION['id'] ?? 0;

if (!empty($user_id)) {
    $sql = "SELECT id, depot_name FROM depots WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $depots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $depot_ids = array_column($depots, 'id');
    $depot_names = array_column($depots, 'depot_name');
    
    // If only one depot, set it in session for backward compatibility
    if (count($depot_ids) === 1) {
        $_SESSION['depot_id'] = $depot_ids[0];
        $depot_id = $depot_ids[0];
        $depot_name = $depot_names[0];
    } else if (count($depot_ids) > 1) {
        // If multiple depots, show a dropdown to select one
        if (empty($_SESSION['selected_depot_id']) || !in_array($_SESSION['selected_depot_id'], $depot_ids)) {
            // Default to first depot if none selected or invalid selection
            $_SESSION['selected_depot_id'] = $depot_ids[0];
            $depot_id = $depot_ids[0];
            $depot_name = $depot_names[0];
        } else {
            $depot_id = $_SESSION['selected_depot_id'];
            $key = array_search($depot_id, $depot_ids);
            $depot_name = $depot_names[$key] ?? 'Unknown Depot';
        }
    }
    
    error_log("User " . $_SESSION['username'] . " manages depots: " . implode(', ', $depot_names) . " (IDs: " . implode(', ', $depot_ids) . ")");
}

// If no depots found, show an error
if (empty($depot_ids)) {
    die("<div class='alert alert-danger'>Error: No depots are assigned to your account. Please contact the administrator.</div>");
}

// Prepare placeholders for the IN clause
$placeholders = str_repeat('?,', count($depot_ids) - 1) . '?';

// Debug: Show the depots being queried
error_log("Querying applications for depots: " . print_r($depot_ids, true));

// First, let's check if there are any applications in the system at all
$check_sql = "SELECT COUNT(*) as total_applications FROM bus_passes";
$check_stmt = $pdo->query($check_sql);
$total_apps = $check_stmt->fetch()['total_applications'];
error_log("Total applications in system: " . $total_apps);

// Check applications for each depot
foreach ($depot_ids as $depot_id) {
    $depot_sql = "SELECT COUNT(*) as count FROM bus_passes WHERE depot_id = ?";
    $depot_stmt = $pdo->prepare($depot_sql);
    $depot_stmt->execute([$depot_id]);
    $count = $depot_stmt->fetch()['count'];
    error_log("Applications for depot $depot_id: $count");
}

// Handle depot selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_depot'])) {
    $selected_depot = (int)$_POST['select_depot'];
    if (in_array($selected_depot, $depot_ids)) {
        $_SESSION['selected_depot_id'] = $selected_depot;
        $depot_id = $selected_depot;
        $key = array_search($depot_id, $depot_ids);
        $depot_name = $depot_names[$key] ?? 'Unknown Depot';
    }
}

// Fetch applications for the selected depot
$sql = "SELECT bp.*, s.full_name, s.registration_no, r.start_point, r.end_point, 
               d.depot_name, d.id as depot_id, s.user_id as student_user_id,
               COALESCE(bp.is_active, 1) as is_active,
               bp.pass_number
        FROM bus_passes bp
        JOIN students s ON bp.student_id = s.id
        JOIN routes r ON bp.route_id = r.id
        JOIN depots d ON bp.depot_id = d.id
        WHERE bp.depot_id = ?
        ORDER BY 
            CASE 
                WHEN bp.status = 'pending' THEN 1
                WHEN bp.status = 'approved' OR bp.status = 'active' THEN 2
                WHEN bp.status = 'rejected' THEN 3
                ELSE 4
            END,
            bp.created_at DESC";

error_log("Executing query: " . $sql);
error_log("For depot ID: " . $depot_id);

$stmt = $pdo->prepare($sql);
$stmt->execute([$depot_id]);
$applications = $stmt->fetchAll();

error_log("Applications found: " . print_r($applications, true));
?>

<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Pass Applications</h1>
            <?php if (!empty($depot_id)): ?>
                <p class="text-muted mb-0">
                    <?php if (count($depot_ids) > 1): ?>
                        <form method="post" action="" class="d-inline">
                            <div class="input-group input-group-sm" style="width: 300px;">
                                <span class="input-group-text">Depot:</span>
                                <select name="select_depot" class="form-select" onchange="this.form.submit()">
                                    <?php foreach ($depot_ids as $key => $id): ?>
                                        <option value="<?php echo $id; ?>" <?php echo ($id == $depot_id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($depot_names[$key] ?? 'Depot ' . $id); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    <?php else: ?>
                        Depot: <?php echo htmlspecialchars($depot_name ?? 'N/A'); ?>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
        <?php if (empty($applications)): ?>
            <div class="alert alert-warning mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No applications found for your depot.
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-ticket-alt text-primary me-2"></i>Pass Applications</h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (count($applications) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Registration No</th>
                                <th>Route</th>
                                <th>Depot</th>
                                <th>Status</th>
                                <th>Applied On</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($app['id']); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2 bg-soft-primary rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($app['full_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($app['depot_name']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($app['registration_no']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($app['start_point'] . ' to ' . $app['end_point']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($app['depot_name']); ?></td>
                                    <td>
                                        <?php
                                        $app_status = !empty($app['status']) ? $app['status'] : 'pending';
                                        $status_config = [
                                            'pending' => ['class' => 'warning', 'text' => 'Pending'],
                                            'active' => ['class' => 'success', 'text' => 'Active'],
                                            'approved' => ['class' => 'success', 'text' => 'Active'],
                                            'rejected' => ['class' => 'danger', 'text' => 'Rejected'],
                                            'expired' => ['class' => 'secondary', 'text' => 'Expired'],
                                            'cancelled' => ['class' => 'secondary', 'text' => 'Cancelled']
                                        ][$app_status] ?? ['class' => 'secondary', 'text' => $app_status];
                                        ?>
                                        <span class="badge bg-<?php echo $status_config['class']; ?> text-white">
                                            <?php echo $status_config['text']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Application Actions">
                                            <!-- View Button -->
                                            <a href="view_application.php?id=<?php echo $app['id']; ?>" 
                                               class="btn btn-primary text-white btn-action" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <!-- Edit Button -->
                                            <a href="edit_application.php?id=<?php echo $app['id']; ?>" 
                                               class="btn btn-warning text-white btn-action" 
                                               title="Edit Application"
                                               <?php echo ($app['status'] !== 'pending') ? 'disabled' : ''; ?>>
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <!-- Delete Button -->
                                            <a href="#"
                                               class="btn btn-danger text-white btn-action"
                                               onclick="return confirmDelete(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars(addslashes($app['depot_name'])); ?>')"
                                               title="Delete Application"
                                               <?php echo ($app['status'] !== 'pending') ? 'disabled' : ''; ?>>
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            
                                            <?php 
                                            $app_status = !empty($app['status']) ? $app['status'] : 'pending';
                                            
                                            if ($app_status === 'pending' || $app_status === 'active' || $app_status === 'approved'): 
                                            ?>
                                            <!-- Approve Button -->
                                            <a href="approve_application.php?id=<?php echo $app['id']; ?>" 
                                               class="btn btn-success text-white btn-action"
                                               title="Approve Application"
                                               <?php echo ($app_status !== 'pending') ? 'disabled' : ''; ?>>
                                                <i class="fas fa-check"></i>
                                            </a>
                                            
                                            <!-- Reject Button -->
                                            <a href="#"
                                               class="btn btn-danger text-white btn-action"
                                               onclick="showRejectReason(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars(addslashes($app['depot_name'])); ?>'); return false;"
                                               title="Reject Application">
                                                <i class="fas fa-times"></i>
                                            </a>
                                            <?php elseif (in_array($app_status, ['approved', 'active'])): 
                                                $is_active = isset($app['is_active']) ? (bool)$app['is_active'] : true;
                                                $btn_class = $is_active ? 'btn-success' : 'btn-secondary';
                                                $btn_icon = $is_active ? 'fa-toggle-on' : 'fa-toggle-off';
                                                $btn_title = $is_active ? 'Deactivate' : 'Activate';
                                            ?>
                                            <!-- Active/Inactive Toggle Button -->
                                            <button type="button"
                                               class="btn <?php echo $btn_class; ?> text-white btn-action toggle-active"
                                               data-id="<?php echo $app['id']; ?>"
                                               data-status="<?php echo $is_active ? '1' : '0'; ?>"
                                               title="<?php echo $btn_title; ?> Application">
                                                <i class="fas <?php echo $btn_icon; ?>"></i>
                                            </button>
                                            <button type="button"
                                               class="btn <?php echo $btn_class; ?> text-white btn-action toggle-active"
                                               data-id="<?php echo $app['id']; ?>"
                                               data-status="<?php echo $is_active ? '1' : '0'; ?>"
                                               title="<?php echo $btn_title; ?> Application">
                                                <i class="fas <?php echo $btn_icon; ?>"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal<?php echo $app['id']; ?>" 
                                             tabindex="-1" 
                                             role="dialog" 
                                             aria-labelledby="rejectModalLabel<?php echo $app['id']; ?>"
                                             aria-hidden="true"
                                             data-bs-backdrop="static">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title" id="rejectModalLabel<?php echo $app['id']; ?>">
                                                            Reject Application #<?php echo $app['id']; ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form id="rejectForm<?php echo $app['id']; ?>" onsubmit="return submitRejection(<?php echo $app['id']; ?>);">
                                                        <div class="modal-body">
                                                            <p>You are about to reject an application for <strong><?php echo htmlspecialchars($app['depot_name']); ?></strong>. Please provide a reason:</p>
                                                            <div class="mb-3">
                                                                <label for="reason<?php echo $app['id']; ?>" class="form-label">Reason for Rejection</label>
                                                                <textarea class="form-control" 
                                                                          id="reason<?php echo $app['id']; ?>" 
                                                                          name="reason" 
                                                                          rows="3" 
                                                                          required
                                                                          aria-required="true"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" 
                                                                    class="btn btn-secondary" 
                                                                    data-bs-dismiss="modal"
                                                                    aria-label="Cancel and close dialog">
                                                                Cancel
                                                            </button>
                                                            <button type="submit" 
                                                                    class="btn btn-danger"
                                                                    aria-label="Submit rejection for application #<?php echo $app['id']; ?>">
                                                                Submit Rejection
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing <span class="fw-semibold">1</span> to <span class="fw-semibold"><?php echo count($applications); ?></span> of 
                        <span class="fw-semibold"><?php echo count($applications); ?></span> entries
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-inbox fa-4x text-muted"></i>
                    </div>
                    <h5 class="text-muted mb-3">No Applications Found</h5>
                    <p class="text-muted mb-4">There are no pass applications available for your depot at the moment.</p>
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Function to confirm and handle application deletion
function confirmDelete(id, depotName) {
    if (confirm(`Are you sure you want to delete the application for ${depotName}? This action cannot be undone.`)) {
        // Show loading state
        const deleteBtn = event.target.closest('.btn-danger');
        const originalContent = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
        
        // Send delete request
        fetch(`delete_application.php?id=${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `_method=DELETE&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showAlert('success', data.message || 'Application deleted successfully');
                // Remove the row from the table
                const row = deleteBtn.closest('tr');
                row.style.transition = 'opacity 0.3s';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
            } else {
                throw new Error(data.message || 'Failed to delete application');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', error.message || 'An error occurred while deleting the application');
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalContent;
        });
    }
    return false;
}

// View application function (simplified to prevent conflicts)
window.viewApplication = function(id, event) {
    if (event) event.preventDefault();
    window.location.href = 'view_application.php?id=' + id;
    return false;
};

// Handle modal focus management
document.addEventListener('DOMContentLoaded', function() {
    // Find all modals
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        // Store the element that had focus before opening the modal
        let focusedElementBeforeModal;
        
        // Handle show event
        modal.addEventListener('show.bs.modal', function() {
            // Store the current focus
            focusedElementBeforeModal = document.activeElement;
            
            // Update ARIA attributes
            this.removeAttribute('aria-hidden');
            this.setAttribute('aria-modal', 'true');
            this.removeAttribute('inert');
            
            // Focus the first focusable element in the modal
            const focusableElements = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
            const firstFocusableElement = modal.querySelectorAll(focusableElements)[0];
            
            // Focus the first element after a small delay to ensure the modal is visible
            setTimeout(() => {
                if (firstFocusableElement) firstFocusableElement.focus();
            }, 100);
        });
        
        // Handle hide event
        modal.addEventListener('hidden.bs.modal', function() {
            // Restore focus to the element that had it before the modal opened
            if (focusedElementBeforeModal) {
                focusedElementBeforeModal.focus();
            }
            
            // Update ARIA attributes
            this.setAttribute('aria-hidden', 'true');
            this.removeAttribute('aria-modal');
            this.setAttribute('inert', '');
        });
        
        // Trap focus inside the modal when open
        modal.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') return; // Let Bootstrap handle escape
            
            if (e.key === 'Tab') {
                const focusableElements = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
                const focusableContent = modal.querySelectorAll(focusableElements);
                
                if (focusableContent.length === 0) return;
                
                const firstFocusableElement = focusableContent[0];
                const lastFocusableElement = focusableContent[focusableContent.length - 1];
                
                if (e.shiftKey) {
                    if (document.activeElement === firstFocusableElement) {
                        lastFocusableElement.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === lastFocusableElement) {
                        firstFocusableElement.focus();
                        e.preventDefault();
                    }
                }
            }
        });
    });
});

// Define the approveApplication function in the global scope
window.approveApplication = function(id, depotName, event) {
    event.preventDefault();
    event.stopPropagation();
    
    if (confirm('Are you sure you want to approve this application for ' + depotName + '? You will be redirected to assign a pass number.')) {
        // First, update the status to 'pending_approval' to indicate it's ready for pass number assignment
        const approveBtn = event.target.closest('.btn-success');
        const originalText = approveBtn.innerHTML;
        approveBtn.disabled = true;
        approveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
        
        // Get the depot_id from the button's data attribute
        const depotId = approveBtn.dataset.depotId || <?php echo $depot_id ?? 0; ?>;
        
        console.log('Approving application:', { id, depotId });
        
        // Create form data
        const formData = new FormData();
        formData.append('application_id', id);
        formData.append('status', 'pending_approval');
        formData.append('depot_id', depotId);
        
        fetch('update_application_status.php', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
            },
            body: new URLSearchParams(formData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Reload the page to show the updated status
                showAlert('success', 'Application approved successfully!');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('danger', data.message || 'Failed to process approval. Please try again.');
                approveBtn.disabled = false;
                approveBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred. Please try again.');
            approveBtn.disabled = false;
            approveBtn.innerHTML = originalText;
        });
    }
    return false;
};

// Show alert function
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to the top of the content
    const content = document.querySelector('.content-wrapper');
    if (content) {
        content.insertBefore(alertDiv, content.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}
</script>

<style>
/* Action buttons styling */
.btn-action {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    margin: 0 2px;
    border-radius: 4px;
}

.btn-action i {
    margin: 0 !important;
}

.btn-group-sm > .btn-action {
    width: 32px;
    height: 32px;
    font-size: 0.875rem;
}

/* Ensure consistent button colors */
.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000 !important;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

/* Hover states */
.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.btn-warning:hover {
    background-color: #ffca2c;
    border-color: #ffc720;
    color: #000 !important;
}

.btn-danger:hover {
    background-color: #bb2d3b;
    border-color: #b02a37;
}

/* Disabled state */
.btn:disabled, .btn.disabled {
    opacity: 0.65;
    pointer-events: none;
}

.bullet {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: currentColor;
}
.avatar-sm {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
}
.bg-soft-primary { background-color: rgba(13,110,253,.1) !important; }
.bg-soft-success { background-color: rgba(25,135,84,.1) !important; }
.bg-soft-warning { background-color: rgba(255,193,7,.1) !important; }
.bg-soft-danger { background-color: rgba(220,53,69,.1) !important; }
.bg-soft-secondary { background-color: rgba(108,117,125,.1) !important; }
</style>

<script>
// Make sure the function is defined in global scope
window.approveApplication = function(id, depotName, event) {
    // Create modal for pass number input
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'approveModal' + id;
    modal.tabIndex = '-1';
    modal.setAttribute('aria-hidden', 'true');
    
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Approve Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="approveForm" method="POST" action="update_application_status.php">
                    <div class="modal-body">
                        <p>You are about to approve the application for <strong>${depotName}</strong>.</p>
                        <div class="mb-3">
                            <label for="passNumber" class="form-label">Pass Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="passNumber" name="pass_number" required 
                                   placeholder="Enter unique pass number">
                            <div class="form-text">Please enter a unique pass number for this student.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="application_id" value="${id}">
                        <input type="hidden" name="status" value="approved">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i> Approve Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    // Add modal to the document
    document.body.appendChild(modal);
    
    // Initialize the modal
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
    
    // Focus on pass number input when modal is shown
    modal.addEventListener('shown.bs.modal', function () {
        modal.querySelector('#passNumber').focus();
    });
    
    // Handle form submission
    const form = modal.querySelector('form');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
        
        // Submit the form
        fetch(form.action, {
            method: 'POST',
            body: new URLSearchParams(new FormData(form))
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success message and reload the page
                showAlert('success', 'Application approved successfully!');
                modalInstance.hide();
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                throw new Error(data.message || 'Failed to process approval');
            }
        })
        .catch(error => {
            showAlert('danger', error.message || 'An error occurred. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    });
    
    // Remove modal from DOM when hidden
    modal.addEventListener('hidden.bs.modal', function () {
        document.body.removeChild(modal);
    });
    
    return false;
};

// View application function
function viewApplication(id) {
    try {
        console.log('Viewing application ID:', id);
        if (!id || isNaN(id)) {
            console.error('Invalid application ID:', id);
            alert('Error: Invalid application ID');
            return false;
        }
        
        // First check if the view_application.php file exists
        fetch('view_application.php?id=' + id, { method: 'HEAD' })
            .then(response => {
                if (response.ok) {
                    // File exists, proceed with navigation
                    window.location.href = 'view_application.php?id=' + id;
                } else {
                    console.error('Error: view_application.php returned status', response.status);
                    alert('Error: Could not load application details. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error checking view_application.php:', error);
                alert('Error: Could not connect to the server. Please check your connection.');
            });
            
        return false;
    } catch (error) {
        console.error('Error in viewApplication:', error);
        alert('An error occurred while trying to view the application.');
        return false;
    }
}
                
                // Show success message
                showAlert('success', 'Application approved successfully!');
            } else {
                showAlert('danger', data.message || 'Failed to approve application. Please try again.');
                approveBtn.disabled = false;
                approveBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred. Please try again.');
            if (approveBtn) {
                approveBtn.disabled = false;
                approveBtn.innerHTML = originalText;
            }
        });
            console.error('Error:', error);
            showAlert('danger', 'An error occurred. Please try again.');
            approveBtn.disabled = false;
            approveBtn.innerHTML = originalText;
        });
    }
    return false;
}

window.showRejectReason = function(id, depotName) {
    // Show the reject modal
    const modal = new bootstrap.Modal(document.getElementById('rejectModal' + id));
    modal.show();
    return false;
}

window.submitRejection = function(id) {
    const reason = document.getElementById('reason' + id).value.trim();
    if (!reason) {
        alert('Please provide a reason for rejection.');
        return false;
    }

    const form = document.getElementById('rejectForm' + id);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Disable button and show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Submitting...';

    // Send AJAX request to reject the application
    fetch('update_application_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'application_id=' + id + '&status=rejected&reason=' + encodeURIComponent(reason)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message and close modal
            showAlert('success', 'Application rejected successfully!');
            const modal = bootstrap.Modal.getInstance(document.getElementById('rejectModal' + id));
            modal.hide();
            // Reload the page to reflect changes
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('danger', data.message || 'Failed to reject application. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });

    return false;
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add the alert to the top of the content
    const contentWrapper = document.querySelector('.content-wrapper');
    contentWrapper.insertBefore(alertDiv, contentWrapper.firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 5000);
}

// Handle Active/Inactive toggle
function handleToggleActive(button) {
    const id = button.dataset.id;
    const currentStatus = parseInt(button.dataset.status);
    const newStatus = currentStatus ? 0 : 1;
    
    // Update button appearance immediately for better UX
    button.disabled = true;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    
    // Send AJAX request to update status
    fetch('toggle_application_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `application_id=${id}&is_active=${newStatus}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button appearance
            button.dataset.status = newStatus;
            button.classList.remove(currentStatus ? 'btn-success' : 'btn-secondary');
            button.classList.add(newStatus ? 'btn-success' : 'btn-secondary');
            button.title = newStatus ? 'Deactivate Application' : 'Activate Application';
            button.innerHTML = `<i class="fas fa-toggle-${newStatus ? 'on' : 'off'}"></i>`;
            
            // Show success message
            showAlert('success', `Application ${newStatus ? 'activated' : 'deactivated'} successfully!`);
        } else {
            throw new Error(data.message || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', error.message || 'An error occurred. Please try again.');
        button.innerHTML = originalHTML;
    })
    .finally(() => {
        button.disabled = false;
    });
}

// Add event listener for toggle buttons
document.addEventListener('DOMContentLoaded', function() {
    // Delegate click events for toggle buttons in actions column
    document.addEventListener('click', function(e) {
        if (e.target.closest('.toggle-active')) {
            e.preventDefault();
            handleToggleActive(e.target.closest('.toggle-active'));
        }
    });
    
    // Delegate click events for toggle buttons in status column
    document.addEventListener('click', function(e) {
        if (e.target.closest('.toggle-status')) {
            e.preventDefault();
            handleToggleStatus(e.target.closest('.toggle-status'));
        }
    });
});

// Handle toggle in status column
function handleToggleStatus(button) {
    const id = button.dataset.id;
    const currentStatus = parseInt(button.dataset.status);
    const newStatus = currentStatus ? 0 : 1;
    
    // Update button appearance immediately for better UX
    button.disabled = true;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    
    // Send AJAX request to update status
    fetch('toggle_application_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `application_id=${id}&is_active=${newStatus}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button appearance
            button.dataset.status = newStatus;
            button.classList.remove(currentStatus ? 'btn-success' : 'btn-secondary');
            button.classList.add(newStatus ? 'btn-success' : 'btn-secondary');
            button.title = newStatus ? 'Deactivate Application' : 'Activate Application';
            button.innerHTML = `<i class="fas fa-toggle-${newStatus ? 'on' : 'off'}" style="font-size: 0.9rem;"></i>`;
            
            // Also update any other toggle buttons for the same application
            document.querySelectorAll(`.toggle-active[data-id="${id}"]`).forEach(btn => {
                btn.dataset.status = newStatus;
                btn.classList.remove(currentStatus ? 'btn-success' : 'btn-secondary');
                btn.classList.add(newStatus ? 'btn-success' : 'btn-secondary');
                btn.title = newStatus ? 'Deactivate Application' : 'Activate Application';
                btn.innerHTML = `<i class="fas fa-toggle-${newStatus ? 'on' : 'off'}"></i>`;
            });
            
            showAlert('success', `Application ${newStatus ? 'activated' : 'deactivated'} successfully!`);
        } else {
            throw new Error(data.message || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', error.message || 'An error occurred. Please try again.');
        button.innerHTML = originalHTML;
    })
    .finally(() => {
        button.disabled = false;
    });
}
</script>

<?php include '../includes/depot_footer.php'; ?>
