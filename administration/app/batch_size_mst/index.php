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
	    // $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    // 	ADMINISTRATOR_CURRENCY_UPDATE,
	    //     ADMINISTRATOR_CURRENCY_DELETE
	    // ]);
	    $branch_id = $POST['branch_id'];
		$where='';
	    if($branch_id){
	       // $where .= check_branch('currmst',$branch_id);
	    }
			
		$appData = array();
		$i=1;
		$aColumns = array('balt.batch_size_id', 'balt.batch_size_name');
		$sIndexColumn = "balt.batch_size_id";
		$isWhere = array("balt.batch_size_status = 0".$where);
		$sTable = "solid_batch_size_mst as balt";			
		$isJOIN = array();
		$hOrder = "balt.batch_size_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['batch_size_name'];
			
			$edit_btn='';$delete_btn='';
			// if(in_array(ADMINISTRATOR_CURRENCY_UPDATE,$bulkAccessArray) && $row['is_deletable']=='0'){
			//if(in_array(ADMINISTRATOR_CURRENCY_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['batch_size_id'].');"><i class="fa fa-pencil"></i></button>';
			//}
			//if(in_array(ADMINISTRATOR_CURRENCY_DELETE,$bulkAccessArray) && $row['is_deletable']=='0'){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_batch_size('.$row['batch_size_id'].')"><i class="fa fa-trash-o"></i></button>';
			//}
			
			$row_data[] = $edit_btn.' '.$delete_btn;
			
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		//$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		
		$tr = $dbcon -> query("SELECT `batch_size_id`,`batch_size_name`, `batch_size_status` FROM `solid_batch_size_mst` WHERE `batch_size_status`=0 and `batch_size_name` = '".$POST['batch_size_name']."'");
		if($tr->num_rows > 0) {
			$r = $tr -> fetch_assoc();
			if($r['batch_size_status'] != 0) {
				$info['batch_size_status']=0;
				$updateid=update_record('solid_batch_size_mst', $info,"batch_size_id=".$r['batch_size_id'] , $dbcon);						
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
			$info['batch_size_name']= $POST['batch_size_name'];
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['userid']		= $_SESSION['user_id'];
			$inserid=add_record('solid_batch_size_mst', $info, $dbcon);
			if($inserid)
				echo "1";
			else
				echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {		
		$q = $dbcon -> query("SELECT * FROM `solid_batch_size_mst` WHERE `batch_size_id` = '$POST[id]' ");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		//$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `batch_size_id`,`batch_size_name`, `batch_size_status` FROM `solid_batch_size_mst` WHERE `batch_size_status`=0 and `batch_size_name` = '".$POST['batch_size_name']."' and `batch_size_id` != '".$POST['eid']."' ");
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['batch_size_name']= $POST['batch_size_name'];	
			$info['cdate']		= date("Y-m-d H:i:s");				
			$updateid=update_record('solid_batch_size_mst', $info,"batch_size_id=".$POST['eid'] , $dbcon);
			
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$info['batch_size_status']='2';
		$updateid=update_record('solid_batch_size_mst', $info,"batch_size_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
?>		