<?php 
session_start();
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
	$query="select invoice.*,country.country_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust.cust_pincode,cust.cust_mobile,cust.gst_no,cust.m_pan,per.c_con_fname,per.c_con_lname,state.gst_state_code,td.transportation_name,cust.cust_cont_name, cust.stateid
	from tbl_sales_order as invoice 
	left join tbl_ledger as cust on cust.l_id=invoice.cust_id
	left join tbl_cust_contact as per on per.cust_id = invoice.cust_id
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid
	left join city_mst as city on city.cityid=cust.cityid
	left join transportation_details as td on td.id=invoice.transport_id
	where sales_order_id=$invoiceid";
	$rel=mysqli_fetch_assoc($dbcon->query($query));

	$transportation_name = ($rel['transportation_name']!='0')?$rel['transportation_name']:'';
	$po_date = '';
	if($rel['po_date']!="1970-01-01" && $rel['po_date']!="0000-00-00")
	{
		$po_date=date('d-m-Y',strtotime($rel['po_date']));
	}

	$so_date = '';
	if($rel['sales_order_date']!="1970-01-01" && $rel['sales_order_date']!="0000-00-00")
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
	<br>  GSTIN : ".(($rel['gst_no']) ? $rel['gst_no'] : '');

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
		<br>  GSTIN : ".(($rel_con['gst_no']) ? $rel_con['gst_no'] : '');
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
$header ='<img src="' . DOMAIN_F . LOGO .$comp_rel["logo"].'" style="width:100%;" />';


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
	<div style="text-align:center;">'.$header.'</div><div>&nbsp;</div>
	</htmlpageheader>
	<!--<htmlpagefooter name="otherpages_footer" style="display:none">
	<div style="text-align:center">'.$footer.'</div>
	</htmlpagefooter>-->
	<!--<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>-->
	<div>
	<table style="font-size:14px; border-collapse:collapse; width:100%;" cellpadding="3" cellspacing="3">
	<tr>
	<td colspan="9" style="text-align:center;font-size:16px;"><strong>Sales Order</strong></td>
	</tr>
	<tr>
	<td style="border-right:0px;width:10%"><strong>S.O.No.</strong></td>
	<td style="border-left:0px;width:15%;white-space:nowrap"><strong>: '.$rel['sales_order_no'].'</strong></td>
	<td style="border-right:0px;width:10%"><strong>S.O.Date</strong></td>
	<td style="border-left:0px;width:15%;white-space:nowrap"><strong>: '.$so_date.'</strong></td>
	<td style="border-right:0px;width:10%"><strong>PO No </strong></td>
	<td style="border-left:0px;border-right:0px;width:15%;white-space:nowrap"><strong>: '.$rel['po_no'].'</strong></td>
	<td style="border-right:0px;border-left:0px;width:10%"><strong>Date </strong></td>
	<td style="border-left:0px;width:15%;white-space:nowrap" colspan="2"><strong>: '.$po_date.'</strong></td>
	</tr>
	<tr>
	<td colspan="4"></td>
	<td style="border-right:0px"><strong>Project</strong></td>
	<td style="border-left:0px;border-right:0px;"><strong>:</strong></td>
	<td colspan="3" style="border-left:0px;text-aling:left;"><strong>'.(($rel['project_name']) ? $rel['project_name'] : '').'</strong></td>
	</tr>
	<tr>
	<td colspan="4" style=" text-align:center;"><b>Customer Name & Address</b> </td>
	<td colspan="5" style=" text-align:center;"><b>Consignee Name & Address</b></td>
	</tr>
	<tr style="border:none; border-left:1px solid; border-right:1px solid;">
	<td colspan="4" style="border-left:1px solid;border-bottom:none; text-align:left;">'.$party_address_billing.' </td>
	<td colspan="5" style="border-bottom:none;  text-align:left;">'.$party_address_con.'</td>
	</tr>
	</table>
	</div>
	<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
	<thead>
	<tr>
	<td style=" text-align:center;width:5%"><b>Sr No.</b> </td>
	<td  style=" text-align:left;width:10%"><b>Item Code</b></td>
	<td colspan="2"  style=" text-align:center;width:35%"><b>Description</b> </td>
	<td style=" text-align:center;width:10%"><b>Hsn Code</b> </td>
	<td style=" text-align:center;width:10%"><b>Qty</b> </td>
	<td style=" text-align:center;width:10%"><b>Uom</b> </td>
	<td style=" text-align:center;width:10%"><b>Unit Rate Rs</b> </td>
	<td style=" text-align:center;width:10%"><b>Amount (INR)</b> </td>
	</tr>
	</thead>
	<tbody>';
	$trn_qry="select *,cat.cat_name,product.product_type,product.product_icode,trn.product_qty as trn_qty, hsn.hsn_code FROM `tbl_sales_ordertrn` as trn 
	left join product_mst as product on product.product_id=trn.product_id 
	left join tbl_category as cat ON cat.cat_id = product.product_category
	left join unit_mst as per on per.unitid=trn.rate_unit 
	left join mst_hsn_code as hsn on hsn.hsn_id=product.product_hsn
	where sales_ordertrn_status=0 and sales_order_id=".$rel['sales_order_id'];

	$trn_qry_rs=$dbcon->query($trn_qry);
	$p=1;$ttl_amt=0;$ttl_qty=0;$total_gst=0;$total_i_gst=0;
	$cnt=mysqli_num_rows($trn_qry_rs);
	while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
    //p($trn_rel);
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
		<td style="text-align:center;font-size:12px;">'.$p.' </td>
		<td style="border: none;text-align:left;font-size:12px;">
		'.$trn_rel['product_item_code'].'       
		</td>
		<td colspan="2" style=" text-align:left;font-size:12px;"> 
		<table style="width:100%;">
		<tr border="0" style="border-radious: 0px; border: none;">';
		$align="";
		if($trn_rel['product_type']=='8'){
			$align="text-align:right";
		}
					//if($trn_rel['image_name']!=''){
						//$html.='<td border="0" style="border-radious: 0px; border: none!important;width:30%;font-size:12px;'.$align.'"><img src="'.ROOT.$path.$trn_rel['image_name'].'" height="50" width="50" class="img-thumbnail" /></td>';
					//}
		$html.='<td border="0" style="border-radious: 0px; border: none!important;font-size:12px;'.$align.'">'.$trn_rel['product_name'].'<br/>'.$product_desc.'</td> 
		</tr>
		</table>
		</td>
		<td style=" text-align:center;font-size:12px;">'.$trn_rel['hsn_code'].' </td>
		<td style=" text-align:center;font-size:12px;">'.without_comma_two_digit_amount($trn_rel['trn_qty']).' </td>
		<td style=" text-align:center;font-size:12px;"> '.$trn_rel['unit_name'].'</td>
		<td style=" text-align:center;font-size:12px;">'.without_comma_two_digit_amount($trn_rel['product_rate']).'</td>
		<td style=" text-align:right;font-size:12px;"> '.without_comma_two_digit_amount($trn_rel['product_amount']).'</td>
		</tr>';

		$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
		if($trn_rel['act_amt_flag']!='1'){
		//$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
			$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
		}
		$p++;
	}
	$pr=10-$cnt;
	
	for($i = 1; $i<$pr; $i++){
		$html .='<tr style="border-top:0px;border-bottom:0px;">
		<td style="text-align:center;font-size:12px;height:25px"></td>
		<td style="border: none;text-align:left;font-size:12px;"></td>
		<td colspan="2" style=" text-align:left;font-size:12px;"></td>
		<td style=" text-align:center;font-size:12px;"></td>
		<td style=" text-align:center;font-size:12px;"></td>
		<td style=" text-align:center;font-size:12px;"></td>
		<td style=" text-align:center;font-size:12px;"></td>
		<td style=" text-align:right;font-size:12px;"></td>
		</tr>';
	}
	$html.='
	<!--<tr>
	<td colspan="5" style=" text-align:right;font-size:12px;"></td>
	<td colspan="3" style=" text-align:right;font-size:12px;">Item Total Amount</td>
	<td  style=" text-align:center;font-size:12px;">'.indian_number($ttl_amt,2).'</td>
	</tr>-->
	<tr>
	<td colspan="5" style=" text-align:left;font-size:12px;"><!--<strong><u>Terms & Condition</u></strong>--></td>
	<td colspan="3" style=" text-align:left;font-size:12px;"><strong>Total</strong></td>
	<td  style=" text-align:right;font-size:12px;">'.indian_number($ttl_amt,2).'</td>
	</tr>';
	if($rel['stateid']==$comp_rel['stateid']){
		$html .= '<tr style="border-top:0px !important;border-bottom:0px !important">
		<td colspan="5" rowspan="2" style=" text-align:left;font-size:12px;"></td>
		<td colspan="3" style=" text-align:left;font-size:12px;border-bottom:none!important;border-top:none!important"><strong>CGST ('.($gst_per/2).' %)</strong></td>
		<td  style="text-align:right;font-size:12px;border-right:1px solid !important">'.number_format(($total_cs_gst/2),2,".","").'</td>
		</tr>
		<tr style="border-top:0px !important;border-bottom:0px !important">
		<td colspan="3" style=" text-align:left;font-size:12px;border-bottom:none!important;border-top:none!important"><strong>SGST ('.($gst_per/2).' %)</strong></td>
		<td  style="text-align:right;font-size:12px;border-right:1px solid !important">'.number_format(($total_cs_gst/2),2,".","").'</td>
		</tr>';
	}else{
		$html .= '<tr style="border-top:0px !important;border-bottom:0px !important">
		<td colspan="5" style=" text-align:left;font-size:12px;"></td>
		<td colspan="3" style=" text-align:left;font-size:12px;border-bottom:none!important;border-top:none!important"><strong>IGST ('.($gst_per).' %)</strong></td>
		<td  style="text-align:right;font-size:12px;border-right:1px solid !important">'.number_format(($total_i_gst),2,".","").'</td>
		</tr>';
	}
	$round_off = round($rel['g_total'])-$rel['g_total'];
	$html .= '<tr>
	<td colspan="5" style=" text-align:right;font-size:14px;"><strong></strong></td>
	<td colspan="3" style=" text-align:left;font-size:14px;"><strong>Round Off</strong></td>
	<td  style=" text-align:right;font-size:14px;">'.indian_number($round_off,2).'</td>
	</tr>
	<tr>
	<td colspan="5" style=" text-align:right;font-size:14px;"></td>
	<td colspan="3" style=" text-align:left;font-size:14px;"><strong>Final Total</strong></td>
	<td  style=" text-align:right;font-size:14px;font-weight:bold;">'.indian_number(($rel['g_total']),2).' </td>
	</tr>';

	$html.='</tbody></table>';
	$html.='<table style="page-break-inside: avoid;">
	
	<tr style="">
	<td colspan="9" style=" text-align:left;font-size:14px;">
	Total Order Value (In Words): <span style="font-weight:bold;font-size:13px;">'.convert_number_to_words_new($rel['g_total']).'</span></td>
	</tr>
	<tr style="">
	<td colspan="9" style="border-bottom: 1px;text-align:left;font-size:14px;">
	Remarks :'.(($rel['remark']) ? $rel['remark'] : '').'</td>
	</tr>
	
	</table>';
