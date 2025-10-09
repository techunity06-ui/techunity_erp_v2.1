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
	    	ADMINISTRATOR_MAKE_NUMBER_UPDATE,
	        ADMINISTRATOR_MAKE_NUMBER_DELETE
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
		$aColumns = array('tblmak.make_number_id', 'tblmak.make_number', 'tblmak.make_number_status', 'tblmak.user_id');
		$sIndexColumn = "tblmak.make_number_id";
		$isWhere = array("tblmak.make_number_status = 0 and tblmak.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_make_number as tblmak";			
		$isJOIN = array();
		$hOrder = "tblmak.make_number_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['make_number'];
			
			$edit_btn='';$delete_btn='';
			if(in_array(ADMINISTRATOR_MAKE_NUMBER_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['make_number_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(ADMINISTRATOR_MAKE_NUMBER_DELETE,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_make('.$row['make_number_id'].')"><i class="fa fa-trash-o"></i></button>';
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

		$tr = $dbcon -> query("SELECT `make_number_id`,`make_number`, `make_number_status` FROM `tbl_make_number` WHERE `make_number_status`=0 and `make_number` = '".$POST['make_number']."' and `company_id` = '".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			$r = $tr -> fetch_assoc();
			if($r['make_number_status'] != 0) {
				$info['make_number_status']=0;
				$updateid=update_record('tbl_make_number', $info,"make_number_id=".$r['make_number_id'] , $dbcon);						
				if($updateid)
					echo "1";
				else
					echo "0";
			}
			else {
				echo '-1';
			}
		} else {
			$info['make_number']= $POST['make_number'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			$inserid=add_record('tbl_make_number', $info, $dbcon, $branch_id);
			if($inserid)
				echo "1";
			else
				echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {		
		$q = $dbcon -> query("SELECT * FROM `tbl_make_number` WHERE `make_number_id` = '".$POST['id']."' ");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `make_number_id`,`make_number`, `make_number_status` FROM `tbl_make_number` WHERE `make_number_status`=0 and `make_number` = '".$POST['make_number']."' and `make_number_id` != '".$POST['eid']."'  and `company_id` = '".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			echo "-1";
		}else{
			$info['make_number']= $POST['make_number'];			
			$info['cdate']		= date("Y-m-d H:i:s");				
			$updateid=update_record('tbl_make_number', $info,"make_number_id=".$POST['eid'] , $dbcon, $branch_id);
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$info['make_number_status']='2';
		$updateid=update_record('tbl_make_number', $info,"make_number_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
?>		