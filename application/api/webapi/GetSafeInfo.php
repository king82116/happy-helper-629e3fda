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
		if (isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['signature']) && isset($shonupost['timestamp'])) {
			$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
			$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
			$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
			$shonustr = '{"language":'.$language.',"random":"'.$random.'"}';
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
					    $userId = $data_auth['payload']['id'];
						applySafeDailyInterestForUser($conn, $userId);
                        $query = "SELECT safe,safeearn,safetoday,motta FROM shonu_kaichila WHERE balakedara = $userId";
                        $result = mysqli_query($conn, $query);
                        $row = mysqli_fetch_assoc($result);
						$safeAmount = (float) $row['safe'];
						if (!isSafeBalanceEligibleForInterest($safeAmount) && !hasSafeInterestPaidToday($conn, $userId)) {
							mysqli_query($conn, "UPDATE shonu_kaichila SET safetoday = 0 WHERE balakedara = $userId");
							$row['safetoday'] = 0;
						}
						$userRate = getSafeUserDayShareRate($safeAmount);
						$estimatedRevenue = computeSafeEstimatedDailyRevenue($safeAmount);

                        $breakdown = getSafeBalanceBreakdown($conn, $userId);

                         $data = [
                            'state' => 1,
                            'shareTime' => 1,
                            'dayShareRate' => SAFE_DAILY_INTEREST_RATE,
                            'userDayShareRate' => $userRate,
                            'safeAmount' => $row['safe'],
                            'safeEarnings' => $row['safetoday'],
                            'willSafeEarnings' => (string) $estimatedRevenue,
                            'safeTotalAmount' => $row['safeearn'],
                            'shareAmount' => (int) SAFE_MIN_BALANCE_FOR_INTEREST,
                            'minSafeAmount' => SAFE_MIN_BALANCE_FOR_INTEREST,
                            'safeBoxCodeAmount' => 0,
                            'isOpenNewSetting' => "0",
                            'maxSafeAmount' => null,
                            'withdrawablePrincipal' => $breakdown['withdrawable_principal'],
                            'withdrawableInterest' => $breakdown['withdrawable_interest'],
                            'lockedPrincipal' => $breakdown['locked_principal'],
                            'minInterestWithdraw' => SAFE_MIN_INTEREST_WITHDRAW,
                            'principalLockDays' => SAFE_PRINCIPAL_LOCK_DAYS,
                          ];
                      
                      
                        $res['data'] = $data;
						$res['code'] = 0;
						$res['msg'] = 'Succeed';
						$res['msgCode'] = 0;
						http_response_code(200);
						echo json_encode($res);	
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