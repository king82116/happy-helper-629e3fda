<?php
include("../serive/samparka.php");
$config = include "bsdconfig.php";


// Read raw headers (case-insensitive)
$raw = getallheaders();
$headers = [];
foreach ($raw as $k => $v) {
    $headers[strtolower($k)] = $v;
}

$accessKey = $headers["accesskey"] ?? "";
$timestamp = $headers["timestamp"] ?? "";
$nonce     = $headers["nonce"] ?? "";
$sign      = $headers["sign"] ?? "";


// Read JSON body
$bodyRaw = file_get_contents("php://input");
$body = json_decode($bodyRaw, true);


// Log raw callback
file_put_contents("bsd_log.txt", print_r([
    "headers" => $headers,
    "body"    => $body,
    "uri"     => $_SERVER["REQUEST_URI"],
    "raw"     => $bodyRaw
], true), FILE_APPEND);


// 1) Validate AccessKey
if ($accessKey !== $config["access_key"]) {
    echo "Error AccessKey";
    exit;
}


// --- AUTO DETECT Correct Path for Signature ---
$url_path = $_SERVER["REQUEST_URI"];  
// e.g. "/pay/bsdwebhook.php?test=1"
// Now remove query string:
$url_path = explode("?", $url_path)[0];


// Signature function
function bsd_signature($method, $url, $accessKey, $secretKey, $timestamp, $nonce) {
    $str = strtoupper($method) . "&" . $url . "&" . $accessKey . "&" . $timestamp . "&" . $nonce;
    return base64_encode(hash_hmac('sha256', $str, $secretKey, true));
}


// 2) Verify signature
$expected = bsd_signature("POST", $url_path, $config["access_key"], $config["secret_key"], $timestamp, $nonce);

if ($sign !== $expected) {
    echo "Sign Fail";
    exit;
}


// Extract order data
$orderNo = $body["merchantorder"] ?? "";
$status  = strtolower($body["status"] ?? "");

if ($orderNo && $status == "success") {

    // Find pending order
    $q = mysqli_query($conn, "SELECT motta, balakedara FROM thevani WHERE dharavahi='$orderNo' AND sthiti='0'");

    if (mysqli_num_rows($q) >= 1) {

        $r = mysqli_fetch_array($q);
        $uid = $r["balakedara"];
        $amt = $r["motta"];

        // Credit user
        mysqli_query($conn, "UPDATE shonu_kaichila SET motta = motta + $amt WHERE balakedara='$uid'");
        mysqli_query($conn, "UPDATE thevani SET sthiti='1' WHERE dharavahi='$orderNo'");
    }

    echo "success";
    exit;
}

echo "ok";
exit;
?>