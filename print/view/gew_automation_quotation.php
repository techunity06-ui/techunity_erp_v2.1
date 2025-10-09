<?php
$quotation_id = $_REQUEST['id'];
if (!empty($quotation_id)) {
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
	include("../../include/function_database_query.php");
	include_once(COMMON_FUNCTION_PATH . "common_functions.php");
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
		$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';


		$set = "select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=" . $rel['company_id'];

		$comp_rel = mysqli_fetch_assoc($dbcon->query($set));
		$header = '<div style="text-align:center;padding-top:30px"><img src="' . DOMAIN_F . LOGO . $comp_rel["logo"] . '" style="width:8.27in;" /></div>';
		$footer = '<div style="text-align:center;"><img src="' . DOMAIN_F . LOGO . $comp_rel["f_logo"] . '" style="width:8.27in;" /></div><div style="text-align:right;">{PAGENO}{nbpg}</div>';
		$trm_and_cond = "select * from tbl_terms_condition";
		$trandCondition = mysqli_fetch_assoc($dbcon->query($trm_and_cond));
		$approve_status = '';
		if ($rel['approve_status'] == '0') {
			$approve_status = ' (DRAFT)';
		}
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
				<div style="text-align:center">' . $header . '</div>
				</htmlpageheader>
				<!-- <htmlpagefooter name="otherpages_footer" style="display:none">
				<div style="text-align:center">' . $footer . '</div>
				</htmlpagefooter> -->
				<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
            

      <br>
    <table style="font-size:16px;width: 100%;border: none;">
        <tr style="border:none;">
            <th style="text-align: left;border: none;">Ref. No.: ' . $rel['inquiry_no'] . ' </th>
            <th style="text-align: right;border: none;">Date: ' .  date("d-m-Y",strtotime($rel['inquiry_date'])). ' </th>
        </tr>
        <br>

        <tr style="border:none;">
            <td colspan="2" style="text-align: left;border: none; "><h2>' . $comp_rel['company_name'] . '</h2></td>
           
				<br>

        </tr >   
        <tr style="border:none;">
            <td colspan="2" style="text-align: left; border: none;"><span style="text-decoration:underline;"><b>Kind Attention </b></span>:	' . $rel['c_con_fname'] . '' . $rel['c_con_lname'] . ' <br><br>
			<span style="text-decoration:underline;"><b>Subject  </b></span>: Quotation for &nbsp;' . $rel["quot_subject"] . '
                <br><br>
                <span style="text-decoration:underline;"><b>Reference </b></span> : As per your discussion with &nbsp;' . $rel['quotation_ref'] . ' </td>
			
        </tr>   
        <tr style="border:none;">
            <td colspan="2" style="text-align: left; border: none; "><br>' . $rel["quot_header"] . '<br>
                </td>
           
        </tr>   
    </table><br><br>
    ';
		// echo $html;die;
		$html .= '<table cellpadding="5" cellspacing="5" style="border:1px solid black;font-size: 16px;">
			<tr >
			<th style="border:1px solid;width: 15%;">Sr. No.</th>
			<th style="border:1px solid;width: 60%;">Description </th>
			<th style="border:1px solid;width: 10%;">Qty</th>
			<th style="border:1px solid;width: 15%;">Unit Price</th>
			</tr>
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

		while ($trn_rel = mysqli_fetch_assoc($trn_qry_rs)) {
		
			$amt = $trn_rel['product_qty'] * $trn_rel['product_rate'];
			$ttl_amt = $ttl_amt + 	$amt;
			$ttl_qty = $ttl_qty + $trn_rel['product_qty'];

			$html .= ' 
				<tr>
			<td style="border:1px solid;text-align:center;">'. $p . ' </td>
			<td style="border:1px solid;text-align:center;">'. $trn_rel['product_name'] . ' </td>
			<td style="border:1px solid;text-align:center;">'. $trn_rel['product_qty'] . ' </td>
			<td style="border:1px solid;text-align:center;">'. $currency_symbol . ' ' . $trn_rel['product_rate'] . '</td>
			</tr>
		
                ';
			$p++;
		}

		$pr=12-$cnt;


		$html .= '</table><br><br>
			 
			 ';
			 $terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
			 left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
			 where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
			 $terms_qry_rs=$dbcon->query($terms_qry);
			 if(mysqli_num_rows($terms_qry_rs)){
				 $html .= '<h3 style="text-align:left;">Terms and Conditions</h3>
				 <div><table width="100%" style="font-size:16px;border: none;width:100%;overflow:wrap;"><tbody>';
				 $t=1;
				 while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
					 $string=(nl2br($term_rel['tc_details']));
	 
					 $html.='<tr style="border:none;">
					 <td width="25%" style="width:25%;text-align:left;padding:5px;border:none; vertical-align: top; font-weight: bold;">'.$t.'. '.$term_rel['tc_name'].'</td>
					 <td width="75%" style="width:70%;text-align:left;padding:5px;border:none;">: '.$string.'</td>
					 </tr>';
					 $t++;
				 }
				 $html.='</tbody></table></div>';	
			 }
	

		$html .= '<br>
		<h3 style="text-align:left"> Bank Details: '.$comp_rel["bank_name"].', '.$comp_rel["branch_name"].',<br> A/C No: '.$comp_rel["ac_no"].',<br>
		RTGS/NEFT IFSC CODE: '.$comp_rel["ifcs"].'</h3>
		
			 ';
		$html .= '</table>
		<div style="text-align:left">
						<h3> <b>For, GEW Automation<br>
						Dhurv Suthar: +91 9913034453<br>
						Lalit Mistri: +91 9426462207<br></b> </h3>
						
					</div>';

					if($rel['with_bom_flag']=='1'){
						/* Get Bom of Product Start */
						$product_qry = "select pro.product_id, pro.product_name, pro.product_type 
						from tbl_quotation_trn as trn 
						left join product_mst as pro on pro.product_id=trn.product_id
						left join unit_mst as unit on unit.unitid=trn.unitid
						where trn.quot_trn_status=0 and trn.quotation_id=".$quotation_id;
						$result = mysqli_query($dbcon,$product_qry);
						$products = mysqli_fetch_all($result,MYSQLI_ASSOC);
						$html.='<center class="nextpage"></center>
						<div>

						<br>
						<table style="font-size:16px;border-collapse: collapse;border:1px solid;width:100%;" cellpadding="3" cellspacing="3">
						<thead>
						<tr>
						<th colspan="6" style="text-align:center;border:1px solid;"><h3 style="color:red;text-decoration:underline;">BILL OF MATERIAL FOR CONTROL PANEL </h3></th>
						</tr>	
						<tr>
						<th style="border:1px solid;width:5%;text-align:center;">Sr.<br/>No.</th>
						<th style="border:1px solid;width:50%;text-align:center;">Description</th>
						<th style="border:1px solid;width:15%;text-align:center;">Item Type</th>
						<th style="border:1px solid;width:10%;text-align:center;">Units</th>
						<th style="border:1px solid;width:10%;text-align:center;">Feedar Qty</th>
						<th style="border:1px solid;width:10%;text-align:center;">Total Qty</th>
						</tr>
						</thead>
						<tbody>';
						$ij=0;
						foreach($products as $product){
						$query="select bom.*,product.product_name,bunit.unit_name as base_unit_name,bunit.unit_name as conv_unit_name, bom_version.is_default_bom,product.product_type from tbl_bom as bom
							left join product_mst as product on product.product_id=bom.bom_product
							left join pro_ms_bom_version as bom_version on bom_version.bom_version_id=bom.bom_version_id
							left join unit_mst as bunit on bunit.unitid=bom.product_base_unit
							left join unit_mst as cunit on cunit.unitid=bom.product_conv_unit
							where bom.bom_product = ".$product['product_id'];
							// echo "<br>";
							$bom_qry_rs=$dbcon->query($query);
							if(mysqli_num_rows($bom_qry_rs)){
			
								$t=1;
								while($bom_rel=mysqli_fetch_assoc($bom_qry_rs)){
									if($bom_rel['is_default_bom']==1){
										$html .='<tr>
										<td style="border:1px solid;font-weight: bold;">'.$ij.'</td>
										<td style="border:1px solid;font-weight: bold;">'.$bom_rel['product_name'].'</td>
										<td style="border:1px solid;font-weight: bold; text-align: center;">'.get_product_type_by_id($dbcon,$bom_rel['product_type']).'</td>
										<td style="border:1px solid;font-weight: bold; text-align: center;">'.$bom_rel['base_unit_name'].'</td>
										<td style="border:1px solid;font-weight: bold; text-align: center;">'.$bom_rel['product_base_qty'].'</td>
										<td style="border:1px solid;font-weight: bold; text-align: center;">'.$bom_rel['product_base_qty'].'</td>
										</tr>';
										 $html .= quotation_print_with_bom($dbcon,$bom_rel['bom_id'],$bom_rel['product_base_qty'],$ij,0,0);
										$t++;
									}
								}
			
							}
							$ij++;
						}
						$html.='</tbody>
						</table>
						</div>';
					
					if($rel['inquiry_type']!='1'){
						$html.='<center class="nextpage"></center>
						<div>
						<br>
						<table style="font-size:16px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
						<thead>
						<tr>
						<th colspan="5" style="text-align:center;border:1px solid;"><h3 style="color:red;text-decoration:underline;">BILL OF MATERIAL FOR CONTROL PANEL </h3></th>
						</tr>
						<tr>
						<th style="border:1px solid;width:5%;text-align:center;">Sr.<br/>No.</th>
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
						where trn.quotation_projecttrn_status=0 and trn.quotation_id=".$quotation_id;
						$result = mysqli_query($dbcon,$product_qry);
						$rt=1;
						while($row=mysqli_fetch_assoc($result)){
							$html.='<tr>
							<td style="border:1px solid;text-align:center;vertical-align:top;">'.$rt.'</td>
							<td style="border:1px solid;vertical-align:top;">'.$row['product_name'].'</td>
							<td style="border:1px solid;text-align:center;vertical-align:top;">'.$row['hsn_code'].'</td>
							<td style="border:1px solid;text-align:center;vertical-align:top;">'.$row['product_qty'].'</td>
							<td style="border:1px solid;text-align:center;vertical-align:top;">'.$row['unit_name'].'</td>
							</tr>';
							$rt++;
						}
						$html.='</tbody>
						</table>
						</div>';
					}
				}	
			/* Get Bom of product end */
					$html.='</div>
					<!--page1 end-->';


		// $html .= '
		// 				<p style="text-align: center;">Subject to Ahmedabad jurisdiction <br>										
		// 					This is a Computer generated Document										
		// 					</p> ';




		$html .= '<sethtmlpagefooter name="otherpages_footer" value="on" />
			</body>
			</html>';
		//echo $trn_qry;
		// echo $html; die;	
		ob_end_clean();
		$file_name = $rel['quotation_no'] . '.pdf';
		$file_name = str_ireplace("/", "_", $file_name);
		if ($save_file == "No") {
			include("../../view/export/mpdf/mpdf.php");
		} else {
			include("../../../view/export/mpdf/mpdf.php");
		}
		$mpdf = new mPDF('', 'A4', '0', 'Calibri', '10', '10', '40', '5	', '1', '1');
		//		$mdf->SetFont('ProximaNova');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		// $mpdf->SetHTMLFooter($footer);
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
