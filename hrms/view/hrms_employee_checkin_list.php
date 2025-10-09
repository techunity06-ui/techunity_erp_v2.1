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
<style type="text/css">
.checkbox_label{ position: absolute !important; overflow: visible; }
.timepicker-hour, .timepicker-minute, .timepicker-second{
	margin-left: 12px !important;
}	
</style>
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
								<h3>New Employee CheckIn</h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . HRMS_ROOT . 'hr_dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li class="active">Employee CheckIn</li>
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
								New Employee CheckIn
							</header>
							<div class="panel-body">
								<form role="form" id="employee_checkin_add" action="javascript:;" method="post" name="employee_checkin_add">
									<div class="form-group">
										<label for="catalog_name">Employee</label>
										<select id="employee_id" class="select2" name="employee_id" required>
											<option selected disabled value="">SELECT EMPLOYEE</option>
											<?php
												$query = $dbcon->query("SELECT `l_id`,`l_name` FROM `tbl_ledger` WHERE `l_status` = 0 and company_id = $companyID and `l_group` = '58' order by l_name ");
												while ($r = $query->fetch_assoc()) {
													echo '<option value="' . $r['l_id'] . '">' . $r['l_name'] . '</option>';
												}
											?>
										</select>
									</div>

									<div class="form-group">
										<label class="control-label">Log Type</label>
										<select id="log_type" class="select2" name="log_type" required>
											<option selected disabled value="">SELECT LOG TYPE</option>
											<option value="0">IN</option>
											<option value="1">OUT</option>
										</select>
									</div>

									<div class="form-group">
										<label for="catalog_name">Log DateTime</label>
										<input type="text" class="form-control" id="log_time" name="log_time" placeholder="Select Log DateTime"/>
									</div>

									<div class="form-group">
										<label for="catalog_name">Location / Device ID</label>
										<input type="text" class="form-control" id="location_device_detail" name="location_device_detail" placeholder="Enter Location / Device Detail"/>
									</div>

									<div class="form-group">
										<input type="checkbox" name="skip_auto_attendance_flag" id="skip_auto_attendance_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['skip_auto_attendance_flag'] : 'No' ?>" <?php if($rel['skip_auto_attendance_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label"> Skip Auto Attendance</span>
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
								Employee CheckIn List
							</header>
							<div class="panel-body">
								<div class="adv-table">
									<table class="display table table-bordered table-striped" id="dynamic-table">
										<thead>
											<tr>
												<th>Sr. NO.</th>
												<th>Company Name</th>
												<th>Employee Name</th>
												<th>Log Type</th>
												<th>Log DateTime</th>
												<th>Location / Device ID</th>
												<th>Skip Auto Flag</th>
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
	<div class="modal colored-header info" id="ModalEditEmployeeCheckIn" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Edit Employee CheckIn</h3>
				</div>
				<div class="modal-body form">
					<form id="FormEditEmployeeCheckIn" role="form" method="post" novalidate>
						<div class="form-group">
							<label for="edit_employee_id">Employee</label>
							<select id="edit_employee_id" class="select2" name="edit_employee_id" required>
								<option selected disabled value="">SELECT EMPLOYEE</option>
								<?php
									$query = $dbcon->query("SELECT `l_id`,`l_name` FROM `tbl_ledger` WHERE `l_status` = 0 and company_id = $companyID and `l_group` = '58' order by l_name ");
									while ($r = $query->fetch_assoc()) {
										echo '<option value="' . $r['l_id'] . '">' . $r['l_name'] . '</option>';
									}
								?>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Log Type</label>
							<select id="edit_log_type" class="select2" name="edit_log_type" required>
								<option selected disabled value="">SELECT LOG TYPE</option>
								<option value="0">IN</option>
								<option value="1">OUT</option>
							</select>
						</div>

						<div class="form-group">
							<label for="catalog_name">Log DateTime</label>
							<input type="text" class="form-control" id="edit_log_time" name="edit_log_time" placeholder="Select Log DateTime"/>
						</div>

						<div class="form-group">
							<label for="catalog_name">Location / Device ID</label>
							<input type="text" class="form-control" id="edit_location_device_detail" name="edit_location_device_detail" placeholder="Enter Location / Device Detail"/>
						</div>

						<div class="form-group">
							<input type="checkbox" name="edit_skip_auto_attendance_flag" id="edit_skip_auto_attendance_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['skip_auto_attendance_flag'] : 'No' ?>" <?php if($rel['skip_auto_attendance_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label"> Skip Auto Attendance</span>
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
					<button class="btn btn-info btn-flat" type="submit">Update Employee CheckIn</button>
				</div>
				</form>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_employee_checkin.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});

		$('#log_time').datetimepicker({
			 format:'DD-MM-YYYY HH:mm:ss',
			 keepOpen: false
		});
		$('#log_time').each(function () {
	        $(this).on('dp.change', function (ev) {
	           $(this).data('DateTimePicker').hide();
	         });
	    });

		$('#edit_log_time').datetimepicker({
			 format:'DD-MM-YYYY HH:mm:ss',
			 keepOpen: false
		});
		$('#edit_log_time').each(function () {
	        $(this).on('dp.change', function (ev) {
	           $(this).data('DateTimePicker').hide();
	         });
	     });

		$(document).ready(function(){
      		$(document).on("click","#skip_auto_attendance_flag", function(){
				if($(this).is(":checked")){
					$("#skip_auto_attendance_flag").val('Yes');
				}else{
					$("#skip_auto_attendance_flag").val('No');
				}
			});

			$(document).on("click","#edit_skip_auto_attendance_flag", function(){
				if($(this).is(":checked")){
					$("#edit_skip_auto_attendance_flag").val('Yes');
				}else{
					$("#edit_skip_auto_attendance_flag").val('No');
				}
			});
      	});
	</script>
</body>
</html>