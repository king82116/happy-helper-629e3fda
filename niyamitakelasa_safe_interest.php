<?php
/**
 * Daily safe-wallet interest cron.
 * Schedule once per day (e.g. 00:05 Asia/Kolkata).
 * Interest: 1% of safe balance when balance >= 50,000.
 */
date_default_timezone_set('Asia/Kolkata');

include __DIR__ . '/serive/samparka.php';
require_once __DIR__ . '/application/functions2.php';

$applied = applySafeDailyInterestForAllEligible($conn);

echo date('Y-m-d H:i:s') . " safe interest applied for {$applied} user(s)\n";
