<?php 
session_start();
include('../include/urlfile.php');
$frmdt=date('d-m-Y');
$todt=date('d-m-Y');
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>FORECAST DASHBOARD</title>
	<?php include_once('../../include/include_css_file.php');?>
</head>
<body>
	<section id="container">

		<?php include_once('../../include/include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once('../../include/left_menu.php');?>
		<!--sidebar end-->
		<!--main content start-->

		<section id="main-content">
			<section class="wrapper">			
				<!--state overview start-->
				<?php error_reporting(E_ALL);
				if(!empty($_SESSION['company_id']))
				{
					include_once('../include/forecast_dashbord_counter.php');
				}
				?>
				
				<!--state overview end-->
			</section>
		</section>
		<!--main content end-->
		<!--footer start-->


		<?php include_once('../../include/footer.php');?>
		<!--footer end-->
	</section>

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php');?> 

	<script>
		$(".select2").select2({
			width: '100%'
		});
		//load_followup_status_history();
		//show_todolist();
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
