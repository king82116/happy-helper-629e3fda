<?php
date_default_timezone_set('Asia/Kolkata');

function aidudi_wait_for_minute_start() {
	while (date('s') != '00') {
		usleep(100000);
	}
}

if (!defined('LOTTERY_CRON_SKIP_WAIT')) {
	aidudi_wait_for_minute_start();
}

include("serive/samparka.php");
if (!isset($conn) || !$conn) {
	error_log('[CRON] niyamitakelasa_aidudi.php: DB connection failed');
	exit(1);
}

try {
	include("nayakaphalitansa_mulaka_unohs_aidudi.php");
	
	$prathama = date('Ymd')."05".sprintf("%04d",1);
	$sesa = $lastperiodid=date('Ymd')."05".sprintf("%04d",1440);
	
	$ajitarika = date('Ymd');
	$ghanta = date('H');
	$nimisa = $ghanta*60;
	$bartamannimisa = date('i');
	$bartamankalasankhya = ceil(($nimisa+$bartamannimisa) / 1);
	
	$bartamankalakrama = $ajitarika ."05". sprintf("%04d", $bartamankalasankhya);
	$bartamankalakrama = $bartamankalakrama + 1;
	
	$tarika = date('Y-m-d H:i:s');
	
	$dekhakalakrama = mysqli_query($conn,"select atadaaidi from `gelluonduhogu_aidudi` order by kramasankhye desc limit 1");
	$kaladhadi = mysqli_num_rows($dekhakalakrama);
	$kalakramadhadi=mysqli_fetch_array($dekhakalakrama);
	
	if($kaladhadi == 0){
		$tathya = mysqli_query($conn,"INSERT INTO `gelluonduhogu_aidudi` (`atadaaidi`,`dinankavannuracisi`) VALUES ('".$bartamankalakrama."','".$tarika."')");
	}
	else if($prathama > $kalakramadhadi['atadaaidi']){
		$katiba=mysqli_query($conn,"TRUNCATE TABLE `gelluonduhogu_aidudi`");
		//$truncateQuery=mysqli_query($con,"TRUNCATE TABLE `abracadabra`");
		$tathya = mysqli_query($conn,"INSERT INTO `gelluonduhogu_aidudi` (`atadaaidi`,`dinankavannuracisi`) VALUES ('".$prathama."','".$tarika."')");
	}
	else{
		$parabartikrama = $kalakramadhadi['atadaaidi'] + 1;
		$tathya = mysqli_query($conn,"INSERT INTO `gelluonduhogu_aidudi` (`atadaaidi`,`dinankavannuracisi`) VALUES ('".$parabartikrama."','".$tarika."')");
	}
	
	$safa_shonu = mysqli_query($conn,"UPDATE hastacalita_phalitansa_aidudi SET sthiti='0'");
	require_once __DIR__ . '/application/functions2.php';
	trimLotteryHistoryTable($conn, 'gellaluhogiondu_aidudi_phalitansa');
} catch (Throwable $e) {
	error_log('[CRON] niyamitakelasa_aidudi.php: ' . $e->getMessage());
	exit(1);
}
?>