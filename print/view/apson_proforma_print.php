<?php 

session_start();
// var_dump(123);
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");

$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	ORDER_ACCEPTANCE_SLUG_PRINT,
]);

/*if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}*/

$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);	
$type='pdf';
if(strtolower($type) == 'pdf') {
//Quotation Data
 	 $query="select invoice.*,country.country_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust.cust_pincode,cust.cust_mobile,cust.common_email_id,cust.gst_no,cust.m_pan,per.cust_contact_person_name,per.cust_contact_person_no,per.cust_contact_person_email,state.gst_state_code,td.transportation_name,cust.cust_cont_name, cust.stateid,terms.payment_terms,usa.user_name,quo.quotation_no,aps.dilivary_type_name from tbl_sales_order as invoice 
	 left join pay_terms as terms on terms.terms_id=invoice.payment_terms
	left join tbl_ledger as cust on cust.l_id=invoice.cust_id
	left join tbl_cust_contact_person as per on per.cust_contact_person_id = invoice.kind_attn
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid
	left join city_mst as city on city.cityid=cust.cityid
	left join transportation_details as td on td.id=invoice.transid
	left join users as usa on usa.user_id=invoice.user_id
	left join tbl_quotation as quo on quo.quotation_id=invoice.quotation_id
	left join tbl_apson_dilivary_type as aps on aps.dilivary_type_id=invoice.apson_dilivary_type
	where invoice.sales_order_id=$invoiceid";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$delivery_type = $rel['delivery_type'];
	$dilivarytype=$rel['dilivary_type_name'];
	$transportation_name = ($rel['transportation_name']!='0')?$rel['transportation_name']:'';
	$po_date = '';
	if($rel['po_date']!="1970-01-01 00:00:00" && $rel['po_date']!="0000-00-00 00:00:00")
	{
		$po_date=date('d-m-Y',strtotime($rel['po_date']));
	}
	if($rel['apson_validity_date']!="1970-01-01" && $rel['apson_validity_date']!="0000-00-00" && $rel['apson_validity_date']!="")
	{
		$apson_validity_date=date('d-m-Y',strtotime($rel['apson_validity_date']));
	}
		$so_date = '';
	if($rel['sales_order_date']!="1970-01-01 00:00:00" && $rel['sales_order_date']!="0000-00-00 00:00:00")
	{
		$so_date=date('d-m-Y',strtotime($rel['sales_order_date']));
	}
	$delivery_date = '';
	if($rel['delivery_date']!="1970-01-01" && $rel['delivery_date']!="0000-00-00")
	{
		$delivery_date=date('d-m-Y',strtotime($rel['delivery_date']));
	}
//echo "<pre>";print_r($rel);die();
	if(!$rel){
		header("Location: ".ROOT.CRM_ROOT."order_acceptance_list");
	}

	$HowManyWeeks = (strtotime( $rel['cdate'] ) - strtotime( $rel['sales_order_date'])) / 604800;
	$HowManyWeeks = round($HowManyWeeks);
	$HowManyWeeks = ($HowManyWeeks=='0')?'0':$HowManyWeeks;
	$delivery_week = $HowManyWeeks .' - '. ($HowManyWeeks+1) . ' WEEKS';

	$order_by = ($rel['order_by']!='0')?$rel['order_by']:"";
	$phone = ($rel['cust_contact_person_no'] == '') ? '' : '+91' . $rel['cust_contact_person_no'];
	$party_address_billing="<strong>".$rel['company_name']."</strong>
	<span style='font-weight:normal;'> <br/>
	".$rel['cust_address'].",<br/>
	".$rel['cust_pincode']."
	".$rel['city_name'].",
	".$rel['state_name'].",
	".$rel['country_name']."</span><br/>
		PH: ".$phone." ;|
		E: ".strtolower($rel['cust_contact_person_email'])."
		<br>  GSTIN : ".$rel['gst_no'];

	if($rel['consignee_id']==0){
		$contact_person = $rel['cust_cont_name'];
		$party_address_con=$party_address_billing;
		
	}else{
		$query_con="select cust.cust_name,cust.company_name,cust.cust_address,cust.cust_mobile,cust.cust_email,cust.cust_pincode,cust.gst_no,country.country_name,state.state_name,city.city_name,state.gst_state_code from tbl_custmer_consignee as cust 
		left join country_mst as country on country.countryid=cust.countryid
		left join state_mst as state on state.stateid=cust.stateid
		left join city_mst as city on city.cityid=cust.cityid
		where cust_id=".$rel['consignee_id'];
		$rel_con=brp_mysqli_fetch_assoc($dbcon->query($query_con));	
		$cpincode="";
		if(!empty($rel_con['cust_pincode'])){
			$cpincode="- ".$rel_con['cust_pincode'];
		}
		$contact_person = $rel_con['cust_name'];

		$party_address_con="<strong>".$rel_con['company_name']."</strong>
		<span style='font-weight:normal;'> <br/>
		".$rel_con['cust_address'].",<br/>
		".$rel_con['cust_pincode']."
		".$rel_con['city_name'].",
		".$rel_con['state_name'].",
		".$rel_con['country_name']."</span><br/>
		Mobile No.: ".$rel_con['cust_mobile']."<br/>
		Email Id: ".$rel_con['cust_email']."
		<br>  State Code : ".$rel_con['gst_state_code']."
		<br>  GSTIN : ".$rel_con['gst_no'];
	}

	$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';

	if($rel['currency_id']=='68'){
		$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`=68 ';
		$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));
		$currency_name = 'INR';
		$currency_word_start = 'Rupees';
		$currency_word_end = 'Paise';
		$currency_symbol = $currency_rel['currency_symbol'];
	}else{
		$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
		$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

		$currency_name = ucfirst(strtolower($currency_rel['currency_code']));
		$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
		$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
		$currency_symbol = $currency_rel['currency_symbol'];
	}

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
//CHEMITEK PROCESS EQUIPMENTS PRIVATE LIMITED		
//>Plot No : 117 , Nr. GETCO Sub -Station , Old GIDC , Gundlav Valsad - 396035, Gujarat, India
	$comp_rel=mysqli_fetch_assoc($dbcon->query($set));

	$companyConfiguration=getCompanyConfiguration($dbcon);
	$sales_pro_print=explode(",", $companyConfiguration['sales_pro_print']);

	$html='';

	$header .= '<table style="border:none">
           <tr style="border:none">
               <td style="height:30px;border:none"></td>
           </tr>
       </table>';

	if($companyConfiguration['sales_print_letterhead_per']==0){
		$header .= get_header($dbcon,'text-align: center','','70px');
		$footer = '';
	}else{
		$header .= get_header($dbcon,'text-align: center','','70px');
		$footer = '';
	}

	$html.='<html>
	<head>					
	<title>Proforma Invoice - '.$rel['sales_order_no'].'</title>

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

		
		/*.quot_annex_content_div table tr,td{
			padding:5px;
		}*/
		.blueHeading {
			background-color: #a7adb5;
			
		}

		</style>
		</head>
		<body>
		<!--Show Logo in other pages-->
		<!--<htmlpageheader name="otherpages" style="display:none">
		<div style="text-align:center">'.$header.'</div>
		</htmlpageheader>-->
		<!--<htmlpagefooter name="otherpages_footer" style="display:none">
		<div style="text-align:center">'.$footer.'</div>
		</htmlpagefooter>-->
		<!--<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>-->
		<div>
			<table style="font-size:10px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<tr >
					<td width="100%" class="blueHeading" style="border:1px solid; font-size:12px;text-align:center"><strong>PROFORMA INVOICE</strong></td>
				</tr>
			</table>
			<table style="font-size:10px; border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
				<tr>
					<td width="50%" colspan="3" class="" style="border-top:1px solid;border-left:1px solid;border-right:1px solid; text-align:left;colspan:3;"><strong><u>SUPPLIER</u></strong></td>
					<td width="50%" colspan="3" class="" style="border-top:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;colspan:3;"><strong><u>BUYER</u></strong></td>
				</tr>
				<tr >
					<td width="50%" colspan="3" class="" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid; text-align:left;colspan:3;vertical-align: top;"><strong>'.$comp_rel["company_name"].'</strong><br/>
					'.$comp_rel["address"].'<br/>PH: '.$comp_rel["contact_no"].' | E: '.$comp_rel["website"].'<br/>GST NO.: '.$comp_rel["vatno"].'</td>
					<td width="50%" colspan="3" class="" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;colspan:3;vertical-align: top;"><strong>'.$party_address_billing.'</strong></td>
				</tr>
				<tr >
					<td  class="blueHeading" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid; text-align:left;width:16%;"><strong>QUOTATION <br/> NUMBER</strong></td>
					<td  class="" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;width:18%;">'.$rel["quotation_no"].'</td>
					<td  class="blueHeading" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;width:16%;"><strong>PURCHASE ORDER <br/>NUMBER</strong></td>

					<td  class="" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;width:16%;">'.$rel['po_no'].'</td>
					<td  class="blueHeading" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;width:18%;"><strong>CUSTOMER
					<br/>NAME</strong></td>
					<td  class="" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;width:16%;">'.$rel['cust_contact_person_name'].'</td>
				</tr>
				<tr >
					<td  class="blueHeading" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid; text-align:left;"><strong>REPRESENTATIVE
					 <br/> NAME</strong></td>
					<td  class="" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;">'.$rel["user_name"].'</td>
					<td  class="blueHeading" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;"><strong>PROFORMA INVOICE <br/>DATE</strong></td>
					<td  class="" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;">'.$so_date.'</td>
					<td  class="blueHeading" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;"><strong>PROFORMA <br/> INVOICE NUMBER</strong></td>
					<td  class="" style="border-bottom:1px solid;border-left:1px solid;border-right:1px solid;text-align:left;">'.$rel["sales_order_no"].'</td>
				</tr>
			</table>
			<table style="font-size:10px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<tr>
					<td class="blueHeading" width="5%" style="border:1px solid;text-align:center;"><strong>SR.No</strong></td>
					<td class="blueHeading" width="35%" style="border:1px solid;text-align:center;"><strong>PRODUCT DESCRIPTION</strong></td>
					<td class="blueHeading" width="10%" style="border:1px solid;text-align:center;"><strong>HSN CODE</strong></td>

					<td class="blueHeading" width="12.5%" style="border:1px solid;text-align:center;"><strong>MOQ</strong></td>
					<td class="blueHeading" width="12.5%" style="border:1px solid;text-align:center;"><strong>UNIT</strong></td>
					<td class="blueHeading" width="12.5%" style="border:1px solid;text-align:center;"><strong>AESSABLE <br/>
					VALUE PER UNIT</strong></td>
					<td class="blueHeading" width="12.5%" style="border:1px solid;text-align:center;"><strong>TOTAL AMOUNT</strong></td>
				</tr>';
				if($rel['inquiry_type'] == 2)
				{
					
					$trn_qry="select *,product.product_name, product.product_alias_name, product.product_icode, dr.drawing_number, hsn.hsn_code as product_hsn_code, product.image_name FROM `tbl_salesorder_project_trn` as trn 
								left join product_mst as product on product.product_id=trn.product_id 
								left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
								left join mst_hsn_code as hsn on hsn.hsn_id=product.product_hsn 
								left join unit_mst as per on per.unitid=product.product_base_unit
								where trn.salesorder_projecttrn_status=0 and trn.sales_order_id=".$invoiceid;
				}
				else
				{
					$trn_qry="select trn.*, per.unit_name, product.product_name, product.product_alias_name, product.product_icode, dr.drawing_number, hsn.hsn_code as product_hsn_code, product.image_name FROM `tbl_sales_ordertrn` as trn 
					left join product_mst as product on product.product_id=trn.product_id
					left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
					left join mst_hsn_code as hsn on hsn.hsn_id=product.product_hsn
					left join unit_mst as per on per.unitid=trn.rate_unit 
					where trn.sales_ordertrn_status=0 and trn.sales_order_id=".$invoiceid;
				}
				$trn_qry_rs=$dbcon->query($trn_qry);
				$p=1;$ttl_amt=0;$ttl_qty=0;$total_cs_gst=0;$total_i_gst=0;$gst_per=0;$gst_rate=0;$total_billsundy_gst=0;
				$cnt=mysqli_num_rows($trn_qry_rs);
				while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
					$product_desc = ($trn_rel['description']!='0') ? nl2br($trn_rel['description']) : '';
					if($trn_rel['rate_unit']==$trn_rel['unit_id']){
						$sqty = $trn_rel['product_qty'];
					}else{
						$sqty = $trn_rel['product_conv_qty'];
					}
					$html.='<tr>
					<td style="border:1px solid;vertical-align: top;">'.$p.'</td>
					<td style="border:1px solid;vertical-align: top;">'.$trn_rel['product_name'].' <br/>'.$product_desc.'</td>
					<td style="border:1px solid;vertical-align: top;text-align:center;">'.$trn_rel['product_hsn_code'].'</td>
					<td style="border:1px solid;vertical-align: top;text-align:center;">'.without_comma_two_digit_amount($sqty).'</td>
					<td style="border:1px solid;vertical-align: top;text-align:center;">'.$trn_rel['unit_name'].'</td>
					<td style="border:1px solid;vertical-align: top;text-align:right;">'.$currency_symbol.' '.indian_number($trn_rel['product_rate_conv'],2).'</td>
					<td style="border:1px solid;vertical-align: top; text-align:right;">'.$currency_symbol.' '.indian_number($trn_rel['product_amount_conv'],2).'</td>
				</tr>';
				$p++;
				$ttl_qty=$ttl_qty+$sqty;
				$ttl_amt=$ttl_amt+$trn_rel['product_amount_conv'];
				}
				$html.='<tr>
					<td colspan="3" style="border:1px solid;text-align:right;">Total</td>
					<td style="border:1px solid;text-align:center;">'.number_format($ttl_qty,2,".","").'</td>
					<td colspan="2" style="border:1px solid;"></td>
					<td style="border:1px solid;text-align:right;">'.$currency_symbol.' '.indian_number($ttl_amt,2).'</td>
					
				</tr>
				
				';
				$html.='</table>
			';

			if($rel['apson_trans_scop_of']==1){
				$transscop=$rel['company_name'];
			}else{
				$transscop=$comp_rel["company_name"];
			}
			/*if($rel['apson_dilivary_type']==1){
				$dilivarytype="DOOR DELIVERY";
			}else{
				$dilivarytype="GODOWN DELIVERY";				
			}*/

			$subtotal=$ttl_amt;
			 $trn_qrytax="SELECT sum(cgst_tax_rate_conv) as cgsttax,sum(sgst_tax_rate_conv) as sgsttax,sum(igst_tax_rate_conv) as igsttax,igst_tax_per,cgst_tax_per,sgst_tax_per FROM tbl_sales_ordertrn as trn 
				 	  where trn.sales_ordertrn_status=0 and trn.sales_order_id=".$invoiceid;
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

			$trn_qrybill="SELECT sundry_amount_conv,sundry_gst_amount_conv FROM tbl_bill_sundry_transaction as trn 
				 	  where trn.isdelete=0 and sundry_ledger_id='2' and sundry_voucher_table='tbl_sales_order' and trn.sundry_voucher_id=".$invoiceid;
			$trn_qry_rsbill=$dbcon->query($trn_qrybill);
			$trn_bill=mysqli_fetch_assoc($trn_qry_rsbill);
			if($rel['currency_id']=='68')
			{
			if($dived==1){
				 $pcgst=$trn_bill['sundry_gst_amount_conv']/2;
				$cgsttax=$cgsttax+$pcgst;
				$sgsttax=$sgsttax+$pcgst;
			}else{
				 $igsttax=$igsttax+$trn_bill['sundry_gst_amount_conv'];
			}
		}
			$trn_qrybill1="SELECT sundry_amount_conv,sundry_gst_amount_conv FROM tbl_bill_sundry_transaction as trn 
				 	  where trn.isdelete=0 and sundry_ledger_id='3' and sundry_voucher_table='tbl_sales_order' and trn.sundry_voucher_id=".$invoiceid;
			$trn_qry_rsbill1=$dbcon->query($trn_qrybill1);
			$trn_bill1=mysqli_fetch_assoc($trn_qry_rsbill1);
			if($rel['currency_id']=='68')
			{
			if($dived==1){
				$pcgst=$trn_bill1['sundry_gst_amount_conv']/2;
				$cgsttax=$cgsttax+$pcgst;
				$sgsttax=$sgsttax+$pcgst;
			}else{
				$igsttax=$igsttax+$trn_bill1['sundry_gst_amount_conv'];
			}
		}
			$trn_qrybill2="SELECT sundry_amount_conv,sundry_gst_amount_conv FROM tbl_bill_sundry_transaction as trn 
				 	  where trn.isdelete=0 and sundry_ledger_id='5' and sundry_voucher_table='tbl_sales_order' and trn.sundry_voucher_id=".$invoiceid;
			$trn_qry_rsbill2=$dbcon->query($trn_qrybill2);
			$trn_bill2=mysqli_fetch_assoc($trn_qry_rsbill2);
			if($rel['currency_id']=='68')
			{
			if($dived==1){
				$pcgst=$trn_bill2['sundry_gst_amount_conv']/2;
				$cgsttax=$cgsttax+$pcgst;
				$sgsttax=$sgsttax+$pcgst;
			}else{
				$igsttax=$igsttax+$trn_bill2['sundry_gst_amount_conv'];
			}
		}
			$subtotal=$subtotal+$trn_bill['sundry_amount_conv']+$trn_bill1['sundry_amount_conv']+$trn_bill2['sundry_amount_conv'];
			if(!empty($trn_bill['sundry_amount_conv'])){
				$b1per=(($trn_bill['sundry_amount_conv']*100)/$ttl_amt);
				$b1AMT= $trn_bill['sundry_amount_conv'];
			}else{
				$b1per='0.00';
				$b1AMT='0.00';
			}
			
			if(!empty($trn_bill1['sundry_amount_conv'])){
				$b1per1=(($trn_bill1['sundry_amount_conv']*100)/$ttl_amt);
				$b1AMT1= $trn_bill1['sundry_amount_conv'];
			}else{
				$b1per1='0.00';
				$b1AMT1='0.00';
			}
			
			if(!empty($trn_bill2['sundry_amount_conv'])){
				$b1per2=(($trn_bill2['sundry_amount_conv']*100)/$ttl_amt);
				$b1AMT2= $trn_bill2['sundry_amount_conv'];
			}else{
				$b1per2='0.00';
				$b1AMT2='0.00';
			}
			$html.='<table style="font-size:10px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
			   <tr>
			   		<td class="blueHeading" style="border:1px solid;width:50%;">PAYMENT TERMS</td>
			   		<td class="blueHeading" style="border:1px solid;width:20%;">PACKING CHARGE</td>
			   		<td style="border:1px solid;width:10%;text-align:Center;">'.indian_number($b1per,2).' %</td>
			   		<td style="border:1px solid;width:20%;text-align:right;">'.$currency_symbol.' '.indian_number($b1AMT,2).'</td>
			   </tr>
			   <tr>
			   		<td  style="border:1px solid;">'.$rel['payment_terms'].'</td>
			   		<td class="blueHeading" style="border:1px solid;">FORWORDING CHARGE</td>
			   		<td style="border:1px solid;text-align:Center;">'.indian_number($b1per1,2).' %</td>
			   		<td style="border:1px solid;text-align:right;">'.$currency_symbol.' '.indian_number($b1AMT1,2).'</td>
			   </tr>
			   <tr>
					<td class="blueHeading" style="border:1px solid;">PROFORMA INVOICE VALIDITY</td>
					<td class="blueHeading" style="border:1px solid;">OTHER</td>
					<td style="border:1px solid;text-align:Center;">'.indian_number($b1per2,2).' %</td>
					<td style="border:1px solid;text-align:right;">'.$currency_symbol.' '.indian_number($b1AMT2,2).'</td>
	  			</tr>
				<tr>
				  <td style="border:1px solid;">'.$apson_validity_date.'</td>
				  <td class="blueHeading" style="border:1px solid;">SUB TOTAL</td>
				  <td style="border:1px solid;text-align:Center;"></td>
				  <td style="border:1px solid;text-align:right;">'.$currency_symbol.' '.indian_number($subtotal,2).'</td>
				</tr>
				<tr>
				  <td class="blueHeading" style="border:1px solid;">DELIEVERY DATE</td>
				  <td class="blueHeading" style="border:1px solid;">IGST</td>
				  <td style="border:1px solid;text-align:Center;">'.indian_number($trn_reltax["igst_tax_per"],2).'%</td>
				  <td style="border:1px solid;text-align:right;">'.$currency_symbol.' '.indian_number($igsttax,2).'</td>
				</tr>
				<tr>
				  <td style="border:1px solid;">'.$delivery_date.'</td>
				  <td class="blueHeading" style="border:1px solid;">SGST</td>
				  <td style="border:1px solid;text-align:Center;">'.indian_number($trn_reltax["sgst_tax_per"],2).'%</td>
				  <td style="border:1px solid;text-align:right;">'.$currency_symbol.' '.indian_number($sgsttax,2).'</td>
				</tr>
				<tr>
				  <td class="blueHeading" style="border:1px solid;">DISPATCH FROM</td>
				  <td class="blueHeading" style="border:1px solid;">CGST</td>
				  <td style="border:1px solid;text-align:Center;">'.indian_number($trn_reltax["cgst_tax_per"],2).'%</td>
				  <td style="border:1px solid;text-align:right;">'.$currency_symbol.' '.indian_number($cgsttax,2).'</td>
				</tr>
				<tr>
				  <td style="border:1px solid;">EX WORKS, APSON EMPIRE - MIRZAPUR, KHEDA</td>
				  <td class="blueHeading" style="border:1px solid;">TOTAL PAYABLE AMOUNT</td>
				  <td style="border:1px solid;"></td>
				  <td style="border:1px solid;text-align:right;">'.$currency_symbol.' '.indian_number($rel["g_total_conv"],2).'</td>
				</tr>
				<tr>
				  <td class="blueHeading" style="border:1px solid;">TRANSPORTATION IN SCOPE OF</td>
				  <td class="blueHeading" colspan="3" style="border:1px solid;">TRANSPORTER NAME</td>
				</tr>
				<tr>
				  <td class="" style="border:1px solid;">'.$transscop.'</td>
				  <td class="" colspan="3" style="border:1px solid;">'.$rel['transportation_name'].'</td>
				</tr>
				<tr>
				  <td class="blueHeading" style="border:1px solid;">DELIVERY TYPE</td>
				  <td class="blueHeading" colspan="3" style="border:1px solid;">DELIVERY ADDRESS</td>
				</tr>
				<tr>
				  <td class="" style="border:1px solid;">'.$dilivarytype.'</td>
				  <td class="" colspan="3" rowspan="3" style="border:1px solid;">'.$rel['ship_address'].'</td>
				</tr>
				<tr>
				  <td class="blueHeading" style="border:1px solid;">DISPATCH FROM</td>
				  
				</tr>
				<tr>
				  <td class="" style="border:1px solid;"><strong>'.$comp_rel['company_name'].'</strong>,<br/>
				  '.$comp_rel["address"].'</td>
				  
				</tr>
				</table>';
				$html.='<table style="font-size:12px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<tr>
				  <td colspan="4" style="border:1px solid; text-transform: uppercase;">TOTAL AMOUNT IN WORDS :'.$currency_name.' '.convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$rel['currency_in_word_end'],$rel['currency_in_word']).'</td>
				  
				</tr>
				
				</table>
				';
				$html.='<table style="font-size:10px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
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
				$terms_qry="select qtrm.*,mst.tc_name from tbl_salesorder_terms_trn as qtrm 
		left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
		where qtrm.quotation_terms_trn_status=0 and qtrm.sales_order_id=".$rel['sales_order_id']." order by qtrm.tc_priority";
		$terms_qry_rs=$dbcon->query($terms_qry);
		if(mysqli_num_rows($terms_qry_rs)>0){
			$html.='<div>
			<table style="font-size:10px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<thead>
				<tr>
					<th style="text-align:center; border:1px solid;">Sr.<br/>No.</th>
					<th style="text-align:left; border:1px solid;">Term Name</th>
					<th style="text-align:left; border:1px solid;">Conditions</th>
				</tr>
			</thead><tbody>';
			$t=1;
			while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
				$string=(nl2br($term_rel['tc_details']));
				$html.='<tr>
				<td width="5%" style="width:5%;text-align:center;border:1px solid;padding:5px;">'.$t.'</td>
				<td width="25%" style="width:25%;text-align:left;border:1px solid;padding:5px;">'.$term_rel['tc_name'].'</td>
				<td width="70%" style="width:70%;text-align:left;border:1px solid #000;padding:5px;">'.$string.'</td>
				</tr>';
				$t++;
			}
		}
		$html.='</tbody></table>';
		$html.='<table style="font-size:8px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
					<tr>
						<td style="border:1px solid;vertical-align: top;text-align: left;">
								<strong>TERMS & CONDITIONS:</strong><br/>'.$rel['quot_general_terms_condition_content'].'
						</td>
					</tr>
					
				</table>
				';
		if($rel['apson_trans_scop_of']==1){
			$transscop=$rel['company_name'];
		}else{
			$transscop=$comp_rel["company_name"];
		}
		$html.='<table style="font-size:8px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
					<tr>
						<td style="border-left:1px solid;border-top:1px solid;text-align: left;vertical-align: top;">
							<strong>'.$rel['company_name'].'</strong>
						</td>
						<td style="border-right:1px solid;border-top:1px solid;vertical-align: top;text-align: right;height: 80px;">
						<strong>'.$comp_rel["company_name"].'</strong>
						</td>
					</tr>
					<tr>
						<td style="border-left:1px solid;border-bottom:1px solid;text-align: left;">
							<strong>AUTHORIZED SIGNATURE</strong>
						</td>
						<td style="border-right:1px solid;border-bottom:1px solid;text-align: right;">
							<strong>AUTHORIZED SIGNATURE</strong>
						</td>
					</tr>
				</table>
				';


		
		/*Annexure Content Print Strat*/
	/*if(!empty($rel['quot_annex_content'])){
		$html.= '<pagebreak page-break-type="clonebycss" />'.$rel['quot_annex_content'];
	}*/
	/*Annexure Content Print End*/

	$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
// echo $header;
//echo $trn_qry;
	//echo $html;exit;
	ob_end_clean();
	include("../../view/export/mpdf/mpdf.php");
	$mpdf=new mPDF('','A4','0','calibri','10','10','30','10','1','1');
//		$mdf->SetFont('ProximaNova');
	$mpdf->defaultheaderfontsize = 10; /* in pts */
	$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
	$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
	$mpdf->defaultfooterfontsize = 10; /* in pts */
	$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
	$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
	$mpdf->SetHTMLHeader($header);
		//$mpdf->SetHTMLFooter($footer);
	$mpdf->SetWatermarkText();
	$mpdf->showWatermarkText = true;
	$mpdf->allow_charset_conversion=true;
	$mpdf->charset_in='UTF-8';
	$mpdf->WriteHTML($html);
	$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
	ob_clean();
	return 'Order Acceptance'.$rel['sales_order_no'].'.pdf';
}	
?>