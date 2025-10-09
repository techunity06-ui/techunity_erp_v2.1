<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
$getspecialConfiguration=getspecialConfiguration($dbcon);
	if(strtolower($POST['mode']) == "generate_report") {
		$s_date=explode(' - ',$POST['date']);
		$str='';$whr='';$hav='';
		$str.='<table class="display table table-bordered table-striped">
				<thead>
					<tr>
						<th style="white-space:nowrap;">Sr. No.</th>
						<th>Date</th>				  
						<th style="white-space:nowrap;">Incharge (User Name)</th>				  
						<th>New/Old</th>				  
						<th>In/Out Call</th>				  
						<th style="white-space:nowrap;">Stages</th>				  
						<th style="white-space:nowrap;">Company Name</th>				  
						<th style="white-space:nowrap;">Deal Status</th>				  
						<th style="white-space:nowrap;">Next Followup</th>				  
					</tr>
				</thead>
				<tbody>';
	
	$whr.=" and DATE(task.create_date) between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";


	
	$qry="SELECT SQL_CALC_FOUND_ROWS task.task_id, task.task_rel_id, task.task_name, tea.t_name, inq.inquiry_no, inq.inquiry_name, inq.inquiry_date, cust.cust_name, per.c_con_fname, row.task_rel_name, state.state_name, city.city_name, task.task_due_date, task.task_remark, usr.user_name, task.task_rel_id, task.assign_user_ids, task.inquiry_id, task.task_type_id, task.task_status, task.entry_type, task.alert_date_time, task.create_date, task.task_due_date, type.mcd_name as type_name, task.user_id, task.task_priority_id, task_sub.mcd_name as task_sub_name, stage.opp_stage, stage.opp_probability, qt_aprv.approve_status, qt_aprv.quotation_id, qt_aprv.quotation_no, type.mcd_id, cust.cust_mobile, mcd.mcd_name as cust_ty, inq.closing_date, task.task_in_out 

FROM tbl_task as task 

left join users as usr on usr.user_id=task.user_id 
left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id 
left join tbl_master_category_detail as task_sub on task_sub.mcd_id=task.task_type_id 

left join task_rel_mst as row on row.task_rel_id=task.task_rel_id 
left join (SELECT cust_id as c_id,inquiry_id,inquiry_no,inquiry_name, inquiry_date, closing_date,inquiry_type_id from tbl_inquiry) as inq on inq.inquiry_id=task.inquiry_id 
left join (SELECT inquiry_id,approve_status,quotation_id,quotation_no FROM `tbl_quotation` WHERE quotation_status=0 and revise_status=0) as qt_aprv on qt_aprv.inquiry_id=task.inquiry_id 
left join tbl_inquiry_trn as tr on tr.inquiry_id=inq.inquiry_id 
left join tbl_customer as cust on cust.cust_id=inq.c_id 
left join state_mst as state on state.stateid=(SELECT c_add_state FROM `tbl_cust_address` WHERE cust_id=inq.c_id and c_add_status=0 limit 1) 
left join city_mst as city on city.cityid=(SELECT c_add_city FROM `tbl_cust_address` WHERE cust_id=inq.c_id and c_add_status=0 limit 1) 
left join tbl_opportunity_mst as stage on stage.opp_id=(SELECT opp_id from tbl_inquiry where inquiry_id=task.inquiry_id) left join tbl_cust_contact as per on per.c_con_id=task.c_con_id 
left join tbl_master_category_detail as mcd on mcd.mcd_id=inq.inquiry_type_id 
left join territory_mst as tea on tea.t_id=cust.t_id 

where  task.task_status=0 and task.entry_type=1 and task.company_id in (0,".$_SESSION['company_id'].") ".$whr." and task.task_type_id != '14' and FIND_IN_SET (1,task.show_user_ids) Group by task.task_id ORDER BY task.task_id";
	$qry_rs=$dbcon->query($qry);
	if(mysqli_num_rows($qry_rs)){
		$i=1;
		while($rel=mysqli_fetch_assoc($qry_rs)){
			$create_date='';
			if($rel['create_date']!='1970-01-01' && $rel['create_date']!='0000-00-00'){
				$create_date=date("d-m-Y",strtotime($rel['create_date']));
			}

			$task_due_date='';
			if($rel['task_due_date']!='1970-01-01' && $rel['task_due_date']!='0000-00-00'){
				$task_due_date=date("d-m-Y",strtotime($rel['task_due_date']));
			}
			
			$task_in_out = "";
			if($rel['task_in_out'] == '0'){
				$task_in_out = "OUT";
			}else{
				$task_in_out = "IN";	
			}

			$str.='<tr>
				<td class="text-left">'.$i.'</td>
				<td class="text-left" style="white-space:nowrap;">'.$create_date.'</td>
				<td class="text-left" >'.$rel['user_name'].'</td>
				<td class="text-left" style="white-space:nowrap;">'.$rel['cust_ty'].'</td>
				<td class="text-left">'.$task_in_out.'</td>
				<td class="text-left">'.$rel['type_name'].'</td>
				<td class="text-left">'.$rel['cust_name'].'</td>
				<td class="text-left">'.$rel['task_remark'].'</td>
				<td class="text-left">'.$task_due_date.'</td>
			</tr>';
			$i++;
		}
	}
	else{
		$str.='<tr><td colspan="9" class="text-center">NO DATA FOUND !!!</td></tr>';
	}
		
		$str.='</tbody>				 
			</table>';
		
		$resp['html_resp']=$str;
		echo json_encode($resp);
	}
?>