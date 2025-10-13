<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'student'){
    header("location: ../auth/login.php");
    exit;
}

// Include config file
require_once '../config/database.php';

// Set page title
$page_title = 'My Bus Passes - SEUSL Bus Pass Management System';

// Include student header
include '../includes/student_header.php';

// Get student details
$student_id = $_SESSION["id"];
$student = [];
$active_passes = [];
$expired_passes = [];
$error = '';

try {
    // Get student details
    $sql = "SELECT * FROM students WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":user_id", $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$student) {
        throw new Exception("Student record not found.");
    }
    
    // Get active passes
    $sql = "SELECT bp.*, r.start_point, r.end_point, r.fare, d.depot_name,
                   DATEDIFF(bp.expiry_date, CURDATE()) as days_remaining
            FROM bus_passes bp
            JOIN routes r ON bp.route_id = r.id
            JOIN depots d ON bp.depot_id = d.id
            WHERE bp.student_id = :student_id 
            AND bp.status = 'active'
            ORDER BY bp.expiry_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":student_id", $student['id'], PDO::PARAM_INT);
    $stmt->execute();
    $active_passes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get expired passes
    $sql = "SELECT bp.*, r.start_point, r.end_point, r.fare, d.depot_name,
                   DATEDIFF(CURDATE(), bp.expiry_date) as days_expired
            FROM bus_passes bp
            JOIN routes r ON bp.route_id = r.id
            JOIN depots d ON bp.depot_id = d.id
            WHERE bp.student_id = :student_id 
            AND (bp.status = 'expired' OR bp.expiry_date < CURDATE())
            ORDER BY bp.expiry_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":student_id", $student['id'], PDO::PARAM_INT);
    $stmt->execute();
    $expired_passes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
} catch(Exception $e) {
    $error = $e->getMessage();
}
?>

<!-- Main content -->
<main class="col-12 px-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">My Bus Passes</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="apply.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Apply for New Pass
            </a>
        </div>
    </div>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Active Passes -->
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-id-card text-primary me-2"></i>Active Passes
            </h5>
            <span class="badge bg-success"><?php echo count($active_passes); ?> Active</span>
        </div>
        <div class="card-body">
            <?php if(empty($active_passes)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-id-card fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No active bus passes found.</p>
                    <a href="apply.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Apply for a Pass
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Pass Number</th>
                                <th>Route</th>
                                <th>Depot</th>
                                <th>Issued On</th>
                                <th>Expires In</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($active_passes as $pass): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pass['pass_number']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($pass['start_point'] . ' to ' . $pass['end_point']); ?>
                                        <br>
                                        <small class="text-muted">Fare: Rs. <?php echo number_format($pass['fare'], 2); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($pass['depot_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($pass['issue_date'])); ?></td>
                                    <td>
                                        <?php 
                                        $expiry_date = new DateTime($pass['expiry_date']);
                                        $today = new DateTime();
                                        $interval = $today->diff($expiry_date);
                                        
                                        if($interval->days == 0) {
                                            echo '<span class="text-warning">Expires today</span>';
                                        } elseif($interval->invert) {
                                            echo '<span class="text-danger">Expired ' . $interval->days . ' days ago</span>';
                                        } else {
                                            echo $interval->days . ' days';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Active</span>
                                    </td>
                                    <td>
                                        <a href="view_pass.php?id=<?php echo $pass['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Expired Passes -->
    <?php if(!empty($expired_passes)): ?>
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-history text-muted me-2"></i>Expired Passes
                <span class="badge bg-secondary ms-2"><?php echo count($expired_passes); ?></span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Pass Number</th>
                            <th>Route</th>
                            <th>Depot</th>
                            <th>Expired On</th>
                            <th>Days Expired</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($expired_passes as $pass): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pass['pass_number']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($pass['start_point'] . ' to ' . $pass['end_point']); ?>
                                    <br>
                                    <small class="text-muted">Fare: Rs. <?php echo number_format($pass['fare'], 2); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($pass['depot_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($pass['expiry_date'])); ?></td>
                                <td>
                                    <?php 
                                    $expiry_date = new DateTime($pass['expiry_date']);
                                    $today = new DateTime();
                                    $interval = $today->diff($expiry_date);
                                    echo $interval->days . ' days';
                                    ?>
                                </td>
                                <td>
                                    <a href="renew_pass.php?id=<?php echo $pass['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-sync-alt"></i> Renew
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

<?php
// Include footer
include '../includes/footer.php';
?>
