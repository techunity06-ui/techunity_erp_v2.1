<?php

session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/function_database_query.php");
$form = "Hrms Attendance Request List";
$mode="Add";
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER['REQUEST_URI'], "hrms_attendance_request_edit")==true) {
	$mode="Edit";
	$hrmsAttenRequestId = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from hrms_attendance_request where id = $hrmsAttenRequestId and company_id = $companyID and user_id = $userID";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	if($rel){
	}else{
		header("Location: " . DOMAIN . HRMS_ROOT ."hrms_attendance_request_list");
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
												<li><a href="<?= ROOT . HRMS_ROOT . 'hrms_attendance_request_list' ?>"><?= $form ?></a></li>
											</ul>
										</div>
									</section>
								</div>
							</div>
							<form class="form-horizontal" role="form" id="hrms_attendance_request_add" action="javascript:;" method="post" name="hrms_attendance_request_add">
								<div class="row">
									<div class="col-sm-12">
										<section class="panel">
											<div class="panel-body">
												<h4><?php if($mode == 'Add') { echo "New"; }else{ echo "Edit"; } ?> <?= $form ?></h4>
													<div class="col-md-12" style="padding-top: 25px;">
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
													  	<div class="col-md-12 margin_row">
													  		<div class="col-md-6">
																<div class="form-group">
																	<label class="col-md-3 control-label">Request From Date*</label>
																	<div class="col-md-8 col-xs-11">
																		<input id="request_from_date" name="request_from_date" type="text" class="form-control default-date-picker" title="Date" placeholder="Request From Date" value="<?php if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['request_from_date']));} ?>">
																	</div>
																</div>
															</div>
														</div>
														<div class="col-md-12 margin_row">
															<div class="col-md-6">
																<div class="form-group">
																	<label class="col-md-3 control-label">Request To Date*</label>
																	<div class="col-md-8 col-xs-11">
																		<input id="request_to_date" name="request_to_date" type="text" class="form-control default-date-picker" title="Date" placeholder="Request To Date" value="<?php if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['request_to_date']));} ?>">
																	</div>
																</div>
															</div>
															<div class="col-md-6">
													  			<div class="form-group">
																	<div class="col-md-8 col-xs-11">
													  					<input type="checkbox" name="is_half_day_flag" id="is_half_day_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['is_half_day_flag'] : 'No' ?>" <?php if($rel['is_half_day_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label3">Half Day </span>
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
					                              REASON
					                              <span class="tools pull-right">
					                                <a href="javascript:;" class="fa fa-chevron-down"></a>
					                              </span>
					                            </header>
					                            <div class="panel-body dd" id="nestable_list_3">
					                              	<div class="col-md-12 margin_row">
														<div class="col-md-6">
															  <div class="form-group">
															  		<label class="col-md-3 control-label">Reason Type</label>
																	<div class="col-md-8 col-xs-11">
																		<select id="reason_type" class="select2" name="reason_type">
																			<option selected disabled value="">SELECT REASON TYPE</option>
																			<option value="0" <?php if($rel['reason_type'] == '0') { echo 'selected'; } ?>>Work From Home</option>
																			<option value="1" <?php if($rel['reason_type'] == '1') { echo 'selected'; } ?>>On Duty</option>
																		</select>
																	</div>
															  </div>							 
														</div>
														<div class="col-md-6">
												  			<div class="form-group">
																<label class="col-md-1 control-label">Reason Explanation</label>
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
																	<textarea style="border: 1px solid #ccc;" id="explanation_description" name="explanation_description" placeholder="Reason Explanation" rows="5" cols="77"><?php if($mode=='Edit') { echo $rel['explanation_description']; } ?></textarea>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-12 margin_row text-center">
														<br>
														<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
														<a href="<?= ROOT . HRMS_ROOT . 'hrms_attendance_request_list' ?>" type="button" class="btn btn-danger">Cancel</a>
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
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_attendance_request.js?<?= time() ?>"></script>
	<script>
		$('#selectAll').click(function(e){
		    var table= $(e.target).closest('table');
		    $('td input:checkbox',table).prop('checked',this.checked);
		});
		$(".select2").select2({
			width: '100%'
		});
		$('#request_from_date, #request_to_date').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});

		$("#request_to_date").change(function () {
		    var startDate = document.getElementById("request_from_date").value;
		    var endDate = document.getElementById("request_to_date").value;
		    if ((Date.parse(parseDate(endDate)) <= Date.parse(parseDate(startDate)))) {
		        alert("Request end date should be greater than request from date");
		        document.getElementById("request_to_date").value = "";
		    }
		});

		function parseDate(str) {
          var mdy = str.split('-');
          return new Date(mdy[2], mdy[1] - 1, mdy[0]);
      	}

      	$(document).ready(function(){
      		$(document).on("click","#is_half_day_flag", function(){
				if($(this).is(":checked")){
					$("#is_half_day_flag").val('Yes');
				}else{
					$("#is_half_day_flag").val('No');
				}
			});
      	});
	</script>
</body>
</html>