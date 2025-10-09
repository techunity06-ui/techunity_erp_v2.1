<?php 
$printstatus = $_REQUEST['printstatus'];	
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
$type='pdf';

$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
if(strtolower($type) == 'pdf') {
	$query="SELECT invoice.*, country.country_name, state.state_name, cust.stateid,cust_con_p.cust_contact_person_name , state.gst_state_code, city.city_name, cust.l_name, cust.m_address, type.invoice_type, cust_pincode, cust_mobile, gst_no, dispatch.mode_dispatch, cust.m_pan, cust.enable_sez, pay.payment_terms as terms from tbl_invoice as invoice
		 
	left join tbl_cust_contact_person  as cust_con_p on cust_con_p.cust_id=invoice.cust_id
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

	$header ='<table style="border:none;">
		<tr style="border:none;">
		
		<td style="width:40%;border:none; text-align:left;"> 
		
		<img src="../../view/img/logo/'.$set_head["logo"].'" style="height: 100px; width: 225px;" />
		</td>
		<td style="width:60%;border:none;"> 
			<strong>'.$set_head["address"].'<br/>
			Mob.No : '.$set_head["contact_no"].'<br/>
			Email : '.$set_head["website"].'<br/>
			Web : '.$set_head["company_website"].'</strong>
		</td>
		</tr>
		</table>
		
	';
	$approve_status='';
		if($rel['approve_status']=='0'){
			$approve_status=' (DRAFT)';
		}
	$topmrg= $set_head['letter_head_top_margin'];
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
		$po_number_date =date('d-m-Y',strtotime($rel['order_date']));
		
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

	$qry_disc=$dbcon->query("SELECT SUM(trn.product_discount) as discount FROM `tbl_invoicetrn` as trn where trancation_status=0 and invoice_id=".$rel['invoice_id']);
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

	 if($printstatus==0){
	 	$header ='';
	 	$footer ='';
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

		<!--Show Logo in other pages-->
			<htmlpageheader name="otherpages" style="display:none">
			<div style="text-align:center">'.$header.'</div>
			</htmlpageheader>
			<htmlpagefooter name="otherpages_footer" style="display:none">
			<div style="text-align:center">'.$footer.'</div>
			</htmlpagefooter>
			<sethtmlpageheader name="otherpages" value="off" show-this-page="0"/>
		<div>


		<table style="font-size:14px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
					<tr >
						<td width="25%" style="border:1px solid;border-right:0px solid;"><strong>Our GSTIN No : '.$set_head["vatno"].'</strong></td>
						
						<td width=40%; style="border:none;text-align: center; " colspan="0"> <strong style="font-size:14px;">'.(($rel["invoice_type"]) ? $rel["invoice_type"] : 'TAX INVOICE').'</strong></td>
		<td width="25%" style="border: none;text-align: right;" colspan="0"> <strong style="font-size:14px;"><input type="checkbox" id="myCheckbox" name="myCheckbox">Orignal <input type="checkbox" id="myCheckbox" name="myCheckbox"> Duplicate <input type="checkbox" id="myCheckbox" name="myCheckbox"> Triplicate</strong></td>
					</tr>
				</table>
		
		
		<table style="font-size:10px;border-collapse: collapse;border-top:none !important;" cellpadding="0" cellspacing="0" width="100%" class="maintable">';
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
		$html.='</table>
		<table style="font-size:12px; width:100%;" cellpadding="5" cellspacing="5">
				<tr style="border-bottom:none;">

					<td  width="10%" style="border: 0;border-bottom:none;"><strong>Cust. Name</strong></td>
					<td   style="border: 0;border-bottom:none;"><strong>:</strong></td>
					<td width="45%" colspan="4" style="border: 0;border-bottom:none;border-top:none; padding: 1px;"><strong>'.$rel['l_name'].'</strong></td>

<td  width="10%" style="border: 0; border-left: 1px solid black;border-bottom:none;border-top:none;"><strong>invoice No </strong></td>
<td style="border-bottom:none;border-top:none;border:0px solid;border-right:none;vertical-align:top"><strong>:</strong></td>

<td width="25%" style="border: 0;border-top:none;"><strong>'.$rel['invoice_no'].'</strong></td>

				</tr>
				
				<tr style="border-top:none;border-bottom:none;">
					<td style="border-bottom:none;border-top:none;border-left:1px solid;border-right:none;vertical-align:top"><strong>Kind. Attn</strong></td>
					<td style="border-bottom:none;border-top:none;border:0px solid;border-right:none;vertical-align:top"><strong>:</strong></td>
					<td style="border-bottom:none;border-top:none;border-left:none;border-right:none;vertical-align:top">'.$rel['cust_contact_person_name'].' '.$rel['c_con_lname'].'</td>
					<td style="border-bottom:none;border-top:none;border-right:none;border-left:none;text-align:right;vertical-align:top"><strong>Mob.No :</strong></td>
					<td colspan="2" style="border-bottom:none;border-top:none;border-right:none;border-left:none;vertical-align:top">'.$rel['cust_mobile'].'</td>
					<td  style="border-bottom:none;border-top:none;border-right:none;vertical-align: top; "><strong>Date</strong></td>
					<td  style="border-bottom:none;border-top:none;border-right:none;border:0px;vertical-align: top;"><strong>:</strong></td>
					<td  style="border-bottom:none;border-top:none;border-left:none;border-right:1px solid;vertical-align: top;">'.date('d-m-Y',strtotime($rel['invoice_date'])).'</td>
				</tr>
				<tr style="border-top:none;border-bottom:none;">
					<td style="border-bottom:none;border-top:none;border-left:1px solid;border-right:none;vertical-align:top"><strong>Address</strong></td>
					<td style="border-bottom:none;border-top:none;border:0px solid;vertical-align:top;border-right:none;"><strong>:</strong></td>
					<td colspan="" style="border-bottom:none;border-top:none;border-left:none;border-right:0px solid;vertical-align:top">'.$rel['m_address'].'<br/>'.$rel['city_name'].', '.$rel['state_name'].', '.$rel['country_name'].'</td>
					
					<td colspan="3" style="border-bottom:none;border-top:none;border-left:none;border-right:0px solid;vertical-align:top"></td>
				
					<td colspan="" style="border-bottom:none;border-top:none;border-left:1px solid black;border-right:0px solid;vertical-align:top"><STRONG>PO NO  </STRONG></td>
					<td colspan="" style="border-bottom:none;border-top:none;border-left:none;border-right:0px solid;vertical-align:top">:</td>
					<td colspan="" style="border-bottom:none;border-top:none;border-left:none;border-right:0px solid;vertical-align:top">'.$rel['order_no'].'</td>
					
				</tr>
				<tr style="border-top:none;border-bottom:none;">
					<td style="border-bottom:none;border-top:none;border-left:1px solid;border-right:none;border-bottom:1px solid;vertical-align:top"><strong>GSTIN</strong></td>
					<td style="border-bottom:none;border-top:none;border:0px solid;border-bottom:1px solid;border-right:none;vertical-align:top"><strong>:</strong></td>
					<td colspan="4" style="border-left:none;border-bottom:none;border-top:none;border-right:0px solid;border-bottom:1px solid;vertical-align:top">'.$rel['gst_no'].'</td>
					<td colspan="" style="border-bottom:none;border-top:none;border-left:1px solid black;border-right:0px solid;vertical-align:top"><STRONG>PO Date  </STRONG></td>
					<td colspan="" style="border-bottom:none;border-top:none;border-left:none;border-right:0px solid;vertical-align:top">:</td>
					<td colspan="" style="border-bottom:none;border-top:none;border-left:none;border-right:0px solid;vertical-align:top">'.$po_date.'</td>
				</tr>
			</table>';

			if($rel['enable_consignee']==1) {
				$html.='<table style="font-size:12px; width:100%;" cellpadding="5" cellspacing="5">
				<tr style="border-bottom:none;">
				<td   style="width:63%;vertical-align:top;border-right:1px solid"><b>Ship To &nbsp;: </b><strong>'.$rel['l_name'].'</strong><br>
				<strong>Address :
				</strong>
				<span style="font-weight:normal;">'.$rel['m_address'].'<br/>'.$rel['city_name'].', '.$rel['state_name'].', '.$rel['country_name'];
				if(!empty($rel['cust_pincode'])){
					$html.='-  '.$rel['cust_pincode'];
				}
				$html.='</span><br>';
				if($company_config['enable_hypothication']==1 && $rel['check_hypothication']!=0){
					$html.='<strong>
					Hypothecation with '.get_id_detail($dbcon,'bank_mst','bankid',$rel['hypo_bank'],'bank_name');
					$html.='</strong><br>';
				} 
			} else { 
				$html.='<table style="font-size:12px; width:100%;" cellpadding="5" cellspacing="5">
				<tr style="border-bottom:none;">
				<td   style="width:63%;vertical-align:top;border-right:1px solid"><b>Consignee &nbsp;: </b><strong>'.$cons_data['company_name'].'</strong><br>
				<strong>Address :
				</strong>
				<span style="font-weight:normal;">'.$cons_data['m_address'].'<br/>'.$cons_data['city_name'].', '.$cons_data['state_name'].', '.$cons_data['country_name'];
				if(!empty($cons_data['cust_pincode'])){
					$html.='-  '.$cons_data['cust_pincode'];
				}
				$html.='</span><br>';
				if($company_config['enable_hypothication']==1 && $rel['check_hypothication']!=0){
					$html.='<strong>
					Hypothecation with '.get_id_detail($dbcon,'bank_mst','bankid',$rel['hypo_bank'],'bank_name');
					$html.='</strong><br>';
				} 





				
			}
	
			$html.='
			<td style="width:37%;vertical-align:top;border-right:1px solid"><strong>Challan No :</strong>'.(($rel['challan_no']) ? $rel['challan_no'] : '').'
			<br><br>
			<strong>Vehicle no :</strong>'.$r_t['transport_vehicle_no'].'
			<br><br>
			<strong>Transporter :</strong>'.$r_t['transportation_name'].'
			</td>
			</tr>
			</table>';
		
		$html.='<table style="font-size:14px;border-collapse: collapse;width:100%;border:1px solid !important;" cellpadding="2" cellspacing="2">
		<thead>
		<tr>
		<td style=" text-align:center;border:1px solid !important; width:5%"><strong>Sr No.</strong> </td>
		<td  style=" text-align:center;border:1px solid !important; "><strong>Product code</strong></td>
		<td  style=" text-align:center;border:1px solid !important; width:25%"><strong>DESCRIPTION</strong></td>
		<td  style=" text-align:center;width:10%;border:1px solid !important;"><strong>HSN<br>Code</strong></td>
		<td colspan="2"  style=" text-align:center;border:1px solid !important; width:7%;"><strong> Qty</strong></td>
		
		
		<td style=" text-align:center;border:1px solid !important; width:9%;"><strong> UOM</strong> </td>
		<td style=" text-align:center;border:1px solid !important; width:8%;"><strong>Price</strong> </td>';
		$html.='<td style="text-align:center;border:1px solid !important;width:7%;"><strong>DISC %</strong></td>';
		$html.='<td style="text-align:center;border:1px solid !important; width:6%;"><strong>GST %</strong></td>
		<td style="text-align:center;border:1px solid !important; width:10%;"><strong>AMOUNT</strong> '.$rel['currency_code'].'</td>
		</tr>
		</thead>
		';
		
		$qry="SELECT trn.*, product.product_icode, product.product_name,product.product_icode, dr.drawing_number, product.product_alias_name, per.unit_name,cper.unit_name as conv_unit FROM `tbl_invoicetrn` as trn 
	left join product_mst as product on product.product_id=trn.product_id 
	left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
	left join unit_mst as per on per.unitid=trn.unit_id
	left join unit_mst as cper on cper.unitid=trn.conv_unit_id
	where trn.trancation_status=0 and trn.invoice_id=".$rel['invoice_id']." group by trn.trancation_id order by trn.trancation_id";	
$trn_qry_rs=$dbcon->query($qry);
$p=1;$ttl_amt=0;$ttl_qty=0;$total_cs_gst=0;$total_i_gst=0;$gst_per=0;$gst_rate=0;
$cnt=brp_mysqli_num_rows($trn_qry_rs);
$item_code = '';
while($trn_rel=brp_mysqli_fetch_array($trn_qry_rs)){
	$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
	$gst_rate = $trn_rel['cgst_tax_rate_conv']+$trn_rel['sgst_tax_rate_conv']+$trn_rel['igst_tax_rate_conv'];
	
	if($trn_rel['cgst_tax_rate_conv'] != 0 || $trn_rel['sgst_tax_rate_conv'] !=0){
		 $total_cs_gst += $gst_rate;
			
	}else{
		 $total_i_gst += $gst_rate;
			
	}

	if(in_array('drawing',$pro_search)){
		$drawing_number = " -- (".$trn_rel['drawing_number'].")";
	}
	
	if(in_array('item',$pro_search)){
		$item_code = $trn_rel['product_icode'];
	}
	if(in_array('alias',$pro_search)){
		$alias = "(".$trn_rel['product_alias_name'].")";
	}

	   
	
	if($trn_rel['unit_id']===$trn_rel['conv_unit_id']){
		if($trn_rel['unit_id']===$trn_rel['rate_unit']){
			$sqty=number_format($trn_rel['product_qty'],3,".","");
			$pro_qty = $trn_rel['product_qty'];
			$unit_name = $trn_rel['unit_name'];
			$rate_unit = $trn_rel['unit_name'];
		}else{
			$sqty=number_format($trn_rel['product_conv_qty'],3,".","");
			$pro_qty = $trn_rel['product_conv_qty'];
			$unit_name = $trn_rel['conv_unit'];
			$rate_unit = $trn_rel['conv_unit'];
		} 
	}else{
		if($trn_rel['unit_id']===$trn_rel['rate_unit']){
			$sqty=number_format($trn_rel['product_qty'],3,".","").'<br>'.number_format($trn_rel['product_conv_qty'],3,".","");
			$pro_qty = $trn_rel['product_qty'];
			$unit_name = $trn_rel['unit_name'].'<br>'.$trn_rel['conv_unit'];
			$rate_unit = $trn_rel['unit_name'];	
		}else{
			$sqty=number_format($trn_rel['product_conv_qty'],3,".","").'<br>'.number_format($trn_rel['product_qty'],3,".","");
			$pro_qty = $trn_rel['product_conv_qty'];
			$unit_name = $trn_rel['conv_unit'].'<br>'.$trn_rel['unit_name'];
			$rate_unit = $trn_rel['conv_unit'];
		}
	}
	
	$item_code = $trn_rel['product_icode'];
	$path='view/upload/product_images/';
	$html.='
	<tr style="border:none;">
	<td style="text-align:center;font-size:12px;border:1px solid !important;border-bottom:none !important;width:5%">'.$p.' </td>
	<td  style=" text-align:center;border:1px solid !important; "><strong>'.$item_code.'</strong></td>

	<td style="border:1px solid !important;border-bottom:0px solid;text-align:left;font-size:12px;border-bottom:none !important; width:25%">'.$trn_rel['product_name'].' ';
	
	$disc_rate = ($trn_rel['product_rate_conv']*$trn_rel['discount_per'])/100;
	$net_rate = $trn_rel['product_rate_conv']-$disc_rate;

	$html .='</td>
	<td style=" text-align:center;font-size:12px;border:1px solid !important;border-bottom:none !important;width:10%;">'.$trn_rel['product_hsn_code'].'</td>
	<td colspan="2" style=" text-align:center;font-size:12px;border:1px solid !important;border-bottom:none !important;width:10%;">'.$sqty.'</td>
	
	<td style=" text-align:center;font-size:12px;border:1px solid !important;width:8%;border-bottom:none !important;">'.$unit_name.'   </td>
	<td style=" text-align:right;font-size:12px;border:1px solid !important;width:8%;border-bottom:none !important;">'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($net_rate).'  </td>';
		
	
	$html.='<td style=" text-align:center;border:1px solid !important;width:7%;">'.without_comma_two_digit_amount($trn_rel['discount_per']).' %</td>';
		
		$html.='<td style=" text-align:right;font-size:12px;border:1px solid !important;width:6%;border-bottom:none !important;">'.$gst_per.'%</td>
		<td style=" text-align:right;font-size:12px;border:1px solid !important;width:10%;border-bottom:none !important;">'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($trn_rel['product_amount_conv']).'</td>
		</tr>
		';
		
	if($product_desc!=""){
		$html.='<div style="border:1px solid !important;border-top: none !important;font-size:12px;">'.$product_desc.'</div>';    
	}
	
	$ttl_qty=$ttl_qty+$pro_qty;
	if($trn_rel['act_amt_flag']!='1'){
	//$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
		$ttl_amt=$ttl_amt+$trn_rel['product_amount_conv'];
	}
	$p++;
}
$pr=8-$cnt;
			for($j=0; $j<$pr; $j++)
			{
				$html.='<tr style="border:none;">
				<td style="text-align:center;font-size:12px;border:1px solid !important;border-bottom:none !important;width:5%"></td>
				<td  style=" text-align:center;border:1px solid !important; "></td>
				<td style="border:1px solid !important;border-bottom:none !important;text-align:left;font-size:12px;width:25%"></td>
				<td style="text-align:center;font-size:12px;border:1px solid !important;border-bottom:none !important;width:10%;"></td>
				<td colspan="2" style="text-align:center;font-size:12px;border:1px solid !important;border-bottom:none !important;width:10%;"></td>
				<td style="text-align:right;font-size:12px;border:1px solid !important;width:8%;border-bottom:none !important;"></td>
				<td style="text-align:center;font-size:12px;border:1px solid !important;width:8%;border-bottom:none !important;"></td>
				<td style=" text-align:center;border:1px solid !important;border-bottom:none !important;width:7%;"></td>
				<td style="text-align:right;font-size:12px;border:1px solid !important;width:6%;border-bottom:none !important;"></td>
				<td style="text-align:right;font-size:12px;border:1px solid !important;width:10%;border-bottom:none !important;"></td>
			</tr>	
			
				';
				
				
			}


$html.='</table>
';
//<!--<tr>
	// <td colspan="11" style="height:25px">The Material Should Be Sent As Per The Qty in Nos Mentioned In Purchase Order. In Case Of Variable UOM <br> i.e. Kgs. / Ltr / Mtr / Feet etc., The Amount Of Purchase Order Will Be Approximate And It Will Be Accepted On Actual Basis.</td>
	// </tr>-->
$html.='
<table style="font-size:14px;border-collapse: collapse;width:100%;border:1px solid !important;" cellpadding="3" cellspacing="3">
<tr style="border-bottom:none;">
<td colspan="3"  style="border-bottom:none;border:1px solid !important; width:70%;"><span style="font-size:13px;"><strong>Words amount in  :</strong> '.convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$currency_code['currency_in_word_end'],$currency_code['currency_in_word']).'</span></td>

<td style=" text-align:right;font-size:12px;border:1px solid !important;width:10%"><strong>Basic</strong>  </td>

<td  style=" text-align:right;font-size:12px;border:1px solid !important;width:10%">'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($ttl_amt,2).'</td>
</tr>
';
$qry11="select sum((tc.tax_per*trn.product_amount_conv)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_invoicetrn as trn 
left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
left join tbl_ledger as l on l.l_id=tc.tax_id 
where tc.tax_additional='1' and trn.invoice_id=".$rel['invoice_id']." and trn.trancation_status!=2 and tc.isdelete='0' group by tc.tax_id";
$result11=$dbcon->query($qry11);		
while($row11=mysqli_fetch_assoc($result11))
{
	$html.='<tr>
	<td  style=" text-align:right;font-size:12px;border:1px solid !important;"></td>
	<td style=" text-align:right;font-size:12px;border:1px solid !important;"><b>'.$row11['l_name'].'</b></td>
	<td style="text-align:center;border:1px solid !important;"><b>
	'.$rel['currency_symbol'].' '.number_format($row11['add_sum'],2,".","").'
	</b></td>
	</tr>';
}
$qry12="select b.sundry_amount_conv,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' and le.default_sundry='0'";
$result12=$dbcon->query($qry12);		
while($row12=mysqli_fetch_assoc($result12))
{
	$html.='<tr>
	<td style=" text-align:right;font-size:12px;border:1px solid !important;"></td>
	<td style=" text-align:right;font-size:12px;border:1px solid !important;"></td>
	<td style=" text-align:right;font-size:12px;border:1px solid !important;">'.$row12['l_name'].'</td>
	<td style="text-align:right;font-size:12px;border:1px solid !important;">'.$rel['currency_symbol'].' '.number_format($row12['sundry_amount_conv'],2,".","").'</td>
	</tr>';
}
if($rel['stateid']=$set_head['stateid']){
	$total_cs_gst = $total_cs_gst + $total_sundrytax;
	$html.='<tr>
	<td  style="border:none !important;"></td>
	<td  style="border:none !important;"></td>
	<td  style="border:none !important;"></td>
	


	<td style=" text-align:right;font-size:12px;border:1px solid !important;"><strong>CGST</strong></td>
	<td  style=" text-align:right;font-size:12px;border:1px solid !important;">'.$rel['currency_symbol'].' '.number_format(($total_cs_gst/2),2,".","").'</td>
	</tr><tr>
	<td style="border:1px solid !important;"></td>

	<td  style="border:1px solid !important;width:40%"><strong>BANK : '.$set_head["bank_name"].'</strong></td>
	<td  style="border:1px solid !important;width:40%"><strong>IFSC CODE : '.$set_head["ifcs"].'</strong></td>
	<td style=" text-align:right;font-size:12px;border:1px solid !important;"><strong>SGST</strong></td>
	<td style=" text-align:right;font-size:12px;border:1px solid !important;">'.$rel['currency_symbol'].' '.number_format(($total_cs_gst/2),2,".","").'</td>
	</tr>';
}else{
$total_i_gst = $total_i_gst + $total_sundrytax;
	$html.='<tr>
	<td  style="border:1px solid !important;"></td>
	<td  style="border:1px solid !important;width:40%"><strong>BANK : '.$set_head["bank_name"].'</strong></td>
	<td  style="border:1px solid !important;width:40%"><strong>IFSC CODE : '.$set_head["ifcs"].'</strong></td>
	<td style=" text-align:right;font-size:12px;border:1px solid !important;"><strong>IGST</strong></td>
	<td style=" text-align:right;font-size:12px;border:1px solid !important;">'.$rel['currency_symbol'].' '.number_format(($total_i_gst),2,".","").'</td>
	</tr>';
}		

//$round_off = round($final_total)-$final_total;

$html.='<tr>
<td style=" text-align:left;font-size:12px;border:1px solid !important;"></td>

<td  style="border:1px solid !important;width:40%"><strong>BRANCH : '.$set_head["branch_name"].'</strong></td>
<td  style="border:1px solid !important;width:40%"><strong>A/C NO : 
'.$set_head["ac_no"].'</strong></td>
<td style=" text-align:right;font-size:12px;border:1px solid !important;"><strong>Round Off</strong></td>
<td style=" text-align:right;font-size:12px;border:1px solid !important;">
'.indian_number($rel['round_off_conv'],2).'

</td>
</tr>';
$html .= '
<tr>
<td style=" text-align:left;font-size:12px;border:1px solid !important;"></td>
<td style=" text-align:left;font-size:12px;border:1px solid !important;"></td>
<td style=" text-align:left;font-size:12px;border:1px solid !important;"></td>

<td style=" text-align:right;font-size:13px;border:1px solid !important;"><strong>GrandTotal</strong></td>
<td style=" text-align:right;font-size:13px;border:1px solid !important;">'.$rel['currency_symbol'].' '.($rel['g_total_conv']).' </td>
</tr>';

$html.='</tbody></table>';
$html.='<table style="font-size:14px;border-collapse: collapse;width:100%;border:1px solid !important;" cellpadding="2" cellspacing="2">
<thead>
<tr>
<td colspan="2"  style=" text-align:left;border:1px solid !important; width:7%;"><strong> Terms & Conditions : </strong>
';
if(!empty($set_head['conditions'])){
	$html.='<br>'.$set_head['conditions'];
}
$html.='</td>
</tr>
<tr style="border-bottom:none;">

<td   style="height:130px;vertical-align: top; text-align:right;border:1px solid !important;border-bottom:none !important;border-right:none !important;width:50%">&nbsp;</td>
<td   style="height:130px;vertical-align: top; text-align:right;border:1px solid !important;border-bottom:1px solid black !important;border-left:none !important;width:50%"><strong>For INVOIT PLAST MACHINERY PRIVATE LIMITED</strong></td>
</tr>
<tr style="border-top: none;">
<td style="vertical-align: top; text-align:left;border:1px solid !important; width:50%;border-right:none !important;"><strong>Receivers sign with stemp</strong></td>
<td   style="vertical-align: top; text-align:right;border-left:none !important;border-top:1px solid black;  width:50%;"><strong>Authoriser Signatory

</strong></td>

</tr>
<tr style="border-bottom: none;">
<td colspan="2" style="vertical-align: top; text-align:left;border:1px solid !important; width:50%;border-right:none !important;"><strong>We declare that this invoice shows the actual price of the goods described and that all particulars are true and
	correct</strong></td>


</tr>
<tr style="border-top: none;">
<td colspan="2" style="vertical-align: top; text-align:center;border:1px solid !important; width:50%;border-right:none !important;"><strong>SUBJECT TO AHMEDABAD JURISDICTION<br>
	This is a Computer Generated Invoice</strong></td>


</tr>
</table>';

		//  echo $html;die;
		// $get = "hello";
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','35','10','1','1');
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