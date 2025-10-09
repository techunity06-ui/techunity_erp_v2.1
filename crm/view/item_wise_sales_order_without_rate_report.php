<?php 
session_start();

include('../include/urlfile.php');	

$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$form="Item Wise Sales Order Without Rate Details";

// $bulkAccessArray = canCheckPermissionAccess($dbcon, [
//         VENDOR_ANALYSIS_REPORT_VIEW
// ]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>ITEM WISE WITHOUT RATE SALES ORDER DETAIL</title>
	<?php include_once($include.'/include_css_file.php');?>
</head>
<body>
	<section id="container" >
		<?php include_once($include.'/include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once($include.'/left_menu.php');?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">

				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3 style="float:left;"><?=$form?></h3><br>
								<?php include_once($include.'/reporthead_menu.php');?>

							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.CRM_ROOT.'crm_report_list'?>"> CRM Report List</a></li>
									<li><?=$form?></li>
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
							</header>				
							<div class="panel-body">
								<div class="row">
									

									<div class="item-so-detail-table" id="item-so-detail-table">
											
									</div>
								</div>

							</div>	

						</section>
					</div>
				</div>
				<!--state overview end-->

			</section>

		</section>
		<!--main content end-->
		<!--footer start-->
		<?php include_once($include.'/footer.php');?>
		<!--footer end-->
	</section>

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'/include_js_file.php');?>   
	
	<!-- <script src=""></script> -->
	<!-- <script src=""></script> -->
	<script src="<?=ROOT.CRM_ROOT?>js/app/item_wise_sales_order_without_rate_report.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
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
	<script>
		var tableToExcel = (function() {
			var uri = 'data:application/vnd.ms-excel;base64,'
			, template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
			, base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
			, format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
			return function(table, name) {
				if (!table.nodeType) table = document.getElementById(table)
					var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
				window.location.href = uri + base64(format(template, ctx))
			}
		})()

		function PrintMe(DivID) {
			//generate_report_product_service_data();

			$('#logo').css('display','');

			var disp_setting="toolbar=yes,location=no,";
			var content_vlue=$('#report_head').show();
			disp_setting+="directories=yes,menubar=yes,";
			disp_setting+="scrollbars=yes,width=800, height=600, left=100, top=25";

			content_vlue= document.getElementById(DivID).innerHTML;
			var docprint=window.open("","",disp_setting);
			docprint.document.open();
			docprint.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"');
			docprint.document.write('"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
			docprint.document.write('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">');
			docprint.document.write('<head><title><?=TITLE?></title>');
			docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/style.css" media="all"/>');
			docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/bootstrap.min.css" media="all"/>');

			docprint.document.write('<style type="text/css">body { margin:20px 10px 10px 35px;');
			docprint.document.write('font-family:Tahoma;color:#000;');
			docprint.document.write('font-family:Tahoma,Verdana; font-size:10px;} .dataTables_length, .dataTables_filter , .dataTables_paginate { display:none; }');
			docprint.document.write('#mainpart table,#mainpart tr,#mainpart td,#mainpart th {border:1px #eee solid;padding:2px 5px 2px 5px;text-align:center;}');
			docprint.document.write('a{color:#000;text-decoration:none;} h1 {font-size:25px; line-height:5px;} b { font-weight:normal; } div.page { page-break-after: always; page-break-inside: avoid; } </style>');
			docprint.document.write('</head><body onLoad="self.print()"><center>');
			docprint.document.write(content_vlue);
			docprint.document.write('</center></body></html>');
			docprint.document.close();
			$('#report_head').hide()
			docprint.focus();
			$('#logo').css('display','none');
		}
	</script>
</body>
</html>
