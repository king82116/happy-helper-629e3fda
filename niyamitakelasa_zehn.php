<?php
// Hardened cron harness for 30-second game scheduler
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Kolkata');

error_log("[CRON] niyamitakelasa_zehn.php started at " . date('Y-m-d H:i:s'));

function waitForSecond(array $desiredSeconds): void
{
    while (true) {
        $currentSecond = date('s');
        if (in_array($currentSecond, $desiredSeconds, true)) {
            break;
        }
        usleep(100000); // 0.1 second poll to avoid busy wait
    }
}

// Run exactly on the 30-second boundaries to avoid off-by-one period issues
waitForSecond(['00', '30']);

$scriptDir = __DIR__;
$dbConfigPath = $scriptDir . '/serive/db_config.php';

error_log("[CRON] Using db_config path: " . $dbConfigPath);

if (!file_exists($dbConfigPath)) {
    error_log("[CRON] ERROR: db_config.php not found at: " . $dbConfigPath);
    exit;
}

include $dbConfigPath;

try {

if (!isset($conn) || !$conn) {
    error_log("[CRON] ERROR: Database connection failed");
    exit;
}

$processingFilePath = $scriptDir . '/nayakaphalitansa_mulaka_unohs_zehn.php';
if (!file_exists($processingFilePath)) {
    error_log("[CRON] ERROR: Processing file not found at: " . $processingFilePath);
    exit;
}

error_log("[CRON] Settling previous period using: " . $processingFilePath);
include $processingFilePath;

// Mirror the Node reference implementation: use Reykjavik (UTC) for period id generation
$reykjavikTz = new DateTimeZone('Atlantic/Reykjavik');
$kolkataTz = new DateTimeZone('Asia/Kolkata');

$nowReykjavik = new DateTimeImmutable('now', $reykjavikTz);
$startOfDayReykjavik = $nowReykjavik->setTime(0, 0, 0);
$diffInSeconds = $nowReykjavik->getTimestamp() - $startOfDayReykjavik->getTimestamp();

$maxSequencesPerDay = intdiv(86400, 30); // 2880 periods in a day
$sequenceNumber = intdiv($diffInSeconds, 30) + 1; // 1..2880
if ($sequenceNumber > $maxSequencesPerDay) {
    $sequenceNumber = 1;
}

$currentDate = $nowReykjavik->format('Ymd');
$uniqueSequence = str_pad((string) $sequenceNumber, 4, '0', STR_PAD_LEFT);
$nextGameId = $currentDate . '10005' . $uniqueSequence;

// Record the period start time in the local (Asia/Kolkata) timezone so API consumers get current timestamps.
$nowKolkata = new DateTimeImmutable('now', $kolkataTz);
$alignedSeconds = intdiv((int) $nowKolkata->format('s'), 30) * 30;
$periodStart = $nowKolkata->setTime(
    (int) $nowKolkata->format('H'),
    (int) $nowKolkata->format('i'),
    $alignedSeconds
);
$periodStartTime = $periodStart->format('Y-m-d H:i:s');

error_log("[CRON] Prepared next period id: $nextGameId (start: $periodStartTime)");

$lastResult = mysqli_query($conn, "SELECT atadaaidi FROM `gelluonduhogu_zehn` ORDER BY kramasankhye DESC LIMIT 1");
$lastRow = $lastResult ? mysqli_fetch_assoc($lastResult) : null;
if ($lastResult) {
    mysqli_free_result($lastResult);
}

if ($lastRow) {
    error_log("[CRON] Latest stored period id: " . $lastRow['atadaaidi']);
}

if ($lastRow && substr($lastRow['atadaaidi'], 0, 8) !== $currentDate && $sequenceNumber === 1) {
    error_log("[CRON] New day detected, resetting gelluonduhogu_zehn");
    if (!mysqli_query($conn, "TRUNCATE TABLE `gelluonduhogu_zehn`")) {
        error_log("[CRON] ERROR: Failed to truncate gelluonduhogu_zehn - " . mysqli_error($conn));
    }
}

$nextGameIdEsc = mysqli_real_escape_string($conn, $nextGameId);
$periodStartEsc = mysqli_real_escape_string($conn, $periodStartTime);

$existingNext = mysqli_query($conn, "SELECT 1 FROM `gelluonduhogu_zehn` WHERE atadaaidi = '$nextGameIdEsc' LIMIT 1");
$nextExists = $existingNext ? mysqli_num_rows($existingNext) : 0;
if ($existingNext) {
    mysqli_free_result($existingNext);
}

if ($nextExists > 0) {
    $updateQuery = "UPDATE `gelluonduhogu_zehn` SET dinankavannuracisi = '$periodStartEsc' WHERE atadaaidi = '$nextGameIdEsc'";
    if (mysqli_query($conn, $updateQuery)) {
        error_log("[CRON] Next period already present: $nextGameId (start time refreshed)");
    } else {
        error_log("[CRON] ERROR: Failed to refresh start time for $nextGameId - " . mysqli_error($conn));
    }
} else {
    $insertQuery = "INSERT INTO `gelluonduhogu_zehn` (`atadaaidi`, `dinankavannuracisi`) VALUES ('$nextGameIdEsc', '$periodStartEsc')";
    if (mysqli_query($conn, $insertQuery)) {
        error_log("[CRON] Next period created successfully: $nextGameId");
    } else {
        error_log("[CRON] ERROR: Failed to create next period - " . mysqli_error($conn));
    }
}

if (mysqli_query($conn, "UPDATE hastacalita_phalitansa_zehn SET sthiti='0'")) {
    error_log("[CRON] Reset hastacalita_phalitansa_zehn status");
} else {
    error_log("[CRON] ERROR: Failed to reset hastacalita_phalitansa_zehn - " . mysqli_error($conn));
}

error_log("[CRON] niyamitakelasa_zehn.php completed at " . date('Y-m-d H:i:s'));
require_once __DIR__ . '/application/functions2.php';
trimLotteryHistoryTable($conn, 'gellaluhogiondu_phalitansa_zehn');

} finally {
    closeDatabaseConnection($conn ?? null);
    $conn = null;
}

?>
