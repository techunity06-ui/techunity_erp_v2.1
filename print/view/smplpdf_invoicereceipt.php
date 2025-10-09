<?php session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
$type='pdf';
$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
if(strtolower($type) == 'pdf') {
	$query="SELECT invoice.*, country.country_name, state.state_name, cust.stateid, state.gst_state_code, city.city_name, cust.l_name, cust.m_address, type.invoice_type, cust_pincode, cust_mobile, gst_no, dispatch.mode_dispatch, cust.m_pan, cust.enable_sez, pay.payment_terms as terms,tans.transportation_name,ttr.transport_doc_no from tbl_invoice as invoice 
	left join tbl_ledger as cust on cust.l_id=invoice.cust_id
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid
	left join city_mst as city on city.cityid=cust.cityid
	left join pay_terms as pay on pay.terms_id=invoice.payment_terms
	left join mode_of_dispatch as dispatch on dispatch.mode_dis_id=invoice.dispatch_doc_no
	left join tbl_invoicetype as type on type.invoicetype_id=invoice.invoicetype_id
	left join tbl_transport_transaction as ttr on ttr.transport_transaction_table_id= invoice.invoice_id
	left join tbl_currency as cur on cur.currency_id = invoice.currency_id
	left join transportation_details as tans on tans.id = ttr.transport_id
	where invoice.invoice_id=$invoiceid";
	$rel=brp_mysqli_fetch_array($dbcon->query($query));
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
		$cons_data=brp_mysqli_fetch_array($dbcon->query($consignee));	
		$cons_gst_no=$cons_data['gst_no'];
		$cons_pan_no=$cons_data['pan_no'];
		$cons_state_name=$cons_data['state_name'];
		$cons_gst_state_code=$cons_data['gst_state_code'];
		$place_of_supply=$cons_data['city_name'];
	}

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$set_head=brp_mysqli_fetch_array($dbcon->query($set));	
	$invoice_date='';$lr_date='';$dispatch_date='';
	if($rel['invoice_date']!="1970-01-01" && $rel['invoice_date']!="0000-00-00")
	{
		$invoice_date=date('d-m-Y',strtotime($rel['invoice_date']));
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
	while($sumsundrytax=brp_mysqli_fetch_array($sundrytax)){
		$total_sundrytax = $total_sundrytax + $sumsundrytax['sundry_gst_amount'];
	}
	$sel_t = $dbcon->query("select trn.*,t.id,t.transportation_name from tbl_transport_transaction as trn 
		left join transportation_details as t on t.id=trn.transport_id
		where transport_transaction_table_id='$invoiceid'");
	$r_t=brp_mysqli_fetch_array($sel_t);

	$qry_disc=$dbcon->query("SELECT SUM(trn.product_discount) as discount FROM `tbl_invoicetrn` as trn where trancation_status=0 and invoice_id=".$rel['invoice_id']);
	$rel_disc = brp_mysqli_fetch_array($qry_disc);

	/* Check Discount is On or off Start */
	if($rel_disc['discount'] > 0){
		$colspan=5;
		$dynamicwidth=35;
	}else{
		$colspan=6;
		$dynamicwidth=41;
	}
	// $logwi = 40; $textwi = 60;
	if($company_config['finance_print_letterhead_per']==0){
		/*$header .= get_header($dbcon,'text-align: center','100px','100px');*/
		$logo = '<td rowspan="3" style="border: 1px solid;" width="20%">
				<img src="' . DOMAIN_F . LOGO . $set_head["logo"] . '" style="width:120px; height: 90px;" />
			</td>';
    	$text = '<td style="border-bottom: none !important;border-right: 1px solid !important; border-top: 1px solid !important;text-align: center;text-transform: capitalize;padding:0px" width="70%">
    			<h2 style="padding:0px"><b>' . $set_head["company_name"] . ' </b></h2>
    		</td>';
		$header .= '<table style="">
				<tr style="">
					' . $logo . '' . $text . '
				</tr>
				<tr style="">
					<td style="border-top: none !important;border-right: 1px solid !important; border-bottom: 1px solid !important;  text-align: left;text-transform: capitalize;">
					' . $set_head["address"] . '<br>
					Contact no. ' . $set_head['contact_no'] . ', Email: ' . strtolower($set_head['website']) . ' <br>
					Website : '.$set_head['company_website'].'
					<hr>
					<strong> Factory Add : </strong> Plot No.10, Phase-1, G.I.D.C.,, B/h Prashant Eng, Vatva, Ahmedabad (Gujarat), 382445, CIN : '.$set_head['cin'].'
					</td>	
				</tr>
		</table>';
	}else{
		$header ='';
		$footer = '';
	}
	/*echo $header;exit;*/
	$header .='<table style="width:100%;border: none;padding:0px">
		<tr style="border: none;">
			<td style="border: none;text-align: left;width:33:33%" ><strong>DEBIT</strong></td>
			<td style="border: none;text-align: center;width:33:33%"> <strong style="font-size:14px;">'.(($rel["invoice_type"]) ? $rel["invoice_type"] : 'TAX INVOICE').'</strong></td>
			<td style="border: none;text-align: right;width:33:33%"> 
				<strong>(DUPLICATE)</strong>
			</td>
		</tr>
		</table>

		<!--<table style="width:100% !important;font-size:10px;border-collapse: collapse;border-top:none !important;padding:0px" cellpadding="0" cellspacing="0" class="maintable">';
		if($rel['enable_sez'] == 1 || $rel['sales_type'] == 2){ 
			$header.='<tr>																		
			<td colspan="6" style="padding: 10px;"><span style="font-weight:normal;padding:0px">( SUPPLY MEANT FOR EXPORT/SUPPLY TO SEZ UNIT OR SEZ DEVELOPER FOR AUTHORIZED OPERATIONS UNDER BOND OR LETTER OF UNDERTAKING WITHOUT PAYMENT OF IGST ) </span> </td>
			</tr>';
		}
		if(!empty($rel['einv_Irn'])){
			$header.='<tr>
			<td colspan="4" class="" style="vertical-align:top;border:1px solid; border-right:none !important;text-align: left;" colspan="4">
			<strong>IRN No : </strong>'.$rel['einv_Irn'].' <br>
			<strong>ACK No : </strong>'.$rel['einv_AckNo'].' <br>
			<strong>ACK Date : </strong>'.date('d-m-Y',strtotime($rel['einv_AckDate'])).'
			</td>
			<td colspan="2" style="vertical-align:top;border-bottom:1px solid; border-right:1px solid;border-top:1px solid;text-align: right;">
			<img style="height: 75px;width: 75px;"  src="data:image/png;base64,'.$rel['einv_SignedQRCode'] .'" />
			</td>
			</tr>';
		}
		$header.='</table>-->
		<table style="width:100% !important;font-size:10px;border-collapse: collapse;border-top:none !important;padding:0px" cellpadding="0" cellspacing="0" class="maintable">
		<tr>
		<td  rowspan="6" style="width:50% !important;vertical-align:top;border-right:1px solid;border-left:1px solid;border-bottom: 1px solid !important;border-top: 1px solid !important;"><b>To,  </b><br/><strong>Name : '.$rel['l_name'].'</strong><br><span style="font-weight:normal;">Address : '.nl2br($rel['m_address']).'<br/>'.$rel['city_name'].', '.$rel['state_name'].', '.$rel['country_name'];
		if(!empty($rel['cust_pincode'])){	
			$header.='-  '.$rel['cust_pincode'];
		}
		$header.='</span><br>';
		if($company_config['enable_hypothication']==1 && $rel['check_hypothication']!=0){
			$header.='<strong>
			Hypothecation with '.get_id_detail($dbcon,'bank_mst','bankid',$rel['hypo_bank'],'bank_name').'
			</strong><br>';
		} 
		$header.='Mobile no : '.$rel['cust_mobile'].'<br>
		Party GST No. : '.$rel['gst_no'].'<br>
		DL No.-20B : <br>
		DL No.-21B : <br>
		</td>

		<td  style="vertical-align:top;border-top:1px solid;border-bottom:1px solid;border-left:1px solid;padding:0px"> &nbsp;<strong>Invoice No : '.$rel['invoice_no'].'</strong></td>
		<td  style="vertical-align:top;border-right:1px solid;border-top:1px solid;border-bottom:1px solid;padding:0px;text-align:right">Date : '.$invoice_date.'</td>
		</tr>

		<tr style="border-bottom:none">
			<td colspan="2" style="width:50% !important;border-bottom:none;border-right:1px solid;padding:0px"> &nbsp;Sent By : '.$rel['transportation_name'].'</td>
		</tr>
		<tr style="border-bottom:none;border-top:none">
			<td colspan="2" style="border-bottom:none;border-right:1px solid;border-top:none;padding:0px"> &nbsp;Docket No : '.$rel['transport_doc_no'].'</td>
		</tr>
		<tr style="border-bottom:none;border-top:none">
			<td colspan="2" style="border-bottom:none;border-top:none;border-right:1px solid;padding:0px"> &nbsp;Delivery Challan No : '.$rel['challan_no'].'</td>
		</tr>
		<tr style="border-bottom:none;border-top:none">
			<td colspan="2" style="border-top:none;border-right:1px solid;padding:0px"> &nbsp;Party Order No : '.$rel['order_no'].'</td>
		</tr>
		<tr style="border-top:none">
			<td colspan="2" style="border-top:none;border-right:1px solid;border-bottom:1px solid;padding:0px"> &nbsp;Party Order Dt. : '.date('d-m-Y',strtotime($rel['order_date'])).'</td>
		</tr>';		
		
		$header.='</table>';
		/*echo $header;
		exit;*/

		$body.='<table style="border-collapse: collapse;border-top:none !important;padding:0px;font-size:12px" cellpadding="0" cellspacing="0" width="100%">
		<thead>
		<tr>
		<th width="3%" style="border:1px solid;border-top: none;border-top:1px solid; padding:0px">SR.</th>
		<th width="8%" style="border:1px solid;border-top: none;border-top:1px solid; padding:0px">Code No.</th>
		<th width="'.$dynamicwidth.'%" style="border:1px solid;border-top: none;border-top:1px solid; padding:0px" ><strong>Particulars </strong></th>
		<th width="10%" style="border:1px solid;border-top: none;border-top:1px solid; white-space:nowrap;padding:0px">HSN Code</th>
		<th width="10%" style="border:1px solid;border-top: none;border-top:1px solid; padding:0px">Lot No</th>
		<th width="4%" style="border:1px solid;border-top: none;border-top:1px solid; padding:0px">Qty.</th>
		<th width="7%" style="border:1px solid;border-top: none;border-top:1px solid; padding:0px">Rate</th>';
		if($rel_disc['discount'] > 0){
			$body.='<th width="6%" style="border:1px solid;border-top: none;border-top:1px solid; padding:0px"><strong>Less:<br/>Disc.</strong></th>';
		}
		
		if($company_config['tax_editable'] == 0 && $rel['sales_type'] == 1 ){
			$body.='<th width="4%" style="border:1px solid;border-top: none;border-top:1px solid; padding:0px">GST</th> 
			<!--<th width="6%" style="border:1px solid;border-top: none;"><strong>GST Amount</strong></th>-->
			<th width="10%" style="border:1px solid;border-top: none;border-top:1px solid; padding:0px"><strong>Amount</strong></th>';
		}
		$body.='</tr>
		</thead>
		<tbody style="border: none;">';
		$qry="select trn.*,product.product_name,product.product_icode,per.unit_name,bst.qty as batch_qty, sto.batch_no, bunit.unit_name as batch_unit  FROM `tbl_invoicetrn` as trn 
		left join product_mst as product on product.product_id=trn.product_id 
		left join unit_mst as per on per.unitid=trn.unit_id 
		left join tbl_batch_stock_tmp as bst on bst.invoice_trn_id = trn.trancation_id and bst.status=1 and bst.product_id = trn.product_id
		left join unit_mst as bunit on bunit.unitid = bst.unitid
		left join tbl_stock_trn as sto on sto.stock_id = bst.stock_id
		where trancation_status=0 and invoice_id=".$rel['invoice_id']."  group by trancation_id order by product.product_type,trancation_id";
		
		$result=$dbcon->query($qry);		
		$pro_du=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;
		$cnt=brp_mysqli_num_rows($result);
		while($row=brp_mysqli_fetch_array($result))
		{
			$gst_per = $row['cgst_tax_per']+$row['sgst_tax_per']+$row['igst_tax_per'];
			$gst_rate = $row['cgst_tax_rate']+$row['sgst_tax_rate']+$row['igst_tax_rate'];

			if($row['cgst_tax_rate'] != 0 || $row['sgst_tax_rate'] !=0){
				$total_cs_gst += $gst_rate;
			}else{
				$total_i_gst += $gst_rate;
			}
			$body.='<tr style="height:25px;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;padding:0px">
			<td class="borderleftadd" style=" vertical-align:top;border-right:1px solid;border-left:1px solid;padding:0px;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;">'.$pro_du.'</td>
			<td width="8%" style="border:1px solid;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0; padding:0px">'.$row['product_icode'].'</td>
			<td style="border-right:1px solid;vertical-align:top;padding:0px">'.$row['product_name'].'';
			if($company_config['enable_item_description']==1){
				$body.='<br>'.(($row['description']) ? stripslashes($row['description']) : '').'<br>';
			}
			/*$batch_detail = "select bst.*,st.batch_no from tbl_batch_stock_tmp as bst
			left join `tbl_stock_trn` as st on st.stock_id=bst.stock_id where invoice_trn_id = ".$row['trancation_id']." and status =1";
			$brtch_q = $dbcon->query($batch_detail);
			while($r = brp_mysqli_fetch_array($brtch_q)){
				$body.='<span><strong>Batch : </strong>'.$r['batch_no'].'</span>	
				<span><strong>Qty : </strong>'.$r['qty'].'</span><br>';
			}*/
			$body.='</td>
			<td style=" border-right:1px solid;vertical-align:top;padding:0px" >'.$row['product_hsn_code'].'</td>
			<td style=" border-right:1px solid;vertical-align:top;padding:0px" >'.$row['batch_no'].'</td>
			<td style="vertical-align:top; border-right:1px solid;white-space:nowrap;padding:0px" >';
			
			if($row['batch_qty']!=""){
				$body.=$row['batch_qty'];
				$row['product_qty'] = $row['batch_qty'];
			}else{
				if($row['product_type']!='3'){
					$body.=$row['product_qty'];
				}else{
					$charges_qty+=$row['product_qty'];
					$body.=$charges_qty;
				}	 
			}
			$pro_amt = $row['product_qty'] * $row['product_rate'];
			$body.='</td>
			<td style="vertical-align:top; border-right:1px solid; text-align: center;padding:0px" >'.number_format($row['product_rate'],2,".","").'</td>';
			if($rel_disc['discount'] > 0){
				$body.='<td style=" vertical-align:top;border-right:1px solid; text-align: center;">'.number_format($row['discount_per'],2,".","").'%'.'</td>';
			}
			if($company_config['tax_editable'] == 0 && $rel['sales_type'] == 1 ){
				$body.='<td style=" vertical-align:top;border-right:1px solid; text-align: center;padding:0px">'.$gst_per.'%</td>
				<!--<td style="vertical-align:top;border-right:1px solid; text-align: center;">'.number_format($gst_rate,2,".","").'</td>
				<td style="vertical-align:top;border-right:1px solid; text-align: center;padding:0px">'.number_format($row['total'],2,".","").'</td>-->';
				$body.='<td style=" vertical-align:top;border-right:1px solid; text-align: center;padding:0px">'.number_format(($pro_amt),2,".","").'</td>';
			}
			$body.='</tr>';
			$pro_du++; 
			$totalqty=$totalqty+$row['product_qty']-$charges_qty;
			$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
			$total_product_amount+=($row['product_qty']*$row['product_rate']);
			$totaltaxable+=$row['product_amount'];
			$totaltax1+=$row['tax_amount1'];
			$totaltax2+=$row['tax_amount2'];
			$total+=$row['total'];
			$total_gst_rate +=$gst_rate;
		}
		if($cnt>35){
			$cal 	= $cnt/35;
			$cal1 	= $cnt*floor($cal);
			$cont1  = $cnt - $cal1;
			$cont  	= 32-$cont1;
			for($i=1;$i<=$cont;$i++){
				if($i == $cont){
					$body .= '<tr style="height:10px;border-top:1px solid ;border-bottom:1px solid ">
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid;border-left:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid;border-left:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid"></td>
					</tr>';
				}else{
					$body .= '<tr style="height:10px;">
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid;border-left:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid;border-left:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid"></td>
					</tr>';
				}
			} 	
		}else{
			$cont  = 32-$cnt;
			for($i=1;$i<=$cont;$i++){
				if($i == $cont){
					$body .= '<tr style="height:10px;border-top:1px solid ;border-bottom:1px solid ">
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid;border-left:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid;border-left:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid ;border-bottom:1px solid ;border-right:1px solid"></td>
					</tr>';
				}else{
					$body .= '<tr style="height:10px;">
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid;border-left:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid;border-left:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid"></td>
					<td class="borderleftadd" style="height:10px; vertical-align:top;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;border-right:1px solid"></td>
					</tr>';
				}
			}	
		}
		
		
		$body.='<tr style="height:20px;">
		<td style="border-top:1px solid;border-bottom:1px solid;border-right:1px solid;border-left:1px solid;text-align:center;" colspan="5"><strong></strong></td>
		<td style="text-align:center;border-top:1px solid;border-bottom:1px solid;border-right:1px solid;"><strong>'.number_format($totalqty,2,".","").'</strong></td>';
		if($rel_disc['discount'] > 0){
			$body.='<td style="border-top:1px solid;border-bottom:1px solid;border-right:1px solid;"></td>';
		}
		$body.='<td style="border-top:1px solid;border-bottom:1px solid;border-right:1px solid;"></td>';
		$body.='<td style="border-top:1px solid;border-bottom:1px solid;border-right:1px solid;"></td>';
		if($company_config['tax_editable'] == 0 && $rel['sales_type'] == 1 ){
			$body.='<!--<td style="border-top:1px solid;border-right:1px solid;text-align:center;"></td>
			<td style="border-top:1px solid;border-bottom:1px solid;border-right:1px solid;text-align:center;"><strong>'.number_format($total_gst_rate,2,".","").'</strong></td>
			<td style="border-top:1px solid;border-bottom:1px solid;border-right:1px solid;text-align:right;"><strong>'.number_format($total,2,".","").'</strong></td>-->
			<td style="border-top:1px solid;border-bottom:1px solid;border-right:1px solid;text-align:center;"><strong>'.number_format($totaltaxable,2,".","").'</strong></td>';
		}
		$body.='</tr>
		</table>';

		$pro_du = $pro_du-1;
		$footer.='';
		if($pro_du == $cnt){
			$rowsp = 0;
			if($rel['stateid']==$set_head['stateid'] && ($custLedgerDetails['enable_sez'] == 0)){
			    $rowsp = 2+2;
			}else{
			    $rowsp = 2+1;
			}
			
			$qry121r="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
					from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
					left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
					where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' and b.sundry_ledger_id=".TCS." ";
			$result121r=$dbcon->query($qry121r);
		    if(brp_mysqli_num_rows($result121r)>0){
		        $rowsp = $rowsp+1;
		    }	
			
			$qry11r="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_invoicetrn as trn 
						left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
						left join tbl_ledger as l on l.l_id=tc.tax_id 
						where tc.tax_additional='1' and trn.invoice_id=".$rel['invoice_id']." and trn.trancation_status!=2 and tc.isdelete='0' group by tc.tax_id";
			$result11r=$dbcon->query($qry11r);
			$dsf11r = brp_mysqli_num_rows($result11r);
			$rowsp = $rowsp+$dsf11r;
			
			$qry12r="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
						from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
						left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
						where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' and le.default_sundry='0' ";
			$result12r=$dbcon->query($qry12r);
			$ccrr = brp_mysqli_num_rows($result12r);
			$rowsp = $rowsp+$ccrr;
			
			$footer.='<table width="100%;vertical-align:top;" style="border: none;">
			<tr>
				<td rowspan="'.$rowsp.'" style="width:25%;border:1px solid;vertical-align:top;padding:5px"> Bank Details : <br>
					Bank A/c No : '.$set_head['ac_no'].'<br>
					Bank Name   : '.$set_head['bank_name'].'<br>
					IFSC Code   : '.$set_head['ifcs'].'<br>
					Branch Name : '.$set_head['branch_name'].'<br>
					Beneficiary Name : '.$set_head['company_name'].'</td>
				<td rowspan="'.$rowsp.'" style="width:45%;border:1px solid;">';
				if(!empty($rel['einv_Irn'])){
					$footer.='
					<img style="height: 75px;width: 75px;"  src="data:image/png;base64,'.$rel['einv_SignedQRCode'] .'" />
					<strong>IRN No : </strong>'.$rel['einv_Irn'].' <br>
					<strong>ACK No : </strong>'.$rel['einv_AckNo'].' <br>
					<strong>ACK Date : </strong>'.date('d-m-Y',strtotime($rel['einv_AckDate']));
				}
			$footer .='</td>
				<td style="padding-top:0px;padding-bottom:0px;border:1px solid;"><strong>Sub Total</strong></td>
				<td style="text-align:right;padding-top:0px;padding-bottom:0px;border:1px solid;">'.number_format($totaltaxable,2,".","").'</td>
			</tr>';
						

						$qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_invoicetrn as trn 
						left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
						left join tbl_ledger as l on l.l_id=tc.tax_id 
						where tc.tax_additional='1' and trn.invoice_id=".$rel['invoice_id']." and trn.trancation_status!=2 and tc.isdelete='0' group by tc.tax_id";
						$result11=$dbcon->query($qry11);		
						while($row11=brp_mysqli_fetch_array($result11)){
							$footer.='<tr>
								<td style="padding-top:0px;padding-bottom:0px;border:1px solid;"><strong>'.$row11['l_name'].'</strong></td>
								<td style="text-align:right;padding-top:0px;padding-bottom:0px;border:1px solid;">'.number_format($row11['add_sum'],2,".","").'</td>
							</tr>';
						}

						$qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
						from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
						left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
						where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' and le.default_sundry='0' ";
						$result12=$dbcon->query($qry12);		
						while($row12=brp_mysqli_fetch_array($result12)){
							$footer.='<tr>
								<td style="padding-top:0px;padding-bottom:0px;border:1px solid;"><strong>'.$row12['l_name'].'</strong></td>
								<td style="text-align:right;padding-top:0px;padding-bottom:0px;border:1px solid;">'.number_format($row12['sundry_amount'],2,".","").'</td>
							</tr>';
						}

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

							$footer.='<tr>
								<td style="padding-top:0px;padding-bottom:0px;border:1px solid;"><strong>CGST</strong></td>
								<td style="text-align:right;padding-top:0px;padding-bottom:0px;border:1px solid;">'.number_format($c_gst,2,".","").'</td>
							</tr>
							<tr>
								<td style="padding-top:0px;padding-bottom:0px;border:1px solid;"><strong>SGST</strong></td>
								<td style="text-align:right;padding-top:0px;padding-bottom:0px;border:1px solid;">'.number_format($s_gst,2,".","").'</td>
							</tr>';
					}else{
						if($company_config['tax_editable'] == 0){ 
							$i_gst = $total_sundrytax+$total_i_gst;
						}else{
							$i_gst = $rel['igst'];
						}
						$merchantTax = ($totaltaxable*(0.1))/100;
						if($rel['sales_type'] == 2){ 
							$i_gst = $merchantTax;
						}

						$footer.='<tr>
							<td style="padding-top:0px;padding-bottom:0px;border:1px solid;"><strong>IGST</strong></td>
							<td style="text-align:right;padding-top:0px;padding-bottom:0px;border:1px solid;">'.number_format($i_gst,2,".","").'</td>
						</tr>';
					}
					$qry121="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
					from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
					left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
					where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' and b.sundry_ledger_id=".TCS." ";
					$result121=$dbcon->query($qry121);
					if(brp_mysqli_num_rows($result121)>0)
					{
						$row121 = brp_mysqli_fetch_array($result121);
						if($company_config['tax_editable'] == 0){ 
							$tcs_gst = $row121['sundry_amount'];
						}else{
							$tcs_gst = $rel['tcs'];
						}
						$footer.='<tr>
							<td style="padding-top:0px;padding-bottom:0px;border:1px solid;"><strong>TCS</strong></td>
							<td style="text-align:right;padding-top:0px;padding-bottom:0px;border:1px solid;">'.number_format($tcs_gst,2,".","").'</td>
						</tr>';
					}
						$totaltax=$totaltax1+$totaltax2;
						$total=($total)+$rel['packing']; 
						$r=round($total)-$total;  
						$round = round($rel['g_total']) - $rel['g_total'];

						/*$footer.='<tr>
							<td style="padding-top:0px;padding-bottom:0px;border:1px solid;"><strong>Total Tax Amount</strong></td>
							<td style="text-align:right;padding-top:0px;padding-bottom:0px;border:1px solid;">'.number_format($total_sundrytax+$total_i_gst+$total_cs_gst,2,".","").'</td>
						</tr>';*/
						$footer.='<tr>
							<td style="padding-top:0px;padding-bottom:0px;border:1px solid;"><strong>Round Off</strong></td>
							<td style="text-align:right;padding-top:0px;padding-bottom:0px;border:1px solid;">'. $rel['round_off'].'</td>
						</tr>';
						
					$footer.='
			</tr>
			<tr>
				<td colspan="2" style="border:1px solid;padding-top:0px; padding-bottom:0px;"><strong>Rs.</strong> '.	
convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$currency_code['currency_in_word_end'],$currency_code['currency_in_word']).'</td>
				
				<td style="padding-top:0px;padding-bottom:0px;border:1px solid;"><strong>Grand Total </strong></td>
				<td style="text-align:right;padding-top:0px;padding-bottom:0px;border:1px solid;">'.number_format($rel['g_total'],0,".","").'.00'.'</td>
			</tr>
			</table>';
			if($company_config['tax_editable'] == 0){
				if($rel['sales_type']==1){
					if($rel['stateid']==$set_head['stateid']){
						$footer.='<table border="0" style="font-size:10px;" width="100%">
						<tr> 
						<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;padding:0px;border-left:1px solid" ><strong>HSN Code</strong></td>
						<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;padding:0px" ><strong>Taxable Amt.</strong></td>
						<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;padding:0px" ><strong>CGST Rate</strong></td>
						<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;padding:0px" ><strong>CGST Amt.</strong></td>
						<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;padding:0px" ><strong>SGST Rate</strong></td>
						<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;padding:0px" ><strong>SGST Amt.</strong></td>
						<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right: none;padding:0px;border-right:1px solid"><strong>Total Tax Amount<strong></td>
						</tr>';
					}else if($rel['stateid']!=$set_head['stateid']){
						$footer.='<table border="0" style="font-size:10px;text-align:right;" width="100%">
						<tr> 
						<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;padding:0px;border-left:1px solid" ><strong>HSN Code</strong></td>
						<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;padding:0px" ><strong>Taxable Amt.</strong></td>
						<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;padding:0px" >
						<strong>IGST Rate</strong>
						</td>
						<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:1px solid;padding:0px" >
						<strong>IGST Amt.</strong>
						</td>
						<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right:none;padding:0px;border-right:1px solid"><strong>Total Tax Amount<strong></td>
						</tr>';
					}

					$query="select sum(product_amount) as product_amount,trn.product_hsn_code, cgst_tax_per,sum(cgst_tax_rate) as cgst_tax_rate, sgst_tax_per,sum(sgst_tax_rate) as sgst_tax_rate, igst_tax_per,sum(igst_tax_rate) as igst_tax_rate FROM `tbl_invoicetrn` as trn where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trn.product_hsn_code";
					$rs_tax=$dbcon->query($query);
					while($rel_tax=brp_mysqli_fetch_array($rs_tax))
					{	
						$total1+=$row_total=$rel_tax['cgst_tax_rate']+$rel_tax['sgst_tax_rate']+$rel_tax['igst_tax_rate'];
						$footer.='<tr> 
						<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;padding:0px;border-left:1px solid" >
						'.$rel_tax['product_hsn_code'].'
						</td>
						<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;padding:0px" >
						'.$rel_tax['product_amount'].'
						</td>';
						if($rel['stateid']==$set_head['stateid']){
							$footer.='<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;padding:0px" >
							'.str_replace("CGST","",$rel_tax['cgst_tax_per']).'
							</td>
							<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;padding:0px" >
							'.$rel_tax['cgst_tax_rate'].'
							</td>
							<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;padding:0px" >
							'.str_replace("SGST","",$rel_tax['sgst_tax_per']).'
							</td>
							<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;padding:0px" >
							'.$rel_tax['sgst_tax_rate'].'
							</td>';
						}else if($rel['stateid']!=$set_head['stateid']) {
							$footer.='<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;padding:0px" >
							'.str_replace("IGST","",$rel_tax['igst_tax_per']).'
							</td>
							<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;padding:0px" >
							'.$rel_tax['igst_tax_rate'].'
							</td>';
						}
						$footer.='<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right: none;padding:0px;border-right:1px solid" >
						'.number_format($row_total,2).'
						</td>';

						$footer.='</tr>';
						$totalamt+=$rel_tax['product_amount'];
						$totaltaxamt1+=$rel_tax['cgst_tax_rate'];
						$totaltaxamt2+=$rel_tax['sgst_tax_rate'];
						$totaltaxamt3+=$rel_tax['igst_tax_rate'];
					}
					$sundrytax1=$dbcon->query("select b.*,tl.ledger_hsn from tbl_bill_sundry_transaction as b
						left join tbl_ledger as tl on b.sundry_ledger_id=tl.l_id
						where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' ");
					while($sundry_tax=brp_mysqli_fetch_array($sundrytax1))
					{
						if($sundry_tax['sundry_gst_amount'] != 0){
							$total_sun1+=$sundry_tax['sundry_gst_amount'];
							$footer.='<tr> 
							<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;padding:0px;border-left:1px solid" >
							'.$sundry_tax['ledger_hsn'].'
							</td>
							<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;padding:0px" >
							'.$sundry_tax['sundry_amount'].'
							</td>';
							if($rel['stateid']==$set_head['stateid'])
							{
								$sun_gst_per = $sundry_tax['sundry_gst_per']/2;
								$sun_gst_amt = $sundry_tax['sundry_gst_amount']/2;
								$footer.='<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;padding:0px" >
								'.number_format($sun_gst_per,2).'
								</td>
								<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;padding:0px" >
								'.number_format($sun_gst_amt,2).'
								</td>
								<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;padding:0px" >
								'.number_format($sun_gst_per,2).'
								</td>
								<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;padding:0px" >
								'.number_format($sun_gst_amt,2).'
								</td>';
							}
							else if($rel['stateid']!=$set_head['stateid'])
							{
								$footer.='<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;padding:0px" >
								'.number_format($sundry_tax['sundry_gst_per'],2).'
								</td>
								<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;padding:0px" >
								'.number_format($sundry_tax['sundry_gst_amount'],2).'
								</td>';
							}
							$footer.='<td style="vertical-align:top;text-align:center;border-bottom:1px solid;border-right: none;padding:0px;border-right:1px solid" >
							'.$sundry_tax['sundry_gst_amount'].'
							</td>';

							$footer.='</tr>';
							$total_sunamt+=$sundry_tax['sundry_amount'];
							$total_suntaxamt1+=$sundry_tax['sundry_gst_amount']/2;
							$total_suntaxamt2+=$sundry_tax['sundry_gst_amount'];

						}
					}
					$footer.='<tr> 
					<td style="text-align: center;padding:0px;border-left:1px solid"><strong>Total</strong></td>
					<td style="vertical-align:top;text-align:center;border-top:1px solid;border-right:1px solid;padding:0px" >
					'.number_format($totalamt+$total_sunamt,2).'
					</td>
					';
					if($rel['stateid']==$set_head['stateid']){
						$footer.='<td style="vertical-align:top;text-align:center;border-top:1px solid;border-right:1px solid;padding:0px" >

						</td>
						<td style="vertical-align:top;text-align:center;border-top:1px solid;border-right:1px solid;padding:0px" >
						'.number_format($totaltaxamt1+$total_suntaxamt1,2).'
						</td>

						<td style="vertical-align:top;text-align:center;border-top:1px solid;border-right:1px solid;padding:0px" >

						</td>
						<td style="vertical-align:top;text-align:center;border-top:1px solid;border-left:1px solid;border-right:1px solid;padding:0px" >
						'.number_format($totaltaxamt2+$total_suntaxamt1,2).'
						</td>';
					}else if($rel['stateid']!=$set_head['stateid']){
						$footer.='<td style="vertical-align:top;text-align:center;border-top:1px solid;border-right:1px solid;padding:0px" >
						</td>
						<td style="vertical-align:top;text-align:center;border-top:1px solid;border-left:1px solid;border-right:1px solid;padding:0px" >
						'.number_format($totaltaxamt3+$total_suntaxamt2,2).'
						</td>';
					}
					$footer.='<td style="vertical-align:top;text-align:center;border-top:1px solid;padding:0px;border-right:1px solid">'.number_format($total1+$total_sun1,2).'</td>
					</tr>
					</table>';

					$footer.='<table >
						<tr>
						<td width="60%" style="vertical-align:top;border:1px solid;
						border-right:none;border-left:1px solid;border-bottom:1px solid;font-size:12px;text-align:left  !important">
						<strong>GST No : '.$set_head['vatno'].' <br>
						D.L.No : MFG/MD/2019/000061</strong><br>
						SUBJECT TO JURISDICTION <br>
						E. & O.E. <br>';
						if(!empty($set_head['conditions'])){
							$footer.='<strong>Terms and Conditions:</strong><br> '.$set_head['conditions'];
						}
						$footer.='</td>
						<td width="40%" style=" border-left:none;vertical-align:top;border-top:1px solid black;border-bottom:1px solid;border-right:1px solid; text-align: center;">
						For, <strong> <span style="font-size:14px;text-decoration:bold;">
						'.$set_head['company_name'].'</span></strong>
						<br>';
						if($set_head['authorized_signature']!=""){
							//$footer.='<img src="'.DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'].'" style="height: 100px; width: 100px;"><br>';
							$footer.='<br><br><br><br><br>';
						}else{
							$footer.='<br><br><br><br><br>';
						}
						$footer.='<span style="vertical-align:bottom;font-size:14px">Authorised Signatory</span>
						</td>
						</tr>
						</table>';
				}
			}
		}else{
			$footer.='<table width="100%;vertical-align:top" style="border: none;">
			<tr>
				<td style="width:25%;vertical-align:top;border:1px solid"> Bank Details : <br>
				Bank A/c No : '.$set_head['ac_no'].'<br>
				Bank Name   : '.$set_head['bank_name'].'<br>
				IFSC Code   : '.$set_head['ifcs'].'<br>
				Branch Name : '.$set_head['branch_name'].'<br>
				Beneficiary Name : '.$set_head['company_name'].'</td>
				<td colspan="3"></td>
			</tr>
			</table>';
	
		$footer.='<table >
			<tr>
				<td width="60%" style="vertical-align:top;border-right:none; border-left:1px solid; border-bottom:1px solid;font-size:12px;text-align:left  !important">
				<strong>GST No : '.$set_head['vatno'].'<br>
				D.L.No : MFG/MD/2019/000061</strong><br>
				SUBJECT TO JURISDICTION <br>
				E. & O.E. <br>';
				if(!empty($set_head['conditions'])){
					$footer.='<strong>Terms and Conditions:</strong><br> '.$set_head['conditions'];
				}
				$footer.='</td>
				<td width="40%" style=" border-left:none;vertical-align:top;border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid; text-align: center;">
				For, <strong> <span style="font-size:14px;text-decoration:bold;">
				'.$set_head['company_name'].'</span></strong>
				<br>';
				if($set_head['authorized_signature']!=""){
					//$footer.='<img src="'.DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'].'" style="height: 100px; width: 100px;"><br>';
					$footer.='<br><br><br><br><br>';
				}else{
					$footer.='<br><br><br><br><br>';
				}
				$footer.='<span style="vertical-align:bottom;font-size:14px">Authorised Signatory</span>
				</td>
			</tr>
			</table>';
		}

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
			padding:0px;
		}

		table tr,td{
			font-size:12px;
			/*border:1px solid black;*/
			padding:0px;
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
		<div>';
		
		$html.=$body;

		
		
		/* Get Terms And Condition Start */

		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
		
		/*echo $header;
		echo $html;
		echo $footer;*/
		// $get = "hello";
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','90','40','10','10');
		//$mpdf->setAutoBottomMargin = 'stretch';
        //$mpdf->setAutoTopMargin = 'stretch'; 
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
		//$mpdf->SetFooter('{PAGENO}{nbpg}');
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