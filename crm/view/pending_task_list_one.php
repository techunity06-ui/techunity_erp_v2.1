<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';
$form="Pending Task";
$task_type_id='';
if($_REQUEST['id']){
$task_type_id=$dbcon->real_escape_string($_REQUEST['id']);
		/*$tsk_qry="select mcd_name from tbl_master_category_detail where mcd_id=".$task_type_id;
		$tsk_rel=mysqli_fetch_assoc($dbcon->query($tsk_qry));*/
}
$userid=$dbcon->real_escape_string($_REQUEST['userid']);
	
	
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../../include/include_css_file.php');?>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once('../../include/include_top_menu.php');?>
			<?php include_once('../../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<section class="panel">
						<header class="panel-heading">
							<div class="col-md-5">
								<div class="form-group">
									<label class="control-label col-md-4" style="font-weight:bold;">Due Date</label>
									<div class="col-md-7">
										<div class="input-group date form_datetime-component">
											<input type="text" id="fil_due_date" onChange="load_pend_task();" class="form-control default-date-picker" value="<?=date('d-m-Y')?>">
											<span class="input-group-btn">
												<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
											</span>
										</div>
									</div>
								</div>
							</div>	
							<div class="col-md-5">
								<div class="form-group">
									<label class="control-label col-md-4" style="font-weight:bold;">Task Type</label>
									<div class="col-md-7">
										<select class="select2" id="fil_task_type_id" name="fil_task_type_id" onChange="load_pend_task();">
											<option value="">ALL</option>
											<?=get_master_category_dtl($dbcon,$task_type_id,10,'','');//10:Task?>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-12" style="height:20px;"></div>
							<div class="col-md-5">
								<div class="form-group">
									<label class="control-label col-md-4" style="font-weight:bold;">Select Employee</label>
									<div class="col-md-7">
										<div class="input-group date form_datetime-component">
											<select class="select2" name="crm_tree_user1" id="crm_tree_user1" onchange="load_pend_task();" >
												<?=get_tree_user($dbcon,$_SESSION['user_id'],$userid);?>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-12" style="height:20px;"></div>
						</header>
						<!--<div class="panel-body">
							<div class="row">
								<div class="col-md-12">
									<div class="col-md-12" style="margin-top:10px;">
										<label class="col-md-12 control-label" style="font-weight: bold;font-size: 20px;color: black;">
										Pending Task</label>
										<div class="col-md-12">
											<table class="display table table-bordered table-striped">
												<tr>
													<th class="text-center">Sr.</th>
													<th class="text-center">Type</th>
													<th class="text-center">Related</th>
													<th class="text-center">Name</th>
													<th class="text-center">Stage</th>
													<th class="text-center">State</th>
													<th class="text-center">City</th>
													<th class="text-center">Due Date</th>
													<th class="text-center">Remark</th>
													<th class="text-center">Owner</th>
													<th class="text-center">Action</th>
												</tr>
												<tbody id="pend_task_tbody"></tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>-->
						<div class="panel-body">
							<div class="adv-table">
								<table class="display table table-bordered table-striped" id="dynamic-table">
									<thead>
										<tr>
											<th class="text-center">Sr.</th>
											<th class="text-center">Type</th>
											<th class="text-center">Related</th>
											<th class="text-center">Name</th>
											<th class="text-center">Mobile No</th>
											<th class="text-center">Customer Type</th>
											<th class="text-center">Product Name</th>
											<th class="text-center">Stage</th>
											<th class="text-center">State / City</th>
											<th class="text-center">Due Date</th>
											<th class="text-center">Remark</th>
											<th class="text-center">Owner User</th>
											<th class="text-center">Assign Users</th>
											<th class="hidden-phone">Action</th>		
										</tr>
									</thead>
									<tbody></tbody>				 
								</table>
							</div>
						</div>
					</section>
				</section>
			</section>
			<?php include_once('../../include/footer.php');?>
		</section>
		<?php include_once('../../include/include_js_file.php');?>   
		<script src="<?=ROOT.CRM_ROOT?>js/app/task_one.js?<?=time()?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			$('.date-set').click(function(){
				$('.default-date-picker').datepicker('show');
			});
			$(document).ready(function() {
				Loading(true);	
				load_pend_task();
				Unloading();
			});
		</script>
	</body>
</html>
