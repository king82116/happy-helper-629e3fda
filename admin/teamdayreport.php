<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'josh296689_max');
define('DB_PASS', 'josh296689_max');
define('DB_NAME', 'josh296689_max');

// Handle API request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error)
        die(json_encode(['error' => 'Database connection failed']));

    $ownCode = $conn->real_escape_string($_POST['ownCode'] ?? '');

    // Get user data
    $userQuery = $conn->prepare("SELECT * FROM shonu_subjects WHERE owncode = ?");
    $userQuery->bind_param('s', $ownCode);
    $userQuery->execute();
    $userResult = $userQuery->get_result();

    if ($userResult->num_rows === 0) {
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    $user = $userResult->fetch_assoc();
    $userId = $user['id'];
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    // Get commission data
    $commissionQuery = $conn->prepare("
        SELECT SUM(ayoga) as total_commission, COUNT(*) as bet_count, SUM(ketebida) as total_bet
        FROM vyavahara 
        WHERE balakedara = ? AND DATE(tiarikala) = ?
    ");
    $commissionQuery->bind_param('ss', $userId, $yesterday);
    $commissionQuery->execute();
    $commissionResult = $commissionQuery->get_result()->fetch_assoc();

    // Get deposit data
    $depositQuery = $conn->prepare("
        SELECT 
            COUNT(*) as total_deposits,
            SUM(motta) as total_deposit_amount,
            SUM(CASE WHEN first_deposit = 1 THEN 1 ELSE 0 END) as first_deposit_count,
            SUM(CASE WHEN first_deposit = 1 THEN motta ELSE 0 END) as first_deposit_total
        FROM thevani 
        WHERE balakedara = ? AND DATE(dinankavannuracisi) = ?
    ");
    $depositQuery->bind_param('ss', $userId, $yesterday);
    $depositQuery->execute();
    $depositResult = $depositQuery->get_result()->fetch_assoc();

    echo json_encode([
        'user' => $user,
        'commission' => $commissionResult,
        'deposit' => $depositResult
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>User Commission Report</title>
    <link rel="stylesheet" href="css/mobile-responsive.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --danger: #e74c3c;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .search-box {
            width: 100%;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .stat-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .commission {
            border-left: 4px solid var(--secondary);
        }

        .bets {
            border-left: 4px solid var(--success);
        }

        .deposits {
            border-left: 4px solid var(--danger);
        }

        .first-deposits {
            border-left: 4px solid #f1c40f;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>User Commission Report</h1>
        <input type="text" class="search-box" placeholder="Enter User Own Code..." id="searchInput">

        <div id="results">
            <div class="loading">Enter user own code to view report</div>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const resultsDiv = document.getElementById('results');

        const debounce = (func, delay) => {
            let timeout;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => func(...args), delay);
            };
        };

        const fetchData = async (ownCode) => {
            resultsDiv.innerHTML = '<div class="loading">Loading...</div>';

            try {
                const formData = new FormData();
                formData.append('action', 'search');
                formData.append('ownCode', ownCode);

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.error) {
                    resultsDiv.innerHTML = `<div class="loading">${data.error}</div>`;
                    return;
                }

                // Format numbers
                const format = num => new Intl.NumberFormat('en-IN').format(num || 0);

                resultsDiv.innerHTML = `
                    <div class="user-info">
                        <div class="user-avatar">${data.user.codechorkamukala[0]}</div>
                        <div>
                            <h2>${data.user.codechorkamukala}</h2>
                            <p>Own Code: ${data.user.owncode}</p>
                        </div>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-card commission">
                            <div class="stat-value">₹${format(data.commission.total_commission)}</div>
                            <div class="stat-label">Yesterday's Commission</div>
                        </div>
                        
                        <div class="stat-card bets">
                            <div class="stat-value">${format(data.commission.bet_count)}</div>
                            <div class="stat-label">Total Bets</div>
                            <div class="stat-value">₹${format(data.commission.total_bet)}</div>
                        </div>
                        
                        <div class="stat-card deposits">
                            <div class="stat-value">${format(data.deposit.total_deposits)}</div>
                            <div class="stat-label">Total Deposits</div>
                            <div class="stat-value">₹${format(data.deposit.total_deposit_amount)}</div>
                        </div>
                        
                        <div class="stat-card first-deposits">
                            <div class="stat-value">${format(data.deposit.first_deposit_count)}</div>
                            <div class="stat-label">First Deposits</div>
                            <div class="stat-value">₹${format(data.deposit.first_deposit_total)}</div>
                        </div>
                    </div>
                `;
            } catch (error) {
                resultsDiv.innerHTML = `<div class="loading">Error loading data</div>`;
            }
        };

        searchInput.addEventListener('input', debounce((e) => {
            const ownCode = e.target.value.trim();
            if (ownCode.length >= 3) {
                fetchData(ownCode);
            }
        }, 300));
    </script>
</body>

</html>