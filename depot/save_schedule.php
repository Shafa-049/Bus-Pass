<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Check if user is logged in and is a depot user
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'depot') {
    header("location: ../auth/login.php");
    exit;
}

$depot_id = $_SESSION['depot_id'] ?? 0;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate required fields
        $required = ['route_id', 'departure_time', 'arrival_time'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("All fields are required.");
            }
        }

        // Validate days
        if (empty($_POST['days']) || !is_array($_POST['days'])) {
            throw new Exception("Please select at least one day of operation.");
        }

        // Prepare schedule data
        $schedule_data = [
            'route_id' => (int)$_POST['route_id'],
            'departure_time' => $_POST['departure_time'],
            'arrival_time' => $_POST['arrival_time'],
            'monday' => in_array('monday', $_POST['days']) ? 1 : 0,
            'tuesday' => in_array('tuesday', $_POST['days']) ? 1 : 0,
            'wednesday' => in_array('wednesday', $_POST['days']) ? 1 : 0,
            'thursday' => in_array('thursday', $_POST['days']) ? 1 : 0,
            'friday' => in_array('friday', $_POST['days']) ? 1 : 0,
            'saturday' => in_array('saturday', $_POST['days']) ? 1 : 0,
            'sunday' => in_array('sunday', $_POST['days']) ? 1 : 0,
            'notes' => !empty($_POST['notes']) ? trim($_POST['notes']) : null,
            'status' => 'active'
        ];

        // Convert time strings to proper datetime format using current date
        $current_date = date('Y-m-d');
        $schedule_data['departure_time'] = $current_date . ' ' . $schedule_data['departure_time'] . ':00';
        $schedule_data['arrival_time'] = $current_date . ' ' . $schedule_data['arrival_time'] . ':00';

        // Check for overlapping schedules (simplified for the current structure)
        $overlap_sql = "SELECT id FROM bus_schedules 
                        WHERE route_id = ? 
                        AND status = 'active' 
                        AND id != ? 
                        AND (
                            (departure_time <= ? AND arrival_time >= ?)
                            OR (departure_time <= ? AND arrival_time >= ?)
                            OR (departure_time >= ? AND arrival_time <= ?)
                        )";
        
        $overlap_params = [
            $schedule_data['route_id'],
            !empty($_POST['id']) ? (int)$_POST['id'] : 0,
            $schedule_data['departure_time'], $schedule_data['departure_time'],
            $schedule_data['arrival_time'], $schedule_data['arrival_time'],
            $schedule_data['departure_time'], $schedule_data['arrival_time']
        ];

        $stmt = $pdo->prepare($overlap_sql);
        $stmt->execute($overlap_params);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception("This vehicle already has a schedule that overlaps with the specified date and time range.");
        }

        // Update existing schedule
        if (!empty($_POST['id'])) {
            $schedule_id = (int)$_POST['id'];
            
            // Verify ownership
            // Remove depot_id check since it's not in the table
            $stmt = $pdo->prepare("SELECT id FROM bus_schedules WHERE id = ?");
            $stmt->execute([$schedule_id]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception("Schedule not found or you don't have permission to edit it.");
            }
            
            // Update query
            $sql = "UPDATE bus_schedules SET 
                    route_id = :route_id,
                    departure_time = :departure_time,
                    arrival_time = :arrival_time,
                    monday = :monday,
                    tuesday = :tuesday,
                    wednesday = :wednesday,
                    thursday = :thursday,
                    friday = :friday,
                    saturday = :saturday,
                    sunday = :sunday,
                    notes = :notes,
                    status = :status,
                    updated_at = NOW()
                    WHERE id = :id";
            
            $schedule_data['id'] = $schedule_id;
            
            $schedule_data['id'] = $schedule_id;
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute($schedule_data);
            
            if ($success) {
                $_SESSION['success'] = 'Schedule updated successfully.';
            } else {
                throw new Exception("Failed to update schedule. Please try again.");
            }
        } 
        // Create new schedule
        else {
            // Insert query
            $sql = "INSERT INTO bus_schedules (
                        route_id, departure_time, arrival_time, 
                        monday, tuesday, wednesday, thursday, friday, saturday, sunday,
                        notes, status, created_at
                    ) VALUES (
                        :route_id, :departure_time, :arrival_time, 
                        :monday, :tuesday, :wednesday, :thursday, :friday, :saturday, :sunday,
                        :notes, :status, NOW()
                    )";
            
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute($schedule_data);
            
            if ($success) {
                $_SESSION['success'] = 'Schedule created successfully.';
            } else {
                throw new Exception("Failed to create schedule. Please try again.");
            }
        }
        
        header("Location: schedule.php");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        
        // Preserve form data in session for repopulation
        $_SESSION['form_data'] = $_POST;
        
        // Redirect back to form
        if (isset($_POST['id'])) {
            header("Location: manage_schedule.php?id=" . (int)$_POST['id']);
        } else {
            header("Location: manage_schedule.php");
        }
        exit;
    }
} else {
    // If not a POST request, redirect to schedule page
    header("Location: schedule.php");
    exit;
}
