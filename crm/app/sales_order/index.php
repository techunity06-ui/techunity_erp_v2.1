<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');
// error_reporting(E_ALL);
$incPath = $path.'include/';
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");
include_once("../../../include/common_send_email.php");
// include_once("../../../print/view/umaboy_sales_order_print.php");

$companyConfiguration=getCompanyConfiguration($dbcon);
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	SALES_ORDER_SLUG_EDIT,
	SALES_ORDER_SLUG_DELETE,
	SALES_ORDER_SLUG_PRINT,
	SALES_ORDER_SLUG_READ,
	SALES_ORDER_SLUG_APPROVE,
	SALES_ORDER_SLUG_FINAL_APPROVE,
	ORDER_ACCEPTANCE_SLUG_DELETE,
	SALES_ORDER_SLUG_UPDATE_USER	
]);

if(!in_array(SALES_ORDER_SLUG_READ,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "fetch") {
	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
		//$branch_id = $POST['branch_id'];	
	$where='';
	$getapprovalsetting = get_userwise_approval_setting($dbcon,2,$_SESSION['user_id']);
	$getspecialConfiguration=getspecialConfiguration($dbcon);
	$companyConfiguration=getCompanyConfiguration($dbcon);
	if($companyConfiguration['branch_wise_manage']==1){
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	}else{
		$branch_id =$companyConfiguration['default_branch_id'];
	}
	
	//$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	
	$where_db = check_branch('estimate', $branch_id);
	$where.="  and sales_order_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND sales_order_date<='".date('Y-m-d',strtotime($s_date[1]))."'".$where_db;

	if( $POST['jobwork_type'] != ""){
		$where .= " and jobwork_type = " . $POST['jobwork_type'];
	}

	if( $POST['user_id'] != ""){
		$where .= " and estimate.user_id = " . $POST['user_id'];
	}else{
		if($_SESSION['user_type']!=2){
			if($companyConfiguration['crm_sales_order_user_selecation']==1){
				$usertype = explode(",",$companyConfiguration['crm_sales_order_user_type_selecation']);
				if(in_array($_SESSION['user_type'], $usertype)){
				// $where .= "";
				}else{
					$where .= " and estimate.user_id = " . $_SESSION['user_id'];
				}
			}
		}
	}
	$stat_where="";
	if($POST['so_status']==8){
		//Approve Pending
		$stat_where=" and estimate.approve_status=0";
	}else if($POST['so_status']==1){
		//Approve Done
		$stat_where=" and estimate.approve_status=3";

	}else if($POST['so_status']==2){
		//Disapprove
		$stat_where=" and estimate.approve_status not in (3,0)";
	}else if($POST['so_status']==3){
		//Order Accept Pending
		$stat_where=" and estimate.order_accept_status=0";
	}else if($POST['so_status']==4){
		//Order Accept Done
		$stat_where=" and estimate.order_accept_status=1";
	}else if($POST['so_status']==5){
		//Invoice Pending
		$stat_where=" and estimate.invoice_status=0";
	}else if($POST['so_status']==6){
		//Invoice Done
		$stat_where=" and estimate.invoice_status=1 and estimate.short_close_status=0";
	}else if($POST['so_status']==7){
		//Short Close
		$stat_where=" and estimate.short_close_status=1";
	}
	
	
	$appData = array();
	$i=1;
	$aColumns = array('estimate.sales_order_no','estimate.g_total','estimate.sales_order_date','cust.l_name','city.city_name','estimate.po_no','estimate.po_date','estimate.sales_order_status','estimate.cdate','estimate.user_id', 'estimate.invoice_status','estimate.approve_status','quot.inquiry_id','branch.branch_name','estimate.branch_id','estimate.jobwork_type','estimate.sales_order_id','estimate.revise_status','estimate.order_accept_status','users.user_name','estimate.short_close_status','SUM(sotrn.product_amount) as total');
	$sIndexColumn = "estimate.sales_order_id";
	$isWhere = array("estimate.sales_order_status = 0 and estimate.company_id IN (0,$_SESSION[company_id])".$where.$stat_where);
	$sTable = "tbl_sales_order as estimate";			
	$isJOIN = array(
		'left join tbl_ledger cust on estimate.cust_id=cust.l_id',
		'left join city_mst city on cust.cityid=city.cityid',
		'left join tbl_quotation quot on quot.quotation_id=estimate.quotation_id',
		'left join branch_mst as branch on branch.branch_id=estimate.branch_id',
		'left join users as users on users.user_id=estimate.user_id',
		'left join tbl_task as tsk on tsk.inquiry_id=quot.inquiry_id and tsk.stage_prob=100',
		'left join tbl_sales_ordertrn as sotrn on sotrn.sales_order_id=estimate.sales_order_id',
	);
	$hOrder = "estimate.sales_order_id desc";
	$hGroupby = array("estimate.sales_order_id");
	$having_clause ="";
	/*END*/

	include($incPath.'pagging.php');
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
			//$row_data[] = $id;
		if(in_array(SALES_ORDER_SLUG_EDIT,$bulkAccessArray) && $row['approve_status']==0){

			$row_data[] = '<a class="" data-original-title="Edit '.$row["sales_order_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'salesorderedit/'.$row['sales_order_id'].'">'.$row["sales_order_no"].'</a>';
			$row_data[] = '<a class="" data-original-title="Edit '.$row["sales_order_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'salesorderedit/'.$row['sales_order_id'].'">'.date('d M, Y',strtotime($row["sales_order_date"])).'</a>';
			$row_data[] = '<a class="" data-original-title="Edit '.$row["sales_order_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'salesorderedit/'.$row['sales_order_id'].'">'.$row["l_name"].'</a>';
			$row_data[] = '<a class="" data-original-title="Edit '.$row["sales_order_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'salesorderedit/'.$row['sales_order_id'].'">'.$row["city_name"].'</a>';
			/*$row_data[] = '<a class="" data-original-title="Edit '.$row["sales_order_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'salesorderedit/'.$row['sales_order_id'].'">'.$row["g_total"].'</a>';*/
		}else{
			$row_data[] = $row['sales_order_no'];
			$row_data[] = date('d M, Y',strtotime($row['sales_order_date']));
			$row_data[] = $row['l_name'];
			$row_data[] = $row['city_name'];

		}
		$row_data[] = $row['total'];
		$row_data[] = $row['g_total'];
		$row_data[] = $row['po_no'];
		$row_data[] = date('d-m-Y',strtotime($row['po_date']));
		if($row['order_accept_status']==0){
			$oa = '  <button class="btn btn-xs btn-warning" data-original-title="Approved" data-toggle="tooltip" data-placement="top">Order Accept Pending</button>';
		}else if($row['order_accept_status']==1){
			$oa = '  <button class="btn btn-xs btn-primary" data-original-title="Approved" data-toggle="tooltip" data-placement="top">Order Accepted</button>';
		}
		$oa_apprv_btn = '';
		if($row['approve_status']==3){
			$row_data[] ='<button class="btn btn-xs btn-success" data-original-title="Approved" data-toggle="tooltip" data-placement="top">Approved</button>'.$oa;
			$oa_apprv_btn='<button class="btn btn-xs btn-warning" data-original-title="Approve/Reject Order Accept" data-toggle="tooltip" data-placement="top" onClick="open_po_approv_payments('.$row['sales_order_id'].',\''.$row["sales_order_no"].'\')"><i class="fa fa-exclamation-triangle"></i></button>';
		}else if($row['approve_status']==0){
			$row_data[] ='<button class="btn btn-xs btn-warning" data-original-title="Approve Pending" data-toggle="tooltip" data-placement="top">Approve Pending </button>';
		}else{
			$disapproved = get_so_disapproved_reason($dbcon,'tbl_quot_po_aprv_log','approve_remark',$row['sales_order_id'],'approve_status','0','quot_aprv_log_id');
			if(empty($disapproved)){
				$disapproved = get_oa_disapproved_reason($dbcon,'tbl_oa_aprv_log','approve_remark',$row['sales_order_id'],'approve_status','0','oa_aprv_log_id');
			}
			$row_data[] ='<button class="btn btn-xs btn-danger" data-original-title="'.$disapproved.'" data-toggle="tooltip" data-placement="top">Disapproved</button>';
			$oa_apprv_btn='<button class="btn btn-xs btn-warning" data-original-title="Approve/Reject Order Accept" data-toggle="tooltip" data-placement="top" onClick="open_po_approv_payments('.$row['sales_order_id'].',\''.$row["sales_order_no"].'\')"><i class="fa fa-exclamation-triangle"></i></button>';
		}
		$row_data[]=$row['approve_status'];
		if($companyConfiguration['outside_jobwork']){

			if($row['jobwork_type'] == '0'){
				$row_data[] ='<button class="btn btn-xs btn-success" data-original-title="Normal Jobwork" data-toggle="tooltip" data-placement="top">Normal</button>';

			}else{
				$row_data[] ='<button class="btn btn-xs btn-danger" data-original-title="Outside Jobwork" data-toggle="tooltip" data-placement="top">Outside Jobwork</button>';

			}
		}


		if($companyConfiguration['branch_wise_manage']==1){
			$row_data[] = $row['branch_name'];
		}

		$row_data[] = $row['user_name'];

		$invoicestatus='';$delete='';$edit='';$po_apprv_btn='';$sales_order_print='';$order_acceptance_print=''; $so_emend='';$revise = '';$stagestatus="";$short_close ='';$tracking = '';$view_attach_doc = '';

		$view_attach_doc = '<button class="btn btn-xs btn-info" data-original-title="View Attached Document" data-toggle="tooltip" data-placement="top" onClick="view_attach_document('.$row['sales_order_id'].',\''.$row['sales_order_no'].'\')"><i class="fa fa-eye"></i></button>';

		if(in_array(SALES_ORDER_SLUG_PRINT,$bulkAccessArray)){
			$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
			$rels=mysqli_fetch_assoc($menusql);
			$menu_show_permissions = explode(",",$rels['print_permission']);
			$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 9 AND approve_status = 1 AND status = 0 ORDER BY priority");
			while($res = mysqli_fetch_assoc($sql)){
				if(in_array($res['id'],$menu_show_permissions)) {
					///////////////////////////////Harshil - 15-10-2022//////////////////////////
					
					if($companyConfiguration['sales_order_print_after_approval']==1){
						if($row['approve_status']==3){
							if($res['with_out_logo']==0){
								$sales_order_print.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['sales_order_id'].'?'.time().'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';	
							}else{
								$ddf="'".DOMAIN_F.PRINT_ROOT.$res['page_path']."/".$row['sales_order_id']."'";
								//$ddf="dfsd";
								$sales_order_print .='<button class="btn btn-xs btn-success" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';" onClick="open_print('.$ddf.')"><i class="'.$res['fa_icon'].'"></i></button>';
							}
						}
					}
					else
					{
						if($res['with_out_logo']==0){
							$sales_order_print.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['sales_order_id'].'?'.time().'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';	
						}else{
							$ddf="'".DOMAIN_F.PRINT_ROOT.$res['page_path']."/".$row['sales_order_id']."'";
								//$ddf="dfsd";
								$sales_order_print .='<button class="btn btn-xs btn-success" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';" onClick="open_print('.$ddf.')"><i class="'.$res['fa_icon'].'"></i></button>';
						}
					}
					
					/////////////////////////////////////////////////Harshil 15-10-2022////////////////////////
				}
			}
			$sqls=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 3 AND approve_status = 1 AND status = 0 ORDER BY priority");
			while($ress = mysqli_fetch_assoc($sqls)){
				if(in_array($ress['id'],$menu_show_permissions)) {
					////////////////////////////////////////////////////////////////////////Harshil 15-10-2022/////////////////////////////////
					if($companyConfiguration['sales_order_print_after_approval']==1){
						if($row['approve_status']==3){
							if($res['with_out_logo']==0){
								$order_acceptance_print.='<a class="btn btn-xs btn-primary" data-original-title="'.$ress['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$ress['page_path'].'/'.$row['sales_order_id'].'?'.time().'" style="background: '.$ress['icon_color'].'; border-color: '.$ress['icon_color'].';"><i class="'.$ress['fa_icon'].'"></i></a>';	
							}else{
								$ddf="'".DOMAIN_F.PRINT_ROOT.$ress['page_path']."/".$row['sales_order_id']."'";
								//$ddf="dfsd";
								$order_acceptance_print .='<button class="btn btn-xs btn-success" data-original-title="'.$ress['print_name'].'" data-toggle="tooltip" data-placement="top" style="background: '.$ress['icon_color'].'; border-color: '.$ress['icon_color'].';" onClick="open_print('.$ddf.')"><i class="'.$ress['fa_icon'].'"></i></button>';
							}
						}
					}
					else
					{
						if($ress['with_out_logo']==0){
							$order_acceptance_print.='<a class="btn btn-xs btn-primary" data-original-title="'.$ress['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$ress['page_path'].'/'.$row['sales_order_id'].'?'.time().'" style="background: '.$ress['icon_color'].'; border-color: '.$ress['icon_color'].';"><i class="'.$ress['fa_icon'].'"></i></a>';
							
						}else{
							$ddf="'".DOMAIN_F.PRINT_ROOT.$ress['page_path']."/".$row['sales_order_id']."'";
								// $ddf="dfsd";
								$order_acceptance_print .='<button class="btn btn-xs btn-success" data-original-title="'.$ress['print_name'].'" data-toggle="tooltip" data-placement="top" style="background: '.$ress['icon_color'].'; border-color: '.$ress['icon_color'].';" onClick="open_print('.$ddf.')"><i class="'.$ress['fa_icon'].'"></i></button>';
						}
					}
				}
			}
		}
		
		if(($getapprovalsetting['amount'] >= $row['g_total']) && ($getapprovalsetting['auto_approval']==1)){
			if(in_array(SALES_ORDER_SLUG_APPROVE,$bulkAccessArray)){
				$po_apprv_btn='<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Sales Order" data-toggle="tooltip" data-placement="top" onClick="open_po_approv_payment('.$row['sales_order_id'].',\''.$row["sales_order_no"].'\')"><i class="fa fa-exclamation-triangle"></i></button>';
			}
			if(in_array(SALES_ORDER_SLUG_FINAL_APPROVE,$bulkAccessArray)){
				$po_apprv_btn='<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Sales Order" data-toggle="tooltip" data-placement="top" onClick="open_po_approv_payment('.$row['sales_order_id'].',\''.$row["sales_order_no"].'\')"><i class="fa fa-exclamation-triangle"></i></button>';
			}
		}else{
			if(in_array(SALES_ORDER_SLUG_APPROVE,$bulkAccessArray)){
				$po_apprv_btn='<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Sales Order" data-toggle="tooltip" data-placement="top" onClick="open_po_approv_payment('.$row['sales_order_id'].',\''.$row["sales_order_no"].'\')"><i class="fa fa-exclamation-triangle"></i></button>';
			}
			if(in_array(SALES_ORDER_SLUG_FINAL_APPROVE,$bulkAccessArray)){
				$po_apprv_btn='<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Sales Order" data-toggle="tooltip" data-placement="top" onClick="open_po_approv_payment('.$row['sales_order_id'].',\''.$row["sales_order_no"].'\')"><i class="fa fa-exclamation-triangle"></i></button>';
			}
		}
		$query="select (select IFNULL(sum(product_qty),0) as qty from tbl_sales_ordertrn as sosub  where sales_ordertrn_status=0 and sosub.sales_order_id=so.sales_order_id ) as soqty,(select IFNULL(sum(product_qty),0) as qty  from tbl_invoice as chall left join tbl_invoicetrn as chtrn on chtrn.invoice_id=chall.invoice_id where invoice_status=0 and chtrn.trancation_status=0 and chall.sales_order_id=so.sales_order_id) as invqty from tbl_sales_order as so where sales_order_status=0   and so.sales_order_id=".$row['sales_order_id']." and company_id=".$_SESSION['company_id'];
		$rs_dispatch=$dbcon->query($query);
		$rel=mysqli_fetch_assoc($rs_dispatch);
	
		if($row['order_accept_status']==1){
			if($row['short_close_status']==0){
				$short_close = '<button class="btn btn-xs btn-danger" data-original-title="Short Close SO" data-toggle="tooltip" data-placement="top" onClick="short_close_so('.$row['sales_order_id'].')"><i class="fa fa-repeat"></i></button>';
			}else{
				$short_close = '<button class="btn btn-xs btn-danger" data-original-title="SO Short Closed" data-toggle="tooltip" data-placement="top">SO Short Closed</button>';
				$invoicestatus = '';
			}
			$po_apprv_btn = '';
			// $oa_apprv_btn = '';
		}
		if($row["invoice_status"]=="1")
		{
			$invoicestatus='<a class="btn btn-xs btn-primary" data-original-title="Invoice Done" data-toggle="tooltip" data-placement="top" href="Javascript:;"><i class="fa fa-thumb-up">Invoice Done</i></a>';
			if($row['short_close_status']==1){
				$short_close = '<button class="btn btn-xs btn-danger" data-original-title="SO Short Closed" data-toggle="tooltip" data-placement="top">SO Short Closed</button>';
			}else{
				$short_close='';
			}
			$po_apprv_btn = '';
			$oa_apprv_btn = '';
		}
		else
		{
			if(in_array(SALES_ORDER_SLUG_DELETE,$bulkAccessArray)){
				$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_sales_order('.$row['sales_order_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			if(in_array(SALES_ORDER_SLUG_EDIT,$bulkAccessArray)){
				$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'salesorderedit/'.$row['sales_order_id'].'"><i class="fa fa-pencil"></i></a>';
			}


			$stagestatus='<a class="btn btn-xs btn-success" data-original-title="Stage" data-toggle="tooltip" data-placement="top" href="'.ROOT.'sales_order_stage/'.$row['sales_order_id'].'">
			Stage Process
			</a>';
		}
		$query1="select sum(work_order_qty) as perm, GROUP_CONCAT(sales_ordertrn_id) AS sotrn from tbl_sales_ordertrn as so where sales_ordertrn_status=0 and so.sales_order_id=".$row['sales_order_id']." and branch_id=".$_SESSION['branch_id'];
		$rs_dispatch1=$dbcon->query($query1);
		$rel1=mysqli_fetch_assoc($rs_dispatch1);

		if($rel1['perm']>0){
			$po_apprv_btn="";
		}
		$chktrn = $dbcon->query("SELECT * FROM tbl_set_main_process WHERE company_id = ".$_SESSION['company_id']." AND sales_order_trn_id IN (".$rel1['sotrn'].")");

		if(brp_mysqli_num_rows($chktrn)>0 && $row['approve_status']==1){
			$po_apprv_btn=$so_emend=$revise=$short_close="";
		}
		if($row['approve_status']==1){
			$edit = $delete  = '';
			$so_emend = '<a class="btn btn-xs btn-info" data-original-title="Sales Order Amend" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'sales_order_emend/'.$row['sales_order_id'].'"><i class="fa fa-repeat"></i></a>';
		}else if($row['approve_status']==3){
			$edit = $delete = '';
		}
		if($row['revise_status']==1){
			$revise = '<button type="button" class="btn btn-xs btn-primary" data-original-title="SO Revised" data-toggle="tooltip" data-placement="top"><i class="fa fa-check"></i></button>';
			$so_emend = '';
			$po_apprv_btn = '';
		}
		$stagestatus='';
		$tracking='<a class="btn btn-xs btn-default" data-original-title="Tracking" data-toggle="tooltip" data-placement="top" onClick="open_tracking_modal('.$row['sales_order_id'].',\''.$row["sales_order_no"].'\')" style="background-color: purple; color: white; border: 1px solid purple;" href="'.ROOT.CRM_ROOT.'sales_order_tracking/'.$row['sales_order_id'].'" target="_blank"><i class="fa fa-history"></i> Tracking</a>';

		if(in_array(SALES_ORDER_SLUG_UPDATE_USER,$bulkAccessArray)){
			$update_user = '<button class="btn btn-xs btn-success" data-original-title="Update User" data-toggle="tooltip" style="background-color:#f17438 !important;border-color:#f17438 !important" data-placement="top" onClick="preview_update_user('.$row['sales_order_id'].',\''.$row["sales_order_no"].'\',\''.$row['user_id'].'\')"><i class="fa fa-user-o" aria-hidden="true"></i></button>';
		}
		else{
			$update_user ='';
		}
		$order_review = '';$order_review_print='';
		if($getspecialConfiguration['libra_engineering_permission']==1){
			$order_review='<button class="btn btn-xs btn-primary" data-original-title="Order Review" data-toggle="tooltip" data-placement="top" onClick="order_review('.$row['sales_order_id'].')"><i class="fa fa-plus"></i></button>';
		}

		$row_data[] = $sales_order_print.' '.$order_acceptance_print.' '.$edit.' '.$delete.' '.$invoicestatus.' '.$stagestatus.' '.$po_apprv_btn.' '.$oa_apprv_btn.' '.$so_emend.' '.$revise.' '.$short_close.' '.$tracking.' '.$view_attach_doc.' '.$update_user.' '.$order_review;


		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {
// echo '<pre>';print_r($POST);exit;
	$companyConfiguration=getCompanyConfiguration($dbcon);
	if($companyConfiguration['branch_wise_manage']==1){
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	}else{
		$branch_id =$companyConfiguration['default_branch_id'];
	}
	if($POST['revise_status']){//Get Revise Count No
		$get_rev_cnt="select count(sales_order_id) as ttl_cnt,(select sales_order_no from tbl_sales_order where sales_order_id=".$POST['start_sales_order_id'].") as qt_no from tbl_sales_order where sales_order_status=0 and start_sales_order_id=".$POST['start_sales_order_id'];
		$rev_cnt=mysqli_fetch_assoc($dbcon->query($get_rev_cnt));
		$info['sales_order_no'] 			= $rev_cnt['qt_no']."/R-".$rev_cnt['ttl_cnt'];
		$info['start_sales_order_id']		= $POST['start_sales_order_id'];			
		$info['prev_sales_order_id']		= $POST['prev_sales_order_id'];	
		$upd_prev_qt_sts=$dbcon->query("UPDATE tbl_sales_order set revise_status=1 where sales_order_id=".$POST['prev_sales_order_id']);
	}
	else{
		// $info['quotation_no']		= load_quotation_no($dbcon);
		// Update Start series of No
		//$info['sales_order_no']	= load_common_no($dbcon,SALES_ORDER_SERIES);
		$info['sales_order_no']	= load_job_no($dbcon,$POST['invoicetype_id']);

		update_common_no($dbcon,SALES_ORDER_SERIES,$POST['invoicetype_id']);
	}

	// $query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$POST['invoicetype_id']);
	/*if(isset($POST['currency_enable'])){*/
		$info['currency_enable'] = 1;
		$curncy_trn['currency_id'] = $POST['currency_id'];
		$curncy_trn['currency_rate'] = $POST['currency_rate'];
	/*}else{
		$basecurrency = getbasecurrency($dbcon);
		$curncy_trn['currency_id'] = $basecurrency['currencyid'];
		$curncy_trn['currency_rate'] = 1;
	}*/
	$info['so_quotation_type']	= $POST['so_quotation_type'];
	$info['is_quotation']		= $POST['is_quotation'];
	$info['inquiry_type']	= $POST['inquiry_type'];
	$info['invoicetype_id']	= $POST['invoicetype_id'];
	$info['payment_terms']	= $POST['payment_terms'];
	$info['ref_no']			= $POST['ref_no'];
	$info['sales_order_date']	= date('Y-m-d',strtotime($POST['sales_order_date']));
	$info['delivery_date']	= date('Y-m-d',strtotime($POST['delivery_date']));
	$info['po_date']		= date('Y-m-d',strtotime($POST['po_date']));
	$info['sfg_date']		= date('Y-m-d',strtotime($POST['sfg_date']));
	$info['po_no']			= $POST['po_no'];
	$info['cust_id']		= $POST['cust_id'];
	$info['kind_attn']		= $POST['kind_attn'];
	$info['quot_type']		= $POST['quot_type'];
	$info['quotation_id']	= $POST['quotaion_id'];
	$info['transport_id']	= $POST['transport_id'];
	$info['with_out_stock_invoice']	= $POST['with_out_stock_invoice'];
	$info['delivery_type']  = $_POST['delivery_type'];
	$info['project_name']       = $POST['project_name'];
	$info['consignee_id']       = $POST['consignee_id'];
	$info['sales_type']			= $POST['sales_type'];
	$info['gst_type']			= $POST['gst_type'];
	$info['transid']			= $POST['transid'];
	$info['trans_add']			= $POST['trans_add'];	
		
	if($getspecialConfiguration['main_master']==1){
		$query_dy = "select * from tbl_master_field where master_field_status=0 and company_id=".$_SESSION['company_id']." order by priority ASC";
		$dy_result = $dbcon->query($query_dy);
		while($dy_row =  brp_mysqli_fetch_array($dy_result)){
			$field = $dy_row['master_field_db_name'];
			$info_field[$field]			= $POST[$field];	 		
		}
	}
	
	$info['apson_validity_date']			= date('Y-m-d',strtotime($POST['apson_validity_date']));
	$info['apson_trans_scop_of']			= $POST['apson_trans_scop_of'];
	$info['apson_dilivary_type']			= $POST['apson_dilivary_type'];

	$info['terms_type']			= $POST['terms_type'];
	$info['term_quotation_id']	= $POST['term_quotation_id'];

	$info['enable_transport'] = (isset($POST['transport_id_enable']) && ($POST['transport_id_enable']=='yes')) ? 1 : 0;
	
	$getspecialConfiguration=getspecialConfiguration($dbcon);
	if ($getspecialConfiguration['smpl_permission'] == '1') {
		if($POST['currency_id']==$_SESSION['currency_id']){
			$info['g_total']			= $POST['g_total'];
			$info['advance_payment']	= $POST['advance_payment'];
			$info['payable_amt']		= $POST['adv_amt'];
			$info['pending_amt']		= $POST['pen_amt'];	

			$info['g_total_conv']			= $POST['g_total']*$POST['currency_rate'];
			$info['advance_payment_conv']	= $POST['advance_payment']*$POST['currency_rate'];
			$info['payable_amt_conv']		= $POST['adv_amt']*$POST['currency_rate'];
			$info['pending_amt_conv']		= $POST['pen_amt']*$POST['currency_rate'];
		}else{
			$info['g_total']			= $POST['g_total']*$POST['currency_rate'];
			$info['advance_payment']	= $POST['advance_payment']*$POST['currency_rate'];
			$info['payable_amt']		= $POST['adv_amt']*$POST['currency_rate'];
			$info['pending_amt']		= $POST['pen_amt']*$POST['currency_rate'];

			$info['g_total_conv']			= $POST['g_total'];
			$info['advance_payment_conv']	= $POST['advance_payment'];
			$info['payable_amt_conv']		= $POST['adv_amt'];
			$info['pending_amt_conv']		= $POST['pen_amt'];
		}
		$info['payable_per']		= $POST['adv_per'];
	} else {
		if($POST['currency_id']==$_SESSION['currency_id']){
			$info['g_total']        = $POST['g_total'];
			$info['g_total_conv']   = $POST['g_total']*$POST['currency_rate'];
		} else {
			$info['g_total']        = $POST['g_total']*$POST['currency_rate'];
			$info['g_total_conv']   = $POST['g_total'];
		}
	}
	
   	$info['formulaid']      = $POST['formula_id']; //added by : Dimple
   	$info['remark']			= text_rnremove($POST['remark']);
   	$info['so_terms_and_condition']		= text_rnremove($_POST['so_terms_and_condition']);
   	$info['order_by']		= $POST['order_by'];
   	$info['cdate']			= date("Y-m-d H:i:s");
   	$info['company_id']		= $_SESSION['company_id'];
   	$info['user_id']		= $POST['user_id'];

   	if(isset($POST['jobwork_type'])){
   		$info['jobwork_type']		= $POST['jobwork_type'];
   	}

   	$info['orange']					= $POST['orange'];
	$info['mfg']					= $POST['mfg'];
	$info['trading']				= $POST['trading'];
	$info['repairing']				= $POST['repairing'];
	$info['other']					= $POST['other'];

	/*$info['orange_total']					= $POST['orange_total'];
	$info['mfg_total']					= $POST['mfg_total'];
	$info['trading_total']				= $POST['trading_total'];
	$info['repairing_total']				= $POST['repairing_total'];
	$info['other_total']					= $POST['other_total'];	*/

	$info['quot_general_terms_condition_content']= $_POST['quot_general_terms_condition_content'];
	$info['ship_address']= $_POST['ship_address'];
	

   	$inserestimateid=add_record('tbl_sales_order', array_merge($info,$curncy_trn), $dbcon, $branch_id);

	
	   
   	$info_term_ref['sales_order_id'] = $inserestimateid;
   	$info_term_ref['so_multi_quot_status']	= 0;
   	update_record('tbl_salesorder_multiple_quot', $info_term_ref,"sales_order_id=0 and so_multi_quot_status=3 and user_id=".$POST['user_id'] , $dbcon);
				//tbl_sales_ordertrn
   	if($inserestimateid){
   		$info_so_attach['attach_status'] = 0;
   		$info_so_attach['sales_order_id'] = $inserestimateid;
   		$upadate_so_attach = update_record('tbl_so_attch', $info_so_attach,"attach_status=3 and user_id=".$POST['user_id'] , $dbcon, $branch_id);

   		$cust_name = get_ledger_expense_by_id($dbcon, $POST['cust_id']);
   		tbl_transcation_entry($dbcon,"Sales Order",$POST['sales_order_no'],$inserestimateid,$cust_name,$POST['g_total']);
   	}
   	$getapprovalsetting = get_userwise_approval_setting($dbcon,2,$POST['user_id']);
   	if($companyConfiguration['automatic_approval_so']==1){
   		get_automatic_so_approval($dbcon,$inserestimateid);

   		$querycu="select cust.cust_email,quo.user_id,quo.cust_id from tbl_sales_order as quo
   		left join tbl_ledger as cust on cust.l_id=quo.cust_id
   		where quo.sales_order_id=".$inserestimateid;
   		$resultcu=$dbcon->query($querycu);
   		$relcu=brp_mysqli_fetch_assoc($resultcu);
   		$to_email_id=$relcu['cust_email'];

   		$cur_user_id = $relcu['user_id'];
   		$cur_user = getUserDetailById($dbcon, $cur_user_id);
   		$from_email_id = ($cur_user && $cur_user['common_email_id']) ? $cur_user['common_email_id'] : ADMIN_EMAIL;

   		$queryst="select email_sms_id from email_sms_template where email_module_id = 5 and status = 0 and company_id=".$_SESSION['company_id'];

   		$resultst=$dbcon->query($queryst);
   		$relst=brp_mysqli_fetch_assoc($resultst);

   		$mail_template = getEmailSMSTemplateById($dbcon, $relst['email_sms_id']);
   		$module_id = 5;
                     // var_dump($mail_template);
   		if($mail_template && $to_email_id) {
   			$querybcc="select email_cc,email_bcc from email_sms_template where email_sms_id=".$relst['email_sms_id'];
   			$resultbdd=$dbcon->query($querybcc);
   			$rel1=brp_mysqli_fetch_assoc($resultbdd);

   			if(!empty($rel1['email_cc'])){
   				$umix=explode(",",$rel1['email_cc']);
   				$umix=array_push($umix,$cur_user_id);
   				$uid=implode(",",$umix);
   			}else{
   				$uid=$cur_user_id;
   			}

   			$querybcc1="select GROUP_CONCAT(common_email_id SEPARATOR ';') as email_cc from users where user_id in (".$uid.")";
   			$resultbdd1=$dbcon->query($querybcc1);
   			$rel11=brp_mysqli_fetch_assoc($resultbdd1);

   			$querybcc2="select GROUP_CONCAT(common_email_id SEPARATOR ";") as email_bcc from users where user_id in (".$rel1['email_bcc'].")";
   			$resultbdd2=$dbcon->query($querybcc2);
   			$rel12=brp_mysqli_fetch_assoc($resultbdd2);
    	                 // Amish Soni Start 18-01-2021
   			$subject = $mail_template['email_subject'];
   			$content = $mail_template['email_content'];

   			$subject = replaceMergeFields($dbcon, $subject, $relcu['cust_id'], $module_id);
   			$content = replaceMergeFields($dbcon, $content, $relcu['cust_id'], $module_id);
    	                 // Amish Soni End 18-01-2021
   			$getspecialConfiguration=getspecialConfiguration($dbcon);
   			if($getspecialConfiguration['umaboy_permission']==1){
   				$attach = array();
   				$quot_file = umaboy_sales_order_print($dbcon, $inserestimateid,'Yes');
   				array_push($attach,$quot_file);
   				final_send_email($from_email_id, $to_email_id, $rel11['email_cc'],$rel12['email_bcc'], $subject, $content,$attach);
   				unlink('../../../view/upload/mail_attach/'.$quot_file);
   			}
   		}
   	}else{
   		//$getapprovalsetting = get_userwise_approval_setting($dbcon,2,$_SESSION['user_id']);
   		if(($getapprovalsetting['amount'] >= $POST['g_total']) && ($getapprovalsetting['auto_approval']==1)){
   			get_automatic_so_approval($dbcon,$inserestimateid);

   			$querycu="select cust.cust_email,quo.user_id,quo.cust_id from tbl_sales_order as quo
   			left join tbl_ledger as cust on cust.l_id=quo.cust_id
   			where quo.sales_order_id=".$inserestimateid;
   			$resultcu=$dbcon->query($querycu);
   			$relcu=brp_mysqli_fetch_assoc($resultcu);
   			$to_email_id=$relcu['cust_email'];

   			$cur_user_id = $relcu['user_id'];
   			$cur_user = getUserDetailById($dbcon, $cur_user_id);
   			$from_email_id = ($cur_user && $cur_user['common_email_id']) ? $cur_user['common_email_id'] : ADMIN_EMAIL;

   			$queryst="select email_sms_id from email_sms_template where email_module_id = 5 and status = 0 and company_id=".$_SESSION['company_id'];

   			$resultst=$dbcon->query($queryst);
   			$relst=brp_mysqli_fetch_assoc($resultst);

   			$mail_template = getEmailSMSTemplateById($dbcon, $relst['email_sms_id']);
   			$module_id = 5;
                     // var_dump($mail_template);
   			if($mail_template && $to_email_id) {
   				$querybcc="select email_cc,email_bcc from email_sms_template where email_sms_id=".$relst['email_sms_id'];
   				$resultbdd=$dbcon->query($querybcc);
   				$rel1=brp_mysqli_fetch_assoc($resultbdd);

   				if(!empty($rel1['email_cc'])){
   					$umix=explode(",",$rel1['email_cc']);
   					$umix=array_push($umix,$cur_user_id);
   					$uid=implode(",",$umix);
   				}else{
   					$uid=$cur_user_id;
   				}

   				$querybcc1="select GROUP_CONCAT(common_email_id SEPARATOR ';') as email_cc from users where user_id in (".$uid.")";
   				$resultbdd1=$dbcon->query($querybcc1);
   				$rel11=brp_mysqli_fetch_assoc($resultbdd1);

   				$querybcc2="select GROUP_CONCAT(common_email_id SEPARATOR ";") as email_bcc from users where user_id in (".$rel1['email_bcc'].")";
   				$resultbdd2=$dbcon->query($querybcc2);
   				$rel12=brp_mysqli_fetch_assoc($resultbdd2);
    	                 // Amish Soni Start 18-01-2021
   				$subject = $mail_template['email_subject'];
   				$content = $mail_template['email_content'];

   				$subject = replaceMergeFields($dbcon, $subject, $relcu['cust_id'], $module_id);
   				$content = replaceMergeFields($dbcon, $content, $relcu['cust_id'], $module_id);
    	                 // Amish Soni End 18-01-2021
   				$getspecialConfiguration=getspecialConfiguration($dbcon);
   				if($getspecialConfiguration['umaboy_permission']==1){
   					$attach = array();
   					$quot_file = umaboy_sales_order_print($dbcon, $inserestimateid,'Yes');
   					array_push($attach,$quot_file);
   					final_send_email($from_email_id, $to_email_id, $rel11['email_cc'],$rel12['email_bcc'], $subject, $content,$attach);
   					unlink('../../../view/upload/mail_attach/'.$quot_file);
   				}
   			}
   		}
   	}
   	if($companyConfiguration['automatic_approval_so']==1 && $companyConfiguration['automatic_approval_order_acceptance']==1){
   		get_automatic_oa_approval($dbcon,$inserestimateid);
   	}else{
   		if($getapprovalsetting['auto_approval']==1){
   			$getapprovalsetting = get_userwise_approval_setting($dbcon,3,$_SESSION['user_id']);
   			if(($getapprovalsetting['amount'] >= $POST['g_total']) && ($getapprovalsetting['auto_approval']==1)){
   				get_automatic_oa_approval($dbcon,$inserestimateid);
   			}
   		}
   	}
   	if(!$POST['revise_status']){
   		$upd_strt_qry=$dbcon->query("update tbl_sales_order set start_sales_order_id=".$inserestimateid." where sales_order_id=".$inserestimateid);
   	}

   //	$info_update['with_out_stock_invoice']	= $POST['with_out_stock_invoice'];
   	$info_update['sales_ordertrn_status']	= 0;
   	$info_update['sales_order_id']	= $inserestimateid;
   	$updateid=update_record('tbl_sales_ordertrn', array_merge($info_update,$curncy_trn),"sales_ordertrn_status=3 and user_id=".$POST['user_id'] , $dbcon, $branch_id);

   	$infoprojecttrn['sales_order_id']		= $inserestimateid;
   	$infoprojecttrn['salesorder_projecttrn_status'] = 0;
   	update_record('tbl_salesorder_project_trn', array_merge($infoprojecttrn,$curncy_trn),"sales_order_id=0 and salesorder_projecttrn_status in (0,3) and user_id=".$POST['user_id'] , $dbcon, $branch_id);
   	get_quotation_complete_sales_order($dbcon,$inserestimateid);
   	if(!empty($_FILES['po_document']['tmp_name'][0])) {
   		$imgresp = upload_so_image($_FILES,$dbcon,$inserestimateid);
   	}
   	$query="select * from tbl_ledger where l_id=".$POST['cust_id'];
   	$rel=mysqli_fetch_assoc($dbcon->query($query));

   	$cust_email = $rel['cust_email'];
				//po_approve_status
   	$info_q['qt_company_name']	= $rel['qt_company_name'];
   	$info_q['qt_com_mno']		= $rel['qt_com_mno'];
   	$info_q['qt_com_gstno']		= $rel['qt_com_gstno'];
   	$info_q['qt_com_addr']		= $rel['qt_com_addr'];
   	$info_q['qt_add_country']	= $rel['qt_add_country'];
   	$info_q['qt_add_state']		= $rel['qt_add_state'];
   	$info_q['qt_add_city']		= $rel['qt_add_city'];
   	$info_q['qt_po_no']			= $POST['po_no'];
   	$info_q['qt_po_date']		= date('Y-m-d',strtotime($POST['sales_order_date']));
   	$info_q['qt_delivery_date']	= date('Y-m-d',strtotime($POST['delivery_date']));
   	$info_q['qt_po_amount']		= $POST['g_total'];
   	$info_q['qt_po_attch']		= "demo.png";
   	$info_q['po_approve_status']		= 1;
   	$info_q['sales_order_id']			= $inserestimateid;
					//var_dump($info_q);
   	$updateid=update_record('tbl_quotation', $info_q, "quotation_id=".$POST['quotaion_id'], $dbcon, $branch_id);

	// JS
	$info_ledger_d = [];
	$info_ledger_d['l_id'] = $POST['cust_id'];
	$info_ledger_d['ledger_type'] = 1;
	$updatedLedgerId = update_record('tbl_ledger', $info_ledger_d,"l_id=".$POST['cust_id'] , $dbcon);

   	foreach ($POST['tc_id'] as $key => $name) {
   		$infotrm['tc_id']		= $POST['tc_id'][$key];
   		$infotrm['ref_tc_id']		= $POST['ref_tc_id'][$key];
   		$infotrm['tc_priority']		= $POST['tc_priority'][$key];
   		$infotrm['tc_details']		= $_POST['tc_details'][$key];
   		$infotrm['sales_order_id']	= $inserestimateid;
   		$infotrm['cdate']		= date("Y-m-d H:i:s");
   		$infotrm['user_id']		= $POST['user_id'];
   		$infotrm['company_id']	= $_SESSION['company_id'];
   		if(in_array($POST['tc_id'][$key],$POST['disp_term_flag'])){
   			$insertrmid=add_record('tbl_salesorder_terms_trn', $infotrm, $dbcon, $branch_id);
   		}
   	}
   

   	if($inserestimateid > 0){
   		foreach ($POST['bill_sundry_tax'] as $bill_sundry_tax_id => $bill_sundry_tax_amount) {
   			$info_sundry_tax['sundry_ledger_id']=$bill_sundry_tax_id;
   			//$info_sundry_tax['sundry_amount']=$bill_sundry_tax_amount;
   			$info_sundry_tax['sundry_voucher_id']=$inserestimateid;
   			$info_sundry_tax['sundry_voucher_type']=SO_VOUCHER;
   			$info_sundry_tax['sundry_voucher_table']='tbl_sales_order';
   			$info_sundry_tax['cdate']	= date("Y-m-d H:i:s");
   			$info_sundry_tax['user_id']	= $POST['user_id'];
   			$info_sundry_tax['company_id']	= $_SESSION['company_id'];

   			if($POST['currency_id']==$_SESSION['currency_id']){
    			$info_sundry_tax['sundry_amount']=$bill_sundry_tax_amount;
    			$info_sundry_tax['sundry_amount_conv']=$bill_sundry_tax_amount*$POST['currency_rate'];
    		}else{
    			$info_sundry_tax['sundry_amount']=$bill_sundry_tax_amount*$POST['currency_rate'];
    			$info_sundry_tax['sundry_amount_conv']=$bill_sundry_tax_amount;
    		}

   			$sundry_tax_insert=add_record('tbl_bill_sundry_transaction', array_merge($info_sundry_tax,$curncy_trn), $dbcon);
   		}
   		foreach ($POST['bill_sundry_addon'] as $bill_sundry_addon_id => $bill_sundry_addon_amount) {
   			$info_sundry_addon['sundry_ledger_id']=$bill_sundry_addon_id;
   			//$info_sundry_addon['sundry_amount']=$bill_sundry_addon_amount;
   			$info_sundry_addon['sundry_voucher_id']=$inserestimateid;
   			$info_sundry_addon['sundry_voucher_type']=SO_VOUCHER;
   			$info_sundry_addon['sundry_voucher_table']='tbl_sales_order';
   			$info_sundry_addon['cdate']	= date("Y-m-d H:i:s");
   			$info_sundry_addon['user_id']	= $POST['user_id'];
   			$info_sundry_addon['company_id']	= $_SESSION['company_id'];
						//print_r(array_merge($info_sundry_addon,$curncy_trn));

   			if($POST['currency_id']==$_SESSION['currency_id']){
    			$info_sundry_addon['sundry_amount']=$bill_sundry_addon_amount;
    			$info_sundry_addon['sundry_amount_conv']=$bill_sundry_addon_amount*$POST['currency_rate'];
    		}else{
    			$info_sundry_addon['sundry_amount']=$bill_sundry_addon_amount*$POST['currency_rate'];
    			$info_sundry_addon['sundry_amount_conv']=$bill_sundry_addon_amount;
    		}

   			$sundry_addon_insert=add_record('tbl_bill_sundry_transaction',array_merge($info_sundry_addon,$curncy_trn), $dbcon);
   		}
   		foreach($POST['bill_sundry_addon_tax'] as $addon_id=>$addon_value){
   			$addon_explode = explode("-",$addon_value);
   			
   			if($POST['currency_id']==$_SESSION['currency_id']){
    			$info_addon['sundry_gst_amount'] = $addon_explode[0];
    			$info_addon['sundry_gst_amount_conv'] = $addon_explode[0]*$POST['currency_rate'];
    		}else{
    			$info_addon['sundry_gst_amount'] = $addon_explode[0]*$POST['currency_rate'];
    			$info_addon['sundry_gst_amount_conv'] = $addon_explode[0];
    		}

   			$updateaddontaxid=update_record('tbl_bill_sundry_transaction', $info_addon,"sundry_voucher_table='tbl_sales_order' and isdelete=0 and sundry_voucher_id=".$inserestimateid." and sundry_ledger_id=".$addon_id." " , $dbcon);
   		}
   	}

   	/*Update Trasport and Eway trans Table Start by Dhruv*/
   	if($inserestimateid){
   		$transp_trn['transport_transaction_table_id'] = $inserestimateid;
   		$updatetcstrnid=update_record('tbl_transport_transaction',$transp_trn,"transport_transaction_table='tbl_sales_order' and transport_transaction_table_id = 0" , $dbcon);
   	}

   	if($inserestimateid){	
   		$arr['msg']="1";
   		$arr['eid']=$inserestimateid;
   		if($companyConfiguration['enable_email'] == '1'){

   			$to = array();

   			if (filter_var($cust_email, FILTER_VALIDATE_EMAIL)) {
   				$to = array($cust_email);
   			}

   			$all_to_email = get_to_email_from_user_id($dbcon,$_SESSION['user_id'],$to);
   			$subject = "On Receipt of Sales Order";
   			$message = "<p>Dear Sir, </p>";
   			$message.= "<p>Greetings from Umaboy!! At the outset, thank you for choosing UMABOY. We are in receipt of your Purchase Order for following Items from our Authorized Business Associates M/s</p>";
   			$message .= "<p>Your total payable amount will be Rs (".$POST['g_total'].") </p>";
   			$message.="<table border='1' style='border-collapse:collapse'>
   			<tr>
   			<th>Product</th>
   			<th>Qty</th>
   			<th>Rate</th>
   			<th>Total</th>
   			</tr>";

   			$sel_so_trn = $dbcon->query("select mst.*,sales_ordertrn_id,if(mst.project_wise=0,(select product_name from product_mst as pro where pro.product_id=mst.product_id) ,(select project_name from tbl_project_assign as proj where proj.project_assign_id=mst.product_id)) as product_name,cat.unit_name,mst.description,product_qty,product_rate,product_amount from tbl_sales_ordertrn as mst 
   				left join unit_mst as cat on cat.unitid=mst.rate_unit 
   				left join product_mst as product on product.product_id=mst.product_id  
   				where sales_ordertrn_status=0 and sales_order_id=".$inserestimateid);
   			while($r_so_trn=brp_mysqli_fetch_assoc($sel_so_trn))
   			{
   				if($r_so_trn['unit_id']===$r_so_trn['rate_unit']){
   					$sqty=$r_so_trn['product_qty'];
   				}else{
   					$sqty=$r_so_trn['product_conv_qty'];
   				}
   				$message.="<tr>
   				<th>".$r_so_trn['product_name']."</th>
   				<th>".$sqty."</th>
   				<th>".$r_so_trn['product_rate']."</th>
   				<th>".$r_so_trn['product_amount']."</th>
   				</tr>";
   			}
   			$message.="</table>";
   			$message .= "<p>delivery schedule on receipt of 30% advance payment. Please find the payment link to make the payment of 30%.
   			We thank you once again for considering UMABOY and request you to kindly get in touch for any query regarding sales or service.</p>";
   			$message .="<p>We thank you once again for considering UMABOY and request you to kindly get in touch for any query regarding sales or service.</p>";
   			$message.="<p>B Associate: 98 xxx xxxxx</p>";
   			$message.="<p>B Partner: 98 xxx xxxxx</p>";
   			$message.="<p>Area Manager: 98 xxx xxxxx</p>";	
   			$message.="<p>Regional Manager: 98 xxx xxxxx</p>";
   			$message.="<p>Looking forward to your continue relationship.</p>";	
   			$message.="<p>With Warm Regards</p>";
   			$message.="<p>Shree Umiya F Tech Machines</p>";	
   			$message.="<p>Ahmedabad – 98 xxx xxxxx</p>";	

   			if(!empty($to)){
   				send_mail($dbcon,$all_to_email,$subject, $message, $from_email = "",$ccmail=[], $attachment=[],$bccmail=[],1);	
   			}
   		}	
   	}
   	else
   	{
   		$arr['msg']="0";
   	}
   	echo json_encode($arr);
   }else if(strtolower($POST['mode']) == "stage"){
   	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
   	$completed_status_arr=explode(',', $POST['completedstatus']);

   	$info['sales_order_id']	= $POST['sales_order_id'];
   	$info['product_id']			= $POST['product_id'];

   	$info['cdate']			= date("Y-m-d H:i:s");
   	$info['user_id']		= $_SESSION['user_id'];
   	$info['company_id']		= $_SESSION['company_id'];

   	foreach($POST['stageid']  as $key=>$val){
   		$info['stage_id']=$val;
   		$info['plan']=($POST['plan'][$key]!='') ? $POST['plan'][$key]: '-';
						//exit;
   		if($POST['completed_date'][$key]!=''){
   			$info['completed_date']= date('Y-m-d',strtotime($POST['completed_date'][$key]));

   		}else{
   			$info['completed_date']=$POST['completed_date'][$key];
   		}

   		$info['product_qty']=$POST['product_qty'][$key];
   		$info['accept_qty']=$POST['accept_qty'][$key];
   		$info['reject_qty']=$POST['reject_qty'][$key];
   		$info['unitid']=$POST['unitid'][$key];
   		$info['instruction']=($POST['instruction'][$key]!='') ? $POST['instruction'][$key]: '-';
   		$info['notes']=($POST['notes'][$key]!='') ? $POST['notes'][$key]: '-';
   		$info['is_completed']=$completed_status_arr[$key];
					// print_r($info);
					// exit;
   		if($POST['accept_qty'][$key]!=''){
   			$inserestimateid=add_record('tbl_sales_order_stage', $info, $dbcon, $branch_id);
   		}
   	}
   	$arr['msg']="1";
   	echo json_encode($arr);
   }else if(strtolower($POST['mode']) == "edit") {
   	$companyConfiguration=getCompanyConfiguration($dbcon);
   	/*var_dump($POST);*/
   /*	if(isset($POST['currency_enable'])){*/
   		$curncy_trn['currency_enable'] = 1;
   		$curncy_trn['currency_id'] 		= $POST['currency_id'];
   		$curncy_trn['currency_rate'] 	= $POST['currency_rate'];
   	/*}else{
   		$basecurrency = getbasecurrency($dbcon);
   		$curncy_trn['currency_enable'] = 0;
   		$curncy_trn['currency_id'] = $basecurrency['currency_id'];
   		$curncy_trn['currency_rate'] = 1;
   	}*/
	if($companyConfiguration['branch_wise_manage']==1){
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	}else{
		$branch_id =$companyConfiguration['default_branch_id'];
	}
   

   	$info['inquiry_type']	= $POST['inquiry_type'];
   	$info['sales_order_no']	= $POST['sales_order_no'];
   	$info['ref_no']			= $POST['ref_no'];
   	$info['sales_order_date']	= date('Y-m-d',strtotime($POST['sales_order_date']));
   	$info['payment_terms']  = $_POST['payment_terms'];
   	$info['delivery_date']	= date('Y-m-d',strtotime($POST['delivery_date']));
   	$info['po_date']		= date('Y-m-d',strtotime($POST['po_date']));
   	$info['sfg_date']		= date('Y-m-d',strtotime($POST['sfg_date']));
   	$info['po_no']			= $POST['po_no'];
   	$info['cust_id']		= $POST['cust_id'];
   	$info['kind_attn']		= $POST['kind_attn'];
	$info['quot_type']		= $POST['quot_type'];

	$getspecialConfiguration=getspecialConfiguration($dbcon);
	if ($getspecialConfiguration['smpl_permission'] == '1') {	
		$info['payable_per']	= $POST['adv_per'];
		if($POST['currency_id']==$_SESSION['currency_id']){
			$info['g_total']                = $POST['g_total'];
			$info['g_total_conv']   = $POST['g_total']*$POST['currency_rate'];

			$info['g_total']				= $POST['g_total'];
			$info['advance_payment']		= $POST['advance_payment'];
			$info['payable_amt']			= $POST['adv_amt'];
			$info['pending_amt']			= $POST['pen_amt'];	

			$info['g_total_conv']			= $POST['g_total']*$POST['currency_rate'];
			$info['advance_payment_conv']	= $POST['advance_payment']*$POST['currency_rate'];
			$info['payable_amt_conv']		= $POST['adv_amt']*$POST['currency_rate'];
			$info['pending_amt_conv']		= $POST['pen_amt']*$POST['currency_rate'];
		}else{
			$info['g_total']				= $POST['g_total']*$POST['currency_rate'];
			$info['advance_payment']		= $POST['advance_payment']*$POST['currency_rate'];
			$info['payable_amt']			= $POST['adv_amt']*$POST['currency_rate'];
			$info['pending_amt']			= $POST['pen_amt']*$POST['currency_rate'];

			$info['g_total_conv']			= $POST['g_total'];
			$info['advance_payment_conv']	= $POST['advance_payment'];
			$info['payable_amt_conv']		= $POST['adv_amt'];
			$info['pending_amt_conv']		= $POST['pen_amt'];
		}
	} else {
		if($POST['currency_id']==$_SESSION['currency_id']){
			$info['g_total']        = $POST['g_total'];
			$info['g_total_conv']   = $POST['g_total']*$POST['currency_rate'];
		} else {
			$info['g_total']        = $POST['g_total']*$POST['currency_rate'];
			$info['g_total_conv']   = $POST['g_total'];
		}
	}

   	$info['remark']			= text_rnremove($POST['remark']);
   	$info['order_by']		= $POST['order_by'];
   	$info['project_name']   = $POST['project_name'];
   	$info['consignee_id']   = $POST['consignee_id'];
   	$info['so_terms_and_condition']		= text_rnremove($_POST['so_terms_and_condition']);
   	$info['cdate']			= date("Y-m-d H:i:s");
   	$info['user_id']		= $POST['user_id'];
   	$info['company_id']		= $_SESSION['company_id'];
   	$info['transport_id']	= $POST['transport_id'];
   	$info['delivery_type']  = $POST['delivery_type'];
   	$info['sales_type']			= $POST['sales_type'];
   	$info['gst_type']			= $POST['gst_type'];
   	$info['transid']			= $POST['transid'];
   	$info['trans_add']			= $POST['trans_add'];

	   $info['apson_validity_date']			= date('Y-m-d',strtotime($POST['apson_validity_date']));
	   $info['apson_trans_scop_of']			= $POST['apson_trans_scop_of'];
	   $info['apson_dilivary_type']			= $POST['apson_dilivary_type'];

   	$info['enable_transport'] = (isset($POST['transport_id_enable']) && ($POST['transport_id_enable']=='yes')) ? 1 : 0;

   	$info['orange']					= $POST['orange'];
	$info['mfg']					= $POST['mfg'];
	$info['trading']				= $POST['trading'];
	$info['repairing']				= $POST['repairing'];
	$info['other']					= $POST['other'];

	/*$info['orange_total']					= $POST['orange_total'];
	$info['mfg_total']					= $POST['mfg_total'];
	$info['trading_total']				= $POST['trading_total'];
	$info['repairing_total']				= $POST['repairing_total'];
	$info['other_total']					= $POST['other_total'];	*/


	$info['terms_type']				= $POST['terms_type'];
	$info['term_quotation_id']		= $POST['term_quotation_id'];
	$info['quot_general_terms_condition_content']= $_POST['quot_general_terms_condition_content'];
	$info['ship_address']= $_POST['ship_address'];



   	$branch_trn['branch_id'] = $branch_id;
   	update_record('tbl_sales_ordertrn', $branch_trn,"sales_order_id=".$POST['eid'] , $dbcon, $branch_id);

   	if(!empty($_FILES['po_document']['tmp_name'][0])) {
   		$imgresp = upload_so_image($_FILES,$dbcon,$POST['eid']);
   	}

   	$updateid=update_record('tbl_sales_order', array_merge($info,$curncy_trn),"sales_order_id=".$POST['eid'] , $dbcon, $branch_id);



   	if(!empty($_FILES['po_document']['tmp_name'][0])) {
   		$imgresp = upload_so_image($_FILES,$dbcon,$POST['eid']);
   	}

   	$deltrmid=delete_record('tbl_salesorder_terms_trn',"sales_order_id=".$POST['eid'], $dbcon, $branch_id);
   	foreach ($POST['tc_id'] as $key => $name) {
   		$infotrm['tc_id']			= $POST['tc_id'][$key];
   		$infotrm['ref_tc_id']		= $POST['ref_tc_id'][$key];
   		$infotrm['tc_priority']		= $POST['tc_priority'][$key];
   		$infotrm['tc_details']		= $POST['tc_details'][$key];
   		$infotrm['sales_order_id']	= $POST['eid'];
   		$infotrm['cdate']		= date("Y-m-d H:i:s");
   		if(in_array($POST['tc_id'][$key],$POST['disp_term_flag'])){
   			$insertrmid=add_record('tbl_salesorder_terms_trn', $infotrm , $dbcon, $branch_id);
   		}
   	}

   	if($updateid)
   	{	
   		$arr['msg']="update";
   		$arr['eid']=$POST['eid'];
   	}
   	else
   		$arr['msg']=0;
   	echo json_encode($arr);
   }
   else if(strtolower($POST['mode']) == "delete") {
   	$getspecialConfiguration=getspecialConfiguration($dbcon);
   	$info['sales_order_status']	= 2;
   	$info1['sales_ordertrn_status']	= 2;
   	$updateestimateid=update_record('tbl_sales_order', $info,"sales_order_id=".$POST['eid'] , $dbcon);	
   	
   	if($getspecialConfiguration['smpl_permission']==1){
   		$query = "select sales_ordertrn_id from tbl_sales_ordertrn where sales_ordertrn_status=0 and sales_order_id=".$POST['eid'];
   		$result = $dbcon->query($query);
   		while($row = brp_mysqli_fetch_array($result)){
   			$info_wo_temp['status']  = 2;
    
		    $updatetrnid=update_record('work_order_reserve_temp',$info_wo_temp,"sales_ordertrn_id=".$row['sales_ordertrn_id'] , $dbcon);

			$info_reserve['stock_status'] = 2;

		    $updatetrnid=update_record('tbl_reserve_stock',$info_reserve,"sales_order_trn_id=".$row['sales_ordertrn_id'] , $dbcon);
		    
		    $info_product['sales_order_production_status'] = 2;
		    
		    $updatetrnid=update_record('tbl_sales_order_production_trn',$info_product,"sales_ordertrn_id=".$row['sales_ordertrn_id'] , $dbcon);
   		}
   	}

   	$updatetrancationid=update_record('tbl_sales_ordertrn', $info1,"sales_order_id=".$POST['eid'] , $dbcon);


   	$infoprojecttrn['salesorder_projecttrn_status']  = 2;
   	$updateprojecttrnid = update_record('tbl_salesorder_project_trn', $infoprojecttrn, "sales_order_id=".$POST['eid'], $dbcon);		
	
	   $qry10="select sales_ordertrn_id from tbl_sales_ordertrn as cert 
	   where sales_ordertrn_status=0 and sales_order_id=".$POST['eid'];
   $result10=$dbcon->query($qry10);
   while($res10=mysqli_fetch_assoc($result10)){
	delete_so_temp_allocate_stock($dbcon,$res10['sales_ordertrn_id']);
   }


   	if($updateestimateid)
   		echo "1";	
   	else
   		echo "0";			
   }
   else if(strtolower($POST['mode']) == "fieldadd") {

				//print_r($POST);exit;
   	$getspecialConfiguration=getspecialConfiguration($dbcon);
   	$companyConfiguration=getCompanyConfiguration($dbcon);
   	/*if(isset($POST['currency_enable']) && $POST['currency_enable']==1){*/
   		$curncy_trn['currency_enable'] = 1;
   		$curncy_trn['currency_id'] = $POST['currency_id'];
   		$curncy_trn['currency_rate'] = $POST['currency_rate'];
   	/*}else{
   		$basecurrency = getbasecurrency($dbcon);
   		$curncy_trn['currency_id'] = $basecurrency['currency_id'];
   		$curncy_trn['currency_rate'] = 1;
   	}*/

   	$company_state = get_company_data($dbcon,$_SESSION['company_id']);
   	$product_detail = get_product_detail($dbcon,$POST['product_id']);
				//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
   	//$sale_gst = get_tax_cat_by_hsn($dbcon,trim($_POST['product_hsn_code']));
   	$custLedgerDetails = get_cust_data_arr($dbcon,$POST['cust_id']);
	//var_dump($sale_gst);


   	if($POST['gst_type']==3){
   		$sale_gst['tax_gst']=0.1;
   		$sale_gst['tax_cat_id']=0;
   	}else if($POST['gst_type']==4){
   		$sale_gst['tax_gst']=0;
   		$sale_gst['tax_cat_id']=0;
   	}else if($POST['gst_type']==5){
   		$sale_gst['tax_gst']=5;
   		$sale_gst['tax_cat_id']=0;
   	}else if($POST['gst_type']==6){
   		$sale_gst['tax_gst']=12;
   		$sale_gst['tax_cat_id']=0;
   	}else if($POST['gst_type']==7){
   		$sale_gst['tax_gst']=18;
   		$sale_gst['tax_cat_id']=0;
   	}else if($POST['gst_type']==9){
   		$sale_gst['tax_gst']=9;
   		$sale_gst['tax_cat_id']=0;
   	}else if($POST['gst_type']==8){
   		$sale_gst['tax_gst']=24;
   		$sale_gst['tax_cat_id']=0;
   	}else{
   		$sale_gst = get_tax_cat_by_hsn($dbcon,trim($_POST['product_hsn_code'])); 
   	}


   	$cgst_tax_rate=0;$cgst_tax_rate_conv=0;
   	$sgst_tax_rate=0;$sgst_tax_rate_conv=0;
   	$igst_tax_rate=0;$igst_tax_rate_conv=0;
   	if($product_detail['product_gst'] == 'including'){
   		$prorate = $POST['product_rate'] * 100 /(100 + $sale_gst['tax_gst']);
   	}else{
   		$prorate = $POST['product_rate']; 
   	}
   	if(($company_state['stateid'] == $POST['cust_stateid']) && ($custLedgerDetails['enable_sez'] == 0)){
   		$gst = $sale_gst['tax_gst']/2;
   		$cgst_tax_per = $gst;
   		$cgst_tax_rate = ($gst*$POST['product_amount'])/100;
   		$cgst_tax_rate_conv = ($POST['currency_rate'] *$gst*$POST['product_amount'])/100;
   		$sgst_tax_per = $gst;
   		$sgst_tax_rate = ($gst*$POST['product_amount'])/100;
   		$sgst_tax_rate_conv = ($POST['currency_rate'] *$gst*$POST['product_amount'])/100;
   	}else{
   		$igst_tax_per = $sale_gst['tax_gst'];
   		$igst_tax_rate = ($sale_gst['tax_gst']*$POST['product_amount'])/100;
   		$igst_tax_rate_conv = ($POST['currency_rate'] *$sale_gst['tax_gst']*$POST['product_amount'])/100;
   	}

   	if($companyConfiguration['branch_wise_manage']==1){
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	}else{
		$branch_id =$companyConfiguration['default_branch_id'];
	}
   
   	$info1['quotation_id']  = $POST['quotation_id'];
   	$info1['quot_trn_id']   = $POST['quot_trn_id'];
   	$info1['inquiry_type']  = $POST['inquiry_type'];
   	$info1['product_id']	= $POST['product_id'];
   	$info1['description']	= $_POST['product_disc'];
   	$info1['product_disc']	= $_POST['product_disc'];
   	$info1['product_spec']	= $_POST['product_spec'];
   	$info1['product_hsn_code']	= $POST['product_hsn_code'];
	$info1['product_category_id']	= $POST['product_category_id'];
	$info1['rcat_id']			= $POST['rcat_id'];
   	if($getspecialConfiguration['elcon_permission'] ==1){
   		$info1['product_item_code']	= $POST['product_item_code'];
   	}
   	if($getspecialConfiguration['vipul_copper_permission'] ==1){
   		$info1['product_pices']		= $POST['product_pices'];
   		$info1['product_length']	= $POST['product_length'];
   	}

   	if($companyConfiguration['sales_wise_branch_planning'] == 1){
   		$info1['production_branch_id'] = 0;
   	}else{
   		$info1['production_branch_id'] = $branch_id;
   	}

   	$info1['product_qty']		= $POST['product_qty'];
   	$info1['remaning_invoice_qty']	= $POST['product_qty'];
   	$info1['product_conv_qty']	= $POST['product_conv_qty'];
   	$info1['remaning_invoice_conv_qty']	= $POST['product_conv_qty'];
   	$info1['unit_id']			= $POST['unit_id'];
   	$info1['conv_unit_id']		= $POST['conv_unitid'];
   	$info1['rate_unit']			= $POST['rate_unitid'];
   	$info1['delivery_type']		= $_POST['delivery_type'];

					//$info1['product_amount']	= $POST['product_amount'];
   	$info1['discount_per']		= $POST['discount_per'];
   	$info1['formulaid']			= $POST['formulaid'];
   	$info1['priority_status']	= $POST['priority_status'];

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

	//$info1['product_amount']	= $total=($POST['product_rate']*$POST['product_qty'])-$POST['product_discount'];
   	$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
   	$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
   	$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0 ;

   	if($POST['currency_id']==$company_state['currency_id']){
	   	$info1['product_rate']		= $prorate;
	   	$info1['product_discount']	= $POST['product_discount'];
	   	$info1['product_amount']	= $POST['product_amount'];
	   	$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
	   	$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
	   	$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
	   	$info1['total']				= $POST['product_amount']+$cgst_tax_rate+$sgst_tax_rate+$igst_tax_rate;
	   	
	   	$info1['product_rate_conv']		= $prorate*$POST['currency_rate'];
	   	$info1['product_discount_conv']	= $POST['product_discount']*$POST['currency_rate'];
	   	$info1['product_amount_conv']	= $POST['product_amount']*$POST['currency_rate'];
	   	$info1['cgst_tax_rate_conv']	= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
	   	$info1['sgst_tax_rate_conv']	= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
	   	$info1['igst_tax_rate_conv']	= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;
	   	$info1['total_conv']			= $info1['product_amount_conv']+$cgst_tax_rate_conv+$sgst_tax_rate_conv+$igst_tax_rate_conv;
  	}else{
  		$info1['product_rate']		= $prorate*$POST['currency_rate'];
	   	$info1['product_discount']	= $POST['product_discount']*$POST['currency_rate'];
	   	$info1['product_amount']	= $POST['product_amount']*$POST['currency_rate'];
	   	$info1['cgst_tax_rate']		= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
	   	$info1['sgst_tax_rate']		= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
	   	$info1['igst_tax_rate']		= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;
	   	$info1['total']				= $info1['product_amount']+$cgst_tax_rate_conv+$sgst_tax_rate_conv+$igst_tax_rate_conv;	

  		$info1['product_rate_conv']		= $prorate;
	   	$info1['product_discount_conv']	= $POST['product_discount'];
	   	$info1['product_amount_conv']	= $POST['product_amount'];
	   	$info1['cgst_tax_rate_conv']	= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
	   	$info1['sgst_tax_rate_conv']	= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
	   	$info1['igst_tax_rate_conv']	= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
	   	$info1['total_conv']			= $POST['product_amount']+$cgst_tax_rate+$sgst_tax_rate+$igst_tax_rate;
   	}

   	$info1['product_tax_cat']	= $sale_gst['tax_cat_id'];
   	if($companyConfiguration['trading_stock']!=0){
   		$info1['bom_status']		= 1;
   	}
   	//var_dump($product_detail['product_stock_count']);
   
   	//$info=get_product_tax($dbcon,$POST['taxable_value'],$POST['formulaid']);
   //	$info1=array_merge($info1,$info);
   	//var_dump($info1);
   	$table='tbl_sales_ordertrn';$tableid='sales_ordertrn_id';
   	if(!empty($POST['sales_order_id']))
   	{
   		$info1['user_id']	= $POST['user_id'];
   		$info1['sales_order_id']= $POST['sales_order_id'];
   		$table='tbl_sales_ordertrn';
   		$tableid='sales_ordertrn_id';
   		
   	}
   	else
   	{
   		$info1['user_id']	= $POST['user_id'];
   		$info1['sales_ordertrn_status']= 3;
   	}

   	if(isset($POST['product_attr']) && strtolower($POST['product_attr'])=='projectwise'){
   		$info1['project_wise']= 1;
   	}
   	if($product_detail['product_stock_count']!="yes"){
   		$info1['with_out_stock_invoice']= 1;
   	}
   	if($companyConfiguration['enable_negative_qty']!=0){
   		$info1['with_out_stock_invoice']= $companyConfiguration['enable_negative_qty'];
   	}
   	//var_dump($info1);

   	if(empty($POST['edit_id']))
   	{
		
		
   		$inserid=add_record($table, array_merge($info1,$curncy_trn), $dbcon,$branch_id);


		// JS : ADD DYNAMIC DATA FOR SALES ORDER TO PRODUCT SAVE
		$dynamic_data = $POST['dynamic_data'];

		$master_name_fields = [];
		foreach ($dynamic_data as $k => $dy_v) {
			$master_name_fields[$k] = $dy_v;
		}
		$master_name_fields['master_id']	= $inserid;
		$master_name_fields['master_type']	= "sales_order";
		$master_name_fields['cdate']		= date("Y-m-d H:i:s");
		$master_name_fields['user_id']		= $_SESSION['user_id'];
		$master_name_fields['company_id']	= $_SESSION['company_id'];
	   
		add_record('master_name_field', $master_name_fields, $dbcon,$branch_id);
   		
   		if($companyConfiguration['direct_sales_allocate']=='1' && $getspecialConfiguration['smpl_permission']!=1){
   			$direct_reserve = direct_salesorder_reserve_stock($dbcon,$inserid);
   		}

   		$updateinfo['salesorder_trn_id'] = $inserid;
   		$tax_trn_id=$inserid;

		$info_wo_temp['sales_ordertrn_id'] = $inserid;
		update_record('work_order_reserve_temp', $info_wo_temp, "status=0 and sales_ordertrn_id=0 and product_id=".$info1['product_id']." and rp_id=0 and user_id=".$_SESSION['user_id'] , $dbcon);

   		$updateins['prev_sales_ordertrn_id'] = $inserid; 
   		update_record('tbl_sales_ordertrn', $updateins, "salesorder_trn_id=".$inserid , $dbcon, $branch_id);

   		
   		$add_durva = special_durva_data_add($dbcon,$POST['product_id'],$POST['gst_type'],$POST['currency_rate'],$branch_id,$POST['inquiry_type'],$POST['currency_id'],$POST['cust_stateid'],$POST['cust_id'],$POST['sales_order_id'],$POST['user_id'],$POST['with_out_stock_invoice'],$POST['product_attr'],$POST['edit_id'],$inserid);		
   	}
   	else
   	{
   		$updateid=update_record($table, array_merge($info1,$curncy_trn),$tableid."=".$POST['edit_id'] , $dbcon, $branch_id);
		
		// Update DYNAMIC DATA FOR SALES ORDER TO PRODUCT SAVE
		$dynamic_data = $POST['dynamic_data'];

		$master_name_fields = [];
		foreach ($dynamic_data as $k => $dy_v) {
			$master_name_fields[$k] = $dy_v;
		}
		$master_name_fields['master_id']	= $POST['edit_id'];
		$master_name_fields['master_type']	= "sales_order";
		$master_name_fields['cdate']		= date("Y-m-d H:i:s");
		$master_name_fields['user_id']		= $_SESSION['user_id'];
		$master_name_fields['company_id']	= $_SESSION['company_id'];
	   

		$qry="select * from master_name_field where master_type='sales_order' and master_id=".$POST['edit_id'];
		$result=$dbcon->query($qry);
		$num_row=mysqli_num_rows($result);
		if ($num_row > 0) {
			update_record('master_name_field', $master_name_fields,"master_type='sales_order' and master_id=".$POST['edit_id'], $dbcon,$branch_id);
		} else {
			add_record('master_name_field', $master_name_fields, $dbcon,$branch_id);
		}
		
   		

   		$inserid=$POST['edit_id'];

   		if($companyConfiguration['direct_sales_allocate']=='1' && $getspecialConfiguration['smpl_permission']!=1){
   			$direct_reserve = direct_salesorder_reserve_stock($dbcon,$inserid);
   		}

   		$tax_trn_id=$inserid;
   		if(isset($POST['product_attr']) && strtolower($POST['product_attr'])=='projectwise' && $POST['old_product_id']!=$POST['product_id']){
   			$updatein['salesorder_projecttrn_status'] = 2; 
   			update_record('tbl_salesorder_project_trn', $updatein, "salesorder_trn_id=".$POST['edit_id']." and project_assign_id=".$POST['old_product_id'] , $dbcon, $branch_id);
   		}
   	}


   	if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
		$cl_id = get_ledger_by_name($dbcon,'CGST');
		$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid,"tbl_sales_ordertrn",$POST['product_id'],3,$POST['edit_id'],$branch_id,$POST['currency_id'],$POST['currency_rate'],$cgst_tax_rate_conv);
	}
	if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
		$cl_id = get_ledger_by_name($dbcon,'SGST');
		$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid,"tbl_sales_ordertrn",$POST['product_id'],3,$POST['edit_id'],$branch_id,$POST['currency_id'],$POST['currency_rate'],$sgst_tax_rate_conv);
	}
	if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
		$cl_id = get_ledger_by_name($dbcon,'IGST');
		$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid,"tbl_sales_ordertrn",$POST['product_id'],3,$POST['edit_id'],$branch_id,$POST['currency_id'],$POST['currency_rate'],$igst_tax_rate_conv);
	}

	// check for the addiotional tax on product Start -- dhaval
	$pro_amt = $POST['product_amount']*$POST['currency_rate'];
	$count_add_tax=get_check_addition_tax($dbcon,$sale_gst['tax_cat_id'],$POST['product_amount'],$inserid,$POST['product_id'],$POST['edit_id'],$branch_id,'tbl_sales_ordertrn',$POST['currency_id'],$POST['currency_rate'],$pro_amt);

	if($getspecialConfiguration['smpl_permission']==1){
		$stock_allocation = get_stock_reserve_allocate_so($dbcon,$inserid);
	}

   	$d_id=array();
   	if(strtolower($POST['delivery_type'])=="product_wise"){	
   		$total_delivery_qty=$POST['total_delivery_qty'];
   		$delivery_date=$POST['delivery_date'];
   		$arry_edit=$POST['arry_edit'];


   		for($i=0;$i<count($total_delivery_qty);$i++)
   		{
   			$info_dil['sales_ordertrn_id']	= $tax_trn_id;
   			$info_dil['delivery_date']			= date('Y-m-d',strtotime($delivery_date[$i]));
   			$info_dil['product_qty']			= $total_delivery_qty[$i];
   			$info_dil['unit_id']				= $info1['unit_id'];

   			$info_dil['user_id']				= $POST['user_id'];
   			$info_dil['cdate']					= date("Y-m-d h:i:s");
   			$info_dil['company_id']				= $_SESSION['company_id'];
						//$info_dil['branch_id']		=$_SESSION['company_id'];
						//var_dump($info);
   			$table_k='tbl_salesorder_delivery_date';$tableid_k='so_delivery_date_id';

   			if(!empty($arry_edit[$i])){
   				$updateid_k=update_record($table_k,$info_dil,"so_delivery_date_id=".$arry_edit[$i],$dbcon,$branch_id);
   				array_push($d_id,$arry_edit[$i]);
   			}else{
   				$inserid_k=add_record($table_k,$info_dil,$dbcon,$branch_id);
   				array_push($d_id,$inserid_k);
   			}
   		}
	}else{
   		$query_dd="select * from tbl_salesorder_delivery_date as mst 
   		where mst.sales_ordertrn_id=".$tax_trn_id." order by so_delivery_date_id desc";
   		$row_dd=$dbcon->query($query_dd);
   		$rel_dd=brp_mysqli_fetch_assoc($row_dd);

   		$info_dil['sales_ordertrn_id']		= $tax_trn_id;
   		$info_dil['delivery_date']			= date('Y-m-d',strtotime($POST['delivery_date']));
   		$info_dil['product_qty']			= $info1['product_qty'];
   		$info_dil['unit_id']				= $info1['unit_id'];

   		$info_dil['user_id']				= $POST['user_id'];
   		$info_dil['cdate']					= date("Y-m-d h:i:s");
   		$info_dil['company_id']				= $_SESSION['company_id'];
					//$info_dil['branch_id']			= $_SESSION['company_id'];
					//var_dump($info);
   		$table_k='tbl_salesorder_delivery_date';
   		$tableid_k='so_delivery_date_id';

   		if(!empty($rel_dd['so_delivery_date_id'])){
   			$updateid_k=update_record($table_k,$info_dil,"so_delivery_date_id=".$rel_dd['so_delivery_date_id'],$dbcon,$branch_id);
   			array_push($d_id,$rel_dd['so_delivery_date_id']);
   		}else{
   			$inserid_k=add_record($table_k,$info_dil,$dbcon,$branch_id);
   			array_push($d_id,$inserid_k);
   		}
   	}

   	$did=implode(",",$d_id);
   	$info_dil_1['po_delivery_date_status']="2";
   	$updateid_p=update_record($table_k,$info_dil_1,"sales_ordertrn_id=".$tax_trn_id." and so_delivery_date_id NOT IN (".$did.")",$dbcon,$branch_id);

   	if(isset($POST['product_attr']) && strtolower($POST['product_attr'])=='projectwise'){
   		$updateinfo['salesorder_projecttrn_status'] = 4; 
   		update_record('tbl_salesorder_project_trn', $updateinfo, "project_assign_id=".$POST['product_id']." and salesorder_projecttrn_status=3" , $dbcon, $branch_id);
   	}
	if($companyConfiguration['so_temp_auto_allocate']==1){
		auto_so_stock_allocate($dbcon,$inserid);
	}
	  
   }
   else if(strtolower($POST['mode'])== "get_series_no"){
   	$query="select * from tbl_invoicetype where status=0 and type_id=16 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
   	$result=$dbcon->query($query);
   	$row=mysqli_fetch_assoc($result);
   	echo $row['invoicetype_id'];
   }
   else if(strtolower($POST['mode'])== "load_invoiceno"){
   	$row=array();

   //	$sales_order_no = load_common_no($dbcon,SALES_ORDER_SERIES);
   	$sales_order_no = load_job_no($dbcon,$POST['typeid']);

	$row['invoiceno']=$sales_order_no;
   	$row['challanno']=$sales_order_no;
   	echo json_encode($row);
   }
   else if(strtolower($POST['mode']) == "formulavalue") {
   	$rate_total=0;$c_total=$POST['c_total'];
   	$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$POST['eid']." order by tax_value desc";
   	$row=$dbcon->query($qry);
   	$j=0;
				//$dis=$POST['total']*$POST['t_dis']/100;
   	$rate_total=$total=$POST['total'];
   	while($tax=mysqli_fetch_assoc($row))
   	{	
   		if(strpos(strtolower(" ".$tax['tax_name']), "excise")==true)
   		{
   			$rate=$total*$tax['tax_value']/100;
   			$total+=$rate;
   		}
   		else	
   		{
   			$rate=($total)*$tax['tax_value']/100;
   		}
   		echo '<div class="form-group">
   		<label class="col-md-3 control-label">'.$tax['tax_name'].'</label>
   		<div class="col-md-6 col-xs-12">
   		<input id="taxvalue'.$j.'" name="taxvalue'.$j.'" value= "'.$rate.'"type="text" class="form-control" readonly="readonly">
   		</div>
   		</div>
   		<input id="taxname'.$j.'" name="taxname'.$j.'" value= "'.$tax['tax_name'].'" type="hidden" class="form-control">';
   		$rate_total=$rate_total+$rate;
   		$j++;
   	}
   	$g_total=$rate_total+$c_total;
   	echo '<input id="rate" name="rate" value= "'.$g_total.'" type="hidden" class="form-control" >';
   }else if(strtolower($POST['mode'])== "load_productdata"){
   	$pid=$POST['eid'];
			//$qry="select * from tbl_product where product_id=".$POST['eid'];
   	$qry="select * from product_mst where product_id=$pid";
   	$result=$dbcon->query($qry);
   	$row=mysqli_fetch_assoc($result);

   	$row['current_stock']=get_current_stock_new($dbcon, $POST['eid'], $row['product_base_unit']);
   	$row['upcoming_stock']=get_current_stock_new($dbcon, $POST['eid'], $row['product_base_unit']);

   	$qry1="select c_add_state as lst,com.stateid as cst from tbl_customer as led 
   	left join tbl_cust_address as cust_addr On cust_addr.cust_id = led.cust_id
   	left join tbl_company as com on com.company_id=led.company_id
   	where led.cust_id =".$POST['cust_id'];
   	$result1=$dbcon->query($qry1);
   	$row1=mysqli_fetch_assoc($result1);

   	$qry3="SELECT h.hsn_id,h.hsn_code,h.sale_gst,t.tax_gst,t.tax_cat_id FROM `mst_hsn_code` as h left join tbl_tax_category as t on t.tax_cat_id=h.sale_gst where h.hsn_status=0 and h.hsn_id=".$row['product_hsn']." ";
   	$sale_gst=brp_mysqli_fetch_assoc($dbcon->query($qry3));

   	$row['tax_gst']=$sale_gst['tax_gst'];
   	$row['product_hsn']=$sale_gst['hsn_code'];

   	echo json_encode( $row );

   }	
   else if(strtolower($POST['mode'])== "load_podata"){
   	getpono($dbcon,$POST['cust_id']);
   }
   else if(strtolower($POST['mode'])== "delivery_detail"){
   	$product = "select pro.product_name from tbl_sales_ordertrn as trn 
   	left join product_mst as pro on pro.product_id=trn.product_id
   	where trn.sales_ordertrn_id=".$POST['so_trn_id'];
   	$pro_e   =$dbcon->query($product);  
   	$pro_r   = mysqli_fetch_array($pro_e); 

   	$delivery = "select * from tbl_salesorder_delivery_date where po_delivery_date_status=0 and sales_ordertrn_id=".$POST['so_trn_id'];
   	$delivery_e   =$dbcon->query($delivery);  
   	$str = '';
   	$str .= '<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
   	<tr>
   	<td><strong>Sr.no</strong></td>
   	<td><strong>Delivery Date</strong></td>
   	<td><strong>Delivery Qty</strong></td>
   	</tr>';
   	$i =1;
   	if(mysqli_num_rows($delivery_e)>0){
   		while($delivery_r   = mysqli_fetch_array($delivery_e)){
   			$str .='<tr>
   			<td>'.$i.'</td>
   			<td>'.date('d-m-Y',strtotime($delivery_r['delivery_date'])).'</td>
   			<td>'.$delivery_r['product_qty'].'</td>
   			</tr>';
   			$i++;
   		}
   	}else{
   		$str .='<tr>
   		<td style="text-align:center">No Data Yet..!!</td>
   		</tr>';
   	}
   	$str .='</table>';
   	$r['delivery_schedule'] = $str; 
   	$r['pro_name'] = $pro_r['product_name'];
   	echo json_encode($r);
   }
   else if(strtolower($POST['mode']) == "load_tempoutward") {
   	$str="";
   	$getspecialConfiguration=getspecialConfiguration($dbcon);
   	$companyConfiguration=getCompanyConfiguration($dbcon);
   	$sales_pro_search = explode(",", $companyConfiguration['sales_pro_search']);

	// Get all dynamic master fields data
	$qdy = $dbcon->query("SELECT master_field_id,master_field_db_name, master_field from tbl_master_field where master_field_status=0  order by priority ASC");
	$master_fields_data = brp_mysqli_fetch_all($qdy);
   
	$join = "";
	$select = "";
	foreach ($master_fields_data as $qd) {
		$join .= " LEFT JOIN tbl_master_field_value as mfv_".$qd['master_field_db_name']. " on mfv_".$qd['master_field_db_name'].".master_field_value_id=mnf.".$qd['master_field_db_name'];
			
		$select .= " , mfv_".$qd['master_field_db_name']. ".master_field_id as ".$qd['master_field_db_name']. "_master_field_id, mfv_".$qd['master_field_db_name'].".master_field_value as ".$qd['master_field_db_name']. "_master_field_value";
	}
		
   	$row=array();
   	if(empty($POST['so_id'])){
   		 $query="SELECT mst.*,sales_ordertrn_id,product.product_name,cat.unit_name,rat_u.unit_name as rate_unit_name,mst.description,product_qty,product_rate,product_amount,product.product_icode,dr.drawing_number,product.product_alias_name,category.cat_name, pcategory.cat_name as pcat_name,quot.quotation_no, mnf.* ".$select." FROM tbl_sales_ordertrn as mst 
   		left join unit_mst as cat on cat.unitid=mst.unit_id 
   		left join unit_mst as rat_u on rat_u.unitid=mst.rate_unit 
   		left join product_mst as product on product.product_id=mst.product_id
   		left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
   		left join tbl_category as category on category.cat_id=mst.product_category_id
   		left join tbl_category_reciclare as pcategory on pcategory.rcat_id=mst.rcat_id
   		left join tbl_quotation as quot on quot.quotation_id = mst.quotation_id   
		left join master_name_field as mnf on mnf.master_id=mst.sales_ordertrn_id and mnf.master_type='sales_order' " . $join . " 
   		WHERE sales_ordertrn_status=3 and mst.user_id=".$POST['user_id'];
   	}else{
   		$query="SELECT mst.*,sales_ordertrn_id,product.product_name,cat.unit_name,rat_u.unit_name as rate_unit_name,mst.description,product_qty,product_rate,product_amount,product.product_icode,dr.drawing_number,product.product_alias_name,category.cat_name, pcategory.cat_name as pcat_name, quot.quotation_no, mnf.* ".$select." FROM tbl_sales_ordertrn as mst 
   		left join unit_mst as cat on cat.unitid=mst.unit_id 
   		left join unit_mst as rat_u on rat_u.unitid=mst.rate_unit 
   		left join product_mst as product on product.product_id=mst.product_id
   		left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
   		left join tbl_category as category on category.cat_id=mst.product_category_id
   		left join tbl_category_reciclare as pcategory on pcategory.rcat_id=mst.rcat_id
   		left join tbl_quotation as quot on quot.quotation_id = mst.quotation_id 
		left join master_name_field as mnf on mnf.master_id=mst.sales_ordertrn_id and mnf.master_type='sales_order' " . $join . " 
   		WHERE mst.sales_ordertrn_status=0 and mst.sales_order_id=".$POST['so_id'];
   	}
   	
   	$result=$dbcon->query($query); 
   	$str .= ' <div class="form-group">
   	<div class="col-md-12 col-xs-12">
   	<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
   	<tr id="field">';
   	$companyConfiguration=getCompanyConfiguration($dbcon);
   	if($companyConfiguration['category_selection_active'] ==1){
   		$str.='<th class="text-center"width="8%">Category Name</th>';
   	}
  
   	$str.='<th class="text-center quotation_detail"width="12%">Quotation No</th>
   	<th class="text-center"width="25%">Product Name</th>';
   	if($getspecialConfiguration['reciclar']==1){
   		$str.='<th class="text-center"width="8%">Reciclare Category</th>';
   	}
   	$str.='<th class="text-center"width="8%" style="'.(($getspecialConfiguration['elcon_permission']==1) ? "" : "display:none").';">Item Code</th>
   	<th class="text-center"width="8%">HSN Code</th>
   	<th class="text-center"width="8%" style="'.(($getspecialConfiguration['vipul_copper_permission']==1) ? "" : "display:none").';">Length</th>
   	<th class="text-center"width="8%" style="'.(($getspecialConfiguration['vipul_copper_permission']==1) ? "" : "display:none").';">Pices</th>
   	<th class="text-center"width="8%">Qty</th>
   	<th class="text-center"width="8%">Rate <span class="currency_icon"></span></th>
   	<th class="text-center"width="5%" style="display:none">Per</th>
   	<th class="text-center"width="7%">Discount <span class="currency_icon"></span></th>
   	<th class="text-center" width="7%">Tax Details <span class="currency_icon"></span></th>
   	<th class="text-center"width="12%">Amount <span class="currency_icon"></span></th>
   	<th class="text-center"width="7%">Priority </th>
   	<th class="text-center"width="10%">Action</th>
   	</tr>';

   	$row['count'] = mysqli_num_rows($result);
   	if(mysqli_num_rows($result)>0)
   	{
   		$i=1;
   		while($rel=mysqli_fetch_assoc($result))
   		{
			
   			if(!empty($rel['currency_id'])){
				$currency=getcurrencydetail($dbcon,$rel['currency_id']);
			}else{
				$currency=getcurrencydetail($dbcon,$_SESSION['currency_id']);
			}


   			$cgst_tax="";				
   			$sgst_tax="";				
   			$igst_tax="";	
   			if($rel['unit_id']===$rel['rate_unit']){
   				$sqty=$rel['product_qty'];
   			}else{
   				$sqty=$rel['product_conv_qty'];
   			}



   			$currency_id = $rel['currency_id'];
			$rate_label = '';$product_amount_label = '';$product_total_label = '';$product_discount_label = '';
			$selectCu = "SELECT * FROM tbl_currency WHERE currency_id='".$currency_id."' ";
			$curenresult=$dbcon->query($selectCu);
			$vrel=brp_mysqli_fetch_assoc($curenresult);

			if($currency_id!=0){

				if($vrel['currency_id']!=$_SESSION['currency_id']){
					$str.= '<input type="hidden" id="currency_type_response" value="'.$vrel['currency_code'].'">';
				// 			$rate_label .= $vrel['currency_symbol'].' :' .$rel['product_rate']."<br>";
					$rate_label .=  $vrel['currency_symbol'].' :' .$rel['product_rate_conv'];

					// $product_amount_label .= $vrel['currency_symbol'].' :' .$rel['product_amount']."<br>";
					$product_amount_label .=  $vrel['currency_symbol'].' :' .$rel['product_amount_conv'];

					$product_total_label .= $vrel['currency_symbol'].' :' .$rel['product_amount_conv']."<br>";

					$product_discount_label .= $vrel['currency_symbol'].' :' .$rel['product_discount_conv']."<br>";
					//$product_total_label .=  $vrel['currency_symbol'].' :' .$rel['currency_total'];

					}else{
						$rate_label .= $vrel['currency_symbol'].' :' .number_format($rel['product_rate'],2,'.','');
						$product_amount_label .=$vrel['currency_symbol'].' :' .$rel['product_amount'];
						$product_total_label .= $vrel['currency_symbol'].' :' .$rel['product_amount'];
						$product_discount_label .= $vrel['currency_symbol'].' :' .$rel['product_discount']."<br>";
					}
				}else{
					$rate_label .= $_SESSION['currency_name'].' :' .number_format($rel['product_rate'],4,'.','');
					$product_amount_label .= $_SESSION['currency_name'].' :' .$rel['product_amount'];
					$product_total_label .= $_SESSION['currency_name'].' :' .$rel['product_amount'];
					$product_discount_label .= $vrel['currency_symbol'].' :' .$rel['product_discount']."<br>";
				}

   			if($rel['cgst_tax_per']!=0)
   			{
   				$cgst_tax="<Strong>CGST (".$rel['cgst_tax_per'].") : </strong>".$currency['currency_symbol']." ".(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['cgst_tax_rate'] : $rel['cgst_tax_rate_conv']).'<br>'	;
   			}
   			if($rel['sgst_tax_per']!=0)
   			{
   				$sgst_tax="<Strong>SGST (".$rel['sgst_tax_per'].") : </strong>".$currency['currency_symbol']." ".(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['sgst_tax_rate'] : $rel['sgst_tax_rate_conv']).'<br>';
   			}

   			if($rel['igst_tax_per']!=0)
   			{
   				$igst_tax="<Strong>IGST (".$rel['igst_tax_per'].") : </strong>".$currency['currency_symbol']." ".(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['igst_tax_rate'] : $rel['igst_tax_rate_conv']).'<br>';
   			}
   			
   			/*if(in_array('item',$sales_pro_search)){
   				$item_code = " -- (".$row['product_icode'].")";
   			} else{
   				$item_code = '';
   			}*/

   			if(in_array('drawing',$sales_pro_search)){
	            $drawing_number = " -- (".$rel['drawing_number'].")";
	        }
	        if(in_array('item',$sales_pro_search)){
	            $item_code = " -- (".$rel['product_icode'].")";
	        }
	        if(in_array('alias',$sales_pro_search)){
	            $alias = " -- (".$rel['product_alias_name'].")";
	        }

			$dyn_data = "";
			foreach ($master_fields_data as $qd) {
				$dyn_field = $qd['master_field_db_name']."_master_field_value";
				if ($rel[$dyn_field]) {
					$dyn_data .= $qd['master_field'] . " : ". $rel[$dyn_field] ."<br>";
				}
			}
   			

   			$str.= '<tr id="fieldtr'.$id.'" >';
   			if($companyConfiguration['category_selection_active'] ==1){
   				$str.='<td data-label="PRODUCT CATEGORY" style="vertical-align:top;" class="text-center">
   				'.$rel['cat_name'].'
   				</td>';	
   			}
   			
   			$str.='<td class="quotation_detail" data-label="QUOTATION NO" style="vertical-align:top;text-align:left">
   			'.$rel['quotation_no'].'
   			</td>
   			<td data-label="PRODUCT NAME" style="vertical-align:top;text-align:left">
   			'.$rel['product_name'].' '.$item_code.' '.$drawing_number.' '.$alias.'
   			'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.$rel['description']:'').'
			<table>
			<tr style="border-bottom:1px solid #D3D3D3;">
			<td style="font-size:15px;font-weight:bold;text-align:center;">Product Specification
			</td>
			</th>
			<tr>
			<td>
			'.$dyn_data .'
			</td>
			</tr>
			</table>
   			</td>';
   			if($getspecialConfiguration['reciclar']==1){
   				$str.='<td data-label="PRODUCT CATEGORY" style="vertical-align:top;" class="text-center">
   				'.$rel['pcat_name'].'
   				</td>';	
   			}
   			$str.='<td data-label="ITEM CODE" style="vertical-align:top; '.(($getspecialConfiguration['elcon_permission']==1) ? "" : "display:none").';" class="text-center">
   			'.$rel['product_item_code'].'
   			</td>
   			<td data-label="HSN CODE" style="vertical-align:top;" class="text-center">';
   			
   			if(empty($rel['product_hsn_code'])){
   				$str.= '-';
   			}else{
   				$str.= $rel['product_hsn_code'];
   			}

   			$str.='</td>
   			<td data-label="LENGTH" style="vertical-align:top; '.(($getspecialConfiguration['vipul_copper_permission']==1) ? "" : "display:none").';" class="text-center">
   			'.$rel['product_length'].'
   			</td>
   			<td data-label="PICES" style="vertical-align:top; '.(($getspecialConfiguration['vipul_copper_permission']==1) ? "" : "display:none").';" class="text-center">
   			'.$rel['product_pices'].'
   			</td>
   			<td data-label="QTY" style="vertical-align:top;" class="text-center">
   			'.$sqty.' '.$rel['rate_unit_name'].'
   			</td>
   			<td  data-label="RATE" style="vertical-align:top;" class="text-center">
   			'.$rate_label.'
   			</td>				
   			<td  data-label="PER" style="vertical-align:top;display:none" class="text-center">';
   			if(empty($rel['rate_unit_name'])){
   				$str.= '-';
   			}else{
   				$str.= $rel['rate_unit_name'];
   			}
   			$str.='</td>
   			<td data-label="DISCOUNT" style="vertical-align:top" class="text-right">
   			'.$product_discount_label.' ('.$rel['discount_per'].'%)
   			</td>

   			<td>'.$cgst_tax.''.$sgst_tax.''.$igst_tax.'</td>
   			<td data-label="AMOUNT" style="vertical-align:top" class="text-right">
   			'.$product_amount_label.'
   			</td>
   			<td data-label="PRIORITY" style="vertical-align:top" class="text-center">
   			'.$rel['priority_status'].'
   			</td>
   			<input type="hidden" name="amount[]" id="amount'.$i.'" value="'.(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['product_amount'] : $rel['product_amount_conv']).'"/>

   			<td data-label="ACTION" style="vertical-align:top">
   			<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['sales_ordertrn_id'].','.$rel['project_wise'].');" ><i class="fa fa-pencil"></i></button>
   			<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['sales_ordertrn_id'].','.$rel['project_wise'].');" id="fieldremove'.$i.'">X</button>';
   			if(strtolower($rel['delivery_type']) == 'product_wise'){
   				$str.= '<button type="button" class="btn btn-round btn-primary btn-xs" onclick="delivery_detail('.$rel['sales_ordertrn_id'].','.$rel['project_wise'].');" ><i class="fa fa-eye" aria-hidden="true"></i> </button>';
   			}
   			$str.='</td>		
   			</tr>';
   			$i++;
   		}
   	}
   	else{
   		$str.= '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
   	}
   	$str.= '

   	</table>			 
   	</div>

   	</div>	';

   	//$row['res'] = $str;
   	echo $str;
   	//echo json_encode($row);
   }
   else if(strtolower($POST['mode']) == "load_tempoutward_durva") {
   	$str="";
   	$getspecialConfiguration=getspecialConfiguration($dbcon);
   	$companyConfiguration=getCompanyConfiguration($dbcon);
   	$row=array();
   	if(empty($POST['so_id'])){
   		$query="SELECT mst.*,sales_ordertrn_id,product.product_name,cat.unit_name,rat_u.unit_name as rate_unit_name,mst.description,product_qty,product_rate,product_amount,product.product_icode,category.cat_name FROM tbl_sales_ordertrn as mst 
   		left join unit_mst as cat on cat.unitid=mst.unit_id 
   		left join unit_mst as rat_u on rat_u.unitid=mst.rate_unit 
   		left join product_mst as product on product.product_id=mst.product_id
   		left join tbl_category as category on category.cat_id=mst.product_category_id   
   		WHERE sales_ordertrn_status=3 and pid=0 and mst.user_id=".$POST['user_id'];
   	}else{
   		$query="SELECT mst.*,sales_ordertrn_id,product.product_name,cat.unit_name,rat_u.unit_name as rate_unit_name,mst.description,product_qty,product_rate,product_amount,product.product_icode,category.cat_name FROM tbl_sales_ordertrn as mst 
   		left join unit_mst as cat on cat.unitid=mst.unit_id 
   		left join unit_mst as rat_u on rat_u.unitid=mst.rate_unit 
   		left join product_mst as product on product.product_id=mst.product_id
   		left join tbl_category as category on category.cat_id=mst.product_category_id 
   		WHERE sales_ordertrn_status=0 and pid=0 and sales_order_id=".$POST['so_id'];
   	}
   	/*echo $query;*/
   	$result=$dbcon->query($query);
   	$str .= ' <div class="form-group">
   	<div class="col-md-12 col-xs-12">
   	<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
   	<tr id="field">
   	<th class="text-center"width="5%">Sr.no.</th>
   	<th class="text-center"width="8%" style="'.(($getspecialConfiguration['vipul_copper_permission']==1) ? "" : "display:none").';">Category Name</th>
   	<th class="text-center"width="25%">Product Name</th>
   	<th class="text-center"width="8%" style="'.(($getspecialConfiguration['elcon_permission']==1) ? "" : "display:none").';">Item Code</th>
   	<th class="text-center"width="8%">HSN Code</th>
   	<th class="text-center"width="8%" style="'.(($getspecialConfiguration['vipul_copper_permission']==1) ? "" : "display:none").';">Length</th>
   	<th class="text-center"width="8%" style="'.(($getspecialConfiguration['vipul_copper_permission']==1) ? "" : "display:none").';">Pices</th>
   	<th class="text-center"width="8%">Qty</th>
   	<th class="text-center"width="10%">Rate <span class="currency_icon"></span></th>
   	<th class="text-center"width="6%" style="display:none">Per</th>
   	<th class="text-center"width="8%">Discount <span class="currency_icon"></span></th>
   	<th class="text-center" width="8%">Tax Details <span class="currency_icon"></span></th>
   	<th class="text-center"width="12%">Amount <span class="currency_icon"></span></th>
   	<th class="text-center"width="10%">Action</th>
   	</tr>';

   	$row['count'] = brp_mysqli_num_rows($result);
   	if(brp_mysqli_num_rows($result)>0)
   	{
   		$i=1;
   		while($rel=brp_mysqli_fetch_array($result))
   		{
   			if(!empty($rel['currency_id'])){
				$currency=getcurrencydetail($dbcon,$rel['currency_id']);
			}else{
				$currency=getcurrencydetail($dbcon,$_SESSION['currency_id']);
			}

   			$cgst_tax="";				
   			$sgst_tax="";				
   			$igst_tax="";	
   			if($rel['unit_id']===$rel['rate_unit']){
   				$sqty=$rel['product_qty'];
   			}else{
   				$sqty=$rel['product_conv_qty'];
   			}

   			$currency_id = $rel['currency_id'];
			$rate_label = '';$product_amount_label = '';$product_total_label = '';$product_discount_label = '';
			$selectCu = "SELECT * FROM tbl_currency WHERE currency_id='".$currency_id."' ";
			$curenresult=$dbcon->query($selectCu);
			$vrel=brp_mysqli_fetch_array($curenresult);

			if($currency_id!=0){

				if($vrel['currency_id']!=$_SESSION['currency_id']){
					$str.= '<input type="hidden" id="currency_type_response" value="'.$vrel['currency_code'].'">';
				// 			$rate_label .= $vrel['currency_symbol'].' :' .$rel['product_rate']."<br>";
					$rate_label .=  $vrel['currency_symbol'].' :' .$rel['product_rate_conv'];

					// $product_amount_label .= $vrel['currency_symbol'].' :' .$rel['product_amount']."<br>";
					$product_amount_label .=  $vrel['currency_symbol'].' :' .$rel['product_amount_conv'];

					$product_total_label .= $vrel['currency_symbol'].' :' .$rel['product_amount_conv']."<br>";

					$product_discount_label .= $vrel['currency_symbol'].' :' .$rel['product_discount_conv']."<br>";
					//$product_total_label .=  $vrel['currency_symbol'].' :' .$rel['currency_total'];

					}else{
						$rate_label .= $vrel['currency_symbol'].' :' .number_format($rel['product_rate'],2,'.','');
						$product_amount_label .=$vrel['currency_symbol'].' :' .$rel['product_amount'];
						$product_total_label .= $vrel['currency_symbol'].' :' .$rel['product_amount'];
						$product_discount_label .= $vrel['currency_symbol'].' :' .$rel['product_discount']."<br>";
					}
				}else{
					$rate_label .= $_SESSION['currency_name'].' :' .number_format($rel['product_rate'],4,'.','');
					$product_amount_label .= $_SESSION['currency_name'].' :' .$rel['product_amount'];
					$product_total_label .= $_SESSION['currency_name'].' :' .$rel['product_amount'];
					$product_discount_label .= $vrel['currency_symbol'].' :' .$rel['product_discount']."<br>";
				}

   			if($rel['cgst_tax_per']!=0)
   			{
   				$cgst_tax="<Strong>CGST (".$rel['cgst_tax_per'].") : </strong>".$currency['currency_symbol']." ".(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['cgst_tax_rate'] : $rel['cgst_tax_rate_conv']).'<br>'	;
   			}
   			if($rel['sgst_tax_per']!=0)
   			{
   				$sgst_tax="<Strong>SGST (".$rel['sgst_tax_per'].") : </strong>".$currency['currency_symbol']." ".(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['sgst_tax_rate'] : $rel['sgst_tax_rate_conv']).'<br>';
   			}

   			if($rel['igst_tax_per']!=0)
   			{
   				$igst_tax="<Strong>IGST (".$rel['igst_tax_per'].") : </strong>".$currency['currency_symbol']." ".(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['igst_tax_rate'] : $rel['igst_tax_rate_conv']).'<br>';
   			}
   			$sales_pro_search = explode(",", $companyConfiguration['sales_pro_search']);
   			if(in_array('item',$sales_pro_search)){
   				$item_code = " -- (".$row['product_icode'].")";
   			} else{
   				$item_code = '';
   			}

   			$str.= '<tr id="fieldtr'.$id.'" >
   			<td style="vertical-align:top;">'.$i.'</td>
   			<td data-label="PRODUCT CATEGORY" style="vertical-align:top; '.(($getspecialConfiguration['vipul_copper_permission']==1) ? "" : "display:none").';" class="text-center">
   			'.$rel['cat_name'].'
   			</td>
   			<td data-label="PRODUCT NAME" style="vertical-align:top;text-align:left">
   			'.$rel['product_name'].''.$item_code.'
   			'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.$rel['description']:'').'
   			</td>
   			<td data-label="ITEM CODE" style="vertical-align:top; '.(($getspecialConfiguration['elcon_permission']==1) ? "" : "display:none").';" class="text-center">
   			'.$rel['product_item_code'].'
   			</td>
   			<td data-label="HSN CODE" style="vertical-align:top;" class="text-center">';
   			
   			if(empty($rel['product_hsn_code'])){
   				$str.= '-';
   			}else{
   				$str.= $rel['product_hsn_code'];
   			}

   			$str.='</td>
   			<td data-label="LENGTH" style="vertical-align:top; '.(($getspecialConfiguration['vipul_copper_permission']==1) ? "" : "display:none").';" class="text-center">
   			'.$rel['product_length'].'
   			</td>
   			<td data-label="PICES" style="vertical-align:top; '.(($getspecialConfiguration['vipul_copper_permission']==1) ? "" : "display:none").';" class="text-center">
   			'.$rel['product_pices'].'
   			</td>
   			<td data-label="QTY" style="vertical-align:top;" class="text-center">
   			'.$rel['product_qty'].' '.$rel['rate_unit_name'].'
   			</td>
   			<td  data-label="RATE" style="vertical-align:top;" class="text-center">
   			'.$rate_label.'
   			</td>				
   			<td  data-label="PER" style="vertical-align:top;display:none" class="text-center">';
   			if(empty($rel['rate_unit_name'])){
   				$str.= '-';
   			}else{
   				$str.= $rel['rate_unit_name'];
   			}
   			$str.='</td>
   			<td data-label="DISCOUNT" style="vertical-align:top" class="text-right">
   			'.$product_discount_label.' ('.$rel['discount_per'].'%)
   			</td>

   			<td>'.$cgst_tax.''.$sgst_tax.''.$igst_tax.'</td>
   			<td data-label="AMOUNT" style="vertical-align:top" class="text-right">
   			'.$product_amount_label.'
   			</td>
   			<input type="hidden" name="amount[]" id="amount'.$i.'" value="'.(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['product_amount'] : $rel['product_amount_conv']).'"/>

   			<td data-label="ACTION" style="vertical-align:top">
   			<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['sales_ordertrn_id'].','.$rel['project_wise'].');" ><i class="fa fa-pencil"></i></button>
   			<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['sales_ordertrn_id'].','.$rel['project_wise'].');" id="fieldremove'.$i.'">X</button>';
   			if(strtolower($rel['delivery_type']) == 'product_wise'){
   				$str.= '<button type="button" class="btn btn-round btn-primary btn-xs" onclick="delivery_detail('.$rel['sales_ordertrn_id'].','.$rel['project_wise'].');" ><i class="fa fa-eye" aria-hidden="true"></i> </button>';
   			}
   			if($getspecialConfiguration['durva_permission']==1){
   				if($rel['pid']==0){
   					$str.= '&nbsp;<button type="button" class="btn btn-xs btn-primary" data-original-title="Add Accessories" data-toggle="tooltip" data-placement="top" onClick="open_accesorice_wise_product_list('.$rel['sales_ordertrn_id'].')">+</button>';
   				}
   			}
   			$str.='</td>		
   			</tr>';
			

			if(empty($POST['so_id'])){
		   		$query12="SELECT mst.*,sales_ordertrn_id,product.product_name,cat.unit_name,rat_u.unit_name as rate_unit_name,mst.description,product_qty,product_rate,product_amount,product.product_icode,category.cat_name FROM tbl_sales_ordertrn as mst 
		   		left join unit_mst as cat on cat.unitid=mst.unit_id 
		   		left join unit_mst as rat_u on rat_u.unitid=mst.rate_unit 
		   		left join product_mst as product on product.product_id=mst.product_id
		   		left join tbl_category as category on category.cat_id=mst.product_category_id   
		   		WHERE sales_ordertrn_status=3 and pid=".$rel['sales_ordertrn_id']." and mst.user_id=".$POST['user_id'];
		   	}else{
		   		$query12="SELECT mst.*,sales_ordertrn_id,product.product_name,cat.unit_name,rat_u.unit_name as rate_unit_name,mst.description,product_qty,product_rate,product_amount,product.product_icode,category.cat_name FROM tbl_sales_ordertrn as mst 
		   		left join unit_mst as cat on cat.unitid=mst.unit_id 
		   		left join unit_mst as rat_u on rat_u.unitid=mst.rate_unit 
		   		left join product_mst as product on product.product_id=mst.product_id
		   		left join tbl_category as category on category.cat_id=mst.product_category_id 
		   		WHERE sales_ordertrn_status=0 and pid=".$rel['sales_ordertrn_id']." and sales_order_id=".$POST['so_id'];
		   	}

		   	$result12=$dbcon->query($query12);

		   	$j=1;
   			while($rel12=brp_mysqli_fetch_array($result12))
	   		{
	   			if(!empty($rel12['currency_id'])){
					$currency=getcurrencydetail($dbcon,$rel12['currency_id']);
				}else{
					$currency=getcurrencydetail($dbcon,$_SESSION['currency_id']);
				}

	   			$cgst_tax="";				
	   			$sgst_tax="";				
	   			$igst_tax="";	
	   			if($rel12['unit_id']===$rel12['rate_unit']){
	   				$sqty=$rel12['product_qty'];
	   			}else{
	   				$sqty=$rel12['product_conv_qty'];
	   			}

	   			$currency_id = $rel12['currency_id'];
				$rate_label = '';$product_amount_label = '';$product_total_label = '';$product_discount_label = '';
				$selectCu = "SELECT * FROM tbl_currency WHERE currency_id='".$currency_id."' ";
				$curenresult=$dbcon->query($selectCu);
				$vrel12=brp_mysqli_fetch_array($curenresult);

				if($currency_id!=0){

					if($vrel12['currency_id']!=$_SESSION['currency_id']){
						$str.= '<input type="hidden" id="currency_type_response" value="'.$vrel12['currency_code'].'">';
					// 			$rate_label .= $vrel12['currency_symbol'].' :' .$rel12['product_rate']."<br>";
						$rate_label .=  $vrel12['currency_symbol'].' :' .$rel12['product_rate_conv'];

						// $product_amount_label .= $vrel12['currency_symbol'].' :' .$rel12['product_amount']."<br>";
						$product_amount_label .=  $vrel12['currency_symbol'].' :' .$rel12['product_amount_conv'];

						$product_total_label .= $vrel12['currency_symbol'].' :' .$rel12['product_amount_conv']."<br>";

						$product_discount_label .= $vrel12['currency_symbol'].' :' .$rel12['product_discount_conv']."<br>";
						//$product_total_label .=  $vrel12['currency_symbol'].' :' .$rel12['currency_total'];

						}else{
							$rate_label .= $vrel12['currency_symbol'].' :' .number_format($rel12['product_rate'],2,'.','');
							$product_amount_label .=$vrel12['currency_symbol'].' :' .$rel12['product_amount'];
							$product_total_label .= $vrel12['currency_symbol'].' :' .$rel12['product_amount'];
							$product_discount_label .= $vrel12['currency_symbol'].' :' .$rel12['product_discount']."<br>";
						}
					}else{
						$rate_label .= $_SESSION['currency_name'].' :' .number_format($rel12['product_rate'],4,'.','');
						$product_amount_label .= $_SESSION['currency_name'].' :' .$rel12['product_amount'];
						$product_total_label .= $_SESSION['currency_name'].' :' .$rel12['product_amount'];
						$product_discount_label .= $vrel12['currency_symbol'].' :' .$rel12['product_discount']."<br>";
					}

	   			if($rel12['cgst_tax_per']!=0)
	   			{
	   				$cgst_tax="<Strong>CGST (".$rel12['cgst_tax_per'].") : </strong>".$currency['currency_symbol']." ".(($rel12['currency_id']==$_SESSION['currency_id']) ? $rel12['cgst_tax_rate'] : $rel12['cgst_tax_rate_conv']).'<br>'	;
	   			}
	   			if($rel12['sgst_tax_per']!=0)
	   			{
	   				$sgst_tax="<Strong>SGST (".$rel12['sgst_tax_per'].") : </strong>".$currency['currency_symbol']." ".(($rel12['currency_id']==$_SESSION['currency_id']) ? $rel12['sgst_tax_rate'] : $rel12['sgst_tax_rate_conv']).'<br>';
	   			}

	   			if($rel12['igst_tax_per']!=0)
	   			{
	   				$igst_tax="<Strong>IGST (".$rel12['igst_tax_per'].") : </strong>".$currency['currency_symbol']." ".(($rel12['currency_id']==$_SESSION['currency_id']) ? $rel12['igst_tax_rate'] : $rel12['igst_tax_rate_conv']).'<br>';
	   			}
	   			$sales_pro_search = explode(",", $companyConfiguration['sales_pro_search']);
	   			if(in_array('item',$sales_pro_search)){
	   				$item_code = " -- (".$row['product_icode'].")";
	   			} else{
	   				$item_code = '';
	   			}

	   			$str.= '<tr id="fieldtr'.$id.'" >
	   			<td style="vertical-align:top;">'.$i.'.'.$j.'</td>
	   			<td data-label="PRODUCT CATEGORY" style="vertical-align:top; '.(($getspecialConfiguration['vipul_copper_permission']==1) ? "" : "display:none").';" class="text-center">
	   			'.$rel12['cat_name'].'
	   			</td>
	   			<td data-label="PRODUCT NAME" style="vertical-align:top;text-align:left">
	   			'.$rel12['product_name'].''.$item_code.'
	   			'.(!empty($rel12['description'])?'<br/><strong>Desc.</strong> :'.$rel12['description']:'').'
	   			</td>
	   			<td data-label="ITEM CODE" style="vertical-align:top; '.(($getspecialConfiguration['elcon_permission']==1) ? "" : "display:none").';" class="text-center">
	   			'.$rel12['product_item_code'].'
	   			</td>
	   			<td data-label="HSN CODE" style="vertical-align:top;" class="text-center">';
	   			
	   			if(empty($rel12['product_hsn_code'])){
	   				$str.= '-';
	   			}else{
	   				$str.= $rel12['product_hsn_code'];
	   			}

	   			$str.='</td>
	   			<td data-label="LENGTH" style="vertical-align:top; '.(($getspecialConfiguration['vipul_copper_permission']==1) ? "" : "display:none").';" class="text-center">
	   			'.$rel12['product_length'].'
	   			</td>
	   			<td data-label="PICES" style="vertical-align:top; '.(($getspecialConfiguration['vipul_copper_permission']==1) ? "" : "display:none").';" class="text-center">
	   			'.$rel12['product_pices'].'
	   			</td>
	   			<td data-label="QTY" style="vertical-align:top;" class="text-center">
	   			'.$rel12['product_qty'].' '.$rel12['rate_unit_name'].'
	   			</td>
	   			<td  data-label="RATE" style="vertical-align:top;" class="text-center">
	   			'.$rate_label.'
	   			</td>				
	   			<td  data-label="PER" style="vertical-align:top;display:none" class="text-center">';
	   			if(empty($rel12['rate_unit_name'])){
	   				$str.= '-';
	   			}else{
	   				$str.= $rel12['rate_unit_name'];
	   			}
	   			$str.='</td>
	   			<td data-label="DISCOUNT" style="vertical-align:top" class="text-right">
	   			'.$product_discount_label.' ('.$rel12['discount_per'].'%)
	   			</td>

	   			<td>'.$cgst_tax.''.$sgst_tax.''.$igst_tax.'</td>
	   			<td data-label="AMOUNT" style="vertical-align:top" class="text-right">
	   			'.$product_amount_label.'
	   			</td>
	   			<input type="hidden" name="amount[]" id="amount'.$i.'" value="'.(($rel12['currency_id']==$_SESSION['currency_id']) ? $rel12['product_amount'] : $rel12['product_amount_conv']).'"/>

	   			<td data-label="ACTION" style="vertical-align:top">
	   			<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel12['sales_ordertrn_id'].','.$rel12['project_wise'].');" ><i class="fa fa-pencil"></i></button>
	   			<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel12['sales_ordertrn_id'].','.$rel12['project_wise'].');" id="fieldremove'.$i.'">X</button>';
	   			if(strtolower($rel12['delivery_type']) == 'product_wise'){
	   				$str.= '<button type="button" class="btn btn-round btn-primary btn-xs" onclick="delivery_detail('.$rel12['sales_ordertrn_id'].','.$rel12['project_wise'].');" ><i class="fa fa-eye" aria-hidden="true"></i> </button>';
	   			}
	   			$str.='</td>		
	   			</tr>';
	   			$j++;
	   		}
	   		$i++;
   		}
   	}
   	else{
   		$str.= '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
   	}
   	$str.= '

   	</table>			 
   	</div>

   	</div>';

   	//$row['res'] = $str;
   	/*var_dump($row);*/
   	//echo json_encode($row);
   	echo $str;
   }
   else if(strtolower($POST['mode'])== "convert_qty")
   {
   	$row=array();
   	if($POST["type"]=="1"){
   		$type="base_unit";
   		$ret_qty=convert_stock($dbcon,$_POST['base_qty'],$POST['product_id'],$type);
   	}else if($POST["type"]=="2"){
   		$type="conv_unit";
   		$ret_qty=convert_stock($dbcon,$_POST['conv_qty'],$POST['product_id'],$type);
   	}else{
   		$ret_qty="0";
   	}
			//var_dump($ret_qty);
   	$ret_qty_new=number_format($ret_qty, 3, ".", "");
			//$ret_qty=$ret_qty;
			//echo $ret_qty;
   	$row['show_qty']=$ret_qty_new;
   	$row['hide_qty']=$ret_qty;
   	echo json_encode($row);
   }
   else if(strtolower($POST['mode']) == "getstages") {
   	$query="select tps.*,sm.stage_name from tbl_product_stage as tps
   	left join stage_mst as sm on sm.stage_id=tps.stage_id
   	where tps.party_product=".$POST['prid'];
   	$rs_cust=$dbcon->query($query);	

   	echo '<div class="form-group">
   	<div class="col-md-12 col-xs-12">
   	<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
   	<tr id="field">
   	<th class="text-center"width="7%">Stage Name</th>
   	<th class="text-center"width="8%">Weightage</th>
   	<th class="text-center"width="8%">Plan</th>
   	<th class="text-center"width="6%" >Completed Date</th>
   	<th class="text-center"width="10%">Product Qty</th>
   	<th class="text-center"width="10%">Accept Qty</th>

   	<th class="text-center"width="10%">Reject Qty</th>
   	<th class="text-center"width="8%">Unit</th>
   	<th class="text-center"width="6%">Instruction</th>
   	<th class="text-center"width="6%">Notes</th>

   	</tr>';
						//<th class="text-center"width="5%">Is Completed ?</th>
   	if(mysqli_num_rows($rs_cust)>0){
   		$k=1;
   		$key=0;
   		$s_id=[];
   		while($rel=mysqli_fetch_assoc($rs_cust))
   		{	
   			$readonly='';
   			array_push($s_id,$rel['stage_id']);
					//$s_id[]=$rel['stage_id'];
   			if($key==0){
						//echo $rel['stage_id'];
   				$maxqty=getmaxtqtystagewise($dbcon,$rel['stage_id'],$POST['prid'],$POST['sales_order_id']);
   			}else{
						//echo $s_id[$key];
   				$maxqty=getmaxtqtystagewise($dbcon,$s_id[$key-1],$POST['prid'],$POST['sales_order_id']);
						//exit;
   			}

   			$sales_order_pr_qty=getsalesorderprdctqty($dbcon,$POST['prid'],$POST['sales_order_id']);
   			if(is_null($maxqty)){
   				$maxqty=$sales_order_pr_qty;
   				if($key!=0){
   					$readonly="readonly";
   				}

						// }else{
						// 	$maxqty=$sales_order_pr_qty;
						// }
   			}else{
   				if($key!=0){
   					$maxqty=$sales_order_pr_qty-$maxqty;
   				}else{
							//$maxqty=$sales_order_pr_qty;
   				}
   			}
					// $check_status=getstagedata($dbcon,'is_completed',$POST['prid'],$POST['sales_order_id'],$rel['stage_id']);
					// $cheked=($check_status==1) ? 'checked': '';
   			$comp_date=getstagedata($dbcon,'completed_date',$POST['prid'],$POST['sales_order_id'],$rel['stage_id']);
   			if($comp_date=="0000-00-00"){
   				$comp_date='';
   			}

   			echo '<tr>
   			<td>'.$rel['stage_name'].'</td>
   			<td>'.$rel['stage_per'].'</td>

   			<td><input type="hidden" name="stageid[]" width="10px" class="form-control" value="'.$rel['stage_id'].'"><input type="textbox" name="plan[]" width="10px" class="form-control" ></td>
   			<td><input type="textbox" name="completed_date[]" class="form-control default-date-picker" ></td>
   			<td><input type="textbox" readonly name="product_qty[]" class="form-control" id="product_qty" value="'.$sales_order_pr_qty.'"></td>
   			<td><input type="textbox" id="acptqty'.$k.'" data-id="'.$k.'" name="accept_qty[]" class="form-control acptqty" max="'.$maxqty.'" '.$readonly.'></td>
   			<td><input type="textbox" name="reject_qty[]" class="form-control"  ></td>
   			<td><select class="select2"  title="Select Unit" placeholder="Unit" name="unitid[]" id="unitid">'.getunit($dbcon,getstagedata($dbcon,'unitid',$POST['prid'],$POST['sales_order_id'],$rel['stage_id'])).'</select></td>
   			<td><input type="textbox" name="instruction[]" class="form-control" ></td>
   			<td><input type="textbox" name="notes[]" class="form-control" ></td>

   			</tr>';
   			$k++;
   			$key++;
   		}
				//print_r($s_id);
   		exit;
				//<td><input type="checkbox" name="is_completed[]" data-id="'.$k.'" id="attribute'.$k.'" value="1" class="form-control attribute" '.$cheked.'></td>
   	}else{
   		echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
   	}
   	echo '</table></div></div>';
			//exit;
   }
   else if(strtolower($POST['mode'])== "delete_attch") {
   	$row=array();
   	$info['status']=2;	
   	$updateid=update_record('tbl_so_attch', $info, "so_attch_id=".$POST['so_attch_id'] , $dbcon);

   	if($updateid)
   		$row['res']="1";
   	else
   		$row['res']="0";
   	echo json_encode($row);
   }
   else if(strtolower($POST['mode'])== "preedit"){
	
	$qdy = $dbcon->query("SELECT master_field_id,master_field_db_name from tbl_master_field where master_field_status=0  order by priority ASC");
	$master_fields_data = brp_mysqli_fetch_all($qdy);

	$join = "";
	$select = "";
	foreach ($master_fields_data as $qd) {
		$join .= " LEFT JOIN tbl_master_field_value as mfv_".$qd['master_field_db_name']. " on mfv_".$qd['master_field_db_name'].".master_field_value_id=mnf.".$qd['master_field_db_name'];

		$select .= " , mfv_".$qd['master_field_db_name']. ".master_field_id as ".$qd['master_field_db_name']. "_master_field_id, mfv_".$qd['master_field_db_name'].".master_field_value as ".$qd['master_field_db_name']. "_master_field_value";
	}
   	
	// Get all dynamic data for sales order wise product 
	$q = $dbcon -> query("SELECT mst.*,pro.product_name,pro.product_gst,pro.parent_category,mnf.*".$select." FROM tbl_sales_ordertrn as mst left join product_mst as pro on mst.product_id=pro.product_id left join master_name_field as mnf on mnf.master_id=mst.sales_ordertrn_id and mnf.master_type='sales_order' " . $join . " WHERE sales_ordertrn_id = '$POST[id]'");
   	
	$r = $q->fetch_assoc();
	$r["master_fields_data"] = $master_fields_data;

   	echo json_encode($r);
   }
   else if(strtolower($POST['mode'])== "load_quotation_details"){
   	$companyConfiguration=getCompanyConfiguration($dbcon);
   	if($companyConfiguration['branch_wise_manage']==1){
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	}else{
		$branch_id =$companyConfiguration['default_branch_id'];
	}
   	//$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
   	$row['cust_id']=$cust_id=copy_ledger_cust($dbcon,$POST['id']);

   	$getspecialConfiguration=getspecialConfiguration($dbcon);

	$where='';
	if($getspecialConfiguration['durva_permission']==1){
		$where = ' and pid=0';
	}

   	$query_u="SELECT quot_won_user_id from tbl_quotation as tps 
   	 where tps.quotation_id=".$POST['id'];
   	$rs_cust_u=$dbcon->query($query_u);	
	$rel_u=brp_mysqli_fetch_array($rs_cust_u);
	$w_user_id=$rel_u['quot_won_user_id'];
   	$infoq['sales_ordertrn_status']=2;	
   	$updateid=update_record('tbl_sales_ordertrn', $infoq, "sales_order_id=0 and sales_ordertrn_status=3 and user_id=".$w_user_id , $dbcon);

   	$sales_dstat = "select sales_ordertrn_id tbl_sales_ordertrn where sales_order_id=0 and sales_ordertrn_status=3 and user_id=".$w_user_id;
   	$rs_sales_sst = $dbcon->query($sales_dstat);
	
	while($rs_drow = brp_mysqli_fetch_array($rs_sales_sst)){
		$info_drow['po_delivery_date_status'] = 2;
		$updateid=update_record('tbl_salesorder_delivery_date', $info_drow, "sales_ordertrn_id".$rs_drow['sales_ordertrn_id'] , $dbcon);		
   	}

   	delete_record('tbl_salesorder_project_trn',"quotation_id='".$POST['id']."' and user_id=".$w_user_id, $dbcon);

   	$query="SELECT tps.*, pro.product_hsn, pro.product_conv_unit, hsn.hsn_code from tbl_quotation_trn as tps LEFT JOIN product_mst as pro ON pro.product_id = tps.product_id LEFT JOIN mst_hsn_code as hsn ON hsn.hsn_id = pro.product_hsn where tps.quot_trn_status=0 ".$where." and tps.quotation_id=".$POST['id'];
   	$rs_cust=$dbcon->query($query);	

   	if(brp_mysqli_num_rows($rs_cust)>0){
   		while($rel=brp_mysqli_fetch_array($rs_cust))
   		{
   			$product_detail = get_product_detail($dbcon,$rel['product_id']);
   			//var_dump($product_detail);
   			$info1['quotation_id']		= $rel['quotation_id'];
   			$info1['quot_trn_id']		= $rel['quot_trn_id'];
   			$info1['product_category_id']= $rel['cat_id'];
   			$info1['rcat_id']			= $rel['rcat_id'];
   			$info1['product_id']		= $rel['product_id'];
   			$info1['description']		= $rel['product_desc'];
   			$info1['product_disc']		= $rel['product_desc'];
   			$info1['product_spec']		= $rel['product_spec'];
   			$info1['product_hsn_code']	= $rel['hsn_code'];
   			$info1['conv_unit_id']		= $rel['product_conv_unit'];
   			$info1['product_qty']		= $rel['product_qty'];
   			$info1['remaning_invoice_qty']	= $rel['product_qty'];
   			$info1['remaning_invoice_conv_qty']	= $rel['product_conv_qty'];
   			$info1['product_conv_qty']	= $rel['product_conv_qty'];
   			$info1['pid']				= 0;

   			$info1['unit_id']			= $rel['unitid'];
   			$info1['rate_unit']			= $rel['rate_unit'];
   			$info1['unit_wise']			= $rel['unit_wise'];
   			$info1['discount_per']		= $rel['discount_per'];
   			$info1['formulaid']			= $rel['formulaid'];
   			$info1['quot_trn_id']		= $rel['quot_trn_id'];
   			$info1['inquiry_type']		= $rel['inquiry_type'];
   			$info1['project_wise']		= $rel['project_wise'];
   			$info1['cgst_tax_per']		= $rel['cgst_tax_per'];
   			$info1['sgst_tax_per']		= $rel['sgst_tax_per'];
   			$info1['igst_tax_per']		= $rel['igst_tax_per'];
   			$info1['delivery_type']		= $_POST['delivery_type']; 
   			$info1['currency_id']		= $rel['currency_id'];
   			$info1['currency_rate']		= $rel['currency_rate'];
   			
   			$info1['cgst_tax_rate']		= $rel['cgst_tax_rate'];
   			$info1['sgst_tax_rate']		= $rel['sgst_tax_rate'];
   			$info1['igst_tax_rate']		= $rel['igst_tax_rate'];

   			$info1['cgst_tax_rate_conv']= $rel['cgst_tax_rate_conv'];
   			$info1['sgst_tax_rate_conv']= $rel['sgst_tax_rate_conv'];
   			$info1['igst_tax_rate_conv']= $rel['igst_tax_rate_conv'];

   			$info1['product_rate']		= $rel['product_rate'];
   			$info1['product_discount']	= $rel['product_discount'];
   			$info1['product_amount']	= $rel['product_amount'];

   			$info1['product_rate_conv']		= $rel['product_rate_conv'];
   			$info1['product_discount_conv']	= $rel['product_discount_conv'];
   			$info1['product_amount_conv']	= $rel['product_amount_conv'];

   			$info1['product_tax_cat']		= $rel['product_tax_cat'];

   			$info1['orange']				= $rel['orange'];
   			$info1['mfg']					= $rel['mfg'];
   			$info1['trading']				= $rel['trading'];
   			$info1['repairing']				= $rel['repairing'];
   			$info1['other']					= $rel['other'];

   			$info1['orange_total']					= $rel['orange_total'];
			$info1['mfg_total']					= $rel['mfg_total'];
			$info1['trading_total']				= $rel['trading_total'];
			$info1['repairing_total']				= $rel['repairing_total'];
			$info1['other_total']					= $rel['other_total'];	


   			if($companyConfiguration['sales_wise_branch_planning'] == 1){
		   		$info1['production_branch_id'] = 0;
		   	}else{
		   		$info1['production_branch_id'] = $branch_id;
		   	}

   			$table='tbl_sales_ordertrn';$tableid='sales_ordertrn_id';

   			$info1['user_id']	= $w_user_id;
   			$info1['sales_ordertrn_status']= 3;
   			//var_dump($product_detail['product_stock_count']);
   			if($product_detail['product_stock_count']!="yes"){
		   		$info1['with_out_stock_invoice']= 1;
		   	}

   			$inserid=add_record($table, $info1, $dbcon,$branch_id);
   			$inser_field = 'sales_ordertrn_id';
   			$insert_tb = 'tbl_salesorder_delivery_date';
   			$refer_tb  = 'tbl_quotation_delivery_date';
   			$refer_st_field  = 'quo_delivery_date_status';
   			$refer_field  = 'quot_trn_id';
   			get_deliverydate_carry_forward($dbcon, $inserid, $inser_field, $insert_tb, $info1['quot_trn_id'], $refer_tb, $refer_st_field, $refer_field,$branch_id);
   			if($info1['inquiry_type']=='2'){
   				copy_quotation_project_trn_ro_salesorder_project_trn($dbcon, $POST['id'], $inserid, $branch_id);
   			}


   			if(($info1['cgst_tax_per'] != 0) && ($info1['cgst_tax_rate'] != 0) ){
	   			$cl_id = get_ledger_by_name($dbcon,'CGST');
	   			$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$info1['cgst_tax_per'],$info1['cgst_tax_rate'],$inserid,"tbl_sales_ordertrn",$rel['product_id'],3,$POST['edit_id'],$branch_id,$info1['currency_id'],$info1['currency_rate'],$info1['cgst_tax_rate_conv']);
	   		}
	   		if(($info1['sgst_tax_per'] != 0) && ($info1['sgst_tax_rate'] != 0) ){
	   			$cl_id = get_ledger_by_name($dbcon,'SGST');
	   			$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$info1['sgst_tax_per'],$info1['sgst_tax_rate'],$inserid,"tbl_sales_ordertrn",$rel['product_id'],3,$POST['edit_id'],$branch_id,$info1['currency_id'],$info1['currency_rate'],$info1['sgst_tax_rate_conv']);
	   		}
	   		if(($info1['igst_tax_per'] != 0) && ($info1['igst_tax_rate'] != 0) ){
	   			$cl_id = get_ledger_by_name($dbcon,'IGST');
	   			$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$info1['igst_tax_per'],$info1['igst_tax_rate'],$inserid,"tbl_sales_ordertrn",$rel['product_id'],3,$POST['edit_id'],$branch_id,$info1['currency_id'],$info1['currency_rate'],$info1['igst_tax_rate_conv']);
	   		}

				// check for the addiotional tax on product Start -- dhaval
	   		$pro_amt = $info1['product_amount']*$info1['currency_rate'];
	   		$count_add_tax=get_check_addition_tax($dbcon,$info1['product_tax_cat'],$info1['product_amount'],$inserid,$rel['product_id'],$POST['edit_id'],$branch_id,'tbl_sales_ordertrn',$info1['currency_id'],$info1['currency_rate'],$pro_amt);

   			if($getspecialConfiguration['durva_permission']==1){
   				$sub_pro = "SELECT tps.*, pro.product_hsn, pro.product_conv_unit, hsn.hsn_code from tbl_quotation_trn as tps LEFT JOIN product_mst as pro ON pro.product_id = tps.product_id LEFT JOIN mst_hsn_code as hsn ON hsn.hsn_id = pro.product_hsn where tps.quot_trn_status=0 and pid = ".$rel['quot_trn_id']." and tps.quotation_id=".$POST['id'];

   				$result_sub = $dbcon->query($sub_pro);
				while($sub_row = brp_mysqli_fetch_array($result_sub)){
					$info12['quotation_id']		= $sub_row['quotation_id'];
   					$info12['quot_trn_id']		= $sub_row['quot_trn_id'];
	   				$info12['product_id']		= $sub_row['product_id'];
		   			$info12['description']		= $sub_row['product_desc'];
		   			$info12['product_disc']		= $sub_row['product_desc'];
		   			$info12['product_spec']		= $sub_row['product_spec'];
		   			$info12['product_hsn_code']	= $sub_row['hsn_code'];
		   			$info12['conv_unit_id']	= $sub_row['product_conv_unit'];
		   			$info12['product_qty']		= $sub_row['product_qty'];
		   			$info12['remaning_invoice_qty']	= $sub_row['product_qty'];
		   			$info12['remaning_invoice_conv_qty']	= $sub_row['product_conv_qty'];
		   			$info12['product_conv_qty']	= $sub_row['product_conv_qty'];
		   			$info12['unit_id']			= $sub_row['unitid'];
		   			$info12['rate_unit']		= $sub_row['unitid'];
		   			$info12['discount_per']		= $sub_row['discount_per'];
		   			$info12['formulaid']		= $sub_row['formulaid'];
		   			$info12['quot_trn_id']		= $sub_row['quot_trn_id'];
		   			$info12['pid']				= $inserid;
		   			$info12['inquiry_type']		= $sub_row['inquiry_type'];
		   			$info12['project_wise']		= $sub_row['project_wise'];
		   			$info12['cgst_tax_per']		= $sub_row['cgst_tax_per'];
		   			$info12['sgst_tax_per']		= $sub_row['sgst_tax_per'];
		   			$info12['igst_tax_per']		= $sub_row['igst_tax_per'];

		   			$info12['currency_id']		= $sub_row['currency_id'];
		   			$info12['currency_rate']		= $sub_row['currency_rate'];
		   			
		   			$info12['cgst_tax_rate']		= $sub_row['cgst_tax_rate'];
		   			$info12['sgst_tax_rate']		= $sub_row['sgst_tax_rate'];
		   			$info12['igst_tax_rate']		= $sub_row['igst_tax_rate'];

		   			$info12['cgst_tax_rate_conv']= $sub_row['cgst_tax_rate_conv'];
		   			$info12['sgst_tax_rate_conv']= $sub_row['sgst_tax_rate_conv'];
		   			$info12['igst_tax_rate_conv']= $sub_row['igst_tax_rate_conv'];

		   			$info12['product_rate']		= $sub_row['product_rate'];
		   			$info12['product_discount']	= $sub_row['product_discount'];
		   			$info12['product_amount']	= $sub_row['product_amount'];

		   			$info12['product_rate_conv']		= $sub_row['product_rate_conv'];
		   			$info12['product_discount_conv']	= $sub_row['product_discount_conv'];
		   			$info12['product_amount_conv']	= $sub_row['product_amount_conv'];

		   			$info12['product_tax_cat']		= $sub_row['product_tax_cat'];
		   			$table='tbl_sales_ordertrn';$tableid='sales_ordertrn_id';

		   			$info12['user_id']	= $w_user_id;
		   			$info12['sales_ordertrn_status']= 3;

		   			$inserid1=add_record($table, $info12, $dbcon,$branch_id);

		   			if($info1['inquiry_type']=='2'){
		   				copy_quotation_project_trn_ro_salesorder_project_trn($dbcon, $POST['id'], $inserid1, $branch_id);
		   			}

		   			if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
			   			$cl_id = get_ledger_by_name($dbcon,'CGST');
			   			$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$info12['cgst_tax_per'],$info12['cgst_tax_rate'],$inserid1,"tbl_sales_ordertrn",$sub_row['product_id'],3,$POST['edit_id'],$branch_id,$info12['currency_id'],$info12['currency_rate'],$info12['cgst_tax_rate_conv']);
			   		}
			   		if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
			   			$cl_id = get_ledger_by_name($dbcon,'SGST');
			   			$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$info12['sgst_tax_per'],$info12['sgst_tax_rate'],$inserid1,"tbl_sales_ordertrn",$sub_row['product_id'],3,$POST['edit_id'],$branch_id,$info12['currency_id'],$info12['currency_rate'],$info12['sgst_tax_rate_conv']);
			   		}
			   		if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
			   			$cl_id = get_ledger_by_name($dbcon,'IGST');
			   			$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$info12['igst_tax_per'],$info12['igst_tax_rate'],$inserid1,"tbl_sales_ordertrn",$sub_row['product_id'],3,$POST['edit_id'],$branch_id,$info12['currency_id'],$info12['currency_rate'],$info12['igst_tax_rate_conv']);
			   		}

						// check for the addiotional tax on product Start -- dhaval
			   		$pro_amt = $info12['product_amount']*$info12['currency_rate'];
			   		$count_add_tax=get_check_addition_tax($dbcon,$info12['product_tax_cat'],$info12['product_amount'],$inserid1,$sub_row['product_id'],$POST['edit_id'],$branch_id,'tbl_sales_ordertrn',$info12['currency_id'],$info12['currency_rate'],$pro_amt);


		   		}
   			}
   			/* END */

   		}
   	}
   	$doc_query = $dbcon->query("SELECT inqattach.*, quot.inquiry_id FROM tbl_inq_attach as inqattach LEFT JOIN tbl_quotation as quot ON quot.inquiry_id = inqattach.inquiry_id WHERE quot.quotation_id = '".$POST['id']."' AND inqattach.inq_attach_status = 0");
   	if(mysqli_num_rows($doc_query)>0){
   		while($rels=mysqli_fetch_assoc($doc_query)){
   			$info2['attach_doc_name']	= $rels['inq_attch_doc_name'];
   			$info2['attach_file']		= $rels['inq_attch_file'];
   			$info2['branch_id']		= $rels['branch_id'];
   			$info2['user_id']			= $w_user_id;
   			$info2['company_id']		= $_SESSION['company_id'];
   			$info2['attach_status']= 3;

   			$table='tbl_so_attch';$tableid='so_attach_id';

   			$inserids=add_record($table, $info2, $dbcon);
   		}
   	}
   	$row['cust_data']=getcust($dbcon,$cust_id,'',0);
   	$row['w_user_id']=$w_user_id;

			//echo $cust_id;
			//var_dump($row['cust_id']);
   	echo json_encode($row);
   }
   else if(strtolower($POST['mode'])== "delete_data"){
   	$getspecialConfiguration=getspecialConfiguration($dbcon);
   	$row=array();
   	$info['sales_ordertrn_status']=2;	
   	$updateid=update_record("tbl_sales_ordertrn", $info,"sales_ordertrn_id=".$POST['eid'] , $dbcon);

	$delete_id=delete_record('master_name_field', "master_type='sales_order' and master_id=".$POST['eid'], $dbcon);

   	$infoprojecttrn['salesorder_projecttrn_status']  = 2;
   	$updateprojecttrnid = update_record('tbl_salesorder_project_trn', $infoprojecttrn, "salesorder_trn_id=".$POST['eid'], $dbcon);

   	$infosodeldate['po_delivery_date_status']  = 2;
   	$updatesodeldateid = update_record('tbl_salesorder_delivery_date', $infosodeldate, "sales_ordertrn_id=".$POST['eid'], $dbcon);

   	$info_tax['tx_status']=2;	
	$updatetax=update_record("tbl_tax_trn", $info_tax, "tx_transaction_type='tbl_sales_ordertrn' and tx_transaction_id=".$POST['eid'] , $dbcon);

	delete_so_temp_allocate_stock($dbcon,$POST['eid']);

	if($getspecialConfiguration['smpl_permission']==1){
		$info_wo_temp['status']  = 2;
    
	    $updatetrnid=update_record('work_order_reserve_temp',$info_wo_temp,"sales_ordertrn_id=".$POST['eid'] , $dbcon);

		$info_reserve['stock_status'] = 2;

	    $updatetrnid=update_record('tbl_reserve_stock',$info_reserve,"sales_order_trn_id=".$POST['eid'] , $dbcon);
	    
	    $info_product['sales_order_production_status'] = 2;
	    
	    $updatetrnid=update_record('tbl_sales_order_production_trn',$info_product,"sales_ordertrn_id=".$POST['eid'] , $dbcon);
	}


   	if($updateid)
   		$row['res']="1";
   	else
   		$row['res']="0";
   	echo json_encode($row);
   }
   else if(strtolower($POST['mode']) == "load_party_po_dtl") {
   	$qt_qry="select qt.*,country_name,state_name,city_name,led.company_name,led.cust_mobile,led.m_address,led.gst_no,led.l_id,led.credit_limit,led.credit_days from tbl_sales_order as qt
   	left join tbl_ledger as led on led.l_id=qt.cust_id
   	left join country_mst as country on country.countryid=led.countryid
   	left join state_mst as state on state.stateid=led.stateid
   	left join city_mst as city on city.cityid=led.cityid
   	where qt.sales_order_id=".$POST['sales_order_id'];
   	$qt_rel=mysqli_fetch_assoc($dbcon->query($qt_qry));

		//Party PO Details Table View
   	$str='';$stri='';
   	$str.='<div class="form-group">
   	<table class="display table table-bordered table-striped">
   	<tr>
   	<td colspan="2"><strong>Company Name:</strong> '.$qt_rel['company_name'].'</td>
   	<td><strong>Contact No.:</strong> '.$qt_rel['cust_mobile'].'</td>
   	</tr>
   	<tr>
   	<td colspan="2"><strong>Address:</strong> '.$qt_rel['m_address'].'</td>
   	<td><strong>GST No.:</strong> '.$qt_rel['gst_no'].'</td>
   	</tr>
   	<tr>
   	<td><strong>City:</strong> '.$qt_rel['city_name'].'</td>
   	<td><strong>State:</strong> '.$qt_rel['state_name'].'</td>
   	<td><strong>Country:</strong> '.$qt_rel['country_name'].'</td>
   	</tr>
   	<tr>
   	<td><strong>Sales order No:</strong> '.$qt_rel['sales_order_no'].'</td>
   	<td><strong>Sales Order Date:</strong> '.date("d-M-Y",strtotime($qt_rel["sales_order_date"])).'</td>
   	<td><strong>Sales Order Amount:</strong> '.$qt_rel['g_total'].'</td>
   	</tr>
   	<tr>
   	<td><strong>Outstanding Amount:</strong> '.load_amount_cust($dbcon, $qt_rel['l_id']).'</td>
   	<td><strong>Credit Limit:</strong> '.$qt_rel['credit_limit'].'</td>
   	<td><strong>Credit Days:</strong> '.$qt_rel['credit_days'].'</td>
   	</tr>
   	';
   	$str.='</table></div>
   	<hr/>
   	';
   	$query="SELECT mst.*,sales_ordertrn_id,if(mst.project_wise=0,(SELECT product_name FROM product_mst as pro WHERE pro.product_id=mst.product_id) ,(SELECT project_name FROM tbl_project_assign as proj WHERE proj.project_assign_id=mst.product_id)) as product_name,cat.unit_name,mst.description,product_qty,product_rate,product_amount,product.product_icode FROM tbl_sales_ordertrn as mst 
   	left join unit_mst as cat on cat.unitid=mst.unit_id 
   	left join product_mst as product on product.product_id=mst.product_id  
   	WHERE sales_ordertrn_status=0 and sales_order_id=".$POST['sales_order_id'];

   	$result=$dbcon->query($query);
   	$stri = ' <div class="form-group">
   	<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
   	<tr id="field">
   	<th class="text-center"width="25%">Product Name</th>
   	<th class="text-center"width="8%">HSN Code</th>
   	<th class="text-center"width="8%">Qty</th>
   	<th class="text-center"width="10%">Rate <span class="currency_icon"></span></th>
   	<th class="text-center"width="8%">Discount <span class="currency_icon"></span></th>
   	<th class="text-center" width="8%">Tax Details <span class="currency_icon"></span></th>
   	<th class="text-center"width="12%">Amount <span class="currency_icon"></span></th>
   	</tr>';
   	$i=1;
   	while($rel=mysqli_fetch_assoc($result))
   	{
   		$cgst_tax="";				
   		$sgst_tax="";				
   		$igst_tax="";	
   		if($rel['unit_id']===$rel['rate_unit']){
   			$sqty=$rel['product_qty'];
   		}else{
   			$sqty=$rel['product_conv_qty'];
   		}
   		if($rel['cgst_tax_per']!=0)
   		{
   			$cgst_tax="<Strong>CGST (".$rel['cgst_tax_per'].") : </strong>".$rel['cgst_tax_rate']."<br>";
   		}
   		if($rel['sgst_tax_per']!=0)
   		{
   			$sgst_tax="<Strong>SGST (".$rel['sgst_tax_per'].") : </strong>".$rel['sgst_tax_rate']."<br>";
   		}

   		if($rel['igst_tax_per']!=0)
   		{
   			$igst_tax="<Strong>IGST (".$rel['igst_tax_per'].") : </strong>".$rel['igst_tax_rate']."<br>";
   		}

   		$stri.= '<tr id="fieldtr'.$id.'" >
   		<td data-label="PRODUCT NAME" style="vertical-align:top;text-align:left">
   		'.$rel['product_name'].'
   		</td>
   		<td data-label="HSN CODE" style="vertical-align:top;" class="text-center">';
   		if(empty($rel['product_hsn_code'])){
   			$stri.= '-';
   		}else{
   			$stri.= $rel['product_hsn_code'];
   		}
   		$stri.='</td>
   		<td data-label="QTY" style="vertical-align:top;" class="text-center">
   		'.$rel['product_qty'].' '.$rel['unit_name'].'
   		</td>
   		<td  data-label="RATE" style="vertical-align:top;" class="text-center">
   		'.$rel['product_rate'].'
   		</td>
   		<td data-label="DISCOUNT" style="vertical-align:top" class="text-right">
   		'.$rel['product_discount'].' ('.$rel['discount_per'].'%)
   		</td>

   		<td>'.$cgst_tax.''.$sgst_tax.''.$igst_tax.'</td>
   		<td data-label="AMOUNT" style="vertical-align:top" class="text-right">
   		'.$rel['product_amount'].'
   		</td>	
   		</tr>';
   		$i++;
   	}
   	$stri.= '</table>			 
   	</div>
   	<hr/>';
   	$qt_rel['mod_so_comp_div_sec'] = $str;
   	$qt_rel['mod_so_pro_div_sec'] = $stri;
   	$qt_rel['load_amount_cust'] = load_amount_cust($dbcon, $qt_rel['l_id']);
   	echo json_encode($qt_rel);
   }else if(strtolower($POST['mode']) == "load_po_hist_datatable") {

   	$where='';
   	$where.="  and log.sales_order_id=".$POST['sales_order_id'];

   	$appData = array();
   	$i=1;
   	$aColumns = array('log.quot_aprv_log_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
   	$sIndexColumn = "log.quot_aprv_log_id";
   	$isWhere = array("log.quot_aprv_log_status=0 ".$where." ");
   	$sTable = "tbl_quot_po_aprv_log as log";			
   	$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
   	$hOrder = "log.quot_aprv_log_id desc";
   	include($incPath.'pagging.php');
   	$id=1;
   	foreach($sqlReturn as $row) {
   		$row_data = array();
   		$row_data[] = $row['sr'];
   		$row_data[] = $row['user_name'];

   		if($row['approve_status']=='1'){
   			$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
   		}
   		else{
   			$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Reject</div>';
   		}
   		$delete_btn = '';
   		if(in_array(ORDER_ACCEPTANCE_SLUG_DELETE,$bulkAccessArray) && $row['sr']==1){
   			$delete_btn = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_approve_log('.$POST['sales_order_id'].','.$row['quot_aprv_log_id'].','.$row['approve_status'].',1)"><i class="fa fa-trash-o"></i></button>';
   		}

   		$row_data[] = nl2br($row['approve_remark']);
   		$row_data[] = date("d-M-Y h:i A",strtotime($row['cdate']));
   		$row_data[] = $delete_btn;

   		$appData[] = $row_data;
   		$id++;
		//print_r($row_data);
   	}
   	$output['aaData'] = $appData;
		//print_r($output);
   	echo json_encode( $output );
   }
   else if(strtolower($POST['mode'])== "get_tax_on_total"){
   	$arr = get_tax_on_total($dbcon,$POST['total'],$POST['formulaid']);
   	echo json_encode($arr);
   }
   else if(strtolower($POST['mode']) == "add_po_apprv_hist") {
   	$companyConfiguration=getCompanyConfiguration($dbcon);
   	if($companyConfiguration['branch_wise_manage']==1){
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	}else{
		$branch_id =$companyConfiguration['default_branch_id'];
	}
   	//$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
   	$qt_qry="select quotation_id, g_total from tbl_sales_order as qt
   	where qt.sales_order_id=".$POST['sales_order_id'];
   	$qt_rel=mysqli_fetch_assoc($dbcon->query($qt_qry));

   	$info1['approve_remark']	= $_POST['approve_remark'];
   	$info1['approve_status']	= $POST['approve_status'];
   	$info1['quotation_id']		= $qt_rel['quotation_id'];
   	$info1['sales_order_id']	= $POST['sales_order_id'];
   	$info1['user_id']			= $_SESSION['user_id'];
   	$info1['company_id']		= $_SESSION['company_id'];
   	$inserid=add_record("tbl_quot_po_aprv_log", $info1, $dbcon, $branch_id);

		//Hide approve btn if not allowed
   	$final_btn_per=check_permission("#mod_po_per_div_sec",$_SESSION['user_id'],'final_aprv',$dbcon);
   	if($inserid){
   		if(in_array(SALES_ORDER_SLUG_FINAL_APPROVE,$bulkAccessArray)){

   			if($POST['approve_status']=='1'){
				$infoso['po_approve_status']	= 3;//Approved
				$infoso1['approve_status']	= 3;//Approved
				$infoso1['order_accept_status']	= 0;//Reject
			}
			else{
				$infoso['po_approve_status']	= 1;//Payment Pending
				$infoso1['approve_status']	= 1;//Reject
				$infoso1['order_accept_status']	= 3;//Reject
			}

			if(!empty($qt_rel['quotation_id'])){
				$updateid=update_record('tbl_quotation', $infoso,"sales_order_id=".$POST['sales_order_id'] , $dbcon, $branch_id);
			}
			$updateid=update_record('tbl_sales_order', $infoso1,"sales_order_id=".$POST['sales_order_id'] , $dbcon, $branch_id);
		}
		$companyConfiguration=getCompanyConfiguration($dbcon);
		if($companyConfiguration['automatic_approval_order_acceptance']==1){
			$qt_qry="select approve_status from tbl_sales_order as qt
			where qt.sales_order_id=".$POST['sales_order_id'];
			$qt_rel=mysqli_fetch_assoc($dbcon->query($qt_qry));

			if($qt_rel['approve_status'] == 3) {
				$infooapprv['approve_remark']	= 'Auto Approved by Admin';
				$infooapprv['approve_status']	= 1;
				$infooapprv['so_id']             = $POST['sales_order_id'];
				$infooapprv['user_id']			= $_SESSION['user_id'];
				$infooapprv['company_id']		= $_SESSION['company_id'];

				$insert_id=add_record("tbl_oa_aprv_log", $infooapprv, $dbcon);

				if($insert_id){
					$q = "select pro.bom_required,trn.sales_ordertrn_id from tbl_sales_order as so 
					left join tbl_sales_ordertrn as trn on trn.sales_order_id=so.sales_order_id
					left join product_mst as pro on pro.product_id = trn.product_id
					where so.sales_order_id=".$POST['sales_order_id'];				
					$re = brp_mysqli_query($dbcon,$q);
					while($row = mysqli_fetch_array($re)){
						if($row['bom_required']==1){
							$infop['bom_status'] = 0;
						}else{
							$infop['bom_status'] = 1;
						}
						$updateid=update_record('tbl_sales_ordertrn', $infop,"sales_ordertrn_id=".$row['sales_ordertrn_id'] , $dbcon, $branch_id);
					}
					$infosooa['order_accept_status'] = 1;

					$updateid=update_record('tbl_sales_order', $infosooa,"sales_order_id=".$POST['sales_order_id'] , $dbcon, $branch_id);

					$infoquots['po_approve_status'] = 3;
					$updateid=update_record('tbl_quotation', $infoquots,"sales_order_id=".$POST['sales_order_id'] , $dbcon, $branch_id);
				}
			}
		}else{
			$getapprovalsetting = get_userwise_approval_setting($dbcon,3,$_SESSION['user_id']);
			if(($getapprovalsetting['amount'] >= $qt_rel['g_total']) && ($getapprovalsetting['auto_approval']==1)){
				get_automatic_oa_approval($dbcon, $POST['sales_order_id']);
			}
		}

		$querycu="select cust.cust_email,quo.user_id,quo.cust_id from tbl_sales_order as quo
		left join tbl_ledger as cust on cust.l_id=quo.cust_id
		where quo.sales_order_id=".$info1['sales_order_id'];
		$resultcu=$dbcon->query($querycu);
		$relcu=brp_mysqli_fetch_assoc($resultcu);
		$to_email_id=$relcu['cust_email'];

		$cur_user_id = $relcu['user_id'];
		$cur_user = getUserDetailById($dbcon, $cur_user_id);
		$from_email_id = ($cur_user && $cur_user['common_email_id']) ? $cur_user['common_email_id'] : ADMIN_EMAIL;

		$queryst="select email_sms_id from email_sms_template where email_module_id = 5 and status = 0 and company_id=".$_SESSION['company_id'];

		$resultst=$dbcon->query($queryst);
		$relst=brp_mysqli_fetch_assoc($resultst);

		$mail_template = getEmailSMSTemplateById($dbcon, $relst['email_sms_id']);
		$module_id = 5;
                     // var_dump($mail_template);
		if($mail_template && $to_email_id) {
			$querybcc="select email_cc,email_bcc from email_sms_template where email_sms_id=".$relst['email_sms_id'];
			$resultbdd=$dbcon->query($querybcc);
			$rel1=brp_mysqli_fetch_assoc($resultbdd);

			if(!empty($rel1['email_cc'])){
				$umix=explode(",",$rel1['email_cc']);
				$umix=array_push($umix,$cur_user_id);
				$uid=implode(",",$umix);
			}else{
				$uid=$cur_user_id;
			}

			if(!empty($rel1['email_bcc'])){
				$umix1=explode(",",$rel1['email_bcc']);
				$umix1=array_push($umix1,$cur_user_id);
				$uid1=implode(",",$umix1);
			}else{
				$uid1=$cur_user_id;
			}

			$querybcc1="select GROUP_CONCAT(common_email_id SEPARATOR ';') as email_cc from users where user_id in (".$uid.")";
			$resultbdd1=$dbcon->query($querybcc1);
			$rel11=brp_mysqli_fetch_assoc($resultbdd1);

			$querybcc2="select GROUP_CONCAT(common_email_id SEPARATOR ';') as email_bcc from users where user_id in (".$uid1.")";
			$resultbdd2=$dbcon->query($querybcc2);
			$rel12=brp_mysqli_fetch_assoc($resultbdd2);
    	                 // Amish Soni Start 18-01-2021
			$subject = $mail_template['email_subject'];
			$content = $mail_template['email_content'];

			$subject = replaceMergeFields($dbcon, $subject, $relcu['cust_id'], $module_id);
			$content = replaceMergeFields($dbcon, $content, $relcu['cust_id'], $module_id);
    	                 // Amish Soni End 18-01-2021
			$getspecialConfiguration=getspecialConfiguration($dbcon);
			if($getspecialConfiguration['umaboy_permission']==1){
				$attach = array();
				$quot_file = umaboy_sales_order_print($dbcon, $POST['sales_order_id'],'Yes');
				array_push($attach,$quot_file);
				final_send_email($from_email_id, $to_email_id, $rel11['email_cc'],$rel12['email_bcc'], $subject, $content,$attach);
				unlink('../../../view/upload/mail_attach/'.$quot_file);
			}else{
    		// 		$print_name = get_print_path($dbcon,'3');
						// require_once('../../../print/view/'.$print_name.'.php');
						// $attach = array();
		    //             $quot_file = $print_name($dbcon, $POST['sales_order_id'],'Yes');
		    //             array_push($attach,$quot_file);
						// final_send_email($from_email_id, $to_email_id, $rel11['email_cc'],$rel12['email_bcc'], $subject, $content,$attach);
		    //             // var_dump('../../../print/view/'.$print_name.'.php');die;
						// unlink('../../../view/upload/mail_attach/'.$quot_file);
			}
		}
		if($POST['approve_status'] ==1){	
			$arr['msg']="1";
		}else{
			$arr['msg']="0";
		}
	}
	echo json_encode($arr);
}
else if(strtolower($POST['mode'])== "load_transport_detail_party_wise"){

	$arr['trans_detail'] =get_trasports_by_cust($dbcon,$POST['cust_id'],$POST['id']);
	echo json_encode($arr);
}
else if(strtolower($POST['mode'])== "load_inquiry_type_product"){
	$inquiry_type = $POST['inquiry_type'];
	if($inquiry_type=='1'){
		$arr['product_list'] = getproduct_typewise($dbcon,"",$_POST['pro_type'],$_POST['pro_search']);
	}elseif($inquiry_type=='2'){
		$getProjectList ='<option value="" >Choose Product</option>';
		$getProjectList .= getProjectList($dbcon,"");
		$arr['product_list'] = $getProjectList;

	}
	elseif($inquiry_type=='3'){
		$product_list = getproduct_typewise($dbcon,"",$_POST['pro_type'],$_POST['pro_search']);
		$product_list .= getProjectList($dbcon,"");
		$arr['product_list'] = $product_list;
	}

	echo json_encode($arr);
}
else if(strtolower($POST['mode'])== "add_project_data"){
	$companyConfiguration=getCompanyConfiguration($dbcon);
	if($companyConfiguration['branch_wise_manage']==1){
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	}else{
		$branch_id =$companyConfiguration['default_branch_id'];
	}
	//$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
    //if($inquiry_id==''){
	$project_assign_id = $POST['project_assign_id'];
	$inquiry_type = $POST['inquiry_type'];
	$inquiry_id = $POST['inquiry_id'];
	$quotation_id = $POST['quotation_id'];
	$sales_order_id = $POST['eid'];

	$update['salesorder_projecttrn_status'] = 2;
	update_record('tbl_salesorder_project_trn', $update, "project_assign_id=".$project_assign_id. " and salesorder_projecttrn_status = 3", $dbcon);

	$project_qry = "select * from `tbl_project_assigntrn` where `project_assign_id` = ".$project_assign_id." AND `project_assigntrn_status` = 0 and company_id='".$_SESSION['company_id']."' ";
	$proj_result=$dbcon->query($project_qry);
	if(brp_mysqli_num_rows($proj_result)>0)
	{
		while($rel=brp_mysqli_fetch_assoc($proj_result))
		{
			$info['inquiry_type'] = $inquiry_type; 
			$info['inquiry_id'] = $inquiry_id;
			$info['quotation_id'] = $quotation_id; 
			$info['sales_order_id'] = $sales_order_id; 

			$info['project_assign_id'] = $project_assign_id;
			$info['product_category_id'] = $rel['product_category_id'];
			$info['product_id'] = $rel['product_id'];
			$info['description'] = $rel['description'];
			$info['product_hsn_code'] = $rel['product_hsn_code'];

			$info['product_qty'] = $rel['product_qty'];
			$info['product_rate'] = $rel['product_rate'];
			$info['product_amount']    = $rel['product_amount'];
			$info['formulaid']         = $rel['formulaid'];

			$info1=get_product_common_tax($dbcon,$rel['product_amount'],$rel['formulaid']);
			$info=array_merge($info,$info1);

			$info['user_id'] = $_SESSION['user_id'];
			$info['company_id'] = $_SESSION['company_id'];
			$info['product_disc'] = $rel['product_disc'];
			$info['product_spec'] = $rel['product_spec'];

			if($sales_order_id!=''){
				$info['salesorder_projecttrn_status'] = 0;
			}else{
				$info['salesorder_projecttrn_status'] = 3;
			}
			add_record('tbl_salesorder_project_trn', $info, $dbcon, $branch_id);
		}
	}    
    //}
}
else if(brp_strtolower($POST['mode']) == "load_project_tempoutward") {

	if(empty($POST['eid'])){
		if($POST['salesorder_trn_id']!=''){
			$where = " salesorder_projecttrn_status in (0,3,4) and salesorder_trn_id='".$POST['salesorder_trn_id']."' ";
		}else{
			$where = " salesorder_projecttrn_status in (3,4) ";
		}	
		$query="select salesorder_projecttrn_id,product.product_name,mst.description,product_qty,product_rate,mst.* from tbl_salesorder_project_trn as mst 
		left join product_mst as product on product.product_id=mst.product_id  
		where $where and project_assign_id=".$POST['project_assign_id']." and mst.user_id=".$_SESSION['user_id'];
	}else{
		$query="select salesorder_projecttrn_id,product.product_name,mst.description,product_qty,product_rate,mst.* from tbl_salesorder_project_trn as mst 
		left join product_mst as product on product.product_id=mst.product_id  
		where salesorder_projecttrn_status=0 and sales_order_id=".$POST['eid']." and salesorder_trn_id='".$POST['salesorder_trn_id']."' and project_assign_id=".$POST['project_assign_id'];
	}

	$result=$dbcon->query($query);
	$companySettings = getCompanySettings($dbcon);
	$project_wise_item_rate = '';
	if($companySettings) {
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
	if($project_wise_item_rate=='Yes'){ 
		echo  '<th class="text-center"width="10%">Rate</th>';
		echo  '<th class="text-center"width="10%">Taxable Value</th>';
		echo  '<th class="text-center"width="10%">Tax</th>';
		echo  '<th class="text-center"width="10%">Total Amount</th>';
	}
	echo '<th class="text-center"width="10%">Action</th>
	</tr>';
	if(brp_mysqli_num_rows($result)>0)
	{
		$i=1;
		while($rel=brp_mysqli_fetch_assoc($result))
		{
			echo '<tr id="fieldtr'.$id.'" >
			<td data-label="PRODUCT NAME" style="vertical-align:top;text-align:left">
			'.$rel['product_name'].'
			'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.$rel['description']:'').'
			</td>

			<td data-label="HSN CODE" style="vertical-align:top;" class="text-center">';
			if(empty($rel['product_hsn_code'])){
				echo '-';
			}else{
				echo $rel['product_hsn_code'];
			}
			echo'</td>
			<td data-label="QTY" style="vertical-align:top;" class="text-center">
			'.$rel['product_qty'].'
			</td>';

			if($project_wise_item_rate=='Yes'){ 
				echo '<td  data-label="RATE" style="vertical-align:top;" class="text-center">
				'.$rel['product_rate'].'
				</td>' ;      

				echo'<td  data-label="TAXABLE AMOUNT" style="vertical-align:top;" class="text-center">
				'.$rel['product_amount'].'
				</td>
				<td  data-label="TAX" style="vertical-align:top;" class="text-center">';
				if(empty($rel['formulaid'])){
					echo '-';
				}else{
					echo (empty($rel['tax_name1']) ? " " : $rel['tax_name1'] .' : '. $rel['tax_amount1']).'<br/>';
					echo (empty($rel['tax_name2']) ? " " : $rel['tax_name2'] .' : '. $rel['tax_amount2']).'<br/>';
					echo (empty($rel['tax_name3']) ? " " : $rel['tax_name3'] .' : '. $rel['tax_amount3']).'<br/>';
				}
				echo '</td>
				<td  data-label="TOTAL AMOUNT" style="vertical-align:top;" class="text-center">
				'.$rel['product_total'].'
				</td>'; 
			}  
			echo '<td data-label="ACTION" style="vertical-align:top">
			<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_project_data('.$rel['salesorder_projecttrn_id'].');" ><i class="fa fa-pencil"></i></button>
			<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_project_data('.$rel['salesorder_projecttrn_id'].');" id="fieldremove'.$i.'">X</button>
			</td>   
			</tr>';
			$i++;
		}
	}
	else{
		echo '<tr><td colspan="8" class="text-center">NO DATA FOUND</td></tr>';
	}
	echo '</table></div></div>';
}
else if(brp_strtolower($POST['mode']) == "add_project_field") {
	$companyConfiguration=getCompanyConfiguration($dbcon);
	if($companyConfiguration['branch_wise_manage']==1){
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	}else{
		$branch_id =$companyConfiguration['default_branch_id'];
	}
	//$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$info1['inquiry_id']        = $POST['inquiry_id'];
	$info1['quotation_id']        = $POST['quotation_id'];
	$info1['sales_order_id']        = $POST['eid'];
	$info1['salesorder_trn_id']        = $POST['salesorder_trn_id'];
	$info1['inquiry_type']        = $POST['inquiry_type'];
	$info1['product_id']        = $POST['product_id'];
           // $info1['product_category_id']        = $POST['product_category_id'];
	$info1['project_assign_id']= $POST['project_assign_id'];
	$info1['description']       = stripslashes($POST['product_des']);
	$info1['product_disc']      = stripslashes($POST['product_des']);
	$info1['product_spec']      = stripslashes($POST['product_spec']);
	$info1['product_hsn_code']  = $POST['product_hsn_code'];
	$info1['product_qty']       = $POST['product_qty'];
	$info1['product_rate']      = $POST['product_rate'];
	$info1['product_amount']    = $POST['product_qty']*$POST['product_rate'];
	$info1['formulaid']         = $POST['formulaid'];
	$info1['user_id']   = $_SESSION['user_id'];
	$info1['company_id']        = $_SESSION['company_id'];

	if($info1['sales_order_id']!='' || $info1['salesorder_trn_id']!=''){
		$info1['salesorder_projecttrn_status'] = 0;
	}else{
		$info1['salesorder_projecttrn_status'] = 3;
	}

	$info=get_product_common_tax($dbcon,$info1['product_amount'],$POST['formulaid']);
	$info1=array_merge($info1,$info);

	$table='tbl_salesorder_project_trn';$tableid='salesorder_projecttrn_id';

	if(empty($POST['edit_id']))
	{
		$inserid=add_record($table, $info1, $dbcon,$branch_id);
	}
	else
	{
		$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon, $branch_id); 
	}
}else if(brp_strtolower($POST['mode'])== "edit_project_data"){
	$q = $dbcon -> query("select mst.*,pro.product_name from tbl_salesorder_project_trn as mst left join tbl_product as pro on mst.product_id=pro.product_id where salesorder_projecttrn_id = '$POST[id]'");
	$r = $q->fetch_assoc();

	echo brp_json_encode($r);
}
else if(brp_strtolower($POST['mode'])== "delete_project_data"){
	$row=array();
	$info['salesorder_projecttrn_status']=2;    
	$updateid=update_record("tbl_salesorder_project_trn", $info,"salesorder_projecttrn_id=".$POST['eid'] , $dbcon);
	if($updateid)
		$row['res']="1";
	else
		$row['res']="0";
	echo brp_json_encode($row);
}
else if(strtolower($POST['mode'])== "load_product_dtls") {
	$companyConfiguration=getCompanyConfiguration($dbcon);
	 $getspecialConfiguration = getspecialConfiguration($dbcon);
	$pro_qry="select pro.*,um.unit_name from product_mst as pro 
	left join unit_mst as um on um.unitid=pro.product_base_unit
	where pro.product_id=".$POST['product_id'];
	$pro_rel=mysqli_fetch_assoc($dbcon->query($pro_qry));
	
	$pro_rel['upcoming_stock']=get_current_stock_new($dbcon, $POST['product_id'], $pro_rel['product_base_unit']);
	if ($getspecialConfiguration['solid_permission'] == 1) { 
		$sres=reserve_stock_data($dbcon, $POST['product_id'], $pro_rel['product_base_unit'], '', '', '', '','', '', '');
		$scur=get_current_stock_new($dbcon, $POST['product_id'], $pro_rel['product_base_unit']);
		$pro_rel['current_stock']=$scur-$sres;
	}else{
		$pro_rel['current_stock']=get_current_stock_new($dbcon, $POST['product_id'], $pro_rel['product_base_unit']);
	}
	$qry1="select c_add_state as lst,com.stateid as cst from tbl_customer as led 
	left join tbl_cust_address as cust_addr On cust_addr.cust_id = led.cust_id
	left join tbl_company as com on com.company_id=led.company_id
	where led.cust_id =".$POST['cust_id'];
	$result1=$dbcon->query($qry1);
	$row1=mysqli_fetch_assoc($result1);

	$qry3="SELECT h.hsn_id,h.hsn_code,h.sale_gst,t.tax_gst,t.tax_cat_id FROM `mst_hsn_code` as h left join tbl_tax_category as t on t.tax_cat_id=h.sale_gst where h.hsn_status=0 and h.hsn_id=".$pro_rel['product_hsn']." ";
	$sale_gst=brp_mysqli_fetch_assoc($dbcon->query($qry3));

	$pro_rel['tax_gst']=$sale_gst['tax_gst'];
	$sale_card=get_product_rate_sales_time($dbcon, $POST['product_id'], $pro_rel['product_base_unit'],$POST['cust_id']);

	if($companyConfiguration['so_calculation_discount_show']==1){
		$pro_rel['product_sale_rate']	= $pro_rel['product_sale_rate']; 	
		$pro_rel['disc_per'] 			= $sale_card['discount_per'];
	}else{
		$rate = $pro_rel['product_sale_rate'] - (($pro_rel['product_sale_rate']*$sale_card['discount_per'])/100);
		$pro_rel['product_sale_rate'] = $rate;
		$pro_rel['disc_per']	= "";
	}
	
	//var_dump($rel['product_sale_rate']);
	echo json_encode($pro_rel);
}
else if(strtolower($POST['mode'])== "get_project_amount")
{
	$arr=get_product_common_tax($dbcon,$POST['product_amount'],$POST['formulaid']);
	echo json_encode($arr);
} 
else if(strtolower($POST['mode'])== "delivary_date_model_open")
{
        	//var_dump($POST['qty']);
	if(empty($POST['trn_id'])){
		echo '<input type="hidden" name="count" id="count" value="1" />
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
		<tr id="field">
		<th width="60%"  class="text-center" style="vertical-align:center;">Date</th>
		<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
		<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
		</tr>
		<tr id="field1">
		<td   class="text-center" style="vertical-align:center;">
		<input type="text" class="form-control default-date-picker delivery_date" id="delivery_date1" name="delivery_date[]" placeholder="Delivery Date" onkeyup="qty_wise_date_validation(1);" >
		</td>
		<td	 class="text-center;" style="vertical-align:center;">
		<input type="text" class="form-control delivery_qty" id="delivery_qty1" name="delivery_qty[]" placeholder="'.$POST["qty"].'" onchange="validate_dilivary_date();" onkeyup="qty_wise_date_validation(1);" />
		</td>
		<td	 class="text-center;" style="vertical-align:center;">
		<input type="hidden" name="arry_sr[]" id="arry_sr" value="1" />
		</td>
		</tr>
		</table>';
	}else{
		$qry="SELECT * FROM `tbl_salesorder_delivery_date` WHERE po_delivery_date_status=0 and sales_ordertrn_id=".$POST['trn_id']." order by so_delivery_date_id";
		$row=$dbcon->query($qry);
		$cnt=brp_mysqli_num_rows($row);
		if($cnt>0){
			$i=1;
			echo '<input type="hidden" name="count" id="count" value="'.$cnt.'" />
			<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
			<tr id="field">
			<th width="60%"  class="text-center" style="vertical-align:center;">Date</th>
			<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
			<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
			</tr>';

			while($tax=brp_mysqli_fetch_assoc($row))
			{
				$date=date('d-m-Y',strtotime($tax['delivery_date']));
				echo '<tr id="field'.$i.'">
				<td   class="text-center" style="vertical-align:center;">
				<input type="text" class="form-control default-date-picker delivery_date" id="delivery_date'.$i.'" name="delivery_date[]" placeholder="Delivery Date" value="'.$date.'" onkeyup="qty_wise_date_validation('.$i.');" >
				</td>
				<td	 class="text-center;" style="vertical-align:center;">
				<input type="text" class="form-control delivery_qty" id="delivery_qty'.$i.'" name="delivery_qty[]" placeholder="'.$tax["product_qty"].'" value="'.$tax["product_qty"].'" onchange="validate_dilivary_date();" onkeyup="qty_wise_date_validation('.$i.');" />
				</td>
				<td	 class="text-center;" style="vertical-align:center;">
				<input type="hidden" name="arry_sr[]" id="arry_sr'.$i.'" value="'.$i.'" />
				<input type="hidden" class="arry_edit" name="arry_edit[]" id="arry_edit'.$i.'" value="'.$tax["po_delivery_date_id"].'" />';
				if($i!=1){
					echo '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="remove_dilivary_date('.$i.');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>';
				}
				echo '</td>
				</tr>';
				$i++;
			}
			echo '</table>';
		}else{
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
			<input type="text" class="form-control delivery_qty" id="delivery_qty1" name="delivery_qty[]" placeholder="'.$POST["qty"].'" onchange="validate_dilivary_date();" onkeyup="qty_wise_date_validation(1);" />
			</td>
			<td	 class="text-center;" style="vertical-align:center;">
			<input type="hidden" name="arry_sr[]" id="arry_sr" value="1" />
			</td>
			</tr>
			</table>';
		}
	}
}
else if(brp_strtolower($POST['mode']) == "load_product_history") {
	$row=get_product_history($dbcon, $_POST['cust_id'], $_POST['product_id'], $_POST['eid'],2);
	echo $row;
}
/* END */
else if(strtolower($POST['mode'])== "load_consignee_detail"){
	$arr['consignee_detail'] = get_consignee($dbcon,$_POST['consignee_id'],$_POST['cust_id']);
	echo json_encode($arr);
}		
else if(strtolower($POST['mode']) == "load_typeswise_terms") {
	$quot_type=$POST['quot_type'];
	$sales_order_id=$POST['sales_order_id'];
	$quotaion_id = $POST['quotaion_id'];
	$terms_type = $POST['terms_type'];
	$cust_id 	= $POST['cust_id'];
	$query_quot = "select terms_type from tbl_quotation where quotation_id=".$quotaion_id;
	$result_quot = $dbcon->query($query_quot);
	$row_quot = brp_mysqli_fetch_array($result_quot);
	$str='';
	$str.='<table class="display table table-bordered table-striped">
	<thead>
	<tr>
	<th width="5%" class="text-center">
	<input type="checkbox" class="check_all_terms" style="height: 20px;width: 20px;" id="check_all_terms" name="check_all_terms" onClick="terms_check_all(this);">
	</th>';
	if($terms_type==3 || $row_quot['terms_type']==2){
		$str .='<th width="25%" class="text-center">Print Name</th>
		<th width="25%" class="text-center">Term Name</th>';
	}else{
		$str .='<th width="25%" class="text-center">Term Name</th>';
	}
	$str.='<th width="5%" class="text-center">Priority</th>
	<th width="65%" class="text-center">Term And Condition</th>				  
	</tr>
	</thead>
	<tbody>';
		//Get All Terms
	if($terms_type==3 || $row_quot['terms_type']==2){
		$terms_qry="select * from tbl_terms_condition where tc_status=0 and
		 tc_category=1 and find_in_set(".$quot_type.",tc_for) group by print_name order by tc_priority";
	}else{
		$terms_qry="select * from tbl_terms_condition where tc_status=0 and
		 tc_category=1 and find_in_set(".$quot_type.",tc_for) order by tc_priority";
	}
	//$terms_qry="select * from tbl_terms_condition where tc_status=0 and tc_category=1 and find_in_set(".$quot_type.",tc_for) order by tc_priority";
	$terms_qry_rs=$dbcon->query($terms_qry);$t=1;
	while($terms_rel=mysqli_fetch_assoc($terms_qry_rs)){
		$tc_priority=$terms_rel['tc_priority'];
		$tc_details=$terms_rel['tc_details'];
		
		if($terms_type=='1'){
			if($sales_order_id){
				$quot_term_qry="select * from tbl_salesorder_terms_trn where quotation_terms_trn_status=0 and sales_order_id=".$sales_order_id." and tc_id=".$terms_rel['tc_id']."";
				$quot_term_rel=mysqli_fetch_assoc($dbcon->query($quot_term_qry));
				if($quot_term_rel['tc_priority']){
					$tc_priority=$quot_term_rel['tc_priority'];
				}
				if($quot_term_rel['tc_details']){
					$tc_details=$quot_term_rel['tc_details'];
				}
			}else{
				$cust_term_qry="select * from tbl_customer_term_trn where customer_terms_trn_status=0 and tc_for=".$quot_type." and ledger_id=".$cust_id." and tc_id=".$terms_rel['tc_id'];
				$cust_term_rel = brp_mysqli_fetch_assoc($dbcon->query($cust_term_qry));
				if($cust_term_rel['tc_priority']){
					$tc_priority=$cust_term_rel['tc_priority'];
				}
				if($cust_term_rel['tc_details']){
					$tc_details=$cust_term_rel['tc_details'];
				}
				$quot_term_rel['tc_id'] = $cust_term_rel['tc_id'];
			}
		}else if($terms_type=='2'){
			if($sales_order_id){
				$quot_term_qry="select * from tbl_salesorder_terms_trn where quotation_terms_trn_status=0 and sales_order_id=".$sales_order_id." and tc_id=".$terms_rel['tc_id']."";
				$quot_term_rel=mysqli_fetch_assoc($dbcon->query($quot_term_qry));
				if($quot_term_rel['tc_priority']){
					$tc_priority=$quot_term_rel['tc_priority'];
				}
				if($quot_term_rel['tc_details']){
					$tc_details=$quot_term_rel['tc_details'];
				}
			}else{
				$quot_term_qry="select * from tbl_quotation_terms_trn where quotation_terms_trn_status=0 and quotation_id=".$quotaion_id." and tc_id=".$terms_rel['tc_id']."";
				$quot_term_rel=mysqli_fetch_assoc($dbcon->query($quot_term_qry));
				if($quot_term_rel['tc_priority']){
					$tc_priority=$quot_term_rel['tc_priority'];
				}
				if($quot_term_rel['tc_details']){
					$tc_details=$quot_term_rel['tc_details'];
				}

				if($quot_term_rel['ref_tc_id']){
					$so_ref_tc_id = $quot_term_rel['ref_tc_id'];
				}
			}
		}else if($terms_type=='3'){
			if($sales_order_id){
				$quot_term_qry="select * from tbl_salesorder_terms_trn where quotation_terms_trn_status=0 and sales_order_id=".$sales_order_id." and tc_id=".$terms_rel['tc_id']."";
				$quot_term_rel=mysqli_fetch_assoc($dbcon->query($quot_term_qry));
				if($quot_term_rel['tc_priority']){
					$tc_priority=$quot_term_rel['tc_priority'];
				}
				if($quot_term_rel['tc_details']){
					$tc_details=$quot_term_rel['tc_details'];
				}
				if($quot_term_rel['ref_tc_id']){
					$so_ref_tc_id = $quot_term_rel['ref_tc_id'];
				}
			}
		}else{
			if($sales_order_id){
				$quot_term_qry="select * from tbl_salesorder_terms_trn where quotation_terms_trn_status=0 and sales_order_id=".$sales_order_id." and tc_id=".$terms_rel['tc_id']."";
				$quot_term_rel=mysqli_fetch_assoc($dbcon->query($quot_term_qry));
				if($quot_term_rel['tc_priority']){
					$tc_priority=$quot_term_rel['tc_priority'];
				}
				if($quot_term_rel['tc_details']){
					$tc_details=$quot_term_rel['tc_details'];
				}
			}
		}
		
		$str.='<tr>
		<td width="5%" class="text-center">
		<input type="checkbox" class="terms_checkbox" style="height: 20px;width: 20px;" id="disp_term_flag'.$t.'" name="disp_term_flag[]" value="'.$terms_rel['tc_id'].'" '.(($terms_rel['tc_id']==$quot_term_rel['tc_id'])?'checked':'').'>
		<input type="hidden" id="tc_id'.$t.'" name="tc_id[]" value="'.$terms_rel['tc_id'].'">
		</td>';
		if($terms_type==3 || $row_quot['terms_type']==2){
			$str.='<td>'.$terms_rel['print_name'].'</td>
			<td>
				<select id="ref_tc_id'.$t.'" name="ref_tc_id[]" class="form-control" onchange="get_terms_detail('.$t.')">
					'.get_terms_printname_wise($dbcon, $so_ref_tc_id, $terms_rel['print_name'],$quot_type).'
				</select>
			</td>';
		}else{
			$str.='<td>'.$terms_rel['tc_name'].'</td>';	
		}

		$str.='<td>
		<input type="number" class="form-control" min="0" id="tc_priority'.$t.'" name="tc_priority[]" value="'.$tc_priority.'">
		</td>';
		if($terms_rel['tc_allow']){
			$str .= '<td>
			<textarea class="form-control" id="tc_details'.$t.'" name="tc_details[]">'.$tc_details.'</textarea>
			</td>';
		} else {
			$str .= '<td>
			<textarea class="form-control" id="tc_details'.$t.'" name="tc_details[]" readonly>'.$tc_details.'</textarea>
			</td>';
		}
		$str .= '</tr>';

		$t++;
	}	  

	$str.='</tbody> 
	</table>';	  

	$resp['resp_html']=$str;
	echo json_encode($resp);
}else if(strtolower($POST['mode'])== "add_document_attach") {
	$companyConfiguration=getCompanyConfiguration($dbcon);
	if($companyConfiguration['branch_wise_manage']==1){
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	}else{
		$branch_id =$companyConfiguration['default_branch_id'];
	}
	//$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	$info1['attach_doc_name']	= $POST['doc_name'];
	$info1['design_dept']		= $POST['design_dept'];
	$info1['attach_file']		= upload_attch_file($_FILES);
	$info1['user_id']			= $_SESSION['user_id'];
	$info1['company_id']		= $_SESSION['company_id'];

	$table='tbl_so_attch';$tableid='so_attach_id';
	if(!empty($POST['sales_order_id'])) {
		$info1['sales_order_id']= $POST['sales_order_id'];
	}
	else{
		$info1['attach_status']= 3;
	}
	/*var_dump($info1);*/
	$inserid=add_record($table, $info1, $dbcon, $branch_id);
}
else if(strtolower($POST['mode'])== "show_document_attach") {

	if($POST['sales_order_id']){
		$query="select mst.* from tbl_so_attch as mst 
		where mst.attach_status=0 and mst.sales_order_id=".$POST['sales_order_id'];
	}
	else{
		$query="select mst.* from tbl_so_attch as mst 
		where attach_status=3 and mst.user_id=".$_SESSION['user_id'];
	}
	$result=$dbcon->query($query);
	echo '<table class="display table table-bordered table-striped">
	<tr>
	<th width="5%" class="text-center">Sr.</th>
	<th width="15%" class="text-center">Design Dept</th>
	<th width="30%" class="text-center">Document Name</th>
	<th width="40%" class="text-center">Attached Document</th>
	<th width="10%" class="text-center">Action</th>					  
	</tr>
	<tbody>';
	if(mysqli_num_rows($result)>0)
	{
		$i=1;
		while($rel=mysqli_fetch_assoc($result))
		{
			$file_path=$dbcon->real_escape_string(DOMAIN.SO_ATTACH_VIEWING.$rel['attach_file']);
			if($rel['design_dept']==1){
				$rel['design_dept'] = '<strong style="color:green">Yes</strong>';
			}else{
				$rel['design_dept'] = '<strong style="color:orange">No</strong>';
			}
			echo '<tr> 
			<td style="vertical-align:top;">
			<strong>'.$i.'</strong>
			</td>
			<td style="vertical-align:top;" class="text-center">
			'.$rel['design_dept'].'
			</td>
			<td style="vertical-align:top;" class="text-center">
			'.$rel['attach_doc_name'].'
			</td>
			<td style="vertical-align:top;" class="text-center">
			<a href="'.ROOT.SO_ATTACH_VIEWING.$rel['attach_file'].'" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>

			<button type="button" onclick="copyToClipboard(\''.$file_path.'\')" class="btn btn-primary" target="_blank"><i class="fa fa-clipboard"></i> Copy Path</button>
			</td>
			<td style="vertical-align:top">';

			echo ' <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_document_attach('.$rel['so_attach_id'].')">X</button>';

			echo '</td>	
			</tr>';
			$i++;
		}
	}
	else{
		echo '<tr><td colspan="5" class="text-center">NO DATA FOUND</td></tr>';
	}

	echo '</tbody>
	</table>';
}
else if(strtolower($POST['mode'])== "delete_document_attach") {
	$row=array();
	$del_attch_qry="select attach_file from tbl_so_attch where so_attach_id=".$POST['attach_id'];
	$del_attch_rel=brp_mysqli_fetch_array($dbcon->query($del_attch_qry));
	unlink(SO_ATTACH_UPING.$del_attch_rel['attach_file']);

	$info['attach_status']=2;	
	$updateid=update_record('tbl_so_attch', $info, "so_attach_id=".$POST['attach_id'] , $dbcon);

	if($updateid)
		$row['res']="1";
	else
		$row['res']="0";
	echo json_encode($row);
}else if(strtolower($POST['mode'])== "get_tax_details_table")
{
	$invoice_id=$POST['invoice_id'];
	$resp='';
	if(!empty($invoice_id)){
		$query="SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_sales_ordertrn` where sales_order_id='$invoice_id' and sales_ordertrn_status!=2 group by cgst_tax_per,sgst_tax_per,igst_tax_per";
	}else{
		$query="SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_sales_ordertrn` where user_id='".$POST['user_id']."' and sales_ordertrn_status=3 group by cgst_tax_per,sgst_tax_per,igst_tax_per";
	}
	$rs_prel=$dbcon->query($query);
	if(!empty($invoice_id)){
		$rs_prel_fetch=brp_mysqli_fetch_assoc($dbcon->query("SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_sales_ordertrn` where sales_order_id='$invoice_id' and sales_ordertrn_status!=2"));
	}else{
		$rs_prel_fetch=brp_mysqli_fetch_assoc($dbcon->query("SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_sales_ordertrn` where user_id='".$POST['user_id']."' and sales_ordertrn_status=3"));
	}
	$rs_prel_num_rows=mysqli_num_rows($rs_prel);
		//print_r($rs_prel_fetch);exit;
	$resp='';
	$resp .= '<table class="table table-bordered">

	<tr>
	<th class="text-center">#</th>
	<th  class="text-center">Total Tax</th>
	<th  class="text-center">Taxable Amount <span class="currency_icon"> </span></th>
	<th  class="text-center">Tax Amount <span class="currency_icon"> </span></th>';
	if(($rs_prel_fetch['cgst_rate']!=0) || ($rs_prel_fetch['sgst_rate']!=0)){
		$resp .='<th  class="text-center">CGST</th>
		<th  class="text-center">SGST</th>';
	}if(($rs_prel_fetch['igst_rate']!=0)){
		$resp .= '<th  class="text-center">IGST</th>';
	}


	$resp .='</tr>';

	if($rs_prel_num_rows > 0){
		$taxRate = brp_mysqli_fetch_all($rs_prel);
			//print_r($taxRate);exit;
		$cnt=1;
		$cntloop=0;
		foreach($taxRate as $taxdetail) {
			$gst_tax_per = ($taxdetail['cgst_tax_per'] != 0 || $taxdetail['sgst_tax_per'] != 0) ? ($taxdetail['cgst_tax_per']+$taxdetail['sgst_tax_per']) : $taxdetail['igst_tax_per'];
			$gst_tax_rate = ($taxdetail['cgst_rate'] != 0 || $taxdetail['sgst_rate'] != 0) ? ($taxdetail['cgst_rate']+$taxdetail['sgst_rate']) : $taxdetail['igst_rate'];

			$gst_tax_rate_conv = ($taxdetail['cgst_rate'] != 0 || $taxdetail['sgst_rate'] != 0) ? ($taxdetail['cgst_rate_conv']+$taxdetail['sgst_rate_conv']) : $taxdetail['igst_rate_conv'];

			if($taxdetail['cgst_tax_per'] != 0 || $taxdetail['sgst_tax_per'] != 0){
				$resp.='<tr>
				<th class="text-center">'.$cnt.'</th>
				<th class="text-center">'.$gst_tax_per.'%'.'</th>
				<th class="text-center">';
				if($POST['currency_id']==$_SESSION['currency_id']){
					$resp.=$taxdetail['product_amount'].'</th>
					<th class="text-center">'.$gst_tax_rate;
				}else{
					$resp.=$taxdetail['product_amount_conv'].'</th>
					<th class="text-center">'.$gst_tax_rate_conv;
				}
				$resp.='</th>
				<th class="text-center">'.($taxdetail['cgst_tax_per']).'%'.'</th>
				<th class="text-center">'.($taxdetail['sgst_tax_per']).'%'.'</th>
				</tr>';
				if(!empty($POST['addontax1']) && $cntloop==0){
					foreach($POST['addontax1'] as $addtax){
						$cnt++;
						$exp_addtax = explode("-",$addtax);
						if($exp_addtax[1] != 0){
							$resp.='<tr>
							<th class="text-center">'.$cnt.'</th>
							<th class="text-center">'.$exp_addtax[1].'%'.'</th>
							<th class="text-center">'.($exp_addtax[2]).'</th>
							<th class="text-center">'.$exp_addtax[0].'</th>
							<th class="text-center">'.($exp_addtax[1]/2).'%'.'</th>
							<th class="text-center">'.($exp_addtax[1]/2).'%'.'</th>
							</tr>';
						}
					}
					$cntloop=1;
				}
			}

			if($taxdetail['igst_tax_per'] != 0){
				$resp.='<tr>
				<th class="text-center">'.$cnt.'</th>
				<th class="text-center">'.$gst_tax_per.'%'.'</th>
				<th class="text-center">';
				
				if($POST['currency_id']==$_SESSION['currency_id']){
					$resp.=$taxdetail['product_amount'].'</th>
					<th class="text-center">'.$gst_tax_rate;
				}else{
					$resp.=$taxdetail['product_amount_conv'].'</th>
					<th class="text-center">'.$gst_tax_rate_conv;
				}

				$resp.='</th>
				<th class="text-center">'.($taxdetail['igst_tax_per']).'%'.'</th>
				</tr>';
				if(!empty($POST['addontax1']) && $cntloop==0){
					foreach($POST['addontax1'] as $addtax){
						$cnt++;
						$exp_addtax = explode("-",$addtax);
								//echo '<pre>';print_r($exp_addtax);
						if($exp_addtax[1] != 0){
							$resp.='<tr>
							<th class="text-center">'.$cnt.'</th>
							<th class="text-center">'.$exp_addtax[1].'%'.'</th>
							<th class="text-center">'.($exp_addtax[2]).'</th>
							<th class="text-center">'.$exp_addtax[0].'</th>
							<th class="text-center">'.($exp_addtax[1]).'%'.'</th>
							</tr>';
						}
					}
					$cntloop=1;
				}
			}			
			$cnt++;	
		}

	}

	$resp.='</table>';

	$row['resp']=$resp;

	echo json_encode($row);
}

else if(strtolower($POST['mode'])== "get_invoice_total_tax")
{
	$invoice_id=$POST['invoice_id'];
	$where='';
	if($invoice_id == 0){
		$where ="sales_ordertrn_status=3 and user_id=".$POST['user_id']."";
	}else{
		$where = "sales_order_id='$invoice_id' and sales_ordertrn_status!=2";
	}
	$resp='';
	$query="SELECT sum(cgst_tax_rate) as cgst_rate,sum(sgst_tax_rate) as sgst_rate,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_sales_ordertrn` where ".$where." ";


	$rs_prel= brp_mysqli_fetch_assoc($dbcon->query($query));

	$row['isTcs']="0";
	$getCompanyConfig = getCompanyConfiguration($dbcon);
	$custLedgerDetails = get_cust_data_arr($dbcon,$POST['cust_id']);		
	$get_bill_sundry = get_bill_sundry_ledger($dbcon,1); 
	$company_state = get_company_data($dbcon,$_SESSION['company_id']);
	foreach ($get_bill_sundry as $billsundry) {

		if((($rs_prel['cgst_rate'] != 0) && $billsundry['l_name'] == 'CGST') || (($rs_prel['sgst_rate']!= 0) && $billsundry['l_name'] == 'SGST')){

			$gstValue = ($billsundry['l_name'] == 'CGST') ? $rs_prel['cgst_rate'] : (($billsundry['l_name'] == 'SGST') ? $rs_prel['sgst_rate'] : '');

			$gstValue_conv = ($billsundry['l_name'] == 'CGST') ? $rs_prel['cgst_rate_conv'] : (($billsundry['l_name'] == 'SGST') ? $rs_prel['sgst_rate_conv'] : '');

			if(!empty($POST['addontax1'])){
				$addontax = $POST['addontax1']/2;
			}
			$resp.='<div class="form-group">
			<label class="col-md-3 control-label">'.$billsundry['l_name'].' <span class="currency_icon"></span></label>
			<div class="col-md-6 col-xs-12">
			<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.(($POST['currency_id']==$company_state['currency_id']) ? round($gstValue+$addontax,2) : round($gstValue_conv+$addontax,2)).'" placeholder="'.$billsundry['l_name'].'" readonly >
			</div>
			</div>';


		}
		if(($rs_prel['igst_rate'] != 0) && $billsundry['l_name'] == 'IGST'){
			if(!empty($POST['addontax1'])){
				$addontax = $POST['addontax1'];
			}
			$resp.='<div class="form-group">
			<label class="col-md-3 control-label">'.$billsundry['l_name'].' <span class="currency_icon"></span></label>
			<div class="col-md-6 col-xs-12">
			<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.(($POST['currency_id']==$company_state['currency_id']) ? ($rs_prel['igst_rate']+$addontax) : ($rs_prel['igst_rate_conv']+$addontax)).'" placeholder="'.$billsundry['l_name'].'" placeholder="'.$billsundry['l_name'].'" readonly >
			</div>
			</div>';
		}

		if(($billsundry['l_name'] == 'TCS') && ($getCompanyConfig['enable_tcs_reporting'] == 1) && ($custLedgerDetails['enable_tcs']==1) && ($POST['gross'] >= $getCompanyConfig['gross_balance_limit'])){
			$row['isTcs']="1";
			$total_tcs_calculate = $rs_prel['product_amount']+$gstValue+$rs_prel['igst_rate'];
			$resp.='<div class="form-group">
			<label class="col-md-3 control-label">'.$billsundry['l_name'].' <span class="currency_icon"></span></label>
			<div class="col-md-6 col-xs-11">
			<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.round((($total_tcs_calculate*$billsundry['tax_value'])/100),2).'" placeholder="'.$billsundry['l_name'].'" readonly >
			<input type="hidden" name="tcs_per" id="tcs_per" value="'.$billsundry['tax_value'].'" >
			</div>
			</div>';
		}

	}

	$row['resp']=$resp;

	echo json_encode($row);
}
else if(strtolower($POST['mode']) == "update_total") {


			//update total , net total , general books entry at edit time start - dhaval 
	$bill_sundry_tax = array_combine($POST['bill_sundry_tax'],$POST['bill_sundry_tax1']);

	if($POST['invoice_id']>0)
	{	
		if($POST['currency_id']==$_SESSION['currency_id']){		
			$update_invoice['g_total'] 			= $POST['g_total'];
			$update_invoice['g_total_conv'] 	= $POST['g_total']*$POST['currency_rate'];	
		}else{	
			$update_invoice['g_total'] 			= $POST['g_total']*$POST['currency_rate'];
			$update_invoice['g_total_conv'] 	= $POST['g_total'];
		}

		update_record("tbl_sales_order",$update_invoice," sales_order_id=".$POST['invoice_id'] ,$dbcon);

				//update bill sundry in bill sundry table and general table 

		foreach ($bill_sundry_tax as $bill_sundry_tax_id => $bill_sundry_tax_amount) {
			if($POST['currency_id']==$_SESSION['currency_id']){
				$info_sundry_tax['sundry_amount']=$bill_sundry_tax_amount;
				$info_sundry_tax['sundry_amount_conv']=$bill_sundry_tax_amount*$POST['currency_rate'];
			}else{
				$info_sundry_tax['sundry_amount']=$bill_sundry_tax_amount*$POST['currency_rate'];
				$info_sundry_tax['sundry_amount_conv']=$bill_sundry_tax_amount;
			}

			$info_sundry_tax['cdate']	= date("Y-m-d H:i:s");
			$info_sundry_tax['user_id']	= $_SESSION['user_id'];
			$info_sundry_tax['company_id']	= $_SESSION['company_id'];

			update_record("tbl_bill_sundry_transaction",$info_sundry_tax," sundry_ledger_id=".$bill_sundry_tax_id." and sundry_voucher_table='tbl_sales_order' and sundry_voucher_id='$POST[invoice_id]'" ,$dbcon);

			/*$info_general_sundry['amount'] = $info_sundry_tax['sundry_amount'];
			$info_general_sundry['cdate']	= date("Y-m-d H:i:s");
			$info_general_sundry['user_id']	= $_SESSION['user_id'];
			$info_general_sundry['company_id']	= $_SESSION['company_id'];

			update_record("tbl_general_book",$info_general_sundry," ledger_id=".$bill_sundry_tax_id." and table_name='tbl_bill_sundry_transaction'" ,$dbcon);*/
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
			
		}
		else if(strtolower($POST['mode'])== "get_bill_sundry_details")
		{
			$invoice_id=$POST['invoice_id'];
		
			$q = $dbcon -> query("SELECT * from tbl_ledger_bill_sundry where isdelete=0 and sundry_ledger_id=".$POST['sundry_ledger_id']." and company_id = ".$_SESSION['company_id']." ");
			$resp = $q->fetch_assoc();

			if(!empty($resp['sundry_gst'])){
				$q_tax = $dbcon -> query("select tax_gst from tbl_tax_category where tax_cat_id=".$resp['sundry_gst']." ");
				$resp_tax = $q_tax->fetch_assoc();
			}else{
				$resp_tax['tax_gst']=0;
			}
			
			$basic_total = $POST['basic_amount'];
			$netamount = $POST['netamount'];
			$taxableamount = $POST['taxableamount'];

			$default_amount = $POST['default_amount'];

			if($POST['gst_type']=="3"){
				$resp_tax['tax_gst']=0.1;
			}else if($POST['gst_type']=="4"){
				$resp_tax['tax_gst']=0;
			}else if($POST['gst_type']=="5"){
				$resp_tax['tax_gst']=5;
			}else if($POST['gst_type']=="6"){
				$resp_tax['tax_gst']=12;
			}else if($POST['gst_type']=="7"){
				$resp_tax['tax_gst']=18;
			}else if($POST['gst_type']=="8"){
				$resp_tax['tax_gst']=24;
			}

			if(($resp['apply_gst'] == 2) && (!empty($resp['sundry_gst']))){
				if($resp['sundry_amount_of'] == 2){
					$taxvl = ($resp_tax['tax_gst']*(($basic_total * $default_amount)/100))/100;
				}else{
					$taxvl = ($resp_tax['tax_gst']*$POST['default_amount'])/100;
				}
			//$taxvl = ($resp_tax['tax_gst']*$POST['default_amount'])/100;
				$taxgst=$resp_tax['tax_gst'];
			}else{
				$taxvl=0;
				$taxgst=0;
			}

		//print_r($POST['totalsundryexist']);exit;
			$totalsundryexist = $POST['totalsundryexist'];

			if($resp['sundry_type'] == 1){
				if($resp['sundry_amount_of'] == 1){
					if($resp['sundry_calculate_on'] == 1){
						$finalNetAmount = $netamount + $default_amount;
						$pervalue =  $default_amount;
					}else if($resp['sundry_calculate_on'] == 2){
						$finalNetAmount = $basic_total + $default_amount;
						$pervalue =  $default_amount;
					}else if($resp['sundry_calculate_on'] == 3){
						$finalNetAmount = $basic_total + $default_amount;
						$pervalue =  $default_amount;
					}
				//$finalNetAmount = $netamount + $default_amount;

				}else if($resp['sundry_amount_of'] == 2){
					if($resp['sundry_calculate_on'] == 1){
						$finalNetAmount = (($netamount * $default_amount)/100) + $netamount;
						$pervalue = ($netamount * $default_amount)/100;
					}else if($resp['sundry_calculate_on'] == 2){
						$finalNetAmount = (($basic_total * $default_amount)/100) + $basic_total;
						$pervalue = ($basic_total * $default_amount)/100;
					}else if($resp['sundry_calculate_on'] == 3){
						$finalNetAmount = (($basic_total * $default_amount)/100) + $basic_total;
						$pervalue = ($basic_total * $default_amount)/100;
					}
				//$finalNetAmount = (($netamount * $default_amount)/100) + $netamount;
				}
			//$per_amount_show='';
			}
			else if($resp['sundry_type'] == 2){
				if($resp['sundry_amount_of'] == 1){
					if($resp['sundry_calculate_on'] == 1){
						$finalNetAmount = $netamount - $default_amount;
						$pervalue =  $default_amount;
					}else if($resp['sundry_calculate_on'] == 2){
						$finalNetAmount = $basic_total - $default_amount;
						$pervalue =  $default_amount;
					}else if($resp['sundry_calculate_on'] == 3){
					//$finalNetAmount = (($basic_total + $taxableamount) - $default_amount) + $totalsundryexist;
						$finalNetAmount = $basic_total - $default_amount;
						$pervalue =  $default_amount;
					}
				//$finalNetAmount = $netamount - $default_amount;
				}else if($resp['sundry_amount_of'] == 2){
					if($resp['sundry_calculate_on'] == 1){
						$finalNetAmount = $netamount - (($netamount * $default_amount)/100);
						$pervalue = -($netamount * $default_amount)/100;
					}else if($resp['sundry_calculate_on'] == 2){
					//$finalNetAmount = (($basic_total + $taxableamount) - (($basic_total * $default_amount)/100)) + $totalsundryexist;
						$finalNetAmount = $basic_total - (($basic_total * $default_amount)/100);
						$pervalue = -($basic_total * $default_amount)/100;
					}else if($resp['sundry_calculate_on'] == 3){
					//$finalNetAmount = (($basic_total + $taxableamount) + ((($basic_total + $taxableamount) * $default_amount)/100)) + $totalsundryexist;
						$finalNetAmount = $basic_total - (($basic_total * $default_amount)/100);
						$pervalue = -($basic_total * $default_amount)/100;
					}
				//$finalNetAmount = $netamount - (($netamount * $default_amount)/100);
				}

			//$per_amount_show = '('.$default_amount.'% )';

			}

		//if invoice is edit time insert data in database start - dhaval
			if($invoice_id>0)
			{
				$info_sundry_addon['sundry_ledger_id']=$POST['sundry_ledger_id'];
				$info_sundry_addon['sundry_voucher_id']=$invoice_id;
				$info_sundry_addon['sundry_voucher_type']=SO_VOUCHER;
				$info_sundry_addon['sundry_voucher_table']='tbl_sales_order';
				$info_sundry_addon['cdate']	= date("Y-m-d H:i:s");
				$info_sundry_addon['user_id']	= $_SESSION['user_id'];
				$info_sundry_addon['company_id']	= $_SESSION['company_id'];
				$info_sundry_addon['sundry_gst_per']	= $taxgst;
				/*$info_sundry_addon['sundry_amount']=$pervalue;
				$info_sundry_addon['sundry_gst_amount']	= $taxvl;*/
			//print_r(array_merge($info_sundry_addon,$curncy_trn));

				if(isset($POST['currency_enable'])){
					$curncy_trn['currency_id'] = $POST['currency_id'];
					$curncy_trn['currency_rate'] = $POST['currency_rate'];
				}else{
					$basecurrency = getbasecurrency($dbcon);
					$curncy_trn['currency_id'] = $basecurrency['currencyid'];
					$curncy_trn['currency_rate'] = 1;
				}

				if($POST['currency_id']==$_SESSION['currency_id']){
					$info_sundry_addon['sundry_amount']=$pervalue;
					$info_sundry_addon['sundry_gst_amount']	= $taxvl;
					$info_sundry_addon['sundry_amount_conv']=$pervalue*$POST['currency_rate'];
					$info_sundry_addon['sundry_gst_amount_conv']= $taxvl*$POST['currency_rate'];
				}else{
					$info_sundry_addon['sundry_amount']=$pervalue*$POST['currency_rate'];
					$info_sundry_addon['sundry_gst_amount']	= $taxvl*$POST['currency_rate'];
					$info_sundry_addon['sundry_amount_conv']=$pervalue;
					$info_sundry_addon['sundry_gst_amount_conv']= $taxvl;
				}
				
				$sundry_addon_insert=add_record('tbl_bill_sundry_transaction',array_merge($info_sundry_addon,$curncy_trn), $dbcon);

			}
		//if invoice is edit time insert data in database end - dhaval

			if($resp['sundry_amount_of'] == 1){

				$per_amount_show="";

			}
			else{

				$per_amount_show= '<strong> ('.$default_amount.'%)</strong>';
			}
			//var_dump($pervalue);
			echo json_encode($finalNetAmount.','.$pervalue.','.$per_amount_show.','.$invoice_id.','.$taxvl.','.$resp_tax['tax_gst']);
		}
		else if(strtolower($POST['mode'])== "remove_sundry"){

			$ledger_id = $POST['ledger_id'];

			$info['isdelete']=1;

			$updateid=update_record('tbl_bill_sundry_transaction', $info,"sundry_id=".$POST['ledger_id'] , $dbcon);

			$info_general['genral_book_status'] = 2;

		}
		else if(strtolower($POST['mode'])== "get_all_bill_sundry")
		{
			$invoice_id=$POST['invoice_id'];

			$q=$dbcon->query("select b.*,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id from tbl_bill_sundry_transaction as b 
				
			left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
			left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 

			where b.sundry_voucher_id='$invoice_id' and b.sundry_voucher_table='tbl_sales_order' and b.isdelete='0' and le.default_sundry='0' ");

			$resp=brp_mysqli_fetch_all($q);

			$str="";$cnt=1;
			foreach($resp as $r)
			{

				if($r['sundry_type'] == 1){

					$per_amount_show='';
				}
				else if($r['sundry_type'] == 2){

					$per_amount_show = '('.$r['sundry_default_value'].'%'.')';

				}

				if(empty($r['sundry_gst_per'])){
					$sundry_amount = ($r['currency_id']==$_SESSION['currency_id']) ? $r['sundry_amount'] : $r['sundry_amount_conv'];
					$str.='<div class="form-group">
					<label class="col-md-3 control-label">'.$r['l_name'].' <span class="currency_icon"></span></label>
					<div class="col-md-6 col-xs-12">
					<input id="sundry_name" name="bill_sundry_addon['.$r['l_id'].']" type="hidden" value="'.$sundry_amount.'">
					<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="'.$sundry_amount.'" readonly placeholder="Amount">
					</div>
					<div class="col-md-3">
					<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
					type="button" value="'.$cnt.'" onclick="removeSundry(\'\',\''.$sundry_amount.'\',this.value,\''.$r['sundry_id'].'\')"><i class="fa fa-times"></i></button>
					</div>
					</div>';
				}else{
					$sundry_amount = ($r['currency_id']==$_SESSION['currency_id']) ? $r['sundry_amount'] : $r['sundry_amount_conv'];
					$sundry_gst_amount = ($r['currency_id']==$_SESSION['currency_id']) ? $r['sundry_gst_amount'] : $r['sundry_gst_amount_conv'];
					$str.='<div class="form-group">
					<label class="col-md-3 control-label">'.$r['l_name'].' <span class="currency_icon"></span></label>
					<div class="col-md-6 col-xs-12">
					<input id="sundry_name" name="bill_sundry_addon['.$r['l_id'].']" type="hidden" value="'.$sundry_amount.'">
					<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="'.$sundry_amount.'" readonly placeholder="Amount">
					<input class="addontax" name="bill_sundry_addon_tax['.$r['l_id'].']" type="hidden" value="'.$sundry_gst_amount.'-'.$r['sundry_gst_per'].'-'.$sundry_amount.'" >
					</div>
					<div class="col-md-3">
					<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
					type="button" value="'.$cnt.'" onclick="removeSundry(\'\',\''.$sundry_amount.'\',this.value,\''.$r['sundry_id'].'\')"><i class="fa fa-times"></i></button>
					</div>
					</div>';
				}

				$cnt++;
			//$str.=$r['sundry_amount'];
			}

			echo $str;
		//echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "get_all_bill_sundrys")
		{
			$invoice_id=$POST['invoice_id'];

			$q=$dbcon->query("select b.*,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id left join tbl_ledger as le on le.l_id=b.sundry_ledger_id where b.sundry_voucher_id='$invoice_id' and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0' ");

			$resp=brp_mysqli_fetch_all($q);

			$str="";$cnt=1;
			foreach($resp as $r)
			{

				if($r['sundry_type'] == 1){

					$per_amount_show='';
				}
				else if($r['sundry_type'] == 2){

					$per_amount_show = '('.$r['sundry_default_value'].'%'.')';

				}

				if(empty($r['sundry_gst_per'])){
					$str.='<div class="form-group '.$cnt.'">
					<label class="col-md-3 control-label">'.$r['l_name'].'</label>
					<div class="col-md-6 col-xs-12">
					<input id="sundry_name" name="bill_sundry_addon['.$r['l_id'].']" type="hidden" value="'.$r['sundry_amount_conv'].'">
					<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="'.$r['sundry_amount_conv'].'" readonly placeholder="Amount">
					</div>
					<div class="col-md-3">
					<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
					type="button" value="'.$cnt.'" onclick="removeSundry(\'\',\''.$r['sundry_amount_conv'].'\',this.value,\''.$r['sundry_id'].'\')"><i class="fa fa-times"></i></button>
					</div>
					</div>';
				}else{
					$str.='<div class="form-group '.$cnt.'">
					<label class="col-md-3 control-label">'.$r['l_name'].'</label>
					<div class="col-md-6 col-xs-12">
					<input id="sundry_name" name="bill_sundry_addon['.$r['l_id'].']" type="hidden" value="'.$r['sundry_amount_conv'].'">
					<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="'.$r['sundry_amount_conv'].'" readonly placeholder="Amount">
					<input class="addontax" name="bill_sundry_addon_tax['.$r['l_id'].']" type="hidden" value="'.$r['sundry_gst_amount'].'-'.$r['sundry_gst_per'].'-'.$r['sundry_amount_conv'].'" >
					</div>
					<div class="col-md-3">
					<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
					type="button" value="'.$cnt.'" onclick="removeSundry(\'\',\''.$r['sundry_amount_conv'].'\',this.value,\''.$r['sundry_id'].'\')"><i class="fa fa-times"></i></button>
					</div>
					</div>';
				}

				$cnt++;
			//$str.=$r['sundry_amount'];
			}

			echo $str;
		//echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_product_unit")
		{
			$query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name FROM product_mst as promst
			left join unit_mst as umst on umst.unitid=promst.product_base_unit
			left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
			WHERE product_id=".$POST['product_id'];

			$rs_type1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_assoc($rs_type1);


			if($row1['product_base_unit']!=$row1['product_conv_unit']){
				$row1['unit_status']="1";
				$opt='<option  value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
				$opt .='<option  value="'.$row1['product_conv_unit'].'">'.$row1['convert_unit_name'].'</option>';
			}else{
				$row1['unit_status']="0";
				$opt='<option value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
			}

			$row1['unit_option']=$opt;
			//$row1['qye']=$query1;

			echo json_encode($row1);
		}
		else if(strtolower($POST['mode'])== "get_gst_statecode")
		{
			$arr = get_crm_gst_statecode($dbcon,$POST['cust_id']);
			echo $arr;
		}else if(strtolower($POST['mode']) == "get_revise_so_no") {
			$get_rev_cnt="select count(sales_order_id) as ttl_cnt,(select sales_order_no from tbl_sales_order where sales_order_id=".$POST['start_sales_order_id'].") as qt_no from tbl_sales_order where sales_order_status=0 and start_sales_order_id=".$POST['start_sales_order_id'];
			$rev_cnt=mysqli_fetch_assoc($dbcon->query($get_rev_cnt));
			$row['sales_order_no'] = $rev_cnt['qt_no']."/R-".$rev_cnt['ttl_cnt'];

			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "copy_prev_so_trn") {
			$del['sales_ordertrn_status'] =2;
			update_record("tbl_sales_ordertrn",$del," sales_ordertrn_status=3 and user_id=".$_SESSION['user_id'] ,$dbcon);
			
			$prev_sales_order_id=$_POST['prev_sales_order_id'];

			$getspecialConfiguration=getspecialConfiguration($dbcon);
			$where ="";
			if($getspecialConfiguration['durva_permission']==1){
				$where = " and trn.pid=0";
			}


			$sql = $dbcon->query("SELECT trn.*,po.* FROM `tbl_sales_ordertrn` as trn 
				left join tbl_sales_order as po on po.sales_order_id = trn.sales_order_id
				WHERE trn.sales_ordertrn_status=0 ".$where." and trn.sales_order_id=".$prev_sales_order_id);
			while($row=brp_mysqli_fetch_assoc($sql)){
				
				$company_state = get_company_data($dbcon,$_SESSION['company_id']);
				//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
				//$sale_gst = get_tax_cat_by_hsn($dbcon,$row['product_hsn_code']);

				$custLedgerDetails = get_cust_data_arr($dbcon,$row['cust_id']);
				
				$ven_s = "select stateid from tbl_ledger where l_id=".$row['cust_id'];
				$ves=$dbcon->query($ven_s);
				$vers = mysqli_fetch_array($ves);

				if($row['gst_type']==3){
					$sale_gst['tax_gst']=0.1;
					$sale_gst['tax_cat_id']=0;
				}else if($row['gst_type']==4){
					$sale_gst['tax_gst']=0;
					$sale_gst['tax_cat_id']=0;
				}else if($row['gst_type']==5){
					$sale_gst['tax_gst']=5;
					$sale_gst['tax_cat_id']=0;
				}else if($row['gst_type']==6){
					$sale_gst['tax_gst']=12;
					$sale_gst['tax_cat_id']=0;
				}else if($row['gst_type']==7){
					$sale_gst['tax_gst']=18;
					$sale_gst['tax_cat_id']=0;
				}else if($row['gst_type']==8){
					$sale_gst['tax_gst']=24;
					$sale_gst['tax_cat_id']=0;
				}else{
					$sale_gst = get_tax_cat_by_hsn($dbcon,trim($row['product_hsn_code']));
				}
				
				$cgst_tax_rate=0;$cgst_tax_rat_conv=0;
				$sgst_tax_rate=0;$sgst_tax_rate_conv=0;
				$igst_tax_rate=0;$igst_tax_rate_conv=0;
				if(($company_state['stateid'] == $vers['stateid']) && ($custLedgerDetails['enable_sez'] == 0)){
					$gst = $sale_gst['tax_gst']/2;
					$cgst_tax_per = $gst;
					$cgst_tax_rate = ($gst*$row['product_amount'])/100;
					$cgst_tax_rate_conv = ($gst*$row['product_amount_conv'])/100;
					$sgst_tax_per = $gst;
					$sgst_tax_rate = ($gst*$row['product_amount'])/100;
					$sgst_tax_rate_conv = ($gst*$row['product_amount_conv'])/100;
				}else{
					$igst_tax_per = $sale_gst['tax_gst'];
					$igst_tax_rate = ($sale_gst['tax_gst']*$row['product_amount'])/100;
					$igst_tax_rate_conv = ($sale_gst['tax_gst']*$row['product_amount_conv'])/100;
				}
				
				if(isset($row['currency_enable']) && $row['currency_enable']==1){
					$info1['currency_id'] = $row['currency_id'];
					$info1['currency_rate'] = $row['currency_rate'];
				}else{
					$basecurrency = getbasecurrency($dbcon);
					$info1['currency_id'] = $basecurrency['currencyid'];
					$info1['currency_rate'] = 1;
				}
				
				$info1['inquiry_type']			= $row['inquiry_type'];
				$info1['product_category_id']	= $row['product_category_id'];
				$info1['rcat_id']				= $row['rcat_id'];
				$info1['product_id']			= $row['product_id'];
				$info1['project_wise']			= $row['project_wise'];
				$info1['description']			= $row['description'];
				$info1['product_hsn_code']		= $row['product_hsn_code'];
				$info1['product_qty']			= $row['product_qty'];
				$info1['product_conv_qty']		= $row['product_conv_qty'];
				$info1['sqr_ft']				= $row['sqr_ft'];
				$info1['unit_id']				= $row['unit_id'];
				$info1['conv_unit_id']			= $row['conv_unit_id'];
				$info1['rate_unit']				= $row['rate_unit'];
				$info1['discount_per']			= $row['discount_per'];
				$info1['delivery_type']			= $row['delivery_type'];
				$info1['product_disc']			= $row['product_disc'];
				$info1['product_delivery_date']	= $row['product_delivery_date'];
				$info1['remaning_invoice_qty']	= $row['remaning_invoice_qty'];
				$info1['remaning_invoice_conv_qty']	= $row['remaning_invoice_conv_qty'];
				$info1['user_id']				= $_SESSION['user_id'];
				// $info1['company_id']			= $_SESSION['company_id'];
				
				//comment by maulik 
				/* $info1['currency_id']			= $row['currency_id'];
				$info1['conversion_rate']		= $row['conversion_rate'];
				$info1['product_currency_rate']	= $row['product_currency_rate'];
				$info1['product_currency_amount']= $row['product_currency_amount'];
				$info1['product_currency_amount_tax']= $row['product_currency_amount_tax'];
				$info1['currency_total']		= $row['currency_total']; */
				
				//finance texasion update
				$info1['cgst_tax_per'] = isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
				$info1['sgst_tax_per'] = isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
				$info1['igst_tax_per'] = isset($igst_tax_per) ? $igst_tax_per : 0 ;
				
				$info1['cgst_tax_rate'] = isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
				$info1['sgst_tax_rate'] = isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
				$info1['igst_tax_rate'] = isset($igst_tax_rate) ? $igst_tax_rate : 0 ;

				$info1['cgst_tax_rate_conv'] = isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
				$info1['sgst_tax_rate_conv'] = isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
				$info1['igst_tax_rate_conv'] = isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;

				$info1['product_rate']			= $row['product_rate'];
				$info1['product_discount']		= $row['product_discount'];
				$info1['product_amount']		= $row['product_amount'];

				$info1['product_rate_conv']		= $row['product_rate_conv'];
				$info1['product_discount_conv']	= $row['product_discount_conv'];
				$info1['product_amount_conv']	= $row['product_amount_conv'];

				$info1['product_tax_cat'] = $sale_gst['tax_cat_id'];
				
				
				$info1['prev_sales_ordertrn_id']= $row['sales_ordertrn_id'];
				$info1['sales_ordertrn_status']	= 3;

				$info1['orange']				= $row['orange'];
				$info1['mfg']					= $row['mfg'];
				$info1['trading']				= $row['trading'];
				$info1['repairing']				= $row['repairing'];
				$info1['other']					= $row['other'];

				$info1['orange_total']					= $row['orange_total'];
				$info1['mfg_total']					= $row['mfg_total'];
				$info1['trading_total']				= $row['trading_total'];
				$info1['repairing_total']				= $row['repairing_total'];
				$info1['other_total']					= $row['other_total'];	


				$table='tbl_sales_ordertrn';$tableid='sales_ordertrn_id';
				$inserid=add_record($table, $info1, $dbcon, $row['branch_id']);				

				if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
					$cl_id = get_ledger_by_name($dbcon,'CGST');
					$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid,"tbl_sales_order",$row['product_id'],3,0,$row['branch_id'],$row['currency_id'],$row['currency_rate'],$cgst_tax_rate_conv);
				}
				if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
					$cl_id = get_ledger_by_name($dbcon,'SGST');
					$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid,"tbl_sales_order",$row['product_id'],3,0,$row['branch_id'],$row['currency_id'],$row['currency_rate'],$sgst_tax_rate_conv);
				}
				if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
					$cl_id = get_ledger_by_name($dbcon,'IGST');
					$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid,"tbl_sales_order",$row['product_id'],3,0,$row['branch_id'],$row['currency_id'],$row['currency_rate'],$igst_tax_rate_conv);
				}

				// check for the addiotional tax on product Start -- Maulik

				$count_add_tax=get_check_addition_tax($dbcon,$sale_gst['tax_cat_id'],$row['taxable_value'],$inserid,$row['product_id'],0,$row['branch_id'],'tbl_sales_order',$row['currency_id'],$row['currency_rate'],$row['product_amount_conv']);

				// $dbcon->query("UPDATE `tbl_purchaseorder_delivery_date` SET `purchaseordertrn_id`		='".$inserid."' WHERE `po_delivery_date_status` = 0 AND `purchaseordertrn_id` = '".$row['purchaseordertrn_id']."'");

				$purchase_delivery_id=$dbcon->query("INSERT INTO `tbl_salesorder_delivery_date`(`sales_ordertrn_id`, `delivery_date`, `product_qty`, `used_qty`, `invoice_status`, `unit_id`, `po_delivery_date_status`, `user_id`, `company_id`, `branch_id`) SELECT  '".$inserid."', `delivery_date`, `product_qty`, `used_qty`, `invoice_status`, `unit_id`, `po_delivery_date_status`, `user_id`, `company_id`, `branch_id` FROM `tbl_salesorder_delivery_date` WHERE po_delivery_date_status=0 AND sales_ordertrn_id='".$row['sales_ordertrn_id']."'");

				if($getspecialConfiguration['durva_permission']==1){
					$where1="";
					if($getspecialConfiguration['durva_permission']==1){
						$where1 = " and trn.pid=".$row['sales_ordertrn_id'];
					}
					$sql1 = $dbcon->query("SELECT trn.*,po.* FROM `tbl_sales_ordertrn` as trn 
				left join tbl_sales_order as po on po.sales_order_id = trn.sales_order_id
				WHERE trn.sales_ordertrn_status=0 ".$where1." and trn.sales_order_id=".$prev_sales_order_id);

					while($row1 = brp_mysqli_fetch_array($sql1)){
						$company_state = get_company_data($dbcon,$_SESSION['company_id']);
						//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
						//$sale_gst = get_tax_cat_by_hsn($dbcon,$row['product_hsn_code']);

						$custLedgerDetails1 = get_cust_data_arr($dbcon,$row1['cust_id']);
						
						$ven_s = "select stateid from tbl_ledger where l_id=".$row1['cust_id'];
						$ves=$dbcon->query($ven_s);
						$vers = mysqli_fetch_array($ves);

						if($row1['gst_type']==3){
							$sale_gst['tax_gst']=0.1;
							$sale_gst['tax_cat_id']=0;
						}else if($row1['gst_type']==4){
							$sale_gst['tax_gst']=0;
							$sale_gst['tax_cat_id']=0;
						}else if($row1['gst_type']==5){
							$sale_gst['tax_gst']=5;
							$sale_gst['tax_cat_id']=0;
						}else if($row1['gst_type']==6){
							$sale_gst['tax_gst']=12;
							$sale_gst['tax_cat_id']=0;
						}else if($row1['gst_type']==7){
							$sale_gst['tax_gst']=18;
							$sale_gst['tax_cat_id']=0;
						}else if($row1['gst_type']==8){
							$sale_gst['tax_gst']=24;
							$sale_gst['tax_cat_id']=0;
						}else{
							$sale_gst = get_tax_cat_by_hsn($dbcon,trim($row1['product_hsn_code']));
						}
						
						$cgst_tax_rate=0;$cgst_tax_rat_conv=0;
						$sgst_tax_rate=0;$sgst_tax_rate_conv=0;
						$igst_tax_rate=0;$igst_tax_rate_conv=0;
						if(($company_state['stateid'] == $vers['stateid']) && ($custLedgerDetails1['enable_sez'] == 0)){
							$gst = $sale_gst['tax_gst']/2;
							$cgst_tax_per = $gst;
							$cgst_tax_rate = ($gst*$row1['product_amount'])/100;
							$cgst_tax_rate_conv = ($gst*$row1['product_amount_conv'])/100;
							$sgst_tax_per = $gst;
							$sgst_tax_rate = ($gst*$row1['product_amount'])/100;
							$sgst_tax_rate_conv = ($gst*$row1['product_amount_conv'])/100;
						}else{
							$igst_tax_per = $sale_gst['tax_gst'];
							$igst_tax_rate = ($sale_gst['tax_gst']*$row1['product_amount'])/100;
							$igst_tax_rate_conv = ($sale_gst['tax_gst']*$row1['product_amount_conv'])/100;
						}
						
						if(isset($row1['currency_enable']) && $row1['currency_enable']==1){
							$info12['currency_id'] = $row1['currency_id'];
							$info12['currency_rate'] = $row1['currency_rate'];
						}else{
							$basecurrency = getbasecurrency($dbcon);
							$info12['currency_id'] = $basecurrency['currencyid'];
							$info12['currency_rate'] = 1;
						}
						
						$info12['inquiry_type']			= $row1['inquiry_type'];
						$info12['product_id']			= $row1['product_id'];
						$info12['project_wise']			= $row1['project_wise'];
						$info12['description']			= $row1['description'];
						$info12['pid']					= $inserid;
						$info12['product_hsn_code']		= $row1['product_hsn_code'];
						$info12['product_qty']			= $row1['product_qty'];
						$info12['product_conv_qty']		= $row1['product_conv_qty'];
						$info12['sqr_ft']				= $row1['sqr_ft'];
						$info12['unit_id']				= $row1['unit_id'];
						$info12['conv_unit_id']			= $row1['conv_unit_id'];
						$info12['rate_unit']				= $row1['rate_unit'];
						$info12['discount_per']			= $row1['discount_per'];
						$info12['delivery_type']			= $row1['delivery_type'];
						$info12['product_disc']			= $row1['product_disc'];
						$info12['product_delivery_date']	= $row1['product_delivery_date'];
						$info12['remaning_invoice_qty']	= $row1['remaning_invoice_qty'];
						$info12['remaning_invoice_conv_qty']	= $row1['remaning_invoice_conv_qty'];

						$info12['user_id']				= $_SESSION['user_id'];
						// $info12['company_id']			= $_SESSION['company_id'];
						
						//comment by maulik 
						/* $info12['currency_id']			= $row1['currency_id'];
						$info12['conversion_rate']		= $row1['conversion_rate'];
						$info12['product_currency_rate']	= $row1['product_currency_rate'];
						$info12['product_currency_amount']= $row1['product_currency_amount'];
						$info12['product_currency_amount_tax']= $row1['product_currency_amount_tax'];
						$info12['currency_total']		= $row1['currency_total']; */
						
						//finance texasion update
						$info12['cgst_tax_per'] = isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
						$info12['sgst_tax_per'] = isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
						$info12['igst_tax_per'] = isset($igst_tax_per) ? $igst_tax_per : 0 ;
						
						$info12['cgst_tax_rate'] = isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
						$info12['sgst_tax_rate'] = isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
						$info12['igst_tax_rate'] = isset($igst_tax_rate) ? $igst_tax_rate : 0 ;

						$info12['cgst_tax_rate_conv'] = isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
						$info12['sgst_tax_rate_conv'] = isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
						$info12['igst_tax_rate_conv'] = isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;

						$info12['product_rate']			= $row1['product_rate'];
						$info12['product_discount']		= $row1['product_discount'];
						$info12['product_amount']		= $row1['product_amount'];

						$info12['product_rate_conv']		= $row1['product_rate_conv'];
						$info12['product_discount_conv']	= $row1['product_discount_conv'];
						$info12['product_amount_conv']	= $row1['product_amount_conv'];

						$info12['product_tax_cat'] = $sale_gst['tax_cat_id'];
						
						
						$info12['prev_sales_ordertrn_id']= $row1['sales_ordertrn_id'];
						$info12['sales_ordertrn_status']= 3;

						$table='tbl_sales_ordertrn';$tableid='sales_ordertrn_id';
						$inserid12=add_record($table, $info12, $dbcon, $row1['branch_id']);				

						if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
							$cl_id = get_ledger_by_name($dbcon,'CGST');
							$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid,"tbl_sales_order",$row1['product_id'],3,0,$row1['branch_id'],$row1['currency_id'],$row1['currency_rate'],$cgst_tax_rate_conv);
						}
						if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
							$cl_id = get_ledger_by_name($dbcon,'SGST');
							$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid,"tbl_sales_order",$row1['product_id'],3,0,$row1['branch_id'],$row1['currency_id'],$row1['currency_rate'],$sgst_tax_rate_conv);
						}
						if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
							$cl_id = get_ledger_by_name($dbcon,'IGST');
							$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid,"tbl_sales_order",$row1['product_id'],3,0,$row1['branch_id'],$row1['currency_id'],$row1['currency_rate'],$igst_tax_rate_conv);
						}

						// check for the addiotional tax on product Start -- Maulik

						$count_add_tax=get_check_addition_tax($dbcon,$sale_gst['tax_cat_id'],$row1['taxable_value'],$inserid,$row1['product_id'],0,$row1['branch_id'],'tbl_sales_order',$row1['currency_id'],$row1['currency_rate'],$row1['product_amount_conv']);
					}	
				}
			}
			echo $prev_sales_order_id;
		} else if(strtolower($POST['mode'])== "get_product_by_category"){
			$cust_id = $POST['cust_id'];
			$category_id = $POST['category_id'];

			$product_sql = "select cwp.party_product,pm.product_name from `tbl_customer_wise_product` as cwp left join product_mst as pm on pm.product_id=cwp.party_product where cwp.`party_id` = ".$cust_id." AND cwp.`party_category_id` = ".$category_id." ";
			$exec=$dbcon->query($product_sql);
			if(brp_mysqli_num_rows($exec) > 0){

				$str ='<option value="" >--Choose Product--</option>';
				while($data=mysqli_fetch_assoc($exec))
				{	
					$sel='';
					$str .= '<option '.$sel.' value="'.$data['party_product'].'">'.$data['product_name'].'</option>';
				}
				$res = $str;

			}else{
				$res = getproductsbycategory($dbcon,$category_id);
			}

			echo $res;
		} else if(strtolower($POST['mode'])== "get_die_master_name"){
			$die_product_id = $POST['prod_id'];
			$die_customer_id = $POST['cust_id'];
			$query="select ps.*,pm.product_name,pm.product_icode,led.l_name from tbl_product_die_allocation as ps left join product_mst as pm on pm.product_id=ps.die_product_id
			left join tbl_ledger as led on led.l_id=ps.die_customer_id
			where ps.product_id='".$die_product_id."' AND ps.die_customer_id = '".$die_customer_id."' order by ps.die_allocation_id Desc";
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			$res['die_master_name']=$row['product_name'];
			$res['die_product_id']=$row['die_product_id'];
			echo json_encode($res);	 
		} else if(strtolower($POST['mode'])== "get_die_master_cal"){
			$die_product_id = $POST['die_product_id'];
			$length_value = $POST['length_val'];
			$pices_value = $POST['pices_val'];
			$query="select ps.* from product_mst as ps where ps.product_id = '".$die_product_id."'";
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			if(empty($POST['pices_val'])){
				$product_die_calculation = ($row['product_net_weight']/100)*$length_value;
			}else{
				$product_die_calculation = ($row['product_net_weight']/100)*$length_value*$pices_value;
			}
			$res['product_qty_calc'] = $product_die_calculation;
			echo json_encode($res);	 
		} else if(strtolower($POST['mode'])== "short_close_so"){
			$sales_order_id = $POST['sales_order_id'];

			$info['invoice_status'] = 1;
			$info['short_close_status'] = 1;

			$updateid=update_record('tbl_sales_order', $info,"sales_order_id=".$sales_order_id , $dbcon);

			$chkqty = $dbcon->query("SELECT * FROM tbl_sales_ordertrn WHERE sales_order_id = ".$sales_order_id);
			while($getqty = brp_mysqli_fetch_assoc($chkqty)){
				$infoso['invoice_status'] = 1;
				$infoso['short_close_status'] = 1;
				$infoso['short_close_product_qty'] = $getqty['remaning_invoice_qty'];
				$infoso['short_close_conv_qty'] = $getqty['remaning_invoice_conv_qty'];
				$infoso['short_close_unit_id'] = $getqty['unit_id'];
				$infoso['short_close_conv_unit_id'] = $getqty['conv_unit_id'];
				$updatesid=update_record('tbl_sales_ordertrn', $infoso,"sales_order_id=".$sales_order_id , $dbcon);

				delete_so_temp_allocate_stock($dbcon,$getqty['sales_ordertrn_id']);
			}

			if($updateid){
				$res['msg'] = 1;
			}else{
				$res['msg'] = 0;
			}
			echo json_encode($res);	 
		}else if(strtolower($POST['mode']) == "load_attach_document") {
			$appData = array();
			$i=1;
			$where='';
			if($POST['sales_order_id']){
				$where = ' and attach.sales_order_id='.$POST['sales_order_id'];
			}
		    // if($branch_id){
		    //     $where .= check_branch('opportun',$branch_id);
		    // }
			$aColumns = array('attach.so_attach_id', 'attach.sales_order_id','attach.attach_doc_name','attach.attach_file');
			$sIndexColumn = "attach.so_attach_id";
			$isWhere = array("attach.attach_status=0 and attach.company_id in (0,$_SESSION[company_id])".$where);
			$sTable = "tbl_so_attch as attach";            
			$isJOIN = array('');
			$hOrder = "attach.so_attach_id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['attach_doc_name']; 
				$row_data[] = '<a href="'.ROOT.SO_ATTACH_VIEWING.$row['attach_file'].'" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>'; 

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}else if(strtolower($POST['mode']) == "update_user_log_history") {
			$appData = array();
			$i=1;

			$where='';
			if($POST['sales_order_id']){
				$where = ' and log.ref_id='.$POST['sales_order_id'];
			}
		    // if($branch_id){
		    //     $where .= check_branch('opportun',$branch_id);
		    // }
			$aColumns = array('log.user_log_id','aus.user_name as updateduser','lus.user_name as loginuser', 'log.updated_user_id','log.remark','log.cdate');
			$sIndexColumn = "log.user_log_id";
			$isWhere = array("log.ref_name='tbl_sales_order' and log.ref_id and log.company_id in (0,$_SESSION[company_id])".$where);
			$sTable = "tbl_update_user_log as log";            
			$isJOIN = array('left join users as aus on aus.user_id=log.updated_user_id','left join users as lus on lus.user_id=log.user_id');
			$hOrder = "log.user_log_id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
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
			echo json_encode( $output );
		}else if(strtolower($POST['mode']) == "user_update") {
			$info['updated_user_id']	= $POST['updated_user_id'];
			$info['previous_user_id']	= $POST['previous_user_id'];
			$info['remark']				= $POST['remark'];
			$info['ref_name']			= 'tbl_sales_order';
			$info['ref_id']				= $POST['ref_id'];
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['user_id']			= $_SESSION['user_id'];	
			$info['branch_id']			= $_SESSION['branch_id'];
			$info['company_id']			= $_SESSION['company_id'];

			$inserid=add_record('tbl_update_user_log', $info, $dbcon, $row['branch_id']);

			$infoso['user_id']	= $POST['updated_user_id'];
			$updatesid=update_record('tbl_sales_order', $infoso, "sales_order_id=".$POST['ref_id'], $dbcon);

		}else if(strtolower($POST['mode'])== "preview_cust_dtls") {
			$cust_id=$POST['cust_id'];$str='';
			$cust_qry="select cust.*,country_name,state_name,city_name from tbl_ledger as cust
			left join country_mst as country on country.countryid=cust.countryid
			left join state_mst as state on state.stateid=cust.stateid
			left join city_mst as city on city.cityid=cust.cityid
			where cust.l_id=".$cust_id;
			$cust_rel=mysqli_fetch_assoc($dbcon->query($cust_qry));

			$prep_add=$cust_rel['m_address'].' '.$cust_rel['city_name'].', '.$cust_rel['state_name'].', '.$cust_rel['country_name'];

			$str.='<table class="display table table-bordered table-striped">
			<tr>
			<td colspan="4"><strong>Company Name:</strong> '.$cust_rel['l_name'].'</td>
			</tr>
			<tr>
			<td colspan="2" width="50%"><strong>Mobile:</strong> '.$cust_rel['cust_mobile'].'</td>
			<td colspan="2" width="50%"><strong>Email:</strong> '.$cust_rel['cust_email'].'</td>
			</tr>
			<tr>
			<td colspan="4"><strong>Address: </strong>'.$prep_add.'</td>
			</tr>';
			$str.='</table>';
			$resp['html_resp']=$str;
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "get_peyment_terms_details") {
			$ledger_id=$POST['ledger_id'];
			$cust_qry="select pt.terms_id,pt.payment_terms from pay_terms as pt left join tbl_ledger as tl on tl.pay_terms = pt.terms_id where pt.terms_status =0 and tl.l_id =".$ledger_id;
			$cust_rel=mysqli_fetch_assoc($dbcon->query($cust_qry));

			
			$resp['terms_id']=$cust_rel['terms_id'];
			$resp['payment_terms']=$cust_rel['payment_terms'];
			
			echo json_encode($resp);
		}

		else if(strtolower($POST['mode'])== "accessories_model_open")
		{
			$html = '<input type="hidden" id="pid" value='.$POST['product_id'].' />
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
		}else if(strtolower($POST['mode']) == "add_accessories_product_pop") {
			
			$info1['product_id']		= $POST['acc_product_id'];
			$info1['pid']				= $POST['pid'];		
			$info1['qty']				= $POST['acc_product_qty'];
			$info1['acce_rate']			= $POST['acce_rate'];
			$info1['acc_amount']		= $POST['acc_amount'];					
			$info1['product_desc']		= text_rnremove($_POST['acc_product_desc']);
			$info1['inq_access_status']	= 3;
			$info1['cdate'] 			= date("Y-m-d H:i:s");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$info1['branch_id']			= $POST['branchid'];
			/*var_dump($info1);*/
			$table='tbl_so_access_trn';$tableid='inq_acc_id';
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}

		else if(strtolower($POST['mode'])== "fetch_accessories_qty")
		{
			$appData = array();
			$i=1;
			$aColumns = array('tpm.product_name','tiat.inq_acc_id','tiat.product_id','tiat.pid','tiat.qty','tiat.acce_rate','tiat.acc_amount','tiat.product_desc');
			$sTable = "tbl_so_access_trn as tiat";			
			$isJOIN = array('left join product_mst as tpm on tpm.product_id=tiat.product_id');
			$sIndexColumn = "tiat.inq_acc_id";
			$where = "  tiat.pid='".$POST['product_id']."' and tiat.inq_access_status=3 ";
			$isWhere = array($where);
			$hOrder = "tiat.inq_acc_id desc";
			include($path.'include/pagging.php');
			$id=1;
			$edit = $delete = '';
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['product_name'];
				$row_data[] = $row['qty'];
				$row_data[] = $row['acce_rate'];
				$row_data[] = $row['acc_amount'];
				$row_data[] = $row['product_desc'];
				
				$edit='<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_accessories_product_pop('.$row['inq_acc_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>';	
				
				$delete='<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_accessories_product_pop('.$row['inq_acc_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>';	
				
				
				$row_data[] = $edit.' '.$delete;
				

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}

		else if(strtolower($POST['mode'])== "delete_data_alternative_product_pop")
		{
			$deleteid=delete_record('tbl_so_access_trn', "inq_acc_id=".$POST['eid']. " and inq_access_status = 3 and user_id='".$_SESSION['user_id']."' and company_id='".$_SESSION['company_id']."'", $dbcon);
			

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}

		else if(strtolower($POST['mode'])== "preedit_accessories_product")
		{
			$q = $dbcon -> query("SELECT tpap.*,pm.product_name FROM tbl_so_access_trn as tpap left join product_mst as pm on pm.product_id=tpap.product_id WHERE inq_acc_id= '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}

		else if(strtolower($POST['mode'])== "get_quotation_details")
		{
			$resp='';
			$company_config = getCompanyConfiguration($dbcon);
			
			if($_SESSION['user_type']!='2' && $_SESSION['user_type']!='9')
			{
				$ser = trim(check_crm_find_in_set($dbcon,$_SESSION['user_id'],0),",");
				$where.=' and inq.won_user_id IN ('.$ser.')';
			}

			
			$ledger_detail = get_ledger_details($dbcon,$POST['cust_id']);
			
			$query="select quot.quotation_no,quot.quotation_id from tbl_quotation as quot 
			left join tbl_inquiry as inq on inq.inquiry_id = quot.inquiry_id
			where quot.sales_order_status=0 and quot.po_approve_status=0 and quot.quotation_status = 0 and quot.revise_status=0 and quot.approve_status=1 and stage_prob>=90 and quot.cust_id=".$ledger_detail['cust_id']." and quot.company_id=".$_SESSION['company_id']." ".$where;
			
			$rs_prel= brp_mysqli_fetch_all($dbcon->query($query));

			foreach ($rs_prel as $result) {	
				$query_tmp_quot = "select * from tbl_salesorder_multiple_quot where sales_order_id=0 and so_multi_quot_status=3 and quotation_id=".$result['quotation_id'];

				$result_tmp_rel = brp_mysqli_fetch_array($dbcon->query($query_tmp_quot));
				$checked = '';
				if($result_tmp_rel['quotation_id']==$result['quotation_id']){
					$checked = "checked";
				}

				$resp.='<div class="row">
				<div class="col-md-6"><label>'.$result['quotation_no'].' </label></div>
				<div class="col-md-4" ><input type="checkbox" class="quotation" value="'.$result['quotation_id'].'" '.$checked.' ></div>
				</div><br>';
			}

			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "get_quotation_dropdown_data"){
			if($_SESSION['user_type']!='2' && $_SESSION['user_type']!='9')
			{
				$ser = trim(check_crm_find_in_set($dbcon,$_SESSION['user_id'],0),",");
				$where.=' and inq.won_user_id IN ('.$ser.')';
			}

			$ledger_detail = get_ledger_details($dbcon,$POST['cust_id']);
			
			
			$query="select quot.quotation_no,quot.quotation_id from tbl_quotation as quot 
			left join tbl_inquiry as inq on inq.inquiry_id = quot.inquiry_id
			where quot.quotation_status = 0 and quot.revise_status=0 and quot.approve_status=1 and stage_prob>=90 and quot.cust_id=".$ledger_detail['cust_id']." and quot.company_id=".$_SESSION['company_id']." ".$where;

			$result = $dbcon->query($query);
			$str='';
			$str .= '<option value="">Choose Quotation</option>';
			while($row = brp_mysqli_fetch_array($result)){
				$str .= '<option value="' . $row['quotation_id'] . '">' . $row['quotation_no'] . '</option>';
			}
			echo json_encode($str);
		}

		else if(strtolower($POST['mode'])== "add_accessories_data"){
	   		// $inquiry_id = $POST['eid'];
	   		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	    	//if($inquiry_id==''){
	    	$product_id = $POST['product_id'];
	    	$inquiry_type = $POST['inquiry_type'];

    		//$update['inq_access_status'] = 2;
    		//update_record('tbl_inq_access_trn', $update, "pid=".$product_id. " and inq_access_status = 3 and user_id='".$_SESSION['user_id']."' and company_id='".$_SESSION['company_id']."'", $dbcon);
	
			$deleteid=delete_record('tbl_quto_access_trn', "pid=".$product_id. " and inq_access_status = 3 and user_id='".$_SESSION['user_id']."' and company_id='".$_SESSION['company_id']."'", $dbcon);
			
			$inq_qry="select tpap.*,pm.product_sale_rate from tbl_product_acc_product tpap left join product_mst as pm on  pm.product_id = tpap.acc_product_id  where tpap.product_id=".$product_id." and tpap.company_id='".$_SESSION['company_id']."'";
					
			$inq_qry_rs=$dbcon->query($inq_qry);
			if(brp_mysqli_num_rows($inq_qry_rs)>0)
			{
				while($inq_rel=brp_mysqli_fetch_assoc($inq_qry_rs))
				{			
					$info12['product_id']		= $inq_rel['acc_product_id'];
					$info12['pid']				= $inq_rel['product_id'];
					$info12['qty']				= $inq_rel['acc_product_qty'];
					$info12['acce_rate']		= $inq_rel['product_sale_rate'];
						
					if(!empty($inq_rel['product_sale_rate']))
					{
						$info12['acc_amount']		= $inq_rel['product_sale_rate'] * $inq_rel['acc_product_qty'] ;
					}
					else
					{
						$info12['acc_amount']		= 0;
					}	
					$info12['product_desc']		= $inq_rel['acc_product_desc'];
					$info12['inq_access_status']= 3;
					$info12['company_id']		=$_SESSION['user_id'];
					$info12['user_id']			=$_SESSION['company_id'];
					//var_dump($info12);
					$inserid_sub=add_record(" tbl_quto_access_trn", $info12, $dbcon, $branch_id);
				}
			}	
		}

		else if(strtolower($POST['mode'])== "open_accesorice_wise_product_list")
		{
			$html = '<input type="hidden" id="pid_l" value='.$POST['product_id'].' />
					<div class="row">
                        <div class="col-md-12 margin_row">
                        	<table class="table table-bordered">
                                <tr>
                                    <th>Accessories Product Name</th>
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
			}
			
			else if(strtolower($POST['mode']) == "add_field_list") 
	 		{
				$pid= $POST['pid']; 
					 
				$inq_qry="select * from tbl_sales_ordertrn  where  sales_ordertrn_id=".$pid;
					
				$inq_qry_rs=$dbcon->query($inq_qry);

				$inq_rel=brp_mysqli_fetch_array($inq_qry_rs);
					
				$inq_unit="select product_base_unit,product_spec,product_spec_id,product_hsn,hsn.hsn_code from product_mst as pro 
				left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
				where  product_id=".$POST['product_id'];
					
				$inq_unit_rs=$dbcon->query($inq_unit);

				$inq_rel_unit=brp_mysqli_fetch_array($inq_unit_rs);
					
				$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

				$curncy_trn['currency_enable'] 	= 1;
		   		$curncy_trn['currency_id'] 		= $inq_rel['currency_id'];
		   		$curncy_trn['currency_rate'] 	= $inq_rel['currency_rate'];

				$company_state = get_company_data($dbcon,$_SESSION['company_id']);
				
				$product_detail1 = get_product_detail($dbcon,$POST['product_id']);
				if($POST['gst_type']==3){
			   		$sale_gst['tax_gst']=0.1;
			   		$sale_gst['tax_cat_id']=0;
			   	}else if($POST['gst_type']==4){
			   		$sale_gst['tax_gst']=0;
			   		$sale_gst['tax_cat_id']=0;
			   	}else if($POST['gst_type']==5){
			   		$sale_gst['tax_gst']=5;
			   		$sale_gst['tax_cat_id']=0;
			   	}else if($POST['gst_type']==6){
			   		$sale_gst['tax_gst']=12;
			   		$sale_gst['tax_cat_id']=0;
			   	}else if($POST['gst_type']==7){
			   		$sale_gst['tax_gst']=18;
			   		$sale_gst['tax_cat_id']=0;
			   	}else if($POST['gst_type']==9){
   					$sale_gst['tax_gst']=9;
   					$sale_gst['tax_cat_id']=0;
   				}else if($POST['gst_type']==8){
			   		$sale_gst['tax_gst']=24;
			   		$sale_gst['tax_cat_id']=0;
			   	}else{
			   		$sale_gst = get_tax_cat_by_hsn_id($dbcon,trim($inq_rel_unit['product_hsn'])); 
			   	}


			   	$cgst_tax_rate=0;$cgst_tax_rate_conv=0;
			   	$sgst_tax_rate=0;$sgst_tax_rate_conv=0;
			   	$igst_tax_rate=0;$igst_tax_rate_conv=0;
			   	if($product_detail1['product_gst'] == 'including'){
			   		$prorate = $POST['product_amount'] * 100 /(100 + $sale_gst['tax_gst']);
			   	}else{
			   		$prorate = $POST['product_amount']; 
			   	}
			   	if(($company_state['stateid'] == $POST['cust_stateid']) && ($custLedgerDetails['enable_sez'] == 0)){
			   		$gst = $sale_gst['tax_gst']/2;
			   		$cgst_tax_per = $gst;
			   		$cgst_tax_rate = ($gst*$POST['product_amount'])/100;
			   		$cgst_tax_rate_conv = ($inq_rel['currency_rate'] *$gst*$POST['product_amount'])/100;
			   		$sgst_tax_per = $gst;
			   		$sgst_tax_rate = ($gst*$POST['product_amount'])/100;
			   		$sgst_tax_rate_conv = ($inq_rel['currency_rate'] *$gst*$POST['product_amount'])/100;
			   	}else{
			   		$igst_tax_per = $sale_gst['tax_gst'];
			   		$igst_tax_rate = ($sale_gst['tax_gst']*$POST['product_amount'])/100;
			   		$igst_tax_rate_conv = ($inq_rel['currency_rate'] *$sale_gst['tax_gst']*$POST['product_amount'])/100;
			   	}

			   	if($companyConfiguration['branch_wise_manage']==1){
					$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
				}else{
					$branch_id =$companyConfiguration['default_branch_id'];
				}
			   

			   	$info12['inquiry_type'] 	= $inq_rel['inquiry_type'];
			   	$info12['product_id']		= $POST['product_id'];
			   	$info12['description']		= $POST['product_desc'];
			   	$info12['product_disc']		= $POST['product_desc'];
			   	$info12['pid']				= $POST['pid'];
			   	//$info12['product_spec']	= $_POST['product_spec'];
			   	$info12['product_hsn_code']	= $inq_rel_unit['hsn_code'];
			   	/*if($getspecialConfiguration['elcon_permission'] ==1){
			   		$info12['product_item_code']	= $POST['product_item_code'];
			   	}*/
			   	/*if($getspecialConfiguration['vipul_copper_permission'] ==1){
			   		$info12['product_category_id']	= $POST['product_category_id'];
			   		$info12['product_length']	= $POST['product_length'];
			   		$info12['product_pices']		= $POST['product_pices'];
			   	}*/

			   	if($companyConfiguration['sales_wise_branch_planning'] == 1){
			   		$info12['production_branch_id'] = 0;
			   	}else{
			   		$info12['production_branch_id'] = $branch_id;
			   	}

			   	$type="base_unit";
				$ret_qty=convert_stock($dbcon,$POST['product_qty'],$POST['product_id'],$type);

			   	$info12['product_qty']			= $POST['product_qty'];
			   	$info12['remaning_invoice_qty']	= $POST['product_qty'];
			   	$info12['remaning_invoice_conv_qty']	= $POST['product_conv_qty'];
			   	$info12['product_conv_qty']		= $ret_qty;
			   	$info12['unit_id']				= $inq_rel_unit['product_base_unit'];
			   	$info12['conv_unit_id']			= $inq_rel_unit['product_conv_unit'];
			   	$info12['rate_unit']			= $inq_rel['product_base_unit'];
			   	$info12['delivery_type']		= 'so_wise';

								//$info12['product_amount']	= $POST['product_amount'];
			   	//$info12['discount_per']		= $POST['discount_per'];
			   	//$info12['formulaid']			= $POST['formulaid'];
				//$info12['product_amount']	= $total=($POST['product_rate']*$POST['product_qty'])-$POST['product_discount'];
			   	$info12['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
			   	$info12['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
			   	$info12['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0 ;

			   	if($POST['currency_id']==$company_state['currency_id']){
				   	$info12['product_rate']			= $POST['product_rate'];
				   	//$info12['product_discount']	= $POST['product_discount'];
				   	$info12['product_amount']		= $POST['product_amount'];
				   	$info12['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
				   	$info12['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
				   	$info12['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
				   	$info12['total']				= $POST['product_amount']+$cgst_tax_rate+$sgst_tax_rate+$igst_tax_rate;
				   	
				   	$info12['product_rate_conv']	= $POST['product_rate']*$inq_rel['currency_rate'];
				   	//$info12['product_discount_conv']	= $POST['product_discount']*$POST['currency_rate'];
				   	$info12['product_amount_conv']	= $POST['product_amount']*$inq_rel['currency_rate'];
				   	$info12['cgst_tax_rate_conv']	= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
				   	$info12['sgst_tax_rate_conv']	= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
				   	$info12['igst_tax_rate_conv']	= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;
				   	$info12['total_conv']			= $info12['product_amount_conv']+$cgst_tax_rate_conv+$sgst_tax_rate_conv+$igst_tax_rate_conv;
			  	}else{
			  		$info12['product_rate']			= $POST['product_rate']*$inq_rel['currency_rate'];
				   	//$info12['product_discount']	= $POST['product_discount']*$POST['currency_rate'];
				   	$info12['product_amount']		= $POST['product_amount']*$inq_rel['currency_rate'];
				   	$info12['cgst_tax_rate']		= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
				   	$info12['sgst_tax_rate']		= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
				   	$info12['igst_tax_rate']		= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;
				   	$info12['total']				= $info12['product_amount']+$cgst_tax_rate_conv+$sgst_tax_rate_conv+$igst_tax_rate_conv;	

			  		$info12['product_rate_conv']		= $POST['product_rate'];
				   	//$info12['product_discount_conv']	= $POST['product_discount'];
				   	$info12['product_amount_conv']		= $POST['product_amount'];
				   	$info12['cgst_tax_rate_conv']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
				   	$info12['sgst_tax_rate_conv']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
				   	$info12['igst_tax_rate_conv']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
				   	$info12['total_conv']				= $POST['product_amount']+$cgst_tax_rate+$sgst_tax_rate+$igst_tax_rate;
			   	}

			   	$info12['product_tax_cat']	= $sale_gst['tax_cat_id'];
			   	if($companyConfiguration['trading_stock']!=0){
			   		$info12['bom_status']		= 1;
			   	}

			   	//$info=get_product_tax($dbcon,$POST['taxable_value'],$POST['formulaid']);
			   //	$info12=array_merge($info12,$info);
			   	//var_dump($info12);
			   	$table='tbl_sales_ordertrn';$tableid='sales_ordertrn_id';
			   	if(!empty($POST['sales_order_id']))
			   	{
			   		$info12['user_id']	= $POST['user_id'];
			   		$info12['sales_order_id']= $POST['sales_order_id'];
			   		$table='tbl_sales_ordertrn';
			   		$tableid='sales_ordertrn_id';
			   		$info12['with_out_stock_invoice']= $POST['with_out_stock_invoice'];
			   	}
			   	else
			   	{
			   		$info12['user_id']	= $POST['user_id'];
			   		$info12['sales_ordertrn_status']= 3;
			   	}

			   	if(isset($POST['product_attr']) && strtolower($POST['product_attr'])=='projectwise'){
			   		$info12['project_wise']= 1;
			   	}
				$inserid_acc=add_record($table, $info12, $dbcon, $branch_id);	
				
				if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
		   			$cl_id = get_ledger_by_name($dbcon,'CGST');
		   			$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid_acc,"tbl_sales_ordertrn",$POST['product_id'],3,$POST['edit_id'],$branch_id,$inq_rel['currency_id'],$inq_rel['currency_rate'],$cgst_tax_rate_conv);
		   		}
		   		if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
		   			$cl_id = get_ledger_by_name($dbcon,'SGST');
		   			$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid_acc,"tbl_sales_ordertrn",$POST['product_id'],3,$POST['edit_id'],$branch_id,$inq_rel['currency_id'],$inq_rel['currency_rate'],$sgst_tax_rate_conv);
		   		}
		   		if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
		   			$cl_id = get_ledger_by_name($dbcon,'IGST');
		   			$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid_acc,"tbl_sales_ordertrn",$POST['product_id'],3,$POST['edit_id'],$branch_id,$inq_rel['currency_id'],$inq_rel['currency_rate'],$igst_tax_rate_conv);
		   		}

				// check for the addiotional tax on product Start -- dhaval
	   			$pro_amt = $POST['product_amount']*$inq_rel['currency_rate'];
	   			$count_add_tax=get_check_addition_tax($dbcon,$sale_gst['tax_cat_id'],$POST['product_amount'],$inserid_acc,$POST['product_id'],$POST['edit_id'],$branch_id,'tbl_sales_ordertrn',$inq_rel['currency_id'],$inq_rel['currency_rate'],$pro_amt);
			}
			else if(strtolower($POST['mode']) == "order_review") 
	 		{
	 			$q = "select sales_order_no from tbl_sales_order where sales_order_id=".$POST['sales_order_id'];
				$result  = brp_mysqli_fetch_array($dbcon->query($q));

				$trn  = "select trn.sales_ordertrn_id,pro.product_name  from tbl_sales_ordertrn as trn
				left join product_mst as pro on pro.product_id = trn.product_id
				where sales_ordertrn_status=0 and trn.sales_order_id=".$POST['sales_order_id'];

				$resl = $dbcon->query($trn);
				$str ='';
				$str.='<option value="">Choose Product</option>';
				while($row = brp_mysqli_fetch_array($resl)){
					$str.= '<option value="' . $row['sales_ordertrn_id'] . '">' . $row['product_name'] . '</option>';
				} 
				$result['sales_product'] = $str;
				echo json_encode($result);
	 		}

	 		else if(brp_strtolower($POST['mode'])== "add_customer_to_company"){
				$arr['cust_id']=$cust_id=copy_ledger_cust($dbcon,'',$POST['crm_cust_id']);

				$arr['data'] = getcust($dbcon,$cust_id,'',1);
				echo json_encode($arr);
			}

			else if(brp_strtolower($POST['mode'])== "load_product_data"){
				$qry = "select pro.product_name,pro.product_id,trn.quot_trn_id from tbl_quotation_trn as trn 
				left join product_mst as pro on pro.product_id = trn.product_id
				where trn.quot_trn_status=0 and trn.quotation_id=".$POST['mquotation_id'];

				$result = $dbcon->query($qry);
				$str='';
				while($row = brp_mysqli_fetch_array($result)){
					$str .='<option value="'.$row['product_id'].'">'.$row['product_name'].'</option>';
				}
				$resp = $str;
				echo json_encode($resp);
			}	

			else if(brp_strtolower($POST['mode'])== 'load_product_dtls_quotation'){
				$company_data = get_company_data($dbcon, $_SESSION['company_id']);
				$companyConfiguration=getCompanyConfiguration($dbcon);
				$quotation_query = "select qtrn.product_qty,qtrn.unitid, qtrn.conv_unit_id, qtrn.product_id, qtrn.product_rate_conv, qtrn.product_rate, qtrn.orange, qtrn.mfg, qtrn.trading, qtrn.repairing, qtrn.other,qtrn.orange_total, qtrn.mfg_total, qtrn.trading_total, qtrn.repairing_total, qtrn.other_total, qtrn.product_desc,qtrn.product_spec,  pro.product_hsn, pro.product_icode, pro.product_gst, pro.product_base_unit, pro.product_category, pro.parent_category from tbl_quotation_trn as qtrn
				left join product_mst as pro on pro.product_id = qtrn.product_id
				where qtrn.quot_trn_id=".$POST['quot_trn_id'];
				$res_quot = $dbcon->query($quotation_query);
				$row_quot = brp_mysqli_fetch_array($res_quot);
				
				$salesorder_qty  ="select sum(product_qty) as base_qty,sum(product_conv_qty) as conv_qty, unit_id, conv_unit_id from tbl_sales_ordertrn where sales_ordertrn_status = 0 and quot_trn_id=".$POST['quot_trn_id']; 
				$res_so  = $dbcon->query($salesorder_qty); 
				$row_so  = brp_mysqli_fetch_array($res_so);

				$qry3="SELECT h.hsn_id,h.hsn_code,h.sale_gst,t.tax_gst,t.tax_cat_id FROM `mst_hsn_code` as h left join tbl_tax_category as t on t.tax_cat_id=h.sale_gst where h.hsn_status=0 and h.hsn_id=".$row_quot['product_hsn']." ";
				$sale_gst=brp_mysqli_fetch_assoc($dbcon->query($qry3));

				$row['tax_gst']=$sale_gst['tax_gst'];

				if($row_quot['unitid']==$row_quot['conv_unit_id']){
					$base_qty = $row_quot['product_qty'] - $row_so['base_qty'];
				}else{
					$type="conv_unit";
					$ret_qty=convert_stock($dbcon,$row_quot['product_qty'],$row_quot['product_id'],$type);
					$base_qty = $row_quot['product_qty'] - $row_so['base_qty'];
					$conv_qty = $ret_qty - $row_so['conv_qty'];
				}
				$row['product_qty']  	  = $base_qty;
				$row['product_conv_qty']  = $conv_qty;
				
				if($company_data['currency_id'] == $POST['currency_id']){
					$row['product_rate']  	  = $row_quot['product_rate_conv'];
				}else{
					$row['product_rate']  	  = $row_quot['product_rate_conv'];
				}

				if($companyConfiguration['so_discount_editable']==1){
					$row['discount_per']  = $row_quot['discount_per'];
				}else{
					$row['discount_per']  = '';
				}
				$row['product_category']  = $row_quot['product_category']; 
				$row['parent_category']	  = $row_quot['parent_category']; 
				$row['product_gst']		  = $row_quot['product_gst'];
				$row['unitid'] 			  = $row_quot['quot_unit'];
				$row['orange']			  =	$row_quot['orange'];	
				$row['mfg']				  = $row_quot['mfg'];
				$row['trading']			  = $row_quot['trading'];
				$row['repairing']		  = $row_quot['repairing'];
				$row['other']		      = $row_quot['other'];

				$row['orange_total']		  =	$row_quot['orange_total'];	
				$row['mfg_total']			  = $row_quot['mfg_total'];
				$row['trading_total']		  = $row_quot['trading_total'];
				$row['repairing_total']		  = $row_quot['repairing_total'];
				$row['other_total']		      = $row_quot['other_total'];

				$row['product_desc']	  = $row_quot['product_desc'];
				$row['product_spec']	  = $row_quot['product_spec'];
				$row['current_stock']	  = get_current_stock_new($dbcon, $row_quot['product_id'], $row_quot['product_base_unit']);
				/*var_dump($row['current_stock']);*/
				echo json_encode($row);
			}
			else if(brp_strtolower($POST['mode'])== 'add_quotation'){

				$so_trn['sales_ordertrn_status']	= 2;
				$updateid=update_record('tbl_sales_ordertrn', $so_trn,"sales_ordertrn_status=3 and user_id=".$POST['user_id'] , $dbcon);

				$getspecialConfiguration=getspecialConfiguration($dbcon);
			   	$companyConfiguration=getCompanyConfiguration($dbcon);
			   	$company_state = get_company_data($dbcon,$_SESSION['company_id']);

			   	$curncy_trn['currency_enable'] = 1;
		   		$curncy_trn['currency_id'] = $POST['currency_id'];
		   		$curncy_trn['currency_rate'] = $POST['currency_rate'];
				$quotation = $POST['quotation'];

				$tmp_quot_rem['so_multi_quot_status']=2;
				$updateid=update_record('tbl_salesorder_multiple_quot', $tmp_quot_rem,"so_multi_quot_status=3 and user_id=".$POST['user_id'] , $dbcon);
				foreach($quotation as $qt){ 
					$tmp_quot['quotation_id'] 		 = $qt;
					$tmp_quot['user_id']			 = $POST['user_id'];
			   		$tmp_quot['so_multi_quot_status']= 3;
			   		$tmp_quot['cdate']				 = date("Y-m-d H:i:s");

			   		$inserid=add_record('tbl_salesorder_multiple_quot', $tmp_quot, $dbcon);
				}
				
				$quotation = implode(",", $POST['quotation']);
				$query = "select qtrn.*,quot.gst_type, qtrn.unitid as quot_unit from tbl_quotation_trn as qtrn
				left join tbl_quotation as quot on quot.quotation_id = qtrn.quotation_id
				where qtrn.quotation_id in(".$quotation.") and qtrn.quot_trn_status=0";
				$result = $dbcon->query($query);
				while($row = brp_mysqli_fetch_array($result)){
					
				   	$product_detail = get_product_detail($dbcon,$row['product_id']);
					$custLedgerDetails = get_cust_data_arr($dbcon,$row['cust_id']);

					$so_qty = "select sum(product_qty) as so_base_qty, sum(product_conv_qty) as so_conv_qty from tbl_sales_ordertrn where sales_ordertrn_status !=2 and quot_trn_id=".$row['quot_trn_id'];

					$so_result = $dbcon->query($so_qty);
					$so_row = brp_mysqli_fetch_array($so_result);
					
				   	if($row['gst_type']==3){
				   		$sale_gst['tax_gst']=0.1;
				   		$sale_gst['tax_cat_id']=0;
				   	}else if($row['gst_type']==4){
				   		$sale_gst['tax_gst']=0;
				   		$sale_gst['tax_cat_id']=0;
				   	}else if($row['gst_type']==5){
				   		$sale_gst['tax_gst']=5;
				   		$sale_gst['tax_cat_id']=0;
				   	}else if($row['gst_type']==6){
				   		$sale_gst['tax_gst']=12;
				   		$sale_gst['tax_cat_id']=0;
				   	}else if($row['gst_type']==7){
				   		$sale_gst['tax_gst']=18;
				   		$sale_gst['tax_cat_id']=0;
				   	}else if($row['gst_type']==8){
				   		$sale_gst['tax_gst']=24;
				   		$sale_gst['tax_cat_id']=0;
				   	}else{
				   		$sale_gst = get_tax_cat_by_hsn_id($dbcon,trim($product_detail['product_hsn'])); 
				   	}

				   	$cgst_tax_rate=0;$cgst_tax_rate_conv=0;
				   	$sgst_tax_rate=0;$sgst_tax_rate_conv=0;
				   	$igst_tax_rate=0;$igst_tax_rate_conv=0;
				   	if($product_detail['product_gst'] == 'including'){
				   		$prorate = $row['product_rate_conv'] * 100 /(100 + $sale_gst['tax_gst']);
				   	}else{
				   		$prorate = $row['product_rate_conv']; 
				   	}

				   	if($product_detail['product_base_unit']==$row['quot_unit']){
				   		$qty = $info1['product_qty']			= $row['product_qty']-$so_row['so_base_qty'];
					   	$info1['remaning_invoice_qty']	= $row['product_qty']-$so_row['so_base_qty'];
					   	
					   	$type="conv_unit";
   						$ret_qty=convert_stock($dbcon,$info1['product_qty'],$row['product_id'],$type);
					   	$info1['product_conv_qty']		= $ret_qty;
					   	$info1['remaning_invoice_conv_qty']	= $ret_qty;
					   	
					   	$info1['unit_id']				= $product_detail['product_base_unit'];
					   	$info1['conv_unit_id']			= $product_detail['product_conv_unit'];
					   	$info1['rate_unit']				= $product_detail['product_base_unit'];	
				   	}else{
				   		
					   	$qty = $info1['product_conv_qty']		= $row['product_qty']-$so_row['so_conv_qty'];
						$type="base_unit";
   						$ret_qty=convert_stock($dbcon,$info1['product_conv_qty'],$row['product_id'],$type);
				   		$info1['product_qty']			= $ret_qty;
					   	$info1['remaning_invoice_qty']	= $ret_qty;
					   	$info1['remaning_invoice_conv_qty']	= $qty;
					   	$info1['unit_id']				= $product_detail['product_base_unit'];
					   	$info1['conv_unit_id']			= $product_detail['product_conv_unit'];
					   	$info1['rate_unit']				= $product_detail['product_conv_unit'];
				   	}
				   	$prod_amt = $prorate*$qty;
				   	$prod_disc = ($prod_amt*$row['discount_per'])/100;
				   	if(($company_state['stateid'] == $POST['cust_stateid']) && ($custLedgerDetails['enable_sez'] == 0)){
				   		$gst = $sale_gst['tax_gst']/2;
				   		$cgst_tax_per = $gst;
				   		$cgst_tax_rate = ($gst*($prod_amt-$prod_disc))/100;
				   		$cgst_tax_rate_conv = ($POST['currency_rate'] *$gst*$prod_amt-($prod_disc*$POST['currency_rate']))/100;
				   		$sgst_tax_per = $gst;
				   		$sgst_tax_rate = ($gst*($prod_amt-$prod_disc))/100;
				   		$sgst_tax_rate_conv = ($POST['currency_rate'] *$gst*$prod_amt-($prod_disc*$POST['currency_rate']))/100;
				   	}else{
				   		$igst_tax_per = $sale_gst['tax_gst'];
				   		$igst_tax_rate = ($sale_gst['tax_gst']*($prod_amt-$prod_disc))/100;
				   		$igst_tax_rate_conv = ($POST['currency_rate'] * $sale_gst['tax_gst'] * $prod_amt-($prod_disc * $POST['currency_rate']))/100;
				   	}

				   	if($companyConfiguration['branch_wise_manage']==1){
						$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
					}else{
						$branch_id =$companyConfiguration['default_branch_id'];
					}
				   
				   	$info1['quotation_id']  = $row['quotation_id'];
				   	$info1['quot_trn_id']   = $row['quot_trn_id'];
				   	$info1['inquiry_type']  = $POST['inquiry_type'];
				   	$info1['product_id']	= $row['product_id'];
				   	$info1['description']	= $row['product_desc'];
				   	$info1['product_disc']	= $row['product_desc'];
				   	$info1['product_spec']	= $row['product_spec'];
				   	$info1['product_hsn_code']	= $sale_gst['hsn_code'];
				   	if($getspecialConfiguration['elcon_permission'] ==1){
				   		$info1['product_item_code']	= $product_detail['product_icode'];
				   	}
				   	if($getspecialConfiguration['vipul_copper_permission'] ==1){
				   		$info1['product_category_id']	= $POST['product_category_id'];
				   		$info1['product_length']	= $POST['product_length'];
				   		$info1['product_pices']		= $POST['product_pices'];
				   	}

				   	if($companyConfiguration['sales_wise_branch_planning'] == 1){
				   		$info1['production_branch_id'] = 0;
				   	}else{
				   		$info1['production_branch_id'] = $branch_id;
				   	}

				   	
				   	
				   	$info1['delivery_type']			= $_POST['delivery_type'];

									
				   	$info1['discount_per']			= $row['discount_per'];
				   	$info1['formulaid']				= $row['formulaid'];
					
				   	$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
				   	$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
				   	$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0 ;

				   	if($POST['currency_id']==$company_state['currency_id']){
				   		$aamt = $prorate*$qty;
				   		$disc = $aamt*$row['discount_per']/100;
					   	$info1['product_rate']		= $prorate;
					   	$info1['product_amount']	= $aamt-$disc;
					   	$info1['product_discount']	= $disc;
					   	$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
					   	$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
					   	$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
					   	$info1['total']				= $row['product_amount_conv']+$cgst_tax_rate+$sgst_tax_rate+$igst_tax_rate;
					   	
					   	$info1['product_rate_conv']		= $prorate*$POST['currency_rate'];
					   	$info1['product_amount_conv']	= ($aamt*$POST['currency_rate'])-($disc*$POST['currency_rate']);
					   	$info1['product_discount_conv']	= $disc*$POST['currency_rate'];
					   	$info1['cgst_tax_rate_conv']	= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
					   	$info1['sgst_tax_rate_conv']	= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
					   	$info1['igst_tax_rate_conv']	= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;
					   	$info1['total_conv']			= $info1['product_amount_conv']+$cgst_tax_rate_conv+$sgst_tax_rate_conv+$igst_tax_rate_conv;
				  	}else{
				  		$aamt = $prorate*$qty;
				   		$disc = $aamt*$row['discount_per']/100;

				  		$info1['product_rate']		= $prorate*$POST['currency_rate'];
					   	$info1['product_amount']	= ($aamt*$POST['currency_rate'])-($disc*$POST['currency_rate']);
					   	$info1['product_discount']	= $disc*$POST['currency_rate'];
					   	$info1['cgst_tax_rate']		= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
					   	$info1['sgst_tax_rate']		= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
					   	$info1['igst_tax_rate']		= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;
					   	$info1['total']				= $info1['product_amount']+$cgst_tax_rate_conv+$sgst_tax_rate_conv+$igst_tax_rate_conv;	

				  		$info1['product_rate_conv']		= $prorate;
					   	$info1['product_amount_conv']	= $aamt-$disc;
					   	$info1['product_discount_conv']	= $disc;
					   	$info1['cgst_tax_rate_conv']	= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
					   	$info1['sgst_tax_rate_conv']	= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
					   	$info1['igst_tax_rate_conv']	= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
					   	$info1['total_conv']			= $row['product_amount_conv']+$cgst_tax_rate+$sgst_tax_rate+$igst_tax_rate;
				   	}

				   	$info1['product_tax_cat']	= $sale_gst['tax_cat_id'];
				   	if($companyConfiguration['trading_stock']!=0){
				   		$info1['bom_status']		= 1;
				   	}

				   	//$info=get_product_tax($dbcon,$POST['taxable_value'],$POST['formulaid']);
				   //	$info1=array_merge($info1,$info);
				   	//var_dump($info1);
				   	$table='tbl_sales_ordertrn';$tableid='sales_ordertrn_id';
				   	if(!empty($POST['sales_order_id']))
				   	{
				   		$info1['user_id']	= $POST['user_id'];
				   		$info1['sales_order_id']= $POST['sales_order_id'];
				   		$table='tbl_sales_ordertrn';
				   		$tableid='sales_ordertrn_id';
				   		$info1['with_out_stock_invoice']= $POST['with_out_stock_invoice'];
				   	}
				   	else
				   	{
				   		$info1['user_id']	= $POST['user_id'];
				   		$info1['sales_ordertrn_status']= 3;
				   	}

				   	if(isset($POST['product_attr']) && strtolower($POST['product_attr'])=='projectwise'){
				   		$info1['project_wise']= 1;
				   	}


				   	
			   		$inserid=add_record($table, array_merge($info1,$curncy_trn), $dbcon,$branch_id);
			   		$updateinfo['salesorder_trn_id'] = $inserid;
			   		$tax_trn_id=$inserid;


			   		$updateins['prev_sales_ordertrn_id'] = $inserid; 
			   		update_record('tbl_sales_ordertrn', $updateins, "salesorder_trn_id=".$inserid , $dbcon, $branch_id);
				   

				   	if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
						$cl_id = get_ledger_by_name($dbcon,'CGST');
						$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid,"tbl_sales_ordertrn",$row['product_id'],3,$POST['edit_id'],$branch_id,$POST['currency_id'],$POST['currency_rate'],$cgst_tax_rate_conv);
					}
					if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
						$cl_id = get_ledger_by_name($dbcon,'SGST');
						$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid,"tbl_sales_ordertrn",$row['product_id'],3,$POST['edit_id'],$branch_id,$POST['currency_id'],$POST['currency_rate'],$sgst_tax_rate_conv);
					}
					if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
						$cl_id = get_ledger_by_name($dbcon,'IGST');
						$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid,"tbl_sales_ordertrn",$row['product_id'],3,$POST['edit_id'],$branch_id,$POST['currency_id'],$POST['currency_rate'],$igst_tax_rate_conv);
					}

					// check for the addiotional tax on product Start -- dhaval
					$pro_amt = $POST['product_amount_conv']*$POST['currency_rate'];
					$count_add_tax=get_check_addition_tax($dbcon,$sale_gst['tax_cat_id'],$row['product_amount_conv'],$inserid,$row['product_id'],$POST['edit_id'],$branch_id,'tbl_sales_ordertrn',$POST['currency_id'],$POST['currency_rate'],$pro_amt);
				}

				if($inserid){
					$arr['msg'] = 1;
				}else{
					$arr['msg'] = 0;
				}
				echo json_encode($arr);
			}
			else if(brp_strtolower($POST['mode'])== 'get_quotation_data_so'){
				$html ='';

				if($POST['sales_order_id']){
					$where = "ref.sales_ordertrn_status=0 and ref.sales_order_id=".$POST['sales_order_id'];

					$query = "select term_quotation_id from tbl_sales_order where sales_order_id=".$POST['sales_order_id'];
					$res = brp_mysqli_fetch_array($dbcon->query($query));
				}else{
					$where = "ref.sales_ordertrn_status=3 and ref.user_id=".$_SESSION['user_id'];
				}
				$query = "select ref.quotation_id,quot.quotation_no from tbl_sales_ordertrn as ref 
				left join tbl_quotation as quot on quot.quotation_id = ref.quotation_id
				where ".$where." group by quotation_id";
				$result = $dbcon->query($query);

				while($row = brp_mysqli_fetch_array($result)){
					$selected  = '';
					if($row['quotation_id'] == $res['term_quotation_id']){
						$selected = 'selected="selected"';
					}
					$html.='<option '.$selected.' value="'.$row['quotation_id'].'">'.$row['quotation_no'].'</option>';
				}
				$resp['resp_html'] = $html;
				$resp['term_quotation_id'] = $res['term_quotation_id'];
				echo json_encode($resp);
			}	

			else if(brp_strtolower($POST['mode'])=='load_parent_cat'){
		        $html='';
		        $query = "select * from tbl_category where cat_status=0 and cat_pid=".$POST['parent_id'];
		        $result = $dbcon->query($query);
		        $html.='<option value="">Choose Category</option>';
		        while($row = brp_mysqli_fetch_array($result)){
		            $html .= '<option value="'.$row['cat_id'].'">'.$row['cat_name'].'</option>';
		        }
		        echo $html;
		    }

		    else if(brp_strtolower($POST['mode']) == "show_stock_new") {

		    	if(!empty($POST['sales_order_trn_id'])){
		    		$que_so="select * from tbl_sales_ordertrn where sales_ordertrn_id=".$POST['sales_order_trn_id'];
					$resi_so=$dbcon->query($que_so);
					$re_so=brp_mysqli_fetch_assoc($resi_so);


					$product_id=$re_so['product_id'];
					$branch_id=$re_so['branch_id'];
					$unit_id=$re_so['unit_id'];	
		    	}else{
		    		$product_id=$POST['product_id'];
					$branch_id=$POST['branch_id'];
					$unit_id=$POST['base_unit'];
		    	}
				
							//$rp_id=$POST['rp_id'];

				$que_po="select batch_wise_stock_manage from product_mst where product_id=".$product_id;
				$resi_grn=$dbcon->query($que_po);
				$re=brp_mysqli_fetch_assoc($resi_grn);


							//$god_stock=req_stock_entry();
							//$wipstock=req_wipstock_entry();
				$str=' 
				<div class="col-md-12" style="font-size: 25px;"><center><strong>Warehouse Stock</strong></center></div>
				<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
				<tr>
				<td style="font-weight: 600;">Warehouse</td>';
				if($re['batch_wise_stock_manage']==1){
					$str .='<td style="font-weight: 600;">Batch No</td>';
				}
				$str .='<td style="font-weight: 600;">Stock</td>
				<td style="font-weight: 600;">Reserve Stock</td>
				<td style="font-weight: 600;">Action</td>
				</tr>
				<tr>';
				if($re['batch_wise_stock_manage']==1){
					$str .='<td>
					<select class="form-control"  title="Select" placeholder="" name="st_godown_id" id="st_godown_id" onchange="load_godown_wise_stock();load_batch_no();">
					'.load_available_stock_godown($dbcon,$product_id,$branch_id).'
					</select>
					</td>
					<td>
					<select class="form-control"  title="Select" placeholder="" name="st_stock_id" id="st_stock_id" onchange="load_godown_wise_stock();">
					</select>
					</td>';
				}else{
					$str .='<td>
					<select class="form-control"  title="Select" placeholder="" name="st_godown_id" id="st_godown_id" onchange="load_godown_wise_stock();">
					'.get_all_godown($dbcon,'','').'
					</select>
					</td>
					<!--<td>
					<select class="form-control"  title="Select" placeholder="" name="st_stock_id" id="st_stock_id" >
					</select>
					</td>-->';
				}
				$str .='<td>
				<input type="number"  title="Stock" min="0" id="st_stock_total" name="st_stock_total"  class="form-control numbersOnly" readonly />
				</td>
				<td>
				<input type="number"  title="Enter Stock" min="0" id="st_stock_reserve" name="st_stock_reserve"  class="form-control numbersOnly"  />
				</td>
				<td>
				<input type="button"  name="addrow" id="addrow" onClick="return add_reserve_temp();"  class="btn btn-primary" value="Add"/>
				</td>
				</tr>
				</table>
				<input type="hidden" name="batch_wise_stock_manage" id="batch_wise_stock_manage" value="'.$re['batch_wise_stock_manage'].'" />
				<div id="reserve_productdata"></div>';

				$str .='<div class="col-md-12" >
				<center>
				<input type="button"  name="" id="" onClick="return add_field();"  class="btn btn-primary" value="Save"/>

				<input type="hidden" name="product_id_model" id="product_id_model" value="'.$product_id.'" />
				<input type="hidden" name="unit_id_model" id="unit_id_model" value="'.$unit_id.'" />
				
				</center>
				</div>
				';


				echo $str;
			}
			else if(strtolower($POST['mode']) == "load_reserve_data") {
				if(empty($POST['sales_ordertrn_id'])){
					$query = "select trn.work_order_reserve_temp_id,trn.reserve_qty,cat.gd_name,uns.unit_name,st.batch_no from work_order_reserve_temp as trn
					left join mst_godown as cat on cat.gd_id=trn.godown_id
					left join unit_mst as uns on uns.unitid=trn.unit_id
					left join tbl_stock_trn as st on st.stock_id=trn.stock_id
					where trn.status=0 and trn.rp_id=0 and trn.sales_ordertrn_id=0 and trn.user_id=".$_SESSION['user_id'].' and trn.product_id='.$POST['product_id'];
				}else{
					$query="select trn.work_order_reserve_temp_id,trn.reserve_qty,cat.gd_name,uns.unit_name,st.batch_no from work_order_reserve_temp as trn
					left join mst_godown as cat on cat.gd_id=trn.godown_id
					left join unit_mst as uns on uns.unitid=trn.unit_id
					left join tbl_stock_trn as st on st.stock_id=trn.stock_id
					where trn.status in (0,3) and trn.sales_ordertrn_id=".$POST['sales_ordertrn_id'];
				}

				/*echo $query;*/
				$result=$dbcon->query($query);
				echo '<div class="form-group">
				<div class="col-md-12 col-xs-11">
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
				<tr id="field">
				<th class="text-center" width="10%">Warehouse</th>';
				if($POST['batch_wise_stock_manage']==1){
					echo '<th class="text-center"width="15%">Batch No</th>';
				}
				echo '<th class="text-center"width="15%">Reserve Stock</th>
				<th class="text-center"width="10%">Action</th>
				</tr>';

					//echo $query;
				if(mysqli_num_rows($result)>0)
				{
					$i=1;$total=0;
					while($rel=brp_mysqli_fetch_assoc($result))
					{

						echo '<tr id="fieldtr'.$i.'">
						<td style="vertical-align:top;" class="text-left">
						'.$rel['gd_name'].'
						</td>';				
						if($POST['batch_wise_stock_manage']==1){
							echo '<td style="vertical-align:top;" class="text-left">
							'.$rel['batch_no'].'
							</td>';
						}
						echo '<td style="vertical-align:top;" class="text-center">
						'.$rel['reserve_qty'].' '.$rel['unit_name'].'
						</td>					

						<td style="vertical-align:top">

						<!--<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['purchaseordertrn_id'].',\'tbl_purchaseordertrn\',\'purchaseordertrn_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>-->

						<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_stock('.$rel['work_order_reserve_temp_id'].');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
						</tr>';
						$total=$total+$rel['reserve_qty'];
						$i++;
					}
				}

				else{
					echo '<tr><td colspan="12" class="text-center">NO DATA FOUND</td></tr>';
				}
				echo '</table> 
				<input type="hidden" name="gstock_total" id="gstock_total" value="'.$total.'" />
				</div>
				</div>';
			}
			else if(brp_strtolower($POST['mode']) == "godown_stock") {
				$gstock=0;$rstock=0;
				$batch_id=$POST['batch_id'];
				$gstock=get_current_godown_stock_new($dbcon,$POST['product_id'],$POST['unit_id'],$POST['st_godown_id'],$branch_id,$batch_id);

				$rstock=reserve_stock($dbcon,$POST['product_id'],$POST['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$batch_id);


				$stock=$gstock-$rstock;
					//var_dump($gstock);
					//var_dump($stock);
					//var_dump($gstock-$rstock);
				echo $stock;
			}
			else if(strtolower($POST['mode'])== "delete_data_stock")
			{
				$row=array();
				$info['status']=2;	
				$updateid=update_record("work_order_reserve_temp", $info, "work_order_reserve_temp_id=".$POST['eid'] , $dbcon);

				if($updateid)
					$row['res']="1";
				else
					$row['res']="0";
				echo json_encode($row);
			}
			else if(strtolower($POST['mode'])== "load_batch_no")
			{
				$godwn_id=$POST['godwn_id'];
				$product_id=$POST['product_id'];
				$customer_id=$POST['customer_id'];
				$unit_id = $POST['unit_id'];

				$unitname = getunitname($dbcon,$unit_id);

				$query="select batch_no,stock_id from tbl_stock_trn as trn
				where trn.stock_status=0 and stock_flage=1 and product_id=".$product_id." and trn.godown_id=".$godwn_id." and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5))";


				//echo $query;
				$str="";
				$result=$dbcon->query($query);
				if(mysqli_num_rows($result)>0)
				{	
					$str .= '<option value="">Select Batch Data</option>';
					$i=1;
					while($rel=brp_mysqli_fetch_assoc($result))
					{
						$gstock=0;$rstock=0;
							$batch_id=$POST['stock_id'];
							
							$gstock=get_current_godown_stock_new($dbcon,$product_id,$unit_id,$godwn_id,$branch_id,$batch_id,$customer_id);

							$rstock=reserve_stock($dbcon,$product_id,$unit_id,$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$batch_id);


							$stock=$gstock-$rstock;

						$str .= '<option value="'.$rel['stock_id'].'">'.$rel['batch_no'].' - (' . $stock . ' '. $unitname . ')</option>';
					}
				}else{
					$str .= '<option value="">No Batch Data !!</option>';
				}

				echo $str;
			}

			else if(brp_strtolower($POST['mode']) == "add_reserve_data_temp") {
				$info1['sales_ordertrn_id']	= $POST['sales_ordertrn_id'];
				$info1['reserve_qty']		= $POST['st_stock_reserve'];
				$info1['unit_id']			= $POST['unit_id'];
				$info1['godown_id']			= $POST['st_godown_id'];
				$info1['product_id']		= $POST['product_id'];
				$info1['stock_id']			= $POST['st_stock_id'];

				$info1['cdate']				= date('Y-m-d H:i:s');
				$info1['user_id']			= $_SESSION['user_id'];	
				$info1['company_id']		= $_SESSION['company_id'];	
				
				$inserpoid=add_record('work_order_reserve_temp',$info1, $dbcon, $branch_id);

				if($inserpoid){
					echo 1;
				}
			}

			else if(brp_strtolower($POST['mode']=='get_terms_detail')){
				$query = 'select * from tbl_terms_condition where tc_id='.$POST['tc_id'];
				$result  = $dbcon->query($query);
				$row = brp_mysqli_fetch_array($result);
				
				if(empty($row['tc_details'])){
					$row['tc_details']='';
				}
				echo json_encode($row);
			}else if(brp_strtolower($POST['mode']=='get_product_filter_option')){
				$getspecialConfiguration = getspecialConfiguration($dbcon);
				$str = "";
				if($getspecialConfiguration['power_drive']==1){
                    $query_field = "select * from tbl_item_master_field where item_master_field_status=0 and company_id=".$_SESSION['company_id']." order by priority ASC";
                    $res_field = $dbcon->query($query_field);
                    $ro_cnt = brp_mysqli_num_rows($res_field);
                    $field=1;$counter=1;
                    while($row_field = brp_mysqli_fetch_array($res_field)){
                        $field_name = $row_field['item_master_field_db_name'];
                        if($field==1){
                           	$str .= '<div class="col-md-12 margin_row " style="margin-top:15px">';  
                 	    }
                    $str .= '<div class="col-md-4">
                        <div class="form-group">
                            <label class="col-md-4 control-label">'.$row_field['item_master_field'].'*</label>
                            <div class="col-md-8 col-xs-11">
                               <select class="select2 dynamic_field" name="'.$row_field['item_master_field_db_name'].'" id="field_id'.$field.'" title="'.$row_field['item_master_field'].'" onchange="generate_product_name_for_search();">
                                    <option value="" data-pcode="">--CHOOSE '.$row_field['item_master_field'].'--</option>
                                    '.get_field_value($dbcon,$rel_field[$field_name],$row_field['item_master_field_id']).'
                               </select>
                            </div>
                        </div>
                    </div>';
                     if($ro_cnt == $field){
                    $str .='</div>';
                    }else{
                        if($counter==3){ 
                            $counter=0;
                    
                     $str .='</div><div class="col-md-12 margin_row" style="margin-top:15px">';
                    }
                }

                $field++;
                $counter++;

            }
                $str .='<input type="hidden" name="dynamic_field" id="dynamic_field" value="'.($field-1).'">';

        }
        echo $str;
			}else if(brp_strtolower($POST['mode']=='load_trans_add')){
				$eid=$POST['edit_id'];
				$query = 'select * from transportation_address where transportation_id='.$POST['tc_id'];
				$result  = $dbcon->query($query);
				$str = '';
       			 $str .= '<option value="">Choose Transport Address</option>';
				while($row = brp_mysqli_fetch_array($result)){
					$sel = '';
					if ($row['id'] == $eid)
					{
						$sel = 'selected="selected"';
					}
					$str .= '<option ' . $sel . ' value="' . $row['id'] . '">' . $row['transportation_address'] . '</option>';
				}

				$row['html']=$str;
				
				echo json_encode($row);
			}
			else if(brp_strtolower($POST['mode']=='load_consingy_address')){
				$query_con="select cust.cust_name,cust.company_name,cust.cust_address,cust.cust_mobile,cust.cust_email,cust.cust_pincode,cust.gst_no,country.country_name,state.state_name,city.city_name,state.gst_state_code from tbl_custmer_consignee as cust 
					left join country_mst as country on country.countryid=cust.countryid
					left join state_mst as state on state.stateid=cust.stateid
					left join city_mst as city on city.cityid=cust.cityid
					where cust_id=".$POST['consignee_id'];
					$rel_con=brp_mysqli_fetch_assoc($dbcon->query($query_con));	
					$cpincode="";
					if(!empty($rel_con['cust_pincode'])){
						$cpincode="- ".$rel_con['cust_pincode'];
					}
					$contact_person = $rel_con['cust_name'];

					$party_address_con="<strong>".$rel_con['company_name']."</strong>
					<span style='font-weight:normal;'> <br/>
					".$rel_con['cust_address'].",<br/>
					".$rel_con['cust_pincode']."
					".$rel_con['city_name'].",
					".$rel_con['state_name'].",
					".$rel_con['country_name']."</span><br/>
					Mobile No.: ".$rel_con['cust_mobile']."<br/>
					Email Id: ".$rel_con['cust_email']."
					<br>  State Code : ".$rel_con['gst_state_code']."
					<br>  GSTIN : ".$rel_con['gst_no'];

					echo $party_address_con;
			}

			

		function get_product_tax($dbcon,$product_amount,$formulaid)
		{
			$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid." order by tax_value desc";
			$row=$dbcon->query($qry);
			$rate_total=$total=$product_amount;
			$i=1;
			while($tax=mysqli_fetch_assoc($row))
			{	
				$info['tax_name'.$i]=$tax['tax_name'];
				$info['tax_amount'.$i]=$tax_amount=($total)*$tax['tax_value']/100;
				$rate_total+=$tax_amount;
				$i++;
			}
			for($j=$i;$j<=3;$j++)
			{
				$info['tax_name'.$i]='';
				$info['tax_amount'.$i]='';		
			}
			$info['total']=$rate_total;
			return $info;
		}



		

		function upload_so_image($FILES,$dbcon,$so_id){
			$cnt=count($_FILES['po_document']['name']);
			for($i=0 ; $i < $cnt ; $i++ ) {
				if(!empty($_FILES['po_document']['tmp_name'][$i])) {
					$rand=rand(0,999999);
					$temp = explode(".", $_FILES["po_document"]["name"][$i]);
					$extension = strtolower(end($temp));
					$file_name = $_FILES['po_document']['name'][$i];
					$err = $_FILES["po_document"]["tmp_name"][$i];
					$file_name = "so_".$rand.'.'.$extension;
					move_uploaded_file($err,$file_name);

					$attch['so_id']			= $so_id;
					$attch['so_file']		= $file_name;
					$attch['cdate']			= date("Y-m-d H:i:s"); 
					$attch['user_id']		= $_SESSION['user_id'];
					$attch['company_id']	= $_SESSION['company_id']; 
					$inserid=add_record('tbl_so_attch', $attch, $dbcon);
			//return 	$file_name;
				}
			}
		}

		function load_amount_cust($dbcon, $vendor_id = false){
			$query="select cust.opn_balance,cust.balance_typeid,
			(SELECT sum(g_total) FROM `tbl_invoice` as inv where inv.cust_id=cust.l_id and inv.invoice_status=0) as invoice_amount,
			(SELECT sum(excess_amount) FROM `tbl_excess` as cr_exc where cr_exc.cust_id=cust.l_id and cr_exc.status!=2 and cr_exc.excess_type=1) as cr_excess_amount,
			(SELECT sum(excess_amount) FROM `tbl_excess` as dr_exc where dr_exc.cust_id=cust.l_id and dr_exc.status!=2 and dr_exc.excess_type=2) as dr_excess_amount,
			(SELECT sum(g_total) FROM `tbl_pono` as po where po.vender_id=cust.l_id and po.status=0 and po.approve_status=1) as po_amount,
			(SELECT sum(paid_amount) FROM `tbl_expense_detail` as exp where exp.emp_id=cust.l_id and exp.expense_status=0 and exp.expense_approve_status=1) as exp_amount,
			(SELECT sum(rec_trn.total_amount) FROM `tbl_receipt` as rec
			left join tbl_receipt_trn as rec_trn on rec_trn.receipt_id=rec.receipt_id
			where rec.cust_id=cust.l_id and rec.status!=2 and rec_trn.status=0 and rec_trn.payment_type=1) as paid_amount,
			(SELECT sum(rec_trn.total_amount) FROM `tbl_receipt` as rec
			left join tbl_receipt_trn as rec_trn on rec_trn.receipt_id=rec.receipt_id
			where rec.cust_id=cust.l_id and rec.status!=2 and rec_trn.status=0 and rec_trn.payment_type=2) as purchasepaid_amount

			from tbl_ledger as cust where cust.l_id=".$vendor_id;
			$rel=mysqli_fetch_assoc($dbcon->query($query));

			$op_balance=0;

			if(!empty($rel['opn_balance']))
			{
				$op_balance=($rel['balance_typeid']=="2"?-($rel['opn_balance']):$rel['opn_balance']);
			}
			$amount=($op_balance+$rel['paid_amount']+$rel['po_amount']+$rel['exp_amount']+$rel['cr_excess_amount']+$rel['credit_amount'])-($rel['invoice_amount']+$rel['proinvoice_amount']+$rel['dr_excess_amount']+$rel['purchasepaid_amount']+$rel['debit_amount']);


			return abs($amount);
		}

/*
Code By Umair: 14/07/2021
Commnet: Copy the Quotation project trn to Sales project trn
START
*/
function upload_attch_file($FILES)
{
	$rand=rand(0,99999999);
	if(!empty($FILES['doc_attach']['tmp_name'])) {
		$temp = explode(".", $FILES["doc_attach"]["name"]);
		$extension = strtolower(end($temp));
		$File = "so_attach".$rand.".".$extension;
		$tmp_name = $FILES["doc_attach"]["tmp_name"];
		move_uploaded_file($tmp_name,SO_ATTACH_UPING.$File);

		return  $File;				
	}
}
function copy_quotation_project_trn_ro_salesorder_project_trn($dbcon, $quotation_id, $salesorder_trn_id, $branch_id, $type=null){
	
	
	
	  $copy_qry="Insert into tbl_salesorder_project_trn (inquiry_id,quotation_id,sales_order_id,salesorder_trn_id,inquiry_type,project_assign_id,product_category_id,product_id,description,product_hsn_code, product_qty,product_rate,product_amount,formulaid,cgst_tax_per,cgst_tax_rate,sgst_tax_per, sgst_tax_rate,igst_tax_per,igst_tax_rate,product_total,salesorder_projecttrn_status,user_id,company_id,branch_id,product_disc,product_spec) 
		select inquiry_id,quotation_id,0,".$salesorder_trn_id.",inquiry_type,project_assign_id,product_category_id,product_id,description,product_hsn_code, product_qty,product_rate,product_amount,formulaid,cgst_tax_per,cgst_tax_rate,sgst_tax_per,sgst_tax_rate,igst_tax_per,igst_tax_rate,product_total,3,".$_SESSION['user_id'].",company_id,branch_id,product_disc,product_spec from tbl_quotation_project_trn where quotation_projecttrn_status=0 and quotation_id=".$quotation_id;
		$copy_qry_rs=$dbcon->query($copy_qry);
		$quotation_trn_id = $dbcon->insert_id;

	/* $inq_qry="select * from tbl_quotation_project_trn where quotation_projecttrn_status=0 and quotation_id='".$quotation_id."' ";
	$inq_qry_rs=$dbcon->query($inq_qry);
	
	while($inq_rel=mysqli_fetch_assoc($inq_qry_rs)){
		
		
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
		
		
		
		
		
		$info1['inquiry_id']	= $inq_rel['inquiry_id'];
		$info1['quotation_id']	= $quotation_id;
		$info1['sales_order_id']	= 0;
		$info1['salesorder_trn_id']	= $salesorder_trn_id;
		$info1['inquiry_type']	= $inq_rel['inquiry_type'];
		$info1['project_assign_id']		= $inq_rel['project_assign_id'];
		$info1['product_category_id']		= $inq_rel['product_category_id'];
		$info1['product_id']	= $inq_rel['product_id'];
		$info1['description']	= $inq_rel['description'];
		$info1['product_hsn_code']= $inq_rel['product_hsn_code'];
		$info1['product_qty']= $inq_rel['product_qty'];
		$info1['product_rate']= $inq_rel['product_rate'];
		$info1['product_amount']    = $inq_rel['product_amount'];
		$info1['formulaid']         = $inq_rel['formulaid'];
		$info1['product_disc']= $inq_rel['product_disc'];
		$info1['product_spec']= $inq_rel['product_spec'];
		$info1['salesorder_projecttrn_status']		= 0;
		
		//$info=get_product_common_tax($dbcon,$info1['product_amount'],$info1['formulaid']);
		//$info1=array_merge($info1,$info);
		$info1['cgst_tax_per']			= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
		$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
		$info1['sgst_tax_per']			= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
		$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
		$info1['igst_tax_per']			= isset($igst_tax_per) ? $igst_tax_per : 0 ;
		$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
		$info1['product_total']		= $t_g_amount;
		$info1['user_id']		= $_SESSION['user_id'];
		$info1['company_id']	= $_SESSION['company_id'];

		$inserid=add_record("tbl_salesorder_project_trn", $info1, $dbcon, $branch_id);

	} */
}
/*  */

function get_to_email_from_user_id($dbcon,$user_id,$to){

	$query="select user_mail,report_to_user_id from users where user_id =".$user_id;

	$res=$dbcon->query($query);
	$rel=mysqli_fetch_assoc($res);

	$email = $rel['user_mail'];
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		array_push($to,$email);
	}

	// $arr[] = $email;
	// array_push($to,$email);
	if($rel['report_to_user_id'] > 0){
		$to =	get_to_email_from_user_id($dbcon,$rel['report_to_user_id'],$to);

	}
	return $to;
}
function umaboy_sales_order_print($dbcon,$sales_order_id,$save_file){
	$type='pdf';
	if(strtolower($type) == 'pdf') {
//Quotation Data
		$query="select invoice.*,country.country_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust.cust_pincode,cust.cust_mobile,cust.gst_no,cust.m_pan,per.c_con_fname,per.c_con_lname,state.gst_state_code,td.transportation_name,cust.cust_cont_name, cust.stateid from tbl_sales_order as invoice 
		left join tbl_ledger as cust on cust.l_id=invoice.cust_id
		left join tbl_cust_contact as per on per.cust_id = invoice.cust_id
		left join country_mst as country on country.countryid=cust.countryid
		left join state_mst as state on state.stateid=cust.stateid
		left join city_mst as city on city.cityid=cust.cityid
		left join transportation_details as td on td.id=invoice.transport_id
		where sales_order_id=$sales_order_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));

		$transportation_name = ($rel['transportation_name']!='0')?$rel['transportation_name']:'';
		$po_date = '';
		if($rel['po_date']!="1970-01-01 00:00:00" && $rel['po_date']!="0000-00-00 00:00:00")
		{
			$po_date=date('d-m-Y',strtotime($rel['po_date']));
		}
		$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
		WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
		$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
		$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
		$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';

		$so_date = '';
		if($rel['sales_order_date']!="1970-01-01 00:00:00" && $rel['sales_order_date']!="0000-00-00 00:00:00")
		{
			$so_date=date('d-m-Y',strtotime($rel['sales_order_date']));
		}
		if($rel['delivery_date']!="1970-01-01" && $rel['delivery_date']!="0000-00-00")
		{
			$delivery_date=date('d-m-Y',strtotime($rel['delivery_date']));
		}

//echo "<pre>";print_r($rel);die();
		if(!$rel){
			header("Location: ".ROOT.CRM_ROOT."sales_order_list");
		}

		$HowManyWeeks = (strtotime( $rel['cdate'] ) - strtotime( $rel['sales_order_date'])) / 604800;
		$HowManyWeeks = round($HowManyWeeks);
		$HowManyWeeks = ($HowManyWeeks=='0')?'0':$HowManyWeeks;
		$delivery_week = $HowManyWeeks .' - '. ($HowManyWeeks+1) . ' WEEKS';

		$order_by = ($rel['order_by']!='0')?$rel['order_by']:"";

		$party_address_billing="<strong>".$rel['company_name']."</strong>
		<span style='font-weight:normal;'> <br/>
		".$rel['cust_address'].",<br/>
		".$rel['cust_pincode']."
		".$rel['city_name'].",
		".$rel['state_name'].",
		".$rel['country_name']."</span>
		<br>  State Code : ".$rel['gst_state_code']."
		<br>  GSTIN : ".$rel['gst_no'];

		if($rel['consignee_id']==0){
			$contact_person = $rel['cust_cont_name'];
			$party_address_con=$party_address_billing;
			$cust_mobile = $rel['cust_mobile'];

		}else{
			$query_con="select cust.cust_name,cust.company_name,cust.cust_address,cust.cust_mobile,cust.cust_email,cust.cust_pincode,cust.gst_no,country.country_name,state.state_name,city.city_name,state.gst_state_code from tbl_custmer_consignee as cust 
			left join country_mst as country on country.countryid=cust.countryid
			left join state_mst as state on state.stateid=cust.stateid
			left join city_mst as city on city.cityid=cust.cityid
			where cust_id=".$rel['consignee_id'];
			$rel_con=brp_mysqli_fetch_assoc($dbcon->query($query_con));	
			$cpincode="";
			if(!empty($rel_con['cust_pincode'])){
				$cpincode="- ".$rel_con['cust_pincode'];
			}
			$contact_person = $rel_con['cust_name'];
			$cust_mobile = $rel_con['cust_mobile'];
			$party_address_con="
			<strong>".$rel_con['cust_name']."</strong>
			<span style='font-weight:normal;'> <br/>
			".$rel_con['cust_address'].",<br/>
			".$rel_con['cust_pincode']."
			".$rel_con['city_name'].",
			".$rel_con['state_name'].",
			".$rel_con['country_name']."</span>
			<br>  State Code : ".$rel_con['gst_state_code']."
			<br>  GSTIN : ".$rel_con['gst_no'];
		}

		$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';
//Company Data
/*$comp_qry="select * from tbl_company as comp 
where comp.company_id=".$rel['company_id'];
*/
$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
//CHEMITEK PROCESS EQUIPMENTS PRIVATE LIMITED		
//>Plot No : 117 , Nr. GETCO Sub -Station , Old GIDC , Gundlav Valsad - 396035, Gujarat, India
$comp_rel=mysqli_fetch_assoc($dbcon->query($set));

$html='';
$header ='
<table >
<tr style="border: 0px; ">
<td  style="border: 0px; "><img src="'.DOMAIN_F.LOGO.$comp_rel['logo'].'" style="width:8.27in" /></td>

</tr>
<tr style="border: 0px; ">
<td style="text-align:center;font-size:17px;"><b>Sales Order</b></td>
</tr>
</table>';
$footer='';
$html.='<html>
<head>					
<title>Sales Order - '.$rel['sales_order_no'].'</title>

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
	<div style="text-align:center">'.$header.'</div>
	</htmlpageheader>
	<!--<htmlpagefooter name="otherpages_footer" style="display:none">
	<div style="text-align:center">'.$footer.'</div>
	</htmlpagefooter>-->
	<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
	<div>
	<table>
	<tr style=" ">
	<td width="25%"  style=" text-align:left;border:none; border-left:1px solid; border-top:1px solid;width:25%;" >
	Sales Order No </b>
	</td>
	<td width="25%"  style=" text-align:left;border-left:none;left;border:none;  border-right:1px solid;border-top:1px solid;width:25%;">
	<b>: '.$rel['sales_order_no'].' </b>
	</td>
	<td width="25%"  style=" text-align:left;border:none; border-left:1px solid; border-top:1px solid;width:25%;">
	Sales Order Date 
	</td>
	<td width="25%"  style=" text-align:left;border-left:none;left;border:none;  border-right:1px solid;border-top:1px solid;width:25%;">
	<b>: '.$so_date.'</b>
	</td>
	</tr>
	<tr style=" ">
	<td width="25%"  style=" text-align:left;border:none; border-left:1px solid; border-top:1px solid;width:25%;" >
	Delivery expected </b>
	</td>
	<td width="25%" style=" text-align:left;border-left:none;left;border:none;  border-right:1px solid;border-top:1px solid;width:25%;">
	<b> : '.$delivery_date.' </b>
	</td>
	<td width="25%"  style=" text-align:left;border:none; border-left:1px solid; border-top:1px solid;width:25%;">
	Payment Date
	</td>
	<td width="25%"  style=" text-align:left;border-left:none;left;border:none;  border-right:1px solid;border-top:1px solid;width:25%;">
	<b>: '.$rel['payment_terms'].'</b>
	</td>
	</tr>
	<tr style=" ">
	<td width="25%"  style=" text-align:left;border:none; border-left:1px solid; border-top:1px solid;width:25%;" >
	GSTIN
	</td>
	<td width="25%"  style=" text-align:left;border-left:none;left;border:none;  border-right:1px solid;border-top:1px solid;width:25%;">
	<b>: '.$rel['gst_no'].'</b>
	</td>
	<td width="25%"  style=" text-align:left;border:none; border-left:1px solid; border-top:1px solid;width:25%;">
	Contact Person<br>Mobile No
	</td>
	<td width="25%"  style=" text-align:left;border-left:none;left;border:none;  border-right:1px solid;border-top:1px solid;width:25%;">
	<b>: '.$contact_person.' <br>: '.$cust_mobile.'</b>
	</td>
	</tr>
	<tr>
	<td colspan="2" style=" text-align:center;">
	<b>Invoice Address </b> 
	</td>
	<td colspan="2" style=" text-align:center;">
	<b>Delivery Address </b>
	</td>
	</tr>
	<tr style="border:none; border-left:1px solid; border-right:1px solid;">
	<td colspan="2" style="border-left:1px solid;border-bottom:none; text-align:left;">
	'.$party_address_billing.' 
	</td>
	<td colspan="2" style="border-bottom:none;  text-align:left;">
	'.$party_address_con.'
	</td>
	</tr>
	</table>
	<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
	<thead>
	<tr>
	<td width="10%" style="text-align:center;"><b>Sr No.</b> </td>
	<td width="40%" style="text-align:center;"><b>Description of Goods </b></td>
	<td width="15%" style="text-align:center;"><b>Qty</b> </td>
	<td width="15%" style="text-align:center;"><b>Unite Rate (INR)</b> </td>
	<td width="20%" style="text-align:center;"><b>Amount (INR)</b> </td>
	</tr>
	</thead>
	<tbody>';
	$trn_qry="select *,product.product_name, hsn.hsn_code as product_hsn_code FROM `tbl_sales_ordertrn` as trn 
	left join product_mst as product on product.product_id=trn.product_id
	left join mst_hsn_code as hsn on hsn.hsn_id=product.product_hsn
	left join unit_mst as per on per.unitid=trn.unit_id 
	where trn.sales_ordertrn_status=0 and trn.sales_order_id=".$sales_order_id;

	$trn_qry_rs=$dbcon->query($trn_qry);
	$p=1;$ttl_amt=0;$ttl_qty=0;$total_cs_gst=0;$total_i_gst=0;$gst_per=0;$gst_rate=0;
	$cnt=mysqli_num_rows($trn_qry_rs);
	while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){

		$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
		$gst_rate = $trn_rel['cgst_tax_rate']+$trn_rel['sgst_tax_rate']+$trn_rel['igst_tax_rate'];

		if($trn_rel['cgst_tax_rate'] != 0 || $trn_rel['sgst_tax_rate'] !=0){
			$total_cs_gst += $gst_rate;
		}else{
			$total_i_gst += $gst_rate;
		}
		//tax summary calculation start
		if(!empty($trn_rel['tax_val']))
		{
			$tax_num=explode(",",$trn_rel['tax_val']);
			$tax_name=explode(",",$trn_rel['tax_name']);
			$total_net_rate=($trn_rel['product_qty']*$trn_rel['product_rate'])-$trn_rel['discount'];
			for($j=0;$j<count($tax_num);$j++)
			{
				if(!in_array($tax_name[$j],$tax['per']))
				{
					$tax['per'][]=$tax_name[$j];
				}
				$tax['per_total'][$tax_name[$j]]+=$total_net_rate*$tax_num[$j]/100;
			}
		}

		$product_desc = ($trn_rel['description']!='0') ? nl2br($trn_rel['description']) : '';
		$path='view/upload/product_images/';
		$html.='
		<tr>
		<td style="vertical-align: top; text-align:center;font-size:12px;">'.$p.' </td>
		<td style="border: none;text-align:left;font-size:12px;">
		<strong>'.$trn_rel['product_name'].'</strong><br>';
		if($delivery_type == 'product_wise'){
			$retu_date = "select sdate.*,unit.unit_name from tbl_salesorder_delivery_date as sdate left join unit_mst as unit on unit.unitid=sdate.unit_id where invoice_status=0 and sdate.po_delivery_date_status=0 and sales_ordertrn_id=".$trn_rel['sales_ordertrn_id'];
			$resadate=$dbcon->query($retu_date);

			$html .='<table width="40%" style="font-size:13px">
			<tr>
			<td><strong>Delivery Date</strong></td>
			<td><strong>Qty</strong></td>
			</tr>';

			while($rowdate=brp_mysqli_fetch_array($resadate)){		
				$html .='<tr>
				<td>'.date('d-m-Y',strtotime($rowdate['delivery_date'])).'</td>
				<td>'.$rowdate['product_qty'].' '.$rowdate['unit_name'].'</td>
				</tr>';		
			}
			$html .='</table>';
		}
		$html .='</td>
		<td style="vertical-align: top; text-align:center;font-size:12px;">'.without_comma_two_digit_amount($trn_rel['product_qty']).' '.$trn_rel['unit_name'].'</td>
		<td style="vertical-align: top; text-align:center;font-size:12px;"> '.without_comma_two_digit_amount($trn_rel['product_rate']).'</td>
		<td style="vertical-align: top; text-align:right;font-size:12px;"> '.without_comma_two_digit_amount($trn_rel['product_amount']).'</td>
		</tr>';

		$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
		if($trn_rel['act_amt_flag']!='1'){
		//$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
			$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
		}
		$p++;
	}
	$pr=10-$cnt;

	$html.='<tr>
	<td colspan="4" style=" text-align:right;font-size:12px;">Total Amount  </td>
	<td  style=" text-align:right;font-size:12px;">'.indian_number($ttl_amt,2).'</td>
	</tr>';
	if($rel['stateid']==$comp_rel['stateid']){
		$html.='<tr>
		<td colspan="4" style=" text-align:right;font-size:12px;">CGST '.($gst_per/2).' %</td>
		<td  style=" text-align:right;font-size:12px;">'.number_format(($total_cs_gst/2),2,".","").'</td>
		</tr><tr>
		<td colspan="4" style=" text-align:right;font-size:12px;">SGST '.($gst_per/2).' %</td>
		<td  style=" text-align:right;font-size:12px;">'.number_format(($total_cs_gst/2),2,".","").'</td>
		</tr>';
	}else{
		$html.='<tr>
		<td colspan="4" style=" text-align:right;font-size:12px;">IGST '.($gst_per).' %</td>
		<td  style=" text-align:right;font-size:12px;">'.number_format(($total_i_gst),2,".","").'</td>
		</tr>';
	}
	$qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_sales_ordertrn as trn 
	left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
	left join tbl_ledger as l on l.l_id=tc.tax_id 
	where tc.tax_additional='1' and trn.sales_order_id=".$sales_order_id." and trn.sales_ordertrn_status!=2 and tc.isdelete='0' group by tc.tax_id";
	$result11=$dbcon->query($qry11);        
	while($row11=mysqli_fetch_assoc($result11))
	{
		$html.='<tr>
		<td colspan="4" style=" text-align:right;font-size:12px;">'.$row11['l_name'].'</td>
		<td  style=" text-align:right;font-size:12px;">'.number_format($row11['add_sum'],2,".","").'</td>
		</tr>';
	}
	$qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
	from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
	left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
	where b.sundry_voucher_id=".$sales_order_id." and b.sundry_voucher_table='tbl_sales_order' and b.isdelete='0' and le.default_sundry='0'";
	$result12=$dbcon->query($qry12);        
	while($row12=mysqli_fetch_assoc($result12))
	{
		$html.='<tr>
		<td colspan="4" style=" text-align:right;font-size:12px;">'.$row12['l_name'].'</td>
		<td  style=" text-align:right;font-size:12px;">'.number_format($row12['sundry_amount'],2,".","").'</td>
		</tr>';
	}
	$round_off = round($rel['g_total'])-$rel['g_total'];
	$html .= '<tr>
	<td colspan="4" style=" text-align:right;font-size:14px;">Round Off  </td>
	<td  style=" text-align:right;font-size:14px;">'.indian_number($round_off,2).'</td>
	</tr>
	<tr>
	<td colspan="4" style=" text-align:right;font-size:14px;">Amount Payable </td>
	<td  style=" text-align:right;font-size:14px;font-weight:bold;">'.indian_number(($rel['g_total']),2).' </td>
	</tr>
	</tbody></table>
	<table style="page-break-inside: avoid;">
	<tr style="">
	<td style=" text-align:left;font-size:14px;">
	Amount payable in words: <span style="font-weight:bold;font-size:13px;">'.convert_number_to_words_new($rel['g_total']).'</span></td>
	</tr>
	<tr style="">
	<td style="border-bottom: 1px;text-align:left;font-size:14px;">
	Remarks :'.$rel['remark'].'</td>
	</tr>
	</table>';

	$html.='<table style="page-break-inside: avoid;" >
	<tr style="text-align:left;font-weight:20px;">
	<td colspan="2" ><b>Terms and Conditions</b></td>
	</tr>';

	$terms_qry="select tc_trn.tc_details,tc.tc_name from tbl_salesorder_terms_trn as tc_trn 
	left join tbl_terms_condition as tc on tc.tc_id=tc_trn.tc_id
	where tc_trn.quotation_terms_trn_status=0 and tc_trn.sales_order_id=".$sales_order_id." order by tc_trn.tc_priority";
	$terms_qry_rs=$dbcon->query($terms_qry);
	while($terms_rel=brp_mysqli_fetch_assoc($terms_qry_rs)){
		$html.='<tr>
		<td  style=" text-align:left;font-size:14px;">
		'.$terms_rel["tc_name"].'
		</td>
		<td  style=" text-align:left;font-size:14px;">
		'.$terms_rel["tc_details"].'
		</td>
		</tr>';
	}
	$html.='</table>';
	$path_sign='view/upload/product_images/';
	$html.='<table style="page-break-inside: avoid;" >';
	$html.='
	<tr>
	<td rowspan="2" style=" text-align:left;font-size:14px;height:130px">
	<span><b>Company\'s GST No : '.$comp_rel['vatno'].'</b></span>
	<br>
	<span><u><b>Payments to be deposited in Yes Bank as per following details</b></u></span>
	<br>
	<span><b>'.$comp_rel["company_name"].'</b></span>
	<br>
	<span>Bank Name : '.$comp_rel["bank_name"].'</span>
	<br>
	<span>A/c No : '.$comp_rel["ac_no"].'</span>
	<br>
	<span>IFSC Code : '.$comp_rel["ifcs"].'</span>
	<br>
	<span>Branch : '.$comp_rel["branch_name"].'</span>
	</td>
	<td style=" text-align:right;font-size:14px;font-weight:bold">
	For , '.$comp_rel["company_name"].'</td>
	</tr>
	<tr style="border-top: 0px; ">
	<td style=" text-align:right;">
	<img src="'.DOMAIN_F.'view/upload/signature/'.$comp_rel['authorized_signature'].'" height="70" width="150" class="img-thumbnail" />
	<br>
	Authorized Signature</td>
	</tr>';
	$html.='</table>';
	/* Get Terms And Condition Start */

	/*Annexure Content Print Strat*/
	/*if(!empty($rel['quot_annex_content'])){
		$html.= '<pagebreak page-break-type="clonebycss" />'.$rel['quot_annex_content'];
	}*/
	/*Annexure Content Print End*/

	$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
	// echo $header;
// 	echo $html;exit;
	$file_name = $rel['sales_order_no'].'.pdf';
	$file_name=str_ireplace("/","_",$file_name);
	ob_end_clean();
	include("../../../view/export/mpdf/mpdf.php");
	$mpdf=new mPDF('','A4','0','calibri','10','10','45','10','1','1');
//		$mdf->SetFont('ProximaNova');
	$mpdf->defaultheaderfontsize = 10; /* in pts */
	$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
	$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
	$mpdf->defaultfooterfontsize = 10; /* in pts */
	$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
	$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
	$mpdf->SetHTMLHeader($header);
		//$mpdf->SetHTMLFooter($footer);
	$mpdf->SetWatermarkText();
	$mpdf->showWatermarkText = true;
	$mpdf->allow_charset_conversion=true;
	$mpdf->charset_in='UTF-8';
	$mpdf->WriteHTML($html);
	if($save_file=="No"){
		$mpdf->Output();
	}else{
		$mpdf->Output('../../../view/upload/mail_attach/'.$file_name,'f');
	}
	ob_clean();
	return $file_name;
}
}

function auto_so_stock_allocate($dbcon,$sales_order_trn_id){
	delete_so_temp_allocate_stock($dbcon,$sales_order_trn_id);
	$qryq="select * from tbl_sales_ordertrn as cert 
		where sales_ordertrn_id=".$sales_order_trn_id;
	$result1q=$dbcon->query($qryq);
	$res1q=mysqli_fetch_assoc($result1q);
	$inv_qty=$res1q['product_qty'];
	$qry10="select * from mst_godown as cert 
		where g_status=0";
	$result10=$dbcon->query($qry10);
	while($res10=mysqli_fetch_assoc($result10)){
		if($inv_qty>0){
			$stock=total_stock_check_solid($dbcon,$res1q['product_id'],$res1q['unit_id'],$res10['gd_id']);
			if($stock>=$inv_qty){
				$uqty=$inv_qty;
			}else{
				$uqty=$stock;
			}
			$inv_qty=$inv_qty-$uqty;
			
			$type="conv_unit";
			$dqty_conv=convert_stock_new($dbcon,$uqty,$res1q['product_id'],$type);

			$info56['reserve_date']			= date("Y-m-d");
			$info56['product_id']			= $res1q['product_id'];
			$info56['base_unit']			= $res1q['unit_id'];
			$info56['base_stock']			= $uqty;
			$info56['convert_unit']			= $res1q['conv_unit_id'];
			$info56['convert_stock']		= $dqty_conv;
			$info56['stock_flage']			= 1;
			$info56['godown_id']			= $res10['gd_id'];
			$info56['ref_name']				= "sales_order_trn";
			$info56['ref_id']				= $sales_order_trn_id;
			$info56['stock_status']			= 0;
			$info56['cdate']				= date("Y-m-d H:i:s");
			$info56['user_id']				= $_SESSION['user_id'];
			$info56['company_id']			= $_SESSION['company_id'];
			$info56['branch_id']			= $res10['branch_id'];
			//$info56['perent_id']			= $res10['stock_id'];
			$info56['temp_stock_allocate']	= 1;
			$info56['sales_order_trn_id']	= $sales_order_trn_id;
			// $info56['customer_id']		= $res10['customer_id'];
			// $info56['batch_id']			= $res10['batch_id'];

			$ins_id5=add_record("tbl_reserve_stock", $info56, $dbcon);

			$info6['sales_ordertrn_id']		= $sales_order_trn_id;
			$info6['product_id']			= $info56['product_id'];
			$info6['product_qty']			= $info56['base_stock'];
			$info6['allocate_qty']			= $info56['base_stock'];
			$info6['user_id']				= $_SESSION['user_id'];
			$info6['cdate']					= date("Y-m-d H:i:s");
			$info6['company_id']			= $_SESSION['company_id'];
			$info6['branch_id']				= $res10['branch_id'];
			$ins_id5s=add_record("tbl_sales_order_production_trn", $info6, $dbcon);
		}
	}
}
function delete_so_temp_allocate_stock($dbcon,$sales_order_trn_id){
	$qry10="select * from tbl_reserve_stock as cert 
		where stock_status=0 and temp_stock_allocate=1 and sales_order_trn_id=".$sales_order_trn_id;
	$result10=$dbcon->query($qry10);
	while($res10=mysqli_fetch_assoc($result10)){
		$inv_trn1['stock_status'] = 2;
		$updatetrnid1=update_record('tbl_reserve_stock', $inv_trn1," reserve_id=".$res10['reserve_id'] , $dbcon,'');

		// $qry11="select used_base_stock,used_convert_stock from tbl_stock_trn as cert 
		// where stock_id=".$res10['stock_id'];
		// $result11=$dbcon->query($qry11);
		// $res11=mysqli_fetch_assoc($result11);

		// $inv_trn['used_base_stock'] = $res11['used_base_stock']-$res10['used_base_stock'];
		// $inv_trn['used_convert_stock'] = $res11['used_convert_stock']-$res10['used_convert_stock'];
		// $updatetrnid1=update_record('tbl_stock_trn', $inv_trn," stock_id=".$res10['stock_id'] , $dbcon,'');

	}

	$inv_trne['sales_order_production_status'] = 2;
		$updatetrnid1w=update_record('tbl_sales_order_production_trn', $inv_trne," sales_ordertrn_id=".$sales_order_trn_id , $dbcon,'');

}
function total_stock_check_solid($dbcon,$product_id,$unit_id,$gd_id){
	$qry10="select IFNULL(sum(base_stock),0) as bstock from tbl_stock_trn as cert 
		where stock_flage=1 and stock_status!=2 and product_id=".$product_id." and base_unit=".$unit_id." and godown_id=".$gd_id;
	$result10=$dbcon->query($qry10);
	$res10=mysqli_fetch_assoc($result10);
	
	$qry11="select IFNULL(sum(convert_stock),0) as bstock from tbl_stock_trn as cert 
		where stock_flage=1 and stock_status!=2 and base_unit!=convert_unit and product_id=".$product_id." and convert_unit=".$unit_id." and godown_id=".$gd_id;
	$result11=$dbcon->query($qry11);
	$res11=mysqli_fetch_assoc($result11);
	
	$qry12="select IFNULL(sum(base_stock),0) as bstock from tbl_stock_trn as cert 
		where stock_flage=2 and stock_status!=2 and product_id=".$product_id." and base_unit=".$unit_id." and godown_id=".$gd_id;
	$result12=$dbcon->query($qry12);
	$res12=mysqli_fetch_assoc($result12);
	
	$qry13="select IFNULL(sum(convert_stock),0) as bstock from tbl_stock_trn as cert 
		where stock_flage=2 and stock_status!=2 and base_unit!=convert_unit and product_id=".$product_id." and convert_unit=".$unit_id." and godown_id=".$gd_id;
	$result13=$dbcon->query($qry13);
	$res13=mysqli_fetch_assoc($result13);
	
	$cstock=($res10['bstock']+$res11['bstock'])-($res12['bstock']+$res13['bstock']);

	$qry14="select IFNULL(sum(base_stock),0) as bstock from tbl_reserve_stock as cert 
		where stock_flage=1 and stock_status!=2 and product_id=".$product_id." and base_unit=".$unit_id." and godown_id=".$gd_id;
	$result14=$dbcon->query($qry14);
	$res14=mysqli_fetch_assoc($result14);

	$qry15="select IFNULL(sum(convert_stock),0) as bstock from tbl_reserve_stock as cert 
		where stock_flage=1 and stock_status!=2 and base_unit!=convert_unit and product_id=".$product_id." and convert_unit=".$unit_id." and godown_id=".$gd_id;
	$result15=$dbcon->query($qry15);
	$res15=mysqli_fetch_assoc($result15);

	$qry16="select IFNULL(sum(base_stock),0) as bstock from tbl_reserve_stock as cert 
	where stock_flage=2 and stock_status!=2 and product_id=".$product_id." and base_unit=".$unit_id." and godown_id=".$gd_id;
$result16=$dbcon->query($qry16);
$res16=mysqli_fetch_assoc($result16);

$qry17="select IFNULL(sum(convert_stock),0) as bstock from tbl_reserve_stock as cert 
	where stock_flage=2 and stock_status!=2 and base_unit!=convert_unit and product_id=".$product_id." and convert_unit=".$unit_id." and godown_id=".$gd_id;
$result17=$dbcon->query($qry17);
$res17=mysqli_fetch_assoc($result17);
$rstock=($res14['bstock']+$res15['bstock'])-($res16['bstock']+$res17['bstock']);
$acstock=$cstock-$rstock;
return $acstock;
}
?>