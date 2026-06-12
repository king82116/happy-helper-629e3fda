<?php
/**
 * Shared MotoRace helpers — dedicated result table, generation, settlement.
 * Required: $conn (mysqli) already open from conn.php; logger optional.
 *
 * Results live in `moto_race_results` (see scripts/migration-motorace-results.sql).
 * Each game_code (MotoRace_1M/3M/5M/10M) has its own periods. A period is
 * drawn (random 1..10 permutation) the moment its end_time has passed and
 * any request — history fetch, win/loss check, settlement — touches it.
 */

/** Map a gameCode like "MotoRace_1M" → ['MOTORACE1M', minutes]. */
function mr_game_minutes($gameCode) {
    $key = preg_replace('/[^A-Z0-9]/', '', strtoupper((string) $gameCode));
    $map = ['MOTORACE1M' => 1, 'MOTORACE3M' => 3, 'MOTORACE5M' => 5, 'MOTORACE10M' => 10];
    return [$key, $map[$key] ?? null];
}

/** Back-compat shim: tests/older code may still call mr_table_map. */
function mr_table_map($gameCode) {
    [$key, $min] = mr_game_minutes($gameCode);
    return [$key, $min ? 'moto_race_results' : null];
}

/**
 * Build a period id in the same WinGo/MotoRace UI format (17-digit string).
 * Must match wingo1m_build_period_id / GetWingoDrawIssue so history, bets, and
 * animations all reference the same issueNumber.
 */
function mr_issue_from_slot($slotTs, $intervalMinutes = 1) {
    $intervalMinutes = (float) $intervalMinutes;
    if ($intervalMinutes <= 0) {
        $intervalMinutes = 1;
    }
    $slotSeconds = (int) round($intervalMinutes * 60);
    if ($slotSeconds < 1) {
        $slotSeconds = 60;
    }
    $currentDate = date('Ymd', $slotTs);
    $sequenceNumber = intdiv($slotTs % 86400, $slotSeconds);
    $uniqueSequence = str_pad((string) $sequenceNumber, 4, '0', STR_PAD_LEFT);
    return (string) ((int) ($currentDate . '10001' . $uniqueSequence) + 1);
}

/** Generate a uniformly random 1..10 permutation (Fisher–Yates with random_int). */
function mr_random_ranking() {
    $ranks = range(1, 10);
    for ($i = 9; $i > 0; $i--) {
        $j = function_exists('random_int') ? random_int(0, $i) : mt_rand(0, $i);
        $tmp = $ranks[$i]; $ranks[$i] = $ranks[$j]; $ranks[$j] = $tmp;
    }
    return $ranks;
}

/** Fetch stored ranking (array of 10 ints) for a period, or null if not drawn. */
function mr_fetch_result($conn, $gameCode, $issueNumber) {
    if (!$conn) return null;
    $gEsc = mysqli_real_escape_string($conn, $gameCode);
    $iEsc = mysqli_real_escape_string($conn, $issueNumber);
    $rs = @mysqli_query($conn, "SELECT ranking FROM moto_race_results WHERE game_code='$gEsc' AND issue_number='$iEsc' LIMIT 1");
    if (!$rs) return null;
    $row = mysqli_fetch_assoc($rs);
    mysqli_free_result($rs);
    if (!$row) return null;
    $parts = array_map('intval', explode(',', $row['ranking']));
    return count($parts) === 10 ? $parts : null;
}

/** Persist a drawn ranking; uses INSERT IGNORE so concurrent draws are safe. */
function mr_store_result($conn, $gameCode, $issueNumber, $ranks, $endTime) {
    if (!$conn) return false;
    $gEsc = mysqli_real_escape_string($conn, $gameCode);
    $iEsc = mysqli_real_escape_string($conn, $issueNumber);
    $rEsc = mysqli_real_escape_string($conn, implode(',', $ranks));
    $eEsc = mysqli_real_escape_string($conn, $endTime);
    $now  = date('Y-m-d H:i:s');
    @mysqli_query($conn, "INSERT IGNORE INTO moto_race_results (game_code, issue_number, ranking, end_time, created_at) VALUES ('$gEsc', '$iEsc', '$rEsc', '$eEsc', '$now')");
    return true;
}

