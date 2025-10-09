<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
$type='pdf';
$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
if(strtolower($type) == 'pdf') {
	$query="SELECT invoice.*, country.country_name, state.state_name, cust.stateid, state.gst_state_code, city.city_name, cust.l_name, cust.m_address, type.invoice_type, cust_pincode, cust_cont_name, cust_mobile, gst_no, dispatch.mode_dispatch, cust.m_pan, cust.enable_sez, pay.payment_terms as terms from tbl_invoice as invoice 
	left join tbl_ledger as cust on cust.l_id=invoice.cust_id
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid
	left join city_mst as city on city.cityid=cust.cityid
	left join pay_terms as pay on pay.terms_id=invoice.payment_terms
	left join mode_of_dispatch as dispatch on dispatch.mode_dis_id=invoice.dispatch_doc_no
	left join tbl_invoicetype as type on type.invoicetype_id=invoice.invoicetype_id
	where invoice.invoice_id=$invoiceid";
	$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
	$cons_name  =$rel['l_name']; 
	$cons_addr  =$rel['m_address'];
	$cons_gst_no=$rel['gst_no'];
	$cons_pan_no=$rel['pan_no'];
	$cons_pin   =$rel['cust_pincode'];
	$cons_state_name=$rel['state_name'];
	$cons_country_name = $rel['country_name'];
	$cons_gst_state_code=$rel['gst_state_code'];
	$place_of_supply=$rel['city_name'];
	$cons_con_name = $rel['cust_cont_name'];
	$cons_mobile = $rel['cust_mobile'];
	if(!empty($rel['consignee_id']))
	{	
		$consignee="select * from tbl_custmer_consignee as cust 
		left join country_mst as country on country.countryid=cust.countryid
		left join state_mst as state on state.stateid=cust.stateid 
		left join city_mst as city on city.cityid=cust.cityid where cust_id=".$rel['consignee_id'];
		$cons_data=brp_mysqli_fetch_assoc($dbcon->query($consignee));	
		$cons_name  =$cons_data['company_name'];
		$cons_addr  =$cons_data['cust_address'];
		$cons_gst_no=$cons_data['gst_no'];
		$cons_pan_no=$cons_data['pan_no'];
		$cons_pin   =$cons_data['cust_pincode'];
		$cons_state_name=$cons_data['state_name'];
		$cons_country_name = $cons_data['country_name'];
		$cons_gst_state_code=$cons_data['gst_state_code'];
		$cons_con_name = $cons_data['cust_name'];
		$place_of_supply=$cons_data['city_name'];
		$cons_mobile = $cons_data['cust_mobile'];
	}

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$set_head=brp_mysqli_fetch_assoc($dbcon->query($set));	
	$challan_date='';$lr_date='';$dispatch_date='';$einv_AckDate='';
	if($rel['challan_date']!="1970-01-01" && $rel['challan_date']!="0000-00-00")
	{
		$challan_date=date('d-m-Y',strtotime($rel['challan_date']));
	}
	if($rel['dispatch_date']!="1970-01-01 00:00:00" && $rel['dispatch_date']!="0000-00-00 00:00:00")
	{
		$dispatch_date=date('d-m-Y h:i a',strtotime($rel['dispatch_date']));
	}
	if($rel['einv_AckDate']!="1970-01-01" && $rel['einv_AckDate']!="0000-00-00 00:00:00" && $rel['einv_AckDate']!="")
	{
		$einv_AckDate=date('d-m-Y',strtotime($rel['einv_AckDate']));
	}
	$po_no = '';
	$po_date = '';
	if($rel['is_sales_order']!=0){
		$qry = $dbcon->query("SELECT po_no, po_date FROM tbl_sales_order WHERE sales_order_id = '".$rel['sales_order_id']."'");
		$rels = brp_mysqli_fetch_assoc($qry);
		$po_no = $rels['po_no'];
		$po_date = ($rels['po_date']!='1970-01-01' && $rels['po_date']!='0000-00-00') ? date("d/m/Y",strtotime($rels['po_date'])) : '';
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

	/*if($rel['sales_order_id']!='')
	{
		$so_number = explode(",",$rel['sales_order_id']);
		for($i=0;$i<count($so_number);$i++)
		{
			$po_number.= get_id_detail($dbcon,'tbl_sales_order','sales_order_id',$so_number[$i],'po_no');
			$po_number_date.= date("d/m/Y",strtotime(get_id_detail($dbcon,'tbl_sales_order','sales_order_id',$so_number[$i],'po_date')));

		}
	}
	else
	{*/
		$po_number = $rel['order_no'];
		$order_date = $rel['order_date'];
	//}

	$sundrytax=$dbcon->query("select b.* from tbl_bill_sundry_transaction as b where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' ");
	$total_sundrytax=0;
	while($sumsundrytax=brp_mysqli_fetch_assoc($sundrytax)){
		$total_sundrytax = $total_sundrytax + $sumsundrytax['sundry_gst_amount'];
	}
	$sel_t = $dbcon->query("select trn.*,t.id,t.transportation_name from tbl_transport_transaction as trn 
		left join transportation_details as t on t.id=trn.transport_id
		where transport_transaction_table='tbl_invoice' and transport_transaction_table_id='$invoiceid'");
	$r_t=brp_mysqli_fetch_assoc($sel_t);
    
    $transport_gr_date='';
    if($r_t['transport_gr_date']!="1970-01-01" && $r_t['transport_gr_date']!="0000-00-00" && $r_t['transport_gr_date']!="")
	{
		$transport_gr_date=date('d-m-Y',strtotime($r_t['transport_gr_date']));
	}
    
	$qry_disc=$dbcon->query("SELECT SUM(trn.product_discount) as discount FROM `tbl_invoicetrn` as trn where trancation_status=0 and invoice_id=".$rel['invoice_id']);
	$rel_disc = brp_mysqli_fetch_assoc($qry_disc);

	/* Check Discount is On or off Start */
	if($rel_disc['discount'] > 0){
		$colspan=5;
		$dynamicwidth=40;
	}else{
		$colspan=4;
		$dynamicwidth=46;
	}
	// $logwi = 40; $textwi = 60;
	if($company_config['finance_print_letterhead_per']==0){
		//$header =get_header($dbcon,'text-align: center','100%','120px');
		$header .= '<table width="100%" style="border: none;">
		<tr style="border:none">
		    <td style="text-align: center;height:150px;border:none" colspan="5"></td>
		</tr>
		<tr>
			<td style="text-align: center;" colspan="5"> <strong style="font-size:14px;">'.(($rel["invoice_type"]) ? $rel["invoice_type"] : 'TAX INVOICE').'</strong></td>
		</tr>

		<tr>

			<td style="width:12%;text-align: left; border-right:none; border-bottom:none; "><strong>GSTIN</strong></td>
			<td style="width:25%;text-align: left;border-left:none;border-right:none;border-bottom:none; "> : <strong>'.$set_head['vatno'].'</strong></td>
			<td style="width:12%;text-align: left;border-left:none;border-right:none;border-bottom:none;"><strong>Invoice No</strong></td>
			<td style="width:25%;text-align: left;border-left:none; border-right:none; border-bottom:none;"> : '.$rel['invoice_no'].'</td>
			<td rowspan="7" style="width:26%;text-align: left;border-left:none;vertical-align:top;">';

			if(!empty($rel['einv_SignedQRCode'])){
				$header.='<img style="height: 120px;width: 120px;"  src="data:image/png;base64,'.$rel['einv_SignedQRCode'] .'" />';
			}
			$header.='</td>
		</tr>

		<tr>
			<td colspan="2" style="text-align: left;border-top:none; border-bottom:none;border-right:none;"><strong>'.$set_head['company_name'].'</strong></td>
			<td style="text-align: left;white-space:nowrap;border-top:none; border-bottom:none;border-right:none;border-left:none;font-size:14px;"><strong>Invoice Date</strong></td>
			<td style="text-align: left;border-top:none; border-bottom:none; border-right:none; border-left:none;"> : '.date('d-m-Y',strtotime($rel['invoice_date'])).'</td>
		</tr>
		<tr>
			<td colspan="2" rowspan="2" style="text-align: left;border-top:none; border-right:none;border-bottom:none;">'.$set_head['address'].'</td>
			<td style="text-align: left;border:none;"><strong>Challan No</strong></td>
			<td style="text-align: left;border:none;"> : '.$rel['challan_no'].'</td>
		</tr>
		<tr>
			<td style="text-align: left;white-space:nowrap;border:none;"><strong>Challan Date</strong></td>
			<td style="text-align: left;border:none;"> : '.date('d-m-Y',strtotime($rel['challan_date'])).'</td>
		</tr>
		
		<tr>
			<td style="border-top:none;border-right:none;border-bottom:none;"><strong>IRN No.</strong></td>
			<td colspan="3" style="border:none;"> : '.$rel['einv_Irn'].'</td>
		</tr>
		<tr>
			<td style="border-top:none;border-right:none;"><strong>Ack No.</strong></td>
			<td colspan="2" style="border-left:none;border-top:none;border-right:none;"> : '.$rel['einv_AckNo'].' &nbsp;&nbsp; <strong>Ack Date : </strong>'.$einv_AckDate.' </td>
			<td style="border-top:none; border-left:none; border-right:none"> </td>
		</tr>

		<tr>
			<td colspan="3" style="border-right:none;vertical-align:top;height:140px">
				
				<strong>Bill To Party</strong><br>
				<strong >GSTIN</strong> : '.$rel['gst_no'].'<br></div>
				<strong>M/s.'.$rel['l_name'].'</strong><br>
				<div style="padding-left: 6.5em;"><span >'.$rel['m_address'].' '.$rel['city_name'].' - '.$rel['cust_pincode'].' </span> <br>
				<span>State : '.$rel['state_name'].'-'.$rel['gst_state_code'].' </span><br>
				<span>'.$rel['country_name'].' </span>
				  <br>
				<span>Kind Atten : '.$rel['cust_cont_name'].' ('.$rel['cust_mobile'].') </span>
				
			</td>
			<td colspan="2" style="border-left:none; vertical-align:top;">
				<div style="height: 30px; overflow:hidden;">
					<strong>Ship To Party</strong><br>
					<strong >GSTIN</strong> : '.$cons_gst_no.'<br></div>
					<strong>M/s.'.$cons_name.'</strong><br>
					<div style="padding-left: 6.5em;"><span>'.$cons_addr.' '.$place_of_supply.' - '.$cons_pin.'</span> <br>
					<span>State : '.$cons_state_name.'-'.$cons_gst_state_code.' </span><br>
					<span>'.$cons_country_name.' </span> <br>
					<span>Kind Atten : '.$cons_con_name.' ('.$cons_mobile.') </span>
				</div>
			</td>
		</tr>
		</table>';
		
		//$footer = '<img src="'.DOMAIN_F.LOGO.$set_head["f_logo"].'" style="width:8.27in;">';
	}else{
		$header ='';
		$footer = '';
	}
	$custLedgerDetails = get_cust_data_arr($dbcon,$rel['cust_id']);
	$arr = get_grossbalance($dbcon,$rel['cust_id']);
	$company_state = get_company_data($dbcon,$_SESSION['company_id']);
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

		table td{
			font-size:12px;
			border:1px solid black;
			/*page-break-inside:avoid;*/
		}
		.quot_annex_content_div table tr,td{
			padding:5px;
		}

		.amt_summary{
			page-break-inside:avoid;
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
		
		
		$html.='<table style="border-collapse: collapse;page-break-inside: auto" cellpadding="0" cellspacing="0" width="100%">
		<thead>
		<tr>
		<th width="3%" style="border:1px solid;"><strong>Sr.<br/> No.</strong></th>
		<th width="'.$dynamicwidth.'%" style="border:1px solid;" ><strong>Item Details </strong></th>
		<th width="8%" style="border:1px solid;"><strong>HSN/SAC</strong></th>
		<th width="7%" style="border:1px solid;"><strong>UOM</strong></th>
		<th width="7%" style="border:1px solid;"><strong>Quantity</strong></th>
		<th width="7%" style="border:1px solid;"><strong>Rate</strong></th>';
        if($rel_disc['discount'] > 0){
			$html.='<th width="7%" style="border:1px solid;"><strong>Discount</strong></th>';
		}
		$html.='<th width="9%" style="border:1px solid;"><strong>Amount</strong></th>';
		
		$html.='</tr>
		</thead>
		<tbody style="border: none;">';
		$qry="select trn.*,product.product_name,unit_name FROM `tbl_invoicetrn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trancation_id order by product.product_type,trancation_id";

		$result=$dbcon->query($qry);		
		$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;
		$cnt=brp_mysqli_num_rows($result);
		while($row=brp_mysqli_fetch_assoc($result))
		{
			$gst_per = $row['cgst_tax_per']+$row['sgst_tax_per']+$row['igst_tax_per'];
			$gst_rate = $row['cgst_tax_rate_conv']+$row['sgst_tax_rate_conv']+$row['igst_tax_rate_conv'];

			if($row['cgst_tax_rate_conv'] != 0 || $row['sgst_tax_rate_conv'] !=0){
				$total_cs_gst += $gst_rate;
			}else{
				$total_i_gst += $gst_rate;
			}
			$html.='<tr style="height:25px;border-bottom:none;border-top:none">
			<td class="borderleftadd" style=" vertical-align:top;border-right:1px solid;border-left:1px solid;border-bottom:none;border-top:none">'.$i.'</td>
			<td style="border-right:1px solid;vertical-align:top;border-bottom:none;border-top:none"><strong>'.$row['product_name'].'</strong>';
			if($company_config['enable_item_description']==1){
				$html.='<br>'.(($row['description']) ? $row['description'] : '').'<br>';
			}
			
			$html.='</td>
			<td style=" border-right:1px solid; vertical-align:top; border-bottom:none; border-top:none;text-align:center" >'.$row['product_hsn_code'].'</td>
			<td style=" border-right:1px solid; vertical-align:top; border-bottom:none; border-top:none;text-align:center" >'.$row['unit_name'].'</td>
			<td style="vertical-align:top; border-right:1px solid; white-space:nowrap; border-bottom:none; border-top:none; text-align:right" ><strong>';
			if($row['product_type']!='3'){
				$html.=number_format($row['product_qty'],2);
			}else{
				$charges_qty+=$row['product_qty'];
				$html.=number_format($charges_qty,2);
			}
			$html.='</strong></td>
			<td style="vertical-align:top; border-right:1px solid; text-align: center; border-bottom:none; border-top:none; text-align:right" >'.number_format($row['product_rate_conv'],2).'</td>';
			if($rel_disc['discount'] > 0){
    			$html.='<td width="7%" style="vertical-align:top; border-right:1px solid; text-align: center; border-bottom:none; border-top:none; text-align:right">('.number_format($row['discount_per'],2).' %) '.number_format($row['product_discount'],2).'</td>';
    		}
			$html.='<td style=" vertical-align:top;border-right:1px solid; text-align: center; border-bottom:none; border-top:none; text-align:right"><strong>'.number_format(($row['product_amount_conv']),2).'</strong>	</td>';
			
			$html.='</tr>';
			$i++; 
			$totalqty=$totalqty+$row['product_qty'];
			$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
			$total_product_amount+=($row['product_qty']*$row['product_rate']);
			$totaltaxable+=$row['product_amount_conv'];
			$totaltax1+=$row['tax_amount1'];
			$totaltax2+=$row['tax_amount2'];
			$total+=$row['total'];
			$total_gst_rate +=$gst_rate;
		}
		
		$lines = 5;
		for($i=1; $i<=$lines; $i++){
			$html .='<tr style="border-bottom:none;border-top:none">
				<td style=" border-top:none;border-bottom:none;border-right:1px solid;vertical-align:top;height:25px" ></td>
				<td style=" border-top:none;border-bottom:none;border-right:1px solid;vertical-align:top;" ></td>
				<td style=" border-top:none;border-bottom:none;border-right:1px solid;vertical-align:top;" ></td>
				<td style=" border-top:none;border-bottom:none;border-right:1px solid;vertical-align:top;" ></td>
				<td style=" border-top:none;border-bottom:none;border-right:1px solid;vertical-align:top;" ></td>
				<td style=" border-top:none;border-bottom:none;border-right:1px solid;vertical-align:top;" ></td>';
				if($rel_disc['discount'] > 0){
				    $html .='<td style=" border-top:none;border-bottom:none;border-right:1px solid;vertical-align:top;" ></td>';
				}
				$html.='<td style=" border-top:none;border-bottom:none;border-right:1px solid;vertical-align:top;" ></td>
			</tr>';
		}

		$html .='<tr>
					<td style="vertical-align:top;height:25px"></td>
					<td style="vertical-align:top;"></td>
					<td style="vertical-align:top;text-align:center"><strong>Total Qty</strong></td>
					<td style="vertical-align:top;"></td>
					<td style="vertical-align:top;text-align:right"><strong>'.number_format($totalqty,2).'</strong></td>
					<td style="vertical-align:top;"></td>';
					if($rel_disc['discount'] > 0){
					    $html.='<td style="vertical-align:top;"></td>';
					}
					$html.='<td style="vertical-align:top;"></td>
				</tr>';

		$html .='<tr class="amt_summary">
			<td colspan="'.$colspan.'" style="border-bottom:none"><strong>Transporter : </strong> '.$r_t['transportation_name'].' &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <strong>L.R. No :</strong>'.$r_t['transport_gr_no'].' </td>
			<td colspan="2" style="text-align:left;border-bottom:none"><strong>Basic Amount</strong></td>
			<td style="text-align:right;border-bottom:none">'.number_format($totaltaxable,2).'</td>
		</tr>';

		if($rel['stateid']==$set_head['stateid'] && ($custLedgerDetails['enable_sez'] == 0)){
			if($company_config['tax_editable'] == 0){ 
				$c_gst = ($total_sundrytax+$total_cs_gst)/2;
				$s_gst = ($total_sundrytax+$total_cs_gst)/2;
			}else{
				$c_gst = $rel['cgst_conv'];
				$s_gst = $rel['sgst_conv'];
			}
			$merchantTax = ($totaltaxable*(0.1))/100;
			if($rel['sales_type'] == 2){ 
				$c_gst = $merchantTax/2;
				$s_gst = $merchantTax/2;
			}

			$tax_gst = $c_gst+$s_gst;
			$html.='<tr style="height:25px;" class="amt_summary">

				<td class="borderleftadd" colspan="'.$colspan.'" style="vertical-align:top; border-bottom:none; border-top:none"><strong>Vehicle No. :</strong> '.$r_t['transport_vehicle_no'].' &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;  <strong>L.R. Date :</strong> '.$transport_gr_date.'</td>

				<td  style="text-align:left;border-top:none; border-bottom:none;" colspan="2"><strong>CGST </strong></td>
				<td style="text-align: right; border-top:none; border-bottom:none;">'.number_format($c_gst,2).'</td>
			</tr>

			<tr style="height:25px;" class="amt_summary">
				<td colspan="'.$colspan.'" style="border-right:1px solid;border-top:none; border-bottom:none; border-top:none"></td>
				<td colspan="2" style="border-top:none; border-right:1px solid; text-align:left; border-bottom:none;" ><strong>SGST </strong></td>
				<td style="text-align:right; border-top:none; border-left:1px solid; border-bottom:none; ">'.number_format($s_gst,2).'</td>
			</tr>';
		}else{
			$html.='<tr style="height:25px;" class="amt_summary">
				<td colspan="'.$colspan.'" style="border-right:1px solid;border-bottom:none;border-top:none"><strong>Vehicle No. :</strong> '.strtoupper($r_t['transport_vehicle_no']).' &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <strong>L.R. Date :</strong> '.$transport_gr_date.'</td>
				<td colspan="2" style="border-right:1px solid;text-align:left; border-bottom:none; border-top:none" ><strong>IGST </strong></td>';
				if($company_config['tax_editable'] == 0){ 
					$i_gst = $total_sundrytax+$total_i_gst;
				}else{
					$i_gst = $rel['igst_conv'];
				}
				$merchantTax = ($totaltaxable*(0.1))/100;
				if($rel['sales_type'] == 2){ 
					$i_gst = $merchantTax;
				}
				$tax_gst = $i_gst;
				$html.='<td style="text-align:right; border-left:1px solid;border-bottom:none;border-top:none ">'.number_format($i_gst,2).'</td>
			</tr>';
		}

		$get_bill_sundry = get_bill_sundry_ledger($dbcon,1); 

		$query="SELECT sum(cgst_tax_rate) as cgst_rate,sum(sgst_tax_rate) as sgst_rate,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_invoicetrn` where invoice_id='$invoiceid' and trancation_status!=2 ";

		$rs_prel= brp_mysqli_fetch_assoc($dbcon->query($query));


		foreach ($get_bill_sundry as $billsundry) {

			if((($rs_prel['cgst_rate'] != 0) && $billsundry['l_name'] == 'CGST') || (($rs_prel['sgst_rate']!= 0) && $billsundry['l_name'] == 'SGST')){
				$gstValue = ($billsundry['l_name'] == 'CGST') ? $rs_prel['cgst_rate'] : (($billsundry['l_name'] == 'SGST') ? $rs_prel['sgst_rate'] : '');

				$gstValue_conv = ($billsundry['l_name'] == 'CGST') ? $rs_prel['cgst_rate_conv'] : (($billsundry['l_name'] == 'SGST') ? $rs_prel['sgst_rate_conv'] : '');
			}

			if(($billsundry['l_name'] == 'TCS') && ($company_config['enable_tcs_reporting'] == 1) && ($custLedgerDetails['enable_tcs']==1) && ($arr >= $company_config['gross_balance_limit'])){
				
				$total_tcs_calculate = $rs_prel['product_amount']+($gstValue*2)+$rs_prel['igst_rate'];

				$total_tcs_calculate_conv = $rs_prel['product_amount_conv']+($gstValue_conv*2)+$rs_prel['igst_rate_conv'];

				$html.='<tr style="height:25px;" class="amt_summary">
				<td colspan="'.$colspan.'" style="border-right:1px solid; border-top:none; border-bottom:none;"></td>
				<td colspan="2" style="border-right:1px solid; text-align:left; border-bottom:none; border-top:none" ><strong>'.$billsundry['l_name'].'</strong></td>
				
				<td style="text-align:right; border-left:1px solid; border-bottom:none; border-top:none ">'.(($rel['currency_id']==$company_state['currency_id']) ? round((($total_tcs_calculate*$billsundry['tax_value'])/100),2) : round((($total_tcs_calculate_conv*$billsundry['tax_value'])/100),2)).'</td>
				</tr>';
			}
		}

		$qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_invoicetrn as trn 
		left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
		left join tbl_ledger as l on l.l_id=tc.tax_id 
		where tc.tax_additional='1' and trn.invoice_id=".$rel['invoice_id']." and trn.trancation_status!=2 and tc.isdelete='0' group by tc.tax_id";

		$result11=$dbcon->query($qry11);		
		while($row11=brp_mysqli_fetch_assoc($result11)){
			$html.='<!-- Added By Dhruv -->
			<tr style="height:25px;" class="amt_summary">
			<td style="border-right:1px solid;border-top:1px solid; border-bottom:none; border-top:none"></td>
			<td style="border-top:1px solid;border-right:1px solid;text-align:right;border-bottom:none;border-top:none" ><strong>'.$row11['l_name'].'</strong></td>
			<td style="text-align:center; border-top:1px solid;border-left:1px solid; border-bottom:none;border-top:none">'.number_format($row11['add_sum'],2).'</td>
			</tr>';
		} 

		$qry12="select b.sundry_amount,b.sundry_amount_conv,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
		from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
		left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
		where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' and le.default_sundry='0' ";

		$result12=$dbcon->query($qry12);		
		while($row12=brp_mysqli_fetch_assoc($result12)){
			$html.='<!-- Added By Dhruv -->
			<tr style="height:25px;" class="amt_summary">
				<td colspan="'.$colspan.'" style="border-right:1px solid; border-bottom:none; border-top:none"></td>
				<td colspan="2" style="border-top:1px solid;border-right:1px solid; text-align:left;border-bottom:none;border-top:none" ><strong>'.$row12['l_name'].'</strong></td>
				
				<td style="text-align:right; border-top:1px solid;border-left:1px solid;border-bottom:none;border-top:none ">'.number_format($row12['sundry_amount_conv'],2).'</td>
			</tr>';
		}
		

		$html .='<tr style="height:25px;border-bottom:none;border-top:none" class="amt_summary">
			<td colspan="'.$colspan.'" style="border-right:1px solid; border-bottom:none; border-top:none;text-align:left"><strong>Bank Name : </strong>'.$set_head['bank_name'].' <strong>A/C No : </strong>'.$set_head['ac_no'].' </td>
			<td colspan="2" style="border-right:1px solid; text-align:left; border-bottom:none; border-top:none"><strong>Round Off</strong></td>
			
			<td style="text-align:right; border-left:1px solid; border-bottom:none; border-top:none ">'.number_format($rel['round_off_conv'],2).'</td>
		</tr>';

		$html.='<tr class="amt_summary">
			<td style="border-top:none;border-right:1px solid;border-left:1px solid; text-align:left;" colspan="'.$colspan.'"><strong>Branch & IFS Code  :</strong> '.$set_head['branch_name'].' & '.$set_head['ifcs'].'</td>
			<td colspan="2" style="text-align:left;border-top:none;border-right:1px solid;"><strong>Total</strong></td>';
			
		$html.='<td style="border-top:none; border-right:1px solid; text-align:right;"><strong>'.number_format($rel['g_total_conv'],0).'.00'.'</strong></td>';
		$html.='</tr>';
		
		$html.='</table>';
		$currency_code = getcurrencydetail($dbcon,$rel['currency_id']);
		
		/* Get Terms And Condition Start */
		$terms_qry="select qtrm.*,mst.tc_name from tbl_invoice_terms_trn as qtrm 
		left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
		where qtrm.invoice_terms_trn_status=0 and qtrm.invoice_id=".$rel['invoice_id']." order by qtrm.tc_priority";
		$terms_qry_rs=$dbcon->query($terms_qry);
		if(mysqli_num_rows($terms_qry_rs)){
			$t=1;
			$html.='<table style="font-size:14px;border-collapse: collapse;width:100%; page-break-inside: avoid;" cellpadding="5" cellspacing="5">
			<thead>
			<tr>
			<th colspan="2" style="text-align:left; border:none; border-left:1px solid; border-right:1px solid;border-top:1px solid">Terms and Condition</th>
			</tr>
			</thead><tbody>';
			while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
				$string=(nl2br($term_rel['tc_details']));
				$html.='<tr>
				<td width="25%" style="width:25%;text-align:left;border:none;border-left:1px solid; padding:5px;  vertical-align: top;">'.$term_rel['tc_name'].'</td>
				<td width="70%" style="width:70%;text-align:left;border:none; border-right:1px solid; padding:5px;"> : '.$string.'</td>
				</tr>';
				$t++;
			}
			$html.='</tbody></table>'; 
		}

		$html.='';
		$html .='<table width="100%" style="border: none;">
			<tr>
				<td style=" width:70%;border-top:1px solid;border-bottom:none;border-right:none;vertical-align:top;text-align:left"> <strong>Amount In Word</strong> :
					'.ucwords(convert_number_to_words_new($rel['g_total_conv'], $rel['currency_id'], $currency_code['currency_in_word_end'], $currency_code['currency_in_word'])).'
				</td>
				<td style=" width:30%;border-top:1px solid; border-bottom:none; border-right:1px solid; border-left:none; vertical-align:top; text-align:right;">
					E. & O.E
				</td>
			</tr>
		</table>';
		
		
		/* $html .='<table width="100%" style="border: none;">
			<tr  style="height:30px;border-bottom:none;border-top:none">
				<td colspan="4" style=" width:100%;border-bottom:1px solid; vertical-align:top; text-align:left"> <strong>Tax In Word</strong> : 
					'.ucwords(convert_number_to_words_new($tax_gst,$rel['currency_id'],$currency_code['currency_in_word_end'],$currency_code['currency_in_word'])).'
				</td>
			</tr>
		</table>'; */

		$html.='<table width="100%">
		<tr style="">
			<td width="60%" style="vertical-align:top;border:1px solid;
			text-align:left !important; ">';
			if(!empty($rel['invoice_condition'])){
				$html.='<strong>Terms and Conditions:</strong><br> '.$rel['invoice_condition'];
			}
			$html.='</td>
			<td width="40%" style=" vertical-align:top;border-top:1px solid black; text-align: center;">
			For, <strong> <span style="text-decoration:bold;">
			'.$set_head['company_name'].'</span></strong>
			<br>';
			if($set_head['authorized_signature']!=""){
				/*$html.='<img src="'.DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'].'" style="height: 100px; width: 100px;"><br>';*/
				$html.='<br><br><br><br><br><br><br>';
			}else{
				$html.='<br><br><br><br><br><br><br>';
			}
			$html.='<span style="vertical-align:bottom;">Authorised Signatory</span>
			</td>
		</tr>
		</table>';
		/* Get Terms And Condition Start */

		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
	    /*echo $header.$html;exit;*/
		// $get = "hello";
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");

		$mpdf=new mPDF('','A4','0','calibri','10','10','130','10','1','1');
		/*$mpdf->SetBorderColor('#000000');
		$mpdf->SetBorderWidth('2');
		$mpdf->AddPageByArray([
		    'margin-top' => '30',
		    'margin-bottom' => '20',
		]);*/
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		$mpdf->pagenumPrefix = 'Page ';
		$mpdf->pagenumSuffix = ' of ';
		$mpdf->nbpgPrefix = '';
		$mpdf->nbpgSuffix = '';
		//$mpdf->SetHTMLFooter('<div style="text-align:right">|{PAGENO} {nbpg}|</div>'.$footer);
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