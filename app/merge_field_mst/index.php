<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions/common_functions.php");


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
		MERGE_FIELD_SLUG_EDIT,
		MERGE_FIELD_SLUG_DELETE
	]);
		
	$appData = array();
	$i=1;
	$aColumns = array('emf.email_merge_id', 'emf.field_name','emf.module_id', 'emf.status', 'emf.user_id', 'ml.name', 'emf.table_name', 'emf.replace_with');
	$sIndexColumn = "emf.email_merge_id";
	$isWhere = array("emf.status = 0");
	$sTable = "email_merge_fields AS emf";			
	$isJOIN = array("left join email_module_list as ml on emf.module_id = ml.email_module_id");
	$hOrder = "emf.email_merge_id";
	include('../../include/pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['field_name'];
		$row_data[] = $row['table_name'];
		$row_data[] = $row['replace_with'];
		$row_data[] = $row['name'];
		
		$edit_btn='';$delete_btn='';
		//Amish Soni 06-01-2021
		if(in_array(MERGE_FIELD_SLUG_EDIT, $bulkAccessArray)) {
		// if($edit_btn_per){
			$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['email_merge_id'].');"><i class="fa fa-pencil"></i></button>';
		}

		//Amish Soni 06-01-2021
		if(in_array(MERGE_FIELD_SLUG_DELETE, $bulkAccessArray)) {
		// if($delete_btn_per){
			$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_field('.$row['email_merge_id'].')"><i class="fa fa-trash-o"></i></button>';
		}
		
		$row_data[] = $edit_btn.' '.$delete_btn;
		
		
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo brp_json_encode( $output );
}
else if(brp_strtolower($POST['mode']) == "add") {
	$tr = $dbcon -> query("SELECT * FROM `email_merge_fields` WHERE `field_name` = '".$POST['field_name']."' AND module_id = '".$POST['module_id']."'");
	if($tr->num_rows > 0) {
		$r = $tr -> fetch_assoc();
		if($r['status'] != 0) {
			$info['status'] = 0;
			$updateid=update_record('email_merge_fields', $info,"email_merge_id=".$r['email_merge_id'] , $dbcon);						
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
		$info['field_name']= $_POST['field_name'];
		// Amish Soni Start 13-01-2021
		$info['table_name'] = $_POST['table_name'];
		if($info['table_name'] == 'other') {
			$info['replace_with'] = $_POST['replace_with_text'];
		} else {
			$info['replace_with'] = $_POST['replace_with_select'];
			$primary_key = getPKColumnFromTable($dbcon, $info['table_name']);
			if($primary_key) {
				$info['primary_id'] = $primary_key;
			}
		}
		// Amish Soni End 13-01-2021
		$info['module_id'] = $_POST['module_id'];						
		$info['cdate'] = date("Y-m-d H:i:s");
		$info['user_id'] = $_SESSION['user_id'];
		$info['company_id'] = $_SESSION['company_id'];
		
		$inserid = add_record('email_merge_fields', $info, $dbcon);
		echo ($inserid) ? '1' : '0'.$dbcon->error;
	}
	
}
else if(brp_strtolower($POST['mode']) == "preedit") {		
	$q = $dbcon -> query("SELECT * FROM `email_merge_fields` WHERE `email_merge_id` = '".$POST['id']."' ");
	$r = $q->fetch_assoc();
	echo brp_json_encode($r);
}
else if(brp_strtolower($POST['mode']) == "edit") {
	
	$info['field_name']= $POST['field_name'];
	// Amish Soni Start 13-01-2021
	$info['table_name'] = $_POST['table_name'];
	if($info['table_name'] == 'other') {
		$info['replace_with'] = $_POST['replace_with_text'];
		$info['primary_id'] = NULL;
	} else {
		$info['replace_with'] = $_POST['replace_with_select'];
		$primary_key = getPKColumnFromTable($dbcon, $info['table_name']);
		if($primary_key) {
			$info['primary_id'] = $primary_key;
		}				
	}
	$info['user_id'] = $_SESSION['user_id'];
	$info['company_id'] = $_SESSION['company_id'];
	// Amish Soni End 13-01-2021
	$info['module_id'] = $_POST['module_id'];				
	$info['cdate'] = date("Y-m-d H:i:s");				
	$updateid = update_record('email_merge_fields', $info,"email_merge_id=".$POST['eid'] , $dbcon);
	
	echo ($updateid) ? '1' : '0'.$dbcon->error;
}
else if(brp_strtolower($POST['mode']) == "delete") {
	
	$info['status']='2';
	$updateid = update_record('email_merge_fields', $info,"email_merge_id=".$POST['eid'] , $dbcon);
	
	echo ($updateid) ? '1' : '0';
}
// Amish Soni Start 12-01-2021
else if(brp_strtolower($POST['mode']) == "get_columns") {
	$fields = '<option value="" selected>Please Select</option>';
	$table_name = $POST['table_name'];
	$fields .= getColumnsFromTable($dbcon, $table_name);
	echo $fields;
}
// Amish Soni End 12-01-2021
?>		