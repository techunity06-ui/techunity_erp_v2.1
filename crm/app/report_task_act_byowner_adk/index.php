<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
$incPath = $path . 'include/';
include_once($incPath . "common_send_email.php");
//print_r($_POST);
//print_r($_FILES);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	if(strtolower($POST['mode']) == "generate_report") {
		$s_date=explode(' - ',$POST['date']);
		$str='';$whr='';
		$str.='<table class="display table table-bordered table-striped">
				<thead>
					<tr>
						<th>Sr. No.</th>
						<th>Owner</th>				  
						<th>Assign Users</th>	
						<th>Created Date</th>				  
						<th>Modified Date</th>	
						<th>Company Name</th>			  
						<th>Address</th>			  
						<th>City</th>			  
						<th>State</th>			  
						<th>Contact Person</th>			  
						<th>Mobile</th>			  
						<th>Email</th>			  
						<th>Type</th>				  
						<th>Due Date</th>				  
						<th>Task Status</th>				  
						<th>Completion Date</th>				  
									  
						<th>Owner Remarks</th>				  
						<th>Actions</th>		  
					</tr>
				</thead>
				<tbody>';
	
	if($POST['task_status']!=1){
		$whr.=" and DATE_FORMAT(task.task_due_date,'%Y-%m-%d') between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";
	}else{
		$whr.=" and DATE_FORMAT(task.task_completion_date,'%Y-%m-%d') between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";
	}
	
	
	if($POST['user_id']){
		/*$user_id = $POST['user_id'];
        $fis = check_crm_find_in_set_new($dbcon, $user_id, 1);
        $whr .= " and FIND_IN_SET (" . $user_id . ",task.show_user_ids)";*/
		$whr.=' and task.user_id='.$POST['user_id'];
	}
	if($POST['task_type_id']){
		$whr.=' and task.task_type_id='.$POST['task_type_id'];
	}
	if($POST['task_status']){
		$whr.=' and task.task_status='.$POST['task_status'];
	}
	if($POST['task_rel_id']){
		$whr.=' and task.task_rel_id in('.$POST['task_rel_id'].')';
	}
	
	
	
$c=1;
$qry="SELECT DISTINCT task.*,usr.user_name,task_sub.mcd_name as task_sub_name,rel.task_rel_name,(select GROUP_CONCAT(user_name) from users where find_in_set(user_id,task.assign_user_ids)) as assign_users, cus.cust_name, addr.c_add_address, state.state_name, city.city_name,con.c_con_fname,con.c_con_lname,con.c_con_mobile,con.c_con_email,
(
    CASE
    	 WHEN task.task_rel_id=1 then task.task_name
    	 WHEN task.task_rel_id=2 then task.task_name
    	 WHEN task.task_rel_id=3 then (SELECT c_con_fname from tbl_cust_contact WHERE c_con_id=task.c_con_id)
    	 WHEN task.task_rel_id=4 then (SELECT cust_name from tbl_customer WHERE cust_id=task.cust_id)
    	 WHEN task.task_rel_id=5 then (SELECT inquiry_no from tbl_inquiry WHERE inquiry_id=task.inquiry_id)
    END
) as rel_name
from tbl_task as task
left join tbl_inquiry as inq on inq.inquiry_id = task.inquiry_id
left join tbl_customer as cus on cus.cust_id = inq.cust_id
left join tbl_cust_contact as con on con.c_con_id = inq.c_con_id
left join tbl_cust_address as addr on addr.cust_id = inq.cust_id and addr.c_addr_defult=1
left join country_mst as coun on coun.countryid = addr.c_add_country
left join state_mst as state on state.stateid = addr.c_add_state
left join city_mst as city on city.cityid = addr.c_add_city
left join users as usr on usr.user_id=task.user_id
left join tbl_master_category_detail as task_sub on task_sub.mcd_id=task.task_type_id
left join task_rel_mst as rel on rel.task_rel_id=task.task_rel_id
WHERE task.task_status!=2 and task.entry_type=1 AND task.company_id IN (0,".$_SESSION['company_id'].")".$whr." and task.task_type_id !=14 Group by task.task_id order by task.create_date";

