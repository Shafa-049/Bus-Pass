<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect to dashboard
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    if($_SESSION["user_type"] === "student"){
        header("location: ../student/dashboard.php");
    } elseif($_SESSION["user_type"] === "depot") {
        header("location: ../depot/dashboard.php");
    } elseif($_SESSION["user_type"] === "admin") {
        header("location: ../admin/dashboard.php");
    }
    exit;
}

// Include config file
require_once '../config/database.php';

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
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
        // Prepare a select statement
        $sql = "SELECT id, username, password, user_type, status FROM users WHERE username = :username";
        
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            
            // Set parameters
            $param_username = trim($_POST["username"]);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Check if username exists, if yes then verify password
                if($stmt->rowCount() == 1){                    
                    if($row = $stmt->fetch()){
                        $id = $row["id"];
                        $username = $row["username"];
                        $hashed_password = $row["password"];
                        $user_type = $row["user_type"];
                        $status = $row["status"];
                        
                        // Check account status
                        if($status !== 'active'){
                            $login_err = "Your account is not active. Please contact the administrator.";
                        } elseif(password_verify($password, $hashed_password)){
                            // Password is correct, so start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;                            
                            $_SESSION["user_type"] = $user_type;
                            
                            // Redirect user to appropriate dashboard
                            if($user_type === "student"){
                                header("location: ../student/dashboard.php");
                            } elseif($user_type === "depot") {
                                header("location: ../depot/dashboard.php");
                            } elseif($user_type === "admin") {
                                header("location: ../admin/dashboard.php");
                            }
                        } else{
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    // Username doesn't exist, display a generic error message
                    $login_err = "Invalid username or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            unset($stmt);
        }
    }
    
    // Close connection
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SEUSL Bus Pass</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            background: white;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header i {
            font-size: 3rem;
            color: var(--teal);
            margin-bottom: 15px;
        }
        .form-control:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 0.2rem rgba(0, 128, 128, 0.25);
        }
        .btn-login {
            background-color: var(--teal);
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background-color: var(--teal-dark);
            color: white;
            transform: translateY(-2px);
        }
        .register-link {
            color: var(--teal);
            text-decoration: none;
            font-weight: 600;
        }
        .register-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body style="background-color: #f8f9fa;">
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-bus"></i>
                <h2>SEUSL Bus Pass</h2>
                <p class="text-muted">Sign in to your account</p>
            </div>
            
            <?php 
            if(!empty($login_err)){
                echo '<div class="alert alert-danger">' . $login_err . '</div>';
            }        
            ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>    
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-login">Sign In</button>
                </div>
                <div class="text-center">
                    <p class="mb-0">Don't have an account? <a href="register.php" class="register-link">Register here</a></p>
                    <p class="mt-2"><a href="forgot-password.php" class="text-muted">Forgot password?</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
