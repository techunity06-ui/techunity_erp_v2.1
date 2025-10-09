<?php 
session_start();
// var_dump(123);
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");

$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	ORDER_ACCEPTANCE_SLUG_PRINT,
]);

/*if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}*/

$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);	
$type='pdf';
if(strtolower($type) == 'pdf') {
//Quotation Data
	 $query="select invoice.*,country.country_name,cuser.user_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust.cust_pincode,cust.cust_mobile,cust.gst_no,cust.m_pan,per.c_con_fname,per.c_con_lname,state.gst_state_code,td.transportation_name,cust.cust_cont_name, cust.stateid from tbl_sales_order as invoice 
	left join tbl_ledger as cust on cust.l_id=invoice.cust_id
	left join users as cuser on cuser.user_id=invoice.user_id
	left join tbl_cust_contact as per on per.cust_id = invoice.cust_id
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid
	left join city_mst as city on city.cityid=cust.cityid
	left join transportation_details as td on td.id=invoice.transport_id
	where sales_order_id=$invoiceid";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$delivery_type = $rel['delivery_type'];
	$transportation_name = ($rel['transportation_name']!='0')?$rel['transportation_name']:'';
	$po_date = '';
	if($rel['po_date']!="1970-01-01 00:00:00" && $rel['po_date']!="0000-00-00 00:00:00")
	{
		$po_date=date('d-m-Y',strtotime($rel['po_date']));
	}

	$so_date = '';
	if($rel['sales_order_date']!="1970-01-01 00:00:00" && $rel['sales_order_date']!="0000-00-00 00:00:00")
	{
		$so_date=date('d-m-Y',strtotime($rel['sales_order_date']));
	}
	$delivery_date = '';
	if($rel['delivery_date']!="1970-01-01" && $rel['delivery_date']!="0000-00-00")
	{
		$delivery_date=date('d-m-Y',strtotime($rel['delivery_date']));
	}
//echo "<pre>";print_r($rel);die();
	if(!$rel){
		header("Location: ".ROOT.CRM_ROOT."order_acceptance_list");
	}

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

		$party_address_con="
		<strong>".$rel_con['company_name']."</strong>
		<span style='font-weight:normal;'> <br/>
		".$rel_con['cust_address'].",<br/>
		".$rel_con['cust_pincode']."
		".$rel_con['city_name'].",
		".$rel_con['state_name'].",
		".$rel_con['country_name']."</span>
		<br>  State Code : ".$rel_con['gst_state_code']."
		<br>  GSTIN : ".$rel_con['gst_no'];
	}
  
	$chkcontact = $dbcon->query("SELECT * FROM tbl_cust_contact_person WHERE cust_contact_person_status = 0 AND cust_id = ".$rel['cust_id']." ORDER BY cust_contact_person_id DESC LIMIT 1");
	$getcontact = brp_mysqli_fetch_assoc($chkcontact);

	$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$comp_rel=mysqli_fetch_assoc($dbcon->query($set));

	$user = "select * from users where user_id=".$_SESSION['user_id'];
	$user_rel = mysqli_fetch_assoc($dbcon->query($user));

	$companyConfiguration=getCompanyConfiguration($dbcon);
	$sales_pro_print=explode(",", $companyConfiguration['sales_pro_print']);

	 //$user_app = "SELECT approve.*, user.user_name from tbl_quot_po_aprv_log as approve LEFT JOIN users as user ON user.user_id = approve.user_id where approve.approve_status = 1 AND approve.sales_order_id=".$invoiceid." ORDER BY approve.quot_aprv_log_id DESC LIMIT 1";
	 $user_app = "SELECT approve.*, user.user_name from tbl_oa_aprv_log as approve LEFT JOIN users as user ON user.user_id = approve.user_id where approve.approve_status = 1 AND approve.so_id=".$invoiceid." ORDER BY approve.oa_aprv_log_id DESC LIMIT 1";
	$user_rels = mysqli_fetch_assoc($dbcon->query($user_app));

	$disc_qry=$dbcon->query("SELECT SUM(trn.product_discount) as discount from tbl_sales_ordertrn as trn where trn.sales_ordertrn_status=0 and trn.sales_order_id=".$rel['sales_order_id']);
	$disc_qrys = brp_mysqli_fetch_assoc($disc_qry);
	$colspan =($disc_qrys['discount'] > 0) ? 7 : 6;

	$chkquot = $dbcon->query("SELECT quotation_no FROM tbl_quotation as quot WHERE quotation_id = ".$rel['quotation_id']);
	$getquot = brp_mysqli_fetch_assoc($chkquot);
	
	$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
	$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

	$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
	$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
	$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));

	$html='';
	$header ='<img src="'.DOMAIN_F.LOGO.$comp_rel['logo'].'" style="width:100%; padding-top: 25px;" />
	<table>
	<tr style="border:0px solid; ">
	<td colspan="2" style="text-align: center; font-weight: bold;">Sales order Acknowldgement</td>
	</tr>
	<tr style="border-top:1px solid; border-bottom:1px solid; border-left:none ;border-right:none ; ">
	<td style="width: 50%; text-align:left; border-right:1px solid ;"><b>Sales Order No:'.$rel['sales_order_no'].' </b></td>
	<td style="width: 50%; text-align:left;"><b>Sales Order Date:'.$so_date.'</b></td>
	</tr>
	</table>';

	$html.='<html>
	<head>					
	<title>Order Acceptance - '.$rel['sales_order_no'].'</title>

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
			/*border:1px solid #000 !important;
			page-break-inside:avoid;*/
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
		<table>
		<tr style="border-top: 1px solid; border-bottom: none;  border-left:none ; border-right:none ; ">
		<td style="width: 50%; text-align:left; border-top:1px solid; border-right:1px solid ;">Transport Mode:'.$transportation_name.'</td>
		<td style="width: 50%; text-align:left;">';
		if($delivery_type != 'product_wise'){
			$html.='Delivery: '.$delivery_date;
		}
		$html.='</td>
		</tr>
		<tr style="border:none; border-left:none ; border-right:none ; ">
		<td style=" text-align:left; border-right:1px solid ;">Reference:'.$order_by.'</td>
		<td style=" text-align:left;"><b>Customer PO No: '.$rel['po_no'].'</b></td>
		</tr>
		<tr style="border:none; border-left:none ; border-right:none ; ">
		<td style=" text-align:left; border-right:1px solid ;">Sales Quote No: '.(($getquot['quotation_no']) ? $getquot['quotation_no'] : '').'</td>
		<td style=" text-align:left;"><b>Customer PO Date:'.$po_date.'</b></td>
		</tr>
		<tr style="border:none; border-left:none ; border-right:none ; ">
		<td style=" text-align:left; border-right:1px solid ;"></td>
		<td style=" text-align:left;">Project Name : '.$rel['project_name'].'</td>
		</tr>
		<tr style="border:none; border-left:none ; border-right:none ;">
		<td style=" text-align:left; border-right:1px solid ;"><b>Details of Recipient (Billed to)</b> </td>
		<td style=" text-align:left;"><b>Details of Consignee (Shipped to)</b></td>
		</tr>
		<tr style="border:none; border-left:none ; border-right:none ; ">
		<td style="text-align:left; border-right:1px solid ;">'.$party_address_billing.' </td>
		<td style="border-bottom:none;  text-align:left;">'.$party_address_con.'</td>
		</tr>
		<tr style="border:none; border-top:1px solid ; border-left:none ; border-right:none ;" >
		<td style=" text-align:left; border-right:1px solid ;"><strong>Kind Attn. : '.$getcontact['cust_contact_person_name'].'</strong><br>Contact No. : '.$getcontact['cust_contact_person_no'].'<br>Email Id : '.$getcontact['cust_contact_person_email'].'</td>
		<td style=" text-align:left;"></td>
		</tr>
		</table>
		</div>';
		
		/////////////////////////////////////////Transaction Data statt-harshil//////////////////////////////////////////////
		$html.='<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		<thead>
		<tr style="border:none; border-top:1px solid ; border-bottom:1px solid ; border-left:none ; border-right:none ;">
		<td style=" text-align:center; width:5%; "><b>Sr No.</b> </td>
		<td style=" text-align:left; width:38%;"><b>Part Code / Goods Description</b></td>
		<td style=" text-align:center; width:10%;"><b>HSN Code</b> </td>
		<td style=" text-align:center; width:10%;"><b>UOM</b></td>
		<td style=" text-align:center; width:10%;"><b>Qty</b></td>
		<td style=" text-align:center; width:15%;"><b>Unite Rate <br>'.strtoupper($currency_name).'</b> </td>';
		if($disc_qrys['discount'] > 0){
			$html.='<td style=" text-align:center;"><b>Disc<br>'.strtoupper($currency_name).'</b></td>';
		}
		$html.='<td style=" text-align:center;"><b>Amount<br>'.strtoupper($currency_name).'</b> </td>
		</tr>
		</thead>
		<tbody></table>';
		
		$sundrytax=$dbcon->query("select b.* from tbl_bill_sundry_transaction as b where b.sundry_voucher_id=".$invoiceid." and b.sundry_voucher_table='tbl_sales_order' and b.isdelete='0' ");
		$total_sundrytax=0;
		while($sumsundrytax=brp_mysqli_fetch_assoc($sundrytax)){
			$total_sundrytax = $total_sundrytax + $sumsundrytax['sundry_gst_amount_conv'];
		}
		$trn_qry="select trn.*,product.product_name, hsn.hsn_code as product_hsn_code,  product.product_alias_name, per.unit_name FROM `tbl_sales_ordertrn` as trn 
		left join product_mst as product on product.product_id=trn.product_id
		left join mst_hsn_code as hsn on hsn.hsn_id=product.product_hsn
		left join unit_mst as per on per.unitid=trn.unit_id 
		where trn.sales_ordertrn_status=0 and trn.sales_order_id=".$invoiceid;

		$trn_qry_rs=$dbcon->query($trn_qry);
		$p=1;$ttl_amt=0;$ttl_qty=0;$total_cs_gst=0;$total_i_gst=0;$gst_per=0;$gst_rate=0;
		$cnt=mysqli_num_rows($trn_qry_rs);
		while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
			$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
			$gst_rate = $trn_rel['cgst_tax_rate_conv']+$trn_rel['sgst_tax_rate_conv']+$trn_rel['igst_tax_rate_conv'];
			if($trn_rel['cgst_tax_rate_conv'] != 0 || $trn_rel['sgst_tax_rate_conv'] !=0){
				$total_cs_gst += $gst_rate;
			}else{
				$total_i_gst += $gst_rate;
			}
			$alias_name = '';
			if(in_array('alias',$sales_pro_print)){
				$alias_name = " -- (".$trn_rel['product_alias_name'].")";
			}
			$product_desc = ($trn_rel['description']!='0') ? nl2br($trn_rel['description']) : '';
			

			$html.='<table style="font-size:16px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
			<tr style="border:none;  border-left:none ; border-right:none ;">
			<td style="text-align:center;font-size:14px; vertical-align: top;  width:5%;">'.$p.' </td>
			<td style="border: none;text-align:left; vertical-align: top;  width:45%;">'.$trn_rel['product_name'].''.$alias_name.'<br>'.$product_desc;
			if($delivery_type == 'product_wise'){
				$retu_date = "select sdate.*,unit.unit_name from tbl_salesorder_delivery_date as sdate left join unit_mst as unit on unit.unitid=sdate.unit_id where invoice_status=0 and sdate.po_delivery_date_status=0 and sales_ordertrn_id=".$trn_rel['sales_ordertrn_id'];
				$resadate=$dbcon->query($retu_date);

				$html .='<br><table width="40%" style="font-size:13px">
				<tr>
				<td><strong>Delivery Date</strong></td>
				<td><strong>Qty</strong></td>
				<td><strong>Pend. Qty</strong></td>
				</tr>';

				while($rowdate=brp_mysqli_fetch_array($resadate)){		
					$html .='<tr>
					<td>'.date('d-m-Y',strtotime($rowdate['delivery_date'])).'</td>
					<td>'.$rowdate['product_qty'].' '.$rowdate['unit_name'].'</td>
					<td>'.$rowdate['used_qty'].' '.$rowdate['unit_name'].'</td>
					</tr>';		
				}
				$html .='</table>';
			}
			$html .='</td>
			<td style=" text-align:center;font-size:16px; vertical-align: top;  width:12%;"> '.$trn_rel['product_hsn_code'].'</td>
			<td style=" text-align:center;font-size:16px; vertical-align: top;  width:10%;">'.$trn_rel['unit_name'].' </td>
			<td style=" text-align:center;font-size:16px; vertical-align: top;  width:10%;">'.without_comma_two_digit_amount($trn_rel['product_qty']).' </td>
			<td style=" text-align:center;font-size:16px; vertical-align: top;  width:10%;"> '.without_comma_two_digit_amount($trn_rel['product_rate_conv']).'</td>';
			if($disc_qrys['discount'] > 0){
				$html.='<td style=" text-align:center;font-size:16px; vertical-align: top;">'.without_comma_two_digit_amount($trn_rel['discount_per']).' % <br>('.without_comma_two_digit_amount($trn_rel['product_discount_conv']).')</td>';
			}
			$html.='<td style=" text-align:center;font-size:16px; vertical-align: top;  width:15%;"> '.without_comma_two_digit_amount($trn_rel['product_amount_conv']).'</td>
			</tr>';

			$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
			if($trn_rel['act_amt_flag']!='1'){
		//$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
				$ttl_amt=$ttl_amt+$trn_rel['product_amount_conv'];
			}
			$p++;
			$html.='</tbody></table>';
			$html.='<div style= "marging-left :100px; font-size:16px;">'.(($trn_rel['product_spec']!='0') ? $trn_rel['product_spec'] : '').'</div>';
			
		}
		$pr=10-$cnt;

		$html.='<table>
		<tr style="border:none;  border-left:none ; border-right:none ;  border-top:1px solid ;">
		<td colspan="'.($colspan+1).'" style=" text-align:right;font-size:16px; width:92%;">Item Total Amount  </td>
		<td  style=" text-align:center;font-size:16px;">'.indian_number($ttl_amt,2).'</td>
		</tr>';
		$qry11="select sum((tc.tax_per*trn.product_amount_conv)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_sales_ordertrn as trn 
		left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
		left join tbl_ledger as l on l.l_id=tc.tax_id 
		where tc.tax_additional='1' and trn.sales_order_id=".$invoiceid." and trn.sales_ordertrn_status!=2 and tc.isdelete='0' group by tc.tax_id";
		$result11=$dbcon->query($qry11);        
		while($row11=mysqli_fetch_assoc($result11))
		{
			$html.='<tr>
			<td colspan="'.($colspan+1).'" style=" text-align:right;font-size:16px; width:92%;">'.$row11['l_name'].'</td>
			<td  style=" text-align:center;font-size:16px;">'.number_format($row11['add_sum'],2,".","").'</td>
			</tr>';
		}
		$qry12="select b.sundry_amount_conv,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
		from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
		left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
		where b.sundry_voucher_id=".$invoiceid." and b.sundry_voucher_table='tbl_sales_order' and b.isdelete='0' and le.default_sundry='0'";
		$result12=$dbcon->query($qry12);        
		while($row12=mysqli_fetch_assoc($result12))
		{
			$html.='<tr>
			<td colspan="'.($colspan+1).'" style=" text-align:right;font-size:16px;width:92%;">'.$row12['l_name'].'</td>
			<td  style=" text-align:center;font-size:16px;">'.number_format($row12['sundry_amount_conv'],2,".","").'</td>
			</tr>';
		}
		if($rel['stateid']==$comp_rel['stateid']){
			$html.='<tr>
			<td colspan="'.($colspan+1).'" style=" text-align:right;font-size:16px;width:92%;">CGST</td>
			<td  style=" text-align:center;font-size:16px;">'.number_format((($total_sundrytax+$total_cs_gst)/2),2,".","").'</td>
			</tr><tr>
			<td colspan="'.($colspan+1).'" style=" text-align:right;font-size:16px;width:92%;">SGST</td>
			<td  style=" text-align:center;font-size:16px;">'.number_format((($total_sundrytax+$total_cs_gst)/2),2,".","").'</td>
			</tr>';
		}else{
			$html.='<tr>
			<td colspan="'.($colspan+1).'" style=" text-align:right;font-size:16px;width:92%;">IGST</td>
			<td  style=" text-align:center;font-size:16px;">'.number_format((($total_sundrytax+$total_i_gst)),2,".","").'</td>
			</tr>';
		}
		$round_off = round($rel['g_total_conv'])-$rel['g_total_conv'];
		$html .= '<tr>
		<td colspan="'.($colspan+1).'" style=" text-align:right;font-size:16px; width:92%;">Round Off  </td>
		<td  style=" text-align:center;font-size:16px;">'.indian_number($round_off,2).'</td>
		</tr>
		<tr style="border:none;  border-left:none ; border-right:none ;   border-bottom:1px solid ;" >
		<td colspan="'.($colspan+1).'" style=" text-align:right;font-size:16px;width:92%;">Total Order Value (In Figure) </td>
		<td  style=" text-align:center;font-size:16px;font-weight:bold;">'.indian_number((round($rel['g_total_conv'])),2).' </td>
		</tr>
		<tr style="">
		<td colspan="'.($colspan+1).'" style=" text-align:left;font-size:16px;">Total Order Value (In Words): <span style="font-weight:bold;font-size:16px;">'.convert_number_to_words_new(round($rel['g_total_conv']),$rel['currency_id'],$currency_word_end,$currency_word_start).'</span></td>
		</tr>
		<tr style="border:none;  border-left:none ; border-right:none ;   border-bottom:1px solid ;">
		<td colspan="'.($colspan+2).'" style=" text-align:left;font-size:16px;">Total Tax (In Words): <span style="font-weight:bold;font-size:16px;">'.convert_number_to_words_new(($total_cs_gst + $total_i_gst + $total_sundrytax),$rel['currency_id'],$currency_word_end,$currency_word_start).'</span></td>
		</tr>
		
		</table>';
		
		$terms_qry="select qtrm.*,mst.tc_name from tbl_salesorder_terms_trn as qtrm 
		left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
		where qtrm.quotation_terms_trn_status=0 and qtrm.sales_order_id=".$rel['sales_order_id']." order by qtrm.tc_priority";
		$terms_qry_rs=$dbcon->query($terms_qry);
		if(mysqli_num_rows($terms_qry_rs)){
			$html.='<div>
			<table width="100%">
			<thead>
			<tr style="border-bottom: none;">
			<th colspan="3" style ="text-align:left;">Terms & Conditions</th>
			</tr>
			</thead>
			<tbody>';
			$t=1;
			while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
				$string=(nl2br($term_rel['tc_details']));

				$html.='<tr style="border: none; border-left: none; border-right: none; ">
				<td width="5%" style="border: none; text-align:center; vertical-align: top; font-size:16px;">'.$t.'</td>
				<td width="20%" style="border: none; text-align:left; vertical-align: top; font-size:16px;"><strong>'.$term_rel['tc_name'].'</strong></td>
				<td width="75%" style="border: none; text-align:left; font-size:16px;"> : '.$string.'</td>
				</tr>';
				$t++;
			}
			$html.='</tbody></table>';	
		}
		$html.='<table width="100%" style="border-collapse: collapse;overflow:wrap;"><tbody>
		<tr style="border-bottom: none; ">
		<td style="width:25%;text-align:left;border-right: none; border-top: 1px solid; font-size:16px;">GSTIN<br>PAN No.</td>
		<td style="width:25%;text-align:left;border-right: none; border-left: none; border-top: 1px solid; font-size:16px;"> : '.$comp_rel['vatno'].'<br> : '.$comp_rel['pan_no'].'</td>
		<td colspan="2" style="width:50%;text-align:left;vertical-align:top; border: none; border-top: 1px solid; font-size:16px;"><strong>For, '.$comp_rel['company_name'].'</strong></td>
		</tr>
		<tr style="border-top: none;">
		<td colspan="2" style="width:50%;text-align:left; border: none; font-size:16px;"></td>
		<td style="width:25%;text-align:left; vertical-align: top; border: none; font-size:16px;">Prepared By <br> '.$rel['user_name'].'</td>
		<td style="width:25%;text-align:left; vertical-align: top; border: none; font-size:16px;">Approved By <br> '.(($user_rels['user_name']) ? $user_rels['user_name'] : '').'</td>
		</tr>
		</table>';

		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
	 //echo $header;
	 //echo $html;exit;
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','51','10','1','1');
//		$mdf->SetFont('ProximaNova');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		$mpdf->pagenumPrefix = 'Page ';
		$mpdf->pagenumSuffix = ' of ';
		$mpdf->nbpgPrefix = ' ';
		$mpdf->nbpgSuffix = '';
		$mpdf->SetFooter('{PAGENO}{nbpg}');
		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='UTF-8';
		$mpdf->WriteHTML($html);
		$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
		ob_clean();
		return 'Sales order Acknowldgement'.$rel['sales_order_no'].'.pdf';
	}	
?>