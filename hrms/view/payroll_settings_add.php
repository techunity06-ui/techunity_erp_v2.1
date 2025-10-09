<?php

session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/hrms_common_functions.php");
include_once("../../include/function_database_query.php");

$form = "Payroll Settings List";
$mode="Add";
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER[REQUEST_URI], "payroll_settings_edit")==true) {
	$mode="Edit";
	$payrollsettingsId = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from payroll_settings where id=$payrollsettingsId and company_id = $companyID and user_id = $userID";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	if($rel){
	}else{
		header("Location: ". DOMAIN . HRMS_ROOT . "payroll_settings_list");
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php'); ?>
</head>
<style type="text/css">
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
												<li><a href="<?= ROOT . HRMS_ROOT . 'payroll_settings_list' ?>"><?= $form ?></a></li>
											</ul>
										</div>
									</section>
								</div>
							</div>
							<form class="form-horizontal" role="form" id="payroll_settings_add" action="javascript:;" method="post" name="payroll_settings_add">
								<div class="row">
									<div class="col-sm-12">
										<section class="panel">
											<div class="panel-body">
												<h4><?php if($mode == 'Add') { echo "New"; }else{ echo "Edit"; } ?> <?= $form ?></h4>
													<div class="col-md-12" style="padding-top: 25px;">
														<div class="col-md-12 margin_row">
															<h5>PAYROLL SETTINGS</h5><br>	
															<div class="col-md-6">
													  			<div class="form-group">
													  				<label class="col-md-4 control-label">Calculate Payroll Working Days Based On</label>
													  				<div class="col-md-8 col-xs-11">
																		<select id="calculate_payroll_working_days_based" class="select2" name="calculate_payroll_working_days_based">
																			<option selected disabled value="">SELECT CALCULATE PAYROLL</option>
																			<option value="leave" <?php if($rel['calculate_payroll_working_days_based'] == '0') { echo 'selected'; } ?>>Leave</option>
																			<option value="attendance" <?php if($rel['calculate_payroll_working_days_based'] == '1') { echo 'selected'; } ?>>Attendance</option>
																		</select>
																	</div>
													  			</div>
													  		</div>
													  		<div class="col-md-6">
																<div class="form-group">
																	<label class="col-md-4 control-label">Fraction of Daily Salary for Half Day</label>
																	<div class="col-md-8 col-xs-11">
																		<input id="fraction_of_daily_salary_for_half_day" name="fraction_of_daily_salary_for_half_day" type="text" class="form-control" title="Fraction of Daily Salary for Half Day" placeholder="Fraction of Daily Salary for Half Day" value="<?php if($mode=='Edit'){ echo $rel['fraction_of_daily_salary_for_half_day']; } else { echo '0.0'; } ?>">
																		<p>The fraction of daily wages to be paid for half-day attendance</p>
																	</div>

																</div>
															</div>
													  	</div>
													  	<div class="col-md-12 margin_row">
													  		<div class="col-md-6">
																<div class="form-group">
																	<label class="col-md-4 control-label">Max working hours against Timesheet</label>
																	<div class="col-md-8 col-xs-11">
																		<input id="max_working_hours_against_timesheet" name="max_working_hours_against_timesheet" type="text" class="form-control" title="Max working hours against Timesheet" placeholder="Max working hours against Timesheet" value="<?php if($mode=='Edit'){ echo $rel['max_working_hours_against_timesheet']; } else { echo '0.0'; } ?>">
																	</div>

																</div>
															</div>
													  		<div class="col-md-6">
													  			<div class="form-group">
													  				<label class="col-md-2 control-label"></label>
																	<div class="col-md-8 col-xs-11">
																		<input type="checkbox" name="email_salary_slip_to_employee_flag" id="email_salary_slip_to_employee_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['email_salary_slip_to_employee_flag'] : 'No' ?>" <?php if($rel['email_salary_slip_to_employee_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label1">Email Salary Slip to Employee</span>
																		<p>Emails salary slip to employee based on preferred email selected in Employee</p>
																	</div>
																</div>
															</div>
														</div>
														<div class="col-md-12 margin_row">
															<div class="col-md-6">
													  			<div class="form-group">
													  				<label class="col-md-2 control-label"></label>
																	<div class="col-md-8 col-xs-11">
																		<input type="checkbox" name="include_holidays_in_total_no_of_working_days_flag" id="include_holidays_in_total_no_of_working_days_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['include_holidays_in_total_no_of_working_days_flag'] : 'No' ?>" <?php if($rel['include_holidays_in_total_no_of_working_days_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label1">Include holidays in Total no. of Working Days</span>
																		<p>If checked, Total no. of Working Days will include holidays, and this will reduce the value of Salary Per Day</p>
																	</div>
																</div>
															</div>
															<div class="col-md-6">
													  			<div class="form-group">
													  				<label class="col-md-2 control-label"></label>
																	<div class="col-md-8 col-xs-11">
																		<input type="checkbox" name="encrypt_salary_slips_in_emails_flag" id="encrypt_salary_slips_in_emails_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['encrypt_salary_slips_in_emails_flag'] : 'No' ?>" <?php if($rel['encrypt_salary_slips_in_emails_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label1">Encrypt Salary Slips in Emails</span>
																		<p>The salary slip emailed to the employee will be password protected, the password will be generated based on the password policy.</p>
																	</div>
																</div>
															</div>
														</div>
														<div class="col-md-12 margin_row">
															<div class="col-md-6">
													  			<div class="form-group">
													  				<label class="col-md-2 control-label"></label>
																	<div class="col-md-8 col-xs-11">
																		<input type="checkbox" name="disable_rounded_total_flag" id="disable_rounded_total_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['disable_rounded_total_flag'] : 'No' ?>" <?php if($rel['disable_rounded_total_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label1">Disable Rounded Total</span>
																		<p>If checked, hides and disables Rounded Total field in Salary Slips</p>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</section>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<section class="panel">
												<div class="panel-body">
									                <div class="col-md-12 margin_row">
														<div class="col-md-6">
												  			<div class="form-group">
																<label class="col-md-3 control-label">Status</label>
																<div class="col-md-8 col-xs-11">
																	<select id="status" class="select2" name="status">
																		<option selected disabled value="">SELECT STATUS</option>
																		<option value="0" <?php if($rel['status'] == '0') { echo 'selected'; } ?>>Active</option>
																		<option value="1" <?php if($rel['status'] == '1') { echo 'selected'; } ?>>InActive</option>
																	</select>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-12 margin_row text-center">
														<br>
														<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
														<a href="<?= ROOT . HRMS_ROOT . 'payroll_settings_list' ?>" type="button" class="btn btn-danger">Cancel</a>
													</div>
												</div>
											</section>
										</div>
									</div>
									<input type='hidden' name='mode' id='mode' value='<?= $mode ?>' />
									<input type='hidden' name='eid' id='eid' value='<?= $rel['id'] ?>' />
									<input type="hidden" name="row_cnt" id="row_cnt" value="<?= ($mode == 'Edit') ? $ecount : '0' ?>">
							</form>
					</div>
			
		</section>
	</section>
	<?php include_once('../../include/footer.php'); ?>
	</section>
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/payroll_settings.js?<?= time() ?>"></script>
	<script>	
		$('#selectAll').click(function(e){
		    var table= $(e.target).closest('table');
		    $('td input:checkbox',table).prop('checked',this.checked);
		});
		$(".select2").select2({
			width: '100%'
		});
		$(document).ready(function(){
			$(document).on("click","#email_salary_slip_to_employee_flag", function(){
				if($(this).is(":checked")){
					$("#email_salary_slip_to_employee_flag").val('Yes');
				}else{
					$("#email_salary_slip_to_employee_flag").val('No');
				}
			});
			$(document).on("click","#include_holidays_in_total_no_of_working_days_flag", function(){
				if($(this).is(":checked")){
					$("#include_holidays_in_total_no_of_working_days_flag").val('Yes');
				}else{
					$("#include_holidays_in_total_no_of_working_days_flag").val('No');
				}
			});
			$(document).on("click","#encrypt_salary_slips_in_emails_flag", function(){
				if($(this).is(":checked")){
					$("#encrypt_salary_slips_in_emails_flag").val('Yes');
				}else{
					$("#encrypt_salary_slips_in_emails_flag").val('No');
				}
			});
			$(document).on("click","#disable_rounded_total_flag", function(){
				if($(this).is(":checked")){
					$("#disable_rounded_total_flag").val('Yes');
				}else{
					$("#disable_rounded_total_flag").val('No');
				}
			});	
		});
	</script>
</body>
</html>