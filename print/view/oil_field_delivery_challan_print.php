<?php 
require_once '../../vendor/autoload.php';
use Mpdf\Mpdf;
session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$_SESSION['contents']=''; 
$form="Delivery Challan";
$mode="Print";
$type='pdf';
error_reporting(E_ALL);
// print_r($_GET);
if(strtolower($type) == 'pdf') {
	$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select invoice.*,country.country_name,state.state_name,city.city_name,cust.l_name,cust.m_address,type.invoice_type,cust_pincode,cust.cust_mobile,cust.gst_no from tbl_invoice as invoice 
		inner join tbl_ledger as cust on cust.l_id=invoice.cust_id
		left join country_mst as country on country.countryid=cust.countryid
		left join state_mst as state on state.stateid=cust.stateid
		left join city_mst as city on city.cityid=cust.cityid
		left join tbl_invoicetype as type on type.invoicetype_id=invoice.invoicetype_id
		where invoice_id=$invoiceid";
	$rel=mysqli_fetch_assoc($dbcon->query($query));	
	$company_name=$rel['l_name'];
	$cust_address=$rel['m_address'];
	$city_name=$rel['city_name'];
	$state_name=$rel['state_name'];
	$country_name=$rel['country_name'];
	$cust_pincode=$rel['cust_pincode'];
	$gst_no=$rel['gst_no'];
	
	/* Get Consignee data start */
	if(!empty($rel['consignee_id']))
	{	
		$consignee="select * from tbl_custmer_consignee as cust 
		left join country_mst as country on country.countryid=cust.countryid
		left join state_mst as state on state.stateid=cust.stateid 
		left join city_mst as city on city.cityid=cust.cityid where cust_id=".$rel['consignee_id'];
		$cons_data=mysqli_fetch_assoc($dbcon->query($consignee));	
			$company_name=$cons_data['company_name'];
			$cust_address=$cons_data['cust_address'];
			$city_name=$cons_data['city_name'];
			$state_name=$cons_data['state_name'];
			$country_name=$cons_data['country_name'];
			$cust_pincode=$cons_data['cust_pincode'];
			$gst_no=$cons_data['gst_no'];
		//var_dump($cons_data);
	}
	/* Get Consignee data end */


	
	$set="select * from tbl_company where company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	$order_date='';$dispatch_date='';
	if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
	{
		$order_date=date('d/m/Y',strtotime($rel['order_date']));
	}
	
	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));

	$chksql=$dbcon->query("SELECT SUM(discount_per) AS discount FROM `tbl_invoicetrn` WHERE trancation_status=0 and invoice_id=".$rel['invoice_id']);
	$get_row=mysqli_fetch_assoc($chksql);

	if($get_row['discount']>0){
		$colspan=2;
		$cols=7;
	}else{
		$colspan=1;
		$cols=6;
	}

	$header ='<table style="border: none; width: 100%;">
	<tbody>
	<tr style="border: none;">
	<td style="border: none; width: 65%; vertical-align: top;"><img src="'.DOMAIN_F.LOGO.$set_head['logo'].'" /></td>
	<td style="border: none; width: 35%; text-align: right;"><h1>'.$form.'</h1></td>
	</tr>
	</tbody>
	</table>';
	$footer ='<table style="border: none; width: 100%;">
	<tbody>
	<tr style="border: none;">
	<td style="border: none; border-bottom: 1px solid; font-size: 14px; text-align: center; font-weight: bold;">THANK YOU FOR YOUR BUSINESS!</td>
	</tr>
	</tbody>
	</table>';

	$approve_status='';
	if($rel['approve_status']=='0'){
		$approve_status=' (DRAFT)';
	}

	$html ='<html>
	<head>					
	<title>'.$form.' - '.$rel['invoice_no'].'</title>
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
		<table style="font-size:12px;border-collapse: collapse;width:100%; border: none;" cellpadding="5" cellspacing="5">
		<tr style="border: none;">
		<td rowspan="3" style="vertical-align: top; border: none;">CONSIGNEE NAME & ADDRESS: <strong><br>Name :</strong> '.$company_name.'<br><strong>Address :</strong> '.nl2br($cust_address).' '.$city_name.' '.$state_name.' '.$country_name.'<br>Contact No :</strong> '.$rel['cust_mobile'].'</td>
		<td rowspan="3" style="width: 15%;font-weight: bold;border: none;"></td>
		<td style="font-weight: bold;border: none;border-right: 1px solid;">DELIVERY NUMBER</td>
		<td style="border: none;white-space: nowrap;">'.$rel['invoice_no'].'</td>
		</tr>
		<tr style="border: none;">
		<td style="font-weight: bold;border: none;border-right: 1px solid;">DELIVERY DATE</td>
		<td style="border: none;white-space: nowrap;">'.$rel['invoice_date'].'</td>
		</tr>
		<tr style="border: none;">
		<td style="font-weight: bold;border: none;border-right: 1px solid;">WORK ORDER NUMBER</td>
		<td style="border: none; white-space: nowrap;"></td>
		</tr>
		<tr style="border: none;">
		<td colspan="2" style="border: none;"><strong>Customer GSTN :</strong>'.$gst_no.'</td>
		<td style="font-weight: bold;border: none;border-right: 1px solid;">YOUR ORDER NO.</td>
		<td style="border: none;white-space: nowrap;"></td>
		</tr>
		<tr style="border: none;">
		<td style="border: none;"><strong>CORRESPONDENCE TO:</strong></td>
		<td style="font-weight: bold;border: none;"></td>
		<td style="font-weight: bold;border: none;border-right: 1px solid;">PLACE OF SUPPLY</td>
		<td style="border: none;white-space: nowrap;"></td>
		</tr>
		<tr style="border: none;">
		<td style="border: none;"><strong>Name:</strong></td>
		<td style="font-weight: bold;border: none;">'.$cons_data['cust_contact_person_name'].'</td>
		<td style="font-weight: bold;border: none;border-right: 1px solid;">MODE OF TRANSPORTATION</td>
		<td style="border: none;white-space: nowrap;"></td>
		</tr>
		<tr style="border: none;">
		<td style="border: none;"><strong>Email:</strong></td>
		<td style="font-weight: bold;border: none;">'.$cons_data['cust_contact_person_email'].'</td>
		<td style="font-weight: bold;border: none;border-right: 1px solid;">VEHICLE NO</td>
		<td style="border: none;white-space: nowrap;"></td>
		</tr>
		<tr style="border: none;">
		<td style="border: none;"><strong>Tel./Fax:</strong></td>
		<td style="font-weight: bold;border: none;">'.$cons_data['cust_contact_person_no'].'</td>
		<td style="font-weight: bold;border: none;border-right: 1px solid;">TERMS</td>
		<td style="border: none;white-space: nowrap;"></td>
		</tr>
		</table>
		<table style="font-size:12px;border-collapse: collapse;width:100%; margin-top: 20px;" cellpadding="3" cellspacing="3">
		<thead>
		<tr>
		<th style="width:3%;text-align:center; border-right: 1px solid;">Sr.<br/>No.</th>
		<th style="width:5%;text-align:center; border-right: 1px solid;">Qty</th>
		<th style="width:8%;text-align:center; border-right: 1px solid;">Unit</th>
		<th colspan="2" style="width:38%;text-align:center; border-right: 1px solid;">Item Description</th>
		<th style="width:10%;text-align:center; border-right: 1px solid;">Unit Price</th>';
		if($get_row['discount']>0){ 
			$html.='<th style="width:9%;text-align:center; border-right: 1px solid;">Disc.</th>';
		}
		$html.='<th style="width:10%;text-align:center; border-left: 1px solid;">Total</th>
		</tr>
		</thead>
		<tbody>';
		$qry="SELECT trn.*,product.*,unit_name FROM `tbl_invoicetrn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trancation_id order by product.product_type,trancation_id";
		$result=$dbcon->query($qry);		
		$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_i_gst=0;$gst_per = 0;$gst_rate = 0;
		 $totaltaxable=0; $taxable_amt=0;$total_product_amount=0; $totalsqr=0; $charges_qty1=0; $total_cs_gst=0;
		$cnt=mysqli_num_rows($result);
		while($row=mysqli_fetch_assoc($result))
		{
			$gst_per = $row['cgst_tax_per']+$row['sgst_tax_per']+$row['igst_tax_per'];
			$gst_rate = $row['cgst_tax_rate']+$row['sgst_tax_rate']+$row['igst_tax_rate'];

			if($row['cgst_tax_rate'] != 0 || $row['sgst_tax_rate'] !=0){
				$total_cs_gst += $gst_rate;
			}else{
				$total_i_gst += $gst_rate;
			}
        //tax summary calculation start
			if(!empty($row['tax_val']))
			{
				$tax_num=explode(",",$row['tax_val']);
				$tax_name=explode(",",$row['tax_name']);
				$total_net_rate=($row['product_qty']*$row['product_rate'])-$row['discount'];
				for($j=0;$j<count($tax_num);$j++)
				{
					if(!in_array($tax_name[$j],$tax['per']))
					{
						$tax['per'][]=$tax_name[$j];
					}
					$tax['per_total'][$tax_name[$j]]+=$total_net_rate*$tax_num[$j]/100;
				}
			}
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
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;">
			'.$row['product_qty'].'
			</td>
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;">'.$row['unit_name'].'</td>
			<td colspan="2" style="text-align:left;border-right: 1px solid;vertical-align:top;">
			<strong>'.$row['product_name'].'</strong><br/>
			'.$pro_descri.'
			</td>
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;">'.number_format($row['product_rate'],2,".","").'</td>';
			if($get_row['discount']>0){
				$html.='<td style="text-align:center;border-right: 1px solid;vertical-align:top;">'.number_format($row['product_discount'],2,".","").'<br>('.$pro_disc.')</td>';
			}
			$html.='<td style="text-align:center;border-right: 1px solid;vertical-align:top;">'.number_format($row['product_amount'],2,".","").'</td>
			</tr>';
			$i++; 
			$totalqty=$totalqty+intval($row['product_qty'])-$charges_qty;
			$totalsqr=$totalsqr+intval($row['sqr_ft'])-$charges_qty1;
			$total_product_amount+=$row['product_amount'];
			$totaltaxable+=$taxable_amt;
			$total+=$row['total'];
		}
		$pr=10-$cnt;
		for($j=0; $j<$pr; $j++){
			$html.='<tr style="border: none; border-right: 1px solid; border-left: 1px solid;">
			<td style="border-right: 1px solid;"></td>
			<td style="border-right: 1px solid;"></td>
			<td style="border-right: 1px solid;"></td>
			<td colspan="2" style="border-right: 1px solid;height:25px;"></td>
			<td style="border-right: 1px solid;"></td>';
			if($get_row['discount']>0){
				$html.='<td style="border-right: 1px solid;"></td>';
			}
			$html.='<td style="border-left: 1px solid;"></td>
			</tr>';
		}
		$remark = ($rel['remark']) ? $rel['remark'] : '';
		//$rows=(($rel['stateid']==$set_head['stateid']) ? 2 : 1)+2;
		$html.='<tr>
		<td colspan="3" style="text-align:left; vertical-align: top; font-weight: bold;">Material Specification :</td>
		<td colspan="2" style="text-align:left; vertical-align: top; font-weight: bold;">"As per attached Material Specification"</td>
		<td colspan="'.$colspan.'"></td>
		<td></td>
		</tr>
		<tr>
		<td></td>
		<td></td>
		<td></td>
		<td style="text-align: center; font-weight: bold;">Applicable&nbsp;<input type="checkbox" class="form-control"></td>
		<td style="text-align: center; font-weight: bold;">Not Applicable&nbsp;<input type="checkbox" class="form-control"></td>
		<td colspan="'.$colspan.'" style="text-align:right; font-weight: bold;">Sub Total</td>
		<td style="text-align:right;font-weight: bold;">'.number_format($total_product_amount,2,".","").'</td>
		</tr>';
		$html.='<tr>
		<td colspan="5" style="text-align:left; vertical-align: top; font-weight: bold;">Remarks: If correction required on any of supplier supplied documents/ records, correction shall be made by the supplier authorised person by striking the error out and initial it with the date.<br>Please notify us immediately if this order cannot be shipped complete on or before:<br>'.$remark.'</td>
		<td colspan="'.$colspan.'"></td>
		<td></td>
		</tr>
		<tr style="border: none;">
		<td colspan="'.($cols).'" style="border: none;text-align:right; font-weight: bold; font-size: 14px;">Total Amount</td>
		<td style="border: none;text-align:right;font-weight: bold; font-size: 14px; text-decoration: underline;">'.number_format($rel['g_total'],2,".","").'</td>
		</tr>
		</tbody>
		</table>
		<table>
		<tbody>
		<tr style="border: none;">
		<td style="border: none; vertical-align:bottom; text-align: left; height: 80px;"><br>Reviewed By</td>
		<td style="border: none; vertical-align:bottom; text-align: left; height: 80px;"><br>Date</td>
		<td style="border: none; vertical-align:bottom; text-align: right; height: 80px;"><br>Approved By</td>
		<td style="border: none; vertical-align:bottom; text-align: right; height: 80px;"><br>Date</td>
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
		include("../../vendor/mpdf/mpdf/src/Mpdf.php");
		$mpdf = new Mpdf(['format' => 'A4','margin_left' => 10,'margin_right' => 10,'margin_top' => 40,'margin_bottom' => 5,'margin_header' => 1,'margin_footer' => 1,'default_font' => 'calibri']);
	
		// include("../../view/export/mpdf/mpdf.php");
		// $mpdf=new mPDF('','A4','0','calibri','10','10','40','5','1','1');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = 'B'; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = 'B'; /* blank, B, I, or BI */
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