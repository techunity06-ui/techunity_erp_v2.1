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
	$query="select po.*,state.state_name,modesup.mode_dispatch,payterms.payment_terms as payment_term,l.l_name as vender_name,country.country_name,l.m_address as vender_address,l.gst_no as tin_no,l.cust_mobile as vender_mobile, l.stateid, state.gst_state_code, city.city_name, bmast.branch_address, city1.city_name as bcity, state1.state_name as bstate, country1.country_name as bcountry, l.emp_signature_img,gd.gd_address,comp.company_name,le.l_name as con_ven,bm.branch_name as cons_bran from tbl_purchaseorder as po 
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
	left join mst_godown as gd on gd.gd_id = po.godown_id
	left join tbl_company as comp on comp.company_id = po.company_id
	left join tbl_ledger as le on le.l_id = po.con_vender_id
	left join branch_mst as bm on bm.branch_id = po.con_branch
	where po.purchaseorder_id=$purchaseorder_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$delivery_type = $rel['delivery_type'];
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

	if($rel['cons_same_as']==0){
		if($rel['con_type'] ==1){
			$cons_name = $rel['cons_bran'];
		}else if($rel['con_type'] ==2){
			$cons_name = $rel['con_ven'];
		}else{
			$cons_name = $rel['company_name'];
		}

		$consignee_address = $rel['con_address'];
		$party_address_con = '<strong>'.$cons_name.'</strong><br>'.$consignee_address;
	}else{
		$cons_name 		   = $rel['company_name'];
		$consignee_address = $set_head['address'];
		$party_address_con = '<strong>'.$cons_name.'</strong><br>'.$consignee_address;
	}
	/*$cons_company_name	= $rel['company_name'];
	$cons_cust_address	= $rel['cust_address'];
	$cons_gst_no		= $rel['gst_no'];
	$cons_state_name	= $rel['state_name'];
	$cons_gst_state_code= $rel['gst_state_code'];
	$cons_city_name		= $rel['city_name'];
	$cons_country_name	= $rel['country_name'];

		//consignee
	if(!empty($rel['consignee_id']))
	{	
		$consignee="select * from tbl_custmer_consignee as cust 
		left join country_mst as country on country.countryid=cust.countryid
		left join state_mst as state on state.stateid=cust.stateid 
		left join city_mst as city on city.cityid=cust.cityid where cust_id=".$rel['consignee_id'];
		$cons_data=mysqli_fetch_assoc($dbcon->query($consignee));	
		$cons_company_name=$cons_data['company_name'];
		$cons_cust_address=$cons_data['cust_address'];
		$cons_gst_no=$cons_data['gst_no'];
		$cons_state_name=$cons_data['state_name'];
		$cons_gst_state_code=$cons_data['gst_state_code'];
		$cons_city_name=$cons_data['city_name'];
		$cons_country_name=$cons_data['country_name'];

	}*/

	/* Check Discount is On or off Start */
	if($set_head['show_disc']=='1'){
		$colspan=5;
		$dynamicwidth=40;
	}else{
		$colspan=4;
		$dynamicwidth=46;
	} 
	
	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));

	$header ='<table style="border: none; width: 100%;">
		<tbody>
			<tr>
				<td colspan="2" style="height:25px"> </td>
			</tr>
			<tr style="border: none;">
				<td style="border: none; width: 65%; vertical-align: top;"><img src="'.DOMAIN_F.LOGO.$set_head['logo'].'" /></td>
				<td style="border: none; width: 35%; text-align: right;"><h1>'.$form.'</h1></td>
			</tr>
		</tbody>
	</table>';
