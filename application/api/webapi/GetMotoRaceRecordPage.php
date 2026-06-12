<?php
/**
 * User bet history for MotoRace. Auto-settles any pending bets on load
 * (on-demand settlement trigger).
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

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = (stripos($authHeader, 'Bearer ') === 0) ? trim(substr($authHeader, 7)) : '';
$userId = 0;
if ($token) {
    $data_auth = json_decode(is_jwt_valid($token), true);
    if ($data_auth && ($data_auth['status'] ?? '') === 'Success') {
        $userId = (int) ($data_auth['payload']['id'] ?? 0);
    }
}
if ($userId <= 0) {
    echo json_encode(['result' => true, 'code' => 0, 'msg' => 'Succeed',
        'data' => ['list' => [], 'pageNo' => 1, 'pageSize' => 10, 'totalPage' => 0, 'totalCount' => 0]]);
    exit;
}

$gameCode = $_GET['gameCode'] ?? '';
$pageNo   = max(1, (int) ($_GET['pageNo'] ?? 1));
$pageSize = min(50, max(1, (int) ($_GET['pageSize'] ?? 10)));

// On-demand settle for this user/game first
$settled = mr_settle_user_pending($conn, $userId, $gameCode ?: null);
mr_log('SETTLE', 'on-demand', ['userId' => $userId, 'gameCode' => $gameCode, 'count' => $settled]);

$where = "user_id=$userId";
if ($gameCode) $where .= " AND game_code='" . mysqli_real_escape_string($conn, $gameCode) . "'";

$totalRow = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM moto_race_bets WHERE $where"));
$totalCount = (int) ($totalRow[0] ?? 0);
$totalPage = (int) ceil($totalCount / $pageSize);
$offset = ($pageNo - 1) * $pageSize;

$rs = mysqli_query($conn, "SELECT id, game_code, issue_number, play_type, play_bet, amount, bet_count, multiplier, total_amount, fee, bet_amount, rate, status, win_amount, result_ranking, created_at, settled_at FROM moto_race_bets WHERE $where ORDER BY id DESC LIMIT $pageSize OFFSET $offset");
$list = [];
if ($rs) {
    while ($r = mysqli_fetch_assoc($rs)) {
        $list[] = [
            'id'          => (int) $r['id'],
            'gameCode'    => $r['game_code'],
            'issueNumber' => $r['issue_number'],
            'playType'    => $r['play_type'],
            'playBet'     => $r['play_bet'],
            'amount'      => (float) $r['amount'],
            'betCount'    => (int) $r['bet_count'],
            'multiplier'  => (int) $r['multiplier'],
            'totalAmount' => (float) $r['total_amount'],
            'fee'         => (float) $r['fee'],
            'betAmount'   => (float) $r['bet_amount'],
            'rate'        => (float) $r['rate'],
            'status'      => $r['status'],
            'winAmount'   => (float) $r['win_amount'],
            'result'      => $r['result_ranking'],
            'createTime'  => $r['created_at'],
            'settleTime'  => $r['settled_at'],
        ];
    }
    mysqli_free_result($rs);
}

$out = ['result' => true, 'code' => 0, 'msg' => 'Succeed',
    'data' => ['list' => $list, 'pageNo' => $pageNo, 'pageSize' => $pageSize, 'totalPage' => $totalPage, 'totalCount' => $totalCount]];
mr_log('OK', 'records served', ['rows' => count($list), 'totalCount' => $totalCount, 'settled' => $settled]);
mr_log_response($out);
echo json_encode($out);