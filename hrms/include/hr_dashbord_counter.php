<link href="assets/morris.js-0.4.3/morris.css" rel="stylesheet" />
<style type="text/css">
	.count , .count2{ margin:0px !important; padding:0px !important; }
	.cc_count { margin-left:5%; }
	.panel-heading { text-align:center; font-weight:bold; FONT-SIZE:16px; }
	.border_line { border-bottom:dotted blue 2px; }
	.link_dash { border-bottom:dotted blue thin; }
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
                            <a href="<?php echo ROOT . HRMS_ROOT .'hr_dashboard';?>" class="label_text">Dashboard</a>
                        </div>
                    </div>
                </div>
                <div class="state-overview">
                    <div class="icons icons12 success">
                    	<div class="icon1 success">
                            <i class="fa fa-users" aria-hidden="true"></i>
                        </div>
                        <div class="" style="margin:10px;">
                            <a href="<?php echo ROOT . HRMS_ROOT .'hrms_employee';?>" class="label_text">Employee</a>
                        </div>
                    </div>
                </div>
                <div class="state-overview">
                    <div class="icons icons12 info">
                    	<div class="icon1 info">
                            <i class="fa fa-envelope-open" aria-hidden="true"></i>
                        </div>
                        <div class="" style="margin:10px;">
                            <a href="<?php echo ROOT . HRMS_ROOT .'hrms_leave_allocation_list';?>" class="label_text">Leave Application</a>
                        </div>
                    </div>
                </div>
                <div class="state-overview">
                    <div class="icons icons12 mustard">
                    	<div class="icon1 mustard">
                            <i class="fa fa-file" aria-hidden="true"></i>
                        </div>
                        <div class="" style="margin:10px;">
                            <a href="<?php echo ROOT . HRMS_ROOT .'hrms_attendance_list';?>" class="label_text">Attendance</a>
                        </div>
                    </div>
                </div>
                <div class="state-overview">
                    <div class="icons icons12 yellow">
                    	<div class="icon1 yellow">
                            <i class="fa fa-cog aria-hidden="true"></i>
                        </div>
                        <div class="" style="margin:10px;">
                            <a href="<?php echo ROOT. HRMS_ROOT .'hrms_settings_list';?>" class="label_text">Settings</a>
                        </div>
                    </div>
                </div>
                <div class="state-overview">
                    <div class="icons icons12 pink">
                    	<div class="icon1 pink">
                            <i class="fa fa-bar-chart-o" aria-hidden="true"></i>
                        </div>
                        <div class="" style="margin:10px;">
                            <a href="<?php echo ROOT . HRMS_ROOT .'hrms_employee_birthday';?>" class="label_text">Reports</a>
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
                        <?php 
				$comp_per=check_permission("#employee",$_SESSION['user_id'],'view',$dbcon);
				if($comp_per)
				{
					
			?>	
			<!-- Pending follow-ups Section Start -->
			<div class="col-md-4">
				<div class="panel panel-primary">
				
					<div class="panel-heading">Employee Modules</div>
					
					<div class="panel-body" id="crm_table_data">
						<table class="table">
							<?php 
								$hrms_employee_per=check_permission(HRMS_ROOT ."hrms_employee",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_employee_per){
							?> 
								<tr>
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'hrms_employee';?>">Employee</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$hrms_emp_type=check_permission(HRMS_ROOT ."hrms_emp_type",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_emp_type){
							?>
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'hrms_emp_type';?>">Employee Type</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$hrms_branch_mst=check_permission(HRMS_ROOT ."branch_mst",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_branch_mst){
							?>
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'branch_mst';?>">Branch</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$hrms_department=check_permission(HRMS_ROOT ."hrms_department",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_department){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'hrms_department';?>">Department</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$hrms_designation=check_permission(HRMS_ROOT ."hrms_designation",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_designation){
							?>
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'hrms_designation';?>">Designation</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$hrms_emp_grade=check_permission(HRMS_ROOT ."hrms_emp_grade",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_emp_grade){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'hrms_emp_grade';?>">Employee Grade</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$hrms_group_list=check_permission(HRMS_ROOT ."group_list",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_group_list){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'group_list';?>">Employee Group</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$hrms_emp_health_insurance=check_permission(HRMS_ROOT ."hrms_emp_health_insurance",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_emp_health_insurance){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'hrms_emp_health_insurance';?>">Employee Health Insurance</a>
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
						</table>
					</div>
				
				</div>
				
			</div>
			<!-- Employee Module End -->	
			<?php }  ?>
			<?php 
				$comp_per=check_permission("#employee_life_cycle",$_SESSION['user_id'],'view',$dbcon);
				if($comp_per)
				{
					
			?>	
			<!-- Employee LifeCycle Start -->
			<div class="col-md-4">
				<div class="panel panel-primary">
					<div class="panel-heading">Employee LifeCycle</div>
					<div class="panel-body" id="employee_lifecycle_table">
						<table class="table">
							<?php 
								$hrms_employee_onboarding=check_permission(HRMS_ROOT ."hrms_employee_onboarding_list",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_employee_onboarding){
							?>
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'hrms_employee_onboarding_list';?>">Employee Onboarding</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$hrms_employee_skill_map=check_permission(HRMS_ROOT ."hrms_employee_skill_map_list",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_employee_skill_map){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'hrms_employee_skill_map_list';?>">Employee Skill Map</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$hrms_employee_promotion=check_permission(HRMS_ROOT ."hrms_employee_promotion_list",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_employee_promotion){
							?>	
								<tr> 
									<th>
										<a href="javascript:;">Employee Promotion</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$hrms_employee_transfer=check_permission(HRMS_ROOT ."hrms_employee_transfer_list",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_employee_transfer){
							?>	
								<tr> 
									<th>
										<a href="javascript:;">Employee Transfer</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$hrms_employee_separation=check_permission(HRMS_ROOT ."hrms_employee_separation_list",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_employee_separation){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'hrms_employee_separation_list';?>">Employee Separation</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$hrms_employee_onboarding_template=check_permission(HRMS_ROOT ."hrms_employee_onboarding_template_list",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_employee_onboarding_template){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'hrms_employee_onboarding_template_list';?>">Employee Onboarding Template</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$hrms_employee_separation_template=check_permission(HRMS_ROOT ."hrms_employee_separation_template_list",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_employee_separation_template){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'hrms_employee_separation_template_list';?>">Employee Separation Template</a>
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
			<!-- Employee LifeCycle End -->	
			<?php }  ?>

			
			<div class="col-md-4">
				<?php 
					$shift_per=check_permission("#shift_management",$_SESSION['user_id'],'view',$dbcon);
					if($shift_per){	
				?>
				<div class="panel panel-primary">
					<div class="panel-heading">Shift Management</div>
					<div class="panel-body" id="crm_table_data1">
						<table class="table">
							<?php 
								$hrms_shift_type=check_permission(HRMS_ROOT ."hrms_shift_type",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_shift_type){
							?>
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'hrms_shift_type';?>">Shift Type</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$hrms_shift_request=check_permission(HRMS_ROOT ."hrms_shift_request",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_shift_request){
							?>
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'hrms_shift_request';?>">Shift Request</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>
							<?php 
								$hrms_shift_assignment=check_permission(HRMS_ROOT ."hrms_shift_assignment",$_SESSION['user_id'],'view',$dbcon);
								if($hrms_shift_assignment){
							?>	
								<tr> 
									<th>
										<a href="<?php echo ROOT . HRMS_ROOT .'hrms_shift_assignment';?>">Shift Assignment</a>
									</th>
									<th>0</th>
								</tr>
							<?php } ?>	
						</table>
					</div>
				</div>
				<?php }  ?>
				<?php 
					$expense_claim_per=check_permission("#expense_claim",$_SESSION['user_id'],'view',$dbcon);
					if($expense_claim_per){	
				?>
					<div class="panel panel-primary">
						<div class="panel-heading">Expense Claims</div>
						<div class="panel-body" id="crm_table_data1">
							<table class="table">
								<?php 
									$hrms_expense_claim=check_permission(HRMS_ROOT ."hrms_expense_claim_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_expense_claim){
								?>
									<tr> 
										<th>
											<a href="<?php echo ROOT . HRMS_ROOT .'hrms_expense_claim_list';?>">Expense Claim</a>
										</th>
										<th>0</th>
									</tr>
								<?php } ?>
								<?php 
									$hrms_employee_advance=check_permission(HRMS_ROOT ."hrms_employee_advance",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_employee_advance){
								?>	
									<tr> 
										<th>
											<a href="<?php echo ROOT . HRMS_ROOT .'hrms_employee_advance';?>">Employee Advance</a>
										</th>
										<th>0</th>
									</tr>
								<?php } ?>	
							</table>
						</div>
					</div>
				<?php }  ?>
			</div>
		
            </div>
			<div class="col-md-12">
				<!-- Leaves Section Start -->
				<?php 
			        $comp_per=check_permission("#hrms_leave",$_SESSION['user_id'],'view',$dbcon);
			        if($comp_per){
			    ?>
				<div class="col-md-4">
					<div class="panel panel-primary">
					
						<div class="panel-heading">Leaves</div>
						
						<div class="panel-body">
							
							<table class="table">
								<?php 
									$hrms_leave_application_list=check_permission(HRMS_ROOT ."hrms_leave_application_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_leave_application_list){
								?>
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_leave_application_list';?>">Leave Application</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_leave_allocation_list=check_permission(HRMS_ROOT ."hrms_leave_allocation_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_leave_allocation_list){
								?>
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_leave_allocation_list';?>">Leave Allocation</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_leave_policy_list=check_permission(HRMS_ROOT ."hrms_leave_policy_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_leave_policy_list){
								?>	
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_leave_policy_list';?>">Leave Policy</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_leave_period_list=check_permission(HRMS_ROOT ."hrms_leave_period_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_leave_period_list){
								?>	
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_leave_period_list';?>">Leave Period</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_leave_type_list=check_permission(HRMS_ROOT ."hrms_leave_type_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_leave_type_list){
								?>	
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_leave_type_list';?>">Leave Type</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_holiday_list=check_permission(HRMS_ROOT ."hrms_holiday_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_holiday_list){
								?>	
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_holiday_list';?>">Holiday List</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_comp_leave_request_list=check_permission(HRMS_ROOT ."hrms_comp_leave_request_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_comp_leave_request_list){
								?>
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_comp_leave_request_list';?>">Compensatory Leave Request</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_leave_encashment_list=check_permission(HRMS_ROOT ."hrms_leave_encashment_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_leave_encashment_list){
								?>	
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_leave_encashment_list';?>">Leave Encashment</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_leave_block_list=check_permission(HRMS_ROOT ."hrms_leave_block_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_leave_block_list){
								?>
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_leave_block_list';?>">Leave Block List</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_employee_leave_balance=check_permission(HRMS_ROOT ."hrms_employee_leave_balance",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_employee_leave_balance){
								?>	
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_employee_leave_balance';?>">Employee Leave Balance</a></th>
										<td>0</td>
									</tr>
								<?php } ?>	
							</table>
							
						</div>
					
					</div>
				</div>
			<?php }   ?>	
			<!-- Leaves Section End -->
		
			<div class="col-md-4">
				<!-- Attendance Module Start -->
				<?php 
			        $attendance_per=check_permission("#attendance",$_SESSION['user_id'],'view',$dbcon);
			        if($attendance_per){
			    ?>    
					<div class="panel panel-primary">
						<div class="panel-heading">Attendance</div>
						<div class="panel-body">
							<table class="table">
								<?php 
									$hrms_attendance_tools=check_permission(HRMS_ROOT ."hrms_attendance_tools",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_attendance_tools){
								?>
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_attendance_tools';?>">Employee Attendance Tool</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_attendance_list=check_permission(HRMS_ROOT ."hrms_attendance_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_attendance_list){
								?>		
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_attendance_list';?>">Attendance</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_attendance_request_list=check_permission(HRMS_ROOT ."hrms_attendance_request_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_attendance_request_list){
								?>	
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_attendance_request_list';?>">Attendance Request</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_upload_attendance_list=check_permission(HRMS_ROOT ."hrms_upload_attendance_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_upload_attendance_list){
								?>	
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_upload_attendance_list';?>">Upload Attendance</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_employee_checkin_list=check_permission(HRMS_ROOT ."hrms_employee_checkin_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_employee_checkin_list){
								?>	
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_employee_checkin_list';?>">Employee Checkin</a></th>
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
				
				<div class="col-md-4">
				    <!-- Setting Module Start -->
				<?php 
			        $hr_setting_per=check_permission("#hr_settings",$_SESSION['user_id'],'view',$dbcon);
			        if($hr_setting_per){
			    ?>
					<div class="panel panel-primary">
					
						<div class="panel-heading">Settings</div>
						<div class="panel-body">
							<table class="table">
								<?php 
									$hrms_letter_head_list=check_permission(HRMS_ROOT ."hrms_letter_head_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_letter_head_list){
								?>
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_letter_head_list';?>">Letter Head</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_settings_list=check_permission(HRMS_ROOT ."hrms_settings_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_settings_list){
								?>	
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_settings_list';?>">HR Settings</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_email_template_list=check_permission(HRMS_ROOT ."hrms_email_template_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_email_template_list){
								?>	
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_email_template_list';?>">HRMS Email Template</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_sms_template_list=check_permission(HRMS_ROOT ."hrms_sms_template_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_sms_template_list){
								?>
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_sms_template_list';?>">HRMS SMS Template</a></th>
										<td>0</td>
									</tr>
								<?php } ?>
								<?php 
									$hrms_daily_summary_group=check_permission(HRMS_ROOT ."hrms_daily_work_summary_group_list",$_SESSION['user_id'],'view',$dbcon);
									if($hrms_daily_summary_group){
								?>	
									<tr>
										<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_daily_work_summary_group_list';?>">Daily Work Summary Group</a></th>
										<td>0</td>
									</tr>
								<?php } ?>	
							</table>
						</div>
					
					</div>
					<?php }   ?>
					<!-- Setting Module End -->
					
					<!-- Report Module Start -->
					<?php 
				        $reports_per=check_permission("#hrms_report",$_SESSION['user_id'],'view',$dbcon);
				        if($reports_per){
				    ?>	
						<div class="panel panel-primary">
							<div class="panel-heading">Reports</div>
							<div class="panel-body">
								<table class="table">
									<?php 
										$hrms_monthly_attendance_sheet=check_permission(HRMS_ROOT ."hrms_monthly_attendance_sheet",$_SESSION['user_id'],'view',$dbcon);
										if($hrms_monthly_attendance_sheet){
									?>	
										<tr>
											<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_monthly_attendance_sheet';?>">Monthly Attendance Sheet</a></th>
											<td>0</td>
										</tr>
									<?php } ?>
									<?php 
										$hrms_employee_birthday=check_permission(HRMS_ROOT ."hrms_employee_birthday",$_SESSION['user_id'],'view',$dbcon);
										if($hrms_employee_birthday){
									?>
										<tr>
											<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_employee_birthday';?>">Employee Birthday</a></th>
											<td>0</td>
										</tr>
									<?php } ?>
									<?php 
										$hrms_employee_working_on_holiday=check_permission(HRMS_ROOT ."hrms_employee_working_on_holiday",$_SESSION['user_id'],'view',$dbcon);
										if($hrms_employee_working_on_holiday){
									?>		
										<tr>
											<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_employee_working_on_holiday';?>">Employees Working On a Holiday</a></th>
											<td>0</td>
										</tr>
									<?php } ?>	
								</table>
							</div>
						</div>
					<?php }   ?>	
					<!-- Report Module End -->
				</div>
			</div>
			<div class="col-md-12">
				<div class="col-md-4">
					<!-- Performance Module Start -->
					<?php 
				        $performance_per=check_permission("#hr_performance",$_SESSION['user_id'],'view',$dbcon);
				        if($performance_per){
				    ?>	
						<div class="panel panel-primary">
							<div class="panel-heading">Performance</div>
							<div class="panel-body">
								<table class="table">
									<?php 
										$hrms_appraisal_list=check_permission(HRMS_ROOT ."hrms_appraisal_list",$_SESSION['user_id'],'view',$dbcon);
										if($hrms_appraisal_list){
									?>	
										<tr>
											<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_appraisal_list';?>">Appraisal</a></th>
											<td>0</td>
										</tr>
									<?php } ?>
									<?php 
										$hrms_appraisal_template_list=check_permission(HRMS_ROOT ."hrms_appraisal_template_list",$_SESSION['user_id'],'view',$dbcon);
										if($hrms_appraisal_template_list){
									?>
										<tr>
											<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_appraisal_template_list';?>">Appraisal Template</a></th>
											<td>0</td>
										</tr>
									<?php } ?>
									<?php 
										$hrms_energy_point_rule_list=check_permission(HRMS_ROOT ."hrms_energy_point_rule_list",$_SESSION['user_id'],'view',$dbcon);
										if($hrms_energy_point_rule_list){
									?>		
										<tr>
											<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_energy_point_rule_list';?>">Energy Point Rule</a></th>
											<td>0</td>
										</tr>
									<?php } ?>
									<?php 
										$hrms_energy_point_log_list=check_permission(HRMS_ROOT ."hrms_energy_point_log_list",$_SESSION['user_id'],'view',$dbcon);
										if($hrms_energy_point_log_list){
									?>		
										<tr>
											<th><a href="<?php echo ROOT . HRMS_ROOT .'hrms_energy_point_log_list';?>">Energy Point Log</a></th>
											<td>0</td>
										</tr>
									<?php } ?>	
								</table>
							</div>
						</div>
					<?php }   ?>	
					<!-- Report Module End -->
					</div>
				</div>
			</div>
	</div>
</section>