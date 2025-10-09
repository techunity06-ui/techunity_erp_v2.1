<?php 
session_start();

include('../include/urlfile.php');	
// error_reporting(E_ALL);
$form="BOM Costing";

$companyConfiguration=getCompanyConfiguration($dbcon);
$type_conf = $companyConfiguration['production_pro_type'];
$pro_search = $companyConfiguration['bom_pro_search'];	
$back_link = ROOT.PRODUCTION_ROOT.'bom_costing_list';
$mode = "Add";
$costing_date=date('d-m-Y');
$readonly = "";

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    PRODUCTION_BOM_COSTING_LIST_SLUG_VIEW,PRODUCTION_BOM_COSTING_LIST_SLUG_CREATE
	]);

	if(!in_array(PRODUCTION_BOM_COSTING_LIST_SLUG_CREATE,$bulkAccessArray)){
        //header("Location: ".DOMAIN."permission_access");
    }
if(strpos($_SERVER['REQUEST_URI'], "bom_costing_edit")==true){

	$mode="Edit";
	$bom_costing_id=$dbcon->real_escape_string($_REQUEST['id']);

	$pro_qry = "select * from  tbl_bom_costing where bom_costing_id = " . $bom_costing_id; 
	$pro_rs=$dbcon->query($pro_qry);
	$pro_row = brp_mysqli_fetch_array($pro_rs);
	$product_id = $pro_row['product_id'];
	$qty = $pro_row['qty'];
	$bom_id = $pro_row['bom_id'];
	$bom_version_id = $pro_row['bom_version_id'];
	$costing_no = $pro_row['costing_no'];
	$costing_date=date('d-m-Y',strtotime($pro_row['costing_date']));
	$purchase_rate = $pro_row['purchase_rate'];
	$template_id = $pro_row['template_id'];
	$readonly = "readonly";
	$product_name = get_product_name($dbcon,$product_id);
}


?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>BOM Costing</title>
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
										<li><a href="<?=ROOT.PRODUCTION_ROOT.'bom_costing_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="bom_costing" action="javascript:;" method="post" name="bom_costing">
										<div class="row">
											<input type="hidden" name="inquiry_type" id="inquiry_type" value="1">
											
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Costing No *</label>
														<div class="col-md-8 col-xs-11">
														<input id="costing_no" name="costing_no"  type="text" class="form-control" title="Quantity" value="<?=@$costing_no?>" <?=$readonly?> placeholder="Enter Costing Number">
														</div>
													</div>
												</div>
												<div class="col-md-4">	
													<div class="form-group">
														<label class="col-md-4 control-label">Costing Date *</label>
														<div class="col-md-8 col-xs-11">
														<input id="costing_date" name="costing_date" type="text" class="form-control default-date-picker required valid" title="Costing Date" value="<?=$costing_date?>" <?=$readonly?> placeholder="Costing Date">
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Costing Template *</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="template_id" id="template_id">
																<!-- <option value="">Select Costing Template</option> -->
																<?=get_bom_costing_template($dbcon,@$template_id);?>
															</select>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Select Product *</label>
														<div class="col-md-8">
															<input <?=$readonly?> id="product_id" name="product_id" style="width:100%;" placeholder="Select product" onchange="load_product_version(this.value);" value=""/>
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">BOM Version *</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="bom_version_id" id="bom_version_id" onchange="get_bom_details(this.value);">
																<option value="">Select BOM Verson</option>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Quantity *</label>
														<div class="col-md-8 col-xs-11">
														<input id="qty" name="qty"  type="text" class="form-control digitOnly" readonly title="Quantity" value="<?=$qty?>" placeholder="Enter Costing Quantity">
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-12">
													<div class="form-group">
														<label class="col-md-2 control-label">Set Costing Rate Priority *</label>
														<div class="col-md-10 col-xs-11">
															 <select class="select2" name="purchase_rate" id="purchase_rate"> 
															<!--<select class="select2" name="purchase_rate[]" id="purchase_rate" multiple data-placeholder="Select Costing Rate Priority">	-->
															<!--	<option value="">Select Rate Type</option> -->
																<option value="1">Last Purchase Bill Rate</option>
																<option value="2">Average Purchase Rate</option>
																<option value="3">Last PO Rate</option>
																<option value="4">Last Purchase Card Rate</option>
																<option value="5">Opening Stock Fitst</option>
																<option value="6">Opening Stock Last</option>
																<option value="7">Opening Stock Average</option>
															</select>
														</div>
													</div>
												</div>
											</div>

											<input type="hidden" name="bom_id" id="bom_id" value="<?=@$bom_id?>" />
											<input type="hidden" name="mode" id="mode" value="<?=$mode?>" />
											<?php if($mode == "Edit"){?>	
												<input type="hidden" name="edit_product_id" id="edit_product_id" value="<?=@$product_id?>" />
												<input type="hidden" name="edit_bom_version_id" id="edit_bom_version_id" value="<?=@$bom_version_id?>" />
												<input type="hidden" name="edit_purchase_rate" id="edit_purchase_rate" value="<?=@$purchase_rate?>" />
												<input type="hidden" name="edit_template_id" id="edit_template_id" value="<?=@$template_id?>" />
												<input type="hidden" name="process_name" id="product_name" value="<?=@$product_name?>" />
												<input type="hidden" name="bom_costing_id" id="bom_costing_id" value="<?=@$bom_costing_id?>" />
											<?php } ?>	
											<input type="hidden" name="bom_costing_id" id="bom_costing_id" value="<?=@$bom_costing_id?>" />
											<div class="col-md-12 text-center mtop20"><?php if($mode == "Add"){?>					
													<button type="submit" class="btn btn-success" id="save" name="save">Generate Costing</button>
												<?php } ?>
													<a href="<?=$back_link?>" type="button" class="btn btn-danger">Cancel</a>
											</div>	
										</div>	
									</form>
								</div>	
								<div class="row">	
									<div class="col-md-12">
										<div id="costing_report" style="padding: 20px;"></div>
									</div>
									<div class="col-md-12 mtop20" style="margin-bottom: 20px;">
										<div class="text-center">
											<button type="button" class="btn btn-success" id="save_costing" name="save_costing" onclick="save_costing_template_value()">Save</button>
										</div>
									</div>
								</div>
								
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
		</section>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/bom_costing.js?<?php echo time(); ?>"></script>
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
