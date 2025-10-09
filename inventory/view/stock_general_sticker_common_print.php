<?php 
	session_start();
	include('../include/urlfile.php');	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	
	/*$bulkAccessArray = canCheckPermissionAccess($dbcon, [
       PRODUCTION_STORE_LIST_SLUG_VIEW,PRODUCTION_STORE_LIST_SLUG_CREATE,PRODUCTION_STORE_LIST_SLUG_READ,PRODUCTION_STORE_LIST_SLUG_UPDATE,PRODUCTION_STORE_LIST_SLUG_DELETE,PRODUCTION_STORE_LIST_APPROVE,PRODUCTION_STORE_LIST_RETURN
]);

if(!in_array(PRODUCTION_STORE_LIST_APPROVE,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}*/

	$form="Lable Printing";
		
	if(empty($_SESSION['start']))
	{
		$start = date('1-m-Y');
		$end = date("d-m-Y");
	}
	else
	{
		$start = $_SESSION['start'];
		$end = $_SESSION['end'];
	}
	
	$branch_id = $_SESSION['branch_id'];
	$stock_gen_id=$dbcon->real_escape_string($_REQUEST['id']);


$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));	

 /*$query_inward="SELECT pro.product_name,pro.product_icode,grn_trn.batch_no,grn_trn.opening_stock_qty,grn_trn.opening_stock_unit,grn_trn.opening_stock_conv_unit,grn_trn.opening_stock_conv_qty,baseunit.unit_name as bunit,conv_unit.unit_name as cunit FROM opening_stock_mst as grn_trn 
				left join product_mst as  pro on pro.product_id=grn_trn.product_id
				left join unit_mst as baseunit on baseunit.unitid=grn_trn.opening_stock_unit
				left join unit_mst as conv_unit on conv_unit.unitid=grn_trn.opening_stock_conv_unit
				where grn_trn.opening_stock_id = ".$opening_stock_id;
				$rs_inward=$dbcon->query($query_inward);
				$x=1;
				$batch_rel=mysqli_fetch_assoc($rs_inward);*/


//$company_config = getCompanyConfiguration($dbcon);
		
?>


<!DOCTYPE html>
<html lang="en">
	<head>
		<title>LABEL PRINTING LIST</title>
		<?php include_once($include.'include_css_file.php');?>
		<style type="text/css">
			 #canvasTarget{
        margin-top: 20px;
		margin-left:-20px;
		
      }   
		</style>
	</head>
	<body >
		<?php include_once($include.'include_js_file.php');?>   
 <script type="text/javascript" src="<?=ROOT.INVENTORY_ROOT?>js/jquery-barcode.js"></script>
<script type="text/javascript">
	function generateBarcode(barcodeno,s){
 	//alert(s);
        var value = barcodeno;
        var btype = "code39";
        var renderer = "css";
       // console.log(value);
		//console.log(btype);
		//console.log(renderer);
		
        var settings = {
          output:renderer,
          bgColor: "#FFFFFF",
          color: "#000000",
          barWidth: "1",
          barHeight: "50",
          addQuietZone: 1
        };
		//console.log("#barcodeTarget"+s);
          $("#barcodeTarget"+s).html("").show().barcode(value, btype, settings);
      }
