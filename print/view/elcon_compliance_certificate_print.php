<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");

$invoiceid=$_REQUEST['id'];
$type='pdf';
if(strtolower($type) == 'pdf') {
    //Sales Order Data
	$query="select invoice.*,country.country_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust.cust_pincode,cust.cust_mobile,cust.cust_cont_name,cust.gst_no,cust.m_pan,per.c_con_fname,per.c_con_lname from tbl_sales_order as invoice 
	left join tbl_ledger as cust on cust.l_id=invoice.cust_id
	left join tbl_cust_contact as per on per.cust_id = invoice.cust_id
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid
	left join city_mst as city on city.cityid=cust.cityid where sales_order_id=$invoiceid";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$companySettings = getCompanySettings($dbcon);
	$company_name=$rel['company_name'];
	$cust_address=$rel['cust_address'];
	$city_name=$rel['city_name'];
	$state_name=$rel['state_name'];
	$country_name=$rel['country_name'];
	$cust_pincode=$rel['cust_pincode'];
	$gst_no=$rel['gst_no'];
	$pan_no = $rel['m_pan']; 
	if($rel['cust_cont_name']!='' && $rel['cust_cont_name']!='0'){
		$contact_person = $rel['cust_cont_name'];
	}else{
		$contact_person = $rel['c_con_fname'].' '.$rel['c_con_lname'];
	}

	$set = "select * from tbl_company where company_id=".$rel['company_id'];
	$set_head = mysqli_fetch_assoc($dbcon->query($set));	
	$order_date='';$dispatch_date='';
	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
	$header ='<img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:8.27in" />';  
//$header ='<img src="'.DOMAIN_F.LOGO.'elcon.png" style="width:3.27in;padding-top:25px;" />';  
//$header =$comp_rel["logo"];
//$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';

	$party_address='<div style="text-align:left;">
	<strong>Name :</strong>'.$company_name.'<br/>
	<strong>Address :</strong>'.(($cust_address) ? nl2br($cust_address) : '').' <br>
	'.$city_name.',
	'.$state_name.',
	'.$country_name.',
	'.$pincode.'
	<strong>Phone :</strong>'.$rel["quot_subject"].' <br>
	<strong>Contact Person :</strong>'.$contact_person.'<br>
	<strong>GSTIN :</strong>'.$gst_no.'<br>
	<strong>PAN No:</strong>'.$pan_no.'<br>
	</div>';

	if($rel['consignee_id']==0){
		if(!empty($cust_pincode)){
			$pincode="- ".$cust_pincode;
		}
		$party_address_con="
		<strong>".$company_name."</strong>
		<span style='font-weight:normal;'> <br/>
		".$cust_address.",<br/>
		".$city_name.",
		".$state_name.",
		".$country_name."
		".$pincode."</span>
		<br> <strong> GSTIN : ".$gst_no."</strong>";
	}else{
		$query_con="select cust.cust_name,cust.company_name,cust.cust_address,cust.cust_mobile,cust.cust_email,cust.cust_pincode,cust.gst_no,country.country_name,state.state_name,city.city_name from tbl_custmer_consignee as cust 
		left join country_mst as country on country.countryid=cust.countryid
		left join state_mst as state on state.stateid=cust.stateid
		left join city_mst as city on city.cityid=cust.cityid
		where cust_id=".$rel['consignee_id'];
		$rel_con=brp_mysqli_fetch_assoc($dbcon->query($query_con));	
		$cpincode="";
		if(!empty($rel_con['cust_pincode'])){
			$cpincode="- ".$rel_con['cust_pincode'];
		}
		$party_address_con="
		<strong>".$rel_con['company_name']."</strong>
		<span style='font-weight:normal;'> <br/>
		".$rel_con['cust_address'].",<br/>
		".$rel_con['city_name'].",
		".$rel_con['state_name'].",
		".$rel_con['country_name']."
		".$cpincode."</span>
		<br> <strong> GSTIN : ".$rel_con['gst_no']."</strong>";
	}

	$approve_status='';
	if($rel['approve_status']=='0'){
		$approve_status=' (DRAFT)';
	}

	$html ='<html>
	<head>					
	<title>Compliance Certificate</title>
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
		<td colspan="4" style="text-align:center;font-size:15px;font-weight:bold;">Compliance Certificate</td>
		</tr>
		<tr style="border-bottom: none;">
		<td colspan="4" style="font-weight:bold;border-bottom: none;border-left: 1px solid;border-right: 1px solid;">Compliance Date : </td>
		</tr>
		<tr style="border-top: none;">
		<td colspan="4" style="font-weight:bold;border-top: none;border-left: 1px solid;border-right: 1px solid;">To, '.$company_name.'</td>
		</tr>
		<tr style="border-bottom: none;">
		<td colspan="4">This is to certify that the material supplied as per following List against your:-</td>
		</tr>
		<tr style="border-top: none;border-bottom: none;">
		<td width="10%" style="vertical-align:top;border-right: none;">O.A. No. </td>
		<td width="40%" style="border-right: none;border-left: none;"> : '.$rel['sales_order_no'].' </td>
		<td width="10%" style="border-right: none;border-left: none;">O.A. Dt </td>
		<td width="40%" style="border-left: none;"> : '.date('d-M-y').'</td>
		</tr>
		<tr style="border-top: none;border-bottom: none;">
		<td style="border-right: none;">PO No </td>
		<td style="border-right: none;border-left: none;">: '.$rel['sales_order_no'].'</td>
		<td style="border-right: none;border-left: none;">P.O Date </td>
		<td style="border-left: none;">: '.date('d-M-y', strtotime($rel['sales_order_date'])).'</td>
		</tr>
		</table>

		<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		<thead>
		<tr>
		<th style="width:2%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
		<th style="width:8%;text-align:center;border:1px solid;">Item Code</th>
		<th style="width:60%;text-align:center;border:1px solid;" >Description</th>
		<th style="width:20%;text-align:center;border:1px solid;">Quantity</th>
		</tr>
		</thead>
		<tbody>';
		$trn_qry="select *,cat.cat_name FROM `tbl_sales_ordertrn` as trn 
		left join product_mst as product on product.product_id=trn.product_id 
		left join tbl_category as cat ON cat.cat_id = product.product_category
		left join unit_mst as per on per.unitid=trn.unit_id 
		where sales_ordertrn_status=0 and sales_order_id=".$rel['sales_order_id'];
		$trn_qry_rs=$dbcon->query($trn_qry);
		$p=1;
		$cnt=mysqli_num_rows($trn_qry_rs);
		while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
			$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
			<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			<b>'.nl2br($trn_rel['product_icode']).'</b>
			</td>
			<td style="text-align:left;border:1px solid;vertical-align:top;">
			'.$trn_rel['product_name'].'</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			'.$trn_rel['product_qty'].' '.$trn_rel['unit_name'].'
			</td>
			</tr>';
			$p++;
		}
		$pr=15-$cnt;
		for($j=0; $j<$pr; $j++)
		{
			$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
			<td style="border:none;border-left:1px solid;border-right:1px solid;height:35px;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			</tr>';
		}

		$html.='<tr style="border-bottom: none;">
		<td colspan="4" style="text-align:top;">Remarks:
		'.(($rel['remark']) ? nl2br($rel['remark']) : '').'
		</td>
		</tr>
		<tr style="border-bottom: none;">
		<td colspan="4" style="text-align:top;">'.$companySettings['sales_order_print'].'</td>
		</tr>
		</tbody>
		</table>';

		$coordinator_qry ="SELECT l_name,cust_mobile FROM `tbl_ledger` WHERE l_status=0 and l_id=".$rel['coordinator_id'];
		$coordinator = mysqli_fetch_assoc($dbcon->query($coordinator_qry));
		$user_qry ="select * from users where user_id=".$_SESSION['user_id'];
		$user = mysqli_fetch_assoc($dbcon->query($user_qry));

		$html.='<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		<tbody>
		<tr>
		<td width="50%" rowspan="2" style="vertical-align: top;"><strong>AN ISO 9001 : '.$companySettings['iso_no'].'<br>SSI : '.$companySettings['ssi_no'].'<br>NSIC - CRISIL : '.$companySettings['nsic_no'].'</strong></td>
		<td style="vertical-align:top;height: 80px;border-bottom: none; text-align: center;" width="50%"> For <strong>'.$set_head['company_name'].'</strong></td>
		</tr>
		<tr>
		<td style="text-align: center;border-top: none;"><strong>Authorised By</strong></td>
		</tr>
		</tbody>
		</table>
		<!--page1 end-->';

		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
		// echo $trn_qry;
		// echo $html;exit;
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
		$mpdf->pagenumPrefix = 'Page ';
		$mpdf->pagenumSuffix = ' of ';
		$mpdf->nbpgPrefix = ' ';
		$mpdf->nbpgSuffix = ' ';
		$mpdf->SetFooter('{PAGENO}{nbpg}');

		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='UTF-8';
		$mpdf->WriteHTML($html);
		$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
		ob_clean();
		return 'compliance_certificate'.$quotation_id.'.pdf';
	}

	?>
