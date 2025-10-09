<link href="assets/morris.js-0.4.3/morris.css" rel="stylesheet" />

<?php include("dashboard_common_functions.php"); ?>
<?php include("common_functions/common_production_functions.php"); ?>
<?php include("common_functions/common_production_store_wise_function.php"); ?>
<style type="text/css">
	.head-text {
		font-family: "Segoe UI",Arial,sans-serif;
		font-weight: 400;
		text-align: center;
		margin: 10px 0;
		font-size: 25px;
		box-sizing: inherit;
		margin-block-start: -0.5em;
		margin-block-end: 0.0em;
		margin-inline-start: 8px;
		margin-inline-end: 0px;
		color: #fff!important;
		border-radius: 4px;
		position: relative;
		background-color: #337ab7!important;
	}
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
<?php
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	WD_TEAM_PENDING_TASK_SLUG_READ,
	WD_PENDING_TASK_SLUG_READ,
	WD_COMPALINT_SLUG_READ,
	WD_EMPLOYEE_SLUG_READ,
	WD_MRP_SLUG_READ,
	/* START JAYESH*/ WD_DESIGN_DEPARTMENT_SLUG_READ,/* END JAYESH*/
	WD_SPARE_PARTS_SLUG_READ,
	WD_PENDING_JOB_CARD_SLUG_READ,
	WD_PURCHASE_SLUG_READ,
	WD_QC_PENDING_SLUG_READ,
	WD_USER_INQUIRY_SLUG_READ,
	WD_INHOUSE_PENDING_PROCESS_SLUG_READ,
	WD_OUTSIDE_PENDING_PROCESS_SLUG_READ,
	WD_VENDOR_UNADJUSTED_AMOUNT,
	WD_CUSTOMER_UNADJUSTED_AMOUNT,
	CRM_SLUG_VIEW,
	SCHEDULING_SLUG_VIEW,
	MRP_SLUG_VIEW,
	PURCHASE_SLUG_VIEW,
	PRODUCTION_SLUG_VIEW,
	RESOURCE_SLUG_VIEW,
	INVENTORY_SLUG_VIEW,
	QC_SLUG_VIEW,
	SERVICE_SLUG_VIEW,
	FINANCE_SLUG_VIEW,
	HRMS_SLUG_VIEW,
	DESIGN_DEPARTMENT_SLUG_VIEW,
	MAINTENANCE_SLUG_VIEW,
	DISTRIBUTOR_PORTAL_SLUG_VIEW,
	VENDOR_PORTAL_SLUG_VIEW,
	SUPPORT_TICKET_SLUG_VIEW,
	DASHBOARD_PENDING_TASK_LIST_INQUIRY_ADD,
	DASHBOARD_PENDING_TASK_LIST,
	DASHBOARD_PENDING_TASK_LIST_GENERAL,
	DASHBOARD_PENDING_TASK_LIST_QUOTATION,
	DASHBOARD_PENDING_TASK_LIST_REVISE_QUOTATION,
	DASHBOARD_PENDING_TASK_LIST_QUOTATION_LIST,
	DASHBOARD_PENDING_TASK_LIST_QUOTATION_FOLLOWUP,
	DASHBOARD_PENDING_TASK_LIST_SALES_ORDER_LIST,
	DASHBOARD_PENDING_TASK_LIST_DISPATCH_LIST,
	DASHBOARD_PENDING_TASK_LIST_APPOINTMENT_LIST,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_INQUIRY_ADD,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_GENERAL,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_REVISE,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION_LIST,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION_FOLLOWUP,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_SALES_ORDER_LIST,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_DISPATCH_LIST,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_APPOINTMENT_LIST,
	DASHBOARD_GET_SALES_ORDER_DETAILS,
	DASHBOARD_GET_STOCK_DETAILS,
	DASHBOARD_GET_STOCK_PENDING_REQUEST,
	DASHBOARD_GET_REJECT_QC_REQUEST_LIST,
	DASHBOARD_GET_FORECAST_LIST,
	DASHBOARD_INDENT_LIST,
	DASHBOARD_PO_QUOTATION_LIST,
	DASHBOARD_PO_REQUEST_LIST,
	DASHBOARD_OVERDUE_PO_PRO_LIST,
	/*START JAYESH ADD GIR*/DASHBOARD_PO_GIR_LIST,/*END JAYESH ADD GIR*/
	DASHBOARD_PURCHASE_BILL_PENDING_LIST,
	DASHBOARD_DEBIT_NOTE_PENDING_LIST,
	DASHBOARD_PURCHASE_DISAPPROVED_VIEW,
	DASHBOARD_JOB_CARD_LIST,
	DASHBOARD_PENDING_JOB_WORK_LIST,
	DASHBOARD_PENDING_JOB_CARD,
	DASHBOARD_PURCHASE_QC_PENDING_LIST,
	DASHBOARD_PARTS_QC_PENDING_LIST,
	DASHBOARD_COMPLAIN_TYPE,
	DASHBOARD_COMPLAIN_TYPE_COMPLIANT_ASSIGNED,
	DASHBOARD_COMPLAIN_TYPE_EMPLOYEE_STARTED,
	DASHBOARD_COMPLAIN_TYPE_EMPLOYEE_NOT_STARTED,
	DASHBOARD_COMPLAIN_TYPE_CLOSED,
	DASHBOARD_COMPLAIN_TYPE_NOT_DONE,
	DASHBOARD_COMPLAIN_LIST,
	DASHBOARD_EMPLOYEE_PRESENT_LIST,
	DASHBOARD_EMPLOYEE_ABSENT_LIST,
	DASHBOARD_EMPLOYEE_EXPENSE_PENDING_LIST,
	DASHBOARD_SPARE_LIST_PENDING,
	DASHBOARD_RETURN_OLD_SPARE,
	DASHBOARD_CUSTOMER_UNADJUSTED_AMOUNT,
	DASHBOARD_PENDING_ORDER_INVOICE,
	DASHBOARD_PENDING_SPARE_INVOICE,
	DASHBOARD_PENDING_SERVICE_CHARGE_INVOICE,
	DASHBOARD_PENDING_FOC_SPARE_INVOICE,
	DASHBOARD_PENDING_INVOICE_APPROVAL,
	DASHBOARD_VENDOR_UNADJUSTED_AMOUNT,
	DASHBOARD_PO_REQUEST_LIST_APPROVE,
	/* START JAYESH*/
	WD_DESIGN_DEPARTMENT_SLUG_READ,
	DESIGN_DEPARTMENT_SLUG_VIEW,
	DASHBOARD_DESIGN_DEPARTMENT_GET_SALES_ORDER_DETAILS,
	/* END JAYESH*/
	/* START MAULIK*/
	PURCHASE_ORDER_APPROVAL,
	DASHBOARD_SERVICE_NOTES_LIST,
	PURCHASE_ORDER_FINANCE_APPROVAL,
	DASHBOARD_PO_SHORTCLOSE_APPROVAL,
	DASHBOARD_PO_SHORTCLOSE_DISAPPROVAL,
	WD_DISPATCH_VIEW,
	DASHBOARD_DISPATCH,
	DASHBOARD_DISPATCH_PENDING,
	DASHBOARD_FINAL_DISPATCH,
	/* END MAULIK*/
	DASHBOARD_WORKORDER_SHORTAGE_LIST_SLUG_VIEW


]);
    //p($bulkAccessArray);
?>
<?php 
//  START :: added by Sanat : 20-09-2021
$company_config = getCompanyConfiguration($dbcon);
$is_store_approval = $company_config['store_approval'];
$production_on_dashboard = $company_config['production_on_dashboard'];
$getspecialConfiguration = getspecialConfiguration($dbcon);

//  END :: added by Sanat : 20-09-2021	

?>