//$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';

	$approve_status='';
	if($rel['approve_status']=='0'){
		$approve_status=' (DRAFT)';
	}

	$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
	$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

	$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
	$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
	$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));

	$sundrytax=$dbcon->query("select b.* from tbl_bill_sundry_transaction as b where b.sundry_voucher_id=".$rel['purchaseorder_id']." and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' ");
	//$sundrytax_rels = mysqli_fetch_assoc($sundrytax);
	$total_sundrytax=0;
	while($sumsundrytax=mysqli_fetch_assoc($sundrytax)){
		$total_sundrytax = $total_sundrytax + $sumsundrytax['sundry_gst_amount_conv'];
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
			<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<tr>
					<td rowspan="6" style="vertical-align: top; width: 70%;"><strong>Vendor Detail:<br>To,<br>K/A :</strong> '.$rel['vender_name'].'<br><strong>Name :</strong> '.$rel['vender_name'].'<br><strong>Address :</strong> '.nl2br($rel['vender_address']).'<br><strong>GST No :</strong> '.$rel['tin_no'].'<br><strong>Contact No :</strong> '.$rel['vender_mobile'].'<br><strong><br>Delivery Address :<br>Shipping Name :</strong> '.$party_address_con.'<br><strong>GST No :</strong> '.$set_head['vatno'].'<br><strong>Contact No :</strong> '.$set_head['contact_no'].'</td>
					<td style="width: 15%;font-weight: bold;">Order No :</td>
					<td style="width: 15%;">'.$rel['purchaseorder_no'].'</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Order Date :</td>
					<td>'.$purchaseorder_date.'</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Reference No :</td>
					<td></td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Reference Date :</td>
					<td></td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Delivery Date :</td>
					<td>'.date('d-m-Y',strtotime($rel['purchaseorder_due_date'])).'</td>
				</tr>
				<tr>
					<td style="font-weight: bold;">Payment Terms :</td>
					<td>'.$rel['payment_term'].'</td>
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
		if($set_head['show_disc']=='1'){ 
			$html.='<th style="width:7%;text-align:center;background: #ededed;">Disc.</th>';
		}
		$html.='<th style="width:10%;text-align:center;background: #ededed;">Total '.strtoupper($currency_name).'</th>
		</tr>
		</thead>
		<tbody>';
		$qry="select trn.*,product.*,product.product_desc as scode,per.unit_name,per1.unit_name as base_unit_name,per2.unit_name as conv_unit_name,group_concat(tax.tax_value) as tax_val,group_concat(tax.tax_name) as tax_name, hsn.hsn_code FROM `tbl_purchaseordertrn` as trn 
		left join product_mst as product on product.product_id=trn.product_id 
		left join unit_mst as per on per.unitid=trn.unit_id 
		left join unit_mst as per1 on per1.unitid=product.product_base_unit 
		left join unit_mst as per2 on per2.unitid=product.product_conv_unit 
		left join mst_hsn_code as hsn on hsn.hsn_id=product.product_hsn
		left join `formula_mst` as ftax on ftax.formulaid=trn.formulaid left join tbl_tax as tax on find_in_set(tax.tax_id,ftax.tax_id)
		where purchaseordertrn_status=0 and purchaseorder_id=".$rel['purchaseorder_id']." group by purchaseordertrn_id order by purchaseordertrn_id";
		$result=$dbcon->query($qry);		
		$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_i_gst=0;$gst_per = 0;$gst_rate = 0;
		$cnt=mysqli_num_rows($result);
		while($row=mysqli_fetch_assoc($result))
		{
			$gst_per 	= $row['cgst_tax_per']+$row['sgst_tax_per']+$row['igst_tax_per'];
        	$gst_rate 	= $row['cgst_tax_rate_conv']+$row['sgst_tax_rate_conv']+$row['igst_tax_rate_conv'];

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
            $total_net_rate=($row['product_qty']*$row['product_currency_rate'])-$row['discount'];
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
			$pro_descri = ($row['product_des']) ? $row['product_des'] : '' ;
			$product_hsn_code = ($row['product_hsn_code']) ? $row['product_hsn_code'] : $row['hsn_code'] ;
			$pro_disc = ($row['discount_per']) ? $row['discount_per']." %" : '';

			$html.='<tr style="border:none;">
			<td style="text-align:center;vertical-align:top;">'.$i.'</td>
			<td style="text-align:left;vertical-align:top;">
			<strong>'.$row['product_name'].'</strong><br/>
			'.$pro_descri.'';
			if($delivery_type == 'product_wise'){
				$retu_date = "select sdate.*,unit.unit_name from tbl_purchaseorder_delivery_date as sdate left join unit_mst as unit on unit.unitid=sdate.unit_id where po_delivery_date_status=0 and purchaseordertrn_id=".$row['purchaseordertrn_id'];
				$resadate=$dbcon->query($retu_date);
		
				$html .='<table width="40%" style="font-size:13px">
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
			<td style="text-align:center;vertical-align:top;">'.$product_hsn_code.'</td>
			<td style="text-align:center;vertical-align:top;">
			'.$row['product_qty'].'
			</td>
			<td style="text-align:center;vertical-align:top;">'.$row['unit_name'].'</td>
			<td style="text-align:center;vertical-align:top;">'.number_format($row['product_currency_rate'],2,".","").'</td>';
			if($set_head['show_disc']=='1'){
				$html.='<td style="text-align:center;vertical-align:top;">'.number_format($row['product_discount_conv'],2,".","").'<br>('.$pro_disc.')</td>';
			}
			$html.='<td style="text-align:center;vertical-align:top;">'.number_format($row['product_currency_amount'],2,".","").'</td>
			</tr>';
			$i++; 
			$totalqty=$totalqty+$row['product_qty']-$charges_qty;$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
			$total_product_amount+=$row['product_currency_amount'];
			$totaltaxable+=$taxable_amt;
			$total+=$row['currency_total'];
		}
		/*$pr=10-$cnt;
		for($j=0; $j<$pr; $j++){
			$html.='<tr style="border:none;">
			<td style="border:none;height:25px;"></td>
			<td style="border:none;"></td>
			<td style="border:none;"></td>
			<td style="border:none;"></td>
			<td style="border:none;"></td>
			<td style="border:none;"></td>';
			if($set_head['show_disc']=='1'){
				$html.='<td style="border:none;"></td>';
			}
			$html.='<td style="border:none;"></td>
			</tr>';
		}*/
		$remark = ($rel['remark']) ? $rel['remark'] : '';
		$qry12="select b.sundry_amount_conv as sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
		from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
		left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
		where b.sundry_voucher_id=".$rel['purchaseorder_id']." and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' and le.default_sundry='0'";
		$result12=$dbcon->query($qry12);
		$cnta = brp_mysqli_num_rows($result12);
		$rows=(($rel['stateid']==$set_head['stateid']) ? 2 : 1)+2+$cnta;
		$html.='<tr>
		<td colspan="'.($colspan).'" rowspan="'.$rows.'" style="text-align:left; vertical-align: top; color: red; font-weight: bold;">REMARKS: '.$remark.'</td>
		<td colspan="2" style="text-align:right; font-weight: bold;background: #ededed;">Total Basic</td>
		<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format($total_product_amount,2,".","").'</td>
		</tr>';

		if($rel['stateid']==$set_head['stateid']){
			$addontax = $total_cs_gst + $total_sundrytax;
			$html.='<tr>
			<td colspan="2" style="text-align:right; font-weight: bold;background: #ededed;">SGST <!--('.($gst_per/2).' %)--></td>
			<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format(($addontax/2),2,".","").'</td>
			</tr>
			<tr>
			<td colspan="2" style="text-align:right; font-weight: bold;background: #ededed;">CGST <!--('.($gst_per/2).' %)--></td>
			<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format(($addontax/2),2,".","").'</td>
			</tr>';
		}else{
			$addontax = $total_i_gst + $total_sundrytax;
			$html.='<tr>
			<td colspan="2" style="text-align:right; font-weight: bold;background: #ededed;">IGST <!--('.($gst_per).' %)--></td>
			<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format(($addontax),2,".","").'</td>
			</tr>';
		}

		

		while($row12=mysqli_fetch_assoc($result12)){
			$html .='<tr>
				<td colspan="2" style="text-align:right; font-weight: bold;background: #ededed;">'.$row12['l_name'].'</td>
				<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format($row12['sundry_amount'],2,".","").'</td>
				</tr>';
		}
		$currency_code = getcurrencydetail($dbcon,$rel['currency_id']);
		$html.='<tr>
		<td colspan="2" style="text-align:right; font-weight: bold;background: #ededed;">Total Amount</td>
		<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format($rel['g_total_conv'],2,".","").'</td>
		</tr>
		<tr>
		<td colspan="'.($colspan+3).'" style="text-align: right; font-weight: bold;">Total In Words: '.ucwords(convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$currency_code['currency_in_word_end'],$currency_code['currency_in_word'])).'</td>
		</tr>
		<tr>
		<td colspan="'.($colspan+3).'" style="vertical-align:bottom; text-align: right; height: 80px;"><img src="'.DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'].'" style="height: 75px;width: 75px;"/><br>Authorised Signatory</td>
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
		$mpdf=new mPDF('','A4','0','calibri','10','10','40','5','1','1');
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
		return 'Purchase Order '.$purchaseorder_id.'.pdf';
	}

?>