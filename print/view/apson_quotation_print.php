<?php 
$quotation_id = $_REQUEST['id'];	
if(!empty($quotation_id)){
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
	include("../../include/function_database_query.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	$incPath = $path.'include/';

	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		QUOTATION_SLUG_PRINT,
	]);

	if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	echo quotation_print($dbcon,$quotation_id,$save_file = "No");
}
function quotation_print($dbcon,$quotation_id,$save_file){
	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
	$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
	$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';

// $quotation_id = $_REQUEST['id'];	
	$type='pdf';
	if(strtolower($type) == 'pdf') {
//Quotation Data
		 $query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name,cust.cust_gst, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name, cadd.c_add_state,usa.user_name,cust.ledger_id,payt.payment_terms from tbl_quotation as quot
		left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
		left join tbl_customer as cust on cust.cust_id=quot.cust_id
		left join tbl_cust_address as cadd on cadd.cust_id=quot.cust_id
		left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
		left join tbl_refer_by as ref on cust.cust_source=ref.rb_id
		left join users as usa on usa.user_id=quot.user_id
		left join pay_terms as payt on payt.terms_id=quot.payment_terms_id
		where quot.quotation_id=".$quotation_id;
		$rel=mysqli_fetch_assoc($dbcon->query($query));

		if($rel['ledger_id']==0){
			$execust="No";
		}else{
			$execust="Yes";
		}
//p($rel);

		if(!$rel){
			header("Location: ".ROOT.CRM_ROOT."quotation_list");
		}

		if($rel['quot_type']=='0'){
			$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`=68 ';
			$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));
			$currency_name = '(INR)';
			$currency_name = 'INR';
			$currency_word_start = 'Rupees';
			$currency_word_end = 'Paise';
			$currency_symbol = $currency_rel['currency_symbol'];
		}else{
			$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
			$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

//			$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
			$currency_name = ucfirst(strtolower($currency_rel['currency_code']));
			$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
			$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
			$currency_symbol = $currency_rel['currency_symbol'];
		}
		$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';
		$phone = ($rel['c_con_mobile'] == '') ? '' : '+91' . $rel['c_con_mobile'];
		$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];

		$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
		$header ='<div style="text-align:center;padding-top:15px"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:8.27in;" /></div>';
		$footer ='<div style="text-align:center;"><img src="'.DOMAIN_F.LOGO.$comp_rel["f_logo"].'" style="width:8.27in;" /></div>';
		$approve_status='';
		if($rel['approve_status']=='0'){
			$approve_status=' (DRAFT)';
		}
		$inquiry_type=$rel['inquiry_type'];
//Amish Soni Start 16-03-2021
		$companySettings = getCompanySettings($dbcon);
		$companyConfiguration=getCompanyConfiguration($dbcon);
		$sales_pro_search=explode(",", $companyConfiguration['sales_pro_search']);

		if($companySettings) {
			$quotation_print_content = $companySettings['quotation_print_content'] ? $companySettings['quotation_print_content'] : '';
			$quotation_print_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_print_content);
			$quotation_footer_content = $companySettings['quotation_footer_content'] ? $companySettings['quotation_footer_content'] : $quotation_footer_content;
			$quotation_footer_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_footer_content);
		}
		$disc_qry=$dbcon->query("SELECT SUM(trn.product_discount_conv) as discount from tbl_quotation_trn as trn where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id']);
		$disc_qrys = brp_mysqli_fetch_assoc($disc_qry);
		if($companyConfiguration['quot_revise_time_rate_with_discount'] == 0){
			$colspan =($disc_qrys['discount'] > 0) ? 5 : 4;	
		}else{
			$colspan = 4;
		}
	
//Amish Soni End 16-03-2021
		$html ='<html>
		<head>					
		<title>Quotation</title>
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
			
			.quot_annex_content_div table tr,td{
				padding:3px;
			}
			.blueHeading {
				background-color: #a7adb5;
			}

			</style>
			</head>
			<body>
			<!--Show Logo in other pages-->
			<htmlpageheader name="otherpages" style="display:none">
			<div>
			<table style="font-size:14px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<tr>
					<td class="blueHeading" style="text-align:Center;font-size:14px;border:1px solid"><strong>QUOTATION</strong></td>
					
				</tr>
			</table>
			<table style="font-size:9px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<tr>
					<td rowspan="0" colspan="5" style="text-align:left;vertical-align:top;border-left:1px solid;border-top:1px solid;width:50%;"> 
						<strong><span><u>SUPPLIER</u></span></strong><br/>
						<strong>'.$comp_rel['company_name'].'</strong>,<br/>
						'.strtoupper($comp_rel["address"]).'
						
					</td>
					<td rowspan="0" colspan="6" style="text-align:left;vertical-align:top;border-left:1px solid;border-right:1px solid;border-top:1px solid;width:50%; "> 
						<strong><span><u>BUYER</u></span></strong><br/>
						<strong>'.$rel['cust_name'].'</strong>,<br/>'.strtoupper($quot_address).'
					</td>
				</tr>
				<tr>
					<td rowspan="0" colspan="5" style="text-align:left;vertical-align:top;border-left:1px solid;"> 
						PH: '.$comp_rel["contact_no"].' | E: '.$comp_rel["website"].'
					</td>
					<td rowspan="0" colspan="6" style="text-align:left;vertical-align:top;border-left:1px solid;border-right:1px solid; "> 
						PH: '.$phone.' | E: '.$rel['c_con_email'].'
					</td>
				</tr>
				<tr>
					<td rowspan="0" colspan="5" style="text-align:left;vertical-align:top;border-left:1px solid; "> 
						GST NO.: '.$comp_rel["vatno"].'
					</td>
					<td rowspan="0" colspan="6" style="text-align:left;vertical-align:top;border-left:1px solid;border-right:1px solid; "> 
						GST NO.: '.$rel["cust_gst"].'
					</td>
				</tr>
				<tr>
					<td colspan="2" class="blueHeading" style="text-align:left;border:1px solid;width:16%;"> 
						INQUIRY <br>SOURCE
					</td>
					<td style="text-align:left;border:1px solid;width:16%;"> 
						'.$rel["rb_name"].'
					</td>
					<td colspan="2" class="blueHeading" style="text-align:left;border:1px solid;width:18%;"> 
						EXISTING <br> CUSTOMER
					</td>

					<td colspan="3" style="text-align:left;border:1px solid;width:16%;"> 
						'.$execust.'
					</td>
					<td colspan="2" class="blueHeading" style="text-align:left;border:1px solid;width:16%;"> 
						CUSTOMER <br> NAME
					</td>
					<td style="text-align:left;border:1px solid;width:18%;"> 
						'.$rel['c_con_fname'].' '.$rel['c_con_lname'].'
					</td>
				</tr>
				<tr>
					<td colspan="2" class="blueHeading" style="text-align:left;border:1px solid;"> 
						REPRESENTATIVE <br>NAME
					</td>
					<td style="text-align:left;border:1px solid;"> 
						<strong>'.$rel['user_name'].'</strong>
					</td>
					<td colspan="2" class="blueHeading" style="text-align:left;border:1px solid;"> 
						QUOTATION DATE
					</td>
					<td colspan="3" style="text-align:left;border:1px solid;"> 
						'.date("d/m/Y",strtotime($rel['quotation_date'])).'
					</td>
					<td colspan="2" class="blueHeading" style="text-align:left;border:1px solid;"> 
						QUOTATION <br>NO.
					</td>
					<td style="text-align:left;border:1px solid;"> 
						'.$rel['quotation_no'].'
					</td>
				</tr>
				</table>
				<table style="font-size:9px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<tr>
					<td class="blueHeading" style="text-align:center;border:1px solid;width:5%;">
						Sr.<br/>No.
					</td>
					<td  class="blueHeading" style="text-align:center;border:1px solid;width:35%;">
						PRODUCT DESCRIPTION
					</td>
					<td  class="blueHeading" style="text-align:center;border:1px solid;width:10%;">
						HSN CODE
					</td>

					<td class="blueHeading" style="text-align:center;border:1px solid;width:10%;">
						MOQ 
					</td>
					<td class="blueHeading" style="text-align:center;border:1px solid;width:10%;">
						UNIT 
					</td>
					<td class="blueHeading"  style="text-align:center;border:1px solid;width:15%;">
						AESSABLE <br/>VALUE PER UNIT 
					</td>
					<td class="blueHeading"  style="text-align:center;border:1px solid;width:15%;">
						TOTAL AMOUNT 
					</td>
					
				</tr>';
				if($inquiry_type!="2"){
					$trn_qry="SELECT trn.*,pro.product_name,unit.unit_name, pro.product_icode,hsn.hsn_code FROM tbl_quotation_trn as trn 
				   left join product_mst as pro on pro.product_id=trn.product_id
				   left join unit_mst as unit on unit.unitid=trn.unitid
				   left join mst_hsn_code as hsn on hsn.hsn_id=pro.product_hsn
				   where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
			   } else {
					 $trn_qry="SELECT trn.* , pro.product_name,pro.product_icode FROM `tbl_quotation_project_trn` as trn 
					 left join product_mst as pro on pro.product_id = trn.product_id 
				   where trn.quotation_projecttrn_status=0 and trn.quotation_id =".$rel['quotation_id'];
			   }
			   $trn_qry_rs=$dbcon->query($trn_qry);
			   $p=1;$ttl_amt=0;$ttl_qty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;
			   $cnt=mysqli_num_rows($trn_qry_rs);
			   while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
				   $item_code = '';
				   if(in_array('item',$sales_pro_search)){
					   $item_code = " -- (".$trn_rel['product_icode'].")";
				   }
				   $product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';
   
				   $html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
							   <td  style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
							   <td style="text-align:left;border:1px solid;vertical-align:top;">
								   <strong>'.$trn_rel['product_name'].'</strong>'.$item_code.'<br/>'.$product_desc.'
							   </td>
							   <td style="text-align:center;border:1px solid;vertical-align:top;">
								   '.$trn_rel['hsn_code'].'
							   </td>
							   <td style="text-align:center;border:1px solid;vertical-align:top;">
								   '.$trn_rel['product_qty'].'
							   </td>
							   <td style="text-align:center;border:1px solid;vertical-align:top;">
								   '.$trn_rel['unit_name'].'
							   </td>
							   <td style="text-align:center;border:1px solid;vertical-align:top;">
								   '.$currency_symbol.' '.indian_number($trn_rel['product_rate_conv'],2).'
							   </td>
							   <td style="text-align:right;border:1px solid;vertical-align:top;">
								   '.$currency_symbol.' '.indian_number($trn_rel['product_amount_conv'],2).'
							   </td>';
   
				   $html.='</tr>';
				   $ttl_qty=$ttl_qty+$trn_rel['product_qty'];
				   $ttl_amt=$ttl_amt+$trn_rel['product_amount_conv'];
				   $p++;
			   }
			   $pr=10-$cnt;
			   $html.='<tr>
						   <td colspan="3" style="text-align:right;border:1px solid;"><b>TOTAL</b></td>
						   <td style="text-align:center;border:1px solid;">
							   <b>
								   '.$currency_symbol.' '.indian_number($ttl_qty,2).'
							   </b>
						   </td>
						   <td colspan="2" style="text-align:right;border:1px solid;"></td>
						   <td style="text-align:right;border:1px solid;">
							   <b>
								   '.$currency_symbol.' '.indian_number($ttl_amt,2).'
							   </b>
						   </td>
					   </tr>
			</table>';
			$subtotal=$ttl_amt;
			$trn_qrytax="SELECT sum(cgst_tax_rate_conv) as cgsttax,sum(sgst_tax_rate_conv) as sgsttax,sum(igst_tax_rate_conv) as igsttax,igst_tax_per,cgst_tax_per,sgst_tax_per FROM tbl_quotation_trn as trn 
				 	  where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
			$trn_qry_rstax=$dbcon->query($trn_qrytax);
			$trn_reltax=mysqli_fetch_assoc($trn_qry_rstax);
			$cgsttax=$trn_reltax['cgsttax'];
			$sgsttax=$trn_reltax['sgsttax'];
			$igsttax=$trn_reltax['igsttax'];
			if($cgsttax>0){
				 $dived=1;
			}else{
				 $dived=0;
			}

			$trn_qrybill="SELECT sundry_amount_conv,sundry_gst_amount_conv,currency_id FROM tbl_bill_sundry_transaction as trn 
				 	  where trn.isdelete=0 and sundry_ledger_id='2' and sundry_voucher_table='tbl_quotation' and trn.sundry_voucher_id=".$rel['quotation_id'];
			$trn_qry_rsbill=$dbcon->query($trn_qrybill);
			$trn_bill=mysqli_fetch_assoc($trn_qry_rsbill);
			if($dived == 1){
				
				if ($trn_bill['currency_id'] == 68)
				{

					$pcgst=$trn_bill['sundry_gst_amount_conv']/2;
					$cgsttax=$cgsttax+$pcgst;
					$sgsttax=$sgsttax+$pcgst;
				}
			}else{
				if ($trn_bill['currency_id'] == 68)
				{
					$igsttax=$igsttax+$trn_bill['sundry_gst_amount_conv'];
				}

			}

			$trn_qrybill1="SELECT sundry_amount_conv,sundry_gst_amount_conv FROM tbl_bill_sundry_transaction as trn 
				 	  where trn.isdelete=0 and sundry_ledger_id='3' and sundry_voucher_table='tbl_quotation' and trn.sundry_voucher_id=".$rel['quotation_id'];
			$trn_qry_rsbill1=$dbcon->query($trn_qrybill1);
			$trn_bill1=mysqli_fetch_assoc($trn_qry_rsbill1);
			if($dived == 1){
				if ($trn_bill['currency_id'] == 68)
				{
				
					$pcgst=$trn_bill1['sundry_gst_amount_conv']/2;
					$cgsttax=$cgsttax+$pcgst;
					$sgsttax=$sgsttax+$pcgst;
				
				}
			}else{
				if ($trn_bill['currency_id'] == 68)
				{
					$igsttax=$igsttax+$trn_bill1['sundry_gst_amount_conv'];
				}
			}

			$trn_qrybill2="SELECT sundry_amount_conv,sundry_gst_amount_conv FROM tbl_bill_sundry_transaction as trn 
				 	  where trn.isdelete=0 and sundry_ledger_id='5' and sundry_voucher_table='tbl_quotation' and trn.sundry_voucher_id=".$rel['quotation_id'];
			$trn_qry_rsbill2=$dbcon->query($trn_qrybill2);
			$trn_bill2=mysqli_fetch_assoc($trn_qry_rsbill2);
			if($dived == 1){
				if ($trn_bill['currency_id'] == 68)
				{
					$pcgst=$trn_bill2['sundry_gst_amount_conv']/2;
					$cgsttax=$cgsttax+$pcgst;
					$sgsttax=$sgsttax+$pcgst;
				}
			}else{
				if ($trn_bill['currency_id'] == 68)
				{
					$igsttax=$igsttax+$trn_bill2['sundry_gst_amount_conv'];
				}
			}
			$subtotal=$subtotal+$trn_bill['sundry_amount_conv']+$trn_bill1['sundry_amount_conv']+$trn_bill2['sundry_amount_conv'];
			if(!empty($trn_bill['sundry_amount_conv'])){
				$b1per=(($trn_bill['sundry_amount_conv']*100)/$ttl_amt);
			}else{
				$b1per='0.00';
			}
			
			if(!empty($trn_bill1['sundry_amount_conv'])){
				$b1per1=(($trn_bill1['sundry_amount_conv']*100)/$ttl_amt);
			}else{
				$b1per1='0.00';
			}
			
			if(!empty($trn_bill2['sundry_amount_conv'])){
				 $b1per2=(($trn_bill2['sundry_amount_conv']*100)/$ttl_amt);
			}else{
				$b1per2='0.00';
			}
			$html.='<table style="font-size:9px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
			   <tr>
			   		<td class="blueHeading" style="border:1px solid;width:50%;">PAYMENT TERMS</td>
			   		<td class="blueHeading" style="border:1px solid;width:20%;">PACKING CHARGE</td>
			   		<td style="border:1px solid;width:15%;text-align:Center;">'.indian_number($b1per,2).' %</td>';

			   		if($trn_bill['sundry_amount_conv'] == 0)
			   		{

			   		$html.='<td style="border:1px solid;width:15%;text-align:right;">'.$currency_symbol.' 0.00</td>';

			   		}
			   		else
			   		{

			   		$html.='<td style="border:1px solid;width:15%;text-align:right;">'.$currency_symbol.' '.indian_number($trn_bill['sundry_amount_conv'],2).'</td>';
			   		}
			   $html.='</tr>
			   <tr>
			   		<td  style="border:1px solid;">'.$rel['payment_terms'].'</td>
			   		<td class="blueHeading" style="border:1px solid;">FORWORDING CHARGE</td>
			   		<td style="border:1px solid;width:15%;text-align:Center;">'.indian_number($b1per1,2).' %</td>';

			   		if($trn_bill1['sundry_amount_conv'] == 0)
			   		{

			   		$html.='<td style="border:1px solid;width:15%;text-align:right;">'.$currency_symbol.' 0.00</td>';

			   		}
			   		else
			   		{
			   		$html.='<td style="border:1px solid;text-align:right;">'.$currency_symbol.' '.indian_number($trn_bill1['sundry_amount_conv'],2).'</td>';
			   		}
			   $html.='</tr>
			   <tr>
					<td class="blueHeading" style="border:1px solid;">DELIEVERY TIME	</td>
					<td class="blueHeading" style="border:1px solid;">OTHER</td>
					<td style="border:1px solid;width:15%;text-align:Center;">'.indian_number($b1per2,2).' %</td>';

			   		if($trn_bill2['sundry_amount_conv'] == 0)
			   		{

			   		$html.='<td style="border:1px solid;width:15%;text-align:right;">'.$currency_symbol.' 0.00</td>';

			   		}
			   		else
			   		{
			   		$html.='<td style="border:1px solid;text-align:right;">'.$currency_symbol.' '.indian_number($trn_bill2['sundry_amount_conv'],2).'</td>';
			   		}
			   $html.='</tr>
				<tr>
				  <td style="border:1px solid;">'.$rel['delivary_time_apson'].'</td>
				  <td class="blueHeading" style="border:1px solid;">SUB TOTAL</td>
				  <td style="border:1px solid;"></td>
				  <td style="border:1px solid;text-align:right;">'.$currency_symbol.' '.indian_number($subtotal,2).'</td>
				</tr>
				<tr>
				  <td class="blueHeading" style="border:1px solid;">QUOTATION VALIDITY</td>
				  <td class="blueHeading" style="border:1px solid;">IGST</td>
				  <td style="border:1px solid; text-align:Center;">'.$trn_reltax["igst_tax_per"].'.00 %</td>
				  <td style="border:1px solid;text-align:right;">'.$currency_symbol.' '.indian_number($igsttax,2).'</td>
				</tr>
				<tr>
				  <td style="border:1px solid;">'.date("d/m/Y",strtotime($rel['quotation_valid_date'])).'</td>
				  <td class="blueHeading" style="border:1px solid;">SGST</td>
				  <td style="border:1px solid; text-align:Center;">'.$trn_reltax["sgst_tax_per"].'.00 %</td>
				  <td style="border:1px solid;text-align:right;">'.$currency_symbol.' '.indian_number($sgsttax,2).'</td>
				</tr>
				<tr>
				  <td class="blueHeading" style="border:1px solid;">DISPATCH FROM</td>
				  <td class="blueHeading" style="border:1px solid;">CGST</td>
				  <td style="border:1px solid; text-align:Center;">'.$trn_reltax["cgst_tax_per"].'.00 %</td>
				  <td style="border:1px solid;text-align:right;">'.$currency_symbol.' '.indian_number($cgsttax,2).'</td>
				</tr>
				<tr>
				  <td style="border:1px solid;">EX WORKS, APSON EMPIRE - MIRZAPUR, KHEDA</td>
				  <td class="blueHeading" style="border:1px solid;">TOTAL PAYABLE AMOUNT</td>
				  <td style="border:1px solid;"></td>
				  <td style="border:1px solid;text-align:right;">'.$currency_symbol.' '.indian_number($rel["g_total_conv"],2).'</td>
				</tr>
				<tr>
				  <td colspan="4" style="border:1px solid; text-transform: uppercase;"><strong>TOTAL AMOUNT IN WORDS :'.$currency_name.' '.convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$rel['currency_in_word_end'],$rel['currency_in_word']).'</strong></td>
				  
				</tr>
			</table>
			<table style="font-size:9px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
			   <tr> 
			   	 	<td style="border-left:1px solid;border-right:1px solid;border-top:1px solid;vertical-align: top;text-align: right;height: 60px;">FOR,'.$comp_rel['company_name'].'</td>
				</tr>
				<tr> 
					<td style="border-left:1px solid;border-right:1px solid;border-bottom:1px solid;vertical-align: top;text-align: right;">AUTHORISED SIGNATURE</td>
				</tr>
			</table>';
			$html.='<table style="font-size:9px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
					<tr>
						<td style="border:1px solid;">
							<strong>BANK DETAILS FOR PAYMENT</strong><br/>
							<strong>BENIFICIERY NAME :</strong> APSON MOTOR (INDIA) PVT. LTD.<br/>
							<strong>BANK NAME :</strong> ICICI BANK LIMITED<br/>
							<strong>BRANCH :</strong> BAKROL, AHMEDABAD<br/>
							<strong>ACCOUNT NO. :</strong> 777805500150<br/>
							<strong>IFSC CODE :</strong> ICIC0007778 ----- (0 – ZERO)
						</td>
					</tr>
				</table>
				';

				$html.='<table style="font-size:9px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
					<tr>
						<td style="border:1px solid;"><strong>TERMS & CONDITIONS :</strong><br/>'.$rel['quot_general_terms_condition_content'].'</td>
					</tr>
				</table>';
				if(1==2){
			$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
	left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
	where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
	$terms_qry_rs=$dbcon->query($terms_qry);$tc = 1;
	if(mysqli_num_rows($terms_qry_rs)){
		$html.='
	<div>
	
		<table style="font-size:9px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
			<thead>
			<tr style="border: 0; ">
				<td style="border: 0; padding-top: 20px; width : 100%;" colspan="3">
				<center><strong>TERMS & CONDITIONS</strong></center>
				</td>
			</tr>
			<tr>
				<th style="text-align:center; border:1px solid;">Sr.<br/>No.</th>
				<th style="text-align:left; border:1px solid;">Terms and Condition</th>
				<th style="text-align:left; border:1px solid;">Description</th>
			</tr>
			</thead><tbody>';
		while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
			$string=(nl2br($term_rel['tc_details']));
			
			$html.='<tr>
				<td width="5%" style="width:5%;text-align:center;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;">'.$tc.'</td>
				<td width="25%" style="width:25%;text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;">'.$term_rel['tc_name'].'</td>
				<td width="70%" style="width:70%;text-align:left;border:1px solid #000;padding:5px;">'.$string.'</td>
				</tr>';
				
			$tc++;
		}
		$html.='
		</table>';	
		$html.='</div>';
	}
}
			$html.='</div>
	</body>
</html>';
 //echo $trn_qry;
 //echo $html;
 //die();
			//echo $html;exit;
			
			ob_end_clean();
			$file_name = $rel['quotation_no'].'.pdf';
			$file_name=str_ireplace("/","_",$file_name);
			if($save_file=="No"){
				include("../../view/export/mpdf/mpdf.php");
			}else{
				include("../../../view/export/mpdf/mpdf.php");
			}
			$mpdf=new mPDF('','A4','0','calibri','10','10','25','25','1','1');
//		$mdf->SetFont('ProximaNova');
			$mpdf->defaultheaderfontsize = 10; /* in pts */
			$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
			$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
			$mpdf->defaultfooterfontsize = 10; /* in pts */
			$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
			$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
			$mpdf->SetHTMLHeader($header);
			$mpdf->SetHTMLFooter($footer);
//Show page number : Dimple Panchal (05-Apr-2021)
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
			if($save_file=="No"){
				$mpdf->Output();
			}else{
				$mpdf->Output('../../../view/upload/mail_attach/'.$file_name,'f');
			}
			ob_clean();
			return $file_name;
		}
	}
?>