<?php
// Add debug logging function
function debugLog($message)
{
	echo "[DEBUG] " . date('Y-m-d H:i:s') . " - " . $message . "\n";
}

function computeThirtySecondPeriodContext()
{
	try {
		$reykjavikTz = new DateTimeZone('Atlantic/Reykjavik');
		$kolkataTz = new DateTimeZone('Asia/Kolkata');
		$nowReykjavik = new DateTimeImmutable('now', $reykjavikTz);
		$settlementInstant = $nowReykjavik->sub(new DateInterval('PT30S'));
		$startOfDay = $settlementInstant->setTime(0, 0, 0);
		$diffInSeconds = $settlementInstant->getTimestamp() - $startOfDay->getTimestamp();
		$sequenceNumber = intdiv($diffInSeconds, 30) + 1;
		if ($sequenceNumber < 1) {
			$sequenceNumber = 1;
		}
		$sequencePart = str_pad((string) $sequenceNumber, 4, '0', STR_PAD_LEFT);
		$issueNumber = $settlementInstant->format('Ymd') . "10005" . $sequencePart;
		$periodStartKolkata = $settlementInstant->setTimezone($kolkataTz);
		$periodEndKolkata = $periodStartKolkata->add(new DateInterval('PT30S'));
		return [
			'issueNumber' => $issueNumber,
			'periodStartKolkata' => $periodStartKolkata,
			'periodEndKolkata' => $periodEndKolkata,
		];
	} catch (Exception $exception) {
		debugLog('Failed to compute period context: ' . $exception->getMessage());
		return [
			'issueNumber' => null,
			'periodStartKolkata' => null,
			'periodEndKolkata' => null,
		];
	}
}

debugLog("Starting game processing...");
$checkQuery = "SELECT sankhye FROM hastacalita_phalitansa_drei WHERE sthiti='1' LIMIT 1";
$checkResult = mysqli_query($conn, $checkQuery);
if ($checkRow = mysqli_fetch_assoc($checkResult)) {
	$defaultNumber = $checkRow['sankhye'];
	debugLog("Using predefined number: " . $defaultNumber);
} else {
	debugLog("No predefined number found, checking API settings...");
	$checkQuery = "SELECT id FROM sametrend LIMIT 1";
	$checkResult = mysqli_query($conn, $checkQuery);
	$useAPI = false;
	if ($checkRow = mysqli_fetch_assoc($checkResult)) {
		$useAPI = ($checkRow['id'] == 1);
		debugLog("sametrend id: " . $checkRow['id'] . ", useAPI: " . ($useAPI ? 'true' : 'false'));
	} else {
		debugLog("No records found in sametrend table");
	}
	if ($useAPI) {
		debugLog("Attempting API call...");
		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => 'https://api.joshclub.fun/wingo_30sec.php',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_USERAGENT => 'PHP Game Client'
		]);
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlError = curl_error($ch);
		curl_close($ch);
		debugLog("API Response: '$response'");
		debugLog("HTTP Code: $httpCode");
		debugLog("cURL Error: '$curlError'");
		if ($curlError) {
			debugLog("API call failed with cURL error, using random");
			$defaultNumber = rand(0, 9);
		} elseif ($httpCode !== 200) {
			debugLog("API call failed with HTTP error, using random");
			$defaultNumber = rand(0, 9);
		} else {
			// Parse JSON response
			$jsonData = json_decode($response, true);
			if (json_last_error() === JSON_ERROR_NONE && isset($jsonData['winning_number'])) {
				// Successfully parsed JSON and found winning_number
				$apiResult = (int) $jsonData['winning_number'];
				debugLog("JSON parsed successfully, winning_number: $apiResult");
				$defaultNumber = ($apiResult >= 0 && $apiResult <= 9) ? $apiResult : rand(0, 9);
			} else {
				// JSON parsing failed, try old method (plain number)
				debugLog("JSON parsing failed, trying plain number parsing");
				$apiResult = (int) trim($response);
				$defaultNumber = ($apiResult >= 0 && $apiResult <= 9) ? $apiResult : rand(0, 9);
			}
			debugLog("API returned: $apiResult");
			debugLog("Final number: $defaultNumber");
		}
	} else {
		debugLog("API disabled, using random number");
		$defaultNumber = rand(0, 9);
	}
}

