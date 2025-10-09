<?php 
$quotation_id = $_REQUEST['id'];	
// $printstatus = $_REQUEST['printstatus'];		
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
		$query="select quot.*,per.c_con_job,country.country_name, quot.quotation_ref,cust.cust_id, per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile,cust.cust_gst, inq.inquiry_no,inq.inquiry_date, ref.rb_name, cadd.c_add_state,com.company_name from tbl_quotation as quot
		left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
		left join tbl_cust_address as cadd on cadd.cust_id=quot.cust_id
		left join country_mst as country on country.countryid=cadd.c_add_country 
		left join tbl_customer as cust on cust.cust_id=quot.cust_id
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
		// if(!empty($rel['consignee_id'])){ 
		// 	if($rel['performa_invoice_type']=='1'){
		// 		$table_name = 'tbl_party_consignee';
		// 	}else{
		// 		$table_name = 'tbl_custmer_consignee';
		// 	}

		// select pars.cust_ref_id,quot.* from tbl_quotation as quot 
		// left join tbl_customer as cust on cust.cust_id=quot.cust_id 
		// left join tbl_party_consignee as pars on pars.cust_ref_id=cust.cust_id 
		// where quot.quotation_id=26

		// $cust_id = $rel["cust_id"];
		// echo	$consignee="select * from tbl_custmer_consignee as pars 
		// 	left join country_mst as country on country.countryid=pars.countryid
		// 	left join state_mst as state on state.stateid=pars.stateid 
		// 	left join city_mst as city on city.cityid=pars.cityid where pars=".$cust_id;
		// 	$cons_data=mysqli_fetch_assoc($dbcon->query($consignee)); die();
		
		$header ='<table style="border:0; border-color:white">
		<tr>
		
		<td style="width:40%; text-align:left;"> 
		
		<img src="../../view/img/logo/'.$comp_rel["logo"].'" style="height: 100px; width: 280px;" />
		</td>
		<td style="width:60%;"> 
	<span style="font-size:30px;color:blue"><strong>FUSIONTECH INTERNATIONAL</strong></span><br>
			<strong>'.$comp_rel["address"].'<br/>
			Mob.No : '.$comp_rel["contact_no"].'<br/>
			Email : '.$comp_rel["website"].'<br/>
			Web : '.$comp_rel["company_website"].'</strong>
		</td>
		</tr>
		</table>
	';
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
					<td style="border:none;font-size:20px;"><center><strong>QUOTATION</strong></center></td>
				</tr>
			</table>





			<table style="font-size:12px; width:100%;" cellpadding="5" cellspacing="5">

				<tr style="background-color: rgb(208, 209, 210);">

					<td width="25%" style="border:none;">Quotation No. :</td>
					<td width="25%" style="border:none;">Date :</td>

					<td width="25%" style="border:none;">Kind Attn :</td>
					<td width="25%" style="border:none;">Email :</td>
				</tr>
				<tr style="background-color:none;">

					<td width="25%" style="border:none;"><strong>'.$rel['quotation_no'].'</strong></td>
					<td width="25%" style="border:none;"><strong>'.date("d/m/Y",strtotime($rel['quotation_date'])).'</strong></td>

					<td width="25%" style="border:none;"><strong>'.$rel['c_con_fname'].' '.$rel['c_con_lname'].'</strong></td>
					<td width="25%" style="border:none;"><strong>'.$rel['c_con_email'].'</strong></td>
				</tr>
				<tr style="background-color: rgb(208, 209, 210);">

					<td width="25%" style="border:none;">Reference : </td>
					<td width="25%" style="border:none;">Website :</td>

					<td width="25%" style="border:none;">Designation :</td>
					<td width="25%" style="border:none;">Mobile :</td>
				</tr>
				<tr style="background-color:none;">

					<td width="25%" style="border:none;">'.$rel['quotation_ref'].'</td>
					<td width="25%" style="border:none;">'.$rel['cust_website'].'</td> 

					<td width="25%" style="border:none;">'.$rel['c_con_job'].'</td>
					<td width="25%" style="border:none;"><strong>'.$rel['c_con_mobile'].'</strong></td>
				</tr>
				</table>




			<table style="font-size:15px; width:100%;" cellpadding="5" cellspacing="5">

				<tr style="background-color: none;">

					<td colspan="2"  style="border:none;"><strong style="text-decoration:underline;font-size:18px;">CUSTOMER INFORMATION </strong></td>

					<td width="50%" style="border:none;padding-top:none; padding-bottom-none;"><strong style="text-decoration:underline;font-size:18px;">CONSIGNEE</strong></td>
					
				</tr>
				<tr style="background-color: none;">

					<td colspan="2"  style="border:none; padding-top:none; padding-bottom-none;"><strong>'.$rel['cust_name'].'</strong></td>
					<td width="50%" style="border:none;padding-top:none; padding-bottom-none;"><strong>'.$rel['c_con_fname'].'</strong></td>

					
				</tr>
				<tr style="background-color: none;">

					<td colspan="2"  style="border:none;padding-top:none; padding-bottom-none;"><strong>'.($quot_address).'</strong></td>
					<td width="50%" style="border:none ;padding-top:none; padding-bottom-none;"><strong></strong></td>

					
				</tr>
				<tr style="background-color: none;">

					<td width="5%"  style="border:none;padding-top:none; padding-bottom-none;"><strong>State</strong></td>
					<td width="45%" style="border:none;padding-top:none; padding-bottom-none;"><strong> : &nbsp;'.$comp_rel['state_name'].'</strong></td>
					<td width="50%" style="border:none;padding-top:none; padding-bottom-none;"> </td>

					
				</tr>
				<tr style="background-color: none;">

					<td width="5%"  style="border:none;padding-top:none; padding-bottom-none;"><strong>country </strong></td>
					<td width="45%" style="border:none;padding-top:none; padding-bottom-none;"><strong> : &nbsp;'.$rel['country_name'].'</strong></td>
					<td width="50%" style="border:none;padding-top:none; padding-bottom-none;"> </td>

					
				</tr>
				<tr style="background-color: none;">

					<td width="5%"  style="border:none;padding-top:none; padding-bottom-none;"><strong>GSTIN </strong></td>
					<td width="45%" style="border:none;padding-top:none; padding-bottom-none;"><strong> : &nbsp;'.$rel['cust_gst'].'</strong></td>
					<td width="50%" style="border:none;padding-top:none; padding-bottom-none;"> </td>

					
				</tr>
				<tr style="background-color: none;">

					<td width="11%"  style="border:none;padding-top:none; padding-bottom-none;"><strong>Mobile No</strong></td>
					<td width="39%" style="border:none;padding-top:none; padding-bottom-none;"><strong> : &nbsp;'.$rel['cust_mobile'].'</strong></td>
					<td width="50%" style="border:none;padding-top:none; padding-bottom-none;"> </td>

					
				</tr>
				
				</table>
