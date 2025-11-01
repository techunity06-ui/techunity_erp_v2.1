<?php
session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/hrms_common_functions.php");
include_once("../../include/function_database_query.php");

$form = "Leave Application List";
$mode="Add";
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER['REQUEST_URI'], "hrms_leave_application_edit")==true) {
	$mode="Edit";
	$leaveApplicationID = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from hrms_leave_application where id=$leaveApplicationID and company_id = $companyID".check_user('hrmsemp');
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	if($rel){
		$leave_from_date='';
		if($rel['leave_from_date']!="1970-01-01" && $rel['leave_from_date']!="0000-00-00"){
			$leave_from_date=date('d-m-Y',strtotime($rel['leave_from_date']));
		}
	}else{
		header("Location: " . DOMAIN . HRMS_ROOT . "hrms_leave_application_list");
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
input[type=checkbox], input[type=radio] { margin: 3px 0 0 !important;}
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
									<li><a href="<?= ROOT . HRMS_ROOT . 'hrms_leave_application_list' ?>"><?= $form ?></a></li>
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
								<form class="form-horizontal" role="form" id="hrms_leave_application_add" action="javascript:;" method="post" name="hrms_leave_application_add">
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
													  		$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='LEAVE APPLICATION' and company_id = $companyID and `type_id` = '16' order by invoicetype_id ");
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
											<?php if($_SESSION['user_type'] == '2') { ?>
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
											</div>
											<?php } ?>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Leave From Date*</label>
														<div class="col-md-8 col-xs-11">
															<input id="leave_from_date" name="leave_from_date" type="text" class="form-control default-date-picker required valid" title="Date" placeholder="Leave From Date" value="<?php if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['leave_from_date']));} ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-2 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="half_day_leave_flag" id="half_day_leave_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['half_day_leave_flag'] : 'No' ?>" <?php if($rel['half_day_leave_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">Half Day</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Leave To Date*</label>
														<div class="col-md-8 col-xs-11">
															<input id="leave_to_date" name="leave_to_date" type="text" class="form-control default-date-picker required valid" title="Date" placeholder="Leave To Date" value="<?php if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['leave_to_date']));} ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6" id="half_day_leave_date_di" style="display: none;">
													<div class="form-group">
														<label class="col-md-3 control-label">Half Day Date</label>
														<div class="col-md-8 col-xs-11">
															<input id="half_day_leave_date" name="half_day_leave_date" type="text" class="form-control default-date-picker valid" title="Date" placeholder="Half Day Leave Date" value="<?php if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['half_day_leave_date']));} ?>">
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Reason</label>
														<div class="col-md-8 col-xs-11">
															<textarea style="border: 1px solid #ccc;" id="leave_reason" name="leave_reason" placeholder="Leave Application Reason" rows="5" cols="72"><?php if($mode=='Edit') { echo $rel['leave_reason']; } ?></textarea>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Leave Approver</label>
														<div class="col-md-8 col-xs-11">
															<select id="leave_approver_id" class="select2" name="leave_approver_id" required>
																<option selected disabled value="">SELECT EMPLOYEE</option>
																<?php
																$employee_id = getEmployeeIdUser($dbcon, $_SESSION['user_id']);
																$query = $dbcon->query("SELECT `l_id`,`l_name` FROM `tbl_ledger` WHERE `l_status` = 0 and company_id = $companyID and l_id != '".$employee_id."' and `l_group` = '58' order by l_name");
																while ($r = $query->fetch_assoc()) {
																	if($rel['leave_approver_id'] == $r['l_id']){
																		$employeeIDS = 'selected';
																	}else{
																		$employeeIDS = '';
																	}
																	echo '<option value="' . $r['l_id'] . '" '.$employeeIDS.'>' . $r['l_name'] . '</option>';
																} ?>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Leave Application Status</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" id="leave_application_status" name="leave_application_status" placeholder="Leave Application Status">
																<option selected disabled value="">SELECT LEAVE APPLICATION</option>
																<option value="0" <?php if($rel['leave_application_status'] == '0') { echo 'selected'; } ?>>Open</option>
																<?php if($_SESSION['user_type'] == '2') { ?>
																<option value="1" <?php if($rel['leave_application_status'] == '1') { echo 'selected'; } ?>>Approved</option>
																<option value="2" <?php if($rel['leave_application_status'] == '2') { echo 'selected'; } ?>>Rejected</option>
																<option value="3" <?php if($rel['leave_application_status'] == '3') { echo 'selected'; } ?>>Cancelled</option>
															<?php } ?>
															</select>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Salary Slip</label>
														<div class="col-md-8 col-xs-11">
															<select id="salary_slip_id" class="select2" name="salary_slip_id">
																<option selected disabled value="">SELECT SALARY SLIP</option>
																<?php
																$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and company_id = $companyID and `invoicetype_id`='23' and `type_id` = '16' order by invoicetype_id ");
																while ($r = $query->fetch_assoc()) {
																	if($rel['salary_slip_id'] == $r['invoicetype_id']){
																		$salaryIDS = 'selected';
																	}else{
																		$salaryIDS = '';
																	}
																	echo '<option value="' . $r['invoicetype_id'] . '" '.$salaryIDS.'>' . $r['format_value'] . $r['taxinvoice_start'] . $r['end_format_value'] . '</option>';
																} ?>
															</select>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Posting Date</label>
														<div class="col-md-8 col-xs-11">
															<input id="leave_posting_date" name="leave_posting_date" type="text" class="form-control default-date-picker valid" title="Date" placeholder="Leave Posting Date" value="<?php if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['leave_posting_date']));} ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Letter Head</label>
														<div class="col-md-8 col-xs-11">
															<select id="letter_head_id" class="select2" name="letter_head_id">
																<option selected disabled value="">SELECT LETTER HEAD</option>
																<?php
																$query = $dbcon->query("SELECT `id`,`letter_head_name` FROM `hrms_letter_head` WHERE `status` = 0 and company_id = $companyID order by letter_head_name ");
																while ($r = $query->fetch_assoc()) {
																	if($rel['invoicetype_id'] == $r['invoicetype_id']){
																		$letterheadIDS = 'selected';
																	}else{
																		$letterheadIDS = '';
																	}
																	echo '<option value="' . $r['id'] . '" '.$letterheadIDS.'>' . $r['letter_head_name'] . '</option>';
																} ?>
															</select>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="leave_follow_via_mail_flag" id="leave_follow_via_mail_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['leave_follow_via_mail_flag'] : 'No' ?>" <?php if($rel['leave_follow_via_mail_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">Follow Via Email</span>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Color Code</label>
														<div class="col-md-8 col-xs-11">
															<input type="color" id="leave_color_code" name="leave_color_code" class="form-control required valid" title="Date" placeholder="Color Code" value="<?php if($mode=='Edit'){ echo $rel['leave_color_code'];} ?>" >
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
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
											</div>
											<div class="col-md-12 margin_row text-center">
												<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
												<a href="<?= ROOT . HRMS_ROOT . 'hrms_leave_application_list' ?>" type="button" class="btn btn-danger">Cancel</a>
											</div>
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
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_leave_application.js?<?= time() ?>"></script>
	<script>
		$('#selectAll').click(function(e){
		    var table= $(e.target).closest('table');
		    $('td input:checkbox',table).prop('checked',this.checked);
		});
		$(".select2").select2({
			width: '100%'
		});
		$(".default-date-picker").datepicker({
	        format: "dd-mm-yyyy",
	        autoclose: true,
	        todayHighlight: true
	    });
		$('#leave_from_date').datepicker()
	    .on('changeDate', function(e) {
	        var start_date = e.format(0,"dd-mm-yyyy");
	        var end_date = $('#leave_to_date').val();

	        job_start_date = start_date.split('-');
	        job_end_date = end_date.split('-');

	        var new_start_date = new Date(job_start_date[2],job_start_date[0],job_start_date[1]);
	        var new_end_date = new Date(job_end_date[2],job_end_date[0],job_end_date[1]);

	        $('#leave_to_date').datepicker('setStartDate', e.format(0,"dd-mm-yyyy"));
	        
	        if(end_date == '' || new_start_date > new_end_date) {
	            $('#leave_to_date').datepicker('setDate', e.format(0,"dd-mm-yyyy"));
	        }

	    });
		$('#half_day_leave_date ,#leave_posting_date').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});
		$("#leave_to_date").change(function () {
		    var startDate = document.getElementById("leave_from_date").value;
		    var endDate = document.getElementById("leave_to_date").value;
		    if ((Date.parse(parseDate(endDate)) < Date.parse(parseDate(startDate)))) {
		        alert("Leave application to date should be greater than leave application from date");
		        document.getElementById("leave_to_date").value = "";
		    }
		});
		function parseDate(str) {
          var mdy = str.split('-');
          return new Date(mdy[2], mdy[1] - 1, mdy[0]);
      	}
      	<?php if($mode == 'Edit'){ ?>
			var checkboxChecked = $("#half_day_leave_flag").is(":checked");
			if(checkboxChecked){
				$("#half_day_leave_date_di").css("display","block");
			}else{
				$("#half_day_leave_date_di").css("display","none");
			}
		<?php } ?>
      	$(document).ready(function(){
      		$(document).on("click","#half_day_leave_flag", function(){
				if($(this).is(":checked")){
					$("#half_day_leave_flag").val('Yes');
					$("#half_day_leave_date_di").css("display","block");
				}else{
					$("#half_day_leave_flag").val('No');
					$("#half_day_leave_date_di").css("display","none");
				}
			});
      	});
	</script>
</body>
</html>