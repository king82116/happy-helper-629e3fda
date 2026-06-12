<?php
$conn = mysqli_connect('localhost', 'josh296689_max', 'josh296689_max', 'josh296689_max');

if (!$conn) {
	echo "Error: " . mysqli_connect_error();
	exit();
}

date_default_timezone_set("Asia/Kolkata");
?>