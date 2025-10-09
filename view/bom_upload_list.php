<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Bom Upload ";
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
?>
<!DOCTYPE html>
<html lang="en">
	<head>
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
										<li><a href="<?=ROOT.'production/bom_list'?>">BOM List</a></li>
										<li><a href="#"><?=$form?> List</a></li>
									</ul>
								</div>
							</section>
						</div>
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									<span class="tools pull-right">
										<a href="<?=ROOT.'import_product_opening_stock'?>" target="_blank" title="Import BOM"><button class="btn btn-info btn-flat" data-original-title="Import BOM" data-toggle="tooltip" data-placement="top">Import BOM</button></a>

									</span>
									<div class="col-md-12" style="height:20px;"></div>
								</header>	
								<div class="panel-body">
									<div class="adv-table">
										<table class="display table table-bordered table-striped" id="dispatch-list-datatable">
											<thead>
												<tr>
													<th>Sr.</th>
													<th>Product Name</th>
													<th>Upload Date</th>
													<th>Status</th>
													<th class="hidden-phone">Action</th>
												</tr>
											</thead>
											<tbody></tbody>				 
										</table>
									</div>
								</div>
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once('../include/footer.php');?>
		</section>
		<?php include_once('../include/include_js_file.php');?>   
		<script src="<?=ROOT?>js/app/import_product_opening_stock.js?<?=time()?>"></script>
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
