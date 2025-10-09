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
		$query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name,cust.cust_gst, cust.cust_email, cust.cust_mobile,cust.cust_iec,cust.cust_pan, inq.inquiry_no,inq.inquiry_date, ref.rb_name, cadd.c_add_state,cadd.c_add_address,cur.currency_in_word,cur.currency_in_word_end from tbl_quotation as quot
		left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
		left join tbl_customer as cust on cust.cust_id=quot.cust_id
		left join tbl_cust_address as cadd on cadd.cust_id=quot.cust_id
		left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
		left join tbl_currency as cur on cur.currency_id = quot.currency_id
		left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
		where quot.quotation_id=".$quotation_id;
		$rel=mysqli_fetch_assoc($dbcon->query($query));
//p($rel);
		if(!$rel){
			header("Location: ".ROOT.CRM_ROOT."quotation_list");
		}

		if($rel['quot_type']=='0'){
			$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$_SESSION['currency_id'].'" ';
			$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));
			$currency_symbol = $currency_rel['currency_symbol'];
			$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
			$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
			$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
		}else{
			$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
			$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));
			$currency_symbol = $currency_rel['currency_symbol'];
			$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
			$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
			$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
		}
		$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';
		$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];

		$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
		$header ='<div style="text-align:center;"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:8.27in;" /></div>';
		$footer ='<div style="text-align:center;"><img src="'.DOMAIN_F.LOGO.$comp_rel["f_logo"].'" style="width:8.27in;" /></div>';
		$approve_status='';
		if($rel['approve_status']=='0'){
			$approve_status=' (DRAFT)';
		}
		$inquiry_type=$rel['inquiry_type'];

		$quotation_date='';

		if($rel['quotation_date']!="1970-01-01" && $rel['quotation_date']!="0000-00-00")
		{
			$quotation_date=date('d M Y',strtotime($rel['quotation_date']));
		}
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
		$disc_qry=$dbcon->query("SELECT SUM(trn.product_discount) as discount from tbl_quotation_trn as trn where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id']);
		$disc_qrys = brp_mysqli_fetch_assoc($disc_qry);
		if($companyConfiguration['quot_revise_time_rate_with_discount'] == 0){
			$colspan =($disc_qrys['discount'] > 0) ? 5 : 4;	
		}else{
			$colspan = 4;
		}
		
//Amish Soni End 16-03-2021
		$html1='<html>
		<head></head>
		<body>
			<div>
			<img src="'.DOMAIN_F.LOGO.'first_page.jpg" style="width:100%;height:100%" />
				
			</div>
		</body>
		</html>';
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
				page-break-inside:avoid !important;
				page-break-after:avoid !important;
				page-break-before:avoid !important;
			}
			.quot_annex_content_div table tr,td{
				padding:5px;
			}
			.blueHeading {
				color: #365f91;
			}

			
			</style>
			</head>
			<body>';
			

			$html .='<!--Show Logo in other pages-->
			<htmlpageheader name="otherpages" style="display:none">
			<div style="text-align:center">'.$header.'</div>
			</htmlpageheader>
			<!--<htmlpagefooter name="otherpages_footer" style="display:none">
			<div style="text-align:center">'.$footer.'</div>-->
			</htmlpagefooter>

			<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
			<div>
			<table cellpadding="5" cellspacing="5" border="0" style="font-size: 14x; font-family: Proxia Nova; border: 0;">
			<tr style="border: 0;">
			<td style="border: 0; text-align: center; font-size: 36px;">SALES OFFER</td>
			</tr>

			<tr style="border: 0;">
			<td style="border: 0; text-align: center; font-size: 18px;">NO. : '.$rel['quotation_no'].'</td>
			</tr>

			<tr style="border: 0;">
			<td style="border: 0; text-align: center; font-size: 18px;">DATE : '.$quotation_date.'</td>
			</tr>
			</table>
			<table cellpadding="5" cellspacing="5" border="0" style="font-size: 16px; font-family: Proxia Nova; border: 0;">
			<tr style="border: 0;">
			<td style="border: 0;width:20%">
				FROM 			
			</td>
			<td style="border: 0;width:80%">
				 :  '.$comp_rel['company_name'].'			
			</td>
			</tr>
			<tr style="border: 0;height : 30px">
				<td colspan="2"></td>
			</tr>
			<tr style="border: 0;">
			<td style="border: 0;width:20%">
				TO 			
			</td>
			<td style="border: 0;width:80%">
				 :  '.$rel['cust_name'].'			
			</td>
			</tr>
			<tr style="border: 0;height : 30px">
				<td colspan="2"></td>
			</tr>
			<tr style="border: 0;">
			<td style="border: 0;width:20%">
				ADD 			
			</td>
			<td style="border: 0;width:80%">
				 : '.$rel['c_add_address'].' 			
			</td>
			</tr>
			<tr style="border: 0;height : 30px">
				<td colspan="2"></td>
			</tr>
			<tr style="border: 0;">
			<td style="border: 0;width:20%">
				CO. PERSON 			
			</td>
			<td style="border: 0;width:80%">
				 :  '.$rel['c_con_fname'].'	'.$rel['c_con_lname'].'		
			</td>
			</tr>
			<tr style="border: 0;height : 30px">
				<td colspan="2"></td>
			</tr>
			<tr style="border: 0;">
			<td style="border: 0;width:20%">
				IEC NO. 			
			</td>
			<td style="border: 0;width:80%">
				 : 	'.$rel['cust_iec'].'		
			</td>
			</tr>
			<tr style="border: 0;height : 30px">
				<td colspan="2"></td>
			</tr>
			<tr style="border: 0;">
			<td style="border: 0;width:20%">
				GST NO. 			
			</td>
			<td style="border: 0;width:80%">
				 :  '.$rel['cust_gst'].'			
			</td>
			</tr>
			<tr style="border: 0;height : 30px">
				<td colspan="2"></td>
			</tr>
			<tr style="border: 0;">
			<td style="border: 0;width:20%">
				PAN 			
			</td>
			<td style="border: 0;width:80%">
				 : 	'.$rel['cust_pan'].'		
			</td>
			</tr>
			<tr style="border: 0;height : 30px">
				<td colspan="2"></td>
			</tr>
			<tr style="border: 0;">
			<td style="border: 0;width:20%">
				EMAIL 			
			</td>
			<td style="border: 0;width:80%">
				 :  '.$rel['cust_email'].'			
			</td>
			</tr>
			<tr style="border: 0;height : 30px">
				<td colspan="2"></td>
			</tr>
			<tr style="border: 0;">
			<td style="border: 0;width:20%">
				SUPPLY SCOPE 			
			</td>
			<td style="border: 0;width:80%">
				 :  '.$rel['quot_subject'].'			
			</td>
			</tr>
			<tr style="border: 0;height : 30px">
				<td colspan="2"></td>
			</tr>
			<tr style="border: 0;">
			<td style="border: 0;width:20%">
				PRODUCTION UP TO 			
			</td>
			<td style="border: 0;width:80%">
				 :  '.$rel['production_up_to'].'			
			</td>
			</tr>
			</table>
			</div>
			<center class="nextpage"></center>
			<div>
			<table style="font-size:14px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
			<tr style="border-left:none;border-right:none">
				<td style="font-size:18px;text-align:left;border-left:none;border-right:none"><strong>1. PROFORMA INVOICE</strong></td>
			</tr>
			</table>
			<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
			<thead>
			<tr>
			<th style="text-align:center;border:1px solid;">Sr.<br/>No.</th>
			<th style="text-align:center;border:1px solid;">Group Of Commodity</th>
			<th style="text-align:center;border:1px solid;">Item Code</th>
			<th style="text-align:center;border:1px solid;">Name Of Commodity</th>
			<!--<th style="text-align:center;border:1px solid;">Specification</th>-->
			<th style="text-align:center;border:1px solid;">Hsncode</th>
			<th style="text-align:center;border:1px solid;">Unit</th>
			<th style="text-align:center;border:1px solid;">Qty</th>
			<th style="text-align:center;border:1px solid;">Unit Price <br>'.$currency_name.'</th>
			<th style="text-align:center;border:1px solid;">Sub Total <br>'.$currency_name.'</th>
			<th style="text-align:center;border:1px solid;">Group Total <br>'.$currency_name.'</th>
			</tr>
			</thead>
			<tbody>';

			$cat_q = "select sum(product_amount_conv) as group_total,cat.cat_name,inq.cat_id from tbl_quotation_trn as inq 
				left join tbl_category as cat on cat.cat_id=inq.cat_id
				where quot_trn_status=0 and quotation_id=".$rel['quotation_id']." group by cat_id order by cat_id";

			$result_cat = $dbcon->query($cat_q);

			$i = 1;

			while($row_cat = brp_mysqli_fetch_array($result_cat)){

				if($inquiry_type!="2"){
				 	$trn_qry="SELECT trn.*,pro.product_name,unit.unit_name, pro.product_icode,hsn.hsn_code,trn.cat_id FROM tbl_quotation_trn as trn 
					left join product_mst as pro on pro.product_id=trn.product_id
					left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
					left join unit_mst as unit on unit.unitid=trn.unitid
					where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id']." and trn.cat_id=".$row_cat['cat_id']." order by quot_trn_id asc";
				} else {
					  $trn_qry="SELECT trn.* , pro.product_name,pro.product_icode,hsn.hsn_code,trn.product_category_id as cat_id FROM `tbl_quotation_project_trn` as trn 
					  left join product_mst as pro on pro.product_id = trn.product_id 
					  left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
					  where trn.quotation_projecttrn_status=0 and trn.quotation_id =".$rel['quotation_id']." and trn.product_category_id=".$row_cat['cat_id'];
				}
				$cnt =0;
				$trn_qry_rs=$dbcon->query($trn_qry);
				$cnt=mysqli_num_rows($trn_qry_rs);
				
				$j=1;
				while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
					
					/*$item_code = '';
					if(in_array('item',$sales_pro_search)){
						$item_code = " -- (".$trn_rel['product_icode'].")";
					}*/
					$gst_rate = $trn_rel['cgst_tax_rate_conv']+$trn_rel['sgst_tax_rate_conv']+$trn_rel['igst_tax_rate_conv'];

					if($trn_rel['cgst_tax_rate_conv'] != 0 || $trn_rel['sgst_tax_rate_conv'] !=0){
						$total_cs_gst += $gst_rate;
					}else{
						$total_i_gst += $gst_rate;
					}
					if($row_cat['cat_id']==0){
						$row_cat['cat_name'] = 'PRIMARY';
					}
					$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';
					if($j==1){
						//rowspan="'.$cnt.'"
					$html.='<tr style="border:1px solid;border-left:1px solid;border-right:1px solid;">
					<td style="text-align:center;border:1px solid;vertical-align:top;">'.$i.'</td>
					<td rowspan="'.$cnt.'" style="text-align:center;border:1px solid;vertical-align:center !important;border-bottom:1px solid">
						'.$row_cat['cat_name'].'
					</td>
					<td style="text-align:left;border:1px solid;vertical-align:center;">
						'.$trn_rel['product_icode'].'
					</td>
					<td style="text-align:left;border:1px solid;vertical-align:center;">
					'.$trn_rel['product_name'].'
					</td>
					<!--<td style="text-align:center;border:1px solid;vertical-align:center;">
					'.$trn_rel['product_desc'].'
					</td>-->
					<td style="text-align:center;border:1px solid;vertical-align:center;">
					'.$trn_rel['hsn_code'].'
					</td>
					<td style="text-align:center;border:1px solid;vertical-align:center;">
					'.$trn_rel['unit_name'].'
					</td>
					<td style="text-align:center;border:1px solid;vertical-align:center;">
					'.indian_number($trn_rel['product_qty'],2).'
					</td>

					<td style="text-align:center;border:1px solid;vertical-align:center;">'.$currency_symbol.' '.indian_number($trn_rel['product_rate_conv'],2).'</td>
					<td style="text-align:center;border:1px solid;vertical-align:center;">'.$currency_symbol.' '.indian_number($trn_rel['product_amount_conv'],2).'</td>
					<td rowspan="'.$cnt.'" style="text-align:center;border:1px solid;vertical-align:center !important;">'.$currency_symbol.' '.indian_number($row_cat['group_total'],2).'</td>
					</tr>';
					}else{
					$html.='<tr style="border:1px solid;border-left:1px solid;border-right:1px solid;">
					<td style="text-align:center;border:1px solid;vertical-align:top;">'.$i.'</td>
					
					<td style="text-align:left;border:1px solid;vertical-align:center;">
						'.$trn_rel['product_icode'].'
					</td>
					<td style="text-align:left;border:1px solid;vertical-align:center;">
					'.$trn_rel['product_name'].'
					</td>
					<!--<td style="text-align:center;border:1px solid;vertical-align:center;">
					'.$trn_rel['product_desc'].'
					</td>-->
					<td style="text-align:center;border:1px solid;vertical-align:center;">
					'.$trn_rel['hsn_code'].'
					</td>
					<td style="text-align:center;border:1px solid;vertical-align:center;">
					'.$trn_rel['unit_name'].'
					</td>
					<td style="text-align:center;border:1px solid;vertical-align:center;">
					'.indian_number($trn_rel['product_qty'],2).'
					</td>

					<td style="text-align:center;border:1px solid;vertical-align:center;">'.$currency_symbol.' '.indian_number($trn_rel['product_rate_conv'],2).'</td>
					<td style="text-align:center;border:1px solid;vertical-align:center;">'.$currency_symbol.' '.indian_number($trn_rel['product_amount_conv'],2).'</td>
					
					</tr>';

					}
					

					$i++;$j++;
				}
				$ttl_amt = 	$ttl_amt + $row_cat['group_total'];
			}
			/*echo $html;exit;*/
			 $bill1_sun=$dbcon->query("select b.*,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id from tbl_bill_sundry_transaction as b 

			left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
			left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 

			where b.sundry_voucher_id=".$rel['quotation_id']." and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0' ");
			while($bill_sundry1 = brp_mysqli_fetch_array($bill1_sun)){
				$total_cs_gst  += $bill_sundry1['sundry_gst_amount_conv'];
				$total_i_gst  += $bill_sundry1['sundry_gst_amount_conv'];
			}
			
			$html.='<tr>
				<td colspan="9" style="text-align:right;border:1px solid;vertical-align:center;"><strong>Total Amount</strong></td>
				<td style="text-align:center;border:1px solid;vertical-align:center;white-space:nowrap">'.$currency_symbol.' '.indian_number($ttl_amt,2).'</td>
			</tr>';
			if($rel['quot_type']=='0'){
				if($rel['c_add_state']==$comp_rel['stateid']){
					$html.='<tr>
					<td colspan="9" style="text-align:right;border:1px solid;"><b>CGST</b></td>
					<td style="text-align:center;border:1px solid;"><b>
					'.$currency_symbol.' '.indian_number(($total_cs_gst/2),2).'
					</b></td>
					</tr>
					<tr>
					<td colspan="9" style="text-align:right;border:1px solid;"><b>SGST</b></td>
					<td style="text-align:center;border:1px solid;"><b>
					'.$currency_symbol.' '.indian_number(($total_cs_gst/2),2).'
					</b></td>
					</tr>';
				}else{
					$html.='<tr>
					<td colspan="9" style="text-align:right;border:1px solid;"><b>IGST</b></td>
					<td style="text-align:center;border:1px solid;"><b>
					'.$currency_symbol.' '.indian_number(($total_i_gst),2).'
					</b></td>
					</tr>';
				}
			}
			$qry11="select sum((tc.tax_per*trn.product_amount_conv)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_quotation_trn as trn 
			left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
			left join tbl_ledger as l on l.l_id=tc.tax_id 
			where tc.tax_additional='1' and trn.quotation_id=".$rel['quotation_id']." and trn.quot_trn_status!=2 and tc.isdelete='0' group by tc.tax_id";
			$result11=$dbcon->query($qry11);		
			while($row11=mysqli_fetch_assoc($result11))
			{
				$html.='<tr>
				<td colspan="9" style="text-align:right;border:1px solid;"><b>'.$row11['l_name'].'</b></td>
				<td style="text-align:center;border:1px solid;"><b>
				'.$currency_symbol.' '.indian_number($row11['add_sum'],2).'
				</b></td>
				</tr>';
			}
			$qry12="select b.sundry_amount_conv as sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
			from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
			left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
			where b.sundry_voucher_id=".$rel['quotation_id']." and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0'";
			$result12=$dbcon->query($qry12);		
			while($row12=mysqli_fetch_assoc($result12))
			{
				$html.='<tr>
				<td colspan="9" style="text-align:right;border:1px solid;"><b>'.$row12['l_name'].'</b></td>
				<td style="text-align:center;border:1px solid;"><b>
				'.$currency_symbol.' '.indian_number($row12['sundry_amount'],2).'
				</b></td>
				</tr>';
			}
			$html.='<tr>
			<td colspan="9" style="text-align:right;border:1px solid;"><b>Grand Total
			</td>
			<td style="text-align:center;border:1px solid;"><b>
			'.$currency_symbol.' '.indian_number($rel['g_total_conv'],2).'
			</b></td>
			</tr>
			
			<tr>
				<td colspan="10" style="text-align:left;border:1px solid;"><b>Total Amount In Word : </b> '.convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$rel['currency_in_word_end'],$rel['currency_in_word']).'</td>
			</tr>
			<tr>';
			/*$html.='<td colspan="19" style="border:1px solid;text-align:right;"><b>Total (In Words): 
			'.(($comp_rel['currency_id']==$rel['currency_id']) ? ucfirst(convert_number_to_words_new($rel['g_total'],$currency_word_start,$currency_word_end)) : ucfirst(convert_number_to_words($rel['g_total_conv'],$currency_word_start,$currency_word_end))).'</b></td></tr>';*/
			/*$pr=10-$cnt;
			for($j=0; $j<$pr; $j++)
			{
				$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
				<td style="border:none;border-left:1px solid;border-right:1px solid;height:40px;"></td>
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>';
				if($companyConfiguration['quot_revise_time_rate_with_discount']==0){
					if($disc_qrys['discount'] > 0){
						$html.='<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>';
					}
				}
				$html.='
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
				</tr>';
			}*/

			/*$html.='<tr>
			<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>Basic Amount</b></td>
			<td style="text-align:center;border:1px solid;"><b>
			'.indian_number($ttl_amt,2).'
			</b></td>
			</tr>';
			if($rel['c_add_state']==$comp_rel['stateid']){
				$html.='<tr>
				<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>CGST</b></td>
				<td style="text-align:center;border:1px solid;"><b>
				'.number_format(($total_cs_gst/2),2,".","").'
				</b></td>
				</tr>
				<tr>
				<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>SGST</b></td>
				<td style="text-align:center;border:1px solid;"><b>
				'.number_format(($total_cs_gst/2),2,".","").'
				</b></td>
				</tr>';
			}else{
				$html.='<tr>
				<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>IGST</b></td>
				<td style="text-align:center;border:1px solid;"><b>
				'.number_format(($total_i_gst),2,".","").'
				</b></td>
				</tr>';
			}
			$qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_quotation_trn as trn 
			left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
			left join tbl_ledger as l on l.l_id=tc.tax_id 
			where tc.tax_additional='1' and trn.quotation_id=".$rel['quotation_id']." and trn.quot_trn_status!=2 and tc.isdelete='0' group by tc.tax_id";
			$result11=$dbcon->query($qry11);		
			while($row11=mysqli_fetch_assoc($result11))
			{
				$html.='<tr>
				<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>'.$row11['l_name'].'</b></td>
				<td style="text-align:center;border:1px solid;"><b>
				'.number_format($row11['add_sum'],2,".","").'
				</b></td>
				</tr>';
			}
			$qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
			from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
			left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
			where b.sundry_voucher_id=".$rel['quotation_id']." and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0'";
			$result12=$dbcon->query($qry12);		
			while($row12=mysqli_fetch_assoc($result12))
			{
				$html.='<tr>
				<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>'.$row12['l_name'].'</b></td>
				<td style="text-align:center;border:1px solid;"><b>
				'.number_format($row12['sundry_amount'],2,".","").'
				</b></td>
				</tr>';
			}
			$html.='<tr>
			<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>Total Amount
			</td>
			<td style="text-align:center;border:1px solid;"><b>
			'.indian_number($rel['g_total'],2).'
			</b></td>
			</tr>
			<tr>
			<td colspan="'.($colspan+1).'" style="border:1px solid;text-align:right;"><b>Total (In Words): 
			'.(($comp_rel['currency_id']==$rel['currency_id']) ? ucfirst(convert_number_to_words_new($rel['g_total'],$currency_word_start,$currency_word_end)) : ucfirst(convert_number_to_words($rel['g_total'],$currency_word_start,$currency_word_end))).'</b></td></tr>';
			$html.='
			<tr>
			<td colspan="'.($colspan+1).'" style="border:1px solid;text-align:left;"><b>Remarks:</b> 
			'.(($rel['quot_remark']) ? $rel['quot_remark'] : '').'</td></tr>';*/

			$html.='</tbody></table></div>';

			/* Get Terms And Condition Start */
			$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
			left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
			where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
			$terms_qry_rs=$dbcon->query($terms_qry);
			if(mysqli_num_rows($terms_qry_rs)){
				$t=1;
				$html.='<center class="nextpage"></center><div>
				<table style="font-size:14px;border-collapse: collapse;width:100%;border:none" cellpadding="5" cellspacing="5">
				<thead>
				<tr style="border:none">
					<th colspan="3" style="text-align:left; border:none;font-size:18px"> 2. SALES TERMS</th>
				</tr>
				<tr style="border:none">
				<th style="text-align:center; border:none;"></th>
				<th style="text-align:left; border:none;"></th>
				<th style="text-align:left; border:none;"></th>
				</tr>
				</thead><tbody>';
				while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
					$string=(nl2br($term_rel['tc_details']));
					$html.='<tr style="border:none">
					<td width="5%" style="width:5%;text-align:center;border:none;padding:5px; font-weight: bold; vertical-align: top;">'.$t.'</td>
					<td width="25%" style="width:25%;text-align:left;border:none;padding:5px; font-weight: bold; vertical-align: top;">'.$term_rel['tc_name'].'</td>
					<td width="70%" style="width:70%;text-align:left;border:none;padding:5px;">'.$string.'</td>
					</tr>';
					$t++;
				}
				$html.='<tr style="border:none">
					<td colspan="3" style="height:50px"> </td>
				</tr>
				<tr style="border:none">
					<td colspan="2" style="text-align:left;font-weight:bold;border:none;font-size:18px">Buyer: '.$rel['cust_name'].'</td>
					<td style="text-align:right;font-weight:bold;border:none;font-size:18px">Seller : '.$comp_rel['company_name'].'</td>
				</tr>
				<tr style="border:none;">
					<td colspan="2" style="text-align:left;font-weight:bold;border:none;font-size:18px;height:70px"></td>
					<td style="text-align:right;font-weight:bold;border:none;font-size:18px">
						<img src="'.DOMAIN_F.'view/upload/signature/'.$comp_rel['authorized_signature'].'" style="height: 100px; width: 180px;"><br>
						Authorized Signatory
					</td>
				</tr>
				</tbody></table>'; 
			}

			$html.='<center class="nextpage"></center><div>
				<span style="font-size:18px"><strong>3. Technical Description</strong></span>
				<table style="font-size:14px;border-collapse: collapse;width:100%;border:1px solid" cellpadding="5" cellspacing="5">
				
				<thead>
					<tr>
						<th style="text-align:center;border:1px solid;width:5%">No.</th>
						<th style="text-align:center;border:1px solid;width:35%">Photo</th>
						<th style="text-align:center;border:1px solid;width:60%">Description</th>
					</tr>
				</thead>
				<tbody>';

			if($inquiry_type!="2"){
			 	$pro_im="SELECT trn.*,pro.product_name, pro.product_icode,hsn.hsn_code,cat.cat_name,pro.image_name,trn.cat_id FROM tbl_quotation_trn as trn 
				left join product_mst as pro on pro.product_id=trn.product_id
				left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
				left join tbl_category as cat on cat.cat_id=trn.cat_id
				where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id']." order by trn.cat_id,trn.quot_trn_id asc";
			} else {
				  $pro_im="SELECT trn.* , pro.product_name,pro.product_icode,hsn.hsn_code,cat.cat_name,pro.image_name,trn.product_category_id as cat_id  FROM `tbl_quotation_project_trn` as trn 
				  left join product_mst as pro on pro.product_id = trn.product_id 
				  left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
				  left join tbl_category as cat on cat.cat_id=trn.cat_id
				  where trn.quotation_projecttrn_status=0 and trn.quotation_id =".$rel['quotation_id'];
			}

			$pro_im_rs=$dbcon->query($pro_im);
			$i=1;
			while($row_im=brp_mysqli_fetch_array($pro_im_rs)){
				if($row_im['cat_id']==0){
					$row_im['cat_name'] = 'PRIMARY';
				}
				$html.='<tr>
					<td rowspan="3">'.$i.'</td>
					<td rowspan="3"><img src="'.ROOT.'view/upload/product_images/'.$row_im['image_name'].'" style="width:250px;height:250px"></td>
					
					<td style="height:35px"><strong>'.$row_im['cat_name'].'</strong></td>
				</tr>
				<tr>
					<td style="height:35px"><strong>'.$row_im['product_name'].'</strong></td>
				</tr>
				<tr>
					<td>'.$row_im['product_spec'].'</td>
				</tr>';
				$i++;
			}
				

			$html.='</tbody>
			</table>';
			/* Check Annexure Attachments Start */
			if(trim($rel['quot_annex_content'])){
				$html.='<center class="nextpage"></center>';
				$html.='<div class="quot_annex_content_div" style="font-size: 16px;">'.$rel['quot_annex_content'];

				$html.='</div><table style="font-size:14px;border-collapse: collapse;width:100%;border:none" cellpadding="5" cellspacing="5">
					<tr style="border:none">
						<td colspan="4" style="height:50px"> </td>
					</tr>
					<tr style="border:none">
						<td colspan="2" style="text-align:left;font-weight:bold;border:none;font-size:18px;width:50%">Buyer: '.$rel['cust_name'].'</td>
						<td colspan="2" style="text-align:right;font-weight:bold;border:none;font-size:18px;width:50%">Seller : '.$comp_rel['company_name'].'</td>
					</tr>
					<tr style="border:none;">
						<td colspan="2" style="text-align:left;font-weight:bold;border:none;font-size:18px;height:70px"></td>
						<td colspan="2" style="text-align:right; font-weight:bold; border:none; font-size:18px">
							<img src="'.DOMAIN_F.'view/upload/signature/'.$comp_rel['authorized_signature'].'" style="height: 100px; width: 180px;"><br>
							Authorized Signatory
						</td>
					</tr>
				</table>
				
				';
			}
			/* Check Annexure Attachments End */
			/*if(!empty($quotation_footer_content)){
				$html.='<br /><br /><div>'.$quotation_footer_content;
				$html.='</div>';
			}*/
			
			$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
			</body>
			</html>';
 //echo $trn_qry;
