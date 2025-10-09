<?php 
session_start();
$sales_order_id=$_REQUEST['id'];
if(!empty($sales_order_id)){
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	ORDER_ACCEPTANCE_SLUG_PRINT,
]);
umaboy_sales_order_print($dbcon,$sales_order_id,$save_file="No");
}

/*if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}*/
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
	include("../../view/export/mpdf/mpdf.php");
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
		$mpdf->Output('../../view/upload/mail_attach/'.$file_name,'f');
	}
	ob_clean();
	return $file_name;
}
}
?>