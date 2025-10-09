<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once("../../crm/include/crm_common_functions.php");
//include_once(COMMON_FUNCTION_PATH."common_production_functions.php");
$_SESSION['contents']=''; 
$form="Order Review";
$mode="Print";
$workorder_id =$dbcon->real_escape_string($_REQUEST['id']);

$type='pdf';
if(strtolower($type) == 'pdf') {

	 $query  = "select wf.*, sp.po_req_no, sp.po_req_date, so.po_no, so.sales_order_date, l.l_name, so.project_name, so.remark from tbl_libra_workorder_fields as wf
	left join tbl_set_main_process as sp on sp.sp_id=wf.workorder_id
	left join tbl_sales_ordertrn as strn on strn.sales_ordertrn_id = sp.sales_order_trn_id
	left join tbl_sales_order as so on so.sales_order_id=strn.sales_order_id
	left join tbl_ledger as l on l.l_id = so.cust_id
	where  wf.workorder_id=".$workorder_id;
// die;
	$result = $dbcon->query($query);
	$rel = brp_mysqli_fetch_array($result);

	$sales_order_date='';
	if($rel['sales_order_date']!="1970-01-01" && $rel['sales_order_date']!="0000-00-00" && !empty($rel['sales_order_date']))
	{
		$sales_order_date=date('d-m-Y',strtotime($rel['sales_order_date']));
	}

	$wo_date='';
	if($rel['po_req_date']!="1970-01-01" && $rel['po_req_date']!="0000-00-00"  && !empty($rel['po_req_date']))
	{
		$wo_date=date('d-m-Y',strtotime($rel['po_req_date']));
	}

	$user_qry = "select user.user_name, user.user_mail, user.user_phone, user.user_type, led.common_email_id from users as user
	left join tbl_ledger as led on led.l_id=user.employee_id
	where user.user_id=".$_SESSION['user_id']." and user.company_id=".$rel['company_id'];
	$user_data = mysqli_fetch_assoc($dbcon->query($user_qry));
	/* Check Discount is On or off Start */
	
	$companyConfiguration=getCompanyConfiguration($dbcon);
	
	$header = '<table>
				<tr>
					<td rowspan="2" style="width:10%"></td>
					<td class="backtdcolor" style="text-align:center;width:70%"><h2>Libra Engineering Works</h2></td>
					<td class="backtdcolor" style="text-align:center;width:20%">Page 1 Of 1</td>
				</tr>
				<tr>
					<td class="backtdcolor" style="text-align:center"><h2>Work Order</h2></td>
					<td class="backtdcolor" style="text-align:center">F-P-07-05 R0</td>
				</tr>
				<tr>
				<td colspan="3" style="border:none"></td>
				</tr>
			</table>
			<table>
				<tr>
					<td class="backtdcolor" style="width:20%"><strong>Work Order#</strong></td>
					<td >'.$rel['po_req_no'].'</td>
					<td class="backtdcolor" style="width:20%"><strong>Work Order Date</strong></td>
					<td >'.$wo_date.'</td>
				</tr>
				<tr>
					<td class="backtdcolor"><strong>Customer PO#</strong></td>
					<td>'.$rel['po_no'].'</td>
					<td class="backtdcolor"><strong>Order Review Form#</strong></td>
					<td>'.$sales_order_date.'</td>
				</tr>

				<tr>
					<td class="backtdcolor"><strong>Customer Name</strong></td>
					<td colspan="3">'.$rel['l_name'].'</td>
					
				</tr>
				<tr>
					<td class="backtdcolor"><strong>Project</strong></td>
					<td colspan="3">'.$rel['project'].'</td>
					
				</tr>
				<tr>
					<td class="backtdcolor"><strong>Remark</strong></td>
					<td colspan="3">'.$rel['remark'].'</td>
					
				</tr>

			</table>';

	/*$footer = '<table>
		<tr>
			<td rowspan="2" class="backtdcolor" style="width:16%"><strong>WO Issue Date</strong></td>
			<td rowspan="2"   style="width:28%;text-align:center">'.$rel['ref_wo_no'].'</td>
			<td  class="backtdcolor" style="width:16%"><strong>Prepared By</strong></td>
			<td  style="width:40%">'.$rel['reviewed_by'].'</td>
		</tr>
		<tr>
			
			<td  style="width:16%" class="backtdcolor"><strong>Approved By</strong></td>
			<td style="width:40%">'.$rel['approved_by'].'</td>
		</tr>
	</table>';*/

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

		table td{
			border:1px solid #000 !important;

			/*page-break-inside:avoid;*/
		}
		.quot_annex_content_div table tr,td{
			padding:2.5px;
			page-break-before:always;
		}
		.backtdcolor{
			background-color: #bfbfbf;
		}

		</style>
		</head>
		<body>

		<htmlpageheader name="otherpages" style="display:none">
		<div style="text-align:center">'.$header.'</div>
		</htmlpageheader>
	<!--	<htmlpagefooter name="otherpages_footer" style="display:none">
		<div style="text-align:center">'.$footer.'</div>
		</htmlpagefooter> -->
		<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
		
				<table style="border:none">
				<tr>
					<td colspan="8" class="backtdcolor" style="text-align:left;"><strong><u>FOR MANUFACTURING USE</u></strong></td>
				</tr>
			
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Item & Materials</td>
					<td colspan="7" style="width:75%">'.$rel['po_item'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Customer Po Item Sr.#</td>
					<td colspan="7" style="width:75%">'.$rel['po_item_sr'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Ref. Approved Datasheet#</td>
					<td colspan="7" style="width:75%">'.$rel['datasheet'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Ref. Approved GAD#</td>
					<td colspan="7" style="width:75%">'.$rel['gad'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Ref. Approved QAP#</td>
					<td colspan="7" style="width:75%">'.$rel['qap'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Valve Type</td>
					<td colspan="7" style="width:75%">'.$rel['valve_type'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Size & Class</td>
					<td colspan="7" style="width:75%">'.$rel['size_class'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">QSL#</td>
					<td colspan="7" style="width:75%">'.$rel['qsl'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Qty.</td>
					<td colspan="7" style="width:75%">'.$rel['qty'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Valve Sr.#</td>
					<td colspan="7" style="width:75%">'.$rel['valve_sr'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">MOC</td>
					<td colspan="7" style="width:75%">'.$rel['moc'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Service</td>
					<td colspan="7" style="width:75%">'.$rel['service'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Design Standard</td>
					<td colspan="7" style="width:75%">'.$rel['design_standard'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Testing Standard</td>
					<td colspan="7" style="width:75%">'.$rel['testing_standard'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Specific Mfg. Req.</td>
					<td colspan="7" style="width:75%">'.$rel['mfg_req'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Specific Test. Req.</td>
					<td colspan="7" style="width:75%">'.$rel['test_req'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">TPISCOPE</td>
					<td colspan="7" style="width:75%">'.$rel['tpi_scope'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">After Sales Service Req</td>
					<td colspan="7" style="width:75%">'.$rel['sales_service_req'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Coating / Painting Req.</td>
					<td colspan="7" style="width:75%">'.$rel['coating_painting_req'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Packing Req.</td>
					<td colspan="7" style="width:75%">'.$rel['packing_req'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Marking On Product</td>
					<td colspan="7" style="width:75%">'.$rel['marking_on_product'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Marking On Packing</td>
					<td colspan="7" style="width:75%">'.$rel['marking_on_packing'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">API Monogram Marking</td>
					<td colspan="7" style="width:75%">'.$rel['api_monogram_marking'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Delivery Due Date</td>
					<td colspan="7" style="width:75%">'.$rel['del_dua_date'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Customer Contact Details</td>
					<td colspan="7" style="width:75%">'.$rel['customer_cont_details'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Delivery Location</td>
					<td colspan="7" style="width:75%">'.$rel['del_location'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Documents to be Submit</td>
					<td colspan="7" style="width:75%">'.$rel['documents'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Payment Temrs</td>
					<td colspan="7" style="width:75%">'.$rel['payment_terms'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Insurance</td>
					<td colspan="7" style="width:75%">'.$rel['insurance'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Freight</td>
					<td colspan="7" style="width:75%">'.$rel['freight'].'</td>
				</tr>
				<tr>
					<td colspan="1" style="white-space:nowrap;width:20%">Additional Req.</td>
					<td colspan="7" style="width:75%">'.$rel['additional_req'].'</td>
				</tr>
			</table>
		
		';

		$html.='<table>
		<tr>
		<td colspan="4" style="border:none"></td>
		<tr>
		<tr>
			<td rowspan="2" class="backtdcolor" style="width:23%"><strong>WO Issue Date</strong></td>
			<td rowspan="2"   style="width:25%;text-align:center">'.$rel['ref_wo_no'].'</td>
			<td  class="backtdcolor" style="width:16%"><strong>Prepared By</strong></td>
			<td  style="width:37%">'.$rel['reviewed_by'].'</td>
		</tr>
		<tr>
			
			<td  style="width:16%" class="backtdcolor"><strong>Approved By</strong></td>
			<td style="width:37%">'.$rel['approved_by'].'</td>
		</tr>
	</table>';
		
		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
		/*echo $header.$html;exit;*/
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','52','10','1','1');

		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		// $mpdf->SetHTMLFooter($footer);

		//Show page number
		/*$mpdf->pagenumPrefix = ' ';
		$mpdf->pagenumSuffix = ' / ';
		$mpdf->nbpgPrefix = ' ';
		$mpdf->nbpgSuffix = ' pages';
		$mpdf->SetFooter('{PAGENO}{nbpg}');*/

		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='UTF-8';
		$mpdf->WriteHTML($html);
		$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$purchaseorder_id.'.pdf','f');
		ob_clean();
		return 'inquiry_review_'.$inquiryid.'.pdf';
	}

	
?>