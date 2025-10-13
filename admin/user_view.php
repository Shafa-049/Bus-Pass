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

// Check if ID parameter exists
if(empty(trim($_GET["id"]))){
    // URL doesn't contain id parameter. Redirect to error page
    header("location: error.php");
    exit();
}

// Prepare a select statement
$sql = "SELECT 
            u.id, 
            u.username, 
            u.user_type, 
            u.status, 
            u.created_at,
            u.updated_at,
            CASE 
                WHEN u.user_type = 'student' THEN s.full_name
                WHEN u.user_type = 'depot' THEN d.manager_name
                ELSE 'Admin User'
            END as full_name,
            CASE 
                WHEN u.user_type = 'student' THEN s.email
                WHEN u.user_type = 'depot' THEN d.email
                ELSE 'admin@seusl.lk'
            END as email,
            CASE 
                WHEN u.user_type = 'student' THEN s.phone
                WHEN u.user_type = 'depot' THEN d.phone
                ELSE 'N/A'
            END as phone,
            CASE 
                WHEN u.user_type = 'student' THEN s.address
                WHEN u.user_type = 'depot' THEN d.address
                ELSE 'N/A'
            END as address,
            s.faculty,
            s.department,
            s.depot_id,
            dep.depot_name as nearest_depot
        FROM users u
        LEFT JOIN students s ON u.id = s.user_id AND u.user_type = 'student'
        LEFT JOIN depots d ON u.id = d.user_id AND u.user_type = 'depot'
        LEFT JOIN depots dep ON s.depot_id = dep.id
        WHERE u.id = :id";

if($stmt = $pdo->prepare($sql)){
    // Bind variables to the prepared statement as parameters
    $param_id = trim($_GET["id"]);
    $user_id = intval($param_id); // Ensure it's an integer
    $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
    
    // Attempt to execute the prepared statement
    if($stmt->execute()){
        if($stmt->rowCount() == 1){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Retrieve individual field value
            $username = $row["username"];
            $user_type = $row["user_type"];
            $status = $row["status"];
            $created_at = $row["created_at"];
            $updated_at = $row["updated_at"] ?? 'Never';
            $full_name = $row["full_name"];
            $email = $row["email"];
            $phone = $row["phone"];
            $address = $row["address"];
            
            // Additional student fields
            $faculty = $row["faculty"] ?? 'Not specified';
            $department = $row["department"] ?? 'Not specified';
            $nearest_depot = $row["nearest_depot"] ?? 'Not assigned';
            
            // Format dates
            $created_date = date('F j, Y', strtotime($created_at));
            $updated_date = $updated_at ? date('F j, Y', strtotime($updated_at)) : 'Never';
            
            // Status badge class
            $status_class = '';
            if($status == 'active'){
                $status_class = 'success';
            } elseif($status == 'pending'){
                $status_class = 'warning';
            } elseif($status == 'suspended'){
                $status_class = 'danger';
            } elseif($status == 'rejected'){
                $status_class = 'secondary';
            }
            
        } else{
            // URL doesn't contain valid id parameter. Redirect to error page
            header("location: error.php");
            exit();
        }
        
    } else{
        echo "Oops! Something went wrong. Please try again later.";
    }
}

// Include admin header
$page_title = 'View User - SEUSL Bus Pass Management System';
include '../includes/admin_header.php';
?>

