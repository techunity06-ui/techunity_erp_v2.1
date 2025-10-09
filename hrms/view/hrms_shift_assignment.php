<?php
session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/hrms_common_functions.php");

$token = md5(rand(1000, 9999));
$_SESSION['token'] = $token;
$infopage = pathinfo(__FILE__);
$_SESSION['page'] = HRMS_ROOT . $infopage['filename'];
$userID = $_SESSION['user_id'];
$companyID = $_SESSION['company_id'];
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
								<h3>New Shift Assignment</h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . HRMS_ROOT . 'hr_dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li class="active">Shift Assignment</li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>
				</div>
				<div class="row">
					<div class="col-sm-3">
						<section class="panel">
							<header class="panel-heading">
								New Shift Assignment
							</header>
							<div class="panel-body">
								<form role="form" id="shift_assignment_add" action="javascript:;" method="post" name="shift_assignment_add">
									<div class="form-group">
										<label for="catalog_name">Employee</label>
										<select id="employee_id" class="select2" name="employee_id" required>
											<option selected disabled value="">SELECT EMPLOYEE</option>
											<?php
												$query = $dbcon->query("SELECT `l_id`,`l_name` FROM `tbl_ledger` WHERE `company_id` = $companyID and `l_status` = 0 and `l_group` = '58' order by l_name ");
												while ($r = $query->fetch_assoc()) {
													echo '<option value="' . $r['l_id'] . '">' . $r['l_name'] . '</option>';
												}
											?>
										</select>
									</div>

									<div class="form-group">
										<label for="catalog_name">Shift Type</label>
										<select id="shift_type_id" class="select2" name="shift_type_id" required>
											<option selected disabled value="">SELECT SHIFT TYPE</option>
											<?php
											$query = $dbcon->query("SELECT `id`, `shift_type_name` FROM `hrms_shift_type` WHERE `company_id` = $companyID and `status` = 0 order by shift_type_name ");
											while ($r = $query->fetch_assoc()) {
												echo '<option value="' . $r['id'] . '">' . $r['shift_type_name'] . '</option>';
											}
											?>
										</select>
									</div>

									<div class="form-group">
										<label for="catalog_name">Shift Assignment Date</label>
										<input type="text" class="form-control" id="shift_assignment_date" name="shift_assignment_date" placeholder="Shift Assignment Date" />
									</div>


									<div class="form-group">
										<label class="control-label">Status</label>
										<select id="status" class="select2" name="status" required>
											<option selected disabled value="">SELECT STATUS</option>
											<option value="0">Active</option>
											<option value="1">InActive</option>
										</select>
									</div>
									<input type='hidden' name='mode' id='mode' value='add' />
									<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />
									<button type="submit" class="btn btn-info">Submit</button>
								</form>

							</div>
						</section>
					</div>
					<div class="col-sm-9">
						<section class="panel">
							<header class="panel-heading">
								Shift Assignment List
							</header>
							<div class="panel-body">
								<div class="adv-table">
									<table class="display table table-bordered table-striped" id="dynamic-table">
										<thead>
											<tr>
												<th>Sr. NO.</th>
												<th>Company Name</th>
												<th>Employee Name</th>
												<th>Shift Type Name</th>
												<th>Shift Assignment Date</th>
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
	<div class="modal colored-header info" id="ModalEditShiftAssignment" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Edit Shift Assignment</h3>
				</div>
				<div class="modal-body form">
					<form id="FormEditShiftAssignment" role="form" method="post" novalidate>
						<div class="form-group">
							<label class="control-label">Employee</label>
							<select id="edit_employee_id" class="select2" name="edit_employee_id" required>
								<option selected disabled value="">SELECT EMPLOYEE</option>
								<?php
								$query = $dbcon->query("SELECT `l_id`,`l_name` FROM `tbl_ledger` WHERE `l_status` = 0 and `l_group` = '58' order by l_name ");
								while ($r = $query->fetch_assoc()) {
									echo '<option value="' . $r['l_id'] . '">' . $r['l_name'] . '</option>';
								}
								?>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Shift Type</label>
							<select id="edit_shift_type_id" class="select2" name="edit_shift_type_id" required>
								<option selected disabled value="">SELECT SHIFT TYPE</option>
								<?php
								$query = $dbcon->query("SELECT `id`,`shift_type_name` FROM `hrms_shift_type` WHERE `status` = 0 order by shift_type_name ");
								while ($r = $query->fetch_assoc()) {
									echo '<option value="' . $r['id'] . '">' . $r['shift_type_name'] . '</option>';
								}
								?>
							</select>
						</div>
						<div class="form-group">
							<label for="catalog_name">Shift Assignment Date</label>
							<input type="text" class="form-control" id="edit_shift_assignment_date" name="shift_assignment_date" placeholder="Shift Assignment Date" />
						</div>

						<div class="form-group">
							<label class="control-label">Status</label>
							<select id="edit_status" class="select2" name="status" required>
								<option selected disabled value="">SELECT STATUS</option>
								<option value="0">Active</option>
								<option value="1">InActive</option>
							</select>
						</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
					<input type="hidden" name="edit_id" id="edit_id" value="" />
					<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
					<button class="btn btn-info btn-flat" type="submit">Update Shift Assignment</button>
				</div>
				</form>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_shift_assignment.js"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});

		$('#shift_assignment_date').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});

		$('#edit_shift_assignment_date').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});
	</script>
</body>
</html>