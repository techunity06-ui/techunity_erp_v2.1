<?php 
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

	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
	$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
	$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';
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
$header ='<div style="text-align:right;"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:8.27in;" /></div>
<table cellpadding="5" cellspacing="5" border="0" style="font-size: 14x; font-family: Proxia Nova; border: 0;">
	<tr style="border: 0;">
	<td style="border: 0; text-align: left; font-size: 15px; font-weight: bold;">Date: '.date("dS F Y",strtotime($rel['quotation_date'])).'</td>
	</tr>
	<tr style="border: 0;">
	<td style="border: 0; text-align: left; font-size: 15px; font-weight: bold;">Offer Ref.: '.$rel['quotation_no'].'</td>
	</tr>
	</table>';
//$header =$comp_rel["logo"];
$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';

$approve_status='';
if($rel['approve_status']=='0'){
	$approve_status=' (DRAFT)';
}
$inquiry_type=$rel['inquiry_type'];

$colspan =5;
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
	<table cellpadding="5" cellspacing="5" border="0" style="font-size: 14px; font-family: Proxia Nova; border: 0;">
	<tr style="border: 0;">
	<td style="border: 0;" colspan="2">
	<p style="float: left; width: 100%;">
	<strong>To,<br />'.$rel['cust_name'].',</strong><br>
	'.($quot_address).'<br>
	</p>
	<p>Mob No. :- (+91)'.$rel['cust_mobile'].'</p><br>
	<p>Email-Id : '.$rel['cust_email'].'</p>
	</td>
	</tr>
	<tr style="border: 0;">
	<td style="border: 0; font-weight: bold;" width="20%">Kind Attention</td>
	<td style="border: 0; font-weight: bold;" width="80%">: '.$rel['c_con_fname'].' '.$rel['c_con_lname'].'</td>
	</td>
	</tr>
	<tr style="border: 0;">
	<td style="border: 0; font-weight: bold;" >Subject</td>
	<td style="border: 0; font-weight: bold;">: '.$rel['quot_subject'].'</td>
	</td>
	</tr>
	<tr style="border: 0;">
	<td style="border: 0; font-weight: bold;">Reference</td>
	<td style="border: 0; font-weight: bold;">: '.$rel['quotation_ref'].'</td>
	</td>
	</tr>
	<tr style="border: 0;">
	<td style="border: 0;" colspan="2">'.$rel['quot_header'].'</td>
	</tr>
	<tr style="border: 0;">
	<td style="border: 0;" colspan="2">Yours Faithfully<br><br>For <strong>'.$comp_rel['company_name'].'</strong></td>
	</tr>
	<tr style="border: 0;">
	<td style="border: 0;" colspan="2"><img src="'.DOMAIN_F.'view/upload/signature/'.$comp_rel['authorized_signature'].'" style="height: 100px;width: 100px;"/><br>'.$userData['user_name'].'<br>'.$userData['usertype_name'].'<br><strong>'.$userPhone.'</strong></td>
	</tr>
	</table>
	</div>
	<center class="nextpage"></center><div>
	<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
	<thead>
	<tr style="border: 0;">
	<th style="text-align:center; font-weight: bold; border: 0;">Annexure I - Commercial Terms & Conditions</th>
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
			$html.='<tr style="border: 0;">
			<td style="text-align:left; font-weight: bold; border: 0;">'.$t.' '.$term_rel['tc_name'].'</td>
			</tr>
			<tr style="border: 0;">
			<td style="text-align:left; border: 0;">&nbsp;&nbsp;&nbsp;'.$string.'</td>
			</tr>';
			$t++;
		}
	}
	$html.='</tbody>
	</table>
	</div>';
	if(trim($rel['quot_annex_content'])){
		$html.='<center class="nextpage"></center>
		<strong><h4 style="text-align: center;">Annexure II - Technical Specifications</h4></strong><div class="quot_annex_content_div">'.$rel['quot_annex_content'].'</div>';
	}
	if($inquiry_type==1){
		$trn_qrys="select trn.* from tbl_quotation_trn as trn where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
	} else {
		$trn_qrys="select trn.* from tbl_quotation_project_trn as trn where trn.quotation_projecttrn_status=0 and trn.quotation_id=".$rel['quotation_id'];
	}
	// echo $trn_qrys;
	$trn_qry_rss=$dbcon->query($trn_qrys);
	if(mysqli_num_rows($trn_qry_rss)>0){
		$html.='<center class="nextpage"></center>
		<div>
		<strong><h4 style="text-align: center;">Annexure II A - Technical Datasheet</h4></strong>';
		while($qry_res = mysqli_fetch_assoc($trn_qry_rss)){
			$product_spec = ($qry_res['product_spec']) ? nl2br($qry_res['product_spec']) : '';
			$html.=$product_spec.'<br>';
		}
		$html.='</div>';
	}
	$html.='<center class="nextpage"></center>
	<div>
	<strong><h4 style="text-align: center;">Annexure III - Price Bid for Storage Tanks</h4></strong>
	<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
	<thead>
	<tr>
	<th style="width:5%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
	<th style="width:50%;text-align:center;border:1px solid;">Description</th>
	<th style="width:10%;text-align:center;border:1px solid;">Unit </th>
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
	$p=1;$ttl_amt=0;$ttl_qty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;
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

		$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';

		$html.='<tr style="border:1px solid;">
		<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
		<td style="text-align:left;border:1px solid;vertical-align:top;">
		<strong>'.$trn_rel['product_name'].'</strong><br>'.$product_desc.'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">
		'.$trn_rel['unit_name'].'
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
				$html.= indian_number($trn_rel['product_amount'],2);
			}else{
				$html.= indian_number($trn_rel['product_amount'],2);
			}
		}

		$html.='</td>
		</tr>';
		$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
		if($trn_rel['act_amt_flag']!='1'){
			if($rel['quot_type']=='0'){
				$ttl_amt=$ttl_amt+($trn_rel['product_amount']);
			}else{
				$ttl_amt=$ttl_amt+($trn_rel['product_amount']);
			}
		}

		$p++;
	}

	$html.='<tr>
	<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>Basic Amount '.$currency_name.'</b></td>
	<td style="text-align:center;border:1px solid;"><b>
	'.indian_number($ttl_amt,2).'
	</b></td>
	</tr>';
	if($rel['qt_add_state']==$comp_rel['stateid']){
		$html.='<tr>
		<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>CGST</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		'.number_format(($total_cs_gst/2),2,".","").'
		</b></td>
		</tr>
		<tr>
		<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>SGST</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		'.number_format(($total_cs_gst/2),2,".","").'
		</b></td>
		</tr>';
	}else{
		$html.='<tr>
		<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>IGST</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		'.number_format(($total_i_gst),2,".","").'
		</b></td>
		</tr>';
	}
	$qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_quotation_trn as trn 
	left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
	left join tbl_ledger as l on l.l_id=tc.tax_id 
	where tc.tax_additional='1' and trn.quotation_id=".$rel['quotation_id']." and trn.quot_trn_status!=2 and tc.isdelete='0' group by tc.tax_id";
	$result11=$dbcon->query($qry11);		
	while($row11=mysqli_fetch_assoc($result11))
	{
		$html.='<tr>
		<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>'.$row11['l_name'].'</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		'.number_format($row11['add_sum'],2,".","").'
		</b></td>
		</tr>';
	}
	$qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
	from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
	left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
	where b.sundry_voucher_id=".$rel['quotation_id']." and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0'";
	$result12=$dbcon->query($qry12);		
	while($row12=mysqli_fetch_assoc($result12))
	{
		$html.='<tr>
		<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>'.$row12['l_name'].'</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		'.number_format($row12['sundry_amount'],2,".","").'
		</b></td>
		</tr>';
	}
	$html.='<tr>
	<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>Total Supply Amount '.$currency_name.'</td>
	<td style="text-align:center;border:1px solid;"><b>
	'.indian_number($rel['g_total'],2).'
	</b></td>
	</tr>
	</tbody>
	</table>';

	$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
	</body>
	</html>';
// echo $trn_qry;
// echo $html;exit;
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
$mpdf->SetHTMLFooter($footer);
//Show page number : Dimple Panchal (05-Apr-2021)
	// $mpdf->pagenumPrefix = ' ';
	// $mpdf->pagenumSuffix = ' / ';
	// $mpdf->nbpgPrefix = ' ';
	// $mpdf->nbpgSuffix = ' pages';
	// $mpdf->SetFooter('{PAGENO}{nbpg}');
	$mpdf->SetWatermarkText();
	$mpdf->showWatermarkText = true;
	$mpdf->allow_charset_conversion=true;
	$mpdf->charset_in='UTF-8';
	$mpdf->WriteHTML($html);
	$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
	ob_clean();
	return 'Quotation '.$quotation_id.'.pdf';
}

?>