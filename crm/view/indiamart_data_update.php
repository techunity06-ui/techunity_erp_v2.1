<?php
session_start(); //start session
$AJAX = true;
include('../include/urlfile.php');

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

$fetch_sql = "select i_id,DATE_TIME_RE from tbl_indiamart_data ";
$fetch_result=$dbcon->query($fetch_sql);
if(brp_mysqli_num_rows($fetch_result) > 0){

	while($row=mysqli_fetch_assoc($fetch_result)){
		$DATE_TIME_RE = $row['DATE_TIME_RE'];
		$DATE_TIME_CURRENT_UPDATE = date('Y-m-d H:i:s', strtotime($DATE_TIME_RE));
		$info['DATE_TIME_CURRENT_UPDATE']=$DATE_TIME_CURRENT_UPDATE;
		$updateid=update_record('tbl_indiamart_data', $info, "i_id=".$row['i_id'] , $dbcon);
	}
}
?>	