<?php
	session_start();
	if($_SESSION['unohs'] == null){
		header("location:index.php?msg=unauthorized");
	}
?>
<?php
	include ("conn.php");
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
  
  <!-- ====================================================================
  ============== NEW STYLES FOR LIGHT YELLOW & BLACK THEME ==============
  ===================================================================== -->
  <style>
    /* --- Base & Background --- */
    body {
        background-color: #fffbeb; /* Light creamy yellow */
        color: #333; /* Dark gray for text */
        font-size: 0.95rem; /* Slightly smaller base font size */
    }

    /* --- Core Layout & Panels --- */
    .container-scroller, .page-body-wrapper {
        background: transparent !important;
    }

    .main-panel {
        background: transparent !important;
    }
    
    .content-wrapper {
        background: transparent !important;
        padding: 1.5rem;
    }
    
    .glass-panel { /* Re-purposing this class for our main content card */
        background: #ffffff;
        border: 1px solid #ddd;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        padding: 2rem;
    }

    /* --- Navbar --- */
    .navbar.fixed-top {
        background: #ffffff !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-bottom: 1px solid #eee;
    }
    .navbar .navbar-brand-wrapper, .navbar .navbar-menu-wrapper {
        background: transparent !important;
    }
    .navbar-toggler span {
        color: #333;
    }

    /* --- Sidebar --- */
    .sidebar {
        background: #fdf8e1; /* Slightly darker yellow */
        border-right: 1px solid #e0dacc;
    }
    .sidebar .user-profile {
        background: #fffbeb;
        border-bottom: 1px solid #e0dacc;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .sidebar .user-profile .user-name, .sidebar .user-profile .user-designation {
        color: #333;
    }
    .sidebar .nav .nav-item .nav-link {
        color: #555;
    }
    .sidebar .nav .nav-item.active > .nav-link {
        background: #fff3cd;
        color: #000;
    }
    .sidebar .nav .nav-item .nav-link i {
       color: #D2691E; /* Chocolate brown for icons */
    }

    /* --- Footer --- */
    .footer {
        background: #f8f9fa !important;
        border-top: 1px solid #dee2e6;
        color: #6c757d;
        padding: 1rem 0;
    }

    /* --- Typography & Colors --- */
    h1, h2, h3, h4, h5, h6, .font-weight-bold, .font-weight-bold.text-dark {
        color: #000000 !important; /* Pure black for headings */
    }
    label {
        color: #333;
        font-weight: 600;
    }
    .text-muted {
        color: #6c757d !important;
    }

    /* --- DataTables & Table Styling --- */
    .table-responsive {
        border-radius: 6px;
    }
    table.dataTable {
        border-collapse: collapse !important;
        width: 100% !important;
    }
    table.dataTable thead th, table.dataTable thead td {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        color: #000;
        font-weight: 600;
        padding: 12px 10px;
    }
    table.dataTable tbody td {
        padding: 10px;
    }
    table.dataTable tbody tr {
        background: #ffffff !important;
        color: #333;
        border-bottom: 1px solid #f1f1f1;
    }
    table.dataTable tbody tr:hover {
        background: #fff3cd !important; /* Light yellow hover */
    }
    table.dataTable.no-footer {
        border-bottom: 1px solid #dee2e6;
    }
    .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_processing, .dataTables_wrapper .dataTables_paginate {
        color: #555;
    }
    .dataTables_paginate .paginate_button {
        color: #333 !important;
        border: 1px solid #ddd;
        margin: 0 2px;
        border-radius: 4px;
    }
    .dataTables_paginate .paginate_button.current, .dataTables_paginate .paginate_button:hover {
        background: #007bff !important;
        border-color: #007bff !important;
        color: #fff !important;
    }
    
    /* --- Form & Input Styling --- */
    .form-control, .col-search-input {
        background-color: #fff;
        border: 1px solid #ced4da;
        color: #495057;
        border-radius: 4px;
        transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        height: calc(1.5em + .75rem + 2px);
        padding: .375rem .75rem;
    }
    .form-control:focus, .col-search-input:focus {
        background-color: #fff;
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        color: #495057;
    }
    .col-search-input {
        width: 100%; /* Make search inputs fill the header space */
        margin-top: 5px;
    }

    /* --- Button Styling --- */
    .btn {
        border-radius: 4px;
        font-weight: 500;
        border: 1px solid transparent;
        padding: .5rem .9rem;
        font-size: 0.9rem;
    }
    .btn-primary { background-color: #007bff; border-color: #007bff; color: white; }
    .btn-primary:hover { background-color: #0069d9; border-color: #0062cc; }
    
    .btn-danger { background-color: #dc3545; border-color: #dc3545; color: white; }
    .btn-danger:hover { background-color: #c82333; border-color: #bd2130; }

    .btn-success { background-color: #28a745; border-color: #28a745; color: white; }
    .btn-success:hover { background-color: #218838; border-color: #1e7e34; }

    .btn-warning { background-color: #ffc107; border-color: #ffc107; color: #212529; }
    .btn-warning:hover { background-color: #e0a800; border-color: #d39e00; }

    /* --- Modal Styling --- */
    .modal-content {
        background: #ffffff !important;
        border: 1px solid rgba(0,0,0,.2);
        border-radius: 0.3rem;
    }
    .modal-header {
        border-bottom: 1px solid #dee2e6;
        background-color: #f8f9fa;
    }
    .modal-header .close {
        color: #000;
    }
    .modal-footer {
        border-top: 1px solid #dee2e6;
        background-color: #f8f9fa;
    }

    /* --- Utility & Fixes --- */
    .dropdown-menu {
        background: #ffffff !important;
        border: 1px solid rgba(0,0,0,.15);
        color: #333;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.175);
    }
    .dropdown-item {
        color: #333;
    }
    .dropdown-item:hover {
        background: #f8f9fa !important;
        color: #16181b;
    }
    .dropdown-header {
        color: #6c757d;
    }
    .icon-cog {
        color: #007bff !important;
        font-size: 1.5rem;
    }
    
	#copied{ /* "Copied to clipboard" style notification */
		visibility: hidden;
		z-index: 1060; /* Higher than modal */
		position: fixed;
		bottom: 50px;
        left: 50%;
        transform: translateX(-50%);
		background-color: #28a745; /* Green for success */
		color: #fff;
		border-radius: 6px;
		padding: 16px;
		font-size: 17px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
	}	   
	#copied.show {
		visibility: visible;
		-webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
		animation: fadein 0.5s, fadeout 0.5s 2.5s;
	}
    @keyframes fadein {
        from {bottom: 0; opacity: 0;}
        to {bottom: 50px; opacity: 1;}
    }
    @keyframes fadeout {
        from {bottom: 50px; opacity: 1;}
        to {bottom: 0; opacity: 0;}
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
			<div class="row">
				<div class="box-header box-header2 align-middle col-12">
					<div class="text-right">
					<h3 class="box-title" style="font-size: 1rem;"><?php 
						if(isset($_GET['msg'])=="updt") 
						{ ?>
						<font color="#dc3545">Update Successfully...</font>
						<?php  } ?></h3>
					</div>	  
				</div>
			</div>
          <div class="row mb-4">
            <div class="col-sm-12">
              <h4 class="font-weight-bold">Manage User</h4>
            </div>
          </div> 		  		  		  			
		  <div class="row">
            <div class="col-sm-12 glass-panel">
				<form id="formID" name="formID" method="post" action="#" enctype="multipart/form-data">
					<div class="table-responsive">
						<table id="example1" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>Mobile</th>
									<th>Refer Code</th>
									<th>IP Address</th>
									<th>Cust ID</th>
									<th>Wallet</th>         
									<th>Recharge</th>
									<th>1st Recharge</th>
									<th>Reg. Date</th>
									<th>Need Bet</th>
									<th>Action</th>
									<th>Password</th>
									<th>Name</th>
									<th>Account No.</th>
									<th>Is Frozen</th>
								</tr>
							</thead>
							<tbody>							
						   
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
							<span aria-hidden="true">×</span>
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
							<button type="submit" class="btn btn-primary" id="add_role">Save</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div id="needBetModal" class="modal fade" role="dialog">
			<div class="modal-dialog modal-md">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Edit Need to Bet<br>
							<small id="needBetMob"></small>
						</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<form id="needBetForm" enctype="multipart/form-data" action="#" method="post">
						<div class="modal-body">
							<p class="text-muted small mb-2" id="needBetSummary"></p>
							<div class="form-group">
								<label for="need_bet_amount">Need to bet amount</label>
								<input class="form-control" id="need_bet_amount" name="need_bet" type="number" step="0.01" min="0" value="0" required>
								<small class="text-muted">Enter 0 to clear remaining wager, or a positive amount (e.g. 5000).</small>
								<input type="hidden" id="need_bet_userid" name="userid" value="">
							</div>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-primary" id="save_need_bet">Save</button>
						</div>
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
  <div id="copied">Copied!</div>

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
		// DataTable initialization
		var table = $('#example1').DataTable({
			"processing": true,
			"serverSide": true,
			"ajax": "manage_user_data.php",
			"paging": true,
			"lengthChange": true,
			"searching": true,
			"ordering": false,
			"info": true,
			"autoWidth": false, // Set to false for better control
            "responsive": true,
			"pageLength": 50,
            "columnDefs": [ // Target specific columns if they cause width issues
                { "width": "120px", "targets": 0 }, // Mobile
                { "width": "150px", "targets": 7 }  // Reg. Date
            ]
		});

        // Setup - add a text input to each header cell for searching
        $('#example1 thead th').each(function () {
            var title = $(this).text();
            // Add search input only to specific columns
            if (['Mobile', 'Cust ID', 'IP Address', 'Refer Code', 'Account No.'].includes(title)) {
                $(this).html(title + '<input type="text" class="col-search-input" placeholder="Search" onclick="event.stopPropagation();" />');
            }
        });

        // Apply the search
        table.columns().every(function () {
            var that = this;
            $('input', this.header()).on('keyup change clear', function () {
                if (that.search() !== this.value) {
                    that.search(this.value).draw();
                }
            });
        });

        // Delegated handler: inline onClick breaks when mobile is quoted inside double-quoted attributes
        $('#example1 tbody').on('click', 'a.edit-need-bet', function (e) {
            e.preventDefault();
            var id = $(this).data('userid');
            var mob = $(this).data('mobile');
            if (id) {
                editNeedBet(id, mob);
            }
        });
    });

	function isNumber(evt) {
		var charCode = (evt.which) ? evt.which : evt.keyCode;
		if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 46) {
			return false;
		}
		return true;
	}

	function edit(id,mob,balance) {
		$('#excel').modal({backdrop: 'static', keyboard: false})   
		$('#excel').modal('show');
		document.getElementById('mob').innerHTML = 'Mobile: '+mob;
		document.getElementById('amount').value = balance;
		document.getElementById('editid').value = id;
	}

	window.editNeedBet = function editNeedBet(id, mob) {
		$('#needBetMob').text('Mobile: ' + mob);
		$('#need_bet_userid').val(id);
		$('#need_bet_amount').val('');
		$('#needBetSummary').text('Loading...');
		$('#needBetModal').modal({backdrop: 'static', keyboard: false});
		$('#needBetModal').modal('show');

		$.getJSON('update_user_need_bet.php', { userid: id }, function(res) {
			if (!res.ok) {
				$('#needBetSummary').text(res.message || 'Could not load wagering info.');
				return;
			}
			var d = res.data;
			$('#need_bet_amount').val(d.amountofCode === 0 || d.amountofCode === '0' ? '0' : d.amountofCode);
			var zeroNote = (parseFloat(d.amountofCode) === 0)
				? '<br><em class="text-warning">Shows 0 because turnover is met or no requirement. You can still set a new amount below.</em>'
				: '';
			$('#needBetSummary').html(
				'Required wager: <strong>' + d.requiredWager + '</strong> &nbsp;|&nbsp; ' +
				'Total bet: <strong>' + d.totalBet + '</strong> &nbsp;|&nbsp; ' +
				'Current need to bet: <strong>' + d.amountofCode + '</strong>' + zeroNote
			);
		}).fail(function() {
			$('#needBetSummary').text('Could not load wagering info.');
		});
	};
	
	$(document).ready(function () {
		$("#type").on('submit',(function(e) {
			e.preventDefault();
			var amount = $('input#amount').val();
			if (amount === "") {
				$("input#amount").focus();
				$('#amount').css({'border-color': '#f00'});
				return false;
			}						
			$.ajax({
				type: "POST", 
				url: "updatewalletNow.php",              
				data: new FormData(this), 
				contentType: false,       
				cache: false,             
				processData:false,       
				success: function(html) {
					if (html == 1) {
						alert("Amount updated successfully.");			
						$("#type")[0].reset();
						$('#excel').modal('hide');
						$('#example1').DataTable().ajax.reload(null, false); // Reload table data without resetting pagination
					} else { 
						alert("An error occurred. Please try again.");						
					}			
				}
			});	
		}));			
	});

	$(document).ready(function () {
		$("#needBetForm").on('submit', function(e) {
			e.preventDefault();
			var needBet = $('#need_bet_amount').val();
			if (needBet === '' || needBet === null || isNaN(needBet) || parseFloat(needBet) < 0) {
				$('#need_bet_amount').css({'border-color': '#f00'}).focus();
				return false;
			}
			$.ajax({
				type: "POST",
				url: "update_user_need_bet.php",
				data: $(this).serialize(),
				dataType: 'json',
				success: function(res) {
					if (res.ok) {
						alert(res.message || "Need to bet updated successfully.");
						$('#needBetModal').modal('hide');
						$('#example1').DataTable().ajax.reload(null, false);
					} else {
						alert(res.message || "An error occurred. Please try again.");
					}
				},
				error: function() {
					alert("An error occurred. Please try again.");
				}
			});
		});
	});
	
	function delete_row(Id) {
		if (confirm("Are you sure you want to delete this user?")) {
			$.ajax({
				type: "Post",
				data:"id=" + Id + "&type=delete" ,
				url: "manage_userAction.php",
				success: function (html) { 
					if(html == 1){
						alert("User deleted successfully.");
						$('#example1').DataTable().ajax.reload(null, false);
					}
					else {
						alert("A technical problem occurred. Could not delete user.");							  
					}
				}
			});
		}
	}
	
	function Respond(Id) {
		if (confirm("Are you sure you want to Unpublish this user?")) {
            $.ajax({
                type: "Post",
                data:"id=" + Id + "&type=chk",
                url: "manage_userAction.php",
                success: function (html) {
                    $('#example1').DataTable().ajax.reload(null, false);
                }
            });
        }
    }
	
	function UnRespond(Id) {
	    if (confirm("Are you sure you want to Publish this user?")) {
            $.ajax({
                type: "Post",
                data:"id=" + Id + "&type=unchk",
                url: "manage_userAction.php",
                success: function (html) {
                    $('#example1').DataTable().ajax.reload(null, false);
                }
            });
        }
    }
	  
	function toggleFreeze(id, status) {
        var action = status == 1 ? 'Freeze' : 'Unfreeze';
        if (confirm("Are you sure you want to " + action + " this user's account?")) {
            $.ajax({
                url: 'toggle_freeze.php',
                type: 'POST',
                data: { id: id, status: status },
                success: function(response) {
                    if(response == "success") {
                        alert("User status updated successfully.");
                        $('#example1').DataTable().ajax.reload(null, false);
                    } else {
                        alert("Failed to update status. " + response);
                    }
                }
            });
        }
    }

  </script>
</body>

</html>