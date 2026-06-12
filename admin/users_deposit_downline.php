<?php
session_start();
if ($_SESSION['unohs'] == null) {
    header("location:index.php?msg=unauthorized");
}
include("conn.php");

if (isset($_POST['submit'])) {
    $user_id = $_POST['user_id'];

    $query = "SELECT * FROM shonu_subjects WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user_result) {

        $query = "SELECT * FROM thevani WHERE balakedara = ? ORDER BY dinankavannuracisi ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $deposit_result = $stmt->get_result();

        $first_deposit = $second_deposit = $third_deposit = null;
        $total_deposit = 0;
        $today_deposit = 0;
        $today = date('Y-m-d');

        while ($row = $deposit_result->fetch_assoc()) {
            $total_deposit += $row['motta'];
            if (!$first_deposit) $first_deposit = $row['motta'];
            elseif (!$second_deposit) $second_deposit = $row['motta'];
            elseif (!$third_deposit) $third_deposit = $row['motta'];

            if (strpos($row['dinankavannuracisi'], $today) !== false) {
                $today_deposit += $row['motta'];
            }
        }
        $stmt->close();

        $query = "SELECT * FROM hintegedukolli WHERE balakedara = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $withdrawal_result = $stmt->execute() ? $stmt->get_result() : [];

        $withdrawals_today = 0;
        $first_withdrawal = $second_withdrawal = $third_withdrawal = null;

        while ($row = $withdrawal_result->fetch_assoc()) {
            $withdrawals_today += $row['motta'];
            if (!$first_withdrawal) $first_withdrawal = $row['motta'];
            elseif (!$second_withdrawal) $second_withdrawal = $row['motta'];
            elseif (!$third_withdrawal) $third_withdrawal = $row['motta'];
        }

        $stmt->close();

        $codes = [
            'Code1' => $user_result['code1'],
            'Code2' => $user_result['code2'],
            'Code3' => $user_result['code3'],
            'Code4' => $user_result['code4'],
            'Code5' => $user_result['code5']
        ];

        $downline_data = [];
        $total_downline_deposit_today = 0;
        $total_downline_withdrawal_today = 0;

        $total_downline_deposit = 0;  // Initialize a variable to hold downline deposits

foreach ($codes as $position => $own_code) {
    if ($own_code) {
        $query = "SELECT id FROM shonu_subjects WHERE owncode = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $own_code);
        $stmt->execute();
        $downline_result = $stmt->get_result();

        while ($row = $downline_result->fetch_assoc()) {
            $downline_id = $row['id'];

            $query = "SELECT * FROM thevani WHERE balakedara = ? ORDER BY dinankavannuracisi ASC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $downline_id);
            $stmt->execute();
            $deposit_result = $stmt->get_result();

            $down_first = $down_second = $down_third = null;
            $down_total = 0;
            $down_today = 0;

            while ($row = $deposit_result->fetch_assoc()) {
                $down_total += $row['motta'];
                $total_downline_deposit += $row['motta'];  // Accumulate the downline deposits
                if (!$down_first) $down_first = $row['motta'];
                elseif (!$down_second) $down_second = $row['motta'];
                elseif (!$down_third) $down_third = $row['motta'];

                if (strpos($row['dinankavannuracisi'], $today) !== false) {
                    $down_today += $row['motta'];
                }
            }

            $stmt->close();
            
            $query = "SELECT * FROM hintegedukolli WHERE balakedara = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $downline_id);
                    $withdrawal_result = $stmt->execute() ? $stmt->get_result() : [];

                    $down_withdrawals_today = 0;
                    $down_first_withdrawal = $down_second_withdrawal = $down_third_withdrawal = null;

                    while ($row = $withdrawal_result->fetch_assoc()) {
                        $down_withdrawals_today += $row['motta'];
                        $total_downline_withdrawal_today += $row['motta'];
                        if (!$down_first_withdrawal) $down_first_withdrawal = $row['motta'];
                        elseif (!$down_second_withdrawal) $down_second_withdrawal = $row['motta'];
                        elseif (!$down_third_withdrawal) $down_third_withdrawal = $row['motta'];
                    }

                    $stmt->close();

            // Add downline data to array
            $downline_data[] = [
                'position' => $position,
                'id' => $downline_id,
                'first_deposit' => $down_first ?? 'N/A',
                'second_deposit' => $down_second ?? 'N/A',
                'third_deposit' => $down_third ?? 'N/A',
                'total_deposit' => $down_total,
                'today_deposit' => $down_today,
                'first_withdrawal' => $down_first_withdrawal ?? 'N/A',
                'second_withdrawal' => $down_second_withdrawal ?? 'N/A',
                'third_withdrawal' => $down_third_withdrawal ?? 'N/A',
                'today_withdrawal' => $down_withdrawals_today
            ];
        }
    }
}

    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Downline Deposit & Withdrawal Details</title>
   <style>
.container {
    padding: 20px;
    display: flex;
    flex-direction: column; 
    gap: 20px; 
    justify-content: flex-start; 
    align-items: center; 
    margin-top: 20px;
    width: 100%;
    box-sizing: border-box; 
}

h2 {
    color: #f39c12;
    text-align: center;
    width: 100%;
    margin-bottom: 30px;
    font-size: 2em;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.box {
    background-color: #2c3e50;
    width: 100%;
    margin: 10px 0;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    color: #ecf0f1;
    border: 1px solid #16a085;
}

h3, h4 {
    color: #ecf0f1;
    margin-bottom: 15px;
}

p {
    font-size: 1.1em;
    color: #bdc3c7;
    line-height: 1.6;
    margin: 8px 0;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: #34495e;
    border-radius: 8px;
    overflow: hidden;
}

th, td {
    padding: 12px;
    text-align: center;
    border: 1px solid #16a085;
    word-wrap: break-word;
    max-width: 200px;
}

th {
    background-color: #16a085;
    color: #fff;
    font-weight: 600;
}

td {
    background-color: #34495e;
    color: #ecf0f1;
}

input[type="number"], input[type="text"] {
    padding: 12px;
    width: 100%;
    margin-bottom: 15px;
    border: 2px solid #16a085;
    border-radius: 5px;
    background-color: #2c3e50;
    color: #ecf0f1;
    font-size: 16px;
}

button {
    background-color: #e74c3c;
    color: white;
    padding: 12px 25px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    width: 100%;
    font-size: 16px;
    transition: all 0.3s ease;
}

button:hover {
    background-color: #c0392b;
    transform: translateY(-1px);
}

.footer {
    background-color: #2c3e50;
    color: #bdc3c7;
    text-align: center;
    padding: 20px;
    width: 100%;
    margin-top: auto;
}

/* Improved Mobile Handling */
@media (max-width: 768px) {
    .container {
        padding: 10px;
    }
    
    table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    
    .box {
        padding: 15px;
    }
    
    th, td {
        min-width: 120px;
        font-size: 0.9em;
        padding: 8px;
    }
    
    h2 {
        font-size: 1.5em;
    }
    
    button {
        padding: 10px 20px;
    }
}

/* Added subtle animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.box {
    animation: fadeIn 0.5s ease-out;
}

/* Enhanced form contrast */
input:focus {
    outline: none;
    border-color: #f39c12;
    box-shadow: 0 0 8px rgba(243, 156, 18, 0.3);
}
</style>


  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Dashboard</title>
  <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="vendors/feather/feather.css">
  <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
  <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css"/>
  <link rel="stylesheet" href="vendors/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="vendors/jquery-bar-rating/fontawesome-stars-o.css">
  <link rel="stylesheet" href="vendors/jquery-bar-rating/fontawesome-stars.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="shortcut icon" href="images/favicon.png" />
</head>
<body>
  <div class="container-scroller">
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
              Shonu Uncensored
          </div>
          <div class="user-designation">
              Developer
          </div>
        </div>
        <?php include 'compass.php';?>
      </nav>
    <div class="container">
        <h2>Check User Downline Deposit & Withdrawal Details</h2>
        <form method="POST" style="width: 100%;">
            <input type="number" name="user_id" placeholder="Enter User ID" required>
            <button type="submit" name="submit">Check</button>
        </form>
   

    <?php if (isset($user_result)) : ?>
        <div class="container">
            <div class="box">
                <h3>Searched User Details</h3>
                <p><strong>ID:</strong> <?php echo $user_result['id']; ?></p>
                <p><strong>Own Code:</strong> <?php echo $user_result['owncode']; ?></p>
                <h4>Deposit Summary</h4>
                <p><strong>First Deposit:</strong> <?php echo $first_deposit ?? 'N/A'; ?></p>
                <p><strong>Second Deposit:</strong> <?php echo $second_deposit ?? 'N/A'; ?></p>
                <p><strong>Third Deposit:</strong> <?php echo $third_deposit ?? 'N/A'; ?></p>
                <p><strong>Total Deposits:</strong> <?php echo $total_deposit; ?></p>
                <p><strong>Today's Deposits:</strong> <?php echo $today_deposit; ?></p>
                <h4>Withdrawal Summary</h4>
                <p><strong>First Withdrawal:</strong> <?php echo $first_withdrawal ?? 'N/A'; ?></p>
                <p><strong>Second Withdrawal:</strong> <?php echo $second_withdrawal ?? 'N/A'; ?></p>
                <p><strong>Third Withdrawal:</strong> <?php echo $third_withdrawal ?? 'N/A'; ?></p>
                <p><strong>Total Withdrawals Today:</strong> <?php echo $withdrawals_today; ?></p>
            </div>

            <div class="box">
                <h3>Downline Summary</h3>
                <p><strong>Total Deposits:</strong> <?php echo $total_downline_deposit; ?></p>
                <p><strong>Total Withdrawals:</strong> <?php echo $withdrawals_today; ?></p>
                <p><strong>Total Downline Deposits Today:</strong> <?php echo $total_downline_deposit_today; ?></p>
                <p><strong>Total Downline Withdrawals Today:</strong> <?php echo $total_downline_withdrawal_today; ?></p>
            </div>
        </div>

        <div class="container">
            <div class="box" style="width: 100%">
                <h3>Downline Deposits & Withdrawals</h3>
                <table>
                    <tr>
                        <th>Position</th>
                        <th>ID</th>
                        <th>First Deposit</th>
                        <th>Second Deposit</th>
                        <th>Third Deposit</th>
                        <th>Total Deposits</th>
                        <th>Today's Deposits</th>
                        <th>First Withdrawal</th>
                        <th>Second Withdrawal</th>
                        <th>Third Withdrawal</th>
                        <th>Today's Withdrawals</th>
                    </tr>
                    <?php foreach ($downline_data as $downline) : ?>
                        <tr>
                            <td><?php echo $downline['position']; ?></td>
                            <td><?php echo $downline['id']; ?></td>
                            <td><?php echo $downline['first_deposit']; ?></td>
                            <td><?php echo $downline['second_deposit']; ?></td>
                            <td><?php echo $downline['third_deposit']; ?></td>
                            <td><?php echo $downline['total_deposit']; ?></td>
                            <td><?php echo $downline['today_deposit']; ?></td>
                            <td><?php echo $downline['first_withdrawal']; ?></td>
                            <td><?php echo $downline['second_withdrawal']; ?></td>
                            <td><?php echo $downline['third_withdrawal']; ?></td>
                            <td><?php echo $downline['today_withdrawal']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    <?php endif; ?>
     </div>
 <footer class="footer">
          <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Powered by  bigplayhub</span>
          </div>
        </footer>
      </div>
    </div>
  </div>
  <script src="vendors/base/vendor.bundle.base.js"></script>
  <script src="js/off-canvas.js"></script>
  <script src="js/hoverable-collapse.js"></script>
  <script src="js/template.js"></script>
  <script src="vendors/chart.js/Chart.min.js"></script>
  <script src="vendors/jquery-bar-rating/jquery.barrating.min.js"></script>
  <script src="js/dashboard.js"></script>
</body>

</html>  please fix css and style and create good design