<?php
session_start();
include('../include/urlfile.php');
$incPath = $path . 'include/';
include_once(COMMON_FUNCTION_PATH . "finance_common_functions.php");
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	SALES_ORDER_SLUG_EDIT,
	SALES_ORDER_SLUG_CREATE
]);

$token = md5(rand(1000, 9999));
$_SESSION['token'] = $token;
$form = "Sales Order";
$countryid = '101';
$stateid = '1';
$cityid = '1';
$branch_id = $_SESSION['branch_id'];
$inquiry_type = '';
$edit_branch_id = '';
$inquiry_id = '';
$quot_type = 0;
$disable = '';
$sales_order_id = '';
if (strpos($_SERVER['REQUEST_URI'], "salesorderedit") == true) {
	if (!in_array(SALES_ORDER_SLUG_EDIT, $bulkAccessArray)) {
		header("Location: " . DOMAIN . "permission_access");
	}
	
	$mode = "Edit";
	$sales_order_id = $dbcon->real_escape_string($_REQUEST['id']);
	$query = "select * from tbl_sales_order where sales_order_id=$sales_order_id";
	$rel = mysqli_fetch_assoc($dbcon->query($query));
	$sales_order_no = $rel['sales_order_no'];
	$date = date('d-m-Y', strtotime($rel['sales_order_date']));
	$cust_id = $rel['cust_id'];
	if ($rel['po_date'] != "1970-01-01" && $rel['po_date'] != "0000-00-00" && $rel['po_date'] != "") {
		$po_date = date('d-m-Y', strtotime($rel['po_date']));
	}
	if ($rel['apson_validity_date'] != "1970-01-01" && $rel['apson_validity_date'] != "0000-00-00" && $rel['apson_validity_date'] != "") {
		$apson_validity_date = date('d-m-Y', strtotime($rel['apson_validity_date']));
	}
	
	if ($rel['delivery_date'] != "1970-01-01" && $rel['delivery_date'] != "0000-00-00" && $rel['delivery_date'] != "") {
		$delivery_date = date('d-m-Y', strtotime($rel['delivery_date']));
	}
	$sfg_date = '';
	if ($rel['sfg_date'] != "1970-01-01" && $rel['sfg_date'] != "0000-00-00" && $rel['sfg_date'] != "") {
		$sfg_date = date('d-m-Y', strtotime($rel['sfg_date']));
	}
	$quotaion_id = $rel['quotation_id'];
	$edit_branch_id = $rel['branch_id'];
	$transport_id = $rel['transport_id'];
	$read_permission = true;
	$inquiry_type = $rel['inquiry_type'];
	$quot_type  = $rel['quot_type'];
	
	$query_q = "select * from tbl_quotation where quotation_id=$quotaion_id";
	$rel_q = mysqli_fetch_assoc($dbcon->query($query_q));
	$rel_q['project_name'] = $rel['project_name'];
	$inquiry_id = $rel_q['inquiry_id'];
	
	
	$with_out_stock_invoice = $rel['with_out_stock_invoice'];
	
	$product_wise = "";
	$sowise = "";
	if (strtolower($rel['delivery_type']) == "product_wise") {
		$product_wise = 'selected="selected"';
	} else {
		$sowise = 'selected="selected"';
	}

	$query_s = $dbcon->query("select mst.* from tbl_so_attch as mst where attach_status=0 and mst.sales_order_id=" . $sales_order_id);

	
	$rent = "";
	if (strtolower($rel['sales_type']) == "rent") {
		$rent = 'selected="selected"';
	} else {
		$sales = 'selected="selected"';
	}
	$disable = 'disabled';
} else if (strpos($_SERVER['REQUEST_URI'], "salesorder_quotation") == true) {
	$quotaion_id = $dbcon->real_escape_string($_REQUEST['id']);
	$mode = "Add";
	$smode = "quotation_mode";
	$date = date('d-m-Y');
	$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = mysqli_fetch_assoc($dbcon->query($set));
	$required = "required";
	
	$query_q = "select tq.*,inq.project_name from tbl_quotation as tq left join tbl_inquiry as inq on inq.inquiry_id=tq.inquiry_id where tq.quotation_id=$quotaion_id";
	$rel_q = mysqli_fetch_assoc($dbcon->query($query_q));
	$inquiry_type = $rel_q['inquiry_type'];
	$edit_branch_id = $rel_q['branch_id'];
	$inquiry_id = $rel_q['inquiry_id'];
	$rel['currency_enable']	= $rel_q['currency_enable'];
	$rel['currency_rate']	= $rel_q['currency_rate'];
	$rel['currency_id']		= $rel_q['currency_id'];
	$quot_type              = $rel_q['quot_type'];
	$rel['kind_attn']		= $rel_q['c_con_id'];
	$quot_type 	 			= $rel_q['quot_type'];
	$rel['branch_id']		= $rel_q['branch_id'];
	$rel['user_id']			= $rel_q['user_id'];
	$rel['transid'] 		= $rel_q['transid'];
	$rel['quot_general_terms_condition_content'] = $rel_q['quot_general_terms_condition_content'];
	$rel['trans_add'] 		= $rel_q['trans_add'];
	$rel['orange']			= $rel_q['orange'];
	$rel['mfg']				= $rel_q['mfg'];
	$rel['trading']			= $rel_q['trading'];
	$rel['repairing']		= $rel_q['repairing'];
	$rel['other']			= $rel_q['other'];
	$rel['orange_total']			= $rel_q['orange_total'];
	$rel['mfg_total']				= $rel_q['mfg_total'];
	$rel['trading_total']			= $rel_q['trading_total'];
	$rel['repairing_total']		= $rel_q['repairing_total'];
	$rel['other_total']			= $rel_q['other_total'];
	
	$po_date = date('d-m-Y');
	$sfg_date = date('d-m-Y');
	//echo $cust_id=copy_ledger_cust1($dbcon,$rel_q['quotation_id']);
	$cust_id = copy_ledger_cust($dbcon, $rel_q['quotation_id']);
	/*var_dump($cust_id);*/
	$delivery_date = date('d-m-Y');
	
	$read_permission = false;
	$set1 = "select * from tbl_company_configuration where company_id=" . $_SESSION['company_id'];
	$set_head1 = brp_mysqli_fetch_assoc($dbcon->query($set1));
	$with_out_stock_invoice = $set_head1['enable_negative_qty'];
	
	
	$query_s = $dbcon->query("select mst.* from tbl_so_attch as mst where attach_status=3 and mst.user_id=" . $_SESSION['user_id']);
	
	
	$sales = 'selected="selected"';
	
	$product_wise = "";
	$sowise = "";
	if (strtolower($rel_q['delivery_type']) == "product_wise") {
		$product_wise = 'selected="selected"';
	} else {
		$sowise = 'selected="selected"';
	}
} else if (strpos($_SERVER['REQUEST_URI'], "sales_order_emend") == true) {
	$mode = "Add";
	$smode = "sales_order_emend";
	$date = date('d-m-Y');
	$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = mysqli_fetch_assoc($dbcon->query($set));
	$required = "required";
	$revise_status = true;

	$sales_order_id = $dbcon->real_escape_string($_REQUEST['id']);
	$query = "select * from tbl_sales_order where sales_order_id=$sales_order_id";
	$rel = mysqli_fetch_assoc($dbcon->query($query));
	// $sales_order_no=$rel['sales_order_no'];
	$date = date('d-m-Y', strtotime($rel['sales_order_date']));
	$cust_id = $rel['cust_id'];
	if ($rel['po_date'] != "1970-01-01" && $rel['po_date'] != "0000-00-00" && $rel['po_date'] != "") {
		$po_date = date('d-m-Y', strtotime($rel['po_date']));
	}
	if ($rel['delivery_date'] != "1970-01-01" && $rel['delivery_date'] != "0000-00-00" && $rel['delivery_date'] != "") {
		$delivery_date = date('d-m-Y', strtotime($rel['delivery_date']));
	}
	$sfg_date = '';
	if ($rel['sfg_date'] != "1970-01-01" && $rel['sfg_date'] != "0000-00-00" && $rel['sfg_date'] != "") {
		$sfg_date = date('d-m-Y', strtotime($rel['sfg_date']));
	}
	$quotaion_id = $rel['quotation_id'];
	$edit_branch_id = $rel['branch_id'];
	$transport_id = $rel['transport_id'];
	$read_permission = true;
	$start_sales_order_id = $rel['start_sales_order_id'];
	$inquiry_type 	= $rel['inquiry_type'];
	$quot_type 	 	= $rel['quot_type'];
	
	$query_q = "select * from tbl_quotation where quotation_id=$quotaion_id";
	$rel_q = mysqli_fetch_assoc($dbcon->query($query_q));
	$rel_q['project_name'] = $rel['project_name'];
	$inquiry_id = $rel_q['inquiry_id'];
	
	$with_out_stock_invoice = $rel['with_out_stock_invoice'];
	
	$product_wise = "";
	$sowise = "";
	if (strtolower($rel['delivery_type']) == "product_wise") {
		$product_wise = 'selected="selected"';
	} else {
		$sowise = 'selected="selected"';
	}
	
	$query_s = $dbcon->query("select mst.* from tbl_so_attch as mst where attach_status=0 and mst.sales_order_id=" . $sales_order_id);

	$disable = 'disabled';
	$rent = "";
	if (strtolower($rel['sales_type']) == "rent") {
		$rent = 'selected="selected"';
	} else {
		$sales = 'selected="selected"';
	}
} else {
	if (!in_array(SALES_ORDER_SLUG_CREATE, $bulkAccessArray)) {
		header("Location: " . DOMAIN . "permission_access");
	}
	$mode = "Add";
	$smode = "";
	$date = date('d-m-Y');
	$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = mysqli_fetch_assoc($dbcon->query($set));
	
	$read_permission = false;
	$set1 = "select * from tbl_company_configuration where company_id=" . $_SESSION['company_id'];
	$set_head1 = mysqli_fetch_assoc($dbcon->query($set1));
	$with_out_stock_invoice = $set_head1['enable_negative_qty'];
	
	$po_date = date('d-m-Y');
	$delivery_date = date('d-m-Y');
	$sfg_date = date('d-m-Y');

	$query_s = $dbcon->query("select mst.* from tbl_so_attch as mst where attach_status=3 and mst.user_id=" . $_SESSION['user_id']);
}
$count_s = brp_mysqli_num_rows($query_s);

