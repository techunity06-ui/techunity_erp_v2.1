<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	include_once("../../include/payroll_common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Employee Benefit Claim";
	$companyID = $_SESSION['company_id'];
	$usertype=$_SESSION['user_type'];
	if(strpos($_SERVER[REQUEST_URI], "payroll_emp_benefit_claim_edit")==false)
	{
		$mode="Add";
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	}
	else
	{
		$mode="Edit";
		$payroll_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select payrollempbenclaim.* from payroll_emp_benefit_claim as payrollempbenclaim 
				left join tbl_company as comp on comp.company_id = payrollempbenclaim.company_id
				where `payrollempbenclaim`.`id` = $payroll_id and `payrollempbenclaim`.`company_id` = $companyID";
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
										<li><a href="<?= ROOT . HRMS_ROOT . 'payroll_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li ><a href="<?= ROOT . HRMS_ROOT . 'payroll_emp_benefit_claim_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="payroll_emp_benefit_claim_add" action="javascript:;" method="post" name="payroll_emp_benefit_claim_add" enctype="multipart/form-data">
										<div class="">
											<div class="col-md-12">
												<?php if($mode == "Edit"){ ?>
													<div class="col-md-6">
														<div class="form-group">
																<label class="col-md-4 control-label">Series</label>
																<div class="col-md-8 col-xs-11">
																	<input type="text" class="form-control" id="series_edit_id" placeholder="series_id" value="<?php if($mode=='Edit'){ echo $rel['series_id'];} ?>" readonly />
																	<input type="hidden" class="form-control" id="series_id" name="series_id" placeholder="series_id" value="<?php if($mode=='Edit'){ echo $rel['series_id'];} ?>" />
																</div>
														</div>							 
													</div>
							 					<?php } else { ?>
													<div class="col-md-6">
														<div class="form-group">
																<label class="col-md-4 control-label">Series</label>
																<?php
																$series_id = '';
																$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='PAYROLL EMP BENEFIT CLAIM' and company_id = $companyID and `type_id` = '16' order by invoicetype_id ");
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
														<label class="col-md-4 control-label">Employee*</label>
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
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Claim Date </label>
														<div class="col-md-8 col-xs-12">
															<input id="claim_date" name="claim_date" type="text" class="form-control default-date-picker" title="Enter Claim Date" placeholder="Enter Claim Date" value="<?php if($mode=='Edit'){ echo $rel['claim_date'];} ?>" >
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>BENEFIT TYPE AND AMOUNT </h4>
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Claim Benefit For*</label>
												  		<div class="col-md-8 col-xs-11">
															<select id="claim_benefit_for" class="select2" name="claim_benefit_for" onchange="getEarningComponentData()" required>
																<option selected disabled value="">SELECT CLAIM BENEFIT FOR</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`salary_component_name` FROM `payroll_salary_component` WHERE `status` = 0 and company_id = $companyID order by salary_component_name");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['claim_benefit_for'] == $r['id']){
																			$salarycomponentIDS = 'selected';
																		}else{
																			$salarycomponentIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$salarycomponentIDS.'>' . $r['salary_component_name'] . '</option>';
																	}
																?>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-6"></div>
											</div>
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Max Amount Eligible</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" title="Enter Max Amount Eligible" placeholder="Max Amount Eligible" id="maximum_display_amount_eligible" class="form-control" disabled/>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Claimed Amount</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" name="claim_amount" title="Enter Claimed Amount" placeholder="Claimed Amount" id="claim_amount" class="form-control" value="<?php if($mode=='Edit'){ echo $rel['claim_amount'];} ?>" />
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>Attachments </h4>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Claim Attachment </label>
														<div class="col-md-8 col-xs-12">
															<input type="file" id="claim_attachment" name="claim_attachment"  class="form-control" title="Select Attachment" >
															<div class="col-md-3">
																<?php if($mode=='Edit') { ?>
																	<img src="<?php echo ROOT . HRMS_ROOT .'upload/emp_benefit_claim_file/'.$rel['claim_attachment']; ?>" width="150" height="150" />
																<?php } ?>
															</div>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
										  			<div class="form-group">
														<label class="col-md-4 control-label">Status*</label>
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
													<a href="<?= ROOT . HRMS_ROOT . 'payroll_emp_benefit_claim_list'?>" type="button" class="btn btn-danger">Cancel</a>
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
			<script src="<?= ROOT . HRMS_ROOT ?>js/app/payroll_emp_benefit_claim.js?<?=time()?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			<?php if($mode == 'Add'){ ?>
				$(document).ready(function(){
					$(document).on("keyup","#claim_amount", function(){
						var maximum_display_amount_eligible = $("#maximum_display_amount_eligible").val();
						var claim_amount = $("#claim_amount").val();
						if(parseInt(claim_amount) >  parseInt(maximum_display_amount_eligible)){
							alert("You can not enter more than maximum benefit amount eligible.");
							$("#claim_amount").val("");
						}
					});
				});
			<?php } ?>
		</script>
		<?php if($mode == 'Edit'){ 
				echo "<script>getEarningComponentData()</script>";
		?>
		<script>
			$(document).ready(function(){
				$(document).on("keyup","#claim_amount", function(){
					var maximum_display_amount_eligible = $("#maximum_display_amount_eligible").val();
					var claim_amount = $("#claim_amount").val();
					var original_claim_amount = "<?php echo $rel['claim_amount']; ?>";
					if(parseInt(claim_amount) >  parseInt(maximum_display_amount_eligible)){
						alert("You can not enter more than maximum benefit amount eligible.");
						$("#claim_amount").val(original_claim_amount);
					}
				});
			});
		</script>
		<?php } ?>
	</body>
</html>
