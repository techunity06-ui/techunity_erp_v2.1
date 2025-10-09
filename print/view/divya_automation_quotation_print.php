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
	$userPhone = $userData['user_phone'] ? 'Mo.: ' . $userData['user_phone'] : '';
	$userEmail = $userData['user_mail'] ? ' - Email: ' . $userData['user_mail'] : '';

	// $quotation_id = $_REQUEST['id'];	
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
		// echo "<pre>";
		// print_r($rel);
		// echo "</pre>";
		// exit;

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
		$header = '<table style="border:none;">
        <tr style="border:none;">
        <td style="text-align:left;padding-top:30px;border:none;vertical-align:top;"><img src="' . DOMAIN_F . LOGO . $comp_rel["logo"] . '" style="width:80px;" /></td>
            <td style="text-align:right;padding-top:30px;border:none; vertical-align:top;">Proposal: ' . $rel['quotation_no'] . '/Ref - ' . $rel['inquiry_no'] . '<br>Date: ' . date("d-m-Y", strtotime($rel['quotation_date'])) . ' </td>
        </tr>
        </table>
     
        ';
		$footer = '
        <div> ' . $comp_rel['address'] . ' | Contact :' . $comp_rel['contact_no'] . '</div><div style="text-align:right;">{PAGENO}{nbpg}</div>';


		$inquiry_type = $rel['inquiry_type'];
		//Amish Soni Start 16-03-2021
		$companySettings = getCompanySettings($dbcon);
		$companyConfiguration = getCompanyConfiguration($dbcon);
		$sales_pro_search = explode(",", $companyConfiguration['sales_pro_search']);

		//	if($companySettings) {
		$quotation_print_content = $rel['quot_header'] ? $rel['quot_header'] : '';
		$quotation_print_content = str_ireplace(array("\r", "\n", '\r', '\n'), '', $quotation_print_content);
		$quotation_footer_content = $rel['quot_footer'] ? $rel['quot_footer'] : $quotation_footer_content;
		$quotation_footer_content = str_ireplace(array("\r", "\n", '\r', '\n'), '', $quotation_footer_content);
		//	}
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
				height:10.69in;
				
			}
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
				<div style="text-align:center">' . $header . '</div>
				</htmlpageheader>
				 <htmlpagefooter name="otherpages_footer" style="display:none">
				<div style="text-align:center">' . $footer . '</div>
				</htmlpagefooter>
				<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>';



		$html .= ' 
		
	  

	  
	  <table style="font-size:16px;width: 100%;border: none;">
	  <tr style="border:none;">
		<td colspan="2" style="text-align: left;border: none; ">To,<br><strong>M/s. ' . $rel['cust_name'] . '</strong> </td>
	  </tr >  
   

	  <tr style = "border: none;"> 
		<td width = "38%"> ' . $rel['quot_address'] . ' </td>  <br> <br>  
	   </tr>
	  <tr style="border:none;">
		 <td colspan="2" style="text-align: left; border: none;"><b>Kind Attn </b>:	' . $rel['c_con_fname'] . $rel['c_con_lname'] . ' <br><b>Mobile :</b>	' . $rel['c_con_mobile'] . '  <b> Email :</b>	' . $rel['c_con_email'] . ' <br> <br>
			<span ><b>Subject :</b></span> <b>' . $rel["quot_subject"] . ' </b><br> <br>'
			. $rel["quot_header"] . '
			 <br>
		   <br>
		  
		   <b>Requirement: M/s. ' . $rel['cust_name'] . ' </b> is part of and looking for Control Panel
		   for their systems.
		   <br>
		   <br>
		   <b>Our offer consist of following</b> 
		   <br>
		   <ul>
		   <li> <b>Annexure \'A\' – Bills of Materials </b></li>
		   <li> <b>Annexure \'B\' – Quotation</b></li>
		   <li> <b>Annexure \'C\' – Commercial Terms & Conditions</b></li>
		   </ul>
		   <br>
		   <br>


		   <div> We hope our offer meets with your approval and look forward to receiving your valued order. We assure
		   you of high quality product, excellent services and timely delivery.</div>
		   <span style="display:block" >Please feel free to contact us in case of any query </span>
		   <br>
		   <br>
		   <span style="display:block" >Thanking you and assuring you of our best attention at all times.</span>
		   <br>
		   <br>
		   <br>
		  </td>
		</tr>   
	</table> 
