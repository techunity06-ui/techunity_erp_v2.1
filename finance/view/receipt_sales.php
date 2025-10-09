<?php 
	session_start();
	$path = '../../';
$include = '../../include/';
$include1 = '../include/';
include_once($path."config/config.php");
include_once($path."config/session.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");

	$form="Receipt";
	$mode="Print";
	$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
    $query="select receipt_id,receipt_no,total_paid_amount, rec.company_id,receipt_date, cust.l_name,cust.m_address, pay.common_mst_name, rec.cheque_dtl 
from tbl_receipt as rec 
left join tbl_ledger as cust on cust.l_id=rec.cust_id 
left join tbl_common_mst as pay on pay.common_mst_id=rec.payment_mode_id and common_category_id='12'
 where receipt_id=$invoiceid";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$set="select * from tbl_company as comp where company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));	
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once($include.'include_css_file.php');?>
<style>
@media print {
    .page-break {page-break-before: always;display:none;}
	
}

</style>

</head>
<body>
  <section id="container" >
      <?php include_once($include.'include_top_menu.php');?>
      <!--sidebar start-->
      <?php include_once($include.'left_menu.php');?>
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
								<li><a href="<?=ROOT.FINANCE_ROOT.'recipt_list'?>">Receipt List</a></li> 
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
								<div class="form-group resspace">
							  <button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>
							<a href="<?=ROOT.'recipt_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
						<!--<input type="button" name="printpdf" id="printpdf" class="btn btn-default" value="Export to PDF" onclick="make_pdf()" />-->
								</div>	
								
								
						</center>
								</center>
								</div>
								<div class="form-group">
							</div>
							
								</section>
								</div>
								</div>
		  <div class="row">			
			<div class="col-md-8 col-md-offset-2 col-sm-12 ">
				<section class="panel">
				 
						<div class="panel-body">
									
							<div class="col-lg-12" id="receipt_print">								
								<?php ob_start(); ?>		
								<div class="form-group col-md-12 breakout" style="margin-top:10px;color:#000000;font-size:9px;" id="print1">
							
					 	
					<!--<img src="<?=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>-->
				<table  class="maintable headermain" border="1" style="border-radius: 10px;
