<?php
include('conn.php');
session_start();

// Check if user is logged in
if(!isset($_SESSION['unohs'])) {
    http_response_code(401);
    echo "Unauthorized access";
    exit;
}

// Verify that this is a POST request with the required data
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['action']) && $_POST['action'] === 'approve_salary') {
    
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // First verify if there's a pending salary for this user
        $check_query = "SELECT userid FROM dailysalary WHERE userid = ? AND status = 0";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "s", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) === 0) {
            throw new Exception("No pending salary found for this user");
        }
        
        // Update salary status
        $update_query = "UPDATE dailysalary SET status = 1, approved_date = NOW() WHERE userid = ? AND status = 0";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "s", $user_id);
        
        if(!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error approving salary: " . mysqli_error($conn));
        }
        
        // If everything is successful, commit the transaction
        mysqli_commit($conn);
        echo "success";
        
    } catch (Exception $e) {
        // If there's an error, rollback the transaction
        mysqli_rollback($conn);
        http_response_code(500);
        echo $e->getMessage();
    }
    
} else {
    http_response_code(400);
    echo "Invalid request";
}
?> 