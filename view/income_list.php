<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Income";
	$info = pathinfo( __FILE__ );
	$_SESSION['page']=$info['filename'];
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
						<h3><span class="english"><?=$form?> List</span>
							</h3>
						</header>
						<div class="">
						   <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i>
					<span class="english"> Home</span>
							</a></li>
							  <li>
								 <a href="#"><span class="english"><?=$form?> List</span>
							</a>
							  </li>
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
					 <div class='col-lg-5 col-md-7 col-xs-9'>
					<div class="form-group">
                                  <label class="control-label col-lg-4 col-md-4 col-xs-3"><span class="english">Choose Date</span></label>
                                  <div class=" col-lg-8 col-md-8 col-xs-9">
                                       <div class="input-group date form_datetime-component">
                                        <input type="hidden" id="from_date"  value="<?=$start?>">
										 <input type="hidden" id="to_date"  value="<?=$end?>">
         					 		        <input type="text" id="rep_date"  onChange="reload_data();;" class="form-control datepikerdemo" value="">
											<span class="input-group-btn">
											<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
											</span>
                                      </div>
                                  </div>
                              </div>
						</div>	
						
					<span class="tools pull-right">
					<a href="javascript:;" onClick="tableToExcel('dynamic-table', 'Instalment Collection')" ><button class="btn btn-info btn-flat" ><span class="english">Export Excel</span></button></a>	
					<a href="<?=ROOT.'income-entry'?>" ><button class="btn btn-success btn-flat" ><span class="english">Create <?=$form?></span></button></a>					
				 </span>
				 <div class="col-md-12"	style="height:10px;" ></div>
			
					</header>	
					 <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
				  <thead>
				  <tr>
					<th><span class="english">Invoice  No </span></th>
					<th><span class="english">Customer</span></th>
					<th><span class="english">Income Date</span></th>
					<th><span class="english">Amount</span></th>
					<th class="hidden-phone"><span class="english">Action</span></th>					  
				  </tr>
				  </thead>
				  <tbody>
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
    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
   <script src="js/app/income_entry.js?<?=time()?>"></script>
    <!--<script src="js/count.js"></script>-->
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
</script>
  </body>
</html>
