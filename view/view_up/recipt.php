<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Receive Payment";
	$mode="Add";
	$date=date('d-m-Y');
	$com="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$comty=mysqli_fetch_assoc($dbcon->query($com));	
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
</head>
<body>
<section id="container" >
<?php include_once('../include/include_top_menu.php');?>
<?php include_once('../include/left_menu.php');?>
<section id="main-content">
<section class="wrapper">
<div class="row">
<div class="col-md-12">
<section class="panel">
	<header class="panel-heading">
		<h3><?=$form?></h3>
	</header>	
	<ul class="breadcrumb">
		<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?=ROOT.'recipt_list'?>">Payment List</a></li>
	</ul>
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
		<form class="form-horizontal" role="form" id="purchasepayment_add" action="javascript:;" method="post" name="purchasepayment_add">
			<div class="col-md-12" style="padding-bottom:15px;display:none;">
				<center><span style="color:#337ab7;">NOTE :</span> <span style="color:red;">1)Due Payment Type Dr = લેવાના </span>&nbsp;&nbsp;&nbsp;
				<span style="color:green;">2)Due Payment Type Cr = આપવાના</span></center>
			</div>
			<div class="col-md-12">	
				<div class="form-group">
					<label class="col-md-3 control-label">Payment No </label>
					<div class="col-md-4 col-xs-11 ">
						<input id="receipt_no" name="receipt_no" type="text" class="form-control" title="Date" value="<?=$rel['receipt_no']?>" placeholder="RECEIPT NO" readonly >
					</div>
				</div>	
				<div class="form-group">  	
					<label class="col-md-3 control-label" >Payment date </label>
					<div class="col-md-4 col-xs-11">
						<input id="payment_date" name="payment_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$date?>" placeholder="Payment Date">
					</div>
				</div>	
				<div class="form-group">
					<label class="col-md-3 col-xs-12 control-label resclear">Select Party *</label>
					<div class="col-md-4 col-xs-8 ">
						<select class="select2" name="vender_id" id="vender_id" onChange="load_billdata(this.value);show_data();" >
							<!--<option value="">Choose Party</option>-->
							<?php //=get_ledger_cust($dbcon,$rel['vender_id']);?>
							<?=get_pay_ledger($dbcon,$rel['vender_id']);?>							
						</select>
					</div>
					<div class="col-md-2 col-xs-3" style="display:none;">
						<input type="button" name="addcust" id="addcust" class="btn btn-primary" value="View Statement" onclick="open_statement_popup()"/>
					</div>
				</div>
				<div class="form-group" style="font-size:14px">
					<label class="col-md-3 control-label">Due Payment</label>
					<div class="col-md-3 col-xs-11"  style="font-size:14px">
						<input id="due_payment" name="due_payment"  type="number" min='0'  class="form-control" title="Due amount" readonly="readonly" value="<?=$due?>">
					</div>
					<div class="col-md-1 col-xs-11"  style="font-size:14px">
						<input id="due_payment_type" name="due_payment_type" type="text" class="form-control" title="Due Amount Type" readonly="readonly" value="<?=$due?>">
					</div>
				</div>	
				<div class="form-group">
					<label class="col-md-3 control-label">Receive Account*</label>
					<div class="col-md-4 col-xs-11">
						<select class="select2" name="paymentmodeid" id="paymentmodeid" onChange="paymentmode(this.value);/*get_cash_opening_bal(this.value,'max_paid_amount','tran_amounterr');*/" required title="Select Receive Account">
							<?php //=getpaymentmode($dbcon);?>	
							<?=get_ledger_bank($dbcon);?>	
							<?php //=get_ledger($dbcon,$rel['vender_id']);?>	
						</select>
						
					</div>
				</div>
				<div style="display:none" id="cheque_data">
					<!--<div class="form-group">
						<label class="col-md-3 control-label">Choose Account  *</label>
						<div class="col-md-4 col-xs-10">
						<select class="form-control"  name="pur_acc_id" id="pur_acc_id" required title="Select Bank" onchange="get_opening_bal(this.value,'max_paid_amount','tran_amounterr');get_chequeno(this.value,'cheque_dtl')">
						<?php //=getaccount($dbcon,$rel['acc_id'],'acc_type!=1');?>	
						</select>
						</div>
						<div class="col-md-2 col-xs-2">
						<input type="button"  name="addcust" id="addcust" data-toggle="modal" data-target="#model_addaccount"  class="btn btn-primary" value="+"/>
						</div>
					</div> -->
					
					<div class="form-group dr" id="cheque_display" style="display:none;">
						<label class="col-md-3 control-label">Select Bank *</label>
						<div class="col-md-4 col-xs-10">
							<select class="form-control"  name="bankid" id="bankid" title="Select Bank">
								<?=getbank($dbcon,0,' and bankid!=0')?>	
							</select>
						</div>
						<!--<div class="col-md-2">
							<input type="button"  name="addBank" id="addBank" data-toggle="modal" data-target="#model_addbank"  class="btn btn-primary" value="+"/>
						</div>-->
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">Reference No *</label>
						<div class="col-md-4 col-xs-11">
							<input id="cheque_dtl" name="cheque_dtl" type="text" class="form-control" title="cheque_dtl" value="<?=$rel['cheque_dtl']?>" placeholder="Cheque No. / NEFT No. / RTGS No." >
						</div>
					</div>
					<div class="form-group">  	
						<label class="col-md-3 control-label" >Reference date </label>
						<div class="col-md-4 col-xs-11">
							<input id="ref_date" name="ref_date" type="text" class="form-control default-date-picker" title="Reference Date" value="<?=$date?>" placeholder="Cheque Date/NEFT Date">
						</div>
					</div>	
				</div>	
				<div class="form-group">
					<label class="col-md-3 control-label">Paid Amount*</label>
					<div class="col-md-4 col-xs-11">
						<input id="paid_amount" name="paid_amount" type="number" min='0' class="form-control" title="" required value="" max="<?php echo $due; ?>" placeholder="Amount" onkeyup="copy_full_payment();">
						
						<br/><span class="amtbalance" style="display:none"><span class="label label-danger"  >NOTE!</span><span style="font-size:14px;padding-left:10px" id="tran_amounterr"> </span></span>
					</div>
					<div class="col-md-2 col-xs-11"  style="font-size:14px;display:none;">
						<select class="select2" name="paid_typeid" onchange="copy_full_payment();" id="paid_typeid" title="Select Type">
							<?=getbalance_type($dbcon,1)?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Remark</label>
					<div class="col-md-4 col-xs-11">
						<textarea id="payment_desc" name="payment_desc" class="form-control" title="Payment Description" placeholder="Description" ></textarea>
					</div>
				</div>	
				<div class="form-group">
					<div class="col-md-3"></div>
					<div class="col-md-4">
						<div class="checkbox" style="display:none;">
							<label>
								<input type="checkbox" class="" style="" id="tdskasar_show" name="tdskasar_show" onclick="tdskasar_show1();"> <span style="color:red;">&nbsp;&nbsp; View TDS And Kasar </span>
							</label>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-md-12">
				<div id="sale_productdata"></div>
			</div>
			<div class="col-md-12"> </div>
			<div class="col-md-12 text-center">
				<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
				<a href="<?=ROOT.'recipt_list'?>" type="button" class="btn btn-danger">Cancel</a>
			</div>
			<!--<button type="button" class="btn btn-success cr" id="save_cheque" name="save & generate cheque" style="display:none;" onclick="save_cheque_genrate();">Save & Generate Cheque</button>-->
							
			<!--Vendor row end-->	
			
			<?php
				$query1="select Max(receipt_id) from tbl_receipt";
				$rows=mysqli_fetch_assoc($dbcon->query($query1));		
				$receiptid=$rows['Max(receipt_id)']+1;
			?>
			
			<input type='hidden' name='receiptid' id='receiptid' value='<?=$receiptid?>' />
			<input type='hidden' name='save_cheque' id='save_cheque_val' value='0' />
			<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
			<input type='hidden' name='wrong_amount' id='wrong_amount' value='<?=$rel['paid_amount']?>' />	
			<input type="hidden" name="max_paid_amount" id="max_paid_amount"  value=""/>
			<input type="hidden" name="bill_max_paid_amount" id="bill_max_paid_amount"  value=""/>
		</form>
	</div>	
</section>
</div>
</div>
</section>
</section>
<?php //include_once('../include/add_account.php');
	//include_once('../include/add_bank.php');
	include_once('../include/preview_statement.php');
?>
<?php include_once('../include/footer.php');?>
</section>
<?php include_once('../include/include_js_file.php');?>   
<script src="<?=ROOT?>js/app/recipt.js?<?=time()?>"></script>
<script src="<?=ROOT?>js/app/bank_account.js?<?=time()?>"></script>

<script>
$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
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
?>			
</body>
</html>
