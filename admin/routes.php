<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    // Debug: Log the reason for redirection
    error_log('Access denied to routes.php - User not logged in or not admin. Session: ' . print_r($_SESSION, true));
    header('Location: ../index.php');
    exit();
}

$page_title = 'Manage Routes';
include_once '../includes/admin_header.php';

// Initialize variables
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_route'])) {
        // Handle add new route
        $start_point = trim($_POST['start_point']);
        $end_point = trim($_POST['end_point']);
        $distance_km = floatval($_POST['distance_km']);
        $estimated_time = trim($_POST['estimated_time']);
        $fare = floatval($_POST['fare']);
        $status = $_POST['status'];
        
        if (empty($start_point) || empty($end_point) || $distance_km <= 0 || empty($estimated_time)) {
            $error = 'Please fill in all required fields with valid data.';
        } else {
            try {
                $sql = "INSERT INTO routes (start_point, end_point, distance_km, estimated_time, fare, status, created_at, updated_at) 
                        VALUES (:start_point, :end_point, :distance_km, :estimated_time, :fare, :status, NOW(), NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':start_point' => $start_point,
                    ':end_point' => $end_point,
                    ':distance_km' => $distance_km,
                    ':estimated_time' => $estimated_time,
                    ':fare' => $fare,
                    ':status' => $status
                ]);
                
                $success = 'Route added successfully!';
            } catch (PDOException $e) {
                $error = 'Error adding route: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['update_route'])) {
        // Handle update route
        $route_id = intval($_POST['route_id']);
        $start_point = trim($_POST['start_point']);
        $end_point = trim($_POST['end_point']);
        $distance_km = floatval($_POST['distance_km']);
        $estimated_time = trim($_POST['estimated_time']);
        $fare = floatval($_POST['fare']);
        $status = $_POST['status'];
        
        if (empty($start_point) || empty($end_point) || $distance_km <= 0 || empty($estimated_time)) {
            $error = 'Please fill in all required fields with valid data.';
        } else {
            try {
                $sql = "UPDATE routes SET 
                        start_point = :start_point,
                        end_point = :end_point,
                        distance_km = :distance_km,
                        estimated_time = :estimated_time,
                        fare = :fare,
                        status = :status,
                        updated_at = NOW()
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':start_point' => $start_point,
                    ':end_point' => $end_point,
                    ':distance_km' => $distance_km,
                    ':estimated_time' => $estimated_time,
                    ':fare' => $fare,
                    ':status' => $status,
                    ':id' => $route_id
                ]);
                
                $success = 'Route updated successfully!';
            } catch (PDOException $e) {
                $error = 'Error updating route: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_route'])) {
        // Handle delete route
        $route_id = intval($_POST['route_id']);
        
        try {
            $sql = "DELETE FROM routes WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $route_id]);
            
            $success = 'Route deleted successfully!';
        } catch (PDOException $e) {
            $error = 'Error deleting route: ' . $e->getMessage();
        }
    }
}

// Get all routes
$routes = [];
try {
    $sql = "SELECT * FROM routes ORDER BY start_point, end_point";
    $stmt = $pdo->query($sql);
    $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error fetching routes: ' . $e->getMessage();
    error_log($error); // Log the error for debugging
}

// Get all depots for dropdown
$depots = [];
try {
    $sql = "SELECT id, depot_name FROM depots WHERE status = 'active' ORDER BY depot_name";
    $stmt = $pdo->query($sql);
    $depots = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error fetching depots: ' . $e->getMessage();
    error_log($error); // Log the error for debugging
}
?>

