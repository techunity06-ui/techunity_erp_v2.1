<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once("../../crm/include/crm_common_functions.php");
include_once(COMMON_FUNCTION_PATH."common_production_functions.php");
include_once(COMMON_FUNCTION_PATH."common_production_store_wise_function.php");
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
	$query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name, anx.an_name from tbl_quotation as quot
	left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
	left join tbl_customer as cust on cust.cust_id=quot.cust_id
	left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
	left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
	left join tbl_annexure as anx on anx.an_id=quot.an_id
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
$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$_SESSION['company_id'];

$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
$header ='<div style="text-align:center;"><img src="'.DOMAIN_F.LOGO.$comp_rel['logo'].'" style="width:8.27in;" /></div>';
//$header =$comp_rel["logo"];
//$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';

$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
$userData = mysqli_fetch_assoc($dbcon->query($userQuery));
$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';

$approve_status='';
if($rel['approve_status']=='0'){
	$approve_status=' (DRAFT)';
}

//Amish Soni Start 16-03-2021
$companySettings = getCompanySettings($dbcon);

$quotation_print_content = $rel['quot_header'] ? $rel['quot_header'] : $companySettings['quotation_print_content'];
$quotation_print_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_print_content);
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
	<!--<htmlpagefooter name="otherpages_footer" style="display:none">
	<div style="text-align:center">'.$footer.'</div>
	</htmlpagefooter>-->
	<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
	<div>
	<table cellpadding="5" cellspacing="5" border="0" style="font-size: 16px; font-family: Proxia Nova; border: 0;">
	<tr style="border: 0;">
	<td style="border: 0; text-align: right; font-size: 15px;"></td>
	</tr>
	</table>
	<table cellpadding="5" cellspacing="5" border="0" style="font-size: 16px; font-family: Proxia Nova; border: 0;">
	<tr style="border: 0;">
	
	<td style="border: 0;">
	<p>
	Ref.:'.$rel['quotation_no'].'
	</p>
	<p>
	Date: '.date("d-M-Y",strtotime($rel['quotation_date'])).'
	</p>
	<br><br>
	<p style="float: left; width: 100%;">
	<strong>M/S : '.$rel['cust_name'].',</strong><br/><br>
	<strong style="color: black">Kind attention : '.$rel['c_con_fname'].' '.$rel['c_con_lname'].',<br />
	'.($quot_address).'</strong>
	</p>
	<p style="float: left; width: 100%;text-align:center">Re. <strong>'.$rel['quot_subject'].'</strong></p>
	<br />
	'.stripslashes($quotation_print_content).'
	<br /><br />
	<p>Regards</p>
	<p>'.$userData['user_name'] .' - '. $userData['usertype_name'].',</p>
	<p>'.$userPhone .''. $userEmail.',</p>
	<p>'.$comp_rel['company_name'].'</p>
	</td>
	</tr>
	</table>
	</div>
	<center class="nextpage"></center>
	<div>
	<table style="border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
	<tr style="border: 0;">
	<td style="text-align:left;border: 0;"> 
	<h4><u>'.$rel['an_name'].'</u></h4><br>
	'.$quot_annex_content.'</td>
	</tr>
	</table><br/>';
	$pr_sp = "select product_spec,product_desc from tbl_quotation_trn where quot_trn_status=0 and quotation_id=".$rel['quotation_id']." and product_spec !=''";

	$pr_se = $dbcon->query($pr_sp);
	while($sp_ro = mysqli_fetch_array($pr_se)){
		$html .= (($sp_ro['product_spec']) ? $sp_ro['product_spec'] : '').'<br>';
	}
	$html .= '</div>';
	$html .='<center class="nextpage"></center>
	<div><table style="font-size:16px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
	<thead>
	<tr style="border:none;height:35px">
	<td style="width:100%;text-align:left;border:none;" colspan="5"><strong><u>Price schedule</u></strong></td>
	</tr>
	<tr>
	<th style="width:2%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
	<th style="width:50%;text-align:center;border:1px solid;">Item Description</th>
	<th style="width:8%;text-align:center;border:1px solid;">Qty</th>
	<th style="width:10%;text-align:center;border:1px solid;">Unit Price</th>';
	if($getsql['total_discount']>0){
	    $html .='<th style="width:10%;text-align:center;border:1px solid;">Discount</th>';
	}
	$html .='<th style="width:15%;text-align:center;border:1px solid;">Total Price</th>
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
		<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
		<td style="text-align:left;border:1px solid;vertical-align:top;">
		<strong>'.$trn_rel['product_name'].'</strong><br/>
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">
		'.$trn_rel['product_qty'].'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">';
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Extra At Actual";
		}
		else{
			$html.=$trn_rel['product_rate'];
		}

		$html.='</td>';
		if($getsql['total_discount']>0){
    	    $html .='<td style="text-align:center;border:1px solid;vertical-align:top;">'.$trn_rel['product_discount'].' ('.$trn_rel['discount_per'].' %)</td>';
    	}
		$html.='<td style="text-align:center;border:1px solid;vertical-align:top;">';
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Included";
		}
		else{
			$html.=$trn_rel['product_total'];
		}

		$html.='</td>
		</tr>';
		$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
		if($trn_rel['act_amt_flag']!='1'){
			$ttl_amt=$ttl_amt+$trn_rel['product_total'];
		}

		$p++;
	}
	$pr=5-$cnt;
	for($j=0; $j<$pr; $j++)
	{
		$html.='<tr style="border:1px solid;border-left:1px solid;border-right:1px solid;">
		<td style="border:1px solid;border-left:1px solid;border-right:1px solid;height:25px;border-bottom:1px solid"></td>
		<td style="border:1px solid;border-left:1px solid;border-right:1px solid;border-bottom:1px solid"></td>
		<td style="border:1px solid;border-left:1px solid;border-right:1px solid;border-bottom:1px solid"></td>
		<td style="border:1px solid;border-left:1px solid;border-right:1px solid;border-bottom:1px solid"></td>';
		if($getsql['total_discount']>0){
    	    $html .='<td style="border:1px solid;border-left:1px solid;border-right:1px solid;border-bottom:1px solid"></td>';
    	}
		$html .='<td style="border:none;border-left:1px solid;border-right:1px solid;border-bottom:1px solid"></td>
		</tr>';
	}

	$html.='<tr>
	<td colspan="2" style="text-align:center;border:1px solid;height:25px; font-weight: bold;">Total</td>
	<td style="text-align:center;border:1px solid;">
	'.$ttl_qty.'
	</td>
	<td style="text-align:center;border:1px solid;"></td>';
	if($getsql['total_discount']>0){
    	    $html .='<td style="border:1px solid;border-left:1px solid;border-right:1px solid;border-bottom:1px solid"></td>';
    	}
	$html .='<td style="text-align:center;border:1px solid; font-weight: bold;">
	'.$ttl_amt.'
	</td>
	</tr>
	<tr>
	<td colspan="2" style="text-align:center;border:1px solid; font-weight: bold;">Total (In Words)</td>
	<td colspan="'.$colsapn.'" border="0" style="border: 1px solid; !important; text-align: right; height:25px; font-weight: bold;">'.convert_number_to_words_new($ttl_amt).'</td>
	</tr>
	</tbody>
	</table>
	<div style="clear:both;"></div>
	</div>';
	/* Get Terms And Condition Start */
	$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
	left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
	where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
	$terms_qry_rs=$dbcon->query($terms_qry);
	if(mysqli_num_rows($terms_qry_rs)){
		$html.='<center class="nextpage"></center>
		<div>
		<table width="100%" style="border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3">
		<tbody>
		<tr style="border: 0;">
		<td style="border: 0; padding-top: 20px;" colspan="3">
		<strong>Terms & Conditions</strong>
		</td>
		</tr>';
		while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
			$string=(nl2br($term_rel['tc_details']));

			$html .='<tr>
			<td style="border: 1px solid black;width:25%; vertical-align: top; font-weight: bold;">'.$term_rel['tc_name'].'</td>
			<td style="border: 1px solid black;width:5%; vertical-align: top; font-weight: bold; text-align: center;"> : </td>
			<td width="70%" style="width:70%;text-align:left;border:1px solid black;">'.$string.'</td>
			</tr>';
		}
		$html.='</tbody>
		</table>
		</div>';	
	}
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
// echo $html;exit;
ob_end_clean();
include("../../view/export/mpdf/mpdf.php");
$mpdf=new mPDF('','A4','0','proximanova','10','10','35','10','1','1');
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
return 'quotation'.$quotation_id.'.pdf';
}

?>