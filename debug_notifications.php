<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "application/conn.php";

$response = [
    'test' => 'Debug Information',
    'timestamp' => date('Y-m-d H:i:s')
];

// 1. Check database connection
if (!$conn) {
    $response['db_connection'] = 'FAILED';
    $response['db_error'] = mysqli_connect_error();
} else {
    $response['db_connection'] = 'SUCCESS';
    $response['db_name'] = DB_NAME;
}

// 2. Check if notifications table exists
if ($conn) {
    $tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
    if (!$tableCheck) {
        $response['table_check'] = 'Query failed: ' . mysqli_error($conn);
    } else if (mysqli_num_rows($tableCheck) == 0) {
        $response['table_exists'] = 'NO - Table does not exist';
        $response['solution'] = 'Run fix_database.php to create the table';
    } else {
        $response['table_exists'] = 'YES';
        
        // 3. Get table structure
        $columns = mysqli_query($conn, "DESCRIBE notifications");
        if ($columns) {
            $response['table_columns'] = [];
            while ($col = mysqli_fetch_assoc($columns)) {
                $response['table_columns'][] = $col['Field'];
            }
        }
        
        // 4. Count records
        $countResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM notifications");
        if ($countResult) {
            $count = mysqli_fetch_assoc($countResult);
            $response['total_records'] = $count['total'];
        }
        
        // 5. Get sample record
        $sampleResult = mysqli_query($conn, "SELECT * FROM notifications LIMIT 1");
        if ($sampleResult && mysqli_num_rows($sampleResult) > 0) {
            $response['sample_record'] = mysqli_fetch_assoc($sampleResult);
        } else {
            $response['sample_record'] = 'No records in table';
        }
    }
}

// 6. Test query that GetSiteMessageList uses
if ($conn && isset($response['table_exists']) && $response['table_exists'] == 'YES') {
    $testSql = "SELECT * FROM notifications WHERE status = 1 ORDER BY created_at DESC LIMIT 0, 10";
    $testResult = mysqli_query($conn, $testSql);
    if ($testResult === false) {
        $response['test_query'] = 'FAILED';
        $response['test_query_error'] = mysqli_error($conn);
    } else {
        $response['test_query'] = 'SUCCESS';
        $response['test_query_rows'] = mysqli_num_rows($testResult);
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>

