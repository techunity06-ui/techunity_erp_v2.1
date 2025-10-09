<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");

include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$form="Purchase Bill Summary Report";

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
        PURCHASE_BILL_SUMMARY_REPORT_VIEW
]);
if(!in_array(PURCHASE_BILL_SUMMARY_REPORT_VIEW,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../include/include_css_file.php');?>
</head>
<body>
	<section id="container" >
		<?php include_once('../include/include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once('../include/left_menu.php');?>
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
								<?php include_once('../include/reporthead_menu.php');?>

							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li ><a href="<?=ROOT.'report'?>">Report List</a></li>

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
									<a href="javascript:;" onClick="tableToExcel('adv-table', 'Instalment Collection')" ><button class="btn btn-success btn-flat" >Export Excel</button></a>	
								</span>
								<span class="tools pull-right">
									<button class="btn btn-warning btn-flat" onClick="PrintMe('adv-table');" style="margin-right:20px;"><i class="fa fa-print"></i> Print Report</button>											
								</span>	
								<?=$form?>
							</header>				
							<div class="panel-body">
								<div class="row">
									<div class="col-md-12">

										<div class="col-md-3">
											<div class="col-md-12">
												<label>
													<div class='external-event label label-primary ui-draggable' style='position: relative;width:120px;'>Vendor Wise</div>				<input id="task_status_all" name="report_wise" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" title="All" value="vendorwisepurchasebillsummary" checked="">
												</label>
											</div>
											<div class="col-md-12">
												<label>
													<div class='external-event label label-warning  ui-draggable' style='position: relative;width:120px;'>Item Wise</div>					
													<input id="task_status_all" name="report_wise" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" title="All" value="itemwisepurchasebillreport">
												</label>
											</div>
											<div class="col-md-12">
												<label>
													<div class='external-event label label-success ui-draggable' style='position: relative;width:120px;'>Bill No Wise</div>					
													<input id="task_status_all" name="report_wise" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" title="Bill No Wise" value="billnowisepurchasebillreport">
												</label>
											</div>
											<div class="col-md-12">
												<label>
													<div class='external-event label label-success ui-draggable' style='position: relative;width:120px;'>Purchase Type Wise</div>					
													<input id="task_status_all" name="report_wise" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" title="All" value="purchasetypewisepurchasebillreport">
												</label>
											</div>

											<div class="col-md-12">
												<label>
													<div class='external-event label label-primary ui-draggable' style='position: relative;width:120px;'>With Conv Qty.</div>					
													<input id="task_status_all withconv" name="withconv" type="checkbox" style="width:20px;height:20px;vertical-align:middle" title="All" value="1">
												</label>
											</div>
										</div>

										<div class="col-md-3">

											<!-- <div class="col-md-12">
												<label>
													<div class='external-event label label-primary ui-draggable' style='position: relative;width:90px;'>Bill Date</div>					
													<input id="task_status_all po_date_wise" name="po_date_wise" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" title="All" value="po" checked="">
												</label>
											</div>	 -->

											<!-- <div class="col-md-12">
												<label>
													<div class='external-event label label-success ui-draggable' style='position: relative;width:90px;'>Delivery Date</div>					
													<input id="task_status_all po_date_wise" name="po_date_wise" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_task_datatable();" class="" title="All" value="delivery">
												</label>
											</div> -->

											<div class="col-md-12">
												<label>
													<div class='external-event label label-success ui-draggable' style='position: relative;width:90px;'>Detail</div>					
													<input id="task_status_all po_date_wise" name="reporttype" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" onchange="reporttype(this.value)" title="All" value="detail" checked="">
												</label>
											</div>

											<!-- <div class="col-md-12">
												<label>
													<div class='external-event label label-success ui-draggable' style='position: relative;width:90px;'>Summary</div>				<input id="task_status_all po_date_wise" name="reporttype" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" title="Summary" onchange="reporttype(this.value)" value="summary">
												</label>
											</div> -->

											<!-- <div class="col-md-12">
												<label>
													<div class='external-event label label-success ui-draggable' style='position: relative;width:90px;'>Format 2</div>				<input id=" formattype" name="formattype" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" title="Format 2" value="format2">
												</label>
											</div> -->

											<!-- <div class="col-md-12">
												<label>
													<div class='external-event label label-success ui-draggable' style='position: relative;width:90px;'>Format 3</div>				<input id=" formattype" name="formattype" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" title="Format 3" value="format3">
												</label>
											</div> -->



										</div>
										<div class="col-md-6">

											<div class="col-md-12">
												
												<div class="col-md-6">
													<div class="input-group date form_datetime-component">
														<?
														$start=date('01-m-Y');
														?>
														<input type="hidden" id="from_po_date"  value="<?=$start?>">
														<input type="hidden" id="to_po_date"  value="<?=date('t-m-Y')?>">
														<input type="text" id="rep_po_date"  onChange="" class="form-control datepikerdemo" value="">
														<span class="input-group-btn">
															<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
														</span>
													</div>

												</div>
											</div>

											<!-- <div class="col-md-12" style="margin-top: 10px;">
												
												<div class="col-md-6">
													<div class="input-group date form_datetime-component">
														<?
														$start=date('01-m-Y');
														?>
														<input type="hidden" id="from_delivery_date"  value="<?=$start?>">
														<input type="hidden" id="to_delivery_date"  value="<?=date('t-m-Y')?>">
														<input type="text" id="rep_del_date"  onChange="" class="form-control deliverydatepikerdemo" value="">
														<span class="input-group-btn">
															<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
														</span>
													</div>

												</div>
											</div> -->

											<div class="col-md-12" style="margin-top: 15px;">
												<!-- <div class="col-md-4">
													<label>
														<div class='external-event label label-primary  ui-draggable' style='position: relative;width:90px;'>Vendor</div>					
														<input id="task_status_all" name="specific_vendor" type="checkbox" style="width:20px;height:20px;vertical-align:middle"  class="" title="VENDOR" value="1">
													</label>
												</div> -->
												<div class="col-md-8" style="">
													<select  class="select2" name="vendor_id" id="vendor_id"  >
														<option value="">--Select Vendor--</option>
														<?=getAllVendor($dbcon,'')?>	
													</select>
												</div>
											</div>

											<div class="col-md-12" style="margin-top: 15px;">
												<!-- <div class="col-md-4">
													
													<label>
														<div class='external-event label label-primary  ui-draggable' style='position: relative;width:90px;'>Item</div>					
														<input id="task_status_all" name="specific_item" type="checkbox" style="width:20px;height:20px;vertical-align:middle"  class="" title="wo" value="1">
													</label>
												</div> -->
												<div class="col-md-8" style="">
													<select  class="select2" name="item_id" id="item_id" >
														<option value="">--Select Item--</option>
														<?=getproduct($dbcon,'')?>
														
													</select>
												</div>
												
											</div>


											<div  class="col-md-12" style="margin-top: 15px;">
												
												<!-- <div class="col-md-4">
													<label>
														<div class='external-event label label-warning  ui-draggable' style='position: relative;width:90px;'> Status</div>					
														<input id="task_status_all" name="item_status" type="checkbox" style="width:20px;height:20px;vertical-align:middle"  class="" title="wo" value="1">
													</label>
												</div> -->
												<div class="col-md-8" style="">
													<select class="select2" name="item_status_id" id="item_status_id" >
														<option value="3">-Select Status</option>
														<option value="1">Prepared</option>
														<option value="2">Completed</option>
														<option value="3">All</option>
													</select>
												</div>
												
											</div>

											<div class="col-md-12" style="margin-top: 15px;">
												
												<!-- <div class="col-md-4">
													<label>
														<div class='external-event label label-warning  ui-draggable' style='position: relative;width:90px;'>Purchase Type</div>					
														<input id="task_status_all" name="purchase_type_status" type="checkbox" style="width:20px;height:20px;vertical-align:middle"  class="" title="po" value="1">
													</label>
												</div> -->
												<div class="col-md-8" style="">
													<select class="select2" name="purchase_type_id" id="purchase_type_id" >
														<option value="">-Select Status</option>
														<option value="1">General</option>
														<option value="2">Import</option>
														<option value="">All</option>
													</select>
												</div>
											</div>
										</div>
									</div>
									<?php if(in_array(PURCHASE_BILL_SUMMARY_REPORT_VIEW,$bulkAccessArray)){ ?> 
									<button id="preview" class="btn btn-success" onclick="generate_report_purchase_bill_summary_data()" style="margin-left:40%">Preview</button>
									<?php } ?>

								</div>

								<div class="adv-table" id="adv-table" style="margin-top:120px;">

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
	<?php include_once('../include/footer.php');?>
	<!--footer end-->
</section>

<!--purchasebillsummaryreport js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   

<script src="<?=ROOT?>js/app/complaint.js?<?=time()?>"></script>
<script src="<?=ROOT?>js/app/purchasebillsummaryreport.js?<?=time()?>"></script>
<!--<script src="js/count.js"></script>-->
<script>
	$(".select2").select2({
		width: '100%'
	});
	$('.default-date-picker').datepicker({
		format: 'dd-mm-yyyy',
		autoclose: true
	});
	function cb(start, end) {
		$('.datepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
	}
	cb(moment().subtract(29, 'days'), moment());

	function cb_del(start, end) {
		$('.deliverydatepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
	}

	cb_del(moment().subtract(29, 'days'), moment());


	$('.datepikerdemo').daterangepicker({       
		locale: {
			format: 'DD-MM-YYYY'
		},
		"autoApply": true,	
		"startDate": $('#from_po_date').val(),
		"endDate": $('#to_po_date').val(),	
		ranges: {
			'Today': [moment(), moment()],
			'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
			'Last 7 Days': [moment().subtract(6, 'days'), moment()],
			'Last 30 Days': [moment().subtract(29, 'days'), moment()],
			'This Month': [moment().startOf('month'), moment().endOf('month')],
			'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		}
	}, cb);

	$('.deliverydatepikerdemo').daterangepicker({       
		locale: {
			format: 'DD-MM-YYYY'
		},
		"autoApply": true,	
		"startDate": $('#from_delivery_date').val(),
		"endDate": $('#to_delivery_date').val(),	
		ranges: {
			'Today': [moment(), moment()],
			'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
			'Last 7 Days': [moment().subtract(6, 'days'), moment()],
			'Last 30 Days': [moment().subtract(29, 'days'), moment()],
			'This Month': [moment().startOf('month'), moment().endOf('month')],
			'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		}
	}, cb_del);


	$('.date-set').click(function(){
		$('.datepikerdemo').trigger('click');
		$('.deliverydatepikerdemo').trigger('click')
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
		generate_report_product_service_data();

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
