<?php
/**
 * TRX Wingo: fetch TRON block at second :54 (or configured) and store block time ending in :54.
 */

function trx_wingo_last_block_number(mysqli $conn, string $resultsTable): int
{
    $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $resultsTable);
    $sql = "SELECT `bh` FROM `{$safeTable}` ORDER BY `shonu` DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result && ($row = $result->fetch_assoc()) && $row['bh'] !== null && $row['bh'] !== '') {
        return (int) $row['bh'];
    }
    return 0;
}

/**
 * @return array{ok:bool,body:string,code:int,error:string,url:string}
 */
function trx_wingo_http_request(string $method, string $url, ?string $body = null, array $headers = []): array
{
    $ch = curl_init($url);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 18,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_USERAGENT => 'TRXWingo/1.0',
        CURLOPT_HTTPHEADER => $headers,
    ];

    if (strtoupper($method) === 'POST') {
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = $body ?? '';
    }

    curl_setopt_array($ch, $opts);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $ok = $response !== false && $code >= 200 && $code < 300;

    if (!$ok && $error !== '') {
        error_log("TRX HTTP {$method} {$url} — cURL: {$error}");
    } elseif (!$ok) {
        error_log("TRX HTTP {$method} {$url} — HTTP {$code}");
    }

    return [
        'ok' => $ok,
        'body' => $response === false ? '' : (string) $response,
        'code' => $code,
        'error' => $error,
        'url' => $url,
    ];
}

/** @return string[] */
function trx_wingo_tronscan_hosts(): array
{
    return [
        'https://apilist.tronscan.org',
        'https://apilist.tronscanapi.com',
    ];
}

/** @return array{tronscan_api_key:string,trongrid_api_key:string} */
function trx_wingo_load_api_config(): array
{
    $defaults = [
        'tronscan_api_key' => '',
        'trongrid_api_key' => '',
    ];

    $path = __DIR__ . '/trx_api_config.php';
    if (!is_file($path)) {
        return $defaults;
    }

    $cfg = include $path;

    return array_merge($defaults, is_array($cfg) ? $cfg : []);
}

/** @return string[] */
function trx_wingo_api_headers(string $apiKey): array
{
    return $apiKey !== '' ? ['TRON-PRO-API-KEY: ' . $apiKey] : [];
}

/**
 * Latest mainnet block — TronScan first (no TronGrid key required).
 *
 * @return array{number:int,timestamp_ms:int}|null
 */
function trx_wingo_chain_head(string $tronscanApiKey, string $trongridApiKey = ''): ?array
{
    foreach (trx_wingo_tronscan_hosts() as $host) {
        $res = trx_wingo_http_request(
            'GET',
            $host . '/api/block?sort=-number&limit=1',
            null,
            trx_wingo_api_headers($tronscanApiKey)
        );

        if (!$res['ok']) {
            continue;
        }

        $data = json_decode($res['body'], true);
        $item = $data['data'][0] ?? null;
        if (is_array($item) && !empty($item['number'])) {
            return [
                'number' => (int) $item['number'],
                'timestamp_ms' => (int) ($item['timestamp'] ?? 0),
            ];
        }
    }

    $gridHeaders = trx_wingo_api_headers($trongridApiKey);
    $v1 = trx_wingo_http_request(
        'GET',
        'https://api.trongrid.io/v1/blocks/latest',
        null,
        $gridHeaders
    );

    if ($v1['ok']) {
        $data = json_decode($v1['body'], true);
        $item = $data['data'][0] ?? $data['data'] ?? null;
        if (is_array($item) && !empty($item['number'])) {
            return [
                'number' => (int) $item['number'],
                'timestamp_ms' => (int) ($item['timestamp'] ?? 0),
            ];
        }
    }

    $walletHeaders = array_merge(['Content-Type: application/json'], $gridHeaders);
    $res = trx_wingo_http_request(
        'POST',
        'https://api.trongrid.io/wallet/getnowblock',
        '{}',
        $walletHeaders
    );

    if ($res['ok']) {
        $blockData = json_decode($res['body'], true);
        $bn = (int) ($blockData['block_header']['raw_data']['number'] ?? 0);
        $tsMs = (int) ($blockData['block_header']['raw_data']['timestamp'] ?? 0);
        if ($bn > 0) {
            return ['number' => $bn, 'timestamp_ms' => $tsMs];
        }
    }

    error_log('TRX Wingo: chain head failed — check TronScan key and outbound HTTPS to apilist.tronscan.org');

    return null;
}

