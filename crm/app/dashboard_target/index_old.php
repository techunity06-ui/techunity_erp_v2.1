<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

//print_r($_POST);
//print_r($_FILES);
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
                $today = date('Y-m-d H:i:s');
                
		$where='';
		//$where.="  and apt.appointment_start_time >= '".date('Y-m-d',strtotime($s_date[0]))."' AND apt.appointment_start_time <= '".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$where.="  and apt.appointment_start_time between '".date('Y-m-d',strtotime($s_date[0]))."' 
		AND '".date('Y-m-d',strtotime("+1 day", strtotime($s_date[1])))."'";
		if($POST['task_status']!=''){
                    if($POST['task_status'] == 1){
                        $where_status .= ' and apt.task_status=1';
                        
                    } else if ($POST['task_status'] == 0){
                        $where_status.=' and apt.task_status=0 AND apt.appointment_start_time > "'.$today.'"';
                        
                    } else if ($POST['task_status'] == 2){
                        $where_status.=' and apt.task_status=0 AND apt.appointment_start_time < "'.$today.'"';
                        
                    } else {
                        $where_status.='apt.task_status != 2';
                    }
		}
		
		$appData = array();
		$i=1;
		$aColumns = array('apt.task_id', 'apt.appointment_subject','apt.task_status','apt.appointment_start_time','apt.appointment_end_time','regrd.task_rel_name', 'apt.task_name', 'usr.user_name', 'apt.create_date', 'inq.inquiry_name', 'cust.cust_name', 'per.c_con_fname', 'per.c_con_lname', 'apt.cdate', 'apt.task_rel_id');
		$sIndexColumn = "apt.task_id";
		$isWhere = array("apt.entry_type=2".$where_status.$where.check_user('apt'));
                //echo '<pre>'; print_r($isWhere); 
		$sTable = "tbl_task as apt";
		$isJOIN = array('left join task_rel_mst as regrd on regrd.task_rel_id=apt.task_rel_id', 'left join tbl_inquiry as inq on inq.inquiry_id=apt.inquiry_id', 'left join tbl_customer as cust on cust.cust_id=apt.cust_id', 'left join tbl_cust_contact as per on per.c_con_id=apt.c_con_id', 'left join users as usr on usr.user_id=apt.user_id');
		$hOrder = "apt.task_id desc";
		include('../../include/pagging.php');
		//$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['appointment_subject'];
			$row_data[] = $row['task_rel_name'];
			
			if($row['task_rel_id']=='5'){//Inquiry
				$row_data[] = $row['inquiry_name'];
			}
			else if($row['task_rel_id']=='4'){//Company
				$row_data[] = $row['cust_name'];
			}
			else if($row['task_rel_id']=='3'){//Person
				$row_data[] = $row['c_con_fname'].' '.$row['c_con_lname'];
			}
			else{
				$row_data[] = $row['task_name'];
			}
			
                        $row_data[] = $row['user_name'];
			$appointment_date = date("d-M-Y",strtotime($row['appointment_start_time']));
                        $start_time = date("H:i: A",strtotime($row['appointment_start_time']));
                        $end_time = date("H:i: A",strtotime($row['appointment_end_time']));
                        $row_data[] = $appointment_date .'<br/>'.$start_time.' to '.$end_time ;
                        
                        if($row['task_status']==1){
                            $tsk_type="<label>Completed</label>";
                            $row_data[] = '<button type="button" class="btn btn-sm btn-success" data-original-title="Task Completed" data-toggle="tooltip" data-placement="top"><i class="fa fa-check"></i>'.$tsk_type.'</button>';
                        }
                        else{
                            $tsk_due_time = strtotime($row['appointment_start_time']);
                            $cur_time = strtotime(date('Y-m-d H:i:s'));
                            
                            $tsk_type='';
                            if($tsk_due_time < $cur_time){
                                $tsk_type="<label>Missed</label>";
                                $row_data[] = '<button type="button" class="btn btn-sm btn-warning" data-original-title="Task Pending" data-toggle="tooltip" data-placement="top">'.$tsk_type.'</button>';
                            } else{
                                $tsk_type="<label>Upcoming</label>";
                                $row_data[] = '<button type="button" class="btn btn-sm btn-primary" data-original-title="Task Pending" data-toggle="tooltip" data-placement="top">'.$tsk_type.'</button>';
                            }
                            			
                        }
			
			$edit='';$delete='';
			if($edit_btn_per) {
				$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'appointment_edit/'.$row['task_id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if($delete_btn_per) {
				$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_appointment('.$row['task_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			$row_data[] = $edit.' '.$delete;

			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {

		$info['task_location']		= $_POST['task_location'];
		$info['full_day_event']		= $_POST['full_day_event'];
		$info['appointment_start_time']	= date('Y-m-d H:i:s',strtotime($POST['appointment_start_time']));
		$info['appointment_end_time']	= date('Y-m-d H:i:s',strtotime($POST['appointment_end_time']));
		$info['appointment_subject']		= $_POST['appointment_subject'];
		$info['task_remark']		= $_POST['task_remark'];
		$info['assign_user_ids']	= implode(",",array_filter($POST['assign_user_ids']));
		$info['task_rel_id']		= $POST['task_rel_id'];
		$info['task_name']			= $POST['task_name'];
		$info['c_con_id']			= $POST['c_con_id'];
		$info['cust_id']			= $POST['cust_id'];
		$info['inquiry_id']			= $POST['inquiry_id'];
		
		if($POST['task_alert_id'] && $POST['task_alert_id']!='1'){//If alert is not none
                    $alert_date = date("Y-m-d H:i:s", strtotime($POST['appointment_start_time']));
                    $gap_mints = get_alert_mintes($dbcon,$POST['task_alert_id']);
                    $alert_date . "-".$gap_mints." minutes";
                    $filt_alert_date = date("Y-m-d H:i:s", strtotime($alert_date . "-".$gap_mints." minutes"));//Subtract Minutes
                    $info['alert_date_time']	= date('Y-m-d H:i:s',strtotime($filt_alert_date));
		}
		$info['task_alert_id']		= $POST['task_alert_id'];

		$info['create_date']		= date('Y-m-d H:i:s');
		$info['entry_type']		= 2;//Fixed Appointment Type
		$info['cdate']			= date("Y-m-d H:i:s");
		$info['user_id']		= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];
		$ins_task_id=add_record('tbl_task', $info, $dbcon);

		if($ins_task_id){	
			$arr['msg']="1";
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"appointment_add",1,"tbl_task",$ins_task_id);							
		}
		else{
			$arr['msg']="0";
		}
		echo json_encode($arr);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$info['task_location']		= $_POST['task_location'];
		$info['full_day_event']		= $_POST['full_day_event'];
		$info['appointment_start_time']	= date('Y-m-d H:i:s',strtotime($POST['appointment_start_time']));
		$info['appointment_end_time']	= date('Y-m-d H:i:s',strtotime($POST['appointment_end_time']));
		$info['appointment_subject']= $_POST['appointment_subject'];
		$info['task_remark']		= $_POST['task_remark'];
		$info['assign_user_ids']	= implode(",",array_filter($POST['assign_user_ids']));
		$info['task_rel_id']		= $POST['task_rel_id'];
		$info['task_name']			= $POST['task_name'];
		
		
		$info['task_alert_id']		= $POST['task_alert_id'];
		if($POST['task_alert_id'] && $POST['task_alert_id']!='1'){//If alert is not none
                    $alert_date = date("Y-m-d H:i:s", strtotime($POST['appointment_start_time']));
                    $gap_mints = get_alert_mintes($dbcon,$POST['task_alert_id']);
                    $alert = $alert_date . "-".$gap_mints." minutes";
                    $filt_alert_date = date("Y-m-d H:i:s", strtotime($alert));//Subtract Minutes
                    $info['alert_date_time']	= date('Y-m-d H:i:s',strtotime($filt_alert_date));
		}
                
                $info['task_status']	= $POST['task_status'];
		if($info['task_status']=='1'){//Update Completion Date if Task Completed
			$info['task_completion_date']= date("Y-m-d H:i:s");
			$info['lost_reason'] = $POST['closed_remark'];
		}
		else{
			$info['task_completion_date']= "0000-00-00 00:00:00";//Reset Date
		}
		$info['cdate']			= date("Y-m-d H:i:s");
		//$info['user_id']		= $_SESSION['user_id'];
		//$info['company_id']	= $_SESSION['company_id'];
                //echo '<pre>';                print_r($info); exit;
		$updateid=update_record('tbl_task', $info, "task_id=".$POST['eid'], $dbcon);
		
		if($updateid){	
			$arr['msg']="update";
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"appointment_add",2,"tbl_task",$POST['eid']);
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
		$log_entry=common_log_entry($dbcon,"appointment_add",3,"tbl_task",$POST['task_id']);
		
		if($updateid)
			echo "1";	
		else
			echo "0";			
	}
	else if(strtolower($POST['mode']) == "load_pend_appointment") {
		$whr='';
		//$s_date=explode(' - ',$POST['fil_due_date']);
		//$start_date=$s_date[0];
                $start_date = $POST['fil_due_date'];
		
		if($POST['fil_due_date']){
			$whr.=" and DATE_FORMAT(task.appointment_start_time,'%Y-%m-%d')<='".date('Y-m-d',strtotime($start_date))."'";
		}
		
		if($POST['log_user_id']){
			$user_id=$POST['log_user_id'];
		}
		else{
			$user_id=$_SESSION['user_id'];
		}
		
		$str='<table class="display table table-bordered table-striped">
				<tr>
					<th class="text-center">Sr.</th>
					<th class="text-center">Location</th>
					<th class="text-center">Subject</th>
					<th class="text-center">Regarding</th>
					<th class="text-center">Name</th>
					<th class="text-center">Appointment Date</th>
					<th class="text-center">Remark</th>
					<th class="text-center">Owner</th>
					<th class="text-center">Action</th>
				</tr>
				<tbody id="pend_task_tbody">';
	$qry='SELECT task.*,usr.user_name,regrd.task_rel_name,inq.inquiry_id,
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
left join tbl_inquiry as inq ON inq.inquiry_id = task.inquiry_id
left join users as usr on usr.user_id=task.user_id
left join task_rel_mst as regrd on regrd.task_rel_id=task.task_rel_id
WHERE task.task_status=0 and task.entry_type=2 
    and find_in_set('.$user_id.',task.assign_user_ids) 
    '.$whr.' order by task.alert_date_time';
		$qry_rs=$dbcon->query($qry);
		if(mysqli_num_rows($qry_rs)){
			$k=1;
			while($rel=mysqli_fetch_assoc($qry_rs)){
				//Action Btns
				$view_inq_btn='';
				if($rel['task_rel_id']=='5'){//Fixed Type Inquiry
					$view_inq_btn='<a href="'.ROOT.'inquiry_view/'.$rel['inquiry_id'].'" class="btn btn-xs btn-success" data-original-title="View Inquiry" data-toggle="tooltip" data-placement="top"><i class="fa fa-eye"></i></a>';
				}
				
				$str.='<tr>
					<td class="text-left">'.$k.'</td>
					<td class="text-left">'.$rel['task_location'].'</td>
					<td class="text-left">'.$rel['appointment_subject'].'</td>
					<td class="text-left">'.$rel['task_rel_name'].'</td>
					<td class="text-left">'.$rel['rel_name'].'</td>
					<td class="text-left">'.date("d-M-Y h:i A",strtotime($rel['appointment_start_time'])).'</td>
					<td class="text-left">'.nl2br($rel['task_remark']).'</td>
					<td class="text-left">'.($rel['user_name']).'</td>
					<td class="text-center">'.$view_inq_btn.'</td>
				</tr>';
				$k++;
			}
		}
		else{
			$str.='<tr>
				<td colspan="11" class="text-center">No Data Found !!!</td>
			</tr>';
		}
		$str.='</tbody>
				</table>';
		$resp['resp_html']=$str;
		echo json_encode($resp);
	}

?>