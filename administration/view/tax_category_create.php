<?php 
	session_start();
	include('../include/urlfile.php');
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
        CREATE_TDS_TAX_CATEGORY_MASTER,
        UPDATE_TDS_TAX_CATEGORY_MASTER,
    ]);

	$form="Tax Category";

	if(strpos($_SERVER['REQUEST_URI'], "tax_category_edit")==false) {
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
		$query="select * from tbl_tax_category where tax_cat_id=$tds_cat_id and isdelete=0";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));

	}
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>TAX CATEGORY</title>
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
					<li><a href="<?=ROOT.ADMINISTRATION_ROOT.'tax_category'?>"><?=$form?> List</a></li>
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
								<label class="col-md-4 control-label">Tax Name *</label>
								<div class="col-md-8 col-xs-11">
									<input type="text" class="form-control" placeholder="" title="" name="tax_cat_name" id="tax_cat_name" value="<?=$rel['tax_cat_name']?>"   />
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-4 control-label">GST % *</label>
								<div class="col-md-8 col-xs-11">
									<input type="text" class="form-control numbersOnly copyPastNotAllowed" placeholder="" title="" name="tax_gst" maxlength="10" id="tax_gst" value="<?=$rel['tax_gst']?>" onkeyup="get_other_tax(this.value)"  />
								</div>
							</div>
						</div>
						
					</div>
				</div>
				
				<div class="row">
					
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">CGST *</label>
							<div class="col-md-8 col-xs-11">
								<input type="text" class="form-control" placeholder="" title="" name="tax_cgst" id="tax_cgst" value="<?=$rel['tax_cgst']?>"  readonly  />
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">SGST % *</label>
							<div class="col-md-8 col-xs-11">
								<input type="text" class="form-control" placeholder="" title="" name="tax_sgst" id="tax_sgst" value="<?=$rel['tax_sgst']?>" readonly  />
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">IGST % *</label>
							<div class="col-md-8 col-xs-11">
								<input type="text" class="form-control" placeholder="" title="" name="tax_igst" id="tax_igst" value="<?=$rel['tax_igst']?>" readonly  />
							</div>
						</div>
					</div>
				</div>
				
				<div class="row">
					
					<div class="col-md-8 col-md-offset-2">
						
						<table class="table table-bordered table-stripped">
							
							<tr>
								<th colspan="3" style="background-color:#F1F2F7;text-align:center">Add Additional Taxes</th>
							</tr>
							
							<tr>
								<th width="50%" style="text-align:center">Select Tax</th>
								<th width="30%" style="text-align:center">Tax Percentage</th>
								<th width="10%" style="text-align:center"></th>
							</tr>
							
							<tr>
								<th>
									<?php $grp_array=implode(",",array(DUTIES_AND_TAXES));
									 ?>
									<select class="select2" name="tax_id" id="tax_id" title="Select Tax Ledger">
										<?=f_get_group_ledger($dbcon,$grp_array,"and l_name NOT IN ('CGST','SGST','IGST')");?>
									</select>
								</th>
								<td>
									<input type="text" class="form-control numbersOnly copyPastNotAllowed" maxlength="10" id="tax_per"  />
								</td>
								<td>
									<input type="button" value="ADD" id="add_tax_btn" class="btn btn-success" onclick="add_tax_percentage()" />
								</td>
							</tr>
							
						</table>
						
					</div>
					
					<div class="col-md-8 col-md-offset-2" id="add_tax_list">
						
						
						
					</div>
					
				</div>
				
				<div class="row" style="margin-top:10px;">
					<div class="col-md-12">	
						<input type="hidden" class="form-control" name="eid" id="eid" value="<?=$mode=='Edit'?$rel['tax_cat_id']:'0'?>" />
						<input type="hidden" class="form-control" name="mode" id="mode" value="<?=$mode;?>" />
						<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
						<a href="<?=ROOT.ADMINISTRATION_ROOT.'tax_category'?>" type="button" class="btn btn-danger">Cancel</a>
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
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/tax_category.js?<?=time()?>"></script>
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