/**
 * Wait until the :54 block for the anchor is on-chain (cron often fires at :00).
 */
function trx_wingo_wait_for_anchor(DateTime $anchor, int $bufferSeconds = 6): void
{
    $waitUntil = $anchor->getTimestamp() + $bufferSeconds;
    $now = time();
    if ($now < $waitUntil) {
        usleep(($waitUntil - $now) * 1000000);
    }
}

/**
 * If cron runs before second 55, wait so the current minute's :54 block exists.
 */
function trx_wingo_wait_for_settle_window(int $minSecond = 55): void
{
    date_default_timezone_set('Asia/Kolkata');
    while ((int) date('s') < $minSecond) {
        usleep(250000);
    }
}

/** Use cron :54 anchor only for the live period (not backlog settlement). */
function trx_wingo_should_use_cron_anchor(string $periodDatetime, int $maxAgeSeconds = 120): bool
{
    try {
        $tz = new DateTimeZone('Asia/Kolkata');
        $period = new DateTime($periodDatetime, $tz);
        $now = new DateTime('now', $tz);

        return ($now->getTimestamp() - $period->getTimestamp()) <= $maxAgeSeconds;
    } catch (Exception $e) {
        return true;
    }
}

/**
 * Anchor datetime for settlement.
 * 1min (cron mode): :54 of the minute that just ended — matches official TRX Wingo.
 * 1min (legacy): :54 of period start minute.
 * 3/5/10min: :54 near period end (start + interval - 9s).
 */
function trx_wingo_settlement_anchor(
    string $periodDatetime,
    int $intervalMinutes = 1,
    int $targetSecond = 54,
    bool $useCronAnchor = false
): DateTime {
    $tz = new DateTimeZone('Asia/Kolkata');

    if ($intervalMinutes <= 1 && $useCronAnchor) {
        $dt = new DateTime('now', $tz);
        $dt->setTime((int) $dt->format('H'), (int) $dt->format('i'), $targetSecond, 0);
        $dt->modify('-1 minute');

        return $dt;
    }

    $dt = new DateTime($periodDatetime, $tz);

    if ($intervalMinutes <= 1) {
        $dt->setTime((int) $dt->format('H'), (int) $dt->format('i'), $targetSecond, 0);

        return $dt;
    }

    $offsetSeconds = ($intervalMinutes * 60) - 9;
    $dt->modify('+' . $offsetSeconds . ' seconds');
    $dt->setTime((int) $dt->format('H'), (int) $dt->format('i'), $targetSecond, 0);

    return $dt;
}

/**
 * Stored/displayed block time — always ends with the game second (default 54).
 */
function trx_wingo_block_display_time(
    string $periodDatetime,
    int $intervalMinutes = 1,
    int $targetSecond = 54,
    bool $useCronAnchor = false
): string {
    return trx_wingo_settlement_anchor($periodDatetime, $intervalMinutes, $targetSecond, $useCronAnchor)
        ->format('Y-m-d H:i:s');
}

/**
 * @return array{hash:string,timestamp_ms:int,block_number:int,source:string}|null
 */
function trx_wingo_fetch_from_tronscan(
    string $apiKey,
    int $targetSecond,
    ?string $periodDatetime = null,
    int $intervalMinutes = 1,
    bool $useCronAnchor = false,
    ?DateTime $anchorOverride = null
): ?array {
    if ($anchorOverride !== null) {
        $anchor = $anchorOverride;
    } else {
        if ($periodDatetime === null || $periodDatetime === '') {
            $periodDatetime = date('Y-m-d H:i:s');
        }
        $anchor = trx_wingo_settlement_anchor($periodDatetime, $intervalMinutes, $targetSecond, $useCronAnchor);
    }

    $centerTs = $anchor->getTimestamp();
    $startMs = ($centerTs - 45) * 1000;
    $endMs = ($centerTs + 45) * 1000;
    $path = '/api/block?sort=-number&start=0&limit=20'
        . "&start_timestamp={$startMs}&end_timestamp={$endMs}";

    foreach (trx_wingo_tronscan_hosts() as $host) {
        $res = trx_wingo_http_request(
            'GET',
            $host . $path,
            null,
            ['TRON-PRO-API-KEY: ' . $apiKey]
        );

        if (!$res['ok']) {
            continue;
        }

        $picked = trx_wingo_pick_block_from_tronscan_list($res['body'], $targetSecond);
        if ($picked !== null) {
            $picked['source'] = 'tronscan:' . parse_url($host, PHP_URL_HOST);

            return $picked;
        }
    }

    return null;
}

