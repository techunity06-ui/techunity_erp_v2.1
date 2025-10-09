<?php

session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/hrms_common_functions.php");
include_once("../../include/function_database_query.php");
$form = "Compensatory Leave Request List";
$mode="Add";
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER[REQUEST_URI], "hrms_comp_leave_request_edit")==true) {
	$mode="Edit";
	$compLeaveId = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from hrms_comp_leave_request where id=$compLeaveId and company_id = $companyID and user_id = $userID";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	if($rel){
	}else{
		header("Location: " . DOMAIN . HRMS_ROOT . "hrms_comp_leave_request_list");
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php'); ?>
</head>
<style type="text/css">
.checkbox_label{ position: absolute !important; overflow: visible; }
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
												<li><a href="<?= ROOT . HRMS_ROOT . 'hrms_comp_leave_request_list' ?>"><?= $form ?></a></li>
											</ul>
										</div>
									</section>
								</div>
							</div>
							<form class="form-horizontal" role="form" id="hrms_comp_leave_request_add" action="javascript:;" method="post" name="hrms_comp_leave_request_add">
								<div class="row">
									<div class="col-sm-12">
										<section class="panel">
											<div class="panel-body">
												<h4><?php if($mode == 'Add') { echo "New"; }else{ echo "Edit"; } ?> <?= $form ?></h4>
												<div class="col-md-12" style="padding-top: 25px;">
													<div class="col-md-12 margin_row">
														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-3 control-label">Leave Type</label>
																<div class="col-md-8 col-xs-11">
																	<select id="leave_type_id" class="select2" name="leave_type_id">
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
													</div>
													<div class="col-md-12 margin_row">
														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-3 control-label">Work From Date*</label>
																<div class="col-md-8 col-xs-11">
																	<input id="work_from_date" name="work_from_date" type="text" class="form-control"  placeholder="Work From Date" value="<?php if($mode=='Edit') { echo date('d-m-Y', strtotime($rel['work_from_date'])); } ?>">
																</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-3 control-label">Work End Date*</label>
																<div class="col-md-8 col-xs-11">
																	<input id="work_end_date" name="work_end_date" type="text" class="form-control"  placeholder="Work End Date" value="<?php if($mode=='Edit') { echo date('d-m-Y', strtotime($rel['work_end_date'])); } ?>">
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-12 margin_row">
														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-3 control-label"></label>
																<div class="col-md-8 col-xs-11">
																	<input type="checkbox" name="is_half_day_leave_flag" id="is_half_day_leave_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['is_half_day_leave_flag'] : 'No' ?>" <?php if($rel['is_half_day_leave_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">Half Day</span>
																</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-3 control-label">Status*</label>
																<div class="col-md-8 col-xs-11">
																	<select id="status" class="select2" name="status">
																		<option selected disabled value="">SELECT STATUS</option>
																		<option value="0" <?php if($rel['status'] == '0') { echo 'selected'; } ?>>Active</option>
																		<option value="1" <?php if($rel['status'] == '1') { echo 'selected'; } ?>>InActive</option>
																	</select>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-12 margin_row">
														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-3 control-label">Reason*</label>
																<div class="col-md-8 col-xs-11">
																	<textarea style="border: 1px solid #ccc;" id="leave_request_reason" name="leave_request_reason" placeholder="Compensatory Leave Request Reason" rows="5" cols="73"><?php if($mode=='Edit') { echo $rel['leave_request_reason']; } ?></textarea>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-12 text-center">
														<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
														<a href="<?= ROOT . HRMS_ROOT . 'hrms_comp_leave_request_list' ?>" type="button" class="btn btn-danger">Cancel</a>
													</div>
												</div>
												</div>
											</section>
										</div>
									</div>
									<input type='hidden' name='mode' id='mode' value='<?= $mode ?>' />
									<input type='hidden' name='eid' id='eid' value='<?= $rel['id'] ?>' />
									<input type="hidden" name="row_cnt" id="row_cnt" value="<?= ($mode == 'Edit') ? $ecount : '0' ?>">
							</form>
					</div>
			
		</section>
	</section>
	<?php include_once('../../include/footer.php'); ?>
	</section>
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_comp_leave_request.js?<?= time() ?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});

		$('#work_from_date, #work_end_date').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});

		$("#work_end_date").change(function () {
		    var startDate = document.getElementById("work_from_date").value;
		    var endDate = document.getElementById("work_end_date").value;
		    if ((Date.parse(parseDate(endDate)) < Date.parse(parseDate(startDate)))) {
		        alert("Work end date should be greater than work from date");
		        document.getElementById("work_end_date").value = "";
		    }
		});

		function parseDate(str) {
          var mdy = str.split('-');
          return new Date(mdy[2], mdy[1] - 1, mdy[0]);
      	}

      	$(document).ready(function(){
      		$(document).on("click","#is_half_day_leave_flag", function(){
				if($(this).is(":checked")){
					$("#is_half_day_leave_flag").val('Yes');
				}else{
					$("#is_half_day_leave_flag").val('No');
				}
			});
      	});
	</script>
</body>
</html>