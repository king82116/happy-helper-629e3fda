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
						
						// Add game vendor data here
						$res['data'] = [
							[
								"vendorCode" => "JILI",
								"sort" => 9,
								"childList" => [
									[ "gameID" => "229", "gameNameEn" => "Mines", "img" => "https://ossimg.diuacting.com/DiuWin/gamelogo/JILI/229.png", "vendorId" => 18, "vendorCode" => "JILI", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "224", "gameNameEn" => "Go Rush", "img" => "https://ossimg.diuacting.com/DiuWin/gamelogo/JILI/224.png", "vendorId" => 18, "vendorCode" => "JILI", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "51", "gameNameEn" => "Money Coming", "img" => "https://ossimg.diuacting.com/DiuWin/gamelogo/JILI/51.png", "vendorId" => 18, "vendorCode" => "JILI", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "223", "gameNameEn" => "Fortune Gems 2", "img" => "https://ossimg.diuacting.com/DiuWin/gamelogo/JILI/223.png", "vendorId" => 18, "vendorCode" => "JILI", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "109", "gameNameEn" => "Fortune Gems", "img" => "https://ossimg.diuacting.com/DiuWin/gamelogo/JILI/109.png", "vendorId" => 18, "vendorCode" => "JILI", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "232", "gameNameEn" => "Tower", "img" => "https://ossimg.diuacting.com/DiuWin/gamelogo/JILI/232.png", "vendorId" => 18, "vendorCode" => "JILI", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "236", "gameNameEn" => "Wheel", "img" => "https://ossimg.diuacting.com/DiuWin/gamelogo/JILI/236.png", "vendorId" => 18, "vendorCode" => "JILI", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "197", "gameNameEn" => "Color Game", "img" => "https://ossimg.diuacting.com/DiuWin/gamelogo/JILI/197.png", "vendorId" => 18, "vendorCode" => "JILI", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "200", "gameNameEn" => "Pappu", "img" => "https://ossimg.diuacting.com/DiuWin/gamelogo/JILI/200.png", "vendorId" => 18, "vendorCode" => "JILI", "imgUrl2" => null, "customGameType" => 0 ]
								]
							],
							// Add the new data you provided
							[
								"vendorCode" => "DG",
								"sort" => 7,
								"childList" => [
									[ "gameID" => "75776a4167b0616cec63966d3877ff22", "gameNameEn" => "DragonTiger", "img" => "https://ossimg.yuk87k786d.com/sikkim/gamelogo/DG/1_3.png", "vendorId" => 7, "vendorCode" => "DG", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "7e1886a44af41f33e03903df4d96d9f8", "gameNameEn" => "InBaccarat", "img" => "https://ossimg.yuk87k786d.com/sikkim/gamelogo/DG/1_2.png", "vendorId" => 7, "vendorCode" => "DG", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "831d834fbc4f2b343e7cd5fb58eb6300", "gameNameEn" => "Three Face", "img" => "https://ossimg.yuk87k786d.com/sikkim/gamelogo/DG/1_16.png", "vendorId" => 7, "vendorCode" => "DG", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "fc836890aa838e03419a2751af98d0ce", "gameNameEn" => "Baccarat", "img" => "https://ossimg.yuk87k786d.com/sikkim/gamelogo/DG/1_1.png", "vendorId" => 7, "vendorCode" => "DG", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "93322d58de1175bb2078e473c1ea5757", "gameNameEn" => "Fish, shrimp and crab", "img" => "https://ossimg.yuk87k786d.com/sikkim/gamelogo/DG/1_15.png", "vendorId" => 7, "vendorCode" => "DG", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "dc4f44b7e7bd3ff918b6008d89598c3f", "gameNameEn" => "Quickness Sicbo", "img" => "https://ossimg.yuk87k786d.com/sikkim/gamelogo/DG/1_12.png", "vendorId" => 7, "vendorCode" => "DG", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "a973fbb6b172cca38155523ab540d69f", "gameNameEn" => "Roulette", "img" => "https://ossimg.yuk87k786d.com/sikkim/gamelogo/DG/1_4.png", "vendorId" => 7, "vendorCode" => "DG", "imgUrl2" => null, "customGameType" => 0 ]
								]
							],
							[
								"vendorCode" => "SEXY_Video",
								"sort" => 1,
								"childList" => [
									[ "gameID" => "3630a6a3c836afa6864578ef21f8fa93", "gameNameEn" => "DragonTiger", "img" => "https://ossimg.yuk87k786d.com/sikkim/gamelogo/SEXY_Video/MX-LIVE-006.png", "vendorId" => 27, "vendorCode" => "SEXY_Video", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "3630a6a3c836afa6864578ef21f8fa93", "gameNameEn" => "Roulette", "img" => "https://ossimg.yuk87k786d.com/sikkim/gamelogo/SEXY_Video/MX-LIVE-009.png", "vendorId" => 27, "vendorCode" => "SEXY_Video", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "9412795579b89bdee57f00ee2d301a22", "gameNameEn" => "Extra Andar Bahar", "img" => "https://ossimg.yuk87k786d.com/sikkim/gamelogo/SEXY_Video/MX-LIVE-012.png", "vendorId" => 27, "vendorCode" => "SEXY_Video", "imgUrl2" => null, "customGameType" => 0 ]
								]
							],
							[
								"vendorCode" => "MG_Video",
								"sort" => 0,
								"childList" => [
									[ "gameID" => "831d834fbc4f2b343e7cd5fb58eb6300", "gameNameEn" => "Roulette", "img" => "https://ossimg.yuk87k786d.com/sikkim/gamelogo/MG_Video/SMG_titaniumLiveGames_Roulette.png", "vendorId" => 38, "vendorCode" => "MG_Video", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "96682117485be3f7551db8fd70f87c73", "gameNameEn" => "Sicbo", "img" => "https://ossimg.yuk87k786d.com/sikkim/gamelogo/MG_Video/SMG_titaniumLiveGames_Sicbo.png", "vendorId" => 38, "vendorCode" => "MG_Video", "imgUrl2" => null, "customGameType" => 0 ],
									[ "gameID" => "f0add665a33f9b0440694015268d1d6d", "gameNameEn" => "Auto Roulette", "img" => "https://ossimg.yuk87k786d.com/sikkim/gamelogo/MG_Video/SMG_titaniumLiveGamesAutoRoulette.png", "vendorId" => 38, "vendorCode" => "MG_Video", "imgUrl2" => null, "customGameType" => 0 ]
								]
							]
						];
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
