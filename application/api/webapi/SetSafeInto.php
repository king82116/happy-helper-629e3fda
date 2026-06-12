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
                        $query = "SELECT safe, motta, safetoday FROM shonu_kaichila WHERE balakedara = $userId";
                        $result = mysqli_query($conn, $query);
                        $row = mysqli_fetch_assoc($result);

                        $amount = (float) $amount;
                        $walletBalance = (float) $row['motta'];
                        $currentSafeBalance = (float) $row['safe'];

                        if ($amount < SAFE_MIN_BALANCE_FOR_INTEREST) {
                            $res['code'] = 8;
                            $res['msg'] = 'Minimum transfer to safe is ' . SAFE_MIN_BALANCE_FOR_INTEREST;
                            $res['msgCode'] = 7;
                            http_response_code(200);
                            echo json_encode($res);
                            exit;
                        }

                        if ($amount <= $walletBalance) {
                            $newSafeBalance = $currentSafeBalance + $amount;
                            $recordRate = getSafeUserDayShareRate($newSafeBalance);

                            $updateQuery = "UPDATE shonu_kaichila SET motta = motta - $amount, safe = safe + $amount WHERE balakedara = $userId";
                            mysqli_query($conn, $updateQuery) or die("Update failed: " . mysqli_error($conn));			
                            
                           $orderNum = date("YmdHis") . mt_rand(10000, 99999);
                           $sql = "INSERT INTO safe_rec (user_id, motta, type, dayShareRate, orderNum, safeEarnings, earnings, created_at) 
                           VALUES ('$userId', '$amount', '18', '$recordRate', '$orderNum', '0', '0', '$shnunc')";
                           mysqli_query($conn, $sql);

 
                            $res['code'] = 0;
                            $res['msg'] = 'Succeed';
                            $res['msgCode'] = 0;
                            http_response_code(200);
                            echo json_encode($res);
                        } else {
                            $res['code'] = 8;
                            $res['msg'] = 'Amount exceeds available safe balance';
                            $res['msgCode'] = 7;
                            http_response_code(200);
                            echo json_encode($res);
                            exit;
                        }
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
