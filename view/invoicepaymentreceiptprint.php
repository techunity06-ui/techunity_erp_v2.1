<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/coman_function.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Receipt";
	$mode="Print";
	$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);

$query ="select payment_date,cust.company_name as paidto,mst.amount,mst.paymentno,mst.credits,referenceno,pay.payment_mode,g.g_name from `payment_mst` as mst left join tbl_payment_mode as pay on mst.payment_mode =pay.paymentmodeid left join tbl_customer as cust on cust.cust_id=mst.partyid left join tbl_group as g on mst.accountid=g.g_id where payment_mstid=$invoiceid";
	$rel=mysqli_fetch_assoc($dbcon->query($query));

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where  company_id=".$rel['company_id'];	
//echo $set;
$set_head=mysqli_fetch_assoc($dbcon->query($set));	

?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>

<style>
@media print {
    .page-break {page-break-before: always;display:none;}
	
}

</style>

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
						  <h3><?=$mode.' '.$form?></h3>
						</header>	
							<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <?php
							  if($_REQUEST['report']=='report')
								{?>
							  <li><a href="<?=ROOT.'partypaymentreport'?>">Party Wise Payment Report</a></li>
							<?php}
							else if($_REQUEST['report']=='0'){?>
							  <li><a href="<?=ROOT.'purchasepayment_list/'.$rel['purchasereceipt_id']?>">Receipt Payment</a></li>
							
							<?}else
							{?>
							  <li><a href="<?=ROOT.'purchasepayment_list'?>">Purchase Payment Receipt list</a></li>
							 <?php}?> 
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--state overview start-->
		  <div class="row">			
			<div class="col-md-12 ">
				<section class="panel">
				  <header class="panel-heading">
					  New <?=$form?>
					</header>	
						<div class="panel-body">
								<center>
								<div class="form-group">
							  <button type="submit" class="btn btn-danger" onClick="PrintMe('receipt_print');">Print</button>
							<?phpif($_REQUEST['report']=="report")
							{?>
							<a href="<?=ROOT.'partypaymentreport'?>" type="button" class="btn btn-success">Cancel</a>
							<?php}
							else if($_REQUEST['report']=='0')
							{
							?>
							<a  type="button" class="btn btn-success"  href="<?=ROOT.'purchasepayment_list/'.$rel['purchasereceipt_id']?>">Cancel</a>
							<?
							}
							else
							{?>
							<a href="<?=ROOT.'invoicepayment_list'?>" type="button" class="btn btn-success">Cancel</a>
							<?php}?> 
						<!--<input type="button" name="printpdf" id="printpdf" class="btn btn-default" value="Export to PDF" onclick="make_pdf()" />-->
								</div>	
								
								
						</center>
							
								</div>
								<div class="form-group">
							</div>
							
								</section>
								</div>
								</div>
		  <div class="row">			
			<div class="col-md-12 ">
				<section class="panel">
				 
						<div class="panel-body">
									
							<div class="col-lg-12 table-responsive" id="receipt_print">								
								<?php ob_start(); ?>		
								<div class="form-group col-md-12" style="margin-top:10px;color:#000000;font-size:9px;border:1px solid;" id="print1">
							
					 	
					<table style="font-size:14px;" width="100%" >
						<tr id="head">
							<td  colspan="10"  style="border:none;padding-left:0px;"> 
						<!--<img src="<?=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>-->
							
							</td>
						</tr>
						<tr height="30px"></tr>
						<tr>
								<td colspan="10" style="text-align:center;font-size:18px;"  height="20px">
									<strong>PAYMENTS MADE</strong><br />
								
									
								</td>
						</tr>
						</table>
						<br /><br />
									
					<table style="font-size:15px;align:left;" width="100%" >
						<tr style="height:30px;">
							<th width="20%" style ="padding:8px;">Payment No:</th>
							<td style="padding:8px;"><?=$rel['paymentno']?></td>
						<tr/>
						<tr style="height:30px;">
							<th width="20%" style ="padding:8px;">Payment Date :</th>
							<td style="padding:8px;"><?=$rel['payment_date']?></td>
						<tr/>
						<tr style="height:30px;">
							<th style ="padding:8px;">Reference No :</th>
							<td style="padding:8px;"><?=$rel['referenceno']?></td>
						<tr/>
						<tr style="height:30px;">
						<th style ="padding:8px;">Paid to :</th>
							<td style="padding:8px;"><?=$rel['paidto']?></td>
						</tr>
						<tr style="height:30px;">
						<th style ="padding:8px;">Payment Mode :</th>
							<td style="padding:8px;"><?=$rel['payment_mode']?></td>
						</tr>
						<tr style="height:30px;padding:8px;">
						<th style ="">Paid Through :</th>
							<td style="padding:8px;"><?=$rel['g_name']?></td>
						</tr>
						<tr style="height:30px;padding:8px;">
							<th style ="">Amount Paid :</th>
							<td style="padding:8px;"><?=$rel['amount']?></td>
						</tr>
						</table>
						
					
									<table style="font-size:15px;" width="100%">
									<h4 style="color:#777"><strong>Payment For</strong></h4>
										<tr style="font-size:14px;background-color:#bfb8b89e;height: 30px;">>
											<td class="col-md-2" width="15%" style="padding:8px;" ><strong>Bill#</strong></td>
											<td class="col-md-2" width="15%" style="padding:8px;"><strong>Bill Date</strong></td>
											<td class="col-md-2" width="15%" style="padding:8px;"><strong>Type</strong></td>
											<td class="col-md-2 text-right" width="15%" style="padding:8px;"><strong>Bill Amount</strong></td>
											<td class="col-md-2 text-right" width="15%" style="padding:8px;"><strong>Amount Due </strong></td>
											<td class="col-md-2 text-right" width="15%" style="padding:8px;"><strong>Payment Amount</strong></td>
										
										</tr>
			
