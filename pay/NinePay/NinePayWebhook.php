<?php
// NinePayWebhook.php - PRODUCTION READY VERSION

include("../../serive/samparka.php");

// Logging function
function webhookLog($message, $data = null) {
    $logMsg = date('Y-m-d H:i:s') . " - " . $message;
    if ($data !== null) {
        $logMsg .= "\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    $logMsg .= "\n" . str_repeat("─", 60) . "\n\n";
    @file_put_contents(__DIR__ . '/webhook_detailed.log', $logMsg, FILE_APPEND);
}

webhookLog("📥 WEBHOOK RECEIVED", [
    'POST' => $_POST,
    'GET' => $_GET,
    'Raw' => file_get_contents('php://input')
]);

try {
    // ✅ FIX: Parse raw JSON input (NinePay sends JSON, not form data)
    $rawInput = file_get_contents('php://input');
    $requestData = json_decode($rawInput, true);
    
    if (empty($requestData)) {
        webhookLog("❌ No data received");
        echo "success";
        exit;
    }
    
    if (!isset($requestData['data'])) {
        webhookLog("❌ No data key");
        echo "success";
        exit;
    }
    
    $data = $requestData['data'];
    
    $merchantOrderNo = $data['merchOrderNo'] ?? '';
    $orderState = $data['orderState'] ?? '';
    $receivedSign = $data['sign'] ?? '';
    
    webhookLog("✅ Data extracted", [
        'Order' => $merchantOrderNo,
        'State' => $orderState,
        'State Type' => gettype($orderState)
    ]);
    
    if (empty($merchantOrderNo) || empty($receivedSign)) {
        webhookLog("❌ Missing fields");
        echo "success";
        exit;
    }
    
    // Verify signature
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
    
    webhookLog("✅ Signature check", [
        'String' => $signStr,
        'Calculated' => $calculatedSign,
        'Received' => strtolower($receivedSign),
        'Match' => (strtolower($receivedSign) === $calculatedSign)
    ]);
    
    if (strtolower($receivedSign) !== $calculatedSign) {
        webhookLog("❌ Signature mismatch");
        echo "success";
        exit;
    }
    
    webhookLog("✅ Signature verified");
    
    // ✅ Handle orderState as STRING
    // Gateway sends: "0" (success), "1" (failed/pending)
    $orderStateStr = (string)$orderState;
    
    webhookLog("✅ Order state check", [
        'Raw State' => $orderState,
        'String State' => $orderStateStr,
        'Is Success' => ($orderStateStr === '0')
    ]);
    
    // Only process if payment successful (orderState = "0")
    if ($orderStateStr !== '0') {
        webhookLog("⚠️ Payment not successful", [
            'State' => $orderStateStr,
            'Expected' => '0'
        ]);
        echo "success";
        exit;
    }
    
    // Find order
    $orderQuery = mysqli_query($conn, 
        "SELECT * FROM thevani 
         WHERE dharavahi = '$merchantOrderNo' 
         LIMIT 1"
    );
    
    if (!$orderQuery) {
        webhookLog("❌ Query error", ['Error' => mysqli_error($conn)]);
        echo "success";
        exit;
    }
    
    if (mysqli_num_rows($orderQuery) == 0) {
        webhookLog("⚠️ Order not found in database", [
            'Order' => $merchantOrderNo,
            'Suggestion' => 'Check if order was created in NinePay.php'
        ]);
        echo "success";
        exit;
    }
    
    $order = mysqli_fetch_assoc($orderQuery);
    
    webhookLog("✅ Order found", [
        'Order' => $order,
        'Current Status' => $order['sthiti']
    ]);
    
    // Check if already processed
    if ($order['sthiti'] == '1') {
        webhookLog("⚠️ Order already processed", [
            'Order' => $merchantOrderNo,
            'Status' => 'Already completed'
        ]);
        echo "success";
        exit;
    }
    
    $uid = $order['balakedara'];
    $orderAmount = $order['motta'];
    
    // Get current balance
    $balanceQuery = mysqli_query($conn, 
        "SELECT motta FROM shonu_kaichila WHERE balakedara = '$uid'"
    );
    
    if (!$balanceQuery || mysqli_num_rows($balanceQuery) == 0) {
        webhookLog("❌ User not found", ['UID' => $uid]);
        echo "success";
        exit;
    }
    
    $balanceRow = mysqli_fetch_assoc($balanceQuery);
    $oldBalance = $balanceRow['motta'];
    
    webhookLog("✅ Processing payment", [
        'UID' => $uid,
        'Old Balance' => $oldBalance,
        'Amount to Add' => $orderAmount
    ]);
    
    // ✅ Use transaction for safety
    mysqli_begin_transaction($conn);
    
    try {
        // Update balance
        $updateBalance = mysqli_query($conn, 
            "UPDATE shonu_kaichila 
             SET motta = ROUND(motta + $orderAmount, 2) 
             WHERE balakedara = '$uid'"
        );
        
        if (!$updateBalance) {
            throw new Exception("Balance update failed: " . mysqli_error($conn));
        }
        
        // Mark order complete
        $updateOrder = mysqli_query($conn, 
            "UPDATE thevani 
             SET sthiti = '1' 
             WHERE dharavahi = '$merchantOrderNo'"
        );
        
        if (!$updateOrder) {
            throw new Exception("Order update failed: " . mysqli_error($conn));
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Get new balance
        $newBalanceQuery = mysqli_query($conn, 
            "SELECT motta FROM shonu_kaichila WHERE balakedara = '$uid'"
        );
        $newBalanceRow = mysqli_fetch_assoc($newBalanceQuery);
        $newBalance = $newBalanceRow['motta'];
        
        webhookLog("🎉 SUCCESS - Payment Processed", [
            'Order' => $merchantOrderNo,
            'User' => $uid,
            'Amount' => $orderAmount,
            'Old Balance' => $oldBalance,
            'New Balance' => $newBalance,
            'Difference' => ($newBalance - $oldBalance)
        ]);
        
        // ✅ IMPORTANT: Return plain text "success"
        echo "success";
        exit;
        
    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($conn);
        
        webhookLog("❌ Transaction failed", [
            'Error' => $e->getMessage()
        ]);
        
        echo "success";
        exit;
    }
    
} catch (Exception $e) {
    webhookLog("❌ EXCEPTION", [
        'Message' => $e->getMessage(),
        'File' => $e->getFile(),
        'Line' => $e->getLine()
    ]);
    
    // ✅ Gateway requirement: Always return success
    echo "success";
    exit;
}
?>