<?php 
	session_start();
	$path = '../../';
	$include1 = '../include/';
	$include = '../../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
        
        $bulkAccessArray = canCheckPermissionAccess($dbcon, [
            FINANCE_RECEIPT_CREATE,
            FINANCE_RECEIPT_EDIT,
        ]);
        
        if(!in_array(FINANCE_RECEIPT_CREATE,$bulkAccessArray)){
            header("Location: ".DOMAIN."permission_access");
        }
        $company_config = getCompanyConfiguration($dbcon);
        $cust_id = $paid_amount = 0;
        $branch_id = $_SESSION['branch_id'];
        $readable = '';
        $date=date('d-m-Y');
        if(strpos($_SERVER['REQUEST_URI'], "recipt_edit")==true) {
                $readable = 'readonly';
                $disable = 'disabled';
                $form = "Recipt Edit";
                $mode = "Edit";
                $receipt_id = $dbcon->real_escape_string($_REQUEST['id']);
                $query = "SELECT * FROM `tbl_receipt` where receipt_id = ".$receipt_id;
                $rel = mysqli_fetch_assoc($dbcon->query($query));
                $cust_id = $rel['cust_id'];
                $paid_amount = $rel['total_paid_amount'];
                $payment_mode_id = $rel['payment_mode_id'];

        } else {
                $form="New Receive Payment";
                $mode="Add";
                $com="select * from tbl_company where company_id=".$_SESSION['company_id'];
                $comty=mysqli_fetch_assoc($dbcon->query($com));	
        }

 $financial_year=get_financial_year_new($dbcon);
 $countryid='101';
 $stateid='1';
 $cityid='1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>RECEIVED PAYMENT</title>
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
<div class="col-md-12">
<section class="panel">
	<header class="panel-heading">
		<h3><?=$form?></h3>
	</header>	
	<ul class="breadcrumb">
		<li><a href="<?=ROOT.FINANCE_ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?=ROOT.FINANCE_ROOT.'recipt_list'?>">Received Payment List</a></li>
		<li ><?=$form?></a></li>
	</ul>
