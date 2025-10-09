<?php 
session_start();
include('../include/urlfile.php');
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	CREATE_TDS_TAX_CATEGORY_MASTER,
	UPDATE_TDS_TAX_CATEGORY_MASTER,
]);

$form="BOM Costing Template";

if(strpos($_SERVER['REQUEST_URI'], "bom_costing_template_edit")==false) {
	if(!in_array(CREATE_TDS_TAX_CATEGORY_MASTER,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$mode="Add";

}
else {
	if(!in_array(UPDATE_TDS_TAX_CATEGORY_MASTER,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}

	$mode="Edit";
	$tds_cat_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from tbl_bom_costing_template where bom_costing_template_id=$tds_cat_id ";
	$rel=brp_mysqli_fetch_assoc($dbcon->query($query));

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title><?=$form?> </title>
	<?php include_once($include.'include_css_file.php');?>
	<style>
		.head_margin
		{
			padding:10px;
		}
		.form_class
		{
			
		}
		.back_head_color
		{
			background-color:#337AB7 !important;
			color:#ffffff !important;
		}
		.row_margin
		{
			margin-top:20px;
		}
		.margin_row
		{
			margin-top:20px;
		}
		
	</style>
</head>
<body>
	<section id="container" >
		<?php include_once($include.'include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once($include.'left_menu.php');?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">			
				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3><?=$mode.' '.$form?></h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.ADMINISTRATION_ROOT.'bom_costing_template'?>"><?=$form?> List</a></li>
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
								New <?=$form?>
							</header>

							<div class="panel-body ">
								<form class="form-horizontal" role="form" id="tax_category_add" method="post" name="tax_category_add">
									<div class="row">

										<div class="col-md-12 margin_row">
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label" style="white-space:nowrap;">Template Name *</label>
													<div class="col-md-8 col-xs-11">
														<input type="text" class="form-control" placeholder="" title="" name="template_name" id="template_name" value="<?=$rel['template_name']?>"   />
													</div>
												</div>
											</div>
										</div>
										<div class="row">

											<div class="col-md-12 ">

												<table class="table table-bordered table-stripped">

													<tr>
														<th style="background-color:#F1F2F7;text-align:center">Type Name</th>
														<th style="background-color:#F1F2F7;text-align:center">Type</th>
														<th style="background-color:#F1F2F7;text-align:center">Per(%)</th>
														<th style="background-color:#F1F2F7;text-align:center">Amount</th>
														<th style="background-color:#F1F2F7;text-align:center">Action</th>
													</tr>

													<tr>
														<td>
															<input type="text" class="form-control"  id="type_name" name="type_name" />
														</td>
														<td>
															<select class="select2" name="type" id="type" title="Select Type">
																<option value="0" >Additive</option>
																<option value="1" >Subtractive</option>
															</select>
														</td>
														<td>
															<input type="text" class="form-control numbersOnly " maxlength="10" id="per" name="per"  />
														</td>
														<td>
															<input type="text" class="form-control numbersOnly " maxlength="10" id="amount" name="amount"  />
														</td>
														<td>
															<input type="button" value="ADD" id="add_tax_btn" class="btn btn-success" onclick="add_tax_percentage()" />
														</td>
													</tr>
													<input type="hidden" name="edit_id" id="edit_id" value="" />
												</table>

											</div>
											<div class="col-md-12" id="add_tax_list">
											</div>
										</div>

										<div class="row" style="margin-top:10px;">
											<div class="col-md-12">	
												<input type="hidden" class="form-control" name="eid" id="eid" value="<?=$mode=='Edit'?$rel['bom_costing_template_id']:'0'?>" />
												<input type="hidden" class="form-control" name="mode" id="mode" value="<?=$mode;?>" />
												<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
												<a href="<?=ROOT.ADMINISTRATION_ROOT.'bom_costing_template'?>" type="button" class="btn btn-danger">Cancel</a>
												<div class="col-md-3"></div>			
											</div>
										</div>					

									</form>
								</div>
							</section>

						</div>


					</div>
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
		<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/bom_costing_template.js?<?=time()?>"></script>
		<script>
			$(".select2").select2({
				width: '80%'
			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});

		</script>
		<?php

		?>
	</body>
	</html>
