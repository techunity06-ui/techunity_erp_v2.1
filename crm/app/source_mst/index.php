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
		//check paermission for party industry add
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	CUSTOMER_SOURCE_SLUG_UPDATE,
	        CUSTOMER_SOURCE_SLUG_DELETE
	    ]);
		
		$appData = array();
		$i=1;
		$branch_id = $POST['branch_id'];
		$where='';
	    if($branch_id){
	        $where .= check_branch('tblref',$branch_id);
	    }
		$aColumns = array('tblref.rb_id', 'tblref.rb_name','tblref.rb_status');
		$sIndexColumn = "tblref.rb_id";
		$isWhere = array("tblref.rb_status = 0  and tblref.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_refer_by as tblref";			
		$isJOIN = array();
		$hOrder = "tblref.rb_id desc";
		include('../../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['rb_name'];
			
			$edit_btn=''; $delete_btn='';  
			if(in_array(CUSTOMER_SOURCE_SLUG_UPDATE,$bulkAccessArray)){
				$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_source_mst('.$row['rb_id'].');"><i class="fa fa-pencil"></i></button>'; 
			}
			if(in_array(CUSTOMER_SOURCE_SLUG_DELETE,$bulkAccessArray)){
				$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_source_mst('.$row['rb_id'].')"><i class="fa fa-trash-o"></i></button>'; 
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
		
		$tr = $dbcon -> query("SELECT `rb_id`,`rb_name`,`rb_status` FROM `tbl_refer_by` WHERE `rb_name` ='".$POST['rb_name']."' and rb_status='0' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			
			$resp['resp']= '-1';
			
		}
		else {
			$info['rb_name']	= $POST['rb_name'];
			$info['company_id']	= $_SESSION['company_id'];
			$inserid=add_record('tbl_refer_by', $info, $dbcon, $branch_id);
			
			if($inserid){
				//Insert LOG
				$log_entry=common_log_entry($dbcon,"source_mst_add",1,"tbl_refer_by",$inserid);
				if(strtolower($POST['source_mst_model']) == "source_mst_model"){
					$sel_qry="select * from tbl_refer_by where rb_id=".$inserid;
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
		$q = $dbcon -> query("SELECT * FROM `tbl_refer_by` WHERE `rb_id` = '$POST[id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {

		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `rb_id`,`rb_name`,`rb_status` FROM `tbl_refer_by` WHERE `rb_name` ='".$POST['e_rb_name']."' and rb_status='0' and `rb_id` != '".$POST['eid']."'  and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			
			echo "-1";
			
		} else {
			$info['rb_name']	= $POST['e_rb_name'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['updated_at']	= date('Y-m-d H:i:s');			
			$updateid=update_record('tbl_refer_by', $info,"rb_id=".$POST['eid'] , $dbcon, $branch_id);
			
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"source_mst_add",2,"tbl_refer_by",$POST['eid']);
					
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
		
	}
	else if(strtolower($POST['mode']) == "delete") {
		//check Entry Record in TRN tables
		$chk_arr[]=array("inquiry_id","tbl_inquiry","inquiry_status=0 and rb_id=".$POST['eid']);
		$chk_resp=check_delete_trn($dbcon,$chk_arr);
		
		if($chk_resp){
			echo '-1';
		}
		else{
			$info['rb_status']='2';
			$updateid=update_record('tbl_refer_by', $info,"rb_id=".$POST['eid'] , $dbcon);
			
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"source_mst_add",3,"tbl_refer_by",$POST['eid']);
		
			if($updateid)
			echo "1";
			else
			echo "0";	
		}
	}
    
	
?>