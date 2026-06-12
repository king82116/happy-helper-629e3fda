<?php
session_start();
if ($_SESSION['unohs'] == null) {
    header("location:index.php?msg=unauthorized");
}
date_default_timezone_set("Asia/Karachi");

include("conn.php");

$curdate = date('Y-m-d h:i:s');

// Handle approval or rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle issue actions (approve/reject)
    if (isset($_POST['action']) && in_array($_POST['action'], ['Approve', 'Reject'])) {
        $issueId = $_POST['issue_id'] ?? null;
        $action = $_POST['action'] ?? null;

        if (!empty($issueId)) {
            $status = $action === 'Approve' ? 'Approved' : 'Rejected';
            $stmt = $conn->prepare("UPDATE issues SET status = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("si", $status, $issueId);
                if ($stmt->execute()) {
                    $message = "Issue #$issueId has been $status successfully.";
                    $messageClass = "success";
                } else {
                    $message = "Failed to update status for issue #$issueId.";
                    $messageClass = "error";
                }
                $stmt->close();
            } else {
                $message = "Failed to prepare the statement for status update.";
                $messageClass = "error";
            }
        }
    }

    // Handle clear data action
    if (isset($_POST['clear_data']) && $_POST['clear_data'] === '1') {
        $confirm = $_POST['confirm'] ?? '';
        if ($confirm === 'DELETE_ALL') {
            $stmt = $conn->prepare("TRUNCATE TABLE issues");
            if ($stmt) {
                if ($stmt->execute()) {
                    $message = "All issues data has been cleared successfully.";
                    $messageClass = "success";
                } else {
                    $message = "Failed to clear issues data.";
                    $messageClass = "error";
                }
                $stmt->close();
            } else {
                $message = "Failed to prepare the statement for clearing data.";
                $messageClass = "error";
            }
        } else {
            $message = "Confirmation text was incorrect. Data was not cleared.";
            $messageClass = "error";
        }
    }
}

