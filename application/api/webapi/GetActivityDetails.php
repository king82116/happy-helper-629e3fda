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
		if (isset($shonupost['bannerId'], $shonupost['language'], $shonupost['random'], $shonupost['signature'], $shonupost['timestamp'])) {

			$bannerId = (int) htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['bannerId']));
			$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
			$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
			$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));

			$shonustr = '{"bannerId":'.$bannerId.',"language":'.$language.',"random":"'.$random.'"}';
			$shonusign = strtoupper(md5($shonustr));

			if ($shonusign == $signature) {
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, true);

				if ($data_auth['status'] === 'Success') {
					$sesquery = "SELECT akshinak FROM shonu_subjects WHERE akshinak = '$author'";
					$sesresult = $conn->query($sesquery);

					if ($sesresult->num_rows == 1) {
						// ✅ Fetch banner from DB
						$query = "SELECT title, img, coverUrl, jumpType FROM banner_data WHERE bannerId = ?";
						$stmt = $conn->prepare($query);
						$stmt->bind_param("i", $bannerId);
						$stmt->execute();
						$result = $stmt->get_result();

						if ($result->num_rows > 0) {
							$row = $result->fetch_assoc();

							$data['title'] = $row['title'];
							$data['img'] = $row['img'];
							$data['coverUrl'] = $row['coverUrl'];
							$data['jumpType'] = (int) $row['jumpType'];

							$res['data'] = $data;
							$res['code'] = 0;
							$res['msg'] = 'Succeed';
							$res['msgCode'] = 0;
						} else {
							$res['code'] = 404;
							$res['msg'] = 'Banner not found';
							$res['msgCode'] = 9;
						}

						http_response_code(200);
						echo json_encode($res);
					}
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
