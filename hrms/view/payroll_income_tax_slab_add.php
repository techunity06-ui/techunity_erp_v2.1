<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	include_once("../../include/payroll_common_functions.php");

	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Income Tax Slab";
	$companyID = $_SESSION['company_id'];
	$usertype=$_SESSION['user_type'];
	if(strpos($_SERVER['REQUEST_URI'], "payroll_income_tax_slab_edit")==false)
	{
		$mode="Add";
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	}
	else
	{
		$mode="Edit";
		$payroll_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select payrollslab.* from payroll_income_tax_slab as payrollslab 
				left join tbl_company as comp on comp.company_id = payrollslab.company_id
				left join payroll_taxable_salary_slabs as paysalaryslab on paysalaryslab.payroll_income_tax_slab_id = payrollslab.id
				left join payroll_taxes_and_charges_on_income_tax as paytaxandcharges on paytaxandcharges.payroll_income_tax_slab_id = payrollslab.id where `payrollslab`.`id` = $payroll_id and `payrollslab`.`company_id` = $companyID";
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
										<li ><a href="<?= ROOT . HRMS_ROOT . 'payroll_income_tax_slab_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="payroll_income_tax_slab_add" action="javascript:;" method="post" name="payroll_income_tax_slab_add">
										<div class="">
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Income Tax Slab Name </label>
														<div class="col-md-8 col-xs-12">
															<input id="income_tax_slab_name" name="income_tax_slab_name" type="text" class="form-control" title="Enter Income Tax Slab Name" placeholder="Enter Income Tax Slab Name" value="<?php if($mode=='Edit'){ echo $rel['income_tax_slab_name'];} ?>" >
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-2 control-label"></label>
														<div class="col-md-8 col-xs-12">
															<input type="checkbox" name="allow_tax_exemption_flag" id="allow_tax_exemption_flag" data-id="allow_tax_exemption_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['allow_tax_exemption_flag'] : 'No' ?>" <?php if($rel['allow_tax_exemption_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">Allow Tax Exemption <h6>(If enabled, Tax Exemption Declaration will be considered for income tax calculation.)</h6></span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Effective From </label>
														<div class="col-md-8 col-xs-12">
															<input id="income_effective_from" name="income_effective_from" type="text" class="form-control default-date-picker" title="Enter Effective From Date" placeholder="Enter Effective From Date" value="<?php if($mode=='Edit'){ echo $rel['income_effective_from'];} ?>" >
														</div>
													</div>
												</div>
												<div class="col-md-6" id="standard_tax_hide" style="display: none">
													<div class="form-group">
														<label class="col-md-4 control-label">Standard Tax Exemption Amount </label>
														<div class="col-md-8 col-xs-12">
															<input id="standard_tax_exemption_amount" name="standard_tax_exemption_amount" type="text" class="form-control" title="Enter Standard Tax Exemption Amount" placeholder="Enter Standard Tax Exemption Amount" value="<?php if($mode=='Edit'){ echo $rel['standard_tax_exemption_amount'];} ?>" >
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-6"></div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-2 control-label"></label>
														<div class="col-md-8 col-xs-12">
															<input type="checkbox" name="income_tax_slab_disabled" id="income_tax_slab_disabled" data-id="income_tax_slab_disabled" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['income_tax_slab_disabled'] : 'No' ?>" <?php if($rel['income_tax_slab_disabled'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">Disabled</span>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>TAXABLE SALARY SLABS </h4>
											<h6>Taxable Salary Slabs</h6>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="taxable_salary_slabs" class="display table table12 table-striped table-bordered">
														<tr id="field">
															<th width="10%" class="text-center">From Amount</th>
															<th width="10%" class="text-center">To Amount</th>
															<th width="10%" class="text-center">Percent Deduction (%)</th>
															<th width="25%" class="text-center">Condition</th>
															<th width="3%" class="text-center">Action</th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td data-label="From Amount" style="vertical-align:top;">
																<input type="text"  name="taxable_from_amount" title="Enter From Amount" placeholder="From Amount" id="taxable_from_amount" class="form-control" />
															</td>
															<td data-label="To Amount" style="vertical-align:top;">
																<input type="text"  name="taxable_to_amount" title="Enter To Amount" placeholder="To Amount" id="taxable_to_amount" class="form-control" />
															</td>
															<td data-label="Percent Deduction (%)" style="vertical-align:top;">
																<input type="text"  name="taxable_percent_deduction" title="Enter Percent Deduction" placeholder="Percent Deduction (%)" id="taxable_percent_deduction" class="form-control" />
															</td>
															<td data-label="Condition" style="vertical-align:top;">
																<textarea id="taxable_condition" name="taxable_condition" placeholder="Condition" title="Enter Condition" class="form-control" ></textarea>
															</td>
															<td style="vertical-align:top;">
																<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>			
												</div>
											</div>
											<div id="payroll_taxable_salary_slabs"></div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>TAXES AND CHARGES ON INCOME TAX</h4>
											<h6>Other Taxes and Charges</h6>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="leave_block_allow_users" class="display table table12 table-striped table-bordered">
														<tr id="field">
															<th width="25%" class="text-center">Description</th>
															<th width="10%" class="text-center">Percent</th>
															<th width="10%" class="text-center">Min Taxable Income</th>
															<th width="10%" class="text-center">Max Taxable Income</th>
															<th width="3%" class="text-center">Action</th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td data-label="Description" style="vertical-align:top;">
																<textarea id="taxes_and_charges_description" name="taxes_and_charges_description" placeholder="Description" title="Enter Description" class="form-control" ></textarea>
															</td>
															<td data-label="Percent (%)" style="vertical-align:top;">
																<input type="text"  name="taxes_and_charges_percent" title="Enter Percent" placeholder="Percent (%)" id="taxes_and_charges_percent" class="form-control" />
															</td>
															<td data-label="Min Taxable Income" style="vertical-align:top;">
																<input type="text"  name="taxes_and_charges_min_taxable_income" title="Enter Min Taxable Income" placeholder="Min Taxable Income" id="taxes_and_charges_min_taxable_income" class="form-control" />
															</td>
															<td data-label="Max Taxable Income" style="vertical-align:top;">
																<input type="text"  name="taxes_and_charges_max_taxable_income" title="Enter Max Taxable Income" placeholder="Max Taxable Income" id="taxes_and_charges_max_taxable_income" class="form-control" />
															</td>
															<td style="vertical-align:top;">
																<input type="button"  name="addotherrow" id="addotherrow" onClick="return add_other_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>			
												</div>
											</div>
											<div id="payroll_other_taxes_and_charges"></div>
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
			<script src="<?= ROOT . HRMS_ROOT ?>js/app/payroll_income_tax_slab.js?<?=time()?>"></script>
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
		<?php 
			echo "<script>show_data() </script>";
			echo "<script>show_other_data() </script>";
		?>
	</body>
</html>
