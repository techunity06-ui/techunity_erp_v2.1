<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_production_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	$company_config = getCompanyConfiguration($dbcon);
	$qry="select * from tbl_batch_data  where  status !=2 and base_qty = conv_qty and base_unit != conv_unit";
	$result=$dbcon->query($qry);
    while($rel=brp_mysqli_fetch_assoc($result)){
    	$grn_qry = "select * from tbl_grn_trn where grn_trn_id = " . $rel['grn_trn_id'];
    	$grn_result=$dbcon->query($grn_qry);
    	$grn_row = brp_mysqli_fetch_assoc($grn_result);

    	var_dump($grn_row['product_qty']);
    	var_dump($grn_row['product_conv_qty']);
    	$bt_info['base_qty'] = $grn_row['product_qty'];
    	$bt_info['conv_qty'] = $grn_row['product_conv_qty'];
    	update_record('tbl_batch_data', $bt_info,"batch_id=".$rel['batch_id'], $dbcon);

    	$stock_qry = "select * from tbl_stock_trn where stock_status !=2 and ref_name = 'tbl_grn_trn' and ref_id = " . $rel['grn_trn_id'] . " and batch_id = " . $rel['batch_id'];
    	$stock_result=$dbcon->query($stock_qry);
    	$stock_row = brp_mysqli_fetch_assoc($stock_result);


    	$tbl_stock_trn['base_stock'] = $grn_row['product_qty'];
    	$st_info['convert_stock'] = $grn_row['product_conv_qty'];

    	update_record('tbl_stock_trn', $st_info,"stock_id=".$stock_row['stock_id'], $dbcon);
    }	
?>
<!-- grn_status_update_in_tbl_job_work_sub_trn($dbcon,$row['job_work_sub_trn_id']); -->