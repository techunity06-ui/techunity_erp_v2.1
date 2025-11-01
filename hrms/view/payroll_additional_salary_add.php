<?php
session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/payroll_common_functions.php");
include_once("../../include/function_database_query.php");

$form = "Payroll Additional Salary List";
$mode="Add";
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER['REQUEST_URI'], "payroll_additional_salary_edit")==true) {
	$mode="Edit";
	$payrolladditionalsalaryID = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from payroll_additional_salary where id=$payrolladditionalsalaryID and company_id = $companyID".check_user('payrolladditionalsalary');
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	if($rel){
		
	}else{
		header("Location: " . DOMAIN . HRMS_ROOT . "payroll_additional_salary_list");
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
									<li><a href="<?= ROOT . HRMS_ROOT . 'payroll_additional_salary_list' ?>"><?= $form ?></a></li>
								</ul>
							</div>
						</section>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
					<form class="form-horizontal" role="form" id="payroll_additional_salary_add" action="javascript:;" method="post" name="payroll_additional_salary_add">
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
																$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='PAYROLL ADDITIONAL SALARY' and company_id = $companyID and `type_id` = '16' order by invoicetype_id ");
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
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Payroll Date*</label>
														<div class="col-md-8 col-xs-11">
															<input id="payroll_date" name="payroll_date" type="text" class="form-control default-date-picker required valid" title="Payroll Date" placeholder="Payroll Date" value="<?php if($mode=='Edit'){ echo $rel['payroll_date']; } ?>">
															<p>Date on which this component is applied</p>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label"></label>
														<div class="col-md-8 col-xs-12">
															<input type="checkbox" name="is_recurring_flag" id="is_recurring_flag" data-id="is_recurring_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['is_recurring_flag'] : 'No' ?>" <?php if($rel['is_recurring_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">Is Recurring</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Salary Component*</label>
														<div class="col-md-8 col-xs-11">
															<select id="salary_component_id" class="select2" name="salary_component_id" required onchange="getSalaryComponentData()">
																<option selected disabled value="">SELECT SALARY COMPONENT</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`salary_component_name` FROM `payroll_salary_component` WHERE `status` = 0 and company_id = $companyID order by id");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['salary_component_id'] == $r['id']){
																			$salarycomponentIDS = 'selected';
																		}else{
																			$salarycomponentIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$salarycomponentIDS.'>' . $r['salary_component_name'] . '</option>';
																	}
																?>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Salary Component Type</label>
														<div class="col-md-8 col-xs-11">
															<input id="salary_component_type" name="salary_structure_assignment_base" type="text" class="form-control valid" title="Salary Component Type" placeholder="Salary Component Type" disabled="" >
														</div>
													</div>
												</div>
											</div>	
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label"></label>
														<div class="col-md-8 col-xs-12">
															<input type="checkbox" name="overwrite_salary_structure_amount_flag" id="overwrite_salary_structure_amount_flag" data-id="overwrite_salary_structure_amount_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['overwrite_salary_structure_amount_flag'] : 'No' ?>" <?php if($rel['overwrite_salary_structure_amount_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">Overwrite Salary Structure Amount</span>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Payroll Amount*</label>
														<div class="col-md-8 col-xs-11">
															<input id="additional_salary_amount" name="additional_salary_amount" type="text" class="form-control valid" title="Additional Salary Amount" placeholder="Additional Salary Amount" value="<?php if($mode=='Edit'){ echo $rel['additional_salary_amount']; } ?>">
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label"></label>
														<div class="col-md-8 col-xs-12">
															<input type="checkbox" name="deduct_fulltax_on_selected_payroll_date_flag" id="deduct_fulltax_on_selected_payroll_date_flag" data-id="deduct_fulltax_on_selected_payroll_date_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['deduct_fulltax_on_selected_payroll_date_flag'] : 'No' ?>" <?php if($rel['deduct_fulltax_on_selected_payroll_date_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">Deduct Full Tax on Selected Payroll Date</span>
														</div>
													</div>
												</div>
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
												<a href="<?= ROOT . HRMS_ROOT . 'payroll_additional_salary_list' ?>" type="button" class="btn btn-danger">Cancel</a>
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
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/payroll_additional_salary.js?<?= time() ?>"></script>
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
	    $(document).ready(function(){
	    	$(document).on("click","#is_recurring_flag", function(){
				if($(this).is(":checked")){
					$("#is_recurring_flag").val('Yes');
				}else{
					$("#is_recurring_flag").val('No');
				}
			});
			$(document).on("click","#overwrite_salary_structure_amount_flag", function(){
				if($(this).is(":checked")){
					$("#overwrite_salary_structure_amount_flag").val('Yes');
				}else{
					$("#overwrite_salary_structure_amount_flag").val('No');
				}
			});
			$(document).on("click","#deduct_fulltax_on_selected_payroll_date_flag", function(){
				if($(this).is(":checked")){
					$("#deduct_fulltax_on_selected_payroll_date_flag").val('Yes');
				}else{
					$("#deduct_fulltax_on_selected_payroll_date_flag").val('No');
				}
			});
	    });
	</script>
	<?php if($mode == 'Edit'){ 
		echo "<script>getSalaryComponentData() </script>";
	} ?>
</body>
</html>