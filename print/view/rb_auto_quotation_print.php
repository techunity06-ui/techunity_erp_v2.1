<?php 
session_start();
include("../../config/config.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	QUOTATION_SLUG_PRINT
]);

if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
$form="Quotation";       
$quotation_id = $_REQUEST['id'];	
$type='pdf';
if(strtolower($type) == 'pdf') {
//Quotation Data
	$query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date from tbl_quotation as quot
	left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
	left join tbl_customer as cust on cust.cust_id=quot.cust_id
	left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
	where quot.quotation_id=".$quotation_id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));

	$com_mobile= ($rel['c_con_mobile']) ? $rel['c_con_mobile'] : '';
	$com_email= ($rel['c_con_email']) ? $rel['c_con_email'] : '';
//Company Data
	$set="SELECT comp.*,state.state_name,state.gst_state_code FROM tbl_company AS comp LEFT JOIN state_mst AS state on comp.stateid=state.stateid WHERE company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));

	$header ='<table style="width: 100%;">
	<tbody>
	<tr>
	<td style="width: 65%; vertical-align: top; text-align: left"><img src="'.DOMAIN_F.LOGO.$set_head['logo'].'" /></td>
	<td style="width: 35%; text-align: right;"><h1>'.$form.'</h1></td>
	</tr>
	</tbody>
	</table>'; 
	$approve_status='';
	if($rel['approve_status']=='0'){
		$approve_status=' (DRAFT)';
	}
	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));

	$user_phone = ($userData['user_phone']) ? $userData['user_phone'] : '';

	$sql=$dbcon->query("SELECT trn.* WHERE trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id']);
	
	$chksql=$dbcon->query("SELECT SUM(discount_per) AS discount FROM `tbl_quotation_trn` WHERE quot_trn_status=0 and quotation_id=".$rel['quotation_id']);
	
	$getrows=mysqli_num_rows($sql);
	$disc_row=mysqli_fetch_assoc($sql);
	$get_row=mysqli_fetch_assoc($chksql);

	if($get_row['discount']>0){
		$colspan=6;
		$cols=8;
	}else{
		$colspan=5;
		$cols=7;
	}
	// $cols=$colspan;
	$rows=(($rel['qt_add_state']==$set_head['stateid']) ? 2 : 1)+2;

	if($rel['quot_type']=='0'){
		$currency_name = '(INR)';
		$currency_word_start = 'Rupees';
		$currency_word_end = 'Paise';
	}else{
		$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
		$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

		$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
		$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
		$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
	}
	$sundrytax=$dbcon->query("select b.* from tbl_bill_sundry_transaction as b where b.sundry_voucher_id=".$rel['quotation_id']." and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' ");
//$sundrytax_rels = mysqli_fetch_assoc($sundrytax);
$total_sundrytax=0;
while($sumsundrytax=mysqli_fetch_assoc($sundrytax)){
	$total_sundrytax = $total_sundrytax + $sumsundrytax['sundry_gst_amount_conv'];
}

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
			border:none !important;
			/*page-break-inside:avoid;*/
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
		<table style="font-size:12px; width:100%; table-layout: fixed;">
		<tr>
		<td style="vertical-align: top; width: 60%;" rowspan="6"><strong>K/A: '.$rel['c_con_fname'].' '.$rel['c_con_lname'].'<br>To,<br>'.$rel['cust_name'].'</strong><br><strong>Address:</strong> '.(stripcslashes(str_replace(array("\n", "\r", "\N"), '', $rel['quot_address']))).'<br><strong>Mobile:</strong> '.$com_mobile.'<br><strong>Email:</strong> '.strtolower($com_email).'</td>
		<td style="font-weight: bold; width: 20%;">Quotation No:</td>
		<td style="width: 20%;">'.$rel['quotation_no'].'</td>
		</tr>
		<tr>
		<td style="font-weight: bold;">Quotation Date:</td>
		<td>'.date("d-m-Y",strtotime($rel['quotation_date'])).'</td>
		</tr>
		<tr>
		<td style="font-weight: bold;">Sales Co-Ordinator:</td>
		<td>'.$userData['user_name'].'</td>
		</tr>
		<tr>
		<td  style="font-weight: bold; vertical-align: top;">Mobile:</td>
		<td style="vertical-align: top;">'.$user_phone.'</td>
		</tr>
		<tr>
		<td style="font-weight: bold;">Inquiry Reference:</td>
		<td>'.$rel['quotation_ref'].'</td>
		</tr>
		<tr>
		<td style="font-weight: bold;">Inquiry Date:</td>
		<td>'.date("d-m-Y",strtotime($rel['inquiry_date'])).'</td>
		</tr>
		</table>
		<table style="font-size:12px;border-collapse: collapse;width:100%; margin-top: 20px;" >
		<thead>
		<tr>
		<th style="width:5%;text-align:center;background: #ededed;">Sr.<br/>No.</th>
		<th style="width:35%;text-align:center;background: #ededed;">Item Description</th>
		<th style="width:5%;text-align:center;background: #ededed;">HSN Code</th>
		<th style="width:8%;text-align:center;background: #ededed;">Qty</th>
		<th style="width:8%;text-align:center;background: #ededed;">UOM</th>
		<th style="width:10%;text-align:center;background: #ededed;">Unit Price '.strtoupper($currency_name).'</th>';
		if($get_row['discount']>0){ 
			$html.='<th style="width:7%;text-align:center;background: #ededed;">Disc.</th>';
		}
		$html.='<th style="width:10%;text-align:center;background: #ededed;">Total '.strtoupper($currency_name).'</th>
		</tr>
		</thead>
		<tbody>';
		$trn_qry="select trn.*,pro.product_name,hsn.hsn_code,unit.unit_name from tbl_quotation_trn as trn 
		left join product_mst as pro on pro.product_id=trn.product_id
		left join unit_mst as unit on unit.unitid=trn.unitid
		left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
		where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
		$trn_qry_rs=$dbcon->query($trn_qry);
		$p=1;$ttl_amt=0;$ttl_qty=0; $total_gst=0;$total_i_gst=0;$gst_per=0;
		$cnt=mysqli_num_rows($trn_qry_rs);
		while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
			$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
			$gst_rate = $trn_rel['cgst_tax_rate_conv']+$trn_rel['sgst_tax_rate_conv']+$trn_rel['igst_tax_rate_conv']+$total_sundrytax;

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
				$total_net_rate=($trn_rel['product_qty']*$trn_rel['product_rate_conv'])-$trn_rel['discount'];
				for($j=0;$j<count($tax_num);$j++)
				{
					if(!in_array($tax_name[$j],$tax['per']))
					{
						$tax['per'][]=$tax_name[$j];
					}
					$tax['per_total'][$tax_name[$j]]+=$total_net_rate*$tax_num[$j]/100;
				}
			}
			$pro_descri = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '' ;
			$product_hsn_code = ($trn_rel['hsn_code']) ? $trn_rel['hsn_code'] : '' ;
			$pro_disc = ($trn_rel['discount_per']) ? $trn_rel['discount_per']." %" : '';

			$html.='<tr style="border:none;">
			<td style="text-align:center;vertical-align:top;">'.$p.'</td>
			<td style="text-align:left;vertical-align:top;">
			<strong>'.$trn_rel['product_name'].'</strong><br/>
			'.$pro_descri.'
			</td>
			<td style="text-align:center;vertical-align:top;">'.$product_hsn_code.'</td>
			<td style="text-align:center;vertical-align:top;">
			'.$trn_rel['product_qty'].'
			</td>
			<td style="text-align:center;vertical-align:top;">'.$trn_rel['unit_name'].'</td>
			<td style="text-align:center;vertical-align:top;">'.number_format($trn_rel['product_rate_conv'],2,".","").'</td>';
			if($get_row['discount']>0){
				$html.='<td style="text-align:center;vertical-align:top;">'.number_format($trn_rel['product_discount_conv'],2,".","").'<br>('.$pro_disc.')</td>';
			}
			$html.='<td style="text-align:center;vertical-align:top;">'.number_format($trn_rel['product_amount_conv'],2,".","").'</td>
			</tr>';
			$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
			if($trn_rel['act_amt_flag']!='1'){
				$ttl_amt=$ttl_amt+$trn_rel['product_amount_conv'];
			}

			$p++;
		}
		/*$pr=7-$cnt;
		for($j=0; $j<$pr; $j++)
		{
			$html.='<tr style="border:none;">
			<td style="border:none;height:25px;"></td>
			<td style="border:none;"></td>
			<td style="border:none;"></td>
			<td style="border:none;"></td>
			<td style="border:none;"></td>
			<td style="border:none;"></td>';
			if($get_row['discount']>0){
				$html.='<td style="border:none;"></td>';
			}
			$html.='<td style="border:none;"></td>
			</tr>';
		}*/
		$remark = ($rel['quot_remark']) ? $rel['quot_remark'] : '';
		$qry11="select sum((tc.tax_per*trn.product_amount_conv)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_quotation_trn as trn 
		left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
		left join tbl_ledger as l on l.l_id=tc.tax_id 
		where tc.tax_additional='1' and trn.quotation_id=".$rel['quotation_id']." and trn.quot_trn_status!=2 and tc.isdelete='0' group by tc.tax_id";
		$result11=$dbcon->query($qry11);

		$qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id,b.sundry_amount_conv 
		from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
		left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
		where b.sundry_voucher_id=".$rel['quotation_id']." and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0'";
		$result12=$dbcon->query($qry12);

		$rows = $rows + mysqli_num_rows($result11) + mysqli_num_rows($result12);

		$html.='<tr>
		<td colspan="'.$colspan.'" rowspan="'.$rows.'" style="text-align:left; vertical-align: top; font-weight: bold;"></td>
		<td style="text-align:right; font-weight: bold;background: #ededed;">Total Basic</td>
		<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format($ttl_amt,2,".","").'</td>
		</tr>';
		if($rel['qt_add_state']==$set_head['stateid']){
			//$total_cs_gst=$total_cs_gst+$total_sundrytax;
			$html.='<tr>
			<td style="text-align:right; font-weight: bold;background: #ededed;">CGST ('.($gst_per/2).' %)</td>
			<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format(($total_cs_gst/2),2,".","").'</td>
			</tr>
			<tr>
			<td style="text-align:right; font-weight: bold;background: #ededed;">SGST ('.($gst_per/2).' %)</td>
			<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format(($total_cs_gst/2),2,".","").'</td>
			</tr>';
		}else{
			// $total_i_gst=$total_i_gst+$total_sundrytax;
			$html.='<tr>
			<td style="text-align:right; font-weight: bold;background: #ededed;">IGST ('.($gst_per).' %)</td>
			<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format(($total_i_gst),2,".","").'</td>
			</tr>';
		}

		while($row11=mysqli_fetch_assoc($result11))
		{
			$html.='<tr>
			<td style="text-align:right; font-weight: bold;background: #ededed;">'.$row11['l_name'].'</td>
			<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format($row11['add_sum'],2,".","").'</td>
			</tr>';
		}
		
		while($row12=mysqli_fetch_assoc($result12))
		{
			$html.='<tr>
			<td style="text-align:right; font-weight: bold;background: #ededed;">'.$row12['l_name'].'</td>
			<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format($row12['sundry_amount_conv'],2,".","").'</td>
			</tr>';
		}
		$html.='<tr>
		<td style="text-align:right; font-weight: bold;background: #ededed;">Total</td>
		<td style="text-align:right; font-weight: bold;background: #ededed;">'.number_format((float)($rel['g_total_conv']), 2, '.', ',').'</td>
		</tr>
		<tr>
		<td colspan="'.$cols.'" style="font-weight: bold; text-align: right;">Total In Words: '.ucfirst(convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$currency_word_end,$currency_word_start)).'</td>
		</tr>
		<tr>
		<td colspan="'.$cols.'" style="font-weight: bold; text-align: left;">REMARKS: '.$remark.'</td>
		</tr>';
		$html.='</tbody></table>
		<div style="clear:both;"></div>';

		$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
		left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
		where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
		$terms_qry_rs=$dbcon->query($terms_qry);
		if(mysqli_num_rows($terms_qry_rs)){
			$html .= '<h3 style="text-align:left;">Terms and Conditions</h3>
			<div><table width="100%" style="font-size:12px;border: none;width:100%;overflow:wrap;"><tbody>';
			$t=1;
			while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
				$string=(nl2br($term_rel['tc_details']));

				$html.='<tr>
				<td width="25%" style="width:25%;text-align:left;padding:5px; vertical-align: top; font-weight: bold;">'.$t.'. '.$term_rel['tc_name'].'</td>
				<td width="75%" style="width:70%;text-align:left;padding:5px;">: '.$string.'</td>
				</tr>';
				$t++;
			}
			$html.='</tbody></table></div>';	
		}
		$html.='<table><tr>
			<td style="vertical-align:bottom; text-align: right; height: 80px; font-weight: bold;"><img src="'.DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'].'" style="height: 100px;width: 100px;"/><br>Authorised Signatory</td>
			</tr></table>';
		if($rel['with_bom_flag']=='1'){
			/* Get Bom of Product Start */
			$product_qry = "select pro.product_id, pro.product_name, pro.product_type 
			from tbl_quotation_trn as trn 
			left join product_mst as pro on pro.product_id=trn.product_id
			left join unit_mst as unit on unit.unitid=trn.unitid
			where trn.quot_trn_status=0 and trn.quotation_id=".$quotation_id;
			$result = mysqli_query($dbcon,$product_qry);
			$products = mysqli_fetch_all($result,MYSQLI_ASSOC);
			$html.='<center class="nextpage"></center>
			<div>
			<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
			<thead>
			<tr>
			<th colspan="6" style="text-align:center;background: #ededed;"><h3>Bill Of Material </h3></th>
			</tr>
			<tr>
			<th style="width:5%;text-align:center;background: #ededed;">Sr.<br/>No.</th>
			<th style="width:50%;text-align:center;background: #ededed;">Description</th>
			<th style="width:15%;text-align:center;background: #ededed;">Item Type</th>
			<th style="width:10%;text-align:center;background: #ededed;">Units</th>
			<th style="width:10%;text-align:center;background: #ededed;">Feedar Qty</th>
			<th style="width:10%;text-align:center;background: #ededed;">Total Qty</th>
			</tr>
			</thead>
			<tbody>';
			$ij=0;
			foreach($products as $product){
				$query="select bom.*,product.product_name,bunit.unit_name as base_unit_name,bunit.unit_name as conv_unit_name, bom_version.is_default_bom,product.product_type from tbl_bom as bom
				left join product_mst as product on product.product_id=bom.bom_product
				left join pro_ms_bom_version as bom_version on bom_version.bom_version_id=bom.bom_version_id
				left join unit_mst as bunit on bunit.unitid=bom.product_base_unit
				left join unit_mst as cunit on cunit.unitid=bom.product_conv_unit
				where bom.bom_product = ".$product['product_id'];
				// echo "<br>";
				$bom_qry_rs=$dbcon->query($query);
				if(mysqli_num_rows($bom_qry_rs)){

					$t=1;
					while($bom_rel=mysqli_fetch_assoc($bom_qry_rs)){
						if($bom_rel['is_default_bom']==1){
							$html .='<tr>
							<td style="font-weight: bold;">'.$ij.'</td>
							<td style="font-weight: bold;">'.$bom_rel['product_name'].'</td>
							<td style="font-weight: bold; text-align: center;">'.get_product_type_by_id($dbcon,$bom_rel['product_type']).'</td>
							<td style="font-weight: bold; text-align: center;">'.$bom_rel['base_unit_name'].'</td>
							<td style="font-weight: bold; text-align: center;">'.$bom_rel['product_base_qty'].'</td>
							<td style="font-weight: bold; text-align: center;">'.$bom_rel['product_base_qty'].'</td>
							</tr>';
							$html .= quotation_print_with_bom($dbcon,$bom_rel['bom_id'],$bom_rel['product_base_qty'],$ij,0,0);
							$t++;
						}
					}

				}
				$ij++;
			}
			$html.='</tbody>
			</table>
			</div>';
		}
		if($rel['inquiry_type']!='1'){
			$html.='<center class="nextpage"></center>
			<div>
			<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
			<thead>
			<tr>
			<th colspan="5" style="text-align:center;background: #ededed;"><h3>Bill Of Material</h3></th>
			</tr>
			<tr>
			<th style="width:5%;text-align:center;background: #ededed;">Sr.<br/>No.</th>
			<th style="width:50%;text-align:center;background: #ededed;">Description</th>
			<th style="width:15%;text-align:center;background: #ededed;">HSN Code</th>
			<th style="width:15%;text-align:center;background: #ededed;">Qty</th>
			<th style="width:15%;text-align:center;background: #ededed;">Unit</th>
			</tr>
			</thead>
			<tbody>';
			$product_qry = "select trn.*, pro.product_name,pro.product_base_unit, unit.unit_name,hsn.hsn_code from tbl_quotation_project_trn as trn
			left join product_mst as pro on pro.product_id=trn.product_id
			left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
			left join unit_mst as unit on unit.unitid=pro.product_base_unit
			where trn.quotation_projecttrn_status=0 and trn.quotation_id=".$quotation_id;
			$result = mysqli_query($dbcon,$product_qry);
			$rt=1;
			while($row=mysqli_fetch_assoc($result)){
				$html.='<tr>
				<td style="text-align:center;vertical-align:top;">'.$rt.'</td>
				<td style="vertical-align:top;">'.$row['product_name'].'</td>
				<td style="text-align:center;vertical-align:top;">'.$row['hsn_code'].'</td>
				<td style="text-align:center;vertical-align:top;">'.$row['product_qty'].'</td>
				<td style="text-align:center;vertical-align:top;">'.$row['unit_name'].'</td>
				</tr>';
				$rt++;
			}
			$html.='</tbody>
			</table>
			</div>';
		}
		/* Get Bom of product end */

		$html.='</div>
		<!--page1 end-->';

		/* Check Annexure Attachments Start */
