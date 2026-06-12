<?php
// create_bondpay_order.php (fixed bind_param types/count)
@ob_start();
header('Content-Type: application/json; charset=utf-8');

include __DIR__ . "/../serive/samparka.php";
$config = include __DIR__ . '/bondpay_config.php';

function logit($msg) {
    global $config;
    $f = $config['LOG_FILE'] ?? '/tmp/bondpay_integration.log';
    file_put_contents($f, date('c') . " | " . $msg . PHP_EOL, FILE_APPEND);
}
function respond_json($arr, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function normalize_amount($amtRaw) {
    $amt = preg_replace('/[^\d.]/', '', (string)$amtRaw);
    if ($amt === '' || !is_numeric($amt)) return '0.00';
    $parts = explode('.', $amt, 2);
    if (count($parts) === 1) return $parts[0] . '.00';
    $dec = substr($parts[1], 0, 2);
    return $parts[0] . '.' . str_pad($dec, 2, '0');
}

$required = ['amount','tyid','uid','sign','urlInfo'];
foreach($required as $r) {
    if (!isset($_GET[$r])) respond_json(['status'=>false,'message'=>"Missing param: $r"],200);
}

$amount = normalize_amount($_GET['amount']);
$tyid   = (int)$_GET['tyid'];
$uid    = (int)$_GET['uid'];
$sign   = trim($_GET['sign']);
$urlInfo= trim($_GET['urlInfo']);

$MIN_AMOUNT = 1.00;
if ((float)$amount < $MIN_AMOUNT) {
    respond_json(['status'=>false,'message'=>"Amount must be >= {$MIN_AMOUNT}"],200);
}

/* demo fast-credit check (keeps original behavior) */
$existsDemo = false;
if ($stmt = $conn->prepare("SELECT 1 FROM demo WHERE balakedara = ? LIMIT 1")) {
    $uidStr = (string)$uid;
    $stmt->bind_param('s',$uidStr);
    $stmt->execute();
    $stmt->store_result();
    $existsDemo = $stmt->num_rows > 0;
    $stmt->close();
}
if ($existsDemo) {
    $serial = date("Ymd") . time() . random_int(100000,999900);
    $createdate = date("Y-m-d H:i:s");
    $payName = 'DEMO';
    $mottaFloat = (float)$amount;

    if ($ins = $conn->prepare("INSERT INTO `thevani` (`balakedara`,`motta`,`dharavahi`,`mula`,`ullekha`,`duravani`,`ekikrtapavati`,`dinankavannuracisi`,`madari`,`pavatiaidi`,`sthiti`) VALUES (?,?,?,?,?,?,?,?,?,?,?)")) {
        $ullekha = 'N/A'; $duravani='N/A'; $ekikrtapavati='1005'; $madari='1005'; $pavatiaidi=2; $sthiti=1;
        $ins->bind_param('sdsssssssss', $uidStr, $mottaFloat, $serial, $payName, $ullekha, $duravani, $ekikrtapavati, $createdate, $madari, $pavatiaidi, $sthiti);
        $ins->execute();
        $ins->close();
    }

    if ($upd = $conn->prepare("UPDATE `shonu_kaichila` SET `motta` = `motta` + ? WHERE `balakedara` = ?")) {
        $upd->bind_param('ds', $mottaFloat, $uidStr);
        $upd->execute();
        $upd->close();
    }

    header('Location: ' . $config['RETURN_URL']);
    exit;
}

/* fetch user mobile */
$userMobile = null;
if ($stmt = $conn->prepare("SELECT mobile FROM shonu_subjects WHERE id = ? LIMIT 1")) {
    $stmt->bind_param('i',$uid);
    $stmt->execute();
    $stmt->bind_result($userMobile);
    $stmt->fetch();
    $stmt->close();
}
if (!$userMobile) {
    respond_json(['status'=>false,'message'=>'Invalid uid or missing mobile'],200);
}

/* prepare merchant_order_no and payload */
$merchant_order_no = 'ORD_' . date("Ymd") . '_' . time() . '_' . random_int(1000,9999);
$rawsig = $config['BOND_MERCHANT_ID'] . $amount . $merchant_order_no . $config['BOND_API_KEY'] . $config['NOTIFY_URL'];
$signature = md5($rawsig);

$payload = [
    "merchant_id" => (string)$config['BOND_MERCHANT_ID'],
    "api_key"     => (string)$config['BOND_API_KEY'],
    "amount"      => (string)$amount,
    "merchant_order_no" => (string)$merchant_order_no,
    "callback_url" => (string)$config['NOTIFY_URL'],
    "extra"       => 0,
    "signature"   => $signature
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, rtrim($config['BOND_BASE_URL'],'/') . $config['BOND_CREATE_PATH']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 25);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

logit("CreateOrder RESPONSE http={$httpcode} curlErr={$curlErr} resp=" . substr($response,0,800));

if ($curlErr) {
    respond_json(['status'=>false,'message'=>'Gateway request error','debug'=>$curlErr],200);
}
$resData = json_decode($response, true);
if (!is_array($resData)) {
    respond_json(['status'=>false,'message'=>'Invalid gateway response','debug'=>substr($response,0,800)],200);
}

/* on success, insert pending record correctly with exact bind types */
if ($httpcode >=200 && $httpcode < 300 && !empty($resData['success']) && !empty($resData['payment_url'])) {
    $createdate = date("Y-m-d H:i:s");
    $payid = 2;
    $srl = $merchant_order_no;
    $refNum = $resData['order_no'] ?? $merchant_order_no;
    $emailMobile = $userMobile;
    $upi = 'BondPay';
    $mottaFloat = (float)$amount;
    $sthiti = 0; // pending

    // prepare variables (no inline expressions in bind_param)
    $balakedara = (string)$uid; // string type as used in other inserts
    $payNameVar = isset($_GET['tyid']) ? (string)$_GET['tyid'] : 'PAY';
    $ekik = '1005';
    $pav  = 2;

    $insertSql = "INSERT INTO `thevani` (`payid`,`balakedara`,`motta`,`dharavahi`,`mula`,`ullekha`,`duravani`,`ekikrtapavati`,`dinankavannuracisi`,`madari`,`pavatiaidi`,`sthiti`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    if ($ins = $conn->prepare($insertSql)) {
        // types: i (payid), s (balakedara), d (motta), s (dharavahi), s (mula), s (ullekha), s (duravani), s (ekikrtapavati),
        // s (dinankavannuracisi), s (madari), i (pavatiaidi), i (sthiti)
        $typeString = 'isdsssssssii'; // total 12 chars -> matches 12 variables below

        $ins->bind_param(
            $typeString,
            $payid,         // i
            $balakedara,    // s
            $mottaFloat,    // d
            $srl,           // s
            $payNameVar,    // s
            $refNum,        // s
            $emailMobile,   // s
            $upi,           // s
            $createdate,    // s
            $ekik,          // s (madari)
            $pav,           // i
            $sthiti         // i
        );

        if (!$ins->execute()) {
            logit("DB INSERT execute failed: " . $ins->error);
        }
        $ins->close();
    } else {
        logit("DB INSERT prepare failed: " . $conn->error);
    }

    header('Location: ' . $resData['payment_url']);
    exit;
}

/* otherwise return error */
respond_json(['status'=>false,'message'=> $resData['message'] ?? 'Failed to create order', 'debug'=>$resData],200);
