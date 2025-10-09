<?php 
session_start();
include('../include/urlfile.php');
$incPath = '../../include/';

// $bulkAccessArray = canCheckPermissionAccess($dbcon, [
// 	SALES_ORDER_SLUG_PRINT,
// 	SALES_ORDER_SLUG_READ
// ]);

// if(!in_array(SALES_ORDER_SLUG_PRINT,$bulkAccessArray)){
// 	header("Location: ".DOMAIN."permission_access");
// }

$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$_SESSION['contents']='';
$form="Job Work Order";
$mode="Print";
?>

<!DOCTYPE html>
<html lang="en">
<head>
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
									<li><a href="<?=ROOT.PRODUCTION_ROOT.'production_report_list'?>"> Report List</a></li>
									<li><?=$form?></li>
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
									<div class="col-sm-4"  style="padding-left:0;">
										<label class="col-md-2 control-label" style="
										padding:10px 0;">Print</label>
										<div class="col-md-10 col-xs-12">
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
									</div>
									<div class="col-sm-4" >
										<label class="control-label col-xs-2">O.A No.</label>
										<div class="col-xs-10">
											<select class="form-control" name="sales_order_id" id="sales_order_id" onchange="load_job_work_data()">
												<option value="">Select O.A No</option>
												<?php$query = $dbcon->query("SELECT sales_order_id, sales_order_no FROM tbl_sales_order WHERE sales_order_status = 0 AND order_accept_status = 1 AND company_id = '".$_SESSION['company_id']."' ORDER BY sales_order_id DESC");
												while($rel=brp_mysqli_fetch_assoc($query)){ ?>
													<option value="<?=$rel['sales_order_id'];?>"><?=$rel['sales_order_no'];?></option>
												<?php} ?>
											</select>
										</div>
									</div>
									<div class="col-sm-4 col-xs-6 resspace"  style="text-align:right">
										<input type="checkbox" class="form-control"  name="logo" id="logo" value="1" checked style="display: none;">
										<button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>
										<a href="<?=ROOT.PRODUCTION_ROOT.'production_report_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
									</div>
									
								</center>	
								<div class="col-md-12"></div>
								<label class="col-md-3 control-label"></label>
								<div class="col-lg-4"></div>
								<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
								<?php ob_start(); ?>
								<div class="col-lg-12" id="receipt_print">	
									<div class="col-md-12 breakout" style=" margin-top:10px;" id="print1">

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
<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/job_work_order.js"></script>
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