/*$html.='<table>
	<tr style="">
		<td colspan="2" rowspan="2" style="font-size:13px;width:100px;text-align:center">
			HSN/SAC
		</td>
		<td colspan="2" rowspan="2" style="font-size:13px;width:100px;text-align:center">
			Taxable Value
		</td>';
		foreach($tax as $key => $value){
            if($key){
            	$key = explode(' ', $key);
				$html.='<td colspan="2" style="font-size:13px;text-align:center">
					'.$key[0].'
				</td>';
			}
        }
		$html.='<td colspan="2" rowspan="2" style="font-size:13px;text-align:center;width:100px">
			Total Tax Amount
		</td>
	</tr>
	<tr>';
	foreach($tax as $key => $value){
		if($key){
		$html.='
		<td style="font-size:12px;text-align:center;">Rate</td>
		<td style="font-size:12px;text-align:center;">Amount</td>';
	}}
	$html.='</tr>';

	$trn_qry_tax="select trn.product_hsn_code,product.product_id,product.product_category,sum(trn.product_amount) as product_amount,sum(trn.tax_amount1) as tax_amount1,sum(trn.tax_amount2) as tax_amount2,sum(trn.tax_amount3) as tax_amount3,sum(trn.total) as total, cat.cat_name FROM `tbl_sales_ordertrn` as trn left join product_mst as product on product.product_id=trn.product_id left join tbl_category as cat ON cat.cat_id = product.product_category left join unit_mst as per on per.unitid=trn.unit_id where sales_ordertrn_status=0 and sales_order_id='".$rel['sales_order_id']."' group by product_hsn_code";

	$trn_qry_rs_tax=$dbcon->query($trn_qry_tax);

	$total_amount_sum = 0;
	$total_tax_sum = 0;
	while($trn_rel_tax=mysqli_fetch_assoc($trn_qry_rs_tax)){
		
        $item_tax_amount = $trn_rel_tax['tax_amount1'] + $trn_rel_tax['tax_amount2'] + $trn_rel_tax['tax_amount3'];
        $total_amount_sum = $total_amount_sum + $trn_rel_tax['product_amount'];
        $total_tax_sum = $total_tax_sum + $item_tax_amount;

		$html.='<tr style="">
		<td colspan="2" style="text-align:center;font-size:12px;">
			'.$trn_rel_tax['product_hsn_code'].'
		</td>
		<td colspan="2" style="text-align:right;font-size:12px;">
			'.without_comma_two_digit_amount($trn_rel_tax['product_amount']).'
		</td>';
		$total_taable_amount = 0;

		$k = 1;
		
		foreach($tax as $key => $value){
            if($key){
            	$key = explode(' ', $key);
            	$total_taable_amount = $total_taable_amount+$value;
            	$each_tax_amount = $trn_rel_tax['tax_amount'.$k];
            	
				$html.='<td style="text-align:right;font-size:12px;">
					'.$key[1].'
				</td>
				<td style="text-align:right;font-size:12px;">
					'.without_comma_two_digit_amount($each_tax_amount).'
				</td>';
				$k++;
		}}
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
		foreach($tax as $key => $value){
            if($key){
				$html.='<td style="text-align:right;font-size:13px;font-weight:bold">
				</td>
				<td style="text-align:right;font-size:13px;font-weight:bold">
					'.without_comma_two_digit_amount($value).'
				</td>
				';
			}
		}
		$html.='<td colspan="2" style=" text-align:right;font-size:13px;font-weight:bold">
			'.without_comma_two_digit_amount($total_tax_sum).'
		</td>
	</tr>

</table>';
/*$html.='';*/