<div class="container-fluid px-4" style="margin-top: -1rem;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Routes</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRouteModal">
            <i class="fas fa-plus me-1"></i> Add New Route
        </button>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Routes</h6>
        </div>
        <div class="card-body">
                <table class="table table-bordered" id="routesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Route</th>
                            <th>From - To</th>
                            <th>Distance (km)</th>
                            <th>Fare (LKR)</th>
                            <th>Estimated Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($routes as $route): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($route['start_point']); ?> 
                                    <i class="fas fa-arrow-right mx-2"></i> 
                                    <?php echo htmlspecialchars($route['end_point']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($route['start_point']); ?> 
                                    <i class="fas fa-arrow-right mx-2"></i> 
                                    <?php echo htmlspecialchars($route['end_point']); ?>
                                </td>
                                <td class="text-end"><?php echo number_format($route['distance_km'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($route['fare'], 2); ?></td>
                                <td><?php echo htmlspecialchars($route['estimated_time']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $route['status'] === 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($route['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-route" 
                                            data-id="<?php echo $route['id']; ?>"
                                            data-start="<?php echo htmlspecialchars($route['start_point']); ?>"
                                            data-end="<?php echo htmlspecialchars($route['end_point']); ?>"
                                            data-distance="<?php echo $route['distance_km']; ?>"
                                            data-estimated-time="<?php echo htmlspecialchars($route['estimated_time']); ?>"
                                            data-fare="<?php echo $route['fare']; ?>"
                                            data-status="<?php echo $route['status']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="routes.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this route?');">
                                        <input type="hidden" name="route_id" value="<?php echo $route['id']; ?>">
                                        <button type="submit" name="delete_route" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Route Modal -->
<div class="modal fade" id="addRouteModal" tabindex="-1" aria-labelledby="addRouteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addRouteModalLabel">
                    <i class="fas fa-route me-2"></i>Add New Route
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="routes.php" method="post" class="needs-validation" novalidate>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_point" class="form-label fw-bold">
                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>Start Point
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-flag text-muted"></i></span>
                                    <input type="text" class="form-control" id="start_point" name="start_point" 
                                           placeholder="Enter starting location" required>
                                    <div class="invalid-feedback">
                                        Please provide a starting point.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_point" class="form-label fw-bold">
                                    <i class="fas fa-map-marker-alt me-2 text-danger"></i>End Point
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-flag-checkered text-muted"></i></span>
                                    <input type="text" class="form-control" id="end_point" name="end_point" 
                                           placeholder="Enter destination" required>
                                    <div class="invalid-feedback">
                                        Please provide an end point.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="distance_km" class="form-label fw-bold">
                                    <i class="fas fa-arrows-alt-h me-2 text-info"></i>Distance (km)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-ruler text-muted"></i></span>
                                    <input type="number" step="0.01" class="form-control" id="distance_km" 
                                           name="distance_km" placeholder="0.00" required>
                                    <span class="input-group-text">km</span>
                                    <div class="invalid-feedback">
                                        Please enter a valid distance.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="estimated_time" class="form-label fw-bold">
                                    <i class="far fa-clock me-2 text-warning"></i>Estimated Time
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="far fa-clock text-muted"></i></span>
                                    <input type="text" class="form-control" id="estimated_time" 
                                           name="estimated_time" placeholder="e.g., 2h 30m" required>
                                    <div class="invalid-feedback">
                                        Please provide estimated travel time.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fare" class="form-label fw-bold">
                                    <i class="fas fa-money-bill-wave me-2 text-success"></i>Fare (LKR)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-rupee-sign text-muted"></i></span>
                                    <input type="number" step="0.01" class="form-control" id="fare" 
                                           name="fare" placeholder="0.00" required>
                                    <div class="invalid-feedback">
                                        Please enter a valid fare amount.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="depot_id" class="form-label fw-bold mb-0">
                                        <i class="fas fa-warehouse me-2"></i>Depot
                                    </label>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addDepotModal">
                                        <i class="fas fa-plus me-1"></i> Add New Depot
                                    </button>
                                </div>
                                <select class="form-select" id="depot_id" name="depot_id" required>
                                    <option value="">Select Depot</option>
                                    <?php foreach ($depots as $depot): ?>
                                        <option value="<?php echo $depot['id']; ?>">
                                            <?php echo htmlspecialchars($depot['depot_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status" class="form-label fw-bold">
                                    <i class="fas fa-toggle-on me-2"></i>Status
                                </label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" name="add_route" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> Add Route
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Route Modal -->
<div class="modal fade" id="editRouteModal" tabindex="-1" aria-labelledby="editRouteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editRouteModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Route
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="routes.php" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="route_id" id="edit_route_id">
                <input type="hidden" name="update_route" value="1">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_start_point" class="form-label fw-bold">
                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>Start Point
                                </label>
                                <input type="text" class="form-control" id="edit_start_point" name="start_point" required>
                                <div class="invalid-feedback">Please enter a start point.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_end_point" class="form-label fw-bold">
                                    <i class="fas fa-map-marker-alt me-2 text-danger"></i>End Point
                                </label>
                                <input type="text" class="form-control" id="edit_end_point" name="end_point" required>
                                <div class="invalid-feedback">Please enter an end point.</div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_distance_km" class="form-label fw-bold">
                                    <i class="fas fa-arrows-alt-h me-2 text-info"></i>Distance (km)
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="edit_distance_km" name="distance_km" step="0.01" min="0.1" required>
                                    <span class="input-group-text">km</span>
                                    <div class="invalid-feedback">Please enter a valid distance.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_estimated_time" class="form-label fw-bold">
                                    <i class="fas fa-clock me-2 text-warning"></i>Estimated Time
                                </label>
                                <input type="text" class="form-control" id="edit_estimated_time" name="estimated_time" placeholder="e.g., 2 hours 30 mins" required>
                                <div class="invalid-feedback">Please enter estimated time.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_fare" class="form-label fw-bold">
                                    <i class="fas fa-tag me-2 text-success"></i>Fare (LKR)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" class="form-control" id="edit_fare" name="fare" step="0.01" min="0" required>
                                    <div class="invalid-feedback">Please enter a valid fare amount.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_status" class="form-label fw-bold">
                                    <i class="fas fa-toggle-on me-2 text-primary"></i>Status
                                </label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <div class="invalid-feedback">Please select a status.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Depot Modal -->
<div class="modal fade" id="addDepotModal" tabindex="-1" aria-labelledby="addDepotModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addDepotModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add New Depot
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addDepotForm" class="needs-validation" novalidate>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="depot_name" class="form-label">Depot Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="depot_name" name="depot_name" required>
                        <div class="invalid-feedback">Please enter depot name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="manager_name" class="form-label">Manager Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="manager_name" name="manager_name" required>
                        <div class="invalid-feedback">Please enter manager name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="depot_email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="depot_email" name="email" required>
                        <div class="invalid-feedback">Please enter a valid email.</div>
                    </div>
                    <div class="mb-3">
                        <label for="depot_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="depot_phone" name="phone" required>
                        <div class="invalid-feedback">Please enter phone number.</div>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="location" name="location" required>
                        <div class="invalid-feedback">Please enter depot location.</div>
                    </div>
                    <div class="mb-3">
                        <label for="depot_address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="depot_address" name="address" rows="2" required></textarea>
                        <div class="invalid-feedback">Please enter depot address.</div>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" required>
                        <div class="form-text">Username must be unique and can only contain letters, numbers, and underscores.</div>
                        <div class="invalid-feedback">Please enter a valid username.</div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Password must be at least 6 characters long.</div>
                        <div class="invalid-feedback">Please enter a password.</div>
                    </div>
                    <div id="depotFormMessage" class="alert d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Depot
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add form validation script -->
<script>
// Enable form validation
(function () {
    'use strict'
    
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll('.needs-validation')
    
    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()

// Auto-calculate fare based on distance (example: 10 LKR per km)
document.getElementById('distance_km').addEventListener('input', function() {
    const distance = parseFloat(this.value) || 0;
    const fareInput = document.getElementById('fare');
    // Calculate fare (10 LKR per km as an example)
    const fare = (distance * 10).toFixed(2);
    fareInput.value = fare;
});
</script>

<!-- Edit Route Modal -->
<div class="modal fade" id="editRouteModal" tabindex="-1" aria-labelledby="editRouteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="routes.php" method="post">
                <input type="hidden" name="route_id" id="edit_route_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRouteModalLabel">Edit Route</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Route name field removed as it's not in the database schema -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_start_point" class="form-label">Start Point</label>
                                <input type="text" class="form-control" id="edit_start_point" name="start_point" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_end_point" class="form-label">End Point</label>
                                <input type="text" class="form-control" id="edit_end_point" name="end_point" required>
                            </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_fare" class="form-label">Fare (LKR)</label>
                                <input type="number" step="0.01" class="form-control" id="edit_fare" name="fare" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_depot_id" class="form-label">Depot</label>
                        <select class="form-select" id="edit_depot_id" name="depot_id" required>
                            <?php foreach ($depots as $depot): ?>
                                <option value="<?php echo $depot['id']; ?>"><?php echo htmlspecialchars($depot['depot_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_route" class="btn btn-primary">Update Route</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for handling edit functionality -->
<script>
// Toggle password visibility
document.addEventListener('click', function(e) {
    if (e.target.closest('.toggle-password')) {
        const button = e.target.closest('.toggle-password');
        const input = button.previousElementSibling;
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
});

// Handle Add Depot Form Submission
document.getElementById('addDepotForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const messageDiv = document.getElementById('depotFormMessage');
    
    // Reset message and validation
    messageDiv.className = 'alert d-none';
    form.classList.remove('was-validated');
    
    // Validate username format
    const username = formData.get('username');
    if (username && !/^[a-zA-Z0-9_]+$/.test(username)) {
        const usernameInput = form.querySelector('[name="username"]');
        usernameInput.setCustomValidity('Username can only contain letters, numbers, and underscores.');
        usernameInput.reportValidity();
        form.classList.add('was-validated');
        return false;
    }
    
    // Validate password length
    const password = formData.get('password');
    if (password && password.length < 6) {
        const passwordInput = form.querySelector('[name="password"]');
        passwordInput.setCustomValidity('Password must be at least 6 characters long.');
        passwordInput.reportValidity();
        form.classList.add('was-validated');
        return false;
    }
    
    // Check form validity
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }
    
    // Add action to form data
    formData.append('action', 'add_depot');
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
    
    // Send AJAX request
    fetch('depot_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            messageDiv.className = 'alert alert-success';
            messageDiv.textContent = data.message || 'Depot added successfully!';
            
            // Add new depot to the dropdown
            const depotSelect = document.getElementById('depot_id');
            const newOption = new Option(data.depot_name + ' - ' + data.location, data.depot_id, true, true);
            depotSelect.add(newOption, undefined);
            
            // Also update the edit modal's dropdown if it exists
            const editDepotSelect = document.getElementById('edit_depot_id');
            if (editDepotSelect) {
                const editNewOption = new Option(data.depot_name + ' - ' + data.location, data.depot_id);
                editDepotSelect.add(editNewOption, undefined);
            }
            
            // Reset form
            form.reset();
            
            // Close the modal after 1.5 seconds
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('addDepotModal'));
                modal.hide();
                
                // Reset form state
                setTimeout(() => {
                    messageDiv.className = 'alert d-none';
                    messageDiv.textContent = '';
                }, 500);
            }, 1500);
        } else {
            // Show error message
            messageDiv.className = 'alert alert-danger';
            messageDiv.textContent = data.message || 'Error adding depot. Please try again.';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        messageDiv.className = 'alert alert-danger';
        messageDiv.textContent = 'An error occurred. Please try again.';
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
});

document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable if jQuery is available
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.DataTable === 'function') {
            $('#routesTable').DataTable({
                "order": [[0, 'asc']],
                "pageLength": 10,
                "responsive": true
            });
        }
        
        // Handle edit route button click
        document.addEventListener('click', function(e) {
            if (e.target && e.target.closest('.edit-route')) {
                const btn = e.target.closest('.edit-route');
                
                // Get all data attributes
                const routeId = btn.getAttribute('data-id');
                const startPoint = btn.getAttribute('data-start');
                const endPoint = btn.getAttribute('data-end');
                const distance = btn.getAttribute('data-distance');
                const estimatedTime = btn.getAttribute('data-estimated-time');
                const fare = btn.getAttribute('data-fare');
                const status = btn.getAttribute('data-status');
                
                console.log('Edit button clicked', { routeId, startPoint, endPoint, distance, estimatedTime, fare, status });
                
                // Set values in the edit form
                document.getElementById('edit_route_id').value = routeId || '';
                document.getElementById('edit_start_point').value = startPoint || '';
                document.getElementById('edit_end_point').value = endPoint || '';
                document.getElementById('edit_distance_km').value = distance || '';
                document.getElementById('edit_estimated_time').value = estimatedTime || '';
                document.getElementById('edit_fare').value = fare || '';
                
                // Set the status dropdown
                const statusSelect = document.getElementById('edit_status');
                if (statusSelect) {
                    statusSelect.value = status || 'active';
                }
                
                // Show the edit modal using Bootstrap 5
                const editModalElement = document.getElementById('editRouteModal');
                if (editModalElement && typeof bootstrap !== 'undefined') {
                    const editModal = new bootstrap.Modal(editModalElement);
                    editModal.show();
                } else {
                    console.error('Bootstrap Modal or edit modal element not found');
                }
            }
        });
        
        // Auto-calculate fare based on distance (example calculation)
        function setupFareCalculation(inputId, fareId) {
            const input = document.getElementById(inputId);
            const fareInput = document.getElementById(fareId);
            
            if (input && fareInput) {
                input.addEventListener('input', function() {
                    const distance = parseFloat(this.value) || 0;
                    // Example: 10 LKR per km as base fare
                    const baseFare = distance * 10;
                    // Round to 2 decimal places
                    const fare = Math.round(baseFare * 100) / 100;
                    fareInput.value = fare.toFixed(2);
                });
            }
        }
        
        // Setup fare calculation for both add and edit forms
        if (document.getElementById('distance') && document.getElementById('fare')) {
            setupFareCalculation('distance', 'fare');
        }
        if (document.getElementById('edit_distance_km') && document.getElementById('edit_fare')) {
            setupFareCalculation('edit_distance_km', 'edit_fare');
        }
    });
</script>

<?php include_once '../includes/footer.php'; ?>
