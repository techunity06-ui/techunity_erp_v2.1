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
	        $where.="  AND date(machineconf.created_at) >= '".date('Y-m-d',strtotime($POST['start_date']))."' AND date(machineconf.created_at) <= '".date('Y-m-d',strtotime($POST['end_date']))."'";
	    }
	    if(!empty($POST['product_id'])){
	        $where.="  AND machineconf.product_id = '".$POST['product_id']."'";
	    }
	    if(!empty($POST['short_count'])){
	        $where.="  AND machineconf.short_count <= '".$POST['short_count']."'";
	    }

		$i=1;
		$aColumns = array('machineconf.id', 'machineconf.product_id', 'machineconf.process_id', 'machineconf.status', 'machineconf.user_id', 'machineconf.short_count', 'prod.product_name', 'pr.process_name');
		$sIndexColumn = "machineconf.id";
		$isWhere = array("machineconf.status = 0 and machineconf.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_machine_configuration as machineconf";			
		$isJOIN = array('left join product_mst prod on prod.product_id=machineconf.product_id',
						'left join process_mst as pr on pr.process_id=machineconf.process_id');
		$hOrder = "machineconf.id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['product_name'];
			$row_data[] = $row['process_name'];
			$row_data[] = $row['short_count'];
			$edit_btn=''; $delete_btn='';  

			$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'machine_configuration_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';

			$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_data('.$row['an_id'].')"><i class="fa fa-trash-o"></i></button>'; 

			$view_btn='<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.'machine_configuration_view/'.$row['id'].'"><i class="fa fa-eye"></i></a>';
			
			$row_data[] = $edit_btn.' '.$delete_btn.' '.$view_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		$info['product_id']	= $_POST['product_id'];							
		$info['process_id'] = $_POST['process_id'];							
		$info['short_count'] = $_POST['short_count'];							
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];

		$inserid=add_record('tbl_machine_configuration', $info, $dbcon);

		// Multiple Machine Configuration Image Upload
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
		        $imageinfo['machine_conf_id']	= $inserid;
		        $imageinfo['upload_machine_file']	= $file_name;
		        $inserimageid=add_record('tbl_machine_configuration_image_upload', $imageinfo, $dbcon);
		    }
		}
		if($inserid)
			echo "1";
		else
			echo "0";
	}
	else if(strtolower($POST['mode']) == "edit") {
		$info['product_id']	= $_POST['product_id'];							
		$info['process_id'] = $_POST['process_id'];							
		$info['short_count'] = $_POST['short_count'];							
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$info['updated_at']	= date("Y-m-d H:i:s");	

		$updateid=update_record('tbl_machine_configuration', $info,"id=".$POST['eid'] , $dbcon);

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
			        $imageinfo['machine_conf_id']	= $POST['eid'];
			        $imageinfo['upload_machine_file']	= $file_name;
			        $updateimageid=add_record('tbl_machine_configuration_image_upload', $imageinfo, $dbcon);
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
		$updateid=update_record('tbl_machine_configuration', $info,"id=".$POST['eid'] , $dbcon);
		$updateimageid=update_record('tbl_machine_configuration_image_upload', $info,"machine_conf_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0";
	}else if(strtolower($POST['mode']) == "load_process") {
		$product='';
		$product_id = $_REQUEST['id'];
		$product_qry="select p.process_id,p.process_priority,pr.process_name from tbl_product_process as p left join process_mst as pr on pr.process_id=p.process_id where p.status=0 and p.product_id='".$product_id."' order by p.process_priority"; 
		$product_data = $dbcon->query($product_qry);	
		$product.= '<option value="">Select Process</option>';	
		while($r=mysqli_fetch_assoc($product_data))
		{	
			$sel='';	
			if($r['process_id']==$product_id)
			{$sel='selected="selected"';}
			$product .= '<option '.$sel.' value="'.$r['process_id'].'">'.$r['process_name'].'</option>';
		}						
		echo $product;
		die;
	}
?>