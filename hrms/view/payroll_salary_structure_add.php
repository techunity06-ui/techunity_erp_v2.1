<?php
session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/payroll_common_functions.php");
include_once("../../include/function_database_query.php");

$form = "Payroll Salary Structure List";
$mode="Add";
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER[REQUEST_URI], "payroll_salary_structure_edit")==true) {
	$mode="Edit";
	$payrollsalarystructureID = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from payroll_salary_structure where id=$payrollsalarystructureID and company_id = $companyID".check_user('payrollsalarystructure');
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	if($rel){
		
	}else{
		header("Location: " . DOMAIN . HRMS_ROOT . "payroll_salary_structure_list");
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
									<li><a href="<?= ROOT . HRMS_ROOT . 'payroll_salary_structure_list' ?>"><?= $form ?></a></li>
								</ul>
							</div>
						</section>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
					<form class="form-horizontal" role="form" id="payroll_salary_structure_add" action="javascript:;" method="post" name="payroll_salary_structure_add">
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
																$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='PAYROLL SALARY STRUCTURE' and company_id = $companyID and `type_id` = '16' order by invoicetype_id ");
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
														<label class="col-md-4 control-label">Is Active*</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" id="salary_structure_status" name="salary_structure_status" placeholder="SALARY IS ACTIVE">
																<option selected disabled value="">SELECT STATUS</option>
																<option value="Yes" <?php if($rel['salary_structure_status'] == 'Yes') { echo 'selected'; } ?>>Yes</option>
																<option value="No" <?php if($rel['salary_structure_status'] == 'No') { echo 'selected'; } ?>>No</option>
															</select>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Salary Structure Name*</label>
														<div class="col-md-8 col-xs-11">
															<input id="salary_structure_name" name="salary_structure_name" type="text" class="form-control required valid" title="Salary Structure Name" placeholder="Salary Structure Name" value="<?php if($mode=='Edit'){ echo $rel['salary_structure_name']; } ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6">
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
											<div class="col-md-12 margin_row">
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
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="salary_slip_timesheet_flag" id="salary_slip_timesheet_flag" value="<?= ($mode == 'Edit') ? $rel['salary_slip_timesheet_flag'] : 'No' ?>" <?php if($rel['salary_disable_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">  Salary Slip Based on Timesheet</span>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Leave Encashment Amount Per Day</label>
														<div class="col-md-8 col-xs-11">
															<input id="leave_encashment_amount_per_day" name="leave_encashment_amount_per_day" type="text" class="form-control" title="Leave Encashment Amount Per Day" placeholder="Leave Encashment Amount Per Day" value="<?php if($mode=='Edit'){ echo $rel['leave_encashment_amount_per_day'];} ?>">
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6"></div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Max Benefits (Amount)</label>
														<div class="col-md-8 col-xs-11">
															<input id="max_benefits_amount" name="max_benefits_amount" type="text" class="form-control" title="Max Benefits (Amount)" placeholder="Max Benefits (Amount)" value="<?php if($mode=='Edit'){ echo $rel['max_benefits_amount'];} ?>">
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row salary_hide_div" style="display: none;">
												<div class="col-md-6"></div>
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
											</div>
											<div class="col-md-12 margin_row salary_hide_div" style="display: none;">
												<div class="col-md-6"></div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Hour Rate</label>
														<div class="col-md-8 col-xs-11">
															<input id="salary_component_hour_rate" name="salary_component_hour_rate" type="text" class="form-control" title="Hour Rate" placeholder="Hour Rate" value="<?php if($mode=='Edit'){ echo $rel['salary_component_hour_rate'];} ?>">
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h5>Salary breakup based on Earning and Deduction.</h5>
											<h6>Earning</h6>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="salary_earning" class="display table table12 table-striped table-bordered">
														<tr id="field">
															<th width="15%" class="text-center">Component</th>
															<th width="15%" class="text-center">Abbr</th>
															<th width="15%" class="text-center">Amount</th>
															<th width="15%" class="text-center">Statistic</th>
															<th width="15%" class="text-center">Formula</th>
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
															<td data-label="Abbr" style="vertical-align:top;">
																<input id="payroll_component_abbr_earn" name="payroll_component_abbr_earn" type="text" class="form-control" title="Abbr" placeholder="Abbr" disabled>
																<input id="payroll_component_abbr_earnings" name="payroll_component_abbr_earnings" type="hidden" class="form-control" title="Abbr" placeholder="Abbr">
															</td>
															<td data-label="Amount" style="vertical-align:top;">
																<input id="payroll_component_amount_earnings" name="payroll_component_amount_earnings" type="text" class="form-control" title="Amount" placeholder="Amount">
															</td>
															<td data-label="Statistic" style="vertical-align:top; text-align: center;">
																<input type="checkbox" name="payroll_component_statistic_flag_earnings" id="payroll_component_statistic_flag_earnings" >
															</td>
															<td data-label="Formula" style="vertical-align:top;">
																<input id="payroll_component_formula_earnings" name="payroll_component_formula_earnings" type="text" class="form-control" title="Formula" placeholder="Formula">
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
															<th width="15%" class="text-center">Abbr</th>
															<th width="15%" class="text-center">Amount</th>
															<th width="15%" class="text-center">Statistic</th>
															<th width="15%" class="text-center">Formula</th>
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
															<td data-label="Abbr" style="vertical-align:top;">
																<input id="payroll_component_abbr_dedu" name="payroll_component_abbr_dedu" type="text" class="form-control" title="Abbr" placeholder="Abbr" disabled>
																<input id="payroll_component_abbr_deductions" name="payroll_component_abbr_deductions" type="hidden" class="form-control" title="Abbr" placeholder="Abbr">
															</td>
															<td data-label="Amount" style="vertical-align:top;">
																<input id="payroll_component_amount_deductions" name="payroll_component_amount_deductions" type="text" class="form-control" title="Amount" placeholder="Amount">
															</td>
															<td data-label="Statistic" style="vertical-align:top; text-align: center;">
																<input type="checkbox" name="payroll_component_statistic_flag_deductions" id="payroll_component_statistic_flag_deductions" >
															</td>
															<td data-label="Formula" style="vertical-align:top;">
																<input id="payroll_component_formula_deductions" name="payroll_component_formula_deductions" type="text" class="form-control" title="Formula" placeholder="Formula">
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
											<a href="#" id="condition_formula">Condition and Formula Help</a><br><br>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h5>ACCOUNT</h5>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Mode of Payment</label>
														<div class="col-md-8 col-xs-11">
															<select id="payment_mode_id" class="select2" name="payment_mode_id">
																<option selected disabled value="">SELECT PAYMENT MODE</option>
																<?php
																	$query = $dbcon->query("SELECT `paymentmodeid`,`payment_mode` FROM `tbl_payment_mode` WHERE `status` = 0");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['payment_mode_id'] == $r['paymentmodeid']){
																			$paymentmodeIDS = 'selected';
																		}else{
																			$paymentmodeIDS = '';
																		}
																		echo '<option value="' . $r['paymentmodeid'] . '" '.$paymentmodeIDS.'>' . $r['payment_mode'] . '</option>';
																	}
																?>
															</select>
														</div>
													</div>
												</div>
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
											</div>
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
												<a href="<?= ROOT . HRMS_ROOT . 'payroll_salary_structure_list' ?>" type="button" class="btn btn-danger">Cancel</a>
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
	<div class="modal colored-header info" id="ModalEditCondition" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Condition and Formula Help</h3>
				</div>
				<div style="margin: 10px;">
					<h4><b>Variables</b></h4>
					<p><b>1.</b> Variables from Salary Structure Assignment:
					<b>base = Base, variable = Variable</b> etc.</p>
					<p><b>2.</b> Variables from Employee:
					<b>Employment Type = employment_type, Branch = branch</b> etc.</p>
					<p><b>3.</b> Variables from Salary Slip:
					<b>Payment Days = payment_days, Leave without pay = leave_without_pay</b> etc.</p>
					<p><b>4.</b> Abbreviation from Salary Component:
					<b>BS = Basic Salary </b> etc.</p>
					<p><b>5.</b> Some additional variable:
					<b>gross_pay and annual_taxable_earning can also be used.</b>
					Direct Amount can also be used</p>
					<h4><b>Examples for Conditions and formula</b></h4>
					<p><b>1.</b> Calculating Basic Salary based on base</p>
					<b>Condition: base < 10000</b><br>
					<b>Formula: base * .2</b><br>
					<p><b>2.</b>Calculating HRA based on Basic SalaryBS</p>
					<b>Condition: BS > 2000</b><br>
					<b>Formula: BS * .1</b><br>
					<p><b>3.</b>Calculating TDS based on Employment Type employment_type</p>
					<b>Condition: employment_type=="Intern"</b><br>
					<b>Amount: 1000</b><br>
					<p><b>3.</b>Calculating Income Tax based on annual_taxable_earning</p>
					<b>Condition: annual_taxable_earning > 20000000</b><br>
					<b>Formula: annual_taxable_earning * 0.10</b><br>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<?php include_once('../../include/footer.php'); ?>
	</section>
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/payroll_salary_structure.js?<?= time() ?>"></script>
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
      	<?php if($mode == 'Edit'){ ?>
			var fexiblecheckboxChecked = $("#salary_slip_timesheet_flag").is(":checked");
			if(fexiblecheckboxChecked){
				$(".salary_hide_div").css("display","block");
			}else{
				$(".salary_hide_div").css("display","none");
			}
		<?php } ?>
      	$(document).ready(function(){
			$(document).on("click","#condition_formula", function(){
				$("#ModalEditCondition").modal("show");
			});
      		$(document).on("click","#salary_slip_timesheet_flag", function(){
				if($(this).is(":checked")){
					$("#salary_slip_timesheet_flag").val('Yes');
					$(".salary_hide_div").css("display","block");
				}else{
					$("#salary_slip_timesheet_flag").val('No');
					$(".salary_hide_div").css("display","none");
				}
			});
      	});
	</script>
</body>
</html>