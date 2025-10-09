<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
$include = '../../include/';
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$_SESSION['contents']='';
$form="Challan";
$mode="Print";
$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
$query="select invoice.*,country.country_name,state.state_name,cust.stateid,state.gst_state_code, city.city_name, cust.l_name, cust.m_address, type.invoice_type,cust_pincode,cust_mobile,gst_no,dispatch.mode_dispatch 
from tbl_invoice as invoice 
left join tbl_ledger as cust on cust.l_id=invoice.cust_id
left join country_mst as country on country.countryid=cust.countryid
left join state_mst as state on state.stateid=cust.stateid
left join city_mst as city on city.cityid=cust.cityid
left join mode_of_dispatch as dispatch on dispatch.mode_dis_id=invoice.dispatch_doc_no
left join tbl_invoicetype as type on type.invoicetype_id=invoice.invoicetype_id
where invoice.invoice_id=$invoiceid";
$rel=mysqli_fetch_assoc($dbcon->query($query));	
$company_name=$rel['l_name'];
$cust_address=$rel['m_address'];
$city_name=$rel['city_name'];
$state_name=$rel['state_name'];
$country_name=$rel['country_name'];
$cust_pincode=$rel['cust_pincode'];
$gst_no=$rel['gst_no'];

/* Get Consignee data start */
if(!empty($rel['consignee_id']))
{	
	$consignee="select * from tbl_custmer_consignee as cust 
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid 
	left join city_mst as city on city.cityid=cust.cityid where cust_id=".$rel['consignee_id'];
	$cons_data=mysqli_fetch_assoc($dbcon->query($consignee));	
	$company_name=$cons_data['company_name'];
	$cust_address=$cons_data['cust_address'];
	$city_name=$cons_data['city_name'];
	$state_name=$cons_data['state_name'];
	$country_name=$cons_data['country_name'];
	$cust_pincode=$cons_data['cust_pincode'];
	$gst_no=$cons_data['gst_no'];
		//var_dump($cons_data);
}
/* Get Consignee data end */



