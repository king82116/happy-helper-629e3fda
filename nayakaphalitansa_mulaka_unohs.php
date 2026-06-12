<?php
function debugLog($message)
{
	echo "[DEBUG] " . date('Y-m-d H:i:s') . " - " . $message . "\n";
}

function fetchWingo1MinSameTrendNumber($issueNumber = null)
{
	// Fast local API first (api.joshclub.fun) — Techmazet often times out from game server.
	$apiUrl = 'https://api.joshclub.fun/wingo_1min.php';
	if ($issueNumber !== null && $issueNumber !== '') {
		$apiUrl .= (strpos($apiUrl, '?') === false ? '?' : '&') . 'issue=' . rawurlencode((string) $issueNumber);
	}

	$ch = curl_init();
	curl_setopt_array($ch, [
		CURLOPT_URL => $apiUrl,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 8,
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_USERAGENT => 'PHP Game Client',
	]);

	$response = curl_exec($ch);
	$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$curlError = curl_error($ch);
	curl_close($ch);

	if ($curlError === '' && $httpCode === 200) {
		$jsonData = json_decode((string) $response, true);
		if (json_last_error() === JSON_ERROR_NONE && isset($jsonData['winning_number'])) {
			$num = (int) $jsonData['winning_number'];
			if ($num >= 0 && $num <= 9) {
				return ['ok' => true, 'number' => $num, 'error' => '', 'http_code' => $httpCode, 'source' => 'joshclub'];
			}
		}

		$plain = (int) trim((string) $response);
		if ($plain >= 0 && $plain <= 9) {
			return ['ok' => true, 'number' => $plain, 'error' => '', 'http_code' => $httpCode, 'source' => 'joshclub'];
		}
	}

	// Do not call Techmazet here — 30s timeout blocks the whole minute cron and skips gelluonduhogu updates.
	return [
		'ok' => false,
		'number' => null,
		'error' => $curlError !== '' ? $curlError : 'Joshclub API failed or invalid response',
		'http_code' => $httpCode,
	];
}

function wingoUpsertPeriodResult($conn, $issueId, $bele, $phalitansa, $banna, $prakara = 'shonu')
{
	$issueEsc = mysqli_real_escape_string($conn, (string) $issueId);
	$bannaEsc = mysqli_real_escape_string($conn, (string) $banna);
	$prakaraEsc = mysqli_real_escape_string($conn, (string) $prakara);
	$dinanka = date('Y-m-d H:i:s');
	$dinankaEsc = mysqli_real_escape_string($conn, $dinanka);
	$sql = "INSERT INTO `gellaluhogiondu_phalitansa`
		(`kalaparichaya`,`bele`,`phalitansa`,`banna`,`phalitansadaprakara`,`dinankavannuracisi`)
		VALUES ('$issueEsc','" . (int) $bele . "','" . (int) $phalitansa . "','$bannaEsc','$prakaraEsc','$dinankaEsc')
		ON DUPLICATE KEY UPDATE
			bele = VALUES(bele),
			phalitansa = VALUES(phalitansa),
			banna = VALUES(banna),
			dinankavannuracisi = VALUES(dinankavannuracisi)";
	return mysqli_query($conn, $sql);
}

debugLog("Starting game processing...");

$defaultNumber = rand(0, 9);
$samasyesreni = [];

if (!empty($periodToSettle)) {
	$samasyesreni['atadaaidi'] = (string) $periodToSettle;
	debugLog("Settling period from cron: " . $periodToSettle);
} else {
	$samasye = 'SELECT atadaaidi FROM gelluonduhogu ORDER BY kramasankhye DESC LIMIT 1';
	$samasyephalitansa = $conn->query($samasye);
	$row = $samasyephalitansa ? mysqli_fetch_array($samasyephalitansa) : null;
	if (!empty($row['atadaaidi'])) {
		$samasyesreni['atadaaidi'] = (string) $row['atadaaidi'];
		debugLog("Settling period from DB: " . $samasyesreni['atadaaidi']);
	} else {
		$fallbackPeriod = null;
		if (function_exists('wingo1m_period_to_settle_now')) {
			$fallbackPeriod = wingo1m_period_to_settle_now();
		} else {
			$ts = time() - 60;
			$fallbackPeriod = (string) ((int) (date('Ymd', $ts) . '10001' . str_pad((string) intdiv($ts % 86400, 60), 4, '0', STR_PAD_LEFT)) + 1);
		}
		$samasyesreni['atadaaidi'] = $fallbackPeriod;
		debugLog("gelluonduhogu empty — computed period to settle: " . $fallbackPeriod);
	}
}

