<?php
	session_start();
	if($_SESSION['unohs'] == null){
		header("location:../index.php?msg=unauthorized");
	}
?>
<?php
include '../conn.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = intval($_POST['user_id']);
    $amountToDeduct = floatval($_POST['amount']);
    $remark = trim($_POST['remark']);

  
    $balanceQuery = $conn->prepare("SELECT motta FROM shonu_kaichila WHERE balakedara = ?");
    $balanceQuery->bind_param("i", $userId);
    $balanceQuery->execute();
    $balanceResult = $balanceQuery->get_result();
    $userBalance = $balanceResult->fetch_assoc()['motta'] ?? 0;

    if ($userBalance >= $amountToDeduct) {
       
        $deductQuery = $conn->prepare("UPDATE shonu_kaichila SET motta = motta - ? WHERE balakedara = ?");
        $deductQuery->bind_param("di", $amountToDeduct, $userId);
        $deductQuery->execute();

      
        $serial = "Imitator"; 
        $processed = 0;
        $insertQuery = $conn->prepare("INSERT INTO balance_detuct_table (userkani, price, serial, shonu, remark, processed) VALUES (?, ?, ?, NOW(), ?, ?)");
        $insertQuery->bind_param("idssi", $userId, $amountToDeduct, $serial, $remark, $processed);
        $insertQuery->execute();

        $message = "<div class='alert alert-success'>✅ Balance Deducted Successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ Insufficient Balance!</div>";
    }
}


$userBalance = 0;
if (!empty($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);
    $balanceQuery = $conn->prepare("SELECT motta FROM shonu_kaichila WHERE balakedara = ?");
    $balanceQuery->bind_param("i", $userId);
    $balanceQuery->execute();
    $balanceResult = $balanceQuery->get_result();
    $userBalance = $balanceResult->fetch_assoc()['motta'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="stylesheet" href="../css/mobile-responsive.css">
    <title>Balance Deduction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 p-4 bg-white shadow rounded">
    <h2 class="text-center">💰 Balance Deduction</h2>
    
    <?= $message; ?>
    
    <form method="get" class="mb-3">
        <div class="input-group">
            <input type="number" class="form-control" name="user_id" placeholder="Enter User ID" required>
            <button type="submit" class="btn btn-info">🔍 Fetch Balance</button>
        </div>
    </form>

    <?php if (!empty($_GET['user_id'])): ?>
        <h4>User ID: <?= $userId; ?> | Current Balance: <b><?= number_format($userBalance, 2); ?></b></h4>
        <form method="post">
            <input type="hidden" name="user_id" value="<?= $userId; ?>">
            <div class="mb-3">
                <label>Amount to Deduct:</label>
                <input type="number" class="form-control" name="amount" min="1" max="<?= $userBalance; ?>" required>
            </div>
            <div class="mb-3">
                <label>Remarks:</label>
                <input type="text" class="form-control" name="remark" required>
            </div>
            <button type="submit" class="btn btn-danger w-100">💸 Deduct Balance</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
