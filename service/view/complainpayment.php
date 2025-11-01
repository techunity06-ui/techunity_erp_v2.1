<?php 
	session_start();
	include('../include/urlfile.php');
	$incPath = $path.'include/';
	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Payment Received";
	if(strpos($_SERVER['REQUEST_URI'], "receipt-update")==false)
	{
		$mode="Add";
		$date=date('d-m-Y');
		$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
		$arr=get_serise_common($dbcon,'1');
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
	
	$user_id=$_SESSION['user_id'];
	$empl_id=getEmployeeIdUser($dbcon,$user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once($incPath.'include_css_file.php');?>
</head>
<body>
<section id="container">
	<?php include_once($incPath.'include_top_menu.php');?>
	<!--sidebar start-->
	<?php include_once($incPath.'left_menu.php');?>
	<!--sidebar end-->
     <!--main content start-->
	<section id="main-content">
		<section class="wrapper">
			<div class="row">
				<div class="col-lg-12">
					<!--breadcrumbs start -->
					<section class="panel">
						<header class="panel-heading">
							<h3>
								<?php if($mode=="Add"){echo $form;}else if($mode=="payment"){echo $form.' #'.$rel['po_no'];}?>
							</h3>
						</header>	
						<div class="">
							<ul class="breadcrumb">
								<li>
									<a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a>
								</li>
								<li>
									<a href="<?=ROOT.SERVICE_ROOT.'complainpayment_list'?>"> Payment List</a>
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
						<form class="form-horizontal" role="form" id="complainpayment_add" action="javascript:;" method="post" name="invoicepayment_add">
							<div class="row">
								<div class="col-md-12">	
										
									<div class="form-group">
										<label class="col-md-2 control-label">Customer Name *</label>
										<div class="col-md-4 col-xs-11">
											<?php if(strtolower($mode)=="add"){?>
											<select  class="select2" name="partyid" id="partyid" onChange="get_all_complain(this.value)" >
												<?=getcust($dbcon,$rel['partyid']);?>	
											</select>
											<?php }
											if(strtolower($mode)=="edit"){?>
												<input type="text" readonly class="form-control" value="<?=$rel['company_name']?>"/>
												<input type="hidden"  id="partyid" name="partyid" value="<?=$rel['partyid']?>"/>
											<?php } ?>
										</div>
							        </div>
									 
									<div class="form-group">
										<label class="col-md-2 control-label">Choose Complaint *</label>
										<div class="col-md-4 col-xs-11">
											<select  class="select2" name="comp_id" id="comp_id" onChange="getPendingPayment(this.value)" >
												<option value="">Choose Complaint</option>
												
											</select>
										</div>
							        </div>
									 
									<div class="form-group"  style="font-size:14px">
										<label class="col-md-2 control-label">Amount Due *</label>
										<div class="col-md-3 col-xs-11"  style="font-size:14px">
											<input id="due_amount" name="due_amount"  type="number" min='0'  class="form-control" title="due_amount" value="<?=$rel['amount']?>" readonly>
										</div>								
							        </div>	
									 
									<div class="form-group"  style="font-size:14px">
										<label class="col-md-2 control-label">Amount To Take *</label>
										<div class="col-md-3 col-xs-11"  style="font-size:14px">
											<input id="paid_amount" name="paid_amount"  type="number" min='0'  class="form-control" title="paid_amount" value="<?=$rel['amount']?>" onkeyup="get_final_comp_payment()">
											<label class="checkbox chkfull_payment col-md-offset-1 hidden"> 
												<input type="checkbox" name="full_payment_checkbox" id="full_payment_checkbox" ><span class="fullpayment_label"></span>
											</label>
											<span id="err_amt" style="color:red;font-weight:bold;display:none">Enter Amount Less Then Due Amount</span>
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
										
									<input type="hidden" class="form-control" name="" id="" value="<?=get_all_acc_type_emp($dbcon,$empl_id);?>" />
												
								</div>
								<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
								<!--<button type="button" class="btn btn-success" id="save_cheque" name="save & generate cheque" style="display:none;" onclick="save_cheque_genrate();">Save & Generate Cheque</button>-->
								<a href="<?=ROOT.'invoicepayment_list'?>" type="button" class="btn btn-danger">Cancel</a><div class="col-md-3"></div>
								
								<input type='hidden' name='receiptid' id='receiptid' value='<?=$receiptid?>' />
								<input type='hidden' name='save_cheque' id='save_cheque_val' value='0' />
								<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
								<input type='hidden' name='eid' id='eid' value='<?=$rel['payment_mstid']?>' />
								<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />	
								<input type='hidden' name='emp_id' id='emp_id' value='<?php echo $empl_id; ?>' />
							</div>
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
<?php //include_once('../include/add_account.php');
 include_once($include1.'preview_statement.php');
?>
<?php include_once($incPath.'footer.php');?>
      <!--footer end-->
</section>
<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($incPath.'include_js_file.php');?>   
<script src="<?=ROOT?><?=SERVICE_ROOT?>js/app/complainpayment.js?<?=time()?>"></script>
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
	$('#invoicepayment_add').submit();
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
} ?>
</body>
</html>
