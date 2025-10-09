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
			$where.="payrollsalaryslip.status IN (0,1) and payrollsalaryslip.company_id = $companyID".check_user('payrollsalaryslip');
			$appData = array();
			$i=1;
			$aColumns = array('payrollsalaryslip.id','payledger.l_name','payrollsalaryslip.posting_date','payrollsalaryslip.series_id','invoicetype.invoice_format','invoicetype.format_value','invoicetype.end_format_value', 'comp.company_name','payrollsalaryslip.status');
			$sIndexColumn = "payrollsalaryslip.id";
			$isWhere = array($where);
			$sTable = "payroll_salary_slip as payrollsalaryslip";			
			$isJOIN = array("left join tbl_company as comp on comp.company_id = payrollsalaryslip.company_id",
							"left join tbl_ledger as payledger on payledger.l_id = payrollsalaryslip.employee_id",
							"left join tbl_invoicetype as invoicetype on invoicetype.invoicetype_id=payrollsalaryslip.series_id");
			$hOrder = "payrollsalaryslip.id desc";
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
				$row_data[] = $row['l_name'];
				$row_data[] = $row['posting_date'];
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per){
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'. ROOT . HRMS_ROOT . 'payroll_salary_slip_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per){
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payroll_salary_slip('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
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
			// Insert New Payroll Salary Slip List
			$info['user_id']= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id'] = $POST['series_id'];
			$info['salary_slip_status'] = $_POST['salary_slip_status'];
			$info['posting_date'] = ($_POST['posting_date']) ? date('Y-m-d',strtotime($_POST['posting_date'])) : '';
			$info['employee_id'] = $_POST['employee_id'];
			$info['letter_head_id'] = $_POST['letter_head_id'];
			$info['salary_start_date']	= ($_POST['salary_start_date']) ? date('Y-m-d',strtotime($_POST['salary_start_date'])) : '';
			$info['salary_end_date']	= ($_POST['salary_end_date']) ? date('Y-m-d',strtotime($_POST['salary_end_date'])) : '';
			$info['salary_slip_based_timesheet_flag']	= ($_POST['salary_slip_based_timesheet_flag'])?$_POST['salary_slip_based_timesheet_flag']:'No';
			$info['payroll_frequency'] = $_POST['payroll_frequency'];
			$info['salary_component_id'] = ($_POST['salary_component_id'])?$_POST['salary_component_id']:'';
			$info['payroll_working_days'] = ($_POST['payroll_working_days'])?$_POST['payroll_working_days']:'0';
			$info['payroll_absent_days'] = ($_POST['payroll_absent_days'])?$_POST['payroll_absent_days']:'0';
			$info['leave_without_pay'] = ($_POST['leave_without_pay'])?$_POST['leave_without_pay']:'0';
			$info['payroll_payment_days'] = ($_POST['payroll_payment_days'])?$_POST['payroll_payment_days']:'0';
			$info['deduct_tax_for_unclaimed_employee_benefits_flag'] = ($_POST['deduct_tax_for_unclaimed_employee_benefits_flag'])?$_POST['deduct_tax_for_unclaimed_employee_benefits_flag']:'No';
			$info['deduct_tax_for_unsubmitted_tax_exemption_proof_flag'] = ($_POST['deduct_tax_for_unsubmitted_tax_exemption_proof_flag'])?$_POST['deduct_tax_for_unsubmitted_tax_exemption_proof_flag']:'No';
			$info['payroll_gross_pay_amount'] = ($_POST['payroll_gross_pay_amount'])?$_POST['payroll_gross_pay_amount']:'0.00';
			$info['payroll_total_deduction_amount'] = ($_POST['payroll_total_deduction_amount'])?$_POST['payroll_total_deduction_amount']:'0.00';
			$info['payroll_total_principal_amount'] = ($_POST['payroll_total_principal_amount'])?$_POST['payroll_total_principal_amount']:'0.00';
			$info['payroll_total_loan_repayment'] = ($_POST['payroll_total_loan_repayment'])?$_POST['payroll_total_loan_repayment']:'0.00';
			$info['payroll_total_interest_amount'] = ($_POST['payroll_total_interest_amount'])?$_POST['payroll_total_interest_amount']:'0.00';
			$info['payroll_net_pay_amount'] = ($_POST['payroll_net_pay_amount'])?$_POST['payroll_net_pay_amount']:'0.00';
			$info['payroll_rounded_total_amount'] = ($_POST['payroll_rounded_total_amount'])?$_POST['payroll_rounded_total_amount']:'0.00';
			$info['status']	= $POST['status'];
			$insertpayrollslipid = add_record('payroll_salary_slip', $info, $dbcon);

			$query = $dbcon->query("SELECT `id` FROM `payroll_salary_slip`");
			$total_records = $query->num_rows;
			$updateInfo['taxinvoice_start'] = $total_records;
			$updatesalaryslipid = update_record('tbl_invoicetype', $updateInfo,"invoice_type = 'SALARY SLIP'" , $dbcon);

			$info_update['status']	= 0;
			$info_update['payroll_salary_slip_id']	= $insertpayrollslipid;
			$updatesalarybreakupearningsid=update_record('payroll_salary_slip_earnings', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			$updatesalarybreakupdeductionsid=update_record('payroll_salary_slip_deductions', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);

			if($updatesalaryslipid) {
				$arr['msg']="1";							
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	

		}else if(strtolower($POST['mode']) == "edit") {
			$info['user_id']= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id'] = $POST['series_id'];
			$info['salary_slip_status'] = $_POST['salary_slip_status'];
			$info['posting_date'] = ($_POST['posting_date']) ? date('Y-m-d',strtotime($_POST['posting_date'])) : '';
			$info['employee_id'] = $_POST['employee_id'];
			$info['letter_head_id'] = $_POST['letter_head_id'];
			$info['salary_start_date']	= ($_POST['salary_start_date']) ? date('Y-m-d',strtotime($_POST['salary_start_date'])) : '';
			$info['salary_end_date']	= ($_POST['salary_end_date']) ? date('Y-m-d',strtotime($_POST['salary_end_date'])) : '';
			$info['salary_slip_based_timesheet_flag']	= ($_POST['salary_slip_based_timesheet_flag'])?$_POST['salary_slip_based_timesheet_flag']:'No';
			$info['payroll_frequency'] = $_POST['payroll_frequency'];
			$info['salary_component_id'] = ($_POST['salary_component_id'])?$_POST['salary_component_id']:'';
			$info['payroll_working_days'] = ($_POST['payroll_working_days'])?$_POST['payroll_working_days']:'0';
			$info['payroll_absent_days'] = ($_POST['payroll_absent_days'])?$_POST['payroll_absent_days']:'0';
			$info['leave_without_pay'] = ($_POST['leave_without_pay'])?$_POST['leave_without_pay']:'0';
			$info['payroll_payment_days'] = ($_POST['payroll_payment_days'])?$_POST['payroll_payment_days']:'0';
			$info['deduct_tax_for_unclaimed_employee_benefits_flag'] = ($_POST['deduct_tax_for_unclaimed_employee_benefits_flag'])?$_POST['deduct_tax_for_unclaimed_employee_benefits_flag']:'No';
			$info['deduct_tax_for_unsubmitted_tax_exemption_proof_flag'] = ($_POST['deduct_tax_for_unsubmitted_tax_exemption_proof_flag'])?$_POST['deduct_tax_for_unsubmitted_tax_exemption_proof_flag']:'No';
			$info['payroll_gross_pay_amount'] = ($_POST['payroll_gross_pay_amount'])?$_POST['payroll_gross_pay_amount']:'0.00';
			$info['payroll_total_deduction_amount'] = ($_POST['payroll_total_deduction_amount'])?$_POST['payroll_total_deduction_amount']:'0.00';
			$info['payroll_total_principal_amount'] = ($_POST['payroll_total_principal_amount'])?$_POST['payroll_total_principal_amount']:'0.00';
			$info['payroll_total_loan_repayment'] = ($_POST['payroll_total_loan_repayment'])?$_POST['payroll_total_loan_repayment']:'0.00';
			$info['payroll_total_interest_amount'] = ($_POST['payroll_total_interest_amount'])?$_POST['payroll_total_interest_amount']:'0.00';
			$info['payroll_net_pay_amount'] = ($_POST['payroll_net_pay_amount'])?$_POST['payroll_net_pay_amount']:'0.00';
			$info['payroll_rounded_total_amount'] = ($_POST['payroll_rounded_total_amount'])?$_POST['payroll_rounded_total_amount']:'0.00';
			$info['status']	= $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			$updatepayrollslipid = update_record('payroll_salary_slip', $info,"id=".$POST['eid'] , $dbcon);
		
			if($updatepayrollslipid){
				$arr['msg']="1";
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode'])== "delete") {
			$row=array();
			$info['status'] = 2;
			$updateid=update_record('payroll_salary_slip', $info, "id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['msg']="1";
			else
				$row['msg']="0";
			echo json_encode($row);
		}	
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];
			$info['status'] = ($p_status=='0') ? '1' : '0';
			$updateid = update_record('payroll_salary_slip', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}
		else if(strtolower($POST['mode'])== "payroll_salary_slip_earnings"){
			$ids = $POST['comp_earn_id'];
			$q = $dbcon -> query("select * from payroll_salary_component as payrollsalarycomponent
				left join tbl_company as comp on comp.company_id = payrollsalarycomponent.company_id
		 		where id = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "payroll_salary_slip_deductions"){
			$ids = $POST['comp_dedu_id'];
			$q = $dbcon -> query("select * from payroll_salary_component as payrollsalarycomponent
				left join tbl_company as comp on comp.company_id = payrollsalarycomponent.company_id
		 		where id = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "load_earnings_ward") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['psse_id'])){
				$query="select salarybreakupslip.*, comp.company_name, salarycompo.salary_component_name from payroll_salary_slip_earnings as salarybreakupslip 
				left join tbl_company as comp on comp.company_id = salarybreakupslip.company_id
				left join payroll_salary_component as salarycompo on salarycompo.id = salarybreakupslip.payroll_component_id
		 		where `salarybreakupslip`.`status` = 3 and `salarybreakupslip`.`user_id` = $userID and `salarybreakupslip`.`company_id` = $companyID";
			}else{
				 $query="select salarybreakupslip.*, comp.company_name, salarycompo.salary_component_name from payroll_salary_slip_earnings as salarybreakupslip
						left join tbl_company as comp on comp.company_id = salarybreakupslip.company_id
						left join payroll_salary_component as salarycompo on salarycompo.id = salarybreakupslip.payroll_component_id
		 			where `salarybreakupslip`.`status` = 0 and `salarybreakupslip`.`payroll_salary_slip_id`=".$POST['psse_id']." and `salarybreakupslip`.`user_id` = $userID and `salarybreakupslip`.`company_id` = ".$companyID;
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th width="15%" class="text-center">Component</th>
							<th width="15%" class="text-center">Amount</th>
							<th width="15%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$rel['id'].'" >
							<td data-label="Component" style="vertical-align:top;" class="text-center">';
									if(empty($rel['salary_component_name'])){
										echo '-';
									}else{
										echo $rel['salary_component_name'];
									}
							echo'</td>
							<td data-label="Amount" style="vertical-align:top;" class="text-center">
								'.$rel['payroll_component_amount'].'
							</td>
							<td data-label="ACTION" style="vertical-align:top">
								<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_earnings_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
								<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_earnings_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
							</td>	
					</tr>';
					$i++;
				}
			}else{
					echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '</table>			 
					</div>
                        </div>';
		}
		else if(strtolower($POST['mode']) == "field_earnings_add") {
			$info1['user_id'] =  $_SESSION['user_id'];
			$info1['company_id'] = $_SESSION['company_id'];
			$info1['payroll_component_id']	= $_POST['payroll_component_name_earnings'];
			$info1['payroll_component_amount']	= $_POST['payroll_component_amount_earnings'];
			if(empty($POST['edit_id'])){
				if(empty($POST['eid'])){
					$info1['status']	= '3';
				}else{
					$info1['status']	= '0';
					$info1['payroll_salary_slip_id'] = $POST['eid'];
				}
			}else{
				if(empty($POST['eid'])){
					$info1['status']	= '3';
				}else{
					$info1['status']	= '0';
				}
			}
			$table='payroll_salary_slip_earnings';
			$tableid='id';
			
			if(empty($POST['edit_id'])){
				$inserid=add_record($table, $info1, $dbcon);
			}else{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
		
		}
		else if(strtolower($POST['mode'])== "pre_earnings_edit")
		{
			$ids = $POST['id'];
			$q = $dbcon -> query("select * from payroll_salary_slip_earnings as salaryslipearnings 
				left join tbl_company as comp on comp.company_id = salaryslipearnings.company_id
		 		where id = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_earnings_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("payroll_salary_slip_earnings", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "load_deductions_ward") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['pssd_id'])){
				$query="select salaryslipdeductions.*, comp.company_name, salarycompo.salary_component_name from payroll_salary_slip_deductions as salaryslipdeductions 
				left join tbl_company as comp on comp.company_id = salaryslipdeductions.company_id
				left join payroll_salary_component as salarycompo on salarycompo.id = salaryslipdeductions.payroll_component_id
		 		where `salaryslipdeductions`.`status` = 3 and `salaryslipdeductions`.`user_id` = $userID and `salaryslipdeductions`.`company_id` = $companyID";
			}else{
				 $query="select salaryslipdeductions.*, comp.company_name, salarycompo.salary_component_name from payroll_salary_slip_deductions as salaryslipdeductions
				left join tbl_company as comp on comp.company_id = salaryslipdeductions.company_id
				left join payroll_salary_component as salarycompo on salarycompo.id = salaryslipdeductions.payroll_component_id
		 		where `salaryslipdeductions`.`status` = 0 and `salaryslipdeductions`.`payroll_salary_slip_id`=".$POST['pssd_id']." and `salaryslipdeductions`.`user_id` = $userID and `salaryslipdeductions`.`company_id` = ".$companyID;
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th width="15%" class="text-center">Component</th>
							<th width="15%" class="text-center">Amount</th>
							<th width="15%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$rel['id'].'" >
							<td data-label="Component" style="vertical-align:top;" class="text-center">';
									if(empty($rel['salary_component_name'])){
										echo '-';
									}else{
										echo $rel['salary_component_name'];
									}
							echo'</td>
							<td data-label="Amount" style="vertical-align:top;" class="text-center">
								'.$rel['payroll_component_amount'].'
							</td>
							<td data-label="ACTION" style="vertical-align:top">
								<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_deductions_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
								<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_deductions_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
							</td>	
					</tr>';
					$i++;
				}
			}else{
					echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '</table>			 
					</div>
                        </div>';
		}
		else if(strtolower($POST['mode']) == "field_deductions_add") {
			$info1['user_id'] =  $_SESSION['user_id'];
			$info1['company_id'] = $_SESSION['company_id'];
			$info1['payroll_component_id']	= $POST['payroll_component_name_deductions'];
			$info1['payroll_component_amount']	= $POST['payroll_component_amount_deductions'];
			if(empty($POST['edit_id'])){
				if(empty($POST['eid'])){
					$info1['status']	= '3';
				}else{
					$info1['status']	= '0';
					$info1['payroll_salary_slip_id'] = $POST['eid'];
				}
			}else{
				if(empty($POST['eid'])){
					$info1['status']	= '3';
				}else{
					$info1['status']	= '0';
				}
			}
			$table='payroll_salary_slip_deductions';
			$tableid='id';
			
			if(empty($POST['edit_id'])){
				$inserid=add_record($table, $info1, $dbcon);
			}else{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
		
		}
		else if(strtolower($POST['mode'])== "pre_deductions_edit")
		{
			$ids = $POST['id'];
			$q = $dbcon -> query("select * from payroll_salary_slip_deductions as salaryslipdeductions 
				left join tbl_company as comp on comp.company_id = salaryslipdeductions.company_id
		 		where id = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_deductions_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("payroll_salary_slip_deductions", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "start_end_date_diff"){
			$row=array();
			$startTimeStamp = strtotime($POST['startDate']);
			$endTimeStamp = strtotime($POST['endDate']);

			$timeDiff = abs($endTimeStamp - $startTimeStamp);

			$numberDays = $timeDiff/86400;  // 86400 seconds in one day

			// and you might want to convert to integer
			$numberDays = intval($numberDays);
			$row['days'] = $numberDays + 1;
			echo json_encode($row);
		}
?>