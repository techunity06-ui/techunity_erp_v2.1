<?php 
require_once '../../vendor/autoload.php';
use Mpdf\Mpdf;
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';

$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	QUOTATION_SLUG_PRINT,
]);

if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}

$quotation_id = $_REQUEST['id'];	
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
$header ='<div style="text-align:right;"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:3.27in;padding-top:25px;" /></div>';
//$header =$comp_rel["logo"];
//$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';

$approve_status='';
if($rel['approve_status']=='0'){
	$approve_status=' (DRAFT)';
}
$inquiry_type=$rel['inquiry_type'];
//Amish Soni Start 16-03-2021
$companySettings = getCompanySettings($dbcon);

$quotation_print_content = '<h3>Subject:-  <u>Budgetary offer for '.$rel['quot_subject'].'</u></h3><br>';

$quotation_print_content .= '<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is with reference to your requirement '.$rel['quot_subject'].'</p>
<br />
<p>We introduce ourselves as <b>ISO 9001:2000</b> certified companies engaged in the manufacturing of all type of packaging machinery for Food and Beverages, Dairy, and have specialization in
commissioning and installation of Turnkey Projects for mineral water since 1992. As desired and discussed with you we are herewith offering our machine with all technical details for your kind perusal.
</p>
<br />
<p>Maruti Machines Pvt. Ltd is a leading name in manufacturing & exporting of packaging machinery we have large no. of satisfied Client Base not only in India but also in major Asian and African countries apart from United Kingdom, USA and GULF.</p>
<br />
<p><u>Maruti Machines Pvt. Ltd</u> Strongly believes in below phenomena.</p>
<br />
<p>1. Quality Product</p>
<p>2. Low Maintenance</p>
<p>3. Easy To Operate</p>
<p>4. Affordable Price</p>
<p>5. Maximum Output</p>

<br />
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; With our constant R & D efforts we have developed totally Economical, Mechanical Maintenance Free Machines, which have so many Technical & Economical benefits.</p>
<br />';

