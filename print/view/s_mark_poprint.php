<?php session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$_SESSION['contents']=''; 
$form="Purchase Order";
$mode="Print";
$type='pdf';
// print_r($_GET);
if(strtolower($type) == 'pdf') {
	$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select po.*,state.state_name,modesup.mode_dispatch,payterms.payment_terms as payment_term,l.l_name as vender_name,country.country_name,l.m_address as vender_address,l.gst_no as tin_no,l.cust_mobile as vender_mobile, l.stateid,l.cust_email as vender_email, state.gst_state_code, city.city_name, bmast.branch_address, city1.city_name as bcity, state1.state_name as bstate, country1.country_name as bcountry, l.emp_signature_img,conper.cust_contact_person_name,conper.cust_contact_person_no,conper.cust_contact_person_email from tbl_purchaseorder as po 
	left join tbl_ledger as l on l.l_id=po.vender_id
	left join country_mst as country on country.countryid=l.countryid
	left join pay_terms as payterms on payterms.terms_id=po.payment_terms
	left join mode_of_dispatch as modesup on modesup.mode_dis_id=po.mode_of_dispatch
	left join state_mst as state on state.stateid=l.stateid
	left join city_mst as city on city.cityid=l.cityid
	left join branch_mst as bmast on bmast.branch_id=po.branch_id
	left join country_mst as country1 on country1.countryid=bmast.countryid
	left join state_mst as state1 on state1.stateid=bmast.stateid
	left join city_mst as city1 on city1.cityid=bmast.cityid
	left join tbl_cust_contact_person as conper on conper.cust_contact_person_id=po.kind_attn
	where po.purchaseorder_id=$purchaseorder_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$delivery_type = $rel['delivery_type'];
	$_SESSION['invoice_no']=$rel['invoice_no'];		

	if(!empty($rel['branch_address'])){
		$baddress=$rel['branch_address'];
	}
	$cons_city_name1=$rel['bcity'];
	$cons_state_name1=$rel['bstate'];
	$cons_country_name1=$rel['bcountry'];

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	$purchaseorder_date='';
	if($rel['purchaseorder_date']!="1970-01-01" && $rel['purchaseorder_date']!="0000-00-00")
	{
		$purchaseorder_date=date('d-m-Y',strtotime($rel['purchaseorder_date']));
	}
	if($rel['purchaseorder_due_date']!="1970-01-01" && $rel['purchaseorder_due_date']!="0000-00-00")
	{
		$purchaseorder_due_date=date('d-m-Y',strtotime($rel['purchaseorder_due_date']));
	}
	$quotation_date='';
	if($rel['quotation_date']!="1970-01-01" && $rel['quotation_date']!="0000-00-00")
	{
		$quotation_date=date('d-m-Y',strtotime($rel['quotation_date']));
	}

	$cons_company_name	= $rel['company_name'];
	$cons_cust_address	= $rel['cust_address'];
	$cons_gst_no		= $rel['gst_no'];
	$cons_state_name	= $rel['state_name'];
	$cons_gst_state_code= $rel['gst_state_code'];
	$cons_city_name		= $rel['city_name'];
	$cons_country_name	= $rel['country_name'];

		//consignee
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

	}
	$po_inde = "select group_concat(po_ref_id) as rp_id from tbl_purchaseordertrn where purchaseordertrn_status=0 and purchaseorder_id=$purchaseorder_id";
	$po_in_exe = $dbcon->query($po_inde);
	$po_in_row = mysqli_fetch_array($po_in_exe);
	$indent_no = "select group_concat(indent_no) as indent from tbl_request_product where rp_id in (".$po_in_row['rp_id'].")";
	$indent_e=$dbcon->query($indent_no);
	$indent_row=mysqli_fetch_array($indent_e);
	/* Check Discount is On or off Start */
	$sql=$dbcon->query("SELECT SUM(discount_per) AS discount FROM `tbl_proforma_trn` AS trn WHERE trancation_status=0 AND invoice_id='$invoiceid'");
$getrows=mysqli_num_rows($sql);
$disc_row=mysqli_fetch_assoc($sql);

if($disc_row['discount']>0){
	$colspan=5;
}else{
	$colspan=4;
} 
	
	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));

	$header ='<table style="border: none; width: 100%;">
	<tbody>
	<tr style="border: none;">
	<td style="border: none; vertical-align: top;"><img src="'.DOMAIN_F.LOGO.$set_head['logo'].'" style="width: 7in"/></td>
	</tr>
	</tbody>
	</table>';
