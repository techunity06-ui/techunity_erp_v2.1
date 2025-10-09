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
	ELCON_SALES_CARD_ADD,ELCON_SALES_CARD_UPDATE
]);
$branch_id = $_SESSION['branch_id'];
if(strpos($_SERVER[REQUEST_URI], "elconsocardedit")==true){
	if(!in_array(ELCON_SALES_CARD_UPDATE,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$socardid =$dbcon->real_escape_string($_REQUEST['id']);
	$so_card = "select * from tbl_product_sales_elcon where elcon_sales_id=".$socardid;
	$rel=mysqli_fetch_assoc($dbcon->query($so_card));
	$sales_card_date = date('d-m-Y',strtotime($rel['sales_card_date']));
	$mode="edit";
	$disable="disabled";
	$isDisabled = true;
	$isRequired = false;
	$branchId=$rel['branch_id'];
}else{
	if(!in_array(ELCON_SALES_CARD_ADD,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$disable="";
	$back="elcon_sales_card_list";
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
				<?php //include_once('../include/equick_link.php');?>
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<h3><?=$mode.' '.$form?></h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.FINANCE_ROOT.'elcon_sales_card_list'?>"><?=$form?> Wise</a></li>
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
							<form class="form-horizontal" role="form" id="elcon_salescard_add" action="javascript:;" method="post" name="elcon_salescard_add">

								<div class="row">
									<div class="col-md-12" style="font-size:16px">
										<div class="col-md-6" style="display: none;">
											<div class="form-group">
												<div class="radio">
													<label><input type="radio" name="card_type" id="prod_wise" value="1" checked <?=$disable?>><strong>Product Wise</strong></label>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-12">
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label" style="text-align:left">Sales Card No : </label>
												<div class="col-md-6 col-xs-11">
													<input type="text" name="sales_card_no" id="sales_card_no" class="form-control" value="<?=$rel['sales_card_no']?>" readonly>
												</div>
											</div>
										</div>	

										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label" style="text-align:left">Sales Card Date : </label>
												<div class="col-md-6 col-xs-11">
													<input type="text" name="sales_card_date" id="sales_card_date" class="form-control default-date-picker" autocomplete="off" value="<?=$sales_card_date?>">
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
													<th colspan="9" style="height:20px;text-align:center">Sales Rate Details</th>
												</tr>
												<tr>
													<th>Product Category</th>
													<th>Rate</th>
													<th>Unit</th>
													<th>Rate1</th>
													<th>Rate2</th>
													<th>Rate3</th>
													<th>Effect Date</th>
													<th>Valid Date</th>
													<th>Action</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td>
														<select class="select2" title="Select product" name="product_cat_id" id="product_cat_id">
															<?=get_all_category($dbcon,'',' AND cat_pid = 0');?>
														</select>
														<select class="select2" name="currency_id" id="currency_id" title="Select Currency" style="display: none;">
															<?=getcurrency($dbcon,'1');?>
														</select>
													</td>
													<td>
														<input id="price" name="price" type="number" class="form-control" title="Rate" placeholder="Rate" >
													</td>
													<td>
														<select class="select2"  title="Select Unit" placeholder="Unit" name="unit_id" id="unit_id">
															<?=getunit($dbcon,'');?>
														</select>
													</td>
													<td>
														<input id="rate1" name="rate1" type="number" class="form-control" title="Rate1" maxlength="2" placeholder="Rate1" >
													</td>
													<td>
														<input id="rate2" name="rate2" type="text" class="form-control" title="Rate2" placeholder="Rate2">
													</td>
													<td>
														<input id="rate3" name="rate3" type="text" class="form-control" title="Rate3" placeholder="Rate3">
													</td>
													<td>
														<input id="effected_date" name="effected_date" type="text" class="form-control default-date-picker" title="Date" placeholder="Effective Date">
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
												<table class="display table table-bordered table-striped" id="item_data_table">
													<thead>
														<tr>
															<th>Product Category</th>
															<th>Rate</th>
															<th>Unit</th> 
															<th>Rate1</th>
															<th>Rate2</th>
															<th>Rate3</th>
															<th>Effected Date</th>
															<th>Valid Date</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody>
													</tbody>
												</table>
											</div>
										</div>
									</div> 
									<div class="col-md-12" style="text-align:center;vertical-align:center">
										<input type="submit" class="btn btn-shadow btn-success" name="submit" value="Submit">&nbsp;
										<a href="<?=ROOT.FINANCE_ROOT.'elcon_sales_card_list'?>" type="button" class="btn btn-danger">Cancel</a>
									</div>
								</div>
								<!--Vendor row end-->
								<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
								<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
								<input type='hidden' name='eid' id='eid' value='<?=$rel['elcon_sales_id']?>' />  
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
<script src="<?=ROOT.FINANCE_ROOT?>js/app/elcon_sales_card.js?<?=time()?>"></script>
<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/common_form_finance.js?<?=time()?>"></script>

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
	echo "<script>get_series_no(".SALES_CARD.");</script>";
}
echo "<script>item_detail_data();</script>";
?>
</body>
</html>