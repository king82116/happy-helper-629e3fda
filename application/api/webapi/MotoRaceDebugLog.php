<?php
/**
 * Tail viewer for motorace_debug.log
 * Usage: /application/api/webapi/MotoRaceDebugLog.php?key=mrdbg2026&lines=200
 * Optional: &clear=1 to wipe the log, &grep=foo to filter.
 * CHANGE the key below before deploying to production.
 */
$KEY = 'mrdbg2026';

header('Content-Type: text/plain; charset=utf-8');
if (($_GET['key'] ?? '') !== $KEY) { http_response_code(403); echo "forbidden"; exit; }

$file = __DIR__ . '/motorace_debug.log';
if (!empty($_GET['clear'])) { @file_put_contents($file, ''); echo "cleared\n"; exit; }
if (!is_file($file)) { echo "(no log yet) $file\n"; exit; }

$lines = max(1, min(2000, (int) ($_GET['lines'] ?? 200)));
$grep = $_GET['grep'] ?? '';

$all = file($file, FILE_IGNORE_NEW_LINES) ?: [];
if ($grep !== '') {
    $all = array_values(array_filter($all, fn($l) => stripos($l, $grep) !== false));
}
$tail = array_slice($all, -$lines);
echo "# motorace_debug.log — last " . count($tail) . " line(s)\n";
echo "# file size: " . filesize($file) . " bytes\n\n";
echo implode("\n", $tail) . "\n";