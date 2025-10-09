<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/coman_function.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Purchase Payment";
	if(strpos($_SERVER[REQUEST_URI], "payment-update")==false)
	{
		$mode="Add";
		$date=date('d-m-Y');
		$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
		//var_dump($invoiceid);
		$arr=get_serise_common($dbcon,'7');
		$receiptid=$arr['paymentno'];
	}
	else if(isset($_REQUEST['id']))
	{
		$eid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select pmst.*,cust.company_name from payment_mst as pmst left join tbl_customer as cust on cust.cust_id=pmst.partyid where payment_mstid=$eid";
		$rel=mysqli_fetch_assoc($dbcon->query($query));		
		$mode="Edit";
		$date=date('d-m-Y',strtotime($rel['payment_date']));
		
	}							
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
</head>
<body>
  <section id="container" >
      <?php include_once('../include/include_top_menu.php');?>
      <!--sidebar start-->
      <?php include_once('../include/left_menu.php');?>
      <!--sidebar end-->
      <!--main content start-->
           <section id="main-content">
          <section class="wrapper">
		    <div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
				  <section class="panel">
					  <header class="panel-heading">
						  <h3><?phpif($mode=="Add"){echo $form;}else if($mode=="payment"){echo $form.' #'.$rel['po_no'];}?></h3>
						</header>	
							<div class="">
						  <ul class="breadcrumb">
								<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
								<li>
									<a href="<?=ROOT.'purchasepayment_list'?>">Purchase Payment List</a>
								</li>
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--state overview start-->
		  <div class="row">			
			<div class="col-sm-12">
				<section class="panel">
				  <header class="panel-heading">
					  New <?=$form?>
					</header>	
			
				<div class="panel-body">
			<form class="form-horizontal" role="form" id="purchasepayment_add" action="javascript:;" method="post" name="purchasepayment_add">
					<div class="row">
							<div class="col-md-12">		
							
							 <div class="form-group">
								<label class="col-md-2 control-label">Vendor Name *</label>
								<div class="col-md-4 col-xs-11">
								<?phpif(strtolower($mode)=="add"){?>
								<select  class="select2" name="partyid" id="partyid" onChange="load_billdata(this.value)" >
									<option value="">Choose Vendor</option>
									<?=getvender($dbcon,$rel['partyid']);?>	
								</select>
								<?php}
								if(strtolower($mode)=="edit"){?>
									<input type="text" readonly class="form-control" value="<?=$rel['company_name']?>"/>
									<input type="hidden"  id="partyid" name="partyid" value="<?=$rel['partyid']?>"/>
								<?php }?>
								</div>
								<div class="col-md-2 col-xs-3">
									<input type="button"  name="addcust" id="addcust"  class="btn btn-primary" value="View Statement" onclick="open_statement_popup()"/>
								</div>
					         </div>
							<div class="form-group"  style="font-size:14px">
								<label class="col-md-2 control-label">Amount *</label>
								<div class="col-md-3 col-xs-11"  style="font-size:14px">
								<input id="paid_amount" name="paid_amount"  type="number" min='0'  class="form-control" title="paid_amount" value="<?=$rel['amount']?>" onblur="calculate_total_used();">
									<label class="checkbox chkfull_payment col-md-offset-1 hidden"> 
										<input type="checkbox" name="full_payment_checkbox" id="full_payment_checkbox" ><span class="fullpayment_label"></span>  
									</label>
								</div>
								
					         </div>	
							<div class="form-group">  	
								<label class="col-md-2 control-label" >Payment Date *</label>
							  <div class="col-md-3 col-xs-11">
								<input id="payment_date" name="payment_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$date?>" placeholder="Payment Date">
								</div>
                             </div>	
							<div class="form-group">
								<label class="col-md-2 control-label">Payment Mode *</label>
								<div class="col-md-3 col-xs-11">
								<select class="form-control" name="paymentmodeid" id="paymentmodeid" onChange="" required title="Select Payment Mode">
									<?php echo getpaymentmode($dbcon,$rel['payment_mode']);?>	
								</select>					
								</div>
							</div>	
							
								<div class="form-group">
									<label class="col-md-2 control-label">Choose Account  *</label>
									<div class="col-lg-3 padding-right">
										<select class="form-control"  name="accountid" id="accountid" required title="Select Bank" onchange="getAllBankByGroup(this.value)">
											<?=get_all_group($dbcon,$rel['cust_group'],'','0');?>
										</select>
										
									</div>
									
								</div>
								
								<div class="form-group" style="display:none" id="form_bank_group">
								 <label class="col-md-2 control-label">Select BanK</label>
									<div class="col-md-3 col-xs-11">
										<select class="form-control" name="bank_group" id="bank_group">
											
										</select>
									</div>
								</div>
								
								<div class="form-group">
									<label class="col-md-2 control-label">Reference No </label>
									<div class="col-lg-3 padding-right">
										<input id="referenceno" name="referenceno" type="text" class="form-control" title="Reference No" value="<?=$rel['referenceno']?>" placeholder="" >
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-2 control-label">Tax Deducted </label>
									<div class="col-lg-3 padding-right">
										<label>
										<input id="tax_deducted_flag" name="tax_deducted_flag" type="checkbox" value="1" placeholder="" <?=$rel['tax_deducted_flag']?'checked':''?> >
											Yes, the customer has deducted tax.
										</label>
									</div>
									<div class="col-lg-3 padding-right">
										
									</div>
								</div>					
						</div>
						<div class="col-md-12">
							<table  class="display table table-striped" id="purchase_data">
							  <thead>
								<tr>
									<th width="10%" >Date</th>
									<th width="10%" >Type</th>
									<th  width="10%">Bill#</th>
									<th class="text-right" width="10%">Bill Amount </th>
									<th class="text-right" width="15%">Amount Due</th>
									<th class="text-right tax_deduct <?=$rel['tax_deducted_flag']?'':'hidden'?>" width="10%">Withholding Tax	</th>
									<th class="text-right" width="15%">Payment</th>					  
								</tr>
							  </thead>
							  <tbody>
								<tr>
									<td colspan="6" style="padding: 30px;" class="text-center">
										<h4> There are no bills for this vendor. </h4>
									</td>
								</tr>
								<tr> 
									<td colspan="5" class="text-right">
										Total : 
									</td> 
									<td class="text-right"> 0</td>
								</tr>
							  </tbody>
								</table>
									<table  class="display table table-striped" >
								<tbody>
									<tr>
										<td colspan="4" width="75%" >
										</td> 
										<td class="text-right" style="vertical-align:middle;">Amount Paid : </td>
										<td class="text-right"><input type="text" class="form-control" name="total_paid_amount" id="total_paid_amount" value="" readonly /></td>
									</tr>
									<tr>
										<td colspan="4" >
										</td> 
										<td class="text-right" style="vertical-align:middle;">Amount used for payments : </td>
										<td class="text-right"><input type="text" class="form-control" name="total_used_payment" id="total_used_payment" value="" readonly /></td>
									</tr>
									<tr>
										<td colspan="4" >
										</td> 
										<td class="text-right" style="vertical-align:middle;">Amount in excess : </td>
										<td class="text-right"><input type="text" class="form-control" name="total_excess_payment" id="total_excess_payment" value="" readonly /></td>
									</tr>
								</tbody>
							  </table>
						</div>
							<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
							<button type="button" class="btn btn-success" id="save_cheque" name="save & generate cheque" style="display:none;" onclick="save_cheque_genrate();">Save & Generate Cheque</button>
							<a href="<?=ROOT.'purchasepayment_list'?>" type="button" class="btn btn-danger">Cancel</a><div class="col-md-3"></div>
							
							<input type='hidden' name='receiptid' id='receiptid' value='<?=$receiptid?>' />
							<input type='hidden' name='save_cheque' id='save_cheque_val' value='0' />
							<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
							<input type='hidden' name='eid' id='eid' value='<?=$rel['payment_mstid']?>' />
							<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />	
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
<?php include_once('../include/add_account.php');
 include_once('../include/preview_statement.php');
?>
<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
   <script src="<?=ROOT?>js/app/purchasepayment.js?<?=time()?>"></script>
   	<script src="<?=ROOT?>js/app/bank_account.js?<?=time()?>"></script>
	
    <!--<script src="js/count.js"></script>-->
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
	if(id==2)//for cheque generate 
		$('#save_cheque').show();
	else
		$('#save_cheque').hide();
	if(id!="1" && id!="")
	{	
		$('#cheque_dtl').val('');
		$('#cheque_data').show();
		$('#cheque_data1').show();
	}
	else
		$('#cheque_data').hide();
	    $('#cheque_data1').hide();
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
		$("#invoice_data").show()
	
	}
	else
	{
		$("#invoice_data").hide()
		
	}
		load_billdata($("#vender_id").val())
}
//preview statement
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
if(!$('#partyid').val())
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
	var partyid=$("#partyid").val();
	if(partyid!="")
	{
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/party_ledger/',
		data: { mode : "generate_report", date :  date,cust_id:partyid},
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
if(strtolower($mode)=="edit")
{
	echo '<script>$("#total_paid_amount").val('.$rel["amount"].');load_billdata('.$rel['partyid'].');</script>';
}
?>
  </body>
</html>
