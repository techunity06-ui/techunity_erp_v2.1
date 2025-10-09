<?php 

session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$form="Stock Report";
if(empty($_SESSION['start']))
{
	$start=date('1-m-Y');
	$end=date("d-m-Y");
}
else
{
	$start=$_SESSION['start'];
	$end=$_SESSION['end'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>STOCK REPORT</title>
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
									<li ><a href="<?=ROOT.'report'?>">Report</a></li>

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
											<div class="col-md-3" style="margin-left: 15px; margin-top: 30px;">
												<div class='external-event label label-success  ui-draggable' style='position: relative;width:135px;'>Item Wise Detail
												</div>			
												<input id="task_status_all" name="report_type" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" title="All" value="itemwisedetail" checked="" onchange="stock_report_filter()">
											</div>

												<div class="col-md-5" style="display:none">
													<div class='external-event label label-warning ui-draggable' style='position: relative;width:125px; margin-top: 30px;'>Item Wise Summary
													</div>	
													<input id="task_status_all" name="report_type" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" title="All" value="itemwisesummary" >			
												</div>

											<div class="col-md-3" style="margin-left: 15px;margin-top: 30px;margin-bottom: 30px;">
												<div class='external-event label label-warning ui-draggable' style='position: relative;width:135px;'>Item Category Wise </div>				
												<input id="task_status_all" name="report_type" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" title="All" value="catgroupwise" onchange="stock_report_filter()">
											</div>

											<div class="col-md-3" style="margin-left:15px;margin-top: 30px; margin-bottom: 30px;">
												<div class='external-event label label-primary ui-draggable' style='position: relative;width:135px;'>Item Type Wise</div>
												<input id="task_status_all" name="report_type" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" title="All" value="typewise" onchange="stock_report_filter()">
											</div>

												<div class="col-md-5" style="display:none">
													<div class='external-event label label-success ui-draggable' style='position: relative;width:125px;'>Stock Anaylysis Detail</div>				<input id="task_status_all" name="report_type" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" title="All" value="stckanalysisdetail" >
												</div>

												<div class="col-md-6" style="margin-left: 15px;display: none;">
													<div class='external-event label label-success ui-draggable' style='position: relative;width:135px;'>Stock Anaylysis Summary</div>				<input id="task_status_all" name="report_type" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" title="All" value="stckanalysissummary">
												</div>

												<div class="col-md-5" style="display:none">
													<div class='external-event label label-warning ui-draggable' style='position: relative;width:125px;'>ABC Anaylysis</div>	
													<input id="task_status_all" name="report_type" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" title="All" value="abcanalysis" >
												</div>
									</div>
									<div class="col-md-12">
										<div class="col-md-6">
											
												<div class="col-md-12" style="display:none">
													<label>
														<div class='external-event label label-success ui-draggable' style='position: relative;width:90px;'>With Conv</div>					
														<input id="task_status_all" name="report_wise" type="radio" style="width:20px;height:20px;vertical-align:middle"  class="" title="All" value="purchasetypewisepurchasebillreport">
													</label>
												</div>

												<div class="col-md-12">
													<div class="col-md-4">
														<label>
															<div class='external-event label label-primary ui-draggable' style='position: relative;width:90px;margin-left: -15px;'>Stock Value
															</div>	
														</label>
													</div>
													<div class="col-md-8">
														<select class="form-control" id="stock_value">
															<option value="">-Select Stock Option</option>
															<option value="0">Min</option>
															<option value="1">Max</option>
															<option value="3">LIFO</option>
															<option value="4">Actual</option>
															<option value="2">Average</option>

														</select>				
													</div>
												</div>	
												<div class="col-md-12 itemwisefil">
													<div class="col-md-4">
														<div class='external-event label label-success ui-draggable' style='position: relative;width:90px;margin-left: -15px;'>Item</div>
													</div>
													<div class="col-md-8" style="margin-top: 10px;">
														<!-- <select  class="select2" name="item_id" id="item_id" >
														<option value="">--Select Item--</option>
															<?php 
																load_all_product($dbcon,'')
															?>
														</select> -->
														<input id="product_id" name="product_id"  style="width:100% !important"  placeholder="Select product"/>
													</div>
												</div>

												<div class="col-md-12 typewisefil">
													<div class="col-md-4">
														<div class='external-event label label-primary ui-draggable' style='position: relative;width:90px;margin-left: -15px;'>Item Type 
														</div>	
													</div>
													<div class="col-md-8" style="    margin-top: 10px;">
														<select class="select2" id="product_type" name="product_type[]" onchange="" multiple="">
															<?=get_product_type_company($dbcon,'')?>
														</select>
													</div>

												</div>
												

												<div class="col-md-12 catgroupwisefil">
													<div class="col-md-4">
														<div class='external-event label label-warning ui-draggable' style='position: relative;width:90px;margin-left: -15px;'>Item Category 
														</div>	
													</div>
													<div class="col-md-8" style="    margin-top: 10px;">
														<select class="select2" name="product_category[]" id="product_category" multiple="">
															<?=get_all_category($dbcon,$rel['product_category']);?>
														</select>
													</div>
												</div>


											</div>
											<div class='col-lg-5 col-md-7 col-xs-9'>
				                              <div class="form-group">
				                                 <label class="control-label col-lg-4 col-md-4 col-xs-3">Choose Date</label>
				                                 <div class=" col-lg-8 col-md-8 col-xs-9">
				                                    <div class="input-group date form_datetime-component">
				                                       <?php 
				                                          //$start=(date('m')<'04') ? date('01-04-Y',strtotime('-1 year')) : date('01-04-Y');
				                                          ?>
				                                       <input type="hidden" id="from_date" value="<?=$start?>">
				                                       <input type="hidden" id="to_date" value="<?=$end?>">
				                                       <input type="text" id="rep_date" class="form-control datepikerdemo" value="">
				                                       <span class="input-group-btn">
				                                       <button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
				                                       </span>
				                                    </div>
				                                 </div>
				                              </div>
				                           </div>
											<!-- <div class="col-md-6">

												<div class="col-md-6">
													<div class="col-md-2">
														<label>From Date</label>
													</div>

													<div class="col-md-10">
														<div class='input-group date datepicker fromdate' id='datetimepicker5'>
															<input type='text' class="form-control fromdate" id="fromDate"/>
															<span class="input-group-addon">
																<span class="glyphicon glyphicon-calendar"></span>
															</span>
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="col-md-2">
														<label>To Date</label>
													</div>
													<div class="col-md-10">
														<div class='input-group date datepicker todate' id='datetimepicker5'>
															<input type='text' class="form-control" id="toDate"/>
															<span class="input-group-addon">
																<span class="glyphicon glyphicon-calendar"></span>
															</span>
														</div>

													</div>
												</div>
											</div> -->

										</div>
										<input type="hidden" name="inquiry_type" id="inquiry_type" value="1">
										<button id="preview" class="btn btn-success" onclick="generate_stock_report()" style="margin-left:40%;margin-top: 15px;">Preview</button>

									</div>

									<div class="adv-table" id="adv-table" style="margin-top:120px;">

									</div>
								</div>

							</div>	

						</section>
					</div>
				</div>
			</section>

		</section>
		<!--main content end-->
		<!--footer start-->
		<?php include_once('../include/footer.php');?>
		<!--footer end-->
	</section>
	<?php include_once('../include/include_js_file.php');?>   
	<script src="<?=ROOT?>js/app/stockreport.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});
	</script>
	<script>

		 $('.default-date-picker').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		 });
		 
         function cb(start, end) {
               $('.datepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
           }
           cb(moment().subtract(29, 'days'), moment());
         
         
           $('.datepikerdemo').daterangepicker({       
         		locale: {
         		format: 'DD-MM-YYYY'
         	},
          "autoApply": true,	
         "startDate": $('#from_date').val(),
         "endDate": $('#to_date').val(),	
            ranges: {
                  'Today': [moment(), moment()],
                  'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                  'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                  'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                  'This Month': [moment().startOf('month'), moment().endOf('month')],
                  'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
               }
           }, cb);
         $('.date-set').click(function(){
              $('.datepikerdemo').trigger('click')
         });

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
			generate_stock_report();

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
