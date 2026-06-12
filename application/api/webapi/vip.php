<?php 
	if (!function_exists('getVipUpgradeBonusSum')) {
		require_once __DIR__ . '/../../functions2.php';
	}

	$vipquery = "SELECT expe, lvl
	  FROM vip
	  WHERE userid = ".$byabaharkarta;
	$vipresult = $conn->query($vipquery);
	$viprow = mysqli_num_rows($vipresult);
	//rebet
	$nabikarana = "UPDATE shonu_kaichila SET rebet = rebet + '$totalamount' WHERE balakedara='$byabaharkarta'";
				$conn->query($nabikarana);
	if($viprow >=1){
		$viparr = mysqli_fetch_array($vipresult);
		$expe = $viparr['expe'] + $totalamount;
		$orlvl = $viparr['lvl'];
		if($expe >= 3000 && $expe < 30000){
			$lvl = 1;
		}
		else if($expe >= 30000 && $expe < 400000){
			$lvl = 2;
		}
		else if($expe >= 400000 && $expe < 4000000){
			$lvl = 3;
		}
		else if($expe >= 4000000 && $expe < 20000000){
			$lvl = 4;
		}
		else if($expe >= 20000000){
			$lvl = 5;
		}
		else{
			$lvl = 0;
		}

		$diff = $lvl - $orlvl;
		if ($diff >= 1 && $lvl >= 1) {
			$giveamt = getVipUpgradeBonusSum($orlvl, $lvl);
			if ($giveamt > 0) {
				$beforeWagering = computeUserWithdrawalWagering($conn, $byabaharkarta);
				$nabikarana = "UPDATE shonu_kaichila set motta = motta + '$giveamt' where balakedara='$byabaharkarta'";
				$conn->query($nabikarana);
				$viprec = "INSERT INTO viprec (user_id, type, motta, created_at, lvl) VALUES ('$byabaharkarta', '1', '$giveamt', '$shnunc', '$lvl')";
				$conn->query($viprec);
				applyBonusNeedToBet($conn, $byabaharkarta, $beforeWagering['amountofCode'], $giveamt);
			}
		}

		$nabikarana = "UPDATE vip set expe = '$expe', lvl = '$lvl', createdate = '$shnunc' where userid='$byabaharkarta'";
		$conn->query($nabikarana);
	}
	else{
		$expe = $totalamount;
		if($expe >= 3000 && $expe < 30000){
			$lvl = 1;
		}
		else if($expe >= 30000 && $expe < 400000){
			$lvl = 2;
		}
		else if($expe >= 400000 && $expe < 4000000){
			$lvl = 3;
		}
		else if($expe >= 4000000 && $expe < 20000000){
			$lvl = 4;
		}
		else if($expe >= 20000000){
			$lvl = 5;
		}
		else{
			$lvl = 0;
		}

		$giveamt = ($lvl >= 1) ? getVipUpgradeBonusSum(0, $lvl) : 0;
		$tathya = mysqli_query($conn,"INSERT INTO `vip` (`userid`,`expe`,`lvl`,`createdate`) VALUES ('".$byabaharkarta."','".$expe."','".$lvl."','".$shnunc."')");
		if ($giveamt > 0) {
			$beforeWagering = computeUserWithdrawalWagering($conn, $byabaharkarta);
			$nabikarana = "UPDATE shonu_kaichila set motta = motta + '$giveamt' where balakedara='$byabaharkarta'";
			$conn->query($nabikarana);
			$viprec = "INSERT INTO viprec (user_id, type, motta, created_at, lvl) VALUES ('$byabaharkarta', '1', '$giveamt', '$shnunc', '$lvl')";
			$conn->query($viprec);
			applyBonusNeedToBet($conn, $byabaharkarta, $beforeWagering['amountofCode'], $giveamt);
		}
	}
?>
