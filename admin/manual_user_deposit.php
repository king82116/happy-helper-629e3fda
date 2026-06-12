<?php
session_start();
if ($_SESSION['unohs'] == null) {
    header('location:index.php?msg=unauthorized');
    exit;
}

include 'conn.php';
include '../application/functions2.php';

header('Content-Type: text/html; charset=utf-8');

function jsonResponse($payload, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function findUserBySearch($conn, $searchType, $searchValue)
{
    $searchValue = trim($searchValue);
    if ($searchValue === '') {
        return null;
    }

    if ($searchType === 'uid') {
        $userId = (int) $searchValue;
        if ($userId <= 0) {
            return null;
        }
        $stmt = $conn->prepare(
            'SELECT s.id, s.mobile, s.owncode, s.createdate, s.status,
                    COALESCE(k.motta, 0) AS balance
             FROM shonu_subjects s
             LEFT JOIN shonu_kaichila k ON k.balakedara = s.id
             WHERE s.id = ?
             LIMIT 1'
        );
        $stmt->bind_param('i', $userId);
    } else {
        $stmt = $conn->prepare(
            'SELECT s.id, s.mobile, s.owncode, s.createdate, s.status,
                    COALESCE(k.motta, 0) AS balance
             FROM shonu_subjects s
             LEFT JOIN shonu_kaichila k ON k.balakedara = s.id
             WHERE s.mobile = ?
             LIMIT 1'
        );
        $stmt->bind_param('s', $searchValue);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $user ?: null;
}

function attachWageringInfo($conn, array $user)
{
    $userId = (int) $user['id'];
    $balance = (float) $user['balance'];
    $wagering = computeUserWithdrawalWagering($conn, $userId, $balance);

    $user['requiredWager'] = $wagering['requiredWager'];
    $user['totalBet'] = $wagering['totalBet'];
    $user['amountofCode'] = $wagering['amountofCode'];
    $user['canWithdraw'] = $wagering['canWithdrawAmount'] > 0 ? 1 : 0;

    return $user;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $action = $_POST['ajax'] ?? '';

    if ($action === 'search') {
        $searchType = $_POST['search_type'] === 'phone' ? 'phone' : 'uid';
        $searchValue = $_POST['search_value'] ?? '';
        $user = findUserBySearch($conn, $searchType, $searchValue);

        if (!$user) {
            jsonResponse(['success' => false, 'message' => 'User not found. Check UID or phone number.'], 404);
        }

        if ((int) $user['status'] !== 1) {
            jsonResponse(['success' => false, 'message' => 'User account is not active.'], 400);
        }

        jsonResponse([
            'success' => true,
            'user' => attachWageringInfo($conn, $user),
        ]);
    }

    if ($action === 'deposit') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $amount = round((float) ($_POST['amount'] ?? 0), 2);
        $remark = trim($_POST['remark'] ?? '');
        if ($remark === '') {
            $remark = 'Admin manual deposit';
        }
        $remark = mb_substr($remark, 0, 200);

        if ($userId <= 0 || $amount <= 0) {
            jsonResponse(['success' => false, 'message' => 'Enter a valid user and amount greater than 0.'], 400);
        }

        $user = findUserBySearch($conn, 'uid', (string) $userId);
        if (!$user) {
            jsonResponse(['success' => false, 'message' => 'User not found.'], 404);
        }

        $serial = 'ADM' . date('YmdHis') . rand(1000, 9999);
        $depositTime = date('Y-m-d H:i:s');
        $mobile = (string) $user['mobile'];
        $mula = 'Admin Panel';
        $ullekha = $remark;
        $madari = '1004';

        mysqli_begin_transaction($conn);

        try {
            $updateStmt = $conn->prepare(
                'UPDATE shonu_kaichila SET motta = motta + ? WHERE balakedara = ?'
            );
            if (!$updateStmt) {
                throw new Exception('Could not prepare balance update.');
            }
            $updateStmt->bind_param('di', $amount, $userId);
            if (!$updateStmt->execute() || $updateStmt->affected_rows < 1) {
                $updateStmt->close();
                throw new Exception('User wallet not found or balance not updated.');
            }
            $updateStmt->close();

            $depositStmt = $conn->prepare(
                'INSERT INTO thevani (balakedara, motta, dharavahi, mula, ullekha, duravani, dinankavannuracisi, madari, sthiti)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            if (!$depositStmt) {
                throw new Exception('Could not prepare deposit record.');
            }
            $sthiti = '1';
            $depositStmt->bind_param(
                'idsssssss',
                $userId,
                $amount,
                $serial,
                $mula,
                $ullekha,
                $mobile,
                $depositTime,
                $madari,
                $sthiti
            );
            if (!$depositStmt->execute()) {
                $depositStmt->close();
                throw new Exception('Failed to save deposit record.');
            }
            $depositStmt->close();

            mysqli_commit($conn);

            $newBalance = (float) $user['balance'] + $amount;
            $wagering = computeUserWithdrawalWagering($conn, $userId, $newBalance);

            jsonResponse([
                'success' => true,
                'message' => 'Deposit successful. Amount counts toward need-to-bet requirement.',
                'serial' => $serial,
                'newBalance' => round($newBalance, 2),
                'wagering' => [
                    'requiredWager' => $wagering['requiredWager'],
                    'totalBet' => $wagering['totalBet'],
                    'amountofCode' => $wagering['amountofCode'],
                ],
            ]);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    jsonResponse(['success' => false, 'message' => 'Invalid action.'], 400);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Manual User Deposit</title>
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/feather/feather.css">
    <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="images/favicon.png" />
    <style>
        .cool-input {
            border: 2px solid rgb(14, 19, 92);
            border-radius: 0.25rem;
            padding: 0.5rem 1rem;
        }
        .user-card {
            display: none;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.25rem;
            background: #f8fafc;
        }
        .user-card.show { display: block; }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            margin-top: 12px;
        }
        .stat-box {
            background: #fff;
            border-radius: 6px;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
        }
        .stat-box span { display: block; font-size: 12px; color: #6b7280; }
        .stat-box strong { font-size: 16px; color: #111827; }
        .alert-inline {
            display: none;
            margin-top: 12px;
        }
        .alert-inline.show { display: block; }
    </style>
</head>
<body>
<div class="container-scroller">
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
            <a class="navbar-brand brand-logo" href="dashboard.php"><img src="images/logo.png" alt="logo"/></a>
            <a class="navbar-brand brand-logo-mini" href="dashboard.php"><img src="images/logo-mini.png" alt="logo"/></a>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
            <ul class="navbar-nav navbar-nav-right">
                <li class="nav-item dropdown d-flex mr-4">
                    <a class="nav-link" href="logout.php"><i class="icon-inbox"></i> Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container-fluid page-body-wrapper">
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
            <div class="user-profile">
                <div class="user-image"><img src="images/faces/face28.png" alt=""></div>
                <div class="user-name">Prime Tech</div>
                <div class="user-designation">Admin</div>
            </div>
            <?php include 'compass.php'; ?>
        </nav>
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="row">
                    <div class="col-12">
                        <h4 class="font-weight-bold text-dark">Manual User Deposit</h4>
                        <p class="text-muted mb-0">Search by UID or phone, then add balance. Deposits count toward the user&apos;s <strong>need to bet</strong> withdrawal rule.</p>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Search user</h5>
                                <div class="form-group">
                                    <label>Search by</label>
                                    <div class="d-flex">
                                        <div class="form-check mr-3">
                                            <input class="form-check-input" type="radio" name="search_type" id="searchUid" value="uid" checked>
                                            <label class="form-check-label" for="searchUid">UID</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="search_type" id="searchPhone" value="phone">
                                            <label class="form-check-label" for="searchPhone">Phone</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="search_value">UID or phone number</label>
                                    <input type="text" id="search_value" class="form-control cool-input" placeholder="e.g. 352082 or 919871556394">
                                </div>
                                <button type="button" id="btnSearch" class="btn btn-primary">Search</button>
                                <div id="searchAlert" class="alert alert-danger alert-inline"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div id="userCard" class="user-card">
                            <h5 class="mb-2">User details</h5>
                            <div class="stat-grid">
                                <div class="stat-box"><span>UID</span><strong id="uId">-</strong></div>
                                <div class="stat-box"><span>Phone</span><strong id="uPhone">-</strong></div>
                                <div class="stat-box"><span>Balance</span><strong id="uBalance">-</strong></div>
                                <div class="stat-box"><span>Need to bet</span><strong id="uNeedBet">-</strong></div>
                                <div class="stat-box"><span>Total bet</span><strong id="uTotalBet">-</strong></div>
                                <div class="stat-box"><span>Required wager</span><strong id="uRequired">-</strong></div>
                            </div>

                            <hr>
                            <h5 class="mb-3">Add deposit</h5>
                            <form id="depositForm">
                                <input type="hidden" id="deposit_user_id" name="user_id" value="">
                                <div class="form-group">
                                    <label for="amount">Amount (₹)</label>
                                    <input type="number" step="0.01" min="0.01" id="amount" class="form-control cool-input" required placeholder="Enter amount">
                                </div>
                                <div class="form-group">
                                    <label for="remark">Remark (optional)</label>
                                    <input type="text" id="remark" class="form-control cool-input" maxlength="200" placeholder="Admin manual deposit">
                                </div>
                                <button type="submit" class="btn btn-success">Deposit to wallet</button>
                            </form>
                            <div id="depositAlert" class="alert alert-inline mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="vendors/base/vendor.bundle.base.js"></script>
<script src="js/off-canvas.js"></script>
<script src="js/hoverable-collapse.js"></script>
<script src="js/template.js"></script>
<script>
(function () {
    const btnSearch = document.getElementById('btnSearch');
    const searchAlert = document.getElementById('searchAlert');
    const depositAlert = document.getElementById('depositAlert');
    const userCard = document.getElementById('userCard');
    const depositForm = document.getElementById('depositForm');

    function showAlert(el, message, type) {
        el.className = 'alert alert-' + type + ' alert-inline show';
        el.textContent = message;
    }

    function hideAlert(el) {
        el.className = 'alert alert-inline';
        el.textContent = '';
    }

    function formatMoney(value) {
        return '₹' + Number(value || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function getSearchType() {
        return document.querySelector('input[name="search_type"]:checked').value;
    }

    btnSearch.addEventListener('click', function () {
        hideAlert(searchAlert);
        hideAlert(depositAlert);
        const formData = new FormData();
        formData.append('ajax', 'search');
        formData.append('search_type', getSearchType());
        formData.append('search_value', document.getElementById('search_value').value.trim());

        fetch('manual_user_deposit.php', { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    userCard.classList.remove('show');
                    showAlert(searchAlert, data.message || 'User not found', 'danger');
                    return;
                }
                const u = data.user;
                document.getElementById('uId').textContent = u.id;
                document.getElementById('uPhone').textContent = u.mobile || '-';
                document.getElementById('uBalance').textContent = formatMoney(u.balance);
                document.getElementById('uNeedBet').textContent = formatMoney(u.amountofCode);
                document.getElementById('uTotalBet').textContent = formatMoney(u.totalBet);
                document.getElementById('uRequired').textContent = formatMoney(u.requiredWager);
                document.getElementById('deposit_user_id').value = u.id;
                userCard.classList.add('show');
            })
            .catch(function () {
                showAlert(searchAlert, 'Search request failed.', 'danger');
            });
    });

    depositForm.addEventListener('submit', function (e) {
        e.preventDefault();
        hideAlert(depositAlert);
        const formData = new FormData();
        formData.append('ajax', 'deposit');
        formData.append('user_id', document.getElementById('deposit_user_id').value);
        formData.append('amount', document.getElementById('amount').value);
        formData.append('remark', document.getElementById('remark').value);

        fetch('manual_user_deposit.php', { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    showAlert(depositAlert, data.message || 'Deposit failed', 'danger');
                    return;
                }
                document.getElementById('uBalance').textContent = formatMoney(data.newBalance);
                if (data.wagering) {
                    document.getElementById('uNeedBet').textContent = formatMoney(data.wagering.amountofCode);
                    document.getElementById('uTotalBet').textContent = formatMoney(data.wagering.totalBet);
                    document.getElementById('uRequired').textContent = formatMoney(data.wagering.requiredWager);
                }
                document.getElementById('amount').value = '';
                showAlert(depositAlert, (data.message || 'Done') + ' Ref: ' + (data.serial || ''), 'success');
            })
            .catch(function () {
                showAlert(depositAlert, 'Deposit request failed.', 'danger');
            });
    });
})();
</script>
</body>
</html>
