<?php
    session_start();
    include('../../config/config.php');
    include('../../config/session.php');
 
    //$rows = array();
    
	//$str = "SELECT * FROM `coro_customers` WHERE `cust_status` = 1 and cust_of =". $_SESSION['user_id'] ." ORDER BY `cust_name` ";
	//$query = $dbcon -> query($str);
    
	$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select *,cust.company_name,cust.cust_address from tbl_invoice as invoice   inner join tbl_customer as cust on cust.cust_id=invoice.cust_id where invoice_id=$invoiceid";
		//echo $query;
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$query1="select *,tv.invoice_no from tbl_tranction as inv   inner join tbl_invoice as tv on tv.invoice_id=inv.invoice_id where inv.invoice_id=$invoiceid";
		//echo $query1;
	$rel1=$dbcon->query($query1);
	//while($data = $query -> fetch_assoc()) {
		//$rows[] = $data;
//	}
    
	function frmt($str) {
		if($str == NULL || $str == "" || $str == " ")
			$str = "  ";
		
		$str = str_replace("<BR>","  ",$str);
		$str = str_replace("<br>","  ",$str);
		$str = str_replace("'","",$str);
		$str = str_replace('"','',$str);
		$str = str_replace(";","",$str);
		$str = str_replace(",","",$str);
		$str = str_replace (array("\r\n", "\n", "\r"), ' ', $str);
		return " ".$str." ";
	}
	
    ///////////////////////////////////////////////////////////////////////
    if(isset($_GET['type'])) {
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
					<style type="text/css">
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
					</style>
					<title>TAX INVOICE</title>
				</head>
				<body>
				<table>
				<tr>
					<td colspan="3" style=" border-left:none;    border-bottom:none; color:#00CCFF;">TO SELLER:</td>
					<td  align="center" style=" border-left:none;   border-bottom:none;"><strong>Invoice</strong></td>
					<td style=" border-left:none; border-bottom:none; color:#00FF99; ">GO GREEN</td>
				</tr>
				<tr>
					<Td  colspan="3"style=" border-left:none;  border-top:none;  border-bottom:none;"><strong>Metr Technology</strong></Td>
					<td  style=" border-left:none;  border-top:none;  border-bottom:none;"></td>
					<td rowspan="3" style=" border-left:none;  border-top:none;  border-bottom:none;font-size:12px; font-family:Arial; ">Electronic Bills. Paper files are a thing of the past. In many cases, there isnt a need for a file at all. You are doing more than going paperless, you are also saving trees.</td>
				</tr>
				<tr>
					<td colspan="3" style=" border-left:none;  border-top:none;  border-bottom:none; font-size:12px; font-family:Arial;">G608, Tianium City Center, Beside Sachin Tower, Anand nagar Road, Satelite, Ahmedabad, Gujarat, India. </td>
					"<td align="center" style=" border-left:none;  border-top:none;  border-bottom:none;  font-size:12px; font-family:Arial;"><strong>"'. if($rel["g_total"]>$rel["paid_amount"]){
echo "UNPAID";
}else
{
echo "PAID";
}.'"</strong></td>
					<Td  style="border-left:none;  border-top:none;  border-bottom:none;  font-size:12px; font-family:Arial;"></Td>
				</tr>	
				<tr>
					<td  colspan="3" style="  border-top:none;  border-bottom:none; "><strong>Sales Support:</strong>+91-9328850777</td>
					<td  style="  border-top:none;  border-bottom:none;"></td>
					<td  style="  border-top:none;  border-bottom:none;"></td></tr>
			<tr>
					<td colspan="2"style=" border-left:none;  border-top:none;  border-bottom:none;"><strong>Tech.Support:</strong>+91-7405409098</td>
					<td  style="  border-top:none;  border-bottom:none;"></td>
					<td  style="  border-top:none; border-bottom:none;"></td></tr>
			</tr>
			<tr>
					<td colspan="3"style=" border-left:none;  border-bottom:none;color:#00CCFF;">TO BUYER:</td>
					<td  align="left" style=" border-right:none; border-left:none;   border-bottom:none;  margin-right:150px;"><strong>Invoice No.:</strong></td>
					"<td style="  border-left:none;  border-bottom:none;margin-left:150px;"><strong>".$rel['invoice_no']."</strong></td>
			</tr>
			<tr>
					"<td colspan="3"style=" border-left:none;  border-top:none;  border-bottom:none; height:12px; width:500px;font-size:12px; font-family:Arial;"><strong>".$rel['company_name']."</strong></td>"
