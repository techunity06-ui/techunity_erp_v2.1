<?php session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once("../../crm/include/crm_common_functions.php");
//include_once(COMMON_FUNCTION_PATH."common_production_functions.php");
$_SESSION['contents']=''; 
$form="SALESORDER STOCK DEALLOCATION SLIP";
$mode="Print";
$id=$dbcon->real_escape_string($_REQUEST['id']);	
$type='pdf';
if(strtolower($type) == 'pdf') {
 $query="SELECT de.de_allo_no,de.de_allo_date,de.company_id FROM tbl_so_stock_deallocate as de 
	WHERE de.de_allo_id = " . $id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	
	$de_allo_date = '';
	if($rel['de_allo_date']!="1970-01-01 00:00:00" && $rel['de_allo_date']!="0000-00-00 00:00:00")
	{
		$de_allo_date=date('d-m-Y',strtotime($rel['de_allo_date']));
	}
	
	$companyConfiguration=getCompanyConfiguration($dbcon);
	$company_id = $rel['company_id'];

	$set="select * from tbl_company where company_id=".$company_id;
	$set_head=mysqli_fetch_assoc($dbcon->query($set));	

	$header = get_header($dbcon,'text-align: left','100%','130px');

	$html ='<html>
	<head>					
	<title>'.$form.' - '.$rel['de_allo_no'].'</title>
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
			/*border:1px solid #000 !important;*/
			/*page-break-inside:avoid;*/
		}
		.quot_annex_content_div table tr,td{
			padding:5px;
		}
		br{

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
		<td colspan="3" style="text-align:center; font-size:15px; font-weight:bold;border-right:none;border-top:1px solid;">'.$form.'</td>
		</tr>
		
		<tr>
			<td style="width:50%;border-top:1px solid;"> 
				<strong> 
					<span style="width:10%">Deallocation No.</span>
					<span style="margin-left:39px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$rel['de_allo_no'].'</span>
			</td>
			<td style="width:50%;border-top:1px solid;"> 
				<strong> 
					<span  style="width:10%">Deallocation Date.</span>
					<span style="margin-left:39px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$de_allo_date.'</span>
			</td>
		</tr>
		</table>
		</div>
		';
		$html.='<div>
				<table style="font-size:12px;border-collapse: collapse;width:100%; border:1px solid" cellpadding="3" cellspacing="3">
				<thead>
				<tr>
				<th style="width:10%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
				<th style="width:20%;text-align:center;border:1px solid;">Salesorder No</th>
				<th style="width:55%;text-align:center;border:1px solid;">Product Name</th>
				<th style="width:15%;text-align:center;border:1px solid;">Qty</th>
				</thead>
				<tbody> ';
		$qry="SELECT de.de_allo_no, de.de_allo_date, so.sales_order_no, p.product_name, trn.de_allocate_qty, u.unit_name,p.product_icode FROM tbl_so_stock_deallocate_trn as trn 
	LEFT JOIN tbl_so_stock_deallocate as de on de.de_allo_id = trn.de_allo_id
	LEFT JOIN tbl_sales_order as so on so.sales_order_id = trn.sales_order_id
	LEFT JOIN product_mst as p on p.product_id = trn.product_id
	LEFT JOIN unit_mst as u on u.unitid = trn.unit_id
	WHERE trn.status = 0 AND trn.de_allo_id = " . $id;

		$result=$dbcon->query($qry);		
		$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$pcount=1;$total_cs_gst=0;$total_i_gst=0;$gst_per=0;$gst_rate=0;
		$cnt=mysqli_num_rows($result);
		while($row=mysqli_fetch_assoc($result))
		{
			
			$html.='<tr>
			<td style="text-align:center;vertical-align:top;border-bottom:1px solid;border-right:1px solid">'.$i.'</td>
			<td style="text-align:left;vertical-align:top;border-bottom:1px solid;border-right:1px solid">
			'.$row['sales_order_no'].'
			</td>
			<td style="text-align:left;vertical-align:top;border-bottom:1px solid;border-right:1px solid">
			<strong>'.$row['product_name'].'</strong><br>
			<strong>Product Code : </strong>'.$row['product_icode'];
			$html .='
			<td style="text-align:center;vertical-align:top;border-bottom:1px solid;border-right:1px solid">
			'.number_format($row['de_allocate_qty'],2,".","").' '.$row['unit_name'].'
			</td>
			';
			
			$html.='</tr>';


			$totalqty=$totalqty+$row['de_allocate_qty'];
			$i++; 
		}

		$html .= '</table></div><div>';
		////////////////////////////////////////////////////////////////////Tax Calculation Start - Harshil//////////////////////////////////////////////////
		$sub_total=0; $bill_sundry=0;
		$html.='<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
			<tbody>
			<tr>
			<td colspan="3" style="text-align:right;border:1px solid; font-weight: bold;">Total Qty</td>
			<td style="text-align:right;border:1px solid;font-weight: bold;width:15%">'.number_format($totalqty,4,".","").'</td>
		</tr>
		</tbody></table>
		';
				
		$html.='</div>
				<div style="clear:both;"></div>
				</div>';
			
		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
		// echo $html;exit;
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
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$purchaseorder_id.'.pdf','f');
		ob_clean();
		return 'salesorder_stock_deallocation_print_'.$id.'.pdf';
	}

?>