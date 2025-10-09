<?php 
session_start();
include('../include/urlfile.php');	
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$_SESSION['contents']='';
$form="Jobcard";
$mode="Print";
$bom_id = $dbcon->real_escape_string($_REQUEST['id']);

$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));

$jobwork_id = $dbcon->real_escape_string($_REQUEST['id']);
$ser_pro=" and jo.jobwork_id=".$jobwork_id;
$query='select jo.*,pr.product_hsn,jo.j_chalan_no,unit.unit_name,pr.product_name,led.l_name,led.m_address,led.gst_no,(select COALESCE(sum(p.product_qty),0) as tqty from tbl_grn as j left join tbl_grn_trn as p on p.grn_id=j.grn_id 
where j.purchaseorder_id=jo.jobwork_id and grn_status=0 and ref_type=1 and grn_trn_status=0) as tqty from tbl_jobwork as jo 
left join product_mst as pr on pr.product_id=jo.j_product_id
left join unit_mst as unit on unit.unitid=pr.product_base_qty
left join tbl_ledger as led on led.l_id=jo.j_vendor
where jo.job_close_status="0" '.$ser_pro.' and jo.j_process_type!=1 and jo.status="0" and  jo.company_id='.$_SESSION['company_id'];
$jobwork_data=mysqli_fetch_assoc($dbcon->query($query));
//print_r($jobwork_data);	
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Jobcard Print</title>
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
/*td, th {
    padding: 0px 2px !important;
    }*/

    .td1
    {
    	text-align:center;
    	vertical-align:top;
    	border-right:1px solid;
    	border-left:1px solid;
    	border-bottom:1px solid;
    }
    .td2
    {
    	text-align:center;
    	border-bottom-color:#FFFFFF; 
    	border-right:1px solid;
    	vertical-align:top;
    	border-bottom:1px solid;
    }
    .td3
    {
    	text-align:center;
    	vertical-align:top;
    	border-bottom-color:#FFFFFF; 
    	border-right:1px solid;
    	border-bottom:1px solid;
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
									<li><a href="<?=ROOT.PRODUCTION_ROOT.'job_card_list'?>"><?=$form?> List</a></li>
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
								<?=$form?> <?=$mode?>
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
										<a href="<?=ROOT.PRODUCTION_ROOT.'bom_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
										<!--<input type="button" name="printpdf" id="printpdf" class="btn btn-default" value="Export to Pdf" onClick="make_pdf()" />-->
									</div>
								</center>	
								<div class="col-md-12"></div>
								<label class="col-md-3 control-label"></label>
								<div class="col-lg-4"></div>
								<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
								<?php ob_start(); ?>
								<div class="col-lg-12 table-responsive" id="receipt_print">	<div class="col-md-12" style=" margin-top:10px;" id="print1">

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
									<table>
										<tr>
											<td width="30%"></td>
											<td  width="10%"><strong><center>Job Work Challan</center></strong></td>
											<td  width="30%"></td>
										</tr>
										<tr>
											<td width="100%" colspan="3">(For movement of Inputs or partially processed goods under New Cenvat rule57 AC (5) (a) from one factory to another factory for further processing operation)</td>
		<!-- <td  width="100%"><strong><center>Job Work Challan</center></strong></td>
			<td  width="30%"></td> -->
		</tr>
	</table>

	<table width="100%" border="0" style="" id="">
		<tr style="border-top:0.5px #000 solid;">
			<td width="90%" style="text-align:center"> 
				<strong style="font-size:16px">
					Part - I
				</strong>
			</td>

		</tr>
	</table>

	<table width="100%" border="0" style="" id="">
		<tr style="border-top:0.5px #000 solid;">
			<td width="60%" > 
				Sub-Contractor's Name & Address
			</td>
			<td width="20%" style="text-align:center"> 
				<strong>Job Work No :</strong>	
			</td>
			<td width="20%" style="text-align:center"> 
				<strong><?php echo $jobwork_data['jobwork_no']; ?></strong>	
			</td>
		</tr>

		<tr>
			<td width="60%" > 
				<?php echo $jobwork_data['l_name']; ?>
				<br>
				<?php echo $jobwork_data['m_address']; ?>

			</td>
			<td width="20%" style="text-align:center"> 
				<strong>Challan No :</strong>	
			</td>
			<td width="20%" style="text-align:center"> 
				<strong><?php echo $jobwork_data['j_chalan_no']; ?></strong>	
			</td>
		</tr>
		<tr>
			<td width="60%" > 

			</td>
			<td width="20%" style="text-align:center"> 
				<strong>Challan Date :</strong>	
			</td>
			<td width="20%" style="text-align:center"> 
				<strong><?php echo $jobwork_data['jobwork_date']; ?></strong>	
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td width="10%" > 
				<strong> GSTIN : </strong>
			</td>
			<td width="10%"> 
				<strong><?php echo $jobwork_data['gst_no']; ?></strong>	
			</td>
			<td width="85%" > 

			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td width="2%"></td>
			<td width="85%"> 
				Please Receive the Following Material and acknowledge the receipt on the Duplicate. In case of any complaint the same should be notified within 10 days from the date hereof. No complaint will be entertained thereafter.	
			</td>
			<td width="10%" style="text-align:center"> 

			</td>
		</tr>
	</table>

	<table width="100%" class="" style="font-size: 12px;margin-top: 5px;">
		<thead>
			<tr height="30px" style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">					
				<th  width="5%" style="">
					<strong>NO.</strong>
				</th>
				<th width="25%"  style="" ><strong>Item Details</strong></th>
				<th width="15%"  style="" ><strong>HSN/SAC</strong></th>
				<th width="15%"  style="" ><strong>UOM</strong></th>
				<th width="15%"  style="" ><strong>Quantity</strong></th>
				<th width="15%"  style="" ><strong>Rate</strong></th>
				<th width="15%"  style="" ><strong>Amount</strong></th>
			</tr>
		</thead>
		<tbody style="border: 1px none;">
			<?php
			$jobwork_id = $dbcon->real_escape_string($_REQUEST['id']);
			$ser_pro=" and jo.jobwork_id=".$jobwork_id;
			$query='select jo.*,pr.product_hsn,unit.unit_name,pr.product_name,led.l_name,(select COALESCE(sum(p.product_qty),0) as tqty from tbl_grn as j left join tbl_grn_trn as p on p.grn_id=j.grn_id 
			where j.purchaseorder_id=jo.jobwork_id and grn_status=0 and ref_type=1 and grn_trn_status=0) as tqty from tbl_jobwork as jo 
			left join product_mst as pr on pr.product_id=jo.j_product_id
			left join unit_mst as unit on unit.unitid=pr.product_base_qty
			left join tbl_ledger as led on led.l_id=jo.j_vendor
			where jo.job_close_status="0" '.$ser_pro.' and jo.j_process_type!=1 and jo.status="0" and  jo.company_id='.$_SESSION['company_id'];
			$result1=$dbcon->query($query);
			if(mysqli_num_rows($result1)>0)
			{
				$i=1;
				$total=0;
				$pending_qty=$rel['j_qty']-$rel['tqty'];
				while($re=mysqli_fetch_assoc($result1)) {
					$total=$re['used_qty']+$total;
					?>
					<tr style="">
						<td style="padding-top: .2em;padding-bottom: .2em;" >
							<?php echo $i; ?>
						</td>
						<td style="padding-top: .2em;padding-bottom: .2em;" ><?php echo $re['product_name']; ?></td>
						<td style="padding-top: .2em;padding-bottom: .2em;" ><?php echo $re['product_hsn']; ?></td>
						<td style="padding-top: .2em;padding-bottom: .2em;" ><?php echo $re['unit_name']; ?></td>
						<td style="padding-top: .2em;padding-bottom: .2em;" ><?php echo $re['j_qty']; ?></td>
						<td style="padding-top: .2em;padding-bottom: .2em;" >-</td>
						<td style="padding-top: .2em;padding-bottom: .2em;" >
						-</td>
					</tr>
					<?php $i++;} }else { ?>
						<tr>
							<td style="border:1px #444 solid;" colspan="7"><center>No data Found</center></td>

						</tr>

					<?php } ?>
					<tr style="border-top:0.5px #000 solid;border-bottom:0.5px #000 solid;">

						<td></td>
						<td  ></td>
						<td  ></td>
						<td  style="float: right;"><strong>TOTAL</strong></td>
						<td  style=""><?php echo $total; ?></td>
						<td  style=""></td>
						<td  style="">-</td>
					</tr> 
				</tbody>	
			</table>

			<table border="0">
				<tr>
					<td width="20%"><strong>Amount In words :</strong></td>
					<td width="20%"><strong><?=ucwords(convert_number_to_words($total))?> </strong></td>
					<td width="20%"></td>
					<td width="20%"></td>
					<td width="10%"></td>
					<td width="10%"></td>
				</tr>
			</table>
			<hr>
			<table width="100%" style="border-collapse: collapse;">
				<tr style="border: none;">
					<td width="40%" ><strong>GST NO : <?php echo $set_head['vatno']; ?></strong></td>
					<td width="60%" style="border-left: solid 1px #000;"><strong>For, SHAREE UMIYA F-TECH MACHINES </strong></td>

				</tr>

				<tr style="border: none;">
					<td width="40%" ><strong>Pan No : <?php echo $set_head['pan_no']; ?></strong></td>
					<td width="60%" style="border-left: solid 1px #000;"><strong> </strong></td>

				</tr>
			</table>
			<hr>

			<table width="100%" border="0" style="" id="">
				<tr style="border-top:0.5px #000 solid;">
					<td width="90%" style="text-align:center"> 
						<strong style="font-size:16px">
							Part - II
						</strong>
					</td>

				</tr>
			</table>

			<table width="100%" border="0" style="" id="">
				<tr style="border-top:0.5px #000 solid;">
					<td width="5%"></td>
					<td width="95%" style="text-align:center"> 
						<strong>
							To be filled by parent factory in duplicate of challan on receipt of goods from the processing factory
						</strong>
					</td>
				</tr>
			</table>
			
			<table border="0">
				<tr>
					<th width="5%"></th>
					<th width="35%"></th>
					<th width="20%">Vender's<br> Ch. No,</th>
					<th width="20%">Date</th>
					<th width="20%">Qty.</th>
				</tr>
				<tr>
					<td width="5%"><strong>1</strong></td>
					<td width="35%"><strong>. Date and time of despatch of finished goods to parent factory/another mfg and entry No. and date of receipt in the account in the processing factory.</strong></td>
					<td width="20%"></td>
					<td width="20%"></td>
					<td width="20%"></td>

				</tr>
				<tr>
					<td width="5%"><strong>2</strong></td>
					<td width="35%"><strong>Quantity despached (No. / Weight / Liters / Meters ) and entered 2 ie account</strong></td>
					<td width="20%"></td>
					<td width="20%"></td>
					<td width="20%"></td>

				</tr>
				<tr>
					<td width="5%"><strong>3</strong></td>
					<td width="35%"><strong>Nature of processing mfg done.</strong></td>
					<td width="20%"></td>
					<td width="20%"></td>
					<td width="20%"></td>
				</tr>
				<tr>
					<td width="5%"><strong>4</strong></td>
					<td width="35%"><strong>Quantity of waste material returned to the parent factory of cleared for home consumption. Invoice No. and date. Quantum of duty paid ( Both figure and words )</strong></td>
					<td width="20%"></td>
					<td width="20%"></td>
					<td width="20%"></td>

				</tr>
			</table>

			<table width="100%">
				<tr>
					<td width="60%"><strong>Place : </strong></td>
					<td width="40%"><strong>Signature Of processor </strong></td>
				</tr>
				<tr>
					<td width="60%"><strong>Date : </strong></td>
					<td width="40%"><strong>Name Of the factory Address </strong></td>
				</tr>
			</table>
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
	{ window.open('".ROOT.PRODUCTION_ROOT."export/print','_blank');
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
