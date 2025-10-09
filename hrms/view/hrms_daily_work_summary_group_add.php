<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	include_once("../../include/hrms_common_functions.php");
	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Hrms Daily Work Summary Group";
	$companyID = $_SESSION['company_id'];
	$usertype=$_SESSION['user_type'];
	if(strpos($_SERVER[REQUEST_URI], "hrms_daily_work_summary_group_edit")==false)
	{
		$mode="Add";
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	}
	else
	{
		$mode="Edit";
		$hrmsdailyworklist_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select hrmsdailyworklist.* from hrms_daily_work_summary_group as hrmsdailyworklist 
		 		where `hrmsdailyworklist`.`id` = $hrmsdailyworklist_id and `hrmsdailyworklist`.`company_id` = $companyID";
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
		.timepicker-hour, .timepicker-minute{ margin-left: 12px !important; }		
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
										<li ><a href="<?=ROOT . HRMS_ROOT . 'hrms_daily_work_summary_group_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="hrms_daily_work_summary_group_add" action="javascript:;" method="post" name="hrms_daily_work_summary_group_add">
										<div class="">
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="daily_work_summary_group_name" class="col-md-3 control-label">Daily Work Summary Group Name*</label>
														<div class="col-md-8 col-xs-11">
															<input type="text"  name="daily_work_summary_group_name" title="Enter Daily Work Summary Group Name" placeholder="Daily Work Summary Group Name" id="daily_work_summary_group_name" class="form-control" value="<?php if($mode=='Edit'){ echo $rel['daily_work_summary_group_name'];} ?>" />
														</div>
													</div>
												</div>	
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label"></label>
														<div class="col-md-8 col-xs-12">
															<input type="checkbox" name="is_enabled_flag" id="is_enabled_flag" data-id="is_enabled_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['is_enabled_flag'] : 'No' ?>" <?php if($rel['is_enabled_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">Enabled </span>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>SELECT USERS </h4>
											<h6>Users</h6>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="hrms_daily_work_summary_group" class="display table table12 table-striped table-bordered">
														<tr id="field">
															<th width="60%" class="text-center">User</th>
															<th width="8%" class="text-center">Action</th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td data-label="User" style="vertical-align:top;">
																<select id="employee_user_id" class="select2" name="employee_user_id">
																	<option selected disabled value="">SELECT USER</option>
																	<?php
																		$query = $dbcon->query("SELECT `user_id`,`user_name` FROM `users` WHERE `active` = 0 and company_id = $companyID and `user_type` = '2' order by user_id");
																		while ($r = $query->fetch_assoc()) {
																			if($rel['employee_user_id'] == $r['user_id']){
																				$userIDS = 'selected';
																			}else{
																				$userIDS = '';
																			}
																			echo '<option value="' . $r['user_id'] . '" '.$userIDS.'>' . $r['user_name'] . '</option>';
																		}
																	?>
																</select>
															</td>
															<td style="vertical-align:top;">
																<input type="button"  name="adddailyworksummaryrow" id="adddailyworksummaryrow" onClick="return add_hrms_daily_work_summary_group_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>			
												</div>
											</div>
											<div id="hrms_hrms_daily_work_summary_group_data"></div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="send_email_timing" class="col-md-3 control-label">Send Emails At</label>
														<div class="col-md-8 col-xs-11">
															<input type="text"  name="send_email_timing" title="Enter Send Email Timing Date" placeholder="Send Email Timing" id="send_email_timing" class="form-control" value="<?php if($mode=='Edit'){ echo date('H:i',strtotime($rel['send_email_timing']));} ?>" />
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="holiday_list_id" class="col-md-3 control-label">Holiday List</label>
														<div class="col-md-8 col-xs-11">
															<select id="holiday_list_id" class="select2" name="holiday_list_id">
																<option selected disabled value="">SELECT HOLIDAY LIST</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`holiday_name` FROM `hrms_holiday_list` WHERE `company_id` = $companyID and `status` = 0  order by id ");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['holiday_list_id'] == $r['id']){
																			$holidaylistIDS = 'selected';
																		}else{
																			$holidaylistIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$holidaylistIDS.'>' .$r['holiday_name']. '</option>';
																	}
																?>
															</select>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>REMINDER </h4>
											<div class="col-md-12 margin_row">
												<div class="row col-md-12">
										  			<div class="form-group">
														<label class="col-md-2 control-label" style="text-align: center;">Subject</label>
														<div class="col-md-9 col-xs-11" style="margin-left: -50px;">
															<input type="text"  name="reminder_subject" title="Enter Reminder Subject" placeholder="Subject" id="reminder_subject" class="form-control" value="<?php if($mode=='Edit'){ echo $rel['reminder_subject'];} ?>" />
														</div>
													</div>
										  		</div>
												<div class="row col-md-12">
										  			<div class="form-group">
														<label class="col-md-2 control-label" style="text-align: center;">Message</label>
														<div class="col-md-9 col-xs-11" style="margin-left: -50px;">
															<textarea style="border: 1px solid #ccc;" id="reminder_message" name="reminder_message" placeholder="Enter Reminder Message" rows="15" cols="75"><?php if($mode=='Edit') { echo $rel['reminder_message']; } ?></textarea>
														</div>
													</div>
										  		</div>
											</div>
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
													<a href="<?=ROOT.'hrms/hrms_employee_separation_list'?>" type="button" class="btn btn-danger">Cancel</a>
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
			<script src="<?=ROOT . HRMS_ROOT ?>js/app/hrms_daily_work_summary_group.js?<?= time() ?>"></script>
		<script>
			//CKEDITOR.replace('quotation_condition');
			$(".select2").select2({
				width: '100%'
			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			function TimePickerCtrl($) {
				var startTime = $('#send_email_timing').datetimepicker({
				    format: 'HH:mm'
				});
			}
			$(document).ready(TimePickerCtrl);
			CKEDITOR.replace( 'reminder_message', {
				enterMode: CKEDITOR.ENTER_BR,
				height: 400
			});
			$(document).ready(function(){
	      		$(document).on("click","#is_enabled_flag", function(){
					if($(this).is(":checked")){
						$("#is_enabled_flag").val('Yes');
					}else{
						$("#is_enabled_flag").val('No');
					}
				});
      		});
			</script>
			<?php 
				echo "<script>show_daily_work_summary_group_data() </script>";
			?>
	</body>
</html>
