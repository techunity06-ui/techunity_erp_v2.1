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
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	ADMINISTRATOR_MATERIAL_PARAMETER_UPDATE,
	        ADMINISTRATOR_MATERIAL_PARAMETER_DELETE
	    ]);
	    $branch_id = $POST['branch_id'];
		$where='';
	    if($branch_id){
	        $where .= check_branch('tblmak',$branch_id);
	    }
			
		$appData = array();
		$i=1;
		$aColumns = array('tblmak.material_parameter_id', 'tblmak.material_parameter_name', 'tblmak.material_parameter_status', 'tblmak.user_id');
		$sIndexColumn = "tblmak.material_parameter_id";
		$isWhere = array("tblmak.material_parameter_status = 0 and tblmak.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_material_parameter as tblmak";			
		$isJOIN = array();
		$hOrder = "tblmak.material_parameter_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['material_parameter_name'];
			
			$edit_btn='';$delete_btn='';
			if(in_array(ADMINISTRATOR_MATERIAL_PARAMETER_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['material_parameter_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(ADMINISTRATOR_MATERIAL_PARAMETER_DELETE,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_make('.$row['material_parameter_id'].')"><i class="fa fa-trash-o"></i></button>';
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

		$tr = $dbcon -> query("SELECT * FROM `tbl_material_parameter` WHERE `material_parameter_status`=0 and `material_parameter_name` = '".$POST['material_parameter_name']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			$r = $tr -> fetch_assoc();
			if($r['material_parameter_status'] != 0) {
				$info['material_parameter_status']=0;
				$updateid=update_record('tbl_material_parameter', $info,"material_parameter_id=".$r['material_parameter_id'] , $dbcon);						
				if($updateid)
					echo "1";
				else
					echo "0";
			}
			else {
				echo '-1';
			}
		} else {
			$info['material_parameter_name']= $POST['material_parameter_name'];	
			$info['material_parameter_code']= strtoupper(str_replace(' ', '_', $POST['material_parameter_name']));	
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			$inserid=add_record('tbl_material_parameter', $info, $dbcon, $branch_id);
			if($inserid)
				echo "1";
			else
				echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {		
		$q = $dbcon -> query("SELECT * FROM `tbl_material_parameter` WHERE `material_parameter_id` = '$POST[id]' ");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT * FROM `tbl_material_parameter` WHERE `material_parameter_status`=0 and `material_parameter_name` = '".$POST['material_parameter_name']."' and `material_parameter_id` != '".$POST['eid']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			echo "-1";
		}else{
			$info['material_parameter_name']= $POST['material_parameter_name'];
			$info['material_parameter_code']= strtoupper(str_replace(' ', '_', $POST['material_parameter_name']));				
			$info['cdate']		= date("Y-m-d H:i:s");				
			$updateid=update_record('tbl_material_parameter', $info,"material_parameter_id=".$POST['eid'] , $dbcon, $branch_id);
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$info['material_parameter_status']='2';
		$updateid=update_record('tbl_material_parameter', $info,"material_parameter_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
?>		