if($companySettings) {
	$quotation_print_content = $companySettings['quotation_print_content'] ? $quotation_print_content : $quotation_print_content;
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
	<!--<htmlpageheader name="otherpages" style="display:none">
	<div style="text-align:center">'.$header.'</div>
	</htmlpageheader>-->
	<!--<htmlpagefooter name="otherpages_footer" style="display:none">
	<div style="text-align:center">'.$footer.'</div>
	</htmlpagefooter>-->
	<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
	<div>
	<table cellpadding="5" cellspacing="5" border="0" style="font-size: 16px; font-family: Proxia Nova; border: 0;">
	<tr style="border: 0;">
	<td style="border: 0; text-align: right; font-size: 15px;">Date: '.date("d-M-Y",strtotime($rel['quotation_date'])).'</td>
	</tr>
	</table>
	<table cellpadding="5" cellspacing="5" border="0" style="font-size: 20px; font-family: Proxia Nova; border: 0;">
	<tr style="border: 0;">
	<td style="border: 0;">
	<p style="float: left; width: 100%;">
	<strong>To,<br />'.$rel['cust_name'].',</strong><br/>
	<strong style="color: #999999;">'.$rel['c_con_fname'].' '.$rel['c_con_lname'].',<br />
	'.($quot_address).'</strong>
	</p>
	<br />
	'.stripslashes($quotation_print_content).'
	<br /><br />
	<p>Regards</p>
	<p>'.$userData['user_name'] .' - '. $userData['usertype_name'].',</p>
	<p>'.$userPhone .''. $userEmail.',</p>
	<p>BRP Software Solutions LLP</p>
	</td>
	</tr>
	</table>
	</div>
	<center class="nextpage"></center>
	<div>
	<table style="font-size:14px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
	<tr>
	<td rowspan="9" style="text-align:left;vertical-align:top;border:1px solid;width:50%;"> 
	<strong>'.$rel['cust_name'].',</strong><br/>
	<strong>'.$rel['c_con_fname'].' '.$rel['c_con_lname'].',<br />
	'.($quot_address).'</strong>
	</td>
	<td style="text-align:left;border:1px solid;width:20%;"> 
	Quotation No
	</td>
	<td style="text-align:left;border:1px solid;width:30%;"> 
	<strong>'.$rel['quotation_no'].'</strong>
	</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;"> 
	Quotation Date
	</td>
	<td style="text-align:left;border:1px solid;"> 
	'.date("d-M-Y",strtotime($rel['quotation_date'])).'
	</td>
	</tr>

	<tr>
	<td style="text-align:left;border:1px solid;"> 
	Inquiry Ref No
	</td>
	<td style="text-align:left;border:1px solid;"> 
	'.$rel['inquiry_no'].'
	</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;"> 
	Inquiry Ref Date
	</td>
	<td style="text-align:left;border:1px solid;"> 
	'.date("d-M-Y",strtotime($rel['inquiry_date'])).'
	</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;"> 
	Executive Name
	</td>
	<td style="text-align:left;border:1px solid;"> 
	'.$userData['user_name'].'
	</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;"> 
	Inquiry Source
	</td>
	<td style="text-align:left;border:1px solid;"> 
	'.$rel['rb_name'].'
	</td>
	</tr>
	</table>
	<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
	<thead>
	<tr>
	<th style="width:5%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
	<th style="width:50%;text-align:center;border:1px solid;">Description</th>
	<th style="width:10%;text-align:center;border:1px solid;">Qty</th>
	<th style="width:10%;text-align:center;border:1px solid;">Rate <br>'.$currency_name.'</th>
	<th style="width:15%;text-align:center;border:1px solid;">Total Amount <br>'.$currency_name.'</th>
	</tr>
	</thead>
	<tbody>';
	if($inquiry_type==1){
		$trn_qry="select trn.*,pro.product_name,unit.unit_name from tbl_quotation_trn as trn 
		left join product_mst as pro on pro.product_id=trn.product_id
		left join unit_mst as unit on unit.unitid=trn.unitid
		where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
	} else {
		$trn_qry="select trn.*,pro.product_name from tbl_quotation_project_trn as trn left join product_mst as pro on pro.product_id=trn.product_id where trn.quotation_projecttrn_status=0 and trn.quotation_id=".$rel['quotation_id'];
	}
	$trn_qry_rs=$dbcon->query($trn_qry);
	$p=1;$ttl_amt=0;$ttl_qty=0;
	$cnt=mysqli_num_rows($trn_qry_rs);
	while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
		$tax_amount = $trn_rel['tax_amount1'] + $trn_rel['tax_amount2'] + $trn_rel['tax_amount3'];
		$tax_amount = $tax_amount ? $tax_amount : '';

		$tax_name1 = $trn_rel['tax_name1'] ? $trn_rel['tax_name1'] : '';
		$tax_name2 = $trn_rel['tax_name2'] ? $trn_rel['tax_name2'] : '';
		$tax_name3 = $trn_rel['tax_name3'] ? $trn_rel['tax_name3'] : '';
		$tax_name = $tax_name1.' '.$tax_name2.' '.$tax_name3;
		$tax_name = trim($tax_name) ? ' ('.$tax_name.')' : '';

		$product_desc = ($trn_rel['product_disc']) ? nl2br($trn_rel['product_disc']) : '';

		$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
		<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
		<td style="text-align:left;border:1px solid;vertical-align:top;">
		<strong>'.$trn_rel['product_name'].'</strong><br/>
		'.$product_desc.'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">
		'.$trn_rel['product_qty'].'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">';
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Extra At Actual";
		}
		else{
			if($rel['quot_type']=='0'){
				$html.= indian_number($trn_rel['product_rate'],2);
			}else{
				$html.= indian_number($trn_rel['product_rate_dollar'],2);
			}

		}

		$html.='</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">';
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Extra At Actual";
		}
		else{
			if($rel['quot_type']=='0'){
				$html.= indian_number($trn_rel['product_qty'] * $trn_rel['product_rate'],2);
			}else{
				$html.= indian_number($trn_rel['product_qty'] * $trn_rel['product_rate_dollar'],2);
			}
		}

		$html.='</td>
		</tr>';
		$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
		if($trn_rel['act_amt_flag']!='1'){
			if($rel['quot_type']=='0'){
				$ttl_amt=$ttl_amt+($trn_rel['product_qty'] * $trn_rel['product_rate']);
			}else{
				$ttl_amt=$ttl_amt+($trn_rel['product_qty'] * $trn_rel['product_rate_dollar']);
			}
		}

		$p++;
	}
	$pr=10-$cnt;
	for($j=0; $j<$pr; $j++)
	{
		$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
		<td style="border:none;border-left:1px solid;border-right:1px solid;height:40px;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		</tr>';
	}

	$html.='<tr>
	<td colspan="4" style="text-align:right;border:1px solid;"><b>Total Amount:- Ex Factory </b></td>
	<td style="text-align:center;border:1px solid;"><b>
	'.indian_number($ttl_amt,2).'
	</b></td>
	</tr>';
	if($rel['installation_charges'] != ''){
		$installation_charges = ($ttl_amt*$rel['installation_charges']/100);

	}else{
		$installation_charges = '0.00';
	}
	$html.='<tr>
	<td colspan="4" style="text-align:right;border:1px solid;"><b>Installation Charges '.$rel['installation_charges'].'% </b></td>
	<td style="text-align:center;border:1px solid;"><b>
	'.indian_number($installation_charges,2).'
	</b></td>
	</tr>';
	if($rel['installation_charges'] != ''){
		$gst_cal_charges = (($ttl_amt+$installation_charges)*18/100);
	}else{
		$gst_cal_charges = '0.00';
	}
	$html.='<tr>
	<td colspan="4" style="text-align:right;border:1px solid;"><b>18% GST </b></td>
	<td style="text-align:center;border:1px solid;"><b>
	'.indian_number($gst_cal_charges,2).'
	</b></td>
	</tr>';
	$grand_total_display = ($ttl_amt + $gst_cal_charges);
	$html.='<tr>
	<td colspan="4" style="text-align:right;border:1px solid;"><b>Total Amount <br>
	Fright, Customs, Packaging (Charged Actual At The Time Of Dispatch)</b>
	</td>
	<td style="text-align:center;border:1px solid;"><b>
	'.indian_number($grand_total_display,2).'
	</b></td>
	</tr>
	<tr>
	<td colspan="5" style="border:1px solid;"><b>Total (In Words): 
	'.ucfirst(convert_number_to_words_new($grand_total_display,$currency_word_start,$currency_word_end)).'</b></td></tr>';
	$html.='</tbody></table>';

	$html.='
	<center class="nextpage"></center><div>
	
	<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
	<thead>
	<tr>
	<th style="text-align:center; border:1px solid;">Sr.<br/>No.</th>
	<th style="text-align:left; border:1px solid;">Equipment</th>
	<th style="text-align:left; border:1px solid;">Price, Terms & Conditions</th>
	</tr>
	</thead><tbody>';
	/* Get Terms And Condition Start */
	$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
	left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
	where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
	$terms_qry_rs=$dbcon->query($terms_qry);
	if(mysqli_num_rows($terms_qry_rs)){
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
	}
	$html.='</tbody></table>';
	if($rel['term_inc']!=''){
		$html.='<center class="nextpage"></center>
		<div>
		<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
		<tr>
		<td width="100%" colspan="1" style="text-align:center;vertical-align:top;border-left:1px solid;border-bottom:1px solid;">
		<b>GENERAL TERMS & CONDITIONS</b>
		</td>
		</tr>
		</table>
		<p style="font-weight: normal;">'.stripslashes($general_terms_condition).'</p>
		</div>';
} //End of term_inc					

