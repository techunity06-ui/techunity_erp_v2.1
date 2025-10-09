<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	ADMINISTRATOR_ZONE_UPDATE,
	        ADMINISTRATOR_ZONE_DELETE
	    ]);
			
		$appData = array();
		$i=1;
		$aColumns = array('zmst.zone_id', 'zmst.zone_name','zmst.cdate', 'zmst.zone_status', 'zmst.user_id');
		$sIndexColumn = "zmst.zone_id";
		$isWhere = array("zmst.zone_status = 0 and zmst.company_id in (0,$_SESSION[company_id])");
		$sTable = "zone_mst as zmst";			
		$isJOIN = array();
		$hOrder = "zmst.zone_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['zone_name']; 
			
			$edit_btn='';$delete_btn='';
			if(in_array(ADMINISTRATOR_ZONE_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_zone('.$row['zone_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(ADMINISTRATOR_ZONE_DELETE,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_zone('.$row['zone_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		$tr = $dbcon -> query("SELECT `zone_id`,`zone_name`,`zone_status`,`company_id` FROM `zone_mst` WHERE zone_status=0 and `zone_name` ='".$POST['zone_name']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			$resp['msg'] = '-1';
		} else {
			$info['zone_name']	= $POST['zone_name'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$inserid=add_record('zone_mst', $info, $dbcon);
			
			if($inserid){
				$resp['msg'] = "1";
				if(strtolower($POST['zone_model']) == "zone_model"){
					$zone_qry="select * from zone_mst where zone_id=".$inserid; 
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
		$q = $dbcon -> query("SELECT * FROM `zone_mst` WHERE `zone_id` = '$POST[zone_id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$tr = $dbcon -> query("SELECT `zone_id`,`zone_name`,`zone_status`,`company_id` FROM `zone_mst` WHERE zone_status=0 and `zone_name` ='".$POST['zone_name']."' and `zone_id` != '".$POST['eid']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['zone_name']	= $POST['zone_name'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$updateid=update_record('zone_mst', $info,"zone_id=".$POST['eid'] , $dbcon);
			
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		} 
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['zone_status']='2';
		$updateid=update_record('zone_mst', $info,"zone_id=".$POST['zone_id'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}
	
	
?>