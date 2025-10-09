<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/common_functions.php");
	include("../include/function_database_query.php");
	
	$cnt=1;
	$que="select * from tbl_request_product where perent_id!=0 and status!=2";
	$sel=$dbcon->query($que);
	while($row=brp_mysqli_fetch_array($sel))
	{

		$que1="select * from tbl_request_product where rp_id=".$row['perent_id'];
		$sel1=$dbcon->query($que1);
		$row1=brp_mysqli_fetch_array($sel1);
		$cal=$row['rp_req_qty']/$row1['rp_req_qty'];

		$info['req_qty_one'] = $cal;	
			$updateid=update_record('tbl_request_product', $info,"rp_id=".$row['rp_id'] , $dbcon);
	}
?>