<?php
	session_start();
	if($_SESSION['unohs'] == null){
		header("location:index.php?msg=unauthorized");
	}	
	date_default_timezone_set("Asia/Karachi");
?>
<?php 
	include ("conn.php");
	
	$curdate = date('Y-m-d h:i:s');
?>
<!DOCTYPE html>
<html lang="en">

<head>
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
  <style>
    /* ----------------------------------------- */
    /* --- NEW LIGHT YELLOW THEME STYLES --- */
    /* ----------------------------------------- */

    /* General Body & Background */
    .content-wrapper {
      background: #FFFDF5; /* A very light, warm off-white background */
    }

    /* Page Title */
    .page-title {
      color: #333333; /* Dark text for readability */
      font-weight: 700 !important;
    }
    .page-subtitle {
      color: #777777;
    }

    /* Main Card Style */
    .dashboard-card {
      background: #ffffff;
      border-radius: 12px;
      border: 1px solid #FFEEBA; /* Soft yellow border */
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
      padding: 25px;
      color: #333;
      transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
      position: relative;
      overflow: hidden;
      width: 100%;
      height: 100%;
    }

    .dashboard-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 8px 25px rgba(255, 193, 7, 0.15);
      border-color: #FFD54F;
    }
    
    .card-col {
        padding: 10px;
    }

    /* Icon Styling */
    .card-icon {
      font-size: 50px;
      position: absolute;
      right: 20px;
      top: 50%;
      transform: translateY(-50%);
      opacity: 0.15;
      color: #FFC107; /* Amber yellow for icons */
      transition: all 0.4s ease;
    }
    .dashboard-card:hover .card-icon {
      opacity: 0.3;
      transform: translateY(-50%) scale(1.1);
    }

    /* Card Text Styling */
    .text-title {
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #888; /* Muted grey for the title */
      white-space: normal;
      word-break: break-word;
    }
    .text-amount {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 0;
      color: #212121; /* Strong dark color for numbers */
    }

    /* Card Footer/Link Styling */
    .panel-footer {
      margin-top: 20px;
      padding-top: 10px;
      border-top: 1px solid #f5f5f5;
      font-size: 13px;
    }
    .panel-footer a {
      color: #B28900; /* Darker, muted gold for links */
      text-decoration: none;
      transition: color 0.3s ease;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: 500;
    }
    .panel-footer a:hover {
      color: #FFC107;
    }
    .panel-footer a::after {
        content: '\F054'; /* FontAwesome right arrow */
        font-family: 'FontAwesome';
        opacity: 0;
        transition: all 0.3s ease;
        transform: translateX(-5px);
    }
    .panel-footer a:hover::after {
        opacity: 1;
        transform: translateX(0);
    }

    /* Settings Form Styling */
    .settings-form {
      background: #ffffff;
      border: 1px solid #FFEEBA;
      border-radius: 12px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
      padding: 30px;
      margin: 20px 10px;
    }
    .settings-form h4 {
      color: #333;
    }
    .settings-form label {
      font-weight: 600;
      color: #555;
      margin-bottom: 10px;
    }
    .settings-form .form-control {
      background: #f9f9f9;
      border: 1px solid #ddd;
      color: #333;
      border-radius: 8px;
    }
    .settings-form .form-control:focus {
      background: #fff;
      border-color: #FFC107;
      color: #333;
      box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
    .settings-form .btn-primary {
      background: #FFC107;
      border-color: #FFC107;
      color: #333;
      font-weight: bold;
      padding: 12px 25px;
      border-radius: 8px;
      transition: all 0.3s ease;
    }
    .settings-form .btn-primary:hover {
      background: #FFB300;
      border-color: #FFB300;
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(255, 193, 7, 0.3);
    }
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
          <style>
    .user-name {
        color: black;
        /* Optional additional styling */
        font-weight: bold;
        font-size: 1.2rem;
    }
</style>

<div class="user-name">
    Prime Tech
</div>
         
        </div>
        <?php include 'compass.php';?>
      </nav>
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-sm-12 mb-4 mb-xl-0">
              <h4 class="font-weight-bold page-title">Hi, welcome back!</h4>
              <p class="font-weight-normal mb-2 text-muted page-subtitle"><?php echo date("F d, Y"); ?></p>
            </div>
          </div>
		  <?php 
			$chkserial = mysqli_query($conn,"select * from `nirvahaka_shonu` where `unohs`='".$_SESSION['unohs']."'");
			$salu = mysqli_fetch_array($chkserial);
			$dashboard = $salu['dashboard'];
			if($dashboard == 1){
		  ?>
		  <div class="row">
            <div class="col-xl-3 col-md-6 card-col">
                <div class="dashboard-card">
                  <i class="mdi mdi-account-multiple-plus card-icon"></i>
                  <p class="text-title">Today User Join</p>
                  <h4 class="text-amount">
                    <?php
                      $result = mysqli_query($conn,"SELECT count(*) as 'total_user' FROM shonu_subjects where status = 1 AND id NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(createdate) = DATE('".$curdate."')");
                      $row = mysqli_fetch_array($result);
                      echo $row["total_user"] ?? "0";
                    ?>
                  </h4>
                </div>
            </div>
			
            <div class="col-xl-3 col-md-6 card-col">
                <div class="dashboard-card">
                    <i class="mdi mdi-cash-multiple card-icon"></i>
                    <p class="text-title">Today's Recharge</p>
                    <h4 class="text-amount">
                    <?php
                        $result = mysqli_query($conn,"SELECT sum(motta) as 'pending' FROM thevani WHERE sthiti = '1' AND balakedara NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(dinankavannuracisi) = DATE('".$curdate."')");
                        $row = mysqli_fetch_array($result);
                        echo number_format($row["pending"] ?? 0, 0);
                    ?>
                    </h4>
                </div>
            </div>
			
			<div class="col-xl-3 col-md-6 card-col">
                <div class="dashboard-card">
                    <i class="mdi mdi-bank-transfer card-icon"></i>
                    <p class="text-title">Today's Withdrawal</p>
                    <h4 class="text-amount">
                    <?php
                        $result = mysqli_query($conn,"SELECT sum(motta) as 'succ_w' FROM hintegedukolli where sthiti = 1 AND balakedara NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(dinankavannuracisi) = DATE('".$curdate."')");
                        $row = mysqli_fetch_array($result);
                        echo number_format($row["succ_w"] ?? 0, 0);
                    ?>
                    </h4>
                </div>
            </div>
			
			<div class="col-xl-3 col-md-6 card-col">
                <div class="dashboard-card">
                    <i class="mdi mdi-wallet card-icon"></i>
                    <p class="text-title">User Balance</p>
                    <h4 class="text-amount">
                    <?php
                        $result = mysqli_query($conn,"SELECT sum(motta) as 'wallt' FROM shonu_kaichila where balakedara NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND motta > 0");
                        $row = mysqli_fetch_array($result);
                        echo number_format($row["wallt"] ?? 0, 2);
                    ?>
                    </h4>
                    <div class="panel-footer">
                        <a href="manage_user.php">
                            <span>See in Detail</span>
                        </a>
                    </div>
                </div>
            </div>
			
			<div class="col-xl-3 col-md-6 card-col">
                <div class="dashboard-card">
                    <i class="mdi mdi-account-group card-icon"></i>
                    <p class="text-title">Total Users</p>
                    <h4 class="text-amount">
                    <?php
                        $result = mysqli_query($conn,"SELECT count(*) as 'total_user' FROM shonu_subjects where id NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND status = 1");
                        $row = mysqli_fetch_array($result);
                        echo $row["total_user"] ?? "0";
                    ?>
                    </h4>
                    <div class="panel-footer">
                        <a href="manage_user.php">
                            <span>See in Detail</span>
                        </a>
                    </div>
                </div>
            </div>
			
			<div class="col-xl-3 col-md-6 card-col">
                <div class="dashboard-card">
                    <i class="mdi mdi-timer-sand card-icon"></i>
                    <p class="text-title">Pending Recharge</p>
                    <h4 class="text-amount">
                    <?php
                        $result = mysqli_query($conn,"SELECT sum(motta) as 'pending_recharge' FROM thevani where balakedara NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND sthiti = '0'");
                        $row = mysqli_fetch_array($result);
                        echo number_format($row["pending_recharge"] ?? 0);
                    ?>
                    </h4>
                    <div class="panel-footer">
                        <a href="deposit_update.php">
                            <span>See in Detail</span>
                        </a>
                    </div>
                </div>
            </div>
			
			<div class="col-xl-3 col-md-6 card-col">
                <div class="dashboard-card">
                    <i class="mdi mdi-check-circle card-icon"></i>
                    <p class="text-title">Success Recharge</p>
                    <h4 class="text-amount">
                    <?php
                        $result = mysqli_query($conn,"SELECT sum(motta) as 'success_recharge' FROM thevani where balakedara NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND sthiti = '1'");
                        $row = mysqli_fetch_array($result);
                        echo number_format($row["success_recharge"] ?? 0, 0);
                    ?>
                    </h4>
                    <div class="panel-footer">
                        <a href="deposit_update.php">
                            <span>See in Detail</span>
                        </a>
                    </div>
                </div>
            </div>
			
			<div class="col-xl-3 col-md-6 card-col">
                <div class="dashboard-card">
                    <i class="mdi mdi-cash-refund card-icon"></i>
                    <p class="text-title">Total Withdrawal</p>
                    <h4 class="text-amount">
                    <?php
                        $result = mysqli_query($conn,"SELECT sum(motta) as 'pending_w' FROM hintegedukolli where balakedara NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND sthiti = '1'");
                        $row = mysqli_fetch_array($result);
                        echo number_format($row["pending_w"] ?? 0);
                    ?>
                    </h4>
                    <div class="panel-footer">
                        <a href="withdraw_accept_list.php">
                            <span>See in Detail</span>
                        </a>
                    </div>
                </div>
            </div>
			
			<div class="col-xl-3 col-md-6 card-col">
                <div class="dashboard-card">
                    <i class="mdi mdi-alert-circle card-icon"></i>
                    <p class="text-title">Withdrawal Requests</p>
                    <h4 class="text-amount">
                    <?php
                        $result = mysqli_query($conn,"SELECT sum(motta) as 'approve_withdrawal' FROM hintegedukolli where balakedara NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND sthiti = '0'");
                        $row = mysqli_fetch_array($result);
                        echo number_format($row["approve_withdrawal"] ?? 0);
                    ?>
                    </h4>
                    <div class="panel-footer">
                        <a href="manage_withdraw.php">
                            <span>See in Detail</span>
                        </a>
                    </div>
                </div>
            </div>
			
			<div class="col-xl-3 col-md-6 card-col">
                <div class="dashboard-card">
                    <i class="mdi mdi-dice-5 card-icon"></i>
                    <p class="text-title">Today's total bet</p>
                    <h4 class="text-amount">
                    <?php
                        $bet_wingo_1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate` where byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_wingo_3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_drei` where byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_wingo_5 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_funf` where byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_wingo_10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_zehn` where byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_k3_1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_kemuru` where byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_k3_3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_kemuru_drei` where byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_k3_5 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_kemuru_funf` where byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_k3_10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_kemuru_zehn` where byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_5d_1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_aidudi` where byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_5d_3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_aidudi_drei` where byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_5d_5 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_aidudi_funf` where byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_5d_10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_aidudi_zehn` where byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $total_bet = ($bet_wingo_1['total'] ?? 0) + ($bet_wingo_3['total'] ?? 0) + ($bet_wingo_5['total'] ?? 0) + ($bet_wingo_10['total'] ?? 0) + ($bet_k3_1['total'] ?? 0) + ($bet_k3_3['total'] ?? 0) + ($bet_k3_5['total'] ?? 0) + ($bet_k3_10['total'] ?? 0) + ($bet_5d_1['total'] ?? 0) + ($bet_5d_3['total'] ?? 0) + ($bet_5d_5['total'] ?? 0) + ($bet_5d_10['total'] ?? 0);
                        $asila = $total_bet;
                        echo number_format($asila, 2);
                    ?>
                    </h4>
                </div>
            </div>
			
			<div class="col-xl-3 col-md-6 card-col">
                <div class="dashboard-card">
                    <i class="mdi mdi-trophy card-icon"></i>
                    <p class="text-title">Today's total win</p>
                    <h4 class="text-amount">
                    <?php
                        $bet_wingo_1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(sesabida) as total FROM `bajikattuttate` where `phalaphala` = 'gagner' AND byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_wingo_3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(sesabida) as total FROM `bajikattuttate_drei` where `phalaphala` = 'gagner' AND byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_wingo_5 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(sesabida) as total FROM `bajikattuttate_funf` where `phalaphala` = 'gagner' AND byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_wingo_10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(sesabida) as total FROM `bajikattuttate_zehn` where `phalaphala` = 'gagner' AND byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_k3_1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(sesabida) as total FROM `bajikattuttate_kemuru` where `phalaphala` = 'gagner' AND byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_k3_3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(sesabida) as total FROM `bajikattuttate_kemuru_drei` where `phalaphala` = 'gagner' AND byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_k3_5 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(sesabida) as total FROM `bajikattuttate_kemuru_funf` where `phalaphala` = 'gagner' AND byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_k3_10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(sesabida) as total FROM `bajikattuttate_kemuru_zehn` where `phalaphala` = 'gagner' AND byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_5d_1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(sesabida) as total FROM `bajikattuttate_aidudi` where `phalaphala` = 'gagner' AND byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_5d_3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(sesabida) as total FROM `bajikattuttate_aidudi_drei` where `phalaphala` = 'gagner' AND byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_5d_5 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(sesabida) as total FROM `bajikattuttate_aidudi_funf` where `phalaphala` = 'gagner' AND byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $bet_5d_10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(sesabida) as total FROM `bajikattuttate_aidudi_zehn` where `phalaphala` = 'gagner' AND byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                        $total_win = ($bet_wingo_1['total'] ?? 0) + ($bet_wingo_3['total'] ?? 0) + ($bet_wingo_5['total'] ?? 0) + ($bet_wingo_10['total'] ?? 0) + ($bet_k3_1['total'] ?? 0) + ($bet_k3_3['total'] ?? 0) + ($bet_k3_5['total'] ?? 0) + ($bet_k3_10['total'] ?? 0) + ($bet_5d_1['total'] ?? 0) + ($bet_5d_3['total'] ?? 0) + ($bet_5d_5['total'] ?? 0) + ($bet_5d_10['total'] ?? 0);
                        $gala = $total_win;
                        echo number_format($gala, 2);
                    ?>
                    </h4>
                </div>
            </div>
			
			<div class="col-xl-3 col-md-6 card-col">
                <div class="dashboard-card">
                    <i class="mdi mdi-chart-line card-icon"></i>
                    <p class="text-title">Today's profit</p>
                    <h4 class="text-amount">
                    <?php
                        $amount = $asila - $gala;
                        echo number_format($amount, 2);
                    ?>
                    </h4>
                </div>
            </div>

		  </div>
		  <?php } ?>
						  
		  <?php
            // Fetch current settings from game_win_setting
            $sql = "SELECT game, process_type FROM game_win_settings LIMIT 1";
            $result = $conn->query($sql);

            $game_mode = "wingo"; // Default value
            $process_type = "highest_bet_wins"; // Default value

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $game_mode = $row['game'];
                $process_type = $row['process_type'];
            }

            // Handle form submission
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['game_mode'])) {
                $game_mode_post = $_POST['game_mode'];
                $process_type_post = $_POST['process_type'];

                // Update game_win_setting in the database
                $update_query = "UPDATE game_win_settings SET game = '$game_mode_post', process_type = '$process_type_post' WHERE id = 1";
                if ($conn->query($update_query) === TRUE) {
                    echo "<script>alert('Settings updated successfully!'); window.location.href=window.location.href;</script>";
                     // Refresh page to show updated values
                } else {
                    echo "<script>alert('Error updating settings: " . $conn->error . "');</script>";
                }
            }
          ?>
          <div class="settings-form">
            <h4 class="font-weight-bold mb-4">Game Settings</h4>
            <form method="POST" action="">
              <div class="form-group">
                <label for="game_mode">Select Game Mode:</label>
                <select name="game_mode" id="game_mode" class="form-control">
                    <option value="wingo" <?= ($game_mode == "wingo") ? "selected" : "" ?>>Wingo</option>
                    <option value="k3" <?= ($game_mode == "k3") ? "selected" : "" ?>>K3</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="process_type">Select Process Type:</label>
                <select name="process_type" id="process_type" class="form-control">
                    <option value="highest_bet_wins" <?= ($process_type == "highest_bet_wins") ? "selected" : "" ?>>Higher Bet Wins</option>
                    <option value="random" <?= ($process_type == "random") ? "selected" : "" ?>>Random</option>
                    <option value="default" <?= ($process_type == "default") ? "selected" : "" ?>>Higher Bet Lose</option>
                </select>
              </div>

              <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
          </div>
		</div>
        <footer class="footer">
          <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Powered by Prime Tech Admin</span>
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

</html>