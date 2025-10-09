<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
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
		$aColumns = array('model_id', 'pro.product_name', 'model_name', 'mst.cdate', 'model_status', 'mst.user_id');
		$sIndexColumn = "model_id";
		$isWhere = array("model_status = 0");
		$sTable = "model_mst as mst";
		$isJOIN = array('left join product_mst as pro on pro.product_id=mst.product_id');
		$hOrder = "mst.model_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['product_name'];
			$row_data[] = $row['model_name'];
			
			$edit_btn='';$delete_btn='';$alloc_btn='';
			if($edit_btn_per){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_model('.$row['model_id'].');"><i class="fa fa-pencil"></i></button>';
				$alloc_btn='<button class="btn btn-xs btn-primary" data-original-title="Allocate Required Product" data-toggle="tooltip" data-placement="top" onClick="alloc_req_pro('.$row['model_id'].');"><i class="fa fa-plus"></i></button>';
			}
			if($delete_btn_per){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_model('.$row['model_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			$row_data[] = $alloc_btn.' '.$edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		$tr = $dbcon -> query("SELECT `model_id`,`model_name`,`model_status` FROM `model_mst` WHERE model_status=0 and `model_name` ='".$POST['model_name']."' and `product_id` ='".$POST['product_id']."'");
		if($tr->num_rows > 0) {
			$resp['msg'] = '-1';
		}
		else {
			$info['product_id']	= $POST['product_id'];
			$info['model_name']	= $POST['model_name'];
			$info['model_desc']	= $_POST['model_desc'];
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$inserid=add_record('model_mst', $info, $dbcon);
			
			if($inserid){
				$resp['msg'] = "1";
				if(strtolower($POST['model_model']) == "model_model"){
					$zone_qry="select * from model_mst where model_id=".$inserid; 
					$zone_rel=mysqli_fetch_assoc($dbcon->query($zone_qry));
					$resp=$zone_rel;
					$resp['msg'] = "2"; 
				}
			}
			else{
				$resp['msg'] = "0";
			}
		}
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `model_mst` WHERE `model_id` = '$POST[model_id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$info['product_id']	= $POST['product_id'];							
		$info['model_name']	= $POST['model_name'];							
		$info['model_desc']	= $_POST['model_desc'];							
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$updateid=update_record('model_mst', $info,"model_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0".$dbcon->error; 
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['model_status']='2';
		$updateid=update_record('model_mst', $info,"model_id=".$POST['model_id'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}
	else if(strtolower($POST['mode']) == "add_req_field") { 
		$info1['model_id']				= $POST['model_id'];
		$info1['req_product_id']		= $POST['req_product_id'];
		$info1['req_product_qty']		= $POST['req_product_qty']; 
		$info1['cdate']					= date("Y-m-d H:i:s");
		$info1['user_id']				= $_SESSION['user_id'];
		$info1['company_id']			= $_SESSION['company_id'];
		$table='model_pro_alloc_mst';$tableid='model_pro_alloc_mst_id';
		
		if(empty($POST['edit_id'])) {
			$inserid=add_record($table, $info1, $dbcon);
		}
		else {
			$updateid=update_record($table, $info1, $tableid."=".$POST['edit_id'], $dbcon);	
		}
	}
	else if(strtolower($POST['mode']) == "show_req_pro") {
		if($POST['model_id']!=""){
		  $where ="and imst.model_id =".$POST['model_id'];
		}
		$appData = array();
		$i=1;
		$aColumns = array('model_pro_alloc_mst_id','pro.product_name','req_product_qty');
		$sIndexColumn = "model_pro_alloc_mst_id";
		$isWhere = array("model_pro_alloc_mst_status=0 ".$where."  and imst.company_id in (0,$_SESSION[company_id])");
		$sTable = "model_pro_alloc_mst as imst";
		$isJOIN = array("left join product_mst as pro on pro.product_id=imst.req_product_id");
		$hOrder = "imst.model_pro_alloc_mst_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['product_name'];
			$row_data[] = $row['req_product_qty'];
			
			$row_data[] = '<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_reqpro('.$row['model_pro_alloc_mst_id'].');"><i class="fa fa-pencil"></i></button>
				<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_req_product('.$row['model_pro_alloc_mst_id'].')"><i class="fa fa-trash-o"></i></button>';
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "preedit_reqpro") {	
		$q = $dbcon -> query("SELECT * FROM `model_pro_alloc_mst` WHERE model_pro_alloc_mst_status=0 and  `model_pro_alloc_mst_id` = '$POST[model_pro_alloc_mst_id]'");
		$r = $q->fetch_assoc(); 
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "delete_req_product") {
		$info['model_pro_alloc_mst_status']='2';
		$updateid=update_record('model_pro_alloc_mst', $info, "model_pro_alloc_mst_id=".$POST['model_pro_alloc_mst_id'], $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0";
	}
	
	
?>