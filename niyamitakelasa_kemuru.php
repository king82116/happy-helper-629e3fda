<?php
date_default_timezone_set('Asia/Kolkata');

function kemuru_wait_for_minute_start() {
	while (date('s') != '00') {
		usleep(100000);
	}
}

if (!defined('LOTTERY_CRON_SKIP_WAIT')) {
	kemuru_wait_for_minute_start();
}

include("serive/samparka.php");
if (!isset($conn) || !$conn) {
	error_log('[CRON] niyamitakelasa_kemuru.php: DB connection failed');
	exit(1);
}

try {
	include("nayakaphalitansa_mulaka_unohs_kemuru.php");
	
	$prathama = date('Ymd')."09".sprintf("%04d",1);
	$sesa = $lastperiodid=date('Ymd')."09".sprintf("%04d",1440);
	
	$ajitarika = date('Ymd');
	$ghanta = date('H');
	$nimisa = $ghanta*60;
	$bartamannimisa = date('i');
	$bartamankalasankhya = ceil(($nimisa+$bartamannimisa) / 1);
	
	$bartamankalakrama = $ajitarika ."09". sprintf("%04d", $bartamankalasankhya);
	$bartamankalakrama = $bartamankalakrama + 1;
	
	$tarika = date('Y-m-d H:i:s');
	
	$dekhakalakrama = mysqli_query($conn,"select atadaaidi from `gelluonduhogu_kemuru` order by kramasankhye desc limit 1");
	$kaladhadi = mysqli_num_rows($dekhakalakrama);
	$kalakramadhadi=mysqli_fetch_array($dekhakalakrama);
	
	if($kaladhadi == 0){
		$tathya = mysqli_query($conn,"INSERT INTO `gelluonduhogu_kemuru` (`atadaaidi`,`dinankavannuracisi`) VALUES ('".$bartamankalakrama."','".$tarika."')");
	}
	else if($prathama > $kalakramadhadi['atadaaidi']){
		$katiba=mysqli_query($conn,"TRUNCATE TABLE `gelluonduhogu_kemuru`");
		//$truncateQuery=mysqli_query($con,"TRUNCATE TABLE `abracadabra`");
		$tathya = mysqli_query($conn,"INSERT INTO `gelluonduhogu_kemuru` (`atadaaidi`,`dinankavannuracisi`) VALUES ('".$prathama."','".$tarika."')");
	}
	else{
		$parabartikrama = $kalakramadhadi['atadaaidi'] + 1;
		$tathya = mysqli_query($conn,"INSERT INTO `gelluonduhogu_kemuru` (`atadaaidi`,`dinankavannuracisi`) VALUES ('".$parabartikrama."','".$tarika."')");
	}
	
	$safa_shonu = mysqli_query($conn,"UPDATE hastacalita_phalitansa_kemeru SET sthiti='0'");
	require_once __DIR__ . '/application/functions2.php';
	trimLotteryHistoryTable($conn, 'gellaluhogiondu_kemeru_phalitansa');
} catch (Throwable $e) {
	error_log('[CRON] niyamitakelasa_kemuru.php: ' . $e->getMessage());
	exit(1);
}
?>