<?php
session_start();
$AJAX = true;

	include('../../include/urlfileinner.php');
// error_reporting(E_ALL);
/*var_dump($_POST);*/
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{
	//if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		if ($_POST != NULL) {
			$POST = bulk_filter($dbcon, $_POST);
		} else {
			$POST = bulk_filter($dbcon, $_GET);
		}

		if (strtolower($POST['mode']) == "fetch") {
			$bulkAccessArray = canCheckPermissionAccess($dbcon, [
				PO_LIST_VIEW,
				PO_LIST_ADD,
				PO_LIST_READ,
				PO_LIST_UPDATE,
				PO_LIST_DELETE,
				PO_LIST_APPROVE,
				DASHBOARD_PO_REQUEST_LIST_APPROVE,
				PURCHASE_ORDER_FINANCE_APPROVAL,
				PURCHASE_ORDER_APPROVAL
			]);
			$s_date = explode(' - ', $POST['date']);
			$_SESSION['start'] = $s_date[0];
			$_SESSION['end'] = $s_date[1];

			$where = '';
			$where .= " and po_type_status=1";

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			// $where_db = check_branch('po', $branch_id);
			if (!empty($POST['branch_id']) && $POST['branch_id'] > 0) {
				$where_db = " and po.branch_id = " . $POST['branch_id'];
			} else {
				$where_db = "";
			}
			$where .= " $where_db ";

			$where_company = check_company('po');

			$where .= " $where_company";

			$vender_id = "";
			$filt_status = $POST['filt_status'];

			if (!empty($POST['vender_id']) && $POST['vender_id'] > 0) {
				$where .= " and po.vender_id = " . $POST['vender_id'];
			}

			if ($filt_status != "" && $filt_status > 0) {

				if ($filt_status == 1) {
					$where .= " and po_approval_status = 0";
				}

				if ($filt_status == 2) {
					$where .= " and po_approval_status = 1";
				}

				if ($filt_status == 3) {
					$where .= " and po_approval_status = 1 and po_aproove_finance = 0";
				}

				if ($filt_status == 4) {
					$where .= " and po_approval_status = 1 and revise_status = 1";
				}
				if ($filt_status == 5) {
					$where .= " and aproove_status = 0";
				}
				if ($filt_status == 6) {
					$where .= " and short_close_status=0 and aproove_status=1";
				}
			}

			$getapprovalsetting = get_userwise_approval_setting($dbcon, 4, $_SESSION['user_id']);
			$getapprovalsettinges = get_userwise_approval_setting($dbcon, 5, $_SESSION['user_id']);
			$company_special = getspecialConfiguration($dbcon);
			$getspecialConfiguration = getspecialConfiguration($dbcon);
			//$where.=" $where_user";
			/*switch($POST['po_type_status']){
			case "1":
			$where.="  and po_type_status=1";
			break;

			case "2":
			$where.="  and po_type_status=2";
			break;

			case "3":
			$where.="  and po_type_status=3";
			break;

			default:
			$where.="";
		}*/
			//echo $_SESSION['page'];
			/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$appr_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'aprv',$dbcon);*/


			$where .= "  and purchaseorder_date >= '" . date('Y-m-d', strtotime($s_date[0])) . "' AND purchaseorder_date <= '" . date('Y-m-d', strtotime($s_date[1])) . "'";
			$appData = array();
			$i = 1;
			$aColumns = array('po.purchaseorder_id', 'lpsc.short_close_status', 'lpsc.aproove_status', 'purchaseorder_no', 'l.l_name', 'city.city_name', 'bms.branch_name', 'purchaseorder_date', 'g_total', 'paid_amount', 'status', 'purchase_status', 'quot_type', 'po.cdate', 'po.userid', 'po.po_type_status', 'po.po_req_status', 'po_approval_status', 'po.branch_id', 'po.revise_status', 'us.user_name');
			$sIndexColumn = "po.purchaseorder_id";
			$isWhere = array("status = 0" . $where);
			$sTable = "tbl_purchaseorder as po";
			$isJOIN = array(
				'left join tbl_ledger as l on po.vender_id=l.l_id',
				'left join  tbl_log_po_short_close as lpsc on lpsc.po_id=po.purchaseorder_id',
				'left join  city_mst city on l.cityid=city.cityid',
				'left join branch_mst as bms on bms.branch_id=po.branch_id',
				'left join users as us on us.user_id=po.userid'
			);
			$hOrder = "po.purchaseorder_id desc";
			$having_clause = '';
			include($include . 'pagging.php');
			$appData = array();

			$id = 1;
			foreach ($sqlReturn as $row) {
				$row_data = array();

				$query = "select sum(g_total) as total_purchase,sum((select sum(product_amount) from tbl_purchaseordertrn as ptr where ptr.purchaseorder_id = po.purchaseorder_id and ptr.purchaseordertrn_status=0 )) as taxable_amt from tbl_purchaseorder as po
				
				where  po.status=0 and po.purchaseorder_date >= '" . date('Y-m-d', strtotime($s_date[0])) . "' AND po.purchaseorder_date <= '" . date('Y-m-d', strtotime($s_date[1])) . "'";

				$result = $dbcon->query($query);
				$res = brp_mysqli_fetch_array($result);

				if (in_array(PO_LIST_UPDATE, $bulkAccessArray) && $row['po_approval_status'] == '0') {
					$row_data[] = '<a class="" data-original-title="Edit ' . $row['purchaseorder_no'] . '" data-toggle="tooltip" data-placement="top" href="' . ROOT . PURCHASE_ROOT . 'poedit/' . $row['purchaseorder_id'] . '">' . $row['purchaseorder_no'] . '</a>';
					$row_data[] = '<a class="" data-original-title="Edit ' . $row['purchaseorder_no'] . '" data-toggle="tooltip" data-placement="top" href="' . ROOT . PURCHASE_ROOT . 'poedit/' . $row['purchaseorder_id'] . '">' . date('d M, Y', strtotime($row['purchaseorder_date'])) . '</a>';
					$row_data[] = '<a class="" data-original-title="Edit ' . $row['purchaseorder_no'] . '" data-toggle="tooltip" data-placement="top" href="' . ROOT . PURCHASE_ROOT . 'poedit/' . $row['purchaseorder_id'] . '">' . $row["l_name"] . '</a>';
				} else {
					$row_data[] = $row['purchaseorder_no'];
					$row_data[] = date('d M, Y', strtotime($row['purchaseorder_date']));
					$row_data[] = $row["l_name"];
				}
				if ($row['branch_id'] == 10000) {
					$row_data[] = 'All Branch';
				} else if ($row['branch_id'] == 0) {
					$row_data[] = '';
				} else {
					$row_data[] = $row['branch_name'];
				}
				$row_data[] = $row['city_name'];
				$row_data[] = round($row['g_total']);
				$row_data[] = round($res['total_purchase']);
				$row_data[] = round($res['taxable_amt']);
				$row_data[] = $row['user_name'];

				if ($row['po_approval_status'] == '3') {
					$row_data[] = '<button class="btn btn-xs btn-warning">Finance Pending</button>';
				} else if ($row['po_approval_status'] == '1') {
					$row_data[] = '<button class="btn btn-xs btn-success" >Approved</button>';
				} else if ($row['po_approval_status'] == '0') {
					$row_data[] = '<button class="btn btn-xs btn-warning">Approved Pending</button>';
				} else if ($row['po_approval_status'] == '2') {
					$disapproved = get_po_disapproved_reason($dbcon, 'tbl_purchaseorder_aprv_log', 'approve_remark', $row['purchaseorder_id'], 'approve_status', '2', 'purchaseorder_aprv_id');
					$row_data[] = '<button class="btn btn-xs btn-danger" title="' . $disapproved . '">Disapproved</button>';
				} else {
					$disapproved_finance = get_po_disapproved_reason($dbcon, 'tbl_purchaseorder_finance_aprv_log', 'approve_remark', $row['purchaseorder_id'], 'approve_status', '4', 'po_finance_approve_id');
					$row_data[] = '<button class="btn btn-xs btn-danger" title="' . $disapproved_finance . '">Finance Disapproved</button>';
				}

				$sent_po_mail = '';
				$poprint = '';
				$delete = '';
				$edit = '';
				$cancel_po_btn = '';
				$po_app_btn = '';
				$po_finance_app = '';
				$po_short_close = '';
				$grn_done = '';
				$po_emend = '';
				$shortclose = '';
				$po_tracking = '';
				$view_attach_doc = '';

				$view_attach_doc = '<button class="btn btn-xs btn-info" data-original-title="View Attached Document" data-toggle="tooltip" data-placement="top" onClick="view_attach_document(' . $row['purchaseorder_id'] . ',\'' . $row['purchaseorder_no'] . '\')"><i class="fa fa-eye"></i></button>';

				$check_inward = "select count(grn_id) as in_cnt from tbl_grn where grn_status=0 and purchaseorder_id=" . $row['purchaseorder_id'];
				$inwad_exe = $dbcon->query($check_inward);
				$check_in = brp_mysqli_fetch_array($inwad_exe);

				$query = "select * from tbl_purchaseordertrn where purchaseordertrn_status=0 and used_status=0 and purchaseorder_id=" . $row['purchaseorder_id'];
				$query_exe = $dbcon->query($query);

				//PO Approval Button To admin
				if (in_array(PO_LIST_APPROVE, $bulkAccessArray)) {
					if (mysqli_num_rows($query_exe) > 0) {
						if ($row['po_approval_status'] == "3") {
							if (in_array(PURCHASE_ORDER_APPROVAL, $bulkAccessArray)) {
								if ($getapprovalsetting['cnt'] > 0) {
									if (($getapprovalsetting['amount'] >= $row['g_total']) && ($getapprovalsetting['auto_approval'] == 1)) {
										$po_app_btn = '<button class="btn btn-xs btn-success" data-original-title="PO Approved" data-toggle="tooltip" data-placement="top" onclick="change_po_approval_status(' . $row['purchaseorder_id'] . ',0, \'' . $row['purchaseorder_no'] . '\')"><i class="fa fa-check"></i></button>';
									}
								} else {
									$po_app_btn = '<button class="btn btn-xs btn-success" data-original-title="PO Approved" data-toggle="tooltip" data-placement="top" onclick="change_po_approval_status(' . $row['purchaseorder_id'] . ',0, \'' . $row['purchaseorder_no'] . '\')"><i class="fa fa-check"></i></button>';
								}
							}
							if (in_array(PURCHASE_ORDER_FINANCE_APPROVAL, $bulkAccessArray)) {
								if ($getapprovalsettinges['cnt'] > 0) {
									if (($getapprovalsettinges['amount'] >= $row['g_total']) && ($getapprovalsettinges['auto_approval'] == 1)) {
										$po_finance_app = '<button class="btn btn-xs btn-warning" data-original-title="Finance Approve PO" data-toggle="tooltip" data-placement="top" onclick="change_po_finance_approval_status(' . $row['purchaseorder_id'] . ',1, \'' . $row['purchaseorder_no'] . '\')"><i class="fa fa-check"></i></button>';
									}
								} else {
									$po_finance_app = '<button class="btn btn-xs btn-warning" data-original-title="Finance Approve PO" data-toggle="tooltip" data-placement="top" onclick="change_po_finance_approval_status(' . $row['purchaseorder_id'] . ',1, \'' . $row['purchaseorder_no'] . '\')"><i class="fa fa-check"></i></button>';
								}
							}
						} else if ($row['po_approval_status'] == "0") {
							if (in_array(PURCHASE_ORDER_APPROVAL, $bulkAccessArray)) {
								if ($getapprovalsetting['cnt'] > 0) {
									if (($getapprovalsetting['amount'] >= $row['g_total']) && ($getapprovalsetting['auto_approval'] == 1)) {
										$po_app_btn = '<button class="btn btn-xs btn-warning" data-original-title="Approve PO" data-toggle="tooltip" data-placement="top" onclick="change_po_approval_status(' . $row['purchaseorder_id'] . ',1, \'' . $row['purchaseorder_no'] . '\')"><i class="fa fa-check"></i></button>';
									}
								} else {
									$po_app_btn = '<button class="btn btn-xs btn-warning" data-original-title="Approve PO" data-toggle="tooltip" data-placement="top" onclick="change_po_approval_status(' . $row['purchaseorder_id'] . ',1, \'' . $row['purchaseorder_no'] . '\')"><i class="fa fa-check"></i></button>';
								}
							}
							$po_finance_app = '';
						} else if ($row['po_approval_status'] == "1") {
							if ($check_in['in_cnt'] == 0) {
								if (in_array(PURCHASE_ORDER_FINANCE_APPROVAL, $bulkAccessArray)) {
									if ($getapprovalsettinges['cnt'] > 0) {
										if (($getapprovalsettinges['amount'] >= $row['g_total']) && ($getapprovalsettinges['auto_approval'] == 1)) {
											$po_finance_app = '<button class="btn btn-xs btn-success" data-original-title="Finace PO Approved" data-toggle="tooltip" data-placement="top" onclick="change_po_finance_approval_status(' . $row['purchaseorder_id'] . ',0, \'' . $row['purchaseorder_no'] . '\')"><i class="fa fa-check"></i></button>';
										}
									} else {
										$po_finance_app = '<button class="btn btn-xs btn-success" data-original-title="Finace PO Approved" data-toggle="tooltip" data-placement="top" onclick="change_po_finance_approval_status(' . $row['purchaseorder_id'] . ',0, \'' . $row['purchaseorder_no'] . '\')"><i class="fa fa-check"></i></button>';
									}
								}
							} else {
								$po_finance_app = '';
							}
						} else if ($row['po_approval_status'] == "4") {
							if (in_array(PURCHASE_ORDER_FINANCE_APPROVAL, $bulkAccessArray)) {
								if ($getapprovalsettinges['cnt'] > 0) {
									if (($getapprovalsettinges['amount'] >= $row['g_total']) && ($getapprovalsettinges['auto_approval'] == 1)) {
										$po_finance_app = '<button class="btn btn-xs btn-warning" data-original-title="Finace PO Approved" data-toggle="tooltip" data-placement="top" onclick="change_po_finance_approval_status(' . $row['purchaseorder_id'] . ',0, \'' . $row['purchaseorder_no'] . '\')"><i class="fa fa-check"></i></button>';
									}
								} else {
									$po_finance_app = '<button class="btn btn-xs btn-warning" data-original-title="Finace PO Approved" data-toggle="tooltip" data-placement="top" onclick="change_po_finance_approval_status(' . $row['purchaseorder_id'] . ',0, \'' . $row['purchaseorder_no'] . '\')"><i class="fa fa-check"></i></button>';
								}
							}
							$po_finance_app = '';
						} else if ($row['po_approval_status'] == "2") {
							if (in_array(PURCHASE_ORDER_APPROVAL, $bulkAccessArray)) {
								if ($getapprovalsetting['cnt'] > 0) {
									if (($getapprovalsetting['amount'] >= $row['g_total']) && ($getapprovalsetting['auto_approval'] == 1)) {
										$po_app_btn = '<button class="btn btn-xs btn-warning" data-original-title="Approve PO" data-toggle="tooltip" data-placement="top" onclick="change_po_approval_status(' . $row['purchaseorder_id'] . ',1, \'' . $row['purchaseorder_no'] . '\')"><i class="fa fa-check"></i></button>';
									}
								} else {
									$po_app_btn = '<button class="btn btn-xs btn-warning" data-original-title="Approve PO" data-toggle="tooltip" data-placement="top" onclick="change_po_approval_status(' . $row['purchaseorder_id'] . ',1, \'' . $row['purchaseorder_no'] . '\')"><i class="fa fa-check"></i></button>';
								}
							}
							$po_finance_app = '';
						} else {
							$po_app_btn = "";
							$po_finance_app = '';
						}
					}
				}
				if ($row['po_approval_status'] == '2' || $row['po_approval_status'] == '4') {
					$po_emend = '<a class="btn btn-xs btn-info" data-original-title="PO Amend" data-toggle="tooltip" data-placement="top" href="' . ROOT . PURCHASE_ROOT . 'poemend/' . $row['purchaseorder_id'] . '"><i class="fa fa-repeat"></i></a>';
				}
				if ($row['po_approval_status'] == '0') {
					if ($row['purchase_status'] == "0") {
						if (in_array(PO_LIST_DELETE, $bulkAccessArray)) {
							$delete = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_po(' . $row['purchaseorder_id'] . ')"><i class="fa fa-trash-o"></i></button>';
						}
						if (in_array(PO_LIST_UPDATE, $bulkAccessArray)) {
							$edit = '<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . PURCHASE_ROOT . 'poedit/' . $row['purchaseorder_id'] . '"><i class="fa fa-pencil"></i></a>';
						}
					}
				} else {
					if (mysqli_num_rows($query_exe) > 0) {
						$short_close = "select * from tbl_purchaseordertrn where purchaseordertrn_status=0 and used_status=0 and shortclose_status = 1 and purchaseorder_id=" . $row['purchaseorder_id'];
						$sclose_exe = $dbcon->query($short_close);
						if (mysqli_num_rows($sclose_exe) > 0) {
							$po_short_close = '<button class="btn btn-xs btn-warning" >Short Close Aprooval Pending</button>';
						} else {
							if (in_array(PO_LIST_DELETE, $bulkAccessArray)) {
								$po_short_close = '<a onclick="shortclosepo(' . $row['purchaseorder_id'] . ',' . "'$row[purchaseorder_no]'" . ')" class="btn btn-xs btn-danger" data-original-title="Sort Close PO" data-toggle="tooltip" data-placement="top"><i class="fa fa-close"></i></a>';
							}
						}
						//$po_emend = '<a class="btn btn-xs btn-info" data-original-title="PO Amend" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'poemend/'.$row['purchaseorder_id'].'"><i class="fa fa-repeat"></i></a>';
					} else {
						if ($row['revise_status'] == 1) {
							$grn_done = '<button class="btn btn-xs btn-primary" >PO Amend</button>';
						} else {
							$shortclose_query = "select  po_id,log_id from tbl_log_po_short_close where po_id=" . $row['purchaseorder_id'] . " and short_close_status=0 and aproove_status=1 and company_id=" . $_SESSION['company_id'];
							/*var_dump($shortclose_query);exit;*/
							$sclose_exe = $dbcon->query($shortclose_query);
							if (brp_mysqli_num_rows($sclose_exe) > 0) {
								$shortclose = '<button class="btn btn-xs btn-primary" >Shortclose Done</button>';
							} else {
								$grn_done = '<button class="btn btn-xs btn-primary" >Grn Done</button>';
							}
						}
						$po_emend = '';
					}
				}
				$add_po_btn = '';
				if ($row['po_type_status'] == '2') {
					if ($row['po_req_status'] == '1') {
						$add_po_btn = '<button class="btn btn-xs btn-success" data-original-title="PO Created" data-toggle="tooltip" data-placement="top" >PO Created</button>';
					} else {
						$add_po_btn = '<a class="btn btn-xs btn-primary" data-original-title="Create PO" data-toggle="tooltip" data-placement="top" href="' . ROOT . PURCHASE_ROOT . 'direct_po_add/' . $row['purchaseorder_id'] . '"><i class="fa fa-plus"></i></a>';
						$cancel_po_btn = '<button class="btn btn-xs btn-danger" data-original-title="Cancel PO" data-toggle="tooltip" data-placement="top" onclick="cancel_po_status(' . $row['purchaseorder_id'] . ',3)"><i class="fa fa-ban"></i></button>';
					}
				}
				if ($row['po_type_status'] == '3') {
					$cancel_po_btn = '<button class="btn btn-xs btn-info" data-original-title="Request PO" data-toggle="tooltip" data-placement="top" onclick="cancel_po_status(' . $row['purchaseorder_id'] . ',2)"><i class="fa fa-check"></i></button>';
				}
				$send_whatsapp = '';
				// $send_whatsapp='<button class="btn btn-xs btn-primary" data-original-title="Send Whatsapp" data-toggle="tooltip" data-placement="top" onClick="send_purchase_order('.$row['purchaseorder_id'].')"><i class="fa fa-whatsapp"></i></button>';

				$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='" . $_SESSION['company_id'] . "'");
				$rels = mysqli_fetch_assoc($menusql);
				$menu_show_permissions = ($rels && $rels['print_permission']) ? explode(",", $rels['print_permission']) : [];
				$sql = $dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 4 AND approve_status = 1 AND status = 0 ORDER BY priority");
				while ($res = mysqli_fetch_assoc($sql)) {
					if (in_array($res['id'], $menu_show_permissions)) {
						if ($company_special['aeon_permission'] == 1) {
							if ($row['quot_type'] == 1) {
								$poprint .= '<a class="btn btn-xs btn-primary" data-original-title="' . $res['print_name'] . '" data-toggle="tooltip" data-placement="top" target="_blank"  href="' . ROOT . PRINT_ROOT . 'import_poprint_aeon' . '/' . $row['purchaseorder_id'] . '?' . time() . '" style="background: ' . $res['icon_color'] . '; border-color: ' . $res['icon_color'] . ';"><i class="' . $res['fa_icon'] . '"></i></a>';
							} else {
								$poprint .= '<a class="btn btn-xs btn-primary" data-original-title="' . $res['print_name'] . '" data-toggle="tooltip" data-placement="top" target="_blank"  href="' . ROOT . PRINT_ROOT . 'domestic_poprint_aeon' . '/' . $row['purchaseorder_id'] . '?' . time() . '" style="background: ' . $res['icon_color'] . '; border-color: ' . $res['icon_color'] . ';"><i class="' . $res['fa_icon'] . '"></i></a>';
							}
						} else {
							$poprint .= '<a class="btn btn-xs btn-primary" data-original-title="' . $res['print_name'] . '" data-toggle="tooltip" data-placement="top" target="_blank"  href="' . ROOT . PRINT_ROOT . $res['page_path'] . '/' . $row['purchaseorder_id'] . '?' . time() . '" style="background: ' . $res['icon_color'] . '; border-color: ' . $res['icon_color'] . ';"><i class="' . $res['fa_icon'] . '"></i></a>';

							if ($getspecialConfiguration['flowjet_permission'] == 1) {
								if ($row['po_approval_status'] == '1' || $row['po_approval_status'] == '4') {
									$send_email_page_path = $res['page_path'];
									$sent_po_mail = '<button class="btn btn-xs btn-primary" data-original-title="Send Mail" data-toggle="tooltip" data-placement="top" onClick="open_mail_dir_modal(' . $row['purchaseorder_id'] . ',\'' . $row['cust_email'] . '\',\'' . $send_email_page_path . '\')"><i class="fa fa-envelope"></i></button>';
								}
							}
						}
					}
				}



				/*if(mysqli_num_rows($query_exe)>0){
					if($check_in['in_cnt']==0){
						if(in_array(PO_LIST_DELETE,$bulkAccessArray)){
							$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_po('.$row['purchaseorder_id'].')"><i class="fa fa-trash-o"></i></button>';
						}
					}
				}*/


				if ($row['po_approval_status'] == '2' || $row['po_approval_status'] == '4') {
					$poprint = '';
					$send_whatsapp = '';
					$po_short_close = '';
				}

				$po_tracking = '<a class="btn btn-xs btn-info" data-original-title="PO Tracking"  data-toggle="tooltip" data-placement="top" href="' . ROOT . PURCHASE_ROOT . 'po_tracking/' . $row['purchaseorder_id'] . '">Live PO Tracking</a>';

				$row_data[] = $sent_po_mail . ' ' . $poprint . ' ' . $edit . ' ' . $delete . ' ' . $po_app_btn . ' ' . $po_finance_app . ' ' . $send_whatsapp . ' ' . $po_short_close . ' ' . $grn_done . ' ' . $po_emend . ' ' . $shortclose . ' ' . $po_tracking . ' ' . $view_attach_doc;

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode($output);
		} else if (strtolower($POST['mode']) == "add") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$companyConfiguration = getCompanyConfiguration($dbcon);
			if ($POST['revise_status']) { //Get Revise Count No
				$get_rev_cnt = "select count(purchaseorder_id) as ttl_cnt,(select purchaseorder_no from tbl_purchaseorder where purchaseorder_id=" . $POST['start_purchaseorder_id'] . ") as qt_no from tbl_purchaseorder where purchase_status=0 and start_purchaseorder_id=" . $POST['start_purchaseorder_id'];
				$rev_cnt = mysqli_fetch_assoc($dbcon->query($get_rev_cnt));
				$info['purchaseorder_no'] = $rev_cnt['qt_no'] . "/R-" . $rev_cnt['ttl_cnt'];
				$info['start_purchaseorder_id'] = $POST['start_purchaseorder_id'];
				$info['prev_purchaseorder_id'] = $POST['prev_purchaseorder_id'];
				$upd_prev_qt_sts = $dbcon->query("UPDATE tbl_purchaseorder set revise_status=1 where purchaseorder_id=" . $POST['prev_purchaseorder_id']);
			} else {
				// $info['quotation_no']		= load_quotation_no($dbcon);
				// Update Start series of No
				$info['purchaseorder_no'] = load_common_no($dbcon, PURCHASE_ORDER_SERIES);
				update_common_no($dbcon, PURCHASE_ORDER_SERIES);
			}
			/*if(isset($POST['currency_enable'])){*/
			$curncy_trn['currency_id'] = $POST['currency_id'];
			$curncy_trn['currency_rate'] = $POST['currency_rate'];
			/*}else{
				$basecurrency = getbasecurrency($dbcon);
				$curncy_trn['currency_id'] = $basecurrency['currency_id'];
				$curncy_trn['currency_rate'] = 1;
			}*/
			$info['po_type_status'] = 1;
			$info['invoicetype_id'] = $POST['invoicetype_id'];
			$info['vender_id'] = $POST['vender_id'];
			$info['consignee_id'] = $POST['consignee_id'];
			$info['purchaseorder_date'] = date('Y-m-d', strtotime($POST['purchaseorder_date']));
			$info['purchaseorder_due_date'] = date('Y-m-d', strtotime($POST['purchaseorder_due_date']));
			$info['mode_of_dispatch'] = $POST['dispatch_doc_no'];
			$info['payment_terms'] = $POST['payment_terms'];
			$info['round_off'] = $POST['round_off'];
			$info['packing'] = $POST['paking'];
			$info['remark'] = $POST['remark'];

			if ($POST['currency_id'] == $_SESSION['currency_id']) {
				$info['g_total'] = $POST['g_total'];
				$info['round_of'] = $POST['round_of'];
				$info['g_total_conv'] = $POST['g_total'] * $POST['currency_rate'];
				$info['round_of_conv'] = $POST['round_of'] * $POST['currency_rate'];
			} else {
				$info['g_total'] = $POST['g_total'] * $POST['currency_rate'];
				$info['round_of'] = $POST['round_of'] * $POST['currency_rate'];
				$info['g_total_conv'] = $POST['g_total'];
				$info['round_of_conv'] = $POST['round_of'];
			}

			$info['po_ref_id'] = $POST['po_ref_id'];
			$info['po_condition'] = $_POST['po_condition'];
			$info['currency_id'] = $_POST['currency_id'];
			$info['godown_id'] = $POST['godown_id'];
			$info['conversion_rate'] = $_POST['conversion_rate'];
			$info['vendor_reference'] = $_POST['vendor_reference'];
			$info['quotation_no'] = $_POST['quotation_no'];
			$info['quotation_date'] = date('Y-m-d', strtotime($POST['quotation_date']));
			$info['po_valid_date'] = date('Y-m-d', strtotime($POST['po_valid_date']));
			$info['supply_type'] = $_POST['supply_type'];
			$info['gst_type'] = $_POST['gst_type'];
			$info['formulaid'] = $POST['formula_id']; //added by : Dimple
			$info['delivery_type'] = $POST['delivery_type']; //added by : pathik 
			$info['po_type'] = $POST['po_type']; //added by : Maulik
			$info['tc_format'] = $POST['tc_format'];
			$info['sales_type'] = $POST['sales_type'];

			$info['currency_enable'] = $POST['currency_enable']; //Added new by Maulik    
			$info['currency_id'] = (isset($POST['currency_enable'])) ? $POST['currency_id'] : 0; //Added new by Maulik
			$info['currency_rate'] = (isset($POST['currency_enable'])) ? $POST['currency_rate'] : 1; //Added new by Maulik
			$info['financial_year_id'] = $POST['financial_year'];
			$info['terms'] = isset($_POST['terms']) ? $_POST['terms'] : ''; //added by hardi
			$info['shipped_via'] = isset($_POST['shipped_via']) ? $_POST['shipped_via'] : ''; //added by hardi
			$info['fob'] = isset($_POST['fob']) ? $_POST['fob'] : ''; //added by hardi

			$info['con_type'] = $POST['con_type'];
			$info['con_vender_id'] = $POST['con_vender_id'];
			$info['con_branch'] = $POST['con_branch'];
			$info['con_address'] = $POST['con_address'];
			$info['cons_same_as'] = $POST['same_as'];

			$info['kind_attn'] = $POST['kind_attn'];
			$info['po_sub'] = $POST['po_sub'];
			$info['quot_type'] = $POST['quot_type'];
			$info['terms_type'] = $POST['terms_type'];

			if (isset($POST['save_print'])) {
				$info['print_status'] = $POST['print_status'];
			}
			$info['cdate'] = date("Y-m-d H:i:s");

			$info['userid'] = $_SESSION['user_id'];
			$info['company_id'] = $_SESSION['company_id'];

			if (isset($POST['currency_total'])) {
				$info['currency_total'] = $POST['currency_total'];
			}


			$inserpoid = add_record('tbl_purchaseorder', array_merge($info, $curncy_trn), $dbcon, $branch_id);

			if ($inserpoid) {
				$inftrn['purchaseorder_id'] = $inserpoid;
				$inftrn['purchaseordertrn_status'] = 0;
				$updatetrnid = update_record('tbl_purchaseordertrn', $inftrn, "user_id=" . $_SESSION['user_id'] . " and purchaseorder_id=0 and purchaseordertrn_status=3", $dbcon, $branch_id);

				$info_po_attach['attach_status'] = 0;
				$info_po_attach['purchaseorder_id'] = $inserpoid;
				$upadate_so_attach = update_record('tbl_po_attch', $info_po_attach, "attach_status=3 and user_id=" . $_SESSION['user_id'], $dbcon, $branch_id);
			}
			if (!$POST['revise_status']) {
				$upd_strt_qry = $dbcon->query("update tbl_purchaseorder set start_purchaseorder_id=" . $inserpoid . " where purchaseorder_id=" . $inserpoid);
			}

			if ($POST['viewmode'] == 'Revise') {
				$po_q = "select trn.*,po.prev_purchaseorder_id from tbl_purchaseordertrn as trn 
				left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id
				where trn.purchaseorder_id=" . $inserpoid;
				$po_q_e = $dbcon->query($po_q);
				while ($row = brp_mysqli_fetch_array($po_q_e)) {
					$dbcon->query("UPDATE `tbl_purchaseordertrn` SET `used_status` = '1' WHERE `purchaseordertrn_id` = '" . $row['prev_purchaseordertrn_id'] . "'");
					//$dbcon->query("UPDATE `tbl_purchaseorder` SET `po_approval_status` = '1' WHERE `purchaseorder_id` = '".$row['prev_purchaseorder_id']."'");
				}
			}

			$cust_name = get_ledger_expense_by_id($dbcon, $POST['vender_id']);
			tbl_transcation_entry($dbcon, "Purchase Order", $POST['purchaseorder_no'], $inserpoid, $cust_name, $POST['g_total']);

			if ($inserpoid) {

				foreach ($POST['tc_id'] as $key => $name) {
					$infotrm['tc_id'] = $POST['tc_id'][$key];
					$infotrm['ref_tc_id'] = $POST['ref_tc_id'][$key];
					$infotrm['tc_priority'] = $POST['tc_priority'][$key];
					$infotrm['tc_details'] = $_POST['tc_details'][$key];
					$infotrm['purchaseorder_id'] = $inserpoid;
					$infotrm['cdate'] = date("Y-m-d H:i:s");
					$infotrm['user_id'] = $_SESSION['user_id'];
					$infotrm['company_id'] = $_SESSION['company_id'];


					if (!empty($POST['disp_term_flag']) && in_array($POST['tc_id'][$key], (array) $POST['disp_term_flag'])) {
						$insertrmid = add_record('tbl_purchaseorder_terms_trn', $infotrm, $dbcon, $branch_id);
					}
				}

				foreach ($POST['bill_sundry_tax'] as $bill_sundry_tax_id => $bill_sundry_tax_amount) {

					$info_sundry_tax['sundry_ledger_id'] = $bill_sundry_tax_id;
					//$info_sundry_tax['sundry_amount']=$bill_sundry_tax_amount;
					$info_sundry_tax['sundry_voucher_id'] = $inserpoid;
					$info_sundry_tax['sundry_voucher_type'] = PO_VOUCHER;
					$info_sundry_tax['sundry_voucher_table'] = 'tbl_purchaseorder';
					$info_sundry_tax['cdate'] = date("Y-m-d H:i:s");
					$info_sundry_tax['user_id'] = $_SESSION['user_id'];
					$info_sundry_tax['company_id'] = $_SESSION['company_id'];

					if ($POST['currency_id'] == $_SESSION['currency_id']) {
						$info_sundry_tax['sundry_amount'] = $bill_sundry_tax_amount;
						$info_sundry_tax['sundry_amount_conv'] = $bill_sundry_tax_amount * $POST['currency_rate'];
					} else {
						$info_sundry_tax['sundry_amount'] = $bill_sundry_tax_amount * $POST['currency_rate'];
						$info_sundry_tax['sundry_amount_conv'] = $bill_sundry_tax_amount;
					}

					$sundry_tax_insert = add_record('tbl_bill_sundry_transaction', array_merge($info_sundry_tax, $curncy_trn), $dbcon);
				}

				foreach ($POST['bill_sundry_addon'] as $bill_sundry_addon_id => $bill_sundry_addon_amount) {

					$info_sundry_addon['sundry_ledger_id'] = $bill_sundry_addon_id;

					$info_sundry_addon['sundry_voucher_id'] = $inserpoid;
					$info_sundry_addon['sundry_voucher_type'] = PO_VOUCHER;
					$info_sundry_addon['sundry_voucher_table'] = 'tbl_purchaseorder';
					$info_sundry_addon['cdate'] = date("Y-m-d H:i:s");
					$info_sundry_addon['user_id'] = $_SESSION['user_id'];
					$info_sundry_addon['company_id'] = $_SESSION['company_id'];

					if ($POST['currency_id'] == $_SESSION['currency_id']) {
						$info_sundry_addon['sundry_amount'] = $bill_sundry_addon_amount;
						$info_sundry_addon['sundry_amount_conv'] = $bill_sundry_addon_amount * $POST['currency_rate'];
					} else {
						$info_sundry_addon['sundry_amount'] = $bill_sundry_addon_amount * $POST['currency_rate'];
						$info_sundry_addon['sundry_amount_conv'] = $bill_sundry_addon_amount;
					}
					//print_r(array_merge($info_sundry_addon,$curncy_trn));

					$sundry_addon_insert = add_record('tbl_bill_sundry_transaction', array_merge($info_sundry_addon, $curncy_trn), $dbcon);
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

					$updateaddontaxid = update_record('tbl_bill_sundry_transaction', $info_addon, "sundry_voucher_table='tbl_purchaseorder' and isdelete=0 and sundry_voucher_id=" . $inserpoid . " and sundry_ledger_id=" . $addon_id . " ", $dbcon);
				}
			}

			if (strtolower($POST['delivery_type']) == "po_wise") {

				$sel_pro_rate = "select * from tbl_purchaseordertrn where purchaseordertrn_status=0 and purchaseorder_id=" . $inserpoid;
				$sel_pro_rate_rs = $dbcon->query($sel_pro_rate);

				while ($sel_pro_rate_rel = brp_mysqli_fetch_array($sel_pro_rate_rs)) {
					$delivery_da = "select * from tbl_purchaseorder_delivery_date as mst 
					where mst.po_delivery_date_status=0  and mst.purchaseordertrn_id=" . $sel_pro_rate_rel['purchaseordertrn_id'];

					$delivery_dae = $dbcon->query($delivery_da);
					if (brp_mysqli_num_rows($delivery_dae) > 0) {
						$inftrn11d['delivery_date'] = date('Y-m-d', strtotime($POST['purchaseorder_due_date']));
						$updatetrnid = update_record('tbl_purchaseorder_delivery_date', $inftrn11d, "po_delivery_date_status=0 and purchaseordertrn_id=" . $sel_pro_rate_rel['purchaseordertrn_id'], $dbcon, $branch_id);
					} else {
						if ($sel_pro_rate_rel['unit_id'] === $sel_pro_rate_rel['rate_unit']) {
							$sqty = $sel_pro_rate_rel['product_qty'];
						} else {
							$sqty = $sel_pro_rate_rel['product_conv_qty'];
						}
						$infodeli['purchaseordertrn_id'] = $sel_pro_rate_rel['purchaseordertrn_id'];
						$infodeli['delivery_date'] = date('Y-m-d', strtotime($POST['purchaseorder_due_date']));
						$infodeli['product_qty'] = $sqty;
						$infodeli['unit_id'] = $sel_pro_rate_rel['rate_unit'];

						$infodeli['user_id'] = $_SESSION['user_id'];
						$infodeli['cdate'] = date("Y-m-d h:i:s");
						$infodeli['company_id'] = $_SESSION['company_id'];

						$inser_del = add_record('tbl_purchaseorder_delivery_date', $infodeli, $dbcon, $branch_id);

						$po_follow_up['po_delivery_date_id'] = $inser_del;
						$po_follow_up['purchaseorder_id'] = $inserpoid;
						$po_follow_up['folloup_date'] = date("Y-m-d h:i:s");
						$po_follow_up['follow_date'] = date("Y-m-d");
						$po_follow_up['followup_status'] = 1;
						$po_follow_up['cdate'] = date("Y-m-d h:i:s");
						$po_follow_up['user_id'] = $_SESSION['user_id'];
						$po_follow_up['company_id'] = $_SESSION['company_id'];

						$inser_followup = add_record('tbl_purchaseorder_followup', $po_follow_up, $dbcon, $branch_id);

						$info_followup_status['followup_status'] = 0;
						update_record('tbl_purchaseorder_followup', $info_followup_status, "po_delivery_date_id=" . $inser_del . " and po_folloup_id	!=" . $inser_followup, $dbcon, $branch_id);
					}
				}
			}



			/*$qry ='INSERT INTO tbl_purchaseordertrn (product_type,product_id, description,product_hsn_code,product_qty,product_rate,unit_id,product_disc,product_amount,product_discount,discount_per,formulaid,tax_name1,tax_amount1,tax_name2,tax_amount2,tax_name3,tax_amount3,total,user_id,purchaseorder_id)
SELECT product_type,product_id,description,product_hsn_code,product_qty, product_rate,unit_id,product_disc,product_amount,product_discount,discount_per,formulaid,tax_name1,tax_amount1,tax_name2,tax_amount2,tax_name3,tax_amount3,total,user_id,'.$inserpoid.' FROM tbl_purchasetrntemp where po_trn_req_status=1 and user_id='.$_SESSION['user_id'];

$dbcon->query($qry);*/
			//$deleteid=delete_record('tbl_purchasetrntemp',"user_id=".$_SESSION['user_id']." and po_trn_req_status=1", $dbcon);		
			//Change Status of Temp to Requested
			//$upd_sts_qry="UPDATE `tbl_purchasetrntemp` set po_trn_req_status=1 WHERE find_in_set(purchaseordertrn_id,(SELECT GROUP_CONCAT(temptrn_ref_id) from tbl_purchaseordertrn WHERE purchaseorder_id=".$inserpoid." and purchaseordertrn_status=0 ))";
			//$upd_sts_qry_rs=$dbcon->query($upd_sts_qry);

			//update_po_status($dbcon,$inserpoid);
			update_poreq_status($dbcon, $inserpoid, $info['prev_purchaseorder_id'], $info['vender_id']);

			wip_stock_po_stock_add($dbcon, $inserpoid);

			//Update Reqested PO Ref id in table
			//$appr_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'aprv',$dbcon);
			//if($appr_btn_per){
			if ($POST['po_ref_id']) {
				$infopo['po_req_status'] = 1; //Change Status to Done
				$updateid = update_record('tbl_purchaseorder', $infopo, "purchaseorder_id=" . $POST['po_ref_id'], $dbcon);
			}
			$getapprovalsetting = get_userwise_approval_setting($dbcon, 4, $_SESSION['user_id']);
			if ($companyConfiguration['automatic_approval_po'] == 1) {
				get_automatic_po_approval($dbcon, $inserpoid);
			} else {
				if (($getapprovalsetting['amount'] >= $POST['g_total']) && ($getapprovalsetting['auto_approval'] == 1)) {
					get_automatic_po_approval($dbcon, $inserpoid);
				}
			}

			if ($companyConfiguration['automatic_approval_po'] == 1 && $companyConfiguration['automatic_finance_approval_po'] == 1) {
				get_automatic_po_finance_approval($dbcon, $inserpoid);
			} else {
				if ($getapprovalsetting['auto_approval'] == 1) {
					$getapprovalsetting = get_userwise_approval_setting($dbcon, 5, $_SESSION['user_id']);
					if (($getapprovalsetting['amount'] >= $POST['g_total']) && ($getapprovalsetting['auto_approval'] == 1)) {
						get_automatic_po_finance_approval($dbcon, $inserpoid);
					}
				}
			}
			//}

			//$appr_btn_per=check_permission("po_list",$_SESSION['user_id'],'aprv',$dbcon);
			//var_dump($appr_btn_per);
			//auto approve stop
			/* if(in_array(PO_LIST_APPROVE,$bulkAccessArray)){
						$infopo1['auserid']			    = $_SESSION['user_id'];
						$infopo1['adate']				= date("Y-m-d H:i:s");
						$infopo1['po_approval_status']	= 1;//Change Status to Done
						$updateid12=update_record('tbl_purchaseorder', $infopo1,"purchaseorder_id=".$POST['eid'] , $dbcon);

				}else{ 
						$infopo1['po_approval_status']			= 0;//Change Status to Done
						$updateid12=update_record('tbl_purchaseorder', $infopo1,"purchaseorder_id=".$POST['eid'] , $dbcon);
						//var_dump("212");
				}
				*/
			//auto approve stop
			//update po transaction

			//$dbcon->query("update tbl_purchaseordertrn set po_trn_req_status");

			//$check_po_rate_status=check_po_rates_status($dbcon, $inserpoid);

			//Insert LOG
			$log_entry = common_log_entry($dbcon, "purchaseorder_add", 1, "tbl_purchaseorder", $inserpoid);

			//auto approve log stop
			/* $logsave['approve_remark']	    = '';
			$logsave['approve_status']	    = $infopo1['po_approval_status'];
			$logsave['purchaseorder_id']	= $inserpoid;
			$logsave['user_id']			    = $_SESSION['user_id'];
			$logsave['company_id']		    = $_SESSION['company_id'];
			$logsave['cdate']				= date('Y-m-d H:i:s');

			add_record("tbl_purchaseorder_aprv_log", $logsave, $dbcon, $branch_id); */

			if (isset($POST['save_print'])) {
				$arr['printstatus'] = $POST['print_status'];
				$arr['msg'] = "1";
				$arr['eid'] = $inserpoeid;
			} else {
				if ($inserpoid) {
					$arr['msg'] = "1";
				} else
					$arr['msg'] = "0";
			}
			$arr['back'] = $POST['back'];

			echo json_encode($arr);
		} else if (strtolower($POST['mode']) == "edit") {
			//if(isset($POST['currency_enable'])){
			$curncy_trn['currency_id'] = $POST['currency_id'];
			$curncy_trn['currency_rate'] = $POST['currency_rate'];
			/*}else{
				$basecurrency = getbasecurrency($dbcon);
				$curncy_trn['currency_id'] = $basecurrency['currency_id'];
				$curncy_trn['currency_rate'] = 1;
			}*/

			$info['po_type_status'] = 1;
			/*$info['invoicetype_id']		= $POST['invoicetype_id'];*/
			$info['purchaseorder_no'] = $POST['purchaseorder_no'];
			$info['vender_id'] = $POST['vender_id'];
			$info['consignee_id'] = $POST['consignee_id'];
			$info['purchaseorder_date'] = date('Y-m-d', strtotime($POST['purchaseorder_date']));
			$info['purchaseorder_due_date'] = date('Y-m-d', strtotime($POST['purchaseorder_due_date']));
			$info['mode_of_dispatch'] = $POST['dispatch_doc_no'];
			$info['payment_terms'] = $POST['payment_terms'];
			$info['round_off'] = $POST['round_off'];
			$info['packing'] = $POST['paking'];
			$info['remark'] = $POST['remark'];

			if ($POST['currency_id'] == $_SESSION['currency_id']) {
				$info['round_off'] = $POST['round_of'];
				$info['g_total'] = $POST['g_total'];
				$info['round_of'] = $POST['round_of'];
				$info['g_total_conv'] = $POST['g_total'] * $POST['currency_rate'];
				$info['round_of_conv'] = $POST['round_of'] * $POST['currency_rate'];
			} else {
				$info['round_off'] = $POST['round_of'] * $POST['currency_rate'];
				$info['g_total'] = $POST['g_total'] * $POST['currency_rate'];
				$info['round_of'] = $POST['round_of'] * $POST['currency_rate'];
				$info['g_total_conv'] = $POST['g_total'];
				$info['round_of_conv'] = $POST['round_of'];
			}

			$info['po_condition'] = $_POST['po_condition'];
			$info['currency_id'] = $_POST['currency_id'];
			$info['godown_id'] = $POST['godown_id'];
			$info['conversion_rate'] = $_POST['conversion_rate'];
			$info['vendor_reference'] = $_POST['vendor_reference'];
			$info['quotation_no'] = $_POST['quotation_no'];
			$info['quotation_date'] = date('Y-m-d', strtotime($POST['quotation_date']));
			$info['po_valid_date'] = date('Y-m-d', strtotime($POST['po_valid_date']));
			$info['supply_type'] = $_POST['supply_type'];
			$info['gst_type'] = $_POST['gst_type'];
			$info['formulaid'] = $POST['formula_id']; //added by : Dimple
			$info['delivery_type'] = $POST['delivery_type']; //added by : pathik
			$info['po_type'] = $POST['po_type']; //added by : Maulik
			$info['tc_format'] = $POST['tc_format']; //added by : Maulik
			$info['sales_type'] = $POST['sales_type']; //added by : Maulik

			$info['currency_enable'] = $POST['currency_enable']; //Added new by Maulik    
			$info['currency_id'] = (isset($POST['currency_enable'])) ? $POST['currency_id'] : 0; //Added new by Maulik
			$info['currency_rate'] = (isset($POST['currency_enable'])) ? $POST['currency_rate'] : 1; //Added new by Maulik
			$info['terms'] = isset($_POST['terms']) ? $_POST['terms'] : ''; //added by hardi
			$info['shipped_via'] = isset($_POST['shipped_via']) ? $_POST['shipped_via'] : ''; //added by hardi
			$info['fob'] = isset($_POST['fob']) ? $_POST['fob'] : ''; //added by hardi

			//start by maulik Kapatel
			$info['con_type'] = $POST['con_type'];
			$info['con_vender_id'] = $POST['con_vender_id'];
			$info['con_branch'] = $POST['con_branch'];
			$info['con_address'] = $POST['con_address'];
			$info['cons_same_as'] = $POST['same_as'];
			//end By Maulik Kapatel

			$info['po_sub'] = $POST['po_sub'];
			$info['kind_attn'] = $POST['kind_attn'];
			$info['quot_type'] = $POST['quot_type'];
			$info['mdate'] = date("Y-m-d H:i:s");
			$info['company_id'] = $_SESSION['company_id'];
			if (isset($POST['save_print'])) {
				$info['print_status'] = $POST['print_status'];
			}
			//$info['cdate']				= 	date("Y-m-d H:i:s");
			$info['muserid'] = $_SESSION['user_id'];


			$updateid1 = update_record('tbl_purchaseorder', array_merge($info, $curncy_trn), "purchaseorder_id=" . $POST['eid'], $dbcon);

			$deltrmid = delete_record('tbl_purchaseorder_terms_trn', "purchaseorder_id=" . $POST['eid'], $dbcon, $branch_id);
// Ensure $disp_term_flag is an array
			$disp_term_flag = isset($_POST['disp_term_flag']) ? $_POST['disp_term_flag'] : [];

			foreach ($_POST['tc_id'] as $key => $name) {
				$infotrm['tc_id'] = $_POST['tc_id'][$key];
				$infotrm['ref_tc_id'] = $_POST['ref_tc_id'][$key];
				$infotrm['tc_priority'] = $_POST['tc_priority'][$key];
				$infotrm['tc_details'] = $_POST['tc_details'][$key];
				$infotrm['purchaseorder_id'] = $_POST['eid'];
				$infotrm['cdate'] = date("Y-m-d H:i:s");
				$infotrm['user_id'] = $_SESSION['user_id'];
				$infotrm['company_id'] = $_SESSION['company_id'];

				// Check if the current tc_id is in the disp_term_flag array
				if (in_array($_POST['tc_id'][$key], $disp_term_flag)) {
					$insertrmid = add_record('tbl_purchaseorder_terms_trn', $infotrm, $dbcon, $branch_id);
				}
			}


			if (strtolower($POST['delivery_type']) == "po_wise") {
				$sel_pro_rate = "select * from tbl_purchaseordertrn where purchaseordertrn_status=0 and purchaseorder_id=" . $POST['eid'];
				$sel_pro_rate_rs = $dbcon->query($sel_pro_rate);

				while ($sel_pro_rate_rel = brp_mysqli_fetch_assoc($sel_pro_rate_rs)) {
					$delivery_da = "select * from tbl_purchaseorder_delivery_date as mst 
					where mst.po_delivery_date_status=0  and mst.purchaseordertrn_id=" . $sel_pro_rate_rel['purchaseordertrn_id'];
					$delivery_dae = $dbcon->query($delivery_da);
					if (brp_mysqli_num_rows($delivery_dae) > 0) {
						$inftrn11d['delivery_date'] = date('Y-m-d', strtotime($POST['purchaseorder_due_date']));
						$updatetrnid = update_record('tbl_purchaseorder_delivery_date', $inftrn11d, "po_delivery_date_status=0 and purchaseordertrn_id=" . $sel_pro_rate_rel['purchaseordertrn_id'], $dbcon, $branch_id);
					} else {
						if ($sel_pro_rate_rel['unit_id'] === $sel_pro_rate_rel['rate_unit']) {
							$sqty = $sel_pro_rate_rel['product_qty'];
						} else {
							$sqty = $sel_pro_rate_rel['product_conv_qty'];
						}
						$infodeli['purchaseordertrn_id'] = $sel_pro_rate_rel['purchaseordertrn_id'];
						$infodeli['delivery_date'] = date('Y-m-d', strtotime($POST['purchaseorder_due_date']));
						$infodeli['product_qty'] = $sqty;
						$infodeli['unit_id'] = $sel_pro_rate_rel['rate_unit'];

						$infodeli['user_id'] = $_SESSION['user_id'];
						$infodeli['cdate'] = date("Y-m-d h:i:s");
						$infodeli['company_id'] = $_SESSION['company_id'];

						$inser_del = add_record('tbl_purchaseorder_delivery_date', $infodeli, $dbcon, $branch_id);

						$po_follow_up['po_delivery_date_id'] = $inser_del;
						$po_follow_up['purchaseorder_id'] = $POST['eid'];
						$po_follow_up['folloup_date'] = date("Y-m-d h:i:s");
						$po_follow_up['followup_status'] = 1;
						$po_follow_up['cdate'] = date("Y-m-d h:i:s");
						$po_follow_up['user_id'] = $_SESSION['user_id'];
						$po_follow_up['company_id'] = $_SESSION['company_id'];

						$inser_followup = add_record('tbl_purchaseorder_followup', $po_follow_up, $dbcon, $branch_id);

						$info_followup_status['followup_status'] = 0;
						update_record('tbl_purchaseorder_followup', $info_followup_status, "po_delivery_date_id=" . $inser_del . " and po_folloup_id	!=" . $inser_followup, $dbcon, $branch_id);
					}
				}
			}
			//$check_po_rate_status=check_po_rates_status($dbcon, $POST['eid']);	

			//Update Reqested PO Ref id in table
			//$appr_btn_per=check_permission("po_list",$_SESSION['user_id'],'aprv',$dbcon);
			//var_dump($appr_btn_per);
			// auto approve stop
			/* if(in_array(PO_LIST_APPROVE,$bulkAccessArray)){
					$infopo['auserid']			   = $_SESSION['user_id'];
					$infopo['adate']			   = date("Y-m-d H:i:s");					
					$infopo['po_approval_status']  = 1;//Change Status to Done
					$updateid12=update_record('tbl_purchaseorder', $infopo,"purchaseorder_id=".$POST['eid'] , $dbcon);


			}else{ 
					$infopo['po_approval_status']			= 0;//Change Status to Done
					$updateid12=update_record('tbl_purchaseorder', $infopo,"purchaseorder_id=".$POST['eid'] , $dbcon);
					//var_dump("212");
				} */
			// auto approve stop
			//update_po_status($dbcon,$POST['eid']);

			//Insert LOG
			$log_entry = common_log_entry($dbcon, "purchaseorder_add", 2, "tbl_purchaseorder", $POST['eid']);

			wip_stock_po_stock_add($dbcon, $POST['eid']);

			if (isset($POST['save_print'])) {
				//var_dump($updateid1);
				$arr['printstatus'] = $POST['print_status'];
				$arr['msg'] = "update";
				$arr['eid'] = $POST['eid'];
			} else {

				if ($updateid1) {
					$arr['msg'] = "update";
				} else {
					$arr['msg'] = 0;
				}
			}
			echo json_encode($arr);
		} else if (strtolower($POST['mode']) == "delete") {
			$info['status'] = 2;
			$info1['purchaseordertrn_status'] = 2;

			$que_po = "select * from tbl_purchaseordertrn where temptrn_ref_id!='' and purchaseorder_id=" . $POST['eid'];
			$resi = $dbcon->query($que_po);
			while ($re_po = brp_mysqli_fetch_assoc($resi)) {
				delete_po_req_status($dbcon, $re_po['purchaseordertrn_id']);
			}

			$updateinvoiceid = update_record(' tbl_purchaseorder', $info, "purchaseorder_id=" . $POST['eid'], $dbcon);
			$updatetrancationid = update_record('tbl_purchaseordertrn', $info1, "purchaseorder_id=" . $POST['eid'], $dbcon);


			//update_po_status($dbcon,$POST['eid']);
			//Insert LOG
			$log_entry = common_log_entry($dbcon, "purchaseorder_add", 3, "tbl_purchaseorder", $POST['eid']);

			if ($updatetrancationid)
				echo "1";
			else
				echo "0";
		} else if (strtolower($POST['mode']) == "get_gst_statecode") {
			$arr = get_gst_statecode($dbcon, $POST['cust_id']);
			echo $arr;
		} else if (strtolower($POST['mode']) == "get_hsn_code") {

			// Sanitize and validate inputs
			$product_id = isset($POST['product_id']) ? (int) $POST['product_id'] : 0;
			$company_id = isset($_SESSION['company_id']) ? (int) $_SESSION['company_id'] : 0;

			// Only run query if product_id is valid (non-zero)
			if ($product_id > 0) {

				$qry = "
            SELECT hc.hsn_code 
            FROM product_mst AS pm
            JOIN mst_hsn_code AS hc 
                ON pm.product_hsn = hc.hsn_id 
            WHERE pm.product_id = $product_id 
              AND pm.company_id = $company_id
        ";

				$result = $dbcon->query($qry);
				$row = brp_mysqli_fetch_assoc($result);

				echo isset($row['hsn_code']) ? $row['hsn_code'] : '';
			} else {
				// Product ID missing or invalid — don’t query
				echo ''; // or you can echo '0' or a custom message if needed
			}
		} else if (strtolower($POST['mode']) == "load_productdata") {

			$eid = isset($POST['eid']) ? (int) $POST['eid'] : 0;
			$vender_id = isset($POST['vender_id']) ? (int) $POST['vender_id'] : 0;
			$company_id = isset($_SESSION['company_id']) ? (int) $_SESSION['company_id'] : 0;

			if (!$eid || !$vender_id || !$company_id) {
				die(json_encode(['error' => 'Missing required parameters']));
			}

			$qry = "
        SELECT popro.*, com.stateid AS com_stateid, ven.stateid AS ven_stateid 
        FROM product_mst AS popro
        LEFT JOIN tbl_company AS com ON com.company_id = $company_id
        LEFT JOIN tbl_ledger AS ven ON ven.l_id = $vender_id
        WHERE product_id = $eid
    ";
			$result = $dbcon->query($qry);
			$row = brp_mysqli_fetch_assoc($result);

			$today_date = date('Y-m-d');
			$qry_purchase_card_rate = "
        SELECT cardtrn.price
        FROM tbl_purchasecardtrn AS cardtrn
        LEFT JOIN tbl_product_party_purchase AS pcard 
            ON pcard.party_purchase_id = cardtrn.party_purchase_id
        WHERE pcard.card_status = 0 
            AND cardtrn.purchasecardtrn_status = 0 
            AND pcard.valid_date >= '$today_date'
            AND pcard.effective_date <= '$today_date'
            AND pcard.is_aproove = 1
            AND pcard.is_active = 0
            AND cardtrn.product_id = $eid
            AND cardtrn.vendor_id = $vender_id
        ORDER BY cardtrn.purchasecardtrn_id DESC 
        LIMIT 1
    ";
			$result_purchase_card_rate = $dbcon->query($qry_purchase_card_rate);
			$row_purchase_card_rate = brp_mysqli_fetch_assoc($result_purchase_card_rate);

			$row['prate'] = !empty($row_purchase_card_rate['price'])
				? $row_purchase_card_rate['price']
				: $row['product_purchase_rate'];

			echo json_encode($row);
		} else if (strtolower($POST['mode']) == "get_series_no") {
			$query = "select * from tbl_invoicetype where status=0 and type_id=" . trim($POST['type_id']) . " and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
			$result = $dbcon->query($query);
			$row = brp_mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		} else if (strtolower($POST['mode']) == "load_typeswise_terms") {
			$quot_type = $POST['quot_type'];
			$purchaseorder_id = $POST['purchaseorder_id'];
			$cust_id = $POST['cust_id'];
			$terms_type = $POST['terms_type'];
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
				$terms_qry = "select * from tbl_terms_condition where tc_status=0 and find_in_set(" . $quot_type . ",tc_for) group by print_name order by tc_priority";
			} else {
				$terms_qry = "select * from tbl_terms_condition where tc_status=0 and  find_in_set(" . $quot_type . ",tc_for) order by tc_priority";
			}


			$terms_qry_rs = $dbcon->query($terms_qry);
			$t = 1;
			while ($terms_rel = mysqli_fetch_assoc($terms_qry_rs)) {
				$tc_priority = $terms_rel['tc_priority'];
				$tc_details = $terms_rel['tc_details'];
				if ($terms_type == 1) {
					if ($purchaseorder_id) {
						$quot_term_qry = "select * from tbl_purchaseorder_terms_trn where po_terms_trn_status=0 and purchaseorder_id=" . $purchaseorder_id . " and tc_id=" . $terms_rel['tc_id'] . "";
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
				} else if ($terms_type == 2) {
					if ($purchaseorder_id) {
						$quot_term_qry = "select * from tbl_purchaseorder_terms_trn where po_terms_trn_status=0 and purchaseorder_id=" . $purchaseorder_id . " and tc_id=" . $terms_rel['tc_id'] . "";
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
					if ($purchaseorder_id) {
						$quot_term_qry = "select * from tbl_purchaseorder_terms_trn where po_terms_trn_status=0 and purchaseorder_id=" . $purchaseorder_id . " and tc_id=" . $terms_rel['tc_id'] . "";
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

			$resp['resp_html'] = $str;
			echo json_encode($resp);
		} else if (strtolower($POST['mode']) == "formulavalue") {
			$rate_total = 0;
			$c_total = $POST['c_total'];
			$qry = "SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=" . $POST['eid'] . " order by tax_value desc";
			$row = $dbcon->query($qry);
			$j = 0;
			//$dis=$POST['total']*$POST['t_dis']/100;
			$rate_total = $total = $POST['total'];
			while ($tax = brp_mysqli_fetch_assoc($row)) {
				if (strpos(strtolower(" " . $tax['tax_name']), "excise") == true) {
					$rate = $total * $tax['tax_value'] / 100;
					$total += $rate;
					$rate = number_format($rate, 2, ".", "");
				} else {
					$rate = ($total) * $tax['tax_value'] / 100;
					$rate = number_format($rate, 2, ".", "");
				}
				echo '<div class="form-group">
					<label class="col-md-6 control-label">' . $tax['tax_name'] . '</label>
					<div class="col-md-4 col-xs-11">
					<input id="taxvalue' . $j . '" name="taxvalue' . $j . '" value= "' . $rate . '"type="text" class="form-control" readonly="readonly">
					</div>
					</div>
					<input id="taxname' . $j . '" name="taxname' . $j . '" value= "' . $tax['tax_name'] . '" type="hidden" class="form-control">';
				$rate_total = $rate_total + $rate;
				$j++;
			}
			$g_total = $rate_total + $c_total;
			$g_total = number_format($g_total, 2, ".", "");

			echo '<input id="rate" name="rate" value= "' . $g_total . '" type="hidden" class="form-control" >';
		} else if (strtolower($POST['mode']) == "fieldadd") {

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$companyConfiguration = getCompanyConfiguration($dbcon);
			$company_state = get_company_data($dbcon, $_SESSION['company_id']);
			//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
			/*$sale_gst = get_tax_cat_by_hsn($dbcon,trim($_POST['product_hsn_code']));*/

			$custLedgerDetails = get_cust_data_arr($dbcon, $POST['cust_id']);
			/*var_dump($POST['sales_type']);*/
			if ($POST['sales_type'] == 1) {
				$sale_gst = get_tax_cat_by_hsn($dbcon, trim($_POST['product_hsn_code']));
			} else if ($POST['sales_type'] == 2) {
				$sale_gst['tax_gst'] = 0.1;
				$sale_gst['tax_cat_id'] = 0;
			} else if ($POST['sales_type'] == 3) {
				$sale_gst['tax_gst'] = 0;
				$sale_gst['tax_cat_id'] = 0;
			} else if ($POST['sales_type'] == 4) {
				$sale_gst['tax_gst'] = 5;
				$sale_gst['tax_cat_id'] = 0;
			} else if ($POST['sales_type'] == 5) {
				$sale_gst['tax_gst'] = 0;
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
			}
			/*var_dump($sale_gst);*/
			$cgst_tax_rate = 0;
			$cgst_tax_rate_conv = 0;
			$sgst_tax_rate = 0;
			$sgst_tax_rate_conv = 0;
			$igst_tax_rate = 0;
			$igst_tax_rate_conv = 0;
			if (($company_state['stateid'] == $POST['cust_stateid']) && ($custLedgerDetails['enable_sez'] == 0)) {
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

			/*if(isset($POST['currency_enable']) && $POST['currency_enable']==1){*/
			$curncy_trn['currency_id'] = $POST['currency_id'];
			$curncy_trn['currency_rate'] = $POST['currency_rate'];
			/*}else{
					$basecurrency = getbasecurrency($dbcon);
					$curncy_trn['currency_id'] = $basecurrency['currencyid'];
					$curncy_trn['currency_rate'] = 1;
				}*/

			$info1['product_type'] = $POST['product_type'];
			if ($companyConfiguration['direct_po_create'] == 0) {
				if (empty($POST['edit_id'])) {
					$info1['product_id'] = $POST['product_id'];
				}
			} else {
				$info1['product_id'] = $POST['product_id'];
			}
			$info1['cat_id'] = $POST['cat_id'];
			$info1['description'] = $_POST['product_des'];

			$info1['product_des'] = $_POST['pro_des'];
			$info1['pro_spe'] = $_POST['pro_spe'];

			$info1['product_hsn_code'] = trim($_POST['product_hsn_code']);
			$info1['product_qty'] = $POST['product_qty'];
			$info1['product_conv_qty'] = $POST['product_conv_qty'];
			$info1['process_id'] = $POST['process_id'];
			//$info1['sqr_ft']				= $POST['sqr_ft'];
			$info1['unit_id'] = $POST['unit_id'];
			$info1['conv_unit_id'] = $POST['conv_unitid'];
			$info1['rate_unit'] = $POST['rate_unitid'];
			$info1['unit_wise'] = $POST['unit_wise'];
			$info1['product_rate'] = $POST['product_rate'];
			$info1['product_discount'] = $POST['product_discount'];
			$info1['discount_per'] = $POST['discount_per'];
			$info1['formulaid'] = $POST['formulaid'];
			$info1['product_amount'] = $POST['taxable_value'];
			$info1['sel_tax'] = $POST['sel_tax'];
			$info1['formula_tax_id'] = $POST['formula_tax_id'];
			$info1['total'] = $POST['product_amount'];
			$info1['purchasecardtrn_id'] = $POST['purchasecardtrn_id'];
			$info1['product_amount_tax'] = $POST['product_amount_tax'];
			$info1['user_id'] = $_SESSION['user_id'];
			$info1['cdate'] = date("Y-m-d h:i:s");

			/*Code By Umair:*/
			/* $info1['currency_id']			= $POST['currency_id'];
				$info1['conversion_rate']		= $POST['conversion_rate'];
				$info1['product_currency_rate']	= sprintf('%0.2f', $product_currency_rate);
				$info1['product_currency_amount']= sprintf('%0.2f', $product_currency_amount);
				$info1['product_currency_amount_tax']= sprintf('%0.2f', $product_currency_amount_tax);
				$info1['currency_total']		= sprintf('%0.2f', $currency_total); */

			/*if($POST['vendor_id']!=''){

				}*/
			//finance texasion update
			$info1['cgst_tax_per'] = isset($cgst_tax_per) ? $cgst_tax_per : 0;
			$info1['cgst_tax_rate'] = isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
			$info1['sgst_tax_per'] = isset($sgst_tax_per) ? $sgst_tax_per : 0;
			$info1['sgst_tax_rate'] = isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
			$info1['igst_tax_per'] = isset($igst_tax_per) ? $igst_tax_per : 0;
			$info1['igst_tax_rate'] = isset($igst_tax_rate) ? $igst_tax_rate : 0;

			$info1['product_tax_cat'] = $sale_gst['tax_cat_id'];

			if ($POST['currency_id'] == $company_state['currency_id']) {
				$info1['product_amount'] = $POST['taxable_value'];
				$info1['product_rate'] = $POST['product_rate'];
				$info1['product_discount'] = $POST['product_discount'];
				$info1['total'] = $POST['product_amount'];
				$info1['product_amount_tax'] = $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
				$info1['cgst_tax_rate'] = isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
				$info1['sgst_tax_rate'] = isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
				$info1['igst_tax_rate'] = isset($igst_tax_rate) ? $igst_tax_rate : 0;

				$info1['product_currency_rate'] = $POST['product_rate'] * $POST['currency_rate'];
				$info1['product_currency_amount'] = $POST['taxable_value'] * $POST['currency_rate'];
				$info1['product_discount_conv'] = $POST['product_discount'] * $POST['currency_rate'];
				$info1['currency_total'] = $POST['product_amount'] * $POST['currency_rate'];
				$info1['product_currency_amount_tax'] = $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv;
				$info1['cgst_tax_rate_conv'] = isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
				$info1['sgst_tax_rate_conv'] = isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
				$info1['igst_tax_rate_conv'] = isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;
			} else {
				$info1['product_rate'] = $POST['product_rate'] * $POST['currency_rate'];
				$info1['product_discount'] = $POST['product_discount'] * $POST['currency_rate'];
				$info1['total'] = $POST['product_amount'] * $POST['currency_rate'];
				$info1['product_amount'] = $POST['taxable_value'] * $POST['currency_rate'];
				$info1['product_amount_tax'] = $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv;
				$info1['cgst_tax_rate'] = isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
				$info1['sgst_tax_rate'] = isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
				$info1['igst_tax_rate'] = isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;

				$info1['product_currency_rate'] = $POST['product_rate'];
				$info1['product_currency_amount'] = $POST['taxable_value'];
				$info1['product_discount_conv'] = $POST['product_discount'];
				$info1['currency_total'] = $POST['product_amount'];
				$info1['product_currency_amount_tax'] = $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
				$info1['cgst_tax_rate_conv'] = isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
				$info1['sgst_tax_rate_conv'] = isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
				$info1['igst_tax_rate_conv'] = isset($igst_tax_rate) ? $igst_tax_rate : 0;
			}
			/*$info=get_product_tax($dbcon,$total,$POST['formulaid']);

				$info1['tax_name1']		= $info['tax_name1'];
				$info1['tax_amount1']	= $info['tax_amount1'];
				$info1['tax_name2']		= $info['tax_name2'];
				$info1['tax_amount2']	= $info['tax_amount2'];
				$info1['tax_name3']		= $info['tax_name3'];
				$info1['tax_amount3']	= $info['tax_amount3'];*/
			//$info1=array_merge($info1,$info);
			$table = 'tbl_purchaseordertrn';
			$tableid = 'purchaseordertrn_id';
			//var_dump($POST['viewmode']);
			if (!empty($POST['purchaseorder_id'])) {
				if ($POST['viewmode'] != "Revise") {
					$info1['purchaseorder_id'] = $POST['purchaseorder_id'];
					$table = 'tbl_purchaseordertrn';
					$tableid = 'purchaseordertrn_id';
				} else {
					$info1['purchaseordertrn_status'] = 3;
				}
			} else {
				$info1['purchaseordertrn_status'] = 3;
			}
			//var_dump(array_merge($info1,$curncy_trn));
			if (empty($POST['edit_id'])) {

				$inserid = add_record($table, array_merge($info1, $curncy_trn), $dbcon, $branch_id);
				$tax_trn_id = $inserid;
				//$tx_tran_type_id=$POST['purchaseorder_id'];
			} else {
				$updateid = update_record($table, array_merge($info1, $curncy_trn), $tableid . "=" . $POST['edit_id'], $dbcon, $branch_id);
				$updateid = $POST['edit_id'];
				$tax_trn_id = $POST['edit_id'];
				//$tx_tran_type_id='0';

				// Update the tax data of those product from the tbl_tax_trn table
				//$updStatus['tx_status']	= 2;
				//$updwhere = " tx_product_id = '".$POST['product_id']."' AND tx_transaction_id = '".$POST['edit_id']."' AND tx_transaction_type = 'purchase_order' ";
				//$updateid=update_record('tbl_tax_trn',$updStatus,$updwhere, $dbcon, $branch_id);	
			}

			if (!empty($POST['purchaseorder_id'])) {
				if (!empty($POST['edit_id'])) {
					update_poreq_status_edit($dbcon, $POST['edit_id'], $branch_id);
					wip_stock_po_stock_add($dbcon, $POST['purchaseorder_id']);
				}
			}

			//add by maulik tax trn 

			if (($cgst_tax_per != 0) && ($cgst_tax_rate != 0)) {
				$cl_id = get_ledger_by_name($dbcon, 'CGST');
				$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $cgst_tax_per, $cgst_tax_rate, $inserid, "tbl_purchaseordertrn", $POST['product_id'], 3, $POST['edit_id'], $POST['branch_id'], $POST['currency_id'], $POST['currency_rate'], $cgst_tax_rate_conv);
			}
			if (($sgst_tax_per != 0) && ($sgst_tax_rate != 0)) {
				$cl_id = get_ledger_by_name($dbcon, 'SGST');
				$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $sgst_tax_per, $sgst_tax_rate, $inserid, "tbl_purchaseordertrn", $POST['product_id'], 3, $POST['edit_id'], $POST['branch_id'], $POST['currency_id'], $POST['currency_rate'], $sgst_tax_rate_conv);
			}
			if (($igst_tax_per != 0) && ($igst_tax_rate != 0)) {
				$cl_id = get_ledger_by_name($dbcon, 'IGST');
				$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $igst_tax_per, $igst_tax_rate, $inserid, "tbl_purchaseordertrn", $POST['product_id'], 3, $POST['edit_id'], $POST['branch_id'], $POST['currency_id'], $POST['currency_rate'], $igst_tax_rate_conv);
			}

			// check for the addiotional tax on product Start -- Maulik
			$pro_amt = $POST['product_amount'] * $POST['currency_rate'];
			$count_add_tax = get_check_addition_tax($dbcon, $sale_gst['tax_cat_id'], $POST['product_amount'], $inserid, $POST['product_id'], $POST['edit_id'], $POST['branch_id'], 'tbl_purchaseordertrn', $POST['currency_id'], $POST['currency_rate'], $pro_amt);

			/* $formula_tax_id=explode(",",$POST['formula_tax_id']);

				foreach($formula_tax_id as $f)
				{
					$tax_value=get_tax_field_tax_id($dbcon,$f,'tax_value');
					$taxable_value=($info1['product_amount']*$tax_value)/100;


					$infot['tx_tax_id']=$f;
					$infot['tx_tax_value']=$tax_value;
					$infot['tx_taxable_value']=$taxable_value;
					$infot['tx_transaction_id']=$tax_trn_id;
					$infot['tx_transaction_type']='purchase_order';
					$infot['tx_product_id']=$POST['product_id'];
					$infot['tx_tran_type_id']=$tx_tran_type_id;
					$infot['user_id']	= $_SESSION['user_id'];
					$infot['cdate']= date("Y-m-d H:i:s");
					$infot['company_id']=$_SESSION['company_id'];

					$table1='tbl_tax_trn';$tableid1='tx_id';

					$inserid1=add_record($table1, $infot, $dbcon, $branch_id);

					echo $taxable_value."<br>";
				} */

			$d_id = array();
			if (strtolower($POST['delivery_type']) == "product_wise") {
				$total_delivery_qty = $POST['total_delivery_qty'];
				$delivery_date = $POST['delivery_date'];
				$arry_edit = $POST['arry_edit'];
				for ($i = 0; $i < count($total_delivery_qty); $i++) {
					$info_dil['purchaseordertrn_id'] = $tax_trn_id;
					$info_dil['delivery_date'] = date('Y-m-d', strtotime($delivery_date[$i]));
					$info_dil['product_qty'] = $total_delivery_qty[$i];
					$info_dil['unit_id'] = $POST['unit_wise'];

					$info_dil['user_id'] = $_SESSION['user_id'];
					$info_dil['cdate'] = date("Y-m-d h:i:s");
					$info_dil['company_id'] = $_SESSION['company_id'];
					//$info_dil['branch_id']		=$_SESSION['company_id'];
					//var_dump($info);

					$table_k = 'tbl_purchaseorder_delivery_date';
					$tableid_k = 'po_delivery_date_id';

					if (!empty($arry_edit[$i])) {
						$updateid_k = update_record($table_k, $info_dil, "po_delivery_date_id=" . $arry_edit[$i], $dbcon, $branch_id);
						array_push($d_id, $arry_edit[$i]);
					} else {
						$inserid_k = add_record($table_k, $info_dil, $dbcon, $branch_id);
						array_push($d_id, $inserid_k);

						$po_follow_up['po_delivery_date_id'] = $inserid_k;
						//$po_follow_up['purchaseorder_id']	 = $POST['eid'];
						$po_follow_up['folloup_date'] = date("Y-m-d h:i:s");
						$po_follow_up['follow_date'] = date('Y-m-d');
						$po_follow_up['followup_status'] = 1;
						$po_follow_up['cdate'] = date("Y-m-d h:i:s");
						$po_follow_up['user_id'] = $_SESSION['user_id'];
						$po_follow_up['company_id'] = $_SESSION['company_id'];

						$inser_followup = add_record('tbl_purchaseorder_followup', $po_follow_up, $dbcon, $branch_id);

						$info_followup_status['followup_status'] = 0;
						update_record('tbl_purchaseorder_followup', $info_followup_status, "po_delivery_date_id=" . $inserid_k . " and po_folloup_id	!=" . $inser_followup, $dbcon, $branch_id);
					}
				}
			} else {
				$query_dd = "select * from tbl_purchaseorder_delivery_date as mst 
					where mst.purchaseordertrn_id=" . $tax_trn_id . " order by po_delivery_date_id desc";
				$row_dd = $dbcon->query($query_dd);
				$rel_dd = brp_mysqli_fetch_assoc($row_dd);

				if ($info1['unit_id'] === $info1['rate_unit']) {
					$sqty = $info1['product_qty'];
				} else {
					$sqty = $info1['product_conv_qty'];
				}
				$info_dil['purchaseordertrn_id'] = $tax_trn_id;
				$info_dil['delivery_date'] = date('Y-m-d', strtotime($POST['purchaseorder_due_date']));
				$info_dil['product_qty'] = $sqty;
				$info_dil['unit_id'] = $info1['rate_unit'];

				$info_dil['user_id'] = $_SESSION['user_id'];
				$info_dil['cdate'] = date("Y-m-d h:i:s");
				$info_dil['company_id'] = $_SESSION['company_id'];
				//$info_dil['branch_id']		=$_SESSION['company_id'];
				//var_dump($info);
				$table_k = 'tbl_purchaseorder_delivery_date';
				$tableid_k = 'po_delivery_date_id';

				if (!empty($rel_dd['po_delivery_date_id'])) {
					$updateid_k = update_record($table_k, $info_dil, "po_delivery_date_id=" . $rel_dd['po_delivery_date_id'], $dbcon, $branch_id);
					array_push($d_id, $rel_dd['po_delivery_date_id']);
				} else {
					$inserid_k = add_record($table_k, $info_dil, $dbcon, $branch_id);
					array_push($d_id, $inserid_k);

					$po_follow_up['po_delivery_date_id'] = $inserid_k;
					//$po_follow_up['purchaseorder_id']	 = $POST['eid'];
					$po_follow_up['folloup_date'] = date("Y-m-d h:i:s");
					$po_follow_up['follow_date'] = date('Y-m-d');
					$po_follow_up['followup_status'] = 1;
					$po_follow_up['cdate'] = date("Y-m-d h:i:s");
					$po_follow_up['user_id'] = $_SESSION['user_id'];
					$po_follow_up['company_id'] = $_SESSION['company_id'];

					$inser_followup = add_record('tbl_purchaseorder_followup', $po_follow_up, $dbcon, $branch_id);

					$info_followup_status['followup_status'] = 0;
					update_record('tbl_purchaseorder_followup', $info_followup_status, "po_delivery_date_id=" . $inserid_k . " and po_folloup_id	!=" . $inser_followup, $dbcon, $branch_id);
				}
			}

			$did = implode(",", $d_id);
			$info_dil_1['po_delivery_date_status'] = "2";
			$updateid_p = update_record($table_k, $info_dil_1, "purchaseordertrn_id=" . $tax_trn_id . " and po_delivery_date_id NOT IN (" . $did . ")", $dbcon, $branch_id);

			if ($inserid) {
				$arr['msg'] = "1";
			} else if ($updateid) {
				$arr['msg'] = "1";
			} else {
				$arr['msg'] = "0";
			}
			echo json_encode($arr);
		} else if (strtolower($POST['mode']) == "load_tempoutward") {
			$companyConfiguration = getCompanyConfiguration($dbcon);
			$purchase_pro_search = $companyConfiguration['purchase_pro_search'];
			$pro_search = explode(",", $purchase_pro_search);
			/*$query="select sum(po.product_qty) as pqty,sum(po.product_amount) as pamt,sum(po.total) as ptotal,po.tax_name,product.* from tbl_purchasetrntemp  as po 
			left join product_mst as product on product.product_id=po.product_id  
			where purchaseordertrn_status=0 and po.product_id=".$po_id." group by po.product_id";*/

			if ($POST['eid']) {
				if ($POST['viewmode'] != "Revise") {
					$query = "select trn.*,product.product_name,product.product_type as product_type_mst,product.product_icode, dr.drawing_number, product.product_alias_name, cat.unit_name as rat_unit,tc.cat_name,proc.process_name,ctrn.price,ctrn.rate_tolerance,ctrn.discount_percentage,buni.unit_name as base_unit,cuni.unit_name as conv_unit from tbl_purchaseordertrn as trn
					left join unit_mst as cat on cat.unitid=trn.rate_unit
					left join unit_mst as buni on buni.unitid = trn.unit_id
					left join unit_mst as cuni on cuni.unitid = trn.conv_unit_id
					left join product_mst as product on product.product_id=trn.product_id 
					left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
					left join tbl_category as tc on trn.cat_id=tc.cat_id 
					left join process_mst as proc on proc.process_id=trn.process_id
					left join tbl_purchasecardtrn as ctrn on ctrn.purchasecardtrn_id=trn.purchasecardtrn_id 
					where trn.purchaseordertrn_status=0 and trn.purchaseorder_id=" . $POST['eid'];
				} else {
					$query = "select trn.*, product.product_name, product.product_type as product_type_mst, product.product_icode, dr.drawing_number, product.product_alias_name, cat.unit_name as rat_unit, tc.cat_name,proc.process_name,ctrn.price,ctrn.rate_tolerance,ctrn.discount_percentage,buni.unit_name as base_unit,cuni.unit_name as conv_unit from tbl_purchaseordertrn as trn
					left join unit_mst as cat on cat.unitid=trn.rate_unit
					left join unit_mst as buni on buni.unitid = trn.unit_id
					left join unit_mst as cuni on cuni.unitid = trn.conv_unit_id
					left join product_mst as product on product.product_id=trn.product_id
					left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
					left join tbl_category as tc on trn.cat_id=tc.cat_id 
					left join process_mst as proc on proc.process_id=trn.process_id	
					left join tbl_purchasecardtrn as ctrn on ctrn.purchasecardtrn_id=trn.purchasecardtrn_id
					where trn.purchaseordertrn_status=3 and trn.purchaseorder_id=0 and trn.user_id=" . $_SESSION['user_id'];
				}
			} else {
				$query = "select trn.*,product.product_name, product.product_icode, dr.drawing_number, product.product_alias_name, product.product_type as product_type_mst, cat.unit_name as rat_unit,tc.cat_name,proc.process_name,ctrn.price,ctrn.rate_tolerance,ctrn.discount_percentage,buni.unit_name as base_unit,cuni.unit_name as conv_unit from tbl_purchaseordertrn as trn
				left join unit_mst as cat on cat.unitid=trn.rate_unit
				left join unit_mst as buni on buni.unitid = trn.unit_id
				left join unit_mst as cuni on cuni.unitid = trn.conv_unit_id
				left join product_mst as product on product.product_id=trn.product_id
				left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
				left join tbl_category as tc on trn.cat_id=tc.cat_id 
				left join process_mst as proc on proc.process_id=trn.process_id	
				left join tbl_purchasecardtrn as ctrn on ctrn.purchasecardtrn_id=trn.purchasecardtrn_id
				where trn.purchaseordertrn_status=3 and trn.purchaseorder_id=0 and trn.user_id=" . $_SESSION['user_id'];
			}
			//echo $query;
			$result = $dbcon->query($query);
			echo '<div class="form-group">
			<div class="col-md-12 col-xs-11">
			<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
			<tr id="field">
			<th class="text-center" width="5%">#</th>
			<th class="text-center" width="10%">Product Type</th>
			<th class="text-center" width="22%">Product Name</th>
			<th class="text-center" width="10%">Product Category</th>';
			if ($POST['po_type'] == 2) {
				echo '<th class="text-center" width="10%">Process Name</th>';
			}
			echo '<th class="text-center"width="6%">HSN Code</th>
			<th class="text-center"width="8%">Qty</th>
			<th class="text-center"width="12%">Rate <span class="currency_icon"></span></th>
			<!--<th class="text-center"width="6%">Unit</th>-->
			<th class="text-center"width="8%">Discount <span class="currency_icon"></span></th>
			<th class="text-center" style="display:none" width="10%">Taxable value</th>
			<th class="text-center"width="15%">Tax Detail <span class="currency_icon"></span></th>
			<th class="text-center"width="15%">Amount <span class="currency_icon"></span></th>
			<th class="text-center"width="10%">Action</th>
			</tr>';

			//echo $query;
			if (brp_mysqli_num_rows($result) > 0) {
				$i = 1;
				while ($rel = brp_mysqli_fetch_assoc($result)) {
					if (!empty($rel['currency_id'])) {
						$currency = getcurrencydetail($dbcon, $rel['currency_id']);
					} else {
						$currency = getcurrencydetail($dbcon, $_SESSION['currency_id']);
					}
					$cat_name = ($rel['cat_name'] != null) ? $rel['cat_name'] : 'PRIMARY';

					if (in_array('drawing', $pro_search)) {
						$drawing_number = " -- (" . $rel['drawing_number'] . ")";
					}
					if (in_array('item', $pro_search)) {
						$item_code = " -- (" . $rel['product_icode'] . ")";
					}
					if (in_array('alias', $pro_search)) {
						$alias = " -- (" . $rel['product_alias_name'] . ")";
					}
					//work order no
					$que = "select smp.po_req_no as work_order_no, group_concat(req.indent_no) as indent, group_concat(product_remark) as product_remark  from tbl_request_product as req
			        left join tbl_set_main_process as smp on smp.sp_id = req.sp_id
			        where rp_id in ('" . $rel['po_ref_id'] . "') group by smp.sp_id";

					$res = $dbcon->query($que);
					$rw = brp_mysqli_fetch_array($res);

					if ($companyConfiguration['po_work_order_wise'] == 1) {
						$wno = '<br> <strong style="color:green">Work Order No : ' . $rw['work_order_no'] . '</strong><br><strong style="color:green">Indent No : ' . $rw['indent'] . '</strong><br><strong style="color:green">Product Remark : </strong> ' . $rw['product_remark'];
					}
					//$r=get_product_tax($dbcon,$rel['purchaseordertrn_id']);
					//$total=$rel['pqty']*$rel['product_purchase_rate'];
					$currency_id = $rel['currency_id'];
					$rate_label = '';
					$product_amount_label = '';
					$product_total_label = '';
					$selectCu = "SELECT * FROM tbl_currency WHERE currency_id='" . $currency_id . "' ";
					$curenresult = $dbcon->query($selectCu);
					$vrel = brp_mysqli_fetch_assoc($curenresult);

					if ($currency_id != 0) {

						if ($vrel['currency_id'] != $_SESSION['currency_id']) {
							echo '<input type="hidden" id="currency_type_response" value="' . $vrel['currency_code'] . '">';
							// 			$rate_label .= $vrel['currency_symbol'].' :' .$rel['product_rate']."<br>";
							$rate_label .= $vrel['currency_symbol'] . ' ' . $rel['product_currency_rate'];

							// $product_amount_label .= $vrel['currency_symbol'].' ' .$rel['product_amount']."<br>";
							$product_amount_label .= $vrel['currency_symbol'] . ' ' . $rel['product_currency_amount'];

							$product_total_label .= $vrel['currency_symbol'] . ' ' . $rel['currency_total'] . "<br>";
							//$product_total_label .=  $vrel['currency_symbol'].' ' .$rel['currency_total'];

						} else {
							$rate_label .= $vrel['currency_symbol'] . ' ' . number_format((float)$rel['product_rate'], 2, '.', '');
							$product_amount_label .= $vrel['currency_symbol'] . ' ' . $rel['product_amount'];
							$product_total_label .= $vrel['currency_symbol'] . ' ' . $rel['total'];
						}
					} else {
						$rate_label .= $_SESSION['currency_name'] . ' ' . number_format($rel['product_rate'], 4, '.', '');
						$product_amount_label .= $_SESSION['currency_name'] . ' ' . $rel['product_amount'];
						$product_total_label .= $_SESSION['currency_name'] . ' ' . $rel['total'];
					}

					// if($rel['unit_id']!=$rel['conv_unit_id']){
					// 	$show_qty=$rel['product_conv_qty']." ".$rel['conv_unit_name']." </br> ".$rel['product_qty']." ".$rel['unit_name'];
					// }else{
					// 	$show_qty=$rel['product_qty']." ".$rel['unit_name'];
					// }
					if ($rel['unit_id'] === $rel['rate_unit']) {
						$sqty = $rel['product_qty'];
					} else {
						$sqty = $rel['product_conv_qty'];
					}

					if ($rel['unit_id'] != $rel['conv_unit_id']) {
						$qty_lb = '<strong style="color:green;">Base Qty</strong> :' . number_format($rel['product_qty'], 4, '.', '') . ' ' . $rel['base_unit'] . '<br><strong style="color:green;">Conv. Qty</strong> :' . number_format($rel['product_conv_qty'], 4, '.', '') . ' ' . $rel['conv_unit'];
					} else {
							$qty_lb = '<strong style="color:green;">Base Qty</strong> : ' 
									. number_format(is_numeric($rel['product_qty']) ? (float)$rel['product_qty'] : 0, 4, '.', '') 
									. ' ' . htmlspecialchars($rel['base_unit']);
					}

					$over_tol = '';
					if ($rel['price'] != '') {
						if ($rel['product_rate'] > $rel['price']) {
							$tole_rate = ($rel['price'] * $rel['rate_tolerance']) / 100;
							$tol_rate = $rel['price'] + $tole_rate;
							if ($rel['product_rate'] > $tol_rate) {
								$over_tol .= "<strong><span style='color:red'>Over Tolerance Rate</span></strong>";
							}
						}
					}

					$ove_disc = '';
					if ($rel['discount_percentage'] != '') {
						if ($rel['discount_percentage'] > $rel['discount_per']) {
							$ove_disc = "<strong><span style='color:red'>Less Discount As Per Minimum Discount</span></strong>";
						}
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

					echo '<tr id="fieldtr' . $i . '">
					<td style="vertical-align:top;"> ' . $i . '</td>
					<td style="vertical-align:top;">
					' . get_pro_type_name_dynamic($dbcon, $rel['product_type_mst']) . '
					</td>
					<td style="vertical-align:top;max-width:310px">
					' . $rel['product_name'] . ' ' . $drawing_number . ' ' . $item_code . ' ' . $alias . '
					' . (!empty($rel['product_des']) ? ' <br> <strong>Desc.</strong> ' . $rel['product_des'] : '') . ' ' . $wno . ' 
					</td>
					<td style="vertical-align:top;" class="text-center">
					' . $cat_name . '
					</td>';
					if ($POST['po_type'] == 2) {
						echo '<th class="text-center" width="10%">' . $rel['process_name'] . '</th>';
					}
					echo '<td style="vertical-align:top;" class="text-center">
					' . $rel['product_hsn_code'] . '
					</td>
					<td style="vertical-align:top;" class="text-left">
					<strong style="color:green">Rate Qty</strong> :' . number_format((float)$sqty, 4, '.', '') . ' ' . $rel['rat_unit'] . '<br>' . $qty_lb . '
					</td>					
					<td style="vertical-align:top;" class="text-left">
					' . $rate_label . ' <br> ' . $over_tol . '
					</td>				
					<!--<td style="vertical-align:top" class="text-center">
					' . $rel['unit_name'] . '
					</td>-->
					<td style="vertical-align:top" class="text-left">
					' . $rel['product_discount'] . ' (' . $rel['discount_per'] . '%)<br>' . $ove_disc . '
					</td>
					<td style="vertical-align:top;display:none" class="text-left">
					' . ($product_amount_label) . '
					</td>
					<td style="vertical-align:top;display:none" class="text-left">
					' . $rel['sel_tax'] . ' - (' . $rel['product_amount_tax'] . ')
					</td>
					<td style="vertical-align:bottom;">
					' . $cgst_tax . '<br>' . $sgst_tax . '<br>' . $igst_tax . '
					</td>
					<td style="vertical-align:top" class="text-left">
					' . $product_total_label . '
					</td>
					<input type="hidden" name="amount[]" id="amount' . $i . '" value="' . (($rel['currency_id'] == $_SESSION['currency_id']) ? $rel['total'] : $rel['currency_total']) . '"/>
					<input type="hidden" name="currency_total[]" id="currency_total' . $i . '" value="' . (($rel['currency_id'] == $_SESSION['currency_id']) ? $rel['total'] : $rel['currency_total']) . '"/>

					<td style="vertical-align:top">

					<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data(' . $rel['purchaseordertrn_id'] . ',\'tbl_purchaseordertrn\',\'purchaseordertrn_id\');" id="fieldedit' . $i . '"><i class="fa fa-pencil"></i></button>

					<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data(' . $rel['purchaseordertrn_id'] . ',\'tbl_purchaseordertrn\',\'purchaseordertrn_id\');" id="fieldremove' . $i . '"><i class="fa fa-times"></i></button>';
					if (strtolower($POST['delivery_type']) == 'product_wise') {
						echo '<button type="button" class="btn btn-round btn-primary btn-xs" onclick="delivery_detail(' . $rel['purchaseordertrn_id'] . ');" ><i class="fa fa-eye" aria-hidden="true"></i> </button>';
					}
					echo '</td>	
					</tr>';

					$i++;
				}
			} else {
				echo '<tr><td colspan="12" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '</table> </div>
			</div>';
		} else if (strtolower($POST['mode']) == "get_po_tax") {
			$cust_id = $POST['cust_id'];

			$query = "select  mst.*,product.product_name,product.product_purchase_rate,cat.unit_name,product.product_name from tbl_purchasetrntemp as mst 
			left join unit_mst as cat on cat.unitid=mst.unit_id 
			left join product_mst as product on product.product_id=mst.product_id  
			where mst.product_id='$POST[eid]' order by purchaseordertrn_id desc";
			$row = $dbcon->query($query);

			while ($rel = brp_mysqli_fetch_assoc($row)) {
				$pur_trn_id = $rel['purchaseordertrn_id'];
				$rate = $rel['product_rate'];
				$qty = $rel['product_qty'];
				$product_id = $rel['product_id'];
				$pr_amount = $rate * $qty;

				$cust_arr = get_cust_data_arr($dbcon, $cust_id);
				$cust_state = $cust_arr['stateid'];
				$r = get_product_tax_formula($dbcon, $product_id, 'purchase', $cust_state);


				$r1 = json_decode($r, true);
				//$info1['formulaid']			= $r['formulaid'];
				//$arr=get_product_tax($dbcon,$rate,$r['formulaid']);

				$fid = $r1['id'];
				$tax_name = $r1['name'];
				$arr = get_product_tax_common($dbcon, $pr_amount, $fid);

				//print_r($arr);
				//echo $fid.",";
				$total = $arr['total'];
				$tax = $arr['tax_total_amount'];

				$dbcon->query("update tbl_purchasetrntemp set product_rate='$rate',product_amount='$pr_amount',product_amount_tax='$tax',formulaid='$fid',total='$total',tax_name='$tax_name',po_trn_req_status='1' where product_id='$product_id'");

				//echo $tax_name;
			}
		} else if (strtolower($POST['mode']) == "get_vendor_contact_details") {
			/* Code By Umair : to return the vendors basic information */
			$cust_id = $POST['cust_id'];
			$venqry = "SELECT `v`.`m_name`, `v`.`company_name`, `v`.`cust_mobile`, `v`.`cust_mobile`, `v`.`cust_email` FROM tbl_ledger as v WHERE `v`.`l_id`='" . $cust_id . "' AND `v`.`company_id`='" . $_SESSION['company_id'] . "'";
			$vrow = $dbcon->query($venqry);

			$vrel = brp_mysqli_fetch_assoc($vrow);

			echo json_encode($vrel);
		} else if (strtolower($POST['mode']) == "preedit") {
			$companyConfiguration = getCompanyConfiguration($dbcon);
			$q = $dbcon->query("SELECT mst.*,pro.product_name,pro.product_type,proc.process_name,(select sum(product_qty) as cqty from tbl_purchasetrntemp where FIND_IN_SET(po_ref_id ,mst.po_ref_id) AND purchaseordertrn_status = 0 ) as pen_qty FROM " . $_POST['table'] . " as mst left join product_mst as pro on mst.product_id=pro.product_id left join process_mst as proc on proc.process_id = mst.process_id  WHERE " . $_POST['whereid'] . " = '$POST[id]'");

			//var_dump("SELECT mst.*,pro.product_name,pro.product_type,proc.process_name,(select sum(product_qty) as cqty from tbl_purchasetrntemp where FIND_IN_SET(po_ref_id ,mst.po_ref_id) ) as pen_qty FROM ".$_POST['table']." as mst left join product_mst as pro on mst.product_id=pro.product_id left join process_mst as proc on proc.process_id = mst.process_id  WHERE ".$_POST['whereid']." = '$POST[id]'");

			$r = $q->fetch_assoc();

			$r['producthtml'] = getrequiredproduct($dbcon, $r['product_id'], ' and product_type=' . $r["product_type"] . '');

			//var_dump($r['producthtml']);
			$r['product_qty_show'] = number_format($r['product_qty'], 4, ".", "");
			if ($r['pen_qty'] != "") {
				$r['pending_qty'] = number_format($r['pen_qty'], 4, ".", "");
			} else {
				$r['pending_qty'] = number_format($r['product_conv_qty'], 4, ".", "");
			}
			$r['product_conv_qty_show'] = number_format($r['product_conv_qty'], 4, ".", "");
			$r['direct_po_create'] = $companyConfiguration['direct_po_create'];

			//print_r($r);
			echo json_encode($r);
		} else if (strtolower($POST['mode']) == "load_product_tax") {
			$cust_arr = get_cust_data_arr($dbcon, $POST['vendor']);
			$cust_state = $cust_arr['stateid'];
			$r = get_product_tax_formula($dbcon, $POST['pid'], $_POST['tran_type'], $cust_state);
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo $r;
			//echo $cust_state;
		} else if (strtolower($POST['mode']) == "load_invoiceno") {
			$row = array();
			$purchase_order_no = load_common_no($dbcon, PURCHASE_ORDER_SERIES);
			$row['invoiceno'] = $purchase_order_no;
			echo json_encode($row);
		} else if (strtolower($POST['mode']) == "getproduct_amount") {
			$arr = get_product_tax($dbcon, $POST['product_amount'], $POST['formulaid']);
			echo json_encode($arr);
		} else if (strtolower($POST['mode']) == "delete_data") {
			$row = array();
			if (!empty($POST['purchaseorder_id'])) {
				$info['purchaseordertrn_status'] = 2;
				//$row['producthtml']=getpurchaseproduct($dbcon,0,'Edit',$POST['purchaseorder_id']);
				delete_po_req_status($dbcon, $POST['eid']);
			} else {
				$info['purchaseordertrn_status'] = 2;
				//$row['producthtml']=getpurchaseproduct($dbcon,0,'Add');
			}
			$info_tax['tx_status'] = 2;
			$updatetax = update_record("tbl_tax_trn", $info_tax, "tx_transaction_type='tbl_purchaseordertrn' and tx_transaction_id=" . $POST['eid'], $dbcon);
			$updateid = update_record($_POST['table'], $info, $_POST['whereid'] . "=" . $POST['eid'], $dbcon);

			if ($updateid)
				$row['res'] = "1";
			else
				$row['res'] = "0";
			echo json_encode($row);
		} else if (strtolower($POST['mode']) == "load_product") {
			$type_id = $POST['type_id'];

			/*Code By Umair: To fetch those product  list which have purchase card.*/
			/*$vender_id = $POST['vender_id'];
			$sql = "SELECT product_id FROM tbl_purchasecardtrn WHERE vendor_id = '".$vender_id."'";
			$vrow=$dbcon->query($sql);
			$product_list = [];
			while($vrel=brp_mysqli_fetch_assoc($vrow)){
				$product_list[] = "'".$vrel['product_id']."'";
			}

			$product_list = implode(',', $product_list);
			if($product_list){
				$where = " and p.product_id in(".$product_list.") and product_type=".$type_id;
			}else{
				$where = " and product_type=".$type_id;
			}*/

			$where = " and product_type=" . $type_id;
			/*End Umair Code*/
			echo getrequiredproduct($dbcon, '', $where);
		}
		/*else if(strtolower($POST['mode'])=="entry_po_req_data")
		{
			$purchaseorder_id=$POST['purchaseorder_id'];
			$deleteid=delete_record('tbl_purchasetrntemp',"user_id=".$_SESSION['user_id'], $dbcon);		

			$qry ='INSERT INTO tbl_purchasetrntemp (product_type,product_id, description,product_hsn_code,product_qty,product_rate,unit_id,product_disc,product_amount,product_discount,discount_per,formulaid,tax_name1,tax_amount1,tax_name2,tax_amount2,tax_name3,tax_amount3,total,user_id)
SELECT product_type,product_id,description,product_hsn_code,product_qty, product_rate,unit_id,product_disc,product_amount,product_discount,discount_per,formulaid,tax_name1,tax_amount1,tax_name2,tax_amount2,tax_name3,tax_amount3,total,user_id FROM tbl_purchaseordertrn where purchaseordertrn_status=0 and purchaseorder_id='.$purchaseorder_id;

			$dbcon->query($qry);

		}*/ else if (strtolower($POST['mode']) == "cancel_po_status") {
			$row = array();
			$info['po_type_status'] = $POST['po_status'];

			$updateid = update_record("tbl_purchaseorder", $info, "purchaseorder_id=" . $POST['eid'], $dbcon);

			if ($updateid)
				$row['res'] = "1";
			else
				$row['res'] = "0";

			echo json_encode($row);
		} else if (strtolower($POST['mode']) == "change_po_approval_status") {
			$row = array();
			$info['po_approval_status'] = $POST['po_approval_status'];

			$updateid = update_record("tbl_purchaseorder", $info, "purchaseorder_id=" . $POST['eid'], $dbcon);

			if ($updateid)
				$row['res'] = "1";
			else
				$row['res'] = "0";
			echo json_encode($row);
		} else if (strtolower($POST['mode']) == "get_po_order") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('po', $branch_id);
			$where .= " $where_db and po.company_id=" . $_SESSION['company_id'];

			$vendor_id = $POST['vendor_id'];
			$qry = "SELECT `po`.`vender_id`,`po`.`purchaseorder_id`, `po`.`purchaseorder_no`, `po`.`po_approval_status` as stage, `po`.`purchaseorder_date`, SUM(`pdt`.`product_amount`) as product_amount, SUM(`pdt`.`product_amount` + `pdt`.`product_amount_tax`) as product_total_amount  FROM `tbl_purchaseorder` as po left join `tbl_purchaseordertrn` as pdt ON  `po`.`purchaseorder_id` = `pdt`.`purchaseorder_id` Where `po`.`vender_id`=" . $vendor_id . " and `po`.`purchase_status`= 0 $where group BY `po`.`purchaseorder_id`";


			$result = $dbcon->query($qry);
			echo '<div class="form-group">
			<div class="col-md-12 col-xs-11">
			<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
			<tr id="field">
			<th class="text-center" width="10%">PO No</th>
			<th class="text-center" width="25%">PO Date</th>
			<th class="text-center"width="8%">Net Amount</th>
			<th class="text-center"width="8%">Gross Amount</th>
			<th class="text-center"width="10%">Status</th>
			<th class="text-center"width="10%">Stage</th>
			</tr>';

			//echo $query;
			if (mysqli_num_rows($result) > 0) {
				$i = 1;
				while ($rel = brp_mysqli_fetch_assoc($result)) {
					//$r=get_product_tax($dbcon,$rel['purchaseordertrn_id']);
					//$total=$rel['pqty']*$rel['product_purchase_rate'];
					if ($rel['stage'] == '1') {
						$stage = 'Approved';
					} else {
						$stage = 'No';
					}
					echo '<tr id="fieldtr' . $i . '">
					
					<td style="vertical-align:top;" class="text-center"><a href="' . ROOT . PURCHASE_ROOT . 'poedit/' . $rel['purchaseorder_id'] . '">' . $rel['purchaseorder_no'] . '</a>
					</td>
					<td style="vertical-align:top;" class="text-center">
					' . $rel['purchaseorder_date'] . '
					</td>					
					<td style="vertical-align:top;" class="text-right">
					' . $rel['product_amount'] . '
					</td>				
					<td style="vertical-align:top" class="text-center">
					' . $rel['product_total_amount'] . '
					</td>
					<td style="vertical-align:top" class="text-center">
					</td>
					<td style="vertical-align:top" class="text-center">
					' . $stage . '
					</td>

					</tr>';
					$i++;
				}
			} else {
				echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '</table> </div>
			</div>';
		} else if (strtolower($POST['mode']) == "get_des") {
			echo '';
		} else if (strtolower($POST['mode']) == "get_po_billing_terms") {
			/*echo "<pre>";
			print_r($_POST);*/
		} else if (strtolower($POST['mode']) == "load_party_purchase_dtl") {
			$qt_qry = "select qt.*,country_name,state_name,city_name,led.company_name,led.cust_mobile,led.m_address,led.gst_no from tbl_purchaseorder as qt
			left join tbl_ledger as led on led.l_id=qt.vender_id
			left join country_mst as country on country.countryid=led.countryid
			left join state_mst as state on state.stateid=led.stateid
			left join city_mst as city on city.cityid=led.cityid
			where qt.purchaseorder_id=" . $POST['purchase_order_id'];
			$qt_rel = brp_mysqli_fetch_assoc($dbcon->query($qt_qry));

			$getspecialConfiguration = getspecialConfiguration($dbcon);

			if ($getspecialConfiguration['rb_auto_permission'] == 1) {
				$sales_order_no = "select trn.purchaseordertrn_id, trn.po_ref_id, req.sp_id from tbl_purchaseordertrn as trn
				left join tbl_request_product as req on req.rp_id = trn.po_ref_id
				where purchaseorder_id=" . $POST['purchase_order_id'] . " group by req.sp_id";
				//var_dump($sales_order_no);
				$sales_order_no_e = $dbcon->query($sales_order_no);
				$sales_no = "";
				$client_name = "";
				while ($rel = brp_mysqli_fetch_array($sales_order_no_e)) {

					//$sales_order_trn_id=get_so_no_po_ref($dbcon,$rel['perent_id']);

					$q = "SELECT sales_order_trn_id FROM tbl_request_product WHERE sp_id='" . $rel['sp_id'] . "' AND main_request=1 GROUP BY sp_id";
					$e = $dbcon->query($q);
					$r = brp_mysqli_fetch_array($e);

					$so_no = "select so.sales_order_no,led.l_name from tbl_sales_ordertrn as strn
					left join tbl_sales_order as so on so.sales_order_id = strn.sales_order_id
					left join tbl_ledger as led on led.l_id = so.cust_id
					where strn.sales_ordertrn_id=" . $r['sales_order_trn_id'];

					//var_dump($so_no);
					$so_no_e = $dbcon->query($so_no);
					$so_no_r = brp_mysqli_fetch_array($so_no_e);
					$sales_no .= $so_no_r['sales_order_no'] . "<br>";
					$client_name .= $so_no_r['l_name'] . "<br>";
				}
			}
			//Party PO Details Table View
			$str = '';
			$str .= '<div class="form-group">
			<table class="display table table-bordered table-striped">
			<tr>
			<td colspan="2"><strong>Company Name:</strong> ' . $qt_rel['company_name'] . '</td>
			<td><strong>Contact No.:</strong> ' . $qt_rel['cust_mobile'] . '</td>
			</tr>
			<tr>
			<td colspan="2"><strong>Address:</strong> ' . $qt_rel['m_address'] . '</td>
			<td><strong>GST No.:</strong> ' . $qt_rel['gst_no'] . '</td>
			</tr>
			<!--<tr>
			<td><strong>City:</strong> ' . $qt_rel['city_name'] . '</td>
			<td><strong>State:</strong> ' . $qt_rel['state_name'] . '</td>
			<td><strong>Country:</strong> ' . $qt_rel['country_name'] . '</td>
			</tr>-->
			<tr>
			<td><strong>City:</strong> ' . $qt_rel['city_name'] . '</td>
			<td><strong>State:</strong> ' . $qt_rel['state_name'] . '</td>
			<td><strong>Country:</strong> ' . $qt_rel['country_name'] . '</td>
			</tr>
			<tr>
			<td><strong>Purchase order No:</strong> ' . $qt_rel['purchaseorder_no'] . '</td>
			<td><strong>Purchase Order Date:</strong> ' . date("d-M-Y", strtotime($qt_rel["purchaseorder_date"])) . '</td>
			<td><strong>Purchase Order Amount:</strong> ' . $qt_rel['g_total'] . '</td>
			</tr>
			';

			if ($getspecialConfiguration['rb_auto_permission'] == 1) {
				$str .= '<tr>
					<td><strong>Sales Order No :</strong> ' . $sales_no . '</td>
					<td colspan="2"><strong>Client Name :</strong> ' . $client_name . '</td>
				</tr>
				';
			}
			$str .= '</table></div>
			<hr/>
			';

			$qt_rel['mod_po_comp_div_sec'] = $str;

			echo json_encode($qt_rel);
		} else if (strtolower($POST['mode']) == "load_pro_purchase_dtl") {
			$companyConfiguration = getCompanyConfiguration($dbcon);
			$purchase_pro_search = $companyConfiguration['purchase_pro_search'];
			$pro_search = explode(",", $purchase_pro_search);
			$qt_qry = "select trn.*,pmst.product_name,pmst.product_icode, dr.drawing_number, pmst.product_alias_name,unit.unit_name,ctrn.price,ctrn.rate_tolerance,ctrn.discount_percentage,tc.cat_name from tbl_purchaseordertrn as trn 
			left join product_mst as pmst on pmst.product_id = trn.product_id
			left join tbl_drawing as dr on dr.drawing_id = pmst.drawing_id
			left join tbl_category as tc on pmst.product_category=tc.cat_id
			left join unit_mst as unit on unit.unitid = trn.rate_unit
			left join tbl_purchasecardtrn as ctrn on ctrn.purchasecardtrn_id=trn.purchasecardtrn_id
			where trn.purchaseordertrn_status=0 and trn.purchaseorder_id=" . $POST['purchase_order_id'];
			$qt_rel = $dbcon->query($qt_qry);
			//Party PO Details Table View
			$str = '';
			$str .= '<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
			<tr id="field">
				<th class="text-center" width="6%">Product Type</th>
				<th class="text-center" width="12%">Product Name</th>
				<th class="text-center" width="10%">Product Category</th>
				<th class="text-center" width="6%">HSN Code</th>
				<th class="text-center" width="6%">Qty</th>
				<th class="text-center" width="10%">Rate</th>
				<th class="text-center" width="10%">Discount</th>
				<th class="text-center" width="8%">Amount</th>
				<th class="text-center" width="8%">Action</th>
			</tr>';
			while ($row = brp_mysqli_fetch_array($qt_rel)) {

				$cat_name = ($row['cat_name'] != null) ? $row['cat_name'] : 'PRIMARY';

				if (in_array('drawing', $pro_search)) {
					$drawing_number = " -- (" . $row['drawing_number'] . ")";
				}
				if (in_array('item', $pro_search)) {
					$item_code = " -- (" . $row['product_icode'] . ")";
				}
				if (in_array('alias', $pro_search)) {
					$alias = " -- (" . $row['product_alias_name'] . ")";
				}


				if ($row['unit_id'] === $row['rate_unit']) {
					$sqty = $row['product_qty'];
				} else {
					$sqty = $row['product_conv_qty'];
				}

				$over_tol = '';
				if ($row['price'] != '') {
					if ($row['product_rate'] > $row['price']) {
						$tole_rate = ($row['price'] * $row['rate_tolerance']) / 100;
						$tol_rate = $row['price'] + $tole_rate;
						if ($row['product_rate'] > $tol_rate) {
							$over_tol .= "<strong><span style='color:red'>Over Tolerance Rate</span></strong>";
						}
					}
				}

				$ove_disc = '';
				if ($row['discount_percentage'] != '') {
					if ($row['discount_percentage'] > $row['discount_per']) {
						$ove_disc = "<strong><span style='color:red'>Less Discount As Per Minimum Discount</span></strong>";
					}
				}

				$str .= '<tr>
					<td>' . get_pro_type_name($row['product_type']) . '</td>
					<td>' . $row['product_name'] . ' ' . $drawing_number . ' ' . $item_code . ' ' . $alias . '</td>
					<td>' . $cat_name . '</td>
					<td>' . $row['product_hsn_code'] . '</td>
					<td>' . number_format($sqty, 4, '.', '') . ' ' . $row['unit_name'] . '</td>
					<td>' . number_format($row['product_rate'], 2, '.', '') . ' <br> ' . $over_tol . '</td>
					<td>' . $row['product_discount'] . ' (' . $row['discount_per'] . '%)<br>' . $ove_disc . '</td>
					<td>' . $row['product_amount'] . '</td>
					<td>
						<button type="button" class="btn btn-round btn-primary btn-xs" onclick="delivery_detail(' . $row['purchaseordertrn_id'] . ');" ><i class="fa fa-eye" aria-hidden="true"></i> </button>
					</td>
				</tr>';
			}
			$str .= '</table>
			<hr/>
			';

			$res['mod_po_pro_div_sec'] = $str;

			echo json_encode($res);
		} else if (strtolower($POST['mode']) == "load_purchase_hist_datatable") {
			$companyConfiguration = getCompanyConfiguration($dbcon);
			$where = '';
			$where .= " log.is_delete=0 and log.purchaseorder_id=" . $POST['purchase_order_id'];

			$appData = array();
			$i = 1;
			$aColumns = array('log.purchaseorder_aprv_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id', 'log.purchaseorder_id', '(select count(fin.purchaseorder_id) from tbl_purchaseorder_finance_aprv_log as fin where fin.purchaseorder_id=log.purchaseorder_id and fin.is_delete=0 ) as cnt');
			$sIndexColumn = "log.purchaseorder_aprv_id";
			$isWhere = array(" " . $where . " ");
			$sTable = "tbl_purchaseorder_aprv_log as log";
			$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
			$hOrder = "log.purchaseorder_aprv_id desc";
			include($include . '/pagging.php');
			//echo $sQuery;
			//exit;
			$appData = array();
			$id = 1;
			foreach ($sqlReturn as $row) {

				$delete = '';
				$tbl = 'tbl_purchaseorder_aprv_log';
				$tblid = 'purchaseorder_aprv_id';
				$status = 'is_delete';
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['user_name'];

				if ($row['approve_status'] == '3') {
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
				} else if ($row['approve_status'] == '2') {
					$row_data[] = '<div class="external-event label label-danger ui-draggable" style="cursor:auto;">Disapproved</div>';
				} else {
					$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Approve Pending</div>';
				}

				$row_data[] = nl2br($row['approve_remark']);
				$row_data[] = date("d-M-Y h:i A", strtotime($row['cdate']));


				if ($row['cnt'] == 0) {
					if ($id == 1) {
						$delete = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_po_approval(' . $row['purchaseorder_aprv_id'] . ',\'' . $tbl . '\',\'' . $tblid . '\',\'' . $status . '\',\'' . $row['purchaseorder_id'] . '\')"><i class="fa fa-trash-o"></i></button>';
					}
				}

				$row_data[] = $delete;

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode($output);
		} else if (strtolower($POST['mode']) == "add_po_apprv_hist") {
			$companyConfiguration = getCompanyConfiguration($dbcon);
			$info1['approve_remark'] = $POST['approve_remark'];
			$info1['approve_status'] = $POST['approve_status'];
			$info1['purchaseorder_id'] = $POST['purchase_order_id'];
			$info1['user_id'] = $_SESSION['user_id'];
			$info1['company_id'] = $_SESSION['company_id'];
			$info1['cdate'] = date('Y-m-d H:i:s');

			$inserid = add_record("tbl_purchaseorder_aprv_log", $info1, $dbcon);

			$info['po_approval_status'] = $POST['approve_status'];

			$updateid = update_record("tbl_purchaseorder", $info, "purchaseorder_id=" . $POST['purchase_order_id'], $dbcon);

			if ($POST['approve_status'] == 3) {
				if ($companyConfiguration['automatic_finance_approval_po'] == 1) {
					get_automatic_po_finance_approval($dbcon, $POST['purchase_order_id']);
				} else {
					$chktotal = $dbcon->query("SELECT g_total FROM tbl_purchaseorder WHERE purchase_order_id = " . $POST['purchase_order_id']);
					$gettotal = brp_mysqli_fetch_assoc($chktotal);
					$getapprovalsetting = get_userwise_approval_setting($dbcon, 5, $_SESSION['user_id']);
					if (($getapprovalsetting['amount'] >= $gettotal['g_total']) && ($getapprovalsetting['auto_approval'] == 1)) {
						get_automatic_po_finance_approval($dbcon, $POST['purchase_order_id']);
					}
				}
			}
		} else if (strtolower($POST['mode']) == "get_po_vendor_details") {
			$vendor_id = $POST['vendor_id'];
			$sql = "SELECT `v`.`l_id`,`v`.`l_name`,`v`.`l_form`, `v`.`cust_pincode`, `v`.`m_address`, `v`.`cust_mobile`, `v`.`cust_email`, `v`.`cust_website`, `v`.`gst_no`, `conm1`.`country_name`, `cm1`.`city_name`, `sm`.`state_name`  FROM `tbl_ledger` as v left join `country_mst` as conm1  ON `v`.`countryid`= `conm1`.`countryid` left join `city_mst` as cm1 ON `v`.`cityid`= `cm1`.`cityid` left join `state_mst` as sm ON `v`.`stateid`= `sm`.`stateid`  WHERE `v`.`l_id` = '" . $vendor_id . "' AND `v`.`company_id`='" . $_SESSION['company_id'] . "'";
			$vrow = $dbcon->query($sql);
			$rel = brp_mysqli_fetch_assoc($vrow);


			echo '<section class="panel">
			<div class="panel-body bio-graph-info">
			<h1>Vendor Details</h1>
			<div class="row">
			<div class="bio-row">
			<p><span>Address </span>: ' . $rel["m_address"] . '</p>
			</div>
			<div class="bio-row">
			<p><span>City </span>: ' . $rel["city_name"] . '</p>
			</div>
			<div class="bio-row">
			<p><span>State </span>: ' . $rel["state_name"] . '</p>
			</div>
			<div class="bio-row">
			<p><span>Country</span>: ' . $rel["country_name"] . '</p>
			</div>
			<div class="bio-row">
			<p><span>Fax No. </span>: NA</p>
			</div>
			<div class="bio-row">
			<p><span>Email ID </span>: ' . $rel["cust_email"] . '</p>
			</div>
			<div class="bio-row">
			<p><span>Mobile </span>: ' . $rel["cust_mobile"] . '</p>
			</div>
			<div class="bio-row">
			<p><span>Website </span>: ' . $rel["cust_website"] . '</p>
			</div>
			<div class="bio-row">
			<p><span>Pin Code </span>: ' . $rel["cust_pincode"] . '</p>
			</div>

			</div>
			</div>
			</section>';
		} else if (strtolower($POST['mode']) == "get_po_history") {
			$eid = $POST['eid']; // as purchase id
			$sql = "SELECT `u`.`user_name` as prepared_by,`po`.`cdate`, `mu`.`user_name` as last_modify_by, `po`.`mdate`, `au`.`user_name` as approved_by, `po`.`adate`, `po`.`po_approval_status` as stage, `po`.`purchaseorder_due_date` as delivery_date  FROM `tbl_purchaseorder` as po left join `users` as u ON  `po`.`userid` = `u`.`user_id` left join `users` as mu ON  `po`.`muserid` = `mu`.`user_id` left join `users` as au ON  `po`.`auserid` = `au`.`user_id` Where `po`.`purchaseorder_id`='" . $eid . "' and `po`.`purchase_status`= 0 and `po`.`company_id`='" . $_SESSION['company_id'] . "'";

			$vrow = $dbcon->query($sql);
			$rel = brp_mysqli_fetch_assoc($vrow);

			if ($rel['stage'] == '1') {
				$stage = 'Approved';
			} else {
				$stage = 'No';
			}
			echo '<section class="panel">
			<div class="panel-body bio-graph-info">
			<h1>PO History</h1>
			<div class="row">
			<div class="bio-row">
			<p><span>Prepared By </span>: ' . $rel["prepared_by"] . '</p>
			</div>
			<div class="bio-row">
			<p><span>Prepared Date </span>: ' . $rel["cdate"] . '</p>
			</div>
			<div class="bio-row">
			<p><span>Modified By </span>: ' . $rel["last_modify_by"] . '</p>
			</div>
			<div class="bio-row">
			<p><span>Modified Date</span>: ' . $rel["mdate"] . '</p>
			</div>

			<div class="bio-row">
			<p><span>Approved By </span>: ' . $rel["approved_by"] . '</p>
			</div>
			<div class="bio-row">
			<p><span>Approved Date </span>: ' . $rel["adate"] . '</p>
			</div>
			<div class="bio-row">
			<p><span>Delivery Date </span>: ' . $rel["delivery_date"] . '</p>
			</div>
			<div class="bio-row">
			<p><span> Stage </span>: ' . $stage . '</p>
			</div>

			</div>
			</div>
			</section>';
		} else if (strtolower($POST['mode']) == "set_vendor_sesion") {
			$vendor_id = $POST['vendor_id'];
			$_SESSION['selected_vendor'] = $vendor_id;
		} else if (strtolower($POST['mode']) == "load_rate") {
			//var_dump($_POST);
			$rate = get_po_card_rate($dbcon, $_POST['product_id'], $_POST['vender_id'], $_POST['unit_id']);

			$row['rate'] = $rate['price'];
			$row['purchasecardtrn_id'] = $rate['purchasecardtrn_id'];
			$row['discount_percentage'] = $rate['discount_percentage'];
			//var_dump($row);
			echo json_encode($row);
		}
		// Dimple Panchal : Start
		else if (strtolower($POST['mode']) == "get_tax_on_total") {
			$arr = get_tax_on_total($dbcon, $POST['total'], $POST['formulaid']);
			echo json_encode($arr);
		}
		// Dimple Panchal : end
		//pathik start
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
		} else if (strtolower($POST['mode']) == "send_purchase_order") {
			get_purchaseorder($dbcon, $_POST['purchaseorder_id']);
			$arr = send_whatsapp_po($dbcon, $_POST['purchaseorder_id']);
			echo $arr;
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
		}

		//maulik Start
		else if (strtolower($POST['mode']) == "po_trn_tbl") {
			$qt_qry = "select trn.*,product.product_name,product.product_type,cat.unit_name,cat_con.unit_name as conv_unit_name,tc.cat_name,product.product_icode,product.drawing_id,dr.drawing_number,(select IFNULL(sum(product_qty),0) as qty from tbl_grn_sub_trn as chtrn where chtrn.status=0 and chtrn.purchaseordertrn_id=trn.purchaseordertrn_id and trn.product_id=chtrn.product_id) as done_qty from tbl_purchaseordertrn as trn
			left join unit_mst as cat on cat.unitid=trn.unit_id
			left join unit_mst as cat_con on cat_con.unitid=trn.conv_unit_id
			left join product_mst as product on product.product_id=trn.product_id 
			left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
			left join tbl_category as tc on product.product_category=tc.cat_id 
			where trn.purchaseordertrn_status=0 and trn.used_status=0 and trn.purchaseorder_id=" . $POST['po_id'];

			$qt_exe = $dbcon->query($qt_qry);
			//Party PO Details Table View
			$str = '';
			$str .= '<div class="form-group">
			<table class="display table table-bordered table-striped">
			<thead>
			<tr>
			<td><strong>#</strong></td>
			<td><strong>Product Type</strong></td>
			<td><strong>Product Name</strong></td>
			<td><strong>Product Category</strong></td>
			<td><strong>HSN Code</strong></td>
			<td><strong>Qty</strong></td>
			<td><strong>Used Qty</strong></td>
			<td><strong>Due Qty</strong></td>
			</tr>
			</thead>
			<tbody>
			';

			$setconf = "select * from tbl_company_configuration where company_id=" . $_SESSION['company_id'];
			$set_conf = brp_mysqli_fetch_assoc($dbcon->query($setconf));
			$purchase_pro_search = $set_conf['purchase_pro_search'];
			$pro_search = explode(",", $purchase_pro_search);

			if (brp_mysqli_num_rows($qt_exe) > 0) {
				while ($rel = brp_mysqli_fetch_assoc($qt_exe)) {
					$cat_name = ($rel['cat_name'] != null) ? $rel['cat_name'] : 'PRIMARY';
					$currency_id = $rel['currency_id'];

					if ($rel['unit_id'] != $rel['conv_unit_id']) {
						$show_qty = $rel['product_qty'] . " " . $rel['unit_name'] . " </br> " . $rel['product_conv_qty'] . " " . $rel['conv_unit_name'];
					} else {
						$show_qty = $rel['product_qty'] . " " . $rel['unit_name'];
					}

					if (in_array('drawing', $pro_search)) {
						$drawing_number = " (" . $rel['drawing_number'] . ")";
					} else {
						$drawing_number = '';
					}
					if (in_array('item', $pro_search)) {
						$item_code = " (" . $rel['product_icode'] . ")";
					} else {
						$item_code = '';
					}
					$done_qty = $rel['done_qty'];
					$due_qty = $rel['product_qty'] - $rel['done_qty'];
					$str .= '
					<tr>
					<td><input type="checkbox" name="po_trn_id[]" value="' . $rel['purchaseordertrn_id'] . '"></td>
					<td>' . get_pro_type_name($rel['product_type']) . '</td>
					<td>' . $rel['product_name'] . ' ' . $drawing_number . ' ' . $item_code . ' ' . (!empty($rel['description']) ? '<br/><strong>Desc.</strong> :' . $rel['description'] : '') . '</td>
					<td>' . $cat_name . '</td>
					<td>' . $rel['product_hsn_code'] . '</td>
					<td>' . number_format($rel['product_qty'], 4, '.', '') . ' ' . $rel['unit_name'] . '</td>
					<td>' . number_format($done_qty, 4, '.', '') . ' ' . $rel['unit_name'] . '</td>
					<td>' . number_format($due_qty, 4, '.', '') . ' ' . $rel['unit_name'] . '</td>
					</tr>';
				}
			} else {
				$str .= '<tr>
				<td colspan="10" style="text-align:center">No Data Yet...!!!</td>
				</tr>';
			}
			$str .= '</tbody></table></div>
			<hr/>
			';

			$qt_rel['po_trn_tbl'] = $str;

			echo json_encode($qt_rel);
		} else if (strtolower($POST['mode']) == "full_poshort_close") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$companyConfiguration = getCompanyConfiguration($dbcon);
			$log_s['short_close_status'] = 2;
			$updateid = update_record("tbl_log_po_short_close", $log_s, "aproove_status in (0,2) and po_id=" . $POST['po_id'], $dbcon);


			$query = "select trn.*,po.purchaseorder_no,(select IFNULL(sum(product_qty),0) as qty from tbl_grn_trn as chtrn where chtrn.grn_trn_status=0 and chtrn.purchaseordertrn_id=trn.purchaseordertrn_id and trn.product_id=chtrn.product_id) as done_qty,(select IFNULL(sum(product_conv_qty),0) as qty from tbl_grn_trn as chtrn where chtrn.grn_trn_status=0 and chtrn.purchaseordertrn_id=trn.purchaseordertrn_id and trn.product_id=chtrn.product_id) as done_conv_qty from tbl_purchaseordertrn as trn 
			left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id
			where trn.used_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po.po_approval_status in (1,3) and po.company_id=" . $_SESSION['company_id'] . " and trn.purchaseorder_id=" . $POST['po_id'];

			$que_e = $dbcon->query($query);

			while ($row = mysqli_fetch_array($que_e)) {
				$due_qty = $row['product_qty'] - $row['done_qty'];
				$info['short_close_qty'] = $due_qty;

				$info['short_close_reason'] = $_POST['close_reson'];
				$info['shortclose_status'] = 1;
				$info['cdate'] = date("Y-m-d H:i:s");
				$info['user_id'] = $_SESSION['user_id'];
				$info['company_id'] = $_SESSION['company_id'];
				$updateid = update_record("tbl_purchaseordertrn", $info, "purchaseordertrn_id=" . $row['purchaseordertrn_id'], $dbcon);

				$log_entry['po_no'] = $row['purchaseorder_no'];
				$log_entry['po_id'] = $row['purchaseorder_id'];
				$log_entry['po_trn_id'] = $row['purchaseordertrn_id'];
				$log_entry['product_id'] = $row['product_id'];
				$log_entry['short_close_qty'] = $due_qty;
				$log_entry['unit_id'] = $row['unit_id'];
				$log_entry['short_close_reason'] = $_POST['close_reson'];
				$log_entry['date'] = date("Y-m-d");
				$log_entry['cdate'] = date("Y-m-d H:i:s");
				$log_entry['user_id'] = $_SESSION['user_id'];
				$log_entry['company_id'] = $_SESSION['company_id'];
				$log_entry['branch_id'] = $row['branch_id'];

				$inserid = add_record("tbl_log_po_short_close", $log_entry, $dbcon);

				if ($companyConfiguration['automatic_shortclose_approval_po'] == 1) {
					get_automatic_po_shortclose_approval($dbcon, $row['purchaseorder_id'], $row['purchaseordertrn_id']);
				}
			}
		} else if (strtolower($POST['mode']) == "manual_poshort_close") {
			$po_trn_id = implode(",", $POST['po_trn_id']);
			$companyConfiguration = getCompanyConfiguration($dbcon);
			$log_s['short_close_status'] = 2;
			$updateid = update_record("tbl_log_po_short_close", $log_s, "po_trn_id=" . $po_trn_id, $dbcon);

			$query = "select trn.*,po.purchaseorder_no,(select IFNULL(sum(product_qty),0) as qty from tbl_grn_trn as chtrn where chtrn.grn_trn_status=0 and chtrn.purchaseordertrn_id=trn.purchaseordertrn_id and trn.product_id=chtrn.product_id) as done_qty,(select IFNULL(sum(product_conv_qty),0) as qty from tbl_grn_trn as chtrn where chtrn.grn_trn_status=0 and chtrn.purchaseordertrn_id=trn.purchaseordertrn_id and trn.product_id=chtrn.product_id) as done_conv_qty from tbl_purchaseordertrn as trn 
			left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id

			where trn.used_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po.po_approval_status in (1,3) and po.company_id=" . $_SESSION['company_id'] . " and trn.purchaseorder_id=" . $POST['po_id'] . " and trn.purchaseordertrn_id in (" . $po_trn_id . ")";


			$que_e = $dbcon->query($query);

			while ($row = mysqli_fetch_array($que_e)) {
				$due_qty = $row['product_qty'] - $row['done_qty'];
				$info['short_close_qty'] = $due_qty;
				$info['short_close_reason'] = $_POST['close_reson'];
				$info['shortclose_status'] = 1;
				$info['cdate'] = date("Y-m-d H:i:s");
				$info['user_id'] = $_SESSION['user_id'];
				$info['company_id'] = $_SESSION['company_id'];
				$updateid = update_record("tbl_purchaseordertrn", $info, "purchaseordertrn_id=" . $row['purchaseordertrn_id'], $dbcon);

				$log_entry['po_no'] = $row['purchaseorder_no'];
				$log_entry['po_id'] = $row['purchaseorder_id'];
				$log_entry['po_trn_id'] = $row['purchaseordertrn_id'];
				$log_entry['product_id'] = $row['product_id'];
				$log_entry['short_close_qty'] = $due_qty;
				$log_entry['unit_id'] = $row['unit_id'];
				$log_entry['short_close_reason'] = $_POST['close_reson'];
				$log_entry['date'] = date("Y-m-d");
				$log_entry['cdate'] = date("Y-m-d H:i:s");
				$log_entry['user_id'] = $_SESSION['user_id'];
				$log_entry['company_id'] = $_SESSION['company_id'];
				$log_entry['branch_id'] = $row['branch_id'];

				$inserid = add_record("tbl_log_po_short_close", $log_entry, $dbcon);

				/*if($companyConfiguration['automatic_shortclose_approval_po']==1){
					get_automatic_po_shortclose_approval($dbcon,$row['purchaseorder_id'],$row['purchaseordertrn_id']);
				}*/
			}
		} else if (strtolower($POST['mode']) == "po_close_reason") {
			$query = "select DISTINCT short_close_reason  from tbl_purchaseordertrn where purchaseorder_id=" . $POST['po_id'];

			$que_e = $dbcon->query($query);
			$str = '';
			$str .= '<div class="form-group">
			<table class="display table table-bordered table-striped">
			<thead>
			<tr>
			<td><strong>Sr.no</strong></td>
			<td><strong>Short Close Reason</strong></td>
			</tr>
			</thead>
			<tbody>';
			if (mysqli_num_rows($que_e) > 0) {
				$i = 1;
				while ($row = mysqli_fetch_array($que_e)) {
					$str .= '<tr>
					<td><strong>' . $i . '</strong></td>
					<td><strong>' . $row['short_close_reason'] . '</strong></td>
					</tr>';
					$i++;
				}
			} else {
				$str .= '<tr>
				<td style="text-align:center" colspan="2"></td>
				</tr>';
			}
			$str .= '</tbody></table></div>';
			$qt_rel['f_po_close_reason'] = $str;

			echo json_encode($qt_rel);
		} else if (strtolower($POST['mode']) == "m_po_close_reason") {
			$query = "select DISTINCT short_close_reason  from tbl_purchaseordertrn where purchaseorder_id=" . $POST['po_id'];

			$que_e = $dbcon->query($query);
			$str = '';
			$str .= '<div class="form-group">
			<table class="display table table-bordered table-striped">
			<thead>
			<tr>
			<td><strong>Sr.no</strong></td>
			<td><strong>Short Close Reason</strong></td>
			</tr>
			</thead>
			<tbody>';
			if (mysqli_num_rows($que_e) > 0) {
				$i = 1;
				while ($row = mysqli_fetch_array($que_e)) {
					$str .= '<tr>
					<td><strong>' . $i . '</strong></td>
					<td><strong>' . $row['short_close_reason'] . '</strong></td>
					</tr>';
					$i++;
				}
			} else {
				$str .= '<tr>
				<td style="text-align:center" colspan="2"></td>
				</tr>';
			}
			$str .= '</tbody></table></div>';
			$qt_rel['m_po_close_reason'] = $str;

			echo json_encode($qt_rel);
		}

		//maulik end
		//pathik end
		//Maulik Start
		else if (strtolower($POST['mode']) == "vender_detail") {
			$qt_qry = "select * from tbl_ledger where l_id=" . $POST['vender_id'];
			//var_dump($qt_qry);
			$qt_rel = brp_mysqli_fetch_assoc($dbcon->query($qt_qry));

			if ($POST['product_id'] != "") {
				$pr_qry = "select product_name from product_mst where product_id=" . $POST['product_id'];
				$pr_rel = brp_mysqli_fetch_assoc($dbcon->query($pr_qry));
			}

			//var_dump($pr_qry);
			//Party PO Details Table View
			$str = '';
			$str .= '<div class="form-group">
			<table class="display table table-bordered table-striped">
			<tr>
			<td colspan="2"><strong>Company Name : </strong> ' . $qt_rel['company_name'] . '</td>
			</tr>
			<tr>
			<td><strong>Mobile : </strong> ' . $qt_rel['cust_mobile'] . '</td>
			<td><strong>Email : </strong> ' . $qt_rel['cust_email'] . '</td>
			</tr>
			<tr>
			<td colspan="2"><strong>Address:</strong> ' . $qt_rel['m_address'] . '</td>
			</tr>';
			$str .= '</table></div>
			<hr/>
			';

			$qt_rel['vender_detail'] = $str;
			$qt_rel['vender_name'] = $qt_rel['l_name'];
			$qt_rel['product_name'] = $pr_rel['product_name'];
			echo json_encode($qt_rel);
		} else if (strtolower($POST['mode']) == "price_detail") {
			$price_list = get_pricelist_po($dbcon, $POST['vender_id'], "");

			$qt_rel['product_detail'] = $price_list;
			echo json_encode($qt_rel);
		} else if (strtolower($POST['mode']) == "product_price_detail") {
			$price_list = get_pricelist_po($dbcon, $POST['vender_id'], $POST['product_id']);

			$qt_rel['product_detail'] = $price_list;
			echo json_encode($qt_rel);
		}
		//Maulik End
		else if (strtolower($POST['mode']) == "delivary_date_model_open") {
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
				$qry = "SELECT * FROM `tbl_purchaseorder_delivery_date` WHERE po_delivery_date_status=0 and purchaseordertrn_id=" . $POST['trn_id'] . " order by po_delivery_date_id";
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
		} else if (strtolower($POST['mode']) == "get_revise_po_no") {
			$get_rev_cnt = "select count(purchaseorder_id) as ttl_cnt,(select purchaseorder_no from tbl_purchaseorder where purchaseorder_id=" . $POST['start_purchaseorder_id'] . ") as qt_no from tbl_purchaseorder where purchase_status=0 and start_purchaseorder_id=" . $POST['start_purchaseorder_id'];
			$rev_cnt = mysqli_fetch_assoc($dbcon->query($get_rev_cnt));
			$row['purchaseorder_no'] = $rev_cnt['qt_no'] . "/R-" . $rev_cnt['ttl_cnt'];

			echo json_encode($row);
		} else if (strtolower($POST['mode']) == "copy_prev_purchase_trn") {
			$del['purchaseordertrn_status'] = 2;
			update_record("tbl_purchaseordertrn", $del, " purchaseordertrn_status=3 and user_id=" . $_SESSION['user_id'], $dbcon);

			$prev_purchaseorder_id = $_POST['prev_purchaseorder_id'];
			/*$del_terms['po_terms_trn_status']	=2;
			update_record("tbl_purchaseorder_terms_trn",$del_terms," po_terms_trn_status=3 and user_id=".$_SESSION['user_id'] ,$dbcon);*/

			$sql = $dbcon->query("SELECT trn.*,po.*,trn.po_ref_id as po_ref,trn.currency_total as cutotal FROM `tbl_purchaseordertrn` as trn 
				left join tbl_purchaseorder as po on po.purchaseorder_id = trn.purchaseorder_id
				WHERE trn.purchaseordertrn_status=0 and trn.purchaseorder_id=" . $prev_purchaseorder_id);

			while ($row = brp_mysqli_fetch_assoc($sql)) {

				$company_state = get_company_data($dbcon, $_SESSION['company_id']);
				//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);


				if ($row['sales_type'] == 1) {
					$sale_gst = get_tax_cat_by_hsn($dbcon, trim($_POST['product_hsn_code']));
				} else if ($row['sales_type'] == 2) {
					$sale_gst['tax_gst'] = 0.1;
					$sale_gst['tax_cat_id'] = 0;
				} else if ($row['sales_type'] == 3) {
					$sale_gst['tax_gst'] = 0;
					$sale_gst['tax_cat_id'] = 0;
				} else if ($row['sales_type'] == 4) {
					$sale_gst['tax_gst'] = 5;
					$sale_gst['tax_cat_id'] = 0;
				} else if ($row['sales_type'] == 5) {
					$sale_gst['tax_gst'] = 0;
					$sale_gst['tax_cat_id'] = 0;
				} else if ($row['sales_type'] == 6) {
					$sale_gst['tax_gst'] = 12;
					$sale_gst['tax_cat_id'] = 0;
				} else if ($row['sales_type'] == 7) {
					$sale_gst['tax_gst'] = 18;
					$sale_gst['tax_cat_id'] = 0;
				} else if ($row['sales_type'] == 8) {
					$sale_gst['tax_gst'] = 24;
					$sale_gst['tax_cat_id'] = 0;
				}

				$custLedgerDetails = get_cust_data_arr($dbcon, $row['vender_id']);

				$ven_s = "select stateid from tbl_ledger where l_id=" . $row['vender_id'];
				$ves = $dbcon->query($ven_s);
				$vers = mysqli_fetch_array($ves);

				$cgst_tax_rate = 0;
				$cgst_tax_rate_conv = 0;
				$sgst_tax_rate = 0;
				$sgst_tax_rate_conv = 0;
				$igst_tax_rate = 0;
				$igst_tax_rate_conv = 0;
				if (($company_state['stateid'] == $vers['stateid']) && ($custLedgerDetails['enable_sez'] == 0)) {
					$gst = $sale_gst['tax_gst'] / 2;
					$cgst_tax_per = $gst;
					$cgst_tax_rate = ($gst * $row['product_amount']) / 100;
					$cgst_tax_rate_conv = ($gst * $row['product_currency_rate']) / 100;
					$sgst_tax_per = $gst;
					$sgst_tax_rate = ($gst * $row['product_amount']) / 100;
					$sgst_tax_rate_conv = ($gst * $row['product_currency_rate']) / 100;
				} else {
					$igst_tax_per = $sale_gst['tax_gst'];
					$igst_tax_rate = ($sale_gst['tax_gst'] * $row['product_amount']) / 100;
					$igst_tax_rate_conv = ($sale_gst['tax_gst'] * $row['product_currency_rate']) / 100;
				}

				/*if(isset($row['currency_enable']) && $row['currency_enable']==1){*/
				$curncy_trn['currency_id'] = $row['currency_id'];
				$curncy_trn['currency_rate'] = $row['currency_rate'];
				/*}else{
					$basecurrency = getbasecurrency($dbcon);
					$curncy_trn['currency_id'] = $basecurrency['currencyid'];
					$curncy_trn['currency_rate'] = 1;
				}*/

				$info1['product_type'] = $row['product_type'];
				$info1['product_id'] = $row['product_id'];
				$info1['cat_id'] = $row['cat_id'];
				$info1['po_ref_id'] = $row['po_ref'];
				$info1['temptrn_ref_id'] = $row['temptrn_ref_id'];
				$info1['description'] = $row['product_des'];
				$info1['product_des'] = $row['product_des'];
				$info1['pro_spe'] = $row['pro_spe'];
				$info1['product_hsn_code'] = $row['product_hsn_code'];
				$info1['product_qty'] = $row['product_qty'];
				$info1['product_conv_qty'] = $row['product_conv_qty'];
				$info1['sqr_ft'] = $row['sqr_ft'];
				$info1['unit_id'] = $row['unit_id'];
				$info1['conv_unit_id'] = $row['conv_unit_id'];
				$info1['rate_unit'] = $row['rate_unit'];
				$info1['discount_per'] = $row['discount_per'];
				$info1['formulaid'] = $row['formulaid'];
				$info1['sel_tax'] = $row['sel_tax'];
				$info1['formula_tax_id'] = $row['formula_tax_id'];
				$info1['purchasecardtrn_id'] = $row['purchasecardtrn_id'];
				$info1['user_id'] = $_SESSION['user_id'];
				$info1['company_id'] = $_SESSION['company_id'];

				//comment by maulik 
				/* $info1['currency_id']			= $row['currency_id'];
				$info1['conversion_rate']		= $row['conversion_rate'];
				$info1['product_currency_rate']	= $row['product_currency_rate'];
				$info1['product_currency_amount']= $row['product_currency_amount'];
				$info1['product_currency_amount_tax']= $row['product_currency_amount_tax'];
				$info1['currency_total']		= $row['currency_total']; */

				//finance texasion update
				$info1['cgst_tax_per'] = isset($cgst_tax_per) ? $cgst_tax_per : 0;
				$info1['sgst_tax_per'] = isset($sgst_tax_per) ? $sgst_tax_per : 0;
				$info1['igst_tax_per'] = isset($igst_tax_per) ? $igst_tax_per : 0;

				$info1['cgst_tax_rate'] = isset($cgst_tax_rate) ? $cgst_tax_rate : 0;
				$info1['sgst_tax_rate'] = isset($sgst_tax_rate) ? $sgst_tax_rate : 0;
				$info1['igst_tax_rate'] = isset($igst_tax_rate) ? $igst_tax_rate : 0;

				$info1['sgst_tax_rate_conv'] = isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
				$info1['cgst_tax_rate_conv'] = isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
				$info1['igst_tax_rate_conv'] = isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;

				$info1['product_amount_tax'] = $row['product_amount_tax'];
				$info1['product_rate'] = $row['product_rate'];
				$info1['product_discount'] = $row['product_discount'];
				$info1['product_amount'] = $row['product_amount'];
				$info1['total'] = $row['total'];

				$info1['product_currency_rate'] = $row['product_currency_rate'];
				$info1['product_currency_amount'] = $row['product_currency_amount'];
				$info1['product_currency_amount_tax'] = $row['product_currency_amount_tax'];
				$info1['product_discount_conv'] = $row['product_discount_conv'];
				$info1['currency_total'] = $row['cutotal'];


				$info1['product_tax_cat'] = $sale_gst['tax_cat_id'];


				$info1['prev_purchaseordertrn_id'] = $row['purchaseordertrn_id'];
				$info1['purchaseordertrn_status'] = 3;
				/*var_dump($info1);*/
				$table = 'tbl_purchaseordertrn';
				$tableid = 'purchaseordertrn_id';
				$inserid = add_record($table, $info1, $dbcon, $row['branch_id']);

				if (($cgst_tax_per != 0) && ($cgst_tax_rate != 0)) {
					$cl_id = get_ledger_by_name($dbcon, 'CGST');
					$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $cgst_tax_per, $cgst_tax_rate, $inserid, "tbl_purchaseordertrn", $row['product_id'], 3, 0, $row['branch_id'], $row['currency_id'], $row['currency_rate'], $cgst_tax_rate_conv);
				}
				if (($sgst_tax_per != 0) && ($sgst_tax_rate != 0)) {
					$cl_id = get_ledger_by_name($dbcon, 'SGST');
					$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $sgst_tax_per, $sgst_tax_rate, $inserid, "tbl_purchaseordertrn", $row['product_id'], 3, 0, $row['branch_id'], $row['currency_id'], $row['currency_rate'], $sgst_tax_rate_conv);
				}
				if (($igst_tax_per != 0) && ($igst_tax_rate != 0)) {
					$cl_id = get_ledger_by_name($dbcon, 'IGST');
					$insert_tax = add_tax_transaction_record($dbcon, $cl_id['l_id'], $igst_tax_per, $igst_tax_rate, $inserid, "tbl_purchaseordertrn", $row['product_id'], 3, 0, $row['branch_id'], $row['currency_id'], $row['currency_rate'], $igst_tax_rate_conv);
				}

				// check for the addiotional tax on product Start -- Maulik

				$count_add_tax = get_check_addition_tax($dbcon, $sale_gst['tax_cat_id'], $row['taxable_value'], $inserid, $row['product_id'], 0, $row['branch_id'], 'tbl_purchaseordertrn', $row['currency_id'], $row['currency_rate'], $row['product_currency_amount']);

				// $dbcon->query("UPDATE `tbl_purchaseorder_delivery_date` SET `purchaseordertrn_id`		='".$inserid."' WHERE `po_delivery_date_status` = 0 AND `purchaseordertrn_id` = '".$row['purchaseordertrn_id']."'");

				$purchase_delivery_id = $dbcon->query("INSERT INTO `tbl_purchaseorder_delivery_date`(`purchaseordertrn_id`, `delivery_date`, `product_qty`, `used_qty`, `grn_status`, `unit_id`, `po_delivery_date_status`, `user_id`, `company_id`, `branch_id`) SELECT  '" . $inserid . "', `delivery_date`, `product_qty`, `used_qty`, `grn_status`, `unit_id`, `po_delivery_date_status`, `user_id`, `company_id`, `branch_id` FROM `tbl_purchaseorder_delivery_date` WHERE po_delivery_date_status=0 AND purchaseordertrn_id='" . $row['purchaseordertrn_id'] . "'");
			}
			echo $prev_purchaseorder_id;
		} else if (strtolower($POST['mode']) == "load_process_out_side") {
			$eid = "";
			if ($POST['proc'] != "") {
				$eid = $POST['proc'];
			}
			$prod_id = $POST['prod_id'];
			$str = load_process_out_side($dbcon, $prod_id, $eid);

			$qt_rel['process_list'] = $str;
			echo json_encode($qt_rel);
		} else if (strtolower($POST['mode']) == "get_tax_details_table") {

			$invoice_id = 0;
			$where = "";
			$custLedgerDetails = get_cust_data_arr($dbcon, $POST['cust_id']);
			if ($_POST['viewmode'] != "Revise") {
				if ($POST['invoice_id']) {
					$invoice_id = $POST['invoice_id'];
				} else {
					$where .= "and user_id=" . $_SESSION['user_id'];
				}
			} else {
				$where .= "and user_id=" . $_SESSION['user_id'];
			}
			$resp = '';
			$query = "SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_currency_amount) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_purchaseordertrn` where purchaseorder_id='$invoice_id' and purchaseordertrn_status!=2 " . $where . " group by cgst_tax_per,sgst_tax_per,igst_tax_per";
			//var_dump($query);
			$rs_prel = $dbcon->query($query);
			$rs_prel_fetch = brp_mysqli_fetch_assoc($dbcon->query("SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_currency_amount) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_purchaseordertrn` where purchaseorder_id='$invoice_id' and purchaseordertrn_status!=2 " . $where));
			$rs_prel_num_rows = mysqli_num_rows($rs_prel);
			//print_r($rs_prel_fetch);exit;

			$resp = '';
			$resp .= '<table class="table table-bordered">
			
			<tr>
			<th class="text-center">#</th>
			<th  class="text-center">Total Tax </th>
			<th  class="text-center">Taxable Amount <span class="currency_icon"></span></th>
			<th  class="text-center">Tax Amount <span class="currency_icon"></span></th>';
			if (($rs_prel_fetch['cgst_rate'] != 0) || ($rs_prel_fetch['sgst_rate'] != 0)) {
				$resp .= '<th  class="text-center">CGST </th>
				<th  class="text-center">SGST</th>';
			}
			if (($rs_prel_fetch['igst_rate'] != 0)) {
				$resp .= '<th  class="text-center">IGST</th>';
			}


			$resp .= '</tr>';

			if ($rs_prel_num_rows > 0) {
				$taxRate = brp_mysqli_fetch_all($rs_prel);
				//print_r($taxRate);exit;
				foreach ($taxRate as $taxdetail) {

					$gst_tax_per = ($taxdetail['cgst_tax_per'] != 0 || $taxdetail['sgst_tax_per'] != 0) ? ($taxdetail['cgst_tax_per'] + $taxdetail['sgst_tax_per']) : $taxdetail['igst_tax_per'];
					$gst_tax_rate = ($taxdetail['cgst_rate'] != 0 || $taxdetail['sgst_rate'] != 0) ? ($taxdetail['cgst_rate'] + $taxdetail['sgst_rate']) : $taxdetail['igst_rate'];

					$gst_tax_rate_conv = ($taxdetail['cgst_rate'] != 0 || $taxdetail['sgst_rate'] != 0) ? ($taxdetail['cgst_rate_conv'] + $taxdetail['sgst_rate_conv']) : $taxdetail['igst_rate_conv'];

					if ($taxdetail['cgst_tax_per'] != 0 || $taxdetail['sgst_tax_per'] != 0) {
						$resp .= '<tr>
						<th class="text-center">1</th>
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
						if (!empty($POST['addontax1']) && $cntloop == 0) {
							foreach ($POST['addontax1'] as $addtax) {
								$cnt++;
								$exp_addtax = explode("-", $addtax);
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
						<th class="text-center">1</th>
						<th class="text-center">' . $gst_tax_per . '%' . '</th>
						<th class="text-center">';

						if ($POST['currency_id'] == $_SESSION['currency_id']) {
							$resp .= $taxdetail['product_amount'] . '</th>
							<th class="text-center">' . $gst_tax_rate;
						} else {
							$resp .= $taxdetail['product_amount_conv'] . '</th>
							<th class="text-center">' . $gst_tax_rate_conv;
						}

						$resp .= '</th><th class="text-center">' . ($taxdetail['igst_tax_per']) . '%' . '</th>
						</tr>';
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
				}
			}

			$resp .= '</table>';

			$row['resp'] = $resp;

			echo json_encode($row);
		} else if (strtolower($POST['mode']) == "get_grossbalance") {
			$arr = get_grossbalance($dbcon, $POST['cust_id']);
			echo $arr;
		} else if (strtolower($POST['mode']) == "get_invoice_total_tax") {
			//var_dump($_POST['viewmode']);
			$company_state = get_company_data($dbcon, $_SESSION['company_id']);
			$invoice_id = 0;
			$where = "";
			if ($_POST['viewmode'] != "Revise") {
				if ($POST['invoice_id']) {
					$invoice_id = $POST['invoice_id'];
				} else {
					$where .= "and user_id=" . $_SESSION['user_id'];
				}
			} else {
				$where .= "and user_id=" . $_SESSION['user_id'];
			}
			//var_dump($where);
			$resp = '';
			$query = "SELECT sum(cgst_tax_rate) as cgst_rate,sum(sgst_tax_rate) as sgst_rate,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_currency_amount) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_purchaseordertrn` where purchaseorder_id='$invoice_id' and purchaseordertrn_status!=2 " . $where;

			$rs_prel = brp_mysqli_fetch_assoc($dbcon->query($query));

			$row['isTcs'] = "0";
			$getCompanyConfig = getCompanyConfiguration($dbcon);
			$custLedgerDetails = get_cust_data_arr($dbcon, $POST['cust_id']);
			$get_bill_sundry = get_bill_sundry_ledger($dbcon, 1);

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
					<input id="' . $billsundry['l_name'] . '" name="bill_sundry_tax[' . $billsundry['l_id'] . ']" type="number" class="form-control gst" title="' . $billsundry['l_name'] . '"  value="' . (($POST['currency_id'] == $company_state['currency_id']) ? round($gstValue + $addontax, 2) : round($gstValue_conv + $addontax, 2)) . '" placeholder="' . $billsundry['l_name'] . '" readonly >
					</div>
					</div>';
				}
				if (($rs_prel['igst_rate'] != 0) && $billsundry['l_name'] == 'IGST') {
					if (!empty($POST['addontax1'])) {
						$addontax = $POST['addontax1'];
					}
					$resp .= '<div class="form-group">
					<label class="col-md-5 control-label">' . $billsundry['l_name'] . ' <span class="currency_icon"></span></label>
					<div class="col-md-5 col-xs-11">
					<input id="' . $billsundry['l_name'] . '" name="bill_sundry_tax[' . $billsundry['l_id'] . ']" type="number" class="form-control gst" title="' . $billsundry['l_name'] . '"  value="' . (($POST['currency_id'] == $company_state['currency_id']) ? ($rs_prel['igst_rate'] + $addontax) : ($rs_prel['igst_rate_conv'] + $addontax)) . '" placeholder="' . $billsundry['l_name'] . '" readonly >
					</div>
					</div>';
				}

				/*if(($billsundry['l_name'] == 'TCS') && ($getCompanyConfig['enable_tcs_reporting'] == 1) && ($custLedgerDetails['enable_tcs']==1) && ($POST['gross'] > $getCompanyConfig['gross_balance_limit'])){
					$row['isTcs']="1";
					$total_tcs_calculate = $rs_prel['product_amount']+$gstValue+$rs_prel['igst_rate'];
					$resp.='<div class="form-group">
					<label class="col-md-5 control-label">'.$billsundry['l_name'].'</label>
					<div class="col-md-5 col-xs-11">
					<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.round((($total_tcs_calculate*$billsundry['tax_value'])/100),2).'" placeholder="'.$billsundry['l_name'].'" readonly >
					<input type="hidden" name="tcs_per" id="tcs_per" value="'.$billsundry['tax_value'].'" >
					</div>
					</div>';
				}*/
			}

			$qry_add = $dbcon->query("select sum((tc.tax_per*trn.product_amount)/100) as add_sum , trn.*,l.l_name,l.l_id,t.tax_cat_id from tbl_purchaseordertrn as trn left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat 
				left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
				left join tbl_ledger as l on l.l_id=tc.tax_id
				where tc.tax_additional='1' and trn.purchaseorder_id='$invoice_id' and trn.purchaseordertrn_status!=2 and tc.isdelete='0' and trn.user_id =" . $_SESSION['user_id'] . " group by tc.tax_id 
				");
			while ($row1 = brp_mysqli_fetch_array($qry_add)) {
				//$tax_rate = ($row1['tax_per']*$row1['product_amount'])/100;
				$resp .= '<div class="form-group">
				<label class="col-md-5 control-label">' . $row1['l_name'] . ' <span class="currency_icon"></span></label>
				<div class="col-md-5 col-xs-11">
				<input id="' . $row1['l_name'] . '" name="bill_sundry_tax[' . $row1['l_id'] . ']" type="number" class="form-control gst" title="' . $row1['l_name'] . '"  value="' . (($POST['currency_id'] == $company_state['currency_id']) ? $row1['add_sum'] : $row1['add_sum_conv']) . '" placeholder="' . $billsundry['l_name'] . '" readonly >
				</div>
				</div>';
			}

			$row['resp'] = $resp;

			echo json_encode($row);
		} else if (strtolower($_POST['mode']) == "update_total") {
			// Ensure required fields exist and are valid
			if (
				!isset($_POST['invoice_id'], $_POST['currency_id'], $_POST['currency_rate'], $_POST['g_total']) ||
				!is_numeric($_POST['invoice_id']) ||
				!is_numeric($_POST['currency_id']) ||
				!is_numeric($_POST['currency_rate']) ||
				!is_numeric($_POST['g_total'])
			) {
				echo json_encode(["error" => "Invalid or missing input data"]);
				exit;
			}

			// Initialize $bill_sundry_tax safely
			$bill_sundry_tax = [];
			if (isset($_POST['bill_sundry_tax']) && isset($_POST['bill_sundry_tax1'])) {
				$bill_sundry_tax = array_combine($_POST['bill_sundry_tax'], $_POST['bill_sundry_tax1']);
			}

			// Update invoice totals
			if ($_POST['invoice_id'] > 0) {
				$update_invoice = [];

				if ($_POST['currency_id'] == $_SESSION['currency_id']) {
					$update_invoice['g_total'] = $_POST['g_total'];
					$update_invoice['g_total_conv'] = $_POST['g_total'] * $_POST['currency_rate'];
				} else {
					$update_invoice['g_total'] = $_POST['g_total'] * $_POST['currency_rate'];
					$update_invoice['g_total_conv'] = $_POST['g_total'];
				}

				// Update the invoice record
				$update_result = update_record(
					"tbl_purchaseorder",
					$update_invoice,
					"purchaseorder_id=" . (int) $_POST['invoice_id'],
					$dbcon
				);

				if (!$update_result) {
					echo json_encode(["error" => "Failed to update invoice"]);
					exit;
				}

				// Update bill sundry transactions
				foreach ($bill_sundry_tax as $bill_sundry_tax_id => $bill_sundry_tax_amount) {
					$info_sundry_tax = [];

					if ($_POST['currency_id'] == $_SESSION['currency_id']) {
						$info_sundry_tax['sundry_amount'] = $bill_sundry_tax_amount;
						$info_sundry_tax['sundry_amount_conv'] = $bill_sundry_tax_amount * $_POST['currency_rate'];
					} else {
						$info_sundry_tax['sundry_amount'] = $bill_sundry_tax_amount * $_POST['currency_rate'];
						$info_sundry_tax['sundry_amount_conv'] = $bill_sundry_tax_amount;
					}

					$info_sundry_tax['cdate'] = date("Y-m-d H:i:s");
					$info_sundry_tax['user_id'] = $_SESSION['user_id'];
					$info_sundry_tax['company_id'] = $_SESSION['company_id'];

					// Update the sundry transaction record
					$sundry_update_result = update_record(
						"tbl_bill_sundry_transaction",
						$info_sundry_tax,
						"sundry_ledger_id=" . (int) $bill_sundry_tax_id .
						" AND sundry_voucher_table='tbl_purchaseorder' " .
						"AND sundry_voucher_id='" . (int) $_POST['invoice_id'] . "'",
						$dbcon
					);

					if (!$sundry_update_result) {
						error_log("Failed to update sundry transaction for ID: $bill_sundry_tax_id");
					}
				}
			} else {
				echo json_encode(["error" => "Invalid invoice ID"]);
				exit;
			}

			echo json_encode(["success" => true]);
		} else if (strtolower($POST['mode']) == "get_ledger_details") {
			$ledger_id = $POST['ledger_id'];

			$row = get_ledger_details($dbcon, $ledger_id);

			$res = "select cust_contact_person_name,cust_contact_person_id from tbl_cust_contact_person as trn 
					where  cust_contact_person_status=0 and cust_id=" . $POST['ledger_id'];
			$str = "";
			$qry_add = $dbcon->query($res);
			$str .= "<option value='' >select Kind Attn.</option>";
			while ($row1 = brp_mysqli_fetch_array($qry_add)) {
				$str .= "<option value='" . $row1['cust_contact_person_id'] . "' >" . $row1['cust_contact_person_name'] . " </option>";
			}
			$row['c_person'] = $str;
			echo json_encode($row);
		} else if (strtolower($POST['mode']) == "get_bill_sundry_details") {
			$invoice_id = $POST['invoice_id'];
			//echo '<pre>'; print_r($POST);exit;
			$q = $dbcon->query("SELECT * from tbl_ledger_bill_sundry where isdelete=0 and  sundry_ledger_id=" . $POST['sundry_ledger_id'] . " and company_id = " . $_SESSION['company_id'] . " ");

			$resp = $q->fetch_assoc();

			$q_tax = $dbcon->query("select tax_gst from tbl_tax_category where tax_cat_id=" . $resp['sundry_gst'] . " ");
			$resp_tax = $q_tax->fetch_assoc();

			$basic_total = $POST['basic_amount'];
			$netamount = $POST['netamount'];
			$taxableamount = $POST['taxableamount'];

			$default_amount = $POST['default_amount'];


			if (($resp['apply_gst'] == 2) && (!empty($resp['sundry_gst']))) {
				if ($resp['sundry_amount_of'] == 2) {

					if ($resp['sundry_calculate_on'] == 2) {
						$taxvl = ($resp_tax['tax_gst'] * (($basic_total * $default_amount) / 100)) / 100;
					} else {
						$taxvl = ($resp_tax['tax_gst'] * (($netamount * $default_amount) / 100)) / 100;
					}
				} else {
					if ($resp['sundry_calculate_on'] == 2) {
						$taxvl = ($resp_tax['tax_gst'] * $POST['default_amount']) / 100;
					} else {
						$taxvl = ($resp_tax['tax_gst'] * $POST['netamount']) / 100;
					}
				}

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
						$pervalue = $default_amount;
					} else if ($resp['sundry_calculate_on'] == 2) {
						$finalNetAmount = $basic_total + $default_amount;
						$pervalue = $default_amount;
					} else if ($resp['sundry_calculate_on'] == 3) {
						$finalNetAmount = $basic_total + $default_amount;
						$pervalue = $default_amount;
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
						$pervalue = -$default_amount;
					} else if ($resp['sundry_calculate_on'] == 2) {
						$finalNetAmount = $basic_total - $default_amount;
						$pervalue = -$default_amount;
					} else if ($resp['sundry_calculate_on'] == 3) {
						//$finalNetAmount = (($basic_total + $taxableamount) - $default_amount) + $totalsundryexist;
						$finalNetAmount = $basic_total - $default_amount;
						$pervalue = -$default_amount;
					}
					//$finalNetAmount = $netamount - $default_amount;
				} else if ($resp['sundry_amount_of'] == 2) {
					if ($resp['sundry_calculate_on'] == 1) {
						$finalNetAmount = $netamount - (($netamount * $default_amount) / 100);
						$pervalue = -($netamount * $default_amount) / 100;
					} else if ($resp['sundry_calculate_on'] == 2) {
						//$finalNetAmount = (($basic_total + $taxableamount) - (($basic_total * $default_amount)/100)) + $totalsundryexist;
						$finalNetAmount = $basic_total - (($basic_total * $default_amount) / 100);
						$pervalue = -($basic_total * $default_amount) / 100;
					} else if ($resp['sundry_calculate_on'] == 3) {
						//$finalNetAmount = (($basic_total + $taxableamount) + ((($basic_total + $taxableamount) * $default_amount)/100)) + $totalsundryexist;
						$finalNetAmount = $basic_total - (($basic_total * $default_amount) / 100);
						$pervalue = -($basic_total * $default_amount) / 100;
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
				$info_sundry_addon['sundry_voucher_type'] = PO_VOUCHER;
				$info_sundry_addon['sundry_voucher_table'] = 'tbl_purchaseorder';
				$info_sundry_addon['cdate'] = date("Y-m-d H:i:s");
				$info_sundry_addon['user_id'] = $_SESSION['user_id'];
				$info_sundry_addon['company_id'] = $_SESSION['company_id'];

				//print_r(array_merge($info_sundry_addon,$curncy_trn));
				$info_sundry_addon['sundry_gst_per'] = $taxgst;
				//$info_sundry_addon['sundry_gst_amount']	= $taxvl;

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
					$info_sundry_addon['sundry_gst_amount'] = $taxvl;
					$info_sundry_addon['sundry_amount_conv'] = $pervalue * $POST['currency_rate'];
					$info_sundry_addon['sundry_gst_amount_conv'] = $taxvl * $POST['currency_rate'];
				} else {
					$info_sundry_addon['sundry_amount'] = $pervalue * $POST['currency_rate'];
					$info_sundry_addon['sundry_gst_amount'] = $taxvl * $POST['currency_rate'];
					$info_sundry_addon['sundry_amount_conv'] = $pervalue;
					$info_sundry_addon['sundry_gst_amount_conv'] = $taxvl;
				}

				$sundry_addon_insert = add_record('tbl_bill_sundry_transaction', array_merge($info_sundry_addon, $curncy_trn), $dbcon);

				/* //general bbok entry 

				$info_general_addon['ledger_id']=$POST['sundry_ledger_id'];
				$info_general_addon['amount']=$pervalue;
				$info_general_addon['table_id']=$sundry_addon_insert;
				$info_general_addon['entry_type']=1;
				$info_general_addon['table_name']='tbl_bill_sundry_transaction';
				$info_general_addon['ref_date']=date("Y-m-d",strtotime($POST['invoice_date']));
				$info_general_addon['cdate']	= date("Y-m-d H:i:s");
				$info_general_addon['user_id']	= $_SESSION['user_id'];
				$info_general_addon['company_id']	= $_SESSION['company_id'];

				add_record('tbl_general_book',array_merge($info_general_addon,$curncy_trn), $dbcon); */
			}
			//if invoice is edit time insert data in database end - dhaval

			if ($resp['sundry_amount_of'] == 1) {
				$per_amount_show = "";
			} else {
				$per_amount_show = '<strong> (' . $default_amount . '%)</strong>';
			}
			echo json_encode($finalNetAmount . ',' . $pervalue . ',' . $per_amount_show . ',' . $invoice_id . ',' . $taxvl . ',' . $resp_tax['tax_gst']);
		} else if (strtolower($POST['mode']) == "get_all_bill_sundry") {
			$invoice_id = $POST['invoice_id'];

			$q = $dbcon->query("select b.*,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id from tbl_bill_sundry_transaction as b 

			left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
			left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 

			where b.sundry_voucher_id='$invoice_id' and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' and le.default_sundry='0' ");

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
									type="button" value="' . $cnt . '" onclick="removeSundry(\'\',\'' . $r['sundry_amount'] . '\',this.value,\'' . $r['sundry_id'] . '\')"><i class="fa fa-times"></i></button>
							</div>
						</div>';
				}

				/*$str.='<div class="form-group">
				<label class="col-md-5 control-label">'.$r['l_name'].'</label>
				<div class="col-md-4">
				<input id="sundry_name" name="bill_sundry_addon['.$r['l_id'].']" type="hidden" value="'.$r['sundry_amount'].'">
				<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="'.$r['sundry_amount'].'" readonly placeholder="Amount">
				</div>
				<div class="col-md-3">
				<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
				type="button" value="'.$cnt.'" onclick="removeSundry(\'\',\''.$r['sundry_amount'].'\',this.value,\''.$r['sundry_id'].'\')"><i class="fa fa-times"></i></button>
				</div>
				</div>';*/

				$cnt++;
				//$str.=$r['sundry_amount'];
			}

			echo $str;
			//echo json_encode($resp);
		} else if (strtolower($POST['mode']) == "remove_sundry") {

			$ledger_id = $POST['ledger_id'];

			$info['isdelete'] = 1;

			$updateid = update_record('tbl_bill_sundry_transaction', $info, "sundry_id=" . $POST['ledger_id'], $dbcon);

			//$info_general['genral_book_status'] = 2;

			//$updateid=update_record('tbl_general_book', $info_general,"table_id=".$POST['ledger_id']." and table_name='tbl_bill_sundry_transaction'" , $dbcon);

		} else if (strtolower($POST['mode']) == "get_bill_sundry_label") {
			$sundry_id = $POST['sundry_id'];

			$row = get_sundry_details($dbcon, $sundry_id);

			echo $row['sundry_amount_of'];
		} else if (strtolower($POST['mode']) == "delivery_detail") {
			$product = "select pro.product_name from tbl_purchaseordertrn as trn 
        	left join product_mst as pro on pro.product_id=trn.product_id
        	where trn.purchaseordertrn_id=" . $POST['po_trn_id'];
			$pro_e = $dbcon->query($product);
			$pro_r = mysqli_fetch_array($pro_e);

			$delivery = "select * from tbl_purchaseorder_delivery_date where po_delivery_date_status=0 and purchaseordertrn_id=" . $POST['po_trn_id'];
			$delivery_e = $dbcon->query($delivery);
			$str = '';
			$str .= '<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
        	<tr>
        	<td><strong>Sr.no</strong></td>
        	<td><strong>Delivery Date</strong></td>
        	<td><strong>Delivery Qty</strong></td>
        	</tr>';
			$i = 1;
			if (mysqli_num_rows($delivery_e) > 0) {
				while ($delivery_r = mysqli_fetch_array($delivery_e)) {
					$str .= '<tr>
        			<td>' . $i . '</td>
        			<td>' . date('d-m-Y', strtotime($delivery_r['delivery_date'])) . '</td>
        			<td>' . $delivery_r['product_qty'] . '</td>
        			</tr>';
					$i++;
				}
			} else {
				$str .= '<tr>
        		<td style="text-align:center" colspan="3">No Data Yet..!!</td>
        		</tr>';
			}
			$str .= '</table>';
			$r['delivery_schedule'] = $str;
			$r['pro_name'] = $pro_r['product_name'];
			echo json_encode($r);
		} else if (strtolower($POST['mode']) == "load_payment_terms") {
			$vendor_id = $_POST['vendor_id'];
			$pay_terms = "select pay_terms from tbl_ledger where l_id=$vendor_id";
			$rel1 = mysqli_fetch_assoc($dbcon->query($pay_terms));
			//var_dump($rel1['pay_terms']);
			$resp = getpaymentterms($dbcon, $rel1['pay_terms']);
			$row['resp_html'] = $resp;
			echo json_encode($row);
		} else if (strtolower($_POST['mode']) == "load_transportation") {
			$vendor_id = $_POST['vendor_id'];
			$id = $_POST['trans_id'];

			// Prepare the SQL query using a prepared statement
			$q = "SELECT trp.id, trp.transportation_name
          FROM tbl_cust_tranportation AS trn
          LEFT JOIN transportation_details AS trp ON trp.id = trn.transportation_id
          WHERE cust_transportation_status = '0' AND cust_id = ?
          ORDER BY cust_transportation_id";

			// Prepare the statement
			$stmt = $dbcon->prepare($q);
			if (!$stmt) {
				die("Prepare failed: " . $dbcon->error);
			}

			// Bind parameters and execute
			$stmt->bind_param("i", $vendor_id);
			$stmt->execute();
			$r = $stmt->get_result();
			$cnt = $r->num_rows;

			if ($vendor_id != '' && $cnt > 0) {
				$resp = get_trasports_by_cust($dbcon, $vendor_id, $id);
			} else {
				$resp = get_trasports($dbcon, $id);
			}

			$row['resp_html'] = $resp;
			echo json_encode($row);

			// Close the statement
			$stmt->close();
		} else if (strtolower($POST['mode']) == "vender_address") {
			$vendor_id = $_POST['vender'];
			$query = "select m_address,ci.city_name,st.state_name,con.country_name from tbl_ledger as l
			left join city_mst as ci on ci.cityid = l.cityid
			left join state_mst as st on st.stateid = l.stateid
			left join country_mst as con on con.countryid = l.countryid
			where l.l_id=" . $vendor_id;
			$rel1 = mysqli_fetch_assoc($dbcon->query($query));
			$row['resp_html'] = $rel1['m_address'] . ',' . $rel1['city_name'] . ',' . $rel1['state_name'] . ',' . $rel1['country_name'];
			echo json_encode($row);
		} else if (strtolower($POST['mode']) == "branch_address") {
			$branch_id = $_POST['branch'];
			$query = "select branch_address from branch_mst where branch_id=" . $branch_id;
			$rel1 = mysqli_fetch_assoc($dbcon->query($query));
			$row['resp_html'] = $rel1['branch_address'];
			echo json_encode($row);
		} else if (strtolower($POST['mode']) == "delete_po_approval") {
			$companyConfiguration = getCompanyConfiguration($dbcon);
			$aprv_id = $POST['aprv_id'];
			$tbl = strtolower($POST['tbl']);
			$tbl_id = strtolower($POST['tbl_id']);
			$status = strtolower($POST['status']);
			$info[$status] = 2;

			$updateid = update_record($tbl, $info, "$tbl_id=" . $aprv_id, $dbcon);

			/*if($companyConfiguration['automatic_finance_approval_po']==1){
				$info[$status]  = 2;
				$updateid=update_record('tbl_purchaseorder_finance_aprv_log', $info,"purchaseorder_id=".$POST['purchaseorder_id'] , $dbcon);
			}

			if($companyConfiguration['automatic_approval_po']==1){
				$info[$status]  = 2;
				$updateid=update_record('tbl_purchaseorder_aprv_log', $info,"purchaseorder_id=".$POST['purchaseorder_id'] , $dbcon);
			}*/

			$query = "select * from " . $tbl . " where " . $status . "=0 and purchaseorder_id=" . $POST['purchaseorder_id'] . " order by " . $tbl_id . " desc  limit 1";
			$result = $dbcon->query($query);
			$cnt = brp_mysqli_num_rows($result);
			$rel = brp_mysqli_fetch_array($result);

			if ($cnt > 0) {
				if ($tbl == 'tbl_purchaseorder_finance_aprv_log') {
					$purchase_approval['po_approval_status'] = $rel['approve_status'];
					$updateid = update_record('tbl_purchaseorder', $purchase_approval, 'purchaseorder_id=' . $POST['purchaseorder_id'], $dbcon);
				} else {
					$purchase_approval['po_approval_status'] = $rel['approve_status'];
					$updateid = update_record('tbl_purchaseorder', $purchase_approval, 'purchaseorder_id=' . $POST['purchaseorder_id'], $dbcon);
				}
			} else {
				if ($tbl == 'tbl_purchaseorder_finance_aprv_log') {
					$info1['po_approval_status'] = 3;
					$updateid = update_record('tbl_purchaseorder', $info1, 'purchaseorder_id=' . $POST['purchaseorder_id'], $dbcon);
				} else {
					$info1['po_approval_status'] = 0;
					$updateid = update_record('tbl_purchaseorder', $info1, 'purchaseorder_id=' . $POST['purchaseorder_id'], $dbcon);
				}
			}
			if ($tbl == 'tbl_purchaseorder_finance_aprv_log') {
				$msg['approval'] = 'finance_approval';
			} else {
				$msg['approval'] = 'po_approval';
			}
			//var_dump($msg);
			echo json_encode($msg);
		} else if (strtolower($POST['mode']) == "add_document_attach") {
			var_dump($_POST);
			$companyConfiguration = getCompanyConfiguration($dbcon);
			if ($companyConfiguration['branch_wise_manage'] == 1) {
				$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			} else {
				$branch_id = $companyConfiguration['default_branch_id'];
			}
			//$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			var_dump(123);
			//var_dump($_FILES);
			var_dump($POST);
			$info1['attach_doc_name'] = $POST['doc_name'];
			$info1['attach_file'] = upload_attch_file($_FILES);
			$info1['user_id'] = $_SESSION['user_id'];
			$info1['company_id'] = $_SESSION['company_id'];

			$table = 'tbl_po_attch';
			$tableid = 'so_attach_id';
			if (!empty($POST['purchaseorder_id'])) {
				$info1['purchaseorder_id'] = $POST['purchaseorder_id'];
			} else {
				$info1['attach_status'] = 3;
			}
			/*var_dump($info1);*/
			$inserid = add_record($table, $info1, $dbcon);
		} else if (strtolower($POST['mode']) == "show_document_attach") {

			if ($POST['purchaseorder_id']) {
				$query = "select mst.* from tbl_po_attch as mst 
				where mst.attach_status=0 and mst.purchaseorder_id=" . $POST['purchaseorder_id'];
			} else {
				$query = "select mst.* from tbl_po_attch as mst 
				where attach_status=3 and mst.user_id=" . $_SESSION['user_id'];
			}
			/*echo $query;*/
			$result = $dbcon->query($query);
			echo '<table class="display table table-bordered table-striped">
			<tr>
			<th width="5%" class="text-center">Sr.</th>
			<th width="35%" class="text-center">Document Name</th>
			<th width="50%" class="text-center">Attached Document</th>
			<th width="10%" class="text-center">Action</th>					  
			</tr>
			<tbody>';
			if (mysqli_num_rows($result) > 0) {
				$i = 1;
				while ($rel = mysqli_fetch_assoc($result)) {
					$file_path = $dbcon->real_escape_string(DOMAIN . SO_ATTACH_VIEWING . $rel['attach_file']);
					echo '<tr> 
					<td style="vertical-align:top;">
					<strong>' . $i . '</strong>
					</td>
					<td style="vertical-align:top;" class="text-center">
					' . $rel['attach_doc_name'] . '
					</td>
					<td style="vertical-align:top;" class="text-center">
					<a href="' . ROOT . PO_ATTACH_VIEWING . $rel['attach_file'] . '" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>

					<button type="button" onclick="copyToClipboard(\'' . $file_path . '\')" class="btn btn-primary" target="_blank"><i class="fa fa-clipboard"></i> Copy Path</button>
					</td>
					<td style="vertical-align:top">';

					echo ' <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_document_attach(' . $rel['po_attach_id'] . ')">X</button>';

					echo '</td>	
					</tr>';
					$i++;
				}
			} else {
				echo '<tr><td colspan="4" class="text-center">NO DATA FOUND</td></tr>';
			}

			echo '</tbody>
			</table>';
		} else if (strtolower($POST['mode']) == "delete_document_attach") {
			$row = array();
			$del_attch_qry = "select attach_file from tbl_po_attch where po_attach_id=" . $POST['attach_id'];
			$del_attch_rel = brp_mysqli_fetch_array($dbcon->query($del_attch_qry));
			unlink(PO_ATTACH_UPING . $del_attch_rel['attach_file']);

			$info['attach_status'] = 2;
			$updateid = update_record('tbl_po_attch', $info, "po_attach_id=" . $POST['attach_id'], $dbcon);

			if ($updateid)
				$row['res'] = "1";
			else
				$row['res'] = "0";
			echo json_encode($row);
		} else if (brp_strtolower($POST['mode'] == 'get_terms_detail')) {
			$query = 'select * from tbl_terms_condition where tc_id=' . $POST['tc_id'];
			$result = $dbcon->query($query);
			$row = brp_mysqli_fetch_array($result);

			if (empty($row['tc_details'])) {
				$row['tc_details'] = '';
			}
			echo json_encode($row);
		} else if (brp_strtolower($POST['mode'] == 'send_mail_po')) {

			$email_page_path = $_POST['email_page_path'];
			include('../../../print/view/' . $email_page_path . '.php');

			// Get Userdata
			$cur_user_id = $_SESSION['user_id'];
			$cur_user = getUserDetailById($dbcon, $cur_user_id);
			$user['user_name'] = $cur_user['user_name'];
			$user['user_email'] = $cur_user['common_email_id'] ? $cur_user['common_email_id'] : 'Purchase@flowjetvalve.com';

			$po_id = $_POST['email_po_id'];
			$purchaseorder_id = $dbcon->real_escape_string($po_id);

			$query = "select po.* from tbl_purchaseorder as po where po.purchaseorder_id=$purchaseorder_id";
			$rel = mysqli_fetch_assoc($dbcon->query($query));


			$content = '<!DOCTYPE html>
			<html lang="en">
			<head>
				<meta charset="UTF-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title>Email Body</title>
			</head>
			<body style="font-family: Arial, sans-serif;">
			
				<p>Dear Sir/ Ma\'am,</p>
			
				<p>Kindly find the attached purchase order. We request you to process the order as early as possible.</p>
			
				<p style="color:red;">Please reply if you have received this email after getting our purchase order.</p>
			
				<p><strong style="color:red;">Note:</strong> Kindly confirm the receipt of this mail via return mail.</p>
			
				<br>
			
				<p>Yours faithfully,</p>
			
				<br>
			
				<p><strong>Flowjet Valves Pvt Ltd.</strong></p>
				<p>Plot No. 519, Road No.14, <br>
					Phase II GIDC, Kathwada, <br>
					Ahmedabad Gujarat, <br>
					India. 382430</p>
			
				<p>Office: 9199786 08334 / 35 /36</p>
				<p>GST NO: 24AABCC6052G1Z1</p>
			
				<p>Website: <a href="http://www.flowjetvalve.com">http://www.flowjetvalve.com</a></p>
			
				<p><a href="https://profiles.dunsregistered.com/TPIN-COMP-004.aspx?PaArea=email">D-U-N-S Registered Profile</a></p>
			
				<br>
			
				<p><em>Save our planet. Every 3000 A4 paper costs 1 tree. Please don\'t print this e-mail unless you really need to.</em></p>
			
			</body>
			</html>';

			$po_header = $content;
			$po_subject = $rel["purchaseorder_no"];
			$to = $_POST['to_email_po'];

			// CC 
			$query = "select website as email,company_name from tbl_company where company_id=" . $_SESSION['company_id'];
			$company = mysqli_fetch_assoc($dbcon->query($query));
			$ccemails = [];
			$ccemails[] = array('email' => $company['email'], 'name' => $company['company_name']);
			if ($_POST['ccemail_id_d']) {
				$ccemail_id_d = explode(';', $_POST['ccemail_id_d']);
				foreach ($ccemail_id_d as $val) {
					$ccemails[]['email'] = $val;
				}
			}

			// BCC
			$bccemail[] = array('email' => $user['user_email'], 'name' => $user['user_name']);
			if ($_POST['bccemail_id_d']) {
				$bccemail_id_d = explode(';', $_POST['bccemail_id_d']);
				foreach ($bccemail_id_d as $val) {
					$ccemails[]['email'] = $val;
				}
			}

			$save_file = 'Yes';
			$file_name = po_print($dbcon, $po_id, $save_file);
			$attachment_path = DOMAIN . 'upload/mail_attach/po/' . $file_name;
			$res = common_print_send_email($to, $user, $quot_subject, $quot_header, $attachment_path, $file_name, $ccemails, $bccemail);
			unlink($attachment_path);

			$arr = array();
			$arr['msg'] = '1';
			echo json_encode($arr);
		}
	}
}

function upload_attch_file($FILES)
{
	$rand = rand(0, 99999999);
	if (!empty($FILES['doc_attach']['tmp_name'])) {
		$temp = explode(".", $FILES["doc_attach"]["name"]);
		$extension = strtolower(end($temp));
		$File = "po_attach" . $rand . "." . $extension;
		$tmp_name = $FILES["doc_attach"]["tmp_name"];
		move_uploaded_file($tmp_name, PO_ATTACH_UPING . $File);

		return $File;
	}
}

function get_purchaseorder($dbcon, $purchaseorder_id)
{
	$query = "select po.*,state.state_name,modesup.mode_dispatch,payterms.payment_terms as payment_term,l.l_name as vender_name,country.country_name,l.m_address as vender_address,l.gst_no as tin_no,l.cust_mobile as vender_mobile,l.stateid,state.gst_state_code,city.city_name from tbl_purchaseorder as po inner join tbl_ledger as l on l.l_id=po.vender_id
	left join country_mst as country on country.countryid=l.countryid
	left join pay_terms as payterms on payterms.terms_id=po.payment_terms
	left join mode_of_dispatch as modesup on modesup.mode_dis_id=po.mode_of_dispatch
	left join state_mst as state on state.stateid=l.stateid
	left join city_mst as city on city.cityid=l.cityid
	where po.purchaseorder_id=$purchaseorder_id";
	$rel = mysqli_fetch_assoc($dbcon->query($query));

	$_SESSION['invoice_no'] = $rel['invoice_no'];
	$form = "Purchase Order";
	$set = "select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=" . $rel['company_id'];
	$set_head = mysqli_fetch_assoc($dbcon->query($set));
	$order_date = '';
	if ($rel['order_date'] != "1970-01-01" && $rel['order_date'] != "0000-00-00") {
		$order_date = date('d-m-Y', strtotime($rel['order_date']));
	}

	$cons_company_name = $rel['company_name'];
	$cons_cust_address = $rel['cust_address'];
	$cons_gst_no = $rel['gst_no'];
	$cons_state_name = $rel['state_name'];
	$cons_gst_state_code = $rel['gst_state_code'];
	$cons_city_name = $rel['city_name'];
	$cons_country_name = $rel['country_name'];

	if (!empty($rel['consignee_id'])) {
		$consignee = "select * from tbl_custmer_consignee as cust 
		left join country_mst as country on country.countryid=cust.countryid
		left join state_mst as state on state.stateid=cust.stateid 
		left join city_mst as city on city.cityid=cust.cityid where cust_id=" . $rel['consignee_id'];
		$cons_data = mysqli_fetch_assoc($dbcon->query($consignee));
		$cons_company_name = $cons_data['company_name'];
		$cons_cust_address = $cons_data['cust_address'];
		$cons_gst_no = $cons_data['gst_no'];
		$cons_state_name = $cons_data['state_name'];
		$cons_gst_state_code = $cons_data['gst_state_code'];
		$cons_city_name = $cons_data['city_name'];
		$cons_country_name = $cons_data['country_name'];
	}
	$user_qry = "select user_name,user_mail,user_phone from users where user_id=" . $_SESSION['user_id'] . " and company_id=" . $rel['company_id'];
	$user_data = mysqli_fetch_assoc($dbcon->query($user_qry));
	/* Check Discount is On or off Start */
	if ($set_head['show_disc'] == '1') {
		$colspan = 5;
		$dynamicwidth = 40;
	} else {
		$colspan = 6;
		$dynamicwidth = 46;
	}
	$header = '<img src="' . DOMAIN_F . LOGO . 'hermatic-logo.jpg' . '" style="" />';

	$approve_status = '';
	if ($rel['approve_status'] == '0') {
		$approve_status = ' (DRAFT)';
	}

	$html = '<html>
	<head>					
	<title>' . $form . ' - ' . $rel['purchaseorder_no'] . '</title>
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

		</style>
		</head>
		<body>

		<htmlpageheader name="otherpages" style="display:none">
		<div style="text-align:center">' . $header . '</div>
		</htmlpageheader>
		<!--<htmlpagefooter name="otherpages_footer" style="display:none">
		<div style="text-align:center"></div>
		</htmlpagefooter>-->
		<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
		<div>
		<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
		<tr>
		<td colspan="3" style="text-align:center; font-size:15px; font-weight:bold;">' . $form . '</td>
		</tr>
		<tr>
		<td rowspan="6" style="text-align:left; vertical-align:top; border:1px solid; width:50%;">
		<strong>To, <br>' . $rel['vender_name'] . '</strong><br/>' . $rel['vender_address'] . '<br/>' . $rel['city_name'] . ',' . $rel['state_name'] . ',' . $rel['country_name'] . '<br>GST NO. : ' . $rel['tin_no'] . '<br>Kind Attn. : ' . $rel['vender_name'] . '
		</td>
		<td style="text-align:left;border:1px solid;width:20%;"><strong>Purchase Order No</strong></td>
		<td style="text-align:left;border:1px solid;width:30%;font-size:14px"><strong>' . $rel['purchaseorder_no'] . '</strong></td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;">Purchase Order Date</td>
		<td style="text-align:left;border:1px solid;"> ' . date("d-M-Y", strtotime($rel['purchaseorder_date'])) . '</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Quotation Ref No</td>
		<td style="text-align:left;border:1px solid;"> ' . $rel['quotation_no'] . '</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Quotation Ref Date</td>
		<td style="text-align:left;border:1px solid;"> ' . date('d-M-Y', strtotime($rel['quotation_date'])) . '</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Vendor Code</td>
		<td style="text-align:left;border:1px solid;"></td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;">Project Code</td>
		<td style="text-align:left;border:1px solid;"></td>
		</tr>
		<tr style="border-bottom: none;">
		<td rowspan="6" style="text-align:left; vertical-align:top; border:1px solid; width:50%;border-bottom: none;">
		<strong>Ship To, <br>' . $rel['vender_name'] . '</strong><br/>' . $rel['vender_address'] . '<br/>' . $rel['city_name'] . ',' . $rel['state_name'] . ',' . $rel['country_name'] . '
		</td>
		<td style="text-align:left;border:1px solid;">PR No</td>
		<td style="text-align:left;border:1px solid;"></td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;">PO Valid Till</td>
		<td style="text-align:left;border:1px solid;"></td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;">Delivery Date</td>
		<td style="text-align:left;border:1px solid;"> ' . date("d-M-Y", strtotime($rel['purchaseorder_due_date'])) . '</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Payment Terms</td>
		<td style="text-align:left;border:1px solid;"> ' . $rel['payment_terms'] . '</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Buyers Name</td>
		<td style="text-align:left;border:1px solid;"> ' . $user_data['user_name'] . '</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Buyers Mobile No</td>
		<td style="text-align:left;border:1px solid;"> ' . $user_data['user_phone'] . '</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;border-top: none;">' . $set_head['company_name'] . ' GST No. : ' . $set_head['vatno'] . '</td>
		<td style="text-align:left;border:1px solid;"> Buyers Email</td>
		<td style="text-align:left;border:1px solid;"> ' . (strtolower($user_data['user_mail'])) . '</td>
		</tr>
		<tr style="border-bottom: none;">
		<td colspan="3"><strong> We are pleased to place this Purchase/ Service Order for the supply of the following, subject to the terms and conditions given in annexure. </strong></td>
		</tr>
		</table>
		<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		<thead>
		<tr>
		<th style="width:5%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
		<th style="width:25%;text-align:center;border:1px solid;">Item Description</th>
		<th style="width:5%;text-align:center;border:1px solid;">HSN Code</th>
		<th style="width:8%;text-align:center;border:1px solid;">Qty</th>
		<th style="width:10%;text-align:center;border:1px solid;">Rate</th>';
	if ($set_head['show_disc'] == '1') {
		$html .= '<th style="width:7%;text-align:center;border:1px solid;">Less. Disc.</th>';
	}
	$html .= '<th style="width:10%;text-align:center;border:1px solid;">Amount</th>
		<th style="width:5%;text-align:center;border:1px solid;">Rate(%)</th>
		<th style="width:10%;text-align:center;border:1px solid;">Tax Value</th>
		<th style="width:15%;text-align:right;border:1px solid;">Total Price</th>
		</tr>
		</thead>
		<tbody>';
	$qry = "select trn.*,product.*,product.product_desc as scode,per.unit_name,per1.unit_name as base_unit_name,per2.unit_name as conv_unit_name,group_concat(tax.tax_value) as tax_val,group_concat(tax.tax_name) as tax_name FROM `tbl_purchaseordertrn` as trn 
		left join product_mst as product on product.product_id=trn.product_id 
		left join unit_mst as per on per.unitid=trn.unit_id 
		left join unit_mst as per1 on per1.unitid=product.product_base_unit 
		left join unit_mst as per2 on per2.unitid=product.product_conv_unit 
		left join `formula_mst` as ftax on ftax.formulaid=trn.formulaid left join tbl_tax as tax on find_in_set(tax.tax_id,ftax.tax_id)
		where purchaseordertrn_status=0 and purchaseorder_id=" . $rel['purchaseorder_id'] . " group by purchaseordertrn_id order by purchaseordertrn_id";
	$result = $dbcon->query($qry);
	$i = 1;
	$total = 0;
	$discount = 0;
	$totalqty = 0;
	$totalsqr = 0;
	$charges_qty = 0;
	$cnt = mysqli_num_rows($result);
	while ($row = mysqli_fetch_assoc($result)) {
		if ($row['product_base_unit'] != $row['product_conv_unit']) {
			//base_unit_name,per2.unit_name as conv_unit_name
			if ($row['unit_id'] == $row['product_base_unit']) {
				$cqty = convert_stock($dbcon, $row['product_qty'], $row['product_id'], "conv_unit");
				$uname = $row['conv_unit_name'];
			} else {
				$cqty = convert_stock($dbcon, $row['product_qty'], $row['product_id'], "base_unit");
				$uname = $row['base_unit_name'];
			}
		}
		$tax_arr = explode(",", $row['tax_val']);
		//tax summary calculation start
		// if (!empty($row['tax_val'])) {
		// 	$tax_num = explode(",", $row['tax_val']);
		// 	$tax_name = explode(",", $row['tax_name']);
		// 	$total_net_rate = ($row['product_qty'] * $row['product_rate']) - $row['discount'];
		// 	for ($j = 0; $j < count($tax_num); $j++) {
		// 		if (!in_array($tax_name[$j], $tax['per'])) {
		// 			$tax['per'][] = $tax_name[$j];
		// 		}
		// 		$tax['per_total'][$tax_name[$j]] += $total_net_rate * $tax_num[$j] / 100;
		// 	}
		// }
		$total_taxs = $tax_arr[0] + $tax_arr[1];
		//tax summary calculation end
		$taxable_amt = $row['total'] - $row['product_amount'];

		$html .= '<tr style="border:none;border-left:1px solid;border-right:1px solid;">
			<td style="text-align:center;border:1px solid;vertical-align:top;">' . $i . '</td>
			<td style="text-align:left;border:1px solid;vertical-align:top;">
			<strong>' . $row['product_name'] . '</strong><br/>
			' . nl2br($row['description']) . '
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">' . $row['product_hsn'] . '</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			' . $row['product_qty'] . '' . $row['unit_name'] . '
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">' . number_format($row['product_rate'], 2, ".", "") . '</td>';
		if ($set_head['show_disc'] == '1') {
			$html .= '<td style="text-align:center;border:1px solid;vertical-align:top;">' . number_format($row['discount_per'], 2, ".", "") . '</td>';
		}
		$html .= '<td style="text-align:center;border:1px solid;vertical-align:top;">' . number_format($row['product_amount'], 2, ".", "") . '</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">' . $total_taxs . ' %</td>
			<td style="text-align:right;border:1px solid;vertical-align:top;">' . number_format($taxable_amt, 2, ".", "") . '</td>
			<td style="text-align:right;border:1px solid;vertical-align:top;">' . number_format($row['total'], 2, ".", "") . '</td>
			</tr>';
		$i++;
		$totalqty = $totalqty + $row['product_qty'] - $charges_qty;
		$totalsqr = $totalsqr + $row['sqr_ft'] - $charges_qty;
		$total_product_amount = 0;
		$totaltaxable = 0;
		$total_product_amount += $row['product_amount'];
		$totaltaxable += $taxable_amt;
		$total += $row['total'];
	}
	$pr = 10 - $cnt;
	for ($j = 0; $j < $pr; $j++) {
		$html .= '<tr style="border:none;border-left:1px solid;border-right:1px solid;">
			<td style="border:none;border-left:1px solid;border-right:1px solid;height:25px;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			</tr>';
	}

	$html .= '<tr>
		<td colspan="5" style="text-align:left;border:1px solid;color:Black; font-weight: bold;">Basic PO Amount: ' . number_format($total_product_amount, 2, ".", "") . '</td>
		<td colspan="4" style="text-align:right;border:1px solid; font-weight: bold;">Total Basic</td>
		<td style="text-align:right;border:1px solid;font-weight: bold;">' . number_format($total_product_amount, 2, ".", "") . '</td>
		</tr>';
	$chkrow = $dbcon->query("select mst.*,t.tax_name,sum(tx_taxable_value) as tamo from tbl_tax_trn as mst 
			left join tbl_tax as t on t.tax_id=mst.tx_tax_id 
			left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=mst.tx_transaction_id
			where trn.purchaseorder_id='" . $purchaseorder_id . "' and tx_status=0 and tx_transaction_type='purchase_order' group by tx_tax_id");
	$getrows = mysqli_num_rows($chkrow);
	$rt = $dbcon->query("select mst.*,t.tax_name,sum(tx_taxable_value) as tamo from tbl_tax_trn as mst 
			left join tbl_tax as t on t.tax_id=mst.tx_tax_id 
			left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=mst.tx_transaction_id
			where trn.purchaseorder_id='" . $purchaseorder_id . "' and tx_status=0 and tx_transaction_type='purchase_order' group by tx_tax_id");
	$k = 0;
	while ($rel1 = mysqli_fetch_assoc($rt)) {
		if ($getrows > 2) {
			$rows = 3;
		} else {
			$rows = 2;
		}
		$rt1 = $dbcon->query("select mst.*,t.tax_name,sum(tx_taxable_value) as tamo from tbl_tax_trn as mst 
				left join tbl_tax as t on t.tax_id=mst.tx_tax_id 
				left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=mst.tx_transaction_id
				where trn.purchaseorder_id='$purchaseorder_id' and mst.tx_tax_id=" . $rel1['tx_tax_id'] . " and tx_status=0 and tx_transaction_type='purchase_order' ");
		$rel122 = mysqli_fetch_assoc($rt1);
		$html .= '<tr>';
		if ($k == 0) {
			$html .= '<td colspan="5" rowspan="' . $rows . '" style="text-align:left;border:1px solid;color:Black; font-weight: bold;">GST Amount: ' . number_format($totaltaxable, 2, ".", "") . '</td>';
		}
		$html .= '<td colspan="4" style="text-align:right;border:1px solid; font-weight: bold;">' . $rel1['tax_name'] . ' Amount</td>
			<td style="text-align:right;border:1px solid;font-weight: bold;">' . number_format($rel122['tamo'], 2, ".", "") . '</td>
			</tr>';
		$k++;
	}
	$html .= '<tr>
		<td colspan="5" rowspan="2" style="text-align:left;border:1px solid;color:Black; font-weight: bold;">Total PO Amount: ' . number_format($rel['g_total'], 2, ".", "") . '</td>
		<td colspan="4" style="text-align:right;border:1px solid; font-weight: bold;">Total Tax Amount</td>
		<td style="text-align:right;border:1px solid;font-weight: bold;">' . number_format($totaltaxable, 2, ".", "") . '</td>
		</tr>
		<tr>
		<td colspan="4" style="text-align:right;border:1px solid; font-weight: bold;">Total Amount</td>
		<td style="text-align:right;border:1px solid;font-weight: bold;">' . number_format($rel['g_total'], 2, ".", "") . '</td>
		</tr>';
	$html .= '
		<tr>
		<td colspan="5" rowspan="2" style="height:80px; text-align:left; vertical-align:top; border-left:1px solid; border-bottom:1px solid; font-weight: bold;"><strong>Terms and Conditions:</strong><br> ' . $set_head['po_condition'] . '</td>
		<td colspan="5" style="text-align:center;vertical-align:top;border-bottom:1px solid;border-right:1px solid;font-weight: bold;height:80px;">For, ' . $set_head['company_name'] . '</td>
		</tr>
		<tr>
		<td colspan="5"><center style="vertical-align:bottom;">Authorised Signatory</center></td>
		</tr>
		</tbody></table>
		<div style="clear:both;"></div>
		</div>
		<!--page1 end-->';

	/* Get Terms And Condition Start */
	$terms_qry = "select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
		left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
		where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=" . $rel['quotation_id'] . " order by qtrm.tc_priority";
	$terms_qry_rs = $dbcon->query($terms_qry);
	if (mysqli_num_rows($terms_qry_rs)) {
		$html .= '<center class="nextpage"></center>
			<h3 style="text-align:center;">Terms & Conditions for Sales Quotation No : <u>' . $rel['quotation_no'] . '</u></h3>
			<div><table width="100%" style="font-size:12px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3"><tbody>';
		$t = 1;
		while ($term_rel = mysqli_fetch_assoc($terms_qry_rs)) {
			$string = (nl2br($term_rel['tc_details']));

			$html .= '<tr>
				<td width="5%" style="width:5%;text-align:center;border:1px solid;padding:5px;">' . $t . '</td>
				<td width="25%" style="width:25%;text-align:left;border:1px solid;padding:5px;">' . $term_rel['tc_name'] . '</td>
				<td width="70%" style="width:70%;text-align:left;border:1px solid #000;padding:5px;">' . $string . '</td>
				</tr>';
			$t++;
		}
		$html .= '</tbody></table></div>';
	}
	/* Get Terms And Condition Start */

	$html .= '<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
	// echo $html;exit;
	// ob_end_clean();
	// include("../view/export/mpdf/mpdf.php");
	// $mpdf = new mPDF('', 'A4', '0', 'calibri', '10', '10', '25', '10', '1', '1');
	// $mpdf->defaultheaderfontsize = 10; /* in pts */
	// $mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
	// $mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
	// $mpdf->defaultfooterfontsize = 10; /* in pts */
	// $mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
	// $mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
	// $mpdf->SetHTMLHeader($header);
	// // $mpdf->SetHTMLFooter($footer);

	// //Show page number
	// $mpdf->pagenumPrefix = ' ';
	// $mpdf->pagenumSuffix = ' / ';
	// $mpdf->nbpgPrefix = ' ';
	// $mpdf->nbpgSuffix = ' pages';
	// $mpdf->SetFooter('{PAGENO}{nbpg}');

	// $mpdf->SetWatermarkText();
	// $mpdf->showWatermarkText = true;
	// $mpdf->allow_charset_conversion = true;
	// $mpdf->charset_in = 'UTF-8';
	// $mpdf->WriteHTML($html);
	// // $mpdf->Output();
	// $mpdf->Output('../view/upload/quotation_pdf_file/Purchase_Order_' . $purchaseorder_id . '.pdf', 'f');
	// ob_clean();
	// return $purchaseorder_id;
}
function get_product_tax($dbcon, $product_amount, $formulaid)
{
	$formulaid = isset($formulaid) ? (int)$formulaid : 0;

	$qry = "SELECT formula.*, tax.*, formula.tax_id as ftax
        FROM `formula_mst` as formula
        INNER JOIN tbl_tax as tax ON find_in_set(tax.tax_id, formula.tax_id)
        WHERE formulaid=" . $formulaid . "
        ORDER BY tax_value DESC";
	$row = $dbcon->query($qry);
	$rate_total = $total = $product_amount;
	$i = 1;
	$tax_total_amount = 0;
	while ($tax = brp_mysqli_fetch_assoc($row)) {
		if ($i == 1) {
			$info['tax_id'] = $tax['ftax'];
		}
		$info['tax_name' . $i] = $tax['tax_name'];
		$info['tax_amount' . $i] = $tax_amount = ($total) * $tax['tax_value'] / 100;
		$rate_total += $tax_amount;
		$tax_total_amount += $info['tax_amount' . $i];
		$i++;
	}
	for ($j = $i; $j <= 3; $j++) {
		$info['tax_name' . $i] = '';
		$info['tax_amount' . $i] = '';
	}
	$info['total'] = $rate_total;
	$info['tax_total_amount'] = $tax_total_amount;
	return $info;
}
function check_po_rates_status($dbcon, $purchaseorder_id)
{
	$sel_pro_rate = "select * from tbl_purchaseordertrn where purchaseordertrn_status=0 and purchaseorder_id=" . $purchaseorder_id;
	$rate_flag = false;
	$sel_pro_rate_rs = $dbcon->query($sel_pro_rate);
	while ($sel_pro_rate_rel = brp_mysqli_fetch_assoc($sel_pro_rate_rs)) {
		$pro_mst_rate = get_pro_field($dbcon, $sel_pro_rate_rel['product_id'], 'product_purchase_mst_rate');
		if ($pro_mst_rate && $sel_pro_rate_rel['product_rate'] > $pro_mst_rate) {
			$rate_flag = true;
			break;
		}
	}
	if ($rate_flag) {
		$upd_stst = $dbcon->query("update tbl_purchaseorder set po_approval_status=0 where purchaseorder_id=" . $purchaseorder_id);
	} else {
		$upd_stst = $dbcon->query("update tbl_purchaseorder set po_approval_status=1 where purchaseorder_id=" . $purchaseorder_id);
	}
}
/* function update_po_status($dbcon,$inserpoid){
				$que_po="select * from tbl_purchaseordertrn where temptrn_ref_id!=0 and purchaseorder_id=".$inserpoid;
				$resi=$dbcon->query($que_po);
				while($re_po=brp_mysqli_fetch_assoc($resi)){

					$query_p="select sum(product_qty) as used_qty from tbl_purchaseordertrn where purchaseordertrn_status=0 and temptrn_ref_id=".$re_po['temptrn_ref_id'];
					$rels=brp_mysqli_fetch_assoc($dbcon->query($query_p));

					$query_s="select product_qty from tbl_purchasetrntemp where purchaseordertrn_status=0 and purchaseordertrn_id=".$re_po['temptrn_ref_id'];
					$relp=brp_mysqli_fetch_assoc($dbcon->query($query_s));
					$pending_qty=$relp['product_qty']-$rels['used_qty'];
					if($pending_qty<=0){
						$inf['po_trn_req_status']=1;
						update_record('tbl_purchasetrntemp', $inf,"purchaseordertrn_id=".$re_po['temptrn_ref_id'], $dbcon);
					}else{
						$inf['po_trn_req_status']=0;
						update_record('tbl_purchasetrntemp', $inf,"purchaseordertrn_id=".$re_po['temptrn_ref_id'], $dbcon);
					}
				}
			}
 */
/*function get_so_no_po_ref($dbcon,$rp_id) {


				$so = "select rp_id,perent_id,sales_order_trn_id from tbl_request_product where rp_id=".$rp_id;	
				var_dump($so);
				$so_e=$dbcon->query($so);
				$sot ='';
				while($row = brp_mysqli_fetch_array($so_e)){
					var_dump($row['parent_id']);
					if($row['perent_id']==0){
						$sot .=  $row['sales_order_trn_id'];
					}else{
						//var_dump($row['perent_id']);
						get_so_no_po_ref($dbcon,$row['parent_id']);	
					}
				}
				return $sot;
			}*/
