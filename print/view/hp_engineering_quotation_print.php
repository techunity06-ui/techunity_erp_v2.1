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

	if(!$rel){
		header("Location: ".ROOT.CRM_ROOT."quotation_list");
	}

	$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';
//Company Data
/*$comp_qry="select * from tbl_company as comp 
where comp.company_id=".$rel['company_id'];
*/
$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];

$comp_rel=mysqli_fetch_assoc($dbcon->query($set));


$approve_status='';
if($rel['approve_status']=='0'){
	$approve_status=' (DRAFT)';
}

//Amish Soni Start 16-03-2021
$companySettings = getCompanySettings($dbcon);
if($companySettings) {
	$quotation_print_content = $companySettings['quotation_print_content'] ? $companySettings['quotation_print_content'] : $quotation_print_content;
	$quotation_print_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_print_content);
}
$company_config = getCompanyConfiguration($dbcon);
//Amish Soni End 16-03-2021
if($company_config['crm_print_letterhead_per']==0){
	$header ='<div style=""><img src="'.DOMAIN_F.LOGO.$comp_rel['logo'].'" style="width:8.27in;" /></div><br>';
//$header =$comp_rel["logo"];
	$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';
} else{
	$header='';
	$footer='';
}

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
	<htmlpagefooter name="otherpages_footer" style="display:none">
	<div style="text-align:center">'.$footer.'</div>
	</htmlpagefooter>
	<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
	<hr style="border:1px solid black">
	<div>
	<table cellpadding="5" cellspacing="5" border="0" style="font-size: 20px; font-family: Proxia Nova; border: 0;">

	<tr style="border: 0;">
	<td style="border: 0; text-align: left; font-size: 15px;font-weight:bold">Ref.no : '.$rel['quotation_no'].'</td>
	<td style="border: 0; text-align: right; font-size: 15px;font-weight:bold">Inq. Date: '.date("d/m/Y",strtotime($rel['inquiry_date'])).'</td>
	</tr>
	<tr style="border: 0;">
	<td style="border: 0; text-align: left; font-size: 15px;font-weight:bold">Date : '.date("d M Y",strtotime($rel['quotation_date'])).'</td>
	<td style="border: 0; text-align: right; font-size: 15px;font-weight:bold">Inq. Ref.: '.$rel['inquiry_no'].'</td>
	</tr>
	</table>
	<table cellpadding="5" cellspacing="5" border="0" style="font-size: 0px; font-family: Proxia Nova; border: 0;">
	<tr style="border: 0;">
	<td style="border: 0;">
	To,<br/>
	<p><b>'.$rel['cust_name'].'</b></p>
	'.(nl2br($rel['quot_address'])).'<br/>

	</td>
	</tr><br><br>
	<tr style="border: 0;">
	<td style="border: 0; text-align: left">
	<strong>KIND ATTN. : '.(nl2br($rel['c_con_fname'])).' '.(nl2br($rel['c_con_lname'])).'</strong><br><br>

	<strong>SUB. : '.(nl2br($rel['quot_subject'])).'</strong>
	</td>
	</tr>
	</table>
	</div>
	<p>Dear Sir/Madam,<br>
	We have received your inquiry regarding your requirement of. We submit here with following offer.</p>
	<table style="font-size:16px;border-collapse:0 collapse;width:100%;" cellpadding="2" cellspacing="2">
	<tr>
	<th style="width:12%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
	<th style="width:50%;text-align:center;border:1px solid;">Description</th>
	<th style="width:15%;text-align:center;border:1px solid;">HSN Code</th>
	<th style="width:8%;text-align:center;border:1px solid;">Qty</th>
	<th style="width:15%;text-align:center;border:1px solid;">Rate Per NOS.</th>
	</tr>
	<tbody>';
	$trn_qry="select trn.*,pro.product_name,hsn.hsn_code as product_hsn_code,unit.unit_name,cunit.unit_name as conv_unit,runit.unit_name as rate_uni from tbl_quotation_trn as trn 
	left join product_mst as pro on pro.product_id=trn.product_id
	left join mst_hsn_code as hsn on hsn.hsn_id=pro.product_hsn
	left join unit_mst as unit on unit.unitid=trn.unitid
	left join unit_mst as cunit on cunit.unitid = trn.conv_unit_id
	left join unit_mst as runit on runit.unitid = trn.rate_unit
	where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
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
		$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';
		if($cnt==$p){
			$sty = "border-bottom: 1px solid;";
		}else{
			$sty = "border-bottom: none;";
		}

		if($trn_rel['unitid']!=$trn_rel['rate_unit']){
		    $sqty = $trn_rel['product_qty'].' '.$trn_rel['unit_name'].'<br>'.$trn_rel['product_conv_qty'].' '.$trn_rel['conv_unit'];
		}else{
		   $sqty = $trn_rel['product_qty'].' '.$trn_rel['unit_name'];
		}

		$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;'.$sty.'">
		<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
		<td style="text-align:left;border:1px solid;vertical-align:top;">
		<strong>'.$trn_rel['product_name'].'</strong><br/>
		'.$product_desc.'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;font-weight:bold">
		'.$trn_rel['product_hsn_code'].'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;font-weight:bold">
		'.$sqty.'
		</td>';

		$html.='
		<td style="text-align:center;border:1px solid;vertical-align:top;font-weight:bold">';
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Extra At Actual";
		}else{
			$html.= 'RS. '. indian_number($trn_rel['product_rate'],2) .' /- Per '.$trn_rel['rate_uni'].'.';
		}
		$html.='</td></tr>';
		$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
		$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
		
		$p++;
	}
	$pr=1-$cnt;
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
	$html.='<!--<tr>
	<td colspan="3" style="text-align:center;border:1px solid;font-weight:bold">Total</td>
	<td style="text-align:center;border:1px solid;font-weight:bold">'.$ttl_qty.'</td>

	<td style="text-align:center;border:1px solid;font-weight:bold">'.indian_number($ttl_amt,2).'</td>
	</tr>
	<tr>
	<td colspan="3" style="text-align:left;border:0px solid;">Total (In Words) :&nbsp;&nbsp;'.ucfirst(convert_number_to_words_new($ttl_amt)).'</td>
	<td colspan="2" border="0" style="border: 0px solid; !important; text-align:left;"></td>
	</tr>-->
	</tbody>
	</table>';
	if(!empty($rel["quot_remark"]) && $rel["quot_remark"]!="0"){
		$html.='<div style="clear:both;margin-top:20px;font-size:14px;"><strong><u>Scope Of Works:</u></strong> <br>'.$rel['quot_remark'].'</div>';
	}
	$html.='</div>';
	/* Get Terms And Condition Start */
	$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
	left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
	where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
	$terms_qry_rs=$dbcon->query($terms_qry);
	if(mysqli_num_rows($terms_qry_rs)){
		$html.='<center class="nextpage"></center><div>
		<h3 style="text-align:center;">OTHER TERMS & CONDITION</h3>
		<table width="100%" style="font-size:12px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3"><tbody>';
		$t=1;
		while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
			$string=(nl2br($term_rel['tc_details']));

			$html.='<tr>
			<td width="5%" style=";text-align:left;padding:5px;font-size:14px">'.$t.'</td>
			<td width="25%" style=";text-align:left;padding:5px;font-size:14px; font-weight: bold;">'.$term_rel['tc_name'].'</td>
			<td width="70%" style=";text-align:left;padding:5px;font-size:14px">'.$string.'</td>
			</tr>';
			$t++;
		}

		$html.='</tbody></table>';
	}
	$html.='<table style="margin-top: 20px;">
	<tr style="border:none;">
	<td style="text-align:right;border:none;"><img src="'.DOMAIN_F.'view/upload/signature/'.$comp_rel['authorized_signature'].'" style="width: 125px; height: 125px;"/></td>
	</tr>
	<tr style="border:none;">
	<td style="text-align:right;border:none;">Authorized Signature</td>
	</tr>
	</table></div>';

	$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
	</body>
	</html>';
	ob_end_clean();
	include("../../view/export/mpdf/mpdf.php");
	$mpdf=new mPDF('','A4','0','calibri','10','10','35','10','1','1');
	$mpdf->defaultheaderfontsize = 10; /* in pts */
	$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
	$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
	$mpdf->defaultfooterfontsize = 10; /* in pts */
	$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
	$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
	$mpdf->SetHTMLHeader($header);
	$mpdf->SetHTMLFooter($footer);
        //Show page number : Dimple Panchal (05-Apr-2021)
// 	$mpdf->pagenumPrefix = ' ';
// 	$mpdf->pagenumSuffix = ' / ';
// 	$mpdf->nbpgPrefix = ' ';
// 	$mpdf->nbpgSuffix = ' pages';
// 	$mpdf->SetFooter('{PAGENO}{nbpg}');
	$mpdf->SetWatermarkText();
	$mpdf->showWatermarkText = true;
	$mpdf->allow_charset_conversion=true;
	$mpdf->charset_in='UTF-8';
	$mpdf->WriteHTML($html);
	$mpdf->Output();
	ob_clean();
	return 'quotation'.$quotation_id.'.pdf';
}

?>