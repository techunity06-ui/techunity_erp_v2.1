<?php
	session_start();
	include('../include/urlfile.php');
	// include('../../include/common_function/common_function.php');
	// $path = '../../';
	$incPath = '../../include/';
	

	// error_reporting(E_ALL);
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form = "Complaint";
	$infopage = pathinfo(__FILE__);
	$_SESSION['page'] = $infopage['filename'];
	if (empty($_SESSION['start'])) {
		$start = date('1-m-Y');
		$end = date("d-m-Y");
	} else {
		$start = $_SESSION['start'];
		$end = $_SESSION['end'];
	}
	$userid = $_SESSION['user_id'];
	$emp_id = getEmployeeIdUser($dbcon, $userid);
	$id = $_REQUEST['id'];
	$type = isset($_GET['type']) ? $_GET['type'] : '';
	if ($type) {
		$type_qry = "select * from tbl_followup_status where f_id=" . $type;
		$type_rel = mysqli_fetch_assoc($dbcon->query($type_qry));
		$head_name = $type_rel['f_status_name'];
	}

	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		COMPLAINT_SLUG_CREATE
	]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>COMPLAINT LIST</title>
	<?php include_once('../../include/include_css_file.php'); ?>
	<style type="text/css">
		.datepicker td.disabled.day {
			color: #ccc;
		}

		.datepicker td.today {
			background-color: #ffdb99;
			border-color: #ffb733;
		}
	</style>
</head>

