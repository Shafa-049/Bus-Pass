<?php
// Include config file
require_once '../config/database.php';

// Define variables and initialize with empty values
$user_type = $username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";
$registration_message = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate user type
    if(empty(trim($_POST["user_type"]))){
        $user_type_err = "Please select user type.";     
    } else{
        $user_type = trim($_POST["user_type"]);
    }
    
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } elseif(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))){
        $username_err = "Username can only contain letters, numbers, and underscores.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = :username";
        
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            
            // Set parameters
            $param_username = trim($_POST["username"]);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            unset($stmt);
        }
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have at least 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Check input errors before inserting in database
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password, user_type, status) VALUES (:username, :password, :user_type, 'pending')";
         
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            $stmt->bindParam(":user_type", $param_user_type, PDO::PARAM_STR);
            
            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_user_type = $user_type;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Get the last inserted user ID
                $user_id = $pdo->lastInsertId();
                
                // Insert into respective table based on user type
                if($user_type === 'student'){
                    // Prepare student details
                    $full_name = trim($_POST['full_name']);
                    $registration_no = trim($_POST['registration_no']);
                    $faculty = trim($_POST['faculty']);
                    $department = trim($_POST['department']);
                    $email = trim($_POST['email']);
                    $phone = trim($_POST['phone']);
                    $address = trim($_POST['address']);
                    $depot_id = trim($_POST['depot_id']);
                    
                    $sql = "INSERT INTO students (user_id, full_name, registration_no, faculty, department, email, phone, address, depot_id) 
                            VALUES (:user_id, :full_name, :registration_no, :faculty, :department, :email, :phone, :address, :depot_id)";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                    $stmt->bindParam(":full_name", $full_name, PDO::PARAM_STR);
                    $stmt->bindParam(":registration_no", $registration_no, PDO::PARAM_STR);
                    $stmt->bindParam(":faculty", $faculty, PDO::PARAM_STR);
                    $stmt->bindParam(":department", $department, PDO::PARAM_STR);
                    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                    $stmt->bindParam(":phone", $phone, PDO::PARAM_STR);
                    $stmt->bindParam(":address", $address, PDO::PARAM_STR);
                    $stmt->bindParam(":depot_id", $depot_id, PDO::PARAM_INT);
                    
                    if($stmt->execute()){
                        $registration_message = "Student registration successful! Your account is pending approval from the administrator.";
                    }
                    
                } elseif($user_type === 'depot') {
                    // Prepare depot details
                    $depot_name = trim($_POST['depot_name']);
                    $manager_name = trim($_POST['manager_name']);
                    $email = trim($_POST['email']);
                    $phone = trim($_POST['phone']);
                    $address = trim($_POST['address']);
                    $location = trim($_POST['location']);
                    
                    $sql = "INSERT INTO depots (user_id, depot_name, manager_name, email, phone, address, location) 
                            VALUES (:user_id, :depot_name, :manager_name, :email, :phone, :address, :location)";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                    $stmt->bindParam(":depot_name", $depot_name, PDO::PARAM_STR);
                    $stmt->bindParam(":manager_name", $manager_name, PDO::PARAM_STR);
                    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                    $stmt->bindParam(":phone", $phone, PDO::PARAM_STR);
                    $stmt->bindParam(":address", $address, PDO::PARAM_STR);
                    $stmt->bindParam(":location", $location, PDO::PARAM_STR);
                    
                    if($stmt->execute()){
                        $registration_message = "Depot registration successful! Your account is pending approval from the administrator.";
                    }
                }
                
                // Reset form fields if registration is successful
                if(!empty($registration_message)){
                    $user_type = $username = $password = $confirm_password = "";
                }
                
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            unset($stmt);
        }
    }
    
    // Close connection
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SEUSL Bus Pass</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .register-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            background: white;
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header i {
            font-size: 3rem;
            color: var(--teal);
            margin-bottom: 15px;
        }
        .form-control:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 0.2rem rgba(0, 128, 128, 0.25);
        }
        .btn-register {
            background-color: var(--teal);
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            background-color: var(--teal-dark);
            color: white;
            transform: translateY(-2px);
        }
        .login-link {
            color: var(--teal);
            text-decoration: none;
            font-weight: 600;
        }
        .login-link:hover {
            text-decoration: underline;
        }
        .user-type-selector {
            margin-bottom: 25px;
        }
        .user-type-btn {
            width: 100%;
            padding: 15px;
            border: 2px solid #dee2e6;
            border-radius: 5px;
            background: white;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .user-type-btn:hover {
            border-color: var(--teal-light);
        }
        .user-type-btn.active {
            border-color: var(--teal);
            background-color: rgba(0, 128, 128, 0.05);
        }
        .user-type-btn i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--teal);
        }
        .form-section {
            display: none;
        }
        .form-section.active {
            display: block;
            animation: fadeIn 0.5s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body style="background-color: #f8f9fa;">
    <div class="container">
        <div class="register-container">
            <div class="register-header">
                <i class="fas fa-bus"></i>
                <h2>Create an Account</h2>
                <p class="text-muted">Select your account type to get started</p>
            </div>
            
            <?php 
            if(!empty($registration_message)){
                echo '<div class="alert alert-success">' . $registration_message . '</div>';
            }        
            ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <!-- User Type Selection -->
                <div class="user-type-selector">
                    <input type="hidden" name="user_type" id="user_type" value="<?php echo $user_type; ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="user-type-btn <?php echo ($user_type === 'student') ? 'active' : ''; ?>" data-type="student">
                                <i class="fas fa-user-graduate"></i>
                                <h5>Student</h5>
                                <p class="text-muted small">Register as a student to apply for bus passes</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="user-type-btn <?php echo ($user_type === 'depot') ? 'active' : ''; ?>" data-type="depot">
                                <i class="fas fa-bus-alt"></i>
                                <h5>Bus Depot</h5>
                                <p class="text-muted small">Register as a bus depot to manage passes</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Common Fields -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo $username; ?>" required>
                        <span class="invalid-feedback"><?php echo $username_err; ?></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo $password; ?>" required>
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo $confirm_password; ?>" required>
                        <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                    </div>
                </div>

                <!-- Student Form -->
                <div id="student-form" class="form-section <?php echo ($user_type === 'student') ? 'active' : ''; ?>">
                    <h5 class="mb-4">Student Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" 
                                   value="<?php echo isset($_POST['full_name']) ? $_POST['full_name'] : ''; ?>" 
                                   <?php echo ($user_type === 'student') ? 'required' : ''; ?>>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="registration_no" class="form-label">Registration Number <span class="text-danger">*</span></label>
                            <input type="text" name="registration_no" class="form-control" 
                                   value="<?php echo isset($_POST['registration_no']) ? $_POST['registration_no'] : ''; ?>" 
                                   <?php echo ($user_type === 'student') ? 'required' : ''; ?>>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="faculty" class="form-label">Faculty <span class="text-danger">*</span></label>
                            <select name="faculty" class="form-select" <?php echo ($user_type === 'student') ? 'required' : ''; ?>>
                                <option value="">Select Faculty</option>
                                <option value="Applied Science">Faculty of Applied Science</option>
                                <option value="Islamic Studies">Faculty of Islamic Studies</option>
                                <option value="Management">Faculty of Management</option>
                                <option value="Technology">Faculty of Technology</option>
                                <option value="Arts & Culture">Faculty of Arts & Culture</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                            <input type="text" name="department" class="form-control" 
                                   value="<?php echo isset($_POST['department']) ? $_POST['department'] : ''; ?>" 
                                   <?php echo ($user_type === 'student') ? 'required' : ''; ?>>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>" 
                                   <?php echo ($user_type === 'student') ? 'required' : ''; ?>>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="depot_id" class="form-label">Nearest Depot <span class="text-danger">*</span></label>
                            <select name="depot_id" class="form-select" <?php echo ($user_type === 'student') ? 'required' : ''; ?>>
                                <option value="">Select Depot</option>
                                <option value="1">Akkaraipattu Depot</option>
                                <option value="2">Sammanthurai Depot</option>
                                <option value="3">Kalmunai Depot</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" rows="2" 
                                 <?php echo ($user_type === 'student') ? 'required' : ''; ?>><?php echo isset($_POST['address']) ? $_POST['address'] : ''; ?></textarea>
                    </div>
                </div>

                <!-- Depot Form -->
                <div id="depot-form" class="form-section <?php echo ($user_type === 'depot') ? 'active' : ''; ?>">
                    <h5 class="mb-4">Bus Depot Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="depot_name" class="form-label">Depot Name <span class="text-danger">*</span></label>
                            <input type="text" name="depot_name" class="form-control" 
                                   value="<?php echo isset($_POST['depot_name']) ? $_POST['depot_name'] : ''; ?>"
                                   <?php echo ($user_type === 'depot') ? 'required' : ''; ?>>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="manager_name" class="form-label">Manager Name <span class="text-danger">*</span></label>
                            <input type="text" name="manager_name" class="form-control" 
                                   value="<?php echo isset($_POST['manager_name']) ? $_POST['manager_name'] : ''; ?>"
                                   <?php echo ($user_type === 'depot') ? 'required' : ''; ?>>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>"
                                   <?php echo ($user_type === 'depot') ? 'required' : ''; ?>>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                            <select name="location" class="form-select" <?php echo ($user_type === 'depot') ? 'required' : ''; ?>>
                                <option value="">Select Location</option>
                                <option value="Akkaraipattu">Akkaraipattu</option>
                                <option value="Sammanthurai">Sammanthurai</option>
                                <option value="Kalmunai">Kalmunai</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Depot Address <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" rows="2"
                                 <?php echo ($user_type === 'depot') ? 'required' : ''; ?>><?php echo isset($_POST['address']) ? $_POST['address'] : ''; ?></textarea>
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-register">Register</button>
                </div>
                <div class="text-center mt-3">
                    <p class="mb-0">Already have an account? <a href="login.php" class="login-link">Login here</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show/hide forms based on user type selection
        document.addEventListener('DOMContentLoaded', function() {
            const userTypeBtns = document.querySelectorAll('.user-type-btn');
            const userTypeInput = document.getElementById('user_type');
            const studentForm = document.getElementById('student-form');
            const depotForm = document.getElementById('depot-form');
            
            // Set required fields based on initial user type
            if (userTypeInput.value === 'student') {
                setRequiredFields(studentForm, true);
                setRequiredFields(depotForm, false);
            } else if (userTypeInput.value === 'depot') {
                setRequiredFields(studentForm, false);
                setRequiredFields(depotForm, true);
            }
            
            // Add click event to user type buttons
            userTypeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const type = this.getAttribute('data-type');
                    userTypeInput.value = type;
                    
                    // Update active state of buttons
                    userTypeBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show/hide forms
                    if (type === 'student') {
                        studentForm.classList.add('active');
                        depotForm.classList.remove('active');
                        setRequiredFields(studentForm, true);
                        setRequiredFields(depotForm, false);
                    } else if (type === 'depot') {
                        studentForm.classList.remove('active');
                        depotForm.classList.add('active');
                        setRequiredFields(studentForm, false);
                        setRequiredFields(depotForm, true);
                    }
                });
            });
            
            // Function to set required attribute on form fields
            function setRequiredFields(form, isRequired) {
                const inputs = form.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    if (input.name !== 'user_type') {
                        input.required = isRequired;
                    }
                });
            }
        });
    </script>
</body>
</html>
