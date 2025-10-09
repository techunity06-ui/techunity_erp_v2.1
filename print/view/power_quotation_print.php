<?php
require_once '../../vendor/autoload.php';
use Mpdf\Mpdf;
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
function quotation_print($dbcon,$quotation_id,$save_file = "yes"){
	// error_reporting(E_ALL);
// $quotation_id = $_REQUEST['id'];	
	$type='pdf';
	if(strtolower($type) == 'pdf') {
//Quotation Data
		$query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_gst, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name, cadd.c_add_state from tbl_quotation as quot
		left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
		left join tbl_customer as cust on cust.cust_id=quot.cust_id
		left join tbl_cust_address as cadd on cadd.cust_id=quot.cust_id
		left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
		left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
		where quot.quotation_id=".$quotation_id;
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		// echo "<pre>";print_r($rel);die();
		$quotation_footer_content = $rel['quot_footer'] ? $rel['quot_footer'] : '';
		$quotation_footer_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_footer_content);
		
// p($rel);
		if(!$rel){
			header("Location: ".ROOT.CRM_ROOT."quotation_list");
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
		$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];

		$comp_rel=mysqli_fetch_assoc($dbcon->query($set));

		$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
		WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
		$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
		$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
		$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';
		
		$header ='<div style="text-align:center;"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:8.27in;" /></div>';
		$footer ='<div style="text-align:center;"><img src="'.DOMAIN_F.LOGO.$comp_rel["f_logo"].'" style="width:8.27in;" /></div>';
		$html = '';
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
			
		}
		$disc_qry=$dbcon->query("SELECT SUM(trn.product_discount_conv) as discount from tbl_quotation_trn as trn where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id']);
		$disc_qrys = brp_mysqli_fetch_assoc($disc_qry);
		if($companyConfiguration['quot_revise_time_rate_with_discount'] == 0){
			$colspan =($disc_qrys['discount'] > 0) ? 6 : 5;	
		}else{
			$colspan = 5;
		}
	
//Amish Soni End 16-03-2021
		$html .='<html>
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
			<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
			
			<div>
			<table style="font-size:14px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
			<tr>
			<td rowspan="6" style="text-align:left;vertical-align:top;border:1px solid;width:50%;"> 
			<strong>To,<br>'.$rel['cust_name'].'</strong><br/>
			'.($quot_address).'</strong><br>
			GSTIN : <strong>'.$rel['cust_gst'].'</strong><br>
			Kind Attn : <strong>'.$rel['c_con_fname'].' '.$rel['c_con_lname'].'<br />
			</td>
			<td style="text-align:left;border:1px solid;width:20%;"> 
			Quotation No
			</td>
			<td style="text-align:left;border:1px solid;width:30%;"> 
			<strong>'.$rel['quotation_no'].'</strong>
			</td>
			</tr>
			<tr>
			<td style="text-align:left;border:1px solid;"> 
			Quotation Date
			</td>
			<td style="text-align:left;border:1px solid;"> 
			'.date("d-M-Y",strtotime($rel['quotation_date'])).'
			</td>
			</tr>

			<tr>
			<td style="text-align:left;border:1px solid;"> 
			Inquiry Ref No
			</td>
			<td style="text-align:left;border:1px solid;"> 
			'.$rel['inquiry_no'].'
			</td>
			</tr>
			<tr>
			<td style="text-align:left;border:1px solid;"> 
			Inquiry Ref Date
			</td>
			<td style="text-align:left;border:1px solid;"> 
			'.date("d-M-Y",strtotime($rel['inquiry_date'])).'
			</td>
			</tr>
			<tr>
			<td style="text-align:left;border:1px solid;"> 
			Executive Name
			</td>
			<td style="text-align:left;border:1px solid;"> 
			'.$userData['user_name'].'
			</td>
			</tr>
			<tr>
			<td style="text-align:left;border:1px solid;"> 
			Valid Till
			</td>
			<td style="text-align:left;border:1px solid;"> 
			'.date("d-M-Y",strtotime($rel['quotation_valid_date'])).'
			</td>
			</tr>
			</table>
			<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
			<thead>
			
			<tr>
			<th style="width:5%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
			<th style="width:7%;text-align:center;border:1px solid;">Item ID <br> / Hsn Code</th>
			<th style="width:51%;text-align:center;border:1px solid;">Specification</th>
			<th style="width:5%;text-align:center;border:1px solid;">Qty</th>
			<th style="width:10%;text-align:center;border:1px solid;">Rate <br>'.$currency_name.'</th>';
			if($companyConfiguration['quot_revise_time_rate_with_discount']==0){
				if($disc_qrys['discount'] > 0){
					$html.='<th style="width:10%;text-align:center;border:1px solid;">Discount <br>'.$currency_name.'</th>';
				}
			}
			$html.='<th style="width:12%;text-align:center;border:1px solid;">Total Amount <br>'.$currency_name.'</th>
			</tr>
			</thead>
			<tbody>';
			if($inquiry_type!="2"){
			 	$trn_qry="SELECT trn.*,pro.product_name,hsn.hsn_code,unit.unit_name, pro.product_icode,pro.product_alias_name FROM tbl_quotation_trn as trn 
				left join product_mst as pro on pro.product_id=trn.product_id
				left join mst_hsn_code as hsn on hsn.hsn_id=pro.product_hsn
				left join unit_mst as unit on unit.unitid=trn.unitid
				where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
			} else {
			  	$trn_qry="SELECT trn.* , pro.product_name,pro.product_icode FROM `tbl_quotation_project_trn` as trn 
			  	left join product_mst as pro on pro.product_id = trn.product_id 
				where trn.quotation_projecttrn_status=0 and trn.quotation_id =".$rel['quotation_id'];
			}
			$trn_qry_rs=$dbcon->query($trn_qry);
			$p=1;$ttl_amt=0;$ttl_qty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;$total_cs_gst=0;
			$cnt=mysqli_num_rows($trn_qry_rs);
			while($trn_rel=brp_mysqli_fetch_assoc($trn_qry_rs)){
				$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
				$gst_rate = $trn_rel['cgst_tax_rate_conv']+$trn_rel['sgst_tax_rate_conv']+$trn_rel['igst_tax_rate_conv'];

				if($trn_rel['cgst_tax_rate_conv'] != 0 || $trn_rel['sgst_tax_rate_conv'] !=0){
					$total_cs_gst += $gst_rate;
				}else{
					$total_i_gst += $gst_rate;
				}
		//tax summary calculation start
				if(!empty($trn_rel['tax_val']))
				{
					$tax_num=explode(",",$trn_rel['tax_val']);
					$tax_name=explode(",",$trn_rel['tax_name']);
					$total_net_rate=($trn_rel['product_qty']*$trn_rel['product_rate_conv'])-$trn_rel['product_discount_conv'];
					for($j=0;$j<count($tax_num);$j++)
					{
						if(!in_array($tax_name[$j],$tax['per']))
						{
							$tax['per'][]=$tax_name[$j];
						}
						$tax['per_total'][$tax_name[$j]]+=$total_net_rate*$tax_num[$j]/100;
					}
				}
				$item_code = '';
				if(in_array('item',$sales_pro_search)){
					$item_code = "(".$trn_rel['product_icode'].")";
				}
				$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';

				$html.='<tr style="border:none;border-left:1px solid;border-bottom:1px solid;border-right:1px solid;">
				<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
				<td style="text-align:left;border:1px solid;vertical-align:top;">
				<strong></strong>'.$trn_rel['product_icode'].'<br/>
				('.$trn_rel['hsn_code'].')
				</td>
				<td style="text-align:left;border:1px solid;vertical-align:top;">';
				
				$trn_qry_sub_data="SELECT * FROM product_name_field as trn
left join product_mst as pro on pro.product_id = trn.product_id
where pro.product_id =".$trn_rel['product_id'];
                
$trn_qry_rs_sub_data=$dbcon->query($trn_qry_sub_data);
$trn_rel_subdata=mysqli_fetch_array($trn_qry_rs_sub_data);
// var_dump($trn_rel_sub12323123);exit;
 
$query_field = "SELECT * FROM tbl_item_master_field WHERE item_master_field_status = 0 AND company_id =".$_SESSION['company_id']." order by priority ASC";

$res_field = $dbcon->query($query_field);
$ro_cnt = brp_mysqli_num_rows($res_field);
$field=1;$counter=1;


		$html.='<table style="font-size:14px;border-collapse: collapse;width:100% !important;" cellpadding="3" cellspacing="3">';
		$html.='<tr>';
while($row_field = brp_mysqli_fetch_array($res_field)){
    $field_name = $row_field['item_master_field_db_name'];
	$subdata_value = isset($trn_rel_subdata[$field_name]) ? $trn_rel_subdata[$field_name] : null;
    // $get_field_value = quotation_sub_data($dbcon, $subdata_value, $row_field['item_master_field_id']);
	// 	if($get_field_value['item_master_field_value'] != '')
	// 	{
	// 		$html.='<td style="width:33.33% !important;"><strong>'.$row_field['item_master_field'].'</strong><br>'.$get_field_value['item_master_field_value'].'</td>';
	// 		if($counter == 3)
	// 		{
	// 			$html.='</tr>';
	// 			$html.='<tr>';
	// 			$counter=0;
	// 		}
	//    		$counter++;
	// 	}
}
				$html.='</tr></table>';
				
				$html.=''.$trn_rel['product_alias_name'].'<br>'.$trn_rel['product_desc'].'</td>
				<td style="text-align:center;border:1px solid;vertical-align:top;">
				'.$trn_rel['product_qty'].'
				</td>
				<td style="text-align:center;border:1px solid;vertical-align:top;">';
				if($trn_rel['act_amt_flag']=='1'){
					$html.="Extra At Actual";
				}
				else{
					$html .=$currency_symbol;
					if($rel['quot_type']=='0'){
						$html.= indian_number($trn_rel['product_rate_conv'],2);
					}else{
						$html.= indian_number($trn_rel['product_rate_conv'],2);
					}

				}

				$html.='</td>';
				if($companyConfiguration['quot_revise_time_rate_with_discount']==0){
					if($disc_qrys['discount'] > 0){
						$html.='<td style="text-align:center;border:1px solid;vertical-align:top;">
						'.$currency_symbol.' '.$trn_rel['product_discount_conv'].'<br>('.$trn_rel['discount_per'].' %)
						</td>';
					}
				}
				$html.='<td style="text-align:center;border:1px solid;vertical-align:top;">';
				if($trn_rel['act_amt_flag']=='1'){
					$html.="Extra At Actual";
				}
				else{
					$html .=$currency_symbol;
					if($rel['quot_type']=='0'){
						$html.= indian_number($trn_rel['product_amount_conv'],2);
					}else{
						$html.= indian_number($trn_rel['product_amount_conv'],2);
					}
				}

				$html.='</td>
				</tr>';
				$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
				if($trn_rel['act_amt_flag']!='1'){
					if($rel['quot_type']=='0'){
						$ttl_amt=$ttl_amt+($trn_rel['product_amount_conv']);
					}else{
						$ttl_amt=$ttl_amt+($trn_rel['product_amount_conv']);
					}
				}

				$p++;
			}
			$pr=10-$cnt;
			for($j=0; $j<$pr; $j++)
			{
				$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;border-bottom:1px solid;">
				<td style="border:none;border-left:1px solid;border-right:1px solid;height:40px;"></td>
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
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
			}

			$html.='<tr>
			<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>Basic Amount</b></td>
			<td style="text-align:center;border:1px solid;"><b>
			'.$currency_symbol.' '.indian_number($ttl_amt,2).'
			</b></td>
			</tr>';

			if(!empty($total_cs_gst) || !empty($total_i_gst)){
				if($rel['c_add_state']==$comp_rel['stateid']){
					$html.='<tr>
					<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>CGST</b></td>
					<td style="text-align:center;border:1px solid;"><b>
					'.$currency_symbol.' '.number_format(($total_cs_gst/2),2,".","").'
					</b></td>
					</tr>
					<tr>
					<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>SGST</b></td>
					<td style="text-align:center;border:1px solid;"><b>
					'.$currency_symbol.' '.number_format(($total_cs_gst/2),2,".","").'
					</b></td>
					</tr>';
				}else{
					$html.='<tr>
					<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>IGST</b></td>
					<td style="text-align:center;border:1px solid;"><b>
					'.$currency_symbol.' '.number_format(($total_i_gst),2,".","").'
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
				<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>'.$row11['l_name'].'</b></td>
				<td style="text-align:center;border:1px solid;"><b>
				'.$currency_symbol.' '.number_format($row11['add_sum'],2,".","").'
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
				<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>'.$row12['l_name'].'</b></td>
				<td style="text-align:center;border:1px solid;"><b>
				'.$currency_symbol.' '.number_format($row12['sundry_amount_conv'],2,".","").'
				</b></td>
				</tr>';
			}
			$html.='<tr>
			<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>Total Amount
			</td>
			<td style="text-align:center;border:1px solid;"><b>
			'.$currency_symbol.' '.indian_number($rel['g_total_conv'],2).'
			</b></td>
			</tr>
			<tr>
			<td colspan="'.($colspan+1).'" style="border:1px solid;text-align:right;"><b>Total (In Words): 
			'.(($comp_rel['currency_id']==$rel['currency_id']) ? ucfirst(convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$currency_word_end,$currency_word_start)) : ucfirst(convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$currency_word_end,$currency_word_start))).'</b></td></tr>';
			$html.='
			<!--<tr>
			<td colspan="'.($colspan+1).'" style="border:1px solid;text-align:left;"><b>Remarks:</b> 
			'.(($rel['quot_remark']) ? $rel['quot_remark'] : '').'</td></tr>--></tbody></table></div>';

			/* Get Terms And Condition Start */
			$terms_condi= " tc_id ";
			if ($rel['terms_type'] == 2) {
				$terms_condi = " ref_tc_id "; 
			}

			$terms_qry="select qtrm.*,mst.tc_name,mst.print_name from tbl_quotation_terms_trn as qtrm 
			left join tbl_terms_condition as mst on mst.tc_id=qtrm.$terms_condi
			where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
			$terms_qry_rs=$dbcon->query($terms_qry);
			if(mysqli_num_rows($terms_qry_rs)){
				$t=1;
				$html.='<center class="nextpage"></center><div>
				<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<thead>
				<tr>
				<th style="text-align:center; border:1px solid;">Sr.<br/>No.</th>
				<th style="text-align:left; border:1px solid;">Terms Name</th>
				<th style="text-align:left; border:1px solid;">Terms and Condition</th>
				</tr>
				</thead><tbody>';
				while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
					// $string=(nl2br($term_rel['tc_details']));
					$html.='<tr>
					<td width="5%" style="text-align:center;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;">'.$t.'</td>
					<td width="25%" style="text-align:left;border:1px solid #000;padding:5px;">'.$term_rel['print_name'].'</td>
					<td width="70%" style="text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;">'.$term_rel['tc_name'].'</td>
					</tr>';
					$t++;
				}
				$html.='</tbody></table>'; 
			}
			/* Check Annexure Attachments Start */
			if(trim($rel['quot_annex_content'])){
				$html.='<center class="nextpage"></center>';
				$html.='<div class="quot_annex_content_div" style="font-size: 16px;">'.$rel['quot_annex_content'];
				$html.='</div>';
			}
			/* Check Annexure Attachments End */
			if(!empty($quotation_footer_content)){
				$html.='<br /><br /><div>'.$quotation_footer_content;
				$html.='</div>';
			}
			
			$html .='<br><br><strong>For, '.$comp_rel['company_name'].'</strong><br>
			'.$userData['user_name'].' <br> '.$userPhone.'';
			$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
			</body>
			</html>';
//  echo $trn_qry;

// echo $html;exit;

// error_reporting(E_ALL);

			ob_end_clean();
			// ob_start();
			$file_name = $rel['quotation_no'].'.pdf';
			$file_name=str_ireplace("/","_",$file_name);
			
			if($save_file=="No"){
				// include("../../view/export/mpdf/mpdf.php");
				include("../../vendor/mpdf/mpdf/src/Mpdf.php");
			}else{
				// include("../../../view/export/mpdf/mpdf.php");
				include("../../vendor/mpdf/mpdf/src/Mpdf.php");
			}
			
			$mpdf=new Mpdf(['','A4','0','calibri','10','10','35','25','1','1']);
			
//		$mdf->SetFont('ProximaNova');
			$mpdf->defaultheaderfontsize = 10; /* in pts */
			$mpdf->defaultheaderfontstyle = 'B'; /* blank, B, I, or BI */
			$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
			$mpdf->defaultfooterfontsize = 10; /* in pts */
			$mpdf->defaultfooterfontstyle = 'B'; /* blank, B, I, or BI */
			$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
			$mpdf->SetHTMLHeader($header);
			//$mpdf->SetHTMLFooter($footer);
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