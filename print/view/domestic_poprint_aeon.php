<?php session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once("../../crm/include/crm_common_functions.php");
//include_once(COMMON_FUNCTION_PATH."common_production_functions.php");
$_SESSION['contents']=''; 
$form="Purchase Order";
$mode="Print";
$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
$type='pdf';
if(strtolower($type) == 'pdf') {
//Quotation Data
		$query="select po.*,state.state_name,modesup.mode_dispatch,payterms.payment_terms as payment_term,l.l_name as vender_name,country.country_name,l.m_address as vender_address,l.ledger_code,l.cust_cont_name,l.gst_no as tin_no,l.cust_mobile as vender_mobile,l.stateid,state.gst_state_code,city.city_name,bran.branch_address,brnc.country_name as branch_country,brns.state_name as branch_state,brnci.city_name as branch_city,branz.zone_name,bran.branch_pincode,comp.company_name,le.l_name as con_ven,bm.branch_name as cons_bran,cur.currency_code,cur.currency_symbol,cur.currency_in_word,cur.currency_in_word_end from tbl_purchaseorder as po 
	left join tbl_ledger as l on l.l_id=po.vender_id
	left join country_mst as country on country.countryid=l.countryid
	left join pay_terms as payterms on payterms.terms_id=po.payment_terms
	left join mode_of_dispatch as modesup on modesup.mode_dis_id=po.mode_of_dispatch
	left join state_mst as state on state.stateid=l.stateid
	left join city_mst as city on city.cityid=l.cityid
	left join branch_mst as bran on bran.branch_id=po.branch_id
	left join country_mst as brnc on brnc.countryid = bran.countryid
	left join state_mst as brns on brns.stateid = bran.stateid
	left join city_mst as brnci on brnci.cityid = bran.cityid
	left join zone_mst as branz on branz.zone_id = bran.zoneid
	left join tbl_company as comp on comp.company_id = po.company_id
	left join tbl_ledger as le on le.l_id = po.con_vender_id
	left join tbl_currency as cur on cur.currency_id = po.currency_id
	left join branch_mst as bm on bm.branch_id = po.con_branch
	where po.purchaseorder_id=$purchaseorder_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$delivery_type = $rel['delivery_type'];
	$_SESSION['invoice_no']=$rel['invoice_no'];		

	$order_date='';
	if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
	{
		$order_date=date('d-m-Y',strtotime($rel['order_date']));
	}

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	
	if($rel['cons_same_as']==0){
		if($rel['con_type'] ==1){
			$cons_name = $rel['cons_bran'];
		}else if($rel['con_type'] ==2){
			$cons_name = $rel['con_ven'];
		}else{
			$cons_name = $rel['company_name'];
		}

		$consignee_address = $rel['con_address'];
		$party_address_con = '<strong>'.$cons_name.'</strong><br><br>'.$consignee_address;
	}else{
		$cons_name 		   = $rel['company_name'];
		$consignee_address = $set_head['address'];
		$party_address_con = '<strong>'.$cons_name.'</strong><br><br>'.$consignee_address;
	}
	/*if(!empty($rel['branch_address'])){
		$branch_address = $rel['branch_address'].'<br/>'.$rel['branch_country'].','.$rel['branch_state'].','.$rel['branch_city'].'<br/> Pincode : '.$rel['branch_pincode'];
	}else{
		$branch_address = $set_head['address'];
	}

	$cons_company_name	= $rel['company_name'];
	$cons_cust_address	= $rel['cust_address'];
	$cons_gst_no		= $rel['gst_no'];
	$cons_state_name	= $rel['state_name'];
	$cons_gst_state_code= $rel['gst_state_code'];
	$cons_city_name		= $rel['city_name'];
	$cons_country_name	= $rel['country_name'];

	if(!empty($rel['consignee_id']))
	{	
		$consignee="select * from tbl_custmer_consignee as cust 
		left join country_mst as country on country.countryid=cust.countryid
		left join state_mst as state on state.stateid=cust.stateid 
		left join city_mst as city on city.cityid=cust.cityid where cust_id=".$rel['consignee_id'];
		$cons_data=mysqli_fetch_assoc($dbcon->query($consignee));	
		$cons_company_name=$cons_data['company_name'];
		$cons_cust_address=$cons_data['cust_address'];
		$cons_gst_no=$cons_data['gst_no'];
		$cons_state_name=$cons_data['state_name'];
		$cons_gst_state_code=$cons_data['gst_state_code'];
		$cons_city_name=$cons_data['city_name'];
		$cons_country_name=$cons_data['country_name'];

	}*/
	$user_qry = "select user.user_name, user.user_mail, user.user_phone, user.user_type, led.common_email_id from users as user
	left join tbl_ledger as led on led.l_id=user.employee_id
	where user.user_id=".$_SESSION['user_id']." and user.company_id=".$rel['company_id'];
	$user_data = mysqli_fetch_assoc($dbcon->query($user_qry));
	/* Check Discount is On or off Start */
	if($set_head['show_disc']=='1'){
		$colspan=5;
		$dynamicwidth=40;
	}else{
		$colspan=6;
		$dynamicwidth=46;
	}
	$companyConfiguration=getCompanyConfiguration($dbcon);

	if($user_data['user_type'] != 2 ){
		$usermail = $user_data['common_email_id'];
	}else{
		$usermail = $set_head['common_email_id'];
	}
	
	$quotation_no=($rel['quotation_no']) ? $rel['quotation_no'] : '';
	$quotation_date=($rel['quotation_date']) ? date('d-M-Y', strtotime($rel['quotation_date'])) : '';
	
//$header ='<div style="text-align:right;"><img src="'.DOMAIN_F.LOGO.'Hermettic_Equipments.png" style="width:8.27in;padding-top:20px;" /></div>';  
	$header ='<img src="'.DOMAIN_F.LOGO.$set_head['logo'].'" style="width: 100%" />';

	$approve_status='';
	if($rel['approve_status']=='0'){
		$approve_status=' (DRAFT)';
	}
	$sundrytax=$dbcon->query("select b.* from tbl_bill_sundry_transaction as b where b.sundry_voucher_id=".$rel['purchaseorder_id']." and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' ");
//$sundrytax_rels = mysqli_fetch_assoc($sundrytax);
	$total_sundrytax=0;
	while($sumsundrytax=mysqli_fetch_assoc($sundrytax)){
		$total_sundrytax = $total_sundrytax + $sumsundrytax['sundry_gst_amount'];
	}
	$qry_disc=$dbcon->query("select SUM(trn.product_discount) as discount FROM `tbl_purchaseordertrn` as trn WHERE trn.purchaseordertrn_status=0 and purchaseorder_id=".$rel['purchaseorder_id']);
	$get_disc = brp_mysqli_fetch_assoc($qry_disc);
	if($companyConfiguration['po_work_order_wise']==1){
		$sales_order_no = in_po_sales_order_no($dbcon,$rel['purchaseorder_id']);
		$ros = 6;
	}else{
		$ros = 5;
	}
	if($rel['po_approval_status']==0){
		$status = "Approval Pending";
	}else if($rel['po_approval_status']==1){
		$status = "Approved";
	}else if($rel['po_approval_status']==2){
		$status = "Disapproved";
	}else if($rel['po_approval_status']==3){
		$status = "Finance Pending";
	}else if($rel['po_approval_status']==4){
		$status = "Finance Disapproved";
	}


	$html ='<html>
	<head>					
	<title>'.$form.' - '.$rel['purchaseorder_no'].'</title>
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
		br{

		}

		</style>
		</head>
		<body>

		<htmlpageheader name="otherpages" style="display:none">
		<div style="text-align:center">'.$header.'</div>
		</htmlpageheader>
		<!--<htmlpagefooter name="otherpages_footer" style="display:none">
		<div style="text-align:center">'.$footer.'</div>
		</htmlpagefooter>-->
		<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
		<div>
		<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
		<tr style="border:none">
			<td colspan="3" style="text-align:right; font-size:15px; font-weight:bold;border:none">'.$status.'</td>
		</tr>
		<tr>
		<td colspan="3" style="text-align:center; font-size:15px; font-weight:bold;border-right:none">'.$form.'</td>
		</tr>
		<tr>
		<td rowspan="'.$ros.'" style="text-align:left; vertical-align:top; border:1px solid; width:50%;">
		<strong>To, <br>'.$rel['vender_name'].'</strong><br/>'.$rel['vender_address'].'<br/>'.$rel['city_name'].','.$rel['state_name'].','.$rel['country_name'].'<br>GST NO. : '.$rel['tin_no'].'<br>Kind Attn. : '.$rel['cust_cont_name'].'
		</td>
		<td style="text-align:left;border:1px solid;width:20%;"><strong>Purchase Order No</strong></td>
		<td style="text-align:left;border:1px solid;width:30%;font-size:14px"><strong>'.$rel['purchaseorder_no'].'</strong></td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;">Purchase Order Date</td>
		<td style="text-align:left;border:1px solid;"> '.date("d-M-Y",strtotime($rel['purchaseorder_date'])).'</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Quotation Ref No</td>
		<td style="text-align:left;border:1px solid;"> '.$quotation_no.'</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Quotation Ref Date</td>
		<td style="text-align:left;border:1px solid;"> '.$quotation_date.'</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Vendor Code</td>
		<td style="text-align:left;border:1px solid;">'.$rel['ledger_code'].'</td>
		</tr>';
		if($companyConfiguration['po_work_order_wise']==1){
			$html .='<tr>
			<td style="text-align:left;border:1px solid;">Project Code</td>
			<td style="text-align:left;border:1px solid;">'.$sales_order_no.'</td>
			</tr>';
		}
			
		$html.='<tr style="border-bottom: none;">
		<td rowspan="5" style="text-align:left; vertical-align:top; border:1px solid; width:50%;border-bottom: none;">
		<strong>Ship To, <br>'.$party_address_con.'
		</td>
		
		<td style="text-align:left;border:1px solid;">PO Valid Till</td>
		<td style="text-align:left;border:1px solid;">'.date("d-M-Y",strtotime($rel['po_valid_date'])).'</td>
		</tr>
		
		<tr>
		<td style="text-align:left;border:1px solid;">Delivery Date</td>';
		if($delivery_type == 'product_wise'){
			$html .='<td style="text-align:left;border:1px solid;">As Per Product Description</td>';
		}else{
			$html .='<td style="text-align:left;border:1px solid;"> '.date("d-M-Y",strtotime($rel['purchaseorder_due_date'])).'</td>';
		}
		$html .='</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Payment Terms</td>
		<td style="text-align:left;border:1px solid;"> '.$rel['payment_term'].'</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Buyers Name</td>
		<td style="text-align:left;border:1px solid;"> '.$user_data['user_name'].'</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Buyers Mobile No</td>
		<td style="text-align:left;border:1px solid;"> '.$user_data['user_phone'].'</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;border-top: none;">'.$set_head['company_name'].' GST No. : '.$set_head['vatno'].'</td>
		<td style="text-align:left;border:1px solid;"> Buyers Email</td>
		<td style="text-align:left;border:1px solid;"> '.(strtolower($usermail)).'</td>
		</tr>
		<tr style="border-bottom: none;">
		<td colspan="3"><strong> We are pleased to place this Purchase/ Service Order for the supply of the following, subject to the terms and conditions given in annexure. </strong></td>
		</tr>
		</table>
		</div>
		';
		$html.='<div>
				<table style="font-size:12px;border-collapse: collapse;width:100%; border:1px solid" cellpadding="3" cellspacing="3">
				<thead>
				<tr>
				<th style="width:5%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
				<th style="width:45%;text-align:center;border:1px solid;">Item Description</th>
				<th style="width:5%;text-align:center;border:1px solid;">HSN Code</th>
				<th style="width:8%;text-align:center;border:1px solid;">Qty</th>
				<th style="width:8%;text-align:center;border:1px solid;">Unit Price</th>';
				/*if($get_disc['discount'] > 0.00){ 
					$html.='<th style="width:7%;text-align:center;border:1px solid;">Disc.</th>';
				}
				$html.='<th style="width:10%;text-align:center;border:1px solid;">Discounted Price</th>';*/
				$html.='<th style="width:5%;text-align:center;border:1px solid;">GST(%)</th>
				<th style="width:8%;text-align:center;border:1px solid;">GST Amount</th>
				<th style="width:10%;text-align:right;border:1px solid;">Total Price</th>
				</tr>
				</thead>
				<tbody> ';
		$qry="select trn.*,trn.product_qty as sh_qty,trn.product_conv_qty as sh_conv_qty,product.*,product.product_desc as scode,per.unit_name,per1.unit_name as base_unit_name,per2.unit_name as conv_unit_name FROM `tbl_purchaseordertrn` as trn 
		left join product_mst as product on product.product_id=trn.product_id 
		left join unit_mst as per on per.unitid=trn.unit_id 
		left join unit_mst as per1 on per1.unitid=product.product_base_unit 
		left join unit_mst as per2 on per2.unitid=product.product_conv_unit
		where purchaseordertrn_status=0 and purchaseorder_id=".$rel['purchaseorder_id']."  order by purchaseordertrn_id asc";
		/*echo $qry;exit;*/
		$result=$dbcon->query($qry);		
		$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$pcount=1;$total_cs_gst=0;$total_i_gst=0;$gst_per=0;$gst_rate=0;
		$cnt=mysqli_num_rows($result);
		while($row=mysqli_fetch_assoc($result))
		{
			if($row['unit_id']!=$row['conv_unit_id']){
			        //base_unit_name,per2.unit_name as conv_unit_name
				if($row['unit_id']==$row['product_base_unit']){
					$cqty=convert_stock($dbcon,$row['sh_qty'],$row['product_id'],"conv_unit");
					$uname=$row['conv_unit_name'];
				}else{
					$cqty=convert_stock($dbcon,$row['sh_qty'],$row['product_id'],"base_unit");
					$uname=$row['base_unit_name'];
				}
			}else{
				$uname=$row['unit_name'];
			}

			if($row['rate_unit'] != $row['unit_id']){
				$pqty = number_format($row['sh_conv_qty'],2,".","").' '.$row['conv_unit_name'];
			}else{
				$pqty = number_format($row['sh_qty'],2,".","").' '.$row['base_unit_name'];	
			}

			$gst_per = $row['cgst_tax_per']+$row['sgst_tax_per']+$row['igst_tax_per'];
			$gst_rate = $row['cgst_tax_rate_conv']+$row['sgst_tax_rate_conv']+$row['igst_tax_rate_conv']+$total_sundrytax;

			if($row['cgst_tax_rate_conv'] != 0 || $row['sgst_tax_rate_conv'] !=0){
				$total_cs_gst += $gst_rate;
			}else{
				$total_i_gst += $gst_rate;
			}

			$taxable_amt=$row['currency_total']-$row['product_currency_amount'];
			$disc_rate = ($row['product_currency_rate'] * $row['discount_per'])/100;
			$product_rate = $row['product_currency_rate'] - $disc_rate;
			$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
			<td style="text-align:center;border:1px solid;vertical-align:top;">'.$i.'</td>
			<td style="text-align:left;border:1px solid;vertical-align:top;">
			<strong>'.$row['product_name'].'</strong>';
			$html.= '<br>'.$row['product_des'].'<br>';
			if($delivery_type == 'product_wise'){
				$retu_date = "select sdate.*,unit.unit_name from tbl_purchaseorder_delivery_date as sdate left join unit_mst as unit on unit.unitid=sdate.unit_id where po_delivery_date_status=0 and purchaseordertrn_id=".$row['purchaseordertrn_id'];
				$resadate=$dbcon->query($retu_date);

				
				$c = 0;
				while($rowdate=brp_mysqli_fetch_array($resadate)){	
					if($c>0){
						$html .="/";
					}	
					$html .=' <strong>D.Date : '.date('d-m-Y',strtotime($rowdate['delivery_date'])).' Product Qty :'.number_format($rowdate['product_qty'],2,".","").' '.$rowdate['unit_name'].'</strong> ';
					
					$c++;		
				}
			}
			
			$html .='</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">'.$row['product_hsn_code'].'</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			'.$pqty.'
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;"> '.number_format($product_rate,2,".","").'</td>';
			/*if($get_disc['discount'] > 0.00){
				$html.='<td style="text-align:center;border:1px solid;vertical-align:top;">'.number_format($row['discount_per'],2,".","").' %</td>';
			}
			$html.='<td style="text-align:center;border:1px solid;vertical-align:top;">'.number_format($row['product_discount'],2,".","").'</td>';*/
			$html.='<td style="text-align:center;border:1px solid;vertical-align:top;">'.$gst_per.' %</td>
			<td style="text-align:right;border:1px solid;vertical-align:top;">'.number_format($gst_rate,2,".","").'</td>
			<td style="text-align:right;border:1px solid;vertical-align:top;">'.number_format($row['currency_total'],2,".","").'</td>
			</tr>';


			$totalqty=$totalqty+$row['sh_qty']-$charges_qty;$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
			$total_product_amount+=$row['product_currency_amount'];
			$totaltaxable+=$gst_rate;
			$total+=$row['currency_total'];
			$cols = ($get_disc['discount'] > 0) ?  3 : 3;

			$i++; 
		}

		$pr = 2-$cnt;
			
		for($j=0; $j<$pr; $j++){
			$html .= '<tr style="border:none;border-left:1px solid;border-right:1px solid;height:30px">
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
			</tr>';
		}
			 
		$pr = 3-$cnt;
			
		for($j=0; $j<$pr; $j++){
			$html .= '<tr style="border:none;border-left:1px solid;border-right:1px solid;height:20px">
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
				<td style="text-align:center;border:1px solid;vertical-align:top;"></td>
			</tr>';
		}
		$html .= '</table></div><div>';
		////////////////////////////////////////////////////////////////////Tax Calculation Start - Harshil//////////////////////////////////////////////////
		$sub_total=0; $bill_sundry=0;
		$html.='<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
			<tbody>
			<tr>
			
			<td colspan="2" style="text-align:right;border:1px solid; font-weight: bold;">Total Basic</td>
			<td style="text-align:right;border:1px solid;font-weight: bold;width:25%">'.number_format($total_product_amount,2,".","").'</td>
		</tr>';
		
		$qry11="select sum((tc.tax_per*trn.product_currency_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_purchaseordertrn as trn 
				left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
				left join tbl_ledger as l on l.l_id=tc.tax_id 
				where tc.tax_additional='1' and trn.purchaseorder_id=".$rel['purchaseorder_id']." and trn.purchaseordertrn_status!=2 and tc.isdelete='0' group by tc.tax_id";
				$qry12="select b.sundry_amount_conv as sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
				from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
				left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
				where b.sundry_voucher_id=".$rel['purchaseorder_id']." and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' and le.default_sundry='0'";
		$result11=$dbcon->query($qry11);
		$result12=$dbcon->query($qry12);
		$rows = brp_mysqli_num_rows($result11) + brp_mysqli_num_rows($result12);
				//echo $rel['stateid'];
				//echo $comp_rel['stateid'];
		if($rel['stateid']==$set_head['stateid']){
			
			while($row12=mysqli_fetch_assoc($result12))
				{
					$html.='<tr>
						
						<td colspan="2" style="text-align:right;border:1px solid; font-weight: bold;">'.$row12['l_name'].'</td>
						<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format($row12['sundry_amount'],2,".","").'</td>
						</tr>';
						$bill_sundry=$bill_sundry+number_format($row12['sundry_amount'],2,".","");
				}
				
				while($row11=mysqli_fetch_assoc($result11))
				{
					$html.='<tr>
						
						<td colspan="2" style="text-align:right;border:1px solid; font-weight: bold;">'.$row11['l_name'].'</td>
						<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format($row11['add_sum'],2,".","").'</td>
						</tr>';
				}
					
			 $sub_total=$bill_sundry+$total_product_amount;
			$html.='<tr>
				
				<td colspan="2" style="text-align:right;border:1px solid; font-weight: bold;">Sub Total </td>
				<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format(($sub_total),2,".","").'</td>
			</tr>';
			
			if($rel['quot_type']==0){
				$html.='<tr>
					
					<td colspan="2" style="text-align:right;border:1px solid; font-weight: bold;">CGST </td>
					<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format(($total_cs_gst/2),2,".","").'</td>
				</tr>
				<tr>
					
					<td colspan="2" style="text-align:right;border:1px solid; font-weight: bold;">SGST </td>
					<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format(($total_cs_gst/2),2,".","").'</td>
				</tr>';
			}	
				
		}else{
			
			
			
			while($row12=mysqli_fetch_assoc($result12))
			{
				$html.='<tr>
						
						<td colspan="2" style="text-align:right;border:1px solid; font-weight: bold;">'.$row12['l_name'].'</td>
						<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format($row12['sundry_amount'],2,".","").'</td>
						</tr>';
						$bill_sundry=$bill_sundry+number_format($row12['sundry_amount'],2,".","");
			}
			
			
			
			
			
			while($row11=mysqli_fetch_assoc($result11))
			{
				$html.='<tr>
					<td colspan="" style="text-align:left;border:1px solid;color:Black; font-weight: bold;width:50%"></td>
					<td colspan="" style="text-align:right;border:1px solid; font-weight: bold;">'.$row11['l_name'].'</td>
					<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format($row11['add_sum'],2,".","").'</td>
				</tr>';
			}
			
			
			 $sub_total=$bill_sundry+$total_product_amount;
			$html.='<tr>
				
				<td colspan="2" style="text-align:right;border:1px solid; font-weight: bold;">Sub Total </td>
				<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format(($sub_total),2,".","").'</td>
			</tr>';

			if($rel['quot_type']==0){
				$html.='<tr>
					
					<td colspan="2" style="text-align:right;border:1px solid; font-weight: bold;">IGST </td>
					<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format(($total_i_gst),2,".","").'</td>
				</tr>';
			}	
		}
		$html.='<tr>
				
				<td colspan="2" style="text-align:right;border:1px solid; font-weight: bold;">Round Off</td>
				<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format($rel['round_of'],2,".","").'</td>
				</tr>
				<tr>
				
				<td colspan="2" style="text-align:right;border:1px solid; font-weight: bold;">Total Amount</td>
				<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format($rel['g_total_conv'],2,".","").'</td>
				</tr>
				<tr>
				
				
				<td colspan="3" style="border-top:1px solid;text-align:left;"><b>Amount in words : 
			'.(($set_head['currency_id']==$rel['currency_id']) ? ucfirst(convert_number_to_words_new($rel['g_total_conv'],$rel['currency_in_word'],$rel['currency_in_word_end'])) : ucfirst(convert_number_to_words($rel['g_total_conv'],$rel['currency_in_word_end']))).'</b></td>
			
				</tr>
				
				</tbody></table>';
				
	///////////////////////////////////////////////////////////////////Tax Calculation End////////////////////////////////////////////////////////			
				
				$html.='<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3"><tr style="">
				<td colspan="" rowspan="0" style="height:80px; width:50%;  text-align:left; vertical-align:top; border-left:1px solid; border-bottom:1px solid !important; font-weight: bold;"><strong>Remarks:</strong><br> '.$rel['remark'].'</td>
				<td colspan="" style="text-align:center;vertical-align:top;border:0px !important;font-weight: bold;height:80px; width:50%;">For, '.$set_head['company_name'].'
				<div style="margin-top:50px !important"><img src="'.DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'].'" style="height: 100px; width: 180px;"><br> Authorised Signatory</div>
				</td>
				</tr>
				


				<!--page1 end-->';
				$html.='</table>
				</div>
				<div style="clear:both;"></div>
				</div>';
			$terms_qry="select qtrm.*,mst.tc_name from tbl_purchaseorder_terms_trn as qtrm 
        left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
        where qtrm.po_terms_trn_status=0 and qtrm.purchaseorder_id=".$rel['purchaseorder_id']." order by qtrm.tc_priority";
        $terms_qry_rs=$dbcon->query($terms_qry);
       if(brp_mysqli_num_rows($terms_qry_rs)){
			
				$html.=' <center class="nextpage"></center>';

		
		//$html .= '<center class="nextpage"></center>';
		
		
        $html.='
        <h3 style="text-align:center;">Terms & Conditions for Purchase Order No : <u>'.$rel['purchaseorder_no'].'</u></h3>
        <div><table width="100%" style="font-size:12px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3"><tbody>';
        	$t=1;
        	while($term_rel=mysqli_fetch_array($terms_qry_rs)){
        	    $string=(nl2br($term_rel['tc_details']));
                
        		$html.='<tr>
        			<td width="5%" style="width:5%;text-align:center;border:1px solid;padding:5px;">'.$t.'</td>
        			<td width="25%" style="width:25%;text-align:left;border:1px solid;padding:5px;">'.$term_rel['tc_name'].'</td>
        			<td width="70%" style="width:70%;text-align:left;border:1px solid #000;padding:5px;">'.$string.'</td>
        		</tr>';
        		$t++;
        	}
        	  $html .="</table></div>";
       }

       $pro_im ="select trn.*, trn.cat_id as product_cat, trn.product_qty as sh_qty, trn.product_conv_qty as sh_conv_qty, product.*, product.product_desc as scode, per.unit_name,per1.unit_name as base_unit_name, per2.unit_name as conv_unit_name,ca.cat_name FROM `tbl_purchaseordertrn` as trn 
		left join product_mst as product on product.product_id=trn.product_id 
		left join unit_mst as per on per.unitid=trn.unit_id 
		left join tbl_category as ca on ca.cat_id = trn.cat_id
		left join unit_mst as per1 on per1.unitid=product.product_base_unit 
		left join unit_mst as per2 on per2.unitid=product.product_conv_unit
		where purchaseordertrn_status=0  and purchaseorder_id=".$rel['purchaseorder_id']."  order by purchaseordertrn_id asc";

		$pro_im_rs=$dbcon->query($pro_im);
		$i=1;
		while($row_im=brp_mysqli_fetch_array($pro_im_rs)){
			 if ($row_im['pro_spe']!="" || $row_im['image_name']!=""){
                if($i==1){
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
				}	
				if(!empty($row_im['product_cat'])){
					$ros = "3";
				}else{
					$ros = "2";
				}
				$img='';
				if(!empty($row_im['image_name'])){
					$img  = '<img src="'.DOMAIN_F.'view/upload/product_images/'.$row_im['image_name'].'" style="width:250px;height:250px">';
				}
				$html.='<tr>
					<td rowspan="'.$ros.'">'.$i.'</td>
					<td rowspan="'.$ros.'">'.$img.'</td>';
				if(!empty($row_im['product_cat'])){	
					$html.=	'<td style="height:35px"><strong>'.$row_im['cat_name'].'</strong></td>';
				}else{
					$html.='<td style="height:35px"><strong>'.$row_im['product_name'].'</strong></td>';
				}
				$html.='</tr>';
				
				if(!empty($row_im['product_cat'])){
					$html.='<tr>
						<td style="height:35px"><strong>'.$row_im['product_name'].'</strong></td>
					</tr>';
					
					$html.='<tr>
						<td>'.$row_im['pro_spe'].'</td>
					</tr>';
				}else{
					$html.='<tr>
						<td>'.$row_im['pro_spe'].'</td>
					</tr>';
				}
				
				
				$i++;
			}
		}
			/*echo $html;exit;*/
       $html.='</tbody></table></div>';
		/*if(!empty($rel['po_condition'])){
			$html.='<center class="nextpage"></center>
			<h3 style="text-align:center;">Terms & Conditions</u></h3>
			<div>
			<table width="100%" style="font-size:12px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3">
			<tbody>
			<tr style="border:none;">
			<td width="100%" style="width:100%;text-align:left;border:none;padding:5px;">'.$rel['po_condition'].'</td>
			</tr>
			</tbody>
			</table>
			</div>';	
		}*/
		/* Get Terms And Condition Start */

		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
		//echo $html;exit;
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','40','10','1','1');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		//$mpdf->SetHTMLFooter($footer);

		//Show page number
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
		$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$purchaseorder_id.'.pdf','f');
		ob_clean();
		return 'purchase_order_'.$purchaseorder_id.'.pdf';
	}

?>