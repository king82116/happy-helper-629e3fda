<?php
// Made By IndiaHost.Co
include "../../conn.php";
include "../../functions2.php";

// Load proxy configuration
$proxyConfig = require(__DIR__ . '/../proxy_config.php');

header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . $origin);
header('Vary: Origin');

date_default_timezone_set("Asia/Kolkata");
$shnunc = date("Y-m-d H:i:s");

$res = [
    'code' => 11,
    'msg' => 'Method not allowed',
    'msgCode' => 12,
    'serviceNowTime' => $shnunc,
];

function generateUrl($fileName)
{
    $protocol = "https://";
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];

    $pathParts = explode("/", trim($uri, "/"));
    if (!empty($pathParts) && count($pathParts) > 1) {
        array_pop($pathParts);
    }

    return $protocol . $host . "/" . implode("/", $pathParts) . "/" . $fileName;
}

/**
 * Call Proxy API to get game launch URL
 * Routes game requests through the proxy server
 */
function getGameUrlFromProxy($username, $gameUid, $creditAmount, $currencyCode, $language, $platform, $homeUrl = '', $callbackUrl = '', $proxyConfig)
{
    $proxyApiUrl = rtrim($proxyConfig['proxy_api_url'], '/') . '/api/game/launch';

    // Prepare request payload
    $payload = [
        'username' => $username,
        'game_uid' => (string) $gameUid,
        'credit_amount' => (string) $creditAmount,
        'currency_code' => $currencyCode,
        'language' => $language,
        'platform' => $platform
    ];

    // Add optional parameters
    if (!empty($homeUrl)) {
        $payload['home_url'] = $homeUrl;
    }

    if (!empty($callbackUrl)) {
        $payload['callback_url'] = $callbackUrl;
    }

    // Prepare headers
    $headers = [
        'Content-Type: application/json',
        'X-API-KEY: ' . $proxyConfig['client_api_key']
    ];

    // Add Origin header for domain validation
    // This is required for the proxy server to validate the allowed_domain
    if (!empty($proxyConfig['client_domain'])) {
        $clientDomain = $proxyConfig['client_domain'];
        // Remove protocol if present
        $clientDomain = preg_replace('#^https?://#', '', $clientDomain);
        // Remove trailing slash
        $clientDomain = rtrim($clientDomain, '/');
        // Remove port if present
        $clientDomain = explode(':', $clientDomain)[0];
        $headers[] = 'Origin: https://' . $clientDomain;
    } else {
        // Fallback: try to extract from homeUrl
        if (!empty($homeUrl)) {
            $parsed = parse_url($homeUrl);
            if (!empty($parsed['host'])) {
                $headers[] = 'Origin: https://' . $parsed['host'];
            }
        }
    }

    // Initialize cURL
    $ch = curl_init($proxyApiUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 20
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Handle errors
    if ($curlError) {
        error_log("Proxy API Error: " . $curlError);
        return null;
    }

    if ($httpCode !== 200) {
        error_log("Proxy API HTTP Error: {$httpCode} - {$response}");
        return null;
    }

    // Parse response
    $responseData = json_decode($response, true);

    if (isset($responseData['status']) && $responseData['status'] === true && isset($responseData['data']['game_url'])) {
        return $responseData['data']['game_url'];
    }

    error_log("Proxy API Invalid Response: " . $response);
    return null;
}

/**
 * Check if vendor code should be routed through proxy
 * NOTE: This function is kept for backward compatibility but is no longer used.
 * ALL vendors now route through proxy by default.
 */
function shouldRouteThroughProxy($vendorCode, $proxyConfig)
{
    // Always return true - all vendors route through proxy
    return true;
}


$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    if (
        isset($shonupost['language']) &&
        isset($shonupost['random']) &&
        isset($shonupost['signature']) &&
        isset($shonupost['timestamp'])
    ) {
        $language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language'] ?? ''));
        $random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random'] ?? ''));
        $signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature'] ?? ''));
        $vendorCode = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['vendorCode'] ?? ''));
        $gameCode = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['gameCode'] ?? ''));
        $phonetype = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['phonetype'] ?? ''));

        $shonustr = '{"gameCode":"' . $gameCode . '","language":' . $language . ',"phonetype":' . $phonetype . ',"random":"' . $random . '","vendorCode":' . $vendorCode . '}';
        $shonusign = strtoupper(md5($shonustr));

        if ($shonusign != $signature) {
            $bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION'] ?? '');
            $author = $bearer[1] ?? '';

            $is_jwt_valid = is_jwt_valid($author);
            $data_auth = json_decode($is_jwt_valid, true);

            if ($data_auth['status'] === 'Success') {
                $sesquery = "SELECT token, mobile, id FROM shonu_subjects WHERE akshinak = '$author'";
                $sesresult = $conn->query($sesquery);
                $sesnum = mysqli_num_rows($sesresult);
                $row = $sesresult->fetch_assoc();

                $uid = $row['token'] ?? null;
                $no = $row['mobile'] ?? null;
                $primaryid = $row['id'] ?? null;

                //-----------------------------------//
                $sesquery2 = "SELECT motta FROM shonu_kaichila WHERE balakedara = '$primaryid'";
                $sesresult2 = $conn->query($sesquery2);
                $row2 = $sesresult2->fetch_assoc();
                $balance = $row2['motta'] ?? null;

                function getGTypeFromMType($mType)
                {
                    if ($mType >= 7000 && $mType <= 7099)
                        return 7;
                    if ($mType >= 8000 && $mType <= 8099)
                        return 0;
                    if ($mType >= 9000 && $mType <= 9099)
                        return 9;
                    if ($mType >= 12000 && $mType <= 12099)
                        return 12;
                    if ($mType >= 14000 && $mType <= 14099)
                        return 0;
                    if ($mType >= 15000 && $mType <= 15099)
                        return 0;
                    if ($mType >= 18000 && $mType <= 18099)
                        return 18;
                    return 0; // default fallback
                }

                // ALL vendors route through proxy server - no exceptions
                // Use token (uid) as username for proxy - this is what game-file uses to identify users
                $username = $uid ?? $no ?? 'user_' . $primaryid;
                $gameUid = $gameCode;
                $creditAmount = (float) ($balance ?? 0);
                $currencyCode = $proxyConfig['default_currency'];

                // Map language
                $langCode = $proxyConfig['language_map'][(int) $language] ?? $proxyConfig['default_language'];

                // Map platform
                $platform = $proxyConfig['platform_map'][(int) $phonetype] ?? $proxyConfig['default_platform'];

                // Generate callback URL (proxy server callback endpoint)
                $callbackUrl = rtrim($proxyConfig['proxy_api_url'], '/') . '/api/huidu/callback';

                // Generate home URL (return URL)
                $homeUrl = generateUrl('index.html');

                // Call proxy API - ALL vendors go through proxy
                $game = getGameUrlFromProxy($username, $gameUid, $creditAmount, $currencyCode, $langCode, $platform, $homeUrl, $callbackUrl, $proxyConfig);

                // If proxy fails, return error (no fallback to direct vendor URLs)
                if ($game === null) {
                    error_log("Proxy API failed for vendor: {$vendorCode}, game: {$gameCode}, user: {$username}");
                    $res['code'] = 6;
                    $res['msg'] = 'Game launch failed - proxy server error';
                    $res['msgCode'] = 5;
                    http_response_code(500);
                    echo json_encode($res);
                    exit;
                }

                if ($sesnum === 1) {
                    $data['url'] = $game;
                    $data['returnType'] = 1;
                    $res['data'] = $data;
                    $res['code'] = 0;
                    $res['msg'] = 'Succeed';
                    $res['msgCode'] = 0;
                    http_response_code(200);
                    echo json_encode($res);
                    exit;
                } else {
                    $res['code'] = 4;
                    $res['msg'] = 'No operation permission';
                    $res['msgCode'] = 2;
                    http_response_code(401);
                    echo json_encode($res);
                    exit;
                }
            } else {
                $res['code'] = 4;
                $res['msg'] = 'No operation permission';
                $res['msgCode'] = 2;
                http_response_code(401);
                echo json_encode($res);
                exit;
            }
        } else {
            $res['code'] = 5;
            $res['msg'] = 'Wrong signature';
            $res['msgCode'] = 3;
            http_response_code(200);
            echo json_encode($res);
            exit;
        }
    } else {
        $res['code'] = 7;
        $res['msg'] = 'Param is Invalid';
        $res['msgCode'] = 6;
        http_response_code(200);
        echo json_encode($res);
        exit;
    }
} else {
    http_response_code(405);
    echo json_encode($res);
    exit;
}
?>