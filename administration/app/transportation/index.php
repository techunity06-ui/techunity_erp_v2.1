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
	    	ADMINISTRATOR_TRANSPORATATION_UPDATE,
	        ADMINISTRATOR_TRANSPORATATION_DELETE
	    ]);
	    $branch_id = $POST['branch_id'];
		
	    $where='';
		     if($branch_id != '1000'){
	        $where .= check_branch('trans',$branch_id);
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
		$aColumns = array('trans.id', 'trans.transportation_name','trans.transportation_gst_number','trans.transportation_email_id','trans.transportation_branch','trans.transportation_phone_num','trans.status');
		$sIndexColumn = "trans.id";
		$isWhere = array("trans.status = 0 and trans.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "transportation_details as trans";			
		$isJOIN = array();
		$hOrder = "trans.id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['transportation_name']; 
			$row_data[] = $row['transportation_email_id']; 
			$row_data[] = $row['transportation_branch']; 
			$row_data[] = $row['transportation_phone_num']; 
			$row_data[] = $row['transportation_gst_number'];
			
			$edit_btn='';$delete_btn='';
			if(in_array(ADMINISTRATOR_TRANSPORATATION_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_transportation('.$row['id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(ADMINISTRATOR_TRANSPORATATION_DELETE,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_transportation('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			$add_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="add_address('.$row['id'].','.$row['address_id'].');">Add Address</button>';
			
			$row_data[] = $edit_btn.' '.$delete_btn.' '.$add_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	}
	else if(strtolower($POST['mode']) == "add") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `id`,`transportation_name`,`status`,`company_id` FROM `transportation_details` WHERE status=0 and `transportation_name` ='".$POST['transportation_name']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			$resp['msg'] = '-1';
		}
		else {
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['transportation_name']	= $POST['transportation_name'];	
			$info['transportation_email_id']	= $POST['transportation_email_id'];	
			$info['transportation_branch']	= $POST['transportation_branch'];	
			$info['transportation_phone_num']	= $POST['transportation_phone_num'];	
			$info['transportation_gst_number']	= $POST['transportation_gst_number'];	
			$info['created_at']		= date("Y-m-d H:i:s");
			
			$inserid=add_record('transportation_details', $info, $dbcon, $branch_id);
			
			if($inserid){
				$resp['msg'] = "1";
				if(strtolower($POST['transportation_model']) == "transportation_model"){
					$transportation_qry="select * from transportation_details where id=".$inserid; 
					$transportation_rel=mysqli_fetch_assoc($dbcon->query($transportation_qry));
					$resp=$transportation_rel;
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
		$q = $dbcon -> query("SELECT * FROM `transportation_details` WHERE `id` = '$POST[transportation_id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `id`,`transportation_name`,`status`,`company_id` FROM `transportation_details` WHERE status=0 and `transportation_name` ='".$POST['transportation_name']."' and `id` != '".$POST['eid']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['transportation_name']	= $POST['transportation_name'];	
			$info['transportation_email_id']	= $POST['transportation_email_id'];	
			$info['transportation_branch']	= $POST['transportation_branch'];	
			$info['transportation_phone_num']	= $POST['transportation_phone_num'];	
			$info['transportation_gst_number']	= $POST['transportation_gst_number'];	
			$info['updated_at']		= date("Y-m-d H:i:s");
			$updateid=update_record('transportation_details', $info,"id=".$POST['eid'] , $dbcon, $branch_id);
			
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error; 
		}
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['status']='2';
		$updateid=update_record('transportation_details', $info,"id=".$POST['transportation_id'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}else if(strtolower($POST['mode']) == "add_address_db") {
		$info['user_id']				= $_SESSION['user_id'];
		$info['company_id']				= $_SESSION['company_id'];
		$info['transportation_address']	= $POST['address'];	
		$info['transportation_id']		= $POST['transport_id'];	
		$info['created_at']				= date("Y-m-d H:i:s");
		if(!empty($POST['address_id'])){
			echo $updateid=update_record('transportation_address', $info,"id=".$POST['address_id'] , $dbcon);die();
		}else{
			$inserid=add_record('transportation_address', $info, $dbcon);
		}
		echo "1";
	}else if(strtolower($POST['mode']) == "fetch_add") {

	  $trans_id = $POST['trans_id'];
		
	    
		$appData = array();
		$i=1;
		$aColumns = array('trans.address_id', 'trans.transportation_address','trans.status');
		$sIndexColumn = "trans.address_id";
		$isWhere = array("trans.status = 0 and trans.transportation_id=".$trans_id);
		$sTable = "transportation_address as trans";			
		$isJOIN = array();
		$hOrder = "trans.address_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['transportation_address']; 
			$edit_btn='';$delete_btn='';
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_transportation_add('.$row['address_id'].');"><i class="fa fa-pencil"></i></button>';
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_transportation_add('.$row['address_id'].')"><i class="fa fa-trash-o"></i></button>';
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	
	}
	else if(strtolower($POST['mode']) == "delete_add") {
		$info['status']='2';
		$updateid=update_record('transportation_address', $info,"address_id=".$POST['transportation_id'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}else if(strtolower($POST['mode']) == "preedit_add") {	
				
		$q = $dbcon -> query("SELECT * FROM `transportation_address` WHERE `address_id` =".$POST['transportation_id']);
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		

		$tr = $dbcon -> query("SELECT `address_id`,`transportation_address`,`status`, FROM `transportation_address` WHERE status=0 and `transportation_address` ='".$POST['transportation_address']."' and `id` != '".$POST['eid']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['transportation_address']	= $POST['transportation_address'];	
			// $info['transportation_email_id']	= $POST['transportation_email_id'];	
			// $info['transportation_branch']	= $POST['transportation_branch'];	
			// $info['transportation_phone_num']	= $POST['transportation_phone_num'];	
			// $info['transportation_gst_number']	= $POST['transportation_gst_number'];	
			$info['updated_at']		= date("Y-m-d H:i:s");
			$updateid=update_record('transportation_address', $info,"address=".$POST['eid'] , $dbcon, $branch_id);
			
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error; 
		}
	}
	
	
?>