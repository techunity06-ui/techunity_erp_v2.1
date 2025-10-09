<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		/* $edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon); */
		
		$appData = array();
		$i=1;
		$aColumns = array('tap.i_id','ref.rb_name','tap.trade_india_user_id','tap.trade_india_profile_id','tap.trad_india_api_key','tap.i_status');
		$sIndexColumn = "tap.i_id";
		$isWhere = array("tap.i_status = 0");
		$sTable = "tbl_trade_india_api as tap";			
		$isJOIN = array("left join tbl_refer_by as ref on ref.rb_id=tap.source_id");
		$hOrder = "tap.i_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['trade_india_user_id'];
			$row_data[] = $row['trade_india_profile_id'];
			$row_data[] = $row['trad_india_api_key'];
			
			$edit_btn=''; $delete_btn='';  
			//if($edit_btn_per){ 
				$edit_btn=' <button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_trade_india('.$row['i_id'].');"><i class="fa fa-pencil"></i></button>'; 
			//}
			//if($delete_btn_per){
				$delete_btn=' <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_cust_ind('.$row['i_id'].')"><i class="fa fa-trash-o"></i></button>'; 
			//}
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		
		$tr = $dbcon -> query("SELECT `i_id`,`mobile_no`,`i_status` FROM `tbl_indiamart_api` WHERE `trade_india_user_id` ='".$POST['trade_india_user_id']."' and `trade_india_profile_id` ='".$POST['trade_india_profile_id']."' and `trad_india_api_key` ='".$POST['trad_india_api_key']."' and i_status='0'");
		if($tr->num_rows > 0) {
			
			$resp['resp']= '-1';
			
		}
		else {
			$info['trade_india_user_id']	= $_POST['trade_india_user_id'];	
			$info['trade_india_profile_id']	= $_POST['trade_india_profile_id'];	
			$info['trad_india_api_key']	= $_POST['trad_india_api_key'];	
			$info['source_id']	= $_POST['source_id'];	
			$info['company_id']	= $_SESSION['company_id'];		
		$info['user_id']	= $_SESSION['user_id'];	
			$inserid=add_record('tbl_trade_india_api', $info, $dbcon);
			
			if($inserid){
				//Insert LOG
				//$log_entry=common_log_entry($dbcon,"cust_ind_mst_add",1,"tbl_indiamart_api",$inserid);
				if(strtolower($POST['cust_ind_model']) == "cust_ind_model"){
					$sel_qry="select * from tbl_trade_india_api where i_id=".$inserid;
					$sel_rel=mysqli_fetch_assoc($dbcon->query($sel_qry));
					$resp=$sel_rel;
					$resp['resp']= "2";
				}
				else{
					$resp['resp']= "1";
				}
			}
			else{
				$resp['resp']= "0";
			}
		}
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `tbl_trade_india_api` WHERE `i_id` = '$POST[id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		
		$info['trade_india_user_id']	= $_POST['trade_india_user_id'];		
		$info['trade_india_profile_id']	= $_POST['trade_india_profile_id'];		
		$info['trad_india_api_key']		= $_POST['trad_india_api_key'];		
		$info['source_id']				= $_POST['source_id'];	
		$info['company_id']	= $_SESSION['company_id'];		
		$info['user_id']	= $_SESSION['user_id'];		
		$updateid=update_record('tbl_trade_india_api', $info,"i_id=".$POST['eid'] , $dbcon);
		
		//Insert LOG
		//$log_entry=common_log_entry($dbcon,"cust_ind_mst_add",2,"tbl_indiamart_api",$POST['eid']);
				
		if($updateid)
			echo "1";
		else
			echo "0".$dbcon->error;
		
	}
	else if(strtolower($POST['mode']) == "delete") {
		
			$info['i_status']='2';
			$updateid=update_record('tbl_trade_india_api', $info,"i_id=".$POST['eid'] , $dbcon);
			
			if($updateid)
			echo "1";
			else
			echo "0";	
		
	}
    
	
?>