<?php 
session_start();
$path = '../../';
$incPath = $path.'include/';
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once("../../crm/include/crm_common_functions.php");
include_once(COMMON_FUNCTION_PATH."common_production_functions.php");
include_once(COMMON_FUNCTION_PATH."common_production_store_wise_function.php");

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	SALES_ORDER_SLUG_PRINT,
	SALES_ORDER_SLUG_READ
]);

if(!in_array(SALES_ORDER_SLUG_PRINT,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}

$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$_SESSION['contents']='';
$form="Sales Order";
$mode="Print";
$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
$type='pdf';
if(strtolower($type) == 'pdf') {
	$query="select sor.*,quo.quotation_ref,quo.quotation_no,quo.quotation_date,per.c_con_mobile,per.c_con_email,country.country_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust.cust_pincode,cust.cust_mobile,cust.gst_no,cust.cust_email from tbl_sales_order as sor 
	left join tbl_quotation as quot on quot.quotation_id = sor.quotation_id
	left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
	left join tbl_ledger as cust on cust.l_id=sor.cust_id
	left join tbl_cust_contact_person as cust_con on cust_con.cust_id = sor.cust_id
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid
	left join city_mst as city on city.cityid=cust.cityid 
	left join tbl_quotation quo on sor.quotation_id=quo.quotation_id
	where sor.sales_order_id=$invoiceid";
	$rel=mysqli_fetch_assoc($dbcon->query($query));	
        //p($rel);
	$company_name=$rel['company_name'];
	$cust_address=$rel['cust_address'];
	$city_name=$rel['city_name'];
	$state_name=$rel['state_name'];
	$country_name=$rel['country_name'];
	$cust_pincode=$rel['cust_pincode'];
	$gst_no=$rel['gst_no'];
	
	$set="select * from tbl_company where company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	$order_date='';$dispatch_date='';
	
	
	if(!$rel){
		header("Location: ".ROOT.CRM_ROOT."sales_order_list");
	}
//Company Data
/*$comp_qry="select * from tbl_company as comp 
where comp.company_id=".$rel['company_id'];
*/
$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];

$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
$header ='<img src="'.DOMAIN_F.LOGO.$comp_rel['logo'].'" style="" />';  
//$header =$comp_rel["logo"];
//$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';

$approve_status='';
if($rel['approve_status']=='0'){
	$approve_status=' (DRAFT)';
}


$html ='<html>
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
	<td colspan="3" style="text-align:center;font-size:15px;font-weight:bold;"> 
	Sales Order(Order Acceptance)
	</td>
	</tr>
	<tr>
	<td rowspan="6" style="text-align:left;vertical-align:top;border:1px solid;width:50%;"> 
	<strong style="font-size:16px">Billed To</strong><br/>
	<strong style="font-size:16px">'.$company_name.'</strong><br/>
	'.$cust_address.'<br/>
	Phone No : '.$rel['cust_mobile'].'<br/>
	<strong>GST no - '.$gst_no.'</strong>              

	</td>
	<td style="text-align:left;border:1px solid;width:20%;font-size:16px"> 
	<strong>Sales Order No</strong>
	</td>
	<td style="text-align:left;border:1px solid;width:30%;font-size:16px;"> 
	<strong>'.$rel['sales_order_no'].'</strong>
	</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;"> 
	Sales Order Date
	</td>
	<td style="text-align:left;border:1px solid;"> 
	'.date("d-M-Y",strtotime($rel['sales_order_date'])).'
	</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;"> 
	Our Quotation reference
	</td>
	<td style="text-align:left;border:1px solid;"> 
	'.$rel['quotation_no'].'
	</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;"> 
	Quotation Date
	</td>
	<td style="text-align:left;border:1px solid;"> 
	'.date('d-M-Y', strtotime($rel['quotation_date'])).'
	</td>
	</tr>
	';

	$user_qry = "select user_name,user_mail,user_phone from users where user_id=".$_SESSION['user_id']." and company_id=".$rel['company_id'];
	$user_data = mysqli_fetch_assoc($dbcon->query($user_qry));

	$html .= '<tr>
	<td style="text-align:left;border:1px solid;"> 
	Contact Person
	</td>
	<td style="text-align:left;border:1px solid;"> 
	'.$user_data['user_name'].'
	</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid ;"> 
	Contact Person Contact No
	</td>
	<td style="text-align:left;border:1px solid;"> 
	'.$user_data['user_phone'].'
	</td>
	</tr>
	<tr>
	<td rowspan="6" style="text-align:left;vertical-align:top;border:1px solid;width:50%;"> 
	<strong style="font-size:16px">Shipped To,</strong>
	<strong style="font-size:16px">'.$rel['cust_name'].'</strong><br/>

	<strong style="font-size:14px">'.$company_name.'</strong><br/>
	'.$cust_address.'<br/>
	Phone No : '.$rel['cust_mobile'].'<br/>
	<strong>GST no - '.$gst_no.'</strong>       

	</td>
	<td style="text-align:left;border:1px solid;"> 
	Project Description 
	</td>
	<td style="text-align:left;border:1px solid;"> 
	'.(strtolower($rel['quotation_ref'])).'
	</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;"> 
	Contact Person Email
	</td>
	<td style="text-align:left;border:1px solid;"> 
	'.(strtolower($user_data['user_mail'])).'
	</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;"> 
	Buyers PO no
	</td>
	<td style="text-align:left;border:1px solid;"> 
	'.$rel['po_no'].'
	</td>
	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;"> 
	PO Date
	</td>
	<td style="text-align:left;border:1px solid;"> 
	'.date("d-M-Y",strtotime($rel['po_date'])).'

	</td>
	</tr>

	<tr>
	<td style="text-align:left;border:1px solid;"> 
	Buyers Email
	</td>
	<td style="text-align:left;border:1px solid;"> 
	'.$rel['c_con_email'].'

	</td>

	</tr>
	<tr>
	<td style="text-align:left;border:1px solid;"> 
	Buyers Mobile No
	</td>
	<td style="text-align:left;border:1px solid;"> 
	'.$rel['c_con_mobile'].'
	</td>

	</tr>

	</table>
	<!--<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
	
	<tr>
	<th style="width:5%;text-align:center;border:1px solid;">Sr.<Br/> No.</th>
	<th style="width:53%;text-align:center;border:1px solid;">Item Description</th>
	<th style="width:5%;text-align:center;border:1px solid;">HSN Code</th>
	<th style="width:5%;text-align:center;border:1px solid;">Qty</th>
	<th style="width:5%;text-align:center;border:1px solid;">Unit</th>
	<th style="width:10%;text-align:center;border:1px solid;">Unit Price</th>
	<th style="width:10%;text-align:center;border:1px solid;">GST Amount</th>
	<th style="width:15%;text-align:center;border:1px solid;">Total Price</th>
	</tr>
	<tbody>-->';
	$trn_qry="select trn.*,unit.unit_name,pro.product_name,hsn.hsn_code as product_hsn, trn.description from tbl_sales_ordertrn as trn 
	left join tbl_sales_order as sor on sor.sales_order_id=trn.sales_order_id
	left join unit_mst as unit on unit.unitid=trn.unit_id
	left join product_mst pro on pro.product_id = trn.product_id
	left join mst_hsn_code hsn on hsn.hsn_id = pro.product_hsn
	where trn.sales_ordertrn_status=0 and trn.sales_order_id=".$rel['sales_order_id'];
	$trn_qry_rs=$dbcon->query($trn_qry);
	$p=1;$ttl_amt=0;$ttl_qty=0;$pcount=1;$total_cs_gst=0;$total_i_gst=0;$gst_per=0;$gst_rate=0;
	$cnt=mysqli_num_rows($trn_qry_rs);
	while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
		$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
		$gst_rate = $trn_rel['cgst_tax_rate']+$trn_rel['sgst_tax_rate']+$trn_rel['igst_tax_rate'];

		if($trn_rel['cgst_tax_rate'] != 0 || $trn_rel['sgst_tax_rate'] !=0){
			$total_cs_gst += $gst_rate;
		}else{
			$total_i_gst += $gst_rate;
		}
		if($pcount=="1"){
			$html.='<div><table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">

			<tr>
			<th style="width:5%;text-align:center;border:1px solid;">Sr.<Br/> No.</th>
			<th style="width:53%;text-align:center;border:1px solid;">Item Description</th>
			<th style="width:5%;text-align:center;border:1px solid; white-space: nowrap;">HSN Code</th>
			<th style="width:5%;text-align:center;border:1px solid;">Qty</th>
			<th style="width:5%;text-align:center;border:1px solid;">Unit</th>
			<th style="width:10%;text-align:center;border:1px solid;">Unit Price</th>
			<th style="width:10%;text-align:center;border:1px solid;">GST Amount</th>
			<th style="width:15%;text-align:center;border:1px solid;">Total Price</th>
			</tr>
			<tbody>';
		}
		$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
		<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
		<td style="text-align:left;border:1px solid;vertical-align:top;">
		<strong>'.$trn_rel['product_name'].'</strong><br/>
		'.nl2br($trn_rel['description']).'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">
		'.$trn_rel['product_hsn'].'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">
		'.$trn_rel['product_qty'].'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">'.$trn_rel['unit_name'].'</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">'.number_format($trn_rel['product_rate'],2).'</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">'.number_format($gst_rate,2,".","").'</td>';
		$html.='</td>
		<td style="text-align:right;border:1px solid;vertical-align:top;">';
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Extra At Actual";
		}
		else{
			$html.=indian_number($trn_rel['product_amount'],2);
		}
		//$TotalBasic += 	$html;
		$html.='</td>
		</tr>';
		$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
		if($trn_rel['act_amt_flag']!='1'){
			$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
		}
		if($trn_rel['cgst_tax_rate']!="")
			$tax1 += $trn_rel['cgst_tax_rate'];
		if($trn_rel['sgst_tax_rate']!="")
			$tax2 += $trn_rel['sgst_tax_rate'];
		if($trn_rel['igst_tax_rate']!="")
			$tax3 += $trn_rel['igst_tax_rate'];
		if($trn_rel['product_discount']!="")
			$product_discount += $trn_rel['product_discount'];
		
		
		if($cnt==$p){

			$html.='<tr >

			<td rowspan="3" colspan="4">
			<b>Total Tax Amount (In Words): '. ucfirst(convert_number_to_words_new($tax1+$tax2+$tax3)).'</b>
			</td>	
			
			<td  colspan="3"><b>Total Basic </b></td>
			<td style="text-align:right;"><b> '.indian_number($ttl_amt, 2).'</b></td>
			</tr>';
			$totalAmount = $ttl_amt+$tax1+$tax2+$tax3-$product_discount;
			$html.='
			<!--<tr>
			<td  colspan="3">Freight Charges</td>
			<td >50,000.00</td>
			</tr>-->
			<tr>
			<td  colspan="3"><b>Total Amount Before Tax</td>
			<td style="text-align:right;"> <b>'.indian_number($ttl_amt, 2).'</td>
			</tr>
			<tr>
			<td  colspan="3"><b>CGST Amount</td>
			<td style="text-align:right;">';
			if($total_cs_gst=="0.00")
			{
				$html .='0.00';
			}
			else
			{
				$html .='<b>'.indian_number($tax1, 2).'</b>';
			}
			$html .='</td>
			</tr>
			<tr>
			<td rowspan="4" colspan="4" >
			<b>Total Sales Amount (In Words): '.ucfirst(convert_number_to_words_new($totalAmount)).'</b></td>	
			<td  colspan="3"><b>SGST Amount</td>
			<td style="text-align:right;"> ';
			if($total_cs_gst=="0.00")
			{
				$html .= '0.00';
			}
			else
			{
				$html .= '<b>'.indian_number($tax2, 2).'</b>';
			}
			$html .='</td>
			</tr>
			<tr>
			<td  colspan="3"><b>IGST Amount</b></td>
			<td style="text-align:right;"> ';
			if($total_i_gst!="0.00")
			{
				$html .= indian_number($tax3, 2);
			}
			else
			{
				$html .= '<b>'.'0.00'.'</b>';
			}
			$html .='</td>
			</tr>
			<tr>
			<td  colspan="3"><b>Total Tax Amount</td>
			<td style="text-align:right;"> <b> '.indian_number($tax1+$tax2+$tax3, 2).'</td>
			</tr>
			<tr>
			<td  colspan="3"><b>Total Amount</b></td>';
			$html .=	'<td style="text-align:right;"> <b>'.indian_number($totalAmount, 2).'</td>
			</tr>';
			$html.='<tr>
			<td  colspan="4"   style="height:100px;vertical-align:top;";><b>Remark:'.$rel['remark'].'</td>
			<td colspan="5" style="vertical-align:bottom;">  <center> <b>Authorised Signatory</center></td>
			</tr>';
			$html.='</tbody></table>
			</div>
			<!--<center class="nextpage"></center>-->';
		}
		$pcount++;
		if($pcount==6 && $cnt!=$p){
			$pcount=1;
			$html.='<tr style="border-left:none;border-right:none;border-top:1px solid;">
			<td  style="text-align:right;border:none;vertical-align:top;"></td>
			<td style="width:53%;text-align:center;border:none;"></td>
			<td style="width:5%;text-align:center;border:none;"></td>
			<td style="width:5%;text-align:center;border:none;"></td>
			<td style="width:5%;text-align:center;border:none;"></td>
			<td style="width:10%;text-align:center;border:none;"></td>
			<td style="width:10%;text-align:center;border:none;"></td>
			<td style="width:15%;text-align:center;border:none;"></td>
			</tr>
			</tbody></table>
			</div>
			<center class="nextpage"></center>';  
		}
		$p++;

	}
	$pr=7-$cnt;
	for($j=0; $j<$pr; $j++)
	{
		$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
		<td style="border:none;border-left:1px solid;border-right:1px solid;height:40px;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		</tr>';
	}

	
	
