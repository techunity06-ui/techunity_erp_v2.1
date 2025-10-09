<?php 

session_start();
include_once("../config/config.php");
include_once("../config/session.php");

include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");

$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$form="Sales Order";
$countryid='101';
$stateid='1';
$cityid='1';
	// if(strpos($_SERVER[REQUEST_URI], "salesorderedit")==false)
	// {
	// 	$mode="Add";
	// 	$date=date('d-m-Y');
	// 	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	// 	$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	// }
	// else
	// {
$mode="stage";
$sales_order_id=$dbcon->real_escape_string($_REQUEST['id']);
$query="select * from tbl_sales_order where sales_order_id=$sales_order_id";
$rel=mysqli_fetch_assoc($dbcon->query($query));
$sales_order_no=$rel['sales_order_no'];
$date=date('d-m-Y',strtotime($rel['sales_order_date']));
if($rel['po_date']!="1970-01-01" && $rel['po_date']!="0000-00-00" && $rel['po_date']!=""){
	$po_date=date('d-m-Y',strtotime($rel['po_date']));
}
if($rel['delivery_date']!="1970-01-01" && $rel['delivery_date']!="0000-00-00" && $rel['delivery_date']!=""){
	$delivery_date=date('d-m-Y',strtotime($rel['delivery_date']));
}
$query="select group_concat(product_id) as prdctsids from tbl_sales_ordertrn  where sales_order_id=$sales_order_id";
$rel_trn=mysqli_fetch_assoc($dbcon->query($query));
		//print_r($rel_trn);
	//}
?>

<!DOCTYPE html>
<html lang="en">
<head>
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
							<header class="panel-heading">
								<h3> <?=$mode .' '.$form?></h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li ><a href="<?=ROOT.'sales_order_list'?>"><?=$form?> List</a></li>
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
								<form class="form-horizontal" role="form" id="sales_order_stage" action="javascript:;" method="post" name="sales_order_stage">
									<div class="">
										<div class="col-md-12">
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Sales Order No *</label>
													<div class="col-md-8 col-xs-12">
														<input id="sales_order_no" name="sales_order_no" type="text" class="form-control" title="Enter Sales Order No" placeholder="Enter Sales Order No" value="<?=$sales_order_no?>" placeholder="Sales Order No" required>
													</div>
												</div>
											</div>
											
											
										</div>
										<input type="hidden" name="sales_order_id" id="sales_order_id" value="<?php echo $sales_order_id; ?>">
										<div class="col-md-12">
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label" > Product *</label>
													<div class="col-md-8 col-xs-12">
														<select class="select2" name="product_id" id="product_id" onChange="show_stage_data(this.value)" >
															<?=getproductbysalesorder($dbcon,$rel_trn['prdctsids'])?>
														</select>
													</div>
												</div>
											</div>
										</div>

										<div id="sale_productdata"></div>
										<div class="col-md-12">
											<center>
												<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
												
												<a href="<?=ROOT.'sales_order_list'?>" type="button" class="btn btn-danger">Cancel</a>
											</center>
										</div>		
									</div>
									<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
									<input type='hidden' name='eid' id='eid' value='<?=$rel['sales_order_id']?>' />
									<input type='hidden' name='invoicetype_id' id='invoicetype_id' value='<?if($mode != "Add"){ echo $rel['sales_order_id']; }?>' />
									<input type='hidden' name='save_print' id='save_print' value='' />
									<input type='hidden' name='receipt_no' id='receipt_no' value='<?=$receiptno?>' />
									<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />
								</form>
							</div>	
						</section>
					</div>
				</div>
			</section>
		</section>
		<?php include_once('../include/add_cust.php');?>
		<?php include_once('../include/add_product.php');?>
		<?php include_once('../include/add_city.php');?>
		<?php include_once('../include/add_state.php');?>
		
		<?php include_once('../include/footer.php');?>
	</section>
	<?php include_once('../include/include_js_file.php');?>   
	<script src="<?=ROOT?>js/app/sales_order.js"></script>
	<script>
			//CKEDITOR.replace('quotation_condition');
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
