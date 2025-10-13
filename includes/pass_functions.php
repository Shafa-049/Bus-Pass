<?php

/**
 * Check if a pass is currently valid
 * @param int $pass_id The ID of the pass to check
 * @return bool True if the pass is valid, false otherwise
 */
function isPassValid($pass_id) {
    global $pdo;
    
    $sql = "SELECT id 
            FROM bus_passes 
            WHERE id = ? 
            AND status = 'active' 
            AND expiry_date >= CURDATE()";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pass_id]);
    return $stmt->rowCount() > 0;
}

/**
 * Get pass status with expiration information
 * @param int $pass_id The ID of the pass
 * @return array Pass information including status and days remaining
 */
function getPassStatus($pass_id) {
    global $pdo;
    
    $sql = "SELECT 
                id, status, issue_date, expiry_date,
                DATEDIFF(expiry_date, CURDATE()) as days_remaining
            FROM bus_passes 
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pass_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
