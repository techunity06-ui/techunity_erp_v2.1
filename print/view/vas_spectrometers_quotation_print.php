<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");

$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
            QUOTATION_SLUG_PRINT,
        ]);
        
if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}
        
$quotation_id = $_REQUEST['id'];	
$type='pdf';
if(strtolower($type) == 'pdf') {
//Quotation Data
$query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name from tbl_quotation as quot
left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
left join tbl_customer as cust on cust.cust_id=quot.cust_id
left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
where quot.quotation_id=".$quotation_id;
$rel=mysqli_fetch_assoc($dbcon->query($query));

if(!$rel){
    header("Location: ".ROOT.CRM_ROOT."quotation_list");
}

$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';
//Company Data
/*$comp_qry="select * from tbl_company as comp 
where comp.company_id=".$rel['company_id'];
*/
$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
		
$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
$header ='<div style="text-align:right;"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:3.27in;padding-top:25px;" /></div>';
//$header =$comp_rel["logo"];
//$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';

$approve_status='';
if($rel['approve_status']=='0'){
	$approve_status=' (DRAFT)';
}

//Amish Soni Start 16-03-2021
$companySettings = getCompanySettings($dbcon);
$quotation_print_content = '<p>True Wisdom is In Finding Solutions. Our Next-Gen ERP software helps in defining a correct business solution as it is designed for overcoming real-time business challenges.</p>
				<br />
				<p>We thrive to bring your <strong>“FACTORY ON SCREEN”</strong> by understanding minute details and processes.</p>
				<br />
				<h3>Quick Introduction:</h3>
				<p>BRP Software Solutions LLP is an extended version of our previous startup, metR Technologies. We have catered 5000+ IT projects and are now growing with our latest ERP solution.</p>
				<br />
				<p>We are a growing ERP Software Company for providing 360-degree business solutions.</p>
				<br />
				<h3>Big DataSuite with:</h3>
				<p>Business Forecasting</p>
				<p>Visitor Management System</p>
				<p>CRM & Service Management</p>
				<p>Purchase Management</p>
				<p>Engineering & Design (BOM)</p>
				<p>Project Planning & Inventory Management</p>
				<p>Production</p>
				<p>Quality management</p>
				<p>Sales</p>
				<p>Financial Management</p>
				<p>Business Intelligence</p>
				<p>HR & Payroll management</p>
				<p>& many other services for managing onground factory operations with ease.</p>
				<br />
				<p>Engineering, Manufacturing and Consumer Industries can take maximum advantage by utilizing our 360 degree ERP solutions.</p>
				<br />
				<p>Well, if you are interested for a free demo- we are just a click away.</p>
				<br />
				<p>Let\'s bring your business operations on screen and help you ease with tedious operational tasks.</p>';

if($companySettings) {
	$quotation_print_content = $companySettings['quotation_print_content'] ? $companySettings['quotation_print_content'] : $quotation_print_content;
	$quotation_print_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_print_content);
}
//Amish Soni End 16-03-2021

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
<htmlpageheader name="otherpages" style="display:none">
    <div style="text-align:center">'.$header.'</div>
</htmlpageheader>
<!--<htmlpagefooter name="otherpages_footer" style="display:none">
    <div style="text-align:center">'.$footer.'</div>
