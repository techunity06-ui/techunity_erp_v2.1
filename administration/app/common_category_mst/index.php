<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

//print_r($_POST);exit;
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		//check permission for party industry add
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	UPDATE_COMMON_CATEGORY_MASTER,
	        DELETE_COMMON_CATEGORY_MASTER
	    ]);
	 //    $branch_id = $POST['branch_id'];
		 $where='';
	 //    if($branch_id){
	 //        $where .= check_branch('fmst',$branch_id);
	 //    }
			
		$appData = array();
		$i=1;
		$aColumns = array('fmst.common_category_id', 'fmst.common_category_name','fmst.cdate', 'fmst.user_id','fmst.is_deletable');
		$sIndexColumn = "fmst.common_category_id";
		$isWhere = array("isdelete=0 and fmst.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_common_category_mst as fmst";			
		$isJOIN = array();
		$hOrder = "fmst.common_category_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['common_category_name'];
			$row_data[] = date('d, M y',strtotime($row['cdate']));
			
			$edit_btn='';$delete_btn='';
			if(in_array(UPDATE_COMMON_CATEGORY_MASTER,$bulkAccessArray) && $row['is_deletable']=='0'){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_common_category('.$row['common_category_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(DELETE_COMMON_CATEGORY_MASTER,$bulkAccessArray) && $row['is_deletable']=='0'){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_common_category('.$row['common_category_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		//print_r($POST);exit;
		//$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `common_category_id`,`common_category_name`,`company_id` FROM `tbl_common_category_mst` WHERE `isdelete`=0 and `common_category_name` ='".$POST['CommonCategory_name']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			$r = brp_mysqli_fetch_assoc($tr);
			if($r['isdelete'] != 0) {
				$info['isdelete']=0;
				$updateid=update_record('tbl_common_category_mst', $info,"common_category_id=".$r['common_category_id'] , $dbcon);						
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
			$info['common_category_name']	= $POST['CommonCategory_name'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['usertype_id']	= $_SESSION['user_type'];
			//print_r($info);exit();
			$inserid=add_record('tbl_common_category_mst', $info, $dbcon);
			if($inserid)
			echo "1";
			else
			echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `tbl_common_category_mst` WHERE `common_category_id` = '$POST[id]'");
		$r = brp_mysqli_fetch_assoc($q);
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `common_category_id`,`common_category_name`,`company_id` FROM `tbl_common_category_mst` WHERE `isdelete`=0 and `common_category_name` ='".$POST['edit_CommonCategory_name']."' and `common_category_id` != '".$POST['eid']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['common_category_name']	= $POST['edit_CommonCategory_name'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['usertype_id']	= $_SESSION['user_type'];
			$updateid=update_record('tbl_common_category_mst', $info,"common_category_id=".$POST['eid'] , $dbcon, $branch_id);
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
		
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['isdelete']='1';
		$updateid=update_record('tbl_common_category_mst', $info,"common_category_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
	
	
?>