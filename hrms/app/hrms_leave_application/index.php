<?php

session_start();
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../../include/function_database_query.php");
include_once("../../../include/common_functions.php");
include_once("../../../include/hrms_common_functions.php");
include_once("../../../include/common_send_email.php");

	if($_POST != NULL) {
		$POST = bulk_filter($dbcon,$_POST);
	} else {
		$POST = bulk_filter($dbcon,$_GET);
	}
	if(strtolower($POST['mode']) == "fetch") {
		
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			$where='';
			$where.="hrmsleaveapp.status IN (0,1) and hrmsleaveapp.company_id = $companyID and leave_from_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND leave_to_date<='".date('Y-m-d',strtotime($s_date[1]))."'".check_user('hrmsleaveapp');
			$appData = array();
			$i=1;
			$aColumns = array('hrmsleaveapp.id','hrmsleaveapp.series_id','invoicetype.invoice_format','invoicetype.format_value','invoicetype.end_format_value','leave_from_date','leave_to_date','half_day_leave_flag','half_day_leave_date','leave_reason', 'comp.company_name','empusers.l_name','empapprousers.l_name as approvername','leave_application_status','salary_slip_id','leave_posting_date','leave_follow_via_mail_flag','letter_head_id','leave_color_code','hrmsleaveapp.status');
			$sIndexColumn = "hrmsleaveapp.id";
			$isWhere = array($where);
			$sTable = "hrms_leave_application as hrmsleaveapp";			
			$isJOIN = array("left join tbl_company as comp on comp.company_id = hrmsleaveapp.company_id","left join tbl_ledger as empusers on empusers.l_id=hrmsleaveapp.employee_id","left join tbl_ledger as empapprousers on empapprousers.l_id=hrmsleaveapp.	leave_approver_id","left join tbl_invoicetype as invoicetype on invoicetype.invoicetype_id=hrmsleaveapp.series_id");
			$hOrder = "hrmsleaveapp.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['id'];
				$row_data[] = $row['series_id'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['l_name'];
				$row_data[] = date('d M, Y',strtotime($row['leave_from_date']));
				$row_data[] = date('d M, Y',strtotime($row['leave_to_date']));
				$row_data[] = $row['half_day_leave_flag'];
				$row_data[] = $row['approvername'];

				if($row['leave_application_status']=='0'){
					$row_data[] = 'Open';
				}else if($row['leave_application_status']=='1'){
					$row_data[] = 'Approved';
				}else if($row['leave_application_status']=='2'){
					$row_data[] = 'Rejected';
				}else{
					$row_data[] = 'Cancelled';
				}

				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per){
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'hrms_leave_application_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per){
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_leave_application('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
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
			$employee_id = getEmployeeIdUser($dbcon, $_SESSION['user_id']);

			// Insert New Leave Application List
			$info['user_id']= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['employee_id'] = ($_SESSION['user_type'] == '2') ? $POST['employee_id'] : $employee_id;
			$info['series_id'] = $POST['series_id'];
			$info['leave_type_id'] = $POST['leave_type_id'];
			$info['leave_from_date'] = date('Y-m-d',strtotime($POST['leave_from_date']));
			$info['leave_to_date'] = date('Y-m-d',strtotime($POST['leave_to_date']));
			$info['half_day_leave_flag']	= $POST['half_day_leave_flag'];
			$info['half_day_leave_date']	= date('Y-m-d',strtotime($POST['half_day_leave_date']));
			$info['leave_reason']	= $POST['leave_reason'];
			$info['leave_approver_id']	= $POST['leave_approver_id'];
			$info['leave_application_status']	= $POST['leave_application_status'];
			$info['salary_slip_id']	= $POST['salary_slip_id'];
			$info['leave_posting_date']	= date('Y-m-d',strtotime($POST['leave_posting_date']));
			$info['leave_follow_via_mail_flag']	= $POST['leave_follow_via_mail_flag'];
			$info['letter_head_id']	= $POST['letter_head_id'];
			$info['leave_color_code']	= $POST['leave_color_code'];
			$info['status']	= $POST['status'];
			$insertleaveid = add_record('hrms_leave_application', $info, $dbcon);

			$query = $dbcon->query("SELECT `id` FROM `hrms_leave_application`");
			$total_records = $query->num_rows;
			$updateInfo['taxinvoice_start'] = $total_records;
			$updateholidayid = update_record('tbl_invoicetype', $updateInfo,"invoicetype_id = '22'" , $dbcon);

			if($insertleaveid) {
				//send mail to approver & admin
				$employee = getUserFromEmployee($dbcon, $info['employee_id']);
				$employee_name = ($employee && $employee['user_name']) ? $employee['user_name'] : '';
				$user = getUserFromEmployee($dbcon, $info['leave_approver_id']);
				$user_email = ($user && $user['user_mail']) ? $user['user_mail'] : '';
				if($user_email) {
					$sts = $info['leave_application_status'];
					$stsLbl = 'Open';
					if($sts == '1') {
						$stsLbl = 'Approved';
					} else if($sts == '2') {
						$stsLbl = 'Rejected';
					}else if($sts == '3') {
						$stsLbl = 'Cancelled';
					}
					$leave = getLeaveType($dbcon, $info['leave_type_id']);
					$leave_type = ($leave && $leave['leave_type_name']) ? $leave['leave_type_name'] : '';
					$emailTemplateBody = getEmailTemplate($dbcon,'LEAVE_STATUS_NOTIFICATION');
					$email_body = $emailTemplateBody['email_template_response'];
					$email_body = str_replace("{{employee_name}}", $employee_name, $email_body);
					$email_body = str_replace("{{leave_type}}", $leave_type, $email_body);
					$email_body = str_replace("{{from_date}}", $info['leave_from_date'], $email_body);
					$email_body = str_replace("{{to_date}}", $info['leave_to_date'], $email_body);
					$email_body = str_replace("{{status}}", $stsLbl, $email_body);
					$email_body = str_replace("{{reason}}", $info['leave_reason'], $email_body);
					$email_subject = $emailTemplateBody['email_template_subject'];

					$sendMail = final_send_email($user_email,ADMIN_EMAIL,'',$email_subject,$email_body,'');
				}
				$arr['msg']="1";							
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	

		}else if(strtolower($POST['mode']) == "edit") {
				$employee_id = getEmployeeIdUser($dbcon, $_SESSION['user_id']);
				$info['employee_id'] = ($_SESSION['user_type'] == '2') ? $POST['employee_id'] : $employee_id;
				$info['series_id'] = $POST['series_id'];
				$info['leave_type_id'] = $POST['leave_type_id'];
				$info['leave_from_date'] = date('Y-m-d',strtotime($POST['leave_from_date']));
				$info['leave_to_date'] = date('Y-m-d',strtotime($POST['leave_to_date']));
				$info['half_day_leave_flag'] = $POST['half_day_leave_flag'];
				$info['half_day_leave_date'] = date('Y-m-d',strtotime($POST['half_day_leave_date']));
				$info['leave_reason'] = $POST['leave_reason'];
				$info['leave_approver_id'] = $POST['leave_approver_id'];
				$info['leave_application_status'] = $POST['leave_application_status'];
				$info['salary_slip_id']	= $POST['salary_slip_id'];
				$info['leave_posting_date']	= date('Y-m-d',strtotime($POST['leave_posting_date']));
				$info['leave_follow_via_mail_flag']	= $POST['leave_follow_via_mail_flag'];
				$info['letter_head_id']	= $POST['letter_head_id'];
				$info['leave_color_code']	= $POST['leave_color_code'];
				$info['status']	= $POST['status'];
				$info['updated_at']	= date("Y-m-d H:i:s");
				$updateleaveid = update_record('hrms_leave_application', $info,"id=".$POST['eid'] , $dbcon);
		
				if($updateleaveid){
					//send mail to employee
					$statusCheck = array('1', '2', '3');
					$sts = $info['leave_application_status'];
					$user = getUserFromEmployee($dbcon, $info['employee_id']);
					$user_email = ($user && $user['user_mail']) ? $user['user_mail'] : '';
					$user_name = ($user && $user['user_name']) ? $user['user_name'] : '';
					$stsLbl = '';
					if($user_email && in_array($sts, $statusCheck)) {
						if($sts == '1') {
							$stsLbl = 'Approved';
						} else if($sts == '2') {
							$stsLbl = 'Rejected';
						}else if($sts == '3') {
							$stsLbl = 'Cancelled';
						}

						$leave = getLeaveType($dbcon, $info['leave_type_id']);
						$leave_type = ($leave && $leave['leave_type_name']) ? $leave['leave_type_name'] : '';
						$emailTemplateBody = getEmailTemplate($dbcon,'LEAVE_APPROVAL_NOTIFICATION');
						$email_body = $emailTemplateBody['email_template_response'];
						$email_body = str_replace("{{employee_name}}", $user_name, $email_body);
						$email_body = str_replace("{{leave_type}}", $leave_type, $email_body);
						$email_body = str_replace("{{from_date}}", $info['leave_from_date'], $email_body);
						$email_body = str_replace("{{to_date}}", $info['leave_to_date'], $email_body);
						$email_body = str_replace("{{status}}", $stsLbl, $email_body);
						$email_body = str_replace("{{reason}}", $info['leave_reason'], $email_body);
						$email_subject = $emailTemplateBody['email_template_subject'];

						$sendMail = final_send_email($user_email,'','',$email_subject,$email_body,'');
					}

					$arr['msg']="1";
				}else{
					$arr['msg']="0";
				}
				echo json_encode($arr);	
		}
		else if(strtolower($POST['mode'])== "delete") {
			$row=array();
			$info['status'] = 2;
			$updateid=update_record('hrms_leave_application', $info, "id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['msg']="1";
			else
				$row['msg']="0";
			
			echo json_encode($row);
		}	
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			
			$updateid = update_record('hrms_leave_application', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}
?>