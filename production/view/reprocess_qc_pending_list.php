<?php 
	session_start();
	include('../include/urlfile.php');
	$form="Reprocess QC Pending";
	$process_id=$dbcon->real_escape_string($_REQUEST['process_id']);
	$process_name="";
	if(!empty($process_id)){
		$query_process="SELECT process_name,process_id FROM `process_mst` as trn WHERE trn.process_status=0 and trn.process_id=".$process_id;
			$row_process=brp_mysqli_fetch_assoc($dbcon->query($query_process));
		$process_name=$row_process['process_name'];
	}
	
	//check permission for get sales order details
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    QC_DONE_PARTS_QC_PENDING_LIST
	]);
	if(!in_array(QC_DONE_PARTS_QC_PENDING_LIST,$bulkAccessArray)) {
		header("Location: ".DOMAIN."permission_access");
	}
	$branch_id = $_SESSION['branch_id'];
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>PROCESS QC LIST</title>
		<?php include_once($include.'include_css_file.php');?>
	</head>
	<body>
		<section id="container">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><strong style="color:red;" ><?=$process_name?></strong> <?=$mode.' '.$form?> List</h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li class="active"><strong style="color:red;" ><?=$process_name?></strong> <?=$form?> list</li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<div class="panel-body">
									<div class="col-md-12" >
										<div class="col-md-4">
											<?php echo getBranchBox($dbcon, $branch_id, '', false, true, 'load_parts_qc_pending_datatable()'); ?>	
										</div>
									</div>
									<div class="adv-table">
										<table class="display table table-bordered table-striped" id="parts-qc-pending-datatable">
											<thead>
												<tr>
													<th>Sr. No.</th>
													<th>Product Name</th>
													<th>Product Category</th>
													<?phpif(empty($process_id)){ ?>
														<th>Process Name</th>
													<?php} ?>
													<th>Batch No</th>
													<th>Product Qty</th>
													<th>User Name</th>
													<?phpif($_SESSION['branch_id']==0){ ?>
														<th>Branch Name</th>
													  <?php} ?>		
													<th>Add QC</th>
												</tr>
											</thead>
											<tbody></tbody>
										</table>
									</div>
									<input type="hidden" value="<?=$process_id?>" id="process_id" name="process_id" />
								</div>
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
		</section>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/reprocess_qc_pending_list.js?<?=time()?>"></script>
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
