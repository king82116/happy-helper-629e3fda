<?php
/**
 * Combined 1-minute cron: K3 (type 9) + 5D (type 5).
 * Schedule: * * * * * php /path/to/niyamitakelasa_k3_5d_1min.php
 */
date_default_timezone_set('Asia/Kolkata');

function waitForSecond($desiredSecond) {
	while (true) {
		if (date('s') == $desiredSecond) {
			break;
		}
		usleep(100000);
	}
}

waitForSecond('00');
define('LOTTERY_CRON_SKIP_WAIT', true);

$scriptDir = __DIR__;
$jobs = [
	'niyamitakelasa_kemuru.php',
	'niyamitakelasa_aidudi.php',
];

foreach ($jobs as $job) {
	$path = $scriptDir . '/' . $job;
	if (!is_file($path)) {
		error_log('[CRON] Missing: ' . $path);
		continue;
	}
	try {
		include $path;
		error_log('[CRON] OK: ' . $job . ' at ' . date('Y-m-d H:i:s'));
	} catch (Throwable $e) {
		error_log('[CRON] FAIL: ' . $job . ' - ' . $e->getMessage());
	}
}
