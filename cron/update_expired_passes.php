<?php
require_once __DIR__ . '/../includes/db_connect.php';

// Update passes that have expired
$sql = "UPDATE bus_passes 
        SET status = 'expired' 
        WHERE status = 'active' 
        AND expiry_date < CURDATE()";

$updated = $pdo->exec($sql);

// Log the update
error_log("[" . date('Y-m-d H:i:s') . "] Updated $updated passes to expired status");

echo "Updated $updated passes to expired status\n";
