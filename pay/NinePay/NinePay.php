<?php
// NinePay.php - LIGHTNING FAST VERSION ⚡

include("../../serive/samparka.php");

// Disable all output buffering for speed
ob_implicit_flush(true);

// Ultra-fast validation
$uid = (int) ($_GET['uid'] ?? 0);
$amount = (float) ($_GET['amount'] ?? 0);

if ($uid <= 0 || $amount <= 0) {
    die("Invalid parameters");
}

// Quick user check (single query)
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM shonu_subjects WHERE id='$uid' LIMIT 1"));
if (!$user)
    die("User not found");

// Quick wallet check/create (optimized)
if (!mysqli_fetch_assoc(mysqli_query($conn, "SELECT balakedara FROM shonu_kaichila WHERE balakedara='$uid' LIMIT 1"))) {
    mysqli_query($conn, "INSERT INTO shonu_kaichila (balakedara, motta, rebet, spin, bonus, safe, safeearn, safetoday, partnerinvite, invitebonus, totalbet) VALUES ('$uid', 0, '0', 28, '0', 0, 0, 0, 0, 0, 0)");
}

// Config
$merchantNo = "joshgame";
$md5Key = "ed65572d8f594bbbaabd80e2b0dd9a00";
$merchantOrderNo = "NP" . $uid . date("ymdHis") . rand(100, 999);

// Minimal params (faster signature)
$params = [
    "merchantNo" => $merchantNo,
    "merchantOrderNo" => $merchantOrderNo,
    "orderAmount" => number_format($amount, 2, '.', ''),
    "notifyUrl" => "https://joshgame.online/pay/NinePay/NinePayWebhook.php"
];

// Fast signature generation
ksort($params);
$signStr = implode("&", array_map(fn($k, $v) => "$k=$v", array_keys($params), $params)) . $md5Key;
$params['sign'] = strtoupper(md5($signStr));

// Ultra-fast cURL (optimized settings)
$ch = curl_init("https://newninepay.cc/api/xd/collectionOrder");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($params),
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 10, // 10 sec timeout
    CURLOPT_CONNECTTIMEOUT => 5, // 5 sec connect timeout
    CURLOPT_TCP_FASTOPEN => true, // Enable TCP Fast Open
    CURLOPT_TCP_NODELAY => true // Disable Nagle's algorithm for speed
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Quick response parsing
$res = json_decode($response, true);

if ($res && $res['code'] == 200 && $res['success'] === true && isset($res['result']['codeUrl'])) {

    // Async database insert (don't wait for confirmation)
    mysqli_query($conn, "INSERT INTO thevani (dharavahi, balakedara, motta, sthiti, mula, dinankavannuracisi) VALUES ('$merchantOrderNo', '$uid', '$amount', '0', 'NinePay', NOW())", MYSQLI_ASYNC);

    // INSTANT REDIRECT (don't wait for DB)
    header("Location: " . $res['result']['codeUrl']);
    exit;

} else {
    die("Payment failed");
}
?>