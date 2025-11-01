<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Hrms Employee Onboarding";
	$companyID = $_SESSION['company_id'];
	$usertype=$_SESSION['user_type'];
	if(strpos($_SERVER['REQUEST_URI'], "hrms_employee_onboarding_edit")==false)
	{
		$mode="Add";
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	}
	else
	{
		$mode="Edit";
		$hrmsempsepalist_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select hrmsempsepalist.* from hrms_main_employee_onboarding as hrmsempsepalist 
				left join tbl_company as com on com.company_id = hrmsempsepalist.company_id
				left join hrms_employee_onboarding_template as hrmstemplate on hrmstemplate.id = hrmsempsepalist.emp_onboarding_temp_id
				left join hrms_designation as hrmsdesig on hrmsdesig.id = hrmstemplate.designation_id
				left join hrms_department as hrmsdepart on hrmsdepart.id = hrmstemplate.designation_id
				left join hrms_emp_grade as hrmsgrade on hrmsgrade.id = hrmstemplate.employee_grade_id
				left join tbl_ledger as empusers on empusers.l_id=hrmsempsepalist.employee_id
		 		where `hrmsempsepalist`.`id` = $hrmsempsepalist_id and `hrmsempsepalist`.`company_id` = $companyID";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../../include/include_css_file.php');?>
	</head>
	<style>
		.check_box_class{ position: absolute !important; overflow: visible !important; }	
		.checkbox_label{ margin-left: 12px; }
		.cke_chrome{ border: 1px solid #d1d1d1 !important; }	
	</style>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once('../../include/include_top_menu.php');?>
			<?php include_once('../../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3> <?=$mode .' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT . HRMS_ROOT . 'hr_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li ><a href="<?=ROOT . HRMS_ROOT . 'hrms_employee_onboarding_list'?>"><?=$form?> List</a></li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									New <?=$form?>
								</header>	
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="hrms_employee_onboarding_add" action="javascript:;" method="post" name="hrms_employee_onboarding_add">
										<div class="">
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
														  		$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='EMPLOYEE ONBOARDING' and company_id = $companyID and `type_id` = '16' order by invoicetype_id ");
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
														<label class="col-md-3 control-label">Employee Onboarding Template*</label>
												  		<div class="col-md-8 col-xs-11">
															<select id="emp_onboarding_temp_id" class="select2" name="emp_onboarding_temp_id" onchange="getEmployeeOnboarding()" required>
																<option selected disabled value="">SELECT EMPLOYEE ONBOARDING TEMPLATE</option>
																<?php
																$query = $dbcon->query("SELECT `id`,`series_id` FROM `hrms_employee_onboarding_template` WHERE `status` = 0 and company_id = $companyID order by id");
																while ($r = $query->fetch_assoc()) {
																	if($rel['emp_onboarding_temp_id'] == $r['id']){
																		$employeeseptempIDS = 'selected';
																	}else{
																		$employeeseptempIDS = '';
																	}
																	echo '<option value="' . $r['id'] . '" '.$employeeseptempIDS.'>' . $r['series_id'] . '</option>';
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
														<label for="designation_id" class="col-md-3 control-label">Designation</label>
														<div class="col-md-8 col-xs-11">
															<select id="designation_id" class="select2" name="designation_id">
																<option selected disabled value="">SELECT DESIGNATION</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`designation_name` FROM `hrms_designation` WHERE `company_id` = $companyID and `status` = 0  order by id ");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['designation_id'] == $r['id']){
																			$designationIDS = 'selected';
																		}else{
																			$designationIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$designationIDS.'>' .$r['designation_name']. '</option>';
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
														<label for="date_of_joining" class="col-md-3 control-label">Date of Joining*</label>
														<div class="col-md-8 col-xs-11">
															<input type="text"  name="date_of_joining" title="Enter Date Of Joining" placeholder="Date Of Joining" id="date_of_joining" class="form-control default-date-picker" value="<?php if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['date_of_joining']));} ?>" />
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="department_id" class="col-md-3 control-label">Department</label>
														<div class="col-md-8 col-xs-11">
															<select id="department_id" class="select2" name="department_id">
																<option selected disabled value="">SELECT DEPARTMENT</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`department_name` FROM `hrms_department` WHERE `company_id` = $companyID and `status` = 0  order by id ");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['department_id'] == $r['id']){
																			$departmentIDS = 'selected';
																		}else{
																			$departmentIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$departmentIDS.'>' .$r['department_name']. '</option>';
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
														<label for="onboarding_status" class="col-md-3 control-label">Onboarding Status*</label>
														<div class="col-md-8 col-xs-11">
															<select id="onboarding_status" class="select2" name="onboarding_status" required>
																<option selected disabled value="">SELECT STATUS</option>
																<option value="pending" <?php if($rel['onboarding_status'] == 'pending') { echo 'selected'; } ?>>Pending</option>
																<option value="inprocess" <?php if($rel['onboarding_status'] == 'inprocess') { echo 'selected'; } ?>>In Process</option>
																<option value="completed" <?php if($rel['onboarding_status'] == 'completed') { echo 'selected'; } ?>>Completed</option>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="employee_grade_id" class="col-md-3 control-label">Grade</label>
														<div class="col-md-8 col-xs-11">
															<select id="employee_grade_id" class="select2" name="employee_grade_id">
																<option selected disabled value="">SELECT GRADE</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`employee_grade_name` FROM `hrms_emp_grade` WHERE `company_id` = $companyID and `status` = 0  order by id ");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['employee_grade_id'] == $r['id']){
																			$employeegradeIDS = 'selected';
																		}else{
																			$employeegradeIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$employeegradeIDS.'>' .$r['employee_grade_name']. '</option>';
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
														<label for="job_applicant_id" class="col-md-3 control-label">Job Applicant</label>
														<div class="col-md-8 col-xs-11">
															<select id="job_applicant_id" class="select2" name="job_applicant_id">
																<option selected disabled value="">SELECT JOB APPLICANT</option>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="job_offer_id" class="col-md-3 control-label">Job Offer</label>
														<div class="col-md-8 col-xs-11">
															<select id="job_offer_id" class="select2" name="job_offer_id">
																<option selected disabled value="">SELECT JOB OFFER</option>
															</select>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label"></label>
														<div class="col-md-8 col-xs-12">
															<input type="checkbox" name="is_notified_user_by_email_flag" id="is_notified_user_by_email_flag" data-id="is_notified_user_by_email_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['is_notified_user_by_email_flag'] : 'No' ?>" <?php if($rel['is_notified_user_by_email_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">Notify Users By Email </span>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>ACTIVITIES </h4>
											<h6>Activities</h6>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="hrms_employee_onboarding" class="display table table12 table-striped table-bordered">
														<tr id="field">
															<th width="20%" class="text-center">Activity Name</th>
															<th width="20%" class="text-center">User</th>
															<th width="20%" class="text-center">Role</th>
															<th width="8%" class="text-center">Action</th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td data-label="Activity Name" style="vertical-align:top;">
																<input type="text"  name="activity_name" title="Enter Activity Name" placeholder="Activity Name" id="activity_name" class="form-control" />
															</td>
															<td data-label="User" style="vertical-align:top;">
																<select id="activity_user_id" class="select2" name="activity_user_id">
																	<option selected disabled value="">SELECT USER</option>
																	<?php
																		$query = $dbcon->query("SELECT `user_id`,`user_name` FROM `users` WHERE `active` = 0 and company_id = $companyID and `user_type` = '2' order by user_id");
																		while ($r = $query->fetch_assoc()) {
																			if($rel['activity_user_id'] == $r['user_id']){
																				$userIDS = 'selected';
																			}else{
																				$userIDS = '';
																			}
																			echo '<option value="' . $r['user_id'] . '" '.$userIDS.'>' . $r['user_name'] . '</option>';
																		}
																	?>
																</select>
															</td>
															<td data-label="Role" style="vertical-align:top;">
																<select id="activity_role_id" class="select2" name="activity_role_id">
																	<option selected disabled value="">SELECT ROLE</option>
																	<?php
																		$query = $dbcon->query("SELECT `usertype_id`,`usertype_name` FROM `tbl_usertype` WHERE `status` = '0' and company_id = $companyID order by usertype_id");
																		while ($r = $query->fetch_assoc()) {
																			if($rel['activity_role_id'] == $r['usertype_id']){
																				$userroleIDS = 'selected';
																			}else{
																				$userroleIDS = '';
																			}
																			echo '<option value="' . $r['usertype_id'] . '" '.$userroleIDS.'>' . $r['usertype_name'] . '</option>';
																		}
																	?>
																</select>
															</td>
															<td style="vertical-align:top;">
																<input type="button"  name="addemployeeonboardingrow" id="addemployeeonboardingrow" onClick="return add_employee_onboarding_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>			
												</div>
											</div>
											<div id="hrms_employee_onboarding_data"></div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<div class="col-md-12 margin_row">
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
											<div class="col-md-12">
												<center>
													<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
													<a href="<?=ROOT . HRMS_ROOT . 'hrms_employee_onboarding_list'?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>		
										</div>
										<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
										<input type='hidden' name='eid' id='eid' value='<?=$rel['id']?>' />
										<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />
									</form>
								</div>	
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once('../../include/footer.php');?>
		</section>
		<?php include_once('../../include/include_js_file.php');?>   
			<script src="<?=ROOT . HRMS_ROOT ?>js/app/hrms_employee_onboarding.js?<?= time() ?>"></script>
		<script>
			//CKEDITOR.replace('quotation_condition');
			$(".select2").select2({
				width: '100%'
			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			CKEDITOR.replace( 'exit_interview_summary', {
				enterMode: CKEDITOR.ENTER_BR,
				height: 400
			});
			$(document).ready(function(){
	      		$(document).on("click","#is_notified_user_by_email", function(){
					if($(this).is(":checked")){
						$("#is_notified_user_by_email").val('Yes');
					}else{
						$("#is_notified_user_by_email").val('No');
					}
				});
      		});
			</script>
			<?php if($mode == 'Edit'){ 
				echo "<script>getEmployeeOnboarding() </script>";
			 } ?>
		<?php 
			echo "<script>show_employee_onboarding_data() </script>";
		?>
	</body>
</html>
