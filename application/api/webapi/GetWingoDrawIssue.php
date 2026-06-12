<?php
/**
 * Local replacement for https://draw.ar-lottery01.com/{lotteryCode}/{gameCode}.json
 * Supplies the current period to the saas WinGo UI (TimeLeft__C-id).
 */
include '../../conn.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(204);
	exit;
}

date_default_timezone_set('Asia/Kolkata');

require_once __DIR__ . '/../../functions2.php';

$gameCode = isset($_GET['gameCode']) ? trim((string) $_GET['gameCode']) : '';
$lotteryCode = isset($_GET['lotteryCode']) ? trim((string) $_GET['lotteryCode']) : '';

if ($gameCode === '') {
	http_response_code(400);
	echo json_encode(['error' => 'gameCode required']);
	exit;
}

if (!isset($conn) || !$conn) {
	http_response_code(500);
	echo json_encode(['error' => 'database unavailable']);
	exit;
}

$config = getWingoDrawIssueConfigByGameCode($gameCode);
if ($config === null) {
	http_response_code(404);
	echo json_encode(['error' => 'unsupported gameCode', 'gameCode' => $gameCode, 'lotteryCode' => $lotteryCode]);
	exit;
}

try {
	$payload = buildWingoDrawIssuePayload($conn, $config['table'], $config['intervalMinute']);
	// MotoRace betting UI checks issueData.gameCode before opening the bet dialog.
	$payload['gameCode'] = $gameCode;
	$payload['lotteryCode'] = $lotteryCode;
	echo json_encode($payload, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
	http_response_code(500);
	echo json_encode(['error' => 'failed to load issue']);
}
