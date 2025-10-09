<?php 
	session_start();
	
	include('../include/urlfile.php');
	$form="Quotation Pending";
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

    $pending_count = get_pending_po_quotation_cnt($dbcon);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>PO QUOTATION LIST</title>
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
												 
								</header>	

								<div class="panel-body">
									<ul class="nav nav-tabs">
									    <li ><a data-toggle="tab" href="#entry_list">Entry List </a></li>
									    <li class="active"><a data-toggle="tab" href="#pending_list">Pending Entry <button class="btn btn-sm" style="background-color:blue;background-color: #7e817f;color: white;border-radius: 8px;padding: 0.5px 10px;" ><strong><?=$pending_count?></strong></button></a></li>
								  	</ul>
								  	<div class="tab-content">
								  		<div id="entry_list" class="tab-pane fade">
										  	<div class="adv-table">
												<table class="display table table-bordered table-striped" id="po-quot-table">
													<thead>
														<tr>
															<th>#</th>
															<th>Ref Document No</th>
															<th>Ref Document date</th>
															<th>User Name</th>
															<th class="hidden-phone">Action</th>
														</tr>
													</thead>
													<tbody> </tbody>
												</table>
											</div>
								  		</div>
								  		<div id="pending_list" class="tab-pane fade in active">
								  			<div id="pending_quot_data">
												
											</div>
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
		<script src="<?=ROOT.PURCHASE_ROOT?>js/app/po_quotation_list_new.js?<?=time()?>"></script>
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
