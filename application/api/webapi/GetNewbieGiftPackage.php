<?php
    include "../../conn.php";  // Include your database connection file
    include "../../functions2.php";  // Include your functions file

    // Set headers
    header('Content-Type: application/json; charset=utf-8');
    header('Strict-Transport-Security: max-age=31536000');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
    header('Access-Control-Allow-Credentials: true');
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    header('Access-Control-Allow-Origin: ' . $origin);
    header('vary: Origin');
    
    // Get the current time in Asia/Kolkata timezone
    date_default_timezone_set("Asia/Kolkata");
    $shnunc = date("Y-m-d H:i:s");

    // Initialize the response array
    $response = [
        'code' => 7,
        'msg' => 'Invalid parameters',
        'msgCode' => 6,
        'serviceNowTime' => $shnunc,
    ];

    // Read raw POST data (in case you need it for other logic)
    $shonubody = file_get_contents("php://input");
    $shonupost = json_decode($shonubody, true);
    
    // Process the logic to fetch the gift package details
    // In a real-world scenario, you would likely fetch this from a database, but for now, I am hardcoding the response
    
    // For demo purposes, hardcoding the gift package details
    $giftPackage = [
        'id' => 0,
        'title' => 'Newbie gift pack',
        'description' => 'Newbie Gift Pack',
        'amount' => 14.0,
        'status' => 0,
        'receivedNumber' => 0,
        'totalNumber' => 7
    ];

    // Set success response
    $response['data'] = $giftPackage;
    $response['code'] = 0;
    $response['msg'] = 'Succeed';
    $response['msgCode'] = 0;
    $response['serviceNowTime'] = $shnunc;

    // Send the JSON response with a 200 status code
    http_response_code(200);
    echo json_encode($response);
?>
