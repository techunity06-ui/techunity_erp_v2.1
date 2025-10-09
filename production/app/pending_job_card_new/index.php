<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    PRODUCTION_PENDING_JOBCARD_SLUG_CREATE
	]);

		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(brp_strtolower($POST['mode']) == "generate_report") {
			$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
		//$branch_id = $POST['branch_id'];	
	$where='';

	
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	
	$where_db = check_branch('jo ', $branch_id);
	$where.="  and jo.cdate>='".date('Y-m-d',strtotime($s_date[0]))."' AND jo.cdate<='".date('Y-m-d',strtotime($s_date[1]))."'".$where_db;
				
		echo $query='select jo.*,led.l_name from tbl_job_work as jo 
				left join tbl_ledger as led on led.l_id=jo.vender_id
				where jo.job_work_type="2" and jo.job_work_status in (0,1) and jo.company_id='.$_SESSION['company_id'];
				
		$rs=$dbcon->query($query);
			$str='';$i=1;
			$rel_num_rows=brp_mysqli_num_rows($rs);
			if($rel_num_rows>0){
				while($rel=brp_mysqli_fetch_assoc($rs))
				{
					
					$edit = '';	
					if(in_array(PRODUCTION_PENDING_JOBCARD_SLUG_CREATE,$bulkAccessArray)){
						$edit = '<a class="btn btn-xs btn-warning" data-original-title="Add GRN" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'job_work_edit_new/'.$rel['job_work_id'].'/'.$rel['vender_id'].'" ><i class="fa fa-pencil"></i></a>';
					}	
					$str.='<tr>
							  <td style="text-align:center;">'.$i.'</td>
							  <td>'.$rel['job_work_no'].'</td>
							  <td >'.date('d M, Y',strtotime($rel['job_work_date'])).'</td>
							  <td >'.$rel['l_name'].'</td>
							  <td style="text-align:right;">'.$rel['g_total'].'</td>
							   <td ></td>
							  <td style="text-align:center;">
								'.$edit.'
							  </td>
							</tr>';
					$i++;					  
				}
			}
			else{
				$str.= '<tr><td colspan="9" style="text-align:center;">No Data Found !!!</td></tr>';
			}
			
			
			//echo $query;
		echo $str;
			
		}

?>