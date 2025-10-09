<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");

$invoiceid=$_REQUEST['id'];
$type='pdf';
if(strtolower($type) == 'pdf') {
    //Sales Order Data
	$query="select invoice.*,country.country_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust.cust_pincode,cust.cust_mobile,cust.cust_cont_name,cust.gst_no,cust.m_pan,per.c_con_fname,per.c_con_lname,cust.stateid from tbl_job_work as invoice 
	left join tbl_ledger as cust on cust.l_id=invoice.vender_id
	left join tbl_cust_contact as per on per.cust_id = invoice.vender_id
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid
	left join city_mst as city on city.cityid=cust.cityid where job_work_id=$invoiceid";
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
	$order_date='';$dispatch_date='';
	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
	$header ='<img src="'.DOMAIN_F.LOGO.$comp_rel['logo'].'" style="width:8.27in" />';  
//$header ='<img src="'.DOMAIN_F.LOGO.'elcon.png" style="width:3.27in;padding-top:25px;" />';  
//$header =$comp_rel["logo"];
//$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';

	$party_address='<div style="text-align:left;">
	<strong>Name :</strong>'.$company_name.'<br/>
	<strong>Address :</strong>'.(($cust_address) ? nl2br($cust_address) : '').' <br>
	'.$city_name.',
	'.$state_name.',
	'.$country_name.',
	'.$pincode.'<br>
	<strong>Phone :</strong>'.$rel["cust_mobile"].' <br>
	<strong>GSTIN :</strong>'.$gst_no.'</div>';

	$approve_status='';
	if($rel['approve_status']=='0'){
		$approve_status=' (DRAFT)';
	}
	$trn_qry="select job.*, product.product_name, hsn.hsn_code as product_hsn, product.product_icode, product.product_category, categoty.cat_name, process.process_name,baseunit.unit_name as baseunitname,convunit.unit_name as convunitname FROM `tbl_job_work_trn` as job 
	left join product_mst as product on product.product_id=job.product_id 
	left join tbl_category as categoty on categoty.cat_id=product.product_category 
	left join process_mst as process ON process.process_id = job.process_id 
	left join unit_mst as baseunit on baseunit.unitid=job.product_base_unit
	left join unit_mst as convunit on convunit.unitid=job.product_con_unit
	left join mst_hsn_code as hsn on hsn.hsn_id=product.product_hsn
	where job_work_trn_status=0 and job_work_id=".$rel['job_work_id'];
	$trn_qry_rs=$dbcon->query($trn_qry);
	$base_sum=0;
	$prorate=0;
	$pro_name="";
	$pro_hsn="";
	$process_name="";
	$cgst_tax_rate=0;
		$sgst_tax_rate=0;
		$igst_tax_rate=0;
		$gst_rate = '';
		$gst_value = 0;
		$total_gst = 0;
	while($trn_rel=brp_mysqli_fetch_assoc($trn_qry_rs)){
		$p++;
		$base_sum=$base_sum+$trn_rel['product_base_qty'];
		$amt=$trn_rel['product_base_qty']*$trn_rel['pr_rate'];
		$pro_name=$trn_rel['product_name'].",".$trn_rel['cat_name'];
		$pro_hsn=$pro_hsn.",".$trn_rel['product_hsn'];
		$process_name=$process_name.",".$trn_rel['process_name'];
		$prorate=$prorate+$amt;

		$sale_gst = get_tax_cat_by_hsn($dbcon,$trn_rel['product_hsn']);
		if(($comp_rel['stateid'] == $rel['stateid'])){
			$gst = $sale_gst['tax_gst']/2;
			$cgst_tax_per = $gst;
			$cgst_tax_rate = ($gst*$amt)/100;
			$sgst_tax_per = $gst;
			$sgst_tax_rate = ($gst*$amt)/100;
			$gst_rate .= 'CGST '.$cgst_tax_per.' % '.$cgst_tax_rate.'<br>SGST '.$sgst_tax_per.' % '.$sgst_tax_rate;
		}else{
			$igst_tax_per = $sale_gst['tax_gst'];
			$igst_tax_rate = ($sale_gst['tax_gst']*$amt)/100;
			$gst_rate .= 'IGST '.$igst_tax_per.' % '.$igst_tax_rate;
		}
		$value = $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
		$total_gst = $total_gst + $value;
	}
	$total_qty=$base_sum;
	$html ='<html>
	<head>					
	<title>Delivery Challan</title>
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
		<tr style="border-bottom: none;">
		<td colspan="2" rowspan="3" style="vertical-align: top;width: 50%;">Name and Address of the Supplier: <br><strong>'.$comp_rel['company_name'].'</strong><br>'.nl2br($comp_rel['address']).'<br>'.$comp_rel['state_name'].'<br>GSTIN No. '.$comp_rel['vatno'].'</td>
		<td style="border-right: none;width: 25%;border-bottom: none;"><strong>Serial No.</strong> : '.$rel['job_work_no'].'</td>
		<td style="width: 25%;border-bottom: none;border-left: none;">Date : '.date_format("d-M-Y",$rel['job_work_date']).'</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td style="border-right: none;border-bottom: none;border-top: none;"><strong>PO No.</strong> : '.$rel['job_work_no'].'</td>
		<td style="border-bottom: none;border-top: none;border-left: none;">Date : '.date_format("d-M-Y",$rel['job_work_date']).'</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td style="border-right: none;border-bottom: none;border-top: none;"><strong>State</strong> : '.$comp_rel['state_name'].'</td>
		<td style="border-bottom: none;border-top: none;border-left: none;">State Code : '.$comp_rel['gst_state_code'].'</td>
		</tr>
		<tr style="border-bottom: none;">
		<td colspan="2" style="text-align:top;">Recipient Details: <br>'.$party_address.'</td>
		<td colspan="2" style="text-align:top;">Delivery Details: <br>'.$party_address.'</td>
		</tr>
		<tr style="border-bottom: none;">
		<td colspan="2" style="text-align:top;border-right: none;">1. Description of Goods</td>
		<td colspan="2" style="text-align:top;border-left: none;">: '.trim($pro_name,",").'</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-right: none;">2. HSN Code</td>
		<td colspan="2" style="text-align:top;border-left: none;">: '.trim($pro_hsn,",").'</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-right: none;">3. Quantity (No. /Weight/Meter/Liter)</td>
		<td colspan="2" style="text-align:top;border-left: none;">: '.$total_qty.'</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-right: none;">4. Value</td>
		<td colspan="2" style="text-align:top;border-left: none;">: '.$prorate.'</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-right: none;">5. Tax Rate</td>
		<td colspan="2" style="text-align:top;border-left: none;">: '.$gst_rate.'</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-right: none;">6. Tax Amount</td>
		<td colspan="2" style="text-align:top;border-left: none;">: '.$total_gst.'</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-right: none;">7. Date and Time</td>
		<td colspan="2" style="text-align:top;border-left: none;">: '.date("d-M-Y",strtotime($rel['job_work_date'])).'</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-right: none;">8. Nature of manufacture / processing to be done</td>
		<td colspan="2" style="text-align:top;border-left: none;">: '.trim($process_name,",").'</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-right: none;">9. Factory / place of processing / manufacture</td>
		<td colspan="2" style="text-align:top;border-left: none;">: '.$company_name.'</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-right: none;">10. Expected duration of processing / manufacture</td>
		<td colspan="2" style="text-align:top;border-left: none;">: 365 Days</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-right: none;">11. Remark</td>
		<td colspan="2" style="text-align:top;border-left: none;">: '.(($rel['remark']) ? $rel['remark'] : '').'</td>
		</tr>
		<tr style="border-top: none;border-bottom: none;">
		<td colspan="4" style="text-align:top;border-right: none;border-left: none;">Place : DIST. PUNE</td>
		</tr>
		<tr style="border-top: none;border-bottom: none;">
		<td colspan="4" style="border-right: none;border-left: none;">Date: '.date("d-M-Y",strtotime($rel['cdate'])).'</td>
		</tr>
		<tr style="border-top: none;border-bottom: none;">
		<td colspan="2" style="border-right: none;border-left: none;"></td>
		<td colspan="2" style="vertical-align:bottom;border-left: none;border-right: none;text-align: right;font-weight: bold;">Signature of manufacturer / Authorised Signatory</td>
		</tr>
		<tr style="border-bottom: none;">
		<td colspan="4" style="text-align: center; font-weight: bold;">To be completed by the processor / job-worker at the time of dispatches, back to manufactures.</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-right: none;">1. Entry No. & date of receipt of goods as entered in the account of processing factory.</td>
		<td colspan="2" style="vertical-align:bottom;border-left: none;">: _________________________________________________________</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-right: none;">2. Nature of processing/manufacturing carried out</td>
		<td colspan="2" style="vertical-align:top;border-left: none;">: _________________________________________________________</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-right: none;">3. Quantity (No. /Weight/Meter/Liter) dispatched as entered in the Account after processing/job-working</td>
		<td colspan="2" style="vertical-align:bottom;border-left: none;">: _________________________________________________________</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-right: none;">4. Date of dispatch</td>
		<td colspan="2" style="vertical-align:top;border-left: none;">: _________________________________________________________</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" rowspan="3" style="text-align:top;border-right: none;vertical-align: top;">5. Partculars of goods dispatched from the premises of the processor / job-worker</td>
		<td style="text-align:top;border-right: none;border-left: none;">a) Quantity: __________________</td>
		<td style="text-align:top;border-left: none;">b) Value: ____________________</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td style="text-align:top;border-right: none;border-left: none;">c) Tax rate: __________________</td>
		<td style="text-align:top;border-left: none;">d) Tax Paid/Payable: ___________</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-left: none;">e) Invoice No. & Dt.: _________________________________________</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-right: none;">6. Quantity of waste generated and particulars of it&apos;s disposal</td>
		<td colspan="2" style="vertical-align:top;border-left: none;">: _________________________________________________________</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="4" style="text-align:top;border-right: none;">Place: ____________________________________</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="4" style="text-align:top;border-right: none;">Date: _____________________________________</td>
		</tr>
		<tr style="border-top: none;border-bottom: none;">
		<td colspan="2" style="vertical-align:top;border-right: none;border-bottom: none;">Name & Address of the processer: ______________________________</td>
		<td colspan="2" style="text-align:top;border-left: none;"></td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td colspan="2" style="text-align:top;border-right: none;"></td>
		<td colspan="2" style="text-align:top;border-left: none;"></td>
		</tr>
		<tr style="border-top: none;">
		<td colspan="2" style="vertical-align:bottom;border-right: none;">___________________________________________________________</td>
		<td colspan="2" style="vertical-align:bottom;border-left: none;text-align: right;"><strong>Singnature of Processor/Job Worker</strong></td>
		</tr>
		</tbody></table>
		</div>
		<center class="nextpage"></center>
		<div>
		<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
		<tr>
		<td colspan="4" style="text-align:center;font-size:15px;font-weight:bold;">ANNEXTURE-1</td>
		</tr>
		<tr style="border-bottom: none;">
			<td colspan="2" rowspan="5" style="vertical-align: top;width: 50%;"><strong>To,<br>'.$party_address.'</strong></td>
			<td style="border-right: none;width: 25%;border-bottom: none;"><strong>Challan No.</strong></td>
			<td style="width: 25%;border-bottom: none;border-left: none;">: '.$rel['job_work_no'].'</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td style="border-right: none;border-bottom: none;border-top: none;"><strong>Challan Date.</strong></td>
		<td style="border-bottom: none;border-top: none;border-left: none;">: '.date("d-M-Y",strtotime($rel['job_work_date'])).'</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td style="border-right: none;border-bottom: none;border-top: none;"><strong>Return Date.</strong></td>
		<td style="border-bottom: none;border-top: none;border-left: none;">: '.date("d-M-Y",strtotime($rel['return_date'])).'</td>
		</tr>
		<tr style="border-bottom: none;border-top: none;">
		<td style="border-right: none;border-bottom: none;border-top: none;"><strong>PO No.</strong></td>
		<td style="border-bottom: none;border-top: none;border-left: none;">: '.$rel['job_work_no'].'</td>
		</tr>
		<tr style="border-top: none;border-bottom: none;">
		<td style="border-right: none;"><strong>PO Date.</strong></td>
		<td style="border-left: none;">: '.date("d-M-Y",strtotime($rel['job_work_date'])).'</td>
		</tr>
		</table>

		<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		<thead>
		<tr>
			<th style="width:5%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
		<th style="width:10%;text-align:center;border:1px solid;">Item Code</th>
		<th style="width:45%;text-align:center;border:1px solid;" >Description</th>
		<th style="width:10%;text-align:center;border:1px solid;">HSN Code</th>
		<th style="width:15%;text-align:center;border:1px solid;">Quantity</th>
		<th style="width:15%;text-align:center;border:1px solid;">Quantity</th>
		</tr>
		</thead>
		<tbody>';
		$trn_qry="select job.*, product.product_name, hsn.hsn_code as product_hsn, product.product_icode, process.process_name,baseunit.unit_name as baseunitname,convunit.unit_name as convunitname FROM `tbl_job_work_trn` as job 
		left join product_mst as product on product.product_id=job.product_id 
		left join process_mst as process ON process.process_id = job.process_id 
		left join unit_mst as baseunit on baseunit.unitid=job.product_base_unit
		left join unit_mst as convunit on convunit.unitid=job.product_con_unit
		left join mst_hsn_code as hsn on hsn.hsn_id=product.product_hsn
		where job_work_trn_status=0 and job_work_id=".$rel['job_work_id'];
		$trn_qry_rs=$dbcon->query($trn_qry);
		$p=1;
		$cnt=brp_mysqli_num_rows($trn_qry_rs);
		$base_sum=0;
		$conv_sum=0;
		$proamt = 0;
		while($trn_rel=brp_mysqli_fetch_assoc($trn_qry_rs)){
			$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
			<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			<b>'.nl2br($trn_rel['product_icode']).'</b>
			</td>
			<td style="text-align:left;border:1px solid;vertical-align:top;">
			'.$trn_rel['product_name'].'<br><strong>Nature of Processing: '.$trn_rel['process_name'].'</strong></td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			'.$trn_rel['product_hsn'].'
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			'.$trn_rel['product_base_qty'].' '.$trn_rel['baseunitname'].'
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			'.$trn_rel['product_con_qty'].' '.$trn_rel['convunitname'].'
			</td>
			</tr>';
			$p++;
			$base_sum=$base_sum+$trn_rel['product_base_qty'];
			$conv_sum=$conv_sum+$trn_rel['product_con_qty'];
			$amt = $trn_rel['product_base_qty']*$trn_rel['pr_rate'];
			$proamt+=$amt;
		}
		$pr=10-$cnt;
		for($j=0; $j<$pr; $j++)
		{
			$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
			<td style="border:none;border-left:1px solid;border-right:1px solid;height:40px;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			</tr>';
		}
		$html.='<tr style="border-bottom:none;border-left:1px solid;border-right:1px solid;">
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"><strong>Material Value: Rs. '.$proamt.'</strong></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;text-align: right;"><strong>Total :</strong></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;text-align: center;"><strong>'.$base_sum.'</strong></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;text-align: right;"><strong>'.$conv_sum.'</strong></td>
		</tr>';
		$html.='<tr style="border-bottom: none;">
		<td colspan="6" style="text-align:top;">Remarks:
		'.(($rel['remark']) ? nl2br($rel['remark']) : '').'
		</td>
		</tr></tbody></table>';

		$coordinator_qry ="SELECT l_name,cust_mobile FROM `tbl_ledger` WHERE l_status=0 and l_id=".$rel['vender_id'];
		$coordinator = brp_mysqli_fetch_assoc($dbcon->query($coordinator_qry));
		$user_qry ="select * from users where user_id=".$_SESSION['user_id'];
		$user = brp_mysqli_fetch_assoc($dbcon->query($user_qry));

		$html.='<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3"><tbody>
		<tr style="border-bottom: none">
		<td width="15%" style="border-bottom: none;border-right: none;border-left: none;"><strong>Mode of Transport</strong></td>
		<td width="15%" style="border-bottom: none;border-right: none;border-left: none;white-space: nowrap;">: '.$rel['mode_of_transport'].'</td>
		<td width="15%" style="border-bottom: none;border-right: none;border-left: none;"><strong>Freight</strong></td>
		<td width="15%" style="border-bottom: none;border-right: none;border-left: none;white-space: nowrap;">: '.$rel['freight'].'</td>
		<td width="15%" style="border-bottom: none;border-right: none;border-left: none;"><strong>LR No.</strong></td>
		<td width="15%" style="border-bottom: none;border-left: none;white-space: nowrap;">: '.$rel['l_r_no'].'</td>
		</tr>
		<tr style="border-top: none">
		<td style="border-top: none;border-right: none;border-left: none;"><strong>Vehicle No.</strong></td>
		<td style="border-top: none;border-right: none;border-left: none;white-space: nowrap;">: '.$rel['vehicle_no'].'</td>
		<td style="border-top: none;border-right: none;border-left: none;"><strong>Transporter</strong></td>
		<td style="border-top: none;border-right: none;border-left: none;white-space: nowrap;">: '.$rel['transporter'].'</td>
		<td style="border-top: none;border-right: none;border-left: none;"><strong>Prepared By</strong></td>
		<td style="border-top: none;border-left: none;white-space: nowrap;">: '.$user['user_name'].'</td>
		</tr>
		<tr>
			<td style="vertical-align: bottom;border-top: none;height: 60px;" colspan="6">Receiver&apos;s Sign with office stamp<br>Received the above material in good condition</td>
		</tr>
		<tr>
			<td style="vertical-align: top;border-top: none;height: 60px;border-right: none;" colspan="4" rowspan="2">1) Certified that particulars given above are correct.<br>2) Any Discrepancy should br reported immediately with full particulars within seven days after that no claim will be returned.</td>
			<td colspan="2" height="80px;" style="vertical-align: top;border-bottom: none;"><strong>For, ELCON CABLE TRAYS PVT. LTD.</strong></td>
		</tr>
		<tr style="border-top: none;">
			<td colspan="2" style="border-top: none;text-align: center;"><strong>Authorized Signatory</strong></td>
		</tr>
		</tbody>
		</table>
		</div>';

		$coordinator_qry ="SELECT l_name,cust_mobile FROM `tbl_ledger` WHERE l_status=0 and l_id=".$rel['coordinator_id'];
		$coordinator = mysqli_fetch_assoc($dbcon->query($coordinator_qry));
		$user_qry ="select * from users where user_id=".$_SESSION['user_id'];
		$user = mysqli_fetch_assoc($dbcon->query($user_qry));

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
		return 'job_work_print'.$quotation_id.'.pdf';
	}

	?>
