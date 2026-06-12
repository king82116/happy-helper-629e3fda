<?php 
	include "../../conn.php";
	include "../../functions2.php";
	
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
		if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp']) && isset($shonupost['withdrawid'])) {
			$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
			$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
			$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
			$withdrawid = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['withdrawid']));
			$shonustr = '{"language":'.$language.',"random":"'.$random.'","withdrawid":'.$withdrawid.'}';
			$shonusign = strtoupper(md5($shonustr));
			if($shonusign == $signature){
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, 1);
				if($data_auth['status'] === 'Success') {
					$sesquery = "SELECT akshinak
					  FROM shonu_subjects
					  WHERE akshinak = '$author'";
					$sesresult=$conn->query($sesquery);
					$sesnum = mysqli_num_rows($sesresult);
					if($sesnum == 1){
						http_response_code(200);
						if($withdrawid == 1){
							echo '
								{
								  "data": {
									"banklist": [
									  {
										"bankID": 16,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Bank of Baroda",
										"reserved": "1"
									  },
									  {
										"bankID": 15,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Union Bank of India",
										"reserved": "1"
									  },
									  {
										"bankID": 14,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Central Bank of India",
										"reserved": "1"
									  },
									  {
										"bankID": 13,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Yes Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 12,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "HDFC Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 11,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Karnataka Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 10,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Standard Chartered Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 9,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "IDBI Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 8,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Bank of India",
										"reserved": "1"
									  },
									  {
										"bankID": 7,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Punjab National Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 6,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "ICICI Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 5,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Canara Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 4,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Kotak Mahindra Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 3,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "State Bank of India",
										"reserved": "1"
									  },
									  {
										"bankID": 2,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Indian Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 1,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Axis Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 17,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "FEDERAL BANK",
										"reserved": "1"
									  },
									  {
										"bankID": 18,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Syndicate Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 22,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Citibank India",
										"reserved": "1"
									  },
									  {
										"bankID": 23,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Indian Overseas Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 24,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "IDFC Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 25,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Bandhan Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 26,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Indusind Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 29,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Equitas Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 30,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "India Post Payments Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 31,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Corporation Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 27,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Jammu & Kashmir Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 32,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "City Union Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 28,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "PYTM PAYMENTS BANK",
										"reserved": "1"
									  },
									  {
										"bankID": 33,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Karur Vysya Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 34,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Tamilnad Mercantile Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 35,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Allahabad Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 36,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "varachha co-operative bank",
										"reserved": "1"
									  },
									  {
										"bankID": 37,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Meghalaya Rural Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 38,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "AU Small Finance Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 39,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Lakshmi Vilas Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 40,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "South Indian Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 41,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Bassein catholic co-operative Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 42,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Airtel Payment Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 43,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "State Bank of Hyderabad",
										"reserved": "1"
									  },
									  {
										"bankID": 44,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Gp parsik bank",
										"reserved": "1"
									  },
									  {
										"bankID": 45,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Kerala Gramin Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 46,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "RBL Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 47,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Dhanlaxmi Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 48,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "TJSB Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 49,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Punjab & Sind Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 50,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Purvanchal bank",
										"reserved": "1"
									  },
									  {
										"bankID": 51,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Sarva Haryana Gramin Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 52,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Ahmedabad District Co-Operative Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 53,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Fino Payments Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 54,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Saraswat Cooperative Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 62,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Dhanlaxmi bank",
										"reserved": "1"
									  },
									  {
										"bankID": 63,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Telangana Grameena Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 57,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "andhra pragathi grameena bank",
										"reserved": "1"
									  },
									  {
										"bankID": 58,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "rajasthan marudhara gramin bank",
										"reserved": "1"
									  },
									  {
										"bankID": 59,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Abhyudaya bank",
										"reserved": "1"
									  },
									  {
										"bankID": 60,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "ujjivan small finance bank",
										"reserved": "1"
									  },
									  {
										"bankID": 61,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Pragathi Krishna Gramin Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 64,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "capital small finance bank",
										"reserved": "1"
									  },
									  {
										"bankID": 65,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Mizoram Rural Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 66,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Andhra Pradesh Grameena Vikas Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 67,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Karnataka Vikas Grameena Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 68,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "The Ahmedabad merchantile co-op bank Ltd",
										"reserved": "1"
									  },
									  {
										"bankID": 69,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Madhya Bihar Gramin Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 70,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "NSDL Payments Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 71,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "ESAF Small Finance Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 72,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Himachal Pradesh state cooperative bank",
										"reserved": "1"
									  },
									  {
										"bankID": 73,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Maharashtra state cooperative bank",
										"reserved": "1"
									  },
									  {
										"bankID": 74,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "ORIENTAL BANK OF COMMERCE",
										"reserved": "1"
									  },
									  {
										"bankID": 75,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "nainital bank",
										"reserved": "1"
									  },
									  {
										"bankID": 76,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Telangana grameena bank",
										"reserved": "1"
									  },
									  {
										"bankID": 77,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Jharkhand Rajya Gramin Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 78,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "jio payments bank",
										"reserved": "1"
									  },
									  {
										"bankID": 79,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "MAHARASHTRA GRAMIN BANK",
										"reserved": "1"
									  },
									  {
										"bankID": 80,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "AIRTEL PAYMENTS BANK",
										"reserved": "1"
									  },
									  {
										"bankID": 81,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Uttarakhand Gramin Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 82,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "DBS BANK",
										"reserved": "1"
									  },
									  {
										"bankID": 83,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Equitas Small Finance Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 84,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Himachal Pradesh Gramin Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 85,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Krishna District Co-Operative Central Bank Ltd.",
										"reserved": "1"
									  },
									  {
										"bankID": 86,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "RAJKOT NAGARIK SAHAKARI BANK LTD",
										"reserved": "1"
									  },
									  {
										"bankID": 87,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "North East small financial bank",
										"reserved": "1"
									  },
									  {
										"bankID": 88,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Catholic syrian bank",
										"reserved": "1"
									  },
									  {
										"bankID": 89,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Fincare small finance bank",
										"reserved": "1"
									  },
									  {
										"bankID": 90,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Baroda Uttar Pradesh Gramin Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 91,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Dhanalakshmi bank",
										"reserved": "1"
									  },
									  {
										"bankID": 92,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Cosmos Co-operative Bank Ltd",
										"reserved": "1"
									  },
									  {
										"bankID": 93,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Saurashtra gramin bank",
										"reserved": "1"
									  },
									  {
										"bankID": 94,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Baroda Rajasthan kshetriya gramin bank",
										"reserved": "1"
									  },
									  {
										"bankID": 95,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Suco Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 96,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Jana small finance bank",
										"reserved": "1"
									  },
									  {
										"bankID": 97,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "",
										"reserved": "1"
									  },
									  {
										"bankID": 98,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Dena Gujarat Gramin Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 99,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Chaitanya Godavari Grameena Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 100,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "SVC BANK",
										"reserved": "1"
									  },
									  {
										"bankID": 101,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Bharat cooperative bank",
										"reserved": "1"
									  },
									  {
										"bankID": 102,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "The Surat District Co-Op. Bank Ltd.",
										"reserved": "1"
									  },
									  {
										"bankID": 103,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "USDT",
										"reserved": "1"
									  },
									  {
										"bankID": 104,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "The Kalupur Commercial Co-operative Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 105,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "India Post Payments Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 106,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Prime co-operative Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 107,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Tripura Gramin Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 108,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Zila Sahakari Bank Ltd Bareilly",
										"reserved": "1"
									  },
									  {
										"bankID": 109,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "ARYAVART Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 110,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Development credit Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 111,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Ujjivan Small Finance Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 112,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Sarva UP Gramin Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 113,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "New India Co-Operative Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 114,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "NKGSB Co-operative Bank Ltd.",
										"reserved": "1"
									  },
									  {
										"bankID": 115,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Vijaya Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 116,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "United Bank of India",
										"reserved": "1"
									  },
									  {
										"bankID": 117,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "State Bank of Bikaner And Jaipur",
										"reserved": "1"
									  },
									  {
										"bankID": 118,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Shri Janata Sahakari Bank LTD",
										"reserved": "1"
									  },
									  {
										"bankID": 119,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Rajgurunagar Sahakari Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 120,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "FEDERAL NEO BANK JUPITER",
										"reserved": "1"
									  },
									  {
										"bankID": 121,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "CHHATTISGARH RAJYA GRAMIN BANK",
										"reserved": "1"
									  },
									  {
										"bankID": 122,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Apna Sahakari Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 123,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "GS Mahanagar Co-Op Bank Ltd",
										"reserved": "1"
									  },
									  {
										"bankID": 124,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Bangiya Gramin Vikash Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 125,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Assam Gramin Vikash Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 126,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Saurashtra Gramin Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 127,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Kangra Central Co-operative Bank Ltd",
										"reserved": "1"
									  },
									  {
										"bankID": 128,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Punjab Gramin Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 129,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Assam gramin bikash bank",
										"reserved": "1"
									  },
									  {
										"bankID": 130,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Karnataka Gramin Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 131,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "SURYODAY SMALL FINANCE BANK LIMITED",
										"reserved": "1"
									  },
									  {
										"bankID": 132,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Utkarsh Small Finance Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 133,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "The Meghalaya Co-operative Apex Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 134,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "UTTAR BIHAR GRAMIN BANK",
										"reserved": "1"
									  },
									  {
										"bankID": 135,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "STATE BANK OF TRAVANCORE",
										"reserved": "1"
									  },
									  {
										"bankID": 136,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "SHIVALIK SMALL FIHANCE BANK",
										"reserved": "1"
									  },
									  {
										"bankID": 137,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "DAKSHIN BIHIR GRAMIN BANK",
										"reserved": "1"
									  },
									  {
										"bankID": 138,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "DBS Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 139,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "State Bank of Hyderabad",
										"reserved": "1"
									  },
									  {
										"bankID": 140,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "manipur rural bank",
										"reserved": "1"
									  },
									  {
										"bankID": 141,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "State bank of patiala",
										"reserved": "1"
									  },
									  {
										"bankID": 142,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "BARODA GUJARAT GRAMIN BANK",
										"reserved": "1"
									  },
									  {
										"bankID": 143,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "The Gujarat State Co-operative Bank Limited",
										"reserved": "1"
									  },
									  {
										"bankID": 144,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "vasai vikas sahakari",
										"reserved": "1"
									  },
									  {
										"bankID": 145,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "paschim banga gramin bank",
										"reserved": "1"
									  },
									  {
										"bankID": 146,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "PRAGATHI KRISHNA GRAMIN BANK",
										"reserved": "1"
									  },
									  {
										"bankID": 147,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "VISHAPATNAM co-operative bank",
										"reserved": "1"
									  },
									  {
										"bankID": 148,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Samarth Sahakari Bank Ltd",
										"reserved": "1"
									  },
									  {
										"bankID": 149,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "uttarbanga kshetriya gramin bank",
										"reserved": "1"
									  },
									  {
										"bankID": 150,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "janata sahakari bank ltd",
										"reserved": "1"
									  },
									  {
										"bankID": 152,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "the gayatri co-operative urban bank",
										"reserved": "1"
									  },
									  {
										"bankID": 153,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Jupiter Federal Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 154,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "ABHYUDAYA CO-OP. BANK LTD.",
										"reserved": "1"
									  },
									  {
										"bankID": 155,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "J&K Grameen Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 156,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Post Office Savings Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 157,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "SBM Bank India",
										"reserved": "1"
									  },
									  {
										"bankID": 20,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Bank of maharashtra",
										"reserved": "1"
									  },
									  {
										"bankID": 158,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "DAMAN",
										"reserved": "1"
									  },
									  {
										"bankID": 159,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Jind central Co-OP Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 151,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "PRATHAMA Up Gramin Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 160,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "The Jalgaon Peoples Co-Op Bank",
										"reserved": "1"
									  },
									  {
										"bankID": 161,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Associated Co-operative Bank limited",
										"reserved": "1"
									  },
									  {
										"bankID": 162,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "Mizoram Co-operative Apex Bank Ltd.",
										"reserved": "1"
									  },
									  {
										"bankID": 163,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "The Muslim Co-operative bank",
										"reserved": "1"
									  },
									  {
										"bankID": 164,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "PRATHAMA BANK",
										"reserved": "1"
									  },
									  {
										"bankID": 165,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "KASIKORNBANK",
										"reserved": "1"
									  }
									]
								  },
								  "code": 0,
								  "msg": "Succeed",
								  "msgCode": 0,
								  "serviceNowTime": "$shnunc"
								}
							';				
						}
						else if($withdrawid == 3){
							echo '
								{
								  "data": {
									"banklist": [
									  {
										"bankID": 55,
										"bankLogo": "https://pub-628304d7b25d454abf303bfafba6a2e0.r2.dev/ALADDINN",
										"bankName": "TRC",
										"reserved": "3"
									  }
									]
								  },
								  "code": 0,
								  "msg": "Succeed",
								  "msgCode": 0,
								  "serviceNowTime": "$shnunc"
								}
							';	
						}
					}
					else{
						$res['code'] = 4;
						$res['msg'] = 'No operation permission';
						$res['msgCode'] = 2;
						http_response_code(401);
						echo json_encode($res);
					}					
				}
				else{					
					$res['code'] = 4;
					$res['msg'] = 'No operation permission';
					$res['msgCode'] = 2;
					http_response_code(401);
					echo json_encode($res);					
				}
			}
			else{
				$res['code'] = 5;
				$res['msg'] = 'Wrong signature';
				$res['msgCode'] = 3;
				http_response_code(200);
				echo json_encode($res);				
			}
		}
		else{
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