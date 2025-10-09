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
		<?php include_once('../../include/include_css_file.php');?>
	</head>
	<style type="text/css">
		.icons{
			width: 14.5%;
			float: left;
			margin: 10px 13px 5px;
			text-align: center;
			position:relative;
			border-radius: 8px;
		}
		.icons12{
			background-color:#fff;
			border: 8px;
		}
		.icons p{
			text-align:center;
			font-size:15px;
			font-weight:600;
			font-color:white
		}
		.success{background-color: #5cb85c;}
		.primary{background-color: #0275d8;}
		.icon1.warning{background-color: #f0ad4e;}
		.info{background-color: #5bc0de;}
		.icon1.danger{background-color: #d9534f;}
		.terques{background-color: #6ccac9;}
		.yellow{background-color: #f8d347;}
		.pink{background-color:#E5649A;}
		.mustard{background-color:#b6bdb4;}
		.icon1.success,.icon1.primary,.icon1.warning,.icon1.danger,.icon1.info,.icon1.terques,.icon1.yellow,.icon1.pink,.icon1.mustard{
			width: 40px;
			height: 40px;
			border-radius: 8px;
			text-align: center;
			margin:0 auto;
		}
		.icon1.success i,.icon1.primary i,.icon1.warning i,.icon1.danger i,.icon1.info i,.icon1.terques i,.icon1.yellow i,.icon1.pink i,.icon1.mustard i{
			text-align:center;
			color:#fff;
			padding-top: 13%;
			font-size: 37px;
		}
		.icons .badge {
			position: absolute;
			right: 25px;
			top: 0px;
			z-index: 100;
		}
		@media (max-width:767px){
			.icons {
				width: 47%;
				float: left;
				margin: 30px 4px 25px;
				position:relative;
			}
		}
		@media (min-width:768px) and (max-width:980px)
		{
			.icons12{
				background-color:#d0bcbc;
				padding-top:20px;
				padding-bottom:20px;
				border-radius: 8px;
			}
			.state-overview .terques{
				background-color: none !important;
			}
			.icons {
				width: 265px;
				float: left;
				margin: 30px 4px 25px;
				text-align: center;
				position:relative;
			}
		}
	</style>
	<body>
		<section id="container" >
			<?php include_once('../../include/include_top_menu.php');?>
			<?php include_once('../../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<?php 
						if(!empty($_SESSION['company_id']))
						{
							include_once('../include/hr_dashbord_counter.php');
						}
					?>
				</section>
			</section>
			<?php include_once('../../include/footer.php');?>
		</section>
		<?php include_once('../../include/include_js_file.php');?>   
		<script src="<?=ROOT?>hrms/js/app/todo_mst.js"></script>
		<script src="<?=ROOT?>hrms/js/app/complaint.js?<?=time()?>"></script>
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
