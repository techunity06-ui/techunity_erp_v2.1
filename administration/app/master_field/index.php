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
	    	ADMINISTRATOR_MASTER_FIELD_UPDATE,
	        ADMINISTRATOR_MASTER_FIELD_DELETE
	    ]);
	    $branch_id = $POST['branch_id'];
		/*$where='';
	    if($branch_id){
	        $where .= check_branch('tblmak',$branch_id);
	    }*/
	    $where='';
		     if($branch_id != '1000'){
	        $where .= check_branch('tblmak',$branch_id);
	    }
	    if($branch_id == ""){
	    	 $output = array(
		        "sEcho" => 1,
		        "iTotalRecords" => 0,
		        "iTotalDisplayRecords" => 0,
		        "aaData" => array()
		    );
	     	
	     	echo json_encode( $output );
	     }else{
			
		$appData = array();
		$i=1;
		$aColumns = array('tblmak.master_field_id', 'tblmak.master_field','tblmak.master_field_db_name', 'tblmak.master_field_status','tblmak.priority', 'tblmak.user_id');
		$sIndexColumn = "tblmak.master_field_id";
		$isWhere = array("tblmak.master_field_status = 0 and tblmak.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_master_field as tblmak";			
		$isJOIN = array();
		$hOrder = "tblmak.master_field_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['master_field'];
			$row_data[] = $row['master_field_db_name'];
			$row_data[] = $row['priority'];
			
			$edit_btn='';$delete_btn='';
			if(in_array(ADMINISTRATOR_MASTER_FIELD_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['master_field_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(ADMINISTRATOR_MASTER_FIELD_DELETE,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_master_field('.$row['master_field_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn;
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	}
	else if(strtolower($POST['mode']) == "add") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		 $tr = $dbcon -> query("SELECT `master_field_id`,`master_field`,`master_field_db_name`, `master_field_status` FROM `tbl_master_field` WHERE `master_field_status`=0 and `master_field` = '".$POST['master_field']."' and `company_id` = '".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			$r = $tr -> fetch_assoc();
			if($r['master_field_status'] != 0) {
				$info['master_field_status']=0;
				$updateid=update_record('tbl_master_field', $info,"master_field_id=".$r['master_field_id'] , $dbcon);						
				if($updateid)
					echo "1";
				else
					echo "0";
			}
			else {
				echo '-1';
			}
		} else {
			$info['master_field']= $POST['master_field'];	
			$info['master_field_db_name']= strtolower($POST['master_field_db_name']);			
			$info['priority']	= $POST['priority'];				
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			$inserid=add_record('tbl_master_field', $info, $dbcon, $branch_id);
			if($inserid)
				echo "1";
			else
				echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {		
		$q = $dbcon -> query("SELECT * FROM `tbl_master_field` WHERE `master_field_id` = '".$POST['id']."' ");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `master_field_id`,`master_field`,`master_field_db_name`, `master_field_status` FROM `tbl_master_field` WHERE `master_field_status`=0 and `master_field` = '".$POST['master_field']."' and `master_field_id` != '".$POST['eid']."'  and `company_id` = '".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			echo "-1";
		}else{
			$info['master_field']= $POST['master_field'];		
			$info['master_field_db_name']= strtolower($POST['master_field_db_name']);			
			$info['priority']	= $POST['priority'];			
			$info['cdate']		= date("Y-m-d H:i:s");				
			$updateid=update_record('tbl_master_field', $info,"master_field_id=".$POST['eid'] , $dbcon, $branch_id);
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$info['master_field_status']='2';
		$updateid=update_record('tbl_master_field', $info,"master_field_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
?>		