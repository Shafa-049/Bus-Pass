<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'depot'){
    header("location: ../auth/login.php");
    exit;
}

// Include depot header
$page_title = 'Depot Dashboard - SEUSL Bus Pass Management System';
include '../includes/depot_header.php';

// Get counts for dashboard
require_once '../config/database.php';

$counts = [
    'pending_applications' => 0,
    'approved_applications' => 0,
    'rejected_applications' => 0,
    'total_students' => 0
];

try {
    // Get counts for the current depot
    $stmt = $pdo->prepare("SELECT 
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        FROM bus_passes 
        WHERE depot_id = :depot_id");
    $stmt->execute(['depot_id' => $_SESSION['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $counts['pending_applications'] = $result['pending'] ?? 0;
        $counts['approved_applications'] = $result['approved'] ?? 0;
        $counts['rejected_applications'] = $result['rejected'] ?? 0;
    }
    
    // Get total students
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE depot_id = :depot_id");
    $stmt->execute(['depot_id' => $_SESSION['id']]);
    $counts['total_students'] = $stmt->fetch()['count'] ?? 0;
    
} catch(PDOException $e) {
    // Log error but don't show to user
    error_log("Error getting dashboard counts: " . $e->getMessage());
}
?>

<div class="content-wrapper">
    <h1 class="mb-4">Depot Dashboard</h1>
    <div class="alert alert-info">
        Welcome back, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>!
    </div>
            
            <div class="row mt-4">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-ticket-alt me-2 text-primary"></i>Pass Applications</h5>
                            <p class="card-text">View and manage bus pass applications for your depot.</p>
                            <a href="applications.php" class="btn btn-primary">View Applications</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-bus me-2 text-success"></i>Bus Schedule</h5>
                            <p class="card-text">Manage bus schedules and routes for your depot.</p>
                            <a href="schedule.php" class="btn btn-success">Manage Schedule</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-users me-2 text-info"></i>Students</h5>
                            <p class="card-text">View and manage student information.</p>
                            <a href="students.php" class="btn btn-info">View Students</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-chart-bar me-2 text-warning"></i>Reports</h5>
                            <p class="card-text">Generate and view depot reports.</p>
                            <a href="reports.php" class="btn btn-warning">View Reports</a>
                        </div>
                    </div>
                </div>
            </div>
</div>

<?php
// Include depot footer
include '../includes/depot_footer.php';
?>
