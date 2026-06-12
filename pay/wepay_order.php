<?php
function generateSignature($data, $secretKey)
{
    unset($data['sign']);
    ksort($data);
    $signStr = '';
    foreach ($data as $key => $value) {
        if ($value !== null && $value !== '') {
            $signStr .= "$key=$value&";
        }
    }
    $signStr .= "key=$secretKey";
    return strtolower(md5($signStr));
}

// Configuration
$merchantId = "3r295032";
$passageId = "32311";
$secretKey = "f675d1b1c85f4e48a8690346135988e3";

$response = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? '';

    // Create order
    if ($mode === 'create') {
        $postData = [
            'mchId' => $merchantId,
            'passageId' => $passageId,
            'orderNo' => trim($_POST['orderNo']),
            'account' => trim($_POST['account']),
            'userName' => trim($_POST['userName']),
            'ifsc' => trim($_POST['ifsc'] ?? ''),
            'number' => trim($_POST['number'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'amount' => trim($_POST['amount']),
            'notifyUrl' => trim($_POST['notifyUrl']),
            'otherData' => trim($_POST['otherData'] ?? '')
        ];

        // Generate signature
        $postData['sign'] = generateSignature($postData, $secretKey);

        // Send request to WePayPlus API
        $ch = curl_init("https://apis.wepayplus.com/client/pay/create");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        // Force cURL to use IPv4
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
        } else {
            $response = json_decode($result, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = "Invalid JSON response: " . htmlspecialchars($result);
                $response = null;
            }
        }
        curl_close($ch);

        // Query order status
    } elseif ($mode === 'query') {
        $queryData = [
            'mchId' => $merchantId,
            'orderNo' => trim($_POST['queryOrderNo'])
        ];
        $queryData['sign'] = generateSignature($queryData, $secretKey);

        // Send request to WePayPlus API
        $ch = curl_init("https://apis.wepayplus.com/client/pay/query");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($queryData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        // Force cURL to use IPv4
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
        } else {
            $response = json_decode($result, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = "Invalid JSON response: " . htmlspecialchars($result);
                $response = null;
            }
        }
        curl_close($ch);
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>WePayPlus Order Interface</title>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
            max-width: 800px;
            margin: auto;
            background: #fafafa;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;
            font-weight: bold;
            cursor: pointer;
        }

        .result {
            margin-top: 30px;
            background: #f9f9f9;
            padding: 20px;
            border: 1px solid #ccc;
            white-space: pre-wrap;
        }

        h2 {
            margin-top: 40px;
            color: #333;
        }
    </style>
</head>

<body>

    <h2>Create Payment Order</h2>
    <form method="post">
        <input type="hidden" name="mode" value="create" />
        <label>Order No:</label>
        <input type="text" name="orderNo" value="<?php echo 'ORDER' . time(); ?>" required>

        <label>Account (UPI/Bank):</label>
        <input type="text" name="account" value="demo@upi" required>

        <label>User Name:</label>
        <input type="text" name="userName" value="Test User" required>

        <label>IFSC (for Indian banks):</label>
        <input type="text" name="ifsc" value="ICIC0001234">

        <label>Bank Number / Name:</label>
        <input type="text" name="number" value="ICICI BANK">

        <label>Email:</label>
        <input type="text" name="email" value="test@example.com">

        <label>Amount (RMB):</label>
        <input type="number" step="0.01" name="amount" value="10.00" required>

        <label>Notify URL:</label>
        <input type="text" name="notifyUrl" value="https://joshgame.online/pay/wepayverifypay.php" required>

        <label>Other Data (optional):</label>
        <input type="text" name="otherData" value="user_id=123">

        <button type="submit">Create Order</button>
    </form>

    <h2>Query Payment Status</h2>
    <form method="post">
        <input type="hidden" name="mode" value="query" />
        <label>Order No:</label>
        <input type="text" name="queryOrderNo" required>
        <button type="submit">Check Status</button>
    </form>

    <?php if ($response): ?>
        <div class="result">
            <h3>Response:</h3>
            <pre><?php echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
        </div>
    <?php elseif ($error): ?>
        <div class="result" style="color: red;">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

</body>

</html>