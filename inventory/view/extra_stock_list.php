<?php 
session_start();

include('../include/urlfile.php');	

$form="Extra Stock";

$companyConfiguration=getCompanyConfiguration($dbcon);
$type_conf = $companyConfiguration['production_pro_type'];
$pro_search = $companyConfiguration['bom_pro_search'];	
$back_link = ROOT.INVENTORY_ROOT.'extra_stock_list';
$mode = "Add";
$costing_date=date('d-m-Y');
$readonly = "";

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    EXTRA_STOCK_LIST,EXTRA_STOCK_LIST
	]);

	if(!in_array(EXTRA_STOCK_LIST,$bulkAccessArray)){
        // header("Location: ".DOMAIN."permission_access");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Extra Stock LIST</title>
<?php include_once($include.'include_css_file.php');?>

</head>
<body>
  <section id="container" >
      <?php include_once($include.'include_top_menu.php');?>
      <!--sidebar start-->
      <?php include_once($include.'left_menu.php');?>
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
					<header class="panel-heading">
						
				<span class="tools pull-right">
					<a href="<?=ROOT.INVENTORY_ROOT.'extra_stock_add'?>" ><button class="btn btn-success btn-flat" >Add <?=$form?></button></a>	
								
				</span>
				 <div class="col-md-12"	style="height:10px;" ></div>
			
					</header>	
				<div class="panel-body">
				  <div class="adv-table">
				  <table class="display table table-bordered table-striped" id="dynamic-table">
					  <thead>
						  <tr>
							  <th>#</th>
							  <th>Product Name</th>
							  <th>Batch No</th>
							  <th>Base Stock</th>
							  <th>Used Base Stock</th>
							  <th>Convert Stock</th>
							  <th>Used Convert Stock</th>
							  <th>Supllier</th>
							  <th>Branch</th>
							  <th>Remark</th>
							  <!-- <th>Status</th> -->
							  <th class="hidden-phone">Action</th>					  
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

	<?php include_once($include.'footer.php');?>
      <!--footer end-->
  </section>
    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
	
<script src="<?=ROOT.INVENTORY_ROOT?>js/app/extra_stock.js?<?php echo time(); ?>"></script>
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
   , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head></head><body><table>{table}</table></body></html>'
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
