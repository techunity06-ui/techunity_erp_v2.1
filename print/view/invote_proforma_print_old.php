<?php 

session_start();
	include("../../config/config.php");
	include("../../config/session.php");
	include("../../include/function_database_query.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	$incPath = $path.'include/';

	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		QUOTATION_SLUG_PRINT,
	]);

/*	if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}*/

$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
$printstatus = $_REQUEST['printstatus'];
 
if(!empty($invoiceid)){
	
	
    $que = "select performa_invoice_type from tbl_proforma_invoice where invoice_id=$invoiceid";
    $rel_que=mysqli_fetch_assoc($dbcon->query($que));

	proforma_print($dbcon,$invoiceid,$rel_que['performa_invoice_type'],$save_file = "No",$printstatus);
}
function proforma_print($dbcon,$invoiceid,$performa_invoice_type,$save_file,$printstatus){
	
// $quotation_id = $_REQUEST['id'];	
	$type='pdf';
	if(strtolower($type) == 'pdf') {
if($performa_invoice_type=='1'){
        $query="select invoice.*,country.country_name,payterms.payment_terms as payment_trm,state.state_name,cust_a.c_add_state as stateid,state.gst_state_code, city.city_name, cust.cust_name as company_name,per.company_name as consignee_company_name,per.cust_name as consignee_name,per.cust_address as consignee_address, cust_a.c_add_location as cust_address,cust_a.c_add_street as cust_street,cust_a.c_add_zip as cust_pincode, type.invoice_type,cust.cust_mobile,cust.cust_email,cust.cust_gst as gst_no,invoice.consignee_id,cur.currency_in_word_end, cur.currency_in_word,CONCAT(cust_con.c_con_fname,' ', cust_con.c_con_lname) as contact_person_name, md.transportation_name 
        from tbl_proforma_invoice as invoice 
        left join tbl_customer as cust on cust.cust_id=invoice.cust_id 
        left join tbl_cust_contact as cust_con on cust_con.cust_id=invoice.cust_id 
        left join tbl_party_consignee as per on per.cust_id=invoice.consignee_id
        left join tbl_cust_address as cust_a on cust_a.cust_id=invoice.cust_id 
        left join tbl_currency as cur on cur.currency_id = invoice.currency_id
        left join country_mst as country on country.countryid=cust_a.c_add_country 
        left join state_mst as state on state.stateid=cust_a.c_add_state 
        left join city_mst as city on city.cityid=cust_a.c_add_city 
        left join tbl_invoicetype as type on type.invoicetype_id=invoice.invoicetype_id 
        left join pay_terms as payterms on payterms.terms_id=invoice.payment_terms 
        left join transportation_details as md on md.id=invoice.transid
        where invoice_id=$invoiceid";
		
		
    }else{
        $query="select invoice.*,country.country_name,payterms.payment_terms as payment_trm,state.state_name,cust.stateid,state.gst_state_code, city.city_name, cust.l_name as company_name,cust.m_address as cust_address, type.invoice_type,cust_pincode,cust_mobile,gst_no as  gst_no,cur.currency_in_word_end, cur.currency_in_word 
        from tbl_proforma_invoice as invoice 
        left join tbl_ledger as cust on cust.l_id=invoice.cust_id 
        left join tbl_currency as cur on cur.currency_id = invoice.currency_id
        left join country_mst as country on country.countryid=cust.countryid 
        left join state_mst as state on state.stateid=cust.stateid 
        left join city_mst as city on city.cityid=cust.cityid 
        left join tbl_invoicetype as type on type.invoicetype_id=invoice.invoicetype_id 
        left join pay_terms as payterms on payterms.terms_id=invoice.payment_terms 
        left join transportation_details as md on md.id=invoice.transid
        where invoice_id=$invoiceid";
		
    }
	
    // echo $query;die;
    $rel=mysqli_fetch_assoc($dbcon->query($query));
    $userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
	$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
	$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';
    if(!$rel){
			header("Location: ".ROOT.CRM_ROOT."proforma_list");
		}

	$party_address_billing="<span style='font-weight:normal;'>
	".$rel['cust_address'].",<br/>
	".$rel['cust_pincode']."
	".$rel['city_name'].",
	".$rel['state_name'].",
	".$rel['country_name']."</span>
	<br>  State Code : ".$rel['gst_state_code'];
	/*<br/>
		Mobile No.: ".$rel['cust_mobile']."<br/>
		Email Id: ".strtolower($rel['common_email_id'])."*/

		$cust_gst_no=$rel['gst_no'];
		$user_name = $rel['company_name'];
		if(!empty($rel['contact_person_name'])){
			$user_name = $rel['contact_person_name'];
		}


		$consignee_company_name = $rel['company_name'];
		$consinee_user_name = $user_name;

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
		

		$consignee_company_name = $rel['company_name'];
		$consinee_user_name = $user_name;

    

	$cons_gst_no=$rel['gst_no'];
    $cons_pan_no=$rel['pan_no'];
    $cons_state_name=$rel['state_name'];
    $cons_gst_state_code=$rel['gst_state_code'];
    $place_of_supply=$rel['city_name'];
    $order_no = ($rel['order_no']!='0')?$rel['order_no']:'';
    
    if(!empty($rel['consignee_id'])){ 
        if($rel['performa_invoice_type']=='1'){
            $table_name = 'tbl_party_consignee';
        }else{
            $table_name = 'tbl_custmer_consignee';
        }
        $consignee="select * from $table_name as cust 
        left join country_mst as country on country.countryid=cust.countryid
        left join state_mst as state on state.stateid=cust.stateid 
        left join city_mst as city on city.cityid=cust.cityid where cust_id=".$rel['consignee_id'];
        $cons_data=mysqli_fetch_assoc($dbcon->query($consignee)); 
        $cons_gst_no=($cons_data['gst_no']!='0')?$cons_data['gst_no']:$rel['gst_no'];
        $cons_pan_no=$cons_data['pan_no'];
        $cons_state_name=$cons_data['state_name'];
        $cons_gst_state_code=$cons_data['gst_state_code'];
        $place_of_supply=$cons_data['city_name'];
    }

    $set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
    $comp_rel=mysqli_fetch_assoc($dbcon->query($set));  
//echo "<pre>";print_r($comp_rel);die();
    $order_date='';$lr_date='';$dispatch_date='';
    if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
    {
        $order_date=date('d/m/Y',strtotime($rel['order_date']));
    }
    if($rel['dispatch_date']!="1970-01-01 00:00:00" && $rel['dispatch_date']!="0000-00-00 00:00:00")
    {
        $dispatch_date=date('d-m-Y h:i a',strtotime($rel['dispatch_date']));
    }
    $dft = '';
    if($rel['approve_status']==3){
        $dft = '( DRAFT )';
    }
    /* Check Discount is On or off Start */
    if($comp_rel['show_disc']=='1'){
        $colspan=6;
        $dynamicwidth=40;
    }else{
        $colspan=6;
        $dynamicwidth=46;
    }
  $colspan=9;
     $sundrytax=$dbcon->query("select b.* from tbl_bill_sundry_transaction as b where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_proforma_invoice' and b.isdelete='0' ");
    //$sundrytax_rels = mysqli_fetch_assoc($sundrytax);
    $total_sundrytax=0;
    while($sumsundrytax=mysqli_fetch_assoc($sundrytax)){
        $total_sundrytax = $total_sundrytax + $sumsundrytax['sundry_gst_amount'];
    }
    $quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';

   		 $consignee_address =  $quot_address;
		if($rel['consignee_id'] > 0){
			$consinee_company_name = $rel['consignee_company_name'];
			$consinee_user_name = $rel['consignee_name'];	
			$consignee_address = $rel['consignee_address'];	
		}


		/*$header ='<table style:"border:0; border-color:white">
			<tr>
			<td style="width:50%;"> 
			<img src="'.DOMAIN_F.LOGO.'tuv_india.png" style="height: 100px; text-align:left;" />
			</td>
			<td style="width:50%; text-align:right;"> 
			<img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="height: 100px; width: 225px;" />
			</td>
			</tr>
			</table>
		
			<div style="text-align:Center;padding-top:0px;">
			<span style=" font-size:20px;"> <b>Jainflex Cables Pvt. Ltd.</b></span><br>
			A-2/1, Sabarmati Industrial Society, B/h. Guru Cold Storage, Sabarmati, Ahmedabad, Gujarat, (India) - 380005 <br>  GST : 24073200318 | Website : www.jainflex.com | CIN No :123456789

			</div>
		';*/
		$header ='<table style:"border:0; border-color:white">
			<tr>
			
			<td style="width:40%; text-align:left;"> 
			<img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="height: 100px; width: 225px;" />
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
		//$header ='<img src="'.DOMAIN_F.LOGO.'tuv_india.png" style="height: 100px; text-align:left;" />';
		
		//$footer ='<div style="text-align:center;"><img src="'.DOMAIN_F.LOGO.$comp_rel["f_logo"].'" style="width:8.27in;" /></div>';
		
//Amish Soni Start 16-03-2021
		$companySettings = getCompanySettings($dbcon);
		$companyConfiguration=getCompanyConfiguration($dbcon);
		$sales_pro_search=explode(",", $companyConfiguration['sales_pro_search']);

		if($companySettings) {
			$quotation_footer_content = $companySettings['quotation_footer_content'] ? $companySettings['quotation_footer_content'] : '';
			$quotation_footer_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_footer_content);
		}
		 $sss = "SELECT SUM(trn.product_discount_conv) as discount from tbl_proforma_trn as trn where trn.trancation_status=0 and trn.invoice_id=".$rel['invoice_id'];
		 
		$disc_qry=$dbcon->query("SELECT SUM(trn.product_discount_conv) as discount from tbl_proforma_trn as trn where trn.trancation_status=0 and trn.invoice_id=".$rel['invoice_id']);
		$disc_qrys = brp_mysqli_fetch_assoc($disc_qry);
		/*if($companyConfiguration['quot_revise_time_rate_with_discount'] == 0){
			
		}else{
			$colspan = 5;
		}*/
$colspan =($disc_qrys['discount'] > 0) ? 5 : 4;		
		$payment_tems = "";

		if($rel['payment_terms'] != ""){
			$payment_tems = $rel['payment_terms']." Days";		
		}

		$mode_of_dispatch = "";

		if(!empty($rel['transportation_name'])){
			$mode_of_dispatch = $rel['transportation_name'];
		}


		$desc_width = 65;
		$amt_width = 20;

		
		$footer ="";
			if(!empty($quotation_footer_content)){
				$footer ='<div style="text-align:center;">'.$quotation_footer_content.'</div>';
			}

			if($printstatus==0){
				$header ='';
				$footer ='';
			}
			
		
//Amish Soni End 16-03-2021
		$html ='<html>
		<head>					
		<title>Proforma - '.$rel['invoice_no'].'</title>
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
						<td width="40%" style="border:1px solid;border-right:0px solid;"><strong>Our GSTIN No : '.$comp_rel["vatno"].'</strong></td>
						<td width="60%" style="border:1px solid;border-left:0px solid;"><strong>PROFORMA INVOICE</strong></td>
					</tr>
				</table>
				<table style="font-size:14px; border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
					<tr>
						<td width="15%" style="border:1px solid;border-right:0px solid;border-bottom:0px solid;vertical-align: top;"><strong>Cust. Name</strong></td>
						<td width="1%" style="border:1px solid;border-left:0px solid;border-right:0px solid;border-bottom:0px solid;vertical-align: top;"><strong>:</strong></td>
						<td width="44%" style="border:1px solid;border-left:0px solid;border-bottom:0px solid;vertical-align: top;"><strong>'.$rel['company_name'].'</strong></td>

						<td width="10%" style="border:1px solid;border-right:0px solid;border-bottom:0px solid;vertical-align: top;"><strong>Pro. Inv No</strong></td>
						<td width="1%" style="border:1px solid;border-left:0px solid;border-right:0px solid;border-bottom:0px solid;vertical-align: top;"><strong>:</strong></td>
						<td width="15%" style="border:1px solid;border-left:0px solid;border-bottom:0px solid;vertical-align: top;"><strong>'.$rel['invoice_no'].'</strong></td>
					</tr>
					<tr>
						<td width="15%" style="border:1px solid;border-right:0px solid;border-top:0px solid;border-bottom:0px solid;vertical-align: top;"><strong>Address</strong></td>
						<td width="1%" style="border:1px solid;border-left:0px solid;border-right:0px solid;border-top:0px solid;border-bottom:0px solid;vertical-align: top;"><strong>:</strong></td>
						<td width="44%" style="border:1px solid;border-left:0px solid;border-top:0px solid;border-bottom:0px solid;vertical-align: top;">'.$party_address_billing.'</td>

						<td width="10%" style="border:1px solid;border-right:0px solid;border-top:0px solid;border-bottom:0px solid;vertical-align: top;"><strong>Date</strong></td>
						<td width="1%" style="border:1px solid;border-left:0px solid;border-right:0px solid;border-top:0px solid;border-bottom:0px solid;vertical-align: top;"><strong>:</strong></td>
						<td width="15%" style="border:1px solid;border-left:0px solid;border-top:0px solid;border-bottom:0px solid;vertical-align: top;">'.date("d/M/Y",strtotime($rel['invoice_date'])).'</td>
					</tr>
					<tr>
						<td width="15%" style="border:1px solid;border-right:0px solid;border-top:0px solid;vertical-align: top;"><strong>GSTIN No</strong></td>
						<td width="1%" style="border:1px solid;border-left:0px solid;border-right:0px solid;border-top:0px solid;vertical-align: top;"><strong>:</strong></td>
						<td width="44%" style="border:1px solid;border-left:0px solid;border-top:0px solid;vertical-align: top;"><strong>'.$cust_gst_no.'</strong></td>

						<td width="10%" style="border:1px solid;border-right:0px solid;border-top:0px solid;vertical-align: top;"><strong>Kind. Attn</strong></td>
						<td width="1%" style="border:1px solid;border-left:0px solid;border-right:0px solid;border-top:0px solid;vertical-align: top;"><strong>:</strong></td>
						<td width="15%" style="border:1px solid;border-left:0px solid;border-top:0px solid;vertical-align: top;"></td>
					</tr>
				</table>

			<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
			<thead>
			
			<tr>
			<th style="width:4%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
			<th style="width:9%;text-align:center;border:1px solid;">PRODUCT<br/>CODE</th>
			<th style="width:'.$desc_width.'%;text-align:center;border:1px solid;">DESCRIPTION</th>
			<th style="width:9%;text-align:center;border:1px solid;">HSN/SAC <br/>Code</th>
			
			<th style="width:9%;text-align:center;border:1px solid;">Qty </th>
			
			<th style="width:11%;text-align:center;border:1px solid;">PRICE</th>
			';
			
			if($disc_qrys['discount'] > 0){
				$html.='<th style="width:10%;text-align:center;border:1px solid;">DISC </th>';
			}
		
			$html.='<th style="width:5%;text-align:center;border:1px solid;">GST <br>%</br> </th>
			<th colspan="2" style="width:'.$desc_width.'%;text-align:center;border:1px solid;">AMOUNT</th>
			</tr>
			</thead>
			<tbody>';
			$trn_qry="select trn.*,product.*,unit_name,pro_hsn.hsn_code,pro_tax.tax_gst,drg.drawing_number FROM `tbl_proforma_trn` as trn 
			left join product_mst as product on product.product_id=trn.product_id 
			left join mst_hsn_code as pro_hsn on pro_hsn.hsn_id=product.product_hsn
			left join tbl_tax_category as pro_tax on pro_tax.tax_cat_id=pro_hsn.sale_gst
			left join tbl_drawing as drg  on drg.drawing_id=product.drawing_id
			left join unit_mst as per on per.unitid=trn.unit_id 
			where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trancation_id order by product_type,trancation_id";
			$trn_qry_rs=$dbcon->query($trn_qry);
			$p=1;$ttl_amt=0;$ttl_qty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;$total_qty=0;
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
					$total_qty = $total_qty + floatval($trn_rel['product_qty']);
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
				if(empty($trn_rel['drawing_number'])){
					$drgno=$trn_rel['product_icode'];
				}else{
					$drgno=$trn_rel['drawing_number'];
				}
				$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';

				$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
				<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
				<td style="text-align:center;border:1px solid;vertical-align:top;">
				'.$trn_rel['drawing_number'].'
				</td>
				<td  style="text-align:left;border:1px solid;vertical-align:top;">
				<strong>'.$trn_rel['product_name'].'</strong><br/>
				'.$trn_rel['product_desc'].'
				</td>
				<td style="text-align:center;border:1px solid;vertical-align:top;">
				'.$trn_rel['hsn_code'].'
				</td>
				
				<td style="text-align:right;border:1px solid;vertical-align:top;">
				'.$trn_rel['product_qty'].' '.$trn_rel['unit_name'].'
				</td>
				
				<td style="text-align:right;border:1px solid;vertical-align:top;">';
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

				$html.='</td>
				';

				
				if($disc_qrys['discount'] > 0){
					$html.='<td style="text-align:right;border:1px solid;vertical-align:top;">
					'.$currency_symbol.' '.$trn_rel['product_discount_conv'].'<br>('.$trn_rel['discount_per'].' %)
					</td>';
				}
				
				$html.='<td style="text-align:right;border:1px solid;vertical-align:top;">
				'.$trn_rel['tax_gst'].'%
				</td>
				<td colspan="2" style="text-align:right;border:1px solid;vertical-align:top;">';
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
			$pr=8-$cnt;
			for($j=0; $j<$pr; $j++)
			{
				$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
				<td  style="border:none;border-left:1px solid;border-right:1px solid;height:40px;"></td>
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			
				<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>';
				
				if($disc_qrys['discount'] > 0){
					$html.='<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>';
				}
			
				$html.='
				<td colspan="2"  style="border:none;border-left:1px solid;border-right:1px solid;"></td>
				</tr>';
			}

			$html.='<tr>
				<td style="text-align:left;border:1px solid;"></td>
				<td colspan="3" style="text-align:left;border:1px solid;text-align:right"><b>Total</b></td>
				
				<td style="text-align:left;border:1px solid;text-align:right"><b>'.$ttl_qty.'</b></td>
				<td style="text-align:left;border:1px solid;"></td>
				<td style="text-align:left;border:1px solid;"></td>
				<td style="text-align:left;border:1px solid;border-right:none"></td>';
				if($companyConfiguration['quot_revise_time_rate_with_discount']==0){
					if($disc_qrys['discount'] > 0){
						$html.='<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>';
					}
				}
				$html.='<td style="text-align:right;border:1px solid;border-left:0px"><b>'.$currency_symbol.' '.indian_number($ttl_amt,2).'</b></td>
				
			';

			$amt_word_span = 7;
			if($companyConfiguration['quot_revise_time_rate_with_discount']==0){
					if($disc_qrys['discount'] > 0){
						$amt_word_span = 8;
					}
				}

			
			$html.='

			<tr>
			<td rowspan="8" colspan="4" style="text-align:left;border:1px solid;vertical-align: top;">
				<b>Word In Amount: '.(($comp_rel['currency_id']==$rel['currency_id']) ? ucfirst(convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$currency_word_end,$currency_word_start)) : ucfirst(convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$currency_word_end,$currency_word_start))).'</b>
			</td>
			
			</tr>';


			if(!empty($total_cs_gst) || !empty($total_i_gst)){
				if($rel['c_add_state']==$comp_rel['stateid']){
					$html.='<tr>
					<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>CGST</b></td>
					<td style="text-align:right;border:1px solid;width:'.$amt_width.'%"><b>
					'.$currency_symbol.' '.number_format(($total_cs_gst/2),2,".","").'
					</b></td>
					</tr>
					<tr>
					<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>SGST</b></td>
					<td style="text-align:right;border:1px solid;"><b>
					'.$currency_symbol.' '.number_format(($total_cs_gst/2),2,".","").'
					</b></td>
					</tr>';
				}else{
					$html.='<tr>
					<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;border-bottom:none;"></td>
					<td style="text-align:right;border:1px solid;border-bottom:none;width:'.$amt_width.'%">
					
					</td>
					</tr>'; $html.='<tr>
					<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;border-bottom:none;border-top:none;"></td>
					<td style="text-align:right;border:1px solid;border-bottom:none;border-top:none;">
					
					</td>
					</tr>';
					 $html.='<tr>
					<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;border-bottom:none;border-top:none;"></td>
					<td style="text-align:right;border:1px solid;border-bottom:none;border-top:none;">
					
					</td>
					</tr>';
					$html.='<tr>
					<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;border-top:none;"><b>IGST</b></td>
					<td style="text-align:right;border:1px solid;border-top:none;"><b>
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
				<td style="text-align:right;border:1px solid;"><b>
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
				<td style="text-align:right;border:1px solid;"><b>
				'.$currency_symbol.' '.number_format($row12['sundry_amount_conv'],2,".","").'
				</b></td>
				</tr>';
			}
			$html.='<tr>
			<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>Total Amount
			</td>
			<td style="text-align:right;border:1px solid;"><b>
			'.$currency_symbol.' '.indian_number($rel['g_total_conv'],2).'
			</b></td>
			</tr>
			';
			$html.='</tbody></table>';

			$html .='<table style="font-size:12px;border-collapse: collapse;width:100%;" >
			<thead>
			
			<tr>
				<td width="20%" style="border-left:1px solid;"><strong>Name of the Bank</strong></td>
				<td  width="1%" style="border:0px solid;">:</td>
				<td width="34%" colspan="'.($colspan-2).'" style="border:0px solid;">'.$comp_rel["bank_name"].'</td>
				<td width="35%" colspan="4" rowspan="3" style="border-right:1px solid;text-align: right;vertical-align: top;"><strong>For '.$comp_rel["company_name"].'</strong></td>
			
			</tr>
			<tr>
				<td  style="border-left:1px solid;"><strong>Branch</strong></td>
				<td  style="border:0px solid;">:</td>
				<td colspan="'.($colspan-2).'" style="border:0px solid;">'.$comp_rel["branch_name"].'</td>
				
			
			</tr>
			<tr>
				<td  style="border-left:1px solid;"><strong>Current Account No.</strong></td>
				<td  style="border:0px solid;">:</td>
				<td colspan="'.($colspan-2).'" style="border:0px solid;">'.$comp_rel["ac_no"].'</td>
				
			
			</tr>
			<tr>
				<td  style="border-left:1px solid;border-bottom:1px solid;"><strong>IFSC Code</strong></td>
				<td  style="border:0px solid;border-bottom:1px solid;">:</td>
				<td colspan="'.($colspan-2).'" style="border:0px solid;border-bottom:1px solid;">'.$comp_rel["ifcs"].'</td>
				<td colspan="4" style="border-right:1px solid;text-align: right;border-bottom:1px solid;"><strong>Authoriser Signatory</strong></td>
			
			</tr>
			</tbody></table>';



			$html.='</div>';
			//$html.='';
			if(!empty($rel['terms_condition'])){
				$string=(nl2br($rel['terms_condition']));
				$html.='<center class="nextpage"></center><div> 
				<table style="font-size:20px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
					<tr>
						<td style="text-align:left; border:0px solid;border-bottom:none;border-right:none;width:60%;font-size:14px;"><b>Terms & Conditions</b></td>
					</tr>
					<tr>
						<td width="25%" style="width:25%;text-align:left;border-left:0px solid;border-right:none;padding:5px; vertical-align: top;font-size:12px;'.$bottom_bdr.'">'.$string.'</td>
					</tr>
					</table>
				';
			}
			$terms_qry="select qtrm.*,mst.tc_name from tbl_proforma_terms_trn as qtrm 
			left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
			where qtrm.proforma_terms_trn_status=0 and qtrm.proforma_id=".$invoiceid." order by qtrm.tc_priority";
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
			
			/* Get Terms And Condition Start */
			
			/*$rowspanterms = 4;
			
			$bottom_bdr = '';
			
				$html.='<div> 
				<table style="font-size:20px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
				<tbody>
				<tr>
				<td style="text-align:left; border:1px solid;border-bottom:none;border-right:none;width:60%;font-size:14px;"><b>Terms & Conditions</b></td>
				
				<td rowspan="'.($rowspanterms+1).'" style="text-align:center; border:1px solid;width:40%;border-left:none;font-size:16px;">With Best Regards, <br><br><br><br> <b> Jainflex Cables Pvt Ltd </b><br>
					<div style="text-align:left">
						<p> '.$userData['user_name'].' - '.$userData['usertype_name'].' </p>
						<p> Mobile No. : '.$userData['user_phone'].' </p>
						<p> Email ID : '.$userData['user_mail'].' </p>
					</div>
				</td>
				</tr>
				';
				if(!empty($rel['terms_condition'])){
				
				$bottom_bdr = 'border-bottom:1px solid;';
				
				$string=(nl2br($rel['terms_condition']));
					$html.='<tr> 
					<td width="25%" style="width:25%;text-align:left;border-left:1px solid;border-right:none;padding:5px; vertical-align: top;font-size:12px;'.$bottom_bdr.'">'.$t.'. &emsp;'.$string.'</td>
					</tr>';
					$t++;
				
			}else{
				$html.='<tr> 
					<td width="25%" style="width:25%;text-align:left;border-left:1px solid;border-right:none;padding:5px; vertical-align: top;font-size:12px;'.$bottom_bdr.'">1. &emsp; Goods dispatched in transport on your behalf. We are not responsible for any damage or loss.</td>
					</tr>';
				$html.='<tr>
					<td width="25%" style="width:25%;text-align:left;border-left:1px solid;border-right:none;padding:5px; vertical-align: top;font-size:12px;'.$bottom_bdr.'">2. &emsp; We declare that the particulars mentioned
in above invoice are true and correct.</td>
					</tr>';
				$html.='<tr>
					<td width="25%" style="width:25%;text-align:left;border-left:1px solid;border-right:none;padding:5px; vertical-align: top;font-size:12px;'.$bottom_bdr.'">3. &emsp; Guage tolerance.</td>
					</tr>';
				$html.='<tr>
					<td width="25%" style="width:25%;text-align:left;border-left:1px solid;border-right:none;padding:5px; vertical-align: top;font-size:12px;'.$bottom_bdr.'">4. &emsp; Quantity Tolerance : + 5%.</td>
					</tr>';
			}*/
			$html.='</tbody></table>'; 
			/* Check Annexure Attachments Start */
			if(trim($rel['quot_annex_content'])){
				//$html.='<center class="nextpage"></center>';
				//$html.='<div class="quot_annex_content_div" style="font-size: 16px;">'.$rel['quot_annex_content'];
				//$html.='</div>';
			}
			/* Check Annexure Attachments End */
			/*if(!empty($quotation_footer_content)){
				$html.='<br /><br /><div style="text-align:center;">'.$quotation_footer_content;
				$html.='</div>';
			}*/

			
			$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
			</body>
			</html>';
// //echo $trn_qry;
//echo $header; exit();
 //echo $html;exit;
			ob_end_clean();
			$file_name = $rel['quotation_no'].'.pdf';
			$file_name=str_ireplace("/","_",$file_name);
			if($save_file=="No"){
				include("../../view/export/mpdf/mpdf.php");
			}else{
				include("../../../view/export/mpdf/mpdf.php");
			}
			$mpdf=new mPDF('','A4','0','calibri','10','10','35','10','1','1');
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