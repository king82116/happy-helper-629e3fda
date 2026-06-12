<?php
/**
 * Update Balance API for Gamefile
 * 
 * This endpoint is called by godspay.server to update player balance
 * Gamefile only needs ONE API key - no HUIDU credentials exposed
 * 
 * Called by: godspay.site (after decrypting HUIDU callback)
 */

include "../../conn.php";
include "../../functions2.php";

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-API-KEY');

// Simple API key validation (optional - you can add this)
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? null;
// TODO: Validate API key if needed
// if ($apiKey !== 'your_gamefile_api_key') { ... }

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['code' => 400, 'msg' => 'Invalid JSON']);
        exit;
    }

    // Validate required fields
    $required = ['serial_number', 'member_account', 'bet_amount', 'win_amount'];
    foreach ($required as $field) {
        if (!isset($input[$field])) {
            http_response_code(400);
            echo json_encode(['code' => 400, 'msg' => "Missing field: {$field}"]);
            exit;
        }
    }

    $serialNumber = $input['serial_number'];
    $memberAccount = $input['member_account'];
    $betAmount = floatval($input['bet_amount']);
    $winAmount = floatval($input['win_amount']);
    $currencyCode = $input['currency_code'] ?? 'INR';
    $gameUid = $input['game_uid'] ?? null;
    $gameRound = $input['game_round'] ?? null;

    // Check for duplicate serial_number (idempotency)
    $checkTableQuery = "SHOW TABLES LIKE 'huidu_transactions'";
    $tableExists = $conn->query($checkTableQuery);

    if ($tableExists->num_rows == 0) {
        $createTableQuery = "CREATE TABLE IF NOT EXISTS huidu_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            serial_number VARCHAR(255) UNIQUE NOT NULL,
            member_account VARCHAR(255) NOT NULL,
            bet_amount DECIMAL(15,2) DEFAULT 0,
            win_amount DECIMAL(15,2) DEFAULT 0,
            balance_before DECIMAL(15,2) DEFAULT 0,
            balance_after DECIMAL(15,2) DEFAULT 0,
            currency_code VARCHAR(10),
            game_uid VARCHAR(255),
            game_round VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_serial_number (serial_number),
            INDEX idx_member_account (member_account)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->query($createTableQuery);
    }

    // Check if already processed
    $checkStmt = $conn->prepare("SELECT balance_after FROM huidu_transactions WHERE serial_number = ?");
    $checkStmt->bind_param("s", $serialNumber);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $existing = $checkResult->fetch_assoc();
        $newBalance = floatval($existing['balance_after']);
        $checkStmt->close();

        http_response_code(200);
        echo json_encode([
            'code' => 0,
            'msg' => 'Already processed',
            'balance' => $newBalance
        ]);
        exit;
    }
    $checkStmt->close();

    // Find user
    $userStmt = $conn->prepare("
        SELECT id, mobile, token 
        FROM shonu_subjects 
        WHERE mobile = ? OR token = ? OR akshinak = ?
        LIMIT 1
    ");
    $userStmt->bind_param("sss", $memberAccount, $memberAccount, $memberAccount);
    $userStmt->execute();
    $userResult = $userStmt->get_result();

    if ($userResult->num_rows == 0) {
        $userStmt->close();
        http_response_code(404);
        echo json_encode(['code' => 404, 'msg' => 'User not found: ' . $memberAccount]);
        exit;
    }

    $user = $userResult->fetch_assoc();
    $userId = $user['id'];
    $userStmt->close();

    // Get current balance
    $balanceStmt = $conn->prepare("SELECT motta FROM shonu_kaichila WHERE balakedara = ?");
    $balanceStmt->bind_param("i", $userId);
    $balanceStmt->execute();
    $balanceResult = $balanceStmt->get_result();

    if ($balanceResult->num_rows == 0) {
        $createBalanceStmt = $conn->prepare("INSERT INTO shonu_kaichila (balakedara, motta) VALUES (?, 0)");
        $createBalanceStmt->bind_param("i", $userId);
        $createBalanceStmt->execute();
        $createBalanceStmt->close();
        $currentBalance = 0;
    } else {
        $balanceRow = $balanceResult->fetch_assoc();
        $currentBalance = floatval($balanceRow['motta']);
    }
    $balanceStmt->close();

    // Calculate new balance
    $newBalance = round($currentBalance - $betAmount + $winAmount, 2);

    if ($newBalance < 0) {
        error_log("Warning: Negative balance for user {$memberAccount}: {$newBalance}");
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update balance
        $updateStmt = $conn->prepare("UPDATE shonu_kaichila SET motta = ? WHERE balakedara = ?");
        $updateStmt->bind_param("di", $newBalance, $userId);

        if (!$updateStmt->execute()) {
            throw new Exception("Failed to update balance: " . $updateStmt->error);
        }
        $updateStmt->close();

        // Store transaction
        $txStmt = $conn->prepare("
            INSERT INTO huidu_transactions 
            (serial_number, member_account, bet_amount, win_amount, balance_before, balance_after, currency_code, game_uid, game_round)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $txStmt->bind_param(
            "ssddddsss",
            $serialNumber,
            $memberAccount,
            $betAmount,
            $winAmount,
            $currentBalance,
            $newBalance,
            $currencyCode,
            $gameUid,
            $gameRound
        );

        if (!$txStmt->execute()) {
            throw new Exception("Failed to store transaction: " . $txStmt->error);
        }
        $txStmt->close();

        $conn->commit();

        // Return plain balance (godspay server will encrypt it)
        http_response_code(200);
        echo json_encode([
            'code' => 0,
            'msg' => 'Success',
            'balance' => $newBalance
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        error_log("UpdateBalance Error: " . $e->getMessage());

        http_response_code(500);
        echo json_encode([
            'code' => 500,
            'msg' => 'System error: ' . $e->getMessage()
        ]);
    }

} catch (Exception $e) {
    error_log("UpdateBalance Fatal Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'code' => 500,
        'msg' => 'Internal server error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>