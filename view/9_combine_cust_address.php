<?php
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/function_database_query.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");

$qry = $dbcon->query("SELECT * FROM tbl_cust_address WHERE c_add_status = 0");
$address = '';
$c_add_ids = '';
while($row = mysqli_fetch_assoc($qry)){
	if(empty($row['c_add_address'])){
		$c_add_street = ($row['c_add_street']) ? ' '.$row['c_add_street'] : '';
		$c_add_zip = ($row['c_add_zip']) ? '-'.$row['c_add_zip'] : '';
		$address=$row['c_add_location'].''.$c_add_street.''.$c_add_zip;
		$dbcon->query("UPDATE tbl_cust_address SET c_add_address = '".$address."' WHERE c_add_id = ".$row['c_add_id']);
		$c_add_ids.=$row['c_add_id'].' -> '.$address.'<br>';
	}
}
echo $c_add_ids;
?>