<td  align="left"style=" border-right:none; border-left:none;   border-bottom:none; border-top:none; width:100px;"><strong>Date:</strong></td>
"<td style=" border-left:none; border-top:none;  border-bottom:none;"> <strong> "'.date('d-M-Y',strtotime($rel['invoice_date'])).'"</strong></td>
</tr>
<tr>
<td colspan="3" style="border-right:none; border-left:none;  border-top:none;  border-bottom:none;font-size:12px; font-family:Arial;">"'.$rel['cust_address'].'"</td>
<td  align="left"rowspan="2" style=" border-right:none; border-top:none; border-bottom:none;  width:100px; ">Sales Person:</td>
<td rowspan="2" style=" border-left:none; border-top:none;  border-bottom:none;">"'.ucwords($rel['cust_name']).'"</td>
</tr>
<tr>
<td  colspan="3" style=" border-left:none;  border-top:none;  border-bottom:none;font-size:12px; font-family:Arial;">Phone: "'.$rel['cust_mobile'].'"</td>
<td style=" border-right:none; border-left:none;  border-top:none;  border-bottom:none;"></td>
<td style="  border-right:none;border-left:none;  border-top:none;  border-bottom:none;"></td>
</tr>
</table>
<table border="1"style="font-size:15px"  width="100%">
<tr >
<td align="center" style= "border-right:none; border-left:none;  border-top:none;height:30px;"><strong>Id</strong></td>
<td  align="center"colspan="2" style="border-left:none; border-right:none;  border-top:none; height:30px;"><strong>DESCRIPTION PRODUCTS/SERVICES</strong></td>
<td align="center"style="border-left:none; border-right:none;  border-top:none; width:100;height:30px;"><strong>NET CHARGES</strong></td>
</tr>
"'. 
$i=1;
while($row=mysqli_fetch_assoc($rel1)){.'"
<tr>
<td  align="center" style="border-right:none; border-left:none; height:30px; border-top:none;">"'.$i.'"</td>
<td  align="left" colspan="2" style="border-left:none; border-top:none; height:30px;">"'.$row['product_name'].'"</td>

<td align="right" style="  border-top:none;height:30px;  border-right:none;">"'.$row['product_amount'].".00"."INR".'"</td>
</tr>
"'.
 $i++;
 }

 $qry2="select SUM(product_amount) from  tbl_tranction where invoice_id=".$rel['invoice_id'];
	
										$rows2=mysqli_fetch_assoc($dbcon->query($qry2));
