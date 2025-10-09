<?php session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
$type='pdf';
$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
if(strtolower($type) == 'pdf') {
	$query="SELECT invoice.*, country.country_name, state.state_name, cust.stateid, state.gst_state_code, city.city_name, cust.l_name, cust.m_address, type.invoice_type, cust.cust_pincode, cust.cust_mobile, cust.gst_no, dispatch.mode_dispatch, cust.m_pan, cust.enable_sez, pay.payment_terms as terms, cust.cust_cont_name from tbl_invoice as invoice 
	left join tbl_ledger as cust on cust.l_id=invoice.cust_id
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid
	left join city_mst as city on city.cityid=cust.cityid
	left join pay_terms as pay on pay.terms_id=invoice.payment_terms
	left join mode_of_dispatch as dispatch on dispatch.mode_dis_id=invoice.dispatch_doc_no
	left join tbl_invoicetype as type on type.invoicetype_id=invoice.invoicetype_id
	where invoice.invoice_id=$invoiceid";
	$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
	$cons_gst_no=$rel['gst_no'];
	$cons_pan_no=$rel['pan_no'];
	$cons_state_name=$rel['state_name'];
	$cons_gst_state_code=$rel['gst_state_code'];
	$place_of_supply=$rel['city_name'];
	if(!empty($rel['consignee_id']))
	{	
		$consignee="select * from tbl_custmer_consignee as cust 
		left join country_mst as country on country.countryid=cust.countryid
		left join state_mst as state on state.stateid=cust.stateid 
		left join city_mst as city on city.cityid=cust.cityid where cust_id=".$rel['consignee_id'];
		$cons_data=brp_mysqli_fetch_assoc($dbcon->query($consignee));	
		$cons_gst_no=$cons_data['gst_no'];
		$cons_pan_no=$cons_data['pan_no'];
		$cons_state_name=$cons_data['state_name'];
		$cons_gst_state_code=$cons_data['gst_state_code'];
		$place_of_supply=$cons_data['city_name'];
	}

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$set_head=brp_mysqli_fetch_assoc($dbcon->query($set));	
	$challan_date='';$lr_date='';$dispatch_date='';
	if($rel['challan_date']!="1970-01-01" && $rel['challan_date']!="0000-00-00")
	{
		$challan_date=date('d-m-Y',strtotime($rel['challan_date']));
	}
	if($rel['dispatch_date']!="1970-01-01 00:00:00" && $rel['dispatch_date']!="0000-00-00 00:00:00")
	{
		$dispatch_date=date('d-m-Y h:i a',strtotime($rel['dispatch_date']));
	}

	$po_no = '';
	$po_date = '';
	if($rel['is_sales_order']!=0){
		$qry = $dbcon->query("SELECT po_no, po_date FROM tbl_sales_order WHERE sales_order_id = '".$rel['sales_order_id']."'");
		$rels = brp_mysqli_fetch_assoc($qry);
		$po_no = $rels['po_no'];
		$po_date = ($rels['po_date']!='1970-01-01' && $rels['po_date']!='0000-00-00') ? $rels['po_date'] : '';
	}
	else
	{
		$po_no = $rel['order_no'];
		if($rel['order_date']!="1970-01-01")
		{
			$po_date = date("d/m/Y",strtotime($rel['order_date']));
		}
		else
		{
			$po_date='';
		}
	}

	$custLedgerDetails = get_cust_data_arr($dbcon,$rel['cust_id']);
	$company_config = getCompanyConfiguration($dbcon);
	$sales_pro_print=explode(",", $company_config['sales_pro_print']);

	if($rel['sales_order_id']!='')
	{
		$so_number = explode(",",$rel['sales_order_id']);
		for($i=0;$i<count($so_number);$i++)
		{
			$po_number.= get_id_detail($dbcon,'tbl_sales_order','sales_order_id',$so_number[$i],'po_no').',';
			$po_number_date.= date("d/m/Y",strtotime(get_id_detail($dbcon,'tbl_sales_order','sales_order_id',$so_number[$i],'po_date'))).',';

		}
	}
	else
	{
		$po_number = $rel['order_no'];
		$po_number_date = $rel['order_date'];
	}

	$sundrytax=$dbcon->query("select b.* from tbl_bill_sundry_transaction as b where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' ");
	$total_sundrytax=0;
	while($sumsundrytax=brp_mysqli_fetch_assoc($sundrytax)){
		$total_sundrytax = $total_sundrytax + $sumsundrytax['sundry_gst_amount'];
	}
	$sel_t = $dbcon->query("select trn.*,t.id,t.transportation_name from tbl_transport_transaction as trn 
		left join transportation_details as t on t.id=trn.transport_id
		where transport_transaction_table_id='$invoiceid'");
	$r_t=brp_mysqli_fetch_assoc($sel_t);

	$qry_disc="SELECT SUM(trn.product_discount) as discount FROM `tbl_invoicetrn` as trn where trancation_status=0 and invoice_id=".$rel['invoice_id'];
	$rel_disc = brp_mysqli_fetch_assoc($qry_disc);

	/* Check Discount is On or off Start */
	if($rel_disc['discount'] > 0){
		$colspan=5;
		$dynamicwidth=40;
	}else{
		$colspan=6;
		$dynamicwidth=46;
	}

	$header ='<table style="border: 1px solid; width: 100%;">
	<tr>
	<td colspan="3" style="border: 0px; "><img src="'.DOMAIN_F.LOGO.$set_head['logo'].'" style="width:2in;height: 70px;padding-top:0px;" /></td>
	<td colspan="6" style="text-align:center;border: 0px;">
	<span style="font-size:16px;"><b>'.(($rel["invoice_type"]) ? $rel["invoice_type"] : 'TAX INVOICE').'</b></span><br/>
	<span style="font-size:16px;"><b>'.$set_head["company_name"].' </b></span><br/>
	<span style="font-size:12px;">'.$set_head["address"].'</span><br>
	<span style="font-size:12px;">CIN : '.$set_head["cin"].'</span>
	</td>
	</tr>
	</table>'; 
	$footer = '';

	$html ='<html>
	<head>					
	<title>Invoice - '.$rel['invoice_no'].'</title>
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
			font-size:12px;
			border:1px solid black;
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
		<table style="font-size:10px;border-collapse: collapse;border-top:none !important;" cellpadding="0" cellspacing="0" width="100%" >';
		if($rel['enable_sez'] == 1 || $rel['sales_type'] == 2){ 
			$html.='<tr>																		
			<td colspan="6" style="padding: 10px;"><span style="font-weight:normal;">( SUPPLY MEANT FOR EXPORT/SUPPLY TO SEZ UNIT OR SEZ DEVELOPER FOR AUTHORIZED OPERATIONS UNDER BOND OR LETTER OF UNDERTAKING WITHOUT PAYMENT OF IGST ) </span> </td>
			</tr>';
		}
		if(!empty($rel['einv_Irn'])){
			$html.='<tr>
			<td class="" style="vertical-align:top;border:1px solid; border-right:none !important;text-align: left;" colspan="4">
			<strong>IRN No : </strong>'.$rel['einv_Irn'].' <br>
			<strong>ACK No : </strong>'.$rel['einv_AckNo'].' <br>
			<strong>ACK Date : </strong>'.date('d-m-Y',strtotime($rel['einv_AckDate'])).'
			</td>
			<td colspan="2" style="vertical-align:top;border-bottom:1px solid; border-right:1px solid;border-top:1px solid;text-align: right;">
			<img style="height: 75px;width: 75px;"  src="data:image/png;base64,'.$rel['einv_SignedQRCode'] .'" />
			</td>
			</tr>';
		}
		$html.='<tr>
		<td style="width:16.5%;white-space: nowrap; border:1px solid; border-right:none !important;"><strong>Invoice No </strong>
		</td>
		<td style="width:16.5%;white-space: nowrap; border-bottom:1px solid; border-right:1px solid;border-top:1px solid;border-left:none !important;">: <strong>'.$rel['invoice_no'].'</strong>
		</td>
		<td style="width:16.5%;white-space: nowrap; border-bottom:1px solid;border-top:1px solid; border-right:none;"><strong>Date </strong>
		</td>
		<td style="width:16.5%;white-space: nowrap; border-bottom:1px solid;border-right:1px solid;border-top:1px solid;border-left:none;">: <strong>'.date('d-m-Y',strtotime($rel['invoice_date'])).'</strong>
		</td>
		<td style="width:16.5%;white-space: nowrap; border-bottom:1px solid;border-top:1px solid;border-right:none;"><strong>Transport Detail</strong>	
		</td>							
		<td style="width:16.5%;white-space: nowrap; border-bottom:1px solid;border-top:1px solid;border-right:1px solid;border-left:none;white-space: nowrap;">: '.$r_t['transportation_name'].'</td>
		</tr>
		<tr>
		<td style="border-bottom:1px solid;border-left:1px solid;white-space:nowrap;border-right:none;"><strong>Challan No </strong></td>
		<td style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.(($rel['challan_no']) ? $rel['challan_no'] : '').' </td>
		<td style="border-bottom:1px solid;border-right:none;"><strong>Date </strong></td>
		<td style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.$challan_date.'</td>
		<td style="border-bottom:1px solid;white-space:nowrap;border-right:none;"><strong>Docket No. </strong></td>							
		<td style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.$r_t['transport_doc_no'].'</td>
		</tr>
		<tr>
		<td style="border-bottom:1px solid;border-left:1px solid;border-right:none;"><strong>Po No </strong></td>
		<td style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.$po_number.' </td>
		<td style="border-bottom:1px solid;border-right:none;"><strong>Date </strong></td>
		<td style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.$po_number_date.'</td>
		<td style="border-bottom:1px solid;white-space:nowrap;border-right:none;"><strong>Place of Supply</strong></td>							
		<td style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.$place_of_supply.'</td>
		</tr>
		<tr>
		<td style="border-bottom:1px solid;border-left:1px solid; border-right:none;"><strong>State</strong></td>
		<td style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.$set_head['state_name'].'
		<td class="boderremoveres" style="border-bottom:1px solid;border-right:none;"><strong>Code</strong></td>
		<td style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.$set_head['gst_state_code'].'</td>
		<td style="border-bottom:1px solid;white-space:nowrap;white-space:nowrap;border-right:none;"><strong>Payment Terms</strong> </td>
		<td style="border-bottom:1px solid;border-right:1px solid;border-left:none;white-space: nowrap;">: '.$rel['terms'].'</td>
		</tr>
		<tr>
		<td style="border-bottom:1px solid;border-left:1px solid;white-space:nowrap;border-right:none;"><strong>E-way Bill No.</strong></td>
		<td style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.$rel['eway_bill_no'].'
		<td class="boderremoveres" style="border-bottom:1px solid;border-right:none;"><strong>Vehicle No</strong></td>
		<td style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.$r_t['transport_vehicle_no'].'</td>
		<td style="border-bottom:1px solid;border-right:none;"><strong>Reverse Charge</strong></td>
		<td style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.(!empty($rel['reverse_charge'])?'Yes':'No').'</td>
		</tr>
		<tr>
		<td colspan="4" width="0%" style="vertical-align:top;border-right:1px solid;border-left:1px solid;"><b>Bill to Party : </b><br/><strong>'.$rel['l_name'].'</strong><br><span style="font-weight:normal;">'.$rel['m_address'].'<br/>'.$rel['city_name'].', '.$rel['state_name'].', '.$rel['country_name'];
		if(!empty($rel['cust_pincode'])){	
			$html.='-  '.$rel['cust_pincode'];
		}
		$html.='</span><br>';
		if($company_config['enable_hypothication']==1 && $rel['check_hypothication']!=0){
			$html.='<strong>
			Hypothecation with '.get_id_detail($dbcon,'bank_mst','bankid',$rel['hypo_bank'],'bank_name').'
			</strong><br>';
		} 
		$html.='Mobile no : '.$rel['cust_mobile'].'</td>';
		if($rel['enable_consignee']==1) {
			$html.='<td colspan="2"  style="vertical-align:top;border-right:1px solid"><b>Shipped to Party : </b><br><strong>'.$rel['l_name'].'</strong><br><span style="font-weight:normal;">'.$rel['m_address'].'<br/>'.$rel['city_name'].', '.$rel['state_name'].', '.$rel['country_name'];
			if(!empty($rel['cust_pincode'])){
				$html.='-  '.$rel['cust_pincode'];
			}
			$html.='</span><br>';
			if($company_config['enable_hypothication']==1 && $rel['check_hypothication']!=0){
				$html.='<strong>
				Hypothecation with '.get_id_detail($dbcon,'bank_mst','bankid',$rel['hypo_bank'],'bank_name');
				$html.='</strong><br>';
			} 
			$html.='Mobile no : '.$rel['cust_mobile'].'</td>';
		} else { 
			$html.='<td colspan="2"  style="vertical-align:top;border-right:1px solid"><b>Consignee : </b><br><strong>'.$cons_data['company_name'].'</strong><span style="font-weight:normal;">'.$cons_data['m_address'].'<br/>'.$cons_data['city_name'].', '.$cons_data['state_name'].', '.$cons_data['country_name'];
			if(!empty($cons_data['cust_pincode'])){
				$html.='-  '.$cons_data['cust_pincode'];
			}
			$html.='</span><br>Mobile no : '.$cons_data['cust_mobile'].'</td>';
		}
		$html.='</tr>';
		if($rel['enable_consignee']==1) {
			$html.='<tr>
			<td colspan="4" style="border-right:1px solid;border-left:1px solid;"><strong>GSTIN: '.$rel['gst_no'].' <br>PAN No. : '.$rel['m_pan'].'</strong></td>
			<td colspan="2" style="border-right:1px solid; vertical-align: top;"><strong>GSTIN:  '.$rel['gst_no'].'</strong></td>
			</tr>
			<tr> 
			<td colspan="2" style="border-left:1px solid;border-bottom:1px solid;font-weight:normal;">State : '.$rel['state_name'].'</td>
			<td colspan="2" style="border-right:1px solid;text-align:left;border-bottom:1px solid;border-right:1px solid;font-weight:normal;">Code : '.$rel['gst_state_code'].'</td>
			<td style="text-align:left;border-bottom:1px solid;font-weight:normal;">State : '.$rel['state_name'].'</td>
			<td style="text-align:left;border-bottom:1px solid;border-right:1px solid;font-weight:normal;">Code : '.$rel['gst_state_code'].'</td>
			</tr>';
		} else {
			$html.='<tr>
			<td colspan="4" style="border-right:1px solid;border-left:1px solid;"><strong>GSTIN:'.$rel['gst_no'].' </strong></td>
			<td colspan="2" style="border-right:1px solid;"><strong>GSTIN: '.$cons_gst_no.'</strong></td>
			</tr>
			<tr> 
			<td colspan="2" style="border-left:1px solid;border-bottom:1px solid;font-weight:normal;">State : '.$rel['state_name'].'</td>
			<td colspan="2" style="border-right:1px solid;text-align:left;border-bottom:1px solid;border-right:1px solid;font-weight:normal;">Code : '.$cons_data['gst_state_code'].'</td>
			<td style="text-align:left;border-bottom:1px solid;font-weight:normal;">State : '.$cons_state_name.'</td>
			<td style="text-align:left;border-bottom:1px solid;border-right:1px solid;font-weight:normal;">Code : '.$cons_gst_state_code.'</td>
			</tr>';
		}
		$html.='</table>
		<table style="font-size:10px;border-collapse: collapse;border-top:none !important;" cellpadding="0" cellspacing="0" width="100%" >
		<thead>
		<tr>
		<th width="3%" style="border:1px solid;border-top: none;"><strong>SR.<br/> NO.</strong></th>
		<th width="'.$dynamicwidth.'%" style="border:1px solid;border-top: none;" ><strong>Particulars </strong></th>
		<th width="8%" style="border:1px solid;border-top: none;"><strong>HSN/SAC <br/>Code</strong></th>
		<th width="7%" style="border:1px solid;border-top: none;"><strong>QTY.</strong></th>
		<th width="7%" style="border:1px solid;border-top: none;"><strong>Rate</strong></th>';
		if($rel_disc['discount'] > 0){
			$html.='<th width="6%" style="border:1px solid;border-top: none;"><strong>Less:<br/>Disc.</strong></th>';
		}
		$html.='<th width="9%" style="border:1px solid;border-top: none;"><strong>Taxable<br/>Value</strong></th>';
		if($company_config['tax_editable'] == 0 && $rel['sales_type'] == 1 ){
			$html.='<th width="4%" style="border:1px solid;border-top: none;"><strong>GST Rate</strong></th> 
			<th width="6%" style="border:1px solid;border-top: none;"><strong>GST Amount</strong></th>
			<th width="10%" style="border:1px solid;border-top: none;"><strong>Total</strong></th>';
		}
		$html.='</tr>
		</thead>
		<tbody style="border: none;">';
		$qry="select trn.*,product.product_name,unit_name,product.product_icode FROM `tbl_invoicetrn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trancation_id order by product.product_type,trancation_id";

		$result=$dbcon->query($qry);		
		$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;
		$cnt=brp_mysqli_num_rows($result);
		while($row=brp_mysqli_fetch_assoc($result))
		{
			$gst_per = $row['cgst_tax_per']+$row['sgst_tax_per']+$row['igst_tax_per'];
			$gst_rate = $row['cgst_tax_rate']+$row['sgst_tax_rate']+$row['igst_tax_rate'];

			if($row['cgst_tax_rate'] != 0 || $row['sgst_tax_rate'] !=0){
				$total_cs_gst += $gst_rate;
			}else{
				$total_i_gst += $gst_rate;
			}
			$item_code = '';
			if(in_array('item',$sales_pro_print)){
				$item_code = " -- (".$row['product_icode'].")";
			}
			$html.='<tr style="height:25px">
			<td class="borderleftadd" style=" vertical-align:top;border-right:1px solid;border-left:1px solid;">'.$i.'</td>
			<td style="border-right:1px solid;vertical-align:top;"><strong>'.$row['product_name'].'</strong>'.$item_code;
			if($company_config['enable_item_description']==1){
				$html.='<br>'.(($row['description']) ? $row['description'] : '').'<br>';
			}
			$batch_detail = "select bst.*,st.batch_no from tbl_batch_stock_tmp as bst
			left join `tbl_stock_trn` as st on st.stock_id=bst.stock_id where invoice_trn_id = ".$row['trancation_id']." and status =1";
			$brtch_q = $dbcon->query($batch_detail);
			while($r = brp_mysqli_fetch_array($brtch_q)){
				$html.='<span><strong>Batch : </strong>'.$r['batch_no'].'</span>	
				<span><strong>Qty : </strong>'.$r['qty'].'</span><br>';
			}
			$html.='</td>
			<td style=" border-right:1px solid;vertical-align:top;" >'.$row['product_hsn_code'].'</td>
			<td style="vertical-align:top; border-right:1px solid;white-space:nowrap;" >';
			if($row['product_type']!='3'){
				$html.=$row['product_qty'].' '.$row['unit_name'];
			}else{
				$charges_qty+=$row['product_qty'];
				$html.=$charges_qty;
			}
			$html.='</td>
			<td style="vertical-align:top; border-right:1px solid; text-align: center;" >'.number_format($row['product_rate'],2,".","").'</td>';
			if($rel_disc['discount'] > 0){
				$html.='<td style=" vertical-align:top;border-right:1px solid; text-align: center;">'.number_format($row['discount_per'],2,".","").'%'.'</td>';
			}
			$html.='<td style=" vertical-align:top;border-right:1px solid; text-align: center;">'.number_format(($row['product_rate'] * $row['product_qty']),2,".","").'</td>';
			if($company_config['tax_editable'] == 0 && $rel['sales_type'] == 1 ){
				$html.='<td style=" vertical-align:top;border-right:1px solid; text-align: center;">'.$gst_per.'%</td>
				<td style="vertical-align:top;border-right:1px solid; text-align: center;">'.number_format($gst_rate,2,".","").'</td>
				<td style="vertical-align:top;border-right:1px solid; text-align: center;">'.number_format($row['total'],2,".","").'</td>';
			}
			$html.='</tr>';
			$i++; 
			$totalqty=$totalqty+$row['product_qty']-$charges_qty;
			$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
			$total_product_amount+=($row['product_qty']*$row['product_rate']);
			$totaltaxable+=$row['product_amount'];
			$totaltax1+=$row['tax_amount1'];
			$totaltax2+=$row['tax_amount2'];
			$total+=$row['total'];
			$total_gst_rate +=$gst_rate;
		}
		$pr = 8-$i;
		
		for($j=0; $j<$pr; $j++){
			$html .= '<tr style="border:none;border-left:1px solid;border-right:1px solid;height:30px">
			<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
			<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
			<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
			<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
			<td style="text-align:center;border:1px solid;vertical-align:top;"></td>';
			if($rel_disc['discount'] > 0){
				$html .= '<td style="text-align:center;border:1px solid;vertical-align:top;"></td>';
			}
			$html .= '<td style="text-align:center;border:1px solid;vertical-align:top;"></td>';
			if($company_config['tax_editable'] == 0 && $rel['sales_type'] == 1 ){
				$html .= '<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>';
			}
			$html .= '</tr>';
		}
		$html.='<tr style="height:20px">
		<td style="border-top:1px solid;border-right:1px solid;border-left:1px solid; text-align:center;" colspan="3"><strong>Total</strong></td>
		<td style="text-align:center;border-top:1px solid;border-right:1px solid;"><strong>'.number_format($totalqty,2,".","").'</strong></td>';
		if($rel_disc['discount'] > 0){
			$html.='<td style="border-top:1px solid;border-right:1px solid;"></td>';
		}
		$html.='<td style="border-top:1px solid;border-right:1px solid;"></td>
		<td style="border-top:1px solid;border-right:1px solid;text-align:center;"><strong>'.number_format($totaltaxable,2,".","").'</strong></td>';
		if($company_config['tax_editable'] == 0 && $rel['sales_type'] == 1 ){
			$html.='<td style="border-top:1px solid;border-right:1px solid;text-align:center;"></td>
			<td style="border-top:1px solid;border-right:1px solid;text-align:center;"><strong>'.number_format($total_gst_rate,2,".","").'</strong></td>
			<td style="border-top:1px solid;border-right:1px solid;text-align:right;"><strong>'.number_format($total,2,".","").'</strong></td>';
		}
		$html.='</tr>
		</table>';
		$html.='<table width="100%" style="border: none;">
		<tr height="20px">
		<td width="50%" style="border-right:1px solid;">';
		if(!empty($set_head['bank_name'])){
			$html.='<strong>Bank Name:</strong> '.$set_head['bank_name'].', ';
		}
		if(!empty($set_head['ac_no'])){
			$html.='<strong>A/c No:</strong> '.$set_head['ac_no'];	 
		}
		$html.='</td>
		<td width="30%" style="border-right:1px solid;text-align:left  !important">Taxable Amount</td>
		<td style="text-align:right;" width="20%">'.number_format($totaltaxable,2,".","").'</td>	
		</tr>';
		if($rel['stateid']==$set_head['stateid'] && ($custLedgerDetails['enable_sez'] == 0)){
			if($company_config['tax_editable'] == 0){ 
				$c_gst = ($total_sundrytax+$total_cs_gst)/2;
				$s_gst = ($total_sundrytax+$total_cs_gst)/2;
			}else{
				$c_gst = $rel['cgst'];
				$s_gst = $rel['sgst'];
			}
			$merchantTax = ($totaltaxable*(0.1))/100;
			if($rel['sales_type'] == 2){ 
				$c_gst = $merchantTax/2;
				$s_gst = $merchantTax/2;
			}

			$html.='<tr height="20px">
			<td  style="border-right:1px solid;border-top:1px solid;">';
			if(!empty($set_head['ifcs'])){ 
				$html.='<strong>IFSC:</strong>'.$set_head['ifcs'].',';
			}
			if(!empty($set_head['branch_name'])){
				$html.='<strong>Branch :</strong> '.$set_head['branch_name'];
			}
			$html.='</td>
			<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left" >CGST</td>
			<td style="text-align:right; border-top:1px solid;font-size:10px;border-left:1px solid; ">'.number_format($c_gst,2,".","").'</td>
			</tr>
			<tr height="20px">
			<td  style="border-right:1px solid;border-top:1px solid; font-size:10px;"></td>
			<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left" >SGST</td>
			<td style="text-align:right; border-top:1px solid;font-size:10px;border-left:1px solid; ">'.number_format($s_gst,2,".","").'</td>
			</tr>';
		}else{
			$html.='<tr height="20px">
			<td  style="border-right:1px solid;border-top:1px solid; font-size:10px;">';
			if(!empty($set_head['ifcs'])){ 
				$html.='<strong>IFSC:</strong>'.$set_head['ifcs'].',';
			}
			if(!empty($set_head['branch_name'])){
				$html.='<strong>Branch :</strong> '.$set_head['branch_name'];
			}
			$html.='</td>
			<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left" >IGST</td>';
			if($company_config['tax_editable'] == 0){ 
				$i_gst = $total_sundrytax+$total_i_gst;
			}else{
				$i_gst = $rel['igst'];
			}
			$merchantTax = ($totaltaxable*(0.1))/100;
			if($rel['sales_type'] == 2){ 
				$i_gst = $merchantTax;
			}

			$html.='<td style="text-align:right; border-top:1px solid;font-size:10px;border-left:1px solid; ">'.number_format($i_gst,2,".","").'</td>
			</tr>';
		} 

		$qry121="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
		from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
		left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
		where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' and b.sundry_ledger_id=".TCS." ";
		$result121=$dbcon->query($qry121);
		if(brp_mysqli_num_rows($result121)>0)
		{
			$row121 = brp_mysqli_fetch_assoc($result121);
			if($company_config['tax_editable'] == 0){ 
				$tcs_gst = $row121['sundry_amount'];
			}else{
				$tcs_gst = $rel['tcs'];
			}

			$html.='<tr height="20px">
			<td  style="border-right:1px solid;border-top:1px solid; font-size:10px;"></td>
			<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left" >TCS</td>
			<td style="text-align:right; border-top:1px solid;font-size:10px;border-left:1px solid; ">'.number_format($tcs_gst,2,".","").'</td>
			</tr>';
		}
		$qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_invoicetrn as trn 
		left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
		left join tbl_ledger as l on l.l_id=tc.tax_id 
		where tc.tax_additional='1' and trn.invoice_id=".$rel['invoice_id']." and trn.trancation_status!=2 and tc.isdelete='0' group by tc.tax_id";
		$result11=$dbcon->query($qry11);		
		while($row11=brp_mysqli_fetch_assoc($result11)){
			$html.='<!-- Added By Dhruv -->
			<tr height="20px">
			<td style="border-right:1px solid;border-top:1px solid; font-size:10px;"></td>
			<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left" >'.$row11['l_name'].'</td>
			<td style="text-align:right; border-top:1px solid;font-size:10px;border-left:1px solid; ">'.number_format($row11['add_sum'],2,".","").'</td>
			</tr>';
		} 
		$qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
		from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
		left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
		where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' and le.default_sundry='0' ";
		$result12=$dbcon->query($qry12);		
		while($row12=brp_mysqli_fetch_assoc($result12)){
			$html.='<!-- Added By Dhruv -->
			<tr height="20px">
			<td style="border-right:1px solid;border-top:1px solid; font-size:10px;"></td>
			<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left" >'.$row12['l_name'].'</td>
			<td style="text-align:right; border-top:1px solid;font-size:10px;border-left:1px solid; ">'.number_format($row12['sundry_amount'],2,".","").'</td>
			</tr>';
		}
		if($tax_name[1]) { 
			$html.='<tr height="20px">
			<td style="border-right:1px solid;border-top:1px solid; font-size:10px;"></td>
			<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left">Add : ';
			$strt=$tax_name[1];
			$position = strpos($strt, "TCS", 0);
			if ($position == true){ 
				$html.=$tax_name[1];
			} else{
				$html.='SGST';	
			}
			$html.='</td>
			<td style="text-align:right; border-top:1px solid;font-size:10px;border-left:1px solid; ">'.number_format($totaltax2,2,".","").'</td>
			</tr>';
		} 
		$totaltax=$totaltax1+$totaltax2;
		$total=($total)+$rel['packing']; 
		$r=round($total)-$total;  
		$round = round($rel['g_total']) - $rel['g_total'];
		$html.='<tr height="20px">
		<td style="border-right:1px solid;border-top:1px solid;font-size:12px;"><strong>COMPANY GSTIN : '.$set_head['vatno'].' </strong><br></td>
		<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left">Round Off</td>
		<td style="text-align:right; border-top:1px solid;font-size:10px;border-left:1px solid; ">'. $rel['round_off'].'</td>
		</tr>
		<tr height="20px">
		<td style="border-right:1px solid;border-top:1px solid;"><strong>Rupees:</strong>'.ucwords(convert_number_to_words_new(round($rel['g_total']))).'</td>
		<td style="border-top:1px solid;border-right:1px solid;text-align:left"><strong>Grand Total</strong> :</td>
		<td style="text-align:right; border-top:1px solid;border-left:1px solid; "><strong>'.number_format($rel['g_total'],0,".","").'.00'.'</strong></td>
		</tr>
		<tr height="35px">
		<td colspan="3" style="border:1px solid;border-left:none;border-right:none;">Remark:'.(($rel['remark']) ? $rel['remark'] : '').'</td>
		</tr>
		</table>';
		if($company_config['tax_editable'] == 0){
			if($rel['sales_type']==1){
				if($rel['stateid']==$set_head['stateid']){
					$html.='<table border="0" style="font-size:10px;" width="100%">
					<tr> 
					<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;" ><strong>HSN Code</strong></td>
					<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;" ><strong>Taxable Amt.</strong></td>
					<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;" ><strong>CGST Rate</strong></td>
					<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;" ><strong>CGST Amt.</strong></td>
					<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;" ><strong>SGST Rate</strong></td>
					<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;" ><strong>SGST Amt.</strong></td>
					<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right: none;"><strong>Total Tax Amount<strong></td>
					</tr>';
				}else if($rel['stateid']!=$set_head['stateid']){
					$html.='<table border="0" style="font-size:10px;text-align:right;" width="100%">
					<tr> 
					<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;" ><strong>HSN Code</strong></td>
					<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;" ><strong>Taxable Amt.</strong></td>
					<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;" >
					<strong>IGST Rate</strong>
					</td>
					<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;" >
					<strong>IGST Amt.</strong>
					</td>
					<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:none;"><strong>Total Tax Amount<strong></td>
					</tr>';
				}

				$query="select sum(product_amount) as product_amount,trn.product_hsn_code,cgst_tax_per,sum(cgst_tax_rate) as cgst_tax_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_tax_rate,igst_tax_per,sum(igst_tax_rate) as igst_tax_rate FROM `tbl_invoicetrn` as trn where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trn.product_hsn_code";
				$rs_tax=$dbcon->query($query);
				while($rel_tax=brp_mysqli_fetch_assoc($rs_tax))
				{	
					$total1+=$row_total=$rel_tax['cgst_tax_rate']+$rel_tax['sgst_tax_rate']+$rel_tax['igst_tax_rate'];
					$html.='<tr> 
					<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
					'.$rel_tax['product_hsn_code'].'
					</td>
					<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
					'.$rel_tax['product_amount'].'
					</td>';
					if($rel['stateid']==$set_head['stateid']){
						$html.='<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
						'.str_replace("CGST","",$rel_tax['cgst_tax_per']).'
						</td>
						<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
						'.$rel_tax['cgst_tax_rate'].'
						</td>
						<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
						'.str_replace("SGST","",$rel_tax['sgst_tax_per']).'
						</td>
						<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
						'.$rel_tax['sgst_tax_rate'].'
						</td>';
					}else if($rel['stateid']!=$set_head['stateid']) {
						$html.='<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
						'.str_replace("IGST","",$rel_tax['igst_tax_per']).'
						</td>
						<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
						'.$rel_tax['igst_tax_rate'].'
						</td>';
					}
					$html.='<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right: none;" >
					'.number_format($row_total,2).'
					</td>';

					$html.='</tr>';
					$totalamt+=$rel_tax['product_amount'];
					$totaltaxamt1+=$rel_tax['cgst_tax_rate'];
					$totaltaxamt2+=$rel_tax['sgst_tax_rate'];
					$totaltaxamt3+=$rel_tax['igst_tax_rate'];
				}
				$sundrytax1=$dbcon->query("select b.*,tl.ledger_hsn from tbl_bill_sundry_transaction as b
					left join tbl_ledger as tl on b.sundry_ledger_id=tl.l_id
					where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' ");
				while($sundry_tax=brp_mysqli_fetch_assoc($sundrytax1))
				{
					if($sundry_tax['sundry_gst_amount'] != 0){
						$total_sun1+=$sundry_tax['sundry_gst_amount'];
						$html.='<tr> 
						<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
						'.$sundry_tax['ledger_hsn'].'
						</td>
						<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
						'.$sundry_tax['sundry_amount'].'
						</td>';
						if($rel['stateid']==$set_head['stateid'])
						{
							$sun_gst_per = $sundry_tax['sundry_gst_per']/2;
							$sun_gst_amt = $sundry_tax['sundry_gst_amount']/2;
							$html.='<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
							'.$sun_gst_per.'
							</td>
							<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
							'.$sun_gst_amt.'
							</td>
							<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
							'.$sun_gst_per.'
							</td>
							<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
							'.$sun_gst_amt.'
							</td>';
						}
						else if($rel['stateid']!=$set_head['stateid'])
						{
							$html.='<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
							'.$sundry_tax['sundry_gst_per'].'
							</td>
							<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
							'.$sundry_tax['sundry_gst_amount'].'
							</td>';
						}
						$html.='<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right: none;" >
						'.$sundry_tax['sundry_gst_amount'].'
						</td>';

						$html.='</tr>';
						$total_sunamt+=$sundry_tax['sundry_amount'];
						$total_suntaxamt1+=$sundry_tax['sundry_gst_amount']/2;
						$total_suntaxamt2+=$sundry_tax['sundry_gst_amount'];

					}
				}
				$html.='<tr> 
				<td style="text-align: center;"><strong>Total</strong></td>
				<td style="vertical-align:top;text-align:center;border-top:1px solid;border-right:1px solid;" >
				'.number_format($totalamt+$total_sunamt,2).'
				</td>
				';
				if($rel['stateid']==$set_head['stateid']){
					$html.='<td style="vertical-align:top;text-align:center;border-top:1px solid;border-right:1px solid;" >

					</td>
					<td style="vertical-align:top;text-align:center;border-top:1px solid;border-right:1px solid;" >
					'.number_format($totaltaxamt1+$total_suntaxamt1,2).'
					</td>

					<td style="vertical-align:top;text-align:center;border-top:1px solid;border-right:1px solid;" >

					</td>
					<td style="vertical-align:top;text-align:center;border-top:1px solid;border-left:1px solid;border-right:1px solid;" >
					'.number_format($totaltaxamt2+$total_suntaxamt1,2).'
					</td>';
				}else if($rel['stateid']!=$set_head['stateid']){
					$html.='<td style="vertical-align:top;text-align:center;border-top:1px solid;border-right:1px solid;" >
					</td>
					<td style="vertical-align:top;text-align:center;border-top:1px solid;border-left:1px solid;border-right:1px solid;" >
					'.number_format($totaltaxamt3+$total_suntaxamt2,2).'
					</td>';
				}
				$html.='<td style="vertical-align:top;text-align:center;border-top:1px solid;">'.number_format($total1+$total_sun1,2).'</td>
				</tr>
				</table>';
			}
		}
		$html.='<table >
		<tr>
		<td width="60%" style="vertical-align:top;border:1px solid;
		border-right:none;border-left:none;border-bottom:none;font-size:10px;text-align:left  !important">';
		if(!empty($set_head['conditions'])){
			$html.='<strong>Terms and Conditions:</strong><br> '.$set_head['conditions'];
		}
		$html.='</td>
		<td width="40%" style=" border-left:none;vertical-align:top;border-top:1px solid black; text-align: center;">
		For, <strong> <span style="font-size:10px;text-decoration:bold;">
		'.$set_head['company_name'].'</span></strong>
		<br>';
		if($set_head['authorized_signature']!=""){
			$html.='<img src="'.DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'].'" style="height: 100px; width: 100px;"><br>';
		}else{
			$html.='<br><br><br>';
		}
		$html.='<span style="vertical-align:bottom;">Authorised Signatory</span>
		</td>
		</tr>
		</table>';
		/* Get Terms And Condition Start */

		$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
		</body>
		</html>';
		// echo $html;
		// $get = "hello";
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','28','15','1','1');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		$mpdf->pagenumPrefix = 'Page ';
		$mpdf->pagenumSuffix = ' of ';
		$mpdf->nbpgPrefix = ' ';
		$mpdf->nbpgSuffix = '';
		$mpdf->SetFooter('{PAGENO}{nbpg}');
		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='UTF-8';
		$mpdf->WriteHTML($html);
		$mpdf->Output();
				//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
		ob_clean();
		return 'Invoice Receipt '.$invoiceid.'.pdf';
	}

?>