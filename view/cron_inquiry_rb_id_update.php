<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");

	echo "Update rb_id in tbl_inquiry table.....";

	$query = "UPDATE tbl_inquiry AS inq LEFT JOIN tbl_customer AS cust ON cust.cust_id = inq.cust_id SET inq.rb_id = cust.cust_source";
	
	$result = $dbcon->query($query);


	echo $result . " Successfully update rb_id in tbl_inqiry table!";
	exit;




?>
