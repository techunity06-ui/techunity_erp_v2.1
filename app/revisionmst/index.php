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
	
	if(strtolower($POST['mode']) == "fetch") {
		$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			
		$appData = array();
		$i=1;
		$aColumns = array('revision_id', 'revision_number', 'revision_status', 'user_id');
		$sIndexColumn = "revision_id";
		$isWhere = array("revision_status = 0");
		$sTable = "tbl_revision";			
		$isJOIN = array();
		$hOrder = "tbl_revision.revision_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['revision_number'];
			
			$edit_btn='';$delete_btn='';
			if($edit_btn_per){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['revision_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if($delete_btn_per){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_revision('.$row['revision_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn;
			
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		
		$tr = $dbcon -> query("SELECT `revision_id`,`revision_number`, `revision_status` FROM `tbl_revision` WHERE `revision_number` = '$POST[revision_number]'");
		if($tr->num_rows > 0) {
			$r = $tr -> fetch_assoc();
			if($r['revision_status'] != 0) {
				$info['revision_status']=0;
				$updateid=update_record('tbl_revision', $info,"revision_id=".$r['revision_id'] , $dbcon);						
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
			$info['revision_number']= $POST['revision_number'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			$inserid=add_record('tbl_revision', $info, $dbcon);
			if($inserid)
				echo "1";
			else
				echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {		
		$q = $dbcon -> query("SELECT * FROM `tbl_revision` WHERE `revision_id` = '$POST[id]' ");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		
		
		$info['revision_number']= $POST['revision_number'];			
		$info['cdate']		= date("Y-m-d H:i:s");				
		$updateid=update_record('tbl_revision', $info,"revision_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0".$dbcon->error;
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$info['revision_status']='2';
		$updateid=update_record('tbl_revision', $info,"revision_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
?>		