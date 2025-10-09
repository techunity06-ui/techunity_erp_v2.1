<?php 
session_start();
$path = '../../';
$incPath = $path.'include/';
include($path.'config/config.php');
include($path.'config/session.php');
include_once(COMMON_FUNCTION_PATH.'common_functions.php');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	SALES_ORDER_SLUG_PRINT,
	SALES_ORDER_SLUG_READ
]);

if(!in_array(SALES_ORDER_SLUG_PRINT,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}

$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$_SESSION['contents']='';
$form="Sales Order";
$mode="Print";
$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
$query="select invoice.*,country.country_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust_pincode,cust_mobile,gst_no, cust.emp_signature_img from tbl_sales_order as invoice 
left join tbl_ledger as cust on cust.l_id=invoice.cust_id
left join country_mst as country on country.countryid=cust.countryid
left join state_mst as state on state.stateid=cust.stateid
left join city_mst as city on city.cityid=cust.cityid
where sales_order_id=$invoiceid";
$rel=mysqli_fetch_assoc($dbcon->query($query));	
$company_name=$rel['company_name'];
$cust_address=$rel['cust_address'];
$city_name=$rel['city_name'];
$state_name=$rel['state_name'];
$country_name=$rel['country_name'];
$cust_pincode=$rel['cust_pincode'];
$gst_no=$rel['gst_no'];
$delivery_type = $rel['delivery_type'];

$set="select * from tbl_company where company_id=".$rel['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));	
$order_date='';$dispatch_date='';

$remark = $rel['remark'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Sales Order - <?=$rel['sales_order_no']?></title>
	<?php include_once($incPath.'include_css_file.php');?>
	<style>
		body {
			color: #000000;
		}
		.con ul 
		{
			padding-left:0px;
		}
		.con ul li 
		{
			margin-left:22px;
			list-style: disc !important;
		}
/*td, th {
    padding: 0px 2px !important;
    }*/
</style>
</head>
<body>
	<section id="container" >
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
								<h3><?=$mode.' '.$form?></h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<?php if(in_array(SALES_ORDER_SLUG_CREATE,$bulkAccessArray)){ ?>
										<li><a href="<?=ROOT.CRM_ROOT.'sales_order_list'?>"><?=$form?> List</a></li>
									<?php } ?>

								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>	
				</div>
				<!--state overview start-->
				<div class="row">			
					<div class="col-sm-8 col-md-offset-2">
						<section class="panel">
							<header class="panel-heading">
								<?=$form?> <?=$mode?>
							</header>	
							<div class="panel-body">
								<center>
									<div class="col-sm-5"  style="padding-left:0;">
										<label class="col-md-2 control-label" style="
										padding:10px 0;">Print</label>
										<div class="col-md-10 col-xs-12">
											<form class="form-horizontal" role="form" id="print_form" action="javascript:;" method="post" name="print_form">
												<select class="form-control" name="print_status" id="print_status" <?if($_REQUEST['printstatus']!=''){ echo "readonly";}?>>
													<option value="">Select Print</option>
													<option value="1">ORIGINAL</option>
													<option value="2">DUPLICATE</option>
													<option value="3">TRIPLICATE</option>
													<option value="4">EXTRA</option>
												</select>
											</form>
										</div>
									</div>
									<div class="col-sm-3 col-xs-6">
										<label class=" control-label col-xs-7" style="
										padding-top: 10px; padding:10px 0 0;">With Logo</label>
										<div class="  col-xs-5">
											<input type="checkbox" class="form-control"  name="logo" id="logo" value="1">
										</div>
									</div>
									<div class="col-sm-4 col-xs-6 resspace"  style="text-align:right">
										<button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>
										<a href="<?=ROOT.CRM_ROOT.'sales_order_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
									</div>

								</center>	
								<div class="col-md-12"></div>
								<label class="col-md-3 control-label"></label>
								<div class="col-lg-4"></div>
								<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
								<?php ob_start(); ?>
								<div class="col-lg-12" id="receipt_print">	<div class="col-md-12 breakout" style=" margin-top:10px;" id="print1">
									<!--<img src="<?=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>	-->	
									<table  class="maintable headermain " border="2" style="border-collapse: separate;border:none;" id="table_head" width="100%">
										<tr style="border:none;">
											<td width="100%" style="border:none;padding:0px 0px !important;"> 
												<img src="<?=ROOT.LOGO.$set_head['logo']?>" style="height: 150px;"/>
											</td>
										</tr>
									</table>
									<table width="100%" border="1" style="border:none; " id="">
										<tr>
											<td width="90%" style="text-align:center !important"> 
												<strong style="font-size:16px">
													<?=$form?> 
												</strong>
											</td>
											<td width="10%" style="text-align:center"> 
												<strong style="font-size:12px">
													<b class="data_title">ORIGINAL</b>
												</strong>
											</td>
										</tr>
									</table>
									<!-- Multi Page Challan Start -->				
									<table width="100%" class="maintable" style="font-size: 11px;   border-right: 1px solid !important; border-left: 1px solid !important;" id="invoice_type" >
										<thead>
											<tr>
												<th colspan="5" style="padding:0px !important;">
													<table  border="0" style="font-size:12px;border-collapse:collapse;" cellpadding="0"  cellspacing="0" width="100%" id="">
														<!--<thead>-->
															<tr>
																<td width="10%" style="vertical-align:top;border:1px solid;border-right:none;border-left:none"><strong>Order No </strong>
																</td>
																<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;border-right:1px solid;">: <strong><?=$rel['sales_order_no']?></strong>
																</td>
																<td rowspan="3" width="55%" style="vertical-align:top;border:1px solid;padding-bottom:0px !important;border-right:none">
																	M/s,<br>
																	<strong><?=$company_name?></strong>
																	<span style="font-weight:normal;"> <br>
																		<?=$cust_address?>,<br/>
																		<?=$city_name?>,
																		<?=$state_name?>,
																		<?=$country_name?>
																		<?phpif(!empty($cust_pincode)){ ?> -  <?=$cust_pincode?><?php} ?></span><br> <strong> GSTIN : <?=$gst_no?></strong>

																	</td>
																</tr>
																<tr>
																	<td style="vertical-align:top;border:1px solid;border-right:none;border-left:none;white-space:nowrap;">
																		<strong>Order Date</strong>
																	</td>
																	<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">
																		: <?=date('d/m/Y',strtotime($rel['sales_order_date']))?>							
																	</td>							
																</tr>
																<tr>
																	<td style="vertical-align:top;border:1px solid;border-right:none;border-left:none;white-space:nowrap;">
																		<strong>Delivery Date</strong>
																	</td>
																	<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">
																		: <?=date('d/m/Y',strtotime($rel['delivery_date']))?>							
																	</td>							
																</tr>
																<!--</thead>-->	
															</table>
														</th>
													</tr>
													<tr height="30px">					
														<th  width="5%" style="text-align:center !important;border:1px solid;border-top:none;">
															<strong>SR. NO.</strong>
														</th>
														<th width="55%"  style="text-align:center !important;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Item Description</strong></th>
														<th width="17%" style="text-align:center !important;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>Quantity</strong></th>
														<!--<th width="17%" style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>Sqr/Ft</strong></th>-->
														<!--<th width="15%" style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>HSN/SAC Code</strong></th>-->

													</tr>
												</thead>
												<tbody style="border: 1px solid;">
													<?php
													$qry="select * FROM `tbl_sales_ordertrn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id where sales_ordertrn_status=0 and sales_order_id=".$rel['sales_order_id'];
													$result=$dbcon->query($qry);		
													$i=1;$total=0;$discount=0;$totalqty=0;
													$cnt=mysqli_num_rows($result);
													while($row=mysqli_fetch_assoc($result))
													{
														?>
														<tr style="height:40px">
															<td style="text-align:center !important;vertical-align:top;border-right:1px solid;border-left:1px solid;">
																<?=$i?>
															</td>
															<td style="padding-left:5px;border-bottom-color:#FFFFFF; border-right:1px solid;vertical-align:top;" >
																<?if($row['product_alias_name']){?>
																	<strong><?=stripcslashes($row['product_alias_name'])?></strong>
																	<br/><?=nl2br(stripcslashes($row['description']));?>
																<?php}else{ ?>
																	<strong><?=stripcslashes($row['product_name'])?></strong>
																	<br/><?=nl2br(stripcslashes($row['description']));?>
																	<?}?>
																	
				<?if($delivery_type == 'product_wise'){
					$retu_date = "select sdate.*,unit.unit_name from tbl_salesorder_delivery_date as sdate left join unit_mst as unit on unit.unitid=sdate.unit_id where invoice_status=0 and sdate.po_delivery_date_status=0 and sales_ordertrn_id=".$row['sales_ordertrn_id'];
									$resadate=$dbcon->query($retu_date);
																	?>
																	<table width="40%" style="font-size:13px">
																		<tr>
																			<td><strong>Delivery Date</strong></td>
																			<td><strong>Qty</strong></td>
																		</tr>
																	
																	
											<?while($rowdate=brp_mysqli_fetch_array($resadate)){?>		<tr>
											<td><?=date('d-m-Y',strtotime($rowdate['delivery_date']))?></td>
												<td><?=$rowdate['product_qty'].' '.$rowdate['unit_name']?></td>
																		</tr>		
																	<?}?>
																	</table>
																		<?}
																	?>
																</td>
																<td style="text-align:center !important;padding-right:10px;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;" >
																	<?=$row['product_qty'].' '.$row['unit_name']?>
																</td>
				<!--<td style="text-align:center;padding-right:10px;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;" >
					<?=$row['sqr_ft']?>
				</td>-->
				<!--<td style="text-align:center;padding-right:10px;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;" >
					<?=stripcslashes($row['product_hsn_code'])?>
				</td>-->			
				<!--<td style="text-align:right;padding-right:10px;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;" >
					<?=$row['product_rate']?>
				</td>
				
				<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid;">
					<?=$row['product_amount']?>
				</td>-->
			</tr>
			<?php	
			$i++; 
			$total=$total+=$row['product_amount'];
			$totalqty=$totalqty+$row['product_qty'];
			$totalsqr=$totalsqr+$row['sqr_ft'];
		}
		$pr=15-$cnt;
		for($j=0; $j<$pr; $j++)
		{
			?>
			<tr style="height:40px">
				<td style="border-right:1px solid;border-left:1px solid;"></td>
				<td style="border-right:1px solid;"></td>
				<td style="border-right:1px solid;"></td>
				<!--	<td style="border-right:1px solid;"></td>-->
				<!--<td style="border-right:1px solid;"></td>-->
					<!--<td style="border-right:1px solid;"></td>
						<td style="border-right:1px solid;"></td>-->
					</tr>
				<?php} ?>
				<tr height="24px">
					<td colspan="3" style="border-top:1px solid;border-bottom:1px solid;border-right:1px solid;border-left:1px solid;font-size:14px;text-align:left !important;"><strong>Remark : </strong> <?=$remark?></td>
				</tr>	
				<tr height="24px">
					<td colspan="2" style="border-top:1px solid;border-bottom:1px solid;border-right:1px solid;border-left:1px solid;font-size:14px;text-align:right !important;">TOTAL</td>
					<td style="text-align:center;border-bottom:1px solid; border-top:1px solid;font-size:14px;border-right:1px solid; "><?=number_format($totalqty,2,".","")?></td>
					<!--<td style="text-align:center;border-bottom:1px solid; border-top:1px solid;font-size:14px;border-right:1px solid; "><?=number_format($totalsqr,2,".","")?></td>-->
			<!--<td style="border-right:1px solid;border-top:1px solid; ">
			</td>-->
		</tr>	
		<tr>
		</table>
	</td>
</tr>
</tbody>	

<td colspan="4" style="padding: 0px !important;border:1px solid">
				<!--<table class="footer-table" width="100%" border="0" style="margin-top: 5px;" id="table_foot">
					<tr>
						<td style="border:none;padding:0px 0px !important;width:100%;"> 
							<img src="<?=ROOT.LOGO.$set_head['f_logo']?>"  style="width:100%"/>
						</td>
					</tr>
				</table>-->
			</table>
				<!--<table class="footer-table" width="100%">
					<tr style="border-bottom:none;">
						<td colspan="2" style="border-top:1px solid;">
						<?if(!empty($set_head['vatno'])){ ?>
							<strong>COMPANY GST No. : <?=$set_head['vatno']?> 
						<?php} ?>
						</td>
						<td style="border-top:1px solid;">
							<span style="font-size:12px;float:right;">For, <strong><?=$set_head['company_name']?></strong></span>
						</td>
					</tr>
					
					<tr height="50px" style="border-bottom:none;">
					<td colspan="2"  style="">
							<?phpif(!empty($set_head['challan_condition'])){ ?>
								<strong>Terms and Conditions:</strong><br> <?=$set_head['challan_condition']?>
							<?php} ?><br/>
					</td>
					<td style="vertical-align:top;text-align:left;border-right:1px solid;">
					
					</td>
					</tr>
					<tr height="20px">
						<td colspan="2" style="vertical-align:bottom;border-bottom:1px solid;"> 
								<br/>Receiver's Signature	
						</td>
						 
						<td style="text-align:right;vertical-align:bottom;border:1px solid;border-left:none;border-top:none;border-left:none;">
							Authorised Signature
						</td>
					</tr>-->

					<!-- Multi Page Challan End -->				


				</div>
				<div id="print2"></div>
				<div id="print3"></div>

			</div>
			<?php  
			$contents = ob_get_contents();
			$_SESSION['contents']=$contents;
			$_SESSION['file_name']='Challan-#';
			$_SESSION['page_size']='A5';
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
<?php include_once($incPath.'footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($incPath.'include_js_file.php');?>   
<script src="<?=ROOT.CRM_ROOT?>js/app/invoice.js"></script>
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
	//var duplicate = $("#invoiceprint").clone().prepend("<hr style='border-color:#000; border-style:dashed; margin:10px 0' />").appendTo("#invoiceprint");
	var printContents = document.getElementById('receipt_print').innerHTML;     
	document.body.innerHTML = printContents;
	window.print();
	document.body.innerHTML = originalContents;
}

function PrintMe(DivID) {

	if($('#print_status').val()=='')
	{
		alert('Select PrintType');
	}
	else
	{


		if($('#print_status').val()<=3)
		{	
			for(var i=1;i<$('#print_status').val();i++)
			{	
				if($("#invoice").val()==2)
				{
					$("#print"+i+" .data_title").html('Performance');
					$("#type").html("Performance Invoice");
				}
				if($("#invoice").val()==1)
				{
					$("#print"+i+" .data_title").html('ORIGINAL');
					$("#type").html($("#typename").val());
				}
				if(i<$('#print_status').val())
				{
					$("#print"+i).after('<div class="page"></div>');
				}
				$("#print"+(i+1)).html($("#print1").clone());
				if((i+1)==2)
				{
					$("#print"+(i+1)+" .data_title").html('DUPLICATE');
				}
				if((i+1)==3)
				{
					$("#print"+(i+1)+" .data_title").html('TRIPLICATE');
				}

			}
		}
		else
		{
			$("#print1 .data_title").html('EXTRA');
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
  docprint.document.write('<head><title><?phpecho TITLE;?></title>');
//  docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/style.css" media="all"/>');
docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/bootstrap.min.css" media="all"/>');
docprint.document.write('<style type="text/css">');
if ($('input[name=logo]:Checked').val() == "1") {

	$('#table_head').show();
	$('#table_foot').show();
	docprint.document.write(' @media print{ @page { size:A4; margin: 0.2in <?=$set_head['letter_head_right_margin']?>in 0.2in <?=$set_head['letter_head_left_margin']?>in; } }   ');

}
else{
	docprint.document.write(' @media print{ @page { size:A4; margin: <?=$set_head['letter_head_top_margin']?>in <?=$set_head['letter_head_right_margin']?>in <?=$set_head['letter_head_bottom_margin']?>in <?=$set_head['letter_head_left_margin']?>in; } }  #table_head, #table_foot { display:none }');
		//$('#invoice_type').css('margin-top','1.7in');	
		
	}

	docprint.document.write('body { font-family:Tahoma;color:#000;');
	docprint.document.write('font-family:Tahoma,Verdana; font-size:10px;} .breakout table td,.breakout table th {padding: inherit !important;text-align: inherit !important;}.dataTables_length, .dataTables_filter , .dataTables_paginate { display:none; }');
	docprint.document.write('.breakout table td,.breakout table th {padding: inherit !important;text-align: inherit !important;}a{color:#000;text-decoration:none;} h1 {font-size:25px; line-height:5px;} b { font-weight:normal; } div.page { page-break-after: always; page-break-inside: avoid; } tr { page-break-inside: avoid } .maintable tbody tr { border-bottom:0.5px #ccc solid; }');
	docprint.document.write(' .breakout table td,.breakout table th {padding: 2px !important;text-align: inherit !important;}.maintable table { page-break-inside:auto } .maintable tr{ page-break-inside:avoid; page-break-after:auto } .maintable thead { display:table-header-group }  .maintable tfoot tr{ /*display:table-footer-group;*/ page-break-inside:avoid; page-break-before:always; } footer-table{ page-break-inside:avoid; page-break-before:always;  } #table_foot{position:fixed;bottom:0}</style>');
	docprint.document.write('</head><body onLoad="self.print()">');
	docprint.document.write(content_vlue);
	docprint.document.write('</body></html>');
	docprint.document.close();
	docprint.focus();
	$('#table_head').show();
	//$('#invoice_type').css('margin-top','0px');

}
location.reload();
}
</script>


</body>
</html>
