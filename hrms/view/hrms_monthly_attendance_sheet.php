<?php
session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
$token = md5(rand(1000, 9999));
$_SESSION['token'] = $token;
$infopage = pathinfo(__FILE__);
$_SESSION['page'] = HRMS_ROOT . $infopage['filename'];
$title = 'Monthly Attendance Sheet';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php'); ?>
	<style type="text/css">
		.chartStyle {
			height: 300px; 
			width: 100%;
		}
		.setMargin {
			margin-top: 20px;
		}

		#dynamic-table {
			overflow: auto;
			display: block;
		}
	</style>
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
					<div class="col-md-8">
						<div class="col-md-2">
							<label for="date">Select Month</label>
						</div>
						<div class="col-md-3">
							<input type="text" class="form-control monthpicker" name="date" id="date" onchange="showGraphReport(this.value)" autocomplete="off" />
						</div>
					</div>
				</div>

				<div class="row setMargin">
					<div class="col-md-12">
						<div id="load_summary_report" class="chartStyle"></div>
					</div>
				</div>

				<div class="row setMargin">
					<div class="col-sm-12">
						<section class="panel">
							<div class="panel-body">
								<div class="adv-table">
									<table class="display table table-bordered table-striped" id="dynamic-table">
										<thead>
											<tr class="showHeader">
												<th>Sr. NO.</th>
												<th class="last">Employee Name</th>
												<th>Total Present Days</th>
												<th>Total Absent Days</th>
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

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_monthly_attendance_sheet.js?<?=time()?>"></script>
	<script>
		$(document).ready(function () {
			var date = new Date();
			var month = date.getMonth() + 1;
			month = (month < 10) ? '0'+month : month;
			var year = date.getFullYear();
			$('.monthpicker').datepicker('setDate',month+'-'+year);
		});
		$(".select2").select2({
			width: '100%'
		});
		$(".monthpicker").datepicker( {
		    format: "mm-yyyy",
		    startView: "months", 
		    minViewMode: "months",
		    autoclose: true,
		});
	</script>
</body>
</html>