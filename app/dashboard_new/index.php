<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../include/function_database_query.php");
include(COMMON_FUNCTION_PATH . "common_functions.php");
include_once(COMMON_FUNCTION_PATH . "common_production_functions.php");
include_once(COMMON_FUNCTION_PATH . "common_production_store_wise_function.php");
include_once("../../include/dashboard_common_functions.php");
include_once(COMMON_FUNCTION_PATH . "common_sub_functions.php");


$company_config = getCompanyConfiguration($dbcon);
$is_store_approval = $company_config['store_approval'];
if ($_POST != NULL) {
	$POST = bulk_filter($dbcon, $_POST);
} else {
	$POST = bulk_filter($dbcon, $_GET);
}
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
	DASHBOARD_POST_CRM_GENERAL_FOLLOWUP,
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
	/*START JAYESH ADD GIR*/ DASHBOARD_PO_GIR_LIST,/*END JAYESH ADD GIR*/
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
	DASHBOARD_FINAL_DISPATCH

	/* END MAULIK*/


]);

$company_config = getCompanyConfiguration($dbcon);
$is_store_approval = $company_config['store_approval'];
$getspecialConfiguration = getspecialConfiguration($dbcon);
$production_on_dashboard = $company_config['production_on_dashboard'];

if (strtolower($POST['mode']) == "sdiv1") {
	$html = '<div class="row">';
	if (in_array(WD_TEAM_PENDING_TASK_SLUG_READ, $bulkAccessArray)) {
		//<!-- Pending follow-ups Section Start -->
		$html .= '<div class="col-md-4">
							<div class="panel panel-primary">
								<div class="panel-heading">TEAM PENDING TASKS</div>
								<div class="panel-body" id="crm_table_data">
									<table class="table">
										<tr> 
											<th colspan="2">
												<select class="form-control" name="crm_tree_user" id="crm_tree_user" onchange="crm_task_data_load();" >';
		$html .= get_assign_users($dbcon, $_SESSION['user_id'], " and user_type in(" . $company_config['crm_user_type'] . ")");
		$html .= '</select>
											</th>
										</tr>';
		if (in_array(DASHBOARD_PENDING_TASK_LIST_INQUIRY_ADD, $bulkAccessArray)) {

			if ($getspecialConfiguration['jainflex_permission'] == '1') {
				$html .= '<tr> 
												<th>
													<a href="' . CRM_ROOT . 'customer">ADD PARTY</a>
												</th>
												<th></th>
											</tr>';
			}

			$html .= '<tr> 
												<th>
													<a href="' . CRM_ROOT . 'inquiry_add">ADD INQUIRY</a>
												</th>
												<th></th>
											</tr>';
		}

		/* define('DASHBOARD_POST_CRM_GENERAL_FOLLOWUP', 'dashboard-post-crm-general-followup'); */
		$in_array_check = array(DASHBOARD_PENDING_TASK_LIST_GENERAL, DASHBOARD_PENDING_TASK_LIST, DASHBOARD_PENDING_TASK_LIST_QUOTATION, DASHBOARD_PENDING_TASK_LIST_REVISE_QUOTATION, DASHBOARD_PENDING_TASK_LIST_QUOTATION_FOLLOWUP, DASHBOARD_POST_CRM_GENERAL_FOLLOWUP);


		/* echo DASHBOARD_POST_CRM_GENERAL_FOLLOWUP; */
		$query = "select mcd_id,mcd_name from tbl_master_category_detail where mcd_status=0 and mcd_cat_id=10 order by priority ASC";
		$query_rs = $dbcon->query($query);
		$i = 0;
		while ($row_p = mysqli_fetch_assoc($query_rs)) {
			if ($row_p['mcd_id'] == GENERAL_TASK_TYPE) {
				$html .= '<tr>
													<th>
														<a href="' . ROOT . CRM_ROOT . 'general_task_list">' . $row_p['mcd_name'] . ' </a>
													</th>
													<th>' . count_general_pen_tsk($dbcon, $_SESSION['user_id']) . ' </th>
												</tr>';
			} else {
				if ($row_p['mcd_id'] == '21' && in_array(DASHBOARD_PENDING_TASK_LIST_QUOTATION_LIST, $bulkAccessArray)) {
					$html .= '<tr>
														<th>
															<a  href="' . CRM_ROOT . 'quotation_list">PENDING QUOTATION APPROVAL</a>
														</th>
														<th>' . count_team_pending_quot_approval($dbcon, $_SESSION['user_id']) . '</th>
													</tr>';
				}
				if (in_array($in_array_check[$i], $bulkAccessArray)) {
					$html .= '<tr>
														<th>
															<a href="' . CRM_ROOT . 'pending_task_list/' . $row_p['mcd_id'] . '/' . $_SESSION['user_id'] . '">' . $row_p['mcd_name'] . '</a>
														</th>
														<th>' . count_usr_pen_tsk($dbcon, $row_p['mcd_id'], $_SESSION['user_id']) . '</th>
													</tr>';
				}
			}
			$i++;
		}
		if (in_array(DASHBOARD_PENDING_TASK_LIST_SALES_ORDER_LIST, $bulkAccessArray)) {
			$html .= '<tr>
											<th>
												<a href="' . ROOT . 'pending_sales_order_list">PENDING P.O. UPLOAD</a>
											</th>
											<th>' . count_pend_po_upload($dbcon, $_SESSION['user_id']) . '</th>
										</tr>
										<tr>
											<th>
												<a href="' . ROOT . CRM_ROOT . 'disapprove_sales_order_list">DISAPPROVE SALES ORDER</a>
											</th>
											<th>' . count_dis_so_upload($dbcon, $_SESSION['user_id']) . '</th>
										</tr>
										<tr>
											<th>
												<a href="' . ROOT . 'pending_so_approve_list">PENDING SALES ORDER APPROVE</a>
											</th>
											<th>' . count_pend_so_approve($dbcon, $_SESSION['user_id']) . '</th>
										</tr>
										<tr>
											<th>
												<a href="' . CRM_ROOT . 'order_acceptance_list">PENDING ORDER ACCEPT</a>
											</th>
											<th>' . count_pend_order_accept($dbcon, $_SESSION['user_id']) . '</th>
										</tr>
										<tr>
											<th>
												<a href="' . CRM_ROOT . 'sales_order_stock_allocation">SALES ORDER STOCK ALLOCATION</a>
											</th>
											<th>' . count_so_stock_allocation($dbcon, $_SESSION['user_id']) . '</th>
										</tr>';
		}
		if (in_array(DASHBOARD_PENDING_TASK_LIST_DISPATCH_LIST, $bulkAccessArray)) {
			$html .= '<tr>
												<th>
													<a href="' . ROOT . 'pending_dispatch_list_crm">PENDING DISPATCH</a>
												</th>
												<th><?php echo count_pend_disp($dbcon); ?></th>
											</tr>';
		}
		if (in_array(DASHBOARD_PENDING_TASK_LIST_APPOINTMENT_LIST, $bulkAccessArray)) {
			$html .= '<tr>
												<th>
													<a href="' . ROOT . 'pending_appointment_list">UPCOMING APPOINTMENTS</a>
												</th>
												<th>' . count_pend_appoint($dbcon, $_SESSION['user_id']) . '</th>
											</tr>';
		}
		$html .= '</table>
								</div>
							</div>
						</div>';
		// Pending follow-ups Section End -->	



	}

	if (in_array(WD_PENDING_TASK_SLUG_READ, $bulkAccessArray)) {
		//<!-- Pending follow-ups Section Start -->
		$html .= '<div class="col-md-4">
							<div class="panel panel-primary">
								<div class="panel-heading">PENDING TASKS</div>
								<div class="panel-body" id="crm_table_data1">
									<table class="table">
										<!-- comment by jayeshbhai -->
										<tr> 
											<th colspan="2"><br></th>
										</tr>';
		if (in_array(DASHBOARD_PERSONAL_PENDING_TASK_LIST_INQUIRY_ADD, $bulkAccessArray)) {
			if ($getspecialConfiguration['jainflex_permission'] == '1') {
				$html .= '<tr> 
												<th>
													<a href="' . CRM_ROOT . 'customer">ADD PARTY</a>
												</th>
												<th></th>
											</tr>';
			}
			$html .= '<tr> 
												<th>
												<a href="' . CRM_ROOT . 'inquiry_add">ADD INQUIRY</a>
												</th>
												<th></th>
											</tr>';
		}
		$personal_in_array_check = array(
			DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_GENERAL, DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE, DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION, DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_REVISE,
			DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION_FOLLOWUP
		);
		$query = "select mcd_id,mcd_name from tbl_master_category_detail where mcd_status=0 and mcd_cat_id=10 order by priority ASC";
		$query_rs = $dbcon->query($query);
		$k = 0;
		while ($row_p = mysqli_fetch_assoc($query_rs)) {
			if ($row_p['mcd_id'] == GENERAL_TASK_TYPE) {
				$html .= '<tr>
													<th>
														<a href="' . ROOT . CRM_ROOT . 'general_task_list">' . $row_p['mcd_name'] . '</a>
													</th>
													<th>' . count_general_pen_tsk($dbcon, $_SESSION['user_id'], false) . '</th>
												</tr>';
			} else {
				if ($row_p['mcd_id'] == '21' && in_array(DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION_LIST, $bulkAccessArray)) {
					$html .= '<tr>
														<th>
															<a href="' . CRM_ROOT . 'quotation_list">PENDING QUOTATION APPROVAL</a>
														</th>
														<th>' . count_user_pending_quot_approval($dbcon, $_SESSION['user_id']) . '</th>
													</tr>';
				}
				if (in_array($personal_in_array_check[$k], $bulkAccessArray)) {
					$html .= '<tr>
														<th>
															<a href="' . CRM_ROOT . 'pending_task_list_one/' . $row_p['mcd_id'] . '/' . $_SESSION['user_id'] . '">' . $row_p['mcd_name'] . '</a>
														</th>
														<th>' . count_usr_pen_tsk1($dbcon, $row_p['mcd_id'], $_SESSION['user_id']) . '</th>
													</tr>';
				}
			}
			$k++;
		}
		if (in_array(DASHBOARD_PERSONAL_PENDING_TASK_LIST_SALES_ORDER_LIST, $bulkAccessArray)) {
			$html .= '<tr>
												<th>
													<a href="' . ROOT . 'pending_sales_order_list">PENDING P.O. UPLOAD</a>
												</th>
												<th>' . count_pend_po_upload($dbcon, $_SESSION['user_id']) . '</th>
											</tr>
											<tr>
												<th>
													<a href="' . ROOT . CRM_ROOT . 'disapprove_sales_order_list">DISAPPROVE SALES ORDER</a>
												</th>
												<th>' . count_dis_so_upload($dbcon, $_SESSION['user_id']) . '</th>
											</tr>
											<tr>
												<th>
													<a href="' . ROOT . 'pending_so_approve_list">PENDING SALES ORDER APPROVE</a>
												</th>
												<th>' . count_pend_so_approve($dbcon, $_SESSION['user_id']) . '</th>
											</tr>
											<tr>
												<th>
													<a href="' . CRM_ROOT . 'order_acceptance_list">PENDING ORDER ACCEPT</a>
												</th>
												<th>' . count_pend_order_accept($dbcon, $_SESSION['user_id']) . '</th>
											</tr>
											<tr>
												<th>
													<a href="' . CRM_ROOT . 'sales_order_stock_allocation">SALES ORDER STOCK ALLOCATION</a>
												</th>
												<th>' . count_so_stock_allocation($dbcon, $_SESSION['user_id']) . '</th>
											</tr>';
		}
		if (in_array(DASHBOARD_PERSONAL_PENDING_TASK_LIST_DISPATCH_LIST, $bulkAccessArray)) {
			$html .= '<tr>
												<th>
													<a href="' . ROOT . 'pending_dispatch_list_crm">PENDING DISPATCH</a>
												</th>
												<th><?php echo count_pend_disp($dbcon); ?></th>
											</tr>';
		}
		if (in_array(DASHBOARD_PERSONAL_PENDING_TASK_LIST_APPOINTMENT_LIST, $bulkAccessArray)) {
			$html .= '<tr>
												<th>
													<a href="' . ROOT . 'pending_appointment_list">UPCOMING APPOINTMENTS</a>
												</th>
												<th>' . count_pend_appoint($dbcon, $_SESSION['user_id']) . '</th>
											</tr>';
		}
		$html .= '</table>
								</div>

							</div>

						</div>';
		//<!-- Pending follow-ups Section End -->	
	}
	$html .= '</div>';

	//var_dump($html);
	//exit;
	echo $html;
} else if (strtolower($POST['mode']) == "sdiv10") {
	$companyConfiguration_dash = getCompanyConfiguration($dbcon);
	$enable_post_crm_dash = $companyConfiguration_dash['enable_post_crm'];

	if ($enable_post_crm_dash == 1) {
		$html .= '
				<div class="row">
					<div class="col-md-4">
						<div class="panel panel-primary">
							<div class="panel-heading">Target</div>
							<table class="table">
								<tr>
									<th>
										<a href="' . ROOT . CRM_ROOT . 'product_wise_target">Product Wise</a>
									</th>
									<th>' . count_product_wise_target($dbcon, $_SESSION['user_id']) . '  </th>
								</tr>
								<tr>
									<th>
										<a href="' . ROOT . CRM_ROOT . 'value_wise_target">Value Wise</a><br>Current month<br>Outstanding<br>Achieved
									</th>
									<th>' . count_value_wise_target($dbcon, $_SESSION['user_id']) . '<br>' . get_total_current_month_target($dbcon, $_SESSION['user_id']) . '<br>' . number_format(get_total_outstanding_target($dbcon, $_SESSION['user_id']), 2) . '<br>' . get_total_achieved_target($dbcon, $_SESSION['user_id']) . '</th>
								</tr>
							</table>
						</div>
					</div>
					<div class="col-md-12">
						<div class="panel panel-primary">
							' . get_target_total_summery($dbcon, $_SESSION['user_id']) . '
						</div>
					</div>
				</div>
			';
		echo $html;
	}
} else if (strtolower($POST['mode']) == "sdiv2") {

	$htmlCode = '<div class="row">
			<div class="col-md-4">';
	//<!-- Design Department Section Start -->
	if (in_array(WD_DESIGN_DEPARTMENT_SLUG_READ, $bulkAccessArray)) {
		$htmlCode .= '<div class="panel panel-primary">

						<div class="panel-heading">DESIGN DEPARTMENT</div>

						<div class="panel-body">

							<table class="table table-hover design-department">
								<thead>';

		if (in_array(DASHBOARD_DESIGN_DEPARTMENT_GET_SALES_ORDER_DETAILS, $bulkAccessArray)) {

			$htmlCode .= '<tr>
										<th class="text-left "><a class="border_line1" href="' . ROOT . PRODUCTION_ROOT . 'design_department_get_sales_order_details">Sales Order Wise Bom</a></th>
										<th class="text-center">' . count_so_wise_bom($dbcon) . '</th>
									</tr>
									<tr>
										<th class="text-left "><a class="border_line1" href="' . ROOT . PRODUCTION_ROOT . 'workorder_permission">Workorder Permission</a></th>
										<th class="text-center">' . count_workorder_permission($dbcon) . '</th>
									</tr>
									<tr>
										<th class="text-left "><a class="border_line1" href="' . ROOT . PRODUCTION_ROOT . 'store_order_design_department">Store Order Wise Bom</a></th>
										<th class="text-center">' . count_store_order_wise_bom($dbcon) . '</th>
									</tr>';
		}

		$htmlCode .= '</thead>

							</table>
						</div>

					</div>';
	}
	//<!-- Out of Stock Section End -->

	$htmlCode .= '</div>
		</div>';

	echo $htmlCode;
} else if (strtolower($POST['mode']) == "sdiv3") {
	$htmlCode = "";
	$htmlCode .= '<div class="row">
						<div class="col-md-4">';
	if (in_array(WD_MRP_SLUG_READ, $bulkAccessArray)) {
		$htmlCode .= '<div class="panel panel-primary">

									<div class="panel-heading">MRP</div>

									<div class="panel-body">

										<table class="table table-hover personal-task">
											<thead>';

		if (in_array(DASHBOARD_GET_SALES_ORDER_DETAILS, $bulkAccessArray)) {
			$htmlCode .= '<tr>
													<th class="text-left "><a class="border_line1" href="' . ROOT . PRODUCTION_ROOT . 'work_order_add">Direct Work Order</a></th>
													<th class="text-center"></th>
													</tr>';
		}

		if (in_array(DASHBOARD_GET_SALES_ORDER_DETAILS, $bulkAccessArray)) {
			if ($company_config["sales_wise_branch_planning"] == "1") {

				$htmlCode .= '<tr>
															<th class="text-left "><a class="border_line1" href="' . ROOT . PRODUCTION_ROOT . 'get_sales_order_details_branch">Sales Order Wise Branch Planning</a></th>
															<th class="text-center">' . count_so_procuct_req_branch($dbcon) . '</th>
														</tr>';
			}
		}
		if (in_array(DASHBOARD_GET_SALES_ORDER_DETAILS, $bulkAccessArray)) {
			$htmlCode .= '<tr>
														<th class="text-left "><a class="border_line1" href="' . ROOT . PRODUCTION_ROOT . 'get_sales_order_details">Sales Order Wise Planning</a></th>
														<th class="text-center">' . count_so_procuct_req($dbcon) . '</th>
													</tr>';
		}
		if (in_array(DASHBOARD_GET_STOCK_DETAILS, $bulkAccessArray)) {

			$htmlCode .= '<tr>
													<th class="text-left "><a class="border_line1" href="' . ROOT . PRODUCTION_ROOT . 'get_stock_detail/min_max">Min-Max Planning</a></th>
													<th class="text-center">' . count_min_max($dbcon, 'min_max') . '</th>
												</tr>
												<tr>
													<th class="text-left "><a class="border_line1" href="' . ROOT . PRODUCTION_ROOT . 'store_order_request/min_max">Store Order Wise Planning</a></th>
													<th class="text-center">' . count_store_order_request($dbcon, 'min_max') . '</th>
												</tr>';
		}
		if (in_array(DASHBOARD_GET_REJECT_QC_REQUEST_LIST, $bulkAccessArray)) {
			$htmlCode .= '<tr>
												<th class="text-left "><a class="border_line1" href="' . ROOT . 'reject_qc_request_list">Reject Product Planning</a></th>
												<th class="text-center">' . count_reject_procuct_req($dbcon) . '</th>
											</tr>';
		}


		$htmlCode .= '</thead>

										</table>

									</div>

								</div>';
	}



	$htmlCode .= '</div>
					</div>';
	echo $htmlCode;
} else if (strtolower($POST['mode']) == "sdiv4") {
	$htmlCode = "";
	$htmlCode .= '<div class="row">';
	if (in_array(WD_PURCHASE_SLUG_READ, $bulkAccessArray)) {
		$htmlCode .= '<div class="col-md-4">
								<div class="panel panel-primary">

									<div class="panel-heading">Purchase</div>

									<div class="panel-body">

										<table class="table">';
		if (in_array(DASHBOARD_INDENT_LIST, $bulkAccessArray)) {
			$htmlCode .= '<tr>
													<th><a href="' . ROOT . PURCHASE_ROOT . 'indent_list">Pending Indent</a></th>
													<td>' . pending_indent_count($dbcon) . '</td>
												</tr>';
		}
		if (in_array(DASHBOARD_PO_QUOTATION_LIST, $bulkAccessArray)) {
			$htmlCode .= '<tr>
													<th><a href="' . ROOT . PURCHASE_ROOT . 'po_quotation_list_new">Purchase Quotation List</a></th>
													<td><span id="purchse_quotation_list"></span></td>
												</tr>';
		}
		if (in_array(DASHBOARD_PO_REQUEST_LIST, $bulkAccessArray)) {
			$htmlCode .= '<tr>
													<th><a href="' . ROOT . PURCHASE_ROOT . 'po_req_list">Purchase Order Pending</a></th>
													<td><span id="purchse_order_pending"></span></td>
												</tr>';
		}
		if (in_array(PURCHASE_ORDER_APPROVAL, $bulkAccessArray)) {
			$htmlCode .= '<tr>
													<th><a href="' . ROOT . PURCHASE_ROOT . 'po_approve_pending_list">Purchase Order Pending Approval</a></th>
													<td><span id="purchse_order_pending_approval"></span></td>
												</tr>';
		}
		if (in_array(PURCHASE_ORDER_FINANCE_APPROVAL, $bulkAccessArray)) {
			$htmlCode .= '<tr>
													<th><a href="' . ROOT . PURCHASE_ROOT . 'po_aprooval_finance">Purchase Order Finance Approval</a></th>
													<td><span id="po_aprooval_finance"></span></td>
												</tr>';
		}
		if (in_array(DASHBOARD_DEBIT_NOTE_PENDING_LIST, $bulkAccessArray)) {
			$htmlCode .= '<tr>
													<th><a href="' . ROOT . PURCHASE_ROOT . 'po_dispproved_list">Purchase Order Disapproved</a></th>
													<td><span id="po_disapproved"></span></td>
												</tr>';
		}
		if (in_array(DASHBOARD_PO_SHORTCLOSE_APPROVAL, $bulkAccessArray)) {
			$htmlCode .= '<tr>
														<th><a href="' . ROOT . PURCHASE_ROOT . 'po_shortclose_approval_list">PO Shortclose Approval Pending</a></th>
														<td><span id="po_shortclose_approval"></span></td>
													</tr>';
		}
		if (in_array(DASHBOARD_PO_SHORTCLOSE_DISAPPROVAL, $bulkAccessArray)) {
			$htmlCode .= '<tr>
															<th><a href="' . ROOT . PURCHASE_ROOT . 'po_shortclose_disapproval_list">PO Shortclose Disapproval</a></th>
															<td><span id="po_shortclose_disapproval"></span></td>
														</tr>';
		}
		if (in_array(DASHBOARD_PO_GIR_LIST, $bulkAccessArray)) {
			$htmlCode .= '<tr>
																<th><a href="' . ROOT . 'gir_list">Gate Inward Receipt</a></th>
																<td><span id="purchase_gate_inward_receipt"></span></td>
															</tr>';
		}
		if (in_array(DASHBOARD_SERVICE_NOTES_LIST, $bulkAccessArray)) {
			$htmlCode .= '<tr>
																<th><a href="' . ROOT . PURCHASE_ROOT . 'service_notes_pro_list">Service Notes</a></th>
																<td><span id="service_notes"></span></td>
															</tr>';
		}
		if (in_array(DASHBOARD_OVERDUE_PO_PRO_LIST, $bulkAccessArray)) {
			$htmlCode .= '<tr>
																	<th><a href="' . ROOT . INVENTORY_ROOT . 'overdue_po_pro_list">Pending Inward</a></th>
																	<td><span id="purchse_overdue_pending"></span></td>
																</tr>

																<tr>
																	<th><a href="' . ROOT . PURCHASE_ROOT . 'over_due_inward">Overdue Purchase Inward</a></th>
																	<td><span id="over_due_inward"></span></td>
																</tr>

																<tr>
																	<th><a href="' . ROOT . PURCHASE_ROOT . 'today_inward">Today Inward</a></th>
																	<td><span id="today_inward"></span></td>
																</tr>';
		}
		if (in_array(DASHBOARD_PURCHASE_BILL_PENDING_LIST, $bulkAccessArray)) {
			$htmlCode .= '<tr>
																		<th><a href="' . ROOT . PURCHASE_ROOT . 'purchase_bill_pending_list">Goods Purchase Bill Pending</a></th>
																		<td><span id="purchase_bill_pending"></span></td>
																	</tr>';
		}
		$htmlCode .= '<tr>
																	<th><a href="' . ROOT . PURCHASE_ROOT . 'services_purchase_bill_pending_list">Services Purchase Bill Pending</a></th>
																	<td><span id="services_purchase_bill_pending"></span></td>
																</tr>

																<tr>
																	<th><a href="' . ROOT . PURCHASE_ROOT . 'jobwork_purchase_bill_pending_list">Job Work Purchase Bill Pending</a></th>
																	<td><span id="jobwork_purchase_bill_pending"></span></td>
																</tr>';
		if (in_array(DASHBOARD_DEBIT_NOTE_PENDING_LIST, $bulkAccessArray)) {
			$htmlCode .= '<tr>
																		<th><a href="' . ROOT . PURCHASE_ROOT . 'debit_note_pending_list">Pending Debit Note</a></th>
																		<td><span id="debit_note_pending"></span></td>
																	</tr>';
		}

		$htmlCode .= '</table>
							
															</div>

														</div>';
	}
	echo $htmlCode;
} else if (strtolower($POST['mode']) == "sdiv5") {
	$htmlCode = "";
	$htmlCode .= '<div class="row">';
	if (in_array(WD_PENDING_JOB_CARD_SLUG_READ, $bulkAccessArray)) {
		$htmlCode .= '<div class="col-md-4">
					<div class="panel panel-primary">

						<div class="panel-heading">PENDING JOB CARD</div>
						
						<div class="panel-body">
							
							<table class="table table-hover personal-task">
								<thead>';
		if (in_array(DASHBOARD_JOB_CARD_LIST, $bulkAccessArray)) {
			$htmlCode .= '<tr>
											<th class="text-left "><a class="border_line1" href="' . ROOT . PRODUCTION_ROOT . 'job_card_list">Job Card</a></th>
											<th><span id="pending_job_card_new"></span></th>
										</tr>';
		}
		if (in_array(DASHBOARD_PENDING_JOB_WORK_LIST, $bulkAccessArray)) {
			$htmlCode .= '<tr>
											<th class="text-left "><a class="border_line1" href="' . ROOT . PRODUCTION_ROOT . 'pending_job_work_list_new">Pending Job Work</a></th>
											<th><span id="pending_job_work_count"></span>
												
											</th>
										</tr>';
		}
		if ($is_store_approval) {
			$htmlCode .= '<tr>
											<th class="text-left "><a class="border_line1" href="' . ROOT . PRODUCTION_ROOT . 'request_jobwork_material">Request Jobwork Material</a></th>
											<th><span id="request_jobwork_count">0</span>
												
											</th>
										</tr>';
		}
		$htmlCode .= '<tr>
										<th class="text-left "><a class="border_line1" href="' . ROOT . PRODUCTION_ROOT . 'pending_jobowork_chalan_list">Create Job Work Chalan</a></th>
										<th><span id="pending_jobowork_chalan_count">0</span>

										</th>
									</tr>';
		if (in_array(DASHBOARD_PENDING_JOB_CARD, $bulkAccessArray)) {
			$htmlCode .= '<tr>
											<th class="text-left "><a class="border_line1" href="' . ROOT . PRODUCTION_ROOT . 'pending_job_work">Pending Job Work GRN</a></th>
											<th><span id="pending_job_card"></span></th>
										</tr>';
		}
		$htmlCode .= '</thead>

							</table>
							
						</div>

					</div>
				</div>';
	}
	$htmlCode .= '<div class="col-md-4">
					<div class="panel panel-primary">

						<div class="panel-heading">JOBWORK REPROCESS</div>
						
						<div class="panel-body">
							
							<table class="table table-hover personal-task">
								<thead>
									
										<tr>
											<th class="text-left "><a class="border_line1" href="' . ROOT . PRODUCTION_ROOT . 'reprocess_pending_jobwork_list">Reprocess Pending Jobwork</a></th>
											<th><span id="reprocess_pending_jobwork_count">' . count_reprocess_jobwork($dbcon) . '</span>
												
											</th>
										</tr>
									<tr>
										<th class="text-left "><a class="border_line1" href="' . ROOT . PRODUCTION_ROOT . 'pending_reprocess_jobowork_chalan_list">Reprocess Create Jobwork Chalan</a></th>
										<th><span id="reprocess_pending_jobowork_chalan_count">0</span>

										</th>
									</tr>

										<tr>
											<th class="text-left "><a class="border_line1" href="' . ROOT . PRODUCTION_ROOT . 'reprocess_pending_jobwork">Reprocess Pending jobWork GRN</a></th>
											<th><span id="reprocess_pending_jobwork_grn"></span></th>
										</tr>
									
								</thead>

							</table>
							
						</div>

					</div>
				</div>';
	echo $htmlCode;
} else if (strtolower($POST['mode']) == "sdiv6") {
	$htmlCode = "";
	if ($production_on_dashboard == '0') {
		$htmlCode .= '<div class="panel-body" style="overflow:auto;">
				<section class="panel">
					<div class="panel-body">
						<ul class="sub ulpad0">';
		if ($company_config['batch_wise_stock'] == '1' && $company_config['batch_process'] == '0') {
			$htmlCode .= '<div class="col-sm-6 col-md-4" style="padding-top:10px;font-size:20px;">
						
									<a style="color: white;" class="two btn btn-shadow btn-primary btn-lg btn-block btn-align" href="<?php echo ROOT.PRODUCTION_ROOT."process_counter_detail_list/create_batch";?>">Create Batch</a>
						
								</div>';
		}
		if ($is_store_approval) {
			$htmlCode .= '<div class="col-sm-6 col-md-4" style="padding-top:10px;font-size:20px;">
						
									<a style="color: white;" class="two btn btn-shadow btn-primary btn-lg btn-block btn-align" href="<?php echo ROOT.PRODUCTION_ROOT."process_counter_detail_list/store_request";?>">Store Request Pending</a>
						
								</div>';
		}
		$htmlCode .= '<div class="col-sm-6 col-md-4" style="padding-top:10px;font-size:20px;">
						
							<a style="color: white;" class="two btn btn-shadow btn-primary btn-lg btn-block btn-align" href="' . ROOT . PRODUCTION_ROOT . 'process_counter_detail_list/pending_start">Pending Start</a>
						
							</div>
					<div class="col-sm-6 col-md-4" style="padding-top:10px;font-size:20px;">
						
							<a style="color: white;" class="two btn btn-shadow btn-primary btn-lg btn-block btn-align" href="' . ROOT . PRODUCTION_ROOT . 'process_counter_detail_list/pending_stop">Pending Stop</a>
						
					</div>
					<div class="col-sm-6 col-md-4" style="padding-top:10px;font-size:20px;">
						
							<a style="color: white;" class="two btn btn-shadow btn-primary btn-lg btn-block btn-align" href="' . ROOT . PRODUCTION_ROOT . 'process_counter_detail_list/reprocess_start">Reprocess Start</a>
						
					</div>
					<div class="col-sm-6 col-md-4" style="padding-top:10px;font-size:20px;">
						
							<a style="color: white;" class="two btn btn-shadow btn-primary btn-lg btn-block btn-align" href="' . ROOT . PRODUCTION_ROOT . 'process_counter_detail_list/reprocess_stop">Reprocess End</a>
						
					</div> 
				</ul>
			</div>
			</section>
		</div>';
	} else {


		$htmlCode .= '<div class="panel-body" style="overflow:auto;">
			
			<table class="table" style="text-align:center">
				
				<tr>
					<th>#</th>
					<th style="white-space:nowrap;">Process Name</th>
					<th style="white-space:nowrap;">Total Pending</th>';
		if ($company_config['batch_wise_stock'] == '1' && $company_config['batch_process'] == '0') {
			$htmlCode .= '<th style="white-space:nowrap;">Create Batch </th>';
		}
		if ($is_store_approval) {
			$htmlCode .= '<th style="white-space:nowrap;">Store Request Pending</th>';
			//<!-- <th style="white-space:nowrap;">Store Release Pending</th> -->
		}
		//	<!--   END ::  Added by Sanat :: 20-09-2021 -->
		$htmlCode .= '<th style="white-space:nowrap;">Pending Start</th>
					<th style="white-space:nowrap;">Pending Stop</th>
					<!-- <th style="white-space:nowrap;">Reprocess Qty</th> -->
					<th style="white-space:nowrap;">Reprocess Start</th>
					<th style="white-space:nowrap;">Reprocess End</th>
					<!--<th>Opening Qty</th>-->
				</tr>';

		$process_array = $bulkcheck =  [];
		$tr = 0;
		$cnt = 1;
		$sel_p1 = $dbcon->query("select process_id,process_name from process_mst where process_status='0' and company_id = " . $_SESSION['company_id'] . " order by dashbord_priority ");
		while ($row_p1 = mysqli_fetch_assoc($sel_p1)) {
			$process_array[] = 'dashboard-inhouse-' . str_replace(' ', '-', strtolower($row_p1['process_name']));
		}
		$bulkcheck = canCheckPermissionAccess($dbcon, $process_array);
		$sel_p = $dbcon->query("select process_id,process_name from process_mst where process_status='0' and company_id = " . $_SESSION['company_id'] . " 
					order by dashbord_priority ");
		while ($row_p = mysqli_fetch_assoc($sel_p)) {


			if (in_array($process_array[$tr], $bulkcheck)) {
				$htmlCode .= '<tr>
							<th>' . $cnt . '</th>
							<th>' . $row_p['process_name'] . '</th>
							
							<th>
								<a href="' . ROOT . PRODUCTION_ROOT . 'process_detail_list/' . $row_p['process_id'] . '/1" class="link_dash">' . count_process_qty($dbcon, $row_p['process_id'], '1') . '</a>
							</th>';
				if ($company_config['batch_wise_stock'] == '1'  && $company_config['batch_process'] == '0') {
					$htmlCode .= '<th>
									<a href="' . ROOT . PRODUCTION_ROOT . 'batch_create_list/' . $row_p['process_id'] . '/1" class="link_dash">' . batch_store_request_pending_count_store_wise($dbcon, $row_p['process_id'], 1, 1, 1) . '</a>
								</th>';
				}
				//<!--   START ::  Added by Sanat :: 20-09-2021 -->
				if ($is_store_approval) {
					$htmlCode .= '<th>
									<a href="' . ROOT . PRODUCTION_ROOT . 'store_request_detail_list/' . $row_p['process_id'] . '/1" class="link_dash">' . store_request_pending_count_store_wise($dbcon, $row_p['process_id'], 1, 1, 1) . '</a>
								</th>
							
								<th> <!--  show allocate qty -->
									<a href="' . ROOT . PRODUCTION_ROOT . 'working_store_process_details_list/' . $row_p['process_id'] . '/1" class="link_dash">' . process_wise_store_production_start_count_new($dbcon, $row_p['process_id'], 1, 1, 1) . '</a>

								</th>';
				} else {
					$htmlCode .= '<th> 
									<a href="' . ROOT . PRODUCTION_ROOT . 'working_process_detail_list/' . $row_p['process_id'] . '/1" class="link_dash">' . process_wise_production_count($dbcon, $row_p['process_id'], 1, 1, 0) . '</a>

								</th>';
				}
				//<!--   END ::  Added by Sanat :: 20-09-2021 -->

				$htmlCode .= '<th>
									<a href="' . ROOT . PRODUCTION_ROOT . 'working_store_process_details_list/' . $row_p['process_id'] . '/2" class="link_dash">' . process_wise_store_production_count($dbcon, $row_p['process_id'], 1, 2) . '</a>

								</th>
								<th>
									<a href="' . ROOT . PRODUCTION_ROOT . 'working_reprocess_detail_list/' . $row_p['process_id'] . '/1"  class="link_dash">' . count_re_process_start_qty($dbcon, $row_p['process_id'], '1') . '</a>
								</th>

								<th>
									<a href="' . ROOT . PRODUCTION_ROOT . 'working_reprocess_detail_list/' . $row_p['process_id'] . '/2"  class="link_dash">' . count_re_process_end_qty($dbcon, $row_p['process_id'], '1') . '</a>
								</th>
							</tr>';
				$cnt++;
			}
			$tr++;
		}
		$htmlCode .= '</table>
			
		</div>';
	}
	echo $htmlCode;
} else if (strtolower($POST['mode']) == "sdiv7") {
	$htmlCode = "";
	$htmlCode .= '<div class="col-md-4">
				<div class="panel-body">
							<table class="table table-hover design-department">
								<thead>';
	if ($is_store_approval) {
		$htmlCode .= '<tr>
												<th class="text-left "><a class="border_line1"  href="' . ROOT . INVENTORY_ROOT . 'store_release_detail_list">Production Direct Material Release </a></th>
												<th class="text-center">0</th>
											</tr>
											<tr>
												<th>
													<a class="border_line1" href="' . ROOT . INVENTORY_ROOT . 'production_direct_material_approval_pending_list">Production Direct Material Approval </a>
												</th> 
												<th class="text-center">' . count_direct_material_approval_request($dbcon) . '
												</th>
											</tr>
											<tr>
												<th class="text-left ">
													<a class="border_line1" href="' . ROOT . INVENTORY_ROOT . 'production_request_pending_material_list">Production Material Release Pending </a>
												</th> 
												<th class="text-center">' . count_store_request($dbcon) . '</th>
											</tr>';
	}
	$htmlCode .= '<tr>
													<th>
														<a class="border_line1" href="' . ROOT . INVENTORY_ROOT . 'returnable_pending_grn_list">Returnable Chalan GRN Pending</a>
													</th> 
													<th class="text-center">' . count_returnable_chalan_grn_pending($dbcon) . '</th>
												</tr>
												<tr>
													<th>
														<a class="border_line1" href="' . ROOT . INVENTORY_ROOT . 'stock_transfer_grn_pending_list">Stock Transfer GRN Pending</a>
													</th> 
													<th class="text-center">' . count_stock_transfer_grn_pending($dbcon) . '</th>
												</tr>
												<tr>
													<th>
														<a class="border_line1" href="' . ROOT . INVENTORY_ROOT . 'store_receive_pending_list_new">Store Receive Approval</a>
													</th> 
													<th class="text-center">' . count_grn_apporve($dbcon) . '</th>
												</tr>
												<tr>
													<th class="text-left ">
														<a class="border_line1" href="' . ROOT . INVENTORY_ROOT . 'store_material_request/min_max">Store Material Request</a>
													</th>
														<th class="text-center">' . count_store_material_request($dbcon, 'min_max') . '</th>
											</tr>

											<tr>
														<th class="text-left "><a class="border_line1" href="' . ROOT . INVENTORY_ROOT . 'production_stock_return_list' . '">Production Store Return List</a></th>
														<th class="text-center">' . count_production_stock_return($dbcon) . '</th>
													</tr>
																	
												</thead>
											</table>
										</div>
									</div>
									
									
								</div>
							
							</div>
						</div>
						</div>';

	echo $htmlCode;
} else if (strtolower($POST['mode']) == "sdiv8") {
	$htmlCode = "";
	$htmlCode .= '<div class="row">';
	if (in_array(WD_QC_PENDING_SLUG_READ, $bulkAccessArray)) {
		$htmlCode .= '<div class="col-md-4">
							<div class="panel panel-primary">
								<div class="panel-heading">Purchase QC </div>
									<div class="panel-body">
										<table class="table">';
		if (in_array(DASHBOARD_PURCHASE_QC_PENDING_LIST, $bulkAccessArray)) {
			$poqcpending = 	"SELECT COUNT(batch.batch_id) as po_qc_pending FROM tbl_batch_data as batch
													left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id
													left join product_mst as pro on pro.product_id=trn.product_id
													left join tbl_grn as grn on grn.grn_id=trn.grn_id
													WHERE batch.status = 0 and batch.qc_status = 0 and grn.grn_status=0 and grn.qc_status=0 and trn.grn_trn_status=0 and trn.product_qc=0 and grn.ref_type in(2,4) and trn.company_id=" . $_SESSION['company_id'];

			$po_qc_pending = mysqli_fetch_assoc($dbcon->query($poqcpending));
			$htmlCode .= '<tr>
															<th><a href="' . ROOT . PURCHASE_ROOT . 'purchase_qc_pending_list">Purchase QC Pending</a></th>
															<td><span id="purchase_qc_pending">' . $po_qc_pending['po_qc_pending'] . '</span></td>
														</tr>';
		}

		$htmlCode .= '</table>

											</div>

										</div>
									</div>
									<div class="col-md-4">
										<div class="panel panel-primary">

											<div class="panel-heading">Process QC </div>

											<div class="panel-body">

												<table class="table">';

		$branch_id_part_qc = $_SESSION['branch_id'];
		$branch_id_part_qc = ($_SESSION['user_type'] == '2' && isset($branch_id_part_qc) && $branch_id_part_qc) ? $branch_id_part_qc : $_SESSION['branch_id'];
		// $where_part_qc_db = check_branch('trn', $branch_id_part_qc);
		$where_part_qc_db = '';
		$part_qc_cou = 0;

		$partsqcpending = "SELECT trn.process_id,trn.process_name FROM `process_mst` as trn
														WHERE trn.process_status=0 and trn.company_id=" . $_SESSION['company_id'] . " " . $where_part_qc_db . "";

		$result_part_qc = $dbcon->query($partsqcpending);
		while ($parts_qc_row = brp_mysqli_fetch_assoc($result_part_qc)) {

			$part_qc_cou = parts_qc_count_process_wise($dbcon, $parts_qc_row['process_id']);
			$htmlCode .= '<tr>
																	<th><a href="' . ROOT . PRODUCTION_ROOT . 'parts_qc_pending_list/' . $parts_qc_row['process_id'] . '">' . $parts_qc_row['process_name'] . '</a></th>
																	<td><span>' . $part_qc_cou . '</span></td>
																</tr>';
		}


		$htmlCode .= '  </table>

                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                        	<div class="panel panel-primary">

                                                        		<div class="panel-heading">Reprocess QC </div>

                                                        		<div class="panel-body">

                                                        			<table class="table">';

		$branch_id_reproces_qc = $_SESSION['branch_id'];
		$branch_id_reproces_qc = ($_SESSION['user_type'] == '2' && isset($branch_id_reproces_qc) && $branch_id_reproces_qc) ? $branch_id_reproces_qc : $_SESSION['branch_id'];
		// $where_reproces_qc_db = check_branch('trn', $branch_id_reproces_qc);
		$where_reproces_qc_db = '';
		$reproces_qc_cou = 0;


		$reprocessqcpending = "SELECT trn.process_id,trn.process_name FROM `process_mst` as trn
                                                        				WHERE trn.process_status=0 and trn.company_id=" . $_SESSION['company_id'] . " " . $where_reproces_qc_db . "";

		$result_reproces_qc = $dbcon->query($reprocessqcpending);
		while ($reprocess_qc_row = brp_mysqli_fetch_assoc($result_reproces_qc)) {

			$reproces_qc_cou = reprocess_qc_count_process_wise($dbcon, $reprocess_qc_row['process_id']);



			$htmlCode .= '<tr>
																				<th><a href="' . ROOT . PRODUCTION_ROOT . 'reprocess_qc_pending_list/' . $reprocess_qc_row['process_id'] . '">' . $reprocess_qc_row['process_name'] . '</a></th>
																				<td><span>' . $reproces_qc_cou . '</span></td>
																			</tr>';
		}


		$htmlCode .= '</table>

                                                        		</div>

                                                        	</div>
                                                        </div>
                                                    </div>';
	}
	$htmlCode .= ' </div>';
	echo $htmlCode;
} else if (strtolower($POST['mode']) == "sdiv9") {
	$htmlCode = "";
	$htmlCode .= '<div class="row">
				<div class="col-md-4">
					<div class="panel panel-primary">
						<div class="panel-heading">Invoice</div>
						<div class="panel-body">
							<table class="table">';
	if (in_array(DASHBOARD_PENDING_ORDER_INVOICE, $bulkAccessArray)) {
		$htmlCode .= '<tr>
										<th><a  href="' . ROOT . 'pending_dispatch_list" >SO Invoice Pending</a></th>
										<th>' . count_so_invoice_pending($dbcon) . '</th>
									</tr>';
	}
	if (in_array(DASHBOARD_CUSTOMER_UNADJUSTED_AMOUNT, $bulkAccessArray)) {
		$htmlCode .= '<tr>
											<th><a  href="' . ROOT . 'report_cust_unadjusted_amount" >Invoice Unadjusted amount</a></th>
											<th>' . count_invoice_unadjusted($dbcon) . '</th>
										</tr>';
	}
	if (in_array(DASHBOARD_PENDING_ORDER_INVOICE, $bulkAccessArray)) {
		$htmlCode .= '<tr>
											<th>
												<a  href="' . ROOT . FINANCE_ROOT . 'pending_invoice_list" >Pending Order Invoice</a>
											</th>
											<th>' . count_pending_order_invoice($dbcon) . '</th>
										</tr>';
	}
	if (in_array(DASHBOARD_PENDING_SPARE_INVOICE, $bulkAccessArray)) {
		$htmlCode .= '<tr>
											<th><a  href="' . ROOT . FINANCE_ROOT . 'pending_invoice_list" >Pending Spare Invoice</a></th>
											<th>' . count_pending_spare_invoice($dbcon) . '</th>
										</tr>';
	}
	if (in_array(DASHBOARD_PENDING_SERVICE_CHARGE_INVOICE, $bulkAccessArray)) {
		$htmlCode .= '<tr>
											<th><a  href="' . ROOT . FINANCE_ROOT . 'pending_invoice_list" >Pending Service Charge Invoice</a></th>
											<th>' . count_pending_service_charge_invoice($dbcon) . '</th>
										</tr>';
	}
	if (in_array(DASHBOARD_PENDING_FOC_SPARE_INVOICE, $bulkAccessArray)) {
		$htmlCode .= '<tr>
											<th><a  href="' . ROOT . FINANCE_ROOT . 'pending_invoice_list" >Pending FOC Spare Invoice</a></th>
											<th>' . count_pending_foc_spare_invoice($dbcon) . '</th>
										</tr>';
	}
	if (in_array(DASHBOARD_PENDING_INVOICE_APPROVAL, $bulkAccessArray)) {
		$htmlCode .= '<tr>
											<th><a  href="' . ROOT . FINANCE_ROOT . 'unapproved_invoice_list">Pending Invoice Approval</a></th>
											<th>' . count_pending_invoice_approval($dbcon) . '</th>
										</tr>';
	}

	$htmlCode .= '</table>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="panel panel-primary">
							<div class="panel-heading">Purchase</div>
							<div class="panel-body">
								<table class="table">';
	if (in_array(DASHBOARD_VENDOR_UNADJUSTED_AMOUNT, $bulkAccessArray)) {
		$htmlCode .= '<tr>
											<th><a  href="' . ROOT . 'report_vendor_unadjusted_amount" >Purchase Unadjusted amount</a></th>
											<th>' . count_purchase_unadjusted($dbcon) . '</th>
										</tr>';
	}
	if (in_array(DASHBOARD_PURCHASE_BILL_PENDING_LIST, $bulkAccessArray)) {
		$htmlCode .= '<tr>
											<th><a href="' . ROOT . 'purchase_bill_pending_list">Pending Purchase Bill</a></th>
											<td><span id="purchase_bill_pending"></span></td>
										</tr>';
	}
	$htmlCode .= '</table>
							</div>
						</div>
					</div>
				</div>
			</div>';
	echo $htmlCode;
} else if (strtolower($POST['mode']) == "sdiv11") {
	$htmlCode = "";
	$htmlCode .= '<div class="row">
				<div class="col-md-12">
							<div class="row">
							<!-- Complaint Section Start -->';
	if (in_array(WD_COMPALINT_SLUG_READ, $bulkAccessArray)) {
		$htmlCode .= '<div class="col-md-4">
									<div class="panel panel-primary">
										<div class="panel-heading">COMPLAINT</div>				
										<div class="panel-body">
											<table class="table">';
		if ($_SESSION['user_type'] != '3' && in_array(DASHBOARD_COMPLAIN_TYPE, $bulkAccessArray)) {
			$htmlCode .= '<tr>
														<th><a href="' . ROOT . SERVICE_ROOT . 'comp_type/1">New Complaint Registered</a></th>
														<td><span id="bussiness_registered"></span></td>
													</tr>';
		}
		if (in_array(DASHBOARD_COMPLAIN_TYPE_COMPLIANT_ASSIGNED, $bulkAccessArray)) {
			$htmlCode .= '<tr>
														<th><a href="' . ROOT . SERVICE_ROOT . 'comp_type/2">Complaint Assigned</a></th>
														<td><span id="bussiness_assign"></span></td>
													</tr>';
		}
		if (in_array(DASHBOARD_COMPLAIN_TYPE_EMPLOYEE_STARTED, $bulkAccessArray)) {
			$htmlCode .= '<tr>
														<th><a href="' . ROOT . SERVICE_ROOT . 'comp_type/7">Employess Started</a></th>
														<td><span id="bussiness_e_start"></span></td>
													</tr>';
		}
		if (in_array(DASHBOARD_COMPLAIN_TYPE_EMPLOYEE_NOT_STARTED, $bulkAccessArray)) {
			$htmlCode .= '<tr>
														<th><a href="' . ROOT . SERVICE_ROOT . 'comp_type/2">Employess Not Started</a></th>
														<td><span id="bussiness_e_notstart"></span></td>
													</tr>';
		}
		if (in_array(DASHBOARD_COMPLAIN_TYPE_CLOSED, $bulkAccessArray)) {
			$htmlCode .= '<tr>
														<th> <a href="' . ROOT . SERVICE_ROOT . 'comp_type/4">Closed</a></th>
														<td><span id="bussiness"></span></td>
													</tr>';
		}
		if (in_array(DASHBOARD_COMPLAIN_TYPE_NOT_DONE, $bulkAccessArray)) {
			$htmlCode .= '<tr>
														<th><a href="' . ROOT . SERVICE_ROOT . 'comp_type/5">Not Done</a></th>
														<td><span id="turnover"></span></td>
													</tr>';
		}
		if (in_array(DASHBOARD_COMPLAIN_LIST, $bulkAccessArray)) {
			$htmlCode .= '<tr>
														<th><a href="' . ROOT . SERVICE_ROOT . 'complaint_list>Total Complaint</a></th>
														<td><span id="all_comp_cnt"></span></td>
													</tr>';
		}
		$htmlCode .= '<tr>
													<th colspan="2">&nbsp;</th>
												</tr>
											</table>
										</div>
									</div>
								</div>';
	}
	$htmlCode .= '<div class="col-md-4">
								<!-- Employee Section Start -->';
	if (in_array(WD_EMPLOYEE_SLUG_READ, $bulkAccessArray)) {
		$htmlCode .= '<div class="panel panel-primary">
										<div class="panel-heading">EMPLOYEE</div>				
										<div class="panel-body">
											<table class="table">';
		if ($_SESSION['user_type'] != '3' && in_array(DASHBOARD_EMPLOYEE_PRESENT_LIST, $bulkAccessArray)) {
			$htmlCode .= '<tr>
														<th><a href="' . ROOT . '"employee_list?type=present">Employee Present</a></th>
														<td><span id="e_present"></span></td>
													</tr>';
		}
		if ($_SESSION['user_type'] != '3' && in_array(DASHBOARD_EMPLOYEE_ABSENT_LIST, $bulkAccessArray)) {
			$htmlCode .= '<tr>
														<th><a href="' . ROOT . '"employee_list?type=absent">Employee Absent</a></th>
														<td><span id="e_absent"></span></td>
													</tr>';
		}
		if (in_array(DASHBOARD_EMPLOYEE_EXPENSE_PENDING_LIST, $bulkAccessArray)) {
			$htmlCode .= '<tr>
														<th>';
			if ($_SESSION['user_type'] != '3') {
				$htmlCode .= '<a href="' . ROOT . '"employee_expense">';
			} else {
				$htmlCode .= '<a href="' . ROOT . '"expense_detail">';
			}
			$htmlCode .= 'Expense Pending
																	</a>
														</th>
														<td><span id="exp_approval"></span></td>
													</tr>';
		}
		$htmlCode .= '</table>
										</div>				
									</div>';
	}
	$htmlCode .= '<!-- Employee Section End -->
							</div>				
							<div class="col-md-4">
								<!-- Spare Parts Section Start -->';
	if (in_array(WD_SPARE_PARTS_SLUG_READ, $bulkAccessArray)) {
		$htmlCode .= '<div class="panel panel-primary">
										<div class="panel-heading">SPARE PARTS</div>				
										<div class="panel-body">
											<table class="table">';
		$usertype = $_SESSION['user_type'];
		if ($usertype != '3') {
			if (in_array(DASHBOARD_SPARE_LIST_PENDING, $bulkAccessArray)) {
				$htmlCode .= '<tr>
															<th><a href="' . ROOT . '"spare_list_pending">Spare Part To send</a></th>
															<td><span id="new_spare"></span></td>
														</tr>';
			}
			if (in_array(DASHBOARD_RETURN_OLD_SPARE, $bulkAccessArray)) {
				$htmlCode .= '<tr>
															<th><a href="' . ROOT . SERVICE_ROOT . '"return_old_spare">Spare Part To Receive</a></th>
															<td><span id="old_spare"></span></td>
														</tr>';
			}
		} else {
			if (in_array(DASHBOARD_SPARE_LIST_PENDING, $bulkAccessArray)) {
				$htmlCode .= '<tr>
															<th><a href="' . ROOT . '"spare_list_pending">Spare Part To Receive</a></th>
															<td><span id="new_spare"></span></td>
														</tr>';
			}
			if (in_array(DASHBOARD_RETURN_OLD_SPARE, $bulkAccessArray)) {
				$htmlCode .= '<tr>
															<th><a href="' . ROOT . SERVICE_ROOT . '"return_old_spare">Spare Part To Send</a></th>
															<td><span id="old_spare"></span></td>
														</tr>';
			}
		}
		$htmlCode .= '</table>
										</div>
									</div>';
	}
	$htmlCode .= '<!-- Spare Part Section End -->
							</div>
						</div>
							</div>
						
			</div>';
	echo $htmlCode;
} else if (strtolower($POST['mode']) == "getemployee") {

	$date = date("Y-m-d");
	$userid = $_SESSION['user_id'];
	$usertype = $_SESSION['user_type'];

	if ($usertype != '3') {

		$p = $dbcon->query("select l_id from tbl_ledger where l_status='0' and l_form='emp_form' and company_id=" . $_SESSION['company_id']);
		$emp_count = mysqli_num_rows($p);

		$q = $dbcon->query("select log_id from login_history where DATE(in_time)='$date' and attendance='yes' and company_id=" . $_SESSION['company_id'] . " group by uid");
		$present_count = mysqli_num_rows($q);


		$count['present'] = $present_count;
		$count['absent'] = $emp_count - $present_count;

		echo json_encode($count);
	}
} else if (strtolower($POST['mode']) == "getyear") {

	$getspecialConfiguration = getspecialConfiguration($dbcon);

	$userid = $_SESSION['user_id'];
	$usertype = $_SESSION['user_type'];
	$cur_date = date("Y-m-d");

	$emp_id = getEmployeeIdUser($dbcon, $userid);

	$where = "";
	$where1 = "";
	if ($usertype != '2' && $getspecialConfiguration['adk_permission'] == '1' && $usertype != '31') {
		// $where .= "  and emp_id='$emp_id'";
		$where .= "  and (emp_id='$emp_id' OR FIND_IN_SET($emp_id,assign_cust_ids))";
		$where1 .= "  and s_emp_id='$emp_id'";
	} else if (($usertype != '2' && $getspecialConfiguration['adk_permission'] == '0')){
		$where .= "  and emp_id='$emp_id'";
		$where1 .= "  and s_emp_id='$emp_id'";
	}

	$cdone = "Select count(complaint_id) as total from tbl_complaint where complaint_status=0 and company_id=" . $_SESSION['company_id'] . " and followup_status='4' " . $where . " ";
	$count_cdone = mysqli_fetch_assoc($dbcon->query($cdone));

	$cndone = "Select count(complaint_id) as n_total from tbl_complaint where complaint_status=0 and company_id=" . $_SESSION['company_id'] . " and  followup_status='5' " . $where . " ";
	$count_cndone = mysqli_fetch_assoc($dbcon->query($cndone));

	$cassign = "Select count(complaint_id) as a_total from tbl_complaint where complaint_status=0 and company_id=" . $_SESSION['company_id'] . " and  followup_status='2' " . $where . " ";
	$count_assign = mysqli_fetch_assoc($dbcon->query($cassign));

	$creassign = "Select count(complaint_id) as re_total from tbl_complaint where complaint_status=0 and company_id=" . $_SESSION['company_id'] . " and  followup_status='3' " . $where . " ";
	$count_reassign = mysqli_fetch_assoc($dbcon->query($creassign));

	$c_cnt_qry = "Select count(complaint_id) as all_comp_cnt from tbl_complaint where complaint_status=0 " . $where . " and company_id=" . $_SESSION['company_id'] . " and followup_status in(1,2,3,4,5,6,7,8)";
	$c_cnt_rel = mysqli_fetch_assoc($dbcon->query($c_cnt_qry));

	$cunassign = "Select count(complaint_id) as una_total from tbl_complaint where complaint_status=0 and  followup_status='1' " . $where . " ";
	$count_unassign = mysqli_fetch_assoc($dbcon->query($cunassign));

	$c_emp_start = "Select count(complaint_id) as emp_start from tbl_complaint where complaint_status=0 and company_id=" . $_SESSION['company_id'] . " and  followup_status='7' " . $where . " ";
	$count_e_start = mysqli_fetch_assoc($dbcon->query($c_emp_start));

	$c_emp_ex = "Select count(ex_id) as exp_count from tbl_expense_detail where expense_approve_status=0 and expense_status='0' " . $where . " ";
	$count_emp_ex = mysqli_fetch_assoc($dbcon->query($c_emp_ex));

	$c_new_spare = "Select count(s_id) as spare_p_new from tbl_complain_spare_part where sp_sent_status='no' and company_id='$_SESSION[company_id]' " . $where1;
	$count_new_spare = mysqli_fetch_assoc($dbcon->query($c_new_spare));

	$c_old_spare = "Select count(tbl_complain_close_spare_part.s_id) as spare_p_old from tbl_complain_close_spare_part inner join tbl_complaint as comp on comp.complaint_id=tbl_complain_close_spare_part.sc_comp_id 
			 left join tbl_complain_spare_part as tct on tct.s_comp_id =comp.complaint_id
			where comp.complaint_status=0 and tct.s_inv_status = 1 and comp.company_id=" . $_SESSION['company_id'] . " and s_return_status=0" . $where1;
	$count_old_spare = mysqli_fetch_assoc($dbcon->query($c_old_spare));

	$cregister = "Select count(complaint_id) as r_total from tbl_complaint where complaint_status=0  and company_id=" . $_SESSION['company_id'] . " and followup_status='1' " . $where . " ";
	$c_register = mysqli_fetch_assoc($dbcon->query($cregister));

	// $cjobwork="Select count(jobwork_id) as pen_total from tbl_jobwork where job_close_status=0  and status='0' ";

	/* $cjobwork='select jo.*,(select COALESCE(sum(strn.product_qty),0) as tqty from tbl_grn as j 
		   left join tbl_grn_trn as p on p.grn_id=j.grn_id 
		   left join tbl_grn_sub_trn as strn on strn.grn_trn_id=p.grn_trn_id
		   where strn.jobwork_id=jo.jobwork_id and j.grn_status=0 and strn.status=0 and j.ref_type=1 and p.grn_trn_status=0) as tqty from tbl_jobwork as jo 
		   left join product_mst as pr on pr.product_id=jo.j_product_id 
		   where jo.job_close_status="0" and jo.j_process_type!=1 and jo.status="0" and  jo.company_id='.$_SESSION['company_id'].' HAVING j_qty>tqty';
		   $conew=$dbcon->query($cjobwork);
		   $c_mrn_hh=mysqli_num_rows($conew); */

	$dask_job_query = "select job.job_work_id from tbl_job_work_trn as job_trn 
			   left join tbl_job_work as job on job.job_work_id=job_trn.job_work_id
			   where job_trn.grn_complete_status=0 and job.job_work_status=0 and job.job_work_type=2 AND job_trn.is_reprocess = 0 and job.grn_complete_status=0 and job_trn.job_work_trn_status=0 and job_trn.company_id=" . $_SESSION['company_id'] . " group by job_trn.product_id,job_trn.process_id,job_trn.branch_id,job_trn.product_version";
	$conew = $dbcon->query($dask_job_query);
	$c_mrn_hh = brp_mysqli_num_rows($conew);

	$reprocess_pending_jobwork_grn_qry = "select job.job_work_id from tbl_job_work_trn as job_trn 
			   left join tbl_job_work as job on job.job_work_id=job_trn.job_work_id
			   where job_trn.grn_complete_status=0 and job.job_work_status=0 and job.job_work_type=2 and job.grn_complete_status=0 and job_trn.job_work_trn_status=0 AND job_trn.is_reprocess = 1 and job_trn.company_id=" . $_SESSION['company_id'];
	$reprocess_pending_jobwork_result = $dbcon->query($reprocess_pending_jobwork_grn_qry);
	$reprocess_pending_jobwork_counter = brp_mysqli_num_rows($reprocess_pending_jobwork_result);


	$cjobwork111 = 'select count(rp_id) as job_count from tbl_request_product as j 
			   where job_card_status=1 and status not in (2,3) and company_id=' . $_SESSION['company_id'];
	$cjobwork111 = $dbcon->query($cjobwork111);
	$c_mrn_hh11 = mysqli_num_rows($cjobwork111);
	$c_jobwork11 = mysqli_fetch_assoc($cjobwork111);


	$branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
	$where_db = check_branch('mrn', $branch_id);
	$where = " $where_db and mrn.company_id=" . $_SESSION['company_id'];

	$query_deb = "SELECT mrn.mrn_id,grn.grn_id,led.l_name,mtrn.rejected_qty,mrn.qc_no,pro.product_name,qc.qc_no,qc.qc_date,grn.grn_no,grn.grn_date,(select IFNULL(sum(product_qty),0) as qty  from tbl_debitnote_trn as chtrn where chtrn.debitnote_trn_status=0 and chtrn.grn_id=mrn.grn_no and mtrn.product_id=chtrn.product_id) as used_qty FROM tbl_mrn as mrn 
		   left join tbl_mrn_trn as mtrn on mtrn.mrn_no=mrn.mrn_id
		   left join product_mst as pro on pro.product_id=mtrn.product_id
		   left join tbl_grn as grn on grn.grn_id=mrn.grn_no
		   left join tbl_qc as qc on qc.qc_id=mrn.qc_no
		   left join tbl_ledger as led on led.l_id=grn.vender_id
		   where mrn.mrn_status=0 and mtrn.mrn_trn_status=0 and grn.ref_type=2 and grn.purchase_status=1 $where having mtrn.rejected_qty > used_qty order by mrn.mrn_id";

	$conew_db = $dbcon->query($query_deb);
	$debit_note_pending = mysqli_num_rows($conew_db);


	$branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
	$where_db = check_branch('grn', $branch_id);
	$where = " $where_db and grn.company_id=" . $_SESSION['company_id'];

	$query_pur = "SELECT SQL_CALC_FOUND_ROWS grn.grn_id, gtrn.product_qty, grn.grn_no, grn.grn_date, pro.product_name, tc.cat_name, led.l_name, bms.branch_name, grn.user_id, grn.gir_no, grn.invoice_no, (select IFNULL(sum(product_qty),0) as qty from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.grn_id=grn.grn_id and gtrn.product_id=chtrn.product_id) as used_qty, grn.branch_id, po.po_type FROM tbl_grn as grn left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id left join product_mst as pro on pro.product_id=gtrn.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_ledger as led on led.l_id=grn.vender_id left join branch_mst as bms on bms.branch_id=grn.branch_id left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id = gtrn.purchaseordertrn_id left join tbl_purchaseorder as po on po.purchaseorder_id = potrn.purchaseorder_id where grn.grn_status=0 and gtrn.grn_trn_status=0 and grn.ref_type=2 and gtrn.purchase_status=0 and po.po_type=0 and grn.company_id=" . $_SESSION['company_id'] . " ORDER BY grn.grn_id";

	$conew_pub = $dbcon->query($query_pur);
	$purchase_bill_pending = mysqli_num_rows($conew_pub);

	$pen_ser_bill = "SELECT SQL_CALC_FOUND_ROWS ser.service_id, strn.product_qty, ser.service_no, ser.service_date, pro.product_name, tc.cat_name, led.l_name, bms.branch_name, ser.user_id, ser.service_no, ser.invoice_no, (select IFNULL(sum(product_qty),0) as qty from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and strn.product_id=chtrn.product_id) as used_qty, ser.branch_id, po.po_type FROM tbl_service_notes as ser left join tbl_service_notes_trn as strn on strn.service_id=ser.service_id left join product_mst as pro on pro.product_id=strn.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_ledger as led on led.l_id=ser.vender_id left join branch_mst as bms on bms.branch_id=ser.branch_id left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id = strn.purchaseordertrn_id left join tbl_purchaseorder as po on po.purchaseorder_id = potrn.purchaseorder_id where ( 1 AND ser.service_status=0 and strn.service_trn_status=0 and strn.purchase_status=0 and po.po_type=1 and ser.company_id =" . $_SESSION['company_id'] . ")";
	$pe_ser = $dbcon->query($pen_ser_bill);
	$service_purchase_bill_pending = mysqli_num_rows($pe_ser);


	$pen_job_bill = "SELECT SQL_CALC_FOUND_ROWS grn.grn_id, gtrn.product_qty, grn.grn_no, grn.grn_date, pro.product_name, tc.cat_name, led.l_name, bms.branch_name, grn.user_id, grn.gir_no, grn.invoice_no, (select IFNULL(sum(product_qty),0) as qty from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.grn_id=grn.grn_id and gtrn.product_id=chtrn.product_id) as used_qty, grn.branch_id, po.po_type FROM tbl_grn as grn left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id left join product_mst as pro on pro.product_id=gtrn.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_ledger as led on led.l_id=grn.vender_id left join branch_mst as bms on bms.branch_id=grn.branch_id left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id = gtrn.job_work_po_trn_id left join tbl_purchaseorder as po on po.purchaseorder_id = potrn.purchaseorder_id where ( 1 AND grn.grn_status=0 and gtrn.grn_trn_status=0 and grn.ref_type in (1,2) and gtrn.purchase_status=0 and po.po_type=2 and  grn.company_id=" . $_SESSION['company_id'] . ") ORDER BY grn.grn_id desc";
	$pe_job = $dbcon->query($pen_job_bill);
	$jobwork_purchase_bill_pending = mysqli_num_rows($pe_job);


	$branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
	$where_db = check_branch('po', $branch_id);
	$where = " $where_db and po.company_id=" . $_SESSION['company_id'];

	$popending2 = "select po.purchaseordertrn_id,po.mdate,pr.product_name,po.total,po.purchaseordertrn_status,po.cdate,po.user_id,po.po_ref_type,sum(po.product_qty) as pqty,po.po_ref_id,po.product_id,po.po_trn_req_status,GROUP_CONCAT(po.purchaseordertrn_id) as purchastrn_id from tbl_purchasetrntemp as po 
		left join product_mst as pr on pr.product_id=po.product_id
		where po.purchaseordertrn_status = 0 and po_trn_req_status=0 $where group by po.product_id,po.po_trn_req_status";
	$pur_ds = $dbcon->query($popending2);
	$pending_qty = mysqli_num_rows($pur_ds);


	$branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
	$where_db = check_branch('apo', $branch_id);
	$where = " $where_db and apo.company_id=" . $_SESSION['company_id'];

	$quotationsql = "SELECT SQL_CALC_FOUND_ROWS apo.approve_no, apo.approve_date, apo.approve_qty, po.indent_no, delivery_date, po.indent_date, po.rp_po_qty, unit.unit_name, spro.po_req_no, pmst.product_name, po.rp_id, apo.approve_indent_id FROM approve_indent as apo left join tbl_request_product as po on po.rp_id=apo.rp_id left join tbl_set_main_process as spro on spro.sp_id=po.sp_id left join product_mst as pmst on pmst.product_id=po.rp_pid left join unit_mst as unit on unit.unitid=apo.approve_unit where ( 1 AND apo.approve_indent_status=0 and quotation_requirement=1 and quotation_approve_status=0 $where) Group by apo.approve_indent_id ORDER BY apo.approve_indent_id desc";
	$quotation_res = $dbcon->query($quotationsql);
	$purchse_quotation_list = mysqli_num_rows($quotation_res);


	/* $pendingjobworksql = "select SQL_CALC_FOUND_ROWS ap.*,sum(ap.p_qty) as ap_qty,sum(ap.pen_qty) as apen_qty,p.product_type,p.product_name,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty, GROUP_CONCAT(ap.p_id ORDER BY `ap`.`p_id` ASC) as allocate_id from tbl_allocate_process as ap left join product_mst as p on p.product_id=ap.p_product_id left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id where  ap.p_status IN (0,1)  and pr_process_type='2' group by ap.p_product_id,ap.process_id ORDER BY ap.p_id asc";   */

	$pendingjobworksql = "SELECT SQL_CALC_FOUND_ROWS p.product_type, p.product_name, pro.process_name, sum(ap.p_qty) as ap_qty, sum(ap.pen_qty) as apen_qty, IFNULL(end_qty,0) as end_qty, IFNULL(strtt_qty,0) as strtt_qty, GROUP_CONCAT(ap.p_id ORDER BY `ap`.`p_id` ASC) as allocate_id, ap.* FROM tbl_allocate_process as ap 
		   left join product_mst as p on p.product_id=ap.p_product_id 
		   left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id
		   left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0) as apta1 on apta1.pt_alloc_id=ap.p_id 
		   left join process_mst as pro on ap.process_id=pro.process_id 
		   where ( 1 AND pr_process_type='2' and ap.p_status IN (0,1) ) Group by ap.p_product_id, ap.process_id ORDER BY ap.p_id asc LIMIT 0, 10";
	$pendingjobwork_res = $dbcon->query($pendingjobworksql);
	$pendingjobwork_list = mysqli_num_rows($pendingjobwork_res);

	$pendingjobwork_count = 0;
	while ($rel = brp_mysqli_fetch_array($pendingjobwork_res)) {
		$min_working_qty = 0;
		$allocate_id = $rel['allocate_id'];

		$start_qty_data = "SELECT sum(pt_qty) as start_qty_valua FROM `tbl_allocate_process_trn` where p_status = 0 and pt_alloc_id IN (" . $allocate_id . ") ";
		$start_result = $dbcon->query($start_qty_data);
		$start_qty_result = brp_mysqli_fetch_assoc($start_result);
		$total_start_qty = $start_qty_result['start_qty_valua'];

		$finish_qty_data = "SELECT sum(pt_qty) as start_qty_valua FROM `tbl_allocate_process_trn` where p_status = 1 and pt_alloc_id IN (" . $allocate_id . ") ";
		$finish_result = $dbcon->query($finish_qty_data);
		$finish_qty_result = brp_mysqli_fetch_assoc($finish_result);
		$total_finsih_qty = $finish_qty_result['start_qty_valua'];

		$current_start_qty = $total_start_qty - $total_finsih_qty;

		$req_working_qty = $rel['apen_qty'] - $current_start_qty;

		$process_id          = $rel['process_id'];
		$process_type        = $rel['pr_process_type'];
		$p_product_id 		 = $rel['p_product_id'];
		$p_status 			 = $rel['p_status'];
		$previous_process_id = $rel['previous_process_id'];

		//$min_working_qty = working_qty_avalable($dbcon, $process_id, $process_type, $p_product_id, $p_status, $previous_process_id);

		$min_working_qty = working_qty_avalable($dbcon, $process_id, $process_type, $p_product_id, $p_status, $previous_process_id);




		if ($min_working_qty > 0) {
			$pendingjobwork_count++;
			//$pendingjobwork_count=$pendingjobworksql;
		}
	}

	$pendingjobwork_count = 0;
	$job_penapproval_sql = "SELECT GROUP_CONCAT(p_id) as pid FROM `tbl_allocate_process` as trn
		   WHERE trn.pr_process_type = 2 and trn.p_status in (0,1) and trn.company_id=" . $_SESSION['company_id'] . " group by p_product_id,process_id,branch_id,process_priority,product_version";
	$job_pen_resulr = $dbcon->query($job_penapproval_sql);

	while ($job_pen_approval = mysqli_fetch_assoc($job_pen_resulr)) {
		$q = "SELECT IFNULL(sum(trn.product_base_qty),0) as used_qty FROM `tbl_job_work_sub_trn` as trn  
			   left join tbl_job_work_trn as job_work_trn on job_work_trn.job_work_trn_id =  trn.job_work_trn_id
			   where job_work_sub_trn_status = 0 and p_id IN (" . $job_pen_approval['pid'] . ")  and job_work_trn.job_work_trn_status = 1";
		$job_trn = $dbcon->query($q);
		$job_trn_result = brp_mysqli_fetch_assoc($job_trn);
		$jobwork_working_qty = 0;
		$jobwork_working_qty = $job_trn_result['used_qty'];
		$qtp = production_start_count_using_p_id($dbcon, $job_pen_approval['pid']);
		if ($qtp - $jobwork_working_qty > 0) {
			if ($qtp > 0) {
				$pendingjobwork_count++;
				//$pendingjobwork_count=$pendingjobwork_count." - ".$job_pen_approval['pid'];
			}
		}
	}

	$request_jobwork_count = 0;
	$jobwrk_pending = "SELECT count(job_work_id) as jobwork_request_pending_cnt  FROM tbl_job_work where job_work_type = 2 and grn_complete_status = 0 and job_work_status = 0 and request_status = 0";
	$jobwrk_pending_result = mysqli_fetch_assoc($dbcon->query($jobwrk_pending));
	$request_jobwork_count =  $jobwrk_pending_result['jobwork_request_pending_cnt'];
	//$pendingjobwork_count=$job_penapproval_sql;


	$pending_jobowork_chalan_count  = 0;
	$jobwrk_chalan = "SELECT count(job_work_id) as jobwork_release_chalan_cnt  FROM tbl_job_work where job_work_type = 2 and grn_complete_status = 0 and job_work_status = 0 and release_status = 1 and chalan_status = 0 AND is_reprocess = 0";
	$jobwrk_chalan_result = mysqli_fetch_assoc($dbcon->query($jobwrk_chalan));
	$pending_jobowork_chalan_count =  $jobwrk_chalan_result['jobwork_release_chalan_cnt'];


	$reprocess_pending_jobowork_chalan_count  = 0;
	$reprocess_jobwrk_chalan = "SELECT count(job_work_id) as jobwork_release_chalan_cnt  FROM tbl_job_work where job_work_type = 2 and grn_complete_status = 0 and job_work_status = 0 and release_status = 1 and chalan_status = 0 AND is_reprocess = 1";
	$reprocess_jobwrk_chalan_result = mysqli_fetch_assoc($dbcon->query($reprocess_jobwrk_chalan));
	$reprocess_pending_jobowork_chalan_count =  $reprocess_jobwrk_chalan_result['jobwork_release_chalan_cnt'];


	/* $pooverduepending="select count(purchaseorder_id) as po_overdue_pending from tbl_purchaseorder as so where status=0 and (select IFNULL(sum(product_qty),0) as qty from tbl_purchaseordertrn as sosub  where purchaseordertrn_status=0 and sosub.purchaseorder_id=so.purchaseorder_id ) > (select IFNULL(sum(product_qty),0) as qty  from tbl_grn as chall left join tbl_grn_trn as chtrn on chtrn.grn_id=chall.grn_id where grn_status=0 and chtrn.grn_trn_status=0 and chtrn.purchaseorder_id=so.purchaseorder_id) and so.po_approval_status=1 and so.purchaseorder_due_date < '$cur_date' ";*/

	/*trn.product_qty>(select IFNULL(sum(product_qty+tolerance),0) as qty  from tbl_grn_trn as chtrn
		   where chtrn.grn_trn_status=0 and chtrn.purchaseorder_id=trn.purchaseorder_id and trn.product_id=chtrn.product_id)*/

	$branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
	$where_db = check_branch('po', $branch_id);
	$where = " $where_db and po.company_id=" . $_SESSION['company_id'];

	$purchse_order_pending_approval_sql = "SELECT COUNT(trn.purchaseorder_id) as purchse_order_pending_approval FROM `tbl_purchaseorder` as trn
		   WHERE trn.po_approval_status = 0 and trn.status=0 and trn.company_id=" . $_SESSION['company_id'];
	$purchse_order_pending_approval = mysqli_fetch_assoc($dbcon->query($purchse_order_pending_approval_sql));

	$purchase_order_finance_aprroval_sql = "select count(po.purchaseorder_id) as po_finance_aprooval from tbl_purchaseorder as po 
		WHERE po.po_approval_status = 3 and po.status=0 and po.company_id=" . $_SESSION['company_id'];

	$purchase_order_finance_aprroval = mysqli_fetch_assoc($dbcon->query($purchase_order_finance_aprroval_sql));

	$pooverduepending = "SELECT COUNT(trn.purchaseordertrn_id) as po_overdue_pending FROM `tbl_purchaseordertrn` as trn
		   left join product_mst as pro on pro.product_id=trn.product_id
		   left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id
		   WHERE  trn.used_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po_approval_status=1 and po.po_type = 0" . $where;

	$po_overdue_pending = mysqli_fetch_assoc($dbcon->query($pooverduepending));

	$service_notes_counter = "SELECT COUNT(trn.purchaseordertrn_id) as service_notes_counter FROM `tbl_purchaseordertrn` as trn
		   left join product_mst as pro on pro.product_id=trn.product_id
		   left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id
		   WHERE  trn.used_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po_approval_status=1 and po.po_type = 1" . $where;

	$service_notes = mysqli_fetch_assoc($dbcon->query($service_notes_counter));

	$po_short_pen = "SELECT SQL_CALC_FOUND_ROWS sht.log_id, sht.po_no, sht.product_id, tc.cat_name, sht.short_close_qty, sht.short_close_reason, sht.date, pro.product_name, bms.branch_name, user.user_name, sht.user_id, sht.aproove_status, sht.po_trn_id, sht.po_id, unit.unit_name FROM tbl_log_po_short_close as sht left join product_mst as pro on pro.product_id=sht.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_purchaseorder as po on po.purchaseorder_id=sht.po_id left join branch_mst as bms on bms.branch_id=sht.branch_id left join unit_mst as unit on unit.unitid=sht.unit_id left join users as user on user.user_id=sht.user_id where  sht.short_close_status=0 and aproove_status=0 and sht.company_id in (" . $_SESSION['company_id'] . ") ORDER BY sht.log_id desc";
	$poshorpen = brp_mysqli_num_rows($dbcon->query($po_short_pen));

	$po_short_dis = "SELECT SQL_CALC_FOUND_ROWS sht.log_id, sht.po_no, sht.product_id, tc.cat_name, sht.short_close_qty, sht.short_close_reason, sht.date, pro.product_name, bms.branch_name, user.user_name, sht.user_id, sht.aproove_status, sht.po_trn_id, sht.po_id, unit.unit_name FROM tbl_log_po_short_close as sht left join product_mst as pro on pro.product_id=sht.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_purchaseorder as po on po.purchaseorder_id=sht.po_id left join branch_mst as bms on bms.branch_id=sht.branch_id left join unit_mst as unit on unit.unitid=sht.unit_id left join users as user on user.user_id=sht.user_id where sht.short_close_status=0 and aproove_status=2 and sht.company_id in (" . $_SESSION['company_id'] . ") ORDER BY sht.log_id desc ";
	$poshordiss = brp_mysqli_num_rows($dbcon->query($po_short_dis));

	$today_date = date('Y-m-d');

	$over_due_inword = "SELECT SQL_CALC_FOUND_ROWS pod.po_delivery_date_id, pod.delivery_date, pod.product_qty, po.purchaseorder_no, po.purchaseorder_date, bms.branch_name, pmst.product_name, tc.cat_name, unit.unit_name, po.purchaseorder_id, trn.purchaseordertrn_id, led.l_name, led.cust_mobile, (pod.product_qty-pod.used_qty) as pending_qty 

		   FROM tbl_purchaseorder_delivery_date as pod 

		   left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=pod.purchaseordertrn_id 

		   left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id 

		   left join tbl_ledger as led on led.l_id=po.vender_id 

		   left join branch_mst as bms on bms.branch_id=pod.branch_id 

		   left join product_mst as pmst on pmst.product_id=trn.product_id 

		   left join tbl_category as tc on pmst.product_category=tc.cat_id 

		   left join unit_mst as unit on unit.unitid=trn.unit_id 

		   where pod.po_delivery_date_status=0 and trn.purchaseordertrn_status=0 and po.po_approval_status = 1 and trn.used_status=0 and po.po_type = 0 and delivery_date<'$today_date' and pod.grn_status=0 and pod.company_id=" . $_SESSION['company_id'] . "  Group by pod.po_delivery_date_id ";
	$over_due_inworde = mysqli_num_rows($dbcon->query($over_due_inword));

	$today_inward = "SELECT SQL_CALC_FOUND_ROWS pod.po_delivery_date_id, pod.delivery_date, pod.product_qty, po.purchaseorder_no, po.purchaseorder_date, bms.branch_name, pmst.product_name, tc.cat_name, unit.unit_name, po.purchaseorder_id, trn.purchaseordertrn_id, led.l_name, led.cust_mobile, (pod.product_qty-pod.used_qty) as pending_qty 

		   FROM tbl_purchaseorder_delivery_date as pod 

		   left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=pod.purchaseordertrn_id 

		   left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id 

		   left join tbl_ledger as led on led.l_id=po.vender_id 

		   left join branch_mst as bms on bms.branch_id=pod.branch_id 

		   left join product_mst as pmst on pmst.product_id=trn.product_id 

		   left join tbl_category as tc on pmst.product_category=tc.cat_id 

		   left join unit_mst as unit on unit.unitid=trn.unit_id 

		   where  pod.po_delivery_date_status=0 and po.po_approval_status = 1 and delivery_date='$today_date' and trn.used_status=0 and trn.purchaseordertrn_status=0 and po.po_type = 0 and pod.grn_status=0 and pod.company_id=" . $_SESSION['company_id'] . " Group by pod.po_delivery_date_id ORDER BY pod.delivery_date desc ";

	$today_inwarde = mysqli_num_rows($dbcon->query($today_inward));

	$inward_followup = "SELECT SQL_CALC_FOUND_ROWS pod.po_delivery_date_id, pod.delivery_date, pod.product_qty, po.purchaseorder_no, po.purchaseorder_date, bms.branch_name, pmst.product_name, tc.cat_name, unit.unit_name, po.purchaseorder_id, trn.purchaseordertrn_id, led.l_name, led.cust_mobile, (pod.product_qty-pod.used_qty) as pending_qty, follow.folloup_date, follow.remark 

		   FROM tbl_purchaseorder_followup as follow 


		   left join tbl_purchaseorder_delivery_date as pod on pod.po_delivery_date_id=follow.po_delivery_date_id 
		   left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=pod.purchaseordertrn_id 
		   left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id 
		   left join tbl_ledger as led on led.l_id=po.vender_id 
		   left join branch_mst as bms on bms.branch_id=pod.branch_id 
		   left join product_mst as pmst on pmst.product_id=trn.product_id 
		   left join tbl_category as tc on pmst.product_category=tc.cat_id 
		   left join unit_mst as unit on unit.unitid=trn.unit_id 

		   where ( 1 AND pod.po_delivery_date_status=0 and trn.purchaseordertrn_status=0 and po.po_approval_status = 1 and trn.used_status=0 and po.po_type = 0 and pod.grn_status=0 and follow.followup_status=1 and follow.follow_date='$today_date' and pod.company_id=" . $_SESSION['company_id'] . ") Group by pod.po_delivery_date_id ORDER BY pod.delivery_date desc";
	$inw_folloup = mysqli_num_rows($dbcon->query($inward_followup));

	$totalinwardpending = "select (po_count+job_count) as total_inward_pending from (select count(purchaseorder_id) as po_count from tbl_purchaseorder as so where status=0 and (select IFNULL(sum(product_qty),0) as qty from tbl_purchaseordertrn as sosub where purchaseordertrn_status=0 and sosub.purchaseorder_id=so.purchaseorder_id ) > (select IFNULL(sum(product_qty),0) as qty from tbl_grn as chall left join tbl_grn_trn as chtrn on chtrn.grn_id=chall.grn_id where grn_status=0 and chtrn.grn_trn_status=0 and chtrn.purchaseorder_id=so.purchaseorder_id) and so.po_approval_status=1 ) as t1,(select count(jobwork_id) as job_count from tbl_jobwork as jo where status=0 and job_close_status=0 ) as t2";
	$total_inward_pending = mysqli_fetch_assoc($dbcon->query($totalinwardpending));

	$po_disapproved_qry = "SELECT SQL_CALC_FOUND_ROWS purchaseorder_id, purchaseorder_no, l.l_name, city.city_name, bms.branch_name, purchaseorder_date, g_total, paid_amount, status, purchase_status, po.cdate, po.userid, po.po_type_status, po.po_req_status, po_approval_status FROM tbl_purchaseorder as po left join tbl_ledger as l on po.vender_id=l.l_id left join city_mst city on l.cityid=city.cityid left join branch_mst as bms on bms.branch_id=po.branch_id where ( 1 AND status = 0 and po_type_status=1 and po.company_id=" . $_SESSION['company_id'] . " and po.po_approval_status in (2,4) ) ORDER BY po.purchaseorder_id desc";
	$po_disapproved = mysqli_num_rows($dbcon->query($po_disapproved_qry));

	$poqcpending = 	"SELECT COUNT(batch.batch_id) as po_qc_pending FROM tbl_batch_data as batch
		   left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id
		   left join product_mst as pro on pro.product_id=trn.product_id
		   left join tbl_grn as grn on grn.grn_id=trn.grn_id
		   WHERE batch.status = 0 and batch.qc_status = 0 and grn.grn_status=0 and grn.qc_status=0 and trn.grn_trn_status=0 and trn.product_qc=0 and grn.ref_type in(2,4) and trn.company_id=" . $_SESSION['company_id'];

	$po_qc_pending = mysqli_fetch_assoc($dbcon->query($poqcpending));


	$partsqcpending = "SELECT COUNT(batch.batch_id) as parts_qc_pending FROM tbl_batch_data as batch
		   left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id
		   left join product_mst as pro on pro.product_id=trn.product_id
		   left join tbl_grn as grn on grn.grn_id=trn.grn_id
		   WHERE batch.status = 0 and batch.qc_status = 0 and grn.grn_status=0 and grn.qc_status=0 and trn.grn_trn_status=0 and trn.product_qc=0";


	$parts_qc_pending = mysqli_fetch_assoc($dbcon->query($partsqcpending));

	$reprocessqcpending = "SELECT COUNT(batch.batch_id) as reprocess_qc_pending FROM tbl_batch_data  as batch
		   WHERE batch.status = 0 and reprocess_qc = 1 and batch.qc_status = 0 " . $pwhere . ' ' . $where_db . " and batch.company_id=" . $_SESSION['company_id'];


	$reprocess_qc_pending = mysqli_fetch_assoc($dbcon->query($reprocessqcpending));

	$fppending = "Select count(qctrn_id) as fp_pending from tbl_qc_trn where qc_status=0";
	$fp_pending = mysqli_fetch_assoc($dbcon->query($fppending));

	$pending_debit_note = "Select count(mrn_id) as c_pending_debit_note from tbl_mrn where mrn_status=0";
	$c_pending_debit_note = mysqli_fetch_assoc($dbcon->query($pending_debit_note));

	/* START JAYESH FOR GIR */
	$gir_counter = "Select count(pro_gir_id) as gir_counter from pro_gir where gir_status=0";
	$gir_counter_result = mysqli_fetch_assoc($dbcon->query($gir_counter));
	/* END  JAYESH FOR GIR */
	$count['c_register'] = $c_register['r_total'];
	$count['cdone'] = $count_cdone['total'];
	$count['cndone'] = $count_cndone['n_total'];
	$count['cassign'] = $count_assign['a_total'] + $count_reassign['re_total'];
	$count['unassign'] = $count_unassign['una_total'];
	$count['all_comp_cnt'] = $c_cnt_rel['all_comp_cnt'];
	$count['expense'] = $count_emp_ex['exp_count'];
	$count['emp_start'] = $count_e_start['emp_start'];
	$count['new_spare'] = $count_new_spare['spare_p_new'];
	$count['old_spare'] = $count_old_spare['spare_p_old'];
	//$count['pending_job_card']=$c_jobwork['pen_total'];
	$count['pending_job_work_count'] = $pendingjobwork_count;
	$count['request_jobwork_count'] = $request_jobwork_count;
	$count['pending_jobowork_chalan_count'] = $pending_jobowork_chalan_count;
	$count['reprocess_pending_jobowork_chalan_count'] = $reprocess_pending_jobowork_chalan_count;
	$count['pending_job_card'] = $c_mrn_hh;
	$count['reprocess_pending_jobwork_grn'] = $reprocess_pending_jobwork_counter;
	$count['pending_job_card_new'] = $c_jobwork11['job_count'];


	//$count['purchse_order_pending']=$po_pending['po_pending'];
	$count['purchse_order_pending'] = $pending_qty;
	$count['purchse_quotation_list'] = $purchse_quotation_list;
	$count['po_overdue_pending'] = $po_overdue_pending['po_overdue_pending'];
	$count['overdue_inward'] = $over_due_inworde;
	$count['today_inward'] = $today_inwarde;
	$count['inward_followup'] = $inw_folloup;
	$count['purchse_order_pending_approval'] = $purchse_order_pending_approval['purchse_order_pending_approval'];
	$count['po_aprooval_finance'] = $purchase_order_finance_aprroval['po_finance_aprooval'];
	$count['total_inward_pending'] = $total_inward_pending['total_inward_pending'];

	$count['po_disapproved']	= $po_disapproved;
	/* START JAYESH  for gir counter*/
	$count['gir_counter'] = $gir_counter_result['gir_counter'];
	/* END JAYESH  for gir counter*/
	$count['service_notes_counter'] = $service_notes['service_notes_counter'];

	$count['po_qc_pending'] = $po_qc_pending['po_qc_pending'];
	$count['parts_qc_pending'] = $parts_qc_pending['parts_qc_pending'];
	$count['reprocess_qc_pending'] = $reprocess_qc_pending['reprocess_qc_pending'];
	$count['fp_pending'] = $fp_pending['fp_pending'];
	$count['pending_debit_note'] = $c_pending_debit_note['c_pending_debit_note'];
	$count['debit_note_pending'] = $debit_note_pending;
	$count['purchase_bill_pending'] = $purchase_bill_pending;
	$count['service_purchase_bill_pending'] = $service_purchase_bill_pending;
	$count['jobwork_purchase_bill_pending'] = $jobwork_purchase_bill_pending;
	$count['service_purchase_bill_pending'] = $service_purchase_bill_pending;
	$count['po_shortclose_approval']	= $poshorpen;
	$count['po_shortclose_disapproval']	= $poshordiss;
	//var_dump($count);
	echo json_encode($count);
} else if (strtolower($POST['mode']) == "get_purchase_order_data") {
	$branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
	$where_db = check_branch('apo', $branch_id);
	$where = " $where_db and apo.company_id=" . $_SESSION['company_id'];

	$quotationsql = "SELECT SQL_CALC_FOUND_ROWS apo.approve_no, apo.approve_date, apo.approve_qty, po.indent_no, delivery_date, po.indent_date, po.rp_po_qty, unit.unit_name, spro.po_req_no, pmst.product_name, po.rp_id, apo.approve_indent_id FROM approve_indent as apo left join tbl_request_product as po on po.rp_id=apo.rp_id left join tbl_set_main_process as spro on spro.sp_id=po.sp_id left join product_mst as pmst on pmst.product_id=po.rp_pid left join unit_mst as unit on unit.unitid=apo.approve_unit where ( 1 AND apo.approve_indent_status=0 and quotation_requirement=1 and quotation_approve_status=0 $where) Group by apo.approve_indent_id ORDER BY apo.approve_indent_id desc";
	$quotation_res = $dbcon->query($quotationsql);
	$purchse_quotation_list = mysqli_num_rows($quotation_res);

	$count['purchse_quotation_list'] = $purchse_quotation_list;

	$branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
	$where_db = check_branch('po', $branch_id);
	$where = " $where_db and po.company_id=" . $_SESSION['company_id'];

	$popending2 = "select po.purchaseordertrn_id,po.mdate,pr.product_name,po.total,po.purchaseordertrn_status,po.cdate,po.user_id,po.po_ref_type,sum(po.product_qty) as pqty,po.po_ref_id,po.product_id,po.po_trn_req_status,GROUP_CONCAT(po.purchaseordertrn_id) as purchastrn_id from tbl_purchasetrntemp as po 
		 left join product_mst as pr on pr.product_id=po.product_id
		 where po.purchaseordertrn_status = 0 and po_trn_req_status=0 $where group by po.product_id,po.po_trn_req_status";
	$pur_ds = $dbcon->query($popending2);
	$pending_qty = mysqli_num_rows($pur_ds);

	$count['purchse_order_pending'] = $pending_qty;

	$branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
	$where_db = check_branch('po', $branch_id);
	$where = " $where_db and po.company_id=" . $_SESSION['company_id'];

	$purchse_order_pending_approval_sql = "SELECT COUNT(trn.purchaseorder_id) as purchse_order_pending_approval FROM `tbl_purchaseorder` as trn
			WHERE trn.po_approval_status = 0 and trn.status=0 and trn.company_id=" . $_SESSION['company_id'];
	$purchse_order_pending_approval = mysqli_fetch_assoc($dbcon->query($purchse_order_pending_approval_sql));

	$count['purchse_order_pending_approval'] = $purchse_order_pending_approval['purchse_order_pending_approval'];

	$purchase_order_finance_aprroval_sql = "select count(po.purchaseorder_id) as po_finance_aprooval from tbl_purchaseorder as po 
			 WHERE po.po_approval_status = 3 and po.status=0 and po.company_id=" . $_SESSION['company_id'];

	$purchase_order_finance_aprroval = mysqli_fetch_assoc($dbcon->query($purchase_order_finance_aprroval_sql));

	$count['po_aprooval_finance'] = $purchase_order_finance_aprroval['po_finance_aprooval'];

	$pooverduepending = "SELECT COUNT(trn.purchaseordertrn_id) as po_overdue_pending FROM `tbl_purchaseordertrn` as trn
				left join product_mst as pro on pro.product_id=trn.product_id
				left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id
				WHERE  trn.used_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po_approval_status=1 and po.po_type = 0" . $where;

	$po_overdue_pending = mysqli_fetch_assoc($dbcon->query($pooverduepending));
	$count['po_overdue_pending'] = $po_overdue_pending['po_overdue_pending'];

	$today_date = date('Y-m-d');

	$over_due_inword = "SELECT SQL_CALC_FOUND_ROWS pod.po_delivery_date_id, pod.delivery_date, pod.product_qty, po.purchaseorder_no, po.purchaseorder_date, bms.branch_name, pmst.product_name, tc.cat_name, unit.unit_name, po.purchaseorder_id, trn.purchaseordertrn_id, led.l_name, led.cust_mobile, (pod.product_qty-pod.used_qty) as pending_qty 

				FROM tbl_purchaseorder_delivery_date as pod 

				left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=pod.purchaseordertrn_id 

				left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id 

				left join tbl_ledger as led on led.l_id=po.vender_id 

				left join branch_mst as bms on bms.branch_id=pod.branch_id 

				left join product_mst as pmst on pmst.product_id=trn.product_id 

				left join tbl_category as tc on pmst.product_category=tc.cat_id 

				left join unit_mst as unit on unit.unitid=trn.unit_id 

				where pod.po_delivery_date_status=0 and trn.purchaseordertrn_status=0 and po.po_approval_status = 1 and trn.used_status=0 and po.po_type = 0 and delivery_date<'$today_date' and pod.grn_status=0 and pod.company_id=" . $_SESSION['company_id'] . "  Group by pod.po_delivery_date_id ";
	$over_due_inworde = mysqli_num_rows($dbcon->query($over_due_inword));

	$today_inward = "SELECT SQL_CALC_FOUND_ROWS pod.po_delivery_date_id, pod.delivery_date, pod.product_qty, po.purchaseorder_no, po.purchaseorder_date, bms.branch_name, pmst.product_name, tc.cat_name, unit.unit_name, po.purchaseorder_id, trn.purchaseordertrn_id, led.l_name, led.cust_mobile, (pod.product_qty-pod.used_qty) as pending_qty 

				FROM tbl_purchaseorder_delivery_date as pod 

				left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=pod.purchaseordertrn_id 

				left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id 

				left join tbl_ledger as led on led.l_id=po.vender_id 

				left join branch_mst as bms on bms.branch_id=pod.branch_id 

				left join product_mst as pmst on pmst.product_id=trn.product_id 

				left join tbl_category as tc on pmst.product_category=tc.cat_id 

				left join unit_mst as unit on unit.unitid=trn.unit_id 

				where  pod.po_delivery_date_status=0 and po.po_approval_status = 1 and delivery_date='$today_date' and trn.used_status=0 and trn.purchaseordertrn_status=0 and po.po_type = 0 and pod.grn_status=0 and pod.company_id=" . $_SESSION['company_id'] . " Group by pod.po_delivery_date_id ORDER BY pod.delivery_date desc ";
	//echo $today_inward; exit;
	$today_inwarde = mysqli_num_rows($dbcon->query($today_inward));

	$inward_followup = "SELECT SQL_CALC_FOUND_ROWS pod.po_delivery_date_id, pod.delivery_date, pod.product_qty, po.purchaseorder_no, po.purchaseorder_date, bms.branch_name, pmst.product_name, tc.cat_name, unit.unit_name, po.purchaseorder_id, trn.purchaseordertrn_id, led.l_name, led.cust_mobile, (pod.product_qty-pod.used_qty) as pending_qty, follow.folloup_date, follow.remark 

				FROM tbl_purchaseorder_followup as follow 


				left join tbl_purchaseorder_delivery_date as pod on pod.po_delivery_date_id=follow.po_delivery_date_id 
				left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=pod.purchaseordertrn_id 
				left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id 
				left join tbl_ledger as led on led.l_id=po.vender_id 
				left join branch_mst as bms on bms.branch_id=pod.branch_id 
				left join product_mst as pmst on pmst.product_id=trn.product_id 
				left join tbl_category as tc on pmst.product_category=tc.cat_id 
				left join unit_mst as unit on unit.unitid=trn.unit_id 

				where ( 1 AND pod.po_delivery_date_status=0 and trn.purchaseordertrn_status=0 and po.po_approval_status = 1 and trn.used_status=0 and po.po_type = 0 and pod.grn_status=0 and follow.followup_status=1 and follow.follow_date='$today_date' and pod.company_id=" . $_SESSION['company_id'] . ") Group by pod.po_delivery_date_id ORDER BY pod.delivery_date desc";
	$inw_folloup = mysqli_num_rows($dbcon->query($inward_followup));


	$count['overdue_inward'] = $over_due_inworde;
	$count['today_inward'] = $today_inwarde;
	$count['inward_followup'] = $inw_folloup;

	$branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
	$where_db = check_branch('mrn', $branch_id);
	$where = " $where_db and mrn.company_id=" . $_SESSION['company_id'];

	$query_deb = "SELECT mrn.mrn_id,grn.grn_id,led.l_name,mtrn.rejected_qty,mrn.qc_no,pro.product_name,qc.qc_no,qc.qc_date,grn.grn_no,grn.grn_date,(select IFNULL(sum(product_qty),0) as qty  from tbl_debitnote_trn as chtrn where chtrn.debitnote_trn_status=0 and chtrn.grn_id=mrn.grn_no and mtrn.product_id=chtrn.product_id) as used_qty FROM tbl_mrn as mrn 
				left join tbl_mrn_trn as mtrn on mtrn.mrn_no=mrn.mrn_id
				left join product_mst as pro on pro.product_id=mtrn.product_id
				left join tbl_grn as grn on grn.grn_id=mrn.grn_no
				left join tbl_qc as qc on qc.qc_id=mrn.qc_no
				left join tbl_ledger as led on led.l_id=grn.vender_id
				where mrn.mrn_status=0 and mtrn.mrn_trn_status=0 and grn.ref_type=2 and grn.purchase_status=1 $where having mtrn.rejected_qty > used_qty order by mrn.mrn_id";

	$conew_db = $dbcon->query($query_deb);
	$debit_note_pending = mysqli_num_rows($conew_db);

	$count['debit_note_pending'] = $debit_note_pending;


	$totalinwardpending = "select (po_count+job_count) as total_inward_pending from (select count(purchaseorder_id) as po_count from tbl_purchaseorder as so where status=0 and (select IFNULL(sum(product_qty),0) as qty from tbl_purchaseordertrn as sosub where purchaseordertrn_status=0 and sosub.purchaseorder_id=so.purchaseorder_id ) > (select IFNULL(sum(product_qty),0) as qty from tbl_grn as chall left join tbl_grn_trn as chtrn on chtrn.grn_id=chall.grn_id where grn_status=0 and chtrn.grn_trn_status=0 and chtrn.purchaseorder_id=so.purchaseorder_id) and so.po_approval_status=1 ) as t1,(select count(jobwork_id) as job_count from tbl_jobwork as jo where status=0 and job_close_status=0 ) as t2";
	$total_inward_pending = mysqli_fetch_assoc($dbcon->query($totalinwardpending));

	$count['total_inward_pending'] = $total_inward_pending['total_inward_pending'];

	$gir_counter = "Select count(pro_gir_id) as gir_counter from pro_gir where gir_status=0";
	$gir_counter_result = mysqli_fetch_assoc($dbcon->query($gir_counter));

	$count['gir_counter'] = $gir_counter_result['gir_counter'];


	$po_disapproved_qry = "SELECT SQL_CALC_FOUND_ROWS purchaseorder_id, purchaseorder_no, l.l_name, city.city_name, bms.branch_name, purchaseorder_date, g_total, paid_amount, status, purchase_status, po.cdate, po.userid, po.po_type_status, po.po_req_status, po_approval_status FROM tbl_purchaseorder as po left join tbl_ledger as l on po.vender_id=l.l_id left join city_mst city on l.cityid=city.cityid left join branch_mst as bms on bms.branch_id=po.branch_id where ( 1 AND status = 0 and po_type_status=1 and po.company_id=" . $_SESSION['company_id'] . " and po.po_approval_status in (2,4) ) ORDER BY po.purchaseorder_id desc";
	$po_disapproved = mysqli_num_rows($dbcon->query($po_disapproved_qry));
	$count['po_disapproved']	= $po_disapproved;

	$po_short_pen = "SELECT SQL_CALC_FOUND_ROWS sht.log_id, sht.po_no, sht.product_id, tc.cat_name, sht.short_close_qty, sht.short_close_reason, sht.date, pro.product_name, bms.branch_name, user.user_name, sht.user_id, sht.aproove_status, sht.po_trn_id, sht.po_id, unit.unit_name FROM tbl_log_po_short_close as sht left join product_mst as pro on pro.product_id=sht.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_purchaseorder as po on po.purchaseorder_id=sht.po_id left join branch_mst as bms on bms.branch_id=sht.branch_id left join unit_mst as unit on unit.unitid=sht.unit_id left join users as user on user.user_id=sht.user_id where  sht.short_close_status=0 and aproove_status=0 and sht.company_id in (" . $_SESSION['company_id'] . ") ORDER BY sht.log_id desc";
	$poshorpen = brp_mysqli_num_rows($dbcon->query($po_short_pen));
	//var_dump($poshorpen);exit;

	$po_short_dis = "SELECT SQL_CALC_FOUND_ROWS sht.log_id, sht.po_no, sht.product_id, tc.cat_name, sht.short_close_qty, sht.short_close_reason, sht.date, pro.product_name, bms.branch_name, user.user_name, sht.user_id, sht.aproove_status, sht.po_trn_id, sht.po_id, unit.unit_name FROM tbl_log_po_short_close as sht left join product_mst as pro on pro.product_id=sht.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_purchaseorder as po on po.purchaseorder_id=sht.po_id left join branch_mst as bms on bms.branch_id=sht.branch_id left join unit_mst as unit on unit.unitid=sht.unit_id left join users as user on user.user_id=sht.user_id where sht.short_close_status=0 and aproove_status=2 and sht.company_id in (" . $_SESSION['company_id'] . ") ORDER BY sht.log_id desc ";
	$poshordiss = brp_mysqli_num_rows($dbcon->query($po_short_dis));

	$count['po_shortclose_approval']	= $poshorpen;
	$count['po_shortclose_disapproval']	= $poshordiss;


	$branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
	$where_db = check_branch('grn', $branch_id);
	$where = " $where_db and grn.company_id=" . $_SESSION['company_id'];

	$query_pur = "SELECT SQL_CALC_FOUND_ROWS grn.grn_id, gtrn.product_qty, grn.grn_no, grn.grn_date, pro.product_name, tc.cat_name, led.l_name, bms.branch_name, grn.user_id, grn.gir_no, grn.invoice_no, (select IFNULL(sum(product_qty),0) as qty from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.grn_id=grn.grn_id and gtrn.product_id=chtrn.product_id) as used_qty, grn.branch_id, po.po_type FROM tbl_grn as grn left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id left join product_mst as pro on pro.product_id=gtrn.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_ledger as led on led.l_id=grn.vender_id left join branch_mst as bms on bms.branch_id=grn.branch_id left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id = gtrn.purchaseordertrn_id left join tbl_purchaseorder as po on po.purchaseorder_id = potrn.purchaseorder_id where grn.grn_status=0 and gtrn.grn_trn_status=0 and grn.ref_type=2 and gtrn.purchase_status=0 and po.po_type=0 and grn.company_id=" . $_SESSION['company_id'] . " ORDER BY grn.grn_id";

	$conew_pub = $dbcon->query($query_pur);
	$purchase_bill_pending = mysqli_num_rows($conew_pub);

	$pen_ser_bill = "SELECT SQL_CALC_FOUND_ROWS ser.service_id, strn.product_qty, ser.service_no, ser.service_date, pro.product_name, tc.cat_name, led.l_name, bms.branch_name, ser.user_id, ser.service_no, ser.invoice_no, (select IFNULL(sum(product_qty),0) as qty from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and strn.product_id=chtrn.product_id) as used_qty, ser.branch_id, po.po_type FROM tbl_service_notes as ser left join tbl_service_notes_trn as strn on strn.service_id=ser.service_id left join product_mst as pro on pro.product_id=strn.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_ledger as led on led.l_id=ser.vender_id left join branch_mst as bms on bms.branch_id=ser.branch_id left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id = strn.purchaseordertrn_id left join tbl_purchaseorder as po on po.purchaseorder_id = potrn.purchaseorder_id where ( 1 AND ser.service_status=0 and strn.service_trn_status=0 and strn.purchase_status=0 and po.po_type=1 and ser.company_id =" . $_SESSION['company_id'] . ")";
	$pe_ser = $dbcon->query($pen_ser_bill);
	$service_purchase_bill_pending = mysqli_num_rows($pe_ser);

	$pen_job_bill = "SELECT SQL_CALC_FOUND_ROWS grn.grn_id, gtrn.product_qty, grn.grn_no, grn.grn_date, pro.product_name, tc.cat_name, led.l_name, bms.branch_name, grn.user_id, grn.gir_no, grn.invoice_no, (select IFNULL(sum(product_qty),0) as qty from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.grn_id=grn.grn_id and gtrn.product_id=chtrn.product_id) as used_qty, grn.branch_id, po.po_type FROM tbl_grn as grn left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id left join product_mst as pro on pro.product_id=gtrn.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_ledger as led on led.l_id=grn.vender_id left join branch_mst as bms on bms.branch_id=grn.branch_id left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id = gtrn.job_work_po_trn_id left join tbl_purchaseorder as po on po.purchaseorder_id = potrn.purchaseorder_id where ( 1 AND grn.grn_status=0 and gtrn.grn_trn_status=0 and grn.ref_type in (1,2) and gtrn.purchase_status=0 and po.po_type=2 and  grn.company_id=" . $_SESSION['company_id'] . ") ORDER BY grn.grn_id desc";
	$pe_job = $dbcon->query($pen_job_bill);
	$jobwork_purchase_bill_pending = mysqli_num_rows($pe_job);


	$count['purchase_bill_pending'] = $purchase_bill_pending;
	$count['service_purchase_bill_pending'] = $service_purchase_bill_pending;
	$count['jobwork_purchase_bill_pending'] = $jobwork_purchase_bill_pending;


	echo json_encode($count);
} else if (strtolower($POST['mode']) == "get_job_work_data") {

	$cjobwork111 = 'select count(rp_id) as job_count from tbl_request_product as j 
					where job_card_status=1 and status not in (2,3) and company_id=' . $_SESSION['company_id'];
	$cjobwork111 = $dbcon->query($cjobwork111);
	$c_mrn_hh11 = mysqli_num_rows($cjobwork111);
	$c_jobwork11 = mysqli_fetch_assoc($cjobwork111);

	$count['pending_job_card_new'] = $c_jobwork11['job_count'];

	$pendingjobwork_count = 0;
	$job_penapproval_sql = "SELECT GROUP_CONCAT(p_id) as pid FROM `tbl_allocate_process` as trn
				WHERE trn.pr_process_type = 2 and trn.p_status in (0,1) and trn.company_id=" . $_SESSION['company_id'] . " group by p_product_id,process_id,branch_id,process_priority,product_version";
	$job_pen_resulr = $dbcon->query($job_penapproval_sql);

	while ($job_pen_approval = mysqli_fetch_assoc($job_pen_resulr)) {
		$q = "SELECT IFNULL(sum(trn.product_base_qty),0) as used_qty FROM `tbl_job_work_sub_trn` as trn  
					left join tbl_job_work_trn as job_work_trn on job_work_trn.job_work_trn_id =  trn.job_work_trn_id
					where job_work_sub_trn_status = 0 and p_id IN (" . $job_pen_approval['pid'] . ")  and job_work_trn.job_work_trn_status = 1";
		$job_trn = $dbcon->query($q);
		$job_trn_result = brp_mysqli_fetch_assoc($job_trn);
		$jobwork_working_qty = 0;
		$jobwork_working_qty = $job_trn_result['used_qty'];
		$qtp = production_start_count_using_p_id($dbcon, $job_pen_approval['pid']);
		if ($qtp - $jobwork_working_qty > 0) {
			if ($qtp > 0) {
				$pendingjobwork_count++;
				//$pendingjobwork_count=$pendingjobwork_count." - ".$job_pen_approval['pid'];
			}
		}
	}

	$count['pending_job_work_count'] = $pendingjobwork_count;

	$request_jobwork_count = 0;
	$jobwrk_pending = "SELECT count(job_work_id) as jobwork_request_pending_cnt  FROM tbl_job_work where job_work_type = 2 and grn_complete_status = 0 and job_work_status = 0 and request_status = 0";
	$jobwrk_pending_result = mysqli_fetch_assoc($dbcon->query($jobwrk_pending));
	$request_jobwork_count =  $jobwrk_pending_result['jobwork_request_pending_cnt'];
	$count['request_jobwork_count'] = $request_jobwork_count;

	$pending_jobowork_chalan_count  = 0;
	$jobwrk_chalan = "SELECT count(job_work_id) as jobwork_release_chalan_cnt  FROM tbl_job_work where job_work_type = 2 and grn_complete_status = 0 and job_work_status = 0 and release_status = 1 and chalan_status = 0 AND is_reprocess = 0";
	$jobwrk_chalan_result = mysqli_fetch_assoc($dbcon->query($jobwrk_chalan));
	$pending_jobowork_chalan_count =  $jobwrk_chalan_result['jobwork_release_chalan_cnt'];


	$reprocess_pending_jobowork_chalan_count  = 0;
	$reprocess_jobwrk_chalan = "SELECT count(job_work_id) as jobwork_release_chalan_cnt  FROM tbl_job_work where job_work_type = 2 and grn_complete_status = 0 and job_work_status = 0 and release_status = 1 and chalan_status = 0 AND is_reprocess = 1";
	$reprocess_jobwrk_chalan_result = mysqli_fetch_assoc($dbcon->query($reprocess_jobwrk_chalan));
	$reprocess_pending_jobowork_chalan_count =  $reprocess_jobwrk_chalan_result['jobwork_release_chalan_cnt'];

	$count['pending_jobowork_chalan_count'] = $pending_jobowork_chalan_count;
	$count['reprocess_pending_jobowork_chalan_count'] = $reprocess_pending_jobowork_chalan_count;

	$dask_job_query = "select job.job_work_id from tbl_job_work_trn as job_trn 
					left join tbl_job_work as job on job.job_work_id=job_trn.job_work_id
					where job_trn.grn_complete_status=0 and job.job_work_status=0 and job.job_work_type=2 AND job_trn.is_reprocess = 0 and job.grn_complete_status=0 and job_trn.job_work_trn_status=0 and job_trn.company_id=" . $_SESSION['company_id'] . " group by job_trn.product_id,job_trn.process_id,job_trn.branch_id,job_trn.product_version";
	$conew = $dbcon->query($dask_job_query);
	$c_mrn_hh = brp_mysqli_num_rows($conew);

	// $c_jobwork=mysqli_fetch_assoc();


	$reprocess_pending_jobwork_grn_qry = "select job.job_work_id from tbl_job_work_trn as job_trn 
					left join tbl_job_work as job on job.job_work_id=job_trn.job_work_id
					where job_trn.grn_complete_status=0 and job.job_work_status=0 and job.job_work_type=2 and job.grn_complete_status=0 and job_trn.job_work_trn_status=0 AND job_trn.is_reprocess = 1 and job_trn.company_id=" . $_SESSION['company_id'];
	$reprocess_pending_jobwork_result = $dbcon->query($reprocess_pending_jobwork_grn_qry);
	$reprocess_pending_jobwork_counter = brp_mysqli_num_rows($reprocess_pending_jobwork_result);


	$count['pending_job_card'] = $c_mrn_hh;
	$count['reprocess_pending_jobwork_grn'] = $reprocess_pending_jobwork_counter;

	echo json_encode($count);
}

function get_sdate($date)
{
	$sdate['start_date'] = date('01-04-' . $date);
	$sdate['end_date'] = date('31-03-' . ($date + 1));
	return $sdate;
}
