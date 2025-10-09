<?php 
	session_start();
	include('../include/urlfile.php');
	$incPath = $path.'include/';

	$form = "Service Ledger Detail";
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page'] = $infopage['filename'];

	$userid = $_SESSION['user_id'];
	$usertype = $_SESSION['user_type'];

	$id = isset($_GET['id']) ? $_GET['id'] : '0';
	// $vendorName = get_vender_name($dbcon, $id);
	$cityQuery="select l.l_id,l.l_name,c.city_name from tbl_ledger as l left join city_mst as c on c.cityid=l.cityid where l.l_id=".$id;
	$cityRow = brp_mysqli_fetch_assoc($dbcon->query($cityQuery));
	$vendorName = $cityRow['l_name'].' ('.$cityRow['city_name'].')';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once($incPath.'include_css_file.php');?>
<style>
.datepicker td.disabled.day {
    color: #ccc;
}
</style>
</head>
<body onload="report_ledger_detail()">
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
						 	<h3 style="float:left;"><?=$form?></h3><br>
						 	<?php include_once($incPath.'reporthead_menu.php');?>
						</header>	
						<div class="">
							<ul class="breadcrumb">
								<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
								<li><a href="<?=ROOT.SERVICE_ROOT.'service_ledger_report'?>">Service Ledger Report</a></li>
								<li><?php echo $vendorName; ?></li>
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
							<span class="tools pull-right">
								<a href="javascript:;" onClick="tableToExcel('adv-table', 'Installment Collection')" ><button class="btn btn-success btn-flat" >Export Excel</button></a>	
							</span>
							<span class="tools pull-right">
								<button class="btn btn-warning btn-flat" onClick="PrintMe('adv-table');" style="margin-right:20px;"><i class="fa fa-print"></i> Print Report</button>											
							</span>	
							<span class="tools pull-right">
								<div class="col-md-2" style="line-height: 34px; padding-right: 0;"><strong>Start Date</strong></div>
								<div class="col-md-4" style="padding-left: 0;">
									<input type="text" autocomplete="off" id="start_date" value="<?php echo date('01-m-Y'); ?>" class="form-control show-date-picker" />
								</div>
								<div class="col-md-2" style="line-height: 34px; padding-right: 0;"><strong>End Date</strong></div>
								<div class="col-md-4" style="padding-left: 0;">
									<input type="text" autocomplete="off" id="end_date" value="<?php echo date('d-m-Y'); ?>" class="form-control show-date-picker" />
								</div>
							</span>	
							<?=$form?>
						</header>				
						<div class="panel-body">
							<input type="hidden" name="l_id" id="l_id" value="<?php echo $id; ?>" />
							<div class="row">
								<div class="adv-table" id="adv-table" style="margin-top:10px;">
									<table class="table table-bordered table-stripped">
										<thead>
											<tr>
												<th>#</th> 
												<th>Date</th>
												<th>Description</th>
												<th>Credit</th>
												<th>Debit</th>
												<th>Balance</th>
											</tr>
										</thead>
										<tbody id="data_table"></tbody>
									</table>
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
	<?php include_once($incPath.'footer.php');?>
    <!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($incPath.'include_js_file.php');?>   
<script src="<?=ROOT?><?php echo SERVICE_ROOT; ?>js/app/service_ledger.js?<?=time()?>"></script>
<script type="text/javascript">
	$(".select2").select2({
		width: '100%'
	});
	$('.default-date-picker').datepicker({
		format: 'dd-mm-yyyy',
		todayHighlight: true,
		autoclose: true
	});
</script>
<script type="text/javascript">
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
