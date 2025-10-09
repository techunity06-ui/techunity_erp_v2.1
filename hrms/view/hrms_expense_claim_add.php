<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	include_once("../../include/hrms_common_functions.php");

	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Hrms Expense Claim";
	$companyID = $_SESSION['company_id'];
	$usertype=$_SESSION['user_type'];
	if(strpos($_SERVER[REQUEST_URI], "hrms_expense_claim_edit")==false)
	{
		$mode="Add";
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	}
	else
	{
		$mode="Edit";
		$block_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select empexpense.* from hrms_emp_expense_claim as empexpense 
				left join tbl_company as comp on comp.company_id = empexpense.company_id
				left join hrms_emp_expenses as expense on expense.emp_exp_claim_id = empexpense.id
				left join hrms_emp_expense_adv_payments as expenseadv on expenseadv.emp_exp_claim_id = empexpense.id
				left join hrms_emp_expense_tax_charge as expensetax on expensetax.emp_exp_claim_id = empexpense.id
		 		where `empexpense`.`id` = $block_id and `empexpense`.`company_id` = $companyID";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../../include/include_css_file.php');?>
	</head>
	<style>
		.check_box_class{ position: absolute !important; overflow: visible !important; }	
		.checkbox_label{ margin-left: 12px; }
	</style>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once('../../include/include_top_menu.php');?>
			<?php include_once('../../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3> <?=$mode .' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?= ROOT . HRMS_ROOT . 'hr_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li ><a href="<?= ROOT . HRMS_ROOT . 'hrms_expense_claim_list'?>"><?=$form?> List</a></li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									New <?=$form?>
								</header>	
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="hrms_expense_claim_add" action="javascript:;" method="post" name="hrms_expense_claim_add">
										<div class="">
											<div class="col-md-12 margin_row">
								 				<?php if($mode == "Edit"){ ?>
								 					<div class="col-md-6">
														  <div class="form-group">
														  		<label class="col-md-3 control-label">Series</label>
														  		<div class="col-md-8 col-xs-11">
														  			<input type="text" class="form-control" id="series_edit_id" placeholder="series_id" value="<?php if($mode=='Edit'){ echo $rel['series_id'];} ?>" readonly />
														  			<input type="hidden" class="form-control" id="series_id" name="series_id" placeholder="series_id" value="<?php if($mode=='Edit'){ echo $rel['series_id'];} ?>" />
																</div>
														  </div>							 
													 </div>
								 				<?php } else { ?>
								 					<div class="col-md-6">
														  <div class="form-group">
														  		<label class="col-md-3 control-label">Series</label>
														  		<?php
														  		$series_id = '';
														  		$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='EMPLOYEE CLAIM' and company_id = $companyID and `type_id` = '16' order by invoicetype_id ");
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
														<label class="col-md-3 control-label">From Employee*</label>
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
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Expense Approver</label>
												  		<div class="col-md-8 col-xs-11">
															<select id="expense_approver_id" class="select2" name="expense_approver_id" required>
																<option selected disabled value="">SELECT EXPENSE APPROVER</option>
																<?php
																$query = $dbcon->query("SELECT `l_id`,`l_name` FROM `tbl_ledger` WHERE `l_status` = 0 and company_id = $companyID and `l_group` = '58' order by l_name");
																while ($r = $query->fetch_assoc()) {
																	if($rel['expense_approver_id'] == $r['l_id']){
																		$approveremployeeIDS = 'selected';
																	}else{
																		$approveremployeeIDS = '';
																	}
																	echo '<option value="' . $r['l_id'] . '" '.$approveremployeeIDS.'>' . $r['l_name'] . '</option>';
																}
																?>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
											 			<label class="col-md-3 control-label">Approval Status</label>
											 			<div class="col-md-8 col-xs-11">
															<select id="approve_status" class="select2" name="approve_status" required>
																<option selected disabled value="">SELECT APPROVAL STATUS</option>
																<option value="draft" <?php if($rel['approve_status'] == 'draft') { echo 'selected'; } ?>>Draft</option>
																<option value="approved" <?php if($rel['approve_status'] == 'approved') { echo 'selected'; } ?>>Approved</option>
																<option value="rejected" <?php if($rel['approve_status'] == 'rejected') { echo 'selected'; } ?>>Rejected</option>
															</select>
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12 margin_row">
												<div class="col-md-6"></div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label"></label>
														<div class="col-md-8 col-xs-12">
															<input type="checkbox" name="is_paid_flag" id="is_paid_flag" data-id="is_paid_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['is_paid_flag'] : 'No' ?>" <?php if($rel['is_paid_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">Is Paid </span>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>EXPENSES</h4>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="expense_claim_days" class="display table table12 table-striped table-bordered">
														<tr id="field">
															<th width="10%" class="text-center">Expense Date</th>
															<th width="10%" class="text-center">Expense Claim Type</th>
															<th width="15%" class="text-center">Expense Description</th>
															<th width="10%" class="text-center">Expense Amount</th>
															<th width="10%" class="text-center">Sanctioned Amount</th>
															<th width="8%" class="text-center">Action</th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td data-label="Expense Date" style="vertical-align:top;">
																<input type="text"  name="expense_date" title="Enter Expense Date" placeholder="Expense Date" id="expense_date" class="form-control default-date-picker" />
															</td>
															<td data-label="Expense Claim Type" style="vertical-align:top;">
																<select id="expense_claim_type_id" class="select2" name="expense_claim_type_id">
																	<option selected disabled value="">SELECT EXPENSE CLAIM TYPE</option>
																	<?php
																	$query = $dbcon->query("SELECT `id`,`expense_claim_name` FROM `hrms_expense_claim_type` WHERE `expense_claim_name` = 0 and company_id = $companyID order by id");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['expense_claim_type_id'] == $r['id']){
																			$expenseclaimIDS = 'selected';
																		}else{
																			$expenseclaimIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$expenseclaimIDS.'>' . $r['expense_claim_name'] . '</option>';
																	}
																	?>
																</select>
															</td>
															<td data-label="Expense Description" style="vertical-align:top;">
																<textarea style="border: 1px solid #ccc;" id="expense_description" name="expense_description" placeholder="Expense Description" rows="3" cols="65" ><?php if($mode=='Edit') { echo $rel['expense_description']; } ?></textarea>
															</td>
															<td data-label="Expense Amount" style="vertical-align:top;">
																<input type="text"  name="expense_amount" title="Enter Expense Amount" placeholder="Expense Amount" id="expense_amount" class="form-control" />
															</td>
															<td data-label="Sanctioned Amount" style="vertical-align:top;">
																<input type="text"  name="expense_sanctioned_amount" title="Enter Sanctioned Amount" placeholder="Sanctioned Amount" id="expense_sanctioned_amount" class="form-control" />
															</td>
															<td style="vertical-align:top;">
																<input type="button"  name="addexpenserow" id="addexpenserow" onClick="return add_expense_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>			
												</div>
											</div>
											<div id="hrms_expensedata"></div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>EXPENSES TAXES AND CHARGES</h4>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="expense_taxes_and_charges" class="display table table12 table-striped table-bordered">
														<tr id="field">
															<th width="20%" class="text-center">Account Head</th>
															<th width="20%" class="text-center">Rate</th>
															<th width="20%" class="text-center">Amount</th>
															<th width="20%" class="text-center">Total</th>
															<th width="8%" class="text-center">Action</th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td data-label="Account Head" style="vertical-align:top;">
																<select id="account_head_id" class="select2" name="account_head_id">
																	<option selected disabled value="">SELECT ACCOUNT HEAD</option>
																	<?php
																		$query = $dbcon->query("SELECT `id`,`expense_account_head_name` FROM `hrms_expense_account_head` WHERE `status` = 0 and company_id = $companyID order by id");
																		while ($r = $query->fetch_assoc()) {
																			if($rel['emp_exp_claim_id	'] == $r['id']){
																				$expclaimIDS = 'selected';
																			}else{
																				$expclaimIDS = '';
																			}
																			echo '<option value="' . $r['id'] . '" '.$expclaimIDS.'>' . $r['expense_account_head_name'] . '</option>';
																		}
																	?>
																</select>
															</td>
															<td data-label="Rate" style="vertical-align:top;">
																<input type="text"  name="exp_tax_rate" title="Enter Rate" placeholder="Enter Rate" id="exp_tax_rate" class="form-control" />
															</td>
															<td data-label="Amount" style="vertical-align:top;">
																<input type="text"  name="exp_tax_amount" title="Enter Amount" placeholder="Enter Amount" id="exp_tax_amount" class="form-control exp_tax_amount" />
															</td>
															<td data-label="Total" style="vertical-align:top;">
																<input type="text"  name="exp_tax_total" title="Enter Total" placeholder="Enter Total" id="exp_tax_total" class="form-control" value="0.00" readonly="" />
															</td>
															<td style="vertical-align:top;">
																<input type="button"  name="addexpensetaxchargerow" id="addexpensetaxchargerow" onClick="return add_expense_tax_charge_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>			
												</div>
											</div>
											<div id="hrms_expensetaxandchargesdata"></div>
											<div class="col-md-12 margin_row" id="expensetaxandcharge" style="display: none;">
												<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Total Taxes and Charges</label>
														<div class="col-md-8 col-xs-11">
															<input id="total_tax_charges_amount" name="total_tax_charges_amount" type="text" class="form-control" title="Total Taxes and Charges" placeholder="Total Taxes and Charges" readonly value="<?php if($mode=='Edit'){ echo $rel['total_tax_charges_amount'];}else{ echo '0.00';} ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Grand Total</label>
														<div class="col-md-8 col-xs-11">
															<input id="expense_grand_total" name="expense_grand_total" type="text" class="form-control" title="Grand Total" placeholder="Grand Total" readonly value="<?php if($mode=='Edit'){ echo $rel['expense_grand_total'];}else{ echo '0.00';} ?>">
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Posting Date</label>
														<div class="col-md-8 col-xs-11">
															<input id="posting_date" name="posting_date" type="text" class="form-control default-date-picker" title="Posting Date" placeholder="Posting Date" value="<?php if($mode=='Edit'){ echo $rel['posting_date']; } ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Task Detail</label>
														<div class="col-md-8 col-xs-11">
															<input id="task_detail" name="task_detail" type="text" class="form-control" title="Task Detail" placeholder="Task Detail" value="<?php if($mode=='Edit'){ echo $rel['task_detail']; } ?>">
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Remark</label>
														<div class="col-md-8 col-xs-11">
															<textarea style="border: 1px solid #ccc;" id="remark_description" name="remark_description" placeholder="Enter Remark" rows="5" cols="74"><?php if($mode=='Edit') { echo $rel['remark_description']; } ?></textarea>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>ACCOUNTING DETAILS</h4>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Clearance Date</label>
														<div class="col-md-8 col-xs-11">
															<input id="clearance_date" name="clearance_date" type="text" class="form-control default-date-picker" title="Clearance Date" placeholder="Clearance Date" value="<?php if($mode=='Edit'){ echo $rel['clearance_date']; } ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="payable_account_id" class="col-md-3 control-label">Payable Account</label>
													  	<div class="col-md-8 col-xs-11">
															<select class="select2" id="payable_account_id" name="payable_account_id">
																<option value="">SELECT PAYABLE ACCOUNT</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`payable_account_name` FROM `hrms_payable_account` WHERE `status` = 0 and company_id = $companyID order by id");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['payable_account_id'] == $r['id']){
																			$payableaccountIDS = 'selected';
																		}else{
																			$payableaccountIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$payableaccountIDS.'>' . $r['payable_account_name'] . '</option>';
																	}
																?>
															</select>	
													  	</div>  	
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row mode_of_payment" style="display: none;" >
												<div class="col-md-6">
													<div class="form-group">
														<label for="mode_of_payment_id" class="col-md-3 control-label">Mode Of Payment</label>
													  	<div class="col-md-8 col-xs-11">
															<select class="select2" id="mode_of_payment_id" name="mode_of_payment_id">
																<option value="">SELECT MODE OF PAYMENT</option>
																<?php
																	$query = $dbcon->query("SELECT `paymentmodeid`,`payment_mode` FROM `tbl_payment_mode` WHERE `status` = 0 order by paymentmodeid");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['mode_of_payment_id	'] == $r['paymentmodeid']){
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
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>ACCOUNTING DIMENSIONS</h4>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="project_id" class="col-md-3 control-label">Project</label>
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
														<label for="cost_center_id" class="col-md-3 control-label">Cost Center</label>
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
											<h4>ADVANCE PAYMENTS</h4>
											<h6>Advances</h6>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="expense_advance_payments" class="display table table12 table-striped table-bordered">
														<tr id="field">
															<th width="15%" class="text-center">Employee Advance</th>
															<th width="15%" class="text-center">Posting Date</th>
															<th width="15%" class="text-center">Advance Paid</th>
															<th width="15%" class="text-center">Unclaimed Amount</th>
															<th width="15%" class="text-center">Allocated Amount</th>
															<th width="8%" class="text-center">Action</th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td data-label="Account Head" style="vertical-align:top;">
																<select id="emp_advance_id" class="select2" name="emp_advance_id" onchange="empAdvanceEmployee()">
																	<option selected disabled value="">SELECT EMPLOYEE ADVANCE</option>
																	<?php
																		$query = $dbcon->query("SELECT `employeeadvance`.`id`,`employeeadvance`.`employee_id`,`ledgerhead`.`l_name` FROM `hrms_employee_advance` as employeeadvance
																			left join tbl_ledger as ledgerhead on `ledgerhead`.`l_id` = `employeeadvance`.`employee_id`
																			WHERE `employeeadvance`.`status` = 0 and `employeeadvance`.`company_id` = $companyID order by `employeeadvance`.`id`");
																		while ($r = $query->fetch_assoc()) {
																			if($rel['emp_advance_id	'] == $r['id']){
																				$empadvanceIDS = 'selected';
																			}else{
																				$empadvanceIDS = '';
																			}
																			echo '<option value="' . $r['id'] . '" '.$empadvanceIDS.'>' . $r['l_name'] . '</option>';
																		}
																	?>
																</select>
															</td>
															<td data-label="Posting Date" style="vertical-align:top;">
																<input type="text"  name="advance_posting_date" title="Enter Posting Date" placeholder="Enter Posting Date" readonly id="advance_posting_date" class="form-control" />
															</td>
															<td data-label="Advance Paid" style="vertical-align:top;">
																<input type="text"  name="advance_paid_amount" title="Enter Advance Paid" placeholder="Enter Advance Paid" readonly id="advance_paid_amount" class="form-control" />
															</td>
															<td data-label="Unclaimed Amount" style="vertical-align:top;">
																<input type="text"  name="unclaim_amount" title="Enter Unclaimed Amount" placeholder="Enter Unclaimed Amount" id="unclaim_amount" class="form-control" readonly />
															</td>
															<td data-label="Allocated Amount" style="vertical-align:top;">
																<input type="text"  name="allocated_amount" title="Enter Allocated Amount" placeholder="Enter Allocated Amount" id="allocated_amount" class="form-control" />
															</td>
															<td style="vertical-align:top;">
																<input type="button"  name="addadvancepaymentrow" id="addadvancepaymentrow" onClick="return add_advance_payment_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>			
												</div>
											</div>
											<div id="hrms_advancepaymentdata"></div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6 typeled">
													<div class="form-group">
														<label for="status" class="col-md-4 control-label">Status*</label>
													  	<div class="col-md-8 col-xs-11">
															<select class="select2" id="status" name="status">
																<?php echo getStatusOptions($rel['status']); ?>
															</select>	
													  	</div>  	
													</div>							 
												</div>
											</div><br>
											<div class="col-md-12">
												<center>
													<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
													<a href="<?=ROOT.'hrms/hrms_expense_claim_list'?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>		
										</div>
										<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
										<input type='hidden' name='eid' id='eid' value='<?=$rel['id']?>' />
										<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />
									</form>
								</div>	
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once('../../include/footer.php');?>
		</section>
		<?php include_once('../../include/include_js_file.php');?>   
			<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_expense_claim.js?<?= time() ?>"></script>
		<script>
			//CKEDITOR.replace('quotation_condition');
			$(".select2").select2({
				width: '100%'
			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			$(document).ready(function(){
				$(document).on("click","#is_paid_flag", function(){
					if($(this).is(":checked")){
						$("#is_paid_flag").val('Yes');
						$(".mode_of_payment").css('display','block');
					}else{
						$("#is_paid_flag").val('No');
						$(".mode_of_payment").css('display','none');
					}
				});
				$(document).on("keyup","#exp_tax_rate", function(){
 					$("#exp_tax_amount").val('0.00');
 					$("#expensetaxandcharge").css("display","block");
				});
				$(document).on("keyup",".exp_tax_amount", function(){
					var exptaxamount = $("#exp_tax_amount").val();
					$("#exp_tax_total").val(exptaxamount);
					$("#total_tax_charges_amount").val(exptaxamount);
					$("#expense_grand_total").val(exptaxamount);
				});
			});
		</script>
		<?php 
			echo "<script>show_expense_data() </script>";
			echo "<script>show_expense_taxes_charges_data() </script>";
			echo "<script>show_expense_advance_payment_data() </script>";
		?>
	</body>
</html>
