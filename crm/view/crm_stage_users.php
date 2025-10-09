<?php
session_start();
include('../include/urlfile.php');
$frmdt = date('d-m-Y');
$todt = date('d-m-Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>STATSTICAL DASHBOARD</title>
	<?php include_once('../../include/include_css_file.php'); ?>
</head>

<body>
	<section id="container">
		<?php include_once('../../include/include_top_menu.php'); ?>
		<?php include_once('../../include/left_menu.php'); ?>
		<section id="main-content">
			<section class="wrapper">
				<?php
				if (isset($_SESSION['summary_start_date']) && !empty($_SESSION['summary_start_date']) && isset($_SESSION['summary_end_date']) && !empty($_SESSION['summary_end_date'])) {
					$start_date = $_SESSION['summary_start_date'];
					$end_date = $_SESSION['summary_end_date'];
				} else if (isset($_SESSION['start_date']) && !empty($_SESSION['start_date']) && isset($_SESSION['end_date']) && !empty($_SESSION['end_date'])) {
					$start_date = $_SESSION['start_date'];
					$end_date = $_SESSION['end_date'];
				} else {
					$start_date = date("01-m-Y");
					$end_date = date("t-m-Y");
				}

				$opp_id = (isset($_REQUEST['opp_id']) && !empty($_REQUEST['opp_id'])) ? $_REQUEST['opp_id'] : '';
				if (strpos($_SERVER['REQUEST_URI'], "crm_stage_users") == true) {
					$opp_id = $dbcon->real_escape_string($_REQUEST['opp_id']);
					$start_date = $dbcon->real_escape_string($_REQUEST['start_date']);
					$end_date = $dbcon->real_escape_string($_REQUEST['end_date']);
				}

				if (!empty($_SESSION['company_id'])) {
					include_once('../include/crm_stage_users.php');
				}
				?>
			</section>
		</section>
		<?php include_once('../../include/footer.php'); ?>
	</section>
	<?php include_once('../../include/include_js_file.php'); ?>
	<!--		<script src="<?= ROOT ?>js/app/todo_mst.js"></script>
	<script src="<?= ROOT ?>js/app/complaint.js?<?= time() ?>"></script>-->
	<script>
		//show_todolist();
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
		$('.date-set').click(function() {
			$('.datepikerdemo').trigger('click')
		});
	</script>
</body>

</html>