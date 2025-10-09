<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once("../../crm/include/crm_common_functions.php");
include_once(COMMON_FUNCTION_PATH."common_production_functions.php");
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
	$query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_job,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name, anx.an_name,cur.currency_symbol,cur.currency_code,cur.currency_in_word,cur.currency_in_word_end from tbl_quotation as quot
	left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
	left join tbl_customer as cust on cust.cust_id=quot.cust_id
	left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
	left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
	left join tbl_annexure as anx on anx.an_id=quot.an_id
	left join tbl_currency as cur on cur.currency_id = quot.currency_id
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
	$quotation_date='';
	if($rel['quotation_date']!="1970-01-01" && $rel['quotation_date']!="0000-00-00")
	{
		$quotation_date=date('d-m-Y',strtotime($rel['quotation_date']));
	}

$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$_SESSION['company_id'];

$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
//$header =$comp_rel["logo"];
$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';
$our_client='<img src="'.DOMAIN_F.LOGO.'Customers.jpg" style="padding-left:0px !important;width:100%"/>';

$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
$userData = mysqli_fetch_assoc($dbcon->query($userQuery));
$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
$userEmail = $userData['user_mail'] ? 'Email: '.$userData['user_mail'] : '';

$approve_status='';
if($rel['approve_status']=='0'){
	$approve_status=' (DRAFT)';
}

//Amish Soni Start 16-03-2021
$companySettings = getCompanySettings($dbcon);
$company_config = getCompanyConfiguration($dbcon);
if($company_config['po_header_content']!=''){
		$po_header_content = '<td style="border: 0px; vertical-align: top; width: 45%;"><span style="font-size: 18px; font-weight: bold;">'.$comp_rel['company_name'].'</span><br>'.$company_config['po_header_content'].'</td>';
	}else{
		$po_header_content = '<td style="border: 0px; vertical-align: top; width: 45%;"><span style="font-size: 18px; font-weight: bold;">'.$comp_rel['company_name'].'</span><br>'.$comp_rel['address'].'<br>Phone no. : '.$comp_rel['contact_no'].'<br>GST No. :- '.$comp_rel['vatno'].'<br>E-mail: '.$comp_rel['website'].'</td>';
	}

	$header =get_header($dbcon,'text-align: center','100%','150px');

$quotation_print_content = $rel['quot_header'] ? $rel['quot_header'] : $companySettings['quotation_print_content'];
$quotation_footer_content = $rel['quot_footer'] ? $rel['quot_footer'] : $companySettings['quotation_footer_content'];
$quotation_print_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_print_content);
$quotation_footer_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_footer_content);
//Amish Soni End 16-03-2021
$quot_annex_content=($rel['quot_annex_content']) ? $rel['quot_annex_content'] : '';

