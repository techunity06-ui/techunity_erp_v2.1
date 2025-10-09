<?php 
	session_start();
	include('../include/urlfile.php');	
	include "qrcode/qrlib.php"; 
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$_SESSION['contents']='';
	$form="GRN STICKER";
	$mode="Print";
	$grn_id = $dbcon->real_escape_string($_REQUEST['id']);	
	$query="select bat.*,u.unit_name from tbl_batch_data as bat 
	left join unit_mst as u on u.unitid =  bat.batch_unit
	where grn_id = '$grn_id'";
	

	
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>GRN STICKER Print</title>
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
							  <li><a href="<?=ROOT.PRODUCTION_ROOT.'bom_list'?>"><?=$form?> List</a></li>
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
					<select class="form-control" name="print_status" id="print_status" <?php if($_REQUEST['printstatus']!=''){ echo "readonly";}?>>
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

						<table width="100%" border="0" style="" id="">
							<tr>
								<td width="90%" style="text-align:center"> 
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
<table width="100%" class="maintable" style="font-size: 12px;" id="invoice_type" >
	<thead>
		<tr>
			<th colspan="8" style="padding:0px !important;">
				<table  border="0" style="font-size:12px;border-collapse:collapse;" cellpadding="0"  cellspacing="0" width="100%" id="">
					<!--<thead>-->
					
						<tr>
						<?php $res = $dbcon->query($query);
						
						$bundle = mysqli_num_rows($res);
						
						//echo "<pre>"; print_r($rel=mysqli_fetch_assoc($res));
						
						$sum = '';
						$x=1;
				$qty = 1;
				$price = "";
				$i=0;

				$year_month = date('Y-m');
						 while($rel=mysqli_fetch_assoc($res))
						{
					
						$grn_query = "select * from tbl_grn as g inner join tbl_grn_trn as gt ON g.grn_id = gt.grn_id where g.grn_id =".$rel['grn_id'];
						$grn_res=mysqli_fetch_assoc($dbcon->query($grn_query));

					//exit;
					$set="select * from tbl_company where company_id=".$grn_res['company_id'];
					$set_head=mysqli_fetch_assoc($dbcon->query($set));
					
					$vender="select * from tbl_ledger where l_id=".$grn_res['vender_id'];
					$vender_res=mysqli_fetch_assoc($dbcon->query($vender));
					
					$product="select * from product_mst where product_id=".$grn_res['product_id'];
					$product_res=mysqli_fetch_assoc($dbcon->query($product));
					
					
					
						if($rel['image_name']!=null){
					$image_name = '<img src="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$rel['image_name'].'" style="width: 60px;height: 50px;">';
					//$image_name = '<a href="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$rel["image_name"].'" target="_blank"><img src="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$rel['image_name'].'" style="width: 60px;height: 50px;"></a>';
					}else{
					$image_name = '';
					}
					$ch_inv_no = '';
					if($grn_res['challan_no'] != '' )
					{
						$ch_inv_no = $grn_res['challan_no'];
					}
					else
					{
						$ch_inv_no = $grn_res['invoice_no'];
					}
					if($grn_res['qc_status'] == 0 )
					{
						$qc_status = 'No';
					}
					else
					{
						$qc_status = 'Ok';
					}
					$month = explode("-",$grn_res['grn_date']);


					if($x == 1){ 
							$data_string=$rel['batch_no'];
							$data_string=strtoupper($data_string);
							//echo '<br/>';
							//QRcode Start
						   //set it to writable location, a place for temp generated PNG files
						    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
						    //html PNG location prefix
						    $PNG_WEB_DIR = '../temp/';
							   
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
						      }
					?>	
					<?php 				
							if($i==3){
								$i=0;
					?>
						</tr><tr>
					<?php 
							}
					?>
							<td width="25%" style="vertical-align:top;border:1px solid;padding:0px; colspan="2">
							
							<table width="100%" style="padding: 0px; !important;">
							<tr>
								<td colspan="2" style="text-align: center;"><?=$set_head['company_name']?> </td>
							</tr>
							<tr>
								<td colspan="2" style="text-align: center;">INCOMING RAW MATERIAL </td>
							</tr>
							<tr>
								<td colspan="2" style="text-align: center;"><?= 
								
								 "MONTH : ".date("M",$month[1])."-".$month[0];  ?> </td>
							</tr>
							<tr>
							<td width="50%" style="vertical-align:top;border:1px solid;border-left:0px;border-bottom: 1px solid #000;"><strong>Suppl Name </strong></td>
							<td width="50%" style="vertical-align:top;border:1px solid;"><?php echo $vender_res['l_name']; ?></td>
							</tr>
							<tr>
							<td width="50%" style="vertical-align:top;border:1px solid;border-left:0px;border-bottom: 1px solid #000;"><strong>CH/INV No</strong></td>
							<td width="50%" style="vertical-align:top;border:1px solid;"><?php echo $ch_inv_no; ?></td>
							</tr>
							<tr>
							<td width="50%" style="vertical-align:top;border:1px solid;border-left:0px;border-bottom: 1px solid #000;"><strong>CH/INV Date</strong></td>
							<td width="50%" style="vertical-align:top;border:1px solid;"><?php echo $grn_res['grn_date'];?></td>
							</tr>
							<tr>
							<td width="50%" style="vertical-align:top;border:1px solid;border-left:0px;border-bottom: 1px solid #000;"><strong>GRN No</strong></td>
							<td width="50%" style="vertical-align:top;border:1px solid;"><?php echo $grn_res['grn_no'];?></td>
							</tr>
							<tr>
							<td width="50%" style="vertical-align:top;border:1px solid;border-left:0px;border-bottom: 1px solid #000;"><strong>GRN Date</strong></td>
							<td width="50%" style="vertical-align:top;border:1px solid;"><?php echo $grn_res['grn_date'];?></td>
							</tr>
							
							<tr>
							<td width="50%" style="vertical-align:top;border:1px solid;border-left:0px;border-bottom: 1px solid #000;"><strong>Batch No </strong></td>
							<td width="50%" style="vertical-align:top;border:1px solid;"><?php echo $rel['batch_no'];?></td>
							</tr>
							
							<tr>
							<td width="50%" style="vertical-align:top;border:1px solid;border-left:0px;border-bottom: 1px solid #000;"><strong>Qty </strong></td>
							<td width="50%" style="vertical-align:top;border:1px solid;"><?php echo $rel['batch_qty'].' '.$rel['unit_name'];?></td>
							</tr>
							<tr>
								<td colspan="2">
									<?php 
										echo '<img style="height: 80px;" src="'.$PNG_WEB_DIR.basename($filename).'"/><br/>'; 
									?>
								</td>
							</tr>
							</table>
							</td>							
							
							
							<?php $i++; }?>
						</tr>
						
						
					
				</table>
			</th>
		</tr>
		
	</thead>
	
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
	<?php include_once($include.'footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
   <script src="<?=ROOT.PRODUCTION_ROOT?>js/app/invoice.js"></script>
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
