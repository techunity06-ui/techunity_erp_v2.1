<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include_once("../../../include/common_functions.php");
include_once("../../../include/function_database_query.php");
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
		$other_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

		$appData = array();
		$i=1;
		$aColumns = array('branch_id', 'branch_name','branch_address','city.city_name','branch.cdate', 'branch_status', 'branch.user_id');
		$sIndexColumn = "branch_id";
		$isWhere = array("branch_status !=2 and branch.company_id=".$_SESSION['company_id']);
		$sTable = "branch_mst as branch";
		$isJOIN = array('left join city_mst city on branch.cityid=city.cityid');
		$hOrder = "branch.branch_id desc";
		include('../../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['branch_name'];
			$row_data[] = $row['branch_address'];
			$row_data[] = $row['city_name'];
			if($row['branch_status']=='0'){
				$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
			}else{
				$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
			}
			$edit_btn='';$del_btn='';$change_status=''; 
			if($row['branch_id']!='0'){ 
				if($edit_btn_per){ 
					$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_branch('.$row['branch_id'].');"><i class="fa fa-pencil"></i></button>';
				}
				if($delete_btn_per){
					$del_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_branch('.$row['branch_id'].')"><i class="fa fa-trash-o"></i></button>';
				} 
			}
			if($other_btn_per) {
				if($row['branch_status'] == '0')
				{  
					$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['branch_id']."\",\"".$row['branch_status']."\")'><i class='fa fa-window-close'></i></a>";	
				} else {
					$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['branch_id']."\",\"".$row['branch_status']."\")'><i class='fa fa-check-square-o'></i></a>";
				}
			}
			

			$row_data[] = $edit_btn.' '.$del_btn.' '.$change_status;
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		$tr = $dbcon -> query("SELECT `branch_id`,`branch_name`,`branch_status` FROM `branch_mst` WHERE `branch_name` ='".$POST['branch_name']."' and `company_id` ='".$_SESSION['company_id']."' ");
		if($tr->num_rows > 0) {
			$r = $tr -> fetch_assoc();
			if($r['branch_status'] != 0) {
				$info['branch_status']=0;
				$updateid=update_record('branch_mst', $info,"branch_id=".$r['branch_id'] , $dbcon);	
				
				if($updateid)
					$row['res'] ="1";
				else
					$row['res'] ="0";
			}
			else {
				$row['res'] ="-1";
			}
		}
		else {
				$info['branch_name']		= $POST['branch_name'];							
				$info['branch_address']		= $_POST['branch_address'];							
				$info['countryid']			= $POST['countryid'];							
				$info['stateid']			= $POST['stateid'];							
				$info['zoneid']				= $POST['zoneid'];							
				$info['cityid']				= $POST['cityid'];							
				$info['branch_pincode']		= $POST['branch_pincode'];		
				$info['branch_status']		= $POST['branch_status'];					
				$info['cdate']				= date("Y-m-d H:i:s");
				$info['user_id']			= $_SESSION['user_id'];
				$info['usertype_id']		= $_SESSION['user_type'];
				$info['company_id']			= $_SESSION['company_id'];
				$inserid=add_record('branch_mst', $info, $dbcon);
	 
			if($inserid){
				if(strtolower($POST['branch_model'])=="branch_model"){
					$query="select * from branch_mst where branch_id=".$inserid;
					$rel=mysqli_fetch_assoc($dbcon->query($query));		
					$row = $rel;
					$row['res']="2"; 
				}
				else{
					$row['res'] ="1";
				}
			}
			else{
				$row['res'] ="0";
			}
			echo json_encode($row);		
		}
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `branch_mst` WHERE `branch_id` = '$POST[id]'");
		$r = $q->fetch_assoc();
		$r['state_html'] = get_state($dbcon,'',$r['countryid']);
		$r['city_html'] = getcity($dbcon,$r['stateid'],'');
		$r['zone_html'] = get_zone($dbcon,$r['zoneid'],'');
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$info['branch_name']		= $POST['branch_name'];
		$info['branch_address']		= $_POST['branch_address'];
		$info['countryid']			= $POST['countryid'];
		$info['stateid']			= $POST['stateid'];
		$info['zoneid']				= $POST['zoneid'];
		$info['cityid']				= $POST['cityid'];
		$info['branch_pincode']		= $POST['branch_pincode'];
		$info['branch_status']		= $POST['branch_status'];
		$info['cdate']				= date("Y-m-d H:i:s");
		$info['user_id']			= $_SESSION['user_id'];
		$info['usertype_id']		= $_SESSION['user_type'];
		$info['company_id']			= $_SESSION['company_id'];
		$updateid=update_record('branch_mst', $info,"branch_id=".$POST['eid'] , $dbcon);
	 
		if($updateid)
			echo "1";
		else
			echo "0".$dbcon->error;
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['branch_status']='2';
		$updateid=update_record('branch_mst', $info,"branch_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0";
	}
	else if(strtolower($POST['mode']) == "change_status") {
		$p_status = $POST['p_status'];

		$info['branch_status'] = ($p_status=='0') ? '1' : '0';
		
		$updateid = update_record('branch_mst', $info,"branch_id=".$POST['eid'] , $dbcon);
		echo ($updateid) ? "1" : "0";
	}
   
?>