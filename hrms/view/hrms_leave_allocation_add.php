<?php

session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/hrms_common_functions.php");
include_once("../../include/function_database_query.php");
$form = "Leave Allocation List";
$mode="Add";
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER[REQUEST_URI], "hrms_leave_allocation_edit")==true) {
	$mode="Edit";
	$leaveAllocationID = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from hrms_leave_allocation where id=$leaveAllocationID and company_id = $companyID and user_id = $userID";
	$rel=mysqli_fetch_assoc($dbcon->query($query));	
	if($rel){
		$leave_from_date='';
		if($rel['leave_from_date']!="1970-01-01" && $rel['leave_from_date']!="0000-00-00"){
			$leave_from_date=date('d-m-Y',strtotime($rel['leave_from_date']));
		}
	}else{
		header("Location: " . DOMAIN . HRMS_ROOT . "hrms_leave_allocation_list");
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php'); ?>
</head>
<style>
.check_box_class{ position: absolute !important; overflow: visible !important; }	
.checkbox_label{ margin-left: 12px; }
</style>
<body>
	<section id="container" class="sidebar-closed">
		<?php include_once('../../include/include_top_menu.php'); ?>
		<?php include_once('../../include/left_menu.php'); ?>
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<h3><?= $mode . ' ' . $form ?></h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . HRMS_ROOT . 'hr_dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?= ROOT . HRMS_ROOT . 'hrms_leave_allocation_list' ?>"><?= $form ?></a></li>
								</ul>
							</div>
						</section>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<section class="panel">
							<div class="panel-body">
								<h4><?php if($mode == 'Add') { echo "New"; }else{ echo "Edit"; } ?> <?= $form ?></h4>
								<form class="form-horizontal" role="form" id="hrms_leave_allocation_add" action="javascript:;" method="post" name="hrms_leave_allocation_add">
									<div class="col-md-12" style="padding-top: 25px;">
							 			<div class="col-md-12 margin_row">
							 				<?php if($mode == "Edit"){ ?>
							 					<div class="col-md-6">
													  <div class="form-group">
													  		<label class="col-md-3 control-label">Series</label>
													  		<div class="col-md-8 col-xs-11">
													  			<input type="text" class="form-control" id="series_edit_id" placeholder="series_id" value="<?php if($mode=='Edit'){ echo $rel['series_id'];} ?>" readonly />
													  			<input type="hidden" class="form-control" id="series_id" name="series_id" placeholder="series_id" value="<?php if($mode=='Edit'){ echo $rel['series_id'];} ?>" />
															</div>
													  </div>							 
												 </div>
							 				<?php } else { ?>
							 					<div class="col-md-6">
													  <div class="form-group">
													  		<label class="col-md-3 control-label">Series</label>
													  		<?php
													  		$series_id = '';
													  		$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='LEAVE ALLOCATION' and company_id = $companyID and `type_id` = '16' order by invoicetype_id ");
																	while ($r = $query->fetch_assoc()) {
																		$series_id = $r['format_value'] . $r['taxinvoice_start'] . $r['end_format_value'];
																	}
															?>
													  		<div class="col-md-8 col-xs-11">
													  			<input type="text" class="form-control" id="series_id" name="series_id" placeholder="series_id" value="<?php echo $series_id; ?>" readonly />
															</div>
													  </div>							 
												 </div>	
											 <?php } ?>
											 <div class="col-md-6">
												  <div class="form-group">
												  		<label class="col-md-3 control-label">Leave Type*</label>
														<div class="col-md-8 col-xs-11">
															<select id="leave_type_id" class="select2" name="leave_type_id" required>
																<option selected disabled value="">SELECT LEAVE TYPE</option>
																<?php
																$query = $dbcon->query("SELECT `id`,`leave_type_name` FROM `hrms_leave_type` WHERE `status` = 0 and company_id = $companyID order by leave_type_name");
																while ($r = $query->fetch_assoc()) {
																	if($rel['leave_type_id'] == $r['id']){
																		$leaveTypeIDS = 'selected';
																	}else{
																		$leaveTypeIDS = '';
																	}
																	echo '<option value="' . $r['id'] . '" '.$leaveTypeIDS.'>' . $r['leave_type_name'] . '</option>';
																}
																?>
															</select>
														</div>
												  </div>							 
											 </div>
							 			</div>
							 			<div class="col-md-12 margin_row">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Employee*</label>
											  		<div class="col-md-8 col-xs-11">
														<select id="employee_id" class="select2" name="employee_id" required>
															<option selected disabled value="">SELECT EMPLOYEE</option>
															<?php
															$query = $dbcon->query("SELECT `l_id`,`l_name` FROM `tbl_ledger` WHERE `l_status` = 0 and company_id = $companyID and `l_group` = '58' order by l_name");
															while ($r = $query->fetch_assoc()) {
																if($rel['employee_id'] == $r['l_id']){
																	$employeeIDS = 'selected';
																}else{
																	$employeeIDS = '';
																}
																echo '<option value="' . $r['l_id'] . '" '.$employeeIDS.'>' . $r['l_name'] . '</option>';
															}
															?>
														</select>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Leave From Date*</label>
													<div class="col-md-8 col-xs-11">
														<input id="leave_from_date" name="leave_from_date" type="text" class="form-control default-date-picker required valid" title="Date" placeholder="Leave From Date" value="<?php if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['leave_from_date']));} ?>">
													</div>
												</div>
											</div>
											<div class="col-md-6">
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Leave To Date*</label>
													<div class="col-md-8 col-xs-11">
														<input id="leave_to_date" name="leave_to_date" type="text" class="form-control default-date-picker required valid" title="Date" placeholder="Leave To Date" value="<?php if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['leave_to_date']));} ?>">
													</div>
												</div>
											</div>
										</div>
										<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
										<div class="col-md-12 margin_row">
											<h4>Allocation</h4>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">New Leave Allocated</label>
													<div class="col-md-8 col-xs-11">
														<input id="new_leave_allocation" name="new_leave_allocation" type="text" class="form-control" title="New Leave Allocation" placeholder="New Leave Allocation" value="<?php if($mode=='Edit'){ echo $rel['new_leave_allocation'];} ?>">
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-1 control-label"></label>
													<div class="col-md-8 col-xs-11">
														<input type="checkbox" name="add_unused_leave_flag" id="add_unused_leave_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['add_unused_leave_flag'] : 'No' ?>" <?php if($rel['add_unused_leave_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label"> Add unused leaves from previous allocations</span>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12 margin_row">
								 			<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Total Leaves Allocated</label>
													<div class="col-md-8 col-xs-11">
														<input id="total_leave_allocated" name="total_leave_allocated" type="text" class="form-control" title="Total Leave Allocation" placeholder="Total Leave Allocation" readonly value="<?php if($mode=='Edit'){ echo $rel['total_leave_allocated'];} ?>">
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group" id="unused_leave_date_di" style="display: none;">
													<label class="col-md-3 control-label">Unused leaves</label>
													<div class="col-md-8 col-xs-11">
														<input id="unused_leave_total" name="unused_leave_total" type="text" class="form-control" title="Unused leaves" placeholder="Unused leaves" value="<?php if($mode=='Edit'){ echo $rel['unused_leave_total'];} ?>">
													</div>
												</div>
											</div>
								 		</div>
								 		<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
								 		<div class="col-md-12 margin_row">
								 			<h4>Notes</h4>
								 			<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Reason</label>
													<div class="col-md-8 col-xs-11">
														<textarea style="border: 1px solid #ccc;" id="allocation_description" name="allocation_description" placeholder="Leave Allocation Reason" rows="5" cols="77"><?php if($mode=='Edit') { echo $rel['allocation_description']; } ?></textarea>
													</div>
												</div>
											</div>
								 		</div>
								 		<div class="col-md-12 margin_row">
								 			<div class="col-md-6">
									 			<label class="col-md-3 control-label">Status</label>
									 			<div class="col-md-8 col-xs-11">
													<select id="status" class="select2" name="status" required>
														<option selected disabled value="">SELECT STATUS</option>
														<option value="0" <?php if($rel['status'] == '0') { echo 'selected'; } ?>>Active</option>
														<option value="1" <?php if($rel['status'] == '1') { echo 'selected'; } ?>>InActive</option>
													</select>
												</div>
											</div>
								 		</div>
										<div class="col-md-12 margin_row text-center">
											<br>
											<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
											<a href="<?= ROOT . 'hrms/hrms_leave_allocation_list' ?>" type="button" class="btn btn-danger">Cancel</a>
										</div>
							 		</div>
							 		
							</div>
							<input type='hidden' name='mode' id='mode' value='<?= $mode ?>' />
							<input type='hidden' name='eid' id='eid' value='<?= $rel['id'] ?>' />
							<input type="hidden" name="row_cnt" id="row_cnt" value="<?= ($mode == 'Edit') ? $ecount : '0' ?>">
							</form>
					</div>
			</section>
			</div>
			</div>
		</section>
	</section>
	<?php include_once('../../include/footer.php'); ?>
	</section>
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_leave_allocation.js?<?= time() ?>"></script>
	<script>
		$('#selectAll').click(function(e){
		    var table= $(e.target).closest('table');
		    $('td input:checkbox',table).prop('checked',this.checked);
		});
		$(".select2").select2({
			width: '100%'
		});
		$('#leave_from_date, #leave_to_date').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});
		$("#leave_to_date").change(function () {
		    var startDate = document.getElementById("leave_from_date").value;
		    var endDate = document.getElementById("leave_to_date").value;
		    if ((Date.parse(parseDate(endDate)) <= Date.parse(parseDate(startDate)))) {
		        alert("Leave allocation to date should be greater than leave allocation from date");
		        document.getElementById("leave_to_date").value = "";
		    }
		});
		function parseDate(str) {
          var mdy = str.split('-');
          return new Date(mdy[2], mdy[1] - 1, mdy[0]);
      	}
      	<?php if($mode == 'Edit'){ ?>
			var checkboxChecked = $("#add_unused_leave_flag").is(":checked");
			if(checkboxChecked){
				$("#unused_leave_date_di").css("display","block");
			}else{
				$("#unused_leave_date_di").css("display","none");
			}
		<?php } ?>
      	$(document).ready(function(){
      		$(document).on("click","#add_unused_leave_flag", function(){
				if($(this).is(":checked")){
					$("#add_unused_leave_flag").val('Yes');
					$("#unused_leave_date_di").css("display","block");
				}else{
					$("#add_unused_leave_flag").val('No');
					$("#unused_leave_date_di").css("display","none");
				}
			});
			$(document).on("keyup", "#new_leave_allocation", function(){
				var new_leave = $("#new_leave_allocation").val();
				$("#total_leave_allocated").val(new_leave);
			});
      	});
	</script>
</body>
</html>