<?php 
	
	session_start();
	set_time_limit(0);
	$path = '../../';
	$include = '../../include/';
	$include1 = '../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
	$form="Price List";
        $branch_id = $_SESSION['branch_id'];
	$countryid='101';$stateid='1';$cityid='1';
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		PRICE_LIST_CREATE,
		PRICE_LIST_EDIT,
		PRICE_LIST_DELETE,
		PRICE_LIST_VIEW,
	]);

	$company_config = getCompanyConfiguration($dbcon);
	
	if(strpos($_SERVER['REQUEST_URI'], "price_list_edit")==true){
		if(!in_array(PRICE_LIST_EDIT,$bulkAccessArray)){
                        header("Location: ".DOMAIN."permission_access");
                }
		$mode="Edit";
		$price_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_price_list where price_list_id=$price_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
        if(!$rel){
            header("Location: ".ROOT."price_list");
        }
		$eid = $price_id;
		
	}
	else if(strpos($_SERVER['REQUEST_URI'], "price_list_product")==true){
		$product_id=$dbcon->real_escape_string($_REQUEST['id']);
		$eid = $dbcon->real_escape_string($_REQUEST['id2']);
	}
	else{
		if(!in_array(PRICE_LIST_CREATE,$bulkAccessArray)){
       		header("Location: ".DOMAIN."permission_access");
    	}
		$mode="Add";
		$date=date('d-m-Y');
		$order_date=date('d-m-Y');
		$load_inv_type='8';
		$lr_date=date('d-m-Y');
		$sales_ledger = $dbcon->query("SELECT l_id FROM `tbl_ledger` WHERE `l_group` = ".SALES_ACCOUNTS)
                                ->fetch_object()->l_id;
								
		$eid=0;
	}
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	
	$financial_year=get_financial_year_new($dbcon);
	
	//print_r($financial_year);

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>PRICE LIST CREATE</title>
		<?php include_once($include.'include_css_file.php');?>
		<style type="text/css">
			.currency_icon{
				color: green;
				font-size: 12px;
			}
			.row_margin
			{
				margin-top:10px;
			}
			.text_center
			{
				text-align:center !important;
			}
			.table_th
			{
				background-color:#6883A3 !important;
				color:#FFFFFF !important;
				text-align:center !important;
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
									<h3 style="float:left;"> <?=$mode .' '.$form?></h3>
								</header>	
								<div class="">
								  <ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li><a href="<?=ROOT.FINANCE_ROOT.'price_list'?>">Price List</a></li>
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
									<form class="form-horizontal" role="form" id="price_list_add" action="javascript:;" method="post" name="invoice_add">
										<input type="hidden" name="cust_stateid" id="cust_stateid">
											<div class="row">
												
												<div class="col-md-4">
													<label class="col-md-4 control-label">Select Branch *</label>
													<div class="col-md-8 col-xs-10 resclear" >
														<select class="select2" name="branch_id" id="branch_id" tabindex="2">
															<option value="">--Please Select Branch--</option>
															<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
															<?=getBranchBox_new($dbcon, $branch);?>
														</select>
													</div>
												</div>
												
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Effective Date*</label>
														<div class="col-md-8 col-xs-11">
															<input id="effective_date" name="effective_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?phpif($mode=='Add'){echo $date;}else if($mode=='Edit'){echo date('d-m-Y',strtotime($rel['price_list_effective_date']));}?>" placeholder="Effective Date" tabindex="4">
														</div>
													</div>	
												</div>
												
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Expiry Date*</label>
														<div class="col-md-8 col-xs-11">
															<input id="expiry_date" name="expiry_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?phpif($mode=='Add'){echo $date;}else if($mode=='Edit'){echo date('d-m-Y',strtotime($rel['price_list_expire_date']));}?>" placeholder="Expiry Date" tabindex="4">
														</div>
													</div>	
												</div>
												
											</div>
											
											<div class="row">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Version*</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" class="form-control" name="price_version" id="price_version" value="<?=$mode=='Edit'?$rel['price_list_version']:''?>" />
														</div>
													</div>	
												</div>
												
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Relase Version To*</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2"  name="relase_version_to" id="relase_version_to" onchange="get_group_customer(this.value)">
																<option value="">--Select Relase Version To--</option>
																<option value="0" <?=$mode=='Edit' && $rel['price_list_allocate_type']=='0'?'selected':''?>>To Group</option>
																<option value="1" <?=$mode=='Edit' && $rel['price_list_allocate_type']=='1'?'selected':''?>>To Customer</option>
															</select>
														</div>
													</div>	
												</div>
												
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Relase Version*</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="relase_version[]" id="relase_version" multiple="multiple">
																
															</select>
														</div>
													</div>	
												</div>
											</div>
											
											<div class="row" style="border:solid #F1F2F7 3px;padding:10px;">
												
												<div class="col-md-6"  style="text-align:right">
													
													<div class="col-md-12">
														<div class="form-group">
															<div class="col-md-4"><label>Last Purchase Price  &nbsp&nbsp</label></div>
															<div class="col-md-4" ><input type="checkbox" class="" id="check_last_purchase_price" name="check_price" value="last_purchase" checked ></div>
														</div>	
													</div>
												
													<div class="col-md-12">
														<div class="form-group">
															<div class="col-md-4 col-xs-11"><label>Current Stock Price  &nbsp&nbsp</label></div>
															<div class="col-md-4" ><input type="checkbox" class="" name="check_price" id="check_current_stock_price"  value="current_stock" ></div>
														</div>	
													</div>
													
													<div class="col-md-12">
														<div class="form-group">
															<div class="col-md-4  col-xs-11"><label>Last Workorder Price  &nbsp&nbsp</label></div>
															<div class="col-md-4" ><input type="checkbox" class="" name="check_price" id="check_last_wo_price"  value="last_wo_price" ></div>
														</div>	
													</div>
												
													
												</div>
												
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Product Type*</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="product_type_sel" id="product_type_sel" onChange="load_product_typeiwse(this.value);" title="Select Product Type">
																<?=getproducttype($dbcon,'0');?>
															</select>
														</div>
													</div>	
													
													<div class="form-group">
														<label class="col-md-4 control-label">Product Name*</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2_product" title="Select product" name="product_id" id="product_id"  style="width:100% !important"><!--load_qty()-->
																<?=getproduct($dbcon,$product_id,'0,1,2,3,4,5')?>
															</select>
															<strong style="display:none;color:red" class="error_1">Select Product To Continue..</strong>
														</div>
													</div>	
													
													<div class="form-group">
														<div class="col-md-12 col-md-offset-6">
															<button type="submit" class="btn btn-info" id="save" name="save" onClick="load_bom_product_detail();">VIEW</button>
														</div>
													</div>
													
												</div>
												
											</div>
											
											<div class="row row_margin">
												
												<div class="col-md-4 product_details_show">
													
													
												</div>
												
												<div class="col-md-8 price_list_details_show">
													
													
													
												</div>
												
											</div>
											
											<input type="hidden" value="<?=$eid ?>" id="eid" name="eid" />
											<input type="hidden" value="<?=$mode=='Edit'?'Edit':'add'?>" id="mode" name="mode" />
											
										</div>
									
									
										<div class="row">
											<div class="col-md-12">
												<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
												<a href="<?=ROOT.FINANCE_ROOT.'price_list'?>" type="button" class="btn btn-danger">Cancel</a>
												<div class="col-md-3"></div>			
											</div>	
										</div>
										
									</form>
								</div>	
							</section>
						</div>
					</div>
				</section>
			</section>
			
			<?php include_once($include.'footer.php');?>
		
			
		</section>
		<?php
			include_once($include.'include_js_file.php');
		?>   
		<script src="<?=ROOT.FINANCE_ROOT?>js/app/price_list.js?<?php echo time(); ?>"></script>
		<script>
			$(document).ready(function() {
	
				var mode = $('#mode').val();
				
				<?php if(strpos($_SERVER['REQUEST_URI'], "price_list_product")==true){ ?>
					load_bom_product_detail(<?=$product_id?>);
				<?php } ?>
				
				$(".price_list_details_show").removeClass('col-md-8');
				$(".price_list_details_show").addClass('col-md-12');
				
				list_price_list_products($('#eid').val());

				if(mode=='Edit')
				{
					get_group_customer(<?=$rel['price_list_allocate_type'];?>,<?=$eid;?>);	
				}
				
			});
			
			
			
			$(".select2").select2({
				width: '100%',
				//minimumInputLength: 3
			});

			$('.select2_product').select2({
				width: '100%',
				minimumInputLength: 3
			});

			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			$('.default-eway-date').datepicker({
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
	
	</body>
</html>