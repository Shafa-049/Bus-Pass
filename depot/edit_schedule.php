<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Check if user is logged in and is a depot user
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'depot') {
    header("location: ../auth/login.php");
    exit;
}

// Check if schedule ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("location: schedule.php");
    exit;
}

$schedule_id = $_GET['id'];
$depot_id = $_SESSION['depot_id'] ?? 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and process the form data
    $route_id = $_POST['route_id'] ?? '';
    $valid_from = $_POST['valid_from'] ?? '';
    $valid_to = $_POST['valid_to'] ?? '';
    $home_to_campus_departure = $_POST['home_to_campus_departure'] ?? '';
    $home_to_campus_arrival = $_POST['home_to_campus_arrival'] ?? '';
    $campus_to_home_departure = $_POST['campus_to_home_departure'] ?? '';
    $campus_to_home_arrival = $_POST['campus_to_home_arrival'] ?? '';
    $days = $_POST['days'] ?? [];
    $notes = $_POST['notes'] ?? '';
    $status = $_POST['status'] ?? 'active';

    // Basic validation
    $errors = [];
    if (empty($route_id)) $errors[] = "Route is required";
    if (empty($valid_from)) $errors[] = "Valid from date is required";
    if (empty($valid_to)) $errors[] = "Valid to date is required";
    if (empty($home_to_campus_departure)) $errors[] = "Home to campus departure time is required";
    if (empty($home_to_campus_arrival)) $errors[] = "Home to campus arrival time is required";
    if (empty($campus_to_home_departure)) $errors[] = "Campus to home departure time is required";
    if (empty($campus_to_home_arrival)) $errors[] = "Campus to home arrival time is required";
    if (empty($days)) $errors[] = "At least one day must be selected";

    if (empty($errors)) {
        try {
            // Update schedule in database
            $sql = "UPDATE bus_schedules 
                    SET route_id = ?, 
                        valid_from = ?, 
                        valid_to = ?,
                        home_to_campus_departure = ?,
                        home_to_campus_arrival = ?,
                        campus_to_home_departure = ?,
                        campus_to_home_arrival = ?,
                        monday = ?,
                        tuesday = ?,
                        wednesday = ?,
                        thursday = ?,
                        friday = ?,
                        saturday = ?,
                        sunday = ?,
                        notes = ?,
                        status = ?,
                        updated_at = NOW()
                    WHERE id = ? AND route_id IN (
                        SELECT r.id FROM routes r 
                        JOIN depot_routes dr ON r.id = dr.route_id 
                        WHERE dr.depot_id = ?
                    )";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $route_id,
                $valid_from,
                $valid_to,
                $home_to_campus_departure,
                $home_to_campus_arrival,
                $campus_to_home_departure,
                $campus_to_home_arrival,
                in_array('monday', $days) ? 1 : 0,
                in_array('tuesday', $days) ? 1 : 0,
                in_array('wednesday', $days) ? 1 : 0,
                in_array('thursday', $days) ? 1 : 0,
                in_array('friday', $days) ? 1 : 0,
                in_array('saturday', $days) ? 1 : 0,
                in_array('sunday', $days) ? 1 : 0,
                $notes,
                $status,
                $schedule_id,
                $depot_id
            ]);

            if ($result) {
                $_SESSION['success'] = "Schedule updated successfully!";
                header("Location: schedule.php");
                exit;
            } else {
                $errors[] = "Failed to update schedule.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch schedule details
$sql = "SELECT bs.*, r.start_point, r.end_point, r.distance_km, r.fare, r.id as route_id
        FROM bus_schedules bs
        JOIN routes r ON bs.route_id = r.id
        JOIN depot_routes dr ON r.id = dr.route_id
        WHERE bs.id = ? AND dr.depot_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$schedule_id, $depot_id]);
$schedule = $stmt->fetch();

if (!$schedule) {
    header("location: schedule.php");
    exit;
}

// Fetch all routes for this depot
$routes_sql = "SELECT r.* FROM routes r 
               JOIN depot_routes dr ON r.id = dr.route_id 
               WHERE dr.depot_id = ?";
