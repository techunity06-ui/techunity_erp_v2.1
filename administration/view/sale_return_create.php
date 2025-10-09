<?php 
	
	session_start();
	include('../include/urlfile.php');
	
	
	$form="Invoice";
        $branch_id = $_SESSION['branch_id'];
	$countryid='101';$stateid='1';$cityid='1';
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		FINANCE_SALE_RETURN_CREATE,
		FINANCE_SALE_RETURN_UPDATE,
		FINANCE_SALE_RETURN
	]);

	if(!in_array(FINANCE_SALE_RETURN_CREATE,$bulkAccessArray)){
     	header("Location: ".DOMAIN."permission_access");
    }

	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	
	$load_inv_type='52';
	
	if(strpos($_SERVER['REQUEST_URI'], "salereturnedit")==true)
	{	
		$mode="Edit";
	}
	else
	{
		$mode="Add";
	}
	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once($include.'include_css_file.php');?>
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
									<?phpinclude_once($include1.'head_menu_sale_return.php') ?>
								</header>	
								<div class="">
								  <ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li><a href="<?=ROOT.'invoice_list'?>">Invoice List</a></li>
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
									<form class="form-horizontal" role="form" id="invoice_add" action="javascript:;" method="post" name="invoice_add">
										
										<div class="row">
											
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Choose Branch *</label>
													<div class="col-md-8 col-xs-11">
															<select class="select2" name="branch_id" id="branch_id" tabindex="1">
																<?=getBranchBox_new($dbcon,$rel['branch_id']);?>
															</select>	
													</div>
												</div>
											</div>
											
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Company *</label>
													<div class="col-md-8 col-xs-11">
														<select class="select2" name="cust_id" id="cust_id" tabindex="2" onchange="get_invoice_by_cust(this.value);get_statecode(this.value);" >
															<?=getcust($dbcon,$cust_id);?>	
														</select>
														<strong id="statecode" style="display:none;color:blue">GST StateCode : <span class="statecode"></span></strong>
														<input type="hidden" name="cust_stateid" id="cust_stateid">
													</div>
												</div>									
											</div>
											
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Voucher No *</label>
													<div class="col-md-8 col-xs-11">
														<input id="voucher_no" name="voucher_no" type="text" class="form-control" title="Enter Voucher No" value="<?=$invoice_no?>" placeholder="Voucher No" required>		
													</div>
												</div>
											</div>
											
										</div>
										
										<div class="row">
											
											
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Material Center *</label>
													<div class="col-md-8 col-xs-11">
														<select class="select2" id="mat_center" name="mat_center">
															<option value="">--select Material Center--</option>
															<?=get_all_godown($dbcon);?>
														</select>	
													</div>
												</div>
											</div>
										
											
                                        
										</div>
										
										
										<div class="row">
										
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-spacing:10px;" id="product_list" class="display table table-bordered table-striped">
														<tr id="field">
															<th  class="text-center">Invoice No</th>
															<th  class="text-center">Product Detail</th>
															<th  class="text-center">Quantity</th>
															<th  class="text-center">Rate</th>
															<th  class="text-center">Unit</th>
															<th  class="text-center">Amount</th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td style="vertical-align:top;" >
																
																<select class="select2" name="invoice_number" id="invoice_number" onchange="get_product_from_invoice(this.value)">
																	
																</select>
																
															</td>
															<td style="vertical-align:top;">
																<select class="select2" title="Select product" name="product_id" id="product_id" onChange="load_productdetail(this.value);">
																	
																</select>
																<strong id="hsn_code"></strong>
																<br/><br/>
																<textarea id="product_des" name="product_des" class="form-control" placeholder="Product Description"></textarea>
															</td>	
															<td style="vertical-align:top;">
																<input type="text" min="0" id="product_qty" name="product_qty"  class="form-control" onKeyUp="get_amount();"/><br/>
																<input type="hidden" min="0" id="product_qty_hid" name="product_qty_hid"  class="form-control" onKeyUp="get_amount();"/>
																<input type="hidden" min="0" id="product_tax" name="product_tax"  class="form-control" onKeyUp="get_amount();"/>
															</td>
															<td style="vertical-align:top;">
																<input type="text"  title="Enter Rate" min="0" id="product_rate" name="product_rate" onKeyUp="get_amount();" class="form-control"/><br/>
																
															</td>
															<td style="vertical-align:top;">
																<select class="select2"  title="Select Unit" name="unit_id" id="unit_id">
																	<?=getunit($dbcon,0);?>
																</select>
															</td>
															
															<td style="vertical-align:top;"> 
																<input type="text" min="0" id="product_amount" readonly="readonly" name="product_amount" class="form-control" onmouseover="this.title=this.value"/>
															</td>
															<td style="vertical-align:top;"> 
																<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>	
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>								
												</div>
											</div>
											
										</div>
										
										<div class="row">
											<div id="sale_productdata"></div>
										</div>
										
										<div class="row">
											
											<div class="col-md-6">
												
												<table class="table table-bordered">
													
													<tr>
														<th>#</th>
														<th  class="text-center">Tax Rate</th>
														<th  class="text-center">Taxable Amount</th>
														<th  class="text-center">CGST</th>
														<th  class="text-center">SGST</th>
														<th  class="text-center">IGST</th>
													</tr>
													
													<tr>
														<th>1</th>
														<th>10%</th>
														<th>1200</th>
														<th>5%</th>
														<th>5%</th>
														<th>10%</th>
													</tr>
													
													<tr>
														<th>2</th>
														<th>10%</th>
														<th>1200</th>
														<th>5%</th>
														<th>5%</th>
														<th>10%</th>
													</tr>
													
													<tr>
														<th>3</th>
														<th>10%</th>
														<th>1200</th>
														<th>5%</th>
														<th>5%</th>
														<th>10%</th>
													</tr>
													
												</table>
												
											</div>
											
											 <div class="col-md-6">
												
												<div class="form-group">
													<label class="col-md-5 control-label">Total *</label>
													<div class="col-md-5 col-xs-11">
														<input id="total" name="total" type="text" readonly="readonly" class="form-control" title="Grand Total" max="0"  value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;}?>" placeholder="total">
													</div>
												</div>	
													
												<div class="form-group">
													<label class="col-md-5 control-label">CGST</label>
													<div class="col-md-5 col-xs-11">
														<input id="packing" name="packing" type="text" class="form-control" title="packing" min="0"  value="<?phpif($mode=='Edit'){ echo $rel['packing'];}?>" placeholder="Packing" onKeyUp="add_freight();" >
													</div>
												</div>
												
												<div class="form-group">
													<label class="col-md-5 control-label">SGST</label>
													<div class="col-md-5 col-xs-11">
														<input id="packing" name="packing" type="text" class="form-control" title="packing" min="0"  value="<?phpif($mode=='Edit'){ echo $rel['packing'];}?>" placeholder="Packing" onKeyUp="add_freight();" >
													</div>
												</div>
												
												<div class="form-group">
													<label class="col-md-5 control-label">TCS</label>
													<div class="col-md-5 col-xs-11">
														<input id="packing" name="packing" type="text" class="form-control" title="packing" min="0"  value="<?phpif($mode=='Edit'){ echo $rel['packing'];}?>" placeholder="Packing" onKeyUp="add_freight();" >
													</div>
												</div>
												                                                       
																									   
												<div class="form-group">
													<label class="col-md-5 control-label">Packing</label>
													<div class="col-md-5 col-xs-11">
														<input id="packing" name="packing" type="text" class="form-control" title="packing" min="0"  value="<?phpif($mode=='Edit'){ echo $rel['packing'];}?>" placeholder="Packing" onKeyUp="add_freight();" >
													</div>
												</div>
												<!-- Dimple Panchal : end -->
												<div class="form-group">
													<label class="col-md-5 control-label">Net Amount *</label>
													<div class="col-md-5 col-xs-11">
														<input id="g_total" name="g_total" type="text" class="form-control" title="Net Amount" value="<?=$rel['g_total']?>" placeholder="Grand Total" readonly="readonly">
													</div>
												</div>	
												<div class="form-group">
													<label class="col-md-5 control-label">Select Print</label>
													<div class="col-md-5 col-xs-11">
														<select class="form-control" name="print_status" id="print_status">
															<option value="1">ORIGINAL</option>
															<option value="2">DUPLICATE</option>
															<option value="3">TRIPLICATE</option>
															<option value="4">EXTRA</option>
														</select>
													</div>
												</div>
											</div>
										
										
										</div>
										
										<div class="row">
											
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Enable Cost Center *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="enable_cost_center" id="allocate_cost_center">
															<option value="no" selected>No</option>
															<option value="yes">Yes</option>
														</select>
													</div>
												</div>
											</div>
											
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Enable Salesman *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="enable_salesman" id="enable_salesman">
															<option value="no" selected>No</option>
															<option value="yes">Yes</option>
														</select>
													</div>
												</div>
											</div>
											
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Enable TCS Detail *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="enable_tcs_details" id="enable_tcs_details">
															<option value="no" selected>No</option>
															<option value="yes">Yes</option>
														</select>
													</div>
												</div>
											</div>
										
											
										</div>
										<div class="clearfix"></div>
										<div class="row">
											
											<div class="col-md-12">
                                                                        
												 <div class="form-group">
														<label class="col-md-1 control-label">Remarks </label>
														<div class="col-md-11 col-xs-11">
																<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
														</div>
												</div>
											</div>	
											
										</div>
											
										<div class="row">
												<div class="col-md-12">
													<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
													<button type="button" onClick="invoice_submit();" class="btn btn-success" id="saveprint" name="saveprint">Save and Print</button> &nbsp;
													<a href="<?=ROOT.'invoice_list'?>" type="button" class="btn btn-danger">Cancel</a>
													<div class="col-md-3"></div>			
												</div>		
											
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type='hidden' name='o_total' id='o_total' value='<?=$rel['g_total']?>' />
											<input type='hidden' name='save_print' id='save_print' value='' />
											<input type='hidden' name='eid' id='eid' value='<?=$rel['invoice_id']?>' />
											<input type='hidden' name='so_trn_id' id='so_trn_id' value='<?=$so_trn_id?>' />
											<input type='hidden' name='sales_order_id' id='sales_order_id' value='<?=$sales_order_id?>' />
											<input type='hidden' name='quotation_id' id='quotation_id' value='<?=$quotation_id?>' />
											<input type='hidden' name='complaint_id' id='complaint_id' value='<?=$complaint_id?>' />
										</div>
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
			<?php include_once('../include/add_payterms.php');?>
			<?php include_once('../include/footer.php');?>
			<?php include_once('../include/add_placesupally.php');?>
			<?php include_once('../include/add_modedispatch.php');?>
			<?php include_once('../include/add_worktype.php');?>
			<?php include_once('../include/add_invdescription.php');?>
			<div class="modal colored-header info" id="inv_srl_modal" role="dialog" data-keyboard="false" data-backdrop="static">
				<div class="modal-dialog modal-md">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
							<h3> <strong id="head_inv_srl_modal_pro_name"></strong></h3>
						</div>
						<div class="modal-body form">
							<div class="row">
								<div class="col-md-12">
									<div class="form-group" id="add_pro_srl_div">
										<table class="display table table-bordered table-striped">
											<tr>
												<th>Serial No.</th>
												<th>Action</th>
											</tr>
											<tr>
												<td>
													<input type="text" class="form-control" name="pro_srl_no" id="pro_srl_no" placeholder="Serial No." value="" autocomplete="off" />
												</td>
												<td>
													<button type="button" id="add_pro_srl_no_btn" onclick="add_pro_srl_no();" class="btn btn-success">Add</button>
												</td>
											</tr>
										</table>
									</div>
									<div class="form-group">
										<div class="adv-table dt-resp">
											<table class="display table table-bordered table-striped">
												<thead>
													<tr>
														<th>Sr.</th>
														<th>Serail No.</th>
														<th class="">Action</th>	
													</tr>
												</thead>
												<tbody id="inv-srlno-modal-datatable">
												</tbody>				 
											</table>
										</div>
									</div>
									
									<div class="clearfix"></div>
									<div class="col-md-12 text-center" style="margin-top:10px;">
										<input type='hidden' name='ref_trancation_id' id='ref_trancation_id' value='' />	
								
										<button type="button" class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
									</div>
								</div>
							</div>
						</div>	
					</div>
				</div>
			</div>
		</section>
		<?php
			include_once($include.'include_js_file.php');
		?>   
		<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/sale_return.js?<?php echo time(); ?>"></script>
		<script src="<?=ROOT?>js/app/customer.js"></script>
        <script src="<?=ROOT?>js/app/payment_new.js"></script>
		<script src="<?=ROOT?>js/app/product_mst.js"></script>
		<script src="<?=ROOT?>js/app/city_mst.js"></script>
		<script src="<?=ROOT?>js/app/payment_terms.js"></script>
		<script src="<?=ROOT?>js/app/invoice_consignee.js"></script>
		<script src="<?=ROOT?>js/app/state_mst.js"></script>
		<script src="<?=ROOT?>js/app/place_supply.js"></script>
		<script src="<?=ROOT?>js/app/mode_disptch.js"></script>
		<script src="<?=ROOT?>js/app/work_type.js"></script>
		<script src="<?=ROOT?>js/app/description_mst.js"></script>
		<script>
			$(".select2").select2({
				width: '100%',
				//minimumInputLength: 3
			});
			$("#cust_id").select2({
				width: '100%',
				minimumInputLength: 3
			});

			$('.default-date-picker').datepicker({
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
		<?
			echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
			echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";

			echo "<script>load_state(".$countryid.",'con_stateid',".$stateid.")</script>";
			echo "<script>load_city(".$stateid.",'con_cityid',".$cityid.")</script>";

			if($mode=="Add"){
				echo "<script>load_invoiceno(".$load_inv_type.");</script>";
			}
			if($quotation_id){
				echo "<script>copy_quot_trn_data(".$quotation_id.");</script>";
			}
			if($complaint_id){
				echo "<script>copy_comp_spare_trn_data(".$complaint_id.");</script>";
			}
			if(!empty($so_trn_id)){
				echo "<script>load_consignee(".$cust_id.",'1');</script>";
				echo "<script>check_due_payment(".$cust_id.");</script>";
				echo "<script>load_sales_order(".$cust_id.",".$sales_order_id.");</script>";
				echo "<script>load_sales_order_data(".$sales_order_id.");</script>";
				
			}
		?>
	</body>
</html>