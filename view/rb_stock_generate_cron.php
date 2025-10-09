<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Stock Cron";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Stock Cron</title>
<?php include_once('../include/include_css_file.php');?>
<style>

@media (min-width: 1200px){
#custom_sold_modal {
    width: 1150px;
}
}
</style>
</head>
<body>
<section id="container" class="sidebar-closed">
<?php include_once('../include/include_top_menu.php');?>
<!--sidebar start-->
<?php include_once('../include/left_menu.php');?>
<!--sidebar end-->
<!--main content start-->

<section id="main-content">
	
	<section class="wrapper">
		
		<div class="row">
			<div class="col-lg-12">
				<!--breadcrumbs start -->
				<section class="panel">
					<header class="panel-heading">
						<h3><?=$form?> List</h3>
					</header>	
					<div class="">
						<ul class="breadcrumb">
							<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							<li class="active"><?=$form?> list</li>
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
						<div class="col-md-12" style="margin-top:10px;">
							<div class="col-md-4">
                                <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'load_pro_tbl()','4','6'); ?>
                            </div>
							<div class="col-md-4">
								<div class="form-group">
									<label class="control-label col-md-4">Product Type</label>
									<div class="col-md-7">
										<select class="select2" id="fil_product_type" name="fil_product_type" onchange="load_pro_tbl();">
										<!--START JAYESH 15-07-2021 purpose : dynamic data from database -->
										
											 <?php echo get_product_type_company($dbcon,'','ALL'); ?>
											
										</select>
									</div>
								</div>
							</div>
							
						</div>
						
					</header>	
					<div class="panel-body">
						<div class="adv-table" id="adv-table">
							<table  class="display table table-bordered table-striped" id="product-table">
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Product Type</th>
										<th>Product Name</th>
										<th>Product Code</th>
										<th>Status</th>
										<th>CRON Status</th>
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
		<input type="hidden" name="custno" id="custno" value="<?=$end?>">	
		<!--state overview end-->
	</section>
</section>
<!--main content end-->
<!--footer start-->
<?php 

	include_once('../include/footer.php');
?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<script src="<?=ROOT?>js/app/rb_stock_generate_cron.js?<?=date('dmy')?>"></script>

</body>
</html>