<?
	$query ="select tbl.*,trn.payment_trnid ,trn.total_amount,trn.total_amount as amount,trn.tax_amount from ((SELECT invoice_id,'invoice' as type,invoice_no,invoice_date,g_total,paid_amount FROM `tbl_invoice` as invoice where invoice.invoice_status=0 ) 
				union all (SELECT incomeid,'income' as type,invoice_no,income_date,g_total,paid_amount FROM `income_mst` as income where income.mst_status=0 )) as tbl
					left join payment_trn as trn on trn.bill_id=tbl.invoice_id and trn.bill_type=cast(tbl.type as CHAR)
					where trn.payment_mstid=$invoiceid";
		//echo $query;						
								$rs_payment_data=$dbcon->query($query);
								//var_dump($rs_payment_data);
				$i=1;
				if(mysqli_num_rows($rs_payment_data)>0)
				{					
					while($rel1=mysqli_fetch_assoc($rs_payment_data))
					{
					//var_dump($rel1);
						$due_amount=($rel1['g_total']-$rel1['paid_amount'])+($rel1['amount']+$rel1['tax_amount']);
						//var_dump($due_amount);
						$str.='<tr>	
									<td class="col-md-2" style="padding:8px;">'.$rel1['invoice_no'].'</td>
									<td class="col-md-2" style="padding:8px;">'.date('d-m-Y',strtotime($rel1['invoice_date'])).'</td>
									<td class="col-md-2" style="padding:8px;">'.$rel1['type'].'</td>
									<td class="col-md-2 text-right" style="padding:8px;">'.floatval($rel1['g_total']).'</td>
									<td class="col-md-2 text-right" style="padding:8px;">'.floatval($due_amount).'</td>
									<td class="col-md-2 text-right" style="padding:8px;" >'.floatval($rel1['total_amount']).'</td>
									
								</tr>
							';
						$total+=$rel1['total_amount'];
					
						$i++;
							}
					
					echo $str;
										}
								//echo $total;
								?>
										<tr>
						<td style="text-align:right;border-bottom: 1px solid #bfb8b89e;font-size: 15px;text-align:center" colspan="5" width="100%"> </td>
							<td style="text-align:right;border-bottom: 1px solid #bfb8b89e;font-size: 15px;text-align:center" colspan="5" width="100%"> </td>
										</tr>
						<tr height="40%"><td style="text-align:right;padding:8px;" colspan="5"><strong>credits</strong>:</td>
							<td style="text-align:right;" colspan="5"><?= $rel['credits']?></td></tr>				
									
									</table>		
						 <!--<tr>
							
							<td colspan="5" style="border-top:1px solid black;border-left:1px solid black;padding-left: 10px;">
								<span style="font-size:25px"><h3>Voucher No. : <?=$rel['purchasereceipt_no']?></h3></span>
							</td>
							
							<td  colspan="2" style="text-align:center; padding-top:5px;vertical-align:top;border-top:1px solid black;border-right:1px solid black;"><h4>Date : <?=date('d/m/Y',strtotime($rel['payment_date']))?></h4></td>
							</tr>
							<tr style="">
								
							<td colspan="4" style="border-left:1px solid black;height:25px;">
							  </td>
							
							<td colspan="3" style=" text-align:right;padding:2px;border-top:none; border-right:1px solid black;"></td>
							</tr>
				<tr style="height:30px;">
					
					<td colspan="2" rowspan="2" style=" border-left:1px solid black;vertical-align: top;"><span style="margin-left:5px;">Party Name  </span> </td>
					<td colspan="5" style=" border-bottom:1px solid black;border-right:1px solid black;
							line-height:1.5;"> <span style=""><?=strtoupper($rel['company_name'])?></span>	  
					</td>
					
						
					</td>
				</tr>
				<tr style="height:30px;">
					
					<td colspan="5" style=" border-bottom:1px solid black;border-right:1px solid black;line-height:1.5;"> <span style=""><?=strtoupper($rel['cust_address'])?></span>	  
					</td>
					<td  colspan="2"  style=""> 
						
					</td>
				</tr>
			<tr style="height:30px;">
				<td colspan="2" style=" border-left:1px solid black;"><span style="margin-left:5px;">RUPEES:  </span>  </td>
				<Td colspan="5" style="border-bottom:1px solid black;border-right:1px solid black;"><span style=""><?=strtoupper(convert_number_to_words($rel['paid_amount']))?>   </span> </Td>
			</tr>
			<tr style="height:30px;">
				<td colspan="2" style=" border-left:1px solid black;" width="30%"><span style="margin-left:5px;">PAYMENT MODE  :  </span>  </td>
				<Td colspan="3" style="border-bottom:1px solid black;" width="30%"><span style=""><?=$rel['payment_mode']?> </span> </Td>
				<td colspan="2" style="border-right:1px solid black;text-align:right;padding:5px;border-bottom:1px solid black;" width="40%"></td>
			</tr>
			<?phpif(strtolower($rel['payment_mode'])=="cheque"){?>
			<tr style="height:30px;">
				<td colspan="2" style=" border-left:1px solid black;"><span style="margin-left:5px;">Bank Name  :  </span>  </td>
				<Td colspan="5" style="border-bottom:1px solid black;border-right:1px solid black;"><span style=""><?echo $rel['bank_name']//." (check NO. :".$rel['cheque_dtl']." ) ";?> </span> </Td>
			</tr>
			<tr style="height:30px;">
				<td colspan="2" style=" border-left:1px solid black;"><span style="margin-left:5px;">Cheque No  :  </span>  </td>
				<Td colspan="5" style="border-bottom:1px solid black;border-right:1px solid black;"><span style=""><?echo $rel['cheque_dtl'] //." (check NO. :".$rel['cheque_dtl']." ) ";?> </span> </Td>
			</tr>
			<tr style="height:30px;">
				<td colspan="2" style=" border-left:1px solid black;"><span style="margin-left:5px;">Reference Date  :  </span>  </td>
				<Td colspan="5" style="border-bottom:1px solid black;border-right:1px solid black;"><span style=""><?echo $rel['ref_date']//." (check NO. :".$rel['cheque_dtl']." ) ";?> </span> </Td>
			</tr>
			<?php}?>
			<?phpif(strtolower($rel['payment_mode'])=="neft"){?>
			<tr style="height:30px;">
				<td colspan="2" style=" border-left:1px solid black;"><span style="margin-left:5px;">Bank Name  :  </span>  </td>
				<Td colspan="5" style="border-bottom:1px solid black;border-right:1px solid black;"><span style=""><?echo $rel['bank_name']//." (check NO. :".$rel['cheque_dtl']." ) ";?> </span> </Td>
			</tr>
			<tr style="height:30px;">
				<td colspan="2" style=" border-left:1px solid black;"><span style="margin-left:5px;">NEFT No  :  </span>  </td>
				<Td colspan="5" style="border-bottom:1px solid black;border-right:1px solid black;"><span style=""><?echo $rel['cheque_dtl'] //." (check NO. :".$rel['cheque_dtl']." ) ";?> </span> </Td>
			</tr>
			<tr style="height:30px;">
				<td colspan="2" style=" border-left:1px solid black;"><span style="margin-left:5px;">Reference Date  :  </span>  </td>
				<Td colspan="5" style="border-bottom:1px solid black;border-right:1px solid black;"><span style=""><?echo $rel['ref_date']//." (check NO. :".$rel['cheque_dtl']." ) ";?> </span> </Td>
			</tr>
			<?php}?>
			<?phpif(strtolower($rel['payment_mode'])=="neft"){?>
			<tr style="height:30px;">
				<td colspan="2" style=" border-left:1px solid black;"><span style="margin-left:5px;">PAYMENT DETAIL  :  </span>  </td>
				<Td colspan="5" style="border-bottom:1px solid black;border-right:1px solid black;"><span style=""><?echo $rel['bank_name']." (NEFT NO. :".$rel['cheque_dtl']." ) ";?> </span> </Td>
			</tr>
			<?php}?>
			<tr style="height:30px;">
				<td colspan="7" style=" border-left:1px solid black;border-right:1px solid black;"></td>
			</tr>	
				<tr style="height:30px;">
					<td  colspan="2"  style="border-left:1px solid black;vertical-align: top;"> 
						<span style="width: 100%; padding-left:10px;padding-right:40px; border: 2px solid black; font-size:30px;margin-left:10px;"> <strong>Rs.<?=$rel['paid_amount']?>/-</strong></span>
					</td>
					<Td colspan="2" style="text-align:left;">					
					</Td>
					
					<Td colspan="2" >
						
					</Td>
					
					<Td colspan="" style="border-top:none;border-right:1px solid black;">
						<table style="height: 80px;width: 190px;margin-right:10px;"> <tr><td style="border:1px solid;"></td></tr></table>
					</Td>
				</tr>
				<tr>
					<Td colspan="5" style="border-bottom:1px solid black;padding:10px;height:40px;border-left:1px solid black;" >
							<span style="">Note : Receipt is Subject to Realization</span></Td>
					<Td colspan="2" style="border-right:1px solid black;border-bottom:1px solid black;padding-left:20px;">
						Authorised Signatory
					</Td>
				</tr>-->
							
							 

