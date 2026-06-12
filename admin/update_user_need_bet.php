<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['unohs'])) {
	http_response_code(401);
	echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
	exit;
}

include('conn.php');
require_once(dirname(__DIR__) . '/application/functions2.php');

if (isset($_GET['userid'])) {
	$userId = (int) $_GET['userid'];
	if ($userId <= 0) {
		echo json_encode(['ok' => false, 'message' => 'Invalid user']);
		exit;
	}

	$wagering = computeUserWithdrawalWagering($conn, $userId);
	echo json_encode([
		'ok' => true,
		'data' => [
			'amountofCode' => $wagering['amountofCode'],
			'requiredWager' => $wagering['requiredWager'],
			'totalBet' => $wagering['totalBet'],
			'totalBonus' => $wagering['totalBonus'],
			'extraFunds' => $wagering['extraFunds'],
			'depositTotal' => $wagering['depositTotal'],
		],
	]);
	exit;
}

if (isset($_POST['userid'], $_POST['need_bet'])) {
	$userId = (int) $_POST['userid'];
	$needBet = $_POST['need_bet'];

	if ($userId <= 0 || !is_numeric($needBet) || (float) $needBet < 0) {
		echo json_encode(['ok' => false, 'message' => 'Enter a valid amount (0 or greater)']);
		exit;
	}

	$result = setUserNeedToBetAmount($conn, $userId, $needBet);
	echo json_encode($result);
	exit;
}

echo json_encode(['ok' => false, 'message' => 'Invalid request']);
