<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'student') {
    header("location: ../auth/login.php");
    exit;
}

// Include config file
require_once '../config/database.php';

// Set page title
$page_title = 'Bus Schedule - SEUSL Bus Pass Management System';

// Include student header
include '../includes/student_header.php';

// Initialize variables
$schedules = [];
$error = '';

try {
    // Get all active bus schedules
    $sql = "SELECT 
                bs.*, 
                r.start_point, 
                r.end_point, 
                r.distance_km, 
                r.fare,
                d.depot_name,
                b.bus_number,
                b.bus_type,
                b.registration_number
            FROM bus_schedules bs
            JOIN routes r ON bs.route_id = r.id
            JOIN buses b ON b.id = (
                SELECT id FROM buses WHERE status = 'active' LIMIT 1
            )
            JOIN depots d ON b.depot_id = d.id
            WHERE bs.status = 'active' 
            AND r.status = 'active'
            ORDER BY 
                d.depot_name,
                bs.departure_time";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
} catch(Exception $e) {
    $error = $e->getMessage();
}

// Function to format time
function formatTime($time) {
    return date('h:i A', strtotime($time));
}

// Function to format duration
function formatDuration($start, $end) {
    $start = new DateTime($start);
    $end = new DateTime($end);
    $interval = $start->diff($end);
    
    $hours = $interval->h;
    $minutes = $interval->i;
    
    $result = '';
    if ($hours > 0) {
        $result .= $hours . ' hr' . ($hours > 1 ? 's' : '');
    }
    if ($minutes > 0) {
        if ($hours > 0) $result .= ' ';
        $result .= $minutes . ' min' . ($minutes > 1 ? 's' : '');
    }
    
    return $result;
}
?>

<!-- Main content -->
<main class="col-12 px-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-bus me-2 text-primary"></i>Bus Schedule
        </h1>
    </div>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-calendar-alt me-2 text-primary"></i>Available Bus Schedules
            </h5>
            <p class="text-muted mb-0">Check the bus timings and plan your journey</p>
        </div>
        <div class="card-body p-0">
            <?php if(empty($schedules)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-bus fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No bus schedules available at the moment.</p>
                    <p>Please check back later or contact your depot for more information.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Route</th>
                                <th>From Home</th>
                                <th>From Campus</th>
                                <th>Days</th>
                                <th>Bus</th>
                                <th>Depot</th>
                                <th>Fare</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($schedules as $schedule): 
                                $departure = new DateTime($schedule['departure_time']);
                                $arrival = new DateTime($schedule['arrival_time']);
                                $isToday = $departure->format('Y-m-d') === date('Y-m-d');
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium"><?php echo htmlspecialchars($schedule['start_point'] . ' to ' . $schedule['end_point']); ?></span>
                                            <small class="text-muted">
                                                <?php echo number_format($schedule['distance_km'], 1); ?> km
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <small class="text-muted">
                                                <strong>Depart:</strong> <?php echo formatTime($schedule['departure_time']); ?>
                                            </small>
                                            <small class="text-muted">
                                                <strong>Arrive:</strong> <?php echo formatTime($schedule['arrival_time']); ?>
                                            </small>
                                            <?php if($isToday): ?>
                                                <span class="badge bg-success mt-1">Today</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <small class="text-muted">
                                                <strong>Depart:</strong> <?php 
                                                    // Calculate return time (add 30 minutes to arrival time as an example)
                                                    $returnDeparture = clone $arrival;
                                                    $returnDeparture->modify('+30 minutes');
                                                    echo $returnDeparture->format('h:i A');
                                                ?>
                                            </small>
                                            <small class="text-muted">
                                                <strong>Arrive:</strong> <?php 
                                                    $returnArrival = clone $returnDeparture;
                                                    $duration = $arrival->diff(new DateTime($schedule['departure_time']));
                                                    $returnArrival->add($duration);
                                                    echo $returnArrival->format('h:i A');
                                                ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $days = [];
                                        $dayMap = [
                                            'monday' => 'Mon',
                                            'tuesday' => 'Tue',
                                            'wednesday' => 'Wed',
                                            'thursday' => 'Thu',
                                            'friday' => 'Fri',
                                            'saturday' => 'Sat',
                                            'sunday' => 'Sun'
                                        ];
                                        
                                        foreach ($dayMap as $day => $shortDay) {
                                            if (!empty($schedule[$day])) {
                                                $days[] = $shortDay;
                                            }
                                        }
                                        echo implode(', ', $days);
                                        ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-bus me-2 text-primary"></i>
                                            <div>
                                                <div class="fw-medium"><?php echo !empty($schedule['bus_number']) ? htmlspecialchars($schedule['bus_number']) : 'N/A'; ?></div>
                                                <small class="text-muted">
                                                    <?php echo !empty($schedule['bus_type']) ? ucfirst(str_replace('_', ' ', $schedule['bus_type'])) : 'Vehicle'; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($schedule['depot_name']); ?></td>
                                    <td class="fw-bold">Rs. <?php echo number_format($schedule['fare'], 2); ?></td>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-white">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Schedules are subject to change. Please check with your depot for the most up-to-date information.
            </small>
        </div>
    </div>
</main>

<?php
// Include footer
include '../includes/footer.php';
?>
