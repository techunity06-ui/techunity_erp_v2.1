<?php 
session_start();
include('../include/urlfile.php');	
$form="Approval Indent";
$branch_id = $_SESSION['branch_id'];
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	INDENT_APPROVE
]);

if(!in_array(INDENT_APPROVE,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
if(strpos($_SERVER['REQUEST_URI'], "indent_approve")==true)
{
	$mode="Add";
	$workorder_id=$dbcon->real_escape_string($_REQUEST['id']);
	$getspecialConfiguration=getspecialConfiguration($dbcon);
	$query="select rpro.*, pro.product_name, pro.product_base_unit, pro.product_conv_unit, pro.product_desc, unit.unit_name, tc.cat_name, spro.po_req_no from tbl_request_product as rpro
	left join product_mst as pro on pro.product_id=rpro.rp_pid
	left join tbl_category as tc on pro.product_category=tc.cat_id
	left join unit_mst as unit on unit.unitid=rpro.purchase_unit
	left join tbl_set_main_process as spro on spro.sp_id=rpro.sp_id
	where rp_id=".$workorder_id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));	

	$rel['product_desc'] = !empty($rel['product_remark']) ? $rel['product_remark'] : (isset($rel['product_desc']) ? $rel['product_desc'] : '');

	$ret_req_conv = '';
	if($rel['product_base_unit'] != $rel['product_conv_unit']){
		if($rel['purchase_unit'] == $rel['product_base_unit']){
			$type="conv_unit";
			$unit_name  = getunitname($dbcon,$rel['product_conv_unit']);
			$ret_qty=convert_stock($dbcon,$rel['rp_po_qty'],$rel['rp_pid'],$type);
			$ret_req_conv='<span style="color:orange"> Conv Unit : '.round_up($ret_qty,5).' '.$unit_name.'</span>';
		}else{
			$type="base_unit";
			$unit_name  = getunitname($dbcon,$rel['product_base_unit']);
			$ret_qty=convert_stock($dbcon,$rel['rp_po_qty'],$rel['rp_pid'],$type);
			$ret_req_conv='<span style="color:green"> Base Unit : '.round_up($ret_qty,5).' '.$unit_name.'</span>';
		}	
	}
	

	$remark = '';

	$que = "select req.pre_trn_id, req.rp_req_type, pre.remark from tbl_request_product as req 
	left join tbl_pre_trn as ptr on ptr.pre_trn_id=req.pre_trn_id
	left join tbl_pre as pre on pre.pre_id = ptr.pre_id
	where req.rp_req_type='direct' and req.rp_id=".$rel['rp_id'];

	$result = $dbcon->query($que);
	$res = brp_mysqli_fetch_array($result);

	if($res['rp_req_type']=='direct' && $res['remark']!=''){
		$remark = $res['remark'];
	}

	if($getspecialConfiguration['hermattic_permission']==1){
		$so_no = "select sales_order_trn_id from tbl_request_product where sp_id =".$rel['sp_id']." and main_request=1";
		$q = $dbcon->query($so_no);
		$r = brp_mysqli_fetch_array($q);

		$get_so = "select so.sales_order_no from tbl_sales_ordertrn as trn
		left join tbl_sales_order as so on so.sales_order_id = trn.sales_order_id
		where trn.sales_ordertrn_id=".$r['sales_order_trn_id']; 

		$exe = $dbcon->query($get_so);
		$res = brp_mysqli_fetch_array($exe);
	}

	$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';

	$approve_no=load_common_no($dbcon,JOURNAL_SERIES);

	$query_used="select IFNULL(sum(approve_qty),0) as used_qty,IFNULL(sum(approve_base_qty),0) as used_base_qty from approve_indent as rpro
	where approve_indent_status=0 and rp_id=".$rel['rp_id'];
	$rel_used=mysqli_fetch_assoc($dbcon->query($query_used));	
	$max_approve_qty=$rel['rp_po_qty']-$rel_used['used_qty'];
	$max_approve_conv_qty = $rel['rp_po_base_qty'] - $rel_used['used_base_qty'];
}
$companyConfiguration=getCompanyConfiguration($dbcon);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>INDENT APPROVAL</title>
	<?php include_once($include.'/include_css_file.php');?>
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
									<li><a href="<?=ROOT.PURCHASE_ROOT.'indent_list'?>"><?=$form?> List</a></li>
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
													<label class="col-md-4 control-label">Approve No </label>
													<div class="col-md-8 col-xs-11">
														<input id="approve_no" name="approve_no" type="text" class="form-control" title="Approve No" value="<?=$approve_no?>" placeholder="Purchase Order No" readonly >
													</div>
												</div>
											</div>
											<?php if($companyConfiguration['branch_wise_manage']==1) { ?>
												<div class="col-md-4">
													<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, false, ''); ?>
												</div>
											<?php } ?>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Category Name </label>
													<div class="col-md-8 col-xs-11">
														<input id="category_name" type="text" class="form-control" title="Category Name" value="<?=$cat_name?>" placeholder="Category Name" readonly >
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Indent No </label>
													<div class="col-md-8 col-xs-11">
														<input id="indent_no" name="indent_no" type="text" class="form-control" title="Date" value="<?=$rel['indent_no']?>" placeholder="Purchase Order No" readonly >
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Indent Date </label>
													<div class="col-md-8 col-xs-11">
														<input id="indent_date" name="indent_date" type="text" class="form-control default-date-picker" title="Date" value="<?php echo date('d-m-Y',strtotime($rel['indent_date'])); ?>" placeholder="Indent Date" readonly >
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Workorder No</label>
													<div class="col-md-8 col-xs-11">
														<input id="workorder_no" name="workorder_no" type="text" class="form-control" title="Workorder No" value="<?=$rel['po_req_no']?>" placeholder="Workorder No" readonly >
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Product Name</label>
													<div class="col-md-8 col-xs-11">
														<input id="product_name" name="product_name" type="text" class="form-control" title="Product Name" value="<?=$rel['product_name']?>" placeholder="Product Name" readonly >

														<input type="hidden" name="product_id" name="product_id" value="<?=$rel['rp_pid']?>">
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Qty</label>
													<div class="col-md-8 col-xs-11">
														<input id="product_qty" name="product_qty" type="text" class="form-control" title="Product Qty" value="<?=round_up($rel['rp_po_qty'],5)?>" placeholder="Product Qty" readonly >
														<?=$ret_req_conv?>
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
										<div class="col-md-12" style="margin-top:15px">
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Delivery date </label>
													<div class="col-md-8 col-xs-11">
														<input id="delivery_date" name="delivery_date" type="text" class="form-control default-date-picker" title="Date" value="<?php echo date("d-m-Y"); ?>" placeholder="Delivery Date">
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Approve Qty</label>
													<div class="col-md-8 col-xs-11">
														<?php 
														if($getspecialConfiguration['filter_concept_permission']==1){ ?>
															
															<input id="approve_qty" name="approve_qty" type="text" class="form-control numbersOnly" title="Approve qty" value="<?=round_up($max_approve_qty,5)?>"  placeholder="Approve qty" >
														<?php } else { ?>
															<input id="approve_qty" name="approve_qty" type="text" class="form-control numbersOnly" title="Approve qty" value="<?=round_up($max_approve_qty,5)?>"  max="<?=round_up($max_approve_qty,5)?>" placeholder="Approve qty" >
															
														<?php } ?>

														<input type="hidden" name="apr_qty" id="apr_qty" value="<?=round_up($max_approve_qty,5)?>">
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Quotation Requirement</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="quotation_requirement" id="quotation_requirement">
															<option value="0">No</option>
															<option value="1">Yes</option>
														</select>
													</div>
												</div>
											</div>
										</div>
										<?php if($getspecialConfiguration['hermattic_permission']==1){?>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Sales Order No.</label>
													<div class="col-md-8 col-xs-11">
														<input id="sales_order_no" name="sales_order_no" type="text" class="form-control" title="Sales Order No" value="<?=$res['sales_order_no']?>"   placeholder="Sales Order No" readonly>
													</div>
												</div>
											</div>
										<?php } ?>
									</div>
									<?php if($companyConfiguration['po_work_order_wise']==1) { ?>
										<div class="col-md-12">
											<div class="form-group">
												<label for="Product Description" class="col-md-4 control-label" style="text-align : left">Description</label>
												<div class="col-md-12 col-xs-11">
													<textarea class="form-control" id="product_desc" name="product_desc" placeholder="Enter Product Description"><?= $rel['product_desc'] ?></textarea>
												</div>
											</div>
										</div>
									<?php } ?>
									<div class="clearfix"></div>
									<div class="col-md-12">
										<center>
											
											<button type="submit" class="btn btn-success" id="save" name="save">Approval</button>

											<a href="<?=ROOT.PURCHASE_ROOT.'indent_list'?>" type="button" class="btn btn-danger">Cancel</a>
										</center>
									</div>					
									<input type='hidden' name='mode' id='mode' value='add' />

									<input type='hidden' name='work_order_id' id='work_order_id' value='<?=$rel['rp_id']; ?>' />	

									<input type='hidden' name='unit_id' id='unit_id' value='<?=$rel['purchase_unit']; ?>' />	

									<input type="hidden" name="max_approve_qty" id="max_approve_qty" value="<?=$max_approve_qty?>" />
									<input type="hidden" name="max_approve_conv_qty" id="max_approve_conv_qty" value="<?=$max_approve_conv_qty?>" />
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
<script src="<?=ROOT.PURCHASE_ROOT?>js/app/indent_list.js?<?=time()?>"></script>


<script>
	<?php if($companyConfiguration['po_work_order_wise']==1){?>
		CKEDITOR.replace('product_desc', {
			enterMode: CKEDITOR.ENTER_BR
		});
		<?php }?>
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
		$('#approve_qty').keyup(function(e) {
			var txtVal = $(this).val();
			$('#apr_qty').val(txtVal);
		});
	</script>
</body>
</html>
