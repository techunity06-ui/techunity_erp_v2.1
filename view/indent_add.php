<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
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
		
		$query="select rpro.*,pro.product_name,unit.unit_name,tc.cat_name,spro.po_req_no from tbl_request_product as rpro
			left join product_mst as pro on pro.product_id=rpro.rp_pid
			left join tbl_category as tc on pro.product_category=tc.cat_id
			left join unit_mst as unit on unit.unitid=rpro.purchase_unit
			left join tbl_set_main_process as spro on spro.sp_id=rpro.sp_id
		where rp_id=".$workorder_id;
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';

		$approve_no=load_common_no($dbcon,18);
		
		$query_used="select IFNULL(sum(approve_qty),0) as used_qty from approve_indent as rpro
				where approve_indent_status=0 and rp_id=".$rel['rp_id'];
		$rel_used=mysqli_fetch_assoc($dbcon->query($query_used));	
		$max_approve_qty=$rel['rp_po_qty']-$rel_used['used_qty'];
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
									  <li><a href="<?=ROOT.'indent_list'?>"><?=$form?> List</a></li>
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
												<div class="col-md-4">
													<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, false, ''); ?>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Category Name </label>
														<div class="col-md-8 col-xs-11">
															<input id="category_name" type="text" class="form-control" title="Category Name" value="<?=$cat_name?>" placeholder="Category Name" readonly >
														</div>
													</div>
												</div>	
											</div>
											<div class="col-md-12">
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
											</div>
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Product Name</label>
														<div class="col-md-8 col-xs-11">
															<input id="product_name" name="product_name" type="text" class="form-control" title="Product Name" value="<?=$rel['product_name']?>" placeholder="Product Name" readonly >
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Qty</label>
														<div class="col-md-8 col-xs-11">
															<input id="product_qty" name="product_qty" type="text" class="form-control" title="Product Qty" value="<?=$rel['rp_po_qty']?>" placeholder="Product Qty" readonly >
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
															<input id="approve_qty" name="approve_qty" type="number" class="form-control" title="Approve qty" value="<?=$max_approve_qty?>"  max="<?=$max_approve_qty?>" placeholder="Approve qty" >
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
											<div class="clearfix"></div>
											<div class="col-md-12">
												<center>
												
													<button type="submit" class="btn btn-success" id="save" name="save">Approval</button>
													
													<a href="<?=ROOT.'indent_list'?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>					
											<input type='hidden' name='mode' id='mode' value='add' />
											
											<input type='hidden' name='work_order_id' id='work_order_id' value='<?=$rel['rp_id']; ?>' />	
											
											<input type='hidden' name='unit_id' id='unit_id' value='<?=$rel['purchase_unit']; ?>' />	
											
											<input type="hidden" name="max_approve_qty" id="max_approve_qty" value="<?=$max_approve_qty?>" />
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
		<script src="<?=ROOT?>js/app/indent_list.js?<?=time()?>"></script>
	

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