</htmlpagefooter>-->
<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
<div>
	<table cellpadding="5" cellspacing="5" border="0" style="font-size: 20px; font-family: Proxia Nova; border: 0;">
		<tr style="border: 0;">
			<td style="border: 0; text-align: left; font-size: 15px;">Quotation No. : '.$rel['quotation_no'].'</td>
			<td style="border: 0; text-align: right; font-size: 15px;">Date: '.date("d-M-Y",strtotime($rel['quotation_date'])).'</td>
		</tr>
	</table>
	<table cellpadding="5" cellspacing="5" border="0" style="font-size: 12px; border: 0;">
		<tr style="border: 0;">
			<td colspan="2" style="border: 0;text-align: center;text-decoration: underline; font-weight: bold; font-size: 16px;">QUOTATION FOR '.$rel['quot_subject'].'</td>
		</tr>
		<tr style="border: 0;">
			<td colspan="2" style="border: 0;">
				<strong>Mr/Ms. '.$rel['cust_name'].',</strong><br/>
				<strong>'.$rel['c_con_fname'].' '.$rel['c_con_lname'].',<br/>
					'.($quot_address).'</strong>
			</td>
		</tr>
		<tr style="border: 0;">
			<td colspan="2" style="border: 0;">
				Ref: 
				</td>
		</tr>
		<tr style="border: 0; border-top: 1px solid;">
			<td colspan="2" style="border: 0;">
				Kind Attn. : '.$userData['user_name'].'<br>
				'.$userPhone .''. $userEmail.'
			</td>
		</tr>
		<tr style="border: 0;">
			<td colspan="2" style="border: 0;">
				'.$quotation_print_content.'
			</td>
		</tr>
	</table>';
$trn_qry="select trn.*, pro.product_name, unit.unit_name, cat.cat_name, variant.variant_name, base.base_name from tbl_quotation_trn as trn 
left join product_mst as pro on pro.product_id=trn.product_id
left join tbl_category as cat on cat.cat_id=trn.cat_id
left join tbl_base_mst as base on base.base_id=trn.base_id
left join tbl_variant_mst as variant on variant.variant_id=trn.variant_id
left join unit_mst as unit on unit.unitid=trn.unitid
where trn.quot_trn_status=0 and trn.type_flag=0 and trn.quotation_id=".$rel['quotation_id'];
$trn_qry_rs=$dbcon->query($trn_qry);
$p=1;$ttl_amt=0;$ttl_qty=0;$tax_amount=0;
$cnt=mysqli_num_rows($trn_qry_rs);
if($cnt>0){
	$html.='<table style="font-size:12px;border-collapse: collapse;width:100%; margin-top: 10px;" cellpadding="5" cellspacing="5">
		<tr>
			<td colspan="6" style="font-size:15px; font-weight:bold; border: 0; background-color: #284c79; color: #ffffff;">Main Product (A)
			</td>
		</tr>
		<tr>
			<th style="width:2%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
			<th style="width:50%;text-align:center;border:1px solid;">Item Description</th>
			<th style="width:8%;text-align:center;border:1px solid;">Qty</th>
			<th style="width:10%;text-align:center;border:1px solid;">Unit Price</th>
			<th style="width:10%;text-align:center;border:1px solid;">Discount</th>
			<th style="width:15%;text-align:center;border:1px solid;">Total Price</th>
		</tr>
	</thead>
	<tbody>';
while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
	$chkProgramSql="SELECT program_name as pname FROM tbl_program_mst WHERE program_id IN (".$trn_rel['program_id'].")";
	$chkProgram=$dbcon->query($chkProgramSql);
	$tax_amount=$trn_rel['tax_amount1']+$trn_rel['tax_amount2']+$trn_rel['tax_amount3'];
	$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';

	$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
		<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
		<td style="text-align:left;border:1px solid;vertical-align:top;">
			'.$trn_rel['cat_name'].'<br>
			Model: <strong>'.$trn_rel['product_name'].'</strong><br/>
			Variant: '.$trn_rel['variant_name'].'<br>
			'.$trn_rel['base_name'].' : ';
	
			$cps=1;
			while($getProgram = brp_mysqli_fetch_assoc($chkProgram)){
				$html.=$cps.') '.$getProgram['pname'].'<br>&nbsp;&nbsp;&nbsp;&nbsp;';
				$cps++;
			}
			$html.='<br>'.$product_desc.'<br>';
			$chkImage=$dbcon->query("SELECT im_name FROM tbl_product_images WHERE im_product='".$trn_rel['product_id']."'");
			while($getImage=mysqli_fetch_assoc($chkImage)){
			if(!empty($getImage['im_name'])){
				$html.='<img src="'.ROOT.PRO_IMG_VWING.$getImage['im_name'].'" alt="'.$trn_rel['product_name'].'" style="height: 100px; width: 100px;" />';
			}
		}
		$html.='</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">
			'.$trn_rel['product_qty'].'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">
		'.$trn_rel['product_rate'].'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">
		'.$trn_rel['product_discount'].'
		</td>';
		$html.='<td style="text-align:center;border:1px solid;vertical-align:top;">';
		if($trn_rel['product_rate']==$trn_rel['product_discount']){
			$html.="Included";
		}else{
			$html.=$trn_rel['product_total'];
		}
		$html.='</td>
	</tr>';
	$ttl_qty=$ttl_qty+$trn_rel['product_discount'];
	if($trn_rel['act_amt_flag']!='1'){
		$ttl_amt=$ttl_amt+$trn_rel['product_total'];
	}
	
	$p++;
}

	$html.='<tr>
		<td colspan="5" style="font-weight: bold; text-align:left;border:1px solid;">Total Discount</td>
		<td style="border: 1px solid; font-weight: bold; text-align: right;">'.number_format($ttl_qty,2,".","").'</td>
	</tr>
	<tr>
		<td colspan="5" style="font-weight: bold; text-align:left;border:1px solid;">Total of Product A</td>
		<td style="border: 1px solid; font-weight: bold; text-align: right;">'.number_format($ttl_amt,2,".","").'</td>
	</tr>
	</tbody>
	</table>
	</div>';
}

