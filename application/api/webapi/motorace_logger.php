<?php
/**
 * MotoRace API debug logger.
 * Writes structured lines to application/api/webapi/motorace_debug.log
 * Each line: [ISO ts] [endpoint] [level] msg | context-json
 *
 * Enable/disable via ?mr_debug=0 query param or set MR_DEBUG=false below.
 * Toggle verbose payloads with MR_LOG_PAYLOAD.
 */
if (!defined('MR_LOG_INITIALIZED')) {
    define('MR_LOG_INITIALIZED', true);
    define('MR_DEBUG', true);            // master switch
    define('MR_LOG_PAYLOAD', true);      // log request/response bodies
    define('MR_LOG_FILE', __DIR__ . '/motorace_debug.log');
    define('MR_LOG_MAX_BYTES', 2 * 1024 * 1024); // rotate at 2 MB

    // Capture fatals/parse errors that would otherwise produce a blank 500.
    register_shutdown_function(function () {
        $e = error_get_last();
        if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
            mr_log('FATAL', $e['message'], [
                'file' => $e['file'], 'line' => $e['line'], 'type' => $e['type'],
            ]);
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode([
                    'result' => false, 'code' => 500,
                    'msg' => 'MotoRace internal error',
                    'debug' => ['file' => basename($e['file']), 'line' => $e['line'], 'message' => $e['message']],
                ]);
            }
        }
    });
    set_error_handler(function ($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) return false;
        mr_log('PHP', $message, ['file' => $file, 'line' => $line, 'severity' => $severity]);
        return false;
    });
    set_exception_handler(function ($ex) {
        mr_log('EXCEPTION', $ex->getMessage(), [
            'file' => $ex->getFile(), 'line' => $ex->getLine(),
            'trace' => $ex->getTraceAsString(),
        ]);
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'result' => false, 'code' => 500,
                'msg' => 'MotoRace exception',
                'debug' => ['message' => $ex->getMessage(), 'line' => $ex->getLine()],
            ]);
        }
    });
}

function mr_log($level, $msg, $ctx = []) {
    if (!MR_DEBUG) return;
    if (isset($_GET['mr_debug']) && $_GET['mr_debug'] === '0') return;
    $endpoint = basename($_SERVER['SCRIPT_NAME'] ?? 'mr');
    $ts = date('Y-m-d H:i:s');
    $line = "[$ts] [$endpoint] [$level] " . preg_replace('/\s+/', ' ', (string) $msg);
    if (!empty($ctx)) {
        $line .= ' | ' . json_encode($ctx, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }
    $line .= "\n";
    // Rotate when too big
    if (is_file(MR_LOG_FILE) && filesize(MR_LOG_FILE) > MR_LOG_MAX_BYTES) {
        @rename(MR_LOG_FILE, MR_LOG_FILE . '.1');
    }
    @file_put_contents(MR_LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}

function mr_log_request() {
    $get = $_GET;
    $post = $_POST;
    $body = null;
    if (MR_LOG_PAYLOAD && in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH'], true)) {
        $raw = @file_get_contents('php://input');
        if ($raw !== false && strlen($raw) > 0) {
            $body = strlen($raw) > 4000 ? substr($raw, 0, 4000) . '…[truncated]' : $raw;
        }
    }
    mr_log('REQ', $_SERVER['REQUEST_METHOD'] ?? '?', [
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 120),
        'get' => $get, 'post' => $post, 'rawBody' => $body,
        'auth' => isset($_SERVER['HTTP_AUTHORIZATION']) ? '<present>' : '<missing>',
    ]);
}

function mr_log_response($payload, $level = 'RES') {
    if (!MR_LOG_PAYLOAD) {
        $ok = is_array($payload) ? ($payload['result'] ?? null) : null;
        mr_log($level, 'response', ['result' => $ok]);
        return;
    }
    $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
    if ($json !== false && strlen($json) > 2000) $json = substr($json, 0, 2000) . '…[truncated]';
    mr_log($level, 'response', ['body' => $json]);
}

function mr_check_db($conn, $context = '') {
    if (!$conn) {
        mr_log('DB', 'connection missing', ['context' => $context]);
        return false;
    }
    if (function_exists('mysqli_connect_errno') && mysqli_connect_errno()) {
        mr_log('DB', 'connect error', [
            'context' => $context,
            'errno' => mysqli_connect_errno(),
            'error' => mysqli_connect_error(),
        ]);
        return false;
    }
    return true;
}

function mr_query($conn, $sql, $context = '') {
    $rs = @mysqli_query($conn, $sql);
    if ($rs === false) {
        mr_log('SQL', 'query failed', [
            'context' => $context,
            'errno' => mysqli_errno($conn),
            'error' => mysqli_error($conn),
            'sql' => substr($sql, 0, 500),
        ]);
    }
    return $rs;
}