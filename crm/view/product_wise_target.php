<?php 
session_start();
include('../include/urlfile.php');
// $incPath = $path.'include/';

	$start=date('d-m-Y');
	$end=date("d-m-Y", strtotime('+1 month'));
	
	$form="Product Wise Target ";

	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<?php include_once($include.'include_css_file.php');?>
		<style type="text/css">
			table tr th ,td{
				text-align: center !important;
			}
		</style>
	</head>
	<body>
		<section id="container">
			<?php include_once($include.'include_top_menu.php');?>
			<!--sidebar start-->
			<?php include_once($include.'left_menu.php');?>
			<!--sidebar end-->
			<!--main content start-->
			<section id="main-content">
				<section class="wrapper">

					<!--state overview start-->
					<section class="panel">
						
						<div class="col-md-12" style="margin-top:20px;"></div>
						<div class="panel-body">
							<div class="row">
								<div class="col-md-12">
									<div class="col-md-12" style="margin-top:10px;">
										<label class="col-md-12 control-label" style="font-weight: bold;font-size: 20px;color: black;">
										<?=$form?></label>
										<input type="hidden" name="month" id="month" value="<?=$month?>" />
										<div class="col-md-12">
											<table class="table table-bordered table-hover table-striped">
												<thead>
													<th>#</th>
									      			<th>Product</th>
									      			<th>Current Target</th>
									      			<th>Achieved</th>
									      			<th>Action</th>
												</thead>
												<tbody  id="product_details_table">
													
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</section>
					<!--state overview end-->

				</section>
			</section>
<!--main content end-->
<!--footer start-->
<?php include_once($include.'footer.php');?>
<!--footer end-->
</section>
<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>
<?php include_once($include1.'add_product_wise_followup.php');?>
<?php include_once($include1.'product_wise_followup_history.php');?>
<script src="<?=ROOT.CRM_ROOT?>js/app/dashboard_target.js?<?=time()?>"></script>
<script>
	$(".select2").select2({
		width: '100%'
	});
	
	$(document).ready(function() {
		Loading(true);	
		load_product_vise_target();
		Unloading();
	});
</script>
</body>
</html>