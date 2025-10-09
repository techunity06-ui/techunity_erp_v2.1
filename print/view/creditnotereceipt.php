<?php
require_once '../../vendor/autoload.php';
use Mpdf\Mpdf;
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
$type='pdf';
// error_reporting(E_ALL);
$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
if(strtolower($type) == 'pdf') {
	$query="SELECT sa.*, country.country_name, state.state_name, cust.stateid, state.gst_state_code, city.city_name, cust.l_name, cust.m_address, cust_pincode, cust_mobile, gst_no, cust.m_pan, cust.enable_sez from tbl_sale_return as sa left join tbl_ledger as cust on cust.l_id=sa.sale_return_customer left join country_mst as country on country.countryid=cust.countryid left join state_mst as state on state.stateid=cust.stateid left join city_mst as city on city.cityid=cust.cityid where sa.sale_return_id=$invoiceid";

	$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
	// echo "<pre>";print_r($rel);die();
	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$set_head=brp_mysqli_fetch_assoc($dbcon->query($set));	
	// echo "<pre>";print_r($set_head);die();
	$topmrg= $set_head['letter_head_top_margin'];
	$sale_return_date='';$lr_date='';$sale_return_eway_bill_date='';
	if($rel['sale_return_date']!="1970-01-01" && $rel['sale_return_date']!="0000-00-00")
	{
		$sale_return_date=date('d-m-Y',strtotime($rel['sale_return_date']));
	}
	if($rel['sale_return_eway_bill_date']!="1970-01-01 00:00:00" && $rel['sale_return_eway_bill_date']!="0000-00-00 00:00:00")
	{
		$sale_return_eway_bill_date=date('d-m-Y h:i a',strtotime($rel['sale_return_eway_bill_date']));
	}

	

	$custLedgerDetails = get_cust_data_arr($dbcon,$rel['sales_ledger_id']);
	$company_config = getCompanyConfiguration($dbcon);

	$sundrytax=$dbcon->query("select b.* from tbl_bill_sundry_transaction as b where b.sundry_voucher_id=".$rel['sale_return_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' ");
	$total_sundrytax=0;
	while($sumsundrytax=brp_mysqli_fetch_assoc($sundrytax)){
		$total_sundrytax = $total_sundrytax + $sumsundrytax['sundry_gst_amount'];
	}
	$sel_t = $dbcon->query("select trn.*,t.id,t.transportation_name from tbl_transport_transaction as trn 
		left join transportation_details as t on t.id=trn.transport_id
		where  transport_transaction_table='tbl_sale_return' and transport_transaction_table_id='$invoiceid'"); 

	/*echo "select trn.*,t.id,t.transportation_name from tbl_transport_transaction as trn 
		left join transportation_details as t on t.id=trn.transport_id
		where  trn.transport_transaction_table='tbl_sale_return' and transport_transaction_table_id='$invoiceid'";
	exit;*/
	$r_t=brp_mysqli_fetch_assoc($sel_t);

	$qry_disc=$dbcon->query("SELECT SUM(trn.product_discount) as discount FROM `tbl_invoicetrn` as trn where trancation_status=0 and invoice_id=".$rel['sale_return_id']);
	$rel_disc = brp_mysqli_fetch_assoc($qry_disc);

	/* Check Discount is On or off Start */
	if($rel_disc['discount'] > 0){
		$colspan=5;
		$dynamicwidth=40;
	}else{
		$colspan=6;
		$dynamicwidth=46;
	}
	// $logwi = 40; $textwi = 60;
	if($company_config['finance_print_letterhead_per']==0){
		//$header = get_header($dbcon,'text-align: center','100%','70px');
		$header ='';
		$footer = '';
	}else{
		$header ='';
		$footer = '';
	}

	$html ='<html>
	<head>					
	<title>Credit Note - '.$rel['sal_return_voucher_no'].'</title>
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
		<table width="100%" style="border: none;">
		<tr style="border: none;">
		<td style="border:none;text-align: right; width:55%;" colspan="0"> <strong style="font-size:14px;">Credit Note</strong></td>
		<td style="border: none;text-align: right;" colspan="0"> <strong style="font-size:14px;">Original / Duplicate / Triplicate</strong></td>
		</tr>
		</table>
		<table style="font-size:10px;border-collapse: collapse;border-top:none !important;" cellpadding="0" cellspacing="0" width="100%" class="maintable">';
		if($rel['enable_sez'] == 1 || $rel['sales_type'] == 2){ 
			$html.='<tr>																		
			<td colspan="7" style="padding: 10px;"><span style="font-weight:normal;">( SUPPLY MEANT FOR EXPORT/SUPPLY TO SEZ UNIT OR SEZ DEVELOPER FOR AUTHORIZED OPERATIONS UNDER BOND OR LETTER OF UNDERTAKING WITHOUT PAYMENT OF IGST ) </span> </td>
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
		<td style="width:80px;border:1px solid; border-right:none !important;"><strong>Voucher No </strong>
		</td>
		<td style="width:120px;border-bottom:1px solid; border-right:1px solid;border-top:1px solid;border-left:none !important;">: <strong>'.$rel['sal_return_voucher_no'].'</strong>
		</td>
		<td style="width:20px;border-bottom:1px solid;border-top:1px solid; border-right:none;"><strong>Date </strong>
		</td>
		<td style="width:120px;border-bottom:1px solid;border-right:1px solid;border-top:1px solid;border-left:none;">: <strong>'.$sale_return_date.'</strong>
		</td>
		<td  style="width:120px;border-bottom:1px solid;border-top:1px solid;border-right:none;"><strong>Transport Detail</strong>	
		</td>							
		<td colspan="2" style="border-bottom:1px solid;border-top:1px solid;border-right:1px solid;border-left:none;white-space: nowrap;">: '.$r_t['transportation_name'].'</td>
		</tr>
		
		<!--<tr>
		<td style="border-bottom:1px solid;border-left:1px solid; border-right:none;"><strong>State</strong></td>
		<td style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.$set_head['state_name'].'
		<td class="boderremoveres" style="border-bottom:1px solid;border-right:none;"><strong>Code</strong></td>
		<td style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.$set_head['gst_state_code'].'</td>
		<td style="border-bottom:1px solid;border-right:none;"><strong>Payment Terms</strong> </td>
		<td colspan="2" style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.$rel['terms'].'</td>
		</tr>-->
		<tr>
		<td style="border-bottom:1px solid;border-left:1px solid;border-right:none;"><strong>E-way Bill No.</strong></td>
		<td style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.$rel['sale_return_eway_bill_no'].'
		<td class="boderremoveres" style="border-bottom:1px solid;border-right:none;white-space:nowrap"><strong>Vehicle No</strong></td>
		<td style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.$r_t['transport_vehicle_no'].'</td>
		<td style="border-bottom:1px solid;border-right:none;"><strong>Reverse Charge</strong></td>
		<td colspan="2" style="border-bottom:1px solid;border-right:1px solid;border-left:none;">: '.(!empty($rel['reverse_charge'])?'Yes':'No').'</td>
		</tr>
		<tr>
		<td colspan="4"  style="vertical-align:top;border-right:1px solid;border-left:1px solid;"><b>Bill to Party : </b><br/><strong>'.$rel['l_name'].'</strong><br><span style="font-weight:normal;">'.$rel['m_address'].'<br/>'.$rel['city_name'].', '.$rel['state_name'].', '.$rel['country_name'];
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
		
		$html.='<td colspan="3"  style="vertical-align:top;border-right:1px solid"><b>Shipped to Party : </b><br><strong>'.$rel['l_name'].'</strong><br><span style="font-weight:normal;">'.$rel['m_address'].'<br/>'.$rel['city_name'].', '.$rel['state_name'].', '.$rel['country_name'];
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
		
		$html.='</tr>';

		$html.='<tr>
			<td colspan="4" style="border-right:1px solid;border-left:1px solid;"><strong>GSTIN: '.$rel['gst_no'].' <br>PAN No. : '.$rel['m_pan'].'</strong></td>
			<td colspan="3" style="border-right:1px solid; vertical-align: top;"><strong>GSTIN:  '.$rel['gst_no'].'</strong></td>
		</tr>
		<tr> 
			<td colspan="3" style="border-left:1px solid;border-bottom:1px solid;font-weight:normal;">State : '.$rel['state_name'].'</td>
			<td style="border-right:1px solid;text-align:left;border-bottom:1px solid;border-right:1px solid;font-weight:normal;">Code : '.$rel['gst_state_code'].'</td>
			<td colspan="2" style="text-align:left;border-bottom:1px solid;font-weight:normal;">State : '.$rel['state_name'].'</td>
			<td style="text-align:left;border-bottom:1px solid;border-right:1px solid;font-weight:normal;">Code : '.$rel['gst_state_code'].'</td>
		</tr>';
	
		$html.='</table>
		<table style="border-collapse: collapse;border-top:none !important;" cellpadding="0" cellspacing="0" width="100%">
		<thead>
		<tr>
		<th width="3%" style="border:1px solid;border-top: none;"><strong>SR.<br/> NO.</strong></th>
		<th width="'.$dynamicwidth.'%" style="border:1px solid;border-top: none;" ><strong>Particulars </strong></th>
		<th width="7%" style="border:1px solid;border-top: none;"><strong>HSN <br> Code</strong></th>
		<th width="7%" style="border:1px solid;border-top: none;"><strong>QTY.</strong></th>
		<th width="7%" style="border:1px solid;border-top: none;"><strong>Rate</strong></th>';
		if($rel_disc['discount'] > 0){
			$html.='<th width="6%" style="border:1px solid;border-top: none;"><strong>Less:<br/>Disc.</strong></th>';
		}
		$html.='<th width="9%" style="border:1px solid;border-top: none;"><strong>Taxable<br/>Value</strong></th>';
		//if($company_config['tax_editable'] == 0 && $rel['sales_type'] == 1 ){
			$html.='<th width="4%" style="border:1px solid;border-top: none;"><strong>GST Rate</strong></th> 
			<!--<th width="6%" style="border:1px solid;border-top: none;"><strong>GST Amount</strong></th>-->';
		//}
		$html.='<th width="10%" style="border:1px solid;border-top: none;"><strong>Total</strong></th>';
		$html.='</tr>
		</thead>
		<tbody style="border: none;">';
		$qry="select trn.*,product.product_name,unit_name,hsn.hsn_code FROM `tbl_sale_return_transaction` as trn 
		left join product_mst as product on product.product_id=trn.sale_return_product 
		left join unit_mst as per on per.unitid=trn.sale_return_unit 
		left join mst_hsn_code as hsn on hsn.hsn_id = product.product_hsn
		where trancation_status=0 and sale_return_id=".$rel['sale_return_id']." group by sale_return_transaction_id order by product.product_type,sale_return_transaction_id";
		
		$result=$dbcon->query($qry);		
		$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;
		$cnt=brp_mysqli_num_rows($result);
		while($row=brp_mysqli_fetch_assoc($result))
		{
			$gst_per = $row['sale_return_cgst_tax_per']+$row['sale_return_sgst_tax_per']+$row['sale_return_igst_tax_per'];
			$gst_rate = $row['sale_return_cgst_tax_amt']+$row['sale_return_sgst_tax_amt']+$row['sale_return_igst_tax_amt'];

			if($row['sale_return_cgst_tax_amt'] != 0 || $row['sale_return_sgst_tax_amt'] !=0){
				$total_cs_gst += $gst_rate;
			}else{
				$total_i_gst += $gst_rate;
			}
			$html.='<tr style="height:25px;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0;">
			<td class="borderleftadd" style=" vertical-align:top;border-right:1px solid;border-left:1px solid;">'.$i.'</td>
			<td style="border-right:1px solid;vertical-align:top;"><strong>'.$row['product_name'].'</strong>';
			if($company_config['enable_item_description']==1){
				$html.='<br>'.(($row['description']) ? stripslashes($row['description']) : '').'<br>';
			}
			
			$html.='</td>
			<td style=" border-right:1px solid;vertical-align:top;" >'.$row['hsn_code'].'</td>
			<td style="vertical-align:top; border-right:1px solid;white-space:nowrap;" >';
			if($row['product_type']!='3'){
				$html.=$row['sale_return_qty'].' '.$row['unit_name'];
			}else{
				$charges_qty+=$row['sale_return_qty'];
				$html.=$charges_qty;
			}
			$html.='</td>
			<td style="vertical-align:top; border-right:1px solid; text-align: center;" >'.number_format($row['sale_return_rate'],2,".","").'</td>';
			if($rel_disc['discount'] > 0){
				$html.='<td style=" vertical-align:top;border-right:1px solid; text-align: center;">'.number_format($row['discount_per'],2,".","").'%'.'</td>';
			}
			$html.='<td style=" vertical-align:top;border-right:1px solid; text-align: center;">'.number_format(($row['sale_return_amount']),2,".","").'</td>';
			///if($company_config['tax_editable'] == 0 && $rel['sales_type'] == 1 ){
				$html.='<td style=" vertical-align:top;border-right:1px solid; text-align: center;">'.$gst_per.'%</td>
				<!--<td style="vertical-align:top;border-right:1px solid; text-align: center;">'.number_format($gst_rate,2,".","").'</td>-->
				<td style="vertical-align:top;border-right:1px solid; text-align: center;">'.number_format($row['sale_return_total_amount'],2,".","").'</td>';
			//}
			$html.='</tr>';
			$i++; 
			$totalqty=$totalqty+$row['sale_return_qty']-$charges_qty;
			$total_product_amount+=($row['sale_return_qty']*$row['sale_return_rate']);
			$totaltaxable+=$row['sale_return_amount'];
			
			$total+=$row['sale_return_total_amount'];
			$total_gst_rate +=$gst_rate;
		}
		$cont  = 8-$cnt;
		for($i=1;$i<$cont;$i++){
			$html .= '<tr style="height:30px;border-top:1px solid #e1e0e0;border-bottom:1px solid #e1e0e0">
				<td class="borderleftadd" style="height:30px; vertical-align:top"></td>
				<td class="borderleftadd" style="height:30px; vertical-align:top"></td>
				<td class="borderleftadd" style="height:30px; vertical-align:top"></td>
				<td class="borderleftadd" style="height:30px; vertical-align:top"></td>
				<td class="borderleftadd" style="height:30px; vertical-align:top"></td>
				<td class="borderleftadd" style="height:30px; vertical-align:top"></td>
				<td class="borderleftadd" style="height:30px; vertical-align:top"></td>
				<td class="borderleftadd" style="height:30px; vertical-align:top"></td>
			</tr>';
		}
		$html.='<tr style="height:20px;border-top:2px solid;">
		<td style="border-top:1px solid;border-right:1px solid;border-left:1px solid; text-align:center;" colspan="3"><strong>Total</strong></td>
		<td style="text-align:center;border-top:1px solid;border-right:1px solid;"><strong>'.number_format($totalqty,2,".","").'</strong></td>';
		if($rel_disc['discount'] > 0){
			$html.='<td style="border-top:1px solid;border-right:1px solid;"></td>';
		}
		$html.='<td style="border-top:1px solid;border-right:1px solid;"></td>
		<td style="border-top:1px solid;border-right:1px solid;text-align:center;"><strong>'.number_format($totaltaxable,2,".","").'</strong></td>';
		//if($company_config['tax_editable'] == 0 && $rel['sales_type'] == 1 ){
			$html.='<td style="border-top:1px solid;border-right:1px solid;text-align:center;"></td>
			<!--<td style="border-top:1px solid;border-right:1px solid;text-align:center;"><strong>'.number_format($total_gst_rate,2,".","").'</strong></td>
			<td style="border-top:1px solid;border-right:1px solid;text-align:right;"><strong>'.number_format($total,2,".","").'</strong></td>-->';
		//}
		$html.='<td style="border-top:1px solid;border-right:1px solid;text-align:right;"><strong>'.number_format($total,2,".","").'</strong></td>';
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
			<td style="text-align:right; border-top:1px solid;border-left:1px solid; ">'.number_format($c_gst,2,".","").'</td>
			</tr>
			<tr height="20px">
			<td  style="border-right:1px solid;border-top:1px solid; "></td>
			<td style="border-top:1px solid;border-right:1px solid;text-align:left" >SGST</td>
			<td style="text-align:right; border-top:1px solid;border-left:1px solid; ">'.number_format($s_gst,2,".","").'</td>
			</tr>';
		}else{
			$html.='<tr height="20px">
			<td  style="border-right:1px solid;border-top:1px solid;">';
			if(!empty($set_head['ifcs'])){ 
				$html.='<strong>IFSC:</strong>'.$set_head['ifcs'].',';
			}
			if(!empty($set_head['branch_name'])){
				$html.='<strong>Branch :</strong> '.$set_head['branch_name'];
			}
			$html.='</td>
			<td style="border-top:1px solid;border-right:1px solid;text-align:left" >IGST</td>';
			if($company_config['tax_editable'] == 0){ 
				$i_gst = $total_sundrytax+$total_i_gst;
			}else{
				$i_gst = $rel['igst'];
			}
			$merchantTax = ($totaltaxable*(0.1))/100;
			if($rel['sales_type'] == 2){ 
				$i_gst = $merchantTax;
			}

			$html.='<td style="text-align:right; border-top:1px solid;border-left:1px solid; ">'.number_format($i_gst,2,".","").'</td>
			</tr>';
		} 

		$qry121="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
		from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
		left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
		where b.sundry_voucher_id=".$rel['sale_return_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' and b.sundry_ledger_id=".TCS." ";
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
			<td  style="border-right:1px solid;border-top:1px solid; "></td>
			<td style="border-top:1px solid;border-right:1px solid;text-align:left" >TCS</td>
			<td style="text-align:right; border-top:1px solid;border-left:1px solid; ">'.number_format($tcs_gst,2,".","").'</td>
			</tr>';
		}
		$qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_invoicetrn as trn 
		left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
		left join tbl_ledger as l on l.l_id=tc.tax_id 
		where tc.tax_additional='1' and trn.invoice_id=".$rel['sale_return_id']." and trn.trancation_status!=2 and tc.isdelete='0' group by tc.tax_id";
		$result11=$dbcon->query($qry11);		
		while($row11=brp_mysqli_fetch_assoc($result11)){
			$html.='<!-- Added By Dhruv -->
			<tr height="20px">
			<td style="border-right:1px solid;border-top:1px solid; "></td>
			<td style="border-top:1px solid;border-right:1px solid;text-align:left" >'.$row11['l_name'].'</td>
			<td style="text-align:right; border-top:1px solid;border-left:1px solid; ">'.number_format($row11['add_sum'],2,".","").'</td>
			</tr>';
		} 
		$qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
		from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
		left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
		where b.sundry_voucher_id=".$rel['sale_return_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' and le.default_sundry='0' ";
		$result12=$dbcon->query($qry12);		
		while($row12=brp_mysqli_fetch_assoc($result12)){
			$html.='<!-- Added By Dhruv -->
			<tr height="20px">
			<td style="border-right:1px solid;border-top:1px solid; font-size:10px;"></td>
			<td style="border-top:1px solid;border-right:1px solid;text-align:left" >'.$row12['l_name'].'</td>
			<td style="text-align:right; border-top:1px solid;border-left:1px solid; ">'.number_format($row12['sundry_amount'],2,".","").'</td>
			</tr>';
		}
		if($tax_name[1]) { 
			$html.='<tr height="20px">
			<td style="border-right:1px solid;border-top:1px solid; font-size:10px;"></td>
			<td style="border-top:1px solid;border-right:1px solid;text-align:left">Add : ';
			$strt=$tax_name[1];
			$position = strpos($strt, "TCS", 0);
			if ($position == true){ 
				$html.=$tax_name[1];
			} else{
				$html.='SGST';	
			}
			$html.='</td>
			<td style="text-align:right; border-top:1px solid;border-left:1px solid; ">'.number_format($totaltax2,2,".","").'</td>
			</tr>';
		} 
		$totaltax=$totaltax1+$totaltax2;
		$total=($total)+$rel['packing']; 
		$r=round($total)-$total;  
		$round = round($rel['sale_return_gtotal']) - $rel['sale_return_gtotal'];
		$html.='<tr height="20px">
		<td style="border-right:1px solid;border-top:1px solid;"><strong>GST Amount(In Words):</strong> '.ucwords(convert_number_to_words_new($total_sundrytax+$total_i_gst+$total_cs_gst)).'</td>
		<td style="border-top:1px solid;border-right:1px solid;text-align:left" >Total Tax Amount</td>
		<td style="text-align:right; border-top:1px solid;border-left:1px solid; ">'.number_format($total_sundrytax+$total_i_gst+$total_cs_gst,2,".","").'</td>
		</tr>
		<tr height="20px">
		<td style="border-right:1px solid;border-top:1px solid;font-size:12px;"><strong>COMPANY GSTIN : '.$set_head['vatno'].' </strong><br></td>
		<td style="border-top:1px solid;border-right:1px solid;text-align:left">Round Off</td>
		<td style="text-align:right; border-top:1px solid;border-left:1px solid; ">'. number_format($round,2,".","").'</td>
		</tr>
		<tr height="20px">
		<td style="border-right:1px solid;border-top:1px solid;"><strong>Rupees:</strong>'.ucwords(convert_number_to_words_new(round($rel['sale_return_gtotal']))).'</td>
		<td style="border-top:1px solid;border-right:1px solid;text-align:left"><strong>Grand Total</strong> :</td>
		<td style="text-align:right; border-top:1px solid;border-left:1px solid; "><strong>'.number_format($rel['sale_return_gtotal'],0,".","").'.00'.'</strong></td>
		</tr>
		<tr style="height:30px">
		<td colspan="3" style="height:30px;border:1px solid;border-left:none;border-right:none;">Remark:'.(($rel['remark']) ? $rel['remark'] : '').'</td>
		</tr>
		</table>';
		//if($company_config['tax_editable'] == 0){
			/*if($rel['sales_type']==1){*/
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

				$query="select sum(sale_return_amount) as product_amount, trn.sale_return_hsn as product_hsn_code, sale_return_cgst_tax_per as cgst_tax_per, sum(sale_return_cgst_tax_amt) as cgst_tax_rate, sale_return_sgst_tax_per as sgst_tax_per, sum(sale_return_sgst_tax_amt) as sgst_tax_rate, sale_return_igst_tax_per as igst_tax_per,sum(sale_return_igst_tax_amt) as igst_tax_rate FROM `tbl_sale_return_transaction` as trn 
				
				where trancation_status=0 and sale_return_id=".$rel['sale_return_id']." group by trn.sale_return_hsn";
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
					where b.sundry_voucher_id=".$rel['sale_return_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' ");
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
							'.number_format($sun_gst_per,2).'
							</td>
							<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
							'.number_format($sun_gst_amt,2).'
							</td>
							<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
							'.number_format($sun_gst_per,2).'
							</td>
							<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
							'.number_format($sun_gst_amt,2).'
							</td>';
						}
						else if($rel['stateid']!=$set_head['stateid'])
						{
							$html.='<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
							'.number_format($sundry_tax['sundry_gst_per'],2).'
							</td>
							<td style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
							'.number_format($sundry_tax['sundry_gst_amount'],2).'
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
			//}
		//}
		$html.='<table >
		<tr>
		<td width="60%" style="vertical-align:top;border:1px solid;
		border-right:none;border-left:none;border-bottom:none;font-size:14px;text-align:left  !important">';
		if(!empty($set_head['conditions'])){
			$html.='<strong>Terms and Conditions:</strong><br> '.$set_head['conditions'];
		}
		$html.='</td>
		<td width="40%" style=" border-left:none;vertical-align:top;border-top:1px solid black; text-align: center;">
		For, <strong> <span style="font-size:14px;text-decoration:bold;">
		'.$set_head['company_name'].'</span></strong>
		<br>';
		if($set_head['authorized_signature']!=""){
			
			$html.='<img src="'.DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'].'" style="height: 100px; width: 100px;"><br>';
		}else{
			$html.='<br><br><br>';
		}
		$html.='<span style="vertical-align:bottom;font-size:14px">Authorised Signatory</span>
		</td>
		</tr>
		</table>';
		/* Get Terms And Condition Start */

		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
		
		// echo $html;exit;
		// $get = "hello";
		ob_end_clean();
		// include("../../view/export/mpdf/mpdf.php");
		// $mpdf=new mPDF('','A4','0','calibri','10','10',$topmrg,'10','1','1');
		include("../../vendor/mpdf/mpdf/src/Mpdf.php");
		$mpdf = new Mpdf(['format' => 'A4','margin_left' => 10,'margin_right' => 10,'margin_top' => 10,$topmrg,'margin_bottom' => 10,'margin_header' => 1,'margin_footer' => 1,'default_font' => 'calibri']);

		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = 'B'; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = 'B'; /* blank, B, I, or BI */
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