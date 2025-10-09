<?php
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/function_database_query.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");

$qry = $dbcon->query("SELECT * FROM tbl_customer WHERE cust_status = 0");
$cust_ids = '';
while($row = mysqli_fetch_assoc($qry)){
	if(empty($row['cust_assign_user'])){

		$cust_assign_user = trim(check_crm_find_in_set_new($dbcon,$row['user_id'],1),",");
		$cust_owner = $row['user_id'];
		$dbcon->query("UPDATE tbl_customer SET cust_owner = '".$cust_owner."', cust_assign_user = '".$cust_assign_user."' WHERE cust_id = ".$row['cust_id']);
		$cust_ids.=$row['cust_id'].' -> '.$cust_assign_user.' => '.$cust_owner.'<br>';
	}
}
echo $cust_ids;

$qryled = $dbcon->query("SELECT * FROM tbl_ledger WHERE l_status = 0");
$l_ids = '';
while($rese = mysqli_fetch_assoc($qryled)){
	if(empty($rese['cust_assign_user'])){

		$cust_assign_users = trim(check_crm_find_in_set_new($dbcon,$rese['user_id'],1),",");
		$cust_owner = $rese['user_id'];
		$dbcon->query("UPDATE tbl_ledger SET cust_assign_user = '".$cust_assign_users."' WHERE l_id = ".$rese['l_id']);
		$l_ids.=$rese['l_id'].' -> '.$cust_assign_users.'<br>';
	}
}
echo $l_ids;
?>