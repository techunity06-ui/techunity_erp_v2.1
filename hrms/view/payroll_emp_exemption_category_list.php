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
$title = 'Employee Tax Exemption Category';
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
									<form role="form" id="payroll_emp_exemption_category_add" action="javascript:;" method="post" name="payroll_emp_exemption_category_add">
										<div class="form-group">
											<label for="category_name">Name*</label>
											<input type="text" class="form-control" id="category_name" name="category_name" placeholder="Enter Name" />
										</div>
										<div class="form-group">
											<label for="max_exemption_amount">Max Exemption Amount</label>
											<input type="text" class="form-control" id="max_exemption_amount" name="max_exemption_amount" placeholder="Enter Max Exemption Amount" />
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
												<th>Category Name</th>
												<th>Max Exemption Amount</th>
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
	<div class="modal colored-header info" id="ModalEditEmpExemptionCategory" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Edit <?php $title; ?></h3>
				</div>
				<form id="FormEditEmpExemptionCategory" role="form" method="post" novalidate>
					<div class="modal-body form">
							<div class="form-group">
								<label for="category_name">Name*</label>
								<input type="text" class="form-control" id="edit_category_name" name="category_name" placeholder="Enter Name" required="" />
							</div>
							<div class="form-group">
								<label for="max_exemption_amount">Max Exemption Amount</label>
								<input type="text" class="form-control" id="edit_max_exemption_amount" name="max_exemption_amount" placeholder="Enter Max Exemption Amount" required="" />
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
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/payroll_emp_exemption_category.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});
	</script>
</body>
</html>