</section>
</div>	
</div>
<div class="row">			
<div class="col-sm-12">
<section class="panel">
	<header class="panel-heading">
            <?=$form?>
	</header>	
	<div class="panel-body">
		<form class="form-horizontal" role="form" id="purchasepayment_add" action="javascript:;" method="post" name="purchasepayment_add">
			<!-- <div class="col-md-12" style="padding-bottom:15px;display:none;">
				<center><span style="color:#337ab7;">NOTE :</span> <span style="color:red;">1)Due Payment Type Dr = લેવાના </span>&nbsp;&nbsp;&nbsp;
				<span style="color:green;">2)Due Payment Type Cr = આપવાના</span></center>
			</div> -->
			<div class="row">
				<div class="col-md-4">
                	<label class="col-md-5 control-label">Select Branch *</label>
					<div class="col-md-7 col-xs-11 resclear" >
						<select class="select2" name="branch_id" id="branch_id" tabindex="1"  required title="Select Branch">
							<option value="">Choose Branch</option>
							<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
							<?=getBranchBox_new($dbcon, $branch);?>
						</select>
					</div>
                </div>
                <div class="col-md-4">
                	<div class="form-group">
						<label class="col-md-5 control-label">Voucher No</label>
						<div class="col-md-7 col-xs-11">
							<input id="receipt_no" name="receipt_no" type="text" class="form-control" title="Date" value="<?=$rel['receipt_no']?>" placeholder="VOUCHER NO" readonly tabindex="2" >
						</div>
					</div>
				</div>	
				<div class="col-md-4">  	
					<div class="form-group">
						<label class="col-md-5 control-label" >Payment date </label>
						<div class="col-md-7 col-xs-11">
							<input id="payment_date" name="payment_date" type="text" class="form-control default_date" title="Date" value="<?=isset($rel['receipt_date'])?date("d-m-Y",strtotime($rel['receipt_date'])):$date?>" placeholder="Payment Date" tabindex="3">
						</div>
					</div>
				</div>	
				
			</div>

			<div class="row">
				<div class="col-md-4">
					<label class="col-md-5 control-label resclear">Select Bank *</label>
					<div class="col-md-7 col-xs-11">
                        
                        <select class="select2" name="vender_id" id="vender_id"  required data-placeholder="Select Bank" <?= $disable ?> tabindex="4">
							<?= get_ledger($dbcon,$cust_id,' and l_group IN ('.BANK_ACCOUNTS.','.BANK_OD_ACCCOUNTS.','.CASH_IN_HAND.')'); ?>
						</select>
					</div>
					<div class="col-md-2 col-xs-3" style="display:none;">
						<input type="button" name="addcust" id="addcust" class="btn btn-primary" value="View Statement" onclick="open_statement_popup()"/>
					</div>
					<input type="hidden" name="isinterstate" id="isinterstate" value="">
                    <?php
                    if($disable){
                        echo '<input type="hidden" name="vender_id" id="vender_id" value="'.$cust_id.'">';
                    }
                    ?>
				</div>
				<!-- <div class="col-md-4">
					<div class="form-group" style="font-size:14px" id="due_payment_div">
						<label class="col-md-5 control-label">Due Payment</label>
						<div class="col-md-7 col-xs-11">
							<input id="due_payment" name="due_payment"  type="number" min='0'  class="form-control" title="Due amount" readonly="readonly" value="<?=$due?>">
						</div>
						<div class="col-md-7 col-xs-11"  style="font-size:14px">
							<input id="due_payment_type" name="due_payment_type" type="text" class="form-control" title="Due Amount Type" readonly="readonly" value="<?=$due?>">
						</div>
					</div>	
				</div> -->
				<!-- <div class="col-md-4">
					<div class="form-group">
						<label class="col-md-5 control-label">Receive Account*</label>
						<div class="col-md-7 col-xs-11">
							<select class="select2" name="paymentmodeid" id="paymentmodeid" onChange="paymentmode(this.value);/*get_cash_opening_bal(this.value,'max_paid_amount','tran_amounterr');*/" required title="Select Receive Account">
								
							</select>
							
						</div>
					</div>
				</div> -->
				<div class="col-md-4">
					<div class="form-group">
						<label class="col-md-5 control-label">Paid Amount* <span class="currency_icon"></label></label>
						<div class="col-md-5 col-xs-11">
							<input id="paid_amount" name="paid_amount" type="text" min='0' class="form-control numbersOnly" title="" required value="<?= $paid_amount; ?>" max="<?php echo $due; ?>" placeholder="Amount" onkeyup="" tabindex="5">
							
							<!-- <br/><span class="amtbalance" style="display:none"><span class="label label-danger"  >NOTE!</span><span style="font-size:14px;padding-left:10px" id="tran_amounterr"> </span></span> -->
						</div>
						<!-- <div class="col-md-2 col-xs-11"  style="font-size:14px;display:none;">
							<select class="select2" name="paid_typeid" onchange="copy_full_payment();" id="paid_typeid" title="Select Type">
								
							</select>
						</div> -->
					</div>
				</div>
				<div class="col-md-4">							
					<div class="form-group">
						<label class="col-md-5 control-label">GST Nature *</label>
						<div class="col-md-6 col-xs-11">
							<select class="select2" name="gst_nature" id="gst_nature" onchange="get_data_description(this.value)" tabindex="6">
								
								<?php if($mode=='Edit') { echo get_common_category($dbcon, 30,'GST Nature',$rel['gst_nature']); } else {
									echo get_common_category($dbcon, 30,'GST Nature',95);
								} ?>
							</select>
							
						</div>
						<div class="col-md-1">
							<a href="#" class='gst_nature_link'  data-original-title="" data-toggle="tooltip" data-placement="top"><i class="fa fa-info-circle fa-sm" style="padding-top: 12px;padding-left: -18px;margin-left: -13px;"></a></i>
						</div>	
					</div>								
				</div>
			</div>

			<div class="row">
				
				<div class="col-md-4">
					<div class="form-group">
						<label class="col-md-5 control-label">Payment Type *</label>
						<div class="col-md-7 col-xs-11">
							<select class="form-control" name="is_pdc" id="is_pdc" onchange="get_pdc_date(this.value)" required title="Select Payment Type" tabindex="7">
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
							<input type="text" class="form-control default-date-picker" name="pdc_date" id="pdc_date" value="<?=isset($rel['is_pdc'])?date("d-m-Y",strtotime($rel['pdc_date'])):date("d-m-Y");?>" tabindex="8" />
						</div>
					</div>
				</div>

				<div class="col-md-4">	
					<div class="form-group">
						<label class="col-md-5 control-label">Payment Mode*</label>
						<div class="col-md-7">
							<select class="select2" name="payment_mode_id" id="payment_mode_id" onChange="isreferencerequire(this.value);check_pay_mode(this.value)" required title="Select Payment Mode" tabindex="9">
								<?=get_payment_mode($dbcon,$rel['payment_mode_id'])?>
							</select>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				
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
			
			<!-- <div class="row">
				<div class="col-md-4" style="display:none" id="div_billbybill">
					<div class="form-group">
						<label class="col-md-5 control-label">Bill by bill Show </label>
						<div class="col-md-7 col-xs-11">
							<select class="form-control" name="enable_billby_bill_show" id="enable_billby_bill_show" onchange="get_bill_show(this.value);">
								<option value="no" >No</option>
								<option value="yes"  >Yes</option>
							</select>
							<a style="display:none;" href="#" id="billby_bill_link" onclick="get_bill_show('yes')">Show Bill by bill</a>
						</div>
					</div>
				</div>
			</div> -->

			<div class="row">												
				<div class="col-md-4">
					<div class="form-group">
					  <label class="col-md-5 control-label">Currency Converter *</label>
						<div class="col-md-7 col-xs-11">
						
							<input id="currency_enable" name="currency_enable" type="checkbox" class="" title="" value="1" style="width:20%;height:25px;" onChange="currency_change();" tabindex="12"  <?php if($mode=='Edit' && $rel['currency_enable']==1){ echo "checked"; }  ?> >
						
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
							<input id="currency_rate" name="currency_rate" type="text" class="form-control valid numbersOnly" title="" value="<?=isset($rel['currency_rate'])?$rel['currency_rate']:''?>" placeholder="" tabindex="14" >
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
								<th width="15%" class="text-center">Ledger</th>
								<th width="10%" class="text-center">Amount <span class="currency_icon"></label></th>
								<th width="5%" class="text-center"></th>
							</tr>
							<tr id="field" >
								<td data-label="Type">
									<select class="select2" name="entry_type" id="entry_type" title="Select Entry Type" onchange="focus_paid_amt();" tabindex="15">
										<?=getbalance_type($dbcon,'','and balance_typeid=1')?>
									</select>
								</td>
								<td data-label="Ledger">
									<div class="col-md-8">
										<select class="select2" name="receiver_ledger" id="receiver_ledger" onchange="get_ledger_details(this.value);load_billdata(this.value);"  tabindex="16" >
				                            <?= get_ledger($dbcon,$payment_mode_id,' and l_group NOT IN ('.BANK_ACCOUNTS.','.BANK_OD_ACCCOUNTS.','.CASH_IN_HAND.')'); ?>
				                        </select>
										 <a href="#" id="billby_bill_link" onclick="get_bill_show('yes','invoice','entry_amount','receiver_ledger')" style="display: none;">Adjust Amount Bill by bill</a>
									</div>
									<div class="col-md-4">
										<button accesskey="n" style="margin-top: 5px;" class="btn btn-round btn-info btn-xs" title="Short-Cut To Open PopUp, Shift + Alt + n " type="button" data-toggle="modal" value="R1" onclick="showledger();"><i class="fa fa-plus"></i> Add Ledger</button>
										<!-- <a href="#"  data-original-title="Short-Cut To Open PopUp, Shift + Alt + n " data-toggle="tooltip" data-placement="top" ><i class="fa fa-info-circle fa-sm" style="color: black;"></a></i> -->
									</div>
								</td>
								<td data-label="Amount">
									<input type="text"  title="Enter Amount" min="0" id="entry_amount" name="entry_amount" onkeyup="verify_amount(this.value);"  class="form-control numbersOnly" tabindex="17" />
									<input type="hidden"  id="r_amount" name="r_amount" value="" />
								</td>
								<td data-label="">
									<input type="button"  name="addrow" id="addrow" onClick="return add_payment_entry_field();"  class="btn btn-primary" value="Add" tabindex="18"  />
								</td>
								<input type='hidden' name='edit_payment_entry_id' id='edit_payment_entry_id' value='' />
							</tr>
						</table>
					</div>
				</div>
				<div class="col-md-12">
					<div class="col-md-2"></div>
					<div class="col-md-10">
						<table cellspacing="10" style="border-spacing:10px;" class="table12 display table table-striped table-bordered" id="payment_entry_table">
							<thead>
								<tr id="field" class="transaction_table_field">
									<th class="text-center" width="15%">Entry Type</th>
									<th class="text-center" width="25%">Ledger Name</th>
									<th class="text-center" width="15%">Amount <span class="currency_icon"></label></th>
									<th class="text-center" width="10%">Action</th>
								</tr>
							</thead>
							<tbody>

							</tbody>
						</table>
					</div>
					
				</div>
			</div><br>
			<div class="row"> 
				<div class="col-md-4">
					<div class="form-group">
						<label class="col-md-5 control-label">Remark</label>
						<div class="col-md-7 col-xs-11">
							<textarea id="payment_desc" name="payment_desc" class="form-control" title="Payment Description"  placeholder="Description" tabindex="19" ><?= isset($rel['payment_remark'])?$rel['payment_remark']:'' ?></textarea>
						</div>
					</div>
				</div>
				<div class="col-md-4">	
					<div class="form-group">
						<div class="col-md-3"></div>
						<div class="col-md-4">
							<div class="checkbox" style="display:none;">
								<label>
									<input type="checkbox" class="" id="tdskasar_show" name="tdskasar_show" onclick="tdskasar_show1();"> <span style="color:red;">&nbsp;&nbsp; View TDS And Kasar </span>
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-md-12">
				<div id="sale_productdata"></div>
			</div>
			<div class="col-md-12"> </div>
			<div class="col-md-12 text-center">
				
				<input id="receipt_no_reference" name="receipt_no_reference" type="hidden" class="form-control"  value="<?=$rel['receipt_no']?>" placeholder="RECEIPT NO" >

				<button type="submit" class="btn btn-success" id="save" name="save" tabindex="20">Save</button>
				<a href="<?=ROOT.FINANCE_ROOT.'recipt_list'?>" type="button" class="btn btn-danger" tabindex="21">Cancel</a>
			</div>
			<!--<button type="button" class="btn btn-success cr" id="save_cheque" name="save & generate cheque" style="display:none;" onclick="save_cheque_genrate();">Save & Generate Cheque</button>-->
							
			<!--Vendor row end-->	
			
			<?php
				$query1="select Max(receipt_id) from tbl_receipt";
				$rows=mysqli_fetch_assoc($dbcon->query($query1));		
				$receiptid=$rows['Max(receipt_id)']+1;
			?>
			<input type='hidden' name='financial_year' id='financial_year' value='<?=$financial_year['financial_year_id'];?>' />
			<input type='hidden' name='receiptid' id='receiptid' value='<?=$receipt_id?>' />
			<input type='hidden' name='save_cheque' id='save_cheque_val' value='0' />
			<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
			<input type='hidden' name='wrong_amount' id='wrong_amount' value='<?=$rel['paid_amount']?>' />	
			<input type="hidden" name="max_paid_amount" id="max_paid_amount"  value=""/>
			<input type="hidden" name="bill_max_paid_amount" id="bill_max_paid_amount"  value=""/>
			<!--Start Added by Dhruv -->
			<input type="hidden" name="company_bill_balance" id="company_bill_balance" value="<?=$company_config['enable_billby_bill_blnc']?>" />
			<input type="hidden" name="payment_type_reciept_pmt_trn" id="payment_type_reciept_pmt_trn" value="2" />
			<input type="hidden" name="receipt_voucher" id="receipt_voucher" value="<?=RECEIPT_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase,receipt">
			<input type="hidden" name="receipt_adv_pay_table" id="receipt_adv_pay_table" placeholder="table name of sale , purchase , payment.." value="tbl_receipt">
			<!-- Bill by bill entry -->
			<input type="hidden" name="bill_adjust_voucher_type" id="bill_adjust_voucher_type" value="<?=RECEIPT_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase">
			<input type="hidden" name="bill_adjust_ledger_id" id="bill_adjust_ledger_id" placeholder="Ledger Id" value="<?=$mode=='Edit'?$rel['ledger_id']:'' ?>">
			<input type="hidden" name="bill_adjust_table" id="bill_adjust_table" value="tbl_receipt" placeholder="table name of sale , purchase , payment.." >
			<input type="hidden" name="bill_adjust_table_id" id="bill_adjust_table_id" value="" placeholder="primary key of that inserted table ">
			<input type="hidden" id="edit_id_bill" value="" />
			<!--End Added by Dhruv -->
		</form>
	</div>	
