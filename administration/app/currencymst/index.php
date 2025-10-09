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
	    	ADMINISTRATOR_CURRENCY_UPDATE,
	        ADMINISTRATOR_CURRENCY_DELETE
	    ]);
	    $branch_id = $POST['branch_id'];
		$where='';
	    if($branch_id){
	        $where .= check_branch('currmst',$branch_id);
	    }
			
		$appData = array();
		$i=1;
		$aColumns = array('currmst.currencyid', 'currmst.currency_name','currmst.currency_rate', 'currmst.currency_status', 'currmst.userid','currmst.is_deletable');
		$sIndexColumn = "currmst.currencyid";
		$isWhere = array("currmst.currency_status = 0".$where);
		$sTable = "currency_mst as currmst";			
		$isJOIN = array();
		$hOrder = "currmst.currencyid desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['currency_name'];
			$row_data[] = $row['currency_rate'];
			
			$edit_btn='';$delete_btn='';
			// if(in_array(ADMINISTRATOR_CURRENCY_UPDATE,$bulkAccessArray) && $row['is_deletable']=='0'){
			if(in_array(ADMINISTRATOR_CURRENCY_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['currencyid'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(ADMINISTRATOR_CURRENCY_DELETE,$bulkAccessArray) && $row['is_deletable']=='0'){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_currency('.$row['currencyid'].')"><i class="fa fa-trash-o"></i></button>';
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
		
		$tr = $dbcon -> query("SELECT `currencyid`,`currency_name`, `currency_rate`, `currency_status` FROM `currency_mst` WHERE `currency_status`=0 and `currency_name` = '".$POST['currency_name']."'");
		if($tr->num_rows > 0) {
			$r = $tr -> fetch_assoc();
			if($r['currency_status'] != 0) {
				$info['currency_status']=0;
				$updateid=update_record('currency_mst', $info,"currencyid=".$r['currencyid'] , $dbcon);						
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
			$info['currency_name']= $POST['currency_name'];
			$info['currency_rate']= $POST['currency_rate'];
			$info['currency_symbl'] = $_POST['currency_symbl'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['userid']		= $_SESSION['user_id'];
			$inserid=add_record('currency_mst', $info, $dbcon, $branch_id);
			if($inserid)
				echo "1";
			else
				echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {		
		$q = $dbcon -> query("SELECT * FROM `currency_mst` WHERE `currencyid` = '$POST[id]' ");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `currencyid`,`currency_name`, `currency_rate`, `currency_status` FROM `currency_mst` WHERE `currency_status`=0 and `currency_name` = '".$POST['currency_name']."' and `currencyid` != '".$POST['eid']."' ");
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['currency_name']= $POST['currency_name'];	
			$info['currency_rate']= $POST['currency_rate'];			
			$info['cdate']		= date("Y-m-d H:i:s");				
			$updateid=update_record('currency_mst', $info,"currencyid=".$POST['eid'] , $dbcon, $branch_id);
			
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$info['currency_status']='2';
		$updateid=update_record('currency_mst', $info,"currencyid=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
?>		