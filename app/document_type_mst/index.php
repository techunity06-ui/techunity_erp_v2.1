<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include_once("../../include/common_functions/common_functions.php");
include_once("../../include/function_database_query.php");

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		//check permission for party industry add
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	ADMINISTRATOR_DOCUMENT_TYPE_MST_UPDATE,
	        ADMINISTRATOR_DOCUMENT_TYPE_MST_DELETE
	    ]);
		 
		$appData = array();
		$i=1;
		$aColumns = array('document.document_id', 'document.document_name','document.document_status');
		$sIndexColumn = "document.document_id";
		$isWhere = array("document.document_status !=2 and document.company_id=".$_SESSION['company_id']);
		$sTable = "tbl_document_type_mst as document";
		$isJOIN = array();
		$hOrder = "document.document_name asc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['document_name'];

			$edit_btn='';$del_btn=''; 
			if($row['document_id']!='0'){ 
				if(in_array(ADMINISTRATOR_DOCUMENT_TYPE_MST_UPDATE,$bulkAccessArray)){
					$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_document('.$row['document_id'].');"><i class="fa fa-pencil"></i></button>';
				}
				if(in_array(ADMINISTRATOR_DOCUMENT_TYPE_MST_DELETE,$bulkAccessArray)){
					$del_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_document('.$row['document_id'].')"><i class="fa fa-trash-o"></i></button>';
				} 
			}

			

			$row_data[] = $pro_stck_btn.' '.$edit_btn.' '.$del_btn;
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		$tr = $dbcon -> query("SELECT `document_name`, `document_status` FROM `tbl_document_type_mst` WHERE `document_name` ='".$_POST['document_name']."' and `company_id` ='".$_SESSION['company_id']."' and `document_status`=0 ");
		if($tr->num_rows > 0) {
			$r = $tr -> fetch_assoc();
			if($r['document_status'] != 0) {
				$info['document_status']=0;
				$updateid=update_record('tbl_document_type_mst', $info,"document_id=".$r['document_id'] , $dbcon);	
				
				if($updateid)
					$row['res'] ="1";
				else
					$row['res'] ="0";
			}
			else {
				$row['res'] ="-1";
			}
		} else {
				$info['document_name']		= $_POST['document_name'];
				$info['user_id']			= $_SESSION['user_id'];
				$info['branch_id']		= $_SESSION['branch_id'];
				$info['company_id']			= $_SESSION['company_id'];
				$inserid=add_record('tbl_document_type_mst', $info, $dbcon);
	 
			if($inserid){
				if(strtolower($POST['document_model'])=="document_model"){
					$query="select * from tbl_document_type_mst where document_id=".$inserid;
					$rel=mysqli_fetch_assoc($dbcon->query($query));		
					$row = $rel;
					$row['res']="2"; 
				}
				else{
					$row['res'] ="1";
				}
			}
			else{
				$row['res'] ="0";
			}
			echo json_encode($row);		
		}
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `tbl_document_type_mst` WHERE `document_id` = '".$POST['id']."'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$tr = $dbcon -> query("SELECT `document_id`,`document_name`,`document_status` FROM `tbl_document_type_mst` WHERE `document_name` ='".$_POST['document_name']."' and `company_id` ='".$_SESSION['company_id']."' and  `document_id` != '".$POST['eid']."' and `document_status`=0");
		if($tr->num_rows > 0) {
			echo "-1";
		} else {
			$info['document_name']		= $_POST['document_name'];
			$info['user_id']			= $_SESSION['user_id'];
			$info['branch_id']		= $_SESSION['branch_id'];
			$info['company_id']			= $_SESSION['company_id'];
			$updateid=update_record('tbl_document_type_mst', $info,"document_id=".$POST['eid'] , $dbcon);
		 
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['document_status']='2';
		$updateid=update_record('tbl_document_type_mst', $info,"document_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0";
	}
   
?>