<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	
	 $qry1="select * from tbl_wororder_product_process as grn
			where group by process_priority,rp_id,process_type,process_id,product_id";
	$result1=$dbcon->query($qry1);
	while($rel=brp_mysqli_fetch_assoc($result1)){
		$qry="select GROUP_CONCAT(pr_process_id) as pid,GROUP_CONCAT(rp_id) as rp_idn from tbl_wororder_product_process as grn
			where pr_process_id!=".$rel['pr_process_id']." and process_priority=".$rel['process_priority']." and rp_id=".$rel['rp_id']." and process_type=".$rel['process_type']." and process_id=".$rel['process_id']." and product_id=".$rel['product_id'];
		$result=$dbcon->query($qry);
		$rel1=brp_mysqli_fetch_assoc($result);

		$query_invoicetype = $dbcon->query("DELETE FROM `tbl_wororder_product_process` WHERE pr_process_id in (".$rel1['pid'].")");

			if(!empty($rel1['pid'])){
				echo $rel1['rp_idn'];
				echo " -- ".$rel1['pid']." </br>";
			}
			
	}
				

?>
