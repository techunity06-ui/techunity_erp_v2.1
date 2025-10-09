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
								<h3>New Leave Period</h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . HRMS_ROOT . 'hr_dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li class="active">Leave Period</li>
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
								New Leave Period
							</header>
							<div class="panel-body">
								<form role="form" id="leave_period_add" action="javascript:;" method="post" name="leave_period_add">
									<div class="form-group">
										<label for="holiday_list_id">Holiday List for Optional Leave</label>
										<select id="holiday_list_id" class="select2" name="holiday_list_id" required>
											<option selected disabled value="">SELECT HOLIDAY</option>
											<?php
											$query = $dbcon->query("SELECT `id`,`holiday_name` FROM `hrms_holiday_list` WHERE `company_id` = $companyID and `status` = 0 order by holiday_name ");
											while ($r = $query->fetch_assoc()) {
												echo '<option value="' . $r['id'] . '">' . $r['holiday_name'] . '</option>';
											}
											?>
										</select>
									</div>

									<div class="form-group">
										<label for="catalog_name">Leave Period From Date</label>
										<input type="text" class="form-control" id="leave_period_from_date" name="leave_period_from_date" placeholder="Select Leave Period From Date" />
									</div>

									<div class="form-group">
										<label for="catalog_name">Leave Period To Date</label>
										<input type="text" class="form-control" id="leave_period_to_date" name="leave_period_to_date" placeholder="Select Leave Period To Date" />
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
								Leave Period List
							</header>
							<div class="panel-body">
								<div class="adv-table">
									<table class="display table table-bordered table-striped" id="dynamic-table">
										<thead>
											<tr>
												<th>Sr. NO.</th>
												<th>Company Name</th>
												<th>Holiday List Name</th>
												<th>Leave Period From Date</th>
												<th>Leave Period To Date</th>
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
	<div class="modal colored-header info" id="ModalEditLeavePeriod" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Edit Leave Period</h3>
				</div>
				<div class="modal-body form">
					<form id="FormEditLeavePeriod" role="form" method="post" novalidate>
						<div class="form-group">
							<label class="control-label">Holiday List for Optional Leave</label>
							<select id="edit_holiday_list_id" class="select2" name="edit_holiday_list_id" required>
								<option selected disabled value="">SELECT HOLIDAY</option>
								<?php
								$query = $dbcon->query("SELECT `id`,`holiday_name` FROM `hrms_holiday_list` WHERE `company_id` = $companyID and `status` = 0 order by holiday_name ");
								while ($r = $query->fetch_assoc()) {
									echo '<option value="' . $r['id'] . '">' . $r['holiday_name'] . '</option>';
								}
								?>
							</select>
						</div>
						<div class="form-group">
							<label for="catalog_name">Leave Period From Date</label>
							<input type="text" class="form-control" id="edit_leave_period_from_date" name="leave_period_from_date" placeholder="Select Leave Period From Date" />
						</div>

						<div class="form-group">
							<label for="catalog_name">Leave Period To Date</label>
							<input type="text" class="form-control" id="edit_leave_period_to_date" name="leave_period_to_date" placeholder="Select Leave Period To Date" />
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
					<button class="btn btn-info btn-flat" type="submit">Update Leave Period</button>
				</div>
				</form>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_leave_period.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});

		$('#leave_period_from_date, #leave_period_to_date').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});

		$('#edit_leave_period_from_date, #edit_leave_period_to_date').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});

		$("#leave_period_to_date").change(function () {
		    var startDate = document.getElementById("leave_period_from_date").value;
		    var endDate = document.getElementById("leave_period_to_date").value;
		    if ((Date.parse(parseDate(endDate)) < Date.parse(parseDate(startDate)))) {
		        alert("Leave period to date should be greater than leave period from date");
		        document.getElementById("leave_period_to_date").value = "";
		    }
		});

		$("#edit_leave_period_to_date").change(function () {
		    var startDate = document.getElementById("edit_leave_period_from_date").value;
		    var endDate = document.getElementById("edit_leave_period_to_date").value;
		    if ((Date.parse(parseDate(endDate)) < Date.parse(parseDate(startDate)))) {
		        alert("Leave period to date should be greater than leave period from date");
		        document.getElementById("edit_leave_period_to_date").value = "";
		    }
		});
		function parseDate(str) {
          var mdy = str.split('-');
          return new Date(mdy[2], mdy[1] - 1, mdy[0]);
      	}
	</script>
</body>
</html>