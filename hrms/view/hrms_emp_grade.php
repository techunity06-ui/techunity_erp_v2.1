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
$title = 'Employee Grade';
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
									<li><a href="<?= ROOT . HRMS_ROOT . 'hr_dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li class="active"><?php echo $title; ?></li>
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
								New <?php echo $title; ?>
							</header>
							<div class="panel-body">
								<form role="form" id="emp_grade_add" action="javascript:;" method="post" name="emp_grade_add">
									<div class="form-group">
										<label for="employee_grade_name">Employee Grade Name*</label>
										<input type="text" class="form-control" id="employee_grade_name" name="employee_grade_name" placeholder="Employee Grade Name" />
									</div>
									<div class="form-group">
										<label for="leave_policy_id">Select Leave Policy*</label>
										<select class="select2" id="leave_policy_id" name="leave_policy_id">
											<?php echo get_leave_policy($dbcon);  ?>
										</select>
									</div>
									<div class="form-group">
										<label for="salary_structure_id">Select Salary Structure*</label>
										<select class="select2" id="salary_structure_id" name="salary_structure_id">
											<?php echo get_salary_structure($dbcon);  ?>
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
					<div class="col-sm-9">
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
												<th>Employee Grade Name</th>
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
	<div class="modal colored-header info" id="ModalEditEmpGrade" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Edit <?php $title; ?></h3>
				</div>
				<form id="FormEditEmpGrade" role="form" method="post" novalidate>
					<div class="modal-body form">
						<div class="form-group">
							<label for="employee_grade_name">Employee Grade Name*</label>
							<input type="text" class="form-control" id="edit_employee_grade_name" name="employee_grade_name" placeholder="Employee Grade Name" required="" />
						</div>
						<div class="form-group">
							<label for="leave_policy_id">Select Leave Policy*</label>
							<select class="select2" id="edit_leave_policy_id" name="leave_policy_id">
								<?php echo get_leave_policy($dbcon);  ?>
							</select>
						</div>
						<div class="form-group">
							<label for="salary_structure_id">Select Salary Structure*</label>
							<select class="select2" id="edit_salary_structure_id" name="salary_structure_id">
								<?php echo get_salary_structure($dbcon);  ?>
							</select>
						</div>
						<div class="form-group">
							<label for="control-label">Status*</label>
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
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_emp_grade.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});
	</script>
</body>
</html>