<?php session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$_SESSION['contents']=''; 
$form="Sales Order";
$mode="Print";
$type='pdf';
// print_r($_GET);
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	SALES_ORDER_SLUG_PRINT,
	SALES_ORDER_SLUG_READ
]);

if(!in_array(SALES_ORDER_SLUG_PRINT,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
if(strtolower($type) == 'pdf') {
	$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
	$query="SELECT invoice.*,country.country_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust.cust_pincode,cust.cust_mobile,cust.gst_no, cust.cust_email from tbl_sales_order as invoice 
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
$delivery_type = $rel['delivery_type'];
$cust_mobile = $rel['cust_mobile'];
$cust_email = $rel['cust_email'];

$set="select * from tbl_company where company_id=".$rel['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));	
$order_date='';$dispatch_date='';
	
	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));

	$header ='<table style="border: none; width: 100%;">
	<tbody>
	<tr style="border: none;">
	<td style="border: none; width: 65%; vertical-align: top;"><img src="'.DOMAIN_F.LOGO.$set_head['logo'].'" /></td>
	<td style="border: none; width: 35%; text-align: right;"><h1>'.$form.'</h1></td>
	</tr>
	</tbody>
	</table>';
	// $footer ='';

	$approve_status='';
	if($rel['approve_status']=='0'){
		$approve_status=' (DRAFT)';
	}

	$html ='<html>
	<head>					
	<title>'.$form.' - '.$rel['sales_order_no'].'</title>
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
			border:1px solid;
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
		<table style="font-size:14px;border-collapse: collapse;width:100%; border: none;" cellpadding="5" cellspacing="5">
		<thead>
		<tr style="border: 1px solid;">
		<td colspan="4" style="font-weight: bold;">Party Name: '.$company_name.'</td>
		<td colspan="4" style="font-weight: bold;">Consignee: '.$company_name.'</td>
		</tr>
		<tr style="border: 1px solid;">
		<td colspan="4" style="font-weight: bold;">Address: '.$cust_address.'</td>
		<td colspan="4" style="font-weight: bold;">Address: '.$cust_address.'</td>
		</tr>
		<tr style="border: 1px solid;">
		<td colspan="4" style="font-weight: bold;">GSTIN: '.$gst_no.'</td>
		<td colspan="4" style="font-weight: bold;">GSTIN: '.$gst_no.'</td>
		</tr>
		<tr style="border: 1px solid;">
		<td colspan="4" style="font-weight: bold;">Contact No: '.$cust_mobile.'</td>
		<td colspan="4" style="font-weight: bold;">Contact No: '.$cust_mobile.'</td>
		</tr>
		<tr style="border: 1px solid;">
		<td colspan="4" style="font-weight: bold;">Email: '.$cust_email.'</td>
		<td colspan="4" style="font-weight: bold;">Email: '.$cust_email.'</td>
		</tr>
		<tr style="border: 1px solid;">
		<td colspan="2" style="font-weight: bold;">SO NO :</td>
		<td style="font-weight: bold;">DATE :</td>
		<td style="font-weight: bold;">P.O NO :</td>
		<td colspan="2" style="font-weight: bold;">DATE :</td>
		<td colspan="2" style="font-weight: bold;">Agent Name :</td>
		</tr>
		<tr style="border: 1px solid;">
		<td colspan="2" style="font-weight: bold;">'.$rel['sales_order_no'].'</td>
		<td style="font-weight: bold;">'.date('d/m/Y',strtotime($rel['sales_order_date'])).'</td>
		<td style="font-weight: bold;">'.$rel['po_no'].'</td>
		<td colspan="2" style="font-weight: bold;">'.date('d/m/Y',strtotime($rel['po_date'])).'</td>
		<td colspan="2" style="font-weight: bold;">'.$userData['user_name'].'</td>
		</tr>
		<tr style="border: 1px solid;">
		<td colspan="8"></td>
		</tr>
		<tr>
		<th width="5%" style="text-align:center; border-right: 1px solid;">Sr<br>No.</th>
		<th width="45%" colspan="3" style="text-align:center; border-right: 1px solid;">ITEM DESCRIPTION</th>
		<th width="12%" style="text-align:center; border-right: 1px solid;">QTY</th>
		<th width="12%" style="text-align:center; border-right: 1px solid;">UNIT</th>
		<th width="12%" style="text-align:center; border-right: 1px solid;">RATE</th>
		<th width="12%" style="text-align:right; border-left: 1px solid;">AMOUNT</th>
		</tr>
		</thead>
		<tbody>';
		$qry="SELECT * FROM `tbl_sales_ordertrn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id where sales_ordertrn_status=0 and sales_order_id=".$rel['sales_order_id'];
		$result=$dbcon->query($qry);		
		$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_i_gst=0;$gst_per = 0;$gst_rate = 0;
		$cnt=mysqli_num_rows($result);
		while($row=mysqli_fetch_assoc($result))
		{
			if($row['product_base_unit']!=$row['product_conv_unit']){
			//base_unit_name,per2.unit_name as conv_unit_name
				if($row['unit_id']==$row['product_base_unit']){
					$cqty=convert_stock($dbcon,$row['product_qty'],$row['product_id'],"conv_unit");
					$uname=$row['conv_unit_name'];
				}else{
					$cqty=convert_stock($dbcon,$row['product_qty'],$row['product_id'],"base_unit");
					$uname=$row['base_unit_name'];
				}
			}
			$pro_descri = ($row['description']) ? nl2br($row['description']) : '' ;
			$product_hsn_code = ($row['product_hsn_code']) ? $row['product_hsn_code'] : $row['product_hsn'] ;
			$pro_disc = ($row['discount_per']) ? $row['discount_per']." %" : '';

			$html.='<tr style="border: none;border-right: 1px solid; border-left: 1px solid;">
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;">'.$i.'</td>
			<td colspan="3" style="text-align:left;border-right: 1px solid;vertical-align:top;">
			<strong>'.$row['product_name'].'</strong><br/>
			'.$pro_descri.'
			</td>
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;">
			'.$row['product_qty'].'
			</td>
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;">'.$row['unit_name'].'</td>
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;">'.number_format($row['product_rate'],2,".","").'</td>
			<td style="text-align:right;border-right: 1px solid;vertical-align:top;">'.number_format($row['product_amount'],2,".","").'</td>
			</tr>';
			$i++; 
			$totalqty=$totalqty+$row['product_qty']-$charges_qty;$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
			$total_product_amount+=$row['product_amount'];
			$totaltaxable+=$taxable_amt;
			$total+=$row['total'];
		}
		$pr=10-$cnt;
		for($j=0; $j<$pr; $j++){
			$html.='<tr style="border: none; border-right: 1px solid; border-left: 1px solid;">
			<td style="border-right: 1px solid;"></td>
			<td colspan="3" style="border-right: 1px solid;height:25px;"></td>
			<td style="border-right: 1px solid;"></td>
			<td style="border-right: 1px solid;"></td>
			<td style="border-right: 1px solid;"></td>
			<td style="border-left: 1px solid;"></td>
			</tr>';
		}
		$remark = ($rel['remark']) ? $rel['remark'] : '';
		//$rows=(($rel['stateid']==$set_head['stateid']) ? 2 : 1)+2;
		$terms_qry="select qtrm.*,mst.tc_name from tbl_salesorder_terms_trn as qtrm 
	left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
	where qtrm.quotation_terms_trn_status=0 and qtrm.sales_order_id=".$rel['sales_order_id']." order by qtrm.tc_priority";
	$terms_qry_rs=$dbcon->query($terms_qry);
		$html.='<tr>
		<td colspan="8" style="height: 50px; text-align:CENTER; vertical-align: middle; font-weight: bold;">TERMS & CONDITIONS OF SUPPLY :</td>
		</tr>';
		$t=1;
		$nums=mysqli_num_rows($terms_qry_rs);
		while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
			$string=(nl2br($term_rel['tc_details']));
		$html.='<tr style="border: 1px solid;">
		<td colspan="2" rowspan="2" style="border: 1px solid; vertical-align:bottom; text-align: left; height: 80px;">'.$term_rel['tc_name'].'</td>
		<td colspan="4" rowspan="2" style="border: 1px solid; vertical-align:bottom; text-align: left; height: 80px;">'.$string.'</td>
		<td colspan="2" style="border: 1px solid; vertical-align:bottom; text-align: left; height: 80px;"><img src="'.DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'].'" style="height: 100px;width: 150px;"/></td>
		</tr>
		';
		$t++;
	}
		$html.='<tr style="border: 1px solid;">
		<td colspan="2" style="border: 1px solid; text-align: center;">Authorized Signature</td>
		</tr>
		</tbody></table>
		<div style="clear:both;"></div>
		</div>
		<!--page1 end-->';

		$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
		</body>
		</html>';
		// echo $html;exit;
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','40','5','1','1');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		//Show page number
		// $mpdf->pagenumPrefix = ' ';
		// $mpdf->pagenumSuffix = ' / ';
		// $mpdf->nbpgPrefix = ' ';
		// $mpdf->nbpgSuffix = ' pages';
		// $mpdf->SetFooter('{PAGENO}{nbpg}');

		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='UTF-8';
		$mpdf->WriteHTML($html);
		$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$purchaseorder_id.'.pdf','f');
		ob_clean();
		return 'Purchase Order '.$purchaseorder_id.'.pdf';
	}

	?>