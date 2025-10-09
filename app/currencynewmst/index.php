<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		//check permission for party industry add
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	ADMINISTRATOR_NEW_CURRENCY_UPDATE,
	        ADMINISTRATOR_NEW_CURRENCY_DELETE
	    ]);
	    //$branch_id = $POST['branch_id'];
		$where='';
	    	
		$appData = array();
		$i=1;
		$aColumns = array('currnewmst.currency_id', 'currnewmst.currency_name','currnewmst.currency_code','currnewmst.currency_symbol','currnewmst.currency_in_word','currnewmst.currency_in_word_end','currnewmst.currency_rate','currnewmst.is_deletable');
		$sIndexColumn = "currnewmst.currency_id";
		$isWhere = array("currnewmst.currency_status=0 || currnewmst.currency_status='' ''".$where);
		$sTable = "tbl_currency as currnewmst";			
		$isJOIN = array();
		$hOrder = "currnewmst.currency_name ASC";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['currency_name'];
			$row_data[] = $row['currency_code'];
			$row_data[] = $row['currency_symbol'];
			$row_data[] = $row['currency_rate'];
			$row_data[] = $row['currency_in_word'];
			$row_data[] = $row['currency_in_word_end'];
			
			$edit_btn='';$delete_btn='';
			// if(in_array(ADMINISTRATOR_NEW_CURRENCY_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['currency_id'].');"><i class="fa fa-pencil"></i></button>';
			// }
			// if(in_array(ADMINISTRATOR_NEW_CURRENCY_DELETE,$bulkAccessArray) && $row['is_deletable']=='0'){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_currency('.$row['currency_id'].')"><i class="fa fa-trash-o"></i></button>';
			// }
			
			$row_data[] = $edit_btn.' '.$delete_btn;
			
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		/*$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		*/
		$tr = $dbcon -> query("SELECT * FROM `tbl_currency` WHERE `currency_name` = '".$POST['currency_name']."' and `currency_id` != '".$POST['eid']."' ");
		if($tr->num_rows > 0) {
			$r = $tr -> fetch_assoc();
			if($r['currency_status'] != 0) {
				$info['currency_status']=0;
				$updateid=update_record('tbl_currency', $info,"currency_id=".$r['currency_id'] , $dbcon);						
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
			$info['currency_name']= $_POST['currency_name'];
			$info['currency_code']= $_POST['currency_code'];	
			$info['currency_symbol']= $_POST['currency_symbol'];	
			$info['currency_in_word']= $_POST['currency_in_word'];	
			$info['currency_in_word_end']= $_POST['currency_in_word_end'];	
			$info['currency_rate']= $_POST['currency_rate'];	

			$inserid=add_record('tbl_currency', $info, $dbcon);
			if($inserid)
				echo "1";
			else
				echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {		
		$q = $dbcon -> query("SELECT * FROM `tbl_currency` WHERE `currency_id` = '$POST[id]' ");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		/*$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		*/
		$tr = $dbcon -> query("SELECT * FROM `tbl_currency` WHERE `currency_name` = '".$POST['currency_name']."' and `currency_id` != '".$POST['eid']."' ");
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['currency_name']= $_POST['currency_name'];	
			$info['currency_code']= $_POST['currency_code'];
			$info['currency_symbol'] = $_POST['currency_symbol'];
			$info['currency_in_word']= $_POST['currency_in_word'];		
			$info['currency_in_word_end']= $_POST['currency_in_word_end'];
			$info['currency_rate']= $_POST['currency_rate'];
							
			$updateid=update_record('tbl_currency', $info,"currency_id=".$POST['eid'] , $dbcon);
			
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$info['currency_status']='2';
		$updateid=update_record('tbl_currency', $info,"currency_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
?>		