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

/*if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}*/

$quotation_id = $_REQUEST['id'];	
$type='pdf';
$type_mail = $_REQUEST['type'];   // create pdf for attach in email ::  Added by Sanat :: 12-08-21
if(strtolower($type) == 'pdf') {
//Quotation Data
	$query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_gst,cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name from tbl_quotation as quot
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
//CHEMITEK PROCESS EQUIPMENTS PRIVATE LIMITED		
//>Plot No : 117 , Nr. GETCO Sub -Station , Old GIDC , Gundlav Valsad - 396035, Gujarat, India
$comp_rel=mysqli_fetch_assoc($dbcon->query($set));

if($rel['quot_type']=='0'){
	$currency_name = '(INR)';
	$currency_word_start = 'Rupees';
	$currency_word_end = 'Paise';
}else{
	$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
	$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

	$currency_name = '('.ucwords(strtolower($currency_rel['currency_code'])).')';
	$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
	$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
}

$html='';
$header ='
<table>
<tr>
<td colspan="3" style="border: 0px; "><img src="'.DOMAIN_F.LOGO.$comp_rel['logo'].'" style="width:2in;height: 70px;padding-top:0px;" /></td>
<td colspan="6" style="text-align:center;border: 0px;">
<span style="font-size:16px;"><b>QUOTATION </b></span><br/>
<span style="font-size:16px;"><b>'.$comp_rel["company_name"].' </b></span><br/>

<span style="font-size:12px;">'.$comp_rel["address"].'</span>
</td>
</tr>
</table>';

$html.='<html>
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
	<!--<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>-->
	<div>
	<table><tr style="border-bottom: 0px; ">
	<td colspan="2" style="border: 0px; font-size:12px;"><b>GSTIN</b> : '.$comp_rel["vatno"].'</td>
	<td colspan="4" style="border: 0px; font-size:12px;"><b>CIN &nbsp;&nbsp;   </b>: '.$comp_rel['cin'].' </td>
	<td colspan="3" style="border: 0px; font-size:12px;"><b>PAN &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;   </b> : '.$comp_rel["pan_no"].'</td>
	</tr>
	<tr style="border-top: 0px; ">
	<td colspan="2" style="border: 0px; font-size:12px;"><b>Phone </b>: '.$comp_rel["contact_no"].'</td>
	<td colspan="4" style="border: 0px; font-size:12px;"><b>Email </b>: '.$comp_rel["website"].'</td>
	<td colspan="3" style="border: 0px; font-size:12px;"><b>Website </b>:'.$comp_rel["company_website"].'</td>
	</tr>
	<tr >
	<tr >
	<td colspan="2" style="border: 0px; font-size:12px;"><b>SQ No</b> : '.$rel['quotation_no'].'</td>
	<td colspan="4" style="border: 0px; font-size:12px;"><b>SQ Date  </b> : '.date("d-M-Y",strtotime($rel['quotation_date'])).'</td>
	<td colspan="3" style="border: 0px; "></td>
	</tr>
	</tr>
	<tr style="background-color:	#b3b3b3">
	<td colspan="4" style=" text-align:center;"><b>Customer Details</b> </td>
	<td colspan="5" style=" text-align:center;"><b>Other Details  </b></td>
	</tr>
	<tr style="border:none; border-left:1px solid; border-right:1px solid;">
	<td colspan="4" style="border-left:1px solid;border-bottom:none; text-align:left;font-size:12px;">'.$rel['cust_name'].' </td>
	<td colspan="5" style="border-bottom:none;  text-align:left;font-size:12px;">Reference :'.$rel['quotation_ref'].'</td>
	</tr>
	<tr style="border:none; border-left:1px solid; border-right:1px solid;">
	<td colspan="4" style="border-left:1px solid;border-bottom:none;  text-align:left;font-size:12px;">
	'.$rel['quot_address'].'</td>
	<td colspan="5" style="border-bottom:none;  text-align:left; font-size:12px;"> Validity :'.date("d-M-Y",strtotime($rel['quotation_date'])).'</td>
	</tr>
	<tr style="border:none; border-left:1px solid; border-right:1px solid;">
	<td colspan="4" style="border-left:1px solid; text-align:left;font-size:12px;">GSTIN : '.$rel['cust_gst'].' </td>
	<td colspan="5" style=" text-align:left;font-size:12px;"></td>
	</tr>
	<tr style="border:none; border-left:1px solid; border-right:1px solid; ">
	<td colspan="4" style=" text-align:left;font-size:12px;">
	Contact Name:'.$rel['c_con_fname'].' '.$rel['c_con_lname'].' </td>
	<td colspan="5" style=" text-align:left;"></td>
	</tr>
	<tr style="border:none; border-left:1px solid; border-right:1px solid; ">
	<td colspan="4" style=" text-align:left;font-size:12px;">Contact No:'.$rel['c_con_mobile'].'</td>
	<td colspan="5" style=" text-align:left;"></td>
	</tr>
	<tr style="border:none; border-left:1px solid; border-right:1px solid; ">
	<td colspan="4" style=" text-align:left;font-size:12px;">
	Email ID :'.$rel['cust_email'].'</td>
	<td colspan="5" style=" text-align:left;"></td>
	</tr>
	<tr >
	<td colspan="9" style=" text-align:left;font-size:12px;">
	With reference to your inquiry, we are pleased to submit our offer as below:</td>
	</tr>
	
	</table>
	</div>
	<table style="font-size:16px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
	<thead>
	<tr style="background-color:	#b3b3b3">
	<td style=" text-align:center;"><b>Sr No.</b> </td>
	<td  style=" text-align:center;"><b>Part Code / Goods Description</b></td>
	<td colspan="2"  style=" text-align:center;"><b>HSN Code</b> </td>
	<td style=" text-align:center;"><b>UOM</b> </td>
	<td style=" text-align:center;"><b>Qty</b> </td>
	<td style=" text-align:center;"><b>Rate (INR)</b> </td>
	<td style=" text-align:center;"><b>Disc %</b> </td>
	<td style=" text-align:center;"><b>Amount (INR)</b> </td>
	</tr>
	</thead>
	<tbody>';
	$trn_qry="select trn.*,pro.product_icode,pro.product_name,pro.product_type,pro.product_hsn,unit.unit_name,image.im_name, hsn.hsn_code 
	from tbl_quotation_trn as trn 
	left join product_mst as pro on pro.product_id=trn.product_id
	left join mst_hsn_code as hsn on pro.product_hsn=hsn.hsn_id
	left join tbl_product_images as image on image.im_product = trn.product_id
	left join unit_mst as unit on unit.unitid=trn.unitid
	where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id']." ORDER BY `trn`.`quot_trn_id` ASC";

	$trn_qry_rs=$dbcon->query($trn_qry);
	$p=1;$ttl_amt=0;$ttl_qty=0;
	$cnt=mysqli_num_rows($trn_qry_rs);
	$align=""; $total_gst = 0;
	while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
		$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
		$gst_rate = $trn_rel['cgst_tax_rate']+$trn_rel['sgst_tax_rate']+$trn_rel['igst_tax_rate'];

		if($trn_rel['cgst_tax_rate'] != 0 || $trn_rel['sgst_tax_rate'] !=0){
			$total_gst += $total_cs_gst += $gst_rate;
		}else{
			$total_gst += $total_i_gst += $gst_rate;
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

		$product_desc = ($trn_rel['product_desc']!='0') ? nl2br($trn_rel['product_desc']) : '';
		$discount_per = ($trn_rel['discount_per']!='0') ? $trn_rel['discount_per'] : '';
		$path='view/upload/product_images/';
		$html.='
		<tr>
		<td style="text-align:center;font-size:12px;">'.$p.' </td>
		<td style="border: none;text-align:left;font-size:12px;">
		<table style="width:100%;">
		<tr border="0" style="border-radious: 0px; border: none;">';
		if($trn_rel['im_name']!=''){
			$html.='<td border="0" style="border-radious: 0px; border: none!important;width:30%;font-size:12px;'.$align.'"><img src="'.ROOT.$path.$trn_rel['im_name'].'" height="50" width="50" class="img-thumbnail" /></td>';
		}else{
			$html.='<td border="0" style="border-radious: 0px; border: none!important;width:30%;font-size:12px;'.$align.'"></td>';
		}
		if($trn_rel['product_type']=='8'){

			$html.='<td border="0" style="border-radious: 0px; border: none!important;font-size:12px;text-align:right;">Item Code:'.$trn_rel['product_icode'].'<br/>'.$trn_rel['product_name'].'<br/>'.$product_desc.'</td>'; 
		}else{
			$html.='<td border="0" style="border-radious: 0px; border: none!important;font-size:12px;">Item Code:'.$trn_rel['product_icode'].'<br/>'.$trn_rel['product_name'].'<br/>'.$product_desc.'</td>'; 
		}


		$html.='</tr>
		</table>
		</td>
		<td colspan="2" style=" text-align:center;font-size:12px;"> '.$trn_rel['hsn_code'].'</td>
		<td style=" text-align:center;font-size:12px;">'.$trn_rel['unit_name'].' </td>
		
		<td style=" text-align:center;font-size:12px;">'.$trn_rel['product_qty'].' </td>
		<td style=" text-align:center;font-size:12px;"> '.$trn_rel['product_rate'].'</td>
		<td style=" text-align:center;font-size:12px;">'.$discount_per.'</td>
		<td style=" text-align:center;font-size:12px;"> '.$trn_rel['product_amount'].'</td>
		</tr>';

		$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
		if($trn_rel['act_amt_flag']!='1'){
		//Amish Soni Start 15-03-2021
//		$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
			$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
		//Amish Soni End 15-03-2021
		}

		$p++;
	}
	$pr=10-$cnt;

	$html.='<tr>
	<td colspan="8" style=" text-align:right;font-size:12px;">Taxable Amount  </td>
	<td  style=" text-align:center;font-size:12px;">'.indian_number($ttl_amt,2).'</td>
	</tr>';
	$final_total = 0;
	$final_total = $ttl_amt + $total_gst;
	if($rel['qt_add_state']==$comp_rel['stateid']){
		$html .= '<tr>
		<td colspan="8" style=" text-align:right;font-size:12px;">CGST</td>
		<td  style=" text-align:center;font-size:12px;">'.number_format(($total_cs_gst/2),2,".","").'</td>
		</tr>
		<tr>
		<td colspan="8" style=" text-align:right;font-size:12px;">SGST</td>
		<td  style=" text-align:center;font-size:12px;">'.number_format(($total_cs_gst/2),2,".","").'</td>
		</tr>';
	}else{
		$html .= '<tr>
		<td colspan="8" style=" text-align:right;font-size:12px;">IGST</td>
		<td  style=" text-align:center;font-size:12px;">'.number_format(($total_i_gst),2,".","").'</td>
		</tr>';
	}
	$qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_quotation_trn as trn 
	left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
	left join tbl_ledger as l on l.l_id=tc.tax_id 
	where tc.tax_additional='1' and trn.quotation_id=".$rel['quotation_id']." and trn.quot_trn_status!=2 and tc.isdelete='0' group by tc.tax_id";
	$result11=$dbcon->query($qry11);		
	while($row11=mysqli_fetch_assoc($result11))
	{
		$html .= '<tr>
		<td colspan="8" style=" text-align:right;font-size:12px;">'.$row11['l_name'].'</td>
		<td  style=" text-align:center;font-size:12px;">'.number_format($row11['add_sum'],2,".","").'</td>
		</tr>';
	}
	$qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
	from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
	left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
	where b.sundry_voucher_id=".$rel['quotation_id']." and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0'";
	$result12=$dbcon->query($qry12);		
	while($row12=mysqli_fetch_assoc($result12))
	{
		$html .= '<tr>
		<td colspan="8" style=" text-align:right;font-size:12px;">'.$row12['l_name'].'</td>
		<td  style=" text-align:center;font-size:12px;">'.number_format($row12['sundry_amount'],2,".","").'</td>
		</tr>';
	}
	$round_off = round($final_total)-$final_total;
	$html .= '
	<tr>
	<td colspan="8" style=" text-align:right;font-size:12px;">Item Sub Total </td>
	<td  style=" text-align:center;font-size:12px;">'.indian_number($rel['g_total'],2).'</td>
	</tr>
	<tr>
	<td colspan="8" style=" text-align:right;font-size:12px;">Round Off  </td>
	<td  style=" text-align:center;font-size:12px;">'.indian_number($round_off,2).'</td>
	</tr>
	<tr>
	<td colspan="8" style=" text-align:right;font-size:12px;">Grand Total </td>
	<td  style=" text-align:center;font-size:12px;">'.indian_number($rel['g_total'],2).' </td>
	</tr>';

	$html.='</tbody></table>';
	$html.='<table style="page-break-inside: avoid;">
	
	<tr style="background-color:	#b3b3b3">
	<td colspan="9" style=" text-align:left;font-size:12px;">
	Total Amount In Words: '.(($comp_rel['currency_id']==$rel['currency_id']) ? ucfirst(convert_number_to_words_new($rel['g_total'],$currency_word_start,$currency_word_end)) : ucfirst(convert_number_to_words($rel['g_total'],$currency_word_start,$currency_word_end))).'</td>
	</tr>
	<tr style=" ">
	<td colspan="9" style="border-bottom: 1px;text-align:left;font-size:12px;">
	Remarks :'.(($rel['quot_remark']) ? $rel['quot_remark'] : '').'</td>
	</tr>
	<!--<tr style="border-bottom: 1px;border-top: 0px; ">
	<td colspan="9" >
	</td>
	</tr>-->

	
	</table>

	';
	/*$html.='';*/

	/* Get Terms And Condition Start */
	$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
	left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
	where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
	$terms_qry_rs=$dbcon->query($terms_qry);
	if(mysqli_num_rows($terms_qry_rs)){
		$html.='
		<table style="font-size:12px;page-break-inside: avoid;"><tbody>
		<tr style="">
		<td colspan="2" style=" text-align:center;font-size:12px;border-top: 1px;">
		Terms & Conditions</td>
		</tr>';
		$t=1;
		while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
			$string=(nl2br($term_rel['tc_details']));

			$html.='<tr style="border-bottom: 0px;border-top: 0px; "><td style="border: 0px;  padding-top: 0px;">'.$term_rel['tc_name'].':</td>
			<td width="80%" style="border: 0px; width:70%;text-align:left;padding:5px;">'.$string.'</td>

			</tr>';
			$t++;
		}
		if($t>1){
			$html.='<tr style="border-bottom: 0px;border-bottom: 1px; ">
			<td colspan="2" style="border-top: 1px;"></td>
			</tr>';
		}
		$html.='</tbody>

		</table>';	
	}

	$path_sign='view/upload/product_images/';
	$html.='<table style="page-break-inside: avoid;" >';
	$html.='
	<tr>
	<td colspan="10" style=" text-align:left;font-size:12px;">
	We hope you will find our offer most suitable and will favour us with your valued order for our prompt action.</td>
	</tr>
	<tr style="border-bottom: 0px; ">
	<td colspan="10" style=" text-align:right;font-size:12px;">
	For , '.$comp_rel["company_name"].'</td>
	</tr>
	<tr style="border-bottom: 0px;border-top: 0px; ">
	<td colspan="10" style=" text-align:right;font-size:12px;">
	<img src="'.ROOT.LOGO.'sign.png" height="50" width="150" class="img-thumbnail" /></td>
	</tr>
	<tr style="border-top: 0px; ">
	<td colspan="10" style=" text-align:right;font-size:12px;">
	Authorized Signature</td>
	</tr>';
	$html.='</table>';
	/* Get Terms And Condition Start */

	/*Annexure Content Print Strat*/
	if(!empty($rel['quot_annex_content'])){
		$html.= '<pagebreak page-break-type="clonebycss" />'.$rel['quot_annex_content'];
	}
	/*Annexure Content Print End*/

	$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
// echo $header;
// echo $html;exit;
	ob_end_clean();
	include("../../view/export/mpdf/mpdf.php");
	$mpdf=new mPDF('','A4','0','calibri','10','10','30','10','1','1');
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
// 		$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');

		/*
		 *  Created by Sanat ::  12-08-2021
		 	Comment ::  added type_mail for create pdf and attach in send_quotation_mail
		 	START
		*/
		 	if($type_mail){
		 		$mpdf->Output('../quotation'.$quotation_id.'.pdf','F');
		 		echo 'quotation'.$quotation_id.'.pdf';
		 	}else{
		 		$mpdf->Output();
		 		return 'quotation'.$quotation_id.'.pdf';
		 	}

		/*
		 *  Created by Sanat ::  12-08-2021
		 	END
		*/

		 	ob_clean();
		 	return 'Quotation_'.$quotation_id.'.pdf';
		 }

		?>