<?php 
session_start();
include("../../config/config.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	QUOTATION_SLUG_PRINT
]);

if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
$form="SALES QUOTE";       
$quotation_id = $_REQUEST['id'];	
$type='pdf';
if(strtolower($type) == 'pdf') {
//Quotation Data
	$query="SELECT quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, cust.cust_gst from tbl_quotation as quot
	left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
	left join tbl_customer as cust on cust.cust_id=quot.cust_id
	left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
	where quot.quotation_id=".$quotation_id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));

	$com_mobile= ($rel['cust_mobile']) ? $rel['cust_mobile'] : '';
	$com_email= ($rel['cust_email']) ? $rel['cust_email'] : '';
//Company Data
	$set="SELECT comp.*,state.state_name,state.gst_state_code FROM tbl_company AS comp LEFT JOIN state_mst AS state on comp.stateid=state.stateid WHERE company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));

	$header ='<table style="width: 100%; border: none;">
	<tbody>
	<tr style="border: none;">
	<td style="width: 65%; border: none; vertical-align: top; text-align: left"><img src="'.DOMAIN_F.LOGO.$set_head['logo'].'" /></td>
	<td style="width: 35%; border: none; text-align: right;"><h1>'.$form.'</h1></td>
	</tr>
	</tbody>
	</table>';
	$footer ='<table style="width: 100%; border: none;">
	<tbody>
	<tr style="border: none;">
	<td style="border: none; text-align: center"><strong>THANK YOU FOR YOUR BUSINESS!</strong></td>
	</tr>
	<tr style="border: none; border-top: 1px solid;">
	<td style="border: none; text-align: center; font-size: 10px;">Note: If correction required, correction shall be made by the process owner of the report by striking the error out and initial it with the date.</td>
	</tr>
	<tr style="border: none;">
	<td style="border: none; text-align: right; font-size: 10px;">FR 301-01 Issue 2 Rev 2</td>
	</tr>
	</tbody>
	</table>'; 
	$approve_status='';
	if($rel['approve_status']=='0'){
		$approve_status=' (DRAFT)';
	}
	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));

	$user_phone = ($userData['user_phone']) ? $userData['user_phone'] : '';

		$colspan=2;
		$cols=5;
	// $cols=$colspan;
	$rows=3;

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
			border:1px solid !important;
			/*page-break-inside:avoid;*/
		}
		.quot_annex_content_div table tr,td{
			padding:5px;
		}

		</style>
		</head>
		<body>

		<htmlpageheader name="otherpages" style="display:none">
		<div style="text-align:center">'.$header.'</div>
		</htmlpageheader>
		<htmlpagefooter name="otherpages_footer" style="display:none">
		<div style="text-align:center">'.$footer.'</div>
		</htmlpagefooter>
		<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
		<div>
		<table style="font-size:12px; border:none; width:100%; table-layout: fixed;">
		<tr style="border:none;">
		<td style="vertical-align: top;border:none; width: 60%;" rowspan="4"><strong>Sold To:<br>'.$rel['cust_name'].'</strong><br><strong>Address:</strong> '.(nl2br($rel['quot_address'])).'<br><strong>Mobile:</strong> '.$com_mobile.'<br><strong>Email:</strong> '.strtolower($com_email).'<br><strong>Cstomer GSTIN No:</strong> '.$rel['cust_gst'].'</td>
		<td style="font-weight: bold;border:none; border-right: 1px solid;width: 20%; text-align: right;">SALES NUMBER</td>
		<td style="width: 20%;border:none;">'.$rel['quotation_no'].'</td>
		</tr>
		<tr style="border:none;">
		<td style="border:none;border-right: 1px solid;font-weight: bold;text-align: right;">SALES DATE</td>
		<td style="border:none;">'.date("d-M-Y",strtotime($rel['quotation_date'])).'</td>
		</tr>
		<tr style="border:none;">
		<td style="border:none;border-right: 1px solid;font-weight: bold;text-align: right;">FROM</td>
		<td style="border:none;"></td>
		</tr>
		<tr style="border:none;">
		<td style="border:none;border-right: 1px solid;font-weight: bold;text-align: right;">YOUR ORDER NO.</td>
		<td style="border:none;">'.$rel['order_no'].'</td>
		</tr>
		<tr style="border:none;">
		<td style="vertical-align: top;border:none;" rowspan="3"><strong>Correspondence To:<br>Name: </strong>'.$rel['c_con_fname'].' '.$rel['c_con_lname'].'<br><strong>Mobile:</strong> '.$rel['c_con_email'].'<br><strong>Email:</strong> '.strtolower($rel['c_con_mobile']).'</td>
		<td style="border:none;border-right: 1px solid;font-weight: bold;text-align: right;">DELIVERY NO.</td>
		<td style="border:none;">'.$rel['delivery_no'].'</td>
		</tr>
		<tr style="border:none;">
		<td style="border:none;border-right: 1px solid;font-weight: bold;text-align: right;">SHIPPED VIA </td>
		<td style="border:none;">'.$rel['shipped_via'].'</td>
		</tr>
		<tr style="border:none;">
		<td style="border:none;border-right: 1px solid;font-weight: bold;text-align: right;">TERMS</td>
		<td style="border:none;">'.$rel['terms'].'</td>
		</tr>
		</table>
		<table style="font-size:12px;border-collapse: collapse;width:100%; margin-top: 20px;" class="table table-striped">
		<thead>
		<tr style="border: 1px solid;">
		<th style="border: 1px solid; width:8%;text-align:center; height: 30px;">ITEM</th>
		<th style="border: 1px solid; width:8%;text-align:center;">QTY</th>
		<th style="border: 1px solid; width:60%;text-align:center;">DESCRIPTION</th>
		<th style="border: 1px solid; width:14%;text-align:center;">UNIT PRICE</th>
		<th style="border: 1px solid; width:10%;text-align:right;">AMOUNT</th>
		</tr>
		</thead>
		<tbody>';
		$trn_qry="select trn.*,pro.product_name,unit.unit_name from tbl_quotation_trn as trn 
		left join product_mst as pro on pro.product_id=trn.product_id
		left join unit_mst as unit on unit.unitid=trn.unitid
		left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
		where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
		$trn_qry_rs=$dbcon->query($trn_qry);
		$p=1;$ttl_amt=0;$ttl_qty=0; $total_gst=0;$total_i_gst=0;$gst_per=0;
		$cnt=mysqli_num_rows($trn_qry_rs);
		while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
			$pro_descri = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '' ;
			$pro_disc = ($trn_rel['discount_per']) ? $trn_rel['discount_per']." %" : '';
			$back = ($p%2!=0) ? "background: #dfebf5;" : "";
			$html.='<tr style="border-left: 1px solid;border-right: 1px solid;'.$back.'">
			<td style="text-align:center;vertical-align:top; border-left: 1px solid;">'.$p.'</td>
			<td style="text-align:center;vertical-align:top;">
			'.$trn_rel['product_qty'].'
			</td>
			<td style="text-align:left;vertical-align:top;">
			<strong>'.$trn_rel['product_name'].'</strong><br/>
			'.$pro_descri.'
			</td>
			<td style="text-align:center;vertical-align:top;">'.number_format($trn_rel['product_rate'],2,".","").'</td>';
			$html.='<td style="text-align:right;vertical-align:top;border-right: 1px solid;">'.number_format($trn_rel['product_amount'],2,".","").'</td>
			</tr>';
			$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
			if($trn_rel['act_amt_flag']!='1'){
				$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
			}

			$p++;
		}
		$pr=7-$cnt;
		for($j=0; $j<$pr; $j++)
		{
			$back = ($j%2!=0) ? "background: #dfebf5;" : "";
			$html.='<tr style="'.$back.'">
			<td style=""></td>
			<td style=""></td>
			<td style="height:25px;"></td>
			<td style=""></td>
			<td style=""></td>
			</tr>';
		}
		$remark = ($rel['quot_remark']) ? $rel['quot_remark'] : '';

		$html.='<tr>
		<td rowspan="'.$rows.'" style="text-align:left; vertical-align: top; font-weight: bold;"></td>
		<td colspan="'.$colspan.'" rowspan="'.$rows.'" style="text-align:left; vertical-align: top;">We trust that the above quotation is acceptable to you and we look forward to your confirmed order.</td>
		<td style="text-align:right; font-weight: bold;">SUBTOTAL</td>
		<td style="text-align:right;font-weight: bold;">'.number_format($ttl_amt,2,".","").'</td>
		</tr>
		<tr>
		<td style="text-align:right; font-weight: bold; height: 25px;"></td>
		<td style="text-align:right; font-weight: bold;"></td>
		</tr>
		<tr>
		<td style="text-align:right; font-weight: bold; white-space: nowrap;">TOTAL AMOUNT</td>
		<td style="text-align:right; font-weight: bold;">'.number_format((float)($ttl_amt), 2, '.', ',').'</td>
		</tr>
		<tr>
		<td colspan="3"></td>
		<td style="text-align:right; font-weight: bold;">CURRENCY</td>
		<td style="text-align:right; font-weight: bold;">'.(($rel['quot_type']=='0') ? "INR" : $currency_name).'</td>
		</tr>';
		$html.='</tbody></table>
		<div style="clear:both;"></div>';
		if(trim($rel['quot_annex_content'])){
	$html.='<h3 style="text-align:left; text-decoration: underline; font-weight: bold;">SCOPE OF WORKS:</h3>
	<div class="quot_annex_content_div">'.$rel['quot_annex_content'];
	$html.='</div>';
}

		$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
		left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
		where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
		$terms_qry_rs=$dbcon->query($terms_qry);
		if(mysqli_num_rows($terms_qry_rs)){
			$html .= '<h3 style="text-align:left; text-decoration: underline; font-weight: bold;">TERM & CONDITIONS:</h3>
			<div><table width="100%" style="font-size:12px;border: none;width:100%;overflow:wrap;"><tbody>';
			while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
				$string=(nl2br($term_rel['tc_details']));

				$html.='<tr style="border: none;">
				<td width="25%" style="border: none; width:25%;text-align:left;padding:5px; vertical-align: top; font-weight: bold;">'.$term_rel['tc_name'].'</td>
				<td width="75%" style="border: none; width:70%;text-align:left;padding:5px;">: '.$string.'</td>
				</tr>';
			}
			$html.='</tbody></table></div>';	
		}
		/* Get Bom of product end */

		$html.='<!--page1 end-->';
$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
</body>
</html>';
// echo $html;exit;
ob_end_clean();
include("../../view/export/mpdf/mpdf.php");
$mpdf=new mPDF('UTF-8','A4','0','calibri','10','10','40','25','1','1');
$mpdf->SetDefaultFont('opensans');
$mpdf->SetFont('opensans');
$mpdf->defaultheaderfontsize = 10; /* in pts */
$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
$mpdf->defaultfooterfontsize = 10; /* in pts */
$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLFooter($footer);
// $mpdf->pagenumPrefix = ' ';
// $mpdf->pagenumSuffix = ' / ';
// $mpdf->nbpgPrefix = ' ';
// $mpdf->nbpgSuffix = ' pages';
// $mpdf->SetFooter('{PAGENO}{nbpg}');
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