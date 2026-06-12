<?php 
	function generate_jwt($headers, $payload, $secret = 'bdgshonuuncensored') {
		$headers_encoded = base64url_encode(json_encode($headers));
		
		$payload_encoded = base64url_encode(json_encode($payload));
		
		$signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);
		$signature_encoded = base64url_encode($signature);
		
		$jwt = "$headers_encoded.$payload_encoded.$signature_encoded";
		
		return $jwt;
	}
	
	function is_jwt_valid($jwt, $secret = 'bdgshonuuncensored') {
		
		$res = [
			'status' => '',
			'payload' => '',
		];

		$tokenParts = explode('.', $jwt);
		$header = base64_decode($tokenParts[0]);
		$payload = base64_decode($tokenParts[1]);
		$signature_provided = $tokenParts[2];

		$base64_url_header = base64url_encode($header);
		$base64_url_payload = base64url_encode($payload);
		$signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, $secret, true);
		$base64_url_signature = base64url_encode($signature);

		$is_signature_valid = ($base64_url_signature === $signature_provided);
		
		if (!$is_signature_valid) {
			$res['status']='Failed';
		} else {
			$res['status']='Success';
			$res['payload']=json_decode($payload, 1);
		}
		
		$allvalue = json_encode($res);
		
		return $allvalue;
	}
	
	function base64url_encode($str) {
		return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
	}

	if (!defined('LOTTERY_HISTORY_MAX_PAGES')) {
		define('LOTTERY_HISTORY_MAX_PAGES', 50);
	}

	if (!defined('LOTTERY_HISTORY_PAGE_SIZE')) {
		define('LOTTERY_HISTORY_PAGE_SIZE', 10);
	}

	function lotteryHistoryMaxRecords() {
		return LOTTERY_HISTORY_MAX_PAGES * LOTTERY_HISTORY_PAGE_SIZE;
	}

	function sanitizeLotteryHistoryTableName($tableName) {
		return preg_replace('/[^a-zA-Z0-9_]/', '', (string) $tableName);
	}

	function trimLotteryHistoryTable($conn, $tableName, $userColumn = null, $userId = null) {
		if (!$conn || !$tableName) {
			return;
		}

		$tableName = sanitizeLotteryHistoryTableName($tableName);
		if ($tableName === '') {
			return;
		}

		$maxRecords = lotteryHistoryMaxRecords();
		$orderColumn = (strpos($tableName, 'bajikattuttate') === 0) ? 'parichaya' : 'shonu';

		if ($userColumn !== null && $userId !== null) {
			$userColumn = sanitizeLotteryHistoryTableName($userColumn);
			$userId = (int) $userId;
			if ($userColumn === '') {
				return;
			}

			$sql = "DELETE FROM `$tableName`
				WHERE `$userColumn` = $userId
				AND `$orderColumn` NOT IN (
					SELECT `$orderColumn` FROM (
						SELECT `$orderColumn`
						FROM `$tableName`
						WHERE `$userColumn` = $userId
						ORDER BY `$orderColumn` DESC
						LIMIT $maxRecords
					) AS keep_rows
				)";
		} else {
			$sql = "DELETE FROM `$tableName`
				WHERE `$orderColumn` NOT IN (
					SELECT `$orderColumn` FROM (
						SELECT `$orderColumn`
						FROM `$tableName`
						ORDER BY `$orderColumn` DESC
						LIMIT $maxRecords
					) AS keep_rows
				)";
		}

		@$conn->query($sql);
	}

	function capLotteryHistoryPagination($totalCount, $pageSize = null, $pageNo = null) {
		$pageSize = max(1, (int) ($pageSize ?? LOTTERY_HISTORY_PAGE_SIZE));
		$maxRecords = lotteryHistoryMaxRecords();
		$cappedCount = min(max(0, (int) $totalCount), $maxRecords);
		$totalPage = $cappedCount > 0 ? min(LOTTERY_HISTORY_MAX_PAGES, (int) ceil($cappedCount / $pageSize)) : 0;

		$result = [
			'totalCount' => $cappedCount,
			'totalPage' => $totalPage,
		];

		if ($pageNo !== null) {
			$pageNo = max(1, (int) $pageNo);
			$result['pageNo'] = $totalPage > 0 ? min($pageNo, $totalPage) : 1;
		}

		return $result;
	}

	function getK3PhalitansaTableByTypeId($typeId) {
		$map = [
			9 => 'gellaluhogiondu_kemeru_phalitansa',
			10 => 'gellaluhogiondu_kemeru_phalitansa_drei',
			11 => 'gellaluhogiondu_kemeru_phalitansa_funf',
			12 => 'gellaluhogiondu_kemeru_phalitansa_zehn',
		];
		return $map[(int) $typeId] ?? null;
	}

	function getK3IssueTableByTypeId($typeId) {
		$map = [
			9 => 'gelluonduhogu_kemuru',
			10 => 'gelluonduhogu_kemuru_drei',
			11 => 'gelluonduhogu_kemuru_funf',
			12 => 'gelluonduhogu_kemuru_zehn',
		];
		return $map[(int) $typeId] ?? null;
	}

	function get5DPhalitansaTableByTypeId($typeId) {
		$map = [
			5 => 'gellaluhogiondu_aidudi_phalitansa',
			6 => 'gellaluhogiondu_aidudi_phalitansa_drei',
			7 => 'gellaluhogiondu_aidudi_phalitansa_funf',
			8 => 'gellaluhogiondu_aidudi_phalitansa_zehn',
		];
		return $map[(int) $typeId] ?? null;
	}

	function get5DIssueTableByTypeId($typeId) {
		$map = [
			5 => 'gelluonduhogu_aidudi',
			6 => 'gelluonduhogu_aidudi_drei',
			7 => 'gelluonduhogu_aidudi_funf',
			8 => 'gelluonduhogu_aidudi_zehn',
		];
		return $map[(int) $typeId] ?? null;
	}

	/** Wingo 1min period id for a unix timestamp (Asia/Kolkata). */
	function wingo1m_build_period_id($epochSeconds = null) {
		$epochSeconds = $epochSeconds ?? time();
		$currentDate = date('Ymd', $epochSeconds);
		$sequenceNumber = intdiv($epochSeconds % 86400, 60);
		$uniqueSequence = str_pad((string) $sequenceNumber, 4, '0', STR_PAD_LEFT);
		return (string) ((int) ($currentDate . '10001' . $uniqueSequence) + 1);
	}

	/**
	 * Real round start/end from period id so every device shows the same countdown.
	 * Works for 1/3/5 min (sequence slot × interval from midnight).
	 */
	function wingo_period_times_from_id($periodId, $intervalMinutes = 1) {
		$periodId = (string) $periodId;
		if ($periodId === '' || !ctype_digit($periodId)) {
			return null;
		}
		$base = (string) ((int) $periodId - 1);
		if (strlen($base) < 12) {
			return null;
		}
		$dateStr = substr($base, 0, 8);
		$seq = (int) substr($base, -4);
		$slotSeconds = (int) round((float) $intervalMinutes * 60);
		if ($slotSeconds < 1) {
			$slotSeconds = 60;
		}
		$startTs = strtotime($dateStr . ' 00:00:00');
		if ($startTs === false) {
			return null;
		}
		$startTs += $seq * $slotSeconds;
		$endTs = $startTs + $slotSeconds;
		return [
			'startTime' => date('Y-m-d H:i:s', $startTs),
			'endTime' => date('Y-m-d H:i:s', $endTs),
		];
	}

	/** Floor current time to the start of the active 1-minute round. */
	function wingo1m_current_round_start_time($epochSeconds = null) {
		$epochSeconds = $epochSeconds ?? time();
		return date('Y-m-d H:i:s', $epochSeconds - ($epochSeconds % 60));
	}

	/** Active round window from wall clock (countdown must use this, not stale DB timestamps). */
	function wingo_live_round_times($intervalMinutes = 1, $epochSeconds = null) {
		$epochSeconds = $epochSeconds ?? time();
		$slotSeconds = (int) round((float) $intervalMinutes * 60);
		if ($slotSeconds < 1) {
			$slotSeconds = 60;
		}
		$startTs = $epochSeconds - ($epochSeconds % $slotSeconds);
		return [
			'startTime' => date('Y-m-d H:i:s', $startTs),
			'endTime' => date('Y-m-d H:i:s', $startTs + $slotSeconds),
			'startTs' => $startTs,
			'endTs' => $startTs + $slotSeconds,
		];
	}

	/** Resolve which issue id the UI should show right now. */
	function wingo_resolve_live_period_id($dbPeriodId, $intervalMinutes = 1) {
		$clockId = wingo1m_build_period_id(time());
		if ($dbPeriodId === null || $dbPeriodId === '') {
			return $clockId;
		}
		$dbPeriodId = (string) $dbPeriodId;
		$now = time();
		$dbTimes = wingo_period_times_from_id($dbPeriodId, $intervalMinutes);
		if ($dbTimes !== null && strtotime($dbTimes['endTime']) > $now) {
			return $dbPeriodId;
		}
		if ((int) $dbPeriodId < (int) $clockId) {
			return $clockId;
		}
		return $dbPeriodId;
	}

	/** Ensure the open period row exists for the current clock minute. */
	function wingo_ensure_db_open_period($conn, $issueTable, $periodId, $intervalMinutes = 1) {
		if (!$conn || $periodId === '') {
			return;
		}
		$live = wingo_live_round_times($intervalMinutes);
		$tableEsc = mysqli_real_escape_string($conn, $issueTable);
		$idEsc = mysqli_real_escape_string($conn, (string) $periodId);
		$startEsc = mysqli_real_escape_string($conn, $live['startTime']);
		$exists = mysqli_query($conn, "SELECT 1 FROM `$tableEsc` WHERE atadaaidi = '$idEsc' LIMIT 1");
		$has = $exists && mysqli_num_rows($exists) > 0;
		if ($exists) {
			mysqli_free_result($exists);
		}
		if ($has) {
			return;
		}
		mysqli_query($conn, "INSERT INTO `$tableEsc` (`atadaaidi`,`dinankavannuracisi`) VALUES ('$idEsc','$startEsc')");
	}

	/** Saas WinGo UI (draw.ar-lottery01.com JSON) → local issue table + interval. */
	function getWingoDrawIssueConfigByGameCode($gameCode) {
		$key = preg_replace('/[^A-Z0-9]/', '', strtoupper((string) $gameCode));
		$map = [
			'WINGO1M' => ['table' => 'gelluonduhogu', 'intervalMinute' => 1],
			'WINGO3M' => ['table' => 'gelluonduhogu_drei', 'intervalMinute' => 3],
			'WINGO5M' => ['table' => 'gelluonduhogu_funf', 'intervalMinute' => 5],
			'WINGO10M' => ['table' => 'gelluonduhogu_zehn', 'intervalMinute' => 0.5],
			'WINGO30S' => ['table' => 'gelluonduhogu_zehn', 'intervalMinute' => 0.5],
			'WINGO30SEC' => ['table' => 'gelluonduhogu_zehn', 'intervalMinute' => 0.5],
			// MotoRace mirrors WinGo period tables (1:1 issueNumber + countdown).
			'MOTORACE1M' => ['table' => 'gelluonduhogu', 'intervalMinute' => 1],
			'MOTORACE3M' => ['table' => 'gelluonduhogu_drei', 'intervalMinute' => 3],
			'MOTORACE5M' => ['table' => 'gelluonduhogu_funf', 'intervalMinute' => 5],
			'MOTORACE10M' => ['table' => 'gelluonduhogu_zehn', 'intervalMinute' => 10],
		];
		return $map[$key] ?? null;
	}

	/** Build open-period payload for saas WinGo header (issueNumber must stay string). */
	function buildWingoDrawIssuePayload($conn, $issueTable, $intervalMinutes) {
		$intervalMinutes = (float) $intervalMinutes;
		if ($intervalMinutes <= 0) {
			$intervalMinutes = 1;
		}
		$slotSeconds = (int) round($intervalMinutes * 60);
		$issueEsc = mysqli_real_escape_string($conn, $issueTable);
		$row = null;
		$result = mysqli_query($conn, "SELECT atadaaidi, dinankavannuracisi FROM `$issueEsc` ORDER BY kramasankhye DESC LIMIT 1");
		if ($result) {
			$row = mysqli_fetch_assoc($result);
			mysqli_free_result($result);
		}

		$dbId = ($row && !empty($row['atadaaidi'])) ? (string) $row['atadaaidi'] : '';
		$currentId = wingo_resolve_live_period_id($dbId, $intervalMinutes);
		wingo_ensure_db_open_period($conn, $issueTable, $currentId, $intervalMinutes);

		$live = wingo_live_round_times($intervalMinutes);
		$now = time();
		// If we are exactly on the boundary, still return a positive countdown.
		if ($live['endTs'] <= $now) {
			$live = wingo_live_round_times($intervalMinutes, $now + 1);
		}

		$nextId = (string) ((int) $currentId + 1);
		$nextLive = [
			'startTime' => $live['endTime'],
			'endTime' => date('Y-m-d H:i:s', $live['endTs'] + $slotSeconds),
		];

		$makeIssue = static function ($issueNumber, $start, $end) {
			return [
				'issueNumber' => (string) $issueNumber,
				'startTime' => $start,
				'endTime' => $end,
			];
		};

		return [
			'intervalMinute' => $intervalMinutes,
			'serviceTime' => (int) round(microtime(true) * 1000),
			'current' => $makeIssue($currentId, $live['startTime'], $live['endTime']),
			'next' => $makeIssue($nextId, $nextLive['startTime'], $nextLive['endTime']),
		];
	}

	function parseK3BeleGameType($bele) {
		if (!preg_match('/^[1-6]{3}$/', (string) $bele)) {
			return null;
		}
		$digits = str_split((string) $bele);
		$d1 = (int) $digits[0];
		$d2 = (int) $digits[1];
		$d3 = (int) $digits[2];
		$allDifferent = ($d1 !== $d2 && $d1 !== $d3 && $d2 !== $d3);
		$consecutive = (max($d1, $d2, $d3) - min($d1, $d2, $d3) == 2) &&
			(abs($d1 - $d2) == 1 || abs($d1 - $d3) == 1 || abs($d2 - $d3) == 1);
		$anyTwoSame = ($d1 === $d2 || $d1 === $d3 || $d2 === $d3);
		$allSame = ($d1 === $d2 && $d2 === $d3);
		if ($allSame) {
			return 3;
		}
		if ($anyTwoSame) {
			return 2;
		}
		if ($consecutive) {
			return 1;
		}
		if ($allDifferent) {
			return 0;
		}
		return null;
	}

	function buildEmptyLotteryListData($pageNo = 1) {
		return [
			'list' => [],
			'pageNo' => max(1, (int) $pageNo),
			'totalPage' => 0,
			'totalCount' => 0,
		];
	}

	/** VIP level-up (index 0 = VIP1) and monthly reward amounts. */
	function getVipRewardConfig() {
		return [
			'levelUp' => [60, 180, 690, 1890, 6900, 16900, 69000, 169000, 690000, 1690000],
			'monthly' => [5, 20, 300, 800, 1800, 6000, 17000, 70000, 170000, 700000],
			'rebate' => [0.05, 0.05, 0.1, 0.1, 0.1, 0.15, 0.15, 0.15, 0.2, 0.3],
		];
	}

	function getVipLevelUpReward($vipLevel) {
		$cfg = getVipRewardConfig();
		$i = (int) $vipLevel - 1;
		return ($i >= 0 && $i < 10) ? $cfg['levelUp'][$i] : 0;
	}

	function getVipMonthlyReward($vipLevel) {
		$cfg = getVipRewardConfig();
		$i = (int) $vipLevel - 1;
		return ($i >= 0 && $i < 10) ? $cfg['monthly'][$i] : 0;
	}

	function getVipRebateRate($vipLevel) {
		$cfg = getVipRewardConfig();
		$i = (int) $vipLevel - 1;
		return ($i >= 0 && $i < 10) ? $cfg['rebate'][$i] : 0;
	}

	/** Total level-up + monthly bonus when jumping from $fromLevel to $toLevel (1-based). */
	function getVipUpgradeBonusSum($fromLevel, $toLevel) {
		$cfg = getVipRewardConfig();
		$total = 0;
		for ($l = (int) $fromLevel + 1; $l <= (int) $toLevel; $l++) {
			$i = $l - 1;
			if ($i >= 0 && $i < 10) {
				$total += $cfg['levelUp'][$i] + $cfg['monthly'][$i];
			}
		}
		return $total;
	}

	function trimUserBetHistoryForGameType($conn, $gameType, $userId) {
		$userId = (int) $userId;
		if (!$conn || $userId <= 0) {
			return;
		}

		$tables = [];
		if ($gameType === '1') {
			$tables = ['bajikattuttate', 'bajikattuttate_drei', 'bajikattuttate_funf', 'bajikattuttate_zehn'];
		} else if ($gameType === '13') {
			$tables = ['bajikattuttate_trx', 'bajikattuttate_trx3', 'bajikattuttate_trx5', 'bajikattuttate_trx10'];
		} else if ($gameType === '5') {
			$tables = ['bajikattuttate_aidudi', 'bajikattuttate_aidudi_drei', 'bajikattuttate_aidudi_funf', 'bajikattuttate_aidudi_zehn'];
		} else if ($gameType === '9') {
			$tables = ['bajikattuttate_kemuru', 'bajikattuttate_kemuru_drei', 'bajikattuttate_kemuru_funf', 'bajikattuttate_kemuru_zehn'];
		}

		foreach ($tables as $tableName) {
			trimLotteryHistoryTable($conn, $tableName, 'byabaharkarta', $userId);
		}
	}

	function get_bet_scope($base_scope = '1|10|100|1000') {
		$scopes = array_values(array_filter(explode('|', $base_scope), 'strlen'));

		if (!in_array('10000', $scopes, true)) {
			$scopes[] = '10000';
		}

		return implode('|', $scopes);
	}

	function fetchWithdrawalSumValue($conn, $sql) {
		$result = $conn->query($sql);
		if (!$result) {
			return 0.0;
		}

		$row = mysqli_fetch_assoc($result);
		if (!$row) {
			return 0.0;
		}

		$value = reset($row);
		return $value === null || $value === '' ? 0.0 : (float) $value;
	}

	function getUserTotalBetTurnover($conn, $userId) {
		$userId = (int) $userId;
		if ($userId <= 0) {
			return 0.0;
		}

		$betTables = [
			'bajikattuttate_trx',
			'bajikattuttate_trx3',
			'bajikattuttate_trx5',
			'bajikattuttate_trx10',
			'bajikattuttate',
			'bajikattuttate_drei',
			'bajikattuttate_funf',
			'bajikattuttate_zehn',
			'bajikattuttate_kemuru',
			'bajikattuttate_kemuru_drei',
			'bajikattuttate_kemuru_funf',
			'bajikattuttate_kemuru_zehn',
			'bajikattuttate_aidudi',
			'bajikattuttate_aidudi_drei',
			'bajikattuttate_aidudi_funf',
			'bajikattuttate_aidudi_zehn',
		];

		$totalBet = 0.0;
		foreach ($betTables as $tableName) {
			$tableName = sanitizeLotteryHistoryTableName($tableName);
			if ($tableName === '') {
				continue;
			}

			$totalBet += fetchWithdrawalSumValue(
				$conn,
				"SELECT COALESCE(SUM(ketebida), 0) AS total FROM `$tableName` WHERE byabaharkarta = $userId"
			);
		}

		return round($totalBet, 2);
	}

	function getUserDepositTurnoverBase($conn, $userId) {
		$userId = (int) $userId;
		if ($userId <= 0) {
			return [
				'depositTotal' => 0.0,
				'approvedDepositTotal' => 0.0,
				'manualCreditTotal' => 0.0,
				'requiredBase' => 0.0,
			];
		}

		$approvedDepositTotal = fetchWithdrawalSumValue(
			$conn,
			"SELECT COALESCE(SUM(motta), 0) AS total FROM thevani WHERE balakedara = $userId AND sthiti = '1'"
		);
		$manualCreditTotal = fetchWithdrawalSumValue(
			$conn,
			"SELECT COALESCE(SUM(price), 0) AS total FROM hodike_balakedara WHERE userkani = $userId"
		);

		return [
			'depositTotal' => round($approvedDepositTotal + $manualCreditTotal, 2),
			'approvedDepositTotal' => round($approvedDepositTotal, 2),
			'manualCreditTotal' => round($manualCreditTotal, 2),
			'requiredBase' => round($approvedDepositTotal + $manualCreditTotal, 2),
		];
	}

	function getUserBonusTurnoverSum($conn, $userId) {
		$userId = (int) $userId;
		if ($userId <= 0) {
			return 0.0;
		}

		$bonusSql = "SELECT (
			(SELECT COALESCE(SUM(motta), 0) FROM viprec WHERE user_id = $userId) +
			(SELECT COALESCE(SUM(
				CASE CAST(sturgis AS UNSIGNED)
					WHEN 1 THEN 60
					WHEN 2 THEN 20
					WHEN 3 THEN 150
					WHEN 4 THEN 300
					WHEN 5 THEN 600
					WHEN 6 THEN 2000
					WHEN 7 THEN 5000
					WHEN 8 THEN 10000
					ELSE 0
				END
			), 0) FROM egrahcer_sonub WHERE dr = $userId AND status = 1) +
			(SELECT COALESCE(SUM(prize), 0) FROM spinrec WHERE user_id = $userId) +
			(SELECT COALESCE(SUM(todayblessings), 0) FROM cihne WHERE identity = $userId) +
			(SELECT COALESCE(SUM(motta), 0) FROM rebetrec WHERE user_id = $userId) +
			(SELECT COALESCE(SUM(rebateAmount_Last), 0) FROM commission WHERE user_id = $userId) +
			(SELECT COALESCE(SUM(bonus), 0) FROM shonu_kaichila WHERE balakedara = $userId)
		) AS total_bonus";

		return round(fetchWithdrawalSumValue($conn, $bonusSql), 2);
	}

	function getUserExtraFundsForWagering($conn, $userId) {
		$userId = (int) $userId;
		if ($userId <= 0) {
			return 0.0;
		}

		$tableCheck = $conn->query("SHOW TABLES LIKE 'user_extra_funds'");
		if (!$tableCheck || mysqli_num_rows($tableCheck) === 0) {
			return 0.0;
		}

		return round(fetchWithdrawalSumValue(
			$conn,
			"SELECT COALESCE(SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE -amount END), 0) AS extra_amount
			FROM user_extra_funds
			WHERE userid = $userId"
		), 2);
	}

	/**
	 * Set the user's remaining need-to-bet (amountofCode) by adjusting user_extra_funds.
	 * When display is 0 because turnover is already met, a simple +/- delta on amountofCode is not enough;
	 * we solve for the extra_funds balance that yields the target remaining wager.
	 */
	function setUserNeedToBetAmount($conn, $userId, $targetAmount) {
		$userId = (int) $userId;
		$targetAmount = max(0, round((float) $targetAmount, 2));

		if ($userId <= 0) {
			return ['ok' => false, 'message' => 'Invalid user'];
		}

		$tableCheck = $conn->query("SHOW TABLES LIKE 'user_extra_funds'");
		if (!$tableCheck || mysqli_num_rows($tableCheck) === 0) {
			return ['ok' => false, 'message' => 'user_extra_funds table not found. Run admin SQL to create it.'];
		}

		$before = computeUserWithdrawalWagering($conn, $userId);
		$depositData = getUserDepositTurnoverBase($conn, $userId);
		$totalBonus = getUserBonusTurnoverSum($conn, $userId);
		$totalBet = getUserTotalBetTurnover($conn, $userId);
		$requiredBase = round($depositData['requiredBase'] + $totalBonus, 2);
		$currentExtra = getUserExtraFundsForWagering($conn, $userId);

		if ($targetAmount <= 0) {
			// Remaining need-to-bet = 0 → required wager must not exceed total bet.
			$newExtraTarget = max(0, round($totalBet - $requiredBase, 2));
		} else {
			// remaining = max(0, requiredBase + extra - totalBet) = target
			$newExtraTarget = max(0, round($totalBet + $targetAmount - $requiredBase, 2));
		}

		$delta = round($newExtraTarget - $currentExtra, 2);

		if (abs($delta) < 0.01) {
			$after = computeUserWithdrawalWagering($conn, $userId);
			if ($targetAmount > 0 && $after['amountofCode'] + 0.01 < $targetAmount) {
				return [
					'ok' => false,
					'message' => 'Cannot set need-to-bet that high: user turnover/deposit base is too low. Lower the target or add deposit first.',
					'amountofCode' => $after['amountofCode'],
				];
			}
			return [
				'ok' => true,
				'message' => 'Already at this need-to-bet level',
				'amountofCode' => $after['amountofCode'],
				'requiredWager' => $after['requiredWager'],
				'totalBet' => $after['totalBet'],
			];
		}

		$txnType = $delta > 0 ? 'credit' : 'debit';
		$amount = abs($delta);
		$sql = "INSERT INTO user_extra_funds (userid, amount, transaction_type)
			VALUES ('$userId', '" . mysqli_real_escape_string($conn, (string) $amount) . "', '$txnType')";

		if (!mysqli_query($conn, $sql)) {
			return ['ok' => false, 'message' => 'Failed to save adjustment: ' . mysqli_error($conn)];
		}

		$after = computeUserWithdrawalWagering($conn, $userId);
		if ($targetAmount > 0 && $after['amountofCode'] + 0.5 < $targetAmount) {
			return [
				'ok' => false,
				'message' => 'Saved but need-to-bet is ' . $after['amountofCode'] . ' (target ' . $targetAmount . '). User may need more deposit/turnover base.',
				'amountofCode' => $after['amountofCode'],
			];
		}

		return [
			'ok' => true,
			'message' => 'Updated',
			'amountofCode' => $after['amountofCode'],
			'requiredWager' => $after['requiredWager'],
			'totalBet' => $after['totalBet'],
			'extraFunds' => $after['extraFunds'],
			'delta' => $delta,
		];
	}

	/** After crediting a bonus, ensure need-to-bet rises by the bonus amount (1x turnover). */
	function applyBonusNeedToBet($conn, $userId, $previousAmountOfCode, $bonusAmount) {
		$userId = (int) $userId;
		$bonusAmount = round((float) $bonusAmount, 2);
		$previousAmountOfCode = max(0, round((float) $previousAmountOfCode, 2));

		if ($userId <= 0 || $bonusAmount <= 0) {
			return;
		}

		setUserNeedToBetAmount($conn, $userId, round($previousAmountOfCode + $bonusAmount, 2));
	}

	function computeUserWithdrawalWagering($conn, $userId, $walletBalance = null) {
		$userId = (int) $userId;
		$depositData = getUserDepositTurnoverBase($conn, $userId);
		$totalBonus = getUserBonusTurnoverSum($conn, $userId);
		$extraFunds = getUserExtraFundsForWagering($conn, $userId);
		$totalBet = getUserTotalBetTurnover($conn, $userId);
		$requiredWager = round($depositData['requiredBase'] + $totalBonus + $extraFunds, 2);

		if ($walletBalance === null) {
			$walletBalance = fetchWithdrawalSumValue(
				$conn,
				"SELECT COALESCE(motta, 0) AS total FROM shonu_kaichila WHERE balakedara = $userId LIMIT 1"
			);
		} else {
			$walletBalance = (float) $walletBalance;
		}

		$amountofCode = 0.0;
		$canWithdrawAmount = 0.0;

		if ($requiredWager > $totalBet) {
			$amountofCode = round($requiredWager - $totalBet, 2);
		} else if ($depositData['approvedDepositTotal'] > 0) {
			$canWithdrawAmount = round($walletBalance, 2);
		}

		return [
			'amountofCode' => $amountofCode,
			'canWithdrawAmount' => $canWithdrawAmount,
			'totalBonus' => $totalBonus,
			'extraFunds' => $extraFunds,
			'requiredWager' => $requiredWager,
			'totalBet' => $totalBet,
			'depositTotal' => $depositData['depositTotal'],
		];
	}

	if (!defined('DAILY_WITHDRAWAL_LIMIT')) {
		define('DAILY_WITHDRAWAL_LIMIT', 2);
	}
	if (!defined('WITHDRAW_CHANNEL_BANK')) {
		define('WITHDRAW_CHANNEL_BANK', 1);
	}
	if (!defined('WITHDRAW_CHANNEL_USDT')) {
		define('WITHDRAW_CHANNEL_USDT', 3);
	}

	/** Bank (1) or USDT (3); null if not a per-channel withdrawal type. */
	function normalizeWithdrawalChannelType($channelType) {
		$channelType = (int) $channelType;
		if ($channelType === WITHDRAW_CHANNEL_BANK || $channelType === WITHDRAW_CHANNEL_USDT) {
			return $channelType;
		}
		return null;
	}

	function getUserDailyWithdrawalCount($conn, $userId, $channelType = null) {
		$userId = (int) $userId;
		if ($userId <= 0) {
			return 0;
		}

		$channelFilter = '';
		$normalizedChannel = normalizeWithdrawalChannelType($channelType);
		if ($normalizedChannel !== null) {
			$channelFilter = " AND madari = $normalizedChannel";
		}

		// Rejected (sthiti=2) does not use a daily slot — user may withdraw again after admin reject.
		return (int) fetchWithdrawalSumValue(
			$conn,
			"SELECT COUNT(*) AS total
			FROM hintegedukolli
			WHERE balakedara = $userId
			AND DATE(dinankavannuracisi) = CURDATE()
			AND sthiti IN ('0', '1', '3')$channelFilter"
		);
	}

	function getUserDailyWithdrawalLimits($conn, $userId, $dailyLimit = null, $channelType = null) {
		$dailyLimit = max(0, (int) ($dailyLimit ?? DAILY_WITHDRAWAL_LIMIT));
		$usedCount = getUserDailyWithdrawalCount($conn, $userId, $channelType);
		$remainingCount = max(0, $dailyLimit - $usedCount);
		$normalizedChannel = normalizeWithdrawalChannelType($channelType);

		return [
			'dailyLimit' => $dailyLimit,
			'withdrawCount' => $usedCount,
			'withdrawRemainingCount' => $remainingCount,
			'canWithdrawToday' => $remainingCount > 0,
			'channelType' => $normalizedChannel,
		];
	}

	function getUserDailyWithdrawalLimitsByChannel($conn, $userId, $dailyLimit = null) {
		return [
			'bank' => getUserDailyWithdrawalLimits($conn, $userId, $dailyLimit, WITHDRAW_CHANNEL_BANK),
			'usdt' => getUserDailyWithdrawalLimits($conn, $userId, $dailyLimit, WITHDRAW_CHANNEL_USDT),
		];
	}

	if (!defined('USDT_MIN_WITHDRAW_INR')) {
		define('USDT_MIN_WITHDRAW_INR', 1000);
	}
	if (!defined('USDT_MAX_WITHDRAW_INR')) {
		define('USDT_MAX_WITHDRAW_INR', 100000);
	}
	if (!defined('USDT_INR_EXCHANGE_RATE')) {
		define('USDT_INR_EXCHANGE_RATE', 95);
	}

	function getWithdrawalSettingsRow($conn) {
		$defaults = [
			'fee' => 0,
			'min_price' => 110,
			'max_price' => 50000,
			'u_rate' => USDT_INR_EXCHANGE_RATE,
		];

		$tableCheck = $conn->query("SHOW TABLES LIKE 'withdrawal_settings'");
		if (!$tableCheck || mysqli_num_rows($tableCheck) === 0) {
			return $defaults;
		}

		$result = $conn->query('SELECT fee, min_price, max_price, u_rate FROM withdrawal_settings WHERE id = 1 LIMIT 1');
		if (!$result || mysqli_num_rows($result) === 0) {
			return $defaults;
		}

		$row = mysqli_fetch_assoc($result);
		return [
			'fee' => (float) ($row['fee'] ?? $defaults['fee']),
			'min_price' => (float) ($row['min_price'] ?? $defaults['min_price']),
			'max_price' => (float) ($row['max_price'] ?? $defaults['max_price']),
			'u_rate' => (float) ($row['u_rate'] ?? $defaults['u_rate']),
		];
	}

	function getStoredUsdtRateFromDatabase($conn) {
		$tableCheck = $conn->query("SHOW TABLES LIKE 'tbl_pg'");
		if ($tableCheck && mysqli_num_rows($tableCheck) > 0) {
			$result = $conn->query("SELECT rate FROM tbl_pg WHERE value = 'usdt' LIMIT 1");
			if ($result && mysqli_num_rows($result) > 0) {
				$row = mysqli_fetch_assoc($result);
				$rate = (float) $row['rate'];
				if ($rate > 0) {
					return round($rate, 2);
				}
			}
		}

		$settings = getWithdrawalSettingsRow($conn);
		return round((float) $settings['u_rate'], 2);
	}

	function fetchLiveUsdInrExchangeRate() {
		$endpoints = [
			'https://open.er-api.com/v6/latest/USD',
			'https://api.exchangerate-api.com/v4/latest/USD',
		];

		foreach ($endpoints as $url) {
			$context = stream_context_create([
				'http' => [
					'timeout' => 8,
					'header' => "User-Agent: JoshClub-Withdraw/1.0\r\n",
				],
			]);
			$body = @file_get_contents($url, false, $context);
			if ($body === false) {
				continue;
			}

			$data = json_decode($body, true);
			if (!is_array($data)) {
				continue;
			}

			if (isset($data['rates']['INR'])) {
				$rate = (float) $data['rates']['INR'];
			} elseif (isset($data['conversion_rates']['INR'])) {
				$rate = (float) $data['conversion_rates']['INR'];
			} else {
				continue;
			}

			if ($rate > 0) {
				return round($rate, 2);
			}
		}

		return 0.0;
	}

	function syncUsdInrRateToDatabase($conn, $rate) {
		$rate = round((float) $rate, 2);
		if ($rate <= 0) {
			return;
		}

		$tableCheck = $conn->query("SHOW TABLES LIKE 'tbl_pg'");
		if ($tableCheck && mysqli_num_rows($tableCheck) > 0) {
			$rateEsc = mysqli_real_escape_string($conn, (string) $rate);
			$conn->query("UPDATE tbl_pg SET rate = '$rateEsc' WHERE value = 'usdt'");
		}

		$settingsCheck = $conn->query("SHOW TABLES LIKE 'withdrawal_settings'");
		if ($settingsCheck && mysqli_num_rows($settingsCheck) > 0) {
			$rateEsc = mysqli_real_escape_string($conn, (string) $rate);
			$conn->query("UPDATE withdrawal_settings SET u_rate = '$rateEsc' WHERE id = 1");
		}
	}

	/** Fixed USDT rate: 1 USDT = 95 INR (synced to DB for recharge/withdraw UI). */
	function getUsdInrExchangeRate($conn, $syncToDatabase = true) {
		$rate = (float) USDT_INR_EXCHANGE_RATE;

		if ($syncToDatabase) {
			syncUsdInrRateToDatabase($conn, $rate);
		}

		return round($rate, 2);
	}

	/** USDT min/max in USD from fixed INR limits and USDT/INR rate. */
	function getUsdtWithdrawalUsdBounds($uRate) {
		$uRate = max(0.01, (float) $uRate);

		return [
			'usdtMinUsd' => ceil((USDT_MIN_WITHDRAW_INR / $uRate) * 100) / 100,
			'usdtMaxUsd' => floor((USDT_MAX_WITHDRAW_INR / $uRate) * 100) / 100,
		];
	}

	function getWithdrawalAmountLimits($conn, $channelType) {
		$settings = getWithdrawalSettingsRow($conn);
		$uRate = getUsdInrExchangeRate($conn);
		$fee = (float) $settings['fee'];

		if (normalizeWithdrawalChannelType($channelType) === WITHDRAW_CHANNEL_USDT) {
			$minPrice = USDT_MIN_WITHDRAW_INR;
			$maxPrice = USDT_MAX_WITHDRAW_INR;
			$usdBounds = getUsdtWithdrawalUsdBounds($uRate);
		} else {
			$minPrice = (int) $settings['min_price'];
			$maxPrice = (int) $settings['max_price'];
			$usdBounds = getUsdtWithdrawalUsdBounds($uRate);
		}

		return [
			'fee' => $fee,
			'minPrice' => (int) $minPrice,
			'maxPrice' => (int) $maxPrice,
			'uRate' => $uRate,
			'usdtMinUsd' => $usdBounds['usdtMinUsd'],
			'usdtMaxUsd' => $usdBounds['usdtMaxUsd'],
			'usdtMinInr' => USDT_MIN_WITHDRAW_INR,
			'usdtMaxInr' => USDT_MAX_WITHDRAW_INR,
		];
	}

	/**
	 * Fetch Wingo 1-min winning number for same-trend mode.
	 * Returns ['ok' => bool, 'number' => int|null, 'error' => string, 'http_code' => int]
	 */
	function fetchWingoSameTrendNumber($issueNumber = null) {
		$configPath = __DIR__ . '/api/wingo_config.php';
		if (!is_file($configPath)) {
			return ['ok' => false, 'number' => null, 'error' => 'wingo_config missing', 'http_code' => 0];
		}

		$wingoConfig = require $configPath;
		$proxyConfigPath = __DIR__ . '/api/proxy_config.php';
		$proxyConfig = is_file($proxyConfigPath) ? require $proxyConfigPath : [];

		$apiUrl = rtrim($wingoConfig['wingo_api_url'] ?? '', '/') . '/api/wingo/1min';
		if ($issueNumber !== null && $issueNumber !== '') {
			$apiUrl .= (strpos($apiUrl, '?') === false ? '?' : '&') . 'issue=' . rawurlencode((string) $issueNumber);
		}

		$apiHost = parse_url($apiUrl, PHP_URL_HOST);
		$headers = [
			'X-API-KEY: ' . ($wingoConfig['wingo_api_key'] ?? ''),
			'Content-Type: application/json',
		];
		$clientDomain = $wingoConfig['wingo_client_domain'] ?? ($proxyConfig['client_domain'] ?? '');
		if ($clientDomain !== '') {
			$clientDomain = preg_replace('#^https?://#', '', $clientDomain);
			$clientDomain = rtrim(explode(':', $clientDomain)[0], '/');
			$headers[] = 'Origin: https://' . $clientDomain;
		}

		$connectTimeout = (int) ($proxyConfig['connect_timeout'] ?? 15);
		$requestTimeout = (int) ($proxyConfig['request_timeout'] ?? 30);
		$resolveIp = trim((string) ($proxyConfig['proxy_api_resolve_ip'] ?? ''));
		$sslVerify = array_key_exists('ssl_verify', $proxyConfig) ? (bool) $proxyConfig['ssl_verify'] : true;
		$maxAttempts = 3;
		$lastError = '';
		$lastHttpCode = 0;

		for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
			$ch = curl_init();
			$curlOpts = [
				CURLOPT_URL => $apiUrl,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => $requestTimeout,
				CURLOPT_CONNECTTIMEOUT => $connectTimeout,
				CURLOPT_SSL_VERIFYPEER => $sslVerify,
				CURLOPT_SSL_VERIFYHOST => $sslVerify ? 2 : 0,
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_USERAGENT => 'PHP Game Client',
				CURLOPT_IPRESOLVE => defined('CURL_IPRESOLVE_V4') ? CURL_IPRESOLVE_V4 : 1,
			];
			if ($resolveIp !== '' && $apiHost) {
				$curlOpts[CURLOPT_RESOLVE] = ["{$apiHost}:443:{$resolveIp}"];
			}
			curl_setopt_array($ch, $curlOpts);

			$response = curl_exec($ch);
			$lastHttpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$lastError = curl_error($ch);
			curl_close($ch);

			if ($lastError !== '') {
				if ($attempt < $maxAttempts) {
					usleep(300000);
				}
				continue;
			}

			if ($lastHttpCode !== 200) {
				$lastError = 'HTTP ' . $lastHttpCode;
				if ($attempt < $maxAttempts) {
					usleep(300000);
				}
				continue;
			}

			$jsonData = json_decode((string) $response, true);
			if (json_last_error() === JSON_ERROR_NONE && isset($jsonData['winning_number'])) {
				$num = (int) $jsonData['winning_number'];
				if ($num >= 0 && $num <= 9) {
					return ['ok' => true, 'number' => $num, 'error' => '', 'http_code' => $lastHttpCode];
				}
			}

			$plain = (int) trim((string) $response);
			if ($plain >= 0 && $plain <= 9) {
				return ['ok' => true, 'number' => $plain, 'error' => '', 'http_code' => $lastHttpCode];
			}

			$lastError = 'Invalid API response';
			if ($attempt < $maxAttempts) {
				usleep(300000);
			}
		}

		return [
			'ok' => false,
			'number' => null,
			'error' => $lastError !== '' ? $lastError : 'Unknown error',
			'http_code' => $lastHttpCode,
		];
	}

	if (!defined('SAFE_DAILY_INTEREST_RATE')) {
		define('SAFE_DAILY_INTEREST_RATE', 0.01);
	}
	if (!defined('SAFE_MIN_BALANCE_FOR_INTEREST')) {
		define('SAFE_MIN_BALANCE_FOR_INTEREST', 50000);
	}
	if (!defined('SAFE_INTEREST_RECORD_TYPE')) {
		define('SAFE_INTEREST_RECORD_TYPE', 17);
	}
	if (!defined('SAFE_DEPOSIT_RECORD_TYPE')) {
		define('SAFE_DEPOSIT_RECORD_TYPE', 18);
	}
	if (!defined('SAFE_PRINCIPAL_BACK_TYPE')) {
		define('SAFE_PRINCIPAL_BACK_TYPE', 19);
	}
	if (!defined('SAFE_INTEREST_WITHDRAW_TYPE')) {
		define('SAFE_INTEREST_WITHDRAW_TYPE', 20);
	}
	if (!defined('SAFE_PRINCIPAL_LOCK_DAYS')) {
		define('SAFE_PRINCIPAL_LOCK_DAYS', 7);
	}
	if (!defined('SAFE_MIN_INTEREST_WITHDRAW')) {
		define('SAFE_MIN_INTEREST_WITHDRAW', 1000);
	}

	/**
	 * Principal deposit lots after FIFO principal withdrawals (type 19).
	 */
	function getSafePrincipalLotsAfterFifo($conn, $userId) {
		$userId = (int) $userId;
		$lots = [];

		$depResult = mysqli_query(
			$conn,
			"SELECT motta, created_at
			FROM safe_rec
			WHERE user_id = $userId
			AND type = " . SAFE_DEPOSIT_RECORD_TYPE . "
			ORDER BY created_at ASC, id ASC"
		);
		if ($depResult) {
			while ($row = mysqli_fetch_assoc($depResult)) {
				$lots[] = [
					'remaining' => (float) $row['motta'],
					'created_at' => $row['created_at'],
				];
			}
		}

		$wdResult = mysqli_query(
			$conn,
			"SELECT motta
			FROM safe_rec
			WHERE user_id = $userId
			AND type = " . SAFE_PRINCIPAL_BACK_TYPE . "
			ORDER BY created_at ASC, id ASC"
		);
		if ($wdResult) {
			while ($row = mysqli_fetch_assoc($wdResult)) {
				$toDeduct = (float) $row['motta'];
				foreach ($lots as &$lot) {
					if ($toDeduct <= 0) {
						break;
					}
					if ($lot['remaining'] <= 0) {
						continue;
					}
					$take = min($lot['remaining'], $toDeduct);
					$lot['remaining'] -= $take;
					$toDeduct -= $take;
				}
				unset($lot);
			}
		}

		return $lots;
	}

	function getSafeBalanceBreakdown($conn, $userId) {
		$userId = (int) $userId;
		$row = mysqli_fetch_assoc(mysqli_query(
			$conn,
			"SELECT safe, safeearn, safetoday FROM shonu_kaichila WHERE balakedara = $userId LIMIT 1"
		));

		$safe = (float) ($row['safe'] ?? 0);
		$lots = getSafePrincipalLotsAfterFifo($conn, $userId);
		$principalRemaining = 0.0;
		$lockedPrincipal = 0.0;
		$lockCutoff = strtotime('-' . SAFE_PRINCIPAL_LOCK_DAYS . ' days');

		foreach ($lots as $lot) {
			if ($lot['remaining'] <= 0) {
				continue;
			}
			$principalRemaining += $lot['remaining'];
			if (strtotime($lot['created_at']) > $lockCutoff) {
				$lockedPrincipal += $lot['remaining'];
			}
		}

		$principalRemaining = round($principalRemaining, 2);
		$lockedPrincipal = round($lockedPrincipal, 2);
		$withdrawablePrincipal = round(max(0, $principalRemaining - $lockedPrincipal), 2);
		$interestInSafe = round(max(0, $safe - $principalRemaining), 2);

		return [
			'safe' => round($safe, 2),
			'principal_in_safe' => $principalRemaining,
			'locked_principal' => $lockedPrincipal,
			'withdrawable_principal' => $withdrawablePrincipal,
			'interest_in_safe' => $interestInSafe,
			'withdrawable_interest' => $interestInSafe,
			'safetoday' => (float) ($row['safetoday'] ?? 0),
			'safeearn' => (float) ($row['safeearn'] ?? 0),
			'min_interest_withdraw' => SAFE_MIN_INTEREST_WITHDRAW,
			'principal_lock_days' => SAFE_PRINCIPAL_LOCK_DAYS,
		];
	}

	function classifySafeWithdrawalAmount($breakdown, $amount) {
		$amount = round((float) $amount, 2);
		$withdrawableInterest = (float) $breakdown['withdrawable_interest'];
		$withdrawablePrincipal = (float) $breakdown['withdrawable_principal'];

		if ($amount <= $withdrawableInterest + 0.001) {
			return [
				'kind' => 'interest',
				'record_type' => SAFE_INTEREST_WITHDRAW_TYPE,
			];
		}

		return [
			'kind' => 'principal',
			'record_type' => SAFE_PRINCIPAL_BACK_TYPE,
		];
	}

	function validateSafeWithdrawal($breakdown, $amount) {
		$amount = round((float) $amount, 2);

		if ($amount <= 0) {
			return ['ok' => false, 'msg' => 'Invalid withdrawal amount', 'msgCode' => 9105];
		}

		if ($amount > (float) $breakdown['safe'] + 0.001) {
			return ['ok' => false, 'msg' => 'Amount exceeds available safe balance', 'msgCode' => 9106];
		}

		$classification = classifySafeWithdrawalAmount($breakdown, $amount);

		if ($classification['kind'] === 'interest') {
			if ($amount + 0.001 < SAFE_MIN_INTEREST_WITHDRAW) {
				return [
					'ok' => false,
					'msg' => 'Minimum interest withdrawal is ' . SAFE_MIN_INTEREST_WITHDRAW,
					'msgCode' => 9101,
				];
			}
			return ['ok' => true, 'record_type' => $classification['record_type']];
		}

		if ($amount > (float) $breakdown['withdrawable_principal'] + 0.001) {
			if ((float) $breakdown['locked_principal'] > 0 && $amount <= (float) $breakdown['principal_in_safe'] + 0.001) {
				return [
					'ok' => false,
					'msg' => 'Investment can be withdrawn only after ' . SAFE_PRINCIPAL_LOCK_DAYS . ' days',
					'msgCode' => 9102,
				];
			}

			if ($amount > (float) $breakdown['withdrawable_interest'] + 0.001) {
				return [
					'ok' => false,
					'msg' => 'Withdraw interest separately (minimum ' . SAFE_MIN_INTEREST_WITHDRAW . ') before investment amount',
					'msgCode' => 9103,
				];
			}

			return [
				'ok' => false,
				'msg' => 'Amount exceeds withdrawable investment balance',
				'msgCode' => 9104,
			];
		}

		return ['ok' => true, 'record_type' => $classification['record_type']];
	}

	function isSafeBalanceEligibleForInterest($safeBalance) {
		return (float) $safeBalance >= SAFE_MIN_BALANCE_FOR_INTEREST;
	}

	function getSafeUserDayShareRate($safeBalance) {
		return isSafeBalanceEligibleForInterest($safeBalance) ? SAFE_DAILY_INTEREST_RATE : 0.0;
	}

	/** Percent value for account/wallet UI (1 = 1%, not 0.01). */
	function getSafeDayShareRatePercentForAccount($safeBalance) {
		return round(getSafeUserDayShareRate($safeBalance) * 100, 2);
	}

	/** Account page: always show product rate 1%; eligibility is separate. */
	function getSafeAccountPageInterestRatePercent() {
		return round(SAFE_DAILY_INTEREST_RATE * 100, 2);
	}

	function getSafeWealthStateData($safeBalance) {
		$safeBalance = (float) $safeBalance;
		return [
			'state' => '1',
			'shareTime' => 1,
			'dayShareRate' => getSafeAccountPageInterestRatePercent(),
			'userDayShareRate' => getSafeDayShareRatePercentForAccount($safeBalance),
			'minSafeAmount' => SAFE_MIN_BALANCE_FOR_INTEREST,
			'safeAmount' => $safeBalance,
			'isEligibleForInterest' => isSafeBalanceEligibleForInterest($safeBalance),
		];
	}

	function formatSafeRecordDayShareRateForApi($row) {
		$type = (int) ($row['type'] ?? 0);
		$rate = (float) ($row['dayShareRate'] ?? 0);
		if (in_array($type, [18, 19], true) && (float) ($row['motta'] ?? 0) < SAFE_MIN_BALANCE_FOR_INTEREST) {
			return 0.0;
		}
		return $rate;
	}

	function computeSafeEstimatedDailyRevenue($safeBalance) {
		if (!isSafeBalanceEligibleForInterest($safeBalance)) {
			return 0.0;
		}
		return round((float) $safeBalance * SAFE_DAILY_INTEREST_RATE, 2);
	}

	function hasSafeInterestPaidToday($conn, $userId) {
		$userId = (int) $userId;
		if ($userId <= 0) {
			return false;
		}

		return (int) fetchWithdrawalSumValue(
			$conn,
			"SELECT COUNT(*) AS total
			FROM safe_rec
			WHERE user_id = $userId
			AND type = " . SAFE_INTEREST_RECORD_TYPE . "
			AND DATE(created_at) = CURDATE()"
		) > 0;
	}

	function applySafeDailyInterestForUser($conn, $userId) {
		$userId = (int) $userId;
		if ($userId <= 0 || hasSafeInterestPaidToday($conn, $userId)) {
			return ['applied' => false];
		}

		$row = mysqli_fetch_assoc(mysqli_query(
			$conn,
			"SELECT safe FROM shonu_kaichila WHERE balakedara = $userId LIMIT 1"
		));
		if (!$row) {
			return ['applied' => false];
		}

		$safe = (float) $row['safe'];
		if (!isSafeBalanceEligibleForInterest($safe)) {
			return ['applied' => false, 'reason' => 'below_minimum'];
		}

		$interest = computeSafeEstimatedDailyRevenue($safe);
		if ($interest <= 0) {
			return ['applied' => false];
		}

		$now = date('Y-m-d H:i:s');
		$orderNum = date('YmdHis') . mt_rand(10000, 99999);
		$rate = SAFE_DAILY_INTEREST_RATE;
		$type = SAFE_INTEREST_RECORD_TYPE;

		$updated = mysqli_query(
			$conn,
			"UPDATE shonu_kaichila
			SET safe = ROUND(safe + $interest, 2),
				safetoday = $interest,
				safeearn = ROUND(safeearn + $interest, 2)
			WHERE balakedara = $userId"
		);

		if (!$updated) {
			return ['applied' => false, 'reason' => 'update_failed'];
		}

		mysqli_query(
			$conn,
			"INSERT INTO safe_rec (user_id, motta, type, dayShareRate, orderNum, safeEarnings, earnings, created_at)
			VALUES ('$userId', '$interest', '$type', '$rate', '$orderNum', '$interest', '$interest', '$now')"
		);

		return ['applied' => true, 'interest' => $interest];
	}

	function resetSafeTodayEarnings($conn) {
		mysqli_query($conn, "UPDATE shonu_kaichila SET safetoday = 0 WHERE safetoday > 0");
	}

	function applySafeDailyInterestForAllEligible($conn) {
		$result = mysqli_query(
			$conn,
			"SELECT balakedara FROM shonu_kaichila WHERE safe >= " . SAFE_MIN_BALANCE_FOR_INTEREST
		);
		$applied = 0;
		while ($row = mysqli_fetch_assoc($result)) {
			$outcome = applySafeDailyInterestForUser($conn, (int) $row['balakedara']);
			if (!empty($outcome['applied'])) {
				$applied++;
			}
		}
		return $applied;
	}
	
?>