/**
 * Ensure every completed period for $gameCode has a result row, up to (and
 * including) the period that ended at or before $upToTs (default now).
 * Generates at most $maxBackfill periods per call to bound work.
 */
function mr_backfill_results($conn, $gameCode, $upToTs = null, $maxBackfill = 200) {
    [$key, $min] = mr_game_minutes($gameCode);
    if (!$min || !$conn) return 0;
    if ($upToTs === null) $upToTs = time();
    $periodSec = $min * 60;

    // Latest already-stored period for this game
    $gEsc = mysqli_real_escape_string($conn, $gameCode);
    $rs = @mysqli_query($conn, "SELECT issue_number, end_time FROM moto_race_results WHERE game_code='$gEsc' ORDER BY end_time DESC LIMIT 1");
    $lastEndTs = null;
    if ($rs && ($row = mysqli_fetch_assoc($rs))) {
        $lastEndTs = strtotime($row['end_time']);
        mysqli_free_result($rs);
    }

    // Latest completed slot start (slot whose endTs <= now)
    $latestSlot = intdiv($upToTs, $periodSec) - 1;
    $latestEndTs = ($latestSlot + 1) * $periodSec;

    // Start slot: just after lastEndTs, or last $maxBackfill if none yet
    if ($lastEndTs === null) {
        $startSlot = $latestSlot - ($maxBackfill - 1);
        if ($startSlot < 0) $startSlot = 0;
    } else {
        $startSlot = intdiv($lastEndTs, $periodSec); // next slot after the one that ended at lastEndTs
    }

    $generated = 0;
    for ($slot = $startSlot; $slot <= $latestSlot && $generated < $maxBackfill; $slot++) {
        $endTs = ($slot + 1) * $periodSec;
        $issue = mr_issue_from_slot($slot * $periodSec, $min);
        if (mr_fetch_result($conn, $gameCode, $issue) !== null) continue;
        $ranks = mr_random_ranking();
        mr_store_result($conn, $gameCode, $issue, $ranks, date('Y-m-d H:i:s', $endTs));
        $generated++;
    }
    return $generated;
}

/**
 * Per-number win counts for the stats panel: { "1": [1st, 2nd, 3rd], ... "10": [...] }.
 * Matches client access pattern statistics[carNo][0..2].
 */
/** Default stats panel shape: { "1": [1st,2nd,3rd counts], ... "10": [...] }. */
function mr_default_number_statistics() {
    $stats = [];
    for ($i = 1; $i <= 10; $i++) {
        $stats[(string) $i] = [0, 0, 0];
    }
    return $stats;
}

/** Coerce API/legacy statistics into per-number count arrays. */
function mr_normalize_number_statistics($raw) {
    $stats = mr_default_number_statistics();
    if (!is_array($raw)) {
        return $stats;
    }
    for ($i = 1; $i <= 10; $i++) {
        $k = (string) $i;
        if (isset($raw[$k]) && is_array($raw[$k])) {
            $stats[$k] = [
                (int) ($raw[$k][0] ?? 0),
                (int) ($raw[$k][1] ?? 0),
                (int) ($raw[$k][2] ?? 0),
            ];
        }
    }
    return $stats;
}

function mr_statistics_from_ranks($ranks) {
    $pos = ['First', 'Second', 'Third'];
    $stat = new stdClass();
    for ($i = 0; $i < 3; $i++) {
        $n = (int) ($ranks[$i] ?? 0);
        $stat->{$pos[$i] . 'OddEven'} = ($n % 2 === 0) ? 'Even' : 'Odd';
        $stat->{$pos[$i] . 'BigSmall'} = ($n >= 6) ? 'Big' : 'Small';
        $stat->{$pos[$i] . 'Num'} = (string) $n;
    }
    return $stat;
}

