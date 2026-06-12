<?php
	session_start();
	if($_SESSION['unohs'] == null){
		header("location:../index.php?msg=unauthorized");
	}
?>
<?php
include '../conn.php';

$searchedId = null;
$subordinates = [];
$teamSubordinates = [];
$totalCommission = $numberOfBetters = $totalBetAmount = 0;
$totalDeposits = $totalDepositAmount = $firstDepositors = $totalFirstDepositAmount = 0;
$teamTotalCommission = $teamNumberOfBetters = $teamTotalBetAmount = 0;
$teamTotalDeposits = $teamTotalDepositAmount = $teamFirstDepositors = $teamTotalFirstDepositAmount = 0;
$selectedDate = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = intval($_POST['user_id']);
    $searchedId = $userId;
    $selectedDate = !empty($_POST['date']) ? $_POST['date'] : null;
	$selectedLevel = isset($_POST['level']) ? $_POST['level'] : 'all'; 



    
    $codeQuery = $conn->prepare("SELECT owncode, code1, code2, code3, code4, code5 FROM shonu_subjects WHERE id = ?");
    $codeQuery->bind_param("i", $userId);
    $codeQuery->execute();
    $codeResult = $codeQuery->get_result();
    
    if ($codeRow = $codeResult->fetch_assoc()) {
        $owncode = $codeRow['owncode'];
        $teamCodes = array_filter([$codeRow['code1'], $codeRow['code2'], $codeRow['code3'], $codeRow['code4'], $codeRow['code5']]);
        
        $subQuery = $conn->prepare("SELECT id FROM shonu_subjects WHERE code = ?");
        $subQuery->bind_param("s", $owncode);
        $subQuery->execute();
        $subResult = $subQuery->get_result();
        while ($row = $subResult->fetch_assoc()) {
            $subordinates[] = $row['id'];
        }

        if (!empty($teamCodes)) {
            $teamCodesStr = implode("','", $teamCodes);
            $teamQuery = $conn->query("SELECT id FROM shonu_subjects WHERE owncode IN ('$teamCodesStr')");
            while ($row = $teamQuery->fetch_assoc()) {
                $teamSubordinates[] = $row['id'];
            }
        }

       function fetchStats($conn, $subordinateIds, $selectedDate, $selectedLevel) {
    $stats = [
        'commission' => 0, 'betters' => 0, 'betAmount' => 0,
        'deposits' => 0, 'depositAmount' => 0, 'firstDepositors' => 0, 'firstDepositAmount' => 0
    ];

    if (!empty($subordinateIds)) {
        $ids = implode(",", $subordinateIds);
        $dateCondition = $selectedDate ? " AND DATE(tiarikala) = '$selectedDate'" : "";

        $levelCondition = "";
        if ($selectedLevel !== 'all') {
            $levelCondition = "AND prakara = 'LVLCOMM" . intval($selectedLevel) . "'";
        }

        $filteredUsers = [];
        $result = $conn->query("SELECT DISTINCT balakedara FROM vyavahara WHERE balakedara IN ($ids) $dateCondition $levelCondition");
        while ($row = $result->fetch_assoc()) {
            $filteredUsers[] = $row['balakedara'];
        }

        if (!empty($filteredUsers)) {
            $filteredIds = implode(",", $filteredUsers);

         
            $result = $conn->query("SELECT SUM(ayoga) AS total FROM vyavahara WHERE balakedara IN ($filteredIds) $dateCondition $levelCondition");
            $stats['commission'] = $result->fetch_assoc()['total'] ?? 0;

         
            $betTables = [
                "bajikattuttate_aidudi", "bajikattuttate_aidudi_drei", "bajikattuttate_aidudi_funf",
                "bajikattuttate_aidudi_zehn", "bajikattuttate_drei", "bajikattuttate_funf",
                "bajikattuttate_kemuru", "bajikattuttate_kemuru_drei", "bajikattuttate_kemuru_funf",
                "bajikattuttate_kemuru_zehn", "bajikattuttate_trx", "bajikattuttate_trx3",
                "bajikattuttate_trx5", "bajikattuttate_trx10", "bajikattuttate_zehn"
            ];
            $betters = [];
            foreach ($betTables as $table) {
                $result = $conn->query("SELECT DISTINCT byabaharkarta FROM $table WHERE byabaharkarta IN ($filteredIds) $dateCondition");
                while ($row = $result->fetch_assoc()) {
                    $betters[$row['byabaharkarta']] = true;
                }
                $result = $conn->query("SELECT SUM(wettanzahl) AS total FROM $table WHERE byabaharkarta IN ($filteredIds) $dateCondition");
                $stats['betAmount'] += $result->fetch_assoc()['total'] ?? 0;
            }
            $stats['betters'] = count($betters);

            // Fetch deposit statistics
            $depositDateCondition = $selectedDate ? " AND DATE(dinankavannuracisi) = '$selectedDate'" : "";
            $result = $conn->query("SELECT COUNT(*) AS count, SUM(motta) AS total FROM thevani WHERE balakedara IN ($filteredIds) AND sthiti = 1 $depositDateCondition");
            $row = $result->fetch_assoc();
            $stats['deposits'] = $row['count'] ?? 0;
            $stats['depositAmount'] = $row['total'] ?? 0;

            $result = $conn->query("SELECT COUNT(DISTINCT balakedara) AS count, SUM(motta) AS total FROM thevani WHERE balakedara IN ($filteredIds) AND sthiti = 1 $depositDateCondition");
            $row = $result->fetch_assoc();
            $stats['firstDepositors'] = $row['count'] ?? 0;
            $stats['firstDepositAmount'] = $row['total'] ?? 0;
        }
    }
    return $stats;
}




        $subStats = fetchStats($conn, $subordinates, $selectedDate, 'all');
        $teamStats = fetchStats($conn, $teamSubordinates, $selectedDate, $selectedLevel);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="stylesheet" href="../css/mobile-responsive.css">
    <title>Subordinate Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-custom {
            background-color: rgb(14, 19, 92);
            color: white;
            width: 100%;
        }
        .btn-custom:hover {
            background-color: rgb(14, 19, 92);
        }
        .info-box {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mb-4 text-center">📊 Subordinate Data</h2>
    <form method="post">
        <div class="mb-3">
            <input type="number" class="form-control" name="user_id" placeholder="Enter User ID" required>
        </div>
		<div class="mb-3">
    <label for="level" class="form-label">Select Level:</label>
    <select class="form-control" name="level" id="level">
        <option value="all">All Levels</option>
        <option value="1">Level 1</option>
        <option value="2">Level 2</option>
        <option value="3">Level 3</option>
        <option value="4">Level 4</option>
        <option value="5">Level 5</option>
        <option value="6">Level 6</option>
    </select>
</div>

		 <div class="mb-3">
            <input type="date" class="form-control" name="date">
        </div>
        <button type="submit" class="btn btn-custom">Fetch Data</button>
    </form>

    <?php if ($searchedId !== null): ?>
        <div class="row mt-4">
			<h5>🔍 Searched User ID: <?php echo $searchedId; ?> <?php if ($selectedDate !== null): ?> And Date: <?php echo $selectedDate; ?>   <?php endif; ?></h5>
            <div class="col-md-6">
                <h4>Direct Subordinates</h4>
                <div class="info-box">💰 <strong>Commission Amount:</strong> <?= number_format($subStats['commission'], 2); ?></div>
                <div class="info-box">🎲 <strong>Number of Betters:</strong> <?= $subStats['betters']; ?></div>
                <div class="info-box">💵 <strong>Total Bet Amount:</strong> <?= number_format($subStats['betAmount'], 2); ?></div>
                <div class="info-box">📥 <strong>Number of Deposits:</strong> <?= $subStats['deposits']; ?></div>
                <div class="info-box">💳 <strong>Total Deposit Amount:</strong> <?= number_format($subStats['depositAmount'], 2); ?></div>
                <div class="info-box">🎉 <strong>First Depositors:</strong> <?= $subStats['firstDepositors']; ?></div>
                <div class="info-box">🏆 <strong>First Deposit Amount:</strong> <?= number_format($subStats['firstDepositAmount'], 2); ?></div>
            </div>

            <div class="col-md-6">
                <h4>Team Subordinates</h4>
                <div class="info-box">💰 <strong>Commission Amount:</strong> <?= number_format($teamStats['commission'], 2); ?></div>
                <div class="info-box">🎲 <strong>Number of Betters:</strong> <?= $teamStats['betters']; ?></div>
                <div class="info-box">💵 <strong>Total Bet Amount:</strong> <?= number_format($teamStats['betAmount'], 2); ?></div>
                <div class="info-box">📥 <strong>Number of Deposits:</strong> <?= $teamStats['deposits']; ?></div>
                <div class="info-box">💳 <strong>Total Deposit Amount:</strong> <?= number_format($teamStats['depositAmount'], 2); ?></div>
                <div class="info-box">🎉 <strong>First Depositors:</strong> <?= $teamStats['firstDepositors']; ?></div>
                <div class="info-box">🏆 <strong>First Deposit Amount:</strong> <?= number_format($teamStats['firstDepositAmount'], 2); ?></div>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
