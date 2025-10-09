<?php
$po_id = $_REQUEST['id'];
if (!empty($po_id)) {
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
	include("../../include/function_database_query.php");
	include_once(COMMON_FUNCTION_PATH . "common_functions.php");
	$include = '../../include/';
	po_print($dbcon, $po_id, $save_file = "No");
}

function po_print($dbcon, $po_id, $save_file = "yes")
{
	$type = 'pdf';
	if (strtolower($type) == 'pdf') {
		$purchaseorder_id = $dbcon->real_escape_string($po_id);
		$query = "select po.*,state.state_name,modesup.mode_dispatch,payterms.payment_terms as payment_term_name,l.l_name as vender_name,country.country_name,l.m_address as vender_address,l.gst_no as tin_no,l.cust_mobile as vender_mobile,l.stateid,state.gst_state_code,city.city_name,l.cust_pincode,l.cust_email,god.gd_name,god.gd_address,comp.company_name,le.l_name as con_ven,bm.branch_name as cons_bran, cur.currency_symbol, l.ledger_code, cper.cust_contact_person_name from tbl_purchaseorder as po 
	inner join tbl_ledger as l on l.l_id=po.vender_id
	left join country_mst as country on country.countryid=l.countryid
	left join pay_terms as payterms on payterms.terms_id=po.payment_terms
	left join mode_of_dispatch as modesup on modesup.mode_dis_id=po.mode_of_dispatch
	left join state_mst as state on state.stateid=l.stateid
	left join city_mst as city on city.cityid=l.cityid
	left join mst_godown as god on god.gd_id = po.godown_id
	left join tbl_company as comp on comp.company_id = po.company_id
	left join tbl_ledger as le on le.l_id = po.con_vender_id
	left join branch_mst as bm on bm.branch_id = po.con_branch
	left join tbl_currency as cur on cur.currency_id = po.currency_id
	left join tbl_cust_contact_person as cper on cper.cust_contact_person_id = po.kind_attn
	where po.purchaseorder_id=$purchaseorder_id";
		$rel = mysqli_fetch_assoc($dbcon->query($query));
		//echo "<pre>";print_r($rel);die();
		$_SESSION['invoice_no'] = $rel['invoice_no'];
		$delivery_type = $rel['delivery_type'];
		$purchaseorder_no = $rel['purchaseorder_no'];
		$purchaseorder_date = '';
		$quotation_date = '';
		if ($rel['purchaseorder_date'] != "1970-01-01" && $rel['purchaseorder_date'] != "0000-00-00") {
			$purchaseorder_date = date('d-m-Y', strtotime($rel['purchaseorder_date']));
		}

		if ($rel['quotation_date'] != "1970-01-01" && $rel['quotation_date'] != "0000-00-00") {
			$quotation_date = date('d-m-Y', strtotime($rel['quotation_date']));
		}

		$cons_company_name	= $rel['vender_name'];
		$cons_cust_address	= $rel['vender_address'];
		$cons_gst_no		= $rel['gst_no'];
		$cons_state_name	= $rel['state_name'];
		$cons_gst_state_code = $rel['gst_state_code'];
		$cons_city_name		= $rel['city_name'];
		$cons_country_name	= $rel['country_name'];

		$party_address_billing = "
	<span style='font-weight:normal;'> 
	" . $rel['vender_address'] . ",<br/>
	" . $rel['cust_pincode'] . "
	" . $rel['city_name'] . ",
	" . $rel['state_name'] . ",
	" . $rel['country_name'] . "</span>";


		$HowManyWeeks = (strtotime($rel['purchaseorder_due_date']) - strtotime($rel['cdate'])) / 604800;
		$HowManyWeeks = round($HowManyWeeks);
		$HowManyWeeks = ($HowManyWeeks == '0') ? '0' : $HowManyWeeks;
		$delivery_week = $HowManyWeeks . ' - ' . ($HowManyWeeks + 1) . ' WEEKS';

		$us_sql = "select user_name from users where user_id='" . $rel['userid'] . "'";
		$user_rel = brp_mysqli_fetch_assoc($dbcon->query($us_sql));

		$set = "select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=" . $rel['company_id'];
		//CHEMITEK PROCESS EQUIPMENTS PRIVATE LIMITED		
		$comp_rel = mysqli_fetch_assoc($dbcon->query($set));

		if ($rel['cons_same_as'] == 0) {
			if ($rel['con_type'] == 1) {
				$cons_name = $rel['cons_bran'];
			} else if ($rel['con_type'] == 2) {
				$cons_name = $rel['con_ven'];
			} else {
				$cons_name = $rel['company_name'];
			}

			$consignee_address = $rel['con_address'];
			$party_address_con = '<strong>' . $cons_name . '</strong><br><br>' . $consignee_address;
		} else {
			$cons_name 		   = $rel['company_name'];
			$consignee_address = $comp_rel['address'];
			$party_address_con = '<strong>' . $cons_name . '</strong><br><br>' . $consignee_address;
		}
		/* Check Discount is On or off Start */
		if ($comp_rel['show_disc'] == '1') {
			$colspan = 5;
			$dynamicwidth = 40;
		} else {
			$colspan = 6;
			$dynamicwidth = 46;
		}
		/* Check Discount is On or off End */
		$sundrytax = $dbcon->query("select b.* from tbl_bill_sundry_transaction as b where b.sundry_voucher_id=" . $rel['purchaseorder_id'] . " and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' ");
		//$sundrytax_rels = mysqli_fetch_assoc($sundrytax);
		$total_sundrytax = 0;
		while ($sumsundrytax = mysqli_fetch_assoc($sundrytax)) {
			$total_sundrytax = $total_sundrytax + $sumsundrytax['sundry_gst_amount'];
		}
		$companyConfiguration = getCompanyConfiguration($dbcon);
		$purchase_pro_search = $companyConfiguration['purchase_pro_print'];
		$pro_search = explode(",", $purchase_pro_search);
		$html = '';
		/*$header ='
<table>
<tr>
<td colspan="3" style="border: 0px; "><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:2.27in;padding-top:25px;" /></td>
<td colspan="6" style="text-align:center;border: 0px;">
<span style="font-size:16px;"><b>Purchase Order</b></span><br/>
<span style="font-size:16px;"><b>'.$comp_rel["company_name"].' </b></span><br/>
<span style="font-size:12px;">'.$comp_rel["address"].'</span>
</td>
</tr>
</table>';*/

		$qry_disc = $dbcon->query("select SUM(trn.product_discount) as discount FROM `tbl_purchaseordertrn` as trn WHERE trn.purchaseordertrn_status=0 and purchaseorder_id=" . $rel['purchaseorder_id']);
		$get_disc = brp_mysqli_fetch_assoc($qry_disc);
		if ($get_disc['discount'] > 0) {
			$colspan_8 = 10;
		} else {
			$colspan_8 = 9;
		}


		$header = get_header($dbcon, 'text-align: center', '100%', '');
		$footer = '<img src="' . DOMAIN_F . LOGO . $comp_rel["f_logo"] . '" style="width:8.27in;" />
<table width="100%">

<tr>
<td style="text-align:right;vertical-align:top;width:60%; bold;border-bottom:1px solid"> <strong>Page {PAGENO} of {nbpg}</strong></td>
</tr>
</table>';
		$html .= '<html>
<head>					
<title>Purchase Order - ' . $rel['purchaseorder_no'] . '</title>

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
	.blueHeading {
		color: #365f91;
	}

	</style>
	</head>
	<body>
	<!--Show Logo in other pages-->
	<!--<htmlpageheader name="otherpages" style="display:none">
	<div style="text-align:center">' . $header . '</div>
	</htmlpageheader>-->
	<!--<htmlpagefooter name="otherpages_footer" style="display:none">
	<div style="text-align:center">' . $footer . '</div>
	</htmlpagefooter>-->
	<!--<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>-->
	<div>
	<table style="font-size:12px">
	<tr>
	<td colspan="3"  style=" text-align:center; font-size:14px"><strong>Purchase Order</strong></td>
	</tr>
		<tr>
			
			<td style="width:33.33%;border-bottom:1px solid #9b9999;border-top:1px solid;">
				<strong>Vender Code : ' . $rel['ledger_code'] . '</strong><br>
				<strong>GSTIN : ' . $rel['tin_no'] . '</strong><br>
				<strong>' . $rel['vender_name'] . '</strong>
			</td>
			<td style="width:33.33%;border-top:1px solid;vertical-align:bottom;border-bottom:1px solid #9b9999 "></td>
			<td style="width:33.33%;border-top:1px solid;vertical-align:bottom; border-bottom:1px solid #9b9999 ;"><strong>P.O. No. : ' . $rel['purchaseorder_no'] . '</strong></td>
		</tr>
		<tr>
			<td rowspan="3" style="border-bottom:1px solid #9b9999">
			' . $party_address_billing . '
			</td>
			<td rowspan="3" style="border-right:none; border-left:none; vertical-align:top; border-bottom:1px solid #9b9999">
			Kind Attn. : ' . $rel['cust_contact_person_name'] . '<br>
			Contact No. : ' . $rel['vender_mobile'] . '<br>
			E-mail : ' . $rel['cust_email'] . '</td>
			<td style="">P.O.Date : ' . $purchaseorder_date . '</td>
		</tr>
		<tr>
			<td style="border-bottom:none">Qtn.No. : ' . $rel['quotation_no'] . '</td>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #9b9999">Qtn.Date : ' . $quotation_date . '</td>
		</tr>
		<tr>
			<td colspan="3" style="border-left:none">Dear Sir,<br>
Kindly arrange to supply the below mention goods as per given Instruction.</td>
		</tr>
	
	</table>
	</div>
	<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
	<thead>
	<tr>
	<td style=" text-align:center;border-top:1px solid #9b9999;border-bottom:1px solid #9b9999;"><b>Sr</b> </td>
	<td  style=" text-align:center; border-top:1px solid #9b9999;border-bottom:1px solid #9b9999;"><b> Description of Goods</b></td>
	<td colspan="2"  style=" text-align:center;border-top:1px solid #9b9999;border-bottom:1px solid #9b9999;"><b>HSN / SAC</b> </td>
	<td colspan="2"  style=" text-align:center;border-top:1px solid #9b9999;border-bottom:1px solid #9b9999;"><b>GST</b> </td>
	<td style=" text-align:center;border-top:1px solid #9b9999;border-bottom:1px solid #9b9999;"><b>Delivery Date</b> </td>
	
	<td style=" text-align:center;border-top:1px solid #9b9999;border-bottom:1px solid #9b9999;"><b>UOM</b> </td>
	<td style=" text-align:center;border-top:1px solid #9b9999;border-bottom:1px solid #9b9999;"><b>Quantity</b> </td>
	<td style="text-align:center;border-top:1px solid #9b9999;border-bottom:1px solid #9b9999;"><b>Rate</b></td>';
		if ($get_disc['discount'] > 0) {
			$html .= '<td style="text-align:center;border-top:1px solid #9b9999;border-bottom:1px solid #9b9999;"><b>Discount</b></td>';
		}
		$html .= '<td style="text-align:right;border-top:1px solid #9b9999;border-bottom:1px solid #9b9999;"><b>Amount</b></td>
	</tr>
	</thead>
	<tbody>';
		$qry = "SELECT trn.*, product.product_icode, product.product_name,product.product_icode, dr.drawing_number, product.product_alias_name,ttc.tax_gst, per.unit_name,con_per.unit_name as con_unit_name FROM `tbl_purchaseordertrn` as trn 
	left join product_mst as product on product.product_id=trn.product_id 
	left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
	left join unit_mst as per on per.unitid=trn.unit_id
	left join unit_mst as con_per on con_per.unitid=trn.conv_unit_id
	left join mst_hsn_code as hsn on hsn.hsn_id=product.product_hsn
	left join tbl_tax_category as ttc on ttc.tax_cat_id =hsn.sale_gst
	where purchaseordertrn_status=0 and purchaseorder_id=" . $rel['purchaseorder_id'] . " group by purchaseordertrn_id order by purchaseordertrn_id";

		$trn_qry_rs = $dbcon->query($qry);
		$p = 1;
		$ttl_amt = 0;
		$ttl_qty = 0;
		$total_cs_gst = 0;
		$total_i_gst = 0;
		$gst_per = 0;
		$gst_rate = 0;
		$cnt = brp_mysqli_num_rows($trn_qry_rs);
		while ($trn_rel = brp_mysqli_fetch_array($trn_qry_rs)) {
			$gst_per = $trn_rel['cgst_tax_per'] + $trn_rel['sgst_tax_per'] + $trn_rel['igst_tax_per'];
			$gst_rate = $trn_rel['cgst_tax_rate_conv'] + $trn_rel['sgst_tax_rate_conv'] + $trn_rel['igst_tax_rate_conv'];

			if ($trn_rel['cgst_tax_rate_conv'] != 0 || $trn_rel['sgst_tax_rate_conv'] != 0) {
				$total_cs_gst += $gst_rate;
			} else {
				$total_i_gst += $gst_rate;
			}

			if (in_array('drawing', $pro_search)) {
				$drawing_number = " -- (" . $trn_rel['drawing_number'] . ")";
			}
			if (in_array('item', $pro_search)) {
				$item_code = " -- (" . $trn_rel['product_icode'] . ")";
			}
			if (in_array('alias', $pro_search)) {
				$alias = " -- (" . $trn_rel['product_alias_name'] . ")";
			}

			$que = "select smp.po_req_no as work_order_no,smp.sales_order_no as so_order_no,req.indent_no,req.indent_date,req.product_remark, group_concat(req.indent_no) as indent  from tbl_request_product as req
			        left join tbl_set_main_process as smp on smp.sp_id = req.sp_id
			        where rp_id in (" . $trn_rel['po_ref_id'] . ") group by smp.sp_id";

			$res = $dbcon->query($que);
			$rw = brp_mysqli_fetch_array($res);

			if ($companyConfiguration['po_work_order_wise'] == 1) {
				//$sno = "<br> <strong>Sales Order No : ".$rw['so_order_no']."</strong>";
				$wno = "<br> <strong>Work Order No : " . $rw['work_order_no'] . "</strong>";
				$indentno = "<br> <strong>Indent No : " . $rw['indent_no'] . " <br>Indent Date :" . date('d-m-Y', strtotime($rw['indent_date'])) . "</strong>";
				$product_remark = "<br> <strong>Product Remarks : " . $rw['product_remark'] . " </strong>";
			}

			//tax summary calculation start
			if (!empty($trn_rel['tax_val'])) {
				$tax_num = explode(",", $trn_rel['tax_val']);
				$tax_name = explode(",", $trn_rel['tax_name']);
				$total_net_rate = ($trn_rel['product_qty'] * $trn_rel['product_rate']) - $trn_rel['discount'];
				for ($j = 0; $j < count($tax_num); $j++) {
					if (!in_array($tax_name[$j], $tax['per'])) {
						$tax['per'][] = $tax_name[$j];
					}
					$tax['per_total'][$tax_name[$j]] += $total_net_rate * $tax_num[$j] / 100;
				}
			}
			$purchaseorder_due_date = '';
			if ($rel['purchaseorder_due_date'] != "1970-01-01" && $rel['purchaseorder_due_date'] != "0000-00-00") {
				$purchaseorder_due_date = date('d-m-Y', strtotime($rel['purchaseorder_due_date']));
			}
			if ($trn_rel['unit_id'] === $trn_rel['rate_unit']) {
				//$sqty=$trn_rel['product_qty'];
				$bqty = $trn_rel['product_qty'];
				$cqty = $trn_rel['product_conv_qty'];
				$bunit = $trn_rel['unit_name'];
				$cunit = $trn_rel['con_unit_name'];
			} else {
				$bqty = $trn_rel['product_conv_qty'];
				$cqty = $trn_rel['product_qty'];
				$bunit = $trn_rel['con_unit_name'];
				$cunit = $trn_rel['unit_name'];
			}
			$path = 'view/upload/product_images/';
			$html .= '
		<tr>
		<td style="text-align:center;font-size:12px;border-bottom:1px dotted #9b9999;vertical-align:top">' . $p . ' </td>
		<td style="border: none;vertical-align:top;text-align:left;font-size:12px;border-bottom:1px dotted #9b9999;">
		<table style="width:100%;">
		<tr border="0" style="border-radious: 0px; border: none;">';
			$align = "";
			if ($trn_rel['product_type'] == '8') {
				$align = "text-align:right";
			}
			if ($trn_rel['image_name'] != '') {
				$html .= '<td border="0" style="border-radious: 0px; border: none!important;width:30%;font-size:12px;' . $align . '"><img src="' . ROOT . $path . $trn_rel['image_name'] . '" height="50" width="50" class="img-thumbnail" /></td>';
			}
			$html .= '<td border="0" style="font-size:11px;font-weight:bold' . $align . '">' . $trn_rel['product_name'] . ' ' . $drawing_number . ' ' . $item_code . ' ' . $alias . '' . $sno . ' ' . $wno . ' ' . $indentno . ' ' . $product_remark . '</td> 
		</tr>
		</table><br>' . $trn_rel['product_des'];
			if ($delivery_type == 'product_wise') {
				$retu_date = "select sdate.*,unit.unit_name from tbl_purchaseorder_delivery_date as sdate left join unit_mst as unit on unit.unitid=sdate.unit_id where po_delivery_date_status=0 and purchaseordertrn_id=" . $trn_rel['purchaseordertrn_id'];
				$resadate = $dbcon->query($retu_date);

				$html .= '<table width="40%" style="font-size:13px">
			<tr>
			<td><strong>Delivery Date</strong></td>
			<td><strong>Qty</strong></td>
			</tr>';

				while ($rowdate = brp_mysqli_fetch_array($resadate)) {
					$html .= '<tr>
				<td>' . date('d-m-Y', strtotime($rowdate['delivery_date'])) . '</td>
				<td>' . $rowdate['product_qty'] . ' ' . $rowdate['unit_name'] . '</td>
				</tr>';
				}
				$html .= '</table>';
			}
			$html .= '</td>
		<td colspan="2" style="text-align:center;font-size:12px;vertical-align:top;border-bottom:1px dotted #9b9999;"> ' . $trn_rel['product_hsn_code'] . '</td>
		<td colspan="2" style="text-align:center;font-size:12px;vertical-align:top;border-bottom:1px dotted #9b9999;"> ' . $trn_rel['tax_gst'] . '%</td>

		<td style=" text-align:center;font-size:12px;vertical-align:top;border-bottom:1px dotted #9b9999;">' . $purchaseorder_due_date . '</td>
		
		<td style=" text-align:center;font-size:12px;vertical-align:top;border-bottom:1px dotted #9b9999;">' . $bunit . '<br>' . $cunit . '  </td>
		<td style=" text-align:center;font-size:12px;vertical-align:top;border-bottom:1px dotted #9b9999;">' . without_comma_two_digit_amount($bqty) . '<br>' . without_comma_two_digit_amount($cqty) . ' </td>
		<td style=" text-align:center;font-size:12px;vertical-align:top;border-bottom:1px dotted #9b9999;">' . $rel['currency_symbol'] . ' ' . without_comma_two_digit_amount($trn_rel['product_currency_rate']) . '</td>';
			if ($get_disc['discount'] > 0) {
				$html .= '<td style=" text-align:center;vertical-align:top;border-bottom:1px dotted #9b9999;">' . $rel['currency_symbol'] . ' ' . without_comma_two_digit_amount($trn_rel['product_discount']) . '<br>' . without_comma_two_digit_amount($trn_rel['discount_per']) . ' %</td>';
			}
			$html .= '<td style=" text-align:right;font-size:12px;vertical-align:top;border-bottom:1px dotted #9b9999;"> ' . $rel['currency_symbol'] . ' ' . without_comma_two_digit_amount($trn_rel['product_currency_amount']) . '</td>
			</tr>';

			$ttl_qty = $ttl_qty + $trn_rel['product_qty'];
			$ttl_qty_conv = $ttl_qty_conv + $trn_rel['product_conv_qty'];
			if ($trn_rel['act_amt_flag'] != '1') {
				//$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
				$ttl_amt = $ttl_amt + $trn_rel['product_currency_amount'];
			}
			$p++;
		}
		$pr = 10 - $cnt;

		$html .= '
	<tr>
	<td colspan="6" style=" text-align:center;font-size:12px;border-bottom:1px solid;">Total Qty</td>
	<td colspan="4"  style=" text-align:center;font-size:12px;border-bottom:1px solid;">' . without_comma_two_digit_amount($ttl_qty) . '<br>' . without_comma_two_digit_amount($ttl_qty_conv) . '</td>
	</tr>
	<tr>
	<td colspan="' . $colspan_8 . '" style=" text-align:left;font-size:12px;">Remarks : <span style="color:red"> ' . (($rel['remark'] != '0') ? $rel['remark'] : '') . ' </span> </td>
	<td style=" text-align:right;font-size:12px;border-top:1px solid">TOTAL  </td>
	<td  style=" text-align:right;font-size:12px;border-top:1px solid">' . $rel['currency_symbol'] . ' ' . without_comma_two_digit_amount($ttl_amt, 2) . '</td>
	</tr>
	';
		if ($rel['stateid'] == $comp_rel['stateid']) {
			$html .= '<tr>
		<td colspan="' . ($colspan_8 + 1) . '" style=" text-align:right;font-size:12px;">CGST</td>
		<td  style=" text-align:right;font-size:12px;">' . $rel['currency_symbol'] . ' ' . number_format(($total_cs_gst / 2), 2, ".", "") . '</td>
		</tr><tr>
		<td colspan="' . ($colspan_8 + 1) . '" style=" text-align:right;font-size:12px;">SGST</td>
		<td  style=" text-align:right;font-size:12px;">' . $rel['currency_symbol'] . ' ' . number_format(($total_cs_gst / 2), 2, ".", "") . '</td>
		</tr>';
		} else {
			$html .= '<tr>
		<td colspan="' . ($colspan_8 + 1) . '" style=" text-align:right;font-size:12px;">IGST</td>
		<td  style=" text-align:right;font-size:12px;">' . $rel['currency_symbol'] . ' ' . number_format(($total_i_gst), 2, ".", "") . '</td>
		</tr>';
		}
		$qry11 = "select sum((tc.tax_per*trn.product_currency_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_purchaseordertrn as trn 
	left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
	left join tbl_ledger as l on l.l_id=tc.tax_id 
	where tc.tax_additional='1' and trn.purchaseorder_id=" . $rel['purchaseorder_id'] . " and trn.purchaseordertrn_status!=2 and tc.isdelete='0' group by tc.tax_id";
		$result11 = $dbcon->query($qry11);
		while ($row11 = mysqli_fetch_assoc($result11)) {
			$html .= '<tr>
		<td colspan="' . ($colspan_8 + 1) . '" style=" text-align:right;font-size:12px;"><b>' . $row11['l_name'] . '</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		' . $rel['currency_symbol'] . ' ' . number_format($row11['add_sum'], 2, ".", "") . '
		</b></td>
		</tr>';
		}
		$qry12 = "select b.sundry_amount_conv,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
	from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
	left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
	where b.sundry_voucher_id=" . $rel['purchaseorder_id'] . " and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' and le.default_sundry='0'";
		$result12 = $dbcon->query($qry12);
		while ($row12 = mysqli_fetch_assoc($result12)) {
			$html .= '<tr>
		<td colspan="' . ($colspan_8 + 1) . '" style=" text-align:right;font-size:12px;"><b>' . $row12['l_name'] . '</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		' . $rel['currency_symbol'] . ' ' . number_format($row12['sundry_amount_conv'], 2, ".", "") . '
		</b></td>
		</tr>';
		}
		//$round_off = round($final_total)-$final_total;
		$round_off = 0;
		$html .= '
	<tr>
	<td colspan="' . ($colspan_8 + 1) . '" style=" text-align:right; font-size:12px;border-bottom:1px solid #9b9999;">Total Amount (In Figure) </td>
	<td style=" text-align:right;font-size:14px;font-weight:bold;border-bottom:1px solid #9b9999;">' . $rel['currency_symbol'] . ' ' . without_comma_two_digit_amount(($rel['g_total_conv']), 2) . ' </td>
	</tr>';
		$currency_code = getcurrencydetail($dbcon, $rel['currency_id']);
		$html .= '</tbody></table>';
		$html .= '<table style="page-break-inside: avoid;border-bottom:none">
	
	<tr style="">
	<td style=" text-align:left;font-size:12px;">
	Total Amount (In Words): <span style="font-weight:bold;font-size:12px;">' . convert_number_to_words_new($rel['g_total_conv'], $rel['currency_id'], $currency_code['currency_in_word_end'], $currency_code['currency_in_word']) . '</span></td>
	</tr>	
	</table>';

		$html .= '<table style="page-break-inside: avoid;border-top:none;border-bottom:none" >';
		$html .= '
	<!--<tr style="border-top:none">
	<td colspan="4" style=" text-align:left;font-size:14px;height:60px;vertical-align:top">
	Remarks : <br>
	' . (($rel['remark'] != '0') ? $rel['remark'] : '') . '
	</td>
	<td colspan="4" style="text-align:left;font-size:14px;height:60px;vertical-align:top">
	Comments :
	</td>
	
	</tr>-->
	<tr>
		<td colspan="8" style="border-bottom:0px solid #9b9999;font-size:14px"><strong>Terms & Conditions</strong></td>
	</tr>
	<tr>
		<td colspan="8" style="border-bottom:1px solid #9b9999;font-size:12px">' . $rel['po_condition'] . '</td>
	</tr>';
		$html .= '</table>';

		$html .= '<table style="page-break-inside: avoid; border-top:none; border-bottom:none;font-size:12px" >
		<tr>
			<td style="border-bottom:1px solid;border-right:1px solid #9b9999;width:50%;vertical-align:top">
				GSTIN : <strong>' . $comp_rel['vatno'] . '</strong><br>
				PANNo : <strong>' . $comp_rel['pan_no'] . '</strong>
			</td>
			<td style="border-bottom:1px solid;width:25%;vertical-align:bottom"><strong>Prepared By</strong> </td>
			<td style="border-bottom:1px solid;width:25%;vertical-align:bottom"><strong>For , ' . $comp_rel['company_name'] . ' </strong>
				<br><br><br><br>
				<strong>Authorized Signatory </strong>
			</td>
		</tr>
	</table>';
		/* Get Terms And Condition Start */

		$terms_qry="select qtrm.*,mst.tc_name from tbl_purchaseorder_terms_trn as qtrm 
        left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
        where qtrm.po_terms_trn_status=0 and qtrm.purchaseorder_id=".$rel['purchaseorder_id']." order by qtrm.tc_priority";
        $terms_qry_rs=$dbcon->query($terms_qry);
       if(brp_mysqli_num_rows($terms_qry_rs)){
        $html.='<center class="nextpage"></center>
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


		$html .= '<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
		/*echo $header;
echo $html;exit;*/
		ob_end_clean();

		$file_name = $purchaseorder_no . '.pdf';
		$file_name = str_ireplace("/", "_", $file_name);
		if ($save_file == "No") {
			include("../../view/export/mpdf/mpdf.php");
		} else {
			include("../../../view/export/mpdf/mpdf.php");
		}

		$mpdf = new mPDF('', 'A4', '0', 'calibri', '10', '10', '30', '35', '1', '1');
		//		$mdf->SetFont('ProximaNova');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		if ($rel['po_approval_status'] == '0') {
			$mpdf->SetWatermarkText('NOT APPROVED');
		} else {
			$mpdf->SetWatermarkText();
		}

		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion = true;
		$mpdf->charset_in = 'UTF-8';
		$mpdf->WriteHTML($html);
		if ($save_file == "No") {
			$mpdf->Output();
		} else {
			$mpdf->Output('../../../view/upload/mail_attach/po/' . $file_name, 'f');
		}
		ob_clean();
		return $file_name;
	}
}
