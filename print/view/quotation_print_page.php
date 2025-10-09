<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once("../../crm/include/crm_common_functions.php");
include_once(COMMON_FUNCTION_PATH."common_production_functions.php");
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	QUOTATION_SLUG_PRINT,
]);

if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}

	$quotation_id = $_GET['id'];	
$type='pdf';
if(strtolower($type) == 'pdf') {
//Quotation Data
	$query="select * from tbl_quotation where quotation_id=".$quotation_id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));
 
	$number_sr = $rel['quotation_no'];
	$r2 = $rel['quotation_date'];
	$r3 = $rel['cust_id'];

		$cust = "select * from tbl_customer where cust_id=".$r3;
		$rscust = mysqli_fetch_assoc($dbcon->query($cust));
		$rname = $rscust['cust_name'];
	$r4 = $rel['quot_address'];
	$r5 = $rel['quot_subject'];
	$r6 = $rel['quot_header'];
	$r7 = $rel['quot_footer'];
	$r8 = $rel['qt_com_mno'];
	$r8id = $rel['c_con_id'];
	$r9 = $rel['user_id'];
	$usenad_tb = "select * from users where user_id=".$r9;
	$rsm_name_user = mysqli_fetch_assoc($dbcon->query($usenad_tb));;
	$r10 = $rsm_name_user['user_name'];
	$r21 = $rsm_name_user['user_phone'];
	$r22 = $rsm_name_user['user_mail'];
	$r11 = $rel['company_id'];
	$company_rel_id_sql = "select * from tbl_company where company_id=".$r11;
	$r12 = mysqli_fetch_assoc($dbcon->query($company_rel_id_sql));
	$r13 = $r12['company_name'];
	$r14 = $r12['logo'];
	$r141 = $r12['f_logo'];
	$r15 = $rel['quot_annex_content'];

	$qu_id_nu_product = $rel['quotation_id'];

	$R1211 = "select * from tbl_cust_contact where c_con_id=".$r8id;
	$rname_cust_kind = mysqli_fetch_assoc($dbcon->query($R1211));

$proditels = "select * from tbl_quotation_trn where quotation_id=".$qu_id_nu_product;
$pro_rel = mysqli_fetch_assoc($dbcon->query($proditels));

			$smidpro = $pro_rel['product_id'];
			$product_today = "select * from product_mst where product_id=".$smidpro;
			$product_today_ditels = mysqli_fetch_assoc($dbcon->query($product_today));
				
				$power_unit_number = $product_today_ditels['product_base_unit'];
			$unitsyspower = "select * from unit_mst where unitid=".$power_unit_number;
			$unit_name_query = mysqli_fetch_assoc($dbcon->query($unitsyspower));

			$trm_and_cond = "select * from tbl_terms_condition";
			$trandCondition = mysqli_fetch_assoc($dbcon->query($trm_and_cond));


$header ='<div style="text-align:center;"><img src="'.DOMAIN_F.LOGO.$r14.'" style="width:100%;height:150px" /></div>';
//$header =$comp_rel["logo"];
$footer='<img src="'.DOMAIN_F.LOGO.$r141.'" style="padding-left:0px !important;width:100%"/>';

$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
$userData = mysqli_fetch_assoc($dbcon->query($userQuery));
$userPhone = $userData['user_phone'] ? '<strong>Mo.:</strong> '.$userData['user_phone'] : '';
$userEmail = $userData['user_mail'] ? '<strong>Email:</strong> '.$userData['user_mail'] : '';

$approve_status='';
if($rel['approve_status']=='0'){
	$approve_status=' (DRAFT)';
}

$date = date('d-m-Y', strtotime($r2));	
//Amish Soni Start 16-03-2021

	$total_main_amunt = $pro_rel['cgst_tax_rate'] + $pro_rel['sgst_tax_rate'] + $pro_rel['product_amount'];

	$number = $total_main_amunt;
   $no = floor($number);
   $point = round($number - $no, 2) * 100;
   $hundred = null;
   $digits_1 = strlen($no);
   $i = 0;
   $str = array();
   $words = array('0' => '', '1' => 'one', '2' => 'two',
    '3' => 'three', '4' => 'four', '5' => 'five', '6' => 'six',
    '7' => 'seven', '8' => 'eight', '9' => 'nine',
    '10' => 'ten', '11' => 'eleven', '12' => 'twelve',
    '13' => 'thirteen', '14' => 'fourteen',
    '15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
    '18' => 'eighteen', '19' =>'nineteen', '20' => 'twenty',
    '30' => 'thirty', '40' => 'forty', '50' => 'fifty',
    '60' => 'sixty', '70' => 'seventy',
    '80' => 'eighty', '90' => 'ninety');
   $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
   while ($i < $digits_1) {
     $divider = ($i == 2) ? 10 : 100;
     $number = floor($no % $divider);
     $no = floor($no / $divider);
     $i += ($divider == 10) ? 1 : 2;
     if ($number) {
        $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
        $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
        $str [] = ($number < 21) ? $words[$number] .
            " " . $digits[$counter] . $plural . " " . $hundred
            :
            $words[floor($number / 10) * 10]
            . " " . $words[$number % 10] . " "
            . $digits[$counter] . $plural . " " . $hundred;
     } else $str[] = null;
  }
  $str = array_reverse($str);
  $result = implode('', $str);
  $points = ($point) ?
    "." . $words[$point / 10] . " " . 
          $words[$point = $point % 10] : '';

          $wordnumber = $result . "Rupees ";


