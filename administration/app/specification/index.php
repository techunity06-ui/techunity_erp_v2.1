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
	        $where .= check_branch('spec',$branch_id);
	    }

		//check paermission for annexure
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	ADMINISTRATOR_SPECIFICATION_UPDATE,
	        ADMINISTRATOR_SPECIFICATION_DELETE
	    ]);
		
		$appData = array();
		$i=1;
		$aColumns = array('spec.specification_id', 'spec.specification_name', 'spec.specification_detail','spec.specification_status','spec.user_id');
		$sIndexColumn = "spec.specification_id";
		$isWhere = array("spec.specification_status = 0 and spec.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_specification as spec";			
		$isJOIN = array();
		$hOrder = "spec.specification_id desc";
		include('../../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['specification_name'];
			//$row_data[] = $row['an_detail'];
			
			$edit_btn=''; $delete_btn='';  
			if(in_array(ADMINISTRATOR_SPECIFICATION_UPDATE,$bulkAccessArray)){
				$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.ADMINISTRATION_ROOT.'specification_detail_edit/'.$row['specification_id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if(in_array(ADMINISTRATOR_SPECIFICATION_DELETE,$bulkAccessArray)){
				$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_data('.$row['specification_id'].')"><i class="fa fa-trash-o"></i></button>'; 
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
		
		$tr = $dbcon -> query("SELECT `specification_id`,`specification_name`,`specification_status` FROM `tbl_specification` WHERE `specification_name` ='".$POST['specification_name']."' and specification_status='0'");
		if($tr->num_rows > 0) {
			echo '-1';
		}
		else {
			$info['specification_name']	= $POST['specification_name'];							
			$info['specification_priority']= $POST['specification_priority'];							
			$info['specification_detail']	= $_POST['specification_detail'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$inserid=add_record('tbl_specification', $info, $dbcon, $branch_id);
			if($inserid)
				echo "1";
			else
				echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `specification_id`,`specification_name`,`specification_status` FROM `tbl_specification` WHERE `specification_name` ='".$POST['specification_name']."' and specification_status='0' and `specification_id` != '".$POST['eid']."'");
		if($tr->num_rows > 0) {
			echo '-1';
		}else {
			$info['specification_name']	= $POST['specification_name'];							
			$info['specification_priority']= $POST['specification_priority'];							
			$info['specification_detail']	= $_POST['specification_detail'];										
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];

			$updateid=update_record('tbl_specification', $info,"specification_id=".$POST['eid'] , $dbcon, $branch_id);
			
			if($updateid)
				echo "2";
			else
				echo "0".$dbcon->error;
		}
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['specification_status']='2';										
		$info['cdate']		= date("Y-m-d H:i:s");
		$updateid=update_record('tbl_specification', $info,"specification_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0";
	}
	
?>