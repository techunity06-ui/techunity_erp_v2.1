<?php 
error_reporting(0);
session_start();
include("../../config/config.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
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
	$query="select invoice.*,country.country_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust.cust_pincode,cust.cust_mobile,cust.gst_no,cust.m_pan,per.c_con_fname,per.c_con_lname,state.gst_state_code,td.transportation_name,cust.cust_cont_name,cust.stateid
	from tbl_sales_order as invoice 
	left join tbl_ledger as cust on cust.l_id=invoice.cust_id
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
//echo "<pre>";print_r($rel);die();
	if(!$rel){
		header("Location: ".ROOT.CRM_ROOT."order_acceptance_list");
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
	$quotation_no = '';
	if(!empty($rel['quotation_id'])){
    	$chkquot = $dbcon->query("SELECT quotation_no FROM tbl_quotation WHERE quotation_id = ".$rel['quotation_id']);
    	$getquot = brp_mysqli_fetch_assoc($chkquot);
    	$quotation_no = $getquot['quotation_no'];
	}
$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
$comp_rel=mysqli_fetch_assoc($dbcon->query($set));

$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';

$sel_t = $dbcon->query("select trn.*,t.id,t.transportation_name from tbl_transport_transaction as trn 
        left join transportation_details as t on t.id=trn.transport_id
        where  transport_voucher='".SO_VOUCHER."' and transport_transaction_table_id='$invoiceid'");
$r_t=brp_mysqli_fetch_assoc($sel_t);

$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));

$html='';
$header ='
<table style="width: 100%;">
<tbody>
<tr style="border: none;">
<td style="width: 65%; border: none; vertical-align: top; text-align: left"><img src="'.DOMAIN_F.LOGO.$comp_rel['logo'].'" /></td>
<td style="width: 35%; border: none; text-align: right; font-weight: bold;"><h1>Sales Order Acknowledgement</h1></td>
</tr>
</tbody>
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
	<table><tr style="border-bottom: 0px; ">
	<td colspan="2" style="border: 0px; font-size:14px;">GSTIN : <span style="font-size:13px;">'.$comp_rel["vatno"].'</span></td>
	<td colspan="4" style="border: 0px; font-size:14px;">CIN <span style="font-size:13px;">:</span> </td>
	<td colspan="3" style="border: 0px; font-size:14px; white-space: nowrap;">PAN <span style="font-size:13px;"> : '.$comp_rel["pan_no"].'</span></td>
	</tr>
	<tr style="border-top: 0px; ">
	<td colspan="2" style="border: 0px; font-size:14px;">Phone : <span style="font-size:13px;">'.$comp_rel["contact_no"].'</span></td>
	<td colspan="4" style="border: 0px; font-size:14px;">Email : <span style="font-size:13px;">'.$comp_rel["website"].'</span></td>
	<td colspan="3" style="border: 0px; font-size:14px; white-space: nowrap;">Website :<span style="font-size:13px;">'.$comp_rel["company_website"].'</span></td>
	</tr>
	<tr style="border:none; border-left:1px solid; border-right:1px solid; ">
	<td colspan="4" style=" text-align:left;">
	Sales Order No:<b>'.$rel['sales_order_no'].' </b></td>
	<td colspan="5" style=" text-align:left;">
	Transport Name:'.$r_t['transportation_name'].'</td>
	</tr>
	<tr style="border:none; border-left:1px solid; border-right:1px solid; ">
	<td colspan="4" style=" text-align:left;">
	Sales Order Date:<b>'.$so_date.'</b> </td>
	<td colspan="5" style=" text-align:left;">
	Delivery: '.(($rel['delivery_date']!="1970-01-01" && $rel['delivery_date']!="0000-00-00") ? date('d-m-Y',strtotime($rel['delivery_date'])) : '').'</td>
	</tr>
	<tr style="border:none; border-left:1px solid; border-right:1px solid; ">
	<td colspan="4" style=" text-align:left;">
	Reference:'.$order_by.'</td>
	<td colspan="5" style=" text-align:left;">
	Customer PO No: '.$rel['po_no'].'</td>
	</tr>
	<tr style="border:none; border-left:1px solid; border-right:1px solid; ">
	<td colspan="4" style=" text-align:left;">
	Contact Person:'.$contact_person.'</td>
	<td colspan="5" style=" text-align:left;">
	Customer PO Date:'.$po_date.'</td>
	</tr>
	<tr style="border:none; border-left:1px solid; border-right:1px solid; ">
	<td colspan="4" style=" text-align:left;"></td>
	<td colspan="5" style=" text-align:left;">
	Sales Quote No: '.$quotation_no.'</td>
	</tr>
	<tr style="background-color:#b3b3b3">
	<td colspan="4" style=" text-align:center;"><b>Details of Recipient (Billed to)</b> </td>
	<td colspan="5" style=" text-align:center;"><b>Details of Consignee (Shipped to)</b></td>
	</tr>
	<tr style="border:none; border-left:1px solid; border-right:1px solid;">
	<td colspan="4" style="border-left:1px solid;border-bottom:none; text-align:left;">'.$party_address_billing.' </td>
	<td colspan="5" style="border-bottom:none;  text-align:left;">'.$party_address_con.'</td>
	</tr>
	</table>
	</div>
	<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
	<thead>
	<tr style="background-color:#b3b3b3">
	<td style=" text-align:center;"><b>Sr No.</b> </td>
	<td  style=" text-align:left;"><b>Part Code / Goods Description</b></td>
	<td colspan="2"  style=" text-align:center;"><b>HSN Code</b> </td>
	<td style=" text-align:center;"><b>UOM</b> </td>
	<td style=" text-align:center;"><b>Qty</b> </td>
	<td style=" text-align:center;"><b>Unite Rate '.strtoupper($currency_name).'</b> </td>
	<td style=" text-align:center;"><b>Disc</b></td>
	<td style=" text-align:center;"><b>Amount '.strtoupper($currency_name).'</b> </td>
	</tr>
	</thead>
	<tbody>';
	$trn_qry="select *,cat.cat_name,product.product_type, hsn.hsn_code as product_hsn_code FROM `tbl_sales_ordertrn` as trn 
	left join product_mst as product on product.product_id=trn.product_id 
	left join tbl_category as cat ON cat.cat_id = product.product_category
	left join unit_mst as per on per.unitid=trn.unit_id 
	left join mst_hsn_code as hsn on hsn.hsn_id=product.product_hsn 
	where sales_ordertrn_status=0 and sales_order_id=".$rel['sales_order_id'];

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

		$product_desc = ($trn_rel['description']!='0') ? nl2br($trn_rel['description']) : '';
		$path='view/upload/product_images/';
		$html.='
		<tr>
		<td style="text-align:center;font-size:12px;">'.$p.' </td>
		<td style="border: none;text-align:left;font-size:12px;">
		<table style="width:100%;">
		<tr border="0" style="border-radious: 0px; border: none;">';
		$align="";
		if($trn_rel['product_type']=='8'){
			$align="";
		}
		if($trn_rel['image_name']!=''){
			$html.='<td border="0" style="border-radious: 0px; border: none!important;width:30%;font-size:12px;'.$align.'"><img src="'.ROOT.$path.$trn_rel['image_name'].'" height="50" width="50" class="img-thumbnail" /></td>';
		}
		$html.='<td border="0" style="border-radious: 0px; border: none!important;font-size:12px;'.$align.'">'.$trn_rel['product_name'].'<br/>'.$product_desc.'</td> 
		</tr>
		</table>';
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
		<td colspan="2" style=" text-align:center;font-size:12px;"> '.$trn_rel['product_hsn_code'].'</td>
		<td style=" text-align:center;font-size:12px;">'.$trn_rel['unit_name'].' </td>
		<td style=" text-align:right;font-size:12px;">'.without_comma_two_digit_amount($trn_rel['product_qty']).' </td>
		<td style=" text-align:right;font-size:12px;"> '.without_comma_two_digit_amount($trn_rel['product_rate_conv']).'</td>
		<td style=" text-align:right;font-size:12px;">'.without_comma_two_digit_amount($trn_rel['discount_per']).' % <br>('.without_comma_two_digit_amount($trn_rel['product_discount_conv']).')</td>
		<td style=" text-align:right;font-size:12px;"> '.without_comma_two_digit_amount($trn_rel['product_amount_conv']).'</td>
		</tr>';

		$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
		if($trn_rel['act_amt_flag']!='1'){
		//$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
			$ttl_amt=$ttl_amt+$trn_rel['product_amount_conv'];
		}
		$p++;
	}
	$pr=10-$cnt;

	$html.='
	<tr>
	<td colspan="8" style=" text-align:right;font-size:12px;">Item Total Amount  </td>
	<td  style=" text-align:center;font-size:12px;">'.indian_number($ttl_amt,2).'</td>
	</tr>';
	 $qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id,b.sundry_amount_conv,b.sundry_gst_amount_conv 
    from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
    left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
    where b.sundry_voucher_id=".$invoiceid." and b.sundry_voucher_table='tbl_sales_order' and b.isdelete='0' and le.default_sundry='0'";
    $result12=$dbcon->query($qry12);        
    while($row12=mysqli_fetch_assoc($result12))
    {
    	if($rel['stateid']==$comp_rel['stateid']){
    		$total_cs_gst=$total_cs_gst+$row12['sundry_gst_amount_conv'];
    	}else{
    		$total_i_gst=$total_i_gst+$row12['sundry_gst_amount_conv'];
    	}
    	$html.='<tr>
		<td colspan="8" style=" text-align:right;font-size:12px;">'.$row12['l_name'].'</td>
		<td  style=" text-align:center;font-size:12px;">'.number_format($row12['sundry_amount_conv'],2,".","").'</td>
		</tr>';
    }
	if($rel['stateid']==$comp_rel['stateid']){
		$html.='<tr>
		<td colspan="8" style=" text-align:right;font-size:12px;">CGST</td>
		<td  style=" text-align:center;font-size:12px;">'.number_format(($total_cs_gst/2),2,".","").'</td>
		</tr><tr>
		<td colspan="8" style=" text-align:right;font-size:12px;">SGST</td>
		<td  style=" text-align:center;font-size:12px;">'.number_format(($total_cs_gst/2),2,".","").'</td>
		</tr>';
	}else{
		$html.='<tr>
		<td colspan="8" style=" text-align:right;font-size:12px;">IGST</td>
		<td  style=" text-align:center;font-size:12px;">'.number_format(($total_i_gst),2,".","").'</td>
		</tr>';
	}

	$qry11="select sum((tc.tax_per*trn.product_amount_conv)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_sales_ordertrn as trn 
    left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
    left join tbl_ledger as l on l.l_id=tc.tax_id 
    where tc.tax_additional='1' and trn.sales_order_id=".$invoiceid." and trn.sales_ordertrn_status!=2 and tc.isdelete='0' group by tc.tax_id";
    $result11=$dbcon->query($qry11);        
    while($row11=mysqli_fetch_assoc($result11))
    {
    	$html.='<tr>
		<td colspan="8" style=" text-align:right;font-size:12px;">'.$row11['l_name'].'</td>
		<td  style=" text-align:center;font-size:12px;">'.number_format($row11['add_sum'],2,".","").'</td>
		</tr>';
    }
   
	$round_off = round($rel['g_total_conv'])-$rel['g_total_conv'];
	$html .= '<tr>
	<td colspan="8" style=" text-align:right;font-size:14px;">Basic Amount<br>Round Off  </td>
	<td  style=" text-align:center;font-size:14px;">'.indian_number($rel['g_total_conv'],2).'<br>'.indian_number($round_off,2).'</td>
	</tr>
	<tr>
	<td colspan="8" style=" text-align:right;font-size:14px;background-color:	#b3b3b3">Total Order Value (In Figure) </td>
	<td  style=" text-align:center;font-size:14px;font-weight:bold;background-color:	#b3b3b3">'.indian_number((round($rel['g_total_conv'])),2).' </td>
	</tr>';

	$html.='</tbody></table>';
	$html.='<table style="page-break-inside: avoid;">
	
	<tr style="">
	<td colspan="9" style=" text-align:left;font-size:14px;">
	Total Order Value (In Words): <span style="font-weight:bold;font-size:13px;">'.convert_number_to_words_new(round($rel['g_total_conv']),$rel['currency_id'],$currency_word_end,$currency_word_start).'</span></td>
	</tr>
	<tr style="">
	<td colspan="9" style="border-bottom: 1px;text-align:left;font-size:14px;">
	Remarks :'.(($rel['remark']) ? $rel['remark'] : '').'</td>
	</tr>
	</table>
	</div>';
	$html.='<center class="nextpage"></center>
	<div>
	<table>
	<tr style="">
	<td colspan="2" rowspan="2" style="font-size:12px;width:100px;text-align:center">
	HSN/SAC
	</td>
	<td colspan="2" rowspan="2" style="font-size:12px;width:100px;text-align:center">
	Taxable Value
	</td>';
	if($rel['stateid']==$comp_rel['stateid']){
		$html.='<td colspan="2" style="font-size:13px;text-align:center">SGST
		</td>
		<td colspan="2" style="font-size:13px;text-align:center">CGST
		</td>';
	}else{
		$html.='<td colspan="2" style="font-size:13px;text-align:center">IGST
		</td>';
	}
	$html.='<td colspan="2" rowspan="2" style="font-size:12px;text-align:center;width:100px">
	Total Tax Amount
	</td>
	</tr>';
	if($rel['stateid']==$comp_rel['stateid']){
		$html.='<tr>
		<td style="font-size:12px;text-align:center;">Rate</td>
		<td style="font-size:12px;text-align:center;">Amount</td>
		<td style="font-size:12px;text-align:center;">Rate</td>
		<td style="font-size:12px;text-align:center;">Amount</td>
		</tr>';
	}else{
		$html.='<tr>
		<td style="font-size:12px;text-align:center;">Rate</td>
		<td style="font-size:12px;text-align:center;">Amount</td>
		</tr>';
	}

	$trn_qry_tax="select trn.*, hsn.hsn_code ,product.product_id,product.product_category,sum(trn.product_amount_conv) as product_amount,cat.cat_name FROM `tbl_sales_ordertrn` as trn left join product_mst as product on product.product_id=trn.product_id left join tbl_category as cat ON cat.cat_id = product.product_category left join mst_hsn_code as hsn on hsn.hsn_id=product.product_hsn where sales_ordertrn_status=0 and sales_order_id='".$rel['sales_order_id']."' group by hsn_code";

	$trn_qry_rs_tax=$dbcon->query($trn_qry_tax);

	$total_amount_sum = 0;
	$total_tax_sum = 0;
	while($trn_rel_tax=mysqli_fetch_assoc($trn_qry_rs_tax)){
		
		$item_tax_amount = $trn_rel_tax['cgst_tax_rate_conv']+$trn_rel_tax['sgst_tax_rate_conv']+$trn_rel_tax['igst_tax_rate_conv'];
		$item_gst_per = $trn_rel_tax['cgst_tax_per']+$trn_rel_tax['sgst_tax_per']+$trn_rel_tax['igst_tax_per'];

		$total_amount_sum = $total_amount_sum + $trn_rel_tax['product_amount'];
		$total_tax_sum = $total_tax_sum + $item_tax_amount;

		$html.='<tr style="">
		<td colspan="2" style="text-align:center;font-size:12px;">
		'.$trn_rel_tax['hsn_code'].'
		</td>
		<td colspan="2" style="text-align:right;font-size:12px;">
		'.without_comma_two_digit_amount($trn_rel_tax['product_amount']).'
		</td>';
		$total_taable_amount = 0;

		$k = 1;

		if($rel['stateid']==$comp_rel['stateid']){
			$html.='<td style="text-align:right;font-size:12px;">
			'.$item_gst_per.'
			</td>
			<td style="text-align:right;font-size:12px;">
			'.number_format(($item_tax_amount/2),2,".","").'
			</td>
			<td style="text-align:right;font-size:12px;">
			'.$item_gst_per.'
			</td>
			<td style="text-align:right;font-size:12px;">
			'.number_format(($item_tax_amount/2),2,".","").'
			</td>';
			$k++;

			$total_taxable_amount = $total_taxable_amount+$item_tax_amount;
			$tax_array[$rel15['tx_tax_id']][] = $texAmount;
		}else{
			$html.='<td style="text-align:right;font-size:12px;">
			'.$item_gst_per.'
			</td>
			<td style="text-align:right;font-size:12px;">
			'.number_format(($item_tax_amount),2,".","").'
			</td>';
			$k++;

			$total_taxable_amount = $total_taxable_amount+$item_tax_amount;
			$tax_array[$rel15['tx_tax_id']][] = $texAmount;
		}
		// $total_tax_sum = $total_tax_sum + $total_taxable_amount; 
		$html.='<td colspan="2" style=" text-align:right;font-size:12px;">
		'.without_comma_two_digit_amount($item_tax_amount).'
		</td>
		</tr>';
	}
	
	$html.='<tr style="">
	<td colspan="2" style="text-align:center;font-size:13px;font-weight:bold">
	Total
	</td>
	<td colspan="2" style="text-align:right;font-size:13px;font-weight:bold">
	'.without_comma_two_digit_amount($total_amount_sum).'
	</td>';
	if($rel['stateid']==$comp_rel['stateid']){
		$html.='<td style="text-align:right;font-size:13px;font-weight:bold" colspan="2">
		'.without_comma_two_digit_amount($total_tax_sum/2).'
		</td>
		<td style="text-align:right;font-size:13px;font-weight:bold" colspan="2">
		'.without_comma_two_digit_amount($total_tax_sum/2).'
		</td>
		';
	} else{
		$html.='<td style="text-align:right;font-size:13px;font-weight:bold" colspan="2">
		'.without_comma_two_digit_amount($total_tax_sum).'
		</td>
		';
	}
	$html.='<td colspan="2" style=" text-align:right;font-size:13px;font-weight:bold">
	'.without_comma_two_digit_amount($total_tax_sum).'
	</td>
	</tr>
	</table>';
	$path_sign='view/upload/product_images/';
	$termsqry = $dbcon->query("SELECT strm.*,mst.tc_name FROM tbl_salesorder_terms_trn as strm left join tbl_terms_condition as mst on mst.tc_id=strm.tc_id WHERE strm.quotation_terms_trn_status = 0 AND strm.sales_order_id = ".$rel['sales_order_id']);
	$html.='<table style="page-break-inside: avoid;" >
	<tr style="borer:1px solid;">
	<td colspan="2" style="borer:1px solid; text-align:left;font-size:13px;">
	<span><strong>Company\'s Bank Details : </strong></span>
	<br>
	<span><strong>Bank Name :</strong> '.$comp_rel['bank_name'].'</span>
	<br>
	<span><strong>A/c No :</strong> '.$comp_rel['ac_no'].'</span>
	<br>
	<span><strong>Branch & IFSC Code :</strong> '.$comp_rel['branch_name'].' & '.$comp_rel['ifcs'].'</span>
	</td>
	</tr>
	<tr style="borer:1px solid;">
	<td width="70%" rowspan="3" style="text-align:left; font-size:12px;">';
	if(brp_mysqli_num_rows($termsqry) > 0){
		$tr = 1;
		while($termsrel = brp_mysqli_fetch_assoc($termsqry)){
			$string=(nl2br($termsrel['tc_details']));
			$html.=$tr.'] '.$termsrel['tc_name'].' : '.$string.'<br>';
			$tr++;
		}
	}
	$html.='</td>
	<td width="30%" style=" text-align:right;font-size:12px;font-weight:bold">
	For , '.$comp_rel["company_name"].'</td>
	</tr>
	<tr style="border-bottom: 0px;border-top: 0px; ">
	<td style=" text-align:right;font-size:12px;">
	<img src="'.DOMAIN_F.'view/upload/signature/'.$comp_rel['authorized_signature'].'" style="height: 65px;width: 65px;" class="img-thumbnail"/></td>
	</tr>
	<tr style="border-top: 0px; ">
	<td style=" text-align:right;font-size:12px;">
	Authorized Signature</td>
	</tr>';
	$html.='</table>
	</div>';

	$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
// echo $header;
// echo $html;exit;
	ob_end_clean();
	include("../../view/export/mpdf/mpdf.php");
	$mpdf=new mPDF('','A4','0','calibri','10','10','35','10','1','1');
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
	$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
	ob_clean();
	return 'Order Acceptance'.$rel['sales_order_no'].'.pdf';
}	
?>