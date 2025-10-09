<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
$form="Material Issue";
if(strpos($_SERVER[REQUEST_URI], "materialissueedit")==true){
	$mode="Edit";
	$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from tbl_material_issue where material_id=$invoiceid";
	$rel=mysqli_fetch_assoc($dbcon->query($query));

	$invoice_no=$rel['invoice_no'];
	$challan_no=$rel['challan_no'];
	$load_inv_type=$rel['invoicetype_id'];
	$cust_id=$rel['cust_id'];
}else{
	$mode="Add";
	$date=date('d-m-Y');
	$order_date=date('d-m-Y');
	$load_inv_type='8';
}
$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>MATERIAL ISSUE</title>
	<?php include_once('../include/include_css_file.php');?>
</head>
<body>
	<section id="container" class="sidebar-closed">
		<?php include_once('../include/include_top_menu.php');?>
		<?php include_once('../include/left_menu.php');?>
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.'materialissue_list'?>">Material Issue List</a></li>
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
								<form class="form-horizontal" role="form" id="invoice_add" action="javascript:;" method="post" name="invoice_add">
									<div class="row">
										<input type="hidden" id="invoicetype_id" name="invoicetype_id" value="<?=$load_inv_type;?>">
										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label">Material Issue No *</label>
												<div class="col-md-6 col-xs-11">
													<input id="material_issue_no" name="material_issue_no" type="text" class="form-control" title="Enter Material Issue No" value="<?=$rel['material_issue_no']?>" placeholder="Material Issue No No" required>		
												</div>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label">Date*</label>
												<div class="col-md-6 col-xs-11">
													<input id="invoice_date" name="material_issue_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?phpif($mode=='Add'){echo $date;}else if($mode=='Edit'){echo date('d-m-Y',strtotime($rel['material_issue_date']));}?>" placeholder="Invoice Date">
												</div>
											</div>	
										</div>

										<div class="col-md-12">
											<div class="form-group">
												<table cellspacing="10" style="border-spacing:10px;" id="product_list" class="display table table-bordered table-striped">
													<tr id="field">
														<th width="5%" class="text-center">Work Order</th>
														<th width="20%" class="text-center">Product Detail</th>

														<th width="6%" class="text-center">Issue Qty</th>

														<th width="5%" class="text-center"></th>
													</tr>
													<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
													<tr id="field1">
														<td style="vertical-align:top;">
															<select class="select2" name="work_order_id" id="work_order_id"  title="Select Work Order">
																<?=getallworkorder($dbcon);?>
															</select>
														</td>
														<td style="vertical-align:top;">
															<select class="select2" title="Select product" name="product_id" id="product_id" onChange="load_productdetail(this.value);"><!--load_qty()-->
																<?=getproduct($dbcon,0,'0,1,2,3,4,5')?>
															</select>
														</td>	

														<td style="vertical-align:top;">
															<input type="number" min="0" id="product_qty" name="product_qty"  class="form-control" onKeyUp=""/><br/>
														</td>

														<td style="vertical-align:top;"> 
															<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>	
														</td>
														<input type='hidden' name='edit_id' id='edit_id' value='' />
													</tr>
												</table>								
											</div>
										</div>
										<div id="sale_productdata"></div>
										<div class="clearfix"></div>
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Remarks </label>
												<div class="col-md-6 col-xs-11">
													<textarea id="remark" name="remarks" class="form-control" rows="3"><?=$rel['remarks']?></textarea> 
												</div>
											</div>
										</div>	

										<div class="col-md-12">
											<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
											<!-- <button type="button" onClick="invoice_submit();" class="btn btn-success" id="saveprint" name="saveprint">Save and Print</button> --> &nbsp;
											<a href="<?=ROOT.'materialissue_list'?>" type="button" class="btn btn-danger">Cancel</a>
											<div class="col-md-3"></div>			
										</div>		
									</div>
									<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
									<input type='hidden' name='o_total' id='o_total' value='<?=$rel['g_total']?>' />
									<input type='hidden' name='save_print' id='save_print' value='' />
									<input type='hidden' name='eid' id='eid' value='<?=$rel['material_id']?>' />

								</form>
							</div>	
						</section>
					</div>
				</div>
			</section>
		</section>
		<?php include_once('../include/footer.php');?>
	</section>
	<?php
	include_once('../include/include_js_file.php');
	?>   
	<script src="<?=ROOT?>js/app/materialissue.js?<?php echo time(); ?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});
		$("#product_id").select2({
			width: '100%'
		});

		$('.default-date-picker').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});
		$(".form_datetime-meridian").datetimepicker({
			format: "dd-mm-yyyy HH:ii P",
			showMeridian: true,
			autoclose: true,
			todayBtn: true,
			pickerPosition: "bottom-left"
		});
	</script>
	<?
	if($mode=="Add"){
		echo "<script>load_invoiceno(".$load_inv_type.");</script>";
	}
	?>
</body>
</html>