';






		if ($inquiry_type != "2") {
			$trn_qry = "SELECT trn.*,pro.product_name,unit.unit_name, pro.product_icode,hsn.hsn_code FROM tbl_quotation_trn as trn 
						left join product_mst as pro on pro.product_id=trn.product_id
						left join unit_mst as unit on unit.unitid=trn.unitid
						left join mst_hsn_code as hsn on hsn.hsn_id=pro.product_hsn
						where trn.quot_trn_status=0 and trn.quotation_id=" . $rel['quotation_id'];
		} else {
			$trn_qry = "SELECT trn.* , pro.product_name,pro.product_icode FROM `tbl_quotation_project_trn` as trn 
							left join product_mst as pro on pro.product_id = trn.product_id 
						where trn.quotation_projecttrn_status=0 and trn.quotation_id =" . $rel['quotation_id'];
		}
		$trn_qry_rs = $dbcon->query($trn_qry);
		$p = 1;
		$ttl_amt = 0;
		$ttl_qty = 0;
		$charges_qty = 0;
		$total_gst = 0;
		$total_i_gst = 0;


		$cnt = mysqli_num_rows($trn_qry_rs);



		$pr = 12 - $cnt;




		$que = "select * from tbl_tax as ta where tax_status=0 and tax_id in (" . $rel['tax_id'] . ") order by tax_id";
		$rs_di = $dbcon->query($que);


		$html .= ' 
		<div style="text-align:left">
						<b>For, ' . $comp_rel['company_name'] . ' </b> <br> <br>
						<img src="' . ROOT . SIGNATURE . $comp_rel['authorized_signature'] . '" width="100px" > <br> <br>
						' . $userPhone . ' <span style="display: block;color:blue;border-left:1px solid ; padding-right:10px !important;">&nbsp; <span style="text-decoration:underline; border-left:none ;">' . $comp_rel['website'] . '  </span></span> <br>
	
						</div> <br> ';


		$html .= ' <div class="nextpage"></div> ';







		if ($rel['with_bom_flag'] == '1') {
			/* Get Bom of Product Start */
			$product_qry = "select trn.product_qty, trn.product_amount, pro.product_id, pro.product_name, pro.product_type 
						from tbl_quotation_trn as trn 
						left join product_mst as pro on pro.product_id=trn.product_id
						left join unit_mst as unit on unit.unitid=trn.unitid
						where trn.quot_trn_status=0 and trn.quotation_id=" . $quotation_id;
			$result = mysqli_query($dbcon, $product_qry);
			$products = mysqli_fetch_all($result, MYSQLI_ASSOC);
			$html .= " <div  style='text-align:center;border:none;font-size:16px;'>
			     <b> Annexure'A' <br> 
				 Bills of Materials </b> <br> </div>
					";


			$html .= '
			<div style="max-height:500px">

						
						<table style="font-size:14px !important;border-collapse: collapse;border:1px solid;width:100%;" cellpadding="3" cellspacing="3">
						<thead>	
						<tr>
						<th colspan="6" style="background: black; color:#FFFFFF;text-align:center;border:1px solid;"><h3>BILL OF MATERIAL FOR CONTROL PANEL </h3></th>
						</tr>	
						<tr>
						<th style="border:1px solid;width:5%;text-align:center;">Sr. No.</th>
						<th style="border:1px solid;width:45%;text-align:center;">Description</th>
						<th style="border:1px solid;width:5%;text-align:center;">Make by</th>
						<th style="border:1px solid;width:10%;text-align:center;">Part no.</th>
						<th style="border:1px solid;width:10%;text-align:center;">Total Qty</th>
						<th style="border:1px solid;width:5%;text-align:center;">Units</th>
						</tr>
						</thead>
						<tbody>';
			$ij = 1;
			foreach ($products as $product) {

				$query = "select bom.*,product.product_name,product.make_by,product.part_number,bunit.unit_name as base_unit_name,bunit.unit_name as conv_unit_name, bom_version.is_default_bom,product.product_type from tbl_bom as bom
							left join product_mst as product on product.product_id=bom.bom_product
							left join pro_ms_bom_version as bom_version on bom_version.bom_version_id=bom.bom_version_id
							left join unit_mst as bunit on bunit.unitid=bom.product_base_unit
							left join unit_mst as cunit on cunit.unitid=bom.product_conv_unit
							where bom.bom_product = " . $product['product_id'];


				$bom_qry_rs = $dbcon->query($query);
				
				if (mysqli_num_rows($bom_qry_rs)) {

					$t = 1;
					while ($bom_rel = mysqli_fetch_assoc($bom_qry_rs)) {


						if ($bom_rel['is_default_bom'] == 1) {

							$html .= '<tr style="background: #CAC3F6;">
										<td style="border:1px solid;font-weight: bold;text-align:center;">' . $ij . '</td>
										<td style="border:1px solid;font-weight: bold;text-align:left;">' . $bom_rel['product_name'] . '</td>
										<td style="border:1px solid;font-weight: bold;text-align:center;">' . $bom_rel['make_by'] . '</td>
										<td style="border:1px solid;font-weight: bold;text-align:center;">' . $bom_rel['part_number'] . '</td>
										<td style="border:1px solid;font-weight: bold; text-align: center;">' . $bom_rel['base_unit_name'] . '</td>
										<td style="border:1px solid;font-weight: bold; text-align: center;">' . $bom_rel['product_base_qty'] . '</td>
										</tr>';
							$html .= quotation_print_with_bom($dbcon, $bom_rel['bom_id'], $bom_rel['product_base_qty'], $ij, 0, 0);
							$t++;
						}
					}

				}
				$ij++;
			}
			$html .= '</tbody>
						</table>
						</div>';

			if ($rel['inquiry_type'] != '1') {
				$html .= '

						<div>
						<br>
						<table style="font-size:16px;border-collapse: collapse;height: 500px !important; ;width:100%;" cellpadding="3" cellspacing="3">
						<thead>
						<tr>
						<th colspan="5" style="text-align:center;border:1px solid;"><h3 style="color:red;text-decoration:underline;">BILL OF MATERIAL FOR CONTROL PANEL </h3></th>
						</tr>
						<tr> 
						<th style="background-color: blue!important; border:1px solid;width:5%;text-align:center;">Sr.<br/>No.</th>
						<th style="border:1px solid;width:50%;text-align:center;">Description</th>
						<th style="border:1px solid;width:15%;text-align:center;">HSN Code</th>
						<th style="border:1px solid;width:15%;text-align:center;">Qty</th>
						<th style="border:1px solid;width:15%;text-align:center;">Unit</th>
						</tr>
						</thead>
						<tbody>';
				$product_qry = "select trn.*, pro.product_name,pro.product_base_unit, unit.unit_name,hsn.hsn_code from tbl_quotation_project_trn as trn
						left join product_mst as pro on pro.product_id=trn.product_id
						left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
						left join unit_mst as unit on unit.unitid=pro.product_base_unit
						where trn.quotation_projecttrn_status=0 and trn.quotation_id=" . $quotation_id;
				$result = mysqli_query($dbcon, $product_qry);
				$rt = 1;
				while ($row = mysqli_fetch_assoc($result)) {

					$html .= '<tr>
							<td style="border:1px solid;text-align:center;vertical-align:top;">' . $rt . '</td>
							<td style="border:1px solid;vertical-align:top;">' . $row['product_name'] . '</td>
							<td style="border:1px solid;text-align:center;vertical-align:top;">' . $row['hsn_code'] . '</td>
							<td style="border:1px solid;text-align:center;vertical-align:top;">' . $row['product_qty'] . '</td>
							<td style="border:1px solid;text-align:center;vertical-align:top;">' . $row['unit_name'] . '</td>
							</tr>';
					$rt++;
				}
				$html .= '</tbody>
						</table>
						</div> ';

			}

			$html .= ' <div class="nextpage"></div> ';

			$html .= '
			<div style="text-align:center;border:none;font-size:16px;">
			
			<b>Annexure \'B\' <br>Quotation </b>
			</div> <br>
			';


			$html .= '
			<div>We acknowledge with thanks receipt of your enquiry for the above subject. We are pleased to submit
			our most competitive offer for the same as per following Annexure: -
			 </div>
			 <br>';

			$html .= '

						<div>
						<br>
						<table style="font-size:16px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
						<thead>
						
						<tr style="background:#EEF3F3;"> 
						<th style="border:1px solid;width:5%;text-align:center;">Sr.No.</th>
						<th style="border:1px solid;width:40%;text-align:center;">Item Description</th>
						<th style="border:1px solid;width:15%;text-align:center;">Qty</th>
						<th style="border:1px solid;width:20%;text-align:center;">Unit Price <br> (RS.)</th>
						<th style="border:1px solid;width:20%;text-align:center;">Total Cost <br> (RS.)</th>
						</tr>
						</thead>
						<tbody>';
						
			
						$rt = 1;
						$rt_qty = 0;
						$rt_total = 0;
						$rt_sub_total = 0;
						foreach ($products as $product) {

							$html .= '<tr>
							<td style="border:1px solid;text-align:center;vertical-align:top;">' . $rt . '</td>
							<td style="border:1px solid;vertical-align:top;">' . $product['product_name'] . '</td>
							<td style="border:1px solid;text-align:right;padding-right:10px;vertical-align:top;">' . $product['product_qty'] . '</td>
							<td style="border:1px solid;text-align:right;padding-right:10px;vertical-align:top;">' . $product['product_amount'] . '</td>
							<td style="border:1px solid;text-align:right;padding-right:10px;vertical-align:top;">' . $product['product_qty'] * $product['product_amount'] . '</td>
							</tr>';
			
							$rt++;
							$rt_qty += $product['product_qty'];
							$rt_total += $product['product_amount'];
							$rt_sub_total += ($product['product_qty'] * $product['product_amount']);
						}

						$rel['g_total'] = $rt_sub_total;

						$html .= '<thead>
						
						<tr> 
						<th style="border:1px solid;width:5%;text-align:center;"></th>
						<th style="border:1px solid;width:40%;text-align:center;">Total Quantity</th>
						<th style="border:1px solid;width:15%;text-align:right;background:yellow;padding-right:10px">'.$rt_qty.'</th>
						<th style="border:1px solid;width:20%;text-align:center;">Total Cost (INR) </th>
						<th style="border:1px solid;width:20%;text-align:right;background:yellow;padding-right:10px">'.$rt_sub_total.'</th>
						</tr>
						</thead>';

		
						$html .= '</tbody>
						</table>
						</div> ';


			
			$html .= '<table>
			<tr style="border:none;">
			<td colspan="5" style="text-align:left; padding-left:8px;"><b>Amount in words : 
			' . (($comp_rel['currency_id'] == $rel['currency_id']) ? ucfirst(convert_number_to_words_new($rel['g_total'], $currency_word_start, $currency_word_end)) : ucfirst(convert_number_to_words($rel['g_total'], $currency_word_start, $currency_word_end))) . '</b></td>
			
			</tr>
			
			</table>';


		}
		
		$terms_qry = "select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
		left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
		where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=" . $rel['quotation_id'] . " order by qtrm.tc_priority";
		$terms_qry_rs = $dbcon->query($terms_qry);
		if (mysqli_num_rows($terms_qry_rs)) {

		    $html .= "<div  style='text-align:center;border:none;font-size:16px;'>
       <br> <b>  Annexure 'C' <br>
		Commercial Terms And Conditions</b> </div>";




		
			$html .= '<br>
			<div><table width="100%" style="font-size:16px;border: none;width:100%;overflow:wrap;"><tbody>';
			$t = 1;
			while ($term_rel = mysqli_fetch_assoc($terms_qry_rs)) {
				$string = (nl2br($term_rel['tc_details']));

				$html .= '<tr style="border:none;">
				<td width="20%" style="text-align:left;padding:10px;border:none; vertical-align: top; font-weight: bold;">' . $t . '. ' . $term_rel['tc_name'] . '</td>
				<td width="75%" style="width:70%;text-align:left;padding:10px;border:none;">: ' . $string . '</td>
				</tr>';
				$t++;
			}
			$html .= '</tbody></table></div> ';
		}
		/* Get Bom of product end */
		$html .= '</div> ';
		$html .= '<sethtmlpagefooter name="otherpages_footer" value="on" />
			</body>
			</html> ';
		//echo $trn_qry;
		// echo $html; die;	
		ob_end_clean();
		$file_name = $rel['quotation_no'] . '.pdf';
		$file_name = str_ireplace("/", "_", $file_name);
		if ($save_file == "No") {
			include ("../../view/export/mpdf/mpdf.php");
		} else {
			include ("../../../view/export/mpdf/mpdf.php");
		}
		$mpdf = new mPDF('', 'A4', '0', 'Calibri', '10', '10', '40', '18', '1', '1');
		//		$mdf->SetFont('ProximaNova');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		//Show page number : Dimple Panchal (05-Apr-2021)
		$mpdf->pagenumPrefix = ' ';
		$mpdf->pagenumSuffix = ' / ';
		$mpdf->nbpgPrefix = ' ';
		$mpdf->nbpgSuffix = ' pages';
		//	$mpdf->SetFooter('{PAGENO}{nbpg}');
		$mpdf->SetWatermarkText();
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