<?php 
session_start();
include('../include/urlfile.php');

$form = 'Stock General';
if(empty($_SESSION['start'])){
	$start = date('1-m-Y');
	$end = date("d-m-Y");
}
else{
	$start = $_SESSION['start'];
	$end = $_SESSION['end'];
}

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    INVENTORY_STOCK_GENERAL_SLUG_READ,
	    INVENTORY_STOCK_GENERAL_SLUG_CREATE
]);

if(!in_array(INVENTORY_STOCK_GENERAL_SLUG_READ,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title><?=$form?></title>
	<?php include_once($include.'include_css_file.php');?>
</head>
<body>
<section id="container">
<?php include_once($include.'include_top_menu.php');?>
<!--sidebar start-->
<?php include_once($include.'left_menu.php');?>
<!--sidebar end-->
<!--main content start-->
<section id="main-content">
<section class="wrapper">
	
	<?php//include_once('../include/equick_link.php');?>
	<div class="row">
		<div class="col-lg-12">
			<!--breadcrumbs start -->
			<section class="panel">
				<header class="panel-heading">
					<h3><?=$mode.' '.$form?></h3>
				</header>	
				<div class="">
					<ul class="breadcrumb">
						<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
						
						<li><a href="<?=ROOT.INVENTORY_ROOT.'stock_general_list'?>"><?=$form?></a></li>
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
					</div>	
					<div class="clearfix"></div>
					<span class="tools pull-right">
						<a href="<?=ROOT.INVENTORY_ROOT.'stock_general_add'?>"><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>
					</span>	
				</header>	
				<div class="panel-body"> 
					<div class="adv-table">
						<table class="display table table-bordered table-striped" id="dynamic-table">
							<thead>
								<tr> 
									<th>Sr.No</th> 
									<th>Stock General No</th> 
									<th>Stock Date</th>
									<th>Remark</th>
									<th>Status</th>
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
<?php include_once($include1.'preview_stock_general_aprv_hist.php'); ?>   
<script src="<?=ROOT.INVENTORY_ROOT?>js/app/stock_general.js?<?=time()?>"></script>
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
</body>
</html>
