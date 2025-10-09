<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/common_functions.php");
	include("../include/function_database_query.php");
	
	$cnt=1;
	$sel=$dbcon->query("select * from tbl_stock_trn where base_unit=0");
	while($row=brp_mysqli_fetch_array($sel))
	{
		
		
		$sel_tax = $dbcon->query("select product_base_unit,product_conv_unit from product_mst where product_id=".$row['product_id']);
		$r_tax = brp_mysqli_fetch_array($sel_tax);
		
		$info['base_unit']			= $r_tax['product_base_unit'];
		$info['convert_unit']		= $r_tax['product_conv_unit'];
		$updateid=update_record('tbl_stock_trn', $info,"stock_id=".$row['stock_id'] , $dbcon);
		echo $row['stock_id'];
		echo "<br/>";
		
	}
?>