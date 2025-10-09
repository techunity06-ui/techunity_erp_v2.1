<?php
session_start(); //start session
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once($include."common_functions.php");

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		//check permission for sales stage
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	CUSTOMER_SALES_STAGE_SLUG_UPDATE,
	        CUSTOMER_SALES_STAGE_SLUG_DELETE,
	        CUSTOMER_SALES_STAGE_SLUG_STATUS
	    ]);
			
		$appData = array();
		$i=1;
		$branch_id = $POST['branch_id'];
		$where='';
	    // if($branch_id){
	    //     $where .= check_branch('opportun',$branch_id);
	    // }
		$aColumns = array('opportun.opp_stage', 'opportun.opp_probability','opportun.opp_priority','opportun.opp_id','opportun.cdate','opportun.opp_status','opportun.user_id','opportun.company_id');
		$sIndexColumn = "opportun.opp_id";
		$isWhere = array("opportun.opp_status!=2 and opportun.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_opportunity_mst as opportun";			
		$isJOIN = array('');
		$hOrder = "opportun.opp_stage desc";
		include('../../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['opp_stage']; 
			$row_data[] = $row['opp_probability']; 
			$row_data[] = $row['opp_priority']; 
			
			$edit_btn='';$delete_btn='';$active_btn='';
			if(in_array(CUSTOMER_SALES_STAGE_SLUG_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_opp('.$row['opp_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(CUSTOMER_SALES_STAGE_SLUG_DELETE,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_opp('.$row['opp_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			if($row['opp_status']=='0')
			{
				$status="<span style='color:green;font-weight:bold'> Active</span>";
				if(in_array(CUSTOMER_SALES_STAGE_SLUG_STATUS,$bulkAccessArray)){
					$active_btn='<button class="btn btn-xs btn-success" data-original-title="Deactivate" data-toggle="tooltip" data-placement="top" onClick="deactive_opp('.$row['opp_id'].')"><i class="fa  fa-check-circle"></i></button>';
				}
			}
			else
			{
				$status="<span style='color:red;font-weight:bold'> In-Active</span>";
				if(in_array(CUSTOMER_SALES_STAGE_SLUG_STATUS,$bulkAccessArray)){
					$active_btn='<button class="btn btn-xs btn-danger" data-original-title="Activate" data-toggle="tooltip" data-placement="top" onClick="active_opp('.$row['opp_id'].')"><i class="fa fa-times-circle"></i></button>';
				}
			}
			
			$row_data[] = $status; 
			if($row['opp_stage'] != 'WON' && $row['opp_stage'] != 'LOST'){
				$row_data[] = $edit_btn.' '.$delete_btn.' '.$active_btn; 
			}else{
				$row_data[] = '';
			}
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `opp_stage`,`opp_status`,`company_id` FROM `tbl_opportunity_mst` WHERE opp_status=0 and `opp_stage` ='".$POST['opp_stage']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			$resp['msg'] = '-1';
		}
		else {
			$info['opp_stage']	= $POST['opp_stage'];							
			$info['opp_probability']= $POST['opp_probability'];							
			$info['opp_priority']	= $POST['opp_priority'];
			$info['opp_color']	= trim($POST['opp_color']);
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];

			if ($POST['whatsapp_status'] == 1) {
				$info['opp_template']	= trim($POST['opp_template']);
				$info['enable_whatsapp']	= $POST['enable_whatsapp'];
				if(!empty($_FILES['opp_file']['tmp_name']))
				{
					$file_name = $_FILES['opp_file']['name'];
					$err = $_FILES["opp_file"]["tmp_name"];
					move_uploaded_file($err,STAGE_TEMPLATE_FILE_UPING.$file_name);					
					$info['opp_file'] = $file_name;
				}
			}

			$inserid=add_record('tbl_opportunity_mst', $info, $dbcon, $branch_id);
			
			if($inserid){
				$resp['msg'] = "1";
			}
			else{
				$resp['msg'] = "0";
			}
		}
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `tbl_opportunity_mst` WHERE `opp_id` = '$POST[opp_id]'");
		$r = $q->fetch_assoc();
                //print_r($r);
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `opp_id`,`opp_stage`,`opp_status`,`company_id` FROM `tbl_opportunity_mst` WHERE `opp_stage` ='".$POST['opp_stage']."' and `opp_id` != '".$POST['eid']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			echo "-1";
		}else {
			$info['opp_stage']	= $POST['opp_stage'];							
			$info['opp_probability']= $POST['opp_probability'];							
			$info['opp_priority']	= $POST['opp_priority'];	
	                $info['opp_color']	= trim($POST['opp_color']);
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];

			if ($POST['whatsapp_status'] == 1) {
				$info['opp_template']	= trim($POST['opp_template']);			
				$info['enable_whatsapp']	= $POST['enable_whatsapp'];	
				if(!empty($_FILES['opp_file']['tmp_name']))
				{
					$file_name = $_FILES['opp_file']['name'];
					$err = $_FILES["opp_file"]["tmp_name"];
					move_uploaded_file($err,STAGE_TEMPLATE_FILE_UPING.$file_name);					
					$info['opp_file'] = $file_name;
				}
			}
			
			$updateid=update_record('tbl_opportunity_mst', $info,"opp_id=".$POST['eid'] , $dbcon, $branch_id);
			
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		} 
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['opp_status']='2';
		$updateid=update_record('tbl_opportunity_mst', $info,"opp_id=".$POST['opp_id'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}
	else if(strtolower($POST['mode']) == "de_active") {
		$info['opp_status']='1';
		$updateid=update_record('tbl_opportunity_mst', $info,"opp_id=".$POST['opp_id'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}
	else if(strtolower($POST['mode']) == "opp_active") {
		$info['opp_status']='0';
		$updateid=update_record('tbl_opportunity_mst', $info,"opp_id=".$POST['opp_id'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}
	else if(strtolower($POST['mode']) == "load_opp_stage_prob") {
		$sel_opp_qry="select * from tbl_opportunity_mst where opp_id=".$POST['opp_id'];
		$sel_opp_rel=mysqli_fetch_assoc($dbcon->query($sel_opp_qry));
		echo json_encode($sel_opp_rel);
	}
	
?>