<?php
session_start();
if($_SESSION['unohs'] == null){
    header("location:index.php?msg=unauthorized");
}
?>
<?php
include ("conn.php");
    
// Add new Telegram
if(isset($_POST['newtelegram'])){
    $newtelegram = mysqli_real_escape_string($conn, $_POST['newtelegram']);
    $sql_q = "INSERT INTO telegramme (value, status) VALUES ('".$newtelegram."', '0')";
    $chk = mysqli_query($conn, $sql_q);
    if($chk){
        echo '<script type="text/JavaScript"> alert("Telegram Added"); </script>';
    }else {
        echo '<script type="text/JavaScript"> alert("Telegram Failed"); </script>';
    }
}
    
// Set active Telegram
if(isset($_POST['telegramid'])){
    $a_id = $_POST['telegramid'];
    $sql_s = "SELECT * FROM telegramme WHERE value = '".$a_id."'";
    $run = mysqli_query($conn, $sql_s);
    
    //Set all status to 0 first
    $sql_d = "UPDATE telegramme SET status='0' WHERE status='1'";        
    $run_d = mysqli_query($conn, $sql_d);
    
    //Set selected to active (status=1)
    $run_f = mysqli_fetch_array($run);
    $ch_s1 = "UPDATE telegramme SET status='1' WHERE id='".$run_f['id']."'";
    $exe_ch_s1 = mysqli_query($conn, $ch_s1);
    if($exe_ch_s1){
        echo '<script type="text/JavaScript"> alert("Active Telegram Updated"); </script>';
    }
}

// Delete Telegram
if(isset($_GET['delete'])){
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Check if this is the active telegram
    $check_active = mysqli_query($conn, "SELECT status FROM telegramme WHERE id='$id'");
    $is_active = mysqli_fetch_assoc($check_active)['status'];
    
    if($is_active == 1) {
        echo '<script type="text/JavaScript"> alert("Cannot delete active Telegram. Set another as active first."); </script>';
    } else {
        $delete = mysqli_query($conn, "DELETE FROM telegramme WHERE id='$id'");
        if($delete){
            echo '<script type="text/JavaScript"> alert("Telegram Deleted"); </script>';
        } else {
            echo '<script type="text/JavaScript"> alert("Delete Failed"); </script>';
        }
    }
}

// Update Telegram
if(isset($_POST['update'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $new_value = mysqli_real_escape_string($conn, $_POST['new_value']);
    
    $update = mysqli_query($conn, "UPDATE telegramme SET value='$new_value' WHERE id='$id'");
    if($update){
        echo '<script type="text/JavaScript"> alert("Telegram Updated"); </script>';
    } else {
        echo '<script type="text/JavaScript"> alert("Update Failed"); </script>';
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
    .action-buttons {
        display: inline-flex;
        gap: 5px;
    }
    .edit-form {
        display: none;
        margin-top: 10px;
        padding: 10px;
        background: #f5f5f5;
        border-radius: 5px;
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
              <h4 class="font-weight-bold text-dark">Telegram Management</h4>
            </div>
          </div> 
          <div class="row">
            <form action="#" id="telegramform" method="post" autocomplete="off">
                <input name="newtelegram" type="text" placeholder="Add new telegram" class="flex-grow-1 cool-input" style="height: 40px;" required />
                <br>
                <br>
                <button type="submit" class="btn btn-primary cool-button mr-2">Add Telegram</button>
            </form>
          </div>
          <div class="row mt-4">
            <div class="col-md-12">
                <h5>Manage Telegrams</h5>
                <form action="#" id="telgramsave" method="post" autocomplete="off">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Active</th>
                                <th>Telegram</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sel_upi = "SELECT * FROM telegramme ORDER BY status DESC";
                            $upi_r = mysqli_query($conn, $sel_upi);
                            while ($row = mysqli_fetch_array($upi_r)) {
                            ?>
                            <tr>
                                <td>
                                    <input name="telegramid" type="radio" value="<?php echo $row['value']; ?>" <?php if($row['status']==1){echo "checked";} ?> />
                                </td>
                                <td>
                                    <span class="telegram-value"><?php echo $row['value']; ?></span>
                                    <div class="edit-form" id="edit-form-<?php echo $row['id']; ?>">
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <input type="text" name="new_value" value="<?php echo $row['value']; ?>" class="cool-input" required>
                                            <button type="submit" name="update" class="btn btn-success btn-sm cool-button">Update</button>
                                            <button type="button" class="btn btn-secondary btn-sm cool-button cancel-edit" data-id="<?php echo $row['id']; ?>">Cancel</button>
                                        </form>
                                    </div>
                                </td>
                                <td><?php echo ($row['status'] == 1) ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>'; ?></td>
                                <td class="action-buttons">
                                    <button class="btn btn-warning btn-sm edit-btn cool-button" data-id="<?php echo $row['id']; ?>">Edit</button>
                                    <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm cool-button" onclick="return confirm('Are you sure you want to delete this Telegram?')">Delete</a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary cool-button mr-2">Save Changes</button>
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
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
    
    // Edit button functionality
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            // Hide all other edit forms first
            document.querySelectorAll('.edit-form').forEach(form => {
                form.style.display = 'none';
            });
            // Show this edit form
            document.getElementById('edit-form-' + id).style.display = 'block';
        });
    });
    
    // Cancel button functionality
    document.querySelectorAll('.cancel-edit').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            document.getElementById('edit-form-' + id).style.display = 'none';
        });
    });
  </script>
</body>
</html>