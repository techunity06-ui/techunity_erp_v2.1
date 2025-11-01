<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	include_once("../../include/hrms_common_functions.php");

	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Hrms Leave Block";
	$companyID = $_SESSION['company_id'];
	$usertype=$_SESSION['user_type'];
	if(strpos($_SERVER['REQUEST_URI'], "salesorderedit")==false)
	{
		$mode="Add";
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	}
	else
	{
		$mode="Edit";
		$block_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from hrms_leave_block_list as blocklist 
				left join tbl_company as comp on comp.company_id = blocklist.company_id
				left join hrms_leave_block_day as blockday on blockday.leave_block_id = blocklist.id
				left join hrms_leave_block_allow_users as blockallowusers on blockallowusers.leave_block_id = blocklist.id
		 		where `id` = $block_id and `company_id` = $companyID";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../../include/include_css_file.php');?>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once('../../include/include_top_menu.php');?>
			<?php include_once('../../include/left_menu.php');?>
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
										<li><a href="<?= ROOT . HRMS_ROOT . 'hr_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li ><a href="<?= ROOT . HRMS_ROOT . 'hrms_leave_block_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="sales_order_add" action="javascript:;" method="post" name="sales_order_add">
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
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4  control-label">Sales Order Date*</label>
														<div class="col-md-8 col-xs-12">
															<input id="sales_order_date" name="sales_order_date" type="text" class="form-control default-date-picker required valid" title="Sales Order Date" placeholder="Sales Order Date" value="<?=$date?>" placeholder="Sales Order Date">
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4  control-label">Delivery Date</label>
														<div class="col-md-8 col-xs-12">
															<input id="delivery_date" name="delivery_date" type="text" class="form-control default-date-picker required valid" title="Delivery Date" placeholder="Delivery Date" value="<?=$delivery_date?>" placeholder="Delivery Date">
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">PO No </label>
														<div class="col-md-8 col-xs-12">
															<input id="po_no" name="po_no" type="text" class="form-control" title="Enter Purchase Order No" placeholder="Enter Purchase Order No" value="<?=$rel['po_no']?>" placeholder="Purchase Order No" >
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4  control-label">PO Date</label>
														<div class="col-md-8 col-xs-12">
															<input id="po_date" name="po_date" type="text" class="form-control default-date-picker valid" title="Purchase Order Date" placeholder="Purchase Order Date" value="<?=$po_date?>" placeholder="Purchase Order Date">
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label" > Company *</label>
														<div class="col-md-8 col-xs-12">
															<select class="select2" name="cust_id" id="cust_id" onChange="" >
																<?=getcust($dbcon,$rel['cust_id']);?>
															</select>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="product_list" class="display table table12 table-striped table-bordered">
														<tr id="field">
															<th width="20%" class="text-center">Product Detail</th>
															<th width="8%" class="text-center">HSN Code</th>
															<th width="6%" class="text-center">Quantity</th>
															<th width="7%" class="text-center">Rate</th>
															<th width="7%" class="text-center">Per</th>
															<th width="6%">Discount</th>
															<th width="10%">Taxable Value</th>
															<th width="13%">Tax</th>
															<th width="10%" class="text-center">Amount</th>
															<th width="5%" class="text-center"></th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td data-label="PRODUCT DETAIL" style="vertical-align:top;">
																<select class="select2"  title="Select product" name="product_id" id="product_id" onChange="load_productdetail(this.value)">
																	<?=getproduct($dbcon,0,'0,1,2,3,4,5')?>
																</select>
															<br><br>
																<textarea id="product_des" name="product_des" placeholder="Product Description" title="Enter Product Description" class="form-control" ></textarea>
															</td>	
															<td data-label="HSN CODE" style="vertical-align:top;">
																<input type="text"  title="Enter HSN Code" placeholder="HSN Code" id="product_hsn_code" name="product_hsn_code" class="form-control"/>
															</td>
															<td data-label="QUANTITY" style="vertical-align:top;">
																<input type="number"  title="Enter Qty"  min="0" id="product_qty" name="product_qty"  class="form-control" onkeyup="get_amount();get_discount('per');"/>
															</td>
															<td data-label="RATE" style="vertical-align:top;">
																<input type="number"  title="Enter Rate" placeholder="Rate" min="0" id="product_rate" name="product_rate" onkeyup="get_amount();get_discount('per');" class="form-control"/>
															</td>
															<td data-label="PER" style="vertical-align:top;">
																<select class="select2"  title="Select Unit" placeholder="Unit" name="unit_id" id="unit_id">
																	<?=getunit($dbcon,0);?>
																</select>
															</td>
															<td data-label="DISCOUNT" style="vertical-align:top;">
																<input type="number" title="Enter Discount" placeholder="Discount" min="0" id="product_discount" name="product_discount" onkeyup="get_discount('amt');" class="form-control" placeholder="in Rs."/><br/>
																<input type="number"  title="Enter Discount Percentage" min="0" id="discount_per" name="discount_per" onkeyup="get_discount('per');" class="form-control" placeholder="in %" max="100"/>
															</td>
															<td data-label="TAXABLE VALUE" style="vertical-align:top;">
																<input type="number" title="Taxable Value" placeholder="Taxable Value" min="0" id="taxable_value" name="taxable_value" class="form-control" readonly/>
															</td>
															<td data-label="TAX" style="vertical-align:top;">
																<select class="form-control" name="formulaid" id="formulaid" onChange="get_amount();">
																		<?php 
																			echo getformula($dbcon,$rel['formulaid']);
																		?>
																</select>
															</td>
															<td  data-label="AMOUNT"  style="vertical-align:top;"> 
																<input type="number" min="0" id="product_amount" readonly="readonly" name="product_amount" placeholder="AMOUNT" class="form-control" onmouseover="this.title=this.value" />
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
											<div class="col-md-7">
												<div class="form-group">
													<label class="col-md-2 control-label">Remarks </label>
													<div class="col-md-6 col-xs-12">
														<textarea id="remark" name="remark" placeholder="Remarks" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
													</div>
												</div>
												<div class="form-group">
													<label class="col-md-2 control-label" style="white-space:nowrap;">Terms & condition</label>
													<div class="col-md-8 col-xs-12">
														<textarea class="form-control" placeholder="Dispatch Footer Content" name="so_terms_and_condition" id="so_terms_and_condition" ><?=$rel['so_terms_and_condition']?></textarea>
													</div>
												</div>
											</div>	
											<div class="col-md-5">
												<div class="form-group">
													<label class="col-md-3 control-label">Grand Total *</label>
													<div class="col-md-6 col-xs-12">
														<input id="g_total" name="g_total" type="text" class="form-control" title="dispatch_no" value="<?=$rel['g_total']?>" placeholder="Grand Total" readonly="readonly">
													</div>
												</div>
												<div class="form-group">
													<label class="col-md-3 control-label">PO Document </label>
													<div class="col-md-6 col-xs-12">
														<input type="file" class="form-control" id="po_document" name="po_document[]" multiple="multiple" <?=$ttrt?> /> 
													</div>
												</div>
												<?php $get_attch_qry="select * from tbl_so_attch where status=0 and so_id=".$rel['sales_order_id'];
												$attch_rs=$dbcon->query($get_attch_qry);
												while($attch_rel=mysqli_fetch_assoc($attch_rs)){ ?>
												<div class="col-md-6">
												<div class="col-md-12">
													<center>
													<img style="width:110px;height:110px;" src="<?=ROOT.so_VWING.$attch_rel['so_file']?>" >
													</center>
												</div>
												<div class="col-md-12">
												<center>
												<a href="<?=ROOT.so_VWING.$attch_rel['so_file']?>" class="btn btn-xs btn-primary" target="_blank" style="margin-bottom: 2px;"><i class="fa fa-eye"></i>  </a> 
												<button type="button" onClick="delete_attch(<?=$attch_rel['so_attch_id']?>)" class="btn btn-xs btn-danger" target="_blank" style="margin-bottom: 2px;"><i class="fa fa-trash-o"></i></button>
												</center>
												</div>
												
												</div>
												<?php } ?>
											</div>
											<div class="col-md-12">
												<center>
													<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
													<button type="button" onClick="invoice_submit();" class="btn btn-success" id="saveprint" name="saveprint">Save and Print</button> &nbsp;
													<a href="<?=ROOT.'sales_order_list'?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>		
										</div>
										<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
										<input type='hidden' name='eid' id='eid' value='<?=$rel['sales_order_id']?>' />
										<input type='hidden' name='invoicetype_id' id='invoicetype_id' value='<?php if($mode != "Add"){ echo $rel['sales_order_id']; }?>' />
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
			<?php include_once('../../include/add_cust.php');?>
			<?php include_once('../../include/add_product.php');?>
			<?php include_once('../../include/add_city.php');?>
			<?php include_once('../../include/add_state.php');?>
			
			<?php include_once('../../include/footer.php');?>
		</section>
		<?php include_once('../../include/include_js_file.php');?>   
			<script src="<?= ROOT . HRMS_ROOT ?>js/app/sales_order.js"></script>
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
		<?php 
			if($mode=="Add")
			{
				
				echo "<script>get_series_no() </script>";
			}
			echo "<script>show_data() </script>";
		?>
		<script>
			CKEDITOR.replace( 'so_terms_and_condition', {
				enterMode: CKEDITOR.ENTER_BR
			});
		</script>
	</body>
</html>