/**
 * @return array{hash:string,timestamp_ms:int,block_number:int,source:string}|null
 */
function trx_wingo_pick_block_from_tronscan_list(string $jsonBody, int $targetSecond): ?array
{
    $data = json_decode($jsonBody, true);
    if (!is_array($data['data'] ?? null) || count($data['data']) === 0) {
        return null;
    }

    $targetSecondPadded = str_pad((string) $targetSecond, 2, '0', STR_PAD_LEFT);
    $targetBlock = null;
    $closestBlock = null;
    $closestDiff = PHP_INT_MAX;

    foreach ($data['data'] as $item) {
        if (!isset($item['timestamp'], $item['hash'])) {
            continue;
        }

        $tsSec = (int) floor(((int) $item['timestamp']) / 1000);
        $blockSecond = (new DateTime('@' . $tsSec))
            ->setTimezone(new DateTimeZone('Asia/Kolkata'))
            ->format('s');

        if ($blockSecond === $targetSecondPadded) {
            $targetBlock = $item;
            break;
        }

        $diff = abs((int) $blockSecond - $targetSecond);
        if ($diff < $closestDiff) {
            $closestDiff = $diff;
            $closestBlock = $item;
        }
    }

    if ($targetBlock === null) {
        $targetBlock = $closestBlock;
    }

    if ($targetBlock === null) {
        return null;
    }

    return [
        'hash' => (string) $targetBlock['hash'],
        'timestamp_ms' => (int) $targetBlock['timestamp'],
        'block_number' => (int) ($targetBlock['number'] ?? 0),
        'source' => 'tronscan',
    ];
}

/**
 * Fetch a mainnet block by height via TronGrid (works when TronScan is blocked).
 *
 * @return array{hash:string,timestamp_ms:int,block_number:int,source:string}|null
 */
function trx_wingo_fetch_block_bynum_trongrid(string $trongridApiKey, int $blockNumber): ?array
{
    if ($blockNumber <= 0) {
        return null;
    }

    $headers = trx_wingo_api_headers($trongridApiKey);

    $v1 = trx_wingo_http_request(
        'GET',
        'https://api.trongrid.io/v1/blocks/' . $blockNumber,
        null,
        $headers
    );

    if ($v1['ok']) {
        $data = json_decode($v1['body'], true);
        $item = $data['data'][0] ?? $data['data'] ?? null;
        if (is_array($item) && !empty($item['hash'])) {
            return [
                'hash' => (string) $item['hash'],
                'timestamp_ms' => (int) ($item['timestamp'] ?? 0),
                'block_number' => (int) ($item['number'] ?? $blockNumber),
                'source' => 'trongrid-v1',
            ];
        }
    }

    $res = trx_wingo_http_request(
        'POST',
        'https://api.trongrid.io/wallet/getblockbynum',
        json_encode(['num' => $blockNumber]),
        array_merge(['Content-Type: application/json'], $headers)
    );

    if (!$res['ok'] && $headers === []) {
        return null;
    }

    if (!$res['ok']) {
        $res = trx_wingo_http_request(
            'POST',
            'https://api.trongrid.io/wallet/getblockbynum',
            json_encode(['num' => $blockNumber]),
            ['Content-Type: application/json']
        );
    }

    if (!$res['ok']) {
        return null;
    }

    $blockData = json_decode($res['body'], true);
    $bn = (int) ($blockData['block_header']['raw_data']['number'] ?? 0);
    $blockId = (string) ($blockData['blockID'] ?? '');
    $tsMs = (int) ($blockData['block_header']['raw_data']['timestamp'] ?? 0);

    if ($bn <= 0 || $blockId === '') {
        return null;
    }

    return [
        'hash' => $blockId,
        'timestamp_ms' => $tsMs > 0 ? $tsMs : (int) round(microtime(true) * 1000),
        'block_number' => $bn,
        'source' => 'trongrid-bynum',
    ];
}

