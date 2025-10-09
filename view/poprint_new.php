<?php session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
$_SESSION['contents']=''; 
$form="Purchase Order";
$mode="Print";
$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
$type='pdf';
if(strtolower($type) == 'pdf') {
//Quotation Data
	$query="select po.*,state.state_name,modesup.mode_dispatch,payterms.payment_terms as payment_term,l.l_name as vender_name,country.country_name,l.m_address as vender_address,l.gst_no as tin_no,l.cust_mobile as vender_mobile,l.stateid,state.gst_state_code,city.city_name from tbl_purchaseorder as po inner join tbl_ledger as l on l.l_id=po.vender_id
	left join country_mst as country on country.countryid=l.countryid
	left join pay_terms as payterms on payterms.terms_id=po.payment_terms
	left join mode_of_dispatch as modesup on modesup.mode_dis_id=po.mode_of_dispatch
	left join state_mst as state on state.stateid=l.stateid
	left join city_mst as city on city.cityid=l.cityid
	where po.purchaseorder_id=$purchaseorder_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));

	$_SESSION['invoice_no']=$rel['invoice_no'];		

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	$order_date='';
	if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
	{
		$order_date=date('d-m-Y',strtotime($rel['order_date']));
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

	}
	$user_qry = "select user_name,user_mail,user_phone from users where user_id=".$_SESSION['user_id']." and company_id=".$rel['company_id'];
	$user_data = mysqli_fetch_assoc($dbcon->query($user_qry));
	/* Check Discount is On or off Start */
	if($set_head['show_disc']=='1'){
		$colspan=5;
		$dynamicwidth=40;
	}else{
		$colspan=6;
		$dynamicwidth=46;
	}
//$header ='<div style="text-align:right;"><img src="'.DOMAIN_F.LOGO.'Hermettic_Equipments.png" style="width:8.27in;padding-top:20px;" /></div>';  
	$header ='<img src="'.DOMAIN_F.LOGO.'hermatic-logo.jpg'.'" style="" />';