<table style="width:100%;" cellpadding="5" cellspacing="5">

				<tr style="background-color: rgb(208, 209, 210);">

				<th style="width:10%; text-align:center; border:none;">Sr.No.</th>
				<th style="width:30%;text-align:left;border:none;"> Particulars</th>
				<th style="width:18%;text-align:center;border:none;">HSN/SAC Code</th>
				<th style="width:7%;text-align:center; border:none;">Quantity</th>
				<th style="width:10%;text-align:center;border:none;">UOM</th>
				<th style="width:10%;text-align:center;border:none;">Rate <br>'.$currency_name.'</th>
				<th style="width:15%;text-align:center;border:none;">Total <br>'.$currency_name.'</th>
			</tr>
				
		</thead>
		<tbody>';
		$trn_qry="select trn.*,pro.product_name,unit.unit_name,pro.product_spec,trn.product_spec as spe, hsn.hsn_code as product_hsn,hsn.sale_gst,ttc.tax_gst, pro.product_alias_name from tbl_quotation_trn as trn 
	left join product_mst as pro on pro.product_id=trn.product_id
	left join unit_mst as unit on unit.unitid=trn.unitid
	left join mst_hsn_code as hsn on hsn.hsn_id=pro.product_hsn
	left join tbl_tax_category as ttc on ttc.tax_cat_id =hsn.sale_gst
	where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
	$trn_qry_rs=$dbcon->query($trn_qry);
	$p=1;$ttl_amt=0;$ttl_qty=0;$total_gst=0;$total_i_gst=0;
	$cnt=mysqli_num_rows($trn_qry_rs);
	while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
	    $alias_name = '';
			if(in_array('alias',$crm_pro_print)){
				$alias_name = " -- (".$trn_rel['product_alias_name'].")";
			}
				$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
				$gst_rate = $trn_rel['cgst_tax_rate']+$trn_rel['sgst_tax_rate']+$trn_rel['igst_tax_rate'];
			if($trn_rel['cgst_tax_rate'] != 0 || $trn_rel['sgst_tax_rate'] !=0){
					$total_cs_gst += $gst_rate;
				}else{
					$total_i_gst += $gst_rate;
				}
		$html.='
		<tr style="border:1px solid rgb(208, 209, 210);border-left:none;border-right:none;">

		<td style="width:10%; text-align:center; border:none;vertical-align:top;">'.$p.'</td>
		<td style="width:30%;text-align:left;border:none;vertical-align:top;">'.$trn_rel['product_name'].'<br>'.nl2br($trn_rel['product_desc']).'';

		if($rel['delivery_type']=='product_wise'){
			$retu_date = "select sdate.*,unit.unit_name from tbl_quotation_delivery_date as sdate left join unit_mst as unit on unit.unitid=sdate.unit_id where sdate.quo_delivery_date_status=0 and quot_trn_id=".$trn_rel['sales_ordertrn_id'];
			$resadate=$dbcon->query($retu_date);
			while($rowdate = brp_mysqli_fetch_array($resadate)){
				$html.='<br>Delivery Schedule :'.date('d-m-Y',strtotime($rowdate['delivery_date'])).' Qty : '.$rowdate['product_qty'].' '.$rowdate['unit_name'];
			}
		}else{
			$html.='<br>Delivery Schedule :'.date("d/m/Y", strtotime($rel['delivery_date'])).' Qty :'.$trn_rel['product_qty'].' '.$trn_rel['unit_name'];
		}
		$html.='</td>
		<td style="width:18%;text-align:center;border:none;vertical-align:top;">'.$trn_rel['product_hsn'].'</td>
		<td style="width:7%;text-align:center; border:none;vertical-align:top;">'.$trn_rel['product_qty'].'</td>
		<td style="width:10%;text-align:center;border:none;vertical-align:top;">'.$trn_rel['unit_name'].'</td>
		<td style="width:10%;text-align:center;border:none;vertical-align:top;">


	';
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Extra At Actual";
		}
		else{
			$html.=$trn_rel['product_rate'];
		}
		$html.='</td>
		
		';
		if($trn_rel['discount_per']!='')
		{
		$html.='<td style="width:5%;text-align:center;vertical-align:top;vertical-align:top;">'.$trn_rel['discount_per'].'</td>';
		}
		else
		{
			$html.='';
		}
		$html.='<td style="width:15%;text-align:center;border:none;vertical-align:top;">
		';
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Extra At Actual";
		}
		else{
			$html.=$trn_rel['product_amount'];
		}
		$html.='</td>
		</tr>
		
	';
		
		$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
		if($trn_rel['act_amt_flag']!='1'){
			$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
		}
		$p++;
	
	$html.='</tbody>
	';

			
		
			
			
		}
		$html.='</table>
		<table style="width:100%;" cellpadding="5" cellspacing="5">

		<tr style="border:1px solid rgb(208, 209, 210);border-left:none;border-right:none;">
					
					
	
				<td  style="width:50%; text-align:right; border:none;"><b>Total </b> :<b>
					'.indian_number($ttl_qty,2).'
					</b></td>
				
				
				
				<td width=""  style=" text-align:right; padding-right: 30px; border:none;"><b>
				'.indian_number($ttl_amt,2).'
				</b>
				</td>
				</tr>
				</table>
		<table style="width:100%;" cellpadding="5" cellspacing="5">
		<tr style="border:1px solid rgb(208, 209, 210);border-left:none;border-right:none;border-bottom:none;">
					
					<td rowspan="7" style="width:50%; text-align:left; border:none;vertical-align:top;"><b>
					<span style="font-size:17px;">Bank Details : </span>
					
					<br>
					Account Name :</b> FUSIONTECH INTERNATIONAL<br>
					<b>Bank Name</b> &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;: Kotak Mahindra Bank<br>
					<b>Account No.</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: 0713586655<br>
					<b>IFSC</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;: KKBK0002568, VATVA BRANCH<br>
					<b>Swift Code</b> &nbsp; &nbsp; &nbsp; &nbsp;: KKBKINBBXXX<br>
					
