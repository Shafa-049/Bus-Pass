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
$page_title = 'Student Dashboard - SEUSL Bus Pass Management System';

// Include student header
include '../includes/student_header.php';

// Get student details
$student_id = $_SESSION["id"];
$student = [];
$active_pass = null;
$recent_payments = [];
$notifications = [];
$upcoming_trips = [];
$depot_info = null;

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
    
    // Get active bus pass
    $sql = "SELECT bp.*, r.start_point, r.end_point, r.fare, d.depot_name 
            FROM bus_passes bp 
            JOIN routes r ON bp.route_id = r.id 
            JOIN depots d ON bp.depot_id = d.id 
            WHERE bp.student_id = :student_id 
            AND bp.status = 'active' 
            AND bp.expiry_date >= CURDATE() 
            ORDER BY bp.expiry_date DESC 
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":student_id", $student['id'], PDO::PARAM_INT);
    $stmt->execute();
    $active_pass = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent payments
    $sql = "SELECT * FROM payments 
            WHERE student_id = :student_id 
            ORDER BY created_at DESC 
            LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":student_id", $student['id'], PDO::PARAM_INT);
    $stmt->execute();
    $recent_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unread notifications
    $sql = "SELECT * FROM notifications 
            WHERE user_id = :user_id 
            AND is_read = FALSE 
            ORDER BY created_at DESC 
            LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":user_id", $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
} catch(Exception $e) {
    $error = $e->getMessage();
}

?>