$trn_qrys="select trn.*, pro.product_name, unit.unit_name, cat.cat_name, variant.variant_name, base.base_name from tbl_quotation_trn as trn 
left join product_mst as pro on pro.product_id=trn.product_id
left join tbl_category as cat on cat.cat_id=trn.cat_id
left join tbl_base_mst as base on base.base_id=trn.base_id
left join tbl_variant_mst as variant on variant.variant_id=trn.variant_id
left join unit_mst as unit on unit.unitid=trn.unitid
where trn.quot_trn_status=0 and trn.type_flag=1 and trn.quotation_id=".$rel['quotation_id'];
$trn_qry_rss=$dbcon->query($trn_qrys);
$ps=1;$ttl_amts=0;$ttl_qtys=0;$tax_amounts=0;
$cnts=mysqli_num_rows($trn_qry_rss);
if($cnts>0){
	$html.='<center class="nextpage"></center>
	<div>
	<table style="font-size:12px;border-collapse: collapse;width:100%; margin-top: 10px;" cellpadding="5" cellspacing="5">
		<tr>
			<td colspan="6" style="font-size:15px; font-weight:bold; border: 0; background-color: #284c79; color: #ffffff;">Add-On Product (B)
			</td>
		</tr>
		<tr>
			<th style="width:2%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
			<th style="width:50%;text-align:center;border:1px solid;">Item Description</th>
			<th style="width:8%;text-align:center;border:1px solid;">Qty</th>
			<th style="width:10%;text-align:center;border:1px solid;">Unit Price</th>
			<th style="width:10%;text-align:center;border:1px solid;">Discount</th>
			<th style="width:15%;text-align:center;border:1px solid;">Total Price</th>
		</tr>
	</thead>
	<tbody>';
while($trn_rels=mysqli_fetch_assoc($trn_qry_rss)){
	$product_desc = ($trn_rels['product_desc']) ? nl2br($trn_rels['product_desc']) : '';
	$tax_amounts=$trn_relS['tax_amount1']+$trn_relS['tax_amount2']+$trn_relS['tax_amount3'];

	$chkProgramSql="SELECT program_name as pname FROM tbl_program_mst WHERE program_id IN (".$trn_rels['program_id'].")";
	$chkProgram=$dbcon->query($chkProgramSql);
	$cp=1;

	$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
		<td style="text-align:center;border:1px solid;vertical-align:top;">'.$ps.'</td>
		<td style="text-align:left;border:1px solid;vertical-align:top;">
			'.$trn_rels['cat_name'].'<br>
			Model: <strong>'.$trn_rels['product_name'].'</strong><br/>
			Variant: '.$trn_rels['variant_name'].'<br>
			'.$trn_rels['base_name'].' : ';
	
			while($getProgram = brp_mysqli_fetch_assoc($chkProgram)){
				$html.=$cp.') '.$getProgram['pname'].'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				$cp++;
			}
			$html.='<br>'.$product_desc.'<br>';
			$chkImage=$dbcon->query("SELECT im_name FROM tbl_product_images WHERE im_product='".$trn_rels['product_id']."'");
			while($getImage=mysqli_fetch_assoc($chkImage)){
			if(!empty($getImage['im_name'])){
				$html.='<img src="'.ROOT.PRO_IMG_VWING.$getImage['im_name'].'" alt="'.$trn_rels['product_name'].'" style="height: 100px; width: 100px;" />';
			}
		}
		$html.='</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">
			'.$trn_rels['product_qty'].'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">
		'.$trn_rels['product_rate'].'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">
		'.$trn_rels['product_discount'].'
		</td>';
		$html.='<td style="text-align:center;border:1px solid;vertical-align:top;">';
		if($trn_rels['product_rate']==$trn_rels['product_discount']){
			$html.="Included";
		}else{
			$html.=$trn_rels['product_total'];
		}	
		$html.='</td>
	</tr>';
	$ttl_qtys=$ttl_qtys+$trn_rels['product_discount'];
	if($trn_rels['act_amt_flag']!='1'){
		$ttl_amts=$ttl_amts+$trn_rels['product_total'];
	}
	
	$ps++;
}

	$html.='<tr>
		<td colspan="5" style="font-weight: bold; text-align:left;border:1px solid;">Total Discount on Add-On Product (B)</td>
		<td style="border: 1px solid; font-weight: bold; text-align: right;">'.number_format($ttl_qtys,2,".","").'</td>
	</tr>
	<tr>
		<td colspan="5" style="font-weight: bold; text-align:left;border:1px solid;">Total Value of Add-On Product (B)</td>
		<td style="border: 1px solid; font-weight: bold; text-align: right;">'.number_format($ttl_amts,2,".","").'</td>
	</tr>
	</tbody>
	</table>
	</div>';
}
	$html.='<table style="font-size:14px;border-collapse: collapse;width:100%; margin-top: 10px; margin-left: 50%;" cellpadding="5" cellspacing="5">
		<tr>
			<td width="50%" style="font-weight: bold; border-top: 1px solid; border-bottom: 1px solid; background-color: #284c79; color: #ffffff;">Total Discount (A + B)</td>
			<td width="50%" style="font-weight: bold; border-top: 1px solid; border-bottom: 1px solid; border-right: 1px solid; text-align: right">'.number_format($ttl_qty+$ttl_qtys,2,".","").'</td>
		</tr>
		<tr>
			<td style="font-weight: bold; border-top: 1px solid; border-bottom: 1px solid; background-color: #284c79; color: #ffffff;">Total Order Value</td>
			<td style="font-weight: bold; border-top: 1px solid; border-bottom: 1px solid; border-right: 1px solid; text-align: right">'.number_format($ttl_amts+$ttl_amt,2,".","").'</td>
		</tr>
		<tr>
			<td style="font-weight: bold; border-top: 1px solid; border-bottom: 1px solid; background-color: #284c79; color: #ffffff;">Total GST</td>
			<td style="font-weight: bold; border-top: 1px solid; border-bottom: 1px solid; border-right: 1px solid; text-align: right">'.number_format($tax_amounts+$tax_amount,2,".","").'</td>
		</tr>
		<tr>
			<td style="font-weight: bold; border-top: 1px solid; border-bottom: 1px solid; background-color: #284c79; color: #ffffff;">Grand Total</td>
			<td style="font-weight: bold; border-top: 1px solid; border-bottom: 1px solid; border-right: 1px solid; text-align: right">'.number_format($ttl_amts+$ttl_amt,2,".","").'</td>
		</tr>
	</table>
	<div style="clear:both;"></div>
