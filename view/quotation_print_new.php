<?php 
session_start();
include("../config/config.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");

$userQuery = "SELECT u.*, type.usertype_name FROM users as u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	QUOTATION_SLUG_PRINT,
]);

if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}

$quotation_id = $_REQUEST['id'];	
$type='pdf';

if(strtolower($type) == 'pdf') {
//Quotation Data
	$query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name, cust.cust_gst from tbl_quotation as quot
	left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
	left join tbl_customer as cust on cust.cust_id=quot.cust_id
	left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
	left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
	where quot.quotation_id=".$quotation_id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));

	if(!$rel){
		header("Location: ".ROOT.CRM_ROOT."quotation_list");
	}

	$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';
//Company Data
/*$comp_qry="select * from tbl_company as comp 
where comp.company_id=".$rel['company_id'];
*/
$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];

$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
$header ='<div style="text-align:right;"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:3.27in;padding-top:25px;" /></div>';
//$header =$comp_rel["logo"];
//$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';
$users=$userData['user_name'].'<br>'.$userPhone .''. $userEmail.'<br>';

$companySettings = getCompanySettings($dbcon);
if($companySettings) {
	$quotation_print_content = $rel['quot_header'] ? $rel['quot_header'] :$companySettings['quotation_print_content'];
	$quotation_print_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_print_content);
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
	<div style="text-align:center">'.$header.'</div>
	</htmlpageheader>
	<!--<htmlpagefooter name="otherpages_footer" style="display:none">
	<div style="text-align:center">'.$footer.'</div>
	</htmlpagefooter>-->
	<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>';
