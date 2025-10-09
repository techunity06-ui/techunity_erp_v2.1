<?php
session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/payroll_common_functions.php");
include_once("../../include/function_database_query.php");

$form = "Payroll Entry List";
$mode="Add";
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER[REQUEST_URI], "payroll_entry_edit")==true) {
	$mode="Edit";
	$payrollentryID = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from payroll_entry where id=$payrollentryID and company_id = $companyID".check_user('payrollentry');
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	if($rel){
		
	}else{
		header("Location: " . DOMAIN . HRMS_ROOT ."payroll_entry_list");
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
									<li><a href="<?= ROOT . HRMS_ROOT . 'payroll_entry_list' ?>"><?= $form ?></a></li>
								</ul>
							</div>
						</section>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
					<form class="form-horizontal" role="form" id="payroll_entry_add" action="javascript:;" method="post" name="payroll_entry_add">
						<section class="panel">
							<div class="panel-body">
									<h4><?php if($mode == 'Add') { echo "New"; }else{ echo "Edit"; } ?> <?= $form ?></h4>
									<h5>SELECT EMPLOYEES</h5>
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
																$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='PAYROLL ENTRY' and company_id = $companyID and `type_id` = '16' order by invoicetype_id ");
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
												<div class="col-md-6"></div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Posting Date*</label>
														<div class="col-md-8 col-xs-11">
															<input id="payroll_posting_date" name="payroll_posting_date" type="text" class="form-control default-date-picker required valid" title="Payroll Posting Date" placeholder="Payroll Posting Date" value="<?php if($mode=='Edit'){ echo $rel['payroll_posting_date']; } ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6" id="payroll_frequency_div" style="display: block;">
													<div class="form-group">
														<label class="col-md-4 control-label">Payroll Frequency*</label>
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
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h5>EMPLOYEES</h5>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Branch</label>
														<div class="col-md-8 col-xs-11">
															<select id="branch_id" class="select2" name="branch_id">
																<option selected disabled value="">SELECT BRANCH</option>
																<?php
																	$query = $dbcon->query("SELECT `branch_id`,`branch_name` FROM `branch_mst` WHERE `branch_status` = 0");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['branch_id'] == $r['branch_id']){
																			$branchIDS = 'selected';
																		}else{
																			$branchIDS = '';
																		}
																		echo '<option value="' . $r['branch_id'] . '" '.$branchIDS.'>' . $r['branch_name'] . '</option>';
																	}
																?>
															</select>
														</div>
													</div>							 
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Designation</label>
														<div class="col-md-8 col-xs-11">
															<select id="designation_id" class="select2" name="designation_id">
																<option selected disabled value="">SELECT DESIGNATION</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`designation_name` FROM `hrms_designation` WHERE `status` = 0");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['designation_id'] == $r['id']){
																			$designationIDS = 'selected';
																		}else{
																			$designationIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$designationIDS.'>' . $r['designation_name'] . '</option>';
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
														<label class="col-md-4 control-label">Department</label>
														<div class="col-md-8 col-xs-11">
															<select id="department_id" class="select2" name="department_id">
																<option selected disabled value="">SELECT DEPARTMENT</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`department_name` FROM `hrms_department` WHERE `status` = 0");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['department_id'] == $r['id']){
																			$departmentIDS = 'selected';
																		}else{
																			$departmentIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$departmentIDS.'>' . $r['department_name'] . '</option>';
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
														<label class="col-md-4 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="validate_attendance_flag" id="validate_attendance_flag" value="<?= ($mode == 'Edit') ? $rel['validate_attendance_flag'] : 'No' ?>" <?php if($rel['validate_attendance_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">  Validate Attendance</span>
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
															<input type="checkbox" name="salary_slip_based_on_timesheet_flag" id="salary_slip_based_on_timesheet_flag" value="<?= ($mode == 'Edit') ? $rel['salary_slip_based_on_timesheet_flag'] : 'No' ?>" <?php if($rel['salary_slip_based_on_timesheet_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">  Salary Slip Based on Timesheet</span>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h5>SELECT PAYROLL PERIOD</h5>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Payroll Start Date*</label>
														<div class="col-md-8 col-xs-11">
															<input id="payroll_start_date" name="payroll_start_date" type="text" class="form-control default-date-picker" title="Payroll Start Date" placeholder="Payroll Start Date" value="<?php if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['payroll_start_date']));} ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="deduct_tax_for_unclaimed_employee_benefits_flag" id="deduct_tax_for_unclaimed_employee_benefits_flag" value="<?= ($mode == 'Edit') ? $rel['deduct_tax_for_unclaimed_employee_benefits_flag'] : 'No' ?>" <?php if($rel['deduct_tax_for_unclaimed_employee_benefits_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">  Deduct Tax For Unclaimed Employee Benefits</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Payroll End Date*</label>
														<div class="col-md-8 col-xs-11">
															<input id="payroll_end_date" name="payroll_end_date" type="text" class="form-control default-date-picker" title="Payroll End Date" placeholder="Payroll End Date" value="<?php if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['payroll_end_date']));} ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="deduct_tax_for_unsubmitted_tax_exemption_proof_flag" id="deduct_tax_for_unsubmitted_tax_exemption_proof_flag" value="<?= ($mode == 'Edit') ? $rel['deduct_tax_for_unsubmitted_tax_exemption_proof_flag'] : 'No' ?>" <?php if($rel['deduct_tax_for_unsubmitted_tax_exemption_proof_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">  Deduct Tax For Unsubmitted Tax Exemption Proof</span>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h5>ACCOUNTING DIMENSIONS</h5>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="project_id" class="col-md-4 control-label">Project</label>
													  	<div class="col-md-8 col-xs-11">
															<select class="select2" id="project_id" name="project_id">
																<option value="">SELECT PROJECT</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`project_name` FROM `hrms_project` WHERE `status` = 0 order by id");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['project_id'] == $r['id']){
																			$projectIDS = 'selected';
																		}else{
																			$projectIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$projectIDS.'>' . $r['project_name'] . '</option>';
																	}
																?>
															</select>	
													  	</div>  	
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="cost_center_id" class="col-md-4 control-label">Cost Center</label>
													  	<div class="col-md-8 col-xs-11">
															<select class="select2" id="cost_center_id" name="cost_center_id">
																<option value="">SELECT COST CENTER</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`cost_center_name` FROM `hrms_cost_center` WHERE `status` = 0 order by id");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['cost_center_id'] == $r['id']){
																			$costcenterIDS = 'selected';
																		}else{
																			$costcenterIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$costcenterIDS.'>' . $r['cost_center_name'] . '</option>';
																	}
																?>
															</select>	
													  	</div>  	
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h5>PAYMENT ENTRY</h5>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Payment Account</label>
														<div class="col-md-8 col-xs-11">
															<select id="payment_account_id" class="select2" name="payment_account_id">
																<option selected disabled value="">SELECT PAYMENT ACCOUNT</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`payment_account_name` FROM `payment_account_info` WHERE `status` = 0");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['payment_account_id'] == $r['id']){
																			$paymentaccountIDS = 'selected';
																		}else{
																			$paymentaccountIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$paymentaccountIDS.'>' . $r['payment_account_name'] . '</option>';
																	}
																?>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Bank Account</label>
														<div class="col-md-8 col-xs-11">
															<select id="bank_account_id" class="select2" name="bank_account_id">
																<option selected disabled value="">SELECT BANK ACCOUNT</option>
																<?php
																	$query = $dbcon->query("SELECT `bankid`,`bank_name` FROM `bank_mst` WHERE `bank_status` = 0");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['bank_account_id'] == $r['bankid']){
																			$bankaccountIDS = 'selected';
																		}else{
																			$bankaccountIDS = '';
																		}
																		echo '<option value="' . $r['bankid'] . '" '.$bankaccountIDS.'>' . $r['bank_name'] . '</option>';
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
											<div class="col-md-12 margin_row text-center">
												<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
												<a href="<?= ROOT . HRMS_ROOT . 'payroll_entry_list' ?>" type="button" class="btn btn-danger">Cancel</a>
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
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/payroll_entry.js?<?= time() ?>"></script>
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

		$("#payroll_end_date").change(function () {
		    var startDate = document.getElementById("payroll_start_date").value;
		    var endDate = document.getElementById("payroll_end_date").value;
		    if ((Date.parse(parseDate(endDate)) <= Date.parse(parseDate(startDate)))) {
		        alert("Payroll end date should be greater than payroll start date");
		        document.getElementById("payroll_end_date").value = "";
		    }
		});

		function parseDate(str) {
          var mdy = str.split('-');
          return new Date(mdy[2], mdy[1] - 1, mdy[0]);
      	}
		<?php if($mode == 'Edit'){ ?>
			var salaryslipbasedontimesheetChecked = $("#salary_slip_based_on_timesheet_flag").is(":checked");
			if(salaryslipbasedontimesheetChecked){
				$("#payroll_frequency_div").css("display","none");
			}else{
				$("#payroll_frequency_div").css("display","block");
			}
		<?php } ?>
		$(document).ready(function(){
      		$(document).on("click","#validate_attendance_flag", function(){
				if($(this).is(":checked")){
					$("#validate_attendance_flag").val('Yes');
				}else{
					$("#validate_attendance_flag").val('No');
				}
			});
			$(document).on("click","#salary_slip_based_on_timesheet_flag", function(){
				if($(this).is(":checked")){
					$("#salary_slip_based_on_timesheet_flag").val('Yes');
					$("#payroll_frequency_div").css("display","none");
				}else{
					$("#salary_slip_based_on_timesheet_flag").val('No');
					$("#payroll_frequency_div").css("display","block");
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
</body>
</html>