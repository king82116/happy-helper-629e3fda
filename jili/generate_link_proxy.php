<?php
ob_start();
$apiKey = require_once(__DIR__ . '/api_key.php');
$gameId = $_GET['gameId'] ?? null;
$token  = $_GET['token'] ?? null;

$log = [];

if (!$gameId || !$token) {
    http_response_code(400);
    exit("Missing parameters.");
}

$apiUrl = "https://auth.india.ke/jili/v1/generate_link";
$headers = [
    "Content-Type: application/json",
    "x-api-key: $apiKey"
];

$maxRetries = 10;
$retryDelay = 2; // seconds
$attempt = 0;
$gameLink = null;

while ($attempt < $maxRetries && !$gameLink) {
    $payload = json_encode([
        "gameId" => $gameId,
        "token"  => $token
    ]);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $log['timestamp'] = date('Y-m-d H:i:s');
    $log['request'] = [
        'url' => $apiUrl,
        'headers' => $headers,
        'payload' => $payload
    ];
    $log['response'] = [
        'http_code' => $httpCode,
        'body' => $response
    ];
    if ($curlError) {
        $log['curl_error'] = $curlError;
    }
    file_put_contents(__DIR__ . '/log.txt', print_r($log, true) . "\n\n", FILE_APPEND);

    $data = json_decode($response, true);
    if (!empty($data['gameLink'])) {
        $gameLink = htmlspecialchars($data['gameLink'], ENT_QUOTES, 'UTF-8');
    } else {
        $attempt++;
        sleep($retryDelay); // wait before trying again
    }
}

if ($gameLink) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Game</title>
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <style>
            body, html {
                margin: 0;
                padding: 0;
                height: 100%;
                overflow: hidden;
            }
            .back-btn {
                position: absolute;
                top: 10px;
                left: 10px;
                z-index: 1000;
                background-color: #ffffff;
                color: #000000;
                border: none;
                padding: 6px 14px;
                border-radius: 40px;
                font-size: 17px;
                cursor: pointer;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            }
            iframe {
                width: 100%;
                height: 100%;
                border: none;
            }
            .loading {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                font-size: 24px;
                font-family: sans-serif;
                color: #444;
            }
        </style>
        <script>
            function goBack() {
                window.close();
            }
        </script>
    </head>
    <body>
        <button class=\"back-btn\" onclick=\"goBack()\">&laquo; Back</button>
        <div class=\"loading\" id=\"loading\">Loading game...</div>
        <iframe src=\"$gameLink\" onload=\"document.getElementById('loading').style.display='none'\"></iframe>
    </body>
    </html>";
} else {
    http_response_code(500);
    echo "Failed to generate game link after $maxRetries attempts. See log.txt for details.";
}

ob_end_flush();