</td>				
					<td  style=" text-align:right; border:none;"><b>
					Basic Amount 
					</b></td>
					<td  style="width:1%; text-align:right; border:none;"><b>
					: </b>
					</td>
	
					<td  style="width:13%; text-align:right; border:none;padding-right:35px;">'.indian_number($ttl_amt,2).'
					</td>
	
				</tr>
	
					
					
					';
					$bill_sundry =0;$total_sundrytax=0;
		$qry12="select b.sundry_gst_amount,b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
	   from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
	   left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
	   where b.sundry_voucher_id=".$rel['quotation_id']." and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0'";
	   
	   
		$result12=$dbcon->query($qry12);		
			while($row12=mysqli_fetch_assoc($result12))
			{
				$html .='	<tr style="border:1px solid rgb(208, 209, 210);border-left:none;border-top:none;border-bottom:none;border-right:none;">
				<td  style=" text-align:right; border:none;"><b>
				'.$row12['l_name'].'
					</b></td>
					<td  style="width:1%; text-align:right; border:none;"><b>
					: </b>
					</td>
	
					<td  style="width:13%; text-align:right; border:none;padding-right:35px;">'.number_format($row12['sundry_amount'],2,".","").'
					</td>
	
				</tr>
			
				';
				$bill_sundry=$bill_sundry+$row12['sundry_amount'];
				$total_sundrytax = $total_sundrytax + $row12['sundry_gst_amount'];


				
				
			}
			 	
		if(isset($_POST['quot_type'])){
				$html .='';
					}			
		else if($rel['c_add_state']==$comp_rel['stateid'] ){
		$gst_amt =	number_format((($total_sundrytax+$total_cs_gst)/2),2,".","") + number_format((($total_sundrytax+$total_cs_gst)/2),2,".","");
				$html.='
		
			<tr style="border:1px solid rgb(208, 209, 210);border-left:none;border-right:none;border-bottom:none;border-top:none;">
					
			
			
			<td  style=" text-align:right; border:none;"><b>
			GST Amount  
			</b></td>
			<td  style="width:1%; text-align:right; border:none;"><b>
			: </b>
			</td>

			<td  style="width:13%; text-align:right; border:none;padding-right:35px;">'.$gst_amt.'
			</td>

		</tr>';
	}
		else{$html.='
		<tr style="border:1px solid rgb(208, 209, 210);border-left:none;border-right:none;border-bottom:none;border-top:none;">
				
		
		
		<td  style=" text-align:right; border:none;"><b>
		IGST Amount  
		</b></td>
		<td  style="width:1%; text-align:right; border:none;"><b>
		: </b>
		</td>

		<td  style="width:13%; text-align:right; border:none;padding-right:35px;">'.number_format(($total_sundrytax+$total_i_gst),2,".","").'
		</td>

	</tr>
		
		
		';
	}
	$round_off = round($rel['g_total_conv'])-$rel['g_total_conv'];
		$gtotal=$rel['g_total_conv']-($round_off);
		
		// '.indian_number($round_off,2).'
	$html.='
	<tr style="border:1px solid rgb(208, 209, 210);border-left:none;border-right:none;border-bottom:none;border-top:none;">
				
		
		
	<td  style=" text-align:right; border:none;"><b>Round off
	</b></td>
	<td  style="width:1%; text-align:right; border:none;"><b>
	: </b>
	</td>

	<td  style="width:13%; text-align:right; border:none;padding-right:35px;">'.$round_off.'
	</td>

