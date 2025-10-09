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
	    	UPDATE_COMMON_MASTER,
	        DELETE_COMMON_MASTER
	    ]);
	    
	    $category_id = $POST['category_id'];
			$where='';
		     if($category_id != ''){
	        $where .= " AND cat.common_category_id=".$category_id;
	    }
		$appData = array();
		$i=1;
		$aColumns = array('fmst.common_mst_id','fmst.common_mst_name','cat.common_category_name','fmst.cdate', 'fmst.user_id','fmst.common_category_id','fmst.is_deletable');
		$sIndexColumn = "fmst.common_mst_id";
		$isWhere = array("fmst.isdelete=0 and fmst.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_common_mst as fmst";			
		$isJOIN = array("left join tbl_common_category_mst as cat on fmst.common_category_id=cat.common_category_id");
		$hOrder = "fmst.common_mst_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['common_mst_name'];
			$row_data[] = $row['common_category_name'];
			$row_data[] = date('d, M y',strtotime($row['cdate']));
			
			$edit_btn='';$delete_btn='';
			if(in_array(UPDATE_COMMON_MASTER,$bulkAccessArray) && $row['is_deletable']=='0'){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_common('.$row['common_mst_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(DELETE_COMMON_MASTER,$bulkAccessArray) && $row['is_deletable']=='0'){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_common('.$row['common_mst_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add_common_category") {
		$tr = $dbcon -> query("SELECT `common_category_id`,`common_category_name`,`company_id` FROM `tbl_common_category_mst` WHERE `isdelete`=0 and `common_category_name` ='".$POST['common_category_name']."' and `company_id`='".$_SESSION['company_id']."'");
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
			$info['common_category_name']	= $POST['common_category_name'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['usertype_id']	= $_SESSION['user_type'];
			//print_r($info);exit();
			$inserid=add_record('tbl_common_category_mst', $info, $dbcon);
			if($inserid)
				//echo json_encode(  );
			echo "1".'-'.$inserid.'-'.$POST['common_category_name'];
			else
			echo "0";
		}
	}
	else if(strtolower($POST['mode']) == "add") {
		//print_r($POST);exit;
		//$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `common_mst_id`,`common_mst_name`,`company_id` FROM `tbl_common_mst` WHERE `isdelete`=0 and `common_mst_name` ='".$POST['Common_name']."' and `common_category_id` ='".$POST['common_category_id']."' and `company_id`='".$_SESSION['company_id']."'");

		if($tr->num_rows > 0) {
			$r = brp_mysqli_fetch_assoc($tr);
			if($r['isdelete'] != 0) {
				$info['isdelete']=0;
				$updateid=update_record('tbl_common_mst', $info,"common_mst_id=".$r['common_mst_id'] , $dbcon);						
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
			$info['common_mst_name']	= $POST['Common_name'];		
			$info['common_category_id']	= $POST['common_category_id'];
			$info['common_mst_desc']	= $POST['common_mst_desc'];					
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['usertype_id']	= $_SESSION['user_type'];
			//print_r($info);exit();
			$inserid=add_record('tbl_common_mst', $info, $dbcon);
			if($inserid)
			echo "1";
			else
			echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `tbl_common_mst` WHERE `common_mst_id` = '$POST[id]'");
		$r = brp_mysqli_fetch_assoc($q);
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `common_mst_id`,`common_mst_name`,`company_id` FROM `tbl_common_mst` WHERE `isdelete`=0 and `common_mst_name` ='".$POST['edit_Common_name']."' and `common_mst_id` != '".$POST['eid']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['common_category_id']	= $POST['common_category_id'];
			$info['common_mst_name']	= $POST['edit_Common_name'];	
			$info['common_mst_desc']	= $POST['common_mst_desc'];						
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['usertype_id']	= $_SESSION['user_type'];
			$updateid=update_record('tbl_common_mst', $info,"common_mst_id=".$POST['eid'] , $dbcon, $branch_id);
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
		
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['isdelete']='1';
		$updateid=update_record('tbl_common_mst', $info,"common_mst_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
	
	
?>