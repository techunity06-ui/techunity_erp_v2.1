<?php 
	session_start();
	include('../include/urlfile.php');
	//echo COMMON_FUNCTION_PATH;
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
        CREATE_TDS_TAX_CATEGORY_MASTER,
        UPDATE_TDS_TAX_CATEGORY_MASTER,
    ]);

	$form="TDS Tax Category";

	if(strpos($_SERVER['REQUEST_URI'], "tds_tax_category_edit")==false) {
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
		$query="select * from tbl_tds_tax_category where tds_cat_id=$tds_cat_id and isdelete=0";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));

        if(!$rel){
            header("Location: ".ROOT."tds_tax_category_list");
        }

		$form_type = $rel['l_form'];
		$form_id = $rel['l_form_id'];
		if($rel['is_deletable'] == 1){
			$readonly='readonly';
		}

	}
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>TDS TAX CATEGORY</title>
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
					<li><a href="<?=ROOT ?>masters_list">Master List</a></li>
					<li><a href="<?=ROOT.ADMINISTRATION_ROOT.'tds_tax_category_list'?>"><?=$form?> List</a></li>
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
				<form class="form-horizontal" role="form" id="tds_tax_category_add" method="post" name="tds_tax_category_add">
					<input type='hidden' name='eid' id='eid' value='<?=$rel['tds_cat_id']?>' />
					
				<div class="row">
					
					<div class="col-md-12 margin_row">
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-4 control-label">Tds Category *</label>
								<div class="col-md-8 col-xs-11">
									<input type="text" class="form-control" placeholder="Tds Category" title="Tds Category" name="tds_cat_name" id="tds_cat_name" value="<?=$rel['tds_cat_name']?>" <?=$readonly?> required  />
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-4 control-label">Section code *</label>
								<div class="col-md-8 col-xs-11">
									<input type="text" class="form-control" placeholder="Section code" title="Section code" name="tds_section" id="tds_section" value="<?=$rel['tds_section']?>" <?=$readonly?>  />
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-4 control-label">Date *</label>
								<div class="col-md-8 col-xs-11">
								<?php if($rel['tds_date'] != '')
								{
									$date = date("d-m-Y",strtotime($rel['tds_date']));
								}
								else
								{
										$date = date("d-m-Y");
								} ?>
								
									<input type="text" class="form-control default-date-picker" placeholder="Date" autocomplete="off" title="Date" name="tds_date" id="tds_date" value="<?=$date?>" <?=$readonly?>  />
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					
					<div class="col-md-12 margin_row">
						<div class="col-md-4">
							<div class="form-group">					
								<label class="col-md-4 control-label">Effective Ledger*</label>
								<div class="col-md-8 col-xs-11" >
									<?php $grp_array=implode(",",array(DUTIES_AND_TAXES)); ?>
									<select class="select2" name="effective_ledger_id" id="effective_ledger_id" <?= isset($readonly) ? 'disabled' : '' ?> required>
										<?=f_get_group_ledger($dbcon,$grp_array,'',$rel['effected_ledger_id']);?>
									</select>
								</div>
							</div>
						</div>
					</div><br><br><br><br>
					<div class="col-md-12">
						<div class="form-group">
							<table cellspacing="10" style="border-spacing:10px;" id="product_list" class="display table table-bordered table-striped">
								<tr id="field">
									<th width="15%" class="text-center">Payee Category</th>
									<th width="10%" class="text-center">Threshold Limit</th>
									<th width="8%" class="text-center">TDS(With PAN)%</th>
									<th width="6%" class="text-center">TDS(Without PAN)%</th>
									<th width="7%" class="text-center">Surcharge %</th>
									<th width="5%" class="text-center"></th>
								</tr>
								<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
								<tr id="field1">
									
									<td style="vertical-align:top;">
										<?php
											$str='';
											$query="SELECT cm.common_mst_name,cm.common_mst_id FROM `tbl_common_mst` as cm join tbl_common_category_mst as ccm on ccm.common_category_id=cm.common_category_id and ccm.common_category_name='TDS Payee' WHERE cm.isdelete=0";
											$rs_dispatch=$dbcon->query($query);	
										?>
										<select class="select2" name="common_mst_id" id="common_mst_id" >
	                    					<option value="">Select Payee Category</option>
	                    					<?php 
	                    						while($rel=brp_mysqli_fetch_assoc($rs_dispatch))
												{													
													$str .= '<option '.$sel.' value="'.$rel['common_mst_id'].'">'.$rel['common_mst_name'].'</option>';
												}
												echo $str;
	                    					?>
	                					</select>			
									</td>	
									<td style="vertical-align:top;">
										<input type="text"  title="Threshold Limit" placeholder="Threshold Limit" id="tds_thresold_limit" name="tds_thresold_limit" class="form-control numbersOnly copyPastNotAllowed" maxlength="15" />
									</td>
									<td style="vertical-align:top;">
										<input type="text"  title="TDS(With PAN)%" placeholder="TDS(With PAN)%" id="tds_with_pan" name="tds_with_pan" class="form-control numbersOnly copyPastNotAllowed" maxlength="15"/>
									</td>
									<td style="vertical-align:top;">
										<input type="text"  title="TDS(Without PAN)%" placeholder="TDS(Without PAN)%" id="tds_without_pan" name="tds_without_pan" class="form-control numbersOnly copyPastNotAllowed" maxlength="15"/>
									</td>
									<td style="vertical-align:top;">
										<input type="text"  title="Surcharge%" placeholder="Surcharge%" id="tds_surcharge" name="tds_surcharge" class="form-control numbersOnly copyPastNotAllowed" maxlength="15" />
									</td>
									<td style="vertical-align:top;"> 
										<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>	
									</td>
									<input type='hidden' name='edit_id' id='edit_id' value='' />
								</tr>
							</table>								
						</div>
					</div><br><br><br><br>
					<div id="sale_productdata"></div>
					<div class="clearfix"></div>
					<div class="col-md-12 col-md-offset-5 row_margin" >
				
						<input type="hidden"  value="" id="form_type" name="form_type"  />
						<input type='hidden' name='mode' id='mode' value='<?php if($mode=='Edit') { echo "edit"; } else { echo "add"; } ?>' />
						<button type="submit" name="" id="btn_submit" class="btn btn-success">Submit</button>
						<a class="btn btn-danger" href="<?=ROOT.ADMINISTRATION_ROOT.'tds_tax_category_list'?>">Cancel</a>
					
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
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/tds_tax_category.js?<?=time()?>"></script>
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
