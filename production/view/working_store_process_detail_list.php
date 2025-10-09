<?php 
	session_start();
	include('../include/urlfile.php');	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	
	$id=$dbcon->real_escape_string($_REQUEST['id']);
	$type=$dbcon->real_escape_string($_REQUEST['type']);
	if($type=="1"){
		$form="Process start Pending List";
	}else{
		$form="Process End Pending List";
	}
	
	
	if(empty($_SESSION['start']))
	{
		$start = date('1-m-Y');
		$end = date("d-m-Y");
	}
	else
	{
		$start = $_SESSION['start'];
		$end = $_SESSION['end'];
	}
	
	
	
	$_SESSION['redirect_page'] = 'working_process_detail_list';
	//echo $type;
	//var_dump(round(8.8, 0, PHP_ROUND_HALF_ODD));
	$branch_id = $_SESSION['branch_id'];
	$companyConfiguration=getCompanyConfiguration($dbcon);
	$store_relese_first_process=$companyConfiguration['store_relese_first_process'];

	$process_end_time_qc = $companyConfiguration['process_end_time_qc'];

?>

<!DOCTYPE html>
<html lang="en">
	<head>
	<title><?php if($type=="1"){
		echo "PROCESS START PENDING LIST";
	}else{
		echo "PROCESS END PENDING LIST";
	}?></title>
		<?php include_once($include.'include_css_file.php');?>
	</head>
	<body>
		<section id="container" >
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$form?> For  <strong style='color:red'><?php echo get_process_name($dbcon,$id); ?></strong></h3>
								</header>
								<div class="">
									<ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									</ul>
								</div>
							</section>
						</div>
			  		</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<div class="col-md-12" style="margin-top: 10px;margin-bottom: 10px;">
									<div class="col-md-6">
										<div class="col-md-4">
											<Strong>Product</strong>
										</div>
										<div class="col-md-6">
											<!-- <select class="select2" title="Select product" name="product_id" id="product_id" onChange="load_datatable();">
												<?=getproduct($dbcon,0,'0,1,2,3,4,5')?>
											</select> -->
											<input id="product_id" name="product_id" style="width:100%;" placeholder="Select product" onchange="load_datatable()"  class=""/>
										</div>
									</div>
									<div class="col-md-6">
										<?php echo getBranchBox($dbcon, $branch_id, '', false, true, 'load_datatable()'); ?>
									</div>
								</div>
								<div class="panel-body">
									<div class="adv-table">
										<table  class="display table table-bordered table-striped" id="dynamic_table_working">
										</table>
									</div>
								</div>
								<input type="hidden" name="process_id" id="process_id" value="<?=$id;?>" />
								<input type="hidden" name="process_type" id="process_type" value="1" />
								<input type="hidden" name="type" id="type" value="<?=$type?>" />
								<input type="hidden" name="process_end_time_qc" id="process_end_time_qc" value="<?=$process_end_time_qc?>" />
								<input type="hidden" name="store_relese_first_process" id="store_relese_first_process" value="<?=$store_relese_first_process?>" />	
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once($include1.'start_process_modal.php');?>
			<?php include_once($include1.'process_start_metirial_deduct_modal.php');?>
			<?php include_once($include1.'batch_wise_deduct_qty.php');?>
			<!-- <?php include_once($include1.'allocate_process_modal.php');?> -->
			<?php include_once($include.'footer.php');?>
		</section>
		 <?php include_once($include1.'production_process_start_stop.php'); ?>
		 <?php include_once($include1.'store_require_confirmation.php'); ?>
		 
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/working_store_process_details_list.js?<?=time()?>"></script>
		<!--<script src="js/count.js"></script>-->
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
				   $('.datepikerdemo').trigger('click')
			});
			var tableToExcel = (function() {
			 var uri = 'data:application/vnd.ms-excel;base64,'
			   , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
			   , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
			   , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
			 return function(table, name) {
			   if (!table.nodeType) table = document.getElementById(table)
			   var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
			   window.location.href = uri + base64(format(template, ctx))
			 }
			})()
		</script>
	</body>
</html>
