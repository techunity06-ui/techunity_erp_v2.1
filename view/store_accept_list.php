<?php 
	session_start();
	$path = '../';
	$include = '../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once($include."common_functions.php");
	$form="Store Accept";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page'] = $infopage['filename'];
	$date = get_current_financial_year();
    extract($date);	
?>
<!DOCTYPE html>
<html lang="en">
<head>
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
	<div class="row">
		<div class="col-lg-12">
			<!--breadcrumbs start -->
			<section class="panel">
				<header class="panel-heading">
					<h3> <?=$form?> List</h3>
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
	
	<div class="row">		
		<!--state overview start-->
		<div class="row">			
			<div class="col-sm-12">
				<section class="panel">
					<header class="panel-heading">
						<div class="col-md-12" style="height:20px;" ></div>
							<div class="col-md-12">
								<div class="col-md-4">
	                                <div class="form-group">
										<div class="col-md-4">
											<label><strong>Start:</strong></label>
										</div>
										<div class="col-md-8">
											<input id="start_date" name="start_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$start_date?>" placeholder="Start Date" onchange="load_machine_configuration_datatable();">
	                                    </div>
	                                </div>
	                            </div>
	                            <div class="col-md-4">
	                                <div class="form-group">
										<div class="col-md-4">
	                                    <label><strong>End:</strong></label>
										</div>
										<div class="col-md-8">
	                                    <input id="end_date" name="end_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$end_date?>" placeholder="End Date" onchange="load_machine_configuration_datatable();">
										</div>
	                                    
	                                </div>
	                            </div>
	                            <div class="col-md-4">
	                            	<span class="tools pull-right">
										<a href="<?=ROOT.'store_accept_add'?>"><button class="btn btn-success btn-flat">Add <?=$form?></button></a>
									</span>
	                            </div>
                        	</div>
					</header>	
					<div class="panel-body">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="machine_configuration-datatable">
								<thead>
									<tr>
										<th>Sr. No.</th>
										<th>Approve Date</th>
										<th>Status</th>
										<th>Remark</th>
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
<script src="<?=ROOT?>js/app/store_accept_list.js?<?=time()?>"></script>
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
