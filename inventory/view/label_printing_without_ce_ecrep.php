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

	$form="Lable Printing List";
		
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
	$batch_id=$dbcon->real_escape_string($_REQUEST['id']);


$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));	

$company_config = getCompanyConfiguration($dbcon);
		
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
	<body>
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
							<a href="<?=ROOT.'inward_list'?>" type="button" class="btn btn-danger"><i class="icon fa fa-ban"></i> Cancel</a>
						</center>
			<div class="col-md-12"></div>
		
			<div class="col-lg-9 table-responsive" id="receipt_print">	
				<div style="width: 100%;" id="print1">
					
			<?php 
				$mode="Print";
				$query_inward="SELECT batch.*, grn.grn_no, p.product_name, p.product_icode,ct.cat_name,p.product_sale_rate, p.product_icode,u.unit_name, pr.process_name,p.smpl_size,p.smpl_material FROM tbl_batch_data as batch 
				left join product_mst as p on p.product_id=batch.product_id 
				left join tbl_category as ct on p.product_category=ct.cat_id  
				left join process_mst as pr on pr.process_id=batch.process_id 
				left join unit_mst as u on u.unitid=p.product_base_unit 
				left join tbl_grn as grn on grn.grn_id=batch.grn_id 
				left join tbl_grn_trn as grnt on grnt.grn_trn_id=batch.grn_trn_id where  batch.batch_id = " . $batch_id;
				$rs_inward=$dbcon->query($query_inward);
				$x=1;
				$qty = 1;
				$price = "";


				$year_month = date('Y-m');

				while($batch_rel=mysqli_fetch_assoc($rs_inward)){
					if($x == 1){ ?>
						<?php
	$data_string='C='.$batch_rel['product_icode'].',N='.$batch_rel['product_name'].',L='.$batch_rel['batch_no'].',Y='.$year_month;
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
	
   //QRcode End
   ?> 
					<?php }
					//for ($i=0; $i < $batch_rel['accept_qty']; $i++) { 
						// code...
					
			?>
						
					<table width="100%" style="margin-top:5px;font-size:10px;
					border-collapse:separate;font-weight:bold;" id="batch<?=$i?>" >
					<tr >
							<td colspan="4" style="font-size:12px;"><strong>Name : <span style="margin-left:5px"><?=$batch_rel['product_name']?></span></strong></td>
						</tr>
						<tr style="font-size: 10px;">
							<td style="width:30%;white-space:nowrap;"><!--<strong>Cat. No.: <span style="margin-left:5px"><?=$batch_rel['product_icode']?></span></strong>--><img style="height: 20px;" src="../stickerprinting/catalogue_no.jpg" /> <strong><span style="margin-left:5px"><?=$batch_rel['product_icode']?></span></strong></td>
							<td style="width:22%;white:space:nowrap;"><strong>Size: <span style="margin-left:5px"><?=$batch_rel['smpl_size']?></span></strong></td>
							<!--<td style="width:8%;text-align: end;"> <img style="height: 20px;" src="../stickerprinting/catalogue_no.jpg" /><strong><span style="border: 1px solid black;padding:2px">REF</span> </strong></td>-->
							<td style="width:40%;" colspan="2"> <!--<strong><span style="margin-left:5px"><?=$batch_rel['product_icode']?></span></strong>--> <strong>Material: <span style="margin-left:5px;font-size: 8px;"><?=$batch_rel['smpl_material']?></span></strong></td>
						</tr>
						<tr style="font-size: 10px;">
							<td style=""><!--<strong>Material: <span style="margin-left:5px;font-size: 8px;"><?=$batch_rel['cat_name']?></span></strong>--> <img style="height: 20px;" src="../stickerprinting/batch_code.jpg" /><strong> <strong><span style="margin-left:5px"><?=$batch_rel['batch_no']?></span></strong></td>
							<td style=""><strong>QTY: <span style="margin-left:5px">1 <?=$batch_rel['unit_name']?></span></strong></td>
							<!--<td style="text-align: end;"><img style="height: 20px;" src="../stickerprinting/batch_code.jpg" /><strong><span style="border: 1px solid black;padding:2px">LOT</span></strong></td>-->
							<td style="" colspan="2"><!--<strong><span style="margin-left:5px"><?=$batch_rel['batch_no']?></span></strong>--> <img style="height: 15px;margin-left:5px;" src="../stickerprinting/Date_of_MFG.jpg" /> <span style="margin-left:0px;"><?=$year_month?></span></td>
						</tr>
						<tr style="font-size: 8px;">
							<td colspan="2" style="white-space: nowrap;width:70%;"><strong>MRP:<span style="margin-left:0px">Rs.<?=$batch_rel['product_sale_rate']?>/-<?=$price?></span><span style="margin-left:5px;font-size: 6px;">(Inclu. All Taxes Per PCS<?php //=$batch_rel['unit_name']?>)</span><!--<img style="height: 15px;margin-left:5px;" src="../stickerprinting/Date_of_MFG.jpg" /><span style="margin-left:0px;"><?=$year_month?></span></strong>--></td>
							<td style="font-size: 5px;text-align: end;vertical-align: top;width:20%;">
								<!-- <img style="width: 35px;" src="../stickerprinting/EC_representativenew.jpg" /> -->
								<!--<span style="border: 1px solid black;padding:2px">EC <span style="border-left: 1px solid black;padding:2px">REP</span></span>--></td>
							<td style="font-size: 5px;width:20%;"><!--CMC Medical Devices & Drugs S.L., C/Horaclolengo N415,CP 29003, Malaga, Spain ES B93318149 --></td>
						</tr>
						<tr style="font-size: 8px;">
							<td rowspan="3" style="width:30%;">
								<?php 
								echo '<img style="height: 80px;" src="'.$PNG_WEB_DIR.basename($filename).'"/><br/>'; 
								?>
							</td>
							<td colspan="3" style="width:70%;"><img style="height: 15px;" src="../stickerprinting/IFU.jpg" /><strong> "Please refer the IFU/Autoclave before use"</strong>
							</td>
							</td>
						</tr>
						<tr style="font-size: 8px;">
							<td colspan="3" style="width:70%;">
								<!-- <img style="height: 20px;" src="../stickerprinting/celogo.png" /> -->
								<img style="height: 22px;" src="../stickerprinting/non_sterilenew.jpg" />
								<img style="height: 20px;margin-left:5px;" src="../stickerprinting/caution.jpg" />
								<img style="height: 20px;margin-left:5px;" src="../stickerprinting/damage_pack.jpg" />
								<img style="height: 20px;margin-left:5px;" src="../stickerprinting/no_reuse.jpg" />
								<img style="height: 20px;margin-left:5px;" src="../stickerprinting/heat_radio_away.jpg" />
								<img style="height: 20px;margin-left:5px;" src="../stickerprinting/KEEP_DRY.jpg" />
								
							</td>
							</td>
						</tr>
						<tr style="font-size: 15px;">
							<td colspan="3" style="width:70%;">
								<!--<img style="margin-left:-5px;height: 25px;" src="../stickerprinting/manufacturer.jpg" /><strong><?=$set_head['company_name']?></strong>-->
								<img style="margin-left:5px;" src="../stickerprinting/smplname.png" />
							</td>
						</tr>
						<!--<tr style="font-size: 7px;">
							<td colspan="3" style="width:70%;margin-bottom:1px !important;"><strong><?=$set_head['address']?></strong>
							</td>
						</tr>
						<tr style="font-size: 5px;line-height: 5px;white-space:nowrap;">
							<td style="width:30%;font-size: 5px;"><strong>D.L.No: <?=$company_config['smpl_dl_no']?></strong></td>
							<td colspan="3" style="width:70%;font-size: 5px;"><strong>Cont. No: <?=$set_head['contact_no']?><span style="margin-left:10px;font-size: 5px;">Email : <?=$set_head['website']?></span></strong></td>
						</tr>-->
						<tr style="font-size: 10px;line-height: 25px;">
							<td colspan="4">&nbsp;</td>
						</tr>
					</table>
					<?php 
						if($i>1){ echo '<hr style="margin-top:4px;margin-bottom:4px;"/>';}
				//	}
					
				$x++;
				}
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

	 	<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/label_printing_list.js?<?=time()?>"></script>
		 <script type="text/javascript" src="<?=ROOT?>js/jquery-barcode.js"></script>
		
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
 function generateBarcode(){
        var value = $("#delegateid").val();
        var btype = "code39";
        var renderer = "css";
        console.log(value);
		console.log(btype);
		console.log(renderer);
		
        var settings = {
          output:renderer,
          bgColor: "#FFFFFF",
          color: "#000000",
          barWidth: "2",
          barHeight: "40",
          addQuietZone: 1
        };
		console.log(settings);
          $("#barcodeTarget").html("").show().barcode(value, btype, settings);
      }
      $(function(){
        generateBarcode();
      });
</script>
	</body>
</html>
