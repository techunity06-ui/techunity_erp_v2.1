<?php
$quotation_id = $_REQUEST['id'];
if (!empty($quotation_id)) {
	session_start();
	include ("../../config/config.php");
	include ("../../config/session.php");
	include ("../../include/function_database_query.php");
	include_once (COMMON_FUNCTION_PATH . "common_functions.php");
	$incPath = $path . 'include/';

	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		QUOTATION_SLUG_PRINT,
	]);

	if (!in_array(QUOTATION_SLUG_PRINT, $bulkAccessArray)) {
		header("Location: " . DOMAIN . "permission_access");
	}
	quotation_print($dbcon, $quotation_id, $save_file = "No");
}
function quotation_print($dbcon, $quotation_id, $save_file)
{
	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = " . $_SESSION['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
	$userPhone = $userData['user_phone'] ? '(M) : ' . $userData['user_phone'] : '';
	$userEmail = $userData['user_mail'] ? ' Email : ' . $userData['user_mail'] : '';

	$type = 'pdf';
	if (strtolower($type) == 'pdf') {
		//Quotation Data
		$query = "select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name, cadd.c_add_state from tbl_quotation as quot
		left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
		left join tbl_customer as cust on cust.cust_id=quot.cust_id
		left join tbl_cust_address as cadd on cadd.cust_id=quot.cust_id
		left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
		left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
		where quot.quotation_id=" . $quotation_id;
		$rel = mysqli_fetch_assoc($dbcon->query($query));
		//p($rel);
		if (!$rel) {
			header("Location: " . ROOT . CRM_ROOT . "quotation_list");
		}

		if ($rel['quot_type'] == '0') {
			$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`=68 ';
			$currency_rel = mysqli_fetch_assoc($dbcon->query($currency_sql));
			$currency_name = '(INR)';
			$currency_word_start = 'Rupees';
			$currency_word_end = 'Paise';
			$currency_symbol = $currency_rel['currency_symbol'];
		} else {
			$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="' . $rel['currency_id'] . '" ';
			$currency_rel = mysqli_fetch_assoc($dbcon->query($currency_sql));

			$currency_name = '(' . ucfirst(strtolower($currency_rel['currency_code'])) . ')';
			$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
			$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
			$currency_symbol = $currency_rel['currency_symbol'];
		}
		$quot_address = $rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';


		$set = "select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=" . $rel['company_id'];

		$comp_rel = mysqli_fetch_assoc($dbcon->query($set));

		$header = '<table style="border:0; border-color:white; height:200px;">
		<tr style="border:0;">
	<td style="width:40%; text-align:left;border:0;"> 
		<img src="../../view/img/logo/' . $comp_rel["logo"] . '" style="width: 165px; height:60px; padding-top:35px; padding-left:10px;" />
		</td>
		</tr>
		<br></br>
		</table>';
		$footer = '<div style="text-align:center;"><img src="' . DOMAIN_F . LOGO . $comp_rel["f_logo"] . '" style="width:8.27in;" /></div><div style="text-align:right;">{PAGENO}{nbpg}</div>';
		$trm_and_cond = "select * from tbl_terms_condition";
		$trandCondition = mysqli_fetch_assoc($dbcon->query($trm_and_cond));
		$approve_status = '';
		if ($rel['approve_status'] == '0') {
			$approve_status = ' (DRAFT)';
		}
		$inquiry_type = $rel['inquiry_type'];
		$companySettings = getCompanySettings($dbcon);
		$companyConfiguration = getCompanyConfiguration($dbcon);
		$sales_pro_search = explode(",", $companyConfiguration['sales_pro_search']);

		$quotation_print_content = $rel['quot_header'] ? $rel['quot_header'] : '';
		$quotation_print_content = str_ireplace(array("\r", "\n", '\r', '\n'), '', $quotation_print_content);
		$quotation_footer_content = $rel['quot_footer'] ? $rel['quot_footer'] : $quotation_footer_content;
		$quotation_footer_content = str_ireplace(array("\r", "\n", '\r', '\n'), '', $quotation_footer_content);
		$disc_qry = $dbcon->query("SELECT SUM(trn.product_discount_conv) as discount from tbl_quotation_trn as trn where trn.quot_trn_status=0 and trn.quotation_id=" . $rel['quotation_id']);
		$disc_qrys = brp_mysqli_fetch_assoc($disc_qry);
		if ($companyConfiguration['quot_revise_time_rate_with_discount'] == 0) {
			$colspan = ($disc_qrys['discount'] > 0) ? 7 : 6;
		} else {
			$colspan = 6;
		}
		$colspan = 7;
		//Amish Soni End 16-03-2021

		$html = '<html>
		<head>					
		<title>Quotation - ' . $rel['quotation_no'] . '</title>
		<style type="text/css">
		.page{
			width:8.27in;
			}
			.nextpage
			{
				page-break-after: always;
			}
			table{
				border-collapse:collapse;
				width:100%;
			}
			th, td {
				border: 1px solid black;
				text-align: center;
			}
		.terms{ 
		
            border-collapse: collapse;
			width:100%;

		}
            table, tr, td{
            border-collapse: collapse;
        }
		</style>
			</head>
			<body>
			<div style="border: solid #1034A6; width:100%; height:1200px; text-align:center;">
				<!--Show Logo in other pages-->
			
				<div style="text-align:center; ">' . $header . '</div><br>
				
				<!-- <htmlpagefooter name="otherpages_footer" style="display:none">
				<div style="text-align:center">' . $footer . '</div>
				</htmlpagefooter> -->
			<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
			<div style="border:None; width:900px;">
			<img width="900px" src="' . DOMAIN_F . '/upload/product_images/rocieopic.png"/> <br>
  </div>
	<br>
	<table style="font-size:16px;width: 100%;border:none;padding-left:10px; ">
        <br>  
        <tr style="border:none;padding-left:10px; ">
            <td colspan="2" style="text-align: left; border: none; padding-left:10px; "><span>Dear</span>: ' . $rel['c_con_fname'] . '' . $rel['c_con_lname'] . ' ,<br><br>
			' . $rel['quot_header'] . '
			<span>Name</span>: ' . $userData["user_name"] . '<br>
			<span >Subject  </b></span>: ' . $rel["quot_subject"] . '<br>
			' . $comp_rel['company_name'] . '
        	 </td>
        </tr>      
    </table><br><br></div>
	<center class="nextpage"></center>
	<div style="clear:both;"></div>';
	  
	// Your existing code
	  $html .= '<div style="border: solid #1034A6; width:100%; height:1200px;">
	  <div style="text-align:center;">' . $header . '</div><br> 
	  <center>
	  <table style="font-size:25px;border-collapse: collapse; width:100%; background-color: #0072A0; color:white;" cellpadding="5" cellspacing="5">
	  <tr>
	  <td style="text-align:center;"><b>ROCIEO INDUSTRIES PRIVATE LIMITED<br>QUOTATION</b></td>
	  </tr>
	  </table>
	  <table style="font-size:20px;border-collapse: collapse;width:100%; text-align:center;" cellpadding="5" cellspacing="5">
	  <tr><td><b>'.$rel['cust_name'].'</b></td></tr>
	  </table>
	 		 <table style="border:1px solid black;font-size: 14px;">
			<tr style="background-color: #0072A0;">
			<th style="border:1px solid;width: 5%; color: white;">Sr.</th>
			<th style="border:1px solid;width: 30%;color: white;">PRODUCT NAME 
			</th>
			<th style="border:1px solid;width: 10%; color: white;">RATE</th>
			<th style="border:1px solid;width: 5%;color: white;">QUANTITY</th>
			<th style="border:1px solid;width: 20%;color: white;">HSN CODE /GST %</th>
			<th style="border:1px solid;width: 15%;color: white;">NET
			RATE</th>
			<th style="border:1px solid;width: 15%; color: white;">AMOUNT</th>
			</tr>
			';

		if ($inquiry_type != "2") {
				$trn_qry = "SELECT 
								trn.*, 
								pro.product_name, 
								unit.unit_name, 
								pro.product_icode, 
								hsn.hsn_code,
								hsn.sale_gst,
								trn.igst_tax_rate_conv
							FROM 
								tbl_quotation_trn as trn 
							LEFT JOIN 
								product_mst as pro ON pro.product_id = trn.product_id
							LEFT JOIN 
								unit_mst as unit ON unit.unitid = trn.unitid
							LEFT JOIN 
								mst_hsn_code as hsn ON hsn.hsn_id = pro.product_hsn
							WHERE 
								trn.quot_trn_status = 0 
								AND trn.quotation_id = $quotation_id 
							GROUP BY 
								pro.product_id";
			} else {
				$trn_qry = "SELECT 
								trn.*, 
								pro.product_name, 
								pro.product_icode,
								trn.igst_tax_rate_conv
							FROM 
								tbl_quotation_project_trn as trn 
							LEFT JOIN 
								product_mst as pro ON pro.product_id = trn.product_id 
							WHERE 
								trn.quot_trn_status = 0 
								AND trn.quotation_id = $quotation_id 
							GROUP BY 
								pro.product_id";
			}
			
		$trn_qry_rs = $dbcon->query($trn_qry);
		$p = 1;
		$ttl_amt = 0;
		$ttl_qty = 0;
		$cnt = mysqli_num_rows($trn_qry_rs);

		while ($rel = mysqli_fetch_assoc($trn_qry_rs)) {

			$gst_per = $rel['cgst_tax_per']+$rel['sgst_tax_per']+$rel['igst_tax_per'];
			$gst_rate = $rel['cgst_tax_rate_conv']+$rel['sgst_tax_rate_conv']+$rel['igst_tax_rate_conv'];
			//echo $rel['product_rate'] . " : " . $gst_rate . " : " . 100 . " : " . $rel['product_rate'];
			// die;
			$net_rate = (($rel['product_rate'] * $rel['sale_gst']) / 100) + $rel['product_rate']; 
			$amt = $net_rate * $rel['product_qty'] ;
			$ttl_amt = $ttl_amt + $amt;
			$ttl_qty = $ttl_qty + $rel['product_qty'];
			
			$html .= ' 
				<tr>
		<td style="border:1px solid black;text-align:center;"><b>' . $p . '</b></td>
		<td style="border:1px solid black;text-align:center;"><b>' . $rel['product_name'] . '</b></strong><br/>' . ' </td>
		<td style="border:1px solid black;text-align:center;"><b> ' . $rel['product_rate'] . '</b></td>
		<td style="border:1px solid black;text-align:center;"><b>' . $rel['product_qty'] . '</b></td>
		<td style="border:1px solid black;text-align:center;"><b>' . $rel['hsn_code'] . '/' . $rel['sale_gst'] . ' %</b></td>
		<td style="border:1px solid black;text-align:center;"><b>' . indian_number($net_rate, 2) . '</b></td>
		<td style="border:1px solid black;text-align:center;"><b>' . indian_number($amt, 2) . '</b></td>
			</tr><br>';
		$p++;
	}

// echo $trn_qry; die();
		$pr = 12 - $cnt;

		$html .= ' </table></center><br><br> ';

		 $html .= '<table style="font-size:20px;border-collapse: collapse;width:100%; text-align:center;" cellpadding="5" cellspacing="5">
		<tr style="border:1px solid black;">
		<th>Tax Amount </th>
		</tr>
		</table>
		<table style="border:1px solid black;font-size: 14px;">
			<tr style="background-color: #0072A0;">
		<th>Sr.</th>
		<th>HSN GST</th>
		<th>GST</th>
		<th>Total Tax</th>
		</tr>';

		if ($inquiry_type != "2") {
				$trn_qry = "SELECT 
								trn.*, 
								pro.product_name, 
								unit.unit_name, 
								pro.product_icode, 
								hsn.hsn_code,
								hsn.sale_gst,
								trn.igst_tax_rate_conv
							FROM 
								tbl_quotation_trn as trn 
							LEFT JOIN 
								product_mst as pro ON pro.product_id = trn.product_id
							LEFT JOIN 
								unit_mst as unit ON unit.unitid = trn.unitid
							LEFT JOIN 
								mst_hsn_code as hsn ON hsn.hsn_id = pro.product_hsn
							WHERE 
								trn.quot_trn_status = 0 
								AND trn.quotation_id = $quotation_id 
							GROUP BY 
								pro.product_id";
			} else {
				$trn_qry = "SELECT 
								trn.*, 
								pro.product_name, 
								pro.product_icode,
								trn.igst_tax_rate_conv
							FROM 
								tbl_quotation_project_trn as trn 
							LEFT JOIN 
								product_mst as pro ON pro.product_id = trn.product_id 
							WHERE 
								trn.quot_trn_status = 0 
								AND trn.quotation_id = $quotation_id 
							GROUP BY 
								pro.product_id";
			} 
			$trn_qry_rs = $dbcon->query($trn_qry);
		$p = 1;
		$ttl_amt_new = 0;
		$ttl_amt = 0;
		$ttl_qty = 0;
		$cnt = mysqli_num_rows($trn_qry_rs);

		while ($rel = mysqli_fetch_assoc($trn_qry_rs)) {

			$gst_per = $rel['cgst_tax_per']+$rel['sgst_tax_per']+$rel['igst_tax_per'];
			$net_rate = (($rel['product_rate'] * $gst_per) / 100) + $rel['product_rate']; 
			$amt = $net_rate * $rel['product_qty'] ;
			$ttl_amt = $ttl_amt + $amt;
			$ttl_qty = $ttl_qty + $rel['product_qty'];
			$gst_conv = $net_rate - $rel['product_rate'];
			$ttl_tax = $gst_conv * $rel['product_qty'];
			$ttl_amt_new = $ttl_amt_new + $ttl_tax;


		
		$html .= '<tr>
		<td style="border:1px solid black;text-align:center; color: white;"><b>' . $p . '</b></td>
		<td style="border:1px solid black;text-align:center; color: white;"><b>' . $rel['hsn_code'] . '/' . $rel['sale_gst'] . ' %</b></td>
		<td style="border:1px solid black;text-align:center; color: white;"><b>'.$gst_conv.'</b></td>
		<td style="border:1px solid black;text-align:center; color: white;"><b>'.$ttl_tax.'</b></td>
		</tr>
		';
	$p++;
	}	

	$html .= '
			 <tr>
		<td colspan="3" style="text-align:right; font-size:14px; padding-right:10px;"><b>TOTAL TAX AMOUNT</b><td>
		<b>'.indian_number($ttl_amt_new,2).'</b>
		</tr>	
			<tr>
		<td colspan="3" style="text-align:right; font-size:20px; padding-right:10px;"><b>GRAND TOTAL</b><td>
		<b>'.indian_number($ttl_amt,2).'</b>
		</tr>
		</table> ';

	 $terms_qry = "SELECT 
qtrm.*, 
mst.tc_name 
FROM 
tbl_quotation_terms_trn AS qtrm 
LEFT JOIN 
tbl_terms_condition AS mst ON mst.tc_id = qtrm.tc_id
WHERE 
qtrm.quotation_terms_trn_status = 0 
AND qtrm.quotation_id = $quotation_id 
ORDER BY 
qtrm.tc_priority";


		$terms_qry_rs = $dbcon->query($terms_qry);
		// echo $terms_qry; die;
		if (mysqli_num_rows($terms_qry_rs)) {
			$html .= '<h3 style="text-align:left;padding:5px;"><u>Terms and Conditions</u></h3>
				 <div><table width="100%" style="font-size:14px;border: none;width:100%;overflow:wrap;"><tbody>';
			$t = 1;
			// echo $terms_qry_rs; die;
			while ($term_rel = mysqli_fetch_assoc($terms_qry_rs)) {
				$string = (nl2br($term_rel['tc_details']));

				$html .= '<tr style="border:none;">
					 <td width="25%" style="width:25%;text-align:left;padding:5px;border:none; vertical-align: top; font-weight: bold;padding-left:10px; ">' . $t . '. ' . $term_rel['tc_name'] . '</td>
					 <td width="75%" style="width:70%;text-align:left;padding:5px;border:none;padding-left:10px; ">: ' . $string . '</td>
					 </tr>';
				$t++;
			}
			$html .= '</tbody></table></div>';
		}

					 
// echo $html;die;

		$html .= '
		<div style="text-align:top-left;font-size:12px; padding-left:10px; ">
		<b>' . $comp_rel['company_name'] . '</b><br>
		' . $comp_rel["address"] . '<br>
		GST: ' . $comp_rel["vatno"] . '<br>
		Mobile: ' . $userPhone . '<br>
		Email : ' . $comp_rel["website"] . '<br><br>
		<span style="font-size:14px;"><u><b>Account Details:</b></u></span><br>
		<b>A/C Number:</b> ' . $comp_rel["ac_no"] . ' <br>
		<b>IFSC Code: </b> ' . $comp_rel["ifcs"] . ' <br>
		<b>Bank Name: </b> ' . $comp_rel["bank_name"] . ' <br>
		<b>Branch Name:</b> ' . $comp_rel["branch_name"] . ' <br>
		</div>';

		/* Get Bom of product end */
		// $html .= '</div></div>
		// 			<!--page1 end-->';


		$html .= '<sethtmlpagefooter name="otherpages_footer" value="on" />
			</div></body>
			</html>';
        // $html.= '</table>';

		ob_end_clean();
		$file_name = $rel['quotation_no'] . '.pdf';
		$file_name = str_ireplace("/", "_", $file_name);
		if ($save_file == "No") {
			include ("../../view/export/mpdf/mpdf.php");
		} else {
			include ("../../../view/export/mpdf/mpdf.php");
		}
		$mpdf = new mPDF('', 'A4', '0', 'Calibri', '10', '10', '5', '5	', '1', '1');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader();
		$mpdf->pagenumPrefix = ' ';
		$mpdf->pagenumSuffix = ' / ';
		$mpdf->nbpgPrefix = ' ';
		$mpdf->nbpgSuffix = ' pages';
		//	$mpdf->SetFooter('{PAGENO}{nbpg}');
		if ($rel['approve_status'] == '0') {
			$mpdf->SetWatermarkText('DRAFT');
		} else {
			$mpdf->SetWatermarkText();
		}
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion = true;
		$mpdf->charset_in = 'UTF-8';
		$mpdf->WriteHTML($html);
		if ($save_file == "No") {
			$mpdf->Output();
		} else {
			$mpdf->Output('../../../view/upload/mail_attach/' . $file_name, 'f');
		}
		ob_clean();
		return $file_name;
	}
}