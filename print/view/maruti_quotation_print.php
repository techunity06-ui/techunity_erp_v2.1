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

		$quotation_date='';

		if($rel['quotation_date']!="1970-01-01" && $rel['quotation_date']!="0000-00-00")
		{
			$quotation_date=date('d M Y',strtotime($rel['quotation_date']));
		}
		if($rel['quot_type']=='0'){
			$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
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
		$header ='<div style="text-align:center;margin-left:0; margin-right:0"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:100%;margin:0px !important" /></div>
					';
		$footer ='<div style="text-align:center;width:100%; margin-left:0; margin-right:0;"><img src="'.DOMAIN_F.LOGO.$comp_rel["f_logo"].'" style="width:8.27in;" /></div>';
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
		<body style="margin:0">
			<div>
			<img  src="'.DOMAIN_F.LOGO.'first_page_maruti.jpg" style="width:100%;height:100%;margin:0px !important;" />
				
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
			<body style="margin:0px">';
			

			$html .='<!--Show Logo in other pages-->
			<htmlpageheader name="otherpages" style="display:none">
			<div style="text-align:center">'.$header.'</div>
			</htmlpageheader>
			<!--<htmlpagefooter name="otherpages_footer" style="display:none">
			<div style="text-align:center">'.$footer.'</div>-->
			</htmlpagefooter>

			<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
			<div style="margin-left:25px !important; margin-right:25px !important">
			<table cellpadding="5" cellspacing="5" border="0" style="font-size: 16px; font-family: Proxia Nova; border: 0;">
				<tr style="border: 0;">
					<td style="border: 0;width:50%"><strong>To,</strong></td>
					<td style="border: 0;width:50%"></td>
				</tr>
				<tr style="border: 0;">
					<td style="border: 0;width:50%"><strong>'.$rel['cust_name'].'</strong></td>
					<td style="border: 0;width:50%"></td>
				</tr>
				<tr style="border: 0;">
					<td style="border: 0;width:50%"><strong>Address : </strong>'.$rel['c_add_address'].'</td>
					<td style="border: 0;width:50%"></td>
				</tr>
				<tr style="border: 0;">
					<td style="border: 0;width:50%"><strong>Call : </strong>'.$rel['c_con_mobile'].'</td>
					<td style="border: 0;width:50%"></td>
				</tr>
				<tr style="border: 0;">
					<td style="border: 0;width:50%"><strong>Email : </strong>'.$rel['cust_email'].'</td>
					<td style="border: 0;width:50%"></td>
				</tr>
				<tr style="border: 0;height : 30px">
					<td colspan="2"></td>
				</tr>
				<tr style="border: 0;">
					<td style="border: 0;width:50%" colspan="2"><strong>  Subject: -</strong>'.$rel['quot_subject'].'</td>
				</tr>
					<!--<tr style="border: 0;">
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
						</tr>-->
			</table>
			</div>
			<div style="margin-left:25px !important; margin-right:25px !important">'.$rel['quot_header'].'</div>
			<center class="nextpage"></center>
			<div style="margin-left:25px !important; margin-right:25px !important">
			<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
	<thead>
	<tr>
	<th style="width:5%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
	<th style="width:50%;text-align:center;border:1px solid;">Description</th>
	<th style="width:10%;text-align:center;border:1px solid;">Qty</th>
	<th style="width:10%;text-align:center;border:1px solid;">Rate <br>'.$currency_name.'</th>
	<th style="width:15%;text-align:center;border:1px solid;">Total Amount <br>'.$currency_name.'</th>
	</tr>
	</thead>
	<tbody>';
	if($inquiry_type==1){
		$trn_qry="select trn.*,pro.product_name,unit.unit_name from tbl_quotation_trn as trn 
		left join product_mst as pro on pro.product_id=trn.product_id
		left join unit_mst as unit on unit.unitid=trn.unitid
		where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
	} else {
		 $trn_qry="select trn.*,pro.product_name,pro.image_name,trn.product_disc as product_desc  from tbl_quotation_project_trn as trn 
		left join product_mst as pro on pro.product_id=trn.product_id where trn.quotation_projecttrn_status=0 and trn.quotation_id=".$rel['quotation_id'];
	}
	$trn_qry_rs=$dbcon->query($trn_qry);
	$p=1;$ttl_amt=0;$ttl_qty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;
	$cnt=mysqli_num_rows($trn_qry_rs);
	while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
		$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
		$gst_rate = $trn_rel['cgst_tax_rate']+$trn_rel['sgst_tax_rate']+$trn_rel['igst_tax_rate'];

		if($trn_rel['cgst_tax_rate'] != 0 || $trn_rel['sgst_tax_rate'] !=0){
			$total_cs_gst += $gst_rate;
		}else{
			$total_i_gst += $gst_rate;
		}
		//tax summary calculation start
		if(!empty($trn_rel['tax_val']))
		{
			$tax_num=explode(",",$trn_rel['tax_val']);
			$tax_name=explode(",",$trn_rel['tax_name']);
			$total_net_rate=($trn_rel['product_qty']*$trn_rel['product_rate'])-$trn_rel['discount'];
			for($j=0;$j<count($tax_num);$j++)
			{
				if(!in_array($tax_name[$j],$tax['per']))
				{
					$tax['per'][]=$tax_name[$j];
				}
				$tax['per_total'][$tax_name[$j]]+=$total_net_rate*$tax_num[$j]/100;
			}
		}

		
			$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';
			$product_desc = $trn_rel['product_desc'];

		$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;border-bottom:1px solid;">
		<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
		<td style="text-align:left;border:1px solid;vertical-align:top;">
		<strong>'.$trn_rel['product_name'].'</strong><br/>
		'.$product_desc.'<br>';
				if($trn_rel['image_name']!=null){
				$html.='<a href="'.DOMAIN_F.'view/upload/product_images/'.$trn_rel["image_name"].'" target="_blank"><img src="'.DOMAIN_F.'view/upload/product_images/'.$trn_rel['image_name'].'" style="width: 100px;height: 100px;"></a>';
			}else{
				$html.='';
			}
				$html.='</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">
		'.$trn_rel['product_qty'].'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">';
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Extra At Actual";
		}
		else{
			if($rel['quot_type']=='0'){
				$html.= indian_number($trn_rel['product_rate'],2);
			}else{
				$html.= indian_number($trn_rel['product_rate_dollar'],2);
			}

		}

		$html.='</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">';
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Extra At Actual";
		}
		else{
			if($rel['quot_type']=='0'){
				$html.= indian_number($trn_rel['product_qty'] * $trn_rel['product_rate'],2);
			}else{
				$html.= indian_number($trn_rel['product_qty'] * $trn_rel['product_rate_dollar'],2);
			}
		}

		$html.='</td>
		</tr>';
		$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
		if($trn_rel['act_amt_flag']!='1'){
			if($rel['quot_type']=='0'){
				$ttl_amt=$ttl_amt+($trn_rel['product_qty'] * $trn_rel['product_rate']);
			}else{
				$ttl_amt=$ttl_amt+($trn_rel['product_qty'] * $trn_rel['product_rate_dollar']);
			}
		}

		$p++;
	}
	$pr=10-$cnt;
	for($j=0; $j<$pr; $j++)
	{
		$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
		<td style="border:none;border-left:1px solid;border-right:1px solid;height:40px;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		</tr>';
	}

	$html.='<tr>
	<td colspan="4" style="text-align:right;border:1px solid;"><b>Total Amount:- Ex Factory </b></td>
	<td style="text-align:right;border:1px solid;"><b>
	'.indian_number($ttl_amt,2).'
	</b></td>
	</tr>';
	
	$bill_sundry =0;$total_sundrytax=0;
			 $qry12="select b.sundry_gst_amount,b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
			from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
			left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
			where b.sundry_voucher_id=".$rel['quotation_id']." and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0'";
			$result12=$dbcon->query($qry12);		
			while($row12=mysqli_fetch_assoc($result12))
			{
				$html.='<tr>
				<td colspan="4" style="text-align:right;"><b>'.$row12['l_name'].'</b></td>
				<td style="text-align:right;"><b>
				'.number_format($row12['sundry_amount'],2,".","").'
				</b></td>
				</tr>';
				$bill_sundry=$bill_sundry+$row12['sundry_amount'];
				 $total_sundrytax = $total_sundrytax + $row12['sundry_gst_amount'];
			}
	
	$html.='<tr>
			<td colspan="4" style="text-align:right;"><b>Sub Total</b></td>
			<td colspan="" style="text-align:right;"><b>
			'.indian_number($ttl_amt+$bill_sundry,2).'
			</b></td>
			</tr>';
	
	if($rel['qt_add_state']==$comp_rel['stateid']){
		$html.='<tr>
		<td colspan="4" style="text-align:right;border:1px solid;"><b>CGST</b></td>
		<td style="text-align:right;border:1px solid;"><b>
		
		'.number_format((($total_sundrytax+$total_cs_gst)/2),2,".","").'
		</b></td>
		</tr>
		<tr>
		<td colspan="4" style="text-align:right;border:1px solid;"><b>SGST</b></td>
		<td style="text-align:right;border:1px solid;"><b>
		'.number_format((($total_sundrytax+$total_cs_gst)/2),2,".","").'
		</b></td>
		</tr>';
	}else{
		$html.='<tr>
		<td colspan="4" style="text-align:right;border:1px solid;"><b>IGST</b></td>
		<td style="text-align:right;border:1px solid;"><b>
		'.number_format(($total_sundrytax+$total_i_gst),2,".","").'
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
		<td colspan="4" style="text-align:right;border:1px solid;"><b>'.$row11['l_name'].'</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		'.number_format($row11['add_sum'],2,".","").'
		</b></td>
		</tr>';
	}
	
	$html.='<tr>
	<td colspan="4" style="text-align:right;border:1px solid;"><b>Total Amount
	</td>
	<td style="text-align:right;border:1px solid;"><b>
	'.indian_number($rel['g_total'],2).'
	</b></td>
	</tr>
	<tr>
	<td colspan="5" style="border:1px solid;text-align:right;"><b>Total (In Words): 
	'.(($comp_rel['currency_id']==$rel['currency_id']) ? ucfirst(convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$currency_word_end,$currency_word_start)) : ucfirst(convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$currency_word_end,$currency_word_start))).'</b></td></tr>';
	
	$html.='
	<tr>
	<td colspan="5" style="border:1px solid;text-align:left;"><b>Remarks:</b> 
	'.(($rel['quot_remark']) ? $rel['quot_remark'] : '').'</td></tr></tbody></table></div>';

			/* Get Terms And Condition Start */
			$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
			left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
			where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
			$terms_qry_rs=$dbcon->query($terms_qry);
			if(mysqli_num_rows($terms_qry_rs)){
				$t=1;
				$html.='<center class="nextpage"></center><div style="margin-left:25px !important; margin-right:25px !important">
				<table style="font-size:14px;border-collapse: collapse;width:100%;border:none" cellpadding="5" cellspacing="5">
				<thead>
				<tr style="border:none">
					<th colspan="3" style="text-align:center; border:none;font-size:18px"> General Terms & Conditions</th>
				</tr>
				
				</thead><tbody>';
				while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
					$string=(nl2br($term_rel['tc_details']));
					$html.='<tr style="border:1px solid black">
					<td width="5%" style="width:5%;text-align:center;border:1px solid black;padding:5px; font-weight: bold; vertical-align: top;">'.$t.'</td>
					<td width="25%" style="width:25%;text-align:left;border:1px solid black;padding:5px; font-weight: bold; vertical-align: top;">'.$term_rel['tc_name'].'</td>
					<td width="70%" style="width:70%;text-align:left;border:1px solid black;padding:5px;">'.$string.'</td>
					</tr>';
					$t++;
				}
				$html.='<tr style="border:none">
					<td colspan="3" style="height:50px"> </td>
				</tr>
				<tr style="border:none">
					<td colspan="2" style="text-align:left;font-weight:bold;border:none;font-size:18px"></td>
					<td style="text-align:right;font-weight:bold;border:none;font-size:18px">'.$comp_rel['company_name'].'</td>
				</tr>
				<tr style="border:none;">
				
					<td colspan="2" style="text-align:left;font-weight:bold;border:none;font-size:18px">
						<!--<img src="'.DOMAIN_F.'view/upload/signature/'.$comp_rel['authorized_signature'].'" style="height: 100px; width: 180px;"><br>-->
						<br/>
						<br/>
						<br/>
						Authorized Signatory
					</td>
						<td  style="text-align:left;font-weight:bold;border:none;font-size:18px;height:70px"></td>
				</tr>
				</tbody></table>'; 
			}

		
			/* Check Annexure Attachments Start */
			if(1==2){

			
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
				</table>';
			}
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
 /*echo $header;
 echo $footer;
echo $html;exit;*/
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
    'margin-left' => '0',
    'margin-right' => '0',
    'margin-top' => '60',
    'margin-bottom' => '25',
]);
	$bgimage=DOMAIN_F.LOGO.'maruti_water_mark.png';
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
			//$mpdf->SetFooter('{PAGENO}{nbpg}');
			$mpdf->SetWatermarkText();
			$mpdf->showWatermarkText = true;
			$mpdf->SetWatermarkImage($bgimage);
			$mpdf->showWatermarkImage = true;
			$mpdf->allow_charset_conversion=true;
			$mpdf->charset_in='UTF-8';
			$mpdf->WriteHTML($html);
			$mpdf->SetHTMLFooter($footer);
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
