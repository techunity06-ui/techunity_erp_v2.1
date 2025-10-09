<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$_SESSION['contents']=''; 
$form="Stock General";
$mode="Print";
$stock_general_id=$dbcon->real_escape_string($_REQUEST['id']);
$query="select * from tbl_general_stock where general_stock_id=$stock_general_id";
$rel=mysqli_fetch_assoc($dbcon->query($query));

$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));	

$general_stock_date='';

if($rel['general_stock_date']!="1970-01-01" && $rel['general_stock_date']!="0000-00-00")
{
	$general_stock_date=date('d-m-Y',strtotime($rel['general_stock_date']));
}


/* Check Discount is On or off End */
$companyConfiguration=getCompanyConfiguration($dbcon);
$purchase_pro_search=$companyConfiguration['purchase_pro_print'];
$pro_search=explode(",", $purchase_pro_search);
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
		td, th {
			padding: 0px 5px !important;
		}
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
									<li><a href="<?=ROOT.INVENTORY_ROOT.'stock_general_list'?>"><?=$form?> List</a></li>
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

							<div class="panel-body">
								<!--<center>-->
									
								<div id="logo_sec_div">
									<div class="col-md-4"> </div>With Logo
									<br/>
									<label class="col-md-4 control-label"> </label>
									<div class="col-md-4 col-xs-11" style="display:none;">
										<form class="form-horizontal" role="form" id="print_form" action="javascript:;" method="post" name="print_form">
											<select class="form-control" name="print_status" id="print_status" <?php if($_REQUEST['printstatus']!=''){ echo "readonly";}?>>
												<option value="">Select Print</option>
												<option value="1" <?php if($_REQUEST['printstatus']=='1'){ echo "selected";}?> selected>ORIGINAL</option>
												<option value="2" <?php if($_REQUEST['printstatus']=='2'){ echo "selected";}?>>DUPLICATE</option>
												<option value="3" <?php if($_REQUEST['printstatus']=='3'){ echo "selected";}?>>TRIPLICATE</option>
												<option value="4" <?php if($_REQUEST['printstatus']=='4'){ echo "selected";}?>>EXTRA</option>
											</select>
										</form>
									</div>
									<div class="col-md-1">
										<input type="checkbox" class="form-control"  name="logo" id="logo" value="1">
									</div>
									<div class="col-md-4">
										<button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>

										<a href="<?=ROOT.INVENTORY_ROOT.'stock_general_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
										<!--<input type="button" name="printpdf" id="printpdf" class="btn btn-default" value="Export to Pdf" onClick="make_pdf()" />-->
									</div>
									<!--</center>	-->	
								</div>	
										
									<div class="col-md-12"></div>
									<label class="col-md-3 control-label"></label>
									<div class="col-lg-4">
									</div>
									<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
									<?php ob_start(); ?>
									<div class="col-lg-12 table-responsive" id="receipt_print">	<div class="col-md-12" style=" margin-top:10px;" id="print1">
										<!-- Fixed Logo Table Start -->
										<table width="100%" class="maintable" id="table_head" style="border: none;border-bottom: 1px solid;" >
											<!-- <tr>
												<td style="border: none;"> 
													<img src="<?=DOMAIN_F . LOGO . $set_head['logo']?>" style="width:8.27in" />
												</td>
											</tr> -->
										<tr style="border:none;">
											<td width="100%" style="border:none;">
												<h2 align="center" style="font-weight:600;"><u><?=$set_head['company_name']?></u></h2>
												<h4 align="center" style="padding:top:0px;margin-top: 0PX;margin-bottom: 0PX;  !important"><?=$set_head['logo_content']?></h4>
												<h4 align="center" style="padding:top:15px;margin-top: 10PX;margin-bottom: 0PX; font-weight:lighter; !important"><?=$set_head['address']?></h4>
												<h4 align="center" style="padding:top:0px;margin-top: 0PX;margin-bottom: 0PX; font-weight:lighter; !important"><?php if($set_head['website']){?><?php }?> 
												<?php if($set_head['contact_no']){?>Contact No. <?=$set_head['contact_no']?><?php }?></h4>
												<h4 align="center" style="padding:top:0px;margin-top: 0PX;margin-bottom: 0PX; font-weight:lighter; !important"><?php if($set_head['website']){?><?php }?> 
												<?php if($set_head['website']){?>E-Mail: <?=$set_head['website']?><?php }?></h4>
												
											</td>
										</tr>
									</table>
									<!-- Fixed Logo Table End -->
									<!-- Multipage Table Start -->	
									<table width="100%" class="maintable" style="font-size: 12px;margin-top: 5px;border-top: 1px solid;" id="invoice_type" >
										<thead>
											<tr>
												<th colspan="11" style="padding:0px !important;">
													<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
														<tr style="height:35px;">
															<td style="text-align:left;border:1px solid;border-top: none;border-right:none;width:25%">Stock General No</td>
															<td style="text-align:left;border:1px solid;border-top: none;border-left:none;width:25%"> : <?=$rel['general_stock_no']?></td>
															<td style="text-align:left;border:1px solid;border-top: none;width:25%;border-right: none;">Stock General Date</td>
															<td style="text-align:left;border:1px solid;border-top: none;width:25%;border-left:none"> : <?=$general_stock_date?></td>
														</tr>
														
													</table>
												</th>
											</tr>
											<tr>
												<th style="width:50%;border:1px solid;border-top: none;border-bottom: none;border-right:1px solid;padding:0px 0px !important;">
													<table style="width:100%;border-collapse: collapse;font-size:12px;">
														<tr style="height:35px;">
															<td style="text-align: center;" colspan="3">Deduct Product</td>
														</tr>

														<tr style="height:35px;">
															<td style="border:1px solid;border-bottom: none;border-left: none;text-align: center;">Sr.No</td>
															<td style="border:1px solid;border-bottom: none;border-left: none;text-align: center;">Sales Order No</td>
															<td style="border:1px solid;border-bottom: none;text-align: center;">Product Name</td>
																<td style="border:1px solid;border-bottom: none;border-left: none;text-align: center;">Qty</td>
															<td style="border:1px solid;border-bottom: none;border-right: none;text-align: center;">User</td>
														</tr>

														<?php 
															$query_d = "select trn.*,pmst.product_name,unit.unit_name,so.sales_order_no,l.l_name from tbl_general_stock_trn as trn 
															left join product_mst as pmst on pmst.product_id = trn.product_id
															left join unit_mst as unit on unit.unitid = trn.rate_unit
																left join tbl_sales_order as so on so.sales_order_id = trn.sales_order_id
															left join tbl_ledger as l on l.l_id = trn.for_user_id
															where trn.status=0 and trn.stock_type=2  and trn.general_stock_id= $stock_general_id";

															$result_d = $dbcon->query($query_d);
															$i=1;
															$cnt_d = brp_mysqli_num_rows($result_d);
															while($row_d = brp_mysqli_fetch_array($result_d)){
														?>
														<tr style="height:35px;">
															<td style="border:1px solid;border-bottom: none;border-left: none;text-align: center;"><?=$i?></td>
															<td style="border:1px solid;border-bottom: none;border-left: none;text-align: center;"><?=$row_d['sales_order_no']?></td>
															<td style="border:1px solid;border-bottom: none;text-align: center;"><?=$row_d['product_name']?></td>
															<td style="border:1px solid;border-bottom: none;border-right: none;text-align: center;"><?=$row_d['product_qty']?> <?=$row_d['unit_name']?></td>
															<td style="border:1px solid;border-bottom: none;border-right: none;text-align: center;"><?=$row_d['l_name']?></td>
														</tr>
														<?php 
																$i++;
															}
															$cnt_d = 20 - $cnt_d;
															for($i=1; $i<$cnt_d; $i++){
														?>
															<tr style="height:35px;">
																<td style="border:1px solid;border-bottom: none;border-left: none;text-align: center;"></td>
																<td style="border:1px solid;border-bottom: none;text-align: center;"></td>
																<td style="border:1px solid;border-bottom: none;border-right: none;text-align: center;"></td>
																<td style="border:1px solid;border-bottom: none;border-right: none;text-align: center;"></td>
																<td style="border:1px solid;border-bottom: none;border-right: none;text-align: center;"></td>
															</tr>
														<?php }?>

													</table>
												</th>
												<th style="width:50%;border:1px solid;padding:0px 0px !important;border-top: none;">
													<table style="width:100%">
														<tr style="height:35px;">
															<td colspan="4" style="text-align:center">Stock In Product</td>
														</tr>
														<tr style="height:35px;">
															<td style="border:1px solid;border-bottom: none;border-left: none;text-align: center;">Sr.No.</td>
															<td style="border:1px solid;border-bottom: none;border-left: none;text-align: center;">Sales Order No</td>
															<td style="border:1px solid;border-bottom: none;text-align: center;">Product Name</td>
															<td style="border:1px solid;border-bottom: none;text-align: center;">Qty</td>
															<td style="border:1px solid;border-bottom: none;border-right: none;text-align: center;">Rates</td>
															<td style="border:1px solid;border-bottom: none;border-right: none;text-align: center;">User</td>
														</tr>
														<?php 
															$query_i = "select trn.*,unit.unit_name,pro.product_name,so.sales_order_no,l.l_name from tbl_general_stock_trn as trn
															left join product_mst as pro on pro.product_id = trn.product_id
															left join unit_mst as unit on unit.unitid = trn.rate_unit
															left join tbl_sales_order as so on so.sales_order_id = trn.sales_order_id
															left join tbl_ledger as l on l.l_id = trn.for_user_id
															where trn.status=0 and trn.stock_type=1 and trn.general_stock_id=$stock_general_id";

															$result_i = $dbcon->query($query_i);
															$j=1;
															$cnt_i = brp_mysqli_num_rows($result_i);
															while($row_i = brp_mysqli_fetch_array($result_i)){
														?>
															<tr style="height:35px;">
																<td style="border:1px solid;border-bottom: none;border-left: none;text-align: center;"><?=$j?></td>
																<td style="border:1px solid;border-bottom: none;border-left: none;text-align: center;"><?=$row_i['sales_order_no']?></td>
																<td style="border:1px solid;border-bottom: none;text-align: center;"><?=$row_i['product_name']?></td>
																<td style="border:1px solid;border-bottom: none;border-right: none;text-align: center;"><?=$row_i['product_qty']?> <?=$row_i['unit_name']?></td>
																<td style="border:1px solid;border-bottom: none;border-right: none;text-align: center;">
																	<?=$row_i['product_rate']?>
																</td>
																<td style="border:1px solid;border-bottom: none;border-right: none;text-align: center;"><?=$row_i['l_name']?></td>
															</tr>
														<?php 
																$j++;
															}
															$cnt_i = 20 - $cnt_i;
															for($i=1; $i<$cnt_i; $i++){
														?>
															<tr style="height:35px;">
																<td style="border:1px solid;
																border-bottom: none;border-left: none;text-align: center;"></td>
																<td style="border:1px solid;border-bottom: none;text-align: center;"></td>
																<td style="border:1px solid;border-bottom: none;border-right: none;text-align: center;"></td>
																<td style="border:1px solid;border-bottom: none;border-right: none;text-align: center;"></td>
																<td style="border:1px solid;border-bottom: none;border-right: none;text-align: center;"></td>
																<td style="border:1px solid;border-bottom: none;border-right: none;text-align: center;"></td>

															</tr>
														<?php 
															}
														?>
													</table>
												</th>
											</tr>
											<tr style="height:35px;border:1px solid;border-bottom: none;text-align: left;">
												<th colspan="11">Remarks : <?=$rel['remark']?></th>
											</tr>
										</thead>
										<tbody style="border: 1px solid;">
											
																	
											<tr>
												<td style="vertical-align:top;border-top:1px solid;height: 50px;text-align: right;" colspan="4">
													For, <strong> <span style="font-size:11px;text-decoration:bold;"> <?=$set_head['company_name']?></span></strong><br>
													<img src="<?=DOMAIN_F . LOGO . $set_head['authorized_signature']?>" style="height: 100px;opacity:0.6">
												</td>
											</tr>
											<tr>
												<td colspan="4" style="vertical-align:bottom;text-align: right;">Authorised Signatory</td>	
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
			<?php include_once('../../include/footer.php');?>
			<!--footer end-->
		</section>

		<!-- js placed at the end of the document so the pages load faster -->
		<?php include_once('../../include/include_js_file.php');?>   

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
  docprint.document.write('<head><title><?php echo TITLE;?></title>');
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