</table> 
	<input type="hidden" name="name" id="name" value="<?=$rel['company_name']?>"/>
	<input type="hidden" name="invoiceno" id="invoiceno" value="<?=$rel['invoice_no']?>"/>
							</div>
								<div id="print2"></div>
								<div id="print3"></div>
							
</div>
<?php  
		$contents = ob_get_contents();
		$_SESSION['contents']=$contents;
		echo "<script> function make_pdf()
		{ window.open('".ROOT."export/print','_blank');
		}</script>";  
		?>
		</div>	
					</section>
				</div>
			  </div>
			  <!--state overview end-->
          </section>
      </section>
      <!--main content end-->
      <!--footer start-->
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
   <script src="<?=ROOT?>js/app/invoice.js"></script>
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
	if(id=="2")
	{	
		$('#cheque_dtl').val('');
		$('#cheque_data').show();
	}
	else
		$('#cheque_data').hide();
}
</script>
<script type="text/javascript"> 
function print_receipt()
{
	var originalContents = document.body.innerHTML;
	//var duplicate = $("#invoiceprint").clone().prepend("<hr style='border-color:#000; border-style:dashed; margin:12px 0' />").appendTo("#invoiceprint");
	 var printContents = document.getElementById('receipt_print').innerHTML;     
     document.body.innerHTML = printContents;
     window.print();
     document.body.innerHTML = originalContents;
}