//$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';

	$approve_status='';
	if($rel['approve_status']=='0'){
		$approve_status=' (DRAFT)';
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
		
		.quot_annex_content_div table tr,td{
			padding:5px;
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
			<tr>
				<td colspan="4" style="font-weight:bold; text-align: center;border-left:none !important;border-right:none !important;border-bottom:none !important;border-top:none !important;">
					<h3>PURCHASE ORDER</h3>
				</td>
			</tr>
			<tr style="background-color: #868989;">
				<td colspan="2" style="width: 20%;font-weight: bold;text-align:center;border-left:1px solid !important;border-right:1px solid !important;border-bottom:1px solid !important;border-top:1px solid !important;">VENDER DETAILS</td>
				<td colspan="2" style="width: 20%;font-weight: bold; text-align:center;border-left:1px solid !important;border-right:1px solid !important;border-bottom:1px solid !important;border-top:1px solid !important;">PURCHASE ORDER DETAILS</td>
			</tr>
			<tr>
				<td style="width: 15%;border-left:1px solid !important;border-right:none !important;border-bottom:none !important;border-top:none !important;vertical-align:top;">
					M/s.
				</td>
				<td style="width: 35%;border-left:none !important;border-bottom:none !important;border-right:1px solid !important;vertical-align:top;">
						'.$rel['vender_name'].'
				</td>
				<td style="width: 15%;border-left:none !important;border-right:none !important;vertical-align:top;">
					P.O. Number:
				</td>
				<td style="width: 35%;border-right:1px solid !important;vertical-align:top;">
					'.$rel['purchaseorder_no'].'
				</td>
			</tr>
			<tr>
				<td style="width: 15%;border-left:1px solid !important;border-right:none !important;border-bottom:none !important;border-top:none !important;vertical-align:top;">
					Address:
				</td>
				<td style="width: 35%;border-left:none !important;border-bottom:none !important;border-right:1px solid !important;vertical-align:top;">
					'.nl2br($rel['vender_address']).'
				</td>
				<td rowspan="2" style="width: 15%;border-left:none !important;border-right:none !important;vertical-align:top;">
					P.O. Date:
				</td>
				<td rowspan="2" style="width: 35%;border-right:1px solid !important;vertical-align:top;">
					'.$purchaseorder_date.'
				</td>
			</tr>
			<tr>
				<td style="width: 15%;border-left:1px solid !important;border-right:none !important;border-bottom:none !important;border-top:none !important;vertical-align:top;">
					E-Mail:
				</td>
				<td style="width: 35%;border-left:none !important;border-bottom:none !important;border-right:1px solid !important;vertical-align:top;">
						'.$rel['cust_contact_person_email'].'
				</td>
				
			</tr>
			<tr>
				<td style="width: 15%;border-left:1px solid !important;border-right:none !important;border-bottom:none !important;border-top:none !important;vertical-align:top;">
					Mobile No.:
				</td>
				<td style="width: 35%;border-left:none !important;border-bottom:none !important;border-right:1px solid !important;vertical-align:top;">
					'.$rel['cust_contact_person_no'].'
				</td>
				<td style="width: 15%;border-left:none !important;border-right:none !important;vertical-align:top;">
					Delivery Date:
				</td>
				<td style="width: 35%;border-right:1px solid !important;vertical-align:top;">
					'.$purchaseorder_due_date.'
				</td>
			</tr>
			<tr>
				<td style="width: 15%;border-left:1px solid !important;border-right:none !important;border-bottom:none !important;border-top:none !important;vertical-align:top;">
					Kind Attn.:
				</td>
				<td style="width: 35%;border-left:none !important;border-bottom:none !important;border-right:1px solid !important;vertical-align:top;">
					'.$rel['cust_contact_person_name'].'
				</td>
				<td style="width: 15%;border-left:none !important;border-right:none !important;vertical-align:top;">
					GST No.:
				</td>
				<td style="width: 35%;border-right:1px solid !important;vertical-align:top;">
					'.$rel['tin_no'].'
				</td>
			</tr>
		</table>
		
		<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		<thead>
		<tr style="border: 1px solid;">
		<th style="border: 1px solid; width:5%;text-align:center;">Sr</th>
		<th style="border: 1px solid; width:45%;text-align:center;">DESCRIPTION OF GOODS</th>
		<th style="border: 1px solid; width:12.5%;text-align:center;">DRWING NUMBER</th>
		<th style="border: 1px solid; width:12.5%;text-align:center;">QUANTITY </br>(IN UNITS)</th>
		<th style="border: 1px solid; width:12.5%;text-align:center;">RATE (IN INR)</th>';
		if($disc_row['discount']>0){ 
			$html.='<th style="border: 1px solid; width:5%;text-align:center;">Discount</th>';
		}
		$html.='<th style="border: 1px solid; width:12.5%;text-align:center;">Amount (INR)</th>
		</tr>
		</thead>
		<tbody>';
		$qry="select trn.*,product.*,product.product_desc as scode,per.unit_name,per1.unit_name as base_unit_name,per2.unit_name as conv_unit_name,group_concat(tax.tax_value) as tax_val,group_concat(tax.tax_name) as tax_name,drd.drawing_number FROM `tbl_purchaseordertrn` as trn 
		left join product_mst as product on product.product_id=trn.product_id 
		left join unit_mst as per on per.unitid=trn.unit_id 
		left join unit_mst as per1 on per1.unitid=product.product_base_unit 
		left join unit_mst as per2 on per2.unitid=product.product_conv_unit 
		left join `formula_mst` as ftax on ftax.formulaid=trn.formulaid left join tbl_tax as tax on find_in_set(tax.tax_id,ftax.tax_id)
		left join tbl_drawing as drd on drd.drawing_id=product.drawing_id
		where purchaseordertrn_status=0 and purchaseorder_id=".$rel['purchaseorder_id']." group by purchaseordertrn_id order by purchaseordertrn_id";
		$result=$dbcon->query($qry);		
		$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_i_gst=0;$gst_per = 0;$gst_rate = 0;
		$cnt=mysqli_num_rows($result);
		while($row=mysqli_fetch_assoc($result))
		{
			$gst_per = $row['cgst_tax_per']+$row['sgst_tax_per']+$row['igst_tax_per'];
			$gst_rate = $row['cgst_tax_rate']+$row['sgst_tax_rate']+$row['igst_tax_rate'];

			if($row['cgst_tax_rate'] != 0 || $row['sgst_tax_rate'] !=0){
				$total_cs_gst += $gst_rate;
			}else{
				$total_i_gst += $gst_rate;
			}
        //tax summary calculation start
			if(!empty($row['tax_val']))
			{
				$tax_num=explode(",",$row['tax_val']);
				$tax_name=explode(",",$row['tax_name']);
				$total_net_rate=($row['product_qty']*$row['product_rate'])-$row['discount'];
				for($j=0;$j<count($tax_num);$j++)
				{
					if(!in_array($tax_name[$j],$tax['per']))
					{
						$tax['per'][]=$tax_name[$j];
					}
					$tax['per_total'][$tax_name[$j]]+=$total_net_rate*$tax_num[$j]/100;
				}
			}
			if($row['product_base_unit']!=$row['product_conv_unit']){
			//base_unit_name,per2.unit_name as conv_unit_name
				if($row['unit_id']==$row['product_base_unit']){
					$cqty=convert_stock($dbcon,$row['product_qty'],$row['product_id'],"conv_unit");
					$uname=$row['conv_unit_name'];
				}else{
					$cqty=convert_stock($dbcon,$row['product_qty'],$row['product_id'],"base_unit");
					$uname=$row['base_unit_name'];
				}
			}
			$pro_descri = ($row['product_des']) ? nl2br($row['product_des']) : '' ;
			$product_hsn_code = ($row['product_hsn_code']) ? $row['product_hsn_code'] : $row['product_hsn'] ;
			$pro_disc = ($row['discount_per']) ? $row['discount_per']." %" : '';
			$html.='<tr >
			<td style="text-align:center;vertical-align:top;border:1px solid;">'.$i.'</td>
			<td style="text-align:left;vertical-align:top;border:1px solid;">
			<strong>'.$row['product_name'].'</strong><br/>
			'.$pro_descri.'';
			if($delivery_type == 'product_wise'){
				$retu_date = "select sdate.*,unit.unit_name from tbl_purchaseorder_delivery_date as sdate left join unit_mst as unit on unit.unitid=sdate.unit_id where po_delivery_date_status=0 and purchaseordertrn_id=".$row['purchaseordertrn_id'];
				$resadate=$dbcon->query($retu_date);

				$html .='<table width="40%" style="font-size:13px">
				<tr>
				<td><strong>Delivery Date</strong></td>
				<td><strong>Qty</strong></td>
				</tr>';

				while($rowdate=brp_mysqli_fetch_array($resadate)){		
					$html .='<tr>
					<td>'.date('d-m-Y',strtotime($rowdate['delivery_date'])).'</td>
					<td>'.$rowdate['product_qty'].' '.$rowdate['unit_name'].'</td>
					</tr>';		
				}
				$html .='</table>';
			}
			$html .='</td>
			<td style="text-align:center;vertical-align:top;border:1px solid;">
			'.$row['drawing_number'].'
			</td>
			<td style="text-align:center;vertical-align:top;border:1px solid;">'.$row['product_qty'].' '.$row['unit_name'].'</td>
			<td style="text-align:center;vertical-align:top;border:1px solid;">'.number_format($row['product_rate'],2,".","").'</td>';
			if($disc_row['discount']>0){
				$html.='<td style="text-align:center;vertical-align:top;border:1px solid;">'.number_format($row['product_discount'],2,".","").'<br>('.$pro_disc.')</td>';
			}
			$html.='<td style="text-align:center;vertical-align:top;border:1px solid;">'.number_format($row['product_amount'],2,".","").'</td>
			</tr>';
			$i++; 
			$totalqty=$totalqty+$row['product_qty']-$charges_qty;$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
			$total_product_amount+=$row['product_amount'];
			$totaltaxable+=$taxable_amt;
			$total+=$row['total'];
		}
		$pr=15-$cnt;
		for($j=0; $j<$pr; $j++){
			$html.='<tr style="border:1px solid;">
			<td style="border:1px solid;height:25px;"></td>
			<td style="border:1px solid;"></td>
			<td style="border:1px solid;"></td>
			<td style="border:1px solid;"></td>
			<td style="border:1px solid;"></td>';
			if($disc_row['discount']>0){
				$html.='<td style="border:1px solid;"></td>';
			}
			$html.='<td style="border:1px solid;"></td>
			</tr>';
		}
		$remark = ($rel['remark']) ? $rel['remark'] : '';
		$rows=(($rel['stateid']==$set_head['stateid']) ? 2 : 1)+2;
		$gtotal_product_amount=$total_product_amount;
		$html.='<!--<tr style="background: #ededed;">
		<td colspan="'.$colspan.'" style="border:1px solid;"></td>
		<td style="text-align:right; font-weight: bold;border:1px solid;">Basic Total</td>
		<td style="text-align:right;font-weight: bold;background: #ededed;border:1px solid;">'.number_format($total_product_amount,2,".","").'</td>
		</tr>-->

		<!--pathik start-->

		<tr>
		<td colspan="'.$colspan.'" style="border:1px solid;"></td>
		<td  style=" text-align:right;font-size:12px;border:1px solid;">TOTAL </td>
		<td  style=" text-align:right;font-size:12px;border:1px solid;">'.without_comma_two_digit_amount($total_product_amount,2).'</td>
		</tr>
		';
		if($rel['stateid']==$set_head['stateid']){
			$gtotal_product_amount=$gtotal_product_amount+$total_cs_gst;
			$html.='<tr>
			<td colspan="'.$colspan.'" style="border:1px solid;"></td>
			<td  style=" text-align:right;font-size:12px;border:1px solid;">CGST</td>
			<td  style=" text-align:right;font-size:12px;border:1px solid;">'.number_format(($total_cs_gst/2),2,".","").'</td>
			</tr>
			<tr>
			<td colspan="'.$colspan.'" style="border:1px solid;"></td>
			<td  style=" text-align:right;font-size:12px;border:1px solid;">SGST</td>
			<td  style=" text-align:right;font-size:12px;border:1px solid;">'.number_format(($total_cs_gst/2),2,".","").'</td>
			</tr>';
		}else{
			$gtotal_product_amount=$gtotal_product_amount+$total_i_gst;
			$html.='<tr>
			<td colspan="'.$colspan.'" style="border:1px solid;"></td>
			<td  style=" text-align:right;font-size:12px;border:1px solid;">IGST</td>
			<td  style=" text-align:right;font-size:12px;border:1px solid;">'.number_format(($total_i_gst),2,".","").'</td>
			</tr>';
		}		
		$qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_purchaseordertrn as trn 
		left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
		left join tbl_ledger as l on l.l_id=tc.tax_id 
		where tc.tax_additional='1' and trn.purchaseorder_id=".$rel['purchaseorder_id']." and trn.purchaseordertrn_status!=2 and tc.isdelete='0' group by tc.tax_id";
		$result11=$dbcon->query($qry11);		
		while($row11=mysqli_fetch_assoc($result11))
		{
			$gtotal_product_amount=$gtotal_product_amount+$row11['add_sum'];
			$html.='<tr>
			<td colspan="'.$colspan.'" style="border:1px solid;"></td>
			<td  style=" text-align:right;font-size:12px;"><b>'.$row11['l_name'].'</b></td>
			<td style="text-align:center;border:1px solid;"><b>
			'.number_format($row11['add_sum'],2,".","").'
			</b></td>
			</tr>';
		}
		$qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
		from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
		left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
		where b.sundry_voucher_id=".$rel['purchaseorder_id']." and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' and le.default_sundry='0'";
		$result12=$dbcon->query($qry12);		
		while($row12=mysqli_fetch_assoc($result12))
		{
			$gtotal_product_amount=$gtotal_product_amount+$row12['sundry_amount'];
			$html.='<tr>
			<td colspan="'.$colspan.'" style="border:1px solid;"></td>
			<td  style=" text-align:right;font-size:12px;"><b>'.$row12['l_name'].'</b></td>
			<td style="text-align:center;border:1px solid;"><b>
			'.number_format($row12['sundry_amount'],2,".","").'
			</b></td>
			</tr>';
		}
    //$round_off = round($final_total)-$final_total;
		$round_off = 0;
		$colspan1=$colspan-1;
		$html .= '
		<tr style="background-color:#b3b3b3">
		<td colspan="'.$colspan1.'" style="border:1px solid;">
		Total Amount (In Words): <span style="font-weight:bold;font-size:13px;">'.convert_number_to_words_new($rel['g_total']).'</span></td>
		<td colspan="2" style=" text-align:right;font-size:14px;border:1px solid;">Total Amount (In Figure) </td>
		<td  style=" text-align:right;font-size:14px;font-weight:bold;border:1px solid;">'.without_comma_two_digit_amount(($gtotal_product_amount),2).' </td>
		</tr>';

		$html.='</tbody></table>';
		$html.='<table style="page-break-inside: avoid;border-bottom:none">
		
		<!--<tr style="border-bottom:none">
		<td colspan="9" style=" text-align:left;font-size:14px;">
		Total Amount (In Words): <span style="font-weight:bold;font-size:13px;">'.convert_number_to_words_new($rel['g_total']).'</span></td>
		</tr>
		-->
		
		
		<!-- pathik end -->
		</tbody>
		</table>';
		//if(!empty($rel['po_condition'])){
		$html.='<div style="font-weight: bold;">
		<h4></h4>
		</div>
		<div></div>';
	//}
		$html.='<table>
		<tbody>
			<tr style="background-color:#b3b3b3">
				<td colspan=2 style="vertical-align:bottom; text-align: left; border:1px solid;">
				TERMS & CONDITION
				</td>
			</tr>
			<tr>
				<td rowspan="3" style="vertical-align:top; text-align: left; border:1px solid;width:75%;">'.(($rel['po_condition']) ? $rel['po_condition'] : '').'</td>
				<td style="vertical-align:top; text-align: center; border:1px solid;border-bottom:none;width:25%;">
					S Mark Engineers
				</td>
			</tr>
			<tr>
				<td style="vertical-align:top; text-align: center; border:1px solid;border-top:none;border-bottom:none;width:50%;height: 85px;">
					<img src="'.DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'].'" style="height: 85px;width: 120px;"/>
				</td>
			</tr>
			<tr>
				<td style="vertical-align:top; text-align: center; border:1px solid;border-top:none;width:50%;">
					Purchase Department
				</td>
			</tr>
		<tr>
		
		</tbody></table>
		
		
		</div>';
		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
		//echo $html;exit;
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','40','5','1','1');
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
		return 'Purchase Order '.$purchaseorder_id.'.pdf';
	}

	?>