/**
 * @return array{hash:string,timestamp_ms:int,block_number:int,source:string}|null
 */
function trx_wingo_fetch_block_by_number(
    string $tronscanApiKey,
    string $trongridApiKey,
    int $blockNumber
): ?array {
    foreach (trx_wingo_tronscan_hosts() as $host) {
        $res = trx_wingo_http_request(
            'GET',
            $host . '/api/block?number=' . $blockNumber,
            null,
            ['TRON-PRO-API-KEY: ' . $tronscanApiKey]
        );

        if (!$res['ok']) {
            continue;
        }

        $data = json_decode($res['body'], true);
        if (!empty($data['data'][0]['hash'])) {
            $item = $data['data'][0];

            return [
                'hash' => (string) $item['hash'],
                'timestamp_ms' => (int) $item['timestamp'],
                'block_number' => (int) ($item['number'] ?? $blockNumber),
                'source' => 'tronscan-by-number:' . parse_url($host, PHP_URL_HOST),
            ];
        }
    }

    if ($trongridApiKey !== '') {
        $block = trx_wingo_fetch_block_bynum_trongrid($trongridApiKey, $blockNumber);
        if ($block !== null) {
            return $block;
        }
    }

    $block = trx_wingo_fetch_block_bynum_trongrid('', $blockNumber);

    return $block;
}

/**
 * @return array{hash:string,timestamp_ms:int,block_number:int,source:string}|null
 */
function trx_wingo_fetch_from_trongrid(string $apiKey, int $blockNumber = 0, ?string $trongridApiKey = null): ?array
{
    if ($blockNumber > 0 && $trongridApiKey !== null && $trongridApiKey !== '') {
        $block = trx_wingo_fetch_block_bynum_trongrid($trongridApiKey, $blockNumber);
        if ($block !== null) {
            return $block;
        }
    }

    if ($blockNumber > 0) {
        $url = 'https://apilist.tronscanapi.com/api/block?number=' . $blockNumber;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_HTTPHEADER => ['TRON-PRO-API-KEY: ' . $apiKey],
        ]);
        $response = curl_exec($ch);
        $httpStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response !== false && $httpStatus === 200) {
            $data = json_decode($response, true);
            if (!empty($data['data'][0]['hash'])) {
                $item = $data['data'][0];
                return [
                    'hash' => (string) $item['hash'],
                    'timestamp_ms' => (int) $item['timestamp'],
                    'block_number' => (int) ($item['number'] ?? $blockNumber),
                    'source' => 'tronscan-by-number',
                ];
            }
        }
    }

    $gridKey = ($trongridApiKey !== null && $trongridApiKey !== '') ? $trongridApiKey : $apiKey;
    $ch = curl_init('https://api.trongrid.io/wallet/getnowblock');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'TRON-PRO-API-KEY: ' . $gridKey,
        ],
    ]);

    $response = curl_exec($ch);
    $httpStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpStatus !== 200) {
        return null;
    }

    $blockData = json_decode($response, true);
    $bn = (int) ($blockData['block_header']['raw_data']['number'] ?? 0);
    $blockId = (string) ($blockData['blockID'] ?? '');
    $tsMs = (int) ($blockData['block_header']['raw_data']['timestamp'] ?? 0);

    if ($bn <= 0 || $blockId === '') {
        return null;
    }

    return [
        'hash' => $blockId,
        'timestamp_ms' => $tsMs > 0 ? $tsMs : (int) round(microtime(true) * 1000),
        'block_number' => $bn,
        'source' => 'trongrid-now',
    ];
}

/**
 * @return array{number:int,timestamp_ms:int}|null
 */
/** @deprecated Use trx_wingo_chain_head */
function trx_wingo_trongrid_now_block(string $trongridApiKey, ?string $tronscanApiKey = null): ?array
{
    $scanKey = ($tronscanApiKey !== null && $tronscanApiKey !== '') ? $tronscanApiKey : '';

    return trx_wingo_chain_head($scanKey, $trongridApiKey);
}