function PrintMe(DivID) {

var c = $('#copies').val();
//for(var j=1;j<=c;j++)
{
	if ($('input[name=logo]:Checked').val() == "1") {
	   
		$('#table_head').show();
		$('#table_foot').show();
		docprint.document.write(' @media print{ @page { size:A4; margin: 0.2in <?=$set_head['right_margin']?>in 0.2in <?=$set_head['left_margin']?>in; } }   ');
		
	}
	else
	{
	for(var i=1;i<=c;i++)
	{	
		$("#print"+i).html($("#print1").clone());
		if(i==2)
		{			$("#print"+i+" .data_title").html('(DUPLICATE)');
		}
	}
	}	
//var duplicate = $("#receipt_data").clone().appendTo("#receipt_duplicate");
var disp_setting="toolbar=yes,location=no,";
				disp_setting+="directories=yes,menubar=yes,";
				disp_setting+="scrollbars=yes,width=800, height=600, left=100, top=25";
				var content_vlue = document.getElementById(DivID).innerHTML;
				var docprint=window.open("","",disp_setting);
				  docprint.document.open();
				  docprint.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"');
				  docprint.document.write('"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
				  docprint.document.write('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">');
				  docprint.document.write('<head><title><?=$receipt_no?></title>');
				  docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/style.css" media="all"/>');
				  docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/bootstrap.min.css" media="all"/>');
				  docprint.document.write('<style type="text/css">body { margin:10px 10px 10px 35px !important;');
				  docprint.document.write('font-family:Tahoma;color:#000;');
				  docprint.document.write('font-family:Tahoma,Verdana; font-size:10px;} .dataTables_length, .dataTables_filter , .dataTables_paginate { display:none; }');
				  docprint.document.write('#mainpart table,#mainpart tr,#mainpart td,#mainpart th {border:1px #eee solid;padding:2px 5px 2px 5px;text-align:center;}');
				  docprint.document.write('a{color:#000;text-decoration:none;} h1 {font-size:25px; line-height:5px;} b { font-weight:normal; } div.page { page-break-after: always; page-break-inside: avoid; } </style>');
				  docprint.document.write('</head><body onLoad="window.print();"><center>');
				  docprint.document.write(content_vlue);
				  docprint.document.write('</center></body></html>');
				  docprint.document.close();
				  docprint.focus();
				 // docprint.close();
				  $("#print2").html('');
				 
}
location.reload();
 $("#print1").css('margin-top','0px');
  }	
</script>


  </body>
</html>
