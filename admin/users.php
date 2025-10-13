<?php
// Initialize the session
session_start();
 
// Check if the user is logged in and is admin, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'admin'){
    header("location: ../auth/login.php");
    exit;
}

// Include database connection
require_once '../config/database.php';

// Define variables and initialize with empty values
$error_message = "";
$success_message = "";

// Fetch all users with their details
$users = [];
try {
    $sql = "SELECT 
                u.id, 
                u.username, 
                u.user_type, 
                u.status, 
                u.created_at,
                CASE 
                    WHEN u.user_type = 'student' THEN s.full_name
                    WHEN u.user_type = 'depot' THEN d.manager_name
                    ELSE 'Admin User'
                END as full_name,
                CASE 
                    WHEN u.user_type = 'student' THEN s.email
                    WHEN u.user_type = 'depot' THEN d.email
                    ELSE 'admin@seusl.lk'
                END as email
            FROM users u
            LEFT JOIN students s ON u.id = s.user_id AND u.user_type = 'student'
            LEFT JOIN depots d ON u.id = d.user_id AND u.user_type = 'depot'
            ORDER BY 
                CASE u.status 
                    WHEN 'pending' THEN 1 
                    WHEN 'active' THEN 2 
                    WHEN 'suspended' THEN 3
                    WHEN 'rejected' THEN 4
                    ELSE 5 
                END,
                u.created_at DESC";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Error fetching users: " . $e->getMessage();
}

// Handle deactivate action
if(isset($_GET['deactivate_id']) && !empty($_GET['deactivate_id'])){
    $user_id = trim($_GET['deactivate_id']);
    try {
        $sql = "UPDATE users SET status = 'suspended' WHERE id = :id AND user_type != 'admin'";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
        if($stmt->execute()) {
            $_SESSION['success_message'] = "User deactivated successfully.";
        } else {
            $_SESSION['error_message'] = "Error deactivating user.";
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
    header("location: users.php");
    exit();
}

// Handle activate action
if(isset($_GET['activate_id']) && !empty($_GET['activate_id'])){
    $user_id = trim($_GET['activate_id']);
    try {
        $sql = "UPDATE users SET status = 'active' WHERE id = :id AND user_type != 'admin'";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
        if($stmt->execute()) {
            $_SESSION['success_message'] = "User activated successfully.";
        } else {
            $_SESSION['error_message'] = "Error activating user.";
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
    header("location: users.php");
    exit();
}

// Process delete operation after confirmation
if(isset($_POST["id"]) && !empty($_POST["id"])){
    // Prepare a delete statement
    $sql = "DELETE FROM users WHERE id = :id AND user_type != 'admin'";
    
    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":id", $param_id);
        
        // Set parameters
        $param_id = trim($_POST["id"]);
        
        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Records deleted successfully. Redirect to landing page
            $success_message = "User account has been deactivated successfully.";
        } else{
            $error_message = "Oops! Something went wrong. Please try again later.";
        }
    }
    
    // Close statement
    unset($stmt);
}

// Process activate operation
if(isset($_GET["activate_id"]) && !empty(trim($_GET["activate_id"]))){
    // Prepare an update statement
    $sql = "UPDATE users SET status = 'active' WHERE id = :id";
    
    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":id", $param_id);
        
        // Set parameters
        $param_id = trim($_GET["activate_id"]);
        
        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Records updated successfully. Redirect to landing page
            $success_message = "User account has been activated successfully.";
        } else{
            $error_message = "Oops! Something went wrong. Please try again later.";
        }
    }
    
    // Close statement
    unset($stmt);
}

