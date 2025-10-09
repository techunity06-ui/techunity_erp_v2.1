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
	$query="select quot.*, inq.inquiry_no, cust.cust_name, ccon.c_con_fname, ccon.c_con_lname, addr.c_add_address,coun.country_name, state.state_name, city.city_name, cust.cust_mobile, cust.cust_email, pro.product_name, cat.cat_name, pcat.cat_name as pcat_name, pro.image_name,qtr.product_spec, qtr.product_amount_conv, cur.currency_code from tbl_quotation as quot
	left join tbl_inquiry as inq on inq.inquiry_id = quot.inquiry_id 
	left join tbl_quotation_trn as qtr on qtr.quotation_id = quot.quotation_id and quot_trn_status=0
	left join product_mst as pro on pro.product_id = qtr.product_id
	left join tbl_category as cat on cat.cat_id = pro.product_category
	left join tbl_category_reciclare as pcat on pcat.rcat_id = qtr.rcat_id
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
	
	$pcat_name = str_ireplace("[ category ]",$rel['pcat_name'],$rel['product_spec']);
	$cat_name = str_ireplace("[ subcategory ]",$rel['cat_name'],stripcslashes($pcat_name));
	$price_sp = str_ireplace("[ price ]",$rel['product_amount'].'<br />('.convert_number_to_words($rel['product_amount']).')',stripcslashes($cat_name));

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

<div class="page">
    
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
	</div>
	<div style="clear:both;"></div>
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
					<u><span style="color:#d35400;"><b><i>TECHNO COMMERCIAL OFFER</i></b></span> <b><i> <br/>FOR</i></b> <br/><span style="color:#3498db"><b><i>'.$rel['product_name'].'-'.$rel['cat_name'].'-'.$rel['pcat_name'].'</i></b></span></u>
				</div>
				<div style="margin-left: 50px;margin-top:50px;font-size:20px;">
					To,<br> <b>'.strtoupper($rel['cust_name']).'</b> <br/> '.$rel['c_con_fname'] .' '.$rel['c_con_lname'].'<br>
						'.$rel['c_add_address'].'<br>
						'.ucwords(strtolower($rel['city_name'])).' , '.ucwords(strtolower($rel['state_name'])).'<br>
					Mobile No : '.$rel['cust_mobile'].'<br>
					Email : '.strtolower($rel['cust_email']).'
				</div>
		</div>
	</div>

</div><!--page1 end-->
<center class="nextpage"></center>
<sethtmlpagefooter name="otherpages_footer" value="on" />';
$html.='<div><ul style="list-style: square;">
	<li>uno</li>
	<li>dos</li>
	<li>tres</li>
</ul>
</div>
List in table:
<table>
	<tbody>
		<tr>
			<td style="border:1px solid black">
				<ul style="list-style: none;">
					<li>uno</li>
					<li>dos</li>
					<li>tres</li>
				</ul>
			</td>
		</tr>
	</tbody>
</table>';
$html.='</body>
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
