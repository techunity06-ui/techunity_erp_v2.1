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
		$aColumns = array('balt.solid_batch_calculation_mst_id', 'balt.batch_size','bsz.batch_size_name','szm.size_name');
		$sIndexColumn = "balt.solid_batch_calculation_mst_id";
		$isWhere = array("balt.status = 0".$where);
		$sTable = "solid_batch_calculation_mst as balt";			
		$isJOIN = array("left join solid_batch_size_mst as bsz on bsz.batch_size_id=balt.batch_size_id","left join solid_size_mst as szm on szm.size_id=balt.size_id");
		$hOrder = "balt.solid_batch_calculation_mst_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['batch_size_name'];
			$row_data[] = $row['size_name'];
			$row_data[] = $row['batch_size'];
			
			$edit_btn='';$delete_btn='';
			// if(in_array(ADMINISTRATOR_CURRENCY_UPDATE,$bulkAccessArray) && $row['is_deletable']=='0'){
			//if(in_array(ADMINISTRATOR_CURRENCY_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['solid_batch_calculation_mst_id'].');"><i class="fa fa-pencil"></i></button>';
			//}
			//if(in_array(ADMINISTRATOR_CURRENCY_DELETE,$bulkAccessArray) && $row['is_deletable']=='0'){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_balty('.$row['solid_batch_calculation_mst_id'].')"><i class="fa fa-trash-o"></i></button>';
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
		
		$tr = $dbcon -> query("SELECT `solid_batch_calculation_mst_id`,`batch_size`, `status` FROM `solid_batch_calculation_mst` WHERE `status`=0 and `batch_size_id` = '".$POST['batch_size_id']."' and size_id='".$POST['size_id']."'");
		if($tr->num_rows > 0) {
			$r = $tr -> fetch_assoc();
			if($r['status'] != 0) {
				$info['status']=0;
				$updateid=update_record('solid_batch_calculation_mst', $info,"solid_batch_calculation_mst_id=".$r['solid_batch_calculation_mst_id'] , $dbcon);						
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
			$info['size_id']= $POST['size_id'];
			$info['batch_size_id']= $POST['batch_size_id'];
			$info['batch_size']= $POST['batch_qty'];
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['userid']		= $_SESSION['user_id'];
			$inserid=add_record('solid_batch_calculation_mst', $info, $dbcon);
			if($inserid){
				echo "1";
			}else{
				echo "0";
			}
				
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {		
		$q = $dbcon -> query("SELECT * FROM `solid_batch_calculation_mst` WHERE `solid_batch_calculation_mst_id` = '$POST[id]' ");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		//$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `solid_batch_calculation_mst_id`,`status` FROM `solid_batch_calculation_mst` WHERE `status`=0 and `size_id` = '".$POST['size_id']."' and `batch_size_id` = '".$POST['batch_size_id']."' and `solid_batch_calculation_mst_id` != '".$POST['eid']."' ");
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['size_id']= $POST['size_id'];	
			$info['batch_size_id']= $POST['batch_size_id'];	
			$info['batch_size']= $POST['batch_qty'];	
			$info['cdate']		= date("Y-m-d H:i:s");				
			$updateid=update_record('solid_batch_calculation_mst', $info,"solid_batch_calculation_mst_id=".$POST['eid'] , $dbcon);
			
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$info['status']='2';
		$updateid=update_record('solid_batch_calculation_mst', $info,"solid_batch_calculation_mst_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
?>		