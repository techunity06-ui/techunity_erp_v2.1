<?php 
//error_reporting(E_ALL); 
session_start();
$AJAX = true;
include("../../config/config.php");
$id=$dbcon->real_escape_string($_REQUEST['id']);
quotation_pdf($id,"pdf",$dbcon);
//quotation_pdf(5646,"pdf",$dbcon);
function quotation_pdf($id,$type,$dbcon)
{	
	ob_start();
	$id=$dbcon->real_escape_string($id);	
	$query="select quot.*, inq.inquiry_no, cust.cust_name, ccon.c_con_fname, ccon.c_con_lname, addr.c_add_address,coun.country_name, state.state_name, city.city_name, cust.cust_mobile, cust.cust_email, cur.currency_code, cur.currency_id, cur.currency_in_word,cur.currency_in_word_end from tbl_quotation as quot
	left join tbl_inquiry as inq on inq.inquiry_id = quot.inquiry_id 
	
	left join tbl_customer as cust on cust.cust_id = quot.cust_id
	left join tbl_cust_contact as ccon on ccon.c_con_id =quot.c_con_id
	left join tbl_currency as cur on cur.currency_id = quot.currency_id
	left join tbl_cust_address as addr on addr.cust_id = quot.cust_id and c_addr_defult=1
	left join country_mst as coun on coun.countryid = addr.c_add_country
	left join state_mst as state on state.stateid = addr.c_add_state
	left join city_mst as city on city.cityid = addr.c_add_city
	where quot.quotation_id=".$id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$type="pdf";
	
	

    if(isset($type)== "pdf") {
		$type = $dbcon -> real_escape_string($type);
	}
	else {
		die('<h1> ERROR </h1>');
	}
      if(strtolower($type) == 'pdf') {
      	$set_head = "select * from tbl_company where company_id=".$rel['company_id'];
      	$company_rel=mysqli_fetch_assoc($dbcon->query($set_head));
		$h=$company_rel["logo"];
		$header ='<img src="'.DOMAIN_F.LOGO.$company_rel["logo"].'" style="width:8.27in" />';  
		$footer='<img src="'.DOMAIN_F.LOGO.$company_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';
$html ='<html>
				<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">					
					<title>Quotation - '.$rel['quotation_no'].'</title>
<style type="text/css">

.page{
	width:8.27in;
	height:10.69in;
}
.nextpage
{
	page-break-after: always;
}
table {
border-width: 0 0 1px 1px;
border-spacing: 0;
border-collapse: collapse;
border-style: solid;

}
th, tr, td {
margin: 0;
padding: 4px;
border-width: 1px 1px 0 0;
border-style: solid;
text-align: center;
}
th {
font-weight:bold;
}
.image2{
    max-height:8in !important;
}
td ul
{
  margin: 0px 0px;
}
.quotation_head{margin:0px 20px 0;font-size:20px;position: relative;width: 100%;}
.date{float:right; width:30%;font-weight:600;}
.quotationno{float:left;width:65%;font-weight:600;}
.logoverticle{ position: absolute; padding:0px; margin:0; width:1.5in;}
p {margin: 10px 0px;}

.brktbl tr,td{
border:1px solid #000 !important;
page-break-inside:avoid;
}

table tr,td{
border:1px solid #000 !important;
page-break-inside:avoid;
}
</style>
</head>
<body>

<htmlpageheader name="otherpages" style="display:none">
    <div style="text-align:center">'.$header.'</div>
</htmlpageheader>
<htmlpagefooter name="otherpages_footer" style="display:none">
    <div style="text-align:center">'.$footer.'</div>
</htmlpagefooter>
<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
<sethtmlpagefooter name="otherpages_footer" value="off" />

<div class="page">';

	$product_trn = "select pro.product_name, cat.cat_name, pcat.cat_name as pcat_name, pro.image_name,qtr.product_spec, qtr.product_amount_conv from tbl_quotation_trn as qtr 
	left join product_mst as pro on pro.product_id = qtr.product_id
	left join tbl_category as cat on cat.cat_id = pro.product_category
	left join tbl_category_reciclare as pcat on pcat.rcat_id = qtr.rcat_id
	where qtr.quot_trn_status=0 and qtr.quotation_id=".$rel['quotation_id'];
	
	$result_trn = $dbcon->query($product_trn);
	$db_res = brp_mysqli_fetch_all($result_trn);
	
	$i = 1;
	foreach($db_res as $row){
		$pcat_name = str_ireplace("[ category ]",$row['pcat_name'],$row['product_spec']);
		$cat_name = str_ireplace("[ subcategory ]",$row['cat_name'],stripcslashes($pcat_name));
		$price_sp = str_ireplace("[ price ]",$row['product_amount'].'<br />('.convert_number_to_words($row['product_amount']).')',stripcslashes($cat_name));
		if($i==1){
			$html.='
				<div class="quotation_head" >
					<table  style="border:solid; margin-left: 500px;">
						<tr style="border:solid;"><td style="border:solid; text-align: left;">
							DOC. NO.:RTPL/MKT/02
						</td>
						</tr>
						<tr style="border:solid;"><td style="border:solid; text-align: left;">
							REV.00
						</td>
						</tr>
						<tr style="border:solid;"><td style="border:solid; text-align: left;">
							ISSUE NO.01,01.09.2020
						</td>
						</tr>
					</table>

			        
					<div class="quotationno"><strong>INQUIRY NUM:'.$rel['inquiry_no'].'</strong></div>
					<div class="date"><strong>DATE: '.date("d/m/Y",strtotime($rel['quotation_date'])).'</strong></div>
					<div class="quotationno"><strong>QUOTE REF NUM:'.$rel['quotation_no'].'</strong></div>
				</div>';
		

		$html.='<div style="clear:both;"></div>
		<div style="width:30%;height:10px; float:left;height:6in;" > 
			<table  style="border:none; float:left;width:30%;height:10%;table-layout: fixed;" >
				<tr style="border:none;width:30%;height:10%;">
				<td style="border:none;width:30%;height:10%;">
					<img class="logoverticle" style="" src="'.DOMAIN_F.LOGO.$h.'" rotate="-90"/>
				</td>
				</tr>
			</table>
		</div>

		<div style="width:70%;height:10px; float:right">
			<div style="padding-top:50px;font-size:35px;">
				<div style="text-align:center">
					<u><span style="color:#d35400;"><b><i>TECHNO COMMERCIAL OFFER</i></b></span> <b><i> <br/>FOR</i></b> <br/><span style="color:#3498db"><b><i>'.$row['product_name'].'-'.$row['cat_name'].'-'.$row['pcat_name'].'</i></b></span></u>
				</div>';
				
				$html.='<div style="margin-left: 50px;margin-top:50px;font-size:20px;">
					To,<br> <b>'.strtoupper($rel['cust_name']).'</b> <br/> '.$rel['c_con_fname'] .' '.$rel['c_con_lname'].'<br>
						'.$rel['c_add_address'].'<br>
						'.ucwords(strtolower($rel['city_name'])).' , '.ucwords(strtolower($rel['state_name'])).'<br>
					Mobile No : '.$rel['cust_mobile'].'<br>
					Email : '.strtolower($rel['cust_email']).'
				</div>';
				
			$html.='</div>
		</div>';
		}else{
			$html .='<div style="width:70%;height:10px; float:right">
				<div style="padding-top:150px;font-size:35px;">
					<div style="text-align:center">
						<u><span style="color:#d35400;"><b><i>TECHNO COMMERCIAL OFFER</i></b></span> <b><i> <br/>FOR</i></b> <br/><span style="color:#3498db"><b><i>'.$row['product_name'].'-'.$row['cat_name'].'-'.$row['pcat_name'].'</i></b></span></u>
					</div>
				</div>
			</div>';
		}

		$html .='<center class="nextpage"></center>
		<sethtmlpagefooter name="otherpages_footer" value="on" />
		<center style="text-align:center;max-width:8in;">
			<img src="'.DOMAIN_F.PRO_IMG_VWING.$row["image_name"].'" class="image2" style="margin-top:60px;" />
		</center>
		<center class="nextpage"></center>
		'.$price_sp.' <br>';
		$i++;
	}
   	
$html.='</div><!--page1 end-->
<center class="nextpage"></center>
	<div>
		<span style="color:#3498db;font-size:16px"><strong><u>PRICE SCHEDULE : </u> </strong> </span> <br><br>
		<table style="width:100%">
			<tr>
				<th>No.</th>
				<th>System - Type</th>
				<th>Price ('.$rel['currency_code'].')</th>
			</tr>';
			$j=1;
			foreach($db_res as $row){
				$html.='<tr>
					<td>'.$j.'</td>
					<td>'.$row['product_name'].'-'.$row['cat_name'].'-'.$row['pcat_name'].'</td>
					<td>'.$row['product_amount_conv'].'</td>
				</tr>';
				$tamt = $tamt + $row['product_amount_conv']; 
			}
			$qry11="select sum((tc.tax_per*trn.product_amount_conv)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_quotation_trn as trn 
			left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
			left join tbl_ledger as l on l.l_id=tc.tax_id 
			where tc.tax_additional='1' and trn.quotation_id=".$rel['quotation_id']." and trn.quot_trn_status!=2 and tc.isdelete='0' group by tc.tax_id";
			$result11=$dbcon->query($qry11);		
			while($row11=mysqli_fetch_assoc($result11))
			{
				$tamt = $tamt + $row11['add_sum'];
				$html.='<tr>
				<td></td>
				<td style="text-align:right;border:1px solid;"><b>'.$row11['l_name'].'</b></td>
				<td style="text-align:center;border:1px solid;"><b>
				'.$rel['currency_code'].' '.indian_number($row11['add_sum'],2).'
				</b></td>
				</tr>';
			}

			$qry12="select b.sundry_amount_conv as sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
			from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
			left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
			where b.sundry_voucher_id=".$rel['quotation_id']." and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0'";
			$result12=$dbcon->query($qry12);		
			while($row12=mysqli_fetch_assoc($result12))
			{
				$sun_amt = $sum_amt + $row12['sundry_amount'];
				$html.='<tr>
				<td></td>
				<td style="text-align:right;border:1px solid;"><b>'.$row12['l_name'].'</b></td>
				<td style="text-align:center;border:1px solid;"><b>
				'.$rel['currency_code'].' '.indian_number($row12['sundry_amount'],2).'
				</b></td>
				</tr>';
			}
			$gtotal = $tamt + $sun_amt;
			$html .='
				<tr>
					<td colspan="2" style="text-align:right"><strong>Grand Total</strong></td>
					<td>'.$gtotal.'</td>
				</tr>

				<tr>
					<td colspan="2" style="text-align:right"><strong>Amount in Word</strong> : '.convert_number_to_words_new($gtotal,$rel['currency_id'],$rel['currency_in_word_end'],$rel['currency_in_word']).'</td>
				</tr>
			';
		$html.='</table>
	</div>
	'.$rel['quot_annex_content'].' <br><br>
<strong><u>For RECICLAR TECHNOLOGIES PVT LTD</u></strong>
</body>
</html>';
//echo $html;exit;
		ob_end_clean();

		include("../../view/export/mpdf/mpdf.php");
		/*echo $html;exit;*/
		$mpdf=new mPDF('','A4','0','calibri','10','10','45','40','1','1');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		//$mpdf->SetHTMLHeader($header);
		//$mpdf->SetHTMLFooter($footer);
		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->SetTitle($rel['quotation_no'].' - '.$rel['company_name'].'.pdf');
		$mpdf->WriteHTML($html);
		
		//$file_name = 'yourFileName.pdf';
        //$mpdf->Output($file_name, 'D');
		$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/'.$rel['quotation_no'].' - '.$rel['company_name'].'.pdf','d');
		ob_clean();
		return $rel['quotation_no'].' - '.$rel['company_name'].'.pdf';
		
}//pdf creation end
}//function end	

?>
