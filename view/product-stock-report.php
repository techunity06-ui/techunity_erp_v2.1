<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Product Stock Report";
	//error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
</head>
<body onLoad="generate_report();">
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
						  <h3><?=$form?> List</h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li class="active"><?=$form?></li>
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
				  <header class="panel-heading" style="padding-bottom:28px;">
					  <?=$form?> List
					  <div class="row" style="margin-top:20px;">
						<div class="col-md-4"> 
						<div class="form-group">
                                  <label class="control-label col-md-5">Choose Date</label>
                                  <div class="col-md-7">
                                       <div class="input-group date form_datetime-component">
										<?
									//  $start=(date('m')<'04') ? date('01-04-Y',strtotime('-1 year')) : date('01-04-Y');
									$start = date('01-m-Y')
									 ?>
                                        <input type="hidden" id="from_date"  value="<?=date('d-m-Y')?>">
										 <input type="hidden" id="to_date"  value="<?=date('d-m-Y')?>">
         					 		        <input type="text" id="rep_date"  onChange="generate_report();" class="form-control default-date-picker" value="<?=date('d-m-Y')?>">
											<span class="input-group-btn">
											<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
											</span>
                                      </div>
                                  </div>
                              </div>
							  </div>
							  <div class="col-md-5">
								<div class="col-md-3" style="text-align:left;">Product </div>
								<div class="col-md-9" >
									<select class="select2" name="material_id" id="material_id" onChange="generate_report();">
										<?=getproduct($dbcon,0,'0');?>	
									</select>
								</div>
							  </div>
					<span class="tools pull-right">
					<button class="btn btn-info btn-flat" onClick="tableToExcel('data_list', 'Candidate')" ><i class="fa fa-file"></i> Export CSV</button> <button class="btn btn-warning btn-flat" onClick="PrintMe('adv-table');" style="margin-right:20px;"><i class="fa fa-print"></i> Print Report</button>											
				 </span>	
					</div>
					
				  </header>
				  <div class="panel-body">
				  <div class="adv-table" id="adv-table">
				  <table  class="display table table-bordered table-striped" id="data_list" >
				  
				  <thead>
				  <tr>
					<td colspan="3"><b>Stock Report</b></td>
					<td style="text-align:right" colspan="3"> Date : <span id="report_date"></span></td>
				  </tr>
				  <tr>
					  <th width="10%" style="text-align:center;">Sr. NO.</th>
					  <th width="54%" style="text-align:center;">Product Name</th>
					  <th width="12%" style="text-align:center;">Opening Stock</th>					  
					  <th width="12%" style="text-align:center;">Minumum Stock</th>					  
					  <th width="12%" style="text-align:center;">Close Stock</th>
					  <!--<th width="12%" style="text-align:center;">Stock Valuation</th>-->
				  </tr>
				  </thead>
				  <tbody id="datatable">
				  </tbody>				 
				  </table>
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
<!-- Modal -->
<!-- /.modal -->
 
    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
	<!--<script src="<?=ROOT?>js/app/product_stock_report.js?<?=time()?>"></script>-->
<script>
$(".select2").select2({
		width: '100%'
	});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});

$('.date-set').click(function(){
       $('.default-date-picker').trigger('click')
});
function generate_report() 
{
	Loading(true);
	var date=$('#rep_date').val()
	var material_id=$('#material_id').val()
	$('#report_date').html(date);
	$.ajax({
		type: "POST",
		url: root_domain+'app/productstockreport/',
		data: { mode : "generate_report", date:date,material_id:material_id  },
		success: function(response)
		{
			//console.log(response);
			if(response != "") {
				$('#datatable').html(response);
				Unloading();
			}
										
		}
	});	

}
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
var disp_setting="toolbar=yes,location=no,";
disp_setting+="directories=yes,menubar=yes,";
disp_setting+="scrollbars=yes,width=800, height=600, left=100, top=25";
  var content_vlue = document.getElementById(DivID).innerHTML;
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
  docprint.focus();
}
</script>
  </body>
</html>
	