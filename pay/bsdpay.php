<?php
include("../serive/samparka.php");
$config = include "bsdconfig.php";

// --------- INPUTS ----------
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
$uid    = isset($_GET['uid']) ? intval($_GET['uid']) : 0;   // user id from game
$tyid   = isset($_GET['tyid']) ? intval($_GET['tyid']) : 0; // payTypeID (optional)

// BASIC CHECK
if ($amount <= 0 || $uid <= 0) {
    echo "BSDPay Error: Invalid amount or uid";
    exit;
}

// --------- MAKE LOCAL ORDER FIRST (thevani) ----------

// unique order no (yahi BSD ko bhejेंगे + apne DB me save करेंगे)
$mcOrderNo = time() . rand(10000, 99999);

// user mobile निकाल लो (जैसे lgpay में किया था)
$emailQ = mysqli_query($conn , "SELECT mobile FROM `shonu_subjects` WHERE `id` = '".$uid."'");
$emailA = mysqli_fetch_array($emailQ);
$mobile = $emailA['mobile'] ?? '';

// createdate
$createdate = date("Y-m-d H:i:s");

// thevani में PENDING ऑर्डर डाल दो
// fields आपके पुराने lgpay वाले pattern पर हैं
// payid = 2 (third party), mula = 'BSDPay'
$insertSql = "
    INSERT INTO `thevani`
    (`payid`, `balakedara`, `motta`, `dharavahi`, `mula`, `ullekha`, `duravani`, `ekikrtapavati`, `dinankavannuracisi`, `madari`, `pavatiaidi`, `sthiti`)
    VALUES
    ('2', '$uid', '$amount', '$mcOrderNo', 'BSDPay', 'N/A', '$mobile', 'BSDPAY_UPI', '$createdate', '1005', '2', '0')
";
mysqli_query($conn, $insertSql);

// --------- SIGNATURE FUNCTION ----------
function bsd_signature($method, $url_path, $accessKey, $secretKey, $timestamp, $nonce)
{
    $string = strtoupper($method) . "&" . $url_path . "&" . $accessKey . "&" . $timestamp . "&" . $nonce;
    return base64_encode(hash_hmac('sha256', $string, $secretKey, true));
}

// --------- BSD CONFIG ----------
$method     = "POST";
$url_path   = "/api/order/create";                        // docs वाला path
$api_url    = rtrim($config["api_url"], "/") . $url_path;

$accessKey  = $config["access_key"];
$secretKey  = $config["secret_key"];

$timestamp  = time();
$nonce      = rand(100000, 999999);

// SIGN
$sign = bsd_signature($method, $url_path, $accessKey, $secretKey, $timestamp, $nonce);

// --------- BSD BODY ----------
$body = [
    "McorderNo"   => $mcOrderNo,                  // वही जो DB में डाला है
    "Amount"      => $amount,
    "Type"        => "inr",
    "ChannelCode" => $config["channelCode"],
    "CallBackUrl" => $config["callbackUrl"],
    "JumpUrl"     => $config["successUrl"],      // success के बाद वापस यहीं हिट करेगा
];

// --------- HEADERS ----------
$headers = [
    "accessKey: $accessKey",
    "timestamp: $timestamp",
    "nonce: $nonce",
    "sign: $sign",
    "Content-Type: application/json"
];

// --------- CURL REQUEST ----------
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$error    = curl_error($ch);
curl_close($ch);

// OPTIONAL DEBUG LOG (file में)
// file_put_contents("bsd_request_log.txt", date('Y-m-d H:i:s') . " | REQ: " . json_encode($body) . " | RES: " . $response . " | ERR: " . $error . "\n", FILE_APPEND);

// --------- HANDLE RESPONSE ----------
$data = json_decode($response, true);

// अगर gateway ने ठीक से payUrl दिया
if ($error === "" && $data && isset($data["code"]) && $data["code"] == 200 && isset($data["result"]["payUrl"])) {
    $payUrl = $data["result"]["payUrl"];
    // सीधे gateway पर भेज दो
    header("Location: $payUrl");
    exit;
} else {
    // अगर यहाँ fail आया तो local order को भी fail कर दो (optional)
    mysqli_query($conn, "UPDATE thevani SET sthiti = '2' WHERE dharavahi = '$mcOrderNo'"); // 2 = failed (अगर आपका ऐसा कोई status है)

    echo "<pre>";
    echo "BSDPay Error: Payment Failed\n\n";
    echo "Response:\n";
    print_r($response);
    echo "\nError:\n";
    print_r($error);
    echo "</pre>";
    exit;
}
?>