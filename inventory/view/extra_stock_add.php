<?php 
session_start();

include('../include/urlfile.php');	

$form="Extra Stock";

$companyConfiguration=getCompanyConfiguration($dbcon);
$type_conf = $companyConfiguration['production_pro_type'];
$pro_search = $companyConfiguration['bom_pro_search'];	
$back_link = ROOT.INVENTORY_ROOT.'extra_stock_list';
$mode = "Add";
$material_issue_date=date('d-m-Y');
$readonly = "";

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    EXTRA_STOCK_ADD,EXTRA_STOCK_ADD
	]);

	if(!in_array(EXTRA_STOCK_ADD,$bulkAccessArray)){
        // header("Location: ".DOMAIN."permission_access");
    }
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Extra Stock <?=$mode?></title>
		<?php include_once($include.'include_css_file.php');?>
		<style>
			.currency_icon{
				color:green;
				font-size:12px;
			}
			label{
				font-size: 15px;
			}
			.row_margin
			{
				margin-top:10px;
			}
			.btn-group-vertical>.btn.active, .btn-group-vertical>.btn:active, .btn-group-vertical>.btn:focus, .btn-group-vertical>.btn:hover, .btn-group>.btn.active, .btn-group>.btn:active, .btn-group>.btn:focus, .btn-group>.btn:hover{
				z-index:2;
				background-color: #bbdce6;
			}
			.control-label{
				font-weight: bold;
			}
			.mb-5{
				margin-bottom: 5px;
			}
  		</style>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3> <?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li><a href="<?=ROOT.INVENTORY_ROOT.'extra_stock_list'?>"><?=$form?> List</a></li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									New <?=$form?>
								</header>	
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="extra_stock_add" action="javascript:;" method="post" name="extra_stock_add">
										<div class="row">
											<input type="hidden" name="inquiry_type" id="inquiry_type" value="1">
											<input type='hidden' name='mode' id='mode' value="<?=$mode?>" />
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Product *</label>
														<div class="col-md-8 col-xs-11">
																<input id="product_id" name="product_id"  style="width:100% !important"  placeholder="Select product" onchange="load_product_detail(this.value);" />
														</div>
													</div>
												</div>
												
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"> Batch No*</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="stock_id" id="stock_id" onchange="load_stock_qty(this.value)">
															
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Current Stock</label>
														<div class="col-md-8 col-xs-11">
															<input type="number" class="form-control" id="item_stock" onkeypress="return isNumberKey(event)" readonly />
														</div>
													</div>
												</div>
												
											</div>
											<div class="col-md-12">
												  <div class="col-md-4">
												 	<div class="form-group">
														<label class="col-md-4 control-label">Base Qty*</label>
														<div class="col-md-5 col-xs-11">
															<input type="number"  title="Enter Qty" min="0" id="product_base_qty" data-product_base_qty="" name="product_base_qty" onkeyup="product_convert_qty(1);" value="1"  class="form-control" />

												<input type="hidden" id="product_base_qty_hide" name="product_base_qty_hide" value="" />
												
														</div>
														<div class="col-md-3 col-xs-11">
												<input class="form-control" type="text" name="product_base_unit_name" id="product_base_unit_name" value="" readonly />
												<input type="hidden" name="product_base_unit" id="product_base_unit"value="" />	
														</div>
													</div>
												 </div>
												  <div class="col-md-4">
												 	<div class="form-group">
														<label class="col-md-4 control-label">Conv Qty</label>
														<div class="col-md-5 col-xs-11">
															<input type="number"  title="Enter Qty" min="0" id="product_conv_qty" data-product_conv_qty=""
												name="product_conv_qty"  class="form-control" onkeyup="product_convert_qty(2);"  value="1"  />
												<!--onkeyup="product_convert_qty(2);"-->
												<input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />
														</div>
													
													<div class="col-md-3 col-xs-11">
															<input class="form-control" type="text" id="product_conv_unit_name" name="product_conv_unit_name" value="" readonly />

												<input type="hidden" name="product_conv_unit" id="product_conv_unit"value="" />
														</div>
													</div>
													</div>
													<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Supplier</label>
														<div class="col-md-8 col-xs-11">
															 <select class="select2" name="vendor_id" id="vendor_id" title="Select Vendor" required>
					                                             <?= getcust($dbcon, $vendor_id); ?>
					                                          </select>
														</div>
													</div>
												</div>

												 </div>
												 <div class="col-md-12">
												 <div class="col-md-4">
                                                <?php echo getBranchBox($dbcon, $branch_id, $_SESSION['branch_id'], false, true,'','4','8'); ?>
                                           		 </div>
                                           		 <div class="col-md-8">
													<div class="form-group">
														<label class="col-md-2 control-label">Remark</label>
														<div class="col-md-10 col-xs-11">
															<textarea id="remark" name="remark" class="form-control" rows="4"></textarea>
														</div>
													</div>
												</div>
                                           		 
											</div>
											</div>
											
											<div class="col-md-12 text-center mtop20">
													<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
											<a href="<?=$back_link?>" type="button" class="btn btn-danger">Cancel</a>
											</div>	
										</div>	
									</form>
								</div>	
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
		</section>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/extra_stock.js?<?php echo time(); ?>"></script>
		<script src="<?=ROOT?>js/advanced-form-components.js"></script>
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