debugLog("Final defaultNumber: $defaultNumber");
$latestGameIdFromDb = null;
// Use the game ID that was created by the main script
if (isset($bartamankalakrama)) {
	$samasyesreni['atadaaidi'] = $bartamankalakrama;
	$latestGameIdFromDb = (string) $bartamankalakrama;
	debugLog("Using game ID from main script: " . $bartamankalakrama);
} else {
	// Fallback: query for the latest game ID if not passed
	$samasye = "SELECT atadaaidi
      FROM gelluonduhogu_zehn
      ORDER BY kramasankhye DESC LIMIT 1";
	$samasyephalitansa = $conn->query($samasye);
	$samasyesreni = mysqli_fetch_array($samasyephalitansa);
	if (!is_array($samasyesreni)) {
		$samasyesreni = [];
	}
	$latestGameIdFromDb = isset($samasyesreni['atadaaidi']) ? (string) $samasyesreni['atadaaidi'] : null;
	debugLog("Using latest game ID from database: " . ($latestGameIdFromDb ?? 'none'));
}
$periodContext = computeThirtySecondPeriodContext();
if (!empty($periodContext['issueNumber']) && $periodContext['periodStartKolkata'] instanceof DateTimeImmutable && $periodContext['periodEndKolkata'] instanceof DateTimeImmutable) {
	debugLog("Computed 30s period id: " . $periodContext['issueNumber'] . " (window: " . $periodContext['periodStartKolkata']->format('Y-m-d H:i:s') . " -> " . $periodContext['periodEndKolkata']->format('Y-m-d H:i:s') . ")");
}
$candidateIssueNumbers = [];
if (!empty($latestGameIdFromDb)) {
	$candidateIssueNumbers[] = $latestGameIdFromDb;
}
if (!empty($periodContext['issueNumber']) && !in_array($periodContext['issueNumber'], $candidateIssueNumbers, true)) {
	$candidateIssueNumbers[] = $periodContext['issueNumber'];
}
$selectedIssueNumber = null;
foreach ($candidateIssueNumbers as $candidateIssueNumber) {
	$candidateEsc = mysqli_real_escape_string($conn, $candidateIssueNumber);
	$checkSql = "SELECT 1 FROM bajikattuttate_zehn WHERE kalaparichaya = '" . $candidateEsc . "' LIMIT 1";
	$checkResult = mysqli_query($conn, $checkSql);
	if ($checkResult) {
		if (mysqli_num_rows($checkResult) > 0) {
			$selectedIssueNumber = $candidateIssueNumber;
			mysqli_free_result($checkResult);
			break;
		}
		mysqli_free_result($checkResult);
	} else {
		debugLog("Bet lookup failed for period $candidateIssueNumber: " . mysqli_error($conn));
	}
}
if ($selectedIssueNumber === null) {
	$fallbackIssue = $periodContext['issueNumber'] ?? $latestGameIdFromDb;
	if ($fallbackIssue !== null) {
		$selectedIssueNumber = $fallbackIssue;
	}
}
if ($selectedIssueNumber !== null) {
	if (!empty($latestGameIdFromDb) && $latestGameIdFromDb !== $selectedIssueNumber) {
		debugLog("Switching period id from " . $latestGameIdFromDb . " to $selectedIssueNumber");
	} elseif (empty($latestGameIdFromDb)) {
		debugLog("Using computed period id $selectedIssueNumber because database returned none");
	}
	$samasyesreni['atadaaidi'] = $selectedIssueNumber;
	$latestGameIdFromDb = $selectedIssueNumber;
} else {
	debugLog("Unable to resolve current period id; skipping settlement step");
}
if ($samasyesreni['atadaaidi'] != null) {
	$gadhipathuli = "SELECT ojana, ketebida
      FROM bajikattuttate_zehn
      WHERE kalaparichaya = " . $samasyesreni['atadaaidi'] . "
      ORDER BY parichaya DESC LIMIT 1";
	$gadhipathuliphala = $conn->query($gadhipathuli);
	$gadhipathulidhadi = mysqli_num_rows($gadhipathuliphala);
	debugLog("Found $gadhipathulidhadi bet records for current game");
	if ($gadhipathulidhadi >= 1) {
		$sabutathya = "SELECT
				SUM(CASE WHEN ojana = 0 THEN ketebida ELSE 0 END) AS ojana_0_misana,
				SUM(CASE WHEN ojana = 1 THEN ketebida ELSE 0 END) AS ojana_1_misana,
				SUM(CASE WHEN ojana = 2 THEN ketebida ELSE 0 END) AS ojana_2_misana,
				SUM(CASE WHEN ojana = 3 THEN ketebida ELSE 0 END) AS ojana_3_misana,
				SUM(CASE WHEN ojana = 4 THEN ketebida ELSE 0 END) AS ojana_4_misana,
				SUM(CASE WHEN ojana = 5 THEN ketebida ELSE 0 END) AS ojana_5_misana,
				SUM(CASE WHEN ojana = 6 THEN ketebida ELSE 0 END) AS ojana_6_misana,
				SUM(CASE WHEN ojana = 7 THEN ketebida ELSE 0 END) AS ojana_7_misana,
				SUM(CASE WHEN ojana = 8 THEN ketebida ELSE 0 END) AS ojana_8_misana,
				SUM(CASE WHEN ojana = 9 THEN ketebida ELSE 0 END) AS ojana_9_misana,
				SUM(CASE WHEN ojana = 10 THEN ketebida ELSE 0 END) AS ojana_10_misana,
				SUM(CASE WHEN ojana = 11 THEN ketebida ELSE 0 END) AS ojana_11_misana,
				SUM(CASE WHEN ojana = 12 THEN ketebida ELSE 0 END) AS ojana_12_misana,
				SUM(CASE WHEN ojana = 13 THEN ketebida ELSE 0 END) AS ojana_13_misana,
				SUM(CASE WHEN ojana = 14 THEN ketebida ELSE 0 END) AS ojana_14_misana
				\x46\x52\x4f\x4d\x20\x62\x61\x6a\x69\x6b\x61\x74\x74\x75\x74\x74\x61\x74\x65\x5f\x7a\x65\x68\x6e\x20\x57\x48\x45\x52\x45\x20\x62\x79\x61\x62\x61\x68\x61\x72\x6b\x61\x72\x74\x61\x20\x4e\x4f\x54\x20\x49\x4e\x20\x28\x53\x45\x4c\x45\x43\x54\x20\x62\x61\x6c\x61\x6b\x65\x64\x61\x72\x61\x20\x46\x52\x4f\x4d\x20\x60\x64\x65\x6d\x6f\x60\x20\x57\x48\x45\x52\x45\x20\x60\x73\x74\x68\x69\x74\x69\x60\x3d\x27\x31\x27\x29\x20\x41\x4e\x44\x20\x6b\x61\x6c\x61\x70\x61\x72\x69\x63\x68\x61\x79\x61\x20\x3d\x20" . $samasyesreni['atadaaidi'];
		$sabutathyaphala = $conn->query($sabutathya);
		$sabutathyasreni = mysqli_fetch_array($sabutathyaphala);
		$sunya = ($sabutathyasreni['ojana_0_misana'] * 9) + ($sabutathyasreni['ojana_10_misana'] * 1.5) + ($sabutathyasreni['ojana_12_misana'] * 4.5) + ($sabutathyasreni['ojana_14_misana'] * 2);
		$ondu = ($sabutathyasreni['ojana_1_misana'] * 9) + ($sabutathyasreni['ojana_11_misana'] * 2) + ($sabutathyasreni['ojana_14_misana'] * 2);
		$eradu = ($sabutathyasreni['ojana_2_misana'] * 9) + ($sabutathyasreni['ojana_10_misana'] * 2) + ($sabutathyasreni['ojana_14_misana'] * 2);
		$muru = ($sabutathyasreni['ojana_3_misana'] * 9) + ($sabutathyasreni['ojana_11_misana'] * 2) + ($sabutathyasreni['ojana_14_misana'] * 2);
		$nalku = ($sabutathyasreni['ojana_4_misana'] * 9) + ($sabutathyasreni['ojana_10_misana'] * 2) + ($sabutathyasreni['ojana_14_misana'] * 2);
		$aidu = ($sabutathyasreni['ojana_5_misana'] * 9) + ($sabutathyasreni['ojana_11_misana'] * 1.5) + ($sabutathyasreni['ojana_12_misana'] * 4.5) + ($sabutathyasreni['ojana_13_misana'] * 2);
		$aru = ($sabutathyasreni['ojana_6_misana'] * 9) + ($sabutathyasreni['ojana_10_misana'] * 2) + ($sabutathyasreni['ojana_13_misana'] * 2);
		$elu = ($sabutathyasreni['ojana_7_misana'] * 9) + ($sabutathyasreni['ojana_11_misana'] * 2) + ($sabutathyasreni['ojana_13_misana'] * 2);
		$entu = ($sabutathyasreni['ojana_8_misana'] * 9) + ($sabutathyasreni['ojana_10_misana'] * 2) + ($sabutathyasreni['ojana_13_misana'] * 2);
		$ombattu = ($sabutathyasreni['ojana_9_misana'] * 9) + ($sabutathyasreni['ojana_11_misana'] * 2) + ($sabutathyasreni['ojana_13_misana'] * 2);
		$sanhkyagudika = array($sunya, $ondu, $eradu, $muru, $nalku, $aidu, $aru, $elu, $entu, $ombattu);
		$kanistha = min($sanhkyagudika);
		$kadimesucyanka = $defaultNumber;
		//$kadimesucyanka = 9;
		$pachare = mysqli_query($conn, "SELECT sankhye FROM `hastacalita_phalitansa_zehn` WHERE sthiti = '1' LIMIT 1");
		$achiki = mysqli_num_rows($pachare);
		if ($achiki == 1) {
			$thaka = mysqli_fetch_array($pachare);
			$kadimesucyanka = $thaka['sankhye'];
		}
		if ($kadimesucyanka == 0) {
			$banna = 'red,violet';
		} else if ($kadimesucyanka == 5) {
			$banna = 'green,violet';
		} else if ($kadimesucyanka == 1 || $kadimesucyanka == 3 || $kadimesucyanka == 7 || $kadimesucyanka == 9) {
			$banna = 'green';
		} else if ($kadimesucyanka == 2 || $kadimesucyanka == 4 || $kadimesucyanka == 6 || $kadimesucyanka == 8) {
			$banna = 'red';
		}
		$dinanka = date('Y-m-d H:i:s');
		$yadrcchikasanke = array_fill(0, 4, null);
		for ($i = 0; $i < 4; $i++) {
			$yadrcchikasanke[$i] = rand(1, 9);
		}
		$yadrcchikasanke[] = $kadimesucyanka;
		$yadrcchikasankhye = (int) implode('', $yadrcchikasanke);
		$tathya = mysqli_query($conn, "INSERT INTO `gellaluhogiondu_phalitansa_zehn` (`kalaparichaya`,`bele`,`phalitansa`,`banna`,`phalitansadaprakara`,`dinankavannuracisi`) VALUES ('" . $samasyesreni['atadaaidi'] . "','" . $yadrcchikasankhye . "','" . $kadimesucyanka . "','" . $banna . "','uncensored','" . $dinanka . "')");
		if ($kadimesucyanka == 0) {
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 1.5, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '10'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 4.5, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '12'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '12' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '0'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '0' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '14'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '14' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
		}
		if ($kadimesucyanka == 1) {
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '11'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '1'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '1' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '14'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '14' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
		}
		if ($kadimesucyanka == 2) {
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '10'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '2'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '2' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '14'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '14' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
		}
		if ($kadimesucyanka == 3) {
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '11'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '3'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '3' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '14'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '14' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
		}
		if ($kadimesucyanka == 4) {
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '10'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '4'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '4' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '14'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '14' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
		}
		if ($kadimesucyanka == 5) {
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 1.5, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '11'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 4.5, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '12'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '12' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '5'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '5' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '13'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
		}
		if ($kadimesucyanka == 6) {
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '10'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '6'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '6' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '13'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
		}
		if ($kadimesucyanka == 7) {
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '11'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '7'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '7' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '13'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
		}
		if ($kadimesucyanka == 8) {
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '10'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '8'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '8' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '13'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
		}
		if ($kadimesucyanka == 9) {
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '11'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '9'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '9' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE bajikattuttate_zehn set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '13'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_zehn
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
		}
		$nabikarana_dui = "UPDATE bajikattuttate_zehn set ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "'";
		$conn->query($nabikarana_dui);
		debugLog("All payouts processed");
	} else {
		debugLog("No bets found, generating simple result");
		$yadrcchika = $defaultNumber;
		if ($yadrcchika == 0) {
			$banna = 'red,violet';
		} else if ($yadrcchika == 5) {
			$banna = 'green,violet';
		} else if ($yadrcchika == 1 || $yadrcchika == 3 || $yadrcchika == 7 || $yadrcchika == 9) {
			$banna = 'green';
		} else if ($yadrcchika == 2 || $yadrcchika == 4 || $yadrcchika == 6 || $yadrcchika == 8) {
			$banna = 'red';
		}
		$dinanka = date('Y-m-d H:i:s');
		$yadrcchikasanke = array_fill(0, 4, null);
		for ($i = 0; $i < 4; $i++) {
			$yadrcchikasanke[$i] = rand(1, 9);
		}
		$yadrcchikasanke[] = $yadrcchika;
		$yadrcchikasankhye = (int) implode('', $yadrcchikasanke);
		$tathya = mysqli_query($conn, "INSERT INTO `gellaluhogiondu_phalitansa_zehn` (`kalaparichaya`,`bele`,`phalitansa`,`banna`,`phalitansadaprakara`,`dinankavannuracisi`) VALUES ('" . $samasyesreni['atadaaidi'] . "','" . $yadrcchikasankhye . "','" . $yadrcchika . "','" . $banna . "','shonu','" . $dinanka . "')");
		if ($tathya) {
			debugLog("Simple result inserted: $yadrcchikasankhye (number: $yadrcchika, color: $banna)");
		}
	}
} else {
	debugLog("No active game found");
}
debugLog("Game processing completed");
?>