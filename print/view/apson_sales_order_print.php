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
 	 $query="select invoice.*,country.country_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust.cust_pincode,cust.cust_mobile,cust.common_email_id,cust.gst_no,cust.m_pan,per.c_con_fname,per.c_con_lname,state.gst_state_code,td.transportation_name,cust.cust_cont_name, cust.stateid,terms.payment_terms from tbl_sales_order as invoice 
	 left join pay_terms as terms on terms.terms_id=invoice.payment_terms
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
	$delivery_date = '';
	if($rel['delivery_date']!="1970-01-01" && $rel['delivery_date']!="0000-00-00")
	{
		$delivery_date=date('d-m-Y',strtotime($rel['delivery_date']));
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
	".$rel['country_name']."</span><br/>
		Mobile No.: ".$rel['cust_mobile']."<br/>
		Email Id: ".strtolower($rel['common_email_id'])."
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
	}

	$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';

	if($rel['currency_id']=='1'){
		$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`=68 ';
		$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));
		$currency_name = '(INR)';
		$currency_word_start = 'Rupees';
		$currency_word_end = 'Paise';
		$currency_symbol = $currency_rel['currency_symbol'];
	}else{
		$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
		$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

		$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
		$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
		$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
		$currency_symbol = $currency_rel['currency_symbol'];
	}

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
//CHEMITEK PROCESS EQUIPMENTS PRIVATE LIMITED		
//>Plot No : 117 , Nr. GETCO Sub -Station , Old GIDC , Gundlav Valsad - 396035, Gujarat, India
	$comp_rel=mysqli_fetch_assoc($dbcon->query($set));

	$companyConfiguration=getCompanyConfiguration($dbcon);
	$sales_pro_print=explode(",", $companyConfiguration['sales_pro_print']);

	$html='';

	$header .= '<table style="border:none">
           <tr style="border:none">
               <td style="height:30px;border:none"></td>
           </tr>
       </table>';

	if($companyConfiguration['sales_print_letterhead_per']==0){
		$header .= get_header($dbcon,'text-align: center','','70px');
		$footer = '';
	}else{
		$header .= get_header($dbcon,'text-align: center','','70px');
		$footer = '';
	}

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

		
		.quot_annex_content_div table tr,td{
			padding:5px;
		}
		.blueHeading {
			background-color: #a7adb5;
			
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
			<table style="font-size:14px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<tr >
					<td width="100%" class="blueHeading" style="border:1px solid; font-size:16px;text-align:center"><strong>SALES ORDER</strong></td>
				</tr>
			</table>
			<table style="font-size:14px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<tr >
					<td width="50%" colspan="3" class="" style="border-top:1px solid;border-left:1px solid;border-right:1px solid; text-align:left;colspan:3;"><strong><u>SUPPLIER</u></strong></td>
					<td width="50%" colspan="3" class="" style="border-top:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;colspan:3;"><strong><u>BUYER</u></strong></td>
				</tr>
				<tr >
					<td width="50%" colspan="3" class="" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid; text-align:left;colspan:3;"><strong>'.$comp_rel["company_name"].'</strong></td>
					<td width="50%" colspan="3" class="" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;colspan:3;"><strong>'.$rel["company_name"].'</strong></td>
				</tr>
				<tr >
					<td  class="blueHeading" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid; text-align:left;"><strong>PROFORMA<br/> INVOICE<br/> NUMBER</strong></td>
					<td  class="" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;">'.$rel["sales_order_no"].'</td>
					<td  class="blueHeading" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;"><strong>PROFORMA <br/>INVOICE <br/>DATE</strong></td>
					<td  class="" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;">'.$so_date.'</td>
					<td  class="blueHeading" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;"><strong>DELIVERY
					DATE</strong></td>
					<td  class="" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;">'.$delivery_date.'</td>
				</tr>
			</table>
			<table style="font-size:14px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<tr>
					<td class="blueHeading" width="5%" style="border:1px solid;text-align:center;"><strong>SR.No</strong></td>
					<td class="blueHeading" width="54%" style="border:1px solid;text-align:center;"><strong>PRODUCT DESCRIPTION</strong></td>
					<td class="blueHeading" width="10%" style="border:1px solid;text-align:center;"><strong>QTY</strong></td>
					<td class="blueHeading" width="10%" style="border:1px solid;text-align:center;"><strong>UNIT</strong></td>
				</tr>';
				if($rel['inquiry_type'] == 2)
				{
					
					$trn_qry="select *,product.product_name, product.product_alias_name, product.product_icode, dr.drawing_number, hsn.hsn_code as product_hsn_code, product.image_name FROM `tbl_salesorder_project_trn` as trn 
								left join product_mst as product on product.product_id=trn.product_id 
								left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
								left join mst_hsn_code as hsn on hsn.hsn_id=product.product_hsn 
								left join unit_mst as per on per.unitid=product.product_base_unit
								where trn.salesorder_projecttrn_status=0 and trn.sales_order_id=".$invoiceid;
				}
				else
				{
					$trn_qry="select trn.*, per.unit_name, product.product_name, product.product_alias_name, product.product_icode, dr.drawing_number, hsn.hsn_code as product_hsn_code, product.image_name FROM `tbl_sales_ordertrn` as trn 
					left join product_mst as product on product.product_id=trn.product_id
					left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
					left join mst_hsn_code as hsn on hsn.hsn_id=product.product_hsn
					left join unit_mst as per on per.unitid=trn.rate_unit 
					where trn.sales_ordertrn_status=0 and trn.sales_order_id=".$invoiceid;
				}
				$trn_qry_rs=$dbcon->query($trn_qry);
				$p=1;$ttl_amt=0;$ttl_qty=0;$total_cs_gst=0;$total_i_gst=0;$gst_per=0;$gst_rate=0;$total_billsundy_gst=0;
				$cnt=mysqli_num_rows($trn_qry_rs);
				while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
					$product_desc = ($trn_rel['description']!='0') ? nl2br($trn_rel['description']) : '';
					if($trn_rel['rate_unit']==$trn_rel['unit_id']){
						$sqty = $trn_rel['product_qty'];
					}else{
						$sqty = $trn_rel['product_conv_qty'];
					}
					$html.='<tr>
					<td style="border:1px solid;">'.$p.'</td>
					<td style="border:1px solid;">'.$trn_rel['product_name'].' <br/>'.$product_desc.'</td>
					<td style="border:1px solid;">'.without_comma_two_digit_amount($sqty).'</td>
					<td style="border:1px solid;">'.$trn_rel['unit_name'].'</td>
				</tr>';
				$p++;
				$ttl_qty=$ttl_qty+$sqty;
				}
				$html.='<tr>
					<td colspan="2" style="border:1px solid;text-align:right;">Total</td>
					<td style="border:1px solid;">'.$ttl_qty.'</td>
					<td style="border:1px solid;"></td>
					
				</tr>
				<tr>
					<td colspan="4" class="blueHeading" style="border:1px solid;text-align:left;"><strong>SPECIAL REMARKS</strong></td>
				</tr>
				<tr>
					<td colspan="4" style="border:1px solid;text-align:left;height: 80px;">'.$rel['remark'].'</td>
				</tr>
				<tr>
					<td colspan="4" style="border-top:1px solid;border-left:1px solid;border-right:1px solid;text-align:right;height: 80px;vertical-align: top;"><strong>FOR,'.$comp_rel['company_name'].'</strong></td>
				</tr>
				<tr>
					<td colspan="4" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:right;vertical-align: top;"><strong>AUTHORISED SIGNATURE</strong></td>
				</tr>
				';
				$html.='</table>
			';
		


		
		/*Annexure Content Print Strat*/
	/*if(!empty($rel['quot_annex_content'])){
		$html.= '<pagebreak page-break-type="clonebycss" />'.$rel['quot_annex_content'];
	}*/
	/*Annexure Content Print End*/

	$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
// echo $header;
//echo $trn_qry;
	//echo $html;exit;
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
	$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
	ob_clean();
	return 'Order Acceptance'.$rel['sales_order_no'].'.pdf';
}	
?>