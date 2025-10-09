<link href="assets/morris.js-0.4.3/morris.css" rel="stylesheet" />
<style type="text/css">
	.count , .count2
	{
		margin:0px !important;
		padding:0px !important

	}
	.cc_count
	{
		margin-left:5%;
	}
	
	.panel-heading
	{
		text-align:center;
		font-weight:bold;
		FONT-SIZE:16px;
	}
	
	.border_line
	{
		border-bottom:dotted blue 2px;
	}
	
	.link_dash
	{
		border-bottom:dotted blue thin;
	}
	
</style>
<?php $user_id = $_SESSION['user_id']; ?>
<section class="panel">
	<div class="panel-body ">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="col-md-3 control-label" style="text-align: right;">Employee :</label>
                            <div class="col-md-4"> 
                                <select class="select2" id="user_id" name="user_id" onChange="reload_data();">
                                    <?= get_assign_users_inq($dbcon,$user_id); ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="" size="30" />
		<div class="row">
                    <div class="col-md-12">
                        <?php 
				$comp_per=check_permission("#team_pend_tasks_sec",$user_id,'view',$dbcon);
				if($comp_per)
				{
					
			?>	
			<!-- Pending follow-ups Section Start -->
			<div class="col-md-4">
				<div class="panel panel-primary">
				
					<div class="panel-heading">Team Pending Tasks</div>
					
					<div class="panel-body" id="crm_table_data">
						<table class="table">
<!--							<tr> 
								<th colspan="2">
									<select class="form-control" name="crm_tree_user" id="crm_tree_user" onchange="crm_task_data_load();" >
										<?=get_tree_user($dbcon,$_SESSION['user_id'],$_SESSION['user_id']);?>
									</select>
								</th>
							</tr>-->
							<tr> 
								<th>
									<a target="_blank" href="<?php echo ROOT.'inquiry_add';?>">Add Inquiry</a>
								</th>
								<th></th>
							</tr>
							<?php 
								 $query="select mcd_id,mcd_name from tbl_master_category_detail where mcd_status=0 and mcd_cat_id=10 order by priority ASC";
								$query_rs=$dbcon->query($query);
								while($row_p=mysqli_fetch_assoc($query_rs))
								{
							?>
								<tr> 
									<th>
										<a target="_blank" href="<?php echo ROOT.'pending_task_list/'.$row_p['mcd_id'].'/'.$_SESSION['user_id'];?>"><?=$row_p['mcd_name']?></a>
									</th>
									<th><?=count_usr_pen_tsk($dbcon,$row_p['mcd_id'],$user_id);?></th>
								</tr>
							<?php
								$cnt++;
								}
							?>
<!--							<tr> 
								<th>
									<a target="_blank" href="<?php echo ROOT.'order_confirm_list/pen_po';?>">Pending P.O. Upload</a>
								</th>
								<th><?php //=count_pend_po_upload($dbcon,$user_id);?></th>
							</tr>
							<tr> 
								<th>
									<a target="_blank" href="<?php echo ROOT.'dispatch_list';?>">Pending Dispatch</a>
								</th>
								<th><?php //=count_pend_disp($dbcon);?></th>
							</tr>-->
							<tr> 
								<th>
									<a target="_blank" href="<?php echo ROOT.'pending_appointment_list';?>">Upcoming Appointments</a>
								</th>
								<th><?=count_pend_appoint($dbcon,$user_id);?></th>
							</tr>
						</table>
					</div>
				
				</div>
				
			</div>
			<!-- Pending follow-ups Section End -->	
			<?php }  ?>
			<?php 
				$comp_per=check_permission("#pend_tasks_sec",$_SESSION['user_id'],'view',$dbcon);
				if($comp_per)
				{
					
			?>	
			<!-- Pending follow-ups Section Start -->
			<div class="col-md-4">
				<div class="panel panel-primary">
				
					<div class="panel-heading">Pending Tasks</div>
					
					<div class="panel-body" id="crm_table_data1">
						<table class="table">
<!--							<tr> 
								<th colspan="2">
									<select class="form-control" name="crm_tree_user1" id="crm_tree_user1" onchange="crm_task_data_load1();" >
										<?=get_tree_user($dbcon,$_SESSION['user_id'],$_SESSION['user_id']);?>
									</select>
								</th>
							</tr>-->
							<tr> 
								<th>
									<a target="_blank" href="<?php echo ROOT.'inquiry_add';?>">Add Inquiry</a>
								</th>
								<th></th>
							</tr>
							<?php 
								 $query="select mcd_id,mcd_name from tbl_master_category_detail where mcd_status=0 and mcd_cat_id=10 order by priority ASC";
								$query_rs=$dbcon->query($query);
								while($row_p=mysqli_fetch_assoc($query_rs))
								{
							?>
								<tr> 
									<th>
										<a target="_blank" href="<?php echo ROOT.'pending_task_list_one/'.$row_p['mcd_id'].'/'.$_SESSION['user_id'];?>"><?=$row_p['mcd_name']?></a>
									</th>
									<th><?=count_usr_pen_tsk1($dbcon,$row_p['mcd_id'],$_SESSION['user_id']);?></th>
								</tr>
							<?php
								$cnt++;
								}
							?>
<!--							<tr> 
								<th>
									<a target="_blank" href="<?php echo ROOT.'order_confirm_list/pen_po';?>">Pending P.O. Upload</a>
								</th>
								<th><?php //=count_pend_po_upload($dbcon,$_SESSION['user_id']);?></th>
							</tr>
							<tr> 
								<th>
									<a target="_blank" href="<?php echo ROOT.'dispatch_list';?>">Pending Dispatch</a>
								</th>
								<th><?php //=count_pend_disp($dbcon);?></th>
							</tr>-->
							<tr> 
								<th>
									<a target="_blank" href="<?php echo ROOT.'pending_appointment_list';?>">Upcoming Appointments</a>
								</th>
								<th><?=count_pend_appoint($dbcon,$user_id);?></th>
							</tr>
						</table>
					</div>
				
				</div>
				
			</div>
			<!-- Pending follow-ups Section End -->	
			<?php }  ?>
                    </div>
			<div class="col-md-12">
				<!-- Complaint Section Start -->
				<?php 
			        $comp_per=check_permission("#COMPLAINT",$_SESSION['user_id'],'view',$dbcon);
			        if($comp_per){
			    ?>
				<div class="col-md-4">
					<div class="panel panel-primary">
					
						<div class="panel-heading">COMPLAINT</div>
						
						<div class="panel-body">
							
							<table class="table">
								
								<?php if($_SESSION['user_type']!='3'){ ?>
								<tr>
									<th><a href="<?php echo ROOT.'comp_type/1';?>">New Complaint Registered</a></th>
									<td><span id="bussiness_registered"></span></td>
								</tr>
								<?php } ?>
								
								<tr>
									<th><a href="<?php echo ROOT.'comp_type/2';?>">Complaint Assigned</a></th>
									<td><span id="bussiness_assign"></span></td>
								</tr>
								
							<!--	<tr>
									<th><a href="<?php echo ROOT."complaint_list?type=1";?>">Complaint Unassigned</a></th>
									<td><span id="bussiness_unassign"></span></td>
								</tr> -->
								
								<tr>
									<th><a href="<?php echo ROOT.'comp_type/7';?>">Employess Started</a></th>
									<td><span id="bussiness_e_start"></span></td>
								</tr>
								
								<tr>
									<th><a  href="<?php echo ROOT.'comp_type/2';?>" >Employess Not Started</a></th>
									<td><span id="bussiness_e_notstart"></span></td>
								</tr>
								
								<tr>
									<th> <a href="<?php echo ROOT.'comp_type/4';?>">Closed</a></th>
									<td><span id="bussiness"></span></td>
								</tr>
								
								<tr>
									<th><a href="<?php echo ROOT.'comp_type/5';?>">Not Done</a></th>
									<td><span id="turnover"></span></td>
								</tr>
								<tr>
									<th><a href="<?php echo ROOT.'complaint_list';?>">Total Complaint</a></th>
									<td><span id="all_comp_cnt"></span></td>
								</tr>
								
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
								
							</table>
							
						</div>
					
					</div>
				</div>
			<?php }   ?>	
		<!-- Complaint Section End -->
		
				<div class="col-md-4">
				    <!-- Employee Section Start -->
				<?php 
			        $emp_per=check_permission("#employee",$_SESSION['user_id'],'view',$dbcon);
			        if($emp_per){
			    ?>    
					<div class="panel panel-primary">
					
						<div class="panel-heading">EMPLOYEE</div>
						
						<div class="panel-body">
							
							<table class="table">
								
								<?php if($_SESSION['user_type']!='3'){ ?>
								<tr>
									<th><a href="<?php echo ROOT."employee_list?type=present";?>">Employee Present</a></th>
									<td><span id="e_present"></span></td>
								</tr>
								<?php } ?>
								
								<?php if($_SESSION['user_type']!='3'){ ?>
								<tr>
									<th><a href="<?php echo ROOT."employee_list?type=absent";?>">Employee Absent</a></th>
									<td><span id="e_absent"></span></td>
								</tr>
								<?php } ?>
								
								<tr>
									<th>
										<?php if($_SESSION['user_type']!='3'){ ?>
											<a href="<?php echo ROOT."employee_expense";?>">
										<?php } else {  ?>
											<a href="<?php echo ROOT."expense_detail";?>">
										<?php } ?>
										Expense Pending
										</a>
									</th>
									<td><span id="exp_approval"></span></td>
								</tr>
								
							</table>
							
						</div>
					
					</div>
				<?php }   ?>	
				<!-- Employee Section End -->
				
				<!-- Spare Parts Section Start -->
				<?php 
			        $spare_per=check_permission("#SPARE",$_SESSION['user_id'],'view',$dbcon);
			        if($spare_per){
			    ?>
					<div class="panel panel-primary">
					
						<div class="panel-heading">SPARE PARTS</div>
						
						<div class="panel-body">
							
							<table class="table">
								
								<?php 
									
									$usertype=$_SESSION['user_type'];
									if($usertype!='3'){
								?>
								<tr>
									<th><a href="<?php echo ROOT."spare_list_pending";?>" >Spare Part To send</a></th>
									<td><span id="new_spare"></span></td>
								</tr>
								
								<tr>
									<th><a href="<?php echo ROOT."return_old_spare";?>" >Spare Part To Receive</a></th>
									<td><span id="old_spare"></span></td>
								</tr>
								<?php } else { ?>
								
								<tr>
									<th><a href="<?php echo ROOT."spare_list_pending";?>" >Spare Part To Receive</a></th>
									<td><span id="new_spare"></span></td>
								</tr>
								
								<tr>
									<th><a href="<?php echo ROOT."return_old_spare";?>" >Spare Part To Send</a></th>
									<td><span id="old_spare"></span></td>
								</tr>
								
								
								<?php } ?>
							</table>
							
						</div>
					
					</div>
				<?php }   ?>	
				<!-- Spare Part Section End -->	
				</div>
				
				<div class="col-md-4">
				    <!-- Out of Stock Section Start -->
				<?php 
			        $out_per=check_permission("#OUT",$_SESSION['user_id'],'view',$dbcon);
			        if($out_per){
			    ?>
					<div class="panel panel-primary">
					
						<div class="panel-heading">MRP</div>
						
						<div class="panel-body">
							
							<table class="table table-hover personal-task">
								 <thead>
								    <tr>
										  <td class="text-left "><a class="border_line1" href="<?php echo ROOT.'get_sales_order_details'; ?>">Sales Order Wise Planning</a></td>
										  <td class="text-center"><?php echo count_so_procuct_req($dbcon); ?></td>
									</tr>
									  <tr>
										  <td class="text-left "><a class="border_line1" href="<?php echo ROOT.'get_stock_detail/min_max'; ?>">Min-Max Planning</a></td>
										  <td class="text-center"><?php echo count_min_max($dbcon,'min_max'); ?></td>
									  </tr>
									   <tr>
										  <td class="text-left "><a class="border_line1" href="<?php echo ROOT.'stock_pending_request'; ?>">Requisition By All Department</a></td>
										  <td class="text-center"><?php echo count_stock_procuct_req($dbcon); ?></td>
									  </tr>
									  <tr>
										  <td class="text-left "><a class="border_line1" href="<?php echo ROOT.'reject_qc_request_list'; ?>">Reject Product Planning</a></td>
										  <td class="text-center"><?php echo count_reject_procuct_req($dbcon); ?></td>
									  </tr>
									 
									
									<tr>
										  <td class="text-left "><a class="border_line1" href="<?php echo ROOT.'not_found'; ?>">forecast</a></td>
										  <td class="text-center"><?php echo count_so_procuct_req($dbcon); ?></td>
									</tr>
									
								 </thead>
					
							</table>
							
						</div>
					
					</div>
					<?php }   ?>
					<!-- Out of Stock Section End -->
					
					<!-- Pending Jobcard Section Start -->
				<?php 
			        $pend_job_per=check_permission("#PENDING",$_SESSION['user_id'],'view',$dbcon);
			        if($pend_job_per){
			    ?>	
					<div class="panel panel-primary">
					
						<div class="panel-heading">PENDING JOB CARD</div>
						
						<div class="panel-body">
							
							<table class="table table-hover personal-task">
								 <thead>
									  <tr>
										  <td class="text-left "><a class="border_line1" href="<?php echo ROOT."job_card_list";?>">Job Card</a></td>
										  <td><span id="pending_job_card_new"></span></td>
									  </tr>
									  <tr>
										  <td class="text-left "><a class="border_line1" href="<?php echo ROOT."pending_job_card";?>">Pending Job Work</a></td>
										  <td><span id="pending_job_card"></span></td>
									  </tr>
									  
								 </thead>
					
							</table>
							
						</div>
					
					</div>
				<?php }   ?>	
					<!-- Pending Jobcard Section End -->
					
				</div>
				
			</div>
			
			</div>
			
			<hr class="" size="30" />
			
			<div class="col-md-12">
				
				<?php 
			       $comp_per=check_permission("#po_pending",$_SESSION['user_id'],'view',$dbcon);
			        if($comp_per){
			    ?>
				<div class="col-md-4">
					<div class="panel panel-primary">
					
						<div class="panel-heading">Purchase</div>
						
						<div class="panel-body">
							
							<table class="table">
								
								<tr>
									<th><a href="<?php echo ROOT.'indent_list'; ?>">Pending Indent</a></th>
									<td><?php echo pending_indent_count($dbcon); ?></td>
								</tr>
								<tr>
									<th><a href="<?=ROOT.'po_req_list'?>">Purchase Order Pending</a></th>
									<td><span id="purchse_order_pending"></span></td>
								</tr>
								
								<tr>
									<th><a href="<?=ROOT.'overdue_po_pro_list'?>">Overdue Purchase Inward</a></th>
									<td><span id="purchse_overdue_pending"></span></td>
								</tr>
								<tr>
									<th><a href="<?=ROOT.'purchase_bill_pending_list'?>">Pending Purchase Bill</a></th>
									<td><span id="purchase_bill_pending"></span></td>
								</tr>
								<tr>
									<th><a href="<?=ROOT.'debit_note_pending_list'?>">Pending Debit Note</a></th>
									<td><span id="debit_note_pending"></span></td>
								</tr>
								
								<!--<tr>
									<th><a href="#">Total Inward Pending</a></th>
									<td><span id="total_inward_pending"></span></td>
								</tr>-->
								
							
							</table>
							
						</div>
					
					</div>
				</div>
				<?php }  ?>
				
				<?php 
			        $comp_per=check_permission("#qc_pending",$_SESSION['user_id'],'view',$dbcon);
			        if($comp_per){
			    ?>
				<div class="col-md-4">
					<div class="panel panel-primary">
					
						<div class="panel-heading">QC Pending</div>
						
						<div class="panel-body">
							
							<table class="table">
								<tr>
									<th><a href="<?=ROOT.'purchase_qc_pending_list'?>">Purchase QC Pending</a></th>
									<td><span id="purchase_qc_pending"></span></td>
								</tr>
								<tr>
									<th><a href="<?=ROOT.'parts_qc_pending_list'?>">Parts QC Pending</a></th>
									<td><span id="parts_qc_pending"></span></td>
								</tr>
								<!--<tr>
									<th><a href="<?=ROOT.'finish_pro_qc_pending_list'?>">Finish Product QC Pending</a></th>
									<td><span id="finish_qc_pending"></span></td>
								</tr>-->
								
								<!--<tr>
									<th><a href="#">Pending Debit Note</a></th>
									<td><span id="pending_debit_note"></span></td>
								</tr>-->
							</table>
							
						</div>
					
					</div>
				</div>
				<?php }  ?>
			<?php 
				$comp_per=check_permission("#manager_userwise_sec",$_SESSION['user_id'],'view',$dbcon);
				if($comp_per)
				{
			?>	
			<!-- Regeional Manager Section Start -->
			<div class="col-md-4">
				<div class="panel panel-primary">
				
					<div class="panel-heading">Userwise Inquiry</div>
					
					<div class="panel-body">
						<table class="table">
							<tr>
								<th>#</th>
								<th>User Name</th>
							</tr>
							
							<?php 
								$cnt=1;
								$query="select user_id,user_name from users where active=0 and report_to_user_id='$_SESSION[user_id]' and company_id='$_SESSION[company_id]'";
								$query_rs=$dbcon->query($query);
								while($row_p=mysqli_fetch_assoc($query_rs))
								{
							?>
								<tr> 
									<th><?php echo $cnt; ?></th>
									<th>
										<a href="<?php echo ROOT.'inquiry_list/'.$row_p['user_id'];?>"><?=$row_p['user_name']?></a>
									</th>
								</tr>
							<?php
								$cnt++;
								}
							?>
						</table>
					</div>
				
				</div>
				
			</div>
			<!-- Regeional Manager Section End -->	
			<?php }  ?>
			
			
			</div>
			
			<div class="col-md-12">
			
			    <!-- Inhouse Pending Section Start -->
			    <?php 
			        $inhouse_pend_per=check_permission("#INHOUSE",$_SESSION['user_id'],'view',$dbcon);
			        if($inhouse_pend_per){
			   ?>
				<div class="col-md-6">
					<div class="panel panel-primary">
					
						<div class="panel-heading">Inhouse Pending Process</div>
						
						<div class="panel-body" style="overflow:auto;">
							
							<table class="table" style="text-align:center">
								
								<tr>
									<th>#</th>
									<th>Process Name</th>
									<th>Total Pending</th>
									<th>Working Qty</th>
									<th>Reprocess Qty</th>
									<th>Opening Qty</th>
								</tr>
								
								<?php 
									$cnt=1;
									$sel_p=$dbcon->query("select * from process_mst where process_status='0'  order by process_name ");
									while($row_p=mysqli_fetch_assoc($sel_p))
									{
								?>
									<tr>
										<th><?php echo $cnt; ?></th>
										<th><?php echo $row_p['process_name']; ?></th>
										
										<th>
											<a href="<?php echo ROOT."process_detail_list/".$row_p['process_id']."/1";?>" class="link_dash"><?php echo count_process_qty($dbcon,$row_p['process_id'],'1'); ?></a>
										</th>
										
										<th>
											<a href="<?php echo ROOT."working_process_detail_list/".$row_p['process_id']."/1";?>" class="link_dash"><?php echo count_working_process_qty($dbcon,$row_p['process_id'],'1'); ?></a>
											
										</th>
										
										<th><a href="<?php echo ROOT."reprocess_detail_list/".$row_p['process_id']."/1";?>"  class="link_dash"><?php echo count_re_process_qty($dbcon,$row_p['process_id'],'1'); ?></a></th>
										
										<th>
											<a href="<?php echo ROOT."opening_detail_list/".$row_p['process_id']."/1";?>"  class="link_dash"><?php echo count_opening_process_qty($dbcon,$row_p['process_id'],'1'); ?></a>
											
										</th>
									</tr>
								<?php
									$cnt++;
									}
								?>
								
								
							</table>
							
						</div>
					
					</div>
					
				</div>
				<?php }   ?>
				<!-- Inhouse Pending Section End -->
				<!-- Outward Pending Section Start -->
				<?php 
			        $outhouse_pend_per=check_permission("#OUTWARD",$_SESSION['user_id'],'view',$dbcon);
			        if($outhouse_pend_per){
			   ?>
					<div class="col-md-6">
					<div class="panel panel-primary">
					
						<div class="panel-heading">Outward Pending Process</div>
						
						<div class="panel-body" style="overflow:auto;">
							
							<table class="table">
								
								<tr>
									<th>#</th>
									<th>Process Name</th>
									<th>Total Pending</th>
									<th>Working Qty</th>
									<th>Reprocess Qty</th>
									<th>Opening Qty</th>
								</tr>
								
								<?php 
									$cnt=1;
									$sel_p=$dbcon->query("select * from process_mst where process_status='0'  order by process_name ");
									while($row_p=mysqli_fetch_assoc($sel_p))
									{
								?>
									<tr > 
										<th><?php echo $cnt; ?></th>
										<th><?php echo $row_p['process_name']; ?></th>
										
										<th><a href="<?php echo ROOT."process_detail_list/".$row_p['process_id']."/2";?>"  class="link_dash"><?php echo count_process_qty($dbcon,$row_p['process_id'],'2'); ?></a></th>
										
										<th>
											<a href="<?php echo ROOT."working_process_detail_list/".$row_p['process_id']."/2";?>" class="link_dash"><?php echo count_working_process_qty($dbcon,$row_p['process_id'],'2'); ?></a>
										</th>
										
										<th><a href="<?php echo ROOT."reprocess_detail_list/".$row_p['process_id']."/2";?>"  class="link_dash"><?php echo count_re_process_qty($dbcon,$row_p['process_id'],'2'); ?></a></th>
										
										<th>
											<a href="<?php echo ROOT."opening_detail_list/".$row_p['process_id']."/2";?>"  class="link_dash"><?php echo count_opening_process_qty($dbcon,$row_p['process_id'],'2'); ?></a>
										</th>
									</tr>
								<?php
									$cnt++;
									}
								?>
								
								
							</table>
							
						</div>
					
					</div>
					
				</div>
				<?php }   ?>
			<!-- Outward Pending Section End -->		
				
			</div>
			
		
	
	</div>