function mr_history_row($issueNumber, $ranks, $endTime) {
    $ranks = array_values(array_map('intval', (array) $ranks));
    return [
        'issueNumber' => (string) $issueNumber,
        'premium' => implode(',', $ranks),
        'number' => (string) ($ranks[0] ?? 0),
        'endTime' => (string) $endTime,
        'statistics' => mr_statistics_from_ranks($ranks),
    ];
}

/**
 * Race animation requires history list[0].issueNumber === live draw issue.
 * Pre-drawn live period is pinned to index 0 even if sort/end_time differs.
 */
function mr_pin_live_issue_first($conn, $gameCode, array $list) {
    if (!$conn) {
        return $list;
    }
    if (!function_exists('getWingoDrawIssueConfigByGameCode')) {
        require_once __DIR__ . '/../../functions2.php';
    }
    [$key, $min] = mr_game_minutes($gameCode);
    if (!$min) {
        return $list;
    }

    mr_ensure_current_period_result($conn, $gameCode);
    $config = getWingoDrawIssueConfigByGameCode($gameCode);
    if (!$config) {
        return $list;
    }
    $payload = buildWingoDrawIssuePayload($conn, $config['table'], $config['intervalMinute']);
    $liveIssue = (string) ($payload['current']['issueNumber'] ?? '');
    if ($liveIssue === '') {
        return $list;
    }

    $liveRow = null;
    $others = [];
    foreach ($list as $row) {
        if ((string) ($row['issueNumber'] ?? '') === $liveIssue) {
            $liveRow = $row;
        } else {
            $others[] = $row;
        }
    }

    if ($liveRow === null) {
        $ranks = mr_fetch_result($conn, $gameCode, $liveIssue);
        if ($ranks !== null) {
            $liveRow = mr_history_row(
                $liveIssue,
                $ranks,
                (string) ($payload['current']['endTime'] ?? date('Y-m-d H:i:s'))
            );
        }
    }

    return $liveRow !== null ? array_merge([$liveRow], $others) : $list;
}

function mr_build_number_statistics($conn, $gameCode, $limit = 100) {
    $stats = mr_default_number_statistics();
    if (!$conn) {
        return $stats;
    }
    $gEsc = mysqli_real_escape_string($conn, $gameCode);
    $limit = max(1, min(100, (int) $limit));
    $rs = @mysqli_query($conn,
        "SELECT ranking FROM moto_race_results WHERE game_code='$gEsc' AND CHAR_LENGTH(issue_number) >= 17 ORDER BY end_time DESC LIMIT $limit");
    if ($rs) {
        while ($row = mysqli_fetch_assoc($rs)) {
            $parts = array_map('intval', explode(',', $row['ranking']));
            for ($pos = 0; $pos < 3; $pos++) {
                if (!isset($parts[$pos])) {
                    continue;
                }
                $num = (int) $parts[$pos];
                if ($num >= 1 && $num <= 10) {
                    $stats[(string) $num][$pos]++;
                }
            }
        }
        mysqli_free_result($rs);
    }
    return $stats;
}

/**
 * Backfill completed periods AND pre-draw the live period (same issueNumber as
 * GetWingoDrawIssue) so race animation can read ranking before countdown hits 0.
 */
function mr_ensure_current_period_result($conn, $gameCode) {
    if (!$conn) {
        return false;
    }
    if (!function_exists('getWingoDrawIssueConfigByGameCode')) {
        require_once __DIR__ . '/../../functions2.php';
    }
    [$key, $min] = mr_game_minutes($gameCode);
    if (!$min) {
        return false;
    }
    mr_backfill_results($conn, $gameCode);

    $config = getWingoDrawIssueConfigByGameCode($gameCode);
    if (!$config) {
        return false;
    }
    $payload = buildWingoDrawIssuePayload($conn, $config['table'], $config['intervalMinute']);
    $issue = (string) ($payload['current']['issueNumber'] ?? '');
    if ($issue === '') {
        return false;
    }
    if (mr_fetch_result($conn, $gameCode, $issue) !== null) {
        return true;
    }
    $endTime = (string) ($payload['current']['endTime'] ?? date('Y-m-d H:i:s'));
    mr_store_result($conn, $gameCode, $issue, mr_random_ranking(), $endTime);
    return true;
}

