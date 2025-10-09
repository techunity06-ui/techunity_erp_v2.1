<?php session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$_SESSION['contents']=''; 
$form="Proforma Invoice";
$mode="Print";
$type='pdf';
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	FINANCE_PROFORMA_INVOICE_PRINT
]);

if(!in_array(FINANCE_PROFORMA_INVOICE_PRINT,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
// print_r($_GET);
if(strtolower($type) == 'pdf') {
	$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
	$que = "SELECT performa_invoice_type FROM tbl_proforma_invoice WHERE invoice_id=$invoiceid";
	$rel_que=mysqli_fetch_assoc($dbcon->query($que));

	if($rel_que['performa_invoice_type']=='1'){
		$query="SELECT invoice.*,country.country_name,payterms.payment_terms AS payment_trm,state.state_name,cust_a.c_add_state as stateid,state.gst_state_code, city.city_name, cust.cust_name AS company_name,cust_a.c_add_address AS cust_address, type.invoice_type,cust_mobile,cust_email,cust.cust_gst AS gst_no
		FROM tbl_proforma_invoice AS invoice 
		LEFT JOIN tbl_customer AS cust on cust.cust_id=invoice.cust_id 
		LEFT JOIN tbl_cust_address AS cust_a on cust_a.cust_id=invoice.cust_id 
		LEFT JOIN country_mst AS country on country.countryid=cust_a.c_add_country 
		LEFT JOIN state_mst AS state on state.stateid=cust_a.c_add_state 
		LEFT JOIN city_mst AS city on city.cityid=cust_a.c_add_city 
		LEFT JOIN tbl_invoicetype AS type on type.invoicetype_id=invoice.invoicetype_id 
		LEFT JOIN pay_terms AS payterms on payterms.terms_id=invoice.payment_terms 
		WHERE invoice_id=$invoiceid";
	}else{
		$query="SELECT invoice.*,country.country_name,payterms.payment_terms AS payment_trm,state.state_name,cust.stateid,state.gst_state_code, city.city_name, cust.l_name AS company_name,cust.m_address AS cust_address, type.invoice_type,cust_pincode,cust_mobile,gst_no, cust.stateid  
		FROM tbl_proforma_invoice AS invoice 
		LEFT JOIN tbl_ledger AS cust on cust.l_id=invoice.cust_id 
		LEFT JOIN country_mst AS country on country.countryid=cust.countryid 
		LEFT JOIN state_mst AS state on state.stateid=cust.stateid 
		LEFT JOIN city_mst AS city on city.cityid=cust.cityid 
		LEFT JOIN tbl_invoicetype AS type on type.invoicetype_id=invoice.invoicetype_id 
		LEFT JOIN pay_terms AS payterms on payterms.terms_id=invoice.payment_terms 
		WHERE invoice_id=$invoiceid";
	}
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$rel['invoice_type'] = 'PROFORMA INVOICE';  
	if(!$rel){
		header("Location: ".ROOT."proforma_list");
	}

	$cons_gst_no=$rel['gst_no'];
	$cons_pan_no=$rel['pan_no'];
	$cons_state_name=$rel['state_name'];
	$cons_gst_state_code=$rel['gst_state_code'];
	$place_of_supply=$rel['city_name'];
	$order_no = ($rel['order_no']!='0')?$rel['order_no']:'';
if(!empty($rel['consignee_id']))//consignee
{ 
	if($rel['performa_invoice_type']=='1'){
		$table_name = 'tbl_party_consignee';
	}else{
		$table_name = 'tbl_custmer_consignee';
	}
	$consignee="SELECT * FROM $table_name AS cust 
	LEFT JOIN country_mst AS country on country.countryid=cust.countryid
	LEFT JOIN state_mst AS state on state.stateid=cust.stateid 
	LEFT JOIN city_mst AS city on city.cityid=cust.cityid WHERE cust_id=".$rel['consignee_id'];
	$cons_data=mysqli_fetch_assoc($dbcon->query($consignee)); 
	$cons_gst_no=($cons_data['gst_no']!='0')?$cons_data['gst_no']:$rel['gst_no'];
	$cons_pan_no=$cons_data['pan_no'];
	$cons_state_name=$cons_data['state_name'];
	$cons_gst_state_code=$cons_data['gst_state_code'];
	$place_of_supply=$cons_data['city_name'];
	$cons_mobile_no = $cons_data['cust_mobile'];
	$cons_address = $cons_data['cust_address'];
	$cons_company_name = $cons_data['company_name'];
}else{
    $cons_gst_no = $rel['gst_no'];
    $cons_state_name=$rel['state_name'];
	$cons_gst_state_code=$rel['gst_state_code'];
	$place_of_supply=$rel['city_name'];
	$cons_mobile_no = $rel['cust_mobile'];
	$cons_address = $rel['cust_address'];
	$cons_company_name = $rel['company_name'];
}
if($rel_que['performa_invoice_type']=='1'){
	$cust_address = $rel['cust_address'].'<br>'.$rel['city_name'].' '.$rel['state_name'].' '.$rel['country_name'];
}else{
	$cust_address = $rel['cust_address'].'<br>'.$rel['city_name'].' '.$rel['state_name'].' '.$rel['country_name'];
}
$set="SELECT comp.*,state.state_name,state.gst_state_code FROM tbl_company AS comp LEFT JOIN state_mst AS state on comp.stateid=state.stateid WHERE company_id=".$rel['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));  
//echo "<pre>";print_r($set_head);die();
$order_date='';$invoice_date='';$dispatch_date='';
if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
{
	$order_date=date('d/m/Y',strtotime($rel['order_date']));
}
if($rel['invoice_date']!="1970-01-01" && $rel['invoice_date']!="0000-00-00")
{
	$invoice_date=date('d/m/Y',strtotime($rel['invoice_date']));
}
if($rel['dispatch_date']!="1970-01-01 00:00:00" && $rel['dispatch_date']!="0000-00-00 00:00:00" && $rel['dispatch_date']!="1970-01-01 h:i:s")
{
	$dispatch_date=date('d-m-Y',strtotime($rel['dispatch_date']));
}
/* Check Discount is On or off Start */  
$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));

$header ='<table style="border: none; width: 100%;">
		<tbody>
			<tr style="border: none;">
				<td style="border: none; width: 65%; vertical-align: top;"><img src="'.DOMAIN_F.LOGO.$set_head['logo'].'" /></td>
				<td style="border: none; width: 35%; text-align: right;"><h1>'.$form.'</h1></td>
			</tr>
		</tbody>
	</table>';
//$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';
$sql=$dbcon->query("SELECT trn.*,SUM(discount_per) AS discount FROM `tbl_proforma_trn` AS trn LEFT JOIN `formula_mst` AS ftax on ftax.formulaid=trn.formulaid LEFT JOIN tbl_tax AS tax on find_in_set(tax.tax_id,ftax.tax_id) WHERE trancation_status=0 AND invoice_id='$invoiceid' GROUP BY tax_name");
$getrows=mysqli_num_rows($sql);
$disc_row=mysqli_fetch_assoc($sql);

if($disc_row['discount']>0){
	$colspan=5;
}else{
	$colspan=4;
}
$cols=$colspan+3;

$approve_status='';
if($rel['approve_status']=='0'){
	$approve_status=' (DRAFT)';
}

$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));


