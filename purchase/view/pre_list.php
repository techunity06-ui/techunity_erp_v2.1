<?php 
	session_start();
	include('../include/urlfile.php');	
	
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		PRE_VIEW
    ]);
    if(!in_array(PRE_VIEW,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
	$form="Indent";
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
	
	$branch_id = $_SESSION['branch_id'];
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>MANUAL INDENT LIST</title>
		<?php include_once($include.'/include_css_file.php');?>
	</head>
	<body>
		<section id="container" >
			<?php include_once($include.'/include_top_menu.php');?>
			<?php include_once($include.'/left_menu.php');?>
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
									<div class='col-lg-5 col-md-7 col-xs-9'>
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
									</div>	
									
									<div class='col-lg-7 col-md-5 col-xs-3' style="text-align:right">
										<a href="<?=ROOT.PURCHASE_ROOT.'add_pre'?>" ><button class="btn btn-success btn-flat" >Add <?=$form?></button></a>
									</div>
								</header>	
								<div class="panel-body">
									<div class="adv-table">
										<table class="display table table-bordered table-striped" id="po-req-table">
											<thead>
												<tr>
													<th>#</th>
													<th>Indent No</th>
													<th>Indent Date</th>
													<th>Branch Name</th>
													<th>User Name</th>
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
			<?php include_once($include.'/footer.php');?>
		</section>
		<?php include_once($include.'/include_js_file.php');?>   
		<script src="<?=ROOT.PURCHASE_ROOT?>js/app/pre.js?<?=time()?>"></script>
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