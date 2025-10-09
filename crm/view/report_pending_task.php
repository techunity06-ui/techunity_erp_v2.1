<?php 
session_start();
include('../include/urlfile.php');
// $incPath = $path.'include/';
$form="Pending Task Report";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
$start=date('1-m-Y');
$end=date("d-m-Y");

$companyConfiguration=getCompanyConfiguration($dbcon);
$crm_user_type = $companyConfiguration['crm_user_type'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>PENDING TASK REPORT</title>
	<?php include_once('../../include/include_css_file.php');?>
</head>
<body>
	<section id="container" class="sidebar-closed">
		<?php include_once('../../include/include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once('../../include/left_menu.php');?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">

			<section class="wrapper">

				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3 style=""><?=$form?> </h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.CRM_ROOT.'crm_report_list'?>"> CRM Report List</a></li>
									<li><?=$form?></li>
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
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label col-md-4" style="text-align: right;">Choose Date</label>
										<div class="col-md-8">
											<div class="input-group date form_datetime-component">
												<input type="hidden" id="from_date" value="<?=$start?>">
												<input type="hidden" id="to_date" value="<?=$end?>">
												<input type="text" id="rep_date" onChange="load_pend_task_report();" class="form-control datepikerdemo" value="">
												<span class="input-group-btn">
													<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label col-md-4" style="text-align: right;">Stage :</label>
										<div class="col-md-8">
											<select class="select2" name="stage_id" id="stage_id" onChange="load_pend_task_report();">
												<?= get_inquiry_stage($dbcon,''); ?>	
											</select>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="col-md-4 control-label" style="text-align: right;">User :</label>
										<div class="col-md-8"> 
											<select class="select2" id="user_id" name="user_id" onChange="load_pend_task_report();">
												<?=get_assign_users($dbcon,"", " and user_type in(".$crm_user_type.")");?>
											</select>
										</div>
									</div>
								</div>
								<div class="col-md-12" style="height:20px;"></div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="col-md-4 control-label" style="text-align: right;">State :</label>
										<div class="col-md-8"> 
											<select class="select2" id="state_id" name="state_id" onChange="load_pend_task_report();load_city(this.value,'city_id','');">
												<?=get_state($dbcon,'','101')?>
											</select>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="col-md-4 control-label" style="text-align:right;">City</label>
										<div class="col-md-8">
											<select class="select2" name="city_id" id="city_id" onChange="load_pend_task_report()">
												<option value="">Select City</option>	
											</select>
										</div>
									</div>	
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="col-md-4 control-label" style="text-align:right;">Task Type</label>
										<div class="col-md-8">
											<select class="select2" id="fil_task_type_id" name="fil_task_type_id" onChange="load_pend_task_report();">
												<option value="">ALL</option>
												<?=get_master_category_dtl($dbcon,'',10,'','');//10:Task?>
											</select>
										</div>
									</div>
								</div>
								<div class="col-md-12" style="height:20px;"></div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label col-md-4" style="text-align: right;">Customer</label>
										<div class="col-md-8">
											<select class="select2" id="cust_id" name="cust_id" onChange="load_pend_task_report();">
												<?= getcustomer($dbcon,'') ?>
											</select>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="control-label col-md-4" style="text-align: right;">Territory</label>
										<div class="col-md-8">
											<select class="select2" id="t_id" name="t_id" onChange="load_pend_task_report();">
												<?=get_all_territory($dbcon,'');?>
											</select>
										</div>
									</div>
								</div>
								<!-- <span class="tools pull-right"> 
									<a href="javascript:;" onClick="tableToExcel('adv-table', '<=$form?>')" ><button class="btn btn-primary btn-flat" >Export Excel</button></a>	
								</span>  -->
								<span class="tools pull-right">
									<a href="javascript:;"><button onClick="exportCsv()" class="btn btn-success btn-flat" >Export Excel</button></a>	
								</span>
								<div class="clearfix"></div>
							</header>	
							<div class="clearfix"></div>
							<div class="panel-body">
								<div class="adv-table" style="overflow-x: scroll; overflow-y: scroll;">
									<table class="display table table-bordered table-striped" id="dynamic-tables">
										<thead>
											<tr>
												<th class="text-center">Sr.</th>
												<th class="text-center">Task Name</th>
												<th class="text-center" style="white-space: nowrap;">Inquiry No</th>
												<th class="text-center" style="white-space: nowrap;">Inquiry Date</th>
												<th class="text-center">Customer Name</th>
												<th class="text-center">Stage</th>
												<th class="text-center">State / City</th>
												<th class="text-center">Territory</th>		
												<th class="text-center">Last Followup Date</th>
												<th class="text-center">Next Followup Date</th>
												<th class="text-center">Remark</th>
												<th class="text-center">User</th>
											</tr>
										</thead>
										<tbody></tbody>				 
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
		include_once('../../include/footer.php');
		?>
		<!--footer end-->
	</section>

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php');?>   
	<?php include_once('../../include/include_report_js_file.php');?>  
	<script src="<?=ROOT.CRM_ROOT?>js/app/report_pending_task.js?<?=time()?>"></script> 
	<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/customer.js?<?=time()?>"></script>
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
		// load_city(state,'city_id','');
	</script>
	<script type="text/javascript">
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