</div>
<!--page1 end-->';

$base_qry="select trn.*, pro.product_name, base.base_name from tbl_quotation_base_trn as trn 
left join product_mst as pro on pro.product_id=trn.product_id
left join tbl_base_mst as base on base.base_id=trn.base_id
where trn.status=0 and trn.quotation_id=".$rel['quotation_id'];
$base_qry_rs=$dbcon->query($base_qry);
if(mysqli_num_rows($base_qry_rs)){
$html.='<center class="nextpage"></center>
<div>
<table cellpadding="5" cellspacing="5" border="0" style="font-size: 20px; font-family: Proxia Nova; border: 0;">
		<tr style="border: 0;">
			<td style="border: 0; text-align: left; font-size: 15px;">Quotation No. : '.$rel['quotation_no'].'</td>
			<td style="border: 0; text-align: right; font-size: 15px;">Date: '.date("d-M-Y",strtotime($rel['quotation_date'])).'</td>
		</tr>
	</table>
	<table width="100%" style="font-size:14px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3">
		<tbody>
			<tr style="border: 0;">
				<td colspan="2" style="font-size: 16px; font-weight: bold; border: 0; text-decoration: underline; text-align: center;">Standard Factory Calibration Analytical Range (SFCAR)</td>
			</tr>';
	while($base_rel=mysqli_fetch_assoc($base_qry_rs)){
	    $string=(nl2br($base_rel['product_other_desc']));

		$html.='<tr style="border: 0;">
			<td style="border: 0;font-weight: bold;">Model : '.$base_rel['product_name'].'</td>
			<td style="border: 0;font-weight: bold; text-align:right;">Base: '.$base_rel['base_name'].'</td>
		</tr>
		<tr style="border: 0;">
			<td style="border: 0;" colspan="2">'.$string.'</td>
		</tr>';
	}
$html.='</tbody>
</table>
</div>';	
}

