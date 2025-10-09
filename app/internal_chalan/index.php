<?php

session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(brp_strtolower($POST['mode']) == "create_internal_chalan") {
	// p($POST); 
	$isSent = false;
	for($i = 0; $i <= count($POST['total_qty']); $i++) {
		if($POST['total_qty'][$i] > 0) {
			$sp_id = $POST['sp_id'][$i];

			$query = "SELECT id FROM tbl_internal_chalan
			WHERE complaint_id = '".$POST['complaint_id']."' AND sp_id = '$sp_id'";
			
			$rs_type = $dbcon->query($query);
			$totalRecords = brp_mysqli_num_rows($rs_type);
			//check if already exist same entry then skip it
			if($totalRecords <= 0) {
				$info['int_chalan_no'] = $POST['int_chalan_no'];
				$info['complaint_id'] = $POST['complaint_id'];
				$info['sp_id'] = $sp_id; 
				$info['sp_name'] = $POST['sp_name'][$i];
				$info['req_qty'] = $POST['req_qty'][$i];
				$info['total_qty'] = $POST['total_qty'][$i];
				$info['status']	= 'sent';
				$info['user_id'] = $_SESSION['user_id']; 
				$info['company_id']	= $_SESSION['company_id'];

				$insertid = add_record('tbl_internal_chalan', $info, $dbcon);

				if($insertid) {
					$isSent = true;
				}
			}
		}
	}
	
	updateSeries($dbcon, 'id', 'tbl_internal_chalan', 'INTERNAL CHALAN');
	$row['res'] = ($isSent) ? '1' : '0';	

	echo brp_json_encode($row);	
}
else if(brp_strtolower($POST['mode']) == "edit_internal_chalan") {
	$isSent = false;
	for($i = 0; $i <= count($POST['total_qty']); $i++) {
		$received_qty = $POST['received_qty'][$i];
		$return_qty = $POST['return_qty'][$i];

		if($received_qty > 0 || $return_qty > 0) {
			$info['sp_id'] = $POST['sp_id'][$i];
			$info['received_qty'] = $received_qty;
			$info['return_qty'] = $return_qty;
			$info['status']	= 'receive';
			$info['user_id'] = $_SESSION['user_id']; 
			$info['company_id']	= $_SESSION['company_id'];

			$updateid = update_record('tbl_internal_chalan', $info, 'sp_id = '.$POST['sp_id'][$i].' AND complaint_id = '.$POST['complaint_id'], $dbcon);

			if($updateid) {
				$isSent = true;
			}
		}
	}
	
	updateSeries($dbcon, 'id', 'tbl_internal_chalan', 'INTERNAL CHALAN');
	$row['res'] = ($isSent) ? '2' : '0';	

	echo brp_json_encode($row);	
}
?>