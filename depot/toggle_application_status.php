<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Check if user is logged in and is a depot user
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'depot') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if required parameters are provided
if (!isset($_POST['application_id']) || !isset($_POST['is_active'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$application_id = (int)$_POST['application_id'];
$is_active = (int)$_POST['is_active'];

// Get depot_id from session
$depot_id = $_SESSION['depot_id'] ?? null;
if (!$depot_id && isset($_SESSION['selected_depot_id'])) {
    $depot_id = $_SESSION['selected_depot_id'];
}

if (!$depot_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Depot not assigned']);
    exit;
}

try {
    // Verify that the application belongs to the depot
    $stmt = $pdo->prepare("SELECT id FROM bus_passes WHERE id = ? AND depot_id = ?");
    $stmt->execute([$application_id, $depot_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Application not found or access denied');
    }
    
    // Update the is_active status
    $stmt = $pdo->prepare("UPDATE bus_passes SET is_active = ? WHERE id = ?");
    $stmt->execute([$is_active, $application_id]);
    
    // Log the action
    $action = $is_active ? 'activated' : 'deactivated';
    $log_message = "Application #$application_id was $action by depot user " . ($_SESSION['username'] ?? 'unknown');
    error_log($log_message);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => "Application $action successfully",
        'is_active' => $is_active
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("Error toggling application status: " . $e->getMessage());
    
    // Return error response
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update application status: ' . $e->getMessage()
    ]);
}
