<?php
// Start session with more secure settings
session_start([
    'cookie_lifetime' => 86400, // 24 minutes
    'read_and_close'  => false,
    'cookie_httponly' => 1,
    'cookie_secure'   => isset($_SERVER['HTTPS']),
    'use_strict_mode' => 1
]);

// Include config file
require_once '../config/database.php';

// Debug: Log session status
error_log("Session started. ID: " . session_id() . ", Status: " . session_status());

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Check if the user is already logged in, only if this is not a POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST" && isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($_SESSION["user_type"])) {
    switch($_SESSION["user_type"]) {
        case "student":
            header("Location: ../student/dashboard.php");
            break;
        case "depot":
            header("Location: ../depot/dashboard.php");
            break;
        case "admin":
            header("Location: ../admin/dashboard.php");
            break;
        default:
            // Invalid user type, destroy session and show error
            session_destroy();
            $login_err = "Invalid user type. Please login again.";
            break;
    }
    exit();
}

// Processing form data when form is submitted
// Debug: Log POST data at the start of processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("Login attempt - POST data: " . print_r($_POST, true));
    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement - First get user info, then get depot info if needed
        $sql = "SELECT u.id, u.username, u.password, u.user_type, u.status
                FROM users u
                WHERE u.username = :username";
        
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            
            // Set parameters
            $param_username = trim($_POST["username"]);
            
            // Attempt to execute the prepared statement
            error_log("Executing query with username: " . $param_username);
            if($stmt->execute()){
                // Check if username exists, if yes then verify password
                $rowCount = $stmt->rowCount();
                error_log("Number of rows found: " . $rowCount);
                if($rowCount == 1){
                    if($row = $stmt->fetch()){
                        $id = $row["id"];
                        $username = $row["username"];
                        $hashed_password = $row["password"];
                        $user_type = $row["user_type"];
                        $status = $row["status"];
                        
                        // Get depot_id only if user_type is 'depot'
                        $depot_id = null;
                        if($user_type === 'depot') {
                            $depot_sql = "SELECT id FROM depots WHERE user_id = :user_id LIMIT 1";
                            $depot_stmt = $pdo->prepare($depot_sql);
                            $depot_stmt->bindParam(":user_id", $id, PDO::PARAM_INT);
                            if($depot_stmt->execute()) {
                                $depot_row = $depot_stmt->fetch();
                                $depot_id = $depot_row ? $depot_row["id"] : null;
                            }
                        }
                        
                        // Debug: Log what we found
                        error_log("Found user: " . print_r([
                            'id' => $id,
                            'username' => $username,
                            'user_type' => $user_type,
                            'status' => $status,
                            'password_hash' => substr($hashed_password, 0, 10) . '...' // Show first 10 chars of hash
                        ], true));
                        
                        error_log("Attempting to verify password for user: $username");
                        $passwordMatch = password_verify($password, $hashed_password);
                        error_log("Password verification result: " . ($passwordMatch ? 'MATCH' : 'NO MATCH'));
                        
                        if($passwordMatch){
                            error_log("Password verified successfully for user: $username");
                            error_log("Password verified for user: $username");
                            // Check if account is active
                            error_log("User status check - Username: $username, Status: $status, Type: $user_type");
                            error_log("User status check - Username: $username, Status: $status, Type: $user_type");
                            if($status === 'active'){
                                error_log("User is active, setting session variables");
                                // Store data in session variables
                                $_SESSION = []; // Clear existing session data
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["username"] = $username;
                                $_SESSION["user_type"] = $user_type;
                                
                                if ($user_type === 'depot' && !empty($depot_id)) {
                                    $_SESSION["depot_id"] = $depot_id;
                                }
                                
                                // Regenerate session ID to prevent session fixation
                                session_regenerate_id(true);
                                
                                error_log("Session data set - " . print_r($_SESSION, true));
                                // Log successful login
                                error_log("Login successful - User: $username, Type: $user_type");
                                // Debug: Verify session data before redirect
                                error_log("Session data before redirect - " . print_r($_SESSION, true));
                                error_log("Session ID before redirect: " . session_id());
                                // Ensure no output has been sent before headers
                                if (headers_sent($file, $line)) {
                                    error_log("Headers already sent in $file on line $line");
                                    die("Redirect failed. Please try again.");
                                }
                                // Redirect based on user type with absolute URL
                                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                                $host = $_SERVER['HTTP_HOST'];
                                $base_path = '/bus_pass_seusl';
                                
                                switch($user_type) {
                                    case 'student':
                                        $redirect_url = $protocol . $host . $base_path . '/student/dashboard.php';
                                        break;
                                    case 'depot':
                                        $redirect_url = $protocol . $host . $base_path . '/depot/dashboard.php';
                                        break;
                                    case 'admin':
                                        $redirect_url = $protocol . $host . $base_path . '/admin/dashboard.php';
                                        break;
                                    default:
                                        $login_err = "Invalid user type. Please contact support.";
                                        error_log("Login failed - Invalid user type: $user_type");
                                        break;
                                }
                                
                                // Perform the redirect
                                if (!empty($redirect_url)) {
                                    error_log("Redirecting to: $redirect_url");
                                    header("Location: $redirect_url");
                                    exit();
                                }
                                exit();
                            } else {
                                $login_err = "Your account is " . htmlspecialchars($status) . ". Please contact the administrator.";
                                error_log("Login failed - Account not active - User: $username, Status: $status");
                                error_log("Session data at error: " . print_r($_SESSION, true));
                            }
                        } else {
                            // Password is not valid, display a generic error message
                            error_log("Login failed - Invalid password for user: $username");
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    // Username doesn't exist, display a generic error message
                    error_log("Login failed - Username not found: " . $param_username);
                    $login_err = "Invalid username or password.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            unset($stmt);
        }
    }
    // Close connection
    unset($pdo);
}

// Include header
$page_title = 'Login - SEUSL Bus Pass Management System';
include '../includes/header.php';
?>

<main class="py-5" style="min-height: calc(100vh - 200px);">
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-bus"></i>
                <h2>Welcome Back</h2>
                <p>Sign in to continue to SEUSL Bus Pass</p>
            </div>
            
            <div class="login-form">
                <?php 
                if(!empty($login_err)){
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                    echo '<i class="fas fa-exclamation-circle me-2"></i>' . $login_err;
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    echo '</div>';
                }        
                ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($username); ?>" 
                                   placeholder="Enter your username"
                                   autocomplete="username"
                                   required>
                        </div>
                        <?php if (!empty($username_err)): ?>
                            <div class="invalid-feedback d-block">
                                <i class="fas fa-exclamation-circle me-1"></i> <?php echo $username_err; ?>
                            </div>
                        <?php endif; ?>
                    </div>    
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" name="password" 
                                   class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                                   placeholder="Enter your password"
                                   autocomplete="current-password"
                                   required>
                        </div>
                        <?php if (!empty($password_err)): ?>
                            <div class="invalid-feedback d-block">
                                <i class="fas fa-exclamation-circle me-1"></i> <?php echo $password_err; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                        <div class="forgot-password">
                            <a href="#">Forgot password?</a>
                        </div>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i> Sign In
                        </button>
                    </div>
                    
                    <div class="register-link">
                        Don't have an account? <a href="register.php">Create one</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<style>
    .login-container {
        max-width: 450px;
        margin: 2rem auto;
        padding: 0;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 128, 128, 0.15);
    }
    .login-header {
        text-align: center;
        padding: 2.5rem 2rem;
        background: linear-gradient(135deg, #008080 0%, #006666 100%);
        color: white;
        margin-bottom: 0;
    }
    .login-header i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: white;
        background: rgba(255, 255, 255, 0.2);
        width: 80px;
        height: 80px;
        line-height: 80px;
        border-radius: 50%;
    }
    .login-header h2 {
        color: white;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .login-header p {
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 0;
    }
    .login-form {
        padding: 2.5rem 2rem;
    }
    .form-label {
        font-weight: 500;
        color: #444;
        margin-bottom: 0.5rem;
    }
    .form-control {
        padding: 0.75rem 1rem;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .form-control:focus {
        border-color: #008080;
        box-shadow: 0 0 0 0.25rem rgba(0, 128, 128, 0.15);
    }
    .btn-login {
        background: #008080;
        color: white;
        border: none;
        padding: 0.75rem;
        font-weight: 600;
        border-radius: 8px;
        width: 100%;
        font-size: 1.05rem;
        letter-spacing: 0.5px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-transform: uppercase;
    }
    .btn-login:hover {
        background: #006666;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 128, 128, 0.3);
    }
    .btn-login:active {
        transform: translateY(0);
    }
    .forgot-password {
        text-align: right;
        margin: -0.5rem 0 1.5rem;
    }
    .forgot-password a {
        color: #666;
        text-decoration: none;
        font-size: 0.9rem;
        transition: color 0.2s ease;
    }
    .forgot-password a:hover {
        color: #008080;
        text-decoration: underline;
    }
    .register-link {
        text-align: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #eee;
        color: #666;
    }
    .register-link a {
        color: #008080;
        font-weight: 600;
        text-decoration: none;
        margin-left: 0.25rem;
        transition: all 0.2s ease;
    }
    .register-link a:hover {
        color: #006666;
        text-decoration: underline;
    }
    .input-group-text {
        background-color: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-right: none;
        border-radius: 8px 0 0 8px;
    }
    .form-control.is-invalid, .was-validated .form-control:invalid {
        border-left: none;
        padding-left: 0.75rem;
    }
    .invalid-feedback {
        font-size: 0.85rem;
        margin-top: 0.25rem;
    }
    .alert {
        border-radius: 8px;
        padding: 0.75rem 1.25rem;
    }
    .alert-dismissible .btn-close {
        padding: 0.75rem 1.25rem;
    }
</style>

<?php
// Include footer
include '../includes/footer.php';
?>

<!-- Close the main content div and body/html tags if not already closed in footer.php -->
</div>
</body>
</html>
