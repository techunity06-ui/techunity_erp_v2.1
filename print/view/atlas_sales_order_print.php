<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");

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
$form="Work Order";
$mode="Print";
$type = 'pdf';
if (strtolower($type) == 'pdf'){
	$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select invoice.*,country.country_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust_pincode,cust_mobile,gst_no from tbl_sales_order as invoice 
	left join tbl_ledger as cust on cust.l_id=invoice.cust_id
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid
	left join city_mst as city on city.cityid=cust.cityid
	where sales_order_id=$invoiceid";
	$rel=mysqli_fetch_assoc($dbcon->query($query));	
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

	$header = '<img src="' . DOMAIN_F . LOGO . $set_head['logo'].'" style="width:8.27in" />';
	$footer='<img src="'.DOMAIN_F.LOGO.$set_head['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';
	$approve_status = '';
	if ($rel['approve_status'] == '0')
	{
		$approve_status = ' (DRAFT)';
	}

	$html = '<html>
	<head>                  
	<title>Sales Order - ' . $rel['sales_order_no'] . '</title>
	<style type="text/css">
	/* .page{
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
		<body>';
		$qry="select trn.*,product.product_name,usr.user_name as sales_person, per.unit_name FROM `tbl_sales_ordertrn` as trn 
				left join product_mst as product on product.product_id=trn.product_id 
				left join unit_mst as per on per.unitid=trn.unit_id 
				left join tbl_quotation_trn as quotrn on quotrn.quot_trn_id=trn.quot_trn_id
				left join tbl_quotation as quo on quo.quotation_id=quotrn.quotation_id
				left join users as usr on usr.user_id=quo.quot_won_user_id
				where sales_ordertrn_status=0 and trn.sales_order_id=".$rel['sales_order_id'];
	$result=$dbcon->query($qry);		
	$i=1;$total=0;$discount=0;$totalqty=0;
	$cnt=mysqli_num_rows($result);
	while($row=mysqli_fetch_assoc($result)){
		
		if(!empty($row['sales_person'])){
			$sales_person=$row['sales_person'];
		}else{
			$sales_person="";
		}

		$html.='<htmlpageheader name="otherpages" style="display:none">
		<div style="text-align:center">' . $header . '</div>
		</htmlpageheader>
		<!-- <htmlpagefooter name="otherpages_footer" style="display:none">
		<div style="text-align:center">' . $footer . '</div>
		</htmlpagefooter> -->
		<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
		<sethtmlpagefooter name="otherpages_footer" value="on" show-this-page="0"/>
		<div>
			<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<tbody>
					<tr>
						<td colspan="3" style="text-align:center; font-size:16px; font-weight:bold;">'.$form.'</td>
					</tr>
					<tr>
						<td style="text-align:left; font-size:15px; font-weight:bold;">'.$rel['sales_order_no'].'</td>
						<td style="text-align:left; font-size:15px; font-weight:bold;">Date: '.date('d/m/Y',strtotime($rel['sales_order_date'])).'</td>
						<td style="text-align:right; font-size:15px; font-weight:bold;">Delivery Date: '.date('d/m/Y',strtotime($rel['delivery_date'])).'</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:left; font-size:14px; font-weight:bold;">Name of Customer/Site: '.$city_name.', '.$state_name.'</td>
						<td style="text-align:right; font-size:14px; font-weight:bold;">Job No. : '.$rel['sales_order_no'].'</td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:left; font-size:14px; "><strong>Model: '.stripcslashes($row['product_name']).'</strong><br>'.nl2br(stripcslashes($row['description'])).'</td>
						<td style="text-align:right; font-size:14px; font-weight:bold;">Qty : '.$row['product_qty'].' '.$row['unit_name'].'</td>
					</tr>
				</tbody>
			</table>
			<!--<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<tbody>
					<tr style="border: none;">
						<td>'.nl2br(stripcslashes($row['description'])).'</td>
					</tr>
				</tbody>
			</table>-->
			<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<tbody>
					<tr>
						<td width="25%">Remarks</td>
						<td colspan="3" style="vertical-align: top;"> '.(($rel['remark']) ? $rel['remark'] : '').'</td>
					</tr>
					<tr>
						<td>Sales By</td>
						<td colspan="3" style="vertical-align: top;"> '.$sales_person.'</td>
					</tr>
					<tr>
						<td>Feedback from Production</td>
						<td colspan="3" style="vertical-align: top;"> </td>
					</tr>
					<tr>
						<td rowspan="3"> Production Team</td>
						<td style="height: 25px;" colspan="3"></td>
					</tr>
					<tr>
						<td style="height: 25px;" colspan="3"></td>
					</tr>
					<tr>
						<td style="height: 25px;" colspan="3"></td>
					</tr>
					<tr style="border-bottom: none;">
						<td colspan="2" style="height: 50px; vertical-align: top; border-right: none;">Prepared By</td>
						<td colspan="2" style="height: 50px; vertical-align: top; text-align: right; border-left: none;">Approved By</td>
					</tr>
					<tr style="border-top: none;">
						<td colspan="2" style="font-weight: bold; border-right: none;">(T.K. Nair)</td>
						<td colspan="2" style="font-weight: bold; text-align: right; border-left: none;">(Nilesh Patel/Bhavesh Patel)</td>
					</tr>
				</tbody>
			</table>
		</div>';
		$i++;
		if($cnt>1){
				$html.='<center class="nextpage"></center>';
		}
	}
$html.='</body>
</html>';
		ob_end_clean();
		include ("../../view/export/mpdf/mpdf.php");
		$mpdf = new mPDF('', 'A4', '0', 'calibri', '10', '10', '40', '3.7', '2', '2');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
       //Show page number
		$mpdf->pagenumPrefix = ' ';
		$mpdf->pagenumSuffix = ' / ';
		$mpdf->nbpgPrefix = ' ';
		$mpdf->nbpgSuffix = ' pages';
		$mpdf->SetFooter('{PAGENO}{nbpg}');

		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion = true;
		$mpdf->charset_in = 'UTF-8';
		$mpdf->WriteHTML($html);
		$mpdf->Output();
       //$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$invoiceid.'.pdf','f');
		ob_clean();
		return 'sales_order_print' . $invoiceid . '.pdf';
	}
?>