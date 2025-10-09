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
 	 $query="select invoice.*,country.country_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust.cust_pincode,cust.cust_mobile,cust.common_email_id,cust.gst_no,cust.m_pan,per.c_con_fname,per.c_con_lname,state.gst_state_code,td.transportation_name,cust.cust_cont_name, cust.stateid,terms.payment_terms,cp.cust_contact_person_name from tbl_sales_order as invoice 
	 left join pay_terms as terms on terms.terms_id=invoice.payment_terms
	left join tbl_ledger as cust on cust.l_id=invoice.cust_id
	left join tbl_cust_contact as per on per.cust_id = invoice.cust_id
	left join tbl_cust_contact_person as cp on cp.cust_contact_person_id = invoice.kind_attn
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid
	left join city_mst as city on city.cityid=cust.cityid
	left join transportation_details as td on td.id=invoice.transport_id
	where sales_order_id=$invoiceid";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$delivery_type = $rel['delivery_type'];
	$transportation_name = ($rel['transportation_name']!='0')?$rel['transportation_name']:'';
	$po_date = '';
	if($rel['po_date']!="1970-01-01 00:00:00" && $rel['po_date']!="0000-00-00 00:00:00")
	{
		$po_date=date('d-m-Y',strtotime($rel['po_date']));
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

	$gem_date = '';
	if($rel['sfg_date']!="1970-01-01" && $rel['sfg_date']!="0000-00-00")
	{
		$gem_date=date('d-m-Y',strtotime($rel['sfg_date']));
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

	$party_address_billing="<strong>".$rel['company_name']."</strong>
	<span style='font-weight:normal;'> <br/>
	".$rel['cust_address'].",<br/>
	".$rel['cust_pincode']."
	".$rel['city_name'].",
	".$rel['state_name'].",
	".$rel['country_name']."</span><br/>
		Mobile No.: ".$rel['cust_mobile']."<br/>
		Email Id: ".strtolower($rel['common_email_id'])."
	<br>  State Code : ".$rel['gst_state_code']."
	<br>  GSTIN : ".$rel['gst_no'];

	if($rel['consignee_id']==0){
		$contact_person = $rel['cust_contact_person_name'];
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

	if($rel['currency_id']=='1'){
		$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`=68 ';
		$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));
		$currency_name = '(INR)';
		$currency_word_start = 'Rupees';
		$currency_word_end = 'Paise';
		$currency_symbol = $currency_rel['currency_symbol'];
	}else{
		$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
		$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

		$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
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
	<title>Order Acceptance - '.$rel['sales_order_no'].'</title>

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
			border:1px solid #000 !important;
			/*page-break-inside:avoid;*/
		}
		.quot_annex_content_div table tr,td{
			padding:5px;
		}
		.blueHeading {
			color: #365f91;
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
		<table><tr style="border: 0px; ">
		    <td colspan="3" width="100%" style="border: 0px; font-size:16px;text-align:center"><strong>Sales Acknowledgement</strong></td>
		</tr>
		<!--<tr style="border-bottom: 0px; ">
		<td width="33%" style="border: 0px; font-size:14px;">GSTIN : <span style="font-size:13px;">'.$comp_rel["vatno"].'</span></td>
		<td width="34%" style="border: 0px; font-size:14px;">CIN : '.$comp_rel['cin'].'<span style="font-size:13px;"></span> </td>
		<td width="33%" style="border: 0px; font-size:14px;">PAN : <span style="font-size:13px;">'.$comp_rel["pan_no"].'</span></td>
		</tr>
		<tr style="border-top: 0px; ">
		<td style="border: 0px; font-size:14px;">Phone : <span style="font-size:13px;">'.$comp_rel["contact_no"].'</span></td>
		<td style="border: 0px; font-size:14px;">Email : <span style="font-size:13px;">'.$comp_rel["website"].'</span></td>
		<td style="border: 0px; font-size:14px;">Website : <span style="font-size:13px;">'.$comp_rel["company_website"].'</span></td>
		</tr>-->
		<tr style="border:none;border-top:1px solid; border-left:1px solid; border-right:1px solid; ">
		<td colspan="2" style="width: 50%; text-align:left;">
		Sales Order No:<b>'.$rel['sales_order_no'].' </b></td>
		<td style="width: 50%; text-align:left;">
		Transport Mode:'.$transportation_name.'</td>
		</tr>
		<tr style="border:none; border-left:1px solid; border-right:1px solid; ">
		<td colspan="2" style=" text-align:left;">
		Sales Order Date:<b>'.$so_date.'</b> </td>
		<td style=" text-align:left;">
		Delivery: '.$delivery_date.'</td>
		</tr>
		<tr style="border:none; border-left:1px solid; border-right:1px solid; ">
		<td colspan="2" style=" text-align:left;">
		Payment Terms: '.$rel['payment_terms'].'</td>
		<td style=" text-align:left;">
		Customer PO No: '.$rel['po_no'].'</td>
		</tr>
		
		<tr style="border:none; border-left:1px solid; border-right:1px solid; ">
		<td colspan="2" style=" text-align:left;">
		Contact Person:'.$contact_person.'
		</td>
		<td style=" text-align:left;">
		Customer PO Date:'.$po_date.'</td>
		</tr>
		<tr style="border:none; border-left:1px solid; border-right:1px solid; ">
		<td colspan="2" style=" text-align:left;">
			Sales Quote No:
		</td>
		<td style=" text-align:left;">';
		if($order_by){
	        $html.='GEM No :'.$order_by;
		}
		$html.'</td>
		</tr>
		<tr style="border:none; border-left:1px solid; border-right:1px solid; ">
		<td colspan="2" style=" text-align:left;">
		
		</td>
		<td style=" text-align:left;">';
		if($gem_date){
	        $html.='GEM Date :'.$gem_date;
		}
		$html.='</td>
		</tr>
		<tr style="background-color:#b3b3b3">
		<td colspan="2" style=" text-align:center;"><b>Details of Recipient (Billed to)</b> </td>
		<td style=" text-align:center;"><b>Details of Consignee (Shipped to)</b></td>
		</tr>
		<tr style="border:none; border-left:1px solid; border-right:1px solid;">
		<td colspan="2" style="border-left:1px solid;border-bottom:none; text-align:left;">'.$party_address_billing.' </td>
		<td style="border-bottom:none;  text-align:left;">'.$party_address_con.'</td>
		</tr>
		</table>
		</div>
		<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		<thead>
		<tr style="background-color:#b3b3b3">
		<td style=" text-align:center;"><b>Sr No.</b> </td>
		<td  style=" text-align:left;"><b>Part Code / Goods Description</b></td>
		<td colspan="2"  style=" text-align:center;"><b>HSN Code</b> </td>
		<td style=" text-align:center;"><b>UOM</b> </td>
		<td style=" text-align:center;"><b>Qty</b> </td>
		<td style=" text-align:center;"><b>Unite Rate <br>'.$currency_name.'</b> </td>
		<td style=" text-align:center;"><b>Disc</b> </td>
		<td style=" text-align:center;"><b>Amount '.$currency_name.'</b> </td>
		</tr>
		</thead>
		<tbody>';
		
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

			$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
			$gst_rate = $trn_rel['cgst_tax_rate_conv']+$trn_rel['sgst_tax_rate_conv']+$trn_rel['igst_tax_rate_conv'];

			if($trn_rel['cgst_tax_rate_conv'] != 0 || $trn_rel['sgst_tax_rate_conv'] !=0){
				$total_cs_gst += $gst_rate;
			}else{
				$total_i_gst += $gst_rate;
			}
			$item_code = '';$item_code='';$alias='';
			if(in_array('drawing',$sales_pro_print)){
	            $drawing_number = " -- (".$trn_rel['drawing_number'].")";
	        }
	        if(in_array('item',$sales_pro_print)){
	            $item_code = " -- (".$trn_rel['product_icode'].")";
	        }
	        if(in_array('alias',$sales_pro_print)){
	            $alias = " -- (".$trn_rel['product_alias_name'].")";
	        }
			
			

			if($trn_rel['rate_unit']==$trn_rel['unit_id']){
				$sqty = $trn_rel['product_qty'];
			}else{
				$sqty = $trn_rel['product_conv_qty'];
			}

			$product_desc = ($trn_rel['description']!='0') ? $trn_rel['description'] : '';
			$path='view/upload/product_images/';
			$html.='
			<tr>
			<td style="text-align:center; vertical-align: top;">'.$p.' </td>
			<td style="border: none;text-align:left; vertical-align: top;">
			<table style="width:100%;">
			<tr border="0" style="border-radious: 0px; border: none;">';
			$align="";
			if($trn_rel['product_type']=='8'){
				$align="text-align:right";
			}
			if($trn_rel['image_name']!=''){
				$html.='<td border="0" style="border-radious: 0px; border: none!important;width:30%;'.$align.'"><img src="'.ROOT.$path.$trn_rel['image_name'].'" height="50" width="50" class="img-thumbnail" /></td>';
			}
			$html.='<td border="0" style="border-radious: 0px; border: none!important;'.$align.'"><strong>'.$trn_rel['product_name'].''.$item_code.' '.$alias.'</strong><br/>'.$product_desc.'</td> 
			</tr>
			</table>';
			if($delivery_type == 'product_wise'){
				$retu_date = "select sdate.*,unit.unit_name from tbl_salesorder_delivery_date as sdate left join unit_mst as unit on unit.unitid=sdate.unit_id where invoice_status=0 and sdate.po_delivery_date_status=0 and sales_ordertrn_id=".$trn_rel['sales_ordertrn_id'];
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
			<td colspan="2" style="vertical-align: top; text-align:center;"> '.$trn_rel['product_hsn_code'].'</td>
			<td style="vertical-align: top; text-align:center;">'.$trn_rel['unit_name'].' </td>
			<td style="vertical-align: top; text-align:center;">'.without_comma_two_digit_amount($sqty).' </td>
			<td style="vertical-align: top; text-align:center;">'.$currency_symbol.' '.without_comma_two_digit_amount($trn_rel['product_rate_conv']).'</td>
			<td style="vertical-align: top; text-align:center;">'.without_comma_two_digit_amount($trn_rel['discount_per']).' % <br>'.$currency_symbol.' ('.without_comma_two_digit_amount($trn_rel['product_discount_conv']).')</td>
			<td style="vertical-align: top; text-align:center;"> '.$currency_symbol.' '.without_comma_two_digit_amount($trn_rel['product_amount_conv']).'</td>
			</tr>';

			$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
			if($trn_rel['act_amt_flag']!='1'){
		//$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
				$ttl_amt=$ttl_amt+$trn_rel['product_amount_conv'];
			}
			$p++;
		}
		$pr=10-$cnt;

		$html.='
		<tr>
		<td colspan="8" style=" text-align:right;">Item Total Amount  </td>
		<td  style=" text-align:center;">'.$currency_symbol.' '.indian_number($ttl_amt,2).'</td>
		</tr>';
		
		$qry12="select b.sundry_amount_conv,b.sundry_gst_amount_conv,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
		from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
		left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
		where b.sundry_voucher_id=".$invoiceid." and b.sundry_voucher_table='tbl_sales_order' and b.isdelete='0' and le.default_sundry='0'";
		$result12=$dbcon->query($qry12);        
		while($row12=mysqli_fetch_assoc($result12))
		{
			$html.='<tr>
			<td colspan="8" style=" text-align:right;">'.$row12['l_name'].'</td>
			<td  style="text-align:center;">'.$currency_symbol.' '.number_format($row12['sundry_amount_conv'],2,".","").'</td>
			</tr>';
			
			$total_billsundy_gst += $row12['sundry_gst_amount_conv'];
		}
		
		if($rel['stateid']==$comp_rel['stateid']){
			 $total_billsundy_gst_amount = ($total_billsundy_gst/2);
			$html.='<tr>
			<td colspan="8" style=" text-align:right;">CGST %'.number_format(($gst_per)/2).'</td>
			<td  style=" text-align:center;">'.$currency_symbol.' '.number_format((($total_cs_gst/2)+$total_billsundy_gst_amount),2,".","").'</td>
			</tr><tr>
			<td colspan="8" style=" text-align:right;">SGST %'.number_format(($gst_per)/2).'</td>
			<td  style=" text-align:center;">'.$currency_symbol.' '.number_format((($total_cs_gst/2)+$total_billsundy_gst_amount),2,".","").'</td>
			</tr>';

			$tax_wr_amt = $total_cs_gst + $total_billsundy_gst_amount;
		}else{
		  	$total_billsundy_gst;
			$html.='<tr>
			<td colspan="8" style=" text-align:right;">IGST %'.number_format($gst_per).'</td>
			<td  style=" text-align:center;">'.$currency_symbol.' '.number_format(($total_i_gst+$total_billsundy_gst),2,".","").'</td>
			</tr>';

			$tax_wr_amt = $total_cs_gst + $total_billsundy_gst_amount;
		}
		$qry11="select sum((tc.tax_per*trn.product_amount_conv)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_sales_ordertrn as trn 
		left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
		left join tbl_ledger as l on l.l_id=tc.tax_id 
		where tc.tax_additional='1' and trn.sales_order_id=".$invoiceid." and trn.sales_ordertrn_status!=2 and tc.isdelete='0' group by tc.tax_id";
		$result11=$dbcon->query($qry11);        
		while($row11=mysqli_fetch_assoc($result11))
		{
			$html.='<tr>
			<td colspan="8" style=" text-align:right;">'.$row11['l_name'].'</td>
			<td  style=" text-align:center;">'.$currency_symbol.' '.number_format($row11['add_sum'],2,".","").'</td>
			</tr>';
		}
		
		$round_off = round($rel['g_total_conv'])-$rel['g_total_conv'];
		$gtotal=$rel['g_total_conv']-($round_off);
		$html .= '<tr>
		<td colspan="8" style=" text-align:right;font-size:14px;">Round Off  </td>
		<td  style=" text-align:center;font-size:14px;">'.indian_number($round_off,2).'</td>
		</tr>
		<tr>
		<td colspan="8" style=" text-align:right;font-size:14px;background-color:	#b3b3b3">Total Order Value (In Figure) </td>
		<td  style=" text-align:center;font-size:14px;font-weight:bold;background-color:#b3b3b3">'.$currency_symbol.' '.number_format($rel['g_total_conv'],0,".","").'.00</td>
		</tr>';

		$html.='</tbody></table>';
		$html.='<table style="page-break-inside: avoid;">

		<tr style="">
		<td colspan="9" style=" text-align:left;font-size:14px;">
		Total Order Value (In Words): <span style="font-weight:bold;font-size:13px;">'.(($rel['currency_id']=='1') ? convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$currency_word_end,$currency_word_start) : convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$currency_word_end,$currency_word_start)).'</span></td>
		</tr>
		<tr style="">
		<td colspan="9" style=" text-align:left;font-size:14px;">
		Total Tax (In Words): <span style="font-weight:bold;font-size:13px;">'.(($rel['currency_id']=='1') ? convert_number_to_words_new($tax_wr_amt,$rel['currency_id'],$currency_word_end,$currency_word_start) : convert_number_to_words_new($tax_wr_amt,$rel['currency_id'],$currency_word_end,$currency_word_start)).'</span></td>
		</tr>
		<tr style="">
		<td colspan="9" style="border-bottom: 1px;text-align:left;font-size:14px;">
		Remarks :'.$rel['remark'].'</td>
		</tr>

		</table>';
		$html.='<table>
		<tr style="">
		<td colspan="2" rowspan="2" style="font-size:13px;width:100px;text-align:center">
		HSN/SAC
		</td>
		<td colspan="2" rowspan="2" style="font-size:13px;width:100px;text-align:center">
		Taxable Value
		</td>';
		if($rel['stateid']==$comp_rel['stateid']){
			$html.='<td colspan="2" style="font-size:13px;text-align:center">SGST
			</td>
			<td colspan="2" style="font-size:13px;text-align:center">CGST
			</td>';
		}else{
			$html.='<td colspan="2" style="font-size:13px;text-align:center">IGST
			</td>';
		}
		$html.='<td colspan="2" rowspan="2" style="font-size:13px;text-align:center;width:100px">
		Total Tax Amount
		</td>
		</tr>';
		if($rel['stateid']==$comp_rel['stateid']){
			$html.='<tr>
			<td style="font-size:12px;text-align:center;">Rate</td>
			<td style="font-size:12px;text-align:center;">Amount</td>
			<td style="font-size:12px;text-align:center;">Rate</td>
			<td style="font-size:12px;text-align:center;">Amount</td>
			</tr>';
		}else{
			$html.='<tr>
			<td style="font-size:12px;text-align:center;">Rate</td>
			<td style="font-size:12px;text-align:center;">Amount</td>
			</tr>';
		}
		
		 $trn_qry_tax="select sum(product_amount_conv) as product_amount,product_hsn_code,cgst_tax_per,sum(cgst_tax_rate_conv) as cgst_tax_rate,sgst_tax_per,sum(sgst_tax_rate_conv) as sgst_tax_rate,igst_tax_per,sum(igst_tax_rate_conv) as igst_tax_rate FROM `tbl_sales_ordertrn` where sales_ordertrn_status=0 and sales_order_id='".$rel['sales_order_id']."' group by product_hsn_code";

		$trn_qry_rs_tax=$dbcon->query($trn_qry_tax);

		$total_amount_sum = 0;
		$total_tax_sum = 0;
		while($trn_rel_tax=brp_mysqli_fetch_assoc($trn_qry_rs_tax)){

			$total1+=$row_total=$trn_rel_tax['cgst_tax_rate']+$trn_rel_tax['sgst_tax_rate']+$trn_rel_tax['igst_tax_rate'];

			$html.='<tr style="">
			<td colspan="2" style="text-align:center;font-size:12px;">
			'.$trn_rel_tax['product_hsn_code'].'
			</td>
			<td colspan="2" style="text-align:right;font-size:12px;">
			'.$currency_symbol.' '.without_comma_two_digit_amount($trn_rel_tax['product_amount']).'
			</td>';
			$total_taable_amount = 0;

			$k = 1;

			if($rel['stateid']==$comp_rel['stateid']){
				$html.='<td style="text-align:right;font-size:12px;">
				'.str_replace("CGST","",$trn_rel_tax['cgst_tax_per']).'
				</td>
				<td style="text-align:right;font-size:12px;">
				'.$currency_symbol.' '.number_format($trn_rel_tax['cgst_tax_rate'],2,".","").'
				</td>
				<td style="text-align:right;font-size:12px;">
				'.str_replace("SGST","",$trn_rel_tax['cgst_tax_per']).'
				</td>
				<td style="text-align:right;font-size:12px;">
				'.$currency_symbol.' '.number_format($trn_rel_tax['sgst_tax_rate'],2,".","").'
				</td>';
				$k++;

				$total_taxable_amount = $total_taxable_amount+$total_cs_gst;
				$tax_array[$rel15['tx_tax_id']][] = $texAmount;
			}else{
				$html.='<td style="text-align:right;font-size:12px;">
				'.str_replace("IGST","",$trn_rel_tax['igst_tax_per']).'
				</td>
				<td style="text-align:right;font-size:12px;">
				'.$currency_symbol.' '.number_format($trn_rel_tax['igst_tax_rate'],2,".","").'
				</td>';
				$k++;

				$total_taxable_amount = $total_taxable_amount+$total_i_gst;
				$tax_array[$rel15['tx_tax_id']][] = $texAmount;
			}

			$total_tax_sum = $total_tax_sum + $total_taxable_amount; 
			$html.='<td colspan="2" style=" text-align:right;font-size:12px;">
			'.number_format($row_total,2).'
			</td>
			</tr>';
			$totalamt+=$trn_rel_tax['product_amount'];
			$totaltaxamt1+=$trn_rel_tax['cgst_tax_rate'];
			$totaltaxamt2+=$trn_rel_tax['sgst_tax_rate'];
			$totaltaxamt3+=$trn_rel_tax['igst_tax_rate'];
		}
		
		$sundrytax1=$dbcon->query("select b.*,tl.ledger_hsn from tbl_bill_sundry_transaction as b
			left join tbl_ledger as tl on b.sundry_ledger_id=tl.l_id
			where b.sundry_voucher_id=".$rel['sales_order_id']." and b.sundry_voucher_table='tbl_sales_order' and b.isdelete='0' ");
		while($sundry_tax=brp_mysqli_fetch_assoc($sundrytax1))
		{
			if($sundry_tax['sundry_gst_amount_conv'] != 0){
				$total_sun1+=$sundry_tax['sundry_gst_amount_conv'];
				$html.='<tr> 
				<td colspan="2" style="vertical-align:top;text-align:center;border-right:1px solid;border-bottom:1px solid;" >
				'.$sundry_tax['ledger_hsn'].'
				</td>
				<td colspan="2" style="vertical-align:top;text-align:right;border-right:1px solid;border-bottom:1px solid;" >
				'.$currency_symbol.' '.$sundry_tax['sundry_amount_conv'].'
				</td>';
				
				if($rel['stateid']==$comp_rel['stateid'])
				{
					$sun_gst_per = $sundry_tax['sundry_gst_per']/2;
					$sun_gst_amt = $sundry_tax['sundry_gst_amount_conv']/2;
					$html.='<td style="vertical-align:top;text-align:right;border-right:1px solid;border-bottom:1px solid;" >
					'.$sun_gst_per.'
					</td>
					<td style="vertical-align:top;text-align:right;border-right:1px solid;border-bottom:1px solid;" >
					'.$currency_symbol.' '.$sun_gst_amt.'
					</td>
					<td style="vertical-align:top;text-align:right;border-right:1px solid;border-bottom:1px solid;" >
					'.$sun_gst_per.'
					</td>
					<td style="vertical-align:top;text-align:right;border-right:1px solid;border-bottom:1px solid;" >
					'.$currency_symbol.' '.$sun_gst_amt.'
					</td>';
				}
				else if($rel['stateid']!=$comp_rel['stateid'])
				{
					
					$html.='<td style="vertical-align:top;text-align:right;border-right:1px solid;border-bottom:1px solid;" >
					'.$sundry_tax['sundry_gst_per'].'
					</td>
					<td style="vertical-align:top;text-align:right;border-right:1px solid;border-bottom:1px solid;" >
					'.$currency_symbol.' '.$sundry_tax['sundry_gst_amount_conv'].'
					</td>';
				}
				$html.='<td  colspan="2" style="vertical-align:top;text-align:right;border-bottom:1px solid;border-right: none;" >
				'.$currency_symbol.' '.$sundry_tax['sundry_gst_amount_conv'].'
				</td>';

				$html.='</tr>';
				$total_sunamt+=$sundry_tax['sundry_amount_conv'];
				$total_suntaxamt1+=$sundry_tax['sundry_gst_amount_conv']/2;
				$total_suntaxamt2+=$sundry_tax['sundry_gst_amount_conv'];

			}
		}
		$html.='<tr style="">
		<td colspan="2" style="text-align:center;font-size:13px;font-weight:bold">
		Total
		</td>
		<td colspan="2" style="text-align:right;font-size:13px;font-weight:bold">
		'.$currency_symbol.' '.number_format($totalamt+$total_sunamt,2).'
		</td>';
		if($rel['stateid']==$comp_rel['stateid']){
			$html.='<td style="text-align:right;font-size:13px;font-weight:bold" colspan="2">
			'.$currency_symbol.' '.number_format($totaltaxamt1+$total_suntaxamt1,2).'
			</td>
			<td style="text-align:right;font-size:13px;font-weight:bold" colspan="2">
			'.$currency_symbol.' '.number_format($totaltaxamt2+$total_suntaxamt1,2).'
			</td>
			';
		} else{
			$html.='<td  style="text-align:right;font-size:13px;font-weight:bold" colspan="2">
			'.$currency_symbol.' '.number_format($totaltaxamt3+$total_suntaxamt1,2).'
			</td>
			';
		}
		$html.='<td colspan="2" style=" text-align:right;font-size:13px;font-weight:bold">
		'.$currency_symbol.' '.number_format($total1+$total_sun1,2).'
		</td>
		</tr>
		</table>';
		$path_sign='view/upload/product_images/';
		$html.='<table style="page-break-inside: avoid;" >';
		$html.='
		<!--<tr>
		<td style=" text-align:left;font-size:14px;">
		<span>Company \'s Bank Details : </span>
		<br>
		<span>Bank Name : '.$comp_rel["bank_name"].'</span>
		<br>
		<span>A/c No : '.$comp_rel["ac_no"].'</span>
		<br>
		<span>Branch & IFSC Code : '.$comp_rel["branch_name"].' & '.$comp_rel["ifcs"].'</span>
		</td>
		</tr>-->
		<tr style="border-bottom: 0px; ">
		<td style=" text-align:right;font-size:14px;font-weight:bold">
		For , '.$comp_rel["company_name"].'</td>
		</tr>
		<tr style="border-bottom: 0px;border-top: 0px; ">
		<td style=" text-align:right;font-size:12px;">
		<img src="'.DOMAIN_F.'view/upload/signature/'.$comp_rel['authorized_signature'].'" height="50" width="150" class="img-thumbnail" /></td>
		</tr>
		<tr style="border-top: 0px; ">
		<td style=" text-align:right;font-size:12px;">
		Authorized Signature</td>
		</tr>';
		$html.='</table>';


		/* Get Terms And Condition Start */
		
		$terms_qry="select qtrm.*,mst.tc_name from tbl_salesorder_terms_trn as qtrm 
		left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
		where qtrm.quotation_terms_trn_status=0 and qtrm.sales_order_id=".$rel['sales_order_id']." order by qtrm.tc_priority";
		$terms_qry_rs=$dbcon->query($terms_qry);
		if(mysqli_num_rows($terms_qry_rs)>0){
			$html.='<center class="nextpage"></center><div>
			<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
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
	$mpdf=new mPDF('','A4','0','calibri','10','10','45','10','1','1');
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