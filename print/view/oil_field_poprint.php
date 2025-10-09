<?php session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$_SESSION['contents']=''; 
$form="Purchase Order";
$mode="Print";
$type='pdf';
// print_r($_GET);
if(strtolower($type) == 'pdf') {
	$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select po.*,state.state_name,modesup.mode_dispatch,payterms.payment_terms as payment_term,l.l_name as vender_name,country.country_name,l.m_address as vender_address,l.gst_no as tin_no,l.cust_mobile as vender_mobile, l.stateid, state.gst_state_code, city.city_name, bmast.branch_address, city1.city_name as bcity, state1.state_name as bstate, country1.country_name as bcountry, l.emp_signature_img from tbl_purchaseorder as po 
	left join tbl_ledger as l on l.l_id=po.vender_id
	left join country_mst as country on country.countryid=l.countryid
	left join pay_terms as payterms on payterms.terms_id=po.payment_terms
	left join mode_of_dispatch as modesup on modesup.mode_dis_id=po.mode_of_dispatch
	left join state_mst as state on state.stateid=l.stateid
	left join city_mst as city on city.cityid=l.cityid
	left join branch_mst as bmast on bmast.branch_id=po.branch_id
	left join country_mst as country1 on country1.countryid=bmast.countryid
	left join state_mst as state1 on state1.stateid=bmast.stateid
	left join city_mst as city1 on city1.cityid=bmast.cityid
	where po.purchaseorder_id=$purchaseorder_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$_SESSION['invoice_no']=$rel['invoice_no'];		

	if(!empty($rel['branch_address'])){
		$baddress=$rel['branch_address'];
	}
	$cons_city_name1=$rel['bcity'];
	$cons_state_name1=$rel['bstate'];
	$cons_country_name1=$rel['bcountry'];

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	$purchaseorder_date='';
	if($rel['purchaseorder_date']!="1970-01-01" && $rel['purchaseorder_date']!="0000-00-00")
	{
		$purchaseorder_date=date('d-m-Y',strtotime($rel['purchaseorder_date']));
	}

	$cons_company_name	= $rel['company_name'];
	$cons_cust_address	= $rel['cust_address'];
	$cons_gst_no		= $rel['gst_no'];
	$cons_state_name	= $rel['state_name'];
	$cons_gst_state_code= $rel['gst_state_code'];
	$cons_city_name		= $rel['city_name'];
	$cons_country_name	= $rel['country_name'];

	$consignee="select * from tbl_cust_contact_person as cust where cust.cust_contact_person_status = 0 and cust.cust_id=".$rel['vender_id'];
	$cons_data=mysqli_fetch_assoc($dbcon->query($consignee));
	
	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));

	if($rel['currency_enable']=='0'){
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

	$chkreview = $dbcon->query("SELECT pa.*, users.user_name FROM tbl_purchaseorder_aprv_log as pa LEFT JOIN users AS users ON users.user_id = pa.user_id WHERE pa.approve_status = 3 AND pa.purchaseorder_id = ".$rel['purchaseorder_id']." ORDER BY pa.purchaseorder_aprv_id DESC LIMIT 1");
	$getreview = brp_mysqli_fetch_assoc($chkreview);

	$chkapprv = $dbcon->query("SELECT pfa.*, users.user_name FROM tbl_purchaseorder_finance_aprv_log as pfa LEFT JOIN users AS users ON users.user_id = pfa.user_id WHERE pfa.approve_status = 1 AND pfa.purchaseorder_id = ".$rel['purchaseorder_id']." ORDER BY pfa.po_finance_approve_id DESC LIMIT 1");
	$getapprv = brp_mysqli_fetch_assoc($chkapprv);

	$colspan=1;
	$cols=6;

	$header ='<table style="border: none; width: 100%;">
	<tbody>
	<tr style="border: none;">
	<td style="border: none; width: 65%; vertical-align: top;"><img src="'.DOMAIN_F.LOGO.$set_head['logo'].'" /></td>
	<td style="border: none; width: 35%; text-align: right;"><h1>'.$form.'</h1></td>
	</tr>
	</tbody>
	</table>';
	$footer ='<table style="border: none; width: 100%;">
	<tbody>
	<tr style="border: none;">
	<td colspan="2" style="border: none; font-size: 10px; text-align: center; font-weight: bold;">Note: If correction required, correction shall be made by the process owner of the report by striking the error out and initial it with the date.</td>
	</tr>
	<tr style="border: none; border-top: 1px solid;">
	<td style="text-align: right; border: none;"><strong>Assuring Our Best Service At All Times</strong></td>
	<td style="text-align: right;font-size: 10px; border: none;">FR/303-02 Issue 2 , Rev 3</td>
	</tr>
	</tbody>
	</table>';

	$approve_status='';
	if($rel['approve_status']=='0'){
		$approve_status=' (DRAFT)';
	}

	$html ='<html>
	<head>					
	<title>'.$form.' - '.$rel['purchaseorder_no'].'</title>
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
			border:1px solid;
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
		<table style="font-size:14px;border-collapse: collapse;width:100%; border: none;" cellpadding="5" cellspacing="5">
		<tr style="border: none;">
		<td rowspan="2" style="vertical-align: top; width: 70%;border: none;">The following number must appear on all invoices, bills of lading, and acknowledgements relating to this PO : </td>
		<td style="width: 15%; font-weight: bold; border: none; border-right: 1px solid; text-align: right;">P.O. DATE</td>
		<td style="width: 15%;border: none;">'.$purchaseorder_date.'</td>
		</tr>
		<tr style="border: none;">
		<td style="font-weight: bold; border: none; border-right: 1px solid; text-align: right;">TERMS</td>
		<td style="border: none;">'.$rel['terms'].'</td>
		</tr>
		<tr style="border: none;">
		<td rowspan="2" style="vertical-align: top; font-weight: bold;border: none; text-align: center;">PURCHASE ORDER : <span style="border:1px solid; padding: 15px;">'.$rel['purchaseorder_no'].'</span></td>
		<td style="font-weight: bold; border: none; border-right: 1px solid; text-align: right;">F.O.B</td>
		<td style="border: none;">'.$rel['fob'].'</td>
		</tr>
		<tr style="border: none;">
		<td style="font-weight: bold; border: none; border-right: 1px solid; text-align: right;">SHIP VIA</td>
		<td style="border: none;">'.$rel['shipped_via'].'</td>
		</tr>
		<tr style="border: none;">
		<td style="vertical-align: top;border: none;">
		<table width="450px" style="padding: 5px; border: 1px solid;">
		<tr style="border: none;">
		<td width="5%" style="border: none;"><strong>TO:</strong></td>
		<td width="95%" style="border: none;"></td>
		</tr>
		<tr style="border: none;">
		<td style="border: none;"></td>
		<td style="border: none;"><strong>'.$rel['vender_name'].'</strong><br>'.((!empty($rel['vender_address'])) ? nl2br($rel['vender_address']) : '').'<br>'.$rel['vender_mobile'].'</td>
		</tr>
		</table>
		</td>
		<td colspan="2" style="vertical-align: top;border: none;"><strong>ADDRESS CORRESPONDENCE TO:<br></strong>Name '.$cons_data['cust_contact_person_name'].'<br>Email '.$cons_data['cust_contact_person_email'].'<br>Phone '.$cons_data['cust_contact_person_no'].'<br>FAX #</td>
		</tr>
		</table>
		<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		<thead>
		<tr>
		<th style="width:3%;text-align:center; border-right: 1px solid;">ITEM</th>
		<th style="width:5%;text-align:center; border-right: 1px solid;">QTY</th>
		<th style="width:8%;text-align:center; border-right: 1px solid;">UNIT</th>
		<th colspan="2" style="width:38%;text-align:center; border-right: 1px solid;">DESCRIPTION</th>
		<th style="width:10%;text-align:center; border-right: 1px solid;white-space: nowrap;">UNIT PRICE'.$currency_name.'</th>
		<th style="width:10%;text-align:right; border-left: 1px solid;white-space: nowrap;">AMOUNT'.$currency_name.'</th>
		</tr>
		</thead>
		<tbody>';
		$qry="SELECT trn.*,product.*,product.product_desc as scode,per.unit_name,per1.unit_name as base_unit_name,per2.unit_name as conv_unit_name FROM `tbl_purchaseordertrn` as trn 
		left join product_mst as product on product.product_id=trn.product_id 
		left join unit_mst as per on per.unitid=trn.unit_id 
		left join unit_mst as per1 on per1.unitid=product.product_base_unit 
		left join unit_mst as per2 on per2.unitid=product.product_conv_unit
		where purchaseordertrn_status=0 and purchaseorder_id=".$rel['purchaseorder_id']." group by purchaseordertrn_id order by purchaseordertrn_id";
		$result=$dbcon->query($qry);		
		$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_i_gst=0;$gst_per = 0;$gst_rate = 0;
		$cnt=mysqli_num_rows($result);
		while($row=mysqli_fetch_assoc($result))
		{
			if($row['product_base_unit']!=$row['product_conv_unit']){
			//base_unit_name,per2.unit_name as conv_unit_name
				if($row['unit_id']==$row['product_base_unit']){
					$cqty=convert_stock($dbcon,$row['product_qty'],$row['product_id'],"conv_unit");
					$uname=$row['conv_unit_name'];
				}else{
					$cqty=convert_stock($dbcon,$row['product_qty'],$row['product_id'],"base_unit");
					$uname=$row['base_unit_name'];
				}
			}
			$pro_descri = ($row['product_des']) ? nl2br($row['product_des']) : '' ;
			$product_hsn_code = ($row['product_hsn_code']) ? $row['product_hsn_code'] : $row['product_hsn'] ;
			$pro_disc = ($row['discount_per']) ? $row['discount_per']." %" : '';

			$html.='<tr style="border: none;border-right: 1px solid; border-left: 1px solid;">
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;">'.$i.'</td>
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;">
			'.$row['product_qty'].'
			</td>
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;">'.$row['unit_name'].'</td>
			<td colspan="2" style="text-align:left;border-right: 1px solid;vertical-align:top;">
			<strong>'.$row['product_name'].'</strong><br/>
			'.$pro_descri.'
			</td>
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;">'.number_format($row['product_rate'],2,".","").'</td>
			<td style="text-align:right;border-right: 1px solid;vertical-align:top;">'.number_format($row['product_amount'],2,".","").'</td>
			</tr>';
			$i++; 
			$totalqty=$totalqty+$row['product_qty']-$charges_qty;$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
			$total_product_amount+=$row['product_amount'];
			$totaltaxable+=$taxable_amt;
			$total+=$row['total'];
		}
		$pr=14-$cnt;
		for($j=0; $j<$pr; $j++){
			$html.='<tr style="border: none; border-right: 1px solid; border-left: 1px solid;">
			<td style="border-right: 1px solid;"></td>
			<td style="border-right: 1px solid;"></td>
			<td style="border-right: 1px solid;"></td>
			<td colspan="2" style="border-right: 1px solid;height:25px;"></td>
			<td style="border-right: 1px solid;"></td>
			<td style="border-left: 1px solid;"></td>
			</tr>';
		}
		$remark = ($rel['remark']) ? $rel['remark'] : '';
		//$rows=(($rel['stateid']==$set_head['stateid']) ? 2 : 1)+2;
		$html.='<tr>
		<td colspan="3" style="height: 50px; text-align:left; vertical-align: middle; font-weight: bold;">Material Specification :</td>
		<td colspan="2" style="height: 50px; text-align:left; vertical-align: middle; font-weight: bold;">"As per attached Material Specification"</td>
		<td colspan="'.$colspan.'" style="height: 50px; border-top: none !important;"></td>
		<td style="height: 50px; border-top: none; !important"></td>
		</tr>
		<tr>
		<td></td>
		<td></td>
		<td></td>
		<td style="text-align: center; font-weight: bold;">Applicable <input type="checkbox" class="form-control"/></td>
		<td style="text-align: center; font-weight: bold;">Not Applicable <input type="checkbox" class="form-control"/></td>
		<td colspan="'.$colspan.'" style="text-align:right; font-weight: bold;white-space: nowrap;">Sub Total'.$currency_name.'</td>
		<td style="text-align:right;font-weight: bold;">'.number_format($total_product_amount,2,".","").'</td>
		</tr>';
		$html.='<tr>
		<td colspan="5" style="text-align:left; vertical-align: top;">Remarks: If correction required on any of supplier supplied documents/ records, correction shall be made by the supplier authorised person by striking the error out and initial it with the date.<br>Please notify us immediately if this order cannot be shipped complete on or before:<br>'.$remark.'</td>
		<td colspan="'.$colspan.'"></td>
		<td></td>
		</tr>
		<tr style="border: none;">
		<td colspan="'.($cols).'" style="border: none;text-align:right; font-weight: bold; font-size: 14px;white-space: nowrap;">TOTAL'.$currency_name.'</td>
		<td style="border: none;text-align:right;font-weight: bold; font-size: 14px; text-decoration: underline;">'.number_format($total_product_amount,2,".","").'</td>
		</tr>
		</tbody>
		</table>
		<table>
		<tbody>
		<tr style="border: none;">
		<td colspan="2" style="width: 40%; border: none; vertical-align:bottom; text-align: left; height: 80px;">'.$getreview['user_name'].'</td>
		<td style="width: 20%; border: none; height: 80px;"></td>
		<td colspan="2" style="width: 40%; border: none; vertical-align:bottom; text-align: left; height: 80px;">'.$getapprv['user_name'].'</td>
		</tr>
		<tr style="border: none;">
		<td colspan="2" style="border: none; text-align: left;">________________________________________</td>
		<td style="border: none; text-align: left;"></td>
		<td colspan="2" style="border: none; text-align: left;">________________________________________</td>
		</tr>
		<tr style="border: none;">
		<td style="border: none; text-align: left;">Reviewed By</td>
		<td style="border: none; text-align: left;">Date</td>
		<td style="border: none; text-align: left;"></td>
		<td style="border: none; text-align: left;">Approved By</td>
		<td style="border: none; text-align: left;">Date</td>
		</tr>
		</tbody></table>
		<div style="clear:both;"></div>
		</div>
		<!--page1 end-->';

		$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
		</body>
		</html>';
		// echo $html;exit;
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','40','5','1','1');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		//Show page number
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
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$purchaseorder_id.'.pdf','f');
		ob_clean();
		return 'Purchase Order '.$purchaseorder_id.'.pdf';
	}

	?>