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
	    	ADMINISTRATOR_PACKING_UPDATE,
	        ADMINISTRATOR_PACKING_DELETE
	    ]);
	    $branch_id = $POST['branch_id'];
		$where='';
	    
		$appData = array();
		$i=1;
		$aColumns = array('pck.packing_id', 'pck.packing_name','pck.size','pck.cdate', 'pck.status', 'pck.user_id','pck.company_id');
		$sIndexColumn = "pck.packing_id";
		$isWhere = array("pck.status = 0 and pck.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "packing_mst as pck";			
		$isJOIN = array();
		$hOrder = "pck.packing_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['packing_name'];
			$row_data[] = $row['size'];
			$row_data[] = date('d, M y',strtotime($row['cdate']));
			
			$edit_btn='';$delete_btn='';
			if(in_array(ADMINISTRATOR_PACKING_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_packing('.$row['packing_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(ADMINISTRATOR_PACKING_DELETE,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_packing('.$row['packing_id'].')"><i class="fa fa-trash-o"></i></button>';
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

		$tr = $dbcon -> query("SELECT `packing_id`,`packing_name`,`status`,`company_id` FROM `packing_mst` WHERE `status`=0 and `packing_name` ='".$POST['packing_name']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			$r = $tr -> fetch_assoc();
			if($r['status'] != 0) {
				$info['status']=0;
				$updateid=update_record('packing_mst', $info,"packing_id=".$r['packing_id'] , $dbcon);						
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
			$info['packing_name']	= $POST['packing_name'];
			$info['size']	= $POST['size'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$inserid=add_record('packing_mst', $info, $dbcon, $branch_id);
			if($inserid)
			echo "1";
			else
			echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `packing_mst` WHERE `packing_id` = '$POST[id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `packing_id`,`packing_name`,`status`,`company_id` FROM `packing_mst` WHERE `status`=0 and `packing_name` ='".$POST['packing_name']."' and `packing_id` != '".$POST['eid']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['packing_name']	= $POST['packing_name'];	
			$info['size']	= $POST['size'];						
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$updateid=update_record('packing_mst', $info,"packing_id=".$POST['eid'] , $dbcon, $branch_id);
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
		
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['status']='2';
		$updateid=update_record('packing_mst', $info,"packing_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
	
	
?>