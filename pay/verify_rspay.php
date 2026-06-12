<?php
include("../serive/samparka.php"); // Database connection
$config = include("rspay_config.php"); // RS Pay config values

// Load raw POST body
$input = file_get_contents("php://input");

// Log raw data
file_put_contents("rspay_debug_log.txt", "[" . date("Y-m-d H:i:s") . "] - Raw POST: $input\n", FILE_APPEND);

// Decode JSON
$data = json_decode($input, true);
if (!$data || !is_array($data)) {
    file_put_contents("rspay_error_log.txt", "[" . date("Y-m-d H:i:s") . "] - Invalid JSON in callback\n", FILE_APPEND);
    http_response_code(400);
    exit("Invalid data");
}

// Parse first array if sent as [ {...} ]
if (isset($data[0]) && is_array($data[0])) {
    $data = $data[0];
}

// Log parsed data
file_put_contents("rspay_debug_log.txt", "[" . date("Y-m-d H:i:s") . "] - Parsed Data: " . print_r($data, true) . "\n", FILE_APPEND);

// Required fields
$requiredFields = ['merchantId', 'merchantOrderId', 'orderId', 'amount', 'state', 'sign'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        file_put_contents("rspay_error_log.txt", "[" . date("Y-m-d H:i:s") . "] - Missing Required Field: $field\n", FILE_APPEND);
        http_response_code(400);
        exit("Missing field: $field");
    }
}

// Step 1: Signature Verification
$signData = $data;
unset($signData['sign']);
ksort($signData);

$signString = '';
foreach ($signData as $key => $value) {
    $signString .= "$key=$value&";
}
$signString .= 'key=' . $config['merchantKey'];
$generatedSign = hash('sha256', $signString);

// Log signature comparison
file_put_contents("rspay_debug_log.txt", "[" . date("Y-m-d H:i:s") . "] - Signature Check: Received: {$data['sign']} vs Generated: $generatedSign\n", FILE_APPEND);

if ($data['sign'] !== $generatedSign) {
    file_put_contents("rspay_error_log.txt", "[" . date("Y-m-d H:i:s") . "] - Signature mismatch\n", FILE_APPEND);
    http_response_code(403);
    exit("Invalid signature");
}

// Step 2: Check payment state
if ($data['state'] != 1) {
    file_put_contents("rspay_error_log.txt", "[" . date("Y-m-d H:i:s") . "] - Payment state not successful\n", FILE_APPEND);
    exit("Payment not successful");
}

// Step 3: Check if already processed
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

    // Step 4: Update wallet
    $updateWallet = "UPDATE shonu_kaichila SET motta = motta + $amount WHERE balakedara = '$uid'";
    $conn->query($updateWallet);

    // Step 5: Mark transaction as complete
    $updateTxn = "UPDATE thevani SET sthiti = '1' WHERE dharavahi = '$orderId'";
    $conn->query($updateTxn);

    file_put_contents("rspay_debug_log.txt", "[" . date("Y-m-d H:i:s") . "] - ✅ Transaction completed for user $uid, amount $amount\n", FILE_APPEND);

    echo "success";
    exit;
} else {
    file_put_contents("rspay_error_log.txt", "[" . date("Y-m-d H:i:s") . "] - Transaction not found in database for ID: $orderId\n", FILE_APPEND);
    http_response_code(404);
    exit("Order not found");
}
?>
