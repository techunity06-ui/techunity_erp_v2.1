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
$query="select inq.*,cust.cust_name from tbl_inquiry as inq 
left join tbl_customer as cust on cust.cust_id = inq.cust_id
where inquiry_id = ".$invoiceid;
$rel=mysqli_fetch_assoc($dbcon->query($query));	

$set="select * from tbl_company where company_id=".$rel['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));	

$inquiry_date='';$dispatch_date='';
if($rel['inquiry_date']!="1970-01-01" && $rel['inquiry_date']!="0000-00-00")
{
	$inquiry_date=date('d/m/Y',strtotime($rel['inquiry_date']));
}

$currency  = getcurrencydetail($dbcon,$rel['currency_id']);
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
<script src="//unpkg.com/xlsx/dist/xlsx.full.min.js" type="text/javascript"></script>
<script>
function exportFile(){
  var wb = XLSX.utils.table_to_book(document.getElementById('invoice_type'));
  XLSX.writeFile(wb, 'sample.xlsx');
  return false;
}
</script>
<!-- <script src="https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js"></script> -->
<script src="https://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<script type="text/javascript">
   /* var tableToExcel = (function () {
        // Define your style class template.
        var style = "<style>.green { background-color: green; }</style>";
        var uri = 'data:application/vnd.ms-excel;base64,'
            , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->' + style + '</head><body><table>{table}</table></body></html>'
            , base64 = function (s) {
                return window.btoa(unescape(encodeURIComponent(s)))
            }
            , format = function (s, c) {
                return s.replace(/{(\w+)}/g, function (m, p) { return c[p]; })
            }
        return function (table, name) {
            if (!table.nodeType) table = document.getElementById(table)
            var ctx = { worksheet: name || 'Worksheet', table: table.innerHTML }
            window.location.href = uri + base64(format(template, ctx))
        }
    })()*/
</script>
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
									<li ><a href="<?=ROOT.CRM_ROOT.'inquiry_list'?>">Invoice List</a></li>
									
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
									<label class="col-md-2 control-label"> Print</label>
									<div class="col-md-4 col-xs-11">
										<form class="form-horizontal" role="form" id="print_form" action="javascript:;" method="post" name="print_form">
											<select class="form-control" name="print_status" id="print_status" <?if($_REQUEST['printstatus']!=''){ echo "readonly";}?>>
												<option value="">Select Print</option>
												<option value="1" selected>ORIGINAL</option>
												<option value="2">DUPLICATE</option>
												<option value="3">TRIPLICATE</option>
												<option value="4">EXTRA</option>
											</select>
										</form>
									</div>
									<div class="col-md-1">With Logo</div>
									<div class="col-md-1">
										<input type="checkbox" class="form-control"  name="logo" id="logo" value="1" checked>
									</div>
									<div class="col-md-4">
										<button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>
										<a href="<?=ROOT.CRM_ROOT.'inquiry_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
										<!--<input type="button" name="printpdf" id="printpdf" class="btn btn-default" value="Export to Pdf" onClick="make_pdf()" />-->
										<!-- <a href="javascript:;" onClick="/* tableToExcel('invoice_type', '<?=$form?>') */" id="exportBtn1" ><button class="btn btn-success btn-flat" >Export Excel</button></a> -->

                                        <a href="javascript:;" onclick="exportFile()" ><button class="btn btn-success btn-flat" >Export Excel</button></a>
									</div>
								</center>	
								<div class="col-md-12"></div>
								<label class="col-md-3 control-label"></label>
								<div class="col-lg-4">
									
								</div>
								<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
								<?php ob_start(); ?>
								<div class="col-lg-12 table-responsive" id="receipt_print">	<div class="col-md-12" style=" margin-top:10px;" id="print1">
									<table width="100%" class="maintable" border="0" style="" id="table_head">
										<tr style="border:none;">
											<td width="100%" style="border:none;"> 
												<img src="<?=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>
												<!-- <h2 align="center"><?=$set_head['company_name']?></h2>
												<h5 align="center" style="padding:top:8px;"><?=$set_head['logo_content']?></h5>
												<h5 align="center"><?=$set_head['address']?></h5>
												<h5 align="center"><?if($set_head['website']){?>Email: <?=$set_head['website']?><?}?> 
												<?if($set_head['contact_no']){?>(M) <?=$set_head['contact_no']?><?}?></h5> -->
											</td>
										</tr>
									</table>				
								<table width="100%" class="maintable" style="font-size: 12px;border-collapse: collapse;" id="invoice_type" >
									<thead>
										<tr>
											<th colspan="3" style="text-align:center">Quotation Print</th>
											<th style="text-align:right;">Original</th>
										</tr>
									</thead>
										
										
									<tbody style="border: 1px solid;">
										<tr>
											<th rowspan="4" colspan="2" style="width: 50%;border:1px solid black;"></th>
											<th style="border:1px solid black;">Customer Name</th>
											<th style="border:1px solid black;"><?=$rel['cust_name']?></th>
										</tr>
										<tr>
											<th style="border:1px solid black;width: 25%;">Order  Date</th>
											<th style="border:1px solid black;width: 25%;"><?=$inquiry_date?></th>
										</tr>
										<tr>
											<th style="border:1px solid black;">Delivery Date</th>
											<th style="border:1px solid black;">-</th>
										</tr>
										<tr>
											<th style="border:1px solid black;">Project No</th>
											<th style="border:1px solid black;"><?=$rel['inquiry_name']?></th>
										</tr>

										<tr>
											<td colspan="4" style="width:100%;padding: 0px;">
												<table style="width:100%;border-collapse: collapse;" >
													<tr style="border-bottom: 1px solid black;">
														<th style="border-right:1px solid;">Sr.no</th>
														<th style="border-right:1px solid;">Group Of Commodity</th>
														<th style="border-right:1px solid;">Item Code</th>
														<th style="border-right:1px solid;">Name Of Commodity</th>
														<!-- <th style="border-right:1px solid;">Specification</th> -->
														<th style="border-right:1px solid;">Hsn Code</th>
														<th style="border-right:1px solid;">Unit</th>
														<th style="border-right:1px solid;">Qty</th>
														<th style="border-right:1px solid;">Unit Price (<?=$currency['currency_code']?>)</th>
														<th style="border-right:1px solid;">Sub Total (<?=$currency['currency_code']?>)</th>
														<th style="border-right:1px solid;">Group Total (<?=$currency['currency_code']?>)</th>
														<th style="">Remark</th>
													</tr>
													<?
														$query_cat = "select sum(product_amount_conv) as group_total,cat.cat_name,inq.cat_id from tbl_inquiry_trn as inq 
														left join tbl_category as cat on cat.cat_id=inq.cat_id

														where inquiry_trn_status=0 and inquiry_id=".$invoiceid." group by cat_id";

														$result_cat = $dbcon->query($query_cat);
														
														$i = 1;
														while($row_cat = brp_mysqli_fetch_array($result_cat)){


														$query_trn = "select trn.*, pro.product_name, unit.unit_name,pro.product_icode, hsn.hsn_code, cur.currency_symbol from tbl_inquiry_trn as trn 
														left join tbl_category as cat on cat.cat_id=trn.cat_id
														left join product_mst as pro on pro.product_id = trn.product_id
														left join unit_mst as unit on unit.unitid = trn.unitid 
														left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
														left join tbl_currency as cur on cur.currency_id = trn.currency_id
														where inquiry_trn_status=0 and inquiry_id=".$invoiceid." and trn.cat_id=".$row_cat['cat_id']." order by inquiry_trn_id asc";
														$result_trn = $dbcon->query($query_trn);
														$cnt = brp_mysqli_num_rows($result_trn);
														$j=1;
														while($row_trn = brp_mysqli_fetch_array($result_trn)){
															if($j==1){
													?>

													<tr style="height:70px;border-bottom: 1px solid black;">
														<td style="border-right:1px solid"><?=$i?></td>
														<td rowspan="<?=$cnt?>" style="border-right:1px solid;text-align:center"><?=$row_cat['cat_name']?></td>
														<td style="border-right:1px solid"><?=$row_trn['product_icode']?></td>
														<td style="border-right:1px solid"><?=$row_trn['product_name']?></td>
														<!-- <td style="border-right:1px solid"><?=$row_trn['product_spec']?></td> -->
														<td style="border-right:1px solid"><?=$row_trn['hsn_code']?></td>
														<td style="border-right:1px solid"><?=$row_trn['unit_name']?></td>
														<td style="border-right:1px solid"><?=$row_trn['product_qty']?></td>
														<td style="border-right:1px solid"> <?=$row_trn['product_rate_conv']?></td>
														<td style="border-right:1px solid"> <?=$row_trn['product_amount_conv']?>	</td>
														<td rowspan="<?=$cnt?>" style="border-right:1px solid;text-align:center"> <?=$row_cat['group_total']?></td>
														<td ><?=$row_trn['product_spec']?></td>
													</tr>
													<?}else{?>
													<tr style="height:70px;border-bottom: 1px solid black;">
														<td style="border-right:1px solid"><?=$i?></td>
														<!-- <td style="border-right:1px solid">press tbl 1 & connecting line</td> -->
														<td style="border-right:1px solid"><?=$row_trn['product_icode']?></td>
														<td style="border-right:1px solid"><?=$row_trn['product_name']?></td>
														<!-- <td style="border-right:1px solid"><?=$row_trn['product_spec']?></td> -->
														<td style="border-right:1px solid"><?=$row_trn['hsn_code']?></td>
														<td style="border-right:1px solid"><?=$row_trn['unit_name']?></td>
														<td style="border-right:1px solid"><?=$row_trn['product_qty']?></td>
														<td style="border-right:1px solid"> <?=$row_trn['product_rate_conv']?></td>
														<td style="border-right:1px solid"> <?=$row_trn['product_amount_conv']?>	</td>
														<!-- <td style="border-right:1px solid">18260</td> -->
														<td ><?=$row_trn['product_spec']?></td>
													</tr>
												<?
															
														}
														$j++;$i++;
													}

														$total = $total + $row_cat['group_total'];
														
													}
												?>
													

													<tr style="height:30px;border-bottom: 1px solid black;">
														<td colspan="9" style="border-right:1px solid;text-align:right"><strong>Fob</strong></td>
														
														<td >-	</td>
														<td style="border-left:1px solid"></td>
														
													</tr>

													<tr style="height:30px;border-bottom: 1px solid black;">
														<td colspan="9" style="border-right:1px solid;text-align:right"><strong>Total Amount</strong></td>
														
														<td > <?=$total?>	</td>
														<td style="border-left:1px solid"></td>
														
													</tr>
													<tr style="height:30px;">
														<td colspan="12" style="text-align:left"><strong> Amount In Word : </strong> <?=convert_number_to_words_new($total,$rel['currency_id'],$currency['currency_in_word_end'],$currency['currency_in_word'])?></td>
													</tr>
												</table>
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
														<td colspan="2" style="vertical-align:top;text-align:left;">
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

	/* var tableToExcel = (function() {
		var uri = 'data:application/vnd.ms-excel;base64,'
		, template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
		, base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
		, format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
		return function(table, name) {
			if (!table.nodeType) table = document.getElementById(table)
				var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
			window.location.href = uri + base64(format(template, ctx))
		}
	})() */
    
    /* $("#exportBtn1").click(function(){
        TableToExcel.convert(document.getElementById("invoice_type"), {
            name: "Traceability.xlsx",
            sheet: {
            name: "Sheet1"
            }
          });
        }); */
</script>
<script type="text/javascript"> 	
 var lineArray = [];
result_table.forEach(function(infoArray, index) {
  var line = infoArray.join(" \t");
  lineArray.push(index == 0 ? line : line);
});
var csvContent = lineArray.join("\r\n");
var excel_file = document.createElement('a');
excel_file.setAttribute('href', 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(csvContent));
excel_file.setAttribute('download', 'Visitor_History.xls');
document.body.appendChild(excel_file);
excel_file.click();
document.body.removeChild(excel_file); 

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