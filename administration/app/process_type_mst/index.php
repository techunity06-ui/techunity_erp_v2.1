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
	    	ADMINISTRATOR_PROCESS_TYPE_UPDATE,
	        ADMINISTRATOR_PROCESS_TYPE_DELETE
	    ]);
	    $branch_id = $POST['branch_id'];
		$where='';
	    if($branch_id != '1000'){
	        $where .= check_branch('zmst',$branch_id);
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
	     	$i=1;
		$aColumns = array('zmst.process_type_id', 'zmst.process_type_name','zmst.cdate', 'zmst.process_type_status', 'zmst.user_id');
		$sIndexColumn = "zmst.process_type_id";
		$isWhere = array("zmst.process_type_status = 0 and zmst.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "process_type_mst as zmst";			
		$isJOIN = array();
		$hOrder = "zmst.process_type_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['process_type_name']; 
			
			$edit_btn='';$delete_btn='';
			if(in_array(ADMINISTRATOR_PROCESS_TYPE_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_process_type('.$row['process_type_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(ADMINISTRATOR_PROCESS_TYPE_DELETE,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_process_type('.$row['process_type_id'].')"><i class="fa fa-trash-o"></i></button>';
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

		$tr = $dbcon -> query("SELECT `process_type_id`,`process_type_name`,`process_type_status`,`company_id` FROM `process_type_mst` WHERE process_type_status=0 and company_id = '".$_SESSION['company_id']."' and `process_type_name` ='".$POST['process_type_name']."'");
		if($tr->num_rows > 0) {
			$resp['msg'] = '-1';
		}
		else {
			$info['process_type_name']	= $POST['process_type_name'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$inserid=add_record('process_type_mst', $info, $dbcon, $branch_id);
			
			if($inserid){
				$resp['msg'] = "1";
				if(strtolower($POST['process_type_model']) == "process_type_model"){
					$process_type_qry="select * from process_type_mst where process_type_id=".$inserid; 
					$process_type_rel=mysqli_fetch_assoc($dbcon->query($process_type_qry));
					$resp=$process_type_rel;
					$resp['msg'] = "2"; 
				}
			}
			else{
				$resp['msg'] = "0";
			}
		}
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `process_type_mst` WHERE `process_type_id` = '$POST[process_type_id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `process_type_id`,`process_type_name`,`process_type_status`,`company_id` FROM `process_type_mst` WHERE process_type_status=0 and company_id = '".$POST['company_id']."' and `process_type_name` ='".$POST['process_type_name']."' and `process_type_id` != '".$POST['eid']."'");
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['process_type_name']	= $POST['process_type_name'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$updateid=update_record('process_type_mst', $info,"process_type_id=".$POST['eid'] , $dbcon, $branch_id);
			
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error; 
		}
	}
	else if(strtolower($POST['mode']) == "delete") {
		$sTable = array(TABLE_PRODUCT_MASTER=>'PROCESS MODULE');
		$aColumns = array(array('process_type'));
		$sWhere = array(array('process_status=0 and process_type = "'.$POST['process_type_id'].'"'));
		$checkLang = getCheckRelation($dbcon, $sTable, $aColumns, $sWhere);
		if(count($checkLang) > 0){
			$resp['msg'] = '-1';
			$resp['table'] = $checkLang;
		}else{
			$info['process_type_status']='2';
			$updateid=update_record('process_type_mst', $info,"process_type_id=".$POST['process_type_id'] , $dbcon);
			
			if($updateid)
				$resp['msg'] = '1';
			else
				$resp['msg'] = '0';
		}
		echo json_encode($resp);
	}
	
	
?>