/* Get Terms And Condition Start */
$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
$terms_qry_rs=$dbcon->query($terms_qry);
$html.='<center class="nextpage"></center>
<div>
<table cellpadding="5" cellspacing="5" border="0" style="font-size: 20px; font-family: Proxia Nova; border: 0;">
		<tr style="border: 0;">
			<td style="border: 0; text-align: left; font-size: 15px;">Quotation No. : '.$rel['quotation_no'].'</td>
			<td style="border: 0; text-align: right; font-size: 15px;">Date: '.date("d-M-Y",strtotime($rel['quotation_date'])).'</td>
		</tr>
	</table>
	<table width="100%" style="font-size:12px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3">
	<tbody>';
if(mysqli_num_rows($terms_qry_rs)){
	$html.='<tr style="border: 0;">
	<td colspan="2" style="font-size: 16px; font-weight: bold; border: 0;">Terms & Conditions</td>
	</tr>';
	$t=1;
	while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
	    $string=(nl2br($term_rel['tc_details']));

		$html.='<tr style="border: 0;">
		<td style="border: 0;font-weight: bold;">'.$t.') '.$term_rel['tc_name'].'</td>
		<td style="border: 0;text-align:left;">: '.$string.'</td>
		</tr>';
		$t++;
	}
}
/* Get Terms And Condition End */
/* Check Annexure Attachments Start */
if(trim($rel['quot_annex_content'])){
	$html.='<tr style="border: 0;">
	<td colspan="2" style="border: 0;">'.$rel['quot_annex_content'].'</td>
	</tr>';
}
/* Check Annexure Attachments End */
$html.='</tbody>
</table>
</div>
<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
</body>
</html>';
// echo $html;exit;
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','proximanova','10','10','35','10','1','1');
//		$mdf->SetFont('ProximaNova');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		//$mpdf->SetHTMLFooter($footer);
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
		// $mpdf->Output('../../view/upload/quotation_pdf/Quotation_'.$quotation_id.'.pdf','f');
		ob_clean();
		return 'Quotation_'.$quotation_id.'.pdf';
	}
	
?>
		