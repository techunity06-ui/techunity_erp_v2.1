<?php 
	session_start();
	include('../include/urlfile.php');
    $incPath = $path.'include/';
    $serIncPath = '../include/';

	$frmdt=date('d-m-Y');
	$todt=date('d-m-Y');
    // error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>SERVICE DASHBOARD</title>
<?php include_once($incPath.'include_css_file.php');?>
<style>

.small-box {
    border-radius: .25rem;
    -moz-border-radius: .25rem;
    -webkit-border-radius: .25rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    -moz-box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    -webkit-box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    display: block;
    margin-bottom: 20px;
    position: relative;
}

.small-box>.inner {
    padding: 10px;
}

.small-box h3 {
    font-size: 3rem;
    font-weight: 700;
    margin: 0 0 10px 0;
    padding: 0;
    white-space: nowrap;
}

.small-box h3, .small-box p {
    z-index: 5;
    color: #fff;
}
.small-box p {
    font-size: 2rem;
}

.small-box .icon {
    color: rgba(0,0,0,.15);
    z-index: 0;
}

.small-box .icon>i {
    font-size: 90px;
    position: absolute;
    right: 15px;
    top: 15px;
    transition: all .3s linear;
    -moz-transition: all .3s linear;
    -webkit-transition: all .3s linear;
}

.small-box .icon>i.ion {
    font-size: 70px;
    top: 20px;
}

.small-box>.small-box-footer {
    background: rgba(0,0,0,.1);
    color: rgba(255,255,255,.8);
    display: block;
    padding: 3px 0;
    position: relative;
    text-align: center;
    text-decoration: none;
    z-index: 10;
}

.bg-info {
    background-color: #17a2b8!important;
}

.bg-success {
    background-color: #28a745!important;
}

.bg-warning {
    background-color: #ffc107!important;
}

.bg-danger {
    background-color: #dc3545!important;
}

.chartStyle {
	height: 300px; 
	width: 100%;
}

.setMargin {
	margin: 20px 0;
}

.datepicker td.disabled.day {
    color: #ccc;
}
</style>
</head>
<body>
<section id="container" >
	<?php include_once($incPath.'include_top_menu.php');?>
	<!--sidebar start-->
	<?php include_once($incPath.'left_menu.php');?>
	<!--sidebar end-->
	<!--main content start-->
	<section id="main-content">
		<section class="wrapper">
			<!--state overview start-->
			<?php 
				if(!empty($_SESSION['company_id']))
				{
					include_once($serIncPath.'complaint_counter.php');
				}
			?>
			
			<!--state overview end-->
		</section>
	</section>
	<!--main content end-->
	<!--footer start-->
	
	<?php include_once($incPath.'footer.php');?>
	<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($incPath.'include_js_file.php');?>   

<script src="<?=ROOT?><?=SERVICE_ROOT?>js/app/complaint_report.js?<?=time()?>"></script>
<script>
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
    todayHighlight: true,
	autoclose: true
});
$(".select2").select2({
	width: '100%'
});
</script>
</body>
</html>
