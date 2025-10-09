<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$include = '../../include/';

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	QUOTATION_SLUG_PRINT
]);

if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}

$quotation_id = $_REQUEST['id'];	
$type='pdf';
if(strtolower($type) == 'pdf') {
//Quotation Data
  $query="select quot.*,per.c_con_fname,cuser.user_name,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile,cadd.c_add_state, inq.inquiry_no,inq.inquiry_date from tbl_quotation as quot
	left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
	left join tbl_customer as cust on cust.cust_id=quot.cust_id
	left join tbl_cust_address as cadd on cadd.cust_id=quot.cust_id
	left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
	left join users as cuser on cuser.user_id=quot.user_id
	where quot.quotation_id=".$quotation_id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));

	if(!$rel){
		header("Location: ".ROOT.CRM_ROOT."quotation_list");
	}
//Company Data
/*$comp_qry="select * from tbl_company as comp 
where comp.company_id=".$rel['company_id'];
*/
$set="SELECT comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
$comp_rel=mysqli_fetch_assoc($dbcon->query($set));

$user = "SELECT * from users where user_id=".$_SESSION['user_id'];
$user_rel = mysqli_fetch_assoc($dbcon->query($user));

if($rel['currency_id']=='0'){
	$currency_name = '(INR)';
	$currency_word_start = 'Rupees';
	$currency_word_end = 'Paise';
}else{
	$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
	$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

	//$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
	$currency_name = '('.strtoupper($currency_rel['currency_code']).')';
	$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
	$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
}

$user_app = "SELECT approve.*, user.user_name from tbl_quot_aprv_log as approve LEFT JOIN users as user ON user.user_id = approve.user_id where approve.approve_status = 1 AND approve.quotation_id=".$quotation_id." ORDER BY approve.quot_aprv_log_id DESC LIMIT 1";
$user_rels = mysqli_fetch_assoc($dbcon->query($user_app));

$header ='<img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:8.27in;" />
<table width="100%">
<tr>
<td colspan="3" style="text-align:center;font-weight:bold;border-bottom:1px solid"> Quotation'.$approve_status.'</td>
</tr>
<tr>
<td style="text-align:left;vertical-align:top;width:60%;"> <strong>M/S. '.$rel['cust_name'].'</strong></td>
<td style="text-align:left;width:20%;vertical-align:top;"> <strong>Quotation No</strong></td>
<td style="text-align:left;width:20%;vertical-align:top;"> <strong> : '.$rel['quotation_no'].'</strong></td>
</tr>
</table>'; 
$footer ='<img src="'.DOMAIN_F.LOGO.$comp_rel["f_logo"].'" style="width:8.27in;" />
<table width="100%">

<tr>
<td style="text-align:right;vertical-align:top;width:60%; bold;border-bottom:1px solid"> <strong>Page {PAGENO} of {nbpg}</strong></td>
</tr>
</table>';
$approve_status='';
if($rel['approve_status']=='0'){
	$approve_status=' (DRAFT)';
}

$companyConfiguration=getCompanyConfiguration($dbcon);
$crm_pro_print=explode(",", $companyConfiguration['crm_pro_print']);

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

	table tr,td,th,div{
		font-size:12px;
		/*border:1px solid #000 !important;*/
		/*page-break-inside:avoid;*/
	}
	.quot_annex_content_div table tr,td{
		padding:5px;
	}
	
	* {
  box-sizing: border-box;
}

.box {
  float: left;
  width: 1%;
  padding: 10px;
}
.box1 {
  float: left;
  width: 70%;
  padding: 10px;
}
.clearfix::after {
  content: "";
  clear: both;
  display: table;
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
	<table style="width:100%;  white-space: nowrap;">
	<tr>
	<td style="text-align:left;width:60%;">'.(($rel['quot_address']) ? $rel['quot_address'] : '').'<br>Ph. No. : '.$rel['cust_mobile'].'<br>Email : '.strtolower($rel['cust_email']).'</td>
	<td style="text-align:left;width:20%; vertical-align:top;"><strong>Quotation Date</strong></td>
	<td style="text-align:left;width:20%; vertical-align:top;"> <strong> : '.date("d-M-Y",strtotime($rel['quotation_date'])).'</strong></td>
	</tr>
	<tr>
	<td style="text-align:left; vertical-align:top;"> <strong>Your Ref. : '.(($rel['quotation_ref']) ? $rel['quotation_ref'] : '').'</strong></td>
	<td style="text-align:left;"> <strong>Ref. Date </strong></td>
	<td style="text-align:left;"> <strong> : '.date("d-M-Y",strtotime($rel['quotation_date'])).'</strong></td>
	</tr>
	<tr >
	<td style="text-align:left;"> <strong>Kind Attn. : '.$rel['c_con_fname'].' '.$rel['c_con_lname'].'</strong><br>Contact No. : '.$rel['c_con_mobile'].'<br>Email Id : '.strtolower($rel['c_con_email']).'</td>
	<td style="text-align:left;"></td>
	</tr>
	<tr>
	<td colspan="3" style="text-align:left; border-top: 1px solid;"> '.(($rel['quot_header']) ? $rel['quot_header'] : '').'</td>
	</tr>
	</table>
	<table style="width:100%; white-space: nowrap;">
	<thead>
	<tr>
	<th style="width:5%;text-align:center;border-top:1px solid;border-bottom:1px solid;">No.</th>
	<th style="width:38%;text-align:left;border-top:1px solid;border-bottom:1px solid">Description</th>
	<th style="width:8%;text-align:center;border-top:1px solid;border-bottom:1px solid">HSN/SAC</th>
	<th style="width:8%;text-align:center;border-top:1px solid;border-bottom:1px solid">Quantity</th>
	<th style="width:10%;text-align:center;border-top:1px solid;border-bottom:1px solid">Rate <br>'.$currency_name.'</th>
	<th style="width:5%;text-align:center;border-top:1px solid;border-bottom:1px solid">UOM</th>
	<th style="width:6%;text-align:center;border-top:1px solid;border-bottom:1px solid">Disc.%</th>
	<th style="width:6%;text-align:center;border-top:1px solid;border-bottom:1px solid">GST %</th>
	<th style="width:14%;text-align:center;border-top:1px solid;border-bottom:1px solid">Amount <br>'.$currency_name.'</th>
	</tr>
	</thead>
	</table>';
	/////////////////////////////////////////////////////Transaction Data start -Harshil/////////////////////////////////////////////////
			
	
	 $trn_qry="select trn.*,pro.product_name,unit.unit_name,trn.product_spec as spe, hsn.hsn_code as product_hsn,hsn.sale_gst,ttc.tax_gst, pro.product_alias_name from tbl_quotation_trn as trn 
	left join product_mst as pro on pro.product_id=trn.product_id
	left join unit_mst as unit on unit.unitid=trn.unitid
	left join mst_hsn_code as hsn on hsn.hsn_id=pro.product_hsn
	left join tbl_tax_category as ttc on ttc.tax_cat_id =hsn.sale_gst
	where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
	$trn_qry_rs=$dbcon->query($trn_qry);
	$p=1;$ttl_amt=0;$ttl_qty=0;$total_gst=0;$total_i_gst=0;
	$cnt=mysqli_num_rows($trn_qry_rs);
	while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
	    $alias_name = '';
			if(in_array('alias',$crm_pro_print)){
				$alias_name = " -- (".$trn_rel['product_alias_name'].")";
			}
				$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
				$gst_rate = $trn_rel['cgst_tax_rate']+$trn_rel['sgst_tax_rate']+$trn_rel['igst_tax_rate'];
		
			if($trn_rel['cgst_tax_rate'] != 0 || $trn_rel['sgst_tax_rate'] !=0){
					$total_cs_gst += $gst_rate;
				}else{
					$total_i_gst += $gst_rate;
				}
			
		$html.='<table><tbody>
		<tr style="border:none;">
		<td style="width:5%;text-align:center;vertical-align:top;">'.$p.'</td>
		<td style="width:40%;text-align:left;vertical-align:top; ">'.$trn_rel['product_name'].'<br>'.nl2br($trn_rel['product_desc']).'</strong>';

		if($rel['delivery_type']=='product_wise'){
			$retu_date = "select sdate.*,unit.unit_name from tbl_quotation_delivery_date as sdate left join unit_mst as unit on unit.unitid=sdate.unit_id where sdate.quo_delivery_date_status=0 and quot_trn_id=".$trn_rel['sales_ordertrn_id'];
			$resadate=$dbcon->query($retu_date);
			while($rowdate = brp_mysqli_fetch_array($resadate)){
				$html.='<br>Delivery Schedule :'.date('d-m-Y',strtotime($rowdate['delivery_date'])).' Qty : '.$rowdate['product_qty'].' '.$rowdate['unit_name'];
			}
		}else{
			$html.='<br>Delivery Schedule :'.date("d/m/Y", strtotime($rel['delivery_date'])).' Qty :'.$trn_rel['product_qty'].' '.$trn_rel['unit_name'];
		}
		$html.='</td>
		<td style="width:8%;text-align:center;vertical-align:top;">'.$trn_rel['product_hsn'].'</td>
		<td style="width:8%;text-align:center;vertical-align:top;">'.$trn_rel['product_qty'].'</td>
		<td style="text-align:center;vertical-align:top;width:10%;">';
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Extra At Actual";
		}
		else{
			$html.=$trn_rel['product_rate_conv'];
		}
		$html.='</td>
		
		<td style="width:5%;text-align:center;vertical-align:top;">'.$trn_rel['unit_name'].'</td>';
		if($trn_rel['discount_per']!='')
		{
		$html.='<td style="width:5%;text-align:center;vertical-align:top;">'.$trn_rel['discount_per'].'</td>';
		}
		else
		{
			$html.='<td style="width:5%;text-align:center;vertical-align:top;"></td>';
		}
		if($rel['currency_id']=='0'){
			$html.='<td style="width:7%;text-align:center;vertical-align:top;">'. $trn_rel['tax_gst'].'%</td>
			<td style="width:12%;text-align:center;vertical-align:top;">';
		}else{
			$html.='<td style="width:7%;text-align:center;vertical-align:top;"> - </td>
			<td style="width:12%;text-align:center;vertical-align:top;">';
		}
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Extra At Actual";
		}
		else{
			$html.=$trn_rel['product_amount_conv'];
		}
		$html.='</td>
		</tr>
		
	<tr>';
		$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
		if($trn_rel['act_amt_flag']!='1'){
			$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
		}
		$p++;
	$html.='</tbody></table>';
	$html.='<div class="clearfix"><div class="box" ></div><div class="box1" >'.(($trn_rel['spe']) ? $trn_rel['spe'] : '').'</div></div>';
	}
	////////////////////////////////////////////////////Transation Data End//////////////////////////////////////////////////////////////
	
	
	/////////////////////////////////////////////////////////TAX/////////////////////////////////////////////////////////////////////////////
	$html.='<table><tr>
			<td colspan="7" style="text-align:right;border-top: 1px solid;"><b>Basic Amount</b></td>
			<td colspan="" style="text-align:right;border-top: 1px solid;"><b>
			'.indian_number($ttl_amt,2).'
			</b></td>
			</tr>';
			$bill_sundry =0;$total_sundrytax=0;
			 $qry12="select b.sundry_gst_amount,b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
			from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
			left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
			where b.sundry_voucher_id=".$rel['quotation_id']." and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0'";
			$result12=$dbcon->query($qry12);		
			while($row12=mysqli_fetch_assoc($result12))
			{
				$html.='<tr>
				<td colspan="7" style="text-align:right;"><b>'.$row12['l_name'].'</b></td>
				<td style="text-align:right;"><b>
				'.number_format($row12['sundry_amount'],2,".","").'
				</b></td>
				</tr>';
				$bill_sundry=$bill_sundry+$row12['sundry_amount'];
			
				 $total_sundrytax = $total_sundrytax + $row12['sundry_gst_amount'];
			
			}
			$html.='<tr>
			<td colspan="7" style="text-align:right;"><b>Sub Total</b></td>
			<td colspan="" style="text-align:right;"><b>
			'.indian_number($ttl_amt+$bill_sundry,2).'
			</b></td>
			</tr>';

		
			$oth_cur_total = $rel['g_total'];
			
			
			if($rel['c_add_state']==$comp_rel['stateid']){
				if($rel['currency_id']=='0'){
				$html.='<tr>
				<td colspan="7" style="text-align:right;"><b>CGST</b></td>
				<td style="text-align:right;"><b>
				'.number_format((($total_sundrytax+$total_cs_gst)/2),2,".","").'
				</b></td>
				</tr>
				<tr>
				<td colspan="7" style="text-align:right;"><b>SGST</b></td>
				<td style="text-align:right;"><b>
				'.number_format((($total_sundrytax+$total_cs_gst)/2),2,".","").'
				</b></td>
				</tr>';
			}
				$oth_cur_total = $oth_cur_total -($total_sundrytax+$total_cs_gst);
			}else{
				if($rel['currency_id']=='0'){
				$html.='<tr>
				<td colspan="7" style="text-align:right;"><b>IGST</b></td>
				<td style="text-align:right;"><b>
				'.number_format(($total_sundrytax+$total_i_gst),2,".","").'
				</b></td>
				</tr>';
			}
				$oth_cur_total = $oth_cur_total -($total_sundrytax+$total_i_gst);
		}
			$qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_quotation_trn as trn 
			left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
			left join tbl_ledger as l on l.l_id=tc.tax_id 
			where tc.tax_additional='1' and trn.quotation_id=".$rel['quotation_id']." and trn.quot_trn_status!=2 and tc.isdelete='0' group by tc.tax_id";
			$result11=$dbcon->query($qry11);		
			while($row11=mysqli_fetch_assoc($result11))
			{
				$html.='<tr>
				<td colspan="7" style="text-align:right;"><b>'.$row11['l_name'].'</b></td>
				<td style="text-align:right;"><b>
				'.number_format($row11['add_sum'],2,".","").'
				</b></td>
				</tr>';
			}
			
			$html.='<tr>
			<td colspan="7" style="text-align:right;"><b>Grand Total
			</td>
			<td style="text-align:right;"><b>
			'.indian_number($oth_cur_total,2).'
			</b></td>
			</tr>
			<tr>
			<td colspan="7" style="border-top:1px solid;text-align:left;"><b>Amount in words : '.ucfirst(convert_number_to_words_new($oth_cur_total,$rel['currency_id'],$currency_word_start,$currency_word_end)).'
			</b></td>
			<td style="border-top:1px solid;">
			</td >
			</tr> </table>';
			//'.(($comp_rel['currency_id']==$rel['currency_id']) ? ucfirst(convert_number_to_words_new($rel['g_total'],$currency_word_start,$currency_word_end)) : ucfirst(convert_number_to_words($rel['g_total'],$currency_word_start,$currency_word_end))).'

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	
	$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
	left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
	where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
	$terms_qry_rs=$dbcon->query($terms_qry);
	if(mysqli_num_rows($terms_qry_rs)){
		$html.='<h4 style="text-align:left;">Terms & Conditions</h4>
		<table width="100%" style="overflow:wrap; white-space: nowrap;"><tbody>';
		$t=1;
		while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
			$string=(nl2br($term_rel['tc_details']));
			$html.='<tr>
			<td width="4%" style="text-align:center; vertical-align: top;">'.$t.'</td>
			<td width="20%" style="text-align:left; vertical-align: top;"><strong>'.$term_rel['tc_name'].'</strong></td>
			<td width="76%" style="text-align:left;"> : '.$string.'</td>
			</tr>';
			$t++;
		}
		$html.='<tr>
		<td width="100%" colspan="3" style="text-align:left;border-top:1px solid;border-bottom:1px solid">'.$rel['quot_footer'].'</td>
		</tr>
		</tbody></table>';	
	}
	$html .='<div>
	<table width="100%" style="border-collapse: collapse;overflow:wrap;"><tbody>
	<tr>
	<td style="width:25%;text-align:left;">GSTIN</td>
	<td style="width:25%;text-align:left;"> : '.$comp_rel['vatno'].'</td>
	<td colspan="2" rowspan="2" style="width:50%;text-align:left;vertical-align:top"><strong>For, '.$comp_rel['company_name'].'</strong></td>
	</tr>
	<tr>
	<td style="width:25%;text-align:left;">PAN No.</td>
	<td style="width:25%;text-align:left;"> : '.$comp_rel['pan_no'].'</td>
	</tr>
	<tr>
	<td colspan="2" style="width:50%;text-align:left;"></td>
	<td style="width:25%;text-align:left; vertical-align: top;">Prepared By <br> '.$rel['user_name'].'</td>
	<td style="width:25%;text-align:left; vertical-align: top;">Approved By <br> '.(($user_rels['user_name']) ? $user_rels['user_name'] : '').'</td>
	</tr>
	</table>
	</div>';
	/* Get Terms And Condition Start */

	$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
	//echo $html;exit;
	ob_end_clean();
	include("../../view/export/mpdf/mpdf.php");
	$mpdf=new mPDF('','A4','0','calibri','10','10','43.5','50','1','1');
	$mpdf->defaultheaderfontsize = 10; /* in pts */
	$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
	$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
	$mpdf->defaultfooterfontsize = 10; /* in pts */
	$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
	$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
	$mpdf->SetHTMLHeader($header);
	$mpdf->SetHTMLFooter($footer);
	/* $mpdf->pagenumPrefix = 'Page ';
	$mpdf->pagenumSuffix = ' of ';
	$mpdf->nbpgPrefix = ' ';
	$mpdf->nbpgSuffix = '';
	$mpdf->SetFooter('{PAGENO}{nbpg}'); 
 */	
	if($rel['approve_status'] == '0'){
		$mpdf->SetWatermarkText('NOT APPROVED');	
	}else{
		$mpdf->SetWatermarkText();	
	}
	$mpdf->showWatermarkText = true;
	$mpdf->allow_charset_conversion=true;
	$mpdf->charset_in='UTF-8';
	$mpdf->WriteHTML($html);
	$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
	ob_clean();
	return 'Quotation_'.$quotation_id.'.pdf';
}

?>