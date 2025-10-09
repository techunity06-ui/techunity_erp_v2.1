<?php
// require_once '../../vendor/autoload.php';
use Mpdf\Mpdf;
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");
include_once("../../../include/common_send_email.php");
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	FINANCE_PROFORMA_INVOICE_CREATE,
	FINANCE_PROFORMA_INVOICE_EDIT,
	FINANCE_PROFORMA_INVOICE_DELETE,
	FINANCE_PROFORMA_INVOICE_PRINT,
	FINANCE_PROFORMA_INVOICE_APPROVE
]);

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "fetch") {
	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];

	$where='';
	/*if($POST['report']=='all')
	{
		
	}
	if($POST['report']=='paid')
	{
		$where.=" and  g_total=paid_amount and invoice_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND invoice_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
	}
	if($POST['report']=='due')
	{
		$where.="  and g_total>paid_amount and invoice_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND invoice_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
	}*/
	$getapprovalsetting = get_userwise_approval_setting($dbcon,6,$_SESSION['user_id']);
	if(!empty($POST['type_id']))
	{
		$where .=" and invoice.invoicetype_id=".$POST['type_id'];
	}
	$where.="  and invoice_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND invoice_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
	if($_SESSION['user_type']!='2'){
		$user_id=$_SESSION['user_id'];
		$fis=check_crm_find_in_set($dbcon,$user_id,1);
		$where.=' and invoice.user_id in ('.$fis.')';
	}
	$appData = array();
	$i=1;
	$aColumns = array('invoice_id','invoice_no','cust.l_name','cas.cust_name','city.city_name','invoice_date','invoice.performa_invoice_type','invoicetype.invoice_type','g_total','paid_amount','invoice_status','invoice.cdate','invoice.user_id','invoice.usertype_id','invoice.invoicetype_id','invoice.gst_flag','ca.c_add_city','invoice.approve_status','users.user_name');
	$sIndexColumn = "invoice_id";
	$isWhere = array("invoice_status = 0 and invoice.company_id IN (0,$_SESSION[company_id])".$where);
	$sTable = "tbl_proforma_invoice as invoice";			
	$isJOIN = array('left join  tbl_ledger cust on invoice.cust_id=cust.l_id','left join  city_mst city on cust.cityid=city.cityid','left join tbl_invoicetype invoicetype on invoice.invoicetype_id=invoicetype.invoicetype_id','left join tbl_customer cas on invoice.cust_id=cas.cust_id','left join tbl_cust_address ca on ca.cust_id=invoice.cust_id','left join users as users on users.user_id=invoice.user_id');
	$hOrder = "invoice.invoice_id desc";
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		if($row['performa_invoice_type']=='1'){
			$row['invoice_type'] = 'Quotation';
		}else if($row['performa_invoice_type']=='2'){
			$row['invoice_type'] = 'Sales Order';
		}else{
			$row['invoice_type'] = 'Direct Invoice';
		}
		$row_data[] = $row['invoice_type'];
		$row_data[] = $row['invoice_no'];
		$row_data[] = date('d M, Y',strtotime($row['invoice_date']));
		if($row['performa_invoice_type']=='1'){
			$row_data[] = $row['cust_name'];
		}else{
			$row_data[] = $row['l_name'];
		}
