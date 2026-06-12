<?php
/**
 * Run on server: php trx_diag.php
 */
date_default_timezone_set('Asia/Kolkata');
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/serive/trx_block_helper.php';

$cfg = trx_wingo_load_api_config();
$tronscanApiKey = (string) ($cfg['tronscan_api_key'] ?? '');
$trongridApiKey = (string) ($cfg['trongrid_api_key'] ?? '');

echo "TRX Wingo diagnostic — " . date('Y-m-d H:i:s') . "\n\n";
echo 'TronScan key: ' . ($tronscanApiKey !== '' ? 'set' : 'MISSING — get free key at tronscan.org') . "\n";
echo 'TronGrid key: ' . ($trongridApiKey !== '' ? 'set (optional)' : 'not set (OK — not required)') . "\n\n";

$tests = [
    'TronScan.org latest' => ['GET', 'https://apilist.tronscan.org/api/block?sort=-number&limit=1', trx_wingo_api_headers($tronscanApiKey)],
    'TronScanAPI latest' => ['GET', 'https://apilist.tronscanapi.com/api/block?sort=-number&limit=1', trx_wingo_api_headers($tronscanApiKey)],
];

if ($trongridApiKey !== '') {
    $tests['TronGrid v1 latest'] = ['GET', 'https://api.trongrid.io/v1/blocks/latest', trx_wingo_api_headers($trongridApiKey)];
}

foreach ($tests as $label => $t) {
    $r = trx_wingo_http_request('GET', $t[0], null, $t[1]);
    echo $label . ': ' . ($r['ok'] ? 'OK HTTP ' . $r['code'] : 'FAIL — ' . ($r['error'] ?: 'HTTP ' . $r['code'])) . "\n";
}

echo "\nChain head (TronScan first):\n";
$head = trx_wingo_chain_head($tronscanApiKey, $trongridApiKey);
echo $head ? "  OK bh={$head['number']}\n" : "  FAILED\n";

if ($tronscanApiKey === '') {
    echo "\nAdd your TronScan API key to serive/trx_api_config.php\n";
    exit;
}

$anchor = trx_wingo_settlement_anchor(date('Y-m-d H:i:s'), 1, 54, true);
echo "\nAnchor: " . $anchor->format('Y-m-d H:i:s') . "\n";

$block = trx_wingo_fetch_for_anchor_via_estimate($tronscanApiKey, $trongridApiKey, $anchor, 54);
if ($block) {
    echo "Settlement test: OK source={$block['source']} bh={$block['block_number']}\n";
} else {
    echo "Settlement test: FAILED — host must allow HTTPS to apilist.tronscan.org\n";
}
