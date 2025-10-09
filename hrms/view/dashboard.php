<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../include/common_functions.php");
	include_once("../../config/session.php");
	$frmdt=date('d-m-Y');
	$todt=date('d-m-Y');
?>
<!DOCTYPE html>
<html lang="en">
	<head>
			<link href="../hrms/view/css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<link href="../hrms/view/css/bootstrap_limitless.min.css" rel="stylesheet" type="text/css">
	<link href="../hrms/view/css/colors.min.css" rel="stylesheet" type="text/css">
	<link href="../hrms/view/css/components.min.css" rel="stylesheet" type="text/css">
		<?php include_once('../../include/include_css_file.php');?>

	</head>

	<body>
		<section id="container" >
			<?php include_once('../../include/include_top_menu.php');?>
			<?php include_once('../../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-4">

								<!-- Members online -->
								<div class="card bg-teal-400">
									<div class="card-body">
										<div class="d-flex">
											<h3 class="font-weight-semibold mb-0">450</h3>
											
					                	</div>
					                	
					                	<div>
											Total Employees
										</div>
									</div>

									<div class="container-fluid">
										<div id="members-online"></div>
									</div>
								</div>
								<!-- /members online -->

							</div>

							<div class="col-lg-4">

								<!-- Current server load -->
								<div class="card bg-pink-400">
									<div class="card-body">
										<div class="d-flex">
											<h3 class="font-weight-semibold mb-0">20,45,680</h3>
											<div class="list-icons ml-auto">
						                		<div class="list-icons-item dropdown">
						                			<a href="#" class="list-icons-item dropdown-toggle" data-toggle="dropdown"><i class="icon-cog3"></i></a>
													<div class="dropdown-menu dropdown-menu-right">
														<a href="#" class="dropdown-item"><i class="icon-sync"></i> Update data</a>
														<a href="#" class="dropdown-item"><i class="icon-list-unordered"></i> Detailed log</a>
														<a href="#" class="dropdown-item"><i class="icon-pie5"></i> Statistics</a>
														<a href="#" class="dropdown-item"><i class="icon-cross3"></i> Clear list</a>
													</div>
						                		</div>
					                		</div>
					                	</div>
					                	
					                	<div>
											Last Month PayOut
										</div>
									</div>

									<div id="server-load"></div>
								</div>
								<!-- /current server load -->

							</div>

							<div class="col-lg-4">

								<!-- Today's revenue -->
								<div class="card bg-blue-400">
									<div class="card-body">
										<div class="d-flex">
											<h3 class="font-weight-semibold mb-0">5</h3>
											<div class="list-icons ml-auto">
						                		<a class="list-icons-item" data-action="reload"></a>
						                	</div>
					                	</div>
					                	
					                	<div>
											Employees On Leave Today
										</div>
									</div>

									<div id="today-revenue"></div>
								</div>
								<!-- /today's revenue -->

							</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="panel panel-flat">
								<div class="panel-heading">
									<h5 class="panel-title">Recruited (Current & Last Year)</h5>
									<div class="heading-elements">
										<ul class="icons-list">
					                		<li><a data-action="collapse"></a></li>
					                		<li><a data-action="reload"></a></li>
					                		<li><a data-action="close"></a></li>
					                	</ul>
				                	</div>
								</div>

								<div class="panel-body">
									<div class="chart-container">
										<div class="chart has-fixed-height" id="bars_basic"></div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="panel panel-flat">
							<div class="panel-heading">
								<h5 class="panel-title">Key Process Indicators (KPA)</h5>
								<div class="heading-elements">
									<ul class="icons-list">
				                		<li><a data-action="collapse"></a></li>
				                		<li><a data-action="reload"></a></li>
				                		<li><a data-action="close"></a></li>
				                	</ul>
			                	</div>
							</div>

							<div class="panel-body">
								<div class="chart-container has-scroll">
									<div class="chart has-fixed-height has-minimum-width" id="funnel_multiple_overlay"></div>
								</div>
							</div>
						</div>
						</div>
					</div>
				</section>
			</section>
			<?php include_once('../../include/footer.php');?>
		</section>
		<?php include_once('../../include/include_js_file.php');?>   
		<script src="<?=ROOT?>hrms/js/app/todo_mst.js"></script>

		<script src="../hrms/view/js/d3.min.js"></script>
		<script src="../hrms/view/js/dashboard.js"></script>
		<script src="../hrms/view/js/d3_tooltip.js"></script>
		<script src="../hrms/view/js/funnels_calendars.js"></script>
		<script src="<?=ROOT?>hrms/js/app/complaint.js?<?=time()?>"></script>
		<script src="../hrms/view/js/echarts.min.js"></script>
		<script src="../hrms/view/js/bars_tornados.js"></script>
		<script>
			show_todolist();
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			$(".select2").select2({
					width: '100%'
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
