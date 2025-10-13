<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Check if user is logged in and is a depot user
requireLogin();
requireRole('depot');

$page_title = 'Edit Application - Depot Dashboard';
$error = '';
$success = '';

// Get application ID from URL
$application_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get depot_id from session or query
if (isset($_SESSION['depot_id'])) {
    $depot_id = $_SESSION['depot_id'];
} else if (isset($_SESSION['selected_depot_id'])) {
    $depot_id = $_SESSION['selected_depot_id'];
} else {
    // If still not set, get the first depot for this user
    $stmt = $pdo->prepare("SELECT id FROM depots WHERE user_id = ? LIMIT 1");
    $stmt->execute([$_SESSION['id']]);
    $depot = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($depot) {
        $depot_id = $depot['id'];
        $_SESSION['depot_id'] = $depot_id;
    } else {
        $_SESSION['error'] = 'No depot assigned to your account';
        header('Location: applications.php');
        exit;
    }
}

if ($application_id <= 0) {
    $_SESSION['error'] = 'Invalid application ID';
    header('Location: applications.php');
    exit;
}

try {
    // Fetch application details
    $stmt = $pdo->prepare("
        SELECT bp.*, s.full_name, s.registration_no, s.faculty, s.department,
               s.email, s.phone, s.address, s.user_id as student_user_id,
               CONCAT(r.start_point, ' to ', r.end_point) as route_display,
               r.start_point, r.end_point, r.distance_km as distance,
               r.fare, d.depot_name
        FROM bus_passes bp
        JOIN students s ON bp.student_id = s.id
        JOIN routes r ON bp.route_id = r.id
        JOIN depots d ON bp.depot_id = d.id
        WHERE bp.id = ? AND bp.depot_id = ?
    ");
    
    $stmt->execute([$application_id, $depot_id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        throw new Exception('Application not found or access denied');
    }
    
    // Only allow editing of pending applications
    if ($application['status'] !== 'pending') {
        throw new Exception('Only pending applications can be edited');
    }
    
    // Fetch available routes for dropdown
    $routes = $pdo->query("SELECT id, CONCAT(start_point, ' to ', end_point) as route_name FROM routes WHERE depot_id = $depot_id")->fetchAll();
    
    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $route_id = isset($_POST['route_id']) ? intval($_POST['route_id']) : 0;
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // Validate input
        if (empty($route_id) || empty($start_date) || empty($end_date)) {
            throw new Exception('All fields are required');
        }
        
        // Update application
        $stmt = $pdo->prepare("
            UPDATE bus_passes 
            SET route_id = ?, start_date = ?, end_date = ?, notes = ?, updated_at = NOW()
            WHERE id = ? AND depot_id = ?
        ");
        
        $updated = $stmt->execute([
            $route_id,
            $start_date,
            $end_date,
            $notes,
            $application_id,
            $depot_id
        ]);
        
        if ($updated) {
            // Log the action
            $stmt = $pdo->prepare("
                INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details) 
                VALUES (?, 'update', 'bus_pass', ?, ?)
            ");
            $details = json_encode([
                'application_id' => $application_id,
                'changes' => [
                    'route_id' => $application['route_id'] . ' → ' . $route_id,
                    'start_date' => $application['start_date'] . ' → ' . $start_date,
                    'end_date' => $application['end_date'] . ' → ' . $end_date
                ]
            ]);
            $stmt->execute([$_SESSION['user_id'], $application_id, $details]);
            
            $_SESSION['success'] = 'Application updated successfully';
            header('Location: view_application.php?id=' . $application_id);
            exit;
        } else {
            throw new Exception('Failed to update application');
        }
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

include '../includes/depot_header.php';
?>

<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit Application #<?php echo $application_id; ?></h1>
        <a href="view_application.php?id=<?php echo $application_id; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Application
        </a>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Application Details</h5>
        </div>
        <div class="card-body">
            <form method="post" id="editApplicationForm">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Student Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($application['full_name']); ?>" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Registration No</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($application['registration_no']); ?>" disabled>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="route_id" class="form-label">Route</label>
                            <select class="form-select" id="route_id" name="route_id" required>
                                <option value="">Select a route</option>
                                <?php foreach ($routes as $route): ?>
                                    <option value="<?php echo $route['id']; ?>" 
                                        <?php echo $route['id'] == $application['route_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($route['route_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Current Status</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo ucfirst(htmlspecialchars($application['status'])); ?>" 
                                   disabled>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?php echo !empty($application['start_date']) ? date('Y-m-d', strtotime($application['start_date'])) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?php echo !empty($application['end_date']) ? date('Y-m-d', strtotime($application['end_date'])) : ''; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($application['notes'] ?? ''); ?></textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-danger" 
                            onclick="if(confirm('Are you sure you want to delete this application?')) { window.location.href='delete_application.php?id=<?php echo $application_id; ?>'; }">
                        <i class="fas fa-trash me-1"></i> Delete Application
                    </button>
                    
                    <div>
                        <a href="view_application.php?id=<?php echo $application_id; ?>" class="btn btn-secondary me-2">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('editApplicationForm');
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    startDate.min = today;
    
    // Update end date min date when start date changes
    startDate.addEventListener('change', function() {
        endDate.min = this.value;
        if (endDate.value && new Date(endDate.value) < new Date(this.value)) {
            endDate.value = this.value;
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>

<?php include '../includes/depot_footer.php'; ?>
