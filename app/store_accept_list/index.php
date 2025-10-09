<?php
session_start(); //start session
$AJAX = true;
$path = '../../';
$include = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include($include."common_functions.php");

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		$appData = array();
		$where = '';
		if(!empty($POST['start_date']) && !empty($POST['end_date'])){
	        $where.="  AND date(machineconf.approve_date) >= '".date('Y-m-d',strtotime($POST['start_date']))."' AND date(machineconf.approve_date) <= '".date('Y-m-d',strtotime($POST['end_date']))."'";
	    }
	    
		$i=1;
		$aColumns = array('machineconf.id','machineconf.remark','machineconf.approve_status','machineconf.status','machineconf.approve_date', 'machineconf.user_id');
		$sIndexColumn = "machineconf.id";
		$isWhere = array("machineconf.status = 0 and machineconf.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_store_accept_configuration as machineconf";			
		$isJOIN = array();
		$hOrder = "machineconf.id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			if($row['approve_status']=="0"){
				$apstatus="<strong style='color:red;'>Accept Pending</strong>";
			}else{
				$apstatus="<strong style='color:green;'>Accepted</strong>";
			}
			$row_data[] = $row['sr'];
			$row_data[] = date('d-m-Y',strtotime($row['approve_date']));
			$row_data[] = $apstatus;
			$row_data[] = $row['remark'];
			$edit_btn=''; $delete_btn='';  

			$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'store_accept_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';

			$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_data('.$row['id'].')"><i class="fa fa-trash-o"></i></button>'; 

			$view_btn='<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.'store_accept_view/'.$row['id'].'"><i class="fa fa-eye"></i></a>';
			
			$row_data[] = $edit_btn.' '.$delete_btn.' '.$view_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		$info['approve_date']		= date('Y-m-d',strtotime($_POST['approve_date']));							
		$info['approve_status'] 	= $_POST['approve_status'];							
		$info['remark'] 			= $_POST['remark'];							
		$info['user_id']			= $_SESSION['user_id'];
		$info['company_id']			= $_SESSION['company_id'];

		$inserid=add_record('tbl_store_accept_configuration', $info, $dbcon);

		// Multiple Machine Configuration Image Upload
		$error=array();
		$extension=array("jpeg","jpg","png","gif");
		foreach($_FILES["upload_machine_file"]["tmp_name"] as $key=>$tmp_name) {
		    $file_name=$_FILES["upload_machine_file"]["name"][$key];
		    $file_tmp=$_FILES["upload_machine_file"]["tmp_name"][$key];
		    $ext=pathinfo($file_name,PATHINFO_EXTENSION);

		    if(in_array($ext,$extension)) {
		    	$path='../../view/upload/store_accept/';
		        if(!file_exists($path.$file_name)) {
		            move_uploaded_file($file_tmp=$_FILES["upload_machine_file"]["tmp_name"][$key],$path.$file_name);
		        }else {
		            $filename=basename($file_name,$ext);
		            $newFileName=$filename.time().".".$ext;
		            move_uploaded_file($file_tmp=$_FILES["upload_machine_file"]["tmp_name"][$key],$path.$newFileName);
		        }
		        $imageinfo['store_accept_id']	= $inserid;
		        $imageinfo['upload_machine_file']	= $file_name;
		        $inserimageid=add_record('tbl_store_accept_image_upload', $imageinfo, $dbcon);
		    }
		}
		if($inserid)
			echo "1";
		else
			echo "0";
	}
	else if(strtolower($POST['mode']) == "edit") {
		$info['approve_date']		= date('Y-m-d',strtotime($_POST['approve_date']));							
		$info['approve_status'] 	= $_POST['approve_status'];							
		$info['remark'] 			= $_POST['remark'];							
		$info['user_id']			= $_SESSION['user_id'];
		$info['company_id']			= $_SESSION['company_id'];
		$info['updated_at']			= date("Y-m-d H:i:s");	

		$updateid=update_record('tbl_store_accept_configuration', $info,"id=".$POST['eid'] , $dbcon);

		// Multiple Machine Configuration Image Upload
		if(isset($_FILES["upload_machine_file"]["tmp_name"]) && !empty($_FILES["upload_machine_file"]["tmp_name"])){
			$error=array();
			$extension=array("jpeg","jpg","png","gif");
			foreach($_FILES["upload_machine_file"]["tmp_name"] as $key=>$tmp_name) {
			    $file_name=$_FILES["upload_machine_file"]["name"][$key];
			    $file_tmp=$_FILES["upload_machine_file"]["tmp_name"][$key];
			    $ext=pathinfo($file_name,PATHINFO_EXTENSION);

			    if(in_array($ext,$extension)) {
			    	$path='../../view/upload/machine_configuration/';
			        if(!file_exists($path.$file_name)) {
			            move_uploaded_file($file_tmp=$_FILES["upload_machine_file"]["tmp_name"][$key],$path.$file_name);
			        }else {
			            $filename=basename($file_name,$ext);
			            $newFileName=$filename.time().".".$ext;
			            move_uploaded_file($file_tmp=$_FILES["upload_machine_file"]["tmp_name"][$key],$path.$newFileName);
			        }
			        $imageinfo['store_accept_id']	= $POST['eid'];
			        $imageinfo['upload_machine_file']	= $file_name;
			        $updateimageid=add_record('tbl_store_accept_image_upload', $imageinfo, $dbcon);
			    }
			}
		}
		if($updateid)
			echo "2";
		else
			echo "0".$dbcon->error;
		
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['status']='2';										
		$info['updated_at']		= date("Y-m-d H:i:s");
		$updateid=update_record('tbl_store_accept_configuration', $info,"id=".$POST['eid'] , $dbcon);
		$updateimageid=update_record('tbl_store_accept_image_upload', $info,"store_accept_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0";
	}
?>