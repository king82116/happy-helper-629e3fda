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
	
	function replaceWithAsterisks($inputString) {
		if (strlen($inputString) < 10) {
			return $inputString;
		}
		$before = substr($inputString, 0, 6);
		$toReplace = substr($inputString, 6, 4);
		$after = substr($inputString, 10);
		$replaced = str_repeat('*', strlen($toReplace));
		$resultString = $before . $replaced . $after;
		return $resultString;
	}
	
	
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
						$shonuid = $data_auth['payload']['id'];
						if($withdrawid == 1){
							$samasye = "SELECT phalanubhavi
							  FROM khate WHERE byabaharkarta = $shonuid AND khatehesaru != 'TRC'
							  ORDER BY shonu DESC LIMIT 1";
							$samasyephalitansa = $conn->query($samasye);
							$samasyephalitansa_dhadi = mysqli_num_rows($samasyephalitansa);	
							if($samasyephalitansa_dhadi >= 1){
								$samasyephalitansa_sreni = mysqli_fetch_array($samasyephalitansa);						
								$data['lastBandCarkName'] = $samasyephalitansa_sreni['phalanubhavi'];
								
								$samasye = "SELECT shonu, khatehesaru, khatesankhye, kod, duravani
								  FROM khate WHERE byabaharkarta = $shonuid AND khatehesaru != 'TRC'
								  ORDER BY shonu DESC";
								$samasyephalitansa = $conn->query($samasye);
								$i = 0;
								while($row = mysqli_fetch_array($samasyephalitansa)){
									$data['withdrawalslist'][$i]['bid'] = $row['shonu'];
									$data['withdrawalslist'][$i]['bankName'] = $row['khatehesaru'];
									$data['withdrawalslist'][$i]['beneficiaryName'] = '';
									
									$data['withdrawalslist'][$i]['accountNo'] = replaceWithAsterisks($row['khatesankhye']);
									$data['withdrawalslist'][$i]['ifsCode'] = $row['kod'];
									$data['withdrawalslist'][$i]['withType'] = 1;
									$data['withdrawalslist'][$i]['mobileNo'] = replaceWithAsterisks($row['duravani']);
									$data['withdrawalslist'][$i]['bankProvince'] = '';
									$data['withdrawalslist'][$i]['bankCity'] = '';
									$data['withdrawalslist'][$i]['bankAddress'] = '';
									$i++;
								}
							}
							else{
								$data['lastBandCarkName'] = null;
								$data['withdrawalslist'] = [];
							}
						}
						elseif($withdrawid == 3){
							$samasye = "SELECT phalanubhavi
							  FROM khate WHERE byabaharkarta = $shonuid AND khatehesaru = 'TRC'
							  ORDER BY shonu DESC LIMIT 1";
							$samasyephalitansa = $conn->query($samasye);
							$samasyephalitansa_dhadi = mysqli_num_rows($samasyephalitansa);	
							if($samasyephalitansa_dhadi >= 1){
								$samasyephalitansa_sreni = mysqli_fetch_array($samasyephalitansa);						
								$data['lastBandCarkName'] = $samasyephalitansa_sreni['phalanubhavi'];
								
								$samasye = "SELECT shonu, khatehesaru, khatesankhye, kod, duravani
								  FROM khate WHERE byabaharkarta = $shonuid AND khatehesaru = 'TRC'
								  ORDER BY shonu DESC";
								$samasyephalitansa = $conn->query($samasye);
								$i = 0;
								while($row = mysqli_fetch_array($samasyephalitansa)){
									$data['withdrawalslist'][$i]['bid'] = $row['shonu'];
									$data['withdrawalslist'][$i]['bankName'] = $row['khatehesaru'];
									$data['withdrawalslist'][$i]['beneficiaryName'] = '';
									
									$data['withdrawalslist'][$i]['accountNo'] = replaceWithAsterisks($row['khatesankhye']);
									$data['withdrawalslist'][$i]['ifsCode'] = $row['kod'];
									$data['withdrawalslist'][$i]['withType'] = 1;
									$data['withdrawalslist'][$i]['mobileNo'] = replaceWithAsterisks($row['duravani']);
									$data['withdrawalslist'][$i]['bankProvince'] = '';
									$data['withdrawalslist'][$i]['bankCity'] = '';
									$data['withdrawalslist'][$i]['bankAddress'] = '';
									$i++;
								}
							}
							else{
								$data['lastBandCarkName'] = null;
								$data['withdrawalslist'] = [];
							}
						}
						
						$withdrawChannel = normalizeWithdrawalChannelType($withdrawid);
						$dailyWithdrawLimits = getUserDailyWithdrawalLimits(
							$conn,
							$shonuid,
							null,
							$withdrawChannel
						);
						$channelLimits = getUserDailyWithdrawalLimitsByChannel($conn, $shonuid);
						$shelly = $dailyWithdrawLimits['withdrawCount'];
						$shelly_1 = $dailyWithdrawLimits['withdrawRemainingCount'];