border-collapse: separate;
border-width: 2px;
border-color: black;
margin-top: 23px;
border: 1px solid; padding:17px 0 0;"  width="100%">
					<tr style="border:none;">
						<td width="100%" style="border:none;padding:0px 0px !important;"> 
						<!--	<img src="<?=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>-->
							
							<h1 style="margin-bottom:0px;" align="center"><?=$set_head['company_name']?></h1>
							<h5 align="center" style="padding:top:8px;"><?=$set_head['logo_content']?></h5>
							<h4 style="font-size:14px; margin-bottom:10px;" align="center"><?=$set_head['address']?></h3>
							
						<h4 style="font-size:16px; margin-top:0px;" align="center"><?if($set_head['website']){?>Email: <?=$set_head['website']?><?}?> 
							<?if($set_head['contact_no']){?>(M) <?=$set_head['contact_no']?><?}?></h4>
											<h4 align="center" style="margin-top:0px;"><?if($set_head['company_website']){?>Website: <?=$set_head['company_website']?><?}?></h4>
											
						</td>
					</tr>
				</table>	
					 	
					<table style="font-size:14px;" width="100%" >
						
						<tr>
								<td  style="text-align:center !important;font-size:18px;border-top: 0px solid;">
									<b>Receipt Voucher</b>
								</td>
						</tr>
						
						</table>
						<table width="100%" border="1" style="border-top:1px solid; border-bottom:none;font-size:14px;" id="" >
						 <tr>
							
							<td colspan="5" style="padding-left: 10px;border-right:0px;border-bottom:none !important">
								<span style="font-size:25px"><h3>Voucher No. : <?=$rel['receipt_no']?></h3></span>
							</td>
							
							<td  colspan="3" style="    text-align: right !important;border-bottom: none !important; padding-top: 5px;vertical-align: top;border-color: #000; border-top: none; border-left: none;"><h4>Date : <?=date('d/m/Y',strtotime($rel['receipt_date']))?></h4></td>
							</tr>
							<tr style="">
								
							<td colspan="4" style="border-left:none;border-right:none;height:25px;    border-top: none !important;    border-bottom: none !important;">
							  </td>
							
							<td colspan="5" style="border-bottom: none;     text-align: right;  padding: 2px;border-top: none;border-right: 1px solid black;border-left: none;"></td>
							</tr>
							<?php $reciept_deta = $dbcon->query("SELECT l.l_name,l.cust_mobile,l.company_name FROM `tbl_receipt_payment_trn` as r left join tbl_ledger as l on l.l_id=r.ledger_id where r.payment_type=2 and r.receipt_id=".$rel['receipt_id']."  and r.isdelete=0 limit 1 ");
								$reciept_deta_1 = brp_mysqli_fetch_assoc($reciept_deta);
							 ?>
				<tr style="height:30px;">
					
					<td colspan="2" style=" border-left:1px solid black;vertical-align: top;border-bottom:none !important; border-right:none !important; border-top:none !important;"><span style="margin-left:5px;">FROM:  </span> </td>
					<td colspan="6" style=" border-bottom:none;border-top: none !important;border-right:1px solid black;    border-left: none !important;
							line-height:1.5;"> <span style=""><?=strtoupper($reciept_deta_1['company_name'])?></span>	  
					</td>
					
						
					</td>
				</tr>
				<!-- <tr style="height:30px;">
					
					<td colspan="2" style=" border-bottom:none !important;border-top: none !important;border-right:none;line-height:1.5;border-left: none;"> <span style="">To :</span>	  
					</td>
					<td  colspan="6"  style=" border-top: none;   border-left: none; border-bottom: 1px solid #000;"> 
						
					</td>
				</tr> -->
			<tr style="height:30px;">
				<td colspan="2" style=" border:none !important"><span style="margin-left:5px;">RUPEES:  </span>  </td>
				<td colspan="6" style="border-bottom:1px solid black;border-right:1px solid black;border-left: none;"><span style=""><?=strtoupper(convert_number_to_words($rel['total_paid_amount']))?>   </span> </td>
			</tr>
			<tr style="height:30px;">
				<td colspan="2" style="border:none !important" width="30%"><span style="margin-left:5px;">PAYMENT MODE  :  </span>  </td>
				<td colspan="3" style="border-bottom:1px solid black;border-right:none;border-left: none;" width="30%"><span style=""><?=$rel['common_mst_name']?> </span> </td>
				<td colspan="3" style="border-right:1px solid black;text-align:right;padding:5px;border-bottom:1px solid black;border-left: none !important;" width="40%"></td>
			</tr>
			<?phpif(strtolower($rel['common_mst_name'])=="cheque"){?>
			<tr style="height:30px;">
				<td colspan="2" style="border:none !important;"><span style="margin-left:5px;">PAYMENT DETAIL  :  </span>  </td>
				<td colspan="6" style="border-bottom:1px solid black;border-right:1px solid black;border-left: none;"><span style=""><?echo $rel['bank_name']." ( NO. :".$rel['cheque_dtl']." ) ";?> </span> </td>
			</tr>
			<?php}?>
			
			
			</table>
			<!--<table  style="font-size:15px;border-left:1px solid black;border-right:1px solid black;"  width="100%" height="30px">
		
			<tr style="height:30px;">
				<td colspan="8" style="border-bottom: none;  border-top: none; border-left:1px solid black;border-right:1px solid black;"></td>
			</tr>
			<tr style="font-size:14px;background-color:#bfb8b89e;height:30px;">
			  <td class="col-md-2" width="15%" style="padding:8px;" ><strong>Ref No</strong></td>
											<td class="col-md-2" width="15%" style="padding:8px;"><strong>Ref Date</strong></td>
											<td class="col-md-2" width="15%" style="padding:8px;"><strong>Type</strong></td>
											<td class="col-md-2 text-right" width="15%" style="padding:8px;"><strong>Bill Amount</strong></td>
											<td class="col-md-2 text-right" width="15%" style="padding:8px;"><strong>Due Amount </strong></td>
											<td class="col-md-2 text-right" width="15%" style="padding:8px;border-right:1px solid black;white-space:nowrap;"><strong>Payment Amount</strong></td>
			
			
			</tr>
			<?
		/*$query ="SELECT rec.receipt_no,rec.receipt_date,rec.total_paid_amount,invoice.invoice_no as ref_id, invoice.invoice_date as ref_date,invoice.g_total as total,'Invoice' as type from tbl_receipt as rec 
						 left join tbl_receipt_trn as rtrn on rec.receipt_id=rtrn.receipt_id 
						 left join tbl_invoice as invoice on rtrn.invoice_id=invoice.invoice_id
						 left join tbl_pono as po on rtrn.purchase_id=po.po_id
						 left join tbl_excess as excess on rtrn.excess_id=excess.excess_id
	                     where rec.status=0 and  rec.receipt_id=$invoiceid";
		
			 $rs_payment_data=$dbcon->query($query);
				$i=1;
				if(mysqli_num_rows($rs_payment_data)>0)
				{
					while($rel1=mysqli_fetch_assoc($rs_payment_data))
					{
						$due_amount=($rel1['total']-$rel1['total_paid_amount']);
						//var_dump($rel1);
						$str.='<tr>	
									<td class="col-md-2" style="padding:8px;">'.$rel1['ref_id'].'</td>
									<td class="col-md-2" style="padding:8px;">'.date('d-m-Y',strtotime($rel1['ref_date'])).'</td>
									<td class="col-md-2" style="padding:8px;">'.$rel1['type'].'</td>
									<td class="col-md-2 text-right" style="padding:8px;">'.floatval($rel1['total']).'</td>
									<td class="col-md-2 text-right" style="padding:8px;">'.floatval($due_amount).'</td>
									<td class="col-md-2 text-right" style="padding:8px;border-right:1px solid black;" >'.floatval($rel1['total_paid_amount']).'</td>
									
								</tr>
							';
                 

				     $i++;
				    }
					
					 echo $str;
				} */
			
			   ?>
			
			</table>-->
			<table width="100%" border="1" style="border-top:0px solid; border-bottom:none;font-size:14px;" id="" >
			<tr style="height:30px;">
				<td colspan="8" style="border-bottom: none;  border-top: none; border-left:1px solid black;border-right:1px solid black;"></td>
			</tr>
				<tr style="height:30px;">
					<td  colspan="2"  style="      border-bottom: none;  border-top: none;border-left:1px solid black;vertical-align: middle;     border-right: none;
