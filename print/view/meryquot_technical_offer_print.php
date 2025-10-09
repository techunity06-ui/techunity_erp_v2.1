<?php 
$quotation_id = $_REQUEST['id'];	
if(!empty($quotation_id)){
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
	include("../../include/function_database_query.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	$incPath = $path.'include/';

	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		QUOTATION_SLUG_PRINT,
	]);

	if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	quotation_print($dbcon,$quotation_id,$save_file = "No");
}
function quotation_print($dbcon,$quotation_id,$save_file){
	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
	$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
	$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';

// $quotation_id = $_REQUEST['id'];	
	$type='pdf';
	if(strtolower($type) == 'pdf') {
//Quotation Data
		$query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.isd_id,per.c_con_email,cust.cust_name,per.c_con_job, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name, cadd.c_add_state,cust.cust_gst from tbl_quotation as quot
		left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
		left join tbl_customer as cust on cust.cust_id=quot.cust_id
		left join tbl_cust_address as cadd on cadd.cust_id=quot.cust_id
		left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
		left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
		where quot.quotation_id=".$quotation_id;
		$rel=mysqli_fetch_assoc($dbcon->query($query));
//p($rel);
		if(!$rel){
			header("Location: ".ROOT.CRM_ROOT."quotation_list");
		}

		$inquiry_ref_date='';
    	
    	if($rel['inquiry_ref_date']!="1970-01-01" && $rel['inquiry_ref_date']!="0000-00-00"){
    		$inquiry_ref_date=date('d-M-Y',strtotime($rel['inquiry_ref_date']));
    	}else if($rel['inquiry_date']!="1970-01-01" && $rel['inquiry_date']!="0000-00-00"){
    	    $inquiry_ref_date = date('d-M-Y',strtotime($rel['inquiry_date']));
    	}

		if($rel['quot_type']=='0'){
			$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`=68 ';
			$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));
			$currency_name = '(INR)';
			$currency_word_start = 'Rupees';
			$currency_word_end = 'Paise';
			$currency_symbol = $currency_rel['currency_symbol'];
		}else{
			$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
			$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

			$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
			$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
			$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
			$currency_symbol = $currency_rel['currency_symbol'];
		}
		$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';
		$set="select comp.*,state.state_name,state.gst_state_code,city.city_name from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid 
			left join city_mst as city on city.cityid = comp.city_id
		where company_id=".$rel['company_id'];

		$comp_rel=brp_mysqli_fetch_assoc($dbcon->query($set));
		
		//$header = get_header($dbcon,'text-align: left','200px','50px');

		$header = '<table style="font-size:13px;border-collapse: collapse; width:100%; border:none !important" >
			<tr>
				<td colspan="4" style="vertical-align:top;border:none !important; text-align:left;height:10px"></td>
			</tr>

			<tr>
				<td colspan="3" style="vertical-align:top;border:none !important; text-align:left"><h2>'.$comp_rel['company_name'].'</h2></td>

				<td rowspan="5" style="vertical-align:top;border:none !important;text-align:right"><img src="'.DOMAIN_F.LOGO.$comp_rel['logo'].'" style="height: 60px; width: 100px;"></td>
			</tr></table>';	
		$head = '<table style="font-size:13px;border-collapse: collapse; width:100%; border:none !important" >
			<tr>
				<td colspan="4" style="vertical-align:top;border:none !important; text-align:left;height:10px"></td>
			</tr>
			<tr>
				<td colspan="3" style="vertical-align:top;border:none !important; text-align:left"><h2>'.$comp_rel['company_name'].'</h2></td>

				<td rowspan="5" style="vertical-align:top;border:none !important;text-align:right"><img src="'.DOMAIN_F.LOGO.$comp_rel['logo'].'" style="height: 60px; width: 100px;"></td>
			</tr>
			<tr>
				<td colspan="3">'.$comp_rel['address'].' '.$comp_rel['city_name'].'-'.$comp_rel['pincode'].','.$comp_rel['state_name'].',INDIA</td>
			</tr>
			<tr>
				<td colspan="3">Phone : '.$comp_rel['contact_no'].'</td>
			</tr>
			<tr>
				<td colspan="3">Web : '.$comp_rel['company_website'].'</td>
			</tr>
			<tr>
				<td >Gst No : '.$comp_rel['vatno'].'</td>
				<td >Lut No : '.$comp_rel['lut_no'].'</td>
				<td >IEC No : '.$comp_rel['iec_no'].'</td>
			</tr>
			</table>';

		
		/*$header ='<div style="text-align:center;">
			<img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:8.27in;" />
		</div>';*/

		/*$footer ='<div style="text-align:center;">
			<img src="'.DOMAIN_F.LOGO.$comp_rel["f_logo"].'" style="width:8.27in;" />
		</div>';*/
		$footer ='<table width="100%" style="border-top:2px solid">

		<tr>
		<td style="text-align:left;vertical-align:top;width:50%; bold;border-top:1px solid !important">
			<strong>'.$rel['quotation_no'].' - DTD : '.date('d-m-Y',strtotime($rel['quotation_date'])).'</strong>
		</td>
		<td style="text-align:right;vertical-align:top;width:50%; bold;border-top:1px solid !important"> <strong>Page {PAGENO} of {nbpg}</strong></td>
		</tr>
		</table>';

		$approve_status='';
		if($rel['approve_status']=='0'){
			$approve_status=' (DRAFT)';
		}
		$inquiry_type=$rel['inquiry_type'];
//Amish Soni Start 16-03-2021
		$companySettings = getCompanySettings($dbcon);
		$companyConfiguration=getCompanyConfiguration($dbcon);
		$sales_pro_search=explode(",", $companyConfiguration['sales_pro_search']);

		if($companySettings) {
			$quotation_print_content = $companySettings['quotation_print_content'] ? $companySettings['quotation_print_content'] : '';
			$quotation_print_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_print_content);
			$quotation_footer_content = $rel['quotation_footer_content'] ? $rel['quotation_footer_content'] : $quotation_footer_content;
			$quotation_footer_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_footer_content);
		}
		$disc_qry=$dbcon->query("SELECT SUM(trn.product_discount_conv) as discount from tbl_quotation_trn as trn where trn.quot_trn_status=0 reand trn.quotation_id=".$rel['quotation_id']);
		$disc_qrys = brp_mysqli_fetch_assoc($disc_qry);
		/*if($companyConfiguration['quot_revise_time_rate_with_discount'] == 0){
			$colspan =($disc_qrys['discount'] > 0) ? 5 : 4;	
		}else{*/
			$colspan = 9;
		//}
		$designation='';
		if($rel['c_con_job']){
			$designation = '('.$rel['c_con_job'].')';
		}

		$isd_code ='';
		if(!empty($rel['isd_id'])){
			$isd_data = get_isd_data_mst($dbcon,$rel['isd_id']);
			$isd_code = '+'.$isd_data['phonecode'].'-';
		}
//Amish Soni End 16-03-2021
		$html ='<html>
		<head>					
		<title>Quotation - '.$rel['quotation_no'].'</title>
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

			
			.quot_annex_content_div table tr,td{
				padding: 2px 5px;
			}
			.blueHeading {
				color: #365f91;
			}

			</style>
			</head>
			<body>
			<!--Show Logo in other pages-->
			<htmlpageheader name="otherpages" style="display:none">
			<div style="text-align:center">'.$head.'</div>
			</htmlpageheader>
			<htmlpagefooter name="otherpages_footer" style="display:none">
			<div style="text-align:center">'.$footer.'</div>
			</htmlpagefooter>
			<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
			<div>
			<table cellpadding="5" cellspacing="5" style="font-size: 14px; font-family: Proxia Nova; ">
			<tr >
				<td style="font-size: 16px; font-family: Proxia Nova; text-align:center; ">
					<strong>TECHNICAL OFFER (Unpriced)</strong>
				</td>
			</tr>
			
			</table>';
			$html .= '<table style="font-size:14px;border-collapse: collapse;width:100%;border:none" cellpadding="3" cellspacing="3">
			
			<tr>
			<td rowspan="6" style="text-align:left;vertical-align:top;border:1px solid;width:49%;"> 
			<strong>Customer Details : <br>
			'.$rel['cust_name'].'<br/></strong>
			
			'.($quot_address).'<br/>
			GST No. : '.$rel['cust_gst'].'
			</td>
			<td style="width:2%"></td>
			<td style="text-align:left;border:1px solid;width:24.5%;"> 
			Quotation No
			</td>
			<td style="text-align:left;border:1px solid;width:24.5%;"> 
			<strong>'.$rel['quotation_no'].'</strong>
			</td>
			</tr>
			<tr>
			<td></td>
			<td style="text-align:left;border:1px solid;"> 
			Quotation Date
			</td>
			<td style="text-align:left;border:1px solid;"> 
			'.date("d-M-Y",strtotime($rel['quotation_date'])).'
			</td>
			</tr>

			<tr>
			<td></td>
			<td style="text-align:left;border:1px solid;"> 
			Inquiry Ref 
			</td>
			<td style="text-align:left;border:1px solid;"> 
			'.$rel['quotation_ref'].'
			</td>
			</tr>
			<tr>
			<td></td>
			<td style="text-align:left;border:1px solid;"> 
			Inquiry Ref Date
			</td>
			<td style="text-align:left;border:1px solid;"> 
			'.$inquiry_ref_date.'
			</td>
			</tr>
			<tr>
			<td></td>
			<td style="text-align:left;border:1px solid;"> 
			Other Ref.
			</td>
			<td style="text-align:left;border:1px solid;"> 
			'.$rel['quot_subject'].'
			</td>
			</tr>
			<tr>
			<td></td>
			<td style="text-align:left;border:1px solid;"> 
			Delivery Period
			</td>
			<td style="text-align:left;border:1px solid;"> 
			'.$rel['production_up_to'].'
			</td>
			</tr>
		</table>
		<br>
		<table style="font-size:14px;border-collapse: collapse;width:100%;border:none" cellpadding="3" cellspacing="3">

		<tr>
			<td style="text-align:left;width:45%;border:1px solid;border-right:none"> 
			Kind Attn. : '.$rel['c_con_fname'].' '.$rel['c_con_lname'].' '.$designation.'
			</td>
			<td style="text-align:left;border:1px solid;border-right:none"> 
			Email : '.$rel['c_con_email'].'
			</td>
			<td style="text-align:left;border:1px solid;border-right:1px solid"> 
			Contact No : '.$isd_code.' '.$rel['c_con_mobile'].'
			</td>
			</tr>
		</table>';
			
			$html.='<table cellpadding="5" cellspacing="5" style="font-size: 14px; font-family: Proxia Nova; ">
			<tr>
				<td>
				'.stripslashes($quotation_print_content).'
				</td>
			</tr>
			</table>';

			if($inquiry_type!="2"){
			 	$trn_qry_meru="SELECT trn.*,pro.product_name,unit.unit_name, pro.product_icode,hsn.hsn_code FROM tbl_quotation_trn as trn 
				left join product_mst as pro on pro.product_id=trn.product_id
				left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
				left join unit_mst as unit on unit.unitid=trn.unitid
				where trn.quot_trn_status=0 and trn.pid = 0 and  trn.quotation_id=".$rel['quotation_id'];
			} else {
			  	$trn_qry_meru="SELECT trn.* , pro.product_name,pro.product_icode FROM tbl_quotation_project_trn as trn 
			  	left join product_mst as pro on pro.product_id = trn.product_id 
				where trn.quotation_projecttrn_status=0 and trn.quotation_id =".$rel['quotation_id'];
			}
			$trn_qry_rs_meru=$dbcon->query($trn_qry_meru);
			while($trn_rel_meru=brp_mysqli_fetch_assoc($trn_qry_rs_meru))
			{
			$product_desc = ($trn_rel_meru['product_desc']) ? nl2br($trn_rel_meru['product_desc']) : '';
			$product_spec = ($trn_rel_meru['product_spec']) ? nl2br($trn_rel_meru['product_spec']) : '';
			//$product_desc_meru = $trn_rel_meru['product_desc'] ? $trn_rel_meru['product_desc'] :'';
			//$product_desc_meru = str_ireplace(array("\r","\n",'\r','\n'),'', $product_desc_meru);
			//$product_spec_meru = $trn_rel_meru['product_spec'] ? $trn_rel_meru['product_spec'] :'';
			//$product_spec_meru = str_ireplace(array("\r","\n",'\r','\n'),'', $product_spec_meru);
				$html.='<!--<table style="font-size:14px;border-collapse: collapse; width:100%;"  cellpadding="3" cellspacing="3">
				<tbody>
				<tr>
				<td style="text-align:center;border:1px solid" colspan="'.($colspan+1).'"><strong>'.$trn_rel_meru['product_name'].'</strong></td>
				</tr>
				</tbody>
				</table>-->
				<div>'.$product_desc.'<br>'.$product_spec.'</div>';

			}
			
			
			/* Get Product Images Start */
			
			 $images_qry = "select trn.quot_trn_id,tpi.im_name from tbl_quotation_trn as trn 
			left join product_mst as pro on pro.product_id = trn.product_id
			left join tbl_product_images as tpi on tpi.im_product = pro.product_id
			where trn.quot_trn_status=0  and trn.pid = 0 and trn.quotation_id=".$rel['quotation_id'];
			
			$html .='
			<div>';

				$images_rec=$dbcon->query($images_qry);
				$path='view/upload/product_images/';
				
				//$html .='<table></tr>';
				while($row  = brp_mysqli_fetch_assoc($images_rec))
				{
					//$html .='<td>';

					if($row['im_name']!=null)
					{
						$html.='<center class="nextpage"></center><center style ="text-align: center" ><br><div><a href="'.DOMAIN_F.'view/upload/product_images/'.$row["im_name"].'" target="_blank"><img src="'.DOMAIN_F.'view/upload/product_images/'.$row['im_name'].'" style="width: 100%; height: 900px;"></a>
					
							</div></center>';
					}
					else
					{
						$html.='';
					}
						
					
				}
				//$html .='</tr></table>';
			
			$html.='</div>';
			/* Get Product Images End */

			/* Check Annexure Attachments Start */
				/*if(!empty(trim($rel['quot_annex_content']))){
					$html.='<center class="nextpage"></center>';
					$html.='<div class="quot_annex_content_div" style="font-size: 16px;">'.$rel['quot_annex_content'];
					$html.='</div>';
				}*/
			/* Check Annexure Attachments End */
			/*if(!empty($quotation_footer_content)){
				$html.='<br /><br /><div>'.$quotation_footer_content;
				$html.='</div>';
			}*/
			
			$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
			</body>
			</html>';
 //echo $trn_qry;
//echo $head;
//echo $html;exit;
			ob_end_clean();
			$file_name = $rel['quotation_no'].'.pdf';
			$file_name=str_ireplace("/","_",$file_name);
			if($save_file=="No"){
				include("../../view/export/mpdf/mpdf.php");
			}else{
				include("../../../view/export/mpdf/mpdf.php");
			}
			$mpdf=new mPDF('','A4','0','calibri','10','10','35','20','3','5');
//		$mdf->SetFont('ProximaNova');
			$mpdf->defaultheaderfontsize = 10; /* in pts */
			$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
			$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
			$mpdf->defaultfooterfontsize = 10; /* in pts */
			$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
			$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
			$mpdf->SetHTMLHeader($head);
			$mpdf->SetHTMLFooter($footer);
//Show page number : Dimple Panchal (05-Apr-2021)
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
			if($save_file=="No"){
				$mpdf->Output();
			}else{
				$mpdf->Output('../../../view/upload/mail_attach/'.$file_name,'f');
			}
			ob_clean();
			return $file_name;
		}
	}
?>