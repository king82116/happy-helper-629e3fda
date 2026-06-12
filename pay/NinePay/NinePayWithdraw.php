<?php
// NinePayWithdraw.php - Production Version (No Logging)

include('../../application/conn.php');

date_default_timezone_set("Asia/Kolkata");

if (!isset($_POST['withdraw_id'])) {
    echo 0;
    exit;
}

$withdraw_id = intval($_POST['withdraw_id']);

/* Withdraw record */
$wq = mysqli_query($conn, "SELECT * FROM hintegedukolli WHERE shonu='$withdraw_id'");
$w = mysqli_fetch_assoc($wq);

if (!$w) {
    echo 0;
    exit;
}

$amount = $w['motta'];
$user_id = $w['balakedara'];

/* Bank details */
$bq = mysqli_query($conn, "SELECT * FROM khate WHERE shonu='" . $w['khateshonu'] . "'");
$bank = mysqli_fetch_assoc($bq);

if (!$bank) {
    echo 0;
    exit;
}

/* Use existing order number or generate new */
$merchantOrderNo = $w['dharavahi'];

if (empty($merchantOrderNo)) {
    $merchantOrderNo = 'W' . date('YmdHis') . rand(10000, 99999);

    mysqli_query($conn, "
        UPDATE hintegedukolli 
        SET dharavahi='$merchantOrderNo' 
        WHERE shonu='$withdraw_id'
    ");
}

/* NINEPAY CONFIG */
$MERCHANT_ID = "joshgame";
$MD5_KEY = "ed65572d8f594bbbaabd80e2b0dd9a00";
$API_URL = "https://newninepay.cc/api/xd/paymentOrder";
$CALLBACK = "https://joshgame.online/pay/NinePay/NinePayWithdrawCallback.php";

/* REQUEST PARAMS */
$params = [
    'merchantNo' => $MERCHANT_ID,
    'merchantOrderNo' => $merchantOrderNo,
    'orderAmount' => number_format($amount, 2, '.', ''),
    'bankAccount' => $bank['khatesankhye'],
    'bankCode' => $bank['kod'],
    'payeeName' => $bank['phalanubhavi'],
    'contactPhone' => '9999999999',
    'notifyUrl' => $CALLBACK
];

/* GENERATE SIGNATURE */
ksort($params);
$signStr = '';
foreach ($params as $k => $v) {
    $signStr .= $k . '=' . $v . '&';
}
$signStr = rtrim($signStr, '&') . $MD5_KEY;
$params['sign'] = strtoupper(md5($signStr));

/* CURL API CALL */
$ch = curl_init($API_URL);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => json_encode($params),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
curl_close($ch);

$res = json_decode($response, true);

/* SUCCESS */
if (
    isset($res['code']) && $res['code'] == 200 &&
    isset($res['success']) && $res['success'] === true
) {

    $gatewayMessage = $res['message'] ?? '';

    mysqli_query($conn, "
        UPDATE hintegedukolli 
        SET 
            sthiti='3',
            tike='Processing',
            remarks='" . mysqli_real_escape_string($conn, $gatewayMessage) . "'
        WHERE shonu='$withdraw_id'
    ");

    echo 1;
    exit;
}

/* FAILURE */
$errorMsg = $res['message'] ?? 'API request failed';

mysqli_query($conn, "
    UPDATE hintegedukolli 
    SET
        sthiti='2',
        tike='Failed',
        remarks='" . mysqli_real_escape_string($conn, $errorMsg) . "'
    WHERE shonu='$withdraw_id'
");

// Refund amount
mysqli_query($conn, "
    UPDATE shonu_kaichila
    SET motta = ROUND(motta + $amount, 2)
    WHERE balakedara='$user_id'
");

echo 0;
exit;
?>