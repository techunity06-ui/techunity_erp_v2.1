<?php

session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/hrms_common_functions.php");
include_once("../../include/function_database_query.php");
$form = "Leave Type List";
$mode="Add";
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER[REQUEST_URI], "leave_type_edit")==true) {
	$mode="Edit";
	$leaveTypeId = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from hrms_leave_type where id=$leaveTypeId and company_id = $companyID and user_id = $userID";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	if($rel){
	}else{
		header("Location: " . DOMAIN . HRMS_ROOT . "hrms/hrms_leave_type_list");
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
												<li><a href="<?= ROOT . HRMS_ROOT . 'hrms_leave_type_list' ?>"><?= $form ?></a></li>
											</ul>
										</div>
									</section>
								</div>
							</div>
							<form class="form-horizontal" role="form" id="hrms_leave_type_add" action="javascript:;" method="post" name="hrms_leave_type_add">
								<div class="row">
									<div class="col-sm-12">
										<section class="panel">
											<div class="panel-body">
												<h4><?php if($mode == 'Add') { echo "New"; }else{ echo "Edit"; } ?> <?= $form ?></h4>
													<div class="col-md-12" style="padding-top: 25px;">
														<div class="col-md-12 margin_row">
															<div class="col-md-6">
													  			<div class="form-group">
													  				<label class="col-md-3 control-label" style="">Leave Type Name*</label>
													  				<div class="col-md-8 col-xs-11">
																		<input type="text" class="form-control" id="leave_type_name" name="leave_type_name" title="Enter Leave Type Name" placeholder="Leave Type Name" value="<?php if($mode=='Edit'){ echo $rel['leave_type_name'];} ?>" required>
																	</div>
													  			</div>
													  		</div>
													  		<div class="col-md-6">
													  			<div class="form-group">
																	<div class="col-md-8 col-xs-11">
													  					<input type="checkbox" name="is_carry_forward_flag" id="is_carry_forward_flag" data-id="is_carry_forward" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['is_carry_forward_flag'] : 'No' ?>" <?php if($rel['is_carry_forward_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">Is Carry Forward</span>
													  				</div>	
													  			</div>
													  		</div>
													  	</div>
													  	<div class="col-md-12 margin_row">
													  		<div class="col-md-6">
													  			<div class="form-group">
													  				<label class="col-md-3 control-label">Max Leaves Allowed</label>
													  				<div class="col-md-8 col-xs-11">
																		<input id="max_leave_allowed" name="max_leave_allowed" type="text" class="form-control" title="Date" placeholder="Max Leaves Allowed" value="<?php if($mode=='Edit'){ echo $rel['max_leave_allowed']; } ?>">
																	</div>
													  			</div>
													  		</div>
													  		<div class="col-md-6">
													  			<div class="form-group">
																	<div class="col-md-8 col-xs-11">
																		<input type="checkbox" name="is_lwp_flag" id="is_lwp_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['is_lwp_flag'] : 'No' ?>" <?php if($rel['is_lwp_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label1">Is Leave Without Pay</span>
																	</div>
																</div>
															</div>
														</div>
														<div class="col-md-12 margin_row">
															<div class="col-md-6">
													  			<div class="form-group">
													  				<label class="col-md-3 control-label">Applicable After (Working Days)</label>
													  				<div class="col-md-8 col-xs-11">
																		<input id="application_after_working" name="application_after_working" type="text" class="form-control" title="Date" placeholder="Applicable After" value="<?php if($mode=='Edit'){ echo $rel['application_after_working'];} ?>">
																	</div>
													  			</div>
													  		</div>
															<div class="col-md-6">
													  			<div class="form-group">
																	<div class="col-md-8 col-xs-11">
																		<input type="checkbox" name="is_optional_leave_flag" id="is_optional_leave_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['is_optional_leave_flag'] : 'No' ?>" <?php if($rel['is_optional_leave_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label2">Is Optional Leave</span>
																	</div>
																</div>
															</div>
														</div>
														<div class="col-md-12 margin_row">
															<div class="col-md-6">
													  			<div class="form-group">
													  				<label class="col-md-3 control-label">Maximum Continuous Days Applicable</label>
													  				<div class="col-md-8 col-xs-11">
																		<input id="max_conti_days" name="max_conti_days" type="text" class="form-control"  placeholder="Maximum Continuous Days Applicable" value="<?php if($mode=='Edit') { echo $rel['max_conti_days']; } ?>">
																	</div>
													  			</div>
													  		</div>
															<div class="col-md-6">
													  			<div class="form-group">
																	<div class="col-md-8 col-xs-11">
													  					<input type="checkbox" name="allow_negative_flag" id="allow_negative_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['allow_negative_flag'] : 'No' ?>" <?php if($rel['allow_negative_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label3">Allow Negative Balance</span>
													  				</div>
													  			</div>
													  		</div>
													  	</div>
													  	<div class="col-md-12 margin_row">
													  		<div class="col-md-6"></div>
													  		<div class="col-md-6">
													  			<div class="form-group">
																	<div class="col-md-8 col-xs-11">
																		<input type="checkbox" name="include_holiday_flag" id="include_holiday_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['include_holiday_flag'] : 'No' ?>" <?php if($rel['include_holiday_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label4"> Include holidays within leaves as leaves</span>
																	</div>
																</div>
															</div>
														</div>
														<div class="col-md-12 margin_row">
															<div class="col-md-6"></div>
															<div class="col-md-6">
													  			<div class="form-group">
																	<div class="col-md-8 col-xs-11">
																		<input type="checkbox" name="is_compensatory_flag" id="is_compensatory_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['is_compensatory_flag'] : 'No' ?>" <?php if($rel['is_compensatory_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label5"> Is Compensatory</span>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</section>
										</div>
									</div>
									<div class="row is_carry_class" style="display: none;">
										<div class="col-sm-12">   
						                    <section class="panel">
												<header class="panel-heading">
					                              <h4>CARRY FORWARD
					                              <span class="tools pull-right">
					                                <a href="javascript:;" class="fa fa-chevron-down"></a>
					                              </span></h4>
					                            </header>
					                            <div class="panel-body dd" id="nestable_list_1">
													<div class="col-md-12 margin_row" >
														<div class="col-md-6">
												  			<div class="form-group">
												  				<label class="col-md-3 control-label" style="font-size: 15px;">Maximum Carry Forwarded Leaves</label>
												  				<div class="col-md-8 col-xs-11">
																	<input id="max_carry_forward_leave" name="max_carry_forward_leave" type="text" class="form-control"  placeholder="Maximum Carry Forwarded Leaves" value="<?php if($mode=='Edit') { echo $rel['max_carry_forward_leave']; } ?>">
																</div>
												  			</div>
												  		</div>
												  	</div>
													<div class="col-md-12 margin_row">
														<div class="col-md-6">
												  			<div class="form-group">
																<label class="col-md-3 control-label" style="font-size: 15px;">Expire Carry Forwarded Leaves (Days)</label>
																<div class="col-md-8 col-xs-11">
																	<input id="expiry_carry_forward_leave" name="expiry_carry_forward_leave" type="text" class="form-control"  placeholder="Expire Carry Forwarded Leaves (Days)" value="<?php if($mode=='Edit') { echo $rel['expiry_carry_forward_leave']; } ?>">
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
					                              ENCASHMENT
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
																	<input type="checkbox" name="encashment_allowed_flag" id="encashment_allowed_flag" value="<?= ($mode == 'Edit') ? $rel['encashment_allowed_flag'] : 'No' ?>" <?php if($rel['encashment_allowed_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label6"> Encashment</span>
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
					                              EARNED LEAVE
					                              <span class="tools pull-right">
					                                <a href="javascript:;" class="fa fa-chevron-down"></a>
					                              </span>
					                            </header>
					                            <div class="panel-body dd" id="nestable_list_3">
					                              	<div class="col-md-12 margin_row">
														<div class="col-md-6">
												  			<div class="form-group">
																<label class="col-md-3 control-label" style="font-size: 15px;"></label>
																<div class="col-md-8 col-xs-11">
																	<input type="checkbox" name="earned_leave_flag" id="earned_leave_flag" value="<?= ($mode == 'Edit') ? $rel['earned_leave_flag'] : 'No' ?>" <?php if($rel['earned_leave_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label6"> Is Earned Leave</span>
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
														<a href="<?= ROOT . HRMS_ROOT . 'hrms_leave_type_list' ?>" type="button" class="btn btn-danger">Cancel</a>
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
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_leave_type.js?<?= time() ?>"></script>
	<script>
		<?php if($mode == 'Edit'){ ?>
			var checkboxChecked = $("#is_carry_forward_flag").is(":checked");
			if(checkboxChecked){
				$(".is_carry_class").css("display","block");
			}else{
				$(".is_carry_class").css("display","none");
			}
		<?php } ?>	
		$('#selectAll').click(function(e){
		    var table= $(e.target).closest('table');
		    $('td input:checkbox',table).prop('checked',this.checked);
		});
		$(".select2").select2({
			width: '100%'
		});
		$(document).ready(function(){
			$(document).on("click","#is_carry_forward_flag", function(){
				var checkboxVal = $("#is_carry_forward_flag").data('id');
				var checkboxChecked = $(this).is(":checked");
				if(checkboxVal == 'is_carry_forward' && checkboxChecked){
					$(".is_carry_class").css("display","block");
					$("#is_carry_forward_flag").val('Yes');
				}else{
					$(".is_carry_class").css("display","none");
					$("#is_carry_forward_flag").val('No');
				}
			});
			$(document).on("click","#is_lwp_flag", function(){
				if($(this).is(":checked")){
					$("#is_lwp_flag").val('Yes');
				}else{
					$("#is_lwp_flag").val('No');
				}
			});
			$(document).on("click","#is_optional_leave_flag", function(){
				if($(this).is(":checked")){
					$("#is_optional_leave_flag").val('Yes');
				}else{
					$("#is_optional_leave_flag").val('No');
				}
			});
			$(document).on("click","#allow_negative_flag", function(){
				if($(this).is(":checked")){
					$("#allow_negative_flag").val('Yes');
				}else{
					$("#allow_negative_flag").val('No');
				}
			});
			$(document).on("click","#include_holiday_flag", function(){
				if($(this).is(":checked")){
					$("#include_holiday_flag").val('Yes');
				}else{
					$("#include_holiday_flag").val('No');
				}
			});	
			$(document).on("click","#is_compensatory_flag", function(){
				if($(this).is(":checked")){
					$("#is_compensatory_flag").val('Yes');
				}else{
					$("#is_compensatory_flag").val('No');
				}
			});
			$(document).on("click","#encashment_allowed_flag", function(){
				if($(this).is(":checked")){
					$("#encashment_allowed_flag").val('Yes');
				}else{
					$("#encashment_allowed_flag").val('No');
				}
			});	
			$(document).on("click","#earned_leave_flag", function(){
				if($(this).is(":checked")){
					$("#earned_leave_flag").val('Yes');
				}else{
					$("#earned_leave_flag").val('No');
				}
			});		
		});
	</script>
</body>
</html>