$routes_stmt = $pdo->prepare($routes_sql);
$routes_stmt->execute([$depot_id]);
$routes = $routes_stmt->fetchAll();

$page_title = 'Edit Schedule - Depot Dashboard';
include '../includes/depot_header.php';
?>

<div class="content-wrapper p-0">
    <div class="container-fluid p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">
                <i class="fas fa-edit me-2 text-primary"></i>Edit Schedule
            </h1>
            <a href="schedule.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="needs-validation" novalidate>
            <div class="card shadow-sm mb-3">
                <div class="card-body p-3">
                    <div class="row g-3">
                        <!-- Route and Validity -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="route_id" class="form-label small fw-bold text-uppercase text-muted">Route</label>
                                <select class="form-select form-select-sm" id="route_id" name="route_id" required>
                                    <option value="">Select Route</option>
                                    <?php foreach ($routes as $route): ?>
                                        <option value="<?php echo $route['id']; ?>" 
                                            <?php echo ($route['id'] == $schedule['route_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($route['start_point'] . ' to ' . $route['end_point']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select a route</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="valid_from" class="form-label small fw-bold text-uppercase text-muted">Valid From</label>
                                <input type="date" class="form-control form-control-sm" id="valid_from" name="valid_from" 
                                       value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($schedule['valid_from']))); ?>" required>
                                <div class="invalid-feedback">Please select a valid from date</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="valid_to" class="form-label small fw-bold text-uppercase text-muted">Valid To</label>
                                <input type="date" class="form-control form-control-sm" id="valid_to" name="valid_to"
                                       value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($schedule['valid_to']))); ?>" required>
                                <div class="invalid-feedback">Please select a valid to date</div>
                            </div>
                        </div>

                        <!-- Home to Campus Timings -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-uppercase text-muted mb-3 fw-bold small">
                                    <i class="fas fa-home me-1"></i> Home to Campus
                                </h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label for="home_to_campus_departure" class="form-label small text-muted">Departure</label>
                                        <input type="time" class="form-control form-control-sm" id="home_to_campus_departure" 
                                               name="home_to_campus_departure" 
                                               value="<?php echo htmlspecialchars(date('H:i', strtotime($schedule['home_to_campus_departure']))); ?>" required>
                                    </div>
                                    <div class="col-6">
                                        <label for="home_to_campus_arrival" class="form-label small text-muted">Arrival</label>
                                        <input type="time" class="form-control form-control-sm" id="home_to_campus_arrival" 
                                               name="home_to_campus_arrival"
                                               value="<?php echo htmlspecialchars(date('H:i', strtotime($schedule['home_to_campus_arrival']))); ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campus to Home Timings -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-uppercase text-muted mb-3 fw-bold small">
                                    <i class="fas fa-university me-1"></i> Campus to Home
                                </h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label for="campus_to_home_departure" class="form-label small text-muted">Departure</label>
                                        <input type="time" class="form-control form-control-sm" id="campus_to_home_departure" 
                                               name="campus_to_home_departure"
                                               value="<?php echo htmlspecialchars(date('H:i', strtotime($schedule['campus_to_home_departure']))); ?>" required>
                                    </div>
                                    <div class="col-6">
                                        <label for="campus_to_home_arrival" class="form-label small text-muted">Arrival</label>
                                        <input type="time" class="form-control form-control-sm" id="campus_to_home_arrival" 
                                               name="campus_to_home_arrival"
                                               value="<?php echo htmlspecialchars(date('H:i', strtotime($schedule['campus_to_home_arrival']))); ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Operating Days -->
                        <div class="col-12">
                            <div class="border rounded p-3">
                                <h6 class="text-uppercase text-muted mb-3 fw-bold small">
                                    <i class="fas fa-calendar-alt me-1"></i> Operating Days
                                </h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php 
                                    $days = [
                                        'monday' => 'Monday',
                                        'tuesday' => 'Tuesday',
                                        'wednesday' => 'Wednesday',
                                        'thursday' => 'Thursday',
                                        'friday' => 'Friday',
                                        'saturday' => 'Saturday',
                                        'sunday' => 'Sunday'
                                    ];
                                    
                                    foreach ($days as $day => $dayName): 
                                        $isChecked = $schedule[$day] ? 'checked' : '';
                                    ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="day_<?php echo $day; ?>" 
                                                   name="days[]" value="<?php echo $day; ?>" <?php echo $isChecked; ?>>
                                            <label class="form-check-label small" for="day_<?php echo $day; ?>">
                                                <?php echo substr($dayName, 0, 3); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Status and Notes -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="status" class="form-label small fw-bold text-uppercase text-muted">Status</label>
                                <select class="form-select form-select-sm" id="status" name="status">
                                    <option value="active" <?php echo ($schedule['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($schedule['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label for="notes" class="form-label small fw-bold text-uppercase text-muted">Notes</label>
                                <textarea class="form-control form-control-sm" id="notes" name="notes" rows="2" 
                                          placeholder="Any additional notes about this schedule"><?php echo htmlspecialchars($schedule['notes'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="if(confirm('Are you sure you want to delete this schedule?')) { deleteSchedule(<?php echo $schedule['id']; ?>); }">
                            <i class="fas fa-trash-alt me-1"></i> Delete
                        </button>
                        <div>
                            <a href="view_schedule.php?id=<?php echo $schedule['id']; ?>" class="btn btn-sm btn-outline-secondary me-2">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict'
    
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll('.needs-validation')
    
    // Loop over them and prevent submission
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            
            form.classList.add('was-validated')
        }, false)
    })
})()

