<link href="assets/morris.js-0.4.3/morris.css" rel="stylesheet" />
<style type="text/css">
	.count , .count2{ margin:0px !important; padding:0px !important }
	.cc_count{ margin-left:5%; }
	.panel-heading{ text-align:center; font-weight:bold; FONT-SIZE:16px;}
	.border_line{ border-bottom:dotted blue 2px; }
	.link_dash{ border-bottom:dotted blue thin; }
	.box_border_remove{ border-top: none !important; }
	.label_text{ color: #444a4a; font-size: 21px; font-weight: 800; }
	a.label_text:hover{ color: #444a4a; }
</style>
<div class="">
    <div class="col-md-12">
        <section class="panel1">
            <div class="panel-body">
                <div class="state-overview">
                    <div class="icons icons12 terques">
                    	<div class="icon1 terques">
                            <i class="fa fa-dashboard" aria-hidden="true"></i>
                        </div>
                        <div class="" style="margin:10px;">
                            <a href="<?php echo ROOT . HRMS_ROOT .'payroll_dashboard';?>" class="label_text">Dashboard</a>
                        </div>
                    </div>
                </div>
                <div class="state-overview">
                    <div class="icons icons12 success">
                    	<div class="icon1 success">
                            <i class="fa fa-file" aria-hidden="true"></i>
                        </div>
                        <div class="" style="margin:10px;">
                            <a href="<?php echo ROOT . HRMS_ROOT .'payroll_salary_structure_list';?>" class="label_text">Salary Structure</a>
                        </div>
                    </div>
                </div>
                <div class="state-overview">
                    <div class="icons icons12 info">
                    	<div class="icon1 info">
                            <i class="fa fa-envelope-open" aria-hidden="true"></i>
                        </div>
                        <div class="" style="margin:10px;">
                            <a href="<?php echo ROOT . HRMS_ROOT .'payroll_entry_list';?>" class="label_text">Payroll Entry</a>
                        </div>
                    </div>
                </div>
                <div class="state-overview">
                    <div class="icons icons12 mustard">
                    	<div class="icon1 mustard">
                            <i class="fa fa-file" aria-hidden="true"></i>
                        </div>
                        <div class="" style="margin:10px;">
                            <a href="<?php echo ROOT . HRMS_ROOT .'payroll_salary_slip_list';?>" class="label_text">Salary Slip</a>
                        </div>
                    </div>
                </div>
                <div class="state-overview">
                    <div class="icons icons12 yellow">
                    	<div class="icon1 yellow">
                            <i class="fa fa-cog aria-hidden="true"></i>
                        </div>
                        <div class="" style="margin:10px;">
                            <a href="<?php echo ROOT . HRMS_ROOT .'payroll_income_tax_slab_list';?>" class="label_text">Income Tax Slab</a>
                        </div>
                    </div>
                </div>
                <div class="state-overview">
                    <div class="icons icons12 pink">
                    	<div class="icon1 pink">
                            <i class="fa fa-bar-chart-o" aria-hidden="true"></i>
                        </div>
                        <div class="" style="margin:10px;">
                            <a href="javascript:;" class="label_text">Reports</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<section class="panel">

	<div class="panel-body ">
	
		<div class="row">
                    <div class="col-md-12">
            <?
				$comp_per=check_permission("#payroll_permission",$_SESSION['user_id'],'view',$dbcon);
				if($comp_per)
				{
					
			?>	
			<!-- Pending follow-ups Section Start -->
			<div class="col-md-4">
				<div class="panel panel-primary">
				
					<div class="panel-heading">Payroll Modules</div>
					
					<div class="panel-body" id="crm_table_data">
						<table class="table">
							<?php 
								$payroll_salary_component_per=check_permission(HRMS_ROOT ."payroll_salary_component_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_salary_component_per){
							?> 
								<tr>
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_salary_component_list';?>">Salary Component</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$payroll_salary_structure_per=check_permission(HRMS_ROOT ."payroll_salary_structure_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_salary_structure_per){
							?>
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_salary_structure_list';?>">Salary Structure</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$payroll_salary_structure_assignment_per=check_permission(HRMS_ROOT ."payroll_salary_structure_assignment_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_salary_structure_assignment_per){
							?>
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_salary_structure_assignment_list';?>">Salary Structure Assignment</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$payroll_entry_per=check_permission(HRMS_ROOT ."payroll_entry_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_entry_per){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_entry_list';?>">Payroll Entry</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$payroll_salary_slip_per=check_permission(HRMS_ROOT ."payroll_salary_slip_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_salary_slip_per){
							?>
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_salary_slip_list';?>">Salary Slip</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<tr> 
								<th class="box_border_remove"></th>
								<th class="box_border_remove"></th>
							</tr>
							<tr> 
								<th class="box_border_remove"></th>
								<th class="box_border_remove"></th>
							</tr>
							<tr> 
								<th class="box_border_remove"></th>
								<th class="box_border_remove"></th>
							</tr>
							<tr> 
								<th class="box_border_remove"></th>
								<th class="box_border_remove"></th>
							</tr>
							<tr> 
								<th class="box_border_remove"></th>
								<th class="box_border_remove"></th>
							</tr>
						</table>
					</div>
				
				</div>
				
			</div>
			<!-- Employee Module End -->	
			<?php }  ?>
			<?
				$comp_per=check_permission("#taxation",$_SESSION['user_id'],'view',$dbcon);
				if($comp_per)
				{
					
			?>	
			<!-- Employee LifeCycle Start -->
			<div class="col-md-4">
				<div class="panel panel-primary">
					<div class="panel-heading">Payroll Taxation</div>
					<div class="panel-body" id="employee_lifecycle_table">
						<table class="table">
							<?php 
								$payroll_period=check_permission(HRMS_ROOT ."payroll_period_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_period){
							?>
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_period_list';?>">Payroll Period</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$payroll_income_tax_slab=check_permission(HRMS_ROOT ."payroll_income_tax_slab_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_income_tax_slab){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_income_tax_slab_list';?>">Income Tax Slab</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$payroll_emp_other_income=check_permission(HRMS_ROOT ."payroll_emp_other_income_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_emp_other_income){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_emp_other_income_list';?>">Employee Other Income</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$payroll_emp_tax_exemption_declaration=check_permission(HRMS_ROOT ."payroll_emp_tax_exemption_declaration_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_emp_tax_exemption_declaration){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_emp_tax_exemption_declaration_list';?>">Employee Tax Exemption Declaration</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$payroll_emp_exemption_proof_submission=check_permission(HRMS_ROOT ."payroll_emp_exemption_proof_submission_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_emp_exemption_proof_submission){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_emp_exemption_proof_submission_list';?>">Employee Tax Exemption Proof Submission</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$payroll_emp_exemption_category=check_permission(HRMS_ROOT ."payroll_emp_exemption_category_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_emp_exemption_category){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_emp_exemption_category_list';?>">Employee Tax Exemption Category</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$payroll_emp_exemption_subcategory=check_permission(HRMS_ROOT ."payroll_emp_exemption_subcategory_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_emp_exemption_subcategory){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_emp_exemption_subcategory_list';?>">Employee Tax Exemption Sub Category</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>	
						</table>
					</div>
				</div>
			</div>
			<!-- Employee LifeCycle End -->	
			<?php }  ?>

			
			<div class="col-md-4">
				<?
					$payroll_compensations=check_permission("#payroll_compensations",$_SESSION['user_id'],'view',$dbcon);
					if($payroll_compensations){	
				?>
				<div class="panel panel-primary">
					<div class="panel-heading">Payroll Compensations</div>
					<div class="panel-body" id="crm_table_data1">
						<table class="table">
							<?php 
								$payroll_additional_salary=check_permission(HRMS_ROOT ."payroll_additional_salary_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_additional_salary){
							?>
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_additional_salary_list';?>">Additional Salary</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$payroll_retention_bonus=check_permission(HRMS_ROOT ."payroll_retention_bonus_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_retention_bonus){
							?>
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_retention_bonus_list';?>">Retention Bonus</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$payroll_employee_incentive=check_permission(HRMS_ROOT ."payroll_emp_incentive_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_employee_incentive){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_emp_incentive_list';?>">Employee Incentive</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$payroll_employee_benefit_application=check_permission(HRMS_ROOT ."payroll_emp_benefit_application_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_employee_benefit_application){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_emp_benefit_application_list';?>">Employee Benefit Application</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$payroll_employee_benefit_claim=check_permission(HRMS_ROOT ."payroll_emp_benefit_claim_list",$_SESSION['user_id'],'view',$dbcon);
								if($payroll_employee_benefit_claim){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'payroll_emp_benefit_claim_list';?>">Employee Benefit Claim</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<tr> 
								<th class="box_border_remove"></th>
								<th class="box_border_remove"></th>
							</tr>
							<tr> 
								<th class="box_border_remove"></th>
								<th class="box_border_remove"></th>
							</tr>
							<tr> 
								<th class="box_border_remove"></th>
								<th class="box_border_remove"></th>
							</tr>
							<tr> 
								<th class="box_border_remove"></th>
								<th class="box_border_remove"></th>
							</tr>
							<tr> 
								<th class="box_border_remove"></th>
								<th class="box_border_remove"></th>
							</tr>	
						</table>
					</div>
				</div>
				<?php }  ?>
			</div>
		
            </div>
			<div class="col-md-12">
				<!-- Payroll Reports Start -->
				<?
			        $payroll_reports_per=check_permission("#payroll_reports",$_SESSION['user_id'],'view',$dbcon);
			        if($payroll_reports_per){
			    ?>
				<div class="col-md-4">
					<div class="panel panel-primary">
					
						<div class="panel-heading">Reports</div>
						
						<div class="panel-body">
							
							<table class="table">
								<?php 
									$payroll_salary_register_list=check_permission(HRMS_ROOT ."payroll_salary_register_list",$_SESSION['user_id'],'view',$dbcon);
									if($payroll_salary_register_list){
								?>
									<tr>
										<th><a href="javascript:;">Salary Register</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$payroll_salary_payments_based_on_payment_mode_list=check_permission(HRMS_ROOT ."payroll_salary_payments_based_on_payment_mode_list",$_SESSION['user_id'],'view',$dbcon);
									if($payroll_salary_payments_based_on_payment_mode_list){
								?>
									<tr>
										<th><a href="javascript:;">Salary Payments Based On Payment Mode</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$payroll_salary_payments_via_ecs_list=check_permission(HRMS_ROOT ."payroll_salary_payments_via_ecs_list",$_SESSION['user_id'],'view',$dbcon);
									if($payroll_salary_payments_via_ecs_list){
								?>	
									<tr>
										<th><a href="javascript:;">Salary Payments via ECS</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$payroll_income_tax_deductions_list=check_permission(HRMS_ROOT ."payroll_income_tax_deductions_list",$_SESSION['user_id'],'view',$dbcon);
									if($payroll_income_tax_deductions_list){
								?>	
									<tr>
										<th><a href="javascript:;">Income Tax Deductions</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$payroll_professional_tax_deductions_list=check_permission(HRMS_ROOT ."payroll_professional_tax_deductions_list",$_SESSION['user_id'],'view',$dbcon);
									if($payroll_professional_tax_deductions_list){
								?>	
									<tr>
										<th><a href="javascript:;">Professional Tax Deductions</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$payroll_provident_fund_deductions_list=check_permission(HRMS_ROOT ."payroll_provident_fund_deductions_list",$_SESSION['user_id'],'view',$dbcon);
									if($payroll_provident_fund_deductions_list){
								?>	
									<tr>
										<th><a href="javascript:;">Provident Fund Deductions</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$payroll_bank_remittance_list=check_permission(HRMS_ROOT ."payroll_bank_remittance_list",$_SESSION['user_id'],'view',$dbcon);
									if($payroll_bank_remittance_list){
								?>	
									<tr>
										<th><a href="javascript:;">Bank Remittance</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
							</table>
							
						</div>
					
					</div>
				</div>
			<?php }   ?>	
			<!-- Payroll Reports End -->
		
			<div class="col-md-4">
				<!-- Payroll Settings Start -->
				<?
			        $payroll_settings_per=check_permission("#payroll_settings",$_SESSION['user_id'],'view',$dbcon);
			        if($payroll_settings_per){
			    ?>    
					<div class="panel panel-primary">
						<div class="panel-heading">Payroll Settings</div>
						<div class="panel-body">
							<table class="table">
								<?php 
									$payroll_settings_list=check_permission(HRMS_ROOT ."payroll_settings_list",$_SESSION['user_id'],'view',$dbcon);
									if($payroll_settings_list){
								?>
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'payroll_settings_list';?>">Payroll Settings</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<tr>
									<th class="box_border_remove"></th>
									<td class="box_border_remove"></td>
								</tr>
								<tr>
									<th class="box_border_remove"></th>
									<td class="box_border_remove"></td>
								</tr>
								<tr>
									<th class="box_border_remove"></th>
									<td class="box_border_remove"></td>
								</tr>
								<tr>
									<th class="box_border_remove"></th>
									<td class="box_border_remove"></td>
								</tr>
								<tr>
									<th class="box_border_remove"></th>
									<td class="box_border_remove"></td>
								</tr>
								<tr>
									<th class="box_border_remove"></th>
									<td class="box_border_remove"></td>
								</tr>
								<tr>
									<th class="box_border_remove"></th>
									<td class="box_border_remove"></td>
								</tr>
								<tr>
									<th class="box_border_remove"></th>
									<td class="box_border_remove"></td>
								</tr>
								<tr>
									<th class="box_border_remove"></th>
									<td class="box_border_remove"></td>
								</tr>
							</table>
							
						</div>
					
					</div>
				<?php }   ?>	
				<!-- Employee Section End -->	
				</div>
				
			</div>
			</div>
	</div>
</section>