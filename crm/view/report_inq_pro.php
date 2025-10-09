<?php 
session_start();
include('../include/urlfile.php');
// $incPath = $path.'include/';
$form="Inquiry Product Report";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
$start=date('1-m-Y');
$end=date("d-m-Y");
$countryid = '101';

$companyConfiguration=getCompanyConfiguration($dbcon);
$crm_user_type = $companyConfiguration['crm_user_type'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>INQUIRY PRODUCT</title>
	<?php include_once('../../include/include_css_file.php');?>
</head>
<body>
	<section id="container">
		<?php include_once('../../include/include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once('../../include/left_menu.php');?>
		<!--sidebar end-->
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3 style=""><?=$form?> </h3>
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
								<div class="col-md-5">
									<div class="form-group">
										<label class="control-label col-md-4">Choose Date</label>
										<div class="col-md-7">
											<div class="input-group date form_datetime-component">
												<input type="hidden" id="from_date" value="<?=$start?>">
												<input type="hidden" id="to_date" value="<?=$end?>">
												<input type="text" id="rep_date" onChange="generate_chart_report();" class="form-control datepikerdemo" value="">
												<span class="input-group-btn">
													<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-5">
									<div class="form-group">
										<label class="control-label col-md-4">Territory</label>
										<div class="col-md-7">
											<select class="select2" id="t_id" name="t_id" onChange="generate_chart_report()">
												<?=get_all_territory($dbcon,"");?>
											</select>
										</div>
									</div>
								</div>
								<span class="tools pull-right"> 
									<a href="javascript:;" onClick="tableToExcel('adv-table', '<?=$form?>')" ><button class="btn btn-primary btn-flat" >Export Excel</button></a>	
								</span> 
								<div class="clearfix"></div>
								<div class="col-md-12" style="padding-top:10px;"></div>
								<div class="col-md-5">
									<div class="form-group">
										<label class="control-label col-md-4">User</label>
										<div class="col-md-7">
											<select class="select2" id="user_id" name="user_id" onChange="generate_chart_report()">
												<option value="">Choose User</option>
												<?=get_assign_users($dbcon,""," and user_type IN (".$crm_user_type.")");?>
											</select>
										</div>
									</div>
								</div>
								


								<div class="col-md-5">
									<div class="form-group">
										<label class="control-label col-md-4">Product</label>
										<div class="col-md-7">
											<input id="product_id" name="product_id" style="width:100%;" placeholder="Select Product" onchange="generate_chart_report();"/>
											<!-- <select class="select2" id="product_id" name="product_id[]" onchange="generate_chart_report()" placeholder="Choose Products" multiple="multiple">
												<?//=getproduct($dbcon,"");?>
											</select> -->
										</div>
									</div>
								</div>

								<div class="col-md-5" style="margin-top: 10px;">	
									<div class="form-group">
										<label class="control-label col-md-4">Country</label>
										<div class="col-md-7">
											<select class="select2" name="c_add_country" id="c_add_country" onChange="load_state(this.value,'c_add_state','');generate_chart_report()">
												<?=get_country($dbcon,$countryid)?>				
											</select>
										</div>
									</div>
								</div>
						
						
								<div class="col-md-5" style="margin-top: 10px;">
									<div class="form-group">
										<label class="control-label col-md-4">State</label>
										<div class="col-md-7">
											<select class="select2" name="c_add_state" id="c_add_state" onChange="load_city(this.value,'c_add_city','');generate_chart_report()">
												<option value="">Select State</option>	
												<?//=getstate($dbcon,$rel['stateid'])?>				
											</select>
										</div>
									</div>
								</div>

								<div class="col-md-5" style="margin-top: 10px;">
									<div class="form-group">
										<label class="control-label col-md-4">City</label>
										<div class="col-md-7">
											<select class="select2" name="c_add_city" id="c_add_city" onchange="generate_chart_report()">
												<option value="">Select City</option>	
											</select>
										</div>
									</div>
								</div>

								<span class="tools pull-right">
									<button class="btn btn-warning btn-flat" onClick="PrintMe('adv-table');"><i class="fa fa-print"></i> Print Report</button>
								</span>	
								<div class="clearfix"></div>
								<div class="col-md-12" style="padding-top:10px;"></div>
							</header>	
							<div class="clearfix"></div>
							<div class="row">
								<div class="col-md-12 margin_row">
									<div class="col-md-2">
										<button class="btn btn-dark btn-flat" onClick="clear_lead_by_source_report();" style="margin-right:20px;"><i class="fa fa-remove"></i> Clear Chart</button>
									</div>
									<div class="col-md-8">
										<div id="report_task_act_byowner" style="width: 900px; height: 400px;"></div>
									</div>
									<div class="col-md-2">
									</div>
								</div>
							</div>
							<div class="panel-body">
								<div class="adv-table" id="adv-table" style="overflow-x: scroll;">
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
		<?php
		include_once('../include/footer.php');
		?>
		<!--footer end-->
	</section>

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php');?>   
	<script src="<?=ROOT.CRM_ROOT?>js/app/report_inq_pro.js?<?=time()?>"></script> 
	<?php include_once('../../include/include_report_js_file.php');?>   
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

	<?
		echo "<script>load_state(".$countryid.",'c_add_state',".$stateid.")</script>";
	?>
</body>
</html>
