<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		//$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		//$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
		
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	CRM_INQUIRY_TYPE_MST_UPDATE,
	        CRM_INQUIRY_TYPE_MST_DELETE
	    ]);
		
		$appData = array();
		$i=1;
		$aColumns = array('inquiry_type_id', 'inquiry_type_name','status');
		$sIndexColumn = "inquiry_type_id";
		$isWhere = array("status = 0 and company_id IN (0,$_SESSION[company_id])");
		$sTable = "mst_inquiry_type";			
		$isJOIN = array();
		$hOrder = "inquiry_type_id desc";
		include('../../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['inquiry_type_name'];
			
			$edit_btn=''; $delete_btn='';  
			if(in_array(CRM_INQUIRY_TYPE_MST_UPDATE,$bulkAccessArray)){
				$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_source_mst('.$row['inquiry_type_id'].');"><i class="fa fa-pencil"></i></button>'; 
			}
			if(in_array(CRM_INQUIRY_TYPE_MST_DELETE,$bulkAccessArray)){
				$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_source_mst('.$row['inquiry_type_id'].')"><i class="fa fa-trash-o"></i></button>'; 
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		
		$tr = $dbcon -> query("SELECT inquiry_type_id,inquiry_type_name,status FROM `mst_inquiry_type` WHERE inquiry_type_name LIKE '".$POST['inquiry_type_name']."' and status='0'");
		if($tr->num_rows > 0) {
			
			$resp['resp']= '-1';
			
		}
		else {
			$info['inquiry_type_name']	= $_POST['inquiry_type_name'];	
			$info['user_id']	= $_SESSION['user_id'];	
			$info['company_id']	= $_SESSION['company_id'];	
			$inserid=add_record('mst_inquiry_type', $info, $dbcon);
			
			if($inserid){
				$resp['resp']= "1";
			}
			else{
				$resp['resp']= "0";
			}
		}
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `mst_inquiry_type` WHERE `inquiry_type_id` = '$POST[id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		
		$info['inquiry_type_name']	= $_POST['e_inquiry_type_name'];	
			$info['user_id']	= $_SESSION['user_id'];	
			$info['company_id']	= $_SESSION['company_id'];
		$updateid=update_record('mst_inquiry_type', $info,"inquiry_type_id=".$POST['eid'] , $dbcon);
				
		if($updateid)
			echo "1";
		else
			echo "0".$dbcon->error;
		
	}
	else if(strtolower($POST['mode']) == "delete") {

			$info['status']='2';
			$updateid=update_record('mst_inquiry_type', $info,"inquiry_type_id=".$POST['eid'] , $dbcon);
		
			if($updateid)
			echo "1";
			else
			echo "0";
	}
    
	
?>