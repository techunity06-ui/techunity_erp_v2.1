<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$include = '../../include/';
$form = "PURCHASE ORDER";
$type='pdf';
if(strtolower($type) == 'pdf') {
	$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select po.*,state.state_name,modesup.transportation_name,payterms.payment_terms as payment_term_name,l.l_name as vender_name,country.country_name,l.m_address as vender_address,l.gst_no as tin_no,l.cust_mobile as vender_mobile,l.stateid,state.gst_state_code,city.city_name,l.cust_pincode,l.cust_email,god.gd_name,god.gd_address,comp.company_name,le.l_name as con_ven,bm.branch_name as cons_bran, l.cust_cont_name from tbl_purchaseorder as po 
	inner join tbl_ledger as l on l.l_id=po.vender_id
	left join country_mst as country on country.countryid=l.countryid
	left join pay_terms as payterms on payterms.terms_id=po.payment_terms
	left join transportation_details as modesup on modesup.id=po.mode_of_dispatch
	left join state_mst as state on state.stateid=l.stateid
	left join city_mst as city on city.cityid=l.cityid
	left join mst_godown as god on god.gd_id = po.godown_id
	left join tbl_company as comp on comp.company_id = po.company_id
	left join tbl_ledger as le on le.l_id = po.con_vender_id
	left join branch_mst as bm on bm.branch_id = po.con_branch
	where po.purchaseorder_id=$purchaseorder_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	//echo "<pre>";print_r($rel);die();
	$_SESSION['invoice_no']=$rel['invoice_no'];		
	$delivery_type = $rel['delivery_type'];
	$purchaseorder_date='';
	if($rel['purchaseorder_date']!="1970-01-01" && $rel['purchaseorder_date']!="0000-00-00")
	{
		$purchaseorder_date=date('d-m-Y',strtotime($rel['purchaseorder_date']));
	}
	if($rel['quotation_date']!="1970-01-01" && $rel['quotation_date']!="0000-00-00")
	{
		$quotation_date=date('d-m-Y',strtotime($rel['quotation_date']));
	}
	
	$cons_company_name	= $rel['vender_name'];
	$cons_cust_address	= $rel['vender_address'];
	$cons_gst_no		= $rel['gst_no'];
	$cons_state_name	= $rel['state_name'];
	$cons_gst_state_code= $rel['gst_state_code'];
	$cons_city_name		= $rel['city_name'];
	$cons_country_name	= $rel['country_name'];
	
	$party_address_billing="<strong>".$rel['vender_name']."</strong>
	<span style='font-weight:normal;'> <br/>
	".$rel['vender_address'].",<br/>
	".$rel['cust_pincode']."
	".$rel['city_name'].",
	".$rel['state_name'].",
	".$rel['country_name']."</span>
	<br><br><strong>".$rel['cust_cont_name']."</strong> - ".$rel['vender_mobile']."
	<br>Email : ".$rel['cust_email'];

	$us_sql = "select user_name from users where user_id='".$rel['userid']."'";
	$user_rel=brp_mysqli_fetch_assoc($dbcon->query($us_sql));

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
//CHEMITEK PROCESS EQUIPMENTS PRIVATE LIMITED		
	$comp_rel=mysqli_fetch_assoc($dbcon->query($set));

	if($rel['cons_same_as']==0){
		if($rel['con_type'] ==1){
			$cons_name = $rel['cons_bran'];
		}else if($rel['con_type'] ==2){
			$cons_name = $rel['con_ven'];
		}else{
			$cons_name = $rel['company_name'];
		}

		$consignee_address = $rel['con_address'];
		$party_address_con = '<strong>'.$cons_name.'</strong><br><br>'.$consignee_address;
	}else{
		$cons_name 		   = $rel['company_name'];
		$consignee_address = $comp_rel['address'];
		$party_address_con = '<strong>'.$cons_name.'</strong><br><br>'.$consignee_address;
	}
	/* Check Discount is On or off Start */
	if($comp_rel['show_disc']=='1'){
		$colspan=5;
		$dynamicwidth=40;
	}else{
		$colspan=6;
		$dynamicwidth=46;
	}
	$HowManyWeeks = (strtotime( $rel['purchaseorder_due_date'] ) - strtotime( $rel['cdate'])) / 604800;
	$HowManyWeeks = round($HowManyWeeks);
	$HowManyWeeks = ($HowManyWeeks=='0')?'0':$HowManyWeeks;
	$delivery_week = $HowManyWeeks .' - '. ($HowManyWeeks+1) . ' WEEKS';
	/* Check Discount is On or off End */
	$sundrytax=$dbcon->query("select b.* from tbl_bill_sundry_transaction as b where b.sundry_voucher_id=".$rel['purchaseorder_id']." and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' ");
//$sundrytax_rels = mysqli_fetch_assoc($sundrytax);
	$total_sundrytax=0;
	while($sumsundrytax=mysqli_fetch_assoc($sundrytax)){
		$total_sundrytax = $total_sundrytax + $sumsundrytax['sundry_gst_amount'];
	}
	$qry_disc=$dbcon->query("select SUM(trn.product_discount) as discount FROM `tbl_purchaseordertrn` as trn WHERE trn.purchaseordertrn_status=0 and purchaseorder_id=".$rel['purchaseorder_id']);
	$get_disc = brp_mysqli_fetch_assoc($qry_disc);
	if($get_disc['discount']>0){
		$colspan = 7;
	}else{
		$colspan = 6;
	}

	$company_config = getCompanyConfiguration($dbcon);

	$html='';
	if($company_config['po_header_content']!=''){
		$po_header_content = '<td rowspan="3" style="vertical-align: top; width: 45%;"><span style="font-size: 18px; font-weight: bold;">'.$comp_rel['company_name'].'</span><br>'.$company_config['po_header_content'].'</td>';
	}else{
		$po_header_content = '<td rowspan="3" style="vertical-align: top; width: 45%;"><span style="font-size: 18px; font-weight: bold;">'.$comp_rel['company_name'].'</span><br>'.$comp_rel['address'].'<br>Phone no. : '.$comp_rel['contact_no'].'<br>GST No. :- '.$comp_rel['vatno'].'<br>E-mail: '.$comp_rel['website'].'</td>';
	}
	$header ='<table style="width: 100%; font-size: 12px;">
	<tr>
	<td rowspan="3" style="text-align: center; width: 25%"><img src="'.DOMAIN_F.LOGO.$comp_rel['logo'].'" style="width:250px;"/></td>
	'.$po_header_content .'
	<td style="text-align: center; width: 15%; font-size: 11px;">FORMS AND FORMAT</td>
	<td style="text-align: center; width: 15%; font-size: 11px;">FORM NO.: F:PUR:01</td>
	</tr>
	<tr>
	<td colspan="2" style="text-align: center; font-weight: bold; font-size: 16px;">'.$form.'</td>
	</tr>
	<tr>
	<td style="text-align: center; font-size: 11px;">REV NO.: 02</td>
	<td style="text-align: center; font-size: 11px;">DATE.: 01/01/2021</td>
	</tr>
	</table>';
	$footer = '<div style="text-align: center; font-size: 12px;">COMPUTER GENRATED PURCHASE ORDER</div>';

	$html.='<html>
	<head>					
	<title>Purchase Order - '.$rel['purchaseorder_no'].'</title>

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
		<table style="font-size: 12px;">
		<tr style="border:1px solid;">
		<td rowspan="5" style="width: 5%; text-align:right; vertical-align: top;">TO,</td>
		<td rowspan="5" style="width: 45%; text-align:left; vertical-align: top;">'.$party_address_billing.'</td>
		<td colspan="3" style="text-align:left; font-weight: bold;">Purchase Order No.</td>
		<td colspan="5" style="text-align:left;">'.$rel['purchaseorder_no'].'</td>
		</tr>
		<tr style="border:1px solid;">
		<td colspan="3" style="text-align:left; font-weight: bold;">PO Date.</td>
		<td colspan="5" style="text-align:left;">'.$purchaseorder_date.'</td>
		</tr>
		<tr style="border:1px solid;">
		<td colspan="3" style="text-align:left; font-weight: bold;">Quotation No.</td>
		<td colspan="5" style="text-align:left;">'.$rel['quotation_no'].'</td>
		</tr>
		<tr style="border:1px solid;">
		<td colspan="3" style="text-align:left; font-weight: bold;">Date.</td>
		<td colspan="5" style="text-align:left;">'.$quotation_date.'</td>
		</tr>
		<tr style="border:1px solid;">
		<td colspan="3" style="text-align:left; font-weight: bold;">Indent/Challan No.:</td>
		<td colspan="5" style="text-align:left;"></td>
		</tr>
		<tr style="border:1px solid;">
		<td colspan="10" style="text-align:left; font-size: 12px;">We are pleased to place an order for the below mentioned items.</td>
		</tr>
		<tr>
		<td style="text-align:center;"><b>Sr No.</b></td>
		<td style="text-align:center;"><b>Item Description</b></td>
		<td style="text-align:center;"><b>HSN Code</b></td>
		<td style="text-align:center;"><b>Quantity</b></td>
		<td style="text-align:center;"><b>Unit</b></td>
		<td style="text-align:center; white-space: nowrap;"><b>Rate/<br>Per Unit</b></td>
		<td style="text-align:center;"><b>Disc. %</b></td>
		<td style="text-align:center;"><b>Gst %</b></td>
		<td style="text-align:center;"><b>Gst Amt.</b></td>
		<td style="text-align:center;"><b>Amount</b></td>
		</tr>
		</thead>
		<tbody>';
		$qry="SELECT trn.*, product.product_name, per.unit_name FROM `tbl_purchaseordertrn` as trn 
		left join product_mst as product on product.product_id=trn.product_id 
		left join unit_mst as per on per.unitid=trn.rate_unit
		where purchaseordertrn_status=0 and purchaseorder_id=".$rel['purchaseorder_id']." group by purchaseordertrn_id order by purchaseordertrn_id";
		$trn_qry_rs=$dbcon->query($qry);
		$p=1;$ttl_amt=0;$ttl_qty=0;$total_cs_gst=0;$total_i_gst=0;$gst_per=0;$gst_rate=0;
		$total_line = 0;
		$cnt=mysqli_num_rows($trn_qry_rs);
		while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
			$indent_no = po_to_indent_no($dbcon,$trn_rel['po_ref_id']);
			$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
			$gst_rate = $trn_rel['cgst_tax_rate']+$trn_rel['sgst_tax_rate']+$trn_rel['igst_tax_rate'];
			if($trn_rel['cgst_tax_rate'] != 0 || $trn_rel['sgst_tax_rate'] !=0){
				$total_cs_gst += $gst_rate;
			}else{
				$total_i_gst += $gst_rate;
			}
			if($trn_rel['unit_id']===$trn_rel['rate_unit']){
				$sqty=$trn_rel['product_qty'];
			}else{
				$sqty=$trn_rel['product_conv_qty'];
			}
			$len = strlen($trn_rel['product_des']);
			//$des = wordwrap(substr($trn_rel['product_des'],0,190),50,"<br>\n");
			$des = $trn_rel['product_des'];
			$desc_count = ceil($len/190);
			$html.='
			<tr>
			<td style="vertical-align: top; text-align:center;">'.$p.' </td>
			<td style="vertical-align: top; text-align:left;"><strong>'.$trn_rel['product_name'].'</strong><br>'.$des.'<br>';
			if($delivery_type == 'product_wise'){
				$retu_date = "select sdate.*,unit.unit_name from tbl_purchaseorder_delivery_date as sdate left join unit_mst as unit on unit.unitid=sdate.unit_id where po_delivery_date_status=0 and purchaseordertrn_id=".$trn_rel['purchaseordertrn_id'];
				$resadate=$dbcon->query($retu_date);

				$html .='<table width="80%" style="font-size:12px">
				<tr>
				<td><strong>Delivery Date</strong></td>
				<td><strong>Qty</strong></td>
				</tr>';

				while($rowdate=brp_mysqli_fetch_array($resadate)){		
					$html .='<tr>
					<td>'.date('d-m-Y',strtotime($rowdate['delivery_date'])).'</td>
					<td>'.$rowdate['product_qty'].' '.$rowdate['unit_name'].'</td>
					</tr>';		
				}
				$html .='</table>';
			}
			$html .='</td>
			<td style="vertical-align: top; text-align:center;"> '.$trn_rel['product_hsn_code'].'</td>
			<td style="vertical-align: top; text-align:center;">'.without_comma_two_digit_amount($sqty).' </td>
			<td style="vertical-align: top; text-align:center;">'.$trn_rel['unit_name'].' </td>
			<td style="vertical-align: top; text-align:center;"> '.without_comma_two_digit_amount($trn_rel['product_rate']).'</td>
			<td style="vertical-align: top; text-align:center;">'.(($trn_rel['discount_per']) ? without_comma_two_digit_amount($trn_rel['discount_per']).'' : '').'</td>
			<td style="vertical-align: top; text-align:center;">'.$gst_per.' </td>
			<td style="vertical-align: top; text-align:center;">'.$gst_rate.'</td>
			<td style="vertical-align: top; text-align:right;"> '.without_comma_two_digit_amount($trn_rel['product_amount']).'</td>
			</tr>';

			$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
			if($trn_rel['act_amt_flag']!='1'){
		//$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
				$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
			}
			$total_line=$desc_count+$total_line+1;
			$p++;
		}
		$pr=10-$total_line;
		for($j=0; $j<$pr; $j++){
			$html.='<tr>
			<td style="height:25px;"></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			</tr>';
		}

		$html.='
		<tr>
		<td colspan="8" style=" text-align:right;">TOTAL AMOUNT</td>
		<td></td>
		<td  style=" text-align:right;">'.without_comma_two_digit_amount($ttl_amt,2).'</td>
		</tr>
		';
		if($rel['stateid']==$comp_rel['stateid']){
			$total_cs_gst = $total_cs_gst/2;
			$html.='<tr>
			<td colspan="8" style=" text-align:right;">CGST</td>
			<td  style=" text-align:center; white-space: nowrap;">'.number_format(($gst_per/2),2,".","").' %</td>
			<td  style=" text-align:right;">'.number_format(($total_cs_gst+$total_sundrytax/2),2,".","").'</td>
			</tr>
			<tr>
			<td colspan="8" style=" text-align:right;">SGST</td>
			<td  style=" text-align:center;  white-space: nowrap;">'.number_format(($gst_per/2),2,".","").' %</td>
			<td  style=" text-align:right;">'.number_format(($total_cs_gst+$total_sundrytax/2),2,".","").'</td>
			</tr>';
		}else{
			$html.='<tr>
			<td colspan="8" style=" text-align:right;">IGST</td>
			<td  style=" text-align:center;  white-space: nowrap;">'.number_format(($gst_per),2,".","").' %</td>
			<td  style=" text-align:right;">'.number_format(($total_i_gst+$total_sundrytax),2,".","").'</td>
			</tr>';
		}		
		$qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_purchaseordertrn as trn 
		left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
		left join tbl_ledger as l on l.l_id=tc.tax_id 
		where tc.tax_additional='1' and trn.purchaseorder_id=".$rel['purchaseorder_id']." and trn.purchaseordertrn_status!=2 and tc.isdelete='0' group by tc.tax_id";
		$result11=$dbcon->query($qry11);		
		while($row11=mysqli_fetch_assoc($result11))
		{
			$html.='<tr>
			<td colspan="8" style=" text-align:right;">'.$row11['l_name'].'</td>
			<td></td>
			<td style=" text-align:right;">'.number_format($row11['add_sum'],2,".","").'</td>
			</tr>';
		}
		$qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
		from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
		left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
		where b.sundry_voucher_id=".$rel['purchaseorder_id']." and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' and le.default_sundry='0'";
		$result12=$dbcon->query($qry12);		
		while($row12=mysqli_fetch_assoc($result12))
		{
			$html.='<tr>
			<td colspan="8" style=" text-align:right;">'.$row12['l_name'].'</td>
			<td></td>
			<td style=" text-align:right;">'.number_format($row12['sundry_amount'],2,".","").'</td>
			</tr>';
		}
    //$round_off = round($final_total)-$final_total;
		$round_off = 0;
		$html .= '
		<tr>
		<td colspan="6" style=" text-align:left;">Rupees : <span style="font-weight:bold;">'.convert_number_to_words_new($rel['g_total']).'</span></td>
		<td colspan="2" style=" text-align:right;">Grand Total</td>
		<td></td>
		<td style=" text-align:right;font-weight:bold;">'.without_comma_two_digit_amount(($rel['g_total']),2).' </td>
		</tr>
		<tr>
		<td colspan="10">'.$rel['po_condition'].'</td>
		</tr>
		<tr>
		<td colspan="2" rowspan="4" style="vertical-align: top;"><strong>Remarks:</strong><br>'.$rel['remark'].'</td>
		<td colspan="8"><strong>Terms & Conditions:-</strong></td>
		</tr>
		<tr style="border-bottom:none">
		<td colspan="4" style="border-bottom:none;text-align:left;">Mode of Dispatch : </td>
		<td colspan="4" style="border-bottom:none;text-align:left;">'.$rel['transportation_name'].' </td>
		</tr>
		<tr style="border-bottom:none">
		<td colspan="4" style="border-bottom:none;text-align:left;">Payment Terms : </td>
		<td colspan="4" style="border-bottom:none;text-align:left;">'.$rel['payment_term_name'].'</td>
		</tr>
		<tr style="border-bottom:none">
		<td colspan="4" style="border-bottom:none;text-align:left;">Delivery Period : </td>
		<td colspan="4" style="border-bottom:none;text-align:left;">'.$delivery_week.'</td>
		</tr>
		<tr style="border-bottom:none">
		<td colspan="2" style="border-bottom:none;text-align:left;font-weight:bold;">Prepared By</td>
		<td colspan="8" style="border-bottom:none;text-align:right;font-weight:bold;">Reviewed & Approved By</td>
		</tr>
		<tr style="border-top:none">
		<td colspan="2" style="border-top:none; height: 60px; vertical-align:top;text-align:left;"></td>
		<td colspan="8" style="height: 60px; vertical-align:bottom; text-align:right; font-weight:bold; border-top:none;">Director / Purchase Incharge</td>
		</tr>
		</table>';
		/* Get Terms And Condition Start */
		$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
		</body>
		</html>';
		// echo $header;
		// echo $html;
		// echo $footer; exit;
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");

		$mpdf=new mPDF('','A4','0','calibri','10','10','34','10','5','4');
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
		return 'Purchase Order'.$rel['purchaseorder_no'].'.pdf';
	}	
?>