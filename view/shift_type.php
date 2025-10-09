<?php

 session_start();
    include_once("../config/config.php");
    include_once("../config/session.php");

    include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
    include_once("../include/function_database_query.php");


$token = md5(rand(1000, 9999));
$_SESSION['token'] = $token;
$infopage = pathinfo(__FILE__);
$_SESSION['page'] = $infopage['filename'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../include/include_css_file.php'); ?>
</head>
<style type="text/css">
.timepicker-hour, .timepicker-minute{
	margin-left: 12px !important;
}	
</style>
<body>
	<section id="container">
		<?php include_once('../include/include_top_menu.php'); ?>
		<!--sidebar start-->
		<?php include_once('../include/left_menu.php'); ?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3>New Shift Type</h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT .'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li class="active">Shift Type</li>
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
								New Shift Type
							</header>
							<div class="panel-body">
								<form role="form" id="shift_type_add" action="javascript:;" method="post" name="shift_type_add">
									<div class="form-group">
										<label for="catalog_name">Shift Type Name</label>
										<input type="text" class="form-control" id="shift_type_name" name="shift_type_name" placeholder="Shift Type Name" required/>
									</div>

									<div class="form-group">
										<label for="catalog_name">Shift Start Time</label>
										<input type="text" class="form-control" id="shift_start_time" name="shift_start_time" placeholder="Select Shift Start Time" />
									</div>

									<div class="form-group">
										<label for="catalog_name">Shift End Time</label>
										<input type="text" class="form-control" id="shift_end_time" name="shift_end_time" placeholder="Select Shift End Time" />
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
								Shift Type List
							</header>
							<div class="panel-body">
								<div class="adv-table">
									<table class="display table table-bordered table-striped" id="dynamic-table">
										<thead>
											<tr>
												<th>Sr. NO.</th>
												<th>Shift Type Name</th>
												<th>Shift Start Time</th>
												<th>Shift End Time</th>
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
		<?php include_once('../include/footer.php'); ?>
		<!--footer end-->
	</section>
	<!-- Modal -->
	<div class="modal colored-header info" id="ModalEditShiftType" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Edit Shift Type</h3>
				</div>
				<div class="modal-body form">
					<form id="FormEditShiftType" role="form" method="post" novalidate>
						<div class="form-group">
							<label class="control-label">Shift Type Name</label>
							<input type="text" name="edit_shift_type_name" id="edit_shift_type_name" class="form-control" placeholder="Shift Type Name" required>
						</div>
						<div class="form-group">
							<label for="catalog_name">Shift Start Time</label>
							<input type="text" class="form-control" id="edit_shift_start_time" name="edit_shift_start_time" placeholder="Select Shift Start Time" />
						</div>

						<div class="form-group">
							<label for="catalog_name">Shift End Time</label>
							<input type="text" class="form-control" id="edit_shift_end_time" name="edit_shift_end_time" placeholder="Select Shift End Time" />
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
					<button class="btn btn-info btn-flat" type="submit">Update Shift Type</button>
				</div>
				</form>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php'); ?>
	<script src="<?= ROOT?>js/app/shift_type.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});

		function TimePickerCtrl($) {
		  var startTime = $('#shift_start_time').datetimepicker({
		  	//keepOpen: true,
		  	//ebug: true,
		    format: 'HH:mm'
		  });
		  
		  var endTime = $('#shift_end_time').datetimepicker({
		    format: 'HH:mm',
		    //minDate: startTime.data("DateTimePicker").date()
		  });
		  
		  function setMinDate() {
		    return endTime
		      .data("DateTimePicker").minDate(
		        startTime.data("DateTimePicker").date()
		      )
		    ;
		  }
		  
		  var bound = false;
		  function bindMinEndTimeToStartTime() {
		  
		    return bound || startTime.on('dp.change', setMinDate);
		  }
		  
		  endTime.on('dp.change', () => {
		    bindMinEndTimeToStartTime();
		    bound = true;
		    setMinDate();
		  });
		}

		$(document).ready(TimePickerCtrl);

		function TimePickerCtrlEdit($) {
		  var startTimeEdit = $('#edit_shift_start_time').datetimepicker({
		    format: 'HH:mm'
		  });
		  
		  var endTimeEdit = $('#edit_shift_end_time').datetimepicker({
		    format: 'HH:mm',
		    minDate: startTimeEdit.data("DateTimePicker").date()
		  });
		  
		  function setMinDateEdit() {
		    return endTimeEdit
		      .data("DateTimePicker").minDate(
		        startTimeEdit.data("DateTimePicker").date()
		      )
		    ;
		  }
		  
		  var bound = false;
		  function bindMinEndTimeToStartTime() {
		  
		    return bound || startTimeEdit.on('dp.change', setMinDateEdit);
		  }
		  
		  endTimeEdit.on('dp.change', () => {
		    bindMinEndTimeToStartTime();
		    bound = true;
		    setMinDateEdit();
		  });
		}

		$(document).ready(TimePickerCtrlEdit);
	</script>
</body>
</html>