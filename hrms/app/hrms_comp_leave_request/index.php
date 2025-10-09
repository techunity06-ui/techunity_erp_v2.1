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
			$where.="hrmscomp.status IN (0,1) and hrmscomp.company_id = $companyID".check_user('hrmscomp');
			$appData = array();
			$i=1;
			$aColumns = array('hrmscomp.id','hrmscomp.work_from_date', 'hrmscomp.work_end_date', 'hrmscomp.is_half_day_leave_flag', 'hrmscomp.leave_request_reason', 'hrmscomp.status', 'comp.company_name', 'empusers.l_name', 'leavetype.leave_type_name');
			$sIndexColumn = "hrmscomp.id";
			$isWhere = array($where);
			$sTable = "hrms_comp_leave_request as hrmscomp";			
			$isJOIN = array("left join tbl_company as comp on comp.company_id = hrmscomp.company_id","left join tbl_ledger as empusers on empusers.l_id=hrmscomp.employee_id", "left join hrms_leave_type as leavetype on leavetype.id=hrmscomp.leave_type_id");
			$hOrder = "hrmscomp.id desc";
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
				$row_data[] = $row['l_name'];
				$row_data[] = $row['leave_type_name'];
				$row_data[] = $row['work_from_date'];
				$row_data[] = $row['work_end_date'];
				$row_data[] = $row['leave_request_reason'];
				
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){  
					if($edit_btn_per) {
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'hrms_comp_leave_request_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per) {
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_hrms_comp_leave_request('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
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

			// Insert New Compensatory Leave Request List
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['employee_id'] = $POST['employee_id'];
			$info['leave_type_id']	= $POST['leave_type_id'];
			$info['work_from_date']	= date('Y-m-d', strtotime($POST['work_from_date']));
			$info['work_end_date']	= date('Y-m-d', strtotime($POST['work_end_date']));
			$info['is_half_day_leave_flag']	= ($POST['is_half_day_leave_flag'])?$POST['is_half_day_leave_flag']:'No';
			$info['leave_request_reason']	= $POST['leave_request_reason'];
			$info['status']	= $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			
			$inserleaveid = add_record('hrms_comp_leave_request', $info, $dbcon);
			
			if($inserleaveid){	
				$arr['msg']="1";							
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	

		}else if(strtolower($POST['mode']) == "edit") {
			$info['employee_id'] = $POST['employee_id'];
			$info['leave_type_id']	= $POST['leave_type_id'];
			$info['work_from_date']	= date('Y-m-d', strtotime($POST['work_from_date']));
			$info['work_end_date']	= date('Y-m-d', strtotime($POST['work_end_date']));
			$info['is_half_day_leave_flag']	= ($POST['is_half_day_leave_flag'])?$POST['is_half_day_leave_flag']:'No';
			$info['leave_request_reason']	= $POST['leave_request_reason'];
			$info['status']	= $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			$updateleaveid = update_record('hrms_comp_leave_request', $info,"id=".$POST['eid'] , $dbcon);
	
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
			$updateid=update_record('hrms_comp_leave_request', $info, "id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['msg']="1";
			else
				$row['msg']="0";
			
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			
			$updateid = update_record('hrms_comp_leave_request', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}	
		
?>