<?php
/**
 * MotoRace bet placement — Phase 2.
 * Auth: Bearer JWT (same as Wingo). Balance deduct from shonu_kaichila.
 * Settlement is on-demand (see GetMotoRaceRecordPage.php / GetMotoRaceWinLossResult.php).
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

function mr_respond($r) { mr_log_response($r); echo json_encode($r); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    mr_respond(['result' => false, 'code' => 11, 'msg' => 'Method not allowed']);
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true) ?: [];

// Auth
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = '';
if (stripos($authHeader, 'Bearer ') === 0) $token = trim(substr($authHeader, 7));
if (!$token) { mr_log('AUTH', 'no token'); http_response_code(401); mr_respond(['result' => false, 'code' => 4, 'msgCode' => 2, 'msg' => 'No operation permission']); }

$jwt = is_jwt_valid($token);
$data_auth = json_decode($jwt, true);
if (!$data_auth || ($data_auth['status'] ?? '') !== 'Success') {
    mr_log('AUTH', 'jwt invalid', ['status' => $data_auth['status'] ?? null]);
    http_response_code(401);
    mr_respond(['result' => false, 'code' => 4, 'msgCode' => 2, 'msg' => 'No operation permission']);
}
$userId = (int) ($data_auth['payload']['id'] ?? 0);
$tokEsc = mysqli_real_escape_string($conn, $token);
$ses = mysqli_query($conn, "SELECT akshinak FROM shonu_subjects WHERE akshinak='$tokEsc'");
if (!$ses || mysqli_num_rows($ses) !== 1) {
    mr_log('AUTH', 'session missing', ['userId' => $userId]);
    http_response_code(401);
    mr_respond(['result' => false, 'code' => 4, 'msgCode' => 2, 'msg' => 'No operation permission']);
}

// Required fields
$gameCode    = (string) ($body['gameCode'] ?? '');
$issueNumber = (string) ($body['issueNumber'] ?? $body['issuenumber'] ?? '');
$playType    = (string) ($body['playType'] ?? $body['gameType'] ?? '');
$playBet     = isset($body['playBet']) ? (string) $body['playBet'] : (isset($body['selectType']) ? (string) $body['selectType'] : '');
$amount      = (float)  ($body['amount'] ?? 0);
$betCount    = max(1, (int) ($body['betCount'] ?? 1));
$multiplier  = max(1, (int) ($body['multiplier'] ?? $body['betMultiple'] ?? 1));

// SaaS MotoRace client sends betContent e.g. "FirstNum_5" or "FirstOddEven_Odd"
if ((!$playType || $playBet === '') && !empty($body['betContent'])) {
    $content = (string) $body['betContent'];
    if (strpos($content, '_') !== false) {
        [$playType, $playBet] = explode('_', $content, 2);
    }
}
if ((!$playType || $playBet === '') && !empty($body['betContent']) && is_array($body['betContent'])) {
    $first = (string) ($body['betContent'][0] ?? '');
    if (strpos($first, '_') !== false) {
        [$playType, $playBet] = explode('_', $first, 2);
    }
}

if (!$gameCode || !$issueNumber || !$playType || $playBet === '' || $amount <= 0) {
    mr_log('ERR', 'missing params', compact('gameCode','issueNumber','playType','playBet','amount'));
    mr_respond(['result' => false, 'code' => 9, 'msg' => 'Missing parameters']);
}

// Resolve game
[$gameKey, $gameMinutes] = mr_game_minutes($gameCode);
if (!$gameMinutes) {
    mr_log('ERR', 'unknown gameCode', ['gameCode' => $gameCode]);
    mr_respond(['result' => false, 'code' => 9, 'msg' => 'Invalid gameCode']);
}

// Reject if period already drawn
if (mr_fetch_result($conn, $gameCode, $issueNumber) !== null) {
    mr_log('ERR', 'period already drawn', ['issue' => $issueNumber]);
    mr_respond(['result' => false, 'code' => 1, 'msgCode' => 404, 'msg' => 'The current period is settled']);
}

$rate = mr_rate_for($playType);
$totalAmount = round($amount * $betCount * $multiplier, 2);
$fee = round($totalAmount * 0.02, 2);
$betAmount = round($totalAmount - $fee, 2);

// Balance check
$bal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT motta FROM shonu_kaichila WHERE balakedara=$userId"));
$balance = (float) ($bal['motta'] ?? 0);
if ($balance < $totalAmount) {
    mr_log('ERR', 'insufficient balance', ['userId' => $userId, 'balance' => $balance, 'need' => $totalAmount]);
    mr_respond(['result' => false, 'code' => 1, 'msgCode' => 142, 'msg' => 'Balance is not enough']);
}

// Insert bet + deduct
$now = date('Y-m-d H:i:s');
$gEsc = mysqli_real_escape_string($conn, $gameCode);
$iEsc = mysqli_real_escape_string($conn, $issueNumber);
$ptEsc = mysqli_real_escape_string($conn, $playType);
$pbEsc = mysqli_real_escape_string($conn, $playBet);

mysqli_query($conn, "INSERT INTO moto_race_bets (user_id, game_code, issue_number, play_type, play_bet, amount, bet_count, multiplier, total_amount, fee, bet_amount, rate, status, created_at) VALUES ($userId, '$gEsc', '$iEsc', '$ptEsc', '$pbEsc', $amount, $betCount, $multiplier, $totalAmount, $fee, $betAmount, $rate, 'pending', '$now')");
if (mysqli_errno($conn)) {
    mr_log('SQL', 'bet insert failed', ['err' => mysqli_error($conn)]);
    mr_respond(['result' => false, 'code' => 500, 'msg' => 'Bet save failed']);
}
$betId = mysqli_insert_id($conn);

$newBal = round($balance - $totalAmount, 2);
mysqli_query($conn, "UPDATE shonu_kaichila SET motta=$newBal WHERE balakedara=$userId");

mr_log('OK', 'bet placed', ['betId' => $betId, 'userId' => $userId, 'gameCode' => $gameCode, 'issue' => $issueNumber, 'playType' => $playType, 'playBet' => $playBet, 'total' => $totalAmount, 'newBal' => $newBal]);
mr_respond(['result' => true, 'code' => 0, 'msgCode' => 0, 'msg' => 'Succeed', 'data' => ['betId' => $betId, 'balance' => $newBal]]);