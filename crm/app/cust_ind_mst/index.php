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
		//check permission for party industry add
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	CUSTOMER_PARTY_INDUSTRY_SLUG_UPDATE,
	        CUSTOMER_PARTY_INDUSTRY_SLUG_DELETE
	    ]);
		
		$appData = array();
		$i=1;
		$branch_id = $POST['branch_id'];
		$where='';
	    if($branch_id){
	        $where .= check_branch('custindu',$branch_id);
	    }
		$aColumns = array('custindu.ci_id', 'custindu.ci_name','custindu.ci_status');
		$sIndexColumn = "custindu.ci_id";
		$isWhere = array("custindu.ci_status = 0 and custindu.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_customer_industry as custindu";			
		$isJOIN = array();
		$hOrder = "custindu.ci_id desc";
		include('../../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['ci_name'];
			
			$edit_btn=''; $delete_btn='';  
			if(in_array(CUSTOMER_PARTY_INDUSTRY_SLUG_UPDATE,$bulkAccessArray)){
				$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_cust_ind('.$row['ci_id'].');"><i class="fa fa-pencil"></i></button>'; 
			}
			if(in_array(CUSTOMER_PARTY_INDUSTRY_SLUG_DELETE,$bulkAccessArray)){
				$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_cust_ind('.$row['ci_id'].')"><i class="fa fa-trash-o"></i></button>'; 
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `ci_id`,`ci_name`,`ci_status`,`company_id` FROM `tbl_customer_industry` WHERE `ci_name` ='".$POST['ci_name']."' and ci_status='0' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			$resp['resp']= '-1';
		}
		else {
			$info['ci_name']	= $POST['ci_name'];
			$info['company_id']			= $_SESSION['company_id'];
				
			$inserid=add_record('tbl_customer_industry', $info, $dbcon, $branch_id);
			
			if($inserid){
				//Insert LOG
				$log_entry=common_log_entry($dbcon,"cust_ind_mst_add",1,"tbl_customer_industry",$inserid);
				if(strtolower($POST['cust_ind_model']) == "cust_ind_model"){
					$sel_qry="select * from tbl_customer_industry where ci_id=".$inserid;
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
		$q = $dbcon -> query("SELECT * FROM `tbl_customer_industry` WHERE `ci_id` = '$POST[id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {

		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['e_branch_id']) && $POST['e_branch_id']) ? $POST['e_branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `ci_id`,`ci_name`,`ci_status`,`company_id` FROM `tbl_customer_industry` WHERE `ci_name` ='".$POST['e_ci_name']."' and ci_status='0' and ci_id != '".$POST['eid']."' and `company_id`='".$_SESSION['company_id']."'");

		if($tr->num_rows > 0) {
			echo '-1';
		}else {
			$info['ci_name']	= $POST['e_ci_name'];
			$info['company_id']	= $_SESSION['company_id'];
			// $info['updated_at']	= date('Y-m-d H:i:s');		
			$updateid=update_record('tbl_customer_industry', $info,"ci_id=".$POST['eid'] , $dbcon, $branch_id);
			
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"cust_ind_mst_add",2,"tbl_customer_industry",$POST['eid']);
					
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
		
	}
	else if(strtolower($POST['mode']) == "delete") {
		//check Entry Record in TRN tables
		$chk_arr[]=array("cust_id","tbl_customer","cust_status=0 and cust_ind=".$POST['eid']);
		$chk_resp=check_delete_trn($dbcon,$chk_arr);
		
		if($chk_resp){
			echo '-1';
		}
		else{
			$info['ci_status']='2';
			$updateid=update_record('tbl_customer_industry', $info,"ci_id=".$POST['eid'] , $dbcon);
			
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"cust_ind_mst_add",3,"tbl_customer_industry",$POST['eid']);
		
			if($updateid)
			echo "1";
			else
			echo "0";	
		}
	}
    
	
?>