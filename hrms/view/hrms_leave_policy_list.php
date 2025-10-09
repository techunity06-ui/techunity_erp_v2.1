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
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
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
								<h3>New Leave Policy</h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . HRMS_ROOT . 'hr_dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li class="active">Leave Policy</li>
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
								New Leave Policy
							</header>
							<div class="panel-body">
								<form role="form" id="leave_policy_add" action="javascript:;" method="post" name="leave_policy_add">
									<div class="form-group">
										<label for="leave_type_id">Leave Type</label>
										<select id="leave_type_id" class="select2" name="leave_type_id" required>
											<option selected disabled value="">SELECT LEAVE TYPE</option>
											<?php
											$query = $dbcon->query("SELECT `id`,`leave_type_name` FROM `hrms_leave_type` WHERE `company_id` = $companyID and `status` = 0 order by leave_type_name ");
											while ($r = $query->fetch_assoc()) {
												echo '<option value="' . $r['id'] . '">' . $r['leave_type_name'] . '</option>';
											}
											?>
										</select>
									</div>

									<div class="form-group">
										<label for="catalog_name">Annual Allocation</label>
										<input type="text" class="form-control" id="annual_allocation" name="annual_allocation" placeholder="Enter Annual Allocation" value="00.00"/>
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
								Leave Policy List
							</header>
							<div class="panel-body">
								<div class="adv-table">
									<table class="display table table-bordered table-striped" id="dynamic-table">
										<thead>
											<tr>
												<th>Sr. NO.</th>
												<th>Company Name</th>
												<th>Leave Type Name</th>
												<th>Annual Allocation</th>
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
	<div class="modal colored-header info" id="ModalEditLeavePolicy" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Edit Leave Policy</h3>
				</div>
				<div class="modal-body form">
					<form id="FormEditLeavePolicy" role="form" method="post" novalidate>
						<div class="form-group">
							<label for="leave_type_id">Leave Type</label>
							<select id="edit_leave_type_id" class="select2" name="edit_leave_type_id" required>
								<option selected disabled value="">SELECT LEAVE TYPE</option>
								<?php
								$query = $dbcon->query("SELECT `id`,`leave_type_name` FROM `hrms_leave_type` WHERE `company_id` = $companyID and `status` = 0 order by leave_type_name ");
								while ($r = $query->fetch_assoc()) {
									echo '<option value="' . $r['id'] . '">' . $r['leave_type_name'] . '</option>';
								}
								?>
							</select>
						</div>
						<div class="form-group">
							<label for="catalog_name">Annual Allocation</label>
							<input type="text" class="form-control" id="edit_annual_allocation" name="annual_allocation" placeholder="Enter Annual Allocation" />
						</div>
						<div class="form-group">
							<label class="control-label">Status</label>
							<select id="edit_status" class="select2" name="edit_status" required>
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
					<button class="btn btn-info btn-flat" type="submit">Update Leave Policy</button>
				</div>
				</form>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_leave_policy.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});
	</script>
</body>
</html>