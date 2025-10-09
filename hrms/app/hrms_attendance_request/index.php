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
			$where='';
			$where.="hrmsattenrequest.status IN (0,1) and hrmsattenrequest.company_id = $companyID".check_user('hrmsattenrequest');
			$appData = array();
			$i=1;
			$aColumns = array('hrmsattenrequest.id','hrmsattenrequest.request_from_date', 'hrmsattenrequest.request_to_date', 'hrmsattenrequest.is_half_day_flag', 'hrmsattenrequest.reason_type', 'hrmsattenrequest.explanation_description', 'empusers.l_name', 'comp.company_name','hrmsattenrequest.status');
			$sIndexColumn = "hrmsattenrequest.id";
			$isWhere = array($where);
			$sTable = "hrms_attendance_request as hrmsattenrequest";			
			$isJOIN = array("left join tbl_company as comp on comp.company_id = hrmsattenrequest.company_id", "left join tbl_ledger as empusers on empusers.l_id=hrmsattenrequest.employee_id");
			$hOrder = "hrmsattenrequest.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['id'];			
				$row_data[] = $row['company_name'];
				$row_data[] = $row['l_name'];
				$row_data[] = $row['request_from_date'];
				$row_data[] = $row['request_to_date'];
				$row_data[] = $row['is_half_day_flag'];
				if($row['reason_type']=='0'){
					$row_data[] = 'Work From Home';
				}else{
					$row_data[] = 'On Duty';
				}
				$row_data[] = $row['explanation_description'];
				
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}

				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per) {
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'hrms_attendance_request_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per) {
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_attendance_request('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
					}
				}
				if($other_btn_per) {
					if($row['status'] == '0')
					{  
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

			// Add New Attendance Request List
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['employee_id'] = $POST['employee_id'];
			$info['request_from_date'] = date('Y-m-d', strtotime($POST['request_from_date']));
			$info['request_to_date'] = date('Y-m-d', strtotime($POST['request_to_date']));
			$info['is_half_day_flag'] = ($POST['is_half_day_flag'])?$POST['is_half_day_flag']:'No';
			$info['reason_type'] = $POST['reason_type'];
			$info['explanation_description'] = $POST['explanation_description'];
			$info['status']	= $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			$inserattenrequestid = add_record('hrms_attendance_request', $info, $dbcon);
			
			if($inserattenrequestid){	
				$arr['msg']="1";							
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	

		}else if(strtolower($POST['mode']) == "edit") {

			// Edit New Attendance List
			$info['employee_id'] = $POST['employee_id'];
			$info['request_from_date'] = date('Y-m-d', strtotime($POST['request_from_date']));
			$info['request_to_date'] = date('Y-m-d', strtotime($POST['request_to_date']));
			$info['is_half_day_flag'] = ($POST['is_half_day_flag'])?$POST['is_half_day_flag']:'No';
			$info['reason_type'] = $POST['reason_type'];
			$info['explanation_description'] = $POST['explanation_description'];
			$info['status']	= $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			$updateattenid = update_record('hrms_attendance_request', $info,"id=".$POST['eid'] , $dbcon);
	
			if($updateattenid){	
				$arr['msg']="1";
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode'])== "delete") {
			$row=array();
			$info['status'] = 2;
			$updateid=update_record('hrms_attendance_request', $info, "id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['msg']="1";
			else
				$row['msg']="0";
			
			echo json_encode($row);
		}	
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			
			$updateid = update_record('hrms_attendance_request', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}	
?>