<!-- Main content -->
<main class="col-12 px-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                        <i class="fas fa-calendar-alt me-1"></i>
                        This week
                    </button>
                </div>
            </div>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card text-white h-100" style="background: linear-gradient(135deg, #008080 0%, #006666 100%);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-uppercase small">Active Bus Pass</h6>
                                    <h3 class="mb-0">
                                        <?php echo $active_pass ? 'Valid' : 'None'; ?>
                                    </h3>
                                </div>
                                <i class="fas fa-ticket-alt fa-3x text-white-50"></i>
                            </div>
                            <?php if($active_pass): ?>
                                <p class="mb-0 mt-2 small">
                                    Expires on <?php echo date('M d, Y', strtotime($active_pass['expiry_date'])); ?>
                                </p>
                            <?php else: ?>
                                <a href="apply-pass.php" class="btn btn-teal text-white mt-2">
    <i class="fas fa-plus-circle me-1"></i> Apply Now
</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-white h-100" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Total Payments</h6>
                                    <h2 class="mb-0">
                                        LKR <?php 
                                            $total = 0;
                                            foreach($recent_payments as $payment) {
                                                if($payment['status'] === 'completed') {
                                                    $total += $payment['amount'];
                                                }
                                            }
                                            echo number_format($total, 2);
                                        ?>
                                    </h2>
                                </div>
                                <i class="fas fa-credit-card fa-3x opacity-50"></i>
                            </div>
                            <p class="mb-0 mt-2 small">
                                <?php echo count($recent_payments); ?> transaction(s)
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-warning h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Notifications</h6>
                                    <h2 class="mb-0">
                                        <?php echo count($notifications); ?>
                                    </h2>
                                </div>
                                <i class="fas fa-bell fa-3x opacity-50"></i>
                            </div>
                            <p class="mb-0 mt-2 small">
                                <?php echo count($notifications) > 0 ? 'You have unread notifications' : 'No new notifications'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Active Bus Pass -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100" style="background-color: rgba(0, 128, 128, 0.05);">
                        <div class="card-header bg-teal text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-ticket-alt me-2"></i>Active Bus Pass
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if($active_pass): ?>
                                <div class="text-center mb-3">
                                    <div class="bus-pass-preview p-3 border rounded bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="text-start">
                                                <h5 class="mb-0">SEUSL Bus Pass</h5>
                                                <small class="text-muted"><?php echo $active_pass['pass_number']; ?></small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-success">Active</span>
                                            </div>
                                        </div>
                                        <div class="text-center my-4">
                                            <div id="qrcode" class="mx-auto" style="width: 150px; height: 150px;"></div>
                                            <p class="text-muted small mt-2">Scan QR Code</p>
                                        </div>
                                        <div class="row text-start">
                                            <div class="col-6">
                                                <p class="mb-1"><strong>Name:</strong></p>
                                                <p class="mb-1"><strong>Reg No:</strong></p>
                                                <p class="mb-1"><strong>Route:</strong></p>
                                                <p class="mb-1"><strong>Depot:</strong></p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1"><?php echo htmlspecialchars($student['full_name']); ?></p>
                                                <p class="mb-1"><?php echo htmlspecialchars($student['registration_no']); ?></p>
                                                <p class="mb-1"><?php echo htmlspecialchars($active_pass['start_point'] . ' to ' . $active_pass['end_point']); ?></p>
                                                <p class="mb-1"><?php echo htmlspecialchars($active_pass['depot_name']); ?></p>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                            <small class="text-muted">Issued: <?php echo date('d/m/Y', strtotime($active_pass['issue_date'])); ?></small>
                                            <small class="text-muted">Expires: <?php echo date('d/m/Y', strtotime($active_pass['expiry_date'])); ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary" onclick="printPass()">
                                        <i class="fas fa-print me-2"></i>Print Pass
                                    </button>
                                    <button class="btn btn-outline-success" onclick="downloadPass()">
                                        <i class="fas fa-download me-2"></i>Download PDF
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-ticket-alt fa-4x text-muted mb-3"></i>
                                    <h5>No Active Bus Pass</h5>
                                    <p class="text-muted">You don't have an active bus pass at the moment.</p>
                                    <a href="apply-pass.php" class="btn btn-teal">
                                        <i class="fas fa-plus-circle me-2"></i>Apply for New Pass
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="col-md-6">
                    <!-- Recent Payments -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-teal text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-credit-card me-2"></i>Recent Payments
                            </h5>
                            <a href="payments.php" class="text-white">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if(count($recent_payments) > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach($recent_payments as $payment): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">Payment #<?php echo $payment['id']; ?></h6>
                                                <small class="text-<?php echo $payment['status'] === 'completed' ? 'success' : ($payment['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                    <?php echo ucfirst($payment['status']); ?>
                                                </small>
                                            </div>
                                            <p class="mb-1">LKR <?php echo number_format($payment['amount'], 2); ?></p>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y h:i A', strtotime($payment['created_at'])); ?>
                                            </small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No recent payments found.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="card">
                        <div class="card-header bg-teal text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bell me-2"></i>Notifications
                            </h5>
                            <a href="notifications.php" class="text-white">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if(count($notifications) > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach($notifications as $notification): ?>
                                        <a href="#" class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                <small><?php echo time_elapsed_string($notification['created_at']); ?></small>
                                            </div>
                                            <p class="mb-1"><?php echo substr(htmlspecialchars($notification['message']), 0, 80); ?>...</p>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No new notifications.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- QR Code Library -->
<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>

<!-- Include student footer -->
<?php include '../includes/student_footer.php'; ?>

<script>
// Generate QR Code for the bus pass
<?php if($active_pass): ?>
document.addEventListener('DOMContentLoaded', function() {
    // Generate QR code with pass details
    const qrData = `SEUSL Bus Pass\n` +
                  `Pass No: ${'<?php echo $active_pass["pass_number"]; ?>'}\n` +
                  `Name: ${'<?php echo addslashes($student["full_name"]); ?>'}\n` +
                  `Reg No: ${'<?php echo $student["registration_no"]; ?>'}\n` +
                  `Route: ${'<?php echo $active_pass["start_point"]; ?> to <?php echo $active_pass["end_point"]; ?>'}\n` +
                  `Valid Until: ${'<?php echo date("d/m/Y", strtotime($active_pass["expiry_date"])); ?>'}`;
    
    // Create QR code
    new QRCode(document.getElementById("qrcode"), {
        text: qrData,
        width: 150,
        height: 150,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
});
<?php endif; ?>

// Print bus pass
function printPass() {
    // You can implement print functionality here
    window.print();
}

// Download bus pass as PDF
function downloadPass() {
    // You can implement PDF download functionality here
    alert('PDF download functionality will be implemented here.');
}

// Function to format time elapsed
function time_elapsed_string(datetime, full = false) {
    let date = new Date(datetime);
    let now = new Date();
    let seconds = Math.floor((now - date) / 1000);
    
    let interval = Math.floor(seconds / 31536000);
    if (interval >= 1) {
        return interval + ' year' + (interval === 1 ? '' : 's') + ' ago';
    }
    interval = Math.floor(seconds / 2592000);
    if (interval >= 1) {
        return interval + ' month' + (interval === 1 ? '' : 's') + ' ago';
    }
    interval = Math.floor(seconds / 86400);
    if (interval >= 1) {
        return interval + ' day' + (interval === 1 ? '' : 's') + ' ago';
    }
    interval = Math.floor(seconds / 3600);
    if (interval >= 1) {
        return interval + ' hour' + (interval === 1 ? '' : 's') + ' ago';
    }
    interval = Math.floor(seconds / 60);
    if (interval >= 1) {
        return interval + ' minute' + (interval === 1 ? '' : 's') + ' ago';
    }
    return Math.floor(seconds) + ' second' + (seconds === 1 ? '' : 's') + ' ago';
}
</script>

<?php
// Include footer
include '../includes/footer.php';
?>
