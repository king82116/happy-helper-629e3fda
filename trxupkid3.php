<?php
include("serive/samparka.php");

$samasye = "SELECT dinankavannuracisi,atadaaidi FROM gelluonduhogu_trx3 ORDER BY kramasankhye DESC LIMIT 1";
$samasyephalitansa = $conn->query($samasye);
$samasyesreni = mysqli_fetch_array($samasyephalitansa);

$TRX_WINGO_GAME_TIME_SS = '54';
$apiKey = "b8c89463-e158-4726-b150-a3a2dd0907f2";

$hash = null;
$timestamp = null;
$blockNumber = null;

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://apilist.tronscanapi.com/api/block?sort=-balance&start=0&limit=20&producer=&number=&start_timestamp=&end_timestamp=",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_HTTPHEADER => [
        "TRON-PRO-API-KEY: $apiKey"
    ],
]);

$response = curl_exec($curl);
$httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ($response === false) {
    error_log('TRX Wingo (3min): Failed to contact TronScan API: ' . curl_error($curl));
}

curl_close($curl);

if ($response !== false && $httpStatus === 200) {
    $data = json_decode($response, true);

    if (json_last_error() === JSON_ERROR_NONE && isset($data['data']) && is_array($data['data'])) {
        $targetSecond = str_pad((string) $TRX_WINGO_GAME_TIME_SS, 2, '0', STR_PAD_LEFT);
        $targetBlock = null;

        foreach ($data['data'] as $item) {
            if (!isset($item['timestamp'], $item['hash'])) {
                continue;
            }

            $blockSecond = gmdate('s', (int) ($item['timestamp'] / 1000));
            if ($blockSecond === $targetSecond) {
                $targetBlock = $item;
                break;
            }
        }

        if ($targetBlock === null && isset($data['data'][0])) {
            $targetBlock = $data['data'][0];
            error_log('TRX Wingo (3min): No block matched configured second; defaulting to latest block.');
        }

        if ($targetBlock !== null) {
            $hash = $targetBlock['hash'];
            $timestamp = $targetBlock['timestamp'];
            $blockNumber = $targetBlock['number'] ?? null;
        }
    } else {
        error_log('TRX Wingo (3min): Unexpected TronScan API payload.');
    }
} else {
    error_log('TRX Wingo (3min): TronScan API responded with HTTP status ' . ($httpStatus ?? 'unknown') . '.');
}

if ($hash === null || $timestamp === null) {
    $hash = "00000000040b4803" . bin2hex(random_bytes(24));
    $timestamp = (int) (microtime(true) * 1000);
    $blockNumber = null;
}

$bt = $timestamp / 1000;
$formattedDate = date("Y-m-d H:i:s", $bt);

$kadimesucyanka = null;
for ($i = strlen($hash) - 1; $i >= 0; $i--) {
    if (is_numeric($hash[$i])) {
        $kadimesucyanka = (int)$hash[$i];
        break;
    }
}

$dinanka = $formattedDate;
/*echo '<pre>';
print_r($data); // This will print the array in a readable format
echo '</pre>';
echo $starttime;
echo $endtime; */
$yadrcchikasanke = $kadimesucyanka;
$yadrcchikasankhye = (int)implode('', (array)$yadrcchikasanke);

if ($conn) {
    $blockNumberValue = $blockNumber !== null
        ? "'" . mysqli_real_escape_string($conn, (string) $blockNumber) . "'"
        : 'NULL';

    $query = "INSERT INTO `gellaluhogiondu_trx3` 
              (`kalaparichaya`, `bele`, `phalitansa`, `banna`, `phalitansadaprakara`, `dinankavannuracisi`, `bh`, `hash`) 
              VALUES 
              ('" . mysqli_real_escape_string($conn, $samasyesreni['atadaaidi']) . "', 
               '" . $yadrcchikasankhye . "', 
               '" . $kadimesucyanka . "', 
               'fallback', 
               'uncensored', 
               '" . $formattedDate . "', 
               " . $blockNumberValue . ", 
               '" . mysqli_real_escape_string($conn, $hash) . "')";

    $tathya = mysqli_query($conn, $query);

    if ($tathya) {
        require_once __DIR__ . '/application/functions2.php';
        trimLotteryHistoryTable($conn, 'gellaluhogiondu_trx3');
        echo "Data inserted successfully!";
    } else {
        echo "Error in query execution: " . mysqli_error($conn);
    }
}

if($kadimesucyanka == 0){
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 1.5, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 4.5, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '12'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '12' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '0'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '0' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '14' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 1){
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '1'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '1' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '14' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 2){
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '2'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '2' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '14' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 3){
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '3'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '3' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '14' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 4){
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '4'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '4' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '14'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '14' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 5){
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 1.5, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 4.5, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '12'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '12' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '5'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '5' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 6){
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '6'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '6' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 7){
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '7'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '7' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 8){
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '10'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '10' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '8'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '8' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			if($kadimesucyanka == 9){
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '11'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '11' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
								
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 9, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '9'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '9' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
				
				$nabikarana = "UPDATE bajikattuttate_trx3 set phalaphala = 'gagner', sesabida = ROUND(sesabida * 2, 2), ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' AND ojana = '13'";
				$conn->query($nabikarana);
				$nabikarana = "UPDATE shonu_kaichila
				INNER JOIN (
					SELECT byabaharkarta, SUM(sesabida) AS total_paid
					FROM bajikattuttate_trx3
					WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."' 
					AND ojana = '13' 
					AND phalaphala ='gagner'
					GROUP BY byabaharkarta
				)  AS subquery ON shonu_kaichila.balakedara = subquery.byabaharkarta
				SET shonu_kaichila.motta = TRUNCATE(shonu_kaichila.motta + subquery.total_paid, 2)
				";
				$conn->query($nabikarana);
			}
			$nabikarana_dui = "UPDATE bajikattuttate_trx3 set ergebnis = '".$kadimesucyanka."', zufallig = '".$yadrcchikasankhye."', tiarikala = '".$dinanka."' WHERE kalaparichaya = '".$samasyesreni['atadaaidi']."'";
			$conn->query($nabikarana_dui);
curl_close($curl);
?>