/**
 * Estimate mainnet block at anchor from TronGrid head, then load hash via TronScan by number.
 *
 * @return array{hash:string,timestamp_ms:int,block_number:int,source:string}|null
 */
function trx_wingo_fetch_for_anchor_via_estimate(
    string $tronscanApiKey,
    string $trongridApiKey,
    DateTime $anchor,
    int $targetSecond = 54
): ?array {
    $head = trx_wingo_chain_head($tronscanApiKey, $trongridApiKey);
    if ($head === null) {
        error_log('TRX Wingo: estimate aborted — cannot read chain head (TronScan key / network).');

        return null;
    }

    $anchorMs = $anchor->getTimestamp() * 1000;
    $diffMs = $head['timestamp_ms'] - $anchorMs;
    $blocksBack = max(0, (int) round($diffMs / 3000));
    $targetBn = $head['number'] - $blocksBack;

    $targetSecondPadded = str_pad((string) $targetSecond, 2, '0', STR_PAD_LEFT);
    $best = null;
    $bestDiff = PHP_INT_MAX;

    for ($delta = -5; $delta <= 5; $delta++) {
        $candidate = trx_wingo_fetch_block_by_number(
            $tronscanApiKey,
            $trongridApiKey,
            $targetBn + $delta
        );
        if ($candidate === null || empty($candidate['hash'])) {
            continue;
        }

        $tsSec = (int) floor($candidate['timestamp_ms'] / 1000);
        $blockSecond = (new DateTime('@' . $tsSec))
            ->setTimezone(new DateTimeZone('Asia/Kolkata'))
            ->format('s');

        if ($blockSecond === $targetSecondPadded) {
            $candidate['source'] = 'tronscan-estimate';
            return $candidate;
        }

        $diff = abs((int) $blockSecond - $targetSecond);
        if ($diff < $bestDiff) {
            $bestDiff = $diff;
            $best = $candidate;
        }
    }

    if ($best !== null) {
        $best['source'] = 'tronscan-estimate-nearest';
        error_log('TRX Wingo: estimate path used nearest :' . $targetSecondPadded . ' block.');
    }

    return $best;
}

/**
 * @return array{hash:string,timestamp_ms:int,block_number:int,source:string,digit:int,banna:string,display_time:string}
 */
function trx_wingo_resolve_block(
    mysqli $conn,
    string $resultsTable,
    string $tronscanApiKey,
    string $trongridApiKey,
    string $targetSecondSs,
    string $logLabel = 'TRX Wingo',
    ?string $periodDatetime = null,
    int $intervalMinutes = 1
): array {
    $targetSecond = (int) $targetSecondSs;

    if ($periodDatetime === null || $periodDatetime === '') {
        $periodDatetime = date('Y-m-d H:i:s');
    }

    $useCronAnchor = $intervalMinutes <= 1 && trx_wingo_should_use_cron_anchor($periodDatetime);

    $anchor = trx_wingo_settlement_anchor($periodDatetime, $intervalMinutes, $targetSecond, $useCronAnchor);
    trx_wingo_wait_for_anchor($anchor);

    $displayTime = $anchor->format('Y-m-d H:i:s');

    $block = null;
    for ($attempt = 0; $attempt < 3 && $block === null; $attempt++) {
        if ($attempt > 0) {
            sleep(2);
        }

        $block = trx_wingo_fetch_from_tronscan(
            $tronscanApiKey,
            $targetSecond,
            $periodDatetime,
            $intervalMinutes,
            $useCronAnchor,
            $anchor
        );
    }

    if ($block === null) {
        error_log("{$logLabel}: TronScan time window failed, estimating block from TronGrid head.");
        $block = trx_wingo_fetch_for_anchor_via_estimate(
            $tronscanApiKey,
            $trongridApiKey,
            $anchor,
            $targetSecond
        );
    }

    if ($block === null) {
        $head = trx_wingo_chain_head($tronscanApiKey, $trongridApiKey);
        if ($head !== null) {
            $blocksBack = max(0, (int) round(($head['timestamp_ms'] - $anchor->getTimestamp() * 1000) / 3000));
            $targetBn = $head['number'] - $blocksBack;
            error_log("{$logLabel}: Last resort by block number bh={$targetBn} (head={$head['number']}).");
            for ($delta = -8; $delta <= 8 && $block === null; $delta++) {
                $block = trx_wingo_fetch_block_by_number(
                    $tronscanApiKey,
                    $trongridApiKey,
                    $targetBn + $delta
                );
            }
            if ($block !== null) {
                $block['source'] = 'chain-estimate-final';
            }
        } else {
            error_log("{$logLabel}: Last resort skipped — chain head unavailable.");
        }
    }

    if ($block === null) {
        error_log("{$logLabel}: CRITICAL — could not fetch mainnet block for {$displayTime}. Set TronScan API key in serive/trx_api_config.php and allow HTTPS to apilist.tronscan.org");
        $block = [
            'hash' => '',
            'timestamp_ms' => $anchor->getTimestamp() * 1000,
            'block_number' => 0,
            'source' => 'failed',
        ];
    }

    $digit = trx_wingo_last_hash_digit($block['hash']);
    $block['digit'] = $digit;
    $block['banna'] = trx_wingo_color_for_digit($digit);
    $block['display_time'] = $displayTime;

    return $block;
}

