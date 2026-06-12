<?php
/**
 * Proxy Balance Webhook Receiver
 * Receives balance updates from proxy server and syncs to game-file database.
 * Also inserts into huidu_transactions so Bet History (GetBetHistory) shows these proxy bets.
 *
 * Endpoint: POST /application/api/webapi/ProxyBalanceWebhook.php
 */

include "../../conn.php";
include "../../functions2.php";
require_once __DIR__ . '/../helpers/game_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-Webhook-Signature');

date_default_timezone_set("Asia/Kolkata");

$response = [
    'status' => 'error',
    'message' => 'Unknown error',
    'timestamp' => date('Y-m-d H:i:s')
];

try {
    // Only allow POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Method not allowed';
        http_response_code(405);
        echo json_encode($response);
        exit;
    }

    // Get request body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['message'] = 'Invalid JSON';
        error_log("Proxy Webhook Error: Invalid JSON - " . substr($input, 0, 500));
        http_response_code(400);
        echo json_encode($response);
        exit;
    }

    // Validate required fields
    $required = ['username', 'balance'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            $response['message'] = "Missing required field: {$field}";
            error_log("Proxy Webhook Error: Missing field - {$field}");
            http_response_code(400);
            echo json_encode($response);
            exit;
        }
    }

    $username = trim($data['username']);
    $newBalance = (float)$data['balance'];
    $betAmount = isset($data['bet_amount']) ? (float)$data['bet_amount'] : 0;
    $winAmount = isset($data['win_amount']) ? (float)$data['win_amount'] : 0;
    $serialNumber = $data['serial_number'] ?? '';
    $webhookSignature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';

    // Optional: Verify webhook signature if secret is configured
    // To enable: Set WEBHOOK_SECRET in your config and uncomment below
    /*
    $webhookSecret = 'YOUR_WEBHOOK_SECRET_HERE'; // Get from config or environment
    if (!empty($webhookSecret) && !empty($webhookSignature)) {
        $expectedSignature = hash_hmac('sha256', $input, $webhookSecret);
        if (!hash_equals($expectedSignature, $webhookSignature)) {
            $response['message'] = 'Invalid webhook signature';
            error_log("Proxy Webhook Error: Invalid signature");
            http_response_code(403);
            echo json_encode($response);
            exit;
        }
    } elseif (!empty($webhookSecret) && empty($webhookSignature)) {
        // Secret configured but no signature provided
        $response['message'] = 'Webhook signature required';
        error_log("Proxy Webhook Error: Signature required but not provided");
        http_response_code(403);
        echo json_encode($response);
        exit;
    }
    */

    // Validate username format (should match token format from game-file)
    if (empty($username)) {
        $response['message'] = 'Invalid username';
        http_response_code(400);
        echo json_encode($response);
        exit;
    }

    // Find user by token (username from proxy = token in game-file)
    // The username sent from proxy should be the user's token
    $userQuery = "SELECT id FROM shonu_subjects WHERE token = ?";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("s", $username);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userRow = $userResult->fetch_assoc();
    $userStmt->close();

    if (!$userRow) {
        // Try finding by mobile number if token doesn't match
        $userQuery2 = "SELECT id FROM shonu_subjects WHERE mobile = ?";
        $userStmt2 = $conn->prepare($userQuery2);
        $userStmt2->bind_param("s", $username);
        $userStmt2->execute();
        $userResult2 = $userStmt2->get_result();
        $userRow = $userResult2->fetch_assoc();
        $userStmt2->close();
    }

    if (!$userRow) {
        $response['message'] = 'User not found';
        error_log("Proxy Webhook Error: User not found - username: {$username}");
        http_response_code(404);
        echo json_encode($response);
        exit;
    }

    $userId = $userRow['id'];

    // Check if balance record exists
    $balanceQuery = "SELECT motta FROM shonu_kaichila WHERE balakedara = ?";
    $balanceStmt = $conn->prepare($balanceQuery);
    $balanceStmt->bind_param("i", $userId);
    $balanceStmt->execute();
    $balanceResult = $balanceStmt->get_result();
    $balanceRow = $balanceResult->fetch_assoc();
    $balanceStmt->close();

    $oldBalance = $balanceRow ? (float)$balanceRow['motta'] : 0;

    if (!$balanceRow) {
        // Create balance record if it doesn't exist
        $insertQuery = "INSERT INTO shonu_kaichila (balakedara, motta, bonus, dinankavannuracisi) VALUES (?, ?, 0, NOW())";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("id", $userId, $newBalance);
        $insertStmt->execute();
        $insertStmt->close();
        error_log("Proxy Webhook: Created balance record for user ID: {$userId}, Balance: {$newBalance}");
    } else {
        // Update balance
        $updateQuery = "UPDATE shonu_kaichila SET motta = ? WHERE balakedara = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("di", $newBalance, $userId);
        $updateStmt->execute();
        $updateStmt->close();
        error_log("Proxy Webhook: Updated balance for user ID: {$userId}, Old: {$oldBalance}, New: {$newBalance}, Bet: {$betAmount}, Win: {$winAmount}, Serial: {$serialNumber}");
    }

    // Record in huidu_transactions for Bet History (same as UpdateBalance.php)
    // game_uid/game_name: should be sent by proxy – if missing, stored as unknown/UNKNOWN
    if ($serialNumber !== '' && $serialNumber !== null && ($betAmount != 0 || $winAmount != 0)) {
        $gameUid = 'unknown';
        foreach (['game_uid', 'game_id', 'game_code', 'gameId', 'gameCode', 'provider_game_id', 'external_game_id'] as $key) {
            if (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null) {
                $gameUid = trim((string) $data[$key]);
                break;
            }
        }
        if ($gameUid === 'unknown' && isset($data['game_round']) && $data['game_round'] !== '') {
            $gr = trim((string) $data['game_round']);
            if (preg_match('/^([a-f0-9]{32})\b/i', $gr)) {
                $gameUid = $gr;
            }
        }
        if ($gameUid === 'unknown') {
            $keys = array_keys($data ?? []);
            error_log("Proxy Webhook WARNING: No game identifier in payload. Add game_uid or game_id to fix Bet History. Keys received: " . implode(', ', $keys));
        }
        $gameName = isset($data['game_name']) && $data['game_name'] !== '' ? trim((string) $data['game_name']) : getGameName($gameUid, $conn);
        $provider = 'proxy';
        $gameRound = $data['game_round'] ?? null;
        $currencyCode = $data['currency_code'] ?? 'BDT';

        $checkTable = $conn->query("SHOW TABLES LIKE 'huidu_transactions'");
        if ($checkTable && $checkTable->num_rows > 0) {
            $dup = $conn->prepare("SELECT 1 FROM huidu_transactions WHERE serial_number = ?");
            $dup->bind_param("s", $serialNumber);
            $dup->execute();
            $dupRes = $dup->get_result();
            $dup->close();
            if ($dupRes->num_rows === 0) {
                $ins = $conn->prepare("
                    INSERT INTO huidu_transactions
                    (serial_number, member_account, bet_amount, win_amount, balance_before, balance_after, currency_code, game_uid, game_round, game_name, provider, user_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $ins->bind_param("ssddddsssssi", $serialNumber, $username, $betAmount, $winAmount, $oldBalance, $newBalance, $currencyCode, $gameUid, $gameRound, $gameName, $provider, $userId);
                if ($ins->execute()) {
                    error_log("Proxy Webhook: inserted huidu_transactions serial={$serialNumber}, user_id={$userId}, game_name={$gameName}");
                }
                $ins->close();
            }
        }
    }

    // Success response
    $response = [
        'status' => 'success',
        'message' => 'Balance updated successfully',
        'user_id' => $userId,
        'balance' => $newBalance,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Proxy Webhook Exception: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
    $response['message'] = 'Internal server error';
    http_response_code(500);
    echo json_encode($response);
}

