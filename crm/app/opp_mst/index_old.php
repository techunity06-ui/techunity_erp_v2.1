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
		$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			
		$appData = array();
		$i=1;
		$aColumns = array('opp_id', 'opp_stage', 'opp_probability','opp_priority','cdate','opp_status','user_id','company_id');
		$sIndexColumn = "opp_id";
		$isWhere = array("opp_status!=2");
		$sTable = "tbl_opportunity_mst";			
		$isJOIN = array('');
		$hOrder = "opp_stage desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['opp_stage']; 
			$row_data[] = $row['opp_probability']; 
			$row_data[] = $row['opp_priority']; 
			
			$edit_btn='';$delete_btn='';
			if($edit_btn_per){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_opp('.$row['opp_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if($delete_btn_per){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_opp('.$row['opp_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			if($row['opp_status']=='0')
			{
				$status="<span style='color:green;font-weight:bold'> Active</span>";
				
				$active_btn='<button class="btn btn-xs btn-success" data-original-title="Deactivate" data-toggle="tooltip" data-placement="top" onClick="deactive_opp('.$row['opp_id'].')"><i class="fa  fa-check-circle"></i></button>';
			}
			else
			{
				$status="<span style='color:red;font-weight:bold'> In-Active</span>";
				
				$active_btn='<button class="btn btn-xs btn-danger" data-original-title="Activate" data-toggle="tooltip" data-placement="top" onClick="active_opp('.$row['opp_id'].')"><i class="fa fa-times-circle"></i></button>';
			}
			
			$row_data[] = $status; 
			$row_data[] = $edit_btn.' '.$delete_btn.' '.$active_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		$tr = $dbcon -> query("SELECT `opp_stage`,`opp_status` FROM `tbl_opportunity_mst` WHERE opp_status=0 and `opp_stage` ='".$POST['opp_stage']."' ");
		if($tr->num_rows > 0) {
			$resp['msg'] = '-1';
		}
		else {
			$info['opp_stage']	= $POST['opp_stage'];							
			$info['opp_probability']	= $POST['opp_probability'];							
			$info['opp_priority']	= $POST['opp_priority'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$inserid=add_record('tbl_opportunity_mst', $info, $dbcon);
			
			if($inserid){
				$resp['msg'] = "1";
				//Insert LOG
				$log_entry=common_log_entry($dbcon,"opp_add",1,"tbl_opportunity_mst",$inserid);
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
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		
		$info['opp_stage']	= $POST['opp_stage'];							
		$info['opp_probability']	= $POST['opp_probability'];							
		$info['opp_priority']	= $POST['opp_priority'];							
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$updateid=update_record('tbl_opportunity_mst', $info,"opp_id=".$POST['eid'] , $dbcon);
		
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"opp_add",2,"tbl_opportunity_mst",$POST['eid']);
		
		if($updateid)
			echo "1";
		else
			echo "0".$dbcon->error; 
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
		
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"opp_add",3,"tbl_opportunity_mst",$POST['opp_id']);
		
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