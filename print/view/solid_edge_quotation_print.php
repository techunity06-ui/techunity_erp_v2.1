<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$incPath = $path.'include/';

$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';

$quotation_id = $_REQUEST['id'];	
$type='pdf';
if(strtolower($type) == 'pdf') {
//Quotation Data
	$query="select quot.*,cust.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,inq.inquiry_no,inq.inquiry_date, ref.rb_name from tbl_quotation as quot
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

	$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';

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
	$cust_mobile = ($rel['cust_mobile']) ? $rel['cust_mobile'] : "";
	$cust_email = ($rel['cust_email']) ? $rel['cust_email'] : "";
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
		<table style="font-size:14px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
		<thead>
		<tr>
		<td colspan="5" rowspan="5" style="text-align:left;vertical-align:top;border:1px solid;width:50%;"> 
		<strong>Party Name: <br>'.$rel['cust_name'].',</strong><br/>
		<strong>'.($quot_address).'<br>
		K/A :- '.$rel['c_con_fname'].' '.$rel['c_con_lname'].',<br />
		Mobile No : '.$cust_mobile.'<br>Email: '.$cust_email.'<br>
		GSTIN : '.$rel['cust_gst'].'</strong>
		</td>
		<td colspan="2" style="text-align:left;border:1px solid;width:20%;font-weight: bold;"> 
		Quotation No
		</td>
		<td colspan="3" style="text-align:left;border:1px solid;width:30%;"> 
		<strong>'.$rel['quotation_no'].'</strong>
		</td>
		</tr>
		<tr>
		<td colspan="2" style="text-align:left;border:1px solid;font-weight: bold;"> 
		Quotation Date
		</td>
		<td colspan="3" style="text-align:left;border:1px solid;"> 
		'.date("d-M-Y",strtotime($rel['quotation_date'])).'
		</td>
		</tr>
		<tr>
		<td colspan="2" style="text-align:left;border:1px solid;font-weight: bold;"> 
		Validity
		</td>
		<td colspan="3" style="text-align:left;border:1px solid;"> 
		'.date("d-M-Y",strtotime($rel['quotation_valid_date'])).'
		</td>
		</tr>
		<tr>
		<td colspan="2" style="text-align:left;border:1px solid;font-weight: bold;"> 
		Mode Of Dispatch
		</td>
		<td colspan="3" style="text-align:left;border:1px solid;"> 
		
		</td>
		</tr>
		<tr>
		<td colspan="2" style="text-align:left;border:1px solid;font-weight: bold;"> 
		Payment Terms
		</td>
		<td colspan="3" style="text-align:left;border:1px solid;"> 
		
		</td>
		</tr>
		<tr>
		<th style="text-align:center;border:1px solid;">Sr.<br/>No.</th>
		<th style="text-align:center;border:1px solid;" colspan="2">Particulars</th>
		<th style="text-align:center;border:1px solid;">HSN/SAC<br>Code</th>
		<th style="text-align:center;border:1px solid;">Qty</th>
		<th style="text-align:center;border:1px solid;">Rate<br>'.$currency_name.'</th>
		<th style="text-align:center;border:1px solid;">Taxable<br>Value</th>
		<th style="text-align:center;border:1px solid;">Tax Rate</th>
		<th style="text-align:center;border:1px solid;">Tax<br>Amount</th>
		<th style="text-align:center;border:1px solid;">Total<br>'.$currency_name.'</th>
		</tr>
		</thead>
		<tbody>';
		if($inquiry_type==1){
			$trn_qry="select trn.*,pro.product_name,unit.unit_name, hsn.hsn_code from tbl_quotation_trn as trn 
			left join product_mst as pro on pro.product_id=trn.product_id
			left join unit_mst as unit on unit.unitid=trn.unitid
			left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
			where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
		} else {
			$trn_qry="select trn.*,pro.product_name from tbl_quotation_project_trn as trn left join product_mst as pro on pro.product_id=trn.product_id where trn.quotation_projecttrn_status=0 and trn.quotation_id=".$rel['quotation_id'];
		}
		$trn_qry_rs=$dbcon->query($trn_qry);
		$p=1;$ttl_amt=0;$ttl_qty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;$pro_amount=0;
		$cnt=mysqli_num_rows($trn_qry_rs);
		while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
			$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
			$gst_rate = $trn_rel['cgst_tax_rate']+$trn_rel['sgst_tax_rate']+$trn_rel['igst_tax_rate'];

			$pro_amount = $pro_amount+$trn_rel['product_amount'];
			$pro_amt = $gst_rate+$trn_rel['product_amount'];
			$total_gst = $gst_rate+$total_gst;
			if($trn_rel['cgst_tax_rate'] != 0 || $trn_rel['sgst_tax_rate'] !=0){
				$total_cs_gst += $gst_rate;
			}else{
				$total_i_gst += $gst_rate;
			}
			$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';

			$html.='<tr style="border:1px solid;">
			<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
			<td style="text-align:left;border:1px solid;vertical-align:top;" colspan="2">
			<strong>'.$trn_rel['product_name'].'</strong><br/>
			'.$product_desc.'
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			'.$trn_rel['hsn_code'].'
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			'.$trn_rel['product_qty'].' '.$trn_rel['unit_name'].'
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			'.indian_number($trn_rel['product_rate'],2).'
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			'.indian_number($trn_rel['product_amount'],2).'
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			'.$gst_per.' %
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">'.indian_number($gst_rate,2).'</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">';
			if($trn_rel['act_amt_flag']=='1'){
				$html.="Extra At Actual";
			}
			else{
				if($rel['quot_type']=='0'){
					$html.= indian_number($pro_amt,2);
				}else{
					$html.= indian_number($pro_amt,2);
				}
			}

			$html.='</td>
			</tr>';
			$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
			if($trn_rel['act_amt_flag']!='1'){
				if($rel['quot_type']=='0'){
					$ttl_amt=$ttl_amt+$pro_amt;
				}else{
					$ttl_amt=$ttl_amt+$pro_amt;
				}
			}

			$p++;
		}
		if($cnt>5){
		$pr=10-$cnt;
		for($j=0; $j<$pr; $j++)
		{
			$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;height:40px;" colspan="2"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			</tr>';
		}
	}

		$html.='<tr>
		<td colspan="4" style="text-align:right;border:1px solid;"><b>Total</b></td>
		<td style="text-align:center;border:1px solid;"><b>'.indian_number($ttl_qty,2).'</b></td>
		<td style="text-align:center;border:1px solid;"></td>
		<td style="text-align:center;border:1px solid;"><b>'.indian_number($pro_amount,2).'</b></td>
		<td style="text-align:center;border:1px solid;"></td>
		<td style="text-align:center;border:1px solid;"><b>'.indian_number($total_gst,2).'</b></td>
		<td style="text-align:center;border:1px solid;"><b>'.indian_number($ttl_amt,2).'</b></td>
		</tr>';
	/*if($rel['stateid']==$set_head['stateid']){
		$html.='<tr>
		<td colspan="4" style="text-align:right;border:1px solid;"><b>CGST</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		'.number_format(($total_cs_gst/2),2,".","").'
		</b></td>
		</tr>
		<tr>
		<td colspan="4" style="text-align:right;border:1px solid;"><b>SGST</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		'.number_format(($total_cs_gst/2),2,".","").'
		</b></td>
		</tr>';
	}else{
		$html.='<tr>
		<td colspan="4" style="text-align:right;border:1px solid;"><b>IGST</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		'.number_format(($total_i_gst/2),2,".","").'
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
		<td colspan="4" style="text-align:right;border:1px solid;"><b>'.$row11['l_name'].'</b></td>
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
		<td colspan="4" style="text-align:right;border:1px solid;"><b>'.$row12['l_name'].'</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		'.number_format($row12['sundry_amount'],2,".","").'
		</b></td>
		</tr>';
	}*/
	$round_off = round($ttl_amt) - $ttl_amt;
	$html.='<tr>
	<td colspan="6" style="text-align:left;border:1px solid;"><b>Company GSTIN: </b>'.$comp_rel['vatno'].'</td>
	<td colspan="2" style="text-align:right;border:1px solid;"><b>Round Off</b></td>
	<td colspan="2" style="text-align:right;border:1px solid;"><b>'.indian_number($round_off,2).'</b></td>
	</tr>
	<tr>
	<td colspan="6" style="border:1px solid;text-align:left;"><b>Total (In Words): 
	'.ucfirst(convert_number_to_words_new(round($ttl_amt),$currency_word_start,$currency_word_end)).'</b></td>
	<td colspan="2" style="text-align:right;border:1px solid;"><b>Grand Total</b></td>
	<td colspan="2" style="text-align:right;border:1px solid;"><b>'.indian_number(round($ttl_amt),2).'</b></td>
	</tr>
	<tr>
	<td colspan="6" rowspan="2" style="vertical-align: top;">';
	$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
	left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
	where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
	$terms_qry_rs=$dbcon->query($terms_qry);
		$t=1;
		while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
			$string=(nl2br($term_rel['tc_details']));
			$html.='('.$t.') '.$string.'<br>';
			$t++;
		}
	$html.='</td>
	<td colspan="4" style="vertical-align: top;font-weight: bold;">
	For '.$comp_rel['company_name'].'<br>
	<img src="'.DOMAIN_F.'view/upload/signature/'.$comp_rel['authorized_signature'].'" style="height: 100px;width: 100px;"/>
	</td>
	</tr>
	<tr>
	<td colspan="4" style="font-weight: bold; text-align: center;">Authorized Signature</td>
	</tr>
	</tbody>
	</table>
	</div>';
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
// echo $trn_qry;
// echo $html;exit;
	ob_end_clean();
	include("../../view/export/mpdf/mpdf.php");
	$mpdf=new mPDF('','A4','0','proximanova','10','10','37','10','1','1');
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
	$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
	ob_clean();
	return 'quotation'.$quotation_id.'.pdf';
}

?>
