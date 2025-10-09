<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	
		$qry2="SELECT potrn.*,pmst.product_base_unit,pmst.product_conv_unit FROM `tbl_purchaseordertrn` as potrn
				left join product_mst as pmst on pmst.product_id=potrn.product_id
				WHERE conv_unit_id=''";
		$result2=$dbcon->query($qry2);
		while($rel1=brp_mysqli_fetch_assoc($result2)){
			if($rel1['product_base_unit']!=$rel1['product_conv_unit']){
				$type="base_unit";
				$ret_qty=convert_stock($dbcon,$rel1['product_qty'],$rel1['product_id'],$type);
			
				$qry="UPDATE `tbl_purchaseordertrn` SET `product_conv_qty` = '".$ret_qty."',conv_unit_id=".$rel1['product_base_unit']." WHERE `purchaseordertrn_id` = ".$rel1['purchaseordertrn_id'];
				$result=$dbcon->query($qry);
			}else{
				$qry="UPDATE `tbl_purchaseordertrn` SET `product_conv_qty` = '".$rel1['product_qty']."',conv_unit_id=".$rel1['unit_id']." WHERE `purchaseordertrn_id` = ".$rel1['purchaseordertrn_id'];
				$result=$dbcon->query($qry);
			}
		}
	

?>
