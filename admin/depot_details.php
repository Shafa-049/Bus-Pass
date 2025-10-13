<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'admin'){
    header("location: ../auth/login.php");
    exit;
}

// Include config file
require_once '../config/database.php';

// Check if depot ID is provided
if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
    header("location: depots.php");
    exit();
}

$depot_id = trim($_GET['id']);
$depot = null;

try {
    // Get depot details
    $sql = "SELECT u.id, u.username, u.status, u.created_at, 
                   d.depot_name, d.manager_name, d.email, d.phone, d.address, d.location
            FROM users u 
            LEFT JOIN depots d ON u.id = d.user_id 
            WHERE u.id = :id AND u.user_type = 'depot'
            LIMIT 1";
            
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $depot_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() == 1) {
        $depot = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error_message'] = "Depot not found.";
        header("location: depots.php");
        exit();
    }
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Error fetching depot details: " . $e->getMessage();
    header("location: depots.php");
    exit();
}

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $status = ($_POST['action'] == 'approve') ? 'active' : 'rejected';
    
    try {
        $sql = "UPDATE users SET status = :status WHERE id = :user_id AND user_type = 'depot'";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $depot_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Depot status updated successfully!";
        header("location: depots.php");
        exit();
    } catch(PDOException $e) {
        $error_message = "Error updating depot status: " . $e->getMessage();
    }
}

// Include admin header
$page_title = 'Depot Details - SEUSL Bus Pass Management System';
include '../includes/admin_header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="depots.php">Manage Depots</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Depot Details</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Depot Details</h1>
                <div>
                    <?php if ($depot['status'] == 'pending'): ?>
                        <form method="post" class="d-inline">
                            <button type="submit" name="action" value="approve" class="btn btn-success me-2">
                                <i class="fas fa-check"></i> Approve Depot
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger" 
                                    onclick="return confirm('Are you sure you want to reject this depot application?')">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="badge bg-<?php 
                            echo $depot['status'] == 'active' ? 'success' : 'danger'; 
                        ?> p-2">
                            <?php echo ucfirst($depot['status']); ?>
                        </span>
                    <?php endif; ?>
                    <a href="depots.php" class="btn btn-outline-secondary ms-2">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Depot Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Basic Information</h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Depot Name</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($depot['depot_name']); ?></dd>
                                
                                <dt class="col-sm-4">Manager Name</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($depot['manager_name']); ?></dd>
                                
                                <dt class="col-sm-4">Location</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($depot['location']); ?></dd>
                                
                                <dt class="col-sm-4">Address</dt>
                                <dd class="col-sm-8"><?php echo nl2br(htmlspecialchars($depot['address'])); ?></dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-muted">Contact Information</h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Email</dt>
                                <dd class="col-sm-8">
                                    <a href="mailto:<?php echo htmlspecialchars($depot['email']); ?>">
                                        <?php echo htmlspecialchars($depot['email']); ?>
                                    </a>
                                </dd>
                                
                                <dt class="col-sm-4">Phone</dt>
                                <dd class="col-sm-8">
                                    <a href="tel:<?php echo htmlspecialchars($depot['phone']); ?>">
                                        <?php echo htmlspecialchars($depot['phone']); ?>
                                    </a>
                                </dd>
                                
                                <dt class="col-sm-4">Location</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($depot['location']); ?></dd>
                                
                                <dt class="col-sm-4">Address</dt>
                                <dd class="col-sm-8"><?php echo nl2br(htmlspecialchars($depot['address'])); ?></dd>
                            </dl>
                            
                            <h6 class="text-muted mt-4">Account Information</h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Username</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($depot['username']); ?></dd>
                                
                                <dt class="col-sm-4">Account Status</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-<?php 
                                        echo $depot['status'] == 'active' ? 'success' : 
                                            ($depot['status'] == 'pending' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($depot['status']); ?>
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-4">Registered On</dt>
                                <dd class="col-sm-8">
                                    <?php echo date('M d, Y \a\t h:i A', strtotime($depot['created_at'])); ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h6 class="text-muted">Actions</h6>
                        <div class="btn-group">
                            <?php if ($depot['status'] == 'pending'): ?>
                                <form method="post" class="me-2">
                                    <button type="submit" name="action" value="approve" class="btn btn-success">
                                        <i class="fas fa-check"></i> Approve Depot
                                    </button>
                                </form>
                                <form method="post" class="me-2">
                                    <button type="submit" name="action" value="reject" class="btn btn-danger" 
                                            onclick="return confirm('Are you sure you want to reject this depot application?')">
                                        <i class="fas fa-times"></i> Reject Application
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <a href="edit_depot.php?id=<?php echo $depot['id']; ?>" class="btn btn-primary me-2">
                                <i class="fas fa-edit"></i> Edit Details
                            </a>
                            
                            <button class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#sendMessageModal">
                                <i class="fas fa-envelope"></i> Send Message
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send Message Modal -->
<div class="modal fade" id="sendMessageModal" tabindex="-1" aria-labelledby="sendMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendMessageModalLabel">Send Message to <?php echo htmlspecialchars($depot['depot_name']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="send_message.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="depot_id" value="<?php echo $depot['id']; ?>">
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Include admin footer
include '../includes/admin_footer.php';
?>
