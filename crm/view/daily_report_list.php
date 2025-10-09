<?php
session_start();
error_reporting(E_ALL);
include('../include/urlfile.php');
// $incPath = $path.'include/';
$user_id = $_SESSION['user_id'];
$form = "Daily report";
$infopage = pathinfo(__FILE__);
$_SESSION['page'] = 'crm/' . $infopage['filename'];
if (isset($_SESSION['summary_start_date']) && !empty($_SESSION['summary_start_date']) && isset($_SESSION['summary_end_date']) && !empty($_SESSION['summary_end_date'])) {
	$start_date = $_SESSION['summary_start_date'];
	$end_date = $_SESSION['summary_end_date'];
} else if (isset($_SESSION['start_date']) && !empty($_SESSION['start_date']) && isset($_SESSION['end_date']) && !empty($_SESSION['end_date'])) {
	$start_date = $_SESSION['start_date'];
	$end_date = $_SESSION['end_date'];
} else {
	$start_date = date("01-m-Y");
	$end_date = date("t-m-Y");
}
if (empty($_SESSION['start'])) {
	$start = date('1-m-Y');
	$end = date("d-m-Y");
} else {
	$start = $_SESSION['start'];
	$end = $_SESSION['end'];
}
$companyConfiguration = getCompanyConfiguration($dbcon);
$getspecialConfiguration = getspecialConfiguration($dbcon);

$companyConfiguration = getCompanyConfiguration($dbcon);
$d = date_create('date');
$date = date_format($d, "d/m/Y ");
$crm_user_type = $companyConfiguration['crm_user_type'];

