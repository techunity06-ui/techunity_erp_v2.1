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
	if(strtolower($POST['mode']) == "generate_appointment_activity_list"){
		$user_id=$POST['user_id'];
		
		$where='';
		if($user_id){
			$where.=' and appoint.user_id='.$POST['user_id'];
		}
		
		$appData = array();
		$i=1;
		$aColumns = array('appoint.task_id', 'inq.inquiry_name', 'usr.user_name as lead_owner', 'appoint.appointment_start_time', 'appoint.appointment_end_time', 'appoint.appointment_subject', 'appoint.task_remark', 'appoint.task_location', 'appoint.task_status', 'appoint.entry_type');
		$sIndexColumn = "appoint.task_id";
		$isWhere = array("appoint.task_status = 0 and appoint.entry_type = 2 AND appoint.company_id IN (0,$_SESSION[company_id])".$where);
		$sTable = " tbl_task as appoint";			
		$isJOIN = array("left join users as usr on usr.user_id=appoint.user_id",
						"left join tbl_inquiry as inq on inq.inquiry_id=appoint.inquiry_id");
		$hOrder = "appoint.task_id desc";
		include('../../../include/pagging.php');
		$appData = array();
		$id=1;

		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = date('Y-m-d H:i:s', strtotime($row['appointment_start_time']));
			$row_data[] = date('Y-m-d H:i:s', strtotime($row['appointment_end_time']));
			$row_data[] = $row['appointment_subject'];
			$row_data[] = $row['task_remark'];
			$row_data[] = $row['inquiry_name'];
			$row_data[] = $row['task_location'];
			$row_data[] = $row['lead_owner'];
			if($row['task_status']==1){
                $tsk_type="<label>Completed</label>";
                $row_data[] = $tsk_type;
            }
            else{
                $tsk_due_time = strtotime($row['appointment_start_time']);
                $cur_time = strtotime(date('Y-m-d H:i:s'));
                
                $tsk_type='';
                if($tsk_due_time < $cur_time){
                    $tsk_type="<label>Missed</label>";
                    $row_data[] = $tsk_type;
                } else{
                    $tsk_type="<label>Upcoming</label>";
                    $row_data[] = $tsk_type;
                }
                			
            }
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	
	
	
?>