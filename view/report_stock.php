<?php 

	session_start();
	
	include_once("../config/config.php");
	include_once("../config/session.php");
	
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Product Stock Report";
	$userid=$_SESSION['user_id'];
	
	$emp_id=getEmployeeIdUser($dbcon,$userid);
	$usertype=$_SESSION['user_type'];
	$getspecialConfiguration=getspecialConfiguration($dbcon);
	// echo $usertype;
	// error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>REPORT STOCK</title>
		<?php include_once('../include/include_css_file.php');?>
	</head>
	<body>
		<section id="container" >
			<?php include_once('../include/include_top_menu.php');?>
			<?php include_once('../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3 style="float:left;"><?=$form?></h3><br>
										<!-- <php include_once('../include/reporthead_menu.php');?> -->
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
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									<span class="tools pull-right">
										<a href="javascript:;"><button onClick="exportCsv()" class="btn btn-info btn-flat" >Export Excel</button></a>	
									</span>
									<span class="tools pull-right">
										<button class="btn btn-warning btn-flat" onClick="PrintMe('adv-table');" style="margin-right:20px;"><i class="fa fa-print"></i> Print Report</button>
									</span>	
									<?=$form?>
								</header>				
								<div class="panel-body">
									<div class="row">
										<div class="col-md-12">
											<div class="col-md-4" >
												
                                                    <div class="form-group">
                                                        <label for="product_type" class="col-md-4 control-label  text-right">Product Type*</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <select class="select2" id="product_type" name="product_type" onchange="generate_report_stock();product_load(this.value);">
                                                                <?php echo get_product_type_company($dbcon, '', ''); ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                          
											</div>
											 <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="product_category" class="col-md-4 control-label">Product Category</label>
                                                        <div class="col-md-8 col-xs-11">
                                                            <select class="select2" name="product_category" id="product_category" onchange="generate_report_stock();">
                                                                <?= get_all_category($dbcon, ''); ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

											<div class="col-md-4" >
												
                                                    <div class="form-group">
                                                        <label for="product_type" class="col-md-3 control-label text-right">Product*</label>
                                                        <div class="col-md-9 col-xs-11">
												<!-- <select  class="select2" name="product_id" id="product_id" onChange="generate_report_stock();" style="width:300px !important" readonly>
													<option value="">--Select Product--</option>
													
												</select>  -->
													<input id="product_id" name="product_id"  style="width:100% !important"  placeholder="Select product" onchange="generate_report_stock();" />
													</div>
											
										</div>
									</div>
									<div class="row mtop20">
										<div class="col-md-12 mtop20">
											<div class="adv-table" id="adv-table">
												<table  class="display table table-bordered table-striped" id="stock-table">
													<thead>
														<tr>
															<th>Sr. NO.</th>
															<th>Product Name</th>
															<?php if($getspecialConfiguration['fusiontech_permission'] == '1'){
																	echo '<th>Godown Details</th>';
																} ?>
															<!-- <th>Process Details</th>
															<th>Godown Details</th> -->
															<th>Stock</th>	  
															<th>Reserve Stock</th>	  
															<th>Free Stock</th>
															<?php if($getspecialConfiguration['rb_auto_permission']!=1){?>
																<th>Rate</th>	  
															<?php } ?>
															<th>Customer Stock</th>	  
															<th>Action</th>	  
														</tr>
													</thead>
													<tbody></tbody>				 
												</table>
											</div>
										</div>
									</div>
								</div>	
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once('../include/footer.php');?>
		</section>
		<?php include_once('../include/include_js_file.php');?>   
		<script src="<?=ROOT?>js/app/stock_report.js?<?=time()?>"></script>
		<script src="<?=ROOT?>js/advanced-form-components.js"></script>
		<script>
			$(".select2").select2({
					width: '100%'
				});
				
		/*	$('#product_id').select2({
				
				minimumInputLength: 2,
			});*/

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
