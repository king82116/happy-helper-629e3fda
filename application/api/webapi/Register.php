<?php 
error_reporting(E_ALL);

// Display errors on the screen
ini_set('display_errors', 1);
	include "../../conn.php";
	include "../../functions2.php";
	
	header('Content-Type: application/json; charset=utf-8');
	header('Strict-Transport-Security: max-age=31536000');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, AR-REAL-IP');
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
		if (isset($shonupost['domainurl']) && isset($shonupost['invitecode']) && isset($shonupost['language']) && isset($shonupost['phonetype']) && isset($shonupost['pwd']) && isset($shonupost['random']) && isset($shonupost['registerType']) && isset($shonupost['signature']) && isset($shonupost['timestamp']) && isset($shonupost['username'])) {
			$domainurl = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['domainurl']));
			$invitecode = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['invitecode']));
			$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
			$phonetype = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['phonetype']));
			$pwd = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['pwd']));
			$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
			$registerType = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['registerType']));
			$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
			$username = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['username']));
			$shonustr = '{"domainurl":"'.$domainurl.'","invitecode":"'.$invitecode.'","language":'.$language.',"phonetype":'.$phonetype.',"pwd":"'.$pwd.'","random":"'.$random.'","registerType":"'.$registerType.'","username":"'.$username.'"}';
			$shonusign = strtoupper(md5($shonustr));
			if($shonusign != $signature){
				if(substr($username, 0, 2) == "91") {
					$username = substr($username, 2);
				}
				$captchaId = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['captchaId']));
                  $trackData = $shonupost['track'];

                $captchaQuery = "SELECT correctPositionx FROM captcha_data WHERE captchaId='$captchaId'";
                $captchaResult = $conn->query($captchaQuery);
                if ($captchaResult && mysqli_num_rows($captchaResult) == 1) {
                    $captchaRow = mysqli_fetch_assoc($captchaResult);
                    $correctPositionx = $captchaRow['correctPositionx'];
                    $userTracks = $trackData['tracks'];
                    $lastTrack = end($userTracks);
                    $userPositionx = $lastTrack['x'];
    
                if (abs($userPositionx - $correctPositionx) > 5) {
                    $res = [
                        "data" => null,
                        "code" => 1,
                        "msg" => "Verification failed, please try again",
                        "msgCode" => 31,
                        "serviceNowTime" => date("Y-m-d H:i:s")
                    ];
                    http_response_code(200);
                    echo json_encode($res);
                    exit;
                }
                } else {
                    $res['msg'] = 'Captcha data not found';
                    $res['msgCode'] = 32;
                    http_response_code(400);
                    echo json_encode($res);
                    exit;
                }
				$samasye = "SELECT id
				  FROM shonu_subjects WHERE owncode = '".$invitecode."'";
				$samasyephalitansa = $conn->query($samasye);
				$samasyephalitansa_dhadi = mysqli_num_rows($samasyephalitansa);
				if($samasyephalitansa_dhadi == 1){
					$samasye = "SELECT id
					  FROM shonu_subjects WHERE mobile = $username";
					$samasyephalitansa = $conn->query($samasye);
					$samasyephalitansa_dhadi = mysqli_num_rows($samasyephalitansa);
					if($samasyephalitansa_dhadi == 0){
						$samasye = "SELECT code
						  FROM shonu_subjects WHERE owncode = '".$invitecode."'";
						$samasyephalitansa = $conn->query($samasye);
						$samasyephalitansa_dhadi = mysqli_num_rows($samasyephalitansa);
						if($samasyephalitansa_dhadi == 1){
							$samasyephalitansa_sreni = mysqli_fetch_array($samasyephalitansa);
							$code1 = $samasyephalitansa_sreni['code'];
							$samasye = "SELECT code
							  FROM shonu_subjects WHERE owncode = '".$code1."'";
							$samasyephalitansa = $conn->query($samasye);
							$samasyephalitansa_dhadi = mysqli_num_rows($samasyephalitansa);
							if($samasyephalitansa_dhadi == 1){
								$samasyephalitansa_sreni = mysqli_fetch_array($samasyephalitansa);
								$code2 = $samasyephalitansa_sreni['code'];
								$samasye = "SELECT code
								  FROM shonu_subjects WHERE owncode = '".$code2."'";
								$samasyephalitansa = $conn->query($samasye);
								$samasyephalitansa_dhadi = mysqli_num_rows($samasyephalitansa);
								if($samasyephalitansa_dhadi == 1){
									$samasyephalitansa_sreni = mysqli_fetch_array($samasyephalitansa);
									$code3 = $samasyephalitansa_sreni['code'];
									$samasye = "SELECT code
									  FROM shonu_subjects WHERE owncode = '".$code3."'";
									$samasyephalitansa = $conn->query($samasye);
									$samasyephalitansa_dhadi = mysqli_num_rows($samasyephalitansa);
									if($samasyephalitansa_dhadi == 1){
										$samasyephalitansa_sreni = mysqli_fetch_array($samasyephalitansa);
										$code4 = $samasyephalitansa_sreni['code'];
										$samasye = "SELECT code
										  FROM shonu_subjects WHERE owncode = '".$code4."'";
										$samasyephalitansa = $conn->query($samasye);
										$samasyephalitansa_dhadi = mysqli_num_rows($samasyephalitansa);
										if($samasyephalitansa_dhadi == 1){
											$samasyephalitansa_sreni = mysqli_fetch_array($samasyephalitansa);
											$code5 = $samasyephalitansa_sreni['code'];
										}
										else{
											$code5 = null;
										}
									}
									else{
										$code4 = null;
										$code5 = null;
									}
								}
								else{
									$code3 = null;
									$code4 = null;
									$code5 = null;
								}
							}
							else{
								$code2 = null;
								$code3 = null;
								$code4 = null;
								$code5 = null;
							}
						}
						else{
							$code1 = null;
							$code2 = null;
							$code3 = null;
							$code4 = null;
							$code5 = null;
						}
												
						function generateRandomNumber() {
							$codethieffu = mt_rand(100000000000, 999999999999);
							return $codethieffu;
						}
						function checkNumberExists($conn, $number) {
                       try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $stmt = $conn->prepare("SELECT COUNT(*) FROM shonu_subjects WHERE owncode = ?");
        $stmt->bind_param("s", $number);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
                         } catch (mysqli_sql_exception $e) {
                            
                           }return false; 
                         }

						do {
    $codethiefstfu = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
} while (checkNumberExists($conn, $codethiefstfu)); 
$query = "SELECT id FROM shonu_subjects ORDER BY id DESC LIMIT 1"; 
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$nextId = isset($row['id']) ? $row['id'] + 1 : 1; 
$nextId = str_pad($nextId, 6, '0', STR_PAD_LEFT);
$owncode = $codethiefstfu . $nextId;
						
						$shnunc = date("Y-m-d H:i:s");
						$ipaddress = '';
						if (isset($_SERVER['HTTP_CLIENT_IP']))
							$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
						else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
							$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
						else if(isset($_SERVER['HTTP_X_FORWARDED']))
							$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
						else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
							$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
						else if(isset($_SERVER['HTTP_FORWARDED']))
							$ipaddress = $_SERVER['HTTP_FORWARDED'];
						else if(isset($_SERVER['REMOTE_ADDR']))
							$ipaddress = $_SERVER['REMOTE_ADDR'];
						else
							$ipaddress = 'UNKNOWN';	
						$user_agent = $_SERVER['HTTP_USER_AGENT'];
						
						function generateUniqueString($length = 8) {
							$letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
							$digits = '0123456789';
							$minDigits = 2;
							$remainingLength = $length - $minDigits;
							$shuffledLetters = str_shuffle($letters);
							$shuffledDigits = str_shuffle($digits);
							$selectedLetters = substr($shuffledLetters, 0, $remainingLength);
							$selectedDigits = substr($shuffledDigits, 0, $minDigits);
							$combined = $selectedLetters . $selectedDigits;
							$uniqueString = 'Member'.str_shuffle($combined);
							return $uniqueString;
						}
						$codechorkamukala = generateUniqueString();
						$password = md5($pwd);
						function generateUniqueStringForToken($length = 75) {
                        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        $charactersLength = strlen($characters);
                        $randomString = '';
                        for ($i = 0; $i < $length; $i++) {
                        $randomString .= $characters[random_int(0, $charactersLength - 1)];
                        }
                        return $randomString;
                        }
                        
                        $uniqueKeyToken = generateUniqueStringForToken();
						$tathya = mysqli_query($conn,"INSERT INTO `shonu_subjects` (`mobile`,`email`,`password`,`code`,`code1`,`code2`,`code3`,`code4`,`code5`,`owncode`,`privacy`,`status`,`createdate`,`ip`,`ishonup`,`pwd`,`shonullgnt`,`tnegaresunohs`,`codechorkamukala`,`token`) VALUES ('".$username."','','".$password."','".$invitecode."','".$code1."','".$code2."','".$code3."','".$code4."','".$code5."','".$owncode."','on','1','".$shnunc."','".$ipaddress."','".$ipaddress."','".$pwd."','".$shnunc."','".$user_agent."','".$codechorkamukala."','".$uniqueKeyToken."')");
						$last_id = $conn->insert_id;
						
						$status = 1;
						$expiresIn = time() + 86400;
						$shnutkn_head = array('alg'=>'HS256','typ'=>'JWT');
						$shnutkn_load = array('id'=>$last_id,'mobile'=>$username, 'status'=>$status, 'expire'=>$expiresIn, 'ishonup'=>$ipaddress, 'codechorkamukala'=>$codechorkamukala);
						$akshinak = generate_jwt($shnutkn_head, $shnutkn_load);
						
						$pwderrsql="UPDATE shonu_subjects set akshinak='".$akshinak."' where id='$last_id'";
						$conn->query($pwderrsql);
						
						$tathya = mysqli_query($conn,"INSERT INTO `shonu_kaichila` (`balakedara`,`motta`,`bonus`,`dinankavannuracisi`) VALUES ('".$last_id."','28','28','".$shnunc."')");
						
                       // New Member Registration Notification
                        $title = "NEW MEMBER";  // Set the notification title
                        $state = 0;  // Notification state (you can use '0' or '1' depending on your logic)
                        $insertNotificationQuery = "INSERT INTO notification (state, title, user_id, created_at) VALUES ($state, '$title', $last_id, '$shnunc')";
                        $conn->query($insertNotificationQuery);
                      
						$res['data']['tokenHeader'] = 'Bearer ';
						$res['data']['token'] = $akshinak;
						$res['code'] = 0;
						$res['msg'] = 'Succeed';
						$res['msgCode'] = 0;
						http_response_code(200);
						echo json_encode($res);	
					}
					else{
						$res['code'] = 1;
						$res['msg'] = 'Phone number have been registered';
						$res['msgCode'] = 111;
						http_response_code(200);
						echo json_encode($res);
					}			
				}
				else{
					$res['code'] = 8;
					$res['msg'] = 'Invitor Not Existed';
					$res['msgCode'] = 110;
					http_response_code(200);
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