<?php 
	session_start();
	//header("Refresh:10");
	//echo date('H:i:s Y-m-d');
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	
	include_once(COMMON_FUNCTION_OUTER_PATH."common_sub_functions.php");
	$frmdt=date('d-m-Y');
	$todt=date('d-m-Y');

	$companyConfiguration=getCompanyConfiguration($dbcon);
	error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>WORKING DASHBOARD</title>
		<?php include_once('../include/include_css_file.php');?>
	</head>
	<body>
		<section id="container" >
			<?php include_once('../include/include_top_menu.php');?>
			<?php include_once('../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<?php 

						if(!empty($_SESSION['company_id']))
						{
							include_once('../include/dashbord_counter_new.php');
						}

					?>
				</section>
			</section>
			<?php include_once('../include/footer.php');?>
		</section>
		<?php include_once('../include/include_js_file.php');?>   
		<script src="<?=ROOT?>js/app/todo_mst.js"></script>
		<script src="<?=ROOT?>js/app/complaint.js?<?=time()?>"></script>
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
