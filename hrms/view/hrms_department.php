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
								<h3>New Department</h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . HRMS_ROOT . 'hr_dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li class="active">Department</li>
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
								New Department
							</header>
							<div class="panel-body">
								<form role="form" id="department_add" action="javascript:;" method="post" name="department_add">
									<div class="form-group">
										<label for="department_name">Department Name*</label>
										<input type="text" class="form-control" id="department_name" name="department_name" placeholder="Department Name" />
									</div>
									<div class="form-group">
										<label for="parent_department_id">Parent Department</label>
										<select id="parent_department_id" class="select2" name="parent_department_id">
											<?php echo get_all_departments($dbcon ,'', '', true); ?>
										</select>
									</div>
									<div class="form-group">
										<label for="is_group">Is Grouped?</label>
										<input type="checkbox" class="" value="Yes" id="is_group" name="is_group" />
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
								Department List
							</header>
							<div class="panel-body">
								<div class="adv-table">
									<table class="display table table-bordered table-striped" id="dynamic-table">
										<thead>
											<tr>
												<th>Sr. NO.</th>
												<th>Company Name</th>
												<th>Department Name</th>
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
	<div class="modal colored-header info" id="ModalEditDepartment" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Edit Department</h3>
				</div>
				<div class="modal-body form">
					<form id="FormEditDepartment" role="form" method="post" novalidate>
					<div class="form-group">
						<label class="control-label">Department Name*</label>
						<input type="text" name="department_name" id="edit_department_name" class="form-control" required>
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
					<button class="btn btn-info btn-flat" type="submit">Update Department</button>
				</div>
				</form>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_department.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});
	</script>
</body>
</html>