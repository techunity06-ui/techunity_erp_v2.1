<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/coman_function.php");

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
		$aColumns = array('inc.inc_id', 'inc.inc_name','inc.inc_status', 'inc.cdate', 'inc.user_id','inc.inc_group','g.g_name');
		$sIndexColumn = "inc.inc_id";
		$isWhere = array("inc.inc_status = 0");
		$sTable = "income_master as inc";			
		$isJOIN = array('left join tbl_group as g on g.g_id=inc.inc_group');
		$hOrder = "inc.inc_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['inc_name'];  
			$row_data[] = $row['g_name'];  
			
			$edit_btn='';$delete_btn='';
			if($edit_btn_per){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_income('.$row['inc_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if($delete_btn_per){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_expense('.$row['inc_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		$tr = $dbcon -> query("SELECT `inc_id`,`inc_name`,`inc_status` FROM `income_master` WHERE inc_status=0 and `inc_name` ='".$POST['inc_name']."' ");
		if($tr->num_rows > 0) {
			$resp['msg'] = '-1';
		}
		else {
			$info['inc_name']	= $POST['income_name'];							
			$info['inc_group']	= $POST['income_head_id'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$inserid=add_record('income_master', $info, $dbcon);
			
			$info_l['l_name']=$POST['income_name'];
			$info_l['l_group']=$POST['income_head_id'];
			$info_l['l_form']='income_form';
			$info_l['l_form_id']=$inserid;
			$info_l['l_form_table']='income_master';
			$info_l['cdate']	   = date("Y-m-d H:i:s");
			$info_l['user_id']	   = $_SESSION['user_id'];
			$info_l['company_id']  = $_SESSION['company_id'];
			
			add_record("tbl_ledger", $info_l, $dbcon);
			
			if($inserid){
				$resp['msg'] = "1";
				
			}
			else{
				$resp['msg'] = "0";
			}
		}
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `income_master` WHERE `inc_id` = '$POST[inc_id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$info['inc_name']	= $POST['income_name'];							
		$info['inc_group']	= $POST['income_head_id'];							
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$updateid=update_record('income_master', $info,"inc_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0".$dbcon->error; 
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['inc_status']='2';
		$updateid=update_record('income_master', $info,"inc_id=".$POST['inc_id'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}
	
	
?>