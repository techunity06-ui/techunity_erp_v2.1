<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
// error_reporting(E_ALL);

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    PRODUCTION_PENDING_JOBCARD_SLUG_CREATE
	]);
	if($_POST != NULL) {
		$POST = bulk_filter($dbcon,$_POST);
	}
	else {
		$POST = bulk_filter($dbcon,$_GET);
	}

$company_config = getCompanyConfiguration($dbcon);

	if(brp_strtolower($POST['mode']) == "generate_report") {

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$check_branch = check_branch('job', $branch_id);


				// $s_date=date('Y-m-d',strtotime($POST['date']));
				$branch=$POST['branch_id'];
				$st_type=$POST['st_type'];
				
				$where='';
				
				
			if(!empty($POST['product_id'])){
				$ser_pro=" and job_trn.product_id=".$POST['product_id'];
			}
			if(!empty($POST['vender_id'])){
				$ser_ven=" and job.vender_id=".$POST['vender_id'];
			}

			if($company_config['jobwork_grn'] == '0'){
				$query='select GROUP_CONCAT(job.job_work_id) as jobwork_id,GROUP_CONCAT(job.chalan_no) as chalan_no,GROUP_CONCAT(res.job_card_no) as job_card_no,GROUP_CONCAT(job_trn.job_work_trn_id) as job_trn_id,produ.product_name,produ.product_icode,job_trn.description,tc.cat_name,led.l_name,sum(job_strn.product_base_qty) as total_qty,pmst.process_name from tbl_job_work as job 
				
				left join tbl_job_work_trn as job_trn on job_trn.job_work_id=job.job_work_id
				left join tbl_job_work_sub_trn as job_strn on job_strn.job_work_trn_id=job_trn.job_work_trn_id
				left join tbl_request_product as res on res.rp_id=job_strn.rp_id
				left join product_mst as produ on produ.product_id=job_trn.product_id
				left join tbl_category as tc on tc.cat_id=produ.product_category
				left join tbl_ledger as led on led.l_id=job.vender_id
				left join process_mst as pmst on pmst.process_id=job_trn.process_id
				
				where job.grn_complete_status="0" and job_trn.grn_complete_status="0" '.$ser_pro.' '.$ser_ven.' '. $check_branch .' and job_strn.job_work_sub_trn_status = 0 and job.job_work_type=2 and job.job_work_status="0" and job.is_reprocess = 1 and job_trn.job_work_trn_status=0 and job.company_id='.$_SESSION['company_id'].' group by job_trn.product_base_unit,job_trn.process_id,job_trn.product_id,job.vender_id';
			}else{
				$query='select job.job_work_id as jobwork_id,job.chalan_no as chalan_no,res.job_card_no as job_card_no,GROUP_CONCAT(job_trn.job_work_trn_id) as job_trn_id,produ.product_name,produ.product_icode,job_trn.description,tc.cat_name,led.l_name,sum(job_strn.product_base_qty) as total_qty,pmst.process_name from tbl_job_work as job 
				
				left join tbl_job_work_trn as job_trn on job_trn.job_work_id=job.job_work_id
				left join tbl_job_work_sub_trn as job_strn on job_strn.job_work_trn_id=job_trn.job_work_trn_id
				left join tbl_request_product as res on res.rp_id=job_strn.rp_id
				left join product_mst as produ on produ.product_id=job_trn.product_id
				left join tbl_category as tc on tc.cat_id=produ.product_category
				left join tbl_ledger as led on led.l_id=job.vender_id
				left join process_mst as pmst on pmst.process_id=job_trn.process_id
				
				where job.grn_complete_status="0" and job.is_reprocess = 1 and job_strn.job_work_sub_trn_status = 0 and job_trn.grn_complete_status="0" '.$ser_pro.' '.$ser_ven.' '. $check_branch .' and job.job_work_type=2 and job.job_work_status="0" and job_trn.job_work_trn_status=0 and job.company_id='.$_SESSION['company_id'].' 
				group by job.job_work_id,job_trn.product_id';
			}

			// echo $query;
				
				
			$rs=$dbcon->query($query);
			$str='';$i=1;
			$rel_num_rows=brp_mysqli_num_rows($rs);
			if($rel_num_rows>0){
				while($rel=brp_mysqli_fetch_assoc($rs))
				{
					$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
					$view = '';	
					$job_trn_id=urlencode($rel['job_trn_id']);
					//if(in_array(PRODUCTION_PENDING_JOBCARD_SLUG_CREATE,$bulkAccessArray)){
						$view = '<a class="btn btn-xs btn-success" data-original-title="Add GRN" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_add_reprocess_job/'.$job_trn_id.'" ><i class="fa fa-plus"></i></a>';
					//}	
					
					$used_qty=jobwork_used_qty_using($dbcon,$job_sub_trn_id,$rel['job_trn_id'],$rel['jobwork_id']);
					$pending_qty=$rel['total_qty']-$used_qty;
					$str.='<tr>
								<td style="text-align:center;">'.$i.'</td>
								<td style="text-align:center;">'.$rel['chalan_no'].'</td>
								<td style="text-align:center;">'.$rel['job_card_no'].'</td>
								<td>'.$rel['product_name']." -- (".$rel['product_icode'].")".'<p>'.$rel['description'].'</p></td>
								<td>'.$rel['process_name'].'</td>
								<td>'.$cat_name.'</td>
								<td >'.$rel['l_name'].'</td>
								<td style="text-align:right;">'.$rel['total_qty'].'</td>
								<td style="text-align:right;">'.$pending_qty.'</td>
								<td style="text-align:center;">'.$view.'</td>
							</tr>';
					$i++;					  
				}
			}
			else{
				$str.= '<tr><td colspan="10" style="text-align:center;">No Data Found !!!</td></tr>';
			}
			
			
			//echo $query;
		echo $str;
			
	}
		

?>