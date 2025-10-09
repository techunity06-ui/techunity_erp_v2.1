<?php 
	session_start();
	
	$path = "../../";
    $include = "../../include";
   
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	$form="PO Quotation Approve";
	
	if(strpos($_SERVER[REQUEST_URI], "po_quotation_approve")==true)
	{
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
            PO_QUOTATION_APPROVE
		]);
		if(!in_array(PO_QUOTATION_APPROVE,$bulkAccessArray)){
	        header("Location: ".DOMAIN."permission_access");
	    }

		$mode="Add";
		$approve_indent_id=$dbcon->real_escape_string($_REQUEST['id']);
		
		$query="select arpro.*,tc.cat_name,rpro.*,pro.product_name,unit.unit_name,spro.po_req_no from approve_indent as arpro
			left join tbl_request_product as rpro on rpro.rp_id=arpro.rp_id
			left join product_mst as pro on pro.product_id=rpro.rp_pid
			left join tbl_category as tc on pro.product_category=tc.cat_id
			left join unit_mst as unit on unit.unitid=arpro.approve_unit
			left join tbl_set_main_process as spro on spro.sp_id=rpro.sp_id
		where approve_indent_id=".$approve_indent_id;
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		
	}
	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once($include.'/include_css_file.php');?>
		<style>

 label.radio {
	display: inline-block !important;
	cursor: pointer;
	font-size: 18px; line-height:18px;
	width:auto; font-weight:bold
} input[type=radio] {
 display:none;	
} .radio:before {
	content: "";
	display: inline-block;
	width: 20px;
	height: 20px;
	vertical-align:middle;
	background-color: #EAEAEA;
	color: #F34B31;
	text-align: center;
	box-shadow: inset 0px 2px 3px 0px rgba(0, 0, 0, .3), 0px 1px 0px 0px rgba(255, 255, 255, .8);	
	border-radius: 3px;
}
input[type=radio]:checked + .radio:before {
    content: "\220E";
    text-shadow: 1px 1px 1px rgba(0, 0, 0, .2);
    font-size: 22px;
    text-align: center;
} @media (max-width: 767px) { 
label.radio {

	width:100%
}}
</style>
	</head>
	<body >
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'/include_top_menu.php');?>
			<?php include_once($include.'/left_menu.php');?>
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
									  <li><a href="<?=ROOT.PURCHASE_ROOT.'po_quotation_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="approve_indent_add" action="javascript:;" method="post" name="approve_indent_add">
										<div class="row">
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Indent No </label>
														<div class="col-md-8 col-xs-11">
															<input id="indent_no" name="indent_no" type="text" class="form-control" title="Product Name" value="<?=$rel['indent_no']?>" placeholder="Indent No" readonly >
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Indent Date</label>
														<div class="col-md-8 col-xs-11">
															<input id="indent_date" name="indent_date" type="text" class="form-control" title="Indent Date" value="<?=date('d-m-Y',strtotime($rel['indent_date']))?>" placeholder="Indent Date" readonly >
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Approve No</label>
														<div class="col-md-8 col-xs-11">
															<input id="approve_no" name="approve_no" type="text" class="form-control" title="Approve No" value="<?=$rel['approve_no']?>" placeholder="Approve No" readonly >
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Product Name </label>
														<div class="col-md-8 col-xs-11">
															<input id="product_name" name="product_name" type="text" class="form-control" title="Product Name" value="<?=$rel['product_name']?>" placeholder="Product Name" readonly >
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Product Qty </label>
														<div class="col-md-8 col-xs-11">
															<input id="product_qty" name="product_qty" type="text" class="form-control" title="Product Qty" value="<?=$rel['approve_qty']?>" placeholder="Product Qty" readonly >
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Unit</label>
														<div class="col-md-8 col-xs-11">
															<input id="product_unit" name="product_unit" type="text" class="form-control" title="Product Unit" value="<?=$rel['unit_name']?>" placeholder="Product Unit" readonly >
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Product Category </label>
														<div class="col-md-8 col-xs-11">
															<input type="text" class="form-control" value="<?=$cat_name?>" title="Product Category" readonly>
														</div>
													</div>
												</div>
											</div>	
											<div class="clearfix"></div>
											<div class="col-md-12" style="margin-bottom: 20px;">
												<div class="row">
													<div class="col-xs-1 col-md-4"></div>
														<div class="col-xs-5 col-md-2">
														<input id="ember1142" type="radio" value="1" name="proType" class="ember-view" onchange="show_data()" checked />
														<label class="radio" for="ember1142">&nbsp;&nbsp;Rate </label>
													</div>
														<div class="col-xs-5 col-md-2">
															<input id="ember1143" type="radio" value="2" name="proType"  class="ember-view" onclick="show_data()" />
											<label class="radio" for="ember1143">&nbsp;&nbsp;Payment Days </label>
														</div>
														<div class="col-xs-1 col-md-4"></div>
														<div class="col-xs-5 col-md-2">
															<input id="ember1144" type="radio" value="3" name="proType"  class="ember-view" onclick="show_data();" />
											<label class="radio" for="ember1144">&nbsp;&nbsp;Delivery Date </label>
														</div>
														<div class="col-xs-1 col-md-4"></div>
													</div>
											</div>
											<div class="clearfix"></div>
											<div class="col-md-12">
												<div id="sale_productdata"></div>
											</div>
											<div class="col-md-12" style="display:none;" >
												<center >
												
													<button type="submit" class="btn btn-success" id="save" name="save">SAVE</button>
													
													<a href="<?=ROOT.PURCHASE_ROOT.'po_quotation_list'?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>					
											<input type='hidden' name='mode' id='mode' value='add' />
											
											<input type='hidden' name='approve_indent_id' id='approve_indent_id' value='<?=$rel['approve_indent_id']; ?>' />	
											
											<input type='hidden' name='unit_id' id='unit_id' value='<?=$rel['approve_unit']; ?>' />	
											
										</div>
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once($include.'/footer.php');?>
		</section>
		<?php include_once($include.'/include_js_file.php');?>   
		<script src="<?=ROOT.PURCHASE_ROOT?>js/app/po_quotation_list.js?<?=time()?>"></script>
	

<script>
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
