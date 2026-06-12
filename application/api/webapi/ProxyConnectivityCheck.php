<?php
/**
 * One-time server diagnostic for outbound access to Techmazet.
 * Open: /application/api/webapi/ProxyConnectivityCheck.php?key=CHANGE_ME
 * DELETE this file after testing.
 */
$secret = 'josh_proxy_test_2026';
if (($_GET['key'] ?? '') !== $secret) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

header('Content-Type: application/json; charset=utf-8');
$proxyConfig = require __DIR__ . '/../proxy_config.php';
$host = 'api.techmazet.in';
$ip = trim((string) ($proxyConfig['proxy_api_resolve_ip'] ?? ''));
$dns = @gethostbyname($host);
$url = rtrim($proxyConfig['proxy_api_url'] ?? 'https://api.techmazet.in', '/') . '/api/game/launch';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 25,
    CURLOPT_CONNECTTIMEOUT => 20,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-API-KEY: ' . ($proxyConfig['client_api_key'] ?? ''),
    ],
    CURLOPT_POSTFIELDS => '{}',
    CURLOPT_IPRESOLVE => defined('CURL_IPRESOLVE_V4') ? CURL_IPRESOLVE_V4 : 0,
]);
if ($ip !== '') {
    curl_setopt($ch, CURLOPT_RESOLVE, ["{$host}:443:{$ip}"]);
}

$body = curl_exec($ch);
$info = curl_getinfo($ch);
$err = curl_error($ch);
$errno = curl_errno($ch);
curl_close($ch);

echo json_encode([
    'server' => php_uname('n'),
    'php' => PHP_VERSION,
    'curl_version' => curl_version()['version'] ?? 'n/a',
    'dns_gethostbyname' => $dns,
    'config_resolve_ip' => $ip,
    'test_url' => $url,
    'curl_errno' => $errno,
    'curl_error' => $err,
    'http_code' => $info['http_code'] ?? 0,
    'primary_ip' => $info['primary_ip'] ?? '',
    'connect_time' => $info['connect_time'] ?? 0,
    'total_time' => $info['total_time'] ?? 0,
    'response_preview' => is_string($body) ? substr($body, 0, 400) : null,
    'ok' => $errno === 0 && ($info['http_code'] ?? 0) > 0,
], JSON_PRETTY_PRINT);