.'"
<tr>
<td style="border-right:none; border-left:none; border-top:none; border-bottom:none;"></td>
<td  style="border-left:none; border-right:none;  border-top:none;border-bottom:none;"></td>
<td  align="right"style="border-right:none; height:30px; ">Sub total</td>
<td align="right"style="border-left:none;border-top:none; border-right:none;height:30px;">"'.$rows2['SUM(product_amount)'].".00"."INR".'" </td>
</tr>
<tr>
<td style="border-right:none; border-left:none; border-top:none;border-bottom:none;"></td>
<td  style="border-left:none; border-right:none;  border-top:none; border-bottom:none;"></td>
<td  align="right"style="border-right:none; height:30px;  border-top:none;">Special Discount</td>
<td align="right"style="border-left:none; height:30px; border-top:none;border-right:none;">"'.$rel['discount'].".00"."INR".'"</td>
</tr>
<tr>
<td style="border-right:none; border-left:none; border-top:none;border-bottom:none;"></td>
<td  style="border-left:none; border-right:none;  border-top:none; border-bottom:none;"></td>
<td  align="right"style="border-right:none; height:30px;  border-top:none;">Adjustments Discount</td>
<td align="right"style="border-left:none;height:30px; border-top:none;">"'.$rel['a_discount'].".00" ."INR".'"</td>
</tr>
<tr>
<td style="border-right:none; border-left:none; border-top:none;"></td>
<td  style="border-left:none; border-right:none;  border-top:none; "></td>
<td  align="right"style="border-right:none; height:30px; border-top:none;">Final Total</td>
<td align="right" style="border-left:none;height:30px; border-top:none; border-right:none;">"'.$rel['g_total'].".00"."INR".'"</td>
</tr>
<tr>
<td colspan="4"  style="border-right:none; border-left:none; border-top:none;" ><strong>Rupees:"'.convert_number_to_words($rel['g_total']).'"</strong> </td></tr>
<tr>
<td rowspan="2" style="border-right:none; border-left:none; border-top:none;border-bottom:none;  "><strong>IMPORTANT 
MESSAGES</strong></td>
<td  style="border-right:none; border-left:none; border-top:none; border-bottom:none;font-size:10px; font-family:Arial;">Please keep your information private & confidential.</td>
<td colspan="2" align="center" style="border-right:none; border-left:none; border-top:none; border-bottom:none; font-family:Arial, Helvetica, sans-serif; ">BILLING ENQUIRIES?</td>
</tr>
<tr>
<td style="border-right:none; border-left:none; border-top:none; border-bottom:none;font-size:10px; font-family:Arial;" >Additionally, you can register our SMS Alerts, which provide regular sms updates on your account activity.</td>
  
<td colspan="2" style="border-right:none; border-left:none; border-top:none; border-bottom:none; font-size:12px; font-family:Arial; ">

Billing and Order activation please raise a Ticket support, A member of staff will then be able to assist you from there.</td>
</tr>
<tr><td   colspan="4"style="border-right:none; border-left:none; border-top:none; border-bottom:none;"><strong>
Responsibilities of Clients</strong></td>

</td>
</tr>
<tr><td  colspan="2"style="border-right:none;  border-left:none; border-bottom:none; border-top:none; font-size:12px ">
We declare that this Quotation shows the actual of the services
described and that all particulars are true and correct.</td>



</td>
<td rowspan="5" align="justify" colspan="2"style="border-right:none; border-left:none; border-top:none; border-bottom:none; font-size:12px">
<strong>Note:</strong>In case you are receiving our e-mail in your Junk-mail/Spam, mark this e-mail as Not Junk/Spam or add it to your Safe Senders list.
</td></tr> 
<tr>
<td  colspan="2"style="border-right:none;  border-left:none; border-bottom:none; border-top:none; font-size:12px ">TERMS & CONDITIONS:</td>
</tr>
<tr>
<td  colspan="2"style="border-right:none;  border-left:none; border-bottom:none; border-top:none; font-size:12px ">1. Products services as per company Terms & Conditions.</td>
</tr>
<tr>
<td  colspan="2"style="border-right:none;  border-left:none; border-bottom:none; border-top:none; font-size:12px ">2. Payment by Cash / Cheque/ D. D in favor of METR TECHNOLOGY</td>
</tr>
<tr>
<td  colspan="2"style="border-right:none;  border-left:none; border-bottom:none; border-top:none; font-size:12px ">3. Payment is not refundable.</td>
</tr>
</table>
				</body>
			</html>
		';
		
		
		
		include("mpdf/mpdf.php");
		$mpdf=new mPDF();
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHeader('{DATE j-m-Y}|{PAGENO}/2| Tax Invoice');
		$mpdf->SetFooter(COMPANY);
		$mpdf->SetWatermarkText(COMPANY);
		$mpdf->showWatermarkText = true;
		
		$mpdf->WriteHTML($html);
		$mpdf->Output();
		exit;
		
	}
?>
