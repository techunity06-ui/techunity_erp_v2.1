<?php
  
	session_start();
    include('../../config/config.php');
    include('../../config/session.php');
  
		$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
	
		$query="select *,cust.company_name,cust.cust_address from tbl_invoice as invoice inner join tbl_customer as cust on cust.cust_id=invoice.cust_id where invoice_id=$invoiceid";
		//echo $query;
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		//echo $rel;
		$query1="select *,tv.invoice_no from tbl_tranction as inv   inner join tbl_invoice as tv on tv.invoice_id=inv.invoice_id where inv.invoice_id=$invoiceid ";
		//echo $query1;
	$rel1=$dbcon->query($query1);
	$quy="select *,tv.employeeid from employee_mst as inv inner join tbl_invoice as tv on tv.employeeid=inv.employeeid where tv.invoice_id=$invoiceid";

		$re1=mysqli_fetch_assoc($dbcon->query($quy));
			
	//while($data = $query -> fetch_assoc()) {
		//$rows[] = $data;
//	}
	
    ///////////////////////////////////////////////////////////////////////
	
//	$type="pdf";
	
	var_dump(isset($_GET['type'])== "pdf");
    if(isset($_GET['type'])== "pdf") {
		$type = $dbcon -> real_escape_string($_GET['type']);
	}
	else {
		die('<h1> ERROR </h1>');
	}
    
     if(strtolower($type) == 'pdf') {
		$id_num = 1;
		
		$html = '
			<html>
				<head>
					
					<title>INVOICE</title>
				</head>
				<body>
				';
							
if($rel["g_total"]>$rel["paid_amount"]){
$a= "UNPAID";
}else
{
$a= "PAID";
}
 if($rel["g_total"]>$rel["paid_amount"]){
 $b= "PERFORMANCE";
}else
{
$b= "ORIGINAL";
}
$html = '	

				<table style="font-size:15px;"  width="100%">
									
					
							<tr><td colspan="3" style="border-top:none;"><img src="../img/logo.png" title="" /></td>
							
							<td  colspan="2" style="text-align:center; padding-top:10px;"><h3><strong><b class="data_title">'.$b.'</b><br><br></strong><strong>INVOICE</strong></h3></td></tr>
</table>

				<table style="font-size:15px"  width="100%">
				<tr>
<td colspan="2" style=" border-left:none;    border-bottom:none; "> SELLER:</td>

<td colspan="2" style=" border-left:none; border-bottom:none; text-align:right;">GO GREEN</td>
</tr>
				<tr>
<Td  colspan="2" style=" border-left:none;  border-top:none;  border-bottom:none;"><strong>Metr Technology</strong></Td>

<td rowspan="2" align="right" colspan="2" style=" border-left:none;  border-top:none;  border-bottom:none; font-size:15px; font-family:calibri; text-align:right;" width="50%">Electronic Bills. Paper files are a thing of the past. In many cases, there isnt a need for a file at all. You are doing more than going paperless, you are also saving trees.</td>
</tr>
				<tr>
<td colspan="1" style=" border-left:none;  border-top:none;  border-bottom:none; font-size:15px; font-family:calibri;">G608, Tianium City Center, Beside Sachin Tower, Anand nagar Road, Satelite, Ahmedabad, Gujarat, India. </td>


</tr>
<tr>
<td  colspan="2" style="  border-top:none;  border-bottom:none; "><strong>Sales Support:</strong>+91-9328850777</td>
<td  colspan="2" align="right" style=" border-left:none;  border-top:none;  border-bottom:none;  font-size:15px; font-family:calibri;"></td>
<td  style="  border-top:none;  border-bottom:none;"></td></tr>
			<tr style="padding-bottom:40px;">
<td colspan="2" style=" border-left:none;  border-top:none;  border-bottom:none;"><strong>Tech.Support:</strong>+91-7405409098</td>
<td  colspan="2" align="right" style=" border-left:none;  border-top:none;  border-bottom:none;  font-size:18px; font-family:calibri;">'.$a.'<strong>
</strong></td></tr>
</tr>
<tr>
<td colspan="3" style=" padding-top:20px; border-left:none;  border-bottom:none;">BUYER:</td>

<td  align="right"  style="  border-left:none; padding-left::50px;  border-bottom:none;margin-left:150px;"><strong>Invoice No.:</strong><strong>'.$rel['invoice_no'].'</strong></td>
</tr>
			<tr>
<td colspan="3" style=" border-left:none;  border-top:none;  border-bottom:none; height:12px; width:500px;font-size:15px; font-family:calibri;"><strong>'.$rel['company_name'].'</strong></td>
<td  align="right"  style=" border-left:none; border-top:none;  border-bottom:none;"><strong>Date:</strong> <strong> '.date('d-M-Y',strtotime($rel['invoice_date'])).'</strong></td>
</tr>
<tr>
<td colspan="1" style="border-right:none; border-left:none;  border-top:none;  border-bottom:none;font-size:15px; font-family:calibri;">'.$rel['cust_address'].'</td>
<td  align="" style=" border-right:none; border-top:none; border-bottom:none;  width:100px; "></td>
<td  align="right"   colspan="2" style=" border-left:none; border-top:none;  border-bottom:none;">Sales Person: '.ucwords($re1['employee_code']).'</td>
</tr>
		

<tr >
<td  colspan="3" style="padding-bottom:20px; border-left:none;  border-top:none;  border-bottom:none;font-size:15px; font-family:calibri;">Phone:'.$rel['cust_mobile'].'</td>
<td style=" border-right:none; border-left:none;  border-top:none;  border-bottom:none;"></td>
<td style="  border-right:none;border-left:none;  border-top:none;  border-bottom:none;"></td>
</tr>
</table>
<table border="1" style="font-size:15px"  width="100%">
<tr >
<td align="center" style= "border-left:none;  border-top:none; height:30px; width:2%"><strong>#</strong></td>
<td  align="center" colspan="3" style="border-left:none;  border-top:none; height:30px;"><strong>Description </strong></td>
<td align="center" style="border-left:none; border-right:none;  border-top:none; width:100;height:30px;"><strong>Amount</strong></td>
</tr>
';

$i=1;
while($row=mysqli_fetch_assoc($rel1)){
$html .='
<tr>
<td  align="center" style=" border-left:none; height:30px; border-top:none; width:20px;">'.$i .'</td>
<td  align="left" colspan="3" style=" padding-left:15px; border-left:none; border-top:none; width:40px; height:30px;">'.$row['product_name'].'</td>

<td align="center"  style="border-left:none; border-right:none;  border-top:none; width:60px; height:30px;">'.$row['product_amount'].".00"." INR".'</td>
</tr>';


 $i++;
 }

 $qry2="select SUM(product_amount) from  tbl_tranction where invoice_id=".$rel['invoice_id'];
	
										$rows2=mysqli_fetch_assoc($dbcon->query($qry2));
$html .='
<tr>
<td  align="right" colspan="4"  style=" border-left:none; border-right:none; height:30px; width:40px;  border-top:none;">Sub total</td>
<td align="center" style="border-left:none;border-top:none; border-right:none; width:60px;height:30px;">'.$rows2['SUM(product_amount)'].".00"." INR".' </td>
</tr>';
if($rel['discount']!= '0')
{
$html .='
<tr>
<td  align="right" colspan="4"  style=" border-left:none; border-right:none; height:30px;  border-top:none;">Special Discount'.$rel['discount']."%".'</td>

<td align="center" style="border-left:none; height:30px; border-top:none;border-right:none;">'.($rows2['SUM(product_amount)']*$rel['discount']/100).".00 INR".'</td>
</tr>';
}
if($rel['a_discount'] != '0')
{
$html .='
<tr>
<td  align="right" colspan="4"  style=" border-left:none; border-right:none; height:30px;  border-top:none;">Adjustments Discount</td>
<td align="center" style="border-left:none;height:30px; border-top:none; border-right:none;">'.$rel['a_discount'].".00" ." INR".'</td>
</tr>';
}
$html .='
<tr>

<td  align="right" colspan="4"  style=" border-left:none; border-right:none; height:30px;  border-top:none;"><strong>Final Total</strong></td>
<td align="center" style="border-left:none; height:30px; border-top:none; border-right:none;">'.$rel['g_total'].".00"." INR".'</td>
</tr>
<tr>
<td colspan="5" align="right" style="border-bottom:none; border-top:none; border-left:none; "><strong>'.convert_number_to_words($rel['g_total'])." Rupees Only".'</strong> </td></tr>
</table>
 <table style="font-size:14px"  width="100%">
<tr><td   colspan="2" style=" border-top:none; border-bottom:none;" width="50%"><strong>
Note:</strong></td>
<td align="center" colspan="3"  colspan="2" style=" border-left:none; border-top:none; border-bottom:none; font-size:15px;">


</td>
</tr>
<tr><td  colspan="2"style=" border-left:none; border-bottom:none; border-top:none; font-size:15px;" width="50%">
We declare that this Quotation shows the actual of the services
described and that all particulars are true and correct.</td>


</tr> 
<tr>
<td  colspan="2" style="border-left:none; border-bottom:none; border-top:none; font-size:15px; " width="150px"><strong> TERMS & CONDITIONS:</strong></td>
<td align="center" colspan="2" style="border-left:none; border-top:none; border-bottom:none; font-size:15px;"  width="150px;" >
<strong>Authorized Person</strong></td>
</tr>
<tr>
<td  colspan="2"style="  border-left:none; border-bottom:none; border-top:none; font-size:15px;" width="50%">1. Products services as per company Terms & Conditions.</td>
<td align="center" colspan="3"  colspan="2"style=" border-left:none; border-top:none; border-bottom:none; font-size:15px;">
</td>
</tr>
<tr>
<td  colspan="2"style=" border-left:none; border-bottom:none; border-top:none; font-size:15px;" width="50%">2. Payment by Cash / Cheque/ D. D in favor of <strong>METR TECHNOLOGY</strong></td>
<td align="center" colspan="3"  colspan="2"style=" border-left:none; border-top:none; border-bottom:none; font-size:15px;" width="50%">

</tr>
<tr>
<td  colspan="2"style="  border-left:none; border-bottom:none; border-top:none; font-size:15px; ">3. Payment is not refundable.</td>
<td align="center" colspan="3" colspan="2"style=" border-left:none; border-top:none; border-bottom:none; font-size:15px;">

</tr>

</table> 
<table>
	<Td align="center" colspan="1" style="text-align:center;"><strong>OUR MAIN SERVICES:</strong></Td>
	<td></td>
	<td align="center" colspan="3" style="text-align:center;"><strong>DOMAIN REGISTRATION | WEB HOSTING | SSL | BULK SMS SERVICES |</strong></td>
	</table>
				</body>
			</html>
		';
		
	//	echo $html;
		require('WriteHTML.php');
		$pdf=new PDF_HTML();
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak(true, 15);

		$pdf->AddPage();		
		$pdf->SetFont('Arial','B',14);
		
		$pdf->WriteHTML2($html);
		$pdf->Output();
		exit;
		
	}
?>