$sales = 'selected="selected"';

$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
$set_head = mysqli_fetch_assoc($dbcon->query($set));
$branch_id = $_SESSION['branch_id'];

// Umair Start 14-07-2021
$companySettings = getCompanySettings($dbcon);
$project_wise_manufacturing = '';
$project_wise_item_rate = '';
if ($companySettings) {
	$project_wise_manufacturing = $companySettings['project_wise_manufacturing'];
	$project_wise_item_rate = $companySettings['project_wise_item_rate'];
}

$getspecialConfiguration = getspecialConfiguration($dbcon);
$companyConfiguration = getCompanyConfiguration($dbcon);
$financial_year = get_financial_year_new($dbcon);
$type_conf = $companyConfiguration['so_pro_type'];
$so_pro_type = $companyConfiguration['so_pro_type'];
$sales_pro_search = $companyConfiguration['sales_pro_search'];
$sales_party_show = $companyConfiguration['sales_party_show'];
$crm_user_type = $companyConfiguration['crm_user_type'];
$currency_id = ($rel['currency_id']) ? $rel['currency_id'] : $set_head['currency_id'];

$discount_editable = "";
if ($companyConfiguration['so_discount_editable'] == 0) {
	$discount_editable = "readonly='readonly'";
}
 $pro_id = $sales_order_id;
$query_field = "select * from master_name_field where master_id='$pro_id'";
$rel_field = brp_mysqli_fetch_assoc($dbcon->query($query_field));
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>SALE ORDER</title>
	<?php include_once($incPath . 'include_css_file.php'); ?>
	<style>
		.currency_icon {
			color: green;
			font-size: 12px;
			font-weight: bold;
		}

		label {
			font-size: 15px;
		}

		.row_margin {
			margin-top: 10px;
		}

		.btn-group-vertical>.btn.active,
		.btn-group-vertical>.btn:active,
		.btn-group-vertical>.btn:focus,
		.btn-group-vertical>.btn:hover,
		.btn-group>.btn.active,
		.btn-group>.btn:active,
		.btn-group>.btn:focus,
		.btn-group>.btn:hover {
			z-index: 2;
			background-color: #bbdce6;
		}

		.control-label {
			font-weight: bold;
		}
	</style>
</head>

