<?php
include("../serive/samparka.php"); // DB connection
$config = include("../pay/wepay_config.php"); // Merchant config

// Get raw data from callback
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Logging raw data
file_put_contents("logs/wepay_callback_raw.log", "[" . date("Y-m-d H:i:s") . "] - Raw: $json\n", FILE_APPEND);

if (!$data || !is_array($data)) {
    echo "No data received.";
    exit;
}

// Extract and remove the original signature
$originalSign = $data['sign'] ?? '';
unset($data['sign']);

// Sort and rebuild the string
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

// Append secret key
$str .= 'key=' . $config['merchantKey'];
$generatedSign = strtolower(md5($str));

// Log signature process
file_put_contents("logs/wepay_signature.log", "[" . date("Y-m-d H:i:s") . "] - Generated: $generatedSign | Received: $originalSign\n", FILE_APPEND);

// Signature verification
if ($originalSign !== $generatedSign) {
    echo "Sign verification failed: signature mismatch";
    exit;
}

// Check if payment is successful
if ($data['payStatus'] != 1) {
    echo "Payment not successful";
    exit;
}

// Step 3: Check if already processed
$orderId = mysqli_real_escape_string($conn, $data['orderNo']);
$checkQuery = "SELECT * FROM thevani WHERE dharavahi = '$orderId' LIMIT 1";
$res = mysqli_query($conn, $checkQuery);

if ($res && mysqli_num_rows($res) > 0) {
    $row = mysqli_fetch_assoc($res);
    if ($row['sthiti'] == '1') {
        echo "success"; // <- Important fix for repeat callbacks
        exit;
    }

    $uid = $row['balakedara'];
    $amount = floatval($row['motta']);

    // Step 4: Update wallet
    $updateWallet = "UPDATE shonu_kaichila SET motta = motta + $amount WHERE balakedara = '$uid'";
    $conn->query($updateWallet);

    // Step 5: Mark transaction as complete
    $updateTxn = "UPDATE thevani SET sthiti = '1' WHERE dharavahi = '$orderId'";
    $conn->query($updateTxn);

    file_put_contents("logs/wepay_callback_success.log", "[" . date("Y-m-d H:i:s") . "] - ✅ TXN DONE: Order $orderId | User $uid | Amount $amount\n", FILE_APPEND);

    echo "success";
    exit;
} else {
    file_put_contents("logs/wepay_callback_error.log", "[" . date("Y-m-d H:i:s") . "] - ❌ TXN NOT FOUND: $orderId\n", FILE_APPEND);
    http_response_code(404);
    exit("Order not found");
}
?>
