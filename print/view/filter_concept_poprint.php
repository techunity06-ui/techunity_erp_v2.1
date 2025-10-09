<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$include = '../../include/';

$type='pdf';
if(strtolower($type) == 'pdf') {
	$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select po.*,state.state_name,modesup.mode_dispatch,payterms.payment_terms as payment_term_name,l.l_name as vender_name,country.country_name,l.m_address as vender_address,l.gst_no as tin_no,l.cust_mobile as vender_mobile,l.stateid,state.gst_state_code,city.city_name,l.cust_pincode,l.cust_email,god.gd_name,god.gd_address,comp.company_name,le.l_name as con_ven,bm.branch_name as cons_bran, per.c_con_fname, per.c_con_lname, per.c_con_email, per.c_con_mobile from tbl_purchaseorder as po 
	inner join tbl_ledger as l on l.l_id=po.vender_id
	left join tbl_cust_contact as per on per.cust_id = po.vender_id
	left join country_mst as country on country.countryid=l.countryid
	left join pay_terms as payterms on payterms.terms_id=po.payment_terms
	left join mode_of_dispatch as modesup on modesup.mode_dis_id=po.mode_of_dispatch
	left join state_mst as state on state.stateid=l.stateid
	left join city_mst as city on city.cityid=l.cityid
	left join mst_godown as god on god.gd_id = po.godown_id
	left join tbl_company as comp on comp.company_id = po.company_id
	left join tbl_ledger as le on le.l_id = po.con_vender_id
	left join branch_mst as bm on bm.branch_id = po.con_branch
	where po.purchaseorder_id=$purchaseorder_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	//echo "<pre>";print_r($rel);die();
	$_SESSION['invoice_no']=$rel['invoice_no'];		
	$delivery_type = $rel['delivery_type'];
	$purchaseorder_date='';
	if($rel['purchaseorder_date']!="1970-01-01" && $rel['purchaseorder_date']!="0000-00-00")
	{
		$purchaseorder_date=date('d-m-Y',strtotime($rel['purchaseorder_date']));
	}
	


	$cons_company_name	= $rel['vender_name'];
	$cons_cust_address	= $rel['vender_address'];
	$cons_gst_no		= $rel['gst_no'];
	$cons_state_name	= $rel['state_name'];
	$cons_gst_state_code= $rel['gst_state_code'];
	$cons_city_name		= $rel['city_name'];
	$cons_country_name	= $rel['country_name'];
	
	$party_address_billing="<strong>".$rel['vender_name']."</strong>
	<span style='font-weight:normal;'> <br/>
	".$rel['vender_address'].",<br/>
	".$rel['cust_pincode']."
	".$rel['city_name'].",
	".$rel['state_name'].",
	".$rel['country_name']."</span>
	<br>  Phone : ".$rel['vender_mobile']."
	<br>  Email : ".$rel['cust_email'];

	$HowManyWeeks = (strtotime( $rel['purchaseorder_due_date'] ) - strtotime( $rel['cdate'])) / 604800;
	$HowManyWeeks = round($HowManyWeeks);
	$HowManyWeeks = ($HowManyWeeks=='0')?'0':$HowManyWeeks;
	$delivery_week = $HowManyWeeks .' - '. ($HowManyWeeks+1) . ' WEEKS';

	$us_sql = "select user_name from users where user_id='".$rel['userid']."'";
	$user_rel=brp_mysqli_fetch_assoc($dbcon->query($us_sql));

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
//CHEMITEK PROCESS EQUIPMENTS PRIVATE LIMITED		
	$comp_rel=mysqli_fetch_assoc($dbcon->query($set));

	if($rel['cons_same_as']==0){
		if($rel['con_type'] ==1){
			$cons_name = $rel['cons_bran'];
		}else if($rel['con_type'] ==2){
			$cons_name = $rel['con_ven'];
		}else{
			$cons_name = $rel['company_name'];
		}

		$consignee_address = $rel['con_address'];
		$party_address_con = '<strong>'.$cons_name.'</strong><br>'.$consignee_address;
	}else{
		$cons_name 		   = $rel['company_name'];
		$consignee_address = $comp_rel['address'];
		$party_address_con = '<strong>'.$cons_name.'</strong><br>'.$consignee_address;
	}
	/* Check Discount is On or off Start */
	if($comp_rel['show_disc']=='1'){
		$colspan=5;
		$dynamicwidth=40;
	}else{
		$colspan=6;
		$dynamicwidth=46;
	}

	$companyConfiguration=getCompanyConfiguration($dbcon);
	$purchase_pro_print=explode(",", $companyConfiguration['purchase_pro_print']);
	/* Check Discount is On or off End */
	$sundrytax=$dbcon->query("select b.* from tbl_bill_sundry_transaction as b where b.sundry_voucher_id=".$rel['purchaseorder_id']." and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' ");
//$sundrytax_rels = mysqli_fetch_assoc($sundrytax);
	$total_sundrytax=0;
	while($sumsundrytax=mysqli_fetch_assoc($sundrytax)){
		$total_sundrytax = $total_sundrytax + $sumsundrytax['sundry_gst_amount'];
	}

	$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
	$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

	$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
	$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
	$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
	

	$companyConfiguration=getCompanyConfiguration($dbcon);
    $purchase_pro_search=$companyConfiguration['purchase_pro_print'];
    $pro_search=explode(",", $purchase_pro_search);

	$html='';
	$header ='<img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:100%" />
	<table>
	<tr style="border:1px solid; ">
	<td colspan="2" style="text-align:center; font-weight:bold;">Purchase Order</td>
	</tr>
	<tr style="border :1px solid;">
	<td style="width: 50%; border: 0px;">PO No : '.$rel['purchaseorder_no'].'</td>
	<td style="width: 50%; border: 0px; text-align: right;">PO Date : '.$purchaseorder_date.'</td>
	</tr>
	</table>';

	$html.='<html>
	<head>					
	<title>Purchase Order - '.$rel['purchaseorder_no'].'</title>

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
		<!--<htmlpageheader name="otherpages" style="display:none">
		<div style="text-align:center">'.$header.'</div>
		</htmlpageheader>-->
		<!--<htmlpagefooter name="otherpages_footer" style="display:none">
		<div style="text-align:center">'.$footer.'</div>
		</htmlpagefooter>-->
		<!--<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>-->
		<div>
		<table>
		<tr style="background-color:#b3b3b3">
		<td style="width: 50%; text-align:center;"><b>Vendor / Supplier Details</b> </td>
		<td style="width: 50%; text-align:center;"><b>Ship to Address</b></td>
		</tr>
		<tr style="border:none; border-left:1px solid; border-right:1px solid;">
		<td style="border-left:1px solid;border-bottom:none; text-align:left;vertical-align:top;">'.$party_address_billing.' </td>
		<td style="border-bottom:none;  text-align:left;vertical-align:top">'.$party_address_con.'</td>
		</tr>
		<tr style="border:none; border-left:1px solid; border-right:1px solid;">
		<td style="border-left:1px solid;border-bottom:none; text-align:left;vertical-align:top;">Kind Attn. : '.$rel['c_con_fname'].' '.$rel['c_con_lname'].'<br>Email : '.$rel['c_con_email'].'<br>Phone : '.$rel['c_con_mobile'].'</td>
		<td style="border-bottom:none;  text-align:left;vertical-align:top"></td>
		</tr>
		<tr style="border:1px solid;">
		<td colspan="2" style="text-align:left;">Please Arrange to supply the following material as per terms and conditions.</td>
		</tr>
		</table>
		</div>
		<table style="border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		<thead>
		<tr style="background-color:#b3b3b3">
		<td style="width: 5%; text-align:center;"><b>Sr No.</b> </td>
		<td style="width: 43%; text-align:left;"><b>Item Description</b></td>
		<td style="width: 8%; text-align:center;"><b>HSN Code</b></td>
		<td style="width: 10%; text-align:center;"><b>Required Date</b></td>
		<td style="width: 8%; text-align:center;"><b>Qty</b></td>
		<td style="width: 8%; text-align:center;"><b>UOM</b></td>
		<td style="width: 8%; text-align:center;"><b>Rate<br>'.$currency_name.'</b></td>
		<td style="width: 10%; text-align:right;"><b>Net Amount<br>'.$currency_name.'</b></td>
		</tr>
		</thead>
		<tbody>';
		$qry="SELECT trn.*, product.product_icode, product.product_name, per.unit_name, product.product_alias_name, hsn.hsn_code,dr.drawing_number FROM `tbl_purchaseordertrn` as trn 
		left join product_mst as product on product.product_id=trn.product_id 
		left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
		left join unit_mst as per on per.unitid=trn.rate_unit
		left join mst_hsn_code as hsn on hsn.hsn_id=product.product_hsn 
		where purchaseordertrn_status=0 and purchaseorder_id=".$rel['purchaseorder_id']." group by purchaseordertrn_id order by purchaseordertrn_id";		

		$trn_qry_rs=$dbcon->query($qry);
		$p=1;$ttl_amt=0;$ttl_qty=0;$total_cs_gst=0;$total_i_gst=0;$gst_per=0;$gst_rate=0;
		$cnt=mysqli_num_rows($trn_qry_rs);
		while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
			
			if(in_array('drawing',$pro_search)){
	            $drawing_number = " -- (".$trn_rel['drawing_number'].")";
	        }
	        if(in_array('item',$pro_search)){
	            $item_code = " -- (".$trn_rel['product_icode'].")";
	        }
	        if(in_array('alias',$pro_search)){
	            $alias = " -- (".$trn_rel['product_alias_name'].")";
	        }

	       	$que = "select smp.po_req_no as work_order_no, group_concat(req.indent_no) as indent  from tbl_request_product as req
				        left join tbl_set_main_process as smp on smp.sp_id = req.sp_id
				        where rp_id in (".$trn_rel['po_ref_id'].") group by smp.sp_id";

	        $res=$dbcon->query($que);
	        $rw = brp_mysqli_fetch_array($res);

	        if($companyConfiguration['po_work_order_wise'] == 1){
	        	$wno = "<br> <strong>Work Order No : ".$rw['work_order_no']."</strong>";
	        }

			$indent_no = po_to_indent_no($dbcon,$trn_rel['po_ref_id']);
			$sales_order_no = po_to_so_no($dbcon,$trn_rel['po_ref_id']);
			$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
			$gst_rate = $trn_rel['cgst_tax_rate']+$trn_rel['sgst_tax_rate']+$trn_rel['igst_tax_rate']+$total_sundrytax;

			if($trn_rel['cgst_tax_rate'] != 0 || $trn_rel['sgst_tax_rate'] !=0){
				$total_cs_gst += $gst_rate;
			}else{
				$total_i_gst += $gst_rate;
			}
			$alias_name = '';
			if(in_array('alias',$purchase_pro_print)){
				$alias_name = " -- (".$trn_rel['product_alias_name'].")";
			}
			$purchaseorder_due_date ='';
			if($rel['purchaseorder_due_date']!="1970-01-01" && $rel['purchaseorder_due_date']!="0000-00-00")
			{
				$purchaseorder_due_date=date('d-m-Y',strtotime($rel['purchaseorder_due_date']));
			}
			if($trn_rel['unit_id']===$trn_rel['rate_unit']){
				$sqty=$trn_rel['product_qty'];
			}else{
				$sqty=$trn_rel['product_conv_qty'];
			}
			$path='view/upload/product_images/';
			$html.='
			<tr>
			<td style="text-align:center; vertical-align: top;">'.$p.' </td>

			<td style="text-align:left; vertical-align: top;">'.$trn_rel['product_name'].' '.$drawing_number.' '.$item_code.' '.$alias.' '.$wno.'<br>Indent No. : '.$indent_no.'<br>SO No. : '.$sales_order_no.' ';

			if($delivery_type == 'product_wise'){
				$retu_date = "select sdate.*,unit.unit_name from tbl_purchaseorder_delivery_date as sdate left join unit_mst as unit on unit.unitid=sdate.unit_id where po_delivery_date_status=0 and purchaseordertrn_id=".$trn_rel['purchaseordertrn_id'];
				$resadate=$dbcon->query($retu_date);

				$html .='<table width="60%" style="font-size:13px">
				<tr>
				<td><strong>Delivery Date</strong></td>
				<td><strong>Qty</strong></td>
				<td><strong>Pend. Qty</strong></td>
				</tr>';

				while($rowdate=brp_mysqli_fetch_array($resadate)){		
					$html .='<tr>
					<td>'.date('d-m-Y',strtotime($rowdate['delivery_date'])).'</td>
					<td>'.$rowdate['product_qty'].' '.$rowdate['unit_name'].'</td>
					<td>'.$rowdate['used_qty'].' '.$rowdate['unit_name'].'</td>
					</tr>';		
				}
				$html .='</table>';
			}
			$html .='</td>
			<td style="vertical-align: top; text-align:center;"> '.$trn_rel['hsn_code'].'</td>

			<td style="vertical-align: top; text-align:center;">'.$purchaseorder_due_date.'</td>

			<td style="vertical-align: top; text-align:center;">'.without_comma_two_digit_amount($sqty).' </td>
			<td style="vertical-align: top; text-align:center;">'.$trn_rel['unit_name'].' </td>
			<td style="vertical-align: top; text-align:center;"> '.without_comma_two_digit_amount($trn_rel['product_rate']).'</td>

			<td style="vertical-align: top; text-align:right;"> '.without_comma_two_digit_amount($trn_rel['product_amount']).'</td>
			</tr>';

			$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
			if($trn_rel['act_amt_flag']!='1'){
		//$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
				$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
			}
			$p++;
		}
		$pr=10-$cnt;

		$html.='
		<tr>
		<td colspan="7" style=" text-align:right;">TOTAL'.$currency_name.'</td>
		<td  style=" text-align:right;">'.without_comma_two_digit_amount($ttl_amt,2).'</td>
		</tr>
		';
		if($rel['stateid']==$comp_rel['stateid']){
			$html.='<tr>
			<td colspan="7" style=" text-align:right;">CGST</td>
			<td  style=" text-align:right;">'.number_format(($total_cs_gst/2),2,".","").'</td>
			</tr><tr>
			<td colspan="7" style=" text-align:right;">SGST</td>
			<td  style=" text-align:right;">'.number_format(($total_cs_gst/2),2,".","").'</td>
			</tr>';
		}else{
			$html.='<tr>
			<td colspan="7" style=" text-align:right;">IGST</td>
			<td  style=" text-align:right;">'.number_format(($total_i_gst),2,".","").'</td>
			</tr>';
		}		
		$qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_purchaseordertrn as trn 
		left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
		left join tbl_ledger as l on l.l_id=tc.tax_id 
		where tc.tax_additional='1' and trn.purchaseorder_id=".$rel['purchaseorder_id']." and trn.purchaseordertrn_status!=2 and tc.isdelete='0' group by tc.tax_id";
		$result11=$dbcon->query($qry11);		
		while($row11=mysqli_fetch_assoc($result11))
		{
			$html.='<tr>
			<td colspan="7" style=" text-align:right;">'.$row11['l_name'].'</td>
			<td style="text-align:right;border:1px solid;">'.number_format($row11['add_sum'],2,".","").'</td>
			</tr>';
		}
		$qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
		from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
		left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
		where b.sundry_voucher_id=".$rel['purchaseorder_id']." and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' and le.default_sundry='0'";
		$result12=$dbcon->query($qry12);		
		while($row12=mysqli_fetch_assoc($result12))
		{
			$html.='<tr>
			<td colspan="7" style=" text-align:right;">'.$row12['l_name'].'</td>
			<td style="text-align:right;border:1px solid;">'.number_format($row12['sundry_amount'],2,".","").'</td>
			</tr>';
		}
    //$round_off = round($final_total)-$final_total;
		$round_off = 0;
		$html .= '
		<tr>
		<td colspan="7" style=" text-align:right;background-color:#b3b3b3">Total Amount (In Figure) </td>
		<td  style=" text-align:right;font-weight:bold;background-color:#b3b3b3">'.without_comma_two_digit_amount(($rel['g_total']),2).' </td>
		</tr>
		<tr style="border-bottom:none">
		<td colspan="8" style=" text-align:left;">Total Amount (In Words): <span style="font-weight:bold;">'.(($comp_rel['currency_id']==$rel['currency_id']) ? ucfirst(convert_number_to_words_new($rel['g_total'],$currency_word_start,$currency_word_end)) : ucfirst(convert_number_to_words($rel['g_total'],$currency_word_start,$currency_word_end))).'</span></td>
		</tr>
		</table>';
		$html.='<table>
		<tr style="">
		<td rowspan="2" style="width:100px;text-align:center">HSN/SAC</td>
		<td rowspan="2" style="width:100px;text-align:center">Taxable Value</td>';
		if($rel['stateid']==$comp_rel['stateid']){
			$html.='<td colspan="2" style="text-align:center">SGST</td>
			<td colspan="2" style="text-align:center">CGST</td>';
		}else{
			$html.='<td colspan="2" style="text-align:center">IGST</td>';
		}
		$html.='<td rowspan="2" style="text-align:center;width:100px">Total Tax Amount</td>
		</tr>';
		if($rel['stateid']==$comp_rel['stateid']){
			$html.='<tr>
			<td style="text-align:center;">Rate</td>
			<td style="text-align:center;">Amount</td>
			<td style="text-align:center;">Rate</td>
			<td style="text-align:center;">Amount</td>
			</tr>';
		}else{
			$html.='<tr>
			<td style="text-align:center;">Rate</td>
			<td style="text-align:center;">Amount</td>
			</tr>';
		}

		$purchase_sql = "SELECT sum(product_amount) as product_amount,product_hsn_code  FROM `tbl_purchaseordertrn` WHERE `purchaseorder_id` = '".$rel['purchaseorder_id']."' AND `purchaseordertrn_status` = 0 group by product_hsn_code";

		$purchase_exec=$dbcon->query($purchase_sql);
		$total_amount_sum = 0;
		$total_tax_sum = 0;
		$tax_array = [];
		while($purchase_data=mysqli_fetch_assoc($purchase_exec)){
			$total_amount_sum = $total_amount_sum + $purchase_data['product_amount'];
			$html.='<tr style="">
			<td style="text-align:center;">'.$purchase_data['product_hsn_code'].'</td>
			<td style="text-align:right;">'.without_comma_two_digit_amount($purchase_data['product_amount']).'</td>';
			$total_taxable_amount = 0;
			if($rel['stateid']==$comp_rel['stateid']){
				$html.='<td style="text-align:right;">'.$gst_per.'</td>
				<td style="text-align:right;">'.number_format(($total_cs_gst/2),2,".","").'</td>
				<td style="text-align:right;">'.$gst_per.'</td>
				<td style="text-align:right;">'.number_format(($total_cs_gst/2),2,".","").'</td>';
				$k++;
				$total_taxable_amount = $total_taxable_amount+$total_cs_gst;
				$tax_array[$rel15['tx_tax_id']][] = $texAmount;
			}else{
				$html.='<td style="text-align:right;">'.$gst_per.'</td>
				<td style="text-align:right;">'.number_format(($total_i_gst),2,".","").'</td>';
				$k++;
				$total_taxable_amount = $total_taxable_amount+$total_i_gst;
				$tax_array[$rel15['tx_tax_id']][] = $texAmount;
			}
			$total_tax_sum = $total_tax_sum + $total_taxable_amount; 
			$html.='<td style=" text-align:right;">'.without_comma_two_digit_amount($total_taxable_amount).'</td>
			</tr>';
		}

		$html.='<tr style="">
		<td style="text-align:center;font-weight:bold">Total</td>
		<td style="text-align:right;font-weight:bold">'.without_comma_two_digit_amount($total_amount_sum).'</td>';
		if($rel['stateid']==$comp_rel['stateid']){
			$html.='<td style="text-align:right;font-weight:bold" colspan="2">'.without_comma_two_digit_amount($total_cs_gst/2).'</td>
			<td style="text-align:right;font-weight:bold" colspan="2">'.without_comma_two_digit_amount($total_cs_gst/2).'</td>
			<td style=" text-align:right;font-weight:bold">'.without_comma_two_digit_amount($total_cs_gst).'</td>';
		} else{
			$html.='<td style="text-align:right;font-weight:bold" colspan="2">'.without_comma_two_digit_amount($total_i_gst).'</td>
			<td style=" text-align:right;font-weight:bold">'.without_comma_two_digit_amount($total_i_gst).'</td>';
		}
		$html.='</tr>
		</table>';
		/*$html.='';*/
		$html.='<table style="page-break-inside: avoid;border-top:none;border-bottom:none" >';
		$html.='
		<tr style="border-top:none">
		<td colspan="4" style=" text-align:left;height:60px;vertical-align:top">
		Remarks : <br>
		'.(($rel['remark']!='0')?$rel['remark']:'').'
		</td>
		</tr>
		<tr>
		<td colspan="4">Terms & Conditions : <br>'.$rel['po_condition'].'</td>
		<tr style="border-bottom: none;">
		<td style="width:25%;text-align:left;border-right: none;">GSTIN<br>PAN No.</td>
		<td style="width:25%;text-align:left;border-right: none; border-left: none;"> : '.$comp_rel['vatno'].'<br> : '.$comp_rel['pan_no'].'</td>
		<td colspan="2" style="width:50%;text-align:left;vertical-align:top; border: none;"><strong>For, '.$comp_rel['company_name'].'</strong></td>
		</tr>
		<tr style="border-top: none;">
		<td colspan="2" style="width:50%;text-align:left; border: none;"></td>
		<td style="width:25%;text-align:left; vertical-align: top; border: none;">Prepared By <br> '.$user_rel['user_name'].'</td>
		<td style="width:25%;text-align:left; vertical-align: top; border: none;">Approved By <br> '.(($user_rel['user_name']) ? $user_rel['user_name'] : '').'</td>
		</tr>';
		$html.='</table>';
		/* Get Terms And Condition Start */

		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
		// echo $header;
		// echo $html;exit;
		$foot = 'P.O No. & Date : '.$rel['purchaseorder_no'].' - '.$purchaseorder_date.'||{PAGENO}{nbpg}';
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','50','10','1','1');
//		$mdf->SetFont('ProximaNova');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		$mpdf->pagenumPrefix = 'Page ';
		$mpdf->pagenumSuffix = ' of ';
		$mpdf->nbpgPrefix = ' ';
		$mpdf->nbpgSuffix = '';
		$mpdf->SetFooter($foot);
		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='UTF-8';
		$mpdf->WriteHTML($html);
		$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
		ob_clean();
		return 'Purchase Order '.$rel['purchaseorder_no'].'.pdf';
	}	
?>