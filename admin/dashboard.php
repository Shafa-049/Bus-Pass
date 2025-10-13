<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'admin'){
    header("location: ../auth/login.php");
    exit;
}

// Include admin header
$page_title = 'Admin Dashboard - SEUSL Bus Pass Management System';
include '../includes/admin_header.php';

// Get counts for dashboard
require_once '../config/database.php';

$counts = [
    'total_students' => 0,
    'total_depots' => 0,
    'pending_applications' => 0,
    'active_passes' => 0
];

try {
    // Get total students
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'student' AND status = 'active'");
    $counts['total_students'] = $stmt->fetch()['count'];
    
    // Get total depots
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'depot' AND status = 'active'");
    $counts['total_depots'] = $stmt->fetch()['count'];
    
    // Get pending applications
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'pending'");
    $counts['pending_applications'] = $stmt->fetch()['count'];
    
    // Get active passes
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bus_passes WHERE status = 'active' AND expiry_date >= CURDATE()");
    $counts['active_passes'] = $stmt->fetch()['count'];
    
} catch(PDOException $e) {
    // Log error but don't show to user
    error_log("Error getting dashboard counts: " . $e->getMessage());
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Admin Dashboard</h1>
            <div class="alert alert-info">
                Welcome, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>! You are logged in as an administrator.
            </div>
            
            <div class="row mt-4">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                            <h5 class="card-title">Manage Users</h5>
                            <p class="card-text">View and manage all system users including students and depot staff.</p>
                            <a href="users.php" class="btn btn-primary">Go to Users</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-bus fa-3x mb-3 text-success"></i>
                            <h5 class="card-title">Manage Depots</h5>
                            <p class="card-text">Manage bus depots, routes, and schedules.</p>
                            <a href="depots.php" class="btn btn-success">Manage Depots</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-bar fa-3x mb-3 text-info"></i>
                            <h5 class="card-title">Reports</h5>
                            <p class="card-text">View system reports and analytics.</p>
                            <a href="reports.php" class="btn btn-info">View Reports</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include admin footer
include '../includes/admin_footer.php';
?>
