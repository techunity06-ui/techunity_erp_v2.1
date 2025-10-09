<?php
$quotation_id = $_REQUEST['id'];
if (!empty($quotation_id)) {
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
	include("../../include/function_database_query.php");
	include_once(COMMON_FUNCTION_PATH . "common_functions.php");

	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = " . $_SESSION['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
	$userPhone = $userData['user_phone'] ? 'Mo.: ' . $userData['user_phone'] : '';
	$userEmail = $userData['user_mail'] ? ' - Email: ' . $userData['user_mail'] : '';
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		QUOTATION_SLUG_PRINT,
	]);

	if (!in_array(QUOTATION_SLUG_PRINT, $bulkAccessArray)) {
		header("Location: " . DOMAIN . "permission_access");
	}
	quotation_print($dbcon,$quotation_id,$save_file = "No");
}

function quotation_print($dbcon, $quotation_id, $save_file)
{
	$type = 'pdf';
	if (brp_strtolower($type) == 'pdf') {
		//Quotation Data
		$query = "select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name,user_led.l_name,user_led.emp_mobile,user_led.emp_email,utype.usertype_name,currency.currency_symbol from tbl_quotation as quot
	left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
	left join tbl_customer as cust on cust.cust_id=quot.cust_id
	left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
	left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
	left join users as usa on usa.user_id=quot.user_id
	left join tbl_ledger as user_led on user_led.l_id=usa.employee_id
	left join tbl_currency as currency on currency.currency_id=quot.currency_id
	left join tbl_usertype as utype on utype.usertype_id=user_led.emp_user_type
	where quot.quotation_id=" . $quotation_id;
		$rel = brp_mysqli_fetch_assoc($dbcon->query($query));

		if (!$rel) {
			header("Location: " . ROOT . CRM_ROOT . "quotation_list");
		}

		$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';

		$set = "select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=" . $rel['company_id'];

		$comp_rel = brp_mysqli_fetch_assoc($dbcon->query($set));

		$header = get_header($dbcon, 'text-align: center', '4in', '80px') . '
		<table border="0" style="font-size: 15px; font-family: Proxia Nova; border: 0;">
		<tr style="border: 0;">
				<td colspan="2" style="border: 0; text-align: right;"><input type="checkbox">&nbsp;Original&nbsp;&nbsp;<input type="checkbox">&nbsp;Duplicate&nbsp;&nbsp;<input type="checkbox">&nbsp;Triplicate</td>
			</tr>
			<tr style="border: 0;">
				<td style="border: 0; text-align: left; font-size: 15px;width:50%;">
					<div style="text-align:left;">Ref. No. ' . $rel["quotation_no"] . '</div>
				</td>
				<td style="border: 0; text-align: right; font-size: 15px;width:50%;white-space:nowrap;">Ref. Date : ' . date("d-M-Y", strtotime($rel['quotation_date'])) . '</td>
			</tr>
		</table>';
		$footer = '<img src="' . DOMAIN_F . LOGO . $comp_rel["f_logo"] . '" style="width: 8.27in" />';

		$greting_cotent = "Dear Sir,<br/><br/>
				
				We are extremely happy and very thankful to you for forwarding us your valuable inquiry. Here we send you a most
				competitive offer for the same as per following terms & conditions.";

		$thankyou_content = "We trust our quotation will meet with your requirement and find your approval . In case you need any further
technical/commercial clarification, please consider at your disposal.";

		$company_content = "Thanking You,<br/> 
							For, " . $comp_rel['company_name'] . "<br/><br/>
							" . $rel['l_name'] . "<br/>" . $rel['usertype_name'] . " <br/> " . $rel['emp_mobile'] . " <br/> " . strtolower($rel['emp_email']);

		$quot_annex_content = $rel['quot_annex_content'];

		$approve_status = '';
		if ($rel['approve_status'] == '0') {
			$approve_status = ' (DRAFT)';
		}
		$disc_qry = $dbcon->query("SELECT SUM(trn.product_discount) as discount from tbl_quotation_trn as trn where trn.quot_trn_status=0 and trn.quotation_id=" . $rel['quotation_id']);
		$disc_qrys = brp_mysqli_fetch_assoc($disc_qry);

		$html = '<html>
					<head>					
						<title>Quotation - ' . $rel['quotation_no'] . '</title>
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
	<div style="text-align:center">' . $header . '</div>
	</htmlpageheader>
	<htmlpagefooter name="otherpages_footer" style="display:none">
	<div style="text-align:center">' . $footer . '</div>
	</htmlpagefooter>
	<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
						<div>
							<table cellpadding="5" cellspacing="5" border="0" style="font-size: 14px;  border: 0;">
								<tr style="border: 0;">
									<td style="border: 0;">
										<p style="float: left; width: 100%;"><strong>To,<br/>' . $rel['cust_name'] . ',</strong><br/>
											<strong style="color: #999999;">
											' . ($quot_address) . '</strong>
										</p>
										<br />
										<p style="float: left; width: 100%;margin-top: 20px;">
											<strong>Kind Attn:-</strong>
											<strong style="color: #999999;"><br/>' . $rel['c_con_fname'] . ' ' . $rel['c_con_lname'] . '<br/>Email Id : ' . strtolower($rel['c_con_email']) . '
											</strong>
										</p>
									</td>
								</tr>
							</table>
							<table cellpadding="5" cellspacing="5" border="0" style="font-size: 14px;  border: 0;margin-top: 20px;">
								<tr style="border: 0;">
									<td style="border: 0;">
										' . $greting_cotent . '
									</td>
								</tr>
							</table>
							<table cellpadding="5" cellspacing="5" border="0" style="font-size: 16px;  border: 0;margin-top: 20px;text-align:center;">
								<tr style="border: 0;">
									<td style="border: 0;font-weight: 700;">
										<b> QUOTATION </b>
									</td>
								</tr>
							</table>
							<table cellpadding="5" cellspacing="5" border="0" style="font-size: 13px;margin-top: 13px;">
								<tr>
									<td style="width:5%;">
										<b>Sr.No.</b>
									</td>
									<td style="width:40%;">
										<b>Particulars</b>
									</td>
									<td style="width:10%;">
										<b>Quantity</b>
									</td>
									<td style="width:15%;">
										<b>Rate (' . $rel['currency_symbol'] . ')</b>
									</td>';
		if ($disc_qrys['discount'] > 0) {
			$html .= '<td style="width:10%;">
										<b>Discount (' . $rel['currency_symbol'] . ')</b>
									</td>';
		}
		$html .= '<td style="width:15%;">
										<b>Amount (' . $rel['currency_symbol'] . ')</b>
									</td>
								</tr>';
		$trn_qry = "select trn.*,pro.product_name,unit.unit_name from tbl_quotation_trn as trn 
										left join product_mst as pro on pro.product_id=trn.product_id
										left join unit_mst as unit on unit.unitid=trn.unitid
										where trn.quot_trn_status=0 and trn.quotation_id=" . $rel['quotation_id'];
		$trn_qry_rs = $dbcon->query($trn_qry);
		$p = 1;
		$ttl_amt = 0;
		$ttl_qty = 0;
		$cnt = brp_mysqli_num_rows($trn_qry_rs);
		while ($trn_rel = brp_mysqli_fetch_assoc($trn_qry_rs)) {
			$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';
			$ratw = indian_number($trn_rel['product_rate'], 2);
			// $total_am=$trn_rel['product_amount']*$trn_rel["product_qty"];
			$total_am = indian_number($trn_rel['product_amount'], 2);
			$html .= '<tr>
														<td style="width:5%;">
															' . $p . '
														</td>
														<td style="width:40%;">
															<b>' . $trn_rel["product_name"] . '</b> <br/>
															' . $product_desc . '
														</td>
														<td style="width:10%;">
															' . $trn_rel["product_qty"] . ' ' . $trn_rel["unit_name"] . '
														</td>
														<td style="width:15%;">
															' . $ratw . '
														</td>';
			if ($disc_qrys['discount'] > 0) {
				$html .= '<td style="width:10%;">
															' . $trn_rel['product_discount'] . ' (' . $trn_rel['discount_per'] . ' %)
														</td>';
			}
			$html .= '<td style="width:15%;">
															' . $total_am . '
														</td>
													</tr>';
			$p++;
		}

		$html .= '</table>
							<table cellpadding="5" cellspacing="5" border="0" style="font-size: 13px;  border: 0;margin-top: 1px;margin-bottom: 0px;white-space:nowrap;page-break-inside: avoid;">
								<tr style="border: 0;">
									<td style="border: 0;">
										' . $thankyou_content . '
									</td>
								</tr>
							</table>
							<table cellpadding="5" cellspacing="5" border="0" style="font-size: 14px;  border: 0;margin-top: 17px;text-align:left;">
								<tr style="border: 0;">
									<td style="border: 0;">
										' . $company_content . '
									</td>
								</tr>
							</table>
						</div>
						
						<table cellpadding="5" cellspacing="5" border="0" style="font-size: 14px;  border: 0;margin-top: 20px;">
								<tr style="border: 0;">
									<td style="border: 0;">
										' . $quot_annex_content1 . '
									</td>
								</tr>
							</table>
						
						
<!--page1 end-->';

		/*$html.='';*/

		/* Check Annexure Attachments Start */
		if (trim($rel['quot_annex_content'])) {
			$html .= '<center class="nextpage"></center>';
			$html .= '<div class="quot_annex_content_div" style="font-size: 16px;">' . $rel['quot_annex_content'];
			$html .= '</div>';
		}
		/* Check Annexure Attachments End */

		/* Get Terms And Condition Start */
		$terms_qry = "select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=" . $rel['quotation_id'] . " order by qtrm.tc_priority";
		$terms_qry_rs = $dbcon->query($terms_qry);
		if (brp_mysqli_num_rows($terms_qry_rs)) {
			$html .= '<center class="nextpage"></center>
<div>
	<table width="100%" style="font-size:16px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3">
		<tbody>
			<tr style="border: 0;">
				<td style="border: 0; padding-top: 20px;">
					<b>TERMS AND CONDITION</b>
				</td>
			</tr>
		</tbody>
	</table>
	<table width="100%" style="font-size:13px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3"><tbody>
';
			$t = 1;
			while ($term_rel = brp_mysqli_fetch_assoc($terms_qry_rs)) {
				$string = (nl2br($term_rel['tc_details']));

				$html .= '<tr style="border: 0;">
					<td style="border: 0; width:25%;vertical-align: top;"><b>' . $t . ' ' . $term_rel['tc_name'] . '</b></td>
					<td style="border: 0; width:3%;vertical-align: top;"><b>:</b></td>
					<td style="border: 0;width:75%;text-align:left;padding:5px;vertical-align: top;">' . $string . '</td>
		
		</tr>';

				$t++;
			}
			$html .= '</tbody></table></div>';
		}
		/* Get Terms And Condition Start */



		$html .= '<sethtmlpagefooter name="otherpages_footer" value="on" />
</body>
</html>';
		//echo $html;exit;
		ob_end_clean();
		$file_name = $rel['quotation_no'].'.pdf';
		$file_name=str_ireplace("/","_",$file_name);
		if($save_file=="No"){
			include("../../view/export/mpdf/mpdf.php");
		}else{
			include("../../../view/export/mpdf/mpdf.php");
		}

		//$mpdf=new mPDF('','A4','0','proximanova','10','10','45','10','1','1');
		$mpdf = new mPDF('', 'A4', '0', 'calibri', '10', '10', '47', '30');
		//		$mdf->SetFont('ProximaNova');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		$mpdf->useSubstitutions = true;

		//Show page number : Dimple Panchal (05-Apr-2021)
		//             $mpdf->pagenumPrefix = ' ';
		//             $mpdf->pagenumSuffix = ' / ';
		//             $mpdf->nbpgPrefix = ' ';
		// $mpdf->nbpgSuffix = ' pages';
		//$mpdf->SetFooter('{PAGENO}{nbpg}');

		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion = true;
		//$mpdf->charset_in='UTF-8';
		$mpdf->WriteHTML($html);
		if($save_file=="No"){
			$mpdf->Output();
		}else{
			$mpdf->Output('../../../view/upload/mail_attach/'.$file_name,'f');
		}
		ob_clean();
		return $file_name;
	}
}
