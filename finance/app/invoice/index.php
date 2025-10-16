<?php
session_start();
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path . "config/config.php");
include($path . "config/session.php");
include($include . "function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH . "common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH . "common_sub_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH . "finance_common_functions.php");
//Ankit Sompura 09-01-2021
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	FINANCE_INVOICE_RECEIPT,
	FINANCE_INVOICE_CHALAN,
	FINANCE_INVOICE_EDIT,
	FINANCE_INVOICE_DELETE,
	FINANCE_USER_UPDATE
]);
if ($_POST != NULL) {
	$POST = bulk_filter($dbcon, $_POST);
} else {
	$POST = bulk_filter($dbcon, $_GET);
}

if (strtolower($POST['mode']) == "fetch") {
	$s_date = explode(' - ', $POST['date']);
	$_SESSION['start'] = $s_date[0];
	$_SESSION['end'] = $s_date[1];
	$branch_id = $POST['branch_id'];

	//branch , company, user check start - dhaval 
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	$where_db = check_branch('invoice', $branch_id);

	$where .= " $where_db";

	$where_company = check_company('invoice');

	$where .= " $where_company";

	$where_user = check_user('invoice');

	//$where.=" $where_user";

	// branch , comapny , user check end - dhaval

	//check_user('invoice')
	$companyConfiguration = getCompanyConfiguration($dbcon);
	if (!empty($POST['type_id'])) {
		$where .= " and invoice.invoicetype_id=" . $POST['type_id'];
	}
	$where .= "  and invoice.invoice_date >= '" . date('Y-m-d', strtotime($s_date[0])) . "' AND invoice.invoice_date <= '" . date('Y-m-d', strtotime($s_date[1])) . "'";
	$appData = array();
	$i = 1;
	$aColumns = array('invoice.invoice_id', 'trn.product_spec', 'trn.description', 'invoice.invoice_no', 'cust.l_name', 'invoice.invoice_date', 'invoice.g_total', 'invoice.order_date', 'invoice.order_no', 'users.user_name', 'invoicetype.invoice_type', 'invoice.paid_amount', 'invoice.invoice_status', 'invoice.cdate', 'invoice.user_id', 'invoice.usertype_id', 'invoice.invoicetype_id', 'invoice.gst_flag', 'invoice.approve_status', 'cust.cust_mobile', 'invoice.eway_bill_no', 'invoice.einv_Irn', 'invoice.basic_total');
	$sIndexColumn = "invoice.invoice_id";
	$isWhere = array("invoice_status = 0 " . $where);
	$sTable = "tbl_invoice as invoice";
	$isJOIN = array(
		'left join tbl_ledger cust on invoice.cust_id=cust.l_id',
		'left join tbl_invoicetype invoicetype on invoice.invoicetype_id=invoicetype.invoicetype_id',
		'left join tbl_invoicetrn as trn on trn.invoice_id=invoice.invoice_id',
		'left join users as users on users.user_id=invoice.user_id'
	);
	$hGroupby = array("invoice.invoice_id");
	$hOrder = "invoice.invoice_id desc";
	include($path . 'include/pagging.php');
	$appData = array();
	$id = 1;
	foreach ($sqlReturn as $row) {
		$row_data = array();
		$eway_bill ='';
        $einv_bill = '';
		if (in_array(FINANCE_INVOICE_EDIT, $bulkAccessArray)) {
			$row_data[] = $id;
			$row_data[] = '<a class="" data-original-title="Edit ' . $row["invoice_no"] . '" data-toggle="tooltip" data-placement="top" href="' . ROOT . FINANCE_ROOT . 'invoiceedit/' . $row['invoice_id'] . '">' . $row["invoice_no"] . '</a>';
			$row_data[] = '<a class="" data-original-title="Edit ' . $row["invoice_no"] . '" data-toggle="tooltip" data-placement="top" href="' . ROOT . FINANCE_ROOT . 'invoiceedit/' . $row['invoice_id'] . '">' . date('d M, Y', strtotime($row["invoice_date"])) . '</a>';
			if ($getspecialConfiguration['power_drive'] == 1) {
				$row_data[] = $row["product_spec"];
				$row_data[] = $row["description"];
			}
			$row_data[] = '<a class="" data-original-title="Edit ' . $row["invoice_no"] . '" data-toggle="tooltip" data-placement="top" href="' . ROOT . FINANCE_ROOT . 'invoiceedit/' . $row['invoice_id'] . '">' . $row["l_name"] . '</a>';
			$row_data[] = $row["g_total"];
			$row_data[] = $row["basic_total"];
			/*$row_data[] = $row["order_no"];
			$row_data[] = date('d-m-Y',strtotime($row["order_date"]));*/
			$row_data[] = '<a class="" data-original-title="Edit ' . $row["invoice_no"] . '" data-toggle="tooltip" data-placement="top" href="' . ROOT . FINANCE_ROOT . 'invoiceedit/' . $row['invoice_id'] . '">' . $row["user_name"] . '</a>';
		} else {
			$row_data[] = $id;
			$row_data[] = $row["invoice_no"];
			$row_data[] = date('d M, Y', strtotime($row["invoice_date"]));
			if ($getspecialConfiguration['power_drive'] == 1) {
				$row_data[] = $row["product_spec"];
				$row_data[] = $row["description"];
			}
			$row_data[] = $row["l_name"];
			$row_data[] = $row["g_total"];
			$row_data[] = $row["basic_total"];
			/*$row_data[] = $row["order_no"];
			$row_data[] = date('d-m-Y',strtotime($row["order_date"]));*/
			$row_data[] = $row["user_name"];
		}


		$addpayment = '';
		$delete = '';
		$edit = '';
		$invoice_chalan = '';
		$print = '';
		$delivery_challan = '';
		$whatsapp = '';
		$ewayprint = '';
		if ($row["g_total"] > $row["paid_amount"]) {
			//$addpayment='<a class="btn btn-xs btn-primary" data-original-title="Payable '.($row['g_total']-$row['paid_amount']).' Rs." data-toggle="tooltip" data-placement="top" href="invoicepaymentmode/'.$row['invoice_id'].'"><i class="fa fa-plus"></i></a>';

		}

		if ($_SESSION['user_type'] != 2) {
			//if($_SESSION['user_id']==$row['user_id']){
			if (in_array(FINANCE_INVOICE_DELETE, $bulkAccessArray)  && $row['approve_status'] != '1') {
				$delete = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_invoice(' . $row['invoice_id'] . ')"><i class="fa fa-trash-o"></i></button>';
			}
			if (in_array(FINANCE_INVOICE_EDIT, $bulkAccessArray)  && $row['approve_status'] != '1') {
				$edit = '<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . FINANCE_ROOT . 'invoiceedit/' . $row['invoice_id'] . '"><i class="fa fa-pencil"></i></a>';
			}

			// }else{
			// 	$delete='';
			// 	$edit='';

			// }
		} else {
			if (in_array(FINANCE_INVOICE_DELETE, $bulkAccessArray)  && $row['approve_status'] != '1') {
				$delete = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_invoice(' . $row['invoice_id'] . ')"><i class="fa fa-trash-o"></i></button>';
			}
			if (in_array(FINANCE_INVOICE_EDIT, $bulkAccessArray)   && $row['approve_status'] != '1') {
				$edit = '<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . FINANCE_ROOT . 'invoiceedit/' . $row['invoice_id'] . '"><i class="fa fa-pencil"></i></a>';
			}
		}
		$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='" . $_SESSION['company_id'] . "'");
		$rels = mysqli_fetch_assoc($menusql);
		$menu_show_permissions = explode(",", $rels['print_permission']);
		if (in_array(FINANCE_INVOICE_RECEIPT, $bulkAccessArray)) {
			$sql = $dbcon->query("SELECT * FROM print_setup_mst WHERE print_type =7 AND approve_status = 1 AND status = 0 ORDER BY priority");
			while ($res = mysqli_fetch_assoc($sql)) {
				if (in_array($res['id'], $menu_show_permissions)) {
					if ($res['with_out_logo'] == 0) {
						$print .= '<a class="btn btn-xs btn-primary" data-original-title="' . $res['print_name'] . '" data-toggle="tooltip" data-placement="top" target="_blank"  href="' . ROOT . PRINT_ROOT . $res['page_path'] . '/' . $row['invoice_id'] . '" style="background: ' . $res['icon_color'] . '; border-color: ' . $res['icon_color'] . ';"><i class="' . $res['fa_icon'] . '"></i></a>&nbsp;';
					} else {
						$ddf = "'" . DOMAIN_F . PRINT_ROOT . $res['page_path'] . "/" . $row['invoice_id'] . "'";

						$print .= '<a class="btn btn-xs btn-primary" data-original-title="' . $res['print_name'] . '" data-toggle="tooltip" data-placement="top" target="_blank" onClick="open_print(' . $ddf . ')"  style="background: ' . $res['icon_color'] . '; border-color: ' . $res['icon_color'] . ';"><i class="' . $res['fa_icon'] . '"></i></a> ';
					}
				}
			}
		}

		if (in_array(FINANCE_INVOICE_CHALAN, $bulkAccessArray)) {
			$sqls = $dbcon->query("SELECT * FROM print_setup_mst WHERE print_type =6 AND approve_status = 1 AND status = 0 ORDER BY priority");
			while ($ress = mysqli_fetch_assoc($sqls)) {
				if (in_array($ress['id'], $menu_show_permissions)) {
					$invoice_chalan .= '<a class="btn btn-xs btn-primary" data-original-title="' . $ress['print_name'] . '" data-toggle="tooltip" data-placement="top" target="_blank"  href="' . ROOT . PRINT_ROOT . $ress['page_path'] . '/' . $row['invoice_id'] . '" style="background: ' . $ress['icon_color'] . '; border-color: ' . $ress['icon_color'] . ';"><i class="' . $ress['fa_icon'] . '"></i></a>&nbsp;';
				}
			}
		}
		$sqldel = $dbcon->query("SELECT * FROM print_setup_mst WHERE print_type =12 AND approve_status = 1 AND status = 0 ORDER BY priority");
		while ($resdel = mysqli_fetch_assoc($sqldel)) {
			if (in_array($resdel['id'], $menu_show_permissions)) {
				$delivery_challan .= '<a class="btn btn-xs btn-primary" data-original-title="' . $resdel['print_name'] . '" data-toggle="tooltip" data-placement="top" target="_blank"  href="' . ROOT . PRINT_ROOT . $resdel['page_path'] . '/' . $row['invoice_id'] . '" style="background: ' . $resdel['icon_color'] . '; border-color: ' . $resdel['icon_color'] . ';"><i class="' . $resdel['fa_icon'] . '"></i></a>&nbsp;';
			}
		}
		$key = "encryptionkey";
		$text = $row['invoice_id'];
		$encrypted = bin2hex(openssl_encrypt($text, 'AES-128-CBC', $key));
		$whatsapp .= '<a title="Send to Whatsapp" type="button" class="btn btn-xs btn-success" href="https://web.whatsapp.com/send?phone=+91' . $row['cust_mobile'] . '&text=' . $rel['company_name'] . 'Thank you for your purchase.%0aInvoice No:-' . $row['invoice_no'] . '%0aDate:- ' . date('d-m-Y', strtotime($row['invoice_date'])) . '%0aAmount:- ' . $row['g_total'] . '%0aBest Regards%0a ' . DOMAIN . PRINT_ROOT . $res['page_path'] . 'linkinvoicereceipt/' . $encrypted . '" target="_blank"> <i class="fa fa-whatsapp"></i></a>&nbsp;';

		if ($companyConfiguration['enable_eway_bill'] == 1) {
			if (empty($row['eway_bill_no'])) {
				$eway_bill = '<button class="btn btn-xs btn-danger" data-original-title="E-Way Bill" data-toggle="tooltip" data-placement="top" onClick="create_eway_bill(' . $row['invoice_id'] . ')">E-Way Bill</button>';
			}
		}

		if ($companyConfiguration['enable_einvoice'] == 1) {
			if (empty($row['einv_Irn'])) {
				$einv_bill = '<button class="btn btn-xs btn-info" data-original-title="E-Invoice" data-toggle="tooltip" data-placement="top" onClick="create_einv_bill(' . $row['invoice_id'] . ')">E-Invoice</button>';
			}
		}

		if (in_array(FINANCE_USER_UPDATE, $bulkAccessArray)) {
			$update_user = '<button class="btn btn-xs btn-success" data-original-title="Update User" data-toggle="tooltip" style="background-color:#f17438 !important;border-color:#f17438 !important" data-placement="top" onClick="preview_update_user(' . $row['invoice_id'] . ',\'' . $row["invoice_no"] . '\',\'' . $row['user_id'] . '\')"><i class="fa fa-user-o" aria-hidden="true"></i>	</button>';
		}

		$ewayprint = '<a class="btn btn-xs btn-primary" data-original-title="' . $ress['print_name'] . '" data-toggle="tooltip" data-placement="top" target="_blank"  href="' . ROOT . PRINT_ROOT . 'ewayprint/' . $row['invoice_id'] . '" ><i class="fa fa-truck"></i></a>';

		$row_data[] = $print . '&nbsp;' . $invoice_chalan . '&nbsp;' . $edit . '&nbsp;' . $delete . '&nbsp;' . $addpayment . '&nbsp;' . $delivery_challan . '&nbsp;' . $whatsapp . ' ' . $einv_bill . ' ' . $eway_bill . ' ' . $update_user . ' ' . $ewayprint;


		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode($output);
} else if (strtolower($POST['mode']) == "add") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	$module_name = MODULE_INVOICE;
	// echo '<pre>';print_r($POST);exit;     
	/*if(isset($POST['currency_enable'])){*/
	$curncy_trn['currency_id'] 		= $POST['currency_id'];
	$curncy_trn['currency_rate'] 	= $POST['currency_rate'];
	/*}else{
		$basecurrency = getbasecurrency($dbcon);
		$curncy_trn['currency_id']		= $basecurrency['currency_id'];
		$curncy_trn['currency_rate'] 	= 1;
	}*/

	$info['invoice_no']			= $POST['invoice_no'];
	$info['invoice_date']		= date('Y-m-d', strtotime($POST['invoice_date']));
	$info['invoice_due_date']	= date('Y-m-d', strtotime($POST['invoice_due_date']));
	$info['dc_enable'] 			= $POST['dc_enable']; //Added new by dhruv
	$info['challan_no']			= (isset($POST['dc_enable'])) ? $POST['challan_no'] : 0; //Added new by dhruv
	$info['challan_date']		= (isset($POST['dc_enable'])) ? date('Y-m-d', strtotime($POST['challan_date'])) : 0; //Added new by dhruv  	
	$info['num_of_parcel']		= $POST['num_of_parcel'];
	$info['po_enable']			= $POST['po_enable']; //Added new by dhruv    
	$info['order_no']			= (isset($POST['po_enable'])) ? $POST['order_no'] : 0; //Added new by dhruv	        
	$info['order_date']			= (isset($POST['po_enable'])) ? date('Y-m-d', strtotime($POST['order_date'])) : 0; //Added new by dhruv	   

	$info['currency_enable'] 	= $POST['currency_enable']; //Added new by dhruv    
	$info['currency_id']		= (isset($POST['currency_enable'])) ? $POST['currency_id'] : 0; //Added new by dhruv
	$info['currency_rate']		= (isset($POST['currency_enable'])) ? $POST['currency_rate'] : 1; //Added new by dhruv 

	$info['sale_material_center']	= $POST['sale_material_center']; //Added new by dhruv
	$info['is_sales_order']		= (isset($POST['is_sales_order']) && ($POST['is_sales_order'] == 'yes')) ? 1 : 0; //Added new by dhruv
	$info['enable_cost_center'] = (isset($POST['enable_cost_center']) && ($POST['enable_cost_center'] == 'yes')) ? 1 : 0; //Added new by dhruv
	$info['enable_tcs_details'] = (isset($POST['enable_tcs_details']) && ($POST['enable_tcs_details'] == 'yes')) ? 1 : 0; //Added new by dhruv
	$info['enable_ewaybill'] 	= (isset($POST['enable_ewaybill']) && ($POST['enable_ewaybill'] == 'yes')) ? 1 : 0; //Added new by dhruv
	$info['enable_transport'] = (isset($POST['enable_transport']) && ($POST['enable_transport'] == 'yes')) ? 1 : 0;

	$info['eway_bill_no'] 		= $POST['eway_bill_no']; //Added new by dhruv
	$info['eway_bill_date'] 	= $POST['eway_bill_date']; //Added new by dhruv

	$info['invoicetype_id']			= $POST['invoicetype_id'];
	$info['payment_terms']			= $POST['payment_terms'];
	$info['cust_id']			= $POST['cust_id'];
	$info['sales_ledger_id']	= implode(",", $POST['sales_ledger_id']);
	$info['print_status']		= $POST['print_status'];
	$info['financial_year_id']	= $POST['financial_year'];
	$info['sales_ledger_id'] 	= $POST['sales_ledger_id'];

	$info['kind_attn']			= $POST['kind_attn'];

	$info['remark']				= $_POST['remark'];
	$info['install_type']		= (isset($POST['install_type']) && ($POST['install_type'] == 'yes')) ? 1 : 0; //Added new by dhruv
	$info['cdate']				= date("Y-m-d H:i:s");
	$info['order_user_id']		= $POST['user_id'];
	$info['user_id']			= $_SESSION['user_id'];
	$info['company_id']			= $_SESSION['company_id'];
	$info['branch_id']   		= $branch_id;
	$info['invoice_status']		= 0;
	$info['usertype_id']		= $_SESSION['usertype_id'];

	if ($POST['currency_id'] == $_SESSION['currency_id']) {
		$info['basic_total']		= $POST['total'];
		$info['g_total']			= $POST['g_total'];
		$info['tcs_amount']  		= $POST['tcs_amount'];
		$info['round_off']			= $POST['round_of'];
		$info['basic_total_conv']	= $POST['total'] * $POST['currency_rate'];
		$info['g_total_conv']		= $POST['g_total'] * $POST['currency_rate'];
		$info['tcs_amount_conv']	= $POST['tcs_amount'] * $POST['currency_rate'];
		$info['round_off_conv']		= $POST['round_of'] * $POST['currency_rate'];
	} else {
		$info['basic_total']		= $POST['total'] * $POST['currency_rate'];
		$info['g_total']			= $POST['g_total'] * $POST['currency_rate'];
		$info['tcs_amount']  		= $POST['tcs_amount'] * $POST['currency_rate'];
		$info['round_off']			= $POST['round_of'] * $POST['currency_rate'];
		$info['basic_total_conv']	= $POST['total'];
		$info['g_total_conv']		= $POST['g_total'];
		$info['tcs_amount_conv']  	= $POST['tcs_amount'];
		$info['round_off_conv']		= $POST['round_of'];
	}

	$info['tcs_per']   = $POST['tcs_per'];
	$info['enable_bill_adjustment'] = $POST['bill_adjustment'];
	$info['sales_type']   = $POST['sales_type'];

	$info['check_hypothication'] 	= $POST['check_hypothication']; //Added new by dhruv    
	$info['hypo_bank']		= (isset($POST['check_hypothication'])) ? $POST['hypo_bank'] : 0; //
	$info['enable_consignee']		= (isset($POST['enable_consignee'])) ? $POST['enable_consignee'] : 0; //
	$info['consignee_id']		= (isset($POST['consignee_id'])) ? $POST['consignee_id'] : 0; //
	//print_r($POST);exit;

	$info['sales_order_id']		= implode(",", $POST['salesorderid']);
	$info['quot_type']			= $POST['quot_type'];
	$info['tc_format']			= $POST['tc_format'];
	$info['invoice_condition']	= $_POST['invoice_condition'];

	$info['orange']					= $POST['orange'];
	$info['mfg']					= $POST['mfg'];
	$info['trading']				= $POST['trading'];
	$info['repairing']				= $POST['repairing'];
	$info['other']					= $POST['other'];

	$info['terms_type']				= $POST['terms_type'];
	$info['term_salesorder_id']		= $POST['term_salesorder_id'];

	if (!empty($POST['salesorderid'][0])) {
		$info['sales_order_id']	= implode(",", $POST['salesorderid']);
	} else if ($POST['sales_order'] != 'undefined' || $POST['sales_order'] != '') {
		$info['sales_order_id']	= implode(",", json_decode($POST['sales_order']));
	}

	$module_id = $inserinvoiceid = add_record('tbl_invoice', array_merge($info, $curncy_trn), $dbcon);

	$cust_name = get_ledger_expense_by_id($dbcon, $POST['cust_id']);
	tbl_transcation_entry($dbcon, "Invoice", $POST['invoice_no'], $inserinvoiceid, $cust_name, $info['g_total']);


	/*Update Invoice Trn Table Start by Dhruv*/
	if ($inserinvoiceid) {
		$inv_trn['invoice_id']	= $inserinvoiceid;
		$inv_trn['trancation_status'] = 0;
		$updatetrnid = update_record('tbl_invoicetrn', array_merge($inv_trn, $curncy_trn), "user_id=" . $_SESSION['user_id'] . " and trancation_status=1 and invoice_id=0 ", $dbcon);

		foreach ($POST['tc_id'] as $key => $name) {
			$infotrm['tc_id']			= $POST['tc_id'][$key];
			$infotrm['ref_tc_id']		= $POST['ref_tc_id'][$key];
			$infotrm['tc_priority']		= $POST['tc_priority'][$key];
			$infotrm['tc_details']		= $_POST['tc_details'][$key];
			$infotrm['invoice_id']		= $inserinvoiceid;
			$infotrm['cdate']			= date("Y-m-d H:i:s");
			$infotrm['user_id']			= $_SESSION['user_id'];
			$infotrm['company_id']		= $_SESSION['company_id'];


			if (in_array($POST['tc_id'][$key], $POST['disp_term_flag'])) {
				$insertrmid = add_record('tbl_invoice_terms_trn', $infotrm, $dbcon, $branch_id);
			}
		}
	}

	//Stock maintain
	$query = "select trn.*, pro_mst.product_base_unit, pro_mst.batch_wise_stock_manage from tbl_invoicetrn as trn
			left join product_mst as pro_mst on pro_mst.product_id=trn.product_id
			where trn.trancation_status=0 and trn.invoice_id=" . $inserinvoiceid;
	//echo $query;exit;
	$result = $dbcon->query($query);

	while ($row = brp_mysqli_fetch_assoc($result)) {
		// if($row['batch_wise_stock_manage'] == 0){
		// 	if($row['unit_id']!=0){
		// 		minus_stock($dbcon,$row['product_id'],$row['unit_id'],$info['invoice_date'],"invoice_trn",$row['trancation_id'],$row['product_qty']);
		// 		deduct_so_reseve_stock($dbcon,$row['sales_ordertrn_id'],$row['product_qty'],$row['unit_id']);
		// 	}else{
		// 		minus_stock($dbcon,$row['product_id'],$row['product_base_unit'],$info['invoice_date'],"invoice_trn",$row['trancation_id'],$row['product_qty']);
		// 		deduct_so_reseve_stock($dbcon,$row['sales_ordertrn_id'],$row['product_qty'],$row['product_base_unit']);
		// 	}
		// }else{
		// 	$sel_itrn = $dbcon->query("SELECT * FROM tbl_batch_stock_tmp where status=1 and product_id=".$row['product_id']." and invoice_trn_id=".$row['trancation_id']." ");					
		// 	while($r_itrn=brp_mysqli_fetch_array($sel_itrn))
		// 	{
		// 		$insrtstockid = minus_batch_stock($dbcon,$r_itrn['product_id'],$r_itrn['unit_id'],$info['invoice_date'],"tbl_batch_stock_tmp",$r_itrn['batch_stk_id'],$r_itrn['qty'],$r_itrn['stock_id'],$POST['cust_id']);
		// 		deduct_so_reseve_stock($dbcon,$r_itrn['product_id'],$r_itrn['qty']);
		// 	}
		// }


		if ($row['paking_wise'] == 1) {
			paking_wise_stock_deduct($dbcon, $row['trancation_id']);
		} else {

			//pathik new stock deduct code add so this code comment 01-03-2023 start
			/*if($row['unit_id']!=0){

						minus_stock($dbcon,$row['product_id'],$row['unit_id'],$info['invoice_date'],"invoice_trn",$row['trancation_id'],$row['product_qty']);
						deduct_so_reseve_stock($dbcon,$row['sales_ordertrn_id'],$row['product_qty'],$row['unit_id']);
					}else{
						minus_stock($dbcon,$row['product_id'],$row['product_base_unit'],$info['invoice_date'],"invoice_trn",$row['trancation_id'],$row['product_qty']);
						deduct_so_reseve_stock($dbcon,$row['sales_ordertrn_id'],$row['product_qty'],$row['product_base_unit']);
					}*/
			//pathik new stock deduct code add so this code comment 01-03-2023 end

			//pathik new stock deduct code add start 01-03-2023
			if ($row['with_out_stock_invoice'] == 0) {
				deduct_stock_in_invoice($dbcon, $row['trancation_id']);
			}
			//pathik new stock deduct code add end 01-03-2023
		}
	}

	/*Update Cost center Trn Table Start by Dhruv*/
	if ($inserinvoiceid && $POST['enable_cost_center'] == 'yes') {
		$cost_trn['cost_center_ledger_id']	= $POST['cust_id'];
		$cost_trn['cost_center_table_id'] = $inserinvoiceid;
		$updatecosttrnid = update_record('tbl_cost_center_transaction', array_merge($cost_trn, $curncy_trn), "isdelete=0 and cost_center_table_id=0 and cost_center_ledger_id=0 and cost_center_table='tbl_invoice' and user_id=" . $_SESSION['user_id'], $dbcon);
	}

	/*Update TCS Trn Table Start by Dhruv*/
	if ($inserinvoiceid && $POST['enable_tcs_details'] == 'yes') {
		$tcs_trn['tcs_sale_id']	= $inserinvoiceid;
		$tcs_trn['tcs_sale_ledger'] = $POST['sales_ledger_id'];
		$tcs_trn['tcs_cust_ledger'] = $POST['cust_id'];
		$updatetcstrnid = update_record('tbl_tcs_deduction_transaction', array_merge($tcs_trn, $curncy_trn), "isdelete=0 and user_id=" . $_SESSION['user_id'], $dbcon);
	}

	//print_r($POST['bill_sundry_tax']);exit;

	/** Insert in general book table By Dhruv **/
	if ($inserinvoiceid) {
		if ($info['round_off'] < 0) {
			add_general_book_entry($dbcon, "tbl_invoice", $inserinvoiceid, 2, 98777, abs($info['round_off']), '', $POST['invoice_date'], '', $curncy_trn, $module_name, $module_id);
		} else {
			add_general_book_entry($dbcon, "tbl_invoice", $inserinvoiceid, 1, 98777, abs($info['round_off']), '', $POST['invoice_date'], '', $curncy_trn, $module_name, $module_id);
		}

		add_general_book_entry($dbcon, "tbl_invoice", $inserinvoiceid, 1, $POST['sales_ledger_id'], $info['total'], '', $POST['invoice_date'], '', $curncy_trn, $module_name, $module_id);
		//basic total & credit - done

		add_general_book_entry($dbcon, "tbl_invoice", $inserinvoiceid, 2, $POST['cust_id'], $info['g_total'], '', $POST['invoice_date'], '', $curncy_trn, $module_name, $module_id); // grand total & debit - done

		foreach ($POST['bill_sundry_tax'] as $bill_sundry_tax_id => $bill_sundry_tax_amount) {

			$info_sundry_tax['sundry_ledger_id'] = $bill_sundry_tax_id;
			//$info_sundry_tax['sundry_amount']=$bill_sundry_tax_amount;
			$info_sundry_tax['sundry_voucher_id'] = $inserinvoiceid;
			$info_sundry_tax['sundry_voucher_type'] = SALES_VOUCHER;
			$info_sundry_tax['sundry_voucher_table'] = 'tbl_invoice';
			$info_sundry_tax['cdate']	= date("Y-m-d H:i:s");
			$info_sundry_tax['user_id']	= $_SESSION['user_id'];
			$info_sundry_tax['company_id']	= $_SESSION['company_id'];

			if ($POST['currency_id'] == $_SESSION['currency_id']) {
				$info_sundry_tax['sundry_amount'] = $bill_sundry_tax_amount;
				$info_sundry_tax['sundry_amount_conv'] = $bill_sundry_tax_amount * $POST['currency_rate'];
			} else {
				$info_sundry_tax['sundry_amount'] = $bill_sundry_tax_amount * $POST['currency_rate'];
				$info_sundry_tax['sundry_amount_conv'] = $bill_sundry_tax_amount;
			}

			$sundry_tax_insert = add_record('tbl_bill_sundry_transaction', array_merge($info_sundry_tax, $curncy_trn), $dbcon);

			add_general_book_entry($dbcon, "tbl_bill_sundry_transaction", $sundry_tax_insert, 1, $bill_sundry_tax_id, $info_sundry_tax['sundry_amount'], '', $POST['invoice_date'], '', $curncy_trn, $module_name, $module_id); // credit entry & tax amount - done	
		}

		foreach ($POST['bill_sundry_addon'] as $bill_sundry_addon_id => $bill_sundry_addon_amount) {

			$info_sundry_addon['sundry_ledger_id'] = $bill_sundry_addon_id;
			//$info_sundry_addon['sundry_amount']=$bill_sundry_addon_amount;
			$info_sundry_addon['sundry_voucher_id'] = $inserinvoiceid;
			$info_sundry_addon['sundry_voucher_type'] = SALES_VOUCHER;
			$info_sundry_addon['sundry_voucher_table'] = 'tbl_invoice';
			$info_sundry_addon['cdate']	= date("Y-m-d H:i:s");
			$info_sundry_addon['user_id']	= $_SESSION['user_id'];
			$info_sundry_addon['company_id']	= $_SESSION['company_id'];

			if ($POST['currency_id'] == $_SESSION['currency_id']) {
				$info_sundry_addon['sundry_amount'] = $bill_sundry_addon_amount;
				$info_sundry_addon['sundry_amount_conv'] = $bill_sundry_addon_amount * $POST['currency_rate'];
			} else {
				$info_sundry_addon['sundry_amount'] = $bill_sundry_addon_amount * $POST['currency_rate'];
				$info_sundry_addon['sundry_amount_conv'] = $bill_sundry_addon_amount;
			}

			//print_r(array_merge($info_sundry_addon,$curncy_trn));

			$sundry_addon_insert = add_record('tbl_bill_sundry_transaction', array_merge($info_sundry_addon, $curncy_trn), $dbcon);


			if ($bill_sundry_addon_amount < 0) {
				add_general_book_entry($dbcon, "tbl_bill_sundry_transaction", $sundry_addon_insert, 2, $bill_sundry_addon_id, abs($info_sundry_addon['sundry_amount']), '', $POST['invoice_date'], '', $curncy_trn, $module_name, $module_id);

				$info_gen1['table_name']	= 'tbl_invoice';
				$info_gen1['table_id']		= $inserinvoiceid;
				$info_gen1['entry_type']	= 1;
				$info_gen1['ref_date']		= date('Y-m-d', strtotime($POST['invoice_date']));
				$info_gen1['ledger_id']		= $POST['cust_id'];
				$info_gen1['amount']		= abs($info_sundry_addon['sundry_amount']);
				$info_gen1['user_id']		= $_SESSION['user_id'];
				$info_gen1['cdate']			= date("Y-m-d H:i:s");
				$info_gen1['company_id']	= $_SESSION['company_id'];
				$info_gen1['ref_by'] = 'tbl_addon_bill_sundry';

				//$inserid_gen1=add_record("tbl_general_book", array_merge($info_gen1,$curncy_trn) , $dbcon);
				//add_general_book_entry($dbcon,"tbl_invoice",$inserinvoiceid,1,$POST['cust_id'],abs($bill_sundry_addon_amount),'',$POST['invoice_date'],'',$curncy_trn,$info_sundry);

			} else {
				add_general_book_entry($dbcon, "tbl_bill_sundry_transaction", $sundry_addon_insert, 1, $bill_sundry_addon_id, $info_sundry_addon['sundry_amount'], '', $POST['invoice_date'], '', $curncy_trn, $module_name, $module_id);

				$info_gen2['table_name']	= 'tbl_invoice';
				$info_gen2['table_id']		= $inserinvoiceid;
				$info_gen2['entry_type']	= 2;
				$info_gen2['ref_date']		= date('Y-m-d', strtotime($POST['invoice_date']));
				$info_gen2['ledger_id']		= $POST['cust_id'];
				$info_gen2['amount']		= $info_sundry_addon['sundry_amount'];
				$info_gen2['user_id']		= $_SESSION['user_id'];
				$info_gen2['cdate']			= date("Y-m-d H:i:s");
				$info_gen2['company_id']	= $_SESSION['company_id'];
				$info_gen2['ref_by'] = 'tbl_addon_bill_sundry';

				//$inserid_gen2=add_record("tbl_general_book", array_merge($info_gen2,$curncy_trn) , $dbcon);

				//add_general_book_entry($dbcon,"tbl_invoice",$inserinvoiceid,2,$POST['cust_id'],$bill_sundry_addon_amount,'',$POST['invoice_date'],'',$curncy_trn,$info_sundry);
			}

			// plus entry credit & cust new entry with debit & sundry amt
			// minus entry debit & cust new entry with credit & sundry amt
		}

		foreach ($POST['bill_sundry_addon_tax'] as $addon_id => $addon_value) {

			$addon_explode = explode("-", $addon_value);

			$info_addon['sundry_gst_per'] = $addon_explode[1];

			if ($POST['currency_id'] == $_SESSION['currency_id']) {
				$info_addon['sundry_gst_amount'] = $addon_explode[0];
				$info_addon['sundry_gst_amount_conv'] = $addon_explode[0] * $POST['currency_rate'];
			} else {
				$info_addon['sundry_gst_amount'] = $addon_explode[0] * $POST['currency_rate'];
				$info_addon['sundry_gst_amount_conv'] = $addon_explode[0];
			}
			$updateaddontaxid = update_record('tbl_bill_sundry_transaction', $info_addon, "sundry_voucher_table='tbl_invoice' and isdelete=0 and sundry_voucher_id=" . $inserinvoiceid . " and sundry_ledger_id=" . $addon_id . " ", $dbcon);
		}
	}


	/**Update sales order table By Dhruv **/
	if ($inserinvoiceid) {
		$sel_so = $dbcon->query("select * from tbl_invoicetrn where trancation_status=0 and invoice_id='$inserinvoiceid'");
		while ($r_so = brp_mysqli_fetch_assoc($sel_so)) {
			$so_trn_id = $r_so['sales_ordertrn_id'];

			$so_qty = $dbcon->query("select product_qty from tbl_sales_ordertrn where sales_ordertrn_id='$so_trn_id'");
			$so_count = brp_mysqli_fetch_assoc($so_qty);

			$inv_qty = $dbcon->query("select sum(product_qty) as total, trancation_status from tbl_invoicetrn where  sales_ordertrn_id='$so_trn_id' and trancation_status=0");
			$inv_count = brp_mysqli_fetch_assoc($inv_qty);

			get_salesorder_invoicedone($dbcon, $so_trn_id, $inserinvoiceid);
			/*if($remain <= 0){
            			$so_tbls['invoice_status'] = 1;
            			$so_tbls['remaning_invoice_qty'] = 0;
            			$updatesoid=update_record('tbl_sales_ordertrn', $so_tbls,"sales_ordertrn_id='".$so_trn_id."'" , $dbcon);
            		}
            		else{
            			$so_tbls['invoice_status'] = 0;
            			$so_tbls['remaning_invoice_qty'] = $remain;
            			$updatesoid=update_record('tbl_sales_ordertrn', $so_tbls,"sales_ordertrn_id='".$so_trn_id."'" , $dbcon);
            		}
        			/*$so_cnt = $dbcon->query("select * from tbl_sales_ordertrn WHERE sales_ordertrn_status = 0 AND sales_order_id = ".$so_count['sales_order_id']);
        			$so_cnts = brp_mysqli_num_rows($so_cnt);

        			$so_in_cnt = $dbcon->query("select * from tbl_sales_ordertrn WHERE sales_ordertrn_status = 0 AND invoice_status = 1 AND sales_order_id = ".$so_count['sales_order_id']);
        			$so_in_cnts = brp_mysqli_num_rows($so_in_cnt);
        			if($so_in_cnts>=$so_cnts){
        				$so_tbled['invoice_status'] = 1;
        				$updatesoid=update_record('tbl_sales_order', $so_tbled,"sales_order_id='".$so_count['sales_order_id']."'" , $dbcon);
        			}*/
		}
		if ($POST['is_sales_order'] == 'yes') {
			$sales_order = json_decode($POST['sales_order']);
			foreach ($sales_order as $sales_order_id) {
				if ($POST['transaction_type'] == 1) {

					$sales_remaning_qty = $dbcon->query("select  strn.sales_ordertrn_id from tbl_sales_ordertrn as strn where strn.sales_ordertrn_status=0 and strn.invoice_status=0 and strn.sales_order_id=" . $sales_order_id);

					while ($row_remaning = brp_mysqli_fetch_array($sales_remaning_qty)) {

						get_salesorder_invoicedone($dbcon, $row_remaning['sales_ordertrn_id'], $inserinvoiceid);
					}



					/*$sales_remaning_qty = brp_mysqli_fetch_array($dbcon->query("select sum(remaning_invoice_qty) as remaning_invoice_qty from tbl_sales_order as so left join tbl_sales_ordertrn as sot on so.sales_order_id=sot.sales_order_id where so.sales_order_id= '".$sales_order_id."' and sot.sales_ordertrn_status=0 group by sot.sales_order_id "));

            			
            				if($sales_remaning_qty['remaning_invoice_qty'] == 0){
	            				$so_tbl['invoice_status'] = 1;
	            				$so_tbl['used_invoice_id'] = $inserinvoiceid;
	            				$updatesoid=update_record('tbl_sales_order', $so_tbl,"sales_order_id='".$sales_order_id."'" , $dbcon);
            				}else{
            					$so_tbl['invoice_status'] = 0;
	            				$so_tbl['used_invoice_id'] = $inserinvoiceid;
	            				$updatesoid=update_record('tbl_sales_order', $so_tbl,"sales_order_id='".$sales_order_id."'" , $dbcon);
            				}*/
				} else if ($POST['transaction_type'] == 2) {

					$sales_remaning_qty = $dbcon->query("select strn.sales_ordertrn_id from tbl_sales_ordertrn as strn where strn.sales_ordertrn_status=0 and strn.invoice_status=0 and strn.sales_order_id=" . $sales_order_id);

					while ($row_remaning = brp_mysqli_fetch_array($sales_remaning_qty)) {
						get_salesorder_invoicedone($dbcon, $row_remaning['sales_ordertrn_id'], $inserinvoiceid);
					}
					/*$sales_remaning_qty = brp_mysqli_fetch_array($dbcon->query("select sum(remaning_invoice_qty) as remaning_invoice_qty from tbl_sales_order as so left join tbl_sales_ordertrn as sot on so.sales_order_id=sot.sales_order_id where so.sales_order_id= '".$sales_order_id."' and sot.sales_ordertrn_status=0 group by sot.sales_order_id "));
            				if($sales_remaning_qty['remaning_invoice_qty'] == 0){
            					// $so_alloc_tbl['remaning_invoice_qty'] = 0;
            					$so_alloc_tbl['invoice_status'] = 1;
            					$so_alloc_tbl['used_invoice_id'] = $inserinvoiceid;
            					$updatesoallocid=update_record('tbl_sales_order', $so_alloc_tbl,"sales_order_id='".$sales_order_id."'" , $dbcon);
            				}else{
            					$so_alloc_tbl['invoice_status'] = 0;
            					$so_alloc_tbl['used_invoice_id'] = $inserinvoiceid;
            					$updatesoallocid=update_record('tbl_sales_order', $so_alloc_tbl,"sales_order_id='".$sales_order_id."'" , $dbcon);
            				}*/
				}
			}
		}
	}

	//Installation type code
	if ($POST['install_type'] == 'yes') {
		$qry1 = "select * from tbl_invoicetrn where trancation_status=0 and invoice_id='$inserinvoiceid'";
		$row1 = $dbcon->query($qry1);
		while ($rel1 = mysqli_fetch_assoc($row1)) {
			$infoc['complaint_no'] = load_complaint_no($dbcon);
			$infoc['complaint_date'] = date('Y-m-d', strtotime($POST['invoice_date']));
			$infoc['cust_id'] = $POST['cust_id'];
			$infoc['complaint_type_id'] = '1';
			$infoc['cdate'] = date("Y-m-d H:i:s");
			$infoc['followup_status'] = '1';
			$infoc['sp_part_status'] = '4';
			$infoc['old_sp_part_status'] = 'no';
			$infoc['user_id'] = $_SESSION['user_id'];
			$infoc['company_id'] = $_SESSION['company_id'];
			$infoc['branch_id'] = $POST['branch_id'];
			$infoc['invoice_id'] = $inserinvoiceid;
			$insercomplainid = add_record('tbl_complaint', $infoc, $dbcon, $branch_id);

			/*$qry ='INSERT INTO tbl_complaint_trn (complaint_id,product_id,comp_pro_sts,user_id)
					SELECT '.$insercomplainid.',product_id,ser_status,user_id FROM  tbl_invoicetrn where invoice_id='.$inserinvoiceid; */

			$qryx = "INSERT INTO tbl_complaint_trn (complaint_id,product_id,comp_pro_sts,comp_amount,user_id) values ('$insercomplainid','$rel1[product_id]','$rel1[ser_status]','$rel1[total]','$_SESSION[user_id]')";

			$dbcon->query($qryx);

			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE status=0 and type_id=1 and company_id= " . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id']);
		}
	}

	if ($POST['quotation_id']) {
		$upd_qt_sts = upd_qt_done_sts($dbcon, $POST['quotation_id'], $inserinvoiceid);
	}
	if ($POST['complaint_id']) {
		$upd_spare_inv_sts = upd_spare_inv_sts($dbcon, $POST['complaint_id'], $inserinvoiceid);
	}

	//add general book entry for service and capital goods products 

	if ($inserinvoiceid) {

		$sel_gen = $dbcon->query("select trn.*,p.product_type,p.ledger_id from tbl_invoicetrn as trn 
					left join product_mst as p on trn.product_id=p.product_id
					where trn.invoice_id='$inserinvoiceid' and trn.trancation_status!='2'");

		while ($r_gen = brp_mysqli_fetch_assoc($sel_gen)) {
			add_general_book_entry($dbcon, "tbl_invoicetrn", $r_gen['trancation_id'], 1, $r_gen['ledger_id'], $r_gen['product_amount'], '', $POST['invoice_date'], '', $curncy_trn, $module_name, $module_id);
		}
	}

	/*Update Tax Trn Table Start by Dhruv*/
	if ($inserinvoiceid) {
		$tax_trn['tx_status'] = 0;
		$tax_trn['tx_trn_ref_id'] = $inserinvoiceid;
		$updatetcstrnid = update_record('tbl_tax_trn', array_merge($tax_trn, $curncy_trn), "tx_transaction_type='tbl_invoicetrn' and tx_status = 3", $dbcon);
	}

	/*Update Salesman Table Start by Dhruv*/
	if ($inserinvoiceid && $POST['enable_salesman'] == 'yes') {
		$salesman_trn['transaction_table_id'] = $inserinvoiceid;
		$updatesalesmantrnid = update_record('tbl_salesman_transaction', array_merge($salesman_trn, $curncy_trn), "transaction_voucher_type=" . SALES_VOUCHER . " and transaction_table_id = 0", $dbcon);
	}

	/* Update voucher No */
	if ($inserinvoiceid) {
		$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id=37 and company_id= " . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id']);
	}

	/* Eway Bill API */
	if ($inserinvoiceid && $POST['enable_ewaybill'] == 'yes' && 1 != 1) {
		$eway_row = getTransportEwayDetails($dbcon, SALES_VOUCHER);
		$company_data = get_company_data($dbcon, $_SESSION['company_id']);
		$customer_ledger_data = get_ledger_details($dbcon, $POST['cust_id']);
		$product_details = get_trans_by_inv_id($dbcon, $inserinvoiceid);
		$company_state_data = get_state_details($dbcon, 'and stateid = ' . $company_data['stateid'] . '');
		$cust_state_data = get_state_details($dbcon, 'and stateid = ' . $customer_ledger_data['stateid'] . '');
		$cust_city_data = get_city_details($dbcon, 'and cityid = ' . $customer_ledger_data['cityid'] . '');
		$trasport_gst = get_trasport_data($dbcon, 'and id = ' . $eway_row['transport_id'] . '');
		$sub_type = get_common_mst_data($dbcon, 'and common_mst_id = ' . $eway_row['eway_sub_type'] . '');
		$trans_mode = get_common_mst_data($dbcon, 'and common_mst_id = ' . $eway_row['transport_mode'] . '');

		$jsonobj .= '
				{
					"Push_Data_List": [ ';

		foreach ($product_details as $product_data) {

			$jsonobj .= '{
							"GSTIN": "' . $company_data['vatno'] . '",  
							"Year": "' . date('Y', strtotime($POST['invoice_date'])) . '",       
							"Month": "' . date('m', strtotime($POST['invoice_date'])) . '",      
							"SupplyType": "O",
							"SubType": "' . $sub_type['common_mst_desc'] . '",       
							"DocType": "INV",        
							"DocNo": "' . $POST['invoice_no'] . '", 
							"DocDate": "' . date('Ymd', strtotime($POST['invoice_date'])) . '",    
							"SupGSTIN": "' . $company_data['vatno'] . '",
							"SupName": "' . $company_data['company_name'] . '",       
							"SupAdd1": "' . $company_data['address'] . '",
							"SupAdd2": "",				
							"SupCity": "' . $company_data['city_name'] . '",			
							"SupState": "' . $company_state_data['gst_state_code'] . '",				
							"SupPincode": "' . $company_data['pincode'] . '",		

							"RecGSTIN": "' . $customer_ledger_data['gst_no'] . '",     
							"RecName": "' . $customer_ledger_data['l_name'] . '",					
							"RecAdd1": "' . $customer_ledger_data['m_address'] . '",	 
							"RecAdd2": "",						// blank
							"Reccity": "' . $cust_city_data['city_name'] . '",				
							"RecState": "' . $cust_state_data['gst_state_code'] . '",				
							"Recpincode": "' . $customer_ledger_data['cust_pincode'] . '",		

							"TransMode": "' . $trans_mode['common_mst_desc'] . '",
							"TransporterId": "' . $trasport_gst['transportation_gst_number'] . '", 
							"TransporterName": "' . $trasport_gst['transportation_name'] . '",
							"TransDistance": "' . $eway_row['distance_km'] . '",
							"TransDocNo": "' . $eway_row['transport_doc_no'] . '", 
							"TransDocDate": "' . $eway_row['transport_doc_date'] . '",
							"VehicleType": "R",
							"VehicleNo": "' . $eway_row['transport_vehicle_no'] . '",


							"ProductName": "' . $product_data['productName'] . '",
							"ProductDesc": "' . $product_data['product_desc'] . '",
							"HSNCode": "' . $product_data['hsnCode'] . '",
							"Quantity": "' . $product_data['product_qty'] . '",
							"QtyUnit": "' . $product_data['unit_code'] . '",
							"TaxableValue": "' . $product_data['taxable_value'] . '",
							"TotalValue": "' . $product_data['total'] . '",
							"SGSTRate": "' . $product_data['sgstPer'] . '",
							"SGSTValue": "' . $product_data['sgstValue'] . '",
							"CGSTRate": "' . $product_data['cgstPer'] . '",
							"CGSTValue": "' . $product_data['cgstValue'] . '",
							"IGSTRate": "' . $product_data['igstper'] . '",
							"IGSTValue": "' . $product_data['igstValue'] . '",
							"CessRate": 0,
							"CessValue": 0,

							"EWBUserName": "05AAACW3775F012",
							"EWBPassword": "Admin!23",
							"CessNonAdvol": 0,
							"SubSupplyDesc": "",
							"ShipFromStateCode": "05",
							"ShipToStateCode": "05",

							"TotalInvoiceValue": "' . $info['g_total'] . '",
							"CessNonAdvolValue": 0,
							"OtherValue": 0,

							"dispatchFromGSTIN ": "' . $company_data['vatno'] . '",
							"dispatchFromTradeName": "' . $company_data['company_name'] . '",	
							"ShipToGSTIN": "' . $customer_ledger_data['gst_no'] . '",		
							"ShipToTradeName": "' . $customer_ledger_data['l_name'] . '",	
							"IsBillFromShipFromSame": "1",			
							"IsBillToShipToSame": "1",

							"IsGSTINSEZ": "' . $customer_ledger_data['enable_sez'] . '"  
						}, ';
		}
		$jsonobj .= ' ],
					"Year": 2018,
					"Month": 10,
					"EFUserName": "29AAACW3775F000",
					"EFPassword": "Admin!23..",
					"CDKey": "1000687"
				}';

		//print_r($jsonobj);exit;
		$callEway = submitEwayApi($jsonobj);

		$obj = json_decode($callEway);

		$arr = json_decode($obj);

		//echo '<pre>';print_r($arr[0]);exit;

		$eway_bill_status = $arr[0]->IsSuccess;
	}

	if ($eway_bill_status == 'true') {
		$eway_status_trn['eway_bill_status'] = 1;
	} else {
		$eway_status_trn['eway_bill_status'] = 2;
	}


	//Update Invoice table with eway_no & date
	if ($POST['enable_ewaybill'] == 'yes' && $eway_bill_status == 'true') {
		$info_invtbl['eway_bill_no'] = $arr[0]->EWayBill;
		$info_invtbl['eway_bill_date'] = date('Y-m-d H:i:s', strtotime($arr[0]->Date));
	} else {
		$info_invtbl['eway_bill_no'] = $POST['eway_bill_no'];
		$info_invtbl['eway_bill_date'] = date('Y-m-d', strtotime($POST['eway_bill_date']));
	}

	$updateinvtbl = update_record('tbl_invoice', $info_invtbl, "invoice_id='" . $inserinvoiceid . "'", $dbcon);

	$updatetcstrnid = update_record('tbl_ewaybill_transaction', array_merge($eway_status_trn, $curncy_trn), "eway_bill_voucher_type='1' and eway_bill_voucher_id ='0'", $dbcon);

	/*Update Trasport and Eway trans Table Start by Dhruv*/
	if ($inserinvoiceid) {
		$transp_trn['transport_transaction_table_id'] = $inserinvoiceid;
		$updatetcstrnid = update_record('tbl_transport_transaction', array_merge($transp_trn, $curncy_trn), "transport_transaction_table='tbl_invoice' and transport_transaction_table_id = 0", $dbcon);
	}

	//bill by bill adjustment

	if ($inserinvoiceid) {

		$bill_adjustment = $POST['bill_adjustment'];

		if ($bill_adjustment == 1) {
			$adj_trn['bill_ref'] = $inserinvoiceid;
			$adj_trn['bill_due_date'] = date('Y-m-d', strtotime($POST['invoice_date']));
			$adj_trn['bill_table_id'] = $inserinvoiceid;

			update_record('tbl_bill_by_bill_adjustment_transaction', array_merge($adj_trn, $curncy_trn), "bill_table='tbl_invoice' and bill_table_id = 0", $dbcon);

			$sel1 = $dbcon->query("select bill_transaction_id,bill_amount from tbl_bill_by_bill_adjustment_transaction where bill_adjustment_status='0' ");
			while ($row1 = brp_mysqli_fetch_assoc($sel1)) {
				$amount = $row1['bill_amount'];
				$sel2 = $dbcon->query("select sum(bill_amount) as paid_total from tbl_bill_by_bill_adjustment_transaction where bill_adjustment_id='$row1[bill_transaction_id]'");
				$row2 = brp_mysqli_fetch_assoc($sel2);

				$remaining = $amount - $row2['paid_total'];

				if ($remaining == 0) {
					$advance_trn['bill_adjustment_status'] = 1;

					update_record('tbl_bill_by_bill_adjustment_transaction', array_merge($advance_trn, $curncy_trn), "bill_transaction_id='$row1[bill_transaction_id]'", $dbcon);
				}
			}
		}
	}

	if ($inserinvoiceid) {
		$arr['eid'] = $inserinvoiceid;
		$arr['msg'] = 1;
	} else {
		$arr['msg'] = 0;
	}

	echo json_encode($arr);
} else if (strtolower($POST['mode']) == "edit") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	if (isset($POST['currency_enable'])) {
		$curncy_trn['currency_id'] = $POST['currency_id'];
		$curncy_trn['currency_rate'] = $POST['currency_rate'];
	} else {
		$basecurrency = getbasecurrency($dbcon);
		$curncy_trn['currency_id'] = $basecurrency['currency_id'];
		$curncy_trn['currency_rate'] = 1;
	}

	$info['invoice_no']	= $POST['invoice_no'];
	$info['invoice_date']	= date('Y-m-d', strtotime($POST['invoice_date']));
	$info['invoice_due_date']	= date('Y-m-d', strtotime($POST['invoice_due_date']));
	$info['dc_enable'] = $POST['dc_enable']; //Added new by dhruv
	$info['challan_no']	= (isset($POST['dc_enable'])) ? $POST['challan_no'] : 0; //Added new by dhruv
	$info['challan_date']	= (isset($POST['dc_enable'])) ? date('Y-m-d', strtotime($POST['challan_date'])) : 0; //Added new by dhruv  	

	$info['po_enable'] = $POST['po_enable']; //Added new by dhruv    
	$info['order_no']	= (isset($POST['po_enable'])) ? $POST['order_no'] : 0; //Added new by dhruv	        
	$info['order_date']	= (isset($POST['po_enable'])) ? date('Y-m-d', strtotime($POST['order_date'])) : 0; //Added new by dhruv

	$info['num_of_parcel']		= $POST['num_of_parcel'];

	$info['payment_terms']	= $POST['payment_terms'];
	$info['currency_enable'] = $POST['currency_enable']; //Added new by dhruv    
	$info['currency_id']	= (isset($POST['currency_enable'])) ? $POST['currency_id'] : 0; //Added new by dhruv
	$info['currency_rate']	= (isset($POST['currency_enable'])) ? $POST['currency_rate'] : 1; //Added new by dhruv 

	$info['check_hypothication'] 	= $POST['check_hypothication']; //Added new by dhruv    
	$info['hypo_bank']		= (isset($POST['check_hypothication'])) ? $POST['hypo_bank'] : 0; //

	$info['sale_material_center']	= $POST['sale_material_center']; //Added new by dhruv
	$info['is_sales_order']	= $POST['is_sales_order']; //Added new by dhruv
	$info['enable_cost_center'] = (isset($POST['enable_cost_center']) && ($POST['enable_cost_center'] == 'yes')) ? 1 : 0; //Added new by dhruv
	$info['enable_tcs_details'] = (isset($POST['enable_tcs_details']) && ($POST['enable_tcs_details'] == 'yes')) ? 1 : 0; //Added new by dhruv
	$info['enable_ewaybill'] = (isset($POST['enable_ewaybill']) && ($POST['enable_ewaybill'] == 'yes')) ? 1 : 0; //Added new by dhruv

	$info['eway_bill_no'] = $POST['eway_bill_no']; //Added new by dhruv
	$info['eway_bill_date'] = date('Y-m-d', strtotime($POST['eway_bill_date'])); //Added new by dhruv
	$info['sales_ledger_id']	= implode(",", $POST['sales_ledger_id']);

	$info['cust_id']	= $POST['cust_id'];
	$info['sales_ledger_id'] = $POST['sales_ledger_id'];
	// $info['invoicetype_id']			= $POST['invoicetype_id'];
	$info['print_status']	= $POST['print_status'];
	$info['financial_year_id']	= $POST['financial_year'];

	$info['kind_attn']		= $POST['kind_attn'];
	$info['remark']			= $_POST['remark'];
	$info['install_type']	= (isset($POST['install_type']) && ($POST['install_type'] == 'yes')) ? 1 : 0; //Added new by dhruv
	$info['cdate']			= date("Y-m-d H:i:s");
	$info['order_user_id']	= $POST['user_id'];
	$info['user_id']		= $_SESSION['user_id'];
	$info['company_id']		= $_SESSION['company_id'];
	$info['branch_id']   	= $branch_id;
	$info['invoice_status']		= 0;
	$info['usertype_id']	= $_SESSION['usertype_id'];
	$info['enable_consignee']		= (isset($POST['enable_consignee'])) ? $POST['enable_consignee'] : 0; //
	$info['consignee_id']		= (isset($POST['consignee_id'])) ? $POST['consignee_id'] : 0; //

	if ($POST['currency_id'] == $_SESSION['currency_id']) {
		$info['basic_total']		= $POST['total'];
		$info['g_total']			= $POST['g_total'];
		$info['tcs_amount']  		= $POST['tcs_amount'];
		$info['round_off']			= $POST['round_of'];
		$info['basic_total_conv']	= $POST['total'] * $POST['currency_rate'];
		$info['g_total_conv']		= $POST['g_total'] * $POST['currency_rate'];
		$info['tcs_amount_conv']	= $POST['tcs_amount'] * $POST['currency_rate'];
		$info['round_off_conv']		= $POST['round_of'] * $POST['currency_rate'];
	} else {
		$info['basic_total']		= $POST['total'] * $POST['currency_rate'];
		$info['g_total']			= $POST['g_total'] * $POST['currency_rate'];
		$info['tcs_amount']  		= $POST['tcs_amount'] * $POST['currency_rate'];
		$info['round_off']			= $POST['round_of'] * $POST['currency_rate'];
		$info['basic_total_conv']	= $POST['total'];
		$info['g_total_conv']		= $POST['g_total'];
		$info['tcs_amount_conv']  	= $POST['tcs_amount'];
		$info['round_off_conv']		= $POST['round_of'];
	}

	$info['tcs_per']   = $POST['tcs_per'];

	$inforoundof['amount'] = abs($info['round_off']);
	$info['quot_type']			= $POST['quot_type'];
	$info['tc_format']			= $POST['tc_format'];
	$info['invoice_condition']	= $_POST['invoice_condition'];

	$info['orange']					= $POST['orange'];
	$info['mfg']					= $POST['mfg'];
	$info['trading']				= $POST['trading'];
	$info['repairing']				= $POST['repairing'];
	$info['other']					= $POST['other'];
	//echo '<pre>';print_r($info);
	// 	print_r($POST);exit;

	$inserinvoiceid = update_record('tbl_invoice', $info, "invoice_id=" . $POST['eid'], $dbcon);
	//$inserinvoiceid=add_record('tbl_invoice', array_merge($info,$curncy_trn), $dbcon);
	if ($inserinvoiceid) {
		if ($info['round_off'] < 0) {
			$inforoundof['entry_type'] = 2;
			update_record("tbl_general_book", $inforoundof, " ledger_id= 98777 and table_name='tbl_invoice' and table_id=" . $POST['eid'], $dbcon);
		} else {
			$inforoundof['entry_type'] = 1;
			update_record("tbl_general_book", $inforoundof, " ledger_id= 98777 and table_name='tbl_invoice' and table_id=" . $POST['eid'], $dbcon);
		}
	}

	$deltrmid = delete_record('tbl_invoice_terms_trn', "invoice_id=" . $POST['eid'], $dbcon, $branch_id);

	foreach ($POST['tc_id'] as $key => $name) {
		$infotrm['tc_id']			= $POST['tc_id'][$key];
		$infotrm['ref_tc_id']		= $POST['ref_tc_id'][$key];
		$infotrm['tc_priority']		= $POST['tc_priority'][$key];
		$infotrm['tc_details']		= $POST['tc_details'][$key];
		$infotrm['invoice_id']		= $POST['eid'];
		$infotrm['cdate']			= date("Y-m-d H:i:s");
		$infotrm['user_id']			= $_SESSION['user_id'];
		$infotrm['company_id']		= $_SESSION['company_id'];
		if (in_array($POST['tc_id'][$key], $POST['disp_term_flag'])) {
			$insertrmid = add_record('tbl_invoice_terms_trn', $infotrm, $dbcon, $branch_id);
		}
	}


	$query1 = "select * from tbl_bill_sundry_transaction where sundry_voucher_id='$POST[eid]'  and isdelete=0 and sundry_voucher_table='tbl_invoice'  ";
	$rel1 = brp_mysqli_fetch_all($dbcon->query($query1));

	foreach ($rel1 as $bill_sundry_addon) {
		$info_general_sundry['ref_date'] = date('Y-m-d', strtotime($POST['invoice_date']));
		update_record("tbl_general_book", $info_general_sundry, " ledger_id=" . $bill_sundry_addon['sundry_ledger_id'] . " and table_name='tbl_bill_sundry_transaction' 
					and table_id= " . $bill_sundry_addon['sundry_id'] . " ", $dbcon);
	}

	foreach ($POST['bill_sundry_tax'] as $bill_sundry_tax_id => $bill_sundry_tax_amount) {

		if ($bill_sundry_tax_id == 9870) {
			if ($POST['currency_id'] == $_SESSION['currency_id']) {
				$infogsttax['cgst'] 	 = $bill_sundry_tax_amount;
				$infogsttax['cgst_conv'] = $bill_sundry_tax_amount * $POST['currency_rate'];
			} else {
				$infogsttax['cgst'] = $bill_sundry_tax_amount * $POST['currency_rate'];
				$infogsttax['cgst_conv'] = $bill_sundry_tax_amount;
			}

			$updateinvoice = update_record('tbl_invoice', $infogsttax, "invoice_id=" . $POST['eid'] . " ", $dbcon);
		} else if ($bill_sundry_tax_id == 9880) {
			if ($POST['currency_id'] == $_SESSION['currency_id']) {
				$infogsttax['sgst'] = $bill_sundry_tax_amount;
				$infogsttax['sgst_conv'] = $bill_sundry_tax_amount * $POST['currency_rate'];
			} else {
				$infogsttax['sgst'] = $bill_sundry_tax_amount * $POST['currency_rate'];
				$infogsttax['sgst_conv'] = $bill_sundry_tax_amount;
			}
			$updateinvoice = update_record('tbl_invoice', $infogsttax, "invoice_id=" . $POST['eid'] . " ", $dbcon);
		} else if ($bill_sundry_tax_id == 9890) {
			if ($POST['currency_id'] == $_SESSION['currency_id']) {
				$infogsttax['igst']	 	 = $bill_sundry_tax_amount;
				$infogsttax['igst_conv'] = $bill_sundry_tax_amount * $POST['currency_rate'];
			} else {
				$infogsttax['igst'] 	 = $bill_sundry_tax_amount * $POST['currency_rate'];
				$infogsttax['igst_conv'] = $bill_sundry_tax_amount;
			}
			$updateinvoice = update_record('tbl_invoice', $infogsttax, "invoice_id=" . $POST['eid'] . " ", $dbcon);
		} else if ($bill_sundry_tax_id == 9892) {
			if ($POST['currency_id'] == $_SESSION['currency_id']) {
				$infogsttax['tcs'] = $bill_sundry_tax_amount;
				$infogsttax['tcs_conv'] = $bill_sundry_tax_amount * $POST['currency_rate'];
			} else {
				$infogsttax['tcs'] = $bill_sundry_tax_amount * $POST['currency_rate'];
				$infogsttax['tcs_conv'] = $bill_sundry_tax_amount;
			}
			$updateinvoice = update_record('tbl_invoice', $infogsttax, "invoice_id=" . $POST['eid'] . " ", $dbcon);
		}
	}
	$info_invoice_sundry['ref_date'] = date('Y-m-d', strtotime($POST['invoice_date']));
	update_record("tbl_general_book", $info_invoice_sundry, "table_name='tbl_invoice' 
				and table_id= " . $POST['eid'] . " ", $dbcon);


	if ($inserinvoiceid) {
		$arr['msg'] = 1;
		$arr['eid'] = $POST['eid'];
	} else {
		$arr['msg'] = 0;
	}

	echo json_encode($arr);
} else if (strtolower($POST['mode']) == "delete") {


	$info['invoice_status']	= 2;
	$info1['trancation_status']	= 2;
	$informdr['status'] = 2;
	$info_sales_order['invoice_status']  = 0;
	$info_srl['inv_srl_trn_status']  = 0;
	$updatesalesid = update_record('tbl_sales_order', $info_sales_order, "used_invoice_id=" . $POST['eid'], $dbcon);
	$updateinvoiceid = update_record('tbl_invoice', $info, "invoice_id=" . $POST['eid'], $dbcon);
	$updatetrancationid = update_record('tbl_invoicetrn', $info1, "invoice_id=" . $POST['eid'], $dbcon);
	$updatesrlid = update_record('tbl_inv_srl_trn', $info_srl, "invoice_id=" . $POST['eid'], $dbcon);
	//Update Payment Reminder
	$updatermdrid = update_record('todo_mst', $informdr, "ref_id=" . $POST['eid'] . " and ref_table='tbl_invoice'", $dbcon);
	//Update Serial Number
	//$deleteid=delete_record('tbl_serialtrn',"invoice_id=".$POST['eid'], $dbcon);

	$info_gen['genral_book_status']		= 2;
	$updateinvoiceid = update_record('tbl_general_book', $info_gen, "table_name='tbl_invoice' and table_id=" . $POST['eid'], $dbcon);

	//tax transaction

	$sel_itrn = $dbcon->query("select * from  tbl_invoicetrn where invoice_id='$POST[eid]' and trancation_status='2'");
	while ($r_itrn = brp_mysqli_fetch_array($sel_itrn)) {
		$info_tax_trn['tx_status'] = 2;
		update_record("tbl_tax_trn", $info_tax_trn, "tx_transaction_id='$r_itrn[trancation_id]' and tx_transaction_type='tbl_invoicetrn'", $dbcon);

		$info_general['genral_book_status'] = 2;
		update_record('tbl_general_book', $info_general, "table_name='tbl_invoicetrn' and table_id=" . $r_itrn['trancation_id'], $dbcon);
	}

	//Eway Bill Transaction

	$eway_trans['isdelete'] = 1;
	$updateinvoiceid = update_record('tbl_ewaybill_transaction', $eway_trans, "eway_bill_voucher_table='tbl_invoice' and eway_bill_voucher_id=" . $POST['eid'], $dbcon);

	//Transport Transaction

	$transport_transaction['transportation_status'] = 1;
	$updateinvoiceid = update_record('tbl_transport_transaction', $transport_transaction, "transport_transaction_table='tbl_invoice' and transport_transaction_table_id=" . $POST['eid'], $dbcon);


	//Salesman Transaction

	$salesman_transaction['isdelete'] = 1;
	$updateinvoiceid = update_record('tbl_salesman_transaction', $salesman_transaction, "transaction_table='tbl_invoice' and transaction_table_id=" . $POST['eid'], $dbcon);

	//Insert LOG
	$log_entry = common_log_entry($dbcon, "invoice_add", 3, "tbl_invoice", $POST['eid']);

	//Cost Center Transaction

	$info_cost['costcenter_status'] = 2;
	$updateid1 = update_record("tbl_cost_center_transaction", $info_cost, "table_name='tbl_invoice' and table_id=" . $POST['eid'], $dbcon);

	//TCS Deduction Transaction

	$info_tcs['isdelete'] = 1;
	$updateid1 = update_record("tbl_tcs_deduction_transaction", $info_tcs, "tcs_sale_id=" . $POST['eid'], $dbcon);

	//Bill Sundry Transaction

	$info_bsun['isdelete'] = 1;
	$updateid1 = update_record(
		"tbl_bill_sundry_transaction",
		$info_bsun,
		"sundry_voucher_table='tbl_invoice' and sundry_voucher_id=" . $POST['eid'] . "",
		$dbcon
	);

	$sel_bsun = $dbcon->query("select * from tbl_bill_sundry_transaction where sundry_voucher_table='tbl_invoice' and sundry_voucher_id=" . $POST['eid'] . " and isdelete='1'");
	while ($r_bsun = brp_mysqli_fetch_array($sel_bsun)) {
		$info_bsun_general['genral_book_status'] = 2;
		$updateid1 = update_record("tbl_general_book", $info_bsun_general, "table_name='tbl_bill_sundry_transaction' and table_id=" . $r_bsun['sundry_id'] . " ", $dbcon);
	}


	$query = "select * from tbl_invoicetrn where invoice_id=" . $POST['eid'];
	$result = $dbcon->query($query);
	while ($row = mysqli_fetch_assoc($result)) {
		//pathik comment code new stock deduct code add so comment start 01-03-2023		 
		//$info_de['stock_status']=2;
		//$updateid1=update_record("tbl_stock_trn", $info_de,"ref_name='invoice_trn' and ref_id=".$row['trancation_id'] ,$dbcon);
		//pathik comment code new stock deduct code add so comment end 01-03-2023

		//pathik stock delete new code add start 01-03-2023
		remove_stock_inv_trn_wise($dbcon, $row['trancation_id']);
		//pathik stock delete new code add start 01-03-2023
		get_salesorder_invoicedone($dbcon, $row['sales_ordertrn_id'], $row['invoice_id']);
		/*if($row['transaction_type'] == 1){
					$info_so_trans['remaning_invoice_qty'] = $row['product_qty'];
					$info_so_trans['invoice_status'] = 0;
					$update_sotransid=update_record('tbl_sales_ordertrn', $info_so_trans,"sales_ordertrn_id=".$row['so_allocation_id'] , $dbcon);
				}
				if($row['transaction_type'] == 2){
					$info_alloc_trans['remaning_invoice_qty'] = $row['product_qty'];
					$update_alloctransid=update_record('tbl_sales_order_production_trn', $info_alloc_trans,"sales_order_production_trn_id=".$row['so_allocation_id'] , $dbcon);
				}*/
	}


	if ($updatetrancationid)
		echo "1";
	else
		echo "0";
} else if (strtolower($POST['mode']) == "fieldadd") {

	//echo '<pre>'; print_r($POST);exit;
	//var_dump(expression);
	if (isset($POST['currency_enable']) && $POST['currency_enable'] == 1) {
		$curncy_trn['currency_id'] = $POST['currency_id'];
		$curncy_trn['currency_rate'] = $POST['currency_rate'];
	} else {
		$basecurrency = getbasecurrency($dbcon);
		$curncy_trn['currency_id'] = $basecurrency['currencyid'];
		$curncy_trn['currency_rate'] = 1;
	}
	//$POST['currency_enable'];
	//print_r($curncy_trn);
	$product_detail = get_product_detail($dbcon, $POST['product_id']);

	$company_state = get_company_data($dbcon, $_SESSION['company_id']);
	//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);

	//$sale_gst = get_tax_cat_by_hsn($dbcon,$POST['product_hsn_code']);

	$custLedgerDetails = get_cust_data_arr($dbcon, $POST['cust_id']);
	$companyConfiguration = getCompanyConfiguration($dbcon);

	if ($POST['sales_type'] == 3) {
		$sale_gst['tax_gst'] = 0.1;
		$sale_gst['tax_cat_id'] = 0;
	} else if ($POST['sales_type'] == 4) {
		$sale_gst['tax_gst'] = 0;
		$sale_gst['tax_cat_id'] = 0;
	} else if ($POST['sales_type'] == 5) {
		$sale_gst['tax_gst'] = 5;
		$sale_gst['tax_cat_id'] = 0;
	} else if ($POST['sales_type'] == 6) {
		$sale_gst['tax_gst'] = 12;
		$sale_gst['tax_cat_id'] = 0;
	} else if ($POST['sales_type'] == 7) {
		$sale_gst['tax_gst'] = 18;
		$sale_gst['tax_cat_id'] = 0;
	} else if ($POST['sales_type'] == 8) {
		$sale_gst['tax_gst'] = 24;
		$sale_gst['tax_cat_id'] = 0;
	} else {
		$sale_gst = get_tax_cat_by_hsn($dbcon, trim($_POST['product_hsn_code']));
	}

	$cgst_tax_rate = 0;
	$cgst_tax_rate_conv = 0;
	$sgst_tax_rate = 0;
	$sgst_tax_rate_conv = 0;
	$igst_tax_rate = 0;
	$igst_tax_rate_conv = 0;

	if ($product_detail['product_gst'] == 'including') {
		$prorate = $POST['product_rate'] * 100 / (100 + $sale_gst['tax_gst']);
	} else {
		$prorate = $POST['product_rate'];
	}

	if (($company_state['stateid'] == $POST['cust_stateid']) && ($custLedgerDetails['enable_sez'] == 0)) {

		$product_amt = $POST['product_amount'];
		$gst = $sale_gst['tax_gst'] / 2;
		$cgst_tax_per = $gst;
		$cgst_tax_rate = ($gst * $product_amt) / 100;
		$cgst_tax_rate_conv = ($POST['currency_rate'] * $gst * $product_amt) / 100;
		$sgst_tax_per = $gst;
		$sgst_tax_rate = ($gst * $product_amt) / 100;
		$sgst_tax_rate_conv = ($POST['currency_rate'] * $gst * $product_amt) / 100;
	} else {
		$product_amt = $POST['product_amount'];
		$igst_tax_per = $sale_gst['tax_gst'];

		if ($custLedgerDetails['enable_sez'] == 0) {
			$igst_tax_rate = ($sale_gst['tax_gst'] * $product_amt) / 100;
			$igst_tax_rate_conv = ($POST['currency_rate'] * $sale_gst['tax_gst'] * $product_amt) / 100;
		} else {
			$igst_tax_rate = 0;
			$igst_tax_rate_conv = 0;
		}
	}

	//print_r($sale_gst);
	//print_r($POST);exit;

	$info1['product_id']		= $POST['product_id'];
	$info1['description']		= $_POST['product_des'];
	$info1['ser_status']		= $POST['ser_status'];
	$info1['product_hsn_code']	= $_POST['product_hsn_code'];

	$info1['product_qty']		= $POST['product_qty'];
	$info1['product_conv_qty']	= $POST['product_conv_qty'];

	$info1['product_disc']		= $POST['product_disc'];
	$info1['unit_id']			= $POST['unit_id'];
	$info1['conv_unit_id']		= $POST['conv_unitid'];
	$info1['rate_unit']			= $POST['rate_unitid'];

	$info1['product_spec']		= $POST['product_spec'];
	//$info1['product_amount']	= $POST['product_amount'];
	$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0;
	$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0;
	$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0;

	if ($POST['currency_id'] == $_SESSION['currency_id']) {
		$info1['product_rate']		= $prorate;
		$info1['product_discount']	= $POST['product_discount'];
		$info1['product_amount']	= $product_amt;
		$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
		$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
		$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0;
		$info1['taxable_value']		= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
		$info1['total'] 			= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate + $product_amt;

		$info1['product_rate_conv']		= $prorate * $POST['currency_rate'];
		$info1['product_discount_conv'] = $POST['product_discount'] * $POST['currency_rate'];
		$info1['product_amount_conv']	= $product_amt * $POST['currency_rate'];
		$info1['cgst_tax_rate_conv'] 	= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
		$info1['sgst_tax_rate_conv'] 	= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
		$info1['igst_tax_rate_conv'] 	= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;
		$info1['taxable_value_conv'] 	= $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv;
		$info1['total_conv']		 	= $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv + $info1['product_amount_conv'];
	} else {
		$info1['product_rate']		= $prorate * $POST['currency_rate'];
		$info1['product_discount']	= $POST['product_discount'] * $POST['currency_rate'];
		$info1['product_amount']	= $product_amt * $POST['currency_rate'];
		$info1['cgst_tax_rate']		= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
		$info1['sgst_tax_rate']		= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
		$info1['igst_tax_rate']		= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;
		$info1['taxable_value']		= $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv;
		$info1['total'] = $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv + $info1['product_amount'];

		$info1['product_rate_conv']		= $prorate;
		$info1['product_discount_conv'] = $POST['product_discount'];
		$info1['product_amount_conv']	= $product_amt;
		$info1['cgst_tax_rate_conv'] 	= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
		$info1['sgst_tax_rate_conv'] 	= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
		$info1['igst_tax_rate_conv'] 	= isset($igst_tax_rate) ? $igst_tax_rate : 0;
		$info1['taxable_value_conv'] 	= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
		$info1['total_conv']		 	= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate + $product_amt;
	}


	$info1['product_tax_cat']	= $sale_gst['tax_cat_id'];
	$info1['discount_per']		= $POST['discount_per'];

	$info1['orange']			= $POST['orange'];
	$info1['mfg']				= $POST['mfg'];
	$info1['trading']			= $POST['trading'];
	$info1['repairing']			= $POST['repairing'];
	$info1['other']				= $POST['other'];

	$info1['orange_total']					= $POST['orange_total'];
	$info1['mfg_total']					= $POST['mfg_total'];
	$info1['trading_total']				= $POST['trading_total'];
	$info1['repairing_total']				= $POST['repairing_total'];
	$info1['other_total']					= $POST['other_total'];
	//$info1['formulaid']			= $POST['formulaid'];
	$info1['company_id']		= $_SESSION['company_id'];
	//$info1['bill_value']		= $POST['bill_value'];
	//$info1['bill_black_value']	= $POST['bill_black_value'];
	$info1['product_type']		= $product_detail['product_type'];
	//$info=get_product_tax($dbcon,$POST['taxable_value'],$POST['formulaid']);
	// $info1=array_merge($info1,$info);
	$info1['user_id']	= $_SESSION['user_id'];

	if ($product_detail['product_stock_count'] != "yes") {
		$info1['with_out_stock_invoice'] = 1;
	}
	if ($companyConfiguration['enable_negative_qty'] != 0) {
		$info1['with_out_stock_invoice'] = $companyConfiguration['enable_negative_qty'];
	}

	$table = 'tbl_invoicetrn';
	$tableid = 'trancation_id';

	if (!empty($POST['invoice_id'])) {
		$info1['invoice_id'] = $POST['invoice_id'];
		$info1['trancation_status']	= 0;
	} else {
		$info1['trancation_status']	= 1;
	}

	if (empty($POST['edit_id'])) {
		$inserid = add_record($table, array_merge($info1, $curncy_trn), $dbcon, $POST['branch_id']);
		//add general book entry 
		if (!empty($POST['invoice_id'])) {
			$sel_gen = $dbcon->query("select trn.*,p.product_type,p.ledger_id,i.invoice_date from tbl_invoicetrn as trn 
						left join product_mst as p on trn.product_id=p.product_id
						left join tbl_invoice as i on i.invoice_id = trn.invoice_id
						where trn.trancation_id='$inserid'");

			$r_gen = brp_mysqli_fetch_assoc($sel_gen);

			add_general_book_entry($dbcon, "tbl_invoicetrn", $r_gen['trancation_id'], 1, $r_gen['ledger_id'], $info1['product_amount'], '', $r_gen['invoice_date'], '', $curncy_trn, MODULE_INVOICE, $POST['invoice_id']);
		}
	} else {
		$updateid = update_record($table, array_merge($info1, $curncy_trn), $tableid . "=" . $POST['edit_id'], $dbcon, $POST['branch_id']);

		$sel_edit = $dbcon->query("select general_book_id from tbl_general_book where table_name='tbl_invoicetrn' and table_id='$POST[edit_id]'");
		$r_edit = brp_mysqli_fetch_assoc($sel_edit);

		$info_gen1['amount'] = $info1['product_amount'];
		update_record("tbl_general_book", $info_gen1, " table_id=" . $POST['edit_id'] . "  and table_name='tbl_invoicetrn'", $dbcon);

		$inserid = $POST['edit_id'];
	}

	//Update invoicetrn id For Batchwise product -- dhruv
	if (empty($POST['edit_id'])) {
		$sel_itrn = $dbcon->query("SELECT * FROM tbl_batch_stock_tmp where status=0 and product_id=" . $POST['product_id'] . " and user_id=" . $_SESSION['user_id']);

		if ($sel_itrn->num_rows > 0) {
			$infobatch['invoice_trn_id'] = $inserid;
			$infobatch['status'] = 1;

			while ($r_itrn = brp_mysqli_fetch_array($sel_itrn)) {
				$updateinvoicetrnid = update_record('tbl_batch_stock_tmp', $infobatch, "batch_stk_id=" . $r_itrn['batch_stk_id'], $dbcon);
			}
		}
	}


	/* insert to tax transaction table by Dhruv */
	if (($cgst_tax_per != 0) && ($cgst_tax_rate != 0)) {
		$cl_id = get_ledger_by_name($dbcon, 'CGST');
		$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $cgst_tax_per, $cgst_tax_rate, $inserid, "tbl_invoicetrn", $POST['product_id'], 3, $POST['edit_id'], $POST['branch_id'], $POST['currency_id'], $POST['currency_rate'], $cgst_tax_rate_conv);
	} else {
		$cl_id = get_ledger_by_name($dbcon, 'CGST');
		$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $cgst_tax_per, $cgst_tax_rate, $inserid, "tbl_invoicetrn", $POST['product_id'], 3, $POST['edit_id'], $POST['branch_id'], $POST['currency_id'], $POST['currency_rate'], $cgst_tax_rate_conv, 1);
	}
	if (($sgst_tax_per != 0) && ($sgst_tax_rate != 0)) {
		$cl_id = get_ledger_by_name($dbcon, 'SGST');
		$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $sgst_tax_per, $sgst_tax_rate, $inserid, "tbl_invoicetrn", $POST['product_id'], 3, $POST['edit_id'], $POST['branch_id'], $POST['currency_id'], $POST['currency_rate'], $sgst_tax_rate_conv);
	} else {
		$cl_id = get_ledger_by_name($dbcon, 'SGST');
		$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $sgst_tax_per, $sgst_tax_rate, $inserid, "tbl_invoicetrn", $POST['product_id'], 3, $POST['edit_id'], $POST['branch_id'], $POST['currency_id'], $POST['currency_rate'], $sgst_tax_rate_conv, 1);
	}
	if (($igst_tax_per != 0) && ($igst_tax_rate != 0)) {
		$cl_id = get_ledger_by_name($dbcon, 'IGST');
		$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $igst_tax_per, $igst_tax_rate, $inserid, "tbl_invoicetrn", $POST['product_id'], 3, $POST['edit_id'], $POST['branch_id'], $POST['currency_id'], $POST['currency_rate'], $igst_tax_rate_conv);
	} else {
		$cl_id = get_ledger_by_name($dbcon, 'IGST');
		$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $igst_tax_per, $igst_tax_rate, $inserid, "tbl_invoicetrn", $POST['product_id'], 3, $POST['edit_id'], $POST['branch_id'], $POST['currency_id'], $POST['currency_rate'], $igst_tax_rate_conv, 1);
	}

	// check for the addiotional tax on product Start -- dhaval
	$pro_amt = $POST['product_amount'] * $POST['currency_rate'];
	$count_add_tax = get_check_addition_tax($dbcon, $sale_gst['tax_cat_id'], $POST['product_amount'], $inserid, $POST['product_id'], $POST['edit_id'], $POST['branch_id'], 'tbl_invoicetrn', $POST['currency_id'], $POST['currency_rate'], $pro_amt);

	// check for the addiotional tax on product End  -- dhaval

	/***Update stock trn and allocate table By Dhruv**/


	get_salesorder_invoicedone($dbcon, $POST['trans_id'], $POST['invoice_id']);



	if (!empty($POST['invoice_id'])) {
		//pathik comment code new stock deduct code add so comment start 01-03-2023
		//$info_de['stock_status']=2;
		//$updateid1=update_record("tbl_stock_trn", $info_de,"ref_name='invoice_trn' and ref_id=".$inserid ,$dbcon);
		//pathik comment code new stock deduct code add so comment end 01-03-2023

		$query = "select i.*,sum(trn.product_amount) as gamo from tbl_invoice as i
				left join tbl_invoicetrn as trn on trn.invoice_id=i.invoice_id
				where trn.trancation_status=0 and i.invoice_id=" . $POST['invoice_id'];
		$result = $dbcon->query($query);
		$row = mysqli_fetch_assoc($result);

		//pathik comment code new stock deduct code add so comment start 01-03-2023
		//minus_stock($dbcon,$info1['product_id'],$info1['unit_id'],$row['invoice_date'],"invoice_trn",$inserid,$info1['product_qty']);
		//pathik comment code new stock deduct code add so comment end 01-03-2023

		//pathik new stock deduct code add start 01-03-2023
		if ($row['with_out_stock_invoice'] == 0) {
			deduct_stock_in_invoice($dbcon, $inserid);
		}
		//pathik new stock deduct code add end 01-03-2023

		$general_book_id = get_general_book_id($dbcon, 'tbl_invoice', $POST['invoice_id'], $row['cust_id']);

		// add_general_book_entry($dbcon,"tbl_invoice",$POST['invoice_id'],2,$row['cust_id'],$row['gamo'],$general_book_id,$row['invoice_date']);
		// general_book_tax_entry($dbcon,$POST['invoice_id']);
		// general_book_sercices_entry($dbcon,$POST['invoice_id']);
	}
} else if (strtolower($POST['mode']) == "formulavalue") {
	$rate_total = 0;
	$c_total = $POST['c_total'];
	$qry = "SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=" . $POST['eid'] . " order by tax_value desc";
	$row = $dbcon->query($qry);
	$j = 0;
	//$dis=$POST['total']*$POST['t_dis']/100;
	$rate_total = $total = $POST['total'];
	while ($tax = mysqli_fetch_assoc($row)) {
		if (strpos(strtolower(" " . $tax['tax_name']), "excise") == true) {
			$rate = $total * $tax['tax_value'] / 100;
			$total += $rate;
		} else {
			$rate = ($total) * $tax['tax_value'] / 100;
		}
		echo '<div class="form-group">
				<label class="col-md-5 control-label">' . $tax['tax_name'] . '</label>
				<div class="col-md-5 col-xs-11">
				<input id="taxvalue' . $j . '" name="taxvalue' . $j . '" value= "' . $rate . '" type="text" class="form-control" readonly="readonly">
				</div>
				</div>
				<input id="taxname' . $j . '" name="taxname' . $j . '" value= "' . $tax['tax_name'] . '" type="hidden" class="form-control">';
		$rate_total = $rate_total + $rate;
		$j++;
	}
	$g_total = $rate_total + $c_total;
	echo '<input id="rate" name="rate" value= "' . $g_total . '" type="hidden" class="form-control" >';
} else if (strtolower($POST['mode']) == "load_productdata") {
	$company_config = getCompanyConfiguration($dbcon);
	$pid = $POST['eid'];
	//$qry="select * from tbl_product where product_id=".$POST['eid'];
	$qry = "select mst.*,unit.unit_name from product_mst as mst
			left join unit_mst as unit on unit.unitid = mst.product_base_unit
			where product_id=$pid";
	$result = $dbcon->query($qry);
	$row = brp_mysqli_fetch_assoc($result);
	/*$product_detail = get_product_detail($dbcon, $product_id);*/
	$qry3 = "SELECT h.hsn_id,h.hsn_code,h.sale_gst,t.tax_gst,t.tax_cat_id FROM `mst_hsn_code` as h left join tbl_tax_category as t on t.tax_cat_id=h.sale_gst where h.hsn_status=0 and h.hsn_id=" . $row['product_hsn'] . " ";
	$sale_gst = brp_mysqli_fetch_assoc($dbcon->query($qry3));

	$sale_card = get_product_rate_sales_time($dbcon, $pid, $row['product_base_unit'], $POST['cust_id']);
	//var_dump($sale_card['discount_per']);

	if ($company_config['invoice_calculation_discount_show'] == 1) {
		$row['disc_per']			= $sale_card['discount_per'];
		$row['product_sale_rate']	= $row['product_sale_rate'];
	} else {
		$rate = $row['product_sale_rate'] - (($row['product_sale_rate'] * $sale_card['discount_per']) / 100);
		$row['product_sale_rate'] = $rate;
		$row['disc_per']	= "";
	}


	$qry1 = "select led.stateid as lst,com.stateid as cst from tbl_ledger as led 
			left join tbl_company as com on com.company_id=led.company_id
			where l_id=" . $POST['cust_id'];
	$result1 = $dbcon->query($qry1);
	$row1 = brp_mysqli_fetch_assoc($result1);
	$row['product_hsn'] = '';
	if ($row['product_hsn'] != '') {
		$row['product_hsn'] = $row['product_hsn'];
	}
	echo json_encode(array_merge($row, $sale_gst));
} else if (strtolower($POST['mode']) == "get_batch_qty") {
	$stock_id = $POST['batch_no'];
	$gstock = 0;
	$rstock = 0;
	$batch_no = $POST['batch_no'];
	$gstock = get_current_godown_stock_new($dbcon, $POST['product_id'], $POST['unit_id'], $POST['st_godown_id'], $branch_id, $stock_id);

	$rstock = reserve_stock($dbcon, $POST['product_id'], $POST['unit_id'], $reserve_id, $request_id, $complaint_id, $sales_order_trn_id, $branch_id, $is_store_approval, $p_id, $POST['st_godown_id'], $stock_id);


	$stock = $gstock - $rstock;
	echo $stock;
} else if (strtolower($POST['mode']) == "batch_stock_model_open") {

	/*$query="SELECT * FROM `tbl_stock_trn` WHERE stock_status = 0 and stock_flage = 1 and `product_id` = ".$POST['product_id']." and batch_no != '' group by batch_no";*/
	$query = "select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i
			where stock_status=0 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and product_id = " . $POST['product_id'] . " and batch_no != '' group by batch_no";
	$rs_batch = $dbcon->query($query);
	$str = '<option value="">Choose Batch No</option>';
	while ($rel = brp_mysqli_fetch_assoc($rs_batch)) {
		if ($rel['pending_base_stock'] > 0) {
			$str .= '<option value="' . $rel['stock_id'] . '" data-stock="' . $rel['base_stock'] . '" >' . $rel['batch_no'] . '</option>';
		}
	}

	$html = '<div class="col-md-12">				
			<div class="col-md-5">
			<div class="form-group">
			<label for="edit_zone_name">Batch No</label>
			<select class="form-control batch_select2" name="batch_id" id="batch_id" onChange="get_batch_qty(this.value);" >
			"' . $str . '"
			</select>							
			</div>	
			</div>
			<div class="col-md-3">
			<div class="form-group">
			<label for="edit_zone_name">Total Qty</label>
			<input type="number" min="0" class="form-control" name="batch_stock" id="batch_stock" readonly />
			</div>	
			</div>

			<div class="col-md-3">
			<div class="form-group">
			<label for="edit_zone_name">Qty</label>
			<input type="number" min="0" class="form-control numbersOnly" name="qtyforbatch" id="qtyforbatch" />
			</div>	
			</div>

			<div class="col-md-1">
			<div class="form-group">
			<input type="button" id="add_batch_qty" value="+"  class="btn btn-primary" title="Add" onclick="add_batch_qty();" 
			style="margin-top: 24px;"  />
			</div>	
			</div>

			</div>';
	$row['html_data'] = $html;
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "add_batch_qty") {

	if (!empty($POST['edit_id'])) {
		$str = " and invoice_trn_id=" . $POST['edit_id'] . " and status=1 ";
		$info['invoice_trn_id']   = $POST['edit_id'];
		$info['status']   = 1;
	} else {
		$str = " and invoice_trn_id=0 and status=0 ";
	}

	$tr = $dbcon->query("SELECT stock_id FROM tbl_batch_stock_tmp where stock_id=" . $POST['stock_id'] . " " . $str . " ");
	if ($tr->num_rows > 0) {
		$row['res'] = '-1';
	} else {
		$info['product_id']   = $POST['product_id'];
		$info['stock_id']   = $POST['stock_id'];
		$info['qty']   		= $POST['qty'];
		$info['unitid']   	= $POST['unit_id'];
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];

		$inserbatchstockid = add_record('tbl_batch_stock_tmp', $info, $dbcon);

		if ($inserbatchstockid) {
			$row['res'] = "1";
		} else {
			$row['res'] = "0";
		}
	}
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "load_product_unit") {

	$query1 = "SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name FROM product_mst as promst
				left join unit_mst as umst on umst.unitid=promst.product_base_unit
				left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
				WHERE product_id=" . $POST['product_id'];
	//var_dump($POST);
	$rs_type1 = $dbcon->query($query1);
	$row1 = brp_mysqli_fetch_assoc($rs_type1);
	$rate_unit = "";
	if ($POST['rate_unit']) {
		$rate_unit = $POST['rate_unit'];
	}
	if ($row1['product_base_unit'] != $row1['product_conv_unit']) {
		$row1['unit_status'] = "1";
		$base_sel = "";
		$conv_sel = "";
		if (empty($POST['edit_id'])) {
			if ($row1['product_base_unit'] == $POST['rate_unit']) {
				$base_sel = "selected=='selected'";
			}
			if ($row1['product_conv_unit'] == $POST['rate_unit']) {
				$conv_sel = "selected=='selected'";
			}
		} else {
			$query_de = "select * from tbl_purchaseordertrn where purchaseordertrn_id=" . $POST['edit_id'];
			$exe = $dbcon->query($query_de);
			$del_ro = brp_mysqli_fetch_array($exe);

			if ($row1['product_base_unit'] == $del_ro['unit_wise']) {
				$base_sel = "selected=='selected'";
			}

			if ($row1['product_conv_unit'] == $del_ro['unit_wise']) {
				$conv_sel = "selected=='selected'";
			}
		}


		$opt = '<option ' . $base_sel . ' value="' . $row1['product_base_unit'] . '">' . $row1['base_unit_name'] . '</option>';
		$opt .= '<option ' . $conv_sel . ' value="' . $row1['product_conv_unit'] . '">' . $row1['convert_unit_name'] . '</option>';
	} else {
		$row1['unit_status'] = "0";
		$opt .= '<option value="' . $row1['product_base_unit'] . '">' . $row1['base_unit_name'] . '</option>';
	}
	//echo $opt;
	$row1['unit_option'] = $opt;
	//$row1['qye']=$query1;
	//var_dump($row1);
	echo json_encode($row1);
} else if (strtolower($POST['mode']) == "convert_qty") {

	//var_dump($POST);
	$row = array();
	if ($POST["type"] == "1") {
		$type = "base_unit";
		$ret_qty = convert_stock($dbcon, $_POST['base_qty'], $POST['product_id'], $type);
	} else if ($POST["type"] == "2") {
		$type = "conv_unit";
		$ret_qty = convert_stock($dbcon, $_POST['conv_qty'], $POST['product_id'], $type);
	} else {
		$ret_qty = "0";
	}
	//var_dump($ret_qty);
	$ret_qty_new = number_format($ret_qty, 4, ".", "");
	//$ret_qty=$ret_qty;
	//	echo $ret_qty;
	$row['show_qty'] = $ret_qty_new;
	$row['hide_qty'] = $ret_qty;
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "validate_qty") {

	if (!empty($POST['edit_id'])) {
		$str = " and bst.invoice_trn_id=" . $POST['edit_id'] . " and bst.status=1 ";
	} else {
		$str = " and bst.invoice_trn_id=0 and bst.status=0 ";
	}
	$qry2 = "SELECT sum(bst.qty) as qty FROM tbl_batch_stock_tmp as bst left join `tbl_stock_trn` as st on st.stock_id=bst.stock_id where st.product_id=" . $POST['product_id'] . " " . $str . " ";

	$result2 = mysqli_fetch_assoc($dbcon->query($qry2));
	$total_qty = $result2['qty'] + $POST['qtyforbatch'];
	if ($total_qty > $POST['product_qty']) {
		$row['res'] = "0";
	} else if ($total_qty == $POST['product_qty']) {
		$row['res'] = "1";
	} else {
		$row['res'] = "2";
	}

	echo json_encode($row);
} else if (strtolower($POST['mode']) == "fetch_batch_qty") {

	if (!empty($POST['edit_id'])) {
		$str = " and bst.invoice_trn_id=" . $POST['edit_id'] . " and bst.status=1 ";
	} else {
		$str = " and bst.status=0";
	}
	$appData = array();
	$i = 1;
	$aColumns = array('bst.qty', 'st.batch_no', 'bst.batch_stk_id');
	$sTable = "tbl_batch_stock_tmp as bst";
	$isJOIN = array('left join `tbl_stock_trn` as st on st.stock_id=bst.stock_id');
	$sIndexColumn = "st.stock_id";
	$where = "  st.product_id='" . $POST['product_id'] . "' " . $str . " ";
	$isWhere = array($where);
	$hOrder = "st.stock_id desc";
	include($path . 'include/pagging.php');
	$id = 1;
	$edit = $delete = '';
	foreach ($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['batch_no'];
		$row_data[] = $row['qty'];
		$delete = '';


		$delete = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_batch_stock_entry(' . $row['batch_stk_id'] . ')"><i class="fa fa-trash-o"></i></button>';


		$row_data[] = $delete;

		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode($output);
} else if (strtolower($POST['mode']) == "delete_batch_entry") {
	$row = array();
	$info['status'] = 2;

	$updateid = update_record("tbl_batch_stock_tmp", $info, "batch_stk_id=" . $POST['batchstockid'], $dbcon);

	if ($updateid)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "load_product_typeiwse") {
	echo get_product($dbcon, "", $POST['type_id']);
} else if (strtolower($POST['mode']) == "get_product_amount") {
	$arr = get_product_tax($dbcon, $POST['product_amount'], $POST['formulaid']);
	echo json_encode($arr);
} else if (strtolower($POST['mode']) == "load_podata") {
	getpono($dbcon, $POST['cust_id']);
} else if (strtolower($POST['mode']) == "load_podate") {
	$qry2 = "select * from tbl_pono where po_id=" . $POST['po_id'];
	$result2 = mysqli_fetch_assoc($dbcon->query($qry2));
	echo json_encode($result2);
} else if (strtolower($POST['mode']) == "reminder") {
	$qry2 = "select * from pay_terms where terms_id=" . $POST['paymentterms'];
	$result2 = mysqli_fetch_assoc($dbcon->query($qry2));
	echo json_encode($result2);
} else if (strtolower($POST['mode']) == "get_series_no") {
	$query = "select * from tbl_invoicetype where status=0 and type_id=37 and company_id= " . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
	$result = $dbcon->query($query);
	$row = mysqli_fetch_assoc($result);
	echo $row['invoicetype_id'];
} else if (strtolower($POST['mode']) == "load_invoiceno") {
	$row = array();
	$query1 = "select * from  tbl_invoicetype where invoicetype_id=" . $POST['typeid'];
	$rows = mysqli_fetch_assoc($dbcon->query($query1));
	$id = $rows['taxinvoice_start'];
	$id = $id + 1;
	//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
	//$end = $start+1;
	if ($rows['invoice_format'] == '2') {
		$row['invoiceno'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
	} else if ($rows['invoice_format'] == '1') {
		$row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
	} else if ($rows['invoice_format'] == '3') {
		$row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
	} else {
		$row['invoiceno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
	}
	$row['challanno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "update_total") {

	//update total , net total , general books entry at edit time start - dhaval 
  // Make sure both keys and values exist and are arrays
    if (!empty($_POST['bill_sundry_tax']) && !empty($_POST['bill_sundry_tax1']) 
        && is_array($_POST['bill_sundry_tax']) && is_array($_POST['bill_sundry_tax1'])) {
        
        $bill_sundry_tax = array_combine($_POST['bill_sundry_tax'], $_POST['bill_sundry_tax1']);
    } else {
        $bill_sundry_tax = []; // default empty array
    }
	if ($POST['invoice_id'] > 0) {
		$query = "select sales_ledger_id,cust_id from tbl_invoice where invoice_id=" . $POST['invoice_id'] . " ";
		$rel = brp_mysqli_fetch_assoc($dbcon->query($query));

		if ($POST['currency_id'] == $_SESSION['currency_id']) {
			$update_invoice['g_total'] 		= $POST['g_total'];
			$update_invoice['basic_total'] 	= $POST['basic_total'];
			$update_invoice['g_total_conv']	= $POST['g_total'] * $POST['currency_rate'];
			$update_invoice['basic_total_conv']	= $POST['basic_total'] * $POST['currency_rate'];
		} else {
			$update_invoice['g_total'] 		= $POST['g_total'] * $POST['currency_rate'];
			$update_invoice['basic_total'] 	= $POST['basic_total'] * $POST['currency_rate'];
			$update_invoice['g_total_conv']	= $POST['g_total'];
			$update_invoice['basic_total_conv']	= $POST['basic_total'];
		}

		$update_invoice['round_off'] =  $POST['round_off'];
		$update_invoice['round_off_conv'] =  $POST['round_off'];
		//$update_invoice['ref_date'] = date('Y-m-d',strtotime($POST['invoice_date']));
		update_record("tbl_invoice", $update_invoice, " invoice_id=" . $POST['invoice_id'], $dbcon);

		//Update Basic total in General book for invoice table - sales ledger entry
		$info_gen['amount'] = $update_invoice['basic_total'];
		$info_gen['ref_date'] = date('Y-m-d', strtotime($POST['invoice_date']));
		update_record("tbl_general_book", $info_gen, " table_id=" . $POST['invoice_id'] . " and ledger_id=" . $rel['sales_ledger_id'] . " and table_name='tbl_invoice'", $dbcon);

		//Update Basic total in General book for invoice table - customer ledger entry
		$info_gen1['amount'] = $update_invoice['g_total'];
		$info_gen1['ref_date'] = date('Y-m-d', strtotime($POST['invoice_date']));
		update_record("tbl_general_book", $info_gen1, " table_id=" . $POST['invoice_id'] . " and ledger_id=" . $rel['cust_id'] . " and ref_by='' and genral_book_status=0  and table_name='tbl_invoice'", $dbcon);

		//update bill sundry in bill sundry table and general table 

		foreach ($bill_sundry_tax as $bill_sundry_tax_id => $bill_sundry_tax_amount) {
			if ($POST['currency_id'] == $_SESSION['currency_id']) {
				$info_sundry_tax['sundry_amount'] = $bill_sundry_tax_amount;
				$info_sundry_tax['sundry_amount_conv'] = $bill_sundry_tax_amount * $POST['currency_rate'];
			} else {
				$info_sundry_tax['sundry_amount'] = $bill_sundry_tax_amount * $POST['currency_rate'];
				$info_sundry_tax['sundry_amount_conv'] = $bill_sundry_tax_amount;
			}

			$info_sundry_tax['cdate']	= date("Y-m-d H:i:s");
			$info_sundry_tax['user_id']	= $_SESSION['user_id'];
			$info_sundry_tax['company_id']	= $_SESSION['company_id'];
			$info_sundry_tax['ref_date'] = date('Y-m-d', strtotime($POST['invoice_date']));
			$update_sundryid = update_record("tbl_bill_sundry_transaction", $info_sundry_tax, " sundry_ledger_id=" . $bill_sundry_tax_id . " and sundry_voucher_table='tbl_invoice' and sundry_voucher_id='$POST[invoice_id]'", $dbcon);

			$query1 = "select * from tbl_bill_sundry_transaction where sundry_voucher_id='$POST[invoice_id]' and sundry_voucher_table='tbl_invoice' and sundry_ledger_id=" . $bill_sundry_tax_id . " and isdelete=0  ";
			$rel1 = brp_mysqli_fetch_assoc($dbcon->query($query1));

			$info_general_sundry['amount'] = $info_sundry_tax['sundry_amount'];
			$info_general_sundry['cdate']	= date("Y-m-d H:i:s");
			$info_general_sundry['user_id']	= $_SESSION['user_id'];
			$info_general_sundry['company_id']	= $_SESSION['company_id'];
			$info_general_sundry['ref_date'] = date('Y-m-d', strtotime($POST['invoice_date']));
			update_record("tbl_general_book", $info_general_sundry, " ledger_id=" . $bill_sundry_tax_id . " and table_name='tbl_bill_sundry_transaction' 
						and table_id= " . $rel1['sundry_id'] . " ", $dbcon);
			//add_general_book_entry($dbcon,"tbl_bill_sundry_transaction",$sundry_tax_insert,1,$bill_sundry_tax_id,$bill_sundry_tax_amount,'',$POST['invoice_date'],'',$curncy_trn);

			//echo $bill_sundry_tax_id.'-'.$bill_sundry_tax_amount."<br>";
		}

		/* $dsun = $dbcon->query("select * from tbl_bill_sundry_transaction where sundry_voucher_id='$POST[invoice_id]' and isdelete='0'");
			    while($r=brp_mysqli_fetch_array($dsun))
				{
					
					$sundry_id = $r['sundry_id'];
					
					$sundry['sundry_amount'] = $r['sundry_amount'];
					$sundry['cdate']			= date("Y-m-d H:i:s A");
					$sundry['user_id']			= $_SESSION['user_id'];
					$sundry['company_id']		= $_SESSION['company_id'];					
					
					update_record("tbl_bill_sundry_transaction",$sundry," sundry_id=".$sundry_id." and sundry_voucher_table='tbl_invoice'" ,$dbcon);
									
					$sundry_general['amount'] = $r['sundry_amount'];
					$sundry_general['entry_type'] = 1;
					
					$sundry_general['branch_id'] = $POST['branch_id'];
					$sundry_general['cdate']			= date("Y-m-d H:i:s A");
					$sundry_general['user_id']			= $_SESSION['user_id'];
					$sundry_general['company_id']		= $_SESSION['company_id'];
					
					
					update_record("tbl_general_book", $sundry_general," table_id=".$sundry_id." and table_name='tbl_bill_sundry_transaction'" ,$dbcon);
					
				
				} */
	}
	//update total , net total , general books entry at edit time end - dhaval 

	//print_r($bill_sundry_tax);
	//print_r($bill_sundry_addon);

} else if (strtolower($POST['mode']) == "load_tempoutward") {
	$getspecialConfiguration = getspecialConfiguration($dbcon);
	$companyConfiguration = getCompanyConfiguration($dbcon);
	$sales_pro_search = explode(",", $companyConfiguration['sales_pro_search']);

	if ($POST['eid']) {
		$query = "select mst.*,product.product_name,product.product_icode,dr.drawing_number,product.product_alias_name,product.product_type,product.product_base_unit,cat.unit_name as base_unit,ccat.unit_name as conv_unit, rcat.unit_name as rat_unit, cate.cat_name, pcat.cat_name as pcat_name  from  tbl_invoicetrn as mst
				left join unit_mst as cat on cat.unitid  =mst.unit_id 
				left join unit_mst as ccat on ccat.unitid=mst.conv_unit_id 
				left join unit_mst as rcat on rcat.unitid=mst.rate_unit 
				left join product_mst as product on product.product_id=mst.product_id
				left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
				left join tbl_category as cate on cate.cat_id = product.product_category
				left join tbl_category as pcat on pcat.cat_id = product.parent_category
				where trancation_status=0 and invoice_id=" . $POST['eid'];
	} else {
		$query = "select mst.*,product.product_name,product.product_icode,dr.drawing_number,product.product_alias_name,product.product_type,cat.unit_name as base_unit,ccat.unit_name as conv_unit, rcat.unit_name as rat_unit,product.product_base_unit, cate.cat_name, pcat.cat_name as pcat_name from  tbl_invoicetrn as mst
				left join unit_mst as cat on cat.unitid=mst.unit_id
				left join unit_mst as ccat on ccat.unitid=mst.conv_unit_id 
				left join unit_mst as rcat on rcat.unitid=mst.rate_unit 
				left join product_mst as product on product.product_id=mst.product_id
				left join tbl_drawing as dr on dr.drawing_id = product.drawing_id  
				left join tbl_category as cate on cate.cat_id = product.product_category
				left join tbl_category as pcat on pcat.cat_id = product.parent_category
				where trancation_status=1 and mst.user_id=" . $_SESSION['user_id'];
	}
	//echo $query;
	$str = "";
	/*$query="select mst.*,product.product_name,cat.unit_name,m.model_name from  tbl_invoicetrntemp as mst 
			left join unit_mst as cat on cat.unitid=mst.unit_id left join product_mst as product on product.product_id=mst.product_id left join model_mst as m on m.model_id=mst.model_id  where temp_status=0 and mst.user_id=".$_SESSION['user_id']." order by tempinvoicetrn_id Desc";*/
	$result = $dbcon->query($query);
	$str .= ' <div class="form-group">
			<div class="col-md-12 col-xs-11">
			<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
			<tr id="field">';
	if ($getspecialConfiguration['reciclar'] == 1) {
		$str .= '<th class="text-center" width="8%">Parent Category</th>
				<th class="text-center" width="8%">Category</th>';
	}

	$str .= '<th class="text-center" width="25%">Product Name</th>
			<th class="text-center" width="8%">Qty</th>
			<th class="text-center" width="8%">Rate <span class="currency_icon"></span></th>
			
			<th class="text-center" width="8%">Discount <span class="currency_icon"></span></th>
			<th class="text-center" width="8%">Tax Details <span class="currency_icon"></span></th>
			<th class="text-center" width="12%">Amount</th>
			<th class="text-center" width="10%">Action</th>
			</tr>';
	if (mysqli_num_rows($result) > 0) {
		$i = 1;
		$j = 0;
		while ($rel = mysqli_fetch_assoc($result)) {
			if (!empty($rel['currency_id'])) {
				$currency = getcurrencydetail($dbcon, $rel['currency_id']);
			} else {
				$currency = getcurrencydetail($dbcon, $_SESSION['currency_id']);
			}
			$cnt_pro_stk = '';
			$product_type_arr = array("0", "1", "2", "3", "4", "5", "15", "16", "17", "18", "19", "20");
			if (in_array($rel['product_type'], $product_type_arr)) {
				if (!empty($rel['unit_id'])) {
					$unit_id = $rel['unit_id'];
				} else {
					$unit_id = $rel['product_base_unit'];
				}
				$current_stock = get_current_stock_new($dbcon, $rel['product_id'], $unit_id);
				$where = " and trancation_status!='2' and invoice_id='0'";
				$unclear_qty = get_unclear_stock($dbcon, $rel['product_id'], $unit_id, 'tbl_invoicetrn', 'product_qty', 'product_id', $where);
				$cnt_pro_stk = $current_stock - $unclear_qty;
			} else {
				$cnt_pro_stk = 9999;
			}

			if ($rel['unit_id'] === $rel['rate_unit']) {
				$sqty = $rel['product_qty'];
			} else {
				$sqty = $rel['product_conv_qty'];
			}

			if ($rel['unit_id'] != $rel['conv_unit_id']) {
				$qty_lb = '<strong style="color:green;">Base Qty</strong> :' . number_format($rel['product_qty'], 4, '.', '') . ' ' . $rel['base_unit'] . '<br><strong style="color:green;">Conv. Qty</strong> :' . number_format($rel['product_conv_qty'], 4, '.', '') . ' ' . $rel['conv_unit'];
			} else {
				$qty_lb = '<strong style="color:green;">Base Qty</strong> :' . number_format($rel['product_qty'], 4, '.', '') . ' ' . $rel['base_unit'];
			}
			//var_dump($cnt_pro_stk);
			$product_name = $dbcon->real_escape_string($rel['product_name']);

			$so = "select * from tbl_sales_ordertrn where sales_ordertrn_id=" . $rel['so_allocation_id'];
			$so_exe = $dbcon->query($so);
			$so_row = mysqli_fetch_array($so_exe);

			$with_out_stock_invoice = "";
			if ($POST['isstockngative'] == '') {

				if ($cnt_pro_stk <= $rel['product_qty']) {

					$with_out_stock_invoice = "<strong style='color:red;' >Product stock is not enough.</strong>";
					$j++;
				}
			}
			//$str .= $j;

			$currency_id = $rel['currency_id'];
			$rate_label = '';
			$product_amount_label = '';
			$product_total_label = '';
			$product_discount_label = '';
			$selectCu = "SELECT * FROM tbl_currency WHERE currency_id='" . $currency_id . "' ";
			$curenresult = $dbcon->query($selectCu);
			$vrel = brp_mysqli_fetch_assoc($curenresult);

			if ($currency_id != 0) {

				if ($vrel['currency_id'] != $_SESSION['currency_id']) {
					$str .= '<input type="hidden" id="currency_type_response" value="' . $vrel['currency_code'] . '">';
					// 			$rate_label .= $vrel['currency_symbol'].' :' .$rel['product_rate']."<br>";
					$rate_label .=  $vrel['currency_symbol'] . ' :' . $rel['product_rate_conv'];

					// $product_amount_label .= $vrel['currency_symbol'].' :' .$rel['product_amount']."<br>";
					$product_amount_label .=  $vrel['currency_symbol'] . ' :' . $rel['product_amount_conv'];

					$product_total_label .= $vrel['currency_symbol'] . ' :' . $rel['product_amount_conv'] . "<br>";

					$product_discount_label .= $vrel['currency_symbol'] . ' :' . $rel['product_discount_conv'] . "<br>";
					//$product_total_label .=  $vrel['currency_symbol'].' :' .$rel['currency_total'];

				} else {
					$rate_label .= $vrel['currency_symbol'] . ' :' . number_format($rel['product_rate'], 2, '.', '');
					$product_amount_label .= $vrel['currency_symbol'] . ' :' . $rel['product_amount'];
					$product_total_label .= $vrel['currency_symbol'] . ' :' . $rel['product_amount'];

					$product_discount_label .= $vrel['currency_symbol'] . ' :' . $rel['product_discount'] . "<br>";
				}
			} else {
				$rate_label .= $_SESSION['currency_name'] . ' :' . number_format($rel['product_rate'], 4, '.', '');
				$product_amount_label .= $_SESSION['currency_name'] . ' :' . $rel['product_amount'];
				$product_total_label .= $_SESSION['currency_name'] . ' :' . $rel['product_amount'];

				$product_discount_label .= $vrel['currency_symbol'] . ' :' . $rel['product_discount'] . "<br>";
			}

			$cgst_tax = "";
			$sgst_tax = "";
			$igst_tax = "";

			if ($rel['cgst_tax_per'] != 0) {
				$cgst_tax = "<Strong>CGST (" . $rel['cgst_tax_per'] . ") : </strong>" . $currency['currency_symbol'] . " " . (($rel['currency_id'] == $_SESSION['currency_id']) ? $rel['cgst_tax_rate'] : $rel['cgst_tax_rate_conv']) . '<br>';
			}

			if ($rel['sgst_tax_per'] != 0) {
				$sgst_tax = "<Strong>SGST (" . $rel['sgst_tax_per'] . ") : </strong>" . $currency['currency_symbol'] . " " . (($rel['currency_id'] == $_SESSION['currency_id']) ? $rel['sgst_tax_rate'] : $rel['sgst_tax_rate_conv']) . '<br>';
			}

			if ($rel['igst_tax_per'] != 0) {
				$igst_tax = "<Strong>IGST (" . $rel['igst_tax_per'] . ") : </strong>" . $currency['currency_symbol'] . " " . (($rel['currency_id'] == $_SESSION['currency_id']) ? $rel['igst_tax_rate'] : $rel['igst_tax_rate_conv']) . '<br>';
			}

			//sales ored number 
			$so_details = '';
			if ($rel['transaction_type'] == 1) {
				$so_details = "<strong style='color:blue'> Sales Order No:  </strong><strong style='color:red'>" . get_id_detail($dbcon, 'tbl_sales_order', 'sales_order_id', $so_row['sales_order_id'], 'sales_order_no') . '</strong>';
			} else if ($rel['transaction_type'] == 2) {
				$so_details = "<strong style='color:blue'> Sales Order No:  </strong><strong style='color:red'>" . get_sales_order_by_allocation($dbcon, $rel['so_allocation_id']) . "</strong>" . "<br>" . "<strong style='color:green'>(Allocated)</strong>";
			} else {
				$so_details = '';
			}

			if (in_array('drawing', $sales_pro_search)) {
				$drawing_number = " -- (" . $rel['drawing_number'] . ")";
			}
			if (in_array('item', $sales_pro_search)) {
				$item_code = " -- (" . $rel['product_icode'] . ")";
			}
			if (in_array('alias', $sales_pro_search)) {
				$alias = " -- (" . $rel['product_alias_name'] . ")";
			}

			$str .= '<tr id="fieldtr' . $id . '" >';

			if ($getspecialConfiguration['reciclar'] == 1) {
				$str .= '<td style="vertical-align:top;" class="text-center">' . $rel['cat_name'] . '</td>
						<td style="vertical-align:top;" class="text-center">' . $rel['pcat_name'] . '</td>';
			}

			$str .= '<td style="vertical-align:top;" class="text-center">
					<b>' . $rel['product_name'] . ' ' . $item_code . ' ' . $drawing_number . ' ' . $alias . '<br>' . $so_details . '<br><strong style="color:green">Current Stock : ' . $cnt_pro_stk . '</strong><br>' . $with_out_stock_invoice . '</b><br>
					</td>
					
					<td style="vertical-align:top;" class="text-center">
					<strong style="color:green">Rate Qty</strong> :' . number_format($sqty, 4, '.', '') . ' ' . $rel['rat_unit'] . '<br>' . $qty_lb . '
					<input type="hidden" id="trn_pro_stk' . $i . '" name="trn_pro_stk[]" value="' . $rel['product_qty'] . '">
					<input type="hidden" id="cnt_pro_stk' . $i . '" name="cnt_pro_stk[]" value="' . $cnt_pro_stk . '">						

					';

			$str .= '</td>
					<td style="vertical-align:top;" class="text-center">
					' . $rate_label . '
					</td>				
					
					<td style="vertical-align:top" class="text-center">
					' . $product_discount_label . ' (' . $rel['discount_per'] . '%)
					</td>
					<td>
					' . $cgst_tax . '' . $sgst_tax . '' . $igst_tax . '
					</td>
					<td style="vertical-align:top" class="text-center">
					' . ($product_amount_label) . '<br>

					</td>
					
					<input type="hidden" name="amount[]" id="amount' . $i . '" value="' . (($rel['currency_id'] == $_SESSION['currency_id']) ? $rel['product_amount'] : $rel['product_amount_conv']) . '"/>
					<td style="vertical-align:top">
					<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data(' . $rel['trancation_id'] . ',\' tbl_invoicetrn\',\'trancation_id\');" id="fieldedit' . $i . '"><i class="fa fa-pencil"></i></button>
					<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data(' . $rel['trancation_id'] . ',\' tbl_invoicetrn\',\'trancation_id\');" id="fieldremove' . $i . '"><i class="fa fa-times"></i></button>
					</td>	
					</tr>';
			$i++;
			if ($rel['product_type'] != "8") {
				$sales_account_amount = $sales_account_amount + $rel["taxable_value"];
			}
		}
	} else {
		$str .= '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
	}

	$str .= '<input type="hidden" name="sales_account_amount" id="sales_account_amount" value="' . $sales_account_amount . '" />
			</table>			 
			</div></div>';
	$row['html_data'] = $str;
	if ($j > 0) {
		$row['stock'] = "1";
	}
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "preedit") {
	$q = $dbcon->query("SELECT mst.*,pro.product_name,cat.unit_name,pro.product_gst,pro.batch_wise_stock_manage, pro.parent_category, pro.product_category, (select sum(product_qty) as invoice_qty from tbl_invoicetrn as itr where itr.trancation_status=0 and trancation_id!='$POST[id]' and itr.sales_ordertrn_id = mst.sales_ordertrn_id) as invoice_qty , strn.product_qty as salesorder_qty 
				FROM tbl_invoicetrn as mst 
				left join tbl_sales_ordertrn as strn on strn.sales_ordertrn_id = mst.sales_ordertrn_id
				left join unit_mst as cat on cat.unitid = mst.unit_id
				left join product_mst as pro on mst.product_id=pro.product_id 
				left join unit_mst as unit on unit.unitid WHERE trancation_id = '$POST[id]'");
	$r = $q->fetch_assoc();

	$r['remaning_invoice_qty'] = $r['salesorder_qty'] - $r['invoice_qty'];

	/*if(strtolower($POST['table'])=='tbl_invoicetrntemp')
			{
				$row['producthtml']=getproduct($dbcon,0,'0,2');
			}
			else
			{
					$row['producthtml']=getproduct($dbcon,0,'0,2');
				}*/
	//$r['producthtml'] = getrequiredproduct($dbcon,$r['product_id'],' and product_type='.$r["product_type"].'');

	echo json_encode($r);
} else if (strtolower($POST['mode']) == "delete_data") {
	//echo '<pre>';print_r($POST);exit;
	$row = array();
	$info['trancation_status'] = 2;

	$updateid = update_record("tbl_invoicetrn", $info, "trancation_id=" . $POST['eid'], $dbcon);

	$info_de['stock_status'] = 2;
	$updateid1 = update_record("tbl_stock_trn", $info_de, "ref_name='invoice_trn' and ref_id=" . $POST['eid'], $dbcon);

	// $info_gen['genral_book_status']=2;	
	// $updateid1=update_record("tbl_general_book", $info_gen, "table_name='tbl_invoicetrn' and table_id=".$POST['eid'] , $dbcon);

	//update tax transaction table By Dhruv
	$info_tax['tx_status'] = 2;
	$updatetax = update_record("tbl_tax_trn", $info_tax, "tx_transaction_type='tbl_invoicetrn' and tx_transaction_id=" . $POST['eid'], $dbcon);

	$query = "select * from tbl_invoicetrn where trancation_id=" . $POST['eid'] . " ";
	$prel = mysqli_fetch_assoc($dbcon->query($query));


	// if($prel['invoice_id']!=0){
	// 	$general_book_id=get_general_book_id($dbcon,'tbl_invoice',$prel['invoice_id'],$prel['cust_id']);

	// 	$query1="select sum(product_amount) as gamo from tbl_invoicetrn as trn left join tbl_invoice as mst on mst.invoice_id=trn.invoice_id where trancation_status=0 and invoice_id=".$prel['invoice_id']." order by trancation_id DESC";
	// 	$prel1=mysqli_fetch_assoc($dbcon->query($query1));

	// 	add_general_book_entry($dbcon,"tbl_invoice",$prel['invoice_id'],2,$prel['cust_id'],$prel1['gamo'],$general_book_id,$prel['invoice_date']);
	// 	general_book_tax_entry($dbcon,$prel['invoice_id']);
	// }

	/***Update stock trn and allocate table By Dhruv**/
	get_salesorder_invoicedone($dbcon, $prel['sales_ordertrn_id'], $prel['invoice_id']);
	/*if($prel['transaction_type'] == 1){
					$info_so_trans['remaning_invoice_qty'] = $prel['product_qty'];
					$info_so_trans['invoice_status'] = 0;
					$update_sotransid=update_record('tbl_sales_ordertrn', $info_so_trans,"sales_ordertrn_id=".$prel['so_allocation_id'] , $dbcon);
				}
				if($prel['transaction_type'] == 2){
					$info_alloc_trans['remaning_invoice_qty'] = $prel['product_qty'];
					$update_alloctransid=update_record('tbl_sales_order_production_trn', $info_alloc_trans,"sales_order_production_trn_id=".$prel['so_allocation_id'] , $dbcon);
				}	*/


	// general book entry check for service and capital goods items 

	$info_general_book['genral_book_status'] = 2;

	update_record('tbl_general_book', $info_general_book, "table_name='tbl_invoicetrn' and table_id=" . $POST['eid'], $dbcon);

	if ($updateid)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "last_rate") {
	$query = "select product_rate,trancation_id,trancation_status,product_id from tbl_invoicetrn as trn left join tbl_invoice as mst on mst.invoice_id=trn.invoice_id where cust_id=" . $POST["cust_id"] . " and product_id=" . $POST["product_id"] . " and trancation_status=0 order by trancation_id DESC";
	$prel = mysqli_fetch_assoc($dbcon->query($query));
	echo $prel['product_rate'];
} else if (strtolower($POST['mode']) == "load_consignee") {
	echo get_custmer_consignee($dbcon, $POST['cust_id']);
} else if (strtolower($POST['mode']) == "load_sales_order") {
	echo get_sales_order($dbcon, $POST['cust_id'], $POST['branch_id']);
} else if (strtolower($POST['mode']) == "load_sales_order_data") {

	//$deleteid=delete_record('tbl_invoicetrn',"trancation_status=3 and user_id=".$_SESSION['user_id'], $dbcon);

	$query_inv = "select * from tbl_invoicetrn as trn 
				where trn.trancation_status=3 and trn.user_id=" . $_SESSION['user_id'] . " and trn.company_id=" . $_SESSION['company_id'];
	$rs_dispatch_inv = $dbcon->query($query_inv);
	while ($rel_inv = mysqli_fetch_assoc($rs_dispatch_inv)) {
		$info_inv['trancation_status'] = 2;
		$updateid_in = update_record('tbl_invoicetrn', $info_inv, "trancation_id=" . $rel_inv['trancation_id'], $dbcon);

		$info_utax['tax_used_status'] = 2;
		$updateidutax = update_record('tbl_used_tax', $info_utax, "table_name='tbl_invoicetrn' and table_id='trancation_id' and used_transaction_id=" . $rel_inv['trancation_id'], $dbcon);
	}



	/* $q = $dbcon -> query("SELECT * from tbl_sales_order where sales_order_id=".$POST['sales_order_id']);
			$rel = $q->fetch_assoc();
			
			$resp['transport_id'] = $rel['transport_id'];
			$resp['sales_order_no'] = $rel['sales_order_no'];
			$resp['sales_order_date'] = date("d-m-Y",strtotime($rel['sales_order_date']));
			$resp['pro_html'] = get_sales_order_data($dbcon,$POST['sales_order_id']);
			echo json_encode($resp); */

	$query = "select * from tbl_sales_ordertrn as trn 
			where trn.sales_ordertrn_status=0 and trn.invoice_status=0 and trn.sales_order_id=" . $POST['sales_order_id'];
	$rs_dispatch = $dbcon->query($query);
	while ($rel = mysqli_fetch_assoc($rs_dispatch)) {
		$resve_stoc = reserve_stock($dbcon, $rel['product_id'], $rel['unit_id'], "", "", "", $rel['sales_ordertrn_id'], $rel['branch_id']);

		if ($resve_stoc > 0) {

			$query_used = "select IFNULL(sum(product_qty),0) as used_qty from tbl_invoicetrn as trn 
					where trn.trancation_status=0 and trn.sales_ordertrn_id=" . $rel['sales_ordertrn_id'];
			$rs_dispatch_used = $dbcon->query($query_used);
			$rel_used = mysqli_fetch_assoc($rs_dispatch_used);

			$pending_qty = $rel['product_qty'] - $rel_used['used_qty'];
			if ($pending_qty > 0) {
				if ($pending_qty >= $resve_stoc) {
					$product_qty = $resve_stoc;
				} else {
					$product_qty = $pending_qty;
				}

				$total_value = $product_qty * $rel['product_rate'];
				if ($rel['discount_per'] > 0) {
					$discount_amount = ($total_value * $rel['discount_per']) / 100;
				} else {
					$discount_amount = 0;
				}
				$taxablevalue = $total_value - $discount_amount;
				$product_amount = find_with_tax_amount($dbcon, $rel['formulaid'], $taxablevalue);

				$info1['product_id']		= $rel['product_id'];
				$info1['product_hsn_code']	= $rel['product_hsn_code'];
				$info1['product_qty']		= $product_qty;
				$info1['product_rate']		= $rel['product_rate'];
				$info1['unit_id']			= $rel['unit_id'];
				$info1['product_discount']	= $discount_amount;
				$info1['discount_per']		= $rel['discount_per'];
				$info1['formulaid']			= $rel['formulaid'];
				$info1['company_id']		= $_SESSION['company_id'];
				$info1['product_amount']	= $product_amount;
				$info1['taxable_value']		= $taxablevalue;
				$info1['sales_ordertrn_id']	= $rel['sales_ordertrn_id'];
				$info1['user_id']			= $_SESSION['user_id'];
				$info1['trancation_status']	= 3;
				$info1['branch_id']			= $rel['branch_id'];
				$info1['cdate']				= date("Y-m-d H:i:s");
				$table = 'tbl_invoicetrn';
				$tableid = 'trancation_id';
				$inserid = add_record($table, $info1, $dbcon);
				$insert_tax = add_tax_record($dbcon, $inserid, "tbl_invoicetrn", "trancation_id", $rel['formulaid'], $info1['taxable_value']);


				//$info=get_product_tax($dbcon,$POST['taxable_value'],$POST['formulaid']);
				//$info1=array_merge($info1,$info);

				//$info1['bill_value']		= $POST['bill_value'];
				//$info1['bill_black_value']	= $POST['bill_black_value'];

				//$info1['model_id']			= $POST['model_id'];
				//$info1['ser_status']		= $POST['ser_status'];
			} else {
				$info_so['invoice_status'] = 1;
				$updateid = update_record('tbl_sales_ordertrn', $info_so, "sales_ordertrn_id=" . $rel['sales_ordertrn_id'], $dbcon);
			}
		}
	}
} else if (strtolower($POST['mode']) == "load_sales_pro") {
	$resp['pro_html'] = getproduct($dbcon, 0, '0,2,3');
	echo json_encode($resp);
} else if (strtolower($POST['mode']) == "loadsales_producttypedata") {
	$resp['pro_html'] 			= get_sales_order_typewise_data($dbcon, $POST['type_id'], $POST['sales_order_id']);
	echo json_encode($resp);
} else if (strtolower($POST['mode']) == "loadsales_productdata") {
	var_dump($POST['sales_order_id']);
	$q = $dbcon->query("SELECT * from tbl_sales_ordertrn where sales_order_id=" . $POST['sales_order_id'] . " and sales_ordertrn_status=0 and product_id=" . $POST['product_id'] . " ");
	echo "SELECT * from tbl_sales_ordertrn where sales_order_id=" . $POST['sales_order_id'] . " and sales_ordertrn_status=0 and product_id=" . $POST['product_id'] . " ";
	$resp = $q->fetch_assoc();
	$resp['rsock'] = reserve_stock($dbcon, $resp['product_id'], $resp['unit_id'], $reserve_id, $request_id, $complaint_id, $POST['so_trn_id']);

	echo json_encode($resp);
} else if (strtolower($POST['mode']) == "load_qty") {
	echo getsale_productqty($dbcon, $POST['product_id']);
} else if (strtolower($POST['mode']) == "load_rate_hist") {
	$resp = '<table class="table table-bordered table-striped">
			<thead>
			<tr>
			<th class="text-center">Invoice No</th>
			<th class="text-center">Invoice Date</th>
			<th class="text-center">Product Qty</th>
			<th class="text-center">Product Rate</th>
			</tr>
			</thead>
			<tbody>';
	$query = "select inv.*,cust.l_name,pro.product_name,trn.product_rate,trn.product_qty from tbl_invoice as inv
			left join tbl_invoicetrn as trn on inv.invoice_id=trn.invoice_id 
			left join tbl_ledger as cust on cust.l_id=inv.cust_id
			left join product_mst as pro on pro.product_id=trn.product_id
			where inv.invoice_status=0 and trn.trancation_status=0 and inv.cust_id=" . $POST["cust_id"] . " and trn.product_id=" . $POST["product_id"] . " order by trn.trancation_id DESC LIMIT 10";

	$rs_prel = $dbcon->query($query);
	$rs_prel_num_rows = mysqli_num_rows($rs_prel);

	if ($rs_prel_num_rows > 0) {
		while ($prel = mysqli_fetch_assoc($rs_prel)) {

			$resp .= '<tr>
					<td class="text-center">' . $prel['invoice_no'] . '</td>
					<td class="text-center">' . date('d-m-y', strtotime($prel['invoice_date'])) . '</td>
					<td class="text-center">' . $prel['product_qty'] . '</td>
					<td class="text-center">' . $prel['product_rate'] . '</td>
					</tr>';
			$row['cust_name'] = '<table class="table table-bordered table-striped"><tr><td><strong>Customer Name : ' . $prel['l_name'] . '</strong></td></tr></table>';
			$row['product_name'] = $prel['product_name'];
		}
	} else {
		$resp .= '<tr>
				<td colspan="4" class="text-center">NO DATA FOUND !!</td>
				</tr>';
		$row['cust_name'] = "";
		$row['product_name'] = "";
	}


	$row['resp'] = $resp;

	echo json_encode($row);
} else if (strtolower($POST['mode']) == "load_stock_qty") {
	$product_id = $POST['product_id'];
	$get_pro_type_qry = "select product_type,product_base_unit from product_mst where product_id=" . $product_id;
	$get_pro_type_rel = mysqli_fetch_assoc($dbcon->query($get_pro_type_qry));

	$product_type_arr = array("0", "1", "2", "3", "4", "5", "15", "16", "17", "18", "19", "20");
	if (in_array($get_pro_type_rel['product_type'], $product_type_arr)) {
		if (!empty($POST['unit_id'])) {
			$unit_id = $POST['unit_id'];
		} else {
			$unit_id = $get_pro_type_rel['product_base_unit'];
		}
		$current_stock = get_current_stock_new($dbcon, $product_id, $unit_id);

		$where = " and trancation_status!='2' and invoice_id='0'";
		$unclear_qty = get_unclear_stock($dbcon, $product_id, $unit_id, 'tbl_invoicetrn', 'product_qty', 'product_id', $where);
		$rstock = reserve_stock($dbcon, $POST['product_id'], $POST['unit_id'], $reserve_id, $request_id, $complaint_id, $sales_order_trn_id, $branch_id, $is_store_approval, $p_id, '', $stock_id);

		echo $current_stock - $unclear_qty - $rstock;
	} else {
		echo 9999;
	}
} else if (strtolower($POST['mode']) == "get_batch_qty") {
	$stock_id = $POST['batch_no'];
	$gstock = 0;
	$rstock = 0;
	$batch_no = $POST['batch_no'];
	$gstock = get_current_godown_stock_new($dbcon, $POST['product_id'], $POST['unit_id'], $POST['st_godown_id'], $branch_id, $stock_id);

	$rstock = reserve_stock($dbcon, $POST['product_id'], $POST['unit_id'], $reserve_id, $request_id, $complaint_id, $sales_order_trn_id, $branch_id, $is_store_approval, $p_id, $POST['st_godown_id'], $stock_id);


	$stock = $gstock - $rstock;
	echo $stock;
} else if (strtolower($POST['mode']) == "copy_quot_trn_data") {
	$deleteid = delete_record('tbl_invoicetrn', "trancation_status=3 and user_id=" . $_SESSION['user_id'], $dbcon);

	$qt_qry = "select * from tbl_quotation_trn where quot_trn_status=0 and quotation_id=" . $POST['quotation_id'];
	$qt_qry_rs = $dbcon->query($qt_qry);
	while ($qt_trn = mysqli_fetch_assoc($qt_qry_rs)) {
		$info1 = array();

		$info1['ref_quot_trn_id']	= $qt_trn['quot_trn_id'];
		$info1['product_id']		= $qt_trn['product_id'];
		$info1['description']		= $qt_trn['product_desc'];
		$info1['product_qty']		= $qt_trn['product_qty'];
		$info1['product_rate']		= $qt_trn['product_rate'];
		$info1['unit_id']			= $qt_trn['unit_id'];
		$info1['product_discount']	= $qt_trn['product_discount'];
		$info1['discount_per']		= $qt_trn['discount_per'];
		$info1['formulaid']			= $qt_trn['formulaid'];
		$info1['product_amount']	= $qt_trn['product_amount'];
		$info1['taxable_value']		= $qt_trn['taxable_value'];
		$info = get_product_tax($dbcon, $qt_trn['product_amount'], $qt_trn['formulaid']);
		$info1 = array_merge($info1, $info);
		$info1['user_id']			= $_SESSION['user_id'];
		$info1['company_id']		= $_SESSION['company_id'];
		$info1['trancation_status']	= 3;
		$inserid = add_record('tbl_invoicetrn', $info1, $dbcon);
	}
} else if (strtolower($POST['mode']) == "copy_comp_spare_trn_data") {
	$deleteid = delete_record('tbl_invoicetrn', "trancation_status=1", $dbcon);
	//Amish Soni Start - 17-12-2020
	$qt_qry = "select sp.*, ic.received_qty from tbl_complain_spare_part sp 
			left join tbl_internal_chalan ic ON ic.sp_id = sp.s_id
			where sp.s_inv_status=0 and sp.s_paid_status='paid' and sp.s_comp_id=" . $POST['complaint_id'];

	$qt_qry_rs = $dbcon->query($qt_qry);
	while ($qt_trn = mysqli_fetch_assoc($qt_qry_rs)) {

		$company_state = get_company_data($dbcon, $_SESSION['company_id']);
		$product_detail = get_product_detail($dbcon, $qt_trn['s_product']);
		$sale_gst = get_tax_cat_by_hsn_id($dbcon, $product_detail['product_hsn']);
		$custLedgerDetails = get_cust_data_arr($dbcon, $qt_trn['s_cust_id']);

		$total_amount = $qt_trn['s_qty'] * $qt_trn['s_rate'];

		$cgst_tax_rate = 0;
		$sgst_tax_rate = 0;
		$igst_tax_rate = 0;
		if (($company_state['stateid'] == $custLedgerDetails['stateid']) && ($custLedgerDetails['enable_sez'] == 0)) {
			$gst = $sale_gst['tax_gst'] / 2;
			$cgst_tax_per = $gst;
			$cgst_tax_rate = ($gst * $qt_trn['s_rate']) / 100;
			$sgst_tax_per = $gst;
			$sgst_tax_rate = ($gst * $qt_trn['s_rate']) / 100;
		} else {
			$igst_tax_per 	= $sale_gst['tax_gst'];
			$igst_tax_rate 	= ($sale_gst['tax_gst'] * $qt_trn['s_rate']) / 100;
		}

		$info1 = array();

		$info1['ref_s_id']			= $qt_trn['s_id'];
		$info1['product_id']		= $qt_trn['s_product'];
		$info1['product_qty']		= $qt_trn['s_qty'];
		$info1['product_rate']		= $qt_trn['s_rate'];
		//$info1['formulaid']			= $qt_trn['formulaid'];
		$info1['product_amount']	= $qt_trn['s_qty'] * $qt_trn['s_rate'];
		//$info1['taxable_value']		=  $total_amount;
		$info1['total'] 	= $qt_trn['s_rate'] + $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
		//Amish Soni End - 17-12-2020
		//$info=get_product_tax($dbcon,$info1['product_amount'],$info1['formulaid']);
		//$info1=array_merge($info1,$info);
		$info1['user_id']			= $_SESSION['user_id'];
		$info1['company_id']		= $_SESSION['company_id'];
		$info1['trancation_status']	= 1;

		$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0;
		$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
		$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0;
		$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
		$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0;
		$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0;
		$inserid = add_record('tbl_invoicetrn', $info1, $dbcon);
	}

	//Entry Service Charge
	$comp_trn_qry = "select trn.*,tc.* from tbl_complaint_trn as trn left join tbl_complaint as tc on tc.complaint_id=trn.complaint_id where trn.comp_pro_sts=2 and trn.complaint_trn_status=0 and trn.complaint_id=" . $POST['complaint_id'];
	$comp_trn_rel = mysqli_fetch_assoc($dbcon->query($comp_trn_qry));
	if ($comp_trn_rel['product_id']) {

		$company_state = get_company_data($dbcon, $_SESSION['company_id']);
		$product_detail = get_product_detail($dbcon, $comp_trn_rel['product_id']);
		$sale_gst = get_tax_cat_by_hsn_id($dbcon, $product_detail['product_hsn']);
		$custLedgerDetails = get_cust_data_arr($dbcon, $comp_trn_rel['cust_id']);

		$total_amount = 1 * $comp_trn_rel['comp_amount'];

		$cgst_tax_rate = 0;
		$sgst_tax_rate = 0;
		$igst_tax_rate = 0;
		if (($company_state['stateid'] == $custLedgerDetails['stateid']) && ($custLedgerDetails['enable_sez'] == 0)) {
			$gst = $sale_gst['tax_gst'] / 2;
			$cgst_tax_per = $gst;
			$cgst_tax_rate = ($gst * $comp_trn_rel['comp_amount']) / 100;
			$sgst_tax_per = $gst;
			$sgst_tax_rate = ($gst * $comp_trn_rel['comp_amount']) / 100;
		} else {
			$igst_tax_per 	= $sale_gst['tax_gst'];
			$igst_tax_rate 	= ($sale_gst['tax_gst'] * $comp_trn_rel['comp_amount']) / 100;
		}

		$info1 = array();

		$info1['product_id']		= 2862;
		$info1['product_qty']		= 1;
		$info1['product_rate']		= $comp_trn_rel['comp_amount'];
		$info1['product_amount']	= $comp_trn_rel['comp_amount'];
		//$info1['taxable_value']		= $comp_trn_rel['comp_amount'];
		//$info=get_product_tax($dbcon,$info1['product_amount'],$info1['formulaid']);
		//$info1=array_merge($info1,$info);
		$info1['total'] 	= $comp_trn_rel['comp_amount'] + $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
		$info1['user_id']			= $_SESSION['user_id'];
		$info1['company_id']		= $_SESSION['company_id'];
		$info1['trancation_status']	= 1;

		$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0;
		$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
		$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0;
		$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
		$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0;
		$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0;

		$inserid = add_record('tbl_invoicetrn', $info1, $dbcon);
	}
} else if (strtolower($POST['mode']) == "add_pro_srl_no") {
	$info1['pro_srl_no']	= $POST['pro_srl_no'];
	$info1['trancation_id']	= $POST['trancation_id'];
	$info1['user_id']		= $_SESSION['user_id'];
	$table = 'tbl_inv_srl_trn';
	$tableid = 'inv_srl_trn_id';
	if (!empty($POST['invoice_id'])) {
		$info1['invoice_id'] = $POST['invoice_id'];
	}
	$inserid = add_record($table, $info1, $dbcon);
} else if (strtolower($POST['mode']) == "show_pro_srl_no") {
	$str = '';
	if ($POST['trancation_id']) {
		$query = "select trn.* from tbl_inv_srl_trn as trn 
				where trn.inv_srl_trn_status=0 and trn.trancation_id=" . $POST['trancation_id'];
	}

	$result = $dbcon->query($query);
	if (mysqli_num_rows($result) > 0) {
		$i = 1;
		while ($rel = mysqli_fetch_assoc($result)) {
			$str .= '<tr> 
					<td style="vertical-align:top;">
					<strong>' . $i . '</strong>
					</td>
					<td style="vertical-align:top;">
					<strong>' . $rel['pro_srl_no'] . '</strong>
					</td>
					<td style="vertical-align:middle"> 
					<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_inv_srl_data(' . $rel['inv_srl_trn_id'] . ')">X</button>
					</td>
					</tr>';
			$i++;
		}
	} else {
		$str .= '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
	}

	echo $str;
} else if (strtolower($POST['mode']) == "delete_inv_srl_data") {
	$row = array();
	$info['inv_srl_trn_status'] = 2;
	$updateid = update_record('tbl_inv_srl_trn', $info, "inv_srl_trn_id=" . $POST['inv_srl_trn_id'], $dbcon);

	if ($updateid)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "load_ven_grn") {
	$qty = $dbcon->query("SELECT payment_terms, enable_transport, consignee_id FROM tbl_sales_order WHERE sales_order_id = " . $POST['id']);
	$re = brp_mysqli_fetch_assoc($qty);
	$resp['payment_terms'] = $re['payment_terms'];
	$resp['enable_transport'] = $re['enable_transport'];
	$resp['enable_consignee'] = ($re['consignee_id']) ? 0 : 1;
	$resp['consignee_id'] = $re['consignee_id'];
	$resp['pro_html'] = get_so_for_finance($dbcon, $POST['vender_id'], $POST['id'], "Add");
	echo json_encode($resp);
} else if (strtolower($POST['mode']) == "get_so_by_vendor") {
	$vender_id = $POST['vender_id'];
	$so_id = $POST['so_id'];
	$modee = $POST['modee'];
	echo get_so_for_finance($dbcon, $vender_id, $so_id, $modee);
} else if (strtolower($POST['mode']) == "count_pro_srl_no") {
	$cnt_srl_qry = "select count(inv_srl_trn_id) srl_qty,(select product_qty from tbl_invoicetrn where trancation_id=" . $POST['trancation_id'] . ") as act_qty from tbl_inv_srl_trn where inv_srl_trn_status=0 and trancation_id=" . $POST['trancation_id'];
	$cnt_srl_rel = mysqli_fetch_assoc($dbcon->query($cnt_srl_qry));
	if (floatval($cnt_srl_rel['act_qty']) > floatval($cnt_srl_rel['srl_qty'])) {
		echo "1";
	} else {
		echo "0";
	}
}
// Dimple Panchal : Start
else if (strtolower($POST['mode']) == "get_tax_on_total") {
	$arr = get_tax_on_total($dbcon, $POST['total'], $POST['formulaid']);
	echo json_encode($arr);
} else if (strtolower($POST['mode']) == "show_tcs_row") {
	$is_tcs_applicable = $dbcon->query("SELECT tcs_applicable FROM tbl_finance_setting WHERE company_id=" . $_SESSION['company_id'])
		->fetch_object()->tcs_applicable;

	if ($is_tcs_applicable) {
		$invoice_total = $dbcon->query("SELECT sum(g_total) as invoice_total FROM `tbl_invoice` where cust_id = " . $POST['cust_id'] . " and company_id = " . $_SESSION['company_id'] . " and invoice_status = 0")
			->fetch_object()->invoice_total;

		if ($invoice_total >= 5000000) {
			echo "1";
		} else {
			echo "0";
		}
	} else {
		echo "0";
	}
}
// Dimple Panchal : end

/*Dhruv start code*/ else if (strtolower($POST['mode']) == "get_gst_statecode") {
	$arr = get_gst_statecode($dbcon, $POST['cust_id']);
	echo $arr;
} else if (strtolower($POST['mode']) == "get_grossbalance") {
	$arr = get_grossbalance($dbcon, $POST['cust_id']);
	if ($arr == '') {
		echo "0";
	} else {
		echo $arr;
	}
} else if (strtolower($POST['mode']) == "get_tax_details_table") {
	//echo '<pre>';print_r($POST);exit;
	$invoice_id = $POST['invoice_id'];
	$custLedgerDetails = get_cust_data_arr($dbcon, $POST['cust_id']);

	$resp = '';
	$where = '';
	if ($POST['invoice_id']) {
		$where .= '';
	} else {
		$where .= "and user_id=" . $_SESSION['user_id'];
	}
	$query = "SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_invoicetrn` where invoice_id='$invoice_id' and trancation_status!=2 " . $where . " group by cgst_tax_per,sgst_tax_per,igst_tax_per";

	$rs_prel = $dbcon->query($query);
	$rs_prel_fetch = brp_mysqli_fetch_assoc($dbcon->query("SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_invoicetrn` where invoice_id='$invoice_id' and trancation_status!=2 " . $where));
	$rs_prel_num_rows = mysqli_num_rows($rs_prel);
	//print_r($rs_prel_fetch);exit;
	$resp = '';
	//if($POST['salestype'] == 1){

	$resp .= '<table class="table table-bordered">

				<tr>
				<th class="text-center">#</th>
				<th  class="text-center">Total Tax</th>
				<th  class="text-center">Taxable Amount <span class="currency_icon"> </span></th>
				<th  class="text-center">Tax Amount <span class="currency_icon"> </span></th>';
	if (($rs_prel_fetch['cgst_rate'] != 0) || ($rs_prel_fetch['sgst_rate'] != 0)) {
		$resp .= '<th  class="text-center">CGST</th>
					<th  class="text-center">SGST</th>';
	}
	if ($custLedgerDetails['enable_sez'] == 0) {
		if (($rs_prel_fetch['igst_rate'] != 0)) {
			$resp .= '<th  class="text-center">IGST</th>';
		}
	} else {
		$resp .= '<th  class="text-center">IGST</th>';
	}

	$resp .= '</tr>';
	//}
	// && $POST['salestype'] == 1
	if ($rs_prel_num_rows > 0) {
		$taxRate = brp_mysqli_fetch_all($rs_prel);

		$cnt = 1;
		$cntloop = 0;
		foreach ($taxRate as $taxdetail) {
			$gst_tax_per = ($taxdetail['cgst_tax_per'] != 0 || $taxdetail['sgst_tax_per'] != 0) ? ($taxdetail['cgst_tax_per'] + $taxdetail['sgst_tax_per']) : $taxdetail['igst_tax_per'];

			$gst_tax_rate = ($taxdetail['cgst_rate'] != 0 || $taxdetail['sgst_rate'] != 0) ? ($taxdetail['cgst_rate'] + $taxdetail['sgst_rate']) : $taxdetail['igst_rate'];

			$gst_tax_rate_conv = ($taxdetail['cgst_rate'] != 0 || $taxdetail['sgst_rate'] != 0) ? ($taxdetail['cgst_rate_conv'] + $taxdetail['sgst_rate_conv']) : $taxdetail['igst_rate_conv'];

			if ($taxdetail['cgst_tax_per'] != 0 || $taxdetail['sgst_tax_per'] != 0) {
				$resp .= '<tr>
						<th class="text-center">' . $cnt . '</th>
						<th class="text-center">' . $gst_tax_per . '%' . '</th>
						<th class="text-center">';
				if ($POST['currency_id'] == $_SESSION['currency_id']) {
					$resp .= $taxdetail['product_amount'] . '</th>
							<th class="text-center">' . $gst_tax_rate;
				} else {
					$resp .= $taxdetail['product_amount_conv'] . '</th>
							<th class="text-center">' . $gst_tax_rate_conv;
				}
				$resp .= '</th>
						<th class="text-center">' . ($taxdetail['cgst_tax_per']) . '%' . '</th>
						<th class="text-center">' . ($taxdetail['sgst_tax_per']) . '%' . '</th>
						</tr>';
				//var_dump($POST['addontax1']);
				if (!empty($POST['addontax1']) && $cntloop == 0) {
					foreach ($POST['addontax1'] as $addtax) {
						$cnt++;
						$exp_addtax = explode("-", $addtax);
						//echo $exp_addtax[1];
						if ($exp_addtax[1] != 0) {
							$resp .= '<tr>
									<th class="text-center">' . $cnt . '</th>
									<th class="text-center">' . $exp_addtax[1] . '%' . '</th>
									<th class="text-center">' . ($exp_addtax[2]) . '</th>
									<th class="text-center">' . $exp_addtax[0] . '</th>
									<th class="text-center">' . ($exp_addtax[1] / 2) . '%' . '</th>
									<th class="text-center">' . ($exp_addtax[1] / 2) . '%' . '</th>
									</tr>';
						}
					}
					$cntloop = 1;
				}
			}

			if ($taxdetail['igst_tax_per'] != 0) {
				$resp .= '<tr>
						<th class="text-center">' . $cnt . '</th>
						<th class="text-center">' . $gst_tax_per . '%' . '</th>
						<th class="text-center">';
				if ($POST['currency_id'] == $_SESSION['currency_id']) {
					$resp .= $taxdetail['product_amount'] . '</th>
								<th class="text-center">' . $gst_tax_rate;
				} else {
					$resp .= $taxdetail['product_amount_conv'] . '</th>
								<th class="text-center">' . $gst_tax_rate_conv;
				}
				$resp .= '</th>
						<th class="text-center">' . ($taxdetail['igst_tax_per']) . '%' . '</th>
						</tr>';
				//var_dump($POST['addontax1']);
				if (!empty($POST['addontax1']) && $cntloop == 0) {
					foreach ($POST['addontax1'] as $addtax) {
						$cnt++;
						$exp_addtax = explode("-", $addtax);
						//echo '<pre>';print_r($exp_addtax);
						if ($exp_addtax[1] != 0) {
							$resp .= '<tr>
									<th class="text-center">' . $cnt . '</th>
									<th class="text-center">' . $exp_addtax[1] . '%' . '</th>
									<th class="text-center">' . ($exp_addtax[2]) . '</th>
									<th class="text-center">' . $exp_addtax[0] . '</th>
									<th class="text-center">' . ($exp_addtax[1]) . '%' . '</th>
									</tr>';
						}
					}
					$cntloop = 1;
				}
			}

			$cnt++;
		}
	}

	$resp .= '</table>';

	$row['resp'] = $resp;

	echo json_encode($row);
} else if (strtolower($POST['mode']) == "get_invoice_total_tax") {
	//echo '<pre>';print_r($POST);exit;
	$invoice_id = $POST['invoice_id'];

	$resp = '';
	$where = '';
	if ($POST['invoice_id']) {
		$where .= '';
	} else {
		$where .= "and user_id=" . $_SESSION['user_id'];
	}
	$query = "SELECT sum(cgst_tax_rate) as cgst_rate,sum(sgst_tax_rate) as sgst_rate,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_invoicetrn` where invoice_id='$invoice_id' and trancation_status!=2 " . $where;


	$rs_prel = brp_mysqli_fetch_assoc($dbcon->query($query));


	$query_inv = "SELECT cgst,sgst,igst,tcs from tbl_invoice where invoice_id='$invoice_id' ";
	$rs_prel_inv = brp_mysqli_fetch_assoc($dbcon->query($query_inv));

	$row['isTcs'] = "0";
	$getCompanyConfig = getCompanyConfiguration($dbcon);
	$custLedgerDetails = get_cust_data_arr($dbcon, $POST['cust_id']);
	$get_bill_sundry = get_bill_sundry_ledger($dbcon, 1);
	$company_config = getCompanyConfiguration($dbcon);
	$company_state = get_company_data($dbcon, $_SESSION['company_id']);
	if ($company_config['tax_editable'] == 1) {
		$readonly = " onChange='update_netbalance()'";
	} else {
		$readonly = "readonly";
	}

	if ($POST['salestype'] == 2) {
		if ($custLedgerDetails['stateid'] == $company_state['stateid']) {
			$rs_prel['cgst_rate'] = ($rs_prel['product_amount'] * (0.05) / 100);
			$rs_prel['sgst_rate'] = ($rs_prel['product_amount'] * (0.05) / 100);
		} else {
			$rs_prel['igst_rate'] = ($rs_prel['product_amount'] * (0.1) / 100);
		}
	}

	if ($company_config['tax_editable'] == 1 && $invoice_id != '') {
		if (($rs_prel_inv['cgst'] != 0) || ($custLedgerDetails['enable_sez'] == 0 && $custLedgerDetails['stateid'] == $company_state['stateid'])) {
			$resp .= '<div class="form-group">
					<label class="col-md-5 control-label">CGST <span class="currency_icon"></span></label>
					<div class="col-md-5 col-xs-11">
					<input id="CGST" name="bill_sundry_tax[9870]" type="number" class="form-control gst" title="CGST"  value="' . (($POST['currency_id'] == $company_state['currency_id']) ? $rs_prel_inv['cgst'] : $rs_prel_inv['cgst_conv']) . '" placeholder="CGST" ' . $readonly . ' >
					</div>
					</div>';
		}

		if (($rs_prel_inv['sgst'] != 0) || ($custLedgerDetails['enable_sez'] == 0 && $custLedgerDetails['stateid'] == $company_state['stateid'])) {
			$resp .= '<div class="form-group">
					<label class="col-md-5 control-label">SGST <span class="currency_icon"></span></label>
					<div class="col-md-5 col-xs-11">
					<input id="SGST" name="bill_sundry_tax[9880]" type="number" class="form-control gst" title="SGST"  value="' . (($POST['currency_id'] == $company_state['currency_id']) ? $rs_prel_inv['sgst'] : $rs_prel_inv['sgst_conv']) . '" placeholder="SGST" ' . $readonly . ' >
					</div>
					</div>';
		}

		if (($rs_prel_inv['igst'] != 0) || ($custLedgerDetails['enable_sez'] == 1 && $custLedgerDetails['stateid'] == $company_state['stateid'])) {
			$resp .= '<div class="form-group">
					<label class="col-md-5 control-label">IGST <span class="currency_icon"></span></label>
					<div class="col-md-5 col-xs-11">
					<input id="IGST" name="bill_sundry_tax[9890]" type="number" class="form-control gst" title="IGST"  value="' . (($POST['currency_id'] == $company_state['currency_id']) ? $rs_prel_inv['igst'] : $rs_prel_inv['igst_conv']) . '" placeholder="IGST" ' . $readonly . ' >
					</div>
					</div>';
		}

		if (($rs_prel_inv['tcs'] != 0) || ($getCompanyConfig['enable_tcs_reporting'] == 1) && ($custLedgerDetails['enable_tcs'] == 1) && ($POST['gross'] >= $getCompanyConfig['gross_balance_limit'])) {
			$resp .= '<div class="form-group">
					<label class="col-md-5 control-label">TCS</label>
					<div class="col-md-5 col-xs-11">
					<input id="TCS" name="bill_sundry_tax[9892]" type="number" class="form-control gst" title="TCS"  value="' . $rs_prel_inv['tcs'] . '" placeholder="TCS" ' . $readonly . ' >
					</div>
					</div>';
		}
	} else {

		foreach ($get_bill_sundry as $billsundry) {

			if ((($rs_prel['cgst_rate'] != 0) && $billsundry['l_name'] == 'CGST') || (($rs_prel['sgst_rate'] != 0) && $billsundry['l_name'] == 'SGST')) {

				if (!empty($POST['addontax1'])) {
					$addontax = $POST['addontax1'] / 2;
				}

				$gstValue = ($billsundry['l_name'] == 'CGST') ? $rs_prel['cgst_rate'] : (($billsundry['l_name'] == 'SGST') ? $rs_prel['sgst_rate'] : '');

				$gstValue_conv = ($billsundry['l_name'] == 'CGST') ? $rs_prel['cgst_rate_conv'] : (($billsundry['l_name'] == 'SGST') ? $rs_prel['sgst_rate_conv'] : '');

				$resp .= '<div class="form-group">
						<label class="col-md-5 control-label">' . $billsundry['l_name'] . ' <span class="currency_icon"></span></label>
						<div class="col-md-5 col-xs-11">
						<input id="' . $billsundry['l_name'] . '" name="bill_sundry_tax[' . $billsundry['l_id'] . ']" type="number" class="form-control gst" title="' . $billsundry['l_name'] . '"  value="' . (($POST['currency_id'] == $company_state['currency_id']) ? round($gstValue + $addontax, 2) : round($gstValue_conv + $addontax, 2)) . '" placeholder="' . $billsundry['l_name'] . '" ' . $readonly . ' >
						</div>
						</div>';
			}
			//echo $custLedgerDetails['enable_sez'];exit;
			if ($custLedgerDetails['enable_sez'] == 0) {
				if (($rs_prel['igst_rate'] != 0) && $billsundry['l_name'] == 'IGST') {
					if (!empty($POST['addontax1'])) {
						$addontax = $POST['addontax1'];
					}
					$resp .= '<div class="form-group">
							<label class="col-md-5 control-label">' . $billsundry['l_name'] . ' <span class="currency_icon"></span></label>
							<div class="col-md-5 col-xs-11">
							<input id="' . $billsundry['l_name'] . '" name="bill_sundry_tax[' . $billsundry['l_id'] . ']" type="number" class="form-control gst" title="' . $billsundry['l_name'] . '"  value="' . (($POST['currency_id'] == $company_state['currency_id']) ? round($rs_prel['igst_rate'] + $addontax, 2) : round($rs_prel['igst_rate_conv'] + $addontax, 2)) . '" placeholder="' . $billsundry['l_name'] . '" ' . $readonly . ' >
							</div>
							</div>';
				}
			} else {
				if ($billsundry['l_name'] == 'IGST') {
					$resp .= '<div class="form-group">
							<label class="col-md-5 control-label">' . $billsundry['l_name'] . ' <span class="currency_icon"></span></label>
							<div class="col-md-5 col-xs-11">
							<input id="' . $billsundry['l_name'] . '" name="bill_sundry_tax[' . $billsundry['l_id'] . ']" type="number" class="form-control gst" title="' . $billsundry['l_name'] . '"  value="' . (($POST['currency_id'] == $company_state['currency_id']) ? round($rs_prel['igst_rate'] + $addontax, 2) : round($rs_prel['igst_rate_conv'] + $addontax, 2)) . '" placeholder="' . $billsundry['l_name'] . '" ' . $readonly . ' >
							</div>
							</div>';
				}
			}

			if (($billsundry['l_name'] == 'TCS') && ($getCompanyConfig['enable_tcs_reporting'] == 1) && ($custLedgerDetails['enable_tcs'] == 1) && ($POST['gross'] >= $getCompanyConfig['gross_balance_limit'])) {
				$row['isTcs'] = "1";
				$total_tcs_calculate = $rs_prel['product_amount'] + ($gstValue * 2) + $rs_prel['igst_rate'];

				$total_tcs_calculate_conv = $rs_prel['product_amount_conv'] + ($gstValue_conv * 2) + $rs_prel['igst_rate_conv'];
				$resp .= '<div class="form-group">
						<label class="col-md-5 control-label">' . $billsundry['l_name'] . ' <span class="currency_icon"></span></label>
						<div class="col-md-5 col-xs-11">
						<input id="' . $billsundry['l_name'] . '" name="bill_sundry_tax[' . $billsundry['l_id'] . ']" type="number" class="form-control gst" title="' . $billsundry['l_name'] . '"  value="' . (($POST['currency_id'] == $company_state['currency_id']) ? round((($total_tcs_calculate * $billsundry['tax_value']) / 100), 2) : round((($total_tcs_calculate_conv * $billsundry['tax_value']) / 100), 2)) . '" placeholder="' . $billsundry['l_name'] . '" ' . $readonly . ' >
						<input type="hidden" name="tcs_amount" id="tcs_amount" value="' . (($POST['currency_id'] == $company_state['currency_id']) ? round((($total_tcs_calculate * $billsundry['tax_value']) / 100), 2) : round((($total_tcs_calculate_conv * $billsundry['tax_value']) / 100), 2)) . '" >
						<input type="hidden" name="tcs_per" id="tcs_per" value="' . $billsundry['tax_value'] . '" >
						</div>
						</div>';
			}
		}
	}

	//additional tax transaction start - dhaval

	/*	$qry_add=$dbcon->query("SELECT trn.*,p.product_hsn,h.sale_gst,tc.tax_cat_id,t.tax_id,t.tax_per,l.l_name from tbl_invoicetrn as trn 
		left join product_mst as p on p.product_id=trn.product_id
		left join mst_hsn_code as h on h.hsn_id=p.product_hsn
		left join tbl_tax_category as tc on tc.tax_cat_id=h.sale_gst
		left join tbl_tax_category_details as t on t.tax_cat=tc.tax_cat_id
		left join tbl_ledger as l on l.l_id=t.tax_id
		where t.tax_additional='1' and trn.invoice_id='$invoice_id' and trn.trancation_status!=2 and t.isdelete='0'");
		while($row1=brp_mysqli_fetch_array($qry_add))
		{
			
			$tax_rate = ($row1['tax_per']*$row1['product_amount'])/100;
			
			
			$resp.='<div class="form-group">
					<label class="col-md-5 control-label">'.$row1['l_name'].'</label>
					<div class="col-md-5 col-xs-11">
						<input id="'.$row1['l_name'].'" name="bill_sundry_tax['.$row1['l_id'].']" type="number" class="form-control gst" title="'.$row1['l_name'].'"  value="'.$tax_rate.'" placeholder="'.$billsundry['l_name'].'" readonly >
					</div>
				</div>';
		}
		*/

	$qry_add = $dbcon->query("select sum((tc.tax_per*trn.product_amount)/100) as add_sum , trn.*,l.l_name,l.l_id,t.tax_cat_id from tbl_invoicetrn as trn left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat 
			left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
			left join tbl_ledger as l on l.l_id=tc.tax_id
			where tc.tax_additional='1' and trn.invoice_id='$invoice_id' and trn.trancation_status!=2 and tc.isdelete='0' group by tc.tax_id 
			");
	while ($row1 = brp_mysqli_fetch_array($qry_add)) {

		//$tax_rate = ($row1['tax_per']*$row1['product_amount'])/100;


		$resp .= '<div class="form-group">
			<label class="col-md-5 control-label">' . $row1['l_name'] . '</label>
			<div class="col-md-5 col-xs-11">
			<input id="' . $row1['l_name'] . '" name="bill_sundry_tax[' . $row1['l_id'] . ']" type="number" class="form-control gst" title="' . $row1['l_name'] . '"  value="' . (($POST['currency_id'] == $company_state['currency_id']) ? $row1['add_sum'] : $row1['add_sum_conv']) . '" placeholder="' . $billsundry['l_name'] . '" readonly >
			</div>
			</div>';
	}

	$row['resp'] = $resp;

	echo json_encode($row);
} else if (strtolower($POST['mode']) == "get_invoice_total_tax_old") {
	$invoice_id = $POST['invoice_id'];

	$resp = '';
	$query = "SELECT sum(cgst_tax_rate) as cgst_rate,sum(sgst_tax_rate) as sgst_rate,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount FROM `tbl_invoicetrn` where invoice_id='$invoice_id' and trancation_status!=2";

	$rs_prel = brp_mysqli_fetch_assoc($dbcon->query($query));

	$row['isTcs'] = "0";
	$getCompanyConfig = getCompanyConfiguration($dbcon);
	$custLedgerDetails = get_cust_data_arr($dbcon, $POST['cust_id']);
	$get_bill_sundry = get_bill_sundry_ledger($dbcon, 1);

	foreach ($get_bill_sundry as $billsundry) {

		if ((($rs_prel['cgst_rate'] != 0) && $billsundry['l_name'] == 'CGST') || (($rs_prel['sgst_rate'] != 0) && $billsundry['l_name'] == 'SGST')) {

			$gstValue = ($billsundry['l_name'] == 'CGST') ? $rs_prel['cgst_rate'] : (($billsundry['l_name'] == 'SGST') ? $rs_prel['sgst_rate'] : '');
			$resp .= '<div class="form-group">
				<label class="col-md-5 control-label">' . $billsundry['l_name'] . '</label>
				<div class="col-md-5 col-xs-11">
				<input id="' . $billsundry['l_name'] . '" name="bill_sundry_tax[' . $billsundry['l_id'] . ']" type="number" class="form-control gst" title="' . $billsundry['l_name'] . '"  value="' . $gstValue . '" placeholder="' . $billsundry['l_name'] . '" readonly >
				</div>
				</div>';
		}
		if (($rs_prel['igst_rate'] != 0) && $billsundry['l_name'] == 'IGST') {
			$resp .= '<div class="form-group">
				<label class="col-md-5 control-label">' . $billsundry['l_name'] . '</label>
				<div class="col-md-5 col-xs-11">
				<input id="' . $billsundry['l_name'] . '" name="bill_sundry_tax[' . $billsundry['l_id'] . ']" type="number" class="form-control gst" title="' . $billsundry['l_name'] . '"  value="' . $rs_prel['igst_rate'] . '" placeholder="' . $billsundry['l_name'] . '" readonly >
				</div>
				</div>';
		}

		if (($billsundry['l_name'] == 'TCS') && ($getCompanyConfig['enable_tcs_reporting'] == 1) && ($custLedgerDetails['enable_tcs'] == 1) && ($POST['gross'] > $getCompanyConfig['gross_balance_limit'])) {
			$row['isTcs'] = "1";
			$total_tcs_calculate = $rs_prel['product_amount'] + $gstValue + $rs_prel['igst_rate'];
			$resp .= '<div class="form-group">
				<label class="col-md-5 control-label">' . $billsundry['l_name'] . '</label>
				<div class="col-md-5 col-xs-11">
				<input id="' . $billsundry['l_name'] . '" name="bill_sundry_tax[' . $billsundry['l_id'] . ']" type="number" class="form-control gst" title="' . $billsundry['l_name'] . '"  value="' . round((($total_tcs_calculate * $billsundry['tax_value']) / 100), 2) . '" placeholder="' . $billsundry['l_name'] . '" readonly >
				<input type="hidden" name="tcs_per" id="tcs_per" value="' . $billsundry['tax_value'] . '" >
				</div>
				</div>';
		}
	}

	$row['resp'] = $resp;

	echo json_encode($row);
} else if (strtolower($POST['mode']) == "eway_api") {
	//$getToken = get_eway_token();
	//print_r($getToken);exit;
	$postData = array();
	$postData['supplyType'] = "0";
	$postData['subSupplyType'] = "1";
	$postData['subSupplyDesc'] = '';
	$postData['docType'] = 'INV';
	$postData['docNo'] = '111-19909';
	$postData['docDate'] = "09/07/2021";
	$postData['fromGstin'] = "34AACCC1596Q002";
	$postData['fromTrdName'] = "welton";
	$postData['fromAddr1'] = "2ND CROSS NO 59  19  A";
	$postData['fromAddr2'] = "GROUND FLOOR OSBORNE ROAD";
	$postData['fromPlace'] = "FRAZER TOWN";
	$postData['fromPincode'] = "605005";
	$postData['actFromStateCode'] = "34";
	$postData['fromStateCode'] = '34';
	$postData['toGstin'] = "02EHFPS5910D2Z0";
	$postData['toTrdName'] = "sthuthya";

	$postData['toAddr1'] = "Shree Nilaya";
	$postData['toAddr2'] = "Dasarahosahalli";
	$postData['toPlace'] = "Beml Nagar";
	$postData['toPincode'] = '176036';
	$postData['actToStateCode'] = "02";
	$postData['toStateCode'] = "02";
	$postData['transactionType'] = "4";
	$postData['dispatchFromGSTIN'] = "29AAAAA1303P1ZV";
	$postData['dispatchFromTradeName'] = "ABC Traders";
	$postData['shipToGSTIN'] = "29ALSPR1722R1Z3";
	$postData['shipToTradeName'] = "XYZ Traders";
	$postData['otherValue'] = -"100";

	$postData['totalValue'] = "56099";
	$postData['cgstValue'] = "0";
	$postData['sgstValue'] = "0";
	$postData['igstValue'] = "300.67";
	$postData['cessValue'] = "400.56";
	$postData['cessNonAdvolValue'] = "400";
	$postData['totInvValue'] = "68358";
	$postData['transporterId'] = "";
	$postData['transporterName'] = "";
	$postData['transDocNo'] = "";
	$postData['transMode'] = "1";
	$postData['transDistance'] = "2786";

	$postData['transDocDate'] = "";
	$postData['vehicleNo'] = "PVC1234";
	$postData['vehicleType'] = "R";


	$postData['itemList'] = get_item_details($dbcon);

	$callEway = submitEwayApi(json_encode($postData));
	print_r($callEway);
	exit;
	//echo $arr;
} else if (strtolower($POST['mode']) == "remove_sundry") {

	$ledger_id = $POST['ledger_id'];
	$invoice_id = $POST['edit_id'];
	$cust_ledger_id = $POST['cust_ledger_id'];

	$info['isdelete'] = 1;

	$updateid = update_record('tbl_bill_sundry_transaction', $info, "sundry_id=" . $POST['ledger_id'], $dbcon);

	$info_general['genral_book_status'] = 2;

	$q = $dbcon->query("SELECT amount from tbl_general_book where table_id=" . $POST['ledger_id'] . " and table_name='tbl_bill_sundry_transaction' ");
	$resp = $q->fetch_assoc();

	$update_gen_cusid = update_record('tbl_general_book', $info_general, "table_id=" . $invoice_id . " and ledger_id=" . $cust_ledger_id . " and amount=" . $resp['amount'] . " and ref_by='tbl_addon_bill_sundry' and  table_name='tbl_invoice'", $dbcon);

	$updateid = update_record('tbl_general_book', $info_general, "table_id=" . $POST['ledger_id'] . " and table_name='tbl_bill_sundry_transaction'", $dbcon);
} else if (strtolower($POST['mode']) == "get_bill_sundry_details") {
	$invoice_id = $POST['invoice_id'];
	$q = $dbcon->query("SELECT * from tbl_ledger_bill_sundry where sundry_ledger_id=" . $POST['sundry_ledger_id'] . " and company_id = " . $_SESSION['company_id'] . " and isdelete=0 ");

	$resp = $q->fetch_assoc();

	$q_tax = $dbcon->query("select tax_gst from tbl_tax_category where tax_cat_id=" . $resp['sundry_gst'] . " ");

	$resp_tax = $q_tax->fetch_assoc();

	$basic_total = $POST['basic_amount'];
	$netamount = $POST['netamount'];
	$taxableamount = $POST['taxableamount'];

	$default_amount = $POST['default_amount'];

	if ($POST['sales_type'] == "3") {
		$resp_tax['tax_gst'] = 0.1;
	} else if ($POST['sales_type'] == "4") {
		$resp_tax['tax_gst'] = 0;
	} else if ($POST['sales_type'] == "5") {
		$resp_tax['tax_gst'] = 5;
	} else if ($POST['sales_type'] == "6") {
		$resp_tax['tax_gst'] = 12;
	} else if ($POST['sales_type'] == "7") {
		$resp_tax['tax_gst'] = 18;
	} else if ($POST['sales_type'] == "8") {
		$resp_tax['tax_gst'] = 24;
	}

	if (($resp['apply_gst'] == 2) && (!empty($resp['sundry_gst']))) {
		if ($resp['sundry_amount_of'] == 2) {
			$taxvl = ($resp_tax['tax_gst'] * (($basic_total * $default_amount) / 100)) / 100;
		} else {
			$taxvl = ($resp_tax['tax_gst'] * $POST['default_amount']) / 100;
		}
		//$taxvl = ($resp_tax['tax_gst']*$POST['default_amount'])/100;

		$taxgst = $resp_tax['tax_gst'];
	} else {
		$taxvl = 0;
		$taxgst = 0;
	}

	//print_r($POST['totalsundryexist']);exit;
	$totalsundryexist = $POST['totalsundryexist'];

	if ($resp['sundry_type'] == 1) {
		if ($resp['sundry_amount_of'] == 1) {
			if ($resp['sundry_calculate_on'] == 1) {
				$finalNetAmount = $netamount + $default_amount;
				$pervalue =  $default_amount;
			} else if ($resp['sundry_calculate_on'] == 2) {
				$finalNetAmount = $basic_total + $default_amount;
				$pervalue =  $default_amount;
			} else if ($resp['sundry_calculate_on'] == 3) {
				$finalNetAmount = $basic_total + $default_amount;
				$pervalue =  $default_amount;
			}
			//$finalNetAmount = $netamount + $default_amount;

		} else if ($resp['sundry_amount_of'] == 2) {
			if ($resp['sundry_calculate_on'] == 1) {
				$finalNetAmount = (($netamount * $default_amount) / 100) + $netamount;
				$pervalue = ($netamount * $default_amount) / 100;
			} else if ($resp['sundry_calculate_on'] == 2) {
				$finalNetAmount = (($basic_total * $default_amount) / 100) + $basic_total;
				$pervalue = ($basic_total * $default_amount) / 100;
			} else if ($resp['sundry_calculate_on'] == 3) {
				$finalNetAmount = (($basic_total * $default_amount) / 100) + $basic_total;
				$pervalue = ($basic_total * $default_amount) / 100;
			}
			//$finalNetAmount = (($netamount * $default_amount)/100) + $netamount;
		}
		//$per_amount_show='';
	} else if ($resp['sundry_type'] == 2) {
		if ($resp['sundry_amount_of'] == 1) {
			if ($resp['sundry_calculate_on'] == 1) {
				$finalNetAmount = $netamount - $default_amount;
				$pervalue =  -$default_amount;
			} else if ($resp['sundry_calculate_on'] == 2) {
				$finalNetAmount = $basic_total - $default_amount;
				$pervalue =  -$default_amount;
			} else if ($resp['sundry_calculate_on'] == 3) {
				//$finalNetAmount = (($basic_total + $taxableamount) - $default_amount) + $totalsundryexist;
				$finalNetAmount = $basic_total - $default_amount;
				$pervalue =  -$default_amount;
			}
			//$finalNetAmount = $netamount - $default_amount;
		} else if ($resp['sundry_amount_of'] == 2) {
			if ($resp['sundry_calculate_on'] == 1) {
				$finalNetAmount = $netamount - (($netamount * $default_amount) / 100);
				$pervalue = - ($netamount * $default_amount) / 100;
			} else if ($resp['sundry_calculate_on'] == 2) {
				//$finalNetAmount = (($basic_total + $taxableamount) - (($basic_total * $default_amount)/100)) + $totalsundryexist;
				$finalNetAmount = $basic_total - (($basic_total * $default_amount) / 100);
				$pervalue = - ($basic_total * $default_amount) / 100;
			} else if ($resp['sundry_calculate_on'] == 3) {
				//$finalNetAmount = (($basic_total + $taxableamount) + ((($basic_total + $taxableamount) * $default_amount)/100)) + $totalsundryexist;
				$finalNetAmount = $basic_total - (($basic_total * $default_amount) / 100);
				$pervalue = - ($basic_total * $default_amount) / 100;
			}
			//$finalNetAmount = $netamount - (($netamount * $default_amount)/100);
		}

		//$per_amount_show = '('.$default_amount.'% )';

	}

	//if invoice is edit time insert data in database start - dhaval
	if ($invoice_id > 0) {
		$info_sundry_addon['sundry_ledger_id'] = $POST['sundry_ledger_id'];
		//$info_sundry_addon['sundry_amount']=$pervalue;
		$info_sundry_addon['sundry_voucher_id'] = $invoice_id;
		$info_sundry_addon['sundry_voucher_type'] = SALES_VOUCHER;
		$info_sundry_addon['sundry_voucher_table'] = 'tbl_invoice';
		$info_sundry_addon['cdate']	= date("Y-m-d H:i:s");
		$info_sundry_addon['user_id']	= $_SESSION['user_id'];
		$info_sundry_addon['company_id']	= $_SESSION['company_id'];

		$info_sundry_addon['sundry_gst_per']	= $taxgst;
		//$info_sundry_addon['sundry_gst_amount']	= $taxvl;
		//print_r(array_merge($info_sundry_addon,$curncy_trn));

		if (isset($POST['currency_enable'])) {
			$curncy_trn['currency_id'] = $POST['currency_id'];
			$curncy_trn['currency_rate'] = $POST['currency_rate'];
		} else {
			$basecurrency = getbasecurrency($dbcon);
			$curncy_trn['currency_id'] = $basecurrency['currencyid'];
			$curncy_trn['currency_rate'] = 1;
		}

		if ($POST['currency_id'] == $_SESSION['currency_id']) {
			$info_sundry_addon['sundry_amount'] = $pervalue;
			$info_sundry_addon['sundry_gst_amount']	= $taxvl;
			$info_sundry_addon['sundry_amount_conv'] = $pervalue * $POST['currency_rate'];
			$info_sundry_addon['sundry_gst_amount_conv'] = $taxvl * $POST['currency_rate'];
		} else {
			$info_sundry_addon['sundry_amount'] = $pervalue * $POST['currency_rate'];
			$info_sundry_addon['sundry_gst_amount']	= $taxvl * $POST['currency_rate'];
			$info_sundry_addon['sundry_amount_conv'] = $pervalue;
			$info_sundry_addon['sundry_gst_amount_conv'] = $taxvl;
		}

		$sundry_addon_insert = add_record('tbl_bill_sundry_transaction', array_merge($info_sundry_addon, $curncy_trn), $dbcon);

		//general book entry 
		$invoice_date = date("Y-m-d", strtotime($POST['invoice_date']));

		if ($pervalue < 0) {
			$ledger_entry_type = 2;
			$cust_entry_type = 1;
		} else {
			$ledger_entry_type = 1;
			$cust_entry_type = 2;
		}
		$module_id = $invoice_id;
		$module_name = MODULE_INVOICE;

		$info_general_addon['ledger_id'] = $POST['sundry_ledger_id'];
		$info_general_addon['amount'] = abs($pervalue);
		$info_general_addon['table_id'] = $sundry_addon_insert;
		$info_general_addon['entry_type'] = $ledger_entry_type;
		$info_general_addon['table_name'] = 'tbl_bill_sundry_transaction';
		$info_general_addon['ref_date'] = $invoice_date;
		$info_general_addon['module_name'] = $module_name;
		$info_general_addon['module_id'] = $module_id;
		$info_general_addon['cdate']	= date("Y-m-d H:i:s");
		$info_general_addon['user_id']	= $_SESSION['user_id'];
		$info_general_addon['company_id']	= $_SESSION['company_id'];

		add_record('tbl_general_book', array_merge($info_general_addon, $curncy_trn), $dbcon);

		$info_gen2['table_name']	= 'tbl_invoice';
		$info_gen2['table_id']		= $invoice_id;
		$info_gen2['entry_type']	= $cust_entry_type;
		$info_gen2['ref_date']		= date('Y-m-d', strtotime($invoice_date));
		$info_gen2['ledger_id']		= $POST['cust_id'];
		$info_gen2['amount']		= abs($pervalue);
		$info_gen2['user_id']		= $_SESSION['user_id'];
		$info_gen2['cdate']			= date("Y-m-d H:i:s");
		$info_gen2['company_id']	= $_SESSION['company_id'];
		$info_gen2['ref_by'] = 'tbl_addon_bill_sundry';

		//$inserid_gen2=add_record("tbl_general_book", array_merge($info_gen2,$curncy_trn) , $dbcon);

		//add_general_book_entry($dbcon,"tbl_invoice",$invoice_id,$cust_entry_type,$POST['cust_id'],abs($pervalue),'',$invoice_date,'',$curncy_trn);
	}
	//if invoice is edit time insert data in database end - dhaval

	if ($resp['sundry_amount_of'] == 1) {

		$per_amount_show = "";
	} else {

		$per_amount_show = '<strong> (' . round($default_amount, 2) . '%)</strong>';
	}

	// $finalNetAmount = round($finalNetAmount,2);
	$pervalue = round($pervalue, 2);
	// $per_amount_show = round($per_amount_show,2);
	//echo $finalNetAmount.','.$pervalue.','.$per_amount_show.','.$invoice_id.','.$taxvl.','.$resp_tax['tax_gst'];
	echo json_encode($finalNetAmount . ',' . $pervalue . ',' . $per_amount_show . ',' . $invoice_id . ',' . $taxvl . ',' . $resp_tax['tax_gst']);
} else if (strtolower($POST['mode']) == "get_all_bill_sundry") {
	$invoice_id = $POST['invoice_id'];

	$q = $dbcon->query("select b.*,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id left join tbl_ledger as le on le.l_id=b.sundry_ledger_id where b.sundry_voucher_id='$invoice_id' and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' and le.default_sundry='0' ");

	$resp = brp_mysqli_fetch_all($q);

	$str = "";
	$cnt = 1;
	foreach ($resp as $r) {

		if ($r['sundry_type'] == 1) {

			$per_amount_show = '';
		} else if ($r['sundry_type'] == 2) {

			$per_amount_show = '(' . $r['sundry_default_value'] . '%' . ')';
		}
		if (empty($r['sundry_gst_per'])) {

			$sundry_amount = ($r['currency_id'] == $_SESSION['currency_id']) ? $r['sundry_amount'] : $r['sundry_amount_conv'];

			$str .= '<div class="form-group">
				<label class="col-md-5 control-label">' . $r['l_name'] . ' <span class="currency_icon"></span></label>
				<div class="col-md-4">
				<input id="sundry_name" name="bill_sundry_addon[' . $r['l_id'] . ']" type="hidden" value="' . $sundry_amount . '">
				<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="' . $sundry_amount . '" readonly placeholder="Amount">
				</div>
				<div class="col-md-3">
				<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
				type="button" value="' . $cnt . '" onclick="removeSundry(\'\',\'' . $sundry_amount . '\',this.value,\'' . $r['sundry_id'] . '\')"><i class="fa fa-times"></i></button>
				</div>
				</div>';
		} else {
			$sundry_amount = ($r['currency_id'] == $_SESSION['currency_id']) ? $r['sundry_amount'] : $r['sundry_amount_conv'];
			$sundry_gst_amount = ($r['currency_id'] == $_SESSION['currency_id']) ? $r['sundry_gst_amount'] : $r['sundry_gst_amount_conv'];

			$str .= '<div class="form-group">
				<label class="col-md-5 control-label">' . $r['l_name'] . ' <span class="currency_icon"></span></label>
				<div class="col-md-4">
				<input id="sundry_name" name="bill_sundry_addon[' . $r['l_id'] . ']" type="hidden" value="' . $sundry_amount . '">
				<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="' . $sundry_amount . '" readonly placeholder="Amount">
				<input class="addontax" name="bill_sundry_addon_tax[' . $r['l_id'] . ']" type="hidden" value="' . $sundry_gst_amount . '-' . $r['sundry_gst_per'] . '-' . $sundry_amount . '" >
				</div>
				<div class="col-md-3">
				<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
				type="button" value="' . $cnt . '" onclick="removeSundry(\'\',\'' . $sundry_amount . '\',this.value,\'' . $r['sundry_id'] . '\')"><i class="fa fa-times"></i></button>
				</div>
				</div>';
		}

		$cnt++;
		//$str.=$r['sundry_amount'];
	}

	echo $str;
	//echo json_encode($resp);
} else if (strtolower($POST['mode']) == "get_sales_order_details") {
	$resp = '';
	$company_config = getCompanyConfiguration($dbcon);
	if ($POST['isallocate'] == 1) {
		$where = '';
		if ($company_config['crm_sales_order_user_selecation'] == 1) {
			$where = " and user_id = " . $POST['user_id'];
		}
		$query = "select * from tbl_sales_order where sales_order_status=0 and approve_status not in (0,1) and cust_id=" . $POST['cust_id'] . " and invoice_status=0 and company_id=" . $_SESSION['company_id'] . " and branch_id=" . $POST['branch_id'] . " " . $where;

		$rs_prel = brp_mysqli_fetch_all($dbcon->query($query));

		foreach ($rs_prel as $result) {

			$resp .= '<div class="row">
				<div class="col-md-6"><label>' . $result['sales_order_no'] . ' </label></div>
				<div class="col-md-4" ><input type="checkbox" class="sales_order" value="' . $result['sales_order_id'] . '"  ></div>
				</div><br>';
		}
	} else if ($POST['isallocate'] == 2) {
		/*$qty = $dbcon->query("SELECT * FROM tbl_reserve_stock WHERE stock_flage = 1 AND stock_status=0 AND company_id = '".$_SESSION['company_id']."' AND sales_order_trn_id !=0");

			var_dump("SELECT * FROM tbl_reserve_stock WHERE stock_flage = 1 AND stock_status=0 AND company_id = '".$_SESSION['company_id']."' AND sales_order_trn_id !=0");

			var_dump("SELECT so.sales_order_no,so.sales_order_id,group_concat(res.reserve_id) as reserved_id,sum(base_stock) as reserve_qty FROM tbl_sales_ordertrn as sot
			left join tbl_sales_order as so on so.sales_order_id=sot.sales_order_id 
			left join tbl_reserve_stock as res on res.sales_order_trn_id=sot.sales_ordertrn_id
			where so.cust_id=".$POST['cust_id']." and so.invoice_status=0 and res.company_id=".$_SESSION['company_id']." and res.sales_order_trn_id !=0 and res.stock_flage = 1 AND res.stock_status=0 ".$where." group by sot.sales_order_id");*/

		/*while($res = brp_mysqli_fetch_assoc($qty)){
				$qtys= $dbcon->query("SELECT * FROM tbl_reserve_stock WHERE stock_flage = 2 AND company_id = '".$_SESSION['company_id']."' AND ref_id = ".$res['reserve_id']);
			
			var_dump("SELECT * FROM tbl_reserve_stock WHERE stock_flage = 2 AND company_id = '".$_SESSION['company_id']."' AND ref_id = ".$res['reserve_id']);
				if(brp_mysqli_num_rows($qtys)==0){
					$rese = brp_mysqli_fetch_assoc($qtys);
					
					$where = '';
					if($company_config['crm_sales_order_user_selecation']==1){
						$where = " and so.user_id = ".$POST['user_id'];
					}

					$query = $dbcon->query("SELECT so.sales_order_no,so.sales_order_id FROM tbl_sales_ordertrn as sot left join tbl_sales_order as so on so.sales_order_id=sot.sales_order_id where so.cust_id=".$POST['cust_id']." and so.invoice_status=0 and so.company_id=".$_SESSION['company_id']." and sot.sales_ordertrn_id = ".$res['sales_order_trn_id'].$where);

					var_dump("SELECT so.sales_order_no,so.sales_order_id FROM tbl_sales_ordertrn as sot left join tbl_sales_order as so on so.sales_order_id=sot.sales_order_id where so.cust_id=".$POST['cust_id']." and so.invoice_status=0 and so.company_id=".$_SESSION['company_id']." and sot.sales_ordertrn_id = ".$res['sales_order_trn_id'].$where);
					
					while($rs_prel= brp_mysqli_fetch_assoc($query)){
						$resp.='<div class="row">
						<div class="col-md-6"><label>'.$rs_prel['sales_order_no'].' </label></div>
						<div class="col-md-4" ><input type="checkbox" class="sales_order" value="'.$rs_prel['sales_order_id'].'"  id="'.$rs_prel['sales_order_id'].'"></div>
						</div><br>';
					}
				}
			}*/

		$adasa = "select  so.sales_order_no,so.sales_order_id from tbl_sales_order as so
			left join tbl_sales_ordertrn as strn on strn.sales_order_id = so.sales_order_id
			where so.invoice_status=0 and so.sales_order_status=0 and so.order_accept_status=1 and cust_id=" . $POST['cust_id'] . " and so.company_id=" . $_SESSION['company_id'] . " group by strn.sales_order_id";

		$result = $dbcon->query($adasa);

		while ($row = brp_mysqli_fetch_array($result)) {
			$strn = "select group_concat(sales_ordertrn_id) as sales_ordertrn_id from tbl_sales_ordertrn where sales_ordertrn_status=0 and invoice_status=0 and sales_order_id=" . $row['sales_order_id'];
			$rstr = $dbcon->query($strn);
			$strrr = brp_mysqli_fetch_array($rstr);

			$qplus = "select IFNULL(sum(base_stock),0) as plus_stock from tbl_reserve_stock as re where stock_status!=2 and stock_flage=1 and re.sales_order_trn_id in (" . $strrr['sales_ordertrn_id'] . ")";
			$rplus = $dbcon->query($qplus);
			$ropl  = brp_mysqli_fetch_array($rplus);

			$qminus = "select IFNULL(sum(base_stock),0) as minus_stock from tbl_reserve_stock as re where stock_status!=2 and stock_flage=2 and re.sales_order_trn_id in (" . $strrr['sales_ordertrn_id'] . ")";
			$rminus = $dbcon->query($qminus);
			$romi  = brp_mysqli_fetch_array($rminus);

			$current_stock = $ropl['plus_stock'] - $romi['minus_stock'];

			if ($current_stock > 0) {
				$resp .= '<div class="row">
						<div class="col-md-6"><label>' . $row['sales_order_no'] . ' </label></div>
						<div class="col-md-4" ><input type="checkbox" class="sales_order" value="' . $row['sales_order_id'] . '"  id="' . $row['sales_order_id'] . '"></div>
					</div><br>';
			}
		}
	} else if ($POST['isallocate'] == 3) {
		$query_paking = "select so_paking_no,so_paking_id from so_paking as re where status!=2 and invoice_status=0 and company_id=" . $_SESSION['company_id'] . " and so_paking_cust_id=" . $POST['cust_id'];
		$result_paking = $dbcon->query($query_paking);
		while ($row_paking = brp_mysqli_fetch_array($result_paking)) {
			$resp .= '<div class="row">
						<div class="col-md-6">
							<label>' . $row_paking['so_paking_no'] . ' </label>
						</div>
						<div class="col-md-4" >
							<input type="checkbox" class="sales_order" value="' . $row_paking['so_paking_id'] . '"  id="' . $row_paking['so_paking_id'] . '">
						</div>
					</div><br>';
		}
	} else {
		$resp .= '';
	}

	echo json_encode($resp);
} else if (strtolower($_POST['mode']) == "get_hsn_code") {
    // Assuming $dbcon is your mysqli connection object
    $stmt = $dbcon->prepare("SELECT hc.hsn_code FROM `product_mst` AS pm
                             JOIN mst_hsn_code AS hc ON pm.product_hsn = hc.hsn_id AND hc.hsn_status = 0
                             WHERE pm.product_id = ?");

    $stmt->bind_param("i", $_POST['product_id']); // 'i' for integer
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        print_r($row['hsn_code']);
    }
    $stmt->close();
} else if (strtolower($POST['mode']) == "add_sales_order") {
	$company_state = get_company_data($dbcon, $_SESSION['company_id']);
	$custLedgerDetails = get_cust_data_arr($dbcon, $POST['cust_id']);
	$companyConfiguration = getCompanyConfiguration($dbcon);
	$isdelete['trancation_status'] = 2;
	$updatesalesid = update_record('tbl_invoicetrn', $isdelete, "invoice_id=0", $dbcon);

	$istaxdelete['tx_status'] = 2;
	$updatesalesid = update_record('tbl_tax_trn', $istaxdelete, "tx_transaction_type='tbl_invoicetrn' and tx_status=3", $dbcon);
	if ($POST['transaction_type'] != 3) {
		foreach ($POST['sales_order'] as $sale_id) {

			$qty = $dbcon->query("SELECT payment_terms, enable_transport, consignee_id FROM tbl_sales_order WHERE sales_order_id = " . $sale_id);
			$re = brp_mysqli_fetch_assoc($qty);
			$resp['payment_terms'] = $re['payment_terms'];
			$resp['enable_transport'] = $re['enable_transport'];
			$resp['enable_consignee'] = ($re['consignee_id']) ? 0 : 1;
			$resp['consignee_id'] = $re['consignee_id'];

			$qry = "SELECT * FROM `tbl_sales_ordertrn` where sales_order_id=" . $sale_id . " and sales_ordertrn_status=0";

			$get_sales_order = brp_mysqli_fetch_all($dbcon->query($qry));
			$POST['transaction_type'];

			$info_sale['billing_type'] = $POST['transaction_type'];
			//$updateid=update_record('tbl_sales_order', $info_sale, "sales_order_id=".$sale_id , $dbcon);
			foreach ($get_sales_order as $get_sales_order_details) {
				/////////////////////////////////////////////////////////////////////////Harshil- 8-2-2023///////////////////////////////////////////////////////////////////
				// $qry_type = "SELECT pmst.product_type,ptype.is_service FROM product_mst as pmst left join pro_ms_product_type as ptype on pmst.product_type=ptype.product_type_id where pmst.product_id=".$get_sales_order_details['product_id'];

				//$get_sales_order_type = brp_mysqli_fetch_assoc($dbcon->query($qry_type));

				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				//var_dump($get_sales_order_details['with_out_stock_invoice']);
				if ($get_sales_order_details['with_out_stock_invoice'] == 1) {
					$transaction_type = 1;
				} else {
					$transaction_type = $POST['transaction_type'];
				}
				//var_dump($transaction_type);
				if ($transaction_type == 2) {


					//////////////////////////////////////////////////////////Harshil - 8-2-203////////////////////////////////////////////////////
					//if($get_sales_order_type['is_service']==0)
					//{
					$chkqty = $dbcon->query("SELECT sum(base_stock) as rmqty,sum(convert_stock) as rcmqty,GROUP_CONCAT(reserve_id) as reid FROM tbl_reserve_stock where stock_flage = 1 AND stock_status=0 AND company_id = '" . $_SESSION['company_id'] . "' AND sales_order_trn_id = " . $get_sales_order_details['sales_ordertrn_id']);
					$qtycount = brp_mysqli_num_rows($chkqty);
					//}
					//else
					//{
					//$qtycount = 1;
					//}
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					if ($qtycount > 0) {
						//////////////////////////////////////////////////////////Harshil - 8-2-203////////////////////////////////////////////////////

						//if($get_sales_order_type['is_service']==0)
						//{
						$getqty = brp_mysqli_fetch_assoc($chkqty);
						$qtys = $dbcon->query("SELECT sum(base_stock) as rused,sum(convert_stock) as rcused FROM tbl_reserve_stock WHERE stock_flage = 2 AND stock_status=0 AND company_id = '" . $_SESSION['company_id'] . "' AND perent_id in (" . $getqty['reid'] . ")");
						$product_qty = 0;
						$getqty_used = brp_mysqli_fetch_assoc($qtys);
						$pending_i_qty = $getqty['rmqty'] - $getqty_used['rused'];
						$pending_ic_qty = $getqty['rcmqty'] - $getqty_used['rcused'];

						//}
						//else
						//{
						//$pending_i_qty = 1;
						//}
						//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						if ($pending_i_qty > 0) {

							if ($get_sales_order_details['remaning_invoice_qty'] > $pending_i_qty) {
								$product_qty = $pending_i_qty;
							} else if ($get_sales_order_details['remaning_invoice_qty'] < $pending_i_qty) {
								$product_qty = $get_sales_order_details['remaning_invoice_qty'];
							} else {
								$product_qty = $pending_i_qty;
							}

							if ($get_sales_order_details['remaning_invoice_conv_qty'] > $pending_ic_qty) {
								$product_conv_qty = $pending_ic_qty;
							} else if ($get_sales_order_details['remaning_invoice_conv_qty'] < $pending_ic_qty) {
								$product_conv_qty = $get_sales_order_details['remaning_invoice_conv_qty'];
							} else {
								$product_conv_qty = $pending_ic_qty;
							}
							// print_r($product_qty);exit;

							$hsn_details = brp_mysqli_fetch_assoc($dbcon->query("SELECT hc.sale_gst,hc.hsn_code,t.tax_gst,pm.batch_wise_stock_manage FROM `product_mst` as pm join mst_hsn_code as hc on hc.hsn_id=pm.product_hsn and hsn_status=0 left join tbl_tax_category as t on t.tax_cat_id=hc.sale_gst where pm.product_id=" . $get_sales_order_details['product_id'] . " "));

							$cgst_tax_rate = 0;
							$cgst_tax_rate_conv = 0;
							$sgst_tax_rate = 0;
							$sgst_tax_rate_conv = 0;
							$igst_tax_rate = 0;
							$igst_tax_rate_conv = 0;

							$type = "conv_unit";
							// $product_conv_qty = convert_stock($dbcon, $product_qty, $get_sales_order_details['product_id'], $type);
							if ($get_sales_order_details['rate_unit'] == $get_sales_order_details['unit_id']) {
								$product_amt = ($get_sales_order_details['product_rate'] * $product_qty) - $get_sales_order_details['product_discount'];

								$product_amt_conv = ($get_sales_order_details['product_rate_conv'] * $product_qty) - $get_sales_order_details['product_discount_conv'];
							} else {
								$product_amt = ($get_sales_order_details['product_rate'] * $product_conv_qty) - $get_sales_order_details['product_discount'];

								$product_amt_conv = ($get_sales_order_details['product_rate_conv'] * $product_conv_qty) - $get_sales_order_details['product_discount_conv'];
							}



							if (($company_state['stateid'] == $POST['cust_stateid']) && ($custLedgerDetails['enable_sez'] == 0)) {
								$gst 			= $hsn_details['tax_gst'] / 2;
								$cgst_tax_per 	= $gst;
								$cgst_tax_rate 	= ($gst * $product_amt) / 100;
								$cgst_tax_rate_conv = ($gst * $product_amt_conv) / 100;
								$sgst_tax_per 	= $gst;
								$sgst_tax_rate 	= ($gst * $product_amt) / 100;
								$sgst_tax_rate_conv = ($gst * $product_amt_conv) / 100;
							} else {
								$gst 			= $hsn_details['tax_gst'];
								$igst_tax_per = $hsn_details['tax_gst'];
								$igst_tax_rate = ($hsn_details['tax_gst'] * $product_amt) / 100;
								$igst_tax_rate_conv = ($gst * $product_amt_conv) / 100;
							}

							$info1['product_id']		= $get_sales_order_details['product_id'];
							if ($companyConfiguration['so_invo_descri_transfer'] == 1) {
								$info1['description']		= $get_sales_order_details['description'];
								$info1['product_spec']		= $get_sales_order_details['product_spec'];
							}


							$info1['product_hsn_code']	= $hsn_details['hsn_code'];
							$info1['product_qty']		= $product_qty;
							$info1['product_conv_qty']	= $product_conv_qty;
							$info1['unit_id']			= $get_sales_order_details['unit_id'];
							$info1['conv_unit_id']		= $get_sales_order_details['conv_unit_id'];
							$info1['rate_unit']			= $get_sales_order_details['rate_unit'];

							$info1['currency_id']		= $get_sales_order_details['currency_id'];
							$info1['currency_rate']		= $get_sales_order_details['currency_rate'];

							$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0;
							$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0;
							$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0;

							$info1['product_discount']	= $get_sales_order_details['product_discount'];
							$info1['product_rate']		= $get_sales_order_details['product_rate'];

							$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
							$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
							$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0;

							$info1['product_amount']		= $product_amt;
							$info1['taxable_value']			= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
							$info1['total'] 			= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate + $product_amt;

							$info1['product_rate_conv']	= $get_sales_order_details['product_rate_conv'];

							$info1['cgst_tax_rate_conv']		= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
							$info1['sgst_tax_rate_conv']		= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
							$info1['igst_tax_rate_conv']		= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;

							$info1['product_discount_conv']	= $get_sales_order_details['product_discount_conv'];
							$info1['product_amount_conv']	= $product_amt_conv;
							$info1['taxable_value_conv'] 	= $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv;
							$info1['total_conv'] 			= $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv + $product_amt_conv;

							$info1['discount_per']		= $get_sales_order_details['discount_per'];
							$info1['company_id']		= $_SESSION['company_id'];
							$info1['transaction_type'] 	= 2;
							$info1['so_allocation_id'] 	= $get_sales_order_details['sales_ordertrn_id'];
							$info1['sales_ordertrn_id'] = $get_sales_order_details['sales_ordertrn_id'];
							$info1['user_id']			= $_SESSION['user_id'];
							$info1['trancation_status']	= 1;

							$info1['orange']			= $get_sales_order_details['orange'];
							$info1['mfg']				= $get_sales_order_details['mfg'];
							$info1['trading']			= $get_sales_order_details['trading'];
							$info1['repairing']			= $get_sales_order_details['repairing'];
							$info1['other']				= $get_sales_order_details['other'];
							$info1['orange_total']					= $get_sales_order_details['orange_total'];
							$info1['mfg_total']					= $get_sales_order_details['mfg_total'];
							$info1['trading_total']				= $get_sales_order_details['trading_total'];
							$info1['repairing_total']				= $get_sales_order_details['repairing_total'];
							$info1['other_total']					= $get_sales_order_details['other_total'];
							$info1['with_out_stock_invoice'] = $get_sales_order_details['with_out_stock_invoice'];
							//var_dump($info1);
							$table = 'tbl_invoicetrn';
							$inserid = add_record($table, $info1, $dbcon, $branch_id);
							//var_dump("sss");
							entry_batch_stock_temp_table($dbcon, $inserid);

							//Batch wise stock entry in tmp table - dhruv
							// if($hsn_details['batch_wise_stock_manage'] == 1){

							// 	$query="SELECT * FROM `tbl_stock_trn` WHERE `product_id` = ".$get_sales_order_details['product_id']." and batch_no != '' ";
							// 	$rel=$dbcon->query($query);
							// 	$rs_batch=brp_mysqli_fetch_all($rel);
							// 	$usedqty=0;
							// 	foreach ($rs_batch as $batchwise) {

							// 		$usedqty = $product_qty-$batchwise['base_stock'];
							// 		if($usedqty > 0){
							// 			$infobatch['invoice_trn_id'] = $inserid;
							// 			$infobatch['product_id']   = $get_sales_order_details['product_id'];
							// 			$infobatch['stock_id']   = $batchwise['stock_id'];
							// 			$infobatch['qty']   		= $usedqty ;
							// 			$infobatch['unitid']   	= $get_sales_order_details['unit_id'];
							// 			$infobatch['status'] = 1;
							// 			$infobatch['cdate']		= date("Y-m-d H:i:s");
							// 			$infobatch['user_id']	= $_SESSION['user_id'];
							// 			$infobatch['company_id']	= $_SESSION['company_id'];		

							// 			$inserbatchstockid=add_record('tbl_batch_stock_tmp', $infobatch, $dbcon);
							// 		}
							// 	}

							// }

							// $info_allo['remaning_invoice_qty']=0;	
							// $update_alloid=update_record('tbl_sales_order_production_trn', $info_allo, "sales_order_production_trn_id=".$get_sales_order_production_trn['sales_order_production_trn_id'] , $dbcon);

							/* insert to tax transaction table by Dhruv */
							if (($cgst_tax_per != 0) && ($cgst_tax_rate != 0)) {
								$cl_id = get_ledger_by_name($dbcon, 'CGST');
								$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $cgst_tax_per, $cgst_tax_rate, $inserid, "tbl_invoicetrn", $get_sales_order_details['product_id'], 3, '', '', $get_sales_order_details['currency_id'], $get_sales_order_details['currency_rate'], $cgst_tax_rate_conv);
							}
							if (($sgst_tax_per != 0) && ($sgst_tax_rate != 0)) {
								$cl_id = get_ledger_by_name($dbcon, 'SGST');
								$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $sgst_tax_per, $sgst_tax_rate, $inserid, "tbl_invoicetrn", $get_sales_order_details['product_id'], 3, '', '', $get_sales_order_details['currency_id'], $get_sales_order_details['currency_rate'], $sgst_tax_rate_conv);
							}
							if (($igst_tax_per != 0) && ($igst_tax_rate != 0)) {
								$cl_id = get_ledger_by_name($dbcon, 'IGST');
								$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $igst_tax_per, $igst_tax_rate, $inserid, "tbl_invoicetrn", $get_sales_order_details['product_id'], 3, '', '', $get_sales_order_details['currency_id'], $get_sales_order_details['currency_rate'], $igst_tax_rate_conv);
							}
						}
					}
				} else if ($transaction_type == 1) {

					$current_stock = get_current_stock_new($dbcon, $get_sales_order_details['product_id'], $get_sales_order_details['unit_id']);

					if ($get_sales_order_details['with_out_stock_invoice'] == 1) {
						$negativestock = 1;
					} else {
						$negativestock = $companyConfiguration['enable_negative_qty'];
					}

					if ($get_sales_order_details['remaning_invoice_qty'] != 0 && ($current_stock > 0 || $negativestock == 1)) {

						$hsn_details = brp_mysqli_fetch_assoc($dbcon->query("SELECT hc.sale_gst,hc.hsn_code,t.tax_gst,pm.batch_wise_stock_manage FROM `product_mst` as pm join mst_hsn_code as hc on hc.hsn_id=pm.product_hsn and hsn_status=0 left join tbl_tax_category as t on t.tax_cat_id=hc.sale_gst where pm.product_id=" . $get_sales_order_details['product_id'] . " "));

						if ($rel_grn['gst_type'] == 3) {
							$hsn_details['tax_gst'] = 0.1;
						} else if ($rel_grn['gst_type'] == 4) {
							$hsn_details['tax_gst'] = 0;
						} else if ($rel_grn['gst_type'] == 5) {
							$hsn_details['tax_gst'] = 5;
						} else if ($rel_grn['gst_type'] == 6) {
							$hsn_details['tax_gst'] = 12;
						} else if ($rel_grn['gst_type'] == 7) {
							$hsn_details['tax_gst'] = 18;
						} else if ($rel_grn['gst_type'] == 8) {
							$hsn_details['tax_gst'] = 24;
						}
						$product_qty = $get_sales_order_details['remaning_invoice_qty'];
						$product_conv_qty = $get_sales_order_details['remaning_invoice_conv_qty'];
						$type = "conv_unit";
						// $product_conv_qty = convert_stock($dbcon, $product_qty, $get_sales_order_details['product_id'], $type);

						if ($get_sales_order_details['rate_unit'] == $get_sales_order_details['unit_id']) {
							$product_amt = ($get_sales_order_details['product_rate'] * $product_qty) - $get_sales_order_details['product_discount'];

							$product_amt_conv = ($get_sales_order_details['product_rate_conv'] * $product_qty) - $get_sales_order_details['product_discount_conv'];
						} else {
							$product_amt = ($get_sales_order_details['product_rate'] * $product_conv_qty) - $get_sales_order_details['product_discount'];

							$product_amt_conv = ($get_sales_order_details['product_rate_conv'] * $product_conv_qty) - $get_sales_order_details['product_discount_conv'];
						}

						$cgst_tax_rate = 0;
						$cgst_tax_rate_conv = 0;
						$sgst_tax_rate = 0;
						$sgst_tax_rate_conv = 0;
						$igst_tax_rate = 0;
						$igst_tax_rate_conv = 0;

						if (($company_state['stateid'] == $POST['cust_stateid']) && ($custLedgerDetails['enable_sez'] == 0)) {
							$gst = $hsn_details['tax_gst'] / 2;
							$cgst_tax_per = $gst;
							$cgst_tax_rate = ($gst * $product_amt) / 100;
							$cgst_tax_rate_conv = ($gst * $product_amt_conv) / 100;

							$sgst_tax_per = $gst;
							$sgst_tax_rate = ($gst * $product_amt) / 100;
							$sgst_tax_rate_conv = ($gst * $product_amt_conv) / 100;
						} else {
							$igst_tax_per = $hsn_details['tax_gst'];
							$igst_tax_rate = ($hsn_details['tax_gst'] * $product_amt) / 100;

							$igst_tax_rate_conv = ($hsn_details['tax_gst'] * $product_amt_conv) / 100;
						}

						$info1['product_id']		= $get_sales_order_details['product_id'];
						if ($companyConfiguration['so_invo_descri_transfer'] == 1) {
							$info1['description']		= $get_sales_order_details['description'];
							$info1['product_spec']		= $get_sales_order_details['product_spec'];
						}

						//$info1['ser_status']		= $POST['ser_status'];
						$info1['product_hsn_code']	= $hsn_details['hsn_code'];
						$info1['product_qty']		= $product_qty;
						$info1['product_conv_qty']		= $product_conv_qty;
						$info1['product_rate']		= $get_sales_order_details['product_rate'];
						//$info1['product_disc']		= $POST['product_disc'];
						$info1['unit_id']			= $get_sales_order_details['unit_id'];
						$info1['conv_unit_id']			= $get_sales_order_details['conv_unit_id'];
						$info1['rate_unit']			= $get_sales_order_details['rate_unit'];

						$info1['currency_id']		= $get_sales_order_details['currency_id'];
						$info1['currency_rate']		= $get_sales_order_details['currency_rate'];

						$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0;
						$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
						$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0;
						$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
						$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0;
						$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0;

						$info1['product_discount']	= $get_sales_order_details['product_discount'];
						$info1['product_amount']	= $product_amt;
						$info1['taxable_value']		= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
						$info1['total'] 			= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate + $product_amt;

						$info1['product_rate_conv']	= $get_sales_order_details['product_rate_conv'];

						$info1['cgst_tax_rate_conv']		= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
						$info1['sgst_tax_rate_conv']		= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
						$info1['igst_tax_rate_conv']		= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;

						$info1['product_discount_conv']	= $get_sales_order_details['product_discount_conv'];
						$info1['product_amount_conv']	= $get_sales_order_details['product_amount_conv'];
						$info1['taxable_value_conv'] 	= $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv;
						$info1['total_conv'] 			= $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv + $product_amt_conv;

						$info1['discount_per']		= $get_sales_order_details['discount_per'];
						$info1['company_id']		= $_SESSION['company_id'];
						$info1['transaction_type'] = 1;
						$info1['so_allocation_id'] = $get_sales_order_details['sales_ordertrn_id'];
						$info1['sales_ordertrn_id'] = $get_sales_order_details['sales_ordertrn_id'];
						$info1['user_id']	= $_SESSION['user_id'];

						$info1['trancation_status']	= 1;

						$info1['orange']			= $get_sales_order_details['orange'];
						$info1['mfg']				= $get_sales_order_details['mfg'];
						$info1['trading']			= $get_sales_order_details['trading'];
						$info1['repairing']			= $get_sales_order_details['repairing'];
						$info1['other']				= $get_sales_order_details['other'];

						$info1['orange_total']					= $get_sales_order_details['orange_total'];
						$info1['mfg_total']					= $get_sales_order_details['mfg_total'];
						$info1['trading_total']				= $get_sales_order_details['trading_total'];
						$info1['repairing_total']				= $get_sales_order_details['repairing_total'];
						$info1['other_total']					= $get_sales_order_details['other_total'];
						$info1['with_out_stock_invoice'] = $get_sales_order_details['with_out_stock_invoice'];

						$table = 'tbl_invoicetrn';

						$inserid = add_record($table, $info1, $dbcon, $branch_id);
						entry_batch_stock_temp_table($dbcon, $inserid);

						//Batch wise stock entry in tmp table - dhruv
						//code comment by pathik 24-2-2023	start//
						/*if($hsn_details['batch_wise_stock_manage'] == 1){

							$query="SELECT * FROM `tbl_stock_trn` WHERE `product_id` = ".$get_sales_order_details['product_id']." and batch_no != '' ";
							$rel=$dbcon->query($query);
							$rs_batch=brp_mysqli_fetch_all($rel);
							$usedqty=0;
							foreach ($rs_batch as $batchwise) {

								$usedqty = $get_sales_order_details['remaning_invoice_qty']-$batchwise['base_stock'];
								if($usedqty > 0){
									$infobatch['invoice_trn_id'] = $inserid;
									$infobatch['product_id']   = $get_sales_order_details['product_id'];
									$infobatch['stock_id']   = $batchwise['stock_id'];
									$infobatch['qty']   		= $usedqty ;
									$infobatch['unitid']   	= $get_sales_order_details['unit_id'];
									$infobatch['status'] = 1;
									$infobatch['cdate']		= date("Y-m-d H:i:s");
									$infobatch['user_id']	= $_SESSION['user_id'];
									$infobatch['company_id']	= $_SESSION['company_id'];		

									$inserbatchstockid=add_record('tbl_batch_stock_tmp', $infobatch, $dbcon);
								}
							}

						}*/
						//code comment by pathik 24-2-2023	end//

						// $info_so['remaning_invoice_qty']=0;	
						// $info_so['invoice_status']=1;
						// $update_soid=update_record('tbl_sales_ordertrn', $info_so, "sales_ordertrn_id=".$get_sales_order_details['sales_ordertrn_id'] , $dbcon);

						/* insert to tax transaction table by Dhruv */
						if (($cgst_tax_per != 0) && ($cgst_tax_rate != 0)) {
							$cl_id = get_ledger_by_name($dbcon, 'CGST');
							$insert_tax = add_tax_transaction_record(
								$dbcon,
								$cl_id['l_id'],
								$cgst_tax_per,
								$cgst_tax_rate,
								$inserid,
								"tbl_invoicetrn",
								$get_sales_order_details['product_id'],
								3,
								'',
								'',
								$get_sales_order_details['currency_id'],
								$get_sales_order_details['currency_rate'],
								$cgst_tax_rate_conv
							);
						}
						if (($sgst_tax_per != 0) && ($sgst_tax_rate != 0)) {
							$cl_id = get_ledger_by_name($dbcon, 'SGST');
							$insert_tax = add_tax_transaction_record(
								$dbcon,
								$cl_id['l_id'],
								$sgst_tax_per,
								$sgst_tax_rate,
								$inserid,
								"tbl_invoicetrn",
								$get_sales_order_details['product_id'],
								3,
								'',
								'',
								$get_sales_order_details['currency_id'],
								$get_sales_order_details['currency_rate'],
								$sgst_tax_rate_conv
							);
						}
						if (($igst_tax_per != 0) && ($igst_tax_rate != 0)) {
							$cl_id = get_ledger_by_name($dbcon, 'IGST');
							$insert_tax = add_tax_transaction_record(
								$dbcon,
								$cl_id['l_id'],
								$igst_tax_per,
								$igst_tax_rate,
								$inserid,
								"tbl_invoicetrn",
								$get_sales_order_details['product_id'],
								3,
								'',
								'',
								$get_sales_order_details['currency_id'],
								$get_sales_order_details['currency_rate'],
								$igst_tax_rate_conv
							);
						}
					}
				}
			}
		}
	} else {
		foreach ($POST['sales_order'] as $sale_pkp) {

			$sitr['invoice_status'] = 3;
			$sitr['invoice_user_id'] = $_SESSION['user_id'];
			//$sitr['invoice_trn_id'] = 3;
			$updatesalesid = update_record('so_paking_trn', $sitr, "so_paking_id=" . $sale_pkp, $dbcon);
		}

		$ress = "select sum(so_paking_qty) as pqty,group_concat(so_paking_trn_id) as trn_id,so_paking_so_trn_id,so_trn.product_id,so_trn.product_rate,so_trn.unit_id  from so_paking_trn as trn
					left join tbl_sales_ordertrn as so_trn on so_trn.sales_ordertrn_id= trn.so_paking_so_trn_id
					where  trn.invoice_status=3 and trn.status=0 and trn.invoice_user_id=" . $_SESSION['user_id'] . " group by trn.so_paking_so_trn_id";
		$qry_adds = $dbcon->query($ress);
		while ($row1s = brp_mysqli_fetch_array($qry_adds)) {

			$pamount = $row1s['pqty'] * $row1s['product_rate'];
			$hsn_details = brp_mysqli_fetch_assoc($dbcon->query("SELECT hc.sale_gst,hc.hsn_code,t.tax_gst,pm.batch_wise_stock_manage FROM `product_mst` as pm join mst_hsn_code as hc on hc.hsn_id=pm.product_hsn and hsn_status=0 left join tbl_tax_category as t on t.tax_cat_id=hc.sale_gst where pm.product_id=" . $row1s['product_id'] . " "));

			if ($rel_grn['gst_type'] == 3) {
				$hsn_details['tax_gst'] = 0.1;
			} else if ($rel_grn['gst_type'] == 4) {
				$hsn_details['tax_gst'] = 0;
			} else if ($rel_grn['gst_type'] == 5) {
				$hsn_details['tax_gst'] = 5;
			} else if ($rel_grn['gst_type'] == 6) {
				$hsn_details['tax_gst'] = 12;
			} else if ($rel_grn['gst_type'] == 7) {
				$hsn_details['tax_gst'] = 18;
			} else if ($rel_grn['gst_type'] == 8) {
				$hsn_details['tax_gst'] = 24;
			}

			$cgst_tax_rate = 0;
			$cgst_tax_rate_conv = 0;
			$sgst_tax_rate = 0;
			$sgst_tax_rate_conv = 0;
			$igst_tax_rate = 0;
			$igst_tax_rate_conv = 0;

			if (($company_state['stateid'] == $POST['cust_stateid']) && ($custLedgerDetails['enable_sez'] == 0)) {
				$gst = $hsn_details['tax_gst'] / 2;
				$cgst_tax_per = $gst;
				$cgst_tax_rate = ($gst * $pamount) / 100;
				$cgst_tax_rate_conv = ($gst * $pamount) / 100;

				$sgst_tax_per = $gst;
				$sgst_tax_rate = ($gst * $pamount) / 100;
				$sgst_tax_rate_conv = ($gst * $pamount) / 100;
			} else {
				$igst_tax_per = $hsn_details['tax_gst'];
				$igst_tax_rate = ($hsn_details['tax_gst'] * $pamount) / 100;

				$igst_tax_rate_conv = ($hsn_details['tax_gst'] * $pamount) / 100;
			}

			$info1['product_id']		= $row1s['product_id'];
			/*if($companyConfiguration['so_invo_descri_transfer']==1){
								$info1['description']		= $get_sales_order_details['description'];
								$info1['product_spec']		=$get_sales_order_details['product_spec'];
							}*/
			//$info1['ser_status']		= $POST['ser_status'];
			$info1['product_hsn_code']	= $hsn_details['hsn_code'];
			$info1['product_qty']		= $row1s['pqty'];
			$info1['product_conv_qty']	= $row1s['pqty'];
			$info1['product_rate']		= $row1s['product_rate'];
			//$info1['product_disc']		= $POST['product_disc'];
			$info1['unit_id']			= $row1s['unit_id'];
			$info1['conv_unit_id']		= $row1s['unit_id'];
			$info1['rate_unit']			= $row1s['unit_id'];

			$info1['currency_id']		= $row1s['currency_id'];
			$info1['currency_rate']		= $row1s['currency_rate'];

			$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0;
			$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
			$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0;
			$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
			$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0;
			$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0;

			//$info1['product_discount']	= $get_sales_order_details['product_discount'];
			$info1['product_amount']	= $pamount;
			$info1['taxable_value']		= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
			$info1['total'] 			= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate + $pamount;

			$info1['product_rate_conv']	= $row1s['product_rate'];

			$info1['cgst_tax_rate_conv']		= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
			$info1['sgst_tax_rate_conv']		= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
			$info1['igst_tax_rate_conv']		= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;

			//$info1['product_discount_conv']	= $get_sales_order_details['product_discount_conv'];
			$info1['product_amount_conv']	= $info1['product_amount'];
			$info1['taxable_value_conv'] 	= $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv;
			$info1['total_conv'] 			= $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv + $info1['product_amount'];

			//$info1['discount_per']		= $get_sales_order_details['discount_per'];
			$info1['company_id']		= $_SESSION['company_id'];
			$info1['transaction_type'] = 1;
			$info1['so_allocation_id'] = $row1s['sales_ordertrn_id'];
			$info1['sales_ordertrn_id'] = $row1s['sales_ordertrn_id'];
			$info1['user_id']	= $_SESSION['user_id'];

			$info1['trancation_status']	= 1;
			$info1['paking_wise']	= 1;

			/*$info1['orange']			= $get_sales_order_details['orange'];
							$info1['mfg']				= $get_sales_order_details['mfg'];
							$info1['trading']			= $get_sales_order_details['trading'];
							$info1['repairing']			= $get_sales_order_details['repairing'];
							$info1['other']				= $get_sales_order_details['other'];*/

			$table = 'tbl_invoicetrn';

			$inserid = add_record($table, $info1, $dbcon, $branch_id);

			/* insert to tax transaction table by Dhruv */
			if (($cgst_tax_per != 0) && ($cgst_tax_rate != 0)) {
				$cl_id = get_ledger_by_name($dbcon, 'CGST');
				$insert_tax = add_tax_transaction_record(
					$dbcon,
					$cl_id['l_id'],
					$cgst_tax_per,
					$cgst_tax_rate,
					$inserid,
					"tbl_invoicetrn",
					$row1s['product_id'],
					3,
					'',
					'',
					$row1s['currency_id'],
					$row1s['currency_rate'],
					$cgst_tax_rate_conv
				);
			}
			if (($sgst_tax_per != 0) && ($sgst_tax_rate != 0)) {
				$cl_id = get_ledger_by_name($dbcon, 'SGST');
				$insert_tax = add_tax_transaction_record(
					$dbcon,
					$cl_id['l_id'],
					$sgst_tax_per,
					$sgst_tax_rate,
					$inserid,
					"tbl_invoicetrn",
					$row1s['product_id'],
					3,
					'',
					'',
					$row1s['currency_id'],
					$row1s['currency_rate'],
					$sgst_tax_rate_conv
				);
			}
			if (($igst_tax_per != 0) && ($igst_tax_rate != 0)) {
				$cl_id = get_ledger_by_name($dbcon, 'IGST');
				$insert_tax = add_tax_transaction_record(
					$dbcon,
					$cl_id['l_id'],
					$igst_tax_per,
					$igst_tax_rate,
					$inserid,
					"tbl_invoicetrn",
					$row1s['product_id'],
					3,
					'',
					'',
					$row1s['currency_id'],
					$row1s['currency_rate'],
					$igst_tax_rate_conv
				);
			}


			$sitr1['invoice_status'] = 0;
			$sitr1['invoice_trn_id'] = $inserid;
			$updatesalesid = update_record('so_paking_trn', $sitr1, "so_paking_trn_id in (" . $row1s['trn_id'] . ")", $dbcon);
		}
	}
	if ($inserid) {
		$resp['msg'] = "1";
	} else {
		$resp['msg'] = "0";
	}
	echo json_encode($resp);
} else if (strtolower($POST['mode']) == "get_ledger_details") {

	$ledger_id = $POST['ledger_id'];

	$row = get_ledger_details($dbcon, $ledger_id);
	$res = "select cust_contact_person_name,cust_contact_person_id from tbl_cust_contact_person as trn 
					where  cust_contact_person_status=0 and cust_id='" . $POST['ledger_id']."'";
	$str = "";
	$qry_add = $dbcon->query($res);
	$str .= "<option value='' >select Kind Attn.</option>";
	while ($row1 = brp_mysqli_fetch_array($qry_add)) {
		$str .= "<option value='" . $row1['cust_contact_person_id'] . "' >" . $row1['cust_contact_person_name'] . " </option>";
	}
	$row['c_person'] = $str;

	echo json_encode($row);
} else if (strtolower($POST['mode']) == "add_tcs_details") {

	//echo '<pre>';print_r($POST);exit;

	$info['tcs_lower_rate']	= $POST['tcs_lower_rate'];
	$info['tcs_lower_rate_reason']	= $POST['tcs_lower_rate_reason'];

	$info['tcs_section']	= $POST['tcs_section'];
	$info['tcs_collection_code']	= $POST['tcs_collection_code'];
	$info['tcs_ref_no']	= $POST['tcs_ref_no'];
	$info['tcs_amt']	= $POST['tcs_amt'];

	$info['tcs_collected_on']	= date("Y-m-d", strtotime($POST['tcs_collected_on']));
	$info['tcs_invoice_date']	= date("Y-m-d", strtotime($POST['tcs_invoice_date']));
	$info['tcs_percentage']	= $POST['tcs_percentage'];

	$info['tcs_amount']	= $POST['tcs_amount'];
	$info['tcs_sur_percentage']	= $POST['tcs_sur_percentage'];
	$info['tcs_sur_percentage_amount']	= $POST['tcs_sur_percentage_amount'];
	$info['tcs_total_tax']	= $POST['tcs_total_tax'];

	$info['cdate']		= date("Y-m-d H:i:s");
	$info['user_id']	= $_SESSION['user_id'];
	$info['company_id']	= $_SESSION['company_id'];


	if ($POST['edit_id'] != '') {
		$updateid = update_record('tbl_tcs_deduction_transaction', $info, "tcs_deduct_id=" . $POST['edit_id'], $dbcon);

		if ($updateid) {
			echo "2";
		} else {
			echo "0";
		}
	} else {
		$inserid = add_record('tbl_tcs_deduction_transaction', $info, $dbcon);

		if ($inserid) {
			echo "1";
		} else {
			echo "0";
		}
	}
} else if (strtolower($POST['mode']) == "load_tcs_detail") {


	$invoice_id = $POST['invoice_id'];

	$query = "select * from tbl_tcs_deduction_transaction where tcs_sale_id='$invoice_id' and isdelete='0'";
	$select = $dbcon->query($query);
	$row = brp_mysqli_fetch_assoc($select);

	echo json_encode($row);
} else if (strtolower($POST['mode']) == "insert_product") {

	$sales_order_id = $POST['sales_order_id'];
	$invoice_id = $POST['eid'];

	$company_state = get_company_data($dbcon, $_SESSION['company_id']);
	$custLedgerDetails = get_cust_data_arr($dbcon, $POST['cust_id']);

	$qry = "SELECT * FROM `tbl_sales_ordertrn` where sales_order_id IN (" . $sales_order_id . ") and sales_ordertrn_status=0";
	$ex_q = $dbcon->query($qry);
	while ($row = brp_mysqli_fetch_assoc($ex_q)) {

		$isdelete['trancation_status'] = 2;
		$updatesalesid = update_record('tbl_invoicetrn', $isdelete, "invoice_id=0", $dbcon);

		$istaxdelete['tx_status'] = 2;
		$updatesalesid = update_record('tbl_tax_trn', $istaxdelete, "tx_transaction_type='tbl_invoicetrn' and tx_status=3", $dbcon);

		$qtys = $dbcon->query("SELECT * FROM tbl_reserve_stock WHERE stock_flage = 1 AND company_id = '" . $_SESSION['company_id'] . "' AND sales_order_trn_id =" . $row['sales_ordertrn_id']);
		while ($res = brp_mysqli_fetch_assoc($qtys)) {
			$qtyes = $dbcon->query("SELECT * FROM tbl_reserve_stock WHERE stock_flage = 2 AND company_id = '" . $_SESSION['company_id'] . "' AND perent_id = " . $res['reserve_id']);
			if (brp_mysqli_num_rows($qtyes) == 0) {

				$company_state = get_company_data($dbcon, $_SESSION['company_id']);
				$sale_gst = get_tax_cat_by_hsn($dbcon, $row['product_hsn_code']);
				$custLedgerDetails = get_cust_data_arr($dbcon, $POST['cust_id']);

				$ven_s = "select stateid from tbl_ledger where l_id=" . $POST['cust_id'];
				$ves = $dbcon->query($ven_s);
				$vers = mysqli_fetch_array($ves);
				$cgst_tax_rate = 0;
				$sgst_tax_rate = 0;
				$igst_tax_rate = 0;
				$pro_amt = $row['remaning_invoice_qty'] * $row['product_rate'];
				if (($company_state['stateid'] == $vers['stateid']) && ($custLedgerDetails['enable_sez'] == 0)) {
					$gst = $sale_gst['tax_gst'] / 2;
					$cgst_tax_per = $gst;
					$cgst_tax_rate = ($gst * $pro_amt) / 100;
					$sgst_tax_per = $gst;
					$sgst_tax_rate = ($gst * $pro_amt) / 100;
				} else {
					$igst_tax_per 	= $sale_gst['tax_gst'];
					$igst_tax_rate 	= ($sale_gst['tax_gst'] * $pro_amt) / 100;
				}

				$info1['product_id']		= $row['product_id'];
				$info1['description']		= $row['description'];
				$info1['product_spec']		= $row['product_spec'];
				$info1['product_hsn_code'] 	= $row['product_hsn_code'];
				$info1['product_qty'] 	   	= $row['remaning_invoice_qty'];
				$info1['product_rate'] 		= $row['product_rate'];
				$info1['unit_id'] 			= $row['unit_id'];
				$info1['product_amount'] 	= $pro_amt;
				$info1['total'] 	= $pro_amt + $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
				$info1['trancation_status'] = 1;
				$info1['transaction_type'] = 1;

				$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0;
				$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
				$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0;
				$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
				$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0;
				$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0;
				$info1['product_tax_cat']	= $sale_gst['tax_cat_id'];
				$info1['sales_ordertrn_id']	= $row['sales_ordertrn_id'];
				$info1['so_allocation_id'] = 	$row['sales_ordertrn_id'];
				$info1['user_id']			= $_SESSION['user_id'];


				if (!empty($invoice_id)) {
					$info1['invoice_id'] = $invoice_id;
				}

				$table = "tbl_invoicetrn";

				$inserid = add_record($table, $info1, $dbcon);
				// echo $inserid;exit;

				$product_detail = get_product_detail($dbcon, $row['product_id']);
				if ($product_detail['batch_wise_stock_manage'] == 1) {

					$query = "SELECT * FROM `tbl_stock_trn` WHERE `product_id` = " . $row['product_id'] . " and batch_no != '' ";
					$rel = $dbcon->query($query);
					$rs_batch = brp_mysqli_fetch_all($rel);
					foreach ($rs_batch as $batchwise) {

						$usedqty = $row['product_qty'] - $batchwise['base_stock'];
						if ($usedqty > 0) {
							$infobatch['invoice_trn_id'] = $inserid;
							$infobatch['product_id']   = $row['product_id'];
							$infobatch['stock_id']   = $batchwise['stock_id'];
							$infobatch['qty']   		= $usedqty;
							$infobatch['unitid']   	= $row['unit_id'];
							$infobatch['status'] = 1;
							$infobatch['cdate']		= date("Y-m-d H:i:s");
							$infobatch['user_id']	= $_SESSION['user_id'];
							$infobatch['company_id']	= $_SESSION['company_id'];

							$inserbatchstockid = add_record('tbl_batch_stock_tmp', $infobatch, $dbcon);
						}
					}
				}

				if (($cgst_tax_per != 0) && ($cgst_tax_rate != 0)) {
					$cl_id = get_ledger_by_name($dbcon, 'CGST');
					$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $cgst_tax_per, $cgst_tax_rate, $inserid, "tbl_invoicetrn", $row['product_id'], 3, '', $POST['branch_id']);
				}
				if (($sgst_tax_per != 0) && ($sgst_tax_rate != 0)) {
					$cl_id = get_ledger_by_name($dbcon, 'SGST');
					$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $sgst_tax_per, $sgst_tax_rate, $inserid, "tbl_invoicetrn", $row['product_id'], 3, '', $POST['branch_id']);
				}
				if (($igst_tax_per != 0) && ($igst_tax_rate != 0)) {
					$cl_id = get_ledger_by_name($dbcon, 'IGST');
					$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $igst_tax_per, $igst_tax_rate, $inserid, "tbl_invoicetrn", $row['product_id'], 3, '', $POST['branch_id']);
				}

				$count_add_tax = get_check_addition_tax($dbcon, $sale_gst['tax_cat_id'], $pro_amt, $inserid, $row['product_id'], 0, $POST['branch_id'], 'tbl_invoicetrn');
			}
		}
	}
} else if (strtolower($POST['mode']) == "get_so_detail") {

	$cust_id = $POST['cust_id'];
	$q = $dbcon->query("select * from tbl_salesorder where cust_id='$cust_id'");
} else if (strtolower($POST['mode']) == "get_sales_bill_sundry") {
	if ($POST['type'] == 0) {
		$id = $POST['id'];

		$q = $dbcon->query("select b.*,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id left join tbl_ledger as le on le.l_id=b.sundry_ledger_id where b.sundry_voucher_id='$id' and b.sundry_voucher_table='tbl_sales_order' and b.isdelete='0' and le.default_sundry='0' ");
		$resp = brp_mysqli_fetch_all($q);
		$str = "";
		$cnt = 1;
		foreach ($resp as $r) {
			if ($r['sundry_type'] == 1) {
				$per_amount_show = '';
			} else if ($r['sundry_type'] == 2) {
				$per_amount_show = '(' . $r['sundry_default_value'] . '%' . ')';
			}
			if (empty($r['sundry_gst_per'])) {
				$str .= '<div class="form-group">
						<label class="col-md-5 control-label">' . $r['l_name'] . '</label>
						<div class="col-md-4">
						<input id="sundry_name" name="bill_sundry_addon[' . $r['l_id'] . ']" type="hidden" value="' . $r['sundry_amount'] . '">
						<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="' . $r['sundry_amount'] . '" readonly placeholder="Amount">
						</div>
						<div class="col-md-3">
						<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
						type="button" value="' . $cnt . '" onclick="removeSundry(\'\',\'' . $r['sundry_amount'] . '\',this.value,\'' . $r['sundry_id'] . '\')"><i class="fa fa-times"></i></button>
						</div>
						</div>';
			} else {
				$str .= '<div class="form-group">
						<label class="col-md-5 control-label">' . $r['l_name'] . '</label>
						<div class="col-md-4">
						<input id="sundry_name" name="bill_sundry_addon[' . $r['l_id'] . ']" type="hidden" value="' . $r['sundry_amount'] . '">
						<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="' . $r['sundry_amount'] . '" readonly placeholder="Amount">
						<input class="addontax" name="bill_sundry_addon_tax[' . $r['l_id'] . ']" type="hidden" value="' . $r['sundry_gst_amount'] . '-' . $r['sundry_gst_per'] . '-' . $r['sundry_amount'] . '" >
						</div>
						<div class="col-md-3">
						<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
						type="button" value="' . $cnt . '" onclick="removeSundry(\'\',\'' . $r['sundry_amount'] . '\',this.value,\'' . $r['sundry_id'] . '\')"><i class="fa fa-times"></i></button>
						</div>
						</div>';
			}
			$cnt++;
			//$str.=$r['sundry_amount'];
		}
	} else {
		foreach ($POST['sales_order'] as $sale_id) {
			$q = $dbcon->query("select b.*,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id left join tbl_ledger as le on le.l_id=b.sundry_ledger_id where b.sundry_voucher_id='$sale_id' and b.sundry_voucher_table='tbl_sales_order' and b.isdelete='0' and le.default_sundry='0' ");
			$resp = brp_mysqli_fetch_all($q);
			$str = "";
			$cnt = 1;
			foreach ($resp as $r) {
				if ($r['sundry_type'] == 1) {
					$per_amount_show = '';
				} else if ($r['sundry_type'] == 2) {
					$per_amount_show = '(' . $r['sundry_default_value'] . '%' . ')';
				}
				if (empty($r['sundry_gst_per'])) {
					$str .= '<div class="form-group">
							<label class="col-md-5 control-label">' . $r['l_name'] . '</label>
							<div class="col-md-4">
							<input id="sundry_name" name="bill_sundry_addon[' . $r['l_id'] . ']" type="hidden" value="' . $r['sundry_amount'] . '">
							<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="' . $r['sundry_amount'] . '" readonly placeholder="Amount">
							</div>
							<div class="col-md-3">
							<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
							type="button" value="' . $cnt . '" onclick="removeSundry(\'\',\'' . $r['sundry_amount'] . '\',this.value,\'' . $r['sundry_id'] . '\')"><i class="fa fa-times"></i></button>
							</div>
							</div>';
				} else {
					$str .= '<div class="form-group">
							<label class="col-md-5 control-label">' . $r['l_name'] . '</label>
							<div class="col-md-4">
							<input id="sundry_name" name="bill_sundry_addon[' . $r['l_id'] . ']" type="hidden" value="' . $r['sundry_amount'] . '">
							<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="' . $r['sundry_amount'] . '" readonly placeholder="Amount">
							<input class="addontax" name="bill_sundry_addon_tax[' . $r['l_id'] . ']" type="hidden" value="' . $r['sundry_gst_amount'] . '-' . $r['sundry_gst_per'] . '-' . $r['sundry_amount'] . '" >
							</div>
							<div class="col-md-3">
							<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
							type="button" value="' . $cnt . '" onclick="removeSundry(\'\',\'' . $r['sundry_amount'] . '\',this.value,\'' . $r['sundry_id'] . '\')"><i class="fa fa-times"></i></button>
							</div>
							</div>';
				}
				$cnt++;
				//$str.=$r['sundry_amount'];
			}
		}
	}
	echo $str;
} else if (strtolower($POST['mode']) == "update_user_log_history") {
	$appData = array();
	$i = 1;

	$where = '';
	if ($POST['sales_order_id']) {
		$where = ' and log.ref_id=' . $POST['sales_order_id'];
	}
	// if($branch_id){
	//     $where .= check_branch('opportun',$branch_id);
	// }
	$aColumns = array('log.user_log_id', 'aus.user_name as updateduser', 'lus.user_name as loginuser', 'log.updated_user_id', 'log.remark', 'log.cdate');
	$sIndexColumn = "log.user_log_id";
	$isWhere = array("log.ref_name='tbl_invoice' and log.ref_id and log.company_id in (0,$_SESSION[company_id])" . $where);
	$sTable = "tbl_update_user_log as log";
	$isJOIN = array('left join users as aus on aus.user_id=log.updated_user_id', 'left join users as lus on lus.user_id=log.user_id');
	$hOrder = "log.user_log_id desc";
	include('../../../include/pagging.php');
	$appData = array();
	$id = 1;
	foreach ($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['updateduser'];
		$row_data[] = $row['remark'];
		$row_data[] = $row['cdate'];
		$row_data[] = $row['loginuser'];

		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode($output);
} else if (strtolower($POST['mode']) == "user_update") {
	$info['updated_user_id']	= $POST['updated_user_id'];
	$info['previous_user_id']	= $POST['previous_user_id'];
	$info['remark']				= $POST['remark'];
	$info['ref_name']			= 'tbl_invoice';
	$info['ref_id']				= $POST['ref_id'];
	$info['cdate']				= date("Y-m-d H:i:s");
	$info['user_id']			= $_SESSION['user_id'];
	$info['branch_id']			= $_SESSION['branch_id'];
	$info['company_id']			= $_SESSION['company_id'];

	$inserid = add_record('tbl_update_user_log', $info, $dbcon, $row['branch_id']);

	$infoinv['order_user_id']	= $POST['updated_user_id'];
	$updatesid = update_record('tbl_invoice', $infoinv, "invoice_id=" . $POST['ref_id'], $dbcon);
} else if (strtolower($POST['mode']) == "load_typeswise_terms") {
	$quot_type  = $POST['quot_type'];
	$invoice_id = $POST['invoice_id'];
	$terms_type = $POST['terms_type'];
	$cust_id 	= $POST['cust_id'];
	$sales_order_id = $POST['sales_order_id'];

	$query_quot = "select terms_type from tbl_sales_order where sales_order_id=" . $sales_order_id;
	$result_quot = $dbcon->query($query_quot);
	$row_quot = brp_mysqli_fetch_array($result_quot);

	$str = '';
	$str .= '<table class="display table table-bordered table-striped">
        	<thead>
        	<tr>
        	<th width="5%" class="text-center">
        	<input type="checkbox" class="check_all_terms" style="height: 20px;width: 20px;" id="check_all_terms" name="check_all_terms" onClick="terms_check_all(this);">
        	</th>';
	if ($terms_type == 3 || $row_quot['terms_type'] == 3) {
		$str .= '<th width="25%" class="text-center">Print Name</th>
        		<th width="25%" class="text-center">Term Name</th>';
	} else {
		$str .= '<th width="25%" class="text-center">Term Name</th>';
	}

	$str .= '<th width="5%" class="text-center">Priority</th>
        	<th width="65%" class="text-center">Term And Condition</th>				  
        	</tr>
        	</thead>
        	<tbody>';
	//Get All Terms
	if ($terms_type == 3 || $row_quot['terms_type'] == 3) {
		$terms_qry = "select * from tbl_terms_condition where tc_status=0 and
					tc_category=1 and find_in_set(" . $quot_type . ",tc_for) group by print_name order by tc_priority";
	} else {
		$terms_qry = "select * from tbl_terms_condition where tc_status=0 and tc_category=1 and find_in_set(" . $quot_type . ",tc_for) order by tc_priority";
	}

	$terms_qry_rs = $dbcon->query($terms_qry);
	$t = 1;

	while ($terms_rel = mysqli_fetch_assoc($terms_qry_rs)) {
		$tc_priority = $terms_rel['tc_priority'];
		$tc_details = $terms_rel['tc_details'];
		if ($terms_type == '1') {
			if ($invoice_id) {
				$quot_term_qry = "select * from tbl_invoice_terms_trn where invoice_terms_trn_status=0 and invoice_id=" . $invoice_id . " and tc_id=" . $terms_rel['tc_id'] . "";
				$quot_term_rel = mysqli_fetch_assoc($dbcon->query($quot_term_qry));
				if ($quot_term_rel['tc_priority']) {
					$tc_priority = $quot_term_rel['tc_priority'];
				}
				if ($quot_term_rel['tc_details']) {
					$tc_details = $quot_term_rel['tc_details'];
				}
			} else {
				$cust_term_qry = "select * from tbl_customer_term_trn where customer_terms_trn_status=0 and tc_for=" . $quot_type . " and ledger_id=" . $cust_id . " and tc_id=" . $terms_rel['tc_id'];
				$cust_term_rel = brp_mysqli_fetch_assoc($dbcon->query($cust_term_qry));
				if ($cust_term_rel['tc_priority']) {
					$tc_priority = $cust_term_rel['tc_priority'];
				}
				if ($cust_term_rel['tc_details']) {
					$tc_details = $cust_term_rel['tc_details'];
				}
				$quot_term_rel['tc_id'] = $cust_term_rel['tc_id'];
			}
		} else if ($terms_type == '2') {
			if ($invoice_id) {
				$quot_term_qry = "select * from tbl_invoice_terms_trn where invoice_terms_trn_status=0 and invoice_id=" . $invoice_id . " and tc_id=" . $terms_rel['tc_id'] . "";
				$quot_term_rel = mysqli_fetch_assoc($dbcon->query($quot_term_qry));
				if ($quot_term_rel['tc_priority']) {
					$tc_priority = $quot_term_rel['tc_priority'];
				}
				if ($quot_term_rel['tc_details']) {
					$tc_details = $quot_term_rel['tc_details'];
				}
			} else {
				$quot_term_qry = "select * from tbl_salesorder_terms_trn where quotation_terms_trn_status=0 and sales_order_id=" . $sales_order_id . " and tc_id=" . $terms_rel['tc_id'] . "";
				$quot_term_rel = mysqli_fetch_assoc($dbcon->query($quot_term_qry));
				if ($quot_term_rel['tc_priority']) {
					$tc_priority = $quot_term_rel['tc_priority'];
				}
				if ($quot_term_rel['tc_details']) {
					$tc_details = $quot_term_rel['tc_details'];
				}
			}
		} else if ($terms_type == '3') {
			$quot_term_qry = "select * from tbl_invoice_terms_trn where invoice_terms_trn_status=0 and invoice_id=" . $invoice_id . " and tc_id=" . $terms_rel['tc_id'] . "";
			$quot_term_rel = mysqli_fetch_assoc($dbcon->query($quot_term_qry));
			if ($quot_term_rel['tc_priority']) {
				$tc_priority = $quot_term_rel['tc_priority'];
			}
			if ($quot_term_rel['tc_details']) {
				$tc_details = $quot_term_rel['tc_details'];
			}
			if ($quot_term_rel['ref_tc_id']) {
				$so_ref_tc_id = $quot_term_rel['ref_tc_id'];
			}
		} else {
			if ($invoice_id) {
				$quot_term_qry = "select * from tbl_invoice_terms_trn where invoice_terms_trn_status=0 and invoice_id=" . $invoice_id . " and tc_id=" . $terms_rel['tc_id'] . "";
				$quot_term_rel = mysqli_fetch_assoc($dbcon->query($quot_term_qry));
				if ($quot_term_rel['tc_priority']) {
					$tc_priority = $quot_term_rel['tc_priority'];
				}
				if ($quot_term_rel['tc_details']) {
					$tc_details = $quot_term_rel['tc_details'];
				}
			}
		}

		$str .= '<tr>
	    				<td width="5%" class="text-center">
	    				<input type="checkbox" class="terms_checkbox" style="height: 20px;width: 20px;" id="disp_term_flag' . $t . '" name="disp_term_flag[]" value="' . $terms_rel['tc_id'] . '" ' . (($terms_rel['tc_id'] == $quot_term_rel['tc_id']) ? 'checked' : '') . '>
	    				<input type="hidden" id="tc_id' . $t . '" name="tc_id[]" value="' . $terms_rel['tc_id'] . '">
	    				</td>';
		if ($terms_type == 3 || $row_quot['terms_type'] == 3) {
			$str .= '<td>' . $terms_rel['print_name'] . '</td>
							<td>
								<select id="ref_tc_id' . $t . '" name="ref_tc_id[]" class="form-control" onchange="get_terms_detail(' . $t . ')">
									' . get_terms_printname_wise($dbcon, $so_ref_tc_id, $terms_rel['print_name'], $quot_type) . '
								</select>
							</td>';
		} else {
			$str .= '<td>' . $terms_rel['tc_name'] . '</td>';
		}
		$str .= '<td>
	    					<input type="number" class="form-control" min="0" id="tc_priority' . $t . '" name="tc_priority[]" value="' . $tc_priority . '">
	    				</td>';
		if ($terms_rel['tc_allow']) {
			$str .= '<td>
		    					<textarea class="form-control" id="tc_details' . $t . '" name="tc_details[]" rows="4">' . $tc_details . '</textarea>
		    				</td>';
		} else {
			$str .= '<td>
		    					<textarea class="form-control" id="tc_details' . $t . '" name="tc_details[]" rows="4" readonly>' . $tc_details . '</textarea>
		    				</td>';
		}
		$str .= '</tr>';

		$t++;
	}

	$str .= '</tbody> 
	    	</table>';
	/*echo $str;*/
	$resp['resp_html'] = $str;
	echo json_encode($resp);
} else if (strtolower($POST['mode']) == "get_so_invoice_data") {
	$where = '';
	$html = '';
	if ($company_config['crm_sales_order_user_selecation'] == 1) {
		$where = " and user_id = " . $POST['user_id'];
	}

	if ($POST['invoice_id']) {
		$query_inv = "select term_salesorder_id from tbl_invoice where invoice_id=" . $POST['invoice_id'];
		$inv_res = $dbcon->query($query_inv);
		$res = brp_mysqli_fetch_array($inv_res);
	}

	$query = "select * from tbl_sales_order where sales_order_status=0 and approve_status not in (0,1) and cust_id=" . $POST['cust_id'] . " and invoice_status=0 and company_id=" . $_SESSION['company_id'] . " and branch_id=" . $POST['branch_id'] . " " . $where;
	$result = $dbcon->query($query);
	$html .= '<option value=""> Choose Sales Order</option>';
	while ($row = brp_mysqli_fetch_array($result)) {
		$selected  = '';
		if ($row['sales_order_id'] == $res['term_salesorder_id']) {
			$selected = 'selected="selected"';
		}

		$html .= '<option ' . $selected . ' value="' . $row['sales_order_id'] . '">' . $row['sales_order_no'] . '</option>';
	}

	$resp['resp_html'] = $html;
	$resp['term_salesorder_id'] = $res['term_salesorder_id'];
	echo json_encode($resp);
} else if (brp_strtolower($POST['mode']) == 'load_parent_cat') {
	$html = '';
	$query = "select * from tbl_category where cat_status=0 and cat_pid=" . $POST['parent_id'];
	$result = $dbcon->query($query);
	$html .= '<option value="">Choose Category</option>';
	while ($row = brp_mysqli_fetch_array($result)) {
		$html .= '<option value="' . $row['cat_id'] . '">' . $row['cat_name'] . '</option>';
	}
	echo $html;
} else if (brp_strtolower($POST['mode'] == 'get_terms_detail')) {
	$query = 'select * from tbl_terms_condition where tc_id=' . $POST['tc_id'];
	$result  = $dbcon->query($query);
	$row = brp_mysqli_fetch_array($result);

	if (empty($row['tc_details'])) {
		$row['tc_details'] = '';
	}
	echo json_encode($row);
}

function get_product_tax($dbcon, $product_amount, $formulaid)
{
	$qry = "SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=" . $formulaid . " order by tax_value desc";
	$row = $dbcon->query($qry);
	$rate_total = $total = $product_amount;
	$i = 1;
	while ($tax = mysqli_fetch_assoc($row)) {
		$info['tax_name' . $i] = $tax['tax_name'];
		$info['tax_amount' . $i] = $tax_amount = ($total) * $tax['tax_value'] / 100;
		$rate_total += $tax_amount;
		$tax_total_amount += $info['tax_amount' . $i];
		$i++;
	}
	for ($j = $i; $j <= 3; $j++) {
		$info['tax_name' . $j] = '';
		$info['tax_amount' . $j] = '';
	}
	$info['total'] = $rate_total;
	//$info['tax_total_amount']=$tax_total_amount;
	return $info;
}
function upd_qt_done_sts($dbcon, $quotation_id, $invoice_id)
{
	$qt_trn_qry = "select sum(product_qty) as qt_qty from tbl_quotation_trn where quot_trn_status=0 and quotation_id=" . $quotation_id;
	$qt_trn_rel = mysqli_fetch_assoc($dbcon->query($qt_trn_qry));
	//Invoice Qty
	$inv_trn_qry = "select sum(product_qty) as inv_qty from tbl_invoicetrn as trn
			inner join tbl_invoice as inv on inv.invoice_id=trn.invoice_id
			where trn.trancation_status=0 and inv.invoice_status=0 and inv.quotation_id=" . $quotation_id;
	$inv_trn_rel = mysqli_fetch_assoc($dbcon->query($inv_trn_qry));

	if (floatval($inv_trn_rel['inv_qty']) >= $qt_trn_rel['qt_qty']) {
		$upd_qt = "update tbl_quotation set inv_done_status=1 where quotation_id=" . $quotation_id;
		$upd_qt_rs = $dbcon->query($upd_qt);
	}

	//Update Quotation trn rows
	$upd_qt_trn_qry = "update tbl_quotation_trn set inv_done_status=1 where quot_trn_status=0 and find_in_set(quot_trn_id,(select group_concat(ref_quot_trn_id) from tbl_invoicetrn where trancation_status=0 and invoice_id=" . $invoice_id . "))";
	$upd_qt_trn_qry_rs = $dbcon->query($upd_qt_trn_qry);
}
function upd_spare_inv_sts($dbcon, $complaint_id, $invoice_id)
{
	//Update Quotation trn rows
	$upd_qt_trn_qry = "update tbl_complain_spare_part set s_inv_status=1 where s_inv_status=0 and find_in_set(s_id,(select group_concat(ref_s_id) from tbl_invoicetrn where trancation_status=0 and invoice_id=" . $invoice_id . "))";
	$upd_qt_trn_qry_rs = $dbcon->query($upd_qt_trn_qry);

	$upd_comp_trn_qry = "update tbl_complaint_trn set inv_done_status=1 where complaint_id=" . $complaint_id;
	$upd_comp_trn_qry_rs = $dbcon->query($upd_comp_trn_qry);
}
function upd_inv_srl_no($dbcon, $invoice_id)
{
	$upd_qry = "update `tbl_inv_srl_trn` set invoice_id=$invoice_id where find_in_set(trancation_id,(select group_concat(trancation_id) from tbl_invoicetrn where trancation_status=0 and invoice_id=$invoice_id));";
	$upd_qry_rs = $dbcon->query($upd_qry);
}
function copy_srl_no($dbcon, $invoice_id)
{
	//Invoice DATA
	$inv_qry = "select cust_id,invoice_no,invoice_date from tbl_invoice where invoice_id=" . $invoice_id;
	$inv_rel = mysqli_fetch_assoc($dbcon->query($inv_qry));

	$srl_qry = "select srl.pro_srl_no,(select product_id from tbl_invoicetrn where trancation_id=srl.trancation_id) as pro_id from tbl_inv_srl_trn as srl where srl.inv_srl_trn_status=0 and srl.invoice_id=" . $invoice_id;
	$srl_qry_rs = $dbcon->query($srl_qry);
	while ($srl_rel = mysqli_fetch_assoc($srl_qry_rs)) {
		$info1['cust_id']				= $inv_rel['cust_id'];
		$info1['sold_inv_foc_date']		= date("Y-m-d", strtotime($inv_rel['invoice_date']));
		$info1['product_id']			= $srl_rel['pro_id'];
		$info1['sold_pro_srl_no']		= $srl_rel['pro_srl_no'];
		$info1['cdate']					= date("Y-m-d H:i:s");
		$info1['user_id']				= $_SESSION['user_id'];
		$info1['company_id']			= $_SESSION['company_id'];

		$table = 'tbl_cust_sold_pro';
		$tableid = 'cust_sold_pro_id';
		$inserid = add_record($table, $info1, $dbcon);
	}
}
function general_book_tcs_entry($dbcon, $invoice_id, $branch_id)
{

	$qry = "select * from tbl_invoice as cert where invoice_status=0 and company_id = " . $_SESSION['company_id'] . " and invoice_id=" . $invoice_id;
	$result = $dbcon->query($qry);
	$invoice = mysqli_fetch_assoc($result);

	$tax_qry = "SELECT utax.*,sum(tax_amount) as tamount FROM `tbl_used_tax` as utax 
			WHERE tax_used_status=0 and used_transaction_id in (" . $invoice_id . ") and table_name='tbl_invoice' 
				GROUP BY ledger_id ORDER BY tax_used_id desc";
	$row = $dbcon->query($tax_qry);
	$tax = mysqli_fetch_assoc($row);


	$ledger_id = $dbcon->query("SELECT l_id FROM `tbl_ledger` where l_name like 'TCS' and l_group = '" . DUTIES_AND_TAXES . "' and company_id=" . $_SESSION['company_id'])
		->fetch_object()->l_id;

	$general_book_id = $dbcon->query("select general_book_id from tbl_general_book as cert where genral_book_status=0 and ledger_id=" . $tax['ledger_id'] . " and table_id=" . $invoice_id . " and table_name='tbl_invoice'")
		->fetch_object()->general_book_id;

	$info['table_name']     = "tbl_invoice";
	$info['table_id']	= $invoice_id;
	$info['ref_date']	= date("Y-m-d", strtotime($invoice['invoice_date']));
	$info['entry_type']     = 1;
	$info['ledger_id']	= $ledger_id;
	$info['amount']         = $invoice['tcs_total'];
	$info['module_name']    = MODULE_INVOICE;
	$info['module_id']         = $invoice['invoice_id'];
	$info['user_id']        = $_SESSION['user_id'];
	$info['cdate']		= date("Y-m-d H:i:s");
	$info['company_id']     = $_SESSION['company_id'];

	if ($general_book_id) {
		$updateid = update_record("tbl_general_book", $info, "general_book_id=" . $general_book_id, $dbcon, $branch_id);
	} else {
		$inserid = add_record("tbl_general_book", $info, $dbcon, $branch_id);
	}
}

function general_book_tax_entry($dbcon, $invoice_id, $branch_id)
{
	$qry1 = "select group_concat(trancation_id) as tid from tbl_invoicetrn as cert where trancation_status=0 and invoice_id=" . $invoice_id;
	$ro = $dbcon->query($qry1);
	$re = mysqli_fetch_assoc($ro);

	$qry122 = "select * from tbl_invoice as cert where invoice_status=0 and company_id = " . $_SESSION['company_id'] . " and invoice_id=" . $invoice_id;
	$ro12 = $dbcon->query($qry122);
	$rea = mysqli_fetch_assoc($ro12);

	$qry = "SELECT utax.*,sum(tax_amount) as tamount FROM `tbl_used_tax` as utax WHERE tax_used_status=0 and used_transaction_id in (" . $re["tid"] . ") and table_name='tbl_invoicetrn' group by ledger_id order by tax_used_id desc";
	$row = $dbcon->query($qry);
	while ($tax = mysqli_fetch_assoc($row)) {
		$qry12 = "select general_book_id from tbl_general_book as cert where genral_book_status=0 and ledger_id=" . $tax['ledger_id'] . " and table_id=" . $invoice_id . " and table_name='tbl_invoice'";
		$ros = $dbcon->query($qry12);
		$re2 = mysqli_fetch_assoc($ros);


		$info1['table_name']            = "tbl_invoice";
		$info1['table_id']		= $invoice_id;
		$info1['ref_date']		= date("Y-m-d", strtotime($rea['invoice_date']));
		$info1['entry_type']            = 1;
		$info1['ledger_id']		= $tax['ledger_id'];
		$info1['amount']		= $tax['tamount'];
		$info1['module_name']	= MODULE_INVOICE;
		$info1['module_id']		= $rea['invoice_id'];
		$info1['user_id']		= $_SESSION['user_id'];
		$info1['cdate']			= date("Y-m-d H:i:s");
		$info1['company_id']    = $_SESSION['company_id'];

		if (!empty($re2['general_book_id'])) {
			$updateid = update_record("tbl_general_book", $info1, "general_book_id=" . $re2['general_book_id'], $dbcon, $branch_id);
		} else {
			$inserid = add_record("tbl_general_book", $info1, $dbcon, $branch_id);
		}
		//var_dump($re2['general_book_id']);
	}
}
function general_book_sercices_entry($dbcon, $invoice_id, $branch_id)
{
	$qry1 = "select group_concat(trancation_id) as tid from tbl_invoicetrn as cert where trancation_status=0 and invoice_id=" . $invoice_id;
	$ro = $dbcon->query($qry1);
	$re = mysqli_fetch_assoc($ro);

	$qry122 = "select * from tbl_invoice as cert where invoice_status=0 and invoice_id=" . $invoice_id;
	$ro12 = $dbcon->query($qry122);
	$rea = mysqli_fetch_assoc($ro12);

	$qry = "SELECT itrn.*,promst.ledger_id FROM `tbl_invoicetrn` as itrn 
					left join product_mst as promst on promst.product_id=itrn.product_id
					WHERE itrn.trancation_status=0 and promst.product_type=8 and itrn.invoice_id=" . $invoice_id . " order by itrn.trancation_id desc";
	$row = $dbcon->query($qry);
	while ($tax = mysqli_fetch_assoc($row)) {
		$qry12 = "select general_book_id from tbl_general_book as cert where genral_book_status=0 and ledger_id=" . $tax['ledger_id'] . " and table_id=" . $tax['trancation_id'] . " and table_name='tbl_invoicetrn'";
		$ros = $dbcon->query($qry12);
		$re2 = mysqli_fetch_assoc($ros);


		$info1['table_name']	= "tbl_invoicetrn";
		$info1['table_id']		= $tax['trancation_id'];
		$info1['ref_date']		= date("Y-m-d", strtotime($rea['invoice_date']));
		$info1['entry_type']	= 1;
		$info1['ledger_id']		= $tax['ledger_id'];
		$info1['amount']		= $tax['product_amount'];
		$info1['module_name']	= MODULE_INVOICE;
		$info1['module_id']		= $rea['invoice_id'];
		$info1['user_id']		= $_SESSION['user_id'];
		$info1['cdate']			= date("Y-m-d H:i:s");
		$info1['company_id']	= $_SESSION['company_id'];

		if (!empty($re2['general_book_id'])) {
			$updateid = update_record("tbl_general_book", $info1, "general_book_id=" . $re2['general_book_id'], $dbcon, $branch_id);
		} else {
			$inserid = add_record("tbl_general_book", $info1, $dbcon, $branch_id);
		}
		//var_dump($re2['general_book_id']);
	}
}
function entry_batch_stock_temp_table($dbcon, $inserid)
{

	$qry12 = "select pro.batch_wise_stock_manage,trancation_id,pro.product_id,unit_id,sales_ordertrn_id,product_qty from tbl_invoicetrn as cert 
								left join product_mst as pro on pro.product_id=cert.product_id
							where trancation_id=" . $inserid;
	$ros = $dbcon->query($qry12);
	$re2 = mysqli_fetch_assoc($ros);
	$inv_qty = $re2['product_qty'];
	if ($re2['batch_wise_stock_manage'] == 1 && $re2['batch_wise_stock_manage'] != 0) {

		$qry = "select reserve_id,base_stock,convert_stock,base_unit,convert_unit,stock_id from tbl_reserve_stock as cert 
									where stock_status=0 and stock_flage=1 and sales_order_trn_id=" . $re2['sales_ordertrn_id'];
		$result = $dbcon->query($qry);
		while ($res = mysqli_fetch_assoc($result)) {

			$qry1 = "select IFNULL(sum(base_stock),0) as total_used_base,IFNULL(sum(convert_stock),0) as total_used_convert from tbl_reserve_stock as cert 
									where stock_status=0 and stock_flage=2 and perent_id=" . $res['reserve_id'];
			$result1 = $dbcon->query($qry1);
			$res1 = mysqli_fetch_assoc($result1);

			$pending_base = $res['base_stock'] - $res1['total_used_base'];
			$pending_conv = $res['convert_stock'] - $res1['total_used_convert'];

			if ($res['base_unit'] == $re2['unit_id']) {
				$stock_qty = $pending_base;
			} else {
				$stock_qty = $pending_conv;
			}
			if ($stock_qty > 0) {
				if ($inv_qty > 0) {
					if ($inv_qty >= $stock_qty) {
						$used_qty = $stock_qty;
					} else {
						$used_qty = $inv_qty;
					}

					$info1['invoice_trn_id']	= $re2['trancation_id'];
					$info1['product_id']		= $re2['product_id'];
					$info1['stock_id']			= $res['stock_id'];
					$info1['reserve_id']		= $res['reserve_id'];
					$info1['qty']				= $used_qty;
					$info1['unitid']			= $re2['unit_id'];
					$info1['status']			= 1;
					$info1['user_id']			= $_SESSION['user_id'];
					$info1['cdate']				= date("Y-m-d H:i:s");
					$info1['company_id']		= $_SESSION['company_id'];

					$ins_id = add_record("tbl_batch_stock_tmp", $info1, $dbcon);
					if ($ins_id) {
						$inv_qty = $inv_qty - $used_qty;
					}
				}
			}
		}
	}
}
function deduct_stock_in_invoice($dbcon, $invoice_trn_id)
{
	remove_stock_inv_trn_wise($dbcon, $invoice_trn_id);
	$qry12 = "select trancation_id,product_id,unit_id,sales_ordertrn_id,product_qty from tbl_invoicetrn as cert 
							where trancation_id=" . $invoice_trn_id;
	$ros = $dbcon->query($qry12);
	$re2 = mysqli_fetch_assoc($ros);
	$inv_qty = $re2['product_qty'];

	$qry = "select batch_stk_id,invoice_trn_id,product_id,stock_id,reserve_id,qty,unitid from tbl_batch_stock_tmp as cert 
									where status=1 and invoice_trn_id=" . $re2['trancation_id'];
	$result = $dbcon->query($qry);
	while ($res = mysqli_fetch_assoc($result)) {
		$batch_d_qty = $res['qty'];

		$qry1 = "select reserve_id,base_unit,base_stock,approve_base_stock,convert_unit,convert_stock,approve_convert_stock,product_id,godown_id,sales_order_trn_id,stock_id,branch_id,customer_id from tbl_reserve_stock as cert 
									where stock_flage=1 and stock_status=0 and sales_order_trn_id=" . $re2['sales_ordertrn_id'] . " and stock_id=" . $res['stock_id'];
		$result1 = $dbcon->query($qry1);
		while ($res1 = mysqli_fetch_assoc($result1)) {

			$qry2 = "select IFNULL(sum(base_stock),0) as total_used_base,IFNULL(sum(convert_stock),0) as total_used_convert from tbl_reserve_stock as cert 
									where stock_status=0 and stock_flage=2 and perent_id=" . $res1['reserve_id'];
			$result2 = $dbcon->query($qry2);
			$res2 = mysqli_fetch_assoc($result2);

			$pending_base = $res1['base_stock'] - $res2['total_used_base'];
			$pending_conv = $res1['convert_stock'] - $res2['total_used_convert'];
			if ($pending_base > 0) {
				if ($batch_d_qty > 0) {
					if ($batch_d_qty >= $pending_base) {
						$r_used = $pending_base;
					} else {
						$r_used = $batch_d_qty;
					}
					$inv_qty = $inv_qty - $r_used;
					$batch_d_qty = $batch_d_qty - $r_used;
					$type = "conv_unit";
					$r_used_conv = convert_stock_new($dbcon, $r_used, $res1['product_id'], $type);

					$info1['reserve_date']			= date("Y-m-d");
					$info1['product_id']			= $res1['product_id'];
					$info1['godown_id']				= $res1['godown_id'];
					$info1['base_unit']				= $res1['base_unit'];
					$info1['base_stock']			= $r_used;
					$info1['convert_unit']			= $res1['convert_unit'];
					$info1['convert_stock']			= $r_used_conv;
					$info1['stock_flage']			= 2;
					$info1['ref_name']				= "invoice_trn";
					$info1['ref_id']				= $re2['trancation_id'];
					$info1['perent_id']				= $res1['reserve_id'];
					$info1['cdate']					= date("Y-m-d H:i:s");
					$info1['user_id']				= $_SESSION['user_id'];
					$info1['company_id']			= $_SESSION['company_id'];
					$info1['sales_order_trn_id']	= $res1['sales_order_trn_id'];
					$info1['stock_id']				= $res1['stock_id'];
					$info1['branch_id']				= $res1['branch_id'];
					$info1['customer_id']			= $res1['customer_id'];

					$ins_id = add_record("tbl_reserve_stock", $info1, $dbcon);

					$qry3 = "select stock_id,product_id,base_unit,convert_unit,base_rate,conv_rate,godown_id,branch_id,batch_no,customer_id,batch_id from tbl_stock_trn as cert 
											where stock_id=" . $res1['stock_id'];
					$result3 = $dbcon->query($qry3);
					$res3 = mysqli_fetch_assoc($result3);

					$info2['stock_date']		= date("Y-m-d");
					$info2['product_id']		= $res3['product_id'];
					$info2['base_unit']			= $res3['base_unit'];
					$info2['base_stock']		= $info1['base_stock'];
					$info2['convert_unit']		= $res3['convert_unit'];
					$info2['convert_stock']		= $info1['convert_stock'];
					$info2['base_rate']			= $res3['base_rate'];
					$info2['conv_rate']			= $res3['conv_rate'];
					$info2['stock_flage']		= 2;
					$info2['godown_id']			= $res3['godown_id'];
					$info2['ref_name']			= "invoice_trn";
					$info2['ref_id']			= $re2['trancation_id'];
					$info2['stock_status']		= 0;
					$info2['cdate']				= date("Y-m-d H:i:s");
					$info2['user_id']			= $_SESSION['user_id'];
					$info2['company_id']		= $_SESSION['company_id'];
					$info2['branch_id']			= $res3['branch_id'];
					$info2['perent_id']			= $res3['stock_id'];
					$info2['reserve_id']		= $ins_id;
					$info2['batch_no']			= $res3['batch_no'];
					$info2['customer_id']		= $res3['customer_id'];
					$info2['batch_id']			= $res3['batch_id'];

					$ins_id1 = add_record("tbl_stock_trn", $info2, $dbcon);
				}
			}
		} //end batch temp reserve stock loop

		//batch temp qty add but reserve id not mention in batch temp start

		//batch temp qty add but reserve id not mention in batch temp end

		//batch temp qty add but not reserve qty deduct start
		$qry4 = "select stock_id,product_id,base_unit,convert_unit,base_rate,conv_rate,godown_id,branch_id,batch_no,customer_id,batch_id,used_convert_stock,used_base_stock from tbl_stock_trn as cert 
											where product_id=" . $res['product_id'] . " and stock_id=" . $res['stock_id'];
		$result4 = $dbcon->query($qry4);
		while ($res4 = mysqli_fetch_assoc($result4)) {
			$stock_qty = 0;
			$qry5 = "select reserve_id,base_unit,base_stock,approve_base_stock,convert_unit,convert_stock,approve_convert_stock,product_id,godown_id,sales_order_trn_id,stock_id,branch_id,customer_id from tbl_reserve_stock as cert 
												where stock_flage=1 and stock_status=0 and stock_id=" . $res4['stock_id'];
			$result5 = $dbcon->query($qry5);
			while ($res5 = mysqli_fetch_assoc($result5)) {

				$qry6 = "select IFNULL(sum(base_stock),0) as total_used_base,IFNULL(sum(convert_stock),0) as total_used_convert from tbl_reserve_stock as cert 
													where stock_status=0 and stock_flage=2 and perent_id=" . $res5['reserve_id'];
				$result6 = $dbcon->query($qry6);
				$res6 = mysqli_fetch_assoc($result6);

				$pending_base_s = $res5['base_stock'] - $res6['total_used_base'];
				$stock_qty = $stock_qty + $pending_base_s;
				//$pending_conv=$res1['convert_stock']-$res2['total_used_convert'];
			}
			if ($stock_qty > 0) {
				if ($batch_d_qty > 0) {
					if ($stock_qty >= $batch_d_qty) {
						$s_used = $batch_d_qty;
					} else {
						$s_used = $stock_qty;
					}
					$inv_qty = $inv_qty - $s_used;
					$batch_d_qty = $batch_d_qty - $s_used;
					$type = "conv_unit";
					$s_used_conv = convert_stock_new($dbcon, $s_used, $res4['product_id'], $type);

					$info3['stock_date']		= date("Y-m-d");
					$info3['product_id']		= $res4['product_id'];
					$info3['base_unit']			= $res4['base_unit'];
					$info3['base_stock']		= $s_used;
					$info3['convert_unit']		= $res4['convert_unit'];
					$info3['convert_stock']		= $s_used_conv;
					$info3['base_rate']			= $res4['base_rate'];
					$info3['conv_rate']			= $res4['conv_rate'];
					$info3['stock_flage']		= 2;
					$info3['godown_id']			= $res4['godown_id'];
					$info3['ref_name']			= "invoice_trn";
					$info3['ref_id']			= $re2['trancation_id'];
					$info3['stock_status']		= 0;
					$info3['cdate']				= date("Y-m-d H:i:s");
					$info3['user_id']			= $_SESSION['user_id'];
					$info3['company_id']		= $_SESSION['company_id'];
					$info3['branch_id']			= $res4['branch_id'];
					$info3['perent_id']			= $res4['stock_id'];
					//$info3['reserve_id']		= $ins_id;
					$info3['batch_no']			= $res4['batch_no'];
					$info3['customer_id']		= $res4['customer_id'];
					$info3['batch_id']			= $res4['batch_id'];

					$ins_id2 = add_record("tbl_stock_trn", $info3, $dbcon);

					$inv_trn['used_base_stock'] = $res4['used_base_stock'] + $info3['base_stock'];
					$inv_trn['used_convert_stock'] = $res4['used_convert_stock'] + $info3['convert_stock'];
					$updatetrnid = update_record('tbl_stock_trn', $inv_trn, " stock_id=" . $res4['stock_id'], $dbcon, '');
				}
			}
		}


		//batch temp qty add but not reserve qty deduct end 

	} //end batch temp

	if ($inv_qty > 0) {
		//reserve wise deduct stock entry start
		if ($re2['sales_ordertrn_id'] > 0) {
			$qry7 = "select reserve_id,base_unit,base_stock,approve_base_stock,convert_unit,convert_stock,approve_convert_stock,product_id,godown_id,sales_order_trn_id,stock_id,branch_id,customer_id from tbl_reserve_stock as cert 
									where stock_flage=1 and stock_status=0 and sales_order_trn_id=" . $re2['sales_ordertrn_id'];
			$result7 = $dbcon->query($qry7);
			while ($res7 = mysqli_fetch_assoc($result7)) {

				$qry8 = "select IFNULL(sum(base_stock),0) as total_used_base,IFNULL(sum(convert_stock),0) as total_used_convert from tbl_reserve_stock as cert 
									where stock_status=0 and stock_flage=2 and perent_id=" . $res7['reserve_id'];
				$result8 = $dbcon->query($qry8);
				$res8 = mysqli_fetch_assoc($result8);

				$pending_base_rd = $res7['base_stock'] - $res8['total_used_base'];
				$pending_conv_rd = $res7['convert_stock'] - $res8['total_used_convert'];

				if ($inv_qty > 0) {
					if ($pending_base_rd > 0) {
						if ($inv_qty >= $pending_base_rd) {
							$qty_rd = $pending_base_rd;
						} else {
							$qty_rd = $inv_qty;
						}
						$inv_qty = $inv_qty - $qty_rd;
						$type = "conv_unit";
						$qty_rd_conv = convert_stock_new($dbcon, $qty_rd, $res7['product_id'], $type);

						$info4['reserve_date']			= date("Y-m-d");
						$info4['product_id']			= $res7['product_id'];
						$info4['godown_id']				= $res7['godown_id'];
						$info4['base_unit']				= $res7['base_unit'];
						$info4['base_stock']			= $qty_rd;
						$info4['convert_unit']			= $res7['convert_unit'];
						$info4['convert_stock']			= $qty_rd_conv;
						$info4['stock_flage']			= 2;
						$info4['ref_name']				= "invoice_trn";
						$info4['ref_id']				= $re2['trancation_id'];
						$info4['perent_id']				= $res7['reserve_id'];
						$info4['cdate']					= date("Y-m-d H:i:s");
						$info4['user_id']				= $_SESSION['user_id'];
						$info4['company_id']			= $_SESSION['company_id'];
						$info4['sales_order_trn_id']	= $res7['sales_order_trn_id'];
						$info4['stock_id']				= $res7['stock_id'];
						$info4['branch_id']				= $res7['branch_id'];
						$info4['customer_id']			= $res7['customer_id'];

						$ins_id3 = add_record("tbl_reserve_stock", $info4, $dbcon);

						$qry9 = "select stock_id,product_id,base_unit,convert_unit,base_rate,conv_rate,godown_id,branch_id,batch_no,customer_id,batch_id from tbl_stock_trn as cert 
											where stock_id=" . $res7['stock_id'];
						$result9 = $dbcon->query($qry9);
						$res9 = mysqli_fetch_assoc($result9);

						$info5['stock_date']		= date("Y-m-d");
						$info5['product_id']		= $res9['product_id'];
						$info5['base_unit']			= $res9['base_unit'];
						$info5['base_stock']		= $info4['base_stock'];
						$info5['convert_unit']		= $res9['convert_unit'];
						$info5['convert_stock']		= $info4['convert_stock'];
						$info5['base_rate']			= $res9['base_rate'];
						$info5['conv_rate']			= $res9['conv_rate'];
						$info5['stock_flage']		= 2;
						$info5['godown_id']			= $res9['godown_id'];
						$info5['ref_name']			= "invoice_trn";
						$info5['ref_id']			= $re2['trancation_id'];
						$info5['stock_status']		= 0;
						$info5['cdate']				= date("Y-m-d H:i:s");
						$info5['user_id']			= $_SESSION['user_id'];
						$info5['company_id']		= $_SESSION['company_id'];
						$info5['branch_id']			= $res9['branch_id'];
						$info5['perent_id']			= $res9['stock_id'];
						$info5['reserve_id']		= $ins_id3;
						$info5['batch_no']			= $res9['batch_no'];
						$info5['customer_id']		= $res9['customer_id'];
						$info5['batch_id']			= $res9['batch_id'];

						$ins_id4 = add_record("tbl_stock_trn", $info5, $dbcon);
					}
				}
			}
		}
		//reserve wise deduct stock entry end

		//direct stock deduct entry start
		if ($inv_qty > 0) {
			$qry10 = "select stock_id,product_id,base_unit,convert_unit,base_rate,conv_rate,godown_id,branch_id,batch_no,customer_id,batch_id,used_convert_stock,used_base_stock,base_stock,convert_stock from tbl_stock_trn as cert 
											where stock_status=0 and stock_flage=1 and product_id=" . $re2['product_id'];
			$result10 = $dbcon->query($qry10);
			$stock_qty_o = 0;
			while ($res10 = mysqli_fetch_assoc($result10)) {
				$stock_qty_o = $res10['base_stock'];

				$qry12d = "select IFNULL(sum(base_stock),0) as total_used_base,IFNULL(sum(convert_stock),0) as total_used_convert from tbl_stock_trn as cert 
													where stock_status!=2 and stock_flage=2 and perent_id=" . $res10['stock_id'];
				$result12d = $dbcon->query($qry12d);
				$res12d = mysqli_fetch_assoc($result12d);
				$stock_qty_o = $stock_qty_o - $res12d['total_used_base'];
				$qry11 = "select reserve_id,base_unit,base_stock,approve_base_stock,convert_unit,convert_stock,approve_convert_stock,product_id,godown_id,sales_order_trn_id,stock_id,branch_id,customer_id from tbl_reserve_stock as cert 
												where stock_flage=1 and stock_status=0 and stock_id=" . $res10['stock_id'];
				$result11 = $dbcon->query($qry11);
				while ($res11 = mysqli_fetch_assoc($result11)) {

					$qry12 = "select IFNULL(sum(base_stock),0) as total_used_base,IFNULL(sum(convert_stock),0) as total_used_convert from tbl_reserve_stock as cert 
													where stock_status=0 and stock_flage=2 and perent_id=" . $res11['reserve_id'];
					$result12 = $dbcon->query($qry12);
					$res12 = mysqli_fetch_assoc($result12);

					$pending_base_so = $res11['base_stock'] - $res12['total_used_base'];
					$stock_qty_o = $stock_qty_o - $pending_base_so;
					//$pending_conv=$res1['convert_stock']-$res2['total_used_convert'];
				}
				if ($stock_qty_o > 0) {
					if ($inv_qty > 0) {
						if ($inv_qty >= $stock_qty_o) {
							$dqty = $stock_qty_o;
						} else {
							$dqty = $inv_qty;
						}
						$inv_qty = $inv_qty - $dqty;
						$type = "conv_unit";
						$dqty_conv = convert_stock_new($dbcon, $dqty, $res10['product_id'], $type);

						$info56['stock_date']		= date("Y-m-d");
						$info56['product_id']		= $res10['product_id'];
						$info56['base_unit']		= $res10['base_unit'];
						$info56['base_stock']		= $dqty;
						$info56['convert_unit']		= $res10['convert_unit'];
						$info56['convert_stock']	= $dqty_conv;
						$info56['base_rate']		= $res10['base_rate'];
						$info56['conv_rate']		= $res10['conv_rate'];
						$info56['stock_flage']		= 2;
						$info56['godown_id']		= $res10['godown_id'];
						$info56['ref_name']			= "invoice_trn";
						$info56['ref_id']			= $re2['trancation_id'];
						$info56['stock_status']		= 0;
						$info56['cdate']			= date("Y-m-d H:i:s");
						$info56['user_id']			= $_SESSION['user_id'];
						$info56['company_id']		= $_SESSION['company_id'];
						$info56['branch_id']		= $res10['branch_id'];
						$info56['perent_id']		= $res10['stock_id'];
						//$info56['reserve_id']		= $ins_id3;
						$info56['batch_no']			= $res10['batch_no'];
						$info56['customer_id']		= $res10['customer_id'];
						$info56['batch_id']			= $res10['batch_id'];

						$ins_id5 = add_record("tbl_stock_trn", $info56, $dbcon);

						$inv_trn1['used_base_stock'] = $res10['used_base_stock'] + $info56['base_stock'];
						$inv_trn1['used_convert_stock'] = $res10['used_convert_stock'] + $info56['convert_stock'];
						$updatetrnid1 = update_record('tbl_stock_trn', $inv_trn1, " stock_id=" . $res10['stock_id'], $dbcon, '');
					}
				}
			}
		}

		//direct stock deduct entry end
	}
}
function remove_stock_inv_trn_wise($dbcon, $inv_trn_id)
{
	$qry = "select reserve_id,stock_id,product_id,perent_id,used_base_stock,used_convert_stock,base_stock,convert_stock from tbl_stock_trn as cert 
					where stock_status=0 and stock_flage=2 and ref_name='invoice_trn' and ref_id=" . $inv_trn_id;
	$result = $dbcon->query($qry);
	while ($res = mysqli_fetch_assoc($result)) {
		$inv_trn1['stock_status'] = 2;
		$updatetrnid1 = update_record('tbl_stock_trn', $inv_trn1, " stock_id=" . $res['stock_id'], $dbcon, '');
		if ($res['reserve_id'] != 0) {
			$inv_trn2['stock_status'] = 2;
			$updatetrnid1d = update_record('tbl_reserve_stock', $inv_trn2, " reserve_id=" . $res['reserve_id'], $dbcon, '');
		} else {

			$qry1 = "select used_base_stock,used_convert_stock from tbl_stock_trn as cert 
							where stock_id=" . $res['perent_id'];
			$result1 = $dbcon->query($qry1);
			$res1 = mysqli_fetch_assoc($result1);

			$inv_trn3['used_base_stock'] = $res1['used_base_stock'] - $res['base_stock'];
			$inv_trn3['used_convert_stock'] = $res1['used_convert_stock'] - $res['convert_stock'];
			$updatetrnid1e = update_record('tbl_stock_trn', $inv_trn3, " stock_id=" . $res['perent_id'], $dbcon, '');
		}
	}
}
function paking_wise_stock_deduct($dbcon, $inv_trn)
{
	/*$qry="select * from tbl_invoicetrn as cert where trancation_status=0 and trancation_id=".$inv_trn." and paking_wise=1";
						$ros=$dbcon->query($qry);
						$row=mysqli_fetch_assoc($ros);*/

	$qry1 = "select * from so_paking_trn as cert where status=0 and invoice_trn_id=" . $inv_trn;
	$ros1 = $dbcon->query($qry1);
	while ($row1 = mysqli_fetch_assoc($ros1)) {
		$pakingqty = $row1['so_paking_qty'];

		$qry2 = "select * from tbl_reserve_stock as cert where stock_status!=2 and stock_flage=1 and ref_name='paking_trn' and temp_stock_allocate!=1 and ref_id=" . $row1['so_paking_trn_id'];
		$ros2 = $dbcon->query($qry2);
		while ($row2 = mysqli_fetch_assoc($ros2)) {
			if ($pakingqty > 0) {
				$que2 = "select sum(base_stock) as ubase_stock from tbl_reserve_stock as ta where temp_stock_allocate!=1 and stock_flage=2 and stock_status!=2 and perent_id=" . $row2['reserve_id'];
				$rs_di2 = $dbcon->query($que2);
				$re1ss = brp_mysqli_fetch_assoc($rs_di2);
				$rpending_stock = $row2['base_stock'] - $re1ss['ubase_stock'];
				if ($rpending_stock >= $pakingqty) {
					$useqty = $pakingqty;
				} else {
					$useqty = $rpending_stock;
				}
				$pakingqty = $pakingqty - $useqty;
				$info['reserve_date']		= date('Y-m-d');
				$info['product_id']			= $row2['product_id'];
				$info['godown_id']			= $row2['godown_id'];
				$info['base_unit']			= $row2['base_unit'];
				$info['base_stock']			= $useqty;
				$info['convert_unit']		= $row2['convert_unit'];
				$info['convert_stock']		= $useqty;
				$info['stock_flage']		= 2;
				$info['ref_name']			= "invoice_trn";
				$info['ref_id']				= $row2['invoice_trn_id'];
				$info['stock_status']		= 0;
				$info['cdate']				= date('Y-m-d H:i:s');
				$info['user_id']			= $_SESSION['user_id'];
				$info['company_id']			= $_SESSION['company_id'];
				$info['sales_order_trn_id']	= $row2['sales_order_trn_id'];
				$info['branch_id']			= $row2['branch_id'];
				$info['perent_id']			= $row2['reserve_id'];
				$info['stock_id']			= $row2['stock_id'];

				$inserid = add_record('tbl_reserve_stock', $info, $dbcon);

				$que3 = "select * from tbl_stock_trn as ta where stock_status!=2 and stock_flage=1 and stock_id=" . $info['stock_id'];
				$rs_di3 = $dbcon->query($que3);
				$re3 = brp_mysqli_fetch_assoc($rs_di3);

				$info2['stock_date']		= date('Y-m-d');
				$info2['product_id']		= $re3['product_id'];
				$info2['base_unit']			= $re3['base_unit'];
				$info2['base_stock']		= $info['base_stock'];
				$info2['convert_unit']		= $re3['convert_unit'];
				$info2['convert_stock']		= $info['convert_stock'];
				$info2['base_rate']			= $re3['base_rate'];
				$info2['conv_rate']			= $re3['conv_rate'];
				$info2['stock_flage']		= 2;
				$info2['godown_id']			= $re3['godown_id'];
				$info2['ref_name']			= "invoice_trn";
				$info2['ref_id']			= $info['ref_id'];
				$info2['cdate']				= date('Y-m-d H:i:s');
				$info2['user_id']			= $_SESSION['user_id'];
				$info2['company_id']		= $_SESSION['company_id'];
				$info2['branch_id']			= $re3['branch_id'];
				$info2['perent_id']			= $re3['stock_id'];
				$info2['reserve_id']		= $inserid;
				$info2['batch_no']			= $re3['batch_no'];
				$info2['batch_id']			= $re3['batch_id'];

				$inseridss = add_record('tbl_stock_trn', $info2, $dbcon);
			}
		}
		invoice_status_in_paking($dbcon, $row1['so_paking_id']);
	}
}
function invoice_status_in_paking($dbcon, $so_paking_id)
{
	$qry1 = "select sum(so_paking_qty) as pen_qty from so_paking_trn as cert where status=0 and invoice_status=0 and so_paking_id=" . $so_paking_id;
	$ros1 = $dbcon->query($qry1);
	$row1 = mysqli_fetch_assoc($ros1);

	if ($row1['pen_qty'] > 0) {
		$info1['invoice_status'] = 0;
	} else {
		$info1['invoice_status'] = 1;
	}

	$updateid = update_record("so_paking", $info1, "so_paking_id=" . $so_paking_id, $dbcon);
}