<body>
	<section id="container" class="sidebar-closed">
		<?php include_once($incPath . 'include_top_menu.php'); ?>
		<?php include_once($incPath . 'left_menu.php'); ?>
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<h3> <?= $mode . ' ' . $form ?></h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?= ROOT . CRM_ROOT . 'sales_order_list' ?>"><?= $form ?> List</a></li>
								</ul>
							</div>
						</section>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
								New <?= $form ?>
							</header>
							<div class="panel-body">
								<form class="form-horizontal" role="form" id="sales_order_add" action="javascript:;" method="post" name="sales_order_add" autocomplete="off">
									<div class="">

										<div class="col-md-12">
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">
															<input type="radio" class="so_quotation_type" id="so_quotation_type_direct" name="so_quotation_type" style="height: 18px;width: 18px;" value="0" onchange="load_company_data();" <?php if (empty($rel['so_quotation_type'])) {
																																																													echo "checked";
																																																												} ?>>
															<strong>Direct So</strong></label>
														<label class="col-md-6 control-label">
															<input type="radio" class="so_quotation_type" id="so_multiple_quotation_type" name="so_quotation_type" style="height: 18px;width: 18px;" value="1" onchange="load_company_data();" <?php if ($rel['so_quotation_type'] == '1') {
																																																													echo "checked";
																																																												} ?>>
															<strong>Multiple Quotation</strong>
														</label>
													</div>
												</div>
												<?phpif ($companyConfiguration['crm_sales_order_user_selecation'] == 1) { ?>
													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label">Users *</label>

															<div class="col-md-8 col-xs-11">
																<select class="select2" name="user_id" id="user_id" onchange="show_data()">
																	<option value="">Select User</option>
																	<?= get_assign_users($dbcon, $rel['user_id'], " and user_type in(" . $crm_user_type . ")"); ?>
																</select>
															</div>
														</div>
													</div>
												<?php} else { ?>
													<input type="hidden" id="user_id" name="user_id" value="<?= $_SESSION['user_id'] ?>">
												<?php} ?>
												<?php if ($companyConfiguration['outside_jobwork']) { ?>
													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label">Jobwork Type *</label>

															<div class="col-md-8 col-xs-11">
																<select class="select2" name="jobwork_type" id="jobwork_type">
																	<option value="0" <?php if ($mode == 'Edit' && $rel['jobwork_type'] == 0) {
																							echo "selected";
																						} ?>>Normal</option>
																	<option value="1" <?php if ($mode == 'Edit' && $rel['jobwork_type'] == 1) {
																							echo "selected";
																						} ?>>Outside Jobwork</option>
																</select>
															</div>
														</div>
													</div>
												<?php } ?>
												<?php if ($project_wise_manufacturing == 'Yes') { ?>
													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label">Inquiry Type*</label>
															<div class="col-md-8">
																<?php if ($inquiry_type != '0' && $inquiry_type != '') { ?>
																	<select class="select2" onchange="load_inquiry_type_product();" disabled="">
																		<?= getInquiryType($dbcon, $inquiry_type) ?>
																	</select>
																	<input type="hidden" id="inquiry_type" name="inquiry_type" value="<?= $inquiry_type ?>">
																<?php } else { ?>
																	<select class="select2" id="inquiry_type" name="inquiry_type" onchange="load_inquiry_type_product('<?= $type_conf ?>','<?= $sales_pro_search ?>');">
																		<?= getInquiryType($dbcon, $inquiry_type) ?>
																	</select>
																<?php } ?>
															</div>
														</div>
													</div>
												<?php } else { ?>
													<input type="hidden" id="inquiry_type" name="inquiry_type" value="1">
												<?php } ?>
												<?phpif ($companyConfiguration['branch_wise_manage'] == 1) { ?>
													<div class="col-md-4">
														<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true, 'load_inquiry_type_product()', '4', '8'); ?>
													</div>
												<?php} ?>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Series*</label>
														<div class="col-md-8 col-xs-12">
															<select <?= $disable ?> class="select2" name="invoicetype_id" id="invoicetype_id" onchange="load_sono(this.value)" required>
																<option value="">--Select Series--</option>
																<?php$chkseri = $dbcon->query("SELECT * FROM tbl_invoicetype WHERE status = 0 AND type_id = 45 AND company_id = " . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id']);
																while ($getseri = brp_mysqli_fetch_assoc($chkseri)) {
																	if ($rel['invoicetype_id'] != '') {
																?>

																		<option value="<?= $getseri['invoicetype_id'] ?>" <?= ($getseri['invoicetype_id'] == $rel['invoicetype_id']) ? "selected" : "" ?>><?= $getseri['invoice_type'] ?></option>
																	<?php } else { ?>
																		<option value="<?= $getseri['invoicetype_id'] ?>" <?= ($getseri['invoicetype_id'] == 22) ? "selected" : "" ?>><?= $getseri['invoice_type'] ?></option>
																<?php}
																} ?>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Sales Order No *</label>
														<div class="col-md-8 col-xs-12">
															<input id="sales_order_no" name="sales_order_no" type="text" class="form-control" title="Enter Sales Order No" placeholder="Enter Sales Order No" value="<?= $sales_order_no ?>" placeholder="Sales Order No" readonly required>
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4  control-label">Sales Order Date*</label>
														<div class="col-md-8 col-xs-12">
															<input id="sales_order_date" name="sales_order_date" type="text" class="form-control default-date-picker required valid" title="Sales Order Date" placeholder="Sales Order Date" value="<?= $date ?>" placeholder="Sales Order Date">
														</div>
													</div>
												</div>

												<?phpif ($getspecialConfiguration['libra_engineering_permission'] == 1) { ?>
													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4  control-label">SFG Date*</label>
															<div class="col-md-8 col-xs-12">
																<input id="sfg_date" name="sfg_date" type="text" class="form-control default-date-picker valid" title="SFG Date" placeholder="SFG Date" value="<?= $sfg_date ?>" placeholder="Sales Order Date">
															</div>
														</div>
													</div>
												<?php} ?>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">PO No </label>
														<div class="col-md-8 col-xs-12">
															<input id="po_no" name="po_no" type="text" class="form-control" title="Enter Purchase Order No" placeholder="Enter Purchase Order No" value="<?= $rel['po_no'] ?>" placeholder="Purchase Order No" <?= $required ?>>
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4  control-label">PO Date</label>
														<div class="col-md-8 col-xs-12">
															<input id="po_date" name="po_date" type="text" class="form-control default-date-picker valid" title="Purchase Order Date" placeholder="Purchase Order Date" value="<?= $po_date ?>" placeholder="Purchase Order Date" <?= $required ?> autocomplete="off">
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4  control-label">Order By</label>
														<div class="col-md-8 col-xs-12">
															<input id="order_by" name="order_by" type="text" class="form-control" title="Order By" placeholder="ex. App" value="<?= $rel['order_by'] ?>" autocomplete="off">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Sales Order Type</label>
														<div class="col-md-8">
															<label class="col-md-6" style="font-weight:bold;"><input type="radio" id="quot_type_domestic" name="quot_type" onclick="load_typeswise_terms(<?= $sales_order_id ?>);" value="0" <?= ($quot_type != '1') ? 'checked' : '' ?>> Domestic</label>
															<label class="col-md-5 " style="font-weight:bold;"><input type="radio" id="quot_type_export" name="quot_type" onclick="load_typeswise_terms(<?= $sales_order_id ?>);" value="1" <?= ($quot_type == '1') ? 'checked' : '' ?>> Export</label>
														</div>
													</div>
												</div>
												<div class="col-md-12" style="margin-top:30px">
													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label"> Company *</label>
															<div class="col-md-6 col-xs-11">
																<select class="select2" name="cust_id" id="cust_id" autocomplete="new-password" onchange="load_consignee_detail(this.value);get_statecode(this.value);get_grossbalance(this.value);get_invoice_total_tax();get_gtotal();get_ledger_details(this.value);get_peyment_terms_details(this.value);quotation_dropdown_data()">
																	<?= getcust($dbcon, $cust_id, $sales_party_show, 1); ?>
																</select>
																<strong style="display:none;color:green" id="gross">Gross balance : <span class="gross"><br></span></strong>
																<strong id="statecode" style="display:none;color:blue">GST StateCode : <span class="statecode"></span></strong>
																<strong id="sez_enable_text" style="display:none;color:red">This Party Is SEZ Enabled</strong>
																<input type="hidden" name="cust_stateid" id="cust_stateid">

															</div>
															<button type="button" id="viewcompany" onclick="preview_cust_dtls()" title="View Company" class="btn btn-primary btn-xs"><i class="fa fa-eye"></i></button>&nbsp;&nbsp;&nbsp;
															<button accesskey="n" class="btn btn-round btn-info btn-xs" type="button" data-toggle="modal" value="R1" onclick="showledger();" data-tooltip="Add New Company"><i class="fa fa-plus"></i></button>
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label">Consignee</label>
															<div class="col-md-7 col-xs-11">
																<select class="select2" name="consignee_id" id="consignee_id" onchange="load_consingy_address();">
																	<?= get_consignee($dbcon, $rel['consignee_id'], $cust_id); ?>
																</select>
															</div>
															<button type="button" id="viewcompany" onclick="add_consignee_open()" title="Add New Consignee" class="btn btn-xs btn-primary"><i class="fa fa-plus"></i></button>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label">Kind Attn.</label>
															<div class="col-md-8 col-xs-11">
																<select class="form-control" name="kind_attn" id="kind_attn" title="Select Kind Attn.">
																	<option value=''>select Kind Attn.</option>
																</select>
																<input type="hidden" name="kind_attn_hidden" id="kind_attn_hidden" value="<?= $rel['kind_attn'] ?>" />
															</div>
														</div>
													</div>

													<div class="col-md-4" style="display:<?= (($companyConfiguration['enable_transport'] == 1) ? "block" : "none") ?>" id="tran_div">
														<div class="form-group">
															<label class="col-md-4 control-label">Transport</label>
															<div class="col-md-8 col-xs-12">
																<select class="form-control" name="transport_id_enable" id="transport_id_enable" onchange="get_eway_bill(this.value,'transport')">
																	<option value="no" selected>No</option>
																	<option value="yes" <?php if ($mode == 'Edit' && $rel['enable_transport'] == 1) {
																							echo "selected";
																						} ?>>Yes</option>
																</select>
																<?php if ($mode == 'Edit' && $rel['enable_transport'] == 1) {
																	$style = "";
																} else {
																	$style = 'display:none';
																} ?>
																<a style="<?= $style; ?>cursor: pointer;" id="transport_link" onclick="get_eway_bill('yes','transport')">Show Transport Details</a>
																<input type="hidden" id="transport_edit_id" name="transport_edit_id" value="<?= $mode == 'Edit' ? $rel['sales_order_id'] : '0' ?>" />
															</div>
														</div>
													</div>
												</div>
										
												<div class="col-md-4">
													<label class="col-md-4 control-label">Sales Type *</label>
													<div class="col-md-8 col-xs-12">
														<select class="form-control" name="sales_type" id="sales_type" required title="Select Sales Type">
															<option value="sales" <?= $sales ?>>Sales</option>
															<option value="rent" <?= $rent ?>>Rent</option>
														</select>
													</div>
												</div>
												<div class="col-md-4">
													<label class="col-md-4 control-label">Delivery Type *</label>
													<div class="col-md-8 col-xs-12">
														<select class="form-control" name="delivery_type" id="delivery_type" onChange="delivery_type_permission();" required title="Select Delivery Type">
															<option value="so_wise" <?= $sowise ?>>SO Wise</option>
															<option value="product_wise" <?= $product_wise ?>>Product Wise</option>
														</select>
													</div>
												</div>

												<div class="col-md-4 delivary_so_wise">
													<div class="form-group">
														<label class="col-md-4  control-label">Delivery Date *</label>
														<div class="col-md-8 col-xs-12">
															<input id="delivery_date" name="delivery_date" type="text" class="form-control default-date-picker required valid" title="Delivery Date" placeholder="Delivery Date" autocomplete="off" value="<?= $delivery_date ?>" placeholder="Delivery Date">
														</div>
													</div>
												</div>
												<?phpif ($smode == "quotation_mode" || !empty($rel['quotation_no'])) { ?>
													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label">Quotation No</label>
															<div class="col-md-8 col-xs-12">
																<input id="quotation_no" name="quotation_no" type="text" class="form-control" title="Show Quotation No" value="<?= $rel_q['quotation_no'] ?>" readonly>
															</div>
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label">Quotation Date</label>
															<div class="col-md-8 col-xs-12">
																<input id="quotation_date" name="quotation_date" type="text" class="form-control" title="Show Quotation Date" value="<?= $rel_q['quotation_date'] ?>" readonly>
															</div>
														</div>
													</div>
												<?php} ?>
												<?phpif ($getspecialConfiguration['elcon_permission'] == 1) { ?>
													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label">Co-ordinator</label>
															<div class="col-md-8 col-xs-12">
																<select class="select2" name="coordinator_id" id="coordinator_id">
																	<?= get_ledger($dbcon, $rel['coordinator_id'], 'and l_group IN (' . SALARY_ACCOUNT . ')'); ?>
																</select>
															</div>
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label">Currency</label>
															<div class="col-md-8">
																<select class="select2" id="currency_id" name="currency_id">
																	<?= get_org_currency($dbcon, $rel['currency_id']) ?>
																</select>
															</div>
														</div>
													</div>
												<?php} ?>
												<?phpif ($getspecialConfiguration['elcon_permission'] == 1 || $getspecialConfiguration['filter_concept_permission'] == 1) { ?>
													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label">Project Name</label>
															<div class="col-md-8">
																<input type="text" id="project_name" name="project_name" class="form-control" value="<?= $rel['project_name'] ?>">
															</div>
														</div>
													</div>
												<?php} ?>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Payment Terms</label>
														<div class="col-md-8 col-xs-11">
															<select class="form-control" name="payment_terms" id="payment_terms">
																<?= getpaymentterms($dbcon, $rel['payment_terms']); ?>
															</select>
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">GST Type</label>
														<div class="col-md-8 col-xs-11">

															<select class="form-control" name="gst_type" id="gst_type">
																<option value="1" <?php if ($rel['gst_type'] == 1) {
																						echo "selected";
																					} else {
																						echo "";
																					} ?>>Item Wise Tax</option>
																<option value="2" <?php if ($rel['gst_type'] == 2) {
																						echo "selected";
																					} else {
																						echo "";
																					} ?>>Merchant</option>
																<option value="3" <?php if ($rel['gst_type'] == 3) {
																						echo "selected";
																					} else {
																						echo "";
																					} ?>>SEZ</option>
																<option value="4" <?php if ($rel['gst_type'] == 4) {
																						echo "selected";
																					} else {
																						echo "";
																					} ?>>GST 0%</option>
																<option value="5" <?php if ($rel['gst_type'] == 5) {
																						echo "selected";
																					} else {
																						echo "";
																					} ?>>GST 5%</option>
																<option value="6" <?php if ($rel['gst_type'] == 6) {
																						echo "selected";
																					} else {
																						echo "";
																					} ?>>GST 12%</option>
																<option value="7" <?php if ($rel['gst_type'] == 7) {
																						echo "selected";
																					} else {
																						echo "";
																					} ?>>GST 18%</option>
																<option value="9" <?php if ($rel['gst_type'] == 9) {
																						echo "selected";
																					} else {
																						echo "";
																					} ?>>GST 9%</option>
																<option value="8" <?php if ($rel['gst_type'] == 8) {
																						echo "selected";
																					} else {
																						echo "";
																					} ?>>GST 24%</option>
															</select>
														</div>
													</div>
												</div>

												<div class="col-md-2" style="display:none">
													<div class="form-group">
														<label class="col-md-6 control-label">Currency Converter *</label>
														<div class="col-md-6">
															<input id="currency_enable" name="currency_enable" type="checkbox" class="" title="" value="1" style="width:20%;height:25px;" onChange="currency_change();" <?php if ($rel['currency_enable'] == 1) {
																																																							echo "checked";
																																																						} ?>>

														</div>
													</div>
												</div>
												<div class="col-md-4 currency_div" style="<?php if ($mode == 'Edit' && $rel['currency_enable'] == 1) {
																								echo "display:block";
																							} else {
																								echo 'display:block';
																							}  ?>">
													<div class="form-group">
														<label class="col-md-4 control-label">Convert Currency *</label>
														<div class="col-md-8">
															<select class="select2" name="currency_id" id="currency_id" onChange="get_symbol();currency_rate_c();">
																<?= getcurrency($dbcon, $currency_id); ?>
															</select>

														</div>
													</div>
												</div>
												<div class="col-md-4 currency_div" style="<?php if ($mode == 'Edit' && $rel['currency_enable'] == 1) {
																								echo "display:block";
																							} else {
																								echo 'display:block';
																							}  ?>">
													<div class="form-group">
														<label class="col-md-4 control-label">Rate *</label>
														<div class="col-md-8 col-xs-11">
															<input id="currency_rate" name="currency_rate" type="text" class="form-control valid numbersOnly" title="" value="<?= $rel['currency_rate'] ?>" placeholder="">
														</div>
													</div>
												</div>

												<?phpif ($mode == 'Add' && $smode == '') { ?>
													<div class="col-md-4 quotation_detail" style="display: none;">
														<div class="form-group">
															<label class="col-md-4 control-label" style="white-space:nowrap">Choose Quotation</label>
															<div class="col-md-6 col-xs-11">
																<select class="select2" name="is_quotation" id="is_quotation" onChange="load_quotation_popup(this.value)" tabindex="18" <?= $disable ?>>
																	<option value="no" <?= ($rel['is_quotation'] == '0') ? 'selected' : '' ?>>No</option>
																	<option value="yes" <?= ($rel['is_quotation'] == '1') ? 'selected' : '' ?>>Yes</option>
																</select>
																<a id="quotation_link" href="#" onclick="load_quotation_popup('yes')" style="display: none;">Choose Quotation</a>
															</div>
														</div>
													</div>
												<?php} ?>
												<div class="col-md-12">
													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label">Transport</label>
															<div class="col-md-8 col-xs-11">
																<select class="form-control" name="transid" id="transid" onchange="load_trans_add();">
																	<?= gettransp($dbcon, $rel['transid']); ?>
																</select>
															</div>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label">Transport Address</label>
															<div class="col-md-8 col-xs-11">
																<select class="form-control" name="trans_add" id="trans_add">
																	<?php//=getpaymentterms($dbcon,$rel['payment_terms']);
																	?>
																</select>
																<input type="hidden" name="trans_add_ed" id="trans_add_ed" value="<?= $rel['trans_add'] ?>" />
															</div>
														</div>
													</div>
												</div>
												<?php if ($getspecialConfiguration['apson_special'] == 1) { ?>
													<div class="col-md-12">
														<div class="col-md-4">
															<div class="form-group">
																<label class="col-md-4 control-label">VALIDITY</label>
																<div class="col-md-8 col-xs-11">
																	<input id="apson_validity_date" name="apson_validity_date" type="text" class="form-control default-date-picker valid" title="VALIDITY" placeholder="VALIDITY" value="<?= $apson_validity_date ?>" placeholder="VALIDITY" autocomplete="off">

																</div>
															</div>
														</div>
														<div class="col-md-4">
															<div class="form-group">
																<label class="col-md-4 control-label">TRANSPORTATION IN SCOPE OF</label>
																<div class="col-md-8 col-xs-11">
																	<select class="form-control" name="apson_trans_scop_of" id="apson_trans_scop_of">
																		<option value="1" <?php if ($rel['apson_trans_scop_of'] == 1) {
																								echo "selected";
																							} else {
																								echo "";
																							} ?>>CUSTOMER</option>
																		<option value="2" <?php if ($rel['apson_trans_scop_of'] == 2) {
																								echo "selected";
																							} else {
																								echo "";
																							} ?>>Our Comapny</option>
																	</select>
																</div>
															</div>
														</div>
														<div class="col-md-4">
															<div class="form-group">
																<label class="col-md-4 control-label">DELIVERY</label>
																<div class="col-md-8 col-xs-11">
																	<select class="form-control" name="apson_dilivary_type" id="apson_dilivary_type">
																		<!--<option value="1" <?php if ($rel['apson_dilivary_type'] == 1) {
																									echo "selected";
																								} else {
																									echo "";
																								} ?> >DOOR DELIVERY</option>
																<option value="2" <?php if ($rel['apson_dilivary_type'] == 2) {
																						echo "selected";
																					} else {
																						echo "";
																					} ?> >GODOWN DELIVERY</option>-->
																		<?= getdlivarytype($dbcon, $rel['apson_dilivary_type']); ?>
																	</select>
																</div>
															</div>
														</div>
													</div>
												<?php} ?>


												<?php//if($getspecialConfiguration['power_drive']==1){ 
												?>
												<!-- <div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label" >Orange</label>
															<div class="col-md-6 col-xs-11">
																<input id="orange" name="orange" type="text" class="form-control"  value="<?php echo $rel['orange']; ?>" placeholder="Orange">
															</div>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label" >MFG.</label>
															<div class="col-md-6 col-xs-11">
																<input id="mfg" name="mfg" type="text" class="form-control" title="mfg" value="<?php echo $rel['mfg']; ?>" placeholder="MFG.">
															</div>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label" >Trading</label>
															<div class="col-md-6 col-xs-11">
																<input id="trading" name="trading" type="text" class="form-control" title="Trading" value="<?php echo $rel['trading']; ?>" placeholder="Trading">
															</div>
														</div>
													</div>


													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label" >Reparing</label>
															<div class="col-md-6 col-xs-11">
																<input id="repairing" name="repairing" type="text" class="form-control" title="Reparing" value="<?php echo $rel['repairing']; ?>" placeholder="Reparing">
															</div>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label" >Other</label>
															<div class="col-md-6 col-xs-11">
																<input id="other" name="other" type="text" class="form-control" title="Other" value="<?php echo $rel['other']; ?>" placeholder="Other">
															</div>
														</div>
													</div> -->
												<?php//}
												?>
											</div>
											
										
											<div class="col-md-12">
												<div class="card">
													<ul class="nav nav-tabs" id="my_tab_id" role="tablist">
														<li role="presentation" id="tab1" class="active"><a href="#product-details" aria-controls="product-details" role="tab" data-toggle="tab">Product Details</a></li>
														<li role="presentation" id="tab2"><a href="#product-desc" aria-controls="product-desc" role="tab" data-toggle="tab">Description</a></li>
														<li role="presentation" id="tab3" ><a href="#other-details" aria-controls="other-details" role="tab" data-toggle="tab">Other Details</a></li>
													
													</ul>
													<!-- Tab panes -->
										
													<div class="tab-content">
														<!-- Remaks Tab Start -->
														<div role="tabpanel" class="tab-pane active" id="product-details">
															<div class="col-md-12">
																<div class="form-group">
																	<table cellspacing="10" style="border-collapse:inherit;table-layout: fixed; " id="product_list" class="display table table12 table-striped table-bordered">
																		<?php 
																		$btn_search = "";
																		if ($getspecialConfiguration['power_drive'] == 1) {
																			$btn_search = '<button accesskey="n" class="btn btn-round btn-info btn-xs" type="button" data-toggle="modal" value="" onclick="show_product_search_modal();" data-tooltip="Add New Company"><i class="fa fa-search"></i> search</button>';
																		} ?>
																		<tr id="field">
																			<?phpif ($companyConfiguration['category_selection_active'] == 1) { ?>
																				<th width="8%" class="text-center">Category</th>
																			<?php} ?>
																			<th width="8%" class="text-center quotation_detail">Choose Quotation</th>
																			<th width="15%" class="text-center">Product Detail <?= $btn_search ?></th>
																			<?phpif ($getspecialConfiguration['reciclar'] == 1) { ?>
																				<th width="8%" class="text-center">Reciclare Category</th>
																			<?php} ?>
																			<th width="8%" class="text-center" style="<?= ($getspecialConfiguration['elcon_permission'] == 1) ? '' : 'display:none'; ?>;">Item Code</th>
																			<?phpif ($getspecialConfiguration['vipul_copper_permission'] == 1) { ?>
																				<th width="8%" class="text-center">HSN Code</th>
																				<th width="8%" class="text-center">Length</th>
																				<th width="8%" class="text-center">Pices</th>
																			<?php} else { ?>
																				<th width="8%" class="text-center" style="display: none;">HSN Code</th>
																			<?php} ?>
																			<th width="8%" class="text-center">Per</th>
																			<th width="6%" class="text-center">Quantity</th>
																			<th width="7%" class="text-center">Rate <span class="currency_icon"></span></th>
																			<th width="7%" class="text-center" style="display:none">Per</th>
																			<th width="6%">Discount <span class="currency_icon"></span></th>

																			<th width="9%" class="text-center">Amount <span class="currency_icon"></span></th>
																			<th width="7%" class="text-center">Priority</th>
																			<th width="5%" class="text-center"></th>
																		</tr>
																		<input type="hidden" value="1" name="fieldcnt" id="fieldcnt" />
																		<tr id="field1">
																			<?phpif ($companyConfiguration['category_selection_active'] == 1) { ?>
																				<td data-label="PRODUCT CATEGORY" style="vertical-align:top;">
																					<select class="select2" title="Select Category" name="product_category_id" id="product_category_id" <?phpif ($companyConfiguration['cat_wise_product_load'] == 1) { ?>onchange="quotation_wise_product_load()" <?php} else { ?>onchange="getProductByCategoryID(this.value)" <?php} ?>>
																						<?= get_all_category($dbcon, $rel['product_category_id']); ?>
																					</select>
																				</td>
																			<?php} ?>
																			<td class="quotation_detail" data-label="QUOTATION DETAIL" style="vertical-align:top;">
																				<select class="select2" title="Select Quotation" name="mquotation_id" id="mquotation_id" onChange="load_product_data(this.value)">
																				</select>
																			</td>
																			<td data-label="PRODUCT DETAIL" style="vertical-align:top;">
																				<?phpif ($getspecialConfiguration['vipul_copper_permission'] == 1) { ?>
																					<select class="select2" title="Select product" name="product_id" id="product_id" onChange="load_productdetail(this.value)">
																						<?= getproduct_typewise($dbcon, '', $type_conf, $sales_pro_search) ?>
																					</select><br>
																					<div id="die_master_product_name" style="display: none">
																						Die Master Name: <span id="die_master_name"></span>
																						<input type="hidden" id="die_product_id" name="die_product_id" class="form-control" />
																						<br>
																					</div>
																				<?php} else { ?>
																					<input id="product_id" name="product_id" style="width:100%;" placeholder="Select Product" onchange="load_product_dtls(this.value);" />
																				<?php} ?>
																				<br><strong class="hsncode" style="display:none;color:blue">HSN Code : <span id="hsncode"></span></strong>
																				<br><label id="current_stock" style="display: none;color:red"></label>
																				<br />
																				<strong class="taxtype" style="display:none;color:blue">TAX : <span id="taxtype"></span></strong>
																				<br><br>
																				<button type="button" id="projectItem" onclick="load_project_item()" title="View Project Wise Item List" class="btn btn-primary" style="display: none;">View Item List <i class="fa fa-plus"></i></button>&nbsp;&nbsp;&nbsp;
																				<button type="button" id="productHistory" onclick="load_product_history()" title="View Product History" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></button>&nbsp;&nbsp;&nbsp;
																				<?phpif ($getspecialConfiguration['oilfield_permission'] == 1) { ?>
																					<button accesskey="n" class="btn btn-round btn-info btn-xs" type="button" data-toggle="modal" onclick="showproduct()"><i class="fa fa-plus"></i> Add Product</button>
																				<?php} ?>
																			</td>
																			<?phpif ($getspecialConfiguration['reciclar'] == 1) { ?>
																				<td data-label="PRODUCT CATEGORY" style="vertical-align:top;">
																					<select class="select2" title="Select Category" name="parent_category_id" id="parent_category_id">
																						<?= get_all_reciclare_category($dbcon, $rel['product_category_id']); ?>
																					</select>
																				</td>
																			<?php} ?>
																			<td data-label="ITEM CODE" style="vertical-align:top; <?= ($getspecialConfiguration['elcon_permission'] == 1) ? '' : 'display:none'; ?>;">
																				<input type="text" title="Enter ITEM Code" placeholder="ITEM Code" id="product_item_code" name="product_item_code" class="form-control" />
																			</td>
																			<?phpif ($getspecialConfiguration['vipul_copper_permission'] == 1) { ?>
																				<td data-label="HSN CODE" style="vertical-align:top;">
																					<input type="text" title="Enter HSN Code" placeholder="HSN Code" id="product_hsn_code" name="product_hsn_code" class="form-control" />
																				</td>
																				<td data-label="Length" style="vertical-align:top;">
																					<input type="text" title="Enter Length" placeholder="Length" id="product_length" name="product_length" class="form-control" onChange="get_product_detail_calc(this.value,'')" />
																				</td>
																				<td data-label="Pices" style="vertical-align:top;">
																					<input type="text" title="Enter Pices" placeholder="Pices" id="product_pices" name="product_pices" class="form-control" onChange="get_product_detail_calc('',this.value)" />
																				</td>
																			<?php} else { ?>
																				<td data-label="HSN CODE" style="display: none;vertical-align:top;">
																					<input type="text" title="Enter HSN Code" placeholder="HSN Code" id="product_hsn_code" name="product_hsn_code" class="form-control" />
																				</td>
																			<?php} ?>
																			<td data-label="PER" style="vertical-align:top;">
																				<select class="form-control" title="Select Unit" placeholder="Unit" name="rate_unit_id" id="rate_unit_id" onchange="load_product_unit();getrate();">
																					<?php//=getunit($dbcon,0);
																					?>
																					<option value="0">Select Unit</option>
																				</select>
																			</td>

																			<td data-label="QUANTITY" style="vertical-align:top;">
																				<div id="convert_unit_block" style="display: none;">
																					<input type="number" title="Enter Qty" min="0" id="product_conv_qty" name="product_conv_qty" class="form-control" onkeyup="product_convert_qty(1);" />

																					<input type="hidden" name="conv_unitid" id="conv_unitid" value="" />

																					<input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />
																					<div style="display: none;">
																						<span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning  btn-xs" id="convert_unit_show"> </span>
																					</div>

																				</div>
																				<div id="base_unit_block" style="">
																					<input type="text" title="Enter Qty" min="0" id="product_qty" name="product_qty" class="form-control numbersOnly" onkeyup="product_convert_qty(2);calculate_special_total();" />

																					<input type="hidden" name="unitid" id="unitid" value="" />

																					<input type="hidden" id="product_qty_hide" name="product_qty_hide" value="" />
																					<div style="display: none;">
																						<span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs" id="unit_show"> </span>
																					</div>
																				</div>
																			</td>
																			<td data-label="RATE" style="vertical-align:top;">
																				<input type="text" title="Enter Rate" placeholder="Rate" min="0" id="product_rate" name="product_rate" onkeyup="get_amount();get_discount('per');" class="form-control numbersOnly" />
																				<br />
																				<strong class="pro_amt" style="display:none;color:green"> Product Rate : <span id="pro_amt"></span></strong>
																				<br />
																				<input type="hidden" id="taxper">
																				<strong class="taxrate" style="display:none;color:green"> Tax Rate : <span id="taxrate"></span></strong>
																			</td>
																			<td data-label="PER" style="vertical-align:top;display:none">
																				<select class="select2" title="Select Unit" placeholder="Unit" name="unit_id" id="unit_id" onchange="getrate();">
																					<?= getunit($dbcon, 0); ?>
																				</select>
																			</td>
																			<td data-label="DISCOUNT" style="vertical-align:top;">
																				<input type="number" title="Enter Discount" placeholder="Discount" min="0" id="product_discount" name="product_discount" onkeyup="get_discount('amt');" class="form-control numbersOnly" <?= $discount_editable ?> /><br />
																				<input type="number" title="Enter Discount Percentage" min="0" id="discount_per" name="discount_per" onkeyup="get_discount('per');" class="form-control numbersOnly" placeholder="in %" max="100" <?= $discount_editable ?> />
																			</td>
																			<td data-label="AMOUNT" style="vertical-align:top;">
																				<input type="number" min="0" id="product_amount" readonly="readonly" name="product_amount" placeholder="AMOUNT" class="form-control" onmouseover="this.title=this.value" />
																			</td>
																			<td>
																				<select class="select2" name="priority_status" id="priority_status">
																					<option value="Low">Low</option>
																					<option value="Medium">Medium</option>
																					<option value="High">High</option>
																				</select>
																			</td>
																			<td style="vertical-align:top;" rowspan="3">

																				<?php 
																				if ($getspecialConfiguration['smpl_permission'] == 1 || $getspecialConfiguration['chetak_permission'] == 1) {
																					$add_field = 'stock_allocate()';
																				} else {
																					$add_field = 'return add_field();';
																				}
																				?>
																				<?phpif ($getspecialConfiguration['durva_permission'] == 1) { ?>
																					<input type="button" name="addrow1" id="addrow1" onClick="open_batch_wise_qty()" class="btn btn-primary product_add_batch_wise" value="Add" />
																					<button type="button" class="btn btn-primary" id="addrow" style=" display:none;" onclick="add_field()">Add</button>
																				<?php} else { ?>
																					<input type="button" name="addrow" id="addrow" onClick="<?= $add_field ?>" class="btn btn-primary delivary_so_wise" value="Add" />
																				<?php} ?>


																				<input type="button" name="addrow" id="addrow" onClick="open_approv_quo1();load_unit_product();delivery_schedule()" class="btn btn-primary delivary_product_wise" value="Add" />
																			</td>
																			<input type='hidden' name='edit_id' id='edit_id' value='' />
																			<input type='hidden' name='pro_cal_type' id='pro_cal_type' value='' />
																			<input type='hidden' name='s_per' id='s_per' value='<?= $getspecialConfiguration['elcon_permission']; ?>' />
																		</tr>
																		<?phpif ($getspecialConfiguration['power_drive'] == 1) { ?>
																			<tr>
																				<th class="text-center">Orange</th>
																				<th class="text-center">MFG</th>
																				<th class="text-center" colspan="2">Trading</th>
																				<th class="text-center" colspan="2">Reparing</th>
																				<th class="text-center">Other</th>
																			</tr>
																			<tr>
																				<td><input id="orange" name="orange" type="text" class="form-control" placeholder="Orange" onkeyup="calculate_orange()"><br><input id="orange_total" name="orange_total" type="text" class="form-control" readonly placeholder="Orange Total"></td>
																				<td><input id="mfg" name="mfg" type="text" class="form-control" title="mfg" placeholder="MFG." onkeyup="calculate_mfg()"><br><input id="mfg_total" name="mfg_total" type="text" class="form-control" title="mfg Total" placeholder="MFG. Total" readonly></td>
																				<td colspan="2"><input id="trading" name="trading" type="text" class="form-control" title="Trading" placeholder="Trading" onkeyup="calculate_trading()"><br><input id="trading_total" name="trading_total" type="text" class="form-control" title="Trading Total" placeholder="Trading Total" readonly></td>
																				<td colspan="2"><input id="repairing" name="repairing" type="text" class="form-control" title="Reparing" placeholder="Reparing" onkeyup="calculate_repairing()"><br><input id="repairing_total" name="repairing_total" type="text" class="form-control" title="Reparing Total" placeholder="Reparing Total" readonly></td>
																				<td><input id="other" name="other" type="text" class="form-control" title="Other" placeholder="Other" onkeyup="calculate_other()"><br><input id="other_total" name="other_total" type="text" class="form-control" title="Other Total" placeholder="Other Total" readonly></td>
																			</tr>
																		<?php} ?>
																	</table>
																</div>
															</div>
														</div>
														<div class="tab-pane" id="product-desc">
															<div class="row">
																<div class="col-md-6">
																	<div class="form-group">
																		<label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Description</label>
																		<div class="col-md-12">
																			<textarea class="form-control" id="product_des" name="product_des" placeholder="Enter Product Description"><?= $rel['product_des'] ?></textarea>
																		</div>
																	</div>
																</div>
																<div class="col-md-6">
																	<div class="form-group">
																		<label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Specification</label>
																		<div class="col-md-12">
																			<textarea class="form-control" id="product_spec" name="product_spec" placeholder="Enter Product Specification"><?= $rel['product_spec'] ?></textarea>
																		</div>
																	</div>
																</div>
															</div>
														</div>
														
														<div role="tabpanel" class="tab-pane " id="other-details">
															<div class="col-md-12">
																<div class="form-group">
																	<?php
																		// if ($getspecialConfiguration['main_master'] == 1) {
																		$query_field = "select * from tbl_master_field where master_field_status=0 and company_id=" . $_SESSION['company_id'] . " order by priority ASC";
																		$res_field = $dbcon->query($query_field);
																		$ro_cnt = brp_mysqli_num_rows($res_field);
																		$fieldcnt = 1;
																		$counter = 1;
																		while ($row_field = brp_mysqli_fetch_array($res_field)) {
																			$field_name = $row_field['master_field_db_name'];	
																			$field = $row_field['master_field_id'];	
																			if ($fieldcnt == 1) { ?>
																				<div class="col-md-12 margin_row">
																				<div class="row">
																				<?php} ?>
																				<div class="col-md-4">
																				<input type="hidden" name="fid" data-id="<?=$field_name;?>" class="dy_fields[<?=$field?>]" id="fid" value="<?=$field?>">
																					<div class="form-group">
																						<label class="col-md-4 control-label"><?= $row_field['master_field'] ?>*</label>
																						<div class="col-md-8 col-xs-11">
																							<select class="select2 dynamic_field" name="<?= $row_field['master_field_db_name'] ?>" id="field_id_<?= $field ?>" title="<?= $row_field['master_field'] ?>" >
																							
																								<option value="" data-pcode="">--CHOOSE <?= $row_field['master_field'] ?>--</option>
																								<?= get_master_field_value($dbcon, $rel_field[$field_name], $row_field['master_field_id']) ?>
																							</select>
																						</div>
																					</div>
																				</div>
																				<?phpif ($ro_cnt == $fieldcnt) { ?>
																				</div>
																				<?php} else {
																					if ($counter == 3) {
																						$counter = 0;
																				?>
																						</div>
																						<div class="col-md-12 margin_row">
																					<?php}
																				} ?>

																			<?php$fieldcnt++;
																			$counter++;
																		}
																	?>
                        											<input type="hidden" name="dynamic_field" id="dynamic_field" value="<?= (int)$field - 1 ?>">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<?phpif ($getspecialConfiguration['vipul_copper_permission'] == 1) { ?>
												<div id="sale_productdata"></div>
											<?php} else { ?>
												<div id="sale_productdata_salesorder"></div>
											<?php} ?>
											<div class="col-md-7">
												<div class="card">
													<ul class="nav nav-tabs" id="my_tab_id" role="tablist">
														<li role="presentation" id="tab2" class="active"><a href="#remark-section" aria-controls="remark-section" role="tab" data-toggle="tab">Remark</a></li>
														<li role="presentation" id="tab1"><a href="#terms-section" aria-controls="terms-section" role="tab" data-toggle="tab">Terms & Condition</a></li>
														<li role="presentation" id="tab3"><a href="#podoc-section" aria-controls="terms-section" role="tab" data-toggle="tab">PO Document</a></li>

														<?phpif ($getspecialConfiguration['apson_special'] == 1) { ?>
															<li role="presentation" id="tab4"><a href="#general-terms-condition-section" aria-controls="general-terms-condition-section" role="tab" data-toggle="tab">General Terms
																	& Conditions</a></li>
														<?php} ?>
														<?phpif ($getspecialConfiguration['apson_special'] == 1) { ?>
															<li role="presentation" id="tab4"><a href="#general-address-condition-section" aria-controls="general-address-condition-section" role="tab" data-toggle="tab">Shiping Address</a></li>
														<?php} ?>


													</ul>
													<div class="tab-content">
														<div role="tabpanel" class="tab-pane active" id="remark-section">
															<div class="form-group" style="margin-top:20px;">
																<div class="col-md-6 tax_details"></div>
															</div>
															<div class="form-group" style="margin-top:20px;">
																<label class="col-md-2 control-label">Remarks </label>
																<div class="col-md-6 col-xs-12">
																	<textarea id="remark" name="remark" placeholder="Remarks" class="form-control" rows="3"><?= $rel['remark'] ?></textarea>
																</div>
															</div>
														</div>
														<div role="tabpanel" class="tab-pane" id="terms-section">
															<div class="form-group" style="margin-top:20px;">
																<label class="col-md-3 control-label" style="white-space:nowrap;">Terms & condition</label>
																<div class="col-md-10" style="margin-top:30px">
																	<div class="col-md-3">
																		<div class="form-group">
																			<input type="radio" class="" name="terms_type" id="common_terms" value="0" onchange="get_quotation_data_so();" <?phpif ($rel['terms_type'] == '0') {
																																																echo 'checked="checked"';
																																															} else {
																																																if ($mode == 'Add') {
																																																	echo 'checked="checked"';
																																																}
																																															} ?>> Common Terms
																		</div>
																	</div>

																	<div class="col-md-3">
																		<div class="form-group">
																			<input type="radio" class="" name="terms_type" id="party_terms" value="1" onchange="get_quotation_data_so();" <?phpif ($rel['terms_type'] == '1') {
																																																echo 'checked="checked"';
																																															} ?>> Party Wise
																		</div>
																	</div>

																	<div class="col-md-3">
																		<div class="form-group">
																			<input type="radio" class="" name="terms_type" id="quotation_terms" value="2" onchange="get_quotation_data_so();" <?phpif ($rel['terms_type'] == '2') {
																																																	echo 'checked="checked"';
																																																} ?>> Quotation Wise
																		</div>
																	</div>

																	<div class="col-md-3">
																		<div class="form-group">
																			<input type="radio" class="" name="terms_type" id="multi_condition" value="3" onchange="get_quotation_data_so();" <?phpif ($rel['terms_type'] == '3') {
																																																	echo 'checked="checked"';
																																																} ?>> Multi Condition
																		</div>
																	</div>

																	<div class="col-md-4" id="quot_wise_term" style="display: none;">
																		<div class="form-group">
																			<select class="select2" name="term_quotation_id" id="term_quotation_id" onchange="load_typeswise_terms()">
																				<option value=""> Choose Quotation</option>
																			</select>
																		</div>
																	</div>
																	<div class="form-group" style="margin-top:20px;" id="quot_terms_cond_div">
																	</div>
																</div>
															</div>
														</div>
														<div role="tabpanel" class="tab-pane" id="podoc-section">
															<div class="form-group" style="margin-top:20px;">
																<table class="display table table-bordered table-striped">
																	<thead>
																		<tr>
																			<th width="20%" class="text-center">Design Dept.</th>
																			<th width="30%" class="text-center">Document Name</th>
																			<th width="30%" class="text-center">Upload Image</th>
																			<th width="10%" class="text-center">Action</th>
																		</tr>
																	</thead>
																	<tbody>
																		<tr>
																			<td>
																				<select class="select2" name="design_dept" id="design_dept">
																					<option value="0">No</option>
																					<option value="1">Yes</option>
																				</select>
																			</td>
																			<td><input type="text" class="form-control" id="doc_name" name="doc_name" placeholder="Document Name"></td>
																			<td><input type="file" class="form-control" id="doc_attach" name="doc_attach"></td>
																			<td><button type="button" class="btn btn-primary" id="dfd_attch_btn" onclick="add_document_attach()">Add</button></td>
																		</tr>
																	</tbody>
																</table>
															</div>
															<div class="form-group" style="margin-top:20px;" id="po_doc_list"></div>
														</div>
														<?phpif ($getspecialConfiguration['apson_special'] == 1) { ?>
															<div role="tabpanel" class="tab-pane" id="general-terms-condition-section">
																<div class="col-md-12">
																	<div class="form-group">
																		<label class="col-md-12 control-label text-center" style="text-align:left;font-weight:bold;">General Terms & Conditions Content</label>
																		<div class="col-md-12">
																			<textarea id="quot_general_terms_condition_content" name="quot_general_terms_condition_content" class="form-control"><?= ($rel['quot_general_terms_condition_content']) ? $rel['quot_general_terms_condition_content'] : $general_terms_condition ?></textarea>
																		</div>
																	</div>
																</div>
															</div>
														<?php} ?>
														<?phpif ($getspecialConfiguration['apson_special'] == 1) { ?>
															<div role="tabpanel" class="tab-pane" id="general-address-condition-section">
																<div class="col-md-12">
																	<div class="form-group">
																		<div class="col-md-12">
																			<textarea id="ship_address" name="ship_address" class="form-control"><?= ($rel['ship_address']) ? $rel['ship_address'] : '' ?></textarea>
																		</div>
																	</div>
																</div>
															</div>
														<?php} ?>
													</div>
												</div>
											</div>
											<div class="col-md-5">
												<!-- Dimple Panchal : start -->
												<?php // $tcs_applicable = $dbcon->query("SELECT tcs_applicable FROM tbl_finance_setting as comp WHERE company_id=".$_SESSION['company_id'])->fetch_object()->tcs_applicable; 
												//  if($tcs_applicable) {
												?>

												<div class="form-group">
													<label class="col-md-3 control-label">Total <span class="currency_icon"></span></label>
													<div class="col-md-6 col-xs-12">
														<input id="total" name="total" type="text" readonly="readonly" class="form-control" title="Grand Total" max="0" value="<?phpif ($mode == "Add") {
																																													echo '0';
																																												} else if ($mode == 'Edit') {
																																													echo indian_number($e_total, 2);
																																												} ?>" placeholder="total">
													</div>
												</div>

												<div class="invoiceTotalTax"></div>
												<div class="sundryadded">

												</div>

												<!-- Dimple Panchal : end -->
												<div class="form-group">
													<label class="col-md-3 control-label">Grand Total * <span class="currency_icon"></span></label>
													<div class="col-md-6 col-xs-12">
														<input id="g_total" name="g_total" type="text" class="form-control" title="dispatch_no" value="<?php?>" placeholder="Grand Total" readonly="readonly">
													</div>
												</div>

												<div class="form-group">
													<label class="col-md-3 control-label">Select Bill Sundry <span class="currency_icon"></span></label>
													<div class="col-md-3">
														<?php $get_bill_sundry = get_bill_sundry_ledger($dbcon, 0); ?>
														<select class="form-control" name="bill_sundry" id="bill_sundry" onchange="get_sundry_label(this.value)">
															<option value="0">Select</option>
															<?php foreach ($get_bill_sundry as $sundry) {

															?>
																<option value="<?php echo $sundry['l_id'] ?>"><?php echo $sundry['l_name']; ?></option>

															<?php } ?>
														</select>
													</div>
													<div class="col-md-2">
														<input id="bill_sundry_amount" name="bill_sundry_amount" type="text" class="form-control numbersOnly" placeholder="Amount" title="Amount" value="<?= $rel['amount'] ?>" placeholder="">
													</div>
													<div class="col-md-2">
														<button style="margin-top: 5px;" class="btn btn-round btn-info btn-xs" type="button" value="R1" onclick="addBillSundry()"><i class="fa fa-plus"></i></button>
													</div>
												</div>
												<?phpif ($getspecialConfiguration['smpl_permission'] == '1') { ?>
													<div class="form-group">
														<label class="col-md-3 control-label">Advance Payment * <span class="currency_icon"></span></label>
														<div class="col-md-6 col-xs-11">
															<input id="advance_payment" name="advance_payment" type="text" class="form-control numbersOnly" onkeyup="get_gtotal()" onchange="get_gtotal()" title="Advance Amount" value="<?= (($rel['currency_id'] == $_SESSION['currency_id']) ? $rel['advance_payment'] : $rel['advance_payment_conv']) ?>" placeholder="Advance Payment">
														</div>
													</div>

													<div class="form-group">
														<label class="col-md-3 control-label">Payable Amt * <span class="currency_icon"></span></label>
														<div class="col-md-2 col-xs-11">
															<input id="adv_per" name="adv_per" type="text" class="form-control numbersOnly" title="Enter Valid Value" onkeyup="get_advance('per');" value="<?= $rel['payable_per'] ?>" placeholder="in (%)" max="100">
														</div>
														<div class="col-md-4 col-xs-11">
															<input id="adv_amt" name="adv_amt" type="text" class="form-control numbersOnly" title="Enter Valid Value" onkeyup="get_advance('amt');" value="<?= (($rel['currency_id'] == $_SESSION['currency_id']) ? $rel['payable_amt'] : $rel['payable_amt_conv']) ?>" placeholder="in Rs.">
														</div>
													</div>

													<div class="form-group">
														<label class="col-md-3 control-label">Pending Amount * <span class="currency_icon"></span></label>
														<div class="col-md-6 col-xs-11">
															<span id="pending_amount" style="color:red;font-weight: bold;"><?= $rel['pending_amt'] ?></span>
															<input type="hidden" name="pen_amt" id="pen_amt" value="<?= $rel['pending_amt'] ?>">
														</div>
													</div>
												<?php} ?>
											</div>
											<div class="col-md-12">
												<center>
													<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
													<button type="button" onclick="invoice_submit();" class="btn btn-success" id="saveprint" name="saveprint">Save and Print</button> &nbsp;
													<a href="<?= ROOT . CRM_ROOT . 'sales_order_list' ?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>
										</div>
										<input type='hidden' name='mode' id='mode' value='<?= $mode ?>' />
										<input type='hidden' name='smode' id='smode' value='<?= $smode ?>' />
										<input type='hidden' name='quotaion_id' id='quotaion_id' value='<?= $quotaion_id ?>' />
										<input type='hidden' name='with_out_stock_invoice' id='with_out_stock_invoice' value='<?= $with_out_stock_invoice ?>' />

										<input type='hidden' name='eid' id='eid' value='<?phpif ($mode == "Edit") {
																							echo $sales_order_id;
																						} ?>' />
										<!-- <input type='hidden' name='invoicetype_id' id='invoicetype_id' value='<?phpif ($mode != "Add") {
																														echo $rel['sales_order_id'];
																													} ?>' /> -->
										<input type='hidden' name='save_print' id='save_print' value='' />
										<input type='hidden' name='receipt_no' id='receipt_no' value='<?= $receiptno ?>' />
										<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />
										<input type='hidden' name='old_product_id' id='old_product_id' value='' />
										<input type='hidden' name='salesorder_trn_id' id='salesorder_trn_id' value='' />
										<input type='hidden' name='project_inquiry_id' id='project_inquiry_id' value='<?= $inquiry_id ?>' />
										<input type='hidden' name='pro_type' id='pro_type' value='<?= $so_pro_type ?>' />
										<input type='hidden' name='pro_search' id='pro_search' value='<?= $sales_pro_search ?>' />
										<input type='hidden' name='start_sales_order_id' id='start_sales_order_id' value='<?= $start_sales_order_id; ?>' />
										<input type='hidden' name='prev_sales_order_id' id='prev_sales_order_id' value='<?= $sales_order_id; ?>' />
										<input type='hidden' name='revise_status' id='revise_status' value='<?= $revise_status; ?>' />

										<input type="hidden" name="company_cost_center" id="company_cost_center" value="<?= $companyConfiguration['enable_cost_center'] ?>" />

										<input type="hidden" name="company_salesman" id="company_salesman" value="<?= $companyConfiguration['enable_salesman'] ?>" />

										<input type="hidden" name="company_tcs" id="company_tcs" value="<?= $companyConfiguration['enable_tcs_reporting'] ?>" />

										<input type="hidden" name="company_eway" id="company_eway" value="<?= $companyConfiguration['enable_eway_bill'] ?>" />

										<input type="hidden" name="company_trans" id="company_trans" value="<?= $companyConfiguration['enable_transport'] ?>" />
										<input type="hidden" name="po_document_required" id="po_document_required" value="<?= $companyConfiguration['po_document_required']; ?>" />
										<input type="hidden" name="po_document_count" id="po_document_count" value="<?= $count_s; ?>" />
										<input type="hidden" name="print_path" id="print_path" value="<?= get_print_path($dbcon, '3'); ?>" />
										<input type="hidden" name="vipul_copper_permission" id="vipul_copper_permission" value="<?= $getspecialConfiguration['vipul_copper_permission']; ?>" />
										<input type="hidden" name="atlas_permission" id="atlas_permission" value="<?= $getspecialConfiguration['atlas_permission']; ?>" />
										<!-- Transport Details -->

										<input type="hidden" name="transport_voucher" id="transport_voucher" value="<?= SO_VOUCHER ?>" placeholder="Voucher Type eg. sale , purchase">
										<input type="hidden" name="transport_transaction_table" id="transport_transaction_table" placeholder="table name of sale , purchase , payment.." value="tbl_sales_order">
										<input type="hidden" name="transport_transaction_table_id" id="transport_transaction_table_id" placeholder="primary key of that inserted table " value="<?= $mode == 'Edit' ? $rel['sales_order_id'] : '0' ?>">
										<input type="hidden" id="edit_id_transport" value="<?= $mode == 'Edit' ? $rel['sales_order_id'] : '0' ?>" />

								</form>
							</div>
						</section>
					</div>
				</div>
			</section>
		</section>
		<?php include_once('../include/add_quotation.php'); ?>
		<?php include_once('../include/add_cust_comp.php'); ?>
		<!--<php include_once('../include/add_cust.php'); ?>-->
		<?php include_once('../include/add_consignee.php'); ?>
		<?php include_once('../include/stock_allocation_modal.php'); ?>
		<?php include_once('../include/add_so_dispatch_date.php'); ?>
		<?php include_once('../include/add_accessories_product.php'); ?>
		<?php include_once('../include/add_accessories_product_list.php'); ?>
		<?php include_once('../../finance/include/add_ledger.php'); ?>

		<?php include_once('../../finance/include/add_eway_bill.php'); ?>
		<?php include_once('../../administration/include/add_product.php'); ?>
		<?php include_once('../../administration/include/add_hsn_in_popup.php'); ?>
		<?php include_once('../include/view_delivery_detail.php'); ?>
		<?php include_once('../include/preview_cust_dtls.php'); ?>
		<?php include_once('../include/preview_product_history.php'); ?>
		<?php include_once('../include/add_project_wise_item.php'); ?>


		<?php include_once($incPath . 'footer.php'); ?>
	</section>
	<?php include_once($incPath . 'include_js_file.php'); ?>
	<?php include_once($incPath . 'product_filter_search_modal.php'); ?>
	<script src="<?= ROOT . CRM_ROOT ?>js/app/sales_order.js?<?= time() ?>"></script>
	<script src="<?= ROOT . CRM_ROOT ?>js/app/customer.js?<?= time() ?>"></script>
	<script src="<?= ROOT . FINANCE_ROOT ?>js/app/add_ledger_js.js?<?= time() ?>"></script>
	<script src="<?= ROOT . ADMINISTRATION_ROOT ?>js/app/ledger.js?<?= time() ?>"></script>
	<script src="<?= ROOT . ADMINISTRATION_ROOT ?>js/app/consignee.js?<?= time() ?>"></script>
	<script src="<?= ROOT ?><?= FINANCE_ROOT ?>js/app/common_form_finance.js?<?= time() ?>"></script>
	<script src="<?= ROOT . ADMINISTRATION_ROOT ?>js/app/product_mst.js?<?php echo time(); ?>"></script>
	<script src="<?= ROOT . ADMINISTRATION_ROOT ?>js/app/hsn_master.js?<?php echo time(); ?>"></script>
	<script>
		<?phpif ($mode == 'Edit') { ?>
			load_typeswise_terms(<?= $sales_order_id ?>);
			get_symbol();
			get_statecode(<?= $rel['cust_id'] ?>);
			get_all_bill_sundry(<?= $sales_order_id ?>);
			get_grossbalance(<?= $rel['cust_id'] ?>);
			load_trans_add(<?= $rel['transid'] ?>)
		<?php} else { ?>
			load_typeswise_terms('');
			get_symbol();
		<?php} ?>
		//CKEDITOR.replace('quotation_condition');
		$(".selproduct").select2({
			width: '100%',
			// minimumInputLength: 2,
		});
		$(".select2").select2({
			width: '100%'
		});
		$('.default-date-picker').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});
		$('.default_date').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true,
			startDate: '<?php echo date("d-m-Y", strtotime($financial_year['financial_start_date'])) ?>',
			endDate: '<?php echo date("d-m-Y", strtotime($financial_year['financial_end_date'])) ?>',

		});
	</script>

	<?php 
	if ($smode == 'sales_order_emend') {
		echo "<script>copy_prev_so_trn('" . $sales_order_id . "');get_revise_so_no('" . $sales_order_id . "','" . $start_sales_order_id . "');get_symbol();get_statecode('" . $rel['cust_id'] . "');get_all_bill_sundry('" . $sales_order_id . "');quotation_dropdown_data();get_grossbalance('" . $rel['cust_id'] . "');</script>";
	}
	if ($mode == "Add" && ($smode == '' || $smode == "quotation_mode")) {
		echo "<script>load_consignee_detail();quotation_dropdown_data();</script>";
	}
	echo "<script>show_data();load_consignee_detail();</script>";

	if ($smode == "quotation_mode") {
		echo "<script>load_quotation_details('" . $quotaion_id . "');get_statecodes(" . $rel_q['cust_id'] . ");get_all_bill_sundry('" . $quotaion_id . "');</script>";
	}

	if (($rel['so_quotation_type'] == 1) && ($rel['terms_type'] == 2)) {
		echo "<script>get_quotation_data_so()</script>";
	}
	echo "<script>load_consinee_state(" . $countryid . ",'state_consinee_id'," . $stateid . ")</script>";
	echo "<script>load_consinee_city(" . $stateid . ",'city_consinee_id'," . $cityid . ")</script>";
	?>

	<script>
		CKEDITOR.replace('product_des', {
			enterMode: CKEDITOR.ENTER_BR
		});
		CKEDITOR.replace('product_spec', {
			enterMode: CKEDITOR.ENTER_BR
		});
		<?phpif ($getspecialConfiguration['apson_special'] == 1) { ?>
			CKEDITOR.replace('quot_general_terms_condition_content', {
				enterMode: CKEDITOR.ENTER_BR
			});
		<?php} ?>
		<?phpif ($getspecialConfiguration['apson_special'] == 1) { ?>
			CKEDITOR.replace('ship_address', {
				enterMode: CKEDITOR.ENTER_BR
			});
		<?php} ?>
	</script>
	

</body>

</html>