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
	    	ADMINISTRATOR_COMPLAINT_MST_UPDATE,
	        ADMINISTRATOR_COMPLAINT_MST_DELETE
	    ]);
	    $branch_id = $POST['branch_id'];
		
	    $where='';
		     if($branch_id != '1000'){
	        $where .= check_branch('cmedia',$branch_id);
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
		$aColumns = array('cmedia.media_id', 'cmedia.media_name', 'cmedia.media_status', 'cmedia.user_id');
		$sIndexColumn = "cmedia.media_id";
		$isWhere = array("cmedia.media_status = 0 and cmedia.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "complaint_media_name_mst as cmedia";			
		$isJOIN = array();
		$hOrder = "cmedia.media_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['media_name'];
			
			$edit_btn='';$delete_btn='';
			if(in_array(ADMINISTRATOR_COMPLAINT_MST_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['media_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(ADMINISTRATOR_COMPLAINT_MST_DELETE,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_media('.$row['media_id'].')"><i class="fa fa-trash-o"></i></button>';
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

		$tr = $dbcon -> query("SELECT `media_id`,`media_name`, `media_status`, `company_id` FROM `complaint_media_name_mst` WHERE `media_status`=0 and `media_name` = '".$POST['media_name']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			$r = $tr -> fetch_assoc();
			if($r['media_status'] != 0) {
				$info['media_status']=0;
				$updateid=update_record('complaint_media_name_mst', $info,"media_id=".$r['media_id'] , $dbcon);						
				if($updateid)
					echo "1";
				else
					echo "0";
			}
			else {
				echo '-1';
			}
		} else {
			$info['media_name']= $POST['media_name'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			$inserid=add_record('complaint_media_name_mst', $info, $dbcon, $branch_id);
			if($inserid)
				echo "1";
			else
				echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {		
		$q = $dbcon -> query("SELECT * FROM `complaint_media_name_mst` WHERE `media_id` = '$POST[id]' ");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `media_id`,`media_name`, `media_status` FROM `complaint_media_name_mst` WHERE `media_status`=0 and `media_name` = '".$POST['media_name']."' and `media_id` != '".$POST['eid']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			echo "-1";
		}else{
			$info['media_name']= $POST['media_name'];			
			$info['cdate']		= date("Y-m-d H:i:s");				
			$updateid=update_record('complaint_media_name_mst', $info,"media_id=".$POST['eid'] , $dbcon, $branch_id);
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$info['media_status']='2';
		$updateid=update_record('complaint_media_name_mst', $info,"media_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
?>		