<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Check if user is logged in and is a depot user
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'depot') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get the schedule ID and status from the request
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

if (!$id || !in_array($status, ['active', 'inactive'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    // Update the schedule status
    $stmt = $pdo->prepare("UPDATE bus_schedules SET status = ? WHERE id = ? AND depot_id = ?");
    $result = $stmt->execute([$status, $id, $_SESSION['depot_id']]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Schedule not found or no changes made']);
    }
} catch (PDOException $e) {
    error_log('Error updating schedule status: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
