<?php
include "../../conn.php";
include "../../functions2.php";

header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . $origin);
header('Vary: Origin');

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
	if (isset($shonupost['language'], $shonupost['random'], $shonupost['signature'], $shonupost['timestamp'])) {
		$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
		$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
		$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));

		$shonustr = '{"language":' . $language . ',"random":"' . $random . '"}';
		$shonusign = strtoupper(md5($shonustr));

		if ($shonusign == $signature) {
			$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
			$author = $bearer[1];
			$is_jwt_valid = is_jwt_valid($author);
			$data_auth = json_decode($is_jwt_valid, true);

			if ($data_auth['status'] === 'Success') {
				$sesquery = "SELECT akshinak FROM shonu_subjects WHERE akshinak = '$author'";
				$sesresult = $conn->query($sesquery);
				$sesnum = mysqli_num_rows($sesresult);

				if ($sesnum == 1) {
					$data['typelist'] = [

						[
							"payID" => 2,
							"payTypeID" => 0,
							"payName" => "Innate UPI-QR",
							"paySysName" => "Online Pay",
							"payNameUrl" => "https://jalwaimg.jalwa-jalwa.com/Jalwa/payNameIcon/payNameIcon_202503171654228pbj.png",
							"payNameUrl2" => "https://jalwaimg.jalwa-jalwa.com/Jalwa/payNameIcon/payNameIcon2_202503171654234g8b.png",
							"minPrice" => 0.0,
							"maxPrice" => 0.0,
							"scope" => null,
							"typeName" => "Innate UPI-QR",
							"typeNameCode" => 0,
							"maxRechargeRifts" => 0.0000,
							"sort" => 110
						],
						[
							"payID" => 32,
							"payTypeID" => 0,
							"payName" => "Expert UPI-QR",
							"paySysName" => "UPI-QR",
							"payNameUrl" => "https://joshgame.online/assets/png/payNameIcon_20250317165432r3g1.png",
							"payNameUrl2" => "https://joshgame.online/assets/png/payNameIcon_20250317165432r3g1.png",
							"minPrice" => 0.0,
							"maxPrice" => 0.0,
							"scope" => null,
							"typeName" => "Expert UPI-QR",
							"typeNameCode" => 0,
							"maxRechargeRifts" => 0.0000,
							"sort" => 120
						],
						[
							"payID" => 1,
							"payTypeID" => 0,
							"payName" => "PAYTM",
							"paySysName" => "QR Pay",
							"payNameUrl" => "https://jalwaimg.jalwa-jalwa.com/Jalwa/payNameIcon/payNameIcon_20250317165601oqbs.png",
							"payNameUrl2" => "https://jalwaimg.jalwa-jalwa.com/Jalwa/payNameIcon/payNameIcon2_20250317165601oj7h.png",
							"minPrice" => 0.0,
							"maxPrice" => 0.0,
							"scope" => null,
							"typeName" => "PAYTM",
							"typeNameCode" => 0,
							"maxRechargeRifts" => 0.0000,
							"sort" => 90
						],
						[
							"payID" => 11,
							"payTypeID" => 0,
							"payName" => "USDT",
							"paySysName" => "USDT",
							"payNameUrl" => "https://jalwaimg.jalwa-jalwa.com/Jalwa/payNameIcon/payNameIcon_20250317165636a3yk.png",
							"payNameUrl2" => "https://jalwaimg.jalwa-jalwa.com/Jalwa/payNameIcon/payNameIcon2_20250317165636r7f4.png",
							"minPrice" => 0.0,
							"maxPrice" => 0.0,
							"scope" => null,
							"typeName" => "USDT",
							"typeNameCode" => 9205,
							"maxRechargeRifts" => 0.0000,
							"sort" => 5
						]
					];

					$res['data'] = $data;
					$res['code'] = 0;
					$res['msg'] = 'Succeed';
					$res['msgCode'] = 0;
					http_response_code(200);
					echo json_encode($res);
				} else {
					$res['code'] = 4;
					$res['msg'] = 'No operation permission';
					$res['msgCode'] = 2;
					http_response_code(401);
					echo json_encode($res);
				}
			} else {
				$res['code'] = 4;
				$res['msg'] = 'No operation permission';
				$res['msgCode'] = 2;
				http_response_code(401);
				echo json_encode($res);
			}
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