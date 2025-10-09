<?php 
	
	session_start();
	$path = '../../';
	$include1 = '../include/';
	$include = '../../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
	
	
	$form="CREDIT NOTE";
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
	
	$load_inv_type='68';
	
	if(strpos($_SERVER['REQUEST_URI'], "salereturnedit")==true)
	{	
		if(!in_array(FINANCE_SALE_RETURN_UPDATE,$bulkAccessArray)){
				header("Location: ".DOMAIN."permission_access");
		}
		
		$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_sale_return where sale_return_id='$invoiceid'";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
                
		if(!$rel){
			header("Location: ".ROOT."invoice_list");
		}
		
		$mode="Edit";
		$disable = 'disabled';
	}
	else
	{
		$mode="Add";
	}
	
	$getCompanyConfig = getCompanyConfiguration($dbcon);
	
	$financial_year=get_financial_year_new($dbcon);
	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>CREDIT NOTE</title>
		<?php include_once($include.'include_css_file.php');?>
		<style type="text/css">
			.currency_icon{
				color: green;
				font-size: 12px;
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
									<?phpinclude_once($include1.'head_menu_sale_return.php') ?>
								</header>	
								<div class="">
								  <ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li><a href="<?=ROOT.FINANCE_ROOT.'sale_return'?>">Sale Return List</a></li>
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
											
											<div class="col-md-4" style="margin-bottom: 15px;">
												
												<label class="col-md-4 control-label">Sales Ledger*</label>
												<div class="col-md-8 col-xs-10 resclear" >
													<?php $sales_grp_array=implode(",",array(SALES_ACCOUNTS));
														$sales_account = isset($rel['sales_ledger_id']) ? $rel['sales_ledger_id'] : SALES_ACCOUNT ;
													 ?>
													<select <?= $disable ?> class="select2" name="sales_ledger_id" id="sales_ledger_id" title="Select Sales Ledger" tabindex="1">
														<?= f_get_group_ledger($dbcon,$sales_grp_array,$sales_account);?>
													</select>
													<?php
								                    if($disable){
								                        echo '<input type="hidden" name="sales_ledger_id" id="sales_ledger_id" value="'.$sales_account.'">';
								                    	}
								                    ?>
												</div>
												
											</div>
											
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Choose Branch *</label>
													<div class="col-md-8 col-xs-11">
															<select class="select2" name="branch_id" id="branch_id" tabindex="2">
																<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
																<?=getBranchBox_new($dbcon,$branch);?>
															</select>	
													</div>
												</div>
											</div>
											
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Company *</label>
													<div class="col-md-8 col-xs-11">
														<select class="select2" <?= $disable ?> name="cust_id" id="cust_id" tabindex="3" onchange="get_invoice_by_cust(this.value,'invoice_number');get_statecode(this.value);get_ledger_details(this.value);get_gtotal();" >
															<?=getcust($dbcon,$rel['sale_return_customer']);?>	
														</select>
														<?php
									                    	if($disable){
									                        	echo '<input type="hidden" name="cust_id" id="cust_id" value="'.$rel['sale_return_customer'].'">';
									                    	}
									                    ?>
														<strong id="statecode" style="display:none;color:blue">GST StateCode : <span class="statecode"></span></strong>
														<input type="hidden" name="cust_stateid" id="cust_stateid">
													</div>
												</div>									
											</div>
											
										</div>
										
										<div class="row">
											
											
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Voucher No *</label>
													<div class="col-md-8 col-xs-11">
														<input id="voucher_no" name="voucher_no" type="text" class="form-control" title="Enter Voucher No" value="<?=$mode=='Edit'?$rel['sal_return_voucher_no']:''?>" placeholder="Voucher No" required readonly tabindex="4">		
													</div>
												</div>
											</div>
											
											
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Material Center</label>
													<div class="col-md-8 col-xs-11">
														<select class="select2" id="mat_center" name="mat_center" tabindex="5">
															<option value="">--Select Material Center--</option>
															<?=get_all_godown($dbcon,$rel['sale_return_material_center']);?>
														</select>	
													</div>
												</div>
											</div>
										
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Date *</label>
													<div class="col-md-8 col-xs-11">
														<input type="text" class="form-control default_date" name="sale_return_date" id="sale_return_date" value="<?phpif($mode=='Add'){ echo date("d-m-Y");}else if($mode=='Edit'){ if($rel['sale_return_date']=='0000-00-00'){ echo ""; } else { echo date('d-m-Y',strtotime($rel['sale_return_date']));} } ?>" tabindex="6" />
													</div>
												</div>
											</div>
											
											
										</div>
										
										<div class="row">
											
											<div class="col-md-4" id="currency_enable_div" style="display:none">
												<div class="form-group">
													<label class="col-md-4 control-label">Enable Multi Currency *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="sale_enable_multi_currency" id="sale_enable_multi_currency" onChange="get_conv_div(this.value)" tabindex="7">
															<option value="0">--Enable Multi Currency--</option>
															<option value="1" <?=$mode=='Edit' && $rel['sale_enable_multi_currency']==1?'selected':''?>>Yes</option>
															<option value="0">No</option>
														</select>
													</div>
												</div>
											</div>
										
											<div class="col-md-4" id="currency_conv_div" style="display:none">
												<div class="form-group">
													<label class="col-md-4 control-label">Currency Conversion *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="currency_conv" id="currency_conv" onchange="get_conv_rate(this.value);get_symbol();" >
															<?=getcurrency($dbcon,$rel['currency_id']);?>
														</select>
													</div>
												</div>
											</div>
											
											<div class="col-md-4" id="currency_conv_rate_div" style="display:none">
												<div class="form-group">
													<label class="col-md-4 control-label">Conversion Rate INR *</label>
													<div class="col-md-8 col-xs-11">
														<input type="text" class="form-control" name="currency_conv_rate" id="currency_conv_rate" value="<?=$mode=='Edit'?$rel['currency_rate']:''?>" />
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
															<th  class="text-center">Rate  <span class="currency_icon"></span></th>
															<th  class="text-center">Unit</th>
															<th  class="text-center">Amount  <span class="currency_icon"></span></th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td style="vertical-align:top;" >
																
																<select class="select2" name="invoice_number" id="invoice_number" onchange="get_product_from_invoice(this.value)" tabindex="8">
																	
																</select>
																
																<strong class="error" id="inv_number_err" style="color:red"></strong> 
															</td>
															<td style="vertical-align:top;">
																<select class="select2" title="Select product" name="product_id" id="product_id" onChange="load_productdetail(this.value);"  tabindex="8">
																	
																</select>
																<strong class="hsncode" style="display:none;color:blue">HSN Code : <span id="hsncode"></span></strong>, 
																<strong class="sale_remained" style="display:none;color:green">Sale Remained Qty : <span id="sale_remained"></span></strong>
																<br/><br/>
																<textarea id="product_des" name="product_des" class="form-control" placeholder="Product Description"  tabindex="10"></textarea>
															</td>	
															<td style="vertical-align:top;">
															
																<input type="text" min="0" id="product_qty" name="product_qty"  class="form-control numbersOnly" onKeyUp="get_amount();"  tabindex="11" onkeypress="return isNumberKey(event)" /><br/>
																
																<input type="hidden" min="0" id="product_qty_hid" name="product_qty_hid"  class="form-control" onKeyUp="get_amount();"/>
																<input type="hidden" min="0" id="product_tax" name="product_tax"  class="form-control" onKeyUp="get_amount();"/>
															</td>
															<td style="vertical-align:top;">
																<input type="text"  title="Enter Rate" min="0" id="product_rate" name="product_rate" onKeyUp="get_amount();" class="form-control numbersOnly" onkeypress="return isNumberKey(event)"  tabindex="12" /><br/>
																
															</td>
															<td style="vertical-align:top;">
																<select class="select2"  title="Select Unit" name="unit_id" id="unit_id"  tabindex="13">
																	<?=getunit($dbcon,0);?>
																</select>
															</td>
															
															<td style="vertical-align:top;"> 
																<input type="text" min="0" id="product_amount" readonly="readonly" name="product_amount" class="form-control" onmouseover="this.title=this.value"  tabindex="14" />
															</td>
															<td style="vertical-align:top;"> 
																<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"  tabindex="15" />	
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
											
											<div class="col-md-6 tax_details">
												
												
												
											</div>
											
											
											 <div class="col-md-6">
												
												<div class="form-group">
													<label class="col-md-5 control-label">Total *</label>
													<div class="col-md-5 col-xs-11">
														<input id="total" name="total" type="text" readonly="readonly" class="form-control" title="Grand Total" max="0"  value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;}?>" placeholder="total">
													</div>
												</div>	
													
												<div class="invoiceTotalTax">
													
												</div>
												
												<div class="sundryadded">
													
												</div>
												
												<div class="form-group">
													<label class="col-md-5 control-label">Net Amount *</label>
													<div class="col-md-5 col-xs-11">
														<input id="g_total" name="g_total" type="text" class="form-control" title="Net Amount" value="<?=$rel['g_total']?>" placeholder="Grand Total" readonly="readonly">
													</div>
												</div>	
												
												<div>
													<div class="form-group">
														<label class="col-md-5 control-label">Select Bill Sundry</label>
														<div class="col-md-2">
															<?php $get_bill_sundry = get_bill_sundry_ledger($dbcon,0); ?>
															<select class="form-control" name="bill_sundry" id="bill_sundry" onchange="get_sundry_label(this.value)">
																<option value="0">Select</option>
																<?php foreach ($get_bill_sundry as $sundry) {
																	
																 ?>
																<option value="<?php echo $sundry['l_id'] ?>"><?php echo $sundry['l_name']; ?></option>
																
																<?php } ?>
															</select>
														</div>
														<div class="col-md-2">
															<input id="bill_sundry_amount" name="bill_sundry_amount" type="text" class="form-control digitOnly" placeholder="Amount" title="Amount" value="<?=$rel['amount']?>" placeholder="" >
														</div>
														<div class="col-md-2">
															<button style="margin-top: 5px;" class="btn btn-round btn-info btn-xs" type="button" value="R1" onclick="addBillSundry()"><i class="fa fa-plus"></i></button>
														</div>
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
											
											
											<div class="col-md-4"  style="display:none" id="div_cost_center">
												<div class="form-group">
													<label class="col-md-4 control-label">Cost Center *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="enable_cost_center" id="allocate_cost_center" onchange="get_cost_center(this.value)">
															<option value="no" selected>No</option>
															<option value="yes" <?php if($mode=='Edit' && $rel['sale_return_cost_center_enable']==1){ echo "selected"; } else{ echo ""; } ?>>Yes</option>
														</select>
														<?php if($mode=='Edit' && $rel['sale_return_cost_center_enable']==1){ $style=""; } else { $style='display:none'; } ?>
																<a style="<?=$style;?>" href="#" id="cost_center_link" onclick="get_cost_center('yes')">Show Cost Center Transaction</a>
													</div>
												</div>
											</div>
											
											<div class="col-md-4" style="display:none" id="tcs_div">
												<div class="form-group">
													<label class="col-md-4 control-label">TCS Reversal Detail *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="enable_tcs_details" id="enable_tcs_details" onchange="get_tcs_reversal(this.value)">
															<option value="no" selected>No</option>
															<option value="yes" <?php if($mode=='Edit' && $rel['sale_return_tcs_enable']==1){ echo "selected"; } else{ echo ""; } ?>>Yes</option>
														</select>
														
														<?php if($mode=='Edit' && $rel['sale_return_tcs_enable']==1){ $style=""; } else { $style='display:none'; } ?>
														
														<a style="<?=$style;?>" href="#" id="tcsr_link" onclick="get_tcs_reversal('yes')">Show TCS Return Details</a>
													</div>
												</div>
											</div>
											
											<div class="col-md-4" style="display:none" id="eway_div">
												<div class="form-group">
													<label class="col-md-4 control-label">Auto Eway Bill *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="enable_ewaybill" id="enable_ewaybill"  onchange="get_eway_bill(this.value)">
															<option value="no" selected>No</option>
															<option value="yes" <?php if($mode=='Edit' && $rel['sale_return_eway_enable']==1){ echo "selected"; } else{ echo ""; } ?>>Yes</option>
														</select>
														<?php if($mode=='Edit' && $rel['sale_return_eway_enable']==1){ $style=""; } else { $style='display:none'; } ?>
														
														<a style="<?=$style;?>" href="#" id="eway_bill_link" onclick="get_eway_bill('yes')">Show Eway Bill Details</a>
													</div>
												</div>
											</div>
											
											<?php /**<div class="col-md-4" style="display:none" id="salesman_div">
												<div class="form-group">
													<label class="col-md-4 control-label">Enable Salesman *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="enable_salesman" id="enable_salesman"  onchange="get_ledger_salesman(this.value,'total')">
															<option value="no" selected>No</option>
															<option value="yes" <?php if($mode=='Edit' && $rel['sale_return_salesman_enable']==1){ echo "selected"; } else{ echo ""; } ?>>Yes</option>
														</select>
														
														<?php if($mode=='Edit' && $rel['sale_return_salesman_enable']==1){ $style=""; } else { $style='display:none'; } ?>
														
														<a style="<?=$style;?>" href="#" id="salesman_link" onclick="get_ledger_salesman('yes','total')">Show Salesman Details</a>
													</div>
												</div>
											</div> **/ ?>
										
											
										</div>
										
										<div class="row">
											
											<div class="col-md-4">
                                                                        
												 <div class="form-group">
														<label class="col-md-4 control-label">EWay Bill No </label>
														<div class="col-md-8 col-xs-11">
																<input type="text" class="form-control" name="eway_bill_no" id="eway_bill_no" value="<?=$mode=='Edit'?$rel['sale_return_eway_bill_no']:''?>" />
														</div>
												</div>
											</div>

											<div class="col-md-4">
                                                                        
												 <div class="form-group">
														<label class="col-md-4 control-label">EWay Bill Date </label>
														<div class="col-md-8 col-xs-11">
																<input type="text" class="form-control" name="eway_bill_date" id="eway_bill_date" value="<?=$mode=='Edit' && $rel['sale_return_eway_bill_date']!='0000-00-00'?date("d/m/Y",strtotime($rel['sale_return_eway_bill_date'])):''?>" />
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
										<div class="clearfix"></div>
										<div class="row" style="margin-top:10px;">
												<div class="col-md-12">
													<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
													<button type="button" onClick="invoice_submit();" class="btn btn-success" id="saveprint" name="saveprint">Save and Print</button> &nbsp;
													<a href="<?=ROOT.FINANCE_ROOT.'sale_return'?>" type="button" class="btn btn-danger">Cancel</a>
													<div class="col-md-3"></div>			
												</div>		
											
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type="hidden" name="salereturntype_id" id="salereturntype_id" value="" />
											<input type='hidden' name='o_total' id='o_total' value='<?=$rel['g_total']?>' />
											<input type='hidden' name='save_print' id='save_print' value='' />
											<input type='hidden' name='eid' id='eid' value='<?=$rel['sale_return_id']?>' />
											<input type='hidden' name='so_trn_id' id='so_trn_id' value='<?=$so_trn_id?>' />
											<input type='hidden' name='sales_order_id' id='sales_order_id' value='<?=$sales_order_id?>' />
											<input type='hidden' name='quotation_id' id='quotation_id' value='<?=$quotation_id?>' />
											<input type='hidden' name='complaint_id' id='complaint_id' value='<?=$complaint_id?>' />
											
											<!-- Financial Year Setting start -->
											
											<input type='hidden' name='financial_year' id='financial_year' value='<?=$financial_year['financial_year_id'];?>' />
											<input type='hidden' name='financial_start_date' id='financial_start_date' value='<?=$financial_year['financial_start_date'];?>' />
											<input type='hidden' name='financial_end_date' id='financial_end_date' value='<?=$financial_year['financial_end_date'];?>' />
											
											<!-- Financial Year Setting end -->
											
											<!-- Company Settings -->
											
											<input type="hidden" name="company_cost_center" id="company_cost_center" value="<?=$getCompanyConfig['enable_cost_center']?>" />
											
											<input type="hidden" name="company_salesman" id="company_salesman" value="<?=$getCompanyConfig['enable_salesman']?>" />
											
											<input type="hidden" name="company_tcs" id="company_tcs" value="<?=$getCompanyConfig['enable_tcs_reporting']?>" />
											
											<input type="hidden" name="company_eway" id="company_eway" value="<?=$getCompanyConfig['enable_eway_bill']?>" />
											
											<input type="hidden" name="enable_multi_currency" id="enable_multi_currency" value="<?=$getCompanyConfig['enable_multi_currency']?>" />
											
											
											<!-- cost center popup --> 
											
											<input type="hidden" name="cost_center_voucher_type" id="cost_center_voucher_type" value="<?=SALES_RETURN_VOUCHER?>" />
											<input type="hidden" name="cost_center_ledger_id" id="cost_center_ledger_id" placeholder="Ledger Id" value="<?=$mode=='Edit'?$rel['sales_ledger_id']:'' ?>">
											<input type="hidden" name="cost_center_table" id="cost_center_table" value="tbl_sale_return" placeholder="table name of sale , purchase , payment..">
											<input type="hidden" name="cost_center_table_id" id="cost_center_table_id" value="<?=$mode=='Edit'?$rel['sale_return_id']:'0'?>" placeholder="primary key of that inserted table ">
											<input type="hidden" id="edit_id" value="" />
											
											<!-- Transport and Eway bill transaction popup -->
											<input type="hidden" name="transport_voucher" id="transport_voucher" value="<?=SALES_RETURN_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase">
											<input type="hidden" name="transport_transaction_table" id="transport_transaction_table" placeholder="table name of sale , purchase , payment.." value="tbl_sale_return">
											<input type="hidden" name="transport_transaction_table_id" id="transport_transaction_table_id" placeholder="primary key of that inserted table ">
											<input type="hidden" id="edit_id_transport" value="<?=$mode=='Edit'?$rel['sale_return_id']:'0'?>" />

											<!-- Transport and Eway bill transaction popup -->
											<input type="hidden" name="eway_bill_voucher_type" id="eway_bill_voucher_type" value="<?=SALES_RETURN_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase">
											<input type="hidden" name="eway_bill_voucher_table" id="eway_bill_voucher_table" placeholder="table name of sale , purchase , payment.." value="tbl_sale_return">
											<input type="hidden" name="eway_bill_voucher_id" id="eway_bill_voucher_id" placeholder="primary key of that inserted table ">
											<input type="hidden" id="edit_id_ewaybill" value="<?=$mode=='Edit'?$rel['invoice_id']:'0'?>" />
											
											<!-- Salesman transaction popup -->
											<input type="hidden" name="salesman_voucher_type" id="salesman_voucher_type" value="<?=SALES_RETURN_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase">
											<input type="hidden" name="salesman_voucher_table" id="salesman_voucher_table" placeholder="table name of sale , purchase , payment.." value="tbl_sale_return">
											
											<input type="hidden" id="edit_id_salesman" value="" />
											
										</div>
									</form>
								</div>
								</section>
								</div>	
						</div>
					</div>
				</section>
			</section>
			<?php include_once($include.'include/footer.php');?>
			
			
	</section>
		<?php
			include_once($include.'include_js_file.php');
			include_once($include1.'add_cost_center.php');
			include_once($include1.'add_eway_bill.php');
			include_once($include1.'add_tcs_reversal.php');
			include_once($include1.'add_salesman.php');
		?>   
		<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/sale_return.js?<?php echo time(); ?>"></script>
		<script src="<?=ROOT?><?=ADMINISTRATION_ROOT?>js/app/customer.js"></script>
		<script src="<?=ROOT?><?=ADMINISTRATION_ROOT?>js/app/product_mst.js"></script>
		<script src="<?=ROOT?>js/app/city_mst.js"></script>
		<script src="<?=ROOT?>js/app/payment_terms.js"></script>
		<script src="<?=ROOT?>js/app/invoice_consignee.js"></script>
		<script src="<?=ROOT?><?=ADMINISTRATION_ROOT?>js/app/state_mst.js"></script>
		<script src="<?=ROOT?>js/app/place_supply.js"></script>
		<script src="<?=ROOT?>js/app/mode_disptch.js"></script>
		<script src="<?=ROOT?>js/app/work_type.js"></script>
		<script src="<?=ROOT?>js/app/description_mst.js"></script>
		<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/common_form_finance.js?<?=time()?>"></script>
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
			$('.default_date').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true,
				startDate:'<?php echo date("d-m-Y", strtotime($financial_year['financial_start_date'])) ?>',
				endDate:'<?php echo date("d-m-Y", strtotime($financial_year['financial_end_date'])) ?>',

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
				echo "<script>get_series_no();</script>";
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
 