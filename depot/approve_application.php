<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Check if user is logged in and is a depot user
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'depot') {
    header("location: ../auth/login.php");
    exit;
}

$page_title = 'Approve Application - Depot Dashboard';
$error = '';
$success = '';
$application = null;
$depot_id = $_SESSION['depot_id'] ?? 0;

// Check if application ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $error = 'Invalid application ID';
} else {
    $application_id = (int)$_GET['id'];
    
    // Fetch application details
    $sql = "SELECT bp.*, s.full_name, s.registration_no, d.depot_name, 
                   r.start_point, r.end_point, r.id as route_id,
                   CONCAT(r.start_point, ' to ', r.end_point) as route_name,
                   r.fare, r.distance_km, r.estimated_time
            FROM bus_passes bp
            JOIN students s ON bp.student_id = s.id
            JOIN depots d ON bp.depot_id = d.id
            JOIN routes r ON bp.route_id = r.id
            WHERE bp.id = ? AND bp.depot_id = ? 
            AND (bp.status = 'pending' OR bp.status = '' OR bp.status IS NULL)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$application_id, $depot_id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        // Let's check if the application exists but has already been processed
        $check_sql = "SELECT status FROM bus_passes WHERE id = ? AND depot_id = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$application_id, $depot_id]);
        $app_status = $check_stmt->fetchColumn();
        
        if ($app_status === false) {
            $error = 'Application not found';
        } else {
            $error = 'Application cannot be approved. Current status: ' . 
                    (empty($app_status) ? 'Pending' : ucfirst($app_status));
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_application'])) {
    if (!$application) {
        $error = 'Invalid application';
    } else {
        $pass_number = trim($_POST['pass_number'] ?? '');
        
        if (empty($pass_number)) {
            $error = 'Pass number is required';
        } else {
            // Check if pass number already exists
            $check_sql = "SELECT id FROM bus_passes WHERE pass_number = ? AND id != ?";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([$pass_number, $application_id]);
            
            if ($check_stmt->fetch()) {
                $error = 'This pass number is already in use. Please choose a different one.';
            } else {
                try {
                    $pdo->beginTransaction();
                    
                    // Update application status and set pass number
                    $update_sql = "UPDATE bus_passes 
                                  SET status = 'active', 
                                      pass_number = ?,
                                      issue_date = CURDATE(),
                                      expiry_date = DATE_ADD(CURDATE(), INTERVAL 30 DAY),
                                      updated_at = NOW()
                                  WHERE id = ? AND depot_id = ?";
                    
                    $update_stmt = $pdo->prepare($update_sql);
                    $update_stmt->execute([$pass_number, $application_id, $depot_id]);
                    
                    // Create notification for student
                    $message = "Your bus pass application has been approved. Your pass number is: $pass_number. The pass is valid for 30 days from today.";
                    $notif_sql = "INSERT INTO notifications (user_id, title, message) 
                                 SELECT user_id, 'Application Approved', ? 
                                 FROM students WHERE id = ?";
                    $notif_stmt = $pdo->prepare($notif_sql);
                    $notif_stmt->execute([$message, $application['student_id']]);
                    
                    $pdo->commit();
                    
                    // Redirect to applications page with success message
                    $_SESSION['success_message'] = 'Application approved successfully!';
                    header("Location: applications.php");
                    exit;
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'Error approving application: ' . $e->getMessage();
                    error_log($error);
                }
            }
        }
    }
}

include '../includes/depot_header.php';
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3">Approve Application</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="applications.php">Applications</a></li>
                        <li class="breadcrumb-item active">Approve Application</li>
                    </ol>
                </nav>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                <a href="applications.php" class="btn btn-sm btn-outline-danger ms-3">Back to Applications</a>
            </div>
        <?php elseif (!$application): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i> No application found or already processed.
                <a href="applications.php" class="btn btn-sm btn-outline-warning ms-3">Back to Applications</a>
            </div>
        <?php else: ?>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-user-graduate me-2"></i>
                                Student Application Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Student Information</h6>
                                    <p class="mb-1">
                                        <strong>Name:</strong> <?php echo htmlspecialchars($application['full_name']); ?>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Registration No:</strong> <?php echo htmlspecialchars($application['registration_no']); ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Route Information</h6>
                                    <p class="mb-1">
                                        <strong>Route:</strong> <?php echo htmlspecialchars($application['route_name']); ?><br>
                                        <strong>Distance:</strong> <?php echo htmlspecialchars($application['distance_km']); ?> km<br>
                                        <strong>Fare:</strong> Rs. <?php echo number_format($application['fare'], 2); ?>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Depot:</strong> <?php echo htmlspecialchars($application['depot_name']); ?>
                                    </p>
                                </div>
                            </div>

                            <hr>
                            
                            <form method="post" action="">
                                <h5 class="mb-3">
                                    <i class="fas fa-ticket-alt text-primary me-2"></i>
                                    Assign Pass Number
                                </h5>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Please enter a unique pass number for this student. This pass will be valid for 30 days from today.
                                </div>
                                
                                <div class="mb-3">
                                    <label for="pass_number" class="form-label">Pass Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg" 
                                           id="pass_number" name="pass_number" 
                                           value="<?php echo isset($_POST['pass_number']) ? htmlspecialchars($_POST['pass_number']) : ''; ?>"
                                           placeholder="Enter unique pass number" required>
                                    <div class="form-text">This pass number must be unique for each student.</div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="applications.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Back to Applications
                                    </a>
                                    <button type="submit" name="approve_application" class="btn btn-success">
                                        <i class="fas fa-check-circle me-1"></i> Approve & Issue Pass
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
