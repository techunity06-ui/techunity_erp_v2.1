<?php 
	session_start();
	include('../include/urlfile.php');
	$form="Pending Job Work";
	$branch_id = $_SESSION['branch_id'];
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>PENDING JOBWPRK LIST</title>
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

									<!--<div class='col-md-5'>
										 <div class="form-group">
											<label class="control-label col-lg-4 col-md-4 col-xs-3">Choose Vendor</label>
											<div class=" col-lg-8 col-md-8 col-xs-9">
												<div class="input-group date form_datetime-component">
													<select class="select2" name="vender_id" id="vender_id"  title="Select Vendor" onChange="reload_data();">
															<?=getcust($dbcon);?>	
													</select>
												</div>
											</div>
										</div>
									</div>	 -->
									<div class="col-md-6">
										<div class="col-md-4">
											<label class="external-event label label-warning ui-draggable" style="position: relative;cursor:pointer;">Total Pending</label>
											<input id="jobwork_status3" name="jobwork_status" onClick="reload_data();" type="radio" class="" title="Pending" value="total_pending" />
										</div>
										<div class="col-md-4">
											<label class="external-event label label-warning ui-draggable" style="position: relative;cursor:pointer;">Working Qty</label>
											<input id="jobwork_status3" name="jobwork_status" checked onClick="reload_data();" type="radio" class="" title="Pending" value="working_qty" />
										</div>
										<div class="col-md-4">
											<label class="external-event label label-success ui-draggable" style="position: relative;cursor:pointer;">Done</label>
											<input id="jobwork_status2" name="jobwork_status" onClick="reload_complete_data();" type="radio" class="" title="Done" value="3" />
										</div>
									</div>
									<div class="col-md-4">
										<?php echo getBranchBox($dbcon, $branch_id, '', false, true, 'load_jobwork_datatable()'); ?>	
									</div>
									
										<span class="tools pull-right">
											<a href="<?=ROOT.PRODUCTION_ROOT.'create_job_work'?>" ><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>
										</span>
									
								</header>	
								<div class="panel-body">
									<div class="adv-table jobwork-table">
										<table class="display table table-bordered table-striped" id="jobwork-table">
											<thead>
												<tr>
													<th>#</th>
													<th>Product Name</th>
													<th>Product Category</th>
													<th>Process Name</th>
													<th class="th_vandor">Vendor</th>
													<th>Total Qty</th>
													<th>Working Qty</th>
													<th>Used Qty</th>
													<?php if($_SESSION['branch_id']==0){ ?>
														<th>Branch Name</th>
													  <?php } ?>
													<th class="hidden-phone">Action</th>
												</tr>
												
											</thead>
											<tbody> </tbody>
										</table>

									</div>
									<div class="adv-table jobwork-done-table" style="display: none">
										<table class="display table table-bordered table-striped" id="jobwork-done-table">
											<thead>
												<tr>
													<th>#</th>
													<th>Jobwork No</th>
													<th>Jobwork Date</th>
													<th>Vendor</th>
													<th>Vehicle No</th>
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
			<?php include_once($include.'footer.php');?>
		</section>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="js/app/pending_jobwork_list.js?<?=time()?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
		</script>
	</body>
</html>
