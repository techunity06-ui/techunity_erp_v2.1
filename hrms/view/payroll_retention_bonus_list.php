<?php
session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/payroll_common_functions.php");
$token = md5(rand(1000, 9999));
$_SESSION['token'] = $token;
$infopage = pathinfo(__FILE__);
$_SESSION['page'] = HRMS_ROOT . $infopage['filename'];
$title = 'Payroll Retention Bonus';
$mode = 'Add';
$companyID = $_SESSION['company_id'];
$usertype=$_SESSION['user_type'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php'); ?>
</head>
<body>
	<section id="container">
		<?php include_once('../../include/include_top_menu.php'); ?>
		<!--sidebar start-->
		<?php include_once('../../include/left_menu.php'); ?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3>New <?php echo $title; ?></h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . HRMS_ROOT . 'payroll_dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li class="active"><?php echo $title; ?></li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>
				</div>
				<div class="row">
					<?php 
						$add_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'add',$dbcon); 
						if($add_btn_per != ""){
					?>
						<div class="col-sm-3">
							<section class="panel">
								<header class="panel-heading">
									New <?php echo $title; ?>
								</header>
								<div class="panel-body">
									<form role="form" id="payroll_retention_bonus_add" action="javascript:;" method="post" name="payroll_retention_bonus_add">
										<div class="form-group">
											<label class="control-label">Series</label>
											<?php
												$series_id = '';
												$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='PAYROLL RETENTION BONUS' and company_id = $companyID and `type_id` = '16' order by invoicetype_id ");
													while ($r = $query->fetch_assoc()) {
														$series_id = $r['format_value'] . $r['taxinvoice_start'] . $r['end_format_value'];
													}
											?>
											<input type="text" class="form-control" id="series_id" name="series_id" placeholder="series_id" value="<?php echo $series_id; ?>" readonly />
										</div>
										<div class="form-group">
											<label class="control-label">Employee*</label>
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
										<div class="form-group">
											<label for="bonus_payment_date">Bonus Payment Date*</label>
											<input type="text" class="form-control datepicker" id="bonus_payment_date" name="bonus_payment_date" placeholder="Bonus Payment Date" required/>
										</div>
										<div class="form-group">
											<label for="bonus_amount">Bonus Amount*</label>
											<input type="text" class="form-control" id="bonus_amount" name="bonus_amount" placeholder="Bonus Amount" required />
										</div>
										<div class="form-group">
											<label for="salary_component_id">Salary Component*</label>
											<select id="salary_component_id" class="select2" name="salary_component_id" required>
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
										<div class="form-group">
											<label for="status">Status*</label>
											<select class="select2" id="status" name="status">
												<?php echo getStatusOptions($rel['status']); ?>
											</select>	
										</div>
										<input type='hidden' name='mode' id='mode' value='add' />
										<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />
										<button type="submit" class="btn btn-info">Submit</button>
									</form>
								</div>
							</section>
						</div>
					<?php } ?>
					<?php if($add_btn_per != ""){ ?>
						<div class="col-sm-9">
					<?php }else { ?>
						<div class="col-sm-12">	
					<?php } ?>
						<section class="panel">
							<header class="panel-heading">
								<?php $title; ?> List
							</header>
							<div class="panel-body">
								<div class="adv-table">
									<table class="display table table-bordered table-striped" id="dynamic-table">
										<thead>
											<tr>
												<th>Sr. NO.</th>
												<th>Company Name</th>
												<th>Employee Name</th>
												<th>Component Name</th>
												<th>Bonus Payment Date</th>
												<th>Status</th>
												<th class="hidden-phone">Action</th>
											</tr>
										</thead>
										<tbody>
										</tbody>
									</table>
								</div>
							</div>
						</section>
					</div>
				</div>
				<!--state overview end-->
			</section>
		</section>
		<!--main content end-->
		<!--footer start-->
		<?php include_once('../../include/footer.php'); ?>
		<!--footer end-->
	</section>
	<!-- Modal -->
	<div class="modal colored-header info" id="ModalEditPayrollRetention" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Edit <?php $title; ?></h3>
				</div>
				<form id="FormEditPayrollRetention" role="form" method="post" novalidate>
					<div class="modal-body form">
							<div class="form-group">
								<label class="control-label">Series</label>
								<input type="text" class="form-control" id="edit_series_id" name="series_id" placeholder="series_id" value="<?php if($mode=='Edit'){ echo $rel['series_id'];} ?>" readonly />
							</div>
							<div class="form-group">
								<label class="control-label">Employee*</label>
								<select id="edit_employee_id" class="select2" name="employee_id" required>
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
							<div class="form-group">
								<label class="control-label">Bonus Payment Date*</label>
								<input type="text" class="form-control datepicker" id="edit_bonus_payment_date" name="bonus_payment_date" placeholder="Bonus Payment Date" value="<?php if($mode=='Edit'){ echo $rel['bonus_payment_date'];} ?>" required/>
							</div>
							<div class="form-group">
								<label class="control-label">Bonus Amount*</label>
								<input type="text" class="form-control" id="edit_bonus_amount" name="bonus_amount" placeholder="Bonus Amount" value="<?php if($mode=='Edit'){ echo $rel['bonus_amount'];} ?>" required />
							</div>
							<div class="form-group">
								<label class="control-label">Salary Component*</label>
								<select id="edit_salary_component_id" class="select2" name="salary_component_id" required>
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
							<div class="form-group">
								<label for="status">Status*</label>
								<select class="select2" id="edit_status" name="status">
									<?php echo getStatusOptions($rel['status']); ?>
								</select>	
							</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
						<input type="hidden" name="edit_id" id="edit_id" value="" />
						<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
						<button class="btn btn-info btn-flat" type="submit">Update <?php $title; ?></button>
					</div>
				</form>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/payroll_retention_bonus.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});
		$(".datepicker").datepicker({
	        format: "dd-mm-yyyy",
	        // startDate: "1d",
	        autoclose: true,
	        todayHighlight: true
	    });
	</script>
</body>
</html>