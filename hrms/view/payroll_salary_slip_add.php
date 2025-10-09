<?php
session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/payroll_common_functions.php");
include_once("../../include/function_database_query.php");

$form = "Payroll Salary Slip List";
$mode="Add";
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER[REQUEST_URI], "payroll_salary_slip_edit")==true) {
	$mode="Edit";
	$payrollsalaryslipID = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from payroll_salary_slip where id=$payrollsalaryslipID and company_id = $companyID".check_user('payrollsalaryslip');
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	if($rel){
		
	}else{
		header("Location: " . DOMAIN . HRMS_ROOT . "payroll_salary_slip_list");
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php'); ?>
</head>
<style>
.checkbox_label{ position: absolute; }
.checkbox_label1{ position: absolute;}
.checkbox_label2{ position: absolute;}
.checkbox_label3{ position: absolute;}
.checkbox_label4{ position: absolute;}
.checkbox_label5{ position: absolute;}
.checkbox_label6{ position: absolute !important; overflow: visible; font-size: 15px;}
.checkbox_label7{ position: absolute !important; overflow: visible; font-size: 15px;}
.dd { max-width: none !important; }
</style>
<body>
	<section id="container" class="sidebar-closed">
		<?php include_once('../../include/include_top_menu.php'); ?>
		<?php include_once('../../include/left_menu.php'); ?>
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<h3><?= $mode . ' ' . $form ?></h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . HRMS_ROOT . 'payroll_dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?= ROOT . HRMS_ROOT . 'payroll_salary_slip_list' ?>"><?= $form ?></a></li>
								</ul>
							</div>
						</section>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
					<form class="form-horizontal" role="form" id="payroll_salary_slip_add" action="javascript:;" method="post" name="payroll_salary_slip_add">
						<section class="panel">
							<div class="panel-body">
								<h4><?php if($mode == 'Add') { echo "New"; }else{ echo "Edit"; } ?> <?= $form ?></h4>
									<div class="col-md-12" style="padding-top: 25px;">
										<div class="col-md-12 margin_row">
												<?php if($mode == "Edit"){ ?>
													<div class="col-md-6">
														<div class="form-group">
																<label class="col-md-4 control-label">Series</label>
																<div class="col-md-8 col-xs-11">
																	<input type="text" class="form-control" id="series_edit_id" placeholder="series_id" value="<?php if($mode=='Edit'){ echo $rel['series_id'];} ?>" readonly />
																	<input type="hidden" class="form-control" id="series_id" name="series_id" placeholder="series_id" value="<?php if($mode=='Edit'){ echo $rel['series_id'];} ?>" />
																</div>
														</div>							 
													</div>
							 					<?php } else { ?>
													<div class="col-md-6">
														<div class="form-group">
																<label class="col-md-4 control-label">Series</label>
																<?php
																$series_id = '';
																$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='SALARY SLIP' and company_id = $companyID and `type_id` = '16' order by invoicetype_id ");
																		while ($r = $query->fetch_assoc()) {
																			$series_id = $r['format_value'] . $r['taxinvoice_start'] . $r['end_format_value'];
																		}
																?>
																<div class="col-md-8 col-xs-11">
																	<input type="text" class="form-control" id="series_id" name="series_id" placeholder="series_id" value="<?php echo $series_id; ?>" readonly />
																</div>
														</div>							 
													</div>	
												<?php } ?>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Status*</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" id="salary_slip_status" name="salary_slip_status" placeholder="SALARY SLIP">
																<option selected disabled value="">SELECT STATUS</option>
																<option value="0" <?php if($rel['salary_slip_status'] == '0') { echo 'selected'; } ?>>Draft</option>
																<option value="1" <?php if($rel['salary_slip_status'] == '1') { echo 'selected'; } ?>>Approved</option>
																<option value="2" <?php if($rel['salary_slip_status'] == '2') { echo 'selected'; } ?>>Rejected</option>
																<option value="3" <?php if($rel['salary_slip_status'] == '3') { echo 'selected'; } ?>>Cancelled</option>
															</select>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Posting Date*</label>
														<div class="col-md-8 col-xs-11">
															<input id="posting_date" name="posting_date" type="text" class="form-control default-date-picker required valid" title="Salary Posting Date" placeholder="Salary Posting Date" value="<?php if($mode=='Edit'){ echo date('Y-m-d',strtotime($rel['posting_date'])); } ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
															<label class="col-md-4 control-label">Letter Head</label>
															<div class="col-md-8 col-xs-11">
																<select id="letter_head_id" class="select2" name="letter_head_id">
																	<option selected disabled value="">SELECT LETTER HEAD</option>
																	<?php
																		$query = $dbcon->query("SELECT `id`,`letter_head_name` FROM `hrms_letter_head` WHERE `status` = 0");
																		while ($r = $query->fetch_assoc()) {
																			if($rel['letter_head_id'] == $r['id']){
																				$letterheadIDS = 'selected';
																			}else{
																				$letterheadIDS = '';
																			}
																			echo '<option value="' . $r['id'] . '" '.$letterheadIDS.'>' . $r['letter_head_name'] . '</option>';
																		}
																	?>
																</select>
															</div>
													</div>							 
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Employee Name*</label>
														<div class="col-md-8 col-xs-11">
															<select id="employee_id" class="select2" name="employee_id" required>
																<option selected disabled value="">SELECT EMPLOYEE</option>
																<?php
																	$query = $dbcon->query("SELECT `l_id`,`l_name` FROM `tbl_ledger` WHERE `l_status` = 0 and company_id = $companyID and `l_group` = '58' order by l_name");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['employee_id'] == $r['l_id']){
																			$employeeIDS = 'selected';
																		}else{
																			$employeeIDS = '';
																		}
																		echo '<option value="' . $r['l_id'] . '" '.$employeeIDS.'>' . $r['l_name'] . '</option>';
																	}
																?>
															</select>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Start Date*</label>
														<div class="col-md-8 col-xs-11">
															<input id="salary_start_date" name="salary_start_date" type="text" class="form-control default-date-picker" title="Salary Start Date" placeholder="Salary Start Date" value="<?php if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['salary_start_date']));} ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="salary_slip_based_timesheet_flag" id="salary_slip_based_timesheet_flag" value="<?= ($mode == 'Edit') ? $rel['salary_slip_based_timesheet_flag'] : 'No' ?>" <?php if($rel['salary_slip_based_timesheet_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">  Salary Slip Based on Timesheet</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">End Date*</label>
														<div class="col-md-8 col-xs-11">
															<input id="salary_end_date" name="salary_end_date" type="text" class="form-control default-date-picker" title="Salary End Date" placeholder="Salary End Date" value="<?php if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['salary_end_date']));} ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Payroll Frequency</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" id="payroll_frequency" name="payroll_frequency" placeholder="SALARY PAYROLL FREQUENCY">
																<option selected disabled value="">SELECT PAYROLL FREQUENCY</option>
																<option value="0" <?php if($rel['payroll_frequency'] == '0') { echo 'selected'; } ?>>Monthly</option>
																<option value="1" <?php if($rel['payroll_frequency'] == '1') { echo 'selected'; } ?>>Fortnightly</option>
																<option value="2" <?php if($rel['payroll_frequency'] == '2') { echo 'selected'; } ?>>Bimonthly</option>
																<option value="3" <?php if($rel['payroll_frequency'] == '3') { echo 'selected'; } ?>>Weekly</option>
																<option value="4" <?php if($rel['payroll_frequency'] == '4') { echo 'selected'; } ?>>Daily</option>
															</select>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row salary_hide_div" style="display: none;">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Salary Component</label>
														<div class="col-md-8 col-xs-11">
															<select id="salary_component_id" class="select2" name="salary_component_id">
																<option selected disabled value="">SELECT SALARY COMPONENT</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`salary_component_name` FROM `payroll_salary_component` WHERE `status` = 0");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['salary_component_id'] == $r['id']){
																			$componentIDS = 'selected';
																		}else{
																			$componentIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$componentIDS.'>' . $r['salary_component_name'] . '</option>';
																	}
																?>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-6"></div>
											</div>
											<div class="working_days" style="display: none">
												<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
												<div class="col-md-12 margin_row">
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Working Days</label>
															<div class="col-md-8 col-xs-11">
																<input id="payroll_working_days" name="payroll_working_days" type="text" class="form-control" title="Working Days" placeholder="Working Days" value="<?php if($mode=='Edit'){ echo $rel['payroll_working_days'];} ?>">
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Absent Days</label>
															<div class="col-md-8 col-xs-11">
																<input id="payroll_absent_days" name="payroll_absent_days" type="text" class="form-control" title="Absent Days" placeholder="Absent Days" value="<?php if($mode=='Edit'){ echo $rel['payroll_absent_days'];} ?>">
																<p>Unmarked Days is treated as Present. You can can change this in <b>Payroll Settings</b></p>
															</div>
														</div>
													</div>
												</div>
												<div class="col-md-12 margin_row">
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Leave Without Pay</label>
															<div class="col-md-8 col-xs-11">
																<input id="leave_without_pay" name="leave_without_pay" type="text" class="form-control" title="Leave Without Pay" placeholder="Leave Without Pay" value="<?php if($mode=='Edit'){ echo $rel['leave_without_pay'];} ?>">
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Payment Days</label>
															<div class="col-md-8 col-xs-11">
																<input id="payroll_payment_days" name="payroll_payment_days" type="text" class="form-control" title="Payment Days" placeholder="Payment Days" value="<?php if($mode=='Edit'){ echo $rel['payroll_payment_days'];} ?>">
															</div>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="deduct_tax_for_unclaimed_employee_benefits_flag" id="deduct_tax_for_unclaimed_employee_benefits_flag" value="<?= ($mode == 'Edit') ? $rel['deduct_tax_for_unclaimed_employee_benefits_flag'] : 'No' ?>" <?php if($rel['deduct_tax_for_unclaimed_employee_benefits_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">  Deduct Tax For Unclaimed Employee Benefits</span>
														</div>
													</div>
												</div>
												<div class="col-md-6"></div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="deduct_tax_for_unsubmitted_tax_exemption_proof_flag" id="deduct_tax_for_unsubmitted_tax_exemption_proof_flag" value="<?= ($mode == 'Edit') ? $rel['deduct_tax_for_unsubmitted_tax_exemption_proof_flag'] : 'No' ?>" <?php if($rel['deduct_tax_for_unsubmitted_tax_exemption_proof_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">  Deduct Tax For Unsubmitted Tax Exemption Proof</span>
														</div>
													</div>
												</div>
												<div class="col-md-6"></div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h5>EARNING & DEDUCTION</h5>
											<div class="col-md-12">
												<h6>Earning</h6>
												<div class="col-md-12">
													<div class="form-group">
														<table cellspacing="10" style="border-collapse:inherit; " id="salary_earning" class="display table table12 table-striped table-bordered">
															<tr id="field">
																<th width="15%" class="text-center">Component</th>
																<th width="15%" class="text-center">Amount</th>
																<th width="15%" class="text-center">Action</th>
															</tr>
															<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
															<tr id="field1">
																<td data-label="Component" style="vertical-align:top;">
																	<select id="payroll_component_name_earnings" class="select2" name="payroll_component_name_earnings" onchange="getComponentEarning()">
																		<option selected disabled value="">SELECT COMPONENT</option>
																		<?php
																			$query = $dbcon->query("SELECT `id`,`salary_component_name` FROM `payroll_salary_component` WHERE `status` = '0' AND `salary_component_type` = '0'");
																			while ($r = $query->fetch_assoc()) {
																				if($rel['payroll_component_name'] == $r['id']){
																					$payrollcomponentIDS = 'selected';
																				}else{
																					$payrollcomponentIDS = '';
																				}
																				echo '<option value="' . $r['id'] . '" '.$payrollcomponentIDS.'>' . $r['salary_component_name'] . '</option>';
																			}
																		?>
																	</select>
																</td>
																<td data-label="Amount" style="vertical-align:top;">
																	<input id="payroll_component_amount_earnings" name="payroll_component_amount_earnings" type="text" class="form-control" title="Amount" placeholder="Amount">
																</td>
																<td style="vertical-align:top;">
																	<input type="button"  name="addearningsrow" id="addearningsrow" onClick="return add_earnings_field();"  class="btn btn-primary" value="Add"/>
																</td>
																<input type='hidden' name='edit_id' id='edit_id' value='' />
															</tr>
														</table>			
													</div>
												</div>
												<div id="payroll_earnings_data"></div>
												<h6>Deductions</h6>
												<div class="col-md-12">
													<div class="form-group">
														<table cellspacing="10" style="border-collapse:inherit; " id="salary_earning" class="display table table12 table-striped table-bordered">
															<tr id="field">
																<th width="15%" class="text-center">Component</th>
																<th width="15%" class="text-center">Amount</th>
																<th width="15%" class="text-center">Action</th>
															</tr>
															<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
															<tr id="field1">
																<td data-label="Component" style="vertical-align:top;">
																	<select id="payroll_component_name_deductions" class="select2" name="payroll_component_name_deductions" onchange="getComponentDeduction()">
																		<option selected disabled value="">SELECT COMPONENT</option>
																		<?php
																			$query = $dbcon->query("SELECT `id`,`salary_component_name` FROM `payroll_salary_component` WHERE `status` = '0' AND `salary_component_type` = '1'");
																			while ($r = $query->fetch_assoc()) {
																				if($rel['payroll_component_name'] == $r['id']){
																					$payrollcomponentIDS = 'selected';
																				}else{
																					$payrollcomponentIDS = '';
																				}
																				echo '<option value="' . $r['id'] . '" '.$payrollcomponentIDS.'>' . $r['salary_component_name'] . '</option>';
																			}
																		?>
																	</select>
																</td>
																<td data-label="Amount" style="vertical-align:top;">
																	<input id="payroll_component_amount_deductions" name="payroll_component_amount_deductions" type="text" class="form-control" title="Amount" placeholder="Amount">
																</td>
																<td style="vertical-align:top;">
																	<input type="button"  name="adddeductionsrow" id="adddeductionsrow" onClick="return add_deductions_field();"  class="btn btn-primary" value="Add"/>
																</td>
																<input type='hidden' name='edit_id' id='edit_id' value='' />
															</tr>
														</table>			
													</div>
												</div>
												<div id="payroll_deductions_data"></div>
											</div>
											<div class="gross_pay_days" style="display: none">
												<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
												<div class="col-md-12 margin_row">
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Gross Pay</label>
															<div class="col-md-8 col-xs-11">
																<input id="payroll_gross_pay_amount" name="payroll_gross_pay_amount" type="text" class="form-control" title="Gross Pay" placeholder="Gross Pay" value="<?php if($mode=='Edit'){ echo $rel['payroll_gross_pay_amount'];} else { echo '0.00'; } ?>">
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Total Deduction</label>
															<div class="col-md-8 col-xs-11">
																<input id="payroll_total_deduction_amount" name="payroll_total_deduction_amount" type="text" class="form-control" title="Total Deduction" placeholder="Total Deduction" value="<?php if($mode=='Edit'){ echo $rel['payroll_total_deduction_amount'];} else { echo '0.00'; } ?>">
															</div>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Total Principal Amount</label>
														<div class="col-md-8 col-xs-11">
															<input id="payroll_total_principal_amount" name="payroll_total_principal_amount" type="text" class="form-control" title="Total Principal Amount" placeholder="Total Principal Amount" value="<?php if($mode=='Edit'){ echo $rel['payroll_total_principal_amount'];} else { echo '0.00'; } ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Total Loan Repayment</label>
														<div class="col-md-8 col-xs-11">
															<input id="payroll_total_loan_repayment" name="payroll_total_loan_repayment" type="text" class="form-control" title="Total Loan Repayment" placeholder="Total Loan Repayment" value="<?php if($mode=='Edit'){ echo $rel['payroll_total_loan_repayment'];} else { echo '0.00'; } ?>">
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Total Interest Amount</label>
														<div class="col-md-8 col-xs-11">
															<input id="payroll_total_interest_amount" name="payroll_total_interest_amount" type="text" class="form-control" title="Total Interest Amount" placeholder="Total Interest Amount" value="<?php if($mode=='Edit'){ echo $rel['payroll_total_interest_amount'];} else { echo '0.00'; } ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6"></div>
											</div>
											<div class="net_pay_days" style="display: none">
												<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
												<h5>NET PAY INFO</h5>
												<div class="col-md-12 margin_row">
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Net Pay</label>
															<div class="col-md-8 col-xs-11">
																<input id="payroll_net_pay_amount" name="payroll_net_pay_amount" type="text" class="form-control" title="Net Pay" placeholder="Net Pay" value="<?php if($mode=='Edit'){ echo $rel['payroll_net_pay_amount'];} else { echo '0.00'; } ?>">
																<p>Gross Pay - Total Deduction - Loan Repayment</p>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Rounded Total</label>
															<div class="col-md-8 col-xs-11">
																<input id="payroll_rounded_total_amount" name="payroll_rounded_total_amount" type="text" class="form-control" title="Rounded Total" placeholder="Rounded Total" value="<?php if($mode=='Edit'){ echo $rel['payroll_rounded_total_amount'];} else { echo '0.00'; } ?>">
															</div>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Status</label>
														<div class="col-md-8 col-xs-11">
															<select id="status" class="select2" name="status" required>
																<option selected disabled value="">SELECT STATUS</option>
																<option value="0" <?php if($rel['status'] == '0') { echo 'selected'; } ?>>Active</option>
																<option value="1" <?php if($rel['status'] == '1') { echo 'selected'; } ?>>InActive</option>
															</select>
														</div>
													</div>
												</div>
											</div>
											<br><br>
											<div class="col-md-12 margin_row text-center">
												<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
												<a href="<?= ROOT . HRMS_ROOT . 'payroll_salary_slip_list' ?>" type="button" class="btn btn-danger">Cancel</a>
											</div>
										</div>
									</div>
								</div>
						</div>
						<input type='hidden' name='mode' id='mode' value='<?= $mode ?>' />
						<input type='hidden' name='eid' id='eid' value='<?= $rel['id'] ?>' />
						<input type="hidden" name="row_cnt" id="row_cnt" value="<?= ($mode == 'Edit') ? $ecount : '0' ?>">
				</section>
				</form>
			</div>
			</div>
		</section>
	</section>
	<?php include_once('../../include/footer.php'); ?>
	</section>
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/payroll_salary_slip.js?<?= time() ?>"></script>
	<script>
		$('#selectAll').click(function(e){
		    var table= $(e.target).closest('table');
		    $('td input:checkbox',table).prop('checked',this.checked);
		});
		$(".select2").select2({
			width: '100%'
		});
		$(".default-date-picker").datepicker({
	        format: "dd-mm-yyyy",
	        autoclose: true,
	        todayHighlight: true
	    });
	    $("#salary_end_date").change(function () {
		    var startDate = document.getElementById("salary_start_date").value;
		    var endDate = document.getElementById("salary_end_date").value;
		    if ((Date.parse(parseDate(endDate)) <= Date.parse(parseDate(startDate)))) {
		        alert("Salary end date should be greater than salary start date");
		        document.getElementById("salary_end_date").value = "";
		    }
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'hrms/app/payroll_salary_slip/',
				data: { mode : "start_end_date_diff",startDate: startDate,endDate: endDate},
				success: function(data){
					var arr = jQuery.parseJSON(data);
					$(".working_days").css("display","block");
					$("#payroll_working_days").val(arr.days);
					$("#payroll_absent_days").val('0');
					$("#payroll_payment_days").val(arr.days);
					$("#leave_without_pay").val('0.00');		
					Unloading();
				}		
			});
		});

		function parseDate(str) {
          var mdy = str.split('-');
          return new Date(mdy[2], mdy[1] - 1, mdy[0]);
      	}
	    $(document).on("change","#employee_id", function(){
	    	$(".working_days").css("display","block");
	    	$(".salary_hide_div").css("display","block");
	    	$(".gross_pay_days").css("display","block");
	    	$(".net_pay_days").css("display","block");
	    });
      	$(document).ready(function(){
      		$(document).on("click","#salary_slip_based_timesheet_flag", function(){
				if($(this).is(":checked")){
					$("#salary_slip_based_timesheet_flag").val('Yes');
				}else{
					$("#salary_slip_based_timesheet_flag").val('No');
				}
			});
			$(document).on("click","#deduct_tax_for_unclaimed_employee_benefits_flag", function(){
				if($(this).is(":checked")){
					$("#deduct_tax_for_unclaimed_employee_benefits_flag").val('Yes');
				}else{
					$("#deduct_tax_for_unclaimed_employee_benefits_flag").val('No');
				}
			});
			$(document).on("click","#deduct_tax_for_unsubmitted_tax_exemption_proof_flag", function(){
				if($(this).is(":checked")){
					$("#deduct_tax_for_unsubmitted_tax_exemption_proof_flag").val('Yes');
				}else{
					$("#deduct_tax_for_unsubmitted_tax_exemption_proof_flag").val('No');
				}
			});
      	});
	</script>
	<?php 
		echo "<script>show_earnings_data() </script>";
		echo "<script>show_deductions_data() </script>";
	?>
</body>
</html>