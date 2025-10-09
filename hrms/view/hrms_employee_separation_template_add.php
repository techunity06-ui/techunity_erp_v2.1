<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Hrms Employee Separation Template";
	$companyID = $_SESSION['company_id'];
	$usertype=$_SESSION['user_type'];
	if(strpos($_SERVER[REQUEST_URI], "hrms_employee_separation_template_edit")==false)
	{
		$mode="Add";
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	}
	else
	{
		$mode="Edit";
		$hrmsempsepalist_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select hrmsempsepalist.*,hrmsdesig.designation_name from hrms_employee_separation_template as hrmsempsepalist 
				left join tbl_company as comp on comp.company_id = hrmsempsepalist.company_id
				left join hrms_designation as hrmsdesig on hrmsdesig.id = hrmsempsepalist.designation_id
				left join hrms_department as hrmsdepart on hrmsdepart.id = hrmsempsepalist.department_id
				left join hrms_emp_grade as hrmsgrade on hrmsgrade.id = hrmsempsepalist.employee_grade_id
		 		where `hrmsempsepalist`.`id` = $hrmsempsepalist_id and `hrmsempsepalist`.`company_id` = $companyID";
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
										<li><a href="<?=ROOT . HRMS_ROOT .'hr_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li ><a href="<?=ROOT . HRMS_ROOT .'hrms_employee_separation_template_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="hrms_employee_separation_template_add" action="javascript:;" method="post" name="hrms_employee_separation_template_add">
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
														  		$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='EMPLOYEE SEPARATION TEMPLATE' and company_id = $companyID and `type_id` = '16' order by invoicetype_id ");
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
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>ACTIVITIES </h4>
											<h6>Activities</h6>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="leave_block_days" class="display table table12 table-striped table-bordered">
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
																<input type="button"  name="addemployeeseparationtemplaterow" id="addemployeeseparationtemplaterow" onClick="return add_employee_separation_template_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>			
												</div>
											</div>
											<div id="hrms_employee_separation_template_data"></div>
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
													<a href="<?=ROOT . HRMS_ROOT .'hrms_employee_separation_template_list'?>" type="button" class="btn btn-danger">Cancel</a>
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
			<script src="<?=ROOT . HRMS_ROOT ?>js/app/hrms_employee_separation_template.js?<?= time() ?>"></script>
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
		<?php 
			echo "<script>show_employee_separation_template_data() </script>";
		?>
	</body>
</html>