$quotation_print_content = $rel['quot_header'] ? $rel['quot_header'] : $companySettings['quotation_print_content'];
$quotation_footer_content = $rel['quot_footer'] ? $rel['quot_footer'] : $companySettings['quotation_footer_content'];
$quotation_print_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_print_content);
$quotation_footer_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_footer_content);
//Amish Soni End 16-03-2021
$quot_annex_content=($rel['quot_annex_content']) ? $rel['quot_annex_content'] : '';

$trnsql=$dbcon->query("select SUM(product_discount) as total_discount from tbl_quotation_trn where quot_trn_status=0 and quotation_id=".$rel['quotation_id']);
$getsql=mysqli_fetch_assoc($trnsql);
$i = 0;
		
					if($rel['qt_po_attch'] != null){
					 $imgtable ='<img src="../../view/upload/qc_attach_doc/'.$rel['qt_po_attch'].'" style="height:330px;width:100%;">';
					 	$name_title_header = $product_today_ditels['product_name'];

					}
					else{

						$imgtable = '';
						$name_title_header = '';
					}


$html ='<html>
<head>					
<title>'.$rel['quotation_no'].'</title>
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
		// border:1px solid #000;
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

			
		<table style="overflow-x:auto;">
			<tr>
				<td></td>
				<td><center><h2>Quotation:</h2></center></td>
				<td></td>
			</tr>
		</table>
		<table style="overflow-x:auto;">
			<tr>
				<td></td>
				<td><center><h5>'.$name_title_header.'</h5></center></td>
				<td></td>
			</tr>
		</table>

		</br>

		<table style="overflow-x:auto;">
			<tr>
				<td>
					'.$imgtable.'
				</td>
			</tr>
		</table>		
		
		<table style="overflow-x:auto;">
		<tr>
			<td>
				<p style="float:left;"><b>REF NO : </b>'.$number_sr.'</p>
			</td>
			<td width="15%"></td>
			<td width="15%"></td>
			<td width="15%"></td>
			<td>
						<p style="float:right !important;"><b> DATE :</b>'.$date.'</p>
			</td>
		</tr>
		</table>
		<br/>


		<table style="overflow-x:auto;text-align:left;">
			<tr>
				<td width="20%"><b>Customer Name</b></td>
				<td width="2%">:</td>
				<td>'.$rname.'</td>
			</tr>
		</table>

		<table style="overflow-x:auto;">
			<tr>
				<td width="20%"><b>Address</b></td>
				<td width="2%">:</td>
				<td>'.$r4.'</td>
			</tr>
		</table>
							
		
		<table style="overflow-x:auto;">
			<tr>
				<td width="20%"><b>Kind Attn.</b></td>
				<td width="2%">:</td>
				<td>'.$rname_cust_kind['c_con_fname'].''.$rname_cust_kind['c_con_lname'].'</td>
			</tr>
		</table>
							
		<table style="overflow-x:auto;">
			<tr>
				<td width="20%"><b>Contact No</b></td>
				<td width="2%">:</td>
				<td>'.$rscust['cust_mobile'].'</td>
			</tr>
		</table>

		<table style="overflow-x:auto;">
			<tr>
				<td width="20%"><b>Email</b></td>
				<td width="2%">:</td>
			    <td>'.$rscust['cust_email'].'</td>
			</tr>
		</table>
					<br>	

		<table style="overflow-x:auto;">
			<tr>
				<td style="border-bottom:2px solid #000;width:100%;"><h3>Content</h3></td>
			</tr>
		</table>
	
		<table style="overflow-x:auto;">
			<tr>
				<td>'.$rel['quot_header'].'</td>
			</tr>
		</table>


		<p>&nbsp;</p>

