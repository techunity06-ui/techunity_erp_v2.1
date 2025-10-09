<?php 
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
	quotation_print($dbcon,$quotation_id,$save_file = "No");
}
function quotation_print($dbcon,$quotation_id,$save_file){
	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
	$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
	$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';

// $quotation_id = $_REQUEST['id'];	
$type='pdf';
if(strtolower($type) == 'pdf') {
//Quotation Data
	$query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name,cadd.c_add_state as state_id from tbl_quotation as quot
	left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
	left join tbl_customer as cust on cust.cust_id=quot.cust_id
	left join tbl_cust_address as cadd on cadd.cust_id=cust.cust_id 
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

$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
$userEmail = $userData['user_mail'] ? ' | Email: '.$userData['user_mail'] : '';

$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';
//Company Data
/*$comp_qry="select * from tbl_company as comp 
where comp.company_id=".$rel['company_id'];
*/
$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];

$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
$header ='<div style="text-align:right;"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:800px;padding-top: 25px;" /></div>';
//$header =$comp_rel["logo"];
$footer='<div style="text-align:center; border-top: 1px solid;padding-bottom: 10px;">'.$comp_rel['address'].'</div><div style="text-align:right;">{PAGENO}{nbpg}</div>';

$approve_status='';
if($rel['approve_status']=='0'){
	$approve_status=' (DRAFT)';
}
$inquiry_type=$rel['inquiry_type'];
//Amish Soni Start 16-03-2021
$companySettings = getCompanySettings($dbcon);

//$quotation_print_content = '<h3>Subject:-  <u>Budgetary offer for '.$rel['quot_subject'].'</u></h3><br>';

if($companySettings) {
	$quotation_print_content = $companySettings['quotation_print_content'] ? $companySettings['quotation_print_content'] : "";
	$quotation_print_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_print_content);
	$general_terms_condition = $companySettings['general_terms_condition'] ? $companySettings['general_terms_condition'] : $general_terms_condition;
	$general_terms_condition = str_ireplace(array("\r","\n",'\r','\n'),'', $general_terms_condition);
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
	<htmlpagefooter name="otherpages_footer" style="display:none">
	<div style="text-align:center">'.$footer.'</div>
	</htmlpagefooter>
	<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
	<div>
	<table cellpadding="5" cellspacing="5" border="0" style="font-size: 12px; font-family: Proxia Nova; border: 0;">
	<tr style="border: 0;">
	<td style="border: 0; text-align: right; font-size: 15px;">Date: '.date("d-M-Y",strtotime($rel['quotation_date'])).'</td>
	</tr>
	</table>
	<table cellpadding="5" cellspacing="5" border="0" style="font-size: 14px; font-family: Proxia Nova; border: 0;">
	<tr style="border: 0;">
	<td style="border: 0;">
	<p style="float: left; width: 100%;">
	<strong>To,<br />'.$rel['cust_name'].'</strong><br/>
	<span style="font-size: 12px;">Attn: '.$rel['c_con_fname'].' '.$rel['c_con_lname'].',<br />
	'.($quot_address).'</span>
	</p>
	<br/>
	<p><strong>Subject:</strong> Proposal for BRP DataSuite for '.$rel['cust_name'].' with the ref of '.$rel['quot_subject'].'</p>
	<br/>
	'.stripslashes($quotation_print_content).'
	<br /><br />
	<p>Regards,</p>
	<p><strong>'.$userData['user_name'] .'</strong> (<span style="font-size: 12px;">'. $userData['usertype_name'].'</span>),</p>
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
	<strong>To,<br>'.$rel['cust_name'].'</strong><br/>
	<span style="font-size: 12px;">Attn: '.$rel['c_con_fname'].' '.$rel['c_con_lname'].',<br />
	'.($quot_address).'</span>
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
	$p=1;$ttl_amt=0;$ttl_qty=0;$total_gst_per=0;$total_gst=0;$total_i_gst=0;$ttl_disc=0;
	$cnt=mysqli_num_rows($trn_qry_rs);
	while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
		$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
		$gst_rate = $trn_rel['cgst_tax_rate']+$trn_rel['sgst_tax_rate']+$trn_rel['igst_tax_rate'];

		if($trn_rel['cgst_tax_rate'] != 0 || $trn_rel['sgst_tax_rate'] !=0){
			$total_cs_gst += $gst_rate;
			$total_gst_per += $gst_per;
		}else{
			$total_i_gst += $gst_rate;
			$total_gst_per += $gst_per;
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

		$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';

		$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
		<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.' </td>
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
	    $ttl_disc=$ttl_disc+$trn_rel['product_discount'];
		
		if($trn_rel['act_amt_flag']!='1'){
			if($rel['quot_type']=='0'){
				$ttl_amt=$ttl_amt+($trn_rel['product_qty'] * $trn_rel['product_rate']);
			}else{
				$ttl_amt=$ttl_amt+($trn_rel['product_qty'] * $trn_rel['product_rate_dollar']);
			}
		}

		$p++;
	}
	$pr=7-$cnt;
	for($j=0; $j<$pr; $j++)
	{
		$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
		<td style="border:none;border-left:1px solid;border-right:1px solid;height:30px;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		</tr>';
	}

	$html.='<tr>
	<td colspan="4" style="text-align:right;border:1px solid;"><b>Basic Amount</b></td>
	<td style="text-align:center;border:1px solid;"><b>
	'.indian_number($ttl_amt,2).'
	</b></td>
	</tr>';
	if($ttl_disc>0){
	$html.='<tr>
	<td colspan="4" style="text-align:right;border:1px solid;"><b>Discount</b></td>
	<td style="text-align:center;border:1px solid;"><b>
	'.indian_number($ttl_disc,2).'
	</b></td>
	</tr>';
	$taxableamount=$ttl_amt-$ttl_disc;
	$html.='<tr>
	<td colspan="4" style="text-align:right;border:1px solid;"><b>Taxable Amount</b></td>
	<td style="text-align:center;border:1px solid;"><b>
	'.indian_number($taxableamount,2).'
	</b></td>
	</tr>';
	
	}
	

	if($rel['state_id']==$comp_rel['stateid']){
	    	$gst_per=$total_gst_per/2;
		$html.='<tr>
		<td colspan="4" style="text-align:right;border:1px solid;"><b>CGST ('.$gst_per.' %)</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		'.number_format(($total_cs_gst/2),2,".","").'
		</b></td>
		</tr>
		<tr>
		<td colspan="4" style="text-align:right;border:1px solid;"><b>SGST ('.$gst_per.' %)</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		'.number_format(($total_cs_gst/2),2,".","").'
		</b></td>
		</tr>';
	}else{
	    	$gst_per=$total_gst_per;
		$html.='<tr>
		<td colspan="4" style="text-align:right;border:1px solid;"><b>IGST ('.$gst_per.' %)</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		'.number_format(($total_i_gst),2,".","").'
		</b></td>
		</tr>';
	}
	$grand_total_display = (($ttl_amt + $total_cs_gst + $total_i_gst)-$ttl_disc);
	$html.='<tr>
	<td colspan="4" style="text-align:right;border:1px solid;"><b>Total Amount</b>
	</td>
	<td style="text-align:center;border:1px solid;"><b>
	'.indian_number($grand_total_display,2).'
	</b></td>
	</tr>
	<tr>
	<td colspan="5" style="border:1px solid;text-align:right;"><b>Total (In Words): 
	'.ucfirst(convert_number_to_words_new($grand_total_display,$currency_word_start,$currency_word_end)).'</b></td></tr>';
	$html.='</tbody></table>';

	$html.='
	<center class="nextpage"></center><div>
	
	<table style="font-size:14px;border-collapse: collapse;width:100%; border: none;" cellpadding="5" cellspacing="5">
	<thead>
	<tr style="border: none;">
	<th style="text-align:left;">TERMS & CONDITIONS</th>
	</tr>
	</thead></table>';
	/* Get Terms And Condition Start */
	$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
	left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
	where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
	$terms_qry_rs=$dbcon->query($terms_qry);
	if(mysqli_num_rows($terms_qry_rs)){
		$t=1;
		while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
			$string=(nl2br($term_rel['tc_details']));
			$html.='<div style="border: none;">
			<div tyle="text-align:left;"><strong>'.$t.' '.$term_rel['tc_name'].' :</strong>
			<br>
			'.$string.'</div>
			</div><br>';
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
}

/* Check Annexure Attachments Start */
if(trim($rel['quot_annex_content'])){
	$html.='<center class="nextpage"></center>';
	$html.='<div class="quot_annex_content_div" style="font-size: 16px;">'.$rel['quot_annex_content'];
	$html.='</div>';
}
/* Check Annexure Attachments End */

$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
</body>
</html>';
// echo $trn_qry;
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
//Show page number : Dimple Panchal (05-Apr-2021)
$mpdf->pagenumPrefix = ' ';
$mpdf->pagenumSuffix = ' / ';
$mpdf->nbpgPrefix = ' ';
$mpdf->nbpgSuffix = ' pages';
// $mpdf->SetFooter('{PAGENO}{nbpg}');
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

	}
?>