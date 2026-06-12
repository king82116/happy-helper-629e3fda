<?php
session_start();
if($_SESSION['unohs'] == null){
    header("location:index.php?msg=unauthorized");
}
?>
<?php
include ("conn.php");
    
// Add new USDT
if(isset($_POST['newupi'])){
    $upiid = mysqli_real_escape_string($conn, $_POST['newupi']);
    $sql_q = "INSERT INTO deyyamrici (maulya, sthiti) VALUES ('".$upiid."', '0')";
    $chk = mysqli_query($conn, $sql_q);
    if($chk){
        echo '<script type="text/JavaScript"> alert("USDT ID Added"); </script>';
    }else {echo '<script type="text/JavaScript"> alert("USDT ID Failed"); </script>';}
}

// Set active USDT
if(isset($_POST['upiid'])){
    $a_id = $_POST['upiid'];
    $sql_s = "SELECT * FROM deyyamrici WHERE maulya = '".$a_id."'";
    $run = mysqli_query($conn, $sql_s);
    //Set status to 0
    $sql_d = "SELECT * FROM deyyamrici WHERE sthiti = '1'";        
    $run_d = mysqli_query($conn, $sql_d);
    $rund_f = mysqli_fetch_array($run_d);
    $ch_s0 = "UPDATE deyyamrici SET sthiti='0' WHERE shonu='".$rund_f['shonu']."'";
    $exe_ch_s0 = mysqli_query($conn, $ch_s0);
    //Set status to 1
    $run_f = mysqli_fetch_array($run);
    $ch_s1 = "UPDATE deyyamrici SET sthiti='1' WHERE shonu='".$run_f['shonu']."'";
    $exe_ch_s1 = mysqli_query($conn, $ch_s1);
}

// Delete USDT
if(isset($_GET['delete'])){
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    // Check if this is the active USDT
    $check_active = "SELECT sthiti FROM deyyamrici WHERE shonu='$id'";
    $active_result = mysqli_query($conn, $check_active);
    $active_row = mysqli_fetch_assoc($active_result);
    
    if($active_row['sthiti'] == 1) {
        echo '<script type="text/JavaScript"> alert("Cannot delete active USDT ID. Please set another USDT as active first."); </script>';
    } else {
        $delete_query = "DELETE FROM deyyamrici WHERE shonu='$id'";
        if(mysqli_query($conn, $delete_query)){
            echo '<script type="text/JavaScript"> alert("USDT ID Deleted"); </script>';
        } else {
            echo '<script type="text/JavaScript"> alert("Delete Failed"); </script>';
        }
    }
}

// Edit USDT
if(isset($_POST['edit_upi'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $new_upi = mysqli_real_escape_string($conn, $_POST['new_upi']);
    
    $update_query = "UPDATE deyyamrici SET maulya='$new_upi' WHERE shonu='$id'";
    if(mysqli_query($conn, $update_query)){
        echo '<script type="text/JavaScript"> alert("USDT ID Updated"); </script>';
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
    .action-btns {
        display: inline-flex;
        gap: 5px;
    }
    .edit-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.4);
    }
    .edit-modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 50%;
        border-radius: 5px;
    }
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    .close:hover {
        color: black;
    }
    .table-container {
        margin-top: 20px;
        background: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
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
              <h4 class="font-weight-bold text-dark">USDT Management</h4>
            </div>
          </div> 
          <div class="row">
            <form action="#" id="upiform" method="post" autocomplete="off">
                <div class="d-flex align-items-center">    
                    <input name="newupi" type="text" placeholder="Add an USDT ID" class="flex-grow-1 cool-input" style="height: 40px;" required />
                </div>
                <div class="d-flex align-items-center mt-3">
                    <button type="submit" class="btn btn-primary cool-button mr-2">Add</button>
                </div>
            </form>
          </div>
          <div class="row mt-4">
            <div class="col-md-12 table-container">
                <?php
                    $sel_upi = "SELECT * FROM deyyamrici WHERE sthiti='0' OR sthiti='1' ORDER BY sthiti DESC";
                    $upi_r = mysqli_query($conn, $sel_upi);
                ?>
                <form action="#" id="upisave" method="post" autocomplete="off">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="10%">Active</th>
                                <th width="60%">USDT ID</th>
                                <th width="30%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_array($upi_r)) { ?>
                            <tr>
                                <td>
                                    <input name="upiid" type="radio" value="<?php echo $row['maulya']; ?>" <?php if($row['sthiti']==1){echo "checked";} ?> />
                                </td>
                                <td><?php echo $row['maulya']; ?></td>
                                <td class="action-btns">
                                    <button type="button" class="btn btn-sm btn-warning edit-btn" 
                                        data-id="<?php echo $row['shonu']; ?>" 
                                        data-upi="<?php echo $row['maulya']; ?>">
                                        Edit
                                    </button>
                                    <a href="?delete=<?php echo $row['shonu']; ?>" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('Are you sure you want to delete this USDT ID?')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary cool-button mr-2">Save Active USDT</button>
                </form>
            </div>
          </div>
          
          <!-- Edit Modal -->
          <div id="editModal" class="edit-modal">
            <div class="edit-modal-content">
                <span class="close">&times;</span>
                <h3>Edit USDT ID</h3>
                <form id="editForm" method="post">
                    <input type="hidden" name="id" id="editId">
                    <div class="form-group">
                        <input type="text" class="form-control cool-input" name="new_upi" id="editUpi" required>
                    </div>
                    <button type="submit" name="edit_upi" class="btn btn-primary">Update</button>
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
    
    // Edit modal functionality
    const modal = document.getElementById("editModal");
    const editBtns = document.querySelectorAll(".edit-btn");
    const span = document.getElementsByClassName("close")[0];
    
    editBtns.forEach(btn => {
        btn.addEventListener("click", function() {
            const id = this.getAttribute("data-id");
            const upi = this.getAttribute("data-upi");
            
            document.getElementById("editId").value = id;
            document.getElementById("editUpi").value = upi;
            modal.style.display = "block";
        });
    });
    
    span.onclick = function() {
        modal.style.display = "none";
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
  </script>
</body>
</html>