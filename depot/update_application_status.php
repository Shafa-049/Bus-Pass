<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Set header to return JSON
header('Content-Type: application/json');

// Function to send JSON response and exit
function sendResponse($success, $message, $data = []) {
    $response = array_merge(['success' => $success, 'message' => $message], $data);
    
    // Clear any previous output
    if (ob_get_level() > 0) {
        ob_clean();
    }
    
    // Set JSON content type
    header('Content-Type: application/json');
    
    // Send JSON response and exit
    echo json_encode($response);
    exit;
}

// Check if user is logged in and is a depot user
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'depot') {
    sendResponse(false, 'Unauthorized access');
}

// Check if required parameters are provided
if ($_SERVER["REQUEST_METHOD"] !== 'POST' || !isset($_POST['application_id'], $_POST['status'])) {
    sendResponse(false, 'Invalid request');
}

$application_id = (int)$_POST['application_id'];
$status = $_POST['status'];
$depot_id = $_SESSION['depot_id'] ?? 0;
$pass_number = trim($_POST['pass_number'] ?? '');
$rejection_reason = trim($_POST['rejection_reason'] ?? '');

// Map status to valid database values according to bus_passes.status enum
$status_map = [
    'approved' => 'active',
    'rejected' => 'rejected',
    'pending_approval' => 'pending_approval',
    'active' => 'active',
    'expired' => 'expired',
    'cancelled' => 'cancelled'
];

if (!isset($status_map[$status])) {
    sendResponse(false, 'Invalid status');
}

// Get the database status value
$db_status = $status_map[$status];

// Check if rejection reason is provided for rejected applications
if ($status === 'rejected' && empty($rejection_reason)) {
    sendResponse(false, 'Please provide a reason for rejection');
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // 1. First, verify the application exists and get current status
    $check_sql = "SELECT bp.*, s.user_id 
                 FROM bus_passes bp 
                 JOIN students s ON bp.student_id = s.id 
                 WHERE bp.id = ? AND bp.depot_id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$application_id, $depot_id]);
    $application = $check_stmt->fetch();
    
    if (!$application) {
        throw new Exception('Application not found or access denied');
    }
    
    // For new approvals, validate and set pass number
    if ($status === 'approved') {
        if (empty($pass_number)) {
            throw new Exception('Pass number is required for approval');
        }
        
        // Check if pass number already exists
        $check_pass_sql = "SELECT id FROM bus_passes WHERE pass_number = ? AND id != ?";
        $check_pass_stmt = $pdo->prepare($check_pass_sql);
        $check_pass_stmt->execute([$pass_number, $application_id]);
        
        if ($check_pass_stmt->fetch()) {
            throw new Exception('This pass number is already in use. Please choose a different one.');
        }
    }
    
    // 2. Update the application status and other fields
    $sql = "UPDATE bus_passes SET 
            status = :status,
            updated_at = NOW()";
    
    $params = [
        ':status' => $db_status,
        ':id' => $application_id,
        ':depot_id' => $depot_id
    ];
    
    // Add pass number for approval
    if ($status === 'approved') {
        $sql .= ", pass_number = :pass_number";
        $params[':pass_number'] = $pass_number;
    }
    
    // Set issue date and expiry date for approved applications
    if ($status === 'approved') {
        $sql .= ", issue_date = CURDATE(), 
                expiry_date = DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    }
    
    // Add rejection reason for rejected applications
    if ($status === 'rejected') {
        $sql .= ", payment_reference = :rejection_reason";
        $params[':rejection_reason'] = $rejection_reason;
    }
    
    $sql .= " WHERE id = :id AND depot_id = :depot_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // 3. Create a notification for the student
    if (isset($application['user_id'])) {
        $title = 'Application ' . ucfirst($status);
        $message = 'Your bus pass application ' . ($pass_number ?: '') . ' has been ' . $status;
        
        if ($status === 'rejected' && !empty($rejection_reason)) {
            $message .= '. Reason: ' . $rejection_reason;
        }
        
        $notif_sql = "INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)";
        $notif_stmt = $pdo->prepare($notif_sql);
        $notif_stmt->execute([$application['user_id'], $title, $message]);
    }
    
    // Commit the transaction
    $pdo->commit();
    
    // Return success response
    sendResponse(true, 'Application status updated successfully', [
        'status' => $db_status,
        'application_id' => $application_id,
        'pass_number' => $pass_number
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Error updating application status: ' . $e->getMessage());
    sendResponse(false, 'Error updating application: ' . $e->getMessage());
}
