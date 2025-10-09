<?php 
	session_start();
	include_once("../config/config.php");

	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Dispatch";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	if(empty($_SESSION['start'])) {
		$start=date('1-m-Y');
		$end=date("d-m-Y");
	}
	else {
		$start=$_SESSION['start'];
		$end=$_SESSION['end'];
	}
	//echo $reserve_stock=reserve_stock($dbcon,8,3,"","","",2);
	error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>PENDING DISPATCH</title>
		<?php include_once('../include/include_css_file.php');?>
	</head>
	<body>
		<section id="container">
			<?php include_once('../include/include_top_menu.php');?>
			<?php include_once('../include/left_menu.php');?>
			<section id="main-content" style="margin-left: 235px;">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$form?> List</h3>
								</header>
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li><a href="<?=ROOT.'dispatch_list'?>"><?=$form?> List</a></li>
									</ul>
								</div>
							</section>
						</div>
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									<span class="tools pull-right"></span>
									<div class="col-md-12" style="height:20px;">
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Status</label>
												<div class="col-md-8 col-xs-11">
													<select class="select2" id="inv_status" name="inv_status" onchange="load_dispatch_datatable();">
														<option value=""> Select Status</option>
														<option value="1"> Planning Pending</option>
														<option value="2"> Production Pending</option>
														<option value="3"> Dispach Pending</option>
														<option value="4"> Dispach Done</option>
													</select>
												</div>
											</div>
										</div>
									</div>
								</header>	
								<div class="panel-body">
									<div class="adv-table">
										<table class="display table table-bordered table-striped" id="dispatch-list-datatable">
											<thead>
												<tr>
													<th>Sr.</th>
													<th>Sales Order No</th>
													<th>Sales Order Date</th>
													<th>customer</th>
													<th>Territory</th>
													<th>Product Name</th>
													<th>Product Qty</th>
													<th>Discount</th>
													<th>Tax Details</th>
													<th>Amount</th>
													<th class="hidden-phone">Status</th>
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
				</section>
			</section>
<!--main content end-->
<!--footer start-->
<?php include_once('../include/footer.php');?>
<!--footer end-->

</section>
<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<script src="<?=ROOT?>js/app/pending_dispatch_list_crm.js?<?=time()?>"></script>
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
	$('.datepikerdemo').trigger('click');
});
/*$(function(){
	setTimeout(function(){ $('#sidebar > ul').hide(); }, 1000);
});*/
</script>
</body>
</html>
