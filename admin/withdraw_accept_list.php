<?php
session_start();
if($_SESSION['unohs'] == null){
    header("location:index.php?msg=unauthorized");
}

include ("conn.php");

// Handle clear data
if(isset($_POST['clear_data'])){
    $sql = "DELETE FROM hintegedukolli WHERE sthiti = '1'";
    if(mysqli_query($conn, $sql)) {
        $_SESSION['clear_success'] = true;
        header("Location: withdraw_accept.php?msg=data_cleared");
        exit();
    } else {
        $_SESSION['clear_error'] = "Error clearing data: " . mysqli_error($conn);
        header("Location: withdraw_accept.php");
        exit();
    }
}
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
  <link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.dataTables.min.css">
  <link rel="shortcut icon" href="images/favicon.png" />
  <style>
    .cool-input {
        border: 2px solid rgb(14, 19, 92);
        border-radius: 0.25rem;
        padding: 0.5rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    .cool-input:focus {
        border-color: rgb(14, 19, 92);
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    .cool-input::placeholder {
        color: #6c757d;
        opacity: 1;
    }
    .cool-button {
        padding: 0.5rem 1rem;
        font-size: 1rem;
        border-radius: 0.25rem;
        transition: all 0.3s ease;
    }
    .cool-button:hover {
        background-color: rgb(14, 19, 92);
        color: #fff;
    }
    .cool-button.btn-secondary:hover {
        background-color: #343a40;
        color: #fff;
    }
    #copied{
        visibility: hidden;
        z-index: 1;
        position: fixed;
        bottom: 50%;
        background-color: #333;
        color: #fff;
        border-radius: 6px;
        padding: 16px;
        max-width: 250px;
        font-size: 17px;
    }       
    #copied.show {
        visibility: visible;
        -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
        animation: fadein 0.5s, fadeout 0.5s 2.5s;
    }
    .clear-btn-container {
        margin-bottom: 20px;
        text-align: right;
    }
    .success-checkmark {
        display: none;
        width: 80px;
        height: 80px;
        margin: 0 auto;
        position: relative;
    }
    .success-checkmark .check-icon {
        width: 80px;
        height: 80px;
        position: relative;
        border-radius: 50%;
        box-sizing: content-box;
        border: 4px solid #4CAF50;
    }
    .success-checkmark .check-icon::before {
        top: 3px;
        left: -2px;
        width: 30px;
        transform-origin: 100% 50%;
        border-radius: 100px 0 0 100px;
    }
    .success-checkmark .check-icon::after {
        top: 0;
        left: 30px;
        width: 60px;
        transform-origin: 0 50%;
        border-radius: 0 100px 100px 0;
        animation: rotate-circle 4.25s ease-in;
    }
    .success-checkmark .check-icon::before, .success-checkmark .check-icon::after {
        content: '';
        height: 100px;
        position: absolute;
        background: #FFFFFF;
        transform: rotate(-45deg);
    }
    .success-checkmark .check-icon .icon-line {
        height: 5px;
        background-color: #4CAF50;
        display: block;
        border-radius: 2px;
        position: absolute;
        z-index: 10;
    }
    .success-checkmark .check-icon .icon-line.line-tip {
        top: 46px;
        left: 14px;
        width: 25px;
        transform: rotate(45deg);
        animation: icon-line-tip 0.75s;
    }
    .success-checkmark .check-icon .icon-line.line-long {
        top: 38px;
        right: 8px;
        width: 47px;
        transform: rotate(-45deg);
        animation: icon-line-long 0.75s;
    }
    @keyframes icon-line-tip {
        0% { width: 0; left: 1px; top: 19px; }
        54% { width: 0; left: 1px; top: 19px; }
        70% { width: 50px; left: -8px; top: 37px; }
        84% { width: 17px; left: 21px; top: 48px; }
        100% { width: 25px; left: 14px; top: 46px; }
    }
    @keyframes icon-line-long {
        0% { width: 0; right: 46px; top: 54px; }
        65% { width: 0; right: 46px; top: 54px; }
        84% { width: 55px; right: 0px; top: 35px; }
        100% { width: 47px; right: 8px; top: 38px; }
    }
    @keyframes rotate-circle {
        0% { transform: rotate(-45deg); }
        5% { transform: rotate(-45deg); }
        12% { transform: rotate(-405deg); }
        100% { transform: rotate(-405deg); }
    }
    .success-message {
        text-align: center;
        color: #4CAF50;
        font-weight: bold;
        margin: 20px 0;
        display: none;
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
          <div class="user-name">
              Prime Tech
          </div>
          <div class="user-designation">
              Admin
          </div>
        </div>
        <?php include 'compass.php';?>
      </nav>
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-sm-12 mb-4 mb-xl-0">
              <h4 class="font-weight-bold text-dark">Withdraw Accept List</h4>
            </div>
          </div>
          
          <!-- Clear Data Button -->
          <div class="clear-btn-container">
            <form method="post" onsubmit="return confirmClearData()">
                <button type="submit" name="clear_data" class="btn btn-danger">
                    <i class="mdi mdi-delete"></i> Clear All Accepted Withdrawals
                </button>
            </form>
          </div>
          
          <!-- Success Message and Checkmark -->
          <?php if(isset($_SESSION['clear_success']) && $_SESSION['clear_success']): ?>
          <div class="success-message" id="successMessage">
              <div class="success-checkmark">
                  <div class="check-icon">
                      <span class="icon-line line-tip"></span>
                      <span class="icon-line line-long"></span>
                      <div class="icon-circle"></div>
                      <div class="icon-fix"></div>
                  </div>
              </div>
              <p>All accepted withdrawals have been cleared successfully!</p>
          </div>
          <?php 
              unset($_SESSION['clear_success']); 
          endif; ?>
          
          <?php if(isset($_SESSION['clear_error'])): ?>
          <div class="alert alert-danger">
              <?php echo $_SESSION['clear_error']; unset($_SESSION['clear_error']); ?>
          </div>
          <?php endif; ?>
                    
          <div class="row">
            <div class="col-sm-12">
                <form id="formID" name="formID" method="post" action="#" enctype="multipart/form-data">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sr.No</th>
                                <th>User Mobile</th>
                                <th>User ID</th>
                                <th>Amount</th>
                                <th>Order ID</th>
                                <th>Payout Type</th>
                                <th>Req. Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $Query = mysqli_query($conn, "SELECT *, 
                            (SELECT `mobile` FROM `shonu_subjects` WHERE `id`=`hintegedukolli`.`balakedara`) AS user,
                            (SELECT `id` FROM `shonu_subjects` WHERE `id`=`hintegedukolli`.`balakedara`) AS subject_id
                        FROM `hintegedukolli` 
                        WHERE `sthiti`='1' 
                        ORDER BY `shonu` DESC");

                        $i = 0;
                        $total = 0; 
                        while ($row = mysqli_fetch_array($Query)) {
                            $i++;
                            $total += $row['motta'];
                        ?>  
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo $row["user"]; ?></td>
                                <td><?php echo $row["subject_id"]; ?></td>
                                <td><?php echo number_format($row['motta'], 2); ?></td>
                                <td><?php echo $row["dharavahi"]; ?></td>
                                <td><?php echo 'bank'; ?></td>
                                <td><?php echo date('d-m-Y', strtotime($row['dinankavannuracisi'])); ?></td>                                    
                            </tr>
                        <?php 
                        }
                        ?>
                        </tbody>
                    </table>
                </form>                          
            </div>            
          </div>          
        </div>
        <footer class="footer">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
                <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Copyright © vgclub.fun 2025</span>
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
  <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
  <script>
    $(function () {
        $('#example1').DataTable({
          "paging": true,
          "lengthChange": false,
          "searching": true,
          "ordering": false,
          "info": true,
          "autoWidth": true,
          "pageLength": 100
        });
        
        // Show success animation if needed
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'data_cleared'): ?>
            showSuccessAnimation();
        <?php endif; ?>
    });
    
    function confirmClearData() {
        return confirm("Are you sure you want to clear all accepted withdrawals?\nThis action cannot be undone.");
    }
    
    function showSuccessAnimation() {
        $('#successMessage').fadeIn();
        setTimeout(function() {
            $('#successMessage').fadeOut();
        }, 5000);
    }
  </script>
</body>
</html>