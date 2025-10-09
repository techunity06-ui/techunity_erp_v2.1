<?php 
session_start();
$include = '../../include/';
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$_SESSION['contents']=''; 
$form="GRN";
$mode="Print";
$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);

$query="select po.*,jo.jobwork_no,ppo.purchaseorder_no,state.state_name,l.l_name as vender_name,country.country_name,l.m_address as vender_address,l.gst_no as tin_no,l.cust_mobile as vender_mobile,l.stateid,state.gst_state_code,city.city_name, user.user_name from tbl_grn as po 
left join tbl_ledger as l on l.l_id=po.vender_id
left join country_mst as country on country.countryid=l.countryid
left join state_mst as state on state.stateid=l.stateid
left join city_mst as city on city.cityid=l.cityid
left join tbl_jobwork as jo on jo.jobwork_id=po.purchaseorder_id
left join tbl_purchaseorder as ppo on ppo.purchaseorder_id=po.purchaseorder_id
left join users as user ON user.user_id = po.user_id
where po.grn_id=$purchaseorder_id";
$rel=mysqli_fetch_assoc($dbcon->query($query));
	//$_SESSION['invoice_no']=$rel['invoice_no'];		

if($rel['ref_type']=="1"){
	$ref_no=$rel['jobwork_no'];
}else{
	$ref_no=$rel['purchaseorder_no'];
}


$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));	
$order_date='';
if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
{
	$order_date=date('d-m-Y',strtotime($rel['order_date']));
}
if($rel['purchaseorder_date']!="1970-01-01" && $rel['purchaseorder_date']!="0000-00-00")
{
	$purchaseorder_date=date('d-m-Y',strtotime($rel['purchaseorder_date']));
}

