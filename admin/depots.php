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

// Handle depot status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = trim($_POST['user_id']);
    $status = ($_POST['action'] == 'approve') ? 'active' : 'rejected';
    
    try {
        $sql = "UPDATE users SET status = :status WHERE id = :user_id AND user_type = 'depot'";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Depot status updated successfully!";
        header("location: depots.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error updating depot status: " . $e->getMessage();
    }
}

// Fetch all depots with their details
$depots = [];
try {
    $sql = "SELECT u.id, u.username, u.status, u.created_at, 
                   d.depot_name, d.manager_name, d.email, d.phone, d.address, d.location
            FROM users u 
            LEFT JOIN depots d ON u.id = d.user_id 
            WHERE u.user_type = 'depot'
            ORDER BY 
                CASE u.status 
                    WHEN 'pending' THEN 1 
                    WHEN 'active' THEN 2 
                    WHEN 'rejected' THEN 3 
                    WHEN 'suspended' THEN 4 
                    ELSE 5 
                END,
                u.created_at DESC";
    $stmt = $pdo->query($sql);
    $depots = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Error fetching depots: " . $e->getMessage();
}

// Include admin header
$page_title = 'Manage Depots - SEUSL Bus Pass Management System';
include '../includes/admin_header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Manage Bus Depots</h1>
            
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
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Depot Applications</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($depots)): ?>
                        <div class="alert alert-info mb-0">No depot applications found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Depot Name</th>
                                        <th>Manager</th>
                                        <th>Contact</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Applied On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($depots as $depot): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($depot['depot_name']); ?></td>
                                            <td><?php echo htmlspecialchars($depot['manager_name']); ?></td>
                                            <td>
                                                <div><?php echo htmlspecialchars($depot['email']); ?></div>
                                                <div class="text-muted small"><?php echo htmlspecialchars($depot['phone']); ?></div>
                                            </td>
                                            <td><?php echo htmlspecialchars($depot['location']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $depot['status'] == 'active' ? 'success' : 
                                                        ($depot['status'] == 'pending' ? 'warning' : 'danger'); 
                                                ?>">
                                                    <?php echo ucfirst($depot['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($depot['created_at'])); ?></td>
                                            <td>
                                                <?php if ($depot['status'] == 'pending'): ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?php echo $depot['id']; ?>">
                                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success me-1">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
                                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" 
                                                                onclick="return confirm('Are you sure you want to reject this depot application?')">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fas fa-check"></i> Processed
                                                    </button>
                                                <?php endif; ?>
                                                <a href="depot_details.php?id=<?php echo $depot['id']; ?>" class="btn btn-sm btn-info ms-1" title="View Details">
                                                    <i class="fas fa-eye"></i>
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
        </div>
    </div>
</div>

<?php
// Include admin footer
include '../includes/admin_footer.php';
?>
