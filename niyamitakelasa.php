<?php
date_default_timezone_set('Asia/Kolkata');

require_once __DIR__ . '/application/functions2.php';

function wingo1m_wait_for_minute_start() {
	while (date('s') != '00') {
		usleep(100000);
	}
}

/** Period that just closed when cron fires at :00 (previous minute). */
function wingo1m_period_to_settle_now() {
	return wingo1m_build_period_id(time() - 60);
}

/** Next open period for the current minute. */
function wingo1m_next_period_id_now() {
	return wingo1m_build_period_id(time());
}

function wingo1m_period_date_prefix($periodId) {
	return substr((string) $periodId, 0, 8);
}

function wingo1m_period_increment($periodId) {
	return (string) ((int) $periodId + 1);
}

/** True only when the period id's calendar day rolled over (not when cron is late). */
function wingo1m_should_reset_period_table($latestId, $nextId) {
	if ($latestId === null || $latestId === '') {
		return false;
	}
	return wingo1m_period_date_prefix($latestId) < wingo1m_period_date_prefix($nextId);
}

/** Pick the open period id for this minute (never skip ahead on duplicate cron runs). */
function wingo1m_resolve_next_insert_id($latestId, $capturedNextId) {
	if ($latestId === null || $latestId === '') {
		return $capturedNextId;
	}
	if ((int) $latestId === (int) $capturedNextId) {
		return $latestId;
	}
	if ((int) $latestId > (int) $capturedNextId) {
		return $capturedNextId;
	}
	$incremented = wingo1m_period_increment($latestId);
	return ((int) $incremented >= (int) $capturedNextId) ? $incremented : $capturedNextId;
}

/**
 * Insert or refresh the open period row (runs before settlement so UI always has an issue).
 */
function wingo1m_ensure_open_period($conn, $nextPeriodId, $periodStartTime) {
	$aligned = wingo_period_times_from_id($nextPeriodId, 1);
	if ($aligned !== null) {
		$periodStartTime = $aligned['startTime'];
	}
	$nextEsc = mysqli_real_escape_string($conn, (string) $nextPeriodId);
	$startEsc = mysqli_real_escape_string($conn, (string) $periodStartTime);

	$lastResult = mysqli_query($conn, 'SELECT atadaaidi FROM `gelluonduhogu` ORDER BY kramasankhye DESC LIMIT 1');
	$lastRow = $lastResult ? mysqli_fetch_assoc($lastResult) : null;
	if ($lastResult) {
		mysqli_free_result($lastResult);
	}

	if ($lastRow && wingo1m_should_reset_period_table($lastRow['atadaaidi'], $nextPeriodId)) {
		error_log('[CRON] Wingo 1m new day — resetting gelluonduhogu');
		if (!mysqli_query($conn, 'TRUNCATE TABLE `gelluonduhogu`')) {
			error_log('[CRON] Wingo 1m truncate failed: ' . mysqli_error($conn));
		}
		$lastRow = null;
	}

	$existsResult = mysqli_query($conn, "SELECT 1 FROM `gelluonduhogu` WHERE atadaaidi = '$nextEsc' LIMIT 1");
	$exists = $existsResult && mysqli_num_rows($existsResult) > 0;
	if ($existsResult) {
		mysqli_free_result($existsResult);
	}

	if ($exists) {
		// Keep original round start — do not reset countdown mid-minute.
		error_log('[CRON] Wingo 1m open period already exists: ' . $nextPeriodId);
		return;
	}

	$sql = "INSERT INTO `gelluonduhogu` (`atadaaidi`,`dinankavannuracisi`) VALUES ('$nextEsc','$startEsc')";
	if (mysqli_query($conn, $sql)) {
		error_log('[CRON] Wingo 1m open period inserted: ' . $nextPeriodId);
	} else {
		error_log('[CRON] Wingo 1m insert period failed: ' . mysqli_error($conn));
	}
}

wingo1m_wait_for_minute_start();

$scriptDir = __DIR__;
$dbConfigPath = $scriptDir . '/serive/db_config.php';
if (!is_file($dbConfigPath)) {
	error_log('[CRON] niyamitakelasa.php: db_config.php not found');
	exit(1);
}

require_once $dbConfigPath;

if (!isset($conn) || !$conn) {
	error_log('[CRON] niyamitakelasa.php: database connection failed');
	exit(1);
}

try {
	// Lock period ids at :00 — settlement must not recompute after a slow API call.
	$periodToSettle = wingo1m_period_to_settle_now();
	$capturedNextPeriodId = wingo1m_next_period_id_now();
	$periodStartTime = wingo1m_current_round_start_time();

	$beforeResult = mysqli_query($conn, 'SELECT atadaaidi FROM `gelluonduhogu` ORDER BY kramasankhye DESC LIMIT 1');
	$latestPeriodId = null;
	if ($beforeResult) {
		$rowBefore = mysqli_fetch_assoc($beforeResult);
		if (!empty($rowBefore['atadaaidi'])) {
			$latestPeriodId = (string) $rowBefore['atadaaidi'];
			// Only trust DB when it is the period closing this minute (not the next open period).
			if ((int) $latestPeriodId === (int) $periodToSettle) {
				$periodToSettle = $latestPeriodId;
			} elseif ((int) $latestPeriodId === (int) $capturedNextPeriodId) {
				$periodToSettle = wingo1m_period_to_settle_now();
			} elseif ((int) $latestPeriodId < (int) $periodToSettle) {
				$periodToSettle = $latestPeriodId;
			}
		}
		mysqli_free_result($beforeResult);
	}

	$nextPeriodToInsert = wingo1m_resolve_next_insert_id($latestPeriodId, $capturedNextPeriodId);
	error_log('[CRON] Wingo 1m settle=' . $periodToSettle . ' insert_open=' . $nextPeriodToInsert);

	// Store open period first so GetGameIssue / bets work even if settlement is slow.
	wingo1m_ensure_open_period($conn, $nextPeriodToInsert, $periodStartTime);

	$processingFile = $scriptDir . '/nayakaphalitansa_mulaka_unohs.php';
	if (!is_file($processingFile)) {
		error_log('[CRON] niyamitakelasa.php: missing settlement file');
		exit(1);
	}
	include $processingFile;

	mysqli_query($conn, "UPDATE hastacalita_phalitansa SET sthiti='0'");

	if (function_exists('trimLotteryHistoryTable')) {
		trimLotteryHistoryTable($conn, 'gellaluhogiondu_phalitansa');
	}
} catch (Throwable $e) {
	error_log('[CRON] niyamitakelasa.php: ' . $e->getMessage());
	exit(1);
}
