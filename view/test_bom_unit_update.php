<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	
	 $qry1="select product_base_unit,product_conv_unit,product_id from product_mst as grn
			where 1";
		$result1=$dbcon->query($qry1);
		while($rel=brp_mysqli_fetch_assoc($result1)){
			
			$inftrn['bom_unit'] = $rel['product_base_unit'];
			$inftrn['bom_conv_unit'] = $rel['product_conv_unit'];
			$updatetrnid=update_record('pro_ms_bom_version', $inftrn,"product_id=".$rel['product_id'] , $dbcon);

			$inftrn1['product_base_unit'] = $rel['product_base_unit'];
			$inftrn1['product_conv_unit'] = $rel['product_conv_unit'];
			$updatetrnid1=update_record('tbl_bom', $inftrn1,"bom_product=".$rel['product_id'] , $dbcon);

			$inftrn2['product_base_unit'] = $rel['product_base_unit'];
			$inftrn2['product_conv_unit'] = $rel['product_conv_unit'];
			$updatetrnid2=update_record('tbl_bom', $inftrn2,"product_id=".$rel['product_id'] , $dbcon);
		}
				

?>
