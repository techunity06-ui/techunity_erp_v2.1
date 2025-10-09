<?php
session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/payroll_common_functions.php");
$token = md5(rand(1000, 9999));
$_SESSION['token'] = $token;
$infopage = pathinfo(__FILE__);
$_SESSION['page'] = HRMS_ROOT . $infopage['filename'];
$title = 'Payroll Period';
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
								<h3>New <?php echo $title; ?></h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . HRMS_ROOT . 'payroll_dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li class="active"><?php echo $title; ?></li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>
				</div>
				<div class="row">
					<?php 
						$add_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'add',$dbcon); 
						if($add_btn_per != ""){
					?>
						<div class="col-sm-3">
							<section class="panel">
								<header class="panel-heading">
									New <?php echo $title; ?>
								</header>
								<div class="panel-body">
									<form role="form" id="payroll_period_add" action="javascript:;" method="post" name="payroll_period_add">
										<div class="form-group">
											<label for="payroll_period_name">Payroll Period Name*</label>
											<input type="text" class="form-control" id="payroll_period_name" name="payroll_period_name" placeholder="Enter Payroll Period Name" />
										</div>
										<div class="form-group">
											<label for="payroll_start_date">Payroll Start Date*</label>
											<input type="text" class="form-control datepicker" id="payroll_start_date" name="payroll_start_date" placeholder="Select Payroll Start Date" />
										</div>
										<div class="form-group">
											<label for="payroll_end_date">Payroll End Date*</label>
											<input type="text" class="form-control datepicker" id="payroll_end_date" name="payroll_end_date" placeholder="Select Payroll End Date" />
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
					<?php } ?>
					<?php if($add_btn_per != ""){ ?>
						<div class="col-sm-9">
					<?php }else { ?>
						<div class="col-sm-12">	
					<?php } ?>
						<section class="panel">
							<header class="panel-heading">
								<?php $title; ?> List
							</header>
							<div class="panel-body">
								<div class="adv-table">
									<table class="display table table-bordered table-striped" id="dynamic-table">
										<thead>
											<tr>
												<th>Sr. NO.</th>
												<th>Company Name</th>
												<th>Payroll Period Name</th>
												<th>Payroll Period Start Date</th>
												<th>Payroll Period End Date</th>
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
	<div class="modal colored-header info" id="ModalEditPayrollPeriod" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Edit <?php $title; ?></h3>
				</div>
				<form id="FormEditPayrollPeriod" role="form" method="post" novalidate>
					<div class="modal-body form">
							<div class="form-group">
								<label for="payroll_period_name">Payroll Period Name*</label>
								<input type="text" class="form-control datepicker" id="edit_payroll_period_name" name="payroll_period_name" placeholder="Enter Payroll Period Name" required="" />
							</div>
							<div class="form-group">
								<label for="payroll_start_date">Payroll Start Date*</label>
								<input type="text" class="form-control datepicker" id="edit_payroll_start_date" name="payroll_start_date" placeholder="Select Payroll Start Date" />
							</div>
							<div class="form-group">
								<label for="payroll_end_date">Payroll End Date*</label>
								<input type="text" class="form-control datepicker" id="edit_payroll_end_date" name="payroll_end_date" placeholder="Select Payroll End Date" />
							</div>
							<div class="form-group">
								<label for="status">Status*</label>
								<select class="select2" id="edit_status" name="status">
									<?php echo getStatusOptions($rel['status']); ?>
								</select>	
							</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
						<input type="hidden" name="edit_id" id="edit_id" value="" />
						<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
						<button class="btn btn-info btn-flat" type="submit">Update <?php $title; ?></button>
					</div>
				</form>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/payroll_period.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});
		$(".datepicker").datepicker({
	        format: "dd-mm-yyyy",
	        // startDate: "1d",
	        autoclose: true,
	        todayHighlight: true
	    });
		$('#payroll_start_date').datepicker().on('changeDate', function(e) {
	        var start_date = e.format(0,"dd-mm-yyyy");
	        var end_date = $('#payroll_end_date').val();

	        job_start_date = start_date.split('-');
	        job_end_date = end_date.split('-');

	        var new_start_date = new Date(job_start_date[2],job_start_date[0],job_start_date[1]);
	        var new_end_date = new Date(job_end_date[2],job_end_date[0],job_end_date[1]);

	        $('#payroll_end_date').datepicker('setStartDate', e.format(0,"dd-mm-yyyy"));
	        
	        if(end_date == '' || new_start_date > new_end_date) {
	            $('#payroll_end_date').datepicker('setDate', e.format(0,"dd-mm-yyyy"));
	        }
	    });
	    $('#edit_payroll_start_date').datepicker().on('changeDate', function(e) {
	        var start_date = e.format(0,"dd-mm-yyyy");
	        var end_date = $('#edit_payroll_end_date').val();

	        job_start_date = start_date.split('-');
	        job_end_date = end_date.split('-');

	        var new_start_date = new Date(job_start_date[2],job_start_date[0],job_start_date[1]);
	        var new_end_date = new Date(job_end_date[2],job_end_date[0],job_end_date[1]);

	        $('#edit_payroll_end_date').datepicker('setStartDate', e.format(0,"dd-mm-yyyy"));
	        
	        if(end_date == '' || new_start_date > new_end_date) {
	            $('#edit_payroll_end_date').datepicker('setDate', e.format(0,"dd-mm-yyyy"));
	        }
	    });
	</script>
</body>
</html>