<?php 
	session_start();
	include('../include/urlfile.php');
	$form="Process Counter Detail ";
	$type=$dbcon->real_escape_string($_REQUEST['type']);
	
	if(empty($type)){
		header("Location: ".DOMAIN."permission_access");
	}
		//check permission for get sales order details
	/*$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    QC_DONE_PARTS_QC_PENDING_LIST
	]);
	if(!in_array(QC_DONE_PARTS_QC_PENDING_LIST,$bulkAccessArray)) {
		header("Location: ".DOMAIN."permission_access");
	}
	*/
	$branch_id = $_SESSION['branch_id'];
	$company_config = getCompanyConfiguration($dbcon);
	$is_store_approval = $company_config['store_approval'];


	if($type == "create_batch") {
		$form="Batch Pending";
	} else if($type == "store_request"){ 
		$form="Store Request Pending ";
	} else if($type == "pending_start"){ 
		$form="Process Start Pending ";
	} else if($type == "pending_stop"){ 
		$form="Process Stop Pending ";
	} else if($type == "reprocess_start"){
		$form="Reprocess Start Pending ";
	} else if($type == "reprocess_stop"){
		$form="Reprocess Stop Pending ";
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?=$form?> List  </title>
		<?php include_once($include.'include_css_file.php');?>
		<style type="text/css">
	.head-text {
		font-family: "Segoe UI",Arial,sans-serif;
		font-weight: 400;
		text-align: center;
		margin: 10px 0;
		font-size: 25px;
		box-sizing: inherit;
		margin-block-start: -0.5em;
		margin-block-end: 0.0em;
		margin-inline-start: 8px;
		margin-inline-end: 0px;
		color: #fff!important;
		border-radius: 4px;
		position: relative;
		background-color: #337ab7!important;
	}
	.count , .count2
	{
		margin:0px !important;
		padding:0px !important

	}
	.cc_count
	{
		margin-left:5%;
	}
	
	.panel-heading
	{
		text-align:center;
		font-weight:bold;
		FONT-SIZE:16px;
	}
	
	.border_line
	{
		border-bottom:dotted blue 2px;
	}
	
	.link_dash
	{
		border-bottom:dotted blue thin;
	}
	
</style>
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
							<section class="panel  panel-primary">
								<div class="panel-body" style="overflow:auto;">
							
							<table class="table" style="text-align:center">
								
								<tr class="bg-primary">
									<th>#</th>
									<th style="white-space:nowrap;">Process Name</th>
									<?php if($company_config['batch_wise_stock'] == '1' && $company_config['batch_process'] == '0' && $type == "create_batch") { ?>
										<th style="white-space:nowrap;">Create Batch </th>
									<?php } ?> 
									
									<?php if($is_store_approval == '1' &&  $type == "store_request"){ ?>
										<th style="white-space:nowrap;">Store Request Pending</th>
									<?php } ?> 
									

									<?php if($type == "pending_start"){ ?>
										<th style="white-space:nowrap;">Pending Start</th>
									<?php } ?> 

									<?php if($type == "pending_stop"){ ?>
										<th style="white-space:nowrap;">Pending Stop</th>
									<?php } ?> 

									<?php if($type == "reprocess_start"){ ?>
										<th style="white-space:nowrap;">Reprocess Start</th>
									<?php } ?> 

									<?php if($type == "reprocess_stop"){ ?>
										<th style="white-space:nowrap;">Reprocess Stop</th>
									<?php } ?> 
									
								</tr>
								
								<?php
								$process_array = $bulkcheck =  [];
								$tr = 0; 
								$cnt=1;
								$sel_p1=$dbcon->query("select process_id,process_name from process_mst where process_status='0' and company_id = ".$_SESSION['company_id']." order by dashbord_priority ");
								while($row_p1=mysqli_fetch_assoc($sel_p1))
								{
									$process_array[] = 'dashboard-inhouse-'.str_replace(' ', '-', strtolower($row_p1['process_name'])); 
								}
								$bulkcheck = canCheckPermissionAccess($dbcon, $process_array);
								$sel_p=$dbcon->query("select process_id,process_name from process_mst where process_status='0' and company_id = ".$_SESSION['company_id']." 
									order by dashbord_priority ");
								$total_count = 0;
								while($row_p=mysqli_fetch_assoc($sel_p))
								{
									$pending_count = 0; 
									?>
									<?php if(in_array($process_array[$tr],$bulkcheck)) { 
										if($type == "create_batch") { 
											$pending_count = batch_store_request_pending_count_store_wise($dbcon,$row_p['process_id'],1,1,1);
										}
										
										if($type == "store_request"){ 
											$pending_count = store_request_pending_count_store_wise($dbcon,$row_p['process_id'],1,1,1);
										}
										if($type == "pending_start"){
											$pending_count = process_wise_store_production_start_count_new($dbcon,$row_p['process_id'],1,1,1);
										} 
										
										if($type == "pending_stop"){ 
											$pending_count = process_wise_store_production_count($dbcon,$row_p['process_id'],1,2);
										}

										if($type == "reprocess_start"){
											$pending_count =  count_re_process_start_qty($dbcon,$row_p['process_id'],'1');
										} 
										if($type == "reprocess_stop"){ 
											$pending_count = count_re_process_end_qty($dbcon,$row_p['process_id'],'1'); 
										} 

										if($pending_count > 0){
											$total_count = $total_count + $pending_count;
										?>
										<tr>
											<th><?php echo $cnt; ?></th>
											<th><?php echo $row_p['process_name']; ?></th>
											
											<?php if($company_config['batch_wise_stock'] == '1' && $company_config['batch_process'] == '0' && $type == "create_batch") { ?>
												<th>
													<a href="<?php echo ROOT.PRODUCTION_ROOT."batch_create_list/".$row_p['process_id']."/1";?>" class="link_dash"><?=$pending_count;?></a>
												</th>
											<?php } ?>
											<!--   START ::  Added by Sanat :: 20-09-2021 -->
											<?php if($is_store_approval == '1'){ ?>
												 <?php if($type == "store_request"){ ?>
												<th>
													<a href="<?php echo ROOT.PRODUCTION_ROOT."store_request_detail_list/".$row_p['process_id']."/1";?>" class="link_dash"><?=$pending_count;?></a>
												</th>
												<?php } ?>
												<?php if($type == "pending_start"){ ?>
												<th> <!--  show allocate qty -->
													<a href="<?php echo ROOT.PRODUCTION_ROOT."working_store_process_details_list/".$row_p['process_id']."/1";?>" class="link_dash"><?=$pending_count;?></a>

												</th>
												<?php } ?>
											<?php }else{ ?>
												<?php if($type == "pending_start"){ ?>
												<th> 
													<a href="<?php echo ROOT.PRODUCTION_ROOT."working_store_process_details_list/".$row_p['process_id']."/1";?>" class="link_dash"><?=$pending_count;?></a>

												</th>
												<?php } ?>
											<?php 	} ?>

											<?php if($type == "pending_stop"){ ?>
												<th>
													<a href="<?php echo ROOT.PRODUCTION_ROOT."working_store_process_details_list/".$row_p['process_id']."/2";?>" class="link_dash"><?=$pending_count;?></a>

												</th>
											<?php } ?> 

											<?php if($type == "reprocess_start"){ ?>
												<th><a href="<?php echo ROOT.PRODUCTION_ROOT."working_reprocess_detail_list/".$row_p['process_id']."/1";?>"  class="link_dash"><?=$pending_count;?></a></th>
											<?php } ?> 

											<?php if($type == "reprocess_stop"){ ?>
												<th><a href="<?php echo ROOT.PRODUCTION_ROOT."working_reprocess_detail_list/".$row_p['process_id']."/2";?>"  class="link_dash"><?=$pending_count;?></a></th>
											<?php } ?> 

										</tr>
									<?php $cnt++;
								}
									 } ?>
									<?php
									$tr++;
									
								}

								if($total_count == 0){ ?>
									<tr>
										<td colspan="3" class="text-center"><h3>NO DATA FOUND.</h3></td>
									</tr>
								<?php }
								?>
								
								
							</table>
							
						</div>
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
		</section>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/parts_qc_pending_list.js?<?=time()?>"></script>
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