/*if(trim($rel['quot_annex_content'])){
	$html.='<center class="nextpage"></center>';
	$html.='<div class="quot_annex_content_div">'.$rel['quot_annex_content'];
	$html.='</div>';
}*/
/* Check Annexure Attachments End */

$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
</body>
</html>';
// echo $html;exit;
$file_name = $set_head['company_name'].'_'.$rel['quotation_no'].'.pdf';
		$file_name=str_ireplace("/","_",$file_name);
		$file_name=str_ireplace(" ","_",$file_name);
ob_end_clean();
include("../../view/export/mpdf/mpdf.php");
$mpdf=new mPDF('UTF-8','A4','0','calibri','10','10','40','5','1','1');
$mpdf->SetDefaultFont('opensans');
$mpdf->SetFont('opensans');
$mpdf->defaultheaderfontsize = 10; /* in pts */
$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
$mpdf->defaultfooterfontsize = 10; /* in pts */
$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
$mpdf->SetHTMLHeader($header);
$mpdf->pagenumPrefix = ' ';
$mpdf->pagenumSuffix = ' / ';
$mpdf->nbpgPrefix = ' ';
$mpdf->nbpgSuffix = ' pages';
$mpdf->SetFooter('{PAGENO}{nbpg}');
$mpdf->showWatermarkText = true;
$mpdf->allow_charset_conversion=true;
$mpdf->charset_in='UTF-8';
$mpdf->WriteHTML($html);
$mpdf->Output();
$mpdf->Output('../../view/upload/mail_attach/'.$file_name,'f');
ob_clean();
return $file_name;
}

?>