<?php
include("../serive/samparka.php"); // DB connection
$config = include("../pay/wepay_config.php"); // WePayPlus config

if (!isset($config['merchantId']) || empty($config['merchantId'])) {
    die("Config error: merchantId missing");
}

// Sanitize and fetch input
$uid = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['uid']));
$ramt = isset($_GET['amount']) ? (float) $_GET['amount'] : 0;
$ramt = number_format($ramt, 2, '.', '');

// Generate order ID
$date = date("Ymd");
$serial = $date . time() . rand(100000, 999900);
$createdate = date("Y-m-d H:i:s");

// Optional demo check
$isDemoAccount = false;
$demoResult = mysqli_query($conn, "SELECT * FROM demo WHERE balakedara = '$uid'");
if (mysqli_num_rows($demoResult) > 0) {
    $isDemoAccount = true;
}

// Insert into local transaction table
$insertQuery = "INSERT INTO thevani (balakedara, motta, dharavahi, mula, ullekha, duravani, ekikrtapavati, dinankavannuracisi, madari, pavatiaidi, sthiti)
    VALUES ('$uid', '$ramt', '$serial', 'WePayPlus', 'N/A', 'N/A', 'N/A', '$createdate', '1005', '2', '0')";
$conn->query($insertQuery);

// If demo, complete transaction immediately
if ($isDemoAccount) {
    $conn->query("UPDATE thevani SET sthiti = '1' WHERE balakedara = '$uid' AND dharavahi = '$serial'");
    $conn->query("UPDATE shonu_kaichila SET motta = motta + $ramt WHERE balakedara = '$uid'");
    header('Location: https://www.josh296689_maxswin.site/#/main');
    exit;
}

// === WePayPlus Payload Preparation ===
$payload = [
    "mchId" => $config['merchantId'],
    "passageId" => $config['passageId'],
    "orderNo" => $serial,
    "amount" => $ramt,
    "notifyUrl" => $config['notifyUrl'],
    "otherData" => json_encode(["uid" => $uid], JSON_UNESCAPED_UNICODE)
];

// Filter and sort
$filteredPayload = array_filter($payload, fn($v) => $v !== null && $v !== '');
ksort($filteredPayload);

// Build sign string
$signParts = [];
foreach ($filteredPayload as $k => $v) {
    $signParts[] = "$k=$v";
}
$signStr = implode('&', $signParts) . '&key=' . $config['merchantKey'];
$sign = strtolower(md5($signStr));

// Add sign to payload
$payload['sign'] = $sign;

// === Debug Logs ===
echo "<pre>";
echo "Signature String:\n$signStr\n";
echo "Generated MD5 Signature: $sign\n";
echo "Payload to WePayPlus:\n";
print_r($payload);
echo "</pre>";

// Send API Request
$ch = curl_init("https://apis.wepayplus.com/client/collect/create");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=UTF-8']);
$response = curl_exec($ch);
curl_close($ch);

// Decode and log
$responseData = json_decode($response, true);
file_put_contents("wepayplus_response.log", date("Y-m-d H:i:s") . "\n" . print_r($responseData, true) . "\n\n", FILE_APPEND);

// Handle response
if ($responseData && isset($responseData['success']) && $responseData['success'] === true && isset($responseData['data']['payUrl'])) {
    header("Location: " . $responseData['data']['payUrl']);
    exit;
} else {
    $errMsg = $responseData['msg'] ?? 'Unknown error';
    echo "Payment Failed: $errMsg";
    echo "<pre>" . print_r($responseData, true) . "</pre>";
}
?>