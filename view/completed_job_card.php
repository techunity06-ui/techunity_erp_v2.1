<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Pending Job Card";
	$type=$dbcon->real_escape_string($_REQUEST['id']);
	//echo $type;
	if(empty($_SESSION['start']))
	{
		$start = date('1-m-Y');
		$end = date("d-m-Y");
	}
	else
	{
		$start = $_SESSION['start'];
		$end = $_SESSION['end'];
	}
	//echo $_SESSION['company_id'];
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
							
							<input type="hidden" class="form-control" name="st_type" id="st_type" value="<?=$type;?>" />
							
							<h3><?=$form?> List</h3>
							
						</header>
						<div class="">
							<ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li class="active"><?=$form?> List</li>
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
				
					 <div class="panel-body">
					<div class="col-md-12" style="margin-top: 10px;margin-bottom: 10px;">
					<div class="col-md-6">
						<div class="col-md-4">
							<Strong>Product</strong>
						</div>
						<div class="col-md-6">
							<select class="select2" title="Select product" name="product_id" id="product_id" onChange="show_data();">
								<?=getproduct($dbcon,0,'0,1,2,3,4,5')?>
							</select>
						</div>
					</div>
					<div class="col-md-6">
						<div class="col-md-4">
							<Strong>Vender</strong>
						</div>
						<div class="col-md-6">
							<select class="select2" name="vender_id" id="vender_id" title="Select Vender" onChange="show_data();">
								<?=getcust($dbcon,$vender_id);?>	
							</select>
						</div>
					</div>
				 </div>
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
				  <thead>
					  <tr>
						  <th>#</th>
						  <th>Product Name</th>
						  <th>Vender Name</th>
						  <th>Jobcard Qty</th>
						 <!-- <th>Completed Qty</th>-->
						  <th>Pending Qty</th>
						  <th>User Name</th>
						  <th>Add GRN</th>
					  </tr>
				  </thead>
				  <tbody id="table_data">
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
	<?php include_once('../include/show_mrn_list.php');?>
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>
    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
   <script src="<?php echo ROOT; ?>js/app/completed_job_card.js"></script>
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
