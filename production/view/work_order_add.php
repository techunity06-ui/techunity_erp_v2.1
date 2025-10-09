<?php 
	session_start();
	include('../include/urlfile.php');	
	$form="Work Order";
	$mode='';
	$countryid='101';
	$stateid='1';
	$cityid='1';

	$company_config = getCompanyConfiguration($dbcon);	

	$extra_stock = $company_config['extra_stock'];

	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    PRODUCTION_WORK_ORDER_SLUG_VIEW,PRODUCTION_WORK_ORDER_SLUG_CREATE,PRODUCTION_WORK_ORDER_SLUG_UPDATE
	]);

	if(!in_array(PRODUCTION_WORK_ORDER_SLUG_CREATE,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
	// error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">
<title>WORK ORDER</title>
	<head>
		<?php include_once($include.'include_css_file.php');?>
		<style >
			.error {
				font-weight: bold;
				color: #ef1717;
				
				font-size: 16px;
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
									<h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li><a href="<?=ROOT.PRODUCTION_ROOT.'work_order'?>"><?=$form?> List</a></li>
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
									<div class="first_page" style="<?=$first_page_style?>" >
										<div class="col-md-12" style="margin-top: 10px;<?=$branch_style?>">
											<div class="col-md-4"></div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label"><strong>Branch </strong></label>
													<div class="col-md-8 col-xs-11">
														<select class="select2" name="branch_id" id="branch_id"  title="Select Branch Name">
															<?=get_branch_name_company($dbcon,$branchid,'','1')?>
														</select>
													</div>
												</div>	
											</div>
										</div>
										<div class="col-md-12" style="margin-top: 10px;">
											<div class="col-md-4"></div>
											<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label"><strong>Product </strong></label>
												<div class="col-md-8 col-xs-11">
													<!-- <select class="select2 selproduct1" title="Select product" name="product_id" id="product_id" onchange="check_bom_version();" >
														
														<=getproduct($dbcon,'');?>
													</select> -->
													<input id="product_id" name="product_id" style="width:100%;" placeholder="Select product" onchange="check_bom_version();get_reorder_qty();"  class="select2 selproduct1"/>
												</div>
												<input type="hidden" name="reorder_qty" id="reorder_qty" value="">
											</div>	
											</div>
										</div>
										<div class="col-md-12" style="margin-top: 10px;">
											<div class="col-md-4"></div>
											<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label"><strong>Bom Version </strong></label>
												<div class="col-md-8 col-xs-11">
													<select class="select2 selbom" style="width: 100%;" title="Select Bom Version" name="bom_version_id" id="bom_version_id">
														<option selected="selected" value="10000">R&D</option>
														
													</select>
												</div>
											</div>	
											</div>
										</div>
										<div class="col-md-12" style="margin-top: 10px;">
											<div class="col-md-4"></div>
											<div class="col-md-4">
												<div class="form-group">  	
													<label class="col-md-4 control-label" ><strong>Quantity </strong></label>
													<div class="col-md-8 col-xs-11">
														<input id="qty" name="qty" type="text" class="form-control digitOnly" title="" value="" placeholder="Qty" onkeydown="return digitonly(event);"  >
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12" style="margin-top: 10px;">
											<div class="col-md-4"></div>
											<div class="col-md-4">
												<div class="form-group">  	
													<label class="col-md-4 control-label" ><strong>Priority </strong></label>
													<div class="col-md-8 col-xs-11">
														<select class="select2 priority_status" name="priority_status" id="priority_status">
															<option value="Low">Low</option>
															<option value="Medium">Medium</option>
															<option value="High">High</option>		
														</select>
													</div>
												</div>
											</div>
										</div>
										<?php if($extra_stock == '1') { ?>
										<div class="col-md-12" style="margin-top: 10px;">
											<div class="col-md-4"></div>
											<div class="col-md-4">
												<div class="form-group">  	
													<label class="col-md-4 control-label"><strong>Stock Type </strong></label>
													<div class="col-md-8 col-xs-11">
														<select class="select2 selbom" style="width: 100%;" title="Select Stock Type" name="extra_stock" id="extra_stock" onclick="toggle_vendor_list(this.value);">
															<option value="0">Actual Stock</option>
															<option value="1">Extra Stock</option>
														</select>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12 vendor_row" style="display:none;margin-top: 10px;">
											<div class="col-md-4"></div>
											<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>Supplier</strong></label>
														<div class="col-md-8 col-xs-11">
															 <select class="select2 selbom" name="ext_stock_vendor_id" id="ext_stock_vendor_id" title="Select Vendor" required>
					                                             <?= getcust($dbcon,'',''); ?>
					                                          </select>
														</div>
													</div>
												</div>
										</div>
										<?php } else { ?>
											<input type="hidden" name="extra_stock" id="extra_stock" value="0">
										<?php } ?>
 										<div class="col-md-12" style="    margin-top: 10px;">
											<div class="col-md-5"></div>
											<div class="col-md-4">
												<button class="btn btn-danger" data-original-title="Next" data-toggle="tooltip" data-placement="Next" onClick="next_page()"><i class="fa fa-arrow-right"></i>Next</button>
											</div>
										</div>
									</div>
									<div class="second_page" style="<?=$second_page_style?>">
										
									</div>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once($include1.'add_workorder_product.php');?>
			<?php include_once($include1.'add_workorder_sub_product.php');?>
			<?php include_once($include1.'update_product_process.php');?>
			<?php include_once($include.'footer.php');?>
		</section>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/work_order_add.js?<?=time()?>"></script>
		<script>
			/*$(".selproduct1").select2({
				width: '100%',
				minimumInputLength: 2,

			});	*/
			
			$(".selbom,.priority_status").select2({
				width: '100%'

			});	
			
			$(".select2").select2({
				width: '100%'
			});
			
			
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});

			$(".form_datetime").datetimepicker({
				format: 'dd-mm-yyyy hh:ii',
				autoclose: true,
				todayBtn: true,
				pickerPosition: "bottom-left"

			});	
			
		</script>
		
	</body>
</html>
