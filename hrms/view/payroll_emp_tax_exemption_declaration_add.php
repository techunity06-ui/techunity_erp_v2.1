<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	include_once("../../include/payroll_common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Employee Tax Exemption Declaration";
	$companyID = $_SESSION['company_id'];
	$usertype=$_SESSION['user_type'];
	if(strpos($_SERVER[REQUEST_URI], "payroll_emp_tax_exemption_declaration_edit")==false)
	{
		$mode="Add";
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	}
	else
	{
		$mode="Edit";
		$payroll_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select payrollexemdecl.* from payroll_emp_tax_exemption_declaration as payrollexemdecl 
				left join tbl_company as comp on comp.company_id = payrollexemdecl.company_id
				left join payroll_emp_tax_declaration as payempdecl on payempdecl.payroll_emp_tax_exemption_decl_id = payrollexemdecl.id
				where `payrollexemdecl`.`id` = $payroll_id and `payrollexemdecl`.`company_id` = $companyID";
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
										<li ><a href="<?= ROOT . HRMS_ROOT . 'payroll_emp_tax_exemption_declaration_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="payroll_emp_tax_exemption_declaration_add" action="javascript:;" method="post" name="payroll_emp_tax_exemption_declaration_add">
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
																$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='EMPLOYEE TAX EXEMPTION DECLARA' and company_id = $companyID and `type_id` = '16' order by invoicetype_id ");
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
														<label class="col-md-4 control-label">Payroll Period*</label>
												  		<div class="col-md-8 col-xs-11">
															<select id="payroll_period_id" class="select2" name="payroll_period_id" required>
																<option selected disabled value="">SELECT PAYROLL PERIOD</option>
																<?php
																	$query = $dbcon->query("SELECT `id`,`payroll_period_name` FROM `payroll_period` WHERE `status` = 0 and company_id = $companyID order by payroll_period_name");
																	while ($r = $query->fetch_assoc()) {
																		if($rel['payroll_period_id'] == $r['id']){
																			$payrollperiodIDS = 'selected';
																		}else{
																			$payrollperiodIDS = '';
																		}
																		echo '<option value="' . $r['id'] . '" '.$payrollperiodIDS.'>' . $r['payroll_period_name'] . '</option>';
																	}
																?>
															</select>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>Declarations </h4>
											<h6>Employee Tax Exemption Declaration</h6>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="payroll_emp_tax_declaration" class="display table table12 table-striped table-bordered">
														<tr id="field">
															<th width="15%" class="text-center">Exemption Sub Category</th>
															<th width="15%" class="text-center">Exemption Category</th>
															<th width="15%" class="text-center">Maximum Exemption Amount</th>
															<th width="15%" class="text-center">Declared Amount</th>
															<th width="5%" class="text-center">Action</th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td data-label="Exemption Sub Category" style="vertical-align:top;">
																<select id="exemption_subcategory" class="select2" name="exemption_subcategory" onchange="getCategoryData()">
																	<option selected disabled value="">SELECT EXEM SUB CATEGORY</option>
																	<?php
																		$query = $dbcon->query("SELECT `id`,`category_name` FROM `payroll_emp_tax_exemption_cat_sub` WHERE `status` = 0 and company_id = $companyID and parent_id != '0' order by category_name");
																		while ($r = $query->fetch_assoc()) {
																			if($rel['exemption_subcaegory'] == $r['id']){
																				$exesubcategoryIDS = 'selected';
																			}else{
																				$exesubcategoryIDS = '';
																			}
																			echo '<option value="' . $r['id'] . '" '.$exesubcategoryIDS.'>' . $r['category_name'] . '</option>';
																		}
																	?>
																</select>
															</td>
															<td data-label="Exemption Category" style="vertical-align:top;">
																<select id="exemption_display_category" class="select2" name="exemption_display_category" disabled>
																	<option selected disabled value="">SELECT EXEM CATEGORY</option>
																	<?php
																		$query = $dbcon->query("SELECT `id`,`category_name` FROM `payroll_emp_tax_exemption_cat_sub` WHERE `status` = 0 and company_id = $companyID and parent_id = '0' order by category_name");
																		while ($r = $query->fetch_assoc()) {
																			if($rel['exemption_subcaegory'] == $r['id']){
																				$execategoryIDS = 'selected';
																			}else{
																				$execategoryIDS = '';
																			}
																			echo '<option value="' . $r['id'] . '" '.$execategoryIDS.'>' . $r['category_name'] . '</option>';
																		}
																	?>
																</select>
																<input type="hidden"  name="exemption_category" title="Enter Exemption Category" placeholder="Exemption Category" id="exemption_category" class="form-control" />
															</td>
															<td data-label="Maximum Exemption Amount" style="vertical-align:top;">
																<input type="text"  name="maximum_display_exemption_amount" title="Enter Maximum Exemption Amount" placeholder="Maximum Exemption Amount" id="maximum_display_exemption_amount" class="form-control" disabled/>
																<input type="hidden"  name="maximum_exemption_amount" title="Enter Maximum Exemption Amount" placeholder="Maximum Exemption Amount" id="maximum_exemption_amount" class="form-control" />
															</td>
															<td data-label="Declared Amount" style="vertical-align:top;">
																<input type="text"  name="declared_amount" title="Enter Declared Amount" placeholder="Declared Amount" id="declared_amount" class="form-control" />
															</td>
															<td style="vertical-align:top;">
																<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>			
												</div>
											</div>
											<div id="payroll_employee_tax_declaration"></div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Total Declared Amount </label>
														<div class="col-md-8 col-xs-12">
															<input id="total_declared_amount" name="total_declared_amount" type="text" class="form-control" title="Enter Total Declared Amount" placeholder="Enter Total Declared Amount" value="<?php if($mode=='Edit'){ echo $rel['total_declared_amount'];} ?>" >
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Total Exemption Amount </label>
														<div class="col-md-8 col-xs-12">
															<input id="total_exemption_amount" name="total_exemption_amount" type="text" class="form-control" title="Enter Total Exemption Amount" placeholder="Enter Total Exemption Amount" value="<?php if($mode=='Edit'){ echo $rel['total_exemption_amount'];} ?>" >
														</div>
													</div>
												</div>
											</div>
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
													<a href="<?= ROOT . HRMS_ROOT . 'payroll_income_tax_slab_list'?>" type="button" class="btn btn-danger">Cancel</a>
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
			<script src="<?= ROOT . HRMS_ROOT ?>js/app/payroll_emp_tax_exemption_declaration.js?<?=time()?>"></script>
		<script>
			//CKEDITOR.replace('quotation_condition');
			$(".select2").select2({
				width: '100%'
			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			<?php if($mode == 'Edit'){ ?>
				var checkboxChecked = $("#allow_tax_exemption_flag").is(":checked");
				if(checkboxChecked){
					$("#standard_tax_hide").css('display','block');
				}else{
					$("#standard_tax_hide").css('display','none');
				}
			<?php } ?>
			$(document).ready(function(){
				
				$(document).on("keyup","#declared_amount", function(){
					var decla_am = $(this).val();
					$("#total_declared_amount").val(decla_am);
					$("#total_exemption_amount").val(decla_am);
				});	
				$(document).on("click","#allow_tax_exemption_flag", function(){
					if($(this).is(":checked")){
						$("#allow_tax_exemption_flag").val('Yes');
						$("#standard_tax_hide").css('display','block');
					}else{
						$("#allow_tax_exemption_flag").val('No');
						$("#standard_tax_hide").css('display','none');
					}
				});
			});
		</script>
		<?php if($mode == 'Edit'){ 
				echo "<script>getCategoryData() </script>";
		} ?>
		<?php 
			echo "<script>show_data() </script>";
		?>
	</body>
</html>
