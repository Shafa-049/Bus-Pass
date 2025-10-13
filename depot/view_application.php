<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Check if user is logged in and is a depot user
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'depot') {
    header("location: ../auth/login.php");
    exit;
}

// Check if depot_id is set in session
if (!isset($_SESSION['depot_id'])) {
    $_SESSION['error'] = 'Depot not assigned. Please log in again.';
    header("location: applications.php");
    exit;
}

// Check if application ID is provided and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid application ID';
    header("location: applications.php");
    exit;
}

// Set application and depot IDs
$application_id = (int)$_GET['id'];
$depot_id = (int)$_SESSION['depot_id'];

error_log("Viewing application ID: $application_id for depot ID: $depot_id");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $application_id = (int)$_POST['application_id'];
    $rejection_reason = trim($_POST['rejection_reason'] ?? '');
    
    if ($action === 'approve' || $action === 'reject') {
        if ($action === 'reject' && empty($rejection_reason)) {
            $_SESSION['error'] = 'Please provide a reason for rejection';
            header("location: view_application.php?id=" . $application_id);
            exit;
        }
        
        $status = $action === 'approve' ? 'approved' : 'rejected';
        
        // Call the update_application_status.php script
        $url = 'update_application_status.php';
        $data = [
            'application_id' => $application_id,
            'status' => $status,
            'rejection_reason' => $rejection_reason
        ];
        
        // Use curl to make the request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        // Redirect back to the application page
        header("location: view_application.php?id=" . $application_id);
        exit;
    }
}

// Application and depot IDs are already set at the top of the file
error_log("Attempting to fetch application ID: $application_id for depot ID: $depot_id");

