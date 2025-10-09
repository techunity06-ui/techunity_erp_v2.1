<?php 
	session_start();
	include('../include/urlfile.php');
	$form="Reprocess Pending JobWork";
	$branch_id = $_SESSION['branch_id'];
	$company_config = getCompanyConfiguration($dbcon);		
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>REPROCESS JOBWORK LIST</title>
		<?php include_once($include.'include_css_file.php');?>
		<link href="<?= ROOT ?>assets/sweetalert2/sweetalert2.min.css" rel="stylesheet">
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
									<?php if($company_config['branch_wise_manage']=='1'){ ?>
									<div class="col-md-4">
										<div class="form-group">
											<label class="col-md-4" style="text-align:right;">Branch *</label>
											<div class="col-md-8">
												<select class="select2" name="branch_id" id="branch_id" required onchange="reload_data();">
	                    								<?php $branch = isset($branch_id) ? $branch_id : '1000'; ?>
														<?=getBranchBox_new($dbcon, $branch,'all');?>
	                							</select>
	                						</div>
						            	</div>
									</div>
									<?php }else{ ?>
										<input type="hidden" name="branch_id" id="branch_id" value="<?=$company_config['default_branch_id']?>" />
									<?php } ?>
								</header>	
								<div class="panel-body" id="jobwork_pending">
									<div class="adv-table jobwork-table">
										<table class="display table table-bordered table-striped" id="jobwork-table">
											<thead>
												<tr>
													<th>#</th>
													<th>Workorder No</th>	
													<th>Jobcard No</th>
													<th>Jobwork No</th>
													<th>Jobwork Chalan No</th>
													<th>Product Name</th>
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
								</div>
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
		</section>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="js/app/reprocess_pending_jobwork_list.js?<?=time()?>"></script>
		<script type='text/javascript' src='<?= ROOT ?>assets/sweetalert2/sweetalert2.all.min.js'></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
		</script>
	</body>
</html>
