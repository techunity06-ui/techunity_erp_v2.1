<?php 
	session_start();
	$path = '../../';
	$include1 = '../include/';
	$include = '../../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
        // error_reporting(E_ALL);
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		FINANCE_MAKE_PAYMENT_CREATE,
		FINANCE_MAKE_PAYMENT_EDIT
	]);

	$company_config = getCompanyConfiguration($dbcon);
	$date = date('d-m-Y');
	$start_date = date('01-m-Y');
	$end_date = date('d-m-Y');
	$branch_id = $_SESSION['branch_id'];
	$readable = '';
	if(strpos($_SERVER['REQUEST_URI'], "payment_edit")==true) {
			if(!in_array(FINANCE_MAKE_PAYMENT_EDIT,$bulkAccessArray)){
				header("Location: ".DOMAIN."permission_access");
			 }
			$readable = 'readonly';
			$disable = 'disabled';
			$readonly = 'readonly="readonly"';
			$form = "Make Payment";
			$form_type='Edit';
			$mode = "Edit";
			$receipt_id = $dbcon->real_escape_string($_REQUEST['id']);
			$query = "SELECT * FROM `tbl_receipt` where receipt_id = ".$receipt_id;
			$rel = mysqli_fetch_assoc($dbcon->query($query));
			$vender_id = $rel['cust_id'];
			$paid_amount = $rel['total_paid_amount'];
			$payment_mode_id = $rel['payment_mode_id'];

			//print_r($rel);

	} else {
			if(!in_array(FINANCE_MAKE_PAYMENT_CREATE,$bulkAccessArray)){
				header("Location: ".DOMAIN."permission_access");
			}
			$form = "Make Payment";
			$form_type='New';
			$mode = "Add";
	}
        
	$com="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$comty=mysqli_fetch_assoc($dbcon->query($com));	
	
    $financial_year=get_financial_year_new($dbcon);
    $countryid='101';
	$stateid='1';
	$cityid='1';
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>MAKE PAYMENT</title>
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
					<h3><?=$form?></h3>
				</header>	
				<ul class="breadcrumb">
					  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
					  <li><a href="<?=ROOT.FINANCE_ROOT.'payment_list'?>">Payment List</a></li>
				</ul>
			</section>
		</div>	
	</div>
	<div class="row">			
		<div class="col-sm-12">
			<section class="panel">
				<header class="panel-heading">
				  <?=$form_type?>  <?=$form?>
				</header>	
				<div class="panel-body">
					<form class="form-horizontal" role="form" id="purchasepayment_add" action="javascript:;" method="post" name="purchasepayment_add">
						<div class="col-md-12" style="padding-bottom:15px;display:none;">
							<center><span style="color:#337ab7;">NOTE :</span> <span style="color:red;">1)Due Payment Type Dr = લેવાના </span>&nbsp;&nbsp;&nbsp;
								   <span style="color:green;">2)Due Payment Type Cr = આપવાના</span></center>
						</div>
						
						<div class="row">
							<div class="col-md-4">
								<label class="col-md-5 control-label">Select Branch *</label>
								<div class="col-md-7 col-xs-11 resclear" >
									<select class="select2" name="branch_id" id="branch_id" tabindex="1"  required title="Select Branch">
										<option value="">--Please Select Branch--</option>
										<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
										<?=getBranchBox_new($dbcon, $branch);?>
									</select>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label class="col-md-5 control-label">Payment No </label>
									<div class="col-md-7 col-xs-11">
										<input id="receipt_no" name="receipt_no" type="text" class="form-control" title="Date" value="<?=$rel['receipt_no']?>" placeholder="RECEIPT NO" readonly tabindex="2" >
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">  	
									<label class="col-md-5 control-label" >Payment date </label>
									<div class="col-md-7 col-xs-11">
										<input id="payment_date" name="payment_date" type="text" class="form-control default-date-picker" title="Payment Date" value="<?=isset($rel['receipt_date'])?date("d-m-Y",strtotime($rel['receipt_date'])):$date?>" placeholder="Payment Date" required tabindex="3">
									</div>
								</div>	
							</div>
							
						</div>
						
						<div class="row">
							
							<div class="col-md-4">
								<div class="form-group">
									<label class="col-md-5 col-xs-12 control-label resclear">Select Party *</label>
									<div class="col-md-7 col-xs-8">
										<select class="select2" name="paymentmodeid" id="paymentmodeid" onChange="get_cash_opening_bal(this.value,'max_paid_amount','tran_amounterr');" title="Select Debit Account" tabindex="4">
											<?= get_ledger($dbcon,$vender_id,' and l_group IN ('.BANK_ACCOUNTS.','.BANK_OD_ACCCOUNTS.','.CASH_IN_HAND.')'); ?>
										</select>

									</div>
									<input type="hidden" name="isinterstate" id="isinterstate" value="">
								</div>
							</div>
							

							
								
								<!--<div class="form-group" id="due_payment_div" style="font-size:14px">
									<label class="col-md-5 control-label">Due Payment</label>
									<div class="col-md-6 col-xs-11"  style="font-size:14px">
										<input id="due_payment" name="due_payment"  type="number" min='0'  class="form-control" title="Due amount" readonly="readonly" value="<?=$due?>">
									</div>
									<div class="col-md-1 col-xs-11"  style="font-size:14px">
										<input id="due_payment_type" name="due_payment_type"  type="text"   class="form-control" title="Due Amount Type" readonly="readonly" value="<?=$due?>">
									</div>
								</div> -->
								
							
							<div class="col-md-4">
								
								<div class="form-group">
									<label class="col-md-5 control-label">Paid Amount* <span class="currency_icon"></label>
									<div class="col-md-7 col-xs-11">
										<input id="paid_amount" name="paid_amount" type="text" min='0' class="form-control numbersOnly" title="" required value="<?= $paid_amount; ?>" max="<?phpecho $due; ?>" placeholder="Amount"  onchange="validateFloatKeyPress(this);" tabindex="5">
									</div>
								</div>
								
							</div>
							
							<!-- <div class="col-md-4">	
							<div class="form-group">
								<label class="col-md-5 control-label">Payment Mode*</label>
								<div class="col-md-7">
									<select class="select2" name="paymentmodeid" id="paymentmodeid" onChange="paymentmode(this.value);/*get_cash_opening_bal(this.value,paymentmode'max_paid_amount','tran_amounterr');*/ isreferencerequire(); " required title="Select Debit Account">
								
									</select>
								</div>
							</div>
							</div> -->
							<div class="col-md-4">	
								<div class="form-group">
									<label class="col-md-5 control-label">Payment Mode*</label>
									<div class="col-md-7">
										<select class="select2" name="payment_mode_id" id="payment_mode_id" onChange="isreferencerequire(this.value);check_pay_mode(this.value)" required title="Select Payment Mode" tabindex="8">
											<?=get_payment_mode($dbcon,$rel['payment_mode_id'])?>
										</select>
									</div>
								</div>
							</div>

							<!-- <div class="col-md-4" style="display:none" id="div_billbybill">
								<div class="form-group">
									<label class="col-md-5 control-label">Bill by bill Show </label>
									<div class="col-md-7 col-xs-11">
										<select class="form-control" name="enable_billby_bill_show" id="enable_billby_bill_show" onchange="focus_paid_amt_billbybill(this.value);" tabindex="7">
											<option value="no" selected>No</option>
											<option value="yes">Yes</option>
										</select>
										<a style="display:none;" href="#" id="billby_bill_link" onclick="get_bill_show('yes')">Show Bill by bill</a>
									</div>
								</div>
							</div> -->
							
						</div>
						
						<div class="row">
							
							<div class="col-md-4">							
								<div class="form-group">
									<label class="col-md-5 control-label">GST Nature *</label>
									<div class="col-md-6 col-xs-11">
										<select class="select2" name="gst_nature" id="gst_nature" onchange="get_data_description(this.value)" required title="Select GST Nature" tabindex="7">
											<?php if($mode=='Edit') { echo get_common_category($dbcon, 29,'GST Nature',$rel['gst_nature']); } else {
												echo get_common_category($dbcon, 29,'GST Nature',69);

											} ?>
										</select>
										<a href="#" style="display: none;" onclick="get_registered_expence_popup('70','field_ledger_id','field_entry_amount')" id="checkRegExpLink" >Check Register Expence</a>
									</div>
									<div class="col-md-1">
										<a href="#" class='gst_nature_link'  data-original-title="" data-toggle="tooltip" data-placement="top"><i class="fa fa-info-circle fa-sm"></a></i>
									</div>	
								</div>								
							</div>
							
							<div class="col-md-4">
								<div class="form-group">
									<label class="col-md-5 control-label">Payment Type *</label>
									<div class="col-md-7 col-xs-11">
										<select class="form-control" name="is_pdc" id="is_pdc" onchange="get_pdc_date(this.value)" required title="Select Payment Type" tabindex="8">
											<option value="">--Select Payment Type--</option>
											<option value="0" <?php if($mode=='Edit' && $rel['is_pdc']=='0'){ echo "selected";  } else { echo "selected"; } ?>>Regular</option>
											<option value="1" <?php if($mode=='Edit' && $rel['is_pdc']=='1'){ echo "selected";  } ?>>PDC</option>
										</select>
									</div>
								</div>
							</div>
							
							<div class="col-md-4 pdc_date_class" style="display:none">
								<div class="form-group">
									<label class="col-md-5 control-label">PDC Date *</label>
									<div class="col-md-7 col-xs-11">
										<input type="text" class="form-control default-date-picker" name="pdc_date" id="pdc_date" value="<?=isset($rel['is_pdc'])?date("d-m-Y",strtotime($rel['pdc_date'])):date("d-m-Y");?>" tabindex="9" />
									</div>
								</div>
							</div>
							
							<div class="reference_field" style="display: none;">
								<div class="col-md-4 cheque_data">
									<div class="form-group">
										<label class="col-md-5 control-label">Reference No *</label>
										<div class="col-md-7 col-xs-11">
											<input id="cheque_dtl" name="cheque_dtl" type="text" class="form-control" title="cheque_dtl" value="<?=isset($rel['cheque_dtl'])?$rel['cheque_dtl']:''?>" placeholder="Cheque No. / NEFT No. / RTGS No." tabindex="10" >
										</div>
									</div>
								</div>
								
								<div class="col-md-4 cheque_data">
									<div class="form-group">  	
										<label class="col-md-5 control-label" >Reference date </label>
										<div class="col-md-7 col-xs-11">
											<input id="ref_date" name="ref_date" type="text" class="form-control default-date-picker" title="Reference Date" value="<?=isset($rel['ref_date'])?date("d-m-Y",strtotime($rel['ref_date'])):$date?>" placeholder="Cheque Date/NEFT Date" tabindex="11">
										</div>
									</div>	
								</div>								
							</div>							
							
						</div>
						<div class="row">												
							<div class="col-md-4">
								<div class="form-group">
								  <label class="col-md-5 control-label">Currency Converter *</label>
									<div class="col-md-7 col-xs-11">
									
										<input id="currency_enable" name="currency_enable" type="checkbox" class="" title="" value="1" style="width:20%;height:25px;" onChange="currency_change();" tabindex="12" <?php if($mode=='Edit' && $rel['currency_enable']==1){ echo "checked"; }  ?>>
									
									</div>
								 </div>
							</div>				
							<div class="col-md-4 currency_div"  style="display:none">
								<div class="form-group">
									<label class="col-md-5 control-label">Convert Currency *</label>
									<div class="col-md-7 col-xs-11">
										<select class="form-control" name="currency_id" id="currency_id" onChange="get_symbol();" tabindex="13">
											<?=getcurrency($dbcon,$rel['currency_id']);?>
										</select>
										
									</div>
								</div>
							</div>				
							<div class="col-md-4 currency_div" style="display:none">
								<div class="form-group">
								  <label class="col-md-5 control-label">Currency Rate *</label>
									<div class="col-md-7 col-xs-11">
										<input id="currency_rate" name="currency_rate" type="text" class="form-control valid numbersOnly" title="" value="<?=isset($rel['currency_rate'])?$rel['currency_rate']:''?>" placeholder="" tabindex="14">
									</div>
								</div>	
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="col-md-2">								
								</div>
								<div class="col-md-10">
									<table cellspacing="10" style=" border-spacing:10px;" class="display table  table-striped table12 table-bordered" id="product_list">
										<tr id="field" >
											<th width="10%" class="text-center">Type</th>
											<th width="10%" class="text-center">Amount <span class="currency_icon"></label></th>
											<th width="15%" class="text-center">Ledger</th>
											<th width="5%" class="text-center"></th>
										</tr>
										<tr id="field" >
											<td data-label="Type">
												<select class="select2" name="entry_type" id="entry_type" title="Select Entry Type" tabindex="15" onchange="focus_paid_amt();">
													<?=getbalance_type($dbcon,'','and balance_typeid=2')?>
												</select>
											</td>
											<td data-label="Amount">
												<input type="text"  title="Enter Amount" min="0" id="entry_amount" name="entry_amount" onkeyup="verify_amount(this.value);" class="form-control numbersOnly" onchange="validateFloatKeyPress(this);" tabindex="16"  />
												<input type="hidden"  id="r_amount" name="r_amount" value="" />
											</td>
											<td data-label="Ledger">
												<div class="col-md-8">
												<select  class="select2" tabindex="17" name="vender_id" id="vender_id" title="Select Party" onChange="load_billdata(this.value);show_payment_data();get_ledger_details(this.value);is_advance_payment('vender_id','entry_amount',1)" tabindex="15"> <?= $disable ?>  >
													<?= get_ledger($dbcon,$vender_id,' and l_group NOT IN ('.BANK_ACCOUNTS.','.BANK_OD_ACCCOUNTS.','.CASH_IN_HAND.')'); ?>
												</select>
												<a href="#" id="billby_bill_link" onclick="get_bill_show('yes','purchase','entry_amount','vender_id')" style="display: none;">Adjust Amount Bill by bill</a>
												</div>
												<div class="col-md-4">
												<button accesskey="n" style="margin-top: 5px;" class="btn btn-round btn-info btn-xs" type="button" data-toggle="modal" title="Short-Cut To Open PopUp, Shift + Alt + n " value="R1" onclick="showledger();"><i class="fa fa-plus"></i> Add Ledger</button>
												<!-- <a href="#"  data-original-title="Short-Cut To Open PopUp, Shift + Alt + n " data-toggle="tooltip" data-placement="top" ><i class="fa fa-info-circle fa-sm" style="color: black;"></a></i> -->
												</div>
												

												<!-- added by dhruv 23-12-2021 -->
												<input type='hidden' name='ledger_Tax_type' id='ledger_Tax_type' value='' />
												
											</td>
											<td data-label="">
												<input type="button"  name="addrow" id="addrow" onClick="return add_payment_entry_field();"  class="btn btn-primary" value="Add" tabindex="18" />
											</td>
											<input type='hidden' name='edit_payment_entry_id' id='edit_payment_entry_id' value='' />
										</tr>
									</table>
								</div>
							</div>
							<div class="col-md-12">
								<div class="col-md-2"></div>
								<div class="col-md-10">
									<table cellspacing="10" style="border-spacing:10px;" class="table12 display table  table-striped table-bordered" id="payment_entry_table">
										<thead>
											<tr id="field" class="transaction_table_field">
												<th class="text-center" width="15%">Entry Type</th>
												<th class="text-center" width="25%">Ledger Name</th>
												<th class="text-center" width="15%">Amount <span class="currency_icon"></th>
												<th class="text-center" width="10%">Action</th>
											</tr>
										</thead>
										<tbody style="text-align: center !important;">

										</tbody>
									</table>
								</div>
								
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="col-md-1"></div>
								<div class="col-md-10" id="sale_productdata"></div>
							</div>
						</div>
						
						<div class="row">							
							<div class="col-md-4" style="display:none" id="div_cost_center">
								<div class="form-group">
									<label class="col-md-5 control-label">Cost Center </label>
									<div class="col-md-7 col-xs-11">
										<select class="form-control" name="enable_cost_center" id="allocate_cost_center" onchange="get_cost_center(this.value)" tabindex="19">
											<option value="no" selected>No</option>
											<option value="yes">Yes</option>
										</select>
										<a style="display:none;" href="#" id="cost_center_link" onclick="get_cost_center('yes')">Show Cost Center Transaction</a>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label class="col-md-5 control-label"> TDS Deduction details </label>
									<div class="col-md-7 col-xs-11">
										<select class="form-control" name="" id="allocate_cost_center" onchange="" tabindex="20">
											<option value="no" selected>No</option>
											<option value="yes">Yes</option>
										</select>
										<a style="display:none;" href="#" id="" onclick="">Show Transaction</a>
									</div>
								</div>
							</div>
							
						</div>
						
						<div class="row">
							
							<div class="col-md-4">
								<div class="form-group">
									<label class="col-md-5 control-label">Remark</label>
									<div class="col-md-7 col-xs-11">
										<textarea id="payment_desc" name="payment_desc" class="form-control" title="Payment Description" tabindex="21" ><?=$rel['payment_remark']?></textarea>
									</div>
								</div>	
							</div>
							
						</div>
						
					
						
						<div class="col-md-12" id="product_data" style="display: none;">
							<div id="sale_productdata"></div>
						</div>
						
						<div class="col-md-12"></div>
						<div class="col-md-12 text-center">
							<button type="submit" class="btn btn-success" id="save" name="save" tabindex="22">Save</button>
                                                        <button type="button" class="btn btn-success cr" id="save_cheque" name="save & generate cheque" style="display:none;" onclick="save_cheque_generate();" tabindex="23">Save & Generate Cheque</button>
							<a href="<?=ROOT.FINANCE_ROOT.'payment_list'?>" type="button" class="btn btn-danger">Cancel</a>
						</div>
											
						<!--Vendor row end-->	
		
							<?php
								$query1="select Max(receipt_id) from tbl_receipt";
								$rows=mysqli_fetch_assoc($dbcon->query($query1));		
								$receiptid=$rows['Max(receipt_id)']+1;
							?>
								<input type='hidden' name='financial_year' id='financial_year' value='<?=$financial_year['financial_year_id'];?>' />
								<input type='hidden' name='receiptid' id='receiptid' value='<?=$receiptid ?>' />
								<input type='hidden' name='save_cheque' id='save_cheque_val' value='0' />
								<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
								<input type='hidden' name='wrong_amount' id='wrong_amount' value='<?=$rel['paid_amount']?>' />
								<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />	
								<input type="hidden" name="max_paid_amount" id="max_paid_amount"  value=""/>
								<input type="hidden" name="bill_max_paid_amount" id="bill_max_paid_amount"  value=""/>
								<!-- Company Settings added by dhruv -->
								<input type="hidden" name="company_cost_center" id="company_cost_center" value="<?=$company_config['enable_cost_center']?>" />
								<input type="hidden" name="company_bill_balance" id="company_bill_balance" value="<?=$company_config['enable_billby_bill_blnc']?>" />
								<input type="hidden" name="payment_type_reciept_pmt_trn" id="payment_type_reciept_pmt_trn" value="1" />

								<!-- cost center popup added by dhruv--> 											
								<input type="hidden" name="cost_center_voucher_type" id="cost_center_voucher_type" value="<?=PAYMENT_VOUCHER?>" />
								<input type="hidden" name="cost_center_ledger_id" id="cost_center_ledger_id" placeholder="Ledger Id" value="<?=$mode=='Edit'?$rel['ledger_id']:'' ?>">
								<input type="hidden" name="cost_center_table" id="cost_center_table" value="tbl_receipt" placeholder="table name of sale , purchase , payment..">
								<input type="hidden" name="cost_center_table_id" id="cost_center_table_id" value="" placeholder="primary key of that inserted table ">
								<input type="hidden" id="edit_id" value="" />

								<!-- Bill by bill entry -->
								<input type="hidden" name="bill_adjust_voucher_type" id="bill_adjust_voucher_type" value="<?=PAYMENT_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase">
								<input type="hidden" name="bill_adjust_ledger_id" id="bill_adjust_ledger_id" placeholder="Ledger Id" value="<?=$mode=='Edit'?$rel['ledger_id']:'' ?>">
								<input type="hidden" name="bill_adjust_table" id="bill_adjust_table" value="tbl_receipt" placeholder="table name of sale , purchase , payment.." >
								<input type="hidden" name="bill_adjust_table_id" id="bill_adjust_table_id" value="" placeholder="primary key of that inserted table ">
								<input type="hidden" id="edit_id_bill" value="" />
								<!-- voucher type -->
								<input type="hidden" name="payment_voucher" id="payment_voucher" value="<?=PAYMENT_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase,receipt">
								<input type="hidden" name="payment_voucher_table" id="payment_voucher_table" value="tbl_receipt" placeholder="table name of sale , purchase , payment..">
								<input type="hidden" name="payment_voucher_id" id="payment_voucher_id" value="" placeholder="primary key of that inserted table">

								<input type="hidden" name="company_tds_per" id="company_tds_per" value="<?=$company_config['enable_tds_reporting']?>">
								<input type="hidden" name="payment_refund_adv_pay_table" id="payment_refund_adv_pay_table" placeholder="table name of sale , purchase , payment.." value="tbl_receipt">

								<!-- payment refrence number for bill by bill popup -->

								<input id="receipt_no_reference" name="receipt_no_reference" type="hidden" class="form-control"  value="<?=$rel['receipt_no']?>" placeholder="RECEIPT NO" >


					</form>
				</div>	
			</section>
		</div>
	</div>
