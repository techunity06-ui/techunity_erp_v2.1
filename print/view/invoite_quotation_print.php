<?php 
$quotation_id = $_REQUEST['id'];	
$printstatus = $_REQUEST['printstatus'];	
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
	quotation_print($dbcon,$quotation_id,$save_file = "No",$printstatus);
}
function quotation_print($dbcon,$quotation_id,$save_file,$printstatus){
	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
	$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
	$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';

// $quotation_id = $_REQUEST['id'];	
	$type='pdf';
	if(strtolower($type) == 'pdf') {
//Quotation Data
		$query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name, cadd.c_add_state,com.company_name from tbl_quotation as quot
		left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
		left join tbl_customer as cust on cust.cust_id=quot.cust_id
		left join tbl_cust_address as cadd on cadd.cust_id=quot.cust_id
		left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
		left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
		left join tbl_company as com on com.company_id=quot.company_id
		where quot.quotation_id=".$quotation_id;
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		
		$parts = explode('/', $rel['quotation_no']);
		$quotation_no_change = $parts[0].'/'.$parts[2].'/'.$parts[1].'/'.$parts[3];
// 		$rel['quotation_no'] = $quotation_no_change;
//p($rel);
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
		//echo '<img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="height: 100px; width: 225px;" />';die;
		//$header ='<div style="text-align:center;padding-top:30px"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:8.27in;" /></div>';
		$header ='<table style:"border:0; border-color:white">
		<tr>
		
		<td style="width:40%; text-align:left;"> 
		
		<img src="../../view/img/logo/'.$comp_rel["logo"].'" style="height: 100px; width: 225px;" />
		</td>
		<td style="width:60%;"> 
			<strong>'.$comp_rel["address"].'<br/>
			Mob.No : '.$comp_rel["contact_no"].'<br/>
			Email : '.$comp_rel["website"].'<br/>
			Web : '.$comp_rel["company_website"].'</strong>
		</td>
		</tr>
		</table>
	';
	//	$footer ='<div style="text-align:center;"><img src="'.DOMAIN_F.LOGO.$comp_rel["f_logo"].'" style="width:8.27in;" /></div>';
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
			$colspan =($disc_qrys['discount'] > 0) ? 8 : 7;	
			if($rel['quot_type']=='1'){
				$colspan =$colspan-1;	
			}
		}else{
			$colspan = 7;
			if($rel['quot_type']=='1'){
				$colspan =$colspan-1;	
			}
		}

		

		if($printstatus==0){
			$header ='';
			$footer ='';
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
				/*border:1px solid #000 !important;*/
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
			<sethtmlpageheader name="otherpages" value="off" show-this-page="0"/>
			<div>
			<table style="font-size:14px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<tr>
					<td style="border:1px solid"><center><strong>QUOTATION</strong></center></td>
				</tr>
			</table>
			<table style="font-size:12px; width:100%;" cellpadding="5" cellspacing="5">
				<tr>

					<td colspan="2" width="10%" style="border-left:1px solid;"><strong>Customer Name </strong>:</td>
					<td width="45%" colspan="4" style="border-right:1px solid;" ><strong>'.$rel['cust_name'].'</strong></td>

					<td width="10%" style="border-left:1px solid;"><strong>Quote No</strong></td>
					<td width="1%" style="border:0px;padding: 1px;"><strong>:</strong></td>
					<td width="25%" style="border-right:1px solid;"><strong>'.$rel['quotation_no'].'</strong></td>
				</tr>
				<tr>
					<td style="border-left:1px solid;vertical-align:top"><strong>Kind. Attn</strong></td>
					<td style="border:0px solid;vertical-align:top"><strong>:</strong></td>
					<td style="border-right:0px solid;vertical-align:top">'.$rel['c_con_fname'].' '.$rel['c_con_lname'].'</td>
					
					<td style="border-left:0px solid;text-align:right;vertical-align:top"><strong>Mob.No :</strong></td>
					
					<td colspan="2" style="border-right:1px solid;vertical-align:top">'.$rel['c_con_mobile'].'</td>

					<td rowspan="3" style="border-left:1px solid;vertical-align: top;border-bottom:1px solid;"><strong>Date</strong></td>
					<td rowspan="3" style="border:0px;vertical-align: top;border-bottom:1px solid;"><strong>:</strong></td>
					<td rowspan="3" style="border-right:1px solid;vertical-align: top;border-bottom:1px solid;">'.date("d/m/Y",strtotime($rel['quotation_date'])).'</td>
				</tr>
				<tr>
					<td style="border-left:1px solid;vertical-align:top"><strong>Address</strong></td>
					<td style="border:0px solid;vertical-align:top"><strong>:</strong></td>
					<td colspan="4" style="border-right:0px solid;vertical-align:top">'.($quot_address).'</td>
				</tr>
				<tr>
					<td style="border-left:1px solid;border-bottom:1px solid;vertical-align:top"><strong>Email Id</strong></td>
					<td style="border:0px solid;border-bottom:1px solid;vertical-align:top"><strong>:</strong></td>
					<td colspan="4" style="border-right:0px solid;border-bottom:1px solid;vertical-align:top">'.$rel['c_con_email'].'</td>
				</tr>
			</table>
			<table style="font-size:12px; width:100%;" cellpadding="5" cellspacing="5">
				<tr>
					<td style="border:1px solid;border-bottom:1px solid;">'.stripslashes($quotation_print_content).'</td>
				</tr>
			</table>
			
			<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
				<thead>
					<tr>
						<th style="width:5%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
						<th style="width:12%;text-align:center;border:1px solid;">PRODUCT <br/> CODE</th>
						<th style="width:50%;text-align:center;border:1px solid;">Description</th>
						<th style="width:7%;text-align:center;border:1px solid;">Qty</th>
						<th style="width:10%;text-align:center;border:1px solid;">UNIT</th>
						<th style="width:10%;text-align:center;border:1px solid;">PRICE <br>'.$currency_name.'</th>';
						if($companyConfiguration['quot_revise_time_rate_with_discount']==0){
							if($disc_qrys['discount'] > 0){
								$html.='<th style="width:10%;text-align:center;border:1px solid;">DISC <br>'.$currency_name.'</th>';
							}
						}
						if($rel['quot_type']=='0'){
							$html.='<th style="width:10%;text-align:center;border:1px solid;">GST <br>%</th>';
						}
					
					$html.='<th style="width:15%;text-align:center;border:1px solid;">AMOUNT <br>'.$currency_name.'</th>;
					</tr>
				</thead>
				<tbody>';
			if($inquiry_type!="2"){
			 	$trn_qry="SELECT trn.*,pro.product_name,unit.unit_name, pro.product_icode,drg.drawing_number FROM tbl_quotation_trn as trn 
				left join product_mst as pro on pro.product_id=trn.product_id
				left join unit_mst as unit on unit.unitid=trn.unitid
				left join tbl_drawing as drg  on drg.drawing_id=pro.drawing_id
				where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
			} else {
			  	$trn_qry="SELECT trn.* , pro.product_name,pro.product_icode,drg.drawing_number FROM `tbl_quotation_project_trn` as trn 
			  	left join product_mst as pro on pro.product_id = trn.product_id 
				left join tbl_drawing as drg  on drg.drawing_id=pro.drawing_id
				where trn.quotation_projecttrn_status=0 and trn.quotation_id =".$rel['quotation_id'];
			}
			$trn_qry_rs=$dbcon->query($trn_qry);
			$p=1;$ttl_amt=0;$ttl_qty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;
			$cnt=mysqli_num_rows($trn_qry_rs);
			while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
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
					$item_code = " -- (".$trn_rel['product_icode'].")";
				}
				$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';
				if(empty($trn_rel['drawing_number'])){
					$grgcode=$trn_rel['product_icode'];
				}else{
					$grgcode=$trn_rel['drawing_number'];
				}

				$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
							<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
							<td style="text-align:center;border:1px solid;vertical-align:top;">'.$trn_rel['drawing_number'].'</td>
							<td style="text-align:left;border:1px solid;vertical-align:top;">
								<strong>'.$trn_rel['product_name'].'</strong><br/>'.$trn_rel['product_desc'].'
							</td>
							<td style="text-align:center;border:1px solid;vertical-align:top;">
								'.$trn_rel['product_qty'].'
							</td>
							<td style="text-align:center;border:1px solid;vertical-align:top;">
								'.$trn_rel['unit_name'].'
							</td>
							<td style="text-align:center;border:1px solid;vertical-align:top;">';
								if($trn_rel['act_amt_flag']=='1'){
									$html.="Extra At Actual";
								}
								else{
									//$html .=$currency_symbol;
									if($rel['quot_type']=='0'){
										$html.= indian_number($trn_rel['product_rate_conv'],0);
									}else{
										$html.= indian_number($trn_rel['product_rate_conv'],0);
									}

								}
					$html.='</td>';
						if($companyConfiguration['quot_revise_time_rate_with_discount']==0){
							if($disc_qrys['discount'] > 0){
								$html.='<td style="text-align:center;border:1px solid;vertical-align:top;">
								'.$trn_rel['product_discount_conv'].'<br>('.$trn_rel['discount_per'].' %)
								</td>';
							}
						}
						if($rel['quot_type']=='0'){
						$html.='<td style="text-align:center;border:1px solid;vertical-align:top;">
								'.$gst_per.'
							</td>';
						}
				$html.='<td style="text-align:center;border:1px solid;vertical-align:top;">';
				if($trn_rel['act_amt_flag']=='1'){
					$html.="Extra At Actual";
				}
				else{
					//$html .=$currency_symbol;
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
			$pr=1-$cnt;
			for($j=0; $j<$pr; $j++)
			{
				$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
				<td style="border:none;border-left:1px solid;border-right:1px solid;height:40px;"></td>
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
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
			if($rel['quot_type']=='1'){
				$rel['g_total_conv'] -= $total_cs_gst;
				$rel['g_total_conv'] -= $total_i_gst;
			}
			$html.='<tr>
			<td colspan="'.($colspan-1).'" style="text-align:left;border:1px solid;"><b>Total (In Words): 
			'.(($comp_rel['currency_id']==$rel['currency_id']) ? ucfirst(convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$currency_word_end,$currency_word_start)) : ucfirst(convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$currency_word_end,$currency_word_start))).'</b></td>
			<td  style="text-align:right;border:1px solid;"><b>Basic Amount</b></td>
			<td style="text-align:center;border:1px solid;"><b>
			'.indian_number($ttl_amt,2).'
			</b></td>
			</tr>';
			if($rel['quot_type'] == 0){
			if(!empty($total_cs_gst) || !empty($total_i_gst)){
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
				'.number_format($row11['add_sum'],2,".","").'
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
				'.number_format($row12['sundry_amount_conv'],2,".","").'
				</b></td>
				</tr>';
			}
			
			$round_off = round($rel['g_total_conv'])-$rel['g_total_conv'];
		$gtotal=$rel['g_total_conv']-($round_off);
	    	
	    		$html.='<tr>
			<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>Round Off 
			</td>
			<td style="text-align:center;border:1px solid;"><b>
			'.indian_number($round_off,2).'
			</b></td>
			</tr>
			';
			
			$html.='<tr>
			<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>Total Amount
			</td>
			<td style="text-align:center;border:1px solid;"><b>
				'.number_format($rel['g_total_conv'],0,".","").'.00
			</b></td>
			</tr>
			';
			//var_dump($rel['g_total_conv']);die;
			$html.='
			
			<!--<tr>
			<td colspan="'.($colspan+1).'" style="border:1px solid;text-align:left;"><b>Remarks:</b> 
			'.(($rel['quot_remark']) ? $rel['quot_remark'] : '').'</td></tr>-->
			
			</tbody></table>

			<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
				<tr>
					<td style="text-align: right;border:1px solid;">
						<strong>For '.$rel['company_name'].'
						<br/><br/><br/><br/>
						Authoriser Signatory </strong>
					</td>
				</tr>
			</table>
			
			</div>';

			/* Get Terms And Condition Start */
			$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
			left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
			where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
			$terms_qry_rs=$dbcon->query($terms_qry);
			if(mysqli_num_rows($terms_qry_rs)){
				$t=1;
				$html.='<center class="nextpage"></center><div>
				<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<thead>
				<tr>
				<th style="text-align:center; border:1px solid;">Sr.<br/>No.</th>
				<th style="text-align:left; border:1px solid;">Terms and Condition</th>
				<th style="text-align:left; border:1px solid;">Description</th>
				</tr>
				</thead><tbody>';
				while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
					$string=(nl2br($term_rel['tc_details']));
					$html.='<tr>
					<td width="5%" style="width:5%;text-align:center;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;">'.$t.'</td>
					<td width="25%" style="width:25%;text-align:left;border:1px solid;padding:5px; font-weight: bold; vertical-align: top;">'.$term_rel['tc_name'].'</td>
					<td width="70%" style="width:70%;text-align:left;border:1px solid #000;padding:5px;">'.$string.'</td>
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
			//	$html.='<br /><br /><div>'.$quotation_footer_content;
				//$html.='</div>';
			}
			
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
			$mpdf=new mPDF('','A4','0','calibri','5','5','30','10','1','1');
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
			$mpdf->SetFooter('{PAGENO}{nbpg}');
			$mpdf->SetWatermarkText();
			$mpdf->showWatermarkText = true;
			$mpdf->allow_charset_conversion=true;
			$mpdf->curlAllowUnsafeSslRequests = true;
			// $mpdf->showImageErrors = true;
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