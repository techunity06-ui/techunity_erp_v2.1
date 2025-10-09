<?php 

$quotation_id = $_REQUEST['id'];	
if (!empty($quotation_id)) {
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
	include("../../include/function_database_query.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	include_once("../../crm/include/crm_common_functions.php");
	include_once(COMMON_FUNCTION_PATH."common_production_functions.php");
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		QUOTATION_SLUG_PRINT,
	]);

	if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	quotation_print($dbcon,$quotation_id,$save_file = "No");
}

function quotation_print($dbcon,$quotation_id,$save_file) {
	$type='pdf';
	if(strtolower($type) == 'pdf') {
		//Quotation Data
		$query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile,cadd.c_add_state, inq.inquiry_no,inq.inquiry_date, ref.rb_name, anx.an_name, cur.currency_code, cur.currency_in_word, cur.currency_in_word_end from tbl_quotation as quot
		left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
		left join tbl_customer as cust on cust.cust_id=quot.cust_id
		left join tbl_cust_address as cadd on cadd.cust_id=quot.cust_id
		left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
		left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
		left join tbl_annexure as anx on anx.an_id=quot.an_id
		left join tbl_currency as cur on cur.currency_id = quot.currency_id
		where quot.quotation_id=".$quotation_id;
		$rel=mysqli_fetch_assoc($dbcon->query($query));

		if(!$rel){
			header("Location: ".ROOT.CRM_ROOT."quotation_list");
		}

		$quot_address =	$rel['quot_address'] ? remove_special_char((nl2br($rel['quot_address']))) : '';
		//Company Data
		/*$comp_qry="select * from tbl_company as comp 
		where comp.company_id=".$rel['company_id'];
		*/
		$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$_SESSION['company_id'];

		$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
		$header ='<div style="text-align:center;"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:100%;height:150px" /></div>';
		//$header =$comp_rel["logo"];
		
		$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:100%"/>';

		$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
		WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
		$userData = mysqli_fetch_assoc($dbcon->query($userQuery));
		$userPhone = $userData['user_phone'] ? '<strong>Mo.:</strong> '.$userData['user_phone'] : '';
		$userEmail = $userData['user_mail'] ? '<strong>Email:</strong> '.$userData['user_mail'] : '';

		$approve_status='';
		if($rel['approve_status']=='0'){
			$approve_status=' (DRAFT)';
		}

		$quotation_print_content = $rel['quot_header'] ? $rel['quot_header'] : $companySettings['quotation_print_content'];
		$quotation_footer_content = $rel['quot_footer'] ? $rel['quot_footer'] : $companySettings['quotation_footer_content'];
		$quotation_print_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_print_content);
		$quotation_footer_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_footer_content);
		$quot_annex_content=($rel['quot_annex_content']) ? $rel['quot_annex_content'] : '';

		$trnsql=$dbcon->query("select SUM(product_discount) as total_discount from tbl_quotation_trn where quot_trn_status=0 and quotation_id=".$rel['quotation_id']);
		$getsql=mysqli_fetch_assoc($trnsql);

		if($getsql['total_discount']>0){
			$colsapn=4;
		}else{
			$colsapn=3;
		}

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
		<htmlpagefooter name="otherpages_footer" style="display:none">
		<div style="text-align:center">'.$footer.'</div>
		</htmlpagefooter>
		<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
		<div>
		<table cellpadding="5" cellspacing="5" border="0" style="font-size: 16px; font-family: Proxia Nova; border: 0;">
		<tr style="border: 0;">
		<td style="border: 0; text-align: right; font-size: 15px;"></td>
		</tr>
		</table>
		<table cellpadding="5" cellspacing="5" border="0" style="font-size: 16px; font-family: Proxia Nova; border: 0;">
		<tr style="border: 0;">
		
		<td style="border: 0;width:50%;">
		<p>
		Ref.:'.$rel['quotation_no'].'
		</p>
		<p>
		Date: '.date("d-M-Y",strtotime($rel['quotation_date'])).'
		</p>
		<br>
		<p style="float: left; width: 100%;text-transform: lowercase;text-transform: capitalize;">
		<strong>M/S : '.$rel['cust_name'].',</strong><br/>';
		$html .='<strong style="color: black">'.$quot_address.'<br>';
		if(!empty($rel['c_con_fname']) || !empty($rel['c_con_lname'])){
			$html .= '<strong>Kind Attn : '.$rel['c_con_fname'].' '.$rel['c_con_lname'].' </strong><br>';
		}
		$html .= '<strong>Mobile No : '.$rel['cust_mobile'].'</strong>
		</p>
		<strong>Email Id : '.$rel['cust_email'].'</strong>
		
		<!--<p>Regards</p>
		<p>'.$userData['user_name'] .' - '. $userData['usertype_name'].'</p>
		<p>'.$userPhone .'<br>'. $userEmail.'</p>-->
		</td>
		<td style="border: 0;width:50%;"></td>
		</tr>
		<tr style="border: 0;">
		<td colspan="2" style="border: 0;">
			<p style="float: left; width: 100%;text-align:center"><strong>Subject: '.$rel['quot_subject'].'</strong></p>
		'.remove_special_char(($quotation_print_content)).'
		</td>
		</tr>
		</table>
		</div>
		<center class="nextpage"></center>
		<div>';

		$pr_sp = "SELECT trns.product_spec,product.product_name from tbl_quotation_trn as trns left join product_mst as product on product.product_id=trns.product_id where trns.quot_trn_status=0 and trns.quotation_id=".$rel['quotation_id']." and trns.product_spec !=''";
		$ps = 1;
		$pr_se = $dbcon->query($pr_sp);
		$pr_cnt = brp_mysqli_num_rows($pr_se);
		while($sp_ro = mysqli_fetch_array($pr_se)){
			$html .= '<h3 style="text-decoration: underline; text-align: left; margin-top: 0px; "><strong>'.$ps.') '.remove_special_char($sp_ro['product_name']).'</strong></h3><br>'.(($sp_ro['product_spec']) ? remove_special_char($sp_ro['product_spec']) : '').'<br>';
			$html .= '';
			$ps++;
			$html.='<center class="nextpage"></center>';
		}
		
		////////////////////////////////////////// JS -///////////////////////////////////////////////////
		
		/* Check Annexure Attachments Start */
		if(trim($rel['quot_annex_content'])){
			
			$html.='<div class="quot_annex_content_div" style="font-size: 16px;">'.remove_special_char($rel['quot_annex_content']);
			$html.='</div>';
			$html.='<center class="nextpage"></center>';
		}
		/* Check Annexure Attachments End */
		
		////////////////////////////////////////////////////////////////////////////////////////////////
		
		$html .= '<div>
		<table style="font-size:16px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		
		<thead>
			<tr style="border-top:none;border-right:none;border-left:none;border-bottom:none">
				<th  style="text-align:left;">
					<p style="">
						Ref.:'.$rel['quotation_no'].'
						</p>
						<p>
						Date: '.date("d-M-Y",strtotime($rel['quotation_date'])).'
						</p>
						<br>
						<p style="float: left; width: 100%;text-transform: lowercase;text-transform: capitalize;">
						M/S : '.$rel['cust_name'].',<br/>'.$quot_address.'<br>';
						if(!empty($rel['c_con_fname']) || !empty($rel['c_con_lname'])){
							$html .= 'Kind Attn : '.$rel['c_con_fname'].' '.$rel['c_con_lname'].' <br>';
						}
						$html .='
						Mobile No : '.$rel['cust_mobile'].'<br>
					</p>
					Email Id : '.$rel['cust_email'].'
				</th>
				<th style="width:50%;">
				</th>
			</tr>

			<tr style="border-top:none;border-right:none;border-left:none">
				<th colspan="2" style="text-align:left;height:50px"></th>
			</tr>
		</thead>
		<tbody></table>';	
		
		$trn_qry="select trn.*,pro.product_name,unit.unit_name from tbl_quotation_trn as trn 
		left join product_mst as pro on pro.product_id=trn.product_id
		left join unit_mst as unit on unit.unitid=trn.unitid
		where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];

		$trn_qry_rs=$dbcon->query($trn_qry);
		$p=1;$ttl_amt=0;$ttl_qty=0;$pcount=1;
		$cnt=mysqli_num_rows($trn_qry_rs);

		while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)) {
			$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
			$gst_rate = $trn_rel['cgst_tax_rate_conv']+$trn_rel['sgst_tax_rate_conv']+$trn_rel['igst_tax_rate_conv'];

			if($trn_rel['cgst_tax_rate_conv'] != 0 || $trn_rel['sgst_tax_rate_conv'] !=0){
				$total_cs_gst += $gst_rate;
			}else{
				$total_i_gst += $gst_rate;
			}
			$total_gst +=$gst_rate; 
			$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';
			if($pcount=="1")
			{
				$html.='<div style="clear:both;"></div>
				<div>
				<table  style="font-size:14px;border-collapse: collapse;width:100% !important;table-layout:fix;" >
				
				<tr>
				<th style="width:2%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
				<th style="width:50%;text-align:center;border:1px solid;">Item Description</th>
				<th style="width:8%;text-align:center;border:1px solid;">Qty</th>
				<th style="width:10%;text-align:center;border:1px solid;">Unit Price <br> '.$rel['currency_code'].'</th>
				<th style="width:10%;text-align:center;border:1px solid;">Disc.% '.$rel['currency_code'].'</th>
				<th style="width:15%;text-align:center;border:1px solid;">Total Price '.$rel['currency_code'].'</th>
				</tr>';
			}
			$html.='<tr style="border:0px solid;border-left:1px solid;border-right:1px solid;">
			<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
			<td style="text-align:left;border:1px solid;vertical-align:top;">
			<strong>'.$trn_rel['product_name'].'</strong><br/>'.remove_special_char($product_desc).'
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			'.$trn_rel['product_qty'].'
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">';

			if($trn_rel['act_amt_flag']=='1'){
				$html.="Extra At Actual";
			} else {
				$html.=$rel['currency_symbol'].indian_number($trn_rel['product_rate_conv'],2);
			}
			$html.='</td>

			<td style="text-align:center;border:1px solid;vertical-align:top;">' . ($trn_rel['discount_per'] ? '('.$trn_rel['discount_per'].' %) '.$trn_rel['product_discount_conv'] : '(0 %) 0.00') .'</td>

			<td style="text-align:center;border:1px solid;vertical-align:top;">';
			if($trn_rel['act_amt_flag']=='1') {
				$html.="Included";
			} else {
				$html.=$rel['currency_symbol'].indian_number($trn_rel['product_total_conv'],2);
			}

			$html.='</td>
			</tr>';
			$ttl_qty=$ttl_qty+$trn_rel['product_qty'];

			if($trn_rel['act_amt_flag']!='1'){
				$ttl_amt=$ttl_amt+$trn_rel['product_total_conv'];
			}

			if($cnt==$p)
			{
					$html.='<tr style="">
					<td colspan="5" style="border-right:1px solid;height:25px;text-align:right"><strong>Basic Amount</strong></td>
					<td style="border:0;text-align:center"><strong>'.$rel['currency_symbol'].' '.indian_number($ttl_amt,2).'</strong></td>
					</tr>';

				// 	$html.='<tr>
				// 			<td colspan="5" style="text-align:right;border:1px solid;"><b>GST</b></td>
				// 			<td style="text-align:center;border:1px solid;"><b>
				// 			'.$rel['currency_symbol'].' '.indian_number(($total_gst),2).'
				// 			</b></td>
				// 		</tr>';
					
					$qry11="select sum((tc.tax_per*trn.product_amount_conv)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_quotation_trn as trn 
							left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
							left join tbl_ledger as l on l.l_id=tc.tax_id 
							where tc.tax_additional='1' and trn.quotation_id=".$rel['quotation_id']." and trn.quot_trn_status!=2 and tc.isdelete='0' group by tc.tax_id";

					$result11=$dbcon->query($qry11);		
					while($row11=mysqli_fetch_assoc($result11))
					{
						$html.='<tr>
							<td colspan="5" style="text-align:right;border:1px solid;"><b>'.$row11['l_name'].'</b></td>
							<td style="text-align:center;border:1px solid;"><b>
								'.$rel['currency_symbol'].' '.indian_number($row11['add_sum'],2).'
							</b></td>
						</tr>';
					}
					
					$qry12="select b.sundry_amount_conv,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
					from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
					left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
					where b.sundry_voucher_id=".$rel['quotation_id']." and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0'";

					$result12=$dbcon->query($qry12);		
					while($row12=mysqli_fetch_assoc($result12))
					{
						$html.='<tr>
							<td colspan="5" style="text-align:right;border:1px solid;"><b>'.$row12['l_name'].'</b></td>
							<td style="text-align:center;border:1px solid;"><b>
								'.$rel['currency_symbol'].' '.indian_number($row12['sundry_amount_conv'],2).'
							</b></td>
							</tr>';
					}

					$html.='<tr>
						<td colspan="5" style="text-align:right;border:1px solid;"><b>Total Amount
						</td>
						<td style="text-align:center;border:1px solid;"><b>
						'.$rel['currency_symbol'].' '.indian_number($ttl_amt,2).'
						</b></td>
					</tr>
					<tr>
						<td colspan="6" style="border:1px solid;text-align:right;"><b>Total (In Words): 
							'.(($comp_rel['currency_id']==$rel['currency_id']) ? convert_number_to_words_new($ttl_amt,$rel['currency_id'], $rel['currency_in_word_end'], $rel['currency_in_word']) : convert_number_to_words_new($ttl_amt,$rel['currency_id'],$rel['currency_in_word_end'], $rel['currency_in_word'])).'</b>
						</td>
					</tr>
					</table>
					<table  style="font-size:14px;border-collapse: collapse;width:100% !important;table-layout:fix;" >
						<tr style="border-bottom:none">
							<td style="width:50%;border-bottom:none;padding-top:0px;padding-bottom:0px"><p><strong>GSTIN: '.$comp_rel['vatno'].'</strong></p></td>
							<td style="width:50%;text-align:center;border-bottom:none;padding:0px">For, <strong>'.$comp_rel['company_name'].'</strong></td>
						</tr>
						<tr style="height:100px;border-top:none;border-bottom:none;">
							<td style="width:50%;height:100px;border-top:none;border-bottom:none;font-size:14px;padding-top:0px;padding-bottom:0px">
						<!--<strong>PAN: '.$comp_rel['pan_no'].'<br />
						IEC: '.$comp_rel['iec_no'].'<br />-->
						<table style="width:100%;border:none;padding:0px !important">
							<tr style="border:none;padding:0px !important">
								<td colspan="2" style="border:none;padding:0px !important"><span style="font-weight:bold;text-decoration: underline;font-size: 14px;"> Our Bankers</span></td>
							</tr>
							<tr style="border:none;padding:0px !important">
								<td style="border:none;padding:0px !important"><strong>Account Name </strong></td>
								<td style="border:none;padding:0px !important"> : ADK ENGINEERING & SOLUTIONS</td>
							</tr>
							<tr style="border:none;padding:0px !important">
								<td style="border:none;padding:0px !important"><span style="font-weight:bold;font-size: 14px"> Bank Name </span></td>
								<td style="border:none;padding:0px !important"> : <span style="font-size:14px;">'.$comp_rel['bank_name'].'</span></td>
							</tr>
							<tr style="border:none;padding:0px !important">
								<td style="border:none;padding:0px !important"><span style="font-weight:bold;font-size: 14px"> Bank Address </span></td>
								<td style="border:none;padding:0px !important"> : <span style="font-size:14px;">'.$comp_rel['branch_name'].'</span></td>
							</tr>
							<tr style="border:none;padding:0px !important">
								<td style="border:none;padding:0px !important"><span style="font-weight:bold;font-size: 14px"> A/c No. </span></td>
								<td style="border:none;padding:0px !important"><span style="font-size:14px;"> : '.$comp_rel['ac_no'].'</span></td>
							</tr>
							<tr style="border:none;padding:0px !important">
								<td style="border:none;padding:0px !important"><span style="font-weight:bold;font-size: 14px"> IFSC Code </span></td>
								<td style="border:none;padding:0px !important"><span style="font-size:14px;"> : '.$comp_rel['ifcs'].'</span></td>
							</tr>
						</table>

						<!--<strong>BANK DETAILS: <br /></strong>Bank Name: - '.$comp_rel['bank_name'].'<br/>
						Branch: - '.$comp_rel['branch_name'].' <br/>
						A/C. NO: - '.$comp_rel['ac_no'].'<br/>
						IFSC CODE: - '.$comp_rel['ifcs'].'<br />-->
						</td>
							<td style="width:50%;border-top:none;border-bottom:none;text-align:center;padding:0px">';
								if($comp_rel['authorized_signature']!=""){
									$html.='<img src="'.DOMAIN_F.'view/upload/signature/'.$comp_rel['authorized_signature'].'" style="height: 80px; width: 80px;"><br>';
								}
							$html.='</td>
						</tr>
						<tr style="border-top:none;">
							<td style="width:50%;border-top:none;padding-top:0px;padding-bottom:0px;vertical-align:top;">
							<!--<strong>Bank Name: - '.$comp_rel['bank_name'].', 
	Branch: - '.$comp_rel['branch_name'].' <br>
	A/C. NO: - '.$comp_rel['ac_no'].'</strong>--></td>
							<td style="width:50%;border-top:none;text-align:center;padding:0px;vertical-align:top"><!--<p>Regards<br>-->
								<!--<strong>'.$userData['user_name'] .'</strong> ('. $userData['usertype_name'].')<br>-->
								</p>
							</td>
						</tr>
					</table>
				</div>
				<div style="clear:both;"></div>';
			}
			///////////////////////////////////////////////////////////////////////////////////////
			
			$pcount++;
				if($pcount==8 && $cnt!=$p)
				{
					$pcount=1;
				$html.='
				<tr style="border-left:1px solid;border-right:1px solid;border-top:none;">
					<td style="text-align:center;border:1px solid;vertical-align:top;width:5% !important;border-top:none;"></td>
					<td style="text-align:left;border:1px solid;vertical-align:top;width:53% !important;border-top:none;"></td>
					<td style="text-align:center;border:1px solid;vertical-align:top;width:5% !important;border-top:none;"></td>
					<td style="text-align:center;border:1px solid;vertical-align:top;width:5% !important;border-top:none;"></td>
					<td style="text-align:center;border:1px solid;vertical-align:top;width:5% !important;border-top:none;"></td>
			
					</tr>
				</table>
				</div>
				<center class="nextpage"></center>
					<div style="clear:both;"></div>';
				
				}
			$p++;
		}

		$html.='</tbody>
		</table></div>
		';

		/* Get Terms And Condition Start */
		$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
		left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
		where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
		$terms_qry_rs=$dbcon->query($terms_qry);$tc = 1;

		if(mysqli_num_rows($terms_qry_rs)){
			$html.='<center class="nextpage"></center>
		<div>
		<!--<table width="100%" style="border-collapse: collapse;width:100%;overflow:wrap; " cellpadding="3" cellspacing="3">
			
			<tr style="border: 0; ">
			<td style="border: 0; padding-top: 20px; width : 100%;" colspan="3">
			<center><strong>TERMS & CONDITIONS</strong></center>
			</td>
			</tr>-->

			<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<thead>
				<tr style="border: 0; ">
					<td style="border: 0; padding-top: 20px; width : 100%;" colspan="3">
					<center><strong>TERMS & CONDITIONS</strong></center>
					</td>
				</tr>
				<tr>
					<th style="text-align:center; border:1px solid;">Sr.<br/>No.</th>
					<th style="text-align:left; border:1px solid;">Terms and Condition</th>
					<th style="text-align:left; border:1px solid;">Description</th>
				</tr>
				</thead><tbody>';
			while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
				$string=(nl2br($term_rel['tc_details']));
				$html.='<tr>
					<td width="5%" style="width:5%;text-align:center;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;">'.$tc.'</td>
					<td width="25%" style="width:25%;text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;">'.remove_special_char($term_rel['tc_name']).'</td>
					<td width="70%" style="width:70%;text-align:left;border:1px solid #000;padding:5px;">'.remove_special_char($string).'</td>
					</tr>';
					
				$tc++;
			}
			$html.='
			</table>';	
			$html.='</div>';
		}
		/* Check Annexure Attachments End */

		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
		ob_end_clean();

		// JS
		$file_name = $rel['quotation_no'].'.pdf';
		$file_name=str_ireplace("/","_",$file_name);
		if($save_file=="No") {
			include("../../view/export/mpdf/mpdf.php");
		} else {
			include("../../../view/export/mpdf/mpdf.php");
		}

		// include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','proximanova','10','10','45','30','1','1');
		//		$mdf->SetFont('ProximaNova');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='UTF-8';
		$mpdf->WriteHTML(utf8_encode($html));
		//$mpdf->WriteHTML($html);

		if($save_file=="No"){
			$mpdf->Output();
		}else{
			$mpdf->Output('../../../view/upload/mail_attach/'.$file_name,'f');
		}
				//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
		ob_clean();
		return $file_name;
	}
}

function remove_special_char($originalString) {

	// Convert encoding to UTF-8
	$htmlContent = html_entity_decode($originalString);
	// Remove special characters from HTML content
	$cleanedContent = preg_replace('/[^\x20-\x7E]/u', '', $htmlContent);
	
	return $cleanedContent;

}

?>