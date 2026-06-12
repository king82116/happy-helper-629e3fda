<?php
	session_start();
	if($_SESSION['unohs'] == null){
		header("location:index.php?msg=unauthorized");
	}
?>
<?php
include ("conn.php");
$tem = date("Y-m-d H:i:s");
$timestamp = strtotime($tem);
$newTimestamp = $timestamp - 60 * 60 * 24;
$startDate = date("Y-m-d 00:00:00", $newTimestamp);
$endDate = date("Y-m-d H:i:s", $timestamp);


$newDate = date("Y-m-d H:i:s", $newTimestamp);

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
				<div class="box-header box-header2 align-middle">
					<div class="col-xs-6 text-right">
					<h3 class="box-title"><?php 
						if(isset($_GET['msg'])=="updt") 
						{ ?>
						<font size="+1" color="#FF0000">Update Successfully...</font>
						<?php  } ?></h3>
					</div>
					<div class="col-sm-6">
						<div class="pull-right">&nbsp;</div>
					</div>		  
				</div>
			</div>
          <div class="row">
            <div class="col-sm-12 mb-4 mb-xl-0">
              <h4 class="font-weight-bold text-dark">Manage User</h4>
            </div>
          </div> 		  		  		  			
		  <div class="row">
            <div class="col-sm-12">
                <!-- Add this search div before the form -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" id="search_id" class="form-control cool-input" placeholder="Enter Customer ID">
                            <div class="input-group-append">
                                <button class="btn btn-primary cool-button" type="button" id="search_button">
                                    <i class="fa fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Existing form starts here -->
				<form id="formID" name="formID" method="post" action="#" enctype="multipart/form-data">
					<div class="table-responsive">
						<table id="example1" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th id="mob">Cust ID</th>
									<th>Direct Deposit</th>
									<th>Team Deposit</th>
									<th>Direct Bet</th>
									<th>Team Bet</th>         
									<th>No. of betters</th>
									<th>Direct Withdraw</th>
									<th>Team Withdraw</th>
									<th>P/L Report</th>
									<th>Salary</th>
									<th>Approve</th>
								</tr>
							</thead>
							<tbody>							
								<?php 
									$query = mysqli_query($conn, "SELECT userid, salary 
                                         FROM `dailysalary` 
                                          WHERE salary > 0 
															 AND `createdate` BETWEEN '$startDate' AND '$endDate' 
															 AND status = 0 
                                          ORDER BY macau DESC");
								while($row = mysqli_fetch_array($query)) {
									$userid = $row['userid'];
									// Get user details
									$mob = mysqli_query($conn, "SELECT id, owncode FROM `shonu_subjects` WHERE id = $userid");
									$mobrow = mysqli_fetch_array($mob);
									$owncode = $mobrow['owncode'];
									$mobile = $mobrow['id'];
								?>
									<tr>
										<td data-cust_id="<?php echo $mobile; ?>"><?php echo $mobile; ?></td>
										<td data-direct_deposit="<?php 
											$mob = mysqli_query($conn, "SELECT SUM(motta) as tomo FROM thevani WHERE balakedara = $userid and `sthiti`='1' && DATE(`dinankavannuracisi`)=DATE('".$newDate."')");
											$mobrow = mysqli_fetch_array($mob);
											echo ($mobrow['tomo'] == null) ? '0' : $mobrow['tomo'];
										?>"><?php echo ($mobrow['tomo'] == null) ? '0' : $mobrow['tomo']; ?></td>
										<td data-team_deposit="<?php 
											$mob = mysqli_query($conn, "SELECT SUM(motta) as tomo FROM thevani WHERE balakedara IN (SELECT id FROM shonu_subjects WHERE code = '$owncode') and `sthiti`='1' && DATE(`dinankavannuracisi`)=DATE('".$newDate."')");
											$mobrow = mysqli_fetch_array($mob);
											echo ($mobrow['tomo'] == null) ? '0' : $mobrow['tomo'];
										?>"><?php echo ($mobrow['tomo'] == null) ? '0' : $mobrow['tomo']; ?></td>
										<td data-direct_bet="<?php 
											$mob = mysqli_query($conn, "SELECT SUM(motta) as tomo FROM thevani WHERE balakedara = $userid and `sthiti`='1' && DATE(`dinankavannuracisi`)=DATE('".$newDate."')");
												$mobrow = mysqli_fetch_array($mob);
											echo ($mobrow['tomo'] == null) ? '0' : $mobrow['tomo'];
										?>"><?php echo ($mobrow['tomo'] == null) ? '0' : $mobrow['tomo']; ?></td>
										<td data-team_bet="<?php 
											$mob = mysqli_query($conn, "SELECT SUM(motta) as tomo FROM thevani WHERE balakedara IN (SELECT id FROM shonu_subjects WHERE code = '$owncode') and `sthiti`='1' && DATE(`dinankavannuracisi`)=DATE('".$newDate."')");
												$mobrow = mysqli_fetch_array($mob);
											echo ($mobrow['tomo'] == null) ? '0' : $mobrow['tomo'];
										?>"><?php echo ($mobrow['tomo'] == null) ? '0' : $mobrow['tomo']; ?></td>
										<td data-num_betters="<?php 
											$mob = mysqli_query($conn, "SELECT COUNT(DISTINCT balakedara) as num_betters FROM thevani WHERE balakedara = $userid and `sthiti`='1' && DATE(`dinankavannuracisi`)=DATE('".$newDate."')");
												$mobrow = mysqli_fetch_array($mob);
											echo ($mobrow['num_betters'] == null) ? '0' : $mobrow['num_betters'];
										?>"><?php echo ($mobrow['num_betters'] == null) ? '0' : $mobrow['num_betters']; ?></td>
										<td data-direct_withdraw="<?php 
											$mob = mysqli_query($conn, "SELECT SUM(motta) as tomo FROM thevani WHERE balakedara = $userid and `sthiti`='1' && DATE(`dinankavannuracisi`)=DATE('".$newDate."')");
												$mobrow = mysqli_fetch_array($mob);
											echo ($mobrow['tomo'] == null) ? '0' : $mobrow['tomo'];
										?>"><?php echo ($mobrow['tomo'] == null) ? '0' : $mobrow['tomo']; ?></td>
										<td data-team_withdraw="<?php 
											$mob = mysqli_query($conn, "SELECT SUM(motta) as tomo FROM thevani WHERE balakedara IN (SELECT id FROM shonu_subjects WHERE code = '$owncode') and `sthiti`='1' && DATE(`dinankavannuracisi`)=DATE('".$newDate."')");
												$mobrow = mysqli_fetch_array($mob);
											echo ($mobrow['tomo'] == null) ? '0' : $mobrow['tomo'];
										?>"><?php echo ($mobrow['tomo'] == null) ? '0' : $mobrow['tomo']; ?></td>
										<td data-pl_report="<?php 
											$pl = ($mobrow['tomo'] == null ? 0 : $mobrow['tomo']) - ($mobrow['tomo'] == null ? 0 : $mobrow['tomo']);
											echo number_format($pl, 2); 
										?>"><?php 
											$pl = ($mobrow['tomo'] == null ? 0 : $mobrow['tomo']) - ($mobrow['tomo'] == null ? 0 : $mobrow['tomo']);
											echo number_format($pl, 2); 
										?></td>
										<td data-salary="<?php echo $row['salary']; ?>">
											<?php echo $row['salary']; ?>&nbsp;
											<a href="javascript:void(0);" onClick="edita(<?php echo $userid; ?>,<?php echo $mobile; ?>,<?php echo $row['salary']; ?>)" class="text-aqua" title="Salary">
												<i class="fa fa-edit"></i>
											</a>
										</td>
										<td data-approve>
											<a href="javascript:void(0);" onClick="edit(<?php echo $userid; ?>,<?php echo $mobile; ?>,<?php echo $row['salary']; ?>)" class="text-aqua" title="Approve">Pay</a>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</form>			  			  
            </div>			
          </div>		  
		</div>
		<div id="excel" class="modal fade" role="dialog">
			<div class="modal-dialog modal-sm">
				<div class="modal-content">
					<div class="modal-header">						
						<h4 class="modal-title" id="chn">Change Amount<br>
							<small id="mob"></small>
						</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form name="type" id="type" enctype="multipart/form-data" action="#" method="post">
						<div class="modal-body">              
							<div class="form-group ">
								<label for="add_item">Amount</label>  
								<input class="form-control" id="amount" name="amount" type="text" value="" onkeypress="return isNumber(event)" required>
								<input class="form-control" id="editid" name="editid"  type="hidden">
								<i id="error"></i>
							</div>           			
						</div>
						<div class="modal-footer">              
							<button type="submit" class="btn btn-danger" id="add_role">Save</button>
						</div>
					</form>
				</div>
			</div>
         
		</div>
		<div id="excela" class="modal fade" role="dialog">
			<div class="modal-dialog modal-sm">
				<div class="modal-content">
					<div class="modal-header">						
						<h4 class="modal-title" id="chn">Change Amount<br>
							<small id="moba"></small>
						</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form name="type" id="typea" enctype="multipart/form-data" action="#" method="post">
						<div class="modal-body">              
							<div class="form-group ">
								<label for="add_item">Amount</label>  
								<input class="form-control" id="amounta" name="amount" type="text" value="" onkeypress="return isNumber(event)" required>
								<input class="form-control" id="editida" name="editid"  type="hidden">
								<i id="error"></i>
							</div>           			
						</div>
						<div class="modal-footer">              
							<button type="submit" class="btn btn-danger" id="add_role">Save</button>
						</div>
					</form>
				</div>
			</div>
         
		</div>
		<footer class="footer">
			<div class="d-sm-flex justify-content-center justify-content-sm-between">
				<span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Copyright © suzlonlottery.com 2025</span>
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
	$(document).ready(function() {
		// Initial DataTable setup - simpler configuration
		var table = $('#example1').DataTable({
			"paging": true,
			"lengthChange": false,
			"searching": true,
			"ordering": false,
			"info": true,
			"pageLength": 50
		});

		// Search button click handler
		$('#search_button').click(function() {
			var searchId = $('#search_id').val().trim();
			if(!searchId) {
				alert('Please enter a Customer ID');
				return;
			}

			// Show loading state
			$('#search_button').prop('disabled', true);
			$('#example1 tbody').html('<tr><td colspan="11" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');

			$.ajax({
				url: 'get_user_data.php',
				type: 'POST',
				data: { user_id: searchId },
				success: function(response) {
					// Clear existing table data
					table.clear();
					
					// Update table content
					$('#example1 tbody').html(response);
					
					// Clear search input
					$('#search_id').val('');
				},
				error: function(xhr, status, error) {
					$('#example1 tbody').html('<tr><td colspan="11" class="text-center">Error fetching data. Please try again.</td></tr>');
				},
				complete: function() {
					$('#search_button').prop('disabled', false);
				}
			});
		});

		// Enter key handler
		$('#search_id').keypress(function(e) {
			if(e.which == 13 && !$('#search_button').prop('disabled')) {
				$('#search_button').click();
			}
		});
	});

	// Helper functions
	function edit(userid, mobile, salary) {
		$('#mob').html(mobile);
		$('#editid').val(userid);
		$('#amount').val(salary);
		$('#excel').modal('show');
	}

	function edita(userid, mobile, salary) {
		$('#moba').html(mobile);
		$('#editida').val(userid);
		$('#amounta').val(salary);
		$('#excela').modal('show');
	}

	function isNumber(evt) {
		evt = (evt) ? evt : window.event;
		var charCode = (evt.which) ? evt.which : evt.keyCode;
		if (charCode > 31 && (charCode < 48 || charCode > 57)) {
			return false;
		}
		return true;
	}
  </script>
<script>
$(document).ready(function() {
    $('.approve-btn').click(function() {
        const userId = $(this).data('userid');
        if(confirm('Are you sure you want to approve this salary?')) {
            $.ajax({
                url: 'approve_salary.php',
                method: 'POST',
                data: { 
                    user_id: userId,
                    action: 'approve_salary'  // Adding action parameter
                },
                success: function(response) {
                    if(response === 'success') {
                        alert('Salary approved successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + response);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error approving salary: ' + error);
                }
            });
        }
    });
});
  </script>
</body>

</html>