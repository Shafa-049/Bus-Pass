<?php
// Initialize the session
session_start();
 
// Check if the user is logged in and is admin, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'admin'){
    header("location: ../auth/login.php");
    exit;
}

// Include database connection
require_once '../config/database.php';

// Check if ID parameter exists
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    // Get the user ID
    $id = trim($_GET["id"]);
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // First, get user type to determine which related tables to update
        $sql = "SELECT user_type FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        if($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_type = $user['user_type'];
            
            // Delete from related tables based on user type
            if($user_type === 'student') {
                // Delete from students table
                $sql = "DELETE FROM students WHERE user_id = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":user_id", $id, PDO::PARAM_INT);
                $stmt->execute();
                
                // Delete any related records in other tables (add more as needed)
                // Example: $pdo->prepare("DELETE FROM some_other_table WHERE user_id = :user_id")->execute([":user_id" => $id]);
                
            } elseif($user_type === 'depot') {
                // Delete from depots table
                $sql = "DELETE FROM depots WHERE user_id = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":user_id", $id, PDO::PARAM_INT);
                $stmt->execute();
                
                // Delete any related records in other tables
                // Example: $pdo->prepare("DELETE FROM depot_schedules WHERE depot_id = (SELECT id FROM depots WHERE user_id = :user_id)")->execute([":user_id" => $id]);
            }
            
            // Finally, delete the user from users table
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Commit transaction
            $pdo->commit();
            
            // Set success message
            $_SESSION['success_message'] = "User has been deleted successfully.";
            
        } else {
            // User not found
            $_SESSION['error_message'] = "User not found.";
        }
        
    } catch(PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error deleting user: " . $e->getMessage();
    }
    
    // Close statement
    unset($stmt);
    
} else {
    // ID parameter is missing
    $_SESSION['error_message'] = "Invalid request.";
}

// Close connection
unset($pdo);

// Redirect back to users page
header("location: users.php");
exit;
?>
