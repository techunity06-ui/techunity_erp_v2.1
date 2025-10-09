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

if(strtolower($POST['mode']) == "fetch") {
	$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
	$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);

	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];

	$where='';
	$where.="  and task.task_due_date between '".date('Y-m-d',strtotime($s_date[0]))."' 
	AND '".date('Y-m-d',strtotime("+1 day", strtotime($s_date[1])))."'";
	if($POST['task_status']!=''){
		$where.=' and task.task_status='.$POST['task_status'];
	}

		//$where.=' and find_in_set('.$_SESSION['user_id'].',task.assign_user_ids)';


	$appData = array();
	$i=1;
	$aColumns = array('task.task_id', 'type.mcd_name', 'regrd.task_rel_name', 'task.task_name', 'task.task_due_date', 'usr.user_name', 'prior.task_priority_name', 'task.task_status','inq.inquiry_no','per.c_con_fname', 'per.c_con_lname', 'cust.cust_name', 'task.task_rel_id', 'task.assign_user_ids', 'task.cdate', 'task.task_completion_date');
	$sIndexColumn = "task.task_id";
	$isWhere = array("task.task_status != 2 and task.entry_type=1".$where.check_user('task'));
	$sTable = "tbl_task as task";
	$isJOIN = array('left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id',
		'left join task_rel_mst as regrd on regrd.task_rel_id=task.task_rel_id',
		'left join users as usr on usr.user_id=task.user_id',
		'left join task_priority_mst as prior on prior.task_priority_id=task.task_priority_id',
		'left join tbl_cust_contact as per on per.c_con_id=task.c_con_id',
		'left join tbl_customer as cust on cust.cust_id=task.cust_id', 
		'left join tbl_inquiry as inq on inq.inquiry_id=task.inquiry_id');
	$hOrder = "task.task_id desc";
	include('../../../include/pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['mcd_name'];
		$row_data[] = $row['task_rel_name'];

			if($row['task_rel_id']=='5'){//Inquiry
				$row_data[] = $inq_name = $row['inquiry_no'];
			}
			else if($row['task_rel_id']=='4'){//Company
				$row_data[] = $inq_name = $row['cust_name'];
			}
			else if($row['task_rel_id']=='3'){//Person
				$row_data[] = $inq_name = $row['c_con_fname'].' '.$row['c_con_lname'];
			}
			else{
				$row_data[] = $inq_name = $row['task_name'];
			}
			
			$row_data[] = '<span style="white-space:nowrap;">'.date("d-M-Y H:i: A",strtotime($row['task_due_date'])).' </span>';
			$row_data[] = $row['user_name'];
			$row_data[] = getTaskAssignNameCommaSeparated($dbcon,$row['assign_user_ids']);
			$row_data[] = $row['task_priority_name'];
			
			if($row['task_status']=='1'){
				$tsk_due_time=strtotime($row['task_due_date']);
				$cur_time=strtotime($row['task_completion_date']);
				$tsk_type='';
				if($tsk_due_time<$cur_time){
					$tsk_type="<label style='background:#d9534f;'>(Delayed)</label>";
				}
				$row_data[] = '<button type="button" class="btn btn-sm btn-success" data-original-title="Task Completed" data-toggle="tooltip" data-placement="top"><i class="fa fa-check"></i> Completed '.$tsk_type.'</button>';
			}
			else{
				$tsk_due_time=strtotime($row['task_due_date']);
				$cur_time=strtotime(date('Y-m-d H:i:s'));
				$tsk_type='';
				if($tsk_due_time<$cur_time){
					$tsk_type="<label style='background:#d9534f;'>(Delayed)</label>";
				}
				$row_data[] = '<button type="button" class="btn btn-sm btn-warning" data-original-title="Task Pending" data-toggle="tooltip" data-placement="top">Pending '.$tsk_type.'</button>';			
			}

			$edit='';$delete='';$task_btn='';$add_flp_btn='';
			if($edit_btn_per) {
				$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'task_edit/'.$row['task_id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if($delete_btn_per) {
				$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_task('.$row['task_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			/*if($row['task_status']=='0'){
				$task_btn='<button class="btn btn-xs btn-success" data-original-title="Click to Complete Task" data-toggle="tooltip" data-placement="top" onClick="change_task_status('.$row['task_id'].',1)"><i class="fa fa-check"></i></button>';
			}
			else if($row['task_status']=='1'){
				$task_btn='<button class="btn btn-xs btn-warning" data-original-title="Click to Open Task" data-toggle="tooltip" data-placement="top" onClick="change_task_status('.$row['task_id'].',0)"><i class="fa fa-ban"></i></button>';
			}*/
			
			$add_flp_btn='<button class="btn btn-xs btn-primary" data-original-title="Click to Add Follow Up" data-toggle="tooltip" data-placement="top" onClick="open_follow_up('.$row['task_id'].',\''.$inq_name.'\')"><i class="fa fa-eye"></i></button>';
			
			$row_data[] = $edit.' '.$delete.' '.$task_btn.' '.$add_flp_btn;

			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {

		$info['task_type_id']		= $POST['task_type_id'];
		$info['task_rel_id']		= $POST['task_rel_id'];
		$info['task_name']			= $POST['task_name'];
		$info['c_con_id']			= $POST['c_con_id'];
		$info['cust_id']			= $POST['cust_id'];
		$info['inquiry_id']			= $POST['inquiry_id'];
		$info['task_remark']		= $_POST['task_remark'];
		$info['assign_user_ids']	= implode(",",array_filter($POST['assign_user_ids']));
		$info['task_priority_id']	= $POST['task_priority_id'];
		$info['task_due_date']		= date('Y-m-d H:i:s',strtotime($POST['task_due_date']));
		$info['task_alert_id']		= $POST['task_alert_id'];

		if($POST['task_alert_id'] && $POST['task_alert_id']!='1'){//If alert is not none
			$alert_date = date("Y-m-d H:i:s", strtotime($POST['task_due_date']));
			$gap_mints=get_alert_mintes($dbcon,$POST['task_alert_id']);
			$filt_alert_date = date("Y-m-d H:i:s", strtotime($alert_date . "-".$gap_mints." minutes"));//Subtract Minutes
			$info['alert_date_time']	= date('Y-m-d H:i:s',strtotime($filt_alert_date));
		}
		
		$info['create_date']		= date('Y-m-d H:i:s');
		$info['entry_type']		= 1;//Fixed Task Type
		$info['cdate']			= date("Y-m-d H:i:s");
		$info['user_id']		= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];
		
		//Auto Complete Prev Flp Task Before Add
		if($POST['inquiry_id']){
			$upd_qry="update tbl_task set task_status=1,task_completion_date='".date("Y-m-d H:i:s")."' where task_status=0 and inquiry_id=".$POST['inquiry_id'];
			$upd_qry_rs=$dbcon->query($upd_qry);
			
		}
		$ins_task_id=add_record('tbl_task', $info, $dbcon);
		
		if($POST['inquiry_id']){
			//Auto Complete All Task Before Add
			if($POST['stage_prob']=='100'){
				$upd_qry="update tbl_task set task_status=1,task_completion_date='".date("Y-m-d H:i:s")."' where task_status=0 and inquiry_id=".$POST['inquiry_id'];
				$upd_qry_rs=$dbcon->query($upd_qry);
				
				$qtupd_qry="update tbl_quotation set quot_won_user_id=".$_SESSION['user_id']." where inquiry_id=".$POST['inquiry_id'];
				$qtupd_qry_rs=$dbcon->query($qtupd_qry);
			}
			
			//Edit Stage In Inquiry 
			$infoinq['opp_id']			= $POST['opp_id'];
			$infoinq['stage_prob']		= $POST['stage_prob'];
			$infoinq['won_user_id']		= $_SESSION['user_id'];//Won User id insert for order confirm list
			$updateinqid=update_record('tbl_inquiry', $infoinq, "inquiry_id=".$POST['inquiry_id'], $dbcon);
		}
		
		if($ins_task_id){	
			$arr['msg']="1";				
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"task_add",1,"tbl_task",$ins_task_id);			
		}
		else{
			$arr['msg']="0";
		}
		echo json_encode($arr);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$info['task_type_id']		= $POST['task_type_id'];
		$info['task_rel_id']		= $POST['task_rel_id'];
		$info['task_name']			= $POST['task_name'];
		$info['task_remark']		= $_POST['task_remark'];
		$info['assign_user_ids']	= implode(",",array_filter($POST['assign_user_ids']));
		$info['task_priority_id']	= $POST['task_priority_id'];
		$info['task_due_date']		= date('Y-m-d H:i:s',strtotime($POST['task_due_date']));
		$info['alert_date_time']	= date('Y-m-d H:i:s', strtotime($POST['alert_date_time']));

		$info['cdate']			= date("Y-m-d H:i:s");
		//$info['user_id']		= $_SESSION['user_id'];
		//$info['company_id']	= $_SESSION['company_id'];
		$updateid=update_record('tbl_task', $info, "task_id=".$POST['eid'], $dbcon);

		if($updateid){	
			$arr['msg']="update";
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"task_add",2,"tbl_task",$POST['eid']);
		}
		else{
			$arr['msg']=0;
		}
		echo json_encode($arr);
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['task_status']	= 2;
		$info['cdate']			= date("Y-m-d H:i:s");
		$updateid=update_record('tbl_task', $info, "task_id=".$POST['task_id'], $dbcon);

		//Insert LOG
		$log_entry=common_log_entry($dbcon,"task_add",3,"tbl_task",$POST['task_id']);

		if($updateid)
			echo "1";	
		else
			echo "0";			
	}
	else if(strtolower($POST['mode']) == "change_task_status") {
		$info['task_status']	= $POST['task_status'];
		if($info['task_status']=='1'){//Update Completion Date if Task Completed
			$info['task_completion_date']= date("Y-m-d H:i:s");
		}
		else{
			$info['task_completion_date']= "0000-00-00 00:00:00";//Reset Date
		}
		$info['cdate']			= date("Y-m-d H:i:s");
		$updateid=update_record('tbl_task', $info, "task_id=".$POST['task_id'], $dbcon);
		
		if($updateid)
			echo "1";	
		else
			echo "0";			
	}
	else if(strtolower($POST['mode']) == "add_flp_hist_field") {
		
		$info1['task_id']				= $POST['task_id'];
		$info1['task_flp_remark']		= $_POST['task_flp_remark'];
		$info1['flp_date']				= date("Y-m-d H:i:s");
		$info1['user_id']				= $_SESSION['user_id'];
		$info1['cdate']					= date("Y-m-d H:i:s");
		$info1['company_id']			= $_SESSION['company_id'];
		$table='tbl_followup';$tableid='flp_id';
		
		if(empty($POST['flp_id'])) {
			$inserid=add_record($table, $info1, $dbcon);
		}
		else {
			$updateid=update_record($table, $info1,$tableid."=".$POST['flp_id'] , $dbcon);	
		}
	}
	else if(strtolower($POST['mode']) == "show_flp_hist") {
		if($POST['task_id']!=""){
			$where ="and flp.task_id =".$POST['task_id'];
		}
		$appData = array();
		$i=1;
		$aColumns = array('flp.flp_id','flp.task_flp_remark','flp.flp_date','usr.user_name');
		$sIndexColumn = "flp.flp_id";
		$isWhere = array("flp.flp_status = 0 ".$where." and flp.company_id in (0,$_SESSION[company_id])");
		$sTable = "tbl_followup as flp";			
		$isJOIN = array("left join users as usr on usr.user_id=flp.user_id");
		$hOrder = "flp.flp_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['task_flp_remark'];
			$row_data[] = date("d-M-Y h:i A",strtotime($row['flp_date']));
			$row_data[] = $row['user_name'];
			
			$row_data[] = '<!--<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_reqpro('.$row['flp_id'].');"><i class="fa fa-pencil"></i></button>-->
			<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_task_flp('.$row['flp_id'].')"><i class="fa fa-trash-o"></i></button>';
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "delete_task_flp") {
		$info['flp_status']		='2';
		$info['cdate']			= date("Y-m-d H:i:s");
		$updateid=update_record('tbl_followup', $info,"flp_id=".$POST['flp_id'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0";
	}
	else if(strtolower($POST['mode']) == "preview_rel_types") {
		$str='';$task_rel_id=$POST['task_rel_id'];
		$c_con_id=$POST['c_con_id'];
		$cust_id=$POST['cust_id'];
		$inquiry_id=$POST['inquiry_id'];
		if($task_rel_id=='3' && $c_con_id){//Person
			$str.='<table class="display table table-bordered table-striped">
			<thead>
			<tr>
			<th>Person Name</th>
			<th>Customer Name</th>
			<th>Email</th>
			<th>Mobile</th>
			</tr>
			</thead>
			<tbody>';
			
			$per_qry="select per.*,cust.cust_name from tbl_cust_contact as per 
			left join tbl_customer as cust on cust.cust_id=per.cust_id 
			where c_con_id=".$c_con_id;
			$per_rel=mysqli_fetch_assoc($dbcon->query($per_qry));
			$str.='<tr>
			<td>'.$per_rel['c_con_fname'].' '.$per_rel['c_con_lname'].'</td>
			<td>'.$per_rel['cust_name'].'</td>
			<td>'.$per_rel['c_con_email'].'</td>
			<td>'.$per_rel['c_con_mobile'].'</td>
			</tr>';
			
			$str.='</tbody>
			</table>';
		}
		else if($task_rel_id=='4' && $cust_id){//Company
			$str.='<table class="display table table-bordered table-striped">
			<thead>
			<tr>
			<th>Customer Name</th>
			<th>Owner</th>
			</tr>
			</thead>
			<tbody>';
			
			$cust_qry="select cust.*,usr.user_name FROM tbl_customer as cust 
			left join users as usr on usr.user_id=cust.user_id
			where cust.cust_id=".$cust_id;
			$cust_rel=mysqli_fetch_assoc($dbcon->query($cust_qry));
			$str.='<tr>
			<td>'.$cust_rel['cust_name'].'</td>
			<td>'.$cust_rel['user_name'].'</td>
			</tr>';
			
			$str.='</tbody>
			</table>';
		}
		else if($task_rel_id=='5' && $inquiry_id){//Inquiry
			$str.='<table class="display table table-bordered table-striped">
			<thead>
			<tr>
			<th>Inquiry Name</th>
			<th>Customer Name</th>
			</tr>
			</thead>
			<tbody>';
			
			$inq_qry="select inq.*,cust.cust_name FROM tbl_inquiry as inq 
			left join tbl_customer as cust on cust.cust_id=cust.cust_id
			where inq.inquiry_id=".$inquiry_id;
			$inq_rel=mysqli_fetch_assoc($dbcon->query($inq_qry));
			$str.='<tr>
			<td>'.$inq_rel['inquiry_no'].'</td>
			<td>'.$inq_rel['cust_name'].'</td>
			</tr>';
			
			$str.='</tbody>
			</table>';
		}
		
		$resp['html_resp']=$str;
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "load_pend_task1") {
		$whr='';
		
		if($POST['fil_due_date']){
			$whr.=" and DATE_FORMAT(task.task_due_date,'%Y-%m-%d')<='".date('Y-m-d',strtotime($POST['fil_due_date']))."'";
		}
		
		if($POST['fil_task_type_id']){
			$whr.=' and task.task_type_id='.$POST['fil_task_type_id'];
		}
		
		if($POST['log_user_id']){
			$user_id=$POST['log_user_id'];
			//$ftp=check_crm_find_in_set($dbcon,$user_id,1);
			$fis=check_crm_find_in_set($dbcon,$user_id,1);
			$ftp=' and instr("'.$fis.'",task.assign_user_ids)';
		}
		else{
			if(!empty($POST['c_user_id'])){
				//$ftp=check_crm_find_in_set($dbcon,$POST['c_user_id'],1);
				$fis=check_crm_find_in_set($dbcon,$POST['c_user_id'],1);
				$ftp=' and instr("'.$fis.'",task.assign_user_ids)';
			}else{
				$user_id=$_SESSION['user_id'];
				//$ftp=check_crm_find_in_set($dbcon,$user_id,1);
				$fis=check_crm_find_in_set($dbcon,$user_id,1);
				$ftp=' and instr("'.$fis.'",task.assign_user_ids)';
			}
			//and find_in_set('.$user_id.',task.assign_user_ids)
		}
		
		$str='';
		$qry='SELECT task.*,state.state_name,city.city_name,usr.user_name,type.mcd_name as type_name,task_sub.mcd_name as task_sub_name,rel.task_rel_name,stage.opp_stage,stage.opp_probability,qt_aprv.approve_status,
		(
			CASE
			WHEN task.task_rel_id=1 then task.task_name
			WHEN task.task_rel_id=2 then task.task_name
			WHEN task.task_rel_id=3 then (SELECT c_con_fname from tbl_cust_contact WHERE c_con_id=task.c_con_id)
			WHEN task.task_rel_id=4 then (SELECT cust_name from tbl_customer WHERE cust_id=task.cust_id)
			WHEN task.task_rel_id=5 then (SELECT CONCAT(inquiry_no," - ",cust_name) from tbl_inquiry 
				left join tbl_customer on tbl_inquiry.cust_id=tbl_customer.cust_id 

				WHERE inquiry_id=task.inquiry_id)
			END
			) as rel_name
		from tbl_task as task
		left join users as usr on usr.user_id=task.user_id
		left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id
		left join tbl_master_category_detail as task_sub on task_sub.mcd_id=task.task_type_id
		left join task_rel_mst as rel on rel.task_rel_id=task.task_rel_id
		left join (SELECT cust_id as c_id,inquiry_id  from tbl_inquiry) as inq on inq.inquiry_id=task.inquiry_id
		left join (SELECT inquiry_id,approve_status FROM `tbl_quotation` WHERE quotation_status=0 and revise_status=0) as qt_aprv on qt_aprv.inquiry_id=task.inquiry_id
		left join tbl_customer as cust on cust.cust_id=inq.c_id
		left join state_mst as state on state.stateid=(SELECT c_add_state FROM `tbl_cust_address` WHERE cust_id=inq.c_id and c_add_status=0 limit 1)
		left join city_mst as city on city.cityid=(SELECT c_add_city FROM `tbl_cust_address` WHERE cust_id=inq.c_id and c_add_status=0 limit 1)
		left join tbl_opportunity_mst as stage on stage.opp_id=(SELECT opp_id from tbl_inquiry where inquiry_id=task.inquiry_id)
		WHERE task.task_status=0 and task.entry_type=1 and task.alert_date_time!="0000-00-00 00:00:00" and task.alert_date_time!="1970-01-01 05:30:00"  and alert_date_time<"'.date('Y-m-d',strtotime($POST['fil_due_date'])).'" '.$whr.' '.$ftp.' order by task.task_priority_id,task.alert_date_time';
		$qry_rs=$dbcon->query($qry);
		if(mysqli_num_rows($qry_rs)){
			$k=1;
			while($rel=mysqli_fetch_assoc($qry_rs)){
				//Action Btns
				//$task_btn='<button class="btn btn-xs btn-success" data-original-title="Click to Complete Task" data-toggle="tooltip" data-placement="top" onClick="change_task_status('.$rel['task_id'].',1)"><i class="fa fa-check"></i></button>';
				
				//$add_rmrk_btn='<button class="btn btn-xs btn-info" data-original-title="Add Remark" data-toggle="tooltip" data-placement="top" onClick="open_follow_up('.$rel['task_id'].',\''.$rel['rel_name'].'\')"><i class="fa fa-eye"></i></button>';
                //Amish Soni Start 02-02-2021
				$editLink = ($rel['task_type_id'] == "'".GENERAL_TASK_TYPE."'") ? 'general_' : '';
				$add_flp_btn='<a href="'.ROOT.CRM_ROOT.$editLink.'task_flp/'.$rel['task_id'].'" class="btn btn-xs btn-primary" data-original-title="Add Follow-Up" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i></a>';
                //Amish Soni End 02-02-2021

				$add_flp_btn='<a href="'.ROOT.CRM_ROOT.'task_flp/'.$rel['task_id'].'" class="btn btn-xs btn-primary" data-original-title="Add Follow-Up" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i></a>';
				
				$add_quot_btn='';$view_inq_btn='';$add_apt_btn='';
				if($rel['task_rel_id']=='5'){//Fixed Type Inquiry
					//$quot_btn_per=check_permission("quotation_list",$_SESSION['user_id'],'edit',$dbcon);
					if($_SESSION['user_type']!='8'){
						$add_quot_btn='<a href="'.ROOT.CRM_ROOT.'inq_to_quot/'.$rel['inquiry_id'].'" data-original-title="Create Quotation" data-toggle="tooltip" data-placement="top" class="btn btn-xs btn-success"><i class="fa fa-plus"></i></a>';
					}
					/*$add_flp_btn='<a href="'.ROOT.'inquiry_edit/'.$rel['inquiry_id'].'" class="btn btn-xs btn-primary" data-original-title="Add Follow-Up" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i></a>';*/
					$add_flp_btn='<a href="'.ROOT.CRM_ROOT.'task_add/'.$rel['inquiry_id'].'" class="btn btn-xs btn-primary" data-original-title="Add Follow-Up" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i></a>';
					$view_inq_btn='<a href="'.ROOT.CRM_ROOT.'inquiry_view/'.$rel['inquiry_id'].'" class="btn btn-xs btn-success" data-original-title="View Inquiry" data-toggle="tooltip" data-placement="top"><i class="fa fa-eye"></i></a>';
					$add_apt_btn='<a href="'.ROOT.CRM_ROOT.'appointment_add/'.$rel['inquiry_id'].'" class="btn btn-xs btn-info" data-original-title="Add Appointment" data-toggle="tooltip" data-placement="top"><i class="fa fa-users"></i></a>';
					
					//Quot flp allow task only if approved in quot
					if($rel['task_type_id']=='21' && $rel['approve_status']!='1'){
						$add_flp_btn='';$add_quot_btn='';
					}
					//if task is to Create or revise quot then dont allow for flp
					if($rel['task_type_id']=='15' && $rel['task_type_id']=='20'){
						$add_flp_btn='';$add_apt_btn='';
					}
					
				}
				
				$str.='<tr>
				<td class="text-left">'.$k.'</td>
				<td class="text-left"><strong>'.$rel['type_name'].'</strong></td>
				<td class="text-left">'.$rel['task_rel_name'].'</td>
				<td class="text-left">'.$rel['rel_name'].'</td>
				<td class="text-left">'.$rel['opp_stage'].'('.$rel['opp_probability'].'%)</td>
				<td class="text-left">'.$rel['state_name'].'</td>
				<td class="text-left">'.$rel['city_name'].'</td>
				<td class="text-left" >'.date("d-M-Y h:i A",strtotime($rel['task_due_date'])).'</td>
				<td class="text-left">'.nl2br($rel['task_remark']).'</td>
				<td class="text-left">'.$rel['user_name'].'</td>
				<td class="text-center">'.$task_btn.' '.$add_rmrk_btn.' '.$add_flp_btn.' '.$add_quot_btn.' '.$add_apt_btn.' '.$view_inq_btn.'</td>
				</tr>';
				$k++;
			}
		}
		else{
			$str.='<tr>
			<td colspan="11" class="text-center">No Data Found !!!</td>
			</tr>';
		}
		$resp['resp_html']=$str;
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "load_pend_task") {
	$whr = '';
    $com_conf = getCompanyConfiguration($dbcon);
    if ($POST['fil_due_date']) {
        $whr .= " and DATE_FORMAT(task.task_due_date,'%Y-%m-%d')<='" . date('Y-m-d', strtotime($POST['fil_due_date'])) . "'";
    }

    if ($POST['fil_task_type_id']) {
        $whr .= ' and task.task_type_id=' . $POST['fil_task_type_id'];
    }

    //Amish Soni Start 01-02-2021
    $is_general = (isset($POST['is_general']) && $POST['is_general']) ? $POST['is_general'] : false;
    if ($is_general) {
        $whr .= " and task.task_type_id = '" . GENERAL_TASK_TYPE . "' ";
    } else {
        $whr .= " and task.task_type_id != '" . GENERAL_TASK_TYPE . "' ";
    }
    //Amish Soni End 01-02-2021

    if ($POST['log_user_id']) {
        $user_id = $POST['log_user_id'];
        $fis = check_crm_find_in_set_new($dbcon, $user_id, 1);
        $ftp = " and FIND_IN_SET (" . $user_id . ",task.assign_user_ids)";
    } else {
        if (!empty($POST['c_user_id'])) {
            $fis = check_crm_find_in_set_new($dbcon, $POST['c_user_id'], 1);
            $ftp = " and FIND_IN_SET (" . $POST['c_user_id'] . ",task.assign_user_ids)";
        } else {
            $user_id = $_SESSION['user_id'];
            $fis = check_crm_find_in_set_new($dbcon, $user_id, 1);
            $ftp = " and FIND_IN_SET (" . $user_id . ",task.assign_user_ids)";
        }
    }

    $appData = array();
    $i = 1;

    $aColumns = array('task.task_id','cust.cust_mobile','cust.cust_name','state.state_name', 'city.city_name', 'task.task_rel_id', 'task.task_name','tea.t_name', 'inq.inquiry_no', 'inq.inquiry_name', 'inq.inquiry_date', 'per.c_con_fname', 'row.task_rel_name', 'task.task_due_date', 'task.task_remark', 'usr.user_name', 'task.task_rel_id', 'task.assign_user_ids', 'task.inquiry_id', 'task.task_type_id', 'task.task_status', 'task.entry_type', 'task.alert_date_time', 'type.mcd_name as type_name', 'task.user_id', 'task.task_priority_id', 'task_sub.mcd_name as task_sub_name','if(tr.project_wise=0,(SELECT group_concat(pro.product_name SEPARATOR ",<br>") FROM `tbl_inquiry_trn` as trn left join product_mst as pro on pro.product_id=trn.product_id where trn.inquiry_trn_status=0 and trn.inquiry_id=inq.inquiry_id) ,(select group_concat(proj.project_name) from tbl_inquiry_trn as trn left join tbl_project_assign as proj on proj.project_assign_id=trn.product_id where trn.inquiry_trn_status=0 and trn.inquiry_id=inq.inquiry_id)) as pro_name','if(tr.project_wise=0,(SELECT group_concat(pro.product_name SEPARATOR ",
    ") FROM `tbl_quotation_trn` as trn 
    left join tbl_quotation as qt on qt.quotation_id = trn.quotation_id
    left join product_mst as pro on pro.product_id=trn.product_id where trn.quot_trn_status=0 and qt.inquiry_id=inq.inquiry_id) ,(select group_concat(proj.project_name) from tbl_inquiry_trn as trn left join tbl_project_assign as proj on proj.project_assign_id=trn.product_id where trn.inquiry_trn_status=0 and trn.inquiry_id=inq.inquiry_id)) as quot_pro_name', 'stage.opp_stage', 'stage.opp_probability', 'qt_aprv.approve_status', 'qt_aprv.quotation_id','qt_aprv.quotation_no', 'type.mcd_id','mcd.mcd_name','inq.closing_date');
    $sIndexColumn = "task.task_id";
    //and alert_date_time="'.date('Y-m-d',strtotime($POST['fil_due_date'])).'" and
    $isWhere = array("task.task_status=0 and task.entry_type=1 and task.company_id in (0,$_SESSION[company_id])" . $whr . " " . $ftp);
    $sTable = "tbl_task as task";
    $isJOIN = array('left join users as usr on usr.user_id=task.user_id',
        'left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id',
        'left join tbl_master_category_detail as task_sub on task_sub.mcd_id=task.task_type_id',
        'left join task_rel_mst as row on row.task_rel_id=task.task_rel_id',
        'left join (SELECT cust_id as c_id,inquiry_id,inquiry_no,inquiry_name, inquiry_date, closing_date from tbl_inquiry) as inq on inq.inquiry_id=task.inquiry_id
        left join (SELECT inquiry_id,approve_status,quotation_id,quotation_no FROM `tbl_quotation` WHERE quotation_status=0 and revise_status=0) as qt_aprv on qt_aprv.inquiry_id=task.inquiry_id',
        'left join tbl_inquiry_trn as tr on tr.inquiry_id=inq.inquiry_id',
        'left join tbl_customer as cust on cust.cust_id=inq.c_id',
        'left join state_mst as state on state.stateid=(SELECT c_add_state FROM `tbl_cust_address` WHERE cust_id=inq.c_id and c_add_status=0 limit 1)',
        'left join city_mst as city on city.cityid=(SELECT c_add_city FROM `tbl_cust_address` WHERE cust_id=inq.c_id and c_add_status=0 limit 1)',
        'left join tbl_opportunity_mst as stage on stage.opp_id=(SELECT opp_id from tbl_inquiry where inquiry_id=task.inquiry_id)',
        'left join tbl_cust_contact as per on per.c_con_id=task.c_con_id',
        'left join tbl_master_category_detail as mcd on mcd.mcd_id=cust.cust_type',
        'left join territory_mst as tea on tea.t_id=cust.t_id');
    $hOrder = "task.task_id ".$com_conf['crm_task_order'];
    $hGroupby = array("task.task_id");
    include('../../../include/pagging.php');
    $appData = array();
    $id = 1;
		foreach($sqlReturn as $row) {
			$com_confi = getCompanyConfiguration($dbcon);
			$add_quot_btn = '';
			$view_inq_btn = '';
			$add_apt_btn = '';
			$inq_limit = ($com_confi['enable_inquiry_autoclose']==1) ? $com_confi['inquiry_autoclose_limit'] : 0;
			$days = $inq_limit." days";
			$inq_dates = date("Y-m-d",strtotime($row['closing_date']));
			$inq_date = date_create($inq_dates);
			date_add($inq_date, date_interval_create_from_date_string($days));
			$next_date = date_format($inq_date, 'Y-m-d');
			$editLink = ($row['mcd_id'] == GENERAL_TASK_TYPE) ? 'general_' : '';
			$add_flp_btn='<a href="'.ROOT.CRM_ROOT.$editLink.'task_flp/'.$row['task_id'].'" class="btn btn-xs btn-primary" data-original-title="Add Follow-Up" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i></a>';
                //Amish Soni End 02-02-2021

			$add_flp_btn='<a href="'.ROOT.CRM_ROOT.'task_flp/'.$row['task_id'].'" class="btn btn-xs btn-primary" data-original-title="Add Follow-Up" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i></a>';

			$add_quot_btn='';$view_inq_btn='';$add_apt_btn='';
				if($row['task_rel_id']=='5'){//Fixed Type Inquiry
					//$quot_btn_per=check_permission("quotation_list",$_SESSION['user_id'],'edit',$dbcon);
					//if($_SESSION['user_type']!='8'){
					if($row['mcd_id']==15){
						$add_quot_btn='<a href="'.ROOT.CRM_ROOT.'inq_to_quot/'.$row['inquiry_id'].'" data-original-title="Create Quotation" data-toggle="tooltip" data-placement="top" class="btn btn-xs btn-success"><i class="fa fa-plus"></i></a>';
					}else{
						$add_quot_btn='';
					}
					//}
					/*$add_flp_btn='<a href="'.ROOT.'inquiry_edit/'.$row['inquiry_id'].'" class="btn btn-xs btn-primary" data-original-title="Add Follow-Up" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i></a>';*/
					if($row['mcd_id']!=15){
						$add_flp_btn='<a href="'.ROOT.CRM_ROOT.'task_add/'.$row['inquiry_id'].'" class="btn btn-xs btn-primary" data-original-title="Add Follow-Up" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i></a>';
					}else{
						$add_flp_btn='';
					}
					$view_inq_btn='<a href="'.ROOT.CRM_ROOT.'inquiry_view/'.$row['inquiry_id'].'" class="btn btn-xs btn-success" data-original-title="View Inquiry" data-toggle="tooltip" data-placement="top"><i class="fa fa-eye"></i></a>';
					$add_apt_btn='<a href="'.ROOT.CRM_ROOT.'appointment_add/'.$row['inquiry_id'].'" class="btn btn-xs btn-info" data-original-title="Add Appointment" data-toggle="tooltip" data-placement="top"><i class="fa fa-users"></i></a>';
					
					//Quot flp allow task only if approved in quot
					if($row['task_type_id']=='21' && $row['approve_status']!='1'){
                        //$add_flp_btn='<a href="'.ROOT.CRM_ROOT.'task_add/'.$row['inquiry_id'].'" class="btn btn-xs btn-primary" data-original-title="Add Follow-Up" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i></a>';
						$add_flp_btn='<a href="javascript:;" class="btn btn-xs btn-primary" data-toggle="tooltip" title="Quotation is not approved yet"><i class="fa fa-plus"></i></a>';
						$add_quot_btn='';
					}
					//if task is to Create or revise quot then dont allow for flp
					if($row['task_type_id']=='15' && $row['task_type_id']=='20'){
						$add_flp_btn='';$add_apt_btn='';
					}
					if($row['task_type_id']=='20'){
						$add_flp_btn='';$add_apt_btn='';
						$add_flp_btn='<a class="btn btn-xs btn-info" data-original-title="Revise Quotation" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'quotation_revise/'.$row['quotation_id'].'"><i class="fa fa-repeat"></i></a>';
					}
					
				}
				if($com_confi['enable_inquiry_autoclose']==1){
					if($next_date < date("Y-m-d")){
						$add_flp_btn = '<a onclick="unlock_inquiry('.$row['inquiry_id'].')" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Inquiry is locked"><i class="fa fa-lock"></i></a>';
						$add_quot_btn = '';
					}
				}
				if($row['task_rel_id']=='5'){//Inquiry
					$inquiry_name = '';
					if($com_confi['followup_inquiry_show']==1){
						$inquiry_name = $row['inquiry_name'];
					}
					$rel_name = $row['cust_name'].'<br/>'.$inquiry_name.'<br/>'.$row['inquiry_no'].'<br/>'.$row['quotation_no'];
				}
                else if($row['task_rel_id']=='4'){ // Company
                	$rel_name = $row['cust_name'];
                }
                else if($row['task_rel_id']=='3'){//Person
                	$rel_name = $row['c_con_fname'];
                }
                else{
                	$rel_name = $row['task_name'];
                }

				$pro_name ='';
				if($row['quot_pro_name']!=''){
					$pro_name = $row['quot_pro_name'];
				}else{
					$pro_name = $row['pro_name'];
				}

                $row_data = array();
                $row_data[] = $row['sr'];
                $row_data[] = '<strong>'.$row['type_name'].'</strong>';
                $row_data[] = $row['task_rel_name'];
                $row_data[] = '<strong>'.$rel_name.'</strong>';
                $row_data[] = $row['cust_mobile'];
                $row_data[] = $row['mcd_name'];
                $row_data[] = $pro_name;
                $row_data[] = ''.$row['opp_stage'].'('.$row['opp_probability'].'%).';
                $row_data[] = $row['state_name'].' - '.$row['city_name'];
                $row_data[] = date("d-M-Y",strtotime($row['task_due_date'])).'<br/>'.date("h:i A",strtotime($row['task_due_date']));
                $row_data[] = nl2br($row['task_remark']);
                $row_data[] = $row['user_name'];
                $row_data[] = getTaskAssignNameCommaSeparated($dbcon,$row['assign_user_ids']);
                $row_data[] = $task_btn.' '.$add_rmrk_btn.' '.$add_flp_btn.' '.$add_quot_btn.' '.$add_apt_btn.' '.$view_inq_btn;


                $appData[] = $row_data;
                $id++;
            }
            $output['aaData'] = $appData;
            echo json_encode( $output );
        }
        function getTaskAssignNameCommaSeparated($dbcon,$assign_user_ids){
        	$strVal = '';
        	$qry='SELECT tsk.task_id, GROUP_CONCAT(userdata.user_name) AS valuesdata FROM tbl_task tsk JOIN users AS userdata ON FIND_IN_SET(userdata.user_id, "'.$assign_user_ids.'") GROUP BY tsk.task_id';
        		$qry_rel=mysqli_fetch_assoc($dbcon->query($qry));

        		if($qry_rel){
        			$strVal = $qry_rel['valuesdata'];
        		}
        		return $strVal;
        	}

        ?>