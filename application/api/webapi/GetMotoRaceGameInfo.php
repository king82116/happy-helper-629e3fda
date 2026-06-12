<?php
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
[$key, $min] = mr_game_minutes($gameCode);
if (!$min) {
	$out = ['result' => false, 'code' => 400, 'msg' => 'Invalid gameCode', 'data' => []];
	mr_log_response($out);
	echo json_encode($out);
	exit;
}

$now = time();
$periodSec = $min * 60;
$slot = intdiv($now, $periodSec);
$endTs = ($slot + 1) * $periodSec;
$issue = mr_issue_from_slot($slot * $periodSec, $min);

$rates = [];
foreach (['FirstNum','SecondNum','ThirdNum'] as $pt) {
	for ($n = 1; $n <= 10; $n++) {
		$rates[] = ['playType' => $pt, 'playBet' => (string) $n, 'playRate' => 9.33];
	}
}
foreach (['FirstOddEven','SecondOddEven','ThirdOddEven'] as $pt) {
	foreach (['Odd','Even'] as $b) {
		$rates[] = ['playType' => $pt, 'playBet' => $b, 'playRate' => 2];
	}
}
foreach (['FirstBigSmall','SecondBigSmall','ThirdBigSmall'] as $pt) {
	foreach (['Big','Small'] as $b) {
		$rates[] = ['playType' => $pt, 'playBet' => $b, 'playRate' => 2];
	}
}

$out = [
	'result' => true, 'code' => 0, 'msg' => 'Succeed',
	'data' => [
		'gameCode' => $gameCode,
		'lotteryCode' => 'MotoRace',
		'currentIssue' => $issue,
		'issueNumber' => $issue,
		'endTime' => date('Y-m-d H:i:s', $endTs),
		'serverTime' => date('Y-m-d H:i:s', $now),
		'minute' => $min,
		'rates' => $rates,
		'betScopes' => [1, 10, 100, 1000],
		'betMultiples' => [1, 5, 10, 20, 50, 100],
	],
	'serviceTime' => $now * 1000,
];
mr_log_response($out);
echo json_encode($out);
