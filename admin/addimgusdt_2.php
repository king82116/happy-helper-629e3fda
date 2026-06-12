<?php
session_start();
if($_SESSION['unohs'] == null){
    header("location:index.php?msg=unauthorized");
}

include ("conn.php");
    
// Handle image upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"])) {
    $target_dir = "../images_usdt/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check === false) {
            echo '<script type="text/JavaScript"> alert("File is not an image."); </script>';
            $uploadOk = 0;
        }
    }
   
    if (file_exists($target_file)) {
        echo '<script type="text/JavaScript"> alert("Sorry, file already exists."); </script>';
        $uploadOk = 0;
    }

    if ($_FILES["image"]["size"] > 500000) {
        echo '<script type="text/JavaScript"> alert("Sorry, your file is too large."); </script>';
        $uploadOk = 0;
    }

    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        echo '<script type="text/JavaScript"> alert("Sorry, only JPG, JPEG, PNG files are allowed."); </script>';
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $filename = basename($_FILES["image"]["name"]);
            $sql = "INSERT INTO images_usdt (filename) VALUES ('$filename')";

            if ($conn->query($sql) !== TRUE) {
                echo '<script type="text/JavaScript"> alert("Error adding record"); </script>';
            }
        } else {
            echo '<script type="text/JavaScript"> alert("Sorry, there was an error uploading your file."); </script>';
        }
    }
}

// Handle image selection
if(isset($_POST['upiid'])){
    $a_id = $_POST['upiid'];
    $sql_s = "SELECT * FROM images_usdt WHERE filename = '".$a_id."'";
    $run = mysqli_query($conn, $sql_s);
    
    // Set current active image to inactive
    $sql_d = "SELECT * FROM images_usdt WHERE status = '1'";        
    $run_d = mysqli_query($conn, $sql_d);
    if(mysqli_num_rows($run_d) > 0) {
        $rund_f = mysqli_fetch_array($run_d);
        $ch_s0 = "UPDATE images_usdt SET status='0' WHERE id='".$rund_f['id']."'";
        mysqli_query($conn, $ch_s0);
    }
    
    // Set new image as active
    $run_f = mysqli_fetch_array($run);
    $ch_s1 = "UPDATE images_usdt SET status='1' WHERE id='".$run_f['id']."'";
    mysqli_query($conn, $ch_s1);
}

// Handle image deletion
if(isset($_POST['delete_id'])) {
    $image_id = $_POST['delete_id'];
    
    // Get filename from database
    $sql = "SELECT filename FROM images_usdt WHERE id = $image_id";
    $result = mysqli_query($conn, $sql);
    
    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $filename = $row['filename'];
        $file_path = "../images_usdt/" . $filename;
        
        // Delete from database
        $delete_sql = "DELETE FROM images_usdt WHERE id = $image_id";
        if(mysqli_query($conn, $delete_sql)) {
            // Delete the file
            if(file_exists($file_path)) {
                unlink($file_path);
            }
            echo '<script type="text/JavaScript"> alert("Image deleted successfully"); </script>';
        } else {
            echo '<script type="text/JavaScript"> alert("Error deleting image"); </script>';
        }
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
    .cool-button.btn-danger {
        background-color: #dc3545;
        color: white;
    }
    .cool-button.btn-danger:hover {
        background-color: #c82333;
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
    .image-item {
        display: flex;
        align-items: center;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    .image-preview {
        width: 80px;
        height: 80px;
        object-fit: contain;
        margin-right: 15px;
    }
    .image-info {
        flex-grow: 1;
    }
    .image-actions {
        margin-left: auto;
    }
    .active-image {
        background-color: #e6f7ff;
        border-color: #1890ff;
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
              <h4 class="font-weight-bold text-dark">Add USDT Image</h4>
            </div>
          </div> 
          <div class="row">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" style="margin:5px; padding:8px;">
                <h4>Select image to upload:</h4>
                <div class="d-flex align-items-center">    
                    <input type="file" name="image" id="image" class="flex-grow-1 cool-input" style="height: 40px;" required>
                </div>
                <div class="d-flex align-items-center mt-3">
                    <input type="submit" value="Upload Image" name="submit" class="btn btn-primary cool-button mr-2">
                </div>
            </form>
          </div>
          <div class="row">
            <div class="col-md-8">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="upisave" method="post" autocomplete="off">
                    <?php
                    $sel_upi = "SELECT * FROM images_usdt WHERE status='0' OR status='1' ORDER BY status DESC";
                    $upi_r = mysqli_query($conn, $sel_upi);
                    
                    if(mysqli_num_rows($upi_r) > 0) {
                        while ($row = mysqli_fetch_array($upi_r)) {
                            $image_path = "../images_usdt/" . $row['filename'];
                    ?>
                    <div class="image-item <?php echo $row['status'] == 1 ? 'active-image' : ''; ?>">
                        <img src="<?php echo $image_path; ?>" alt="USDT Image" class="image-preview">
                        <div class="image-info">
                            <input name="upiid" type="radio" value="<?php echo $row['filename']; ?>" 
                                   id="img_<?php echo $row['id']; ?>" 
                                   <?php echo $row['status'] == 1 ? 'checked' : ''; ?> />
                            <label for="img_<?php echo $row['id']; ?>"><?php echo $row['filename']; ?></label>
                        </div>
                        <div class="image-actions">
                            <button type="button" onclick="confirmDelete(<?php echo $row['id']; ?>)" 
                                    class="btn btn-danger cool-button">
                                Delete
                            </button>
                        </div>
                    </div>
                    <?php
                        }
                    ?>
                    <button type="submit" class="btn btn-primary cool-button mr-2" style="margin-top: 10px;">
                        Save Selection
                    </button>
                    <?php
                    } else {
                        echo "<p>No USDT images uploaded yet.</p>";
                    }
                    ?>
                </form>
                
                <!-- Hidden form for delete action -->
                <form id="deleteForm" method="post" style="display:none;">
                    <input type="hidden" name="delete_id" id="delete_id" value="">
                </form>
            </div>
          </div>
        </div>
        <footer class="footer">
          <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Copyright © 2025</span>
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
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    
    function confirmDelete(imageId) {
        if(confirm('Are you sure you want to delete this image?')) {
            document.getElementById('delete_id').value = imageId;
            document.getElementById('deleteForm').submit();
        }
    }
  </script>
</body>
</html>