<section class="panel">
	<div class="panel-body ">
		<!-- CRM SECTION Start -->
		<?php if(in_array(CRM_SLUG_VIEW,$bulkAccessArray)) { ?>
			<div class="col-md-12">
				<div class="panel panel-primary">
					<div class="panel-heading">CRM</div>
				</div>
			</div>
			<div class="col-md-12">
				<div class="row">
					<?php
					if(in_array(WD_TEAM_PENDING_TASK_SLUG_READ,$bulkAccessArray)) { ?>	
						<!-- Pending follow-ups Section Start -->
						<div class="col-md-4">
							<div class="panel panel-primary">
								<div class="panel-heading">TEAM PENDING TASKS</div>
								<div class="panel-body" id="crm_table_data">
									<table class="table">
										<tr> 
											<th colspan="2">
												<select class="form-control" name="crm_tree_user" id="crm_tree_user" onchange="crm_task_data_load();" >
													<?=get_assign_users($dbcon, $_SESSION['user_id'], " and user_type in(".$company_config['crm_user_type'].")");?>
												</select>
											</th>
										</tr>
										<?php if(in_array(DASHBOARD_PENDING_TASK_LIST_INQUIRY_ADD,$bulkAccessArray)) { ?>

											<?php if($getspecialConfiguration['jainflex_permission'] == '1'){ ?>
													<tr> 
												<th>
													<a href="<?php echo CRM_ROOT.'customer_list';?>">ADD INQUIRY</a>
												</th>
												<th></th>
											</tr>
											<?php } ?>
											
											<tr> 
												<th>
													<a href="<?php echo CRM_ROOT.'inquiry_add';?>">ADD INQUIRY</a>
												</th>
												<th></th>
											</tr>
										<?php } ?>
										<?php 
										$in_array_check = array(DASHBOARD_PENDING_TASK_LIST_GENERAL,DASHBOARD_PENDING_TASK_LIST,DASHBOARD_PENDING_TASK_LIST_QUOTATION,DASHBOARD_PENDING_TASK_LIST_REVISE_QUOTATION,
											DASHBOARD_PENDING_TASK_LIST_QUOTATION_FOLLOWUP);
										$query="select mcd_id,mcd_name from tbl_master_category_detail where mcd_status=0 and mcd_cat_id=10 order by priority ASC";
										$query_rs=$dbcon->query($query);
										$i = 0;
										while($row_p=mysqli_fetch_assoc($query_rs))
										{
											if($row_p['mcd_id'] == GENERAL_TASK_TYPE) { ?>
												<tr>
													<th>
														<a href="<?php echo ROOT.CRM_ROOT.'general_task_list'; ?>"><?php echo $row_p['mcd_name']; ?> </a>
													</th>
													<th><?= count_general_pen_tsk($dbcon, $_SESSION['user_id']);?></th>
												</tr>
											<?php } else {
												if($row_p['mcd_id'] == '21' && in_array(DASHBOARD_PENDING_TASK_LIST_QUOTATION_LIST,$bulkAccessArray)){ ?>
													<tr>
														<th>
															<a  href="<?php echo CRM_ROOT.'quotation_list' ?>">PENDING QUOTATION APPROVAL</a>
														</th>
														<th><?=count_team_pending_quot_approval($dbcon,$_SESSION['user_id']);?></th>
													</tr>
												<?php } ?>
												<?php if(in_array($in_array_check[$i],$bulkAccessArray)) { ?>
													<tr>
														<th>
															<a  href="<?php echo CRM_ROOT.'pending_task_list/'.$row_p['mcd_id'].'/'.$_SESSION['user_id'];?>"><?=$row_p['mcd_name']?></a>
														</th>
														<th><?=count_usr_pen_tsk($dbcon,$row_p['mcd_id'],$_SESSION['user_id']);?></th>
													</tr>
												<?php } ?>
											<?php } ?>
											<?php $i++;
										} ?>
										<?php if(in_array(DASHBOARD_PENDING_TASK_LIST_SALES_ORDER_LIST,$bulkAccessArray)) { ?>
											<tr> 
												<th>
													<a  href="<?php echo ROOT.'pending_sales_order_list';?>">PENDING P.O. UPLOAD</a>
												</th>
												<th><?=count_pend_po_upload($dbcon,$_SESSION['user_id']);?></th>
											</tr>
											<tr> 
												<th>
													<a  href="<?php echo ROOT.CRM_ROOT.'disapprove_sales_order_list';?>">DISAPPROVE SALES ORDER</a>
												</th>
												<th><?=count_dis_so_upload($dbcon,$_SESSION['user_id']);?></th>
											</tr>
											<tr> 
												<th>
													<a  href="<?php echo ROOT.'pending_so_approve_list';?>">PENDING SALES ORDER APPROVE</a>
												</th>
												<th><?=count_pend_so_approve($dbcon,$_SESSION['user_id']);?></th>
											</tr>
											<tr> 
												<th>
													<a  href="<?php echo CRM_ROOT.'order_acceptance_list';?>">PENDING ORDER ACCEPT</a>
												</th>
												<th><?=count_pend_order_accept($dbcon,$_SESSION['user_id']);?></th>
											</tr>
											<tr> 
												<th>
													<a  href="<?php echo CRM_ROOT.'sales_order_stock_allocation';?>">SALES ORDER STOCK ALLOCATION</a>
												</th>
												<th><?=count_so_stock_allocation($dbcon,$_SESSION['user_id']);?></th>
											</tr>
										<?php } ?>
										<?php if(in_array(DASHBOARD_PENDING_TASK_LIST_DISPATCH_LIST,$bulkAccessArray)) { ?>
											<tr> 
												<th>
													<a  href="<?php echo ROOT.'pending_dispatch_list_crm';?>">PENDING DISPATCH</a>
												</th>
												<th><?php //=count_pend_disp($dbcon);?></th>
											</tr>
										<?php } ?>
										<?php if(in_array(DASHBOARD_PENDING_TASK_LIST_APPOINTMENT_LIST,$bulkAccessArray)) { ?>
											<tr> 
												<th>
													<a  href="<?php echo ROOT.'pending_appointment_list';?>">UPCOMING APPOINTMENTS</a>
												</th>
												<th><?=count_pend_appoint($dbcon,$_SESSION['user_id']);?></th>
											</tr>
										<?php } ?>	
									</table>
								</div>
							</div>
						</div>
						<!-- Pending follow-ups Section End -->	
					<?php  }  ?>
					<?php
					if(in_array(WD_PENDING_TASK_SLUG_READ,$bulkAccessArray)){ ?>	
						<!-- Pending follow-ups Section Start -->
						<div class="col-md-4">
							<div class="panel panel-primary">
								<div class="panel-heading">PENDING TASKS</div>
								<div class="panel-body" id="crm_table_data1">
									<table class="table">
										<!-- comment by jayeshbhai -->
										<tr> 
											<th colspan="2"><br></th>
										</tr>
										<?php if(in_array(DASHBOARD_PERSONAL_PENDING_TASK_LIST_INQUIRY_ADD,$bulkAccessArray)){ ?>
											<tr> 
												<th>
													<a href="<?php echo CRM_ROOT.'inquiry_add';?>">ADD INQUIRY</a>
												</th>
												<th></th>
											</tr>
										<?php } ?>
										<?php 
										$personal_in_array_check = array(DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_GENERAL,DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE,DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION,DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_REVISE,
											DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION_FOLLOWUP);
										$query="select mcd_id,mcd_name from tbl_master_category_detail where mcd_status=0 and mcd_cat_id=10 order by priority ASC";
										$query_rs=$dbcon->query($query);
										$k = 0;
										while($row_p=mysqli_fetch_assoc($query_rs))
										{
											if($row_p['mcd_id'] == GENERAL_TASK_TYPE) { ?>
												<tr>
													<th>
														<a  href="<?php echo ROOT.CRM_ROOT.'general_task_list'; ?>"><?php echo $row_p['mcd_name']; ?> </a>
													</th>
													<th><?= count_general_pen_tsk($dbcon, $_SESSION['user_id'], false);?></th>
												</tr>
											<?php } else {
												if($row_p['mcd_id'] == '21' && in_array(DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION_LIST,$bulkAccessArray)){ ?>
													<tr>
														<th>
															<a  href="<?php echo CRM_ROOT.'quotation_list' ?>">PENDING QUOTATION APPROVAL</a>
														</th>
														<th><?=count_user_pending_quot_approval($dbcon,$_SESSION['user_id']);?></th>
													</tr>
												<?php } ?>
												<?php if(in_array($personal_in_array_check[$k],$bulkAccessArray)) { ?>
													<tr>
														<th>
															<a  href="<?php echo CRM_ROOT.'pending_task_list_one/'.$row_p['mcd_id'].'/'.$_SESSION['user_id'];?>"><?=$row_p['mcd_name']?></a>
														</th>
														<th><?=count_usr_pen_tsk1($dbcon,$row_p['mcd_id'],$_SESSION['user_id']);?></th>
													</tr>
												<?php } ?>
											<?php } ?>
											<?php $k++; 
										} ?>
										<?php if(in_array(DASHBOARD_PERSONAL_PENDING_TASK_LIST_SALES_ORDER_LIST,$bulkAccessArray)) { ?>
											<tr> 
												<th>
													<a  href="<?php echo ROOT.'pending_sales_order_list';?>">PENDING P.O. UPLOAD</a>
												</th>
												<th><?=count_pend_po_upload($dbcon,$_SESSION['user_id']);?></th>
											</tr>
											<tr> 
												<th>
													<a  href="<?php echo ROOT.CRM_ROOT.'disapprove_sales_order_list';?>">DISAPPROVE SALES ORDER</a>
												</th>
												<th><?=count_dis_so_upload($dbcon,$_SESSION['user_id']);?></th>
											</tr>
											<tr> 
												<th>
													<a  href="<?php echo ROOT.'pending_so_approve_list';?>">PENDING SALES ORDER APPROVE</a>
												</th>
												<th><?=count_pend_so_approve($dbcon,$_SESSION['user_id']);?></th>
											</tr>
											<tr> 
												<th>
													<a  href="<?php echo CRM_ROOT.'order_acceptance_list';?>">PENDING ORDER ACCEPT</a>
												</th>
												<th><?=count_pend_order_accept($dbcon,$_SESSION['user_id']);?></th>
											</tr>
											<tr> 
												<th>
													<a  href="<?php echo CRM_ROOT.'sales_order_stock_allocation';?>">SALES ORDER STOCK ALLOCATION</a>
												</th>
												<th><?=count_so_stock_allocation($dbcon,$_SESSION['user_id']);?></th>
											</tr>
										<?php } ?>
										<?php if(in_array(DASHBOARD_PERSONAL_PENDING_TASK_LIST_DISPATCH_LIST,$bulkAccessArray)) { ?>	
											<tr> 
												<th>
													<a  href="<?php echo ROOT.'pending_dispatch_list_crm';?>">PENDING DISPATCH</a>
												</th>
												<th><?php //=count_pend_disp($dbcon);?></th>
											</tr>
										<?php } ?>
										<?php if(in_array(DASHBOARD_PERSONAL_PENDING_TASK_LIST_APPOINTMENT_LIST,$bulkAccessArray)) { ?>
											<tr> 
												<th>
													<a  href="<?php echo ROOT.'pending_appointment_list';?>">UPCOMING APPOINTMENTS</a>
												</th>
												<th><?=count_pend_appoint($dbcon,$_SESSION['user_id']);?></th>
											</tr>
										<?php } ?>	
									</table>
								</div>

							</div>

						</div>
						<!-- Pending follow-ups Section End -->	
					<?php  }  ?>
				</div>
			</div>
		<?php } ?>
		<!-- CRM SECTION End -->

		<?php
		$companyConfiguration_dash=getCompanyConfiguration($dbcon);
		$enable_post_crm_dash = $companyConfiguration_dash['enable_post_crm'];

		if($enable_post_crm_dash == 1) { ?>
			<!-- POST_CRM SECTION Start -->
			<div class="col-md-12">
				<div class="panel panel-primary">
					<div class="panel-heading">POST CRM</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="panel panel-primary">
							<div class="panel-heading">Target</div>
							<table class="table">
								<tr>
									<th>
										<a href="<?php echo ROOT.CRM_ROOT.'product_wise_target';?>">Product Wise</a>
									</th>
									<th> <?=count_product_wise_target($dbcon,$_SESSION['user_id']);?>  </th>
								</tr>
								<tr>
									<th>
										<a href="<?php echo ROOT.CRM_ROOT.'value_wise_target';?>">Value Wise</a><br>Current month<br>Outstanding<br>Achieved
									</th>
									<th> <?=count_value_wise_target($dbcon,$_SESSION['user_id']);?><br><?=get_total_current_month_target($dbcon,$_SESSION['user_id']);?><br><?=number_format(get_total_outstanding_target($dbcon,$_SESSION['user_id']),2);?><br><?=get_total_achieved_target($dbcon,$_SESSION['user_id']);?></th>
								</tr>
							</table>
						</div>
					</div>
					<div class="col-md-12">
						<div class="panel panel-primary">
							<?=get_target_total_summery($dbcon,$_SESSION['user_id']);?>
						</div>
					</div>
				</div>
			</div>  
			<!-- POST_CRM SECTION Start --> 
		<?php } ?>   

		<!-- SCHEDULING SECTION Start -->
		<?php if(in_array(SCHEDULING_SLUG_VIEW,$bulkAccessArray)) { ?>
			<div class="col-md-12">
				<div class="panel panel-primary">
					<div class="panel-heading">SCHEDULING</div>
				</div>
				<div class="row"></div>
			</div>
		<?php } ?>
		<!-- SCHEDULING SECTION End -->
		<!-- DESIGN DEPARTMENT Section Start-->	
		<?php

		$design_department_query="SELECT design_department from tbl_company_configuration";
		$design_department_row=mysqli_fetch_assoc($dbcon->query($design_department_query));
		$design_department_flag = $design_department_row['design_department'];
		if($design_department_flag == 1) {
			if(in_array(DESIGN_DEPARTMENT_SLUG_VIEW,$bulkAccessArray)) { ?>
				<div class="col-md-12">
					<div class="panel panel-primary">
						<div class="panel-heading">DESIGN DEPARTMENT</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<!-- Design Department Section Start -->
							<?php if(in_array(WD_DESIGN_DEPARTMENT_SLUG_READ,$bulkAccessArray)){ ?>
								<div class="panel panel-primary">

									<div class="panel-heading">DESIGN DEPARTMENT</div>

									<div class="panel-body">

										<table class="table table-hover design-department">
											<thead>
												<?php if(in_array(DASHBOARD_DESIGN_DEPARTMENT_GET_SALES_ORDER_DETAILS,$bulkAccessArray)) { ?>
													<tr>
														<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT.'design_department_get_sales_order_details'; ?>">Sales Order Wise Bom</a></th>
														<th class="text-center"><?php echo count_so_wise_bom($dbcon); ?></th>
													</tr>
													<tr>
														<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT.'workorder_permission'; ?>">Workorder Permission</a></th> 
														<th class="text-center"><?php echo count_workorder_permission($dbcon); ?></th>
													</tr>
													<tr>
														<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT.'store_order_design_department'; ?>">Store Order Wise Bom</a></th>
														<th class="text-center"><?php echo count_store_order_wise_bom($dbcon); ?></th>
													</tr>

												<?php } ?>

											</thead>

										</table>
									</div>

								</div>
							<?php  }   ?>
							<!-- Out of Stock Section End -->

						</div>
					</div>
				</div>
			<?php } }?>
			<!-- DESIGN DEPARTMENT Section End-->


			<!-- MRP Section Start-->
			<?php if(in_array(MRP_SLUG_VIEW,$bulkAccessArray)) { ?>
				<div class="col-md-12">
					<div class="panel panel-primary">
						<div class="panel-heading">MRP</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<!-- Out of Stock Section Start -->
							<?php if(in_array(WD_MRP_SLUG_READ,$bulkAccessArray)){ ?>
								<div class="panel panel-primary">

									<div class="panel-heading">MRP</div>

									<div class="panel-body">

										<table class="table table-hover personal-task">
											<thead>

												<?php if(in_array(DASHBOARD_GET_SALES_ORDER_DETAILS,$bulkAccessArray)) { ?>
													<tr>
														<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT.'work_order_add'; ?>">Direct Work Order</a></th>
														<th class="text-center"></th>
													</tr>
												<?php } ?>

												<?php if(in_array(DASHBOARD_GET_SALES_ORDER_DETAILS,$bulkAccessArray)) { ?>
													<?php if($company_config['sales_wise_branch_planning'] == '1'){ ?>
														<tr>
															<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT.'get_sales_order_details_branch'; ?>">Sales Order Wise Branch Planning</a></th>
															<th class="text-center"><?php echo count_so_procuct_req_branch($dbcon); ?></th>
														</tr>
													<?php } ?>	
												<?php } ?>

												<?php if(in_array(DASHBOARD_GET_SALES_ORDER_DETAILS,$bulkAccessArray)) { ?>
													<tr>
														<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT.'get_sales_order_details'; ?>">Sales Order Wise Planning</a></th>
														<th class="text-center"><?php echo count_so_procuct_req($dbcon); ?></th>
													</tr>
												<?php } ?>

												<?php if(in_array(DASHBOARD_GET_STOCK_DETAILS,$bulkAccessArray)) { ?>

												<tr>
													<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT.'get_stock_detail/min_max'; ?>">Min-Max Planning</a></th>
													<th class="text-center"><?php echo count_min_max($dbcon,'min_max'); ?></th>
												</tr>
												<tr>
													<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT.'store_order_request/min_max'; ?>">Store Order Wise Planning</a></th>
													<th class="text-center"><?php echo count_store_order_request($dbcon,'min_max'); ?></th>
												</tr>
											<?php } ?>

											<?php /* if(in_array(DASHBOARD_GET_STOCK_PENDING_REQUEST,$bulkAccessArray)) { ?>
												<tr>
													<th class="text-left "><a class="border_line1" href="<?php echo ROOT.'stock_pending_request'; ?>">Requisition By All Department</a></th>
													<th class="text-center"><?php echo count_stock_procuct_req($dbcon); ?></th>
												</tr>
											<?php } */ ?>		
											<?php if(in_array(DASHBOARD_GET_REJECT_QC_REQUEST_LIST,$bulkAccessArray)) { ?>
												<tr>
													<th class="text-left "><a class="border_line1" href="<?php echo ROOT.'reject_qc_request_list'; ?>">Reject Product Planning</a></th>
													<th class="text-center"><?php echo count_reject_procuct_req($dbcon); ?></th>
												</tr>
											<?php } ?>

											<?php if(in_array(DASHBOARD_GET_FORECAST_LIST,$bulkAccessArray)) { ?>
											<!-- 	<tr>
													<th class="text-left "><a class="border_line1" href="<?php //echo ROOT.'updating_module'; ?>">forecast</a></th>
													<th class="text-center"><?php // echo count_so_procuct_req($dbcon); ?></th>
												</tr> -->

											<?php } ?>	

											
													<?php if(in_array(DASHBOARD_GET_STOCK_PENDING_REQUEST,$bulkAccessArray)) { ?>
													<!-- <tr>
														<th class="text-left "><a class="border_line1" href="<?php echo ROOT.'stock_pending_request'; ?>">Requisition By All Department</a></th>
														<th class="text-center"><?php // echo count_stock_procuct_req($dbcon); ?></th>
													</tr> -->
												<?php } ?>

												
												<?php if(in_array(DASHBOARD_GET_FORECAST_LIST,$bulkAccessArray)) { ?>
													<tr>
														<th class="text-left "><a class="border_line1" href="<?php echo ROOT.'updating_module'; ?>">forecast</a></th>
														<th class="text-center"><?php echo count_so_procuct_req($dbcon); ?></th>
													</tr>
												<?php } ?>

												<?php if(in_array(DASHBOARD_WORKORDER_SHORTAGE_LIST_SLUG_VIEW,$bulkAccessArray)) { ?>
													<tr>
														<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT.'workorder_shortage_list'; ?>">Workorder Shortage List</a></th>
														<th class="text-center"><?php echo count_workorder_shortage($dbcon); ?></th>
													</tr>
												<?php } ?>

											<?php //} ?>	

											</thead>

										</table>

									</div>

								</div>
							<?php  }   ?>
							<!-- Out of Stock Section End -->
						</div>
					</div>
				</div>
			<?php } ?>
			<!-- MRP Section End-->
			

			<!-- PURCHASE Section Start-->
			<?php if(in_array(PURCHASE_SLUG_VIEW,$bulkAccessArray)) { ?>
				<div class="col-md-12">
					<div class="panel panel-primary">
						<div class="panel-heading">PURCHASE</div>
					</div>
					<div class="row">
						<?php if(in_array(WD_PURCHASE_SLUG_READ,$bulkAccessArray)){ ?>
							<div class="col-md-4">
								<div class="panel panel-primary">

									<div class="panel-heading">Purchase</div>

									<div class="panel-body">

										<table class="table">
											<?php if(in_array(DASHBOARD_INDENT_LIST,$bulkAccessArray)) { ?>
												<tr>
													<th><a href="<?php echo ROOT.PURCHASE_ROOT.'indent_list'; ?>">Pending Indent</a></th>
													<td><?php echo pending_indent_count($dbcon); ?></td>
												</tr>
											<?php } ?>
											<?php if(in_array(DASHBOARD_PO_QUOTATION_LIST,$bulkAccessArray)) { ?>
												<tr>
													<th><a href="<?php echo ROOT.PURCHASE_ROOT.'po_quotation_list'; ?>">Purchase Quotation List</a></th>
													<td><span id="purchse_quotation_list"></span></td>
												</tr>
											<?php } ?>
											<?php if(in_array(DASHBOARD_PO_REQUEST_LIST,$bulkAccessArray)) { ?>
												<tr>
													<th><a href="<?=ROOT.PURCHASE_ROOT.'po_req_list'?>">Purchase Order Pending</a></th>
													<td><span id="purchse_order_pending"></span></td>
												</tr>
											<?php } ?>
											<?php if(in_array(PURCHASE_ORDER_APPROVAL,$bulkAccessArray)) { ?>
												<tr>
													<th><a href="<?=ROOT.PURCHASE_ROOT.'po_approve_pending_list'?>">Purchase Order Pending Approval</a></th>
													<td><span id="purchse_order_pending_approval"></span></td>
												</tr>
											<?php } ?>
											<?php if(in_array(PURCHASE_ORDER_FINANCE_APPROVAL,$bulkAccessArray)) { ?>
												<tr>
													<th><a href="<?=ROOT.PURCHASE_ROOT.'po_aprooval_finance'?>">Purchase Order Finance Approval</a></th>
													<td><span id="po_aprooval_finance"></span></td>
												</tr>
											<?php } ?>
											<?php if(in_array(DASHBOARD_DEBIT_NOTE_PENDING_LIST,$bulkAccessArray)) { ?>
												<tr>
													<th><a href="<?=ROOT.PURCHASE_ROOT.'po_dispproved_list'?>">Purchase Order Disapproved</a></th>
													<td><span id="po_disapproved"></span></td>
												</tr>
												<?php }?>
												<?php if(in_array(DASHBOARD_PO_SHORTCLOSE_APPROVAL,$bulkAccessArray)) { ?>
													<tr>
														<th><a href="<?=ROOT.PURCHASE_ROOT.'po_shortclose_approval_list'?>">PO Shortclose Approval Pending</a></th>
														<td><span id="po_shortclose_approval"></span></td>
													</tr>
													<?php }?>

													<?php if(in_array(DASHBOARD_PO_SHORTCLOSE_DISAPPROVAL,$bulkAccessArray)) { ?>
														<tr>
															<th><a href="<?=ROOT.PURCHASE_ROOT.'po_shortclose_disapproval_list'?>">PO Shortclose Disapproval</a></th>
															<td><span id="po_shortclose_disapproval"></span></td>
														</tr>
														<?php }?>
														<?php /* START JAYESH FOR GIR */ if(in_array(DASHBOARD_PO_GIR_LIST,$bulkAccessArray)) { ?>
															<tr>
																<th><a href="<?=ROOT.'gir_list'?>">Gate Inward Receipt</a></th>
																<td><span id="purchase_gate_inward_receipt"></span></td>
															</tr>
														<?php }  /* END JAYESH FOR GIR */ ?>

														<?php if(in_array(DASHBOARD_SERVICE_NOTES_LIST,$bulkAccessArray)) {?>
															<tr>
																<th><a href="<?=ROOT.PURCHASE_ROOT.'service_notes_pro_list'?>">Service Notes</a></th>
																<td><span id="service_notes"></span></td>
															</tr>

															<?php }?>
															<?php if(in_array(DASHBOARD_OVERDUE_PO_PRO_LIST,$bulkAccessArray)) { ?>
																<tr>
																	<th><a href="<?=ROOT.INVENTORY_ROOT.'overdue_po_pro_list'?>">Pending Inward</a></th>
																	<td><span id="purchse_overdue_pending"></span></td>
																</tr>

																<tr>
																	<th><a href="<?=ROOT.PURCHASE_ROOT.'over_due_inward'?>">Overdue Purchase Inward</a></th>
																	<td><span id="over_due_inward"></span></td>
																</tr>

																<tr>
																	<th><a href="<?=ROOT.PURCHASE_ROOT.'today_inward'?>">Today Inward</a></th>
																	<td><span id="today_inward"></span></td>
																</tr>

																<?php }?>
																<?php if(in_array(DASHBOARD_PURCHASE_BILL_PENDING_LIST,$bulkAccessArray)) { ?>
																	<tr>
																		<th><a href="<?=ROOT.PURCHASE_ROOT.'purchase_bill_pending_list'?>">Goods Purchase Bill Pending</a></th>
																		<td><span id="purchase_bill_pending"></span></td>
																	</tr>
																<?php } ?>
																<tr>
																	<th><a href="<?=ROOT.PURCHASE_ROOT.'services_purchase_bill_pending_list'?>">Services Purchase Bill Pending</a></th>
																	<td><span id="services_purchase_bill_pending"></span></td>
																</tr>

																<tr>
																	<th><a href="<?=ROOT.PURCHASE_ROOT.'jobwork_purchase_bill_pending_list'?>">Job Work Purchase Bill Pending</a></th>
																	<td><span id="jobwork_purchase_bill_pending"></span></td>
																</tr>
																<?php if(in_array(DASHBOARD_DEBIT_NOTE_PENDING_LIST,$bulkAccessArray)) { ?>
																	<tr>
																		<th><a href="<?=ROOT.PURCHASE_ROOT.'debit_note_pending_list'?>">Pending Debit Note</a></th>
																		<td><span id="debit_note_pending"></span></td>
																	</tr>
																<?php } ?>

								<!--<tr>
									<th><a href="#">Total Inward Pending</a></th>
									<td><span id="total_inward_pending"></span></td>
								</tr>-->
								

							</table>
							
						</div>

					</div>
				</div>
			<?php  }  ?>
			<div class="col-md-4">
				<div class="panel panel-primary">
					<div class="panel-heading">Purchase Follow-Up</div>
					<div class="panel-body">
						<table class="table">
							<tr>
								<th><a href="<?=ROOT.PURCHASE_ROOT.'po_inward_follow_up'?>">PO Inward Follow-up</a></th>
								<th><span id="inward_followup"></span></th>
							</tr>
						</table>
					</div>
				</div>
			</div>


			<?php if(in_array(WD_USER_INQUIRY_SLUG_READ,$bulkAccessArray)){ ?>	
				<!-- Disapprove Purchase -->


				<!-- End -->
				<!-- Regeional Manager Section Start -->
			<!-- <div class="col-md-4">
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
				
			</div> -->
			<!-- Regeional Manager Section End -->	
		<?php  }  ?>
	</div>
</div>
<?php } ?>
<!-- PURCHASE Section End-->


