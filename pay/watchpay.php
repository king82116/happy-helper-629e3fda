<?php
header("Content-Type: text/html; charset=UTF-8");
include("../serive/samparka.php");
include(__DIR__ . "/signapi.php");

// params
$amount = number_format((float) ($_GET['amount'] ?? 0), 2, '.', '');
$uid = $_GET['uid'] ?? '';
$order = date("YmdHis") . rand(1000, 9999);  // order no

// store pending
$time = date("Y-m-d H:i:s");
mysqli_query($conn, "INSERT INTO thevani(balakedara,motta,dharavahi,sthiti,dinankavannuracisi,mula)
VALUES('$uid','$amount','$order','0','$time','Watchpay')");

// watchpay config
$mch_id = "100666795";
$key = "VKRW7BLBSWUJUWYYY9A3U6IPINUPYSXP";
$paytype = "101";
$notify = "https://joshgame.online/pay/watchpayverify.php";

// required params
$params = [
    "version" => "1.0",
    "mch_id" => $mch_id,
    "notify_url" => $notify,
    "mch_order_no" => $order,
    "pay_type" => $paytype,
    "trade_amount" => $amount,
    "order_date" => date("Y-m-d H:i:s"),
    "goods_name" => "Topup",
];

// remove empty values
$params = array_filter($params, fn($v) => $v !== "" && $v !== null);

// build signature string sorted
ksort($params);
$q = "";
foreach ($params as $k => $v) {
    $q .= "$k=$v&";
}
$q .= "key=$key";

// lowercase md5
$sign = md5($q);

$params["sign_type"] = "MD5";
$params["sign"] = $sign;

// send curl
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://api.watchglb.com/pay/web",
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($params),
    CURLOPT_RETURNTRANSFER => true
]);

$res = curl_exec($ch);
curl_close($ch);

// try json decode
$data = json_decode($res, true);

// redirect if available
if (isset($data["payInfo"])) {
    header("Location: " . $data["payInfo"]);
    exit;
}

// dump
echo $res;
exit;
?>