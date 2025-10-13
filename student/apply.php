<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'student'){
    header("location: ../auth/login.php");
    exit;
}

// Include config file
require_once '../config/database.php';

// Set page title
$page_title = 'Apply for Bus Pass - SEUSL Bus Pass Management System';

// Include student header
include '../includes/student_header.php';

// Get student details
$student_id = $_SESSION["id"];
$student = [];
$routes = [];
$error = '';
$success = '';

// Get student details
try {
    $sql = "SELECT * FROM students WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":user_id", $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$student) {
        throw new Exception("Student record not found.");
    }
    
    // Get available routes
    $sql = "SELECT r.*, 'Main Depot' as depot_name, 1 as depot_id
            FROM routes r 
            WHERE r.status = 'active'
            ORDER BY r.start_point, r.end_point";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
} catch(Exception $e) {
    $error = $e->getMessage();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form data
    if(empty(trim($_POST["route_id"]))) {
        $error = "Please select a route.";
    } else {
        $route_id = trim($_POST["route_id"]);
        
        // Check if student already has an active pass for this route
        $sql = "SELECT id FROM bus_passes 
                WHERE student_id = :student_id 
                AND route_id = :route_id 
                AND status = 'active' 
                AND expiry_date >= CURDATE()";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":student_id", $student['id'], PDO::PARAM_INT);
        $stmt->bindParam(":route_id", $route_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $error = "You already have an active pass for this route.";
        } else {
            // First, get the route details
            $sql = "SELECT * FROM routes WHERE id = :route_id AND status = 'active' LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":route_id", $route_id, PDO::PARAM_INT);
            $stmt->execute();
            $route = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($route) {
                // First, try to find the depot for this route from bus_schedules
                $sql = "SELECT DISTINCT d.id as depot_id, d.depot_name 
                        FROM bus_schedules bs
                        JOIN depots d ON bs.depot_id = d.id 
                        WHERE bs.route_id = :route_id 
                        AND d.status = 'active'
                        LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":route_id", $route_id, PDO::PARAM_INT);
                $stmt->execute();
                $depot = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($depot) {
                    // If we found a depot for this route, use it
                    $route['depot_id'] = $depot['depot_id'];
                    $route['depot_name'] = $depot['depot_name'];
                } else {
                    // If no specific depot found for this route, use a default depot
                    // First try to find Kalmunai depot
                    $sql = "SELECT id as depot_id, depot_name 
                            FROM depots 
                            WHERE LOWER(depot_name) LIKE '%kalmunai%' 
                            AND status = 'active' 
                            LIMIT 1";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $depot = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($depot) {
                        $route['depot_id'] = $depot['depot_id'];
                        $route['depot_name'] = $depot['depot_name'];
                    } else {
                        // Fallback to any active depot
                        $sql = "SELECT id as depot_id, depot_name 
                                FROM depots 
                                WHERE status = 'active' 
                                LIMIT 1";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        $depot = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($depot) {
                            $route['depot_id'] = $depot['depot_id'];
                            $route['depot_name'] = $depot['depot_name'];
                        } else {
                            // Last resort fallback
                            $route['depot_id'] = 1;
                            $route['depot_name'] = 'Main Depot';
                        }
                    }
                }
                
                if (isset($route) && $route) {
                    // Calculate expiry date (1 month from now)
                    $expiry_date = date('Y-m-d', strtotime('+1 month'));
                
                    // Generate a unique pass number
                    $pass_number = 'PASS' . strtoupper(uniqid());
                    
                    // Set default values
                    $status = 'pending';
                    $amount_paid = 0.00;
                    $issue_date = date('Y-m-d');
                    
                    // Insert new bus pass
                    $sql = "INSERT INTO bus_passes (
                                student_id, 
                                route_id, 
                                depot_id, 
                                pass_number, 
                                issue_date, 
                                expiry_date, 
                                status, 
                                amount_paid
                            ) VALUES (
                                :student_id, 
                                :route_id, 
                                :depot_id, 
                                :pass_number, 
                                :issue_date, 
                                :expiry_date, 
                                :status, 
                                :amount_paid
                            )";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(":student_id", $student['id'], PDO::PARAM_INT);
                    $stmt->bindParam(":route_id", $route_id, PDO::PARAM_INT);
                    $stmt->bindParam(":depot_id", $route['depot_id'], PDO::PARAM_INT);
                    $stmt->bindParam(":pass_number", $pass_number, PDO::PARAM_STR);
                    $stmt->bindParam(":issue_date", $issue_date, PDO::PARAM_STR);
                    $stmt->bindParam(":expiry_date", $expiry_date, PDO::PARAM_STR);
                    $stmt->bindParam(":status", $status, PDO::PARAM_STR);
                    $stmt->bindParam(":amount_paid", $amount_paid, PDO::PARAM_STR);
                    
                    if($stmt->execute()) {
                        $success = "Your bus pass application has been submitted successfully. It is now pending approval.";
                    } else {
                        $error = "Failed to submit your application. Please try again.";
                    }
                }
            }
        }
    }
}
?>

<!-- Main content -->
<main class="col-12 px-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Apply for Bus Pass</h1>
    </div>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if(!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Bus Pass Application</h5>
                <p class="text-muted">Please select your preferred route and submit the form to apply for a bus pass.</p>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label for="student_name" class="form-label">Student Name</label>
                        <input type="text" class="form-control" id="student_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="registration_no" class="form-label">Registration Number</label>
                        <input type="text" class="form-control" id="registration_no" value="<?php echo htmlspecialchars($student['registration_no']); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="route_id" class="form-label">Select Route <span class="text-danger">*</span></label>
                        <select class="form-select <?php echo (!empty($error) && empty($_POST['route_id'])) ? 'is-invalid' : ''; ?>" id="route_id" name="route_id" required>
                            <option value="">-- Select Route --</option>
                            <?php 
                            $current_depot = '';
                            foreach($routes as $route): 
                                if($current_depot != $route['depot_name']) {
                                    if($current_depot != '') echo '</optgroup>';
                                    echo '<optgroup label="' . htmlspecialchars($route['depot_name']) . '">';
                                    $current_depot = $route['depot_name'];
                                }
                            ?>
                                <option value="<?php echo $route['id']; ?>" <?php echo (isset($_POST['route_id']) && $_POST['route_id'] == $route['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($route['start_point'] . ' to ' . $route['end_point'] . ' - Rs. ' . number_format($route['fare'], 2)); ?>
                                </option>
                            <?php 
                            endforeach; 
                            if($current_depot != '') echo '</optgroup>';
                            ?>
                        </select>
                        <?php if(!empty($error) && empty($_POST['route_id'])): ?>
                            <div class="invalid-feedback">Please select a route.</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Your bus pass will be valid for 1 year from the date of approval. You will be notified once your application is processed.
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Submit Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php
// Include footer
include '../includes/footer.php';
?>
