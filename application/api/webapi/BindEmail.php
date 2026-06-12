<?php 
include "../../conn.php";
include "../../functions2.php";

header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000');

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, ar-origin');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Credentials: true');

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
	if (
		isset($shonupost['language']) && 
		isset($shonupost['random']) && 
		isset($shonupost['signature']) && 
		isset($shonupost['timestamp']) && 
		isset($shonupost['email']) && 
		isset($shonupost['emailvCode'])
	) {
		$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
		$email = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['email']));
		$emailvCode = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['emailvCode']));
		$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
		$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));

		$shonustr = '{"email":"'.$email.'", "language":"'.$language.'", "random":"'.$random.'"}';
		$shonusign = strtoupper(md5($shonustr));

		if ($shonusign == $signature) {
			if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1] ?? '';

				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, true);

				if ($data_auth['status'] === 'Success') {
					$sesquery = "SELECT akshinak FROM shonu_subjects WHERE akshinak = '$author'";
					$sesresult = $conn->query($sesquery);

					if ($sesresult && mysqli_num_rows($sesresult) === 1) {
						$dbotp = "SELECT emailotp FROM shonu_subjects WHERE akshinak = '$author'";
						$dbotpresult = $conn->query($dbotp);
						$otpData = mysqli_fetch_assoc($dbotpresult);

						if ($otpData && $otpData['emailotp'] == $emailvCode) {
							$updateemail = "UPDATE shonu_subjects SET email = '$email', emailverify = '1' WHERE akshinak = '$author'";
							$updateemailresult = $conn->query($updateemail);

							if ($updateemailresult) {
								$res['code'] = 0;
								$res['msg'] = 'Succeed';
								$res['msgCode'] = 0;
							} else {
								$res['code'] = 3;
								$res['msg'] = 'Failed to update email';
								$res['msgCode'] = 105;
							}
						} else {
							$res['code'] = 2;
							$res['msg'] = 'Verification code error';
							$res['msgCode'] = 107;
						}
					} else {
						$res['code'] = 4;
						$res['msg'] = 'No operation permission';
						$res['msgCode'] = 2;
					}
				} else {
					$res['code'] = 4;
					$res['msg'] = 'No operation permission';
					$res['msgCode'] = 2;
				}
			} else {
				$res['code'] = 6;
				$res['msg'] = 'Authorization header missing';
				$res['msgCode'] = 110;
			}
		} else {
			$res['code'] = 5;
			$res['msg'] = 'Wrong signature';
			$res['msgCode'] = 3;
		}
	} else {
		$res['code'] = 7;
		$res['msg'] = 'Param is Invalid';
		$res['msgCode'] = 6;
	}
	http_response_code(200);
	echo json_encode($res);
} else {
	http_response_code(405);
	echo json_encode($res);
}
?>
