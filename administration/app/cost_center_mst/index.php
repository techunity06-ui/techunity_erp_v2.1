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
	    	UPDATE_COST_CENTER_MASTER,
	        DELETE_COST_CENTER_MASTER
	    ]);
	 //    $branch_id = $POST['branch_id'];
		 $where='';
	 //    if($branch_id){
	 //        $where .= check_branch('fmst',$branch_id);
	 //    }
			
		$appData = array();
		$i=1;
		$aColumns = array('cg.cost_group_name','fmst.cost_center_id', 'fmst.cost_center_name','fmst.cdate', 'fmst.user_id');
		$sIndexColumn = "fmst.cost_center_id";
		$isWhere = array("fmst.isdelete=0 and fmst.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_cost_center as fmst";			
		$isJOIN = array("left join tbl_cost_center_group as cg on fmst.cost_group_id=cg.cost_group_id");
		$hOrder = "fmst.cost_center_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['cost_center_name'];
			$row_data[] = $row['cost_group_name'];
			$row_data[] = date('d, M y',strtotime($row['cdate']));
			
			$edit_btn='';$delete_btn='';
			if(in_array(UPDATE_COST_CENTER_MASTER,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_cost_center('.$row['cost_center_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(DELETE_COST_CENTER_MASTER,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_cost_center('.$row['cost_center_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add_cost_center_group") {
		
		$tr = $dbcon -> query("SELECT `cost_group_id`,`cost_group_name`,`company_id` FROM `tbl_cost_center_group` WHERE `isdelete`=0 and `cost_group_name` ='".$POST['cost_group_name']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			$r = brp_mysqli_fetch_assoc($tr);
			if($r['isdelete'] != 0) {
				$info['isdelete']=0;
				$updateid=update_record('tbl_cost_center_group', $info,"cost_group_id=".$r['cost_group_id'] , $dbcon);						
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
			$info['cost_group_name']	= $POST['cost_group_name'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['usertype_id']	= $_SESSION['user_type'];
			//print_r($info);exit();
			$inserid=add_record('tbl_cost_center_group', $info, $dbcon);
			if($inserid)
			echo "1".'-'.$inserid.'-'.$POST['cost_group_name'];
			else
			echo "0";
		}

	}else if(strtolower($POST['mode']) == "add") {
		//print_r($POST);exit;
		//$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `cost_center_id`,`cost_center_name`,`company_id` FROM `tbl_cost_center` WHERE `isdelete`=0 and `cost_center_name` ='".$POST['CostCenter_name']."'and `cost_group_id` ='".$POST['cost_group_id']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			$r = brp_mysqli_fetch_assoc($tr);
			if($r['isdelete'] != 0) {
				$info['isdelete']=0;
				$updateid=update_record('tbl_cost_center', $info,"cost_center_id=".$r['cost_center_id'] , $dbcon);						
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
			$info['cost_center_name']	= $POST['CostCenter_name'];	
			$info['cost_group_id']	= $POST['cost_group_id'];						
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['usertype_id']	= $_SESSION['user_type'];
			//print_r($info);exit();
			$inserid=add_record('tbl_cost_center', $info, $dbcon);
			if($inserid)
			echo "1";
			else
			echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `tbl_cost_center` WHERE `cost_center_id` = '$POST[id]' order by cost_group_id desc");
		$r = brp_mysqli_fetch_assoc($q);
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `cost_center_id`,`cost_center_name`,`company_id` FROM `tbl_cost_center` WHERE `isdelete`=0 and `cost_center_name` ='".$POST['edit_CostCenter_name']."' and `cost_center_id` != '".$POST['eid']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['cost_group_id']	= $POST['cost_group_id'];
			$info['cost_center_name']	= $POST['edit_CostCenter_name'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['usertype_id']	= $_SESSION['user_type'];
			$updateid=update_record('tbl_cost_center', $info,"cost_center_id=".$POST['eid'] , $dbcon, $branch_id);
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
		
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['isdelete']='1';
		$updateid=update_record('tbl_cost_center', $info,"cost_center_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
	
	
?>