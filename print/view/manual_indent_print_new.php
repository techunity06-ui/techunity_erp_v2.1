<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$include = '../../include/';
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	PRE_VIEW,
	PRE_VIEW
]);
if(!in_array(PRE_VIEW,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
$types='pdf';
if(strtolower($types) == 'pdf') {
	$path = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/'));
	$filename = pathinfo($path, PATHINFO_FILENAME);

	$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
	if($filename =="manual_indent_print_new"){
$form="Manual Indent";
		$query="select * from tbl_pre where pre_id=$invoiceid";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$indent_no = $rel['pre_no'];
		$indent_date = date('d/m/Y',strtotime($rel['pre_date']));
	} else if($filename =="manual_indent_print_single"){ 
		$form="Indent";
		$query="select po.*, smp.po_req_no, smp.vendor_id, l.l_name as vendor_name, smp.po_no from tbl_request_product as po
		left join tbl_set_main_process as smp on smp.sp_id = po.sp_id
		left join tbl_ledger as l on l.l_id = smp.vendor_id
		where po.rp_id=$invoiceid";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$indent_no = $rel['indent_no'];
		$indent_date = date('d/m/Y',strtotime($rel['indent_date']));
	}

	$set="select * from tbl_company where company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));

	$companyConfiguration=getCompanyConfiguration($dbcon);
	$purchase_pro_search=explode(",", $companyConfiguration['purchase_pro_search']);

	$html='';
	$header =get_header($dbcon,'text-align: center','5.7in','150px');
	$footer = '<img src="'.DOMAIN_F.LOGO.$set_head['f_logo'].'" style="width:100%" />';

	$html.='<html>
	<head>					
	<title>'.$form.' - '.$indent_no.'</title>

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
		<htmlpageheader name="otherpages" style="display:none">
		<div style="text-align:center">'.$header.'</div>
		</htmlpageheader>
		<htmlpagefooter name="otherpages_footer" style="display:none">
		<div style="text-align:center">'.$footer.'</div>
		</htmlpagefooter>
		<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
		<div>
		<table>
		<tr style="border:1px solid; ">
		<td colspan="4" style="text-align:center;font-size:18px;font-weight:bold;">'.$form.'</td>
		</tr>
		<tr style="border:none; border-left:1px solid; border-right:1px solid;">
		<td style="border: 0px; width: 25%; font-weight: bold;">Manual Indent No</td>
		<td style="border: 0px; width: 25%;">: '.$indent_no.'</span></td>
		<td style="border: 0px; border-left: 1px solid; width: 25%; font-weight: bold;">Indent Date</td>
		<td style="border: 0px; width: 25%;">: '.$indent_date.'</td>
		</tr>
		</table>
		</div>
		<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		<thead>
		<tr>
		<td width="5%" style="text-align:center;"><b>Sr No.</b> </td>
		<td width="60%" style="text-align:left;"><b>Item Description</b></td>
		<td width="10%" style="text-align:center;"><b>Qty</b> </td>
		<td width="10%" style="text-align:center;"><b>Rate</b> </td>
		<td width="15%" style="text-align:center;"><b>Vendor</b> </td>
		</tr>
		</thead>
		<tbody>';
		if($filename =="manual_indent_print_new"){
			$qry="select trn.*,product.product_icode,product.product_name,per.unit_name,wor.po_req_no,led.l_name,product.product_desc FROM `tbl_pre_trn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unitid
			left join tbl_set_main_process as wor on wor.sp_id = trn.sp_id
			left join tbl_ledger as led on led.l_id=trn.vender_id
			where pre_trn_status=0 and pre_id=".$rel['pre_id'];
		} else if($filename =="manual_indent_print_single"){
			$qry="SELECT trn.*,product.*,product.product_desc,per.unit_name FROM `tbl_pre_trn` as trn 
			left join product_mst as product on product.product_id=trn.product_id 
			left join unit_mst as per on per.unitid=trn.unitid 
			where trn.pre_trn_id=".$rel['pre_trn_id'];
		}
		$result=$dbcon->query($qry);		
		$i=1;$total=0;$discount=0;$totalqty=0;
		$cnt=mysqli_num_rows($result);
		while($trn_rel=mysqli_fetch_assoc($result))
		{
			$item_code = '';
			if(in_array('item',$purchase_pro_search)){
				$item_code = " -- (".$trn_rel['product_icode'].")";
			}
			$html.='<tr>
			<td style="vertical-align: top; text-align:center;">'.$i.' </td>
			<td style="border: none;text-align:left; vertical-align: top;"><strong>'.stripcslashes($trn_rel['product_name']).''.$item_code.'<br>Work Order No :</strong>'.$trn_rel['po_req_no'].'<br>'.$trn_rel['product_desc'].'</td>
			<td style="vertical-align: top; text-align:center;">'.without_comma_two_digit_amount($trn_rel['product_qty']).' '.$trn_rel['unit_name'].'</td>
			<td style="vertical-align: top; text-align:center;"> '.without_comma_two_digit_amount($trn_rel['rate']).'</td>
			<td style="vertical-align: top; text-align:center;"> '.$trn_rel['l_name'].'</td>
			</tr>';

			$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
			$ttl_amt=$ttl_amt+$trn_rel['rate'];
			$p++;
		}
		$pr=10-$cnt;
		for($j=0; $j<$pr; $j++){
			$html .= '<tr style="border:none;border-left:1px solid;border-right:1px solid;height:30px">
			<td style="height:30px; text-align:center; border-right: 1px solid; vertical-align:top;"></td>
			<td style="height:30px; text-align:center; border-right: 1px solid; vertical-align:top;"></td>
			<td style="height:30px; text-align:center; border-right: 1px solid; vertical-align:top;"></td>
			<td style="height:30px; text-align:center; border-right: 1px solid; vertical-align:top;"></td>
			<td style="height:30px; text-align:center; border-right: 1px solid; vertical-align:top;"></td>
			</tr>';
		}
		$html.='
		<tr>
		<td colspan="2" style=" text-align:right;">TOTAL  </td>
		<td  style=" text-align:center;">'.without_comma_two_digit_amount($ttl_qty,2).'</td>
		<td  style=" text-align:center;">'.without_comma_two_digit_amount($ttl_amt,2).'</td>
		<td  style=" text-align:center;"></td>
		</tr>
		</tbody></table>
		<table style="page-break-inside: avoid;border-top:none;border-bottom:none" >
		<tr style="border-bottom:none">
		<td style="height:50px;border-bottom:none;vertical-align:top;text-align:right;font-weight:bold;">For '.$set_head['company_name'].'</td>
		</tr>
		<tr style="border-top:none">
		<td style="border-top:none;text-align:right;vertical-align: bottom;">Authorised Signatory</td>
		</tr>
		</table>';
		/* Get Terms And Condition Start */
		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
		// echo $header;
		// echo $html;exit;
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','35','10','1','1');
//		$mdf->SetFont('ProximaNova');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='UTF-8';
		$mpdf->WriteHTML($html);
		$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
		ob_clean();
		return $form.'-'.$indent_no.'.pdf';
	}	
?>