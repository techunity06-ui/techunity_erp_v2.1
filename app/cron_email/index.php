<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
include("../../include/function_database_query.php"); 

				

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

	if(strtolower($POST['mode']) == "fetch") {

		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		    CRON_EMAIL_SLUG_EDIT,CRON_EMAIL_SLUG_DELETE
		]);		
		 
		$where='';
		
		
		$appData = array();
		$i=1;
		$aColumns = array('cron.send_email_id','cron.email_user_id','u.user_name','u.common_email_id', 'cron.director_name', 'cron.email');
		$sIndexColumn = "cron.send_email_id";
		$isWhere = array("cron.status = 0 AND cron.company_id in (0,$_SESSION[company_id])");
		$sTable = " tbl_cron_send_email as cron";			
		$isJOIN = array("left join users as u on u.user_id = cron.email_user_id");
		$hOrder = "cron.send_email_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		// echo "ok";
		// print_r($sqlReturn);die;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			
			$row_data[] = $row['user_name'];
			$row_data[] = $row['common_email_id'];
			
			$edit_btn=''; $delete_btn=''; 
			
			if(in_array(CRON_EMAIL_SLUG_EDIT,$bulkAccessArray)){
			
				$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="javascript:;" onClick="edit_data('. $row['send_email_id'] .')"><i class="fa fa-pencil"></i></a>'; 
			}
			if(in_array(CRON_EMAIL_SLUG_DELETE,$bulkAccessArray)){
			
				$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_email('.$row['send_email_id'].')"><i class="fa fa-trash-o"></i></button>'; 
			}
			
			$row_data[] = $printcheckbox.' '.$edit_btn.' '.$delete_btn;
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode'])=="add") {
		// var_dump($_POST);die;
			$info['email_user_id']		= $POST['email_user_id'];
			$info['director_name']		= $POST['director_name'];
			$info['email']		= $POST['director_email'];
			
			$info['cdate']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];

			if (empty($POST['eid'])) {
				$inserid=add_record('tbl_cron_send_email', $info, $dbcon);

			}else{
				$inserid=update_record('tbl_cron_send_email', $info, "send_email_id=".$POST['eid'] , $dbcon);
			}
			
					
			$row['msg']='';
			
			if($inserid)
			{
				if (empty($POST['eid'])) {
					$row['msg'] ="1";
				}else{
					$row['msg'] ="update";
				}
			}
			else
			{
				$row['msg'] ="0";
			}
			
		
		echo json_encode($row);	
	}
	
	else if(strtolower($POST['mode']) == "delete") {
		//check Entry Record in TRN tables
		
			$info['status']		= 2;
			$updateid=update_record('tbl_cron_send_email', $info,"send_email_id=".$POST['eid'] , $dbcon);
			
			if($updateid)
				echo "1";	
			else
				echo "0";	
		
	}
	else if(strtolower($POST['mode'])== "get_cron_email_data") {
		
		$send_email_id = $POST['send_email_id'];
		$q = $dbcon->query("SELECT * from tbl_cron_send_email WHERE send_email_id =".$send_email_id);
	
		$r = $q->fetch_assoc();
		
		echo json_encode($r);
	}
	
?>