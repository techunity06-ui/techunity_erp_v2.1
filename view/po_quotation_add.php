<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="PO Quotation";
	
	$disabled = '';
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
            PO_QUOTATION_ADD, PO_QUOTATION_UPDATE
	]);
	$branch_id = $_SESSION['branch_id'];
	if(strpos($_SERVER['REQUEST_URI'], "po_quotation_add")==true)
	{
	    if(!in_array(PO_QUOTATION_ADD,$bulkAccessArray)){
	        header("Location: ".DOMAIN."permission_access");
	    }

		$mode="add";
		$approve_indent_id=$dbcon->real_escape_string($_REQUEST['id']);
		
		$query="select arpro.*,tc.cat_name,rpro.*,pro.product_name,unit.unit_name,spro.po_req_no from approve_indent as arpro
			left join tbl_request_product as rpro on rpro.rp_id=arpro.rp_id
			left join product_mst as pro on pro.product_id=rpro.rp_pid
			left join tbl_category as tc on pro.product_category=tc.cat_id
			left join unit_mst as unit on unit.unitid=arpro.approve_unit
			left join tbl_set_main_process as spro on spro.sp_id=rpro.sp_id
		where approve_indent_id=".$approve_indent_id;
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$quotation_no = $rel['quotation_no'];
		$quotation_date = date('d-m-Y');
		$delivery_date = date('d-m-Y');
		$payment_days = $rel['payment_days'];
		$product_rate = $rel['product_rate'];
		$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		
	}
	if(strpos($_SERVER['REQUEST_URI'], "po_quotation_edit")==true)
	{
	    if(!in_array(PO_QUOTATION_UPDATE,$bulkAccessArray)){
	        header("Location: ".DOMAIN."permission_access");
	    }

		$disabled = 'disabled';
		$mode="vendor_quotation_edit";
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

		$po_quotation_id=$dbcon->real_escape_string($_REQUEST['poid']);

		$sql = "SELECT pq.*, `l`.`l_name` FROM `po_quotation` as pq
				left join tbl_ledger as l ON `pq`.`vender_id`=`l`.`l_id` 
				WHERE `pq`.`approve_indent_id` = '".$approve_indent_id."' 
				AND `pq`.`po_quotation_id` = '".$po_quotation_id."' 
				AND `pq`.`po_quotation_status`='0' 
				AND `pq`.`user_id` = '".$_SESSION['user_id']."' 
				AND `pq`.`company_id` = '".$_SESSION['company_id']."'";
				
   		$result=mysqli_fetch_assoc($dbcon->query($sql));	
		$vender_id = $result['vender_id'];
		$quotation_no = $result['quotation_no'];
		$quotation_date = date('d-m-Y', strtotime($result['quotation_date']));
		$delivery_date = date('d-m-Y', strtotime($result['delivery_date']));
		$payment_days = $result['payment_days'];
		$product_rate = $result['product_rate'];
	}
	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../include/include_css_file.php');?>
	</head>
	<body >
		<section id="container" class="sidebar-closed">
			<?php include_once('../include/include_top_menu.php');?>
			<?php include_once('../include/left_menu.php');?>
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
									  <?php if($_REQUEST['poid']!=''){ ?>
									  <li><a href="<?=ROOT.'po_quotation_vendor_list'?>/<?=$_REQUEST['id']?>"><?=$form?> Vendor List</a></li>
									<?php } else { ?>
										<li><a href="<?=ROOT.'po_quotation_list'?>"><?=$form?> List</a></li>
									<?php } ?>		
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
														<label class="col-md-4 control-label">Vendor Name *</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="vender_id" id="vender_id" required title="Select Vender"  <?=$disabled?>>
																<?=getcust($dbcon,$vender_id,'');?>	
															</select>
														</div>
													</div>
												</div>
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
											</div>
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Quotation No *</label>
														<div class="col-md-8 col-xs-11">
															<input id="quotation_no" name="quotation_no" type="text" class="form-control" title="Quotation No" value="<?=$quotation_no?>" placeholder="Quotation No" required>
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Quotation Date </label>
														<div class="col-md-8 col-xs-11">
															<input id="quotation_date" name="quotation_date" type="text" class="form-control default-date-picker" title="Date" value="<?php echo $quotation_date;  ?>" placeholder="Quotation Date"  >
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Delivery Date</label>
														<div class="col-md-8 col-xs-11">
															<input id="delivery_date" name="delivery_date" type="text" class="form-control default-date-picker" title="Date" value="<?php echo $delivery_date; ?>" placeholder="Delivery Date" >
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Payment Days *</label>
														<div class="col-md-8 col-xs-11">
															<input id="payment_days" name="payment_days" type="number" class="form-control" title="Payment Days" value="<?=$payment_days?>" placeholder="Payment Days" required/>
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Rate *</label>
														<div class="col-md-8 col-xs-11">
															<input id="product_rate" name="product_rate" type="text" class="form-control" title="Product Rate" value="<?=$product_rate?>" placeholder="Product Rate" required>
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
													<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], true, false, ''); ?>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Product Category</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" class="form-control" value="<?=$cat_name?>" title="Product Category" readonly>
														</div>
													</div>
												</div>	
											</div>
											<div class="clearfix"></div>
											<div class="col-md-12">
												<center>
													<button type="submit" class="btn btn-success" id="save" name="save">SAVE</button>
													<a href="<?=ROOT.'po_quotation_list'?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>					
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type='hidden' name='approve_indent_id' id='approve_indent_id' value='<?=$rel['approve_indent_id']; ?>' />
											<input type='hidden' name='po_quotation_id' id='po_quotation_id' value='<?=$result['po_quotation_id']; ?>' />	
											<input type='hidden' name='unit_id' id='unit_id' value='<?=$rel['approve_unit']; ?>' />	
										</div>
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once('../include/footer.php');?>
		</section>
		<?php include_once('../include/include_js_file.php');?>   
		<script src="<?=ROOT?>js/app/po_quotation_list.js?<?=time()?>"></script>
	

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