// 		if($row['performa_invoice_type']=='1'){
// 			$citysql = $dbcon->query("select city_name from city_mst WHERE cityid = ".$row['c_add_city']);
// 			$city_address = brp_mysqli_fetch_assoc($citysql);
// 			$row_data[] = $city_address['city_name'];
// 		}else{
// 			$row_data[] = $row['city_name'];
// 		}

		$row_data[] = $row['g_total'];

		if($row['g_total']>$row['paid_amount']){
					//$cr_btn= '<a class="btn btn-xs btn-primary" data-original-title="Use Credit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'use_cr_note/'.$row['invoice_id'].'"><i class="fa fa-plus"></i></a>';
		}
		else{
			$cr_btn= '';
		}


		$addpayment='';$delete='';$edit='';$app_btn='';$print='';
		if($row["g_total"]>$row["paid_amount"]){
						//$addpayment='<a class="btn btn-xs btn-primary" data-original-title="Payable '.($row['g_total']-$row['paid_amount']).' Rs." data-toggle="tooltip" data-placement="top" href="invoicepaymentmode/'.$row['invoice_id'].'"><i class="fa fa-plus"></i></a>';

		}
		if(in_array(FINANCE_PROFORMA_INVOICE_PRINT,$bulkAccessArray)){
			$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
			$rels=mysqli_fetch_assoc($menusql);
			$menu_show_permissions = explode(",",$rels['print_permission']);
			$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 2 AND approve_status = 1 AND status = 0 ORDER BY priority");
			while($res = mysqli_fetch_assoc($sql)){
				if(in_array($res['id'],$menu_show_permissions)) {
					// $print.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['invoice_id'].'?'.time().'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';
					if($res['with_out_logo']==0){
						$print.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['invoice_id'].'?'.time().'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';
					}else{
						$ddf="'".DOMAIN_F.PRINT_ROOT.$res['page_path']."/".$row['invoice_id']."'";
						//$ddf="dfsd";
						$print .='<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Quotation" data-toggle="tooltip" data-placement="top" onClick="open_print('.$ddf.')"><i class="'.$res['fa_icon'].'"></i></button>';
					}
					
				}
			}
		}
		if(in_array(FINANCE_PROFORMA_INVOICE_DELETE,$bulkAccessArray)){
			$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_invoice('.$row['invoice_id'].')"><i class="fa fa-trash-o"></i></button>';
		}
		if(in_array(FINANCE_PROFORMA_INVOICE_EDIT,$bulkAccessArray)){
			$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'proformaedit/'.$row['invoice_id'].'"><i class="fa fa-pencil"></i></a>';
		}
		if($row['approve_status']==0){
			$row_data[] = '<a class="btn btn-xs btn-primary">Approved</a>';
			if($_SESSION['user_type'] != 2){
				$delete='';$edit='';
			}
		} else {
			$row_data[] = '<a class="btn btn-xs btn-warning">Pending</a>';
		}
		if(($getapprovalsetting['amount'] >= $row['g_total']) && ($getapprovalsetting['auto_approval']==1)){
			if(in_array(FINANCE_PROFORMA_INVOICE_APPROVE,$bulkAccessArray)){
				$app_btn = '<button class="btn btn-xs btn-success" data-original-title="Approve/Reject P.I." data-toggle="tooltip" data-placement="top" onClick="open_pi_approv_payment(\''.$row['invoice_id'].'\',\''.$row["invoice_no"].'\')"><i class="fa fa-exclamation-triangle"></i></button>';
			}
		}else{
			if(in_array(FINANCE_PROFORMA_INVOICE_APPROVE,$bulkAccessArray)){
				$app_btn = '<button class="btn btn-xs btn-success" data-original-title="Approve/Reject P.I." data-toggle="tooltip" data-placement="top" onClick="open_pi_approv_payment(\''.$row['invoice_id'].'\',\''.$row["invoice_no"].'\')"><i class="fa fa-exclamation-triangle"></i></button>';
			}
		}
		$row_data[] = $row['user_name'];
		$row_data[] = $print.' '.$edit.' '.$delete.' '.$addpayment.' '.$letterprint.' '.$cr_btn.' '.$app_btn;


		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {

	$com="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$comty=mysqli_fetch_assoc($dbcon->query($com));	
	update_common_no($dbcon,PROFORMA_SERIES);
	if($POST['quotation_id']!=''){
		$quote = $dbcon->query("select quot_address from tbl_quotation WHERE quotation_id = ".$POST['quotation_id']);
		$quot_address = brp_mysqli_fetch_assoc($quote);
		$info['quot_address']	= $quot_address['quot_address'];
	}

	$curncy_trn['currency_id'] = $POST['currency_id'];
	$curncy_trn['currency_rate'] = $POST['currency_rate'];

	$info['invoicetype_id']	= $POST['invoicetype_id'];
	$info['invoice_no']		= $POST['invoice_no'];
	$info['invoice_date']	= date('Y-m-d',strtotime($POST['invoice_date']));
	$info['challan_no']		= $POST['challan_no'];
	$info['challan_date']	= date('Y-m-d',strtotime($POST['challan_date']));
	$info['sales_order_id']	= $POST['sales_order_id'];
	$info['performa_invoice_type']	= $POST['performa_invoice_type'];
	$info['quotation_id']	= $POST['quotation_id'];
	$info['num_of_parcel']	= $POST['num_of_parcel'];
	$info['dispatch_doc_no']= text_rnremove($POST['dispatch_doc_no']);
	$info['dispatch_date']  = date('Y-m-d H:i:s',strtotime($POST['dispatch_date']));
	$info['vehicle_no']		= $POST['vehicle_no'];
	$info['gst_type']		= $POST['gst_type'];
	$info['order_no']		= $POST['order_no'];
	if(!empty($POST['order_date'])){
		$info['order_date'] 	= date('Y-m-d',strtotime($POST['order_date']));
	}
	$info['dispatch_by']	= $POST['dispatch_by'];
	$info['destination']	= $POST['destination'];
	$info['payment_terms']	= $POST['payment_terms'];

	$info['docket_no']		= $POST['docket_no'];
	$info['packing_boxes']	= $POST['packing_boxes'];
	$info['total_weight']	= $POST['total_weight'];

	$info['cust_id']		= $POST['cust_id'];
	$info['consignee_id']	= $POST['consignee_id'];
	//$info['packing']		= $POST['packing'];
	//$info['cutting']		= $POST['cutting'];
	//$info['freight']		= $POST['freight'];
    $info['formulaid']		= $POST['formula_id']; //added by : Dimple
    $info['delivery_note']		= $POST['delivery_note'];
    $info['supplier_ref']		= $POST['supplier_ref'];
    $info['delivery_note']		= $POST['delivery_note'];
    $info['supplier_ref']		= $POST['supplier_ref'];
    $info['other_reference']		= $POST['other_reference'];
    $info['dispatch_document_no']		= $POST['dispatch_document_no'];
    $info['dispatch_document_date']		= date('Y-m-d',strtotime($POST['dispatch_document_date']));
    $info['gst_type']		= $POST['gst_type'];
    $info['dispatched_through']		= $POST['dispatched_through'];
    $info['destination']			= $POST['destination'];
    $info['terms_condition']		= $_POST['terms_condition'];
    $info['terms_delivery']			= $_POST['terms_delivery'];
    $info['lr_rr_no']				= $POST['lr_rr_no'];
    $info['port_of_loading']				= $POST['port_of_loading'];
    $info['final_destination']				= $POST['final_destination'];
    $info['client_id']				= $POST['client_id'];

    $info['remark']			= text_rnremove($POST['remark']);
    $info['reverse_charge']	= $POST['reverse_charge_check'];
    $info['gst_flag']	    = '2';
    $info['quot_type']		= $POST['quot_type'];
    $info['tc_format']		= $POST['tc_format'];
    $info['terms_type']		= $POST['terms_type'];
    //maulik add start
    $info['payable_per']		= $POST['adv_per'];
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
	
    //maulik add End
	$info['transid']			= $POST['transid'];
	$info['trans_add']			= $POST['trans_add'];

    $info['cdate']			= date("Y-m-d H:i:s");
    $info['user_id']		= $_SESSION['user_id'];
    $info['company_id']		= $_SESSION['company_id'];
    if(isset($POST['save_print']))
    {
    	$info['print_status']	= $POST['print_status'];
    }
    $inserinvoiceid=add_record('tbl_proforma_invoice', array_merge($info,$curncy_trn), $dbcon);

    $qry ='INSERT INTO tbl_proforma_trn (product_id, description,product_hsn_code,product_qty,product_rate,unit_id,product_disc,product_spec,product_amount,product_discount,discount_per,start_serial1,end_serial1,start_serial2,end_serial2,start_serial3,end_serial3,formulaid,tax_name1,tax_amount1,tax_name2,tax_amount2,tax_name3,tax_amount3,total,user_id,invoice_id)
    SELECT product_id,description,product_hsn_code,product_qty, product_rate,unit_id,product_disc,product_spec,product_amount,product_discount,discount_per,start_serial1,end_serial1,start_serial2,end_serial2,start_serial3,end_serial3,formulaid,tax_name1,tax_amount1,tax_name2,tax_amount2,tax_name3,tax_amount3,total,user_id,'.$inserinvoiceid.' FROM  tbl_proforma_trntemp where temp_status=0 and user_id='.$_SESSION['user_id'];

    $dbcon->query($qry);
    $deleteid=delete_record('tbl_proforma_trntemp',"user_id=".$_SESSION['user_id'], $dbcon);		

    foreach ($POST['tc_id'] as $key => $name) {
   		$infotrm['tc_id']		= $POST['tc_id'][$key];
   		$infotrm['ref_tc_id']	= $POST['ref_tc_id'][$key];
   		$infotrm['tc_priority']	= $POST['tc_priority'][$key];
   		$infotrm['tc_details']	= $_POST['tc_details'][$key];
   		$infotrm['proforma_id']	= $inserinvoiceid;
   		$infotrm['cdate']		= date("Y-m-d H:i:s");
   		$infotrm['user_id']		= $POST['user_id'];
   		$infotrm['company_id']	= $_SESSION['company_id'];
   		if(in_array($POST['tc_id'][$key],$POST['disp_term_flag'])){
   			$insertrmid=add_record('tbl_proforma_terms_trn', $infotrm, $dbcon, $branch_id);
   		}
   	}

    /**Payment Reminder Entry START***/
    if(!empty($POST['payment_reminder']) && $POST['payment_reminder']>0)
    {
    	$remainder_date=addDayswithdate($info['invoice_date'],$POST['payment_reminder']);
    	$info_remainder['task_detail']		= 'Invoice #'.$info['invoice_no'].' Payment On '.date('d-m-Y',strtotime($remainder_date));
    	$info_remainder['date']				= $remainder_date;

    	$info_remainder['ref_id']			= $inserinvoiceid;
    	$info_remainder['ref_table']		= 'tbl_proforma_invoice';

    	$info_remainder['user_id']			= $_SESSION['user_id'];
    	$info_remainder['company_id']		= $_SESSION['company_id'];
    	$inserinvoiceid1=add_record('todo_mst', $info_remainder, $dbcon);

    }
    /**Payment Reminder Entry END***/

    /** Sales Order Entry Start ***/
    if($POST['sales_order_id']){
    	$info_sales_order['invoice_status']  = 1;
    	$info_sales_order['used_invoice_id'] = $inserinvoiceid;
    	// $updatesalesid=update_record('tbl_sales_order', $info_sales_order,"sales_order_id=".$POST['sales_order_id'], $dbcon);
    }
    $info_trn['trancation_status']  = 0;
    $info_trn['invoice_id'] = $inserinvoiceid;
    $updatesalesids=update_record('tbl_proforma_trn', $info_trn,"trancation_status=3 and user_id = ".$_SESSION['user_id'], $dbcon);

    
    foreach ($POST['bill_sundry_addon'] as $bill_sundry_addon_id => $bill_sundry_addon_amount) {

    	$info_sundry_addon['sundry_ledger_id']=$bill_sundry_addon_id;
    	$info_sundry_addon['sundry_voucher_id']=$inserinvoiceid;
    	$info_sundry_addon['sundry_voucher_type']=QUOTATION_VOUCHER;
    	$info_sundry_addon['sundry_voucher_table']='tbl_proforma_invoice';
    	$info_sundry_addon['cdate']	= date("Y-m-d H:i:s");
    	$info_sundry_addon['user_id']	= $_SESSION['user_id'];
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

    	$info_addon['sundry_gst_per'] = $addon_explode[1];
    	if($POST['currency_id']==$_SESSION['currency_id']){
    		$info_addon['sundry_gst_amount'] = $addon_explode[0];
    		$info_addon['sundry_gst_amount_conv'] = $addon_explode[0]*$POST['currency_rate'];
    	}else{
    		$info_addon['sundry_gst_amount'] = $addon_explode[0]*$POST['currency_rate'];
    		$info_addon['sundry_gst_amount_conv'] = $addon_explode[0];
    	}
    	$updateaddontaxid=update_record('tbl_bill_sundry_transaction', $info_addon,"sundry_voucher_table='tbl_proforma_invoice' and isdelete=0 and sundry_voucher_id=".$inserinvoiceid." and sundry_ledger_id=".$addon_id." " , $dbcon);
    }
    $companyConfiguration=getCompanyConfiguration($dbcon);
    $getapprovalsetting = get_userwise_approval_setting($dbcon,6,$_SESSION['user_id']);
    if($_SESSION['user_type'] == 2 || $companyConfiguration['automatic_approval_proforma']==1){
    	$infoaprvqt['approve_status']	= 0;
    	$updateid=update_record('tbl_proforma_invoice', $infoaprvqt,"invoice_id=".$inserinvoiceid , $dbcon, $branch_id);

    	$infoapp['approve_remark']	= 'Auto Approved by Admin';
    	$infoapp['approve_status']	= 1;
    	$infoapp['invoice_id']          = $inserinvoiceid;
    	$infoapp['user_id']		= $_SESSION['user_id'];
    	$infoapp['company_id']            = $_SESSION['company_id'];

    	$inserid=add_record("tbl_quot_po_aprv_log", $infoapp, $dbcon, $branch_id);

    	$querycu="select cust.cust_email,quo.user_id,quo.cust_id from tbl_proforma_invoice as quo
    	left join tbl_ledger as cust on cust.l_id=quo.cust_id
    	where quo.invoice_id=".$inserinvoiceid;
    	$resultcu=$dbcon->query($querycu);
    	$relcu=brp_mysqli_fetch_assoc($resultcu);
    	$to_email_id=$relcu['cust_email'];

    	$cur_user_id = $relcu['user_id'];
    	$cur_user = getUserDetailById($dbcon, $cur_user_id);
    	$from_email_id = ($cur_user && $cur_user['common_email_id']) ? $cur_user['common_email_id'] : ADMIN_EMAIL;

    	$queryst="select email_sms_id from email_sms_template where email_module_id = 4 and company_id=".$_SESSION['company_id'];

    	$resultst=$dbcon->query($queryst);
    	$relst=brp_mysqli_fetch_assoc($resultst);

    	$mail_template = getEmailSMSTemplateById($dbcon, $relst['email_sms_id']);
    	$module_id = 4;
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
    			$quot_file = umaboy_proformareceipt($dbcon, $POST['invoice_id'],'Yes');
    			array_push($attach,$quot_file);
    			final_send_email($from_email_id, $to_email_id, $rel11['email_cc'],$rel12['email_bcc'], $subject, $content, $attach);
    			unlink('../../../view/upload/mail_attach/'.$quot_file);
    		}else{
    			final_send_email($from_email_id, $to_email_id, $rel11['email_cc'],$rel12['email_bcc'], $subject, $content,'');
    		}
    	}
    }else{
    	if(($getapprovalsetting['amount'] >= $POST['g_total']) && ($getapprovalsetting['auto_approval']==1)){
    		$infoaprvqt['approve_status']	= 0;
    		$updateid=update_record('tbl_proforma_invoice', $infoaprvqt,"invoice_id=".$inserinvoiceid , $dbcon, $branch_id);

    		$infoapp['approve_remark']	= 'Auto Approved by Admin';
    		$infoapp['approve_status']	= 1;
    		$infoapp['invoice_id']          = $inserinvoiceid;
    		$infoapp['user_id']		= $_SESSION['user_id'];
    		$infoapp['company_id']            = $_SESSION['company_id'];

    		$inserid=add_record("tbl_quot_po_aprv_log", $infoapp, $dbcon, $branch_id);
    	}else{
    		$infoaprvqt['approve_status']	= 3;
    		$updateid=update_record('tbl_proforma_invoice', $infoaprvqt,"invoice_id=".$inserinvoiceid , $dbcon, $branch_id);
    	}
    }

    if(isset($POST['save_print']))
    {
    	$arr['printstatus']=$POST['print_status'];
    	$arr['msg']="1";
    	$arr['eid']=$inserinvoiceid;
    }
    else
    {
    	if($inserinvoiceid)
    	{	
    		$arr['msg']="1";							
    	}
    	else
    	{
    		$arr['msg']="0";
    	}
    }
    echo json_encode($arr);

}		
else if(strtolower($POST['mode']) == "edit") {
			//if($_POST['token'] == $_SESSION['token']) 
	{
		//print_r($_POST['terms_condition1']);
		$curncy_trn['currency_id'] = $POST['currency_id'];
		$curncy_trn['currency_rate'] = $POST['currency_rate'];

		$info['invoicetype_id']	= $POST['invoicetype_id'];
		$info['invoice_no']		= $POST['invoice_no'];
		$info['invoice_date']	= date('Y-m-d',strtotime($POST['invoice_date']));
		$info['challan_no']		= $POST['challan_no'];
		$info['challan_date']	= date('Y-m-d',strtotime($POST['challan_date']));
		$info['vehicle_no']		= $POST['vehicle_no'];
		$info['gst_type']		= $POST['gst_type'];
		$info['order_no']		= $POST['order_no'];
		$info['order_date']		= date('Y-m-d',strtotime($POST['order_date']));
		$info['num_of_parcel']	= $POST['num_of_parcel'];
		$info['dispatch_doc_no']= $POST['dispatch_doc_no'];
		$info['dispatch_date']  = date('Y-m-d H:i:s',strtotime($POST['dispatch_date']));
		$info['dispatch_by']	= $POST['dispatch_by'];
		$info['destination']	= $POST['destination'];
		$info['payment_terms']	= $POST['payment_terms'];
		$info['sales_order_id']	= $POST['sales_order_id'];
		$info['performa_invoice_type']	= $POST['performa_invoice_type'];
		$info['quotation_id']	= $POST['quotation_id'];

		$info['docket_no']		= $POST['docket_no'];
		$info['packing_boxes']	= $POST['packing_boxes'];
		$info['total_weight']	= $POST['total_weight'];

		$info['cust_id']		= $POST['cust_id'];
		$info['consignee_id']	= $POST['consignee_id'];
		$info['packing']		= $POST['packing'];
		$info['cutting']		= $POST['cutting'];
		$info['freight']		= $POST['freight'];
        $info['formulaid']		= $POST['formula_id']; //added by : Dimple
       /* $info['currency_id']		= $POST['currency_id'];*/  

        $info['delivery_note']		= $POST['delivery_note'];
        $info['supplier_ref']		= $POST['supplier_ref'];
        $info['other_reference']		= $POST['other_reference'];
        $info['dispatch_document_no']		= $POST['dispatch_document_no'];
        $info['dispatch_document_date']		= date('Y-m-d',strtotime($POST['dispatch_document_date']));
        $info['gst_type']		= $POST['gst_type'];
        $info['dispatched_through']		= $POST['dispatched_through'];
        $info['destination']		= $POST['destination'];
        $info['terms_condition']		= $_POST['terms_condition'];
        $info['terms_delivery']			= $_POST['terms_delivery'];
        $info['lr_rr_no']			= $POST['lr_rr_no'];
        $info['port_of_loading']			= $POST['port_of_loading'];
        $info['final_destination']			= $POST['final_destination'];
		$info['client_id']				= $POST['client_id'];
         // var_dump($info);
		/*$info['formulaid']		= $POST['formulaid'];
		$info['discount']		= $POST['discount_amt'];
		$info['discount_per']	= $POST['discount_per'];
		$info['tax1_name']		= $POST['taxname0'];
		$info['tax2_name']		= $POST['taxname1'];
		$info['tax3_name']		= $POST['taxname2'];
		$info['taxvalue1']		= $POST['taxvalue0'];
		$info['taxvalue2']		= $POST['taxvalue1'];
		$info['taxvalue3']		= $POST['taxvalue2'];
		$info['round_off']		= $POST['round_off'];*/
		$info['remark']			= text_rnremove($POST['remark']);
		$info['reverse_charge']			= $POST['reverse_charge_check'];

		//maulik add start
	    $info['payable_per']	= $POST['adv_per'];
	    $info['quot_type']		= $POST['quot_type'];
    	$info['tc_format']		= $POST['tc_format'];
		$info['terms_type']		= $POST['terms_type'];

		if($POST['currency_id']==$_SESSION['currency_id']){
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
	    //maulik add End
	    $info['transid']			= $POST['transid'];
		$info['trans_add']			= $POST['trans_add'];
		
		$info['cdate']			= date("Y-m-d H:i:s");
		$info['user_id']		= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];
		if(isset($POST['save_print']))
		{
			$info['print_status']	= $POST['print_status'];
		}
		$updateid=update_record('tbl_proforma_invoice', array_merge($info,$curncy_trn),"invoice_id=".$POST['eid'] , $dbcon);


		$deltrmid=delete_record('tbl_proforma_terms_trn',"proforma_id=".$POST['eid'], $dbcon, $branch_id);
	   	foreach ($POST['tc_id'] as $key => $name) {
	   		$infotrm['tc_id']		= $POST['tc_id'][$key];
	   		$infotrm['ref_tc_id']	= $POST['ref_tc_id'][$key];
	   		$infotrm['tc_priority']	= $POST['tc_priority'][$key];
	   		$infotrm['tc_details']	= $POST['tc_details'][$key];
	   		$infotrm['proforma_id']	= $POST['eid'];
	   		$infotrm['cdate']		= date("Y-m-d H:i:s");
	   		if(in_array($POST['tc_id'][$key],$POST['disp_term_flag'])){
	   			$insertrmid=add_record('tbl_proforma_terms_trn', $infotrm , $dbcon, $branch_id);
	   		}
	   	}
		if(isset($POST['save_print']))
		{
			$arr['printstatus']=$POST['print_status'];
			$arr['msg']="update";
			$arr['eid']=$POST['eid'];
		}
		else
		{
			if($updateid)
			{	
				$arr['msg']="update";

			}
			else
				$arr['msg']=0;
		}
		echo json_encode($arr);	

	}
}
else if(strtolower($POST['mode']) == "delete") {

	$info['invoice_status']	= 2;
	$info1['trancation_status']	= 2;
	$informdr['status'] = 2;
	$info_sales_order['invoice_status']  = 0;
	$updatesalesid=update_record('tbl_sales_order', $info_sales_order,"used_invoice_id=".$POST['eid'], $dbcon);
	$updateinvoiceid=update_record('tbl_proforma_invoice', $info,"invoice_id=".$POST['eid'] , $dbcon);	
	$updatetrancationid=update_record('tbl_proforma_trn', $info1,"invoice_id=".$POST['eid'] , $dbcon);	
			//Update Payment Reminder
	$updatermdrid=update_record('todo_mst', $informdr,"ref_id=".$POST['eid']." and ref_table='tbl_proforma_invoice'" , $dbcon);
			//Update Serial Number
			//$deleteid=delete_record('tbl_serialtrn',"invoice_id=".$POST['eid'], $dbcon);

	if($updatetrancationid)
		echo "1";	
	else
		echo "0";			
}
else if(strtolower($POST['mode']) == "fieldadd") {

	$company_state = get_company_data($dbcon,$_SESSION['company_id']);
	//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
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
   	}else if($POST['gst_type']==8){
   		$sale_gst['tax_gst']=24;
   		$sale_gst['tax_cat_id']=0;
   	}else{
		$sale_gst = get_tax_cat_by_hsn($dbcon,$POST['product_hsn']);
	}

	$cgst_tax_rate=0;$cgst_tax_rate_conv=0;
	$sgst_tax_rate=0;$sgst_tax_rate_conv=0;
	$igst_tax_rate=0;$igst_tax_rate_conv=0;
	if(($company_state['stateid'] == $POST['cust_stateid'])){
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

	$info1['product_id']		= $POST['product_id'];
	$info1['item_size']		= $POST['item_size'];
	$info1['cat_id']			= $POST['cat_id'];
	$info1['description']		= stripcslashes(str_replace(array("\n", "\r", "\N", "\R"), '', $POST['product_disc']));//stripcslashes(text_rnremove($_POST['product_des']));
	$info1['product_hsn_code']	= $POST['product_hsn_code'];
	
	$info1['product_qty']		= $POST['product_qty'];
	$info1['product_conv_qty']	= $POST['product_conv_qty'];

	$info1['product_disc']		= stripcslashes(str_replace(array("\n", "\r", "\N"), '', $POST['product_disc']));
	$info1['product_spec']		= stripcslashes(str_replace(array("\n", "\r", "\N"), '', $POST['product_spec']));

	$info1['unit_id']			= $POST['unit_id'];
	$info1['conv_unit_id']		= $POST['conv_unitid'];
	$info1['rate_unit']			= $POST['rate_unitid'];
	$info1['packing_id']		= $POST['packing_id'];
	$info1['packing_size']		= $POST['packing_size'];
	$info1['total_qty']			= $POST['total_qty'];
	$info1['rate_unit']			= $POST['rate_unitid'];

	$info1['discount_per']		= $POST['discount_per'];
	$info1['formulaid']			= $POST['formulaid'];
	$info1['currency_id']		= $POST['currency_id'];
	$info1['currency_rate']		= $POST['currency_rate']; 
	
	//$info=get_product_tax($dbcon,$total,$POST['formulaid']);
	//$info1=array_merge($info1,$info);
	$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
	$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
	$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0 ;
	$info1['product_tax_cat']	= $sale_gst['tax_cat_id'];
	if($POST['currency_id']==$company_state['currency_id']){
		$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
		$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
		$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
		$info1['product_rate']		= $POST['product_rate'];
		$info1['product_discount']	= $POST['product_discount'];
		$info1['product_amount']	= $POST['product_amount'];
		$info1['total']				= $POST['product_amount'] + $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;

		$info1['cgst_tax_rate_conv']= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
		$info1['sgst_tax_rate_conv']= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
		$info1['igst_tax_rate_conv']= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;
		$info1['product_rate_conv']	= $POST['product_rate'] * $POST['currency_rate'];
		$info1['product_discount_conv']	= $POST['product_discount'] * $POST['currency_rate'];
		$info1['product_amount_conv']	= $POST['product_amount'] * $POST['currency_rate'];
		$info1['total_conv']		= $info1['product_amount_conv'] + $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv;
	}else{
		$info1['cgst_tax_rate']		= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
		$info1['sgst_tax_rate']		= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
		$info1['igst_tax_rate']		= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;
		$info1['product_rate']		= $POST['product_rate'] * $POST['currency_rate'];
		$info1['product_discount']	= $POST['product_discount'] * $POST['currency_rate'];
		$info1['product_amount']	= $POST['product_amount'] * $POST['currency_rate'];
		$info1['total']				= $POST['product_amount'] + $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv;

		$info1['cgst_tax_rate_conv']= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
		$info1['sgst_tax_rate_conv']= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
		$info1['igst_tax_rate_conv']= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
		$info1['product_rate_conv']	= $POST['product_rate'];
		$info1['product_discount_conv']	= $POST['product_discount'];
		$info1['product_amount_conv']	= $POST['product_amount'];
		$info1['total_conv']		= $POST['product_amount'] + $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
	}
	//$table='tbl_proforma_trntemp';$tableid='tempinvoicetrn_id';
// var_dump($sale_gst);die();
	$table='tbl_proforma_trn';
	$tableid='trancation_id';
	if(!empty($POST['invoice_id']))
	{
		$info1['invoice_id']= $POST['invoice_id'];
	}
	else
	{
		$info1['trancation_status']	= 3;
	}
	$info1['user_id']	= $_SESSION['user_id'];
	if(empty($POST['edit_id']))
	{
		$inserid=add_record($table, $info1, $dbcon);

		////////Maulik Code Start --- 06-12-2022--////////////////////////
   		$inq_qry="select tiat.*, pm.product_base_unit, pm.product_conv_unit, pm.product_spec, pm.product_spec_id, pm.product_hsn, hsn.hsn_code from tbl_proforma_access_trn as tiat 
   			left join product_mst as pm on pm.product_id=tiat.product_id 
   			left join mst_hsn_code as hsn on hsn.hsn_id = pm.product_hsn
   			where tiat.inq_access_status=3 and tiat.pid=".$POST['product_id']." and tiat.company_id=".$_SESSION['company_id']." and tiat.user_id=".$_SESSION['user_id']."";
		$inq_qry_rs=$dbcon->query($inq_qry);

		while($inq_rel=brp_mysqli_fetch_array($inq_qry_rs)){
			//$company_state = get_company_data($dbcon,$_SESSION['company_id']);
			//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
			if($POST['gst_type']==3){
		   		$sale_gst1['tax_gst']=0.1;
		   		$sale_gst1['tax_cat_id']=0;
		   	}else if($POST['gst_type']==4){
		   		$sale_gst1['tax_gst']=0;
		   		$sale_gst1['tax_cat_id']=0;
		   	}else if($POST['gst_type']==5){
		   		$sale_gst1['tax_gst']=5;
		   		$sale_gst1['tax_cat_id']=0;
		   	}else if($POST['gst_type']==6){
		   		$sale_gst1['tax_gst']=12;
		   		$sale_gst1['tax_cat_id']=0;
		   	}else if($POST['gst_type']==7){
		   		$sale_gst1['tax_gst']=18;
		   		$sale_gst1['tax_cat_id']=0;
		   	}else if($POST['gst_type']==8){
		   		$sale_gst1['tax_gst']=24;
		   		$sale_gst1['tax_cat_id']=0;
		   	}else{
				$sale_gst1 = get_tax_cat_by_hsn($dbcon,trim($inq_rel['hsn_code']));
			}

			$cgst_tax_rate1=0;$cgst_tax_rate_conv1=0;
			$sgst_tax_rate1=0;$sgst_tax_rate_conv1=0;
			$igst_tax_rate1=0;$igst_tax_rate_conv1=0;
			if(($company_state['stateid'] == $POST['cust_stateid'])){
				$gst = $sale_gst1['tax_gst']/2;
				$cgst_tax_per1 = $gst;
				$cgst_tax_rate1 = ($gst*$inq_rel['acc_amount'])/100;
				$cgst_tax_rate_conv1 = ($POST['currency_rate'] *$gst*$inq_rel['acc_amount'])/100;
				$sgst_tax_per1 = $gst;
				$sgst_tax_rate1 = ($gst*$inq_rel['acc_amount'])/100;
				$sgst_tax_rate_conv1 = ($POST['currency_rate'] *$gst*$inq_rel['acc_amount'])/100;
			}else{
				$igst_tax_per1 = $sale_gst1['tax_gst'];
				$igst_tax_rate1 = ($sale_gst1['tax_gst']*$inq_rel['acc_amount'])/100;
				$igst_tax_rate_conv1 = ($POST['currency_rate'] *$sale_gst1['tax_gst']*$inq_rel['acc_amount'])/100;
			}

			$info12['product_id']		= $inq_rel['product_id'];
			$info12['description']		= stripcslashes(str_replace(array("\n", "\r", "\N", "\R"), '', $inq_rel['product_disc']));//stripcslashes(text_rnremove($_POST['product_des']));
			$info12['product_hsn_code']	= $inq_rel['hsn_code'];
			$info12['product_qty']		= $inq_rel['qty'];
			$info12['product_disc']		= stripcslashes(str_replace(array("\n", "\r", "\N"), '', $inq_rel['product_disc']));
			//$info12['product_spec']		= stripcslashes(str_replace(array("\n", "\r", "\N"), '', $POST['product_spec']));
			$info12['unit_id']			= $inq_rel['product_base_unit'];
			//$info12['discount_per']		= $POST['discount_per'];
			//$info12['formulaid']			= $POST['formulaid'];
			$info12['currency_id']		= $POST['currency_id'];
			$info12['currency_rate']		= $POST['currency_rate']; 
			$info12['pid']				= $inserid;
			//$info=get_product_tax($dbcon,$total,$POST['formulaid']);
			//$info12=array_merge($info12,$info);
			$info12['cgst_tax_per']		= isset($cgst_tax_per1) ? $cgst_tax_per1 : 0 ;
			$info12['sgst_tax_per']		= isset($sgst_tax_per1) ? $sgst_tax_per1 : 0 ;
			$info12['igst_tax_per']		= isset($igst_tax_per1) ? $igst_tax_per1 : 0 ;
			$info12['product_tax_cat']	= $sale_gst1['tax_cat_id'];
			if($POST['currency_id']==$company_state['currency_id']){
				$info12['cgst_tax_rate']		= isset($cgst_tax_rate1) ? $cgst_tax_rate : 0 ;
				$info12['sgst_tax_rate']		= isset($sgst_tax_rate1) ? $sgst_tax_rate1 : 0 ;
				$info12['igst_tax_rate']		= isset($igst_tax_rate1) ? $igst_tax_rate1 : 0 ;
				$info12['product_rate']		= $inq_rel['acce_rate'];
				//$info12['product_discount']	= $POST['product_discount'];
				$info12['product_amount']	= $total1=($inq_rel['acce_rate']*$inq_rel['qty']);
				$info12['total']				= $total1 + $cgst_tax_rate1 + $sgst_tax_rate1 + $igst_tax_rate1;

				$info12['cgst_tax_rate_conv']= isset($cgst_tax_rate_conv1) ? $cgst_tax_rate_conv1 : 0;
				$info12['sgst_tax_rate_conv']= isset($sgst_tax_rate_conv1) ? $sgst_tax_rate_conv1 : 0;
				$info12['igst_tax_rate_conv']= isset($igst_tax_rate_conv1) ? $igst_tax_rate_conv1 : 0;
				$info12['product_rate_conv']	= $inq_rel['acce_rate'] * $POST['currency_rate'];
				//$info12['product_discount_conv']	= $POST['product_discount'] * $POST['currency_rate'];
				$info12['product_amount_conv']	= $total1 * $POST['currency_rate'];
				$info12['total_conv']		= $info12['product_amount_conv'] + $cgst_tax_rate_conv1 + $sgst_tax_rate_conv1 + $igst_tax_rate_conv1;
			}else{
				$info12['cgst_tax_rate']		= isset($cgst_tax_rate_conv1) ? $cgst_tax_rate_conv1 : 0;
				$info12['sgst_tax_rate']		= isset($sgst_tax_rate_conv1) ? $sgst_tax_rate_conv1 : 0;
				$info12['igst_tax_rate']		= isset($igst_tax_rate_conv1) ? $igst_tax_rate_conv1 : 0;
				$info12['product_rate']		= $inq_rel['acce_rate'] * $POST['currency_rate'];
				//$info12['product_discount']	= $POST['product_discount'] * $POST['currency_rate'];
				$info12['product_amount']	= $total_c1=($inq_rel['acce_rate']*$inq_rel['qty'] * $POST['currency_rate']);
				$info12['total']				= $total_c1 + $cgst_tax_rate_conv1 + $sgst_tax_rate_conv1 + $igst_tax_rate_conv1;

				$info12['cgst_tax_rate_conv']= isset($cgst_tax_rate1) ? $cgst_tax_rate1 : 0 ;
				$info12['sgst_tax_rate_conv']= isset($sgst_tax_rate1) ? $sgst_tax_rate1 : 0 ;
				$info12['igst_tax_rate_conv']= isset($igst_tax_rate1) ? $igst_tax_rate1 : 0 ;
				$info12['product_rate_conv']	= $inq_rel['acce_rate'];
				//$info12['product_discount_conv']	= $POST['product_discount'];
				$info12['product_amount_conv']	= $total1=($inq_rel['acce_rate']*$inq_rel['qty']);
				$info12['total_conv']		= $total1 + $cgst_tax_rate1 + $sgst_tax_per1 + $igst_tax_per1;
			}
			//$table='tbl_proforma_trntemp';$tableid='tempinvoicetrn_id';
		// var_dump($sale_gst1);die();
			$table='tbl_proforma_trn';
			$tableid='trancation_id';
			if(!empty($POST['invoice_id']))
			{
				$info12['invoice_id']= $POST['invoice_id'];
			}
			else
			{
				$info12['trancation_status']	= 3;
			}
			$info12['user_id']	= $_SESSION['user_id'];
			$inserid1=add_record($table, $info12, $dbcon);

			if(($cgst_tax_per1 != 0) && ($cgst_tax_rate1 != 0) ){
				$cl_id = get_ledger_by_name($dbcon,'CGST');
				$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per1,$cgst_tax_rate1,$inserid1,"tbl_proforma_trn",$inq_rel['product_id'],3,$POST['edit_id'],$_SESSION['branch_id'],$POST['currency_id'],$POST['currency_rate'],$cgst_tax_rate_conv1);
			}
			if(($sgst_tax_per1 != 0) && ($sgst_tax_rate1 != 0) ){
				$cl_id = get_ledger_by_name($dbcon,'SGST');
				$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per1,$sgst_tax_rate1,$inserid1,"tbl_proforma_trn",$inq_rel['product_id'],3,$POST['edit_id'],$_SESSION['branch_id'],$POST['currency_id'],$POST['currency_rate'],$sgst_tax_rate_conv	);
			}
			if(($igst_tax_per1 != 0) && ($igst_tax_rate1 != 0) ){
				$cl_id = get_ledger_by_name($dbcon,'IGST');
				$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per1,$igst_tax_rate1,$inserid1,"tbl_proforma_trn",$inq_rel['product_id'],3,$POST['edit_id'],$_SESSION['branch_id'],$POST['currency_id'],$POST['currency_rate'],$igst_tax_rate_conv1);
			}

					// check for the addiotional tax on product Start -- dhaval
			$pro_amt = $inq_rel['acc_amount']*$POST['currency_rate'];
			$count_add_tax=get_check_addition_tax($dbcon,$sale_gst1['tax_cat_id'],$inq_rel['acc_amount'],$inserid1,$inq_rel['product_id'],$POST['edit_id'],$_SESSION['branch_id'],'tbl_proforma_trn',$POST['currency_id'],$POST['currency_rate'],$pro_amt);

			$deleteid=delete_record('tbl_proforma_access_trn', "pid=".$POST['product_id']. " and inq_access_status = 3 and user_id='".$_SESSION['user_id']."' and company_id='".$_SESSION['company_id']."'", $dbcon);
		}
	}
	else
	{
		$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
		$inserid = $POST['edit_id'];
	}
	if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
		$cl_id = get_ledger_by_name($dbcon,'CGST');
		$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid,"tbl_proforma_trn",$POST['product_id'],3,$POST['edit_id'],$_SESSION['branch_id'],$POST['currency_id'],$POST['currency_rate'],$cgst_tax_rate_conv);
	}
	if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
		$cl_id = get_ledger_by_name($dbcon,'SGST');
		$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid,"tbl_proforma_trn",$POST['product_id'],3,$POST['edit_id'],$_SESSION['branch_id'],$POST['currency_id'],$POST['currency_rate'],$sgst_tax_rate_conv);
	}
	if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
		$cl_id = get_ledger_by_name($dbcon,'IGST');
		$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid,"tbl_proforma_trn",$POST['product_id'],3,$POST['edit_id'],$_SESSION['branch_id'],$POST['currency_id'],$POST['currency_rate'],$igst_tax_rate_conv);
	}

			// check for the addiotional tax on product Start -- dhaval
	$pro_amt = $POST['product_amount']*$POST['currency_rate'];
	$count_add_tax=get_check_addition_tax($dbcon,$sale_gst['tax_cat_id'],$POST['product_amount'],$inserid,$POST['product_id'],$POST['edit_id'],$_SESSION['branch_id'],'tbl_proforma_trn',$POST['currency_id'],$POST['currency_rate'],$pro_amt);
}
else if(strtolower($POST['mode']) == "formulavalue") 
{
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
		<label class="col-md-5 control-label">'.$tax['tax_name'].'</label>
		<div class="col-md-5 col-xs-12">
		<input id="taxvalue'.$j.'" name="taxvalue'.$j.'" value= "'.$rate.'"type="text" class="form-control" readonly="readonly">
		</div>
		</div>
		<input id="taxname'.$j.'" name="taxname'.$j.'" value= "'.$tax['tax_name'].'" type="hidden" class="form-control">';
		$rate_total=$rate_total+$rate;
		$j++;
	}
	$g_total=$rate_total+$c_total;
	echo '<input id="rate" name="rate" value= "'.$g_total.'" type="hidden" class="form-control" >';
}
else if(strtolower($POST['mode'])== "load_productdata")
{
			//$qry="select * from tbl_product where product_id=".$POST['eid'];
			//$qry="select popro.*,com.stateid as com_stateid,cust.stateid as cust_stateid from `tbl_product` as popro left join `tbl_company` as com on com.company_id=".$_SESSION['company_id']." left join tbl_customer as cust on cust.cust_id=".$POST['cust_id']." where product_id=".$POST['eid'];
	$qry="select popro.*,com.stateid as com_stateid,cust.stateid as cust_stateid, hsn.hsn_code as product_hsn_code from `product_mst` as popro 
	left join `tbl_company` as com on com.company_id=".$_SESSION['company_id']." 
	left join mst_hsn_code as hsn on hsn.hsn_id = popro.product_hsn
	left join tbl_ledger as cust on cust.l_id=".$POST['cust_id']." where product_id=".$POST['eid'];
	$result=$dbcon->query($qry);
	$row=mysqli_fetch_assoc($result);

	if($row['com_stateid']==$row['cust_stateid']){
		$qry2="select * from formula_mst as led 
		where formula_status=0 and tax_cat='INTRA' and tax_per_id=".$row['product_sale_gst'];
		$result2=$dbcon->query($qry2);
		$row2=mysqli_fetch_assoc($result2);
		$row['fom_id']=$row2['formulaid'];
	}else{
		$qry2="select * from formula_mst as led 
		where formula_status=0 and tax_cat='INTER' and tax_per_id=".$row['product_sale_gst'];
		$result2=$dbcon->query($qry2);
		$row2=mysqli_fetch_assoc($result2);
		$row['fom_id']=$row2['formulaid'];
	}
	$row['product_desc']=stripcslashes(str_replace(array("\n", "\r", "\N"), '', $row['product_desc']));
	$row['product_spec']=stripcslashes(str_replace(array("\n", "\r", "\N"), '', $row['product_spec']));
	$row['product_sale_rate']=get_product_rate_sales_time($dbcon, $row['product_id'], $row['product_base_unit']);		
	echo json_encode( $row );

}	
else if(strtolower($POST['mode'])== "load_podata")
{
	getpono($dbcon,$POST['cust_id']);
}
else if(strtolower($POST['mode'])== "load_podate")
{
	$qry2="select * from tbl_pono where po_id=".$POST['po_id'];
	$result2=mysqli_fetch_assoc($dbcon->query($qry2));
	echo json_encode($result2);	
}
else if(strtolower($POST['mode'])== "reminder")
{
	$qry2="select * from pay_terms where terms_id=".$POST['paymentterms'];
	$result2=mysqli_fetch_assoc($dbcon->query($qry2));
	echo json_encode($result2);	
}
else if(strtolower($POST['mode'])== "get_series_no")
{
	$query="select * from tbl_invoicetype where status=0 and type_id=19 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
	$result=$dbcon->query($query);
	$row=mysqli_fetch_assoc($result);
	echo $row['invoicetype_id'];

}
else if(strtolower($POST['mode'])== "load_invoiceno")
{
	$row=array();
	$invoice_no= load_common_no($dbcon,PROFORMA_SERIES);
	$row['invoiceno']=$invoice_no;
	$row['challanno']=$invoice_no;
	echo json_encode($row);
}
else if(strtolower($POST['mode']) == "load_tempoutward") {
	$performa_invoice_type = $POST['performa_invoice_type'];
	$sales_order_id = $POST['sales_order_id'];
	$quotation_id = $POST['quotation_id'];
	$companyConfiguration=getCompanyConfiguration($dbcon);
	if($performa_invoice_type=='3'){
		if(empty($POST['eid'])){
			$query="SELECT mst.*,hsn.hsn_code,product.product_name,cat.unit_name as base_unit, ccat.unit_name as conv_unit, rcat.unit_name as rat_unit,product.product_name,mst.description, product_qty, product_rate,product_disc, product_amount, categ.cat_name, pack.packing_name from  tbl_proforma_trn as mst 
			LEFT JOIN packing_mst as pack ON pack.packing_id = mst.packing_id
			left join unit_mst as cat on cat.unitid=mst.unit_id 
			left join unit_mst as ccat on ccat.unitid=mst.conv_unit_id 
			left join unit_mst as rcat on rcat.unitid=mst.rate_unit
			left join product_mst as product on product.product_id=mst.product_id 
			left join tbl_category as categ on categ.cat_id = mst.cat_id
			left join mst_hsn_code as hsn on hsn.hsn_id = product.product_hsn 

			where trancation_status=3 and mst.user_id=".$_SESSION['user_id']." order by trancation_id Desc";
		}else{
			$query="SELECT mst.*,hsn.hsn_code,product.product_name,cat.unit_name as base_unit, ccat.unit_name as conv_unit, rcat.unit_name as rat_unit,product.product_name,mst.description,product_qty,product_rate,product_disc,product_amount, categ.cat_name, pack.packing_name from  tbl_proforma_trn as mst 
			LEFT JOIN packing_mst as pack ON pack.packing_id = mst.packing_id
			left join unit_mst as cat on cat.unitid=mst.unit_id 
			left join unit_mst as ccat on ccat.unitid=mst.conv_unit_id 
			left join unit_mst as rcat on rcat.unitid=mst.rate_unit
			left join product_mst as product on product.product_id=mst.product_id 
			left join tbl_category as categ on categ.cat_id = mst.cat_id
			left join mst_hsn_code as hsn on hsn.hsn_id = product.product_hsn 

			where trancation_status=0 and mst.invoice_id=".$POST['eid']." order by trancation_id Desc";
		}
	}else{
		$where = '';
		
		if($performa_invoice_type=='1'){
			if(!empty($POST['eid'])){
				$where .= "mst.trancation_status=0 and mst.invoice_id=".$POST['eid'];
			}else{
				$where .= 'mst.trancation_status=3 and mst.user_id = "'.$_SESSION['user_id'] .'" ';	
			}
			
		}
		if($performa_invoice_type=='2'){
			if(!empty($POST['eid'])){
				$where .= "mst.trancation_status=0 and mst.invoice_id=".$POST['eid'];
			}else{
				$where .= 'mst.trancation_status=3 and mst.user_id = "'.$_SESSION['user_id'] .'" ';	
			}
		}
		$query="SELECT trancation_id,hsn.hsn_code,product.product_name,cat.unit_name as base_unit, ccat.unit_name as conv_unit, rcat.unit_name as rat_unit,product.product_name,mst.description,product_qty,product_rate,product_disc,mst.*,product_amount, categ.cat_name, pack.packing_name from  tbl_proforma_trn as mst 
		LEFT JOIN packing_mst as pack ON pack.packing_id = mst.packing_id
		left join unit_mst as cat on cat.unitid=mst.unit_id 
		left join unit_mst as ccat on ccat.unitid=mst.conv_unit_id 
		left join unit_mst as rcat on rcat.unitid=mst.rate_unit 
		left join product_mst as product on product.product_id=mst.product_id 
		left join tbl_category as categ on categ.cat_id = mst.cat_id
		left join mst_hsn_code as hsn on hsn.hsn_id = product.product_hsn 

		where  $where order by trancation_id Desc";
	}	
// var_dump($query);
	$result=$dbcon->query($query);
	echo ' <div class="form-group">
	<div class="col-md-12 col-xs-12">
	<table cellspacing="10" class="display table table-striped table-bordered table12">
	<tr id="field">';
	if($companyConfiguration['category_selection_active']==1){
		echo '<th class="text-center" width="8%">Category</th>';
	}
	echo '<th class="text-center" width="25%">Product Name</th>
	<th class="text-center"width="8%">Item Size</th>
	<th class="text-center"width="8%">HSN Code</th>
	<th width="15%" class="text-center ">Packing Name</th>
    <th width="10%" class="text-center">Size</th>
    <th class="text-center"width="8%">Qty</th>
    <th width="10%" class="text-center">Total Qty</th>
	<!--<th class="text-center"width="8%">Sqr/Ft</th>-->
	<!--<th class="text-center"width="8%">Serial No.</th>-->
	<th class="text-center" width="8%">Rate <span class="currency_icon"></span></th>
	<th class="text-center" width="8%">Discount <span class="currency_icon"></span></th>
	<th class="text-center" width="15%">Tax <span class="currency_icon"></span></th>
	<th class="text-center" width="12%">Amount <span class="currency_icon"></span></th>
	<th class="text-center" width="10%">Action</th>
	</tr>';
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

			if($rel['unit_id']===$rel['rate_unit']){
				$sqty=$rel['product_qty'];
			}else{
				$sqty=$rel['product_conv_qty'];
			}

			if($rel['unit_id'] != $rel['conv_unit_id']){
				$qty_lb = '<strong style="color:green;">Base Qty</strong> :'.number_format($rel['product_qty'],4,'.','').' '.$rel['base_unit'].'<br><strong style="color:green;">Conv. Qty</strong> :'.number_format($rel['product_conv_qty'],4,'.','').' '.$rel['conv_unit']; 
			}else{
				$qty_lb = '<strong style="color:green;">Base Qty</strong> :'.number_format($rel['product_qty'],4,'.','').' '.$rel['base_unit'];
			}

			echo '<tr id="fieldtr'.$id.'" >';
			if($companyConfiguration['category_selection_active']==1){
				echo '<td class="text-center" >'.$rel['cat_name'].'</td>';
			}
			echo '<td data-label="PRODUCT NAME" style="vertical-align:top;text-align:left">
			'.$rel['product_name'].'
			'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.stripcslashes(str_replace(array("\n", "\r", "\N"), '', $rel['description'])):'').'
			</td>
			<td data-label="HSN CODE" style="vertical-align:top;" class="text-center">
			'.$rel['item_size'].'
			</td>
			<td data-label="HSN CODE" style="vertical-align:top;" class="text-center">';
			if(empty($rel['hsn_code'])){
				echo '-';
			}else{
				echo $rel['hsn_code'];
			}
			echo'</td>
			<td>'.$rel['packing_name'].'</td>
			<td>'.$rel['packing_size'].'</td>
			<td data-label="QTY" style="vertical-align:top;" class="text-center">
			<strong style="color:green">Rate Qty</strong> :'.number_format($sqty,4,'.','').' '.$rel['rat_unit'].'<br>'.$qty_lb.'
			</td>
			<td>'.number_format($rel['total_qty'],4,'.','').' '.$rel['base_unit'].'</td>
			<!--<td style="vertical-align:top;" class="text-center">
			'.$rel['sqr_ft'].'
			</td>-->';

			echo '<td data-label="RATE" style="vertical-align:top;" class="text-right">
			'.$rate_label.'
			</td>				
			
			<td data-label="DISCOUNT" style="vertical-align:top" class="text-right">
			'.$product_discount_label.' ('.$rel['discount_per'].'%)
			</td>
			<td data-label="TAX" style="vertical-align:top" class="text-left">'.$cgst_tax.''.$sgst_tax.''.$igst_tax.'</td>
			<td data-label="AMOUNT" style="vertical-align:top" class="text-right">
			'.$product_amount_label.'
			</td>
			<input type="hidden" name="amount[]" id="amount'.$i.'" value="'.(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['product_amount'] : $rel['product_amount_conv']).'"/>

			<td data-label="ACTION" style="vertical-align:top">
			<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['trancation_id'].',\' tbl_proforma_trn\',\'trancation_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>
			<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['trancation_id'].',\' tbl_proforma_trn\',\'trancation_id\');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
			</td>	
			</tr>';
			$i++;
		}
	}
	else{
		echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
	}
	echo '

	</table>			 
	</div>

	</div>	';
}
else if(strtolower($POST['mode']) == "load_tempoutward_durva") {
	$performa_invoice_type = $POST['performa_invoice_type'];
	$sales_order_id = $POST['sales_order_id'];
	$quotation_id = $POST['quotation_id'];
	$getspecialConfiguration=getspecialConfiguration($dbcon);

	if($performa_invoice_type=='3'){
		if(empty($POST['eid'])){
			$query="SELECT mst.*,hsn.hsn_code,product.product_name,cat.unit_name,product.product_name,mst.description,product_qty,product_rate,product_disc,product_amount from  tbl_proforma_trn as mst left join unit_mst as cat on cat.unitid=mst.unit_id left join product_mst as product on product.product_id=mst.product_id LEFT JOIN mst_hsn_code as hsn on hsn.hsn_id = product.product_hsn where trancation_status=3 and mst.pid=0 and mst.user_id=".$_SESSION['user_id']." order by trancation_id Desc";
		}else{
			$query="SELECT mst.*,hsn.hsn_code,product.product_name,cat.unit_name,product.product_name,mst.description,product_qty,product_rate,product_disc,product_amount from  tbl_proforma_trn as mst left join unit_mst as cat on cat.unitid=mst.unit_id left join product_mst as product on product.product_id=mst.product_id LEFT JOIN mst_hsn_code as hsn on hsn.hsn_id = product.product_hsn where trancation_status=0 and mst.pid=0 and mst.invoice_id=".$POST['eid']." order by trancation_id Desc";
		}
	}else{
		$where = '';
		
		if($performa_invoice_type=='1'){
			if(!empty($POST['eid'])){
				$where .= "mst.trancation_status=0 and mst.invoice_id=".$POST['eid'];
			}else{
				$where .= 'mst.trancation_status=3 and mst.user_id = "'.$_SESSION['user_id'] .'" ';	
			}
			
		}
		if($performa_invoice_type=='2'){
			if(!empty($POST['eid'])){
				$where .= "mst.trancation_status=0 and mst.invoice_id=".$POST['eid'];
			}else{
				$where .= 'mst.trancation_status=3 and mst.user_id = "'.$_SESSION['user_id'] .'" ';	
			}
		}
		$query="SELECT trancation_id,hsn.hsn_code,product.product_name,cat.unit_name,product.product_name,mst.description,product_qty,product_rate,product_disc,mst.*,product_amount from  tbl_proforma_trn as mst left join unit_mst as cat on cat.unitid=mst.unit_id left join product_mst as product on product.product_id=mst.product_id LEFT JOIN mst_hsn_code as hsn on hsn.hsn_id = product.product_hsn where  $where and mst.pid=0 order by trancation_id Desc";
	}	
	/*var_dump($query);*/
	$result=$dbcon->query($query);
	echo ' <div class="form-group">
	<div class="col-md-12 col-xs-12">
	<table cellspacing="10" class="display table table-striped table-bordered table12">
	<tr id="field">
	<th class="text-center" width="8%">Sr. No.</th>
	<th class="text-center" width="25%">Product Name</th>
	<th class="text-center"width="8%">HSN Code</th>
	<th class="text-center"width="8%">Qty</th>
	<!--<th class="text-center"width="8%">Sqr/Ft</th>-->
	<!--<th class="text-center"width="8%">Serial No.</th>-->
	<th class="text-center" width="8%">Rate <span class="currency_icon"></span></th>
	<th class="text-center" width="6%">Per</th>
	<th class="text-center" width="8%">Discount <span class="currency_icon"></span></th>
	<th class="text-center" width="15%">Tax <span class="currency_icon"></span></th>
	<th class="text-center" width="12%">Amount <span class="currency_icon"></span></th>
	<th class="text-center" width="10%">Action</th>
	</tr>';
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
			echo '<tr id="fieldtr'.$id.'" >
			<td style="vertical-align:top;text-align:left">'.$i.'</td>
			<td data-label="PRODUCT NAME" style="vertical-align:top;text-align:left">
			'.$rel['product_name'].'
			'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.stripcslashes(str_replace(array("\n", "\r", "\N"), '', $rel['description'])):'').'
			</td>

			<td data-label="HSN CODE" style="vertical-align:top;" class="text-center">';
			if(empty($rel['hsn_code'])){
				echo '-';
			}else{
				echo $rel['hsn_code'];
			}
			echo'</td>
			<td data-label="QTY" style="vertical-align:top;" class="text-center">
			'.$rel['product_qty'].'
			</td>
			<!--<td style="vertical-align:top;" class="text-center">
			'.$rel['sqr_ft'].'
			</td>-->';

			echo '<td data-label="RATE" style="vertical-align:top;" class="text-right">
			'.$rate_label.'
			</td>				
			<td data-label="PER" style="vertical-align:top" class="text-center">';
			if(empty($rel['unit_name'])){
				echo '-';
			}else{
				echo $rel['unit_name'];
			}

			echo'</td>
			<td data-label="DISCOUNT" style="vertical-align:top" class="text-right">
			'.$product_discount_label.' ('.$rel['discount_per'].'%)
			</td>
			<td data-label="TAX" style="vertical-align:top" class="text-left">'.$cgst_tax.''.$sgst_tax.''.$igst_tax.'</td>
			<td data-label="AMOUNT" style="vertical-align:top" class="text-right">
			'.$product_amount_label.'
			</td>
			<input type="hidden" name="amount[]" id="amount'.$i.'" value="'.(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['product_amount'] : $rel['product_amount_conv']).'"/>

			<td data-label="ACTION" style="vertical-align:top">
			<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['trancation_id'].',\' tbl_proforma_trn\',\'trancation_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>
			<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['trancation_id'].',\' tbl_proforma_trn\',\'trancation_id\');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>';
			if($getspecialConfiguration['durva_permission']==1){
   				if($rel['pid']==0){
   					echo '&nbsp;<button type="button" class="btn btn-xs btn-primary" data-original-title="Add Accessories" data-toggle="tooltip" data-placement="top" onClick="open_accesorice_wise_product_list('.$rel['trancation_id'].')">+</button>';
   				}
   			}
			echo '</td>	
			</tr>';

			if($performa_invoice_type=='3'){
				if(empty($POST['eid'])){
					$query1="SELECT mst.*,hsn.hsn_code,product.product_name,cat.unit_name,product.product_name,mst.description,product_qty,product_rate,product_disc,product_amount from  tbl_proforma_trn as mst left join unit_mst as cat on cat.unitid=mst.unit_id left join product_mst as product on product.product_id=mst.product_id LEFT JOIN mst_hsn_code as hsn on hsn.hsn_id = product.product_hsn where trancation_status=3 and mst.user_id=".$_SESSION['user_id']." and mst.pid=".$rel['trancation_id']." order by trancation_id Desc";
				}else{
					$query1="SELECT mst.*,hsn.hsn_code,product.product_name,cat.unit_name,product.product_name,mst.description,product_qty,product_rate,product_disc,product_amount from  tbl_proforma_trn as mst left join unit_mst as cat on cat.unitid=mst.unit_id left join product_mst as product on product.product_id=mst.product_id LEFT JOIN mst_hsn_code as hsn on hsn.hsn_id = product.product_hsn where trancation_status=0 and mst.invoice_id=".$POST['eid']." and mst.pid=".$rel['trancation_id']." order by trancation_id Desc";
				}
			}else{
				$where = '';
				
				if($performa_invoice_type=='1'){
					if(!empty($POST['eid'])){
						$where .= "mst.trancation_status=0 and mst.invoice_id=".$POST['eid'];
					}else{
						$where .= 'mst.trancation_status=3 and mst.user_id = "'.$_SESSION['user_id'] .'" ';	
					}
					
				}
				if($performa_invoice_type=='2'){
					if(!empty($POST['eid'])){
						$where .= "mst.trancation_status=0 and mst.invoice_id=".$POST['eid'];
					}else{
						$where .= 'mst.trancation_status=3 and mst.user_id = "'.$_SESSION['user_id'] .'" ';	
					}
				}
				$query1="SELECT trancation_id,hsn.hsn_code,product.product_name,cat.unit_name,product.product_name,mst.description,product_qty,product_rate,product_disc,mst.*,product_amount from  tbl_proforma_trn as mst left join unit_mst as cat on cat.unitid=mst.unit_id left join product_mst as product on product.product_id=mst.product_id LEFT JOIN mst_hsn_code as hsn on hsn.hsn_id = product.product_hsn where  $where and mst.pid=".$rel['trancation_id']." order by trancation_id Desc";
			}

			$result1=$dbcon->query($query1);
			$j=1;
			while($rel1 = brp_mysqli_fetch_array($result1)){
				if(!empty($rel1['currency_id'])){
					$currency=getcurrencydetail($dbcon,$rel1['currency_id']);
				}else{
					$currency=getcurrencydetail($dbcon,$_SESSION['currency_id']);
				}
				$cgst_tax="";				
				$sgst_tax="";				
				$igst_tax="";				

				$currency_id = $rel1['currency_id'];
				$rate_label = '';$product_amount_label = '';$product_total_label = '';$product_discount_label = '';
				$selectCu = "SELECT * FROM tbl_currency WHERE currency_id='".$currency_id."' ";
				$curenresult=$dbcon->query($selectCu);
				$vrel1=brp_mysqli_fetch_assoc($curenresult);

				if($currency_id!=0){

					if($vrel1['currency_id']!=$_SESSION['currency_id']){
						$str.= '<input type="hidden" id="currency_type_response" value="'.$vrel1['currency_code'].'">';
					// 			$rate_label .= $vrel1['currency_symbol'].' :' .$rel1['product_rate']."<br>";
						$rate_label .=  $vrel1['currency_symbol'].' :' .$rel1['product_rate_conv'];

						// $product_amount_label .= $vrel1['currency_symbol'].' :' .$rel1['product_amount']."<br>";
						$product_amount_label .=  $vrel1['currency_symbol'].' :' .$rel1['product_amount_conv'];

						$product_total_label .= $vrel1['currency_symbol'].' :' .$rel1['product_amount_conv']."<br>";

						$product_discount_label .= $vrel1['currency_symbol'].' :' .$rel1['product_discount_conv']."<br>";
						//$product_total_label .=  $vrel1['currency_symbol'].' :' .$rel1['currency_total'];

						}else{
							$rate_label .= $vrel1['currency_symbol'].' :' .number_format($rel1['product_rate'],2,'.','');
							$product_amount_label .=$vrel1['currency_symbol'].' :' .$rel1['product_amount'];
							$product_total_label .= $vrel1['currency_symbol'].' :' .$rel1['product_amount'];
							$product_discount_label .= $vrel1['currency_symbol'].' :' .$rel1['product_discount']."<br>";
						}
				}else{
					$rate_label .= $_SESSION['currency_name'].' :' .number_format($rel1['product_rate'],4,'.','');
					$product_amount_label .= $_SESSION['currency_name'].' :' .$rel1['product_amount'];
					$product_total_label .= $_SESSION['currency_name'].' :' .$rel1['product_amount'];
					$product_discount_label .= $vrel1['currency_symbol'].' :' .$rel1['product_discount']."<br>";
				}


				if($rel1['cgst_tax_per']!=0)
				{
					$cgst_tax="<Strong>CGST (".$rel1['cgst_tax_per'].") : </strong>".$currency['currency_symbol']." ".(($rel1['currency_id']==$_SESSION['currency_id']) ? $rel1['cgst_tax_rate'] : $rel1['cgst_tax_rate_conv']).'<br>'	;
				}

				if($rel1['sgst_tax_per']!=0)
				{
					$sgst_tax="<Strong>SGST (".$rel1['sgst_tax_per'].") : </strong>".$currency['currency_symbol']." ".(($rel1['currency_id']==$_SESSION['currency_id']) ? $rel1['sgst_tax_rate'] : $rel1['sgst_tax_rate_conv']).'<br>';
				}

				if($rel1['igst_tax_per']!=0)
				{
					$igst_tax="<Strong>IGST (".$rel1['igst_tax_per'].") : </strong>".$currency['currency_symbol']." ".(($rel1['currency_id']==$_SESSION['currency_id']) ? $rel1['igst_tax_rate'] : $rel1['igst_tax_rate_conv']).'<br>';
				}
				echo '<tr id="fieldtr'.$id.'" >
				<td style="vertical-align:top;text-align:left">'.$i.'.'.$j.'</td>
				<td data-label="PRODUCT NAME" style="vertical-align:top;text-align:left">
				'.$rel1['product_name'].'
				'.(!empty($rel1['description'])?'<br/><strong>Desc.</strong> :'.stripcslashes(str_replace(array("\n", "\r", "\N"), '', $rel1['description'])):'').'
				</td>

				<td data-label="HSN CODE" style="vertical-align:top;" class="text-center">';
				if(empty($rel1['hsn_code'])){
					echo '-';
				}else{
					echo $rel1['hsn_code'];
				}
				echo'</td>
				<td data-label="QTY" style="vertical-align:top;" class="text-center">
				'.$rel1['product_qty'].'
				</td>
				<!--<td style="vertical-align:top;" class="text-center">
				'.$rel1['sqr_ft'].'
				</td>-->';

				echo '<td data-label="RATE" style="vertical-align:top;" class="text-right">
				'.$rate_label.'
				</td>				
				<td data-label="PER" style="vertical-align:top" class="text-center">';
				if(empty($rel1['unit_name'])){
					echo '-';
				}else{
					echo $rel1['unit_name'];
				}

				echo'</td>
				<td data-label="DISCOUNT" style="vertical-align:top" class="text-right">
				'.$product_discount_label.' ('.$rel1['discount_per'].'%)
				</td>
				<td data-label="TAX" style="vertical-align:top" class="text-left">'.$cgst_tax.''.$sgst_tax.''.$igst_tax.'</td>
				<td data-label="AMOUNT" style="vertical-align:top" class="text-right">
				'.$product_amount_label.'
				</td>
				<input type="hidden" name="amount[]" id="amount'.$i.'" value="'.(($rel1['currency_id']==$_SESSION['currency_id']) ? $rel1['product_amount'] : $rel1['product_amount_conv']).'"/>

				<td data-label="ACTION" style="vertical-align:top">
				<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel1['trancation_id'].',\' tbl_proforma_trn\',\'trancation_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>
				<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel1['trancation_id'].',\' tbl_proforma_trn\',\'trancation_id\');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
				</td>	
				</tr>';
				$j++;
			}

			$i++;
		}
	}
	else{
		echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
	}
	echo '

	</table>			 
	</div>

	</div>	';
}
else if(strtolower($POST['mode'])== "preedit")
{

	/*$q = $dbcon -> query("SELECT mst.*,pro.product_name FROM ".$_POST['table']." as mst left join tbl_product as pro on mst.product_id=pro.product_id WHERE ".$_POST['whereid']." = '$POST[id]'");
	$r = $q->fetch_assoc();*/
	if(strtolower($POST['table'])=='tbl_proforma_trntemp')
	{

				//$row['producthtml']=getproduct($dbcon,0,'0,2');
		$q = $dbcon -> query("SELECT mst.*,pro.product_name, hsn.hsn_code as product_hsn_code, pro.product_hsn FROM ".$_POST['table']." as mst left join product_mst as pro on mst.product_id=pro.product_id LEFT JOIN mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn WHERE ".$_POST['whereid']." = '$POST[id]'");
		$r = $q->fetch_assoc();

	}
	else
	{
		$q = $dbcon -> query("SELECT mst.*,pro.product_name,pro.product_type, hsn.hsn_code as product_hsn_code, pro.product_hsn FROM ".$_POST['table']." as mst left join product_mst as pro on mst.product_id=pro.product_id LEFT JOIN mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn WHERE ".$_POST['whereid']." = '$POST[id]'");
		$r = $q->fetch_assoc();

	}
	$r['producthtml'] = getrequiredproduct($dbcon,$r['product_id'],' and product_type='.$r["product_type"].'');
	$r['product_qty_show']=number_format($r['product_qty'], 4, ".", "");
	$r['product_conv_qty_show']=number_format($r['product_conv_qty'], 4, ".", "");
	echo json_encode($r);
}
else if(strtolower($POST['mode']) == "load_typeswise_terms") {
    	$quot_type=$POST['quot_type'];
    	$proforma_id=$POST['proforma_id'];
    	$performa_invoice_type = $POST['performa_invoice_type'];
    	$quotation_id = $POST['quotation_id'];
    	$sales_order_id = $POST['sales_order_id'];
    	$terms_type = $POST['terms_type'];

    	if($terms_type==4){
    		$query_so = "select terms_type from tbl_sales_order where sales_order_id=".$sales_order_id;
    		$result_so = $dbcon->query($query_so);
			$row_so = brp_mysqli_fetch_array($result_so);
    	}else if($terms_type==3){
    		$query_quot = "select terms_type from tbl_quotation where quotation_id=".$quotaion_id;
    		$result_quot = $dbcon->query($query_quot);
			$row_quot = brp_mysqli_fetch_array($result_quot);
    	}
    	

    	$str='';
    	$str.='<table class="display table table-bordered table-striped">
    	<thead>
    	<tr>
    	<th width="5%" class="text-center">
    	<input type="checkbox" class="check_all_terms" style="height: 20px;width: 20px;" id="check_all_terms" name="check_all_terms" onClick="terms_check_all(this);">
    	</th>';
    	if($terms_type==5 || $row_quot['terms_type']==2 || $row_so['terms_type']==3){
    		$str.='<th width="25%" class="text-center">Print Name</th>
    		<th width="25%" class="text-center">Term Name</th>';
    	}else{
    		$str.='<th width="25%" class="text-center">Term Name</th>';
    	}
    	$str.='<th width="5%" class="text-center">Priority</th>
    	<th width="65%" class="text-center">Term And Condition</th>				  
    	</tr>
    	</thead>
    	<tbody>';
		//Get All Terms
		if($terms_type==5 || $row_quot['terms_type']==2 || $row_so['terms_type']==3){
			$terms_qry="select * from tbl_terms_condition where tc_status=0 and
		 	tc_category=1 and find_in_set(".$quot_type.",tc_for) group by print_name order by tc_priority";
		}else{
			$terms_qry="select * from tbl_terms_condition where tc_status=0 and tc_category=1 and find_in_set(".$quot_type.",tc_for) order by tc_priority";	
		}
		
		$terms_qry_rs=$dbcon->query($terms_qry);$t=1;
		while($terms_rel=mysqli_fetch_assoc($terms_qry_rs)){
			$tc_priority=$terms_rel['tc_priority'];
			$tc_details=$terms_rel['tc_details'];
			
			if($terms_type == 1){
				if($proforma_id){
					$quot_term_qry="select * from tbl_proforma_terms_trn where proforma_terms_trn_status=0 and proforma_id=".$proforma_id." and tc_id=".$terms_rel['tc_id']."";
					$quot_term_rel=mysqli_fetch_assoc($dbcon->query($quot_term_qry));
					if($quot_term_rel['tc_priority']){
						$tc_priority=$quot_term_rel['tc_priority'];
					}
					if($quot_term_rel['tc_details']){
						$tc_details=$quot_term_rel['tc_details'];
					}
				}else{
					$cust_term_qry="select * from tbl_customer_term_trn where customer_terms_trn_status=0 and tc_for=".$quot_type." and cust_id=".$cust_id." and tc_id=".$terms_rel['tc_id'];
					$cust_term_rel = brp_mysqli_fetch_assoc($dbcon->query($cust_term_qry));
					if($cust_term_rel['tc_priority']){
						$tc_priority=$cust_term_rel['tc_priority'];
					}
					if($cust_term_rel['tc_details']){
						$tc_details=$cust_term_rel['tc_details'];
					}
					$quot_term_rel['tc_id'] = $cust_term_rel['tc_id'];
				}
			}else if($terms_type == 2){
				if($proforma_id){
					$quot_term_qry="select * from tbl_proforma_terms_trn where proforma_terms_trn_status=0 and proforma_id=".$proforma_id." and tc_id=".$terms_rel['tc_id']."";
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
			}else if($terms_type == 3){
				if($proforma_id){
					$quot_term_qry="select * from tbl_proforma_terms_trn where proforma_terms_trn_status=0 and proforma_id=".$proforma_id." and tc_id=".$terms_rel['tc_id']."";
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
			}else if($terms_type == 4){
				if($proforma_id){
					$quot_term_qry="select * from tbl_proforma_terms_trn where proforma_terms_trn_status=0 and proforma_id=".$proforma_id." and tc_id=".$terms_rel['tc_id']."";
					$quot_term_rel=mysqli_fetch_assoc($dbcon->query($quot_term_qry));
					if($quot_term_rel['tc_priority']){
						$tc_priority=$quot_term_rel['tc_priority'];
					}
					if($quot_term_rel['tc_details']){
						$tc_details=$quot_term_rel['tc_details'];
					}
				}else{
					$quot_term_qry="select * from tbl_salesorder_terms_trn where quotation_terms_trn_status=0 and sales_order_id=".$quotaion_id." and tc_id=".$terms_rel['tc_id']."";
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
			}else if($terms_type == 5){
				if($proforma_id){
					$quot_term_qry="select * from tbl_proforma_terms_trn where proforma_terms_trn_status=0 and proforma_id=".$proforma_id." and tc_id=".$terms_rel['tc_id']."";
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
				if($proforma_id){
					$quot_term_qry="select * from tbl_proforma_terms_trn where proforma_terms_trn_status=0 and proforma_id=".$proforma_id." and tc_id=".$terms_rel['tc_id']."";
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
			if($terms_type==5 || $row_quot['terms_type']==2 || $row_so['terms_type']==3){
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
					<textarea class="form-control" id="tc_details'.$t.'" name="tc_details[]" rows="4">'.$tc_details.'</textarea>
				</td>';
			} else {
				$str .= '<td>
					<textarea class="form-control" id="tc_details'.$t.'" name="tc_details[]" rows="4" readonly>'.$tc_details.'</textarea>
				</td>';
			}
		$str .= '</tr>';

		$t++;
	}	  

	$str.='</tbody> 
	</table>';	  

	$resp['resp_html']=$str;
	echo json_encode($resp);
}
else if(strtolower($POST['mode'])== "delete_data")
{
	$row=array();
	if(!empty($POST['invoice_id']))
	{
		$info['trancation_status']=2;	
				//$row['producthtml']=getproduct($dbcon,0,'0,2');
	}
	else
	{
		$info['trancation_status']=2;	
				//$row['producthtml']=getproduct($dbcon,0,'0,2');
	}
	$updateid=update_record($_POST['table'], $info,$_POST['whereid']."=".$POST['eid'] , $dbcon);

	$info_tax['tx_status']=2;	
	$updatetax=update_record("tbl_tax_trn", $info_tax, " tx_transaction_type='tbl_proforma_trn' and tx_transaction_id=".$POST['eid'] , $dbcon);

	if($updateid)
		$row['res']="1";
	else
		$row['res']="0";
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "last_rate")
{
	$query="select product_rate,trancation_id,trancation_status,product_id from tbl_proforma_trn as trn left join tbl_proforma_invoice as mst on mst.invoice_id=trn.invoice_id where cust_id=".$POST["cust_id"]." and product_id=".$POST["product_id"]." and trancation_status=0 order by trancation_id DESC";
	$prel=mysqli_fetch_assoc($dbcon->query($query));
	echo $prel['product_rate'];
}
else if(strtolower($POST['mode'])== "load_consignee")
{	
	$table = '';
	if($POST['performa_invoice_type']=='1'){
		$table='tbl_party_consignee';
	}
	echo get_custmer_consignee($dbcon,$POST['cust_id'],$POST['consignee_id'],$table);
}
else if(strtolower($POST['mode'])== "load_sales_order")
{
	echo get_sales_order($dbcon,$POST['cust_id']);
}
else if(strtolower($POST['mode'])== "load_sales_order_data")
{
	$q = $dbcon -> query("SELECT * from tbl_sales_order where sales_order_id=".$POST['sales_order_id']);
	$rel = $q->fetch_assoc();

	$resp['sales_order_no'] = $rel['sales_order_no'];
	$resp['sales_order_date'] = date("d-m-Y",strtotime($rel['sales_order_date']));
	$resp['pro_html'] = get_sales_order_data($dbcon,$POST['sales_order_id']);
	echo json_encode($resp);
}
else if(strtolower($POST['mode'])== "load_sales_pro")
{
	$resp['pro_html']=getproduct($dbcon,0,'0,2,3');
	echo json_encode($resp);
}
else if(strtolower($POST['mode'])== "loadsales_productdata")
{
	$q = $dbcon -> query("SELECT sotrn.*,
		(select IFNULL(sum(product_qty),0)  from tbl_proforma_trn as insub 
			left join tbl_proforma_invoice as inv on inv.invoice_id=insub.invoice_id
			where trancation_status=0 and inv.sales_order_id=sotrn.sales_order_id and insub.product_id=sotrn.product_id) as qty
		from tbl_sales_ordertrn as sotrn where sales_order_id=".$POST['sales_order_id']." and sales_ordertrn_status=0 and product_id=".$POST['product_id']." ");
	$resp = $q->fetch_assoc();

	echo json_encode($resp);
}
else if(strtolower($POST['mode'])== "load_qty")
{

	echo getsale_productqty($dbcon,$POST['product_id']);


}
else if(strtolower($POST['mode'])== "load_rate_hist")
{
	$resp='';
	$query="select inv.*,cust.company_name,pro.product_name,trn.product_rate from tbl_proforma_invoice as inv
	inner join tbl_proforma_trn as trn on inv.invoice_id=trn.invoice_id 
	inner join tbl_customer as cust on cust.cust_id=inv.cust_id
	inner join tbl_product as pro on pro.product_id=trn.product_id
	where inv.invoice_status=0 and trn.trancation_status=0 and inv.cust_id=".$POST["cust_id"]." and trn.product_id=".$POST["product_id"]." order by trn.trancation_id DESC LIMIT 10";

	$rs_prel=$dbcon->query($query);
	$rs_prel_num_rows=mysqli_num_rows($rs_prel);

	if($rs_prel_num_rows>0){
		while($prel=mysqli_fetch_assoc($rs_prel)){

			$resp.='<tr>
			<td data-label="Invoice No." class="text-center">'.$prel['invoice_no'].'</td>
			<td data-label="Invoice Date" class="text-center">'.date('d-m-y',strtotime($prel['invoice_date'])).'</td>
			<td data-label="Product Date" class="text-center">'.$prel['product_rate'].'</td>
			</tr>';
			$row['cust_name']=$prel['company_name'];
			$row['product_name']=$prel['product_name'];		
		}
	}
	else{
		$resp.='<tr>
		<td colspan="3" class="text-center">NO DATA FOUND !!</td>
		</tr>';
		$row['cust_name']="";
		$row['product_name']="";
	}


	$row['resp']=$resp;

	echo json_encode($row);
}

else if(strtolower($POST['mode'])== "use_cr"){
			//Delete Old paid Amount from Invoice Table
	$inv_upd = $dbcon->query("UPDATE tbl_proforma_invoice INNER JOIN tbl_used_credit ON tbl_proforma_invoice.invoice_id = tbl_used_credit.invoice_id SET paid_amount = paid_amount - ( SELECT SUM( inr_cr.used_credit_amt ) 
		FROM tbl_used_credit AS inr_cr WHERE inr_cr.invoice_id =".$POST['invoice_id']." ) 
		WHERE tbl_used_credit.invoice_id =".$POST['invoice_id']);

	foreach($POST['used_credit_amt'] as $key => $name)
	{
		if(floatval($POST['used_credit_amt'][$key])){
					//Delete Old paid Amount from Credit Note Table
			$cr_upd = $dbcon->query("UPDATE tbl_credit_note 
				inner join tbl_used_credit on tbl_credit_note.credit_note_id=tbl_used_credit.credit_note_id set paid_amount = paid_amount - used_credit_amt
				where tbl_credit_note.credit_note_id=".$POST['credit_note_id'][$key]." and tbl_used_credit.invoice_id=".$POST['invoice_id']);
		}
	}
	$del_id=delete_record('tbl_used_credit',"invoice_id=".$POST['invoice_id'], $dbcon);	

	foreach($POST['used_credit_amt'] as $key => $name)
	{
		if(floatval($POST['used_credit_amt'][$key])){
					//Entry in Used Credit Table
			$info1['invoice_id']		= $POST['invoice_id'];
			$info1['credit_note_id']	= $POST['credit_note_id'][$key];
			$info1['used_credit_amt']	= $POST['used_credit_amt'][$key];
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$info1['cdate']				= date("Y-m-d H:i:s");
			$insertrnid=add_record('tbl_used_credit', $info1, $dbcon);

					//Update In Credit Note Table
			$cr_upd = $dbcon->query("UPDATE tbl_credit_note SET paid_amount = paid_amount + ".$POST['used_credit_amt'][$key]." WHERE credit_note_id = ".$POST['credit_note_id'][$key]);
		}
	}


			//Update In Invoice Table
	$inv_upd =  $dbcon->query("UPDATE tbl_proforma_invoice SET paid_amount = paid_amount + ".$POST['total_cr']." WHERE invoice_id = ".$POST['invoice_id']);

	if($insertrnid){
		$resp['msg']='1';
	}
	else{
		$resp['msg']='0';
	}
	echo json_encode($resp);
}
                // Start : Dimple panchal
else if(strtolower($POST['mode'])== "get_tax_on_total")
{
	$arr = get_tax_on_total($dbcon,$POST['total'],$POST['formulaid']);
	echo json_encode($arr);
}
                // End : Dimple panchal
else if(brp_strtolower($POST['mode'])== "performa_invoice_type"){
	$performa_type = $POST['performa_type'];
	$cust_id = $POST['cust_id'];

	if($performa_type=='1'){
		$sql = "select qt.quotation_id, qt.quotation_no, qt.inquiry_id,qt.revise_status,inq.lost_by_userid from tbl_quotation as qt left join tbl_inquiry as inq on inq.inquiry_id = qt.inquiry_id where qt.cust_id='".$cust_id."' and inq.lost_by_userid='0' and qt.revise_status!='1'";
		$rs_prel=$dbcon->query($sql);

		$str .="<option value=''>Choose Quotation</option>";
		$q_id = $POST['edit_quotation_id'];
		while($row=mysqli_fetch_assoc($rs_prel))
		{	
			$sel='';
					//if($row['user_id']==$sid)

			if($row['quotation_id']==$q_id)
				{$sel='selected="selected"';} else {$sel="";}
			$str.= '<option '.$sel.' value="'.$row['quotation_id'].'">'.$row['quotation_no'].'</option>';
		}

		$arr['data'] = $str;
	}
	if($performa_type=='2'){
		$sql = "select sales_order_id, sales_order_no from tbl_sales_order where cust_id='".$cust_id."' and sales_order_status!='2'";
		$rs_prel=$dbcon->query($sql);

		$str .="<option value=''>Choose S.O.</option>";
		$s_id = $POST['edit_sales_order_id'];
		while($row=mysqli_fetch_assoc($rs_prel))
		{	
			$sel='';
					//if($row['user_id']==$sid)
			if($row['sales_order_id']==$s_id)
				{$sel='selected="selected"';} else {$sel="";}
			$str.= '<option '.$sel.' value="'.$row['sales_order_id'].'">'.$row['sales_order_no'].'</option>';
		}

		$arr['data'] = $str;
	}
	$arr['performa_type'] = $performa_type;
	echo json_encode($arr);
}
else if(brp_strtolower($POST['mode'])== "load_company_data"){
	$companyConfiguration=getCompanyConfiguration($dbcon);
	$sales_party_show = $companyConfiguration['sales_party_show'];
	$performa_invoice_type = $POST['performa_invoice_type'];
	$edit_customer_id = $POST['edit_customer_id'];
	$deleteid=delete_record('tbl_proforma_trn',"trancation_status=3", $dbcon);
	if($performa_invoice_type=='1'){

		$arr['data'] = getcustomer($dbcon,$edit_customer_id,1);

	}else{
		$arr['data'] = getcust($dbcon,$edit_customer_id,$sales_party_show,1);
	}

	echo json_encode($arr);
}
else if(strtolower($POST['mode']) == "get_so_detail") {
	$q = $dbcon -> query("SELECT * from tbl_sales_order where sales_order_id=".$POST['id']);
	$rel = $q->fetch_assoc();
	// $arr['data'] = $rel;
	echo json_encode($rel);
}
else if(strtolower($POST['mode']) == "get_quotation_detail") {
	$q = $dbcon -> query("SELECT * from tbl_quotation where quotation_id=".$POST['id']);
	$rel = $q->fetch_assoc();
	// $arr['data'] = $rel;
	echo json_encode($rel);
}
else if(strtolower($POST['mode']) == "insert_quotation_salesorder_item") {

	$performa_invoice_type = $POST['performa_invoice_type'];
	$quotation_salesorder_id = $POST['id'];
	$getspecialConfiguration=getspecialConfiguration($dbcon);


	if($performa_invoice_type=='1'){
		$where = '';
		if($getspecialConfiguration['durva_permission']==1){
			$where = ' and qtn.pid=0';
		}

		$sql = "SELECT qtn.*,p.product_hsn as product_hsn_code FROM `tbl_quotation_trn` as qtn left join product_mst as p on p.product_id = qtn.product_id where qtn.quotation_id='".$quotation_salesorder_id."' ".$where." and qtn.quot_trn_status!=2 ";
		$rs_prel=$dbcon->query($sql);
		$rs_prel_num_rows=mysqli_num_rows($rs_prel);
		$deleteid=delete_record('tbl_proforma_trn',"trancation_status= 3", $dbcon);
		if($rs_prel_num_rows>0){
			while($prel=mysqli_fetch_assoc($rs_prel)){
				// $info1['quotation_id']	= $quotation_salesorder_id;
				$company_state = get_company_data($dbcon,$_SESSION['company_id']);
			//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
				$sale_gst = get_tax_cat_by_hsn($dbcon,$prel['product_hsn_code']);

				$cgst_tax_rate=0;
				$sgst_tax_rate=0;
				$igst_tax_rate=0;
				if(($company_state['stateid'] == $POST['cust_stateid'])){
					$gst = $sale_gst['tax_gst']/2;
					$cgst_tax_per = $gst;
					$cgst_tax_rate = ($gst*$POST['product_amount'])/100;
					$sgst_tax_per = $gst;
					$sgst_tax_rate = ($gst*$POST['product_amount'])/100;
				}else{
					$igst_tax_per = $sale_gst['tax_gst'];
					$igst_tax_rate = ($sale_gst['tax_gst']*$POST['product_amount'])/100;
				}
				$info1['product_id']		= $prel['product_id'];
				$info1['cat_id']			= $prel['cat_id'];
				$info1['description']		= stripcslashes(str_replace(array("\n", "\r", "\N"), '', $prel['product_desc']));//stripcslashes(text_rnremove($prel['product_desc']));
				$info1['product_hsn_code']	= $prel['product_hsn_code'];
				$info1['product_qty']		= $prel['product_qty'];
				$info1['product_conv_qty']	= $prel['product_conv_qty'];
				$info1['product_disc']		= stripcslashes(str_replace(array("\n", "\r", "\N"), '', $prel['product_desc']));;

				$info1['unit_id']			= $prel['unitid'];
				$info1['conv_unit_id']		= $prel['conv_unit_id'];
				$info1['rate_unit']			= $prel['rate_unit'];
				$info1['discount_per']		= $prel['discount_per'];
				$info1['product_tax_cat']	= $prel['product_tax_cat'];
				$info1['cgst_tax_per']		= $prel['cgst_tax_per'];
				$info1['sgst_tax_per']		= $prel['sgst_tax_per'];
				$info1['igst_tax_per']		= $prel['igst_tax_per'];
				$info1['currency_id']		= $prel['currency_id'];
				$info1['currency_rate']		= $prel['currency_rate'];
				
				$info1['product_rate']		= $prel['product_rate'];
				$info1['total']				= $prel['product_total'];
				$info1['product_discount']	= $prel['product_discount'];
				$info1['cgst_tax_rate']		= $prel['cgst_tax_rate'];
				$info1['sgst_tax_rate']		= $prel['sgst_tax_rate'];
				$info1['igst_tax_rate']		= $prel['igst_tax_rate'];
				$info1['product_amount']	= $total=($prel['product_rate']*$prel['product_qty'])-$prel['product_discount'];

				$info1['product_discount_conv']	= $prel['product_discount_conv'];
				$info1['total_conv']			= $prel['product_total_conv'];
				$info1['product_amount_conv']	= $prel['product_amount_conv'];
				$info1['product_rate_conv']		= $prel['product_rate_conv'];
				$info1['cgst_tax_rate_conv']	= $prel['cgst_tax_rate_conv'];
				$info1['sgst_tax_rate_conv']	= $prel['sgst_tax_rate_conv'];
				$info1['igst_tax_rate_conv']	= $prel['igst_tax_rate_conv'];

				// $info1['formulaid']		= $prel['formulaid'];
				// $info=get_product_tax($dbcon,$total,$prel['formulaid']);
				// $info1=array_merge($info1,$info);
				$table='tbl_proforma_trn';$tableid='trancation_id';
				if(!empty($POST['invoice_id']))
				{
					$info1['invoice_id']= $POST['invoice_id'];
				}
				else
				{
					$info1['user_id']	= $_SESSION['user_id'];
					$info1['trancation_status']	= 3;
				}

				if(empty($POST['edit_id']))
				{
					$inserid=add_record($table, $info1, $dbcon);
				}
				else
				{
					$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);
					$inserid = $POST['edit_id'];
				}
				if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
					$cl_id = get_ledger_by_name($dbcon,'CGST');
					$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid,"tbl_proforma_trn",$prel['product_id'],3,$inserid,$prel['branch_id'],$prel['currency_id'],$prel['currency_rate'],$prel['cgst_tax_rate_conv']);
				}
				if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
					$cl_id = get_ledger_by_name($dbcon,'SGST');
					$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid,"tbl_proforma_trn",$prel['product_id'],3,$inserid,$prel['branch_id'],$prel['currency_id'],$prel['currency_rate'],$prel['sgst_tax_rate_conv']);
				}
				if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
					$cl_id = get_ledger_by_name($dbcon,'IGST');
					$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid,"tbl_proforma_trn",$prel['product_id'],3,$inserid,$prel['branch_id'],$prel['currency_id'],$prel['currency_rate'],$prel['igst_tax_rate_conv']);
				}
				$pro_amt = $prel['product_amount']*$prel['currency_rate'];
				$count_add_tax=get_check_addition_tax($dbcon,$sale_gst['tax_cat_id'],$prel['product_amount'],$inserid,$prel['product_id'],$inserid,$prel['branch_id'],'tbl_proforma_trn',$prel['currency_id'],$prel['currency_rate'],$pro_amt);

				if($getspecialConfiguration['durva_permission']==1){
					$where1 = '';
					if($getspecialConfiguration['durva_permission']==1){
						$where1 = ' and qtn.pid='.$prel['quot_trn_id'];
					}

					$sql1 = "SELECT qtn.*,p.product_hsn as product_hsn_code FROM `tbl_quotation_trn` as qtn left join product_mst as p on p.product_id = qtn.product_id where qtn.quotation_id='".$quotation_salesorder_id."' ".$where1." and qtn.quot_trn_status!=2 ";
					$rs_prel1=$dbcon->query($sql1);
					$rs_prel_num_rows1=brp_mysqli_num_rows($rs_prel1);

					while($prel1 = brp_mysqli_fetch_array($rs_prel1)){
						$sale_gst = get_tax_cat_by_hsn($dbcon,$prel['product_hsn_code']);

						$cgst_tax_rate=0;
						$sgst_tax_rate=0;
						$igst_tax_rate=0;
						if(($company_state['stateid'] == $POST['cust_stateid'])){
							$gst = $sale_gst['tax_gst']/2;
							$cgst_tax_per = $gst;
							$cgst_tax_rate = ($gst*$POST['product_amount'])/100;
							$sgst_tax_per = $gst;
							$sgst_tax_rate = ($gst*$POST['product_amount'])/100;
						}else{
							$igst_tax_per = $sale_gst['tax_gst'];
							$igst_tax_rate = ($sale_gst['tax_gst']*$POST['product_amount'])/100;
						}
						$info12['product_id']		= $prel1['product_id'];
						$info12['cat_id']			= $prel1['cat_id'];
						$info12['description']		= stripcslashes(str_replace(array("\n", "\r", "\N"), '', $prel1['product_desc']));//stripcslashes(text_rnremove($prel1['product_desc']));
						$info12['product_hsn_code']	= $prel1['product_hsn_code'];
						$info12['product_qty']		= $prel1['product_qty'];
						$info12['product_conv_qty']	= $prel1['product_conv_qty'];
						$info12['product_disc']		= stripcslashes(str_replace(array("\n", "\r", "\N"), '', $prel1['product_desc']));;

						$info12['pid']				= $inserid;

						$info12['unit_id']			= $prel1['unitid'];
						$info12['conv_unit_id']		= $prel1['conv_unit_id'];
						$info12['rate_unit']		= $prel1['rate_unit'];
						$info12['discount_per']		= $prel1['discount_per'];
						$info12['product_tax_cat']	= $prel1['product_tax_cat'];
						$info12['cgst_tax_per']		= $prel1['cgst_tax_per'];
						$info12['sgst_tax_per']		= $prel1['sgst_tax_per'];
						$info12['igst_tax_per']		= $prel1['igst_tax_per'];
						$info12['currency_id']		= $prel1['currency_id'];
						$info12['currency_rate']		= $prel1['currency_rate'];
						
						$info12['product_rate']		= $prel1['product_rate'];
						$info12['total']				= $prel1['product_total'];
						$info12['product_discount']	= $prel1['product_discount'];
						$info12['cgst_tax_rate']		= $prel1['cgst_tax_rate'];
						$info12['sgst_tax_rate']		= $prel1['sgst_tax_rate'];
						$info12['igst_tax_rate']		= $prel1['igst_tax_rate'];
						$info12['product_amount']	= $total=($prel1['product_rate']*$prel1['product_qty'])-$prel1['product_discount'];

						$info12['product_discount_conv']	= $prel1['product_discount_conv'];
						$info12['total_conv']			= $prel1['product_total_conv'];
						$info12['product_amount_conv']	= $prel1['product_amount_conv'];
						$info12['product_rate_conv']		= $prel1['product_rate_conv'];
						$info12['cgst_tax_rate_conv']	= $prel1['cgst_tax_rate_conv'];
						$info12['sgst_tax_rate_conv']	= $prel1['sgst_tax_rate_conv'];
						$info12['igst_tax_rate_conv']	= $prel1['igst_tax_rate_conv'];

						// $info12['formulaid']		= $prel1['formulaid'];
						// $info=get_product_tax($dbcon,$total,$prel1['formulaid']);
						// $info12=array_merge($info12,$info);
						$table='tbl_proforma_trn';$tableid='trancation_id';
						if(!empty($POST['invoice_id']))
						{
							$info12['invoice_id']= $POST['invoice_id'];
						}
						else
						{
							$info12['user_id']	= $_SESSION['user_id'];
							$info12['trancation_status']	= 3;
						}

						if(empty($POST['edit_id']))
						{
							$inserid1=add_record($table, $info12, $dbcon);
						}
						else
						{
							$updateid=update_record($table, $info12,$tableid."=".$POST['edit_id'] , $dbcon);
							$inserid1 = $POST['edit_id'];
						}
						if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
							$cl_id = get_ledger_by_name($dbcon,'CGST');
							$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid1,"tbl_proforma_trn",$prel1['product_id'],3,$inserid1,$prel1['branch_id'],$prel1['currency_id'],$prel1['currency_rate'],$prel1['cgst_tax_rate_conv']);
						}
						if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
							$cl_id = get_ledger_by_name($dbcon,'SGST');
							$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid1,"tbl_proforma_trn",$prel1['product_id'],3,$inserid1,$prel1['branch_id'],$prel1['currency_id'],$prel1['currency_rate'],$prel1['sgst_tax_rate_conv']);
						}
						if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
							$cl_id = get_ledger_by_name($dbcon,'IGST');
							$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid1,"tbl_proforma_trn",$prel1['product_id'],3,$inserid1,$prel1['branch_id'],$prel1['currency_id'],$prel1['currency_rate'],$prel1['igst_tax_rate_conv']);
						}
						$pro_amt = $prel1['product_amount']*$prel1['currency_rate'];
						$count_add_tax=get_check_addition_tax($dbcon,$sale_gst['tax_cat_id'],$prel1['product_amount'],$inserid1,$prel1['product_id'],$inserid1,$prel1['branch_id'],'tbl_proforma_trn',$prel1['currency_id'],$prel1['currency_rate'],$pro_amt);
					}
				}
			}
		}
	}else{
		$where = '';
		if($getspecialConfiguration['durva_permission']==1){
			$where = ' and pid=0';
		}

		$sql = "SELECT * FROM `tbl_sales_ordertrn` where sales_order_id='".$quotation_salesorder_id."' ".$where." and sales_ordertrn_status!=2 ";
		$rs_prel=$dbcon->query($sql);
		$rs_prel_num_rows=mysqli_num_rows($rs_prel);
		$deleteid=delete_record('tbl_proforma_trn',"trancation_status= 3", $dbcon);
		if($rs_prel_num_rows>0){
			while($prel=mysqli_fetch_assoc($rs_prel)){
				$company_state1 = get_company_data($dbcon,$_SESSION['company_id']);
			//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
				$sale_gst = get_tax_cat_by_hsn($dbcon,$prel['product_hsn_code']);

				$cgst_tax_rate=0;
				$sgst_tax_rate=0;
				$igst_tax_rate=0;
				if(($company_state['stateid'] == $POST['cust_stateid'])){
					$gst = $sale_gst['tax_gst']/2;
					$cgst_tax_per = $gst;
					$cgst_tax_rate = ($gst*$POST['product_amount'])/100;
					$sgst_tax_per = $gst;
					$sgst_tax_rate = ($gst*$POST['product_amount'])/100;
				}else{
					$igst_tax_per = $sale_gst['tax_gst'];
					$igst_tax_rate = ($sale_gst['tax_gst']*$POST['product_amount'])/100;
				}
				// $info1['sales_order_id']	= $quotation_salesorder_id;
				$info1['product_id']		= $prel['product_id'];
				$info1['cat_id']			= $prel['product_category_id'];
				$info1['description']		= stripcslashes(text_rnremove($prel['description']));
				$info1['product_hsn_code']	= $prel['product_hsn_code'];
				$info1['product_qty']		= $prel['product_qty'];
				$info1['product_conv_qty']	= $prel['product_conv_qty'];
				$info1['product_disc']		= $prel['description'];

				$info1['unit_id']			= $prel['unit_id'];
				$info1['conv_unit_id']		= $prel['conv_unit_id'];
				$info1['rate_unit']			= $prel['rate_unit'];

				$info1['discount_per']		= $prel['discount_per'];
				$info1['formulaid']		= $prel['formulaid'];
				$info1['product_tax_cat']	= $prel['product_tax_cat'];
				$info1['cgst_tax_per']		= $prel['cgst_tax_per'];
				$info1['sgst_tax_per']		= $prel['sgst_tax_per'];
				$info1['igst_tax_per']		= $prel['igst_tax_per'];
				$info1['currency_id']		= $prel['currency_id'];
				$info1['currency_rate']		= $prel['currency_rate'];
				
				$info1['product_rate']		= $prel['product_rate'];
				$info1['product_discount']	= $prel['product_discount'];
				$info1['total']				= $prel['total'];
				$info1['product_amount']	= $prel['product_amount'];
				$info1['cgst_tax_rate']		= $prel['cgst_tax_rate'];
				$info1['sgst_tax_rate']		= $prel['sgst_tax_rate'];
				$info1['igst_tax_rate']		= $prel['igst_tax_rate'];

				$info1['product_discount_conv']	= $prel['product_discount_conv'];
				$info1['total_conv']			= $prel['total_conv'];
				$info1['product_amount_conv']	= $prel['product_amount_conv'];
				$info1['product_rate_conv']		= $prel['product_rate_conv'];
				$info1['cgst_tax_rate_conv']	= $prel['cgst_tax_rate_conv'];
				$info1['sgst_tax_rate_conv']	= $prel['sgst_tax_rate_conv'];
				$info1['igst_tax_rate_conv']	= $prel['igst_tax_rate_conv'];

				$table='tbl_proforma_trn';$tableid='trancation_id';
				if(!empty($POST['invoice_id']))
				{
					$info1['invoice_id']= $POST['invoice_id'];
					// $table='tbl_proforma_trn';
					// $tableid='trancation_id';
				}
				else
				{
					$info1['user_id']	= $_SESSION['user_id'];
					$info1['trancation_status']	= 3;
				}

				if(empty($POST['edit_id']))
				{
					$inserid=add_record($table, $info1, $dbcon);
				}
				else
				{
					$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
					$inserid = $POST['edit_id'];
				}
				if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
					$cl_id = get_ledger_by_name($dbcon,'CGST');
					$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid,"tbl_proforma_trn",$prel['product_id'],3,$inserid,$prel['branch_id'],$prel['currency_id'],$prel['currency_rate'],$prel['cgst_tax_rate_conv']);
				}
				if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
					$cl_id = get_ledger_by_name($dbcon,'SGST');
					$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid,"tbl_proforma_trn",$prel['product_id'],3,$inserid,$prel['branch_id'],$prel['currency_id'],$prel['currency_rate'],$prel['sgst_tax_rate_conv']);
				}
				if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
					$cl_id = get_ledger_by_name($dbcon,'IGST');
					$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid,"tbl_proforma_trn",$prel['product_id'],3,$inserid,$prel['branch_id'],$prel['currency_id'],$prel['currency_rate'],$prel['igst_tax_rate_conv']);
				}
				$pro_amt = $prel['product_amount']*$prel['currency_rate'];
				$count_add_tax=get_check_addition_tax($dbcon,$sale_gst['tax_cat_id'],$prel['product_amount'],$inserid,$prel['product_id'],$inserid,$prel['branch_id'],'tbl_proforma_trn',$prel['currency_id'],$prel['currency_rate'],$pro_amt);

				if($getspecialConfiguration['durva_permission']==1){
					$where1 = '';
					if($getspecialConfiguration['durva_permission']==1){
						$where1 = ' and pid='.$prel['sales_ordertrn_id'];
					}

					$sql1 = "SELECT * FROM `tbl_sales_ordertrn` where sales_order_id='".$quotation_salesorder_id."' ".$where1." and sales_ordertrn_status!=2 ";
					$rs_prel1=$dbcon->query($sql1);
					$rs_prel_num_rows1=brp_mysqli_num_rows($rs_prel1);
					while($prel1=brp_mysqli_fetch_array($rs_prel1)){
						$company_state1 = get_company_data($dbcon,$_SESSION['company_id']);
						//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
						$sale_gst1 = get_tax_cat_by_hsn($dbcon,$prel1['product_hsn_code']);

						$cgst_tax_rate=0;
						$sgst_tax_rate=0;
						$igst_tax_rate=0;
						if(($company_state1['stateid'] == $POST['cust_stateid'])){
							$gst = $sale_gst1['tax_gst']/2;
							$cgst_tax_per = $gst;
							$cgst_tax_rate = ($gst*$POST['product_amount'])/100;
							$sgst_tax_per = $gst;
							$sgst_tax_rate = ($gst*$POST['product_amount'])/100;
						}else{
							$igst_tax_per = $sale_gst1['tax_gst'];
							$igst_tax_rate = ($sale_gst1['tax_gst']*$POST['product_amount'])/100;
						}
						// $info1['sales_order_id']	= $quotation_salesorder_id;
						$info12['product_id']		= $prel1['product_id'];
						$info12['cat_id']			= $prel1['product_category_id'];
						$info12['description']		= stripcslashes(text_rnremove($prel1['description']));
						$info12['product_hsn_code']	= $prel1['product_hsn_code'];
						$info12['product_qty']		= $prel1['product_qty'];
						$info12['product_conv_qty']	= $prel1['product_conv_qty'];
						$info12['product_disc']		= $prel1['description'];
						$info12['pid']				= $inserid;

						$info12['unit_id']			= $prel1['unit_id'];
						$info12['conv_unit_id']		= $prel1['conv_unit_id'];
						$info12['rate_unit']		= $prel1['rate_unit'];
						$info12['discount_per']		= $prel1['discount_per'];
						$info12['formulaid']		= $prel1['formulaid'];
						$info12['product_tax_cat']	= $prel1['product_tax_cat'];
						$info12['cgst_tax_per']		= $prel1['cgst_tax_per'];
						$info12['sgst_tax_per']		= $prel1['sgst_tax_per'];
						$info12['igst_tax_per']		= $prel1['igst_tax_per'];
						$info12['currency_id']		= $prel1['currency_id'];
						$info12['currency_rate']		= $prel1['currency_rate'];
						
						$info12['product_rate']		= $prel1['product_rate'];
						$info12['product_discount']	= $prel1['product_discount'];
						$info12['total']				= $prel1['total'];
						$info12['product_amount']	= $total=($prel1['product_rate']*$prel1['product_qty'])-$prel1['product_discount'];
						$info12['cgst_tax_rate']		= $prel1['cgst_tax_rate'];
						$info12['sgst_tax_rate']		= $prel1['sgst_tax_rate'];
						$info12['igst_tax_rate']		= $prel1['igst_tax_rate'];

						$info12['product_discount_conv']	= $prel1['product_discount_conv'];
						$info12['total_conv']			= $prel1['total_conv'];
						$info12['product_amount_conv']	= $prel1['product_amount_conv'];
						$info12['product_rate_conv']		= $prel1['product_rate_conv'];
						$info12['cgst_tax_rate_conv']	= $prel1['cgst_tax_rate_conv'];
						$info12['sgst_tax_rate_conv']	= $prel1['sgst_tax_rate_conv'];
						$info12['igst_tax_rate_conv']	= $prel1['igst_tax_rate_conv'];

						$table='tbl_proforma_trn';$tableid='trancation_id';
						if(!empty($POST['invoice_id']))
						{
							$info12['invoice_id']= $POST['invoice_id'];
							// $table='tbl_proforma_trn';
							// $tableid='trancation_id';
						}
						else
						{
							$info12['user_id']	= $_SESSION['user_id'];
							$info12['trancation_status']	= 3;
						}

						if(empty($POST['edit_id']))
						{
							$inserid1=add_record($table, $info12, $dbcon);
						}
						else
						{
							$updateid=update_record($table, $info12,$tableid."=".$POST['edit_id'] , $dbcon);	
							$inserid1 = $POST['edit_id'];
						}
						if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
							$cl_id = get_ledger_by_name($dbcon,'CGST');
							$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid1,"tbl_proforma_trn",$prel1['product_id'],3,$inserid1,$prel1['branch_id'],$prel1['currency_id'],$prel1['currency_rate'],$prel1['cgst_tax_rate_conv']);
						}
						if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
							$cl_id = get_ledger_by_name($dbcon,'SGST');
							$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid1,"tbl_proforma_trn",$prel1['product_id'],3,$inserid1,$prel1['branch_id'],$prel1['currency_id'],$prel1['currency_rate'],$prel1['sgst_tax_rate_conv']);
						}
						if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
							$cl_id = get_ledger_by_name($dbcon,'IGST');
							$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid1,"tbl_proforma_trn",$prel1['product_id'],3,$inserid1,$prel1['branch_id'],$prel1['currency_id'],$prel1['currency_rate'],$prel1['igst_tax_rate_conv']);
						}
						$pro_amt = $prel1['product_amount']*$prel1['currency_rate'];
						$count_add_tax=get_check_addition_tax($dbcon,$sale_gst1['tax_cat_id'],$prel1['product_amount'],$inserid1,$prel1['product_id'],$inserid1,$prel1['branch_id'],'tbl_proforma_trn',$prel1['currency_id'],$prel1['currency_rate'],$pro_amt);
					}
				}
			}
		}		
	}

}
else if(strtolower($POST['mode'])== "get_ledger_details")
{

	$ledger_id=$POST['ledger_id'];

	$row=get_ledger_details($dbcon,$ledger_id);

	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "get_gst_statecode")
{
	if($POST['performa_invoice_type']==1){
		$arr = get_crm_gst_statecode($dbcon,$POST['cust_id']);
	}else{
		$arr = get_gst_statecode($dbcon,$POST['cust_id']);
	}
	echo $arr;
}
else if(strtolower($POST['mode']) == "load_pi_hist_datatable") {

	$where='';
	$where.="  and log.invoice_id=".$POST['invoice_id'];

	$appData = array();
	$i=1;
	$aColumns = array('log.quot_aprv_log_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
	$sIndexColumn = "log.quot_aprv_log_id";
	$isWhere = array("log.quot_aprv_log_status=0 ".$where." ");
	$sTable = "tbl_quot_po_aprv_log as log";			
	$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
	$hOrder = "log.quot_aprv_log_id desc";
	include($include.'pagging.php');
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
			//print_r($row_data);
	}
	$output['aaData'] = $appData;
		//print_r($output);
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "load_party_pi_dtl") {
	$que = "SELECT performa_invoice_type FROM tbl_proforma_invoice WHERE invoice_id=".$POST['invoice_id'];
	$rel_que=mysqli_fetch_assoc($dbcon->query($que));

	if($rel_que['performa_invoice_type']=='1'){
		$qt_qry="SELECT invoice.*,country.country_name,payterms.payment_terms AS payment_trm,state.state_name,cust_a.c_add_state,state.gst_state_code, city.city_name, cust.cust_name AS company_name,cust_a.c_add_location AS cust_address,cust_a.c_add_street AS cust_street,cust_a.c_add_zip AS cust_pincode, type.invoice_type,cust_mobile,cust_email,cust.cust_gst AS gst_no 
		FROM tbl_proforma_invoice AS invoice 
		LEFT JOIN tbl_customer AS cust on cust.cust_id=invoice.cust_id 
		LEFT JOIN tbl_cust_address AS cust_a on cust_a.cust_id=invoice.cust_id 
		LEFT JOIN country_mst AS country on country.countryid=cust_a.c_add_country 
		LEFT JOIN state_mst AS state on state.stateid=cust_a.c_add_state 
		LEFT JOIN city_mst AS city on city.cityid=cust_a.c_add_city 
		LEFT JOIN tbl_invoicetype AS type on type.invoicetype_id=invoice.invoicetype_id 
		LEFT JOIN pay_terms AS payterms on payterms.terms_id=invoice.payment_terms 
		WHERE invoice_id=".$POST['invoice_id'];
	}else{
		$qt_qry="SELECT invoice.*,country.country_name,payterms.payment_terms AS payment_trm,state.state_name,cust.stateid,state.gst_state_code, city.city_name, cust.l_name AS company_name,cust.m_address AS cust_address, type.invoice_type,cust_pincode,cust_mobile,gst_no 
		FROM tbl_proforma_invoice AS invoice 
		LEFT JOIN tbl_ledger AS cust on cust.l_id=invoice.cust_id 
		LEFT JOIN country_mst AS country on country.countryid=cust.countryid 
		LEFT JOIN state_mst AS state on state.stateid=cust.stateid 
		LEFT JOIN city_mst AS city on city.cityid=cust.cityid 
		LEFT JOIN tbl_invoicetype AS type on type.invoicetype_id=invoice.invoicetype_id 
		LEFT JOIN pay_terms AS payterms on payterms.terms_id=invoice.payment_terms 
		WHERE invoice_id=".$POST['invoice_id'];
	}

	$qt_rel=mysqli_fetch_assoc($dbcon->query($qt_qry));

	$query="select mst.*,product.product_name,cat.unit_name from tbl_proforma_trn as mst 
	left join unit_mst as cat on cat.unitid=mst.unit_id 
	left join product_mst as product on product.product_id=mst.product_id  
	where trancation_status=0 and invoice_id=".$POST['invoice_id'];
	$result=$dbcon->query($query);
// 			var_dump($qt_qry);
		//Party PO Details Table View
	$str='<div class="card">
	<ul class="nav nav-tabs" id="my_tab_id" role="tablist">
	<li role="presentation" id="tab1" class="active"><a href="#product-details" aria-controls="product-details" role="tab" data-toggle="tab">Company Details</a></li>
	<li role="presentation" id="tab2"><a href="#product-desc" aria-controls="product-desc" role="tab" data-toggle="tab">Products</a></li>
	</ul>
	<div class="tab-content">
	<div role="tabpanel" class="tab-pane active" id="product-details">
	<div class="form-group col-md-12">
	<table class="display table table-bordered table-striped">
	<tr>
	<td colspan="2"><strong>Company Name:</strong> '.$qt_rel['company_name'].'</td>
	<td><strong>Contact No.:</strong> '.$qt_rel['cust_mobile'].'</td>
	</tr>
	<tr>
	<td colspan="2"><strong>Address:</strong> '.$qt_rel['cust_address'].'</td>
	<td><strong>GST No.:</strong> '.$qt_rel['gst_no'].'</td>
	</tr>
	<tr>
	<td><strong>City:</strong> '.$qt_rel['city_name'].'</td>
	<td><strong>State:</strong> '.$qt_rel['state_name'].'</td>
	<td><strong>Country:</strong> '.$qt_rel['country_name'].'</td>
	</tr>
	<tr>
	<td><strong>Invoice No:</strong> '.$qt_rel['invoice_no'].'</td>
	<td><strong>Invoice Date:</strong> '.date("d-M-Y",strtotime($qt_rel["invoice_date"])).'</td>
	<td><strong>Invoice Total Amount:</strong> '.$qt_rel['g_total'].'</td>
	</tr>
	</table>
	</div>
	</div>
	<div class="tab-pane" id="product-desc" >
	<div class="row">
	<div class="col-md-12">
	<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
	<tr id="field">
	<th class="text-center"width="25%">Product Name</th>
	<th class="text-center"width="8%">HSN Code</th>
	<th class="text-center"width="8%">Qty</th>
	<th class="text-center"width="10%">Rate</th>
	<th class="text-center"width="6%" style="display:none">Per</th>
	<th class="text-center"width="8%">Discount</th>
	<th class="text-center"width="10%">Taxable value</th>
	<th class="text-center"width="15%">Tax</th>
	<th class="text-center"width="12%">Amount</th>
	</tr>';
	if(mysqli_num_rows($result)>0) {
		$i=1;
		while($rel=mysqli_fetch_assoc($result)) {
			$str.='<tr id="fieldtr'.$id.'" >
			<td data-label="PRODUCT NAME" style="vertical-align:top;text-align:left">
			'.$rel['product_name'].'
			'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.$rel['description']:'').'
			</td>

			<td data-label="HSN CODE" style="vertical-align:top;" class="text-center">';
			if(empty($rel['product_hsn_code'])){
				$str.='-';
			}else{
				$str.=$rel['product_hsn_code'];
			}
			$str.='</td>
			<td data-label="QTY" style="vertical-align:top;" class="text-center">
			'.$rel['product_conv_qty'].' '.$rel['unit_name'].'
			</td>
			<td  data-label="RATE" style="vertical-align:top;" class="text-center">
			'.$rel['product_rate'].'
			</td>				
			<td  data-label="PER" style="vertical-align:top;display:none" class="text-center">';
			if(empty($rel['unit_name'])){
				$str.='-';
			}else{
				$str.=$rel['unit_name'];
			}
			$str.='</td>
			<td data-label="DISCOUNT" style="vertical-align:top" class="text-right">
			'.$rel['product_discount'].' ('.$rel['discount_per'].'%)
			</td>
			<td data-label="TAXABLE VALUE" style="vertical-align:top" class="text-right">
			'.($rel['product_amount']).'
			</td>
			<td data-label="TAX" style="vertical-align:top" class="text-left">';
			if(empty($rel['formulaid'])){
				$str.='-';
			}else{
				$str.=(empty($rel['tax_name1']) ? " " : $rel['tax_name1'] .' : '. $rel['tax_amount1']).'<br/>';
				$str.=(empty($rel['tax_name2']) ? " " : $rel['tax_name2'] .' : '. $rel['tax_amount2']).'<br/>';
				$str.= (empty($rel['tax_name3']) ? " " : $rel['tax_name3'] .' : '. $rel['tax_amount3']).'<br/>';
			}
			$str.='</td>
			<td data-label="AMOUNT" style="vertical-align:top" class="text-right">
			'.$rel['total'].'
			</td>';
			$i++;
		}
	} else{
		$str.='<tr>
		<td colspan="9" class="text-center">NO DATA FOUND</td>
		</tr>';
	}
	$str.='</table>
	</div>
	</div>
	</div>
	</div>
	</div>';

	$qt_rel['mod_po_comp_div_sec'] = $str;
// 			$qt_rel['load_amount_cust'] = load_amount_cust($dbcon, $qt_rel['l_id']);
	echo json_encode($qt_rel);
}
else if(strtolower($POST['mode']) == "add_pi_apprv_hist") {
     // var_dump($POST);exit;             
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	$qt_qry="select quotation_id, sales_order_id from tbl_proforma_invoice as qt where qt.invoice_id=".$POST['invoice_id'];
	$qt_rel=mysqli_fetch_assoc($dbcon->query($qt_qry));

	$info1['approve_remark']	= $_POST['approve_remark'];
	$info1['approve_status']	= $_POST['approve_status'];
	$info1['quotation_id']		= $qt_rel['quotation_id'];
	$info1['sales_order_id']		= $qt_rel['sales_order_id'];
	$info1['invoice_id']	= $_POST['invoice_id'];
	$info1['user_id']			= $_SESSION['user_id'];
	$info1['company_id']		= $_SESSION['company_id'];
	$inserid=add_record("tbl_quot_po_aprv_log", $info1, $dbcon, $branch_id);

	if($POST['approve_status']=='1'){
		$infoso1['approve_status']	= 0;//Approved
		$querycu="select cust.cust_email,quo.user_id,quo.cust_id from tbl_proforma_invoice as quo
		left join tbl_ledger as cust on cust.l_id=quo.cust_id
		where quo.invoice_id=".$info1['invoice_id'];
		$resultcu=$dbcon->query($querycu);
		$relcu=brp_mysqli_fetch_assoc($resultcu);
		$to_email_id=$relcu['cust_email'];

		$cur_user_id = $relcu['user_id'];
		$cur_user = getUserDetailById($dbcon, $cur_user_id);
		$from_email_id = ($cur_user && $cur_user['common_email_id']) ? $cur_user['common_email_id'] : ADMIN_EMAIL;

		$queryst="select email_sms_id from email_sms_template where email_module_id = 4 and company_id=".$_SESSION['company_id'];

		$resultst=$dbcon->query($queryst);
		$relst=brp_mysqli_fetch_assoc($resultst);

		$mail_template = getEmailSMSTemplateById($dbcon, $relst['email_sms_id']);
		$module_id = 4;
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

            // if(!empty($rel1['email_cc'])){
    		// 	$umix=explode(",",$rel1['email_cc']);
    		// 	$umix=array_push($umix,$cur_user_id);
    		// 	$uid=implode(",",null);
    		// }else{
    		// 	$uid=$cur_user_id;
    		// }
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
				$quot_file = umaboy_proformareceipt($dbcon, $POST['invoice_id'],'Yes');
				array_push($attach,$quot_file);
				final_send_email($from_email_id, $to_email_id, $rel11['email_cc'],$rel12['email_bcc'], $subject, $content, $attach);
				unlink('../../../view/upload/mail_attach/'.$quot_file);
			}
		}
	}
	else{
		$infoso1['approve_status']	= 3;//Payment Pending
	}
	$updateid=update_record('tbl_proforma_invoice', $infoso1,"invoice_id=".$POST['invoice_id'] , $dbcon);
}else if(strtolower($POST['mode'])== "get_invoice_total_tax")
{
	$invoice_id=$POST['invoice_id'];

	$resp='';
	if(!empty($invoice_id)){
		$query="SELECT sum(cgst_tax_rate) as cgst_rate,sum(sgst_tax_rate) as sgst_rate,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_proforma_trn` where invoice_id ='$invoice_id' and trancation_status!=2";
	}else{
		$query="SELECT sum(cgst_tax_rate) as cgst_rate,sum(sgst_tax_rate) as sgst_rate,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_proforma_trn` where trancation_status = 3 and user_id=".$_SESSION['user_id'];
	}

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
			<label class="col-md-5 control-label">'.$billsundry['l_name'].' <span class="currency_icon"></span></label>
			<div class="col-md-5 col-xs-11">
			<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.(($POST['currency_id']==$company_state['currency_id']) ? round($gstValue+$addontax,2) : round($gstValue_conv+$addontax,2)).'" placeholder="'.$billsundry['l_name'].'" readonly >
			</div>
			</div>';


		}
		if(($rs_prel['igst_rate'] != 0) && $billsundry['l_name'] == 'IGST'){
			if(!empty($POST['addontax1'])){
				$addontax = $POST['addontax1'];
			}
			$resp.='<div class="form-group">
			<label class="col-md-5 control-label">'.$billsundry['l_name'].' <span class="currency_icon"></span></label>
			<div class="col-md-5 col-xs-11">
			<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.(($POST['currency_id']==$company_state['currency_id']) ? ($rs_prel['igst_rate']+$addontax) : ($rs_prel['igst_rate_conv']+$addontax)).'" placeholder="'.$billsundry['l_name'].'" readonly >
			</div>
			</div>';
		}

		if(($billsundry['l_name'] == 'TCS') && ($getCompanyConfig['enable_tcs_reporting'] == 1) && ($custLedgerDetails['enable_tcs']==1) && ($POST['gross'] >= $getCompanyConfig['gross_balance_limit'])){
			$row['isTcs']="1";
			$total_tcs_calculate = $rs_prel['product_amount']+$gstValue+$rs_prel['igst_rate'];
			$resp.='<div class="form-group">
			<label class="col-md-5 control-label">'.$billsundry['l_name'].'</label>
			<div class="col-md-5 col-xs-11">
			<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.round((($total_tcs_calculate*$billsundry['tax_value'])/100),2).'" placeholder="'.$billsundry['l_name'].'" readonly >
			<input type="hidden" name="tcs_per" id="tcs_per" value="'.$billsundry['tax_value'].'" >
			</div>
			</div>';
		}
		

	}
	if(!empty($invoice_id)){
		$qry_add = $dbcon->query("select sum((tc.tax_per*trn.product_amount)/100) as add_sum , trn.*,l.l_name,l.l_id,t.tax_cat_id from tbl_proforma_trn as trn left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat 
			left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
			left join tbl_ledger as l on l.l_id=tc.tax_id
			where tc.tax_additional='1' and trn.trancation_id='$invoice_id' and trn.trancation_status!=2 and tc.isdelete='0' group by tc.tax_id 
			");
	}else{
		$qry_add = $dbcon->query("select sum((tc.tax_per*trn.product_amount)/100) as add_sum , trn.*,l.l_name,l.l_id,t.tax_cat_id from tbl_proforma_trn as trn left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat 
			left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
			left join tbl_ledger as l on l.l_id=tc.tax_id
			where tc.tax_additional='1' and trn.user_id='".$_SESSION['user_id']."' and trn.trancation_status=3 and tc.isdelete='0' group by tc.tax_id 
			");
	}
	while($row1=brp_mysqli_fetch_array($qry_add))
	{

			//$tax_rate = ($row1['tax_per']*$row1['product_amount'])/100;
		$resp.='<div class="form-group">
		<label class="col-md-5 control-label">'.$row1['l_name'].'</label>
		<div class="col-md-5 col-xs-11">
		<input id="'.$row1['l_name'].'" name="bill_sundry_tax['.$row1['l_id'].']" type="number" class="form-control gst" title="'.$row1['l_name'].'"  value="'.round($row1['add_sum'],2).'" placeholder="'.$billsundry['l_name'].'" readonly >
		</div>
		</div>';
	}

	$row['resp']=$resp;
	echo json_encode($row);
}

else if(strtolower($POST['mode'])== "get_invoice_total_tax_old")
{
	$invoice_id=$POST['invoice_id'];

	$resp='';
	$query="SELECT sum(cgst_tax_rate) as cgst_rate,sum(sgst_tax_rate) as sgst_rate,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount FROM `tbl_invoicetrn` where invoice_id='$invoice_id' and trancation_status!=2";

	$rs_prel= brp_mysqli_fetch_assoc($dbcon->query($query));

	$row['isTcs']="0";
	$getCompanyConfig = getCompanyConfiguration($dbcon);
	$custLedgerDetails = get_cust_data_arr($dbcon,$POST['cust_id']);		
	$get_bill_sundry = get_bill_sundry_ledger($dbcon,1); 

	foreach ($get_bill_sundry as $billsundry) {
		
		if((($rs_prel['cgst_rate'] != 0) && $billsundry['l_name'] == 'CGST') || (($rs_prel['sgst_rate']!= 0) && $billsundry['l_name'] == 'SGST')){

			$gstValue = ($billsundry['l_name'] == 'CGST') ? $rs_prel['cgst_rate'] : (($billsundry['l_name'] == 'SGST') ? $rs_prel['sgst_rate'] : '');
			$resp.='<div class="form-group">
			<label class="col-md-5 control-label">'.$billsundry['l_name'].'</label>
			<div class="col-md-5 col-xs-11">
			<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.$gstValue.'" placeholder="'.$billsundry['l_name'].'" readonly >
			</div>
			</div>';


		}
		if(($rs_prel['igst_rate'] != 0) && $billsundry['l_name'] == 'IGST'){
			$resp.='<div class="form-group">
			<label class="col-md-5 control-label">'.$billsundry['l_name'].'</label>
			<div class="col-md-5 col-xs-11">
			<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.$rs_prel['igst_rate'].'" placeholder="'.$billsundry['l_name'].'" readonly >
			</div>
			</div>';
		}

		if(($billsundry['l_name'] == 'TCS') && ($getCompanyConfig['enable_tcs_reporting'] == 1) && ($custLedgerDetails['enable_tcs']==1) && ($POST['gross'] > $getCompanyConfig['gross_balance_limit'])){
			$row['isTcs']="1";
			$total_tcs_calculate = $rs_prel['product_amount']+$gstValue+$rs_prel['igst_rate'];
			$resp.='<div class="form-group">
			<label class="col-md-5 control-label">'.$billsundry['l_name'].'</label>
			<div class="col-md-5 col-xs-11">
			<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.round((($total_tcs_calculate*$billsundry['tax_value'])/100),2).'" placeholder="'.$billsundry['l_name'].'" readonly >
			<input type="hidden" name="tcs_per" id="tcs_per" value="'.$billsundry['tax_value'].'" >
			</div>
			</div>';
		}
		

	}

	$row['resp']=$resp;

	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "get_grossbalance")
{
	$arr = get_grossbalance($dbcon,$POST['cust_id']);
	echo $arr;
}
else if(strtolower($POST['mode']) == "update_total") {
	//update total , net total , general books entry at edit time start - dhaval 
	$bill_sundry_tax = array_combine($POST['bill_sundry_tax'],$POST['bill_sundry_tax1']);
	/*var_dump($bill_sundry_tax);*/
	if($POST['invoice_id']>0)
	{		
		if($POST['currency_id']==$_SESSION['currency_id']){		
			$update_invoice['g_total'] 		= $POST['g_total'];
			$update_invoice['g_total_conv'] = $POST['g_total']*$POST['currency_id'];
			$update_invoice['basic_total'] 		= $POST['basic_total'];
			$update_invoice['basic_total_conv'] = $POST['basic_total']*$POST['currency_id'];
		}else{
			$update_invoice['g_total'] 		= $POST['g_total']*$POST['currency_id'];
			$update_invoice['g_total_conv'] = $POST['g_total'];
			$update_invoice['basic_total'] 		= $POST['basic_total']*$POST['currency_id'];
			$update_invoice['basic_total_conv'] = $POST['basic_total'];
		}
		update_record("tbl_proforma_invoice",$update_invoice," invoice_id=".$POST['invoice_id'] ,$dbcon);
		//update bill sundry in bill sundry table and general table 
		foreach ($bill_sundry_tax as $bill_sundry_tax_id => $bill_sundry_tax_amount) {
			if($POST['currency_id']==$_SESSION['currency_id']){
				$info_sundry_tax['sundry_amount']		= $bill_sundry_tax_amount;
				$info_sundry_tax['sundry_amount_conv']	= $bill_sundry_tax_amount*$POST['currency_rate'];
			}else{
				$info_sundry_tax['sundry_amount']		= $bill_sundry_tax_amount*$POST['currency_rate'];
				$info_sundry_tax['sundry_amount_conv']	= $bill_sundry_tax_amount;
			}
			$info_sundry_tax['cdate']			= date("Y-m-d H:i:s");
			$info_sundry_tax['user_id']			= $_SESSION['user_id'];
			$info_sundry_tax['company_id']		= $_SESSION['company_id'];

			update_record("tbl_bill_sundry_transaction",$info_sundry_tax," sundry_ledger_id=".$bill_sundry_tax_id." and sundry_voucher_table='tbl_proforma_invoice' and sundry_voucher_id='".$POST['invoice_id']."'" ,$dbcon);
		}				
	}
}
else if(strtolower($POST['mode'])== "get_tax_details_table")
{
	$invoice_id=$POST['invoice_id'];
	$discount=$POST['discount'];
	$resp='';
	if(!empty($invoice_id)){
		$query="SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_proforma_trn` where invoice_id ='$invoice_id' and trancation_status!=2 group by cgst_tax_per,sgst_tax_per,igst_tax_per";
	}else{
		$query="SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_proforma_trn` where user_id ='".$_SESSION['user_id']."' and trancation_status=3 group by cgst_tax_per,sgst_tax_per,igst_tax_per";
	}

	$rs_prel=$dbcon->query($query);
	if(!empty($invoice_id)){
		$rs_prel_fetch=brp_mysqli_fetch_assoc($dbcon->query("SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_proforma_trn` where invoice_id ='$invoice_id' and trancation_status!=2"));
	}else{
		$rs_prel_fetch=brp_mysqli_fetch_assoc($dbcon->query("SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_proforma_trn` where user_id ='".$_SESSION['user_id']."' and trancation_status=3"));
	}
	$rs_prel_num_rows=mysqli_num_rows($rs_prel);
		// print_r($rs_prel_fetch);exit;
	$resp='';
	$resp .= '<table class="table table-bordered">

	<tr>
	<th class="text-center">#</th>
	<th  class="text-center">Total Tax</th>
	<th  class="text-center">Taxable Amount <span class="currency_icon"></span></th>
	<th  class="text-center">Tax Amount <span class="currency_icon"></span></th>';
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
else if(strtolower($POST['mode'])== "get_bill_sundry_label") {
	$sundry_id = $POST['sundry_id'];

	$row=get_sundry_details($dbcon,$sundry_id);

	echo $row['sundry_amount_of'];

} else if(strtolower($POST['mode'])== "get_bill_sundry_details")
{
	$invoice_id=$POST['invoice_id'];
		//echo '<pre>'; print_r($POST);exit;
	$q = $dbcon -> query("SELECT * from tbl_ledger_bill_sundry where isdelete=0 and sundry_ledger_id=".$POST['sundry_ledger_id']." and company_id = ".$_SESSION['company_id']." ");

	$resp = $q->fetch_assoc();

	if(!empty($resp['sundry_gst'])){
		$q_tax = $dbcon -> query("select tax_gst from tbl_tax_category where tax_cat_id=".$resp['sundry_gst']." ");
		$resp_tax = $q_tax->fetch_assoc();
	}else{
		$resp_tax['tax_gst']=0;
	}

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

	$basic_total = $POST['basic_amount'];
	$netamount = $POST['netamount'];
	$taxableamount = $POST['taxableamount'];
	$discount = $POST['discount'];

	$basic_total = $basic_total - $discount;

	$default_amount = $POST['default_amount'];

	
	if(($resp['apply_gst'] == 2) && (!empty($resp['sundry_gst']))){
		if($resp['sundry_amount_of'] == 2){
			$taxvl = ($resp_tax['tax_gst']*(($basic_total * $default_amount)/100))/100;
		}else{
			$taxvl = ($resp_tax['tax_gst']*$POST['default_amount'])/100;
		}
		$taxgst=$resp_tax['tax_gst'];
	}else{
		$taxvl=0;
		$taxgst=0;
	}
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
		}
	}
	else if($resp['sundry_type'] == 2){
		if($resp['sundry_amount_of'] == 1){
			if($resp['sundry_calculate_on'] == 1){
				$finalNetAmount = $netamount - $default_amount;
				$pervalue =  -$default_amount;
			}else if($resp['sundry_calculate_on'] == 2){
				$finalNetAmount = $basic_total - $default_amount;
				$pervalue =  -$default_amount;
			}else if($resp['sundry_calculate_on'] == 3){
				$finalNetAmount = $basic_total - $default_amount;
				$pervalue =  -$default_amount;
			}
		}else if($resp['sundry_amount_of'] == 2){
			if($resp['sundry_calculate_on'] == 1){
				$finalNetAmount = $netamount - (($netamount * $default_amount)/100);
				$pervalue = -($netamount * $default_amount)/100;
			}else if($resp['sundry_calculate_on'] == 2){
				$finalNetAmount = $basic_total - (($basic_total * $default_amount)/100);
				$pervalue = -($basic_total * $default_amount)/100;
			}else if($resp['sundry_calculate_on'] == 3){
				$finalNetAmount = $basic_total - (($basic_total * $default_amount)/100);
				$pervalue = -($basic_total * $default_amount)/100;
			}
		}
	}
	if($invoice_id>0)
	{
		$info_sundry_addon['sundry_ledger_id']=$POST['sundry_ledger_id'];
		$info_sundry_addon['sundry_voucher_id']=$invoice_id;
		$info_sundry_addon['sundry_voucher_type']=QUOTATION_VOUCHER;
		$info_sundry_addon['sundry_voucher_table']='tbl_proforma_invoice';
		$info_sundry_addon['cdate']	= date("Y-m-d H:i:s");
		$info_sundry_addon['user_id']	= $_SESSION['user_id'];
		$info_sundry_addon['company_id']	= $_SESSION['company_id'];
		$info_sundry_addon['sundry_gst_per']	= $taxgst;
		
		//print_r(array_merge($info_sundry_addon,$curncy_trn));
		if(isset($POST['currency_id'])){
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
	if($resp['sundry_amount_of'] == 1){
		$per_amount_show="";
	}
	else{
		$per_amount_show= '<strong> ('.$default_amount.'%)</strong>';
	}
	echo json_encode($finalNetAmount.','.$pervalue.','.$per_amount_show.','.$invoice_id.','.$taxvl.','.$resp_tax['tax_gst']);
}
else if(strtolower($POST['mode'])== "get_all_bill_sundry")
{
	$invoice_id=$POST['invoice_id'];
	$q=$dbcon->query("select b.*,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id left join tbl_ledger as le on le.l_id=b.sundry_ledger_id where b.sundry_voucher_id='$invoice_id' and b.sundry_voucher_table='tbl_proforma_invoice' and b.isdelete='0' and le.default_sundry='0' ");
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
			$str.='<div class="form-group">
			<label class="col-md-5 control-label">'.$r['l_name'].'</label>
			<div class="col-md-4">
			<input id="sundry_name" name="bill_sundry_addon['.$r['l_id'].']" type="hidden" value="'.$r['sundry_amount'].'">
			<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="'.$r['sundry_amount'].'" readonly placeholder="Amount">
			</div>
			<div class="col-md-3">
			<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
			type="button" value="'.$cnt.'" onclick="removeSundry(\'\',\''.$r['sundry_amount'].'\',this.value,\''.$r['sundry_id'].'\')"><i class="fa fa-times"></i></button>
			</div>
			</div>';
		}else{
			$str.='<div class="form-group">
			<label class="col-md-5 control-label">'.$r['l_name'].'</label>
			<div class="col-md-4">
			<input id="sundry_name" name="bill_sundry_addon['.$r['l_id'].']" type="hidden" value="'.$r['sundry_amount'].'">
			<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="'.$r['sundry_amount'].'" readonly placeholder="Amount">
			<input class="addontax" name="bill_sundry_addon_tax['.$r['l_id'].']" type="hidden" value="'.$r['sundry_gst_amount'].'-'.$r['sundry_gst_per'].'-'.$r['sundry_amount'].'" >
			</div>
			<div class="col-md-3">
			<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
			type="button" value="'.$cnt.'" onclick="removeSundry(\'\',\''.$r['sundry_amount'].'\',this.value,\''.$r['sundry_id'].'\')"><i class="fa fa-times"></i></button>
			</div>
			</div>';
		}

		$cnt++;
			//$str.=$r['sundry_amount'];
	}
	echo $str;
		//echo json_encode($resp);
}
else if(strtolower($POST['mode'])== "remove_sundry"){
	$ledger_id = $POST['ledger_id'];
	$info['isdelete']=1;
	$updateid=update_record('tbl_bill_sundry_transaction', $info,"sundry_id=".$POST['ledger_id'] , $dbcon);
	$info_general['genral_book_status'] = 2;
}else if(strtolower($POST['mode'])== "get_sales_bill_sundry"){
	$performa_invoice_type = $POST['performa_invoice_type'];
	$id = $POST['id'];
	if($performa_invoice_type=='1'){
		$q=$dbcon->query("select b.*,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id left join tbl_ledger as le on le.l_id=b.sundry_ledger_id where b.sundry_voucher_id='$id' and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0' ");
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
				$str.='<div class="form-group">
				<label class="col-md-5 control-label">'.$r['l_name'].'</label>
				<div class="col-md-4">
				<input id="sundry_name" name="bill_sundry_addon['.$r['l_id'].']" type="hidden" value="'.$r['sundry_amount'].'">
				<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="'.$r['sundry_amount'].'" readonly placeholder="Amount">
				</div>
				<div class="col-md-3">
				<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
				type="button" value="'.$cnt.'" onclick="removeSundry(\'\',\''.$r['sundry_amount'].'\',this.value,\''.$r['sundry_id'].'\')"><i class="fa fa-times"></i></button>
				</div>
				</div>';
			}else{
				$str.='<div class="form-group">
				<label class="col-md-5 control-label">'.$r['l_name'].'</label>
				<div class="col-md-4">
				<input id="sundry_name" name="bill_sundry_addon['.$r['l_id'].']" type="hidden" value="'.$r['sundry_amount'].'">
				<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="'.$r['sundry_amount'].'" readonly placeholder="Amount">
				<input class="addontax" name="bill_sundry_addon_tax['.$r['l_id'].']" type="hidden" value="'.$r['sundry_gst_amount'].'-'.$r['sundry_gst_per'].'-'.$r['sundry_amount'].'" >
				</div>
				<div class="col-md-3">
				<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
				type="button" value="'.$cnt.'" onclick="removeSundry(\'\',\''.$r['sundry_amount'].'\',this.value,\''.$r['sundry_id'].'\')"><i class="fa fa-times"></i></button>
				</div>
				</div>';
			}
			$cnt++;
			//$str.=$r['sundry_amount'];
		}
	}else{
		$q=$dbcon->query("select b.*,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id left join tbl_ledger as le on le.l_id=b.sundry_ledger_id where b.sundry_voucher_id='$id' and b.sundry_voucher_table='tbl_sales_order' and b.isdelete='0' and le.default_sundry='0' ");
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
				$str.='<div class="form-group">
				<label class="col-md-5 control-label">'.$r['l_name'].'</label>
				<div class="col-md-4">
				<input id="sundry_name" name="bill_sundry_addon['.$r['l_id'].']" type="hidden" value="'.$r['sundry_amount'].'">
				<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="'.$r['sundry_amount'].'" readonly placeholder="Amount">
				</div>
				<div class="col-md-3">
				<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
				type="button" value="'.$cnt.'" onclick="removeSundry(\'\',\''.$r['sundry_amount'].'\',this.value,\''.$r['sundry_id'].'\')"><i class="fa fa-times"></i></button>
				</div>
				</div>';
			}else{
				$str.='<div class="form-group">
				<label class="col-md-5 control-label">'.$r['l_name'].'</label>
				<div class="col-md-4">
				<input id="sundry_name" name="bill_sundry_addon['.$r['l_id'].']" type="hidden" value="'.$r['sundry_amount'].'">
				<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="'.$r['sundry_amount'].'" readonly placeholder="Amount">
				<input class="addontax" name="bill_sundry_addon_tax['.$r['l_id'].']" type="hidden" value="'.$r['sundry_gst_amount'].'-'.$r['sundry_gst_per'].'-'.$r['sundry_amount'].'" >
				</div>
				<div class="col-md-3">
				<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
				type="button" value="'.$cnt.'" onclick="removeSundry(\'\',\''.$r['sundry_amount'].'\',this.value,\''.$r['sundry_id'].'\')"><i class="fa fa-times"></i></button>
				</div>
				</div>';
			}
			$cnt++;
			//$str.=$r['sundry_amount'];
		}
	}
	echo $str;
}else if(strtolower($POST['mode'])== "accessories_model_open")
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
}else if(strtolower($POST['mode'])== "fetch_accessories_qty")
{
	$appData = array();
	$i=1;
	$aColumns = array('tpm.product_name','tiat.inq_acc_id','tiat.product_id','tiat.pid','tiat.qty','tiat.acce_rate','tiat.acc_amount','tiat.product_desc');
	$sTable = "tbl_proforma_access_trn as tiat";			
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
	$table='tbl_proforma_access_trn';$tableid='inq_acc_id';
	
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
else if(strtolower($POST['mode'])== "preedit_accessories_product")
{
	$q = $dbcon -> query("SELECT tpap.*,pm.product_name FROM tbl_proforma_access_trn as tpap left join product_mst as pm on pm.product_id=tpap.product_id WHERE inq_acc_id= '$POST[id]'");
	$r = $q->fetch_assoc();
	echo json_encode($r);
}

else if(strtolower($POST['mode'])== "delete_data_alternative_product_pop")
{
	$deleteid=delete_record('tbl_proforma_access_trn', "inq_acc_id=".$POST['eid']. " and inq_access_status = 3 and user_id='".$_SESSION['user_id']."' and company_id='".$_SESSION['company_id']."'", $dbcon);
	

	if($deleteid)
		$row['res']="1";
	else
		$row['res']="0";
	echo json_encode($row);
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
else if(brp_strtolower($POST['mode']=='get_terms_detail')){
	$query = 'select * from tbl_terms_condition where tc_id='.$POST['tc_id'];
	$result  = $dbcon->query($query);
	$row = brp_mysqli_fetch_array($result);
	
	if(empty($row['tc_details'])){
		$row['tc_details']='';
	}
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "load_product_unit")
{
	$query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name FROM product_mst as promst
		left join unit_mst as umst on umst.unitid=promst.product_base_unit
		left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
		WHERE product_id=".$POST['product_id'];
	//var_dump($POST);
	$rs_type1=$dbcon->query($query1);
	$row1=brp_mysqli_fetch_assoc($rs_type1);
		$rate_unit = "";
		if($POST['rate_unit']){
			$rate_unit = $POST['rate_unit'];
		}
		if($row1['product_base_unit']!=$row1['product_conv_unit']){
			$row1['unit_status']="1";
			$base_sel = "";$conv_sel="";
			if(empty($POST['edit_id'])){
				if($row1['product_base_unit']==$POST['rate_unit']){
    				$base_sel="selected=='selected'";
    			}
    			if($row1['product_conv_unit']==$POST['rate_unit']){
    				$conv_sel="selected=='selected'";
    			}
			}else{
				$query_de = "select * from tbl_purchaseordertrn where purchaseordertrn_id=".$POST['edit_id'];
				$exe = $dbcon->query($query_de);
				$del_ro = brp_mysqli_fetch_array($exe);

				if($row1['product_base_unit']==$del_ro['unit_wise']){
					$base_sel="selected=='selected'";
				}

				if($row1['product_conv_unit']==$del_ro['unit_wise']){
					$conv_sel="selected=='selected'";
				}
			}
			

			$opt='<option '.$base_sel.' value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
			$opt .='<option '.$conv_sel.' value="'.$row1['product_conv_unit'].'">'.$row1['convert_unit_name'].'</option>';
		}else{
			$row1['unit_status']="0";
			$opt.='<option value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
		}
		//echo $opt;
		$row1['unit_option']=$opt;
		//$row1['qye']=$query1;
	//var_dump($row1);
	echo json_encode($row1);
}
else if(strtolower($POST['mode'])== "convert_qty")
{
	//var_dump($POST);
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
	$ret_qty_new=number_format($ret_qty, 4, ".", "");
			//$ret_qty=$ret_qty;
		//	echo $ret_qty;
	$row['show_qty']=$ret_qty_new;
	$row['hide_qty']=$ret_qty;
	echo json_encode($row);
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
else if(strtolower($POST['mode']) == "add_gst_for_all_product"){
		$company_state = get_company_data($dbcon,$_SESSION['company_id']);
			//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
		

		$where  = "  q.trancation_status = 3 and q.invoice_id = 0";

		if(!empty($POST['edit_id'])){
			$where  = "  q.trancation_status = 0 and q.invoice_id = " . $POST['edit_id'];
		}

		 $query = "SELECT q.*,pro.product_hsn FROM tbl_proforma_trn as q left join product_mst as pro ON pro.product_id = q.product_id where " . $where;
		$result = $dbcon->query($query);

		while($row = brp_mysqli_fetch_assoc($result)){
			$sale_gst = get_tax_cat_by_hsn($dbcon,$POST['product_hsn']);
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
		   	}else if($POST['gst_type']==8){
		   		$sale_gst['tax_gst']=24;
		   		$sale_gst['tax_cat_id']=0;
		   	}else{
		   		$sale_gst = get_tax_cat_by_hsn($dbcon,trim($row['product_hsn'])); 
		   	}

			$cgst_tax_rate=0;$cgst_tax_rate_conv=0;
			$sgst_tax_rate=0;$sgst_tax_rate_conv=0;
			$igst_tax_rate=0;$igst_tax_rate_conv=0;
			if(($company_state['stateid'] == $POST['cust_stateid'])){
				$gst = $sale_gst['tax_gst']/2;
				$cgst_tax_per = $gst;
				$cgst_tax_rate = ($gst*$row['product_amount'])/100;
				$cgst_tax_rate_conv = ($row['currency_rate'] *$gst*$row['product_amount'])/100;
				$sgst_tax_per = $gst;
				$sgst_tax_rate = ($gst*$row['product_amount'])/100;
				$sgst_tax_rate_conv = ($row['currency_rate'] *$gst*$row['product_amount'])/100;
			}else{
				$igst_tax_per = $sale_gst['tax_gst'];
				$igst_tax_rate = ($sale_gst['tax_gst']*$row['product_amount'])/100;
				$igst_tax_rate_conv = ($row['currency_rate'] *$sale_gst['tax_gst']*$row['product_amount'])/100;
			}

			$info=get_product_common_tax($dbcon,$row['product_amount'],$row['formulaid']);

			$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
			$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
			$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0 ;

			if($row['currency_id']==$company_state['currency_id']){
				$info1['product_rate']			= $row['product_rate'];
				$info1['product_discount']		= $row['product_discount'];
				$info1['product_amount']		= $row['product_amount'];
				$info1['cgst_tax_rate']			= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
				$info1['sgst_tax_rate']			= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
				$info1['igst_tax_rate']			= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
				$info1['total']			= $row['product_amount'];

				$info1['product_rate_conv']		= $row['product_rate']*$row['currency_rate'];
				$info1['product_amount_conv']	= $row['product_amount']*$row['currency_rate'];
				$info1['product_discount_conv']	=$row['product_discount']*$row['currency_rate'];
				$info1['cgst_tax_rate_conv']	= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
				$info1['sgst_tax_rate_conv']	= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
				$info1['igst_tax_rate_conv']	= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;
				$info1['total_conv']	= $row['product_amount']*$row['currency_rate'];
			}else{
				$info1['product_rate']			= $row['product_rate']*$row['currency_rate'];
				$info1['product_discount']		= $row['product_discount']*$row['currency_rate'];;
				$info1['product_amount']		= $row['product_amount']*$row['currency_rate'];
				$info1['cgst_tax_rate']			= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
				$info1['sgst_tax_rate']			= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
				$info1['igst_tax_rate']			= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;
				$info1['total']			= $row['product_amount']*$row['currency_rate'];

				$info1['product_rate_conv']		= $row['product_rate'];
				$info1['product_amount_conv']	= $row['product_amount'];
				$info1['product_discount_conv']	= $row['product_discount'];
				$info1['cgst_tax_rate_conv']	= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
				$info1['sgst_tax_rate_conv']	= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
				$info1['igst_tax_rate_conv']	= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
				$info1['total_conv']	= $row['product_amount'];
			}

			//var_dump($info1);

			$updateid=update_record('tbl_proforma_trn', $info1,"trancation_id=".$row['trancation_id'] , $dbcon, $branch_id);
			if(!empty($POST['edit_id'])){
				$info['quot_type'] = $POST['quot_type'];
				$updateid=update_record('tbl_proforma_invoice', $info,"invoice_id=".$POST['edit_id'], $dbcon);
			}
		}	
	}
else if(strtolower($POST['mode']) == "add_field_list") 
{
	$pid= $POST['pid']; 
		 
	$inq_qry="select * from tbl_proforma_trn  where  trancation_id=".$pid;
		
	$inq_qry_rs=$dbcon->query($inq_qry);

	$inq_rel=brp_mysqli_fetch_array($inq_qry_rs);
		
	$inq_unit="select product_base_unit,product_spec,product_spec_id,product_hsn,hsn.hsn_code from product_mst as pro 
	left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
	where  product_id=".$POST['product_id'];
		
	$inq_unit_rs=$dbcon->query($inq_unit);

	$inq_rel_unit=brp_mysqli_fetch_array($inq_unit_rs);
		
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$company_state = get_company_data($dbcon,$_SESSION['company_id']);
			//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
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
   	}else if($POST['gst_type']==8){
   		$sale_gst['tax_gst']=24;
   		$sale_gst['tax_cat_id']=0;
   	}else{
		$sale_gst = get_tax_cat_by_hsn($dbcon,$inq_rel_unit['hsn_code']);
	}

	$cgst_tax_rate=0;$cgst_tax_rate_conv=0;
	$sgst_tax_rate=0;$sgst_tax_rate_conv=0;
	$igst_tax_rate=0;$igst_tax_rate_conv=0;
	if(($company_state['stateid'] == $POST['cust_stateid'])){
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

	$info1['product_id']		= $POST['product_id'];
	$info1['description']		= stripcslashes(str_replace(array("\n", "\r", "\N", "\R"), '', $POST['product_desc']));//stripcslashes(text_rnremove($_POST['product_des']));
	$info1['product_hsn_code']	= $inq_rel_unit['hsn_code'];
	$info1['product_qty']		= $POST['product_qty'];
	$info1['product_disc']		= stripcslashes(str_replace(array("\n", "\r", "\N"), '', $POST['product_desc']));
	//$info1['product_spec']		= stripcslashes(str_replace(array("\n", "\r", "\N"), '', $POST['product_spec']));
	$info1['unit_id']			= $inq_rel_unit['product_base_unit'];
	//$info1['discount_per']		= $POST['discount_per'];
	//$info1['formulaid']			= $POST['formulaid'];
	$info1['currency_id']		= $inq_rel['currency_id'];
	$info1['currency_rate']		= $inq_rel['currency_rate']; 
	$info1['pid']				= $pid;
	
	//$info=get_product_tax($dbcon,$total,$POST['formulaid']);
	//$info1=array_merge($info1,$info);
	$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
	$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
	$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0 ;
	$info1['product_tax_cat']	= $sale_gst['tax_cat_id'];
	if($POST['currency_id']==$company_state['currency_id']){
		$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
		$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
		$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
		$info1['product_rate']		= $POST['product_rate'];
		//$info1['product_discount']	= $POST['product_discount'];
		$info1['product_amount']	= $total=($POST['product_rate']*$POST['product_qty'])-$POST['product_discount'];
		$info1['total']				= $total + $cgst_tax_rate + $sgst_tax_per + $igst_tax_per;

		$info1['cgst_tax_rate_conv']= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
		$info1['sgst_tax_rate_conv']= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
		$info1['igst_tax_rate_conv']= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;
		$info1['product_rate_conv']	= $POST['product_rate'] * $inq_rel['currency_rate'];
		$info1['product_discount_conv']	= $POST['product_discount'] * $inq_rel['currency_rate'];
		$info1['product_amount_conv']	= $total * $inq_rel['currency_rate'];
		$info1['total_conv']		= $info1['product_amount_conv'] + $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv;
	}else{
		$info1['cgst_tax_rate']		= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0;
		$info1['sgst_tax_rate']		= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0;
		$info1['igst_tax_rate']		= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0;
		$info1['product_rate']		= $POST['product_rate'] * $inq_rel['currency_rate'];
		//$info1['product_discount']	= $POST['product_discount'] * $inq_rel['currency_rate'];
		$info1['product_amount']	= $total_c=($POST['product_rate']*$POST['product_qty'] * $inq_rel['currency_rate'])-($POST['product_discount'] * $inq_rel['currency_rate']);
		$info1['total']				= $total_c + $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv;

		$info1['cgst_tax_rate_conv']= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
		$info1['sgst_tax_rate_conv']= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
		$info1['igst_tax_rate_conv']= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
		$info1['product_rate_conv']	= $POST['product_rate'];
		$info1['product_discount_conv']	= $POST['product_discount'];
		$info1['product_amount_conv']	= $total=($POST['product_rate']*$POST['product_qty'])-$POST['product_discount'];
		$info1['total_conv']		= $total + $cgst_tax_rate + $sgst_tax_per + $igst_tax_per;
	}
	//$table='tbl_proforma_trntemp';$tableid='tempinvoicetrn_id';
// var_dump($sale_gst);die();
	$table='tbl_proforma_trn';
	$tableid='trancation_id';
	if(!empty($POST['invoice_id']))
	{
		$info1['invoice_id']= $POST['invoice_id'];
	}
	else
	{
		$info1['trancation_status']	= 3;
	}
	$info1['user_id']	= $_SESSION['user_id'];

	$inserid=add_record($table, $info1, $dbcon);

	if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
		$cl_id = get_ledger_by_name($dbcon,'CGST');
		$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid,"tbl_proforma_trn",$POST['product_id'],3,$inserid,$_SESSION['branch_id'],$inq_rel['currency_id'],$inq_rel['currency_rate'],$cgst_tax_rate_conv);
	}
	if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
		$cl_id = get_ledger_by_name($dbcon,'SGST');
		$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid,"tbl_proforma_trn",$POST['product_id'],3,$inserid,$_SESSION['branch_id'],$inq_rel['currency_id'],$inq_rel['currency_rate'],$sgst_tax_rate_conv);
	}
	if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
		$cl_id = get_ledger_by_name($dbcon,'IGST');
		$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid,"tbl_proforma_trn",$POST['product_id'],3,$inserid,$_SESSION['branch_id'],$inq_rel['currency_id'],$inq_rel['currency_rate'],$igst_tax_rate_conv);
	}

			// check for the addiotional tax on product Start -- dhaval
	$pro_amt = $POST['product_amount']*$POST['currency_rate'];
	$count_add_tax=get_check_addition_tax($dbcon,$sale_gst['tax_cat_id'],$POST['product_amount'],$inserid,$POST['product_id'],$inserid,$_SESSION['branch_id'],'tbl_proforma_trn',$inq_rel['currency_id'],$inq_rel['currency_rate'],$pro_amt);
} else if(brp_strtolower($POST['mode']) == "get_packing_size") {
	$packing_id = $POST['packing_id'];

	$query = "SELECT size FROM packing_mst WHERE status = 0 AND packing_id = " . $packing_id;
	$result = $dbcon->query($query);
	$cnt = brp_mysqli_num_rows($result);
	$row = brp_mysqli_fetch_assoc($result);
	if($cnt > 0){
			echo $row['size'];
	}else{
		echo "0";
	}
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
function umaboy_proformareceipt($dbcon, $invoiceid, $save_file){
	$type='pdf';
	if(strtolower($type) == 'pdf') {
		$que = "select performa_invoice_type from tbl_proforma_invoice where invoice_id=$invoiceid";
		$rel_que=mysqli_fetch_assoc($dbcon->query($que));

		$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
		WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
		$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
		$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
		$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';

		if($rel_que['performa_invoice_type']=='1'){
			$query="select invoice.*,country.country_name,payterms.payment_terms as payment_trm,state.state_name,cust_a.c_add_state,state.gst_state_code, city.city_name, cust.cust_name as company_name,cust_a.c_add_location as cust_address,cust_a.c_add_street as cust_street,cust_a.c_add_zip as cust_pincode, type.invoice_type,cust_mobile,cust_email,cust.cust_gst as gst_no 
			from tbl_proforma_invoice as invoice 
			left join tbl_customer as cust on cust.cust_id=invoice.cust_id 
			left join tbl_cust_address as cust_a on cust_a.cust_id=invoice.cust_id 
			left join country_mst as country on country.countryid=cust_a.c_add_country 
			left join state_mst as state on state.stateid=cust_a.c_add_state 
			left join city_mst as city on city.cityid=cust_a.c_add_city 
			left join tbl_invoicetype as type on type.invoicetype_id=invoice.invoicetype_id 
			left join pay_terms as payterms on payterms.terms_id=invoice.payment_terms 
			where invoice_id=$invoiceid";
		}else{
			$query="select invoice.*,country.country_name,payterms.payment_terms as payment_trm,state.state_name,cust.stateid,state.gst_state_code, city.city_name, cust.l_name as company_name,cust.m_address as cust_address, type.invoice_type,cust_pincode,cust_mobile,gst_no 
			from tbl_proforma_invoice as invoice 
			left join tbl_ledger as cust on cust.l_id=invoice.cust_id 
			left join country_mst as country on country.countryid=cust.countryid 
			left join state_mst as state on state.stateid=cust.stateid 
			left join city_mst as city on city.cityid=cust.cityid 
			left join tbl_invoicetype as type on type.invoicetype_id=invoice.invoicetype_id 
			left join pay_terms as payterms on payterms.terms_id=invoice.payment_terms 
			where invoice_id=$invoiceid";
		} 
		$rel=mysqli_fetch_assoc($dbcon->query($query));
 // echo $query;die();

		$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
//CHEMITEK PROCESS EQUIPMENTS PRIVATE LIMITED       
//>Plot No : 117 , Nr. GETCO Sub -Station , Old GIDC , Gundlav Valsad - 396035, Gujarat, India
		$comp_rel=mysqli_fetch_assoc($dbcon->query($set));

		$rel['invoice_type'] = 'PROFORMA INVOICE';  
		if(!$rel){
			header("Location: ".ROOT."proforma_list");
		}

		$cons_gst_no=$rel['gst_no'];
		$cons_pan_no=$rel['pan_no'];
		$cons_state_name=$rel['state_name'];
		$cons_gst_state_code=$rel['gst_state_code'];
		$place_of_supply=$rel['city_name'];
		$order_no = ($rel['order_no']!='0')?$rel['order_no']:'';
if(!empty($rel['consignee_id']))//consignee
{ 
	if($rel['performa_invoice_type']=='1'){
		$table_name = 'tbl_party_consignee';
	}else{
		$table_name = 'tbl_custmer_consignee';
	}
	$consignee="select * from $table_name as cust 
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid 
	left join city_mst as city on city.cityid=cust.cityid where cust_id=".$rel['consignee_id'];
	$cons_data=mysqli_fetch_assoc($dbcon->query($consignee)); 
	$cons_gst_no=($cons_data['gst_no']!='0')?$cons_data['gst_no']:$rel['gst_no'];
	$cons_pan_no=$cons_data['pan_no'];
	$cons_state_name=$cons_data['state_name'];
	$cons_gst_state_code=$cons_data['gst_state_code'];
	$place_of_supply=$cons_data['city_name'];
}

$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));  
//echo "<pre>";print_r($set_head);die();
$order_date='';$lr_date='';$dispatch_date='';
if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
{
	$order_date=date('d/m/Y',strtotime($rel['order_date']));
}
if($rel['dispatch_date']!="1970-01-01 00:00:00" && $rel['dispatch_date']!="0000-00-00 00:00:00")
{
	$dispatch_date=date('d-m-Y h:i a',strtotime($rel['dispatch_date']));
}

if($rel['dispatch_document_date']!="1970-01-01" && $rel['dispatch_document_date']!="0000-00-00")
{
	$dispatch_document_date=date('d-m-Y',strtotime($rel['dispatch_document_date']));
}

if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
{
	$order_date=date('d-m-Y',strtotime($rel['order_date']));
}





/* Check Discount is On or off Start */
if($set_head['show_disc']=='1'){
	$colspan=5;
	$dynamicwidth=40;
}else{
	$colspan=6;
	$dynamicwidth=46;
}
/* Check Discount is On or off End */
/*<img src="'.DOMAIN_F.LOGO.$set_head['logo'].'" style="width:2.27in;padding-top:0px;" />*/
$html='';$address='';$header='';

if($rel['quot_address']!='' && $rel['quot_address']!='0'){ 
	$address.= $rel['quot_address'];
	$address.='<br/>';
}else{ 
	$address.= $rel['cust_address'];
	$address.=' <br/>';
	$address.=$rel['city_name'].','. $rel['state_name'].','. $rel['country_name'];
	if(!empty($rel['cust_pincode'])){
		$address.='-'. $rel['cust_pincode'];
	} 
}

if($rel['quot_address']!='' && $rel['quot_address']!='0'){ 
	$header.= $rel['quot_address'];
	$header.='<br/>';
}else{ 
	$header.= $rel['cust_address'];
	$header.=' <br/>';
	$header.=$rel['city_name'].','. $rel['state_name'].','. $rel['country_name'];
	if(!empty($rel['cust_pincode'])){
		$header.='-'. $rel['cust_pincode'];
	} 
}

if(empty($rel['consignee_id'])){ 
	$header.= $rel['cust_address'];
	$header.='<br/>';
	$header.=$rel['city_name'].','. $rel['state_name'].','. $rel['country_name'];
	if(!empty($rel['cust_pincode'])){
		$header.='-'. $rel['cust_pincode'];
	} 
}else{ 
	$header.= $cons_data['cust_address'];
	$header.=' <br/>';
	$header.=$cons_data['city_name'].','. $cons_data['state_name'].','. $cons_data['country_name'];
	if(!empty($cons_data['cust_pincode'])){
		$header.='-'. $cons_data['cust_pincode'];
	} 
}
$header.=' </td>
</tr>

</table>';

$footer = '<hr>';
/*$header ='
<table>
  <tr>
    <td colspan="3" style="border: 0px; "><img src="'.DOMAIN_F.LOGO.'logo.jpg" style="width:2.27in;padding-top:25px;" /></td>
    <td colspan="6" style="text-align:center;border: 0px;">
    <span style="font-size:16px;"><b>Sales Order Acknowledgement </b></span><br/>
    <span style="font-size:16px;"><b>'.$set_head['company_name'].' </b></span><br/>
    <span style="font-size:12px;">'.$comp_rel["address"].'</span>
    </td>
  </tr>
  </table>';*/
  $header ='
  <table >
  <tr style="border: 0px; ">
  <td  style="border: 0px; "><img src="'.DOMAIN_F.LOGO.$comp_rel['logo'].'" style="width:810px;height:100px;" /></td>

  </tr>
  <tr style="border: 0px; ">
  <td style="text-align:center;font-size:17px;font-weight:600px;"><b>Performa Invoice</b></td>
  </tr>
  </table>';

  $html.='<html>
  <head>          
  <title>Proforma Invoice - '.$rel['invoice_no'].'</title>

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
  	<div style="text-align:center">'.$header.'</div>
  	</htmlpageheader>-->
  	<!--<htmlpagefooter name="otherpages_footer" style="display:none">
  	<div style="text-align:center">'.$footer.'</div>
  	</htmlpagefooter>-->
  	<!--<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>-->
  	<div>


  	</div>
  	<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
  	<thead>
  	<tr>    
  	<td rowspan="3" style="width:50%;vertical-align:top;vertical-align:top;"><b>Buyer</b> <br>
  	'.$address.'
  	</td>
  	<td style="width:25%;vertical-align:top;"><b>Invoice No.</b> <br> '.$rel['invoice_no'].' </td>
  	<td style="width:25%;vertical-align:top;"><b>Dated</b> <br> '.date('d/m/Y',strtotime($rel['invoice_date'])).'</td>
  	</tr>
  	<tr>    

  	<td style="vertical-align:top;"><b>Delivery Note</b>  <br> '.$rel['delivery_note'].'</td>
  	<td style="vertical-align:top;"><b>Mode/Terms of Payment</b> <br> '.$rel['payment_terms'].' </td>
  	</tr>
  	<tr>    
  	<td style="vertical-align:top;"><b>Supplier\'s Ref</b> <br> '.$rel['supplier_ref'].'</td>
  	<td style="vertical-align:top;"> <b>Other Reference(s)</b> <br> '.$rel['other_reference'].' </td>
  	</tr>
  	<tr>    
  	<td rowspan="3" style="vertical-align:top;"><b>Delivery Address</b> 
  	<br>
  	'.$address.'
  	</td>
  	<td style="vertical-align:top;"> <b>Buyer\'s Order No</b> <br> '.$rel['order_no'].'</td>
  	<td><b>Dated </b> <br> '.$order_date.' </td>
  	</tr>
  	<tr>    

  	<td style="vertical-align:top;"> <b>Dispatch Document No.</b> <br> '.$rel['dispatch_document_no'].'  </td>
  	<td style="vertical-align:top;"><b>Dated </b>  <br> '. $dispatch_document_date.'  </td>
  	</tr>
  	<tr>    
  	<td style="vertical-align:top;"> <b>Dispatched through</b> <br> '.$rel['dispatched_through'].'</td>
  	<td style="vertical-align:top;"> <b>Destination </b>  <br> '.$rel['destination'].'</td>
  	</tr>
  	</thead>
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

  	$qry="select trn.*,product.*,unit_name FROM `tbl_proforma_trn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trancation_id order by product_type,trancation_id";
  	$result=$dbcon->query($qry);    
  	$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_packing=0;$final_amount=0;$total_i_gst=0;$gst_per = 0;$gst_rate = 0;
    $total_cs_gst = $totaltaxable = $total_product_amount = $charges_qty1 = $totalsqr = 0;
  	$cnt=mysqli_num_rows($result);
  	while($row=mysqli_fetch_assoc($result))
  	{
  		$gst_per = $row['cgst_tax_per']+$row['sgst_tax_per']+$row['igst_tax_per'];
  		$gst_rate = $row['cgst_tax_rate']+$row['sgst_tax_rate']+$row['igst_tax_rate'];

  		if($row['cgst_tax_rate'] != 0 || $row['sgst_tax_rate'] !=0){
  			$total_cs_gst += $gst_rate;
  		}else{
  			$total_i_gst += $gst_rate;
  		}
        //tax summary calculation start
  		if(!empty($row['tax_val']))
  		{
  			$tax_num=explode(",",$row['tax_val']);
  			$tax_name=explode(",",$row['tax_name']);
  			$total_net_rate=($row['product_qty']*$row['product_rate'])-$row['discount'];
  			for($j=0;$j<count($tax_num);$j++)
  			{
  				if(!in_array($tax_name[$j],$tax['per']))
  				{
  					$tax['per'][]=$tax_name[$j];
  				}
  				$tax['per_total'][$tax_name[$j]]+=$total_net_rate*$tax_num[$j]/100;
  			}
  		}

  		$html.='<tr style="height:35px;border-bottom:none;border-top:none;">
  		<td style="text-align:center; vertical-align:top;">'.$i.'</td>
  		<td style="width:400px;border-right:1px solid;vertical-align:top;" >
  		<strong>'.stripcslashes($row['product_name']).'</strong><br>';
  		$html.= ($row['product_disc']!='' && $row['product_disc']!='0')?nl2br(stripcslashes($row['product_disc'])):'';
  		$html.= '</td>
  		<td style="vertical-align:top; border-right:1px solid; text-align:center" >'.$row['product_qty'].' '.$row['unit_name'].'</td>
  		<td style="vertical-align:top;text-align:center" >'.number_format($row['product_rate'],2,".","").'</td>
  		<td style=" vertical-align:top;text-align:right">'.number_format($row['product_amount'],2,".","").'</td>
  		</tr>';

  		$i++; 
  		$totalqty=$totalqty+$row['product_qty']-$charges_qty;
  		$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
  		$total_product_amount+=($row['product_qty']*$row['product_rate']);
  		$totaltaxable+=$row['product_amount'];
  		$total+=$total_net_rate;
  		$total_packing +=$rel['packing'];

  		$final_amount = $totaltaxable+$total_packing;

  	}
  	$pr=5-$cnt;

  	for($j=0; $j<$pr; $j++){

  /* $html.='<tr style="height:35px;border-bottom:none;border-top:none;">
      <td style=""></td>
      <td style=""></td>
      <td style=""></td>
      <td style=""></td>
      <td style=""></td>
      <td style=""></td>
      </tr>';*/

  }
  $html.='<tr>
  <td colspan="4" style=" text-align:right;">Total Amount  </td>
  <td  style=" text-align:right;">'.indian_number($totaltaxable,2).'</td>
  </tr>';
  if($rel['stateid']==$comp_rel['stateid']){
  	$html.='<tr>
  	<td colspan="4" style=" text-align:right;">CGST '.($gst_per/2).' %</td>
  	<td  style=" text-align:right;">'.number_format(($total_cs_gst/2),2,".","").'</td>
  	</tr><tr>
  	<td colspan="4" style=" text-align:right;">SGST '.($gst_per/2).' %</td>
  	<td  style=" text-align:right;">'.number_format(($total_cs_gst/2),2,".","").'</td>
  	</tr>';
  }else{
  	$html.='<tr>
  	<td colspan="4" style=" text-align:right;">IGST '.($gst_per).' %</td>
  	<td  style=" text-align:right;">'.number_format(($total_i_gst),2,".","").'</td>
  	</tr>';
  }
  $qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_proforma_trn as trn 
  left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
  left join tbl_ledger as l on l.l_id=tc.tax_id 
  where tc.tax_additional='1' and trn.trancation_id=".$invoiceid." and trn.trancation_status!=2 and tc.isdelete='0' group by tc.tax_id";
  $result11=$dbcon->query($qry11);        
  while($row11=mysqli_fetch_assoc($result11))
  {
  	$html.='<tr>
  	<td colspan="4" style=" text-align:right;">'.$row11['l_name'].'</td>
  	<td  style=" text-align:right;">'.number_format($row11['add_sum'],2,".","").'</td>
  	</tr>';
  }
  $qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
  from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
  left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
  where b.sundry_voucher_id=".$invoiceid." and b.sundry_voucher_table='tbl_proforma_invoice' and b.isdelete='0' and le.default_sundry='0'";
  $result12=$dbcon->query($qry12);        
  while($row12=mysqli_fetch_assoc($result12))
  {
  	$html.='<tr>
  	<td colspan="4" style=" text-align:right;">'.$row12['l_name'].'</td>
  	<td  style=" text-align:right;">'.number_format($row12['sundry_amount'],2,".","").'</td>
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
  <tr>
  <td><strong>Terms & Conditions</strong></td>
  </tr>
  <tr>
  <td>'.(($rel['terms_condition']) ? $rel['terms_condition'] : '').'</td>
  </tr>
  </table>
  <table style="page-break-inside: avoid;" >';
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
  $html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
  </body>
  </html>';
  $file_name = $rel['invoice_no'].'.pdf';
// stripcslashes(str_replace(array("\n", "\r", "\N"), '', $POST['product_desc']));
  $file_name=str_ireplace(array("-","/"),"_",$file_name);
  ob_end_clean();
//   include("../../../view/export/mpdf/mpdf.php");
//   $mpdf=new mPDF('','A4','0','calibri','10','10','40','2','1','1');
include("../../vendor/mpdf/mpdf/src/Mpdf.php");
$mpdf = new Mpdf(['format' => 'A4','margin_left' => 10,'margin_right' => 10,'margin_top' => 40,'margin_bottom' => 2,'margin_header' => 1,'margin_footer' => 1,'default_font' => 'calibri']);
		
//    $mdf->SetFont('ProximaNova');
  $mpdf->defaultheaderfontsize = 10; /* in pts */
  $mpdf->defaultheaderfontstyle = 'B'; /* blank, B, I, or BI */
  $mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
  $mpdf->defaultfooterfontsize = 10; /* in pts */
  $mpdf->defaultfooterfontstyle = 'B'; /* blank, B, I, or BI */
  $mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
  $mpdf->SetHTMLHeader($header);
  $mpdf->SetHTMLFooter($footer);
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
?>