$set="select * from tbl_company where company_id=".$rel['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));	
$challan_date='';$dispatch_date='';
if($rel['challan_date']!="1970-01-01" && $rel['challan_date']!="0000-00-00")
{
	$challan_date=date('d/m/Y',strtotime($rel['challan_date']));
}
$po_no = '';
$po_date = '';
if($rel['is_sales_order']!=0){
	$qry = $dbcon->query("SELECT po_no, po_date FROM tbl_sales_order WHERE sales_order_id = '".$rel['sales_order_id']."'");
	$rels = brp_mysqli_fetch_assoc($qry);
	$po_no = $rels['po_no'];
	$po_date = ($rels['po_date']!='1970-01-01' && $rels['po_date']!='0000-00-00') ? $rels['po_date'] : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php');?>
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
		<?php include_once('../../include/include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once('../../include/left_menu.php');?>
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
									<li ><a href="<?=ROOT.'invoice_list'?>">Invoice List</a></li>
									
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>	
				</div>
				<!--state overview start-->
				<div class="row">			
					<div class="col-md-12">
						<section class="panel">
							<header class="panel-heading">
								New <?=$form?>
							</header>	
							<div class="panel-body">
								<center>
									<div class="col-md-1"> </div>With Logo
									<br/>
									<label class="col-md-2 control-label"> Print</label>
									<div class="col-md-4 col-xs-11">
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
									<div class="col-md-1">
										<input type="checkbox" class="form-control"  name="logo" id="logo" value="1">
									</div>
									<div class="col-md-4">
										<button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>
										<a href="<?=ROOT.'invoice_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
										<!--<input type="button" name="printpdf" id="printpdf" class="btn btn-default" value="Export to Pdf" onClick="make_pdf()" />-->
									</div>
								</center>	
								<div class="col-md-12"></div>
								<label class="col-md-3 control-label"></label>
								<div class="col-lg-4">
									
								</div>
								<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
								<?php ob_start(); ?>
								<div class="col-lg-12 table-responsive" id="receipt_print">	<div class="col-md-12" style=" margin-top:10px;" id="print1">
<!--			
<table width="100%" class="maintable" border="1" id="table_head" style="border-radius:6px;border-collapse: separate; border-width: 2px;border-color: black;" >
	<thead>
		<tr>
			<th style="border: none;padding:5px !important;" width="50%"> 
				<img src="<?=ROOT.LOGO.'fixed_logo.png'?>" style="width:100%;padding: 2px;"/>
				<!--<img src="<?=ROOT.LOGO.$set_head['logo']?>" style="width:100%"/>--
			</th>
			<th style="text-align:left;border: none;"> 
				<?=$set_head['address']?> 
				<?phpif($set_head['contact_no']){?><br/>Contact No. <?=$set_head['contact_no']?><?}?>
				<?phpif($set_head['website']){?><br/>E-Mail: <?=$set_head['website']?><?}?>
			</th>
		</tr>
	</thead>
</table>
-->

<table width="100%" class="maintable" border="0" style="" id="table_head">
	<tr style="border:none;">
		<td width="100%" style="border:none;"> 
			<!--<img src="<?=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>-->
			
			<h2 align="center"><?=$set_head['company_name']?></h2>
			<h5 align="center" style="padding:top:8px;"><?=$set_head['logo_content']?></h5>
			<h5 align="center"><?=$set_head['address']?></h5>
			<h5 align="center"><?if($set_head['website']){?>Email: <?=$set_head['website']?><?}?> 
			<?if($set_head['contact_no']){?>(M) <?=$set_head['contact_no']?><?}?></h5>
			
		</td>
	</tr>
</table>

	<!--<table width="100%" border="0" style="" id="">
		<tr>
			<td width="90%" style="text-align:center"> 
				
			</td>
			<td width="10%" style="text-align:center"> 
				<strong style="font-size:12px">
					<b class="data_title">ORIGINAL</b>
				</strong>
			</td>
		</tr>
	</table>-->
	<!-- Multi Page Challan Start -->				
	<table width="100%" class="maintable" style="font-size: 12px;" id="invoice_type" >
		<thead>
			<tr>
				<th colspan="3" style="padding-left:230px;">
					<strong style="font-size:16px">DELIVERY CHALLAN</strong>
				</th>
				<th style="text-align:right">
					<strong style="font-size:12px">
						<b class="data_title">ORIGINAL</b>
					</strong>
				</th>
			</tr>
		</tr>
		<th colspan="4" style="padding:0px !important;">
			<table border="0" style="font-size:12px;border-collapse:separate;" cellpadding="0"  cellspacing="0" width="100%" id="">
				<!--<thead>-->
					<tr>
						<td rowspan="4" width="55%" style="vertical-align:top;border:1px solid;padding-bottom:0px !important;">
							M/s,<br>
							<strong><?=$company_name?></strong>
							<span style="font-weight:normal;"> <br>
								<?=$cust_address?>,<br/>
								<?=$city_name?>,
								<?=$state_name?>,
								<?=$country_name?>
								<?phpif(!empty($cust_pincode))
								{	?>
									-  <?=$cust_pincode?>
									<?php} ?></span>
									<br> <strong> GSTIN : <?=$gst_no?></strong>
									
								</td>
								
								<td width="15%" style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;"><strong>D.C. No </strong>
								</td>
								
								<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;border-right:1px solid;">: <strong><?=($rel['challan_no']) ? $rel['challan_no'] : '';?></strong>
								</td>							
							</tr>
							<tr>
								<td style="vertical-align:top;border-bottom:1px solid;">
									<strong>D.C. Date</strong>
								</td>
								<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">
									: <?=$challan_date?>							
								</td>							
							</tr>
							
						<!--
						<tr>
							<td style="vertical-align:top;border-bottom:1px solid;">
								<strong>Docket No</strong>
							</td>
							<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">
								: <?=$rel['docket_no']?>							
							</td>							
						</tr>
						<tr>
							<td style="vertical-align:top;border-bottom:1px solid;">
								<strong>Total Box</strong>
							</td>
							<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">
								: <?=$rel['packing_boxes']?>							
							</td>							
						</tr>
						<tr>
							<td style="vertical-align:top;border-bottom:1px solid;">
								<strong>Total Weight</strong>
							</td>
							<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">
								: <?=$rel['total_weight']?>							
							</td>							
						</tr>
						<tr>
							<td style="vertical-align:top;border-bottom:1px solid;white-space:nowrap;">
								<strong>Mode Of Dispatch</strong>
							</td>
							<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">
								: <?=$rel['dispatch_doc_no']?>							
							</td>							
						</tr>
					-->
					<tr>
						<td style="vertical-align:top;border-bottom:1px solid;">
							<strong>PO No.</strong>
						</td>
						<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">
							: <?=$po_no?>							
						</td>							
					</tr>
					<tr>
						<td style="vertical-align:top;border-bottom:1px solid;">
							<strong>PO Date</strong>
						</td>
						<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">
							: <?=$po_date?>							
						</td>							
					</tr>
					<!--</thead>	-->
				</table>
			</th>
		</tr>
		<tr height="30px">					
			<th  width="5%" style="text-align:center;border:1px solid;border-top:none;">
				<strong>SR. NO.</strong>
			</th>
			<th width="55%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Item Description</strong></th>
			<th width="17%" style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>Quantity</strong></th>
			<th width="15%" style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>HSN/SAC Code</strong></th>
			
		</tr>
	</thead>
	<tbody style="border: 1px solid;">
		<?php
		$qry="select * FROM `tbl_invoicetrn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id where trancation_status=0 and product.product_type not in(3) and invoice_id=".$rel['invoice_id'];
		$result=$dbcon->query($qry);		
		$i=1;$total=0;$discount=0;$totalqty=0;
		$cnt=mysqli_num_rows($result);
		while($row=mysqli_fetch_assoc($result))
		{
			?>
			<?php
		/*if(!empty($rel['machine_name']))
		{
			?>
		<tr style="height:34px">
					<td style="border-right:1px solid;border-left:1px solid;"></td>
					<td style="border-right:1px solid;"><?= $rel['machine_name']?></td>
					<td style="border-right:1px solid;"></Td>
					<td style="border-right:1px solid;"></td>
					
				</tr>
				<?}
				*/?>
				<tr style="height:40px">
					<td style="text-align:center;vertical-align:top;border-right:1px solid;border-left:1px solid;">
						<?=$i?>
					</td>
					<td style="padding-left:5px;border-bottom-color:#FFFFFF; border-right:1px solid;vertical-align:top;" >
						<strong><?=stripcslashes($row['product_name'])?></strong>
						<br/><?=nl2br(stripcslashes($row['description']));?>
					</td>
					<td style="text-align:center;padding-right:10px;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;" >
						<?=$row['product_qty'].' '.$row['unit_name']?>
					</td>
					<td style="text-align:center;padding-right:10px;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;" >
						<?=stripcslashes($row['product_hsn_code'])?>
					</td>					
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
		}
		$pr=16-$cnt;
		for($j=0; $j<$pr; $j++)
		{
			?>
			<tr style="height:40px">
				<td style="border-right:1px solid;border-left:1px solid;"></td>
				<td style="border-right:1px solid;"></td>
				<td style="border-right:1px solid;"></Td>
					<td style="border-right:1px solid;"></td>
					<!--<td style="border-right:1px solid;"></td>
						<td style="border-right:1px solid;"></td>-->
					</tr>
				<?php} ?>
				<tr height="24px">
					<td colspan="2" style="border-top:1px solid;border-right:1px solid;border-left:1px solid;font-size:14px;text-align:right;">TOTAL</td>
					<td style="text-align:center; border-top:1px solid;font-size:14px;border-right:1px solid; "><?=number_format($totalqty,2,".","")?></td>
					<td style="border-right:1px solid;border-top:1px solid; ">
						
					</td>
				</tr>	
				<tr>
					<td colspan="4" style="padding: 0px !important;border:1px solid">
						<table class="footer-table" width="100%">
							<tr style="border-bottom:none;">
								<td colspan="2" style="">
									<?if(!empty($set_head['vatno'])){ ?>
										<strong>COMPANY GST No. : <?=$set_head['vatno']?> 
									<?php} ?>
								</td>
								<td colspan="2" style="">
									<span style="font-size:12px;float:right;">For, <strong><?=$set_head['company_name']?></strong></span>
								</td>
							</tr>
							
							<tr height="50px" style="border-bottom:none;">
								<td colspan="2"  style="">
								</td>
								<td colspan="2" style="vertical-align:top;text-align:left;border-right:1px solid;">
								</td>
							</tr>
							<tr height="20px">
								<td colspan="2" style="vertical-align:bottom;"> 
									<br/>Receiver's Signature	
								</td>
								
								<td  colspan="2" style="text-align:right;vertical-align:bottom;border-left:none;border-top:none;border-left:none;">
									Authorised Signature
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</tbody>
			<!--<table width="100%" border="0" style="margin-top: 5px;" id="table_foot">
					<tr>
						<td style="border:none;padding:0px 0px !important;width:100%;"> 
							<img src="<?//=ROOT.LOGO.$set_head['f_logo']?>"  style="width:100%"/>
						</td>
					</tr>
				</table>-->
				
			</table>
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
<?php include_once('../../include/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../../include/include_js_file.php');?>   
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
  docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/style.css" media="all"/>');
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
	docprint.document.write('font-family:Tahoma,Verdana; font-size:10px;} .dataTables_length, .dataTables_filter , .dataTables_paginate { display:none; }');
	docprint.document.write('a{color:#000;text-decoration:none;} h1 {font-size:25px; line-height:5px;} b { font-weight:normal; } div.page { page-break-after: always; page-break-inside: avoid; } tr { page-break-inside: avoid } .maintable tbody tr { border-bottom:0.5px #ccc solid; }');
	docprint.document.write(' .maintable table { page-break-inside:auto } .maintable tr{ page-break-inside:avoid; page-break-after:auto } .maintable thead { display:table-header-group }  .maintable tfoot tr{ /*display:table-footer-group;*/ page-break-inside:avoid; page-break-before:always; } footer-table{ page-break-inside:avoid; page-break-before:always;  } #table_foot{position:fixed;bottom:0}</style>');
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