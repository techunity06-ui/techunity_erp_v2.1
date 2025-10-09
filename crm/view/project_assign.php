<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	CRM_PROJECT_ASSIGN_SLUG_CREATE,
	CRM_PROJECT_ASSIGN_SLUG_UPDATE
]);

$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$form="Project Wise Item Assign";
$branch_id = $_SESSION['branch_id'];

if(strpos($_SERVER[REQUEST_URI], "project_assign_edit")==true)
{
	if(!in_array(CRM_PROJECT_ASSIGN_SLUG_UPDATE,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$mode="Edit";
	$project_assign_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from product_mst where product_id=$project_assign_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$edit_branch_id=$rel['branch_id'];
}
else
{
	if(!in_array(CRM_PROJECT_ASSIGN_SLUG_CREATE,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$mode="Add";
}
$generate_item_code = "";
$companyConfiguration=getCompanyConfiguration($dbcon);
if($companyConfiguration) {
	$generate_item_code = $companyConfiguration['generate_item_code'] ? $companyConfiguration['generate_item_code'] : $generate_item_code;
	$crm_pro_type=$companyConfiguration['crm_pro_type'];
	$crm_pro_search=$companyConfiguration['crm_pro_search'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>PROJECT ASSIGN</title>
	<?php include_once($incPath.'include_css_file.php');?>
</head>
<body>
	<section id="container">
		<?php include_once($incPath.'include_top_menu.php');?>
		<?php include_once($incPath.'left_menu.php');?>
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<h3> <?=$mode .' '.$form?></h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li ><a href="<?=ROOT.CRM_ROOT.'project_assign_list'?>"><?=$form?> List</a></li>
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
								<form class="form-horizontal" role="form" id="project_assign_add" action="javascript:;" method="post" name="project_assign_add">
									<div class="">
										<div class="col-md-12" style="margin: 20px 0;">
											<input type="hidden" class="form-control" id="product_type" name="product_type" value="-1">
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Project Name *</label>
													<div class="col-md-8">
														<input id="project_name" name="project_name" type="text" class="form-control" title="Enter Project Name" placeholder="Enter Project Name" value="<?=$rel['product_name']?>" placeholder="Project Name" required>
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="Product Type" class="col-md-4 control-label">Project Code</label>
													<div class="col-md-8">
														<input type="text" class="form-control" id="product_icode" name="product_icode" placeholder="Item Code" value="<?php if($mode=="Edit") { echo $rel['product_icode']; } else { }?>" <?= ($generate_item_code==0) ? "readonly" : ""; ?> />
														<input type="hidden" class="form-control" id="product_icode_code" name="product_icode_code"  value="" readonly />
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<?php echo getBranchBox($dbcon,$branch_id,$edit_branch_id, false, true,'','4','8'); ?>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="Product Type" class="col-md-4 control-label">Project Unit</label>
													<div class="col-md-8">
														<select class="select2" name="product_base_unit" id="product_base_unit"  title="Select Unit" onchange="get_base_unit(this.value);" required <?=$disabled?>>
															<?php if($mode=='Edit' || $mode=='Copy') { echo getunit($dbcon,$rel['product_base_unit']); } else { echo getunit($dbcon,3); } ?>
														</select>
														<?php if($mode=='Edit' || $mode=='Copy') { ?>
															<input type="hidden" name="product_base_unit" id="product_base_units" value="<?=$rel['product_base_unit']?>">
														<?php } ?> 
														<input type="hidden" name="product_conv_unit" id="product_conv_unit" value="<?php if($mode=='Edit' || $mode=='Copy'){ echo $rel['product_conv_qty']; } ?>"/>
														<input type="hidden" name="product_conv_qty" id="product_conv_qty" value="<?php if($mode=='Edit' || $mode=='Copy'){ echo $rel['product_conv_qty'];  } else { ?>1<?php } ?>"/>
														<input type="hidden" class="form-control" name="product_base_qty" id="product_base_qty" value="<?php if($mode=='Edit' || $mode=='Copy'){ echo $rel['product_base_qty'];  } else { ?>1<?php } ?>"/>
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="HSN Code" class="col-md-4 control-label">Project HSN Code</label>
													<div class="col-md-8">
														<select class="select2" name="product_hsn" id="product_hsn"  title="Select HSN Code">
												<?=get_hsn($dbcon,$rel['product_hsn']);?>
                                             </select>
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="Product Type" class="col-md-4 control-label">Project Sales Rate</label>
													<div class="col-md-8">
														<input type="text" class="form-control" id="product_sale_rate" name="product_sale_rate" placeholder="Project Sales Rate" value="<?php if($mode=="Edit") { echo $rel['product_icode']; } else { }?>" <?= ($generate_item_code==0) ? "readonly" : ""; ?> />
														<input type="hidden" class="form-control" id="product_icode_code" name="product_icode_code"  value="" readonly />
													</div>
												</div>
											</div>
										</div>
									</div>	
									<div class="">	
										<div class="col-md-12"></div>
										<div class="col-md-12">
											<div class="card">
												<ul class="nav nav-tabs" id="my_tab_id" role="tablist">
													<li role="presentation" id="tab1" class="active"><a href="#product-details" aria-controls="product-details" role="tab" data-toggle="tab">Product Details</a></li>
													<li role="presentation" id="tab2"><a href="#product-desc" aria-controls="product-desc" role="tab" data-toggle="tab">Description</a></li>
												</ul>
												<!-- Tab panes -->
												<div class="tab-content">
													<!-- Remaks Tab Start -->
													<div role="tabpanel" class="tab-pane active" id="product-details">
														<div class="col-md-12">
															<div class="form-group">
																<table cellspacing="10" style="border-collapse:inherit; table-layout: fixed;" id="product_list" class="display table table12 table-striped table-bordered">
																	<tr id="field">
																		<th width="20%" class="text-center">Product Detail</th>
																		<th width="8%" class="text-center">HSN Code</th>
																		<th width="6%" class="text-center">Quantity</th>
																		<th width="7%" class="text-center">Rate</th>
																		<th width="5%" class="text-center"></th>
																	</tr>
																	<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
																	<tr id="field1">
																		<td data-label="PRODUCT DETAIL" style="vertical-align:top;">
																			<input id="product_id" name="product_id" style="width:100%;" placeholder="Select Product" onchange="load_productdetail(this.value);"/>
																		</td>	
																		<td data-label="HSN CODE" style="vertical-align:top;">
																			<select class="select2" name="product_hsn_code" id="product_hsn_code"  title="Select HSN Code">
																				<?=get_hsn($dbcon,$rel['product_hsn_code']);?>
																			</select>
																		</td>
																		<td data-label="QUANTITY" style="vertical-align:top;">
																			<input type="number"  title="Enter Qty"  min="0" id="product_qty" name="product_qty"  class="form-control" />
																		</td>
																		<td data-label="RATE" style="vertical-align:top;">
																			<input type="number"  title="Enter Rate" placeholder="Rate" min="0" id="product_rate" name="product_rate" class="form-control"/>
																		</td>
																		<td style="vertical-align:top;">
																			<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>
																		</td>
																		<input type='hidden' name='edit_id' id='edit_id' value='' />
																	</tr>
																</table>			
															</div>
														</div>
													</div>
													<div class="tab-pane" id="product-desc" >
														<div class="row">
															<div class="col-md-6">
																<div class="form-group">
																	<label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Description</label>
																	<div class="col-md-12">
																		<textarea class="form-control" id="product_des" name="product_des" placeholder="Enter Product Description"><?=$rel['product_des']?></textarea>
																	</div>
																</div>
															</div>
															<div class="col-md-6">
																<div class="form-group">
																	<label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Specification</label>
																	<div class="col-md-12">
																		<textarea class="form-control" id="product_spec" name="product_spec" placeholder="Enter Product Specification"><?=$rel['product_spec']?></textarea> 
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div id="sale_productdata"></div>
										<div class="col-md-12">
											<center>
												<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
												<a href="<?=ROOT.CRM_ROOT.'project_assign_list'?>" type="button" class="btn btn-danger">Cancel</a>
											</center>
										</div>		
									</div>
									<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
									<input type='hidden' name='eid' id='eid' value='<?=$rel['product_id']?>' />
									<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />
								</form>
							</div>	
						</section>
					</div>
				</div>
			</section>
		</section>
		<?php include_once($incPath.'footer.php');?>
	</section>
	<?php include_once($incPath.'include_js_file.php');?>   
	<script src="<?=ROOT.CRM_ROOT?>js/app/project_assign.js?<?=time()?>"></script>
	<script>
			//CKEDITOR.replace('quotation_condition');
			$(".select2").select2({
				width: '100%'
			});
		</script>
		<?
		echo "<script>show_data() </script>";
		?>
		<script>
			CKEDITOR.replace( 'product_des', {
				enterMode: CKEDITOR.ENTER_BR
			});
			CKEDITOR.replace( 'product_spec', {
				enterMode: CKEDITOR.ENTER_BR
			});
		</script>
	</body>
	</html>