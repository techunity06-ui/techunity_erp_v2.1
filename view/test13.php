<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	
	 $qry1="select item_grade_id,product_id from product_mst as grn
			where grn.product_status!=0";
		$result1=$dbcon->query($qry1);
		while($rel=brp_mysqli_fetch_assoc($result1)){
			
			$qry12="select * from grade_wise_process as grn
			where grn.grade_id=".$rel['item_grade_id'];
			$result12=$dbcon->query($qry12);
			while($rel2=brp_mysqli_fetch_assoc($result12)){
			 			
	 			$info_used['process_priority']		= $rel2['priority'];
	 			$info_used['process_type']			= 1;
	 			$info_used['process_id']			= $rel2['process_id'];
	 			$info_used['product_id']			= $rel['product_id'];
				
				$info_used['cdate']				= date("Y-m-d");
				$info_used['user_id']			= $_SESSION['user_id'];
				$info_used['company_id']		= $_SESSION['company_id'];
				//var_dump($info_used);
				$inserid3=add_record("tbl_product_process", $info_used, $dbcon);
			}			
		
		}
				

?>