$html ='<html>
<head>					
<title>'.$form.' - '.$rel['invoice_no'].'</title>
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
		border:none !important;
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
	<!--<htmlpagefooter name="otherpages_footer" style="display:none">
	<div style="text-align:center">'.$footer.'</div>
	</htmlpagefooter>-->
	<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
	<div>
	<table style="font-size:12px;width:100%; table-layout: fixed;">
	<tr>
	<td colspan="2" rowspan="7" style="vertical-align: top; width: 60%;"><strong>Bill To<br>Billing Name : '.$rel['company_name'].'</strong><br>Address : '.$cust_address.'<br>GST No: '.$rel['gst_no'].'<br>Contact No : '.$rel['cust_mobile'].'<br><br><strong>Ship To<br>Shipping Name : '.$cons_company_name.'<br>Address :</strong> '.$cons_address.'<br>'.$place_of_supply.' '.$cons_state_name.'<br>GST No: '.$cons_gst_no.'<br>Contact No : '.$cons_mobile_no.'</td>
	<td style="width: 20%; font-weight: bold;">Proforma Invoice No :</td>
	<td style="width: 20%;">'.$rel['invoice_no'].'</td>
	</tr>
	<tr>
	<td style="font-weight: bold;">Proforma Invoice Date :</td>
	<td>'.$invoice_date.'</td>
	</tr>
	<tr>
	<td style="font-weight: bold;">Purchase Order No :</td>
	<td>'.$rel['order_no'].'</td>
	</tr>
	<tr>
	<td style="font-weight: bold;">Purchase Order Date :</td>
	<td>'.$order_date.'</td>
	</tr>
	<tr>
	<td style="font-weight: bold;">Payment Terms :</td>
	<td>'.$rel['payment_terms'].'</td>
	</tr>
	<tr>
	<td style="font-weight: bold;">Sales Receipt :</td>
	<td>'.$userData['user_name'].'</td>
	</tr>
	<tr>
	<td style="font-weight: bold;">Mobile No :</td>
	<td>'.$userData['user_phone'].'</td>
	</tr>
	</table>
	<table style="font-size:12px;border-collapse: collapse;width:100%; margin-top: 20px;" cellpadding="3" cellspacing="3">
	<thead>
	<tr>
	<th style="width:5%;text-align:center;background: #ededed;">Sr.<br/>No.</th>
	<th style="width:35%;text-align:center;background: #ededed;">Item Description</th>
	<th style="width:5%;text-align:center;background: #ededed;">HSN Code</th>
	<th style="width:8%;text-align:center;background: #ededed;">Qty</th>
	<th style="width:8%;text-align:center;background: #ededed;">UOM</th>
	<th style="width:10%;text-align:center;background: #ededed;">Unit Price '.strtoupper($currency_name).'</th>';
	if($disc_row['discount']>0){ 
		$html.='<th style="width:7%;text-align:center;background: #ededed;">Disc.</th>';
	}
	$html.='<th style="width:10%;text-align:center;background: #ededed;">Total '.strtoupper($currency_name).'</th>
	</tr>
	</thead>
	<tbody>';
	
	$qry="SELECT trn.*,product.product_name,hsn.hsn_code,unit_name  FROM `tbl_proforma_trn` as trn LEFT JOIN product_mst as product on product.product_id=trn.product_id LEFT JOIN unit_mst as per on per.unitid=trn.unit_id left join mst_hsn_code as hsn ON hsn.hsn_id = product.product_hsn WHERE trancation_status=0 and invoice_id='$invoiceid' GROUP BY trancation_id ORDER BY product_type,trancation_id";
	$result=$dbcon->query($qry);		
	$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_i_gst=0;$gst_per = 0;$gst_rate = 0;
	$cnt=mysqli_num_rows($result);
	while($row=mysqli_fetch_assoc($result))
	{
		$gst_per = $row['cgst_tax_per']+$row['sgst_tax_per']+$row['igst_tax_per'];
        $gst_rate = $row['cgst_tax_rate_conv']+$row['sgst_tax_rate_conv']+$row['igst_tax_rate_conv'];

        if($row['cgst_tax_rate_conv'] != 0 || $row['sgst_tax_rate_conv'] !=0){
            $total_cs_gst += $gst_rate;
        }else{
            $total_i_gst += $gst_rate;
        }
        //tax summary calculation start
        if(!empty($row['tax_val']))
        {
            $tax_num=explode(",",$row['tax_val']);
            $tax_name=explode(",",$row['tax_name']);
            $total_net_rate=($row['product_qty']*$row['product_rate_conv'])-$row['discount'];
            for($j=0;$j<count($tax_num);$j++)
            {
                if(!in_array($tax_name[$j],$tax['per']))
                {
                    $tax['per'][]=$tax_name[$j];
                }
                $tax['per_total'][$tax_name[$j]]+=$total_net_rate*$tax_num[$j]/100;
            }
        }
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
		$pro_descri = ($row['description']) ? nl2br($row['description']) : '' ;
		$product_hsn_code = ($row['hsn_code']) ? $row['hsn_code'] : $row['hsn_code'] ;
		$pro_disc = ($row['discount_per']) ? $row['discount_per']." %" : '';

		$html.='<tr style="border:none;">
		<td style="text-align:center;vertical-align:top;">'.$i.'</td>
		<td style="text-align:left;vertical-align:top;">
		<strong>'.$row['product_name'].'</strong><br/>
		'.$pro_descri.'
		</td>
		<td style="text-align:center;vertical-align:top;">'.$product_hsn_code.'</td>
		<td style="text-align:center;vertical-align:top;">
		'.$row['product_qty'].'
		</td>
		<td style="text-align:center;vertical-align:top;">'.$row['unit_name'].'</td>
		<td style="text-align:center;vertical-align:top;">'.number_format($row['product_rate_conv'],2,".","").'</td>';
		if($disc_row['discount']>0){
			$html.='<td style="text-align:center;vertical-align:top;">'.number_format($row['product_discount_conv'],2,".","").'<br>('.$pro_disc.')</td>';
		}
		$html.='<td style="text-align:right;vertical-align:top;">'.number_format($row['product_amount_conv'],2,".","").'</td>
		</tr>';
		$i++; 
		$totalqty=$totalqty+$row['product_qty']-$charges_qty;$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
		$total_product_amount+=$row['product_amount_conv'];
		$totaltaxable+=$taxable_amt;
		$total+=$row['total_conv'];
	}
	$pr=8-$cnt;
