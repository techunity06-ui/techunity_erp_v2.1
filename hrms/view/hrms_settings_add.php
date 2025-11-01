<?php

session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/hrms_common_functions.php");
include_once("../../include/function_database_query.php");

$form = "HR Settings List";
$mode="Add";
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER['REQUEST_URI'], "hrms_settings_edit")==true) {
	$mode="Edit";
	$hrsettingsId = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from hrms_settings where id=$hrsettingsId and company_id = $companyID and user_id = $userID";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	if($rel){
	}else{
		header("Location: ". DOMAIN . HRMS_ROOT . "hrms_settings_list");
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php'); ?>
</head>
<style type="text/css">
.checkbox_label{ position: absolute; }
.checkbox_label1{ position: absolute;}
.checkbox_label2{ position: absolute;}
.checkbox_label3{ position: absolute;}
.checkbox_label4{ position: absolute;}
.checkbox_label5{ position: absolute;}
.checkbox_label6{ position: absolute !important; overflow: visible; font-size: 15px;}
.checkbox_label7{ position: absolute !important; overflow: visible; font-size: 15px;}
.dd { max-width: none !important; }
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
												<li><a href="<?= ROOT . HRMS_ROOT . 'hrms_settings_list' ?>"><?= $form ?></a></li>
											</ul>
										</div>
									</section>
								</div>
							</div>
							<form class="form-horizontal" role="form" id="hrms_settings_add" action="javascript:;" method="post" name="hrms_settings_add">
								<div class="row">
									<div class="col-sm-12">
										<section class="panel">
											<div class="panel-body">
												<h4><?php if($mode == 'Add') { echo "New"; }else{ echo "Edit"; } ?> <?= $form ?></h4>
													<div class="col-md-12" style="padding-top: 25px;">
														<div class="col-md-12 margin_row">
															<h5>EMPLOYEE SETTINGS</h5><br>	
															<div class="col-md-6">
													  			<div class="form-group">
													  				<label class="col-md-3 control-label" style="">Retirement Age</label>
													  				<div class="col-md-8 col-xs-11">
																		<input type="text" class="form-control" id="retirement_age" name="retirement_age" title="Enter Retirement Age" placeholder="Retirement Age" value="<?php if($mode=='Edit'){ echo $rel['retirement_age'];} ?>">
																		<p>Enter retirement age in years</p>
																	</div>
													  			</div>
													  		</div>
													  		<div class="col-md-6">
													  			<div class="form-group">
																	<div class="col-md-8 col-xs-11">
													  					<input type="checkbox" name="stop_birthday_reminders_flag" id="stop_birthday_reminders_flag" data-id="stop_birthday_reminders_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['stop_birthday_reminders_flag'] : 'No' ?>" <?php if($rel['stop_birthday_reminders_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">Stop Birthday Reminders</span>
													  					<p>Don't send Employee Birthday Reminders</p>
													  				</div>	
													  			</div>
													  		</div>
													  	</div>
													  	<div class="col-md-12 margin_row">
													  		<div class="col-md-6">
													  			<div class="form-group">
													  				<label class="col-md-3 control-label">Employee Records to be created by</label>
													  				<div class="col-md-8 col-xs-11">
																		<select id="employee_records_created_by_type" class="select2" name="employee_records_created_by_type">
																			<option selected disabled value="">SELECT EMPLOYEE TYPE</option>
																			<option value="name_series" <?php if($rel['employee_records_created_by_type'] == 'name_series') { echo 'selected'; } ?>>Naming Series</option>
																			<option value="emp_number" <?php if($rel['employee_records_created_by_type'] == 'emp_number') { echo 'selected'; } ?>>Employee Number</option>
																			<option value="full_name" <?php if($rel['employee_records_created_by_type'] == 'full_name') { echo 'selected'; } ?>>Full Name</option>
																		</select>
																		<p>Employee record is created using selected field.</p>
																	</div>
													  			</div>
													  		</div>
													  		<div class="col-md-6">
													  			<div class="form-group">
																	<div class="col-md-8 col-xs-11">
																		<input type="checkbox" name="expense_approver_mandatory_flag" id="expense_approver_mandatory_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['expense_approver_mandatory_flag'] : 'No' ?>" <?php if($rel['expense_approver_mandatory_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label1">Expense Approver Mandatory In Expense Claim</span>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</section>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-12">   
						                    <section class="panel">
												<header class="panel-heading">
					                              <h4>LEAVE SETTINGS
					                              <span class="tools pull-right">
					                                <a href="javascript:;" class="fa fa-chevron-down"></a>
					                              </span></h4>
					                            </header>
					                            <div class="panel-body dd" id="nestable_list_1">
													<div class="col-md-12 margin_row" >
														<div class="col-md-6">
												  			<div class="form-group">
												  				<label class="col-md-3 control-label">Leave Approval Notification Template</label>
												  				<div class="col-md-8 col-xs-11">
																	<select id="leave_approval_email_template" class="select2" name="leave_approval_email_template">
																		<option selected disabled value="">SELECT APPROVAL NOTIFICATION TEMPLATE</option>
																		<?php
																			$query = $dbcon->query("SELECT `id`,`email_template_name` FROM `hrms_email_template` WHERE `status` = 0 and `company_id` = $companyID order by id");
																			while ($r = $query->fetch_assoc()) {
																				if($rel['id'] == $r['id']){
																					$emailapproverIDS = 'selected';
																				}else{
																					$emailapproverIDS = '';
																				}
																				echo '<option value="' . $r['id'] . '" '.$emailapproverIDS.'>' . $r['email_template_name'] . '</option>';
																			}
																		?>
																	</select>
																</div>
												  			</div>
													  	</div>
													  	<div class="col-md-6">
												  			<div class="form-group">
																<div class="col-md-8 col-xs-11">
																	<input type="checkbox" name="leave_approval_mandatory_flag" id="leave_approval_mandatory_flag" value="<?= ($mode == 'Edit') ? $rel['leave_approval_mandatory_flag'] : 'No' ?>" <?php if($rel['leave_approval_mandatory_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label6">  Leave Approver Mandatory In Leave Application</span>
																</div>
															</div>
														</div>
												  	</div>
													<div class="col-md-12 margin_row">
														<div class="col-md-6">
												  			<div class="form-group">
												  				<label class="col-md-3 control-label">Leave Status Notification Template</label>
												  				<div class="col-md-8 col-xs-11">
																	<select id="leave_status_notification_template" class="select2" name="leave_status_notification_template">
																		<option selected disabled value="">SELECT STATUS NOTIFICATION TEMPLATE</option>
																		<?php
																			$query = $dbcon->query("SELECT `id`,`email_template_name` FROM `hrms_email_template` WHERE `status` = 0 and `company_id` = $companyID order by id");
																			while ($r = $query->fetch_assoc()) {
																				if($rel['id'] == $r['id']){
																					$emailapproverIDS = 'selected';
																				}else{
																					$emailapproverIDS = '';
																				}
																				echo '<option value="' . $r['id'] . '" '.$emailapproverIDS.'>' . $r['email_template_name'] . '</option>';
																			}
																		?>
																	</select>
																</div>
												  			</div>
													  	</div>
													  	<div class="col-md-6">
												  			<div class="form-group">
																<div class="col-md-8 col-xs-11">
																	<input type="checkbox" name="show_leave_all_department_member_flag" id="show_leave_all_department_member_flag" value="<?= ($mode == 'Edit') ? $rel['show_leave_all_department_member_flag'] : 'No' ?>" <?php if($rel['show_leave_all_department_member_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label6">  Show Leaves Of All Department Members In Calendar</span>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-12 margin_row">
														<div class="col-md-6"></div>
														<div class="col-md-6">
												  			<div class="form-group">
																<div class="col-md-8 col-xs-11">
																	<input type="checkbox" name="auto_leave_encashment_flag" id="auto_leave_encashment_flag" value="<?= ($mode == 'Edit') ? $rel['auto_leave_encashment_flag'] : 'No' ?>" <?php if($rel['auto_leave_encashment_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label6">  Auto Leave Encashment</span>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-12 margin_row">
														<div class="col-md-6"></div>
														<div class="col-md-6">
												  			<div class="form-group">
																<div class="col-md-8 col-xs-11">
																	<input type="checkbox" name="restrict_backend_leave_application_flag" id="restrict_backend_leave_application_flag" value="<?= ($mode == 'Edit') ? $rel['restrict_backend_leave_application_flag'] : 'No' ?>" <?php if($rel['restrict_backend_leave_application_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label6"> Restrict Backdated Leave Application</span>
																</div>
															</div>
														</div>
													</div>
					                        	</div>
					                        </section>
					                    </div>
					                </div>
					                <div class="row">
						                <div class="col-md-12">
						                    <section class="panel">
												<header class="panel-heading" style="font-size: 18px;">
					                              HIRING SETTINGS
					                              <span class="tools pull-right">
					                                <a href="javascript:;" class="fa fa-chevron-down"></a>
					                              </span>
					                            </header>
					                            <div class="panel-body dd" id="nestable_list_2">
					                              	<div class="col-md-12 margin_row">
														<div class="col-md-6">
												  			<div class="form-group">
																<label class="col-md-3 control-label" style="font-size: 15px;"></label>
																<div class="col-md-8 col-xs-11">
																	<input type="checkbox" name="check_vacancies_on_job_offer_creation_flag" id="check_vacancies_on_job_offer_creation_flag" value="<?= ($mode == 'Edit') ? $rel['check_vacancies_on_job_offer_creation_flag'] : 'No' ?>" <?php if($rel['check_vacancies_on_job_offer_creation_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label6"> Check Vacancies On Job Offer Creation</span>
																</div>
															</div>
														</div>
													</div>
					                            </div>
					                        </section>
					                    </div>
					                </div>
									<div class="row">
										<div class="col-md-12">
											<section class="panel">
												<div class="panel-body">
									                <div class="col-md-12 margin_row">
														<div class="col-md-6">
												  			<div class="form-group">
																<label class="col-md-3 control-label">Status</label>
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
													<div class="col-md-12 margin_row text-center">
														<br>
														<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
														<a href="<?= ROOT . HRMS_ROOT . 'hrms_settings_list' ?>" type="button" class="btn btn-danger">Cancel</a>
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
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_settings.js?<?= time() ?>"></script>
	<script>	
		$('#selectAll').click(function(e){
		    var table= $(e.target).closest('table');
		    $('td input:checkbox',table).prop('checked',this.checked);
		});
		$(".select2").select2({
			width: '100%'
		});
		$(document).ready(function(){
			$(document).on("click","#stop_birthday_reminders_flag", function(){
				if($(this).is(":checked")){
					$("#stop_birthday_reminders_flag").val('Yes');
				}else{
					$("#stop_birthday_reminders_flag").val('No');
				}
			});
			$(document).on("click","#expense_approver_mandatory_flag", function(){
				if($(this).is(":checked")){
					$("#expense_approver_mandatory_flag").val('Yes');
				}else{
					$("#expense_approver_mandatory_flag").val('No');
				}
			});
			$(document).on("click","#leave_approval_mandatory_flag", function(){
				if($(this).is(":checked")){
					$("#leave_approval_mandatory_flag").val('Yes');
				}else{
					$("#leave_approval_mandatory_flag").val('No');
				}
			});
			$(document).on("click","#show_leave_all_department_member_flag", function(){
				if($(this).is(":checked")){
					$("#show_leave_all_department_member_flag").val('Yes');
				}else{
					$("#show_leave_all_department_member_flag").val('No');
				}
			});	
			$(document).on("click","#auto_leave_encashment_flag", function(){
				if($(this).is(":checked")){
					$("#auto_leave_encashment_flag").val('Yes');
				}else{
					$("#auto_leave_encashment_flag").val('No');
				}
			});
			$(document).on("click","#restrict_backend_leave_application_flag", function(){
				if($(this).is(":checked")){
					$("#restrict_backend_leave_application_flag").val('Yes');
				}else{
					$("#restrict_backend_leave_application_flag").val('No');
				}
			});
			$(document).on("click","#check_vacancies_on_job_offer_creation_flag", function(){
				if($(this).is(":checked")){
					$("#check_vacancies_on_job_offer_creation_flag").val('Yes');
				}else{
					$("#check_vacancies_on_job_offer_creation_flag").val('No');
				}
			});		
		});
	</script>
</body>
</html>