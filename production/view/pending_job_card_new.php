<?php 
session_start();
include('../include/urlfile.php');
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$form="Pending Job Card";
$type=$dbcon->real_escape_string($_REQUEST['id']);
	//echo $type;
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

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    PRODUCTION_PENDING_JOBCARD_SLUG_VIEW
]);

if(!in_array(PRODUCTION_PENDING_JOBCARD_SLUG_VIEW,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>PENDING JOBCARD</title>
	<?php include_once($include.'include_css_file.php');?>

</head>
<body>
	<section id="container" >
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

								<input type="hidden" class="form-control" name="st_type" id="st_type" value="<?=$type;?>" />

								<h3><?=$form?> List</h3>

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
				<!--state overview start-->
				<div class="row">			
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading respadlr0">
							<div class='col-lg-4 col-md-4 col-xs-12'>
								<div class="form-group">
									<label class="control-label col-lg-4 col-md-4 col-xs-4 respad-l0">Choose Date</label>
									<div class=" col-lg-8 col-md-8 col-xs-8 respad-r0">
										<div class="input-group date form_datetime-component">
											<input type="hidden" id="from_date"  value="<?=$start?>">
											<input type="hidden" id="to_date"  value="<?=$end?>">
											<input type="text" id="rep_date"  onChange="reload_data();" class="form-control datepikerdemo" value="">
											<span class="input-group-btn">
												<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
											</span>
										</div>
									</div>
								</div>

							</div>

							<div class='col-lg-5 col-md-7 col-xs-12'>
								<div class="form-group">
										<label class="control-label col-lg-4 col-md-4 col-xs-4 text-right respad-l0">Branch *</label>
										<div class=" col-lg-8 col-md-8 col-xs-8 respad-r0">
								<select class="branch_validate select2" name="branch_id" id="branch_id" required onchange="reload_data()">
	                    								<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
														<?=getBranchBox_new($dbcon, $branch,'all');?>
	                					</select>
	                				</div>
								</div>
							</div>
						</header>
							<div class="panel-body">
										<div class="adv-table" style="margin-top:30px;">
											<table class="display table table-bordered table-striped" id="dynamic-table">
												<thead>
													<tr>
														<th>#</th>
														<th>Jobwork No.</th>
														<th>Jobwork Date</th>
														<th>Vender Name</th>
														<th>Amount</th>
														<th>Status</th>
														<th>Action</th>
													</tr>
												</thead>
												<tbody id="table_data">
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
				<?php include_once($include1.'show_mrn_list.php');?>
				<?php include_once($include.'footer.php');?>
				<!--footer end-->
			</section>
			<!-- js placed at the end of the document so the pages load faster -->
			<?php include_once($include.'include_js_file.php');?>   
			<script src="<?php echo ROOT.PRODUCTION_ROOT; ?>js/app/pending_job_card_new.js"></script>
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
