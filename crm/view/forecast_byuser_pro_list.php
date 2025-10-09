<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';
$form="Forecast";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
$branch_id = $_SESSION['branch_id'];
if(empty($_SESSION['start'])) {
	$start=date('1-m-Y');
	$end=date("d-m-Y");
}
else {
	$start=$_SESSION['start'];
	$end=$_SESSION['end'];
}

	//check permission for forcast by user pro list
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	FORECAST_BY_USER_PRO_SLUG_READ,
	FORECAST_BY_USER_PRO_SLUG_ADD
]);

if(!in_array(FORECAST_BY_USER_PRO_SLUG_READ,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>FORECAST LIST</title>
	<?php include_once($incPath.'include_css_file.php');?>
</head>
<body>
	<section id="container">
		<?php include_once($incPath.'include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once($incPath.'left_menu.php');?>
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
									<li><a href="<?=ROOT.CRM_ROOT.'forecast_byuser_pro_list'?>"><?=$form?> list</a></li>
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
						<!--<div class="col-md-5">
							<div class="form-group">
								<label class="control-label col-md-4">Choose Date</label>
								<div class="col-md-7">
									<div class="input-group date form_datetime-component">
										<input type="hidden" id="from_date" value="<?=$start?>">
										<input type="hidden" id="to_date" value="<?=$end?>">
										<input type="text" id="rep_date" onChange="load_forecast_datatable();" class="form-control datepikerdemo" value="">
										<span class="input-group-btn">
											<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
										</span>
									</div>
								</div>
							</div>
						</div>-->
						<div class="col-md-12">
							<div class="col-md-6">
								<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'load_forecast_datatable()','4','6'); ?>
							</div>
							<div class="col-md-6">	
								<span class="tools pull-right">
									<?php if(in_array(FORECAST_BY_USER_PRO_SLUG_ADD,$bulkAccessArray)){ ?>
										<a href="<?=ROOT.CRM_ROOT.'forecast_byuser_pro_add'?>"><button class="btn btn-success btn-flat">Add <?=$form?></button></a>
									<?php } ?>
								</span>
							</div>
						</div>
						
					</header>	
					<div class="panel-body">
						<div class="adv-table" id="adv-table">
							<table class="display table table-bordered table-striped" id="forecast-datatable">
								<thead>
									<tr>
										<th>Sr. No.</th>
										<th>Year</th>
										<th>Period Name</th>
										<th>Target Amount</th>
										<th>Target Qty.</th>
										<th>Action</th>					  
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
<?php
include_once($incPath.'footer.php');
?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($incPath.'include_js_file.php');?>   
<script src="<?=ROOT.CRM_ROOT?>js/app/forecast_byuser_pro.js?<?=time()?>"></script>

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
</script>
</body>
</html>
