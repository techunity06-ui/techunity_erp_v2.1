<?php
	session_start();
	error_reporting(E_ALL);
	include('../include/urlfile.php');

	$user_id = $_SESSION['user_id'];
	$form = "Daily report";
	$infopage = pathinfo(__FILE__);
	$_SESSION['page'] = 'crm/' . $infopage['filename'];

	if (empty($_SESSION['start'])) {
		$start = date('1-m-Y');
		$end = date("d-m-Y");
	} else {
		$start = $_SESSION['start'];
		$end = $_SESSION['end'];
	}
	$companyConfiguration = getCompanyConfiguration($dbcon);
	$getspecialConfiguration = getspecialConfiguration($dbcon);

	$daily_report_date = date('d-m-Y');
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
				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3>Daily report</h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li>
										<a href="<?= ROOT . CRM_ROOT . 'daily_report_list' ?>">
											<?= $form ?>
										</a>
									</li>
								</ul>
							</div>
							<div class="">
								<form id="send_input" role="form" method="post" novalidate>
									<div class="col-md-12">
										<div class="col-md-4 mb-4">
											<label for="date">Date</label>
											<!-- <input type="text" class="form-control" placeholder="date" title="date" name="date" id="date" value="<?php echo date('d-m-Y'); ?>"  /> -->
											<input id="date" name="date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$daily_report_date?>" placeholder="Date">
										</div>
										<div class="col-md-4 mb-4">
											<label for="employee">Employee</label>
											<?php
												$companyConfiguration = getCompanyConfiguration($dbcon);
												$d = date_create('date');
												$date = date_format($d, "d/m/Y ");
												$crm_user_type = $companyConfiguration['crm_user_type'];
												if ($_SESSION['user_type'] == 2) {
													$user_ids = get_users_typewise($dbcon, "", " AND user_type IN (" . $crm_user_type . ")");
												} else {
													$user_id = check_user_chein($dbcon, $_SESSION['user_id'], 1);
													$user_ids = get_user_report($dbcon, $user_id);
												}
												$set = "SELECT * FROM `users` WHERE `user_id` =" . $user_id;
												$comp_rel = mysqli_fetch_assoc($dbcon->query($set));
											?>
											<input type="hidden" name="user_id" id="user_id" value="<?= $user_id; ?>" />
											<input type="hidden" name="mode" id="mode" value="add" />
											<input class="form-control" type="text" id="user_name" value="<?= $comp_rel['user_name']; ?>" readonly>
										</div>
										<div class="col-md-4 mb-4">
											<label>File Attachment</label>
											<div style="display: flex;">
												<input class="form-control" type="file" name="file_attachment" id="file_attachment" placeholder="" />
												<div class="btn btn-xs btn-danger hidden" id="btn-file-delete" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_file()" style="margin: 5px;"><i class="fa fa-trash-o"></i></div>
											</div>
										</div>
									</div>
									<div class="col-md-12" style="padding-top:20px;">
										<div class="col-md-12">
											<!-- <h4>Description</h4> -->
											<label for="description">Description</label>

											<textarea class="form-control" rows="4" id="user_input" name="user_input" placeholder="Add your Description..." validate></textarea>
											<div class="col-md-12 " style="display: flex; justify-content: center;">

												<br>
												<div style="margin:auto;">
													<button type="submit" id="send_input" class="btn btn-success"> submit
														report</button>
													<a href="<?= ROOT . CRM_ROOT . 'daily_report_list' ?>" type="button" class="btn btn-danger">Cancel</a>
												</div>
												<br>
											</div>
										</div>
									</div>
								</form>

								<div class="panel-body" style="	margin-left: 25px;">

								</div>
								<div class="modal colored-header info" id="ModalEditreport" role="dialog" data-keyboard="false" data-backdrop="static">
									<div class="modal-dialog custom-width">

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

		var date = new Date();
		var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
		$('.default-date-picker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            endDate: today,

        });
			CKEDITOR.replace('user_input', {
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