"> 
						<span style="width: 100%; padding-left:10px;padding-right:40px; border: 2px solid black; font-size:30px;margin-left:10px;"> <strong>Rs.<?=$rel['total_paid_amount']?>/-</strong></span>
					</td>
					<td colspan="2" style="text-align:left;     border-bottom: none;    border-top: none;   border-left: none;
    border-right: none;">					
					</td>
					
					<td colspan="3" style="      border-bottom: none;   border-top: none;   border-left: none;
    border-right: none;" >
						
					</td>
					
					<td colspan="2" style="    border-bottom: none;border-top:none;border-right:1px solid black;border-left: none;">
						<table style="height: 80px;width: 190px;margin-right:10px;"> <tr><td style="border:1px solid;"><img src="<?=DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'];?>" style="height: 100px; width: 100px;"></td></tr></table>
					</td>
				</tr>
				<tr>
					<td colspan="5" style="border-bottom:1px solid black;    border-top: none !important;padding:10px;height:40px;border-left:1px solid black;border-right: none;" >
							<span style="">Note : Receipt is Subject to Realization</span></td>
					<td colspan="3" style="    border-top: none;border-right:1px solid black;border-bottom:1px solid black;padding-left:20px;border-left: none;">
						Authorised Signatory
					</td>
				</tr>
							
							 

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
	<?php include_once($include.'footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
 
<script>
$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
</script>
<script type="text/javascript"> 

function PrintMe(DivID) {

var c = $('#copies').val();
//for(var j=1;j<=c;j++)
{
	for(var i=1;i<=c;i++)
	{	
		$("#print"+i).html($("#print1").clone());
		if(i==2)
		{			$("#print"+i+" .data_title").html('(DUPLICATE)');
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
			//	  docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/style.css" media="all"/>');
				  docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/bootstrap.min.css" media="all"/>');
				  docprint.document.write('<style type="text/css">body { margin:10px 10px 10px 35px !important;');
				  docprint.document.write('font-family:Tahoma;color:#000;');
				  docprint.document.write('font-family:Tahoma,Verdana; font-size:10px;}.breakout table td,.breakout table th {padding: 2px !important;text-align: inherit !important;} .dataTables_length, .dataTables_filter , .dataTables_paginate { display:none; }');
				  docprint.document.write('.breakout table td,.breakout table th {padding: 2px !important;text-align: inherit !important;}#mainpart table,#mainpart tr,#mainpart td,#mainpart th {border:1px #eee solid;padding:2px 5px 2px 5px;text-align:center;}');
				  docprint.document.write('.breakout table td,.breakout table th {padding: 2px !important;text-align: inherit !important;}a{color:#000;text-decoration:none;} h1 {font-size:25px; line-height:5px;} b { font-weight:normal; } div.page { page-break-after: always; page-break-inside: avoid; } </style>');
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