// Time validation
const homeToCampusDeparture = document.getElementById('home_to_campus_departure');
const homeToCampusArrival = document.getElementById('home_to_campus_arrival');
const campusToHomeDeparture = document.getElementById('campus_to_home_departure');
const campusToHomeArrival = document.getElementById('campus_to_home_arrival');

function validateTimes() {
    // Ensure arrival is after departure for each trip
    if (homeToCampusDeparture.value && homeToCampusArrival.value) {
        if (homeToCampusDeparture.value >= homeToCampusArrival.value) {
            homeToCampusArrival.setCustomValidity('Arrival time must be after departure time');
        } else {
            homeToCampusArrival.setCustomValidity('');
        }
    }
    
    if (campusToHomeDeparture.value && campusToHomeArrival.value) {
        if (campusToHomeDeparture.value >= campusToHomeArrival.value) {
            campusToHomeArrival.setCustomValidity('Arrival time must be after departure time');
        } else {
            campusToHomeArrival.setCustomValidity('');
        }
    }
}

// Add event listeners for time validation
[homeToCampusDeparture, homeToCampusArrival, campusToHomeDeparture, campusToHomeArrival].forEach(input => {
    input.addEventListener('change', validateTimes);
});

// Function to handle schedule deletion
function deleteSchedule(id) {
    if (confirm('Are you sure you want to delete this schedule? This action cannot be undone.')) {
        fetch('delete_schedule.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'schedule.php?success=' + encodeURIComponent('Schedule deleted successfully');
            } else {
                alert('Error: ' + (data.message || 'Failed to delete schedule'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the schedule');
        });
    }
}
</script>
</div>

<script>
// Function to handle schedule deletion
function deleteSchedule(id) {
    if (confirm('Are you sure you want to delete this schedule? This action cannot be undone.')) {
        fetch('delete_schedule.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'schedule.php?success=' + encodeURIComponent('Schedule deleted successfully');
            } else {
                alert('Error: ' + (data.message || 'Failed to delete schedule'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the schedule');
        });
    }
}

// Client-side validation
const form = document.querySelector('form');
form.addEventListener('submit', function(event) {
    const validFrom = new Date(document.getElementById('valid_from').value);
    const validTo = new Date(document.getElementById('valid_to').value);
    
    if (validFrom > validTo) {
        event.preventDefault();
        alert('Valid To date must be after Valid From date');
        return false;
    }
    
    const daysSelected = document.querySelectorAll('input[name="days[]"]:checked').length;
    if (daysSelected === 0) {
        event.preventDefault();
        alert('Please select at least one operating day');
        return false;
    }
    
    return true;
});
</script>

<?php include '../includes/footer.php'; ?>