// echo "SELECT tqps.*, tqpb.block_formate, tqpb.block_type FROM tbl_quotation_print_setup AS tqps LEFT JOIN tbl_quotation_print_block AS tqpb ON tqpb.quotation_print_block_id=tqps.quotation_print_block_id WHERE tqps.status=0 AND tqps.company_id='".$_SESSION['company_id']."' ORDER BY tqps.priority ASC";
	$chkPrintFormate=$dbcon->query("SELECT tqps.*, tqpb.block_formate, tqpb.block_type FROM tbl_quotation_print_setup AS tqps LEFT JOIN tbl_quotation_print_block AS tqpb ON tqpb.quotation_print_block_id=tqps.quotation_print_block_id WHERE tqps.status=0 AND tqps.company_id='".$_SESSION['company_id']."' ORDER BY tqps.priority ASC");
	while ($getPrintFormate=brp_mysqli_fetch_assoc($chkPrintFormate)) {
		if($getPrintFormate['block_type']==0){
			$details=$getPrintFormate['block_formate'];
			$details=str_replace(EMAIL_INSERT_TAG_PREFIX.'QUOTATION NO'.EMAIL_INSERT_TAG_POSTFIX, $rel['quotation_no'], $details);
			$details=str_replace(EMAIL_INSERT_TAG_PREFIX.'QUOTATION DATE'.EMAIL_INSERT_TAG_POSTFIX, date("d-M-Y",strtotime($rel['quotation_date'])), $details);
			$details=str_replace(EMAIL_INSERT_TAG_PREFIX.'INQUIRY REF NO'.EMAIL_INSERT_TAG_POSTFIX, $rel['inquiry_no'], $details);
			$details=str_replace(EMAIL_INSERT_TAG_PREFIX.'INQUIRY REF DATE'.EMAIL_INSERT_TAG_POSTFIX, date("d-M-Y",strtotime($rel['inquiry_date'])), $details);
			$details=str_replace(EMAIL_INSERT_TAG_PREFIX.'CUSTOMER NAME'.EMAIL_INSERT_TAG_POSTFIX, $rel['cust_name'], $details);
			$details=str_replace(EMAIL_INSERT_TAG_PREFIX.'CUSTOMER GST NO'.EMAIL_INSERT_TAG_POSTFIX, $rel['cust_gst'], $details);
			$details=str_replace(EMAIL_INSERT_TAG_PREFIX.'COMPANY NAME'.EMAIL_INSERT_TAG_POSTFIX, $comp_rel['company_name'], $details);
			$details=str_replace(EMAIL_INSERT_TAG_PREFIX.'USER DETAIL'.EMAIL_INSERT_TAG_POSTFIX, $users, $details);
			$details=str_replace(EMAIL_INSERT_TAG_PREFIX.'COMPANY GST NO'.EMAIL_INSERT_TAG_POSTFIX, $comp_rel['vatno'], $details);
			$details=str_replace(EMAIL_INSERT_TAG_PREFIX.'QUOTATION HEADER GREETING'.EMAIL_INSERT_TAG_POSTFIX, $quotation_print_content, $details);
			$details=str_replace(EMAIL_INSERT_TAG_PREFIX.'QUOTATION FOOTER GREETING'.EMAIL_INSERT_TAG_POSTFIX, $rel['quot_footer'], $details);
			$details=str_replace(EMAIL_INSERT_TAG_PREFIX.'ANNEXTURE DETAIL'.EMAIL_INSERT_TAG_POSTFIX, $rel['quot_annex_content'], $details);
			$details=str_replace(EMAIL_INSERT_TAG_PREFIX.'QUOTATION SUBJECT'.EMAIL_INSERT_TAG_POSTFIX, $rel['quot_subject'], $details);
			$details=str_replace(EMAIL_INSERT_TAG_PREFIX.'QUOTATION ADDRESS'.EMAIL_INSERT_TAG_POSTFIX, $quot_address, $details);

			if($getPrintFormate['quotation_print_block_id']==6 || $getPrintFormate['quotation_print_block_id']==7 || $getPrintFormate['quotation_print_block_id']==10){
				if($getPrintFormate['quotation_print_block_id']==6){
					$colspan=5;
				}else if($getPrintFormate['quotation_print_block_id']==7){
					$colspan=6;
				}
				$pro_type='<center class="nextpage"></center>
				<div>
					<table border="1" cellpadding="0" cellspacing="0" style="width: 100%; font-size: 12px;">
						<thead>
							<tr style="font-weight: bold;">
								<th style="width:5%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
								<th style="width:40%;text-align:center;border:1px solid;">Item Description</th>';
								if($getPrintFormate['quotation_print_block_id']==6 || $getPrintFormate['quotation_print_block_id']==10){
									$pro_type.='<th style="width:10%;text-align:center;border:1px solid;">HSN Code</th>
									<th style="width:10%;text-align:center;border:1px solid;">Qty</th>
									<th style="width:10%;text-align:center;border:1px solid;">Unit Price</th>
									<th style="width:10%;text-align:center;border:1px solid;">Discount</th>
									<th style="width:15%;text-align:center;border:1px solid;">Total Price</th>';
								} else {
									$pro_type.='<th style="width:7%;text-align:center;border:1px solid;">HSN Code</th>
									<th style="width:8%;text-align:center;border:1px solid;">Qty</th>
									<th style="width:10%;text-align:center;border:1px solid;">Unit Price</th>
									<th style="width:10%;text-align:center;border:1px solid;">Discount</th>
									<th style="width:10%;text-align:center;border:1px solid;">GST</th>
									<th style="width:10%;text-align:center;border:1px solid;">Total Price</th>';
								}
							$pro_type.='</tr>
						</thead>
						<tbody>';
						if($rel['inquiry_type']!='2'){
							$trn_qry="select trn.*,pro.product_name, pro.product_icode, unit.unit_name from tbl_quotation_trn as trn 
							left join product_mst as pro on pro.product_id=trn.product_id
							left join unit_mst as unit on unit.unitid=trn.unitid
							where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
						}else{
							$trn_qry="select trn.*,pro.product_name, pro.product_icode from tbl_quotation_project_trn as trn 
						left join product_mst as pro on pro.product_id=trn.product_id
						where trn.quotation_projecttrn_status=0 and trn.quotation_id=".$rel['quotation_id'];
						}
						$trn_qry_rs=$dbcon->query($trn_qry);
						$p=1;$ttl_amt=0;$ttl_qty=0;$ttl_pro_amt=0;
						$cnt=mysqli_num_rows($trn_qry_rs);
						while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
							$tax_amount = $trn_rel['tax_amount1'] + $trn_rel['tax_amount2'] + $trn_rel['tax_amount3'];
							$tax_amount = $tax_amount ? $tax_amount : '';
							if($rel['inquiry_type']!='2'){
								$unit_name= $trn_rel['unit_name'];
							}else{
								$unit_name="";
							}

							$tax_name1 = $trn_rel['tax_name1'] ? $trn_rel['tax_name1'] : '';
							$tax_name2 = $trn_rel['tax_name2'] ? $trn_rel['tax_name2'] : '';
							$tax_name3 = $trn_rel['tax_name3'] ? $trn_rel['tax_name3'] : '';
							$tax_name = $tax_name1.' '.$tax_name2.' '.$tax_name3;
							$tax_name = trim($tax_name) ? ' ('.$tax_name.')' : '';
							$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';
							$pro_type.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
								<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
								<td style="text-align:left;border:1px solid;vertical-align:top;"><strong>'.$trn_rel['product_name'].'</strong><br/>'.$product_desc.'</td>
								<td style="text-align:center;border:1px solid;vertical-align:top;">'.$trn_rel['product_icode'].'</td>
								<td style="text-align:center;border:1px solid;vertical-align:top;">'.$trn_rel['product_qty'].' '.$unit_name.'</td>
								<td style="text-align:center;border:1px solid;vertical-align:top;">';
								if($trn_rel['act_amt_flag']=='1'){
									$pro_type.="Extra At Actual";
								}
								else{
									$pro_type.=$trn_rel['product_rate'];
								}
								$pro_type.='</td>
								<td style="text-align:center;border:1px solid;vertical-align:top;">'.$trn_rel['product_discount'].'</td>';
								if($getPrintFormate['quotation_print_block_id']==7){
									$pro_type.='<td style="text-align:center;border:1px solid;vertical-align:top;">'.$tax_amount.$tax_name.'</td>';
								}
								$pro_type.='<td style="text-align:center;border:1px solid;vertical-align:top;">';
								if($trn_rel['act_amt_flag']=='1'){
									$pro_type.="Extra At Actual";
								}else{
									if($getPrintFormate['quotation_print_block_id']==10){
										$pro_type.=$trn_rel['product_amount'];
									} else{
										$pro_type.=$trn_rel['product_total'];
									}
								}	
								$pro_type.='</td>
							</tr>';
							$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
							$ttl_pro_amt=$ttl_pro_amt+$trn_rel['product_amount'];
							if($trn_rel['act_amt_flag']!='1'){
								$ttl_amt=$ttl_amt+$trn_rel['product_total'];
							}
								
							$p++;
						}
						$pr=10-$cnt;
						for($j=0; $j<$pr; $j++)
						{
						    $pro_type.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
								<td style="border:none;border-left:1px solid;border-right:1px solid;height:40px;"></td>
								<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
								<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
								<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
								<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
								<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>';
								if($getPrintFormate['quotation_print_block_id']==7){
									$pro_type.='<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>';
								}
								$pro_type.='<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
							</tr>';
						}
						$pro_type.='<tr>
								<td colspan="2" style="text-align:center;border:1px solid;"><strong>Total</strong></td>
								<td style="text-align:center;border:1px solid;"></td>
								<td style="text-align:center;border:1px solid;"><strong>'.$ttl_qty.'</strong></td>
								<td style="text-align:center;border:1px solid;"></td>
								<td style="text-align:center;border:1px solid;"></td>';
						if($getPrintFormate['quotation_print_block_id']==7){
							$pro_type.='<td style="text-align:center;border:1px solid;"></td>';
						}
						$pro_type.='<td style="text-align:center;border:1px solid;"><strong>'.$ttl_amt.'</strong></td>
							</tr>';
						if($getPrintFormate['quotation_print_block_id']==7 || $getPrintFormate['quotation_print_block_id']==6){
							$pro_type.='<tr>
								<td colspan="2" style="text-align:center;border:1px solid;"><strong>Total (In Words)</strong></td>
								<td colspan="'.$colspan.'" style="border: 1px solid; text-align: right;"><strong>'.convert_number_to_words_new($ttl_amt).'</strong></td>
							</tr>';
						} else if($getPrintFormate['quotation_print_block_id']==10){
							$pro_type.='<tr>
								<td colspan="4" style="text-align:center;border:1px solid;"><strong>Total (In Words): '.convert_number_to_words_new($ttl_amt).'</strong></td>
								<td colspan="2" style="border: 1px solid; !important; text-align: right;"><strong>Basic Amount</strong></td>
								<td style="border: 1px solid; text-align: right;"><strong>'.$ttl_pro_amt.'</strong></td>
							</tr>';
							$tax_qry="SELECT SUM(tax_amount1) AS tax1, SUM(tax_amount2) AS tax2, SUM(tax_amount3) AS tax3, tax_name1, tax_name2, tax_name3 FROM tbl_quotation_trn WHERE quot_trn_status=0 AND quotation_id='".$rel['quotation_id']."' GROUP BY formulaid";
							$tax_qry_rel=mysqli_fetch_assoc($dbcon->query($tax_qry));
							//echo $tax_qry;
							$pro_type.='<tr>
								<td colspan="4" style="text-align:center;border:1px solid;"></td>
								<td colspan="2" style="border: 1px solid; !important; text-align: right;"><strong>'.$tax_qry_rel['tax_name1'].'</strong></td>
								<td style="border: 1px solid; text-align: right;"><strong>'.$tax_qry_rel['tax1'].'</strong></td>
							</tr>';
							if($tax_qry_rel['tax2']!="" || $tax_qry_rel['tax2']!=0){
								$pro_type.='<tr>
									<td colspan="4" style="text-align:center;border:1px solid;"></td>
									<td colspan="2" style="border: 1px solid; !important; text-align: right;"><strong>'.$tax_qry_rel['tax_name2'].'</strong></td>
									<td style="border: 1px solid; text-align: right;"><strong>'.$tax_qry_rel['tax2'].'</strong></td>
								</tr>';
							}
							if(!empty($tax_qry_rel['tax3'])){
								$pro_type.='<tr>
									<td colspan="4" style="text-align:center;border:1px solid;"></td>
									<td colspan="2" style="border: 1px solid; !important; text-align: right;"><strong>'.$tax_qry_rel['tax_name3'].'</strong></td>
									<td style="border: 1px solid; text-align: right;"><strong>'.$tax_qry_rel['tax3'].'</strong></td>
								</tr>';
							}
							$pro_type.='<tr>
								<td colspan="4" style="text-align:center;border:1px solid;"></td>
								<td colspan="2" style="border: 1px solid; !important; text-align: right;"><strong>Grand Total</strong></td>
								<td style="border: 1px solid; text-align: right;"><strong>'.$ttl_amt.'</strong></td>
							</tr>';
						}
						$pro_type.='</tbody>
					</table>
				</div>
				<center class="nextpage"></center>';

				$html.=$pro_type;
			} else {
				$html.="<div>";
				$html.=$details;
				$html.="</div>";
			}
		} 
		if($getPrintFormate['block_type']==1){
			if($rel['inquiry_type']!='2'){
				$trn_qry="select trn.*,pro.product_name,unit.unit_name from tbl_quotation_trn as trn 
				left join product_mst as pro on pro.product_id=trn.product_id
				left join unit_mst as unit on unit.unitid=trn.unitid
				where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
			}else{
				$trn_qry="select trn.*,pro.product_name from tbl_quotation_project_trn as trn 
				left join product_mst as pro on pro.product_id=trn.product_id
				where trn.quotation_projecttrn_status=0 and trn.quotation_id=".$rel['quotation_id'];
			}

			$trn_qry_rs=$dbcon->query($trn_qry);
			$cnt=mysqli_num_rows($trn_qry_rs);
			if($cnt>0){
				$html.="<div><h3><strong>Product Specification</strong></h3>";
				while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
					$product_spec = ($trn_rel['product_spec']) ? nl2br($trn_rel['product_spec']) : '';
					$details=$getPrintFormate['block_formate'];
					$details=str_replace(EMAIL_INSERT_TAG_PREFIX.'PRODUCT SPECIFICATION'.EMAIL_INSERT_TAG_POSTFIX, $product_spec, $details);
					$details=str_replace(EMAIL_INSERT_TAG_PREFIX.'PRODUCT NAME'.EMAIL_INSERT_TAG_POSTFIX, $trn_rel['product_name'], $details);

					$html.=$details;
				}
				$html.="</div>";
			}
		}
		if($getPrintFormate['block_type']==2){
			$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
			left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
			where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
			$terms_qry_rs=$dbcon->query($terms_qry);
			$cnt=mysqli_num_rows($terms_qry_rs);
			if($cnt > 0){
				$count=1;
				$html.="<div><h3><strong>Terms & Conditions</strong></h3>";
				while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
					$string=(nl2br($term_rel['tc_details']));
					$title=EMAIL_INSERT_TAG_PREFIX.'TC TITLE'.EMAIL_INSERT_TAG_POSTFIX;
					$descri=EMAIL_INSERT_TAG_PREFIX.'TC DESCRIPTION'.EMAIL_INSERT_TAG_POSTFIX;
					$tc_ti=$getPrintFormate['block_formate'];
					$tc_ti=str_replace($title, $term_rel['tc_name'], $tc_ti);
					$tc_ti=str_replace($descri, $string, $tc_ti);
					if($count==1){
						$tc_ti=str_replace('</tbody></table>', "", $tc_ti);
						$html.=$tc_ti;
					}else if($count==$cnt){
						$tc_ti=str_replace('<table border="1" cellpadding="0" cellspacing="0" style="width: 100%;">	<tbody>', "", $tc_ti);
						$html.=$tc_ti;
					}else{
						$tc_ti=str_replace('<table border="1" cellpadding="0" cellspacing="0" style="width: 100%;">	<tbody>', "", $tc_ti);
						$tc_ti=str_replace('</tbody></table>', "", $tc_ti);
						$html.=$tc_ti;
					}
					//echo $tc_ti;
					$count++;
				}
				$html.="</div>";
			}
		}
	}
	$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
	// echo $html;exit;
	ob_end_clean();
	include("../view/export/mpdf/mpdf.php");
	$mpdf=new mPDF('','A4','0','proximanova','10','10','35','10','1','1');
//		$mdf->SetFont('ProximaNova');
	$mpdf->defaultheaderfontsize = 10; /* in pts */
	$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
	$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
	$mpdf->defaultfooterfontsize = 10; /* in pts */
	$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
	$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
	$mpdf->SetHTMLHeader($header);
		//$mpdf->SetHTMLFooter($footer);
	$mpdf->SetWatermarkText();
	$mpdf->showWatermarkText = true;
	$mpdf->allow_charset_conversion=true;
	$mpdf->charset_in='UTF-8';
	$mpdf->WriteHTML($html);
	$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
	ob_clean();
	return 'quotation'.$quotation_id.'.pdf';
}
?>