<?php
session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/payroll_common_functions.php");
include_once("../../include/function_database_query.php");

$form = "Payroll Salary Structure Assignment List";
$mode="Add";
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER['REQUEST_URI'], "payroll_salary_structure_assignment_edit")==true) {
	$mode="Edit";
	$payrollsalarystructureassignmentID = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from payroll_salary_structure_assignment where id=$payrollsalarystructureassignmentID and company_id = $companyID".check_user('payrollsalarystructureassignment');
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	if($rel){
		
	}else{
		header("Location: " . DOMAIN . HRMS_ROOT . "payroll_salary_structure_assignment_list");
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
									<li><a href="<?= ROOT . HRMS_ROOT . 'payroll_salary_structure_assignment_list' ?>"><?= $form ?></a></li>
								</ul>
							</div>
						</section>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
					<form class="form-horizontal" role="form" id="payroll_salary_structure_assignment_add" action="javascript:;" method="post" name="payroll_salary_structure_assignment_add">
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
																$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='PAYROLL SALARY STRUCTURE ASSIG' and company_id = $companyID and `type_id` = '16' order by invoicetype_id ");
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
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Salary Structure</label>
														<div class="col-md-8 col-xs-11">
															<select id="salary_structure_id" class="select2" name="salary_structure_id" required>
																<option selected disabled value="">SELECT SALARY STRUCTURE</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`salary_structure_name` FROM `payroll_salary_structure` WHERE `status` = 0 and company_id = $companyID order by id");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['salary_structure_id'] == $r['id']){
																			$salarystructureIDS = 'selected';
																		}else{
																			$salarystructureIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$salarystructureIDS.'>' . $r['salary_structure_name'] . '</option>';
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
														<label class="col-md-4 control-label">Assignment From Date*</label>
														<div class="col-md-8 col-xs-11">
															<input id="assignment_from_date" name="assignment_from_date" type="text" class="form-control default-date-picker required valid" title="Assignment From Date" placeholder="Assignment From Date" value="<?php if($mode=='Edit'){ echo $rel['assignment_from_date']; } ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Income Tax Slab</label>
														<div class="col-md-8 col-xs-11">
															<select id="income_tax_slab_id" class="select2" name="income_tax_slab_id">
																<option selected disabled value="">SELECT INCOME TAX SLAB</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`income_tax_slab_name` FROM `payroll_income_tax_slab` WHERE `status` = 0 and company_id = $companyID order by id");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['income_tax_slab_id'] == $r['id']){
																			$incometaxslabIDS = 'selected';
																		}else{
																			$incometaxslabIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$incometaxslabIDS.'>' . $r['income_tax_slab_name'] . '</option>';
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
														<label class="col-md-4 control-label">Base</label>
														<div class="col-md-8 col-xs-11">
															<input id="salary_structure_assignment_base" name="salary_structure_assignment_base" type="text" class="form-control valid" title="Salary Structure Assignment Base" placeholder="Salary Structure Assignment Base" value="<?php if($mode=='Edit'){ echo $rel['salary_structure_assignment_base']; } ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Variable</label>
														<div class="col-md-8 col-xs-11">
															<input id="salary_structure_assignment_variable" name="salary_structure_assignment_variable" type="text" class="form-control valid" title="Salary Structure Assignment Variable" placeholder="Salary Structure Assignment Variable" value="<?php if($mode=='Edit'){ echo $rel['salary_structure_assignment_variable']; } ?>">
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
											<div class="col-md-12 margin_row text-center">
												<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
												<a href="<?= ROOT . HRMS_ROOT . 'payroll_salary_structure_assignment_list' ?>" type="button" class="btn btn-danger">Cancel</a>
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
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/payroll_salary_structure_assignment.js?<?= time() ?>"></script>
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
	</script>
</body>
</html>