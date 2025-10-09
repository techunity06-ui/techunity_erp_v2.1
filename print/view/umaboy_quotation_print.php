<?php 
// var_dump(123);
$quotation_id = $_REQUEST['id'];	
if(!empty($quotation_id)){
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
	include("../../include/function_database_query.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	$incPath = $path.'include/';

	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		QUOTATION_SLUG_PRINT,
	]);

	if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}

	umaboy_quotation_print($dbcon,$quotation_id,$save_file = "No");
}


function umaboy_quotation_print($dbcon,$quotation_id,$save_file){
$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';

$type='pdf';
if(strtolower($type) == 'pdf') {
//Quotation Data
	$query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name from tbl_quotation as quot
	left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
	left join tbl_customer as cust on cust.cust_id=quot.cust_id
	left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
	left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
	where quot.quotation_id=".$quotation_id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));
//p($rel);
	if(!$rel){
		header("Location: ".ROOT.CRM_ROOT."quotation_list");
	}

	if($rel['quot_type']=='0'){
		$currency_name = '(INR)';
		$currency_word_start = 'Rupees';
		$currency_word_end = 'Paise';
	}else{
		$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
		$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

		$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
		$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
		$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
	}

/*if($rel['currency_id'] == '68'){
	$currency_name = '(INR)';
}else{
	$currency_name = '(USD)';
}*/


$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';
//Company Data
/*$comp_qry="select * from tbl_company as comp 
where comp.company_id=".$rel['company_id'];
*/
$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];

$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
$header ='<div style="text-align:right;"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:8.27in;" /></div>';
//$header =$comp_rel["logo"];
//$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';

$approve_status='';
if($rel['approve_status']=='0'){
	$approve_status=' (DRAFT)';
}
$inquiry_type=$rel['inquiry_type'];
//Amish Soni Start 16-03-2021
$companySettings = getCompanySettings($dbcon);
$quotation_print_content = $rel['quot_header'];
if($companySettings) {
	$quotation_print_content = $companySettings['quotation_print_content'] ? $companySettings['quotation_print_content'] : $quotation_print_content;
	$quotation_print_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_print_content);
	$general_terms_condition = $companySettings['general_terms_condition'] ? $companySettings['general_terms_condition'] : $general_terms_condition;
	$general_terms_condition = str_ireplace(array("\r","\n",'\r','\n'),'', $general_terms_condition);
	$battery_limits_and_schedule_exclusion = $companySettings['battery_limits_and_schedule_exclusion'] ? $companySettings['battery_limits_and_schedule_exclusion'] : $general_terms_condition;
	$battery_limits_and_schedule_exclusion = str_ireplace(array("\r","\n",'\r','\n'),'', $battery_limits_and_schedule_exclusion);
}
//Amish Soni End 16-03-2021
$html ='<html>
<head>					
<title>Quotation - '.$rel['quotation_no'].'</title>
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
	<table cellpadding="5" cellspacing="5" style="font-size: 14x; font-family: Proxia Nova; margin-top: 30px;">
	<tr style="border: none;">
	<td colspan="4" style="text-align: center; font-weight: bold; border: none;"><h2>Quotation</h2></td>
	</tr>
	<tr>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">Quotation No. :</td>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">'.$rel['quotation_no'].'</td>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">Date :</td>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">'.date("d-M-Y",strtotime($rel['quotation_date'])).'</td>
	</tr>
	<tr>
	<td colspan="4" style=" text-align: left; font-size: 14px; font-weight: bold;">Customer Details:</td>
	</tr>
	<tr>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">Name :</td>
	<td colspan="3" style=" text-align: left; font-size: 15px;">'.$rel['cust_name'].'</td>
	</tr>
	<tr>
	<td style="font-weight: bold; text-align: left; font-size: 15px; vertical-align: top;">Address :</td>
	<td colspan="3" style=" text-align: left; font-size: 15px;">'.($quot_address).'</td>
	</tr>
	<tr>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">Phone :</td>
	<td style=" text-align: left; font-size: 15px;"></td>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">Mobile No :</td>
	<td style=" text-align: left; font-size: 15px;">'.$rel['cust_mobile'].'</td>
	</tr>
	<tr>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">Email :</td>
	<td colspan="3" style=" text-align: left; font-size: 15px;">'.strtolower($rel['cust_email']).'</td>
	</tr>
	<tr>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">C.C :</td>
	<td colspan="3" style=" text-align: left; font-size: 15px;">'.$userData['user_name'].'</td>
	</tr>
	<tr>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">Kind. Attn :</td>
	<td style=" text-align: left; font-size: 15px;">'.$rel['c_con_fname'].' '.$rel['c_con_lname'].'</td>
	<td style="font-weight: bold; text-align: left; font-size: 15px;">ReF. By :</td>
	<td style=" text-align: left; font-size: 15px;">'.$rel['rb_name'].'</td>
	</tr>
	</table>
	</div>';
	if($inquiry_type==1){
		$trn_qry="select trn.*,pro.product_name,unit.unit_name from tbl_quotation_trn as trn 
		left join product_mst as pro on pro.product_id=trn.product_id
		left join unit_mst as unit on unit.unitid=trn.unitid
		where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
	} else {
		$trn_qry="select trn.*,pro.product_name from tbl_quotation_project_trn as trn left join product_mst as pro on pro.product_id=trn.product_id where trn.quotation_projecttrn_status=0 and trn.quotation_id=".$rel['quotation_id'];
	}
	$trn_qry_rs=$dbcon->query($trn_qry);
	$p=1;$ttl_amt=0;$ttl_qty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;
	$cnt=mysqli_num_rows($trn_qry_rs);
	while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
	    $chkimg = $dbcon->query("SELECT im_name FROM tbl_product_images WHERE im_status = 0 and im_product = ".$trn_rel['product_id']." ORDER BY img_id DESC LIMIT 1");
	    $getimg = mysqli_fetch_assoc($chkimg);
		$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';
		$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
		$gst_rate = $trn_rel['cgst_tax_rate']+$trn_rel['sgst_tax_rate']+$trn_rel['igst_tax_rate'];

		$ttl_amt = $gst_rate + $trn_rel['product_amount'];
		if($trn_rel['cgst_tax_rate'] != 0 || $trn_rel['sgst_tax_rate'] !=0){
			$total_cs_gst += $gst_rate;
		}else{
			$total_i_gst += $gst_rate;
		}

		$html.='<center class="nextpage"></center><div><table style="width: 100%; font-size: 13px; margin-top: 30px;">
		<tr style="border:1px solid;">
		<td style="text-align:center;border:1px solid;vertical-align:top; font-size: 16px; background: #ededed;"><strong>'.$p.') '.$trn_rel['product_name'].'</strong></td>
		</tr>';
		if(!empty($getimg['im_name'])){
			$html.='<tr style="border:none;">
			<td><br><img src="'.DOMAIN_F.PRO_IMG_VWING.$getimg['im_name'].'" /></td>
			</tr>
			<tr style="border:none;">
			<td style="text-align: center;"><br>img for refernce</td>
			</tr>';
		}
		$html.='</table>
		</div>
		<center class="nextpage"></center><div>
		<table style="margin-top: 30px;">
		<tr style="border:1px solid;">
		<td style="text-align:left;border:1px solid;vertical-align:top;background: #ededed;"><strong>TECHNICAL SPECIFICATION</strong></td>
		</tr>
		</table>
		'.$trn_rel['product_spec'].'
		<div>
		<h3 style="text-decoration: underline;"><strong>PRICE FOR THE MACHINE '.$currency_name.' :</strong></h3>
		</div>
		<table style="text-align: right; margin-left: 350px;">
		<tr style="border:1px solid;">
		<td style="border:1px solid;font-weight: bold;">BASIC AMOUNT :</td>
		<td style="border:1px solid;font-weight: bold;"> '.indian_number($trn_rel['product_amount'],2).'</td>
		</tr>';
		if($rel['qt_add_state']==$comp_rel['stateid']){
			$html.='<tr style="border:1px solid;">
			<td style="border:1px solid;font-weight: bold;">CGST ('.($gst_per/2).' %) :</td>
			<td style="border:1px solid;font-weight: bold;">'.indian_number(($trn_rel['cgst_tax_rate']),2).'</td>
			</tr>
			<tr style="border:1px solid;">
			<td style="border:1px solid;font-weight: bold;">SGST ('.($gst_per/2).' %) :</td>
			<td style="border:1px solid;font-weight: bold;">'.indian_number(($trn_rel['sgst_tax_rate']),2).'</td>
			</tr>';
		}else{
			$html.='<tr style="border:1px solid;">
			<td style="border:1px solid;font-weight: bold;">IGST ('.($gst_per).' %) : </td>
			<td style="border:1px solid;font-weight: bold;">'.indian_number($trn_rel['igst_tax_rate'],2).'</td>
			</tr>';
		}
		$html.='<tr style="border:1px solid;">
		<td style="border:1px solid;font-weight: bold;">NET AMOUNT :</td>
		<td style="border:1px solid;font-weight: bold;">'.indian_number($ttl_amt,2).'</td>
		</tr>
		</table>
		</div>
		<center class="nextpage"></center><div>
		<table style="margin-top: 30px;">
		<tr style="border:1px solid;">
		<td style="text-align:left;border:1px solid;vertical-align:top;background: #ededed;"><strong>SILENT FEATURES</strong></td>
		</tr>
		</table>
		'.$product_desc;
		$p++;
	}
	
	$html.='<center class="nextpage"></center><div>
	<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
	<thead>
	<tr>
	<th style="text-align:left; border:1px solid; font-weight: bold; background: #ededed;">GENERAL TERMS & CONDITIONS</th>
	</tr>
	<tr style="border:none">
	<th style="text-align:center; border:none"></th>
	</tr>
	</thead><tbody>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;background: #ededed;">PRICE : </td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">Ex- Works Ahmedabad, Packing, Forwarding, Octroi, Unloading/ Insurance to your Account.</td>
	</tr>
	<tr style="border:none;padding:10px;">
	<td style="border:none;padding:10px;"></td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;background: #ededed;">DELIVERY TIME :</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">To be Agreed upon 6 to 8 weeks on receipt of your techno-commercial clear order at our end.</td>
	</tr>
	<tr style="border:none;padding:10px;">
	<td style="border:none;padding:10px;"></td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;background: #ededed;">PAYMENTS :</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">30 % along with Purchase Order and Balance against Performa Invoice, before Delivery.</td>
	</tr>
	<tr style="border:none;padding:10px;">
	<td style="border:none;padding:10px;"></td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;background: #ededed;">BANK DETAILS :</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">Beneficiary : Shree Umiya F- Tech Machines</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">Bank Name : Yes Bank</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">Branch Name : Maninagar Branch, Ahmedabad</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">Account Number : 021584600002099</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">RTGS/NEFT/IFSC Code : YESB0000215</td>
	</tr>
	<tr style="border:none;padding:10px;">
	<td style="border:none;padding:10px;"></td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;background: #ededed;">INSTALLATION & TRAINING :</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">By Shree Umiya F-Tech Machines<br>(Traveling, Boarding & Loading for Service Engineers to be borne by Customer)</td>
	</tr>
	<tr style="border:none;padding:10px;">
	<td style="border:none;padding:10px;"></td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;background: #ededed;">NOTE :</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">This Offer is Valid for 30 days from the date of offer. Technical Specification may be changed without  any prior notice. Product image are just for the reference.</td>
	</tr>
	<tr style="border:none;padding:10px;">
	<td style="border:none;padding:10px;"></td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;background: #ededed;">JURISDICTION :</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;padding:5px;">Our Quotation is Subject to Jurisdiction Situated within Ahmedabad Municipal Corporation Only.</td>
	</tr>
	<tr style="border:none;padding:10px;">
	<td style="border:none;padding:10px;"></td>
	</tr>
	<tr style="border:none">
	<th style="text-align:left; border:none">Yours Faithfully</th>
	</tr>
	<tr style="border:none; height: 30px;">
	<th style="text-align:left; border:none">For '.$comp_rel['company_name'].'</th>
	</tr>
	</tbody>
	</table>
	</div>';

	$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
// echo $trn_qry;
	// echo $html;exit;
	// $filename = 'umaboy_quotation_print_'.$quotation_id.'.pdf';
		$file_name = $rel['quotation_no'].'.pdf';
		$file_name=str_ireplace("/","_",$file_name);
	ob_end_clean();
	include("../../view/export/mpdf/mpdf.php");
	$mpdf=new mPDF('','A4','0','proximanova','10','10','40','10','1','1');
//		$mdf->SetFont('ProximaNova');
	$mpdf->defaultheaderfontsize = 10; /* in pts */
	$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
	$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
	$mpdf->defaultfooterfontsize = 10; /* in pts */
	$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
	$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
	$mpdf->SetHTMLHeader($header);
//$mpdf->SetHTMLFooter($footer);
//Show page number : Dimple Panchal (05-Apr-2021)
	$mpdf->pagenumPrefix = ' ';
	$mpdf->pagenumSuffix = ' / ';
	$mpdf->nbpgPrefix = ' ';
	$mpdf->nbpgSuffix = ' pages';
	$mpdf->SetFooter('{PAGENO}{nbpg}');
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