/**
 * Decide if a single bet wins given a ranking array (1st..10th moto numbers).
 * Returns ['won' => bool, 'winAmount' => float]
 */
function mr_evaluate_bet($playType, $playBet, $betAmount, $rate, $ranks) {
    $pos = null;
    if (strpos($playType, 'First')  === 0) $pos = 0;
    elseif (strpos($playType, 'Second') === 0) $pos = 1;
    elseif (strpos($playType, 'Third')  === 0) $pos = 2;
    if ($pos === null || !isset($ranks[$pos])) return ['won' => false, 'winAmount' => 0];
    $moto = (int) $ranks[$pos];

    $won = false;
    if (substr($playType, -3) === 'Num') {
        $won = ((int)$playBet === $moto);
    } elseif (substr($playType, -7) === 'OddEven') {
        $isOdd = ($moto % 2 === 1);
        $won = ($playBet === 'Odd' ? $isOdd : ($playBet === 'Even' ? !$isOdd : false));
    } elseif (substr($playType, -8) === 'BigSmall') {
        $isBig = ($moto >= 6);
        $won = ($playBet === 'Big' ? $isBig : ($playBet === 'Small' ? !$isBig : false));
    }
    return ['won' => $won, 'winAmount' => $won ? round($betAmount * (float)$rate, 2) : 0];
}

function mr_rate_for($playType) {
    if (substr($playType, -3) === 'Num') return 9.33;
    return 2.00;
}

/**
 * Settle all pending bets for a user (optionally scoped to game_code/issue).
 * On win: credit user's shonu_kaichila.motta by winAmount. Bet amount was
 * already deducted at placement, so we only ADD win.
 * Returns number of bets settled.
 */
function mr_settle_user_pending($conn, $userId, $gameCode = null, $issueNumber = null) {
    $uid = (int) $userId;
    $where = "user_id=$uid AND status='pending'";
    if ($gameCode)    $where .= " AND game_code='" . mysqli_real_escape_string($conn, $gameCode) . "'";
    if ($issueNumber) $where .= " AND issue_number='" . mysqli_real_escape_string($conn, $issueNumber) . "'";

    // Make sure result rows exist for the games touched here.
    $games = $gameCode ? [$gameCode] : ['MotoRace_1M','MotoRace_3M','MotoRace_5M','MotoRace_10M'];
    foreach ($games as $gc) mr_backfill_results($conn, $gc);

    $rs = @mysqli_query($conn, "SELECT id, game_code, issue_number, play_type, play_bet, bet_amount, rate FROM moto_race_bets WHERE $where");
    if (!$rs) return 0;
    $count = 0;
    $totalWin = 0.0;
    while ($b = mysqli_fetch_assoc($rs)) {
        $ranks = mr_fetch_result($conn, $b['game_code'], $b['issue_number']);
        if ($ranks === null) continue; // not drawn yet
        $ev = mr_evaluate_bet($b['play_type'], $b['play_bet'], (float)$b['bet_amount'], (float)$b['rate'], $ranks);
        $status = $ev['won'] ? 'win' : 'lose';
        $win = (float) $ev['winAmount'];
        $rankStr = implode(',', $ranks);
        $id = (int) $b['id'];
        $now = date('Y-m-d H:i:s');
        $rankEsc = mysqli_real_escape_string($conn, $rankStr);
        @mysqli_query($conn,
            "UPDATE moto_race_bets SET status='$status', win_amount=$win, result_ranking='$rankEsc', settled_at='$now' WHERE id=$id AND status='pending'");
        if ($ev['won']) $totalWin += $win;
        $count++;
    }
    mysqli_free_result($rs);
    if ($totalWin > 0) {
        @mysqli_query($conn, "UPDATE shonu_kaichila SET motta = motta + $totalWin WHERE balakedara=$uid");
    }
    return $count;
}