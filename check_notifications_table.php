<?php
// Quick script to check the notifications table structure
include "application/conn.php";

header('Content-Type: application/json; charset=utf-8');

$response = [];

// Check connection
if (!$conn) {
    $response['error'] = 'Database connection failed';
    echo json_encode($response);
    exit;
}

// Check if notifications table exists
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
if (mysqli_num_rows($tableCheck) == 0) {
    $response['error'] = 'notifications table does not exist';
    $response['solution'] = 'Run fix_database.php to create the table';
} else {
    $response['table_exists'] = true;
    
    // Get table structure
    $columns = mysqli_query($conn, "DESCRIBE notifications");
    $response['columns'] = [];
    while ($col = mysqli_fetch_assoc($columns)) {
        $response['columns'][] = $col['Field'];
    }
    
    // Get sample data count
    $countResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM notifications");
    if ($countResult) {
        $count = mysqli_fetch_assoc($countResult);
        $response['total_records'] = $count['total'];
    }
    
    // Get sample record
    $sampleResult = mysqli_query($conn, "SELECT * FROM notifications LIMIT 1");
    if ($sampleResult && mysqli_num_rows($sampleResult) > 0) {
        $response['sample_record'] = mysqli_fetch_assoc($sampleResult);
    } else {
        $response['sample_record'] = 'No records found';
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>