$html.='<center class="nextpage"></center>
<div>
<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
<tr>
<td width="100%" colspan="1" style="text-align:center;vertical-align:top;border-left:1px solid;border-bottom:1px solid;">
<b>BATTERY LIMITS AND SCHEDULE OF EXCLUSION</b>
</td>
</tr>
</table>
<p style="font-weight: normal;">'.stripslashes($battery_limits_and_schedule_exclusion).'</p>
</div>';			
$html.='<div>
<div style="clear:both;"></div>
</div>
<!--page1 end-->';

/* Check Annexure Attachments Start */
if(trim($rel['quot_annex_content'])){
	$html.='<center class="nextpage"></center>';
	$html.='<div class="quot_annex_content_div" style="font-size: 16px;">'.$rel['quot_annex_content'];
	$html.='</div>';
}
/* Check Annexure Attachments End */

$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
</body>
</html>';
// echo $trn_qry;
// echo $html;exit;
ob_end_clean();
// include("../../view/export/mpdf/mpdf.php");
// $mpdf=new mPDF('','A4','0','proximanova','10','10','35','10','1','1');
include("../../vendor/mpdf/mpdf/src/Mpdf.php");
$mpdf = new Mpdf(['format' => 'A4','margin_left' => 10,'margin_right' => 10,'margin_top' => 35,'margin_bottom' => 10,'margin_header' => 1,'margin_footer' => 1,'default_font' => 'calibri']);
//		$mdf->SetFont('ProximaNova');
$mpdf->defaultheaderfontsize = 10; /* in pts */
$mpdf->defaultheaderfontstyle = 'B'; /* blank, B, I, or BI */
$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
$mpdf->defaultfooterfontsize = 10; /* in pts */
$mpdf->defaultfooterfontstyle = 'B'; /* blank, B, I, or BI */
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
$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
ob_clean();
return 'quotation'.$quotation_id.'.pdf';
}

?>
