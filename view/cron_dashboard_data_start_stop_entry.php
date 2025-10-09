<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/common_functions/common_functions.php");
include_once("../include/common_functions/common_production_functions.php");
include("../include/function_database_query.php");
$godown_id = 23;
$query = "SELECT * FROM tbl_allocate_process WHERE cron_status = 0 AND p_status != 2";
$result = $dbcon->query($query);
$cnt = brp_mysqli_num_rows($result);
while($row = brp_mysqli_fetch_array($result)){
	$q1 = "SELECT IFNULL(sum(release_qty),0) as release_qty from tbl_store_request WHERE store_request_status != 2 and p_id = " . $row['p_id'];
	$rw = brp_mysqli_fetch_assoc($dbcon->query($q1));

	$q1 = "SELECT IFNULL(sum(release_qty),0) as release_qty from tbl_store_request WHERE store_request_status != 2 and p_id = " . $row['p_id'];
	$rw = brp_mysqli_fetch_assoc($dbcon->query($q1));

	$total_start_qty=total_process_transaction_qty($dbcon,0,$row['p_id']);
	$total_end_qty=total_process_transaction_qty($dbcon,1,$row['p_id']);
	// $rel_info['material_id'] =  $material_id;;
	$rel_info = array();
	$rel_info['product_id'] = $row['p_product_id'];
	$rel_info['p_id'] = $row['p_id'];
	$rel_info['rp_id'] = $row['p_ref_id'];	
	$release_qty = $rw['release_qty'];
	$rel_info['release_qty'] =  $release_qty;
	$rel_info['release_unit'] = $row['process_unit'];
	
	$rel_info['pending_qty'] = $release_qty - $total_start_qty;
	$rel_info['start_qty'] = $total_start_qty;
	$rel_info['end_qty'] = $total_end_qty;
	
	$rel_info['complete_status'] =  $row['p_status'];
	$rel_info['cdate'] =  $row['cdate'];
	$rel_info['user_id'] =  $row['user_id'];
	$rel_info['company_id'] =  $row['company_id'];
	

	if(!empty($rw['release_qty']) && $rw['release_qty'] > 0){

		$start_stop_id = add_record('tbl_start_stop_production',$rel_info, $dbcon);		
	}

	$dbcon->query("update tbl_allocate_process set cron_status = 1 where p_id = " . $row['p_id']);
}

?>