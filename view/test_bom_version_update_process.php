<?php
session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	
	
	$query="select * from tbl_bom as bom
			where bom.company_id=".$_SESSION['company_id']." group by bom.bom_version_id";
		$result=$dbcon->query($query);
		$i=1;
		while($row=brp_mysqli_fetch_assoc($result)){
			
			$query1="select * from tbl_product_process as bom
					where bom.status=0 and  bom.product_id=".$row['bom_product'];
			$result1=$dbcon->query($query1);
			while($row1=brp_mysqli_fetch_assoc($result1)){
				
				$info['product_id']			= $row['bom_product'];
				$info['bom_version_id']		= $row['bom_version_id'];
				$info['pr_process_id']		= $row1['pr_process_id'];
				$info['bom_id']				= $row['bom_id'];
				$info['priority']			= $row1['process_priority'];
				$info['process_status']		= 0;
				$info['cdate']				= date("Y-m-d H:i:s");
				$info['user_id']			= $_SESSION['user_id'];
				$info['company_id']			= $_SESSION['company_id'];
				//var_dump($info);
				$inserestimateid=add_record('pro_bom_process', $info, $dbcon);
			}
		$i++;
	}
	
	
?>