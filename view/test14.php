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
			
			/*$qry12="select * from grade_wise_process as grn
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
			}*/

			$sql_sub = "select * from tbl_grade_wise_product_parameter as it where it.status=0 and process_id='-1' and item_grade_id=".$rel['item_grade_id'];
				$wo_sub=$dbcon->query($sql_sub);
				while($row_sub=brp_mysqli_fetch_assoc($wo_sub)){
					$info_sub['process_id'] 			= $row_sub['process_id'];
					$info_sub['param_id']				= $row_sub['param_id'];
					$info_sub['param_value']			= $row_sub['param_value'];
					$info_sub['product_id']				= $rel['product_id'];
					$info_sub['tolerance_plus']			= $row_sub['tolerance_plus'];
					$info_sub['tolerance_minus']		= $row_sub['tolerance_minus'];
					$info_sub['unit_id']				= $row_sub['unit_id'];
					$info_sub['cdate'] 					= date("Y-m-d");
					$info_sub['user_id']				= $_SESSION['user_id'];
					$info_sub['company_id']				= $_SESSION['company_id'];
					
					$inserid_pera=add_record("tbl_product_parameter", $info_sub, $dbcon, $branch_id);
					//$arr['param_count']  = 1;
				}			
		
		}
				

?>