// 	for($j=0; $j<$pr; $j++){
// 		$html.='<tr style="border:none;">
// 		<td style="border:none;height:25px;"></td>
// 		<td style="border:none;"></td>
// 		<td style="border:none;"></td>
// 		<td style="border:none;"></td>
// 		<td style="border:none;"></td>
// 		<td style="border:none;"></td>';
// 		if($disc_row['discount']>0){
// 			$html.='<td style="border:none;"></td>';
// 		}
// 		$html.='<td style="border:none;"></td>
// 		</tr>';
// 	}
	$remark = ($rel['remark']) ? $rel['remark'] : '';

	$qry11="select sum((tc.tax_per*trn.product_amount_conv)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_proforma_trn as trn 
    left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
    left join tbl_ledger as l on l.l_id=tc.tax_id 
    where tc.tax_additional='1' and trn.invoice_id=".$invoiceid." and trn.trancation_status!=2 and tc.isdelete='0' group by tc.tax_id";
    $resultwer=$dbcon->query($qry11);
    $billnum = mysqli_num_rows($resultwer);

    $qry12="select b.sundry_amount_conv as sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id,b.sundry_gst_amount_conv 
    from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
    left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
    where b.sundry_voucher_id=".$invoiceid." and b.sundry_voucher_table='tbl_proforma_invoice' and b.isdelete='0' and le.default_sundry='0'";
    $resultrty=$dbcon->query($qry12);
    $billnums = mysqli_num_rows($resultrty);

	$rows=(($rel['stateid']==$set_head['stateid']) ? 3 : 2)+1+$billnum+$billnums;
	$html.='<tr>
	<td colspan="'.$colspan.'" rowspan="'.$rows.'" style="text-align:left; vertical-align: top; font-weight: bold;"><span style="color: red;">MAKE ALL CHEQUES PAYABLE TO</span><br>Bank Name : '.$set_head['bank_name'].'<br>Account Name : '.$set_head['company_name'].'<br>Account Number : '.$set_head['ac_no'].'<br>Branch : '.$set_head['branch_name'].'<br>ISFC Code : '.$set_head['ifcs'].'</td>
	<td colspan="2" style="text-align:right; font-weight: bold;background: #ededed;">Total Basic</td>
	<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format($total_product_amount,2,".","").'</td>
	</tr>';
	$resultrtyw=$dbcon->query($qry12);      
    while($row12=mysqli_fetch_assoc($resultrtyw))
    {
    	$html.='<tr>
		<td colspan="2" style="text-align:right; font-weight: bold;background: #ededed;">'.$row12['l_name'].'</td>
		<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format($row12['sundry_amount'],2,".","").'</td>
		</tr>';
		if($rel['stateid']==$set_head['stateid']){
			$total_cs_gst=$total_cs_gst+$row12['sundry_gst_amount_conv'];
		}else{
			$total_i_gst=$total_i_gst+$row12['sundry_gst_amount_conv'];
		}
    }

	if($rel['stateid']==$set_head['stateid']){
		$html.='<tr>
		<td colspan="2" style="text-align:right; font-weight: bold;background: #ededed;">SGST ('.($gst_per/2).' %)</td>
		<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format(($total_cs_gst/2),2,".","").'</td>
		</tr>
		<tr>
		<td colspan="2" style="text-align:right; font-weight: bold;background: #ededed;">CGST ('.($gst_per/2).' %)</td>
		<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format(($total_cs_gst/2),2,".","").'</td>
		</tr>';
	}else{
		$html.='<tr>
		<td colspan="2" style="text-align:right; font-weight: bold;background: #ededed;">IGST ('.($gst_per).' %)</td>
		<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format(($total_i_gst),2,".","").'</td>
		</tr>';
	}
	
    // $result11=$dbcon->query($qry11);        
    while($row11=mysqli_fetch_assoc($resultwer))
    {
    	$html.='<tr>
		<td colspan="2" style="text-align:right; font-weight: bold;background: #ededed;">'.$row11['l_name'].'</td>
		<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format($row11['add_sum'],2,".","").'</td>
		</tr>';
    }
    // $qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
    // from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
    // left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
    // where b.sundry_voucher_id=".$invoiceid." and b.sundry_voucher_table='tbl_proforma_invoice' and b.isdelete='0' and le.default_sundry='0'";
    // $result12=$dbcon->query($qry12);  
    
	$html.='<tr>
	<td colspan="2" style="text-align:right; font-weight: bold;background: #ededed;">Total Amount</td>
	<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format($rel['g_total'],2,".","").'</td>
	</tr>
	<tr>
	<td colspan="'.$colspan.'" style="text-align:left; vertical-align: top; font-weight: bold;">REMARKS: '.$remark.'</td>
	<td colspan="3" style="text-align: right; font-weight: bold;">Total In Words: '.ucwords(convert_number_to_words_new($rel['g_total'],$rel['currency_id'],$currency_word_end,$currency_word_start)).'</td>
	</tr>
	<tr>
	<td colspan="'.$cols.'" style="vertical-align:top; text-align: left;"><strong>Terms & Conditions:</strong><br>'.$rel['terms_condition'].'</td>
	</tr>
	<tr>
	<td colspan="'.$cols.'" style="vertical-align:bottom; text-align: right; height: 80px; font-weight: bold;"><img src="'.DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'].'" style="height: 100px;width: 100px;"/><br>Authorised Signatory</td>
	</tr>
	<tr>
	<td colspan="'.$cols.'" style="text-align: center;font-weight: bold;">THANK YOU FOR YOUR BUSINESS</td>
	</tr>
	</tbody></table>
	<div style="clear:both;"></div>
	</div>
	<!--page1 end-->';

	$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
	// echo $html;exit;
	ob_end_clean();
	include("../../view/export/mpdf/mpdf.php");
	$mpdf=new mPDF('UTF-8','A4','0','calibri','10','10','40','5','1','1');
	$mpdf->defaultheaderfontsize = 10; /* in pts */
	$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
	$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
	$mpdf->defaultfooterfontsize = 10; /* in pts */
	$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
	$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
	$mpdf->SetHTMLHeader($header);
		//$mpdf->SetHTMLFooter($footer);

		//Show page number
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
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$purchaseorder_id.'.pdf','f');
	ob_clean();
	return 'Proforma Invoice '.$rel['invoice_no'].'.pdf';
}


?>