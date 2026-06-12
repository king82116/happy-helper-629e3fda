<?php
include "../../conn.php";

header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . $origin);
header('vary: Origin');

date_default_timezone_set("Asia/Kolkata");
$shnunc = date("Y-m-d H:i:s");
$res = [
	'code' => 11,
	'msg' => 'Method not allowed',
	'msgCode' => 12,
	'serviceNowTime' => $shnunc,
];
$shonubody = file_get_contents("php://input");
$shonupost = json_decode($shonubody, true);
if ($_SERVER['REQUEST_METHOD'] != 'GET') {
	if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
		$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
		$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
		$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
		$shonustr = '{"language":' . $language . ',"random":"' . $random . '"}';
		$shonusign = strtoupper(md5($shonustr));
		if ($shonusign == $signature) {
			// Creating the desired response structure
			$data = [
				'merchantCode' => 'AR0031',
				'memberId' => '2115258',
				'walletActivationPageUrl' => 'https://arbpay.me/#/wallet?token=eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJhY3RpdmF0ZVdhbGxldEFSMDAzMTIxMTUyNTgiLCJleHAiOjE3NDU2OTUwOTgsImlhdCI6MTc0NTY5MzI5OCwianRpIjoiNTI0YmE2NzAtYzA3Ny00NDNhLThmMjItMTJhNGNlNjdlZDdlIn0._EKC5nKywI4ewsJz_78S0krAlGVASH-F8-as8AdIR70&lang=0&mobileNumber=917804016256&returnUrl=https://joshgame.online/#/main',
				'timestamp' => null
			];

			// Setting the final response
			$res['data'] = $data;
			$res['code'] = 0;
			$res['msg'] = 'Succeed';
			$res['msgCode'] = 0;
			http_response_code(200);
			echo json_encode($res);
		} else {
			$res['code'] = 5;
			$res['msg'] = 'Wrong signature';
			$res['msgCode'] = 3;
			http_response_code(200);
			echo json_encode($res);
		}
	} else {
		$res['code'] = 7;
		$res['msg'] = 'Param is Invalid';
		$res['msgCode'] = 6;
		http_response_code(200);
		echo json_encode($res);
	}
} else {
	http_response_code(405);
	echo json_encode($res);
}
?>