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
		$header = '<table style="border:0; border-color:white">
		<tr style="border:0;">
	<td colspan="2" style="width:40%; text-align:right;border:0;"> 
		<img src="../../view/img/logo/' . $comp_rel["logo"] . '" style="height: 100px; width: 300px; height:60px;" />
		</td>
		</tr>
		<tr style="border:0;">
		<td style="width:65%;border:0;text-align:right;">
		</td>
		<td style="width:35%;border:0;text-align:right;font-size:13px"> 
			' . $comp_rel["address"] . '<br><br>
	
			Email : ' . $comp_rel["website"] . '<br>
			Web : ' . $comp_rel["company_website"] . '<br>
		<span style="font-size:15px">	<b>GST: ' . $comp_rel["vatno"] . '</b></span>
		</td>
		</tr>
		</table>
	';
// 	echo $header;die;
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
			tr,td{
			border:1px solid black !important;
			}

		.terms{ 
		
            border-collapse: collapse;

			width:100%;

		}
            table, tr, td{
            border-collapse: collapse;
        }
			
			.blueHeading {
				color: #365f91;
			}

			</style>
			</head>
			<body>
				<!--Show Logo in other pages-->
			<htmlpageheader name="otherpages" style="display:none">
				<div style="text-align:center">' . $header . '</div><br>
				</htmlpageheader><br>
				<!-- <htmlpagefooter name="otherpages_footer" style="display:none">
				<div style="text-align:center">' . $footer . '</div>
				</htmlpagefooter> -->
				<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
            

      <br><br>
    <table style="font-size:16px;width: 100%;border: none;">
        <tr style="border:none;">
            <th style="text-align: left;border: none;"><u>BY EMAIL</u><br>' . date("d-M-Y", strtotime($rel['quotation_date'])) .'<br> '. $rel['quotation_no'] . ' </th>
        </tr>
        <br>
        <tr style="border:none;">
		<td style="text-align: left;border: none;"> <br><br><b>To,<br>' . $rel['cust_name'] . '</b><br>' . $rel['quot_address'] . '<br><br><b>Email:- ' . $rel['cust_email'] . '</b></td>
				<br>
        </tr >   
        <tr style="border:none;">
            <td colspan="2" style="text-align: left; border: none;"><b><span><br>Kind Attn. </span>:	' . $rel['c_con_fname'] . '' . $rel['c_con_lname'] .'<span style="margin-left:25px"> - ' . $rel['c_con_mobile'] . '</span> <br>
			<span ><br>Subject  </span>: Quotation for &nbsp;' . $rel["quot_subject"] . '
                </b><br>
        	 </td>
			
        </tr>   
        <tr style="border:none;">
            <td colspan="2" style="text-align: left; border: none; "><br>' . $rel['quot_header'] . '
                </td>           
        </tr>   
    </table><br><br>
	<table style="font-size:16px;width: 100%;border: none;">
	<tr style="border:none;">
	<th style="text-align:left;">
	<span style="color: red;">1.Technical Specification<br>
	<span style="color: red;">2.Commercial Offer
	</th>
	</tr></table>  <center class="nextpage"></center>
	<div style="clear:both;"></div>';

	$trn_qry = "SELECT 
    trn.*,
    pro.product_spec as specification,
    pro.product_desc as descripsion,
    pro.image_name,
    pro.product_name
FROM 
    tbl_quotation_trn AS trn
LEFT JOIN 
    product_mst AS pro ON pro.product_id = trn.product_id
	WHERE 
                trn.quot_trn_status = 0 
                AND trn.quotation_id = $quotation_id
            GROUP BY 
                pro.product_id";

$trn_qry_rs = $dbcon->query($trn_qry);

