<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$include = '../../include/';

$type='pdf';
if(strtolower($type) == 'pdf') {
	$salesorderid=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select po.*, state.state_name, payterms.payment_terms as payment_term_name, l.l_name as vender_name, l.ledger_code, country.country_name,coun.country_name as cons_country, stat.state_name as cons_state, cit.city_name as cons_city, l.m_address as vender_address,l.gst_no as tin_no,  l.cust_mobile as vender_mobile, l.stateid, state.gst_state_code, city.city_name, l.cust_pincode,l.iec_no, l.cust_email, comp.company_name, cur.currency_symbol, cur.currency_code, td.transportation_name, con.cust_contact_person_name, con.cust_contact_person_no, con.cust_contact_person_email, con.cust_contact_person_designation, consi.company_name as cons_name, consi.cust_address as cons_address, consi.cust_pincode as cons_pin, consi.gst_no as cons_gst

	from tbl_sales_order as po 
	inner join tbl_ledger as l on l.l_id=po.cust_id
	left join tbl_custmer_consignee as consi on consi.cust_id = po.consignee_id
	left join country_mst as country on country.countryid=l.countryid 
	left join country_mst as coun on coun.countryid = consi.countryid
	left join pay_terms as payterms on payterms.terms_id=po.payment_terms 
	left join state_mst as state on state.stateid=l.stateid 
	left join state_mst as stat on stat.stateid = consi.stateid
	left join city_mst as city on city.cityid=l.cityid  
	left join city_mst as cit on cit.cityid = consi.cityid
	left join tbl_company as comp on comp.company_id = po.company_id 
	left join tbl_transport_transaction as tdtr on tdtr.transport_transaction_table_id=po.sales_order_id and tdtr.transport_transaction_table='tbl_sales_order'
	left join transportation_details as td on td.id=tdtr.transport_id
	left join tbl_currency as cur on cur.currency_id = po.currency_id 
	left join tbl_cust_contact_person as con on con.cust_contact_person_id = po.kind_attn
	where po.sales_order_id=$salesorderid";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	//echo "<pre>";print_r($rel);die();
	$_SESSION['invoice_no']=$rel['invoice_no'];		
	$delivery_type = $rel['delivery_type'];
	$sales_order_date='';
	if($rel['sales_order_date']!="1970-01-01" && $rel['sales_order_date']!="0000-00-00")
	{
		$sales_order_date=date('d-m-Y',strtotime($rel['sales_order_date']));
	}

	$po_date='';
	if($rel['po_date']!="1970-01-01" && $rel['po_date']!="0000-00-00")
	{
		$po_date=date('d-m-Y',strtotime($rel['po_date']));
	}

	$delivery_date='';
	if($rel['delivery_date']!="1970-01-01" && $rel['delivery_date']!="0000-00-00")
	{
		$delivery_date=date('d-m-Y',strtotime($rel['delivery_date']));
	}
	

	$order_by = ($rel['order_by']!='0')?$rel['order_by']:"";
	
	

	

	$HowManyWeeks = (strtotime( $rel['purchaseorder_due_date'] ) - strtotime( $rel['cdate'])) / 604800;
	$HowManyWeeks = round($HowManyWeeks);
	$HowManyWeeks = ($HowManyWeeks=='0')?'0':$HowManyWeeks;
	$delivery_week = $HowManyWeeks .' - '. ($HowManyWeeks+1) . ' WEEKS';

	$us_sql = "select us.*,type.usertype_name from users as us
	left join tbl_usertype as type on type.usertype_id = us.user_type 
	where us.user_id='".$rel['user_id']."'";
	$user_rel=brp_mysqli_fetch_assoc($dbcon->query($us_sql));
//Company Data
/*$comp_qry="select * from tbl_company as comp 
where comp.company_id=".$rel['company_id'];
*/
$set="select comp.*,state.state_name,state.gst_state_code,city.city_name from tbl_company as comp 
left join state_mst as state on comp.stateid=state.stateid 
left join city_mst as city on city.cityid = comp.city_id
where company_id=".$rel['company_id'];
//CHEMITEK PROCESS EQUIPMENTS PRIVATE LIMITED		
$comp_rel=mysqli_fetch_assoc($dbcon->query($set));

if($rel['consignee_id']==0){
	$bill_to_party = '<br>
	<strong>'.$rel['vender_name'].'</strong><br>
	'.$rel['vender_address'].' '.$rel['city_name'].'-'.$rel['pincode'].', '.$rel['state_name'].','.$rel['country_name'].'<br>
	GST NO.: '.$rel['tin_no'].' <br>
	IEC No: '.$rel['iec_no'].'<br>';

	
	$consigni_to_party = $bill_to_party;
}else{
	$bill_to_party = '<br>
	<strong>'.$rel['vender_name'].'</strong><br>
	'.$rel['vender_address'].' '.$rel['city_name'].'-'.$rel['pincode'].', '.$rel['state_name'].','.$rel['country_name'].'<br>
	GST NO.: '.$rel['tin_no'].' <br>
	IEC No: '.$rel['iec_no'].'<br>';

	$consigni_to_party = '<br>
	<strong>'.$rel['cons_name'].'</strong><br>
	'.$rel['cons_address'].' '.$rel['cons_city'].'-'.$rel['cons_pin'].', '.$rel['cons_state'].','.$rel['cons_country'].'<br>
	GST NO.: '.$rel['cons_gst'].' <br>';
}
/* Check Discount is On or off Start */
if($comp_rel['show_disc']=='1'){
	$colspan=5;
	$dynamicwidth=40;
}else{
	$colspan=6;
	$dynamicwidth=46;
}
/* Check Discount is On or off End */
$sundrytax=$dbcon->query("select b.* from tbl_bill_sundry_transaction as b where b.sundry_voucher_id=".$rel['sales_order_id']." and b.sundry_voucher_table='tbl_sales_order' and b.isdelete='0' ");
//$sundrytax_rels = mysqli_fetch_assoc($sundrytax);
$total_sundrytax=0;
while($sumsundrytax=mysqli_fetch_assoc($sundrytax)){
	$total_sundrytax = $total_sundrytax + $sumsundrytax['sundry_gst_amount'];
}
$companyConfiguration=getCompanyConfiguration($dbcon);
$purchase_pro_search=$companyConfiguration['purchase_pro_print'];
$pro_search=explode(",", $purchase_pro_search);

$designation='';
if(!empty($rel['cust_contact_person_designation'])){
	$designation = '('.$rel['cust_contact_person_designation'].')';
}
/*echo "select GROUP_CONCAT(DISTINCT quot.quotation_no,', ') as qtn_no from tbl_sales_ordertrn as trn  
left join tbl_quotation as quot on quot.quotation_id = trn.quotation_id
where trn.sales_ordertrn_status=0 and trn.sales_order_id=".$rel['sales_order_id']." group by trn.quotation_id";exit;*/
$query_quotation_no = $dbcon ->query("select GROUP_CONCAT(DISTINCT quot.quotation_no,' ') as qtn_no from tbl_sales_ordertrn as trn  
left join tbl_quotation as quot on quot.quotation_id = trn.quotation_id
where trn.sales_ordertrn_status=0 and trn.sales_order_id=".$rel['sales_order_id']." group by trn.sales_order_id");
$rel_quot = brp_mysqli_fetch_array($query_quotation_no);
$html='';
/*$header ='
<table>
<tr>
<td colspan="3" style="border: 0px; "><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:2.27in;padding-top:25px;" /></td>
<td colspan="6" style="text-align:center;border: 0px;">
<span style="font-size:16px;"><b>Purchase Order</b></span><br/>
<span style="font-size:16px;"><b>'.$comp_rel["company_name"].' </b></span><br/>
<span style="font-size:12px;">'.$comp_rel["address"].'</span>
</td>
</tr>
</table>';*/

$qry_disc=$dbcon->query("select SUM(trn.product_discount) as discount FROM `tbl_purchaseordertrn` as trn WHERE trn.purchaseordertrn_status=0 and purchaseorder_id=".$rel['purchaseorder_id']);
	$get_disc = brp_mysqli_fetch_assoc($qry_disc);
	if($get_disc['discount']>0){
		$colspan_8 = 9;
	}else{
		$colspan_8 = 8;
		
	}

$header = '';
//$header = get_header($dbcon,'text-align: left','200px','50px');

        $header = '<table style="font-size:13px;border-collapse: collapse; width:100%; border:none !important" >
        	<tr>
				<td colspan="4" style="vertical-align:top;border:none !important; text-align:left;height:10px"></td>
			</tr>
			<tr>
				<td colspan="3" style="vertical-align:top;border:none !important; text-align:left"><h2>'.$comp_rel['company_name'].'</h2></td>

				<td rowspan="5" style="vertical-align:top;border:none !important;text-align:right"><img src="'.DOMAIN_F.LOGO.$comp_rel['logo'].'" style="height: 60px; width: 100px;"></td>
			</tr></table>';
		$head = '<table style="font-size:13px;border-collapse: collapse; width:100%; border:none !important;border-bottom:1px solid !important" >
			<tr>
				<td colspan="4" style="vertical-align:top;border:none !important; text-align:left;height:10px"></td>
			</tr>
			<tr>
				<td colspan="3" style="vertical-align:top;border:none !important; text-align:left"><h2>'.$comp_rel['company_name'].'</h2></td>

				<td rowspan="5" style="vertical-align:top;border:none !important;text-align:right"><img src="'.DOMAIN_F.LOGO.$comp_rel['logo'].'" style="height: 60px; width: 100px;"></td>
			</tr>
			<tr>
				<td colspan="3" style="border:none !important">'.$comp_rel['address'].' '.$comp_rel['city_name'].'-'.$comp_rel['pincode'].','.$comp_rel['state_name'].',INDIA</td>
			</tr>
			<tr>
				<td colspan="3" style="border:none !important">Phone : '.$comp_rel['contact_no'].'</td>
			</tr>
			<tr>
				<td colspan="3" style="border:none !important">Web : '.$comp_rel['company_website'].'</td>
			</tr>
			<tr>
				<td style="border:none !important">Gst No : '.$comp_rel['vatno'].'</td>
				<td style="border:none !important">Lut No : '.$comp_rel['lut_no'].'</td>
				<td style="border:none !important">IEC No : '.$comp_rel['iec_no'].'</td>
			</tr>
			</table>';
		$footer ='<table width="100%" style="border:none !important;border-top:2px solid;">

		<tr stye="border:none !important;">
		<td style="text-align:left;vertical-align:top;width:50%; bold;border:none !important;border-top:1px solid !important">
			<strong>'.$rel['sales_order_no'].' - DTD : '.$sales_order_date.'</strong>
		</td>
		<td style="text-align:right;vertical-align:top;width:50%; bold;border:none !important;border-top:1px solid !important"> <strong>Page {PAGENO} of {nbpg}</strong></td>
		</tr>
		</table>';

$html.='<html>
<head>					
<title>Sales Order - '.$rel['sales_order_no'].'</title>

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
		/*border:1px solid #000 !important;*/
		/*page-break-inside:avoid;*/
	}
	.quot_annex_content_div table tr,td{
		padding:2px 5px;
	}
	.blueHeading {
		color: #365f91;
	}

	</style>
	</head>
	<body>
	<!--Show Logo in other pages-->
	<!--<htmlpageheader name="otherpages" style="display:none">
	<div style="text-align:center">'.$head.'</div>
	</htmlpageheader>-->
	<htmlpagefooter name="otherpages_footer" style="display:none">
	<div style="text-align:center">'.$footer.'</div>
	</htmlpagefooter>
	<!--<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>-->
	<div>
	
	<table style="font-size:13px;border-collapse: collapse;width:100%;border:none !important" >
	    
		<tr>
			<td colspan="6" style="text-align:center; font-size:18px; padding-bottom:0px;border:none "> <strong>Order Acceptance</strong>
			</td>
		</tr>
		<tr>
			<td colspan="6" style="text-align:center; font-size:18px; padding-bottom:0px;border:none;height:5px "> 
			</td>
		</tr>
	</table>';
	
	$html .='<table width="100%" style="font-size:13px;border-collapse: collapse;width:100%;border:none !important;" cellpadding="3" cellspacing="3">
		
		<tr>
			<td style="width:49%;vertical-align:top !important;border:1px solid !important;">
				<strong>Bill To : </strong>'.$bill_to_party.'
			</td>
			<td style="width:2%;border:none !important;"></td>
			<td style="width:49%;vertical-align:top;border:1px solid !important;">
				<strong>Ship To : </strong>'.$consigni_to_party.'
			</td>
		</tr>
	</table>';
		
	$html.='<table style="font-size:14px;border-collapse: collapse;width:100%;border : 1px solid" cellpadding="3" cellspacing="3">
			<tr>
				<td colspan="6" style="border:none !important;height:15px;"></td>
			</tr>
			<tr>
				<td style="text-align:left; width:40%;border:1px solid !important;white-space:nowrap"> 
				Kind Attn. : '.$rel['cust_contact_person_name'].' '.$designation.'
				</td>
				<td style="width:30%;text-align:left;border:1px solid !important;white-space:nowrap"> 
				Email : '.$rel['cust_contact_person_email'].'
				</td>
				<td style="width:30%;text-align:left;border:1px solid !important; white-space:nowrap"> 
				Contact No : '.$rel['cust_contact_person_no'].'
				</td>
			</tr>
			<tr>
				<td colspan="6" style="border:none !important;height:15px;"></td>
			</tr>
		</table>
	</div>';
	
	
	
	
	/*$qry_des="SELECT trn.*, product.product_icode, product.product_name,product.product_icode, dr.drawing_number, product.product_alias_name, per.unit_name,cper.unit_name as conv_unit FROM `tbl_sales_ordertrn` as trn 
	left join product_mst as product on product.product_id=trn.product_id 
	left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
	left join unit_mst as per on per.unitid=trn.unit_id
	left join unit_mst as cper on cper.unitid=trn.conv_unit_id
	where trn.sales_ordertrn_status=0 and trn.sales_order_id=".$rel['sales_order_id']." group by trn.sales_ordertrn_id order by trn.sales_ordertrn_id";	

	$trn_qry_rs_des=$dbcon->query($qry_des);
	
	while($row_des = brp_mysqli_fetch_array($trn_qry_rs_des)){
	    $product_desc = ($row_des['description']) ? $row_des['description'] : '';
	    $html .='<div>'.$product_desc.'</div>';
	}*/
	
	
	
	$html.='<table style="font-size:14px;border-collapse: collapse;width:100%;border:1px solid !important;" cellpadding="3" cellspacing="3">
	<thead>
	<tr>
	<td style=" text-align:center;border:1px solid !important; width:5%">Sr No. </td>
	<td  style=" text-align:center;border:1px solid !important; width:25%">Product Detail</td>
	<td  style=" text-align:center;width:10%;border:1px solid !important;">GST</td>
	<td colspan="2"  style=" text-align:center;border:1px solid !important; width:10%;">HSN Code </td>
	
	
	<td style=" text-align:center;border:1px solid !important; width:10%;">Qty </td>
	<td style=" text-align:center;border:1px solid !important; width:8%;">UOM </td>';
	//$html.='<td style="text-align:center;border:1px solid !important;">Discount</td>';
	$html.='<td style="text-align:center;border:1px solid !important; width:10%;">Unit Rate</td>
	<td style="text-align:center;border:1px solid !important; width:10%;">Taxable Amount '.$rel['currency_code'].'</td>
	</tr>
	</thead>
	</table>';
	$qry="SELECT trn.*, product.product_icode, product.product_name,product.product_icode, dr.drawing_number, product.product_alias_name, per.unit_name,cper.unit_name as conv_unit FROM `tbl_sales_ordertrn` as trn 
	left join product_mst as product on product.product_id=trn.product_id 
	left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
	left join unit_mst as per on per.unitid=trn.unit_id
	left join unit_mst as cper on cper.unitid=trn.conv_unit_id
	where trn.sales_ordertrn_status=0 and trn.sales_order_id=".$rel['sales_order_id']." group by trn.sales_ordertrn_id order by trn.sales_ordertrn_id";	

	$trn_qry_rs=$dbcon->query($qry);
	$p=1;$ttl_amt=0;$ttl_qty=0;$total_cs_gst=0;$total_i_gst=0;$gst_per=0;$gst_rate=0;
	$cnt=brp_mysqli_num_rows($trn_qry_rs);
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
		$product_desc = ($trn_rel['description']) ? $trn_rel['description'] : '';
		
		$path='view/upload/product_images/';
		$html.='<table style="font-size:14px;border-collapse: collapse;width:100%;border:1px solid !important;border-bottom:none !important;" cellpadding="3" cellspacing="3">
		<tr>
		<td style="text-align:center;font-size:12px;border:1px solid !important;border-bottom:none !important;width:5%">'.$p.' </td>
		<td style="border:1px solid !important;border-bottom:1px solid;text-align:left;font-size:12px;border-bottom:none !important; width:25%">'.$trn_rel['product_name'].' <br> Item Code : '.$item_code.' <br> ';
		if($delivery_type == 'product_wise'){
			$retu_date = "select sdate.*,unit.unit_name from tbl_salesorder_delivery_date as sdate left join unit_mst as unit on unit.unitid=sdate.unit_id where po_delivery_date_status=0 and sales_ordertrn_id=".$trn_rel['sales_ordertrn_id'];
			$resadate=$dbcon->query($retu_date);
			
			while($rowdate=brp_mysqli_fetch_array($resadate)){		
				$html .='<strong>Delivery Date : </strong>'.date('d-m-Y',strtotime($rowdate['delivery_date'])).' / <strong>Qty : </strong>'.$rowdate['product_qty'].' '.$rowdate['unit_name'].'<br>';		
			}
			
		}
		$html .='</td>
		<td style=" text-align:center;font-size:12px;border:1px solid !important;border-bottom:none !important;width:10%;"> '.$gst_per.' %</td>
		<td colspan="2" style=" text-align:center;font-size:12px;border:1px solid !important;border-bottom:none !important;width:10%;"> '.$trn_rel['product_hsn_code'].'</td>
		
		<td style=" text-align:right;font-size:12px;border:1px solid !important;width:10%;border-bottom:none !important;">'.$sqty.' </td>
		<td style=" text-align:center;font-size:12px;border:1px solid !important;width:8%;border-bottom:none !important;">'.$unit_name.' </td>';
			
		$disc_rate = ($trn_rel['product_rate_conv']*$trn_rel['discount_per'])/100;
		$net_rate = $trn_rel['product_rate_conv']-$disc_rate;
		//$html.='<td style=" text-align:center;border:1px solid !important;">'.without_comma_two_digit_amount($trn_rel['discount_per']).' %</td>';
			
			$html.='<td style=" text-align:right;font-size:12px;border:1px solid !important;width:10%;border-bottom:none !important;">'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($net_rate).'</td>
			<td style=" text-align:right;font-size:12px;border:1px solid !important;width:10%;border-bottom:none !important;">'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($trn_rel['product_amount_conv']).'</td>
			</tr>
			</table>';
			
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
	$pr=10-$cnt;

	$html.='<!--<tr>
		<td colspan="11" style="height:25px">The Material Should Be Sent As Per The Qty in Nos Mentioned In Purchase Order. In Case Of Variable UOM <br> i.e. Kgs. / Ltr / Mtr / Feet etc., The Amount Of Purchase Order Will Be Approximate And It Will Be Accepted On Actual Basis.</td>
	</tr>-->
	<table style="font-size:14px;border-collapse: collapse;width:100%;border:1px solid !important;" cellpadding="3" cellspacing="3">
	<tr>
	<td style="border:1px solid !important;width:80%"></td>
	<td style=" text-align:right;font-size:12px;border:1px solid !important;width:10%">TOTAL  </td>
	
	<td  style=" text-align:right;font-size:12px;border:1px solid !important;width:10%">'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($ttl_amt,2).'</td>
	</tr>
	';
	$qry11="select sum((tc.tax_per*trn.product_amount_conv)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_sales_ordertrn as trn 
	left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
	left join tbl_ledger as l on l.l_id=tc.tax_id 
	where tc.tax_additional='1' and trn.sales_order_id=".$rel['sales_order_id']." and trn.sales_ordertrn_status!=2 and tc.isdelete='0' group by tc.tax_id";
	$result11=$dbcon->query($qry11);		
	while($row11=mysqli_fetch_assoc($result11))
	{
		$html.='<tr>
		<td style=" text-align:right;font-size:12px;border:1px solid !important;"></td>
		<td style=" text-align:right;font-size:12px;border:1px solid !important;"><b>'.$row11['l_name'].'</b></td>
		<td style="text-align:center;border:1px solid !important;"><b>
		'.$rel['currency_symbol'].' '.number_format($row11['add_sum'],2,".","").'
		</b></td>
		</tr>';
	}
	$qry12="select b.sundry_amount_conv,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
	from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
	left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
	where b.sundry_voucher_id=".$rel['sales_order_id']." and b.sundry_voucher_table='tbl_sales_order' and b.isdelete='0' and le.default_sundry='0'";
	$result12=$dbcon->query($qry12);		
	while($row12=mysqli_fetch_assoc($result12))
	{
		$html.='<tr>
		<td style=" text-align:right;font-size:12px;border:1px solid !important;"></td>
		<td style=" text-align:right;font-size:12px;border:1px solid !important;">'.$row12['l_name'].'</td>
		<td style="text-align:right;font-size:12px;border:1px solid !important;">'.$rel['currency_symbol'].' '.number_format($row12['sundry_amount_conv'],2,".","").'</td>
		</tr>';
	}
	if($rel['stateid']==$comp_rel['stateid']){
		$total_cs_gst = $total_cs_gst + $total_sundrytax;
		$html.='<tr>
		<td style=" text-align:right;font-size:12px;border:1px solid !important;"></td>
		<td style=" text-align:right;font-size:12px;border:1px solid !important;">CGST</td>
		<td  style=" text-align:right;font-size:12px;border:1px solid !important;">'.$rel['currency_symbol'].' '.number_format(($total_cs_gst/2),2,".","").'</td>
		</tr><tr>
		<td style=" text-align:right;font-size:12px;border:1px solid !important;"></td>
		<td style=" text-align:right;font-size:12px;border:1px solid !important;">SGST</td>
		<td style=" text-align:right;font-size:12px;border:1px solid !important;">'.$rel['currency_symbol'].' '.number_format(($total_cs_gst/2),2,".","").'</td>
		</tr>';
	}else{
		$total_i_gst = $total_i_gst + $total_sundrytax;
		$html.='<tr>
		<td style=" text-align:right;font-size:12px;border:1px solid !important;"></td>
		<td style=" text-align:right;font-size:12px;border:1px solid !important;">IGST</td>
		<td style=" text-align:right;font-size:12px;border:1px solid !important;">'.$rel['currency_symbol'].' '.number_format(($total_i_gst),2,".","").'</td>
		</tr>';
	}		
	
    //$round_off = round($final_total)-$final_total;
	$round_off = 0;
	$currency_code = getcurrencydetail($dbcon,$rel['currency_id']);
	$html .= '
	<tr>
	<td style=" text-align:left;font-size:12px;border:1px solid !important;"><span style="font-size:13px;"><strong>Amount in words :</strong> '.convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$currency_code['currency_in_word_end'],$currency_code['currency_in_word']).'</span></td>
	<td style=" text-align:right;font-size:13px;border:1px solid !important;">Total </td>
	<td style=" text-align:right;font-size:13px;border:1px solid !important;">'.$rel['currency_symbol'].' '.without_comma_two_digit_amount(($rel['g_total_conv']),2).' </td>
	</tr>';
	
	$html.='</tbody></table>';
	
	$html.='<table>
		<tr style="">
		<td colspan="2" rowspan="2" style="font-size:13px;width:100px;text-align:center;border:1px solid !important;">
		HSN/SAC
		</td>
		<td colspan="2" rowspan="2" style="font-size:13px;width:100px;text-align:center;border:1px solid !important;">
		Taxable Value
		</td>';
		if($rel['stateid']==$comp_rel['stateid']){
			$html.='<td colspan="2" style="font-size:13px;text-align:center;border:1px solid !important;">SGST
			</td>
			<td colspan="2" style="font-size:13px;text-align:center;border:1px solid !important;">CGST
			</td>';
		}else{
			$html.='<td colspan="2" style="font-size:13px;text-align:center;border:1px solid !important;">IGST
			</td>';
		}
		$html.='<td colspan="2" rowspan="2" style="font-size:13px;text-align:center;width:100px;border:1px solid !important;">
		Total Tax Amount
		</td>
		</tr>';
		if($rel['stateid']==$comp_rel['stateid']){
			$html.='<tr>
			<td style="font-size:12px;text-align:center;border:1px solid !important;">Rate</td>
			<td style="font-size:12px;text-align:center;border:1px solid !important;">Amount</td>
			<td style="font-size:12px;text-align:center;border:1px solid !important;">Rate</td>
			<td style="font-size:12px;text-align:center;border:1px solid !important;">Amount</td>
			</tr>';
		}else{
			$html.='<tr>
			<td style="font-size:12px;text-align:center;border:1px solid !important;">Rate</td>
			<td style="font-size:12px;text-align:center;border:1px solid !important;">Amount</td>
			</tr>';
		}
		
		 $trn_qry_tax="select sum(product_amount_conv) as product_amount,product_hsn_code,cgst_tax_per,sum(cgst_tax_rate_conv) as cgst_tax_rate,sgst_tax_per,sum(sgst_tax_rate_conv) as sgst_tax_rate,igst_tax_per,sum(igst_tax_rate_conv) as igst_tax_rate FROM `tbl_sales_ordertrn` where sales_ordertrn_status=0 and sales_order_id='".$rel['sales_order_id']."' group by product_hsn_code";

		$trn_qry_rs_tax=$dbcon->query($trn_qry_tax);

		$total_amount_sum = 0;
		$total_tax_sum = 0;
		while($trn_rel_tax=brp_mysqli_fetch_assoc($trn_qry_rs_tax)){

			$total1+=$row_total=$trn_rel_tax['cgst_tax_rate']+$trn_rel_tax['sgst_tax_rate']+$trn_rel_tax['igst_tax_rate'];

			$html.='<tr style="">
			<td colspan="2" style="text-align:center;font-size:12px;border:1px solid !important;">
			'.$trn_rel_tax['product_hsn_code'].'
			</td>
			<td colspan="2" style="text-align:right;font-size:12px;border:1px solid !important;">
			'.$currency_symbol.' '.without_comma_two_digit_amount($trn_rel_tax['product_amount']).'
			</td>';
			$total_taable_amount = 0;

			$k = 1;

			if($rel['stateid']==$comp_rel['stateid']){
				$html.='<td style="text-align:right;font-size:12px;border:1px solid !important;">
				'.str_replace("CGST","",$trn_rel_tax['cgst_tax_per']).'
				</td>
				<td style="text-align:right;font-size:12px;border:1px solid !important;">
				'.$currency_symbol.' '.number_format($trn_rel_tax['cgst_tax_rate'],2,".","").'
				</td>
				<td style="text-align:right;font-size:12px;border:1px solid !important;">
				'.str_replace("SGST","",$trn_rel_tax['cgst_tax_per']).'
				</td>
				<td style="text-align:right;font-size:12px;border:1px solid !important;">
				'.$currency_symbol.' '.number_format($trn_rel_tax['sgst_tax_rate'],2,".","").'
				</td>';
				$k++;

				$total_taxable_amount = $total_taxable_amount+$total_cs_gst;
				$tax_array[$rel15['tx_tax_id']][] = $texAmount;
			}else{
				$html.='<td style="text-align:right;font-size:12px;border:1px solid !important;">
				'.str_replace("IGST","",$trn_rel_tax['igst_tax_per']).'
				</td>
				<td style="text-align:right;font-size:12px;border:1px solid !important;">
				'.$currency_symbol.' '.number_format($trn_rel_tax['igst_tax_rate'],2,".","").'
				</td>';
				$k++;

				$total_taxable_amount = $total_taxable_amount+$total_i_gst;
				$tax_array[$rel15['tx_tax_id']][] = $texAmount;
			}

			$total_tax_sum = $total_tax_sum + $total_taxable_amount; 
			$html.='<td colspan="2" style=" text-align:right;font-size:12px;border:1px solid !important;">
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
				<td colspan="2" style="vertical-align:top;text-align:center;font-size:12px;border:1px solid !important;" >
				'.$sundry_tax['ledger_hsn'].'
				</td>
				<td colspan="2" style="vertical-align:top;text-align:right;font-size:12px;border:1px solid !important;" >
				'.$currency_symbol.' '.$sundry_tax['sundry_amount_conv'].'
				</td>';
				
				if($rel['stateid']==$comp_rel['stateid'])
				{
					$sun_gst_per = $sundry_tax['sundry_gst_per']/2;
					$sun_gst_amt = $sundry_tax['sundry_gst_amount_conv']/2;
					$html.='<td style="vertical-align:top;text-align:right;border:1px solid !important;font-size:12px;" >
					'.$sun_gst_per.'
					</td>
					<td style="vertical-align:top;text-align:right;border:1px solid !important;font-size:12px;" >
					'.$currency_symbol.' '.$sun_gst_amt.'
					</td>
					<td style="vertical-align:top;text-align:right;border:1px solid !important;font-size:12px;" >
					'.$sun_gst_per.'
					</td>
					<td style="vertical-align:top;text-align:right;border:1px solid !important;font-size:12px;" >
					'.$currency_symbol.' '.$sun_gst_amt.'
					</td>';
				}
				else if($rel['stateid']!=$comp_rel['stateid'])
				{
					
					$html.='<td style="vertical-align:top;text-align:right;border:1px solid !important;font-size:12px;" >
					'.$sundry_tax['sundry_gst_per'].'
					</td>
					<td style="vertical-align:top;text-align:right;border:1px solid !important;font-size:12px;" >
					'.$currency_symbol.' '.$sundry_tax['sundry_gst_amount_conv'].'
					</td>';
				}
				$html.='<td  colspan="2" style="vertical-align:top;text-align:right;border:1px solid !important;font-size:12px;" >
				'.$currency_symbol.' '.$sundry_tax['sundry_gst_amount_conv'].'
				</td>';

				$html.='</tr>';
				$total_sunamt+=$sundry_tax['sundry_amount_conv'];
				$total_suntaxamt1+=$sundry_tax['sundry_gst_amount_conv']/2;
				$total_suntaxamt2+=$sundry_tax['sundry_gst_amount_conv'];

			}
		}
		$html.='<tr style="">
		<td colspan="2" style="text-align:center;font-size:13px;font-weight:bold;border:1px solid !important;">
		Total
		</td>
		<td colspan="2" style="text-align:right;font-size:13px;font-weight:bold;border:1px solid !important;">
		'.$currency_symbol.' '.number_format($totalamt+$total_sunamt,2).'
		</td>';
		if($rel['stateid']==$comp_rel['stateid']){
			$html.='<td style="text-align:right;font-size:13px;font-weight:bold;border:1px solid !important;" colspan="2">
			'.$currency_symbol.' '.number_format($totaltaxamt1+$total_suntaxamt1,2).'
			</td>
			<td style="text-align:right;font-size:13px;font-weight:bold;border:1px solid !important;" colspan="2">
			'.$currency_symbol.' '.number_format($totaltaxamt2+$total_suntaxamt1,2).'
			</td>
			';
		} else{
			$html.='<td  style="text-align:right;font-size:13px;font-weight:bold;border:1px solid !important;" colspan="2">
			'.$currency_symbol.' '.number_format($totaltaxamt3+$total_suntaxamt1,2).'
			</td>
			';
		}
		$html.='<td colspan="2" style=" text-align:right;font-size:13px;font-weight:bold;border:1px solid !important;">
		'.$currency_symbol.' '.number_format($total1+$total_sun1,2).'
		</td>
		</tr>
		</table>';
	
	
	$html .='<table style="font-size:13px;border-collapse: collapse;width:100%;border:none !important" >
			<tr>
			    <td colspan="5" style="text-align:left;vertical-align:top;border:none;width:100%;height:20px"> </td>
			</tr>
			<tr>
				<td style="text-align:left;vertical-align:top;border:1px solid;width:20%;"> 
					O.A. No
				</td>
				<td style="text-align:left;vertical-align:top;border:1px solid;width:30%;"> 
				<strong>'.$rel['sales_order_no'].'</strong>
				</td>
				<td style="width:15px;border:none !important"></td>
				<td style="text-align:left;border:1px solid;width:20%;"> 
				Transport Mode
				</td>
				<td style="text-align:left;border:1px solid;width:30%;"> 
				'.$rel['transportation_name'].'
				</td>
			</tr>

			<tr>
				<td style="text-align:left;vertical-align:top;border:1px solid;width:20%;"> 
					O.A. Date
				</td>
				<td style="text-align:left;vertical-align:top;border:1px solid;width:30%;"> 
				'.$sales_order_date.'
				</td>
				<td style="border:none !important"></td>
				<td style="text-align:left;border:1px solid;"> 
				Delivery Date
				</td>
				<td style="text-align:left;border:1px solid;"> 
				'.$delivery_date.'
				</td>
			</tr>

			<tr>
				<td style="text-align:left;vertical-align:top;border:1px solid;width:20%;"> 
					Quotation No
				</td>
				<td style="text-align:left;vertical-align:top;border:1px solid;width:30%;"> 
				'.$rel_quot['qtn_no'].'
				</td>
				<td style="border:none !important"></td>
				<td style="text-align:left;border:1px solid;white-space:nowrap"> 
				Customer PO No
				</td>
				<td style="text-align:left;border:1px solid;"> 
				'.$rel['po_no'].'
				</td>
			</tr>

			<tr>
				<td style="text-align:left;vertical-align:top;border:1px solid;width:20%;"> 
					Payment Terms
				</td>
				<td style="text-align:left;vertical-align:top;border:1px solid;width:30%;"> 
				'.$rel['payment_term_name'].'
				</td>
				<td style="border:none !important"></td>
				<td style="text-align:left;border:1px solid;"> 
				Customer PO Date
				</td>
				<td style="text-align:left;border:1px solid;"> 
				'.$po_date.'
				</td>
			</tr>
			
		</table>';

	$terms_qry="select qtrm.*,mst.tc_name from tbl_salesorder_terms_trn as qtrm 
	left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
	where qtrm.quotation_terms_trn_status=0 and qtrm.sales_order_id=".$rel['sales_order_id']." order by qtrm.tc_priority";
	$terms_qry_rs=$dbcon->query($terms_qry);
	if($t_cnt = mysqli_num_rows($terms_qry_rs)){
		$t=1;
		$html.='<div>
		<table style="font-size:14px;border-collapse: collapse;width:100%;border:none !important" cellpadding="5" cellspacing="5">
		<thead>
		<tr style="border-bottom:none !important">
			<th colspan="2" style="text-align:center;"></th>
		</tr>
		<tr style="border-bottom:none !important">
			<th colspan="2" style="text-align:center;">COMMERCIAL TERMS & CONDITIONS</th>
		</tr>
		</thead><tbody>';
		while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
			$string=(nl2br($term_rel['tc_details']));
			

			$html.='<tr>
			<!--<td style="width:25%;text-align:left; font-weight: bold; vertical-align: top;">'.$term_rel['tc_name'].'</td>-->
			<td style="width:100%;text-align:left;border:none !important">'.$string.'</td>
			</tr>';
			$t++;
		}
		$html.='</tbody></table></div>'; 
	}

	$html.='<table style="font-size:14px;border-collapse: collapse;width:100%;border:none !important" cellpadding="5" cellspacing="5">
		<tr>
			<td style="border:none !important;height:25px"></td>
		</tr>

		<tr>
			<td style="border:none !important">
				<strong>For,</strong> '.$comp_rel['company_name'].'<br>
				'.$user_rel['user_name'].' ('.$user_rel['usertype_name'].')<br>
				 Contact No : '.$user_rel['user_phone'].' <br> Email : '.$user_rel['user_mail'].'
			</td>
		</tr>
	</table>';
	
	/* Get Terms And Condition Start */

	$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
/*echo $head;
echo $html;echo $footer;exit;*/
ob_end_clean();
include("../../view/export/mpdf/mpdf.php");
$mpdf=new mPDF('','A4','0','calibri','10','10','33','10','1','1');
//		$mdf->SetFont('ProximaNova');
$mpdf->defaultheaderfontsize = 10; /* in pts */
$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
$mpdf->defaultfooterfontsize = 10; /* in pts */
$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
$mpdf->SetHTMLHeader($head);
$mpdf->SetHTMLFooter($footer);
$mpdf->SetWatermarkText();
$mpdf->showWatermarkText = true;
$mpdf->allow_charset_conversion=true;
$mpdf->charset_in='UTF-8';
$mpdf->WriteHTML($html);
$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
ob_clean();
return 'Purchase Order'.$rel['purchaseorder_no'].'.pdf';
}	
?>
