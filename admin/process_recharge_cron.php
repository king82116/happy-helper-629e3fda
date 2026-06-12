<?php
include('conn.php');

// Set script execution time
set_time_limit(300); // 5 minutes
ini_set('memory_limit', '256M');

// Log function
function logMessage($message) {
    $date = date('Y-m-d H:i:s');
    echo "[$date] $message\n";
    file_put_contents('cron_log.txt', "[$date] $message\n", FILE_APPEND);
}

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // Get unprocessed records
    $query = "SELECT id, userkani, price, serial, shonu, remark 
              FROM agent_red_envelope_recharge_table 
              WHERE processed = 0 
              LIMIT 100";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception("Error fetching records: " . mysqli_error($conn));
    }

    $processed_count = 0;
    $failed_count = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        try {
            // Update user balance in shonu_kaichila
            $update_query = "UPDATE shonu_kaichila 
                           SET motta = motta + ? 
                           WHERE kramasankhye = ?";
            
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "ds", $row['price'], $row['userkani']);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to update balance for user {$row['userkani']}: " . mysqli_error($conn));
            }

            // Mark record as processed
            $mark_processed = "UPDATE agent_red_envelope_recharge_table 
                             SET processed = 1 
                             WHERE id = ?";
            
            $stmt = mysqli_prepare($conn, $mark_processed);
            mysqli_stmt_bind_param($stmt, "i", $row['id']);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to mark record {$row['id']} as processed: " . mysqli_error($conn));
            }

            $processed_count++;
            logMessage("Successfully processed ID: {$row['id']}, User: {$row['userkani']}, Amount: {$row['price']}");

        } catch (Exception $e) {
            $failed_count++;
            logMessage("Error processing record ID {$row['id']}: " . $e->getMessage());
            continue;
        }
    }

    // Commit transaction
    mysqli_commit($conn);
    
    logMessage("Cron completed. Processed: $processed_count, Failed: $failed_count");

} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($conn);
    logMessage("Critical Error: " . $e->getMessage());
}

// Close connection
mysqli_close($conn);
?> 