<body>
	<section id="container" class="sidebar-closed">
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
								<h3>
									<!-- <= $head_name ?>  -->
									<?= $form ?> List</h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?= ROOT . SERVICE_ROOT . 'complaint_list' ?>"><?= $form ?> list</a></li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>
				</div>
				<!--state overview start-->
				<div class="row">
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
								<div class="col-md-2">
									<div class="form-group">
										<label class="control-label">Start Date</label>
										<input type="text" id="start_date" class="form-control default-date-picker inline-block" value="<?php echo $start; ?>" onchange="load_complaint_datatable();" />
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label class="control-label">End Date</label>
										<input type="text" id="end_date" class="form-control default-date-picker inline-block" value="<?php echo $end; ?>" onchange="load_complaint_datatable();" />
									</div>
								</div>
								<input type="hidden" name="" id="follow_id" value="<?= isset($id) ? $id : '' ?>" />
								<input type="hidden" name="" id="f_type" value="<?= isset($type) ? $type : '' ?>" />

								<?phpif (!$type) { ?>
									<div class="col-md-2">
										<div class="form-group">
											<label class="control-label">Complaint Status</label>
											<select class="select2" id="fil_followup_status" name="fil_followup_status" onchange="load_complaint_datatable();">
												<option value="">ALL</option>
												<?=getAllStatus($dbcon,"");?>
											</select>
										</div>
									</div>
								<?php} ?>

								<div class="col-md-3">
									<div class="form-group">
										<label class="control-label">Complaint Type</label>
										<select class="select2" id="fil_followup_type" name="fil_followup_type" onchange="load_complaint_datatable();">
											<?=get_complaint_type($dbcon,"");?>
										</select>
									</div>
								</div>
								<?php 
								if ($_SESSION['user_type'] == '2' || $_SESSION['user_type'] == '5') {
								?>
									<div class="col-md-3">
										<div class="form-group">
											<label class="control-label">Employee</label>
											<select class="select2" name="emp_id" id="emp_id" title="Choose Employee" onchange="load_complaint_datatable ();">
												<option value="">--ALL Employee--</option>
												<?=getAllEmployee($dbcon,"");?>
											</select>
										</div>
									</div>

									<?php if ($_SESSION['user_type'] == '2') { ?>
										<!-- <div class="col-md-4">
												<php echo getBranchBox($dbcon, $_SESSION['branch_id'], '', false, true, 'load_complaint_datatable()'); ?>
											</div> -->
									<?php }
								}
								if (in_array(COMPLAINT_SLUG_CREATE, $bulkAccessArray)) { ?>
									<span class="tools pull-right">
										<a href="<?= ROOT . SERVICE_ROOT . 'complaint_add' ?>"><button class="btn btn-success btn-flat">Add <?= $form ?></button></a>
									</span>
								<?php } ?>
							</header>
							<div class="panel-body">
								<div class="adv-table">
									<table class="display table table-bordered table-striped" id="complaint-table">
										<thead>
											<tr>
												<th>Sr. NO.</th>
												<th>Complaint No</th>
												<th>Complaint Date</th>
												<th>Last Action Date</th>
												<th>Company Name</th>
												<th>City</th>
												<th>Machine Name</th>
												<th>Complaint Type</th>
												<th>Complaint Status</th>
												<th>Spair Part Status</th>
												<th>Employee Name</th>
												<th>Action</th>
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
		<?php
		include_once('../../include/footer.php');
		include_once('../../include/add_complain_status.php');
		include_once('../../include/view_complain_history.php');
		?>
		<!--footer end-->
	</section>

	<!-- //Amish Soni 03-09-2020 -->
	<!-- Modal -->
	<div class="modal colored-header info" id="ModalSortClose" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Sort Close Complain</h3>

				</div>
				<form role="form" id="FormSortClose" action="javascript:;" method="post" name="FormSortClose">
					<div class="modal-body form">
						<div class="form-group">
							<label for="remark">Remark</label>
							<textarea class="form-control" name='remark' id='remark' required=""></textarea>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="complaint_id" id="complaint_id" value="" />
						<input type="hidden" name="employee_id" id="employee_id" value="" />
						<input type="hidden" name="mode" value="sortclose_complaint" />
						<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
						<button class="btn btn-info btn-flat" id="sc_complain" type="submit">Submit</button>
					</div>
				</form>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<!-- //Amish Soni 07-09-2020 -->
	<!-- Modal -->
	<div class="modal colored-header info" id="ModalSortCloseSP" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Sort Close Spare Parts</h3>

				</div>
				<form role="form" id="FormSortCloseSP" action="javascript:;" method="post" name="FormSortCloseSP">
					<div class="modal-body form">
						<div class="form-group">
							<label for="remark">Remark</label>
							<textarea class="form-control" name='remarkSP' id='remarkSP' required=""></textarea>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="complaint_idSP" id="complaint_idSP" value="" />
						<input type="hidden" name="employee_idSP" id="employee_idSP" value="" />
						<input type="hidden" name="cust_idSP" id="cust_idSP" value="" />
						<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
						<button class="btn btn-info btn-flat" type="submit">Submit</button>
					</div>
				</form>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->


	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php'); ?>
	
	<script src="<?=ROOT.SERVICE_ROOT?>js/app/complaint.js?<?=time()?>"></script>

	<script type="text/javascript">
		$(".select2").select2({
			width: '100%'
		});
		$('.default-date-picker').datepicker({
			format: 'dd-mm-yyyy',
			todayHighlight: true,
			autoclose: true
		});

		$(function() {
			setTimeout(function() {
				$('#sidebar > ul').hide();
			}, 1000);
		});

		$('#start_date').datepicker()
			.on('changeDate', function(e) {
				var start_date = e.format(0, "dd-mm-yyyy");
				var end_date = $('#end_date').val();

				if (start_date == '') {
					var date = new Date();
					var month = date.getMonth() + 1;
					start_date = '01-' + month + '-' + date.getFullYear();
					$('#start_date').datepicker('setDate', start_date);
				}

				job_start_date = start_date.split('-');
				job_end_date = end_date.split('-');

				var new_start_date = new Date(job_start_date[2], job_start_date[1], job_start_date[0]);
				var new_end_date = new Date(job_end_date[2], job_end_date[1], job_end_date[0]);

				$('#end_date').datepicker('setStartDate', e.format(0, "dd-mm-yyyy"));

				if (end_date == '' || new_start_date > new_end_date) {
					$('#end_date').datepicker('setDate', e.format(0, "dd-mm-yyyy"));
				}
			});

		$('#end_date').datepicker()
			.on('changeDate', function(e) {
				var start_date = $('#start_date').val();
				var end_date = e.format(0, "dd-mm-yyyy");

				if (end_date == '') {
					$('#end_date').datepicker('setDate', start_date);
				}
			});
	</script>
</body>

</html>