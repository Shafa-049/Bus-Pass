<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Check if user is logged in and is a depot user
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'depot') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if schedule ID is provided
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid schedule ID']);
    exit;
}

$schedule_id = $_POST['id'];
$depot_id = $_SESSION['depot_id'] ?? 0;

try {
    // First, verify that the schedule belongs to this depot
    $verify_sql = "SELECT bs.id 
                  FROM bus_schedules bs
                  JOIN routes r ON bs.route_id = r.id
                  JOIN depot_routes dr ON r.id = dr.route_id
                  WHERE bs.id = ? AND dr.depot_id = ?";
    
    $stmt = $pdo->prepare($verify_sql);
    $stmt->execute([$schedule_id, $depot_id]);
    
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Schedule not found or access denied']);
        exit;
    }
    
    // Delete the schedule
    $delete_sql = "DELETE FROM bus_schedules WHERE id = ?";
    $stmt = $pdo->prepare($delete_sql);
    $result = $stmt->execute([$schedule_id]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Schedule deleted successfully']);
    } else {
        throw new Exception('Failed to delete schedule');
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