</script>
		<section id="container" >
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$form?></h3>
								</header>
								<div class="">
									<ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li class="active"><a href="<?=ROOT.'inventory/stock_general_list'?>"> Stock General</a></li>
									</ul>
								</div>
							</section>
						</div>
			  		</div>
					<div class="row">			
			<div class="col-sm-8 col-md-offset-2">
				<section class="panel">
						<div class="panel-body">
						<center>
							<button type="submit" class="btn btn-success"  onClick="PrintMe('receipt_print');"><i class="icon fa fa-print"></i> Print</button>
							<a href="<?=ROOT.'inventory/stock_general_list'?>" type="button" class="btn btn-danger"><i class="icon fa fa-ban"></i> Cancel</a>
						</center>
			<div class="col-md-12"></div>
		
			<div class="col-lg-9 table-responsive" id="receipt_print">	
				<div style="width: 100%;" id="print1">
					
			<?
				$mode="Print";
				
					
	
	/*$data_string='Item Name='.$batch_rel['product_name'].',Item Code='.$batch_rel['product_icode'].',Batch No='.$batch_rel['batch_no'].',Purchase Bill No='.$batch_rel['invoice_no'].',Grn No='.$batch_rel['grn_no'].',Grn Date='.$batch_rel['grn_date'];
	$data_string=strtoupper($data_string);
	//echo '<br/>';
	//QRcode Start
   //set it to writable location, a place for temp generated PNG files
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    //html PNG location prefix
    $PNG_WEB_DIR = '../temp/';
	include "qrcode/qrlib.php";    
    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);
    $filename = $PNG_TEMP_DIR.'test.png';
    //processing form input
    //remember to sanitize user input in real-life solution !!!
    $errorCorrectionLevel = 'L';
	$matrixPointSize = 1;
	$filename = $PNG_TEMP_DIR.'test'.md5($data_string.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        QRcode::png($data_string, $filename, $errorCorrectionLevel, $matrixPointSize, 2); 
	//Show Only id if User Type mobile
	
   //QRcode End*/
      //$aa=generateBarcode("ssss");

        $query_inward="SELECT btry.batch_stock_no,pro.product_name,pro.product_icode,btry.qty,unit.unit_name FROM tbl_general_stock_trn as grn_trn 
        	left join tbl_batch_stock_trn_in as btry on btry.general_stock_trn_id=grn_trn.general_stock_trn_id
        	left join product_mst as pro on pro.product_id=grn_trn.product_id
        	left join unit_mst as unit on unit.unitid=btry.unitid
				where grn_trn.stock_type=1 and btry.status=0 and grn_trn.status=0 and grn_trn.general_stock_id = ".$stock_gen_id;
				$rs_inward=$dbcon->query($query_inward);
				$s=1;
				while($batch_rel=mysqli_fetch_assoc($rs_inward)){
       
   ?>	
					<table width="100%" style="margin-top:5px;font-size:10px;
					border-collapse:separate;font-weight:bold;" id="batch<?=$i?>" >
						<tr style="font-size: 12px;">
							<td rowspan="3" style="width:30%;">
								<div id="barcodeTarget<?=$s?>"></div>
							</td>
							<td  style="width:15%;"><strong>Item Name </strong></td>
							<td  style="width:55%;">: <?=$batch_rel['product_name']?></td>
							
						</tr>
						<tr style="font-size: 12px;">
							<td  ><strong>Item Code </strong></td>
							<td  >: <?=$batch_rel['product_icode']?></td>
						</tr>
						<tr style="font-size: 12px;">
							<td  ><strong>Qty</strong></td>
							<td  >: <?=$batch_rel['qty']?> <?=$batch_rel['unit_name']?></td>
						</tr>
						<!-- <tr style="font-size: 12px;">
							<td style="white-space: nowrap;" ><strong>Purchase Bill No</strong></td>
							<td  >: <?=$batch_rel['invoice_no']?></td>
						</tr> -->
						
						<tr style="font-size: 10px;line-height: 25px;">
							<td colspan="4">&nbsp;</td>
						</tr>
					</table>
					<?
						if($i>1){ echo '<hr style="margin-top:4px;margin-bottom:4px;"/>';}
						 echo "<script>generateBarcode('".$batch_rel['batch_stock_no']."','$s');</script>";

						//echo "<script>generateBarcode(".$batch_rel['batch_stock_no'].",".$s.");</script>
						 $s++;

					}
					
			//	$x++;
				//}
			?>
		
				</div>
				
			</div>
		
		</div>	
					</section>
				</div>
			  </div>
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
		</section>

	 
		
		
		
<script type="text/javascript"> 
	
	$(document).ready(function () {
		//alert("1");
	//alert("<?=$batch_rel['batch_no']?>");
	// generateBarcode("<?=$batch_rel['batch_no']?>");
	});
	
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

  docprint.document.write('<style type="text/css">@media print{ @page {  margin: 0;} } body {margin-top: 0;margin-bottom: 0;margin-right: 10px !important;margin-left: 10px !important; ');
  docprint.document.write('font-family:Tahoma;color:#000;');
  docprint.document.write('font-family:Tahoma,Verdana; font-size:10px} .dataTables_length, .dataTables_filter , .dataTables_paginate { display:none; }');
  docprint.document.write('ul li {list-style: disc !important;} .dtl-data td,th { padding: 0 2px;}');
  docprint.document.write('a{color:#000;text-decoration:none;} h1 {font-size:15px; line-height:2px;} b { font-weight:normal; } div.page { page-break-after: always; page-break-inside: avoid; } .con ul {padding-left:0px !important;}.con ul li {margin-left:22px !important;} </style>');
  docprint.document.write('</head><body onLoad="self.print()">');
  docprint.document.write(content_vlue);
  docprint.document.write('</body></html>');
  docprint.document.close();
  docprint.focus();
	$.removeCookie('label_inward', { path: '/' });//reset cookie 
  //$.cookie("label_customer", cust_arr);
  //location.reload();
}
 
</script>
	</body>
</html>
