<?php
session_start();
error_reporting(E_ALL);
include('../include/urlfile.php');
// $incPath = $path.'include/';
$form = "Quotation";
$infopage = pathinfo(__FILE__);
$_SESSION['page'] = 'crm/' . $infopage['filename'];
$branch_id = $_SESSION['branch_id'];
if (empty($_SESSION['start'])) {
	$start = date('1-m-Y');
	$end = date("d-m-Y");
} else {
	$start = $_SESSION['start'];
	$end = $_SESSION['end'];
}
$companyConfiguration = getCompanyConfiguration($dbcon);
$getspecialConfiguration = getspecialConfiguration($dbcon);

$amnts = explode(",", get_quot_won_taxable_total($dbcon));
$cnyts = explode(",", get_quot_lost_taxable_total($dbcon));
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>QUOTATION LIST</title>
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
								<h3> <?= $form ?> List</h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?= ROOT . CRM_ROOT . 'quotation_list' ?>"><?= $form ?> List</a></li>
								</ul>
							</div>
							<div class="row">
								<div class="col-lg-12 centeral-align">
									<div class="icons">
										<div class="icon1 success">
											<p style="color:white;padding-top:10px;">Total Quotation Amount</p>
											<p style="color:white;" id="quotcount"></p>
											<h3 style="font-size:20px;color:white;" id="quotamt"></h3>
										</div>
									</div>
								</div>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>
				</div>

				<div class="row">
					<!--state overview start-->
					<div class="row">
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label col-md-4">Choose Date</label>
											<div class="col-md-7">
												<div class="input-group date form_datetime-component">
													<input type="hidden" id="from_date" value="<?= $start ?>">
													<input type="hidden" id="to_date" value="<?= $end ?>">
													<input type="text" id="rep_date" onChange="load_quotation_datatable();" class="form-control datepikerdemo" value="">
													<span class="input-group-btn">
														<button type="button" class="btn btn-danger date-set"><i
																class="fa fa-calendar"></i></button>
													</span>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label col-md-4" style="text-align: right;">Stage
												:</label>
											<div class="col-md-8">
												<select class="select2" name="stage_id" id="stage_id" onChange="load_quotation_datatable();">
													<?= get_inquiry_stage($dbcon, $stage_id); ?>
												</select>
											</div>
										</div>
									</div>
									<?phpif ($companyConfiguration['branch_wise_manage'] == 1) { ?>
										<div class="col-md-4">
											<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true, 'load_quotation_datatable()', '4', '8'); ?>
										</div>
									<?php} ?>

									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label col-md-4" style="text-align: right;">Quotation
												Approval Status :</label>
											<div class="col-md-8">
												<select class="select2" name="approve_status_val" id="approve_status_val" onChange="load_quotation_datatable();">
													<option value="">All</option>
													<option value="1">Authorized</option>
													<option value="2">Rejected</option>
													<option value="0">Pending</option>
												</select>
											</div>
										</div>
									</div>
								</header>
								<div class="col-md-12" style="height:20px;"></div>
								<div class="col-md-12">
									<span class="tools pull-right">
										<a href="javascript:;"><button onClick="exportCsv()" class="btn btn-success btn-flat">Export Excel</button></a>
									</span>
								</div>
								<div class="panel-body">
									<div class="adv-table dt-resp">
										<table class="display table table-bordered table-striped"
											id="quotation-datatable">
											<thead>
												<tr>
													<th>Quotation No</th>
													<th>Quotation Date</th>
													<th>Customer</th>
													<?phpif ($getspecialConfiguration['power_drive'] == 1) { ?>
														<th>Item code</th>
														<th>specification</th>
													<?php} ?>
													<th>Inquiry</th>

													<th>Stage</th>
													<th>City</th>
													<th>Amount</th>
													<?phpif ($getspecialConfiguration['durva_permission'] == 1) { ?>
														<th>Subject</th>
													<?php} ?>
													<th>Owner</th>
													<th>Assing User</th>
													<th>Approval</th>
													<th class="">Action</th>
												</tr>
											</thead>
											<tbody>
											</tbody>
											<tfoot>
												<tr>
													<th colspan="6" style="text-align:right">Total:</th>
													<th></th>
													<th></th>
													<th></th>
													<th></th>
													<th></th>
												</tr>
											</tfoot>
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
		<?php include_once('../include/preview_approval_hist.php'); ?>
		<?php include_once('../include/qutation_print_option.php'); ?>
		<?php include_once('../include/preview_quot_revision_hist.php'); ?>
		<?php include_once('../include/send_email_via_quotation.php'); ?>
		<?php include_once('../include/send_email_via_quotation_dir.php'); ?>
		<!--footer end-->
	</section>
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . CRM_ROOT ?>js/app/quotation.js?<?= time() ?>"></script>
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
		$('.date-set').click(function () {
			$('.datepikerdemo').trigger('click');
		});
		$(function () {
			setTimeout(function () { $('#sidebar > ul').hide(); }, 1000);
		});
		CKEDITOR.replace('email_content', {
			enterMode: CKEDITOR.ENTER_BR
		});

		<?//Hide approve btn if not allowed
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
			QUOTATION_SLUG_APPROVE
		]);
		//$mod_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'aprv',$dbcon);
		if (!in_array(QUOTATION_SLUG_APPROVE, $bulkAccessArray)) {
			?>
			$('#mod_per_div_sec').hide();
		<?
		}
		?>
	</script>
</body>

</html>