// Include admin header
$page_title = 'Manage Users - SEUSL Bus Pass Management System';
include '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Manage Users</h1>
                <a href="user_add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New User
                </a>
            </div>
            
            <?php
            // Display success/error messages
            if(!empty($success_message)){
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
                echo $success_message;
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
            } elseif(!empty($error_message)){
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                echo $error_message;
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
            }
            ?>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">User Management</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($users)): ?>
                        <div class="alert alert-info mb-0">No users found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Created On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    foreach($users as $row): 
                                        $status_class = '';
                                        if($row['status'] == 'active'){
                                            $status_class = 'success';
                                        } elseif($row['status'] == 'pending'){
                                            $status_class = 'warning';
                                        } elseif($row['status'] == 'suspended'){
                                            $status_class = 'danger';
                                        } elseif($row['status'] == 'rejected'){
                                            $status_class = 'secondary';
                                        }
                                        
                                        $name_parts = explode(' ', $row['full_name'] ?? 'N/A');
                                        $initials = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
                                    ?>
                                    <tr>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2 bg-soft-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                    <span class="text-primary fw-bold"><?php echo $initials; ?></span>
                                                </div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($row['full_name'] ?? 'N/A'); ?></div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <span class="text-muted">@<?php echo htmlspecialchars($row['username']); ?></span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="text-muted"><?php echo htmlspecialchars($row['email']); ?></span>
                                        </td>
                                        <td class="align-middle">
                                            <?php echo ucfirst(htmlspecialchars($row['user_type'])); ?>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                        </td>
                                        <td class="align-middle">
                                            <div class="btn-group" role="group">
                                                <a href="user_view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info me-1" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if($row['user_type'] != 'admin' || $_SESSION['id'] == $row['id']): ?>
                                                    <a href="user_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary me-1" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if($row['status'] == 'active' && $row['user_type'] != 'admin'): ?>
                                                    <a href="#" class="btn btn-sm btn-warning me-1 deactivate-btn" 
                                                       title="Deactivate" data-bs-toggle="modal" data-bs-target="#confirmModal" 
                                                       data-id="<?php echo $row['id']; ?>" data-action="deactivate">
                                                        <i class="fas fa-ban"></i>
                                                    </a>
                                                <?php elseif($row['status'] != 'active'): ?>
                                                    <a href="users.php?activate_id=<?php echo $row['id']; ?>" 
                                                       class="btn btn-sm btn-success me-1" 
                                                       title="Activate" 
                                                       onclick="return confirm('Are you sure you want to activate this user?')">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if($row['user_type'] != 'admin'): ?>
                                                    <a href="#" class="btn btn-sm btn-danger delete-btn" 
                                                       title="Delete" data-bs-toggle="modal" data-bs-target="#confirmModal" 
                                                       data-id="<?php echo $row['id']; ?>" data-action="delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
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
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be set by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning btn-confirm">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Activate Confirmation Modal -->
<div class="modal fade" id="activateModal" tabindex="-1" aria-labelledby="activateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activateModalLabel">Confirm Activation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to activate this user account? The user will be able to log in after activation.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirm-activate" class="btn btn-success">Activate</a>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 for better alerts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
/* Custom styles for SweetAlert2 */
.swal2-popup {
    font-size: 0.875rem !important;
    border: 2px solid #20c997 !important;
    border-radius: 0.5rem !important;
}

.swal2-title {
    font-size: 1.25rem !important;
    color: #20c997 !important;
}

.swal2-html-container {
    font-size: 0.9375rem !important;
    color: #495057 !important;
}

.swal2-actions {
    margin: 1rem 0 0 0 !important;
}

.swal2-styled {
    padding: 0.5rem 1.25rem !important;
    font-size: 0.875rem !important;
}

.swal2-styled.swal2-confirm {
    background-color: #20c997 !important;
    border-color: #20c997 !important;
}

.swal2-styled.swal2-confirm:focus {
    box-shadow: 0 0 0 0.25rem rgba(32, 201, 151, 0.5) !important;
}

