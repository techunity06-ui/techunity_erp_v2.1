<?php

session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
							

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(brp_strtolower($POST['mode']) == "fetch") {
	$module_id = $POST['module_id'];
	
	$where='';
	//Amish Soni 06-01-2021
	// $edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
	// $delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
	// $appr_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'aprv',$dbcon);
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		EMAIL_TEMPLATE_SLUG_EDIT,
		EMAIL_TEMPLATE_SLUG_DELETE
	]);
	
	if($module_id!=''){
		$where.="  and est.email_module_id = '".$module_id."' ";
	}
	
	$appData = array();
	$i=1;
	$aColumns = array('est.email_sms_id', 'est.email_module_id', 'est.template_title', 'est.email_content', 'est.sms_content', 'est.status', 'est.cdate', 'eml.name', );
	$sIndexColumn = "est.email_sms_id";
	$isWhere = array("est.status IN (0,1) and est.company_id = '".$_SESSION['company_id']."' ".$where);
	$sTable = "email_sms_template as est";
	$isJOIN = array('left join email_module_list as eml on est.email_module_id = eml.email_module_id');
	$hOrder = "est.email_sms_id";
	include('../../include/pagging.php');
	$appData = array();
	$cnt=1;

	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $cnt;
		$row_data[] = $row['template_title'];		
		$row_data[] = $row['name'];
		$row_data[] = date('d M, Y',strtotime($row['cdate']));
		
		
		if($row['status']=='0'){
			$row_data[] = '<button class="btn btn-xs btn-success" >Active</button>';
		}else{
			$row_data[] = '<button class="btn btn-xs btn-danger" >In-Active</button>';
		}

		$delete=''; $edit=''; $st_btn='';
		// Edit Icon
		//Amish Soni 06-01-2021
		if(in_array(EMAIL_TEMPLATE_SLUG_EDIT, $bulkAccessArray)) {
		// if($edit_btn_per){
				$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'emailtemplateedit/'.$row['email_sms_id'].'"><i class="fa fa-pencil"></i></a>';
		}

		// Change Status Icon
		if($row['status']=="0"){
			$st_btn ='<button class="btn btn-xs btn-danger" title="Change to In-Active" data-toggle="tooltip" data-placement="top" onclick="change_email_template_status('.$row['email_sms_id'].',1)"><i class="fa fa-window-close"></i></button>';
		}else if($row['status']=="1"){
			$st_btn ='<button class="btn btn-xs btn-success" title="Change to Active" data-toggle="tooltip" data-placement="top" onclick="change_email_template_status('.$row['email_sms_id'].',0)"><i class="fa fa-check"></i></button>';
		}

		// Delete Status Icon
		//Amish Soni 06-01-2021
		if(in_array(EMAIL_TEMPLATE_SLUG_DELETE, $bulkAccessArray)) {
		// if($delete_btn_per){
				$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_email('.$row['email_sms_id'].')"><i class="fa fa-trash-o"></i></button>';
		}
		
		$row_data[] = $edit.' '.$st_btn.' '. $delete;
		
		$appData[] = $row_data;
		$cnt++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
}

else if(brp_strtolower($POST['mode']) == "add") {
	
	$email_content = brp_sc_mysql_escape( $_POST["email_content"], $dbcon );
	$email_content = str_ireplace(array("\r","\n",'\r','\n'),'', $email_content);

	$sms_content = brp_sc_mysql_escape( $_POST["sms_content"], $dbcon );
	$sms_content = str_ireplace(array("\r","\n",'\r','\n'),'', $sms_content);

	$info['template_title']	= $_POST['template_title'];
	$info['email_module_id'] = $_POST['email_module_id'];
	$info['email_subject'] = $_POST['email_subject'];
	$info['email_content'] = $email_content; 
	$info['sms_content'] = $sms_content;
	$info['email_cc'] = $_POST['email_cc'];
	$info['email_bcc'] = $_POST['email_bcc'];
	$info['print_page_id'] = $_POST['print_page_id'];
	$info['user_id']	= $_SESSION['user_id'];
	$info['company_id']	= $_SESSION['company_id'];
	$info['status']	= 0;
	$info['cdate']	= date("Y-m-d H:i:s");
	
	//CRM Module Condition
	if($info['email_module_id'] == 2) {
		if($_POST['task_id']) {
			$info['task_id'] = $_POST['task_id'];
		}
		
		if($_POST['stage_id']) {
			$info['stage_id'] = $_POST['stage_id'];
		}
	}
	
	$inserpoid=add_record('email_sms_template', $info, $dbcon);

	$arr['msg'] = ($inserpoid) ? '1' : '0';

	$arr['back']=$_POST['back'];
	echo json_encode($arr);					 
}	