</section>

<script type="text/javascript">
function get_value()
{
 Loading(true);	

$('#title_chart').html('');
$('#year_graph1').val($('#c_year').val());
$('#year_graph2').val($('#c_year').val());
$('#chart-3').html('');
load_value();
load_graph(); 
load_graph_emp(); 
//load_excisepichart();
 
 Unloading();
}
$(document).ready(function() {
    Loading(true);	
    load_value();
    load_employee();
    Unloading();
});

function reload_data(){
    load_value();
    crm_task_data_load();
    crm_task_data_load1();
}
function load_fivecust()
{
	var c_year=$('#c_year').val();
        $.ajax({
            type: "POST",
            url: root_domain+'app/dashboard/',
            data: { mode : "getcust", c_year : c_year},
            success: function(response){
                $('#top_5_cust').html(response);
            }
	});
}  

function load_value()
{
    var user_id=$("#user_id").val();
    $.ajax({
	type: "POST",
	url: root_domain+'app/employee_dashboard/',
	data: { mode : "getyear", user_id:user_id},
	success: function(response){
		//console.log(response);
		
		var data = JSON.parse(response);
		//alert(data.purchse_order_pending);
		$('#bussiness').html(data.cdone);
		$('#bussiness_registered').html(data.c_register);
		$('#turnover').html(data.cndone);
		$('#all_comp_cnt').html(data.all_comp_cnt);
		$('#bussiness_assign').html(data.cassign);
		$('#bussiness_unassign').html(data.unassign);
		$('#bussiness_e_start').html(data.emp_start);
		$('#bussiness_e_notstart').html(data.cassign);
		$('#exp_approval').html(data.expense);
		$('#new_spare').html(data.new_spare);
		$('#old_spare').html(data.old_spare);
		$('#pending_job_card').html(data.pending_job_card);
		$('#pending_job_card_new').html(data.pending_job_card_new);
		
		$('#purchse_order_pending').html(data.purchse_order_pending);
		$('#purchse_overdue_pending').html(data.po_overdue_pending);
		$('#debit_note_pending').html(data.debit_note_pending);
		$('#total_inward_pending').html(data.total_inward_pending);
		
		
		$('#purchase_qc_pending').html(data.po_qc_pending);
		$('#parts_qc_pending').html(data.parts_qc_pending);
		$('#finish_qc_pending').html(data.fp_pending);
		$('#pending_debit_note').html(data.pending_debit_note);
		$('#purchase_bill_pending').html(data.purchase_bill_pending);
		
	}
	});
	Unloading();
}  



