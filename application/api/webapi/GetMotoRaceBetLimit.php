<?php
include __DIR__ . '/motorace_logger.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
mr_log_request();

$rates = [];
foreach (['FirstNum','SecondNum','ThirdNum'] as $pt) {
	for ($n = 1; $n <= 10; $n++) $rates[] = ['playType' => $pt, 'playBet' => $n, 'playRate' => 9.33];
}
foreach (['FirstOddEven','SecondOddEven','ThirdOddEven'] as $pt) {
	foreach (['Odd','Even'] as $b) $rates[] = ['playType' => $pt, 'playBet' => $b, 'playRate' => 2];
}
foreach (['FirstBigSmall','SecondBigSmall','ThirdBigSmall'] as $pt) {
	foreach (['Big','Small'] as $b) $rates[] = ['playType' => $pt, 'playBet' => $b, 'playRate' => 2];
}

$out = [
	'result' => true, 'code' => 0, 'msg' => 'Succeed',
	'data' => [
		'rates' => $rates,
		'betScopes' => [1, 10, 100, 1000],
		'betMultiples' => [1, 5, 10, 20, 50, 100],
		'minAmount' => 1,
		'maxAmount' => 100000,
	],
];
mr_log('OK', 'bet limit', ['rateCount' => count($rates)]);
mr_log_response($out);
echo json_encode($out);