/*echo $qry;*/

	$qry_rs=$dbcon->query($qry);
	if(mysqli_num_rows($qry_rs)){
		while($rel=mysqli_fetch_assoc($qry_rs)){
			$task_due_date='';$task_completion_date='';
			if($rel['task_due_date']!='1970-01-01 00:00:00' && $rel['task_due_date']!='0000-00-00 00:00:00'){
				$task_due_date=date("d-m-Y h:i A",strtotime($rel['task_due_date']));
			}
			if($rel['task_completion_date']!='1970-01-01 00:00:00' && $rel['task_completion_date']!='0000-00-00 00:00:00'){
				$task_completion_date=date("d-m-Y h:i A",strtotime($rel['task_completion_date']));
			}
			if($rel['task_status']=='1'){ 
				$task_status="<strong style='color:#5cb85c;'>Completed</strong>";
			}
			else{
				$task_status="<strong  style='color:#d43f3a;'>Pending</strong>";
			}

			$contact_person = "select c_con_fname,c_con_lname,c_con_mobile,c_con_email from tbl_cust_contact where cust_id=".$rel['cust_id']." and c_con_status=0 order by c_con_id desc limit 1";
			$contact_per = brp_mysqli_fetch_array($dbcon->query($contact_person));
			//$view_hist_btn = '<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'task_edit/'.$rel['inquiry_id'].'"><i class="fa fa-eye"></i></a>';
			$view_hist_btn = '<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'task_list"><i class="fa fa-eye"></i></a>';
			$str.='<tr>
				<td class="text-left">'.$c.'</td>
				<td class="text-left">'.$rel['user_name'].'</td>
				<td class="text-left">'.$rel['assign_users'].'</td>
				<td class="text-left" style="white-space:nowrap;">'.date("d-m-Y h:i A",strtotime($rel['create_date'])).'</td>
				<td class="text-left" style="white-space:nowrap;">'.date("d-m-Y h:i A",strtotime($rel['cdate'])).'</td>
				<td class="text-left">'.$rel['cust_name'].'</td>
				<td class="text-left">'.$rel['c_add_address'].'</td>
				<td class="text-left">'.$rel['city_name'].'</td>
				<td class="text-left">'.$rel['state_name'].'</td>
				<td class="text-left">'.$rel['c_con_fname'].' '.$rel['c_con_lname'].'</td>
				<td class="text-left">'.$rel['c_con_mobile'].'</td>
				<td class="text-left">'.strtolower($rel['c_con_email']).'</td>
				<td class="text-left" style="white-space:nowrap;">'.$rel['task_rel_name'].'</td>
				<td class="text-left" style="white-space:nowrap;">'.$task_due_date.'</td>
				<td class="text-left">'.$task_status.'</td>
				<td class="text-left" style="white-space:nowrap;">'.$task_completion_date.'</td>
				
				<td class="text-left">'.$rel['task_remark'].'</td>
				<!--<td class="text-left">
					<table class="display table table-bordered table-striped">
						<tr>
							<th>Sr.</th>
							<th>User Name</th>
							<th>Remarks Date</th>
							<th>Remarks</th>
						</tr>-->';
						
			/*$trn_qry="select flp.*,usr.user_name from tbl_followup as flp
				left join users as usr on usr.user_id=flp.user_id
				where flp.flp_status=0 and flp.task_id=".$rel['task_id'];
			$trn_qry_rs=$dbcon->query($trn_qry);
			$t=1;
			if(mysqli_num_rows($trn_qry_rs)){
				while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
					$str.='<tr>
						<td class="text-left">'.$t.'</td>
						<td class="text-left">'.$trn_rel['user_name'].'</td>
						<td class="text-left" style="white-space:nowrap;">'.date("d-M-Y h:i A",strtotime($trn_rel['flp_date'])).'</td>
						<td class="text-left">'.nl2br($trn_rel['task_flp_remark']).'</td>
					</tr>';
					$t++;
				}
			}
			else{
				$str.='<tr><td colspan="4" class="text-center">No Data FOUND !!</td></tr>';
			}
			*/
			$str.='<!--</table>
				</td>-->
				<td class="text-left">'.$view_hist_btn.'</td>
			</tr>';
			$c++;
		}
	}
	else{
		$str.='<tr><td colspan="20" class="text-center">NO DATA FOUND !!!</td></tr>';
	}
		
		$str.='</tbody>				 
			</table>';
		
		$resp['html_resp']=$str;
		echo json_encode($resp);
	}
?>