$withdrawLimits = getWithdrawalAmountLimits($conn, $withdrawChannel);
$fee = $withdrawLimits['fee'];
$minPrice = $withdrawLimits['minPrice'];
$maxPrice = $withdrawLimits['maxPrice'];
$uRate = $withdrawLimits['uRate'];

// Withdrawal rules array
$data["withdrawalsrule"]["withdrawCount"] = $shelly;
$data["withdrawalsrule"]["withdrawRemainingCount"] = $shelly_1;
$data["withdrawalsrule"]["withdrawDailyLimit"] = $dailyWithdrawLimits['dailyLimit'];
$data["withdrawalsrule"]["channelType"] = $withdrawChannel;
$data["withdrawalsrule"]["bankWithdrawCount"] = $channelLimits['bank']['withdrawCount'];
$data["withdrawalsrule"]["bankWithdrawRemainingCount"] = $channelLimits['bank']['withdrawRemainingCount'];
$data["withdrawalsrule"]["usdtWithdrawCount"] = $channelLimits['usdt']['withdrawCount'];
$data["withdrawalsrule"]["usdtWithdrawRemainingCount"] = $channelLimits['usdt']['withdrawRemainingCount'];
$data["withdrawalsrule"]["startTime"] = "00:00";
$data["withdrawalsrule"]["endTime"] = "23:59";
$data["withdrawalsrule"]["fee"] = (int)$fee;
$data["withdrawalsrule"]["minPrice"] = (int)$minPrice;
$data["withdrawalsrule"]["maxPrice"] = (int)$maxPrice;
$data["withdrawalsrule"]["usdtMinUsd"] = $withdrawLimits['usdtMinUsd'];
$data["withdrawalsrule"]["usdtMaxUsd"] = $withdrawLimits['usdtMaxUsd'];
$data["withdrawalsrule"]["usdtMinInr"] = $withdrawLimits['usdtMinInr'];
$data["withdrawalsrule"]["usdtMaxInr"] = $withdrawLimits['usdtMaxInr'];
$data["withdrawalsrule"]["withMinPrice"] = (int)$minPrice;
$data["withdrawalsrule"]["withMaxPrice"] = (int)$maxPrice;

// Query to fetch withdrawal amount
$balquery = "SELECT motta
             FROM shonu_kaichila
             WHERE balakedara = ".$data_auth['payload']['id'];

$balresult = $conn->query($balquery);
$balarr = mysqli_fetch_array($balresult);

// Assign withdrawal amount
$data["withdrawalsrule"]["amount"] = $balarr["motta"];
						
						$wagering = computeUserWithdrawalWagering($conn, $shonuid, $balarr["motta"]);
						$data["withdrawalsrule"]["amountofCode"] = $wagering['amountofCode'];
						$data["withdrawalsrule"]["totalBonus"] = $wagering['totalBonus'];
						$data["withdrawalsrule"]["requiredWager"] = $wagering['requiredWager'];
						$data["withdrawalsrule"]["totalBet"] = $wagering['totalBet'];
						$data["withdrawalsrule"]["canWithdrawAmount"] = $wagering['canWithdrawAmount'];
				$data["withdrawalsrule"]["c2cUnitAmount"] = 0;
				$data["withdrawalsrule"]["uRate"] = round((float)$uRate, 2);
				$data["withdrawalsrule"]["uGold"] = 0;
						
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