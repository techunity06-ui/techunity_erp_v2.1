<?php

session_start();
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
	$companyID = $_SESSION['company_id'];
	$userID =  $_SESSION['user_id'];
	$where='';
	$where.="hrmssms.status IN (0,1) and hrmssms.company_id = $companyID".check_user('hrmssms');
	$appData = array();
	$i=1;
	$aColumns = array('id','sms_template_name', 'sms_template_body', 'status', 'comp.company_name');
	$sIndexColumn = "hrmssms.id";
	$isWhere = array($where);
	$sTable = "hrms_sms_template as hrmssms";			
	$isJOIN = array("left join tbl_company as comp on comp.company_id = hrmssms.company_id");
	$hOrder = "hrmssms.id desc";
	include('../../../include/pagging.php');
	$appData = array();
	$id=1;

	$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
	$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
	$other_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['id'];
		$row_data[] = $row['company_name'];
		$row_data[] = $row['sms_template_name'];
		
		if($row['status']=='0'){
			$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
		}else{
			$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
		}

		$edit_btn='';$delete_btn='';$change_status='';
		if($row['id']!='0'){ 
			if($edit_btn_per) {
				$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'. ROOT . HRMS_ROOT . 'hrms_sms_template_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if($delete_btn_per) {
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_sms_template('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';

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
		$row_data[] = $edit_btn.' '.$delete_btn.' '.$change_status;
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {

	// Insert New Leave Type List
	$info['user_id']	= $_SESSION['user_id'];
	$info['company_id']	= $_SESSION['company_id'];
	$info['sms_template_name'] = $POST['sms_template_name'];
	$info['sms_template_body']	= $_POST['sms_template_body'];
	$info['sms_document_attachment']	= upload_attch_file($_FILES);
	$info['status']	= $POST['status'];
	$info['updated_at']	= date("Y-m-d H:i:s");
	
	$insertsmstemplateid = add_record('hrms_sms_template', $info, $dbcon);
	
	if($insertsmstemplateid){	
		$arr['msg']="1";							
	}else{
		$arr['msg']="0";
	}
	echo json_encode($arr);	
}else if(strtolower($POST['mode']) == "edit") {
		$info['sms_template_name'] = $POST['sms_template_name'];
		$info['sms_template_body']	= $_POST['sms_template_body'];
		if(!empty($_FILES['sms_document_attachment']['tmp_name'])){
			$info['sms_document_attachment'] = upload_attch_file($_FILES);
		}
		$info['status']	= $POST['status'];
		$info['updated_at']	= date("Y-m-d H:i:s");
		$updatesmstemplateid = update_record('hrms_sms_template', $info,"id=".$POST['eid'] , $dbcon);
		if($updatesmstemplateid){	
			$arr['msg']="1";
		}else{
			$arr['msg']="0";
		}
		echo json_encode($arr);	
}
else if(strtolower($POST['mode'])== "delete") {
	$row=array();
	$info['status'] = 2;
	$updateid=update_record('hrms_sms_template', $info, "id=".$POST['eid'] , $dbcon);
	if($updateid)
		$row['msg']="1";
	else
		$row['msg']="0";
	
	echo json_encode($row);
}
else if(strtolower($POST['mode']) == "change_status") {
	$p_status = $POST['p_status'];

	$info['status'] = ($p_status=='0') ? '1' : '0';
	
	$updateid = update_record('hrms_sms_template', $info,"id=".$POST['eid'] , $dbcon);
	echo ($updateid) ? "1" : "0";
}
function upload_attch_file($FILES)
{
	$rand=rand(0,99999999);
	if(!empty($FILES['sms_document_attachment']['tmp_name'])) {
		$temp = explode(".", $FILES["sms_document_attachment"]["name"]);
		$extension = strtolower(end($temp));
		$File = "sms_document_attachment_".$rand.".".$extension;
		$tmp_name = $FILES["sms_document_attachment"]["tmp_name"];
		move_uploaded_file($tmp_name,SMS_ATTACH_UPING.$File);

		return  $File;				
	}
}	
		
?>