$checkQuery = "SELECT sankhye FROM hastacalita_phalitansa WHERE sthiti='1' LIMIT 1";
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
		$apiIssue = !empty($samasyesreni['atadaaidi']) ? $samasyesreni['atadaaidi'] : null;
		$apiFetch = fetchWingo1MinSameTrendNumber($apiIssue);
		debugLog("HTTP Code: " . ($apiFetch['http_code'] ?? 0));
		debugLog("API Error: '" . ($apiFetch['error'] ?? '') . "'");

		if (!empty($apiFetch['ok']) && $apiFetch['number'] !== null) {
			$defaultNumber = (int) $apiFetch['number'];
			debugLog("Same-trend winning_number: $defaultNumber");
		} else {
			debugLog("API call failed, using random");
			$defaultNumber = rand(0, 9);
		}
	} else {
		debugLog("API disabled, using random number");
		$defaultNumber = rand(0, 9);
	}
}

debugLog("Final defaultNumber: $defaultNumber");

if (empty($samasyesreni['atadaaidi'])) {
	debugLog("Unable to resolve period id; skipping settlement");
}

if (!empty($samasyesreni['atadaaidi'])) {
	$gadhipathuli = "SELECT ojana, ketebida
      FROM bajikattuttate
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
				FROM bajikattuttate WHERE byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND kalaparichaya = " . $samasyesreni['atadaaidi'];
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

		// Check for manual override first
		$pachare = mysqli_query($conn, "SELECT sankhye FROM `hastacalita_phalitansa` WHERE sthiti = '1' LIMIT 1");
		$achiki = mysqli_num_rows($pachare);
		if ($achiki == 1) {
			$thaka = mysqli_fetch_array($pachare);
			$kadimesucyanka = $thaka['sankhye'];
		} else {
			$kadimesucyanka = (int) $defaultNumber;
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

		if (wingoUpsertPeriodResult($conn, $samasyesreni['atadaaidi'], $yadrcchikasankhye, $kadimesucyanka, $banna, 'uncensored')) {
			debugLog("Result saved (with bets): $yadrcchikasankhye number $kadimesucyanka");
		} else {
			debugLog("Result save failed (with bets): " . mysqli_error($conn));
		}

		if ($kadimesucyanka == 0) {
			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 1.5, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '10'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 4.5, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '12'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '12' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '0'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '0' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '14'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
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
			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '11'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '1'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '1' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '14'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
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
			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '10'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '2'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '2' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '14'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
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
			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '11'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '3'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '3' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '14'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
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
			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '10'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '4'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '4' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '14'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
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
			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 1.5, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '11'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 4.5, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '12'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '12' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '5'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '5' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '13'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
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
			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '10'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '6'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '6' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '13'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
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
			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '11'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '7'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '7' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '13'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
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
			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '10'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '8'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '8' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '13'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
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
			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '11'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '9'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '9' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);

			$nabikarana = "UPDATE bajikattuttate set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' AND ojana = '13'";
			$conn->query($nabikarana);
			$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate
					WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
			$conn->query($nabikarana);
		}
		$nabikarana_dui = "UPDATE bajikattuttate set ergebnis = '" . $kadimesucyanka . "', zufallig = '" . $yadrcchikasankhye . "', tiarikala = '" . $dinanka . "' WHERE kalaparichaya = '" . $samasyesreni['atadaaidi'] . "'";
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

		if (wingoUpsertPeriodResult($conn, $samasyesreni['atadaaidi'], $yadrcchikasankhye, $yadrcchika, $banna, 'shonu')) {
			debugLog("Simple result saved: $yadrcchikasankhye (number: $yadrcchika, color: $banna)");
		} else {
			debugLog("Simple result save failed: " . mysqli_error($conn));
		}
	}
} else {
	debugLog("No active game found");
}

debugLog("Game processing completed");