</tr>
	<tr style="border:1px solid rgb(208, 209, 210);border-left:none;border-right:none;border-bottom:none;border-top:none;">
				
		
		
	<td  style=" text-align:right; border:none;"><b>
	Total   
	</b></td>
	<td  style="width:1%; text-align:right; border:none;"><b>
	: </b>
	</td>

	<td  style="width:13%; text-align:right; border:none;padding-right:35px;">'.number_format($rel['g_total_conv'],0,".","").'.00
	</td>

</tr>';
$gtotalamt = number_format($rel['g_total_conv'],0,".","").'00';
$html .='
</table>
		<table style="width:100%;" cellpadding="5" cellspacing="5">

<tr style="border:1px solid rgb(208, 209, 210);border-left:none;border-right:none;">

<td width="100%" style=" text-align:left; border:1px solid black;"><b>Total Amount in Words :</b>'.ucfirst(convert_number_to_words_new($gtotalamt,$rel['currency_id'],$currency_word_start,$currency_word_end)).'
</td>

</tr>
</table>
		
		';
		
		
		

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
				<th style="text-align:left; border:none;">Terms and Condition</th>
				<th style="text-align:left; border:none;Total Amount in Words :"></th>
				</tr>
				</thead><tbody>';
				while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
					$string=(nl2br($term_rel['tc_details']));
					$html.='<tr>
					
					<td width="25%" style="width:25%;text-align:left;border:none;padding:5px; font-weight: bold; vertical-align: top;">'.$term_rel['tc_name'].'</td>
					<td width="70%" style="width:70%;text-align:left;border:none;padding:5px;">'.$string.'</td>
					</tr>';
					$t++;
				}
				$html.='
				</tbody></table>
				
				<div><h1 style="text-align:center;">Specification</h1>
				<p></p>
				<P><br>Thank You.<br>
							For, FUSIONTECH INTERNATIONAL<br><br><br>
							Authorised Signatory
							</P></div>'; 
			}
			
			
			/* Check Annexure Attachments Start */
			
			
			$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
			</body>
			</html>';
 //echo $trn_qry;
// echo $html;exit;
			ob_end_clean();
			$file_name = $rel['quotation_no'].'.pdf';
			$file_name=str_ireplace("/","_",$file_name);
			if($save_file=="No"){
				include("../../view/export/mpdf/mpdf.php");
			}else{
				include("../../../view/export/mpdf/mpdf.php");
			}
			$mpdf=new mPDF('','A4','0','calibri','5','5','35','20','1','1');
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