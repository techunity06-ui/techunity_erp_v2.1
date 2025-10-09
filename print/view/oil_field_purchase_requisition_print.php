<?php session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$_SESSION['contents']=''; 
$form="Purchase Requisition Form";
$mode="Print";
$type='pdf';
// print_r($_GET);
if(strtolower($type) == 'pdf') {
	$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select po.*, smp.po_req_no, smp.vendor_id, l.l_name as vendor_name, smp.po_no from tbl_request_product as po
	left join tbl_set_main_process as smp on smp.sp_id = po.sp_id
	left join tbl_ledger as l on l.l_id = smp.vendor_id
	where po.rp_id=$purchaseorder_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	$indent_date='';
	if($rel['indent_date']!="1970-01-01" && $rel['indent_date']!="0000-00-00")
	{
		$indent_date=date('d-m-Y',strtotime($rel['indent_date']));
	}

	$consignee="select * from tbl_cust_contact_person as cust where cust.cust_contact_person_status = 0 and cust.cust_id=".$rel['vendor_id'];
	$cons_data=mysqli_fetch_assoc($dbcon->query($consignee));
	
	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));

	// if($rel['currency_enable']=='0'){
		$currency_name = '(INR)';
		$currency_word_start = 'Rupees';
		$currency_word_end = 'Paise';
	// }else{
	// 	$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
	// 	$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

	// 	$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
	// 	$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
	// 	$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
	// }

	$chkreview = $dbcon->query("SELECT pa.*, users.user_name FROM tbl_purchaseorder_aprv_log as pa LEFT JOIN users AS users ON users.user_id = pa.user_id WHERE pa.approve_status = 3 AND pa.purchaseorder_id = ".$rel['purchaseorder_id']." ORDER BY pa.purchaseorder_aprv_id DESC LIMIT 1");
	$getreview = brp_mysqli_fetch_assoc($chkreview);

	$chkapprv = $dbcon->query("SELECT pfa.*, users.user_name FROM tbl_purchaseorder_finance_aprv_log as pfa LEFT JOIN users AS users ON users.user_id = pfa.user_id WHERE pfa.approve_status = 1 AND pfa.purchaseorder_id = ".$rel['purchaseorder_id']." ORDER BY pfa.po_finance_approve_id DESC LIMIT 1");
	$getapprv = brp_mysqli_fetch_assoc($chkapprv);

	$colspan=1;
	$cols=6;

	$header ='<table style="border: none; width: 100%;">
	<tbody>
	<tr style="border: none;">
	<td style="border: none; width: 35%; vertical-align: top;"><img src="'.DOMAIN_F.LOGO.'Oil Field-2.png" style="height: 100px;"/></td>
	<td style="border: none; width: 65%; text-align: center;"><h1>'.$form.'</h1></td>
	</tr>
	</tbody>
	</table>';
	$footer ='<table style="border: none; width: 100%;">
	<tbody>
	<tr style="border: none;">
	<td style="border: none; font-size: 10px; text-align: center; font-weight: bold;">Note: If correction required, correction shall be made by the process owner of the report by striking the error out and initial it with the date.</td>
	</tr>
	<tr style="border: none; border-top: 1px solid;">
	<td style="text-align: right;font-size: 10px; border: none;">FR/303-01 Issue 2 Rev 2</td>
	</tr>
	</tbody>
	</table>';

	$approve_status='';
	if($rel['approve_status']=='0'){
		$approve_status=' (DRAFT)';
	}

	$html ='<html>
	<head>					
	<title>'.$form.' - '.$rel['po_req_no'].'</title>
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
		<tr>
		<td rowspan="2">FOR<br>ITEM</td>
		<td style="border: none;">WORK ORDER NO</td>
		<td style="border: none;">'.$rel['po_req_no'].'</td>
		<td style="border: none;"><input type="checkbox" class="form-control"/> OUTSOURCE</td>
		<td style="border: none;"><input type="checkbox" class="form-control"/> OFFICE</td>
		<td style="border: none;"><input type="checkbox" class="form-control"/> WORKSHOP</td>
		<td style="border: none;"><input type="checkbox" class="form-control"/> QUALITY</td>
		</tr>
		<tr>
		<td style="border: none;"><input type="checkbox" class="form-control"/> EQUIPMENT</td>
		<td style="border: none;"><input type="checkbox" class="form-control"/> MATERIAL</td>
		<td style="border: none;"><input type="checkbox" class="form-control"/> STATIONERY</td>
		<td style="border: none;"><input type="checkbox" class="form-control"/> OTHERS</td>
		<td style="border: none;"></td>
		<td style="border: none;"></td>
		</tr>
		</table>
		<table style="margin-top: 5px;">
		<tr style="border-bottom: none;">
		<td colspan="4"><strong>REQUEST INFO</strong></td>
		<td colspan="4"><strong>PURCHASE INFO</strong></td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td style="border: none;">Requested by:</td>
		<td style="border: none;">'.$userData['user_name'].'</td>
		<td style="border: none;">Date:</td>
		<td style="border-right: 1px solid; border-left: none;">'.date('d-m-Y').'</td>
		<td style="border: none;">VENDOR:</td>
		<td style="border: none;" colspan="3">'.$rel['vendor_name'].'</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td style="border: none;" colspan="2">Urgent&nbsp;&nbsp;<input type="checkbox" class="form-control"/>&nbsp;&nbsp;&nbsp;&nbsp;Routine&nbsp;&nbsp;<input type="checkbox" class="form-control"/></td>
		<td style="border: none;">Date Required:</td>
		<td style="border-right: 1px solid; border-left: none;"></td>
		<td style="border: none;">PURCHASE BY:</td>
		<td style="border: none;">Sakthivel</td>
		<td style="border: none;">DATE:</td>
		<td style="border: none;">'.date('d-m-Y', strtotime($rel['indent_date'])).'</td>
		</tr>
		<tr style="border-top: none;">
		<td style="border: none;"></td>
		<td style="border: none;"></td>
		<td style="border: none;"></td>
		<td style="border-right: 1px solid; border-left: none;"></td>
		<td style="border: none;"><strong>PO NUMBER:</strong></td>
		<td style="border: none;" colspan="3">'.$rel['po_no'].'</td>
		</tr>
		</table>
		<table style="margin-top: 5px; font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		<thead>
		<tr>
		<th style="width:3%;text-align:center; border-right: 1px solid;">NO</th>
		<th style="width:5%;text-align:center; border-right: 1px solid;">QTY</th>
		<th style="width:8%;text-align:center; border-right: 1px solid;">UNIT</th>
		<th colspan="2" style="width:38%;text-align:center; border-right: 1px solid;">DESCRIPTION</th>
		<th style="width:10%;text-align:center; border-right: 1px solid;">CHARGE CODE</th>
		<th style="width:10%;text-align:center; border-right: 1px solid;white-space: nowrap;">UNIT PRICE'.$currency_name.'</th>
		<th style="width:10%;text-align:right; border-left: 1px solid;white-space: nowrap;">AMOUNT'.$currency_name.'</th>
		</tr>
		</thead>
		<tbody>';
		$qry="SELECT trn.*,product.*,product.product_desc,per.unit_name FROM `tbl_pre_trn` as trn 
		left join product_mst as product on product.product_id=trn.product_id 
		left join unit_mst as per on per.unitid=trn.unitid 
		where trn.pre_trn_id=".$rel['pre_trn_id'];
		$result=$dbcon->query($qry);		
		$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_i_gst=0;$gst_per = 0;$gst_rate = 0;
		$cnt=mysqli_num_rows($result);
		while($row=mysqli_fetch_assoc($result))
		{
			$pro_descri = (!$row['product_desc']) ? $row['product_desc'] : '';
			$html.='<tr style="border: none;border-right: 1px solid; border-left: 1px solid;">
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;">'.$i.'</td>
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;">
			'.$row['product_qty'].'
			</td>
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;">'.$row['unit_name'].'</td>
			<td colspan="2" style="text-align:left;border-right: 1px solid;vertical-align:top;">'.$row['product_name'].'</td>
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;"></td>
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;">'.number_format($row['rate'],2,".","").'</td>
			<td style="text-align:right;border-right: 1px solid;vertical-align:top;">'.number_format($row['rate']*$row['product_qty'],2,".","").'</td>
			</tr>
			<tr style="border: none;border-right: 1px solid; border-left: 1px solid;">
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;"></td>
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;"></td>
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;"></td>
			<td colspan="2" style="text-align:left;border-right: 1px solid;vertical-align:top;">'.$pro_descri.'</td>
			<td style="text-align:center;border-right: 1px solid;vertical-align:top;"></td>
			<td style="text-align:right;border-right: 1px solid;vertical-align:top;"></td>
			<td style="text-align:right;border-right: 1px solid;vertical-align:top;"></td>
			</tr>';
			$i++; 
			$totalqty=$totalqty+$row['product_qty'];
			$total_product_amount+=$row['rate']*$row['product_qty'];
		}
		$pr=14-$cnt;
		for($j=0; $j<$pr; $j++){
			$html.='<tr style="border: none; border-right: 1px solid; border-left: 1px solid;">
			<td style="border-right: 1px solid;"></td>
			<td style="border-right: 1px solid;"></td>
			<td style="border-right: 1px solid;"></td>
			<td colspan="2" style="border-right: 1px solid;height:25px;"></td>
			<td style="border-left: 1px solid;"></td>
			<td style="border-left: 1px solid;"></td>
			<td style="border-right: 1px solid;"></td>
			</tr>';
		}
		$remark = ($rel['remark']) ? $rel['remark'] : '';
		//$rows=(($rel['stateid']==$set_head['stateid']) ? 2 : 1)+2;
		$html.='<tr>
		<td></td>
		<td></td>
		<td></td>
		<td colspan="2"></td>
		<td colspan="2" style="text-align:center;font-weight: bold;">Total'.$currency_name.'</td>
		<td style="text-align:right;font-weight: bold;">'.number_format($total_product_amount,2,".","").'</td>
		</tr>
		</tbody>
		</table>
		<table style="margin-top: 5px;">
		<tr>
		<td style="border: none;">Reviewd by:</td>
		<td style="border: none;"></td>
		<td style="border: none;">Remarks (if any):</td>
		<td style="border: none;"></td>
		<td style="border: none;">Monthly PR:</td>
		<td style="border: none;"></td>
		<td style="border: none;">Date:</td>
		<td style="border: none;"></td>
		</tr>
		</table>
		<table style="margin-top: 5px;">
		<tr style="border-bottom: none;">
		<td style="border-right: none; border: none;" width="70%"><strong>APPROVAL INFO</strong>&nbsp;&nbsp;<input type="checkbox" class="form-control"/>&nbsp;&nbsp;Approved&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" class="form-control"/>&nbsp;&nbsp;Not Approved</td>
		<td style="border-left: none; border: none; text-align: right;" width="30%">Approved by: ______________</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td style="border-right: none; border: none; font-size: 11px; font-style: italic;">Note: In case of new vendors (or) PR approved without any reference quotes, the reason is to be stated.</td>
		<td style="border-left: none; border: none; text-align: right;">Date: _______________</td>
		</tr>
		<tr style="border-top: none;">
		<td style="border: none;" colspan="2">Remarks: __________________________________________________</td>
		</tr>
		</table>
		<div style="clear:both;"></div>
		</div>
		<!--page1 end-->';

		$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
		</body>
		</html>';
		// echo $html;exit;
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','30','5','1','1');
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
		return $form.' '.$purchaseorder_id.'.pdf';
	}

	?>