<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(brp_strtolower($POST['mode']) == "fetch") {
		//Amish Soni 06-01-2021
		// $edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		// $delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
			EMAIL_MODULE_SLUG_EDIT,
			EMAIL_MODULE_SLUG_DELETE
		]);
			
		$appData = array();
		$i=1;
		$aColumns = array('email_module_id', 'name', 'status', 'user_id');
		$sIndexColumn = "email_module_id";
		$isWhere = array("status = 0");
		$sTable = "email_module_list";			
		$isJOIN = array();
		$hOrder = "email_module_list.email_module_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['name'];
			
			$edit_btn='';$delete_btn='';
			//Amish Soni 06-01-2021
			if(in_array(EMAIL_MODULE_SLUG_EDIT, $bulkAccessArray)) {
			// if($edit_btn_per){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['email_module_id'].');"><i class="fa fa-pencil"></i></button>';
			}

			//Amish Soni 06-01-2021
			if(in_array(EMAIL_MODULE_SLUG_DELETE, $bulkAccessArray)) {
			// if($delete_btn_per){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_module('.$row['email_module_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn;
			
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo brp_json_encode( $output );
	}
	else if(brp_strtolower($POST['mode']) == "add") {
		
		$tr = $dbcon -> query("SELECT * FROM `email_module_list` WHERE `name` = '".$POST['name']."' ");
		if($tr->num_rows > 0) {
			$r = $tr -> fetch_assoc();
			if($r['status'] != 0) {
				$info['status']=0;
				$updateid=update_record('email_module_list', $info,"email_module_id=".$r['email_module_id'] , $dbcon);						
				if($updateid)
					echo "1";
				else
					echo "0";
			}
			else {
				echo '-1';
			}
		}
		else {
			$info['name']= $POST['name'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			$inserid=add_record('email_module_list', $info, $dbcon);
			if($inserid)
				echo "1";
			else
				echo "0";
		}
		
	}
	else if(brp_strtolower($POST['mode']) == "preedit") {		
		$q = $dbcon -> query("SELECT * FROM `email_module_list` WHERE `email_module_id` = '".$POST['id']."' ");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(brp_strtolower($POST['mode']) == "edit") {
		
		
		$info['name']= $POST['name'];			
		$info['cdate']		= date("Y-m-d H:i:s");				
		$updateid=update_record('email_module_list', $info,"email_module_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0".$dbcon->error;
	}
	else if(brp_strtolower($POST['mode']) == "delete") {
		
		$info['status']='2';
		$updateid=update_record('email_module_list', $info,"email_module_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
?>		