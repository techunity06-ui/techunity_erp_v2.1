<?php 
session_start();

include('../include/urlfile.php');	

$form="Workorder Material Issue";

$companyConfiguration=getCompanyConfiguration($dbcon);
$type_conf = $companyConfiguration['production_pro_type'];
$pro_search = $companyConfiguration['bom_pro_search'];	
$back_link = ROOT.INVENTORY_ROOT.'workorder_material_issue';
$mode = "Add";
$material_issue_date=date('d-m-Y');
$readonly = "";

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    INVENTORY_WORKORDER_MATERIAL_ISSUE_LIST_SLUG_VIEW,INVENTORY_WORKORDER_MATERIAL_ISSUE_LIST_SLUG_CREATE
	]);

	// if(!in_array(INVENTORY_WORKORDER_MATERIAL_ISSUE_LIST_SLUG_CREATE,$bulkAccessArray)){
 //        header("Location: ".DOMAIN."permission_access");
 //    }
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Workorder Material Issue Create</title>
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
										<li><a href="<?=ROOT.INVENTORY_ROOT.'workorder_material_issue'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="material_issue" action="javascript:;" method="post" name="material_issue">
										<div class="row">
											<input type="hidden" name="inquiry_type" id="inquiry_type" value="1">
											<input type='hidden' name='mode' id='mode' value="<?=$mode?>" />
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Material Issue No *</label>
														<div class="col-md-8 col-xs-11">
														<input id="material_issue_no" name="material_issue_no"  type="text" class="form-control" title="Quantity" value="<?=@$material_issue_no?>" readonly placeholder="Enter Material Issue Number">
														</div>
													</div>
												</div>
												<div class="col-md-4">	
													<div class="form-group">
														<label class="col-md-4 control-label">Material Issue Date *</label>
														<div class="col-md-8 col-xs-11">
														<input id="material_issue_date" name="material_issue_date" type="text" class="form-control default-date-picker required valid" title="Material Issue Date" value="<?=$material_issue_date?>" <?=$readonly?> placeholder="Material Issue Date">
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Workorder No *</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="workorder_id" id="workorder_id" onchange="get_workorder_product(this.value);" placeholder="Select Workorder">
																<?=getallworkorder($dbcon)?>
															</select>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Product *</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="rp_id" id="rp_id" onchange="get_workorder_product_process(this.value);get_product_id(this.value)" placeholder="Select Product">
																<option value="">Select Product</option>
															</select>
															<input type="hidden" name="wo_product_id" id="wo_product_id" value="">
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Process *</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="process_id" id="process_id">
																<option value="">Select Process</option>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Allocate User*</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="allocate_user_id" id="allocate_user_id">
																<?=getalluser($dbcon,$_SESSION['user_id']); ?>
															</select>
														</div>
													</div>
												</div>

												 <div class="col-md-4">
                                                <?php echo getBranchBox($dbcon, $branch_id, $_SESSION['branch_id'], false, true,'','4','8'); ?>
                                            </div>
											</div>
											<div class="col-md-12">
								<div class="form-group">
									<table cellspacing="10" style="border-collapse:inherit;table-layout: fixed;" id="product_list" class="display table table-bordered table-striped">
										<tr id="field">
											<!-- <th width="20%" class="text-center">Type</th> -->
											<th width="25%" class="text-center">Product Name</th>
											<th width="20%">Available Stock</th>
											<!-- <th width="20%" class="text-center hide_product_version">Version</th> -->
											<th width="5%" class="text-center hide_act_add">Unit</th>
											<th width="10%" class="text-center hide_act_add">Base Qty</th>
											<th width="5%" class="text-center hide_act_add">UOM</th>
											<th width="15%" class="text-center hide_act_add">Convert Qty.</th>

											<th width="10%" class="text-center"></th>
										</tr>
										<tr id="field1">
										<!-- 	<td style="vertical-align:top;" width="20%">
												<select class="select2 prtype" name="product_type" id="product_type" onChange="load_product(this.value);" title="Select Product Type" style="width: 100%;">
													<?=get_bom_producttype($dbcon,'');?>
												</select>
											</td> -->
											<td style="vertical-align:top;" width="25%">
												<input id="product_id" name="product_id" style="width:100%;" placeholder="Select product" onchange="load_product_detail(this.value);load_stock_qty(this.value,0);"/>
												<br/><br/>
												
											</td>	
											<td>
				                                 <input type="number" class="form-control" id="item_stock" onkeypress="return isNumberKey(event)" readonly />
				                             </td>
											<!-- <td class="hide_product_version">
												<select class="select2 productversion" title="Select Version" name="pro_version_id" onchange="get_p_bom_id(this.value)"  id="pro_version_id">
													<option value="">Choose Product Version</option>

												</select>
											</td> -->
											<td style="vertical-align:top;" class="hide_act_add">
												<input class="form-control" type="text" name="product_base_unit_name" id="product_base_unit_name" value="" readonly />
												<input type="hidden" name="product_base_unit" id="product_base_unit"value="" />	
											</td>	
											<td style="vertical-align:top;" class="hide_act_add">
												<input type="number"  title="Enter Qty" min="0" id="product_base_qty" data-product_base_qty="" name="product_base_qty" onkeyup="product_convert_qty(1);" value="1"  class="form-control" />

												<input type="hidden" id="product_base_qty_hide" name="product_base_qty_hide" value="" />

											</td>
											<td style="vertical-align:top;" class="hide_act_add">
												<input class="form-control" type="text" id="product_conv_unit_name" name="product_conv_unit_name" value="" readonly />

												<input type="hidden" name="product_conv_unit" id="product_conv_unit"value="" />
											</td>
											<td style="vertical-align:top;" class="hide_act_add">
												<input type="number"  title="Enter Qty" min="0" id="product_conv_qty" data-product_conv_qty=""
												name="product_conv_qty"  class="form-control" onkeyup="product_convert_qty(2);"  value="1"  />
												<!--onkeyup="product_convert_qty(2);"-->
												<input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />

												<input type="hidden"  title="" id="product_spec_hid" name="product_spec_hid"  class="form-control" />
												<input type="hidden"  title="" id="product_spec_hid_qty" name="product_spec_hid_qty"  class="form-control" />
												<input type="hidden"  title="" id="product_spec_act_qty" name="product_spec_act_qty"  class="form-control" />
											</td>		
											<td style="vertical-align:top;">
												<!-- Sanat :: comment below button :: 03-03-2021 -->
												<!-- <input type="button"  name="addrow" id="addrow" onClick="return add_field();" class="btn btn-primary" value="Add"/> -->
												<input type="button" id="addrow" class="btn btn-primary" data-original-title="Add Product" data-toggle="tooltip" data-placement="top" onclick="add_field();" value="Add"/>
											</td>
											<input type='hidden' name='edit_id' id='edit_id' value="" />
										</tr>
									</table>			
								</div>
							</div>
											<div class="mtop20" id="material_issue_temp_div"> </div>
											<div class="col-md-12 text-center mtop20">
													<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
											<a href="<?=$back_link?>" type="button" class="btn btn-danger">Cancel</a>
											</div>	
										</div>	
									</form>
								</div>	
								<div class="row">	
									<div class="col-md-12">
										<div id="costing_report" style="padding: 20px;"></div>
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
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/workorder_material_issue.js?<?php echo time(); ?>"></script>
		<script src="<?=ROOT?>js/advanced-form-components.js"></script>
	<script>
		$(document).ready(function() {
			delete_tempout_data();
			 get_material_issue_no();
		 	
		});
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
