<?php
/**
 * MotoRace history list — reads from dedicated `moto_race_results` table.
 * Before returning, ensures any newly completed periods are drawn.
 * Response shape matches the saas client: { result:true, data:{ list, totalPage, statistics } }.
 */
include '../../conn.php';
include '../../functions2.php';
include __DIR__ . '/motorace_logger.php';
include __DIR__ . '/motorace_common.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
date_default_timezone_set('Asia/Kolkata');
mr_log_request();

$gameCode = $_GET['gameCode'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$body = json_decode(file_get_contents('php://input'), true) ?: [];
	$gameCode = $body['gameCode'] ?? $gameCode;
	$pageNo = (int) ($body['pageNo'] ?? ($_GET['pageNo'] ?? 1));
	$pageSize = (int) ($body['pageSize'] ?? ($_GET['pageSize'] ?? 10));
} else {
	$pageNo = (int) ($_GET['pageNo'] ?? 1);
	$pageSize = (int) ($_GET['pageSize'] ?? 10);
}
if ($pageNo < 1) $pageNo = 1;
if ($pageSize < 1 || $pageSize > 50) $pageSize = 10;

[$key, $minutes] = mr_game_minutes($gameCode);
if (!$minutes || !$conn) {
	mr_log('ERR', 'invalid gameCode or DB missing', ['gameCode' => $gameCode, 'key' => $key, 'connOk' => (bool)$conn]);
	$out = ['result' => false, 'code' => 400, 'msg' => 'invalid gameCode or DB unavailable',
		'data' => ['list' => [], 'totalPage' => 0, 'statistics' => array_combine(
			array_map('strval', range(1, 10)),
			array_fill(0, 10, [0, 0, 0])
		)]];
	mr_log_response($out); echo json_encode($out); exit;
}
mr_check_db($conn, 'history');

// Generate completed periods + pre-draw the live period for race animation.
mr_ensure_current_period_result($conn, $gameCode);

$gEsc = mysqli_real_escape_string($conn, $gameCode);
$offset = ($pageNo - 1) * $pageSize;

$totalRs = mr_query($conn, "SELECT COUNT(*) FROM moto_race_results WHERE game_code='$gEsc' AND CHAR_LENGTH(issue_number) >= 17", 'count');
$totalRow = $totalRs ? mysqli_fetch_row($totalRs) : [0];
$totalCount = (int) ($totalRow[0] ?? 0);
$totalPage = (int) ceil($totalCount / $pageSize);

// Ignore legacy YmdHi rows (12 digits) — they never match the live WinGo-style issue id.
$rs = mr_query($conn, "SELECT issue_number, ranking, end_time FROM moto_race_results WHERE game_code='$gEsc' AND CHAR_LENGTH(issue_number) >= 17 ORDER BY end_time DESC LIMIT $pageSize OFFSET $offset", 'list');
$list = [];
if ($rs) {
		while ($row = mysqli_fetch_assoc($rs)) {
		$ranks = array_map('intval', explode(',', $row['ranking']));
		$list[] = mr_history_row($row['issue_number'], $ranks, $row['end_time']);
	}
	mysqli_free_result($rs);
}

// Live period must be list[0] so client currentResult matches GetWingoDrawIssue.
$list = mr_pin_live_issue_first($conn, $gameCode, $list);

// Aggregate statistics: per-number 1st/2nd/3rd counts (last 100 draws).
$agg = mr_normalize_number_statistics(mr_build_number_statistics($conn, $gameCode, 100));

$out = [
	'result' => true,
	'code' => 0,
	'msg' => 'Succeed',
	'data' => [
		'list' => $list,
		'pageNo' => $pageNo,
		'pageSize' => $pageSize,
		'totalPage' => $totalPage,
		'totalCount' => $totalCount,
		'statistics' => $agg,
	],
];
mr_log('OK', 'history served', ['gameCode' => $gameCode, 'rows' => count($list), 'totalCount' => $totalCount]);
mr_log_response($out);
echo json_encode($out);