<?php

session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/function_database_query.php");
$form = "Hrms Attendance List";
$mode="Add";
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER[REQUEST_URI], "hrms_attendance_edit")==true) {
	$mode="Edit";
	$hrmsAttendanceId = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from hrms_attendance where id = $hrmsAttendanceId and company_id = $companyID and user_id = $userID";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	if($rel){
	}else{
		header("Location: " . DOMAIN . HRMS_ROOT. "hrms_attendance_list");
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
												<li><a href="<?= ROOT . HRMS_ROOT . 'hrms_attendance_list' ?>"><?= $form ?></a></li>
											</ul>
										</div>
									</section>
								</div>
							</div>
							<form class="form-horizontal" role="form" id="hrms_attendance_add" action="javascript:;" method="post" name="hrms_attendance_add">
								<div class="row">
									<div class="col-sm-12">
										<section class="panel">
											<div class="panel-body">
												<h4><?php if($mode == 'Add') { echo "New"; }else{ echo "Edit"; } ?> <?= $form ?></h4>
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
																	  		$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='ATTENDANCE' and company_id = $companyID and `type_id` = '16' order by invoicetype_id ");
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
																	<label class="col-md-3 control-label">Attendance Date*</label>
																	<div class="col-md-8 col-xs-11">
																		<input id="attendance_date" name="attendance_date" type="text" class="form-control default-date-picker" title="Date" placeholder="Attendance Date" value="<?php if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['attendance_date']));} ?>">
																	</div>
																</div>
															</div>
													  		<div class="col-md-6">
													  			<div class="form-group">
																	<label class="col-md-3 control-label">Attendance Status*</label>
																  	<div class="col-md-8 col-xs-11">
																  		<select class="select2" id="attendance_status" name="attendance_status">
																			<?php echo get_approval_status($dbcon,$rel['attendance_status']); ?>
																		</select>
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
						                <div class="col-md-12">
						                    <section class="panel">
												<header class="panel-heading" style="font-size: 18px;">
					                              DETAILS
					                              <span class="tools pull-right">
					                                <a href="javascript:;" class="fa fa-chevron-down"></a>
					                              </span>
					                            </header>
					                            <div class="panel-body dd" id="nestable_list_3">
					                              	<div class="col-md-12 margin_row">
														<div class="col-md-6">
															  <div class="form-group">
															  		<label class="col-md-3 control-label">Shift Type</label>
																	<div class="col-md-8 col-xs-11">
																		<select id="shift_type_id" class="select2" name="shift_type_id">
																			<option selected disabled value="">SELECT SHIFT TYPE</option>
																			<?php
																			$query = $dbcon->query("SELECT `id`, `shift_type_name` FROM `hrms_shift_type` WHERE `company_id` = $companyID and `status` = 0 order by shift_type_name ");
																			while ($r = $query->fetch_assoc()) {
																				if($rel['shift_type_id'] == $r['id']){
																					$shiftTypeIDS = 'selected';
																				}else{
																					$shiftTypeIDS = '';
																				}
																				echo '<option value="' . $r['id'] . '" '.$shiftTypeIDS.'>' . $r['shift_type_name'] . '</option>';
																			}
																			?>
																		</select>
																	</div>
															  </div>							 
														</div>
														<div class="col-md-6">
												  			<div class="form-group">
																<div class="col-md-8 col-xs-11">
												  					<input type="checkbox" name="late_entry_flag" id="late_entry_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['late_entry_flag'] : 'No' ?>" <?php if($rel['late_entry_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label3">Late Entry </span>
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
														<div class="col-md-6">
												  			<div class="form-group">
																<div class="col-md-8 col-xs-11">
																	<input type="checkbox" name="early_exit_flag" id="early_exit_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['early_exit_flag'] : 'No' ?>" <?php if($rel['early_exit_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label4"> Early Exit</span>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-12 margin_row text-center">
														<br>
														<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
														<a href="<?= ROOT . HRMS_ROOT . 'hrms_attendance_list' ?>" type="button" class="btn btn-danger">Cancel</a>
													</div>
													<div class="col-md-12 margin_row text-center"><br></div>
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
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_attendance.js?<?= time() ?>"></script>
	<script>
		$('#selectAll').click(function(e){
		    var table= $(e.target).closest('table');
		    $('td input:checkbox',table).prop('checked',this.checked);
		});
		$(".select2").select2({
			width: '100%'
		});
		$('#attendance_date').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});
		$(document).ready(function(){
			$(document).on("click","#late_entry_flag", function(){
				if($(this).is(":checked")){
					$("#late_entry_flag").val('Yes');
				}else{
					$("#late_entry_flag").val('No');
				}
			});
			$(document).on("click","#early_exit_flag", function(){
				if($(this).is(":checked")){
					$("#early_exit_flag").val('Yes');
				}else{
					$("#early_exit_flag").val('No');
				}
			});
		});
	</script>
</body>
</html>