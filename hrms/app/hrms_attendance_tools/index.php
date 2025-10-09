<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../../include/function_database_query.php");
include_once("../../../include/common_functions.php");
include_once("../../../include/hrms_common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
} else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
	$appData = array();
	$i=1;
	$companyID = $_SESSION['company_id'];
	$userID =  $_SESSION['user_id'];
	$aColumns = array('id', 'department_id', 'zone_id', 'branch_id', 'attendance_date', 'approval_status_id', 'status');
	$sIndexColumn = "id";
	$isWhere = array("status IN (0,1) and attendancetools.company_id = $companyID".check_user('attendancetools'));
	$sTable = "hrms_attendance_tools as attendancetools";			
	$isJOIN = array("left join tbl_company as comp on comp.company_id=attendancetools.company_id");
	$hOrder = "attendancetools.id ASC";
	include('../../../include/pagging.php');
	$appData = array();
	$id=1;

	$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
	$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
	$other_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);
	
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = ($row['attendance_date'] && $row['attendance_date'] != '0000-00-00') ? date('d-m-Y',strtotime($row['attendance_date'])) : ' - ';
		$row_data[] = $row['zone_id'] ? get_zone_name_by_id($dbcon, $row['zone_id']) : '-';
		$row_data[] = $row['branch_id'] ? get_branch_name_by_id($dbcon, $row['branch_id']) : '-';
		$row_data[] = $row['department_id'] ? get_department_name_by_id($dbcon, $row['department_id']) : '-';
		$row_data[] = $row['approval_status_id'] ? get_approval_status_by_id($dbcon, $row['approval_status_id']) : '-';
		if($row['status']=='0'){
			$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
		}else{
			$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
		}
		$edit_btn='';$delete_btn='';$change_status='';
		if($row['id']!='0'){ 
			if($edit_btn_per) {
				$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'. ROOT . HRMS_ROOT . 'hrms_attendance_tools_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if($delete_btn_per) {
				$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_record('.$row['id'].')"><i class="fa fa-trash-o"></i></button>'; 
			}
		}

		if($row['status'] == '0')
		{  
			$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-window-close'></i></a>";	
		} else {
			$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-check-square-o'></i></a>";
		}
		$row_data[] = $edit_btn.' '.$delete_btn. ' '. $change_status; 
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {
	
	$row['res']='';
	
	if(empty($POST['attendance_date']) || empty($POST['employee_ids']) || empty($POST['branch_id']) || empty($POST['department_id']) || empty($POST['approval_status_id'])) {
		$row['res'] = "-1";
	} else {
		$info['user_id'] = $_SESSION['user_id'];
		$info['company_id'] = $_SESSION['company_id'];
		$info['attendance_date'] = date('Y-m-d',strtotime($POST['attendance_date']));
		$info['employee_ids'] = (is_array($POST['employee_ids'])) ? implode(',', $POST['employee_ids']) : $POST['employee_ids'];		
		$info['zone_id'] = $POST['zone_id'];
		$info['branch_id'] = $POST['branch_id'];
		$info['department_id'] = $POST['department_id'];
		$info['approval_status_id'] = $POST['approval_status_id'];
		$info['status'] = $POST['status'];
		$info['updated_at']	= date("Y-m-d H:i:s");

		$insertid = add_record('hrms_attendance_tools', $info, $dbcon);

		$row['res'] = ($insertid) ? "1" : "0";
	}
	echo json_encode($row);
	
}
else if(strtolower($POST['mode']) == "preedit") {			
	$q = $dbcon -> query("SELECT * FROM `hrms_attendance_tools` WHERE `id` = '$POST[id]'");
	$r = $q->fetch_assoc();
	echo json_encode($r);
}
else if(strtolower($POST['mode']) == "edit") {
	if($_POST['token'] == $_SESSION['token']) {
		$info['attendance_date'] = date('Y-m-d',strtotime($POST['attendance_date']));
		$info['employee_ids'] = (is_array($POST['employee_ids'])) ? implode(',', $POST['employee_ids']) : $POST['employee_ids'];		
		$info['zone_id'] = $POST['zone_id'];
		$info['branch_id'] = $POST['branch_id'];
		$info['department_id'] = $POST['department_id'];
		$info['approval_status_id'] = $POST['approval_status_id'];
		$info['status'] = $POST['status'];
		$info['updated_at']	= date("Y-m-d H:i:s");			
		$updateid = update_record('hrms_attendance_tools', $info,"id=".$POST['eid'] , $dbcon);
		
		$row['res'] = ($updateid) ? "1" : "0".$dbcon->error;
	} else {
		$row['res'] = "0";
	}
	echo json_encode($row);
}
else if(strtolower($POST['mode']) == "delete") {
	$info['status'] = '2';
	$info['updated_at']	= date("Y-m-d H:i:s");
	$updateid = update_record('hrms_attendance_tools', $info,"id=".$POST['eid'] , $dbcon);
	
	echo ($updateid) ? "1" : "0";
}
else if(strtolower($POST['mode']) == "change_status") {
	$p_status = $POST['p_status'];

	$info['status'] = ($p_status=='0') ? '1' : '0';
	$info['updated_at']	= date("Y-m-d H:i:s");

	$updateid = update_record('hrms_attendance_tools', $info,"id=".$POST['eid'] , $dbcon);
	
	echo ($updateid) ? "1" : "0";
}
else if(strtolower($POST['mode']) == "load_branch") {
	$val=$POST['val'];
	$where = ' AND zoneid = '.$val;		
	echo get_branch($dbcon, '', $where);
}
else if(strtolower($POST['mode']) == "load_emp") {
	$val=$POST['val'];
	$where = ' AND branch_id_employee = '.$val;		
	echo getAllEmployee($dbcon, '', $where);
}