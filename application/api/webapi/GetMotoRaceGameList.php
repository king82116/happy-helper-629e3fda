<?php
include __DIR__ . '/motorace_logger.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
mr_log_request();

$games = [];
$intervals = [['1M',1,1],['3M',3,2],['5M',5,3],['10M',10,4]];
foreach ($intervals as $i => $row) {
	list($suffix,$min,$sort) = $row;
	$games[] = [
		'sort' => $sort,
		'gameCode' => 'MotoRace_'.$suffix,
		'gameName' => 'MotoRace '.$min.'Min',
		'lotteryCode' => 'MotoRace',
		'gameTypeName' => 'MotoRace',
		'minute' => $min,
		'icon' => '',
	];
}
$out = [
	'result' => true, 'code' => 0, 'msg' => 'Succeed',
	'data' => [[
		'sort' => 1,
		'gameTypeName' => 'MotoRace',
		'lotteryCode' => 'MotoRace',
		'gameList' => $games,
	]],
];
mr_log_response($out);
echo json_encode($out);