.delete-confirm-dialog .swal2-html-container {
    margin: 0.5rem 0 !important;
    font-size: 0.875rem !important;
{{ ... }}

.delete-confirm-dialog .swal2-icon {
    margin: 0 0 0.5rem 0 !important;
    transform: scale(0.8) !important;
}

/* Custom toast notification */
.swal2-toast.swal2-icon-success {
    border: 2px solid #20c997 !important;
    background: #fff !important;
}

.swal2-toast .swal2-title {
    color: #20c997 !important;
    font-weight: 600 !important;
}

.swal2-toast .swal2-icon.swal2-success .swal2-success-ring {
    border-color: rgba(32, 201, 151, 0.3) !important;
}

.swal2-toast .swal2-icon.swal2-success [class^=swal2-success-line] {
    background-color: #20c997 !important;
}
</style>

<script>
// Set up delete button click handler
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap modal
    const confirmModalElement = document.getElementById('confirmModal');
    const confirmModal = new bootstrap.Modal(confirmModalElement);
    
    const modalTitle = confirmModalElement.querySelector('.modal-title');
    const modalBody = confirmModalElement.querySelector('.modal-body');
    const confirmBtn = confirmModalElement.querySelector('.btn-confirm');
    let currentAction = '';
    let currentUserId = '';

    // Handle deactivate/delete button clicks
    document.querySelectorAll('.deactivate-btn, .delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            currentAction = this.getAttribute('data-action');
            currentUserId = this.getAttribute('data-id');
            const userName = this.closest('tr').querySelector('td:nth-child(2) .fw-bold').textContent.trim();
            
            if (currentAction === 'deactivate') {
                modalTitle.textContent = 'Deactivate User';
                modalBody.innerHTML = `Are you sure you want to deactivate ${userName}?`;
                confirmBtn.textContent = 'Deactivate';
                confirmBtn.className = 'btn btn-warning btn-confirm';
            } else if (currentAction === 'delete') {
                modalTitle.textContent = 'Delete User';
                modalBody.innerHTML = `Are you sure you want to delete ${userName}? This action cannot be undone.`;
                confirmBtn.textContent = 'Delete';
                confirmBtn.className = 'btn btn-danger btn-confirm';
            }
        });
    });

    // Handle confirm button click
    confirmBtn.addEventListener('click', function() {
        if (currentAction === 'deactivate') {
            window.location.href = `users.php?deactivate_id=${currentUserId}`;
        } else if (currentAction === 'delete') {
            window.location.href = `user_delete.php?id=${currentUserId}`;
        } else {
            // If no action is set, just close the modal
            confirmModal.hide();
        }
    });

    // Close modal when clicking outside
    confirmModalElement.addEventListener('hidden.bs.modal', function () {
        // Reset modal content
        modalTitle.textContent = 'Confirm Action';
        modalBody.innerHTML = '';
        confirmBtn.textContent = 'Confirm';
        confirmBtn.className = 'btn btn-primary btn-confirm';
    });
    
    // Check for success/error messages in session storage
    <?php if(isset($_SESSION['success_message'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?php echo addslashes($_SESSION['success_message']); ?>',
            timer: 3000,
            timerProgressBar: true,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            customClass: {
                popup: 'swal2-popup-custom',
                title: 'text-teal'
            },
            background: '#fff',
            color: '#333',
            iconColor: '#20c997',
            timerProgressBar: 'rgba(32, 201, 151, 0.7)'
        });
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error_message'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo addslashes($_SESSION['error_message']); ?>',
            timer: 5000,
            timerProgressBar: true,
            toast: true,
            position: 'top-end',
            showConfirmButton: true,
            confirmButtonColor: '#20c997',
            customClass: {
                popup: 'swal2-popup-custom',
                title: 'text-teal',
                confirmButton: 'btn btn-teal'
            },
            background: '#fff',
            color: '#333',
            iconColor: '#dc3545',
            timerProgressBar: 'rgba(220, 53, 69, 0.7)'
        });
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
});
</script>

<?php
// Include admin footer
include '../includes/admin_footer.php';
?>
