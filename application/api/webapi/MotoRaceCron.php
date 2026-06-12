<?php
/**
 * MotoRace draw cron — generates results for any completed periods across
 * all 4 game codes. Safe to run every minute (idempotent via UNIQUE key).
 * Can be triggered by cron OR by hitting this URL from a scheduler.
 */
include '../../conn.php';
include '../../functions2.php';
include __DIR__ . '/motorace_logger.php';
include __DIR__ . '/motorace_common.php';

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Kolkata');

$out = ['result' => true, 'generated' => []];
foreach (['MotoRace_1M','MotoRace_3M','MotoRace_5M','MotoRace_10M'] as $gc) {
    $n = mr_backfill_results($conn, $gc);
    $out['generated'][$gc] = $n;
}
mr_log('CRON', 'draw run', $out['generated']);
echo json_encode($out);