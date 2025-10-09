<?php
    session_start();
    include('../../../config/config.php');
  //  error_reporting(E_ALL);
	include('../../../config/session.php');
    $rows = array();
    $cid = $dbcon -> real_escape_string($_GET['id']);
    $str = "SELECT * FROM `tbl_vender` as ven left join state_mst as state on state.stateid=ven.stateid left join city_mst as city on city.cityid=ven.cityid WHERE vender_status =0 and vendor_cat=2 AND  `company_id` = '$_SESSION[company_id]'";
    $query = $dbcon -> query($str);

    $data = $query -> fetch_assoc();
    
    "' width=300 height=200 />"):"NA";
    ///////////////////////////////////////////////////////////////////////
    
    $filename = strtolower(str_replace(" ","_",$data['cust_name'])).".pdf";
    
    $html = '
    <html>
	<head>
		<style type="text/css">
		    body {
			font-size: 10px;
		    }
			table {
			    width:100%;
				border-width: 0 0 1px 1px;
				border-spacing: 0;
				border-collapse: collapse;
				border-style: dotted;
			}
			th, tr, td {
				margin: 0;
				padding: 4px;
				border-width: 1px 1px 0 0;
				border-style: dotted;
				text-align: left;
			}
			th {
				font-weight:bold;
			}
		</style>
		<title>Customers - '.COMPANY.'</title>
	</head>
	<body>
	    <center><img src="'.DOMAIN.'images/mail/logo.png" height="80px" /></center>
	    <br /><br />
	    <table>
		<tr>
		    <th> NAME </th>
		    <td>'.$data['vender_name'].'</td>
		</tr>
		<tr>
		    <th> CITY </th>
		    <td>'.$data['city_name'].'</td>
		</tr>
		<tr>
		    <th> PHONE </th>
		    <td>'.$data['vender_mobile'].'</td>
		</tr>
		<tr>
		    <th> E-MAIL </th>
		    <td>'.$data['vender_email'].'</td>
		</tr>
		 
	    </table>
	</body>
    </html>
    ';
    
    include("mpdf/mpdf.php");
    $mpdf=new mPDF();
    $mpdf->defaultheaderfontsize = 10; // in pts 
    $mpdf->defaultheaderfontstyle = B; // blank, B, I, or BI 
    $mpdf->defaultheaderline = 1; // 1 to include line below header/above footer
    //$mpdf->defaultfooterfontsize = 10; // in pts
    //$mpdf->defaultfooterfontstyle = B; // blank, B, I, or BI
    //$mpdf->defaultfooterline = 1; // 1 to include line below header/above footer
    $mpdf->SetHeader('Date : {DATE j-m-Y} | Payee Details |'.COMPANY);
    //$mpdf->SetFooter('CoRo Technologies');
    $mpdf->SetWatermarkText(COMPANY);
    $mpdf->showWatermarkText = true;
    
    $mpdf->WriteHTML($html);
    $mpdf->Output($filename,'D');
    $mpdf->Output();
    exit;
?>

