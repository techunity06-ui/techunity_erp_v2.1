<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	
		$qry2="SELECT product_id FROM `product_mst` as potrn WHERE 1";
		$result2=$dbcon->query($qry2);
		while($rel1=brp_mysqli_fetch_assoc($result2))
		{
			
			$qry4="SELECT sum(base_stock) as cstock FROM `tbl_stock_trn` as g_trn
					WHERE ref_name='opening_stock' and stock_flage=1 and stock_status=0 and product_id=".$rel1['product_id'];
			$result4=$dbcon->query($qry4);
			$rel4=brp_mysqli_fetch_assoc($result4);
			
			$qry_up11="UPDATE `product_mst` SET `product_opening` = ".$rel4['cstock']." WHERE product_id=".$rel1['product_id'];
				$result_up11=$dbcon->query($qry_up11);
				
				
		}
	

?>
