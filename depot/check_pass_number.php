<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(['available' => false, 'message' => 'Not authenticated']);
    exit;
}

if (!isset($_GET['pass_number'])) {
    http_response_code(400);
    echo json_encode(['available' => false, 'message' => 'Pass number is required']);
    exit;
}

$pass_number = trim($_GET['pass_number']);

// Validate pass number format
if (!preg_match('/^[A-Z0-9]{6,20}$/i', $pass_number)) {
    echo json_encode([
        'available' => false, 
        'message' => 'Invalid format. Use 6-20 alphanumeric characters.'
    ]);
    exit;
}

try {
    // Check if pass number exists
    $sql = "SELECT id FROM bus_passes WHERE pass_number = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pass_number]);
    
    $is_available = $stmt->rowCount() === 0;
    
    echo json_encode([
        'available' => $is_available,
        'message' => $is_available ? 'Pass number is available' : 'Pass number is already in use'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'available' => false, 
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
