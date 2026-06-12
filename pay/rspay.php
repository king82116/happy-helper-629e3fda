<?php
// Background processing logic
include("../serive/samparka.php");
$config = include("../pay/rspay_config.php");

$uid = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['uid']));
$tyid = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['tyid']));
$ramt = number_format((float) htmlspecialchars(mysqli_real_escape_string($conn, $_GET['amount'])), 2, '.', '');

$date = date("Ymd");
$time = time();
$serial = $date . $time . rand(100000, 999900);
$createdate = date("Y-m-d H:i:s");

// Check demo
$isDemoAccount = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM demo WHERE balakedara = '$uid'")) > 0;

$conn->query("INSERT INTO thevani (balakedara, motta, dharavahi, mula, ullekha, duravani, ekikrtapavati, dinankavannuracisi, madari, pavatiaidi, sthiti) 
    VALUES ('$uid', '$ramt', '$serial', 'RS Pay', 'N/A', 'N/A', 'N/A', '$createdate', '1005', '2', '0')");

if ($isDemoAccount) {
    $conn->query("UPDATE thevani SET sthiti = '1' WHERE balakedara = '$uid' AND dharavahi = '$serial'");
    $conn->query("UPDATE shonu_kaichila SET motta = motta + $ramt WHERE balakedara = '$uid'");
    header('Location: https://joshgame.online/#/main');
    exit;
}

// Prepare RS Pay API params
$params = [
    "amount" => $ramt,
    "ext" => "test",
    "merchantId" => $config['merchantId'],
    "merchantOrderId" => $serial,
    "notifyUrl" => "https://joshgame.online/pay/verify_rspay.php",
    "redirectUrl" => "https://joshgame.online/#/main",
    "paymentCurrency" => "INR",
    "type" => 2,
    "userName" => $uid,
    "remark" => "user_id_" . $uid,
];
ksort($params);
$signString = '';
foreach ($params as $key => $value) {
    if ($key !== 'sign' && $value !== '' && $value !== null) {
        $signString .= $key . '=' . $value . '&';
    }
}
$signString .= 'key=' . $config['merchantKey'];
$params['sign'] = hash('sha256', $signString);

// Retry logic
$maxAttempts = 10;
$attempt = 0;
$payUrl = null;

while ($attempt < $maxAttempts) {
    $ch = curl_init("https://api.rs-pay.cc/apii/in/createOrder");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);
    if ($responseData && $responseData['status'] == 200 && isset($responseData['data']['payUrl'])) {
        $payUrl = $responseData['data']['payUrl'];
        break;
    }

    $attempt++;
    sleep(2);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Processing Payment...</title>
    <style>
        body {
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
            background: #000;
            color: #fff;
            font-family: Arial;
            flex-direction: column;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 6px solid #fff;
            border-top: 6px solid #00ffcc;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="spinner"></div>
    <h2>Please wait... Redirecting to payment</h2>

    <script>
        const payUrl = <?php echo json_encode($payUrl); ?>;
        if (payUrl) {
            setTimeout(() => {
                window.location.href = payUrl;
            }, 1500); // Small delay for better UX
        } else {
            document.body.innerHTML = "<h2>Failed to initiate payment. Please try again later.</h2>";
        }
    </script>
</body>

</html>