$trnsql=$dbcon->query("select SUM(product_discount) as total_discount from tbl_quotation_trn where quot_trn_status=0 and quotation_id=".$rel['quotation_id']);
$getsql=mysqli_fetch_assoc($trnsql);
if($getsql['total_discount']>0){
    $colsapn=4;
}else{
    $colsapn=3;
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
	<div>
	<table cellpadding="5" cellspacing="5" border="0" style="font-size: 16px; font-family: Proxia Nova; border: 0;">
	<tr style="border: 0;">
	<td style="border: 0; text-align: right; font-size: 15px;"></td>
	</tr>
	</table>
	<table cellpadding="5" cellspacing="5" border="0" style="font-size: 16px; font-family: Proxia Nova; border: 1px solid;font-weight: bold;">
		<tr>
			<td style="width:15%">To :</td>
			<td style="width:50%">'.$rel['cust_name'].'</td>
			<td style="width:15%">Date</td>
			<td style="width:20%">'.$quotation_date.'</td>
		</tr>
		<tr >
			<td style="width:5%;vertical-align:top">Address :</td>
			<td style="width:50%;vertical-align:top">'.$quot_address.'</td>
			<td style="vertical-align:top">Quotation No</td>
			<td style="vertical-align:top">'.$rel['quotation_no'].'</td>
		</tr>
	</table>
	<table cellpadding="5" cellspacing="5" border="0" style="font-size: 16px; font-family: Proxia Nova; border: 0;">

	<tr style="border: 0;">
	
	<td style="border: 0;height:40px">
	</td>
	</tr>
	<tr style="border: 0;">
	
	<td style="border: 0;">
	<!--<p>
	Ref.:'.$rel['quotation_no'].'
	</p>
	<p>
	Date: '.date("d-M-Y",strtotime($rel['quotation_date'])).'
	</p>-->
	<p style="float: left; width: 100%;text-align:center"><strong>Subject: '.$rel['quot_subject'].'</strong></p>
	<br><br>
	<p style="float: left; width: 100%;">
	<!--<strong>M/S : '.$rel['cust_name'].',</strong><br/>-->';
	if(!empty($rel['c_con_lname'])){
	    $html .= 'To, <br> <strong>'.$rel['c_con_fname'].' '.$rel['c_con_lname'].' </strong><br>'.$rel['c_con_job'].'<br>';
	}
	$html .='<!--<strong style="color: black">
	'.($quot_address).'</strong>
	</p><br>-->
	
	<br><br>
	'.stripslashes($quotation_print_content).'
	<br><br>
	<!--<p>Regards</p>
	<p>'.$userData['user_name'] .' - '. $userData['usertype_name'].'</p>
	<p>'.$userPhone .'<br>'. $userEmail.'</p>-->
	</td>
	</tr>
	</table>
	</div>
	<div>';
	$pr_sp = "SELECT trns.product_spec,product.product_name from tbl_quotation_trn as trns left join product_mst as product on product.product_id=trns.product_id where trns.quot_trn_status=0 and trns.quotation_id=".$rel['quotation_id']." and trns.product_spec !=''";
	$ps = 1;
	$pr_se = $dbcon->query($pr_sp);
	if(brp_mysqli_num_rows($pr_se)>0){
		$html .='<center class="nextpage"></center>
		<h3 style="text-align:left">Specification : </h3>';
		while($sp_ro = mysqli_fetch_array($pr_se)){
			$html .= '<h3 style="text-decoration: underline; text-align: center;"><strong>'.$ps.') '.$sp_ro['product_name'].'</strong></h3>'.(($sp_ro['product_spec']) ? $sp_ro['product_spec'] : '').'<br>';
			$ps++;
		}
	}

	$html .= '</div>
	<center class="nextpage"></center>
	<div>
	<h3 style=" text-align: left;"><strong>Best Competitive OFFER for you;</strong></h3>
	<table style="font-size:16px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
	<thead>
	<tr>
	<!--<th style="width:2%;text-align:center;border:1px solid;">Sr.<br/>No.</th>-->
	<th style="width:30%;text-align:center;border:1px solid;">Item</th>
	<!--<th style="width:8%;text-align:center;border:1px solid;">Qty</th>-->
	<th style="width:10%;text-align:center;border:1px solid;">Price</th>
	<th style="width:40%;text-align:center;border:1px solid;">Amount In Words</th>
	</tr>
	</thead>
	<tbody>';
	$trn_qry="select trn.*,pro.product_name,unit.unit_name from tbl_quotation_trn as trn 
	left join product_mst as pro on pro.product_id=trn.product_id
	left join unit_mst as unit on unit.unitid=trn.unitid
	where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
	$trn_qry_rs=$dbcon->query($trn_qry);
	$p=1;$ttl_amt=0;$ttl_qty=0;
	$cnt=mysqli_num_rows($trn_qry_rs);
	while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){

		$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';

		$html.='<tr style="border:1px solid;border-left:1px solid;border-right:1px solid;">
		<!--<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>-->
		<td style="text-align:left;border:1px solid;vertical-align:top;">
		<strong>'.$trn_rel['product_name'].'</strong><br/><!--'.$product_desc.'-->
		</td>
		<!--<td style="text-align:center;border:1px solid;vertical-align:top;">
		'.$trn_rel['product_qty'].'
		</td>-->
		<td style="text-align:center;border:1px solid;vertical-align:top;">';
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Extra At Actual";
		}
		else{
			$html.=$rel['currency_code']." ".$trn_rel['product_amount_conv'];
		}

		$html.='</td>
		<td style="text-align:left;border:1px solid;vertical-align:top;">
		'.convert_number_to_words_new($trn_rel['product_amount_conv'],$rel['currency_id'],$rel['currency_in_word_end'],$rel['currency_in_word']);
		/*if($trn_rel['act_amt_flag']=='1'){
			$html.="Included";
		}
		else{
			$html.=$trn_rel['product_total_conv'];
		}*/

		$html.='</td>
		</tr>';
		$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
		if($trn_rel['act_amt_flag']!='1'){
			$ttl_amt=$ttl_amt+$trn_rel['product_total_conv'];
		}

		$p++;
	}
	/*$pr=5-$cnt;
	for($j=0; $j<$pr; $j++)
	{
		$html.='<tr style="border:1px solid;border-left:1px solid;border-right:1px solid;">
		<td style="border:1px solid;border-left:1px solid;border-right:1px solid;height:25px;border-bottom:1px solid"></td>
		<td style="border:1px solid;border-left:1px solid;border-right:1px solid;border-bottom:1px solid"></td>
		<td style="border:1px solid;border-left:1px solid;border-right:1px solid;border-bottom:1px solid"></td>
		<td style="border:1px solid;border-left:1px solid;border-right:1px solid;border-bottom:1px solid"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;border-bottom:1px solid"></td>
		</tr>';
	}*/

	$html.='
	</tbody>
	</table></div>
	<div>';
	/* Get Terms And Condition Start */
	$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
	left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
	where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
	$terms_qry_rs=$dbcon->query($terms_qry);$tc = 1;
	if(mysqli_num_rows($terms_qry_rs)){
		$html.='<table width="100%" style="border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3">
		<tbody>
		<tr style="border: 0;">
		<td style="border: 0; padding-top: 20px;">
		<strong>Terms & Conditions</strong>
		</td>
		</tr>';
		while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
			$string=(nl2br($term_rel['tc_details']));
			$html .='<tr style="border: 0;">
			<td style="border: 0;"><strong>'.$tc.') '.$term_rel['tc_name'].' : </strong>'.$string.'</td>
			</tr>';
			$tc++;
		}
		$html.='</tbody>
		</table>';	
	}
	

	$html.='<p>Thanking you in anticipation of our close business relation.<br><br>Regards<br>
	<strong>For; '.$comp_rel['company_name'].' </strong><br><br>
	<strong>'.$userData['user_name'] .'<br>('. $userData['usertype_name'].')<br>
	'.$userPhone .'<br></strong></p><!--'. $userEmail.'-->
	</div>';


	$html.='<br>'.$quotation_footer_content.'<br><br><center class="nextpage"></center><div>';
	$html.=$our_client.'</div>';
	/* Get Terms And Condition Start */

	/* Check Annexure Attachments Start */
/*if(trim($rel['quot_annex_content'])){
	$html.='<center class="nextpage"></center>';
	$html.='<div class="quot_annex_content_div" style="font-size: 16px;">'.$rel['quot_annex_content'];
	$html.='</div>';
}*/
/* Check Annexure Attachments End */

$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
</body>
</html>';
//echo $html;exit;
ob_end_clean();
include("../../view/export/mpdf/mpdf.php");
$mpdf=new mPDF('','A4','0','proximanova','10','10','35','30','1','1');
//		$mdf->SetFont('ProximaNova');
$mpdf->defaultheaderfontsize = 10; /* in pts */
$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
$mpdf->defaultfooterfontsize = 10; /* in pts */
$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLFooter($footer);
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