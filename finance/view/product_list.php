<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	$form="Item";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$countryid='101';
	$stateid='1';
	$cityid='1';
	$end = date("d-m-Y");
	$branch_id = $_SESSION['branch_id'];
	//check permission for annexure list
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	ADMINISTRATOR_PRODUCT_LIST,
        ADMINISTRATOR_PRODUCT_CREATE,
        ADMINISTRATOR_PRODUCT_EXCEL
    ]);

    if(!in_array(ADMINISTRATOR_PRODUCT_LIST,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
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
							<li><a href="<?=ROOT.'masters_list'?>"> Masters List</a></li>
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
							<div class="col-md-4">
								<span class="tools pull-right">
									<?php if(in_array(ADMINISTRATOR_PRODUCT_EXCEL,$bulkAccessArray)){ ?> 
										<a href="<?=ROOT.'generate_export_product_csv'?>" ><button class="btn btn-info btn-flat" ><i class="fa fa-download" aria-hidden="true"></i> Export Items</button></a>
									<?php } ?>
									<?php if(in_array(ADMINISTRATOR_PRODUCT_CREATE,$bulkAccessArray)){ ?> 
										<a href="<?=ROOT.'product_add'?>" ><button class="btn btn-success btn-flat" >Add <?=$form?></button></a>
									<?php } ?>	
								</span> 
							</div>
						</div>
						
					</header>	
					<div class="panel-body">
						<div class="adv-table" id="adv-table">
							<table  class="display table table-bordered table-striped" id="product-table">
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Product Image</th>
										<th>Product Type</th>
										<th>Product Name</th>
										<th>Drawing Number</th>
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
