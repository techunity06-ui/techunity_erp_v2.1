<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
$incPath = $path.'include/';

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	ORDER_CONFIRMATION_SLUG_READ,
	ORDER_CONFIRMATION_SLUG_PO_APPROVE,
	ORDER_CONFIRMATION_SLUG_PO_FINAL_APPROVE,
	ORDER_CONFIRMATION_SLUG_PAYMENT,
	SALES_ORDER_SLUG_PRINT,
	QUOTATION_SLUG_PRINT,
	SALES_ORDER_SLUG_CREATE,
	SALES_ORDER_SLUG_EDIT
]);

if(!in_array(ORDER_CONFIRMATION_SLUG_READ,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "fetch") {
	
	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
	$branch_id = $POST['branch_id'];
	$where='';
	if($branch_id){
		$where .= check_branch('quot',$branch_id);
	}
	$where.="  and quot.quotation_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND quot.quotation_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
	if($_SESSION['user_type']!='2' && $_SESSION['user_type']!='9')
	{
		$where.=' and inq.won_user_id='.$_SESSION['user_id'];
	}
	if($POST['fil_type']=='pen_po'){
		$where.=' and quot.po_approve_status!=3';
	}
	
	$appData = array();
	$i=1;
	$aColumns = array('quot.quotation_id', 'quot.quotation_no', 'quot.quotation_date', 'cust.cust_name', 'quot.g_total', 'quot.po_approve_status', 'quot.order_approve_status', 'quot.payment_approve_status', 'inq.inquiry_no', 'quot.quot_subject', 'usr.user_name', 'quot.quotation_status','quot.cdate','quot.company_id','quot.revise_status','quot.prev_quotation_id','quot.approve_status','quot.cust_id','quot.l_id','quot.qt_order_conf_attch','quot.qt_po_attch','quot.payment_mstid','quot.sales_order_id');
	$sIndexColumn = "quot.quotation_id";
	$isWhere = array("quot.quotation_status = 0 and revise_status=0 and approve_status=1 and stage_prob>=90 and quot.company_id in (0,$_SESSION[company_id])".$where);
	$sTable = "tbl_quotation as quot";
	$isJOIN = array('left join tbl_customer as cust on cust.cust_id=quot.cust_id', 'left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id', 'left join users as usr on usr.user_id=inq.user_id');
	$hOrder = "quot.quotation_id desc";
	include('../../../include/pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['quotation_no'];
		$quotation_no=$dbcon->real_escape_string($row['quotation_no']);
		
		$row_data[] = date('d M, Y',strtotime($row['quotation_date']));
		$row_data[] = $row['cust_name'];
		$row_data[] = $row['g_total'];
		
			//PO Status
			//Payment Status
		if(in_array(ORDER_CONFIRMATION_SLUG_PO_APPROVE,$bulkAccessArray)){
			$po_apprv_btn='<button class="btn btn-xs btn-success" data-original-title="Approve/Reject P.O." data-toggle="tooltip" data-placement="top" onClick="open_po_approv_payment('.$row['quotation_id'].',\''.$quotation_no.'\')"><i class="fa fa-exclamation-triangle"></i></button>';
		}
		
			//$po_attch_btn='<button type="button" class="btn btn-xs btn-primary" data-original-title="Add PO Details" data-toggle="tooltip" data-placement="top" onclick="attch_po_dtl('.$row['quotation_id'].',\''.$quotation_no.'\')"><i class="fa fa-plus"></i></button>';
		if(in_array(SALES_ORDER_SLUG_CREATE,$bulkAccessArray)){
			$po_attch_btn='<a class="btn btn-xs btn-primary" data-original-title="Add Sales Order" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'salesorder_quotation/'.$row['quotation_id'].'"><i class="fa fa-plus-circle"></i>add sales order</a>';
		}
		
		if(in_array(SALES_ORDER_SLUG_EDIT,$bulkAccessArray)){
			$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
			$rels=mysqli_fetch_assoc($menusql);
			$menu_show_permissions = explode(",",$rels['print_permission']);
			$sqls=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 3 AND approve_status = 1 AND status = 0 ORDER BY priority");
			while($ress = mysqli_fetch_assoc($sqls)){
				if(in_array($ress['id'],$menu_show_permissions)) {
					$sales_order_print='<a class="btn btn-xs btn-primary" data-original-title="'.$ress['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$ress['page_path'].'/'.$row['sales_order_id'].'" style="background: '.$ress['icon_color'].'; border-color: '.$ress['icon_color'].';"><i class="'.$ress['fa_icon'].'"></i></a>&nbsp;';	
				}
			}
                            // $sales_order_print='<a class="btn btn-xs btn-primary" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'sales_order_print/'.$row['sales_order_id'].'"><i class="fa fa-print"></i></a>';
		}
		if($row['po_approve_status']=='1'){
			$v_btn='';
//				if($row['qt_po_attch']){
//					$v_btn='<a href="'.ROOT.INQ_ATTACH_VWING.$row['qt_po_attch'].'" class="btn btn-xs btn-info" target="_blank"><i class="fa fa-eye"></i></a>';
//				}
			$row_data[] = '<div class="external-event label label-info ui-draggable" style="cursor:auto;">Pending Approval</div> '.$sales_order_print.' '.$v_btn.' '.$po_apprv_btn1;
		}
			else if($row['po_approve_status']=='3'){//PO Approved
				$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div> '.$po_apprv_btn1.' '.$sales_order_print;
			}
			else{
				$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Pending</div> '.$po_attch_btn;
			}
			
			//Order confirmation Status
			$order_conf_btn='';
			if($row['po_approve_status']=='3'){//PO Approved
				$order_conf_btn='<button type="button" class="btn btn-xs btn-primary" data-original-title="Add Order Confirmation Details" data-toggle="tooltip" data-placement="top" onclick="attch_order_conf_dtl('.$row['quotation_id'].',\''.$quotation_no.'\')"><i class="fa fa-plus"></i></button>';
			}
			
			if($row['order_approve_status']=='1'){
				$v_btn='';
//				if($row['qt_order_conf_attch']){
//					$v_btn='<a href="'.ROOT.INQ_ATTACH_VWING.$row['qt_order_conf_attch'].'" class="btn btn-xs btn-info" target="_blank"><i class="fa fa-eye"></i></a>';
//				}
				$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Attached</div> '.$order_conf_btn.' '.$v_btn;
			}
			else{
				$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Pending</div> '.$order_conf_btn;
			}
			
			//Payment Status
			$open_payment_btn='';
			if($row['l_id']){
				$open_payment_btn='<button type="button" class="btn btn-xs btn-primary" data-original-title="Add Payment Details" data-toggle="tooltip" data-placement="top" onclick="open_payment_dtl('.$row['quotation_id'].',\''.$quotation_no.'\')"><i class="fa fa-plus"></i></button>';
			}
			
			if($row['payment_approve_status']=='1'){
				$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div> '.$open_payment_btn;
			}
			else{
				$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Pending</div> '.$open_payment_btn;
			}
			$print_btn = '';
			if(in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
				$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
				$rels=mysqli_fetch_assoc($menusql);
				$menu_show_permissions = explode(",",$rels['print_permission']);
				$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 1 AND approve_status = 1 AND status = 0 ORDER BY priority");
				while($res = mysqli_fetch_assoc($sql)){
					if(in_array($res['id'],$menu_show_permissions)) {
						$print_btn.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['quotation_id'].'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';
					}
				}
			}
			
			
			$row_data[] = $print_btn;

			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "load_party_po_dtl") {
		$qt_qry="select qt.*,country_name,state_name,city_name from tbl_quotation as qt
		left join country_mst as country on country.countryid=qt.qt_add_country
		left join state_mst as state on state.stateid=qt.qt_add_state
		left join city_mst as city on city.cityid=qt.qt_add_city
		where qt.quotation_id=".$POST['quotation_id'];
		$qt_rel=mysqli_fetch_assoc($dbcon->query($qt_qry));
		
		//Party PO Details Table View
		$str='';
		$str.='<div class="form-group">
		<table class="display table table-bordered table-striped">
		<tr>
		<td colspan="2"><strong>Company Name:</strong> '.$qt_rel['qt_company_name'].'</td>
		<td><strong>Contact No.:</strong> '.$qt_rel['qt_com_mno'].'</td>
		</tr>
		<tr>
		<td colspan="2"><strong>Address:</strong> '.$qt_rel['qt_com_addr'].'</td>
		<td><strong>GST No.:</strong> '.$qt_rel['qt_com_gstno'].'</td>
		</tr>
		<tr>
		<td><strong>City:</strong> '.$qt_rel['city_name'].'</td>
		<td><strong>State:</strong> '.$qt_rel['state_name'].'</td>
		<td><strong>Country:</strong> '.$qt_rel['country_name'].'</td>
		</tr>
		';
		$str.='</table></div>
		<hr/>
		';
		
		$qt_rel['mod_po_comp_div_sec'] = $str;
		
		echo json_encode($qt_rel);
	}
	else if(strtolower($POST['mode']) == "attch_po_dtl") {
		$qt_qry="select qt.*,country_name,state_name,city_name from tbl_quotation as qt
		left join country_mst as country on country.countryid=qt.qt_add_country
		left join state_mst as state on state.stateid=qt.qt_add_state
		left join city_mst as city on city.cityid=qt.qt_add_city
		where qt.quotation_id=".$POST['quotation_id'];
		$qt_rel=mysqli_fetch_assoc($dbcon->query($qt_qry));
		
		if($qt_rel['qt_po_date']!="1970-01-01" && $qt_rel['qt_po_date']!="0000-00-00"){
			$qt_rel['qt_po_date'] = date('d-m-Y',strtotime($qt_rel['qt_po_date']));
		}
		else{
			$qt_rel['qt_po_date'] = '';
		}
		
		if($qt_rel['qt_delivery_date']!="1970-01-01" && $qt_rel['qt_delivery_date']!="0000-00-00"){
			$qt_rel['qt_delivery_date'] = date('d-m-Y',strtotime($qt_rel['qt_delivery_date']));
		}
		else{
			$qt_rel['qt_delivery_date'] = '';
		}
		
		if($qt_rel['qt_po_attch']){
			$qt_rel['qt_po_attch'] = INQ_ATTACH_VWING.$qt_rel['qt_po_attch'];
		}
		else{
			$qt_rel['qt_po_attch'] = '';
		}
		
		//Party PO Details Table View
		$str='';
		$str.='<div class="form-group">
		<table class="display table table-bordered table-striped">
		<tr>
		<td colspan="2">Company Name: '.$qt_rel['qt_company_name'].'</td>
		<td>Contact No.: '.$qt_rel['qt_com_mno'].'</td>
		</tr>
		<tr>
		<td colspan="2">Address: '.$qt_rel['qt_com_addr'].'</td>
		<td>GST No.: '.$qt_rel['qt_com_gstno'].'</td>
		</tr>
		<tr>
		<td>City: '.$qt_rel['city_name'].'</td>
		<td>State: '.$qt_rel['state_name'].'</td>
		<td>Country: '.$qt_rel['country_name'].'</td>
		</tr>
		';
		$str.='</table></div>';
		
		$qt_rel['mod_po_comp_div_sec'] = $str;
		
		echo json_encode($qt_rel);
	}
	else if(strtolower($POST['mode']) == "add_attch_po_dtl") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$info['qt_company_name']	= $_POST['qt_company_name'];
		$info['qt_com_mno']			= $POST['qt_com_mno'];
		$info['qt_com_gstno']		= $POST['qt_com_gstno'];
		$info['qt_com_addr']		= $POST['qt_com_addr'];
		$info['qt_add_country']		= $POST['qt_add_country'];
		$info['qt_add_state']		= $POST['qt_add_state'];
		$info['qt_add_city']		= $POST['qt_add_city'];
		
		$info['qt_po_no']			= $_POST['qt_po_no'];
		$info['qt_po_date']			= date('Y-m-d',strtotime($POST['qt_po_date']));
		$info['qt_delivery_date']	= date('Y-m-d',strtotime($POST['qt_delivery_date']));
		$info['qt_po_amount']		= $POST['qt_po_amount'];
		if($_FILES['qt_po_attch']['tmp_name']){
			$info['qt_po_attch']		= upload_attch_file($_FILES);
		}
		$info['po_approve_status']		= 1;
		$updateid=update_record('tbl_quotation', $info, "quotation_id=".$POST['quotation_id'], $dbcon, $branch_id);
		
		$arr['msg']="1";
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"add_attch_po_dtl",2,"tbl_quotation",$POST['quotation_id']);
		/*if($updateid){	
			$arr['msg']="1";
		}
		else{
			$arr['msg']=0;
		}*/
		echo json_encode($arr);
	}
	else if(strtolower($POST['mode']) == "attch_order_conf_dtl") {
		$qt_qry="select * from tbl_quotation where quotation_id=".$POST['quotation_id'];
		$qt_rel=mysqli_fetch_assoc($dbcon->query($qt_qry));
		
		if($qt_rel['qt_order_conf_attch']){
			$qt_rel['qt_order_conf_attch'] = '..//'.INQ_ATTACH_VWING.$qt_rel['qt_order_conf_attch'];
		}
		else{
			$qt_rel['qt_order_conf_attch'] = '';
		}
		
		echo json_encode($qt_rel);
	}
	else if(strtolower($POST['mode']) == "add_order_conf_dtl") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		if($_FILES['qt_order_conf_attch']['tmp_name']){
			$info['qt_order_conf_attch']		= upload_attch_file1($_FILES);
			$copy_ledger=copy_ledger_cust($dbcon,$POST['quotation_id']);
		}
		$info['order_approve_status']		= 1;
		$updateid=update_record('tbl_quotation', $info, "quotation_id=".$POST['quotation_id'], $dbcon, $branch_id);
		
		
		if($updateid){	
			$arr['msg']="1";
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"add_order_conf_dtl",2,"tbl_quotation",$POST['quotation_id']);
		}
		else{
			$arr['msg']=0;
		}
		echo json_encode($arr);
	}
	else if(strtolower($POST['mode']) == "open_payment_dtl") {
		$qt_qry="select * from tbl_quotation where quotation_id=".$POST['quotation_id'];
		$qt_rel=mysqli_fetch_assoc($dbcon->query($qt_qry));
		
		$qt_rel['due_amt']=floatval($qt_rel['g_total'])-floatval($qt_rel['paid_amount']);
		
		echo json_encode($qt_rel);
	}
	else if(strtolower($POST['mode']) == "add_pay_dtl") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		//Payment Entry 
		/*$get_qt_ledger="select l_id from tbl_quotation where quotation_id=".$POST['quotation_id'];
		$qt_ledger_rel=mysqli_fetch_assoc($dbcon->query($get_qt_ledger));
		
		$infopa['partyid']			= $qt_ledger_rel['l_id'];
		$infopa['accountid']		= 0;
		$infopa['payment_date']		= date("Y-m-d H:i:s");
		$infopa['payment_mode']		= $POST['payment_mode_id'];
		$infopa['amount']			= $POST['paid_amt'];
		$infopa['emp_id']			= $POST['employee_id'];
		$infopa['mst_status']		= 1;
		$infopa['referenceno']		= $POST['referenceno'];
		$infopa['used_amount']		= $POST['paid_amt'];
		$infopa['credits']			= '';
		$infopa['tax_deducted_flag']= '';
		$infopa['notes']			= '';
		$infopa['typeid']			= 2;//credit
		$infopa['cdate']			= date("Y-m-d H:i:s");
		$infopa['user_id']			= $_SESSION['user_id'];
		$infopa['company_id']		= $_SESSION['company_id'];
		
		$arr=get_serise_common($dbcon,'4');
		$receiptid=$arr['paymentno'];
		
		$infopa['paymentno']= $receiptid;//paymentno($dbcon,$paymentno,$invoicetype=8);
		$infoptrn['payment_mstid']=$paymentid=add_record("payment_mst",$infopa,$dbcon);
		
		$infoptrn['bill_id']		= $POST['quotation_id'];
		$infoptrn['bill_type']		= 'quotation';
		$infoptrn['paid_amount']	= $POST['paid_amt'];
		$infoptrn['total_amount']	= $POST['paid_amt'];
		$infoptrn['pay_status']		= 1;
		$infoptrn['emp_id']			= $POST['employee_id'];
		$insertidptrn=add_record("payment_trn",$infoptrn,$dbcon);*/
		
		
		$info1['payment_mode_id']	= $POST['payment_mode_id'];
		$info1['payment_date']		= date("Y-m-d H:i:s");
		$info1['referenceno']		= $_POST['referenceno'];
		$info1['paid_amt']			= $POST['paid_amt'];
		$info1['quotation_id']		= $POST['quotation_id'];
		$info1['user_id']			= $_SESSION['user_id'];
		$trninserid=add_record("tbl_quot_payment_trn", $info1, $dbcon, $branch_id);
		
		/*$info['payment_approve_status']		= 1;//Pending Approval
		$info['paid_amount']				= $POST['paid_amt'];
		$info['payment_mstid']				= $paymentid;
		$updateid=update_record('tbl_quotation', $info, "quotation_id=".$POST['quotation_id'], $dbcon);*/
		
		
		$resp['msg']="1";
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "view_payment_dtl") {
		
		$where='';
		$where.="  and trn.quotation_id=".$POST['quotation_id'];
		
		$appData = array();
		$i=1;
		$aColumns = array('trn.quot_paytrn_id', 'mode.payment_mode', 'trn.payment_date', 'trn.referenceno', 'trn.paid_amt', 'trn.approve_status', 'trn.cdate', 'trn.user_id', 'quot.quotation_no');
		$sIndexColumn = "trn.quot_paytrn_id";
		$isWhere = array("trn.quot_paytrn_status=0 ".$where." ");
		$sTable = "tbl_quot_payment_trn as trn";			
		$isJOIN = array('left join tbl_payment_mode as mode on mode.paymentmodeid=trn.payment_mode_id', 'left join tbl_quotation as quot on quot.quotation_id=trn.quotation_id');
		$hOrder = "trn.quot_paytrn_id desc";
		include('../../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['payment_mode'];
			$row_data[] = date("d-M-Y",strtotime($row['payment_date']));
			$row_data[] = $row['referenceno'];
			$row_data[] = $row['paid_amt'];
			
			if($row['approve_status']=='1'){
				$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
			}
			else if($row['approve_status']=='2'){
				$row_data[] = '<div class="external-event label label-danger ui-draggable" style="cursor:auto;">Reject</div>';
			}
			else{
				$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Pending</div>';
			}
			
			$quotation_no=$dbcon->real_escape_string($row['quotation_no']);
			$apprv_btn='<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Payment" data-toggle="tooltip" data-placement="top" onClick="open_approv_payment('.$row['quot_paytrn_id'].',\''.$quotation_no.'\')"><i class="fa fa-exclamation-triangle"></i></button>';
			
			$row_data[] = $apprv_btn;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add_apprv_hist") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$info1['assign_user_ids']	= $POST['assign_user_ids'];
		$info1['approve_remark']	= $_POST['approve_remark'];
		$info1['approve_status']	= $POST['approve_status'];
		$info1['quot_paytrn_id']	= $POST['quot_paytrn_id'];
		$info1['user_id']			= $_SESSION['user_id'];
		$info1['company_id']		= $_SESSION['company_id'];
		
		$inserid=add_record("tbl_quot_payment_aprv_log", $info1, $dbcon, $branch_id);
		
		//Hide approve btn if not allowed
		$final_btn_per=in_array(ORDER_CONFIRMATION_SLUG_PAYMENT,$bulkAccessArray);
		
		if($final_btn_per){
			if($POST['approve_status']=='1'){
				$infoso['approve_status']	= 1;//Payment Approved
				//Payment Rel
				$pay_qry="select * from tbl_quot_payment_trn where quot_paytrn_id=".$POST['quot_paytrn_id'];
				$pay_rel=mysqli_fetch_assoc($dbcon->query($pay_qry));
				$upd_qtpay_qry="update tbl_quotation set paid_amount=paid_amount+".floatval($pay_rel['paid_amt'])." where quotation_id=".$pay_rel['quotation_id'];
				$upd_qtpay_qry_rs=$dbcon->query($upd_qtpay_qry);
				
				//Match Qt Amt and update status
				$qt_amt_qry="select g_total,paid_amount from tbl_quotation where quotation_id=".$pay_rel['quotation_id'];
				$qt_amt_rel=mysqli_fetch_assoc($dbcon->query($qt_amt_qry));
				if($qt_amt_rel['paid_amount']>=$qt_amt_rel['g_total']){
					$infoqt['payment_approve_status']		= 1;//Pending Approval
					$updateid=update_record('tbl_quotation', $infoqt, "quotation_id=".$pay_rel['quotation_id'], $dbcon);
				}
			}
			else{
				$infoso['approve_status']	= 2;//Payment Pending
			}
			$updateid=update_record('tbl_quot_payment_trn', $infoso,"quot_paytrn_id=".$POST['quot_paytrn_id'] , $dbcon, $branch_id);
		}
		
	}
	else if(strtolower($POST['mode']) == "load_pay_hist_datatable") {
		
		$where='';
		$where.="  and log.quot_paytrn_id=".$POST['quot_paytrn_id'];
		
		$appData = array();
		$i=1;
		$aColumns = array('log.quot_aprv_log_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
		$sIndexColumn = "log.quot_aprv_log_id";
		$isWhere = array("log.quot_aprv_log_status=0 ".$where." ");
		$sTable = "tbl_quot_payment_aprv_log as log";			
		$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
		$hOrder = "log.quot_aprv_log_id desc";
		include('../../../include/pagging.php');
		$appData = array();
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
			
			$row_data[] = nl2br($row['approve_remark']);
			$row_data[] = date("d-M-Y h:i A",strtotime($row['cdate']));
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add_po_apprv_hist") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$info1['approve_remark']	= $_POST['approve_remark'];
		$info1['approve_status']	= $POST['approve_status'];
		$info1['quotation_id']		= $POST['quotation_id'];
		$info1['user_id']			= $_SESSION['user_id'];
		$info1['company_id']		= $_SESSION['company_id'];
		$inserid=add_record("tbl_quot_po_aprv_log", $info1, $dbcon, $branch_id);
		
		//Hide approve btn if not allowed
		$final_btn_per=check_permission("#mod_po_per_div_sec",$_SESSION['user_id'],'final_aprv',$dbcon);
		
		if($final_btn_per){
			if($POST['approve_status']=='1'){
				$infoso['po_approve_status']	= 3;//Approved
			}
			else{
				$infoso['po_approve_status']	= 0;//Payment Pending
			}
			
			$updateid=update_record('tbl_quotation', $infoso,"quotation_id=".$POST['quotation_id'] , $dbcon, $branch_id);
		}
		
	}
	else if(strtolower($POST['mode']) == "load_po_hist_datatable") {
		
		$where='';
		$where.="  and log.quotation_id=".$POST['quotation_id'];
		
		$appData = array();
		$i=1;
		$aColumns = array('log.quot_aprv_log_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
		$sIndexColumn = "log.quot_aprv_log_id";
		$isWhere = array("log.quot_aprv_log_status=0 ".$where." ");
		$sTable = "tbl_quot_po_aprv_log as log";			
		$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
		$hOrder = "log.quot_aprv_log_id desc";
		include('../../include/pagging.php');
		$appData = array();
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
			
			$row_data[] = nl2br($row['approve_remark']);
			$row_data[] = date("d-M-Y h:i A",strtotime($row['cdate']));
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	
	function upload_attch_file($FILES)
	{
		$rand=rand(0,99999999);
		if(!empty($FILES['qt_po_attch']['tmp_name'])) {
			$temp = explode(".", $FILES["qt_po_attch"]["name"]);
			$extension = strtolower(end($temp));
			$File = "qt_po_attch_".$rand.".".$extension;
			$tmp_name = $FILES["qt_po_attch"]["tmp_name"];
			move_uploaded_file($tmp_name,'..//'.INQ_ATTACH_UPING.$File);
			return  $File;				
		}
	}	
	function upload_attch_file1($FILES)
	{
		$rand=rand(0,99999999);
		if(!empty($FILES['qt_order_conf_attch']['tmp_name'])) {
			$temp = explode(".", $FILES["qt_order_conf_attch"]["name"]);
			$extension = strtolower(end($temp));
			$File = "qt_ord_attch_".$rand.".".$extension;
			$tmp_name = $FILES["qt_order_conf_attch"]["tmp_name"];
			move_uploaded_file($tmp_name,'..//'.INQ_ATTACH_UPING.$File);
			return  $File;				
		}
	}	

	?>