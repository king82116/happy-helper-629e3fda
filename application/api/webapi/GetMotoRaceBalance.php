<?php
include __DIR__ . '/motorace_logger.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
mr_log_request();

$balance = 0;
// Try resolve from JWT if present
if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
	$parts = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
	$token = $parts[1] ?? '';
	if ($token) {
		$connFile = __DIR__ . '/../../conn.php';
		$fnFile = __DIR__ . '/../../functions2.php';
		if (file_exists($connFile) && file_exists($fnFile)) {
			include_once $connFile;
			include_once $fnFile;
			if (function_exists('is_jwt_valid')) {
				$auth = json_decode(is_jwt_valid($token), true);
				if (!empty($auth) && ($auth['status'] ?? '') === 'Success' && !empty($conn)) {
					$uid = (int)($auth['payload']['id'] ?? 0);
					if ($uid > 0) {
						$rs = $conn->query("SELECT motta FROM shonu_kaichila WHERE balakedara = $uid LIMIT 1");
						if ($rs && ($row = mysqli_fetch_assoc($rs))) {
							$balance = (float) $row['motta'];
						}
					}
				}
			}
		}
	}
}

$out = [
	'result' => true, 'code' => 0, 'msg' => 'Succeed',
	'data' => ['balance' => $balance],
	'serviceTime' => time() * 1000,
];
mr_log_response($out);
echo json_encode($out);
