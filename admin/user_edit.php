<?php
// Initialize the session
session_start();
 
// Check if the user is logged in and is admin, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'admin'){
    header("location: ../auth/login.php");
    exit();
}

// Include database connection
require_once '../config/database.php';

// Define variables and initialize with empty values
$error_message = "";
$success_message = "";

// Check if ID parameter exists
if(empty(trim($_GET["id"]))){
    // URL doesn't contain id parameter. Redirect to error page
    header("location: error.php");
    exit();
}

// Get user ID from URL parameter
$user_id = trim($_GET["id"]);
$user_data = [];
$user_type = '';

// Process form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Initialize variables with POST data
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $status = 'active'; // Always set status to active since we removed the toggle
    $password = trim($_POST["password"] ?? '');
    $confirm_password = trim($_POST["confirm_password"] ?? '');
    $full_name = trim($_POST["full_name"] ?? '');
    $faculty = trim($_POST["faculty"] ?? '');
    $department = trim($_POST["department"] ?? '');
    
    // Validate input
    if(empty($username)){
        $error_message = "Please enter a username.";
    } elseif(empty($email)){
        $error_message = "Please enter an email address.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error_message = "Please enter a valid email address.";
    } elseif(!empty($password) && (strlen($password) < 6)){
        $error_message = "Password must have at least 6 characters.";
    } elseif(!empty($password) && ($password != $confirm_password)){
        $error_message = "Passwords do not match.";
    } else {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Update users table
            if(!empty($password)){
                // Update with new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET username = :username, status = :status, password = :password, updated_at = NOW() WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
            } else {
                // Update without changing password
                $sql = "UPDATE users SET username = :username, status = :status, updated_at = NOW() WHERE id = :id";
                $stmt = $pdo->prepare($sql);
            }
            
            // Bind parameters
            $stmt->bindParam(":username", $username, PDO::PARAM_STR);
            $stmt->bindParam(":status", $status, PDO::PARAM_STR);
            $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Update user type specific tables
            if($user_type == 'student'){
                // Check if student record exists
                $check_sql = "SELECT id FROM students WHERE user_id = :user_id";
                $check_stmt = $pdo->prepare($check_sql);
                $check_stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                $check_stmt->execute();
                
                if($check_stmt->rowCount() > 0){
                    // Update existing student record
                    $update_sql = "UPDATE students SET full_name = :full_name, email = :email, faculty = :faculty, department = :department, updated_at = NOW() WHERE user_id = :user_id";
                } else {
                    // Insert new student record with default values for required fields
                    $update_sql = "INSERT INTO students (user_id, full_name, email, faculty, department, registration_no, phone, address, depot_id, created_at, updated_at) 
                                  VALUES (:user_id, :full_name, :email, :faculty, :department, 'TEMP_" . uniqid() . "', '0000000000', 'Not specified', 1, NOW(), NOW())";
                }
                
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                $update_stmt->bindParam(":full_name", $full_name, PDO::PARAM_STR);
                $update_stmt->bindParam(":email", $email, PDO::PARAM_STR);
                $update_stmt->bindParam(":faculty", $faculty, PDO::PARAM_STR);
                $update_stmt->bindParam(":department", $department, PDO::PARAM_STR);
                $update_stmt->execute();
            } elseif($user_type == 'depot') {
                // Handle depot users
                $check_sql = "SELECT id FROM depots WHERE user_id = :user_id";
                $check_stmt = $pdo->prepare($check_sql);
                $check_stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                $check_stmt->execute();
                
                if($check_stmt->rowCount() > 0) {
                    // Update existing depot record
                    $update_sql = "UPDATE depots SET email = :email, updated_at = NOW() WHERE user_id = :user_id";
                    $update_stmt = $pdo->prepare($update_sql);
                    $update_stmt->bindParam(":email", $email, PDO::PARAM_STR);
                    $update_stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                    $update_stmt->execute();
                }
            }
            
            // Commit transaction
            $pdo->commit();
            
            // Set success message and refresh user data
            $success_message = "User information updated successfully!";
            
            // Refresh user data
            $sql = "SELECT 
                        u.id, 
                        u.username, 
                        u.user_type, 
                        u.status,
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
                        s.faculty,
                        s.department,
                        s.depot_id
                    FROM users u
                    LEFT JOIN students s ON u.id = s.user_id AND u.user_type = 'student'
                    LEFT JOIN depots d ON u.id = d.user_id AND u.user_type = 'depot'
                    WHERE u.id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            $error_message = "Error updating user: " . $e->getMessage();
        }
    }
}

// User data is already fetched in the form processing section

try {
    // Prepare a select statement
    $sql = "SELECT 
                u.id, 
                u.username, 
                u.user_type, 
                u.status,
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
                s.faculty,
                s.department,
                s.depot_id
            FROM users u
            LEFT JOIN students s ON u.id = s.user_id AND u.user_type = 'student'
            LEFT JOIN depots d ON u.id = d.user_id AND u.user_type = 'depot'
            WHERE u.id = :id";
    
    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
        
        // Attempt to execute the prepared statement
        if($stmt->execute()){
            if($stmt->rowCount() == 1){
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                $user_type = $user_data['user_type'];
            } else{
                // URL doesn't contain valid id parameter. Redirect to error page
                header("location: error.php");
                exit();
            }
        } else{
            $error_message = "Oops! Something went wrong. Please try again later.";
        }
    }
    
    // Close statement
    unset($stmt);
    
} catch(PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}

// Include admin header
$page_title = 'Edit User - SEUSL Bus Pass Management System';
include '../includes/admin_header.php';
?>

<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="users.php">Users</a></li>
                    <li class="breadcrumb-item"><a href="user_view.php?id=<?php echo $user_id; ?>">View User</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit User</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h4 mb-0">Edit User</h1>
                <div>
                    <a href="user_view.php?id=<?php echo $user_id; ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" form="editUserForm" class="btn btn-sm btn-primary">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </div>
            
            <?php 
            // Display error message if any
            if(!empty($error_message)){
                echo '<div class="alert alert-danger">' . $error_message . '</div>';
            }            
            // Display success message if any
            if(!empty($success_message)){
                echo '<div class="alert alert-success">' . $success_message . '</div>';
            }
            ?>
            
            <div class="card shadow-sm">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $user_id; ?>" method="post" id="editUserForm">
                    <div class="card-header bg-primary text-white py-2">
                        <h5 class="mb-0">User Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <?php if($user_type == 'student'): ?>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="faculty" class="form-label">Faculty</label>
                                    <input type="text" class="form-control" id="faculty" name="faculty" 
                                           value="<?php echo htmlspecialchars($user_data['faculty'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="department" class="form-label">Department</label>
                                    <input type="text" class="form-control" id="department" name="department" 
                                           value="<?php echo htmlspecialchars($user_data['department'] ?? ''); ?>">
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-1"></i> 
                                    Leave password fields blank to keep the current password.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include admin footer
include '../includes/admin_footer.php';
?>