// Fetch all issues
$sql = "SELECT * FROM issues ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Admin Panel | Prime Tech</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/mobile-responsive.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #2e7d32;
            /* Darker green */
            --danger-color: #c62828;
            /* Darker red */
            --warning-color: #f8961e;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7ff;
            color: var(--dark-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .header-left h1 {
            color: var(--primary-color);
            font-size: 28px;
            font-weight: 700;
        }

        .header-left p {
            color: var(--gray-color);
            font-size: 14px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .back-btn {
            background-color: var(--gray-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
            margin-right: 15px;
        }

        .back-btn:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }

        .logout-btn {
            background-color: var(--danger-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .logout-btn:hover {
            background-color: #a71f1f;
            transform: translateY(-2px);
        }

        .clear-data-btn {
            background-color: var(--warning-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .clear-data-btn:hover {
            background-color: #e07e0c;
            transform: translateY(-2px);
        }

        .message {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: var(--border-radius);
            font-weight: 500;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.success {
            background-color: rgba(46, 125, 50, 0.2);
            color: #1b5e20;
            border-left: 4px solid var(--success-color);
        }

        .message.error {
            background-color: rgba(198, 40, 40, 0.2);
            color: #b71c1c;
            border-left: 4px solid var(--danger-color);
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            font-size: 14px;
            color: var(--gray-color);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card .value {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-card.pending .value {
            color: var(--warning-color);
        }

        .stat-card.approved .value {
            color: var(--success-color);
        }

        .stat-card.rejected .value {
            color: var(--danger-color);
        }

        .stat-card i {
            font-size: 40px;
            float: right;
            opacity: 0.2;
        }

        .table-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: var(--primary-color);
            color: white;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 500;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending {
            background-color: rgba(248, 150, 30, 0.1);
            color: var(--warning-color);
        }

        .status-approved {
            background-color: rgba(46, 125, 50, 0.1);
            color: var(--success-color);
        }

        .status-rejected {
            background-color: rgba(198, 40, 40, 0.1);
            color: var(--danger-color);
        }

        .proof-links {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .proof-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 13px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .proof-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .proof-link i {
            font-size: 12px;
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            border: none;
            padding: 6px 12px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            font-size: 13px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-btn.approve {
            background-color: rgba(46, 125, 50, 0.2);
            color: var(--success-color);
        }

        .action-btn.approve:hover {
            background-color: rgba(46, 125, 50, 0.3);
        }

        .action-btn.reject {
            background-color: rgba(198, 40, 40, 0.2);
            color: var(--danger-color);
        }

        .action-btn.reject:hover {
            background-color: rgba(198, 40, 40, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--gray-color);
        }

        .empty-state i {
            font-size: 50px;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        .empty-state p {
            margin-top: 10px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 25px;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .modal-header h2 {
            color: var(--danger-color);
            font-size: 22px;
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .modal-body p {
            margin-bottom: 15px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .modal-btn {
            padding: 8px 15px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            border: none;
        }

        .modal-btn.cancel {
            background-color: var(--gray-color);
            color: white;
        }

        .modal-btn.cancel:hover {
            background-color: #5a6268;
        }

        .modal-btn.confirm {
            background-color: var(--danger-color);
            color: white;
        }

        .modal-btn.confirm:hover {
            background-color: #a71f1f;
        }

        .confirmation-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            margin-top: 10px;
        }

        .confirmation-note {
            font-size: 12px;
            color: var(--gray-color);
            margin-top: 5px;
        }

        /* Responsive styles */
        @media (max-width: 1200px) {
            .dashboard-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .dashboard-stats {
                grid-template-columns: 1fr;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <div class="header-left">
                <button class="back-btn" onclick="window.history.back()">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <h1><i class="fas fa-shield-alt"></i> Prime Tech Admin</h1>
                <p>Issue Management Dashboard</p>
            </div>
            <div class="header-right">
                <button class="clear-data-btn" id="clearDataBtn">
                    <i class="fas fa-trash-alt"></i> Clear Data
                </button>
                <button class="logout-btn" onclick="window.location.href='logout.php'">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </header>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageClass; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Dashboard Stats -->
        <div class="dashboard-stats">
            <?php
            // Count different statuses
            $totalIssues = $result ? $result->num_rows : 0;
            $pendingCount = 0;
            $approvedCount = 0;
            $rejectedCount = 0;

            if ($result) {
                $result->data_seek(0); // Reset pointer to start
                while ($row = $result->fetch_assoc()) {
                    if ($row['status'] == 'Pending')
                        $pendingCount++;
                    if ($row['status'] == 'Approved')
                        $approvedCount++;
                    if ($row['status'] == 'Rejected')
                        $rejectedCount++;
                }
                $result->data_seek(0); // Reset pointer again for main display
            }
            ?>
            <div class="stat-card">
                <h3>Total Issues</h3>
                <div class="value"><?php echo $totalIssues; ?></div>
                <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-card pending">
                <h3>Pending</h3>
                <div class="value"><?php echo $pendingCount; ?></div>
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-card approved">
                <h3>Approved</h3>
                <div class="value"><?php echo $approvedCount; ?></div>
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-card rejected">
                <h3>Rejected</h3>
                <div class="value"><?php echo $rejectedCount; ?></div>
                <i class="fas fa-times-circle"></i>
            </div>
        </div>

        <!-- Issues Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Issue Type</th>
                        <th>Account</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Proofs</th>
                        <th>Submitted On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['issue_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['account']); ?></td>
                                <td>
                                    <?php
                                    if ($row['issue_type'] === 'withdrawalProblem') {
                                        echo htmlspecialchars($row['withdrawal_amount'] ?: 'N/A');
                                    } else {
                                        echo htmlspecialchars($row['amount_deposit'] ?: 'N/A');
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="status status-<?php echo strtolower($row['status']); ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="proof-links">
                                        <?php
                                        $base_url = "https://joshgame.online/support/";
                                        if ($row['deposit_proof_path']):
                                            ?>
                                            <a href="<?php echo $base_url . htmlspecialchars($row['deposit_proof_path']); ?>"
                                                target="_blank" class="proof-link">
                                                <i class="fas fa-receipt"></i> Deposit Proof
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($row['screenshot_path']): ?>
                                            <a href="<?php echo $base_url . htmlspecialchars($row['screenshot_path']); ?>"
                                                target="_blank" class="proof-link">
                                                <i class="fas fa-image"></i> Screenshot
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($row['identification_card_path']): ?>
                                            <a href="<?php echo $base_url . htmlspecialchars($row['identification_card_path']); ?>"
                                                target="_blank" class="proof-link">
                                                <i class="fas fa-id-card"></i> ID Card
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($row['latest_deposit_proof_path']): ?>
                                            <a href="<?php echo $base_url . htmlspecialchars($row['latest_deposit_proof_path']); ?>"
                                                target="_blank" class="proof-link">
                                                <i class="fas fa-file-invoice-dollar"></i> Latest Deposit
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="issue_id"
                                                value="<?php echo htmlspecialchars($row['id']); ?>">
                                            <input type="hidden" name="action" value="Approve">
                                            <button type="submit" class="action-btn approve">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="issue_id"
                                                value="<?php echo htmlspecialchars($row['id']); ?>">
                                            <input type="hidden" name="action" value="Reject">
                                            <button type="submit" class="action-btn reject">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h3>No Issues Found</h3>
                                    <p>There are currently no issues to display.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Clear Data Confirmation Modal -->
    <div class="modal" id="clearDataModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Clear All Data</h2>
            </div>
            <div class="modal-body">
                <p>This action will permanently delete all issues from the database. This cannot be undone.</p>
                <p>To confirm, please type <strong>DELETE_ALL</strong> in the box below:</p>
                <form method="POST" id="clearDataForm">
                    <input type="hidden" name="clear_data" value="1">
                    <input type="text" name="confirm" class="confirmation-input" required>
                    <p class="confirmation-note">Type exactly "DELETE_ALL" (without quotes) to confirm</p>
                </form>
            </div>
            <div class="modal-footer">
                <button class="modal-btn cancel" id="cancelClear">Cancel</button>
                <button class="modal-btn confirm" type="submit" form="clearDataForm">Clear Data</button>
            </div>
        </div>
    </div>

    <script>
        // Add confirmation for reject actions
        document.querySelectorAll('.action-btn.reject').forEach(button => {
            button.addEventListener('click', function (e) {
                if (!confirm('Are you sure you want to reject this issue?')) {
                    e.preventDefault();
                }
            });
        });

        // Clear Data Modal functionality
        const clearDataBtn = document.getElementById('clearDataBtn');
        const clearDataModal = document.getElementById('clearDataModal');
        const cancelClear = document.getElementById('cancelClear');

        clearDataBtn.addEventListener('click', () => {
            clearDataModal.style.display = 'flex';
        });

        cancelClear.addEventListener('click', () => {
            clearDataModal.style.display = 'none';
        });

        // Close modal when clicking outside the modal content
        window.addEventListener('click', (e) => {
            if (e.target === clearDataModal) {
                clearDataModal.style.display = 'none';
            }
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>