//echo $html;exit;
			ob_end_clean();
			$file_name = $rel['quotation_no'].'.pdf';
			$file_name=str_ireplace("/","_",$file_name);
			if($save_file=="No"){
				include("../../view/export/mpdf/mpdf.php");
			}else{
				include("../../../view/export/mpdf/mpdf.php");
			}
			//echo '{PAGENO}{nbpg}';exit;
			/*$mpdf->AddPageByArray([
			    'margin-left' => 0,
			    'margin-right' => 0,
			    'margin-top' => 0,
			    'margin-bottom' => 0,
			]);*/

			//$mpdf->WriteHTML($html1);
			
			//$mpdf=new mPDF('','A4','0','calibri','10','10','50','25','1','1');
			$mpdf=new mPDF('','A4','0','calibri');
//		$mdf->SetFont('ProximaNova');
			//$mpdf->AddPage('','E');
$mpdf->AddPageByArray([
    'margin-left' => 0,
    'margin-right' => 0,
    'margin-top' => 0,
    'margin-bottom' => 0,
]);
			//set your header firstpage
 $mpdf->SetHTMLHeader($header);
//write a space 
$mpdf->WriteHTML($html1);
$mpdf->AddPageByArray([
    'margin-left' => '10',
    'margin-right' => '10',
    'margin-top' => '50',
    'margin-bottom' => '25',
]);

			$mpdf->defaultheaderfontsize = 10; /* in pts */
			$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
			$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
			$mpdf->defaultfooterfontsize = 10; /* in pts */
			$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
			$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
			$mpdf->SetHTMLHeader($header);
			$mpdf->SetHTMLFooter();
//Show page number : Dimple Panchal (05-Apr-2021)
			$mpdf->pagenumPrefix = ' ';
			$mpdf->pagenumSuffix = ' / ';
			$mpdf->nbpgPrefix = ' ';
			$mpdf->nbpgSuffix = ' pages';
			$mpdf->SetFooter('{PAGENO}{nbpg}');
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