// echo $trn_qry;die();
while ($rel = $trn_qry_rs->fetch_assoc()) {
$html .= '<br>
  ' . $rel['descripsion'] . ' <br><br>' . $rel['specification'] . '<br>';

if (!empty($rel['image_name'])) {
$html .= '
	   <div style="text-align: center; align-items: center;">
		   <img width="448" height="355" src="' . DOMAIN_F . '/upload/product_images/' . $rel["image_name"] . '"/> <br>
	   </div>';
}

}
	  // Your existing code
	  $html .= '<br><center><table style="border:1px solid black;font-size: 18px;">
			<tr >
			<th style="border:1px solid;width: 15%;">Sr.</th>
			<th style="border:1px solid;width: 60%;">Item Description
			</th>
			<th style="border:1px solid;width: 10%;">Qty</th>
			<th style="border:1px solid;width: 15%;">Unit</th>
			<th style="border:1px solid;width: 15%;">Rate (Each)</th>
			<th style="border:1px solid;width: 15%;">Amount</th>
			</tr>
			';

		if ($inquiry_type != "2") {
			$trn_qry = "SELECT 
                trn.*, 
                pro.product_name, 
                unit.unit_name, 
                pro.product_icode, 
                hsn.hsn_code 
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
			$trn_qry = "SELECT trn.* , pro.product_name,pro.product_icode FROM `tbl_quotation_project_trn` as trn 
					 left join product_mst as pro on pro.product_id = trn.product_id 
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

			$amt = $rel['product_qty'] * $rel['product_rate'];
			$ttl_amt = $ttl_amt + $amt;
			$ttl_qty = $ttl_qty + $rel['product_qty'];
			$html .= ' 
				<tr>
		<td style="border:1px solid black;text-align:center;"><b>' . $p . '</b></td>
		<td style="border:1px solid black;text-align:center;"><b>' . $rel['product_name'] . '</b></strong><br/>' . ' </td>
		<td style="border:1px solid black;text-align:center;"><b>' . $rel['product_qty'] . '</b></td>
		<td style="border:1px solid black;text-align:center;"><b>' . $rel['unit_name'] . '</b></td>
		<td style="border:1px solid black;text-align:center;"><b>Rs. ' . $rel['product_rate'] . '</b></td>
		<td style="border:1px solid black;text-align:center;"><b>Rs. ' . $rel['product_amount'] . '</b> </td>
			</tr><br>
                ';
			$p++;
		}
// echo $trn_qry; die();
		$pr = 12 - $cnt;

		$html .= ' <tr>
		<td colspan="5" style="text-align:center;"><b>Total Amount</b><td>
		<b>Rs.' . number_format($ttl_amt, 2, ".", "") . '</b>
		</tr>
		<tr>
		<td colspan="7" style="text-align:center;"><b>Technical Specification as per PARA - 1
		</b></td>
		</tr>
		</table></center><br><br>
			 
			 ';
	  

//   echo $trn_qry;die();

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
		if (mysqli_num_rows($terms_qry_rs)) {
			$html .= '<h3 style="text-align:left;"><u>Terms and Conditions</u></h3>
				 <div><table width="100%" style="font-size:14px;border: none;width:100%;overflow:wrap;"><tbody>';
			$t = 1;
			while ($term_rel = mysqli_fetch_assoc($terms_qry_rs)) {
				$string = (nl2br($term_rel['tc_details']));

				$html .= '<tr style="border:none;">
					 <td width="25%" style="width:25%;text-align:left;padding:5px;border:none; vertical-align: top; font-weight: bold;">' . $t . '. ' . $term_rel['tc_name'] . '</td>
					 <td width="75%" style="width:70%;text-align:left;padding:5px;border:none;">: ' . $string . '</td>
					 </tr>';
				$t++;
			}
			$html .= '</tbody></table></div>';
		}

		$html .= '</table>';
		
			$html .= '<div style="text-align:top-left;font-size:13px; ">
		<div>'.$quotation_footer_content.'</div><br><br>
		<span style="font-size:16px">Yours truly,</span>
		<h3> <b>For, 
						<br>' . $comp_rel['company_name'] . '<br>
						' . $userData['user_name'] . ' <br>
						' . $userPhone . '</b>
						</h3>
					</div>';

		/* Get Bom of product end */
		$html .= '</div>
					<!--page1 end-->';





		$html .= '<sethtmlpagefooter name="otherpages_footer" value="on" />
			</body>
			</html>';

		ob_end_clean();
		$file_name = $rel['quotation_no'] . '.pdf';
		$file_name = str_ireplace("/", "_", $file_name);
		if ($save_file == "No") {
			include ("../../view/export/mpdf/mpdf.php");
		} else {
			include ("../../../view/export/mpdf/mpdf.php");
		}
		$mpdf = new mPDF('', 'A4', '0', 'Calibri', '10', '10', '50', '5	', '1', '1');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
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