<?php
/**
 * SERVER-SIDE ONLY — SOURCEAPI HTTP client (gameList, getGameUrl).
 */

function sourceapi_get_config(): array
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/sourceapi_config.php';
    }
    return $config;
}

/**
 * @return array{ok:bool,http_code:int,data:?array,error:?string}
 */
function sourceapi_request(string $endpoint, array $body = []): array
{
    $config = sourceapi_get_config();
    $baseUrl = rtrim($config['base_url'] ?? 'https://sourceapi.com', '/');
    $path = '/' . ltrim($endpoint, '/');
    $url = $baseUrl . $path;

    $payload = array_merge([
        'api_key' => $config['api_key'] ?? '',
        'api_secret' => $config['api_secret'] ?? '',
        'vendor_code' => $config['vendor_code'] ?? 'sourceapi',
    ], $body);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Api-Key: ' . ($config['api_key'] ?? ''),
            'X-Api-Secret: ' . ($config['api_secret'] ?? ''),
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
    ]);

    $raw = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        error_log('SOURCEAPI cURL error: ' . $curlError);
        return ['ok' => false, 'http_code' => 0, 'data' => null, 'error' => $curlError ?: 'Connection failed'];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        error_log('SOURCEAPI invalid JSON (' . $httpCode . '): ' . substr($raw, 0, 500));
        return ['ok' => false, 'http_code' => $httpCode, 'data' => null, 'error' => 'Invalid response from SOURCEAPI'];
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        $err = $data['error'] ?? $data['message'] ?? ('HTTP ' . $httpCode);
        error_log('SOURCEAPI HTTP ' . $httpCode . ': ' . $err);
        return ['ok' => false, 'http_code' => $httpCode, 'data' => $data, 'error' => (string) $err];
    }

    if (empty($data['success'])) {
        $err = $data['error'] ?? $data['message'] ?? 'Request failed';
        return ['ok' => false, 'http_code' => $httpCode, 'data' => $data, 'error' => (string) $err];
    }

    return ['ok' => true, 'http_code' => $httpCode, 'data' => $data, 'error' => null];
}

/**
 * POST /api/gameList
 */
function sourceapi_game_list(array $options = []): array
{
    return sourceapi_request('/api/gameList', $options);
}

/**
 * POST /api/getGameUrl — returns launch URL or null.
 */
function sourceapi_get_game_url(
    string $memberAccount,
    string $gameUid,
    float $amount,
    string $currencyCode,
    string $language = 'en',
    string $homeUrl = '',
    string $supplierCode = ''
): ?string {
    $body = [
        'member_account' => $memberAccount,
        'game_uid' => (string) $gameUid,
        'amount' => $amount,
        'currency_code' => $currencyCode,
        'language' => $language,
    ];

    if ($homeUrl !== '') {
        $body['home_url'] = $homeUrl;
    }

    if ($supplierCode !== '') {
        $body['supplier_code'] = strtoupper($supplierCode);
    }

    $result = sourceapi_request('/api/getGameUrl', $body);
    if (!$result['ok'] || empty($result['data']['game_launch_url'])) {
        return null;
    }

    return (string) $result['data']['game_launch_url'];
}
