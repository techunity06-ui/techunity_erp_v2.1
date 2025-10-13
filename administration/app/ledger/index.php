<?php
session_start(); //start session
//error_reporting(E_ALL); 
//ini_set('display_errors', '1');
$AJAX = true;
include('../../include/urlfileinner.php');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	ADMINISTRATOR_LEDGER_DELETE,
	ADMINISTRATOR_LEDGER_EDIT,
	ADMINISTRATOR_LEDGER_APPROVE,
	ADMINISTRATOR_LEDGER_FINAL_APPROVE
]);

$POST = ($_POST != NULL) ? bulk_filter($dbcon, $_POST) : bulk_filter($dbcon, $_GET);



if (strtolower($POST['mode']) == "fetch") {
	//Amish Soni 15-09-2020
	//$approve_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'aprv',$dbcon);
	//$final_approve_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'final_aprv',$dbcon);

	$getspecialConfiguration = getspecialConfiguration($dbcon);

	$approve_btn_per = in_array(ADMINISTRATOR_LEDGER_APPROVE, $bulkAccessArray);
	$final_approve_btn_per = in_array(ADMINISTRATOR_LEDGER_FINAL_APPROVE, $bulkAccessArray);

	$branch_id = $POST['branch_id'];
	$g_id = $POST['gr_id'];

	$where = '';
	if (!empty($g_id)) {
		$where = " and l.l_group =" . $g_id . "";
	}

	$appData = array();
	$i = 1;
	$aColumns = array('l.l_id', 'l.emp_profile_img', 'l.l_name', 'l.l_group', 'cit.city_name', 'l.user_id', 'g.g_name', 'l.l_form', 'l.l_status', 'l.ledger_code', 'l.is_deletable');
	$sIndexColumn = "l_id";
	$isWhere = array("l_status !=2 and l.company_id in (0," . $_SESSION['company_id'] . ") " . $where);
	$sTable = " tbl_ledger as l";
	$isJOIN = array("left join tbl_group as g on g.g_id=l.l_group", "left join city_mst as cit on cit.cityid=l.cityid");
	$hOrder = "l.l_status desc";
	include($include . 'pagging.php');
	//$appData = array();
	$id = 1;
	foreach ($sqlReturn as $row) {

		if ($row['l_status'] == '0') {
			$status = "<strong style='color:green'>Approved</strong>";
			$change_status = "<a class='btn btn-success' onclick='changeStatus(\"" . $row['l_id'] . "\",\"" . $row['l_status'] . "\")'><i class='fa fa-check-square-o'></i></a>";
		} else {
			$status = "<strong style='color:red' >Pending</strong>";
			$change_status = "<a class='btn btn-danger' onclick='changeStatus(\"" . $row['l_id'] . "\",\"" . $row['l_status'] . "\")'><i class='fa fa-window-close'></i></a>";
		}

		//upload documnet only for salary accounts
		if ($row['l_group'] == '58') {
			$upload = '<a class="btn btn-success" data-original-title="Upload Document" data-toggle="tooltip" data-placement="top" href="' . ROOT . 'upload_document/' . $row['l_id'] . '">Upload Documents</a>';
		} else {
			$upload = '';
		}

		$row_data = array();
		$row_data[] = $row['sr'];
		if ($getspecialConfiguration['adk_permission'] == 0) {
			if (isset($row['emp_profile_img']) && !empty($row['emp_profile_img'])) {
				$imagePath = ROOT . 'administration/upload/emp_profile_image/' . $row['emp_profile_img'];
				$row_data[] = "<img class='text-center' src='$imagePath' alt='Member' width='50px' height='50px'>";
			} else {
				$imagePath = ROOT . 'administration/upload/emp_profile_image/no_profile.png';
				$row_data[] = "<img class='text-center' src='$imagePath' alt='Member' width='50px' height='50px'>";
			}
		}

		$row_data[] = $row['ledger_code'];
		$row_data[] = $row['l_name'];
		$row_data[] = $row['g_name'];
		$row_data[] = $row['city_name'];
		$row_data[] = $status;
		/*$row_data[] = $upload;*/

		if ($row['l_status'] == '0') {
			$used_ledger = $dbcon->query("SELECT count(general_book_id) as used_ledger FROM `tbl_general_book` 
                        where ledger_id = " . $row['l_id'] . " AND table_name != 'tbl_ledger'")
				->fetch_object()->used_ledger;
			//echo $used_ledger;
		}

		$edit_btn = '';
		$delete_btn = '';
		if (in_array(ADMINISTRATOR_LEDGER_EDIT, $bulkAccessArray) && $row['is_deletable'] == 0) {
			$edit_btn = '<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . ADMINISTRATION_ROOT . 'ledger_edit/' . $row['l_id'] . '"><i class="fa fa-pencil"></i></a>';
		}
		if (in_array(ADMINISTRATOR_LEDGER_DELETE, $bulkAccessArray) &&  $row['is_deletable'] == 0) {
			$delete_btn = ' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_ledger(' . $row['l_id'] . ')"><i class="fa fa-trash-o"></i></button>';
		}

		if ($row['l_form'] == 'customer_form') {
			$sold_btn = '<button class="btn btn-xs btn-primary" data-original-title="Allocate Sale Customer Product" data-toggle="tooltip" data-placement="top" onClick="alloc_sold_pro(' . $row['l_id'] . ');"><i class="fa fa-plus"></i></button>';
		} else {
			$sold_btn = '';
		}


		$row_data[] = $edit_btn . ' ' . $delete_btn . ' ' . $sold_btn;

		//Amish Soni 15-09-2020
		if ($row['is_deletable'] == 0) {
			$row_data[] = ($approve_btn_per && $final_approve_btn_per) ? $change_status : '';
		} else {
			$row_data[] = '';
		}

		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode($output);
} else if (strtolower($POST['mode']) == "get_open_form") {


	$gid = $POST['gid'];

	$q = $dbcon->query("select * from tbl_group where g_id='$gid'");
	$row = brp_mysqli_fetch_array($q);

	$form_id = $row['form_id'];
	$group_id = $row['g_id'];

	$res['form_id'] = $form_id;
	$res['group_id'] = $group_id;
	$res['group_parent_id'] = $row['g_pid'];

	echo json_encode($res);
	//echo $gid;
} else if (strtolower($POST['mode']) == "add") {
	//echo '<pre>';print_r($_POST);exit;
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	$info['l_name']			= $POST['ledger_name'];
	$info['l_group']		= $POST['ledger_grp'];
	$info['ledger_type']		= $POST['ledger_type'];

	if (isset($_FILES["emp_profile_img"]["name"]) && !empty($_FILES["emp_profile_img"]["name"])) {
		$file = $_FILES['emp_profile_img']['tmp_name'];
		$sourceProperties = getimagesize($file);
		$fileNewName = time();
		$folderPath = "../../view/upload/emp_profile_image/";
		$ext = pathinfo($_FILES['emp_profile_img']['name'], PATHINFO_EXTENSION);
		$imageType = $sourceProperties[2];
		switch ($imageType) {
			case IMAGETYPE_PNG:
				$imageResourceId = imagecreatefrompng($file);
				$targetLayer = imageResize($imageResourceId, $sourceProperties[0], $sourceProperties[1]);
				imagepng($targetLayer, $folderPath . $fileNewName . "_thump." . $ext);
				break;
			case IMAGETYPE_GIF:
				$imageResourceId = imagecreatefromgif($file);
				$targetLayer = imageResize($imageResourceId, $sourceProperties[0], $sourceProperties[1]);
				imagegif($targetLayer, $folderPath . $fileNewName . "_thump." . $ext);
				break;
			case IMAGETYPE_JPEG:
				$imageResourceId = imagecreatefromjpeg($file);
				$targetLayer = imageResize($imageResourceId, $sourceProperties[0], $sourceProperties[1]);
				imagejpeg($targetLayer, $folderPath . $fileNewName . "_thump." . $ext);
				break;
			default:
				echo "Invalid Image type.";
				exit;
				break;
		}
		$info['emp_profile_img'] = $fileNewName . "_thump." . $ext;
		$empinfo['emp_profile_img'] = $fileNewName . "_thump." . $ext;
	}
	if (isset($_FILES["emp_signature_img"]["name"]) && !empty($_FILES["emp_signature_img"]["name"])) {
		$files = $_FILES['emp_signature_img']['tmp_name'];
		$sourceProperty = getimagesize($files);
		$fileNewNames = time();
		$folderPath = "../../../view/upload/signature/";
		$exts = pathinfo($_FILES['emp_signature_img']['name'], PATHINFO_EXTENSION);
		$imageTypes = $sourceProperty[2];
		switch ($imageTypes) {
			case IMAGETYPE_PNG:
				$imageResourceIds = imagecreatefrompng($files);
				$targetLayer = imageResize($imageResourceIds, $sourceProperty[0], $sourceProperty[1]);
				imagepng($targetLayer, $folderPath . $fileNewNames . "_thump." . $exts);
				break;
			case IMAGETYPE_GIF:
				$imageResourceIds = imagecreatefromgif($files);
				$targetLayer = imageResize($imageResourceIds, $sourceProperty[0], $sourceProperty[1]);
				imagegif($targetLayer, $folderPath . $fileNewNames . "_thump." . $exts);
				break;
			case IMAGETYPE_JPEG:
				$imageResourceIds = imagecreatefromjpeg($files);
				$targetLayer = imageResize($imageResourceIds, $sourceProperty[0], $sourceProperty[1]);
				imagejpeg($targetLayer, $folderPath . $fileNewNames . "_thump." . $exts);
				break;
			default:
				echo "Invalid Image type.";
				exit;
				break;
		}
		$info['emp_signature_img'] = $fileNewNames . "_thump." . $exts;
		if ($POST['form_type'] == 'emp_form') {
			$infousr['authorized_signature'] = $fileNewNames . "_thump." . $exts;
		}
	}

	if ($POST['ledger_grp'] == '58') {
		// Insert Employee Table Code
		$empinfo['user_id'] = $_SESSION['user_id'];
		$empinfo['company_id'] = $_SESSION['company_id'];
		$empinfo['employee_name'] = $POST['ledger_name'];
		$empinfo['branch_id'] = $POST['branch_id'];
		$empinfo['birth_date'] = (isset($POST['birth_date'])) ? date('Y-m-d', strtotime($POST['birth_date'])) : date('Y-m-d');
		$empinfo['joining_date'] = (isset($POST['joining_date'])) ? date('Y-m-d', strtotime($POST['joining_date'])) : date('Y-m-d');
		$empinfo['gender'] = (isset($POST['gender'])) ? $POST['gender'] : '';
		$empinfo['country_id']	= $POST['countryid'];
		$empinfo['state_id'] = $POST['stateid'];
		$empinfo['city_id'] = $POST['cityid'];
		$empinfo['cust_pincode'] = (isset($POST['cust_pincode'])) ? $POST['cust_pincode'] : '';
		$empinfo['m_pan'] = (isset($POST['m_pan'])) ? $POST['m_pan'] : '';
		$empinfo['emp_email'] = strtolower($POST['emp_email']);
		$empinfo['emp_password'] = (isset($POST['emp_password'])) ? $POST['emp_password'] : '';
		$empinfo['emp_mobile']	= (isset($POST['emp_mobile'])) ? $POST['emp_mobile'] : '';
		$empinfo['emp_zone_id']	= (isset($POST['emp_zone_id'])) ? $POST['emp_zone_id'] : '';
		//$empinfo['emp_branch_id']	= (isset($POST['branch_id_emp']))?$POST['branch_id_emp']:'';
		$empinfo['emp_user_type']	= (isset($POST['emp_user_type'])) ? $POST['emp_user_type'] : '';
		$empinfo['alloc_state_id']	= (isset($POST['alloc_stateid'])) ? implode(",", $POST['alloc_stateid']) : '';
		$empinfo['alloc_city_id']	= (isset($POST['alloc_cityid'])) ? implode(",", $POST['alloc_cityid']) : '';
		$empinfo['report_to_user_type']	= (isset($POST['report_to_user_type'])) ? $POST['report_to_user_type'] : '';
		$empinfo['report_to_user_id']	= (isset($POST['report_to_user_id'])) ? $POST['report_to_user_id'] : '';
		$empinfo['open_balance'] = (isset($POST['opn_balance'])) ? $POST['opn_balance'] : '0';
		$empinfo['balance_typeid']	= (isset($POST['balance_typeid'])) ? $POST['balance_typeid'] : '';
		$empinfo['emergenecy_contact_name'] = (isset($POST['emergenecy_contact_name'])) ? $POST['emergenecy_contact_name'] : '';
		$empinfo['emergenecy_contact_number'] = (isset($POST['emergenecy_contact_number'])) ? $POST['emergenecy_contact_number'] : '';
		$empinfo['per_day_salary'] = '0';
		$empinfo['status'] = '0';
		$empinfo['shift_time'] = $POST['shift_time'];
		$empinfo['updated_at']	= date("Y-m-d H:i:s");
		$tr = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE status != 2 and invoice_type = 'EMPLOYEE' and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id']);
		$updateRecord = brp_mysqli_fetch_assoc($tr);
		$empinfo['series_id'] = $updateRecord['format_value'] . $updateRecord['taxinvoice_start'] . $updateRecord['end_format_value'];
		$empinsertid = add_record('hrms_employee', $empinfo, $dbcon, $branch_id);

		// Series Number Update Code
		$query = $dbcon->query("SELECT `id` FROM `hrms_employee`");
		$total_records = $query->num_rows;
		$updateInfo['taxinvoice_start'] = $total_records;
		$updateinvoiceid = update_record('tbl_invoicetype', $updateInfo, "invoice_type = 'EMPLOYEE' and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'], $dbcon, $branch_id);

		$info['employee_id'] = $empinsertid;
	}
	$info['ledger_code']	= $POST['ledger_code'];
	$info['l_code_id']	= $POST['code_id'];
	$info['common_email_id']	= $_POST['common_email_id'];
	$info['m_name']			= $POST['m_name'];
	$info['m_address']		= $POST['m_address'];
	$info['countryid']		= $POST['countryid'];
	$info['stateid']		= $POST['stateid'];
	$info['cityid']         = $POST['cityid'];
	$info['cust_pincode']	= $POST['cust_pincode'];
	$info['m_pan']          = (isset($POST['m_pan'])) ? $POST['m_pan'] : '';
	$info['company_name']	= $POST['company_name'];
	$info['cust_cont_name']	= $POST['cust_cont_name'];
	$info['cust_mobile']	= (isset($POST['cust_mobile']) && !empty($POST['cust_mobile'])) ? $POST['cust_mobile'] : '';
	$info['cust_email']	  = (isset($POST['cust_email']) && !empty($POST['cust_email'])) ? strtolower($POST['cust_email']) : '';
	$info['cust_website']	= $POST['cust_website'];
	$info['zone_id']		= $POST['zone_id'];
	$info['cust_remark']	= $POST['cust_remark'];
	$info['gst_no']         = $POST['gst_no'];
	$info['iec_no']			= $POST['iec_no'];
	$info['party_type']		= $POST['party_type'];
	$info['cust_gst_reg']	= $POST['cust_gst_reg'];
	$info['pay_terms']		= $POST['pay_terms'];
	$info['pay_method']		= $POST['pay_method'];
	$info['credit_limit']	= $POST['credit_limit'];
	$info['credit_days']	= $POST['credit_days'];
	$info['bill_type']		= $POST['bill_type'];
	$info['balance_typeid']	= $POST['balance_typeid'];
	$info['emp_mobile']		= $POST['emp_mobile'];
	$info['emp_email']		= $POST['emp_email'];
	$info['emp_password']	= $POST['emp_password'];
	$info['emp_zone_id']	= $POST['emp_zone_id'];
	$info['emp_user_type']	= $POST['emp_user_type'];
	$info['tax_value']		= $POST['tax_value'];
	$info['cust_assign_user']	= trim(check_crm_find_in_set_new($dbcon, $POST['cust_assign_user'], 1), ",");
	$info['cust_owner']	= $POST['cust_assign_user'];
	$info['party_sez']		= $POST['party_sez'];
	//$info['branch_id_employee']	= $POST['branch_id_emp'];
	//$info['l_status']		= '1';
	$info['usertype_terr']	= implode(",", $POST['usertype_terr']);
	$info['alloc_stateid']	= implode(",", $POST['alloc_stateid']);
	$info['alloc_cityid']	= implode(",", $POST['alloc_cityid']);
	$info['report_to_user_type']	= $POST['report_to_user_type'];
	$info['report_to_user_id']	= $POST['report_to_user_id'];
	$info['print_priority']		= $POST['print_priority'];
	$info['branch_id'] = $POST['branch_id'];
	$info['opn_balance']	= $POST['opn_balance'];
	$info['l_form']			= $POST['form_type'];
	$info['shift_time'] = $POST['shift_time'];
	$info['cdate']			= date("Y-m-d H:i:s");
	$info['user_id']		= $_SESSION['user_id'];
	$info['company_id']		= $_SESSION['company_id'];
	if ($POST['direct_ledger_add'] == 1) {
		$info['l_status']	= 0;
	} else {
		$info['l_status']	= 1;
	}
	/* new field Added by Dhruv */
	$info['ledger_alias']	= $POST['alias_name'];
	$info['enable_multi_currency_opening']	= (isset($POST['multi_currency']) && ($_POST['multi_currency'] == 'yes')) ? 1 : 0;
	$info['enable_branch_opening']	= (isset($POST['multi_branch']) && ($_POST['multi_branch'] == 'yes')) ? 1 : 0;
	$info['ledger_opening_balance_type']	= $POST['set_op_balance'];
	$info['enable_cost_center']	= (isset($POST['enable_cost_center']) && ($POST['enable_cost_center'] == 'yes')) ? 1 : 0;
	$info['enable_tds']	= (isset($POST['enable_tds']) && ($POST['enable_tds'] == 'yes')) ? 1 : 0;
	$info['tdstax_cat']	= $POST['tdstax_cat'];
	$info['party_pay_cat']	= $POST['party_pay_cat'];
	$info['enable_billbybill_opening'] = (isset($POST['enable_billbybill_opening']) && ($POST['enable_billbybill_opening'] == 'yes')) ? 1 : 0;
	$info['enable_tcs']	= (isset($POST['enable_tcs']) && ($POST['enable_tcs'] == 'yes')) ? 1 : 0;
	$info['enable_depreciation']	= (isset($POST['enable_depreciation']) && ($POST['enable_depreciation'] == 'yes')) ? 1 : 0;
	$info['enable_monthly_budget']	= (isset($POST['enable_monthly_budget']) && ($POST['enable_monthly_budget'] == 'yes')) ? 1 : 0;
	$info['ledger_Tax_type']	= $POST['ledger_Tax_type'];
	$info['ledger_gst_applicable']	= (isset($POST['ledger_gst_applicable']) && ($POST['ledger_gst_applicable'] == 'yes')) ? 1 : 0;
	$info['ledger_tax_category']	= $POST['ledger_tax_category'];
	$info['ledger_hsn']	= $POST['ledger_hsn'];
	$info['ledger_itc']	= $POST['ledger_itc'];
	$info['ledger_rcm']	= $POST['ledger_rcm'];
	$info['enable_bill_sunfry']	= (isset($POST['enable_bill_sunfry']) && ($POST['enable_bill_sunfry'] == 'yes')) ? 1 : 0;
	$info['enable_sez']	= (isset($POST['enable_sez']) && ($_POST['enable_sez'] == 'yes')) ? 1 : 0;
	$info['enable_cheque_deposit'] = (isset($POST['enable_cheque_deposit']) && ($POST['enable_cheque_deposit'] == 'yes')) ? 1 : 0;
	$info['enable_salesman'] = (isset($POST['enable_salesman']) && ($POST['enable_salesman'] == 'yes')) ? 1 : 0;
	/* End code: Dhruv */

	if ($POST['form_type'] == 'bank_form') {
		$info['acc_type']	= $POST['acc_type'];
		$info['bankid']	= $POST['bankid'];
		$info['branch_name']	= $POST['branch_name'];
		$info['acc_name']	= $POST['acc_name'];
		$info['acc_number']	= $POST['acc_number'];
		$info['acc_chequeno']	= $POST['acc_chequeno'];
		$info['acc_chequeleft']	= $POST['acc_chequeleft'];

		$bank_info = array();
		$bank_info['acc_type']          = ($POST['bankid']) ? '2' : '1';
		$bank_info['b_grp']             = $POST['ledger_grp'];
		$bank_info['bankid']            = $POST['bankid'];
		$bank_info['branch_name']       = $POST['branch_name'];
		$bank_info['cityid']            = $POST['cityid'];
		$bank_info['acc_name']          = $POST['acc_name'];
		$bank_info['acc_number']        = $POST['acc_number'];
		$bank_info['acc_chequeno']      = $POST['acc_chequeno'];
		$bank_info['acc_chequeleft']    = $POST['acc_chequeleft'];
		$bank_info['opn_balance']       = $POST['opn_balance'];
		$bank_info['acc_status']        = ACTIVE;
		$bank_info['cdate']             = date("Y-m-d H:i:s");
		$bank_info['user_id']           = $_SESSION['user_id'];
		$bank_info['company_id']        = $_SESSION['company_id'];
		$inserid1 = add_record('account_mst', $bank_info, $dbcon);
	}

	//echo "SELECT `l_id`,`l_name`,`l_status`,`l_group` FROM `tbl_ledger` WHERE l_status!=2 and `l_name` LIKe '".$POST['ledger_name']."' and l_group = ".$_POST['ledger_grp']." and company_id = ".$_SESSION['company_id'];
	$tr = $dbcon->query("SELECT `l_id`,`l_name`,`l_status`,`l_group` FROM `tbl_ledger` WHERE l_status!=2 and `l_name` ='" . $POST['ledger_name'] . "' and l_group = " . $_POST['ledger_grp'] . " and company_id = " . $_SESSION['company_id']);
	if ($tr->num_rows > 0) {
		$row['res'] = "-1";
	} else {
		$dbcon->query("update tbl_group set group_start_series=group_start_series+1 where g_id=" . $POST['ledger_grp'] . " and company_id=" . $_SESSION['company_id']);

		//pathik territory_id add 27-01-2022
		$info['territory_id']		= $POST['territory_id'];
		//pathik territory_id add 27-01-2022

		//pathik ip address wise login 02-03-2023 start
		$info['ip_add']		= $_POST['ip_add'];
		//pathik ip address wise login 02-03-2023 end

		$inserid = add_record('tbl_ledger', $info, $dbcon, '');

		foreach ($POST['tc_id_dom'] as $key => $name) {
			$infotrm['tc_id']			= $POST['tc_id_dom'][$key];
			$infotrm['tc_for']			= 0;
			$infotrm['tc_priority']		= $POST['tc_priority_dom'][$key];
			$infotrm['tc_details']		= $_POST['tc_details_dom'][$key];
			$infotrm['cust_id']			= $inserid;
			$infotrm['cdate']			= date("Y-m-d H:i:s");
			$infotrm['user_id']			= $_SESSION['user_id'];
			$infotrm['company_id']		= $_SESSION['company_id'];
			if (in_array($POST['tc_id_dom'][$key], $POST['disp_term_flag_dom'])) {
				$insertrmid = add_record('tbl_customer_term_trn', $infotrm, $dbcon, $branch_id);
			}
		}

		foreach ($POST['tc_id_exp'] as $key => $name) {
			$infotrm['tc_id']			= $POST['tc_id_exp'][$key];
			$infotrm['tc_for']			= 1;
			$infotrm['tc_priority']		= $POST['tc_priority_exp'][$key];
			$infotrm['tc_details']		= $_POST['tc_details_exp'][$key];
			$infotrm['cust_id']			= $inserid;
			$infotrm['cdate']			= date("Y-m-d H:i:s");
			$infotrm['user_id']			= $_SESSION['user_id'];
			$infotrm['company_id']		= $_SESSION['company_id'];
			if (in_array($POST['tc_id_exp'][$key], $POST['disp_term_flag_exp'])) {
				$insertrmid = add_record('tbl_customer_term_trn', $infotrm, $dbcon, $branch_id);
			}
		}
		$dbcon->query("update tbl_custmer_consignee set cust_ref_id=" . $inserid . " where cust_ref_id='0' and user_id=" . $_SESSION['user_id']);

		$dbcon->query("update tbl_ledger_attach_doc set l_id=" . $inserid . ",led_attach_status=0 where ref_type='0' and led_attach_status=3 and user_id=" . $_SESSION['user_id']);
		if ($inserid) {
			$ref_date = date("Y-m-d");
			$currency_trn['currency_id']	= $_SESSION['currency_id'];
			$currency_trn['currency_rate']	= 1;
			add_general_book_entry($dbcon, "tbl_ledger", $inserid, $POST['balance_typeid'], $inserid, $POST['opn_balance'], $general_book_id = '', $ref_date, $branch_id, $currency_trn, 'ledger', $inserid);

			//Insert LOG
			$log_entry = common_log_entry($dbcon, "ledger_add", 1, "tbl_ledger", $inserid);

			if ($POST['form_type'] == 'customer_form') {
				/* Add Record in customer Person Table Start */

				$info1['cust_contact_person_name']			= stripcslashes($POST['cust_cont_name']);
				$info1['isd_id']							= $POST['isd_id'];
				$info1['cust_contact_person_no']			= $POST['cust_mobile'];
				$info1['cust_contact_person_email']			= strtolower($POST['cust_email']);
				$info1['cust_id']							= $inserid;
				$info1['user_id']							= $_SESSION['user_id'];
				$info1['cust_contact_person_direct_status']	= 1;
				$insercntid = add_record("tbl_cust_contact_person", $info1, $dbcon, $branch_id);

				/* Add Record in customer Person Table End */

				$dbcon->query("update tbl_customer_bank set b_cust='$inserid' where b_cust='0' and userid=" . $_SESSION['user_id']);

				$dbcon->query("update tbl_cust_contact_person set cust_id='$inserid' where cust_id='0' and user_id=" . $_SESSION['user_id']);
				$dbcon->query("update tbl_cust_tranportation set cust_id='$inserid' where cust_id='0' and user_id=" . $_SESSION['user_id']);
			}

			if ($POST['form_type'] == 'emp_form') {
				/*Entry in User Table Start*/

				$infousr['common_email_id']	= $_POST['common_email_id'];
				$infousr['user_name']		= $POST['ledger_name'];
				$infousr['user_mail']		= strtolower($POST['emp_email']);
				$infousr['user_key']		= md5($POST['emp_password']);
				$infousr['user_type']		= $POST['emp_user_type']; //Fixed Type Employee
				$infousr['user_country']	= $POST['countryid'];
				$infousr['user_stat']		= $POST['stateid'];
				$infousr['user_city']		= $POST['cityid'];
				$infousr['user_phone']		= $POST['emp_mobile'];
				$infousr['usertype_terr']	= implode(",", $POST['usertype_terr']);
				$infousr['alloc_stateid']	= implode(",", $POST['alloc_stateid']);
				$infousr['alloc_cityid']	= implode(",", $POST['alloc_cityid']);
				$infousr['report_to_user_type']	= $POST['report_to_user_type'];
				$infousr['report_to_user_id']	= $POST['report_to_user_id'];
				$infousr['question_id']		= ($POST['forgotquestion_id'] != '') ? md5($POST['forgotquestion_id']) : '';
				$infousr['answer']			= ($POST['forgotgive_answer'] != '') ? md5($POST['forgotgive_answer']) : '';
				$infousr['user_address']	= $POST['m_address'];
				//pathik ipaddress wise login 02-03-2023 start
				$infousr['ip_add']			= $_POST['ip_add'];
				//pathik ipaddress wise login 02-03-2023 end
				$infousr['user_rid']		= $_SESSION['user_id'];
				$infousr['company_id']		= $_SESSION['company_id'];
				//$infousr['branch_id']		= $POST['branch_id_emp'];
				$infousr['payment_status'] 	= 1;
				$infousr['employee_id'] 	= $inserid; //Employee ID flag check
				if (isset($_POST['template_id']) && $_POST['template_id'] != '') {
					$temp = $dbcon->query("SELECT * FROM `template_access_permission` WHERE `id` = '" . $_POST['template_id'] . "'");
					$temprecord = brp_mysqli_fetch_assoc($temp);
					$infousr['template_access_perm_id'] = $temprecord['id'];
					$infousr['user_access_permission'] = $temprecord['template_access_permission'];
					$infousr['menu_show_permission'] = $temprecord['template_menu_show_permission'];
				}
				$inserusrid = add_record('users', $infousr, $dbcon, $branch_id);

				/*Entry in User Table End*/
			}

			/*Added By Dhruv*/
			if ((isset($POST['multi_currency'])) && ($POST['multi_currency'] == 'yes')) {
				$dbcon->query("update tbl_ledger_currency_opening set currency_ledger_id=" . $inserid . " where currency_ledger_id='0' and user_id=" . $_SESSION['user_id']);
			}
			if ((isset($POST['multi_branch'])) && ($POST['multi_branch'] == 'yes')) {
				$dbcon->query("update tbl_ledger_branch_opening set branch_ledger_id=" . $inserid . " where branch_ledger_id='0' and user_id=" . $_SESSION['user_id']);
			}
			if ((isset($POST['enable_depreciation'])) && ($POST['enable_depreciation'] == 'yes')) {
				$dbcon->query("update tbl_ledger_depreciation set depreciate_ledger_id=" . $inserid . " where depreciate_ledger_id='0' and user_id=" . $_SESSION['user_id']);
			}
			if ((isset($POST['enable_monthly_budget'])) && ($POST['enable_monthly_budget'] == 'yes')) {
				$dbcon->query("update tbl_ledger_month_budget set budget_ledger_id=" . $inserid . " where budget_ledger_id='0' and user_id=" . $_SESSION['user_id']);
			}
			if ((isset($POST['enable_cheque_deposit'])) && ($POST['enable_cheque_deposit'] == 'yes')) {
				$dbcon->query("update tbl_ledger_cheque_opening set cheque_ledger=" . $inserid . " where cheque_ledger='0' and user_id=" . $_SESSION['user_id']);
			}
			if ((isset($POST['enable_bill_sunfry'])) && ($POST['enable_bill_sunfry'] == 'yes')) {
				$dbcon->query("update tbl_ledger_bill_sundry set sundry_ledger_id=" . $inserid . " where sundry_ledger_id='0' and user_id=" . $_SESSION['user_id']);
			}
			if ((isset($POST['enable_salesman'])) && ($POST['enable_salesman'] == 'yes')) {
				$dbcon->query("update tbl_ledger_salesman set salesman_ledger_id=" . $inserid . " where salesman_ledger_id='0' and user_id=" . $_SESSION['user_id']);
			}
			/*End Code Dhruv*/

			// Add Data to CRM Party master start - dhaval
			//if($POST['ledger_grp']==SUNDRY_CREDITORS || $POST['ledger_grp']==SUNDRY_DEBTORS)

			$gquery = "SELECT * FROM `tbl_group` where g_id=" . $POST['ledger_grp'];
			$gresult = $dbcon->query($gquery);
			$gres = brp_mysqli_fetch_array($gresult);
			if ($POST['ledger_grp'] == SUNDRY_DEBTORS || $gres['g_pid'] == SUNDRY_DEBTORS) {
				$info_crm['cust_name']		= $POST['ledger_name'];
				$info_crm['cust_creator']	= $_SESSION['user_id'];
				$info_crm['cust_code']		= get_customer_code($dbcon); //Generate New Code
				$info_crm['cust_code_series'] = get_customer_code_series($dbcon); //Generate New Code
				$info_crm['cust_gst']		= $POST['gst_no'];
				$info_crm['cust_mobile']	= $POST['cust_mobile'];
				$info_crm['cust_email']		= $POST['cust_email'];
				$info_crm['account_terms']		= $POST['pay_terms'];
				$info_crm['account_credit_limit']		= $POST['credit_limit'];
				$info_crm['account_credit_days']		= $POST['credit_days'];
				$info_crm['ledger_id'] = $inserid;
				$info_crm['cust_assign_user'] = isset($POST['cust_assign_user']) ? trim(check_crm_find_in_set_new($dbcon, $POST['cust_assign_user'], 1), ",") : trim(check_crm_find_in_set_new($dbcon, $_SESSION['user_id'], 1), ",");
				$info_crm['cust_owner']		= isset($POST['cust_assign_user']) ? $POST['cust_assign_user'] : $_SESSION['user_id'];
				$info_crm['cdate']			= date("Y-m-d H:i:s");
				$info_crm['user_id']		= $_SESSION['user_id'];
				$info_crm['company_id']		= $_SESSION['company_id'];

				$inserid_crm = add_record('tbl_customer', $info_crm, $dbcon, $POST['branch_id']);

				$info_ledger['cust_id'] = $inserid_crm;

				update_record('tbl_ledger', $info_ledger, "l_id=" . $inserid, $dbcon, '');

				$info_terms['cust_id'] = $inserid_crm;

				update_record('tbl_customer_term_trn', $info_terms, "customer_terms_trn_status=0  and ledger_id=" . $inserid, $dbcon);

				$sel_con = $dbcon->query("select * from tbl_custmer_consignee where cust_ref_id='$inserid'");

				if (brp_mysqli_num_rows($sel_con) > 0) {
					while ($row_con = brp_mysqli_fetch_array($sel_con)) {
						$consignee_crm['company_name']  = $row_con['company_name'];
						$consignee_crm['cust_name']     = $row_con['cust_name'];
						$consignee_crm['cust_mobile']   = $row_con['cust_mobile'];
						$consignee_crm['cust_email']    = $row_con['cust_email'];
						$consignee_crm['cust_address']  = stripcslashes(str_replace(array("\n", "\r", "\N"), '', $row_con['cust_address'])); //nl2br($POST['con_address']);
						$consignee_crm['countryid']     = $row_con['countryid'];
						$consignee_crm['stateid']       = $row_con['stateid'];
						$consignee_crm['cityid']        = $row_con['cityid'];
						$consignee_crm['gst_no']        = $row_con['gst_no'];
						$consignee_crm['cust_ref_id']   = $inserid_crm;
						$consignee_crm['user_id']       = $_SESSION['user_id'];

						add_record('tbl_party_consignee', $consignee_crm, $dbcon, '');
					}
				}

				$sel_cont = $dbcon->query("select * from tbl_cust_contact_person where cust_contact_person_status=0 and cust_id='$inserid'");

				if (brp_mysqli_num_rows($sel_cont) > 0) {
					while ($row_cont = brp_mysqli_fetch_array($sel_cont)) {
						$contact_per['c_con_fname']  = $row_cont['cust_contact_person_name'];
						$contact_per['c_con_mobile'] = $row_cont['cust_contact_person_no'];
						$contact_per['c_con_phone']  = $row_cont['cust_contact_person_no'];
						$contact_per['c_con_email']  = $row_cont['cust_contact_person_email'];
						$contact_per['c_con_job']  	 = $row_cont['cust_contact_person_designation'];
						$contact_per['cust_id']    	 = $inserid_crm;
						$contact_per['cdate']        = date("Y-m-d H:i:s");
						$contact_per['user_id']		 = $_SESSION['user_id'];
						$contact_per['company_id']	 = $_SESSION['company_id'];
						$contact_per['branch_id'] 	 = $branch_id;

						add_record('tbl_cust_contact', $contact_per, $dbcon, '');
					}
				}

				$cust_addr['c_add_address']		= $POST['m_address'];
				$cust_addr['c_add_country']		= $POST['countryid'];
				$cust_addr['c_add_state']		= $POST['stateid'];
				$cust_addr['c_add_city']		= $POST['cityid'];
				$cust_addr['cust_id']    	 	= $inserid_crm;
				$cust_addr['c_addr_defult']		= 1;
				$cust_addr['user_id']			= $_SESSION['user_id'];
				$cust_addr['company_id']		= $_SESSION['company_id'];
				$cust_addr['branch_id']			= $branch_id;

				add_record('tbl_cust_address', $cust_addr, $dbcon, '');
			}
			// Add Data to CRM Party master end - dhaval

			$row['res'] = "1";
			$row['ledger_add_type'] = $POST['ledger_add_type'];
			$row['direct_ledger_add'] = $POST['direct_ledger_add'];
			$row['l_name'] = $POST['ledger_name'];
			$row['inserid'] = $inserid;
		} else {
			$row['res'] = "0";
		}
	}

	//$row['res'] ="1";

	echo json_encode($row);
} else if (strtolower($POST['mode']) == "edit") {

	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	$info['l_name']			= $POST['ledger_name'];
	$info['l_group']		= $POST['ledger_grp'];

	if (isset($_FILES["emp_profile_img"]["name"]) && !empty($_FILES["emp_profile_img"]["name"])) {
		$file = $_FILES['emp_profile_img']['tmp_name'];
		$sourceProperties = getimagesize($file);
		$fileNewName = time();
		$folderPath = "../../view/upload/emp_profile_image/";
		$ext = pathinfo($_FILES['emp_profile_img']['name'], PATHINFO_EXTENSION);
		$imageType = $sourceProperties[2];
		switch ($imageType) {
			case IMAGETYPE_PNG:
				$imageResourceId = imagecreatefrompng($file);
				$targetLayer = imageResize($imageResourceId, $sourceProperties[0], $sourceProperties[1]);
				imagepng($targetLayer, $folderPath . $fileNewName . "_thump." . $ext);
				break;
			case IMAGETYPE_GIF:
				$imageResourceId = imagecreatefromgif($file);
				$targetLayer = imageResize($imageResourceId, $sourceProperties[0], $sourceProperties[1]);
				imagegif($targetLayer, $folderPath . $fileNewName . "_thump." . $ext);
				break;
			case IMAGETYPE_JPEG:
				$imageResourceId = imagecreatefromjpeg($file);
				$targetLayer = imageResize($imageResourceId, $sourceProperties[0], $sourceProperties[1]);
				imagejpeg($targetLayer, $folderPath . $fileNewName . "_thump." . $ext);
				break;
			default:
				echo "Invalid Image type.";
				exit;
				break;
		}
		$info['emp_profile_img'] = $fileNewName . "_thump." . $ext;
		$empinfo['emp_profile_img'] = $fileNewName . "_thump." . $ext;
	}
	if (isset($_FILES["emp_signature_img"]["name"]) && !empty($_FILES["emp_signature_img"]["name"])) {
		$files = $_FILES['emp_signature_img']['tmp_name'];
		$sourceProperty = getimagesize($files);
		$fileNewNames = time();
		$folderPath = "../../../view/upload/signature/";
		$exts = pathinfo($_FILES['emp_signature_img']['name'], PATHINFO_EXTENSION);
		$imageTypes = $sourceProperty[2];
		switch ($imageTypes) {
			case IMAGETYPE_PNG:
				$imageResourceIds = imagecreatefrompng($files);
				$targetLayer = imageResize($imageResourceIds, $sourceProperty[0], $sourceProperty[1]);
				imagepng($targetLayer, $folderPath . $fileNewNames . "_thump." . $exts);
				break;
			case IMAGETYPE_GIF:
				$imageResourceIds = imagecreatefromgif($files);
				$targetLayer = imageResize($imageResourceIds, $sourceProperty[0], $sourceProperty[1]);
				imagegif($targetLayer, $folderPath . $fileNewNames . "_thump." . $exts);
				break;
			case IMAGETYPE_JPEG:
				$imageResourceIds = imagecreatefromjpeg($files);
				$targetLayer = imageResize($imageResourceIds, $sourceProperty[0], $sourceProperty[1]);
				imagejpeg($targetLayer, $folderPath . $fileNewNames . "_thump." . $exts);
				break;
			default:
				echo "Invalid Image type.";
				exit;
				break;
		}
		$info['emp_signature_img'] = $fileNewNames . "_thump." . $exts;
		if ($POST['form_type'] == 'emp_form') {
			$info1['authorized_signature'] = $fileNewNames . "_thump." . $exts;
		}
	}
	if ($POST['ledger_grp'] == '58') {
		// Update Employee Table Data
		$empinfo['employee_name'] = $POST['ledger_name'];
		$empinfo['country_id']	= $POST['countryid'];
		$empinfo['state_id'] = $POST['stateid'];
		$empinfo['city_id'] = $POST['cityid'];
		$empinfo['cust_pincode'] = (isset($POST['cust_pincode'])) ? $POST['cust_pincode'] : '';
		$empinfo['m_pan'] = (isset($POST['m_pan'])) ? $POST['m_pan'] : '';
		$empinfo['emp_email'] = strtolower($POST['emp_email']);
		if ($POST['emp_password'] != '') {
			$empinfo['emp_password'] = (isset($POST['emp_password'])) ? $POST['emp_password'] : '';
		}
		$empinfo['emp_mobile']	= (isset($POST['emp_mobile'])) ? $POST['emp_mobile'] : '';
		$empinfo['emp_zone_id']	= (isset($POST['emp_zone_id'])) ? $POST['emp_zone_id'] : '';
		//$empinfo['emp_branch_id']	= (isset($POST['branch_id_emp']))?$POST['branch_id_emp']:'';
		$empinfo['emp_user_type']	= (isset($POST['emp_user_type'])) ? $POST['emp_user_type'] : '';
		$empinfo['alloc_state_id']	= (isset($POST['alloc_stateid'])) ? implode(",", $POST['alloc_stateid']) : '';
		$empinfo['alloc_city_id']	= (isset($POST['alloc_cityid'])) ? implode(",", $POST['alloc_cityid']) : '';
		$empinfo['report_to_user_type']	= (isset($POST['report_to_user_type'])) ? $POST['report_to_user_type'] : '';
		$empinfo['report_to_user_id']	= (isset($POST['report_to_user_id'])) ? $POST['report_to_user_id'] : '';
		$empinfo['open_balance'] = (isset($POST['opn_balance'])) ? $POST['opn_balance'] : '0';
		$empinfo['balance_typeid']	= (isset($POST['balance_typeid'])) ? $POST['balance_typeid'] : '';
		$empinfo['emergenecy_contact_name'] = (isset($POST['emergenecy_contact_name'])) ? $POST['emergenecy_contact_name'] : '';
		$empinfo['emergenecy_contact_number'] = (isset($POST['emergenecy_contact_number'])) ? $POST['emergenecy_contact_number'] : '';
		$empinfo['per_day_salary'] = '0';
		$empinfo['status'] = '0';
		$empinfo['shift_time'] = $POST['shift_time'];
		$empinfo['updated_at']	= date("Y-m-d H:i:s");

		$tr = $dbcon->query("SELECT `l_id`,`employee_id` FROM `tbl_ledger` WHERE l_status != 2 and `l_id` !='" . $POST['ledger_id'] . "' and company_id = " . $_SESSION['company_id']);
		$updateRecord = brp_mysqli_fetch_assoc($tr);

		$empupdateid = update_record('hrms_employee', $empinfo, "id=" . $updateRecord['employee_id'], $dbcon, $branch_id);

		$userinfo['user_type'] = $POST['emp_user_type'];
		$userupdateid = update_record('users', $userinfo, "employee_id=" . $POST['ledger_id'], $dbcon, $branch_id);
	}

	$info['ledger_type']	= $POST['ledger_type'];
	$info['ledger_code']	= $POST['ledger_code'];
	$info['common_email_id']	= $_POST['common_email_id'];
	$info['m_name']			= $POST['m_name'];
	$info['m_address']		= $POST['m_address'];
	$info['countryid']		= $POST['countryid'];
	$info['stateid']		= $POST['stateid'];
	$info['cityid']			= $POST['cityid'];
	$info['cust_pincode']	= $POST['cust_pincode'];
	$info['m_pan']			= $POST['m_pan'];
	$info['company_name']	= $POST['company_name'];
	$info['cust_cont_name']	= $POST['cust_cont_name'];
	$info['cust_mobile']	= $POST['cust_mobile'];
	$info['cust_email']		= strtolower($POST['cust_email']);
	$info['cust_website']	= $POST['cust_website'];
	$info['zone_id']		= $POST['zone_id'];
	$info['cust_remark']	= $POST['cust_remark'];
	$info['gst_no']			= $POST['gst_no'];
	$info['iec_no']			= $POST['iec_no'];
	$info['party_type']		= $POST['party_type'];
	$info['cust_gst_reg']	= $POST['cust_gst_reg'];
	$info['party_sez']		= $POST['party_sez'];
	$info['pay_terms']		= $POST['pay_terms'];
	$info['pay_method']		= $POST['pay_method'];
	$info['credit_limit']	= $POST['credit_limit'];
	$info['credit_days']	= $POST['credit_days'];
	$info['bill_type']		= $POST['bill_type'];
	$info['balance_typeid']	= $POST['balance_typeid'];
	$info['acc_type']		= $POST['acc_type'];
	$info['bankid']			= $POST['bankid'];
	$info['branch_name']	= $POST['branch_name'];
	$info['acc_name']		= $POST['acc_name'];
	$info['acc_number']		= $POST['acc_number'];
	$info['acc_chequeno']	= $POST['acc_chequeno'];
	$info['acc_chequeleft']	= $POST['acc_chequeleft'];
	$info['emp_mobile']		= $POST['emp_mobile'];
	$info['emp_email']		= $POST['emp_email'];
	$info['cust_assign_user']	= trim(check_crm_find_in_set_new($dbcon, $POST['cust_assign_user'], 1), ",");
	$info['cust_owner']	= $POST['cust_assign_user'];
	if ($POST['emp_password'] != '') {
		$info['emp_password']	= $POST['emp_password'];
	}

	$info['emp_zone_id']	= $POST['emp_zone_id'];
	$info['emp_user_type']	= $POST['emp_user_type'];
	$info['tax_value']		= $POST['tax_value'];
	$info['usertype_terr']	= implode(",", $POST['usertype_terr']);
	$info['alloc_stateid']	= implode(",", $POST['alloc_stateid']);
	$info['alloc_cityid']	= implode(",", $POST['alloc_cityid']);
	$info['report_to_user_type'] = $POST['report_to_user_type'];
	$info['report_to_user_id']	= $POST['report_to_user_id'];
	$info['print_priority']		= $POST['print_priority'];

	$info['opn_balance']	= $POST['opn_balance'];
	$info['l_form']			= $POST['form_type'];
	$info['shift_time'] 	= $POST['shift_time'];
	$info['cdate']			= date("Y-m-d H:i:s");
	$info['user_id']		= $_SESSION['user_id'];
	$info['company_id']		= $_SESSION['company_id'];
	/* new field Added by Dhruv */
	$info['ledger_alias']	= $POST['alias_name'];
	$info['enable_multi_currency_opening']	= (isset($POST['multi_currency']) && ($_POST['multi_currency'] == 'yes')) ? 1 : 0;
	$info['enable_branch_opening']	= (isset($POST['multi_branch']) && ($_POST['multi_branch'] == 'yes')) ? 1 : 0;
	$info['ledger_opening_balance_type']	= $POST['set_op_balance'];
	$info['enable_cost_center']	= (isset($POST['enable_cost_center']) && ($POST['enable_cost_center'] == 'yes')) ? 1 : 0;
	$info['enable_tds']	= (isset($POST['enable_tds']) && ($POST['enable_tds'] == 'yes')) ? 1 : 0;
	$info['tdstax_cat']	= $POST['tdstax_cat'];
	$info['party_pay_cat']	= $POST['party_pay_cat'];

	$info['enable_tcs']	= (isset($POST['enable_tcs']) && ($POST['enable_tcs'] == 'yes')) ? 1 : 0;
	$info['enable_depreciation']	= (isset($POST['enable_depreciation']) && ($POST['enable_depreciation'] == 'yes')) ? 1 : 0;
	$info['enable_monthly_budget']	= (isset($POST['enable_monthly_budget']) && ($POST['enable_monthly_budget'] == 'yes')) ? 1 : 0;
	$info['ledger_Tax_type']	= $POST['ledger_Tax_type'];
	$info['ledger_gst_applicable']	= (isset($POST['ledger_gst_applicable']) && ($POST['ledger_gst_applicable'] == 'yes')) ? 1 : 0;
	$info['enable_billbybill_opening'] = (isset($POST['enable_billbybill_opening']) && ($POST['enable_billbybill_opening'] == 'yes')) ? 1 : 0;
	$info['ledger_tax_category']	= $POST['ledger_tax_category'];
	$info['ledger_hsn']	= $POST['ledger_hsn'];
	$info['ledger_itc']	= $POST['ledger_itc'];
	$info['ledger_rcm']	= $POST['ledger_rcm'];
	$info['enable_bill_sunfry']	= (isset($POST['enable_bill_sunfry']) && ($POST['enable_bill_sunfry'] == 'yes')) ? 1 : 0;
	$info['enable_sez']	= (isset($POST['enable_sez']) && ($_POST['enable_sez'] == 'yes')) ? 1 : 0;
	$info['enable_cheque_deposit'] = (isset($POST['enable_cheque_deposit']) && ($POST['enable_cheque_deposit'] == 'yes')) ? 1 : 0;
	$info['enable_salesman'] = (isset($POST['enable_salesman']) && ($POST['enable_salesman'] == 'yes')) ? 1 : 0;
	/* End code: Dhruv */

	$tr = $dbcon->query("SELECT `l_id`,`l_name`,`l_status`,`l_group` FROM `tbl_ledger` WHERE l_status!=2 and `l_name` LIKe '" . $POST['ledger_name'] . "' and `l_id` !='" . $POST['ledger_id'] . "' and l_group = " . $POST['ledger_grp'] . " and company_id = " . $_SESSION['company_id']);
	if ($tr->num_rows > 0) {
		$row['res'] = "-1";
	} else {

		//pathik territory_id add 27-01-2022
		$info['territory_id']		= $POST['territory_id'];
		//pathik territory_id add 27-01-2022

		//pathik ip address wise login 02-03-2023 start
		$info['ip_add']		= $_POST['ip_add'];
		//pathik ip address wise login 02-03-2023 start
		//var_dump($info);

		$updateid = update_record('tbl_ledger', $info, "l_id=" . $POST['ledger_id'], $dbcon, $branch_id);

		$custLedgerDetails = get_cust_data_arr($dbcon, $POST['ledger_id']);
		$deltrmid = delete_record('tbl_customer_term_trn', "ledger_id=" . $POST['ledger_id'], $dbcon, $branch_id);
		foreach ($POST['tc_id_dom'] as $key => $name) {
			$infotrm['tc_id']			= $POST['tc_id_dom'][$key];
			$infotrm['tc_for']			= 0;
			$infotrm['tc_priority']		= $POST['tc_priority_dom'][$key];
			$infotrm['tc_details']		= $_POST['tc_details_dom'][$key];
			$infotrm['ledger_id']		= $POST['ledger_id'];
			$infotrm['cust_id']			= $custLedgerDetails['cust_id'];
			$infotrm['cdate']			= date("Y-m-d H:i:s");
			$infotrm['user_id']			= $_SESSION['user_id'];
			$infotrm['company_id']		= $_SESSION['company_id'];
			if (in_array($POST['tc_id_dom'][$key], $POST['disp_term_flag_dom'])) {
				$insertrmid = add_record('tbl_customer_term_trn', $infotrm, $dbcon, $branch_id);
			}
		}

		foreach ($POST['tc_id_exp'] as $key => $name) {
			$infotrm['tc_id']			= $POST['tc_id_exp'][$key];
			$infotrm['tc_for']			= 1;
			$infotrm['tc_priority']		= $POST['tc_priority_exp'][$key];
			$infotrm['tc_details']		= $_POST['tc_details_exp'][$key];
			$infotrm['ledger_id']		= $POST['ledger_id'];
			$infotrm['cust_id']			= $custLedgerDetails['cust_id'];
			$infotrm['cdate']			= date("Y-m-d H:i:s");
			$infotrm['user_id']			= $_SESSION['user_id'];
			$infotrm['company_id']		= $_SESSION['company_id'];
			if (in_array($POST['tc_id_exp'][$key], $POST['disp_term_flag_exp'])) {
				$insertrmid = add_record('tbl_customer_term_trn', $infotrm, $dbcon, $branch_id);
			}
		}

		$info1['user_name'] 	= $POST['ledger_name'];
		if ($POST['emp_password'] && $POST['emp_password'] != '') {
			$info1['user_key']		= md5($_POST['emp_password']);
		}
		$info1['common_email_id']		= $_POST['common_email_id'];
		$info1['user_mail']		= $POST['emp_email'];
		$info1['usertype_terr']	= implode(",", $POST['usertype_terr']);
		$info1['alloc_stateid']	= implode(",", $POST['alloc_stateid']);
		$info1['alloc_cityid']	= implode(",", $POST['alloc_cityid']);
		$info1['report_to_user_type']	= $POST['report_to_user_type'];
		$info1['report_to_user_id']	= $POST['report_to_user_id'];
		//$info1['branch_id']			= $POST['branch_id_emp'];

		//////////////////////////UPDATE USER DATA MAULIK/////////////////////////
		$info1['user_type']		= $POST['emp_user_type']; //Fixed Type Employee
		$info1['user_country']	= $POST['countryid'];
		$info1['user_stat']		= $POST['stateid'];
		$info1['user_city']		= $POST['cityid'];
		$info1['user_phone']	= $POST['emp_mobile'];

		$info1['user_address']	= $POST['m_address'];
		$info1['user_rid']		= $_SESSION['user_id'];
		$info1['company_id']		= $_SESSION['company_id'];
		//$info1['branch_id']		= $POST['branch_id_emp'];
		$info1['payment_status'] 	= 1;

		if ($POST['forgotquestion_id']) {
			$info1['question_id']		= ($_POST['forgotquestion_id'] != '') ? md5($_POST['forgotquestion_id']) : '';
		}
		if ($POST['forgotgive_answer']) {
			$info1['answer']			= ($_POST['forgotgive_answer'] != '') ? md5($_POST['forgotgive_answer']) : '';
		}
		if (isset($_POST['template_id']) && $_POST['template_id'] != '') {
			$temp = $dbcon->query("SELECT * FROM `template_access_permission` WHERE `id` = '" . $_POST['template_id'] . "'");
			$temprecord = brp_mysqli_fetch_assoc($temp);
			$updateinfoper['user_access_permission'] = '';
			$updateinfoper['menu_show_permission'] = '';
			$infopermission['template_access_perm_id'] = $temprecord['id'];
			$infopermission['user_access_permission'] = $temprecord['template_access_permission'];
			$infopermission['menu_show_permission'] = $temprecord['template_menu_show_permission'];
			$updaterecord = update_record('users', $updateinfoper, "user_type!=2 and employee_id=" . $POST['ledger_id'], $dbcon);
			$updateid1 = update_record('users', $infopermission, "user_type!=2 and employee_id=" . $POST['ledger_id'], $dbcon);
		}
		//pathik ip address wise login 02-03-2023 start
		$info1['ip_add'] 	= $_POST['ip_add'];
		//pathik ip address wise login 02-03-2023 end
		update_record('users', $info1, "employee_id=" . $POST['ledger_id'], $dbcon);

		if ($updateid) {
			$general_book_id = get_general_book_id($dbcon, 'tbl_ledger', $POST['ledger_id'], $POST['ledger_id']);
			$ref_date = date('Y-m-d');
			$currency_trn['currency_id']	= $_SESSION['currency_id'];
			$currency_trn['currency_rate']	= 1;
			add_general_book_entry($dbcon, "tbl_ledger", $POST['ledger_id'], $POST['balance_typeid'], $POST['ledger_id'], $POST['opn_balance'], $general_book_id, $ref_date, '', $currency_trn, 'ledger', $POST['ledger_id']);
			//Insert LOG
			$log_entry = common_log_entry($dbcon, "ledger_add", 2, "tbl_ledger", $POST['ledger_id']);

			$row['res'] = "3";
		} else {
			$row['res'] = "0";
		}
	}
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "delete") {
	$ledger_id = $POST['eid'];
	$sTable = array(TABLE_PURCHASE_ORDER_NUMBER => 'PURCHASE MODULE', TABLE_INVOICE => 'INVOICE MODULE', TABLE_GENERAL_BOOK => 'GENERAL BOOK MODULE', TABLE_JOURNAL_TRN => 'JOURNAL TRN MODULE', TABLE_RECEIPT => 'RECEIPT MODULE', TABLE_CONTRA_TRN => 'CONTRA TRN MODULE', TABLE_EXCESS => 'EXCESS MODULE');
	$aColumns = array(array('vender_id'), array('cust_id', 'sales_ledger_id'), array('ledger_id'), array('ledger_id'), array('payment_mode_id', 'cust_id'), array('ledger_id'), array('cust_id'));
	$sWhere = array(array('status=0 and vender_id = "' . $ledger_id . '"'), array('invoice_status=0 and cust_id = "' . $ledger_id . '" OR sales_ledger_id = "' . $ledger_id . '"'), array('genral_book_status=0 and ledger_id = "' . $ledger_id . '"'), array('journal_trn_status=0 and ledger_id = "' . $ledger_id . '"'), array('status=0 and payment_mode_id = "' . $ledger_id . '" OR cust_id = "' . $ledger_id . '"'), array('contra_trn_status=0 and ledger_id = "' . $ledger_id . '"'), array('status=0 and cust_id = "' . $ledger_id . '"'));
	$checkLang = getCheckRelation($dbcon, $sTable, $aColumns, $sWhere);
	if (count($checkLang) > 0) {
		$resp['msg'] = '-1';
		$resp['table'] = $checkLang;
	} else {
		$general_book_id = get_general_book_id($dbcon, 'tbl_ledger', $POST['eid'], $POST['eid']);

		$info1['genral_book_status']	= 2;
		$updateid11 = update_record('tbl_ledger', $info1, "general_book_id=" . $general_book_id, $dbcon);


		$info['l_status']	= 2;
		$updateid = update_record('tbl_ledger', $info, "l_id=" . $POST['eid'], $dbcon);

		//Deactivate Users
		$infusr['active'] = 2;
		$updateusrid = update_record('users', $infusr, "user_type!=2 and employee_id=" . $POST['eid'], $dbcon);

		//Insert LOG
		$log_entry = common_log_entry($dbcon, "ledger_add", 2, "tbl_ledger", $POST['eid']);

		if ($updateid)
			$resp['msg'] = '1';
		else
			$resp['msg'] = '0';
	}

	echo json_encode($resp);
} else if (strtolower($POST['mode']) == "change_status") {
	$l_status = $POST['l_status'];
	$lid = $POST['lid'];

	if ($l_status == 0) {
		$info['l_status'] = 1;
	} else {
		$info['l_status'] = 0;
	}

	$updateid = update_record('tbl_ledger', $info, "l_id=" . $POST['lid'], $dbcon);

	//Deactivate Users
	$infusr['active'] = $info['l_status'];
	$updateusrid = update_record('users', $infusr, "user_type!=2 and employee_id=" . $POST['lid'], $dbcon);

	//Insert LOG
	$log_entry = common_log_entry($dbcon, "ledger_add", 2, "tbl_ledger", $POST['lid']);

	if ($updateid)
		echo "1";
	else
		echo "0";
} else if (strtolower($POST['mode']) == "load_city_all") {
	$alloc_stateid = array_filter($POST['alloc_stateid']);
	$alloc_stateid = implode(",", $alloc_stateid);
	$str = get_city_all($dbcon, "", $alloc_stateid);
	$resp['html_resp'] = $str;
	echo json_encode($resp);
} else if (strtolower($POST['mode']) == "load_report_to_users") {
	$resp['html_resp'] = get_users_typewise($dbcon, "", " and user_type=" . $POST['report_to_user_type']);
	echo json_encode($resp);
} else if (strtolower($POST['mode']) == "get_branch_by_zone") {
	$zid = $POST['zid'];
	$bid = $POST['bid'];
	$sindex = $POST['sindex'];

	echo get_branch_from_zone($dbcon, $zid, $bid, $sindex);
	//echo $zid;
} else if (strtolower($POST['mode']) == "generate_report_ledger") {
	$s_date = explode(' - ', $POST['date']);

	$_SESSION['ledger_start_date'] = $s_date[0];
	$_SESSION['ledger_end_date'] = $s_date[1];

	if (!empty($POST['g_id'])) {
		$con = " and g_id=" . $POST['g_id'];
	} else {
		$con = "";
	}
	$query = "select g.* from tbl_group as g where g.g_status=0 " . $con . " order by g.g_name";
	$qry = $dbcon->query($query);

	$cnt = 1;
	$str = '';
	$totaldebit_amount = 0;
	$totalcradit_amo = 0;
	while ($row = brp_mysqli_fetch_assoc($qry)) {
		$balance = get_group_ledger_amount($dbcon, $row['g_id'], $s_date['1']);
		if ($balance > 0) {
			$cradit_amo = abs($balance);
			$debit_amount = "";
			$totalcradit_amo = $totalcradit_amo + $cradit_amo;
		} else if ($balance < 0) {
			$cradit_amo = "";
			$debit_amount = abs($balance);
			$totaldebit_amount = $totaldebit_amount + $debit_amount;
		} else {
			$cradit_amo = "";
			$debit_amount = "";
		}
		$str .= '<tr>
					
					<th>' . $cnt . '</th>
					<th><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . FINANCE_ROOT . 'ledger_detail/' . $row['g_id'] . '">' . $row['g_name'] . '</a></th>
					<th>' . $cradit_amo . '</th>
					<th>' . $debit_amount . '</th>
				</tr>';

		$cnt++;
	}
	$str .= '<tr>
					
					<th colspan="2" style="text-align:right;"><strong>Total</strong></th>
					<th>' . $totalcradit_amo . '</th>
					<th>' . $totaldebit_amount . '</th>
				</tr>';

	echo $str;
} else if (strtolower($POST['mode']) == "load_ledger") {
	$query = "select l.* from tbl_ledger as l where l_status=0 and l.l_group=" . $POST['group_id'];
	$qry = $dbcon->query($query);
	$str = "";
	$str .= '<option >----Ledger---</option>';
	while ($row = brp_mysqli_fetch_assoc($qry)) {
		$str .= '<option value="' . $row["l_id"] . '" >' . $row["l_name"] . '</option>';
	}

	echo $str;
} else if (strtolower($POST['mode']) == "generate_report_ledger_detail") {
	$l_id = $POST['l_id'];
	$s_date = explode(' - ', $POST['date']);
	$_SESSION['ledger_start_date'] = $s_date[0];
	$_SESSION['ledger_end_date'] = $s_date[1];
	if (!empty($POST['showledger_id'])) {
		$con = " and l_id=" . $POST['showledger_id'];
	} else {
		$con = "";
	}
	$query = "select l.* from tbl_ledger as l where l_status=0 " . $con . " and l.l_group='$l_id'";
	$qry = $dbcon->query($query);

	$cnt = 1;
	$str = '';
	$total_cr = "";
	$total_dr = "";
	while ($row = brp_mysqli_fetch_assoc($qry)) {
		$balance = get_ledger_amount($dbcon, $row['l_id'], $s_date['1']);
		if ($balance < 0) {
			$cradit_amo = abs($balance);
			$debit_amount = "";
			$total_cr = $total_cr + $cradit_amo;
		} else if ($balance > 0) {
			$cradit_amo = "";
			$debit_amount = abs($balance);
			$total_dr = $total_dr + $debit_amount;
		} else {
			$cradit_amo = "";
			$debit_amount = "";
		}
		$str .= '<tr>
					
					<th>' . $cnt . '</th>
					<th><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . FINANCE_ROOT . 'ledger_form/' . $row['l_id'] . '">' . $row['l_name'] . '</a></th>
					<th>' . $cradit_amo . '</th>
					<th>' . $debit_amount . '</th>
				</tr>';

		$cnt++;
	}
	$str .= '<tr>
					<td></td>
					<td>Total</td>
					<td>' . $total_cr . '</td>
					<td>' . $total_dr . '</td>
				</tr>';
	echo $str;
} else if (strtolower($POST['mode']) == "fetch_all_ledger") {
	if (!empty($POST['showledger_id'])) {
		$where = ' and l_id=' . $POST['showledger_id'] . ' ';
	} else {
		$where = '';
	}
	$s_date = explode(' - ', $POST['date']);
	$_SESSION['ledger_start_date'] = $s_date[0];
	$_SESSION['ledger_end_date'] = $s_date[1];
	$appData = array();
	$i = 1;
	$aColumns = array('l.l_id', 'l.l_name');
	$sIndexColumn = "l.l_id";
	$isWhere = array("l.l_status=0 and l.company_id in (0,$_SESSION[company_id])" . $where);
	$sTable = "tbl_ledger as l";
	$isJOIN = array("");
	$hOrder = "l.l_name";
	include($include . 'pagging.php');
	$appData = array();
	$id = 1;
	foreach ($sqlReturn as $row) {
		$balance = get_ledger_amount($dbcon, $row['l_id'], $s_date['1']);
		if ($balance < 0) {
			$cradit_amo = abs($balance);
			$debit_amount = "";
			$total_cr = $total_cr + $cradit_amo;
		} else if ($balance > 0) {
			$cradit_amo = "";
			$debit_amount = abs($balance);
			$total_dr = $total_dr + $debit_amount;
		} else {
			$cradit_amo = "";
			$debit_amount = "";
		}

		$row_data = array();
		$row_data[] = $row['sr'];
		// $row_data[] ='<a  data-original-title="Ledger-Detail" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'ledger_form/'.$row['l_id'].'">'.$row['l_name'].'</a>';

		$row_data[] = '<form method="post" action="' . ROOT . FINANCE_ROOT . 'ledger_form/' . $row['l_id'] . '" class="inline">
								  <input type="hidden" name="report_type" id="report_type" value="all_ledger">
								  <button type="submit" name="submit_param"  value="submit_value" class="link-button">
								    ' . $row['l_name'] . '
								  </button>
								</form>';


		// $row_data[] = '<form id="ledger_form1" action="'.ROOT.FINANCE_ROOT.'ledger_form/'.$row['l_id'].'" method="post">
		// 			    <a href="javascript:;" onclick="document.getElementById('ledger_form1').submit();">'.$row['l_name'].'</a>
		// 			    <input type="hidden" name="mess" value=<%=n%->/>
		// 			</form>';
		$row_data[] = $cradit_amo;
		$row_data[] = $debit_amount;

		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode($output);
} else if (strtolower($POST['mode']) == "ledger_tree") {
	$parentKey = -1;
	//echo $parentKey;
	$sql = "select * from tbl_group order by g_name";
	$rs = $dbcon->query($sql);
	$count = mysqli_num_rows($rs);

	if ($count > 0) {
		$data = members_Tree($dbcon, $parentKey);
	} else {
		$data = ["id" => "0", "name" => "No Members present in list", "text" => "No Members is present in list", "nodes" => []];
	}

	echo json_encode(array_values($data));
	// print_r($data);
	//echo $count;
} else if (strtolower($POST['mode']) == "check_username") {
	$uname = $POST['uname'];

	$sel = $dbcon->query("select emp_email from tbl_ledger where l_status=0 and emp_email='$uname' and company_id IN (0, " . $_SESSION['company_id'] . ")");
	$count = mysqli_num_rows($sel);

	echo $count;
} else if (strtolower($POST['mode']) == "upload_docs") {
	$l_id = $POST['l_id'];
	$docs_id = $POST['docs_id'];

	$rel = $dbcon->query("select ed_id from tbl_employee_document where ed_lid='$l_id' and ed_doc_type='$docs_id'");
	$count = mysqli_num_rows($rel);

	$test = explode('.', $_FILES["file"]["name"]);
	$ext = end($test);
	$name = rand(100, 99999) . '.' . $ext;
	$path = '../../../view/upload/employee_document/';
	$location = $path . $name;
	move_uploaded_file($_FILES["file"]["tmp_name"], $location);

	$info1['ed_lid'] = $l_id;
	$info1['ed_doc_type'] = $docs_id;
	$info1['ed_path'] = $name;
	$info1['cdate'] = date("Y-m-d");
	$info1['user_id']			= $_SESSION['user_id'];
	$info1['company_id']			= $_SESSION['company_id'];

	$table = 'tbl_employee_document';
	$tableid = 'ed_id';

	if ($count > 0) {
		update_record($table, $info1, "ed_lid='$l_id' and ed_doc_type='$docs_id'", $dbcon);
	} else {
		$inserid = add_record($table, $info1, $dbcon);
	}
} else if (strtolower($POST['mode']) == "show_upload_docs") {
	$l_id = $POST['l_id'];

	$q = "SELECT * from tbl_employee_document as ed LEFT JOIN tbl_document_type_mst AS type ON type.document_id=ed.ed_doc_type where ed_lid='$l_id'";

	$str = "";

	$sel = $dbcon->query($q);
	while ($row = brp_mysqli_fetch_assoc($sel)) {
		$str .= "<div class='col-md-3' style='text-align:center;font-size:18px;'>";
		$str .= "<strong >" . $row['document_name'] . "</strong><br>";
		$str .= '<a href="' . ROOT . 'view/upload/employee_document/' . $row['ed_path'] . '" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>&nbsp;&nbsp;&nbsp;&nbsp;<a onClick="delete_docs(' . $row['ed_id'] . ')" class="btn btn-sm btn-danger" ><i class="fa fa-times"></i></a>';
		$str .= "</div>";
	}

	echo $str;
} else if (strtolower($POST['mode']) == "delete_docs") {
	$row = array();
	$del_attch_qry = "select ed_path from tbl_employee_document where ed_id=" . $POST['ed_id'];
	$del_attch_rel = mysqli_fetch_assoc($dbcon->query($del_attch_qry));
	unlink('../../../view/upload/employee_document/' . $del_attch_rel['ed_path']);

	$delete = delete_record('tbl_employee_document', "ed_id=" . $POST['ed_id'], $dbcon);
	if ($delete)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "add_sold_pro_field") {
	$tr = $dbcon->query("SELECT `cust_sold_pro_id` FROM `tbl_cust_sold_pro` WHERE `cust_id` = '$POST[cust_id]' and `product_id` = '$POST[product_id]' and `sold_pro_srl_no` = '$POST[sold_pro_srl_no]' and cust_sold_pro_status=0 and company_id=" . $_SESSION['company_id']);
	if ($tr->num_rows > 0 && !$POST['edit_id']) {
		$row['res'] = '-1';
	} else {
		$info1['cust_id']				= $POST['cust_id'];
		$info1['sold_inv_foc_date']		= date("Y-m-d", strtotime($POST['sold_inv_foc_date']));
		$info1['product_id']			= $POST['product_id'];
		$info1['sold_pro_srl_no']		= $POST['sold_pro_srl_no'];
		$info1['sold_inv_rmrk']			= $_POST['sold_inv_rmrk'];
		$info1['cdate']					= date("Y-m-d H:i:s");
		$info1['user_id']				= $_SESSION['user_id'];
		$info1['company_id']			= $_SESSION['company_id'];
		$table = 'tbl_cust_sold_pro';
		$tableid = 'cust_sold_pro_id';

		if (empty($POST['edit_id'])) {
			$inserid = add_record($table, $info1, $dbcon);
		} else {
			$updateid = update_record($table, $info1, $tableid . "=" . $POST['edit_id'], $dbcon);
		}
		$row['res'] = '1';
	}
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "show_sold_pro") {
	if ($POST['cust_id'] != "") {
		$where = "and imst.cust_id =" . $POST['cust_id'];
	}
	$appData = array();
	$i = 1;
	$aColumns = array('pro.product_name', 'sold_inv_foc_date', 'sold_pro_srl_no', 'sold_inv_rmrk', 'cust_sold_pro_id', 'sold_inv_no', 'sold_inv_date', 'sold_inv_rate', 'model.model_name');
	$sIndexColumn = "cust_sold_pro_id";
	$isWhere = array("cust_sold_pro_status=0 " . $where . " and imst.company_id in(0,$_SESSION[company_id])");
	$sTable = "tbl_cust_sold_pro as imst";
	$isJOIN = array("left join product_mst as pro on pro.product_id=imst.product_id", "left join model_mst as model on model.model_id=imst.model_id");
	$hOrder = "imst.cust_sold_pro_id desc";
	include($include . 'pagging.php');
	$appData = array();
	$id = 1;
	foreach ($sqlReturn as $row) {
		$row_data = array();
		//$row_data[] = $row['sr'];

		$row_data[] = $row['product_name'];
		$row_data[] = date("d-m-Y", strtotime($row['sold_inv_foc_date']));
		$row_data[] = $row['sold_pro_srl_no'];
		$row_data[] = $row['sold_inv_rmrk'];

		$row_data[] = '<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_sold_pro(' . $row['cust_sold_pro_id'] . ');"><i class="fa fa-pencil"></i></button>
				<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_sold_pro(' . $row['cust_sold_pro_id'] . ')"><i class="fa fa-trash-o"></i></button>';
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode($output);
} else if (strtolower($POST['mode']) == "edit_sold_pro") {
	$q = $dbcon->query("SELECT * FROM `tbl_cust_sold_pro` WHERE cust_sold_pro_status=0 and `cust_sold_pro_id` = '$POST[cust_sold_pro_id]'");
	$r = brp_mysqli_fetch_assoc($q);
	$r['model_resp_html'] = get_prowise_model($dbcon, $r['model_id'], $r['product_id']);
	$r['sold_inv_date'] = date("d-m-Y", strtotime($r['sold_inv_date']));
	$r['sold_inv_foc_date'] = date("d-m-Y", strtotime($r['sold_inv_foc_date']));
	echo json_encode($r);
} else if (strtolower($POST['mode']) == "delete_sold_pro") {
	$info['cust_sold_pro_status'] = '2';
	$updateid = update_record('tbl_cust_sold_pro', $info, "cust_sold_pro_id=" . $POST['cust_sold_pro_id'], $dbcon);

	if ($updateid)
		echo "1";
	else
		echo "0";
} else if (strtolower($POST['mode']) == "add_bank_name") {

	$info1['bank_ac'] = $POST['bank_ac'];
	$info1['b_name'] = $POST['bank_name'];
	$info1['ac_name'] = $POST['ac_name'];
	$info1['bank_ifsc'] = $POST['bank_ifsc'];
	$info1['bank_open'] = $POST['bank_open'];
	$info1['b_cust'] = $POST['cust_id'];
	$info1['userid']		= $_SESSION['user_id'];

	$info1['cdate'] = date("Y-m-d");

	$table = 'tbl_customer_bank';
	$tableid = 'b_id';

	if (empty($POST['edit_id'])) {
		$inserid = add_record($table, $info1, $dbcon);
	} else {
		$updateid = update_record($table, $info1, $tableid . "=" . $POST['edit_id'], $dbcon);
	}

	echo "1";
} else if (strtolower($_POST['mode']) == "load_bank_detail") {
   if (strtolower($_POST['form_mode']) == "edit") {
    $query = "SELECT mst.*, b.bank_name
              FROM tbl_customer_bank AS mst
              LEFT JOIN bank_mst AS b ON b.bankid = mst.b_name
              WHERE mst.b_cust = " . (int)$_POST['cust_id'] . "
              ORDER BY mst.b_id DESC";
} else {
    $query = "SELECT mst.*, b.bank_name
              FROM tbl_customer_bank AS mst
              LEFT JOIN bank_mst AS b ON b.bankid = mst.b_name
              WHERE mst.b_cust = '0'
              ORDER BY mst.b_id DESC";
}
$result = $dbcon->query($query);



	echo '<div class="clearfix"></div>
					
					<div class="col-md-12 col-xs-12" style="overflow-x: scroll;">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped" style="overflow-x: scroll;">
						<tr id="field">
							<th>A/c No</th>
							<th width="5%">Bank Name</th>
							<th>A/C Name</th>
							<th>IFSC</th>
							<td>Opening</td>
							<td></td>
						</tr>';
	if (mysqli_num_rows($result) > 0) {
		$i = 1;
		while ($rel = brp_mysqli_fetch_assoc($result)) {
			echo '<tr id="fieldtr' . $id . '" >
						<td style="vertical-align:top;">
							' . $rel['bank_ac'] . '
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							' . $rel['bank_name'] . '
						</td>
						<td style="vertical-align:top;" class="text-right">
							' . $rel['ac_name'] . '
						</td>
						<td style="vertical-align:top;" class="text-right">
							' . $rel['bank_ifsc'] . '
						</td>
						<td style="vertical-align:top;" class="text-right">
							' . $rel['bank_open'] . '
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_bank(' . $rel['b_id'] . ');" id="fieldtrnedit' . $i . '"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_bank(' . $rel['b_id'] . ');" id="fieldtrnremove' . $i . '"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
			$i++;
		}
	} else {
		echo '<tr><td colspan="7" class="text-center">NO DATA FOUND</td></tr>';
	}
	echo '
					</table>			 
				</div>
			</div>';
}
// Start Code : Added other Field By Dhruv
else if (strtolower($POST['mode']) == "fetch_multi_branch_table") {

	$where = '';

	$appData = array();
	$i = 1;
	$aColumns = array('mbt.balance_type_name', 'lbo.branch_opening_id', 'bm.branch_name', 'lbo.branch_opening_balance', 'lbo.branch_entry_type');
	$sIndexColumn = "lbo.branch_opening_id";
	if ($POST['edit_ledger_id'] == 0) {
		$isWhere = array("lbo.isdelete=0 and lbo.branch_ledger_id=0 " . $where);
	} else {
		$isWhere = array("lbo.isdelete=0 and lbo.branch_ledger_id=" . $POST['edit_ledger_id']);
	}

	$sTable = "tbl_ledger_branch_opening as lbo";

	$isJOIN = array("left join branch_mst as bm on lbo.`branch_id`=bm.`branch_id`", "left join mst_balance_type as mbt on mbt.`balance_typeid`=lbo.`branch_entry_type`");

	$hOrder = "lbo.branch_opening_id desc";
	include($include . 'pagging.php');
	$appData = array();
	$id = 1;

	foreach ($sqlReturn as $row) {

		if ($row['balance_type_name'] == "Debit") {
			$color = "red";
		} else {
			$color = "green";
		}

		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['branch_name'];
		$row_data[] = "<strong style='color:" . $color . "'>" . $row['balance_type_name'] . "</strong>";
		$row_data[] = "<strong style='color:" . $color . "'>" . $row['branch_opening_balance'] . "</strong><br>" . "<input type='hidden' class='multi_branch_" . $row['balance_type_name'] . "' name='multi_branch_text' id='multi_branch_text' value='" . $row['branch_opening_balance'] . "' />";
		//$row_data[] = date('d, M y',strtotime($row['cdate']));

		$edit_btn = '';
		$delete_btn = '';
		if (in_array(ADMINISTRATOR_LEDGER_EDIT, $bulkAccessArray)) {
			$edit_btn = '<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_multi_branch_field(' . $row['branch_opening_id'] . ');"><i class="fa fa-pencil"></i></button>';
		}
		if (in_array(ADMINISTRATOR_LEDGER_DELETE, $bulkAccessArray)) {
			$delete_btn = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_multi_branch_field(' . $row['branch_opening_id'] . ')"><i class="fa fa-trash-o"></i></button>';
		}

		$row_data[] = $edit_btn . ' ' . $delete_btn;
		$appData[] = $row_data;
		$id++;
	}
	//print_r($appData);exit;
	$output['aaData'] = $appData;
	echo json_encode($output);
} else if (strtolower($POST['mode']) == 'multibranch_fieldadd') {

	$info1['branch_id'] = $POST['branch_id'];
	$info1['branch_opening_balance'] = $POST['branch_opening_balance'];
	$info1['branch_entry_type'] = $POST['branch_entry_type'];
	$info1['user_id'] = $_SESSION['user_id'];
	$info1['cdate'] = date("Y-m-d h:i:s");
	$info1['company_id'] = $_SESSION['company_id'];
	if ($POST['ledger_id'] != 0) {
		$info1['branch_ledger_id'] = $POST['ledger_id'];
	}

	$tableid = 'branch_opening_id';

	if (empty($POST['edit_id'])) {
		$inserid = add_record('tbl_ledger_branch_opening', $info1, $dbcon);
	} else {
		$updateid = update_record('tbl_ledger_branch_opening', $info1, $tableid . "=" . $POST['edit_id'], $dbcon);
	}
	if ($inserid) {
		echo "1";
	} else if ($updateid) {
		echo "2";
	} else {
		echo "0";
	}
} else if (strtolower($POST['mode']) == "preedit_multibranch") {
	$q = $dbcon->query("SELECT * FROM `tbl_ledger_branch_opening` where isdelete=0 and branch_opening_id = '$POST[id]'");
	$r = brp_mysqli_fetch_assoc($q);

	echo json_encode($r);
} else if (strtolower($POST['mode']) == "delete_multi_branch") {
	$row = array();
	$info['isdelete'] = 1;

	$updateid = update_record('tbl_ledger_branch_opening', $info, "branch_opening_id=" . $POST['eid'], $dbcon);

	if ($updateid)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "multi_currency_table") {

	$where = '';

	$appData = array();
	$i = 1;
	$aColumns = array('mbt.balance_type_name', 'lco.currency_opening_id', 'cm.currency_name', 'lco.currency_opening_balance', 'lco.currency_entry_type', 'lco.curreency_opening_balance_rs');
	$sIndexColumn = "lco.currency_opening_id";
	if ($POST['edit_ledger_id'] == 0) {
		$isWhere = array("lco.isdelete=0 and lco.currency_ledger_id=0 " . $where);
	} else {
		$isWhere = array("lco.isdelete=0 and lco.currency_ledger_id=" . $POST['edit_ledger_id']);
	}

	$sTable = "tbl_ledger_currency_opening as lco";
	$isJOIN = array("left join currency_mst as cm on lco.`currency_id`=cm.`currencyid`", "left join mst_balance_type as mbt on mbt.`balance_typeid`=lco.`currency_entry_type`");
	$hOrder = "lco.currency_opening_id desc";
	include($include . 'pagging.php');
	$appData = array();
	$id = 1;

	foreach ($sqlReturn as $row) {

		if ($row['balance_type_name'] == "Debit") {
			$color = "red";
		} else {
			$color = "green";
		}

		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['currency_name'];
		$row_data[] = $row['currency_opening_balance'];
		$row_data[] = "<strong style='color:" . $color . "'>" . $row['balance_type_name'] . "</strong>";
		$row_data[] = "<strong style='color:" . $color . "'>" . $row['curreency_opening_balance_rs'] . "</strong><br>" . "<input type='hidden' class='multi_currency_" . $row['balance_type_name'] . "' name='multi_currency_text' id='multi_currency_text' value='" . $row['curreency_opening_balance_rs'] . "' />";
		//$row_data[] = date('d, M y',strtotime($row['cdate']));

		$edit_btn = '';
		$delete_btn = '';
		if (in_array(ADMINISTRATOR_LEDGER_EDIT, $bulkAccessArray)) {
			$edit_btn = '<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_multi_currency_field(' . $row['currency_opening_id'] . ');"><i class="fa fa-pencil"></i></button>';
		}
		if (in_array(ADMINISTRATOR_LEDGER_DELETE, $bulkAccessArray)) {
			$delete_btn = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_multi_currency_field(' . $row['currency_opening_id'] . ')"><i class="fa fa-trash-o"></i></button>';
		}

		$row_data[] = $edit_btn . ' ' . $delete_btn;
		$appData[] = $row_data;
		$id++;
	}
	//print_r($appData);exit;
	$output['aaData'] = $appData;
	echo json_encode($output);
} else if (strtolower($POST['mode']) == 'multicurrency_fieldadd') {

	$info1['currency_id'] = $POST['currencyid'];
	$info1['currency_opening_balance'] = $POST['currency_opening_balance'];
	$info1['currency_entry_type'] = $POST['currency_entry_type'];
	$info1['curreency_opening_balance_rs']   = $POST['curreency_opening_balance_rs'];
	$info1['user_id']	= $_SESSION['user_id'];
	$info1['cdate'] = date("Y-m-d h:i:s");
	$info1['company_id'] = $_SESSION['company_id'];
	if (!empty($POST['ledger_id'])) {
		$info1['currency_ledger_id'] = $POST['ledger_id'];
	}
	$tableid = 'currency_opening_id';


	if (empty($POST['edit_id'])) {
		$inserid = add_record('tbl_ledger_currency_opening', $info1, $dbcon);
	} else {
		$updateid = update_record('tbl_ledger_currency_opening', $info1, $tableid . "=" . $POST['edit_id'], $dbcon);
	}
	if ($inserid) {
		echo "1";
	} else if ($updateid) {
		echo "2";
	} else {
		echo "0";
	}
} else if (strtolower($POST['mode']) == "preedit_multicurrency") {
	$q = $dbcon->query("SELECT * FROM `tbl_ledger_currency_opening` where isdelete=0 and currency_opening_id = '$POST[id]'");
	$r = brp_mysqli_fetch_assoc($q);

	echo json_encode($r);
} else if (strtolower($POST['mode']) == "delete_multi_currency") {
	$row = array();
	$info['isdelete'] = 1;

	$updateid = update_record('tbl_ledger_currency_opening', $info, "currency_opening_id=" . $POST['eid'], $dbcon);

	if ($updateid)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "get_op_balance") {
	if ($POST['op_type'] == 1) {
		echo !empty(total_multicurrency($dbcon)) ? total_multicurrency($dbcon) : '0';
	} else if ($POST['op_type'] == 2) {
		echo !empty(total_multibranch($dbcon)) ? total_multibranch($dbcon) : '0';
	} else {
		echo '0';
	}
} else if (strtolower($POST['mode']) == "depreciation_fieldadd") {
	//echo '<pre>';print_r($_POST);exit;
	$info1['depreciate_ledger_id'] = $POST['ledger_id'];
	$info1['depreciate_annual_rate'] = $POST['depreciate_annual_rate'];
	$info1['depreciate_half_rate'] = $POST['depreciate_half_rate'];
	$info1['depreciate_rate_wdv'] = $POST['depreciate_rate_wdv'];
	$info1['depreciate_opening']   = $POST['depreciate_opening'];
	$info1['user_id']		= $_SESSION['user_id'];
	$info1['cdate'] = date("Y-m-d h:i:s");
	$info1['company_id'] = $_SESSION['company_id'];

	$tableid = 'depreciate_id';


	if (empty($POST['edit_id'])) {
		$inserid = add_record('tbl_ledger_depreciation', $info1, $dbcon);
	} else {
		$updateid = update_record('tbl_ledger_depreciation', $info1, $tableid . "=" . $POST['edit_id'], $dbcon);
	}
	if ($inserid) {
		echo "1";
	} else if ($updateid) {
		echo "2";
	} else {
		echo "0";
	}
} else if (strtolower($POST['mode']) == "monthly_budget_popup") {

	$ledger_id = $POST['ledger_id'];

	$qry = "SELECT mb.budget_id,mb.annual_budget,mbd.budget_month,mbd.budget_month_amount,mbd.budget_detail_id FROM `tbl_ledger_month_budget` as mb left join tbl_ledger_month_budget_details as mbd on mb.`budget_id`= mbd.`budget_id` where mb.isdelete=0 and mb.budget_ledger_id=" . $ledger_id . "";
	$result = $dbcon->query($qry);
	$budget = brp_mysqli_fetch_all($result);

	$month4 = (($budget[0]['budget_month']) && ($budget[0]['budget_month'] == 04)) ? $budget[0]['budget_month_amount'] : '0';
	$month5 = (($budget[1]['budget_month']) && ($budget[1]['budget_month'] == 05)) ? $budget[1]['budget_month_amount'] : '0';
	$month6 = (($budget[2]['budget_month']) && ($budget[2]['budget_month'] == 06)) ? $budget[2]['budget_month_amount'] : '0';
	$month7 = (($budget[3]['budget_month']) && ($budget[3]['budget_month'] == 07)) ? $budget[3]['budget_month_amount'] : '0';
	$month8 = (($budget[4]['budget_month']) && ($budget[4]['budget_month'] == '08')) ? $budget[4]['budget_month_amount'] : '0';
	$month9 = (($budget[5]['budget_month']) && ($budget[5]['budget_month'] == '09')) ? $budget[5]['budget_month_amount'] : '0';
	$month10 = (($budget[6]['budget_month']) && ($budget[6]['budget_month'] == 10)) ? $budget[6]['budget_month_amount'] : '0';
	$month11 = (($budget[7]['budget_month']) && ($budget[7]['budget_month'] == 11)) ? $budget[7]['budget_month_amount'] : '0';
	$month12 = (($budget[8]['budget_month']) && ($budget[8]['budget_month'] == 12)) ? $budget[8]['budget_month_amount'] : '0';
	$month1 = (($budget[9]['budget_month']) && ($budget[9]['budget_month'] == 01)) ? $budget[9]['budget_month_amount'] : '0';
	$month2 = (($budget[10]['budget_month']) && ($budget[10]['budget_month'] == 02)) ? $budget[10]['budget_month_amount'] : '0';
	$month3 = (($budget[11]['budget_month']) && ($budget[11]['budget_month'] == 03)) ? $budget[11]['budget_month_amount'] : '0';
	$buttonVal = !empty($budget[0]['budget_id']) ? 'Update' : 'Add';
	$budgetid = !empty($budget[0]['budget_id']) ? $budget[0]['budget_id'] : '';
	//echo '<pre>';print_r($month8);exit;

	$html = '<div class="form-group"><div class="col-md-12">
						<div class="col-md-4">							
						</div>
						<div class="col-md-4">	
							<div class="form-group">
								  <label class="control-label" style="padding-left: 63px;">Annual Budget</label>
								  <div class="">
										<input type="text"  class="form-control" id="annual_budget" name="annual_budget" onkeyup="changeMonthlyBudget()" placeholder="" min="0" max="" value="' . $budget[0]['annual_budget'] . '"  onkeypress="return isNumberKey(event)"  onchange="validateFloatKeyPress(this);" />
								  </div>
							</div>						
						</div>
						<div class="col-md-4">							
						</div>
					</div>
					<hr>
					<div class="col-md-12" style="text-align: center; text-decoration: underline; margin: 20px; font-size: 25px;">
						Monthly Budgets
					</div>
					<div class="col-md-12" style="margin: 5px;">
						<div class="col-md-6">	
							<div class="form-group">
								  <label class="col-md-4 control-label">April</label>
								  <div class="col-md-8 col-xs-11">
										<input type="text"  class="form-control monthlyDivide" id="april" onkeyup="changeAnnualBudget()" name="month[04]" placeholder="" min="0" max="" value="' . $month4 . '" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
								  </div>
							</div>						
						</div>
						<div class="col-md-6">	
							<div class="form-group">
								  <label class="col-md-4 control-label">May</label>
								  <div class="col-md-8 col-xs-11">
										<input type="text"  class="form-control monthlyDivide" id="2" onkeyup="changeAnnualBudget()" name="month[05]" placeholder="" min="0" max="" value="' . $month5 . '" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
								  </div>
							</div>						
						</div>
					</div>
					<div class="col-md-12" style="margin: 5px;">
						<div class="col-md-6">	
							<div class="form-group">
								  <label class="col-md-4 control-label">June</label>
								  <div class="col-md-8 col-xs-11">
										<input type="text"  class="form-control monthlyDivide" id="3" onkeyup="changeAnnualBudget()" name="month[06]" placeholder="" min="0" max="" value="' . $month6 . '" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
								  </div>
							</div>						
						</div>
						<div class="col-md-6">	
							<div class="form-group">
								  <label class="col-md-4 control-label">July</label>
								  <div class="col-md-8 col-xs-11">
										<input type="text"  class="form-control monthlyDivide" id="4" onkeyup="changeAnnualBudget()" name="month[07]" placeholder="" min="0" max="" value="' . $month7 . '" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
								  </div>
							</div>						
						</div>
					</div>
					<div class="col-md-12" style="margin: 5px;">
						<div class="col-md-6">	
							<div class="form-group">
								  <label class="col-md-4 control-label">August</label>
								  <div class="col-md-8 col-xs-11">
										<input type="text"  class="form-control monthlyDivide" id="" onkeyup="changeAnnualBudget()" name="month[08]" placeholder="" min="0" max="" value="' . $month8 . '" onchange="validateFloatKeyPress(this);" />
								  </div>
							</div>						
						</div>
						<div class="col-md-6">	
							<div class="form-group">
								  <label class="col-md-4 control-label">September</label>
								  <div class="col-md-8 col-xs-11">
										<input type="text"  class="form-control monthlyDivide" id="" onkeyup="changeAnnualBudget()" name="month[09]" placeholder="" min="0" max="" value="' . $month9 . '" onchange="validateFloatKeyPress(this);" />
								  </div>
							</div>						
						</div>
					</div>
					<div class="col-md-12" style="margin: 5px;">
						<div class="col-md-6">	
							<div class="form-group">
								  <label class="col-md-4 control-label">October</label>
								  <div class="col-md-8 col-xs-11">
										<input type="text"  class="form-control monthlyDivide" id="" onkeyup="changeAnnualBudget()" name="month[10]" placeholder="" min="0" max="" value="' . $month10 . '" onchange="validateFloatKeyPress(this);" />
								  </div>
							</div>						
						</div>
						<div class="col-md-6">	
							<div class="form-group">
								  <label class="col-md-4 control-label">November</label>
								  <div class="col-md-8 col-xs-11">
										<input type="text"  class="form-control monthlyDivide" id="" onkeyup="changeAnnualBudget()" name="month[11]" placeholder="" min="0" max="" value="' . $month11 . '" onchange="validateFloatKeyPress(this);" />
								  </div>
							</div>						
						</div>
					</div>
					<div class="col-md-12" style="margin: 5px;">
						<div class="col-md-6">	
							<div class="form-group">
								  <label class="col-md-4 control-label">December</label>
								  <div class="col-md-8 col-xs-11">
										<input type="text"  class="form-control monthlyDivide" id="" onkeyup="changeAnnualBudget()" name="month[12]" placeholder="" min="0" max="" value="' . $month12 . '" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
								  </div>
							</div>						
						</div>
						<div class="col-md-6">	
							<div class="form-group">
								  <label class="col-md-4 control-label">January</label>
								  <div class="col-md-8 col-xs-11">
										<input type="text"  class="form-control monthlyDivide" id="" onkeyup="changeAnnualBudget()" name="month[01]" placeholder="" min="0" max="" value="' . $month1 . '" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
								  </div>
							</div>						
						</div>
					</div>
					<div class="col-md-12" style="margin: 5px;">
						<div class="col-md-6">	
							<div class="form-group">
								  <label class="col-md-4 control-label">February</label>
								  <div class="col-md-8 col-xs-11">
										<input type="text"  class="form-control monthlyDivide" id="" onkeyup="changeAnnualBudget()" name="month[02]" placeholder="" min="0" max="" value="' . $month2 . '" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
								  </div>
							</div>						
						</div>
						<div class="col-md-6">	
							<div class="form-group">
								  <label class="col-md-4 control-label">March</label>
								  <div class="col-md-8 col-xs-11">
										<input type="text"  class="form-control monthlyDivide" id="" onkeyup="changeAnnualBudget()" name="month[03]" placeholder="" min="0" max="" value="' . $month3 . '" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
								  </div>
							</div>						
						</div>
					</div></div>
					<div class="col-md-5"></div>
					<div class="col-md-6">
						<button type="submit" class="btn btn-success">' . $buttonVal . '</button> &nbsp; &nbsp;
						<button type="button" class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
					<input type="hidden" name="edit_id" id="edit_id" value="' . $budgetid . '" />';
	echo $html;
} else if (strtolower($POST['mode']) == "add_budget") {
	//echo '<pre>';print_r($_POST);exit;
	$info1['budget_ledger_id'] = $POST['ledger_id'];
	$info1['annual_budget'] = $POST['annual_budget'];
	$info1['user_id']		= $_SESSION['user_id'];
	$info1['cdate'] = date("Y-m-d h:i:s");
	$info1['company_id'] = $_SESSION['company_id'];

	if (empty($POST['edit_id'])) {
		$inserid = add_record('tbl_ledger_month_budget', $info1, $dbcon);
	} else {
		$updateid = update_record('tbl_ledger_month_budget', $info1, 'budget_id=' . $POST['edit_id'], $dbcon);
	}
	//echo $inserid;exit;
	if (!empty($inserid)) {
		foreach ($POST['month'] as $key => $value) {
			//echo "hiiii";exit;
			$info2['budget_id'] = $inserid;
			$info2['budget_month'] = $key;
			$info2['budget_month_amount'] = $value;
			$info2['user_id'] = $_SESSION['user_id'];
			$info2['cdate'] = date("Y-m-d h:i:s");
			$info2['company_id'] = $_SESSION['company_id'];

			$inserid1 = add_record('tbl_ledger_month_budget_details', $info2, $dbcon);
		}
	} else if ($updateid) {
		$delete = delete_record('tbl_ledger_month_budget_details', "budget_id=" . $POST['edit_id'], $dbcon);
		if ($delete) {

			foreach ($POST['month'] as $key1 => $value1) {
				$info3['budget_id'] = $POST['edit_id'];
				$info3['budget_month'] = $key1;
				$info3['budget_month_amount'] = $value1;
				$info3['user_id'] = $_SESSION['user_id'];
				$info3['cdate'] = date("Y-m-d h:i:s");
				$info3['company_id'] = $_SESSION['company_id'];

				$inserid2 = add_record('tbl_ledger_month_budget_details', $info3, $dbcon);
			}
		}
	}

	if ($inserid1) {
		echo "1";
	} else if ($inserid2) {
		echo "2";
	} else {
		echo "0";
	}
} else if (strtolower($POST['mode']) == "bankchaque_addfield") {
	//echo '<pre>';print_r($_POST);exit;
	$info1['cheque_date'] = date("Y-m-d", strtotime($POST['cheque_date']));
	$info1['cheque_voucher_no'] = $POST['cheque_voucher_no'];
	$info1['cheque_account'] = $POST['cheque_account'];
	$info1['cheque_amount']   = $POST['cheque_amount'];
	$info1['cheque_narration'] = $POST['cheque_narration'];
	$info1['cheque_pay_mode'] = $POST['cheque_pay_mode'];
	$info1['cheque_transaction_number']   = $POST['cheque_transaction_number'];
	$info1['cheque_entry_type'] = $POST['cheque_entry_type'];
	$info1['user_id']		= $_SESSION['user_id'];
	$info1['cdate'] = date("Y-m-d h:i:s");
	$info1['company_id'] = $_SESSION['company_id'];
	if ($POST['ledger_id'] != 0) {
		$info1['cheque_ledger'] = $POST['ledger_id'];
	}

	$tableid = 'cheque_id';


	if (empty($POST['edit_id'])) {
		$inserid = add_record('tbl_ledger_cheque_opening', $info1, $dbcon);
	} else {
		$updateid = update_record('tbl_ledger_cheque_opening', $info1, $tableid . "=" . $POST['edit_id'], $dbcon);
	}
	if ($inserid) {
		echo "1";
	} else if ($updateid) {
		echo "2";
	} else {
		echo "0";
	}
} else if (strtolower($POST['mode']) == "fetch_bank_cheque_table") {

	$where = '';

	$appData = array();
	$i = 1;
	$aColumns = array('lco.cheque_id', 'pm.payment_mode', 'l.l_name', 'lco.cheque_date', 'lco.cheque_voucher_no', 'lco.cheque_account', 'lco.cheque_amount', 'lco.cheque_narration', 'lco.cheque_pay_mode', 'lco.cheque_transaction_number', 'lco.cheque_entry_type', 'lco.cheque_status');
	$sIndexColumn = "lco.cheque_id";
	if ($POST['edit_ledger_id'] == 0) {
		$isWhere = array("lco.isdelete=0 and lco.cheque_ledger=0 " . $where);
	} else {
		$isWhere = array("lco.isdelete=0 and lco.cheque_ledger=" . $POST['edit_ledger_id']);
	}

	$sTable = "tbl_ledger_cheque_opening as lco";

	$isJOIN = array("left join tbl_ledger as l on l.l_id=lco.cheque_account ", "left join tbl_payment_mode as pm on pm.paymentmodeid = lco.cheque_pay_mode");

	$hOrder = "lco.cheque_id desc";
	include($include . 'pagging.php');
	$appData = array();
	$id = 1;

	foreach ($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['cheque_date'];
		$row_data[] = $row['cheque_voucher_no'];
		$row_data[] = $row['l_name'];
		$row_data[] = $row['cheque_amount'];
		$row_data[] = $row['payment_mode'];
		$row_data[] = $row['cheque_transaction_number'];
		$row_data[] = $row['cheque_narration'];
		$row_data[] = (isset($row['cheque_entry_type']) && ($row['cheque_entry_type'] == 1)) ? 'Deposit' : 'Issued';
		//$row_data[] = date('d, M y',strtotime($row['cdate']));

		$edit_btn = '';
		$delete_btn = '';
		if (in_array(ADMINISTRATOR_LEDGER_EDIT, $bulkAccessArray)) {
			$edit_btn = '<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_bank_cheque_field(' . $row['cheque_id'] . ');"><i class="fa fa-pencil"></i></button>';
		}
		if (in_array(ADMINISTRATOR_LEDGER_DELETE, $bulkAccessArray)) {
			$delete_btn = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_bank_cheque_field(' . $row['cheque_id'] . ')"><i class="fa fa-trash-o"></i></button>';
		}

		$row_data[] = $edit_btn . ' ' . $delete_btn;
		$appData[] = $row_data;
		$id++;
	}
	//print_r($appData);exit;
	$output['aaData'] = $appData;
	echo json_encode($output);
} else if (strtolower($POST['mode']) == "preedit_bankcheque") {
	$q = $dbcon->query("SELECT * FROM `tbl_ledger_cheque_opening` WHERE cheque_id='$POST[id]'");
	$r = brp_mysqli_fetch_assoc($q);

	//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
	echo json_encode($r);
	//echo $POST['mode'];
} else if (strtolower($POST['mode']) == "delete_bank_cheque") {
	$row = array();
	$info['isdelete'] = 1;

	$updateid = update_record('tbl_ledger_cheque_opening', $info, "cheque_id=" . $POST['eid'], $dbcon);

	if ($updateid)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "get_bankcheque_total") {
	$depoTotal = total_deposite_bankcheque($dbcon, $POST['edit_ledger_id']);
	$issuedTotal = total_issued_bankcheque($dbcon, $POST['edit_ledger_id']);
	echo $depoTotal . '-' . $issuedTotal;
} else if (strtolower($POST['mode']) == "get_multicurrency_total") {
	$multiTotal = total_multicurrency($dbcon, $POST['edit_ledger_id']);
	echo $multiTotal;
} else if (strtolower($POST['mode']) == "get_multibranch_total") {
	$multiTotal = total_multibranch($dbcon, $POST['edit_ledger_id']);
	echo $multiTotal;
} else if (strtolower($POST['mode']) == "get_billbybill_total") {
	$billbybillTotal = total_billbybill($dbcon, $POST['edit_ledger_id']);
	echo $billbybillTotal;
} else if (strtolower($POST['mode']) == 'billbybill_fieldadd') {

	$info1['bill_ref_no'] = $POST['bill_ref_no'];
	$info1['bill_opening_date'] = date("Y-m-d", strtotime($POST['bill_opening_date']));
	$info1['bill_amount'] = $POST['bill_amount'];
	$info1['bill_entry_type']   = $POST['bill_entry_type'];
	$info1['bill_due_date'] = date("Y-m-d", strtotime($POST['bill_due_date']));
	$info1['user_id']	= $_SESSION['user_id'];
	$info1['cdate'] =  date("Y-m-d h:i:s");
	$info1['company_id'] = $_SESSION['company_id'];
	if (!empty($POST['ledger_id'])) {
		$info1['bill_ledger_id'] = $POST['ledger_id'];
	}
	$tableid = 'bill_opening_id';


	if (empty($POST['edit_bill_id'])) {
		$inserid = add_record('tbl_ledger_billbybill_opening', $info1, $dbcon);
	} else {
		$updateid = update_record('tbl_ledger_billbybill_opening', $info1, $tableid . "=" . $POST['edit_bill_id'], $dbcon);
	}
	if ($inserid) {
		echo "1";
	} else if ($updateid) {
		echo "2";
	} else {
		echo "0";
	}
} else if (strtolower($POST['mode']) == "fetch_billbybill_table") {

	$where = '';

	$appData = array();
	$i = 1;
	$aColumns = array('bbo.bill_opening_id', 'mbt.balance_type_name', 'bbo.bill_ref_no', 'bbo.bill_opening_date', 'bbo.bill_amount', 'bbo.bill_due_date', 'bbo.isdelete=0');
	$sIndexColumn = "bbo.bill_opening_id";
	if ($POST['edit_ledger_id'] == 0) {
		$isWhere = array("bbo.isdelete=0 and bbo.bill_ledger_id=0 " . $where);
	} else {
		$isWhere = array("bbo.isdelete=0 and bbo.bill_ledger_id=" . $POST['edit_ledger_id']);
	}

	$sTable = "tbl_ledger_billbybill_opening as bbo";
	$isJOIN = array("left join mst_balance_type as mbt on mbt.`balance_typeid`=bbo.`bill_entry_type`");
	$hOrder = "bbo.bill_opening_id desc";
	include($include . 'pagging.php');
	$appData = array();
	$id = 1;

	foreach ($sqlReturn as $row) {
		$row_data = array();

		if ($row['balance_type_name'] == "Debit") {
			$color = "red";
		} else {
			$color = "green";
		}

		$row_data[] = $row['sr'];
		$row_data[] = $row['bill_ref_no'];
		$row_data[] = $row['bill_opening_date'];
		$row_data[] = "<strong style='color:" . $color . "'>" . $row['bill_amount'] . "</strong><br>" . "<input type='hidden' class='multi_bill_" . $row['balance_type_name'] . "' name='multi_bill_text' id='multi_bill_text' value='" . $row['bill_amount'] . "' />";
		$row_data[] = "<strong style='color:" . $color . "'>" . $row['balance_type_name'] . "</strong>";
		$row_data[] = $row['bill_due_date'];
		//$row_data[] = date('d, M y',strtotime($row['cdate']));

		$edit_btn = '';
		$delete_btn = '';
		if (in_array(ADMINISTRATOR_LEDGER_EDIT, $bulkAccessArray)) {
			$edit_btn = '<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_billbybill_field(' . $row['bill_opening_id'] . ');"><i class="fa fa-pencil"></i></button>';
		}
		if (in_array(ADMINISTRATOR_LEDGER_DELETE, $bulkAccessArray)) {
			$delete_btn = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_billbybill_field(' . $row['bill_opening_id'] . ')"><i class="fa fa-trash-o"></i></button>';
		}

		$row_data[] = $edit_btn . ' ' . $delete_btn;
		$appData[] = $row_data;
		$id++;
	}
	//print_r($appData);exit;
	$output['aaData'] = $appData;
	echo json_encode($output);
} else if (strtolower($POST['mode']) == "preedit_billbybill") {
	$q = $dbcon->query("SELECT * FROM `tbl_ledger_billbybill_opening` where isdelete=0 and bill_opening_id = '$POST[id]'");
	$r = brp_mysqli_fetch_assoc($q);

	echo json_encode($r);
} else if (strtolower($POST['mode']) == "delete_billbybill_field") {

	$deleteid = delete_record('tbl_ledger_billbybill_opening', "bill_opening_id=$POST[eid]", $dbcon);

	if ($deleteid)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "billsundry_fieldadd") {
	$qry = "SELECT hsn_id,hsn_code,sale_gst FROM `mst_hsn_code` where hsn_status=0 and hsn_id=" . $POST['sundry_hsn'];

	$result = brp_mysqli_fetch_assoc($dbcon->query($qry));

	$info1['sundry_ledger_id'] = $POST['ledger_id'];
	$info1['sundry_type'] = $POST['sundry_type'];
	$info1['sundry_nature'] = $POST['sundry_nature'];
	$info1['sundry_default_value'] = $POST['sundry_default_value'];
	$info1['sundry_amount_of']   = $POST['sundry_amount_of'];
	$info1['sundry_calculate_on'] = $POST['sundry_calculate_on'];
	$info1['user_id']		= $_SESSION['user_id'];
	$info1['cdate'] = date("Y-m-d h:i:s");
	$info1['company_id'] = $_SESSION['company_id'];
	$info1['apply_gst']   = $POST['apply_gst'];
	$info1['sundry_hsn']  = $POST['sundry_hsn'];
	$info1['sundry_gst'] = $POST['sundry_gst'];

	$tableid = 'bill_sundry_id';


	if (empty($POST['edit_sundry_id'])) {
		$inserid = add_record('tbl_ledger_bill_sundry', $info1, $dbcon);
	} else {
		$updateid = update_record('tbl_ledger_bill_sundry', $info1, $tableid . "=" . $POST['edit_sundry_id'], $dbcon);
	}
	if ($inserid) {
		$row['msg'] =  "1";
	} else if ($updateid) {
		$row['msg'] = "2";
	} else {
		$row['msg'] =  "0";
	}
	$row['hsn_code']  = $result['hsn_code'];
	echo json_encode($row);
}

//End Code : Done By Dhruv 

else if (strtolower($POST['mode']) == "preedit_bank") {
	$q = $dbcon->query("SELECT * FROM tbl_customer_bank WHERE b_id='$POST[id]'");
	$r = brp_mysqli_fetch_assoc($q);

	//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
	echo json_encode($r);
	//echo $POST['mode'];
} else if (strtolower($POST['mode']) == "delete_data_bank") {

	$deleteid = delete_record('tbl_customer_bank', "b_id=$POST[eid]", $dbcon);

	if ($deleteid)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo json_encode($row);
}

//contact person details

else if (strtolower($POST['mode']) == "add_contact_person") {
	//p($POST);
	//check if already exist contact person
	if (!empty($POST['edit_id'])) {
		$where = "and cust_contact_person_id != " . $POST['edit_id'] . " ";
	} else {
		$where = "";
	}

	$person_qry = "Select cust_contact_person_name,cust_contact_person_email 
                From tbl_cust_contact_person 
                WHERE cust_id = " . $POST['cust_id'] . " and cust_contact_person_status = 0
                    and cust_contact_person_name = '" . $POST['con_name'] . "' 
                    and cust_contact_person_email = '" . $POST['con_email'] . "' " . $where . " ";
	$q = $dbcon->query($person_qry);
	$row = brp_mysqli_fetch_all($q);

	if (!$row) {
		$info1['cust_contact_person_name']			= $POST['con_name'];
		$info1['isd_id']							= $POST['con_isd_id'];
		$info1['cust_contact_person_no']			= $POST['con_mobile'];
		$info1['cust_contact_person_email']			= $POST['con_email'];
		$info1['cust_contact_person_designation']	= $POST['job_title'];
		$info1['cust_id']   						= $POST['cust_id'];
		$info1['user_id']							= $_SESSION['user_id'];
		$info1['cdate'] 							= date("Y-m-d h:i:s");

		$table = 'tbl_cust_contact_person';
		$tableid = 'cust_contact_person_id';


		if (empty($POST['edit_id'])) {
			$inserid = add_record($table, $info1, $dbcon);
		} else {
			$updateid = update_record($table, $info1, $tableid . "=" . $POST['edit_id'], $dbcon);
		}

		echo "1";
	} else {
		echo "2";
	}
} else if (strtolower($POST['mode']) == "add_tran_del") {

	//p($POST);
	//check if already exist contact person
	$person_qry = "Select cust_id,transportation_id 
                            From tbl_cust_tranportation 
                            WHERE cust_id = " . $POST['cust_id'] . " and cust_transportation_status = 0
                                and transportation_id = " . $POST['transport_id'];
	//echo $person_qry;exit;
	$q = $dbcon->query($person_qry);
	$row = brp_mysqli_fetch_all($q);

	if (!$row) {
		$info1['transportation_id'] = $POST['transport_id'];
		$info1['cust_id']   = $POST['cust_id'];
		$info1['user_id']		= $_SESSION['user_id'];
		$info1['cdate'] = date("Y-m-d h:i:s");

		$table = 'tbl_cust_tranportation';
		$tableid = 'cust_transportation_id';


		if (empty($POST['edit_id'])) {
			$inserid = add_record($table, $info1, $dbcon);
		} else {
			$updateid = update_record($table, $info1, $tableid . "=" . $POST['edit_id'], $dbcon);
		}

		echo "1";
	} else {
		echo "2";
	}
} else if (strtolower($POST['mode']) == "load_contact_detail") {

	if (strtolower($POST['form_mode']) == "edit") {
		$query = "select * from tbl_cust_contact_person where cust_id='$_POST[cust_id]' and user_id='$_SESSION[user_id]' order by cust_contact_person_id Desc";
	} else {
		$query = "select * from tbl_cust_contact_person where cust_id='0' and user_id='$_SESSION[user_id]' order by cust_contact_person_id Desc";
	}

	$result = $dbcon->query($query);
	echo '<div class="clearfix"></div>
					<div class="col-md-12 col-xs-12" style="overflow-x: scroll;">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px; overflow-x: scroll;" class="display table table-bordered table-striped">
						<tr id="field">
							<th>Name</th>
							<th width="5%">Mobile</th>
							<th style="text-align: center;">Email</th>
							<th style="text-align: center;">Job Title</th>
							<td></td>
						</tr>';
	if (mysqli_num_rows($result) > 0) {
		$i = 1;
		while ($rel = brp_mysqli_fetch_assoc($result)) {
			$isd_code = '';
			if (!empty($rel['isd_id'])) {
				$isd_data = get_isd_data_mst($dbcon, $rel['isd_id']);
				$isd_code = '+' . $isd_data['phonecode'] . '-';
			}
			echo '<tr id="fieldtr' . $id . '" >
						<td style="vertical-align:top;">
							' . $rel['cust_contact_person_name'] . '
						</td>
						<td style="vertical-align:top;white-space:nowrap" class="text-center hide_act_add">
							' . $isd_code . ' ' . $rel['cust_contact_person_no'] . '
						</td>
						<td style="vertical-align:top; text-align:center;" class="text-right">
							' . $rel['cust_contact_person_email'] . '
						</td>
						<td style="vertical-align:top; text-align:center;" class="text-right">
							' . $rel['cust_contact_person_designation'] . '
						</td>
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_contact_data(' . $rel['cust_contact_person_id'] . ');" id="fieldtrnedit' . $i . '"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_contact_data(' . $rel['cust_contact_person_id'] . ');" id="fieldtrnremove' . $i . '"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
			$i++;
		}
	} else {
		echo '<tr><td colspan="6" class="text-center">NO DATA FOUND</td></tr>';
	}
	echo '
					</table>			 
				</div>
			</div>';
} else if (strtolower($POST['mode']) == "show_tran_data") {

	if (strtolower($POST['form_mode']) == "edit") {
		$query = "select trn.*,tds.transportation_name from tbl_cust_tranportation as trn
					left join transportation_details as tds on tds.id=trn.transportation_id
					where trn.cust_id='$_POST[cust_id]' and trn.user_id='$_SESSION[user_id]' order by trn.cust_transportation_id Desc";
	} else {
		$query = "select trn.*,tds.transportation_name from tbl_cust_tranportation as trn
							left join transportation_details as tds on tds.id=trn.transportation_id
					where trn.cust_id='0' and trn.user_id='$_SESSION[user_id]' order by trn.cust_transportation_id Desc";
	}

	$result = $dbcon->query($query);
	echo '<div class="clearfix"></div>
					<div class="col-md-12 col-xs-12" style="overflow-x: scroll;">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px; overflow-x: scroll;" class="display table table-bordered table-striped">
						<tr id="field">
							<th>Name</th>
							<td></td>
						</tr>';
	if (mysqli_num_rows($result) > 0) {
		$i = 1;
		while ($rel = brp_mysqli_fetch_assoc($result)) {
			echo '<tr id="fieldtr' . $id . '" >
						<td style="vertical-align:top;">
							' . $rel['transportation_name'] . '
						</td>
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_tran_data(' . $rel['cust_transportation_id'] . ');" id="fieldtrnedit' . $i . '"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_tran_data(' . $rel['cust_transportation_id'] . ');" id="fieldtrnremove' . $i . '"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
			$i++;
		}
	} else {
		echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
	}
	echo '
					</table>			 
				</div>
			</div>';
} else if (strtolower($POST['mode']) == "preedit_contact") {
	$q = $dbcon->query("SELECT * FROM tbl_cust_contact_person WHERE cust_contact_person_id='$POST[id]'");
	$r = brp_mysqli_fetch_assoc($q);

	//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
	echo json_encode($r);
	//echo $POST['mode'];
} else if (strtolower($POST['mode']) == "edit_tran_data") {
	$q = $dbcon->query("SELECT * FROM tbl_cust_tranportation WHERE cust_transportation_id='$POST[id]'");
	$r = brp_mysqli_fetch_assoc($q);

	//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
	echo json_encode($r);
	//echo $POST['mode'];
} else if (strtolower($POST['mode']) == "delete_data_contact") {

	$deleteid = delete_record('tbl_cust_contact_person', "cust_contact_person_id=$POST[eid]", $dbcon);

	if ($deleteid)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "delete_tran_data") {

	$deleteid = delete_record('tbl_cust_tranportation', "cust_transportation_id=$POST[eid]", $dbcon);

	if ($deleteid)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "add_salesman") {


	$info1['salesman_parent'] = $POST['salesman_parent'];
	$info1['salesman_commision_mode'] = $POST['salesman_commision_mode'];
	$info1['salesman_commision_percentage']	= $POST['salesman_commision_percentage'];
	$info1['salesman_ledger_id']	= $POST['ledger_id'];
	//$info1['edit_salesman_id']	= $POST['edit_salesman_id'];

	$info1['cdate']					= date("Y-m-d H:i:s");
	$info1['user_id']				= $_SESSION['user_id'];
	$info1['company_id']			= $_SESSION['company_id'];
	$info1['usertype_id']			= $_SESSION['usertype_id'];

	$table = 'tbl_ledger_salesman';
	$tableid = 'salesman_id';
	//echo $POST['edit_salesman_id'];exit();
	if ($POST['edit_salesman_id'] == '0') {

		$inserid = add_record($table, $info1, $dbcon);

		if ($inserid) {
			echo "1";
		} else {
			echo "0";
		}
	} else {
		$updateid = update_record($table, $info1, $tableid . "=" . $POST['edit_salesman_id'], $dbcon);

		if ($updateid) {
			echo "3";
		} else {
			echo "0";
		}
	}
} else if (strtolower($POST['mode']) == "load_salesman_data") {

	$ledger_id = $POST['ledger_id'];

	$sel = $dbcon->query("select * from tbl_ledger_salesman where salesman_ledger_id='$ledger_id'");
	$count = brp_mysqli_num_rows($sel);
	if ($count > 0) {
		$row = brp_mysqli_fetch_assoc($sel);
		$row['count'] = 1;
	} else {
		$row['count'] = 0;
	}
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "load_ledger_no") {
	$row = array();
	$companyConfiguration = getCompanyConfiguration($dbcon);

	$query1 = "select * from  tbl_group where g_id=" . $POST['id'];
	$rows = brp_mysqli_fetch_assoc($dbcon->query($query1));
	$id = $rows['group_start_series'];
	$id = $id + 1;

	if ($rows['group_format'] == '2') {
		$row['ledgerno'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
	} else if ($rows['group_format'] == '1') {
		$row['ledgerno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
	} else if ($rows['group_format'] == '3') {
		$row['ledgerno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
	} else {
		$row['ledgerno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
	}
	$row['challanno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
	$row['code_id'] = $id;
	//Addded by dhruv
	if ($companyConfiguration['ledger_code'] == 1) {

		$query2 = "SELECT ledger_code FROM tbl_ledger WHERE ledger_code='" . $row['ledgerno'] . "' and l_status IN (0,1)";
		$rows2 = brp_mysqli_fetch_assoc($dbcon->query($query2));
		if (!empty($rows2['ledger_code'])) {
			$query3 = "select max(l_code_id) as codeid from  tbl_ledger where l_status IN (0,1) and l_group=" . $POST['id'];
			$rows3 = brp_mysqli_fetch_assoc($dbcon->query($query3));
			$id_code = $rows3['codeid'];
			$id_code = $id_code + 1;

			$query4 = "select * from  tbl_group where g_id=" . $POST['id'];
			$rows4 = brp_mysqli_fetch_assoc($dbcon->query($query4));

			if ($rows4['group_format'] == '2') {
				$rows4['ledgerno'] = str_pad($id_code, 4, "0", STR_PAD_LEFT) . $rows4['format_value'];
			} else if ($rows4['group_format'] == '1') {
				$rows4['ledgerno'] = $rows4['format_value'] . str_pad($id_code, 3, "0", STR_PAD_LEFT);
			} else if ($rows4['group_format'] == '3') {
				$rows4['ledgerno'] = $rows4['format_value'] . str_pad($id_code, 3, "0", STR_PAD_LEFT) . $rows4['end_format_value'];
			} else {
				$rows4['ledgerno'] = str_pad($id_code, 3, "0", STR_PAD_LEFT);
			}

			$row['ledgerno'] = $rows4['ledgerno'];
			$row['code_id'] = $id_code;
		}
	}

	echo json_encode($row);
} else if (strtolower($POST['mode']) == "check_manual_ledger_code") {
	$query2 = "SELECT ledger_code FROM tbl_ledger WHERE ledger_code='" . $POST['code'] . "' and l_status IN (0,1)";
	//echo $query2;exit;
	$rows2 = brp_mysqli_fetch_assoc($dbcon->query($query2));
	if (!empty($rows2['ledger_code'])) {
		$row['error'] = 'Ledger code is already exists, please try another one';
	}
	echo json_encode($row);
} else if (strtolower($POST['mode']) == "delete_ledger_popup_data") {
	$info['isdelete'] = 1;

	//delete multi currency '0' id entry 
	$updatemultiCurr = update_record('tbl_ledger_currency_opening', $info, "currency_ledger_id=0", $dbcon);

	//delete multi branch '0' id entry 
	$updatemultiBranch = update_record('tbl_ledger_branch_opening', $info, "branch_ledger_id=0", $dbcon);

	//delete billbybill openning '0' id entry 
	$updateBillbybill = update_record('tbl_ledger_billbybill_opening', $info, "bill_ledger_id=0", $dbcon);

	//delete bill sundry openning '0' id entry 
	$updateBillSundry = update_record('tbl_ledger_bill_sundry', $info, "sundry_ledger_id=0", $dbcon);

	//delete monthly budget '0' id entry 
	$updateLedgerMonth = update_record('tbl_ledger_month_budget', $info, "budget_ledger_id=0", $dbcon);

	//delete salesman '0' id entry 
	$updateSalesman = update_record('tbl_ledger_salesman', $info, "salesman_ledger_id=0", $dbcon);
} else if (strtolower($POST['mode']) == "check_duplicate_ledger") {
	$ledger_name = $POST['ledger_name'];
	$ledger_id = $POST['ledger_id'];

	if ($ledger_id != '' && $ledger_id != 0) {
		$where = " and  l_id!=" . $ledger_id . "";
	} else {
		$where = '';
	}
	//check old ledger name
	$q = $dbcon->query("select l_name from tbl_ledger where l_id='$ledger_id' and  l.company_id in (0," . $_SESSION['company_id'] . ")");
	$row = brp_mysqli_fetch_assoc($q);
	$old_name = $row['l_name'];

	if ($ledger_id == '' || $ledger_id == 0 || $old_name != $ledger_name) {
		//check in ledger list
		$q = $dbcon->query("select l_name from tbl_ledger where l_name='$ledger_name'  and  l.company_id in (0," . $_SESSION['company_id'] . ")  ");
		$count = brp_mysqli_num_rows($q);
		if ($count != 0) {
			$check = '1';
		}

		if (($ledger_id == '' || $ledger_id == 0) && $check != 1) {
			//check in crm party master 
			$q1 = $dbcon->query("select cust_name from tbl_customer where cust_name='$ledger_name'");
			$count1 = brp_mysqli_num_rows($q1);
			if ($count1 != 0) {
				$check = '2';
			}
		}
	} else {
		$check = '3';
	}

	echo $check;
} else if (strtolower($POST['mode']) == "get_party_by_ledger") {
	$ledger_grp = $POST['ledger_grp'];
	$party_pay_cat = $POST['party_pay_cat'];
	$tds_cat_id = $POST['tds_cat_id'];

	//if($ledger_grp==SUNDRY_CREDITORS || $ledger_grp==SUNDRY_DEBTORS)
	//{
	//echo get_common_category($dbcon, $tds_cat_id,'Payee Category',$party_pay_cat);
	echo get_tds_tax_payee($dbcon, $tds_cat_id, 'Payee Category', $party_pay_cat);
	//}
	//	else
	//{
	//echo get_all_tds_cat($dbcon,$party_pay_cat);
	//}
} else if (strtolower($POST['mode']) == "get_tax_category") {
	$tax_cat = hsn_wise_tax_category($dbcon, $POST['hsn_id']);
	echo $tax_cat;
} else if (strtolower($POST['mode']) == "add_ledger_doc_field") {
	/*var_dump($_POST);
	    var_dump($_FILES);*/
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	$info1['led_doc_name']   		= $POST['led_doc_name'];
	$info1['led_attch_file']		= upload_attch_file($_FILES);
	$info1['user_id']				= $_SESSION['user_id'];
	$info1['company_id']			= $_SESSION['company_id'];

	$table = 'tbl_ledger_attach_doc';
	$tableid = 'led_attach_id';
	if (!empty($POST['l_id'])) {
		$info1['l_id']	= $POST['l_id'];
	} else {
		$info1['led_attach_status']	= 3;
	}

	$inserid = add_record($table, $info1, $dbcon, $branch_id);

	if ($inserid) {
		echo "1";
	} else {
		echo "0";
	}
} else if (strtolower($POST['mode']) == "show_led_attach_data") {
	$chkmode = $POST['modee'];
	$delete_btn_per = in_array(ADMINISTRATOR_LEDGER_DELETE, $bulkAccessArray);
	if ($POST['l_id']) {
		$query = "select mst.* from tbl_ledger_attach_doc as mst 
	        where mst.led_attach_status=0 and mst.l_id=" . $POST['l_id'];
	} else {
		$query = "select mst.* from tbl_ledger_attach_doc as mst 
	        where mst.led_attach_status=3 and mst.ref_type=0 and mst.user_id=" . $_SESSION['user_id'];
	}
	$result = $dbcon->query($query);
	echo '<table class="display table table-bordered table-striped">
	    <tr>
	    <th width="60%" class="text-center">Document Name</th>
	    <th width="30%" class="text-center">Attached Document</th>';
	if ($chkmode != 'VIEW' && $delete_btn_per) {
		echo '<th width="10%" class="text-center">Action</th>';
	}
	echo '</tr>
	    <tbody>';
	if (mysqli_num_rows($result) > 0) {
		$i = 1;
		while ($rel = mysqli_fetch_assoc($result)) {
			echo '<tr> 
	            <td style="vertical-align:top;">
	            <strong>' . $rel['led_doc_name'] . '</strong>
	            </td>
	            <td style="vertical-align:top;" class="text-center">
	            <a href="' . ROOT . LED_ATTACH_VWING . $rel['led_attch_file'] . '" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>
	            </td>';
			if ($chkmode != 'VIEW'  && $delete_btn_per) {
				// if($delete_btn_per){
				echo '<td style="vertical-align:top"> 
	                    <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_led_attach_data(' . $rel['led_attach_id'] . ')">X</button>
	                    </td>';
				// }
			}
			echo '</tr>';
			$i++;
		}
	} else {
		echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
	}

	echo '</tbody>
	    </table>';
} else if (strtolower($POST['mode']) == "delete_led_attach_data") {
	$row = array();
	$del_attch_qry = "select led_attch_file from tbl_ledger_attach_doc where led_attach_id=" . $POST['led_attach_id'];
	$del_attch_rel = mysqli_fetch_assoc($dbcon->query($del_attch_qry));

	/*var_dump('..//'.LED_ATTACH_UPING.$del_attch_rel['led_attch_file']);*/
	unlink('..//' . LED_ATTACH_UPING . $del_attch_rel['led_attch_file']);

	$info['led_attach_status'] = 2;
	$updateid = update_record('tbl_ledger_attach_doc', $info, "led_attach_id=" . $POST['led_attach_id'], $dbcon);

	if ($updateid)
		$row['res'] = "1";
	else
		$row['res'] = "0";
	echo json_encode($row);
}
//Maulik Start
else if (strtolower($POST['mode']) == "load_typeswise_terms_dom") {
	$quot_type = $POST['quot_type'];
	$ledger_id = $POST['ledger_id'];
	$str = '';
	$str .= '<table class="display table table-bordered table-striped">
		<thead>
		<tr>
		<th width="5%" class="text-center">
		<input type="checkbox" class="check_all_terms_dom" style="height: 20px;width: 20px;" id="check_all_terms_dom" name="check_all_terms_dom" onClick="terms_check_all_dom(this);">
		</th>
		<th width="25%" class="text-center">Term Name</th>
		<th width="5%" class="text-center">Priority</th>
		<th width="65%" class="text-center">Term And Condition</th>				  
		</tr>
		</thead>
		<tbody>';

	//Get All Terms
	$terms_qry = "select * from tbl_terms_condition where tc_status=0 and tc_category=1 and find_in_set(" . $quot_type . ",tc_for) order by tc_priority";
	$terms_qry_rs = $dbcon->query($terms_qry);
	$t = 1;
	while ($terms_rel = mysqli_fetch_assoc($terms_qry_rs)) {
		$tc_priority = $terms_rel['tc_priority'];
		$tc_details = $terms_rel['tc_details'];
		if ($ledger_id) {
			$quot_term_qry = "select * from tbl_customer_term_trn where customer_terms_trn_status=0 and ledger_id=" . $ledger_id . " and tc_for=" . $quot_type . " and tc_id=" . $terms_rel['tc_id'] . "";
			$quot_term_rel = mysqli_fetch_assoc($dbcon->query($quot_term_qry));
			if ($quot_term_rel['tc_priority']) {
				$tc_priority = $quot_term_rel['tc_priority'];
			}
			if ($quot_term_rel['tc_details']) {
				$tc_details = $quot_term_rel['tc_details'];
			}
		}

		$str .= '<tr>
			<td width="5%" class="text-center">
			<input type="checkbox" class="terms_checkbox_dom" style="height: 20px;width: 20px;" id="disp_term_flag_dom' . $t . '" name="disp_term_flag_dom[]" value="' . $terms_rel['tc_id'] . '" ' . (($terms_rel['tc_id'] == $quot_term_rel['tc_id']) ? 'checked' : '') . '>
			<input type="hidden" id="tc_id_dom' . $t . '" name="tc_id_dom[]" value="' . $terms_rel['tc_id'] . '">
			</td>
			<td>' . $terms_rel['tc_name'] . '</td>
			<td>
			<input type="number" class="form-control" min="0" id="tc_priority_dom' . $t . '" name="tc_priority_dom[]" value="' . $tc_priority . '">
			</td>';
		if ($terms_rel['tc_allow']) {
			$str .= '<td>
				<textarea class="form-control" id="tc_details_dom' . $t . '" name="tc_details_dom[]">' . $tc_details . '</textarea>
				</td>';
		} else {
			$str .= '<td>
				<textarea class="form-control" id="tc_details_dom' . $t . '" name="tc_details_dom[]" readonly>' . $tc_details . '</textarea>
				</td>';
		}
		$str .= '</tr>';

		$t++;
	}

	$str .= '</tbody> 
		</table>';

	$resp['resp_html'] = $str;
	echo json_encode($resp);
} else if (strtolower($POST['mode']) == "load_typeswise_terms_exp") {
	$quot_type = $POST['quot_type'];
	$ledger_id = $POST['ledger_id'];
	$str = '';
	$str .= '<table class="display table table-bordered table-striped">
		<thead>
		<tr>
		<th width="5%" class="text-center">
		<input type="checkbox" class="check_all_terms_exp" style="height: 20px;width: 20px;" id="check_all_terms_exp" name="check_all_terms_exp" onClick="terms_check_all_exp(this);">
		</th>
		<th width="25%" class="text-center">Term Name</th>
		<th width="5%" class="text-center">Priority</th>
		<th width="65%" class="text-center">Term And Condition</th>				  
		</tr>
		</thead>
		<tbody>';

	//Get All Terms
	$terms_qry = "select * from tbl_terms_condition where tc_status=0 and tc_category=1 and find_in_set(" . $quot_type . ",tc_for) order by tc_priority";
	$terms_qry_rs = $dbcon->query($terms_qry);
	$t = 1;
	while ($terms_rel = mysqli_fetch_assoc($terms_qry_rs)) {
		$tc_priority = $terms_rel['tc_priority'];
		$tc_details = $terms_rel['tc_details'];
		if ($ledger_id) {
			$quot_term_qry = "select * from tbl_customer_term_trn where customer_terms_trn_status=0 and ledger_id=" . $ledger_id . " and tc_for=" . $quot_type . " and tc_id=" . $terms_rel['tc_id'] . "";
			$quot_term_rel = mysqli_fetch_assoc($dbcon->query($quot_term_qry));
			if ($quot_term_rel['tc_priority']) {
				$tc_priority = $quot_term_rel['tc_priority'];
			}
			if ($quot_term_rel['tc_details']) {
				$tc_details = $quot_term_rel['tc_details'];
			}
		}

		$str .= '<tr>
			<td width="5%" class="text-center">
			<input type="checkbox" class="terms_checkbox_exp" style="height: 20px;width: 20px;" id="disp_term_flag_exp' . $t . '" name="disp_term_flag_exp[]" value="' . $terms_rel['tc_id'] . '" ' . (($terms_rel['tc_id'] == $quot_term_rel['tc_id']) ? 'checked' : '') . '>
			<input type="hidden" id="tc_id_exp' . $t . '" name="tc_id_exp[]" value="' . $terms_rel['tc_id'] . '">
			</td>
			<td>' . $terms_rel['tc_name'] . '</td>
			<td>
			<input type="number" class="form-control" min="0" id="tc_priority_exp' . $t . '" name="tc_priority_exp[]" value="' . $tc_priority . '">
			</td>';
		if ($terms_rel['tc_allow']) {
			$str .= '<td>
				<textarea class="form-control" id="tc_details_exp' . $t . '" name="tc_details_exp[]">' . $tc_details . '</textarea>
				</td>';
		} else {
			$str .= '<td>
				<textarea class="form-control" id="tc_details_exp' . $t . '" name="tc_details_exp[]" readonly>' . $tc_details . '</textarea>
				</td>';
		}
		$str .= '</tr>';

		$t++;
	}

	$str .= '</tbody> 
		</table>';

	$resp['resp_html'] = $str;
	echo json_encode($resp);
}
//Maulik End

function imageResize($imageResourceId, $width, $height)
{
	$targetWidth = 100;
	$targetHeight = 100;
	$targetLayer = imagecreatetruecolor($targetWidth, $targetHeight);
	imagecopyresampled($targetLayer, $imageResourceId, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
	return $targetLayer;
}

function upload_attch_file($FILES)
{
	$rand = rand(0, 99999999);
	if (!empty($FILES['led_attch_file']['tmp_name'])) {
		$temp = explode(".", $FILES["led_attch_file"]["name"]);
		$extension = strtolower(end($temp));
		$File = "led_attch_" . $rand . "." . $extension;
		$tmp_name = $FILES["led_attch_file"]["tmp_name"];
		move_uploaded_file($tmp_name, '..//' . LED_ATTACH_UPING . $File);

		return  $File;
	}
}