/*	$html.='<tr >

		<td rowspan="3" colspan="4">
		<b>Total Tax Amount (In Words): '. ucfirst(convert_number_to_words($tax1+$tax2+$tax3)).'</b>
		</td>	
			
		<td  colspan="3"><b>Total Basic </b></td>
		<td style="text-align:right;"><b> '.indian_number($ttl_amt, 2).'</b></td>
	</tr>';
	$totalAmount = $ttl_amt+$tax1+$tax2+$tax3-$product_discount;
	$html.='
		<!--<tr>
			<td  colspan="3">Freight Charges</td>
			<td >50,000.00</td>
		</tr>-->
		<tr>

		<td  colspan="3"><b>Total Amount Before Tax</td>
		<td style="text-align:right;"> <b>'.indian_number($ttl_amt, 2).'</td>
		
		<tr>

		<td  colspan="3"><b>CGST Amount</td>
		<td style="text-align:right;">';
		if($trn_rel['tax_amount2']=="0.00")
		{
			$html .='0.00';
		}
		else
		{
		
		$html .='<b>'.indian_number($tax1, 2);
		
		}
		$html .='</td>
		
		<tr>
<td rowspan="4" colspan="4" >
		<b>Total Sales Amount (In Words): '.ucfirst(convert_number_to_words($totalAmount)).'
		</td>	
		<td  colspan="3"><b>SGST Amount</td>
		<td style="text-align:right;"> ';
		
		if($trn_rel['tax_amount2']=="0.00")
		{
			$html .= '0.00';
		}
		else
		{
		
		$html .= '<b>'.indian_number($tax2, 2);
		}
		
		$html .='</td>
		<tr>

		<td  colspan="3"><b>IGST Amount</td>
		<td style="text-align:right;"> ';
		
		if($trn_rel['tax_amount2']=="33330.00")
		{
			$html .= '<b>'.indian_number($tax1, 2);
			
		}
		else
		{
				$html .= '<b>'.'0.00';
				
		
		}
		$html .='</td>
		<tr>

		<td  colspan="3"><b>Total Tax Amount</td>
		<td style="text-align:right;"> <b> '.indian_number($tax1+$tax2+$tax3, 2).'</td>
		<tr>

		<td  colspan="3"><b>Total Amount</td>';
		
	$html .=	'<td style="text-align:right;"> <b>'.indian_number($totalAmount, 2).'</td>
		
		
	</tr>';
	
	
	$html.='<tr>

		
		
		
		<td  colspan="4"   style="height:100px;vertical-align:top;";><b>Remark:'.$rel['remark'].'
</td>
		<td colspan="5" style="vertical-align:bottom;">  <center> <b>Authorised Signatory</center></td>
		

		
	</tr>';
	
	
	
	*/
	
	
	
	$html.='<!--</tbody></table>
	<div style="clear:both;"></div>-->
	</div>
	<!--page1 end-->';

	/* Get Terms And Condition Start */
	$terms_qry="select qtrm.*,mst.tc_name from tbl_salesorder_terms_trn as qtrm 
	left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
	where qtrm.quotation_terms_trn_status=0 and qtrm.sales_order_id=".$rel['sales_order_id']." order by qtrm.tc_priority";
	$terms_qry_rs=$dbcon->query($terms_qry);
	if(mysqli_num_rows($terms_qry_rs)){
		$html.='<center class="nextpage"></center>
		<h3 style="text-align:center;">Terms & Conditions for Sales Order No : <u>'.$rel['sales_order_no'].'</u></h3>
		<div><table width="100%" style="font-size:12px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3"><tbody>';
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
		$html.='</tbody></table></div>';	
	}

	$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
//echo $html;exit;
	ob_end_clean();
	include("../../view/export/mpdf/mpdf.php");
	$mpdf=new mPDF('','A4','0','calibri','10','10','25','10','1','1');
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
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
	ob_clean();
	return 'Order Acceptance '.$quotation_id.'.pdf';
}
?>