/* Check Discount is On or off Start */
if($set_head['show_disc']=='1'){
	$colspan=5;
	$dynamicwidth=40;
}else{
	$colspan=6;
	$dynamicwidth=46;
}
/* Check Discount is On or off End */

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>GRN View</title>
	<?php include_once($include.'include_css_file.php');?>
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
		td, th {
			padding: 0px 5px !important;
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
									<li ><a href="<?=ROOT.INVENTORY_ROOT.'grn_list'?>"><?=$form?> List</a></li>
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

							<div class="panel-body">
								<center>
									<div class="col-sm-5"  style="padding-left:0;">
										<label class="col-md-2 control-label" style="
										padding:10px 0;">Print</label>
										<div class="col-md-10 col-xs-12">
											<form class="form-horizontal" role="form" id="print_form" action="javascript:;" method="post" name="print_form">
												<select class="form-control" name="print_status" id="print_status" <?php if($_REQUEST['printstatus']!=''){ echo "readonly";}?>>
													<option value="">Select Print</option>
													<option value="1" selected>ORIGINAL</option>
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
											<input type="checkbox" class="form-control"  name="logo" id="logo" value="1" checked>
										</div>
									</div>
									<div class="col-sm-4 col-xs-6 resspace"  style="text-align:right">
										<button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>
										<a href="<?=ROOT.INVENTORY_ROOT.'grn_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
									</div>
									
								</center>

								<div class="col-md-12"></div>
								<label class="col-md-3 control-label"></label>
								<div class="col-lg-4">
								</div>
								<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
								<?php ob_start(); ?>
								<div class="col-lg-12 table-responsive" id="receipt_print">	<div class="col-md-12" style=" margin-top:10px;" id="print1">
									<!-- Fixed Logo Table Start -->
									<table width="100%" class="maintable" border="0" style="" id="table_head">
										<tr>
											<td width="100%" style="border:none;padding:0px 0px !important;"> 
												<img src="<?=ROOT.LOGO.$set_head['logo']?>" style="width:100%"/>
											</td>
										</tr>
									</table>
									<!-- Fixed Logo Table End -->
									<!-- Multipage Table Start -->	
									<table width="100%" class="maintable" style="font-size: 12px;margin-top: 5px;" id="invoice_type" >
										<thead>
											<tr>
												<th colspan="5" style="padding:0px !important;">
													<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
														<tr>
															<td colspan="4" style="text-align:center;width:100%;"> 
																<strong style="font-size:14px;">
																	<?=$form?>
																</strong>
															</td>
														</tr>
														<tr>
															<td width="50%" colspan="2" rowspan="4" style="vertical-align:top;border:1px solid;">
																<b>To, </b> <strong><?=$rel['vender_name']?></strong><span style="font-weight:normal;"><br/><?=$rel['vender_address']?><br><?=$rel['city_name']?>, <?=$rel['state_name']?>, <?=$rel['country_name']?></span><br/>Vendor GST No. : <?=$rel['tin_no']?>
															</td>
															<td width="25%" style="vertical-align:top;border:1px solid;border-right:none;"><strong>Grn No </strong>
															</td>
															<td width="25%" style="vertical-align:top;border:1px solid;border-left:none;"> : <strong><?=$rel['grn_no']?></strong>
															</td>
														</tr>
														<tr>
															<td style="vertical-align:top;border:1px solid;border-right:none;white-space:nowrap;"><strong>Grn Date </strong>
															</td>
															<td style="vertical-align:top;border:1px solid;border-left:none;"> : <strong><?=date('d-m-Y',strtotime($rel['grn_date']))?></strong>
															</td>
														</tr>
														<tr>
															<td style="vertical-align:top;border:1px solid;border-right:none;"><strong>GRN Against </strong>
															</td>
															<td style="vertical-align:top;border:1px solid;border-left:none;"> : <strong><?php if($rel['ref_type']=="1"){ echo "Job Work";}else{ echo "PO";}?></strong>
															</td>
														</tr>
														<tr>
															<td style="vertical-align:top;border:1px solid;border-right:none;white-space:nowrap;"><strong>Ref No </strong>
															</td>
															<td style="vertical-align:top;border:1px solid;border-left:none;"> : <strong><?=$ref_no?></strong>
															</td>
														</tr>
														<tr>
															<td width="25%" style="vertical-align:top;border:1px solid;border-right:none;white-space:nowrap;"><strong>Challan No </strong>
															</td>
															<td width="25%" style="vertical-align:top;border:1px solid;border-left:none;"> : <strong><?=$rel['challan_no']?></strong>
															</td>
															<td width="25%" style="vertical-align:top;border:1px solid;border-right:none;white-space:nowrap;"><strong>Challan Date </strong>
															</td>
															<td width="25%" style="vertical-align:top;border:1px solid;border-left:none;"> : <strong><?=date('d-m-Y',strtotime($rel['grn_date']))?></strong>
															</td>
														</tr>
														<tr>
															<td width="25%" style="vertical-align:top;border:1px solid;border-right:none;white-space:nowrap;"><strong>GIR No </strong>
															</td>
															<td width="25%" style="vertical-align:top;border:1px solid;border-left:none;"> : <strong><?=$rel['gir_no']?></strong>
															</td>
															<td width="25%" style="vertical-align:top;border:1px solid;border-right:none;white-space:nowrap;"><strong>GIR Date </strong>
															</td>
															<td width="25%" style="vertical-align:top;border:1px solid;border-left:none;"> : <strong><?=date('d-m-Y',strtotime($rel['grn_date']))?></strong>
															</td>
														</tr>
														<tr>
															<td width="25%" style="vertical-align:top;border:1px solid;border-right:none;white-space:nowrap;"><strong>L.R No </strong>
															</td>
															<td width="25%" style="vertical-align:top;border:1px solid;border-left:none;"> : <strong></strong>
															</td>
															<td width="25%" style="vertical-align:top;border:1px solid;border-right:none;white-space:nowrap;"><strong>L.R Date </strong>
															</td>
															<td width="25%" style="vertical-align:top;border:1px solid;border-left:none;"> : <strong><?=date('d-m-Y',strtotime($rel['grn_date']))?></strong>
															</td>
														</tr>
													</table>
												</th>
											</tr>
											<tr>
												<th width="3%" style="text-align:center;border:1px solid;border-top: none;"><strong>SR.<br/> NO.</strong></th>
												<th width="<?=$dynamicwidth?>%" style="text-align:center;border:1px solid;border-top: none;" colspan="2">
													<strong>Item Details</strong>
												</th>
												<th width="8%" style="text-align:center;border:1px solid;border-top: none;">
													<strong>HSN/SAC <br/>Code</strong>
												</th>
												<th width="7%" style="text-align:center;border:1px solid;border-top: none;">
													<strong>QTY.</strong>
												</th>
											</tr>
										</thead>
										<tbody style="border: 1px solid;">
											<?php$qry="select trn.*,trn.product_conv_qty as trn_conv_qty,perc.unit_name as conv_unit,product.*,per.unit_name, hsn.hsn_code FROM `tbl_grn_trn` as trn 
											left join product_mst as product on product.product_id=trn.product_id 
											left join mst_hsn_code as hsn on hsn.hsn_id = product.product_hsn 
											left join unit_mst as per on per.unitid=trn.unit_id 
											left join unit_mst as perc on perc.unitid=trn.product_conv_unit 
											where grn_trn_status=0 and grn_id=".$rel['grn_id']." group by grn_trn_id order by grn_trn_id";
											$result=$dbcon->query($qry);		
											$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;
											$cnt=mysqli_num_rows($result);
											while($row=mysqli_fetch_assoc($result))
											{
												if($row['unit_id'] != $row['product_conv_unit']){
													$qty = $row['product_qty'].' '.$row['unit_name'].'<br>'.$row['trn_conv_qty'].' '.$row['conv_unit'];
												}else{
													$qty = $row['product_qty'].' '.$row['unit_name'];
												} ?>
												<tr style="">
													<td style="text-align:center;vertical-align:top;border-right:1px solid;border-left:1px solid;">
														<?=$i?>
													</td>
													<td style="border-bottom-color:#FFFFFF; border-right:1px solid; vertical-align: top;" colspan="2">
														<strong><?=stripcslashes($row['product_name'])?> -- (<?=stripcslashes($row['product_alias_name'])?>)</strong>
														<br/><?=nl2br(stripcslashes($row['product_spec']));?>
													</td>
													<td style="border-bottom-color:#FFFFFF; border-right:1px solid;vertical-align:top;text-align:center" >
														<?=$row['hsn_code']?>
													</td>
													<td style="text-align:center;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;white-space:nowrap;" >
														<?=$qty?>
													</td>
												</tr>
												<?php$i++; 
												$totalqty=$totalqty+$row['product_qty']-$charges_qty;
												$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
												$total_product_amount+=($row['product_qty']*$row['product_rate']);
												$totaltaxable+=$row['product_amount'];
												$totaltax1+=$row['tax_amount1'];
												$totaltax2+=$row['tax_amount2'];
												$total+=$row['total'];
											}
											$pr=8-$cnt;
											for($j=0; $j<$pr; $j++){ ?>	
												<tr style="height:40px; border: none; border-right:1px solid;border-left:1px solid;">
													<td style="border-right:1px solid;border-left:1px solid;"></td>
													<td style="border-right:1px solid;" colspan="2"></td>
													<td style="border-right:1px solid;"></td>
													<td style="border-right:1px solid;"></td>
												</tr>
											<?php} ?>
											<tr style="border: 1px solid;">
												<td style="text-align: right; font-weight: bold;" colspan="3">Total GRN Qty</td>
												<td style="text-align: center;"></td>
												<td style="border-left:1px solid; border-right:1px solid; text-align: center; font-weight: bold;"><?=$totalqty;?></td>
											</tr>
											<tr style="border: 1px solid;">
												<td colspan="5" style="border: 1px solid;">
													<table style="width: 100%; font-weight: bold;">
														<tr>
															<td style="width: 25%;">Received By</td>
															<td style="width: 25%; white-space: nowrap;">: <?=$rel['user_name'];?></td>
															<td style="width: 25%;">Inspected By</td>
															<td style="width: 25%;">: </td>
														</tr>
														<tr>
															<td>Received Date</td>
															<td>: <?=date("d/m/Y",strtotime($rel['cdate']));?></td>
															<td>Inspected Date</td>
															<td>: </td>
														</tr>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
								<div id="print2" style="margin-top:0in;"></div>
								<div id="print3" style="margin-top:0in;"></div>
							</div>
							<?php  
							$contents = ob_get_contents();
							$_SESSION['contents']=$contents;
							$_SESSION['file_name']='invoice-#';
							$_SESSION['page_size']='A4';
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

<script type="text/javascript"> 
	function print_receipt()
	{
		var originalContents = document.body.innerHTML;
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
		//$("#print"+i+" .data_title").html('Performance');
		$("#type").html("Performance Invoice");
	}
	if($("#invoice").val()==1)
	{
		//$("#print"+i+" .data_title").html('ORIGINAL FOR RECIPIENT');
		$("#type").html($("#typename").val());
	}
	if(i<$('#print_status').val())
	{
		$("#print"+i).after('<div class="page"></div>');
	}
	$("#print"+(i+1)).html($("#print1").clone());
	if((i+1)==2)
	{
		//$("#print"+(i+1)+" .data_title").html('DUPLICATE FOR SUPPLIER');
	}
	if((i+1)==3)
	{
		//$("#print"+(i+1)+" .data_title").html('TRIPLICATE FOR TRANSPORTER');
	}
	
}
}
else
{
	//$("#print1 .data_title").html('EXTRA');
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
  else
  {
  	docprint.document.write(' @media print{ @page { size:A4; margin: <?=$set_head['letter_head_top_margin']?>in <?=$set_head['letter_head_right_margin']?>in <?=$set_head['letter_head_bottom_margin']?>in <?=$set_head['letter_head_left_margin']?>in; }  }  #table_head, #table_foot { display:none }');
		//$('#invoice_type').css('margin-top','1.7in');	
		
	}

	docprint.document.write('body { font-family:Tahoma;color:#000;');
	docprint.document.write('font-family:Tahoma,Verdana; font-size:10px;} .dataTables_length, .dataTables_filter , .dataTables_paginate { display:none; }');
	docprint.document.write('a{color:#000;text-decoration:none;} h1 {font-size:25px; line-height:5px;} b { font-weight:normal; } div.page { page-break-after: always; page-break-inside: avoid; } tr { page-break-inside: avoid } .maintable tbody tr { border-bottom:1px solid; }');
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