if ($_SESSION['user_type'] == 2) {
	$user_ids = get_users_typewise($dbcon,""," AND user_type IN (".$crm_user_type.")", true);
	$user_id = check_user_chein($dbcon, $_SESSION['user_id'], 1);
} else {
	$user_id = check_user_chein($dbcon, $_SESSION['user_id'], 1);
	$user_ids = get_user_report($dbcon, $user_id, true);
}
// die($user_ids);

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<?php include_once('../../include/include_css_file.php'); ?>
	<style>
		.icons {
			width: 18%;
			float: left;
			margin: 10px 7px 10px;
			text-align: center;
			position: relative;

		}

		.icons12 {
			background-color: #fff;
			padding-top: 15px;
			border: 8px;
		}

		.icons p {
			text-align: center;
			font-size: 15px;
			font-weight: 600;
			padding-top: 5px;
			font-color: white
		}

		.icon1 fa {}

		.icon1.success {
			background-color: #5cb85c;
		}

		.icon1.primary {
			background-color: #0275d8;
		}

		.icon1.warning {
			background-color: #f0ad4e;
		}

		.icon1.info {
			background-color: #5bc0de;
		}

		.icon1.danger {
			background-color: #d9534f;
		}

		.icon1.terques {
			background-color: #6ccac9;
		}

		.icon1.yellow {
			background-color: #f8d347;
		}

		.icon1.pink {
			background-color: #E5649A;
		}

		.icon1.mustard {
			background-color: #F0BD23;
		}

		.icon1.success,
		.icon1.primary,
		.icon1.warning,
		.icon1.danger,
		.icon1.info,
		.icon1.terques,
		.icon1.yellow,
		.icon1.pink,
		.icon1.mustard {
			width: 150px;
			height: 140px;
			border-radius: 8px;
			text-align: center;
			margin: 0 auto
		}

		.icon1.success i,
		.icon1.primary i,
		.icon1.warning i,
		.icon1.danger i,
		.icon1.info i,
		.icon1.terques i,
		.icon1.yellow i,
		.icon1.pink i,
		.icon1.mustard i {
			text-align: center;
			color: #fff;
			padding-top: 27%;
			font-size: 37px;
		}

		@media (max-width:767px) {
			.icons {
				width: 265px;
				float: left;
				margin: 30px 4px 25px;
				position: relative;
			}

		}

		@media (min-width:768px) and (max-width:980px) {
			.icons12 {
				background-color: #fff;
				padding-top: 20px;
				padding-bottom: 20px;
				border-radius: 8px;
			}

			.icons {
				width: 17%;
				float: left;
				margin: 30px 4px 25px;
				text-align: center;
				position: relative;
			}

		}

		.icons .badge {
			position: absolute;
			right: 25px;
			top: 0px;
			z-index: 100;
		}
	</style>
	<title>Daily Report</title>

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
				<div class="col-lg-12">
					<!--breadcrumbs start -->
					<section class="panel">
						<header class="panel-heading">
							<h3>Daily report</h3>

						</header>
						<div class="">
							<ul class="breadcrumb">
								<li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
								<li><a href="<?= ROOT . CRM_ROOT . 'daily_report_list' ?>">
										<?= $form ?>
									</a></li>
							</ul>
						</div>
						<div class="col-md-4 mb-5">
							<div class="form-group">
								<div class="control-label col-md-4" style="white-space:nowrap;padding-left: 0px;">
									<strong>Employee</strong>
								</div>
								<div class="col-md-8">
									<input type="hidden" name="user_id" id="user_id" value="<?= $user_id; ?>" />

									<select class="form-control" id="user_names" name="user_names">
										<?= $user_ids; ?>
									</select>

								</div>
							</div>
						</div>
						<div class="col-md-4 mb-5" style="vertical-align:center;">
							<div class="form-group">
								<label class="control-label col-md-4" style="text-align: right;">Start :</label>
								<div class="col-md-8">
									<input id="start_date" name="start_date" type="text" class="form-control default-date-picker" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="loadtable();">
								</div>
							</div>
						</div>
						<div class="col-md-4 mb-5">
							<div class="form-group">
								<label class="col-md-4 control-label" style="text-align: right;">End :</label>
								<div class="col-md-8">
									<input id="end_date" name="end_date" type="text" class="form-control default-date-picker" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="loadtable();">
								</div>
							</div>
						</div>
						<div class="row">

							<div class="col-md-12" style="padding-top:30px;">
								<span class="tools pull-right">
									<a href="javascript:;"><button onClick="exportCsv()" class="btn btn-success btn-flat">Export Excel</button></a>
								</span>
								<span class="tools pull-right">
									<a href="<?= ROOT . CRM_ROOT . 'daily_report_add' ?>"><button class="btn btn-success btn-flat">Add
											<?= $form ?>
										</button></a>
								</span>
							</div>
						</div>

						<div class="panel-body">
							<div class="row">
								<div class="adv-table">
									<table class="display table table-bordered table-striped" id="daily_report-table">
										<thead>
											<tr>
												<th width="5%">SR. NO.</th>
												<th width="70%">Description</th>
												<th width="10%">Date</th>
												<th width="10%">Username</th>
												<th width="5%" class="hidden-phone">Action</th>
											</tr>
										</thead>
										<tbody>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="modal colored-header info" id="ModalEditreport" role="dialog" data-keyboard="false" data-backdrop="static">
							<div class="modal-dialog custom-width">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h3>Edit
											<?= $form ?>
										</h3>
									</div>
									<form id="formeditreport" role="form" method="post" novalidate>
										<div class="modal-body form">
											<div class="form-group">
												<label for="edit_description">Edit Description</label>
												<textarea class="form-control" name='edit_description' id='edit_description' validate></textarea>
											</div>
											<div class="form-group">
												<label>File Attachment</label>
												<div style="display: flex;">
													<input class="form-control" type="file" name="file_attachment" id="file_attachment" placeholder="" />
													<input type="hidden" class="file_attachment_name" id="file_attachment_name">
													<div class="btn btn-xs btn-danger" id="btn-file-delete" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_editatble_file()" style="margin: 5px;"><i class="fa fa-trash-o"></i></div>
												</div>
												<label id="add_file_name"></label>
											</div>
										</div>
										<div class="modal-footer">
											<input type="hidden" name="edit_id" id="edit_id" value="" />
											<input type="hidden" name="mode" id="mode" value="edit" />
											<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
											<button class="btn btn-success btn-flat" type="submit">Update</button>
										</div>
									</form>
								</div><!-- /.modal-content -->
							</div><!-- /.modal-dialog -->
						</div>
						<!--main content end-->
						<!--footer start-->
						<?php include_once('../../include/footer.php'); ?>
						<?php include_once('../include/preview_approval_hist.php'); ?>
						<?php include_once('../include/qutation_print_option.php'); ?>
						<?php include_once('../include/preview_quot_revision_hist.php'); ?>
						<?php //include_once('../include/send_email.php');
						?>
						<?php include_once('../include/send_email_via_quotation.php'); ?>
						<?php include_once('../include/send_email_via_quotation_dir.php'); ?>
						<!--footer end-->
					</section>
			</section>
		</section>
		<!-- js place
			d at the end of the document so the pages load faster -->
		<?php include_once('../../include/include_js_file.php'); ?>
		<script src="<?= ROOT . CRM_ROOT ?>js/app/daily_report.js?<?= time() ?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});

			function cb(start, end) {
				$('.datepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
			}
			cb(moment().subtract(29, 'days'), moment());

			$(document).ready(function() {
				// var country = $("#country_id").val();
				// var state = $("#state_id").val();
				// load_state(country,'state_id',state);
			});

			var date = new Date();
			var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
			var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + 15);

			$(".form_datetime-meridian").datetimepicker({
				format: "dd-mm-yyyy HH:ii P",
				showMeridian: true,
				autoclose: true,
				todayBtn: true,
				pickerPosition: "bottom-left",
				startDate: today,
				endDate: endDate
			});

			$('.datepikerdemo').daterangepicker({
				locale: {
					format: 'DD-MM-YYYY'
				},
				"autoApply": true,
				"startDate": $('#from_date').val(),
				"endDate": $('#to_date').val(),
				ranges: {
					'Today': [moment(), moment()],
					'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
					'Last 7 Days': [moment().subtract(6, 'days'), moment()],
					'Last 30 Days': [moment().subtract(29, 'days'), moment()],
					'This Month': [moment().startOf('month'), moment().endOf('month')],
					'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
				}
			}, cb);
			$('.date-set').click(function() {
				$('.datepikerdemo').trigger('click');
			});

			CKEDITOR.replace('edit_description', {
				enterMode: CKEDITOR.ENTER_BR
			});



			$(function() {
				setTimeout(function() {
					$('#sidebar > ul').hide();
				}, 1000);
			});
		</script>
</body>

</html>