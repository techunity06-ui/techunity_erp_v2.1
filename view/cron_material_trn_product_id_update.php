<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/common_functions/common_functions.php");
include("../include/function_database_query.php");

$query = "SELECT material_trn_id,p_id FROM tbl_material_release_trn WHERE status  = 0 AND product_id = 0";
$result = $dbcon->query($query);
$cnt = brp_mysqli_num_rows($result);
while($row = brp_mysqli_fetch_array($result)){
	$ap_qry = "SELECT p_product_id,previous_process_id FROM tbl_allocate_process WHERE p_id = " . $row['p_id'];

	$ap_result = $dbcon->query($ap_qry);
	$ap_cnt = brp_mysqli_num_rows($ap_result);

	if($ap_cnt > 0){
		while($ap_row = brp_mysqli_fetch_array($ap_result)){
			if($ap_row['previous_process_id'] > 0){
				$dbcon->query("update tbl_material_release_trn set product_id = ".$ap_row['p_product_id']." where material_trn_id = " . $row['material_trn_id']);
			}
		}
	}

} 


?>
