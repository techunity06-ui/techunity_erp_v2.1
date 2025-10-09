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
	    	ADMINISTRATOR_COUNTRY_UPDATE,
	        ADMINISTRATOR_COUNTRY_DELETE
	    ]);
	    $branch_id = $POST['branch_id'];
		$where='';
	   
	   /* if($branch_id != '1000'){
	        $where .= check_branch('counmst',$branch_id);
	    }*/
	   
			
		$appData = array();
		$i=1;
		$aColumns = array('counmst.countryid', 'counmst.country_initital', 'counmst.country_name', 'counmst.country_code', 'counmst.country_status', 'counmst.userid');
		$sIndexColumn = "counmst.countryid";
		$isWhere = array("counmst.country_status = 0".$where);
		$sTable = "country_mst as counmst";			
		$isJOIN = array();
		$hOrder = "counmst.countryid desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['country_initital'];
			$row_data[] = $row['country_name'];
			$row_data[] = $row['country_code'];
			
			$edit_btn='';$delete_btn='';
			if(in_array(ADMINISTRATOR_COUNTRY_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['countryid'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(ADMINISTRATOR_COUNTRY_DELETE,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_country('.$row['countryid'].')"><i class="fa fa-trash-o"></i></button>';
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

		$tr = $dbcon -> query("SELECT `countryid`,`country_name`,`country_status` FROM `country_mst` WHERE `country_status`=0 and `country_name` = '".$POST['country_name']."'");
		if($tr->num_rows > 0) {
			$r = $tr -> fetch_assoc();
			if($r['country_status'] != 0) {
				$info['country_status']=0;
				$updateid=update_record('country_mst', $info,"countryid=".$r['countryid'] , $dbcon);						
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
			$info['country_initital']= $POST['country_initital'];							
			$info['country_name']= $POST['country_name'];							
			$info['country_code']= $POST['country_code'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['userid']		= $_SESSION['user_id'];
			$inserid=add_record('country_mst', $info, $dbcon, $branch_id);
			if($inserid)
				echo "1";
			else
				echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {		
		$q = $dbcon -> query("SELECT * FROM `country_mst` WHERE `countryid` = '$POST[id]' ");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `countryid`,`country_name`,`country_status` FROM `country_mst` WHERE `country_status`=0 and `country_name` = '".$POST['country_name']."' and `countryid` != '".$POST['eid']."'");
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['country_initital']= $POST['country_initital'];				
			$info['country_name']= $POST['country_name'];				
			$info['country_code']= $POST['country_code'];				
			$info['cdate']		= date("Y-m-d H:i:s");				
			$updateid=update_record('country_mst', $info,"countryid=".$POST['eid'] , $dbcon, $branch_id);
			
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$info['country_status']='2';
		$updateid=update_record('country_mst', $info,"countryid=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
?>		