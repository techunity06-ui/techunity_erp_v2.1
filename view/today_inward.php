<?php 
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	$form="Today Inward";
	if(empty($_SESSION['start']))
	{
		$start=date('1-m-Y');
		$end=date("d-m-Y");
	}
	else
	{
		$start=$_SESSION['start'];
		$end=$_SESSION['end'];
	}

	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
            PO_QUOTATION_VIEW
    ]);
    if(!in_array(PO_QUOTATION_VIEW,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
    $branch_id = $_SESSION['branch_id'];
	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../include/include_css_file.php');?>
	</head>
	<body>
		<section id="container" >
			<?php include_once('../include/include_top_menu.php');?>
			<?php include_once('../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$mode.' '.$form?> List</h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li class="active"><?=$form?> list</li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									<!--<div class='col-lg-5 col-md-7 col-xs-9'>
										<div class="form-group">
											<label class="control-label col-lg-4 col-md-4 col-xs-3">Choose Date</label>
											<div class=" col-lg-8 col-md-8 col-xs-9">
												<div class="input-group date form_datetime-component">
													<input type="hidden" id="from_date" value="<?=$start?>">
													<input type="hidden" id="to_date" value="<?=$end?>">
													<input type="text" id="rep_date" onChange="reload_data();;" class="form-control datepikerdemo" value="">
													<span class="input-group-btn">
														<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
													</span>
												</div>
											</div>
										</div>
									</div>-->
									<!--<span class="tools pull-right">
										<a href="<?=ROOT.'po_req_add_mul'?>" ><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>
									</span>	-->			 
								</header>	
								<div class="panel-body">
									<div class="adv-table">
										<table class="display table table-bordered table-striped" id="over-inward-table">
											<thead>
												<tr>
													<th>#</th>
													<th>PO No</th>
													<th>PO Date</th>
													<th>Vender Name</th>
													<th>Product Name</th>
													<th>Product Category</th>
													<th>Branch Name</th>
													<th>Product Qty</th>
													<th>Pending Qty</th>
													<th>Unit</th>
													<th>Delivery Date</th>
													<th class="hidden-phone">Action</th>
												</tr>
											</thead>
											<tbody> </tbody>
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
		<?php include_once('../include/over_due_inward_followup.php');?>
		<script src="js/app/today_inward.js?<?=time()?>"></script>
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
		</script>
	</body>
</html>
