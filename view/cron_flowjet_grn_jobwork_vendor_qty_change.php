<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_production_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	$company_config = getCompanyConfiguration($dbcon);
	$qry1="select * from tbl_grn where grn_no='GRN/OUT/355/23-24'";
	$result1=$dbcon->query($qry1);
    while($rel=brp_mysqli_fetch_assoc($result1)){
    	
        $qry2="select * from tbl_grn_trn where grn_trn_status = 0 and grn_id= " . $rel['grn_id'];
	   	$result2=$dbcon->query($qry2);
	   	while($rel2=brp_mysqli_fetch_assoc($result2)){
	   		$qry3="select * from tbl_grn_sub_trn where status = 0 and cron_grn_ven = 0 and grn_trn_id= " . $rel2['grn_trn_id'];
		   	$result3=$dbcon->query($qry3);
		   	while($rel3=brp_mysqli_fetch_assoc($result3)){
		   		$product_qty = $rel3['product_qty'];
		   		$product_conv_qty = $rel3['product_conv_qty'];
		   		$qry4="select p_id from tbl_job_work_sub_trn where job_work_sub_trn_status = 0 and job_work_sub_trn_id = " . $rel3['job_work_sub_trn_id'];
		   		$result4=$dbcon->query($qry4);
		   		$rel4=brp_mysqli_fetch_assoc($result4);
		   	
		   		 $qry5="select strn.*,trn.job_work_id,job.vender_id from tbl_job_work_sub_trn as strn
		   				left join tbl_job_work_trn as trn on trn.job_work_trn_id = strn.job_work_trn_id
		   				left join tbl_job_work as job on trn.job_work_id = job.job_work_id
		   				 where  cron_grn_ven = 0 and  job_work_sub_trn_status = 0 and p_id = " . $rel4['p_id'];
		   		$result5=$dbcon->query($qry5);
		   		while($rel5=brp_mysqli_fetch_assoc($result5)){
		   			var_dump($rel2['vender_id']);
		   			var_dump($rel5['vender_id']);
	   				if($rel['vender_id'] == $rel5['vender_id']){
	   					if($product_qty > 0){
	   						echo "<br><br>";
	   						echo $grn_query="select IFNULL(sum(grn_sub_trn.product_qty),0) as grn_used_qty from tbl_grn_sub_trn as grn_sub_trn where cron_grn_ven = 1 and grn_sub_trn.status=0 and grn_sub_trn.company_id=".$_SESSION['company_id']." and job_work_sub_trn_id = ".$rel['job_work_sub_trn_id']." group by grn_sub_trn.job_work_sub_trn_id";
	   						$grn_result = $dbcon->query($grn_query);
	   						$grn_row = brp_mysqli_fetch_assoc($grn_result);
	   						$grn_used_qty = $grn_row['grn_used_qty'];

	   						$job_work_pen_qty = $rel5['product_base_qty'] - $grn_used_qty;

	   						if($job_work_pen_qty > 0){
	   							$info['job_work_sub_trn_id'] = $rel5['job_work_sub_trn_id'];
	   							$info['job_work_trn_id'] = $rel5['job_work_trn_id'];
	   							$info['jobwork_id'] = $rel5['job_work_id'];
	   							$info['cron_grn_ven'] = 1;

	   							update_record('tbl_grn_sub_trn', $info,"grn_trn_sub_id=".$rel3['grn_trn_sub_id'], $dbcon);
	   							$product_qty = 0;
	   							if($job_work_pen_qty - $pending_qty <= 0){
	   								$info1['cron_grn_ven'] = 1;
	   								$info1['grn_complete_status'] = 1;
	   								update_record('tbl_job_work_sub_trn', $info1,"job_work_sub_trn_id=".$rel5['job_work_sub_trn_id'], $dbcon);

	   							}
	   						}
	   					}
	   				}
		   		}
		   	}
	   	}
    }
?>
<!-- grn_status_update_in_tbl_job_work_sub_trn($dbcon,$row['job_work_sub_trn_id']); -->