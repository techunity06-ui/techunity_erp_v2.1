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
	$query="select po.*,state.state_name,modesup.mode_dispatch,payterms.payment_terms as payment_term,l.l_name as vender_name,country.country_name,l.m_address as vender_address,l.gst_no as tin_no,l.cust_mobile as vender_mobile, l.stateid,l.cust_email as vender_email, state.gst_state_code, city.city_name, bmast.branch_address, city1.city_name as bcity, state1.state_name as bstate, country1.country_name as bcountry, l.emp_signature_img from tbl_purchaseorder as po 
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
		table tr,td{
			border:1px solid !important;
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
		<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
		<tr>
		<td colspan="3" style="font-weight:bold; text-align: center"><h1>PURCHASE ORDER</h1></td>
		</tr>
		<tr>
		<td rowspan="4" style="vertical-align: top; width: 60%;"><strong>Vendor Detail:<br>To,<br>K/A :</strong> '.$rel['vender_name'].'<br><strong>Name :</strong> '.$rel['vender_name'].'<br><strong>Address :</strong> '.nl2br($rel['vender_address']).'<br><strong>GST No :</strong> '.$rel['tin_no'].'<br><strong>Contact No :</strong> '.$rel['vender_mobile'].'</td>
		<td style="width: 20%;font-weight: bold;">PO No : '.$rel['purchaseorder_no'].'</td>
		<td style="width: 20%;">Date : '.$purchaseorder_date.'</td>
		</tr>
		<tr>
		<td style="font-weight: bold;">Indent No : '.$indent_row['indent'].'</td>
		<td>Date : </td>
		</tr>
		<tr>
		<td style="font-weight: bold;">Offer No : '.$rel['quotation_no'].'</td>
		<td>Date : '.$quotation_date.'</td>
		</tr>
		<tr>
		<td style="font-weight: bold;">PO.Ammendment No : '.$rel['purchaseorder_no'].'</td>
		<td>Date : '.$purchaseorder_date.'</td>
		</tr>
		<tr>
		<td colspan="3" style="font-size: 14px;">Please supply the following goods / Services to us as per delivery schedule and terms and conditions mentioned below.</td>
		</tr>
		</table>
		<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		<thead>
		<tr style="border: 1px solid;">
		<th style="border: 1px solid; width:5%;text-align:center;">Sr</th>
		<th style="border: 1px solid; width:35%;text-align:center;">DESCRIPTION OF GOODS</th>
		<th style="border: 1px solid; width:8%;text-align:center;">Quantity</th>
		<th style="border: 1px solid; width:8%;text-align:center;">Unit</th>
		<th style="border: 1px solid; width:10%;text-align:center;">Unit Rate</th>';
		if($disc_row['discount']>0){ 
			$html.='<th style="border: 1px solid; width:7%;text-align:center;">Discount</th>';
		}
		$html.='<th style="border: 1px solid; width:10%;text-align:center;">Amount (INR)</th>
		</tr>
		</thead>
		<tbody>';
		$qry="select trn.*,product.*,product.product_desc as scode,per.unit_name,per1.unit_name as base_unit_name,per2.unit_name as conv_unit_name,group_concat(tax.tax_value) as tax_val,group_concat(tax.tax_name) as tax_name FROM `tbl_purchaseordertrn` as trn 
		left join product_mst as product on product.product_id=trn.product_id 
		left join unit_mst as per on per.unitid=trn.unit_id 
		left join unit_mst as per1 on per1.unitid=product.product_base_unit 
		left join unit_mst as per2 on per2.unitid=product.product_conv_unit 
		left join `formula_mst` as ftax on ftax.formulaid=trn.formulaid left join tbl_tax as tax on find_in_set(tax.tax_id,ftax.tax_id)
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

			$html.='<tr style="border:1px solid;">
			<td style="text-align:center;vertical-align:top;">'.$i.'</td>
			<td style="text-align:left;vertical-align:top;">
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
			<td style="text-align:center;vertical-align:top;">
			'.$row['product_qty'].'
			</td>
			<td style="text-align:center;vertical-align:top;">'.$row['unit_name'].'</td>
			<td style="text-align:center;vertical-align:top;">'.number_format($row['product_rate'],2,".","").'</td>';
			if($disc_row['discount']>0){
				$html.='<td style="text-align:center;vertical-align:top;">'.number_format($row['product_discount'],2,".","").'<br>('.$pro_disc.')</td>';
			}
			$html.='<td style="text-align:center;vertical-align:top;">'.number_format($row['product_amount'],2,".","").'</td>
			</tr>';
			$i++; 
			$totalqty=$totalqty+$row['product_qty']-$charges_qty;$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
			$total_product_amount+=$row['product_amount'];
			$totaltaxable+=$taxable_amt;
			$total+=$row['total'];
		}
		$pr=7-$cnt;
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
		$html.='<tr>
		<td colspan="'.$colspan.'"></td>
		<td style="text-align:right; font-weight: bold;background: #ededed;">Basic Total</td>
		<td style="text-align:right;font-weight: bold;background: #ededed;">'.number_format($total_product_amount,2,".","").'</td>
		</tr>
		</tbody>
		</table>';
		if(!empty($rel['po_condition'])){
		$html.='<div style="font-weight: bold;">
		<h4>Commercial Terms & Conditions</h4>
		</div>
		<div>'.(($rel['po_condition']) ? $rel['po_condition'] : '').'</div>';
	}
		$html.='<table>
		<tbody>
		<tr>
		<td style="vertical-align:bottom; text-align: center; height: 80px;"></td>
		<td style="vertical-align:bottom; text-align: center; height: 80px;"><img src="'.DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'].'" style="height: 85px;width: 120px;"/></td>
		</tr>
		<tr>
		<td style="text-align: center; font-weight: bold;">Dinesh Kumar (Head Purchase)</td>
		<td style="text-align: center; font-weight: bold;">Hanumant Singh Rajpurohit (MD)</td>
		</tr>
		<tr>
		<td style="text-align: center; font-weight: bold;">Coroseal Contact Detail</td>
		<td style="text-align: center; font-weight: bold;">Supplier Contact Detail</td>
		</tr>
		<tr>
		<td style="text-align: left; font-weight: bold;">Person : Dinesh Kumar Rajpurohit</td>
		<td style="text-align: left; font-weight: bold;">Person : '.$rel['vender_name'].'</td>
		</tr>
		<tr>
		<td style="text-align: left; font-weight: bold;">Number : +91 9619667243</td>
		<td style="text-align: left; font-weight: bold;">Number : '.$rel['vender_mobile'].'</td>
		</tr>
		<tr>
		<td style="text-align: left; font-weight: bold;">Email : dinesh@coroseal.com</td>
		<td style="text-align: left; font-weight: bold;">Email : '.$rel['vender_email'].'</td>
		</tr>
		</tbody></table>
		<div style="clear:both;"></div>
		</div>
		<center class="nextpage"></center>
		<!--page1 end-->';
		$html.='<div><table style="font-size:14px;width:100%;">
			<tr>
				<td colspan="2" style="width:100%"><strong>Terms & Conditions :<strong></td>
			</tr>
			<tr>
				<td style="width:10%"><strong>1</strong></td>
				<td style="width:90%"><strong>Delivery Address  :</strong> The material shall be delivered at following address :</td>
			</tr>
			<tr>
				<td style="width:10%"></td>
				<td style="width:90%;padding:0px">
					<table style="font-size:14px;width:100%;">
						<tr style="border:none !important">
							<td style="width:50%;text-align:left;border:none !important">
							Coroseal Chemical Equipment Pvt. Ltd.<br>
							Area, Village - Padghe,<br>
							Dist. - Raigad, Taloja - 410208, Mahrashtra, INDIA<br>
							Jadhav, Contact Number : +91 9823296587
							</td>
							<td style="width:50%;text-align:right;border:none !important">
							 Plot No H - 7/8, MIDC Industrial<br>
							 Contact Person : Mr Ajinkya 
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td><strong>2</strong></td>
				<td><strong>Acceptance :</strong> The order will be binding upon the supplier on receipt thereoff, and considered accepted by supplier. The formal communication shall be made to coroseal within 7 days of receipt of PO, failing which the order may be considered as accepted OR revocked/cancelled at the option of Coroseal.</td>
			</tr>
			<tr>
				<td><strong>3</strong></td>
				<td><strong>Quality :</strong> Goods supplied against the order must, in all respect confimed to description to this order/approved samples/specifications and shall be subject to final approval by Coroseal. All relevent Test certificates shall be submitted along with the material.</td>
			</tr>
			<tr>
				<td>i</td>
				<td>Goods must be accompanied by suppliers / Manufactures certificate, guaranting the quality of each batch/lot been supplied.</td>
			</tr>
			<tr>
				<td>ii</td>
				<td>Goods supplied, not confirming to specifications/standard/approved samples, will be rejected. Rejeted goods should be removed from the place of business of coroseal/wherever stored forthwith.</td>
			</tr>
			<tr>
				<td>iii</td>
				<td>So long as rejected goods lie at coroseals premises for any reason they shall be entirly at suppliers risk and responsibilty.</td>
			</tr>
			<tr>
				<td><strong>4</strong></td>
				<td><strong>Payment :</strong> Unless otherwise stated the payment shall be made within 90 days from the date of receipt of goods and bills in triplicate complete in all respect.</td>
			</tr>
			<tr>
				<td><strong>5</strong></td>
				<td><strong>Packing :</strong> Supplier shall ensure suitable, secure and transshipment worthy packing for all goods supplied against the order. The packing list must accompany each consignment.</td>
			</tr>
			<tr>
				<td><strong>6</strong></td>
				<td><strong>Warranty :</strong> Supplier shall warrant all goods delivered hereunder to be free from all defects in material or workmanship and confirming strictly to the specification and quality standard as provided by Coroseal.</td>
			</tr>
			<tr>
				<td><strong>7</strong></td>
				<td><strong>Insurance :</strong> Unless otherwise stipulated by Coroseal in writing, the goods supplied aganst this order are to be ensured by supplier on receipt of necessary advise (overall cost including taxes, freight and oher incidental and particullary carriers goods receipt) to be intimated by supplier on the date of dispatch. All losses incurred in transportaton shall be to suppliers account.</td>
			</tr>
			<tr>
				<td><strong>8</strong></td>
				<td><strong>Jurisdiction :</strong> Only the courts at Mumbai, India shall be the venue of any proceeding arising from any dispute out of this PO. The contract shall be governed in all respect by the Indian laws.</td>
			</tr>
		</table>
		<br><br>
		<table style="font-size:14px;width:100%;">
			<tr>
				<td colspan="2" style="font-size:16px;text-align:center"><strong>Purchase order Acknowldgement cum acceptance by Vendor</strong></td>
				
			</tr>
			<tr>
				<td colspan="2" style="font-size:14px;text-align:left"><strong>To,Coroseal Chemical Equipment Pvt. Ltd.</strong></td>
				
			</tr>
			<tr>
				<td colspan="2" style="font-size:14px;text-align:left">We hereby acknowledge and accept the terms and conditions as mentioned in this PO along with all attachment without any deviation and shall bound to complete the same.</td>
				
			</tr>
			<tr>
				<td style="font-size:14px;text-align:center;vertical-align:center;width:50%">
						<br><strong>Sign & Seal of Vendor</strong></td>
				<td style="width:50%"><br><br><br><br><br></td>
			</tr>
			<tr>
				<td style="font-size:14px;text-align:left;"><strong>Date:</strong></td>
				<td style="font-size:14px;text-align:left;"><strong>Place:</strong></td>
			</tr>
		</table>
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