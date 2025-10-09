<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
$incPath = $path.'include/';

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
	
	if(strtolower($POST['mode']) == "fetch") {

		//check permission for india mart api
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	INDIA_MART_API_SLUG_EDIT,
	        INDIA_MART_API_SLUG_DELETE
	    ]);
		
		$appData = array();
		$i=1;
		$aColumns = array('ind.i_id','ref.rb_name','ind.mobile_no','ind.api_key','ind.i_status');
		$sIndexColumn = "ind.i_id";
		$isWhere = array("ind.i_status = 0 and ind.company_id IN (0,$_SESSION[company_id])");
		$sTable = "tbl_indiamart_api as ind";			
		$isJOIN = array("left join tbl_refer_by as ref on ref.rb_id=ind.source_id");
		$hOrder = "ind.i_id desc";
		include($incPath.'pagging.php');
		//$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['rb_name'];
			$row_data[] = $row['mobile_no'];
			$row_data[] = $row['api_key'];
			
			$edit_btn=''; $delete_btn='';  
			if(in_array(INDIA_MART_API_SLUG_EDIT,$bulkAccessArray)){
				$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_cust_ind('.$row['i_id'].');"><i class="fa fa-pencil"></i></button>'; 
			}
			if(in_array(INDIA_MART_API_SLUG_DELETE,$bulkAccessArray)){
				$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_cust_ind('.$row['i_id'].')"><i class="fa fa-trash-o"></i></button>'; 
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		
		$tr = $dbcon -> query("SELECT `i_id`,`mobile_no`,`i_status` FROM `tbl_indiamart_api` WHERE `mobile_no` ='".$POST['mobile_no']."' and `api_key` ='".$POST['api_key']."' and i_status='0'");
		if($tr->num_rows > 0) {
			
			$resp['resp']= '-1';
			
		}
		else {
			$info['mobile_no']	= $POST['mobile_no'];	
			$info['api_key']	= $_POST['api_key'];	
			$info['source_id']	= $_POST['source_id'];	
			$info['company_id']	= $_SESSION['company_id'];	
			$info['user_id']	= $_SESSION['user_id'];	
			$inserid=add_record('tbl_indiamart_api', $info, $dbcon);
			
			if($inserid){
				//Insert LOG
				//$log_entry=common_log_entry($dbcon,"cust_ind_mst_add",1,"tbl_indiamart_api",$inserid);
				if(strtolower($POST['cust_ind_model']) == "cust_ind_model"){
					$sel_qry="select * from tbl_indiamart_api where i_id=".$inserid;
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
		$q = $dbcon -> query("SELECT * FROM `tbl_indiamart_api` WHERE `i_id` = '$POST[id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		
		$info['mobile_no']	= $POST['mobile_no'];		
		$info['api_key']	= $_POST['api_key'];		
		$info['source_id']	= $_POST['source_id'];		
		$info['company_id']	= $_SESSION['company_id'];		
		$info['user_id']	= $_SESSION['user_id'];		
		$updateid=update_record('tbl_indiamart_api', $info,"i_id=".$POST['eid'] , $dbcon);
		
		//Insert LOG
		//$log_entry=common_log_entry($dbcon,"cust_ind_mst_add",2,"tbl_indiamart_api",$POST['eid']);
				
		if($updateid)
			echo "1";
		else
			echo "0".$dbcon->error;
		
	}
	else if(strtolower($POST['mode']) == "delete") {
		
			$info['i_status']='2';
			$updateid=update_record('tbl_indiamart_api', $info,"i_id=".$POST['eid'] , $dbcon);
			
			if($updateid)
			echo "1";
			else
			echo "0";	
		
	}
    
	
?>