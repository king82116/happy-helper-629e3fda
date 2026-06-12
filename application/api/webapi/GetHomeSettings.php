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
			$data['isShowAppDownloadUp'] = true;
			$data['isShowAppDownloadDown'] = true;
			$data['isShowLotteryDragon'] = true;
			$data['isSplitLocalEWallet'] = true;
			$data['jackportMaxReswadAmount'] = 500;
			$data['projectName'] = 'Josh Game';
			$data['projectLogo'] = 'https://joshgame.online/logoo.png';
			$data['languages'] = 'en|hd';
			$data['webIco'] = 'https://joshgame.online/images/mainjosh.png';
			$data['headLogo'] = 'https://joshgame.online/logoo.png';
			$data['dollarSign'] = '₹';
			$data['upperOrLower'] = '0';
			$data['defaultCurrentLanguage'] = 'en';
			$data['registerMobile'] = '1';
			$data['registerEmail'] = '1';
			$data['areaPhoneLenList'][0]['area'] = '+91';
			$data['areaPhoneLenList'][0]['len'] = '9-12';

			$data['registerSms'] = '1';
			$data['isOpenLoginChangeLanguage'] = '1';
			$data['rewardValidityTime'] = 30;
			$data['electronicWinRateExternalLink'] = 'https://live.88bitluck.com/';
			$data['electronicWinRateImgUrl'] = 'https://ossimg.ffpay.in/55five/vendorlogo/vendorlogo_20231118161120og6e.png';
			$data['isShowElectronicWinRateExternalLink'] = true;
			$data['isShowAppHandCodeWashingSwitch'] = true;
			$data['isShowHotGameWinOdds'] = true;
			$data['ossUrl'] = 'https://ossimg.yuk87k786d.com';
			$data['bigTurntableLink'] = null;
			$data['isSelfCustomerService'] = true;
			$data['telegramExternalLink'] = null;
			$data['isOpenActivityAward'] = true;
			$data['isOpenTurntable'] = true;
			$data['isPartnerReward'] = true;
			$data['isOpenFacebookEvent'] = true;
			$data['isOpenRegisterPhoneFirstZeroSwitch'] = true;

			$data['firstDepositRewardCodeAmount'] = "1";
			$data['isOpenAdjustEvent'] = true;

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