</section>
</section>
<?php 
	// error_reporting(E_ALL);
	include_once($include.'add_account.php');
	include_once($include1.'add_cost_center.php'); //adedd by dhruv
	include_once($include1.'add_billbybill_show.php'); //adedd by dhruv
	include_once($include1.'add_tds_advance_pyment.php'); //adedd by dhruv

	include_once($include1.'test_tds_advance.php'); //adedd by dhruv

	include_once($include1.'add_refund_advance_receipt.php'); //adedd by dhruv
	include_once($include1.'add_registered_expence.php'); //adedd by dhruv
	include_once($include1.'add_payment_to_gov.php'); //adedd by dhruv
	include_once($include1.'adjust_tds_reference.php');
	include_once($include1.'adjust_tcs_reference.php');

	include_once($include1.'add_ledger.php');

	include_once($path.'administration/include/add_multi_currency.php');
	include_once($path.'administration/include/add_multi_branch.php');
	include_once($path.'administration/include/add_billbybill_opening.php');
	include_once($path.'administration/include/add_depreciation.php');
	include_once($path.'administration/include/add_bill_sundry.php');
	include_once($path.'administration/include/add_monthly_budget.php');
	include_once($path.'administration/include/add_bank_cheque.php');
?>
<?php include_once($include.'footer.php');?>
</section>
<script src="<?=ROOT.FINANCE_ROOT?>js/app/payment_new.js?<?=time()?>"></script>
<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/common_form_finance.js?<?=time()?>"></script><!-- adedd by dhruv -->
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT?>js/app/bank_account.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/bank_mst.js"></script>
<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/add_ledger_js.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/ledger.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/consignee.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/customer.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%',
	//minimumInputLength: 3
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
$("#vender_id").select2({
	width: '100%',
	//minimumInputLength: 3
});

