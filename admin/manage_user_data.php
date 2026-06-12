<?php
require_once(dirname(__DIR__) . '/application/functions2.php');

// Database connection info 
$dbDetails = array(
	'host' => 'localhost',
	'user' => 'josh296689_max',
	'pass' => 'josh296689_max',
	'db' => 'josh296689_max'
);

$wageringConn = mysqli_connect($dbDetails['host'], $dbDetails['user'], $dbDetails['pass'], $dbDetails['db']);
// DB table to use 
// DB table to use 
//$table = 'shonu_subjects'; 
$table = <<<EOT
 (
	SELECT
	  shonu_subjects.mobile,
	  shonu_subjects.code,
	  shonu_subjects.ip,
	  shonu_subjects.id,
	  shonu_subjects.createdate,
	  shonu_subjects.status,
	  shonu_subjects.pwd,
	  shonu_subjects.account_frozen, 
	  shonu_kaichila.motta,
	  (SELECT SUM(motta) FROM thevani WHERE balakedara = shonu_subjects.id and `status`='1') AS total_deposit,
	  (SELECT motta FROM thevani WHERE balakedara = shonu_subjects.id and `status`='1' ORDER BY motta ASC LIMIT 1) AS deposit_motta,
	  (SELECT name FROM bankcard WHERE userid = shonu_subjects.id ORDER BY id ASC LIMIT 1) AS name,
	  (SELECT account FROM bankcard WHERE userid = shonu_subjects.id ORDER BY id ASC LIMIT 1) AS account
	FROM shonu_subjects
	JOIN shonu_kaichila ON shonu_subjects.id = shonu_kaichila.balakedara
 ) temp
EOT;


// Table's primary key 
$primaryKey = 'id';

// Array of database columns which should be read and sent back to DataTables. 
// The `db` parameter represents the column name in the database.  
// The `dt` parameter represents the DataTables column identifier. 
$columns = array(
	array('db' => 'mobile', 'dt' => 0),
	array('db' => 'code', 'dt' => 1),
	array('db' => 'ip', 'dt' => 2),
	array('db' => 'id', 'dt' => 3),
	array(
		'db' => 'account_frozen',
		'dt' => 13,
		'formatter' => function ($d, $row) {
			$id = $row['id'];
			return ($d == 1)
				? '<a href="javascript:void(0);" onclick="toggleFreeze(' . $id . ', 0)" style="color:red;">Yes</a>'
				: '<a href="javascript:void(0);" onclick="toggleFreeze(' . $id . ', 1)" style="color:green;">No</a>';
		}
	),

	array(
		'db' => 'motta',
		'dt' => 4,
		'formatter' => function ($d, $row) {
			$aid = $row['id'];
			$mobile = $row['mobile'];
			$motta = $row['motta'];
			return
				number_format($d, 2) . '&nbsp;<a href="javascript:void(0);" onClick="edit(' . $aid . ',' . $mobile . ',' . $motta . ')" class="text-aqua" title="Delete"><i class="fa fa-edit"></i></a>'
			;
		}
	),
	array(
		'db' => 'total_deposit',
		'dt' => 5,
		'formatter' => function ($d, $row) {
			return
				($d == null) ? '0.00' : number_format($d, 2)
			;
		}
	),
	array(
		'db' => 'deposit_motta',
		'dt' => 6,
		'formatter' => function ($d, $row) {
			return
				($d == null) ? '0.00' : number_format($d, 2)
			;
		}
	),
	array(
		'db' => 'createdate',
		'dt' => 7,
		'formatter' => function ($d, $row) {
			return date('jS M Y', strtotime($d));
		}
	),
	array(
		'db' => 'id',
		'dt' => 8,
		'formatter' => function ($d, $row) use ($wageringConn) {
			$needBet = '0.00';
			if ($wageringConn) {
				$w = computeUserWithdrawalWagering($wageringConn, (int) $row['id']);
				$needBet = number_format($w['amountofCode'], 2);
			}
			$aid = (int) $row['id'];
			$mobileAttr = htmlspecialchars((string) $row['mobile'], ENT_QUOTES, 'UTF-8');
			return $needBet . '&nbsp;<a href="javascript:void(0);" class="text-aqua edit-need-bet" data-userid="' . $aid . '" data-mobile="' . $mobileAttr . '" title="Edit need to bet"><i class="fa fa-edit"></i></a>';
		}
	),
	array(
		'db' => 'status',
		'dt' => 9,
		'formatter' => function ($d, $row) {
			$id = $row['id'];
			return
				($d == 1) ?
				'<a href="javascript:void(0);" onClick="delete_row(' . $id . ')" class="update-person" style="color:#f56954; font-size:16px;" title="Delete"><i class="fa fa-trash"></i></a>
				&nbsp;
				<a href="javascript:void(0);" onClick="Respond(' . $id . ')" class="update-person" style="color:#090; font-size:16px;" data-toggle="tooltip" title="Publish"><i class="fa fa-check-square-o"></i></a>
				<a href="user-details.php?user=' . $id . '"  class="update-person" style="color:#0E0E44; font-size:16px;" data-toggle="tooltip" title="User Deatil"><i class="fa fa-info"></i></a>'
				:
				'<a href="javascript:void(0);" onClick="delete_row(' . $id . ')" class="update-person" style="color:#f56954; font-size:16px;" title="Delete"><i class="fa fa-trash"></i></a>
				&nbsp;
				<a href="javascript:void(0);" onClick="UnRespond(' . $id . ')" class="update-person" style="color:#f00; font-size:16px;" data-toggle="tooltip" title="Unpublish"><i class="fa fa-square-o"></i></a>
				<a href="user-details.php?user=' . $id . '"  class="update-person" style="color:#0E0E44; font-size:16px;" data-toggle="tooltip" title="User Deatil"><i class="fa fa-info"></i></a>';
		}
	),
	array('db' => 'pwd', 'dt' => 10),
	array('db' => 'name', 'dt' => 11),
	array('db' => 'account', 'dt' => 12)
);

// Include SQL query processing class 
require 'ssp_without_quote_table.php';

// Output data as json format 
echo json_encode(
	SSP::simple($_GET, $dbDetails, $table, $primaryKey, $columns)
);
?>