$path_sign='view/upload/product_images/';
$html.='<table style="page-break-inside: avoid;" >';
$html.='
<tr style="border-bottom: 0px;">
<td colspan="10" style=" text-align:left;font-size:14px;height:130px">
<!--<span>Company\'s Bank Details : </span>
<br>
<span>Bank Name : HDFC Bank Limited</span>
<br>
<span>A/c No : 50200048926428</span>
<br>
<span>Branch & IFSC Code : Gundlav, Valsad & HDFC0009216</span>-->
</td>
</tr>
<tr style="border-bottom: 0px; border-top:0px">
<td colspan="10" style=" text-align:right;font-size:14px;font-weight:bold">
<!--For , '.$comp_rel["company_name"].'--></td>
</tr>
<tr style="border-bottom: 0px;border-top: 0px; ">
<td colspan="10" style=" text-align:right;font-size:12px;">
<!--<img src="'.ROOT.LOGO.'sign.png" height="50" width="150" class="img-thumbnail" />--></td>
</tr>
<tr style="border-top: 0px; ">
<td colspan="10" style=" text-align:right;font-size:12px;">
Authorized Signature</td>
</tr>';
$html.='</table>';
/* Get Terms And Condition Start */
	$terms_qry="select qtrm.*,mst.tc_name from tbl_salesorder_terms_trn as qtrm 
	left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
	where qtrm.quotation_terms_trn_status=0 and qtrm.sales_order_id=".$rel['sales_order_id']." order by qtrm.tc_priority";
	$terms_qry_rs=$dbcon->query($terms_qry);
	if(mysqli_num_rows($terms_qry_rs)){
		$html.='<center class="nextpage"></center>
		<h3 style="text-align:center;">Terms & Conditions for Sales Order No : <u>'.$rel['sales_order_no'].'</u></h3>
		<div><table width="100%" style="font-size:12px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3"><tbody>';
		$t=1;
		while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
			$string=(nl2br($term_rel['tc_details']));

			$html.='<tr>
			<td width="5%" style="width:5%;text-align:center;border:1px solid;padding:5px;">'.$t.'</td>
			<td width="25%" style="width:25%;text-align:left;border:1px solid;padding:5px;">'.$term_rel['tc_name'].'</td>
			<td width="70%" style="width:70%;text-align:left;border:1px solid #000;padding:5px;">'.$string.'</td>
			</tr>';
			$t++;
		}
		$html.='</tbody></table></div>';	
	}
/* Get Terms And Condition Start */

/*Annexure Content Print Strat*/
	/*if(!empty($rel['quot_annex_content'])){
		$html.= '<pagebreak page-break-type="clonebycss" />'.$rel['quot_annex_content'];
	}*/
	/*Annexure Content Print End*/

	$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
//echo $header;
///echo $html;exit;
	ob_end_clean();
	include("../../view/export/mpdf/mpdf.php");
	$mpdf=new mPDF('','A4','0','calibri','10','10','37','10','1','1');
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
