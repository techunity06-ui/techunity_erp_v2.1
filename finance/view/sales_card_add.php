<?php 
error_reporting(E_ALL);
session_start();
$path = '../../';
$include = '../../include/';
include_once($path."config/config.php");
include_once($path."config/session.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$form="Sales Card";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
$countryid='101';
$stateid='1';
$cityid='1';
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	SALES_CARD_ADD,SALES_CARD_UPDATE
]);
$branch_id = $_SESSION['branch_id'];
if(strpos($_SERVER[REQUEST_URI], "socardedit")==true){
	if(!in_array(SALES_CARD_UPDATE,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$socardid =$dbcon->real_escape_string($_REQUEST['id']);
	$so_card = "select * from tbl_product_party_sales where party_sales_id=".$socardid;
	$rel=mysqli_fetch_assoc($dbcon->query($so_card));
	$sales_card_date = date('d-m-Y',strtotime($rel['sales_card_date']));
	$mode="edit";
	$disable="disabled";
	$isDisabled = true;
	$isRequired = false;
	$branchId=$rel['branch_id'];
}else{
	if(!in_array(SALES_CARD_ADD,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$disable="";
	$back="sales_card_list";
	$mode="Add";
	$isDisabled = false;
	$isRequired = true;
	$sales_card_date=date('d-m-Y');
}
$setconf="select * from tbl_company_configuration where company_id=".$_SESSION['company_id'];
$set_conf=mysqli_fetch_assoc($dbcon->query($setconf));
$so_pro_type = $set_conf['so_pro_type'];
$sales_party_show = $set_conf['sales_party_show'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>SALES CARD</title>
	<?php include_once($include.'/include_css_file.php');?>
	<style>
		#main-content{
			margin-left: 0px;
		}
	</style>
</head>
<body>
	<section id="container" class="sidebar-closed">
		<?php include_once($include.'/include_top_menu.php');?>
		<?php include_once($include.'/left_menu.php');?>
		<section id="main-content">
			<section class="wrapper">
				<?php//include_once('../include/equick_link.php');?>
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<h3><?=$mode.' '.$form?></h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.FINANCE_ROOT.'sales_card_list'?>"><?=$form?> List</a></li>
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
								<div class="row">

								</div>
							</div>
							<form class="form-horizontal" role="form" id="salescard_add" action="javascript:;" method="post" name="salescard_add">

								<div class="row">
									<div class="col-md-12" style="font-size:16px">
										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-3 control-label">Select Type</label>
												<div class="col-md-9 radio">
													<label><input type="radio" name="card_type" id="prod_wise1" value="1" <?=(($rel['card_type']==1) ? "checked" : "checked");?> onclick ="show_card_type();dynamic_table_heading();"><strong>Normal</strong></label>
													<label><input type="radio" name="card_type" id="prod_wise2" value="2" <?=(($rel['card_type']==2) ? "checked" : "");?> onclick ="show_card_type();dynamic_table_heading();"><strong>Party Wise</strong></label>
													<label><input type="radio" name="card_type" id="prod_wise3" value="3" <?=(($rel['card_type']==3) ? "checked" : "");?> onclick ="show_card_type();dynamic_table_heading();"><strong>Group Wise</strong></label>
													<input type="hidden" name="card_type_hi" id="card_type_hi">
												</div>
											</div>
										</div>
									</div>

									<div class="col-md-12" style="font-size:16px">
										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-3 control-label">Transaction Type</label>
												<div class="col-md-9 radio">
													<label><input type="radio" name="trn_type" id="trn_type1" value="1" <?=(($rel['trn_type']==1) ? "checked" : "checked");?> onclick ="show_card_type();dynamic_table_heading();"><strong>Product Wise</strong></label>
													<label><input type="radio" name="trn_type" id="trn_type2" value="2" <?=(($rel['trn_type']==2) ? "checked" : "");?> onclick ="show_card_type();dynamic_table_heading();"><strong>Category Wise</strong></label>

													<input type="hidden" name="trn_type_hi" id="trn_type_hi">
													
												</div>
											</div>
										</div>
									</div>

									<div class="col-md-12">
										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label">Sales Card No : </label>
												<div class="col-md-8 col-xs-11">
													<input type="text" name="sales_card_no" id="sales_card_no" class="form-control" value="<?=$rel['sales_card_no']?>" readonly>
												</div>
											</div>
										</div>	
										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label">Sales Card Date : </label>
												<div class="col-md-8 col-xs-11">
													<input type="text" name="sales_card_date" id="sales_card_date" class="form-control default-date-picker" autocomplete="off" value="<?=$sales_card_date?>">
												</div>
											</div>
										</div>

										<div class="col-md-4">
											<div class="col-md-12 normal_card">
											<div class="col-md-12 party_card">
												<div class="form-group">
													<label class="col-md-4 control-label">Select Party</label>
													<div class="col-md-8 col-xs-11">
														<select class="select2 party_sele" name="party_id" id="party_id" required title="Select Party" <?=$disabled?>>
															<?=getcust($dbcon,$rel['party_id'],SUNDRY_CREDITORS.",".SUNDRY_DEBTORS);?> 
														</select>
													</div>
												</div>
											</div>
											<div class="col-md-12 group_card">
												<div class="form-group">
													<label class="col-md-4 control-label">Select Group</label>
													<div class="col-md-8 col-xs-11">
														<select class="select2 party_sele" name="group_id" id="group_id" required title="Select Party Group" <?=$disabled?>>
															<?=get_all_group($dbcon,$rel['group_id'],"and (g_id = '".SUNDRY_DEBTORS."' OR g_pid = '".SUNDRY_DEBTORS."')",'0');?>
														</select>
													</div>
												</div>
											</div>
										</div>
										</div>
									</div>
									
								</div>
								<hr>
								<div class="row" id="existing_item_div" style="margin-top: 30px">
									<div class="col-md-12" style="height:20px">
									</div>
									<div class="col-md-12" >
										<table class="display table table-bordered table-striped" style="table-layout: fixed;">
											<thead>
												<tr>
													<th class="trn_type_pro">Product</th>
													<th class="trn_type_cat">Category</th>
													<th>Disc(%)</th>
													<!-- <th class="party_card_trn">Rate</th> -->
													<th class="party_card_trn">Unit</th>
													<th>Effective Date</th>
													<th>Valid Date</th>
													<th>Action</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td class="trn_type_pro">
														<select class="select2" title="Select product" name="product_id" id="product_id" onchange="load_product_unit()" >
															<!-- <option value="">Choose Product</option> -->
															<?=getproduct_typewise($dbcon,'',$so_pro_type);?>
														</select>
														<select class="select2" name="currency_id" id="currency_id" title="Select Currency" style="display: none;">
															<?=getcurrency($dbcon,$_SESSION['currency_id']);?>
														</select>
													</td>

													<td class="trn_type_cat">
														<select class="select2" title="Select Category" name="category_id" id="category_id">
															<?=get_all_category($dbcon,'','')?>
														</select>
													</td>

													<td>
														<input id="discount_percentage" name="discount_percentage" type="number" class="form-control" title="Discount Percentage" maxlength="2" placeholder="Discount Percentage" >
													</td>
													<!-- <td class="party_card_trn">
														<input id="price" name="price" type="number" class="form-control" title="Rate" placeholder="Rate" >
													</td> -->
													<td class="party_card_trn">
														<select class="form-control"  title="Select Unit" placeholder="Unit" name="unit_id" id="unit_id">
															<option value="0">Select Unit</option>
														</select>
													</td>
													<td>
														<input id="affected_date" name="affected_date" type="text" class="form-control default-date-picker" title="Date" placeholder="Effective Date">
													</td>
													<td>
														<input id="valid_date" name="valid_date" type="text" class="form-control default-date-picker" title="Date" placeholder="Valid Date">
													</td>
													<td>
														<input type="button" class="btn btn-success" id="save" name="save" onclick="return add_field();" value="Add">
													</td>
													<input type='hidden' name='edit_id' id='edit_id' value='' />
												</tr>
											</tbody>
										</table>
									</div>
									<div class="col-md-12" style="height:20px">
									</div>

									<div class="col-md-12">
										<div class="panel-body">
											<div class="adv-table">
												
											</div>
										</div>
									</div> 
								</div>
								<div class="col-md-12" style="text-align:center;vertical-align:center">
									<input type="submit" class="btn btn-shadow btn-success" name="submit" value="Submit">&nbsp;
									<a href="<?=ROOT.FINANCE_ROOT.'sales_card_list'?>" type="button" class="btn btn-danger">Cancel</a>
								</div>
								<!--Vendor row end-->
								<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
								<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
								<input type='hidden' name='eid' id='eid' value='<?=$rel['party_sales_id']?>' />  
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
	<?php include_once($include.'/footer.php');?>
	<!--footer end-->
</section>
<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'/include_js_file.php');?>
<script src="<?=ROOT.FINANCE_ROOT?>js/app/sales_card.js?<?=time()?>"></script>

<script>
	$(".select2").select2({
		width: '100%',
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
<?php 
if($mode=="Add"){
	echo "<script>get_series_no(".SALES_CARD.");show_card_type();</script>";
}else{
	echo "<script>load_product_unit();show_card_type();</script>";
}
echo "<script>dynamic_table_heading();</script>";
?>
</body>
</html>