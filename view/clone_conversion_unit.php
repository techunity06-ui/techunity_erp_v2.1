<?php 
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");

	$query = "select * from tbl_invoicetrn where trancation_status!=2";
	$result  = $dbcon->query($query);
	while($row = brp_mysqli_fetch_array($result)){
		$product_detail = get_product_detail($dbcon,$row['product_id']);
		$type="conv_unit";
		$ret_qty=convert_stock($dbcon,$row['product_qty'],$row['product_id'],$type);
		//$ret_qty=convert_stock($dbcon,'1000',3233,$type);
		
		$info['rate_unit']			= $row['unit_id'];
		$info['conv_unit_id']		= $product_detail['product_conv_unit'];
		$info['product_conv_qty']	= $ret_qty;
		
		$updateid=update_record("tbl_invoicetrn", $info, "trancation_id=".$row['trancation_id'], $dbcon);
	}

	$query1 = "select * from tbl_proforma_trn where trancation_status!=2";
	$result1  = $dbcon->query($query1);
	while($row1 = brp_mysqli_fetch_array($result1)){
		$product_detail1 = get_product_detail($dbcon,$row1['product_id']);
		$type1="conv_unit";
		$ret_qty1=convert_stock($dbcon,$row1['product_qty'],$row1['product_id'],$type1);
		//$ret_qty=convert_stock($dbcon,'1000',3233,$type);
		
		$info1['rate_unit']			= $row1['unit_id'];
		$info1['conv_unit_id']		= $product_detail1['product_conv_unit'];
		$info1['product_conv_qty']	= $ret_qty1;
		//var_dump($info1);
		$updateid1=update_record("tbl_proforma_trn", $info1, "trancation_id=".$row1['trancation_id'], $dbcon);
	}
?>