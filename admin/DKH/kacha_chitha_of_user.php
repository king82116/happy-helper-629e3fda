<?php
include("../conn.php");

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$page_bets = isset($_GET['page_bets']) ? intval($_GET['page_bets']) : 1;
$page_deposits = isset($_GET['page_deposits']) ? intval($_GET['page_deposits']) : 1;
$page_withdrawals = isset($_GET['page_withdrawals']) ? intval($_GET['page_withdrawals']) : 1;
$records_per_page = 30;

$user_data = [];
$bet_history = [];
$deposit_history = [];
$illegal_bets = [];
$commission_earned = 0;
$user_data2 = [];
$same_ip_accounts = [];
$withdrawal_history = [];

// Fetch User Data from `shonu_kaichila`
if ($user_id > 0) {
    $query = $conn->prepare("SELECT * FROM shonu_kaichila WHERE balakedara = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();
    $user_data = $result->fetch_assoc();
}
// Fetch User Data from `shonu_kaichila`
if ($user_id > 0) {
    $query = $conn->prepare("SELECT * FROM bankcard WHERE userid = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();
    $user_data3 = $result->fetch_assoc();
}
$same_ip_accounts = []; // Ensure this variable is initialized before use

if ($user_id > 0) {
    $query = $conn->prepare("SELECT id, ip, ishonup FROM shonu_subjects WHERE id = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();
    $user_data2 = $result->fetch_assoc();

    if ($user_data2) {
        $user_ip = $user_data2['ip'] ?? null;
        $user_ishonuip = $user_data2['ishonup'] ?? null;

        // Only search if at least one of them is not null
        if (!empty($user_ip) || !empty($user_ishonuip)) {
            $query = $conn->prepare("
                SELECT id, ip, ishonup FROM shonu_subjects 
                WHERE (ip = ? OR ishonup = ?) AND id != ?
            ");
            $query->bind_param("ssi", $user_ip, $user_ishonuip, $user_id);
            $query->execute();
            $result = $query->get_result();

            while ($row = $result->fetch_assoc()) {
                $same_ip_accounts[] = $row;
            }
        }
    }
}




function getGameName($table_name) {
    $game_names = [
        "bajikattuttate_zehn" => "Wingo 30 sec",
        "bajikattuttate" => "Wingo 1 min",
        "bajikattuttate_drei" => "Wingo 3 min",
        "bajikattuttate_funf" => "Wingo 5 min",
        "bajikattuttate_aidudi" => "D5 1 min",
        "bajikattuttate_aidudi_drei" => "D5 3 min",
        "bajikattuttate_aidudi_funf" => "D5 5 min",
        "bajikattuttate_aidudi_zehn" => "D5 10 min"
    ];
    return $game_names[$table_name] ?? "Unknown Game";
}
// Fetch Upline Chain (up to 5 levels)
function getUplineChain($conn, $user_id) {
    $chain = [];
    for ($i = 0; $i < 5; $i++) {
        $query = $conn->prepare("
            SELECT id FROM shonu_subjects 
            WHERE owncode = (SELECT code FROM shonu_subjects WHERE id = ?)
        ");
        $query->bind_param("i", $user_id);
        $query->execute();
        $result = $query->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['id']; 
            $chain[] = $user_id; // Store only ID
        } else {
            break; 
        }
    }
    return $chain; // Return only ID array
}

// Fetch Illegal Bets
function checkIllegalBets($conn, $user_id) {
    $query = "
        SELECT byabaharkarta, kalaparichaya, MAX(tiarikala) AS latest_time, table_name, COUNT(DISTINCT ojana) AS bet_type_count 
        FROM (
            SELECT byabaharkarta, kalaparichaya, tiarikala, ojana, 'bajikattuttate_zehn' AS table_name FROM bajikattuttate_zehn
            UNION ALL
            SELECT byabaharkarta, kalaparichaya, tiarikala, ojana, 'bajikattuttate' FROM bajikattuttate
            UNION ALL
            SELECT byabaharkarta, kalaparichaya, tiarikala, ojana, 'bajikattuttate_drei' FROM bajikattuttate_drei
            UNION ALL
            SELECT byabaharkarta, kalaparichaya, tiarikala, ojana, 'bajikattuttate_funf' FROM bajikattuttate_funf
            UNION ALL
            SELECT byabaharkarta, kalaparichaya, tiarikala, ojana, 'bajikattuttate_aidudi' FROM bajikattuttate_aidudi
            UNION ALL
            SELECT byabaharkarta, kalaparichaya, tiarikala, ojana, 'bajikattuttate_aidudi_drei' FROM bajikattuttate_aidudi_drei
            UNION ALL
            SELECT byabaharkarta, kalaparichaya, tiarikala, ojana, 'bajikattuttate_aidudi_funf' FROM bajikattuttate_aidudi_funf
            UNION ALL
            SELECT byabaharkarta, kalaparichaya, tiarikala, ojana, 'bajikattuttate_aidudi_zehn' FROM bajikattuttate_aidudi_zehn
        ) AS merged_tables
        WHERE byabaharkarta = ?
        GROUP BY kalaparichaya, table_name, byabaharkarta
        HAVING bet_type_count > 1
        ORDER BY latest_time DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

if ($user_id > 0) {
    $result = checkIllegalBets($conn, $user_id);
    while ($row = $result->fetch_assoc()) {
        $illegal_bets[] = $row;
    }
}

// Calculate Pagination for Bet History
$start_bets = ($page_bets - 1) * $records_per_page;
$query = $conn->prepare("SELECT * FROM bajikattuttate_drei WHERE byabaharkarta = ? ORDER BY tiarikala DESC LIMIT ?, ?");
$query->bind_param("iii", $user_id, $start_bets, $records_per_page);
$query->execute();
$result = $query->get_result();
while ($row = $result->fetch_assoc()) {
    $bet_history[] = $row;
}

// Get total bet history count
$query = $conn->prepare("SELECT COUNT(*) as total FROM bajikattuttate_drei WHERE byabaharkarta = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$total_bets = $result->fetch_assoc()['total'];
$total_pages_bets = ceil($total_bets / $records_per_page);

// Calculate Pagination for Deposit History
$start_deposits = ($page_deposits - 1) * $records_per_page;
$query = $conn->prepare("SELECT * FROM thevani WHERE balakedara = ? AND sthiti = 1 ORDER BY dinankavannuracisi DESC LIMIT ?, ?");
$query->bind_param("iii", $user_id, $start_deposits, $records_per_page);
$query->execute();
$result = $query->get_result();
while ($row = $result->fetch_assoc()) {
    $deposit_history[] = $row;
}

// Calculate Pagination for Withdrawal History
$start_withdrawals = ($page_withdrawals - 1) * $records_per_page;
$query = $conn->prepare("SELECT motta, dinankavannuracisi FROM hintegedukolli WHERE balakedara = ? AND sthiti = 1 ORDER BY dinankavannuracisi DESC LIMIT ?, ?");
$query->bind_param("iii", $user_id, $start_withdrawals, $records_per_page);
$query->execute();
$result = $query->get_result();
while ($row = $result->fetch_assoc()) {
    $withdrawal_history[] = $row;
}

// Get total deposit history count
$query = $conn->prepare("SELECT COUNT(*) as total FROM thevani WHERE balakedara = ? AND sthiti = 1");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$total_deposits = $result->fetch_assoc()['total'];
$total_pages_deposits = ceil($total_deposits / $records_per_page);

// Get total withdrawal history count
$query = $conn->prepare("SELECT COUNT(*) as total FROM hintegedukolli WHERE balakedara = ? AND sthiti = 1");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$total_withdrawals = $result->fetch_assoc()['total'];
$total_pages_withdrawals = ceil($total_withdrawals / $records_per_page);

// Fetch Commission Earned from `vyavahara`
$query = $conn->prepare("SELECT SUM(ayoga) as total_commission FROM vyavahara WHERE balakedara = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$commission_earned = $result->fetch_assoc()['total_commission'] ?? 0;
$upline_chain = getUplineChain($conn, $user_id);


if ($user_id > 0) {
    // Fetch the user's own referral code
    $query = $conn->prepare("SELECT owncode FROM shonu_subjects WHERE id = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();
    $user_data = $result->fetch_assoc();
    $user_owncode = $user_data['owncode'];

    // Get deposits from direct referrals (Level 1)
    $query = $conn->prepare("
        SELECT s.id, 'Level 1' as level, t.motta, t.dinankavannuracisi 
        FROM shonu_subjects s
        JOIN thevani t ON s.id = t.balakedara
        WHERE s.code = ? AND t.sthiti = 1 AND t.dinankavannuracisi >= NOW() - INTERVAL 1 DAY
    ");
    $query->bind_param("s", $user_owncode);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $deposit_records[] = $row;
        $commission_direct += $row['motta'] * 0.05;
        $total_deposit += $row['motta']; // Sum up deposits
    }

    // Get deposits from team referrals (Level X)
    $query = $conn->prepare("
        SELECT s.id, 'Level X' as level, t.motta, t.dinankavannuracisi 
        FROM shonu_subjects s
        JOIN thevani t ON s.id = t.balakedara
        WHERE (s.code1 = ? OR s.code2 = ? OR s.code3 = ? OR s.code4 = ? OR s.code5 = ?)
        AND t.sthiti = 1 AND t.dinankavannuracisi >= NOW() - INTERVAL 1 DAY
    ");
    $query->bind_param("sssss", $user_owncode, $user_owncode, $user_owncode, $user_owncode, $user_owncode);
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $deposit_records[] = $row;
        $commission_team += $row['motta'] * 0.05;
        $total_deposit += $row['motta']; // Sum up deposits
    }

    // Total Commission
    $total_commission = $commission_direct + $commission_team;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="stylesheet" href="../css/mobile-responsive.css">
    <title>User Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5 p-4 bg-white shadow rounded">
    <h2 class="text-center">📋 User Details</h2>

    <form method="get" class="mb-3">
        <div class="input-group">
            <input type="number" class="form-control" name="user_id" placeholder="Enter User ID" required>
            <button type="submit" class="btn btn-primary">🔍 Search</button>
        </div>
    </form>

    <?php if ($user_data): ?>
        <h4>User Information</h4>
        <table class="table table-bordered">
            <tr><th>User ID</th><td><?= $user_data['balakedara']; ?></td></tr>
            <tr><th>Name</th><td><?= $user_data3['name']; ?></td></tr>
            <tr><th>Number</th><td><?= $user_data3['account']; ?></td></tr>
            <tr><th>IP</th><td><?= $user_data2['ip'] ?? 'N/A'; ?></td></tr>
            <tr><th>Recent IP</th><td><?= $user_data2['ishonup'] ?? 'N/A'; ?></td></tr>
            <tr><th>Balance</th><td>💰 <?= number_format($user_data['motta'], 2); ?></td></tr>
            <tr><th>Status</th><td><?= $user_data['status'] == 0 ? '✅ Active' : '❌ Banned'; ?></td></tr>
        </table>

        <h4>Commission Earned: 💵 <?= number_format($commission_earned, 2); ?></h4>
        <?php if (!empty($same_ip_accounts)): ?>
    <h4>📌 Accounts on the Same IP</h4>
    <table class="table table-bordered">
        <tr>
            <th>User ID</th>
            <th>IP</th>
            <th>Recent IP</th>
        </tr>
        <?php foreach ($same_ip_accounts as $account): ?>
            <tr>
                <td><?= $account['id']; ?></td>
                <td><?= $account['ip']; ?></td>
                <td><?= $account['ishonup']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <div class="alert alert-success">✅ No other accounts found on the same IP.</div>
<?php endif; ?>


        <h4>📌 Bet History</h4>
        <table class="table table-striped">
            <tr><th>Period</th><th>Amount</th><th>Date</th></tr>
            <?php foreach ($bet_history as $bet): ?>
                <tr>
                    <td><?= $bet['kalaparichaya']; ?></td>
                    <td>💰 <?= number_format($bet['ketebida'], 2); ?></td>
                    <td><?= $bet['tiarikala']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Pagination for Bet History -->
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_pages_bets; $i++): ?>
                    <li class="page-item <?= ($i == $page_bets) ? 'active' : ''; ?>">
                        <a class="page-link" href="?user_id=<?= $user_id; ?>&page_bets=<?= $i; ?>&page_deposits=<?= $page_deposits; ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
	<?php if (!empty($upline_chain)): ?>
	<h4>🆙 Upline Chain</h4>
    <h4 class="text-center">
        <?php echo "<strong>Chain:</strong> " . $user_id . " ➡️ " . implode(" ➡️ ", $upline_chain); ?>
    </h4>
<?php else: ?>
    <div class="alert alert-warning">⚠️ No uplines found.</div>
<?php endif; ?>

	
	 <h4>🚨 Illegal Bet History</h4>
        <?php if (!empty($illegal_bets)): ?>
            <table class="table table-danger">
                <tr><th>Game</th><th>Period</th><th>Bet Types</th><th>Latest Bet Time</th></tr>
                <?php foreach ($illegal_bets as $bet): ?>
                    <tr>
                        <td><?= getGameName($bet['table_name']); ?></td>
                        <td><?= $bet['kalaparichaya']; ?></td>
                        <td><?= $bet['bet_type_count']; ?></td>
                        <td><?= $bet['latest_time']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <div class="alert alert-success">✅ No illegal bets found.</div>
        <?php endif; ?>

      
        <h4>📌 Deposit & Withdrawal History</h4>
        <div class="row">
            <!-- Deposit History -->
            <div class="col-md-6">
                <h5>💰 Deposit History</h5>
                <table class="table table-striped">
                    <tr><th>Amount</th><th>Date</th></tr>
                    <?php foreach ($deposit_history as $deposit): ?>
                        <tr>
                            <td>💰 <?= number_format($deposit['motta'], 2); ?></td>
                            <td><?= $deposit['dinankavannuracisi']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <!-- Pagination for Deposits -->
                <nav>
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $total_pages_deposits; $i++): ?>
                            <li class="page-item <?= ($i == $page_deposits) ? 'active' : ''; ?>">
                                <a class="page-link" href="?user_id=<?= $user_id; ?>&page_deposits=<?= $i; ?>&page_withdrawals=<?= $page_withdrawals; ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>

            <!-- Withdrawal History -->
            <div class="col-md-6">
                <h5>💸 Withdrawal History</h5>
                <table class="table table-striped">
                    <tr><th>Amount</th><th>Date</th></tr>
                    <?php foreach ($withdrawal_history as $withdrawal): ?>
                        <tr>
                            <td>💸 <?= number_format($withdrawal['motta'], 2); ?></td>
                            <td><?= $withdrawal['dinankavannuracisi']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <!-- Pagination for Withdrawals -->
                <nav>
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $total_pages_withdrawals; $i++): ?>
                            <li class="page-item <?= ($i == $page_withdrawals) ? 'active' : ''; ?>">
                                <a class="page-link" href="?user_id=<?= $user_id; ?>&page_deposits=<?= $page_deposits; ?>&page_withdrawals=<?= $i; ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>

    <?php else: ?>
        <div class="alert alert-danger">❌ No user found with ID <?= $user_id; ?></div>
    <?php endif; ?>
    
    <h2 class="text-center">📊 Daily Salary</h2>


    <?php if ($user_id > 0): ?>
   




        <div class="row">
            <div class="col-md-6">
                <h5>🟢 Direct Referral Commission (Level 1)</h5>
                <p>Total Earned: <strong>💰 <?= number_format($commission_direct, 2); ?></strong></p>
            </div>
            <div class="col-md-6">
                <h5>🟣 Team Referral Commission (Level X)</h5>
                <p>Total Earned: <strong>💰 <?= number_format($commission_team, 2); ?></strong></p>
            </div>
        </div>
        <h4 class="mt-4">💵 Total Deposit: <strong><?= number_format($total_deposit, 2); ?></strong></h4>
        <!--<h4 class="mt-4">💵 Total Commission: <strong><?= number_format($total_commission, 2); ?></strong></h4>-->
        
    <h4>Commission Earned: 💵 <span id="commissionAmount"><?= number_format($total_commission, 2); ?></span></h4>

<label for="commissionPercentage">Set Commission %:</label>
<input type="number" id="commissionPercentage" value="5" min="0" max="100" step="0.1">
<button id="sendCommissionBtn" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; cursor: pointer; font-size: 16px; border-radius: 5px;">
    Send Commission
</button>

<script>
    let baseCommission = <?= json_encode($total_deposit); ?>; // Pass total deposit safely
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert for Popups -->

<script>
document.addEventListener("DOMContentLoaded", function () {
    let userID = <?= $user_id ?>; // Get user ID from PHP
    
    // Commission calculation on input change
    document.getElementById("commissionPercentage").addEventListener("input", function() {
        let commissionPercent = parseFloat(this.value) / 100; // Convert to decimal
        let newCommission = baseCommission * commissionPercent; // Apply percentage

        // Update the displayed commission amount
        document.getElementById("commissionAmount").innerText = newCommission.toFixed(2);
    });

    // Handle commission save on button click
    document.getElementById("sendCommissionBtn").addEventListener("click", function () {
        let commissionAmount = document.getElementById("commissionAmount").innerText.trim();
        commissionAmount = Number(commissionAmount.replace(/,/g, "")); // Convert to number

        if (isNaN(commissionAmount) || commissionAmount <= 0 || !userID) {
            Swal.fire({
                title: "Error!",
                text: "Invalid commission amount or user ID.",
                icon: "error",
                confirmButtonText: "OK"
            });
            return;
        }

        let data = new FormData();
        data.append("commission", commissionAmount);
        data.append("userID", userID);

        fetch("save_commission.php", {
            method: "POST",
            body: data
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                Swal.fire({
                    title: "Success!",
                    text: "Commission saved successfully.",
                    icon: "success",
                    confirmButtonText: "OK"
                });
            } else {
                Swal.fire({
                    title: "Error!",
                    text: result.message || "Failed to save commission.",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: "Error!",
                text: "Something went wrong.",
                icon: "error",
                confirmButtonText: "OK"
            });
            console.error("Error:", error);
        });
    });
});
</script>


        <h3 class="mt-4">📜 Deposit History (Last 24 Hours)</h3>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Level</th>
                        <th>Deposit Amount (💰)</th>
                        <th>Deposit Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($deposit_records)): ?>
                        <?php foreach ($deposit_records as $record): ?>
                            <tr>
                                <td><?= $record['id']; ?></td>
                                <td><?= $record['level']; ?></td>
                                <td><?= number_format($record['motta'], 2); ?></td>
                                <td><?= $record['dinankavannuracisi']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">🚫 No deposits found in the last 24 hours.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <div class="alert alert-danger">❌ No user found with ID <?= $user_id; ?></div>
    <?php endif; ?>
</div>
</body>
</html>
