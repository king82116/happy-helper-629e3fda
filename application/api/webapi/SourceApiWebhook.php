<?php
/**
 * SOURCEAPI optional ledger webhook (bet / win / cancel).
 * Register this URL in SOURCEAPI API Details, e.g.:
 * https://yoursite.com/application/api/webapi/SourceApiWebhook.php
 */
include "../../conn.php";

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

date_default_timezone_set("Asia/Kolkata");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit;
}

$memberAccount = trim((string) ($data['member_account'] ?? ''));
$eventType = strtolower((string) ($data['type'] ?? ''));

if ($memberAccount === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing member_account']);
    exit;
}

$userId = null;
if (preg_match('/^player_(\d+)$/i', $memberAccount, $m)) {
    $userId = (int) $m[1];
} else {
    $userStmt = $conn->prepare("SELECT id FROM shonu_subjects WHERE token = ? OR mobile = ? LIMIT 1");
    $userStmt->bind_param('ss', $memberAccount, $memberAccount);
    $userStmt->execute();
    $userRow = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();
    $userId = $userRow ? (int) $userRow['id'] : null;
}

if (!$userId) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

$balStmt = $conn->prepare("SELECT motta FROM shonu_kaichila WHERE balakedara = ? LIMIT 1");
$balStmt->bind_param('i', $userId);
$balStmt->execute();
$balRow = $balStmt->get_result()->fetch_assoc();
$balStmt->close();

if (isset($data['balance_after']) && is_numeric($data['balance_after'])) {
    $newBalance = (float) $data['balance_after'];
} else {
    $betAmount = (float) ($data['bet_amount'] ?? 0);
    $winAmount = (float) ($data['win_amount'] ?? 0);
    $current = (float) ($balRow['motta'] ?? 0);

    if ($eventType === 'bet') {
        $newBalance = $current - $betAmount;
    } elseif ($eventType === 'win') {
        $newBalance = $current + $winAmount;
    } elseif ($eventType === 'cancel') {
        $newBalance = $current + $betAmount;
    } else {
        $newBalance = $current;
    }
}

if ($balRow) {
    $upd = $conn->prepare("UPDATE shonu_kaichila SET motta = ? WHERE balakedara = ?");
    $upd->bind_param('di', $newBalance, $userId);
    $upd->execute();
    $upd->close();
} else {
    $ins = $conn->prepare("INSERT INTO shonu_kaichila (balakedara, motta, bonus, dinankavannuracisi) VALUES (?, ?, 0, NOW())");
    $ins->bind_param('id', $userId, $newBalance);
    $ins->execute();
    $ins->close();
}

error_log(sprintf(
    'SOURCEAPI webhook: user=%d type=%s tx=%s balance=%.2f',
    $userId,
    $eventType,
    (string) ($data['transaction_id'] ?? ''),
    $newBalance
));

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'user_id' => $userId,
    'balance' => $newBalance,
    'timestamp' => date('Y-m-d H:i:s'),
]);
