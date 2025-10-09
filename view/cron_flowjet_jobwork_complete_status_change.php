<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_production_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	$company_config = getCompanyConfiguration($dbcon);
	$qry1="select * from tbl_job_work  where  job_work_status !=2 and job_work_type = 2";
	$result1=$dbcon->query($qry1);
    while($rel=brp_mysqli_fetch_assoc($result1)){
    	$qry2="select * from tbl_job_work_trn where  job_work_trn_status !=2 and job_work_id = " . $rel['job_work_id'];
	   	$result2=$dbcon->query($qry2);
	   	while($rel2=brp_mysqli_fetch_assoc($result2)){
	   		$qry4="select * from tbl_job_work_sub_trn where job_work_sub_trn_status = 0 and job_work_trn_id = " . $rel2['job_work_trn_id'];
		   	$result4=$dbcon->query($qry4);
		   	while($rel4 = brp_mysqli_fetch_assoc($result4)){
		   		$grn_query="select IFNULL(sum(grn_sub_trn.product_qty),0) as grn_used_qty from tbl_grn_sub_trn as grn_sub_trn where cron_grn_ven = 1 and grn_sub_trn.status=0 and grn_sub_trn.company_id=".$_SESSION['company_id']." and job_work_sub_trn_id = ".$rel4['job_work_sub_trn_id']." group by grn_sub_trn.job_work_sub_trn_id";
				$grn_result = $dbcon->query($grn_query);
				$grn_row = brp_mysqli_fetch_assoc($grn_result);
				$grn_used_qty = $grn_row['grn_used_qty'];

				if($grn_used_qty >= $rel4['product_base_qty']){
					$info1['grn_complete_status'] = 1;
					update_record('tbl_job_work_sub_trn', $info1,"job_work_sub_trn_id=".$rel4['job_work_sub_trn_id'], $dbcon);

				}else{
					$info1['grn_complete_status'] = 0;
					update_record('tbl_job_work_sub_trn', $info1,"job_work_sub_trn_id=".$rel4['job_work_sub_trn_id'], $dbcon);
				}
	   		}

	   		$jb_trn_qry = "SELECT count(job_work_sub_trn_id) as grn_complete from tbl_job_work_sub_trn where job_work_sub_trn_status != 2 and grn_complete_status = 0 and job_work_trn_id = " . $rel2['job_work_trn_id'];
	   		$jb_trn_res = $dbcon->query($jb_trn_qry);
	   		$jb_trn_rw = brp_mysqli_fetch_assoc($jb_trn_res);

	   		if($jb_trn_rw['grn_complete'] > 0){
	   			$info2['grn_complete_status'] = 0;
					update_record('tbl_job_work_trn', $info2,"job_work_sub_id=".$rel4['job_work_sub_id'], $dbcon);
	   		}else{
	   			$info2['grn_complete_status'] = 1;
					update_record('tbl_job_work_trn', $info2,"job_work_sub_id=".$rel4['job_work_sub_id'], $dbcon);
	   		}
	   	}

	   	$job_qry = "SELECT count(job_work_trn_id) as grn_complete from tbl_job_work_trn where job_work_trn_status != 2 and grn_complete_status = 0 and job_work_id = " . $rel['job_work_id'];
	   		$job_res = $dbcon->query($job_qry);
	   		$job_rw = brp_mysqli_fetch_assoc($job_res);

	   		if($job_rw['grn_complete'] > 0){
	   			$info3['grn_complete_status'] = 0;
					update_record('tbl_job_work', $info3,"job_work_id=".$rel['job_work_id'], $dbcon);
	   		}else{
	   			$info3['grn_complete_status'] = 1;
					update_record('tbl_job_work', $info3,"job_work_id=".$rel['job_work_id'], $dbcon);
	   		}
    }	
?>
<!-- grn_status_update_in_tbl_job_work_sub_trn($dbcon,$row['job_work_sub_trn_id']); -->