//Start Added by Dhruv
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
//End Code By Dhruv

// function paymentmode(id)
// {
// 	//alert(id);
// 	$.ajax({
// 		type: "POST",
// 		url: root_domain+finance_root_domain+'app/payment_new/',
// 		data : {mode : "bank_type1",id:id},
// 		success: function(data){
// 			//alert(data);
// 			var data = JSON.parse(data);
// 			//alert(data.type);
// 			if(data.type == "cash"){
// 				$('#cheque_data').hide();
//                                 $('#save_cheque').hide();
// 			}else{
// 				$('#save_cheque').show();
// 				$('#cheque_dtl').val('');
// 				$('#cheque_data').show();
//                                 $('#save_cheque').show();
//                                 get_chequeno(id,'cheque_dtl');
// 			}
// 		}
// 	});
// 	/* if(id=="2" && $("#due_payment_type").val()=="CR")
// 	{//for cheque generate 
// 		$('#save_cheque').show();
// 	}else{
// 		$('#save_cheque').hide();
// 	}
	
// 	if(id!="1")
// 	{	
// 		$('#cheque_dtl').val('');
// 		$('#cheque_data').show();
// 	}
// 	else{
// 		$('#cheque_data').hide();
// 	} */			
// }
function save_cheque_generate()
{
	$('#save_cheque_val').val('1');
	$('#purchasepayment_add').submit();
}
function show_invoiceno()
{
	var data=$('input[name=payment_type]:Checked').val();
	if(data=="1")
	{
		$("#invoice_data").show();
		$("#sale_productdata").hide();
		
	}
	else
	{
		$("#invoice_data").hide();
		$("#adv-table").show();
		$("#sale_productdata").show();
		show_payment_data();
		
	}
	load_billdata($("#vender_id").val());
}
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
function cb(start, end) {
		$('.datepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
	}
	cb(moment().subtract(29, 'days'), moment());
	
  
	$('.datepikerdemo').daterangepicker({       
			locale: {
				format: 'DD-MM-YYYY'
			},
		 "autoApply": true,	
		"startDate": $('#from_date').val(),
		"endDate": $('#to_date').val(),	
		ranges: {
		   'Today': [moment(), moment()],
		   'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
		   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
		   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
		   'This Month': [moment().startOf('month'), moment().endOf('month')],
		   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		}
	}, cb);
        $('.date-set').click(function(){
	   $('.datepikerdemo').trigger('click')
});
function open_statement_popup()
{
if(!$('#vender_id').val())
{
	toastr.warning("SELECT VENDOR", "ERROR");
}
else
{
	$('#bs-example-modal-preivew_statement').modal('show');
	generate_report() ;
}
}
function generate_report() 
{
	var date=$("#rep_date").val();
	var cust_id=$("#vender_id").val();
	if(cust_id!="")
	{
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/cust_ledger/',
		data: { mode : "generate_report", date :  date,cust_id:cust_id},
		success: function(response)
		{
			//console.log(response);
			if(response != "") {
				$('#adv-table1').html(response);
				Unloading();
			}
										
		}
	});	
	}
}

</script>
<?php
if($mode=="Add")
{
//echo "<script>show_payment_data() </script>";
echo "<script>get_series_no() </script>";
}
if($mode=="Edit")
{
	echo "<script>
            $('#due_payment_div').hide();
            focus_paid_amt();
            </script>";
    if($rel['gst_nature'] == 70){
    	echo "<script>
    			showHideLink(70);
    		</script>";
    }
    if($rel['gst_nature'] == 72){
    	echo "<script>
    			showHideLink(72);
    		</script>";
    }
    if($rel['gst_nature'] == 73){
    	echo "<script>
    			showHideLink(73);
    		</script>";
    }
}

echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";

?>		
</body>
</html>
