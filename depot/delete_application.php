<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Check if user is logged in and is a depot user
requireLogin();
requireRole('depot');

header('Content-Type: application/json');

// Check if this is a POST request with _method=DELETE
$isDeleteRequest = ($_SERVER['REQUEST_METHOD'] === 'POST' && 
                   (isset($_POST['_method']) && strtoupper($_POST['_method']) === 'DELETE'));

if (!$isDeleteRequest) {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the application ID
$application_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get depot_id from session or query
if (isset($_SESSION['depot_id'])) {
    $depot_id = $_SESSION['depot_id'];
} else if (isset($_SESSION['selected_depot_id'])) {
    $depot_id = $_SESSION['selected_depot_id'];
} else {
    // If still not set, get the first depot for this user
    $stmt = $pdo->prepare("SELECT id FROM depots WHERE user_id = ? LIMIT 1");
    $stmt->execute([$_SESSION['id']]);
    $depot = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($depot) {
        $depot_id = $depot['id'];
        $_SESSION['depot_id'] = $depot_id;
    } else {
        http_response_code(403); // Forbidden
        echo json_encode(['success' => false, 'message' => 'No depot assigned to your account']);
        exit;
    }
}

if ($application_id <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Invalid application ID']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // First, verify the application exists and belongs to the depot
    $stmt = $pdo->prepare("SELECT id, status FROM bus_passes WHERE id = ? AND depot_id = ?");
    $stmt->execute([$application_id, $depot_id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        throw new Exception('Application not found or access denied');
    }
    
    // Only allow deletion of pending applications
    if ($application['status'] !== 'pending') {
        throw new Exception('Only pending applications can be deleted');
    }
    
    // Delete the application
    $stmt = $pdo->prepare("DELETE FROM bus_passes WHERE id = ? AND depot_id = ?");
    $deleted = $stmt->execute([$application_id, $depot_id]);
    
    if (!$deleted) {
        throw new Exception('Failed to delete application');
    }
    
    // Log the action
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details) 
        VALUES (?, 'delete', 'bus_pass', ?, ?)
    ");
    $details = json_encode([
        'application_id' => $application_id,
        'deleted_at' => date('Y-m-d H:i:s')
    ]);
    $stmt->execute([$_SESSION['user_id'], $application_id, $details]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Application deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