function trx_wingo_last_hash_digit(string $hash): int
{
    for ($i = strlen($hash) - 1; $i >= 0; $i--) {
        if (is_numeric($hash[$i])) {
            return (int) $hash[$i];
        }
    }
    return 0;
}

function trx_wingo_color_for_digit(int $digit): string
{
    if ($digit === 0) {
        return 'red,violet';
    }
    if ($digit === 5) {
        return 'green,violet';
    }
    if (in_array($digit, [1, 3, 7, 9], true)) {
        return 'green';
    }
    if (in_array($digit, [2, 4, 6, 8], true)) {
        return 'red';
    }
    return 'fallback';
}

/**
 * Force block time to Y-m-d H:i:s with seconds = 54 (Asia/Kolkata).
 * App expects full datetime, e.g. "2025-01-30 18:28:54" — not "18:28:54" only.
 */
function trx_wingo_normalize_block_time_display(?string $datetime, int $targetSecond = 54): string
{
    $datetime = trim((string) $datetime);
    if ($datetime === '' || $datetime === '0000-00-00 00:00:00') {
        return '';
    }
    try {
        $tz = new DateTimeZone('Asia/Kolkata');
        $dt = new DateTime($datetime, $tz);
        $dt->setTime((int) $dt->format('H'), (int) $dt->format('i'), $targetSecond, 0);
        return $dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        return (string) $datetime;
    }
}

/**
 * blockTime for API row — never return empty when issue number exists.
 */
function trx_wingo_block_time_for_api(?string $dinankavannuracisi, ?string $issueNumber = null, int $targetSecond = 54): string
{
    $normalized = trx_wingo_normalize_block_time_display($dinankavannuracisi, $targetSecond);
    if ($normalized !== '') {
        return $normalized;
    }

    $issueDigits = preg_replace('/\D/', '', (string) $issueNumber);
    if (strlen($issueDigits) >= 8) {
        $y = substr($issueDigits, 0, 4);
        $m = substr($issueDigits, 4, 2);
        $d = substr($issueDigits, 6, 2);
        $tz = new DateTimeZone('Asia/Kolkata');
        $dt = new DateTime('now', $tz);
        $dt->setDate((int) $y, (int) $m, (int) $d);
        $dt->setTime((int) $dt->format('H'), (int) $dt->format('i'), $targetSecond, 0);
        return $dt->format('Y-m-d H:i:s');
    }

    $tz = new DateTimeZone('Asia/Kolkata');
    $dt = new DateTime('now', $tz);
    $dt->setTime((int) $dt->format('H'), (int) $dt->format('i'), $targetSecond, 0);
    return $dt->format('Y-m-d H:i:s');
}

/** @deprecated Use display_time from trx_wingo_resolve_block */
function trx_wingo_format_time_from_ms(int $timestampMs): string
{
    return date('Y-m-d H:i:s', (int) floor($timestampMs / 1000));
}
