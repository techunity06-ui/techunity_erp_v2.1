<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include_once("../../include/common_functions.php");
//error_reporting(E_ALL);
//include("../../config/session.php");
include("../../include/function_database_query.php");
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		//check permission for party industry add
			
		$appData = array();
		$i=1;
		$aColumns = array('module_type_id', 'module_name', 'status');
		$sIndexColumn = "module_type_id";
		$isWhere = array("status = 0 ");
		$sTable = "tbl_module_type";			
		$isJOIN = array();
		$hOrder = "module_type_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['module_name'];
			
			$edit_btn='';$delete_btn='';
			
			$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['module_type_id'].');"><i class="fa fa-pencil"></i></button>';
			
			$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_module('.$row['module_type_id'].')"><i class="fa fa-trash-o"></i></button>';
			
			
			$row_data[] = $edit_btn.' '.$delete_btn;
			$appData[] = $row_data;
			$id++;
		
			$output['aaData'] = $appData;
		}
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
	
		$tr = $dbcon -> query("SELECT `module_type_id`,`module_name`, `status` FROM `tbl_module_type` WHERE `status`=0 and `make_name` = '".$POST['make_name']."'");
		if($tr->num_rows > 0) {
			$r = $tr -> fetch_assoc();
			if($r['status'] != 0) {
				$info['status']=0;
				$updateid=update_record('tbl_module_type', $info,"module_type_id=".$r['module_type_id'] , $dbcon);						
				if($updateid)
					echo "1";
				else
					echo "0";
			}
			else {
				echo '-1';
			}
		} else {
			$info['module_name']	= $POST['module_name'];							
			$info['cdate']			= date("Y-m-d H:i:s");
			$inserid=add_record('tbl_module_type', $info, $dbcon);
			if($inserid)
				echo "1";
			else
				echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {		
		$q = $dbcon -> query("SELECT * FROM `tbl_module_type` WHERE `module_type_id` = '$POST[id]' ");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		
		$tr = $dbcon -> query("SELECT `module_type_id`,`module_name`, `status` FROM `tbl_module_type` WHERE `status`=0 and `module_name` = '".$POST['module_name']."' and `module_type_id` != '".$POST['eid']."'");
		if($tr->num_rows > 0) {
			echo "-1";
		}else{
			$info['module_name']= $POST['module_name'];			
			$info['cdate']		= date("Y-m-d H:i:s");				
			$updateid=update_record('tbl_module_type', $info,"module_type_id=".$POST['eid'] , $dbcon);
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$info['status']='2';
		$updateid=update_record('tbl_module_type', $info,"module_type_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
?>		