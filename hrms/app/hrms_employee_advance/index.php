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
	$aColumns = array('id', 'series_id', 'employee_id', 'posting_date', 'status');
	$sIndexColumn = "id";
	$isWhere = array("status IN (0,1) and emp_adv.company_id = $companyID".check_user('emp_adv'));
	$sTable = "hrms_employee_advance as emp_adv";			
	$isJOIN = array("left join tbl_company as comp on comp.company_id=emp_adv.company_id");
	$hOrder = "emp_adv.id ASC";
	include('../../../include/pagging.php');
	$appData = array();
	$id=1;

	$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
	$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
	$other_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);
	
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['series_id'];
		$row_data[] = $row['employee_id'] ? get_ledger_expense_by_id($dbcon, $row['employee_id']) : '-';
		$row_data[] = ($row['posting_date'] && $row['posting_date'] != '0000-00-00') ? date('d-m-Y',strtotime($row['posting_date'])) : ' - ';
		if($row['status']=='0'){
			$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
		}else{
			$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
		}
		$edit_btn='';$delete_btn='';$change_status='';
		if($row['id']!='0'){
			if($edit_btn_per) {
				$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'. ROOT . HRMS_ROOT .'hrms_employee_advance_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if($delete_btn_per) {
				$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_record('.$row['id'].')"><i class="fa fa-trash-o"></i></button>'; 
			}
		}
		if($other_btn_per) {
			if($row['status'] == '0')
			{  
				$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-window-close'></i></a>";	
			} else {
				$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-check-square-o'></i></a>";
			}
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

	if(empty($POST['series_id']) || empty($POST['employee_id']) || empty($POST['advance_account_id']) || empty($POST['mode_payment_id']) || empty($POST['posting_date']) || empty($POST['purpose']) || empty($POST['advance_amount']) || empty($POST['pending_amount'])) {
		$row['res'] = "-1";
	} else {
		$info['user_id'] = $_SESSION['user_id'];
		$info['company_id'] = $_SESSION['company_id'];
		$info['posting_date'] = date('Y-m-d',strtotime($POST['posting_date']));
		$info['series_id'] = $POST['series_id'];
		$info['employee_id'] = $POST['employee_id'];
		$info['advance_account_id'] = $POST['advance_account_id'];
		$info['reply_unclaim_amount_flag'] = $POST['reply_unclaim_amount_flag'];
		$info['mode_payment_id'] = $POST['mode_payment_id'];
		$info['purpose'] = $POST['purpose'];
		$info['advance_amount'] = $POST['advance_amount'];
		$info['pending_amount'] = $POST['pending_amount'];
		$info['status'] = $POST['status'];

		$insertid = add_record('hrms_employee_advance', $info, $dbcon);

		updateSeries($dbcon, 'id', 'hrms_employee_advance', 'EMPLOYEE ADVANCE');

		$row['res'] = ($insertid) ? "1" : "0";
	}
	echo json_encode($row);
	
}
else if(strtolower($POST['mode']) == "preedit") {			
	$q = $dbcon -> query("SELECT * FROM `hrms_employee_advance` WHERE `id` = '$POST[id]'");
	$r = $q->fetch_assoc();
	echo json_encode($r);
}
else if(strtolower($POST['mode']) == "edit") {
	if($_POST['token'] == $_SESSION['token']) {
		$info['posting_date'] = date('Y-m-d',strtotime($POST['posting_date']));
		$info['series_id'] = $POST['series_id'];
		$info['employee_id'] = $POST['employee_id'];
		$info['advance_account_id'] = $POST['advance_account_id'];
		$info['reply_unclaim_amount_flag'] = $POST['reply_unclaim_amount_flag'];
		$info['mode_payment_id'] = $POST['mode_payment_id'];
		$info['purpose'] = $POST['purpose'];
		$info['advance_amount'] = $POST['advance_amount'];
		$info['pending_amount'] = $POST['pending_amount'];
		$info['status'] = $POST['status'];
		$info['updated_at']	= date("Y-m-d H:i:s");			
		$updateid = update_record('hrms_employee_advance', $info,"id=".$POST['eid'] , $dbcon);
		
		$row['res'] = ($updateid) ? "1" : "0".$dbcon->error;
	} else {
		$row['res'] = "0";
	}
	echo json_encode($row);
}
else if(strtolower($POST['mode']) == "delete") {
	$info['status'] = '2';
	$info['updated_at']	= date("Y-m-d H:i:s");
	$updateid = update_record('hrms_employee_advance', $info,"id=".$POST['eid'] , $dbcon);
	
	echo ($updateid) ? "1" : "0";
}
else if(strtolower($POST['mode']) == "change_status") {
	$p_status = $POST['p_status'];

	$info['status'] = ($p_status=='0') ? '1' : '0';
	$info['updated_at']	= date("Y-m-d H:i:s");

	$updateid = update_record('hrms_employee_advance', $info,"id=".$POST['eid'] , $dbcon);
	
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
else if(strtolower($POST['mode']) == "change_status") 
{
	$p_status = $POST['p_status'];

	$info['status'] = ($p_status=='0') ? '1' : '0';
	
	$updateid = update_record('hrms_employee_advance', $info,"id=".$POST['eid'] , $dbcon);
	echo ($updateid) ? "1" : "0";
}