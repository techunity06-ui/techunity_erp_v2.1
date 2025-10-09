<?php 
session_start();
include('../include/urlfile.php');
if(strpos($_SERVER['REQUEST_URI'], "non_returnable_channal_list")==true){
	$form="NON RETURNABLE CHALLAN LIST";
}else{
	$form="RETURNABLE CHALLAN LIST";
}
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
if(empty($_SESSION['start'])){
	$start = date('1-m-Y');
	$end = date("d-m-Y");
}
else{
	$start = $_SESSION['start'];
	$end = $_SESSION['end'];
}

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    INVENTORY_RETURNABLE_CHANNAL_SLUG_READ,
	    INVENTORY_RETURNABLE_CHANNAL_SLUG_CREATE
]);
if(!in_array(INVENTORY_RETURNABLE_CHANNAL_SLUG_READ,$bulkAccessArray)){
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
						<?php if(strpos($_SERVER['REQUEST_URI'], "non_returnable_channal_list")==true){ ?>
							<li><a href="<?=ROOT.INVENTORY_ROOT.'non_returnable_channal_list'?>"><?=$form?></a></li>
						<?php }else{ ?>
							<li><a href="<?=ROOT.INVENTORY_ROOT.'returnable_channal_list'?>"><?=$form?></a></li>
						<?php } ?>	
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
					<?php if(in_array(INVENTORY_RETURNABLE_CHANNAL_SLUG_CREATE,$bulkAccessArray)){ ?>
					<span class="tools pull-right">
						<?php if(strpos($_SERVER['REQUEST_URI'], "non_returnable_channal_list")==true){ ?>
							<a href="<?=ROOT.INVENTORY_ROOT.'non_returnable_channal_add'?>"><button class="btn btn-success btn-flat" >CREATE <?=$form?></button></a>
						<?php }else{ ?>
							<a href="<?=ROOT.INVENTORY_ROOT.'returnable_channal_add'?>"><button class="btn btn-success btn-flat" >CREATE <?=$form?></button></a>
						<?php } ?>	
					</span>	
					<?php } ?>
				</header>	
				<div class="panel-body"> 
					<div class="adv-table">
						<table class="display table table-bordered table-striped" id="dynamic-table">
							<thead>
								<tr> 
									<th>Type.</th> 
									<th>Party Name</th> 
									<th>Channal No</th>
									<th>Channal Date</th>
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
<?php include_once($include1.'preview_returnable_channal_aprv_hist.php'); ?>   
<script src="<?=ROOT.INVENTORY_ROOT?>js/app/returnable_channal.js?<?=time()?>"></script>
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