try {
    // Fetch application details with student and route information
    $sql = "SELECT bp.*, 
                   s.full_name, 
                   s.registration_no, 
                   s.faculty, 
                   s.department, 
                   s.email, 
                   s.phone, 
                   s.address,
                   s.user_id as student_user_id,
                   CONCAT(r.start_point, ' to ', r.end_point) as route_display,
                   r.start_point, 
                   r.end_point,
                   r.distance_km as distance,
                   r.fare,
                   d.depot_name
            FROM bus_passes bp
            JOIN students s ON bp.student_id = s.id
            JOIN routes r ON bp.route_id = r.id
            JOIN depots d ON bp.depot_id = d.id
            WHERE bp.id = ? AND bp.depot_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$application_id, $depot_id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        error_log("No application found with ID: $application_id for depot ID: $depot_id");
        throw new Exception('Application not found or you do not have permission to view it.');
    }
    
    error_log("Successfully loaded application: " . print_r($application, true));
    
} catch (Exception $e) {
    error_log("Error in view_application.php: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header("location: applications.php");
    exit;
}

// Error handling is now in the try-catch block above

$page_title = 'View Application - Depot Dashboard';
include '../includes/depot_header.php';
?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php 
        echo $_SESSION['error']; 
        unset($_SESSION['error']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Pass Application #<?php echo htmlspecialchars($application_id); ?></h1>
        <div>
            <a href="applications.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Applications
            </a>
            <?php if ($application['status'] === 'pending'): ?>
                <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#approveModal">
                    <i class="fas fa-check me-1"></i> Approve
                </button>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fas fa-times me-1"></i> Reject
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Application Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Application ID:</div>
                        <div class="col-md-8">#<?php echo htmlspecialchars($application['id']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Status:</div>
                        <div class="col-md-8">
                            <?php
                            $status_config = [
                                'pending' => ['class' => 'warning', 'text' => 'Pending'],
                                'approved' => ['class' => 'success', 'text' => 'Approved'],
                                'active' => ['class' => 'success', 'text' => 'Active'],
                                'rejected' => ['class' => 'danger', 'text' => 'Rejected'],
                                'expired' => ['class' => 'secondary', 'text' => 'Expired'],
                                'cancelled' => ['class' => 'secondary', 'text' => 'Cancelled']
                            ][$application['status']] ?? ['class' => 'secondary', 'text' => ucfirst($application['status'])];
                            ?>
                            <span class="badge bg-<?php echo $status_config['class']; ?> text-white">
                                <?php echo $status_config['text']; ?>
                            </span>
                        </div>
                    </div>
                    <?php if (in_array($application['status'], ['approved', 'active']) && !empty($application['pass_number'])): ?>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Pass Number:</div>
                        <div class="col-md-8">
                            <span class="badge bg-info text-white"><?php echo htmlspecialchars($application['pass_number']); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Applied On:</div>
                        <div class="col-md-8">
                            <?php echo date('F j, Y \a\t g:i A', strtotime($application['created_at'])); ?>
                        </div>
                    </div>
                    <?php if (in_array($application['status'], ['approved', 'active'])): ?>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Approved On:</div>
                        <div class="col-md-8">
                            <?php 
                            $approved_date = !empty($application['approved_at']) ? $application['approved_at'] : $application['updated_at'];
                            echo date('F j, Y \a\t g:i A', strtotime($approved_date)); 
                            ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Valid Until:</div>
                        <div class="col-md-8">
                            <?php 
                            $valid_until = !empty($application['expiry_date']) ? $application['expiry_date'] : date('Y-m-d', strtotime('+30 days'));
                            $is_expired = strtotime($valid_until) < time();
                            $days_remaining = floor((strtotime($valid_until) - time()) / (60 * 60 * 24));
                            $valid_until_formatted = date('F j, Y', strtotime($valid_until));
                            ?>
                            <div class="d-flex align-items-center">
                                <span class="<?php echo $is_expired ? 'text-danger' : 'text-success'; ?> me-2">
                                    <?php echo $valid_until_formatted; ?>
                                </span>
                                <?php if ($is_expired): ?>
                                    <span class="badge bg-danger">Expired</span>
                                <?php else: ?>
                                    <span class="badge bg-<?php echo $days_remaining <= 7 ? 'warning' : 'success'; ?>" 
                                          title="<?php echo $days_remaining; ?> days remaining">
                                        <?php echo $days_remaining; ?> day<?php echo $days_remaining != 1 ? 's' : ''; ?> left
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!$is_expired): ?>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        Will be deactivated on <?php echo $valid_until_formatted; ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Route Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Route:</div>
                        <div class="col-md-8"><?php echo htmlspecialchars($application['route_display']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">From:</div>
                        <div class="col-md-8"><?php echo htmlspecialchars($application['start_point']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">To:</div>
                        <div class="col-md-8"><?php echo htmlspecialchars($application['end_point']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Distance:</div>
                        <div class="col-md-8"><?php echo htmlspecialchars($application['distance']); ?> km</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 fw-bold">Fare:</div>
                        <div class="col-md-8">LKR <?php echo number_format($application['fare'], 2); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Student Details</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <?php if (!empty($application['profile_image'])): ?>
                            <img src="../uploads/profiles/<?php echo htmlspecialchars($application['profile_image']); ?>" 
                                 alt="Profile Image" class="img-fluid rounded-circle mb-2" style="width: 120px; height: 120px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-2" 
                                 style="width: 120px; height: 120px; margin: 0 auto;">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <h5 class="mb-1"><?php echo htmlspecialchars($application['full_name']); ?></h5>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($application['registration_no']); ?></p>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="fw-bold mb-1">Faculty</div>
                        <div><?php echo htmlspecialchars($application['faculty']); ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="fw-bold mb-1">Department</div>
                        <div><?php echo htmlspecialchars($application['department']); ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="fw-bold mb-1">Email</div>
                        <div><?php echo htmlspecialchars($application['email']); ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="fw-bold mb-1">Phone</div>
                        <div><?php echo htmlspecialchars($application['phone']); ?></div>
                    </div>
                    <div>
                        <div class="fw-bold mb-1">Address</div>
                        <div class="text-muted"><?php echo nl2br(htmlspecialchars($application['address'])); ?></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
    
    <?php if ($application['status'] === 'rejected' && !empty($application['rejection_reason'])): ?>
    <div class="alert alert-warning">
        <h5><i class="fas fa-exclamation-triangle me-2"></i>Application Rejected</h5>
        <p class="mb-0"><strong>Reason:</strong> <?php echo htmlspecialchars($application['rejection_reason']); ?></p>
    </div>
    <?php endif; ?>
    
    <script>
    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const fadeOut = () => {
                    alert.style.transition = 'opacity 1s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 1000);
                };
                setTimeout(fadeOut, 5000);
            });
        }, 1000);
    });
    </script>
    
<?php include '../includes/depot_footer.php'; ?>
