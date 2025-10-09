<?php
/*
	old bom to new bom (version wise bom ) convert cloan
	pro_ms_bom_version new entry 
	pro_bom_process  new entry
*/
session_start();
include_once("../config/config.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
include_once("../config/session.php");
include_once("../include/function_database_query.php");

	echo $query="select bom.* from pro_ms_bom_version as bom
				where bom.bom_conv_qty='0.00' AND  bom.bom_conv_qty='0' AND bom.bom_conv_qty='' ";
		$result=$dbcon->query($query);
		$i=1;
		while($row=brp_mysqli_fetch_assoc($result)){

			$qry="select * from product_mst where product_id = " . $row['product_id'];
			$result1=$dbcon->query($qry);
			$row1=brp_mysqli_fetch_assoc($result1);

			$info['bom_unit']		= $row1['product_base_unit'];
			$info['bom_conv_unit']		= $row1['product_conv_unit'];

			$type="conv_qty";
			$conv_qty=convert_stock($dbcon,$row['bom_unit_qty'],$row['product_id'],$type);
			
			$info['bom_conv_qty'] = $conv_qty;
			
			$updateid11=update_record('pro_ms_bom_version', $info, "bom_version_id=".$row['bom_version_id'] , $dbcon);
			
			// echo "<pre>";
			// print_r($info);
		

		$i++;
	}
	
	

?>