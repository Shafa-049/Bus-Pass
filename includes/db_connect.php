<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$db_host = 'localhost';     // Database host
$db_name = 'bus_pass_seusl'; // Database name
$db_user = 'root';          // Database username
$db_pass = '';              // Database password

// Set PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create PDO instance
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // Log the error and display a user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    die("Connection failed. Please try again later or contact the administrator.");
}

// Include authentication functions
require_once __DIR__ . '/auth.php';
?>
