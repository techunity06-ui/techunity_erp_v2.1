<?php 
session_start();

include('../include/urlfile.php');	

$form="Salesorder Deallocate";

$companyConfiguration=getCompanyConfiguration($dbcon);
$type_conf = $companyConfiguration['production_pro_type'];
$pro_search = $companyConfiguration['bom_pro_search'];	
$back_link = ROOT.INVENTORY_ROOT.'salesorder_deallocate_stock_list';
$mode = "Add";
$so_deallocate_date=date('d-m-Y');
$readonly = "";

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    INVENTORY_SO_DEALLOCATE_LIST_SLUG_VIEW,INVENTORY_SO_DEALLOCATE_LIST_SLUG_CREATE,INVENTORY_SO_DEALLOCATE_LIST_SLUG_DELETE
	]);

if(!in_array(INVENTORY_SO_DEALLOCATE_LIST_SLUG_CREATE,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}

$query = "SELECT so.sales_order_no,so_trn.sales_order_id FROM tbl_reserve_stock AS res LEFT JOIN tbl_sales_ordertrn AS so_trn ON res.sales_order_trn_id = so_trn.sales_ordertrn_id LEFT JOIN tbl_sales_order AS so ON so.sales_order_id = so_trn.sales_order_id WHERE so_trn.sales_ordertrn_status != 2 AND so_trn.invoice_status = 0 GROUP BY so_trn.sales_order_id";

$result = $dbcon->query($query);

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Salesorder Deallocate Create</title>
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
										<li><a href="<?=$back_link?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="so_deallocate" action="javascript:;" method="post" name="so_deallocate">
										<div class="row">
											<input type="hidden" name="inquiry_type" id="inquiry_type" value="1">
											<input type='hidden' name='mode' id='mode' value="<?=$mode?>" />
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Deallocate No *</label>
														<div class="col-md-8 col-xs-11">
														<input id="so_deallocate_no" name="so_deallocate_no"  type="text" class="form-control" title="Quantity" value="<?=@$so_deallocate_no?>" readonly placeholder="Enter Material Issue Number">
														</div>
													</div>
												</div>
												<div class="col-md-6">	
													<div class="form-group">
														<label class="col-md-4 control-label">Deallocate Date </label>
														<div class="col-md-8 col-xs-11">
														<input id="so_deallocate_date" name="so_deallocate_date" type="text" class="form-control default-date-picker required valid" title="Salesorder Deallocate Date" value="<?=$so_deallocate_date?>" readonly placeholder="Salesorder Deallocate Issue Date">
														</div>
													</div>
												</div>
											</div>
											
											<div class="col-md-12">
								<div class="form-group">
									<table cellspacing="10" style="border-collapse:inherit;table-layout: fixed;" id="product_list" class="display table table-bordered table-striped">
										<tr id="field">
											<th width="20%" class="text-center">Salesorder No</th>
											<th width="20%" class="text-center">Product Name</th>
											<th width="15%">Salesorder QTY</th>
											<th width="15%">Reserve Stock</th>
											<th width="15%" class="text-center">Deallocate Stock</th>
											<th width="10%" class="text-center"></th>
										</tr>
										<tr id="field1">
											<td style="vertical-align:top;" width="25%">
												<select class="select2" id="sales_order_id" name="sales_order_id" onchange="get_salesorder_product(this.value)">
													<option value="">Select Salesorder</option>
													<?
														while($row = brp_mysqli_fetch_assoc($result)){
															echo '<option value="'.$row['sales_order_id'].'">'.$row['sales_order_no'].'</option>';
														}
													?>
												</select>
											</td>
											<td style="vertical-align:top;" width="25%">
												<select class="select2" id="sales_ordertrn_id" name="sales_ordertrn_id" onchange="load_product_detail(this.value);">
												</select>
												<input type="hidden" name="product_id" id="product_id">
											</td>	
											<td>
				                                 <input type="number" class="form-control" id="so_qty" onkeypress="return isNumberKey(event)" readonly />

				                                 <span class="unitname text-success" style="font-size: 16px;font-weight: 900;"></span>
				                             </td>
											<td>
				                                 <input type="number" class="form-control" id="reserve_qty" onkeypress="return isNumberKey(event)" readonly />

				                                 <span class="unitname text-success" style="font-size: 16px;font-weight: 900;"></span>
				                             </td>
											
											
											<td style="vertical-align:top;" class="hide_act_add">
												<input type="number"  title="Enter Qty" name="de_allocate_qty" id="de_allocate_qty" onkeypress="return isNumberKey(event)" value=""  class="form-control" />
												<span class="unitname text-success" style="font-size: 16px;font-weight: 900;"></span>
											</td>
													
											<td style="vertical-align:top;">
												<input type="button" class="btn btn-primary  product_add_direct" value="ADD"  style="" onclick="add_field()" id="btn_add_field" />

												<input type="button"  name="addrow1" id="addrow1" onClick="open_batch_wise_qty()"  class="btn btn-primary product_add_batch_wise" value="Add" />
											</td>
											<input type='hidden' name='edit_id' id='edit_id' value="" />
											<input type='hidden' name='unit_id' id='unit_id' value="" />
											<input type="hidden" id="isbatchwise" name="isbatchwise" value="">
										</tr>
									</table>			
								</div>
							</div>
											<div class="mtop20" id="so_deallocate_temp_div"> </div>
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
		<?php include_once($include1.'so_deallocate_batch_wise.php');?>   
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/salesorder_deallocate_stock.js?<?php echo time(); ?>"></script>
		<script src="<?=ROOT?>js/advanced-form-components.js"></script>
	<script>
		$(document).ready(function() {
			delete_tempout_data();
			 get_so_deallocate_no();
		 	
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
