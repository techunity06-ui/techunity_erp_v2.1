<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$incPath = '../../include/';

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
$form="Godown Tranfer Stock";
$mode="Print";
$stock_transfer_id=$dbcon->real_escape_string($_REQUEST['id']);
$query="select stc.*,gd.gd_name as to_godown,gd.gd_address as to_godownaddress, fgd.gd_name as from_godown, fgd.gd_address as from_godownaddress from tbl_stock_transfer as stc 
left join mst_godown as gd on gd.gd_id = stc.to_godown_id
left join mst_godown as fgd on fgd.gd_id = stc.from_branch_id 
where stock_transfer_id=$stock_transfer_id";

$rel=brp_mysqli_fetch_assoc($dbcon->query($query));	

$set="select * from tbl_company where company_id=".$rel['company_id'];
$set_head=brp_mysqli_fetch_assoc($dbcon->query($set));	

$company_name=$set_head['company_name'];
$gst_no=$set_head['vatno'];
$godown_address=$rel['to_godownaddress'];

$order_date='';$dispatch_date='';

$companyConfiguration=getCompanyConfiguration($dbcon);
$sales_pro_search=explode(",", $companyConfiguration['sales_pro_search']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>SALES ORDER - <?=$rel['stock_transfer_doc_no']?></title>
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
									<?php if(in_array(INVENTORY_STOCK_TRANSFER_LIST_SLUG_CREATE,$bulkAccessArray)){ ?>
										<li><a href="<?=ROOT.INVENTORY_ROOT.'stock_transfer_list'?>"><?=$form?> List</a></li>
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
										<a href="<?=ROOT.INVENTORY_ROOT.'stock_transfer_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
									</div>
									
								</center>	
								<div class="col-md-12"></div>
								<label class="col-md-3 control-label"></label>
								<div class="col-lg-4"></div>
								<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
								<?php ob_start(); ?>
								<div class="col-lg-12" id="receipt_print">	<div class="col-md-12 breakout" style=" margin-top:10px;" id="print1">
									<!--<img src="<?=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>	-->	
									<table  class="maintable headermain " border="2" style="border-radius: 10px;
									border-collapse: separate;
									border-width: 2px;
									border-color: black;
									margin-top: 23px;
									border: 1px solid; padding:17px 0 0;" id="table_head" width="100%">
									<tr style="border:none;">
										<td width="100%" style="border:none;padding:0px 0px !important;"> 
											<!--<img src="<?=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>-->
											
											<h1 style="margin-bottom:0px;" align="center"><?=$set_head['company_name']?></h1>
											<h5 align="center" style="padding:top:8px;"><?=$set_head['logo_content']?></h5>
											<h4 style="font-size:19px; margin-bottom:0px;" align="center"><?=$set_head['address']?></h3>
												
												<h4 style="font-size:14px; margin-top:0px;" align="center"><?php if($set_head['website']){?>Email: <?=$set_head['website']?><?php }?> 
												<?php if($set_head['contact_no']){?>(M) <?=$set_head['contact_no']?><?php }?></h4>
												<h4 align="center" style="margin-top:0px;"><?php if($set_head['company_website']){?>Website: <?=$set_head['company_website']?><?php }?></h4>

												<h4 align="center" style="margin-top:0px;"><?php if($set_head['cin']){?>CIN : <?=$set_head['cin']?><?php }?></h4>
												
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
																<td rowspan="4" width="55%" style="vertical-align:top;border:1px solid;padding-bottom:0px !important;border-right:1px solid;border-left: none;">
																	M/s,<br>
																	<strong><?=$company_name?></strong>
																	<span style="font-weight:normal;"> <br>
																		<?=$godown_address?>,<br/></span>
																		
																			
																		</td>
																		<td width="10%" style="vertical-align:top;border:1px solid;border-right:none;border-left:none"><strong>Transfer No </strong>
																		</td>

																		<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;border-right:none;">: <strong><?=$rel['stock_transfer_doc_no']?></strong>
																		</td>

																	</tr>
																	<tr>
																		<td style="vertical-align:top;border:1px solid;border-right:none;border-left:none;white-space:nowrap;">
																			<strong>Transfer Date</strong>
																		</td>

																		<td style="vertical-align:top;border-bottom:1px solid;border-right:none;">
																			: <?=date('d/m/Y',strtotime($rel['stock_transfer_doc_date']))?>	
																		</td>
																	</tr>

																	
																	
																	
																	<!--</thead>-->	
																</table>
															</th>
														</tr>
														<tr style="border-bottom:1px solid;">
																		
																		<td  width="10%"  style="vertical-align:top;border-right:1px solid;border-left:none;white-space:nowrap;">Our Order No.</td>
																		<td width="90%"  style="vertical-align:top;border-left:none;border-right:none;white-space:nowrap;"></td>
																	</tr>
																	<tr style="border-bottom:1px solid;">
																		<td  width="10%"  style="vertical-align:top;border:1px solid;border-left:none;white-space:nowrap;">Date:</td>
																		<td width="90%"  style="vertical-align:top;border:1px solid;border-left:none;border-right:none;white-space:nowrap;"></td>
																	</tr>
														
													</thead>
												</table>
												<table width="100%" class="maintable" style="font-size: 11px;   border-right: 1px solid !important; border-left: 1px solid !important;" id="invoice_types" >

													<tbody style="border: 1px solid;">
														<tr height="30px">					
															<th  width="5%" style="text-align:center !important;border:1px solid;border-top:none;">
																<strong>SR. NO.</strong>
															</th>
															<th width="10%" colspan="1"  style="text-align:center !important;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Code No.</strong></th>
															<th width="45%"  style="text-align:center !important;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Product Name</strong></th>
															<th width="17%" style="text-align:center !important;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>Quantity</strong></th>
															<th width="17%" style="text-align:center !important;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>Batch No.</strong></th>
															<!--<th width="17%" style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>Sqr/Ft</strong></th>-->
															<!--<th width="15%" style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>HSN/SAC Code</strong></th>-->
															
														</tr>
														<?php
														/*$qry="select * FROM `tbl_stock_transfer_trn` as trn left join product_mst as product on product.product_id=trn.product_id 
														left join tbl_reserve_stock
														left join unit_mst as per on per.unitid=trn.stock_unit where status=0 and stock_transfer_id=".$rel['stock_transfer_id'];*/
														$qry="select res.*,stock.batch_no,pro.product_icode, pro.product_name, pro.product_alias_name, res.base_stock, umst.unit_name from tbl_reserve_stock as res
															left join tbl_stock_transfer_trn as strn on strn.stock_transfer_trn_id = res.ref_id 
															left join tbl_stock_transfer as st on strn.stock_transfer_id = st.stock_transfer_id 
															left join product_mst as pro on pro.product_id = res.product_id
															left join tbl_stock_trn as stock on stock.stock_id = res.stock_id 
															left join unit_mst as umst on umst.unitid=res.base_unit
															where  strn.status = 0 and strn.stock_transfer_id =".$stock_transfer_id." and res.stock_flage = 1 and res.stock_status = 0 and res.ref_name = 'stock_transfer_trn'";
														$result=$dbcon->query($qry);		
														$i=1;$total=0;$discount=0;$totalqty=0;
														$cnt=mysqli_num_rows($result);
														while($row=mysqli_fetch_assoc($result))
														{
															$item_code = '';
															if(in_array('item',$sales_pro_search)){
																$item_code = $row['product_icode'];
															}
															$batch_no = '-';
															if($row['batch_no'] !=""){
																$batch_no = $row['batch_no'];
															}

															$totalqty = $totalqty + $row['base_stock'];
															?>
															<tr style="height:40px">
																<td style="text-align:center !important;vertical-align:top;border-right:1px solid;border-left:1px solid;">
																	<?=$i?>
																</td>
																<td style="text-align:center !important;vertical-align:top;border-right:1px solid;border-left:1px solid;"><?=$item_code?></td>
																<td style="padding-left:5px;border-bottom-color:#FFFFFF; border-right:1px solid;vertical-align:top;" >
																	<?php if($row['product_alias_name']){?>
																		<strong><?=stripcslashes($row['product_alias_name'])?> - <?=$item_code;?></strong>
																	<?php }else{ ?>
																		<strong><?=stripcslashes($row['product_name'])?></strong>
																		<?php }?>
																		</td>
																		<td style="text-align:center !important;padding-right:10px;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;" >
																			<?=$row['base_stock'].' '.$row['unit_name']?>
																		</td>
																		<td style="text-align:center !important;padding-right:10px;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;" >
																			<?=$batch_no?>
																		</td>
																		
																	</tr>
																	<?php	
																	$i++; 
																	
																	$totalqty=$totalqty+$row['product_qty'];
																}
																$pr=15-$cnt;
																for($j=0; $j<$pr; $j++)
																{
																	?>
																	<tr style="height:40px">
																		<td style="border-right:1px solid;border-left:1px solid;"></td>
																		<td style="border-right:1px solid;"></td>
																		<td style="border-right:1px solid;"></td>
																		<td style="border-right:1px solid;"></td>
																		<td style="border-right:1px solid;"></td>
																	</tr>
																<?php } ?>
																<tr height="24px">
																	<td colspan="3" style="border-top:1px solid;border-bottom:1px solid;border-right:1px solid;border-left:1px solid;font-size:14px;text-align:right !important;">TOTAL</td>
																	<td style="text-align:center;border-bottom:1px solid; border-top:1px solid;font-size:14px;border-right:1px solid; "><?=number_format($totalqty,2,".","")?></td>
																	<td style="text-align:center;border-bottom:1px solid; border-top:1px solid;font-size:14px;border-right:1px solid; ">
																	</td>
																</tr>	
																<tr>
																	<td colspan="7" class="text-justify" style="border:1px solid;">
																	<strong>Note:</strong> We under take no responsibility of breakage, shortage in transit in spite of our paying carefulattention to the dispatch All Claims will be settled in Ahmedaad. Goods once sold will notbe taken back. Interest willbe charged @24% on all bills on all bills not paid within one month. Breakage or disapproval of goods must beintimated to us in within 8 days, on receipt of goods.
																</td>
																</tr>
																<tr>
																	<td rowspan="5" colspan="2" style="text-align: right; border-right: 1px solid;vertical-align: top;">
																		Received Sign.
																	</td>
																	<td rowspan="5" colspan="2" style="text-align: left; border-right: 1px solid;vertical-align: top;">
																		<p>G.S.T.TIN. 24075601753 DT.01.07.2002</p>
																		<p>C.S.T.TIN. 24075601753 DT.20.04.2000</p>
																		<p style="font-size: 14px;font-weight: bolder;"> 
																			<strong>G.S.T.NO. <?=$gst_no?></strong> 
																		</p>
																	</td>
																	<td colspan="5" style="text-align: right; border-right: 1px solid;"><img src="<?=DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'];?>" style="height: 100px; width: : 100px;"></td>
																</tr>
																<tr>
																	<td colspan="3" style="text-align: right; border-right: 1px solid; border-bottom: 1px solid;"><strong>For, <?=$company_name?></strong></td>
																</tr>
															</tbody>
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
				<!-- <script src="<?=ROOT.CRM_ROOT?>js/app/invoice.js"></script> -->
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
  docprint.document.write('<head><title><?php echo TITLE;?></title>');
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