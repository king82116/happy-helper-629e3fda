<?php
session_start();
if ($_SESSION['unohs'] == null) {
    header("location:index.php?msg=unauthorized");
}
include("conn.php");

function checkIllegalBets($conn, $user_id_search = '') {
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
        WHERE byabaharkarta LIKE '%$user_id_search%' -- Add search filter for user_id
        GROUP BY kalaparichaya, table_name, byabaharkarta
        HAVING bet_type_count > 1
        ORDER BY latest_time DESC
    ";
    return $conn->query($query);
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ban_user'])) {
    $user_id = $conn->real_escape_string($_POST['user_id']);
    $period = $conn->real_escape_string($_POST['period']);
    $game = $conn->real_escape_string($_POST['game']);

    $insert_query = "INSERT INTO illegal_bet_banned (user_id, period, game) VALUES ('$user_id', '$period', '$game')";
    $update_query = "UPDATE shonu_subjects SET status = 0 WHERE id = '$user_id'";

    if ($conn->query($insert_query) === TRUE && $conn->query($update_query) === TRUE) {
        $message = "User $user_id has been banned successfully!";
    } else {
        $message = "Error banning user: " . $conn->error;
    }
}

// Handle user search input
$user_id_search = isset($_POST['search_user_id']) ? $conn->real_escape_string($_POST['search_user_id']) : '';

$illegal_bets = checkIllegalBets($conn, $user_id_search);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Set auto-refresh interval to 30 seconds -->
    <meta http-equiv="refresh" content="30">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Illegal Bet Users</title>
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/feather/feather.css">
    <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Center the title */
        .page-title {
            text-align: center;
            font-size: 2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 30px;
        }

        /* Enhanced Table Design */
        .illegal-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            background: #F5F5F5;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.15);
        }

        .illegal-table thead {
            background: rgb(14, 19, 92);
        }

        .illegal-table th {
            padding: 18px 24px;
            text-align: left;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
            border-bottom: none;
        }

        .illegal-table td {
            padding: 16px 24px;
            color: #000;
            font-size: 0.95em;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            background: #F5F5F5;
            transition: all 0.3s ease;
        }

        .illegal-table tbody tr:nth-child(even) td {
            background: #E5E3D4;
        }

        .illegal-table tbody tr:hover td {
            background: rgb(14, 19, 92);
            transform: translateX(4px);
        }

        .ban-btn {
            background: linear-gradient(135deg, #ff6b6b 0%, #ff4757 100%);
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(255,107,107,0.2);
        }

        .ban-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255,107,107,0.3);
        }

        .ban-btn i {
            font-size: 1.1em;
        }

        .status-message {
            padding: 15px 25px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-success {
            background: rgba(40,167,69,0.15);
            color: #28a745;
            border: 1px solid rgba(40,167,69,0.3);
        }

        .status-error {
            background: rgba(220,53,69,0.15);
            color: #dc3545;
            border: 1px solid rgba(220,53,69,0.3);
        }

        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .search-input {
            width: 200px;
            padding: 8px;
            margin: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
  <div class="container-scroller">
    <!-- Navbar and sidebar here -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo" href="dashboard.php"><img src="images/logo.png" alt="logo"/></a>
        <a class="navbar-brand brand-logo-mini" href="dashboard.php"><img src="images/logo-mini.png" alt="logo"/></a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
          <span class="icon-menu"></span>
        </button>       
        <ul class="navbar-nav navbar-nav-right">           
          <li class="nav-item dropdown d-flex mr-4 ">
            <a class="nav-link count-indicator dropdown-toggle d-flex align-items-center justify-content-center" id="notificationDropdown" href="#" data-toggle="dropdown">
              <i class="icon-cog"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
              <p class="mb-0 font-weight-normal float-left dropdown-header">Settings</p>              
              <a class="dropdown-item preview-item" href="logout.php">
                  <i class="icon-inbox"></i> Logout
              </a>
            </div>
          </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
          <span class="icon-menu"></span>
        </button>
      </div>
    </nav>
    <div class="container-fluid page-body-wrapper">
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <div class="user-profile">
          <div class="user-image">
            <img src="images/faces/face28.png">
          </div>
          <div class="user-name">
              Game
          </div>
          <div class="user-designation">
              Admin
          </div>
        </div>
        <?php include 'compass.php';?>
      </nav>
    <div class="main-panel">
      <div class="content-wrapper">
        <!-- Centered Title -->
        <div class="page-title">
            Illegal Bet Users
        </div>

        <div class="table-responsive">
          <?php if (isset($message)): ?>
              <div class="status-message <?= strpos($message, 'successfully') !== false ? 'status-success' : 'status-error' ?>">
                  <i class="mdi <?= strpos($message, 'successfully') !== false ? 'mdi-check-circle' : 'mdi-alert-circle' ?>"></i>
                  <?= $message ?>
              </div>
          <?php endif; ?>

          <!-- User ID Search -->
          <form method="POST" class="mb-4">
            <input type="text" name="search_user_id" class="search-input" placeholder="Search by User ID" value="<?= htmlspecialchars($user_id_search) ?>" />
            <button type="submit" class="btn btn-primary">Search</button>
          </form>

          <table class="illegal-table">
              <thead>
                  <tr>
                      <th>Period</th>
                      <th>User ID</th>
                      <th>Game</th>
                      <th>DateTime</th>
                      <th>Action</th>
                  </tr>
              </thead>
              <tbody>
                  <?php if ($illegal_bets->num_rows > 0): ?>
                      <?php while ($row = $illegal_bets->fetch_assoc()): ?>
                          <tr>
                              <td><?= $row['kalaparichaya'] ?></td>
                              <td><?= $row['byabaharkarta'] ?></td>
                              <td><?= getGameName($row['table_name']) ?></td>
                              <td><?= $row['latest_time'] ?></td>
                              <td>
                                  <form method="POST">
                                      <input type="hidden" name="user_id" value="<?= $row['byabaharkarta'] ?>">
                                      <input type="hidden" name="period" value="<?= $row['kalaparichaya'] ?>">
                                      <input type="hidden" name="game" value="<?= getGameName($row['table_name']) ?>">
                                      <button type="submit" name="ban_user" class="ban-btn">
                                          <i class="mdi mdi-account-cancel-outline"></i>
                                          Ban User
                                      </button>
                                  </form>
                              </td>
                          </tr>
                      <?php endwhile; ?>
                  <?php else: ?>
                      <tr>
                          <td colspan="5" class="text-center text-muted py-4">
                              <i class="mdi mdi-shield-check"></i> No illegal bets detected
                          </td>
                      </tr>
                  <?php endif; ?>
              </tbody>
          </table>
        </div>
      </div>
        <footer class="footer">
			<div class="d-sm-flex justify-content-center justify-content-sm-between">
				<span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Copyright © Jeetopkr.site 2025</span>
			</div>
		</footer>
    </div>
  </div>

  <script src="vendors/base/vendor.bundle.base.js"></script>
  <script src="js/off-canvas.js"></script>
  <script src="js/hoverable-collapse.js"></script>
  <script src="js/template.js"></script>
</body>
</html>