<!-- Production Section Start-->
<?php if(in_array(PRODUCTION_SLUG_VIEW,$bulkAccessArray)) { ?>
	<div class="col-md-12">
		<div class="panel panel-primary">
			<div class="panel-heading">PRODUCTION</div>
		</div>
		<div class="row">
			<!-- Pending Jobcard Section Start -->

			<?php if(in_array(WD_PENDING_JOB_CARD_SLUG_READ,$bulkAccessArray)){ ?>
				<div class="col-md-4">
					<div class="panel panel-primary">

						<div class="panel-heading">PENDING JOB CARD</div>
						
						<div class="panel-body">
							
							<table class="table table-hover personal-task">
								<thead>
									<?php if(in_array(DASHBOARD_JOB_CARD_LIST,$bulkAccessArray)) { ?>
										<tr>
											<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT."job_card_list";?>">Job Card</a></th>
											<th><span id="pending_job_card_new"></span></th>
										</tr>
									<?php } ?>
									<?php if(in_array(DASHBOARD_PENDING_JOB_WORK_LIST,$bulkAccessArray)) { ?>
										<tr>
											<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT."pending_job_work_list_new";?>">Pending Job Work</a></th>
											<th><span id="pending_job_work_count"></span>
												<?php //=production_start_count_using_p_id($dbcon,"1"); ?>
											</th>
										</tr>
									<?php } ?>
									<?php if($is_store_approval == '1'){ ?>
										<tr>
											<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT."request_jobwork_material";?>">Request Jobwork Material</a></th>
											<th><span id="request_jobwork_count">0</span>
												
											</th>
										</tr>
									<?php } ?>
									<tr>
										<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT."pending_jobowork_chalan_list";?>">Create Job Work Chalan</a></th>
										<th><span id="pending_jobowork_chalan_count">0</span>

										</th>
									</tr>

									<?php if(in_array(DASHBOARD_PENDING_JOB_CARD,$bulkAccessArray)) { ?>
										<tr>
											<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT."pending_job_work";?>">Pending Job Work GRN</a></th>
											<th><span id="pending_job_card"></span></th>
										</tr>
									<?php } ?>
								</thead>

							</table>
							
						</div>

					</div>
				</div>
			<?php }   ?>	
			<!-- Pending Jobcard Section End -->

			
			<!-- Jobwork reprocess Section Start -->
			<?php //if(in_array(WD_PENDING_JOB_CARD_SLUG_READ,$bulkAccessArray)){ ?>
				<div class="col-md-4">
					<div class="panel panel-primary">

						<div class="panel-heading">JOBWORK REPROCESS</div>
						
						<div class="panel-body">
							
							<table class="table table-hover personal-task">
								<thead>
									<?php // if(in_array(DASHBOARD_JOB_CARD_LIST,$bulkAccessArray)) { ?>
										<tr>
											<!-- <th class="text-left "><a class="border_line1" href="<?php //echo ROOT.PRODUCTION_ROOT."job_card_list";?>">Job Card</a></th>
											<th><span id="pending_job_card_new"></span></th>
										</tr> -->
									<?php //} ?>
									<?php // if(in_array(DASHBOARD_PENDING_JOB_WORK_LIST,$bulkAccessArray)) { ?>
										<tr>
											<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT."reprocess_pending_jobwork_list";?>">Reprocess Pending Jobwork</a></th>
											<th><span id="reprocess_pending_jobwork_count"><?=count_reprocess_jobwork($dbcon); ?></span>
												
											</th>
										</tr>
									<?php // } ?>
									<tr>
										<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT."pending_reprocess_jobowork_chalan_list";?>">Reprocess Create Jobwork Chalan</a></th>
										<th><span id="reprocess_pending_jobowork_chalan_count">0</span>

										</th>
									</tr>

									<?php //if(in_array(DASHBOARD_PENDING_JOB_CARD,$bulkAccessArray)) { ?>
										<tr>
											<th class="text-left "><a class="border_line1" href="<?php echo ROOT.PRODUCTION_ROOT."reprocess_pending_jobwork";?>">Reprocess Pending jobWork GRN</a></th>
											<th><span id="reprocess_pending_jobwork_grn"></span></th>
										</tr>
									<?php //} ?>
								</thead>

							</table>
							
						</div>

					</div>
				</div>
			<?php // } ?>	
			<!-- Jobwork reprocess Section End -->
			<!-- Inhouse Pending Section Start -->
			<?php if(in_array(WD_INHOUSE_PENDING_PROCESS_SLUG_READ,$bulkAccessArray)){ ?>
				<div class="col-md-12">
					<div class="panel panel-primary">

						<div class="panel-heading">Inhouse Pending Process</div>
						<?php 

						if($production_on_dashboard == '0'){ ?>
							<div class="panel-body" style="overflow:auto;">
								<section class="panel">
							<div class="panel-body">
								<ul class="sub ulpad0">
									<?php if($company_config['batch_wise_stock'] == '1' && $company_config['batch_process'] == '0') { ?>
									<div class="col-sm-6 col-md-4" style="padding-top:10px;font-size:20px;">
										
											<a style="color: white;" class="two btn btn-shadow btn-primary btn-lg btn-block btn-align" href="<?php echo ROOT.PRODUCTION_ROOT."process_counter_detail_list/create_batch";?>">Create Batch</a>
										
									</div>
									<?php } ?> 
									<?php if($is_store_approval == '1'){ ?>
									<div class="col-sm-6 col-md-4" style="padding-top:10px;font-size:20px;">
										
											<a style="color: white;" class="two btn btn-shadow btn-primary btn-lg btn-block btn-align" href="<?php echo ROOT.PRODUCTION_ROOT."process_counter_detail_list/store_request";?>">Store Request Pending</a>
										
									</div>
									<?php } ?>
									<div class="col-sm-6 col-md-4" style="padding-top:10px;font-size:20px;">
										
											<a style="color: white;" class="two btn btn-shadow btn-primary btn-lg btn-block btn-align" href="<?php echo ROOT.PRODUCTION_ROOT."process_counter_detail_list/pending_start";?>">Pending Start</a>
										
									</div>
									<div class="col-sm-6 col-md-4" style="padding-top:10px;font-size:20px;">
										
											<a style="color: white;" class="two btn btn-shadow btn-primary btn-lg btn-block btn-align" href="<?php echo ROOT.PRODUCTION_ROOT."process_counter_detail_list/pending_stop";?>">Pending Stop</a>
										
									</div>
									<div class="col-sm-6 col-md-4" style="padding-top:10px;font-size:20px;">
										
											<a style="color: white;" class="two btn btn-shadow btn-primary btn-lg btn-block btn-align" href="<?php echo ROOT.PRODUCTION_ROOT."process_counter_detail_list/reprocess_start";?>">Reprocess Start</a>
										
									</div>
									<div class="col-sm-6 col-md-4" style="padding-top:10px;font-size:20px;">
										
											<a style="color: white;" class="two btn btn-shadow btn-primary btn-lg btn-block btn-align" href="<?php echo ROOT.PRODUCTION_ROOT."process_counter_detail_list/reprocess_stop";?>">Reprocess End</a>
										
									</div> 
								</ul>
							</div>
							</section>
						</div>
						<?php } else { ?>
						<div class="panel-body" style="overflow:auto;">
							
							<table class="table" style="text-align:center">
								
								<tr>
									<th>#</th>
									<th style="white-space:nowrap;">Process Name</th>
									<th style="white-space:nowrap;">Total Pending</th>
									<?php if($company_config['batch_wise_stock'] == '1' && $company_config['batch_process'] == '0') { ?>
										<th style="white-space:nowrap;">Create Batch </th>
									<?php } ?> 
									<!--   START ::  Added by Sanat :: 20-09-2021 -->
									<?php if($is_store_approval == '1'){ ?>
										<th style="white-space:nowrap;">Store Request Pending</th>
										<!-- <th style="white-space:nowrap;">Store Release Pending</th> -->
									<?php } ?> 
									<!--   END ::  Added by Sanat :: 20-09-2021 -->
									<th style="white-space:nowrap;">Pending Start</th>
									<th style="white-space:nowrap;">Pending Stop</th>
									<!-- <th style="white-space:nowrap;">Reprocess Qty</th> -->
									<th style="white-space:nowrap;">Reprocess Start</th>
									<th style="white-space:nowrap;">Reprocess End</th>
									<!--<th>Opening Qty</th>-->
								</tr>
								
								<?php
								$process_array = $bulkcheck =  [];
								$tr = 0; 
								$cnt=1;
								$sel_p1=$dbcon->query("select process_id,process_name from process_mst where process_status='0' and company_id = ".$_SESSION['company_id']." order by dashbord_priority ");
								while($row_p1=mysqli_fetch_assoc($sel_p1))
								{
									$process_array[] = 'dashboard-inhouse-'.str_replace(' ', '-', strtolower($row_p1['process_name'])); 
								}
								$bulkcheck = canCheckPermissionAccess($dbcon, $process_array);
								$sel_p=$dbcon->query("select process_id,process_name from process_mst where process_status='0' and company_id = ".$_SESSION['company_id']." 
									order by dashbord_priority ");
								while($row_p=mysqli_fetch_assoc($sel_p))
								{

									?>
									<?php if(in_array($process_array[$tr],$bulkcheck)) { ?>
										<tr>
											<th><?php echo $cnt; ?></th>
											<th><?php echo $row_p['process_name']; ?></th>
											
											<th>
												<a href="<?php echo ROOT.PRODUCTION_ROOT."process_detail_list/".$row_p['process_id']."/1";?>" class="link_dash"><?php echo count_process_qty($dbcon,$row_p['process_id'],'1'); ?></a>
											</th>
											<?php if($company_config['batch_wise_stock'] == '1'  && $company_config['batch_process'] == '0') { ?>
												<th>
													<a href="<?php echo ROOT.PRODUCTION_ROOT."batch_create_list/".$row_p['process_id']."/1";?>" class="link_dash"><?=batch_store_request_pending_count_store_wise($dbcon,$row_p['process_id'],1,1,1);?></a>
												</th>
											<?php } ?>
											<!--   START ::  Added by Sanat :: 20-09-2021 -->
											<?php if($is_store_approval == '1'){ ?>
												<th>
													<a href="<?php echo ROOT.PRODUCTION_ROOT."store_request_detail_list/".$row_p['process_id']."/1";?>" class="link_dash"><?=store_request_pending_count_store_wise($dbcon,$row_p['process_id'],1,1,1);?></a>
												</th>
											<?php /*<th>
												<?php 
													$total_req_qty = store_request_approval_pending_count($dbcon,$row_p['process_id'],1,1);
													$total_release_qty = store_release_count($dbcon,$row_p['process_id'],1,1);

												?>
												<a href="<?php echo ROOT.PRODUCTION_ROOT."store_release_pending_list/".$row_p['process_id']?>" class="link_dash"><?=($total_req_qty - $total_release_qty);?></a>
												</th> */?>

												<th> <!--  show allocate qty -->
													<a href="<?php echo ROOT.PRODUCTION_ROOT."working_store_process_details_list/".$row_p['process_id']."/1";?>" class="link_dash"><?=process_wise_store_production_start_count_new($dbcon,$row_p['process_id'],1,1,1);?></a>

												</th>
												<th> <!--  show allocate qty -->
													<a href="<?php echo ROOT.PRODUCTION_ROOT."working_store_process_details_list/".$row_p['process_id']."/2";?>" class="link_dash"><?=process_wise_store_production_count($dbcon,$row_p['process_id'],1,2,1);?></a>

												</th>
											<?php }else{ ?>
												<th> 
													<a href="<?php echo ROOT.PRODUCTION_ROOT."working_process_detail_list/".$row_p['process_id']."/1";?>" class="link_dash"><?=process_wise_production_count($dbcon,$row_p['process_id'],1,1,0);?></a>

												</th>

													<th>
													<a href="<?php echo ROOT.PRODUCTION_ROOT."working_process_detail_list/".$row_p['process_id']."/2";?>" class="link_dash"><?=process_wise_production_count($dbcon,$row_p['process_id'],1,2);?></a>

												</th>
											<?php 	} ?>
											<!--   END ::  Added by Sanat :: 20-09-2021 -->


											<?php /*  <!-- Hide by Sanat :: 20-09-2021  -->
											<th> 
												<a href="<?php echo ROOT."working_process_detail_list/".$row_p['process_id']."/1";?>" class="link_dash"><?=process_wise_production_count($dbcon,$row_p['process_id'],1,1);?></a>
												
												</th> */ ?>
											

												<!-- <th><a href="<?php echo ROOT.PRODUCTION_ROOT."reprocess_detail_list/".$row_p['process_id']."/1";?>"  class="link_dash"><?php echo count_re_process_qty($dbcon,$row_p['process_id'],'1'); ?></a></th> -->

												<th><a href="<?php echo ROOT.PRODUCTION_ROOT."working_reprocess_detail_list/".$row_p['process_id']."/1";?>"  class="link_dash"><?php echo count_re_process_start_qty($dbcon,$row_p['process_id'],'1'); ?></a></th>

												<th><a href="<?php echo ROOT.PRODUCTION_ROOT."working_reprocess_detail_list/".$row_p['process_id']."/2";?>"  class="link_dash"><?php echo count_re_process_end_qty($dbcon,$row_p['process_id'],'1'); ?></a></th>

											<!--<th>
												<a href="<?php echo ROOT.PRODUCTION_ROOT."opening_detail_list/".$row_p['process_id']."/1";?>"  class="link_dash"><?php //echo count_opening_process_qty($dbcon,$row_p['process_id'],'1'); ?></a>
												
											</th>-->
										</tr>
									<?php $cnt++;
									 } ?>
									<?php
									$tr++;
									
								}
								?>
								
								
							</table>
							
						</div>
						<?php 	} ?>
					</div>
					
				</div>
			<?php }   ?>
			<!-- Inhouse Pending Section End -->
			<!-- Outward Pending Section Start -->
			<?php if(in_array(WD_OUTSIDE_PENDING_PROCESS_SLUG_READ,$bulkAccessArray)){ ?>
				<div class="col-md-6">
					<div class="panel panel-primary">

						<div class="panel-heading">Outward Pending Process</div>
						
						<div class="panel-body" style="overflow:auto;">
							
							<table class="table">
								
								<tr>
									<th>#</th>
									<th style="white-space:nowrap;">Process Name</th>
									<th style="white-space:nowrap;">Total Pending</th>
									<th style="white-space:nowrap;">Working Qty</th>
									<th style="white-space:nowrap;">Reprocess Qty</th>
									<!--<th>Opening Qty</th>-->
								</tr>
								
								<?php 
								$cnt=1;
								$sel_p=$dbcon->query("select process_id,process_name from process_mst where process_status='0' AND company_id = ".$_SESSION['company_id']." order by process_name ");
								while($row_p=mysqli_fetch_assoc($sel_p))
								{
									?>
									<tr > 
										<th><?php echo $cnt; ?></th>
										<th><?php echo $row_p['process_name']; ?></th>
										
										<th><a href="<?php echo ROOT.PRODUCTION_ROOT."process_detail_list/".$row_p['process_id']."/2";?>"  class="link_dash"><?php echo count_process_qty($dbcon,$row_p['process_id'],'2'); ?></a></th>
										
										<th>
											<a href="<?php echo ROOT.PRODUCTION_ROOT."working_process_detail_list/".$row_p['process_id']."/2";?>" class="link_dash"><?php echo count_working_process_qty($dbcon,$row_p['process_id'],'2'); ?></a>
										</th>
										
										<th><a href="<?php echo ROOT.PRODUCTION_ROOT."reprocess_detail_list/".$row_p['process_id']."/2";?>"  class="link_dash"><?php echo count_re_process_qty($dbcon,$row_p['process_id'],'2'); ?></a></th>
										
										<!--<th>
											<a href="<?php echo ROOT.PRODUCTION_ROOT."opening_detail_list/".$row_p['process_id']."/2";?>"  class="link_dash"><?php //echo count_opening_process_qty($dbcon,$row_p['process_id'],'2'); ?></a>
										</th>-->
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
<?php } ?>
<!-- Production Section End-->



<!-- RESOURCE Section Start-->
<?php if(in_array(RESOURCE_SLUG_VIEW,$bulkAccessArray)) { ?>
	<div class="col-md-12">
		<div class="panel panel-primary">
			<div class="panel-heading">RESOURCE</div>
		</div>
		<?php if($company_config['resource_display'] == 0) { ?>

			<div class="panel-body" style="overflow:auto;">

				
				<link href="<?= ROOT ?>calendar_css/fullcalendar.css" rel="stylesheet" />
				<link href="<?= ROOT ?>calendar_css/fullcalendar.print.css" rel="stylesheet" media="print" />
				<script src="<?= ROOT ?>calendar_js/moment.min.js"></script>
				<script src="<?= ROOT ?>calendar_js/fullcalendar.js"></script>
				<script type="text/javascript" src="<?= ROOT ?>calendar_js/script.js"></script>
				<style type="text/css">
					.block a:hover{
						color: silver;
					}
					.block a{
						color: #fff;
					}
					.block {
						position: fixed;
						background: #2184cd;
						padding: 20px;
						z-index: 1;
						top: 240px;
					}
				</style>


				<!-- add calander in this div -->
				<div class="row">
					<div class="col-md-12">

						<div class="col-md-4">
							<?php echo getBranchBox($dbcon, $branch_id, '', false, true, 'fetch_resource_based_on_branch();'); ?>
						</div>

						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-4 control-label" style="">Resource Name * </label>
								<div class="col-md-8 col-xs-11">
									<select class="select2"  name="resource_id" id="resource_id" title="Resource Name" onchange="resourceselect(this.value)";>

										<?=get_all_resource($dbcon,$resource_id, '', $branch_id)?>

									</select>
								</div>
							</div>
						</div>
						<div class="col-md-2"></div>
						<?php if(in_array(RESOURCE_REPORT_VIEW,$bulkAccessArray)){ ?> 
							<div class="col-md-2">
								<div class="form-group">
									<button type="submit" class="btn btn-success" id="save" name="save">Generate Report</button>
									<a href="<?=ROOT.'resource_report'?>" type="button" class="btn btn-danger">Cancel</a>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
				<div class="col-md-12">
					<div id="calendar"></div>
				</div>
				<div>&nbsp;</div>
				<div>Assembly Wise work priority</div>

				<div class="col-md-12">
					<div id="calendar1"></div>
				</div>
				
			</div>


			<div id="createEventModal" class="modal fade" role="dialog">
				<div class="modal-dialog">


					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Add Event</h4>
						</div>
						<div class="modal-body">
							<div class="control-group">
								<label class="control-label" for="inputPatient">Event:</label>
								<div class="field desc">
									<input class="form-control" id="title" name="title" placeholder="Event" type="text" value="">
								</div>
							</div>

							<input type="hidden" id="startTime"/>
							<input type="hidden" id="endTime"/>



							<div class="control-group">
								<label class="control-label" for="when">When:</label>
								<div class="controls controls-row" id="when" style="margin-top:5px;">
								</div>
							</div>

						</div>
						<div class="modal-footer">
							<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
							<button type="submit" class="btn btn-primary" id="submitButton">Save</button>
						</div>
					</div>

				</div>
			</div>


			<div id="calendarModal" class="modal fade">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Event Details</h4>
						</div>
						<div id="modalBody" class="modal-body">
							<h4 id="modalTitle" class="modal-title"></h4>
							<div id="modalWhen" style="margin-top:5px;"></div>
						</div>
						<input type="hidden" id="eventID"/>
						<div class="modal-footer">
							<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
							<button type="submit" class="btn btn-danger" id="deleteButton">Delete</button>
						</div>
					</div>
				</div>
			</div>
			<!--Modal-->
		<?php } ?>
	</div>
</div>
<?php } ?>
<!-- RESOURCE Section End-->

<!-- INVENTORY Section Start-->
<?php if(in_array(INVENTORY_SLUG_VIEW,$bulkAccessArray)) { ?>
	<div class="col-md-12">
		<div class="panel panel-primary">
			<div class="panel-heading">INVENTORY</div>
		</div>
		<div class="row">
			<!-- Start :: code by Sanat ::  20-09-2021 -->
			<div class="col-md-4">
				<!-- Inventory Section Start -->
				<?php //if(in_array(WD_DESIGN_DEPARTMENT_SLUG_READ,$bulkAccessArray)){ ?>
					<div class="panel panel-primary">
						<div class="panel-heading">INVENTORY</div>
						<div class="panel-body">
							<table class="table table-hover design-department">
								<thead>
									<?php //if(in_array(DASHBOARD_DESIGN_DEPARTMENT_GET_SALES_ORDER_DETAILS,$bulkAccessArray)) { ?>
										<?php if($is_store_approval == '1') { ?>
											<tr>
												<th class="text-left "><a class="border_line1"  href="<?php echo ROOT.INVENTORY_ROOT."store_release_detail_list" ?>">Production Direct Material Release </a></th>
												<th class="text-center">0</th>
											</tr>
											<tr>
												<th>
													<a class="border_line1" href="<?php echo ROOT.INVENTORY_ROOT."production_direct_material_approval_pending_list" ?>">Production Direct Material Approval </a></th> 
													<th class="text-center"><?php echo count_direct_material_approval_request($dbcon); ?></th>

													
												</tr>
												<tr>
													<th class="text-left "><a class="border_line1" href="<?php echo ROOT.INVENTORY_ROOT."production_request_pending_material_list" ?>">Production Material Release Pending </a></th> 
													<th class="text-center"><?php echo count_store_request($dbcon); ?></th>
												</tr>
												<!-- <tr>
													<th class="text-left "><a class="border_line1" href="<?php //echo ROOT.PRODUCTION_ROOT."production_return_material" ?>">Production Return Material </a></th> 
													<th class="text-center">0</th>
												</tr> -->
												
												<?php } ?>
												<tr>
													<th>
														<a class="border_line1" href="<?php echo ROOT.INVENTORY_ROOT."returnable_pending_grn_list" ?>">Returnable Chalan GRN Pending</a></th> 
														<th class="text-center"><?php echo count_returnable_chalan_grn_pending($dbcon); ?></th>
														

													</tr>
													<tr>
													<th>
														<a class="border_line1" href="<?php echo ROOT.INVENTORY_ROOT."stock_transfer_grn_pending_list" ?>">Stock Transfer GRN Pending</a></th> 
														<th class="text-center"><?php echo count_stock_transfer_grn_pending($dbcon); ?></th>
													</tr>
													<tr>
														<th>
															<a class="border_line1" href="<?php echo ROOT.INVENTORY_ROOT."store_receive_pending_list_new" ?>">Store Receive Approval</a></th> 
															<th class="text-center"><?php echo count_grn_apporve($dbcon); ?></th>


														</tr>
														<tr>
														<th class="text-left "><a class="border_line1" href="<?php echo ROOT.INVENTORY_ROOT.'store_material_request/min_max'; ?>">Store Material Request</a></th>
														<th class="text-center"><?php echo count_store_material_request($dbcon,'min_max'); ?></th>
													</tr>
													<tr>
														<th class="text-left "><a class="border_line1" href="<?php echo ROOT.INVENTORY_ROOT.'production_stock_return_list'; ?>">Production Store Return List</a></th>
														<th class="text-center"><?php echo count_production_stock_return($dbcon); ?></th>
													</tr>
											 <?php /*		<tr>
											  	<th class="text-left "><a class="border_line1"  href="<?php echo ROOT.PRODUCTION_ROOT."store_release_material_list" ?>">Production Release Material History</a></th>
													<th class="text-center"><?php echo count_store_release_material($dbcon); ?></th>

												
													</tr>*/	?>
													<?php //} ?>					
												</thead>
											</table>
										</div>
									</div>
									<?php // }   ?>
									<!--  Inventory Section End -->
								</div>
								<!-- END :: code by Sanat ::  20-09-2021 -->
							</div>
						</div>
					<?php } ?>
					<!-- INVENTORY Section End-->

					<!-- QC Pending Section Start-->
					<?php if(in_array(QC_SLUG_VIEW,$bulkAccessArray)) { ?>
						<div class="col-md-12">
							<div class="panel panel-primary">
								<div class="panel-heading">QC PENDING</div>
							</div>
							<div class="row">
								<?php if(in_array(WD_QC_PENDING_SLUG_READ,$bulkAccessArray)){ ?>
									<div class="col-md-4">
										<div class="panel panel-primary">

											<div class="panel-heading">Purchase QC </div>

											<div class="panel-body">

												<table class="table">
													<?php if(in_array(DASHBOARD_PURCHASE_QC_PENDING_LIST,$bulkAccessArray)) { ?>
														<tr>
															<th><a href="<?=ROOT.PURCHASE_ROOT.'purchase_qc_pending_list'?>">Purchase QC Pending</a></th>
															<td><span id="purchase_qc_pending"></span></td>
														</tr>
													<?php } ?>

												</table>

											</div>

										</div>
									</div>
									<div class="col-md-4">
										<div class="panel panel-primary">

											<div class="panel-heading">Process QC </div>

											<div class="panel-body">

												<table class="table">

													<?php 
													$branch_id_part_qc=$_SESSION['branch_id'];
													$branch_id_part_qc = ($_SESSION['user_type'] == '2' && isset($branch_id_part_qc) && $branch_id_part_qc) ? $branch_id_part_qc : $_SESSION['branch_id'];
													$where_part_qc_db = check_branch('trn', $branch_id_part_qc);
													$part_qc_cou=0;
																		/* $partsqcpending="SELECT trn.process_id,pmst.process_name FROM `tbl_grn_trn` as trn
																		left join process_mst as pmst on pmst.process_id=trn.process_id
																		left join tbl_grn as grn on grn.grn_id=trn.grn_id
																		WHERE grn.grn_status=0 and grn.qc_status=0 and trn.grn_trn_status=0 and trn.product_qc=0 and grn.ref_type='1' and trn.company_id=".$_SESSION['company_id']." ".$where_part_qc_db." group by trn.process_id"; */
																		
																		$partsqcpending="SELECT trn.process_id,trn.process_name FROM `process_mst` as trn
																		WHERE trn.process_status=0 and trn.company_id=".$_SESSION['company_id']." ".$where_part_qc_db."";
																		
																		$result_part_qc=$dbcon->query($partsqcpending);
																		while($parts_qc_row=brp_mysqli_fetch_assoc($result_part_qc)){

																			$part_qc_cou=parts_qc_count_process_wise($dbcon,$parts_qc_row['process_id']);
																			
																			?>
																			
																			<tr>
																				<th><a href="<?=ROOT.PRODUCTION_ROOT.'parts_qc_pending_list'?>/<?=$parts_qc_row['process_id']?>"><?=$parts_qc_row['process_name']?></a></th>
																				<td><span><?=$part_qc_cou?></span></td>
																			</tr>
																			

																		<?php 	}

																		?>
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
                                                        <div class="col-md-4">
                                                        	<div class="panel panel-primary">

                                                        		<div class="panel-heading">Reprocess QC </div>

                                                        		<div class="panel-body">

                                                        			<table class="table">

                                                        				<?php 
                                                        				$branch_id_reproces_qc=$_SESSION['branch_id'];
                                                        				$branch_id_reproces_qc = ($_SESSION['user_type'] == '2' && isset($branch_id_reproces_qc) && $branch_id_reproces_qc) ? $branch_id_reproces_qc : $_SESSION['branch_id'];
                                                        				$where_reproces_qc_db = check_branch('trn', $branch_id_reproces_qc);
                                                        				$reproces_qc_cou=0;


                                                        				$reprocessqcpending="SELECT trn.process_id,trn.process_name FROM `process_mst` as trn
                                                        				WHERE trn.process_status=0 and trn.company_id=".$_SESSION['company_id']." ".$where_reproces_qc_db."";

                                                        				$result_reproces_qc=$dbcon->query($reprocessqcpending);
                                                        				while($reprocess_qc_row=brp_mysqli_fetch_assoc($result_reproces_qc)){

                                                        					$reproces_qc_cou=reprocess_qc_count_process_wise($dbcon,$reprocess_qc_row['process_id']);

                                                        					?>

                                                        					<tr>
                                                        						<th><a href="<?=ROOT.PRODUCTION_ROOT.'reprocess_qc_pending_list'?>/<?=$reprocess_qc_row['process_id']?>"><?=$reprocess_qc_row['process_name']?></a></th>
                                                        						<td><span><?=$reproces_qc_cou?></span></td>
                                                        					</tr>


                                                        				<?php 	}

                                                        				?>

                                                        			</table>

                                                        		</div>

                                                        	</div>
                                                        </div>
                                                    </div>
                                                <?php  }  ?>
                                            </div>
                                        <?php } ?>
                                        <!-- QC Pending Section End-->


                                        <!-- SERVICE Section Start-->
                                        <?php if(in_array(SERVICE_SLUG_VIEW,$bulkAccessArray)) { ?>
                                        	<div class="col-md-12">
                                        		<div class="panel panel-primary">
                                        			<div class="panel-heading">SERVICE</div>
                                        		</div>
                                        		<div class="row"> 
                                        			<!-- Complaint Section Start -->
                                        			<?php if(in_array(WD_COMPALINT_SLUG_READ,$bulkAccessArray)) {?>
                                        				<div class="col-md-4">
                                        					<div class="panel panel-primary">

                                        						<div class="panel-heading">COMPLAINT</div>

                                        						<div class="panel-body">

                                        							<table class="table">

                                        								<?php if($_SESSION['user_type']!='3' && in_array(DASHBOARD_COMPLAIN_TYPE,$bulkAccessArray)){ ?>
                                        									<tr>
                                        										<th><a href="<?php echo ROOT.SERVICE_ROOT.'comp_type/1';?>">New Complaint Registered</a></th>
                                        										<td><span id="bussiness_registered"></span></td>
                                        									</tr>
                                        								<?php } ?>
                                        								<?php if(in_array(DASHBOARD_COMPLAIN_TYPE_COMPLIANT_ASSIGNED,$bulkAccessArray)){ ?>
                                        									<tr>
                                        										<th><a href="<?php echo ROOT.SERVICE_ROOT.'comp_type/2';?>">Complaint Assigned</a></th>
                                        										<td><span id="bussiness_assign"></span></td>
                                        									</tr>
                                        								<?php } ?>
							<!--	<tr>
									<th><a href="<?php echo ROOT.SERVICE_ROOT."complaint_list?type=1";?>">Complaint Unassigned</a></th>
									<td><span id="bussiness_unassign"></span></td>
								</tr> -->
								<?php if(in_array(DASHBOARD_COMPLAIN_TYPE_EMPLOYEE_STARTED,$bulkAccessArray)){ ?>
									<tr>
										<th><a href="<?php echo ROOT.SERVICE_ROOT.'comp_type/7';?>">Employess Started</a></th>
										<td><span id="bussiness_e_start"></span></td>
									</tr>
								<?php } ?>
								<?php if(in_array(DASHBOARD_COMPLAIN_TYPE_EMPLOYEE_NOT_STARTED,$bulkAccessArray)){ ?>
									<tr>
										<th><a  href="<?php echo ROOT.SERVICE_ROOT.'comp_type/2';?>" >Employess Not Started</a></th>
										<td><span id="bussiness_e_notstart"></span></td>
									</tr>
								<?php } ?>
								<?php if(in_array(DASHBOARD_COMPLAIN_TYPE_CLOSED,$bulkAccessArray)){ ?>
									<tr>
										<th> <a href="<?php echo ROOT.SERVICE_ROOT.'comp_type/4';?>">Closed</a></th>
										<td><span id="bussiness"></span></td>
									</tr>
								<?php } ?>
								<?php if(in_array(DASHBOARD_COMPLAIN_TYPE_NOT_DONE,$bulkAccessArray)){ ?>
									<tr>
										<th><a href="<?php echo ROOT.SERVICE_ROOT.'comp_type/5';?>">Not Done</a></th>
										<td><span id="turnover"></span></td>
									</tr>
								<?php } ?>
								<?php if(in_array(DASHBOARD_COMPLAIN_LIST,$bulkAccessArray)){ ?>	
									<tr>
										<th><a href="<?php echo ROOT.SERVICE_ROOT.'complaint_list';?>">Total Complaint</a></th>
										<td><span id="all_comp_cnt"></span></td>
									</tr>
								<?php } ?>
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
								
							</table>
							
						</div>

					</div>
				</div>
			<?php  }   ?>
			<!-- Complaint Section End -->

			<div class="col-md-4">
				<!-- Employee Section Start -->
				<?php if(in_array(WD_EMPLOYEE_SLUG_READ,$bulkAccessArray)){ ?>    
					<div class="panel panel-primary">

						<div class="panel-heading">EMPLOYEE</div>
						
						<div class="panel-body">
							
							<table class="table">
								
								<?php if($_SESSION['user_type']!='3' && in_array(DASHBOARD_EMPLOYEE_PRESENT_LIST,$bulkAccessArray)){ ?>
									<tr>
										<th><a href="<?php echo ROOT."employee_list?type=present";?>">Employee Present</a></th>
										<td><span id="e_present"></span></td>
									</tr>
								<?php } ?>
								
								<?php if($_SESSION['user_type']!='3' && in_array(DASHBOARD_EMPLOYEE_ABSENT_LIST,$bulkAccessArray)){ ?>
									<tr>
										<th><a href="<?php echo ROOT."employee_list?type=absent";?>">Employee Absent</a></th>
										<td><span id="e_absent"></span></td>
									</tr>
								<?php } ?>
								<?php if(in_array(DASHBOARD_EMPLOYEE_EXPENSE_PENDING_LIST,$bulkAccessArray)){ ?>
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
									<?php } ?>
								</table>

							</div>

						</div>
					<?php  }   ?>	
					<!-- Employee Section End -->
				</div>

				<div class="col-md-4">
					<!-- Spare Parts Section Start -->
					<?php if(in_array(WD_SPARE_PARTS_SLUG_READ,$bulkAccessArray)){ ?>  
						<div class="panel panel-primary">

							<div class="panel-heading">SPARE PARTS</div>

							<div class="panel-body">

								<table class="table">

									<?php 
									$usertype=$_SESSION['user_type'];
									if($usertype!='3'){
										?>
										<?php if(in_array(DASHBOARD_SPARE_LIST_PENDING,$bulkAccessArray)){ ?>
											<tr>
												<th><a href="<?php echo ROOT."spare_list_pending";?>" >Spare Part To send</a></th>
												<td><span id="new_spare"></span></td>
											</tr>
										<?php } ?>
										<?php if(in_array(DASHBOARD_RETURN_OLD_SPARE,$bulkAccessArray)){ ?>
											<tr>
												<th><a href="<?php echo ROOT.SERVICE_ROOT."return_old_spare";?>" >Spare Part To Receive</a></th>
												<td><span id="old_spare"></span></td>
											</tr>
										<?php } ?>	
									<?php } else { ?>
										<?php if(in_array(DASHBOARD_SPARE_LIST_PENDING,$bulkAccessArray)){ ?>
											<tr>
												<th><a href="<?php echo ROOT."spare_list_pending";?>" >Spare Part To Receive</a></th>
												<td><span id="new_spare"></span></td>
											</tr>
										<?php } ?>
										<?php if(in_array(DASHBOARD_RETURN_OLD_SPARE,$bulkAccessArray)){ ?>	
											<tr>
												<th><a href="<?php echo ROOT.SERVICE_ROOT."return_old_spare";?>" >Spare Part To Send</a></th>
												<td><span id="old_spare"></span></td>
											</tr>
										<?php } ?>
									<?php } ?>
								</table>
							</div>
						</div>
					<?php }   ?>	
					<!-- Spare Part Section End -->	
				</div>
			</div>
		</div>
	<?php } ?>
	<!-- SERVICE Section END-->
	<!-- solid dashbord start-->
	<?php if ($getspecialConfiguration['solid_permission'] == 1) { ?>
		<div class="col-md-12">
			<div class="panel panel-primary">
				<div class="panel-heading">Production</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class="panel panel-primary">
						<div class="panel-heading">Process</div>
						<div class="panel-body">
							<table class="table">
								<tr>
									<th>Process Name</th>
									<th>In Order</th>
									<th>In Stock</th>
									<th>End Process</th>
									<!-- <th><?php //= count_so_invoice_pending($dbcon); ?></th> -->
								</tr>
								<tr>
									<th><a  href="<?php echo ROOT."production/solid_mixing_entry";?>" >Mixing</a></th>
									<th>-</th>
									<th>-</th>
									<th><a  href="<?php echo ROOT."production/solid_mixing_entry";?>" ><?=count_mixing_end($dbcon);?></a></th>
									<!-- <th><?php //= count_so_invoice_pending($dbcon); ?></th> -->
								</tr>
								<tr>
									<th><a  href="<?php echo ROOT."production/solid_extrusion_entry";?>" >Extrusion</a></th>
									<th><a  href="<?php echo ROOT."production/solid_extrusion_entry/0";?>" ><?=count_exe_inorder($dbcon);?></a></th>
									<th><a  href="<?php echo ROOT."production/solid_extrusion_entry/1";?>" ><?=count_exe_instock($dbcon);?></a></th>
									<th><a  href="<?php echo ROOT."production/solid_extrusion_entry/2";?>" ><?=count_exe_end($dbcon);?></a></th>
									<!-- <th><?php //= count_so_invoice_pending($dbcon); ?></th> -->
								</tr>
								<tr>
									<th><a  href="<?php echo ROOT."production/solid_printing_entry";?>" >Printing</a></th>
									<th><a  href="<?php echo ROOT."production/solid_printing_entry/0";?>" ><?=count_printing_inorder($dbcon);?></a></th>
									<th><a  href="<?php echo ROOT."production/solid_printing_entry/1";?>" ><?=count_printing_instock($dbcon);?></a></th>
									<th><a  href="<?php echo ROOT."production/solid_printing_entry/2";?>" ><?=count_printing_end($dbcon);?></a></th>
									<!-- <th><?php //= count_so_invoice_pending($dbcon); ?></th> -->
								</tr>
							</table>

						</div>
					</div>
				</div>
			</div>
	<?php } ?>
	<!-- solid dashbord end-->

	<!-- FINANCE Section Start-->
	<?php if(in_array(FINANCE_SLUG_VIEW,$bulkAccessArray)) { ?>
		<div class="col-md-12">
			<div class="panel panel-primary">
				<div class="panel-heading">FINANCE</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<div class="panel panel-primary">
						<div class="panel-heading">Invoice</div>
						<div class="panel-body">
							<table class="table">
								<?php if(in_array(DASHBOARD_PENDING_ORDER_INVOICE,$bulkAccessArray)){ ?>
									<tr>
										<th><a  href="<?php echo ROOT."pending_dispatch_list";?>" >SO Invoice Pending</a></th>
										<th><?= count_so_invoice_pending($dbcon); ?></th>
									</tr>
									<?php }?>
									<?php if(in_array(DASHBOARD_CUSTOMER_UNADJUSTED_AMOUNT,$bulkAccessArray)){ ?>
										<tr>
											<th><a  href="<?php echo ROOT."report_cust_unadjusted_amount";?>" >Invoice Unadjusted amount</a></th>
											<th><?= count_invoice_unadjusted($dbcon); ?></th>
										</tr>
									<?php } ?>
									<?php if(in_array(DASHBOARD_PENDING_ORDER_INVOICE,$bulkAccessArray)){ ?>
										<tr>
											<th><a  href="<?php echo ROOT.FINANCE_ROOT."pending_invoice_list";?>" >Pending Order Invoice</a></th>
											<th><?= count_pending_order_invoice($dbcon); ?></th>
										</tr>
									<?php } ?>
									<?php if(in_array(DASHBOARD_PENDING_SPARE_INVOICE,$bulkAccessArray)){ ?>
										<tr>
											<th><a  href="<?php echo ROOT.FINANCE_ROOT."pending_invoice_list";?>" >Pending Spare Invoice</a></th>
											<th><?= count_pending_spare_invoice($dbcon); ?></th>
										</tr>
									<?php } ?>
									<?php if(in_array(DASHBOARD_PENDING_SERVICE_CHARGE_INVOICE,$bulkAccessArray)){ ?>    
										<tr>
											<th><a  href="<?php echo ROOT.FINANCE_ROOT."pending_invoice_list";?>" >Pending Service Charge Invoice</a></th>
											<th><?= count_pending_service_charge_invoice($dbcon); ?></th>
										</tr>
									<?php } ?>
									<?php if(in_array(DASHBOARD_PENDING_FOC_SPARE_INVOICE,$bulkAccessArray)){ ?>    
										<tr>
											<th><a  href="<?php echo ROOT.FINANCE_ROOT."pending_invoice_list";?>" >Pending FOC Spare Invoice</a></th>
											<th><?= count_pending_foc_spare_invoice($dbcon); ?></th>
										</tr>
									<?php } ?>
									<?php if(in_array(DASHBOARD_PENDING_INVOICE_APPROVAL,$bulkAccessArray)){ ?>    
										<tr>
											<th><a  href="<?php echo ROOT.FINANCE_ROOT."unapproved_invoice_list";?>" >Pending Invoice Approval</a></th>
											<th><?=count_pending_invoice_approval($dbcon); ?></th>
										</tr>
									<?php } ?>  
									 
								</table>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="panel panel-primary">
							<div class="panel-heading">Purchase</div>
							<div class="panel-body">
								<table class="table">
									<?php if(in_array(DASHBOARD_VENDOR_UNADJUSTED_AMOUNT,$bulkAccessArray)){ ?>
										<tr>
											<th><a  href="<?php echo ROOT."report_vendor_unadjusted_amount";?>" >Purchase Unadjusted amount</a></th>
											<th><?= count_purchase_unadjusted($dbcon); ?></th>
										</tr>
									<?php } ?>
									<?php if(in_array(DASHBOARD_PURCHASE_BILL_PENDING_LIST,$bulkAccessArray)){ ?>
										<tr>
											<th><a href="<?=ROOT.'purchase_bill_pending_list'?>">Pending Purchase Bill</a></th>
											<td><span id="purchase_bill_pending"></span></td>
										</tr>
									<?php } ?>    
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
		<!-- FINANCE Section END-->
		<!-- DISPATCH Section Start -->
		<?php if(in_array(WD_DISPATCH_VIEW,$bulkAccessArray)) { ?>	
			<div class="col-md-12">
				<div class="panel panel-primary">
					<div class="panel-heading">DISPATCH</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="panel panel-primary">
							<div class="panel-heading">DISPATCH</div>
								<div class="panel-body">
									<table class="table">
										<?php if(in_array(DASHBOARD_DISPATCH_PENDING,$bulkAccessArray)){ ?>
										<tr>
											<th><a  href="<?php echo ROOT.DISPATCH_ROOT."dispatch_pending";?>" > Dispatch Pending</a></th>
											<th><?=dispatch_pending($dbcon,'0')?><?php //=count_pending_invoice_approval($dbcon); ?></th>
										</tr>
										<?php }?>
										<?php if(in_array(DASHBOARD_DISPATCH,$bulkAccessArray)){ ?>
										<tr>
											<th><a  href="<?php echo ROOT.DISPATCH_ROOT."dispatch";?>" > Dispatch </a></th>
											<th><?=dispatch_pending($dbcon,'2')?><?php //=count_pending_invoice_approval($dbcon); ?></th>
										</tr>
										<?php }?>

										<?php if(in_array(DASHBOARD_FINAL_DISPATCH,$bulkAccessArray)){ ?>
										<tr>
											<th><a  href="<?php echo ROOT.DISPATCH_ROOT."final_dispatch";?>" > Final Dispatch </a></th>
											<th><?=dispatch_pending($dbcon,'1')?><?php //=count_pending_invoice_approval($dbcon); ?></th>
										</tr>
										<?php }?>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php }?>
		<!-- DISPATCH Section End -->
		<!-- HRMS Section Start-->
		<?php if(in_array(HRMS_SLUG_VIEW,$bulkAccessArray)) { ?>
			<div class="col-md-12">
				<div class="panel panel-primary">
					<div class="panel-heading">HRMS</div>
				</div>
				<div class="row"></div>
			</div>
		<?php } ?>
		<!-- HRMS Section END-->

		<!-- MAINTENANCE Section Start-->
		<?php if(in_array(MAINTENANCE_SLUG_VIEW,$bulkAccessArray)) { ?>
			<div class="col-md-12">
				<div class="panel panel-primary">
					<div class="panel-heading">MAINTENANCE</div>
				</div>
				<div class="row"></div>
			</div>
		<?php } ?>
		<!-- MAINTENANCE PORTAL Section END-->

		<!-- DISTRIBUTOR PORTAL Section Start-->
		<?php if(in_array(DISTRIBUTOR_PORTAL_SLUG_VIEW,$bulkAccessArray)) { ?>
			<div class="col-md-12">
				<div class="panel panel-primary">
					<div class="panel-heading">DISTRIBUTOR PORTAL</div>
				</div>
				<div class="row"></div>
			</div>
		<?php } ?>
		<!-- DISTRIBUTOR PORTAL Section END-->

		<!-- VENDOR PORTAL Section Start-->
		<?php if(in_array(VENDOR_PORTAL_SLUG_VIEW,$bulkAccessArray)) { ?>
			<div class="col-md-12">
				<div class="panel panel-primary">
					<div class="panel-heading">VENDOR PORTAL</div>
				</div>
				<div class="row"></div>
			</div>
		<?php } ?>
		<!-- VENDOR PORTAL Section END-->

		<!-- SUPPORT TICKET Section Start-->
		<?php if(in_array(SUPPORT_TICKET_SLUG_VIEW,$bulkAccessArray)) { ?>
			<div class="col-md-12">
				<div class="panel panel-primary">
					<div class="panel-heading">SUPPORT TICKET</div>
				</div>
				<div class="row"></div>
			</div>
		<?php } ?>
		<!-- SUPPORT TICKET Section END-->

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
    //load_graph();
    //load_graph_emp();
    load_employee();
    Unloading();
});

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
	var c_year=$('#c_year').val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/dashboard/',
		data: { mode : "getyear", c_year : c_year},
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
		$('#pending_job_work_count').html(data.pending_job_work_count);	
		$('#request_jobwork_count').html(data.request_jobwork_count);	
		$('#pending_jobowork_chalan_count').html(data.pending_jobowork_chalan_count);	
		$('#reprocess_pending_jobowork_chalan_count').html(data.reprocess_pending_jobowork_chalan_count);	
		$('#pending_job_card').html(data.pending_job_card);
		$('#reprocess_pending_jobwork_grn').html(data.reprocess_pending_jobwork_grn);
		$('#pending_job_card_new').html(data.pending_job_card_new);

		
		$('#purchse_quotation_list').html(data.purchse_quotation_list);
		$('#purchse_order_pending').html(data.purchse_order_pending);
		$('#purchse_order_pending_approval').html(data.purchse_order_pending_approval);
		$('#po_aprooval_finance').html(data.po_aprooval_finance);
		$('#purchse_overdue_pending').html(data.po_overdue_pending);
		$('#over_due_inward').html(data.overdue_inward);
		$('#today_inward').html(data.today_inward);
		$('#inward_followup').html(data.inward_followup);
		$('#debit_note_pending').html(data.debit_note_pending);
		$('#total_inward_pending').html(data.total_inward_pending);
		/* START JAYESH gir_counter */
		$('#purchase_gate_inward_receipt').html(data.gir_counter);
		
		$('#service_notes').html(data.service_notes_counter);
		
		$('#po_disapproved').html(data.po_disapproved);
		$('#po_shortclose_approval').html(data.po_shortclose_approval);
		$('#po_shortclose_disapproval').html(data.po_shortclose_disapproval);
		
		$('#purchase_qc_pending').html(data.po_qc_pending);
		$('#parts_qc_pending').html(data.parts_qc_pending);
		$('#reprocess_qc_pending').html(data.reprocess_qc_pending);
		$('#finish_qc_pending').html(data.fp_pending);
		$('#pending_debit_note').html(data.pending_debit_note);
		$('#purchase_bill_pending').html(data.purchase_bill_pending);
		$('#services_purchase_bill_pending').html(data.service_purchase_bill_pending);
		$('#jobwork_purchase_bill_pending').html(data.jobwork_purchase_bill_pending);
		
	}
});
	Unloading();
}  

