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

		$branch_id = $POST['branch_id'];
		$where='';
	    if($branch_id){
	        $where .= check_branch('annex',$branch_id);
	    }

		//check paermission for annexure
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	CUSTOMER_ANNEXURE_SLUG_UPDATE,
	        CUSTOMER_ANNEXURE_SLUG_DELETE
	    ]);
		
		$appData = array();
		$i=1;
		$aColumns = array('annex.an_id', 'annex.an_name', 'annex.an_detail','annex.an_status','annex.user_id');
		$sIndexColumn = "annex.an_id";
		$isWhere = array("annex.an_status = 0 and annex.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_annexure as annex";			
		$isJOIN = array();
		$hOrder = "annex.an_id desc";
		include('../../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['an_name'];
			//$row_data[] = $row['an_detail'];
			
			$edit_btn=''; $delete_btn='';  
			if(in_array(CUSTOMER_ANNEXURE_SLUG_UPDATE,$bulkAccessArray)){
				$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'annexure_detail_edit/'.$row['an_id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if(in_array(CUSTOMER_ANNEXURE_SLUG_DELETE,$bulkAccessArray)){
				$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_data('.$row['an_id'].')"><i class="fa fa-trash-o"></i></button>'; 
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
		
		$tr = $dbcon -> query("SELECT `an_id`,`an_name`,`an_status` FROM `tbl_annexure` WHERE `an_name` ='".$POST['a_name']."' and an_status='0'");
		if($tr->num_rows > 0) {
			echo '-1';
		}
		else {
			$info['an_name']	= $POST['a_name'];							
			$info['an_priority']= $POST['a_priority'];							
			$info['an_detail']	= $_POST['a_detail'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$inserid=add_record('tbl_annexure', $info, $dbcon, $branch_id);
			if($inserid)
				echo "1";
			else
				echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `an_id`,`an_name`,`an_status` FROM `tbl_annexure` WHERE `an_name` ='".$POST['a_name']."' and an_status='0' and `an_id` != '".$POST['eid']."'");
		if($tr->num_rows > 0) {
			echo '-1';
		}else {
			$info['an_name']	= $POST['a_name'];							
			$info['an_priority']= $POST['a_priority'];							
			$info['an_detail']	= $_POST['a_detail'];										
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];

			$updateid=update_record('tbl_annexure', $info,"an_id=".$POST['eid'] , $dbcon, $branch_id);
			
			if($updateid)
				echo "2";
			else
				echo "0".$dbcon->error;
		}
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['an_status']='2';										
		$info['cdate']		= date("Y-m-d H:i:s");
		$updateid=update_record('tbl_annexure', $info,"an_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0";
	}
	
?>