function load_employee()
{
  $.ajax({
	type: "POST",
	url: root_domain+'app/employee_dashboard/',
	data: { mode : "getemployee"},
	success: function(response){
		//console.log(response);
		//alert(response);
		var data = JSON.parse(response);
		$('#e_present').html(data.present);
		$('#e_absent').html(data.absent);
	}
	});
	Unloading();
} 
function crm_task_data_load(){
	var user_id = $("#user_id").val();
	$.ajax({
            type: "POST",
            url: root_domain+'app/employee_dashboard/',
            data: { mode : "crm_dashbord_data_load",user_id:user_id},
            success: function(response){
                    //var data = JSON.parse(response);
                    $('#crm_table_data').html(response);
                    /*$(".select2").select2({
                        width: '100%'
                    });*/
            }
	});
}
function crm_task_data_load1(){
	var user_id=$("#user_id").val();
	$.ajax({
	type: "POST",
	url: root_domain+'app/employee_dashboard/',
	data: { mode : "crm_dashbord_data_load1",user_id:user_id},
	success: function(response){
		//console.log(response);
		//var data = JSON.parse(response);
		$('#crm_table_data1').html(response);
		//$('#e_absent').html(data.absent);
	}
	});
}
/*
function load_graph()
{
	$('#chart-3').html('');
	Loading(true);	
	//var c_year=$('#c_year').val();
	var status_graph1=$('#status_graph1').val();
	var year_graph1=$('#year_graph1').val();
	var mainurl = root_domain+'app/dashboard/index.php?mode=dynamic_chart&status='+status_graph1+'&year_graph1='+year_graph1;
	//alert(mainurl);
	$.getJSON(mainurl, function(json) {
	var arr=new Array();
		for(var i=0;i<12;i++)
		{	
			arr[i]=json[i];	
		}
		Morris.Bar({
        element: 'chart-3',
        data: arr,
		barSizeRatio:0.55,
        xkey: 'device',
        ykeys: ['geekbench'],
        labels: ['complaint'],
        barRatio: 0.4,
        xLabelAngle: 35,
        hideHover: 'auto',
        barColors: ['#6883a3'],
		lineWidth:25
      });
	});
Unloading();
}



function load_graph_emp()
{
	$('#chart-4').html('');
	Loading(true);	
	//var c_year=$('#c_year').val();
	var status_graph2=$('#status_graph2').val();
	var year_graph2=$('#year_graph2').val();
	var mainurl = root_domain+'app/dashboard/index.php?mode=dynamic_chart_emp&status='+status_graph2+'&year_graph2='+year_graph2;
	//alert(mainurl);
	$.getJSON(mainurl, function(json1) {
		count_loop=json1.count;
	var arr1=new Array();
		for(var j=0;j<count_loop;j++)
		{	
			arr1[j]=json1[j];	
		}
		Morris.Bar({
        element: 'chart-4',
        data: arr1,
		barSizeRatio:0.1,
        xkey: 'device',
        ykeys: ['geekbench'],
        labels: ['complaint'],
        barRatio: 0.1,
        xLabelAngle: 35,
        hideHover: 'auto',
        barColors: ['#6883a3'],
		lineWidth:25
      });
	});
Unloading();
}

*/
 </script>
 