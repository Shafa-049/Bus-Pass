<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Check if user is logged in and is admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add_depot') {
        // Validate required fields
        $required_fields = ['depot_name', 'manager_name', 'email', 'phone', 'location', 'address', 'username', 'password'];
        $missing_fields = [];
        $data = [];
        
        foreach ($required_fields as $field) {
            if (empty(trim($_POST[$field] ?? ''))) {
                $missing_fields[] = $field;
            } else {
                $data[$field] = trim($_POST[$field]);
            }
        }
        
        if (!empty($missing_fields)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Please fill in all required fields: ' . implode(', ', $missing_fields)
            ]);
            exit;
        }
        
        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Please enter a valid email address.'
            ]);
            exit;
        }
        
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Use provided username and password
            $username = $data['username'];
            $password = $data['password'];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into users table
            $sql = "INSERT INTO users (username, password, user_type, status) 
                    VALUES (:username, :password, 'depot', 'active')";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'username' => $username,
                'password' => $hashed_password
            ]);
            
            $user_id = $pdo->lastInsertId();
            
            // Insert into depots table
            $sql = "INSERT INTO depots (user_id, depot_name, manager_name, email, phone, address, location) 
                    VALUES (:user_id, :depot_name, :manager_name, :email, :phone, :address, :location)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'depot_name' => $data['depot_name'],
                'manager_name' => $data['manager_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'location' => $data['location']
            ]);
            
            $depot_id = $pdo->lastInsertId();
            
            // Commit the transaction
            $pdo->commit();
            
            // Return success response
            echo json_encode([
                'success' => true,
                'message' => 'Depot added successfully!',
                'depot_id' => $depot_id,
                'depot_name' => $data['depot_name'],
                'location' => $data['location'],
                'username' => $username,
                'password' => $password
            ]);
            
        } catch (PDOException $e) {
            // Rollback the transaction on error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            // Check for duplicate entry error
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'username') !== false) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'A depot with this name already exists. Please choose a different name.'
                    ]);
                } elseif (strpos($e->getMessage(), 'email') !== false) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'This email is already registered. Please use a different email.'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'A duplicate entry was found. Please check your input.'
                    ]);
                }
            } else {
                // Log the error for debugging
                error_log('Database error: ' . $e->getMessage());
                
                echo json_encode([
                    'success' => false, 
                    'message' => 'An error occurred while adding the depot. Please try again.'
                ]);
            }
        }
        
        exit;
    }
}

// If no valid action was provided
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
