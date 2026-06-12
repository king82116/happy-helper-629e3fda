<?php
include('conn.php');

// Bonus tables configuration
$bonusTables = [
    3   => ['table' => 'hodike_balakedara', 'name' => 'Red envelope'],
    8   => ['table' => 'agent_red_envelope_recharge_table', 'name' => 'Agent red envelope recharge'],
    10  => ['table' => 'recharge_gift_table', 'name' => 'Recharge gift'],
    13  => ['table' => 'bonus_recharge_table', 'name' => 'Bonus recharge'],
    14  => ['table' => 'first_full_gift_table', 'name' => 'First full gift'],
    20  => ['table' => 'invite_bonus_table', 'name' => 'Invite bonus'],
    25  => ['table' => 'card_binding_gift_table', 'name' => 'Card binding gift'],
    107 => ['table' => 'weekly_awards_table', 'name' => 'Weekly Awards'],
    124 => ['table' => 'agent_bonus_table', 'name' => 'Agent Bonus'],
    118 => ['table' => 'daily_awards_table', 'name' => 'Daily Awards'],
    117 => ['table' => 'new_members_bonus_table', 'name' => 'New members bonus'],
    115 => ['table' => 'return_awards_table', 'name' => 'Return Awards']
];

// Logging function with error handling
function logMessage($message, $type = 'info') {
    $logFile = 'C:\\xampp\\htdocs\\Dev_AK7\\bonus_cron.log';
    $timestamp = date('[Y-m-d H:i:s] ');
    
    // Ensure log directory exists
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    // Prepare log message
    $logEntry = $timestamp . strtoupper($type) . ': ' . $message . PHP_EOL;
    
    // Append to log file
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    // Also output to console/error log for immediate visibility
    if ($type === 'error') {
        error_log($logEntry);
    }
}

// Function to add processed column if not exists
function addProcessedColumn($conn, $table) {
    try {
        // First, check if the column exists
        $checkQuery = "SHOW COLUMNS FROM `$table` LIKE 'processed'";
        $result = mysqli_query($conn, $checkQuery);
        
        // If column doesn't exist, add it
        if (mysqli_num_rows($result) == 0) {
            $alterQuery = "ALTER TABLE `$table` ADD COLUMN processed TINYINT(1) DEFAULT 0";
            if (mysqli_query($conn, $alterQuery)) {
                logMessage("Added 'processed' column to $table", 'info');
                return true;
            } else {
                logMessage("Failed to add 'processed' column to $table: " . mysqli_error($conn), 'error');
                return false;
            }
        }
        
        return true;
    } catch (Exception $e) {
        logMessage("Exception in addProcessedColumn: " . $e->getMessage(), 'error');
        return false;
    }
}

// Main processing function
function processBonusTable($conn, $bonusType, $tableInfo) {
    $table = $tableInfo['table'];
    $name = $tableInfo['name'];

    // Ensure processed column exists
    if (!addProcessedColumn($conn, $table)) {
        logMessage("Skipping $table due to column addition failure", 'error');
        return false;
    }

    logMessage("Processing Bonus Type: $name ($bonusType)", 'info');

    // Modify query to handle tables without processed column
    $query = "SELECT * FROM `$table` WHERE 1=1 LIMIT 100";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        logMessage("Error querying $table: " . mysqli_error($conn), 'error');
        return false;
    }

    $processedCount = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        // Start transaction
        mysqli_begin_transaction($conn);

        try {
            // Check if this bonus has already been processed in userbonus table
            $checkQuery = "SELECT COUNT(*) as count FROM tracking_userbonus 
                           WHERE userkani = ? AND price = ? AND bonus_type = ?";
            $checkStmt = mysqli_prepare($conn, $checkQuery);
            
            // Ensure userkani is converted to integer
            $userkani = intval($row['userkani']);
            $price = floatval($row['price']);
            
            mysqli_stmt_bind_param($checkStmt, "idi", $userkani, $price, $bonusType);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            $checkRow = mysqli_fetch_assoc($checkResult);

            if ($checkRow['count'] > 0) {
                logMessage("Bonus already processed for user {$row['userkani']} with type $bonusType", 'info');
                mysqli_stmt_close($checkStmt);
                mysqli_rollback($conn);
                continue;
            }

            // Update user balance
            $updateBalanceQuery = "UPDATE shonu_kaichila 
                                   SET motta = motta + ? 
                                   WHERE balakedara = ?";
            $updateStmt = mysqli_prepare($conn, $updateBalanceQuery);
            mysqli_stmt_bind_param($updateStmt, "di", $price, $userkani);
            
            if (!mysqli_stmt_execute($updateStmt)) {
                throw new Exception("Failed to update balance for user {$row['userkani']}");
            }

            // Insert into tracking_userbonus
            $trackingQuery = "INSERT INTO tracking_userbonus 
                              (userkani, price, serial, shonu, remark, ordernumber, bonus_type) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)";
            $trackingStmt = mysqli_prepare($conn, $trackingQuery);
            $serial = "Cron_" . time();
            $shonu = date('Y-m-d H:i:s');
            $remark = "Processed $name bonus";
            $orderNumber = uniqid('BONUS_');
            
            mysqli_stmt_bind_param($trackingStmt, "idssssi", 
                $userkani, 
                $price, 
                $serial, 
                $shonu, 
                $remark, 
                $orderNumber, 
                $bonusType
            );

            if (!mysqli_stmt_execute($trackingStmt)) {
                throw new Exception("Failed to insert into tracking_userbonus");
            }

            // Attempt to mark as processed, but don't fail if column doesn't exist
            $markProcessedQuery = "UPDATE `$table` SET processed = 1 WHERE id = ?";
            $markStmt = mysqli_prepare($conn, $markProcessedQuery);
            mysqli_stmt_bind_param($markStmt, "i", $row['id']);
            
            mysqli_stmt_execute($markStmt);  // Don't throw error if this fails

            // Commit transaction
            mysqli_commit($conn);
            $processedCount++;
            logMessage("Processed bonus for user {$row['userkani']} from $name", 'info');

        } catch (Exception $e) {
            // Rollback transaction
            mysqli_rollback($conn);
            logMessage("Error processing bonus: " . $e->getMessage(), 'error');
        }
    }

    logMessage("Processed $processedCount entries for $name", 'info');
    return true;
}

// Main execution
function runBonusCron($conn) {
    global $bonusTables;

    foreach ($bonusTables as $bonusType => $tableInfo) {
        processBonusTable($conn, $bonusType, $tableInfo);
    }

    logMessage("Bonus Cron Job Completed", 'info');
}

// Execute the cron job
runBonusCron($conn);

// Close connection
mysqli_close($conn);
?>