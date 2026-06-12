<?php
/**
 * Per-period win/loss check for the popup that appears after a draw.
 * Settles this user's pending bets for the given period, then returns
 * aggregate status/winAmount across all of their bets in that period.
 */
include '../../conn.php';
include '../../functions2.php';
include __DIR__ . '/motorace_logger.php';
include __DIR__ . '/motorace_common.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Vary: Origin');
date_default_timezone_set('Asia/Kolkata');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
mr_log_request();

$emptyOk = ['result' => true, 'code' => 0, 'msg' => 'Succeed', 'data' => ['status' => null, 'winAmount' => 0]];

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = (stripos($authHeader, 'Bearer ') === 0) ? trim(substr($authHeader, 7)) : '';
if (!$token) { mr_log('AUTH', 'no token (winloss)'); echo json_encode($emptyOk); exit; }
$data_auth = json_decode(is_jwt_valid($token), true);
if (!$data_auth || ($data_auth['status'] ?? '') !== 'Success') { echo json_encode($emptyOk); exit; }
$userId = (int) ($data_auth['payload']['id'] ?? 0);

$issueNumber = $_GET['issueNumber'] ?? '';
$gameCode    = $_GET['gameCode'] ?? null;
if (!$issueNumber) { echo json_encode($emptyOk); exit; }

$settled = mr_settle_user_pending($conn, $userId, $gameCode, $issueNumber);

$where = "user_id=$userId AND issue_number='" . mysqli_real_escape_string($conn, $issueNumber) . "'";
if ($gameCode) $where .= " AND game_code='" . mysqli_real_escape_string($conn, $gameCode) . "'";

$rs = mysqli_query($conn, "SELECT status, SUM(win_amount) AS win, SUM(total_amount) AS bet FROM moto_race_bets WHERE $where GROUP BY status");
$totalWin = 0.0; $totalBet = 0.0; $hasWin = false; $hasLose = false; $hasPending = false;
if ($rs) {
    while ($r = mysqli_fetch_assoc($rs)) {
        $totalWin += (float) $r['win'];
        $totalBet += (float) $r['bet'];
        if ($r['status'] === 'win') $hasWin = true;
        if ($r['status'] === 'lose') $hasLose = true;
        if ($r['status'] === 'pending') $hasPending = true;
    }
    mysqli_free_result($rs);
}

$status = null;
if ($hasPending) {
    $status = null;
} elseif ($hasWin) {
    $status = true;  // client checks status === true for win popup
} elseif ($hasLose) {
    $status = false;
}

$out = ['result' => true, 'code' => 0, 'msg' => 'Succeed',
    'data' => ['status' => $status, 'winAmount' => round($totalWin, 2), 'betAmount' => round($totalBet, 2)]];
mr_log('OK', 'winloss', ['userId' => $userId, 'issue' => $issueNumber, 'settled' => $settled, 'status' => $status, 'win' => $totalWin]);
mr_log_response($out);
echo json_encode($out);