else if(brp_strtolower($POST['mode']) == "edit") {

	$email_content = brp_sc_mysql_escape( $_POST["email_content"], $dbcon );
	$email_content = str_ireplace(array("\r","\n",'\r','\n'),'', $email_content);

	$sms_content = brp_sc_mysql_escape( $_POST["sms_content"], $dbcon );
	$sms_content = str_ireplace(array("\r","\n",'\r','\n'),'', $sms_content);

	$info['template_title']	= $_POST['template_title'];
	$info['email_module_id'] = $_POST['email_module_id'];
	$info['email_subject'] = $_POST['email_subject'];
	$info['email_content'] = $email_content; 
	$info['sms_content'] = $sms_content;
	$info['print_page_id'] = $_POST['print_page_id'];
	$info['email_cc'] = $_POST['email_cc'];
	$info['email_bcc'] = $_POST['email_bcc'];
	
	$task_id = $stage_id = '';
	//CRM Module Condition
	if($info['email_module_id'] == 2) {
		if($_POST['task_id']) {
			$task_id = $_POST['task_id'];
		}
		
		if($_POST['stage_id']) {
			$stage_id = $_POST['stage_id'];
		}
	}

	$info['task_id'] = $task_id;
	$info['stage_id'] = $stage_id;

	$info['user_id'] = $_SESSION['user_id'];
	$info['company_id']	= $_SESSION['company_id'];
	$info['mdate'] = date("Y-m-d H:i:s");
	
	$updateid = update_record('email_sms_template', $info,"email_sms_id=".$POST['eid'] , $dbcon);

	$arr['msg'] = ($updateid) ? '2' : '0';
	echo brp_json_encode($arr);	
}

else if(brp_strtolower($POST['mode']) == "get_email_type_based_on_module") {
	$module_id = $POST['module_id'];
	$emailmoduletypeid = $POST['emailmoduletypeid'];

	$res['email_type'] = get_email_type_based_on_module($dbcon,$module_id, $emailmoduletypeid);	

	echo brp_json_encode($res);	
}

else if(brp_strtolower($POST['mode']) == "get_insert_tags_data") {

	$module_id = $POST['module_id'];
	$sql = "select * from email_merge_fields where status = 0 and module_id = $module_id and company_id = '".$_SESSION['company_id']."' ";
	$exec = $dbcon->query($sql);

	$insert_tag = [];	
	while($rel=brp_mysqli_fetch_assoc($exec))
	{	
		$tag_key = EMAIL_INSERT_TAG_PREFIX.$rel['field_name'].EMAIL_INSERT_TAG_POSTFIX;
		$tag_value = $rel['field_name']; 

		$insert_tag[] = array($tag_key , $tag_value);
	}
	
	echo json_encode($insert_tag);
}

else if(brp_strtolower($POST['mode']) == "delete_email_template") {
	$updatedata['status'] = 2;
	$updatedata['mdate'] = date("Y-m-d H:i:s");
	$updatedata['user_id']	= $_SESSION['user_id'];

	$updateid1=update_record('email_sms_template', $updatedata, "email_sms_id=".$POST['email_sms_id'], $dbcon);
	$arr = [];
	$arr['res'] = ($updateid1) ? "1" : "0";
	echo brp_json_encode($arr);	
}

else if(brp_strtolower($POST['mode']) == "change_email_template_status") {
	$updatedata['status'] = $POST['status'];
	$updatedata['mdate'] = date("Y-m-d H:i:s");
	$updatedata['user_id']	= $_SESSION['user_id'];

	$updateid1=update_record('email_sms_template', $updatedata, "email_sms_id=".$POST['email_sms_id'], $dbcon);
	$arr = [];

	$arr['res'] = ($updateid1) ? "1" : "0";

	echo brp_json_encode($arr);	
}
		

// /*
// Code By Umair:  26/12/2020
// Comment: Display The Limited String From The Paragraph
// */
// 	function display_limited_length($str, $id, $limit, $strip = false) {
//     $str = ($strip == true)?strip_tags($str):$str;
//     if (strlen ($str) > $limit) {
//         $str = substr ($str, 0, $limit - 3);

//         $continue = '...';
//         return (substr ($str, 0, strrpos ($str, ' ')).$continue);
//     }
//     return trim($str);
// }

?>