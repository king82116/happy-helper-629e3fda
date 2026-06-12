<?php 
	include "../../conn.php";
	include "../../functions2.php";

	try {
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
		if (isset($shonupost['amount']) && isset($shonupost['betCount']) && isset($shonupost['gameType']) && isset($shonupost['issuenumber']) && 
			isset($shonupost['language']) && isset($shonupost['random']) && isset($shonupost['selectType']) && isset($shonupost['signature']) && 
			isset($shonupost['timestamp']) && isset($shonupost['typeId'])) {
			$amount = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['amount']));
			$betCount = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['betCount']));
			$gameType = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['gameType']));
			$issuenumber = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['issuenumber']));
			$language = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['language']));
			$random = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['random']));
			$selectType = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['selectType']));
			$signature = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['signature']));
			$typeId = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['typeId']));
			$shonustr = '{"amount":'.$amount.',"betCount":'.$betCount.',"gameType":'.$gameType.',"issuenumber":"'.$issuenumber.'","language":'.$language.',"random":"'.$random.'","selectType":'.$selectType.',"typeId":'.$typeId.'}';
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
						if($typeId == 1){
							$lordjesus = 'bajikattuttate';
							$sonofgod = 'gelluonduhogu';
						}
						else if($typeId == 2){
							$lordjesus = 'bajikattuttate_drei';
							$sonofgod = 'gelluonduhogu_drei';
						}
						else if($typeId == 3){
							$lordjesus = 'bajikattuttate_funf';
							$sonofgod = 'gelluonduhogu_funf';
						}
						else if($typeId == 4){
							$lordjesus = 'bajikattuttate_zehn';
							$sonofgod = 'gelluonduhogu_zehn';
						}
						if($betCount >= 1){
							if($amount >= 1){
								$periodExpired = false;
								$samasyesreni = null;
								if ($typeId == 4) {
									$periodLookupSql = "SELECT atadaaidi, dinankavannuracisi
									  FROM " . $sonofgod . "
									  WHERE atadaaidi = '" . $issuenumber . "'
									  LIMIT 1";
									$periodLookupResult = $conn->query($periodLookupSql);
									if ($periodLookupResult) {
										if (mysqli_num_rows($periodLookupResult) === 1) {
											$samasyesreni = mysqli_fetch_assoc($periodLookupResult);
											$periodStart = false;
											if (isset($samasyesreni['dinankavannuracisi']) && $samasyesreni['dinankavannuracisi'] !== '0000-00-00 00:00:00') {
												$periodStart = strtotime($samasyesreni['dinankavannuracisi']);
											}
											if ($periodStart !== false) {
												$nowTimestamp = time();
												// Allow betting only while we are within the 30-second window
												if ($nowTimestamp > ($periodStart + 30)) {
													$periodExpired = true;
												}
											}
										}
										mysqli_free_result($periodLookupResult);
									}
								}
								if ($periodExpired) {
									$res['code'] = 1;
									$res['msg'] = 'The current period is settled';
									$res['msgCode'] = 404;
									http_response_code(200);
									echo json_encode($res);
									exit;
								}
								if (!$samasyesreni) {
									$samasye = "SELECT atadaaidi, dinankavannuracisi
									  FROM " . $sonofgod . "
									  ORDER BY kramasankhye DESC LIMIT 1";
									$samasyephalitansa = $conn->query($samasye);
									if ($samasyephalitansa) {
										$samasyesreni = mysqli_fetch_assoc($samasyephalitansa);
										mysqli_free_result($samasyephalitansa);
									}
								}
								if(isset($samasyesreni['atadaaidi']) && $samasyesreni['atadaaidi'] == $issuenumber){
									$totalamount = $amount * $betCount;								
									$balquery = "SELECT motta
									  FROM shonu_kaichila
									  WHERE balakedara = ".$data_auth['payload']['id'];
									$balresult = $conn->query($balquery);
									$balarr = mysqli_fetch_array($balresult);									
									$shonubalance = $balarr['motta'];								
									if($shonubalance >= $totalamount){
										$byabaharkarta = $data_auth['payload']['id'];
										
										// Check if the user has completed their first recharge
										$first_recharge_query = "SELECT * FROM thevani WHERE balakedara = '$byabaharkarta' AND sthiti = 1";
										//$first_recharge_result = $conn->query($first_recharge_query);
                                      
                                      $first_recharge_result = 1;
										//if ($first_recharge_result->num_rows > 0) {
										if ($first_recharge_result) {
											// Proceed with the bet if the first recharge is done
											$sesabida = sprintf("%.2f", $totalamount * 0.98);
											$tathya = mysqli_query($conn,"INSERT INTO `".$lordjesus."` (`byabaharkarta`,`kalaparichaya`,`prakar`,`ojana`,`menge`,`wettanzahl`,`ketebida`,`phalaphala`,`sesabida`,`tiarikala`) VALUES ('".$byabaharkarta."','".$issuenumber."','".$gameType."','".$selectType."','".$amount."','".$betCount."','".$totalamount."','perte','".$sesabida."','".$shnunc."')");
											$mottanutan = $shonubalance - $totalamount;
											$nabikarana = "UPDATE shonu_kaichila set motta='$mottanutan' where balakedara='$byabaharkarta'";
											$conn->query($nabikarana);
											include "commission.php";
											include "vip.php";
											$res['data'] = null;
											$res['code'] = 0;
											$res['msg'] = 'Succeed';
											$res['msgCode'] = 0;
											http_response_code(200);
											echo json_encode($res);
										} else {
											
											$res['code'] = 7;
											$res['msg'] = 'First recharge not completed';
											$res['msgCode'] = 502;
											http_response_code(200);
											echo json_encode($res);
										}
									} else {
										$res['code'] = 1;
										$res['msg'] = 'Balance is not enough';
										$res['msgCode'] = 142;
										http_response_code(200);
										echo json_encode($res);
									}
								} else {
									$res['code'] = 1;
									$res['msg'] = 'The current period is settled';
									$res['msgCode'] = 404;
									http_response_code(200);
									echo json_encode($res);
								}																																				
							} else {
								$res['code'] = 7;
								$res['msg'] = "Invalid value for parameter 'Amount'";
								unset($res['msgCode']);
								unset($res['serviceNowTime']);
								http_response_code(200);
								echo json_encode($res);
							}
						} else {
							$res['code'] = 7;
							$res['msg'] = "Invalid value for parameter 'BetCount'";
							unset($res['msgCode']);
							unset($res['serviceNowTime']);
							http_response_code(200);
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
					$res['code'] = 4;
					$res['msg'] = 'No operation permission';
					$res['msgCode'] = 2;
					http_response_code(401);
					echo json_encode($res);					
				}
			} else {
				$res['code'] = 5;
				$res['msg'] = 'Wrong signature';
				$res['msgCode'] = 1;
				http_response_code(403);
				echo json_encode($res);
			}
		} else {
			$res['code'] = 9;
			$res['msg'] = 'Missing parameters';
			unset($res['msgCode']);
			unset($res['serviceNowTime']);
			http_response_code(200);
			echo json_encode($res);
		}
	}
	else{
		http_response_code(405);
		echo json_encode($res);
	}	
	} finally {
		closeDBConnection();
		$conn = null;
	}
?>