</section>
</div>
</div>
</section>
</section>
<?php //include_once('../include/add_account.php');
	//include_once('../include/add_bank.php');
	//include_once($include.'preview_statement.php');
	include_once($include1.'add_advance_receipt.php'); //adedd by dhruv
	include_once($include1.'add_billbybill_show.php'); //adedd by dhruv

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
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.FINANCE_ROOT?>js/app/recipt.js?<?=time()?>"></script>
<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/common_form_finance.js?<?=time()?>"></script><!-- adedd by dhruv -->
<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/add_ledger_js.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/ledger.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/consignee.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/customer.js?<?=time()?>"></script>
<!-- <script src="js/app/bank_account.js?"></script> -->

<script>
$(".select2").select2({
	width: '100%'
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
		$('#currency_id').val('');
		get_symbol();
		$("#currency_rate").val('');
	}
}
//End Code By Dhruv

function paymentmode(id)
{
	if(id=="2" && $("#due_payment_type").val()=="CR")
	{//for cheque generate 
		$('#save_cheque').show();
		}else{
		$('#save_cheque').hide();
	}
	
	if(id!="1")
	{	
		$('#cheque_dtl').val('');
		$('#cheque_data').show();
	}
	else{
		$('#cheque_data').hide();
	}
	get_chequeno($("#pur_acc_id").val(),'cheque_dtl')
	
}
function save_cheque_genrate()
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
		show_data();
		
	}
	load_billdata($("#vender_id").val())
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
	$('.datepikerdemo').trigger('click');
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
			url: root_domain+'app/cust_ledger/',
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
	//echo "<script>show_data() </script>";
	echo "<script>get_series_no() </script>";
}
if($mode=="Edit")
{
	echo "<script>
            
            $('#due_payment_div').hide();
            get_ledger_details(".$cust_id.");
            </script>";
    if($rel['enable_billby_bill_show'] == 1){
    	echo "<script>
    			$('#billby_bill_link').show();
    			focus_paid_amt();
    		</script>";
    }
    if($rel['gst_nature'] == 99){
    	echo "<script>
    			showHideLink(99);
    		</script>";
    }
}

echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";

?>			
</body>
</html>
