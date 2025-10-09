<?php
session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/payroll_common_functions.php");
include_once("../../include/function_database_query.php");

$form = "Hrms Energy Point Rule";
$mode="Add";
$reference_document_type = '1';
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER[REQUEST_URI], "hrms_energy_point_rule_edit")==true) {
	$mode="Edit";
	$hrmsenergypointrulesID = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from hrms_energy_point_rule where id=$hrmsenergypointrulesID and company_id = $companyID".check_user('hrmsenergypointrule');
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	if($rel){
		
	}else{
		header("Location: " . DOMAIN . HRMS_ROOT . "hrms_energy_point_rule_list");
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php'); ?>
</head>
<style>
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
									<li><a href="<?= ROOT . HRMS_ROOT . 'hrms_energy_point_rule_list' ?>"><?= $form ?></a></li>
								</ul>
							</div>
						</section>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
					<form class="form-horizontal" role="form" id="hrms_energy_point_rule_add" action="javascript:;" method="post" name="hrms_energy_point_rule_add">
						<section class="panel">
							<div class="panel-body">
									<h4><?php if($mode == 'Add') { echo "New"; }else{ echo "Edit"; } ?> <?= $form ?></h4>
									<hr style="margin-top: 20px; margin-bottom:0px; border: 1px solid #eee">
										<div class="col-md-12" style="padding-top: 25px;">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label"></label>
													<div class="col-md-8 col-xs-11">
														<input type="checkbox" name="is_enabled_flag" id="is_enabled_flag" data-id="is_enabled_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['is_enabled_flag'] : 'No' ?>" <?php if($rel['is_enabled_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label"> Enabled</span>
													</div>	
												</div>
											</div>
										</div>
									<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
									<div class="col-md-12">
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Rule Name*</label>
														<div class="col-md-8 col-xs-11">
															<input id="energy_rule_name" name="energy_rule_name" type="text" class="form-control required valid" title="Rule Name" placeholder="Rule Name" value="<?php if($mode=='Edit'){ echo $rel['energy_rule_name']; } ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-2 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="energy_apply_only_once_flag" id="energy_apply_only_once_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['energy_apply_only_once_flag'] : 'No' ?>" <?php if($rel['energy_apply_only_once_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label1"> Apply Only Once</span>
															<p>Apply this rule only once per document</p>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
												  <div class="form-group">
												  		<label class="col-md-4 control-label">Reference Document Type*</label>
														<div class="col-md-8 col-xs-11">
															<select id="reference_document_type_id" class="select2" name="reference_document_type_id" required onChange="load_user_fields(this.value,'energy_user_field','')">
																<option selected disabled value="">SELECT REFERENCE DOCUMENT</option>
																<?php
																$query = $dbcon->query("SELECT `id`,`reference_document_type_name` FROM `hrms_reference_document_type` WHERE `status` = 0 and company_id = $companyID order by reference_document_type_name");
																while ($r = $query->fetch_assoc()) {
																	if($rel['reference_document_type_id'] == $r['id']){
																		$referencedocumentIDS = 'selected';
																	}else{
																		$referencedocumentIDS = '';
																	}
																	echo '<option value="' . $r['id'] . '" '.$referencedocumentIDS.'>' . $r['reference_document_type_name'] . '</option>';
																}
																?>
															</select>
														</div>
												  </div>							 
											 	</div>
												<div class="col-md-6"></div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">For Document Event</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" id="for_document_event_id" name="for_document_event_id" placeholder="For Document Event">
																<option selected disabled value="">SELECT FOR DOCUMENT EVENT</option>
																<option value="0" <?php if($rel['for_document_event'] == '0') { echo 'selected'; } ?>>New</option>
																<option value="1" <?php if($rel['for_document_event'] == '1') { echo 'selected'; } ?>>Submit</option>
																<option value="2" <?php if($rel['for_document_event'] == '2') { echo 'selected'; } ?>>Cancel</option>
																<option value="3" <?php if($rel['for_document_event'] == '3') { echo 'selected'; } ?>>Modified By</option>
																<option value="4" <?php if($rel['for_document_event'] == '4') { echo 'selected'; } ?>>Owner</option>
																<option value="5" <?php if($rel['for_document_event'] == '5') { echo 'selected'; } ?>>Custom</option>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-6"></div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Points*</label>
														<div class="col-md-8 col-xs-11">
															<input id="energy_points" name="energy_points" type="text" class="form-control required valid" title="Points" placeholder="Points" value="<?php if($mode=='Edit'){ echo $rel['energy_points']; } ?>">
														</div>
													</div>
												</div>
												<div class="col-md-6"></div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label"></label>
														<div class="col-md-8 col-xs-11">
															<input type="checkbox" name="allot_points_to_assigned_users_flag" id="allot_points_to_assigned_users_flag" value="<?= ($mode == 'Edit') ? $rel['allot_points_to_assigned_users_flag'] : 'No' ?>" <?php if($rel['allot_points_to_assigned_users_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label4">  Allot Points To Assigned Users</span>
															<p>Users assigned to the reference document will get points.</p>
														</div>
													</div>
												</div>
												<div class="col-md-6"></div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">User Field</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="energy_user_field_id" id="energy_user_field_id">
																<option value="">SELECT USER FIELD</option>	
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-6"></div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Multiplier Field</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="energy_multiplier_field_id" id="energy_multiplier_field_id">
																<option value="">SELECT MULTIPLIER FIELD</option>	
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-6"></div>
											</div>
											<div class="col-md-12 margin_row" id="maximum_points_div" style="display: none">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Maximum Points</label>
														<div class="col-md-8 col-xs-11">
															<input id="energy_maximum_points" name="energy_maximum_points" type="text" class="form-control required valid" title="Maximum Points" placeholder="Maximum Points" value="<?php if($mode=='Edit'){ echo $rel['energy_maximum_points']; } ?>">
															<p>Maximum points allowed after multiplying points with the multiplier value (Note: For no limit leave this field empty or set 0)</p>
														</div>
													</div>
												</div>
												<div class="col-md-6"></div>
											</div>
										</div>
									</div>
								</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<section class="panel">
									<header class="panel-heading" style="font-size: 18px;">
										CONDITION
										<span class="tools pull-right">
											<a href="javascript:;" class="fa fa-chevron-down"></a>
										</span>
									</header>
									<div class="panel-body" id="condition">
										<div class="col-md-12 margin_row">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Condition</label>
													<div class="col-md-8 col-xs-11">
														<textarea style="border: 1px solid #ccc;" id="energy_condition" name="energy_condition" placeholder="Condition" rows="8" cols="72"><?php if($mode=='Edit') { echo $rel['energy_condition']; } ?></textarea>
														<p>If the condition is satisfied user will be rewarded with the points. eg. doc.status == 'Closed'</p>
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
													<label class="col-md-4 control-label">Status</label>
													<div class="col-md-8 col-xs-11">
														<select id="status" class="select2" name="status" required>
															<option selected disabled value="">SELECT STATUS</option>
															<option value="0" <?php if($rel['status'] == '0') { echo 'selected'; } ?>>Active</option>
															<option value="1" <?php if($rel['status'] == '1') { echo 'selected'; } ?>>InActive</option>
														</select>
													</div>
												</div>
											</div>
										</div><br><br>
										<div class="col-md-12 margin_row text-center">
											<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
											<a href="<?= ROOT . HRMS_ROOT . 'hrms_energy_point_rule_list' ?>" type="button" class="btn btn-danger">Cancel</a>
										</div>
									</div>
								</section>
							</div>
						</div>
						<input type='hidden' name='mode' id='mode' value='<?= $mode ?>' />
						<input type='hidden' name='eid' id='eid' value='<?= $rel['id'] ?>' />
						<input type="hidden" name="row_cnt" id="row_cnt" value="<?= ($mode == 'Edit') ? $ecount : '0' ?>">
				</section>
				</form>
			</div>
			</div>
		</section>
	</section>
	<?php include_once('../../include/footer.php'); ?>
	</section>
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_energy_point_rule.js?<?= time() ?>"></script>
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
      	<?php if($mode == 'Edit'){ ?>
			var checkboxChecked = $("#allot_points_to_assigned_users_flag").is(":checked");
			if(checkboxChecked){
				$("#maximum_points_div").css("display","block");
			}else{
				$("#maximum_points_div").css("display","none");
			}
			var energyMultiplier = $("#energy_multiplier_field").val();
			if(energyMultiplier != ""){
				$("#maximum_points_div").css("display","block");
			}else{
				$("#maximum_points_div").css("display","none");
			}
		<?php } ?>
      	$(document).ready(function(){
      		$(document).on("change","#energy_multiplier_field", function(){
				if($(this).val() != ""){
					$("#maximum_points_div").css("display","block");
				}else{
					$("#maximum_points_div").css("display","none");
				}
			});
			$(document).on("click","#is_enabled_flag", function(){
				if($(this).is(":checked")){
					$("#is_enabled_flag").val('Yes');
				}else{
					$("#is_enabled_flag").val('No');
				}
			});
			$(document).on("click","#energy_apply_only_once_flag", function(){
				if($(this).is(":checked")){
					$("#energy_apply_only_once_flag").val('Yes');
				}else{
					$("#energy_apply_only_once_flag").val('No');
				}
			});
			$(document).on("click","#allot_points_to_assigned_users_flag", function(){
				if($(this).is(":checked")){
					$("#allot_points_to_assigned_users_flag").val('Yes');
					$("#maximum_points_div").css("display","block");
				}else{
					$("#allot_points_to_assigned_users_flag").val('No');
					$("#maximum_points_div").css("display","none");
				}
			});
      	});
	</script>
	<?php
		if($mode=="Edit"){
			echo "<script>load_user_fields(".$rel['reference_document_type_id'].",'reference_document_type_id',".$rel['energy_user_field_id'].",".$rel['energy_multiplier_field_id'].")</script>";
		}
	?>
</body>
</html>