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
		//check permission for party industry add
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	ADMINISTRATOR_UNIT_UPDATE,
	        ADMINISTRATOR_UNIT_DELETE
	    ]);
	    $branch_id = $POST['branch_id'];
		$where='';
	    
		$appData = array();
		$i=1;
		$aColumns = array('fmst.unitid', 'fmst.unit_name','fmst.unit_code','fmst.cdate', 'fmst.unit_status', 'fmst.user_id');
		$sIndexColumn = "fmst.unitid";
		$isWhere = array("fmst.unit_status = 0 and fmst.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "unit_mst as fmst";			
		$isJOIN = array();
		$hOrder = "fmst.unitid desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['unit_name'];
			$row_data[] = $row['unit_code'];
			$row_data[] = date('d, M y',strtotime($row['cdate']));
			
			$edit_btn='';$delete_btn='';
			if(in_array(ADMINISTRATOR_UNIT_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_unit('.$row['unitid'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(ADMINISTRATOR_UNIT_DELETE,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_unit('.$row['unitid'].')"><i class="fa fa-trash-o"></i></button>';
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

		$tr = $dbcon -> query("SELECT `unitid`,`unit_name`,`unit_status`,`company_id` FROM `unit_mst` WHERE `unit_status`=0 and `unit_name` ='".$POST['unit_name']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			$r = $tr -> fetch_assoc();
			if($r['unit_status'] != 0) {
				$info['unit_status']=0;
				$updateid=update_record('unit_mst', $info,"unitid=".$r['unitid'] , $dbcon);						
				if($updateid)
				echo "1";
				else
				echo "0";
			}
			else {
				echo '-1';
			}
		}
		else {
			$info['unit_name']	= $POST['unit_name'];
			$info['unit_code']	= $POST['unit_code'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['usertype_id']	= $_SESSION['user_type'];
			$inserid=add_record('unit_mst', $info, $dbcon, $branch_id);
			if($inserid)
			echo "1";
			else
			echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `unit_mst` WHERE `unitid` = '$POST[id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `unitid`,`unit_name`,`unit_status`,`company_id` FROM `unit_mst` WHERE `unit_status`=0 and `unit_name` ='".$POST['unit_name']."' and `unitid` != '".$POST['eid']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['unit_name']	= $POST['unit_name'];	
			$info['unit_code']	= $POST['unit_code'];						
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['usertype_id']	= $_SESSION['user_type'];
			$updateid=update_record('unit_mst', $info,"unitid=".$POST['eid'] , $dbcon, $branch_id);
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
		
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['unit_status']='2';
		$updateid=update_record('unit_mst', $info,"unitid=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
	
	
?>