//$header =$comp_rel["logo"];
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
			border:1px solid #000 !important;
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
					<td colspan="3" style="text-align:center; font-size:15px; font-weight:bold;">'.$form.'</td>
				</tr>
				<tr>
					<td rowspan="6" style="text-align:left; vertical-align:top; border:1px solid; width:50%;">
						<strong>To, <br>'.$rel['vender_name'].'</strong><br/>'.$rel['vender_address'].'<br/>'.$rel['city_name'].','.$rel['state_name'].','.$rel['country_name'].'<br>GST NO. : '.$rel['tin_no'].'<br>Kind Attn. : '.$rel['vender_name'].'
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
					<td style="text-align:left;border:1px solid;"> '.$rel['quotation_no'].'</td>
				</tr>
				<tr>
					<td style="text-align:left;border:1px solid;"> Quotation Ref Date</td>
					<td style="text-align:left;border:1px solid;"> '.date('d-M-Y', strtotime($rel['quotation_date'])).'</td>
				</tr>
				<tr>
					<td style="text-align:left;border:1px solid;"> Vendor Code</td>
					<td style="text-align:left;border:1px solid;"></td>
				</tr>
				<tr>
					<td style="text-align:left;border:1px solid;">Project Code</td>
					<td style="text-align:left;border:1px solid;"></td>
				</tr>
				<tr style="border-bottom: none;">
					<td rowspan="6" style="text-align:left; vertical-align:top; border:1px solid; width:50%;border-bottom: none;">
						<strong>Ship To, <br>'.$rel['vender_name'].'</strong><br/>'.$rel['vender_address'].'<br/>'.$rel['city_name'].','.$rel['state_name'].','.$rel['country_name'].'
					</td>
					<td style="text-align:left;border:1px solid;">PR No</td>
					<td style="text-align:left;border:1px solid;"></td>
				</tr>
				<tr>
					<td style="text-align:left;border:1px solid;">PO Valid Till</td>
					<td style="text-align:left;border:1px solid;"></td>
				</tr>
				<tr>
					<td style="text-align:left;border:1px solid;">Delivery Date</td>
					<td style="text-align:left;border:1px solid;"> '.date("d-M-Y",strtotime($rel['purchaseorder_due_date'])).'</td>
				</tr>
				<tr>
					<td style="text-align:left;border:1px solid;"> Payment Terms</td>
					<td style="text-align:left;border:1px solid;"> '.$rel['payment_terms'].'</td>
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
					<td style="text-align:left;border:1px solid;"> '.(strtolower($user_data['user_mail'])).'</td>
				</tr>
				<tr style="border-bottom: none;">
					<td colspan="3"><strong> We are pleased to place this Purchase/ Service Order for the supply of the following, subject to the terms and conditions given in annexure. </strong></td>
				</tr>
			</table>
		<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
			<thead>
				<tr>
					<th style="width:5%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
					<th style="width:25%;text-align:center;border:1px solid;">Item Description</th>
					<th style="width:5%;text-align:center;border:1px solid;">HSN Code</th>
					<th style="width:8%;text-align:center;border:1px solid;">Qty</th>
					<th style="width:10%;text-align:center;border:1px solid;">Rate</th>';
					if($set_head['show_disc']=='1'){ 
						$html.='<th style="width:7%;text-align:center;border:1px solid;">Less. Disc.</th>';
					}
					$html.='<th style="width:10%;text-align:center;border:1px solid;">Amount</th>
					<th style="width:5%;text-align:center;border:1px solid;">Rate(%)</th>
					<th style="width:10%;text-align:center;border:1px solid;">Tax Value</th>
					<th style="width:15%;text-align:right;border:1px solid;">Total Price</th>
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
			$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;
			$cnt=mysqli_num_rows($result);
			while($row=mysqli_fetch_assoc($result))
			{
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
				$tax_arr=explode(",",$row['tax_val']);
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
				$total_taxs=$tax_arr[0]+$tax_arr[1];
		//tax summary calculation end
				$taxable_amt=$row['total']-$row['product_amount'];

			$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
			<td style="text-align:center;border:1px solid;vertical-align:top;">'.$i.'</td>
			<td style="text-align:left;border:1px solid;vertical-align:top;">
			<strong>'.$row['product_name'].'</strong><br/>
			'.nl2br($row['description']).'
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">'.$row['product_hsn'].'</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			'.$row['product_qty'].''.$row['unit_name'].'
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">'.number_format($row['product_rate'],2,".","").'</td>';
			if($set_head['show_disc']=='1'){
				$html.='<td style="text-align:center;border:1px solid;vertical-align:top;">'.number_format($row['discount_per'],2,".","").'</td>';
			}
			$html.='<td style="text-align:center;border:1px solid;vertical-align:top;">'.number_format($row['product_amount'],2,".","").'</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">'.$total_taxs.' %</td>
			<td style="text-align:right;border:1px solid;vertical-align:top;">'.number_format($taxable_amt,2,".","").'</td>
			<td style="text-align:right;border:1px solid;vertical-align:top;">'.number_format($row['total'],2,".","").'</td>
			</tr>';
			$i++; 
			$totalqty=$totalqty+$row['product_qty']-$charges_qty;$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
			$total_product_amount+=$row['product_amount'];
			$totaltaxable+=$taxable_amt;
			$total+=$row['total'];
		}
		$pr=10-$cnt;
		for($j=0; $j<$pr; $j++){
			$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
			<td style="border:none;border-left:1px solid;border-right:1px solid;height:25px;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			</tr>';
		}

		$html.='<tr>
			<td colspan="5" style="text-align:left;border:1px solid;color:Black; font-weight: bold;">Basic PO Amount: '. number_format($total_product_amount,2,".","").'</td>
			<td colspan="4" style="text-align:right;border:1px solid; font-weight: bold;">Total Basic</td>
			<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format($total_product_amount,2,".","").'</td>
		</tr>';
		$chkrow=$dbcon->query("select mst.*,t.tax_name,sum(tx_taxable_value) as tamo from tbl_tax_trn as mst 
			left join tbl_tax as t on t.tax_id=mst.tx_tax_id 
			left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=mst.tx_transaction_id
			where trn.purchaseorder_id='".$purchaseorder_id."' and tx_status=0 and tx_transaction_type='purchase_order' group by tx_tax_id");
		$getrows=mysqli_num_rows($rt);
		$rt=$dbcon->query("select mst.*,t.tax_name,sum(tx_taxable_value) as tamo from tbl_tax_trn as mst 
			left join tbl_tax as t on t.tax_id=mst.tx_tax_id 
			left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=mst.tx_transaction_id
			where trn.purchaseorder_id='".$purchaseorder_id."' and tx_status=0 and tx_transaction_type='purchase_order' group by tx_tax_id");
		$k=0;
		while($rel1=mysqli_fetch_assoc($rt)){
			if($getrows>2){
				$rows=3;
			}else{
				$rows=2;
			}
			$rt1=$dbcon->query("select mst.*,t.tax_name,sum(tx_taxable_value) as tamo from tbl_tax_trn as mst 
				left join tbl_tax as t on t.tax_id=mst.tx_tax_id 
				left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=mst.tx_transaction_id
				where trn.purchaseorder_id='$purchaseorder_id' and mst.tx_tax_id=".$rel1['tx_tax_id']." and tx_status=0 and tx_transaction_type='purchase_order' ");
			$rel122=mysqli_fetch_assoc($rt1);
			$html.='<tr>';
				if($k==0){
					$html.='<td colspan="5" rowspan="'.$rows.'" style="text-align:left;border:1px solid;color:Black; font-weight: bold;">GST Amount: '. number_format($totaltaxable,2,".","").'</td>';
				}
				$html.='<td colspan="4" style="text-align:right;border:1px solid; font-weight: bold;">'.$rel1['tax_name'].' Amount</td>
				<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format($rel122['tamo'],2,".","").'</td>
			</tr>';
			$k++;
		}
		$html.='<tr>
			<td colspan="5" rowspan="2" style="text-align:left;border:1px solid;color:Black; font-weight: bold;">Total PO Amount: '. number_format($rel['g_total'],2,".","").'</td>
			<td colspan="4" style="text-align:right;border:1px solid; font-weight: bold;">Total Tax Amount</td>
			<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format($totaltaxable,2,".","").'</td>
		</tr>
		<tr>
			<td colspan="4" style="text-align:right;border:1px solid; font-weight: bold;">Total Amount</td>
			<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format($rel['g_total'],2,".","").'</td>
		</tr>';
		$html.='
		<tr>
		<td colspan="5" rowspan="2" style="height:80px; text-align:left; vertical-align:top; border-left:1px solid; border-bottom:1px solid; font-weight: bold;"><strong>Terms and Conditions:</strong><br> '.$set_head['po_condition'].'</td>
		<td colspan="5" style="text-align:center;vertical-align:top;border-bottom:1px solid;border-right:1px solid;font-weight: bold;height:80px;">For, '.$set_head['company_name'].'</td>
		</tr>
		<tr>
			<td colspan="5"><center style="vertical-align:bottom;">Authorised Signatory</center></td>
		</tr>
		</tbody></table>
		<div style="clear:both;"></div>
		</div>
		<!--page1 end-->';

		/* Get Terms And Condition Start */
		$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
		left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
		where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
		$terms_qry_rs=$dbcon->query($terms_qry);
		if(mysqli_num_rows($terms_qry_rs)){
			$html.='<center class="nextpage"></center>
			<h3 style="text-align:center;">Terms & Conditions for Sales Quotation No : <u>'.$rel['quotation_no'].'</u></h3>
			<div><table width="100%" style="font-size:12px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3"><tbody>';
			$t=1;
			while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
				$string=(nl2br($term_rel['tc_details']));

				$html.='<tr>
				<td width="5%" style="width:5%;text-align:center;border:1px solid;padding:5px;">'.$t.'</td>
				<td width="25%" style="width:25%;text-align:left;border:1px solid;padding:5px;">'.$term_rel['tc_name'].'</td>
				<td width="70%" style="width:70%;text-align:left;border:1px solid #000;padding:5px;">'.$string.'</td>
				</tr>';
				$t++;
			}
			$html.='</tbody></table></div>';	
		}
		/* Get Terms And Condition Start */

		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
		// echo $html;exit;
		ob_end_clean();
		include("../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','25','10','1','1');
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