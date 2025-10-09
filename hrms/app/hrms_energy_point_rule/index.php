<?php

session_start();
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../../include/function_database_query.php");
include_once("../../../include/common_functions.php");
include_once("../../../include/hrms_common_functions.php");
	if($_POST != NULL) {
		$POST = bulk_filter($dbcon,$_POST);
	} else {
		$POST = bulk_filter($dbcon,$_GET);
	}
	if(strtolower($POST['mode']) == "fetch") {
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			$where='';
			$where.="hrmsenergypointrule.status IN (0,1) and hrmsenergypointrule.company_id = $companyID".check_user('hrmsenergypointrule');
			$appData = array();
			$i=1;
			$aColumns = array('hrmsenergypointrule.id','hrmsenergypointrule.energy_rule_name','hrmsenergypointrule.is_enabled_flag','comp.company_name','hrmsenergypointrule.status');
			$sIndexColumn = "hrmsenergypointrule.id";
			$isWhere = array($where);
			$sTable = "hrms_energy_point_rule as hrmsenergypointrule";			
			$isJOIN = array("left join tbl_company as comp on comp.company_id = hrmsenergypointrule.company_id");
			$hOrder = "hrmsenergypointrule.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['company_name'];
				$row_data[] = $row['energy_rule_name'];
				if($row['is_enabled_flag']=='Yes'){
					$row_data[] = 'Yes';
				}else{
					$row_data[] = 'No';
				}
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per){
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'hrms_energy_point_rule_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per){
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_hrms_energy_point_rule('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
					}
				}
			    if($other_btn_per) {
					if($row['status'] == '0'){  
						$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-window-close'></i></a>";	
					} else {
						$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-check-square-o'></i></a>";
					}
				}
				
				$row_data[] = $edit_btn.' '.$delete_btn.' '.$change_status;
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			// Insert New Hrms Energy Point Rule List
			$info['user_id']= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['is_enabled_flag']	= ($_POST['is_enabled_flag'])?$_POST['is_enabled_flag']:'No';
			$info['energy_rule_name'] = $_POST['energy_rule_name'];
			$info['reference_document_type_id'] = $_POST['reference_document_type_id'];
			$info['for_document_event_id'] = $_POST['for_document_event_id'];
			$info['energy_points'] = $_POST['energy_points'];
			$info['allot_points_to_assigned_users_flag']	= ($_POST['allot_points_to_assigned_users_flag'])?$_POST['allot_points_to_assigned_users_flag']:'No';
			$info['energy_user_field_id']	= $_POST['energy_user_field_id'];
			$info['energy_multiplier_field_id']	= $_POST['energy_multiplier_field_id'];
			$info['energy_maximum_points']	= $_POST['energy_maximum_points'];
			$info['energy_apply_only_once_flag']	= ($_POST['energy_apply_only_once_flag'])?$_POST['energy_apply_only_once_flag']:'No';
			$info['energy_condition']	= ($_POST['energy_condition'])?$_POST['energy_condition']:'';
			$info['status']	= $POST['status'];

			$insertenergypointid = add_record('hrms_energy_point_rule', $info, $dbcon);

			if($insertenergypointid) {
				$arr['msg']="1";							
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	

		}else if(strtolower($POST['mode']) == "edit") {
			$info['user_id']= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['is_enabled_flag']	= ($_POST['is_enabled_flag'])?$_POST['is_enabled_flag']:'No';
			$info['energy_rule_name'] = $_POST['energy_rule_name'];
			$info['reference_document_type_id'] = $_POST['reference_document_type_id'];
			$info['for_document_event_id'] = $_POST['for_document_event_id'];
			$info['energy_points'] = $_POST['energy_points'];
			$info['allot_points_to_assigned_users_flag']	= ($_POST['allot_points_to_assigned_users_flag'])?$_POST['allot_points_to_assigned_users_flag']:'No';
			$info['energy_user_field_id']	= $_POST['energy_user_field_id'];
			$info['energy_multiplier_field_id']	= $_POST['energy_multiplier_field_id'];
			$info['energy_maximum_points']	= $_POST['energy_maximum_points'];
			$info['energy_apply_only_once_flag']	= ($_POST['energy_apply_only_once_flag'])?$_POST['energy_apply_only_once_flag']:'No';
			$info['energy_condition']	= ($_POST['energy_condition'])?$_POST['energy_condition']:'';
			$info['status']	= $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			$updateenergypointid = update_record('hrms_energy_point_rule', $info,"id=".$POST['eid'] , $dbcon);
		
			if($updateenergypointid){
				$arr['msg']="1";
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode'])== "delete") {
			$row=array();
			$info['status'] = 2;
			$updateid=update_record('hrms_energy_point_rule', $info, "id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['msg']="1";
			else
				$row['msg']="0";
			echo json_encode($row);
		}	
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];
			$info['status'] = ($p_status=='0') ? '1' : '0';
			$updateid = update_record('hrms_energy_point_rule', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}
		else if(strtolower($POST['mode']) == "load_user_fields") {
			$referenceID = $POST['id'];	
			$selectedID = $_POST['val1'];	
			$qry="select * from hrms_energy_user_field where status = 0 and reference_document_type_id=".$referenceID;
			$rs_state=$dbcon->query($qry);		
			$str='';
			$str.= '<option value="">SELECT USER FIELD</option>';
			while($row=mysqli_fetch_assoc($rs_state))
			{	
				$sel='';
				if($row['id']==$selectedID)
				{ 
					$sel='selected="selected"';
				}
				$str.='<option '.$sel.' value="'.$row['id'].'">'.$row['energy_user_field_name'].'</option>';
			}
			echo $str;
			die;
		}
		else if(strtolower($POST['mode']) == "load_multiplier_fields") {
			$referenceID = $POST['id'];	
			$selectedID1 = $_POST['val2'];	
			$qry="select * from hrms_multiplier_field where status = 0 and reference_document_type_id=".$referenceID;
			$rs_state=$dbcon->query($qry);		
			$str='';
			$str.= '<option value="">SELECT MULTIPLIER FIELD</option>';
			while($row=mysqli_fetch_assoc($rs_state))
			{	
				$sel='';
				if($row['id']==$selectedID1)
				{ 
					$sel='selected="selected"';
				}
				$str.='<option '.$sel.' value="'.$row['id'].'">'.$row['multiplier_field_name'].'</option>';
			}
			echo $str;
			die;
		}
?>