<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Check if user is logged in and is a depot user
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'depot') {
    header("location: ../auth/login.php");
    exit;
}

$page_title = isset($_GET['id']) ? 'Edit Schedule' : 'Add New Schedule';
include '../includes/depot_header.php';

$depot_id = $_SESSION['depot_id'] ?? 0;
$schedule = null;
$is_edit = false;

// If editing, fetch the schedule data
if (isset($_GET['id'])) {
    $is_edit = true;
    $schedule_id = (int)$_GET['id'];
    
    $stmt = $pdo->prepare("SELECT * FROM bus_schedules WHERE id = ? AND depot_id = ?");
    $stmt->execute([$schedule_id, $depot_id]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$schedule) {
        $_SESSION['error'] = 'Schedule not found or you do not have permission to edit it.';
        header("Location: schedule.php");
        exit;
    }
}

// Fetch routes for dropdown
$routes = $pdo->query("SELECT id, start_point, end_point FROM routes WHERE status = 'active' ORDER BY start_point")->fetchAll();
?>

<div class="content-wrapper">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0"><?php echo $is_edit ? 'Edit Schedule' : 'Add New Schedule'; ?></h1>
            <a href="schedule.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Schedules
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <form id="scheduleForm" method="POST" action="save_schedule.php">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="id" value="<?php echo $schedule['id']; ?>">
                <?php endif; ?>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="route_id" class="form-label">Route</label>
                        <select class="form-select" id="route_id" name="route_id" required>
                            <option value="">Select Route</option>
                            <?php foreach ($routes as $route): ?>
                                <option value="<?php echo $route['id']; ?>" 
                                    <?php echo ($is_edit && $schedule['route_id'] == $route['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($route['start_point'] . ' to ' . $route['end_point']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="vehicle_number" class="form-label">Vehicle Number</label>
                        <input type="text" class="form-control" id="vehicle_number" name="vehicle_number" 
                               value="<?php echo $is_edit ? htmlspecialchars($schedule['vehicle_number']) : ''; ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="valid_from" class="form-label">Valid From</label>
                        <input type="date" class="form-control" id="valid_from" name="valid_from" 
                               value="<?php echo $is_edit ? $schedule['valid_from'] : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="valid_to" class="form-label">Valid To</label>
                        <input type="date" class="form-control" id="valid_to" name="valid_to" 
                               value="<?php echo $is_edit ? $schedule['valid_to'] : ''; ?>" required>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Home to Campus</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="departure_time" class="form-label">Departure Time</label>
                                <input type="time" class="form-control" id="departure_time" name="departure_time" 
                                       value="<?php echo $is_edit ? date('H:i', strtotime($schedule['departure_time'])) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="arrival_time" class="form-label">Arrival Time</label>
                                <input type="time" class="form-control" id="arrival_time" name="arrival_time" 
                                       value="<?php echo $is_edit ? date('H:i', strtotime($schedule['arrival_time'])) : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Campus to Home</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="campus_to_home_departure" class="form-label">Departure Time</label>
                                <input type="time" class="form-control" id="campus_to_home_departure" name="campus_to_home_departure" 
                                       value="<?php echo $is_edit && $schedule['campus_to_home_departure'] ? date('H:i', strtotime($schedule['campus_to_home_departure'])) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="campus_to_home_arrival" class="form-label">Arrival Time</label>
                                <input type="time" class="form-control" id="campus_to_home_arrival" name="campus_to_home_arrival" 
                                       value="<?php echo $is_edit && $schedule['campus_to_home_arrival'] ? date('H:i', strtotime($schedule['campus_to_home_arrival'])) : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Days of Operation</label>
                    <div class="row">
                        <?php
                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                        $selected_days = $is_edit ? explode(',', $schedule['days']) : [];
                        foreach ($days as $day):
                            $is_checked = $is_edit ? in_array($day, $selected_days) : false;
                        ?>
                            <div class="col-6 col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="<?php echo $day; ?>" 
                                           id="day_<?php echo $day; ?>" <?php echo $is_checked ? 'checked' : ''; ?>>
                                    <label class="form-check-label text-capitalize" for="day_<?php echo $day; ?>">
                                        <?php echo ucfirst($day); ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes (Optional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo $is_edit ? htmlspecialchars($schedule['notes']) : ''; ?></textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> <?php echo $is_edit ? 'Update' : 'Save'; ?> Schedule
                    </button>
                    <a href="schedule.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Form validation
$(document).ready(function() {
    $('#scheduleForm').validate({
        rules: {
            route_id: 'required',
            vehicle_number: 'required',
            valid_from: 'required',
            valid_to: {
                required: true,
                greaterThan: '#valid_from'
            },
            departure_time: 'required',
            arrival_time: 'required',
            campus_to_home_departure: 'required',
            campus_to_home_arrival: 'required',
            'days[]': 'required'
        },
        messages: {
            'days[]': 'Please select at least one day of operation',
            valid_to: {
                greaterThan: 'Valid To date must be after Valid From date'
            }
        },
        errorElement: 'div',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group, .row').append(error);
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        }
    });

    // Custom method to validate that end date is after start date
    $.validator.addMethod('greaterThan', function(value, element, param) {
        var startDate = $(param).val();
        if (!startDate) return true; // Skip validation if start date is empty
        
        var start = new Date(startDate);
        var end = new Date(value);
        return end > start;
    }, 'End date must be after start date');

    // Set minimum date for valid_from to today
    var today = new Date().toISOString().split('T')[0];
    $('#valid_from').attr('min', today);
    $('#valid_to').attr('min', today);
});
</script>

<?php include '../includes/footer.php'; ?>
