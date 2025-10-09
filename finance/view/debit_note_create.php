<?php 
	session_start();
	$path = '../../';
	$include1 = '../include/';
	$include = '../../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	include_once($include."function_database_query.php");
	include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
	
	
	$form="Debit Note";
    $branch_id = $_SESSION['branch_id'];
	$countryid='101';$stateid='1';$cityid='1';
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		FINANCE_PURCHASE_RETURN_CREATE,
		FINANCE_PURCHASE_RETURN_UPDATE,
		FINANCE_PURCHASE_RETURN
	]);

	if(!in_array(FINANCE_PURCHASE_RETURN_CREATE,$bulkAccessArray)){
     	header("Location: ".DOMAIN."permission_access");
    }

	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	
	$load_inv_type='19';
	/*var_dump($_SERVER[REQUEST_URI]);*/
	if(strpos($_SERVER[REQUEST_URI], "debitnote_add_qc")==true) {
		$grn_id=$dbcon->real_escape_string($_REQUEST['id']);

		$query_grn="select * from tbl_grn where grn_id=$grn_id";
		$rel_grn=brp_mysqli_fetch_assoc($dbcon->query($query_grn));

		$mode="Add";
		$date=date('d-m-Y');
		$order_date='';
		$dlt_trn['debitnote_trn_status']=2;
		$updateid=update_record('tbl_debitnote_trn',$dlt_trn,"debitnote_trn_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
		$rel['vender_id']=$rel_grn['vender_id'];
		$disable = 'disabled';
	}
	else if(strpos($_SERVER['REQUEST_URI'], "debitnoteedit")==true)
	{	
		if(!in_array(FINANCE_PURCHASE_RETURN_UPDATE,$bulkAccessArray)){
				header("Location: ".DOMAIN."permission_access");
		}
		
		$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_debitnote where debitnote_id='$invoiceid'";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
                
		if(!$rel){
			header("Location: ".ROOT."invoice_list");
		}
		$currency_enable="";
		if($rel['currency_enable']==1){
			$currency_enable="checked";
		}
		$currency_rate="";
		if($rel['currency_rate']){
			$currency_rate = $rel['currency_rate'];
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
		<title>DEBIT NOTE</title>
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
									<?phpinclude_once($include1.'head_menu_purchase_return.php') ?>
								</header>	
								<div class="">
								  <ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li><a href="<?=ROOT.FINANCE_ROOT.'debitnote'?>">Debit Note List</a></li>
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
												
												<label class="col-md-4 control-label">Purchase Ledger*</label>
												<div class="col-md-8 col-xs-10 resclear" >
													<?php $purchase_grp_array=implode(",",array(PURCHASE_ACCOUNTS));
														$purchase_account = isset($rel['sales_ledger_id']) ? $rel['sales_ledger_id'] : PURCHASE_ACCOUNT ;
													 ?>
													<select class="select2" <?= $disable ?> name="sales_ledger_id" id="sales_ledger_id" title="Select Purchase Ledger" tabindex="1">
														<?= f_get_group_ledger($dbcon,$purchase_grp_array,$purchase_account);?>
													</select>
													<?php
								                    if($disable){
								                        echo '<input type="hidden" name="sales_ledger_id" id="sales_ledger_id" value="'.$rel['sales_ledger_id'].'">';
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
														<select class="select2" <?= $disable ?> name="cust_id" id="cust_id" tabindex="3" onchange="get_invoice_by_cust(this.value,'invoice_number');get_statecode(this.value);get_ledger_details(this.value);get_gtotal();get_grossbalance(this.value);get_invoice_total_tax();" >
															<?=getcust($dbcon,$rel['vender_id']);?>	
														</select>
														<?php
									                    	if($disable){
									                        echo '<input type="hidden" name="cust_id" id="cust_id" value="'.$rel['vender_id'].'">';
									                    	}
									                    ?>
														<strong style="display:none;color:green" id="gross">Gross balance : <span class="gross"></span></strong> <br><strong id="statecode" style="display:none;color:blue">GST StateCode : <span class="statecode"></span></strong>
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
														<input id="voucher_no" name="voucher_no" type="text" class="form-control" title="Enter Voucher No" value="<?=$mode=='Edit'?$rel['debitnote_no']:''?>" placeholder="Voucher No" required readonly tabindex="4">		
													</div>
												</div>
											</div>
											
											
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Material Center *</label>
													<div class="col-md-8 col-xs-11">
														<select class="select2" id="mat_center" name="mat_center" tabindex="5">
															<?=get_all_godown($dbcon,$rel['material_center']);?>
														</select>	
													</div>
												</div>
											</div>
										
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Date *</label>
													<div class="col-md-8 col-xs-11">
														<input type="text" class="form-control default-date-picker" name="debitnote_date" id="debitnote_date" value="<?phpif($mode=='Add'){ echo date("d-m-Y");}else if($mode=='Edit'){ if($rel['debitnote_date']=='0000-00-00'){ echo ""; } else { echo date('d-m-Y',strtotime($rel['debitnote_date']));} } ?>" tabindex="6" />
													</div>
												</div>
											</div>
											
											
										</div>
										
										<div class="row">
											<div class="col-md-12">
												
												<div class="col-md-4">
													<div class="form-group">
													  <label class="col-md-4 control-label">Currency Converter *</label>
														<div class="col-md-8 col-xs-11">
														
															<input id="currency_enable" name="currency_enable" type="checkbox" class="" title="" value="1" style="width:20%;height:25px;" onChange="currency_change();" <?=$currency_enable?>>
														
														</div>
													 </div>
												</div>
												
												<div class="col-md-4 currency_div"  style="display:none">
													<div class="form-group">
														<label class="col-md-4 control-label">Convert Currency *</label>
														<div class="col-md-6 col-xs-11">
															<select class="form-control" name="currency_id" id="currency_id" onChange="get_symbol();">
																<?=getcurrency($dbcon,$rel['currency_id']);?>
															</select>
															
														</div>
													</div>
												</div>
												
												<div class="col-md-4 currency_div" style="display:none">
													<div class="form-group">
													  <label class="col-md-4 control-label">Rate *</label>
														<div class="col-md-6 col-xs-11">
															<input id="currency_rate" name="currency_rate" type="text" class="form-control valid numbersOnly" title="" value="<?=$currency_rate?>" placeholder="">
														</div>
													</div>	
												</div>
											</div>
										</div>
										<!--<div class="row">
											
											<div class="col-md-4" id="currency_enable_div" style="display:none">
												<div class="form-group">
													<label class="col-md-4 control-label">Enable Multi Currency *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="sale_enable_multi_currency" id="sale_enable_multi_currency" onChange="get_conv_div(this.value)" tabindex="7">
															<option value="0">--Enable Multi Currency--</option>
															<option value="1" <?=$mode=='Edit' && $rel['currency_enable']==1?'selected':''?>>Yes</option>
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
											
											
										</div>-->
										
										
										<div class="row">
										
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-spacing:10px;" id="product_list" class="display table table-bordered table-striped">
														<tr id="field">
															<th class="text-center grn">GRN</th>
															<th  class="text-center purchase_bill">Purchase Bill No</th>
															<th  class="text-center">Product Detail</th>
															<th  class="text-center">Unit</th>
															<th  class="text-center">Quantity</th>
															<th  class="text-center">Rate  <span class="currency_icon"></span></th>
															<th  class="text-center">Amount  <span class="currency_icon"></span></th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td style="vertical-align:top;" class="grn">
																<select class="select2" name="grn_id" id="grn_id" onChange="load_grn_data(this.value);">
																	<?=get_grn_for_debitnote($dbcon,$vender_id,"",$mode);?>
																</select>
															</td>

															<td style="vertical-align:top;" >
																
																<select class="select2" name="invoice_number" id="invoice_number" onchange="get_product_from_invoice(this.value)" tabindex="8">
																	
																</select>
																
																<strong class="error" id="inv_number_err" style="color:red"></strong> 
															</td>
															<td style="vertical-align:top;">
																<select class="select2" title="Select product" name="product_id" id="product_id" onChange="load_productdetail(this.value);"  tabindex="8">
																	
																</select>
																<strong class="hsncode" style="display:none;color:blue">HSN Code : <span id="hsncode"></span></strong>, 
																<strong class="purcha_qty" style="display:none;color:green">
																Debit Note Qty Remained : <span id="purcha_qty"></span></strong>
																<br/><br/>
																<textarea id="product_des" name="product_des" class="form-control" placeholder="Product Description"  tabindex="10"></textarea>
															</td>
															<td style="vertical-align:top;">
																<select class="form-control"  title="Select Unit" name="rate_unit_id" id="rate_unit_id"  onclick="load_product_unit();" tabindex="13">
																	<option value="0">Select Unit</option>
																</select>
															</td>	
															<td style="vertical-align:top;">
																<div id="convert_unit_block" style="display:none;" >
		                                                            <input type="text"  title="Enter Qty" min="0" id="product_conv_qty" name="product_conv_qty"  class="form-control numbersOnly" onkeyup="product_convert_qty(1);"onChange="get_discount('per');" />
		                                                            <input type="hidden" name="conv_unitid" id="conv_unitid" value="" />
		                                                            <input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />
		                                                            <span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning  btn-xs" id="convert_unit_show">  </span>
		                                                        </div>

		                                                        <div id="base_unit_block" style="">
		                                                        	<input type="text" min="0" id="product_qty" name="product_qty"  class="form-control numbersOnly" onKeyUp="product_convert_qty(2);get_amount();"
		                                                        	onchange="get_discount('per');"  tabindex="11" />
																	<input type="hidden" name="unit_id" id="unit_id" value="" />
																	<input type="hidden" min="0" id="product_qty_hid" name="product_qty_hid"  class="form-control" onKeyUp="get_amount();"/>
																	<input type="hidden" min="0" id="product_qty_hide" name="product_qty_hide"  class="form-control" onKeyUp="get_amount();"/>
																	<span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs" id="unit_show" >  </span>
		                                                        </div>
																
																<input type="hidden" min="0" id="product_tax" name="product_tax"  class="form-control" onKeyUp="get_amount();"/>
															</td>
															<td style="vertical-align:top;">
																<input type="text"  title="Enter Rate" min="0" id="product_rate" name="product_rate" onKeyUp="get_amount();" class="form-control numbersOnly"  tabindex="12" /><br/>
																
															</td>
															
															
															<td style="vertical-align:top;"> 
																<input type="text" min="0" id="product_amount" readonly="readonly" name="product_amount" class="form-control numbersOnly" onmouseover="this.title=this.value"  tabindex="14" />
															</td>
															<td style="vertical-align:top;"> 
																<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"  tabindex="15" />	
															</td>
															<input type='hidden' name='pro_cal_type' id='pro_cal_type' value='' />
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
													<label class="col-md-5 control-label">Total * <span class="currency_icon"></span></label>
													<div class="col-md-5 col-xs-11">
														<input id="total" name="total" type="text" readonly="readonly" class="form-control" title="Grand Total" max="0"  value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $rel['basic_total'];}?>" placeholder="total">
													</div>
												</div>	
													
												<div class="invoiceTotalTax">
													
												</div>
												
												<div class="sundryadded">
													
												</div>
												
												<div class="form-group">
													<label class="col-md-5 control-label">Net Amount * <span class="currency_icon"></span></label>
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
															<input id="bill_sundry_amount" name="bill_sundry_amount" type="text" class="form-control numbersOnly" placeholder="Amount" title="Amount" value="<?=$rel['amount']?>" >
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
															<option value="yes" <?php if($mode=='Edit' && $rel['cost_center_enable']==1){ echo "selected"; } else{ echo ""; } ?>>Yes</option>
														</select>
														<?php if($mode=='Edit' && $rel['cost_center_enable']==1){ $style=""; } else { $style='display:none'; } ?>
																<a style="<?=$style;?>" href="#" id="cost_center_link" onclick="get_cost_center('yes')">Show Cost Center Transaction</a>
													</div>
												</div>
											</div>
										
											
										<?php /**	<div class="col-md-4" style="display:none" id="salesman_div">
												<div class="form-group">
													<label class="col-md-4 control-label">Enable Salesman *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="enable_salesman" id="enable_salesman"  onchange="get_ledger_salesman(this.value,'total')">
															<option value="no" selected>No</option>
															<option value="yes" <?php if($mode=='Edit' && $rel['salesman_enable']==1){ echo "selected"; } else{ echo ""; } ?>>Yes</option>
														</select>
														
														<?php if($mode=='Edit' && $rel['salesman_enable']==1){ $style=""; } else { $style='display:none'; } ?>
														
														<a style="<?=$style;?>" href="#" id="salesman_link" onclick="get_ledger_salesman('yes','g_total')">Show Salesman Details</a>
													</div>
												</div>
											</div> **/ ?>
										
											
										</div>
											
										
										<div class="clearfix"></div>
										<div class="row">
											
											<div class="col-md-12">
                                                                        
												 <div class="form-group">
														<label class="col-md-1 control-label">Remarks </label>
														<div class="col-md-11 col-xs-11">
																<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['narration']?></textarea> 
														</div>
												</div>
											</div>	
											
										</div>
										<div class="clearfix"></div>
										<div class="row" style="margin-top:10px;">
												<div class="col-md-12">
													<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
													<button type="button" onClick="invoice_submit();" class="btn btn-success" id="saveprint" name="saveprint" style="display:none">Save and Print</button> &nbsp;
													<a href="<?=ROOT.FINANCE_ROOT.'debitnote'?>" type="button" class="btn btn-danger">Cancel</a>
													<div class="col-md-3"></div>			
												</div>		
											
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type='hidden' name='o_total' id='o_total' value='<?=$rel['g_total']?>' />
											<input type='hidden' name='save_print' id='save_print' value='' />
											<input type='hidden' name='eid' id='eid' value='<?=$rel['debitnote_id']?>' />
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
											
											
											<input type="hidden" name="company_eway" id="company_eway" value="<?=$getCompanyConfig['enable_eway_bill']?>" />
											
											<input type="hidden" name="enable_multi_currency" id="enable_multi_currency" value="<?=$getCompanyConfig['enable_multi_currency']?>" />
											
											
											<!-- cost center popup --> 
											
											<input type="hidden" name="cost_center_voucher_type" id="cost_center_voucher_type" value="<?=PURCHASE_RETURN_VOUCHER?>" />
											<input type="hidden" name="cost_center_ledger_id" id="cost_center_ledger_id" placeholder="Ledger Id" value="<?=$mode=='Edit'?$rel['sales_ledger_id']:'' ?>">
											<input type="hidden" name="cost_center_table" id="cost_center_table" value="tbl_debitnote" placeholder="table name of sale , purchase , payment..">
											<input type="hidden" name="cost_center_table_id" id="cost_center_table_id" value="<?=$mode=='Edit'?$rel['debitnote_id']:'0'?>" placeholder="primary key of that inserted table ">
											
											
											<!-- Salesman transaction popup -->
											<input type="hidden" name="salesman_voucher_type" id="salesman_voucher_type" value="<?=PURCHASE_RETURN_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase">
											<input type="hidden" name="salesman_voucher_table" id="salesman_voucher_table" placeholder="table name of sale , purchase , payment.." value="tbl_debitnote">
											
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
		<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/debitnote.js?<?php echo time(); ?>"></script>
		
		<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/common_form_finance.js?<?=time()?>"></script>
		<script src="<?=ROOT?><?=ADMINISTRATION_ROOT?>js/app/customer.js?<?=time()?>"></script>
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

			function currency_change()
			{
				if($('#currency_enable').is(":checked"))
				{
					$('.currency_div').show();
				}
				else
				{
					$('.currency_div').hide();
				}
			}
		</script>
		<?php 

			if($mode=="Add"){
				echo "<script>load_invoiceno(".$load_inv_type.");</script>";
			}
			if($quotation_id){
				echo "<script>copy_quot_trn_data(".$quotation_id.");</script>";
			}
			if($complaint_id){
				echo "<script>copy_comp_spare_trn_data(".$complaint_id.");</script>";
			}
			
			echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
			echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";

			echo "<script>load_state(".$countryid.",'con_stateid',".$stateid.")</script>";
			echo "<script>load_city(".$stateid.",'con_cityid',".$cityid.")</script>";

			if(!empty($grn_id)){ 
				echo "<script>load_ven_grn(".$rel['vender_id'].",".$grn_id.")</script>";
				echo "<script>load_grn_data(".$grn_id.")</script>";
				echo "<script>get_invoice_by_cust(".$rel['vender_id'].",'invoice_number','',".$grn_id.")</script>";
				echo "<script>get_statecode(".$rel['vender_id'].")</script>";
				echo "<script>get_ledger_details(".$rel['vender_id'].")</script>";
				echo "<script>get_gtotal()</script>";
				echo "<script>get_grossbalance(".$rel['vender_id'].")</script>";
				echo "<script>get_invoice_total_tax()</script>";
				//echo "<script>$('#vender_id').select2('readonly',true)</script>";
			}
		?>
	</body>
</html>
 