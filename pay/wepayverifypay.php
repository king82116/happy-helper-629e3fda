<?php
include("../serive/samparka.php"); // Database connection

// Step 1: Get raw JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (empty($data)) {
    echo "No data received.";
    exit;
}

// Log received callback
file_put_contents('logs/pay/callback_data.log', "Received Callback Data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

// Step 2: Signature Verification
$originalSign = $data['sign'];
unset($data['sign']);
ksort($data);

$str = '';
foreach ($data as $key => $value) {
    if (is_bool($value)) {
        $value = $value ? 'true' : 'false';
    }
    if ($key === 'otherData' && is_array($value)) {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    if ($value !== null) {
        $str .= "$key=$value&";
    }
}

file_put_contents('logs/pay/sign_string_before_key.log', "String Before Key: " . $str . "\n", FILE_APPEND);

$token = 'f675d1b1c85f4e48a8690346135988e3'; // Replace with your real key
$str .= 'key=' . $token;

file_put_contents('logs/pay/sign_string_final.log', "Final String for MD5: " . $str . "\n", FILE_APPEND);

$generatedMd5 = strtolower(md5($str));

file_put_contents('logs/pay/generated_md5.log', "Generated MD5: " . $generatedMd5 . "\n", FILE_APPEND);

// Step 3: Signature comparison
if ($originalSign !== $generatedMd5) {
    echo 'Sign verification failed: signature mismatch';
    exit;
}

// Step 4: Check order existence and status
$orderId = mysqli_real_escape_string($conn, $data['merchantOrderId']);
$checkQuery = "SELECT * FROM thevani WHERE dharavahi = '$orderId' LIMIT 1";
$res = mysqli_query($conn, $checkQuery);

if ($res && mysqli_num_rows($res) > 0) {
    $row = mysqli_fetch_assoc($res);

    if ($row['sthiti'] == '1') {
        exit("Already processed");
    }

    $uid = $row['balakedara'];
    $amount = floatval($row['motta']);

    // Step 5: Update wallet
    $updateWallet = "UPDATE shonu_kaichila SET motta = motta + $amount WHERE balakedara = '$uid'";
    $conn->query($updateWallet);

    // Step 6: Mark transaction as complete
    $updateTxn = "UPDATE thevani SET sthiti = '1' WHERE dharavahi = '$orderId'";
    $conn->query($updateTxn);

    file_put_contents("logs/pay/wepay_debug_log.txt", "[" . date("Y-m-d H:i:s") . "] - ✅ Transaction completed for user $uid, amount $amount\n", FILE_APPEND);

    echo "success";
    exit;
} else {
    file_put_contents("logs/pay/wepay_error_log.txt", "[" . date("Y-m-d H:i:s") . "] - Transaction not found in database for ID: $orderId\n", FILE_APPEND);
    http_response_code(404);
    exit("Order not found");
}
?>
