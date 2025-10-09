<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Item";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$countryid='101';
	$stateid='1';
	$cityid='1';
	$end = date("d-m-Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
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
							<li><a href="<?=ROOT.'product_stage_list'?>"><?=$form?> list</a></li>
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
							<div class="col-md-6">
								<div class="form-group">
									<label class="control-label col-md-4">Product Type</label>
									<div class="col-md-7">
										<select class="select2" id="fil_product_type" name="fil_product_type" onchange="load_pro_tbl();">
											<option value="">--ALL--</option>
											<option value="0">FINISH PRODUCT</option>
											<option value="1">ASSEMBLY PRODUCT</option>
											<option value="2">SUB ASSEMBLY</option>
											<option value="3">RAW MATERIAL</option>
											<option value="4">FINISH PART</option>
											<option value="5">BOI</option>
											<option value="6">CAPITAL GOODS</option>
											<option value="7">CONSUMABLE</option>
											<option value="8">Service</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<span class="tools pull-right"> 
							
							
							<a href="<?=ROOT.'stage_add'?>" ><button class="btn btn-success btn-flat" >Add <?=$form?></button></a>
						</span> 
					</header>	
					<div class="panel-body">
						<div class="adv-table" id="adv-table">
							<table  class="display table table-bordered table-striped" id="product-table">
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Product Type</th>
										<th>Product Name</th>
										<th>Description</th>
										<th>Status</th>
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
<script src="<?=ROOT?>js/app/product_mst.js?<?=time()?>"></script>


<!--<script src="js/count.js"></script>-->
<script>
$(".select2").select2({
	width: '100%'
});

$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
</script>
</body>
</html>
