<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Debug: Show all session data
echo "<!-- Session Data: " . print_r($_SESSION, true) . " -->";

// Check if user is logged in and is a depot user
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'depot') {
    header("location: ../auth/login.php");
    exit;
}

$page_title = 'Bus Schedule - Depot Dashboard';
include '../includes/depot_header.php';

// Get the depot ID of the logged-in user
$depot_id = $_SESSION['depot_id'] ?? 0;

// Fetch bus schedules with route information
$sql = "SELECT 
            bs.*, 
            r.start_point, 
            r.end_point, 
            r.distance_km, 
            r.fare, 
            r.id as route_id,
            DATE(bs.departure_time) as schedule_date,
            TIME(bs.departure_time) as departure_time_only,
            TIME(bs.arrival_time) as arrival_time_only
        FROM bus_schedules bs
        JOIN routes r ON bs.route_id = r.id
        WHERE bs.status = 'active' 
        AND r.status = 'active'
        ORDER BY bs.departure_time";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$schedules = $stmt->fetchAll();
?>

<div class="content-wrapper" style="padding-top: 0;">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Bus Schedule</h1>
            <a href="manage_schedule.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Schedule
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-bus me-2 text-primary"></i>Bus Schedules</h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Route</th>
                                <th>Validity</th>
                                <th>To Campus</th>
                                <th>To Home</th>
                                <th>Days</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $schedule): 
                                $departure = new DateTime($schedule['departure_time']);
                                $arrival = new DateTime($schedule['arrival_time']);
                                $isActive = $schedule['status'] === 'active';
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium">Route #<?php echo htmlspecialchars($schedule['route_id']); ?></span>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($schedule['start_point']); ?> to 
                                                <?php echo htmlspecialchars($schedule['end_point']); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <small class="text-muted">
                                                <?php 
                                                $schedule_date = new DateTime($schedule['schedule_date']);
                                                $valid_to = clone $schedule_date;
                                                $valid_to->modify('+1 year'); // Assuming 1 year validity
                                                echo $schedule_date->format('M d, Y') . ' to ' . $valid_to->format('M d, Y');
                                                ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <small class="text-muted">
                                                <strong>Depart:</strong> <?php echo date('h:i A', strtotime($schedule['departure_time_only'])); ?>
                                            </small>
                                            <small class="text-muted">
                                                <strong>Arrive:</strong> <?php echo date('h:i A', strtotime($schedule['arrival_time_only'])); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <small class="text-muted">
                                                <strong>Depart:</strong> <?php echo date('h:i A', strtotime($schedule['arrival_time_only'])); ?>
                                            </small>
                                            <small class="text-muted">
                                                <strong>Arrive:</strong> <?php 
                                                    // Calculate arrival time by adding some duration (e.g., 30 minutes)
                                                    $departure = new DateTime($schedule['arrival_time_only']);
                                                    $departure->modify('+30 minutes');
                                                    echo $departure->format('h:i A');
                                                ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $days = [];
                                        if (isset($schedule['monday']) && $schedule['monday']) $days[] = 'Mon';
                                        if (isset($schedule['tuesday']) && $schedule['tuesday']) $days[] = 'Tue';
                                        if (isset($schedule['wednesday']) && $schedule['wednesday']) $days[] = 'Wed';
                                        if (isset($schedule['thursday']) && $schedule['thursday']) $days[] = 'Thu';
                                        if (isset($schedule['friday']) && $schedule['friday']) $days[] = 'Fri';
                                        if (isset($schedule['saturday']) && $schedule['saturday']) $days[] = 'Sat';
                                        if (isset($schedule['sunday']) && $schedule['sunday']) $days[] = 'Sun';
                                        echo !empty($days) ? implode(', ', $days) : 'Not set';
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-<?php echo $isActive ? 'success' : 'secondary'; ?> text-<?php echo $isActive ? 'success' : 'secondary'; ?> d-inline-flex align-items-center">
                                            <span class="bullet bullet-xs me-1"></span>
                                            <?php echo ucfirst($schedule['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" href="#" onclick="viewSchedule(<?php echo $schedule['id']; ?>)">
                                                        <i class="fas fa-eye me-2"></i>View Details
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="manage_schedule.php?id=<?php echo $schedule['id']; ?>">
                                                        <i class="fas fa-edit me-2"></i> Edit
                                                    </a>
                                                </li>
                                                <?php if ($isActive): ?>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" onclick="deactivateSchedule(<?php echo $schedule['id']; ?>)">
                                                        <i class="fas fa-times-circle me-2"></i>Deactivate
                                                </a>
                                                    </a>
                                                </li>
                                                <?php else: ?>
                                                <li>
                                                    <a class="dropdown-item text-success" href="#" onclick="activateSchedule(<?php echo $schedule['id']; ?>)">
                                                        <i class="fas fa-check-circle me-2"></i>Activate
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php if (empty($schedules)): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-bus fa-4x text-muted"></i>
                    </div>
                    <h5 class="text-muted mb-3">No Schedules Found</h5>
                    <p class="text-muted mb-4">There are no bus schedules available for your depot at the moment.</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                        <i class="fas fa-plus me-2"></i>Add New Schedule
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addScheduleModalLabel">Add New Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php
                // Display success/error messages if any
                if (isset($_SESSION['success'])) {
                    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                    unset($_SESSION['success']);
                }
                if (isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
                    unset($_SESSION['error']);
                }
                ?>
                <form id="scheduleForm" method="POST" action="save_schedule.php">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="route_id" class="form-label">Route</label>
                            <select class="form-select" id="route_id" name="route_id" required>
                                <option value="">Select Route</option>
                                <?php
                                // Debug: Show depot_id
                                echo "<!-- Debug: Current depot_id = $depot_id -->";
                                
                                // Get routes associated with this depot
                                $routes_sql = "SELECT r.id, r.start_point, r.end_point, r.distance_km 
                                             FROM routes r 
                                             JOIN depot_routes dr ON r.id = dr.route_id 
                                             WHERE dr.depot_id = :depot_id
                                             ORDER BY r.start_point, r.end_point";
                                $routes_stmt = $pdo->prepare($routes_sql);
                                $routes_stmt->execute([':depot_id' => $depot_id]);
                                $routes = $routes_stmt->fetchAll();
                                
                                // Debug: Show number of routes found
                                echo "<!-- Debug: Found " . count($routes) . " routes for this depot -->";
                                if (count($routes) > 0) {
                                    echo "<!-- Debug: First route: " . $routes[0]['start_point'] . ' to ' . $routes[0]['end_point'] . " -->";
                                }
                                foreach ($routes as $route):
                                ?>
                                <option value="<?php echo $route['id']; ?>">
                                    <?php echo htmlspecialchars($route['start_point'] . ' to ' . $route['end_point'] . ' (' . $route['distance_km'] . ' km)'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="notes" class="form-label">Notes</label>
                            <input type="text" class="form-control" id="notes" name="notes" placeholder="Any special instructions">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="valid_from" class="form-label">Valid From</label>
                            <input type="date" class="form-control" id="valid_from" name="valid_from" required>
                        </div>
                        <div class="col-md-6">
                            <label for="valid_to" class="form-label">Valid To</label>
                            <input type="date" class="form-control" id="valid_to" name="valid_to" required>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Home to Campus</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="home_to_campus_departure" class="form-label">Departure Time</label>
                                    <input type="time" class="form-control" id="home_to_campus_departure" name="home_to_campus_departure" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="home_to_campus_arrival" class="form-label">Arrival Time</label>
                                    <input type="time" class="form-control" id="home_to_campus_arrival" name="home_to_campus_arrival" required>
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
                                    <input type="time" class="form-control" id="campus_to_home_departure" name="campus_to_home_departure" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="campus_to_home_arrival" class="form-label">Arrival Time</label>
                                    <input type="time" class="form-control" id="campus_to_home_arrival" name="campus_to_home_arrival" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Days of Operation</label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="monday" name="days[]" value="monday" checked>
                                    <label class="form-check-label" for="monday">Monday</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="tuesday" name="days[]" value="tuesday" checked>
                                    <label class="form-check-label" for="tuesday">Tuesday</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="wednesday" name="days[]" value="wednesday" checked>
                                    <label class="form-check-label" for="wednesday">Wednesday</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="thursday" name="days[]" value="thursday" checked>
                                    <label class="form-check-label" for="thursday">Thursday</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="friday" name="days[]" value="friday" checked>
                                    <label class="form-check-label" for="friday">Friday</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="saturday" name="days[]" value="saturday">
                                    <label class="form-check-label" for="saturday">Saturday</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sunday" name="days[]" value="sunday">
                                    <label class="form-check-label" for="sunday">Sunday</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Notes field moved to the top of the form -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveSchedule">Save Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation and time calculations
$(document).ready(function() {
    // Set default dates
    const today = new Date();
    const nextYear = new Date();
    nextYear.setFullYear(today.getFullYear() + 1);
    
    // Format dates as YYYY-MM-DD
    const formatDate = (date) => {
        const d = new Date(date);
        let month = '' + (d.getMonth() + 1);
        let day = '' + d.getDate();
        const year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    };
    
    // Set default date values
    $('#valid_from').val(formatDate(today));
    $('#valid_to').val(formatDate(nextYear));
    
    // Set default times
    $('#home_to_campus_departure').val('08:00');
    $('#home_to_campus_arrival').val('09:00');
    $('#campus_to_home_departure').val('17:00');
    $('#campus_to_home_arrival').val('18:00');
    
    // Validate date range
    $('#valid_from, #valid_to').on('change', function() {
        const from = new Date($('#valid_from').val());
        const to = new Date($('#valid_to').val());
        
        if (from > to) {
            alert('Valid From date must be before Valid To date');
            $('#valid_to').val(formatDate(from));
        }
    });
    
    // Validate time order for home to campus
    $('#home_to_campus_departure, #home_to_campus_arrival').on('change', function() {
        const departure = $('#home_to_campus_departure').val();
        const arrival = $('#home_to_campus_arrival').val();
        
        if (departure && arrival && departure >= arrival) {
            alert('Departure time must be before arrival time');
            $('#home_to_campus_arrival').val('');
        }
    });
    
    // Validate time order for campus to home
    $('#campus_to_home_departure, #campus_to_home_arrival').on('change', function() {
        const departure = $('#campus_to_home_departure').val();
        const arrival = $('#campus_to_home_arrival').val();
        
        if (departure && arrival && departure >= arrival) {
            alert('Departure time must be before arrival time');
            $('#campus_to_home_arrival').val('');
        }
    });
    
    // Toggle all weekdays
    $('#selectAllDays').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('input[name="days[]"]').prop('checked', isChecked);
    });
});

// Functions for schedule actions
function viewSchedule(id) {
    window.location.href = 'view_schedule.php?id=' + id;
}

function editSchedule(id) {
    window.location.href = 'edit_schedule.php?id=' + id;
}

function activateSchedule(id) {
    if (confirm('Are you sure you want to activate this schedule?')) {
        // Implement activation
        console.log('Activating schedule:', id);
        location.reload();
    }
}

function deactivateSchedule(id) {
    if (confirm('Are you sure you want to deactivate this schedule?')) {
        // Implement deactivation
        console.log('Deactivating schedule:', id);
        location.reload();
    }
}

// Handle form submission with AJAX
$(document).ready(function() {
    $('#scheduleForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = $('#saveSchedule');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...');
        
        // Submit form via AJAX
        $.ajax({
            url: 'save_schedule.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // On success, reload the page to show the new schedule
                location.reload();
            },
            error: function(xhr, status, error) {
                // Show error message
                alert('Error saving schedule: ' + error);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>

<?php include '../includes/depot_footer.php'; ?>
