<?php 
session_start();
include('../include/urlfile.php');

$form="Process";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	ADMINISTRATOR_PROCESS_LIST,
	ADMINISTRATOR_PROCESS_CREATE
]);

if(!in_array(ADMINISTRATOR_PROCESS_LIST,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
$companyConfiguration=getCompanyConfiguration($dbcon);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>PROCESS LIST</title>
	<?php include_once($include.'include_css_file.php');?>
	<link href="<?= ROOT ?>assets/sweetalert2/sweetalert2.min.css" rel="stylesheet">
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
								<h3>New <?=$form?></h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.'masters_list'?>"> Masters List</a></li>
									<li class="active"><?=$form?> List</li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>	
				</div>
				<!--unit overview start-->
				<div class="row">
					<?php if(in_array(ADMINISTRATOR_PROCESS_CREATE,$bulkAccessArray)){ ?>
						<div class="col-sm-3">
							<section class="panel">
								<header class="panel-heading">
									New <?=$form?>
								</header>	
								<div class="panel-body">
									<form role="form" id="process_add" action="javascript:;" method="post" name="process_add">
										<?php //if($branch_id=='0'){ ?>
											<?php if($companyConfiguration['branch_wise_manage']=='1'){ ?>
											<div class="form-group">
												<label>Branch *</label>

												<select class="branch_validate" name="abranch_id" id="abranch_id" required >
													<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
													<?=getBranchBox_new($dbcon, $branch,'all');?>
												</select>
											</div>
										<?php } ?>
										<div class="form-group">
											<label>Process Type *</label>
											<select class="select2" name="process_type" id="process_type">
												<?php echo get_process_type($dbcon,$rel['process_type']) ?>
											</select>
										</div>
										<div class="form-group">
											<label>Process Name *</label>
											<input class="form-control" type='text' name='process_name' id='process_name' placeholder="process Name" value='' />
										</div>	
										<div class="form-group">
											<label>Dashbord Priority *</label>
											<input class="form-control" type='number' name='dashbord_priority' id='dashbord_priority' placeholder="Dashbord Priority" value='' />
										</div>		  
										<button type="submit" class="btn btn-success">Submit</button>
									</form>

								</div>
							</section>
						</div>
					<?php } ?>
					<?php if(in_array(ADMINISTRATOR_PROCESS_CREATE,$bulkAccessArray)){ ?>	
						<div class="col-sm-9">
						<?php }else{ ?>	
							<div class="col-sm-12">
							<?php } ?>		
							<section class="panel">
								<header class="panel-heading">
									<?=$form?> List
									<span class="tools pull-right">
										<a href="javascript:;" class="fa fa-chevron-down"></a>
									</span>
								</header>
								<div class="panel-body">
									<div class="adv-table">
										<table class="display table table-bordered table-striped" id="process-table">
											<div class="col-md-12">
												<div class="col-md-6">
												
													<div class="form-group">
														<label class="col-md-4" style="text-align: right">Branch *</label>
														<div class="col-md-6">
															<select class="select2" name="branch_id" id="branch_id" onchange="load_process_datatable()" required <?php if($companyConfiguration['branch_wise_manage']=='0'){ ?> disabled <?php } ?>  >
																<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
																<?=getBranchBox_new($dbcon, $branch,'all');?>
															</select>
														</div>
													</div>
												</div>
												<span class="tools pull-right">
													<a href="javascript:;"><button onClick="exportCsv()" class="btn btn-success btn-flat" >Export Excel</button></a>	
												</span>
											</div>	
											<thead>
												<tr>
													<th>Sr. NO.</th>
													<th>Process Name</th> 
													<th>Process Type</th> 
													<th>Dashbord Priority</th> 
													<th class="hidden-phone">Action</th>					  
												</tr>
											</thead>
											<tbody>
											</tbody>
										</table>
									</div>
								</div>
							</section>
						</div>
					</div>

					<!--unit overview end-->
				</section>
			</section>
			<!--main content end-->
			<!--footer start-->
			<?php include_once($include.'footer.php');?>
			<!--footer end-->
		</section>
		<!-- Modal -->
		<div class="modal colored-header info" id="ModalEditprocess" role="dialog" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog custom-width">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3>Edit <?=$form?></h3>

					</div>
					<div class="modal-body form">
						<form id="FormEditprocess" role="form" method="post" novalidate>
							<?php //if($branch_id=='0'){ ?>
								<?php if($companyConfiguration['branch_wise_manage']=='1'){ ?>
								<div class="form-group">
									<label>Branch *</label>

									<select class="branch_validate" name="branch_id" id="e_branch_id" required>
										<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
										<?=getBranchBox_new($dbcon, $branch,'all');?>
									</select>
								</div>
							<?php } ?>
							<div class="form-group">
								<label for="edit_process_name">Process Type *</label>
								<Select class="form-control" type='text' name='edit_process_type' id='edit_process_type'>
									<option value="">--Select Process Type--</option>
								</select>
							</div>		
							<div class="form-group">
								<label for="edit_process_name">Process Name *</label>
								<input class="form-control" type='text' name='edit_process_name' id='edit_process_name' value='' />
							</div>
							<div class="form-group">
								<label for="edit_dashbord_priority">Dashbord Priority *</label>
								<input class="form-control" type='number' name='edit_dashbord_priority' id='edit_dashbord_priority' value='' />
							</div>

							<div class="modal-footer">
								<input type="hidden" name="edit_id" id="edit_id" value="" />
								<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
								<button class="btn btn-success btn-flat" type="submit">Update</button>
							</div>
						</form>
					</div><!-- /.modal-content -->
				</div><!-- /.modal-dialog -->
			</div><!-- /.modal -->

			<!-- js placed at the end of the document so the pages load faster -->
			<?php include_once($include.'include_js_file.php');?>   
			<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/process_mst.js?<?=time()?>"></script>
			<script type='text/javascript' src='<?= ROOT ?>assets/sweetalert2/sweetalert2.all.min.js'></script>
			<script>
				$(".select2").select2({
					width: '100%'
				});
				$(".branch_validate").select2({
					width: '100%'
				}).on('change', function() {
					$(this).valid();
				});
			</script>
		</body>
		</html>
