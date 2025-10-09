<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start(); //start session
$AJAX = true;
include('../../include/quoturlfileinner.php');
include("../send_quotation.php");
include_once(COMMON_FUNCTION_INNER_PATH . "finance_common_functions.php");
$getspecialConfiguration = getspecialConfiguration($dbcon);
$incPath = $path . 'include/';
include_once($incPath . "common_send_email.php");
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	QUOTATION_SLUG_EDIT,
	QUOTATION_SLUG_DELETE,
	QUOTATION_SLUG_APPROVE,
	QUOTATION_SLUG_FINAL_APPROVE,
	QUOTATION_SLUG_PRINT
]);

$POST = ($_POST != NULL) ? bulk_filter($dbcon, $_POST) : bulk_filter($dbcon, $_GET);

if (strtolower($POST['mode']) == "fetch") {

	$cur_user_id = $_SESSION['user_id'];
	$cur_user = getUserDetailById($dbcon, $cur_user_id);
	$send_email_flag = false;
	if (!empty($cur_user['common_email_id'])) {
		$send_email_flag = true;
	}

	$s_date = explode(' - ', $POST['date']);
	$_SESSION['start'] = $s_date[0];
	$_SESSION['end'] = $s_date[1];

	$set = "select company_name from tbl_company as comp where company_id=" . $_SESSION['company_id'];

	$comp_rel = mysqli_fetch_assoc($dbcon->query($set));

	$where = '';
	$branch_id = $POST['branch_id'];
	if ($branch_id) {
		$where .= check_branch('quot', $branch_id);
	}
	$getapprovalsetting = get_userwise_approval_setting($dbcon, 1, $_SESSION['user_id']);
	$where .= "  and quot.quotation_date >= '" . date('Y-m-d', strtotime($s_date[0])) . "' AND quot.quotation_date <= '" . date('Y-m-d', strtotime($s_date[1])) . "'";

	$user_id = $_SESSION['user_id'];
	// $fis = check_crm_find_in_set($dbcon, $user_id, 1);
	// $where .= ' and tsk.assign_user_ids in (' . $fis . ')';

	if ($POST['approve_status'] != "") {
		$where .= " and quot.approve_status=" . $POST['approve_status'];
	}

	if (!empty($POST['stage_id'])) {
		$where .= " AND inq.opp_id =" . $POST['stage_id'];
	}

	$appData = array();
	$i = 1;
	$aColumns = array('quot.quotation_id', 'quot.quotation_no', 'city.city_name', 'pro.product_icode', 'qtrn.product_spec', 'qtrn.product_id', 'quot.quotation_date', 'cust.cust_name', 'cust.cust_email', 'cust.cust_mobile', 'quot.quot_header', 'inq.inquiry_no', 'quot.quot_subject', 'usr.user_name', 'quot.quotation_status', 'quot.start_quotation_id', 'quot.cdate', 'quot.revise_status', 'quot.prev_quotation_id', 'quot.approve_status', 'quot.cust_id', 'quot.company_id', 'inq.stage_prob', 'tsk.assign_user_ids', 'stage.opp_stage', 'stage.opp_color', 'quot.g_total', 'quot.inquiry_id', 'quot.sales_order_status', 'quot.currency_id', 'usr_approv_sett.amount_type as approve_amount_type', 'usr_approv_sett.amount as approve_amount', 'usr_approv_sett.percentage as approve_percentage');


	// $aColumns = array('quot.quotation_id', 'quot.quotation_no', 'city.city_name', 'pro.product_icode', 'qtrn.product_spec', 'qtrn.product_id', 'quot.quotation_date', 'cust.cust_name', 'cust.cust_email', 'cust.cust_mobile', 'quot.quot_header', 'inq.inquiry_no', 'quot.quot_subject', 'usr.user_name', 'quot.quotation_status', 'quot.start_quotation_id', 'quot.cdate', 'quot.revise_status', 'quot.prev_quotation_id', 'quot.approve_status', 'quot.cust_id', 'quot.company_id', 'inq.stage_prob', 'tsk.assign_user_ids', 'stage.opp_stage', 'stage.opp_color', 'quot.g_total', 'quot.inquiry_id', 'quot.sales_order_status', 'quot.currency_id');

	$sIndexColumn = "quot.quotation_id";
	$isWhere = array("quot.quotation_status = 0 and quot.revise_status=0 and quot.company_id in (0,$_SESSION[company_id])" . $where . check_user_inq('quot'));
	$sTable = "tbl_quotation as quot";
	$isJOIN = array(
		'left join tbl_customer as cust on cust.cust_id=quot.cust_id',
		'left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id',
		'left join users as usr on usr.user_id=inq.user_id',
		'left join tbl_quotation_trn as qtrn on qtrn.quotation_id=quot.quotation_id',
		'left join product_mst as pro on pro.product_id=qtrn.product_id',
		'left join tbl_task as tsk on tsk.inquiry_id=inq.inquiry_id',
		'left join tbl_userwise_approval_setting as usr_approv_sett on usr_approv_sett.permission_user_id=tsk.assign_user_ids and usr_approv_sett.status=0 and usr_approv_sett.module_type=1',
		'left join tbl_cust_address as addr on addr.cust_id=cust.cust_id',
		'left join state_mst as state on state.stateid=addr.c_add_state',
		'left join tbl_opportunity_mst as stage on stage.opp_id=inq.opp_id',
		'left join city_mst as city on city.cityid=addr.c_add_city'
	);
	$hOrder = "quot.quotation_id desc";
	$hGroupby = array("quot.quotation_id");
	include('../../../include/pagging.php');
	$id = 1;

	$q = "select * from tbl_userwise_approval_setting as usr_approv_sett where usr_approv_sett.permission_user_id='" . $_SESSION['user_id'] . "' and usr_approv_sett.status=0 and usr_approv_sett.module_type=1";
	$result_q = $dbcon->query($q);
	$res_q = mysqli_fetch_assoc($result_q);

	foreach ($sqlReturn as $row) {

		$aprroval_status = true;
		if ($res_q && $_SESSION['user_type'] != 2) {
			$query_qtrn = "select quot_trn_status,quotation_id,quot_trn_id,product_discount,discount_per from tbl_quotation_trn where quot_trn_status!=2 and quotation_id='" . $row['quotation_id'] . "'";
			$result_qtrn = $dbcon->query($query_qtrn);

			while ($rel_qtrn = mysqli_fetch_assoc($result_qtrn)) {
				if ($row['approve_amount_type'] == 2 && $rel_qtrn['discount_per'] > $row['approve_percentage']) {
					$aprroval_status = false;
				} else if ($row['approve_amount_type'] == 1 && $rel_qtrn['product_discount'] > $row['approve_amount']) {
					$aprroval_status = false;
				}
			}
		}

		$row_data = array();
		$bg_color = ($row['opp_color']) ? trim($row['opp_color']) : '';
		if (in_array(QUOTATION_SLUG_EDIT, $bulkAccessArray) && $row['approve_status'] == 0) {
			$row_data[] = '<a class="" data-original-title="Edit ' . $row["quotation_no"] . '" data-toggle="tooltip" data-placement="top" href="' . ROOT . CRM_ROOT . 'quotation_edit/' . $row['quotation_id'] . '">' . $row["quotation_no"] . '</a>';
			$row_data[] = '<a class="" data-original-title="Edit ' . $row["quotation_no"] . '" data-toggle="tooltip" data-placement="top" href="' . ROOT . CRM_ROOT . 'quotation_edit/' . $row['quotation_id'] . '">' . date('d M, Y', strtotime($row["quotation_date"])) . '</a>';
			$row_data[] = '<a class="" data-original-title="Edit ' . $row["quotation_no"] . '" data-toggle="tooltip" data-placement="top" href="' . ROOT . CRM_ROOT . 'quotation_edit/' . $row['quotation_id'] . '">' . $row["cust_name"] . '</a>';
			if ($getspecialConfiguration['power_drive'] == 1) {

				$row_data[] = $row['product_icode'];
				$row_data[] = $row['product_spec'];
			}
			$row_data[] = '<a class="" data-original-title="Edit ' . $row["quotation_no"] . '" data-toggle="tooltip" data-placement="top" href="' . ROOT . CRM_ROOT . 'quotation_edit/' . $row['quotation_id'] . '">' . $row["inquiry_no"] . '</a>';
			$row_data[] = '<a class="" data-original-title="Edit ' . $row["quotation_no"] . '" data-toggle="tooltip" data-placement="top" href="' . ROOT . CRM_ROOT . 'quotation_edit/' . $row['quotation_id'] . '"><span class="btn btn-sm" style="color:black;background-color: ' . $bg_color . ';">' . $row['opp_stage'] . '<br>(' . $row['stage_prob'] . '%)<span></a>';
			$row_data[] = '<a class="" data-original-title="Edit ' . $row["quotation_no"] . '" data-toggle="tooltip" data-placement="top" href="' . ROOT . CRM_ROOT . 'quotation_edit/' . $row['quotation_id'] . '">' . $row["city_name"] . '</a>';
			$row_data[] = $row["g_total"];
			if ($getspecialConfiguration['durva_permission'] == 1) {
				$row_data[] = '<a class="" data-original-title="Edit ' . $row["quotation_no"] . '" data-toggle="tooltip" data-placement="top" href="' . ROOT . CRM_ROOT . 'quotation_edit/' . $row['quotation_id'] . '">' . $row['quot_subject'] . '</a>';
			}
			$row_data[] = '<a class="" data-original-title="Edit ' . $row["quotation_no"] . '" data-toggle="tooltip" data-placement="top" href="' . ROOT . CRM_ROOT . 'quotation_edit/' . $row['quotation_id'] . '">' . $row["user_name"] . '</a>';
		} else {
			$row_data[] = $row['quotation_no'];
			$row_data[] = date('d M, Y', strtotime($row['quotation_date']));
			$row_data[] = $row['cust_name'];
			if ($getspecialConfiguration['power_drive'] == 1) {

				$row_data[] = $row['product_icode'];
				$row_data[] = $row['product_spec'];
			}
			$row_data[] = $row['inquiry_no'];

			$row_data[] = '<span class="btn btn-sm" style="color:black;background-color: ' . $bg_color . ';">' . $row['opp_stage'] . '<br>(' . $row['stage_prob'] . '%)<span>';
			$row_data[] = $row['city_name'];
			$row_data[] = $row['g_total'];
			if ($getspecialConfiguration['durva_permission'] == 1) {
				$row_data[] = $row['quot_subject'];
			}
			$row_data[] = $row['user_name'];
		}

		$query_i = "select GROUP_CONCAT(DISTINCT mst.user_name SEPARATOR ',<br/>') as asinguser from users as mst
		where mst.user_id in (" . $row['assign_user_ids'] . ")";
		$result_i = $dbcon->query($query_i);
		$rel_i = mysqli_fetch_assoc($result_i);

		$row_data[] = $rel_i['asinguser'];

		if ($row['approve_status'] == '1') {
			$row_data[] = '<button class="btn btn-xs btn-success" data-original-title="Authorized" data-toggle="tooltip" data-placement="top" >Authorized</button>';
		} else if ($row['approve_status'] == '2') {
			$disapproved = get_quot_disapproved_reason($dbcon, 'tbl_quot_aprv_log', 'approve_remark', $row['quotation_id'], 'approve_status', '2', 'quot_aprv_log_id');
			$row_data[] = '<button class="btn btn-xs btn-danger" data-original-title="' . $disapproved . '" data-toggle="tooltip" data-placement="top" >Rejected</button>';
		} else {
			$row_data[] = '<button class="btn btn-xs btn-warning" data-original-title="Pending" data-toggle="tooltip" data-placement="top" >Pending</button>';
		}


		$quotation_no = $dbcon->real_escape_string($row['quotation_no']);
		$edit = '';
		$delete = '';
		$print_btn = '';
		$revise_btn = '';
		$apprv_btn = '';
		$send_email = '';
		$revision_history = '';
		if (in_array(QUOTATION_SLUG_EDIT, $bulkAccessArray)) {
			$edit = '<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . CRM_ROOT . 'quotation_edit/' . $row['quotation_id'] . '"><i class="fa fa-pencil"></i></a>';
		}
		if (in_array(QUOTATION_SLUG_DELETE, $bulkAccessArray)) {
			$delete = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_quotation(' . $row['quotation_id'] . ',\'' . $quotation_no . '\',' . $row['prev_quotation_id'] . ')"><i class="fa fa-trash-o"></i></button>';
		}

		if ($row['revise_status'] == '1') {
			$edit = '';
			$delete = '';
		}

		$send_email_page_path = '';
		// if($row['approve_status']!='0' || in_array(QUOTATION_SLUG_APPROVE,$bulkAccessArray)){
		if (in_array(QUOTATION_SLUG_PRINT, $bulkAccessArray)) {
			$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='" . $_SESSION['company_id'] . "'");
			$rels = mysqli_fetch_assoc($menusql);
			$menu_show_permissions = explode(",", $rels['print_permission']);
			$sql = $dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 1 AND approve_status = 1 AND status = 0 ORDER BY priority");
			while ($res = mysqli_fetch_assoc($sql)) {
				if (in_array($res['id'], $menu_show_permissions)) {
					if ($res['with_out_logo'] == 0) {
						$print_btn .= '<a class="btn btn-xs btn-primary" data-original-title="' . $res['print_name'] . '" data-toggle="tooltip" data-placement="top" target="_blank" href="' . ROOT . PRINT_ROOT . $res['page_path'] . '/' . $row['quotation_id'] . '?' . time() . '" style="background: ' . $res['icon_color'] . '; border-color: ' . $res['icon_color'] . ';"><i class="' . $res['fa_icon'] . '"></i></a>';
					} else {
						$ddf = "'" . DOMAIN_F . PRINT_ROOT . $res['page_path'] . "/" . $row['quotation_id'] . "'";
						//$ddf="dfsd";
						$print_btn .= ' <button class="btn btn-xs btn-success" data-original-title="Print" data-toggle="tooltip" data-placement="top" onClick="open_print(' . $ddf . ')"><i class="' . $res['fa_icon'] . '"></i></button>';
					}

					$quotation_link .= "'" . $_SERVER['SERVER_NAME'] . ROOT . PRINT_ROOT . $res['page_path'] . '/' . $row['quotation_id'] . '?' . time() . "'";

					if ($getspecialConfiguration['flowjet_permission'] == '1') {
						$send_email_page_path = "flowjet_quotation_print";
					} else {
						$send_email_page_path = $res['page_path'];
					}
				}
			}

			if ($getspecialConfiguration['flowjet_permission'] == '1' && $row['currency_id'] > 0 && $row['currency_id'] != '68') {

				$print_btn .= '<a class="btn btn-xs btn-primary" data-original-title="CURRENCY PRINT" data-toggle="tooltip" data-placement="top" target="_blank" href="' . ROOT . PRINT_ROOT . 'flowjet_quotation_print_currency/' . $row['quotation_id'] . '?' . time() . '" style="background: ' . $res['icon_color'] . '; border-color: ' . $res['icon_color'] . ';"><i class="' . $res['fa_icon'] . '"></i> Cur</a>';
			}
		}
		// }

		if ($aprroval_status) {
			if (($getapprovalsetting['amount'] >= $row['g_total']) && ($getapprovalsetting['auto_approval'] == 1)) {
				if (in_array(QUOTATION_SLUG_APPROVE, $bulkAccessArray)) {
					$apprv_btn = '<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Quotation" data-toggle="tooltip" data-placement="top" onClick="open_approv_quot(' . $row['quotation_id'] . ',\'' . $quotation_no . '\',\'' . $row['inquiry_id'] . '\')"><i class="fa fa-exclamation-triangle"></i></button>';
				}
			} else {
				if (in_array(QUOTATION_SLUG_APPROVE, $bulkAccessArray)) {
					$apprv_btn = '<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Quotation" data-toggle="tooltip" data-placement="top" onClick="open_approv_quot(' . $row['quotation_id'] . ',\'' . $quotation_no . '\',\'' . $row['inquiry_id'] . '\')"><i class="fa fa-exclamation-triangle"></i></button>';
				}
			}
		}

		if ($send_email_flag || $getspecialConfiguration['adk_permission'] == '1') {
			$dir_sent_quotation_mail = '<button class="btn btn-xs btn-primary" data-original-title="Send Mail" data-toggle="tooltip" data-placement="top" onClick="open_mail_dir_modal(' . $row['quotation_id'] . ',\'' . $row['cust_email'] . '\',\'' . $send_email_page_path . '\')"><i class="fa fa-envelope"></i></button>';
		} else {
			$dir_sent_quotation_mail = "";
		}


		//$send_whatsapp='<button class="btn btn-xs btn-primary" data-original-title="Send Whatsapp" data-toggle="tooltip" data-placement="top" onClick="send_quotation('.$row['quotation_id'].',\''.$quotation_no.'\')"><i class="fa fa-whatsapp"></i></button>';


		if ($row['cust_mobile']) {
			$company_configuration = getCompanyConfiguration($dbcon);
			if ($company_configuration["enable_whatsapp"] == 1 && $row['approve_status'] == '1') {
				$send_whatsapp_new = '<a title="Send to Whatsapp New" type="button" class="btn btn-xs btn-success" onClick="send_quotation_whatsapp(' . $row['quotation_id'] . ',\'' . $row['cust_mobile'] . '\',\'' . $row['inquiry_id'] . '\',\'' . $send_email_page_path . '\')"> <i class="fa fa-whatsapp"></i></a>&nbsp;';
			} else {
				$send_whatsapp_new = '<a title="Send to Whatsapp" type="button" class="btn btn-xs btn-success" href="https://web.whatsapp.com/send?phone=+91' . $row['cust_mobile'] . '&text=' . $rel['cust_name'] . 'Thank you for your inquiry.%0aQuotation No:-' . $row['quotation_no'] . '%0aDate:- ' . date('d-m-Y', strtotime($row['quotation_date'])) . '%0aAmount:- ' . $row['g_total'] . '%0aBest Regards%0a' . $comp_rel['company_name'] . '" target="_blank"> <i class="fa fa-whatsapp"></i></a>&nbsp;';
			}
		}

		if ($row['approve_status'] == '1') {
			$edit = '';
			$delete = '';
		} else if ($row['approve_status'] == '2') {
			if ($row['revise_status'] == '1') {
				$revise_btn = '<button type="button" class="btn btn-xs btn-success" data-original-title="Quotation Revised" data-toggle="tooltip" data-placement="top"><i class="fa fa-check"></i></button>';
				$apprv_btn = '';
			} else {
				$revise_btn = '<a class="btn btn-xs btn-info" data-original-title="Revise Quotation" data-toggle="tooltip" data-placement="top" href="' . ROOT . CRM_ROOT . 'quotation_revise/' . $row['quotation_id'] . '"><i class="fa fa-repeat"></i></a>';
			}
			$edit = '';
			$delete = '';
			$send_whatsapp = '';
			$send_whatsapp_new = '';
			$sent_quotation_mail = '';
		}
		if ($row['stage_prob'] == '100' && $row['approve_status'] == '1') {
			$edit = '<button type="button" class="btn btn-xs btn-success" data-original-title="Inquiry Won" data-toggle="tooltip" data-placement="top">Won</button>';
			$delete = '';
			$revise_btn = '';
		}

		if ($row['quotation_id'] != $row['start_quotation_id']) {
			$revision_history = '<button class="btn btn-xs btn-primary" data-original-title="Revision History" data-toggle="tooltip" data-placement="top" onClick="open_revise_quotation_history(' . $row['quotation_id'] . ',' . $row['start_quotation_id'] . ',\'' . $quotation_no . '\')"><i class="fa fa-eye"></i></button>';
		}

		$so_status = '';
		/*if($row['sales_order_status']==1){
			$so_status = '<button class="btn btn-sm btn-success">Sales Order Done</button>';
		}else{
			$so_status = '<button class="btn btn-sm btn-warning">Sales Order Pending</button>';
		}*/

		if (empty($row['cust_mobile'])) {
			$send_whatsapp = "";
			$send_whatsapp_new = "";
		}

		$row_data[] = $edit . ' ' . $delete . ' ' . $print_btn . ' ' . $revise_btn . ' ' . $apprv_btn . ' ' . $sent_quotation_mail . ' ' . $dir_sent_quotation_mail . ' ' . $send_whatsapp . ' ' . $send_whatsapp_new . ' ' . $revision_history . ' ' . $so_status;

		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode($output);
} else if (strtolower($POST['mode']) == "add") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	if ($POST['revise_status']) { //Get Revise Count No
		$get_rev_cnt = "select count(quotation_id) as ttl_cnt,(select quotation_no from tbl_quotation where quotation_id=" . $POST['start_quotation_id'] . ") as qt_no from tbl_quotation where quotation_status=0 and quot_revise_type=0 and start_quotation_id=" . $POST['start_quotation_id'];
		$rev_cnt = mysqli_fetch_assoc($dbcon->query($get_rev_cnt));

		if ($POST['quot_revise_type'] == 0) {
			$info['quotation_no'] 			= $rev_cnt['qt_no'] . "/R-" . $rev_cnt['ttl_cnt'];
		} else {
			$get_prev_no = "select quotation_no from tbl_quotation where quotation_id=" . $POST['prev_quotation_id'];
			$prev_no = mysqli_fetch_assoc($dbcon->query($get_prev_no));
			$info['quotation_no']			= $prev_no['quotation_no'];
		}

		$info['start_quotation_id']		= $POST['start_quotation_id'];
		$info['prev_quotation_id']		= $POST['prev_quotation_id'];
		$upd_prev_qt_sts = $dbcon->query("update tbl_quotation set revise_status=1 where quotation_id=" . $POST['prev_quotation_id']);
	} else {
		$info['quotation_no']		= load_common_no($dbcon, QUOTATION_SERIES);
		//Update Start series of No
		update_common_no($dbcon, QUOTATION_SERIES);
	}

	if ($POST['cust_id']) {
		$query = $dbcon->query("SELECT tc.cust_name,tc.cust_mobile,tc.cust_gst,ca.c_add_location, ca.c_add_country, ca.c_add_state, ca.c_add_city
			FROM tbl_customer tc
			LEFT JOIN tbl_cust_address as ca ON ca.cust_id = tc.cust_id
			WHERE tc.cust_id = " . $POST['cust_id']);
		$cust_data = $query->fetch_assoc();
		$info['qt_company_name']	= $cust_data['cust_name'];
		$info['qt_com_mno']         = $cust_data['cust_mobile'];
		$info['qt_com_gstno']		= $cust_data['cust_gst'];
		$info['qt_com_addr']		= $cust_data['c_add_location'];
		$info['qt_add_country']		= $cust_data['c_add_country'];
		$info['qt_add_state']		= $cust_data['c_add_state'];
		$info['qt_add_city']		= $cust_data['c_add_city'];
	}

	//$curncy_trn['currency_enable'] 	= 1;
	$curncy_trn['currency_id'] 		= $POST['currency_id'];
	$curncy_trn['currency_rate'] 	= $POST['currency_rate'];


	$show_user_ids					= show_user_ids($dbcon, $_SESSION['user_id']);
	$info['quot_revise_type']		= $POST['quot_revise_type'];
	$info['quotation_date']         = date('Y-m-d', strtotime($POST['quotation_date']));
	$info['cust_id']				= $POST['cust_id'];
	$info['c_con_id']				= $POST['c_con_id'];
	$info['inquiry_id']				= $POST['inquiry_id'];
	$info['task_id']				= $POST['task_id'];
	$info['quot_subject']           = $POST['quot_subject'];
	$info['quot_type']				= $POST['quot_type'];
	$info['quotation_valid_date']	= date('Y-m-d', strtotime($POST['quotation_valid_date']));
	$info['inquiry_ref_date']	= date('Y-m-d', strtotime($POST['inquiry_ref_date']));
	$info['quotation_ref']          = $POST['quotation_ref'];
	$info['client_id']          = $POST['client_id'];
	$info['payment_terms']          = $POST['payment_terms'];
	$info['quot_remark']            = $_POST['quot_remark'];
	$info['install_type']			= $POST['install_type'];
	$info['production_up_to']		= $POST['production_up_to'];

	$info['delivery_type']			= $POST['delivery_type'];
	$info['delivery_date']			= date('Y-m-d', strtotime($POST['quo_delivery_date']));

	$info['transid']                        = $POST['transid'];
	$info['trans_add']                      = $POST['trans_add'];
	$querypayt = 'select payment_days,payment_terms from pay_terms where terms_id=' . $POST['payment_terms_id'];

	$resultpayt = $dbcon->query($querypayt);
	$rowpayt = brp_mysqli_fetch_array($resultpayt);

	$info['payment_tems']			= $rowpayt['payment_days'];
	$info['payment_terms_id']		= $POST['payment_terms_id'];

	if ($getspecialConfiguration['jainflex_permission'] == 1) {
		$info['payment_tems_jainflex']		= $POST['payment_tems_jainflex'];
	}
	$info['delivary_time_apson']		= $POST['delivary_time_apson'];

	//$info['payment_tems']			= $POST['payment_tems'];
	$info['mode_of_dispatch']			= $POST['mode_of_dispatch'];
	$info['destination']			= $POST['destination'];

	/*$info['currency_id']            = (isset($POST['currency_enable'])) ? $POST['currency_id'] : $_SESSION['currency_id'];
	$info['currency_enable']		= $POST['currency_enable'];
	$info['currency_rate']			= (isset($POST['currency_enable'])) ? $POST['currency_rate'] : 1 ;*/

	$info['gst_type']				= $POST['gst_type'];
	$info['quatation_greeting']		= $_POST['quatation_greeting'];

	$explo = explode(",", $_POST['anx_id']);
	$an_id = implode(',', array_map('quote', $explo));
	/*var_dump($an_id);*/
	/*var_dump($an_id);*/
	$an_id1 = implode(',', $explo);
	/*var_dump($an_id1);*/
	/*var_dump($an_id1);*/
	$query_anex = 'select GROUP_CONCAT(an_id ORDER BY FIND_IN_SET(an_name,"' . $an_id1 . '")) as an_id from tbl_annexure where an_name IN (' . $an_id . ')';

	$result_anex = $dbcon->query($query_anex);
	$row_anex = brp_mysqli_fetch_array($result_anex);

	$info['an_id']					= $row_anex['an_id'];

	$info['quot_annex_content']		= $_POST['quot_annex_content'];
	$info['quot_address']			= $POST['quot_address'];
	$info['with_bom_flag']        	= $POST['with_bom_flag'];
	$info['inquiry_type']         	= $POST['inquiry_type'];
	$info['quot_header']			= $_POST['quot_header'];
	$info['quot_footer']			= $_POST['quot_footer'];
	$info['attach_part']			= $_POST['attach_part'];
	$info['project_name']			= $_POST['project_name'];

	$info['orange']					= $POST['orange'];
	$info['mfg']					= $POST['mfg'];
	$info['trading']				= $POST['trading'];
	$info['repairing']				= $POST['repairing'];
	$info['other']					= $POST['other'];

	/*$info['orange_total']		  =	$POST['orange_total'];	
	$info['mfg_total']			  = $POST['mfg_total'];
	$info['trading_total']		  = $POST['trading_total'];
	$info['repairing_total']		  = $POST['repairing_total'];
	$info['other_total']		      = $POST['other_total'];*/

	$info['terms_type']				= $POST['terms_type'];

	if ($POST['currency_id'] == $_SESSION['currency_id']) {
		$info['g_total']				= $POST['g_total'];
		$info['basic_total']			= $POST['total'];
		$info['basic_total_conv']		= $POST['total'] * $POST['currency_rate'];
		$info['g_total_conv']			= $POST['g_total'] * $POST['currency_rate'];
	} else {
		$info['g_total']				= $POST['g_total'] * $POST['currency_rate'];
		$info['basic_total']			= $POST['total'] * $POST['currency_rate'];
		$info['basic_total_conv']		= $POST['total'];
		$info['g_total_conv']			= $POST['g_total'];
	}

	$info['terms'] = isset($_POST['terms']) ? $_POST['terms'] : ''; //added by hardi
	$info['shipped_via'] = isset($_POST['shipped_via']) ? $_POST['shipped_via'] : ''; //added by hardi
	$info['order_no'] = isset($_POST['order_no']) ? $_POST['order_no'] : ''; //added by hardi
	$info['delivery_no'] = isset($_POST['delivery_no']) ? $_POST['delivery_no'] : ''; //added by hardi
	$info['delivery_from'] = isset($_POST['delivery_from']) ? $_POST['delivery_from'] : ''; //added by hardi
	$info['po_address_to'] = isset($_POST['po_address_to']) ? $_POST['po_address_to'] : ''; //added by hardi

	$info['quot_general_terms_condition_content'] = $_POST['quot_general_terms_condition_content'];
	$info['quot_battery_limits_and_schedule_exclusion_content'] = $_POST['quot_battery_limits_and_schedule_exclusion_content'];
	//                $info['kind_attn']			= $POST['kind_attn'];
	//                $info['quatation_greeting']		= $_POST['quatation_greeting'];
	//		$info['attach_part']			= $_POST['attach_part'];

	$info['create_date']	= date('Y-m-d H:i:s');
	$info['cdate']			= date("Y-m-d H:i:s");
	$info['show_user_ids']          = $show_user_ids;
	$info['user_id']		= $_SESSION['user_id'];
	$info['company_id']		= $_SESSION['company_id'];
	$ins_quotation_id = add_record('tbl_quotation', array_merge($info, $curncy_trn), $dbcon, $branch_id);

	if (!$POST['revise_status']) {
		$upd_strt_qry = $dbcon->query("update tbl_quotation set start_quotation_id=" . $ins_quotation_id . " where quotation_id=" . $ins_quotation_id);
	}

	/*Update Trn Table Start*/
	if ($ins_quotation_id) {
		$infotrn['quotation_id']		= $ins_quotation_id;
		$infotrn['quot_trn_status']	= 0;
		$updatetrnid = update_record('tbl_quotation_trn', $infotrn, "quot_trn_status=3 and user_id=" . $_SESSION['user_id'], $dbcon, $branch_id);

		$infoprojecttrn['quotation_id']		= $ins_quotation_id;
		$infoprojecttrn['quotation_projecttrn_status']		= 0;

		update_record('tbl_quotation_project_trn', $infoprojecttrn, "quotation_id=0 and inquiry_id=" . $POST['project_inquiry_id'], $dbcon, $branch_id);
	}
	/*Update Trn Table End*/
	/* Terms and Condition Start */
	foreach ($POST['tc_id'] as $key => $name) {
		$infotrm['tc_id']		= $POST['tc_id'][$key];
		$infotrm['ref_tc_id']		= $POST['ref_tc_id'][$key];
		$infotrm['tc_priority']		= $POST['tc_priority'][$key];
		$infotrm['tc_details']		= $_POST['tc_details'][$key];
		$infotrm['quotation_id']	= $ins_quotation_id;
		$infotrm['cdate']		= date("Y-m-d H:i:s");
		$infotrm['user_id']		= $_SESSION['user_id'];
		$infotrm['company_id']	= $_SESSION['company_id'];
		if (in_array($POST['tc_id'][$key], $POST['disp_term_flag'])) {
			$insertrmid = add_record('tbl_quotation_terms_trn', $infotrm, $dbcon, $branch_id);
		}
	}
	/* Terms and Condition End */

	//Update quotation id in budget TRN data-original-title
	/*$upd_qt_id_qry="update `tbl_quot_budget_trn` set quotation_id=".$ins_quotation_id." where find_in_set(quot_trn_id,(select group_concat(DISTINCT quot_trn_id) from tbl_quotation_trn where quotation_id=".$ins_quotation_id."))";
		$upd_qt_id_qry_rs=$dbcon->query($upd_qt_id_qry);*/


	/*Update Attach Trn Table Start*/
	if ($ins_quotation_id) {
		$infonote['quotation_id']			= $ins_quotation_id;
		$infonote['dfd_attach_status']	= 0;
		$updatetrnid = update_record('tbl_quot_dfd_attach', $infonote, "dfd_attach_status=3 and user_id=" . $_SESSION['user_id'], $dbcon, $branch_id);
	}
	/*Update Attach Trn Table End*/


	/*Direct Task Entry Start*/
	if ($POST['inquiry_id']) { //Auto Complete Prev Flp Task Before Add

		$prev_task_id = get_previous_taskid($dbcon, $POST['inquiry_id']);

		$upd_qry = "update tbl_task set task_status=1, is_delete=1,task_completion_date='" . date("Y-m-d H:i:s") . "' where task_status=0 and inquiry_id=" . $POST['inquiry_id'];
		$upd_qry_rs = $dbcon->query($upd_qry);

		$chkinqu = $dbcon->query("SELECT * FROM tbl_inquiry WHERE inquiry_id = " . $POST['inquiry_id']);
		$rowinq = brp_mysqli_fetch_assoc($chkinqu);


		//Add new Task


		$show_user_ids					= show_user_ids($dbcon, $POST['assign_user_ids']);
		$infotsk['show_user_ids']		= $show_user_ids;
		$infotsk['task_type_id']		= $POST['task_type_id'];
		$infotsk['task_rel_id']			= 5; //Fixed Type Inquiry
		$infotsk['opp_id']				= $rowinq['opp_id'];
		$infotsk['stage_prob']			= $rowinq['stage_prob'];
		$infotsk['sales_stage_id']		= $rowinq['sales_stage_id'];
		$infotsk['inquiry_id']			= $POST['inquiry_id'];
		$infotsk['quotation_id']		= $ins_quotation_id;
		$infotsk['cust_id']				= $POST['cust_id'];
		$infotsk['c_con_id']			= $POST['c_con_id'];
		$infotsk['assign_user_ids']		= $POST['assign_user_ids'];
		$infotsk['task_priority_id']    = $POST['task_priority_id'];
		$infotsk['task_due_date']		= date('Y-m-d H:i:s', strtotime($POST['task_due_date']));
		$infotsk['task_alert_id']		= 2;
		$infotsk['alert_date_time']		= date('Y-m-d H:i:s', strtotime($POST['task_due_date']));
		$infotsk['task_remark']         = $_POST['quot_remark'];
		$infotsk['create_date']         = date('Y-m-d H:i:s');
		$infotsk['entry_type']          = 1; //Fixed Task Type
		$infotsk['cdate']				= date("Y-m-d H:i:s");
		$infotsk['user_id']				= $_SESSION['user_id'];
		$infotsk['company_id']      	= $_SESSION['company_id'];
		$infotsk['is_delete']       	= 1;
		$infotsk['perent_id']      		= $prev_task_id['prev_taskid'];

		$ins_task_id = add_record('tbl_task', $infotsk, $dbcon, $branch_id);

		$update_quotation_task = $dbcon->query("update tbl_quotation set quotation_task_id=" . $ins_task_id . " where quotation_id=" . $ins_quotation_id);
		//$infoinq['show_user_ids']	= $show_user_ids;
		//$updateinqid=update_record('tbl_inquiry', $infoinq, "inquiry_id=".$POST['inquiry_id'], $dbcon);
	}
	/*Direct Task Entry End*/
	/*$basecurrency = getbasecurrency($dbcon);
	$curncy_trn['currency_id']		= $basecurrency['currencyid'];
	$curncy_trn['currency_rate'] 	= 1;*/
	foreach ($POST['bill_sundry_addon'] as $bill_sundry_addon_id => $bill_sundry_addon_amount) {

		$info_sundry_addon['sundry_ledger_id'] = $bill_sundry_addon_id;
		if ($POST['currency_id'] == $_SESSION['currency_id']) {
			$info_sundry_addon['sundry_amount'] = $bill_sundry_addon_amount;
			$info_sundry_addon['sundry_amount_conv'] = $bill_sundry_addon_amount * $POST['currency_rate'];
		} else {
			$info_sundry_addon['sundry_amount'] = $bill_sundry_addon_amount * $POST['currency_rate'];
			$info_sundry_addon['sundry_amount_conv'] = $bill_sundry_addon_amount;
		}
		$info_sundry_addon['sundry_voucher_id'] = $ins_quotation_id;
		$info_sundry_addon['sundry_voucher_type'] = QUOTATION_VOUCHER;
		$info_sundry_addon['sundry_voucher_table'] = 'tbl_quotation';
		$info_sundry_addon['cdate']	= date("Y-m-d H:i:s");
		$info_sundry_addon['user_id']	= $_SESSION['user_id'];
		$info_sundry_addon['company_id']	= $_SESSION['company_id'];
		//print_r(array_merge($info_sundry_addon,$curncy_trn));

		$sundry_addon_insert = add_record('tbl_bill_sundry_transaction', array_merge($info_sundry_addon, $curncy_trn), $dbcon);
	}

	foreach ($POST['bill_sundry_addon_tax'] as $addon_id => $addon_value) {

		$addon_explode = explode("-", $addon_value);

		$info_addon['sundry_gst_per'] = $addon_explode[1];
		//$info_addon['sundry_gst_amount'] = $addon_explode[0];

		if ($POST['currency_id'] == $_SESSION['currency_id']) {
			$info_addon['sundry_gst_amount'] = $addon_explode[0];
			$info_addon['sundry_gst_amount_conv'] = $addon_explode[0] * $POST['currency_rate'];
		} else {
			$info_addon['sundry_gst_amount'] = $addon_explode[0] * $POST['currency_rate'];
			$info_addon['sundry_gst_amount_conv'] = $addon_explode[0];
		}

		$updateaddontaxid = update_record('tbl_bill_sundry_transaction', $info_addon, "sundry_voucher_table='tbl_quotation' and isdelete=0 and sundry_voucher_id=" . $ins_quotation_id . " and sundry_ledger_id=" . $addon_id . " ", $dbcon);
	}

	if (strtolower($POST['delivery_type']) == "po_wise") {

		$sel_pro_rate = "select * from tbl_quotation_trn where quot_trn_status=0 and quotation_id=" . $inserpoid;
		$sel_pro_rate_rs = $dbcon->query($sel_pro_rate);

		while ($sel_pro_rate_rel = brp_mysqli_fetch_array($sel_pro_rate_rs)) {
			$delivery_da = "select * from tbl_quotation_delivery_date as mst 
			where mst.quo_delivery_date_status=0  and mst.quot_trn_id=" . $sel_pro_rate_rel['quot_trn_id'];

			$delivery_dae = $dbcon->query($delivery_da);
			if (brp_mysqli_num_rows($delivery_dae) > 0) {
				$inftrn11d['delivery_date'] = date('Y-m-d', strtotime($POST['delivery_date']));
				$updatetrnid = update_record('tbl_quotation_delivery_date', $inftrn11d, "quo_delivery_date_status=0 and quot_trn_id=" . $sel_pro_rate_rel['quot_trn_id'], $dbcon, $branch_id);
			} else {
				if ($sel_pro_rate_rel['unit_id'] === $sel_pro_rate_rel['rate_unit']) {
					$sqty = $sel_pro_rate_rel['product_qty'];
				} else {
					$sqty = $sel_pro_rate_rel['product_conv_qty'];
				}
				$infodeli['quot_trn_id'] 			= $sel_pro_rate_rel['quot_trn_id'];
				$infodeli['delivery_date'] 			= date('Y-m-d', strtotime($POST['quo_delivery_date']));
				$infodeli['product_qty'] 			= $sqty;
				$infodeli['unit_id'] 				= $sel_pro_rate_rel['rate_unit'];

				$infodeli['user_id']				= $_SESSION['user_id'];
				$infodeli['cdate']					= date("Y-m-d h:i:s");
				$infodeli['company_id']				= $_SESSION['company_id'];

				$inser_del = add_record('tbl_quotation_delivery_date', $infodeli, $dbcon, $branch_id);
			}
		}
	}
	//Auto approve if allowed
	$companyConfiguration = getCompanyConfiguration($dbcon);
	$final_btn_per = in_array(QUOTATION_SLUG_FINAL_APPROVE, $bulkAccessArray);
	//if($final_btn_per || $companyConfiguration['automatic_approval_quotation']==1){
	if ($companyConfiguration['automatic_approval_quotation'] == 1) {
		get_automatic_quotation_approval($dbcon, $ins_quotation_id);

		$querycu = "select cust.cust_email,quo.user_id,quo.cust_id,quo.prev_quotation_id from tbl_quotation as quo left join tbl_customer as cust on cust.cust_id=quo.cust_id where quo.quotation_id=" . $ins_quotation_id;
		$resultcu = $dbcon->query($querycu);
		$relcu = brp_mysqli_fetch_assoc($resultcu);
		$to_email_id = $relcu['cust_email'];

		$cur_user_id = $relcu['user_id'];
		$cur_user = getUserDetailById($dbcon, $cur_user_id);
		$from_email_id = ($cur_user && $cur_user['common_email_id']) ? $cur_user['common_email_id'] : ADMIN_EMAIL;

		if (!empty($relcu['prev_quotation_id'])) {
			$queryst = "select email_sms_id from email_sms_template where task_id=20 and status=0 and company_id=" . $_SESSION['company_id'];
		} else {
			$queryst = "select email_sms_id from email_sms_template where task_id=21 and status=0 and company_id=" . $_SESSION['company_id'];
		}

		$resultst = $dbcon->query($queryst);
		$relst = brp_mysqli_fetch_assoc($resultst);

		$mail_template = getEmailSMSTemplateById($dbcon, $relst['email_sms_id']);
		$module_id = 2;

		if ($mail_template && $to_email_id) {

			$querybcc = "select email_cc,email_bcc from email_sms_template where email_sms_id=" . $relst['email_sms_id'];
			$resultbdd = $dbcon->query($querybcc);
			$rel1 = brp_mysqli_fetch_assoc($resultbdd);

			if (!empty($rel1['email_cc'])) {
				$umix = explode(",", $rel1['email_cc']);
				$umix = array_push($umix, $cur_user_id);
				$uid = implode(",", $umix);
			} else {
				$uid = $cur_user_id;
			}

			$querybcc1 = "select GROUP_CONCAT(common_email_id SEPARATOR ';') as email_cc from users where user_id in (" . $uid . ")";
			$resultbdd1 = $dbcon->query($querybcc1);
			$rel11 = brp_mysqli_fetch_assoc($resultbdd1);

			$querybcc2 = "select GROUP_CONCAT(common_email_id SEPARATOR ";
			") as email_bcc from users where user_id in (" . $rel1['email_bcc'] . ")";
			$resultbdd2 = $dbcon->query($querybcc2);
			$rel12 = brp_mysqli_fetch_assoc($resultbdd2);

			// Amish Soni Start 18-01-2021
			$subject = $mail_template['email_subject'];
			$content = $mail_template['email_content'];

			$subject = replaceMergeFields($dbcon, $subject, $relcu['cust_id'], $module_id);
			$content = replaceMergeFields($dbcon, $content, $relcu['cust_id'], $module_id);
			// Amish Soni End 18-01-2021
			$getspecialConfiguration = getspecialConfiguration($dbcon);
			if ($getspecialConfiguration['umaboy_permission'] == 1) {
				$attach = array();
				$quot_file = umaboy_quotation_print($dbcon, $ins_quotation_id, 'Yes');
				array_push($attach, $quot_file);
				final_send_email($from_email_id, $to_email_id, $rel11['email_cc'], $rel12['email_bcc'], $subject, $content, $attach);
				unlink('../../../view/upload/mail_attach/' . $quot_file);
			}
		}
	} else {
		$getapprovalsetting = get_userwise_approval_setting($dbcon, 1, $_SESSION['user_id']);
		if (($getapprovalsetting['amount'] >= $POST['g_total']) && ($getapprovalsetting['auto_approval'] == 1)) {
			get_automatic_quotation_approval($dbcon, $ins_quotation_id);

			$querycu = "select cust.cust_email,quo.user_id,quo.cust_id,quo.prev_quotation_id from tbl_quotation as quo left join tbl_customer as cust on cust.cust_id=quo.cust_id where quo.quotation_id=" . $ins_quotation_id;
			$resultcu = $dbcon->query($querycu);
			$relcu = brp_mysqli_fetch_assoc($resultcu);
			$to_email_id = $relcu['cust_email'];

			$cur_user_id = $relcu['user_id'];
			$cur_user = getUserDetailById($dbcon, $cur_user_id);
			$from_email_id = ($cur_user && $cur_user['common_email_id']) ? $cur_user['common_email_id'] : ADMIN_EMAIL;

			if (!empty($relcu['prev_quotation_id'])) {
				$queryst = "select email_sms_id from email_sms_template where task_id=20 and status=0 and company_id=" . $_SESSION['company_id'];
			} else {
				$queryst = "select email_sms_id from email_sms_template where task_id=21 and status=0 and company_id=" . $_SESSION['company_id'];
			}

			$resultst = $dbcon->query($queryst);
			$relst = brp_mysqli_fetch_assoc($resultst);

			$mail_template = getEmailSMSTemplateById($dbcon, $relst['email_sms_id']);
			$module_id = 2;

			if ($mail_template && $to_email_id) {

				$querybcc = "select email_cc,email_bcc from email_sms_template where email_sms_id=" . $relst['email_sms_id'];
				$resultbdd = $dbcon->query($querybcc);
				$rel1 = brp_mysqli_fetch_assoc($resultbdd);

				if (!empty($rel1['email_cc'])) {
					$umix = explode(",", $rel1['email_cc']);
					$umix = array_push($umix, $cur_user_id);
					$uid = implode(",", $umix);
				} else {
					$uid = $cur_user_id;
				}

				$querybcc1 = "select GROUP_CONCAT(common_email_id SEPARATOR ';') as email_cc from users where user_id in (" . $uid . ")";
				$resultbdd1 = $dbcon->query($querybcc1);
				$rel11 = brp_mysqli_fetch_assoc($resultbdd1);

				$querybcc2 = "select GROUP_CONCAT(common_email_id SEPARATOR ";
				") as email_bcc from users where user_id in (" . $rel1['email_bcc'] . ")";
				$resultbdd2 = $dbcon->query($querybcc2);
				$rel12 = brp_mysqli_fetch_assoc($resultbdd2);

				// Amish Soni Start 18-01-2021
				$subject = $mail_template['email_subject'];
				$content = $mail_template['email_content'];

				$subject = replaceMergeFields($dbcon, $subject, $relcu['cust_id'], $module_id);
				$content = replaceMergeFields($dbcon, $content, $relcu['cust_id'], $module_id);
				// Amish Soni End 18-01-2021
				$getspecialConfiguration = getspecialConfiguration($dbcon);
				if ($getspecialConfiguration['umaboy_permission'] == 1) {
					$attach = array();
					$quot_file = umaboy_quotation_print($dbcon, $ins_quotation_id, 'Yes');
					array_push($attach, $quot_file);
					final_send_email($from_email_id, $to_email_id, $rel11['email_cc'], $rel12['email_bcc'], $subject, $content, $attach);
					unlink('../../../view/upload/mail_attach/' . $quot_file);
				}
			}
		}
	}

	if ($ins_quotation_id) {
		$arr['msg'] = "1";
		//Insert LOG
		$log_entry = common_log_entry($dbcon, "quotation_add", 1, "tbl_quotation", $ins_quotation_id);
	} else {
		$arr['msg'] = "0";
	}
	echo json_encode($arr);
} else if (strtolower($POST['mode']) == "edit") {
	error_reporting(E_ALL);
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	if ($POST['cust_id']) {
		$query = $dbcon->query("SELECT tc.cust_name,tc.cust_mobile,tc.cust_gst,ca.c_add_location, ca.c_add_country, ca.c_add_state, ca.c_add_city
			FROM tbl_customer tc
			LEFT JOIN tbl_cust_address as ca ON ca.cust_id = tc.cust_id
			WHERE tc.cust_id = " . $POST['cust_id']);
		$cust_data = $query->fetch_assoc();
		$info['qt_company_name']	= $cust_data['cust_name'];
		$info['qt_com_mno']         = $cust_data['cust_mobile'];
		$info['qt_com_gstno']		= $cust_data['cust_gst'];
		$info['qt_com_addr']		= $cust_data['c_add_location'];
		$info['qt_add_country']		= $cust_data['c_add_country'];
		$info['qt_add_state']		= $cust_data['c_add_state'];
		$info['qt_add_city']		= $cust_data['c_add_city'];
	}

	$curncy_trn['currency_enable'] 	= 1;
	$curncy_trn['currency_id'] 		= $POST['currency_id'];
	$curncy_trn['currency_rate'] 	= $POST['currency_rate'];

	$info['quot_revise_type']		= $POST['quot_revise_type'];
	$info['quotation_date']			= date('Y-m-d', strtotime($POST['quotation_date']));
	$info['quotation_no']				= $POST['quotation_no'];
	$info['cust_id']				= $POST['cust_id'];
	$info['c_con_id']				= $POST['c_con_id'];
	$info['inquiry_id']				= $POST['inquiry_id'];
	$info['quot_subject']           = $POST['quot_subject'];
	$info['quot_type']				= $POST['quot_type'];
	$info['quotation_valid_date']	= date('Y-m-d', strtotime($POST['quotation_valid_date']));
	$info['inquiry_ref_date']		= date('Y-m-d', strtotime($POST['inquiry_ref_date']));
	$info['quotation_ref']          = $POST['quotation_ref'];
	$info['client_id']               = $POST['client_id'];
	$info['payment_terms']          = $POST['payment_terms'];
	$info['quot_remark']            = $_POST['quot_remark'];
	$info['install_type']			= $POST['install_type'];
	$info['production_up_to']		= $POST['production_up_to'];

	$info['delivery_type']			= $POST['delivery_type'];
	$info['delivery_date']			= date('Y-m-d', strtotime($POST['quo_delivery_date']));

	$querypayt = 'select payment_days,payment_terms from pay_terms where terms_id=' . $POST['payment_terms_id'];

	$resultpayt = $dbcon->query($querypayt);
	$rowpayt = brp_mysqli_fetch_array($resultpayt);

	$info['payment_tems']			= $rowpayt['payment_days'];
	$info['payment_terms_id']		= $POST['payment_terms_id'];
	$info['mode_of_dispatch']		= $POST['mode_of_dispatch'];
	$info['destination']			= $POST['destination'];

	if ($getspecialConfiguration['jainflex_permission'] == 1) {
		$info['payment_tems_jainflex']		= $POST['payment_tems_jainflex'];
	}

	$info['transid']                        = $POST['transid'];
	$info['trans_add']                      = $POST['trans_add'];

	//$info['payment_tems_apson']                      = $POST['payment_tems_apson'];
	$info['delivary_time_apson']                      = $POST['delivary_time_apson'];

	/*$info['currency_id']            = (isset($POST['currency_enable'])) ? $POST['currency_id'] : $_SESSION['company_id'] ;
	$info['currency_enable']		= $POST['currency_enable'];
	$info['currency_rate']			= (isset($POST['currency_enable'])) ? $POST['currency_rate'] : 1 ;*/
	/*var_dump($_POST['an_id']);*/
	// var_dump($_POST['anx_id']);
	$explo = explode(",", $_POST['anx_id']);
	$an_id = implode(',', array_map('quote', $explo));
	/*var_dump($an_id);*/
	/*var_dump($an_id);*/
	$an_id1 = implode(',', $explo);
	/*var_dump($an_id1);*/
	/*var_dump($an_id1);*/
	$query_anex = 'select GROUP_CONCAT(an_id ORDER BY FIND_IN_SET(an_name,"' . $an_id1 . '")) as an_id from tbl_annexure where an_name IN (' . $an_id . ')';

	$result_anex = $dbcon->query($query_anex);
	$row_anex = brp_mysqli_fetch_array($result_anex);

	$info['an_id']					= $row_anex['an_id'];
	//var_dump($info['an_id']);exit;
	/*$info['an_id']					= implode(",",$POST['an_id']);*/
	$info['quatation_greeting']		= $_POST['quatation_greeting'];
	$info['quot_annex_content']		= $POST['quot_annex_content'];
	$info['quot_address']			= $POST['quot_address'];
	$info['with_bom_flag']          = $POST['with_bom_flag'];
	$info['quot_header']			= $_POST['quot_header'];
	$info['quot_footer']			= $_POST['quot_footer'];
	$info['attach_part']			= $_POST['attach_part'];
	$info['project_name']			= $_POST['project_name'];

	$info['orange']					= $POST['orange'];
	$info['mfg']					= $POST['mfg'];
	$info['trading']				= $POST['trading'];
	$info['repairing']				= $POST['repairing'];
	$info['other']					= $POST['other'];

	/*$info['orange_total']		  =	$POST['orange_total'];	
	$info['mfg_total']			  = $POST['mfg_total'];
	$info['trading_total']		  = $POST['trading_total'];
	$info['repairing_total']		  = $POST['repairing_total'];
	$info['other_total']		      = $POST['other_total'];*/

	$info['terms_type']				= $POST['terms_type'];

	if ($POST['currency_id'] == $_SESSION['currency_id']) {
		$info['g_total']				= $POST['g_total'];
		$info['basic_total']			= $POST['total'];
		$info['basic_total_conv']		= $POST['total'] * $POST['currency_rate'];
		$info['g_total_conv']			= $POST['g_total'] * $POST['currency_rate'];
	} else {
		$info['g_total']				= $POST['g_total'] * $POST['currency_rate'];
		$info['basic_total']			= $POST['total'] * $POST['currency_rate'];
		$info['basic_total_conv']		= $POST['total'];
		$info['g_total_conv']			= $POST['g_total'];
	}

	$info['quot_general_terms_condition_content'] = $_POST['quot_general_terms_condition_content'];
	$info['quot_battery_limits_and_schedule_exclusion_content'] = $_POST['quot_battery_limits_and_schedule_exclusion_content'];
	$info['terms'] = isset($_POST['terms']) ? $_POST['terms'] : ''; //added by hardi
	$info['shipped_via'] = isset($_POST['shipped_via']) ? $_POST['shipped_via'] : ''; //added by hardi
	$info['order_no'] = isset($_POST['order_no']) ? $_POST['order_no'] : ''; //added by hardi
	$info['delivery_no'] = isset($_POST['delivery_no']) ? $_POST['delivery_no'] : ''; //added by hardi
	$info['delivery_from'] = isset($_POST['delivery_from']) ? $_POST['delivery_from'] : ''; //added by hardi
	$info['po_address_to'] = isset($_POST['po_address_to']) ? $_POST['po_address_to'] : ''; //added by hardi
	$info['cdate']			= date("Y-m-d H:i:s");
	$info['user_id']		= $_SESSION['user_id'];
	$info['company_id']		= $_SESSION['company_id'];
	$updateid = update_record('tbl_quotation', array_merge($info, $curncy_trn), "quotation_id=" . $POST['eid'], $dbcon, $branch_id);


	/* Terms and Condition Start */
	$deltrmid = delete_record('tbl_quotation_terms_trn', "quotation_id=" . $POST['eid'], $dbcon, $branch_id);
	foreach ($POST['tc_id'] as $key => $name) {
		$infotrm['tc_id']		= $POST['tc_id'][$key];
		$infotrm['ref_tc_id']		= $POST['ref_tc_id'][$key];
		$infotrm['tc_priority']		= $POST['tc_priority'][$key];
		$infotrm['tc_details']		= $POST['tc_details'][$key];
		$infotrm['quotation_id']	= $POST['eid'];
		$infotrm['cdate']		= date("Y-m-d H:i:s");
		if (in_array($POST['tc_id'][$key], $POST['disp_term_flag'])) {
			$insertrmid = add_record('tbl_quotation_terms_trn', $infotrm, $dbcon, $branch_id);
		}
	}

	if (strtolower($POST['delivery_type']) == "po_wise") {
		$sel_pro_rate = "select * from tbl_quotation_trn where quot_trn_status=0 and quotation_id=" . $POST['eid'];
		$sel_pro_rate_rs = $dbcon->query($sel_pro_rate);

		while ($sel_pro_rate_rel = brp_mysqli_fetch_assoc($sel_pro_rate_rs)) {
			$delivery_da = "select * from tbl_quotation_delivery_date as mst 
			where mst.quo_delivery_date_status=0  and mst.quot_trn_id=" . $sel_pro_rate_rel['quot_trn_id'];
			$delivery_dae = $dbcon->query($delivery_da);
			if (brp_mysqli_num_rows($delivery_dae) > 0) {
				$inftrn11d['delivery_date'] = date('Y-m-d', strtotime($POST['delivery_date']));
				$updatetrnid = update_record('tbl_quotation_delivery_date', $inftrn11d, "quo_delivery_date_status=0 and quot_trn_id=" . $sel_pro_rate_rel['quot_trn_id'], $dbcon, $branch_id);
			} else {
				if ($sel_pro_rate_rel['unit_id'] === $sel_pro_rate_rel['rate_unit']) {
					$sqty = $sel_pro_rate_rel['product_qty'];
				} else {
					$sqty = $sel_pro_rate_rel['product_conv_qty'];
				}
				$infodeli['quot_trn_id'] 			= $sel_pro_rate_rel['quot_trn_id'];
				$infodeli['delivery_date'] 			= date('Y-m-d', strtotime($POST['quo_delivery_date']));
				$infodeli['product_qty'] 			= $sqty;
				$infodeli['unit_id'] 				= $sel_pro_rate_rel['rate_unit'];

				$infodeli['user_id']				= $_SESSION['user_id'];
				$infodeli['cdate']					= date("Y-m-d h:i:s");
				$infodeli['company_id']				= $_SESSION['company_id'];

				$inser_del = add_record('tbl_quotation_delivery_date', $infodeli, $dbcon, $branch_id);
			}
		}
	}
	/* Terms and Condition End */

	//Update quotation id in budget TRN data-original-title
	/*$upd_qt_id_qry="update `tbl_quot_budget_trn` set quotation_id=".$POST['eid']." where find_in_set(quot_trn_id,(select group_concat(DISTINCT quot_trn_id) from tbl_quotation_trn where quotation_id=".$POST['eid']."))";
		$upd_qt_id_qry_rs=$dbcon->query($upd_qt_id_qry);*/

	if ($updateid) {
		$arr['msg'] = "update";
		//Insert LOG
		$log_entry = common_log_entry($dbcon, "quotation_add", 2, "tbl_quotation", $POST['eid']);
	} else {
		$arr['msg'] = 0;
	}
	echo json_encode($arr);
} else if (strtolower($POST['mode']) == "delete") {
	$info['quotation_status']	= 2;
	$updateid = update_record('tbl_quotation', $info, "quotation_id=" . $POST['quotation_id'], $dbcon);

	$infotrn['quot_trn_status']	= 2;
	$updatetrnid = update_record('tbl_quotation_trn', $infotrn, "quotation_id=" . $POST['quotation_id'], $dbcon);

	$infoprojecttrn['quotation_projecttrn_status']  = 2;
	$updateprojecttrnid = update_record('tbl_quotation_project_trn', $infoprojecttrn, "quotation_id=" . $POST['quotation_id'], $dbcon);

	//Update Prev quotation ID
	$prev_quotation_id = $POST['prev_quotation_id'];

	if ($prev_quotation_id) {
		$upd_prev_qt_sts = $dbcon->query("update tbl_quotation set revise_status=0 where quotation_id=" . $prev_quotation_id);
	}

	//Insert LOG
	$log_entry = common_log_entry($dbcon, "quotation_add", 3, "tbl_quotation", $POST['quotation_id']);

	if ($updateid)
		echo "1";
	else
		echo "0";
} else if (strtolower($POST['mode']) == "add_gst_for_all_product") {
	$company_state = get_company_data($dbcon, $_SESSION['company_id']);
	//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);


	$where  = "  q.quot_trn_status = 3 and q.quotation_id = 0";

	if (!empty($POST['edit_id'])) {
		$where  = "  q.quot_trn_status = 0 and q.quotation_id = " . $POST['edit_id'];
	}

	$query = "SELECT q.*,pro.product_hsn FROM tbl_quotation_trn as q left join product_mst as pro ON pro.product_id = q.product_id where " . $where;
	$result = $dbcon->query($query);

	while ($row = brp_mysqli_fetch_assoc($result)) {
		$sale_gst = get_tax_cat_by_hsn($dbcon, $POST['product_hsn']);
		if ($POST['gst_type'] == 3) {
			$sale_gst['tax_gst'] = 0.1;
			$sale_gst['tax_cat_id'] = 0;
		} else if ($POST['gst_type'] == 4) {
			$sale_gst['tax_gst'] = 0;
			$sale_gst['tax_cat_id'] = 0;
		} else if ($POST['gst_type'] == 5) {
			$sale_gst['tax_gst'] = 5;
			$sale_gst['tax_cat_id'] = 0;
		} else if ($POST['gst_type'] == 6) {
			$sale_gst['tax_gst'] = 12;
			$sale_gst['tax_cat_id'] = 0;
		} else if ($POST['gst_type'] == 7) {
			$sale_gst['tax_gst'] = 18;
			$sale_gst['tax_cat_id'] = 0;
		} else if ($POST['gst_type'] == 8) {
			$sale_gst['tax_gst'] = 24;
			$sale_gst['tax_cat_id'] = 0;
		} else {
			$sale_gst = get_tax_cat_by_hsn($dbcon, trim($row['product_hsn']));
		}

		$cgst_tax_rate = 0;
		$cgst_tax_rate_conv = 0;
		$sgst_tax_rate = 0;
		$sgst_tax_rate_conv = 0;
		$igst_tax_rate = 0;
		$igst_tax_rate_conv = 0;
		if (($company_state['stateid'] == $POST['cust_stateid'])) {
			$gst = $sale_gst['tax_gst'] / 2;
			$cgst_tax_per = $gst;
			$cgst_tax_rate = ($gst * $row['product_amount']) / 100;
			$cgst_tax_rate_conv = ($row['currency_rate'] * $gst * $row['product_amount']) / 100;
			$sgst_tax_per = $gst;
			$sgst_tax_rate = ($gst * $row['product_amount']) / 100;
			$sgst_tax_rate_conv = ($row['currency_rate'] * $gst * $row['product_amount']) / 100;
		} else {
			$igst_tax_per = $sale_gst['tax_gst'];
			$igst_tax_rate = ($sale_gst['tax_gst'] * $row['product_amount']) / 100;
			$igst_tax_rate_conv = ($row['currency_rate'] * $sale_gst['tax_gst'] * $row['product_amount']) / 100;
		}

		$info = get_product_common_tax($dbcon, $row['product_amount'], $row['formulaid']);

		$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0;
		$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0;
		$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0;

		if ($row['currency_id'] == $company_state['currency_id']) {
			$info1['product_rate']			= $row['product_rate'];
			$info1['product_discount']		= $row['product_discount'];
			$info1['product_amount']		= $row['product_amount'];
			$info1['cgst_tax_rate']			= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
			$info1['sgst_tax_rate']			= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
			$info1['igst_tax_rate']			= isset($igst_tax_rate) ? $igst_tax_rate : 0;
			$info1['product_total']			= $row['product_amount'];

			$info1['product_rate_conv']		= $row['product_rate'] * $row['currency_rate'];
			$info1['product_amount_conv']	= $row['product_amount'] * $row['currency_rate'];
			$info1['product_discount_conv']	= $row['product_discount'] * $row['currency_rate'];
			$info1['cgst_tax_rate_conv']	= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
			$info1['sgst_tax_rate_conv']	= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
			$info1['igst_tax_rate_conv']	= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;
			$info1['product_total_conv']	= $row['product_amount'] * $row['currency_rate'];
		} else {
			$info1['product_rate']			= $row['product_rate'] * $row['currency_rate'];
			$info1['product_discount']		= $row['product_discount'] * $row['currency_rate'];;
			$info1['product_amount']		= $row['product_amount'] * $row['currency_rate'];
			$info1['cgst_tax_rate']			= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
			$info1['sgst_tax_rate']			= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
			$info1['igst_tax_rate']			= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;
			$info1['product_total']			= $row['product_amount'] * $row['currency_rate'];

			$info1['product_rate_conv']		= $row['product_rate'];
			$info1['product_amount_conv']	= $row['product_amount'];
			$info1['product_discount_conv']	= $row['product_discount'];
			$info1['cgst_tax_rate_conv']	= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
			$info1['sgst_tax_rate_conv']	= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
			$info1['igst_tax_rate_conv']	= isset($igst_tax_rate) ? $igst_tax_rate : 0;
			$info1['product_total_conv']	= $row['product_amount'];
		}

		//var_dump($info1);

		$updateid = update_record('tbl_quotation_trn', $info1, "quot_trn_id=" . $row['quot_trn_id'], $dbcon, $branch_id);
		if (!empty($POST['edit_id'])) {
			$info['gst_type'] = $POST['gst_type'];
			$updateid = update_record('tbl_quotation', $info, "quotation_id=" . $POST['edit_id'], $dbcon);
		}
	}
} else if (strtolower($POST['mode']) == "add_field") {

	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$company_state = get_company_data($dbcon, $_SESSION['company_id']);
	//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
	$sale_gst = get_tax_cat_by_hsn($dbcon, $POST['product_hsn_code']);

	if ($POST['gst_type'] == 3) {
		$sale_gst['tax_gst'] = 0.1;
		$sale_gst['tax_cat_id'] = 0;
	} else if ($POST['gst_type'] == 4) {
		$sale_gst['tax_gst'] = 0;
		$sale_gst['tax_cat_id'] = 0;
	} else if ($POST['gst_type'] == 5) {
		$sale_gst['tax_gst'] = 5;
		$sale_gst['tax_cat_id'] = 0;
	} else if ($POST['gst_type'] == 6) {
		$sale_gst['tax_gst'] = 12;
		$sale_gst['tax_cat_id'] = 0;
	} else if ($POST['gst_type'] == 7) {
		$sale_gst['tax_gst'] = 18;
		$sale_gst['tax_cat_id'] = 0;
	} else if ($POST['gst_type'] == 8) {
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
	if (($company_state['stateid'] == $POST['cust_stateid'])) {
		$gst = $sale_gst['tax_gst'] / 2;
		$cgst_tax_per = $gst;
		$cgst_tax_rate = ($gst * $POST['product_amount']) / 100;
		$cgst_tax_rate_conv = ($POST['currency_rate'] * $gst * $POST['product_amount']) / 100;
		$sgst_tax_per = $gst;
		$sgst_tax_rate = ($gst * $POST['product_amount']) / 100;
		$sgst_tax_rate_conv = ($POST['currency_rate'] * $gst * $POST['product_amount']) / 100;
	} else {
		$igst_tax_per = $sale_gst['tax_gst'];
		$igst_tax_rate = ($sale_gst['tax_gst'] * $POST['product_amount']) / 100;
		$igst_tax_rate_conv = ($POST['currency_rate'] * $sale_gst['tax_gst'] * $POST['product_amount']) / 100;
	}

	$info1['inquiry_type']	= $POST['inquiry_type'];
	$info1['project_wise']	= ($POST['inquiry_type'] == 2) ? 1 : 0;
	$info1['product_id']	= $POST['product_id'];
	$info1['cat_id']	    = $POST['cat_id'];
	$info1['rcat_id']	    = $POST['rcat_id'];
	$info1['product_desc']	= text_rnremove($_POST['product_desc']);
	$info1['product_spec']	= text_rnremove($_POST['product_spec']);
	$info1['product_spec_id'] =  implode(",", $_POST['specification']);
	$info1['product_other_desc']	= text_rnremove($_POST['product_other_desc']);
	$info1['level_id']		= $POST['level_id'];

	$info1['unitid']		= $POST['unitid'];
	$info1['product_qty']	= $POST['product_qty'];

	$info1['unit_wise']					 = $POST['unit_wise'];
	$info1['product_conv_qty']           = $POST['product_conv_qty'];
	$info1['conv_unit_id']               = $POST['conv_unit_id'];
	$info1['rate_unit']                  = $POST['rate_unit'];

	$info1['act_amt_flag']	= $POST['act_amt_flag'];
	$info1['discount_per']	= $POST['discount_per'];
	$info1['formulaid']		= $POST['formulaid'];
	//$info1['product_total']= $POST['product_total'];
	$info = get_product_common_tax($dbcon, $POST['product_amount'], $POST['formulaid']);
	$info1 = array_merge($info1, $info);
	$info1['user_id']		= $_SESSION['user_id'];
	$info1['company_id']	= $_SESSION['company_id'];
	$info1['currency_id']	= $POST['currency_id'];
	$info1['currency_rate'] = $POST['currency_rate'];

	$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0;
	$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0;
	$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0;

	if ($POST['currency_id'] == $company_state['currency_id']) {
		$info1['product_rate']			= $POST['product_rate'];
		$info1['product_discount']		= $POST['product_discount'];
		$info1['product_amount']		= $POST['product_amount'];
		$info1['cgst_tax_rate']			= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
		$info1['sgst_tax_rate']			= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
		$info1['igst_tax_rate']			= isset($igst_tax_rate) ? $igst_tax_rate : 0;
		$info1['product_total']			= $POST['product_amount'];

		$info1['product_rate_conv']		= $POST['product_rate'] * $POST['currency_rate'];
		$info1['product_amount_conv']	= $POST['product_amount'] * $POST['currency_rate'];
		$info1['product_discount_conv']	= $POST['product_discount'] * $POST['currency_rate'];
		$info1['cgst_tax_rate_conv']	= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
		$info1['sgst_tax_rate_conv']	= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
		$info1['igst_tax_rate_conv']	= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;
		$info1['product_total_conv']	= $POST['product_amount'] * $POST['currency_rate'];
	} else {
		$info1['product_rate']			= $POST['product_rate'] * $POST['currency_rate'];
		$info1['product_discount']		= $POST['product_discount'] * $POST['currency_rate'];;
		$info1['product_amount']		= $POST['product_amount'] * $POST['currency_rate'];
		$info1['cgst_tax_rate']			= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
		$info1['sgst_tax_rate']			= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
		$info1['igst_tax_rate']			= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;
		$info1['product_total']			= $POST['product_amount'] * $POST['currency_rate'];

		$info1['product_rate_conv']		= $POST['product_rate'];
		$info1['product_amount_conv']	= $POST['product_amount'];
		$info1['product_discount_conv']	= $POST['product_discount'];
		$info1['cgst_tax_rate_conv']	= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
		$info1['sgst_tax_rate_conv']	= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
		$info1['igst_tax_rate_conv']	= isset($igst_tax_rate) ? $igst_tax_rate : 0;
		$info1['product_total_conv']	= $POST['product_amount'];
	}

	$info1['orange']					= $POST['orange'];
	$info1['mfg']						= $POST['mfg'];
	$info1['trading']					= $POST['trading'];
	$info1['repairing']					= $POST['repairing'];
	$info1['other']						= $POST['other'];

	$info1['orange_total']		  =	$POST['orange_total'];
	$info1['mfg_total']			  = $POST['mfg_total'];
	$info1['trading_total']		  = $POST['trading_total'];
	$info1['repairing_total']		  = $POST['repairing_total'];
	$info1['other_total']		      = $POST['other_total'];

	if (isset($POST['item_no'])) {
		$info1['item_no']						= $POST['item_no'];
	}
	if (isset($POST['item_size'])) {
		$info1['item_size']						= $POST['item_size'];
	}
	if (isset($POST['item_class'])) {
		$info1['item_class']						= $POST['item_class'];
	}

	$info1['product_tax_cat']	= $sale_gst['tax_cat_id'];

	$table = 'tbl_quotation_trn';
	$tableid = 'quot_trn_id';
	if (!empty($POST['quotation_id'])) {
		$info1['quotation_id'] = $POST['quotation_id'];
	} else {
		$info1['quot_trn_status'] = 3;
	}


	if (empty($POST['edit_id'])) {
		
		$inserid = add_record($table, $info1, $dbcon, $branch_id);
		
		// JS : ADD DYNAMIC DATA FOR SALES ORDER TO PRODUCT SAVE
		$dynamic_data = $POST['dynamic_data'];

		$master_name_fields = [];
		foreach ($dynamic_data as $k => $dy_v) {
			$master_name_fields[$k] = $dy_v;
		}
		$master_name_fields['master_id']	= $inserid;
		$master_name_fields['master_type']	= "quotation";
		$master_name_fields['cdate']		= date("Y-m-d H:i:s");
		$master_name_fields['user_id']		= $_SESSION['user_id'];
		$master_name_fields['company_id']	= $_SESSION['company_id'];

		add_record('master_name_field', $master_name_fields, $dbcon, $branch_id);

		$tax_trn_id = $inserid;

		$inq_qry = "select tiat.*,pm.product_base_unit,pm.product_spec,pm.product_spec_id,pm.product_hsn from tbl_quto_access_trn as tiat left join product_mst as pm on pm.product_id=tiat.product_id where tiat.inq_access_status=3 and tiat.pid=" . $POST['product_id'] . " and tiat.company_id=" . $_SESSION['company_id'] . " and tiat.user_id=" . $_SESSION['user_id'] . "";
		$inq_qry_rs = $dbcon->query($inq_qry);

		while ($inq_rel = mysqli_fetch_assoc($inq_qry_rs)) {
			/* $inq_qry="select * from tbl_quotation_trn  where  quot_trn_id=".$pid;
				$inq_qry_rs=$dbcon->query($inq_qry);
				$inq_rel=brp_mysqli_fetch_assoc($inq_qry_rs);
						
				$inq_unit="select product_base_unit,product_spec,product_spec_id,product_hsn from product_mst  where  product_id=".$POST['product_id'];
						
				$inq_unit_rs=$dbcon->query($inq_unit);

				$inq_rel_unit=brp_mysqli_fetch_assoc($inq_unit_rs); */
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$company_state = get_company_data($dbcon, $_SESSION['company_id']);
			//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
			//$sale_gst = get_tax_cat_by_hsn($dbcon,$POST['product_hsn_code']);

			if ($POST['gst_type'] == 3) {
				$sale_gst['tax_gst'] = 0.1;
				$sale_gst['tax_cat_id'] = 0;
			} else if ($POST['gst_type'] == 4) {
				$sale_gst['tax_gst'] = 0;
				$sale_gst['tax_cat_id'] = 0;
			} else if ($POST['gst_type'] == 5) {
				$sale_gst['tax_gst'] = 5;
				$sale_gst['tax_cat_id'] = 0;
			} else if ($POST['gst_type'] == 6) {
				$sale_gst['tax_gst'] = 12;
				$sale_gst['tax_cat_id'] = 0;
			} else if ($POST['gst_type'] == 7) {
				$sale_gst['tax_gst'] = 18;
				$sale_gst['tax_cat_id'] = 0;
			} else if ($POST['gst_type'] == 8) {
				$sale_gst['tax_gst'] = 24;
				$sale_gst['tax_cat_id'] = 0;
			} else {
				$sale_gst = get_tax_cat_by_hsn_id($dbcon, trim($inq_rel['product_hsn']));
			}

			$cgst_tax_rate = 0;
			$cgst_tax_rate_conv = 0;
			$sgst_tax_rate = 0;
			$sgst_tax_rate_conv = 0;
			$igst_tax_rate = 0;
			$igst_tax_rate_conv = 0;
			if (($company_state['stateid'] == $POST['cust_stateid'])) {
				$gst = $sale_gst['tax_gst'] / 2;
				$cgst_tax_per = $gst;
				$cgst_tax_rate = ($gst * $inq_rel['acc_amount']) / 100;
				$cgst_tax_rate_conv = ($POST['currency_rate'] * $gst * $inq_rel['acc_amount']) / 100;
				$sgst_tax_per = $gst;
				$sgst_tax_rate = ($gst * $inq_rel['acc_amount']) / 100;
				$sgst_tax_rate_conv = ($POST['currency_rate'] * $gst * $inq_rel['acc_amount']) / 100;
			} else {
				$igst_tax_per = $sale_gst['tax_gst'];
				$igst_tax_rate = ($sale_gst['tax_gst'] * $inq_rel['acc_amount']) / 100;
				$igst_tax_rate_conv = ($POST['currency_rate'] * $sale_gst['tax_gst'] * $inq_rel['acc_amount']) / 100;
			}

			$info1['inquiry_type']	= $POST['inquiry_type'];
			$info1['project_wise']	= ($POST['inquiry_type'] == 2) ? 1 : 0;
			$info1['pid']			= $inserid;
			$info1['product_id']	= $inq_rel['product_id'];
			//$info1['cat_id']	    = $POST['cat_id'];
			$info1['product_desc']	= text_rnremove($inq_rel['product_desc']);
			$info1['product_spec']	= text_rnremove($inq_rel['product_spec']);
			$info1['product_spec_id'] =  $inq_rel['product_spec_id'];
			//$info1['product_other_desc']	= text_rnremove($_POST['product_other_desc']);
			//$info1['level_id']		= $POST['level_id'];
			$info1['unitid']		= $inq_rel['product_base_unit'];
			//$info1['act_amt_flag']	= $POST['act_amt_flag'];
			$info1['product_qty']	= $inq_rel['qty'];
			//$info1['discount_per']	= $POST['discount_per'];
			//$info1['formulaid']= $POST['formulaid'];
			//$info1['product_total']= $POST['product_total'];
			$info = get_product_common_tax($dbcon, $inq_rel['acc_amount'], '');
			$info1 = array_merge($info1, $info);
			$info1['user_id']		= $_SESSION['user_id'];
			$info1['company_id']	= $_SESSION['company_id'];
			$info1['currency_id']	= $POST['currency_id'];
			$info1['currency_rate'] = $POST['currency_rate'];

			$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0;
			$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0;
			$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0;

			if ($POST['currency_id'] == $company_state['currency_id']) {
				$info1['product_rate']			= $inq_rel['acce_rate'];
				//$info1['product_discount']		= $POST['product_discount'];
				$info1['product_amount']		= $inq_rel['acc_amount'];
				$info1['cgst_tax_rate']			= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
				$info1['sgst_tax_rate']			= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
				$info1['igst_tax_rate']			= isset($igst_tax_rate) ? $igst_tax_rate : 0;
				$info1['product_total']			= $inq_rel['acc_amount'];

				$info1['product_rate_conv']		= $inq_rel['acce_rate'] * $POST['currency_rate'];
				$info1['product_amount_conv']	= $inq_rel['acc_amount'] * $POST['currency_rate'];
				//$info1['product_discount_conv']	= $POST['product_discount']*$inq_rel['currency_rate'];
				$info1['cgst_tax_rate_conv']	= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
				$info1['sgst_tax_rate_conv']	= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
				$info1['igst_tax_rate_conv']	= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;
				$info1['product_total_conv']	= $inq_rel['acc_amount'] * $POST['currency_rate'];
			} else {
				$info1['product_rate']			= $inq_rel['acce_rate'] * $POST['currency_rate'];
				//$info1['product_discount']		= $inq_rel['product_discount']*$POST['currency_rate'];;
				$info1['product_amount']		= $inq_rel['product_amount'] * $POST['currency_rate'];
				$info1['cgst_tax_rate']			= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
				$info1['sgst_tax_rate']			= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
				$info1['igst_tax_rate']			= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;
				$info1['product_total']			= $inq_rel['acc_amount'] * $POST['currency_rate'];

				$info1['product_rate_conv']		= $inq_rel['acce_rate'];
				$info1['product_amount_conv']	= $inq_rel['acc_amount'];
				//$info1['product_discount_conv']	= $POST['product_discount'];
				$info1['cgst_tax_rate_conv']	= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
				$info1['sgst_tax_rate_conv']	= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
				$info1['igst_tax_rate_conv']	= isset($igst_tax_rate) ? $igst_tax_rate : 0;
				$info1['product_total_conv']	= $inq_rel['acc_amount'];
			}


			$info1['product_tax_cat']	= $sale_gst['tax_cat_id'];
			// var_dump($info1);
			$table = 'tbl_quotation_trn';
			$tableid = 'quot_trn_id';
			if (!empty($POST['quotation_id'])) {
				$info1['quotation_id'] = $POST['quotation_id'];
			} else {
				$info1['quot_trn_status'] = 3;
			}
			//var_dump($info1);
			$inserid_acc = add_record($table, $info1, $dbcon, $branch_id);
		}

		$deleteid = delete_record('tbl_quto_access_trn', "pid=" . $POST['product_id'] . " and inq_access_status = 3 and user_id='" . $_SESSION['user_id'] . "' and company_id='" . $_SESSION['company_id'] . "'", $dbcon);

		///////////////////////////////////////////Harshil 4-7-2022////////////////////////////////////////////////////////////////

		$inq_qry = "select * from tbl_project_assigntrn where project_assigntrn_status=0 and project_assign_id=" . $POST['product_id'];

		$inq_qry_rs = $dbcon->query($inq_qry);

		while ($inq_rel = mysqli_fetch_assoc($inq_qry_rs)) {
			$t_Qty = ($inq_rel['product_qty'] * $POST['product_qty']);
			$t_amount = ($t_Qty * $inq_rel['product_rate']);

			$company_state = get_company_data($dbcon, $_SESSION['company_id']);

			$sale_gst = get_tax_cat_by_hsn_id($dbcon, $inq_rel['product_hsn_code']);

			$cgst_tax_rate = 0;
			$sgst_tax_rate = 0;
			$igst_tax_rate = 0;
			if (($company_state['stateid'] == $POST['cust_stateid'])) {
				$gst = $sale_gst['tax_gst'] / 2;
				$cgst_tax_per = $gst;
				$cgst_tax_rate = ($gst * $t_amount) / 100;
				$sgst_tax_per = $gst;
				$sgst_tax_rate = ($gst * $t_amount) / 100;
				$t_g_amount = ($t_amount + $cgst_tax_rate + $sgst_tax_rate);
			} else {
				$igst_tax_per = $sale_gst['tax_gst'];
				$igst_tax_rate = ($sale_gst['tax_gst'] * $t_amount) / 100;
				$t_g_amount = ($t_amount + $igst_tax_rate);
			}
			$info12['inquiry_id']			= $POST['inquiry_id'];

			if (!empty($POST['quotation_id'])) {
				$info12['quotation_id'] = $POST['quotation_id'];
			} else {
				$info12['quotation_projecttrn_status'] = 3;
			}


			$info12['quotation_trn_id']		= $inserid;
			$info12['inquiry_type']			= $POST['inquiry_type'];
			$info12['project_assign_id']	= $POST['product_id'];
			$info12['product_category_id']	= 0;
			$info12['product_id']			= $inq_rel['product_id'];
			$info12['description']			= $inq_rel['description'];
			$info12['product_hsn_code']		= $inq_rel['product_hsn_code'];
			$info12['product_qty']			= $t_Qty;
			$info12['product_rate']			= $inq_rel['product_rate'];
			$info12['product_amount']    	= $t_amount;
			$info12['formulaid']         	= $inq_rel['formulaid'];
			$info12['product_disc']			= $inq_rel['product_disc'];
			$info12['product_spec']			= $inq_rel['product_spec'];
			//$info=get_product_common_tax($dbcon,$t_amount,$info12['formulaid']);
			//$info12=array_merge($info12,$info);

			$info12['user_id']				= $_SESSION['user_id'];
			$info12['company_id']			= $_SESSION['company_id'];
			$info12['cgst_tax_per']			= isset($cgst_tax_per) ? $cgst_tax_per : 0;
			$info12['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
			$info12['sgst_tax_per']			= isset($sgst_tax_per) ? $sgst_tax_per : 0;
			$info12['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
			$info12['igst_tax_per']			= isset($igst_tax_per) ? $igst_tax_per : 0;
			$info12['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0;
			$info12['product_total']		= $t_g_amount;
			$inserid_sub = add_record("tbl_quotation_project_trn", $info12, $dbcon, $branch_id);
		}
		///////////////////////////////////////////Harshil 4-7-2022////////////////////////////////////////////////////////////////
	} else {
		$updateid = update_record($table, $info1, $tableid . "=" . $POST['edit_id'], $dbcon, $branch_id);

		// Update DYNAMIC DATA FOR SALES ORDER TO PRODUCT SAVE
		$dynamic_data = $POST['dynamic_data'];

		$master_name_fields = [];
		foreach ($dynamic_data as $k => $dy_v) {
			$master_name_fields[$k] = $dy_v;
		}
		$master_name_fields['master_id']	= $POST['edit_id'];
		$master_name_fields['master_type']	= "quotation";
		$master_name_fields['cdate']		= date("Y-m-d H:i:s");
		$master_name_fields['user_id']		= $_SESSION['user_id'];
		$master_name_fields['company_id']	= $_SESSION['company_id'];


		$qry = "select * from master_name_field where master_type='quotation' and master_id=" . $POST['edit_id'];

		$result = $dbcon->query($qry);
		$num_row = mysqli_num_rows($result);
		if ($num_row > 0) {
			update_record('master_name_field', $master_name_fields, "master_type='quotation' and master_id=" . $POST['edit_id'], $dbcon, $branch_id);
		} else {
			add_record('master_name_field', $master_name_fields, $dbcon, $branch_id);
		}

		$tax_trn_id = $POST['edit_id'];

		$inquiry_id = $POST['inquiry_id'];
		$deletpro = delete_record('tbl_quotation_project_trn', "inquiry_id='" . $inquiry_id . "' and user_id=" . $_SESSION['user_id'], $dbcon);

		///////////////////////////////////////////Harshil 4-7-2022////////////////////////////////////////////////////////////////

		$inq_qry = "select * from tbl_project_assigntrn where project_assigntrn_status=0 and project_assign_id=" . $POST['product_id'];

		$inq_qry_rs = $dbcon->query($inq_qry);

		while ($inq_rel = mysqli_fetch_assoc($inq_qry_rs)) {
			$t_Qty = ($inq_rel['product_qty'] * $POST['product_qty']);
			$t_amount = ($t_Qty * $inq_rel['product_rate']);

			$company_state = get_company_data($dbcon, $_SESSION['company_id']);

			$sale_gst = get_tax_cat_by_hsn_id($dbcon, $inq_rel['product_hsn_code']);

			$cgst_tax_rate = 0;
			$sgst_tax_rate = 0;
			$igst_tax_rate = 0;
			if (($company_state['stateid'] == $POST['cust_stateid'])) {
				$gst = $sale_gst['tax_gst'] / 2;
				$cgst_tax_per = $gst;
				$cgst_tax_rate = ($gst * $t_amount) / 100;
				$sgst_tax_per = $gst;
				$sgst_tax_rate = ($gst * $t_amount) / 100;
				$t_g_amount = ($t_amount + $cgst_tax_rate + $sgst_tax_rate);
			} else {
				$igst_tax_per = $sale_gst['tax_gst'];
				$igst_tax_rate = ($sale_gst['tax_gst'] * $t_amount) / 100;
				$t_g_amount = ($t_amount + $igst_tax_rate);
			}
			$info12['inquiry_id']			= $POST['inquiry_id'];

			if (!empty($POST['quotation_id'])) {
				$info12['quotation_id'] = $POST['quotation_id'];
			} else {
				$info12['quotation_projecttrn_status'] = 3;
			}

			$info12['quotation_trn_id']		= $POST['edit_id'];
			$info12['inquiry_type']			= $POST['inquiry_type'];
			$info12['project_assign_id']	= $POST['product_id'];
			$info12['product_category_id']	= 0;
			$info12['product_id']			= $inq_rel['product_id'];
			$info12['description']			= $inq_rel['description'];
			$info12['product_hsn_code']		= $inq_rel['product_hsn_code'];
			$info12['product_qty']			= $t_Qty;
			$info12['product_rate']			= $inq_rel['product_rate'];
			$info12['product_amount']    	= $t_amount;
			$info12['formulaid']         	= $inq_rel['formulaid'];
			$info12['product_disc']			= $inq_rel['product_disc'];
			$info12['product_spec']			= $inq_rel['product_spec'];
			//$info=get_product_common_tax($dbcon,$t_amount,$info12['formulaid']);
			//$info12=array_merge($info12,$info);

			$info12['user_id']				= $_SESSION['user_id'];
			$info12['company_id']			= $_SESSION['company_id'];
			$info12['cgst_tax_per']			= isset($cgst_tax_per) ? $cgst_tax_per : 0;
			$info12['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
			$info12['sgst_tax_per']			= isset($sgst_tax_per) ? $sgst_tax_per : 0;
			$info12['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
			$info12['igst_tax_per']			= isset($igst_tax_per) ? $igst_tax_per : 0;
			$info12['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0;
			$info12['product_total']		= $t_g_amount;
			$inserid_sub = add_record("tbl_quotation_project_trn", $info12, $dbcon, $branch_id);
		}
		///////////////////////////////////////////Harshil 4-7-2022////////////////////////////////////////////////////////////////
	}
	/* insert to tax transaction table by Dhruv */
	if (($cgst_tax_per != 0) && ($cgst_tax_rate != 0)) {
		$cl_id = get_ledger_by_name($dbcon, 'CGST');
		$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $cgst_tax_per, $cgst_tax_rate, $inserid, "tbl_quotation_trn", $POST['product_id'], 3, $inserid, $POST['branch_id'], $POST['currency_id'], $POST['currency_rate'], $cgst_tax_rate_conv);
	}
	if (($sgst_tax_per != 0) && ($sgst_tax_rate != 0)) {
		$cl_id = get_ledger_by_name($dbcon, 'SGST');
		$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $sgst_tax_per, $sgst_tax_rate, $inserid, "tbl_quotation_trn", $POST['product_id'], 3, $inserid, $POST['branch_id'], $POST['currency_id'], $POST['currency_rate'], $sgst_tax_rate_conv);
	}
	if (($igst_tax_per != 0) && ($igst_tax_rate != 0)) {
		$cl_id = get_ledger_by_name($dbcon, 'IGST');
		$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $igst_tax_per, $igst_tax_rate, $inserid, "tbl_quotation_trn", $POST['product_id'], 3, $inserid, $POST['branch_id'], $POST['currency_id'], $POST['currency_rate'], $igst_tax_rate_conv);
	}

	// check for the addiotional tax on product Start -- dhaval
	$pro_amt = $POST['product_amount'] * $POST['currency_rate'];
	$count_add_tax = get_check_addition_tax($dbcon, $sale_gst['tax_cat_id'], $POST['product_amount'], $inserid, $POST['product_id'], $inserid, $POST['branch_id'], 'tbl_quotation_trn', $POST['currency_id'], $POST['currency_rate'], $pro_amt);

	// check for the addiotional tax on product End  -- dhaval


	//////////////Maulik Add Delivery Date//////////////////////////////////////////
	$d_id = array();

	if (strtolower($POST['delivery_type']) == "product_wise") {
		$total_delivery_qty = $POST['total_delivery_qty'];
		$delivery_date = $POST['delivery_date'];
		$arry_edit = $POST['arry_edit'];
		for ($i = 0; $i < count($total_delivery_qty); $i++) {
			$info_dil['quot_trn_id']			= $tax_trn_id;
			$info_dil['delivery_date']			= date('Y-m-d', strtotime($delivery_date[$i]));
			$info_dil['product_qty']			= $total_delivery_qty[$i];
			$info_dil['unit_id']				= $POST['unit_wise'];

			$info_dil['user_id']		= $_SESSION['user_id'];
			$info_dil['cdate']			= date("Y-m-d h:i:s");
			$info_dil['company_id']		= $_SESSION['company_id'];
			//$info_dil['branch_id']		=$_SESSION['company_id'];
			//var_dump($info);

			$table_k = 'tbl_quotation_delivery_date';
			$tableid_k = 'quo_delivery_date_id';

			if (!empty($arry_edit[$i])) {
				$updateid_k = update_record($table_k, $info_dil, "quo_delivery_date_id=" . $arry_edit[$i], $dbcon, $branch_id);
				array_push($d_id, $arry_edit[$i]);
			} else {
				$inserid_k = add_record($table_k, $info_dil, $dbcon, $branch_id);
				array_push($d_id, $inserid_k);
			}
		}
	} else {
		$query_dd = "select * from tbl_quotation_delivery_date as mst 
				where mst.quot_trn_id=" . $tax_trn_id . " order by quo_delivery_date_id desc";
		$row_dd = $dbcon->query($query_dd);
		$rel_dd = brp_mysqli_fetch_assoc($row_dd);

		if ($info1['unit_id'] === $info1['rate_unit']) {
			$sqty = $info1['product_qty'];
		} else {
			$sqty = $info1['product_conv_qty'];
		}
		$info_dil['quot_trn_id']			= $tax_trn_id;
		$info_dil['delivery_date']			= date('Y-m-d', strtotime($POST['quo_delivery_date']));
		$info_dil['product_qty']			= $sqty;
		$info_dil['unit_id']				= $info1['rate_unit'];

		$info_dil['user_id']		= $_SESSION['user_id'];
		$info_dil['cdate']			= date("Y-m-d h:i:s");
		$info_dil['company_id']		= $_SESSION['company_id'];
		//$info_dil['branch_id']		=$_SESSION['company_id'];
		//var_dump($info);
		$table_k = 'tbl_quotation_delivery_date';
		$tableid_k = 'quo_delivery_date_id';

		if (!empty($rel_dd['po_delivery_date_id'])) {
			$updateid_k = update_record($table_k, $info_dil, "quo_delivery_date_id=" . $rel_dd['po_delivery_date_id'], $dbcon, $branch_id);
			array_push($d_id, $rel_dd['po_delivery_date_id']);
		} else {
			$inserid_k = add_record($table_k, $info_dil, $dbcon, $branch_id);
			array_push($d_id, $inserid_k);
		}
	}

	$did = implode(",", $d_id);
	$info_dil_1['quo_delivery_date_status'] = "2";
	$updateid_p = update_record($table_k, $info_dil_1, "quot_trn_id=" . $tax_trn_id . " and quo_delivery_date_id NOT IN (" . $did . ")", $dbcon, $branch_id);
	//////////////Maulik End Delivery Date//////////////////////////////////////////
	/////////Harshil - 4-7-2022///////////////////////////////////

	/////////////////////////////////////////////////
} else if (strtolower($POST['mode']) == "get_mail_data") {
	$query  = "select quot_subject,quot_header from tbl_quotation where quotation_id=" . $POST['quotation_id'];
	$result = $dbcon->query($query);
	$row = brp_mysqli_fetch_array($result);
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "show_data") {
	$str = '';
	if ($POST['quotation_id']) {
		$query = "select trn.*,if(trn.project_wise=0,(select product_name from product_mst as pro where pro.product_id=trn.product_id) ,(select product_name from product_mst as pro where pro.product_id=trn.product_id)) as product_name,cat.cat_name, unit.unit_name as rat_unit, buni.unit_name as base_unit, cuni.unit_name as conv_unit, pcat.cat_name as pcat_name from tbl_quotation_trn as trn 
			left join product_mst as pro on pro.product_id=trn.product_id
			left join unit_mst as unit on unit.unitid=trn.rate_unit
			left join unit_mst as buni on buni.unitid = trn.unitid
			left join unit_mst as cuni on cuni.unitid = trn.conv_unit_id
			left join tbl_category as cat on cat.cat_id=trn.cat_id
			left join tbl_category_reciclare as pcat on pcat.rcat_id=trn.rcat_id
			where trn.quot_trn_status=0 and trn.quotation_id=" . $POST['quotation_id'] . " order by cat_id,quot_trn_id ";
	} else {
		$query = "select trn.*,if(trn.project_wise=0,(select product_name from product_mst as pro where pro.product_id=trn.product_id) ,(select product_name from product_mst as pro where pro.product_id=trn.product_id)) as product_name,cat.cat_name,unit.unit_name as rat_unit,buni.unit_name as base_unit, cuni.unit_name as conv_unit, pcat.cat_name as pcat_name from tbl_quotation_trn as trn 
			left join product_mst as pro on pro.product_id=trn.product_id
			left join unit_mst as unit on unit.unitid=trn.rate_unit
			left join unit_mst as buni on buni.unitid = trn.unitid
			left join unit_mst as cuni on cuni.unitid = trn.conv_unit_id
			left join tbl_category as cat on cat.cat_id=trn.cat_id
			left join tbl_category_reciclare as pcat on pcat.rcat_id=trn.rcat_id
			where trn.quot_trn_status=3 and trn.user_id=" . $_SESSION['user_id'] . " order by cat_id,quot_trn_id ";
	}
	//echo $query;
	$result = $dbcon->query($query);
	$str .= '<table class="display table table-bordered table-striped" style="width:100%; table-layout: fixed;">
		<tr>		
		
		<th width="5%" class="text-center">#</th>';

	$getspecialConfiguration = getspecialConfiguration($dbcon);
	$companyConfiguration = getCompanyConfiguration($dbcon);
	if ($companyConfiguration['category_selection_active'] == 1) {
		$str .= '<th width="10%" class="text-center">Product Category</th>';
	}
	$str .= '<th width="35%" class="text-center">Product Name</th>';
	if ($getspecialConfiguration['reciclar'] == 1) {
		$str .= '<th width="10%" class="text-center">Reciclar Category</th>';
	}
	$str .= '<th width="8%" class="text-center">Quantity</th>
		<th width="8%" class="text-center">Rate <span class="currency_icon"> </span></th>
		<th width="5%" class="text-center">Discount <span class="currency_icon"> </span></th>		
		<th width="8%" class="text-center">Tax Details <span class="currency_icon"> </span></th>		
		<th width="10%" class="text-center">Amount <span class="currency_icon"> </span></th>		  			  
		<th width="5%" class="text-center">Extra<br/>Actual</th>		  			  
		<th width="25%" class="text-center">Specification</th>		  	
		<th width="4%" class="text-center">Action</th>		  
		</tr>
		<tbody>';
	if (mysqli_num_rows($result) > 0) {
		$i = 1;
		while ($rel = mysqli_fetch_assoc($result)) {
			if (!empty($rel['currency_id'])) {
				$currency = getcurrencydetail($dbcon, $rel['currency_id']);
			} else {
				$currency = getcurrencydetail($dbcon, $_SESSION['currency_id']);
			}

			$act_amt_flag = "No";
			if ($rel['act_amt_flag'] == '1') {
				$act_amt_flag = "Yes";
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
					echo '<input type="hidden" id="currency_type_response" value="' . $vrel['currency_code'] . '">';
					// 			$rate_label .= $vrel['currency_symbol'].' :' .$rel['product_rate']."<br>";
					$rate_label .=  $vrel['currency_symbol'] . ' :' . $rel['product_rate_conv'];

					// $product_amount_label .= $vrel['currency_symbol'].' :' .$rel['product_amount']."<br>";
					$product_amount_label .=  $vrel['currency_symbol'] . ' :' . $rel['product_amount_conv'];
					$pr_amt = $rel['product_amount_conv'];

					$product_total_label .= $vrel['currency_symbol'] . ' :' . $rel['product_total_conv'] . "<br>";

					$product_discount_label .= $vrel['currency_symbol'] . ' :' . $rel['product_discount_conv'] . "<br>";
					//$product_total_label .=  $vrel['currency_symbol'].' :' .$rel['currency_total'];

				} else {
					$rate_label .= $vrel['currency_symbol'] . ' :' . number_format($rel['product_rate'], 2, '.', '');
					$product_amount_label .= $vrel['currency_symbol'] . ' :' . $rel['product_amount'];
					$product_total_label .= $vrel['currency_symbol'] . ' :' . $rel['product_total'];

					$product_discount_label .= $vrel['currency_symbol'] . ' :' . $rel['product_discount'] . "<br>";
					$pr_amt = $rel['product_amount'];
				}
			} else {
				$rate_label .= $_SESSION['currency_name'] . ' :' . number_format($rel['product_rate'], 4, '.', '');
				$product_amount_label .= $_SESSION['currency_name'] . ' :' . $rel['product_amount'];
				$product_total_label .= $_SESSION['currency_name'] . ' :' . $rel['product_total'];
				$product_discount_label .= $vrel['currency_symbol'] . ' :' . $rel['product_discount'] . "<br>";
				$pr_amt = $rel['product_amount'];
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

			$str .= '<tr> 
				 <td style="vertical-align:top;"> ' . $i . '</td>';
			if ($rel['project_wise'] == 1) {
				$str .= '<button type="button" class="btn btn-xs btn-primary" onclick="load_project_item_list(' . $rel['quot_trn_id'] . ',' . $rel['product_id'] . ')"><i class="fa fa-eye" aria-hidden="true"></i></button>';
			}
			$str .= '</td>';

			if ($companyConfiguration['category_selection_active'] == 1) {
				$str .= '<td style="vertical-align:top;"><strong>' . $rel['cat_name'] . '</strong></td>';
			}
			$str .= '<td style="vertical-align:top;" >
				<strong>' . $rel['product_name'] . '</strong><br/>
				<strong>Desc:</strong> ' . (nl2br($rel['product_desc'])) . '
				</td>';
			if ($getspecialConfiguration['reciclar'] == 1) {
				$str .= '<td style="vertical-align:top;"><strong>' . $rel['pcat_name'] . '</strong></td>';
			}
			$str .= '<!--<td style="vertical-align:top;" class="text-center">
				' . $rel['level_id'] . '
				</td>-->
				<td style="vertical-align:top;" class="text-left">
				<strong style="color:green">Rate Qty</strong> : ' . number_format($sqty, 4, '.', '') . ' ' . $rel['rat_unit'] . '<br>' . $qty_lb . '
				</td>
				<td style="vertical-align:top;" class="text-right">
				' . $rate_label . '
				</td>
				<td style="vertical-align:top;" class="text-right">
				' . $product_discount_label . ' (' . $rel['discount_per'] . '%)
				</td>	
				<td>
				' . $cgst_tax . '<br>' . $sgst_tax . '<br>' . $igst_tax . '
				</td>
				<td style="vertical-align:top;" class="text-right">
				<input type="hidden" name="amount[]" value="' . $pr_amt . '">
				' . $product_amount_label . '
				</td>
				<td style="vertical-align:top;" class="text-center">
				' . $act_amt_flag . '
				</td>
				<td style="vertical-align:top;">
				' . $rel['product_spec'] . '
				</td>
				<td style="vertical-align:middle"> 
				<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_trn_data(' . $rel['quot_trn_id'] . ',' . $rel['project_wise'] . ')"><i class="fa fa-pencil"></i></button>
				<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onclick="delete_trn_data(' . $rel['quot_trn_id'] . ',' . $rel['project_wise'] . ')">X</button><br/></td>
				</tr>';
			$i++;
		}
	} else {
		$str .= '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
	}

	$str .= '</tbody>
		</table>';
	echo $str;
} else if (strtolower($POST['mode']) == "show_data_durva") {
	$str = '';
	if ($POST['quotation_id']) {
		$query = "select trn.*,if(trn.project_wise=0,(select product_name from product_mst as pro where pro.product_id=trn.product_id) ,(select product_name from product_mst as pro where pro.product_id=trn.product_id)) as product_name,cat.cat_name, unit.unit_name from tbl_quotation_trn as trn 
			left join product_mst as pro on pro.product_id=trn.product_id
			left join unit_mst as unit on unit.unitid=trn.unitid
			left join tbl_category as cat on cat.cat_id=trn.cat_id
			where trn.quot_trn_status=0 and pid=0 and trn.quotation_id=" . $POST['quotation_id'];
	} else {
		$query = "select trn.*,if(trn.project_wise=0,(select product_name from product_mst as pro where pro.product_id=trn.product_id) ,(select product_name from product_mst as pro where pro.product_id=trn.product_id)) as product_name,cat.cat_name,unit.unit_name from tbl_quotation_trn as trn 
			left join product_mst as pro on pro.product_id=trn.product_id
			left join unit_mst as unit on unit.unitid=trn.unitid
			left join tbl_category as cat on cat.cat_id=trn.cat_id
			where trn.quot_trn_status=3 and pid=0 and trn.user_id=" . $_SESSION['user_id'];
	}
	$result = $dbcon->query($query);
	$str .= '<table class="display table table-bordered table-striped" style="width:100%; table-layout: fixed;">
		<tr>		
		<th width="4%" class="text-center">Action</th>
		<th width="4%" class="text-center">Sr.no</th>';

	$getspecialConfiguration = getspecialConfiguration($dbcon);

	if ($getspecialConfiguration['aeon_permission'] == 1) {
		$str .= '<th width="10%" class="text-center">Product Category</th>';
	}
	$str .= '<th width="20%" class="text-center">Product Name</th>
		<th width="5%" class="text-center">Quantity</th>
		<th width="8%" class="text-center">Rate <span class="currency_icon"> </span></th>
		<th width="5%" class="text-center">Discount <span class="currency_icon"> </span></th>		
		<th width="8%" class="text-center">Tax Details <span class="currency_icon"> </span></th>		
		<th width="10%" class="text-center">Amount <span class="currency_icon"> </span></th>		  			  
		<th width="5%" class="text-center">Extra<br/>Actual</th>		  			  
		<th width="25%" class="text-center">Specification</th>		  			  
		</tr>
		<tbody>';
	if (mysqli_num_rows($result) > 0) {
		$i = 1;
		while ($rel = mysqli_fetch_assoc($result)) {
			if (!empty($rel['currency_id'])) {
				$currency = getcurrencydetail($dbcon, $rel['currency_id']);
			} else {
				$currency = getcurrencydetail($dbcon, $_SESSION['currency_id']);
			}

			$act_amt_flag = "No";
			if ($rel['act_amt_flag'] == '1') {
				$act_amt_flag = "Yes";
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
					echo '<input type="hidden" id="currency_type_response" value="' . $vrel['currency_code'] . '">';
					// 			$rate_label .= $vrel['currency_symbol'].' :' .$rel['product_rate']."<br>";
					$rate_label .=  $vrel['currency_symbol'] . ' :' . $rel['product_rate_conv'];

					// $product_amount_label .= $vrel['currency_symbol'].' :' .$rel['product_amount']."<br>";
					$product_amount_label .=  $vrel['currency_symbol'] . ' :' . $rel['product_amount_conv'];
					$pr_amt = $rel['product_amount_conv'];

					$product_total_label .= $vrel['currency_symbol'] . ' :' . $rel['product_total_conv'] . "<br>";

					$product_discount_label .= $vrel['currency_symbol'] . ' :' . $rel['product_discount_conv'] . "<br>";
					//$product_total_label .=  $vrel['currency_symbol'].' :' .$rel['currency_total'];

				} else {
					$rate_label .= $vrel['currency_symbol'] . ' :' . number_format($rel['product_rate'], 2, '.', '');
					$product_amount_label .= $vrel['currency_symbol'] . ' :' . $rel['product_amount'];
					$product_total_label .= $vrel['currency_symbol'] . ' :' . $rel['product_total'];

					$product_discount_label .= $vrel['currency_symbol'] . ' :' . $rel['product_discount'] . "<br>";
					$pr_amt = $rel['product_amount'];
				}
			} else {
				$rate_label .= $_SESSION['currency_name'] . ' :' . number_format($rel['product_rate'], 4, '.', '');
				$product_amount_label .= $_SESSION['currency_name'] . ' :' . $rel['product_amount'];
				$product_total_label .= $_SESSION['currency_name'] . ' :' . $rel['product_total'];
				$product_discount_label .= $vrel['currency_symbol'] . ' :' . $rel['product_discount'] . "<br>";
				$pr_amt = $rel['product_amount'];
			}


			$str .= '<tr> 
				<td style="vertical-align:middle"> 
				<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_trn_data(' . $rel['quot_trn_id'] . ',' . $rel['project_wise'] . ')"><i class="fa fa-pencil"></i></button>
				<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onclick="delete_trn_data(' . $rel['quot_trn_id'] . ',' . $rel['project_wise'] . ')">X</button><br/>';
			if ($rel['project_wise'] == 1) {
				$str .= '<button type="button" class="btn btn-xs btn-primary" onclick="load_project_item_list(' . $rel['quot_trn_id'] . ',' . $rel['product_id'] . ')"><i class="fa fa-eye" aria-hidden="true"></i></button>';
			}
			if ($getspecialConfiguration['durva_permission'] == 1) {
				if ($rel['pid'] == 0) {
					$str .= '&nbsp;<button type="button" class="btn btn-xs btn-primary" data-original-title="Add Accessories" data-toggle="tooltip" data-placement="top" onClick="open_accesorice_wise_product_list(' . $rel['quot_trn_id'] . ')">+</button>';
				}
			}

			$str .= '</td>
				<td style="vertical-align:middle">	
					' . $i . '
				</td>
				';
			if ($getspecialConfiguration['aeon_permission'] == 1) {

				$str .= '<td style="vertical-align:top;"><strong>' . $rel['cat_name'] . '</strong></td>';
			}
			$str .= '<td style="vertical-align:top;">
				<strong>' . $rel['product_name'] . '</strong><br/>
				<strong>Desc:</strong> ' . (nl2br($rel['product_desc'])) . '
				</td>
				<!--<td style="vertical-align:top;" class="text-center">
				' . $rel['level_id'] . '
				</td>-->
				<td style="vertical-align:top;" class="text-center">
				' . $rel['product_qty'] . ' ' . $rel['unit_name'] . '
				</td>
				<td style="vertical-align:top;" class="text-right">
				' . $rate_label . '
				</td>
				<td style="vertical-align:top;" class="text-right">
				' . $product_discount_label . ' (' . $rel['discount_per'] . '%)
				</td>	
				<td>
				' . $cgst_tax . '<br>' . $sgst_tax . '<br>' . $igst_tax . '
				</td>
				<td style="vertical-align:top;" class="text-right">
				<input type="hidden" name="amount[]" value="' . $pr_amt . '">
				' . $product_amount_label . '
				</td>
				<td style="vertical-align:top;" class="text-center">
				' . $act_amt_flag . '
				</td>
				<td style="vertical-align:top;">
				' . $rel['product_spec'] . '
				</td>
				</tr>';

			if ($POST['quotation_id']) {
				$sub_product = "select trn.*,if(trn.project_wise=0,(select product_name from product_mst as pro where pro.product_id=trn.product_id) ,(select product_name from product_mst as pro where pro.product_id=trn.product_id)) as product_name,cat.cat_name, unit.unit_name from tbl_quotation_trn as trn 
					left join product_mst as pro on pro.product_id=trn.product_id
					left join unit_mst as unit on unit.unitid=trn.unitid
					left join tbl_category as cat on cat.cat_id=trn.cat_id
					where trn.quot_trn_status=0 and trn.pid=" . $rel['quot_trn_id'] . " and trn.quotation_id=" . $POST['quotation_id'];
			} else {
				$sub_product = "select trn.*,if(trn.project_wise=0,(select product_name from product_mst as pro where pro.product_id=trn.product_id) ,(select product_name from product_mst as pro where pro.product_id=trn.product_id)) as product_name,cat.cat_name,unit.unit_name from tbl_quotation_trn as trn 
					left join product_mst as pro on pro.product_id=trn.product_id
					left join unit_mst as unit on unit.unitid=trn.unitid
					left join tbl_category as cat on cat.cat_id=trn.cat_id
					where trn.quot_trn_status=3 and trn.pid=" . $rel['quot_trn_id'] . " and trn.user_id=" . $_SESSION['user_id'];
			}

			$sub_pro = $dbcon->query($sub_product);
			$j = 1;
			while ($row = brp_mysqli_fetch_array($sub_pro)) {
				if (!empty($row['currency_id'])) {
					$currency = getcurrencydetail($dbcon, $row['currency_id']);
				} else {
					$currency = getcurrencydetail($dbcon, $_SESSION['currency_id']);
				}

				$act_amt_flag = "No";
				if ($row['act_amt_flag'] == '1') {
					$act_amt_flag = "Yes";
				}
				$cgst_tax = "";
				$sgst_tax = "";
				$igst_tax = "";

				if ($row['cgst_tax_per'] != 0) {
					$cgst_tax = "<Strong>CGST (" . $row['cgst_tax_per'] . ") : </strong>" . $currency['currency_symbol'] . " " . (($row['currency_id'] == $_SESSION['currency_id']) ? $row['cgst_tax_rate'] : $row['cgst_tax_rate_conv']) . '<br>';
				}

				if ($row['sgst_tax_per'] != 0) {
					$sgst_tax = "<Strong>SGST (" . $row['sgst_tax_per'] . ") : </strong>" . $currency['currency_symbol'] . " " . (($row['currency_id'] == $_SESSION['currency_id']) ? $row['sgst_tax_rate'] : $row['sgst_tax_rate_conv']) . '<br>';
				}

				if ($row['igst_tax_per'] != 0) {
					$igst_tax = "<Strong>IGST (" . $row['igst_tax_per'] . ") : </strong>" . $currency['currency_symbol'] . " " . (($row['currency_id'] == $_SESSION['currency_id']) ? $row['igst_tax_rate'] : $row['igst_tax_rate_conv']) . '<br>';
				}

				$currency_id = $row['currency_id'];
				$rate_label = '';
				$product_amount_label = '';
				$product_total_label = '';
				$product_discount_label = '';

				$selectCu = "SELECT * FROM tbl_currency WHERE currency_id='" . $currency_id . "' ";
				$curenresult = $dbcon->query($selectCu);
				$vrow = brp_mysqli_fetch_assoc($curenresult);

				if ($currency_id != 0) {

					if ($vrow['currency_id'] != $_SESSION['currency_id']) {
						echo '<input type="hidden" id="currency_type_response" value="' . $vrow['currency_code'] . '">';
						// 			$rate_label .= $vrow['currency_symbol'].' :' .$row['product_rate']."<br>";
						$rate_label .=  $vrow['currency_symbol'] . ' :' . $row['product_rate_conv'];

						// $product_amount_label .= $vrow['currency_symbol'].' :' .$row['product_amount']."<br>";
						$product_amount_label .=  $vrow['currency_symbol'] . ' :' . $row['product_amount_conv'];
						$pr_amt = $row['product_amount_conv'];

						$product_total_label .= $vrow['currency_symbol'] . ' :' . $row['product_total_conv'] . "<br>";

						$product_discount_label .= $vrow['currency_symbol'] . ' :' . $row['product_discount_conv'] . "<br>";
						//$product_total_label .=  $vrow['currency_symbol'].' :' .$row['currency_total'];

					} else {
						$rate_label .= $vrow['currency_symbol'] . ' :' . number_format($row['product_rate'], 2, '.', '');
						$product_amount_label .= $vrow['currency_symbol'] . ' :' . $row['product_amount'];
						$product_total_label .= $vrow['currency_symbol'] . ' :' . $row['product_total'];

						$product_discount_label .= $vrow['currency_symbol'] . ' :' . $row['product_discount'] . "<br>";
						$pr_amt = $row['product_amount'];
					}
				} else {
					$rate_label .= $_SESSION['currency_name'] . ' :' . number_format($row['product_rate'], 4, '.', '');
					$product_amount_label .= $_SESSION['currency_name'] . ' :' . $row['product_amount'];
					$product_total_label .= $_SESSION['currency_name'] . ' :' . $row['product_total'];
					$product_discount_label .= $vrow['currency_symbol'] . ' :' . $row['product_discount'] . "<br>";
					$pr_amt = $row['product_amount'];
				}


				$str .= '<tr> 
					<td style="vertical-align:middle"> 
					<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_trn_data(' . $row['quot_trn_id'] . ',' . $row['project_wise'] . ')"><i class="fa fa-pencil"></i></button>
					<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onclick="delete_trn_data(' . $row['quot_trn_id'] . ',' . $row['project_wise'] . ')">X</button><br/>';
				if ($row['project_wise'] == 1) {
					$str .= '<button type="button" class="btn btn-xs btn-primary" onclick="load_project_item_list(' . $row['quot_trn_id'] . ',' . $row['product_id'] . ')"><i class="fa fa-eye" aria-hidden="true"></i></button>';
				}

				$str .= '</td><td style="vertical-align:top;">

						' . $i . '.' . $j . '
					</td>';
				if ($getspecialConfiguration['aeon_permission'] == 1) {

					$str .= '<td style="vertical-align:top;"><strong>' . $row['cat_name'] . '</strong></td>';
				}
				$str .= '<td style="vertical-align:top;">
					<strong>' . $row['product_name'] . '</strong><br/>
					<strong>Desc:</strong> ' . (nl2br($row['product_desc'])) . '
					</td>
					<!--<td style="vertical-align:top;" class="text-center">
					' . $row['level_id'] . '
					</td>-->
					<td style="vertical-align:top;" class="text-center">
					' . $row['product_qty'] . ' ' . $row['unit_name'] . '
					</td>
					<td style="vertical-align:top;" class="text-right">
					' . $rate_label . '
					</td>
					<td style="vertical-align:top;" class="text-right">
					' . $product_discount_label . ' (' . $row['discount_per'] . '%)
					</td>	
					<td>
					' . $cgst_tax . '<br>' . $sgst_tax . '<br>' . $igst_tax . '
					</td>
					<td style="vertical-align:top;" class="text-right">
					<input type="hidden" name="amount[]" value="' . $pr_amt . '">
					' . $product_amount_label . '
					</td>
					<td style="vertical-align:top;" class="text-center">
					' . $act_amt_flag . '
					</td>
					<td style="vertical-align:top;">
					' . $row['product_spec'] . '
					</td>
					</tr>';
				$j++;
			}
			$i++;
		}
	} else {
		$str .= '<tr><td colspan="9" class="text-center">NO DATA FOUND</td></tr>';
	}

	$str .= '</tbody>
		</table>';
	echo $str;
} else if (strtolower($POST['mode']) == "edit_trn_data") {

	$qdy = $dbcon->query("SELECT master_field_id,master_field_db_name from tbl_master_field where master_field_status=0  order by priority ASC");
	$master_fields_data = brp_mysqli_fetch_all($qdy);

	$join = "";
	$select = "";
	foreach ($master_fields_data as $qd) {
		$join .= " LEFT JOIN tbl_master_field_value as mfv_".$qd['master_field_db_name']. " on mfv_".$qd['master_field_db_name'].".master_field_value_id=mnf.".$qd['master_field_db_name'];

		$select .= " , mfv_".$qd['master_field_db_name']. ".master_field_id as ".$qd['master_field_db_name']. "_master_field_id, mfv_".$qd['master_field_db_name'].".master_field_value as ".$qd['master_field_db_name']. "_master_field_value";
	}

	$q = "SELECT trn.*,pmst.product_name,pmst.parent_category,mnf.* ".$select." FROM tbl_quotation_trn as trn left join product_mst as pmst on pmst.product_id=trn.product_id left join master_name_field as mnf on mnf.master_id=trn.quot_trn_id and mnf.master_type='quotation' " . $join . " WHERE quot_trn_id = '$POST[quot_trn_id]'";
	$e = $dbcon->query($q);
	$r = brp_mysqli_fetch_array($e);
	$r["master_fields_data"] = $master_fields_data;

	if (!empty($r['product_spec_id'])) {
		$a = explode(",", $r['product_spec_id']);
		$specification = implode(',', array_map('quote', $a));
		$spq = $dbcon->query("select group_concat(specification_id ORDER BY FIND_IN_SET(specification_name,'" . $r['product_spec_id'] . "')) as spec_id from tbl_specification where specification_name in(" . $specification . ")");
		$spr = $spq->fetch_assoc();
		$r['product_spec_id_id'] = $spr['spec_id'];
	}
	echo json_encode($r);
} else if (strtolower($POST['mode']) == "has_product") {
	$products = get_quotation_products($dbcon, $POST['quotation_id']);
	echo ($products) ? json_encode($products) : 0;
} else if (strtolower($POST['mode']) == "delete_trn_data") {
	$row = array();
	$flp_qry = "select * from tbl_quotation_trn where pid =" . $POST['quot_trn_id'] . " and quot_trn_status !=2   ";
	$flp_qry_rs = $dbcon->query($flp_qry);
	if (mysqli_num_rows($flp_qry_rs)) {
		$row['res'] = "2";
	} else {
		$info['quot_trn_status'] = 2;
		$info1['quotation_projecttrn_status'] = 2;
		$updateid = update_record('tbl_quotation_trn', $info, "quot_trn_id=" . $POST['quot_trn_id'], $dbcon);
		$updatprojecteid = update_record('tbl_quotation_project_trn', $info1, "quotation_trn_id=" . $POST['quot_trn_id'], $dbcon);

		if ($updateid)
			$row['res'] = "1";
		else
			$row['res'] = "0";
	}
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "getproduct_amount") {
	$arr = get_product_common_tax($dbcon, $POST['product_amount'], $POST['formulaid']);
	echo json_encode($arr);
} else if (strtolower($POST['mode']) == "load_cust_inq") {
	$_SESSION['def_quot_cust_id'] = $POST['cust_id'];
	$resp['resp_html']	= get_cust_inq($dbcon, "", $POST['cust_id']);
	echo json_encode($resp);
} else if (strtolower($POST['mode']) == "load_inq_pro") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	$_SESSION['def_quot_inquiry_id'] = $POST['inquiry_id'];
	$_SESSION['def_quot_subject'] = $_POST['def_quot_subject'];
	$_SESSION['def_c_con_id'] = $POST['c_con_id'];
	$inquiry_id = $POST['inquiry_id'];
	$getspecialConfiguration = getspecialConfiguration($dbcon);

	$where = '';
	if ($getspecialConfiguration['durva_permission'] == 1) {
		$where = ' and pid=0';
	}
	
	//Get Quotation Data
	$quot_qry = "select quot_trn_id from tbl_quotation_trn where quot_trn_status=3 and user_id=" . $_SESSION['user_id'];
	$quot_qry_rs = $dbcon->query($quot_qry);

	// while ($quot_rel = mysqli_fetch_assoc($quot_qry_rs)) {
	// 	$delmasttempid = delete_record('master_name_field', "master_id=".$quot_rel['quot_trn_id']. " and master_type='quotation'", $dbcon);
	// }
	
	//Delete temp DATA
	$deltempid = delete_record('tbl_quotation_trn', "quot_trn_status=3 and user_id=" . $_SESSION['user_id'], $dbcon);
	// print_r($deltempid);
	delete_record('tbl_quotation_project_trn', "inquiry_id='" . $inquiry_id . "' and user_id=" . $_SESSION['user_id'], $dbcon);

	//Get Inq Data
	$inq_qry = "select trn.*,inq.gst_type, pro.product_hsn from tbl_inquiry_trn as trn 
		left join tbl_inquiry as inq on inq.inquiry_id = trn.inquiry_id
		left join product_mst as pro on pro.product_id = trn.product_id 
		where inquiry_trn_status=0 " . $where . " and trn.inquiry_id=" . $inquiry_id;
	$inq_qry_rs = $dbcon->query($inq_qry);

	// var_dump($inq_qry);
	// Get Formula id null for now
	$formulaid = 0;
	$company_state = get_company_data($dbcon, $_SESSION['company_id']);
	while ($inq_rel = mysqli_fetch_assoc($inq_qry_rs)) {

		//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
		//$sale_gst = get_tax_cat_by_hsn_id($dbcon,$inq_rel['product_hsn']);

		if ($inq_rel['gst_type'] == 3) {
			$sale_gst['tax_gst'] = 0.1;
			$sale_gst['tax_cat_id'] = 0;
		} else if ($inq_rel['gst_type'] == 4) {
			$sale_gst['tax_gst'] = 0;
			$sale_gst['tax_cat_id'] = 0;
		} else if ($inq_rel['gst_type'] == 5) {
			$sale_gst['tax_gst'] = 5;
			$sale_gst['tax_cat_id'] = 0;
		} else if ($inq_rel['gst_type'] == 6) {
			$sale_gst['tax_gst'] = 12;
			$sale_gst['tax_cat_id'] = 0;
		} else if ($inq_rel['gst_type'] == 7) {
			$sale_gst['tax_gst'] = 18;
			$sale_gst['tax_cat_id'] = 0;
		} else if ($inq_rel['gst_type'] == 8) {
			$sale_gst['tax_gst'] = 24;
			$sale_gst['tax_cat_id'] = 0;
		} else {
			$sale_gst = get_tax_cat_by_hsn_id($dbcon, trim($inq_rel['product_hsn']));
		}
		$cust_state_id = $POST['cust_stateid'];

		$cgst_tax_rate = 0;
		$cgst_tax_rate_conv = 0;
		$sgst_tax_rate = 0;
		$sgst_tax_rate_conv = 0;
		$igst_tax_rate = 0;
		$igst_tax_rate_conv = 0;

		if (($company_state['stateid'] == $POST['cust_stateid'])) {
			$gst = $sale_gst['tax_gst'] / 2;
			$cgst_tax_per = $gst;
			$cgst_tax_rate = ($gst * $inq_rel['product_amount']) / 100;
			$cgst_tax_rate_conv = ($gst * $inq_rel['product_amount_conv']) / 100;
			$sgst_tax_per = $gst;
			$sgst_tax_rate = ($gst * $inq_rel['product_amount']) / 100;
			$sgst_tax_rate_conv = ($gst * $inq_rel['product_amount_conv']) / 100;
		} else {
			$igst_tax_per = $sale_gst['tax_gst'];
			$igst_tax_rate = ($sale_gst['tax_gst'] * $inq_rel['product_amount']) / 100;
			$igst_tax_rate_conv = ($sale_gst['tax_gst'] * $inq_rel['product_amount_conv']) / 100;
		}

		$info1['inquiry_type']		= $inq_rel['inquiry_type'];
		$info1['project_wise']		= ($inq_rel['inquiry_type'] == 2) ? 1 : 0;
		$info1['product_id']		= $inq_rel['product_id'];
		$info1['cat_id']			= $inq_rel['cat_id'];
		$info1['rcat_id']			= $inq_rel['rcat_id'];
		$info1['product_desc']		= $inq_rel['product_desc'];
		$info1['product_spec']		= $inq_rel['product_spec'];
		$info1['product_spec_id']		= $inq_rel['product_spec_id'];
		$info1['pid']				= 0;

		$info1['level_id']			= $inq_rel['level_id'];

		$info1['unitid']			= $inq_rel['unitid'];
		$info1['product_qty']		= $inq_rel['product_qty'];
		$info1['product_conv_qty']	= $inq_rel['product_conv_qty'];
		$info1['conv_unit_id']		= $inq_rel['conv_unit_id'];
		$info1['rate_unit']			= $inq_rel['rate_unit'];

		$info1['inquiry_type']		= $inq_rel['inquiry_type'];
		$info1['project_wise']		= $inq_rel['project_wise'];
		$info1['formulaid']			= $formulaid;
		$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0;
		$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0;
		$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0;
		$info1['currency_id']		= $inq_rel['currency_id'];
		$info1['currency_rate']		= $inq_rel['currency_rate'];


		$info1['product_rate']		= $inq_rel['product_rate'];
		$info1['product_amount']	= $inq_rel['product_amount'];
		$info1['product_total']		= $inq_rel['product_amount'];
		$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
		$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
		$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0;
		$info1['product_rate_conv']	= $inq_rel['product_rate_conv'];
		$info1['product_amount_conv'] = $inq_rel['product_amount_conv'];
		$info1['product_total_conv'] = $inq_rel['product_amount_conv'];
		$info1['cgst_tax_rate_conv'] = isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
		$info1['sgst_tax_rate_conv'] = isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
		$info1['igst_tax_rate_conv'] = isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;

		$info1['product_tax_cat']	= $sale_gst['tax_cat_id'];
		$info1['user_id']			= $_SESSION['user_id'];
		$info1['company_id']		= $_SESSION['company_id'];
		$info1['quot_trn_status']	= 3;
		$inserid = add_record("tbl_quotation_trn", $info1, $dbcon, $branch_id);



		if (($cgst_tax_per != 0) && ($cgst_tax_rate != 0)) {
			$cl_id = get_ledger_by_name($dbcon, 'CGST');
			$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $cgst_tax_per, $cgst_tax_rate, $inserid, "tbl_quotation_trn", $inq_rel['product_id'], 3, $inserid, $POST['branch_id'], $inq_rel['currency_id'], $inq_rel['currency_rate'], $cgst_tax_rate_conv);
		}
		if (($sgst_tax_per != 0) && ($sgst_tax_rate != 0)) {
			$cl_id = get_ledger_by_name($dbcon, 'SGST');
			$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $sgst_tax_per, $sgst_tax_rate, $inserid, "tbl_quotation_trn", $inq_rel['product_id'], 3, $inserid, $POST['branch_id'], $inq_rel['currency_id'], $inq_rel['currency_rate'], $sgst_tax_rate_conv);
		}
		if (($igst_tax_per != 0) && ($igst_tax_rate != 0)) {
			$cl_id = get_ledger_by_name($dbcon, 'IGST');
			$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $igst_tax_per, $igst_tax_rate, $inserid, "tbl_quotation_trn", $inq_rel['product_id'], 3, $inserid, $POST['branch_id'], $inq_rel['currency_id'], $inq_rel['currency_rate'], $igst_tax_rate_conv);
		}

		// check for the addiotional tax on product Start -- dhaval

		$count_add_tax = get_check_addition_tax($dbcon, $sale_gst['tax_cat_id'], $POST['product_amount'], $inserid, $POST['product_id'], $inserid, $POST['branch_id'], 'tbl_quotation_trn', $inq_rel['currency_id'], $inq_rel['currency_rate'], $pro_amt);

		/*
			Code By Umair: 13/07/2021
			Commnet: Copy the inquiry project trn to quotation project trn
			START
			*/
		if ($info1['inquiry_type'] == 2) {
			copy_inquiry_project_trn_ro_quotation_project_trn($dbcon, $inquiry_id, $inserid, $branch_id, $cust_state_id);
		}
		/* END */
		if ($getspecialConfiguration['durva_permission'] == 1) {
			$sub_pro = "select trn.*, pro.product_hsn from tbl_inquiry_trn as trn left join product_mst as pro on pro.product_id = trn.product_id where inquiry_trn_status=0 and pid=" . $inq_rel['inquiry_trn_id'] . " and inquiry_id=" . $inquiry_id;
			$result_sub = $dbcon->query($sub_pro);
			while ($sub_row = brp_mysqli_fetch_array($result_sub)) {
				$sale_gst = get_tax_cat_by_hsn_id($dbcon, $sub_row['product_hsn']);


				$cust_state_id = $POST['cust_stateid'];

				$cgst_tax_rate = 0;
				$cgst_tax_rate_conv = 0;
				$sgst_tax_rate = 0;
				$sgst_tax_rate_conv = 0;
				$igst_tax_rate = 0;
				$igst_tax_rate_conv = 0;

				if (($company_state['stateid'] == $POST['cust_stateid'])) {
					$gst = $sale_gst['tax_gst'] / 2;
					$cgst_tax_per = $gst;
					$cgst_tax_rate = ($gst * $sub_row['product_amount']) / 100;
					$cgst_tax_rate_conv = ($gst * $sub_row['product_amount_conv']) / 100;
					$sgst_tax_per = $gst;
					$sgst_tax_rate = ($gst * $sub_row['product_amount']) / 100;
					$sgst_tax_rate_conv = ($gst * $sub_row['product_amount_conv']) / 100;
				} else {
					$igst_tax_per = $sale_gst['tax_gst'];
					$igst_tax_rate = ($sale_gst['tax_gst'] * $sub_row['product_amount']) / 100;
					$igst_tax_rate_conv = ($sale_gst['tax_gst'] * $sub_row['product_amount_conv']) / 100;
				}

				$info12['inquiry_type']		= $sub_row['inquiry_type'];
				$info12['project_wise']		= ($sub_row['inquiry_type'] == 2) ? 1 : 0;
				$info12['product_id']		= $sub_row['product_id'];
				$info12['cat_id']			= $sub_row['cat_id'];
				$info12['product_desc']		= $sub_row['product_desc'];
				$info12['product_spec']		= $sub_row['product_spec'];
				$info12['product_spec_id']		= $sub_row['product_spec_id'];


				$info12['level_id']			= $sub_row['level_id'];

				$info12['unitid']			= $sub_row['unitid'];
				$info12['product_qty']		= $sub_row['product_qty'];
				$info12['product_conv_qty']	= $inq_rel['product_conv_qty'];
				$info12['conv_unit_id']		= $inq_rel['conv_unit_id'];
				$info12['rate_unit']			= $inq_rel['rate_unit'];

				$info12['inquiry_type']		= $sub_row['inquiry_type'];
				$info12['project_wise']		= $sub_row['project_wise'];
				$info12['formulaid']			= $formulaid;
				$info12['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0;
				$info12['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0;
				$info12['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0;
				$info12['currency_id']		= $sub_row['currency_id'];
				$info12['currency_rate']	= $sub_row['currency_rate'];
				$info12['pid']				= $inserid;


				$info12['product_rate']		= $sub_row['product_rate'];
				$info12['product_amount']	= $sub_row['product_amount'];
				$info12['product_total']		= $sub_row['product_amount'];
				$info12['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
				$info12['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
				$info12['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0;
				$info12['product_rate_conv']	= $sub_row['product_rate_conv'];
				$info12['product_amount_conv'] = $sub_row['product_amount_conv'];
				$info12['product_total_conv'] = $sub_row['product_amount_conv'];
				$info12['cgst_tax_rate_conv'] = isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
				$info12['sgst_tax_rate_conv'] = isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
				$info12['igst_tax_rate_conv'] = isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;



				$info12['product_tax_cat']	= $sale_gst['tax_cat_id'];
				$info12['user_id']			= $_SESSION['user_id'];
				$info12['company_id']		= $_SESSION['company_id'];
				$info12['quot_trn_status']	= 3;
				$inserid1 = add_record("tbl_quotation_trn", $info12, $dbcon, $branch_id);

				if (($cgst_tax_per != 0) && ($cgst_tax_rate != 0)) {
					$cl_id = get_ledger_by_name($dbcon, 'CGST');
					$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $cgst_tax_per, $cgst_tax_rate, $inserid1, "tbl_quotation_trn", $sub_row['product_id'], 3, $inserid1, $POST['branch_id'], $sub_row['currency_id'], $sub_row['currency_rate'], $cgst_tax_rate_conv);
				}
				if (($sgst_tax_per != 0) && ($sgst_tax_rate != 0)) {
					$cl_id = get_ledger_by_name($dbcon, 'SGST');
					$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $sgst_tax_per, $sgst_tax_rate, $inserid1, "tbl_quotation_trn", $sub_row['product_id'], 3, $inserid1, $POST['branch_id'], $sub_row['currency_id'], $sub_row['currency_rate'], $sgst_tax_rate_conv);
				}
				if (($igst_tax_per != 0) && ($igst_tax_rate != 0)) {
					$cl_id = get_ledger_by_name($dbcon, 'IGST');
					$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $igst_tax_per, $igst_tax_rate, $inserid1, "tbl_quotation_trn", $sub_row['product_id'], 3, $inserid1, $POST['branch_id'], $sub_row['currency_id'], $sub_row['currency_rate'], $igst_tax_rate_conv);
				}

				// check for the addiotional tax on product Start -- dhaval

				$count_add_tax = get_check_addition_tax($dbcon, $sale_gst['tax_cat_id'], $POST['product_amount'], $inserid1, $POST['product_id'], $inserid1, $POST['branch_id'], 'tbl_quotation_trn', $sub_row['currency_id'], $sub_row['currency_rate'], $pro_amt);

				/*
					Code By Umair: 13/07/2021
					Commnet: Copy the inquiry project trn to quotation project trn
					START
					*/
				if ($info1['inquiry_type'] == 2) {
					copy_inquiry_project_trn_ro_quotation_project_trn($dbcon, $inquiry_id, $inserid1, $branch_id, $cust_state_id);
				}
			}
		}
	}
} else if (strtolower($POST['mode']) == "load_annex_content") {
	$an_id = implode(',', array_map('quote', $_POST['an_id']));
	$an_id1 = implode(',', $_POST['an_id']);

	//$an_id = implode(",",$_POST['an_id']);
	$annex_qry = "select * from tbl_annexure where an_name IN (" . $an_id . ") ORDER BY FIND_IN_SET(an_name,'" . $an_id1 . "')";
	$rows = '';
	$aqry = $dbcon->query($annex_qry);
	$cnt = brp_mysqli_num_rows($aqry);
	$i = 1;
	while ($annex_rel = brp_mysqli_fetch_array($aqry)) {
		// $annex_rel = brp_mysqli_fetch_assoc($dbcon->query($annex_qry));
		//$rows.="<strong>"."$annex_rel[an_name]"."</strong><br>";
		$rows .= "$annex_rel[an_detail]" . "<br>";
		if ($cnt > $i) {
			$rows .= "<div style='page-break-after: always'><span style='display: none;'></span></div><br>";
		}
		$i++;
	}
	echo $rows;
	// echo json_encode($annex_rel);
} else if (strtolower($POST['mode']) == "load_product_dtls") {
	$pro_qry = "select pm.*,pm.product_sale_rate as psalerate,um.unit_name from product_mst as pm left join unit_mst as um on um.unitid=pm.product_base_unit  where pm.product_id=" . $POST['product_id'];
	//$pro_qry="select * from product_mst where product_id=".$POST['product_id'];
	$pro_rel = mysqli_fetch_assoc($dbcon->query($pro_qry));
	$pro_rel['current_stock'] = get_current_stock_new($dbcon, $POST['product_id'], $pro_rel['product_base_unit']);

	$qry1 = "select c_add_state as lst,com.stateid as cst from tbl_customer as led 
		left join tbl_cust_address as cust_addr On cust_addr.cust_id = led.cust_id
		left join tbl_company as com on com.company_id=led.company_id
		where led.cust_id =" . $POST['cust_id'];
	$result1 = $dbcon->query($qry1);
	$row1 = mysqli_fetch_assoc($result1);

	if ($row1['lst'] == $row1['cst']) {
		$qry2 = "select * from formula_mst as led 
			where formula_status=0 and tax_cat='INTRA' and tax_per_id=" . $pro_rel['product_sale_gst'];
		$result2 = $dbcon->query($qry2);
		$row2 = mysqli_fetch_assoc($result2);
		$pro_rel['formula_id'] = $row2['formulaid'];
	} else {
		$qry2 = "select * from formula_mst as led 
			where formula_status=0 and tax_cat='INTER' and tax_per_id=" . $pro_rel['product_sale_gst'];
		$result2 = $dbcon->query($qry2);
		$row2 = mysqli_fetch_assoc($result2);
		$pro_rel['formula_id'] = $row2['formulaid'];
	}

	if ($POST['inquiry_type'] == 2) {
		$pro_qry_total = "SELECT sum(`product_amount`) as total FROM tbl_project_assigntrn WHERE project_assigntrn_status=0 and project_assign_id=" . $POST['product_id'];
		$pro_rel_total = mysqli_fetch_assoc($dbcon->query($pro_qry_total));

		$pro_rel['product_sale_rate'] = $pro_rel_total['total'];
	} else {
		$pro_rel['product_sale_rate'] = get_product_rate_sales_time($dbcon, $POST['product_id'], $pro_rel['product_base_unit'], '');
	}



	echo json_encode($pro_rel);
} else if (strtolower($POST['mode']) == "load_typeswise_terms") {
	$quot_type = $POST['quot_type'];
	$quotation_id = $POST['quotation_id'];
	$terms_type = $POST['terms_type'];
	$cust_id = $POST['cust_id'];
	$str = '';
	$str .= '<table class="display table table-bordered table-striped">
		<thead>
		<tr>
		<th width="5%" class="text-center">
		<input type="checkbox" class="check_all_terms" style="height: 20px;width: 20px;" id="check_all_terms" name="check_all_terms" onClick="terms_check_all(this);">
		</th>';
	if ($terms_type == 2) {
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
	if ($terms_type == 2) {
		$terms_qry = "select * from tbl_terms_condition where tc_status=0 and
			 tc_category=1 and find_in_set(" . $quot_type . ",tc_for) group by print_name order by tc_priority";
	} else {
		$terms_qry = "select * from tbl_terms_condition where tc_status=0 and
			 tc_category=1 and find_in_set(" . $quot_type . ",tc_for) order by tc_priority";
	}

	$terms_qry_rs = $dbcon->query($terms_qry);
	$t = 1;
	while ($terms_rel = mysqli_fetch_assoc($terms_qry_rs)) {
		$tc_priority = $terms_rel['tc_priority'];
		$tc_details = $terms_rel['tc_details'];

		if ($terms_type == 1) {
			if ($quotation_id) {
				$quot_term_qry = "select * from tbl_quotation_terms_trn where quotation_terms_trn_status=0 and quotation_id=" . $quotation_id . " and tc_id=" . $terms_rel['tc_id'] . "";
				$quot_term_rel = mysqli_fetch_assoc($dbcon->query($quot_term_qry));
				if ($quot_term_rel['tc_priority']) {
					$tc_priority = $quot_term_rel['tc_priority'];
				}
				if ($quot_term_rel['tc_details']) {
					$tc_details = $quot_term_rel['tc_details'];
				}
			} else {
				$cust_term_qry = "select * from tbl_customer_term_trn where customer_terms_trn_status=0 and tc_for=" . $quot_type . " and cust_id=" . $cust_id . " and tc_id=" . $terms_rel['tc_id'];
				$cust_term_rel = brp_mysqli_fetch_assoc($dbcon->query($cust_term_qry));
				if ($cust_term_rel['tc_priority']) {
					$tc_priority = $cust_term_rel['tc_priority'];
				}
				if ($cust_term_rel['tc_details']) {
					$tc_details = $cust_term_rel['tc_details'];
				}
				$quot_term_rel['tc_id'] = $cust_term_rel['tc_id'];
			}
		} else if ($terms_type == 2) {
			if ($quotation_id) {
				$quot_term_qry = "select * from tbl_quotation_terms_trn where quotation_terms_trn_status=0 and quotation_id=" . $quotation_id . " and tc_id=" . $terms_rel['tc_id'] . "";
				$quot_term_rel = mysqli_fetch_assoc($dbcon->query($quot_term_qry));
				if ($quot_term_rel['tc_priority']) {
					$tc_priority = $quot_term_rel['tc_priority'];
				}
				if ($quot_term_rel['tc_details']) {
					$tc_details = $quot_term_rel['tc_details'];
				}
				if ($quot_term_rel['ref_tc_id']) {
					$quot_ref_tc_id = $quot_term_rel['ref_tc_id'];
				}
			}
		} else {
			if ($quotation_id) {
				$quot_term_qry = "select * from tbl_quotation_terms_trn where quotation_terms_trn_status=0 and quotation_id=" . $quotation_id . " and tc_id=" . $terms_rel['tc_id'] . "";
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
		if ($terms_type == 2) {
			$str .= '<td>' . $terms_rel['print_name'] . '</td>
				<td>
					<select id="ref_tc_id' . $t . '" name="ref_tc_id[]" class="form-control" onchange="get_terms_detail(' . $t . ')">
						' . get_terms_printname_wise($dbcon, $quot_ref_tc_id, $terms_rel['print_name'], $quot_type) . '
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
				<textarea class="form-control" id="tc_details' . $t . '" name="tc_details[]">' . $tc_details . '</textarea>
				</td>';
		} else {
			$str .= '<td>
				<textarea class="form-control" id="tc_details' . $t . '" name="tc_details[]" readonly>' . $tc_details . '</textarea>
				</td>';
		}
		$str .= '</tr>';

		$t++;
	}

	$str .= '</tbody> 
		</table>';

	$resp['resp_html'] = $str;
	echo json_encode($resp);
} else if (strtolower($POST['mode']) == "copy_prev_quot_trn") {
	$del_trn = delete_record('tbl_quotation_trn', "quot_trn_status=3 and user_id=" . $_SESSION['user_id'], $dbcon);
	$del_trns = delete_record('tbl_quot_dfd_attach', "dfd_attach_status=3 and user_id=" . $_SESSION['user_id'], $dbcon);
	$del_trn = delete_record('tbl_quotation_project_trn', "quotation_projecttrn_status=3 and user_id=" . $_SESSION['user_id'], $dbcon);
	$prev_quotation_id = $POST['prev_quotation_id'];

	/* $copy_qry="Insert into tbl_quotation_trn (inquiry_type,project_wise,product_id,product_desc,product_spec,level_id,product_qty,unitid,product_rate, product_discount,discount_per,product_amount,formulaid,tax_name1,tax_amount1,tax_name2, tax_amount2,tax_name3,tax_amount3,product_total,company_id,user_id,quot_trn_status,product_rate_dollar,product_amount_dollar,product_total_dollar,product_hp,cgst_tax_per,cgst_tax_rate,sgst_tax_per,sgst_tax_rate,igst_tax_per,igst_tax_rate,product_tax_cat) 
		select inquiry_type,project_wise,product_id,product_desc,product_spec,level_id,product_qty,unitid,product_rate, product_discount,discount_per,product_amount,formulaid,tax_name1,tax_amount1,tax_name2, tax_amount2,tax_name3,tax_amount3,product_total,company_id,".$_SESSION['user_id'].",3,product_rate_dollar,product_amount_dollar,product_total_dollar,product_hp,cgst_tax_per,cgst_tax_rate,sgst_tax_per,sgst_tax_rate,igst_tax_per,igst_tax_rate,product_tax_cat from tbl_quotation_trn where quot_trn_status=0 and quotation_id=".$prev_quotation_id;
		$copy_qry_rs=$dbcon->query($copy_qry);
		$quotation_trn_id = $dbcon->insert_id; */
	//var_dump($prev_quotation_id);

	$quotation_trn_id = copy_quotation_trn($dbcon, $prev_quotation_id);



	$copy_dfd = "INSERT INTO tbl_quot_dfd_attach (dfd_attch_doc_name, dfd_attch_file, dfd_attach_status, user_id, company_id, branch_id) SELECT dfd_attch_doc_name, dfd_attch_file, 3, " . $_SESSION['user_id'] . ", " . $_SESSION['company_id'] . ", branch_id FROM tbl_quot_dfd_attach WHERE dfd_attach_status = 0  AND quotation_id = " . $prev_quotation_id;
	$copy_qry_dfd = $dbcon->query($copy_dfd);

	/*
		Code By Umair : 14/07/2021
		Comment: Insert the tbl_quotation_project_trn copy for revise quotation
		*/

	revise_quotation_project_trn_to_quotation_project_trn($dbcon, $prev_quotation_id, $quotation_trn_id, $branch_id, $type = null);

	/*
		END
		*/
} else if (strtolower($POST['mode']) == "add_budget_trn_data") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	$info1['req_product_id']	= $POST['req_product_id'];
	$info1['req_product_desc']	= $_POST['req_product_desc'];
	$info1['req_product_qty']	= $POST['req_product_qty'];
	$info1['req_product_rate']	= $POST['req_product_rate'];
	$info1['req_unitid']		= $POST['req_unitid'];
	$info1['req_product_amount'] = $POST['req_product_amount'];
	$info1['quot_trn_id']		= $POST['quot_trn_id'];

	$info1['user_id']		= $_SESSION['user_id'];
	$info1['company_id']	= $_SESSION['company_id'];
	//var_dump($info1);
	$table = 'tbl_quot_budget_trn';
	$tableid = 'quot_budget_trn_id';
	$info1['quot_budget_trn_status'] = 0;

	if (empty($POST['budget_trn_edit_id'])) {
		$get_dupl_qry = "select quot_budget_trn_id from tbl_quot_budget_trn where quot_budget_trn_status=0 and quot_trn_id=" . $POST['quot_trn_id'] . " and req_product_id=" . $POST['req_product_id'];
		$dupl_qry_rs = $dbcon->query($get_dupl_qry);
		if (mysqli_num_rows($dupl_qry_rs)) {
			echo '-1';
		} else {
			$inserid = add_record($table, $info1, $dbcon, $branch_id);
			echo '1';
		}
	} else {
		$updateid = update_record($table, $info1, $tableid . "=" . $POST['budget_trn_edit_id'], $dbcon, $branch_id);
		echo '1';
	}
} else if (strtolower($POST['mode']) == "show_budget_trn_data") {
	$str = '';

	$query = "select trn.*,pro.product_name,unit.unit_name from tbl_quot_budget_trn as trn 
		left join tbl_product as pro on pro.product_id=trn.req_product_id
		left join unit_mst as unit on unit.unitid=trn.req_unitid
		where trn.quot_budget_trn_status=0 and trn.quot_trn_id=" . $POST['quot_trn_id'];

	$result = $dbcon->query($query);
	$str .= '<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr>
		<th width="35%" class="text-center">Item Details</th>
		<th width="" class="text-center">Quantity</th>
		<th width="" class="text-center">Rate</th>
		<th width="" class="text-center">Amount</th>
		<th width="7%" class="text-center">Action</th>
		</tr>
		<tbody>';
	if (mysqli_num_rows($result) > 0) {
		$i = 1;
		while ($rel = mysqli_fetch_assoc($result)) {
			$str .= '<tr> 
				<td style="vertical-align:top;">
				<strong>' . $rel['product_name'] . '</strong><br/>
				<strong>Desc:</strong> ' . (nl2br($rel['req_product_desc'])) . '
				</td>
				<td style="vertical-align:top;" class="text-center">
				' . $rel['req_product_qty'] . ' ' . $rel['unit_name'] . '
				</td>
				<td style="vertical-align:top;" class="text-right">
				' . $rel['req_product_rate'] . '
				</td>
				<td style="vertical-align:top;" class="text-right">
				' . $rel['req_product_amount'] . '
				<input type="hidden" name="req_product_amount_ttl[]" value="' . $rel['req_product_amount'] . '">
				</td>';

			$str .= '
				<td style="vertical-align:middle"> 
				<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_budget_trn_data(' . $rel['quot_budget_trn_id'] . ')"><i class="fa fa-pencil"></i></button>
				<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_budget_trn_data(' . $rel['quot_budget_trn_id'] . ')">X</button>
				</td>
				</tr>';
			$i++;
		}
	} else {
		$str .= '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
	}

	$str .= '</tbody>
		</table>';
	echo $str;
} else if (strtolower($POST['mode']) == "edit_budget_trn_data") {
	$q = $dbcon->query("SELECT trn.* FROM tbl_quot_budget_trn as trn WHERE quot_budget_trn_id = '$POST[quot_budget_trn_id]'");
	$r = $q->fetch_assoc();
	echo json_encode($r);
} else if (strtolower($POST['mode']) == "delete_budget_trn_data") {
	$row = array();
	$info['quot_budget_trn_status'] = 2;
	$updateid = update_record('tbl_quot_budget_trn', $info, "quot_budget_trn_id=" . $POST['quot_budget_trn_id'], $dbcon);

	if ($updateid)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "get_budget") {
	$info['budget_trn_ttl']		= $POST['budget_trn_ttl'];
	$info['budget_margin_per']	= $POST['budget_margin_per'];
	$info['budget_margin_amt']	= $POST['budget_margin_amt'];
	$info['budget_trn_g_total']	= $POST['budget_trn_g_total'];
	$updateid = update_record('tbl_quotation_trn', $info, "quot_trn_id=" . $POST['quot_trn_id'], $dbcon);

	$row['msg'] = "1";
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "copy_master_bom_data") {
	$del_trn = delete_record('tbl_quot_budget_trn', "quot_trn_id=" . $POST['quot_trn_id'], $dbcon);
	$product_id = $POST['product_id'];
	$product_qty = floatval($POST['product_qty']);
	$copy_qry = "Insert into tbl_quot_budget_trn (req_product_id,req_product_desc,req_unitid,req_product_qty,req_product_rate,req_product_amount,user_id,quot_trn_id)

		SELECT alloctrn.req_product_id,alloctrn.req_product_desc,req_unitid,act_req_qty*" . $product_qty . ",(select product_purchase_mst_rate from tbl_product where product_id=alloctrn.req_product_id) as p_rate,((select product_purchase_mst_rate from tbl_product where product_id=alloctrn.req_product_id)*act_req_qty*" . $product_qty . ") as p_amt," . $_SESSION['user_id'] . "," . $POST['quot_trn_id'] . " FROM `tbl_master_bom_trn` as alloctrn
		left join tbl_master_bom as alloc on alloc.bom_mst_id=alloctrn.bom_mst_id
		where alloctrn.bom_mst_trn_status=0 and alloc.bom_mst_status=0 and alloc.product_id=" . $product_id;
	$copy_qry_rs = $dbcon->query($copy_qry);
} else if (strtolower($POST['mode']) == "approv_quot") {

	$info['approve_status'] = $POST['approve_status'];
	$updateid = update_record('tbl_quotation', $info, "quotation_id=" . $POST['quotation_id'], $dbcon);

	if ($updateid) {
		echo 1;
	} else {
		echo 0;
	}
} else if (strtolower($POST['mode']) == "add_apprv_hist") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	// check if user has already Approved or Rejected Quotation
	$check_hist_qry = "SELECT log.quot_aprv_log_id, usr.user_name, log.approve_status, log.approve_remark, log.cdate, log.user_id 
		FROM tbl_quot_aprv_log as log left join users as usr on usr.user_id=log.user_id 
		where log.quot_aprv_log_status=0 and log.quotation_id=" . $POST['quotation_id'] . " and log.user_id = " . $_SESSION['user_id'] . "
		order by log.quot_aprv_log_id desc limit 1";
	$result = brp_mysqli_query($dbcon, $check_hist_qry);
	$history_data = brp_mysqli_fetch_all($result, MYSQLI_ASSOC);
	// echo "hi";

	// 		if($history_data[0]['approve_status'] !== $POST['approve_status']) {
	$info1['assign_user_ids']	= $POST['assign_user_ids'];
	$info1['approve_remark']	= $POST['approve_remark'];
	$info1['approve_status']	= $POST['approve_status'];
	$info1['quotation_id']	= $POST['quotation_id'];
	$info1['user_id']		= $_SESSION['user_id'];
	$info1['company_id']	= $_SESSION['company_id'];

	$inserid = add_record("tbl_quot_aprv_log", $info1, $dbcon, $branch_id);

	if ($info1['approve_status'] == 1) {
		$querycu = "select cust.cust_email,quo.user_id,quo.cust_id,quo.prev_quotation_id from tbl_quotation as quo
			left join tbl_customer as cust on cust.cust_id=quo.cust_id
			where quo.quotation_id=" . $info1['quotation_id'];
		$resultcu = $dbcon->query($querycu);
		$relcu = brp_mysqli_fetch_assoc($resultcu);
		$to_email_id = $relcu['cust_email'];

		$cur_user_id = $relcu['user_id'];
		$cur_user = getUserDetailById($dbcon, $cur_user_id);
		$from_email_id = ($cur_user && $cur_user['common_email_id']) ? $cur_user['common_email_id'] : ADMIN_EMAIL;

		if (!empty($relcu['prev_quotation_id'])) {
			$queryst = "select email_sms_id from email_sms_template where task_id=20 and status=0 and company_id=" . $_SESSION['company_id'];
		} else {
			$queryst = "select email_sms_id from email_sms_template where task_id=21 and status=0 and company_id=" . $_SESSION['company_id'];
		}

		$resultst = $dbcon->query($queryst);
		$relst = brp_mysqli_fetch_assoc($resultst);

		$mail_template = getEmailSMSTemplateById($dbcon, $relst['email_sms_id']);
		$module_id = 2;

		if ($mail_template && $to_email_id) {

			$querybcc = "select email_cc,email_bcc from email_sms_template where email_sms_id=" . $relst['email_sms_id'];
			$resultbdd = $dbcon->query($querybcc);
			$rel1 = brp_mysqli_fetch_assoc($resultbdd);

			if (!empty($rel1['email_cc'])) {
				$umix = explode(",", $rel1['email_cc']);
				$umix = array_push($umix, $cur_user_id);
				$uid = implode(",", $umix);
			} else {
				//var_dump($uid);
				$uid = $cur_user_id;
			}

			$querybcc1 = "select GROUP_CONCAT(common_email_id SEPARATOR ';') as email_cc from users where user_id in (" . $uid . ")";
			$resultbdd1 = $dbcon->query($querybcc1);
			$rel11 = brp_mysqli_fetch_assoc($resultbdd1);

			$querybcc2 = "select GROUP_CONCAT(common_email_id SEPARATOR ";
			") as email_bcc from users where user_id in (" . $rel1['email_bcc'] . ")";
			$resultbdd2 = $dbcon->query($querybcc2);
			$rel12 = brp_mysqli_fetch_assoc($resultbdd2);

			// Amish Soni Start 18-01-2021
			$subject = $mail_template['email_subject'];
			$content = $mail_template['email_content'];

			$subject = replaceMergeFields($dbcon, $subject, $relcu['cust_id'], $module_id);
			$content = replaceMergeFields($dbcon, $content, $relcu['cust_id'], $module_id);
			// Amish Soni End 18-01-2021
			// var_dump($mail_template);
			$getspecialConfiguration = getspecialConfiguration($dbcon);
			if ($getspecialConfiguration['umaboy_permission'] == 1) {
				$attach = array();
				$quot_file = umaboy_quotation_print($dbcon, $POST['quotation_id'], 'Yes');
				array_push($attach, $quot_file);
				final_send_email($from_email_id, $to_email_id, $rel11['email_cc'], $rel12['email_bcc'], $subject, $content, $attach);
				unlink('../../../view/upload/mail_attach/' . $quot_file);
			} else {
				// $print_name = get_print_path($dbcon,'1');
				// require_once('../../../print/view/'.$print_name.'.php');
				// $attach = array();
				//             $quot_file = $print_name($dbcon, $POST['quotation_id'],'Yes');
				//             array_push($attach,$quot_file);
				// final_send_email($from_email_id, $to_email_id, $rel11['email_cc'],$rel12['email_bcc'], $subject, $content,$attach);
				//             // var_dump('../../../print/view/'.$print_name.'.php');die;
				// unlink('../../../view/upload/mail_attach/'.$quot_file);
			}
		}
	} else {
		$qty_qry = $dbcon->query("SELECT inquiry_id FROM tbl_quotation WHERE quotation_id = " . $POST['quotation_id']);
		$rel_qry = brp_mysqli_fetch_assoc($qty_qry);
		$infoqyt['task_status'] = 1;
		$infoqyt['task_completion_date'] = date("Y-m-d H:i:s");
		$updateid = update_record('tbl_task', $infoqyt, "task_status=0 and inquiry_id=" . $rel_qry['inquiry_id'], $dbcon);

		$infotask['task_type_id'] = 20;
		$infotask['task_rel_id'] = 5;
		$infotask['inquiry_id'] = $rel_qry['inquiry_id'];
		$infotask['task_remark'] = $POST['approve_remark'];
		$infotask['assign_user_ids'] = $_SESSION['user_id'];
		$show_user_ids	= show_user_ids($dbcon, $_SESSION['user_id']);
		$infotask['show_user_ids'] = $show_user_ids;
		$infotask['user_id'] = $_SESSION['user_id'];
		$infotask['company_id'] = $_SESSION['company_id'];
		$infotask['task_priority_id'] = 1;
		$infotask['entry_type'] = 1;
		$infotask['task_alert_id'] = 0;
		$infotask['alert_date_time'] = date("Y-m-d H:i:s");
		$infotask['task_due_date'] = date("Y-m-d H:i:s");
		$infotask['create_date'] = date("Y-m-d H:i:s");
		$infotask['cdate'] = date("Y-m-d H:i:s");
		$infotask['email_template_id'] = '';

		$ins_task_id = add_record('tbl_task', $infotask, $dbcon, $branch_id);
	}

	//Hide approve btn if not allowed
	//$final_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'final_aprv',$dbcon);
	if (in_array(QUOTATION_SLUG_APPROVE, $bulkAccessArray)) {
		$infoso['approve_status'] = $POST['approve_status'];
		$updateid = update_record('tbl_quotation', $infoso, "quotation_id=" . $POST['quotation_id'], $dbcon, $branch_id);
	}
	echo TRUE;
	// 		} else {
	// 			echo FALSE;
	// 		}

} else if (strtolower($POST['mode']) == "load_quot_hist_datatable") {

	$where = '';
	$where .= "  and log.quotation_id=" . $POST['quotation_id'];

	$appData = array();
	$i = 1;
	$aColumns = array('log.quot_aprv_log_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
	$sIndexColumn = "log.quot_aprv_log_id";
	$isWhere = array("log.quot_aprv_log_status=0 " . $where . " ");
	$sTable = "tbl_quot_aprv_log as log";
	$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
	$hOrder = "log.quot_aprv_log_id desc";
	include('../../../include/pagging.php');
	$appData = array();
	$id = 1;
	foreach ($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['user_name'];

		if ($row['approve_status'] == '1') {
			$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
		} else {
			$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Reject</div>';
		}

		$row_data[] = nl2br($row['approve_remark']);
		$row_data[] = date("d-M-Y h:i A", strtotime($row['cdate']));

		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode($output);
} else if (strtolower($POST['mode']) == "open_quot_email") {
	$set = "select quot_email_content from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = mysqli_fetch_assoc($dbcon->query($set));
	$email_content = $set_head['quot_email_content'];
	$resp['email_content']	= $email_content;

	//Get Customer Detail
	$custqry = "select cust_email from tbl_customer where cust_id=" . $POST['cust_id'];
	$cust_rel = mysqli_fetch_assoc($dbcon->query($custqry));
	$resp['to_email_id']	= strtolower($cust_rel['cust_email']);

	//Get Quot Subject
	$qt_qry = "select qt.quot_subject from tbl_quotation as qt where qt.quotation_id=" . $POST['quotation_id'];
	$qt_rel = mysqli_fetch_assoc($dbcon->query($qt_qry));
	$resp['email_subject']	= $qt_rel['quot_subject'];

	echo json_encode($resp);
} else if (strtolower($POST['mode']) == "send_mail") {
	//var_dump($POST);
	//exit;
	$files = array();
	$inquiry_id = strtolower($POST['email_ref_id']);
	$to_email_id = strtolower($POST['to_email_id']);
	$ccemail_id = strtolower($POST['ccemail_id']);
	$bccemail_id = strtolower($POST['bccemail_id']);
	$email_subject = $_POST['email_subject'];
	$email_content = $_POST['email_content'];
	if (!empty($_FILES['email_attach']['tmp_name'])) {
		$file = upload_mail_attch_file($_FILES, $dbcon);
		array_push($files, $file);
	}

	//Direct PDF Generate
	//$quotation_pdf=email_quotation_pdf($inquiry_id,$dbcon);
	//array_push($files,$quotation_pdf);

	//final_send_email($to_email_id,$ccemail_id,$bccemail_id,$email_subject,$email_content,$files);		
	$resp = final_send_email($from_email,$to_email_id, $ccemail_id, $bccemail_id, $email_subject, $email_content, $files);


	$arr['msg'] = array();
	if ($resp['code'] == 'success') {
		$arr['msg'] = '1';
		if ($file) {
			unlink(MAIL_ATTACH_UPING . $file);
		}
		unlink(MAIL_ATTACH_UPING . $quotation_pdf);
	} else {
		$arr['msg'] = '0';
	}
	echo json_encode($arr);
} else if (strtolower($POST['mode']) == "add_dfd_attch_field") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	$info1['dfd_attch_file']	= upload_attch_file($_FILES);
	$info1['user_id']			= $_SESSION['user_id'];
	$info1['company_id']		= $_SESSION['company_id'];

	$table = 'tbl_quot_dfd_attach';
	$tableid = 'dfd_attach_id';
	if (!empty($POST['quotation_id'])) {
		$info1['quotation_id'] = $POST['quotation_id'];
	} else {
		$info1['dfd_attach_status'] = 3;
	}

	$inserid = add_record($table, $info1, $dbcon, $branch_id);
} else if (strtolower($POST['mode']) == "show_dfd_attach_data") {

	if ($POST['quotation_id']) {
		$query = "select mst.* from tbl_quot_dfd_attach as mst 
			where dfd_attach_status=0 and mst.quotation_id=" . $POST['quotation_id'];
	} else {
		$query = "select mst.* from tbl_quot_dfd_attach as mst 
			where dfd_attach_status=3 and mst.user_id=" . $_SESSION['user_id'];
	}
	$result = $dbcon->query($query);
	echo '<table class="display table table-bordered table-striped">
		<tr>
		<th width="5%" class="text-center">Sr.</th>
		<th width="60%" class="text-center">Attached Image</th>
		<th width="10%" class="text-center">Action</th>					  
		</tr>
		<tbody>';
	if (mysqli_num_rows($result) > 0) {
		$i = 1;
		while ($rel = mysqli_fetch_assoc($result)) {
			$file_path = $dbcon->real_escape_string(DOMAIN . INQ_ATTACH_VWING . $rel['dfd_attch_file']);
			echo '<tr> 
				<td style="vertical-align:top;">
				<strong>' . $i . '</strong>
				</td>
				<td style="vertical-align:top;" class="text-center">
				<a href="' . ROOT . INQ_ATTACH_VWING . $rel['dfd_attch_file'] . '" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>

				<button type="button" onclick="copyToClipboard(\'' . $file_path . '\')" class="btn btn-primary" target="_blank"><i class="fa fa-clipboard"></i> Copy Path</button>
				</td>
				<td style="vertical-align:top">';

			echo ' <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_dfd_attach_data(' . $rel['dfd_attach_id'] . ')">X</button>';

			echo '</td>	
				</tr>';
			$i++;
		}
	} else {
		echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
	}

	echo '</tbody>
		</table>';
} else if (strtolower($POST['mode']) == "delete_dfd_attach_data") {
	$row = array();
	$del_attch_qry = "select dfd_attch_file from tbl_quot_dfd_attach where dfd_attach_id=" . $POST['dfd_attach_id'];
	$del_attch_rel = mysqli_fetch_assoc($dbcon->query($del_attch_qry));
	unlink(INQ_ATTACH_UPING . $del_attch_rel['dfd_attch_file']);

	$info['dfd_attach_status'] = 2;
	$updateid = update_record('tbl_quot_dfd_attach', $info, "dfd_attach_id=" . $POST['dfd_attach_id'], $dbcon);

	if ($updateid)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "load_def_quotation_no") {
	if (empty($POST['start_quotation_id']) && empty($POST['eid'])) {
		$resp['quot_no'] = load_common_no($dbcon, QUOTATION_SERIES);
	} else {

		$where = "";
		if (!empty($POST['eid'])) {
			$where = " and quotation_id!=" . $POST['eid'];
		}
		$get_rev_cnt = "select count(quotation_id) as ttl_cnt,(select quotation_no from tbl_quotation where quotation_id=" . $POST['start_quotation_id'] . ") as qt_no from tbl_quotation where quotation_status=0 and quot_revise_type=0 and start_quotation_id=" . $POST['start_quotation_id'] . $where;

		$rev_cnt = mysqli_fetch_assoc($dbcon->query($get_rev_cnt));
		if ($POST['quot_revise_type'] == 0) {
			$resp['quot_no'] = $rev_cnt['qt_no'] . "/R-" . $rev_cnt['ttl_cnt'];
		} else {
			$prev_quot_no = "select quotation_no from tbl_quotation where quotation_id=" . $POST['prev_quotation_id'];
			$prev_no = brp_mysqli_fetch_array($dbcon->query($prev_quot_no));
			$resp['quot_no'] = $prev_no['quotation_no'];
		}
	}
	echo json_encode($resp);
}
/*
	Code By Umair : 13-07-2021
	Comment: Load Product Based On the Inquiry Type
	START
	*/ else if (strtolower($POST['mode']) == "load_inquiry_type_product") {
	$inquiry_type = $POST['inquiry_type'];

	if ($inquiry_type == '1') {
		$arr['product_list'] = getproduct_typewise($dbcon, "", $_POST['pro_type']);
	} elseif ($inquiry_type == '2') {
		$getProjectList = '<option value="" >Choose Product</option>';
		$getProjectList .= getProjectList($dbcon, "");
		$arr['product_list'] = $getProjectList;
	} elseif ($inquiry_type == '3') {
		$product_list = getproduct_typewise($dbcon, "", $_POST['pro_type']);
		$product_list .= getProjectList($dbcon, "");
		$arr['product_list'] = $product_list;
	}

	echo json_encode($arr);
} else if (brp_strtolower($POST['mode']) == "load_tempoutward") {

	if (empty($POST['eid'])) {
		$query = "select mst.*,pro.product_name, hsn.hsn_code from tbl_project_assigntrn as mst left join product_mst as pro on pro.product_id=mst.product_id 
			LEFT JOIN mst_hsn_code AS hsn on hsn.hsn_id = mst.product_hsn_code
			where mst.project_assign_id=" . $POST['project_assign_id'] . " and mst.project_assigntrn_status=0";
	} else {
		$query = "select mst.*,pro.product_name from, hsn.hsn_code tbl_project_assigntrn as mst left join product_mst as pro on pro.product_id=mst.product_id
			LEFT JOIN mst_hsn_code AS hsn on hsn.hsn_id = mst.product_hsn_code 
			where mst.project_assign_id=" . $POST['project_assign_id'] . " and mst.project_assigntrn_status=0";
	}
	$result = $dbcon->query($query);
	$companySettings = getCompanySettings($dbcon);
	$project_wise_item_rate = '';
	if ($companySettings) {
		$project_wise_item_rate = $companySettings['project_wise_item_rate'];
	}
	echo ' <div class="form-group">
		<div class="col-md-12 col-xs-12"  style="overflow-y: scroll;height: 350px;">
		<input type="text" class="form-control" id="projectProductTrn" placeholder="Search Product Only.." title="Product Only"><br>    
		<table id="project-product-table" cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
		<tr id="field">
		<th class="text-center"width="25%">Product Name</th>
		<th class="text-center"width="8%">HSN Code</th>
		<th class="text-center"width="8%">Qty</th>';
	echo  '<th class="text-center"width="10%">Rate</th>';
	echo  '<th class="text-center"width="10%">Taxable Value</th>';
	echo  '<th class="text-center"width="10%">Tax</th>';
	echo  '<th class="text-center"width="10%">Total Amount</th>';
	echo '</tr>';
	if (brp_mysqli_num_rows($result) > 0) {
		$i = 1;
		$ttl_amt = 0;
		while ($rel = brp_mysqli_fetch_assoc($result)) {
			echo '<tr id="fieldtr' . $id . '" >
				<td data-label="PRODUCT NAME" style="vertical-align:top;text-align:left">
				' . $rel['product_name'] . '
				' . (!empty($rel['description']) ? '<br/><strong>Desc.</strong> :' . $rel['description'] : '') . '
				</td>

				<td data-label="HSN CODE" style="vertical-align:top;" class="text-center">';
			if (empty($rel['hsn_code'])) {
				echo '-';
			} else {
				echo $rel['hsn_code'];
			}
			echo '</td>
				<td data-label="QTY" style="vertical-align:top;" class="text-center">
				' . $rel['product_qty'] . '
				</td>';
			echo '<td  data-label="RATE" style="vertical-align:top;" class="text-center">
				' . $rel['product_rate'] . '
				</td>';

			echo '<td  data-label="TAXABLE AMOUNT" style="vertical-align:top;" class="text-center">
				' . $rel['product_amount'] . '
				</td>
				<td  data-label="TAX" style="vertical-align:top;" class="text-center">';
			if (empty($rel['formulaid'])) {
				echo '-';
			} else {
				echo (empty($rel['tax_name1']) ? " " : $rel['tax_name1'] . ' : ' . $rel['tax_amount1']) . '<br/>';
				echo (empty($rel['tax_name2']) ? " " : $rel['tax_name2'] . ' : ' . $rel['tax_amount2']) . '<br/>';
				echo (empty($rel['tax_name3']) ? " " : $rel['tax_name3'] . ' : ' . $rel['tax_amount3']) . '<br/>';
			}
			echo '</td>
				<td  data-label="TOTAL AMOUNT" style="vertical-align:top;" class="text-center">
				' . $rel['product_total'] . '
				</td>';
			echo '</tr>';
			$i++;
			$ttl_amt += $rel['product_total'];
		}
		echo '<tr>
			<td colspan="6" style="font-weight: bold; text-align: right;">TOTAL</td>
			<td class="text-center" style="font-weight: bold;">' . $ttl_amt . '</td>
			</tr>';
	} else {
		echo '<tr><td colspan="7" class="text-center">NO DATA FOUND</td></tr>';
	}
	echo '</table></div></div>';
} else if (brp_strtolower($POST['mode']) == "add_project_field") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$info1['inquiry_id']        = $POST['inquiry_id'];
	$info1['quotation_id']        = $POST['eid'];
	$info1['quotation_trn_id']        = $POST['quotation_trn_id'];
	$info1['inquiry_type']        = $POST['inquiry_type'];
	$info1['product_id']        = $POST['product_id'];
	// $info1['product_category_id']        = $POST['product_category_id'];
	$info1['project_assign_id'] = $POST['project_assign_id'];
	$info1['description']       = stripslashes($POST['product_des']);
	$info1['product_disc']      = stripslashes($POST['product_des']);
	$info1['product_spec']      = stripslashes($POST['product_spec']);
	$info1['product_hsn_code']  = $POST['product_hsn_code'];
	$info1['product_qty']       = $POST['product_qty'];
	$info1['product_rate']      = $POST['product_rate'];
	$info1['product_amount']    = $POST['product_qty'] * $POST['product_rate'];
	$info1['formulaid']         = $POST['formulaid'];

	$info1['user_id']   = $_SESSION['user_id'];
	$info1['company_id']        = $_SESSION['company_id'];
	$info1['quotation_projecttrn_status'] = 0;

	$info = get_product_common_tax($dbcon, $info1['product_amount'], $POST['formulaid']);
	$info1 = array_merge($info1, $info);

	$table = 'tbl_quotation_project_trn';
	$tableid = 'quotation_projecttrn_id';

	if (empty($POST['edit_id'])) {
		$inserid = add_record($table, $info1, $dbcon, $branch_id);
	} else {
		$updateid = update_record($table, $info1, $tableid . "=" . $POST['edit_id'], $dbcon, $branch_id);
	}
} else if (brp_strtolower($POST['mode']) == "load_productdata") {
	$pid = $POST['eid'];
	//$qry="select * from tbl_product where product_id=".$POST['eid'];
	$qry = "select * from product_mst where product_id=$pid";
	$result = $dbcon->query($qry);
	$row = brp_mysqli_fetch_assoc($result);

	echo brp_json_encode($row);
} else if (brp_strtolower($POST['mode']) == "edit_project_data") {
	$q = $dbcon->query("select mst.*,pro.product_name from tbl_quotation_project_trn as mst left join tbl_product as pro on mst.product_id=pro.product_id where quotation_projecttrn_id = '$POST[id]'");
	$r = $q->fetch_assoc();

	echo brp_json_encode($r);
} else if (brp_strtolower($POST['mode']) == "delete_project_data") {
	$row = array();
	$info['quotation_projecttrn_status'] = 2;
	$updateid = update_record("tbl_quotation_project_trn", $info, "quotation_projecttrn_id=" . $POST['eid'], $dbcon);
	if ($updateid)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo brp_json_encode($row);
} else if (strtolower($POST['mode']) == "load_product_dtls") {
	$pro_qry = "select * from product_mst where product_id=" . $POST['product_id'];

	$pro_rel = mysqli_fetch_assoc($dbcon->query($pro_qry));

	$qry1 = "select c_add_state as lst,com.stateid as cst from tbl_customer as led 
		left join tbl_cust_address as cust_addr On cust_addr.cust_id = led.cust_id
		left join tbl_company as com on com.company_id=led.company_id
		where led.cust_id =" . $POST['cust_id'];
	$result1 = $dbcon->query($qry1);
	$row1 = mysqli_fetch_assoc($result1);

	if ($row1['lst'] == $row1['cst']) {
		$qry2 = "select * from formula_mst as led 
			where formula_status=0 and tax_cat='INTRA' and tax_per_id=" . $pro_rel['product_sale_gst'];
		$result2 = $dbcon->query($qry2);
		$row2 = mysqli_fetch_assoc($result2);
		$pro_rel['formula_id'] = $row2['formulaid'];
	} else {
		$qry2 = "select * from formula_mst as led 
			where formula_status=0 and tax_cat='INTER' and tax_per_id=" . $pro_rel['product_sale_gst'];
		$result2 = $dbcon->query($qry2);
		$row2 = mysqli_fetch_assoc($result2);
		$pro_rel['formula_id'] = $row2['formulaid'];
	}
} else if (strtolower($POST['mode']) == "get_project_amount") {
	$arr = get_product_common_tax($dbcon, $POST['product_amount'], $POST['formulaid']);
	echo json_encode($arr);
} else if (strtolower($POST['mode']) == "send_quotation") {
	get_quotation($dbcon, $_POST['quotation_id']);
	$arr = send_whatsapp_quotation($dbcon, $_POST['quotation_id'], $_POST['quotation_no']);

	//var_dump($arr);

	echo $arr;
} else if (brp_strtolower($POST['mode']) == "load_product_history") {
	$row = get_product_history($dbcon, $_POST['cust_id'], $_POST['product_id'], $_POST['eid'], 1);
	echo $row;
} else if (strtolower($POST['mode']) == "get_gst_statecode") {
	$arr = get_crm_gst_statecode($dbcon, $POST['cust_id']);
	echo $arr;
}
/* END */

/*
	*  Code by Sanat :: 12-08-2021
		Comment :: added send quotation mail button
		START
	*/ else if (strtolower($POST['mode']) == "send_quotation_mail") {

	// send quotations code
	$quot_qry = "select quot.*,cust.cust_name,cust.cust_email,usr.user_mail from tbl_quotation as quot 
			left join tbl_customer as cust on cust.cust_id=quot.cust_id 
			left join users as usr on usr.user_id=quot.user_id
			where quotation_id =" . $POST['quotation_id'];
	$quot_data = brp_mysqli_fetch_assoc($dbcon->query($quot_qry));

	// Send Mail
	$from_email_id = ($quot_data['user_mail']) ? $quot_data['user_mail'] : ADMIN_EMAIL;
	$to_email_id = ($quot_data['cust_email']) ? $quot_data['cust_email'] : '';

	// $Vdata = file_get_contents('https://'.$_SERVER["SERVER_ADDR"].ROOT.CRM_ROOT.'/quotation_print/'.$POST['quotation_id']);
	// $loaddata = file_put_contents($POST['quotation_id'].'.pdf', $Vdata);


	$curl = curl_init();
	$url = 'https://' . $_SERVER["SERVER_NAME"] . ROOT . CRM_ROOT . 'quotation_print/' . $POST['quotation_id'] . '/1';

	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"postman-token: b2515d8a-6317-fda2-c72c-27f9d51f823c"
		),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
		$arr['res'] = '0';
	} else {

		$path = $_SERVER['DOCUMENT_ROOT'] . ROOT . 'crm/' . trim($response);
		$subject = ucwords($POST['subject']); //'INQUIRY ACKNOWLEGEMENT';
		$content = 'Dear Sir/Madam,<br><br>';
		$content .= 'Greetings from Vipul Copper...!<br><br>';
		$content .= 'Please find attached quotation copy.<br><br>';
		$content .= 'If you have any queries we will be in touch with you.';
		$content .= 'We look forward to an association with you.<br><br>';
		$content .= 'Thank you.';

		send_mail($dbcon, [$to_email_id], $subject, $content, $from_email_id, '', [trim($path)], [], 1);
		unlink(trim($path));
		$arr['res'] = '1';
	}


	echo json_encode($arr);
} else if (strtolower($POST['mode']) == "get_tax_details_table") {
	$quotation_id = $POST['invoice_id'];
	$resp = '';
	if (!empty($quotation_id)) {
		$query = "SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_quotation_trn` where quotation_id='$quotation_id' and quot_trn_status !=2 group by cgst_tax_per,sgst_tax_per,igst_tax_per";
	} else {
		$query = "SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_quotation_trn` where user_id='" . $_SESSION['user_id'] . "' and quot_trn_status = 3 and company_id = '" . $_SESSION['company_id'] . "' group by cgst_tax_per,sgst_tax_per,igst_tax_per";
	}

	$rs_prel = $dbcon->query($query);

	if (!empty($quotation_id)) {
		$rs_prel_fetch = brp_mysqli_fetch_assoc($dbcon->query("SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_quotation_trn` where quotation_id='$quotation_id' and quot_trn_status !=2"));
	} else {
		$rs_prel_fetch = brp_mysqli_fetch_assoc($dbcon->query("SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_quotation_trn` where user_id='" . $_SESSION['user_id'] . "' and quot_trn_status = 3 and company_id = '" . $_SESSION['company_id'] . "'"));
	}
	$rs_prel_num_rows = mysqli_num_rows($rs_prel);
	//print_r($rs_prel_fetch);exit;
	$resp = '';
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
	if (($rs_prel_fetch['igst_rate'] != 0)) {
		$resp .= '<th  class="text-center">IGST</th>';
	}


	$resp .= '</tr>';

	if ($rs_prel_num_rows > 0) {
		$taxRate = brp_mysqli_fetch_all($rs_prel);
		//print_r($taxRate);exit;
		$cnt = 1;
		$cntloop = 0;
		foreach ($taxRate as $taxdetail) {
			$gst_tax_per = ($taxdetail['cgst_tax_per'] != 0 || $taxdetail['sgst_tax_per'] != 0) ? ($taxdetail['cgst_tax_per'] + $taxdetail['sgst_tax_per']) : $taxdetail['igst_tax_per'];

			$gst_tax_rate = ($taxdetail['cgst_rate'] != 0 || $taxdetail['sgst_rate'] != 0) ? ($taxdetail['cgst_rate'] + $taxdetail['sgst_rate']) : $taxdetail['igst_rate'];

			$gst_tax_rate_conv = ($taxdetail['cgst_rate'] != 0 || $taxdetail['sgst_rate'] != 0) ? ($taxdetail['cgst_rate_conv'] + $taxdetail['sgst_rate_conv']) : $taxdetail['igst_rate_conv'];

			if ($taxdetail['cgst_tax_per'] != 0 || $taxdetail['sgst_tax_per'] != 0) {
				$resp .= '<tr>
				<th class="text-center">' . $cnt . '</th>
				<th class="text-center">' . number_format($gst_tax_per, 2, ".", "") . '%' . '</th>
				<th class="text-center">';
				if ($POST['currency_id'] == $_SESSION['currency_id']) {
					$resp .= $taxdetail['product_amount'] . '</th>
					<th class="text-center">' . $gst_tax_rate;
				} else {
					$resp .= $taxdetail['product_amount_conv'] . '</th>
					<th class="text-center">' . $gst_tax_rate_conv;
				}
				$resp .= '</th>
				<th class="text-center">' . number_format(($taxdetail['cgst_tax_per']), 2, ".", "") . '%' . '</th>
				<th class="text-center">' . number_format(($taxdetail['sgst_tax_per']), 2, ".", "") . '%' . '</th>
				</tr>';
				if (!empty($POST['addontax1']) && $cntloop == 0) {
					foreach ($POST['addontax1'] as $addtax) {
						$cnt++;
						$exp_addtax = explode("-", $addtax);
						if ($exp_addtax[1] != 0) {
							$resp .= '<tr>
							<th class="text-center">' . $cnt . '</th>
							<th class="text-center">' . number_format($exp_addtax[1], 2, ".", "") . '%' . '</th>
							<th class="text-center">' . ($exp_addtax[2]) . '</th>
							<th class="text-center">' . number_format($exp_addtax[0], 2, ".", "") . '</th>
							<th class="text-center">' . number_format(($exp_addtax[1] / 2), 2, ".", "") . '%' . '</th>
							<th class="text-center">' . number_format(($exp_addtax[1] / 2), 2, ".", "") . '%' . '</th>
							</tr>';
						}
					}
					$cntloop = 1;
				}
			}

			if ($taxdetail['igst_tax_per'] != 0) {
				$resp .= '<tr>
				<th class="text-center">' . $cnt . '</th>
				<th class="text-center">' . number_format($gst_tax_per, 2, ".", "") . '%' . '</th>
				<th class="text-center">';
				if ($POST['currency_id'] == $_SESSION['currency_id']) {
					$resp .= $taxdetail['product_amount'] . '</th>
					<th class="text-center">' . $gst_tax_rate;
				} else {
					$resp .= $taxdetail['product_amount_conv'] . '</th>
					<th class="text-center">' . $gst_tax_rate_conv;
				}
				$resp .= '</th>
				<th class="text-center">' . number_format(($taxdetail['igst_tax_per']), 2, ".", "") . '%' . '</th>
				</tr>';
				if (!empty($POST['addontax1']) && $cntloop == 0) {
					foreach ($POST['addontax1'] as $addtax) {
						$cnt++;
						$exp_addtax = explode("-", $addtax);
						//echo '<pre>';print_r($exp_addtax);
						if ($exp_addtax[1] != 0) {
							$resp .= '<tr>
							<th class="text-center">' . $cnt . '</th>
							<th class="text-center">' . number_format($exp_addtax[1], 2, ".", "") . '%' . '</th>
							<th class="text-center">' . ($exp_addtax[2]) . '</th>
							<th class="text-center">' . number_format($exp_addtax[0], 2, ".", "") . '</th>
							<th class="text-center">' . number_format(($exp_addtax[1]), 2, ".", "") . '%' . '</th>
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
	$invoice_id = $POST['invoice_id'];
	$company_state = get_company_data($dbcon, $_SESSION['company_id']);
	$resp = '';
	$quot_type = $POST['quot_type'];


	if (!empty($invoice_id)) {
		$query = "SELECT sum(cgst_tax_rate) as cgst_rate,sum(sgst_tax_rate) as sgst_rate,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_quotation_trn` where quotation_id='$invoice_id' and quot_trn_status!=2";
	} else {
		$query = "SELECT sum(cgst_tax_rate) as cgst_rate,sum(sgst_tax_rate) as sgst_rate,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_quotation_trn` where user_id='" . $_SESSION['user_id'] . "' and quot_trn_status = 3 and company_id = '" . $_SESSION['company_id'] . "'";
	}
	$rs_prel = brp_mysqli_fetch_assoc($dbcon->query($query));

	$row['isTcs'] = "0";
	$getCompanyConfig = getCompanyConfiguration($dbcon);
	$custLedgerDetails = get_cust_data_arr($dbcon, $POST['cust_id']);
	$get_bill_sundry = get_bill_sundry_ledger($dbcon, 1);

	foreach ($get_bill_sundry as $billsundry) {

		if ((($rs_prel['cgst_rate'] != 0) && $billsundry['l_name'] == 'CGST') || (($rs_prel['sgst_rate'] != 0) && $billsundry['l_name'] == 'SGST')) {

			$gstValue = ($billsundry['l_name'] == 'CGST') ? $rs_prel['cgst_rate'] : (($billsundry['l_name'] == 'SGST') ? $rs_prel['sgst_rate'] : '');

			$gstValue_conv = ($billsundry['l_name'] == 'CGST') ? $rs_prel['cgst_rate_conv'] : (($billsundry['l_name'] == 'SGST') ? $rs_prel['sgst_rate_conv'] : '');

			if (!empty($POST['addontax1'])) {
				$addontax = $POST['addontax1'] / 2;
			}
			$resp .= '<div class="form-group">
			<label class="col-md-5 control-label">' . $billsundry['l_name'] . ' <span class="currency_icon"> </span></label>
			<div class="col-md-5 col-xs-11">
			<input id="' . $billsundry['l_name'] . '" name="bill_sundry_tax[' . $billsundry['l_id'] . ']" type="number" class="form-control gst" title="' . $billsundry['l_name'] . '"  value="' . (($POST['currency_id'] == $company_state['currency_id']) ? round($gstValue + $addontax, 2) : round($gstValue_conv + $addontax, 2)) . '" placeholder="' . $billsundry['l_name'] . '" readonly >
			</div>
			</div>';
		}
		if (($rs_prel['igst_rate'] != 0) && $billsundry['l_name'] == 'IGST') {
			if (!empty($POST['addontax1'])) {
				$addontax = $POST['addontax1'];
			}
			$resp .= '<div class="form-group">
			<label class="col-md-5 control-label">' . $billsundry['l_name'] . ' <span class="currency_icon"> </span></label>
			<div class="col-md-5 col-xs-11">
			<input id="' . $billsundry['l_name'] . '" name="bill_sundry_tax[' . $billsundry['l_id'] . ']" type="number" class="form-control gst" title="' . $billsundry['l_name'] . '"  value="' . (($POST['currency_id'] == $company_state['currency_id']) ? ($rs_prel['igst_rate'] + $addontax) : ($rs_prel['igst_rate_conv'] + $addontax)) . '" placeholder="' . $billsundry['l_name'] . '" readonly >
			</div>
			</div>';
		}

		if (($billsundry['l_name'] == 'TCS') && ($getCompanyConfig['enable_tcs_reporting'] == 1) && ($custLedgerDetails['enable_tcs'] == 1) && ($POST['gross'] >= $getCompanyConfig['gross_balance_limit'])) {
			$row['isTcs'] = "1";
			$total_tcs_calculate = $rs_prel['product_amount'] + $gstValue + $rs_prel['igst_rate'];
			$resp .= '<div class="form-group">
			<label class="col-md-5 control-label">' . $billsundry['l_name'] . ' <span class="currency_icon"> </span></label>
			<div class="col-md-5 col-xs-11">
			<input id="' . $billsundry['l_name'] . '" name="bill_sundry_tax[' . $billsundry['l_id'] . ']" type="number" class="form-control gst" title="' . $billsundry['l_name'] . '"  value="' . round((($total_tcs_calculate * $billsundry['tax_value']) / 100), 2) . '" placeholder="' . $billsundry['l_name'] . '" readonly >
			<input type="hidden" name="tcs_per" id="tcs_per" value="' . $billsundry['tax_value'] . '" >
			</div>
			</div>';
		}
	}

	if (!empty($invoice_id)) {
		$qry_add = $dbcon->query("SELECT sum((tc.tax_per*trn.product_amount_conv)/100) as add_sum , trn.*,l.l_name,l.l_id,t.tax_cat_id from tbl_quotation_trn as trn left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat 
			left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
			left join tbl_ledger as l on l.l_id=tc.tax_id
			where tc.tax_additional='1' and trn.quotation_id='$invoice_id' and trn.quot_trn_status!=2 and tc.isdelete='0' group by tc.tax_id 
			");
	} else {
		$qry_add = $dbcon->query("SELECT sum((tc.tax_per*trn.product_amount_conv)/100) as add_sum , trn.*,l.l_name,l.l_id,t.tax_cat_id from tbl_quotation_trn as trn left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat 
			left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
			left join tbl_ledger as l on l.l_id=tc.tax_id
			where tc.tax_additional='1' and trn.user_id='" . $_SESSION['user_id'] . "' and trn.quot_trn_status = 3 and trn.company_id = '" . $_SESSION['company_id'] . "' and tc.isdelete='0' group by tc.tax_id 
			");
	}
	while ($row1 = brp_mysqli_fetch_array($qry_add)) {

		//$tax_rate = ($row1['tax_per']*$row1['product_amount'])/100;
		$resp .= '<div class="form-group">
		<label class="col-md-5 control-label">' . $row1['l_name'] . ' <span class="currency_icon"> </span></label>
		<div class="col-md-5 col-xs-11">
		<input id="' . $row1['l_name'] . '" name="bill_sundry_tax[' . $row1['l_id'] . ']" type="number" class="form-control gst" title="' . $row1['l_name'] . '"  value="' . $row1['add_sum'] . '" placeholder="' . $billsundry['l_name'] . '" readonly >
		</div>
		</div>';
	}

	$row['resp'] = $resp;

	echo json_encode($row);
} else if (strtolower($POST['mode']) == "update_total") {


	//update total , net total , general books entry at edit time start - dhaval 
	$bill_sundry_tax = array_filter($POST['bill_sundry_tax']);

	if ($POST['invoice_id'] > 0) {
		if ($POST['currency_id'] == $_SESSION['currency_id']) {
			$update_invoice['g_total'] 			= $POST['g_total'];
			$update_invoice['g_total_conv']		= $POST['g_total'] * $POST['currency_rate'];
			$update_invoice['basic_total'] 		= $POST['basic_total'];
			$update_invoice['basic_total_conv']	= $POST['basic_total'] * $POST['currency_rate'];
		} else {
			$update_invoice['g_total'] 			= $POST['g_total'] * $POST['currency_rate'];
			$update_invoice['g_total_conv']		= $POST['g_total'];
			$update_invoice['basic_total'] 		= $POST['basic_total'] * $POST['currency_rate'];
			$update_invoice['basic_total_conv']	= $POST['basic_total'];
		}
		update_record("tbl_quotation", $update_invoice, " quotation_id=" . $POST['invoice_id'], $dbcon);

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

			update_record("tbl_bill_sundry_transaction", $info_sundry_tax, " sundry_ledger_id=" . $bill_sundry_tax_id . " and sundry_voucher_table='tbl_quotation' and sundry_voucher_id='$POST[invoice_id]'", $dbcon);

			/*$info_general_sundry['amount'] = $bill_sundry_tax_amount;
			$info_general_sundry['cdate']	= date("Y-m-d H:i:s");
			$info_general_sundry['user_id']	= $_SESSION['user_id'];
			$info_general_sundry['company_id']	= $_SESSION['company_id'];*/

			// update_record("tbl_general_book",$info_general_sundry," ledger_id=".$bill_sundry_tax_id." and table_name='tbl_bill_sundry_transaction'" ,$dbcon);
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
} else if (strtolower($POST['mode']) == "get_bill_sundry_label") {
	$sundry_id = $POST['sundry_id'];

	$row = get_sundry_details($dbcon, $sundry_id);

	echo $row['sundry_amount_of'];
} else if (strtolower($POST['mode']) == "get_bill_sundry_details") {
	$invoice_id = $POST['invoice_id'];
	//echo '<pre>'; print_r($POST);exit;
	$q = $dbcon->query("SELECT * from tbl_ledger_bill_sundry where isdelete=0 and sundry_ledger_id=" . $POST['sundry_ledger_id'] . " and company_id = " . $_SESSION['company_id'] . " ");
	$resp = $q->fetch_assoc();

	$q_tax = $dbcon->query("select tax_gst from tbl_tax_category where tax_cat_id=" . $resp['sundry_gst'] . " ");
	$resp_tax = $q_tax->fetch_assoc();

	$basic_total = $POST['basic_amount'];
	$netamount = $POST['netamount'];
	$taxableamount = $POST['taxableamount'];

	$default_amount = $POST['default_amount'];

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
		$info_sundry_addon['sundry_ledger_id']		= $POST['sundry_ledger_id'];
		$info_sundry_addon['sundry_voucher_id']		= $invoice_id;
		$info_sundry_addon['sundry_voucher_type']	= QUOTATION_VOUCHER;
		$info_sundry_addon['sundry_voucher_table']	= 'tbl_quotation';
		$info_sundry_addon['cdate']					= date("Y-m-d H:i:s");
		$info_sundry_addon['user_id']				= $_SESSION['user_id'];
		$info_sundry_addon['company_id']			= $_SESSION['company_id'];
		$info_sundry_addon['sundry_gst_per']		= $taxgst;
		//$info_sundry_addon['sundry_amount']			=$pervalue;
		//$info_sundry_addon['sundry_gst_amount']		= $taxvl;
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
	}
	//if invoice is edit time insert data in database end - dhaval

	if ($resp['sundry_amount_of'] == 1) {

		$per_amount_show = "";
	} else {

		$per_amount_show = '<strong> (' . $default_amount . '%)</strong>';
	}
	$pervalue = round($pervalue, 2);
	echo json_encode($finalNetAmount . ',' . $pervalue . ',' . $per_amount_show . ',' . $invoice_id . ',' . $taxvl . ',' . $resp_tax['tax_gst']);
} else if (strtolower($POST['mode']) == "get_all_bill_sundry") {
	$invoice_id = $POST['invoice_id'];

	$q = $dbcon->query("select b.*,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id left join tbl_ledger as le on le.l_id=b.sundry_ledger_id where b.sundry_voucher_id='$invoice_id' and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0' ");

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
			$str .= '<div class="form-group R' . $cnt . '">
					<label class="col-md-5 control-label">' . $r['l_name'] . ' <span class="currency_icon"> </span></label>
					<div class="col-md-4">
					<input id="sundry_name" name="bill_sundry_addon[' . $r['l_id'] . ']" type="hidden" value="' . $r['sundry_amount_conv'] . '">
					<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="' . $r['sundry_amount_conv'] . '" readonly placeholder="Amount">
					</div>
					<div class="col-md-3">
					<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
					type="button" value="R' . $cnt . '" onclick="removeSundry(\'\',\'' . $r['sundry_amount_conv'] . '\',this.value,\'' . $r['sundry_id'] . '\')"><i class="fa fa-times"></i></button>
					</div>
					</div>';
		} else {
			$str .= '<div class="form-group R' . $cnt . '">
					<label class="col-md-5 control-label">' . $r['l_name'] . ' <span class="currency_icon"> </span></label>
					<div class="col-md-4">
					
					<input id="sundry_name" name="bill_sundry_addon[' . $r['l_id'] . ']" type="hidden" value="' . $r['sundry_amount_conv'] . '">
					<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="' . $r['sundry_amount_conv'] . '" readonly placeholder="Amount">
					<input class="addontax" name="bill_sundry_addon_tax[' . $r['l_id'] . ']" type="hidden" value="' . $r['sundry_gst_amount_conv'] . '-' . $r['sundry_gst_per'] . '-' . $r['sundry_amount_conv'] . '" >
					</div>
					<div class="col-md-3">
					<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
					type="button" value="R' . $cnt . '" onclick="removeSundry(' . $r['l_id'] . ',\'' . $r['sundry_amount_conv'] . '\',this.value,\'' . $r['sundry_id'] . '\')"><i class="fa fa-times"></i></button>
					</div>
					</div>';
		}

		$cnt++;
		//$str.=$r['sundry_amount'];
	}

	echo $str;
	//echo json_encode($resp);
} else if (strtolower($POST['mode']) == "remove_sundry") {

	$ledger_id = $POST['ledger_id'];

	$info['isdelete'] = 1;

	$updateid = update_record('tbl_bill_sundry_transaction', $info, "sundry_id=" . $POST['ledger_id'], $dbcon);

	$info_general['genral_book_status'] = 2;

	// $updateid=update_record('tbl_general_book', $info_general,"table_id=".$POST['ledger_id']." and table_name='tbl_bill_sundry_transaction'" , $dbcon);

} else if (strtolower($POST['mode']) == "load_party_po_dtl") {
	$qt_qry = "SELECT qt.*, country.country_name, state.state_name, city.city_name, cust.cust_name, cust.cust_mobile, cust.cust_email, cust.cust_gst, addr.c_add_address, addr.c_add_location, addr.c_add_street from tbl_quotation as qt
			left join tbl_customer as cust on cust.cust_id=qt.cust_id
			left join tbl_cust_address as addr on addr.cust_id=cust.cust_id
			left join country_mst as country on country.countryid=addr.c_add_country
			left join state_mst as state on state.stateid=addr.c_add_state
			left join city_mst as city on city.cityid=addr.c_add_city
			where qt.quotation_id=" . $POST['quotation_id'];
	$qt_rel = mysqli_fetch_assoc($dbcon->query($qt_qry));

	//Party PO Details Table View
	$str = '';
	$stri = '';
	$str .= '<div class="form-group">
			<table class="display table table-bordered table-striped">
			<tr>
			<td colspan="2"><strong>Company Name:</strong> ' . $qt_rel['cust_name'] . '</td>
			<td><strong>Contact No.:</strong> ' . $qt_rel['cust_mobile'] . '</td>
			</tr>
			<tr>
			<td colspan="2"><strong>Address:</strong> ' . $qt_rel['c_add_address'] . ' ' . $qt_rel['c_add_location'] . ' ' . $qt_rel['c_add_street'] . '</td>
			<td><strong>GST No.:</strong> ' . $qt_rel['gst_no'] . '</td>
			</tr>
			<tr>
			<td><strong>City:</strong> ' . $qt_rel['city_name'] . '</td>
			<td><strong>State:</strong> ' . $qt_rel['state_name'] . '</td>
			<td><strong>Country:</strong> ' . $qt_rel['country_name'] . '</td>
			</tr>
			<tr>
			<td><strong>Quotation No:</strong> ' . $qt_rel['quotation_no'] . '</td>
			<td><strong>Quotation Date:</strong> ' . date("d-M-Y", strtotime($qt_rel["quotation_date"])) . '</td>
			<td><strong>Quotation Amount:</strong> ' . $qt_rel['g_total'] . '</td>
			</tr>';
	$str .= '</table></div>
			<hr/>';

	$qt_pro_qry = "select trn.*,if(trn.project_wise=0,(select product_name from product_mst as pro where pro.product_id=trn.product_id) ,(select product_name from product_mst as pro where pro.product_id=trn.product_id)) as product_name, unit.unit_name from tbl_quotation_trn as trn 
			left join product_mst as pro on pro.product_id=trn.product_id
			left join unit_mst as unit on unit.unitid=trn.unitid
			where trn.quot_trn_status=0 and trn.quotation_id=" . $POST['quotation_id'];
	$result = $dbcon->query($qt_pro_qry);
	$stri .= '<div class="form-group">
			<table class="display table table-bordered table-striped" style="width:100%; table-layout: fixed;">
			<tr>	
			<th width="25%" class="text-center">Product Name</th>
			<th width="10%" class="text-center">Quantity</th>
			<th width="10%" class="text-center">Rate</th>
			<th width="10%" class="text-center">Discount</th>
			<th width="15%" class="text-center">Tax Details</th>
			<th width="20%" class="text-center">Amount</th>
			</tr>
			<tbody>';
	$i = 1;
	while ($rel = mysqli_fetch_assoc($result)) {
		$cgst_tax = "";
		$sgst_tax = "";
		$igst_tax = "";

		if ($rel['cgst_tax_per'] != 0) {
			$cgst_tax = "<Strong>CGST (" . $rel['cgst_tax_per'] . ") : </strong>" . $rel['cgst_tax_rate'];
		}

		if ($rel['sgst_tax_per'] != 0) {
			$sgst_tax = "<Strong>SGST (" . $rel['sgst_tax_per'] . ") : </strong>" . $rel['sgst_tax_rate'];
		}

		if ($rel['igst_tax_per'] != 0) {
			$igst_tax = "<Strong>IGST (" . $rel['igst_tax_per'] . ") : </strong>" . $rel['igst_tax_rate'];
		}
		$stri .= '<tr>
				<td style="vertical-align:top;">
				<strong>' . $rel['product_name'] . '</strong>
				</td>
				<td style="vertical-align:top;" class="text-center">
				' . $rel['product_qty'] . ' ' . $rel['unit_name'] . '
				</td>
				<td style="vertical-align:top;" class="text-right">
				' . $rel['product_rate'] . '
				</td>
				<td style="vertical-align:top;" class="text-right">
				' . $rel['product_discount'] . ' (' . $rel['discount_per'] . '%)
				</td>	
				<td>
				' . $cgst_tax . '<br>' . $sgst_tax . '<br>' . $igst_tax . '
				</td>
				<td style="vertical-align:top;" class="text-right">
				<input type="hidden" name="amount[]" value="' . $rel['product_amount'] . '">
				' . $rel['product_amount'] . '
				</td>
				</tr>';
		$i++;
	}
	$stri .= '</tbody>
			</table>
			</div>
			<hr/>';

	$strj = '';
	$strj = '<div class="form-group">
			<table class="display table table-bordered table-striped" style="width:100%; table-layout: fixed;">
			<tr>
				<th width="50%" class="text-center">Document Name</th>
				<th width="40%" class="text-center">Document File</th>
				<th width="10%" class="text-center">Action</th>
			</tr>
			<tbody>
			<tr>
				<td>
					<input type="text" class="form-control" id="inq_attch_doc_name" name="inq_attch_doc_name" placeholder="Document Name">
				</td>
				<td>
					<input type="file" class="form-control" id="inq_attch_file" name="inq_attch_file">
				</td>
				<td>
					<button type="button" class="btn btn-primary" id="inq_attch_btn" onclick="add_attch_doc_field()">Add</button>
				</td>
			</tr>
			</tbody>
			</table>
			</div>
			<hr/>';

	$qt_rel['mod_quot_comp_div_sec'] = $str;
	$qt_rel['mod_quot_pro_div_sec'] = $stri;
	$qt_rel['mod_quot_doc_div_sec'] = $strj;
	echo json_encode($qt_rel);
} else if (strtolower($POST['mode']) == "load_revision_data") {
	$str .= '<table class="display table table-bordered table-striped" style="width:100%; table-layout: fixed;">
				<tr>
					<th>Sr.no</th>
					<th>Quotation No</th>
					<th>Quotation Date</th>
					<th>Customer</th>
					<th>Amout</th>
					<th>Date & Time</th>
					<th>Action</th>
				</tr>';
	$query = "select quot.*,cust.cust_name from tbl_quotation as quot
			left join tbl_customer as cust on cust.cust_id=quot.cust_id
			where quotation_id != " . $POST['quotation_id'] . " and start_quotation_id=" . $POST['start_quotation_id'] . " and quotation_status=0";

	$ex = $dbcon->query($query);
	$i = 1;
	while ($row = brp_mysqli_fetch_array($ex)) {
		$str .= "<tr>
					<td>" . $i . "</td>
					<td>" . $row['quotation_no'] . "</td>
					<td>" . date('d-m-Y', strtotime($row['quotation_date'])) . "</td>
					<td>" . $row['cust_name'] . "</td>
					<td>" . $row['g_total_conv'] . "</td>
					<td>" . $row['cdate'] . "</td>";
		if (in_array(QUOTATION_SLUG_PRINT, $bulkAccessArray)) {
			$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='" . $_SESSION['company_id'] . "'");
			$rels = mysqli_fetch_assoc($menusql);
			$menu_show_permissions = explode(",", $rels['print_permission']);
			$sql = $dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 1 AND approve_status = 1 AND status = 0 ORDER BY priority");
			while ($res = mysqli_fetch_assoc($sql)) {
				if (in_array($res['id'], $menu_show_permissions)) {
					$str .= '<td><a class="btn btn-xs btn-primary" data-original-title="' . $res['print_name'] . '" data-toggle="tooltip" data-placement="top" target="_blank" href="' . ROOT . PRINT_ROOT . $res['page_path'] . '/' . $row['quotation_id'] . '?' . time() . '" style="background: ' . $res['icon_color'] . '; border-color: ' . $res['icon_color'] . ';"><i class="' . $res['fa_icon'] . '"></i></a></td>';
				}
			}
		}
		$str .= "</tr>";
		$i++;
	}
	$str .= '</table>';

	echo $str;
}

////////////////////////////////////////////Harshil   - 21-9-2022 /////////////////////////////////////////////////////////////////
else if (strtolower($POST['mode']) == "open_accesorice_wise_product_list") {
	$html = '
			 <input type="hidden" id="pid_l" value=' . $POST['product_id'] . ' />
			<div class="row">
                                        <div class="col-md-12 margin_row">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th>Accessories Product Nameeee</th>
													<th>Qty</th>
													<th>Rate</th>
													<th>Total</th>
													
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input id="acc_product_id_l" name="acc_product_id_l" style="width:100%;" placeholder="Select Product" onchange="load_product_dtls_pop_list(this.value);get_hsn_pop_list(this.value);" />
														<br><label id="current_stock_pop_l" style="display: none;"></label><strong class="hsncode_pop_l" style="display:none;color:blue">HSN Code : <span id="hsncode_pop_l"></span></strong><br>
                                                    </td>
													 <td>
                                                        <input type="text" class="form-control" name="acc_product_qty_l" id="acc_product_qty_l" onkeyup="get_amount_pop_list();" placeholder="QTY" />
														<strong class="unit_pop_l" style="display:none;color:blue"><span id="unit_pop_l"></span></strong>
                                                    </td>
													 <td>
                                                        <input type="text" class="form-control" name="acce_rate_l" id="acce_rate_l" onkeyup="get_amount_pop_list();" placeholder="Rate" />
                                                    </td>
													<td>
                                                        <input type="text" class="form-control" name="acc_amount_l" id="acc_amount_l" placeholder="Total" />
                                                    </td>
													
													</tr>
													<tr>
													<td colspan="4">
													 <div class="form-group">
														<label for="Product Description" class="col-md-4 control-label">Description</label>
														<div class="col-md-12 col-xs-11">
														<textarea class="form-control" id="acc_product_desc_l" name="acc_product_desc_l" placeholder="Enter Product Description"></textarea>
														</div>
													</div>
													</td>
													</tr>
													
                                            </table>
                                        </div>
                                    </div>';
	$row['html_data'] = $html;
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "add_field_list") {

	$pid = $POST['pid'];

	$inq_qry = "select * from tbl_quotation_trn  where  quot_trn_id=" . $pid;

	$inq_qry_rs = $dbcon->query($inq_qry);

	$inq_rel = brp_mysqli_fetch_assoc($inq_qry_rs);

	$inq_unit = "select product_base_unit,product_spec,product_spec_id,product_hsn from product_mst  where  product_id=" . $POST['product_id'];

	$inq_unit_rs = $dbcon->query($inq_unit);

	$inq_rel_unit = brp_mysqli_fetch_assoc($inq_unit_rs);





	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$company_state = get_company_data($dbcon, $_SESSION['company_id']);
	//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
	//$sale_gst = get_tax_cat_by_hsn($dbcon,$POST['product_hsn_code']);

	if ($POST['gst_type'] == 3) {
		$sale_gst['tax_gst'] = 0.1;
		$sale_gst['tax_cat_id'] = 0;
	} else if ($POST['gst_type'] == 4) {
		$sale_gst['tax_gst'] = 0;
		$sale_gst['tax_cat_id'] = 0;
	} else if ($POST['gst_type'] == 5) {
		$sale_gst['tax_gst'] = 5;
		$sale_gst['tax_cat_id'] = 0;
	} else if ($POST['gst_type'] == 6) {
		$sale_gst['tax_gst'] = 12;
		$sale_gst['tax_cat_id'] = 0;
	} else if ($POST['gst_type'] == 7) {
		$sale_gst['tax_gst'] = 18;
		$sale_gst['tax_cat_id'] = 0;
	} else if ($POST['gst_type'] == 8) {
		$sale_gst['tax_gst'] = 24;
		$sale_gst['tax_cat_id'] = 0;
	} else {
		$sale_gst = get_tax_cat_by_hsn_id($dbcon, trim($inq_rel_unit['product_hsn']));
	}

	$cgst_tax_rate = 0;
	$cgst_tax_rate_conv = 0;
	$sgst_tax_rate = 0;
	$sgst_tax_rate_conv = 0;
	$igst_tax_rate = 0;
	$igst_tax_rate_conv = 0;
	if (($company_state['stateid'] == $POST['cust_stateid'])) {
		$gst = $sale_gst['tax_gst'] / 2;
		$cgst_tax_per = $gst;
		$cgst_tax_rate = ($gst * $POST['product_amount']) / 100;
		$cgst_tax_rate_conv = ($inq_rel['currency_rate'] * $gst * $POST['product_amount']) / 100;
		$sgst_tax_per = $gst;
		$sgst_tax_rate = ($gst * $POST['product_amount']) / 100;
		$sgst_tax_rate_conv = ($inq_rel['currency_rate'] * $gst * $POST['product_amount']) / 100;
	} else {
		$igst_tax_per = $sale_gst['tax_gst'];
		$igst_tax_rate = ($sale_gst['tax_gst'] * $POST['product_amount']) / 100;
		$igst_tax_rate_conv = ($inq_rel['currency_rate'] * $sale_gst['tax_gst'] * $POST['product_amount']) / 100;
	}

	$info1['inquiry_type']	= $inq_rel['inquiry_type'];
	$info1['project_wise']	= ($POST['inquiry_type'] == 2) ? 1 : 0;
	$info1['pid']			= $POST['pid'];
	$info1['product_id']	= $POST['product_id'];
	//$info1['cat_id']	    = $POST['cat_id'];
	$info1['product_desc']	= text_rnremove($_POST['product_desc']);
	$info1['product_spec']	= text_rnremove($inq_rel_unit['product_spec']);
	$info1['product_spec_id'] =  $inq_rel_unit['product_spec_id'];
	//$info1['product_other_desc']	= text_rnremove($_POST['product_other_desc']);
	//$info1['level_id']		= $POST['level_id'];
	$info1['unitid']		= $inq_rel_unit['product_base_unit'];
	//$info1['act_amt_flag']	= $POST['act_amt_flag'];
	$info1['product_qty']	= $POST['product_qty'];
	//$info1['discount_per']	= $POST['discount_per'];
	//$info1['formulaid']= $POST['formulaid'];
	//$info1['product_total']= $POST['product_total'];
	$info = get_product_common_tax($dbcon, $POST['product_amount'], '');
	$info1 = array_merge($info1, $info);
	$info1['user_id']		= $_SESSION['user_id'];
	$info1['company_id']	= $_SESSION['company_id'];
	$info1['currency_id']	= $inq_rel['currency_id'];
	$info1['currency_rate'] = $inq_rel['currency_rate'];

	$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0;
	$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0;
	$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0;

	if ($inq_rel['currency_id'] == $company_state['currency_id']) {
		$info1['product_rate']			= $POST['product_rate'];
		//$info1['product_discount']		= $POST['product_discount'];
		$info1['product_amount']		= $POST['product_amount'];
		$info1['cgst_tax_rate']			= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
		$info1['sgst_tax_rate']			= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
		$info1['igst_tax_rate']			= isset($igst_tax_rate) ? $igst_tax_rate : 0;
		$info1['product_total']			= $POST['product_amount'];

		$info1['product_rate_conv']		= $POST['product_rate'] * $inq_rel['currency_rate'];
		$info1['product_amount_conv']	= $POST['product_amount'] * $inq_rel['currency_rate'];
		$info1['product_discount_conv']	= $POST['product_discount'] * $inq_rel['currency_rate'];
		$info1['cgst_tax_rate_conv']	= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
		$info1['sgst_tax_rate_conv']	= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
		$info1['igst_tax_rate_conv']	= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;
		$info1['product_total_conv']	= $POST['product_amount'] * $inq_rel['currency_rate'];
	} else {
		$info1['product_rate']			= $POST['product_rate'] * $inq_rel['currency_rate'];
		$info1['product_discount']		= $POST['product_discount'] * $inq_rel['currency_rate'];;
		$info1['product_amount']		= $POST['product_amount'] * $inq_rel['currency_rate'];
		$info1['cgst_tax_rate']			= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
		$info1['sgst_tax_rate']			= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
		$info1['igst_tax_rate']			= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;
		$info1['product_total']			= $POST['product_amount'] * $inq_rel['currency_rate'];

		$info1['product_rate_conv']		= $POST['product_rate'];
		$info1['product_amount_conv']	= $POST['product_amount'];
		//$info1['product_discount_conv']	= $POST['product_discount'];
		$info1['cgst_tax_rate_conv']	= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
		$info1['sgst_tax_rate_conv']	= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
		$info1['igst_tax_rate_conv']	= isset($igst_tax_rate) ? $igst_tax_rate : 0;
		$info1['product_total_conv']	= $POST['product_amount'];
	}


	$info1['product_tax_cat']	= $sale_gst['tax_cat_id'];
	// var_dump($info1);
	$table = 'tbl_quotation_trn';
	$tableid = 'quot_trn_id';
	if (!empty($POST['quotation_id'])) {
		$info1['quotation_id'] = $POST['quotation_id'];
	} else {
		$info1['quot_trn_status'] = 3;
	}



	$inserid = add_record($table, $info1, $dbcon, $branch_id);
} else if (strtolower($POST['mode']) == "add_accessories_data") {
	// $inquiry_id = $POST['eid'];
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	//if($inquiry_id==''){
	$product_id = $POST['product_id'];
	$inquiry_type = $POST['inquiry_type'];

	//$update['inq_access_status'] = 2;
	//update_record('tbl_inq_access_trn', $update, "pid=".$product_id. " and inq_access_status = 3 and user_id='".$_SESSION['user_id']."' and company_id='".$_SESSION['company_id']."'", $dbcon);

	$deleteid = delete_record('tbl_quto_access_trn', "pid=" . $product_id . " and inq_access_status = 3 and user_id='" . $_SESSION['user_id'] . "' and company_id='" . $_SESSION['company_id'] . "'", $dbcon);

	$inq_qry = "select tpap.*,pm.product_sale_rate from tbl_product_acc_product tpap left join product_mst as pm on  pm.product_id = tpap.acc_product_id  where tpap.product_id=" . $product_id . " and tpap.company_id='" . $_SESSION['company_id'] . "'";

	$inq_qry_rs = $dbcon->query($inq_qry);
	if (brp_mysqli_num_rows($inq_qry_rs) > 0) {

		while ($inq_rel = brp_mysqli_fetch_assoc($inq_qry_rs)) {

			$info12['product_id']		= $inq_rel['acc_product_id'];
			$info12['pid']				= $inq_rel['product_id'];
			$info12['qty']				= $inq_rel['acc_product_qty'];
			$info12['acce_rate']		= $inq_rel['product_sale_rate'];

			if (!empty($inq_rel['product_sale_rate'])) {
				$info12['acc_amount']		= $inq_rel['product_sale_rate'] * $inq_rel['acc_product_qty'];
			} else {
				$info12['acc_amount']		= 0;
			}
			$info12['product_desc']		= $inq_rel['acc_product_desc'];
			$info12['inq_access_status'] = 3;
			$info12['company_id']		= $_SESSION['company_id'];
			$info12['user_id']			= $_SESSION['user_id'];
			//var_dump($info12);
			$inserid_sub = add_record("tbl_quto_access_trn", $info12, $dbcon, $branch_id);
		}
	}
} else if (strtolower($POST['mode']) == "fetch_accessories_qty") {


	$appData = array();
	$i = 1;
	$aColumns = array('tpm.product_name', 'tiat.inq_acc_id', 'tiat.product_id', 'tiat.pid', 'tiat.qty', 'tiat.acce_rate', 'tiat.acc_amount', 'tiat.product_desc');
	$sTable = "tbl_quto_access_trn as tiat";
	$isJOIN = array('left join product_mst as tpm on tpm.product_id=tiat.product_id');
	$sIndexColumn = "tiat.inq_acc_id";
	$where = "  tiat.pid='" . $POST['product_id'] . "' and tiat.inq_access_status=3 ";
	$isWhere = array($where);
	$hOrder = "tiat.inq_acc_id desc";
	include($path . 'include/pagging.php');
	$id = 1;
	$edit = $delete = '';
	foreach ($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['product_name'];
		$row_data[] = $row['qty'];
		$row_data[] = $row['acce_rate'];
		$row_data[] = $row['acc_amount'];
		$row_data[] = $row['product_desc'];


		$edit = '<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_accessories_product_pop(' . $row['inq_acc_id'] . ');" id="fieldtrnedit' . $i . '"><i class="fa fa-pencil"></i></button>';
		$delete = '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_accessories_product_pop(' . $row['inq_acc_id'] . ');" id="fieldtrnremove' . $i . '"><i class="fa fa-times"></i></button>';


		$row_data[] = $edit . ' ' . $delete;


		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode($output);
} else if (strtolower($POST['mode']) == "preedit_accessories_product") {
	$q = $dbcon->query("SELECT tpap.*,pm.product_name FROM tbl_quto_access_trn as tpap left join product_mst as pm on pm.product_id=tpap.product_id WHERE inq_acc_id= '$POST[id]'");
	$r = $q->fetch_assoc();
	echo json_encode($r);
} else if (strtolower($POST['mode']) == "delete_data_alternative_product_pop") {
	$deleteid = delete_record('tbl_quto_access_trn', "inq_acc_id=" . $POST['eid'] . " and inq_access_status = 3 and user_id='" . $_SESSION['user_id'] . "' and company_id='" . $_SESSION['company_id'] . "'", $dbcon);


	if ($deleteid)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "accessories_model_open") {
	$html = '<input type="hidden" id="pid" value=' . $POST['product_id'] . ' />
			<div class="row">
                <div class="col-md-12 margin_row">
                    <table class="table table-bordered">
                    	<tr>
                            <th>Accessories Product Name</th>
							<th>Qty</th>
							<th>Rate</th>
							<th>Total</th>
							<td>Action</td>
                        </tr>
                        <tr>
                            <td>
                                <input id="acc_product_id" name="acc_product_id" style="width:100%;" placeholder="Select Product" />
                            </td>
							<td>
                                <input type="text" class="form-control" name="acc_product_qty" id="acc_product_qty" placeholder="QTY" />
                            </td>
							<td>
                                <input type="text" class="form-control" name="acce_rate" id="acce_rate" placeholder="Rate" />
                            </td>
							<td>
                            	<input type="text" class="form-control" name="acc_amount" id="acc_amount" placeholder="Total" />
                            </td>
													<td rowspan="2"><input type="button" class="btn btn-primary" value="ADD" onclick="add_accessories_product_pop()" id="add_alternative_btn" /></td>
                                                    <input type="hidden" id="edit_id_accessories" value="" />
                                                    <input type="hidden" id="eid_accessories" value="" />
													</tr>
													<tr>
													<td colspan="4">
													 <div class="form-group">
														<label for="Product Description" class="col-md-4 control-label">Description</label>
														<div class="col-md-12 col-xs-11">
														<textarea class="form-control" id="acc_product_desc" name="acc_product_desc" placeholder="Enter Product Description"></textarea>
														</div>
													</div>
													</td>
													</tr>
													
                                            </table>
                                        </div>
                                    </div>';
	$row['html_data'] = $html;
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "add_accessories_product_pop") {

	$info1['product_id']		= $POST['acc_product_id'];
	$info1['pid']				= $POST['pid'];
	$info1['qty']				= $POST['acc_product_qty'];
	$info1['acce_rate']			= $POST['acce_rate'];
	$info1['acc_amount']		= $POST['acc_amount'];
	$info1['product_desc']		= text_rnremove($_POST['acc_product_desc']);
	$info1['inq_access_status']	= 3;
	$info1['cdate'] 				= date("Y-m-d H:i:s");
	$info1['user_id']				= $_SESSION['user_id'];
	$info1['company_id']			= $_SESSION['company_id'];
	$info1['branch_id']				= $POST['branchid'];
	/*var_dump($info1);*/
	$table = 'tbl_quto_access_trn';
	$tableid = 'inq_acc_id';

	if (empty($POST['edit_id'])) {
		$inserid = add_record($table, $info1, $dbcon);
	} else {
		$updateid = update_record($table, $info1, $tableid . "=" . $POST['edit_id'], $dbcon);
	}

	echo "1";
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
else if (strtolower($POST['mode']) == "add_attch_doc_field") {
	/*var_dump($_POST);
    var_dump($_FILES);*/
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	$info1['inq_attch_doc_name']    = $POST['inq_attch_doc_name'];
	$info1['inq_attch_file']	= upload_attch_doc_file($_FILES);
	$info1['user_id']		= $_SESSION['user_id'];
	$info1['company_id']		= $_SESSION['company_id'];

	$table = 'tbl_inq_attach';
	$tableid = 'inq_attach_id';
	if (!empty($POST['inquiry_id'])) {
		$info1['inquiry_id'] = $POST['inquiry_id'];
	} else {
		$info1['inq_attach_status'] = 3;
	}

	$inserid = add_record($table, $info1, $dbcon, $branch_id);

	if ($inserid) {
		echo "1";
	} else {
		echo "0";
	}
} else if (strtolower($POST['mode']) == "show_attach_doc_data") {
	$chkmode = $POST['modee'];
	$delete_btn_per = in_array(INQUIRY_SLUG_DELETE, $bulkAccessArray);
	if ($POST['inquiry_id']) {
		$query = "select mst.* from tbl_inq_attach as mst 
        where mst.inq_attach_status=0 and mst.inquiry_id=" . $POST['inquiry_id'];
	} else {
		$query = "select mst.* from tbl_inq_attach as mst 
        where mst.inq_attach_status=3 and mst.user_id=" . $_SESSION['user_id'];
	}
	$result = $dbcon->query($query);
	echo '<table class="display table table-bordered table-striped">
    <tr>
    <th width="60%" class="text-center">Document Name</th>
    <th width="30%" class="text-center">Attached Document</th>';

	echo '<th width="10%" class="text-center">Action</th>';

	echo '</tr>
    <tbody>';
	if (mysqli_num_rows($result) > 0) {
		$i = 1;
		while ($rel = mysqli_fetch_assoc($result)) {
			echo '<tr> 
            <td style="vertical-align:top;">
            <strong>' . $rel['inq_attch_doc_name'] . '</strong>
            </td>
            <td style="vertical-align:top;" class="text-center">
            <a href="' . ROOT . INQ_ATTACH_VWING . $rel['inq_attch_file'] . '" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>
            </td>';

			// if($delete_btn_per){
			echo '<td style="vertical-align:top"> 
                    <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_doc_attach_data(' . $rel['inq_attach_id'] . ')">X</button>
                    </td>';
			// }

			echo '</tr>';
			$i++;
		}
	} else {
		echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
	}

	echo '</tbody>
    </table>';
} else if (strtolower($POST['mode']) == "delete_doc_attach_data") {
	$row = array();
	$del_attch_qry = "select inq_attch_file from tbl_inq_attach where inq_attach_id=" . $POST['inq_attach_id'];
	$del_attch_rel = mysqli_fetch_assoc($dbcon->query($del_attch_qry));
	unlink('../' . INQ_ATTACH_UPING . $del_attch_rel['inq_attch_file']);

	$info['inq_attach_status'] = 2;
	$updateid = update_record('tbl_inq_attach', $info, "inq_attach_id=" . $POST['inq_attach_id'], $dbcon);

	if ($updateid)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo json_encode($row);
}
/*
	*  Code by Sanat :: 12-08-2021
		END
	*/
//Maulik Start
else if (strtolower($POST['mode']) == "load_product_unit") {
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
			$query_de = "select * from tbl_quotation_trn where quot_trn_id=" . $POST['edit_id'];
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
} else if (strtolower($POST['mode']) == "delivary_date_model_open") {
	if (empty($POST['trn_id'])) {
		echo '<input type="hidden" name="count" id="count" value="1" />
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
		<tr id="field">
		
		<th width="30%"  class="text-center" style="vertical-align:center;">Date</th>
		<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
		<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
		</tr>
		<tr id="field1">
		
		<td   class="text-center" style="vertical-align:center;">
		<input type="text" class="form-control default-date-picker delivery_date" id="delivery_date1" name="delivery_date[]" placeholder="Delivery Date" onkeyup="qty_wise_date_validation(1);" >
		</td>
		<td	 class="text-center;" style="vertical-align:center;">
		<input type="text" class="form-control delivery_qty" id="delivery_qty1" name="delivery_qty[]" placeholder="' . $POST["qty"] . '" onchange="validate_dilivary_date();" onkeyup="qty_wise_date_validation(1);" />
		</td>
		<td	 class="text-center;" style="vertical-align:center;">
		<input type="hidden" name="arry_sr[]" id="arry_sr" value="1" />
		</td>
		</tr>
		</table>';
	} else {
		$qry = "SELECT * FROM `tbl_quotation_delivery_date` WHERE quo_delivery_date_status=0 and quot_trn_id=" . $POST['trn_id'] . " order by quo_delivery_date_id";
		$row = $dbcon->query($qry);
		$cnt = brp_mysqli_num_rows($row);
		if ($cnt > 0) {
			$i = 1;
			echo '<input type="hidden" name="count" id="count" value="' . $cnt . '" />
			<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
			<tr id="field">
			<th width="30%"  class="text-center" style="vertical-align:center;">Date</th>
			<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
			<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
			</tr>';

			while ($tax = brp_mysqli_fetch_assoc($row)) {
				$date = date('d-m-Y', strtotime($tax['delivery_date']));
				echo '<tr id="field' . $i . '">
				
				<td   class="text-center" style="vertical-align:center;">
				<input type="text" class="form-control default-date-picker delivery_date" id="delivery_date' . $i . '" name="delivery_date[]" placeholder="Delivery Date" value="' . $date . '" onkeyup="qty_wise_date_validation(' . $i . ');" >
				</td>
				<td	 class="text-center;" style="vertical-align:center;">
				<input type="text" class="form-control delivery_qty" id="delivery_qty' . $i . '" name="delivery_qty[]" placeholder="' . $tax["product_qty"] . '" value="' . $tax["product_qty"] . '" onchange="validate_dilivary_date();" onkeyup="qty_wise_date_validation(' . $i . ');" />
				</td>
				<td	 class="text-center;" style="vertical-align:center;">
				<input type="hidden" name="arry_sr[]" id="arry_sr' . $i . '" value="' . $i . '" />
				<input type="hidden" class="arry_edit" name="arry_edit[]" id="arry_edit' . $i . '" value="' . $tax["po_delivery_date_id"] . '" />';
				if ($i != 1) {
					echo '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="remove_dilivary_date(' . $i . ');" id="fieldremove' . $i . '"><i class="fa fa-times"></i></button>';
				}
				echo '</td>
				</tr>';
				$i++;
			}
			echo '</table>';
		} else {
			echo '<input type="hidden" name="count" id="count" value="1" />
			<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
			<tr id="field">
			<th width="60%"  class="text-center" style="vertical-align:center;">Date</th>
			<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
			<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
			</tr>
			<tr id="field1">
			<td class="text-center" style="vertical-align:center;">
			<input type="text" class="form-control default-date-picker delivery_date" id="delivery_date1" name="delivery_date[]" placeholder="Delivery Date" onkeyup="qty_wise_date_validation(1);" >
			</td>
			<td	 class="text-center;" style="vertical-align:center;">
			<input type="text" class="form-control delivery_qty" id="delivery_qty1" name="delivery_qty[]" placeholder="' . $POST["qty"] . '" onchange="validate_dilivary_date();" onkeyup="qty_wise_date_validation(1);" />
			</td>
			<td	 class="text-center;" style="vertical-align:center;">
			<input type="hidden" name="arry_sr[]" id="arry_sr" value="1" />
			</td>
			</tr>
			</table>';
		}
	}
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
} else if (brp_strtolower($POST['mode'] == 'send_mail_quotation_dir')) {
	$email_page_path = $_POST['email_page_path'];
	include('../../../print/view/' . $email_page_path . '.php');

	// Get Userdata
	$cur_user_id = $_SESSION['user_id'];

	if ($getspecialConfiguration['adk_permission']) {
		$user['user_name'] = "ADK Engineering";
		$user['user_email'] = "marketing@adkeng.com";
	} else {
		$cur_user = getUserDetailById($dbcon, $cur_user_id);
		$user['user_name'] = $cur_user['user_name'];
		$user['user_email'] = $cur_user['common_email_id'];
	}

	$quotation_id = $_POST['quotation_id_d'];

	$query  = "select quot_subject,quot_header, quotation_no from tbl_quotation where quotation_id=" . $quotation_id;
	$result = $dbcon->query($query);
	$row = brp_mysqli_fetch_array($result);

	$quot_subject = $row['quot_subject'] ? $row['quot_subject'] : $row['quotation_no'];
	$quot_header = $row['quot_header'];
	$to = $_POST['to_email_id_d'];

	// CC 
	$query = "select website as email,company_name from tbl_company where company_id=" . $_SESSION['company_id'];
	$company = mysqli_fetch_assoc($dbcon->query($query));
	$ccemails = [];
	if ($company['email']) {
		$ccemails[] = array('email' => $company['email'], 'name' => $company['company_name']);
	}
	if ($_POST['ccemail_id_d']) {
		$ccemail_id_d = explode(';', $_POST['ccemail_id_d']);
		foreach ($ccemail_id_d as $val) {
			if ($val) {
				$ccemails[]['email'] = $val;
			}
		}
	}

	// BCC
	$bccemail[] = array('email' => $user['user_email'], 'name' => $user['user_name']);
	if ($_POST['bccemail_id_d']) {
		$bccemail_id_d = explode(';', $_POST['bccemail_id_d']);
		foreach ($bccemail_id_d as $val) {
			if ($val) {
				$bccemail[]['email'] = $val;
			}
		}
	}

	$save_file = 'Yes';
	$file_name = quotation_print($dbcon, $quotation_id, $save_file);
	$attachment_path  = DOMAIN . 'upload/mail_attach/' . $file_name;
	$res = common_print_send_email($to, $user, $quot_subject, $quot_header, $attachment_path, $file_name, $ccemails, $bccemail);
	unlink($attachment_path);

	$arr = array();
	$arr['msg'] = $res;
	echo json_encode($arr);
} else if (brp_strtolower($POST['mode'] == 'send_quotation_whatsapp')) {
	$email_page_path = $_POST['email_page_path'];

	include('../../../print/view/quotation_print_whatsapp.php');
	// include('../../../print/view/'.$email_page_path.'.php');

	$quotation_id = $_POST['quotation_id'];
	$query  = "select quot_subject,quot_header, quotation_no from tbl_quotation where quotation_id=" . $quotation_id;
	$result = $dbcon->query($query);
	$row = brp_mysqli_fetch_array($result);

	$quot_header_msg = $row['quot_header'];
	$mobile_no = str_replace('-', '', $_POST['cust_mobile']);

	$save_file = 'Yes';
	$file_name = whatsapp_quotation_print($dbcon, $quotation_id, $save_file);
	$attachment_path  = DOMAIN . 'upload/mail_attach/' . $file_name;
	$template_name = "quotation_sharing";
	$res = send_whatsapp_message($dbcon, $mobile_no, $attachment_path, $template_name);
	unlink($attachment_path);

	$arr = array();
	$arr['msg'] = $res;
	echo json_encode($arr);
} else if (brp_strtolower($POST['mode'] == 'send_mail_quotation')) {
	//print_r($_POST);//die;

	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = " . $_SESSION['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
	$userPhone = $userData['user_phone'] ? 'Mo.: ' . $userData['user_phone'] : '';
	$userEmail = $userData['user_mail'] ? ' - Email: ' . $userData['user_mail'] : '';

	$quotation_id = $_POST['quotation_id'];
	$type = 'pdf';
	if (strtolower($type) == 'pdf') {
		//Quotation Data
		$query = "select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name, cadd.c_add_state from tbl_quotation as quot
		left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
		left join tbl_customer as cust on cust.cust_id=quot.cust_id
		left join tbl_cust_address as cadd on cadd.cust_id=quot.cust_id
		left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
		left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
		where quot.quotation_id=" . $quotation_id;
		$rel = mysqli_fetch_assoc($dbcon->query($query));
		//print_r($rel);die;
		if (!$rel) {
			header("Location: " . ROOT . CRM_ROOT . "quotation_list");
		}

		if ($rel['quot_type'] == '0') {
			$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`=68 ';
			$currency_rel = mysqli_fetch_assoc($dbcon->query($currency_sql));
			$currency_name = '(INR)';
			$currency_word_start = 'Rupees';
			$currency_word_end = 'Paise';
			$currency_symbol = $currency_rel['currency_symbol'];
		} else {
			$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="' . $rel['currency_id'] . '" ';
			$currency_rel = mysqli_fetch_assoc($dbcon->query($currency_sql));

			$currency_name = '(' . ucfirst(strtolower($currency_rel['currency_code'])) . ')';
			$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
			$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
			$currency_symbol = $currency_rel['currency_symbol'];
		}
		$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';
		$set = "select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=" . $rel['company_id'];

		$comp_rel = mysqli_fetch_assoc($dbcon->query($set));
		$header = '<div style="text-align:center;padding-top:30px"><img src="' . DOMAIN_F . LOGO . $comp_rel["logo"] . '" style="width:8.27in;" /></div>';
		$footer = '<div style="text-align:center;"><img src="' . DOMAIN_F . LOGO . $comp_rel["f_logo"] . '" style="width:8.27in;" /></div>';
		$approve_status = '';
		if ($rel['approve_status'] == '0') {
			$approve_status = ' (DRAFT)';
		}
		$inquiry_type = $rel['inquiry_type'];
		//Amish Soni Start 16-03-2021
		$companySettings = getCompanySettings($dbcon);
		$companyConfiguration = getCompanyConfiguration($dbcon);
		$sales_pro_search = explode(",", $companyConfiguration['sales_pro_search']);

		if ($companySettings) {
			$quotation_print_content = $companySettings['quotation_print_content'] ? $companySettings['quotation_print_content'] : '';
			$quotation_print_content = str_ireplace(array("\r", "\n", '\r', '\n'), '', $quotation_print_content);
			$quotation_footer_content = $companySettings['quotation_footer_content'] ? $companySettings['quotation_footer_content'] : $quotation_footer_content;
			$quotation_footer_content = str_ireplace(array("\r", "\n", '\r', '\n'), '', $quotation_footer_content);
		}
		$disc_qry = $dbcon->query("SELECT SUM(trn.product_discount_conv) as discount from tbl_quotation_trn as trn where trn.quot_trn_status=0 and trn.quotation_id=" . $rel['quotation_id']);
		$disc_qrys = brp_mysqli_fetch_assoc($disc_qry);
		if ($companyConfiguration['quot_revise_time_rate_with_discount'] == 0) {
			$colspan = ($disc_qrys['discount'] > 0) ? 5 : 4;
		} else {
			$colspan = 4;
		}

		//Amish Soni End 16-03-2021
		$html = '<html>
		<head>					
		<title>Quotation - ' . $rel['quotation_no'] . '</title>
		<style type="text/css">
		/*
		.page{
			width:8.27in;
			height:10.69in;
			}*/
			.nextpage
			{
				page-break-after: always;
			}
			table{
				border-collapse:collapse;
				width:100%;
			}

			table tr,td{
				border:1px solid #000 !important;
				/*page-break-inside:avoid;*/
			}
			.quot_annex_content_div table tr,td{
				padding:5px;
			}
			.blueHeading {
				color: #365f91;
			}

			</style>
			</head>
			<body>
			<!--Show Logo in other pages-->
			<htmlpageheader name="otherpages" style="display:none">
			<div style="text-align:center">' . $header . '</div>
			</htmlpageheader>
			<htmlpagefooter name="otherpages_footer" style="display:none">
			<div style="text-align:center">' . $footer . '</div>
			</htmlpagefooter>
			<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
			<div>
			<table cellpadding="5" cellspacing="5" border="0" style="font-size: 14x; font-family: Proxia Nova; border: 0;">
			<tr style="border: 0;">
			<td style="border: 0; text-align: right; font-size: 15px;">Date: ' . date("d-M-Y", strtotime($rel['quotation_date'])) . '</td>
			</tr>
			</table>
			<table cellpadding="5" cellspacing="5" border="0" style="font-size: 14px; font-family: Proxia Nova; border: 0;">
			<tr style="border: 0;">
			<td style="border: 0;">
			<p style="float: left; width: 100%;">
			<strong>To,<br />' . $rel['cust_name'] . ',</strong><br/>
			<strong style="color: #999999;">' . $rel['c_con_fname'] . ' ' . $rel['c_con_lname'] . ',<br />
			' . ($quot_address) . '</strong>
			</p>
			<br />
			' . stripslashes($quotation_print_content) . '
			
			</td>
			</tr>
			</table>
			</div>
			<center class="nextpage"></center>
			<div>
			<table style="font-size:14px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
			<tr>
			<td rowspan="6" style="text-align:left;vertical-align:top;border:1px solid;width:50%;"> 
			<strong>' . $rel['cust_name'] . ',</strong><br/>
			<strong>' . $rel['c_con_fname'] . ' ' . $rel['c_con_lname'] . ',<br />
			' . ($quot_address) . '</strong>
			</td>
			<td style="text-align:left;border:1px solid;width:20%;"> 
			Quotation No
			</td>
			<td style="text-align:left;border:1px solid;width:30%;"> 
			<strong>' . $rel['quotation_no'] . '</strong>
			</td>
			</tr>
			<tr>
			<td style="text-align:left;border:1px solid;"> 
			Quotation Date
			</td>
			<td style="text-align:left;border:1px solid;"> 
			' . date("d-M-Y", strtotime($rel['quotation_date'])) . '
			</td>
			</tr>

			<tr>
			<td style="text-align:left;border:1px solid;"> 
			Inquiry Ref No
			</td>
			<td style="text-align:left;border:1px solid;"> 
			' . $rel['inquiry_no'] . '
			</td>
			</tr>
			<tr>
			<td style="text-align:left;border:1px solid;"> 
			Inquiry Ref Date
			</td>
			<td style="text-align:left;border:1px solid;"> 
			' . date("d-M-Y", strtotime($rel['inquiry_date'])) . '
			</td>
			</tr>
			<tr>
			<td style="text-align:left;border:1px solid;"> 
			Executive Name
			</td>
			<td style="text-align:left;border:1px solid;"> 
			' . $userData['user_name'] . '
			</td>
			</tr>
			<tr>
			<td style="text-align:left;border:1px solid;"> 
			Valid Till
			</td>
			<td style="text-align:left;border:1px solid;"> 
			' . date("d-M-Y", strtotime($rel['quotation_valid_date'])) . '
			</td>
			</tr>
			</table>
			<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
			<thead>
			
			<tr>
			<th style="width:5%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
			<th style="width:50%;text-align:center;border:1px solid;">Description</th>
			<th style="width:10%;text-align:center;border:1px solid;">Qty</th>
			<th style="width:10%;text-align:center;border:1px solid;">Rate <br>' . $currency_name . '</th>';
		if ($companyConfiguration['quot_revise_time_rate_with_discount'] == 0) {
			if ($disc_qrys['discount'] > 0) {
				$html .= '<th style="width:10%;text-align:center;border:1px solid;">Discount <br>' . $currency_name . '</th>';
			}
		}
		$html .= '<th style="width:15%;text-align:center;border:1px solid;">Total Amount <br>' . $currency_name . '</th>
			</tr>
			</thead>
			<tbody>';
		if ($inquiry_type != "2") {
			$trn_qry = "SELECT trn.*,pro.product_name,unit.unit_name, pro.product_icode FROM tbl_quotation_trn as trn 
				left join product_mst as pro on pro.product_id=trn.product_id
				left join unit_mst as unit on unit.unitid=trn.unitid
				where trn.quot_trn_status=0 and trn.quotation_id=" . $rel['quotation_id'];
		} else {
			$trn_qry = "SELECT trn.* , pro.product_name,pro.product_icode FROM `tbl_quotation_project_trn` as trn 
			  	left join product_mst as pro on pro.product_id = trn.product_id 
				where trn.quotation_projecttrn_status=0 and trn.quotation_id =" . $rel['quotation_id'];
		}
		$trn_qry_rs = $dbcon->query($trn_qry);
		$p = 1;
		$ttl_amt = 0;
		$ttl_qty = 0;
		$charges_qty = 0;
		$total_gst = 0;
		$total_i_gst = 0;
		$cnt = mysqli_num_rows($trn_qry_rs);
		while ($trn_rel = mysqli_fetch_assoc($trn_qry_rs)) {
			$gst_per = $trn_rel['cgst_tax_per'] + $trn_rel['sgst_tax_per'] + $trn_rel['igst_tax_per'];
			$gst_rate = $trn_rel['cgst_tax_rate_conv'] + $trn_rel['sgst_tax_rate_conv'] + $trn_rel['igst_tax_rate_conv'];

			if ($trn_rel['cgst_tax_rate_conv'] != 0 || $trn_rel['sgst_tax_rate_conv'] != 0) {
				$total_cs_gst += $gst_rate;
			} else {
				$total_i_gst += $gst_rate;
			}
			//tax summary calculation start
			if (!empty($trn_rel['tax_val'])) {
				$tax_num = explode(",", $trn_rel['tax_val']);
				$tax_name = explode(",", $trn_rel['tax_name']);
				$total_net_rate = ($trn_rel['product_qty'] * $trn_rel['product_rate_conv']) - $trn_rel['product_discount_conv'];
				for ($j = 0; $j < count($tax_num); $j++) {
					if (!in_array($tax_name[$j], $tax['per'])) {
						$tax['per'][] = $tax_name[$j];
					}
					$tax['per_total'][$tax_name[$j]] += $total_net_rate * $tax_num[$j] / 100;
				}
			}
			$item_code = '';
			if (in_array('item', $sales_pro_search)) {
				$item_code = " -- (" . $trn_rel['product_icode'] . ")";
			}
			$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';

			$html .= '<tr style="border:none;border-left:1px solid;border-right:1px solid;">
				<td style="text-align:center;border:1px solid;vertical-align:top;">' . $p . '</td>
				<td style="text-align:left;border:1px solid;vertical-align:top;">
				<strong>' . $trn_rel['product_name'] . '</strong>' . $item_code . '<br/>
				' . $product_desc . '
				</td>
				<td style="text-align:center;border:1px solid;vertical-align:top;">
				' . $trn_rel['product_qty'] . '
				</td>
				<td style="text-align:center;border:1px solid;vertical-align:top;">';
			if ($trn_rel['act_amt_flag'] == '1') {
				$html .= "Extra At Actual";
			} else {
				$html .= $currency_symbol;
				if ($rel['quot_type'] == '0') {
					$html .= indian_number($trn_rel['product_rate_conv'], 2);
				} else {
					$html .= indian_number($trn_rel['product_rate_conv'], 2);
				}
			}

			$html .= '</td>';
			if ($companyConfiguration['quot_revise_time_rate_with_discount'] == 0) {
				if ($disc_qrys['discount'] > 0) {
					$html .= '<td style="text-align:center;border:1px solid;vertical-align:top;">
						' . $currency_symbol . ' ' . $trn_rel['product_discount_conv'] . '<br>(' . $trn_rel['discount_per'] . ' %)
						</td>';
				}
			}
			$html .= '<td style="text-align:center;border:1px solid;vertical-align:top;">';
			if ($trn_rel['act_amt_flag'] == '1') {
				$html .= "Extra At Actual";
			} else {
				$html .= $currency_symbol;
				if ($rel['quot_type'] == '0') {
					$html .= indian_number($trn_rel['product_amount_conv'], 2);
				} else {
					$html .= indian_number($trn_rel['product_amount_conv'], 2);
				}
			}

			$html .= '</td>
				</tr>';
			$ttl_qty = $ttl_qty + $trn_rel['product_qty'];
			if ($trn_rel['act_amt_flag'] != '1') {
				if ($rel['quot_type'] == '0') {
					$ttl_amt = $ttl_amt + ($trn_rel['product_amount_conv']);
				} else {
					$ttl_amt = $ttl_amt + ($trn_rel['product_amount_conv']);
				}
			}

			$p++;
		}
		$pr = 10 - $cnt;
		for ($j = 0; $j < $pr; $j++) {
			$html .= '<tr style="border:none;border-left:1px solid;border-right:1px solid;">
				<td style="border:none;border-left:1px solid;border-right:1px solid;height:40px;"></td>
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>';
			if ($companyConfiguration['quot_revise_time_rate_with_discount'] == 0) {
				if ($disc_qrys['discount'] > 0) {
					$html .= '<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>';
				}
			}
			$html .= '
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
				</tr>';
		}

		$html .= '<tr>
			<td colspan="' . $colspan . '" style="text-align:right;border:1px solid;"><b>Basic Amount</b></td>
			<td style="text-align:center;border:1px solid;"><b>
			' . $currency_symbol . ' ' . indian_number($ttl_amt, 2) . '
			</b></td>
			</tr>';

		if (!empty($total_cs_gst) || !empty($total_i_gst)) {
			if ($rel['c_add_state'] == $comp_rel['stateid']) {
				$html .= '<tr>
					<td colspan="' . $colspan . '" style="text-align:right;border:1px solid;"><b>CGST</b></td>
					<td style="text-align:center;border:1px solid;"><b>
					' . $currency_symbol . ' ' . number_format(($total_cs_gst / 2), 2, ".", "") . '
					</b></td>
					</tr>
					<tr>
					<td colspan="' . $colspan . '" style="text-align:right;border:1px solid;"><b>SGST</b></td>
					<td style="text-align:center;border:1px solid;"><b>
					' . $currency_symbol . ' ' . number_format(($total_cs_gst / 2), 2, ".", "") . '
					</b></td>
					</tr>';
			} else {
				$html .= '<tr>
					<td colspan="' . $colspan . '" style="text-align:right;border:1px solid;"><b>IGST</b></td>
					<td style="text-align:center;border:1px solid;"><b>
					' . $currency_symbol . ' ' . number_format(($total_i_gst), 2, ".", "") . '
					</b></td>
					</tr>';
			}
		}
		$qry11 = "select sum((tc.tax_per*trn.product_amount_conv)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_quotation_trn as trn 
			left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
			left join tbl_ledger as l on l.l_id=tc.tax_id 
			where tc.tax_additional='1' and trn.quotation_id=" . $rel['quotation_id'] . " and trn.quot_trn_status!=2 and tc.isdelete='0' group by tc.tax_id";
		$result11 = $dbcon->query($qry11);
		while ($row11 = mysqli_fetch_assoc($result11)) {
			$html .= '<tr>
				<td colspan="' . $colspan . '" style="text-align:right;border:1px solid;"><b>' . $row11['l_name'] . '</b></td>
				<td style="text-align:center;border:1px solid;"><b>
				' . $currency_symbol . ' ' . number_format($row11['add_sum'], 2, ".", "") . '
				</b></td>
				</tr>';
		}
		$qry12 = "select b.sundry_amount_conv,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
			from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
			left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
			where b.sundry_voucher_id=" . $rel['quotation_id'] . " and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0'";
		$result12 = $dbcon->query($qry12);
		while ($row12 = mysqli_fetch_assoc($result12)) {
			$html .= '<tr>
				<td colspan="' . $colspan . '" style="text-align:right;border:1px solid;"><b>' . $row12['l_name'] . '</b></td>
				<td style="text-align:center;border:1px solid;"><b>
				' . $currency_symbol . ' ' . number_format($row12['sundry_amount_conv'], 2, ".", "") . '
				</b></td>
				</tr>';
		}
		$html .= '<tr>
			<td colspan="' . $colspan . '" style="text-align:right;border:1px solid;"><b>Total Amount
			</td>
			<td style="text-align:center;border:1px solid;"><b>
			' . $currency_symbol . ' ' . indian_number($rel['g_total_conv'], 2) . '
			</b></td>
			</tr>
			<tr>
			<td colspan="' . ($colspan + 1) . '" style="border:1px solid;text-align:right;"><b>Total (In Words): 
			' . (($comp_rel['currency_id'] == $rel['currency_id']) ? ucfirst(convert_number_to_words_new($rel['g_total_conv'], $rel['currency_id'], $currency_word_end, $currency_word_start)) : ucfirst(convert_number_to_words_new($rel['g_total_conv'], $rel['currency_id'], $currency_word_end, $currency_word_start))) . '</b></td></tr>';
		$html .= '
			<tr>
			<td colspan="' . ($colspan + 1) . '" style="border:1px solid;text-align:left;"><b>Remarks:</b> 
			' . (($rel['quot_remark']) ? $rel['quot_remark'] : '') . '</td></tr></tbody></table></div>';

		/* Get Terms And Condition Start */
		$terms_qry = "select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
			left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
			where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=" . $rel['quotation_id'] . " order by qtrm.tc_priority";
		$terms_qry_rs = $dbcon->query($terms_qry);
		if (mysqli_num_rows($terms_qry_rs)) {
			$t = 1;
			$html .= '<center class="nextpage"></center><div>
				<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<thead>
				<tr>
				<th style="text-align:center; border:1px solid;">Sr.<br/>No.</th>
				<th style="text-align:left; border:1px solid;">Terms and Condition</th>
				<th style="text-align:left; border:1px solid;">Description</th>
				</tr>
				</thead><tbody>';
			while ($term_rel = mysqli_fetch_assoc($terms_qry_rs)) {
				$string = (nl2br($term_rel['tc_details']));
				$html .= '<tr>
					<td width="5%" style="width:5%;text-align:center;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;">' . $t . '</td>
					<td width="25%" style="width:25%;text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;">' . $term_rel['tc_name'] . '</td>
					<td width="70%" style="width:70%;text-align:left;border:1px solid #000;padding:5px;">' . $string . '</td>
					</tr>';
				$t++;
			}
			$html .= '</tbody></table>';
		}
		/* Check Annexure Attachments Start */
		if (trim($rel['quot_annex_content'])) {
			$html .= '<center class="nextpage"></center>';
			$html .= '<div class="quot_annex_content_div" style="font-size: 16px;">' . $rel['quot_annex_content'];
			$html .= '</div>';
		}
		/* Check Annexure Attachments End */
		if (!empty($quotation_footer_content)) {
			$html .= '<br /><br /><div>' . $quotation_footer_content;
			$html .= '</div>';
		}

		$html .= '<sethtmlpagefooter name="otherpages_footer" value="on" />
			</body>
			</html>';
		//echo $trn_qry;
		//echo $html;exit;
		ob_end_clean();
		$file_name = $rel['quotation_no'] . '.pdf';
		$file_name = str_ireplace("/", "_", $file_name);
		if ($save_file == "No") {
			include("../../view/export/mpdf/mpdf.php");
		} else {
			include("../../../view/export/mpdf/mpdf.php");
		}
		$mpdf = new mPDF('', 'A4', '0', 'calibri', '10', '10', '45', '25', '1', '1');
		//		$mdf->SetFont('ProximaNova');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		//Show page number : Dimple Panchal (05-Apr-2021)
		$mpdf->pagenumPrefix = ' ';
		$mpdf->pagenumSuffix = ' / ';
		$mpdf->nbpgPrefix = ' ';
		$mpdf->nbpgSuffix = ' pages';
		$mpdf->SetFooter('{PAGENO}{nbpg}');
		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion = true;
		$mpdf->charset_in = 'UTF-8';
		$mpdf->WriteHTML($html);
		if ($save_file == "No") {
			$mpdf->Output();
		} else {
			$mpdf->Output('../../../view/upload/mail_attach/' . $file_name, 'f');
		}
		ob_clean();

		//echo $file_name;die;
		//return $file_name;
	}






	require '../../../vendor/autoload.php';
	$mail = new PHPMailer(true);

	// Passing `true` enables exceptions
	try {

		//Server settings
		//$mail->SMTPDebug = 2;
		$mail->isSMTP();
		$mail->Host = 'smtp.gmail.com';
		$mail->SMTPAuth = true;
		$mail->Username = 'darshankyada@gmail.com';
		$mail->Password = 'schdbbblvxzxgjmp';
		$mail->SMTPSecure = 'tls';
		$mail->Port = 587;

		$mail->setFrom('darshankyada@gmail.com', 'Darshan');
		//$mail->setFrom('techunityerp@gmail.com', 'Tech unity');
		$mail->addAddress('techunityerp@gmail.com', 'Tech unity');

		// Email content
		$mail->isHTML(true);
		$mail->Subject = $_POST['email_subject'];
		$mail->Body = "" . $_POST['email_content'] . "";
		$mail->AltBody = 'This is the plain text message body';

		// Add an attachment
		//print_r($dbcon);die;
		$attach = array();
		//$quot_file = umaboy_quotation_print($dbcon, $POST['quotation_id'],'Yes');
		// $quot_file = quotation_print($dbcon, $POST['quotation_id'],'Yes');
		// array_push($attach,$quot_file);
		//final_send_email($from_email_id, $to_email_id, $rel11['email_cc'],$rel12['email_bcc'], $subject, $content,$attach);
		//unlink('../../../view/upload/mail_attach/'.$quot_file);
		//save_quotation($dbcon,$POST['quotation_id']);
		//echo $q;die;
		//$pdf_path = "pdf/Quotation_".$_POST['quotation_id'].".pdf";
		$pdf_path = '../../../view/upload/mail_attach/' . $file_name;
		//echo $pdf_path;die;
		$mail->addAttachment($pdf_path, $file_name);

		$mail->send();
		$arr['msg'] = array();
		$arr['msg'] = '1';
		unlink('../../../view/upload/mail_attach/' . $file_name);

		//echo json_encode($arr);
	} catch (Exception $e) {
		$arr['msg'] = array();
		$arr['msg'] = '0';
		//echo json_encode($arr);
		//echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;die();

	}
	echo json_encode($arr);
}
//Maulik End
function upload_mail_attch_file($FILES)
{
	$rand = rand(0, 99999999);
	if (!empty($FILES['email_attach']['tmp_name'])) {
		$temp = explode(".", $FILES["email_attach"]["name"]);
		$extension = strtolower(end($temp));
		$File = "mail_attch_" . $rand . "." . $extension;
		$tmp_name = $FILES["email_attach"]["tmp_name"];
		move_uploaded_file($tmp_name, MAIL_ATTACH_UPING . $File);

		return  $File;
	}
}
function load_quotation_no($dbcon)
{
	//Load no by Type ID
	$row = array();
	$query1 = "select * from tbl_invoicetype where status=0 and type_id=3 and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
	$rows = mysqli_fetch_assoc($dbcon->query($query1));
	$id = $rows['taxinvoice_start'];
	$id = $id + 1;
	if ($rows['invoice_format'] == '2') {
		$row['invoiceno'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
	} else if ($rows['invoice_format'] == '1') {
		$row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
	} else if ($rows['invoice_format'] == '3') {
		$row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
	} else {
		$row['invoiceno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
	}
	return $row['invoiceno'];
}

function upload_attch_file($FILES)
{
	$rand = rand(0, 99999999);
	if (!empty($FILES['dfd_attch_file']['tmp_name'])) {
		$temp = explode(".", $FILES["dfd_attch_file"]["name"]);
		$extension = strtolower(end($temp));
		$File = "dfd_attch_" . $rand . "." . $extension;
		$tmp_name = $FILES["dfd_attch_file"]["tmp_name"];
		move_uploaded_file($tmp_name, '..//' . INQ_ATTACH_UPING . $File);

		return  $File;
	}
}

/////////attach Doc Maulik///////////////////////////////
function upload_attch_doc_file($FILES)
{
	$rand = rand(0, 99999999);
	if (!empty($FILES['inq_attch_file']['tmp_name'])) {
		$temp = explode(".", $FILES["inq_attch_file"]["name"]);
		$extension = strtolower(end($temp));
		$File = "inq_attch_" . $rand . "." . $extension;
		$tmp_name = $FILES["inq_attch_file"]["tmp_name"];
		move_uploaded_file($tmp_name, '..//' . INQ_ATTACH_UPING . $File);

		return  $File;
	}
}
// fetch all products for quotation
function get_quotation_products($dbcon, $quotationId = 0)
{
	if ($quotationId) {
		$query = "select trn.*,pro.product_name,unit.unit_name,hsn.hsn_code as product_hsn_code from tbl_quotation_trn as trn 
				left join product_mst as pro on pro.product_id=trn.product_id
				left join unit_mst as unit on unit.unitid=trn.unitid
				left join mst_hsn_code as hsn on hsn.hsn_id=pro.product_hsn
				where trn.quot_trn_status=0 and trn.quotation_id=" . $quotationId;
	} else {
		$query = "select trn.*,pro.product_name,unit.unit_name,hsn.hsn_code as product_hsn_code from tbl_quotation_trn as trn 
				left join product_mst as pro on pro.product_id=trn.product_id
				left join unit_mst as unit on unit.unitid=trn.unitid
				left join mst_hsn_code as hsn on hsn.hsn_id=pro.product_hsn
				where trn.quot_trn_status=3 and trn.user_id=" . $_SESSION['user_id'];
	}
	$result = brp_mysqli_query($dbcon, $query);
	$products = brp_mysqli_fetch_all($result, MYSQLI_ASSOC);
	if ($products) {
		return true;
	} else {
		return false;
	}
}

/*
Code By Umair: 13/07/2021
Commnet: Copy the inquiry project trn to quotation project trn
START
*/

function copy_inquiry_project_trn_ro_quotation_project_trn($dbcon, $inquiry_id, $quotation_trn_id, $branch_id, $cust_state_id, $type = null)
{


	$inq_qry = "select * from tbl_inquiry_project_trn where inquiry_projecttrn_status=0 and inquiry_id=" . $inquiry_id;
	$inq_qry_rs = $dbcon->query($inq_qry);

	///////////////////////////////////////////////////////////////////////Harshil ///////////////////////////////////////////////////
	while ($inq_rel = mysqli_fetch_assoc($inq_qry_rs)) {


		$t_Qty = ($inq_rel['product_qty']);
		$t_amount = ($t_Qty * $inq_rel['product_rate']);

		$company_state = get_company_data($dbcon, $_SESSION['company_id']);

		$sale_gst = get_tax_cat_by_hsn_id($dbcon, $inq_rel['product_hsn_code']);

		$cgst_tax_rate = 0;
		$sgst_tax_rate = 0;
		$igst_tax_rate = 0;

		if (($company_state['stateid'] == $cust_state_id)) {
			$gst = $sale_gst['tax_gst'] / 2;
			$cgst_tax_per = $gst;
			$cgst_tax_rate = ($gst * $t_amount) / 100;
			$sgst_tax_per = $gst;
			$sgst_tax_rate = ($gst * $t_amount) / 100;
			$t_g_amount = ($t_amount + $cgst_tax_rate + $sgst_tax_rate);
		} else {
			$igst_tax_per = $sale_gst['tax_gst'];
			$igst_tax_rate = ($sale_gst['tax_gst'] * $t_amount) / 100;
			$t_g_amount = ($t_amount + $igst_tax_rate);
		}




		$info12['inquiry_id']			= $inq_rel['inquiry_id'];
		$info12['quotation_id'] = 0;
		$info12['quotation_projecttrn_status'] = 0;


		$info12['quotation_trn_id']		= $quotation_trn_id;
		$info12['inquiry_type']			= $inq_rel['inquiry_type'];
		$info12['project_assign_id']		= $inq_rel['project_assign_id'];
		$info12['product_category_id']	= $inq_rel['product_category_id'];
		$info12['product_id']			= $inq_rel['product_id'];
		$info12['description']			= $inq_rel['description'];
		$info12['product_hsn_code']		= $inq_rel['product_hsn_code'];
		$info12['product_qty']			= $t_Qty;
		$info12['product_rate']			= $inq_rel['product_rate'];
		$info12['product_amount']    	= $t_amount;
		$info12['formulaid']         	= $inq_rel['formulaid'];
		$info12['product_disc']			= $inq_rel['product_disc'];
		$info12['product_spec']			= $inq_rel['product_spec'];
		//$info=get_product_common_tax($dbcon,$t_amount,$info12['formulaid']);
		//$info12=array_merge($info12,$info);

		$info12['user_id']				= $_SESSION['user_id'];
		$info12['company_id']			= $_SESSION['company_id'];
		$info12['cgst_tax_per']			= isset($cgst_tax_per) ? $cgst_tax_per : 0;
		$info12['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
		$info12['sgst_tax_per']			= isset($sgst_tax_per) ? $sgst_tax_per : 0;
		$info12['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
		$info12['igst_tax_per']			= isset($igst_tax_per) ? $igst_tax_per : 0;
		$info12['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0;
		$info12['product_total']		= $t_g_amount;
		$info12['user_id']				= $_SESSION['user_id'];
		$info12['company_id']			= $_SESSION['company_id'];
		$inserid_sub = add_record("tbl_quotation_project_trn", $info12, $dbcon, $branch_id);
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


}

function revise_quotation_project_trn_to_quotation_project_trn($dbcon, $quotation_id, $quotation_trn_id, $branch_id, $type = null)
{



	$copy_qry = "Insert into tbl_quotation_project_trn (inquiry_id,quotation_id,quotation_trn_id,inquiry_type,project_assign_id,product_category_id,product_id,description,product_hsn_code, product_qty,product_rate,product_amount,formulaid,cgst_tax_per,cgst_tax_rate,sgst_tax_per, sgst_tax_rate,igst_tax_per,igst_tax_rate,product_total,quotation_projecttrn_status,user_id,company_id,branch_id,product_disc,product_spec,product_hp) 
		select inquiry_id,0," . $quotation_trn_id . ",inquiry_type,project_assign_id,product_category_id,product_id,description,product_hsn_code, product_qty,product_rate,product_amount,formulaid,cgst_tax_per,cgst_tax_rate,sgst_tax_per, sgst_tax_rate,igst_tax_per,igst_tax_rate,product_total,3," . $_SESSION['user_id'] . ",company_id,branch_id,product_disc,product_spec,product_hp from tbl_quotation_project_trn where quotation_projecttrn_status=0 and quotation_id=" . $quotation_id;
	$copy_qry_rs = $dbcon->query($copy_qry);
	$quotation_trn_id = $dbcon->insert_id;


	/* $inq_qry="select * from tbl_quotation_project_trn where quotation_projecttrn_status=0 and quotation_id='".$quotation_id."' ";
	$inq_qry_rs=$dbcon->query($inq_qry);
	while($inq_rel=mysqli_fetch_assoc($inq_qry_rs))
					{
						
		

						
					}
	
	

	
	///////////////////////////////////////////////////////////////////////Harshil ///////////////////////////////////////////////////
					while($inq_rel=mysqli_fetch_assoc($inq_qry_rs))
					{
						
						
						$t_Qty=($inq_rel['product_qty']);
						$t_amount = ($t_Qty * $inq_rel['product_rate']);
						
						$company_state = get_company_data($dbcon,$_SESSION['company_id']);
						
						 $sale_gst = get_tax_cat_by_hsn_id($dbcon,$inq_rel['product_hsn_code']);
						
						$cgst_tax_rate=0;
						$sgst_tax_rate=0;
						$igst_tax_rate=0;
						if(($company_state['stateid'] == $POST['cust_stateid']))
						{
							$gst = $sale_gst['tax_gst']/2;
							$cgst_tax_per = $gst;
							$cgst_tax_rate = ($gst*$t_amount)/100;
							$sgst_tax_per = $gst;
							$sgst_tax_rate = ($gst*$t_amount)/100;
							$t_g_amount=($t_amount+$cgst_tax_rate+$sgst_tax_rate);
						}else
						{
							$igst_tax_per = $sale_gst['tax_gst'];
							$igst_tax_rate = ($sale_gst['tax_gst']*$t_amount)/100;
							$t_g_amount=($t_amount+$igst_tax_rate);
						}
						
						
						
						
						$info12['inquiry_id']			= $inq_rel['inquiry_id'];
						$info12['quotation_id']= 0;
						$info12['quotation_projecttrn_status']= 3;
						
						
						$info12['quotation_trn_id']		= $quotation_trn_id;
						$info12['inquiry_type']			= $inq_rel['inquiry_type'];
						$info12['project_assign_id']		= $inq_rel['project_assign_id'];
						$info12['product_category_id']	= $inq_rel['product_category_id'];
						$info12['product_id']			= $inq_rel['product_id'];
						$info12['description']			= $inq_rel['description'];
						$info12['product_hsn_code']		= $inq_rel['product_hsn_code'];
						$info12['product_qty']			= $t_Qty ;
						$info12['product_rate']			= $inq_rel['product_rate'];
						$info12['product_amount']    	= $t_amount;
						$info12['formulaid']         	= $inq_rel['formulaid'];
						$info12['product_disc']			= $inq_rel['product_disc'];
						$info12['product_spec']			= $inq_rel['product_spec'];
						//$info=get_product_common_tax($dbcon,$t_amount,$info12['formulaid']);
						//$info12=array_merge($info12,$info);

						$info12['user_id']				= $_SESSION['user_id'];
						$info12['company_id']			= $_SESSION['company_id'];
						$info12['cgst_tax_per']			= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
						$info12['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
						$info12['sgst_tax_per']			= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
						$info12['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
						$info12['igst_tax_per']			= isset($igst_tax_per) ? $igst_tax_per : 0 ;
						$info12['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
						$info12['product_total']		= $t_g_amount;
						$info12['user_id']				= $_SESSION['user_id'];
						$info12['company_id']			= $_SESSION['company_id'];
						$inserid_sub=add_record("tbl_quotation_project_trn", $info12, $dbcon, $branch_id);
 */
	//}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


}

function copy_quotation_trn($dbcon, $prev_quotation_id)
{
	$companyConfiguration = getCompanyConfiguration($dbcon);
	$query = "select * from tbl_quotation_trn where quot_trn_status=0 and pid=0 and quotation_id=" . $prev_quotation_id;
	$result = $dbcon->query($query);

	while ($row = brp_mysqli_fetch_array($result)) {

		if ($companyConfiguration['quot_revise_time_rate_with_discount'] == 1) {
			$rate  = $row['product_rate'] - ($row['product_rate'] * $row['discount_per'] / 100);
			$row['product_rate'] 			= $rate;
			$row['product_discount']		= '';
			$row['discount_per']			= '';
		}

		$info['inquiry_type']				= $row['inquiry_type'];
		$info['project_wise']				= $row['project_wise'];
		$info['product_id']		 			= $row['product_id'];
		$info['cat_id']		 				= $row['cat_id'];
		$info['rcat_id']		 			= $row['rcat_id'];
		$info['product_desc']				= $row['product_desc'];
		$info['product_spec']				= $row['product_spec'];
		$info['product_spec_id']			= $row['product_spec_id'];
		$info['level_id']					= $row['level_id'];
		$info['product_qty']				= $row['product_qty'];
		$info['unitid']						= $row['unitid'];
		$info['product_conv_qty']			= $row['product_conv_qty'];
		$info['conv_unit_id']				= $row['conv_unit_id'];
		$info['rate_unit']					= $row['rate_unit'];
		$info['product_rate']				= $row['product_rate'];
		$info['product_discount']			= $row['product_discount'];
		$info['discount_per']				= $row['discount_per'];
		$info['product_amount']				= $row['product_amount'];
		$info['formulaid']					= $row['formulaid'];
		$info['tax_name1']					= $row['tax_name1'];
		$info['tax_amount1']				= $row['tax_amount1'];
		$info['tax_name2']					= $row['tax_name2'];
		$info['tax_amount2']				= $row['tax_amount2'];
		$info['tax_name3']					= $row['tax_name3'];
		$info['tax_amount3']				= $row['tax_amount3'];
		$info['product_total']				= $row['product_total'];
		$info['company_id']					= $row['company_id'];
		$info['user_id']					= $_SESSION['user_id'];
		$info['quot_trn_status']			= 3;
		$info['product_rate_dollar']		= $row['product_rate_dollar'];
		$info['product_amount_dollar']		= $row['product_amount_dollar'];
		$info['product_total_dollar']		= $row['product_total_dollar'];
		$info['product_hp']					= $row['product_hp'];
		$info['cgst_tax_per']				= $row['cgst_tax_per'];
		$info['cgst_tax_rate']				= $row['cgst_tax_rate'];
		$info['sgst_tax_per']				= $row['sgst_tax_per'];
		$info['sgst_tax_rate']				= $row['sgst_tax_rate'];
		$info['igst_tax_per']				= $row['igst_tax_per'];
		$info['igst_tax_rate']				= $row['igst_tax_rate'];
		$info['product_tax_cat']			= $row['product_tax_cat'];
		$info['currency_id']				= $row['currency_id'];
		$info['currency_rate']				= $row['currency_rate'];
		$info['product_rate_conv']			= $row['product_rate_conv'];
		$info['product_amount_conv']		= $row['product_amount_conv'];
		$info['product_discount_conv']		= $row['product_discount_conv'];
		$info['cgst_tax_rate_conv']			= $row['cgst_tax_rate_conv'];
		$info['sgst_tax_rate_conv']			= $row['sgst_tax_rate_conv'];
		$info['igst_tax_rate_conv']			= $row['igst_tax_rate_conv'];
		$info['product_total_conv']			= $row['product_total_conv'];
		$info['orange']						= $row['orange'];
		$info['mfg']						= $row['mfg'];
		$info['trading']					= $row['trading'];
		$info['repairing']					= $row['repairing'];
		$info['other']						= $row['other'];
		$info['orange_total']		  =	$row['orange_total'];
		$info['mfg_total']			  = $row['mfg_total'];
		$info['trading_total']		  = $row['trading_total'];
		$info['repairing_total']		  = $row['repairing_total'];
		$info['other_total']		      = $row['other_total'];

		//var_dump($info);
		$inserid = add_record("tbl_quotation_trn", $info, $dbcon);

		$copy_qry = "Insert into tbl_quotation_trn (inquiry_type,project_wise,product_id,cat_id,product_desc,product_spec,product_spec_id,level_id,product_qty,unitid,product_rate,product_discount,discount_per,product_amount,product_total,company_id,user_id,quot_trn_status,product_rate_dollar,product_amount_dollar,product_total_dollar,product_hp,cgst_tax_per,cgst_tax_rate,sgst_tax_per,sgst_tax_rate,igst_tax_per,igst_tax_rate,product_tax_cat,currency_id,currency_rate,product_rate_conv,product_amount_conv,product_discount_conv,cgst_tax_rate_conv,sgst_tax_rate_conv,igst_tax_rate_conv,product_total_conv,pid) 
		select inquiry_type,project_wise,product_id,cat_id,product_desc,product_spec,product_spec_id,level_id,product_qty,unitid,product_rate,product_discount,discount_per,product_amount,product_total,company_id,user_id,3,product_rate_dollar,product_amount_dollar,product_total_dollar,product_hp,cgst_tax_per,cgst_tax_rate,sgst_tax_per,sgst_tax_rate,igst_tax_per,igst_tax_rate,product_tax_cat,currency_id,currency_rate,product_rate_conv,product_amount_conv,product_discount_conv,cgst_tax_rate_conv,sgst_tax_rate_conv,igst_tax_rate_conv,product_total_conv," . $inserid . " from tbl_quotation_trn where quot_trn_status=0 and pid=" . $row['quot_trn_id'];
		$copy_qry_rs = $dbcon->query($copy_qry);
		$quotation_trn_id = $dbcon->insert_id;

		//echo $inserid;

		/*$copy_qry="Insert into tbl_quotation_trn (inquiry_type,project_wise,product_id,product_desc,product_spec,level_id,product_qty,unitid,product_rate, product_discount,discount_per,product_amount,formulaid,tax_name1,tax_amount1,tax_name2, tax_amount2,tax_name3,tax_amount3,product_total,company_id,user_id,quot_trn_status,product_rate_dollar,product_amount_dollar,product_total_dollar,product_hp,cgst_tax_per,cgst_tax_rate,sgst_tax_per,sgst_tax_rate,igst_tax_per,igst_tax_rate,product_tax_cat) ";*/
	}
	return $inserid;
}

function get_quotation($dbcon, $quotation_id)
{

	$query = "select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name from tbl_quotation as quot
	left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
	left join tbl_customer as cust on cust.cust_id=quot.cust_id
	left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
	left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
	where quot.quotation_id=" . $quotation_id;
	$rel = brp_mysqli_fetch_assoc($dbcon->query($query));

	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = " . $rel['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
	$userPhone = $userData['user_phone'] ? 'Mo.: ' . $userData['user_phone'] : '';
	$userEmail = $userData['user_mail'] ? ' - Email: ' . $userData['user_mail'] : '';

	$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';

	$set = "select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=" . $rel['company_id'];

	$comp_rel = brp_mysqli_fetch_assoc($dbcon->query($set));
	$header = '<div style="text-align:right;"><img src="' . DOMAIN_F . LOGO . $comp_rel["logo"] . '" style="width:3.27in;padding-top:25px;" /></div>';
	$footer ='<hr>';
	$companySettings = getCompanySettings($dbcon);
	if ($companySettings) {
		$quotation_print_content = $rel['quot_header'] ? $rel['quot_header'] : $companySettings['quotation_print_content'];
		$quotation_print_content = str_ireplace(array("\r", "\n", '\r', '\n'), '', $quotation_print_content);
	}

	$html = '<html>
	<head>					
	<title>Quotation - ' . $rel['quotation_no'] . '</title>
	<style type="text/css">
	/*
	.page{
		width:8.27in;
		height:10.69in;
		}*/
		.nextpage
		{
			page-break-after: always;
		}
		table{
			border-collapse:collapse;
			width:100%;
		}

		table tr,td{
			border:1px solid #000 !important;
			/*page-break-inside:avoid;*/
		}
		.quot_annex_content_div table tr,td{
			padding:5px;
		}
		.blueHeading {
			color: #365f91;
		}

		</style>
		</head>
		<body>
		<!--Show Logo in other pages-->
		<!--<htmlpageheader name="otherpages" style="display:none">
		<div style="text-align:center">' . $header . '</div>
		</htmlpageheader>-->
		<!--<htmlpagefooter name="otherpages_footer" style="display:none">
		<div style="text-align:center">' . $footer . '</div>
		</htmlpagefooter>-->
		<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
		<div>
		<table cellpadding="5" cellspacing="5" border="0" style="font-size: 20px; font-family: Proxia Nova; border: 0;">
		<tr style="border: 0;">
		<td style="border: 0; text-align: right; font-size: 15px;">Date: ' . date("d-M-Y", strtotime($rel['quotation_date'])) . '</td>
		</tr>
		</table>
		<table cellpadding="5" cellspacing="5" border="0" style="font-size: 20px; font-family: Proxia Nova; border: 0;">
		<tr style="border: 0;">
		<td style="border: 0;">
		<p style="float: left; width: 100%;">
		<strong>To,<br />' . $rel['cust_name'] . ',</strong><br/>
		<strong style="color: #999999;">' . $rel['c_con_fname'] . ' ' . $rel['c_con_lname'] . ',<br />
		' . ($quot_address) . '</strong>
		</p>
		<br />
		' . stripslashes($quotation_print_content) . '
		<br /><br />
		<p>Regards</p>
		<p>' . $userData['user_name'] . ' - ' . $userData['usertype_name'] . ',</p>
		<p>' . $userPhone . '' . $userEmail . ',</p>
		<p>BRP Software Solutions LLP</p>
		</td>
		</tr>
		</table>
		</div>
		<center class="nextpage"></center>
		<div>
		<table style="font-size:16px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
		<tr style="border: 0;">
		<td colspan="3" style="text-align:center;font-size:15px;font-weight:bold; border: 0; padding-bottom: 20px;"> 
		<h2 class="blueHeading">Commercials for BRP DataSuite</h2>
		</td>
		</tr>
		<tr>
		<td rowspan="9" style="text-align:left;vertical-align:top;border:1px solid;width:50%;"> 
		<strong>' . $rel['cust_name'] . ',</strong><br/>
		<strong style="color: #999999;">' . $rel['c_con_fname'] . ' ' . $rel['c_con_lname'] . ',<br />
		' . ($quot_address) . '</strong>
		</td>
		<td style="text-align:left;border:1px solid;width:20%;"> 
		Quotation No
		</td>
		<td style="text-align:left;border:1px solid;width:30%;"> 
		<strong>' . $rel['quotation_no'] . '</strong>
		</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> 
		Quotation Date
		</td>
		<td style="text-align:left;border:1px solid;"> 
		' . date("d-M-Y", strtotime($rel['quotation_date'])) . '
		</td>
		</tr>
		
		<tr>
		<td style="text-align:left;border:1px solid;"> 
		Inquiry Ref No
		</td>
		<td style="text-align:left;border:1px solid;"> 
		' . $rel['inquiry_no'] . '
		</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> 
		Inquiry Ref Date
		</td>
		<td style="text-align:left;border:1px solid;"> 
		' . date("d-M-Y", strtotime($rel['inquiry_date'])) . '
		</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> 
		Executive Name
		</td>
		<td style="text-align:left;border:1px solid;"> 
		' . $userData['user_name'] . '
		</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> 
		Inquiry Source
		</td>
		<td style="text-align:left;border:1px solid;"> 
		' . $rel['rb_name'] . '
		</td>
		</tr>
		</table>
		<br />
		<table style="font-size:16px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		<thead>
		<tr>
		<th style="width:2%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
		<th style="width:50%;text-align:center;border:1px solid;">Item Description</th>
		<th style="width:8%;text-align:center;border:1px solid;">Qty</th>
		<th style="width:10%;text-align:center;border:1px solid;">Unit Price</th>
		<th style="width:10%;text-align:center;border:1px solid;">Discount</th>
		<th style="width:10%;text-align:center;border:1px solid;">GST</th>
		<th style="width:15%;text-align:center;border:1px solid;">Total Price</th>
		</tr>
		</thead>
		<tbody>';
	if ($rel['inquiry_type'] != '2') {
		$trn_qry = "select trn.*,pro.product_name,unit.unit_name from tbl_quotation_trn as trn 
			left join product_mst as pro on pro.product_id=trn.product_id
			left join unit_mst as unit on unit.unitid=trn.unitid
			where trn.quot_trn_status=0 and trn.quotation_id=" . $rel['quotation_id'];
	} else {
		$trn_qry = "select trn.*,pro.product_name from tbl_quotation_project_trn as trn 
			left join product_mst as pro on pro.product_id=trn.product_id
			where trn.quotation_projecttrn_status=0 and trn.quotation_id=" . $rel['quotation_id'];
	}

	$trn_qry_rs = $dbcon->query($trn_qry);
	$p = 1;
	$ttl_amt = 0;
	$ttl_qty = 0;
	$cnt = mysqli_num_rows($trn_qry_rs);
	while ($trn_rel = brp_mysqli_fetch_assoc($trn_qry_rs)) {
		$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';

		$html .= '<tr style="border:none;border-left:1px solid;border-right:1px solid;">
			<td style="text-align:center;border:1px solid;vertical-align:top;">' . $p . '</td>
			<td style="text-align:left;border:1px solid;vertical-align:top;">
			<strong>' . $trn_rel['product_name'] . '</strong><br/>
			' . $product_desc . '
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			' . $trn_rel['product_qty'] . '
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">';
		if ($trn_rel['act_amt_flag'] == '1') {
			$html .= "Extra At Actual";
		} else {
			$html .= $trn_rel['product_rate'];
		}
		$html .= '<td style="text-align:center;border:1px solid;vertical-align:top;">' . $trn_rel['product_discount'] . '</td>';
		$html .= '<td style="text-align:center;border:1px solid;vertical-align:top;"></td>';

		$html .= '</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">';
		if ($trn_rel['act_amt_flag'] == '1') {
			$html .= "Extra At Actual";
		} else {
			$html .= $trn_rel['product_total'];
		}
		$html .= '</td>
			</tr>';
		$ttl_qty = $ttl_qty + $trn_rel['product_qty'];
		if ($trn_rel['act_amt_flag'] != '1') {
			$ttl_amt = $ttl_amt + $trn_rel['product_total'];
		}

		$p++;
	}
	$pr = 10 - $cnt;
	for ($j = 0; $j < $pr; $j++) {
		$html .= '<tr style="border:none;border-left:1px solid;border-right:1px solid;">
			<td style="border:none;border-left:1px solid;border-right:1px solid;height:40px;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<!--Amish Soni Start 15-03-2021-->
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<!--Amish Soni End 15-03-2021-->
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			</tr>';
	}
	$html .= '<tr>
		<td colspan="2" style="text-align:center;border:1px solid;">Total</td>
		<td style="text-align:center;border:1px solid;">
		' . $ttl_qty . '
		</td>
		<td style="text-align:center;border:1px solid;"></td>
		<!--Amish Soni Start 15-03-2021-->
		<td style="text-align:center;border:1px solid;"></td>
		<td style="text-align:center;border:1px solid;"></td>
		<td style="text-align:center;border:1px solid;">
		' . $ttl_amt . '
		</td>
		</tr>
		<tr>
		<td colspan="2" style="text-align:center;border:1px solid;">Total (In Words)</td>
		<td colspan="5" border="0" style="border: 1px solid; !important; text-align: right;">' . convert_number_to_words_new($ttl_amt) . '</td></tr>';
	$html .= '</tbody></table>

		<div style="clear:both;"></div>
		</div>
		<!--page1 end-->';

	/*$html.='';*/

	/* Get Terms And Condition Start */
	$terms_qry = "select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
		left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
		where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=" . $rel['quotation_id'] . " order by qtrm.tc_priority";
	$terms_qry_rs = $dbcon->query($terms_qry);
	if (mysqli_num_rows($terms_qry_rs)) {
		$html .= '<center class="nextpage"></center>
			<div><table width="100%" style="font-size:16px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3"><tbody>';
		$t = 1;
		while ($term_rel = brp_mysqli_fetch_assoc($terms_qry_rs)) {
			$string = (nl2br($term_rel['tc_details']));

			$html .= '<tr style="border: 0;"><td style="border: 0; padding-top: 20px;"><h2 class="blueHeading">' . $term_rel['tc_name'] . '</h2></td></tr>';
			$html .= '<tr style="border: 0;">
				<td width="70%" style="border: 0;width:70%;text-align:left;border:1px solid #000;padding:5px;">' . $string . '</td>
				</tr>';
			$t++;
		}
		$html .= '</tbody></table></div>';
	}
	if (trim($rel['quot_annex_content'])) {
		$html .= '<center class="nextpage"></center>';
		$html .= '<div class="quot_annex_content_div" style="font-size: 16px;">' . $rel['quot_annex_content'];
		$html .= '</div>';
	}
	/* Check Annexure Attachments End */

	$html .= '<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
	ob_end_clean();
	include("../../../view/export/mpdf/mpdf.php");
	$mpdf = new mPDF('', 'A4', '0', 'proximanova', '10', '10', '35', '10', '1', '1');
	//		$mdf->SetFont('ProximaNova');
	$mpdf->defaultheaderfontsize = 10; /* in pts */
	$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
	$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
	$mpdf->defaultfooterfontsize = 10; /* in pts */
	$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
	$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
	$mpdf->SetHTMLHeader($header);
	$mpdf->SetHTMLFooter($footer);
	$mpdf->SetWatermarkText();
	$mpdf->showWatermarkText = true;
	$mpdf->allow_charset_conversion = true;
	$mpdf->charset_in = 'UTF-8';
	$mpdf->WriteHTML($html);
	// $mpdf->Output();
	// $mpdf->Output('http://www.brperp.com/common_brp_devlopment/view/upload/quotation_pdf_file/Quotation_'.$quotation_id.'.pdf','f');
	$mpdf->Output('../../../view/upload/quotation_pdf_file/Quotation_' . $quotation_id . '.pdf', 'f');
	ob_clean();
	return $quotation_id;
}
function umaboy_quotation_print($dbcon, $quotation_id, $save_file)
{
	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
		WHERE u.active = 0 AND u.user_id = " . $_SESSION['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
	$userPhone = $userData['user_phone'] ? 'Mo.: ' . $userData['user_phone'] : '';
	$userEmail = $userData['user_mail'] ? ' - Email: ' . $userData['user_mail'] : '';

	$type = 'pdf';
	if (strtolower($type) == 'pdf') {
		//Quotation Data
		$query = "select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name from tbl_quotation as quot
			left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
			left join tbl_customer as cust on cust.cust_id=quot.cust_id
			left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
			left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
			where quot.quotation_id=" . $quotation_id;
		$rel = mysqli_fetch_assoc($dbcon->query($query));
		//p($rel);
		if (!$rel) {
			header("Location: " . ROOT . CRM_ROOT . "quotation_list");
		}

		if ($rel['quot_type'] == '0') {
			$currency_name = '(INR)';
			$currency_word_start = 'Rupees';
			$currency_word_end = 'Paise';
		} else {
			$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="' . $rel['currency_id'] . '" ';
			$currency_rel = mysqli_fetch_assoc($dbcon->query($currency_sql));

			$currency_name = '(' . ucfirst(strtolower($currency_rel['currency_code'])) . ')';
			$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
			$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
		}

		/*if($rel['currency_id'] == '68'){
	$currency_name = '(INR)';
}else{
	$currency_name = '(USD)';
}*/


		$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';
		//Company Data
		/*$comp_qry="select * from tbl_company as comp 
where comp.company_id=".$rel['company_id'];
*/
		$set = "select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=" . $rel['company_id'];

		$comp_rel = mysqli_fetch_assoc($dbcon->query($set));
		$header = '<div style="text-align:right;"><img src="' . DOMAIN_F . LOGO . $comp_rel["logo"] . '" style="width:8.27in;" /></div>';
		//$header =$comp_rel["logo"];
		//$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';
		$footer = '<hr>';
		$approve_status = '';
		if ($rel['approve_status'] == '0') {
			$approve_status = ' (DRAFT)';
		}
		$inquiry_type = $rel['inquiry_type'];
		//Amish Soni Start 16-03-2021
		$companySettings = getCompanySettings($dbcon);
		$quotation_print_content = $rel['quot_header'];
		if ($companySettings) {
			$quotation_print_content = $companySettings['quotation_print_content'] ? $companySettings['quotation_print_content'] : $quotation_print_content;
			$quotation_print_content = str_ireplace(array("\r", "\n", '\r', '\n'), '', $quotation_print_content);
			$general_terms_condition = $companySettings['general_terms_condition'] ? $companySettings['general_terms_condition'] : "";
			$general_terms_condition = str_ireplace(array("\r", "\n", '\r', '\n'), '', $general_terms_condition);
			$battery_limits_and_schedule_exclusion = $companySettings['battery_limits_and_schedule_exclusion'] ? $companySettings['battery_limits_and_schedule_exclusion'] : $general_terms_condition;
			$battery_limits_and_schedule_exclusion = str_ireplace(array("\r", "\n", '\r', '\n'), '', $battery_limits_and_schedule_exclusion);
		}
		//Amish Soni End 16-03-2021
		$html = '<html>
<head>					
<title>Quotation - ' . $rel['quotation_no'] . '</title>
<style type="text/css">
/*
.page{
	width:8.27in;
	height:10.69in;
	}*/
	.nextpage
	{
		page-break-after: always;
	}
	table{
		border-collapse:collapse;
		width:100%;
	}

	table tr,td{
		border:1px solid #000 !important;
		/*page-break-inside:avoid;*/
	}
	.quot_annex_content_div table tr,td{
		padding:5px;
	}
	.blueHeading {
		color: #365f91;
	}

	</style>
	</head>
	<body>
	<!--Show Logo in other pages-->
	<htmlpageheader name="otherpages" style="display:none">
	<div style="text-align:center">' . $header . '</div>
	</htmlpageheader>
	<!--<htmlpagefooter name="otherpages_footer" style="display:none">
	<div style="text-align:center">' . $footer . '</div>
	</htmlpagefooter>-->
	<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
	<div>
	<table cellpadding="5" cellspacing="5" style="font-size: 14x; font-family: Proxia Nova; margin-top: 30px;">
	<tr style="border: none;">
	<td colspan="4" style="text-align: center; font-weight: bold; border: none;"><h2>Quotation</h2></td>
	</tr>
	<tr>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">Quotation No. :</td>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">' . $rel['quotation_no'] . '</td>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">Date :</td>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">' . date("d-M-Y", strtotime($rel['quotation_date'])) . '</td>
	</tr>
	<tr>
	<td colspan="4" style=" text-align: left; font-size: 14px; font-weight: bold;">Customer Details:</td>
	</tr>
	<tr>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">Name :</td>
	<td colspan="3" style=" text-align: left; font-size: 15px;">' . $rel['cust_name'] . '</td>
	</tr>
	<tr>
	<td style="font-weight: bold; text-align: left; font-size: 15px; vertical-align: top;">Address :</td>
	<td colspan="3" style=" text-align: left; font-size: 15px;">' . ($quot_address) . '</td>
	</tr>
	<tr>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">Phone :</td>
	<td style=" text-align: left; font-size: 15px;"></td>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">Mobile No :</td>
	<td style=" text-align: left; font-size: 15px;">' . $rel['cust_mobile'] . '</td>
	</tr>
	<tr>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">Email :</td>
	<td colspan="3" style=" text-align: left; font-size: 15px;">' . strtolower($rel['cust_email']) . '</td>
	</tr>
	<tr>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">C.C :</td>
	<td colspan="3" style=" text-align: left; font-size: 15px;">' . $userData['user_name'] . '</td>
	</tr>
	<tr>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">Kind. Attn :</td>
	<td style=" text-align: left; font-size: 15px;">' . $rel['c_con_fname'] . ' ' . $rel['c_con_lname'] . '</td>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">ReF. By :</td>
	<td style=" text-align: left; font-size: 15px;">' . $rel['rb_name'] . '</td>
	</tr>
	</table>
	</div>';
		if ($inquiry_type == 1) {
			$trn_qry = "select trn.*,pro.product_name,unit.unit_name from tbl_quotation_trn as trn 
		left join product_mst as pro on pro.product_id=trn.product_id
		left join unit_mst as unit on unit.unitid=trn.unitid
		where trn.quot_trn_status=0 and trn.quotation_id=" . $rel['quotation_id'];
		} else {
			$trn_qry = "select trn.*,pro.product_name from tbl_quotation_project_trn as trn left join product_mst as pro on pro.product_id=trn.product_id where trn.quotation_projecttrn_status=0 and trn.quotation_id=" . $rel['quotation_id'];
		}
		$trn_qry_rs = $dbcon->query($trn_qry);
		$p = 1;
		$ttl_amt = 0;
		$ttl_qty = 0;
		$charges_qty = 0;
		$total_gst = 0;
		$total_cs_gst = 0;
		$total_i_gst = 0;
		$cnt = mysqli_num_rows($trn_qry_rs);
		while ($trn_rel = mysqli_fetch_assoc($trn_qry_rs)) {
			$chkimg = $dbcon->query("SELECT im_name FROM tbl_product_images WHERE im_status = 0 and im_product = " . $trn_rel['product_id'] . " ORDER BY img_id DESC LIMIT 1");
			$getimg = mysqli_fetch_assoc($chkimg);
			$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';
			$gst_per = $trn_rel['cgst_tax_per'] + $trn_rel['sgst_tax_per'] + $trn_rel['igst_tax_per'];
			$gst_rate = $trn_rel['cgst_tax_rate'] + $trn_rel['sgst_tax_rate'] + $trn_rel['igst_tax_rate'];

			$ttl_amt = $gst_rate + $trn_rel['product_amount'];
			if ($trn_rel['cgst_tax_rate'] != 0 || $trn_rel['sgst_tax_rate'] != 0) {
				$total_cs_gst += $gst_rate;
			} else {
				$total_i_gst += $gst_rate;
			}

			$html .= '<center class="nextpage"></center><div><table style="width: 100%; font-size: 13px; margin-top: 30px;">
		<tr style="border:1px solid;">
		<td style="text-align:center;border:1px solid;vertical-align:top; font-size: 16px; background: #ededed;"><strong>' . $p . ') ' . $trn_rel['product_name'] . '</strong></td>
		</tr>';
			if (!empty($getimg['im_name'])) {
				$html .= '<tr style="border:none;">
			<td><br><img src="' . DOMAIN_F . PRO_IMG_VWING . $getimg['im_name'] . '" /></td>
			</tr>
			<tr style="border:none;">
			<td style="text-align: center;"><br>img for refernce</td>
			</tr>';
			}
			$html .= '</table>
		</div>
		<center class="nextpage"></center><div>
		<table style="margin-top: 30px;">
		<tr style="border:1px solid;">
		<td style="text-align:left;border:1px solid;vertical-align:top;background: #ededed;"><strong>TECHNICAL SPECIFICATION</strong></td>
		</tr>
		</table>
		' . $trn_rel['product_spec'] . '
		<div>
		<h3 style="text-decoration: underline;"><strong>PRICE FOR THE MACHINE ' . $currency_name . ' :</strong></h3>
		</div>
		<table style="text-align: right; margin-left: 350px;">
		<tr style="border:1px solid;">
		<td style="border:1px solid;font-weight: bold;">BASIC AMOUNT :</td>
		<td style="border:1px solid;font-weight: bold;"> ' . indian_number($trn_rel['product_amount'], 2) . '</td>
		</tr>';
			if ($rel['qt_add_state'] == $comp_rel['stateid']) {
				$html .= '<tr style="border:1px solid;">
			<td style="border:1px solid;font-weight: bold;">CGST (' . ($gst_per / 2) . ' %) :</td>
			<td style="border:1px solid;font-weight: bold;">' . indian_number(($trn_rel['cgst_tax_rate']), 2) . '</td>
			</tr>
			<tr style="border:1px solid;">
			<td style="border:1px solid;font-weight: bold;">SGST (' . ($gst_per / 2) . ' %) :</td>
			<td style="border:1px solid;font-weight: bold;">' . indian_number(($trn_rel['sgst_tax_rate']), 2) . '</td>
			</tr>';
			} else {
				$html .= '<tr style="border:1px solid;">
			<td style="border:1px solid;font-weight: bold;">IGST (' . ($gst_per) . ' %) : </td>
			<td style="border:1px solid;font-weight: bold;">' . indian_number($trn_rel['igst_tax_rate'], 2) . '</td>
			</tr>';
			}
			$html .= '<tr style="border:1px solid;">
		<td style="border:1px solid;font-weight: bold;">NET AMOUNT :</td>
		<td style="border:1px solid;font-weight: bold;">' . indian_number($ttl_amt, 2) . '</td>
		</tr>
		</table>
		</div>
		<center class="nextpage"></center><div>
		<table style="margin-top: 30px;">
		<tr style="border:1px solid;">
		<td style="text-align:left;border:1px solid;vertical-align:top;background: #ededed;"><strong>SILENT FEATURES</strong></td>
		</tr>
		</table>
		' . $product_desc;
			$p++;
		}

		$html .= '<center class="nextpage"></center><div>
	<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
	<thead>
	<tr>
	<th style="text-align:left; border:1px solid; font-weight: bold; background: #ededed;">GENERAL TERMS & CONDITIONS</th>
	</tr>
	<tr style="border:none">
	<th style="text-align:center; border:none"></th>
	</tr>
	</thead><tbody>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;background: #ededed;">PRICE : </td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">Ex- Works Ahmedabad, Packing, Forwarding, Octroi, Unloading/ Insurance to your Account.</td>
	</tr>
	<tr style="border:none;padding:10px;">
	<td style="border:none;padding:10px;"></td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;background: #ededed;">DELIVERY TIME :</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">To be Agreed upon 6 to 8 weeks on receipt of your techno-commercial clear order at our end.</td>
	</tr>
	<tr style="border:none;padding:10px;">
	<td style="border:none;padding:10px;"></td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;background: #ededed;">PAYMENTS :</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">30 % along with Purchase Order and Balance against Performa Invoice, before Delivery.</td>
	</tr>
	<tr style="border:none;padding:10px;">
	<td style="border:none;padding:10px;"></td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;background: #ededed;">BANK DETAILS :</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">Beneficiary : Shree Umiya F- Tech Machines</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">Bank Name : Yes Bank</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">Branch Name : Maninagar Branch, Ahmedabad</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">Account Number : 021584600002099</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">RTGS/NEFT/IFSC Code : YESB0000215</td>
	</tr>
	<tr style="border:none;padding:10px;">
	<td style="border:none;padding:10px;"></td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;background: #ededed;">INSTALLATION & TRAINING :</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">By Shree Umiya F-Tech Machines<br>(Traveling, Boarding & Loading for Service Engineers to be borne by Customer)</td>
	</tr>
	<tr style="border:none;padding:10px;">
	<td style="border:none;padding:10px;"></td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;background: #ededed;">NOTE :</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">This Offer is Valid for 30 days from the date of offer. Technical Specification may be changed without  any prior notice. Product image are just for the reference.</td>
	</tr>
	<tr style="border:none;padding:10px;">
	<td style="border:none;padding:10px;"></td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;background: #ededed;">JURISDICTION :</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">Our Quotation is Subject to Jurisdiction Situated within Ahmedabad Municipal Corporation Only.</td>
	</tr>
	<tr style="border:none;padding:10px;">
	<td style="border:none;padding:10px;"></td>
	</tr>
	<tr style="border:none">
	<th style="text-align:left; border:none">Yours Faithfully</th>
	</tr>
	<tr style="border:none; height: 30px;">
	<th style="text-align:left; border:none">For ' . $comp_rel['company_name'] . '</th>
	</tr>
	</tbody>
	</table>
	</div>';

		$html .= '<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
		// echo $trn_qry;
		// echo $html;exit;
		// $filename = 'umaboy_quotation_print_'.$quotation_id.'.pdf';
		$file_name = $rel['quotation_no'] . '.pdf';
		$file_name = str_ireplace("/", "_", $file_name);
		ob_end_clean();
		include("../../../view/export/mpdf/mpdf.php");
		$mpdf = new mPDF('', 'A4', '0', 'proximanova', '10', '10', '40', '10', '1', '1');
		//		$mdf->SetFont('ProximaNova');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		//Show page number : Dimple Panchal (05-Apr-2021)
		$mpdf->pagenumPrefix = ' ';
		$mpdf->pagenumSuffix = ' / ';
		$mpdf->nbpgPrefix = ' ';
		$mpdf->nbpgSuffix = ' pages';
		$mpdf->SetFooter('{PAGENO}{nbpg}');
		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion = true;
		$mpdf->charset_in = 'UTF-8';
		$mpdf->WriteHTML($html);
		if ($save_file == "No") {
			$mpdf->Output();
		} else {
			$mpdf->Output('../../../view/upload/mail_attach/' . $file_name, 'f');
		}
		ob_clean();
		return $file_name;
	}
}
