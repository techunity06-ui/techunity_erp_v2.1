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
	echo quotation_print($dbcon,$quotation_id,$save_file = "No");
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
		 $query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name, cadd.c_add_state,usa.user_name,cust.ledger_id,payt.payment_terms from tbl_quotation as quot
		left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
		left join tbl_customer as cust on cust.cust_id=quot.cust_id
		left join tbl_cust_address as cadd on cadd.cust_id=quot.cust_id
		left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
		left join tbl_refer_by as ref on cust.cust_source=ref.rb_id
		left join users as usa on usa.user_id=quot.user_id
		left join pay_terms as payt on payt.terms_id=quot.payment_terms_id
		where quot.quotation_id=".$quotation_id;
		$rel=mysqli_fetch_assoc($dbcon->query($query));

		if($rel['ledger_id']==0){
			$execust="No";
		}else{
			$execust="Yes";
		}
//p($rel);

		if(!$rel){
			header("Location: ".ROOT.CRM_ROOT."quotation_list");
		}

		if($rel['quot_type']=='0'){
			$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`=68 ';
			$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));
			$currency_name = '(INR)';
			$currency_name = 'INR';
			$currency_word_start = 'Rupees';
			$currency_word_end = 'Paise';
			$currency_symbol = $currency_rel['currency_symbol'];
		}else{
			$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
			$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

//			$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
			$currency_name = ucfirst(strtolower($currency_rel['currency_code']));
			$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
			$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
			$currency_symbol = $currency_rel['currency_symbol'];
		}
		$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';
		$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];

		$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
		$header ='<div style="text-align:center;padding-top:30px"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:8.27in;" /></div>';
		$footer ='<div style="text-align:center;"><img src="'.DOMAIN_F.LOGO.$comp_rel["f_logo"].'" style="width:8.27in;" /></div>';
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
			$quotation_footer_content = $companySettings['quotation_footer_content'] ? $companySettings['quotation_footer_content'] : $quotation_footer_content;
			$quotation_footer_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_footer_content);
		}
		$disc_qry=$dbcon->query("SELECT SUM(trn.product_discount_conv) as discount from tbl_quotation_trn as trn where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id']);
		$disc_qrys = brp_mysqli_fetch_assoc($disc_qry);
		if($companyConfiguration['quot_revise_time_rate_with_discount'] == 0){
			$colspan =($disc_qrys['discount'] > 0) ? 5 : 4;	
		}else{
			$colspan = 4;
		}
	
//Amish Soni End 16-03-2021
		$html ='<html>
		<head>					
		<title>Quotation</title>
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
			
			.quot_annex_content_div table tr,td{
				padding:5px;
			}
			.blueHeading {
				background-color: #a7adb5;
			}
				.nextpage
			{
				page-break-after: always;
			}
				.border {
					border: none;
					/*padding-top: 125px;
					padding-bottom: 125px;*/
		
				}
		
				.pxborder {
					border: 1px solid black;
					padding-top: 125px;
					padding-bottom: 125px;
					border-collapse: collapse;
		
				}
		
				.noborder {
					
					border-bottom: 1px solid black;
					border-top: 1px solid black;
				}
		
				.insideb {
					border: 1px solid black;
					border-bottom: 1px solid black;
					border-top: 1px solid black;
				}
		
				.padding {
					padding-left: 10px;
				}
		
				.textcentre {
					text-align: center;
				}
				.textleft {
					text-align: left;
				}
				.center-table {
					display: flex;
            justify-content: center;
            align-items: center;
				}

			</style>
			</head>
			<body>
			<!--Show Logo in other pages-->
			<htmlpageheader name="otherpages" style="display:none">
			<div>
			<table class="border" style="font-size: 13px; ">
				<tr>
					<td class="padding">
						<b>KindAttn : '.$rel['c_con_fname'].' '.$rel['c_con_lname'].'</b>
					</td>
				</tr>
				<tr>
					<td class="padding">
						<b>To,</b>
					</td>
				</tr>
				<tr>
					<td class="padding">
						<b>M/s. '.$rel['cust_name'].'</b>
					</td>
				</tr>
				<tr>
					<td style="padding-left: 30px;">
						Kolkata India.
					</td>
				</tr>
				<tr>
					<td class="padding" style="padding-top: 15px;">
						Email : saurav@chemtexlimited.com
					</td>
				</tr>
				<tr>
					<td class="padding">
						Ph No.: +91 – 9830009999
					</td>
				</tr>
				<tr>
					<td class="padding" style="padding-top: 8px;">
						<b>Offer No: JP/23-24/ 195 </b>
						<span style="padding-top: 8px; padding-left: 225px;"><b>Dtd : 01/10/2023</b></span>
					</td>
					<td>
		
					</td>
				</tr>
				<tr>
					<td class="padding" style="padding-top: 15px;">
						<b>Ref : As Per your inquiry For SKIDDED FILTERATION SYSTEM Dated : 30/09/2023</b>
					</td>
				</tr>
				<tr>
					<td class="padding" style="padding-top: 15px;">
						<b>Respected Sir / Madam,</b>
					</td>
				</tr>
				<tr>
					<td class="padding" style="padding-top: 8px;text-align: justify;">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;With reference to your above mentioned enquiry and data provided to us, we are pleased to submit herewith our techno-commercial offer for BAG FILTER
						HOUSING.
					</td>
				</tr>
				<tr>
					<td class="padding" style="padding-top: 8px;text-align: justify;">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>JP SONS ENGINEERING</b> is committed to Provide the best product with Services to it’s customer to improve the efficiency of their processes. All Products are manufacture at Kathwada, Ahmedabad with all advance equipments. We would like to highlight that we are also manufacture the below advance equipments. We would like to highlight that we are also manufacture the below product at our Kathwada Plants with number of products in different industries as per the requirements.
					</td>
				</tr>
				<tr>
					<td class="padding" style="padding-top: 8px;text-align: justify;">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;We are working with our group of companies, manufacturer of <b>AGITATOR, FLASH MIXER,
							FLOCCULATOR, HIGH SHEAR MIXER, MIXING TANK, STIRRER, JACKETED TANK, REACTOR VESSEL, OINTMENT MFG.
							PLANT, SYRUP MFG.PLANT,BAG FILTER HOUSING, CARTRIDGE FILTER HOUSING, AUTO LIFTING MECHANICAL STAND,
							HYDRAULIC LIFTING STAND FOR AGITATOR AND HIGH SHEAR MIXER, HOMOGENISER, ETC.</b> Our Product is used
						by thousand of satisfied customers in industrial Sector like Sugar, Fertilizer, Textiles, Pharma,
						Electroplating, Chemical Industries, Paints, Food and Beverages, Rubber and Tyer industries and Paper
						Industries.
					</td>
				</tr>
				<tr>
					<td class="padding" style="padding-top: 8px;text-align: justify;">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;We sincerely trust the above is in line with your requirement and we now look forward to the
						pleasure of receiving you valuable purchase order. If in case, you required any further
						clarifications/information then please feel free to contact us at above address and it would be our
						pleasure to reply on the same. <br>Thanking you and assuring you of our best attention always.
					</td>
				</tr>
				<tr>
					<td class="padding" style="padding-top: 15px;">
						With Regards,
					</td>
				</tr>
				<tr>
					<td class="padding" style="padding-top: 15px;">
						<b>SagarPanchal</b>
					</td>
				</tr>
				<tr>
					<td class="padding" style="padding-top: 15px;">
						<b>JP SONS ENGINEERING </b>
					</td>
				</tr>
				<tr>
					<td class="padding" style="padding-top: 15px;">
						<b>Mobile: +91 7359352645</b>
					</td>
				</tr>
			</table>
			<center class="nextpage"></center>
			<div class="textcentre " style=" padding-top: 20px; padding-bottom: 20px; font-size: 16px; text-align:center"><b> • BAG FILTER HOUSING consist of the
						following equipment’s and accessories.
					</b></div>
			<div>
				<table class="pxborder" style="width:100%;font-size: 13px; width: 100%; font-family: Arial, Helvetica, sans-serif;">
				<tr>
					<td class="insideb textcentre"  style="border: 1px solid black;width:10%"><b>SR NO.</b></td>
					<td class="insideb textcentre" style="border: 1px solid black;width:30%"><b>NAME OF EQUIPMENT</b> </td>
					<td class="insideb textcentre" style="border: 1px solid black;width:20%"><b> QUANTITY</b></td>
					<td class="insideb textcentre" style="border: 1px solid black;width:20%"><b> MAKE</b></td>
					<td class="insideb textcentre" style="border: 1px solid black;width:20%"><b>PRICE FOR <br>(EACH NO.)</b></td>
				</tr>
			';

				if($inquiry_type!="2"){
					$trn_qry="SELECT trn.*,pro.product_name,unit.unit_name, pro.product_icode,hsn.hsn_code FROM tbl_quotation_trn as trn 
				   left join product_mst as pro on pro.product_id=trn.product_id
				   left join unit_mst as unit on unit.unitid=trn.unitid
				   left join mst_hsn_code as hsn on hsn.hsn_id=pro.product_hsn
				   where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
			   } else {
					 $trn_qry="SELECT trn.* , pro.product_name,pro.product_icode FROM `tbl_quotation_project_trn` as trn 
					 left join product_mst as pro on pro.product_id = trn.product_id 
				   where trn.quotation_projecttrn_status=0 and trn.quotation_id =".$rel['quotation_id'];
			   }
			   $trn_qry_rs=$dbcon->query($trn_qry);
			   $p=1;$ttl_amt=0;$ttl_qty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;
			   $cnt=mysqli_num_rows($trn_qry_rs);
			   while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
				   $item_code = '';
				   if(in_array('item',$sales_pro_search)){
					   $item_code = " -- (".$trn_rel['product_icode'].")";
				   }
				   $product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';
   
				   $html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
							   <td  style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
							   <td style="text-align:left;border:1px solid;vertical-align:top;">
								   <strong>'.$trn_rel['product_name'].'</strong>'.$item_code.'
							   </td>
							   <td style="text-align:center;border:1px solid;vertical-align:top;">
								   '.$trn_rel['product_qty']. ' '.$trn_rel['unit_name'].'
							   </td>
							   <td style="text-align:center;border:1px solid;vertical-align:top;">
								   JP SONS
							   </td>
							   <td style="text-align:center;border:1px solid;vertical-align:top;">
								   '.$currency_symbol.' '.$trn_rel['product_rate'].'
							   </td>
							   ';
   
				   $html.='</tr>';
				   $ttl_qty=$ttl_qty+$trn_rel['product_qty'];
				   $ttl_amt=$ttl_amt+$trn_rel['product_amount'];
				   $p++;
			   }
			   $pr=10-$cnt;
			   $html.='</table>
            
			<center class="nextpage"></center>';
			$x = 1;
			  $trn_qry_rs=$dbcon->query($trn_qry);
			 while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
			      $product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';
			     $html .="<div style='text-align:center'> <h2>".$x."• &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;". $trn_rel['product_name'] ."  </h2></div>";
			     $html .="<div style='text-align:center'> ".  $product_desc ." </div>";
			     $x++;
			 }
			$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
	left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
	where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
	$terms_qry_rs=$dbcon->query($terms_qry);$tc = 1;
	if(mysqli_num_rows($terms_qry_rs)){
		$html.='
	<div>

		<div style="text-align:center"><h2><u>TERMS & CONDITIONS</u></h2></div>
		<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
			<tbody>';
		while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
			$string=(nl2br($term_rel['tc_details']));
			
			$html.='<tr>
				<td width="25%" style="width:25%;text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;">'.$term_rel['tc_name'].'</td>
				<td width="70%" style="width:70%;text-align:left;border:1px solid #000;padding:5px;">'.$string.'</td>
				</tr>';
				
			$tc++;
		}
		$html.='
		</table>';	
		$html.='</div>';
	}

	$html .='<p>Hope the same will be in line of your requirement. If you need any further clarification on the same, please feel free to contact us. <br></p>
			</table><p>Thanking you and looking forward to having our long and fruitful business relationship. Yours Faithfully,</p>
			<p style="font-size: larger;"><b>FOR, JP SONS ENGINEERING</b></p>
			<p ><b>SAGAR PANCHAL</b></p>
			<p >CELL NO.: +91 7359352645 <br>
				EMAIL:jpsonsengineering@gmail.com
				</p>';

			$html.='</div>
	</body>
</html>';
 //echo $trn_qry;
 //echo $html;
 //die();
			echo $html;exit;
			
			ob_end_clean();
			$file_name = $rel['quotation_no'].'.pdf';
			$file_name=str_ireplace("/","_",$file_name);
			if($save_file=="No"){
				include("../../view/export/mpdf/mpdf.php");
			}else{
				include("../../../view/export/mpdf/mpdf.php");
			}
			$mpdf=new mPDF('','A4','0','calibri','10','10','50','30','1','1');
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
			// $mpdf->SetFooter('{PAGENO}{nbpg}');
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