<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="users.php">Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View User</li>
                </ol>
            </nav>
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                <h1 class="h4 mb-2 mb-md-0">User Details</h1>
                <div class="d-flex flex-wrap gap-2">
                    <a href="users.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Users
                    </a>
                    <?php if($user_type != 'admin' || $_SESSION['id'] == $user_id): ?>
                        <a href="user_edit.php?id=<?php echo htmlspecialchars($user_id); ?>" class="btn btn-sm btn-primary" id="editUserBtn">
                            <i class="fas fa-edit me-1"></i> Edit User
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white py-2">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-3 border-end p-4 text-center d-flex flex-column">
                            <?php 
                            $name_parts = explode(' ', $full_name);
                            $initials = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
                            ?>
                            <div class="mx-auto mb-3">
                                <div class="avatar-xxl bg-soft-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                    <span class="display-5 text-primary fw-bold"><?php echo $initials; ?></span>
                                </div>
                            </div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($full_name); ?></h5>
                            <span class="badge bg-<?php echo $status_class; ?> mb-2">
                                <?php echo ucfirst(htmlspecialchars($status)); ?>
                            </span>
                            <p class="text-muted small mb-0"><?php echo ucfirst(htmlspecialchars($user_type)); ?> Account</p>
                        </div>
                        <div class="col-md-9">
                            <div class="p-4">
                                <div class="row g-3">
                                    <div class="col-sm-6 col-md-4">
                                        <h6 class="text-muted small mb-1">Username</h6>
                                        <p class="mb-0"><?php echo htmlspecialchars($username); ?></p>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <h6 class="text-muted small mb-1">Email Address</h6>
                                        <p class="mb-0 text-truncate"><?php echo htmlspecialchars($email); ?></p>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <h6 class="text-muted small mb-1">Phone Number</h6>
                                        <p class="mb-0"><?php echo htmlspecialchars($phone); ?></p>
                                    </div>
                                    <?php if($user_type == 'student'): ?>
                                    <div class="col-sm-6 col-md-4">
                                        <h6 class="text-muted small mb-1">Faculty</h6>
                                        <p class="mb-0">
                                            <?php 
                                            if (!empty($faculty) && $faculty !== 'Not specified') {
                                                echo htmlspecialchars($faculty);
                                            } else {
                                                echo '<span class="text-muted fst-italic small">Not specified</span>';
                                            }
                                            ?>
                                        </p>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <h6 class="text-muted small mb-1">Department</h6>
                                        <p class="mb-0">
                                            <?php 
                                            if (!empty($department) && $department !== 'Not specified') {
                                                echo htmlspecialchars($department);
                                            } else {
                                                echo '<span class="text-muted fst-italic small">Not specified</span>';
                                            }
                                            ?>
                                        </p>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <h6 class="text-muted small mb-1">Nearest Depot</h6>
                                        <p class="mb-0">
                                            <?php 
                                            if (!empty($nearest_depot) && $nearest_depot !== 'Not assigned') {
                                                echo htmlspecialchars($nearest_depot);
                                            } else {
                                                echo '<span class="text-muted fst-italic small">Not assigned</span>';
                                            }
                                            ?>
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                    <div class="col-12">
                                        <h6 class="text-muted small mb-1">Address</h6>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($address)); ?></p>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <h6 class="text-muted small mb-1">Account Created</h6>
                                        <p class="mb-0"><?php echo $created_date; ?></p>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <h6 class="text-muted small mb-1">Last Updated</h6>
                                        <p class="mb-0"><?php echo $updated_date; ?></p>
                                    </div>
                            </div>
                        </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                        <a href="users.php" class="btn btn-sm btn-outline-secondary w-100 w-sm-auto">
                            <i class="fas fa-arrow-left me-1"></i> Back to Users
                        </a>
                        <div class="d-flex gap-2 w-100 w-sm-auto justify-content-end">
                            <?php if($status == 'active' && $user_type != 'admin'): ?>
                                <a href="#" class="btn btn-sm btn-warning deactivate-btn w-100 w-sm-auto" 
                                   data-bs-toggle="modal" data-bs-target="#deactivateModal" 
                                   data-id="<?php echo $param_id; ?>">
                                    <i class="fas fa-ban me-1"></i> Deactivate
                                </a>
                            <?php elseif($status != 'active'): ?>
                                <a href="users.php?activate_id=<?php echo $param_id; ?>" 
                                   class="btn btn-sm btn-success w-100 w-sm-auto" 
                                   onclick="return confirm('Are you sure you want to activate this user?')">
                                    <i class="fas fa-check me-1"></i> Activate
                                </a>
                            <?php endif; ?>
                            
                            <?php if($user_type != 'admin'): ?>
                                <a href="#" class="btn btn-sm btn-danger delete-btn w-100 w-sm-auto" 
                                   data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                   data-id="<?php echo $param_id; ?>">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user? This action cannot be undone.</p>
                <p class="text-danger"><strong>Warning:</strong> This will permanently remove all data associated with this user.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="user_delete.php" method="post" class="d-inline">
                    <input type="hidden" name="id" id="delete_id" value="">
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Deactivate Confirmation Modal -->
<div class="modal fade" id="deactivateModal" tabindex="-1" aria-labelledby="deactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="deactivateModalLabel">Confirm Deactivation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to deactivate this user account?</p>
                <p class="text-muted">The user will no longer be able to log in until the account is reactivated.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="user_deactivate.php" method="post" class="d-inline">
                    <input type="hidden" name="id" id="deactivate_id" value="">
                    <button type="submit" class="btn btn-warning">Deactivate</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Ensure the edit button works properly
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit button click
    const editBtn = document.getElementById('editUserBtn');
    if (editBtn) {
        editBtn.addEventListener('click', function(e) {
            // Allow the default link behavior
            return true;
        });
    }
    // For delete buttons
    var deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            document.getElementById('delete_id').value = id;
        });
    });
    
    // For deactivate buttons
    var deactivateButtons = document.querySelectorAll('.deactivate-btn');
    deactivateButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            document.getElementById('deactivate_id').value = id;
        });
    });
});
</script>

<?php
// Include admin footer
include '../includes/admin_footer.php';
?>