<pagebreak page-break-type="clonebycss" />

	<br/>	
		<h3>Scope Of Supply | Price Schedule</h3>
		<table style="overflow-x:auto;">
			<tr style="border:1px solid #000;">
				<th  style="border-right:1px solid #000;padding:3px;">Sr.No</th>
				<th  style="border-right:1px solid #000;padding:3px;" width="25%">Scope of Supply And Scope of Work</th>
				<th  style="border-right:1px solid #000;padding:3px;">HSN/SAC</th>
				<th  style="border-right:1px solid #000;padding:3px;">Unit</th>
				<th  style="border-right:1px solid #000;padding:3px;">Qty</th>
				<th  style="border-right:1px solid #000;padding:3px;">Rate</th>
				<th  style="border-right:1px solid #000;padding:3px;"s>Amount</th>
			</tr>
				<tr style="border:1px solid #000;">
				<td style="border-right:1px solid #000;"><center>'.++$i.'</center></td>
				<td style="border-right:1px solid #000;padding:7px;">
					<h4>'.$product_today_ditels['product_name'].'</h4>
					<p>'.$product_today_ditels['product_spec'].'</p>
				</td>
				<td style="border-right:1px solid #000;">
					<center><p>'.$product_today_ditels['product_hsn'].'</p></center>
				</td>
				<td style="border-right:1px solid #000;">
					<center><p>'.$unit_name_query['unit_name'].'</p></center>
				</td>
				<td style="border-right:1px solid #000;">
					<center><p>'.$pro_rel['product_qty'].'</p></center>
				</td>
				<td style="border-right:1px solid #000;">
					<center><p>'.$pro_rel['product_rate'].'</p></center>
				</td>
				<td style="border-right:1px solid #000;">
					<center><p>'.$pro_rel['product_amount'].'</p></center>
				</td>
			</tr>
			<tr>
				<td colspan="5" style="border:1px solid #000;"></td>
				<td colspan="2" style="border:1px solid #000;">
				<p><b>Basic Amount :</b>'.$pro_rel['product_amount'].'</p><br>
				<p><b>CGST @' .$pro_rel['cgst_tax_per'].'% : </b>'.$pro_rel['cgst_tax_rate'].'</p><br>
				<p><b>SGST @' .$pro_rel['sgst_tax_per'].'% : </b>'.$pro_rel['sgst_tax_rate'].'</p><br>
				</td>
			</tr>
			<tr>
				<td colspan="5" style="border:1px solid #000;">
				<p style="text-transform: uppercase;"><b>AMOUNT IN WORDS : '.$wordnumber.'</b></p>
				</td>
				<td colspan="2" style="border:1px solid #000;">
				<p><b>Grand Total :</b> '.$total_main_amunt.'</p>
				</td>
			</tr>
			
		</table>


<pagebreak page-break-type="clonebycss" />
	<p>'.$rel['quot_annex_content'].'</p>
	<pagebreak page-break-type="clonebycss" />
		<h3>Terms & Conditions</h3>
	

			<table style="overflow-x:auto;">
				<tr>
					<td width="20%">
						<b>'.$trandCondition['tc_name'].'</b>
					</td>
					<td>: '.$trandCondition['tc_details'].'
</td>
					
				</tr>
			</table>

		<h3>BANK DETAILS / GST DETAILS</h3>
		<table>
			<tr>
				<td><b>GST No</b></td>
				<td>:</td>
				<td>'.$r12['vatno'].'</td>
			</tr>
			<tr>
				<td><b>PAN No</b></td>
				<td>:</td>
				<td>'.$r12['pan_no'].'</td>
			</tr>
			<tr>
				<td><b>Account Name</b></td>
				<td>:</td>
				<td>'.$r12['company_name'].'</td>
			</tr>
			<tr>
				<td><b>CompanyAddress</b></td>
				<td>:</td>
				<td>'.$r12['address'].'</td>

			</tr>
			<tr>
				<td><b>Bank Name</b></td>
				<td>:</td>
				<td>'.$r12['bank_name'].'</td>
			</tr>
			<tr>
				<td><b>Account No</b></td>
				<td>:</td>
				<td>'.$r12['ac_no'].'</td>
			</tr>
			<tr>
				<td><b>Branch</b></td>
				<td>:</td>
				<td>'.$r12['address'].'</td>
			</tr>
			<tr>
				<td><b>RTGS/NEFT IFSC Code</b></td>
				<td>:</td>
				<td>'.$r12['ifcs'].'</td>
			</tr>
		</table>

		<br>
		<table>
			<tr>
				<td><h4>Your Contact point with '.$r13.'</h4></td>
			</tr>
			<tr>
				<td><p>'.$r10.' | '.$r21.' | '.$r22.'</p></td>
			</tr>

		</table>

	</body>
</html>';
//echo $html;exit;
ob_end_clean();
include("../../view/export/mpdf/mpdf.php");
$html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
$mpdf=new mPDF('','A4','0','proximanova','10','10','45','30','1','1');
//		$mdf->SetFont('ProximaNova');
$mpdf->defaultheaderfontsize = 10; /* in pts */
$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
$mpdf->defaultfooterfontsize = 10; /* in pts */
$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLFooter($footer);
$mpdf->SetWatermarkText();
$mpdf->showWatermarkText = true;
$mpdf->allow_charset_conversion=true;
$mpdf->charset_in='UTF-8';
$mpdf->WriteHTML($html);

$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
ob_clean();
return 'quotation'.$quotation_id.'.pdf';
}

?>