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
			$endDate = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['endDate']));
			$startDate = htmlspecialchars(mysqli_real_escape_string($conn, $shonupost['startDate']));
			$shonustr = '{"endDate":"'.$endDate.'","language":'.$language.',"random":"'.$random.'","startDate":"'.$startDate.'"}';
			
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
						$shonuid = (int)$data_auth['payload']['id'];
						$startDateSql = mysqli_real_escape_string($conn, $startDate);
						$endDateSql = mysqli_real_escape_string($conn, $endDate);
						$dateFilter = "byabaharkarta = $shonuid AND date(tiarikala) >= date('$startDateSql') AND date(tiarikala) <= date('$endDateSql')";

						$gameCategories = [
							['gameType' => 1, 'gameTypeName' => 'Win Go', 'tables' => [
								'bajikattuttate',
								'bajikattuttate_drei',
								'bajikattuttate_funf',
								'bajikattuttate_zehn',
							]],
							['gameType' => 2, 'gameTypeName' => '5D', 'tables' => [
								'bajikattuttate_aidudi',
								'bajikattuttate_aidudi_drei',
								'bajikattuttate_aidudi_funf',
								'bajikattuttate_aidudi_zehn',
							]],
							['gameType' => 3, 'gameTypeName' => 'K3', 'tables' => [
								'bajikattuttate_kemuru',
								'bajikattuttate_kemuru_drei',
								'bajikattuttate_kemuru_funf',
								'bajikattuttate_kemuru_zehn',
							]],
							['gameType' => 4, 'gameTypeName' => 'Trx Win Go', 'tables' => [
								'bajikattuttate_trx',
								'bajikattuttate_trx3',
								'bajikattuttate_trx5',
								'bajikattuttate_trx10',
							]],
						];

						$data = ['gameStatis' => [], 'sumBetAmount' => 0];
						$fnbetamt = 0;

						foreach ($gameCategories as $category) {
							$unionParts = [];
							foreach ($category['tables'] as $table) {
								$unionParts[] = "SELECT ketebida, sesabida, phalaphala FROM $table WHERE $dateFilter";
							}

							$samasye = "SELECT COALESCE(SUM(ketebida), 0) AS betAmount,
								COUNT(*) AS betCount,
								COALESCE(SUM(CASE WHEN phalaphala = 'gagner' THEN sesabida ELSE 0 END), 0) AS betWinLossAmount
								FROM (" . implode(' UNION ALL ', $unionParts) . ") AS game_bets";

							$samasyephalitansa = $conn->query($samasye);
							if ($samasyephalitansa && $row = $samasyephalitansa->fetch_assoc()) {
								$betCount = (int)$row['betCount'];
								if ($betCount > 0) {
									$betAmount = (float)$row['betAmount'];
									$data['gameStatis'][] = [
										'gameType' => $category['gameType'],
										'gameTypeName' => $category['gameTypeName'],
										'betAmount' => $betAmount,
										'betCount' => $betCount,
										'betWinLossAmount' => (float)$row['betWinLossAmount'],
									];
									$fnbetamt += $betAmount;
								}
							}
						}

						$data['sumBetAmount'] = $fnbetamt;
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