<?php 
	session_start();

	include('../include/urlfile.php');	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	include "../../inventory/view/qrcode/qrlib.php"; 
	
	$form="Workorder Package Print";
		
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
	
$count_package = 0;
$workorder_packing_trn_id = 0;

if (is_array($_POST) && !empty($_POST)) {
    $count_package = count($_POST);
    $workorder_packing_trn_id = $_POST['workorder_packing_trn_id'];
}

$back_link = PRODUCTION_ROOT.'work_order/';

$display = "";

if(strpos($_SERVER[REQUEST_URI], "workorderpackingprint")==true)
{
	$work_order_id = $dbcon->real_escape_string($_REQUEST['id']);
	$query_inward="SELECT trn.*,sp.po_req_no,p.product_name,u.unit_name FROM tbl_workorder_packing_trn as trn 
			left join tbl_workorder_packing as wp on wp.workorder_packing_id=trn.workorder_packing_id 
			left join tbl_set_main_process as sp on sp.sp_id=trn.workorder_id 
			left join product_mst as p on p.product_id=wp.product_id 
			left join unit_mst as u on u.unitid=wp.unit_id 
			where trn.status=0 AND trn.workorder_id = ".$work_order_id ;
			$PNG_WEB_DIR = '../temp/';
}else{
		$query_inward="SELECT trn.*,sp.po_req_no,p.product_name,u.unit_name FROM tbl_workorder_packing_trn as trn 
			left join tbl_workorder_packing as wp on wp.workorder_packing_id=trn.workorder_packing_id 
			left join tbl_set_main_process as sp on sp.sp_id=trn.workorder_id 
			left join product_mst as p on p.product_id=wp.product_id 
			left join unit_mst as u on u.unitid=wp.unit_id 
			where trn.status=0 AND trn.workorder_packing_trn_id IN (".$workorder_packing_trn_id.")";
			$PNG_WEB_DIR = 'temp/';
}
		
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Workorder Package Print</title>
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
									  <li><a href="<?=ROOT.PRODUCTION_ROOT.'work_order_add'?>">Workorder List</a></li>
									  <li>Workorder Package Print</li>
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
					
			<?
				$mode="Print";
				
				$rs_inward=$dbcon->query($query_inward);
				$x=1;
				$qty = 1;
				$price = "";


				$year_month = date('Y-m');

				while($rel=mysqli_fetch_assoc($rs_inward)){
					
						
	$data_string=$rel['batch_no'];
	$data_string=strtoupper($data_string);
	//echo '<br/>';
	//QRcode Start
   //set it to writable location, a place for temp generated PNG files
     $PNG_TEMP_DIR = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    //html PNG location prefix
    
	
    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);
    $filename = $PNG_TEMP_DIR.'test.png';
    //processing form input
    //remember to sanitize user input in real-life solution !!!
    $errorCorrectionLevel = 'L';
	$matrixPointSize = 1;
	$filename = $PNG_TEMP_DIR.'test'.md5($data_string.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
	// echo $PNG_TEMP_DIR;
        QRcode::png($data_string, $filename, $errorCorrectionLevel, $matrixPointSize, 2); 
	
 ?>
						
					<table width="100%" style="margin-top:5px;font-size:10px;
					border-collapse:separate;font-weight:bold;" id="packing<?=$x?>" >
					<tr style="font-size: 8px;">
							<td rowspan="6" style="width:30%;">
								<?
								echo '<img style="height: 80px;" src="'.$PNG_WEB_DIR.basename($filename).'"/><br/>'; 
								?>
							</td>
						</tr>
					<tr>
							<td style="font-size:12px;"><strong>Product Name : <span style="margin-left:5px"><?=$rel['product_name']?></span></strong></td>
							</tr>
					<tr>
							<td style="font-size:12px;"><strong>Workorder No : <span style="margin-left:5px"><?=$rel['po_req_no']?></span></strong></td>
							</tr>
					<tr>
							<td style="font-size:12px;"><strong>Batch No : <span style="margin-left:5px"><?=$rel['batch_no']?></span></strong></td>
							</tr>
					<tr>
							<td style="font-size:12px;"><strong>Quantity : <span style="margin-left:5px"><?=$rel['total_box_qty'] . ' ' .$rel['unit_name']?></span></strong></td>
							</tr>
					<tr>
							<td style="font-size:12px;"><strong>Remark : <span style="margin-left:5px"><?=$rel['remark'] ?></span></strong></td>
						</tr>
						
					</table>
					<?
						 echo '<hr style="margin-top:4px;margin-bottom:4px;"/>';
					//}
					
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
