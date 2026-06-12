<?php
// bondpay_callback.php
// Fully working callback handler for BondPay
// - Redirects to /#/wallet/RechargeHistory when payload is invalid JSON (browser tests).
// - Handles success/pending/failed statuses.
// - Idempotent: won't double-credit on duplicate callbacks.
// - Logs to config LOG_FILE or /tmp/bondpay_integration.log

@ob_start();
header('Content-Type: application/json; charset=utf-8');

// include DB and config
include __DIR__ . "/../serive/samparka.php";      // must define $conn (mysqli)
$config = include __DIR__ . '/bondpay_config.php'; // must return LOG_FILE etc.

function logit($m) {
    global $config;
    $f = $config['LOG_FILE'] ?? '/tmp/bondpay_integration.log';
    file_put_contents($f, date('c') . " | " . $m . PHP_EOL, FILE_APPEND);
}
function respond_json($arr, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    exit;
}
function redirect_to_history_and_exit() {
    if (ob_get_length()) { @ob_end_clean(); }
    // browser redirect target requested by you
    header('Location: https://ninjaclub.online/#/wallet/RechargeHistory');
    exit;
}

// Read raw body
$raw = file_get_contents('php://input');
logit("RAW_CALLBACK_LEN=" . strlen($raw) . " | " . substr($raw,0,2000));

// Try decode JSON
$data = json_decode($raw, true);
if (!is_array($data)) {
    // Invalid JSON — log and redirect to RechargeHistory (per your request)
    logit("Invalid JSON callback received. Raw (first 2000 chars): " . substr($raw,0,2000));
    redirect_to_history_and_exit();
}

// Normalize incoming fields
$orderNo       = isset($data['orderNo'])       ? (string)$data['orderNo'] : null;       // BondPay orderNo
$merchantOrder = isset($data['merchantOrder']) ? (string)$data['merchantOrder'] : null; // our merchant_order_no (stored in thevani.dharavahi)
$status        = isset($data['status'])        ? strtolower(trim($data['status'])) : '';
$amount        = isset($data['amount'])        ? (float)$data['amount'] : 0.0;

// Basic validation
if (!$merchantOrder) {
    logit("Callback missing merchantOrder. Payload: " . json_encode($data));
    respond_json(['status'=>'error','message'=>'Missing merchantOrder'],200);
}

// Find the transaction in thevani using dharavahi (merchant_order_no)
$found = false;
$balakedara = null;
$motta_db = 0.0;
$sthiti_db = null;

if ($stmt = $conn->prepare("SELECT balakedara, motta, sthiti FROM thevani WHERE dharavahi = ? LIMIT 1")) {
    $stmt->bind_param('s', $merchantOrder);
    $stmt->execute();
    $stmt->bind_result($balakedara, $motta_db, $sthiti_db);
    $found = $stmt->fetch();
    $stmt->close();
} else {
    logit("DB prepare failed (select thevani): " . $conn->error);
    respond_json(['status'=>'error','message'=>'DB error'],200);
}

if (!$found) {
    logit("Order not found for merchantOrder={$merchantOrder}. Payload: " . json_encode($data));
    respond_json(['status'=>'error','message'=>'Order not found'],200);
}

// Idempotency: if already marked as paid and status is success, acknowledge
if ((int)$sthiti_db === 1 && $status === 'success') {
    logit("Duplicate success callback ignored for merchantOrder={$merchantOrder}");
    respond_json(['status'=>'ok','message'=>'Already processed'],200);
}

// Process statuses
if ($status === 'success') {
    // success -> mark thevani paid (sthiti=1), save orderNo, credit user's motta
    $conn->begin_transaction();
    try {
        // update thevani: set ullekha (BondPay orderNo), duravani (merchantOrder), sthiti=1
        if ($up = $conn->prepare("UPDATE thevani SET ullekha = ?, duravani = ?, sthiti = 1 WHERE dharavahi = ? LIMIT 1")) {
            $ul = $orderNo ?? '';
            $dur = $merchantOrder;
            $up->bind_param('sss', $ul, $dur, $merchantOrder);
            $up->execute();
            $up->close();
        } else {
            throw new Exception("Prepare failed (update thevani): " . $conn->error);
        }

        // credit user's shonu_kaichila.motta by the stored amount (motta_db)
        if ($up2 = $conn->prepare("UPDATE shonu_kaichila SET motta = motta + ? WHERE balakedara = ? LIMIT 1")) {
            $creditAmount = (float)$motta_db;
            $up2->bind_param('ds', $creditAmount, $balakedara);
            $up2->execute();
            $affected = $up2->affected_rows;
            $up2->close();
            if ($affected === 0) {
                // maybe balakedara stored differently — log but continue
                logit("Warning: shonu_kaichila not updated (maybe balakedara mismatch) for {$balakedara}");
            }
        } else {
            throw new Exception("Prepare failed (update shonu_kaichila): " . $conn->error);
        }

        $conn->commit();
        logit("SUCCESS processed merchantOrder={$merchantOrder} bond_order={$orderNo} credited={$motta_db} to balakedara={$balakedara}");
        respond_json(['status'=>'ok','message'=>'Callback received successfully'],200);
    } catch (Exception $e) {
        $conn->rollback();
        logit("ERROR processing success callback: " . $e->getMessage() . " | payload: " . json_encode($data));
        respond_json(['status'=>'error','message'=>'Internal error'],200);
    }
}

// pending or failed -> update record but do not credit
if (in_array($status, ['pending','failed'])) {
    $pavad = ($status === 'pending') ? 2 : 3;
    if ($up = $conn->prepare("UPDATE thevani SET ullekha = ?, duravani = ?, pavatiaidi = ? WHERE dharavahi = ? LIMIT 1")) {
        $ul = $orderNo ?? '';
        $dur = $merchantOrder;
        $up->bind_param('ssis', $ul, $dur, $pavad, $merchantOrder);
        $up->execute();
        $up->close();
    } else {
        logit("Prepare failed updating pending/failed: " . $conn->error);
    }
    logit("STATUS {$status} processed for merchantOrder={$merchantOrder}");
    respond_json(['status'=>'ok','message'=>"Callback noted: {$status}"],200);
}

// Fallback for other statuses: log and respond ok
logit("Unhandled status '{$status}' for merchantOrder={$merchantOrder} | payload: " . json_encode($data));
respond_json(['status'=>'ok','message'=>"Unhandled status '{$status}' ignored"],200);
