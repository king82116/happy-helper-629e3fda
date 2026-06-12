<?php
// NinePayWithdrawCallback.php - PRODUCTION VERSION (No Logging)

include('../../application/conn.php');

// ✅ Read raw input
$rawInput = file_get_contents('php://input');

// ✅ IMMEDIATELY send response
http_response_code(200);
header('Content-Type: text/plain');
echo "success";
flush();

if (!isset($conn) || !$conn) {
    exit;
}

try {
    $requestData = json_decode($rawInput, true);
    
    if (empty($requestData)) {
        $requestData = $_POST;
    }
    
    if (empty($requestData) || !isset($requestData['data'])) {
        exit;
    }
    
    $data = $requestData['data'];
    
    $merchantOrderNo = trim($data['merchOrderNo'] ?? '');
    $orderState = (string)($data['orderState'] ?? '');
    $receivedSign = $data['sign'] ?? '';
    $msg = $data['msg'] ?? '';
    $amount = $data['amount'] ?? '';
    $gatewayOrderNo = $data['orderNo'] ?? '';
    $refNo = $data['refNo'] ?? '';
    
    if (empty($merchantOrderNo) || empty($receivedSign)) {
        exit;
    }
    
    // ✅ Verify signature
    $md5Key = "ed65572d8f594bbbaabd80e2b0dd9a00";
    
    $signData = $data;
    unset($signData['sign']);
    ksort($signData);
    
    $signStr = "";
    foreach ($signData as $key => $value) {
        $signStr .= $key . "=" . $value . "&";
    }
    $signStr = rtrim($signStr, "&") . $md5Key;
    $calculatedSign = strtolower(md5($signStr));
    
    if (strtolower($receivedSign) !== $calculatedSign) {
        exit;
    }
    
    // ✅ Find withdrawal order
    $merchantOrderNo = mysqli_real_escape_string($conn, $merchantOrderNo);
    
    $orderQuery = mysqli_query($conn, 
        "SELECT * FROM hintegedukolli 
         WHERE dharavahi = '$merchantOrderNo' 
         LIMIT 1"
    );
    
    if (!$orderQuery || mysqli_num_rows($orderQuery) == 0) {
        exit;
    }
    
    $order = mysqli_fetch_assoc($orderQuery);
    
    // Check if already processed
    if ($order['sthiti'] == '1' || $order['sthiti'] == '2') {
        exit;
    }
    
    $withdraw_id = $order['shonu'];
    $user_id = $order['balakedara'];
    $orderAmount = $order['motta'];
    
    // ✅ SUCCESS - orderState = "0"
    if ($orderState === '0') {
        
        $remarksText = !empty($msg) ? $msg : 'Success';
        
        mysqli_query($conn, "
            UPDATE hintegedukolli 
            SET 
                sthiti='1',
                tike='Completed',
                remarks='".mysqli_real_escape_string($conn, $remarksText)."',
                dinankavannuracisi=NOW()
            WHERE shonu='$withdraw_id'
        ");
        
        exit;
    }
    
    // ❌ FAILED - orderState = "1"
    if ($orderState === '1') {
        
        $remarksText = !empty($msg) ? $msg : 'Failed';
        
        mysqli_query($conn, "
            UPDATE hintegedukolli 
            SET 
                sthiti='2',
                tike='Failed',
                remarks='".mysqli_real_escape_string($conn, $remarksText)."',
                dinankavannuracisi=NOW()
            WHERE shonu='$withdraw_id'
        ");
        
        // Refund to wallet
        mysqli_query($conn, "
            UPDATE shonu_kaichila
            SET motta = ROUND(motta + $orderAmount, 2)
            WHERE balakedara='$user_id'
        ");
        
        exit;
    }
    
    // ⏳ PROCESSING - orderState = "2"
    if ($orderState === '2') {
        
        $remarksText = !empty($msg) ? $msg : 'Processing';
        
        mysqli_query($conn, "
            UPDATE hintegedukolli 
            SET remarks='".mysqli_real_escape_string($conn, $remarksText)."'
            WHERE shonu='$withdraw_id'
        ");
        
        exit;
    }
    
    exit;
    
} catch (Exception $e) {
    exit;
}
?>