function load_employee()
{
	$.ajax({
		type: "POST",
		url: root_domain+'app/dashboard/',
		data: { mode : "getemployee"},
		success: function(response){
			console.log(response);
		//alert(response);
		var data = JSON.parse(response);
		$('#e_present').html(data.present);
		$('#e_absent').html(data.absent);
	}
});
	Unloading();
} 

function crm_task_data_load(){
	var user_id=$("#crm_tree_user").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/dashboard/',
		data: { mode : "crm_dashbord_data_load",user_id:user_id},
		success: function(response){
		//console.log(response);
		//var data = JSON.parse(response);
		$('#crm_table_data').html(response);
		$(".select2").select2({
			width: '100%'
		});
		//$('#e_absent').html(data.absent);
	}
});
}
function crm_task_data_load1(){
	var user_id=$("#crm_tree_user1").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/dashboard/',
		data: { mode : "crm_dashbord_data_load1",user_id:user_id},
		success: function(response){
		//console.log(response);
		//var data = JSON.parse(response);
		$('#crm_table_data1').html(response);
		$(".select2").select2({
			width: '100%'
		});
		//$('#e_absent').html(data.absent);
	}
});
}
function fetch_resource_based_on_branch() {
	var branch_id = $('#branch_id').val();
	if(branch_id!=''){
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/resource_report/',
			data: { mode : 'fetch_resource_based_on_branch', branch_id : branch_id},
			success: function(data){
				var arr = jQuery.parseJSON(data);
				$('#resource_id').empty().append(arr.resource_id);
				$("#resource_id").select2({
					width: '100%'
				});	
			}		
		});
		Unloading();
	}else{
		$('#resource_id').empty();
		$("#resource_id").select2({
			width: '100%'
		});	
	}
}
</script>

