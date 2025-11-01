<?php
session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/payroll_common_functions.php");
include_once("../../include/function_database_query.php");

$form = "Payroll Salary Component List";
$mode="Add";
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER['REQUEST_URI'], "payroll_salary_component_edit")==true) {
	$mode="Edit";
	$payrollsalarycomponentID = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from payroll_salary_component where id=$payrollsalarycomponentID and company_id = $companyID".check_user('payrollsalarycomponent');
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	if($rel){
		
	}else{
		header("Location: " . DOMAIN . HRMS_ROOT . "payroll_salary_component_list");
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
									<li><a href="<?= ROOT . HRMS_ROOT . 'payroll_salary_component_list' ?>"><?= $form ?></a></li>
								</ul>
							</div>
						</section>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
					<form class="form-horizontal" role="form" id="payroll_salary_component_add" action="javascript:;" method="post" name="payroll_salary_component_add">
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
																$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='PAYROLL SALARY COMPONENT' and company_id = $companyID and `type_id` = '16' order by invoicetype_id ");
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
														<label class="col-md-2 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="depend_on_payment_day_flag" id="depend_on_payment_day_flag" data-id="depend_on_payment_day_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['depend_on_payment_day_flag'] : 'No' ?>" <?php if($rel['depend_on_payment_day_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label"> Depends on Payment Days</span>
														</div>	
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Component Name*</label>
														<div class="col-md-8 col-xs-11">
															<input id="salary_component_name" name="salary_component_name" type="text" class="form-control required valid" title="Salary Component Name" placeholder="Salary Component Name" value="<?php if($mode=='Edit'){ echo $rel['salary_component_name']; } ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6" id="is_tax_earning" style="display: block;">
													<div class="form-group">
														<label class="col-md-2 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="is_tax_applicable_flag" id="is_tax_applicable_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['is_tax_applicable_flag'] : 'No' ?>" <?php if($rel['is_tax_applicable_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label1"> Is Tax Applicable</span>
														</div>
													</div>
												</div>
												<div class="col-md-6" id="is_tax_deduction" style="display: none;">
													<div class="form-group">
														<label class="col-md-2 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="is_income_tax_component_flag" id="is_income_tax_component_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['is_income_tax_component_flag'] : 'No' ?>" <?php if($rel['is_income_tax_component_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label1"> Is Income Tax Component</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Component Abbr*</label>
														<div class="col-md-8 col-xs-11">
															<input id="salary_abbr_value" name="salary_abbr_value" type="text" class="form-control required valid" title="Salary Component Abbr" placeholder="Salary Component Abbr" value="<?php if($mode=='Edit'){ echo $rel['salary_abbr_value']; } ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6" id="deduct_fullamount_taxable" style="display: block;">
													<div class="form-group">
														<label class="col-md-2 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="deduct_fullamount_selected_payroll_flag" id="deduct_fullamount_selected_payroll_flag" value="<?= ($mode == 'Edit') ? $rel['deduct_fullamount_selected_payroll_flag'] : 'No' ?>" <?php if($rel['deduct_fullamount_selected_payroll_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label2">  Deduct Full Tax on Selected Payroll Date</span>
														</div>
													</div>
												</div>
												<div class="col-md-6" id="variable_based_taxable" style="display: none;">
													<div class="form-group">
														<label class="col-md-2 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="variable_based_taxable_salary_flag" id="variable_based_taxable_salary_flag" value="<?= ($mode == 'Edit') ? $rel['variable_based_taxable_salary_flag'] : 'No' ?>" <?php if($rel['variable_based_taxable_salary_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label2">  Variable Based On Taxable Salary</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row" id="exempted_from_income" style="display: none;">
												<div class="col-md-6"></div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-2 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="exempted_from_income_tax_flag" id="exempted_from_income_tax_flag" value="<?= ($mode == 'Edit') ? $rel['exempted_from_income_tax_flag'] : 'No' ?>" <?php if($rel['exempted_from_income_tax_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label4">  Exempted from Income Tax</span>
															<p>If checked, the full amount will be deducted from taxable income before calculating income tax without any declaration or proof submission.</p>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Type*</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" id="salary_component_type" name="salary_component_type" placeholder="SALARY COMPONENT TYPE">
																<option selected disabled value="">SELECT TYPE</option>
																<option value="0" <?php if($rel['salary_component_type'] == '0') { echo 'selected'; } ?>>Earning</option>
																<option value="1" <?php if($rel['salary_component_type'] == '1') { echo 'selected'; } ?>>Deduction</option>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-2 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="nearest_interger_flag" id="nearest_interger_flag" value="<?= ($mode == 'Edit') ? $rel['nearest_interger_flag'] : 'No' ?>" <?php if($rel['nearest_interger_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label3">  Round to the Nearest Integer</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Component Description</label>
														<div class="col-md-8 col-xs-11">
															<textarea style="border: 1px solid #ccc;" id="salary_component_description" name="salary_component_description" placeholder="Salary Component Description" rows="3" cols="72"><?php if($mode=='Edit') { echo $rel['salary_component_description']; } ?></textarea>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-2 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="statistical_component_flag" id="statistical_component_flag" value="<?= ($mode == 'Edit') ? $rel['statistical_component_flag'] : 'No' ?>" <?php if($rel['statistical_component_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label4">  Statistical Component</span>
															<p>If selected, the value specified or calculated in this component will not contribute to the earnings or deductions. However, it's value can be referenced by other components that can be added or deducted.</p>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group" id="component_type_main" style="display: none;">
														<label class="col-md-4 control-label">Component Type*</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" id="component_type" name="component_type" placeholder="COMPONENT TYPE">
																<option selected disabled value="">SELECT COMPONENT TYPE</option>
																<option value="0" <?php if($rel['component_type'] == '0') { echo 'selected'; } ?>>Provident Fund</option>
																<option value="1" <?php if($rel['component_type'] == '1') { echo 'selected'; } ?>>Additional Provident Fund</option>
																<option value="2" <?php if($rel['component_type'] == '2') { echo 'selected'; } ?>>Provident Fund Loan</option>
																<option value="3" <?php if($rel['component_type'] == '3') { echo 'selected'; } ?>>Professional Tax</option>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-2 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="do_not_include_total_flag" id="do_not_include_total_flag" value="<?= ($mode == 'Edit') ? $rel['do_not_include_total_flag'] : 'No' ?>" <?php if($rel['do_not_include_total_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label5">  Do Not Include in Total</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6"></div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-2 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="salary_disable_flag" id="salary_disable_flag" value="<?= ($mode == 'Edit') ? $rel['salary_disable_flag'] : 'No' ?>" <?php if($rel['salary_disable_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label6">  Disabled</span>
														</div>
													</div>
												</div>
											</div>
											<div class="hide_flexible">
												<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
												<h5>FLEXIBLE BENEFITS</h5>
												<div class="col-md-12 margin_row">
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label"></label>
															<div class="col-md-8 col-xs-11">
																<input type="checkbox" name="is_fexible_benefit_flag" id="is_fexible_benefit_flag" value="<?= ($mode == 'Edit') ? $rel['is_fexible_benefit_flag'] : 'No' ?>" <?php if($rel['is_fexible_benefit_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label7">  Is Flexible Benefit</span>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group flexible" style="display: none;">
															<label class="col-md-2 control-label"></label>
															<div class="col-md-8 col-xs-11">
																<input type="checkbox" name="pay_against_benefit_claim_flag" id="pay_against_benefit_claim_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['pay_against_benefit_claim_flag'] : 'No' ?>" <?php if($rel['pay_against_benefit_claim_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label"> Pay Against Benefit Claim</span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group flexible" style="display: none;">
														<label class="col-md-4 control-label">Max Benefit Amount (Yearly)</label>
														<div class="col-md-8 col-xs-11">
															<input id="max_benefit_amount_yearly" name="max_benefit_amount_yearly" type="text" class="form-control" title="Date" placeholder="Max Benefit Amount (Yearly)" value="<?php if($mode=='Edit'){ echo $rel['max_benefit_amount_yearly'];} ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group flexible" style="display: none;">
														<label class="col-md-2 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="only_tax_impect_flag" id="only_tax_impect_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['only_tax_impect_flag'] : 'No' ?>" <?php if($rel['only_tax_impect_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label"> Only Tax Impact (Cannot Claim But Part of Taxable Income)</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6"></div>
												<div class="col-md-6">
													<div class="form-group flexible" style="display: none;">
														<label class="col-md-2 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="create_separate_payment_entry_flag" id="create_separate_payment_entry_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['create_separate_payment_entry_flag'] : 'No' ?>" <?php if($rel['create_separate_payment_entry_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label"> Create Separate Payment Entry Against Benefit Claim</span>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h5>ACCOUNTS</h5>
											<h6>Accounts</h6>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="leave_block_days" class="display table table12 table-striped table-bordered">
														<tr id="field">
															<th width="25%" class="text-center">Company</th>
															<th width="25%" class="text-center">Default Account</th>
															<th width="10%" class="text-center">Action</th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td data-label="Company" style="vertical-align:top;">
																<select id="payroll_account_company_id" class="select2" name="payroll_account_company_id">
																	<option selected disabled value="">SELECT COMPANY</option>
																	<?php
																		$query = $dbcon->query("SELECT `company_id`,`company_name` FROM `tbl_company` WHERE `com_status` = 0");
																		while ($r = $query->fetch_assoc()) {
																			if($rel['payroll_account_company_id'] == $r['company_id']){
																				$accountCompIDS = 'selected';
																			}else{
																				$accountCompIDS = '';
																			}
																			echo '<option value="' . $r['company_id'] . '" '.$accountCompIDS.'>' . $r['company_name'] . '</option>';
																		}
																	?>
																</select>
															</td>
															<td data-label="Default Account" style="vertical-align:top;">
																<select id="payroll_account_id" class="select2" name="payroll_account_id">
																	<option selected disabled value="">SELECT DEFAULT ACCOUNT</option>
																	<?php
																		$query = $dbcon->query("SELECT `id`,`account_name` FROM `payroll_account` WHERE `status` = 0");
																		while ($r = $query->fetch_assoc()) {
																			if($rel['payroll_account_id'] == $r['id']){
																				$defaultaccIDS = 'selected';
																			}else{
																				$defaultaccIDS = '';
																			}
																			echo '<option value="' . $r['id'] . '" '.$defaultaccIDS.'>' . $r['account_name'] . '</option>';
																		}
																	?>
																</select>
															</td>
															<td style="vertical-align:top;">
																<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>			
												</div>
											</div>
											<div id="payroll_accountsdata"></div>
										</div>
									</div>
								</div>
						</div>
						
						<div class="row" id="condition_formula" style="display: block;">
							<div class="col-md-12">
								<section class="panel">
									<header class="panel-heading" style="font-size: 18px;">
										CONDITION AND FORMULA
										<span class="tools pull-right">
											<a href="javascript:;" class="fa fa-chevron-down"></a>
										</span>
									</header>
									<div class="panel-body" id="condition">
										<div class="col-md-12 margin_row">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Condition</label>
													<div class="col-md-8 col-xs-11">
														<textarea style="border: 1px solid #ccc;" id="salary_component_condition" name="salary_component_condition" placeholder="Condition" rows="8" cols="72"><?php if($mode=='Edit') { echo $rel['salary_component_condition']; } ?></textarea>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<h4><b>Help</b></h4>
												<p>1. Use field <b>base</b> for using base salary of the Employee</p>
												<p>2. Use Salary Component abbreviations in conditions and formulas. <b>BS = Basic Salary</b></p>
												<p>3. Use field name for employee details in conditions and formulas. <b>Employment Type = employment_typeBranch = branch</b></p>
												<p>4. Use field name from Salary Slip in conditions and formulas. <b>Payment Days = payment_daysLeave without pay = leave_without_pay</b></p>
											</div>			
										</div>
										<div class="col-md-12 margin_row" style="margin-bottom: 10px;">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Amount</label>
													<div class="col-md-8 col-xs-11">
														<input id="salary_component_amount" name="salary_component_amount" type="text" class="form-control" title="Amount" placeholder="Amount" value="<?php if($mode=='Edit'){ echo $rel['salary_component_amount'];} else { echo '0.00'; } ?>">
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<p>5. Direct Amount can also be entered based on Condition. See example 3</p>
											</div>
										</div>
										<div class="col-md-12 margin_row" style="margin-bottom: 10px;">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label"></label>
													<div class="col-md-8 col-xs-11">
														<input type="checkbox" name="salary_component_amount_flag" id="salary_component_amount_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['salary_component_amount_flag'] : 'No' ?>" <?php if($rel['salary_component_amount_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label"> Amount based on formula</span>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<h4><b>Examples</b></h4>
											</div>
										</div>
										<div class="col-md-12 margin_row" style="margin-bottom: 10px;" >
											<div class="col-md-6">
												<div class="form-group" id="formula_div" style="display: none;">
													<label class="col-md-4 control-label">Formula</label>
													<div class="col-md-8 col-xs-11">
														<textarea style="border: 1px solid #ccc;" id="salary_component_amount_formula" name="salary_component_amount_formula" placeholder="Formula" rows="8" cols="72"><?php if($mode=='Edit') { echo $rel['salary_component_amount_formula']; } ?></textarea>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<p>1. Calculating Basic Salary based on base , <b>Condition: base < 10000</b> , <b>Formula: base * .2</b></p>
												<p>2. Calculating HRA based on Basic SalaryBS , <b>Condition: BS > 2000</b> , <b>Formula: BS * .1</b></p>
												<p>3. Calculating TDS based on Employment Type <b>employment_type</b> , <b>Condition: employment_type=="Intern"</b> , <b>Amount: 1000</b></p>
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
										</div><br><br>
										<div class="col-md-12 margin_row text-center">
											<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
											<a href="<?= ROOT . HRMS_ROOT . 'payroll_salary_component_list' ?>" type="button" class="btn btn-danger">Cancel</a>
										</div>
									</div>
								</section>
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
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/payroll_salary_component.js?<?= time() ?>"></script>
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
			var fexiblecheckboxChecked = $("#is_fexible_benefit_flag").is(":checked");
			if(fexiblecheckboxChecked){
				$(".flexible").css("display","block");
				$("#condition_formula").css("display","none");
			}else{
				$(".flexible").css("display","none");
				$("#condition_formula").css("display","block");
			}
			var statisticalcomponentChecked = $("#statistical_component_flag").is(":checked");
			if(statisticalcomponentChecked){
				$(".hide_flexible").css("display","none");
			}else{
				$(".hide_flexible").css("display","block");
			}
			var checkboxChecked = $("#salary_component_amount_flag").is(":checked");
			if(checkboxChecked){
				$("#formula_div").css("display","block");
			}else{
				$("#formula_div").css("display","none");
			}
			var currentOption = $("#salary_component_type option:selected").val();
			if(currentOption == 0){
				$("#component_type_main").css("display","none");
				$("#is_tax_earning").css("display","block");
				$("#is_tax_deduction").css("display","none");
				$("#deduct_fullamount_taxable").css("display","block");
				$("#variable_based_taxable").css("display","none");
				$("#exempted_from_income").css("display","none");
			}else{
				$("#component_type_main").css("display","block");
				$("#is_tax_earning").css("display","none");
				$("#is_tax_deduction").css("display","block");
				$("#deduct_fullamount_taxable").css("display","none");
				$("#variable_based_taxable").css("display","block");
				$("#exempted_from_income").css("display","block");
			}
		<?php } ?>
      	$(document).ready(function(){
			$(document).on("change","#salary_component_type", function(){
				var currentOption = $(this).val();
				if(currentOption == 0){
					$("#component_type_main").css("display","none");
					$("#is_tax_earning").css("display","block");
					$("#is_tax_deduction").css("display","none");
					$("#deduct_fullamount_taxable").css("display","block");
					$("#variable_based_taxable").css("display","none");
					$("#exempted_from_income").css("display","none");
				}else{
					$("#component_type_main").css("display","block");
					$("#is_tax_earning").css("display","none");
					$("#is_tax_deduction").css("display","block");
					$("#deduct_fullamount_taxable").css("display","none");
					$("#variable_based_taxable").css("display","block");
					$("#exempted_from_income").css("display","block");
				}
			});
			$(document).on("click","#depend_on_payment_day_flag", function(){
				if($(this).is(":checked")){
					$("#depend_on_payment_day_flag").val('Yes');
				}else{
					$("#depend_on_payment_day_flag").val('No');
				}
			});
			$(document).on("click","#is_tax_applicable_flag", function(){
				if($(this).is(":checked")){
					$("#is_tax_applicable_flag").val('Yes');
				}else{
					$("#is_tax_applicable_flag").val('No');
				}
			});
			$(document).on("click","#deduct_fullamount_selected_payroll_flag", function(){
				if($(this).is(":checked")){
					$("#deduct_fullamount_selected_payroll_flag").val('Yes');
				}else{
					$("#deduct_fullamount_selected_payroll_flag").val('No');
				}
			});
			$(document).on("click","#nearest_interger_flag", function(){
				if($(this).is(":checked")){
					$("#nearest_interger_flag").val('Yes');
				}else{
					$("#nearest_interger_flag").val('No');
				}
			});
			$(document).on("click","#statistical_component_flag", function(){
				if($(this).is(":checked")){
					$("#statistical_component_flag").val('Yes');
					$(".hide_flexible").css("display","none");
				}else{
					$("#statistical_component_flag").val('No');
					$(".hide_flexible").css("display","block");
				}
			});
			$(document).on("click","#do_not_include_total_flag", function(){
				if($(this).is(":checked")){
					$("#do_not_include_total_flag").val('Yes');
				}else{
					$("#do_not_include_total_flag").val('No');
				}
			});
			$(document).on("click","#salary_disable_flag", function(){
				if($(this).is(":checked")){
					$("#salary_disable_flag").val('Yes');
				}else{
					$("#salary_disable_flag").val('No');
				}
			});
      		$(document).on("click","#is_fexible_benefit_flag", function(){
				if($(this).is(":checked")){
					$("#is_fexible_benefit_flag").val('Yes');
					$(".flexible").css("display","block");
					$("#condition_formula").css("display","none");
				}else{
					$("#is_fexible_benefit_flag").val('No');
					$(".flexible").css("display","none");
					$("#condition_formula").css("display","block");
				}
			});
			$(document).on("click","#pay_against_benefit_claim_flag", function(){
				if($(this).is(":checked")){
					$("#pay_against_benefit_claim_flag").val('Yes');
				}else{
					$("#pay_against_benefit_claim_flag").val('No');
				}
			});
			$(document).on("click","#only_tax_impect_flag", function(){
				if($(this).is(":checked")){
					$("#only_tax_impect_flag").val('Yes');
				}else{
					$("#only_tax_impect_flag").val('No');
				}
			});
			$(document).on("click","#create_separate_payment_entry_flag", function(){
				if($(this).is(":checked")){
					$("#create_separate_payment_entry_flag").val('Yes');
				}else{
					$("#create_separate_payment_entry_flag").val('No');
				}
			});
			$(document).on("click","#salary_component_amount_flag", function(){
				if($(this).is(":checked")){
					$("#salary_component_amount_flag").val('Yes');
					$("#formula_div").css("display","block");
				}else{
					$("#salary_component_amount_flag").val('No');
					$("#formula_div").css("display","none");
				}
			});
      	});
	</script>
</body>
</html>