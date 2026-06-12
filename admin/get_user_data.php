<?php
include('conn.php');
session_start();

if(!isset($_SESSION['unohs'])) {
    http_response_code(401);
    exit('Unauthorized');
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Performance optimizations
set_time_limit(30);
ini_set('memory_limit', '256M');
mysqli_query($conn, "SET SESSION wait_timeout=30");
mysqli_query($conn, "SET SESSION interactive_timeout=30");
mysqli_query($conn, "SET SESSION sql_mode=''");

// Date calculations
$tem = date("Y-m-d H:i:s");
$timestamp = strtotime($tem);
$newTimestamp = $timestamp - 60 * 60 * 24;
$startDate = date("Y-m-d 00:00:00", $newTimestamp);
$endDate = date("Y-m-d H:i:s", $timestamp);
$newDate = date("Y-m-d", $newTimestamp);

if(isset($_POST['user_id'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    
    // Get main user's details
    $user_query = "SELECT id, owncode FROM shonu_subjects WHERE id = '$user_id'";
    $user_result = mysqli_query($conn, $user_query);
    
    if(!$user_result) {
        error_log("Database error: " . mysqli_error($conn));
        http_response_code(500);
        exit("Database error occurred");
    }
    
    $user = mysqli_fetch_assoc($user_result);
    if(!$user) {
        exit("<tr><td colspan='11' class='text-center'>User not found</td></tr>");
    }
    
    $output = '';
    $owncode = $user['owncode'];
    
    // Get direct deposit
    $direct_deposit = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COALESCE(SUM(motta), 0) as total FROM thevani 
         WHERE balakedara = '$user_id' AND sthiti = '1' AND DATE(dinankavannuracisi) = '$newDate'"))['total'];
    
    // Get team deposit
    $team_deposit = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COALESCE(SUM(t.motta), 0) as total 
         FROM thevani t 
         JOIN shonu_subjects s ON t.balakedara = s.id 
         WHERE s.code = '$owncode' AND t.sthiti = '1' AND DATE(t.dinankavannuracisi) = '$newDate'"))['total'];
    
    // Get direct withdraw
    $direct_withdraw = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COALESCE(SUM(motta), 0) as total 
         FROM hintegedukolli 
         WHERE balakedara = '$user_id' AND sthiti = 1 AND DATE(dinankavannuracisi) = '$newDate'"))['total'];
    
    // Get team withdraw
    $team_withdraw = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COALESCE(SUM(h.motta), 0) as total 
         FROM hintegedukolli h 
         JOIN shonu_subjects s ON h.balakedara = s.id 
         WHERE s.code = '$owncode' AND h.sthiti = 1 AND DATE(h.dinankavannuracisi) = '$newDate'"))['total'];
    
    // Get total referrals
    $total_refer = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COUNT(id) as total FROM shonu_subjects WHERE code = '$owncode'"))['total'];
    
    // Calculate P/L
    $pl = ($direct_deposit + $team_deposit) - ($direct_withdraw + $team_withdraw);
    
    $output .= "<tr>
        <td>{$user_id} <small class='text-muted'>(Refs: {$total_refer})</small></td>
        <td>" . number_format($direct_deposit, 2) . "</td>
        <td>" . number_format($team_deposit, 2) . "</td>
        <td>" . number_format($direct_deposit, 2) . "</td>
        <td>" . number_format($team_deposit, 2) . "</td>
        <td>{$total_refer}</td>
        <td>" . number_format($direct_withdraw, 2) . "</td>
        <td>" . number_format($team_withdraw, 2) . "</td>
        <td>" . number_format($pl, 2) . "</td>
        <td>0.00 <a href='javascript:void(0);' onClick='edita({$user_id},{$user_id},0)' class='text-aqua' title='Salary'><i class='fa fa-edit'></i></a></td>
        <td><a href='javascript:void(0);' onClick='edit({$user_id},{$user_id},0)' class='text-aqua' title='Approve'>Pay</a></td>
    </tr>";
    
    echo $output;
} else {
    http_response_code(400);
    exit("<tr><td colspan='11' class='text-center'>Invalid request</td></tr>");
}
?> 