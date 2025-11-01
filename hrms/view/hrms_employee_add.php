<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	include_once("../../include/hrms_common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="HRMS Employee";
	$userID = $_SESSION['user_id'];
	$companyID = $_SESSION['company_id'];
	if(strpos($_SERVER['REQUEST_URI'], "hrms_employee_edit")==false) {
		$mode="Add";
		$countryid="101";
		$stateid="1";
		$cityid="1";
	}
	else {
		$mode="Edit";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="SELECT hrmsemp.id as empid,hrmsemp.*, joindetail.*, departdetail.*, attendetail.*, salarydetail.*, contactdetail.*, personaldetail.*, educationdetail.*, previouswork.*, historywork.*, exitwork.*,usr.template_access_perm_id FROM hrms_employee as hrmsemp
				left join tbl_ledger as ledge on ledge.employee_id=hrmsemp.id
				left join users as usr ON usr.employee_id = ledge.l_id 
				left join tbl_company as comp on comp.company_id=hrmsemp.company_id
				left join hrms_employee_joining_details as joindetail on joindetail.employee_id=hrmsemp.id 
				left join hrms_employee_department_grade_details as departdetail on departdetail.employee_id=hrmsemp.id
				left join hrms_employee_attendance_leave_details as attendetail on attendetail.employee_id=hrmsemp.id 
				left join hrms_employee_salary_details as salarydetail on salarydetail.employee_id=hrmsemp.id 
				left join hrms_employee_contact_details as contactdetail on contactdetail.employee_id=hrmsemp.id   
				left join hrms_employee_personal_details as personaldetail on personaldetail.employee_id=hrmsemp.id 
				left join hrms_employee_education_details as educationdetail on educationdetail.employee_id=hrmsemp.id 
				left join hrms_employee_previous_work_experience_details as previouswork on previouswork.employee_id=hrmsemp.id
				left join hrms_employee_history_company_details as historywork on historywork.employee_id=hrmsemp.id
				left join hrms_employee_exit_details as exitwork on exitwork.employee_id=hrmsemp.id 
				WHERE hrmsemp.status IN('0','1') AND hrmsemp.id=$id";
		$tr = $dbcon->query($query);
		if($tr->num_rows <= 0) {
			header("Location: " . DOMAIN . HRMS_ROOT . "hrms_employee");
		}
		$rel=mysqli_fetch_assoc($tr);
		$countryid=$rel['country_id'];
		$stateid=$rel['state_id'];
		$cityid=$rel['city_id'];
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php');?>
	<style type="text/css">
		.margin_row {
			margin-top:10px !important;
		}
		.datepicker td.disabled {
			color: #ccc;
		}
		.cke_chrome{
			border: 1px solid #d1d1d1 !important;
		}	
	</style>
	<script type="text/javascript" src="<?php echo ROOT . HRMS_ROOT ?>js/jquery.form.min.js"></script>
</head>
<body>
<section id="container" class="sidebar-closed">
    <?php include_once('../../include/include_top_menu.php');?>
    <!--sidebar start-->
    <?php include_once('../../include/left_menu.php');?>
    <!--sidebar end-->
    
    <!--main content start-->
    <section id="main-content">
        <section class="wrapper">
			<div class="row">
			  	<div class="col-lg-12">
				  <!--breadcrumbs start -->
					<section class="panel">
						<header class="panel-heading">
						  <h3>New <?=$form?>
						  
						  </h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT . HRMS_ROOT . 'hr_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li class="active"><a href="<?=ROOT . HRMS_ROOT . 'hrms_employee'?>"><?=$form?> List </a></li>
						  </ul>
						</div>
					</section>
				  <!--breadcrumbs end -->
			  	</div>	
             </div>
              <!--Customer overview start-->
			<form role="form" id="hrms_employee_add" action="javascript:;" method="post" name="hrms_employee_add" enctype="multipart/form-data">
			  	<div class="row">
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
							  New <?=$form?> 
								<span class="tools pull-right">
									<a href="javascript:;" class="fa fa-chevron-down"></a>
								</span>
							</header>	
							<div class="panel-body">
								<div class="col-md-12" style="padding-top: 25px;">
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
										  		<label for="series_id" class="col-md-4 control-label">Series*</label>
											  	<div class="col-md-8 col-xs-11">
											  		<?php $series_id = ($mode == "Edit" && $rel['series_id']) ? $rel['series_id'] : get_series_by_type($dbcon, 'EMPLOYEE', '16'); ?>
											  		<input type="text" class="form-control" id="series_id" name="series_id" value="<?php echo $series_id; ?>" readonly />
											  	</div>
											</div>							 
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6 typeled">
											<div class="form-group">
										  		<label for="employee_name" class="col-md-4 control-label">Employee Name*</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="employee_name" name="employee_name" placeholder="Enter Employee Name" value="<?php echo $rel['employee_name']; ?>" />
											  	</div>
												  
											</div>							 
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Profile Photo* </label>
												<div class="col-md-8 col-xs-12">
													<div class="col-md-7">
														<input type="file" id="emp_profile_img" name="emp_profile_img"  title="Select Profile Photo" <?php if($mode=='Add') { ?>required <?php } ?> >
													</div>
													<div class="col-md-1">
														<?php if($mode=='Edit') { ?>
															<img src="<?php if(isset($rel['emp_profile_img']) && !empty($rel['emp_profile_img'])){ echo ROOT . HRMS_ROOT .'upload/emp_profile_image/'.$rel['emp_profile_img']; } else { echo ROOT . HRMS_ROOT .'upload/emp_profile_image/no_profile.png'; } ?>" width="50" height="50" />
														<?php } ?>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
											  	<label for="birth_date" class="col-md-4 control-label">Birth Date*</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control datepickerPrev" id="birth_date" name="birth_date" placeholder="Enter Birth Date" value="<?=($rel['birth_date'] && $rel['birth_date'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['birth_date'])) : ''?>" autocomplete="off" />
											  	</div>
											</div>
										</div>
										<div class="col-md-6 typeled">
											<div class="form-group">
										  		<label for="joining_date" class="col-md-4 control-label">Joining Date*</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control datepicker" id="joining_date" name="joining_date" placeholder="Enter Joining Date" value="<?=($rel['joining_date'] && $rel['joining_date'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['joining_date'])) : ''?>" autocomplete="off" />
											  	</div>
												  
											</div>							 
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Select Country *</label>
												<div class="col-md-8 col-xs-11">
													<select class="select2" name="country_id" id="countryid" onChange="load_state(this.value,'stateid','')">
														<?=get_country($dbcon,$countryid)?>				
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-6">
											<div class="form-group">
												<label for="gender" class="col-md-4 control-label">Gender*</label>
											  	<div class="col-md-8 col-xs-11">
													<select class="select2" id="gender" name="gender">
														<?php $gender = $rel['gender'];
														$ms = ($gender == 'male' || $gender == 'MALE') ? 'selected' : '';
														$fs = ($gender == 'female' || $gender == 'FEMALE') ? 'selected' : '';
														$os = ($gender == 'other' || $gender == 'OTHER') ? 'selected' : ''; ?>
														<option value="">Please Select</option>
														<option value="male" <?php echo $ms; ?>>Male</option>
														<option value="female" <?php echo $fs; ?>>Female</option>
														<option value="other" <?php echo $os; ?>>Other</option>
													</select>	
											  	</div>  	
											</div>							 
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Select State *</label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="state_id" id="stateid" onChange="load_city(this.value,'cityid','')">
														<option value="">Select State</option>	
													</select>
												</div>
												<div class="col-md-2">
													<input type="button"  name="addState" id="addState" data-toggle="modal" data-target="" onclick="add_state();" class="btn btn-primary" value="+ Add State"/>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Select City *</label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="city_id" id="cityid">
														<option value="">Select City</option>	
													</select>
												</div>
												<div class="col-md-2">
												<input type="button" name="addCity" id="addCity" data-toggle="modal" data-target="" onclick="add_city();" class="btn btn-primary" value="+ Add city"/>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Pin Code</label>
												<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" placeholder="Customer Pincode" name="cust_pincode" id="cust_pincode"   value="<?=$rel['cust_pincode']?>"  />
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">PAN / IT No.</label>
												<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" placeholder="Customer PAN" name="m_pan" id="m_pan"   value="<?=$rel['m_pan']?>" style="text-transform:uppercase"  />
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Email(User name)*</label>
												<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" placeholder="Email" title="Email" name="emp_email" id="emp_email" value="<?=$rel['emp_email']?>" onkeyup="checkUsername(this.value)" required />
													
													<input type="hidden" class="form-control" placeholder="Email" title="Email" name="" id="emp_email_hid" value="<?=$rel['emp_email']?>"   />
													
													<div id="user_error"></div>
												</div>	
											</div> 
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Password</label>
												<div class="col-md-8 col-xs-11">
													<input type="password" class="form-control" placeholder="Password" title="Password" name="emp_password" id="emp_password" <?=($mode=='Add')?'required':''?>  />
												</div>	
											</div> 
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Mobile No. </label>
												<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" placeholder="Mobile No." name="emp_mobile" id="emp_mobile" value="<?=$rel['emp_mobile']?>" required  />
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Zone</label>
												<div class="col-md-8 col-xs-11">
													<select class="select2" name="emp_zone_id" id="emp_zone_id" onchange="get_branch_by_zone(this.value,'branch_id_emp')" required>
														<?=get_zone($dbcon,$rel['emp_zone_id'])?>				
													</select>
												</div>	
											</div>
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Branch*</label>
												<div class="col-md-8 col-xs-11">
													<select class="select2" name="branch_id_emp" id="branch_id_emp" required>
																		
													</select>
												</div>	
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Employee User Type*</label>
												<div class="col-md-8 col-xs-11">
													<select class="select2" name="emp_user_type" id="emp_user_type" title="Select Type" required>
														<option value="">--Select User Type--</option>
														<?=getusertype($dbcon,$rel['emp_user_type']," and (usertype_id!=1 or company_id=".$_SESSION['company_id'].")")?>			
													</select>
												</div>	
											</div>
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Allocated State</label>
												<div class="col-md-8 col-xs-11">
													<select class="select2" name="alloc_stateid[]" id="alloc_stateid" onChange="load_city_all();" placeholder="Allocated State" multiple>
														<?=get_state_all($dbcon,$rel['alloc_state_id'],"101")?>				
													</select>
												</div>	
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Allocated City</label>
												<div class="col-md-8 col-xs-11">
													<select class="select2" name="alloc_cityid[]" id="alloc_cityid" placeholder="Allocated City" multiple>
														<?=get_city_all($dbcon,$rel['alloc_city_id'],$rel['alloc_state_id'])?>	
													</select>
												</div>	
											</div>
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Report To User-Type</label>
												<div class="col-md-8 col-xs-11">
													<select class="select2" name="report_to_user_type" id="report_to_user_type" title="Select Type" onchange="load_report_to_users(this.value)">
														<option value="">--Select User Type--</option>
														<?=getusertype($dbcon,$rel['report_to_user_type']," and (usertype_id!=1 or company_id=".$_SESSION['company_id'].")")?>			
													</select>
												</div>	
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Report To User</label>
												<div class="col-md-8 col-xs-11">
													<select class="select2" name="report_to_user_id" id="report_to_user_id" >
														<?=get_users_typewise($dbcon,$rel['report_to_user_id']," and user_type=".$rel['report_to_user_type'])?>			
													</select>
												</div>	
											</div>
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												  <label class="col-md-4 control-label">Opening Balance</label>
												  <div class="col-md-8 col-xs-11">
														<input type="number"  class="form-control" id="opn_balance" name="open_balance" placeholder="" min="0" max="" value="<?php echo $rel['open_balance']; ?>" />
												  </div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												  <label class="col-md-4 control-label">Balance Type</label>
												  <div class="col-md-8 col-xs-11">
														<select class="select2" name="balance_typeid" id="balance_typeid" title="Select Type">
															<?=getbalance_type($dbcon,$rel['balance_typeid'])?>				
														</select>
												  </div>
											</div>
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												  <label class="col-md-4 control-label">Per Day Salary</label>
												  <div class="col-md-8 col-xs-11">
														<input type="number"  class="form-control" id="per_day_salary" name="per_day_salary" placeholder="" min="0" max="" value="<?php if($mode=='Edit'){ echo $rel['per_day_salary']; }else{ echo '0'; } ?>" />
												  </div>
											</div>
										</div>
										<div class="col-md-6">
											<label for="template_name" class="col-md-4 control-label">Template Name</label>
											<div class="col-md-8 col-xs-11">
												<select class="select2" id="template_id" name="template_id">
											  			<option selected disabled value="">SELECT TEMPLATE NAME</option>
														<?php
															echo getTemplateName($dbcon, $rel['template_access_perm_id']);
														?>
											  	</select>
											</div>							 
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="status" class="col-md-4 control-label">Status*</label>
											  	<div class="col-md-8 col-xs-11">
													<select class="select2" id="status" name="status">
														<?php echo getStatusOptions($rel['status']); ?>
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
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
							  Emergency Contact 
								<span class="tools pull-right">
									<a href="javascript:;" class="fa fa-chevron-down"></a>
								</span>
							</header>	
							<div class="panel-body">
								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
										  		<label for="emergenecy_contact_name" class="col-md-4 control-label">Contact Name*</label>
											  	<div class="col-md-8 col-xs-11">
											  		<input type="text" class="form-control" id="emergenecy_contact_name" name="emergenecy_contact_name" placeholder="Enter Emergenecy Contact Name" value="<?php echo $rel['emergenecy_contact_name']; ?>" />
											  	</div>
											</div>							 
										</div>
										<div class="col-md-6 typeled">
											<div class="form-group">
										  		<label for="emergenecy_contact_number" class="col-md-4 control-label">Contact Number*</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="emergenecy_contact_number" name="emergenecy_contact_number" placeholder="Enter Emergenecy Contact Number" value="<?php echo $rel['emergenecy_contact_number']; ?>" />
											  	</div>
												  
											</div>							 
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
										  		<label for="relation" class="col-md-4 control-label">Relation</label>
											  	<div class="col-md-8 col-xs-11">
											  		<input type="text" class="form-control" id="relation" name="relation" placeholder="Enter Relation" value="<?php echo $rel['relation']; ?>" />
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
							  JOINING DETAILS
							<span class="tools pull-right">
								<a href="#demo" class="fa fa-chevron-down" data-toggle="collapse"></a>
							</span>
							</header>	
							<div class="panel-body collapse" id="demo">
								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="job_applicant_id" class="col-md-4 control-label">Job Applicant</label>
											  	<div class="col-md-8 col-xs-11">
													<select class="select2" id="job_applicant_id" name="job_applicant_id">
														<option value="">SELECT JOB APPLICANT</option>
													</select>	
											  	</div>  	
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
											  	<label for="contract_end_date" class="col-md-4 control-label">Contract End Date</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control datepicker" id="contract_end_date" name="contract_end_date" placeholder="Enter Contract End Date" value="<?=($rel['contract_end_date'] && $rel['contract_end_date'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['contract_end_date'])) : ''?>" autocomplete="off" />
											  	</div>
											</div>
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
											  	<label for="offer_date" class="col-md-4 control-label">Offer Date</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control datepicker" id="offer_date" name="offer_date" placeholder="Enter Offer Date" value="<?=($rel['offer_date'] && $rel['offer_date'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['offer_date'])) : ''?>" autocomplete="off" />
											  	</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
										  		<label for="notice" class="col-md-4 control-label">Notice (Days)</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="notice" name="notice" placeholder="Enter Notice (Days)" value="<?php echo $rel['notice']; ?>" />
											  	</div>
												  
											</div>							 
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
											  	<label for="confirmation_date" class="col-md-4 control-label">Confirmation Date</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control datepicker" id="confirmation_date" name="confirmation_date" placeholder="Enter Confirmation Date" value="<?=($rel['confirmation_date'] && $rel['confirmation_date'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['confirmation_date'])) : ''?>" autocomplete="off" />
											  	</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
											  	<label for="date_of_retirement" class="col-md-4 control-label">Date Of Retirement</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control datepicker" id="date_of_retirement" name="date_of_retirement" placeholder="Enter Date Of Retirement" value="<?=($rel['date_of_retirement'] && $rel['date_of_retirement'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['date_of_retirement'])) : ''?>" autocomplete="off" />
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
							  DEPARTMENT AND GRADE
							<span class="tools pull-right">
								<a href="#department" class="fa fa-chevron-down" data-toggle="collapse"></a>
							</span>
							</header>	
							<div class="panel-body collapse" id="department">
								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="department_id" class="col-md-4 control-label">Department</label>
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
												<label for="grade_id" class="col-md-4 control-label">Grade</label>
												<div class="col-md-8 col-xs-11">
													<select id="grade_id" class="select2" name="grade_id">
														<option selected disabled value="">SELECT GRADE</option>
														<?php
															$query = $dbcon->query("SELECT `id`,`employee_grade_name` FROM `hrms_emp_grade` WHERE `company_id` = $companyID and `status` = 0  order by id ");
															while ($r = $query->fetch_assoc()) {
																if($rel['grade_id'] == $r['id']){
																	$gradeIDS = 'selected';
																}else{
																	$gradeIDS = '';
																}
																echo '<option value="' . $r['id'] . '" '.$gradeIDS.'>' .$r['employee_grade_name']. '</option>';
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
												<label for="designation_id" class="col-md-4 control-label">Designation</label>
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
										<div class="col-md-6">
											<div class="form-group">
												<label for="branch_id" class="col-md-4 control-label">Branch</label>
												<div class="col-md-8 col-xs-11">
													<select id="branch_id" class="select2" name="branch_id">
														<option selected disabled value="">SELECT BRANCH</option>
														<?php
															$query = $dbcon->query("SELECT `branch_id`,`branch_name` FROM `branch_mst` WHERE `company_id` = $companyID and `branch_status` = 0  order by branch_id ");
															while ($r = $query->fetch_assoc()) {
																if($rel['branch_id'] == $r['branch_id']){
																	$branchIDS = 'selected';
																}else{
																	$branchIDS = '';
																}
																echo '<option value="' . $r['branch_id'] . '" '.$branchIDS.'>' .$r['branch_name']. '</option>';
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
												<label for="report_to_id" class="col-md-4 control-label">Report To</label>
												<div class="col-md-8 col-xs-11">
													<select id="report_to_id" class="select2" name="report_to_id">
														<option selected disabled value="">SELECT REPORT TO</option>
														<?php
															$query = $dbcon->query("SELECT `l_id`,`l_name` FROM `tbl_ledger` WHERE `company_id` = $companyID and `l_status` = 0 and `l_group` = '58' order by l_name ");
															while ($r = $query->fetch_assoc()) {
																if($rel['report_to_id'] == $r['l_id']){
																	$reportIDS = 'selected';
																}else{
																	$reportIDS = '';
																}
																echo '<option value="' . $r['l_id'] . '" '.$reportIDS.'>' . $r['l_name'] . '</option>';
															}
														?>
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
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
							  ATTENDANCE AND LEAVE DETAILS
							<span class="tools pull-right">
								<a href="#attendance" class="fa fa-chevron-down" data-toggle="collapse"></a>
							</span>
							</header>	
							<div class="panel-body collapse" id="attendance">
								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
										  <div class="form-group">
										  		<label for="leave_policy_id" class="col-md-4 control-label">Leave Policy</label>
												<div class="col-md-8 col-xs-11">
													<select id="leave_policy_id" class="select2" name="leave_policy_id">
														<option selected disabled value="">SELECT LEAVE POLICY</option>
														<?php
															$query = $dbcon->query("SELECT `id` FROM `hrms_leave_policy` WHERE `status` = 0 and company_id = $companyID order by id");
															while ($r = $query->fetch_assoc()) {
																if($rel['leave_policy_id'] == $r['id']){
																	$leavePolicyIDS = 'selected';
																}else{
																	$leavePolicyIDS = '';
																}
																echo '<option value="' . $r['id'] . '" '.$leavePolicyIDS.'>' . $r['id'] .'- HRMS-LP'. '</option>';
															}
														?>
													</select>
												</div>
										  </div>							 
										</div>
										<div class="col-md-6">
										  <div class="form-group">
										  		<label for="holiday_list_id" class="col-md-4 control-label">Holiday List (Applicable Holiday List)</label>
												<div class="col-md-8 col-xs-11">
													<select id="holiday_list_id" class="select2" name="holiday_list_id">
														<option selected disabled value="">SELECT HOLIDAY LIST</option>
														<?php
															$query = $dbcon->query("SELECT `id`,`holiday_name` FROM `hrms_holiday_list` WHERE `status` = 0 and company_id = $companyID order by id");
															while ($r = $query->fetch_assoc()) {
																if($rel['holiday_list_id'] == $r['id']){
																	$holidayListIDS = 'selected';
																}else{
																	$holidayListIDS = '';
																}
																echo '<option value="' . $r['id'] . '" '.$holidayListIDS.'>' . $r['holiday_name'] . '</option>';
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
										  		<label for="attendance_device_id" class="col-md-4 control-label">Attendance Device ID (Biometric/RF tag ID)</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="attendance_device_id" name="attendance_device_id" placeholder="Enter Attendance Device ID (Biometric/RF tag ID)" value="<?php echo $rel['attendance_device_id']; ?>" />
											  	</div>
											</div>							 
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="shift_type_id" class="col-md-4 control-label">Shift Type</label>
												<div class="col-md-8 col-xs-11">
													<select id="shift_type_id" class="select2" name="shift_type_id">
														<option selected disabled value="">SELECT SHIFT TYPE</option>
														<?php
														$query = $dbcon->query("SELECT `id`, `shift_type_name` FROM `hrms_shift_type` WHERE `company_id` = $companyID and `status` = 0 order by shift_type_name ");
														while ($r = $query->fetch_assoc()) {
															if($rel['holiday_list_id'] == $r['id']){
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
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="leave_approver_id" class="col-md-4 control-label">Leave Approver</label>
										  		<div class="col-md-8 col-xs-11">
													<select id="leave_approver_id" class="select2" name="leave_approver_id">
														<option selected disabled value="">SELECT LEAVE APPROVER</option>
														<?php
														$query = $dbcon->query("SELECT `l_id`,`l_name` FROM `tbl_ledger` WHERE `l_status` = 0 and company_id = $companyID and `l_group` = '58' order by l_name");
														while ($r = $query->fetch_assoc()) {
															if($rel['leave_approver_id'] == $r['l_id']){
																$leaveapproverIDS = 'selected';
															}else{
																$leaveapproverIDS = '';
															}
															echo '<option value="' . $r['l_id'] . '" '.$leaveapproverIDS.'>' . $r['l_name'] . '</option>';
														}
														?>
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
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
							  SALARY DETAILS
							<span class="tools pull-right">
								<a href="#salarydetail" class="fa fa-chevron-down" data-toggle="collapse"></a>
							</span>
							</header>	
							<div class="panel-body collapse" id="salarydetail">
								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
								 			<label for="salary_mode" class="col-md-4 control-label">Salary Mode</label>
								 			<div class="col-md-8 col-xs-11">
												<select id="salary_mode" class="select2" name="salary_mode">
													<option selected disabled value="">SELECT SALARY MODE</option>
													<option value="bank" <?php if($rel['salary_mode'] == 'bank') { echo 'selected'; } ?>>Bank</option>
													<option value="cash" <?php if($rel['salary_mode'] == 'cash') { echo 'selected'; } ?>>Cash</option>
													<option value="cheque" <?php if($rel['salary_mode'] == 'cheque') { echo 'selected'; } ?>>Cheque</option>
												</select>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="expense_approver_id" class="col-md-4 control-label">Expense Approver</label>
											  	<div class="col-md-8 col-xs-11">
													<select class="select2" id="expense_approver_id" name="expense_approver_id">
														<option value="">SELECT EXPENSE APPROVER</option>
													</select>	
											  	</div>  	
											</div>
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="payroll_cost_center_id" class="col-md-4 control-label">Payroll Cost Center</label>
											  	<div class="col-md-8 col-xs-11">
													<select class="select2" id="payroll_cost_center_id" name="payroll_cost_center_id">
														<option value="">SELECT PAYROLL COST CENTER</option>
													</select>	
											  	</div>  	
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
										  		<label for="pan_number" class="col-md-4 control-label">PAN Number</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="pan_number" name="pan_number" placeholder="Enter PAN Number" value="<?php echo $rel['pan_number']; ?>" />
											  	</div>
											</div>							 
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
										  		<label for="provident_fund_account" class="col-md-4 control-label">Provident Fund Account</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="provident_fund_account" name="provident_fund_account" placeholder="Enter Provident Fund Account" value="<?php echo $rel['provident_fund_account']; ?>" />
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
							  HEALTH INSURANCE
							<span class="tools pull-right">
								<a href="#healthinsurance" class="fa fa-chevron-down" data-toggle="collapse"></a>
							</span>
							</header>	
							<div class="panel-body collapse" id="healthinsurance">
								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="health_insurance_id" class="col-md-4 control-label">Health Insurance Provider</label>
										  		<div class="col-md-8 col-xs-11">
													<select id="health_insurance_id" class="select2" name="health_insurance_id">
														<option selected disabled value="">SELECT HEALTH INSURANCE</option>
														<?php
														$query = $dbcon->query("SELECT `id`,`health_insurance_name` FROM `hrms_emp_health_insurance` WHERE `status` = 0 and company_id = $companyID order by health_insurance_name");
														while ($r = $query->fetch_assoc()) {
															if($rel['health_insurance_id'] == $r['id']){
																$healthinsuranceIDS = 'selected';
															}else{
																$healthinsuranceIDS = '';
															}
															echo '<option value="' . $r['id'] . '" '.$healthinsuranceIDS.'>' . $r['health_insurance_name'] . '</option>';
														}
														?>
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
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
							  CONTACT DETAILS
							<span class="tools pull-right">
								<a href="#contactdetail" class="fa fa-chevron-down" data-toggle="collapse"></a>
							</span>
							</header>	
							<div class="panel-body collapse" id="contactdetail">
								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
										  		<label for="employee_mobile" class="col-md-4 control-label">Personel Mobile</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="employee_mobile" name="employee_mobile" placeholder="Enter Personel Mobile" value="<?php echo $rel['employee_mobile']; ?>" />
											  	</div>
											</div>							 
										</div>
										<div class="col-md-6">
								 			<label for="prefered_contact_email_type" class="col-md-4 control-label">Prefered Contact Email</label>
								 			<div class="col-md-8 col-xs-11">
												<select id="prefered_contact_email_type" class="select2" name="prefered_contact_email_type">
													<option selected disabled value="">SELECT PREFERED CONTACT EMAIL</option>
													<option value="comp_email" <?php if($rel['prefered_contact_email_type'] == 'comp_email') { echo 'selected'; } ?>>Company Email</option>
													<option value="per_email" <?php if($rel['prefered_contact_email_type'] == 'per_email') { echo 'selected'; } ?>>Personel Email</option>
													<option value="user_id" <?php if($rel['prefered_contact_email_type'] == 'user_id') { echo 'selected'; } ?>>User ID</option>
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
										  		<label for="employee_personal_email" class="col-md-4 control-label">Personel Email</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="employee_personal_email" name="employee_personal_email" placeholder="Enter Personel Email" value="<?php echo $rel['employee_personal_email']; ?>" />
											  	</div>
											</div>							 
										</div>
										<div class="col-md-6">
											<div class="form-group">
										  		<label for="company_email" class="col-md-4 control-label">Company Email</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="company_email" name="company_email" placeholder="Enter Company Email" value="<?php echo $rel['company_email']; ?>" />
											  	</div>
											</div>							 
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label"></label>
												<div class="col-md-8 col-xs-11">
													<input type="checkbox" name="employee_unsubscribed_flag" id="employee_unsubscribed_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['employee_unsubscribed_flag'] : 'No' ?>" <?php if($rel['employee_unsubscribed_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">  Unsubscribed</span>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label"></label>
												<div class="col-md-8 col-xs-11">Provide Email Address registered in company</div>
											</div>
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
								 			<label for="permenent_address_type" class="col-md-4 control-label">Permanent Address Is</label>
								 			<div class="col-md-8 col-xs-11">
												<select id="permenent_address_type" class="select2" name="permenent_address_type">
													<option selected disabled value="">SELECT PERMANENT ADDRESS IS</option>
													<option value="rented" <?php if($rel['permenent_address_type'] == 'rented') { echo 'selected'; } ?>>Rented</option>
													<option value="owned" <?php if($rel['permenent_address_type'] == 'owned') { echo 'selected'; } ?>>Owned</option>
												</select>
											</div>
										</div>
										<div class="col-md-6">
								 			<label for="current_address_type" class="col-md-4 control-label">Current Address Is</label>
								 			<div class="col-md-8 col-xs-11">
												<select id="current_address_type" class="select2" name="current_address_type">
													<option selected disabled value="">SELECT CURRENT ADDRESS IS</option>
													<option value="rented" <?php if($rel['current_address_type'] == 'rented') { echo 'selected'; } ?>>Rented</option>
													<option value="owned" <?php if($rel['current_address_type'] == 'owned') { echo 'selected'; } ?>>Owned</option>
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="permenent_address" class="col-md-4 control-label">Permenent Address</label>
												<div class="col-md-8 col-xs-11">
													<textarea style="border: 1px solid #ccc;" id="permenent_address" name="permenent_address" placeholder="Enter Permenent Address" rows="5" cols="69"><?php if($mode=='Edit') { echo $rel['permenent_address']; } ?></textarea>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="current_address" class="col-md-4 control-label">Current Address</label>
												<div class="col-md-8 col-xs-11">
													<textarea style="border: 1px solid #ccc;" id="current_address" name="current_address" placeholder="Enter Current Address" rows="5" cols="69"><?php if($mode=='Edit') { echo $rel['current_address']; } ?></textarea>
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
							  PERSONAL BIO
							<span class="tools pull-right">
								<a href="#personalbio" class="fa fa-chevron-down" data-toggle="collapse"></a>
							</span>
							</header>	
							<div class="panel-body collapse" id="personalbio">
								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<div class="col-md-12">
											<div class="form-group">
												<label for="personal_bio_description" class="col-md-2 control-label">Bio / Cover Letter</label>
												<div class="col-md-8 col-xs-11">
													<textarea style="border: 1px solid #ccc;" id="personal_bio_description" name="personal_bio_description" placeholder="Enter Bio / Cover Letter" rows="5" cols="69"><?php if($mode=='Edit') { echo $rel['personal_bio_description']; } ?></textarea>
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
							  PERSONAL DETAILS
							<span class="tools pull-right">
								<a href="#personaldetail" class="fa fa-chevron-down" data-toggle="collapse"></a>
							</span>
							</header>	
							<div class="panel-body collapse" id="personaldetail">
								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
										  		<label for="passport_number" class="col-md-4 control-label">Passport Number</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="passport_number" name="passport_number" placeholder="Enter Passport Number" value="<?php echo $rel['passport_number']; ?>" />
											  	</div>
											</div>							 
										</div>
										<div class="col-md-6 typeled">
											<div class="form-group">
										  		<label for="date_of_issue" class="col-md-4 control-label">Date of Issue</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="date_of_issue" name="date_of_issue" placeholder="Enter Date of Issue" value="<?=($rel['date_of_issue'] && $rel['date_of_issue'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['date_of_issue'])) : ''?>" autocomplete="off" />
											  	</div>
											</div>							 
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6 typeled">
											<div class="form-group">
										  		<label for="valid_up_to" class="col-md-4 control-label">Valid Upto</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control datepicker" id="valid_up_to" name="valid_up_to" placeholder="Enter Valid Up To" value="<?=($rel['valid_up_to'] && $rel['valid_up_to'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['valid_up_to'])) : ''?>" autocomplete="off" />
											  	</div>
											</div>							 
										</div>
										<div class="col-md-6">
											<div class="form-group">
										  		<label for="place_of_issue" class="col-md-4 control-label">Place of Issue</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="place_of_issue" name="place_of_issue" placeholder="Enter Place of Issue" value="<?php echo $rel['place_of_issue']; ?>" />
											  	</div>
											</div>							 
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
								 			<label for="matrial_status" class="col-md-4 control-label">Marital Status</label>
								 			<div class="col-md-8 col-xs-11">
												<select id="matrial_status" class="select2" name="matrial_status">
													<option selected disabled value="">SELECT MARITAL STATUS</option>
													<option value="single" <?php if($rel['matrial_status'] == 'single') { echo 'selected'; } ?>>Single</option>
													<option value="married" <?php if($rel['matrial_status'] == 'married') { echo 'selected'; } ?>>Married</option>
													<option value="divorced" <?php if($rel['matrial_status'] == 'divorced') { echo 'selected'; } ?>>Divorced</option>
													<option value="widowed" <?php if($rel['matrial_status'] == 'widowed') { echo 'selected'; } ?>>Widowed</option>
												</select>
											</div>
										</div>
										<div class="col-md-6">
								 			<label for="blood_group" class="col-md-4 control-label">Blood Group</label>
								 			<div class="col-md-8 col-xs-11">
												<select id="blood_group" class="select2" name="blood_group">
													<option selected disabled value="">SELECT BLOOD GROUP</option>
													<option value="A+" <?php if($rel['blood_group'] == 'A+') { echo 'selected'; } ?>>A+</option>
													<option value="A-" <?php if($rel['blood_group'] == 'A-') { echo 'selected'; } ?>>A-</option>
													<option value="B+" <?php if($rel['blood_group'] == 'B+') { echo 'selected'; } ?>>B+</option>
													<option value="B-" <?php if($rel['blood_group'] == 'B-') { echo 'selected'; } ?>>B-</option>
													<option value="AB+" <?php if($rel['blood_group'] == 'AB+') { echo 'selected'; } ?>>AB+</option>
													<option value="AB-" <?php if($rel['blood_group'] == 'AB-') { echo 'selected'; } ?>>AB-</option>
													<option value="O+" <?php if($rel['blood_group'] == 'O+') { echo 'selected'; } ?>>O+</option>
													<option value="O-" <?php if($rel['blood_group'] == 'O-') { echo 'selected'; } ?>>O-</option>
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="family_background" class="col-md-4 control-label">Family Background</label>
												<div class="col-md-8 col-xs-11">
													<textarea style="border: 1px solid #ccc;" id="family_background" name="family_background" placeholder="Enter Family Background" rows="5" cols="69"><?php if($mode=='Edit') { echo $rel['family_background']; } ?></textarea>
													<p>Here you can maintain family details like name and occupation of parent, spouse and children</p>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="health_detail" class="col-md-4 control-label">Health Details</label>
												<div class="col-md-8 col-xs-11">
													<textarea style="border: 1px solid #ccc;" id="health_detail" name="health_detail" placeholder="Enter Health Details" rows="5" cols="69"><?php if($mode=='Edit') { echo $rel['health_detail']; } ?></textarea>
													<p>Here you can maintain height, weight, allergies, medical concerns etc</p>
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
							  EDUCATIONAL QUALIFICATION
							<span class="tools pull-right">
								<a href="#educationaldetail" class="fa fa-chevron-down" data-toggle="collapse"></a>
							</span>
							</header>	
							<div class="panel-body collapse" id="educationaldetail">
								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<h5>Education</h5>
										<div class="col-md-12">
											<div class="form-group">
												<table cellspacing="10" style="border-collapse:inherit; " id="educational_company_days" class="display table table12 table-striped table-bordered">
													<tr id="field">
														<th width="15%" class="text-center">School/University</th>
														<th width="15%" class="text-center">Qualification</th>
														<th width="15%" class="text-center">Level</th>
														<th width="15%" class="text-center">Year of Passing</th>
														<th width="15%" class="text-center">Class/Percentage</th>
														<th width="15%" class="text-center">Major/Optional Subjects</th>
														<th width="10%" class="text-center"></th>
													</tr>
													<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
													<tr id="field1">
														<td data-label="School/University" style="vertical-align:top;">
															<input type="text"  name="education_school_university" title="School/University" placeholder="School/University" id="education_school_university" class="form-control" />
														</td>
														<td data-label="Qualification" style="vertical-align:top;">
															<input type="text"  name="education_qualification" title="Qualification" placeholder="Qualification" id="education_qualification" class="form-control" />
														</td>
														<td data-label="Level" style="vertical-align:top;">
															<select id="education_level" class="select2" name="education_level">
																<option selected disabled value="">SELECT LEVEL</option>
																<option value="graduate">Graduate</option>
																<option value="post_graduate">Post Graduate</option>
																<option value="under_graduate">Under Graduate</option>
															</select>
														</td>
														<td data-label="Year of Passing" style="vertical-align:top;">
															<input type="text"  name="year_of_passing" title="Year of Passing" placeholder="Year of Passing" id="year_of_passing" class="form-control" />
														</td>
														<td data-label="Class/Percentage" style="vertical-align:top;">
															<input type="text"  name="class_percentage" title="Class/Percentage" placeholder="Class/Percentage" id="class_percentage" class="form-control" />
														</td>
														<td data-label="Major/Optional Subjects" style="vertical-align:top;">
															<textarea style="border: 1px solid #ccc;" id="optional_subjects" name="optional_subjects" placeholder="Major/Optional Subjects" rows="2" cols="50"></textarea>
														</td>
														<td style="vertical-align:top;">
															<input type="button"  name="addeducationalrow" id="addeducationalrow" onClick="return add_educational_field();"  class="btn btn-primary" value="Add"/>
														</td>
														<input type='hidden' name='edit_educational_id' id='edit_educational_id' value='' />
													</tr>
												</table>			
											</div>
										</div>
										<div id="educationalcompanydata"></div>
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
							  PREVIOUS WORK EXPERIENCE
							<span class="tools pull-right">
								<a href="#previousworkdetail" class="fa fa-chevron-down" data-toggle="collapse"></a>
							</span>
							</header>	
							<div class="panel-body collapse" id="previousworkdetail">
								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<h5>External Work History</h5>
										<div class="col-md-12">
											<div class="form-group">
												<table cellspacing="10" style="border-collapse:inherit; " id="previous_company_days" class="display table table12 table-striped table-bordered">
													<tr id="field">
														<th width="15%" class="text-center">Company Name</th>
														<th width="15%" class="text-center">Designation</th>
														<th width="15%" class="text-center">Salary</th>
														<th width="15%" class="text-center">Address</th>
														<th width="15%" class="text-center">Contact</th>
														<th width="15%" class="text-center">Total Experience</th>
														<th width="10%" class="text-center"></th>
													</tr>
													<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
													<tr id="field1">
														<td data-label="Company Name" style="vertical-align:top;">
															<input type="text"  name="company_name" title="Company Name" placeholder="Company Name" id="company_name" class="form-control" />
														</td>
														<td data-label="Designation" style="vertical-align:top;">
															<input type="text"  name="designation" title="Designation" placeholder="Designation" id="designation" class="form-control" />
														</td>
														<td data-label="Salary" style="vertical-align:top;">
															<input type="text"  name="salary_amount" title="Salary" placeholder="Salary" id="salary_amount" class="form-control" />
														</td>
														<td data-label="Address" style="vertical-align:top;">
															<input type="text"  name="address" title="Address" placeholder="Address" id="address" class="form-control" />
														</td>
														<td data-label="Contact" style="vertical-align:top;">
															<input type="text"  name="contact" title="Contact" placeholder="Contact" id="contact" class="form-control" />
														</td>
														<td data-label="Total Experience" style="vertical-align:top;">
															<input type="text"  name="total_experience" title="Total Experience" placeholder="Total Experience" id="total_experience" class="form-control" />
														</td>
														<td style="vertical-align:top;">
															<input type="button"  name="addpreviousrow" id="addpreviousrow" onClick="return add_previous_field();"  class="btn btn-primary" value="Add"/>
														</td>
														<input type='hidden' name='edit_previous_id' id='edit_previous_id' value='' />
													</tr>
												</table>			
											</div>
										</div>
										<div id="previouscompanydata"></div>
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
							  HISTORY IN COMPANY
							<span class="tools pull-right">
								<a href="#historycompanydetail" class="fa fa-chevron-down" data-toggle="collapse"></a>
							</span>
							</header>	
							<div class="panel-body collapse" id="historycompanydetail">
								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<h5>Internal Work History</h5>
										<div class="col-md-12">
											<div class="form-group">
												<table cellspacing="10" style="border-collapse:inherit; " id="history_company_days" class="display table table12 table-striped table-bordered">
													<tr id="field">
														<th width="15%" class="text-center">Branch</th>
														<th width="15%" class="text-center">Department</th>
														<th width="15%" class="text-center">Designation</th>
														<th width="15%" class="text-center">From Date</th>
														<th width="15%" class="text-center">To Date</th>
														<th width="10%" class="text-center"></th>
													</tr>
													<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
													<tr id="field1">
														<td data-label="Branch" style="vertical-align:top;">
															<select id="history_branch_id" class="select2" name="history_branch_id">
																<option selected disabled value="">SELECT BRANCH</option>
																<?php
																	$query = $dbcon->query("SELECT `branch_id`,`branch_name` FROM `branch_mst` WHERE `company_id` = $companyID and `branch_status` = 0  order by branch_id ");
																	while ($r = $query->fetch_assoc()) {
																		echo '<option value="' . $r['branch_id'] . '">' .$r['branch_name']. '</option>';
																	}
																?>
															</select>
														</td>
														<td data-label="Department" style="vertical-align:top;">
															<select id="history_department_id" class="select2" name="history_department_id">
																<option selected disabled value="">SELECT DEPARTMENT</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`department_name` FROM `hrms_department` WHERE `company_id` = $companyID and `status` = 0  order by id ");
																	while ($r = $query->fetch_assoc()) {
																		echo '<option value="' . $r['id'] . '">' .$r['department_name']. '</option>';
																	}
																?>
															</select>
														</td>
														<td data-label="Designation" style="vertical-align:top;">
															<select id="history_designation_id" class="select2" name="history_designation_id">
																<option selected disabled value="">SELECT DESIGNATION</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`designation_name` FROM `hrms_designation` WHERE `company_id` = $companyID and `status` = 0  order by id ");
																	while ($r = $query->fetch_assoc()) {
																		echo '<option value="' . $r['id'] . '">' .$r['designation_name']. '</option>';
																	}
																?>
															</select>
														</td>
														<td data-label="From Date" style="vertical-align:top;">
															<input type="text"  name="history_from_date" title="From Date" placeholder="From Date" id="history_from_date" class="form-control default-date-picker" />
														</td>
														<td data-label="To Date" style="vertical-align:top;">
															<input type="text"  name="history_to_date" title="To Date" placeholder="To Date" id="history_to_date" class="form-control default-date-picker" />
														</td>
														<td style="vertical-align:top;">
															<input type="button"  name="addrow" id="addrow" onClick="return add_history_field();"  class="btn btn-primary" value="Add"/>
														</td>
														<input type='hidden' name='edit_id' id='edit_id' value='' />
													</tr>
												</table>			
											</div>
										</div>
										<div id="historycompanydata"></div>
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
							  EXIT
							<span class="tools pull-right">
								Exit Interview Details 
								<a href="#exitdetail" class="fa fa-chevron-down" data-toggle="collapse"></a>
							</span>
							</header>	
							<div class="panel-body collapse" id="exitdetail">
								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<div class="col-md-6 typeled">
											<div class="form-group">
										  		<label for="resignation_letter_date" class="col-md-4 control-label">Resignation Letter Date</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control datepicker" id="resignation_letter_date" name="resignation_letter_date" placeholder="Enter Resignation Letter Date" value="<?=($rel['resignation_letter_date'] && $rel['resignation_letter_date'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['resignation_letter_date'])) : ''?>" autocomplete="off" />
											  	</div>
											</div>							 
										</div>
										<div class="col-md-6 typeled">
											<div class="form-group">
										  		<label for="held_on_date" class="col-md-4 control-label">Held On Date</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control datepicker" id="held_on_date" name="held_on_date" placeholder="Enter Held On Date" value="<?=($rel['held_on_date'] && $rel['held_on_date'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['held_on_date'])) : ''?>" autocomplete="off" />
											  	</div>
											</div>							 
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6 typeled">
											<div class="form-group">
										  		<label for="reason_for_leaving" class="col-md-4 control-label">Reason for Leaving</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="reason_for_leaving" name="reason_for_leaving" placeholder="Enter Reason For Leaving" value="<?php echo $rel['reason_for_leaving']; ?>" />
											  	</div>
											</div>							 
										</div>
										<div class="col-md-6 typeled">
								 			<label for="reason_for_resignation" class="col-md-4 control-label">Reason for Resignation</label>
								 			<div class="col-md-8 col-xs-11">
												<select id="reason_for_resignation" class="select2" name="reason_for_resignation">
													<option selected disabled value="">SELECT REASON FOR RESIGNATION</option>
													<option value="better_prospects" <?php if($rel['reason_for_resignation'] == 'better_prospects') { echo 'selected'; } ?>>Better Prospects</option>
													<option value="health_concerns" <?php if($rel['reason_for_resignation'] == 'health_concerns') { echo 'selected'; } ?>>Health Concerns</option>
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
								 			<label for="leave_encashed_flag" class="col-md-4 control-label">Leave Encashed?</label>
								 			<div class="col-md-8 col-xs-11">
												<select id="leave_encashed_flag" class="select2" name="leave_encashed_flag">
													<option selected disabled value="">SELECT LEAVE ENCASHED</option>
													<option value="Yes" <?php if($rel['leave_encashed_flag'] == 'Yes') { echo 'selected'; } ?>>Yes</option>
													<option value="No" <?php if($rel['leave_encashed_flag'] == 'No') { echo 'selected'; } ?>>No</option>
												</select>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
										  		<label for="new_workplace" class="col-md-4 control-label">New Workplace</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="new_workplace" name="new_workplace" placeholder="Enter New Workplace" value="<?php echo $rel['new_workplace']; ?>" />
											  	</div>
											</div>							 
										</div>
									</div>
									<div class="col-md-12 margin_row">
										<div class="col-md-6 typeled">
											<div class="form-group">
										  		<label for="encashment_date" class="col-md-4 control-label">Encashment Date</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control datepicker" id="encashment_date" name="encashment_date" placeholder="Enter Encashment Date" value="<?=($rel['encashment_date'] && $rel['encashment_date'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['encashment_date'])) : ''?>" autocomplete="off" />
											  	</div>
											</div>							 
										</div>
										<div class="col-md-6 typeled">
											<div class="form-group">
												<label for="exit_feedback" class="col-md-4 control-label">Exit Feedback</label>
												<div class="col-md-8 col-xs-11">
													<textarea style="border: 1px solid #ccc;" id="exit_feedback" name="exit_feedback" placeholder="Enter Exit Feedback" rows="5" cols="69"><?php if($mode=='Edit') { echo $rel['exit_feedback']; } ?></textarea>
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
							<div class="panel-body">
								<div class="col-md-12">
									<div class="col-md-12 margin_row text-center">
										<br>
										<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
										<a href="<?= ROOT . HRMS_ROOT . 'hrms_employee' ?>" type="button" class="btn btn-danger">Cancel</a>
									</div>
								</div>
							</div>
						</section>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-12">	
						<div style="background-color: #fff; padding: 10px 0; text-align: center;">	
							<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  			  
							<input type='hidden' name='eid' id='eid' value="<?php if($mode=='Edit'){ echo $rel['empid']; } else { echo "0"; } ?>" />
							<input type="hidden" name="mode" id="mode" value="<?php if($mode=='Add'){ echo "add"; } else { echo "edit"; } ?>" />
						</div>
					</div>
				</div>
			</form>
		</section>
    </section>
    <!--main content end-->
    <!--footer start-->
    <?php include_once('../../include/add_zone.php');?>
	<?php include_once('../../include/add_city.php');?>
	<?php include_once('../../include/add_state.php');?>
	<?php include_once('../../include/footer.php');?>
    <!--footer end-->
</section>
<!-- Modal -->

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../../include/include_js_file.php');?>   
<script src="<?=ROOT . HRMS_ROOT?>js/app/hrms_employee.js?<?php echo time(); ?>"></script>
<script src="<?=ROOT . HRMS_ROOT?>js/app/state_mst.js?<?=time()?>"></script>
<script src="<?=ROOT . HRMS_ROOT?>js/app/city_mst.js?<?=time()?>"></script>
<script src="<?=ROOT . HRMS_ROOT?>js/app/zone_mst.js?<?=time()?>"></script>
<script>
	$(".default-date-picker").datepicker({
        format: "dd-mm-yyyy",
        autoclose: true,
        todayHighlight: true
    });
	$('#history_from_date').datepicker()
    .on('changeDate', function(e) {
        var start_date = e.format(0,"dd-mm-yyyy");
        var end_date = $('#history_to_date').val();

        job_start_date = start_date.split('-');
        job_end_date = end_date.split('-');

        var new_start_date = new Date(job_start_date[2],job_start_date[0],job_start_date[1]);
        var new_end_date = new Date(job_end_date[2],job_end_date[0],job_end_date[1]);

        $('#history_to_date').datepicker('setStartDate', e.format(0,"dd-mm-yyyy"));
        
        if(end_date == '' || new_start_date > new_end_date) {
            $('#history_to_date').datepicker('setDate', e.format(0,"dd-mm-yyyy"));
        }

    });
	$('#date_of_issue').datepicker({
		format: 'dd-mm-yyyy',
		autoclose: true
	});
	CKEDITOR.replace( 'personal_bio_description', {
		enterMode: CKEDITOR.ENTER_BR
	});
	$(document).on("click","#employee_unsubscribed_flag", function(){
		if($(this).is(":checked")){
			$("#employee_unsubscribed_flag").val('Yes');
		}else{
			$("#employee_unsubscribed_flag").val('No');
		}
	});
	$(".select2").select2({
		width: '100%'
	});
	$(".datepicker").datepicker({
		format: "dd-mm-yyyy",
	    startDate: "1d",
	    autoclose: true,
	    todayHighlight: true
	});
	$(".datepickerPrev").datepicker({
		format: "dd-mm-yyyy",
	    endDate: "-1y",
	    autoclose: true,
	    todayHighlight: true
	});
</script>
<?php
	if($mode=="Edit"){
		echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
		echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";
		echo "<script>get_branch_by_zone(".$rel['emp_zone_id'].",'branch_id_emp',".$rel['emp_branch_id'].")</script>";
	}
	else{
		echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
		echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";
	}
	echo "<script>show_history_data() </script>";
	echo "<script>show_previous_data() </script>";
	echo "<script>show_educational_data() </script>";
?>
</body>
</html>