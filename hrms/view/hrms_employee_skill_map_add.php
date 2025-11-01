<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Hrms Employee Skill Map";
	$companyID = $_SESSION['company_id'];
	$usertype=$_SESSION['user_type'];
	if(strpos($_SERVER['REQUEST_URI'], "hrms_employee_skill_map_edit")==false)
	{
		$mode="Add";
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	}
	else
	{
		$mode="Edit";
		$empskillmap_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select empskillmap.* from hrms_employee_skill_map as empskillmap 
				left join tbl_company as comp on comp.company_id = empskillmap.company_id
				left join hrms_employee_skills as empskills on empskills.emp_skill_map_id = empskillmap.id
				left join hrms_employee_training as emptrainings on emptrainings.emp_skill_map_id = empskillmap.id
		 		where `empskillmap`.`id` = $empskillmap_id and `empskillmap`.`company_id` = $companyID";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../../include/include_css_file.php');?>
	</head>
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
										<li ><a href="<?=ROOT . HRMS_ROOT . 'hrms_employee_skill_map_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="hrms_employee_skill_map_add" action="javascript:;" method="post" name="hrms_employee_skill_map_add">
										<div class="">
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Employee*</label>
												  		<div class="col-md-8 col-xs-11">
															<select id="employee_id" class="select2" name="employee_id" onchange="getEmployeeDesignation()" required>
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
															<select id="designation_id" class="select2" name="designation_id" disabled>
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
															<input type='hidden' name='designation_hidden_id' id='designation_hidden_id' value='' />
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>SKILLS </h4>
											<h6>Employee Skills</h6>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="leave_block_days" class="display table table12 table-striped table-bordered">
														<tr id="field">
															<th width="25%" class="text-center">Skill Name</th>
															<th width="25%" class="text-center">Proficiency</th>
															<th width="25%" class="text-center">Evaluation Date</th>
															<th width="8%" class="text-center">Action</th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td data-label="Skill Name" style="vertical-align:top;">
																<select id="skill_id" class="select2" name="skill_id">
																	<option selected disabled value="">SELECT SKILL</option>
																	<?php
																		$query = $dbcon->query("SELECT `id`,`skill_name` FROM `hrms_skills` WHERE `status` = 0 and company_id = $companyID order by skill_name");
																		while ($r = $query->fetch_assoc()) {
																			if($rel['employee_id'] == $r['id']){
																				$skillIDS = 'selected';
																			}else{
																				$skillIDS = '';
																			}
																			echo '<option value="' . $r['id'] . '" '.$skillIDS.'>' . $r['skill_name'] . '</option>';
																		}
																	?>
																</select>
															</td>
															<td data-label="Proficiency" style="vertical-align:top;">
																<input type="text"  name="proficiency" title="Enter Proficiency" placeholder="Proficiency (Rating Ex. 1.5, 2.5 etc)" id="proficiency" class="form-control" />
															</td>
															<td data-label="Evaluation Date" style="vertical-align:top;">
																<input type="text"  name="evaluation_date" title="Enter Evaluation Date" placeholder="Evaluation Date" id="evaluation_date" class="form-control default-date-picker" />
															</td>
															<td style="vertical-align:top;">
																<input type="button"  name="addskillrow" id="addskillrow" onClick="return add_skill_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>			
												</div>
											</div>
											<div id="hrms_employee_skills_data"></div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>TRAININGS </h4>
											<h6>Trainings</h6>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="leave_block_allow_users" class="display table table12 table-striped table-bordered">
														<tr id="field">
															<th width="35%" class="text-center">Training</th>
															<th width="35%" class="text-center">Training Date</th>
															<th width="8%" class="text-center">Action</th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td data-label="Training" style="vertical-align:top;">
																<select id="training_id" class="select2" name="training_id">
																	<option selected disabled value="">SELECT TRAINING</option>
																	<?php
																		$query = $dbcon->query("SELECT `id`,`training_name` FROM `hrms_trainings` WHERE `status` = 0 and company_id = $companyID order by training_name");
																		while ($r = $query->fetch_assoc()) {
																			if($rel['training_id'] == $r['id']){
																				$trainingIDS = 'selected';
																			}else{
																				$trainingIDS = '';
																			}
																			echo '<option value="' . $r['id'] . '" '.$trainingIDS.'>' . $r['training_name'] . '</option>';
																		}
																	?>
																</select>
															</td>
															<td data-label="Training Date" style="vertical-align:top;">
																<input type="text"  name="training_date" title="Enter Training Date" placeholder="Training Date" id="training_date" class="form-control default-date-picker" />
															</td>
															<td style="vertical-align:top;">
																<input type="button"  name="addtrainingrow" id="addtrainingrow" onClick="return add_training_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>			
												</div>
											</div>
											<div id="hrms_employee_training_data"></div>
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
											<div class="col-md-12">
												<center>
													<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
													<a href="<?=ROOT . HRMS_ROOT . 'hrms_employee_skill_map_list'?>" type="button" class="btn btn-danger">Cancel</a>
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
			<script src="<?=ROOT . HRMS_ROOT ?>js/app/hrms_employee_skill_map.js?<?=time()?>"></script>
		<script>
			//CKEDITOR.replace('quotation_condition');
			$(".select2").select2({
				width: '100%'
			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
		</script>
		<?php if($mode == 'Edit'){ 
				echo "<script>getEmployeeDesignation() </script>";
		} ?>
		<?php 
			echo "<script>show_employee_skill_data() </script>";
			echo "<script>show_employee_training_data() </script>";
		?>
	</body>
</html>
