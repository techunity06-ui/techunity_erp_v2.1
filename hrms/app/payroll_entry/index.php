<?php

session_start();
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../../include/function_database_query.php");
include_once("../../../include/common_functions.php");
include_once("../../../include/payroll_common_functions.php");
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
			$where.="payrollentry.status IN (0,1) and payrollentry.company_id = $companyID".check_user('payrollentry');
			$appData = array();
			$i=1;
			$aColumns = array('payrollentry.id','brn.branch_name','payrollentry.series_id','invoicetype.invoice_format','invoicetype.format_value','invoicetype.end_format_value','comp.company_name','payrollentry.status');
			$sIndexColumn = "payrollentry.id";
			$isWhere = array($where);
			$sTable = "payroll_entry as payrollentry";			
			$isJOIN = array("left join tbl_company as comp on comp.company_id = payrollentry.company_id",
							"left join branch_mst as brn on brn.branch_id = payrollentry.branch_id",
							"left join tbl_invoicetype as invoicetype on invoicetype.invoicetype_id=payrollentry.series_id");
			$hOrder = "payrollentry.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['series_id'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['branch_name'];
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per){
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'payroll_entry_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per){
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payroll_entry('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
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
			// Insert New Payroll Salary Component List
			$info['user_id']= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id'] = $_POST['series_id'];
			$info['payroll_posting_date'] = ($_POST['payroll_posting_date'])?date('Y-m-d', strtotime($_POST['payroll_posting_date'])):'';
			$info['payroll_frequency'] = $_POST['payroll_frequency'];
			$info['branch_id'] = $_POST['branch_id'];
			$info['designation_id'] = $_POST['designation_id'];
			$info['department_id'] = $_POST['department_id'];
			$info['validate_attendance_flag'] = ($_POST['validate_attendance_flag'])?$_POST['validate_attendance_flag']:'No';
			$info['salary_slip_based_on_timesheet_flag'] = ($_POST['salary_slip_based_on_timesheet_flag'])?$_POST['salary_slip_based_on_timesheet_flag']:'No';
			$info['payroll_start_date'] = ($_POST['payroll_start_date'])?date('Y-m-d', strtotime($_POST['payroll_start_date'])):'';
			$info['deduct_tax_for_unclaimed_employee_benefits_flag'] = ($_POST['deduct_tax_for_unclaimed_employee_benefits_flag'])?$_POST['deduct_tax_for_unclaimed_employee_benefits_flag']:'No';
			$info['payroll_end_date'] = ($_POST['payroll_end_date'])?date('Y-m-d', strtotime($_POST['payroll_end_date'])):'';
			$info['deduct_tax_for_unsubmitted_tax_exemption_proof_flag'] = ($_POST['deduct_tax_for_unsubmitted_tax_exemption_proof_flag'])?$_POST['deduct_tax_for_unsubmitted_tax_exemption_proof_flag']:'No';
			$info['project_id'] = $_POST['project_id'];
			$info['cost_center_id']	= $_POST['cost_center_id'];
			$info['payment_account_id']	= $_POST['payment_account_id'];
			$info['bank_account_id']	= $_POST['bank_account_id'];
			$info['status']	= $POST['status'];
			$insertpayrollentryid = add_record('payroll_entry', $info, $dbcon);

			$query = $dbcon->query("SELECT `id` FROM `payroll_entry`");
			$total_records = $query->num_rows;
			$updateInfo['taxinvoice_start'] = $total_records;
			$updatesalarypayrollentryid = update_record('tbl_invoicetype', $updateInfo,"invoice_type = 'PAYROLL ENTRY'" , $dbcon);

			if($insertpayrollentryid) {
				$arr['msg']="1";							
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	

		}else if(strtolower($POST['mode']) == "edit") {
			$info['user_id']= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id'] = $_POST['series_id'];
			$info['payroll_posting_date'] = ($_POST['payroll_posting_date'])?date('Y-m-d', strtotime($_POST['payroll_posting_date'])):'';
			$info['payroll_frequency'] = $_POST['payroll_frequency'];
			$info['branch_id'] = $_POST['branch_id'];
			$info['designation_id'] = $_POST['designation_id'];
			$info['department_id'] = $_POST['department_id'];
			$info['validate_attendance_flag'] = ($_POST['validate_attendance_flag'])?$_POST['validate_attendance_flag']:'No';
			$info['salary_slip_based_on_timesheet_flag'] = ($_POST['salary_slip_based_on_timesheet_flag'])?$_POST['salary_slip_based_on_timesheet_flag']:'No';
			$info['payroll_start_date'] = ($_POST['payroll_start_date'])?date('Y-m-d', strtotime($_POST['payroll_start_date'])):'';
			$info['deduct_tax_for_unclaimed_employee_benefits_flag'] = ($_POST['deduct_tax_for_unclaimed_employee_benefits_flag'])?$_POST['deduct_tax_for_unclaimed_employee_benefits_flag']:'No';
			$info['payroll_end_date'] = ($_POST['payroll_end_date'])?date('Y-m-d', strtotime($_POST['payroll_end_date'])):'';
			$info['deduct_tax_for_unsubmitted_tax_exemption_proof_flag'] = ($_POST['deduct_tax_for_unsubmitted_tax_exemption_proof_flag'])?$_POST['deduct_tax_for_unsubmitted_tax_exemption_proof_flag']:'No';
			$info['project_id'] = $_POST['project_id'];
			$info['cost_center_id']	= $_POST['cost_center_id'];
			$info['payment_account_id']	= $_POST['payment_account_id'];
			$info['bank_account_id']	= $_POST['bank_account_id'];
			$info['status']	= $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			$updatepayrollentryid = update_record('payroll_entry', $info,"id=".$POST['eid'] , $dbcon);
		
			if($updatepayrollentryid){
				$arr['msg']="1";
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode'])== "delete") {
			$row=array();
			$info['status'] = 2;
			$updateid=update_record('payroll_entry', $info, "id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['msg']="1";
			else
				$row['msg']="0";
			echo json_encode($row);
		}	
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];
			$info['status'] = ($p_status=='0') ? '1' : '0';
			$updateid = update_record('payroll_entry', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}
?>