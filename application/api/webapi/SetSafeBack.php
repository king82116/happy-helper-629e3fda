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
		if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature'])) {
			$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
			$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
			$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
			
            $amount = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['amount']));
        
			$shonustr = '{"language":"'.$language.'","random":"'.$random.'"}';  
			$shonusign = strtoupper(md5($shonustr));
			
			if ($signature) {
				$bearer = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);
				$author = $bearer[1];				
				$is_jwt_valid = is_jwt_valid($author);
				$data_auth = json_decode($is_jwt_valid, 1);
				
				if ($data_auth['status'] === 'Success') {
					$sesquery = "SELECT akshinak FROM shonu_subjects WHERE akshinak = '$author'";
					$sesresult = $conn->query($sesquery);
					$sesnum = mysqli_num_rows($sesresult);
					
					if ($sesnum == 1) {
                        $userId = $data_auth['payload']['id'];
                        applySafeDailyInterestForUser($conn, $userId);

                        $amount = round((float) $amount, 2);
                        $breakdown = getSafeBalanceBreakdown($conn, $userId);
                        $validation = validateSafeWithdrawal($breakdown, $amount);

                        if (!$validation['ok']) {
                            $res['code'] = 8;
                            $res['msg'] = $validation['msg'];
                            $res['msgCode'] = $validation['msgCode'];
                            http_response_code(200);
                            echo json_encode($res);
                            exit;
                        }

                        $recordType = (int) $validation['record_type'];
                        $remainingSafe = max(0, (float) $breakdown['safe'] - $amount);
                        $recordRate = getSafeUserDayShareRate($remainingSafe);
                        $orderNum = date("YmdHis") . mt_rand(10000, 99999);
                        $earningsValue = $recordType === SAFE_INTEREST_WITHDRAW_TYPE ? $amount : 0;

                        $updateQuery = "UPDATE shonu_kaichila
                            SET motta = ROUND(motta + $amount, 2),
                                safe = ROUND(safe - $amount, 2)
                            WHERE balakedara = $userId
                            AND safe >= $amount";
                        $updated = mysqli_query($conn, $updateQuery);

                        if (!$updated || mysqli_affected_rows($conn) === 0) {
                            $res['code'] = 8;
                            $res['msg'] = 'Amount exceeds available safe balance';
                            $res['msgCode'] = 9106;
                            http_response_code(200);
                            echo json_encode($res);
                            exit;
                        }

                        $sql = "INSERT INTO safe_rec (user_id, motta, type, dayShareRate, orderNum, safeEarnings, earnings, created_at)
                            VALUES ('$userId', '$amount', '$recordType', '$recordRate', '$orderNum', '$earningsValue', '$earningsValue', '$shnunc')";
                        mysqli_query($conn, $sql);

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
