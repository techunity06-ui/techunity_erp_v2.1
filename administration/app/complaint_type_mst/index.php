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
	
if(brp_strtolower($POST['mode']) == "fetch") {
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		ADMINISTRATOR_COMPLAINT_TYPE_UPDATE,
		ADMINISTRATOR_COMPLAINT_TYPE_DELETE
	]);
	
	$where='';

	//branch , company, user check start - dhaval 
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$where_db = check_branch('zmst', $branch_id);
	
	$where.=" $where_db";

	$where_company=check_company('zmst');

	$where.=" $where_company";

	$where_user=check_user('zmst');

	$where.=" $where_user";
	// branch , comapny , user check end - dhaval
   
	$appData = array();
	$i=1;
	$aColumns = array('zmst.complaint_type_id', 'zmst.complaint_type_name','zmst.cdate', 'zmst.complaint_type_status', 'zmst.user_id');
	$sIndexColumn = "zmst.complaint_type_id";
	$isWhere = array("zmst.complaint_type_status = 0 ".$where);
	$sTable = "complaint_type_mst as zmst";			
	$isJOIN = array();
	$hOrder = "zmst.complaint_type_id desc";
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['complaint_type_name']; 
		
		$edit_btn='';$delete_btn='';
		if(in_array(ADMINISTRATOR_COMPLAINT_TYPE_UPDATE, $bulkAccessArray)) {
			$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_complaint_type('.$row['complaint_type_id'].');"><i class="fa fa-pencil"></i></button>';
		}

		if(in_array(ADMINISTRATOR_COMPLAINT_TYPE_DELETE, $bulkAccessArray)) {
			$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_complaint_type('.$row['complaint_type_id'].')"><i class="fa fa-trash-o"></i></button>';
		}
		
		$row_data[] = $edit_btn.' '.$delete_btn; 
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo brp_json_encode( $output );
}
else if(brp_strtolower($POST['mode']) == "add") {
	
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$tr = $dbcon -> query("SELECT `complaint_type_id`,`complaint_type_name`,`complaint_type_status`,`company_id` FROM `complaint_type_mst` WHERE complaint_type_status=0 and `complaint_type_name` ='".$POST['complaint_type_name']."' and `company_id`='".$_SESSION['company_id']."'");
	if($tr->num_rows > 0) {
		$resp['msg'] = '-1';
	}
	else {
		$info['complaint_type_name']	= $POST['complaint_type_name'];							
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$inserid=add_record('complaint_type_mst', $info, $dbcon, $branch_id);
		
		if($inserid){
			$resp['msg'] = "1";
			if(brp_strtolower($POST['complaint_type_model']) == "complaint_type_model"){
				$zone_qry="select * from complaint_type_mst where complaint_type_id=".$inserid; 
				$zone_rel=brp_mysqli_fetch_assoc($dbcon->query($zone_qry));
				$resp=$zone_rel;
				$resp['msg'] = "2"; 
			}
		}
		else{
			$resp['msg'] = "0";
		}
	}
	echo brp_json_encode($resp);
}
else if(brp_strtolower($POST['mode']) == "preedit") {			
	$q = $dbcon -> query("SELECT * FROM `complaint_type_mst` WHERE `complaint_type_id` = '$POST[complaint_type_id]'");
	$r = $q->fetch_assoc();
	echo brp_json_encode($r);
}
else if(brp_strtolower($POST['mode']) == "edit") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$tr = $dbcon -> query("SELECT `complaint_type_id`,`complaint_type_name`,`complaint_type_status`,`company_id` FROM `complaint_type_mst` WHERE complaint_type_status=0 and `complaint_type_name` ='".$POST['complaint_type_name']."' and complaint_type_id!= '".$POST['eid']."' and `company_id`='".$_SESSION['company_id']."'");
	if($tr->num_rows > 0) {
		echo '-1';
	}
	else{
	$info['complaint_type_name']	= $POST['complaint_type_name'];							
	$info['cdate']		= date("Y-m-d H:i:s");
	$info['user_id']	= $_SESSION['user_id'];
	$info['company_id']	= $_SESSION['company_id'];
	$updateid=update_record('complaint_type_mst', $info,"complaint_type_id=".$POST['eid'] , $dbcon, $branch_id);
	
	if($updateid)
		echo "1";
	else
		echo "0".$dbcon->error; 
	}
}
else if(brp_strtolower($POST['mode']) == "delete") {
	$info['complaint_type_status']='2';
	$updateid=update_record('complaint_type_mst', $info,"complaint_type_id=".$POST['complaint_type_id'] , $dbcon);
	
	if($updateid)
		echo "1";
	else
		echo "0"; 
}	
?>