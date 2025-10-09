<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once("../../crm/include/crm_common_functions.php");
//include_once(COMMON_FUNCTION_PATH."common_production_functions.php");
$_SESSION['contents']=''; 
$form="Inquiry Review";
$mode="Print";
$inquiryid =$dbcon->real_escape_string($_REQUEST['id']);
$type='pdf';
if(strtolower($type) == 'pdf') {

	$query  = "select rerviw.*,inq.inquiry_no,inq.inquiry_date,cus.cust_name from tbl_inquiry_review as rerviw 
	left join tbl_inquiry as inq on inq.inquiry_id = rerviw.inquiry_id
	left join tbl_customer as cus on cus.cust_id = inq.cust_id
	where inquiry_review_status=0 and rerviw.inquiry_id=".$inquiryid;

	$result = $dbcon->query($query);
	$rel = brp_mysqli_fetch_array($result);

	/*$order_date='';
	if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
	{
		$order_date=date('d-m-Y',strtotime($rel['order_date']));
	}*/

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	
	
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
					<td class="backtdcolor" style="text-align:center"><h2>Inquiry Review Form</h2></td>
					<td class="backtdcolor" style="text-align:center">F-P 07-03 R0</td>
				</tr>
			</table><br>
			<table>
				<tr>
					<td class="backtdcolor" style="width:20%"><strong>Inquiry No. & Date</strong></td>
					<td style="width:80%">'.$rel['inquiry_no'].' & '.date('d-m-Y',strtotime($rel['inquiry_date'])).'</td>
				</tr>
				<tr>
					<td class="backtdcolor"><strong>Customer Name</strong></td>
					<td>'.$rel['cust_name'].'</td>
				</tr>
			</table>';

	$footer = '<table>
		<tr>
			<td class="backtdcolor" style="width:35%"><strong>Ref.WO No.</strong>(If Order Recieved)</td>
			<td style="width:15%;text-align:center">'.$rel['ref_wo_no'].'</td>
			<td class="backtdcolor" style="width:25%"><strong>Reviewed By</strong></td>
			<td style="width:25%">'.$rel['reviewed_by'].'</td>
		</tr>
		<tr>
			<td class="backtdcolor"><strong>WO Date</strong></td>
			<td style="text-align:center">'.date('d-m-Y',strtotime($rel['wo_date'])).'</td>
			<td class="backtdcolor"><strong>Approved By</strong></td>
			<td>'.$rel['approved_by'].'</td>
		</tr>
	</table>';

	$html ='<html>
	<head>					
	<title>'.$form.' - '.$rel['inquiry_no'].'</title>
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
			padding:5px;
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
		<htmlpagefooter name="otherpages_footer" style="display:none">
		<div style="text-align:center">'.$footer.'</div>
		</htmlpagefooter>
		<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
		<div>
			<table style="border:none">
				<tr>
					<td style="width:10%;border:none">1.</td>
					<td style="width:86%;height:60px;border:none">Customer Address</td>
					<td style="width:2%;border:none">'.get_review_opinion($dbcon,$rel['customer_address'],'yes').' Y </td>
					<td style="width:2%;border:none;text-align:left" >'.get_review_opinion($dbcon,$rel['customer_address'],'no').' N </td>';
				$html .='</tr>
				<tr>
					<td style="width:10%;border:none">2.</td>
					<td style="width:70%;height:60px;border:none">Enquiry No. & Date</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['inquiry_no_date'],'yes').' Y</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['inquiry_no_date'],'no').' N</td>
				</tr>
				<tr>
					<td style="width:10%;border:none">3.</td>
					<td style="width:70%;height:60px;border:none">Technical Specification of the items available</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['technical_spacification'],'yes').' Y</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['technical_spacification'],'no').' N</td>
				</tr>
				<tr>
					<td style="width:10%;border:none">4.</td>
					<td style="width:70%;height:60px;border:none">Applicable API Product Specification Requirements</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['pro_speci_req'],'yes').' Y</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['pro_speci_req'],'no').' N</td>
				</tr>
				<tr>
					<td style="width:10%;border:none">5.</td>
					<td style="width:70%;height:60px;border:none">Customer Drawing Enclosed</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['cust_draw_enclose'],'yes').' Y</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['cust_draw_enclose'],'no').' N</td>
				</tr>
				<tr>
					<td style="width:10%;border:none">6.</td>
					<td style="width:70%;height:60px;border:none">Scope Of Inspection</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['scope_inspection'],'yes').' Y</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['scope_inspection'],'no').' N</td>
				</tr>
				<tr>
					<td style="width:10%;border:none">7.</td>
					<td style="width:70%;height:60px;border:none">Delivery</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['delivery'],'yes').' Y</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['delivery'],'no').' N</td>
				</tr>
				<tr>
					<td style="width:10%;border:none">8.</td>
					<td style="width:70%;height:60px;border:none">Pricing Available</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['pricing_available'],'yes').' Y</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['pricing_available'],'no').' N</td>
				</tr>
				<tr>
					<td style="width:10%;border:none">9.</td>
					<td style="width:70%;height:60px;border:none">Commercial Terms clear</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['com_term_clear'],'yes').' Y</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['com_term_clear'],'no').' N</td>
				</tr>
				<tr>
					<td style="width:10%;border:none">10.</td>
					<td style="width:70%;height:60px;border:none">Earnest Money Deposit</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['earn_money_deposit'],'yes').' Y</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['earn_money_deposit'],'no').' N</td>
				</tr>
				<tr>
					<td style="width:10%;border:none">11.</td>
					<td style="width:70%;height:60px;border:none">Bank Gurantee /D.D./TDR</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['bank_guarantee_dd_tdr'],'yes').' Y</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['bank_guarantee_dd_tdr'],'no').' N</td>
				</tr>
				<tr>
					<td style="width:10%;border:none">12.</td>
					<td style="width:70%;height:60px;border:none">Separate Cover for price & Technical BID</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['sep_cov_price_techbid'],'yes').' Y</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['sep_cov_price_techbid'],'no').' N</td>
				</tr>
				<tr>
					<td style="width:10%;border:none">13.</td>
					<td style="width:70%;height:60px;border:none">Delivery Due Date</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['del_due_date'],'yes').' Y</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['del_due_date'],'no').' N</td>
				</tr>

				<tr>
					<td style="width:10%;border:none">14.</td>
					<td style="width:70%;height:60px;border:none">Any Other Comments</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['any_other_comment'],'yes').' Y</td>
					<td style="width:5%;border:none">'.get_review_opinion($dbcon,$rel['any_other_comment'],'no').' N</td>
				</tr>
			</table>
		</div>';
		
		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
		/*echo $header.$html;exit;*/
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','40','10','1','1');

		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);

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

	function get_review_opinion($dbcon,$rev_par,$flag){
		if($flag=='yes'){
			if($rev_par==1){
				$box = '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
			}else{
				$box = '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
			}
		}else{
			if($rev_par==1){
				$box = '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
			}else{
				$box = '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
			}
		}
		
		return $box;
	}
?>