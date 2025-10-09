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
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			$branch=$_SESSION['branch_id'];
			$where='';
			$where.="status IN (0,1) and hrmsleave.company_id = $companyID".check_user('hrmsleave');
			$appData = array();
			$i=1;
			$aColumns = array('id','leave_type_name', 'max_leave_allowed', 'application_after_working', 'max_conti_days', 'status', 'comp.company_name');
			$sIndexColumn = "id";
			$isWhere = array($where);
			$sTable = "hrms_leave_type as hrmsleave";			
			$isJOIN = array("left join tbl_company as comp on comp.company_id = hrmsleave.company_id");
			$hOrder = "hrmsleave.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);
			
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['id'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['leave_type_name'];
				$row_data[] = $row['max_leave_allowed'];
				$row_data[] = $row['application_after_working'];
				$row_data[] = $row['max_conti_days'];
				
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per) {
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'leave_type_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per) {
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_leave_type('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
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

			// Insert New Leave Type List
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['leave_type_name'] = $POST['leave_type_name'];
			$info['max_leave_allowed']	= $POST['max_leave_allowed'];
			$info['application_after_working']	= $POST['application_after_working'];
			$info['max_conti_days']	= $POST['max_conti_days'];
			$info['is_carry_forward_flag']	= ($POST['is_carry_forward_flag'])?$POST['is_carry_forward_flag']:'No';
			$info['is_lwp_flag']	= ($POST['is_lwp_flag'])?$POST['is_lwp_flag']:'No';
			$info['is_optional_leave_flag']	= ($POST['is_optional_leave_flag'])?$POST['is_optional_leave_flag']:'No';
			$info['allow_negative_flag']	= ($POST['allow_negative_flag'])?$POST['allow_negative_flag']:'No';
			$info['include_holiday_flag']	= ($POST['include_holiday_flag'])?$POST['include_holiday_flag']:'No';
			$info['is_compensatory_flag']	= ($POST['is_compensatory_flag'])?$POST['is_compensatory_flag']:'No';
			$info['max_carry_forward_leave']	= ($POST['max_carry_forward_leave'])?$POST['max_carry_forward_leave']:'';
			$info['expiry_carry_forward_leave']	= ($POST['expiry_carry_forward_leave'])?$POST['expiry_carry_forward_leave']:'';
			$info['encashment_allowed_flag']	= ($POST['encashment_allowed_flag'])?$POST['encashment_allowed_flag']:'No';
			$info['earned_leave_flag']	= ($POST['earned_leave_flag'])?$POST['earned_leave_flag']:'No';
			$info['status']	= $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			
			$inserleaveid = add_record('hrms_leave_type', $info, $dbcon);
			
			if($inserleaveid){	
				$arr['msg']="1";							
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	

		}else if(strtolower($POST['mode']) == "edit") {
				$info['leave_type_name'] = $POST['leave_type_name'];
				$info['max_leave_allowed']	= $POST['max_leave_allowed'];
				$info['application_after_working']	= $POST['application_after_working'];
				$info['max_conti_days']	= $POST['max_conti_days'];
				$info['is_carry_forward_flag']	= ($POST['is_carry_forward_flag'])?$POST['is_carry_forward_flag']:'No';
				$info['is_lwp_flag']	= ($POST['is_lwp_flag'])?$POST['is_lwp_flag']:'No';
				$info['is_optional_leave_flag']	= ($POST['is_optional_leave_flag'])?$POST['is_optional_leave_flag']:'No';
				$info['allow_negative_flag']	= ($POST['allow_negative_flag'])?$POST['allow_negative_flag']:'No';
				$info['include_holiday_flag']	= ($POST['include_holiday_flag'])?$POST['include_holiday_flag']:'No';
				$info['is_compensatory_flag']	= ($POST['is_compensatory_flag'])?$POST['is_compensatory_flag']:'No';
				$info['max_carry_forward_leave']	= ($POST['max_carry_forward_leave'])?$POST['max_carry_forward_leave']:'';
				$info['expiry_carry_forward_leave']	= ($POST['expiry_carry_forward_leave'])?$POST['expiry_carry_forward_leave']:'';
				$info['encashment_allowed_flag']	= ($POST['encashment_allowed_flag'])?$POST['encashment_allowed_flag']:'No';
				$info['earned_leave_flag']	= ($POST['earned_leave_flag'])?$POST['earned_leave_flag']:'No';
				$info['status']	= $POST['status'];
				$info['updated_at']	= date("Y-m-d H:i:s");
				$updateleaveid = update_record('hrms_leave_type', $info,"id=".$POST['eid'] , $dbcon);
		
				if($updateleaveid){	
					$arr['msg']="1";
				}else{
					$arr['msg']="0";
				}
				echo json_encode($arr);	
		}
		else if(strtolower($POST['mode'])== "delete") {
			$row=array();
			$info['status'] = 2;
			$updateid=update_record('hrms_leave_type', $info, "id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['msg']="1";
			else
				$row['msg']="0";
			
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			
			$updateid = update_record('hrms_leave_type', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}	
		
?>