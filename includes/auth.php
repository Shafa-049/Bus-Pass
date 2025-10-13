<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'db_connect.php';

/**
 * Check if user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && 
           isset($_SESSION['id']) && !empty($_SESSION['id']);
}

/**
 * Check if user is an admin
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

/**
 * Check if user is depot staff
 * @return bool True if user is depot staff, false otherwise
 */
function isDepotStaff() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'depot';
}

/**
 * Check if user is a student
 * @return bool True if user is a student, false otherwise
 */
function isStudent() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student';
}

/**
 * Redirect to login if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: /auth/login.php');
        exit();
    }
}

/**
 * Redirect to dashboard if already logged in
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        $redirect = match($_SESSION['user_type']) {
            'admin' => '/admin/dashboard.php',
            'depot' => '/depot/dashboard.php',
            'student' => '/student/dashboard.php',
            default => '/index.php'
        };
        header("Location: $redirect");
        exit();
    }
}

/**
 * Check if user has specific role(s)
 * @param string|array $allowed_roles Single role or array of roles
 */
function requireRole($allowed_roles) {
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    
    if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], $allowed_roles)) {
        header('HTTP/1.1 403 Forbidden');
        include($_SERVER['DOCUMENT_ROOT'] . '/403.php');
        exit();
    }
}

/**
 * Log out the current user
 */
function logout() {
    // Unset all session variables
    $_SESSION = array();
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header('Location: /auth/login.php');
    exit();
}

/**
 * Authenticate user
 * @param string $username Username or email
 * @param string $password Password
 * @return array|bool User data if authenticated, false otherwise
 */
function authenticate($username, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1");
        $stmt->execute([':username' => $username, ':email' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login time
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
            $updateStmt->execute([':id' => $user['id']]);
            
            // Remove password from user data
            unset($user['password']);
            return $user;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Authentication error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if username or email already exists
 * @param string $username Username to check
 * @param string $email Email to check
 * @return string|bool Error message if exists, false otherwise
 */
function userExists($username, $email) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT username, email FROM users WHERE username = :username OR email = :email LIMIT 1");
        $stmt->execute([':username' => $username, ':email' => $email]);
        $user = $stmt->fetch();
        
        if ($user) {
            if ($user['username'] === $username) {
                return 'Username already exists';
            }
            if ($user['email'] === $email) {
                return 'Email already exists';
            }
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("User exists check error: " . $e->getMessage());
        return 'An error occurred. Please try again.';
    }
}
?>
