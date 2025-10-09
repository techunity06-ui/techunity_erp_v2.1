<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
include_once("../include/function_database_query.php");

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
        RESOURCE_DASHBOARD_VIEW
]);
if(!in_array(RESOURCE_DASHBOARD_VIEW,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}

$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$form="Resource Dashboard";
$start_date = date('d-m-Y');

$set_resource_id = '';
if(isset($_SESSION['dashboard_resource_id']) && $_SESSION['dashboard_resource_id']!=''){
	$set_resource_id = $_SESSION['dashboard_resource_id'];
}

$set_branch_id = '';
if(isset($_SESSION['dashboard_branch_id']) && $_SESSION['dashboard_branch_id']!=''){
	$set_branch_id = $_SESSION['dashboard_branch_id'];
}

$_SESSION['redirect_page'] = 'resource_dashboard';

$branch_id = $_SESSION['branch_id'];


?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>RESOURCE DASHBOARD</title>
	<?php include_once('../include/include_css_file.php');?>
</head>
<body>
	<section id="container" >
		<?php include_once('../include/include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once('../include/left_menu.php');?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
                  <div class="col-lg-12">
                     <section class="panel">
                        <header class="panel-heading">
                           <h3><?=$mode.' '.$form?></h3>
                        </header>
                        <div class="">
                           <ul class="breadcrumb">
                              <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                              <li><a href="javascript:void(0)"><?=$form?> </a></li>
                           </ul>
                        </div>
                     </section>
                  </div>
               </div>
				<!--state overview start-->
				<div class="row">			
					<div class="col-sm-12">
						<section class="panel">

							<div class="panel-body">
								<div class="col-md-12" style="margin-top: 10px;margin-bottom: 10px;">
										<div class="col-md-4">
											<?php echo getBranchBox($dbcon, $branch_id, $set_branch_id, false, false, 'fetch_resource_based_on_branch();'); ?>
										</div>
										<div class="col-md-4">
										<?php if($_SESSION['user_type']=='2'){ ?>
											<div class="col-md-4">
												<strong>Resource * </strong>
											</div>
											<div class="col-md-8">
												<select class="select2" title="Select Resource" name="resource_id" id="resource_id" onChange="show_data();">
													<!-- <?=get_resource_work_list($dbcon,$set_resource_id)?> -->
												</select>
											</div>
										<?php }else{ ?>
											<select class="select2" style="display: block" title="Select Resource" name="resource_id" id="resource_id" onChange="show_data();">
												 <?=get_resource_work_list($dbcon,$_SESSION['resource_id'])?> 
											</select>
										 <?php } ?>	
										 </div>
										<div class="col-md-4">
											<div class="col-md-4">
												<strong>Date *</strong>
												</div>
												<div class="col-md-8">
													<input id="start_date" name="start_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$start_date?>" placeholder="Start Date" onChange="show_data();" >
												</div>
											</div>
										</div>
										<div class="adv-table">
											<table class="display table table-bordered table-striped hide entered_data_info" id="entered_data_info" style="width: 40%;float: left">
												<thead>
													<tr>
														<td>Resource Name</td>
														<td id="resource_name_label"></td>
													</tr>
													<tr>
														<td>Date</td>
														<td id="date_label"></td>
													</tr>
												</thead>
											</table>
											<div class="col-md-7 text-right hide entered_data_info">
                                             <div class="form-group">
                                                <a href="<?=ROOT.'resource_report/'?><?=$set_resource_id?>" class="btn btn-success" id="check_work_report" >Check Work Report</a>
                                             </div>
                                          </div>
											<table class="display table table-bordered table-striped" id="dynamic-table">
												<thead>
													<tr>
														<th>#</th>
														<th>Product Name</th>
														<th>Product Image</th>
														<th>Category</th>
														<th>Item Type</th>
														<th>Process Name</th>
														<th>Work Order</th>
														<th>Job Card</th>
														<th>Total Qty</th>
														<th>Work Qty</th>
														<th>Product Name</th>
														<th>Time</th>
														<th>Work Delay</th>
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
				<?php include_once('../include/show_mrn_list.php');?>
				<?php include_once('../include/footer.php');?>
				<!--footer end-->
			</section>
			<!-- js placed at the end of the document so the pages load faster -->
			<?php include_once('../include/include_js_file.php');?>   
			<script src="<?php echo ROOT; ?>js/app/resource_dashboard.js"></script>
			<script>
				$(".select2").select2({
					width: '100%'
				});
				var dateToday = new Date();    
				$('.default-date-picker').datepicker({
					format: 'dd-mm-yyyy',
					autoclose: true,
					startDate: new Date()
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
