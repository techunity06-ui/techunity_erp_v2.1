<?php 
session_start();
ob_start();		
//error_reporting(E_ALL); 
 	$type="pdf";
	if(strtolower($type) == 'pdf') {
	$html = '
    <html>
	<head>
		<title>Billing360-Invoice PDF</title>
		<style type="text/css">
			body {
				font-size: 10px;
		    }
			table {
			    width:100%;
				border-spacing: 0;
				border-collapse: collapse;
		 	}
			th, tr, td {
				margin: 0;
				padding: 2px;
				height:20px;
			}
			
		</style>
	</head>
	<body>
	  '.$_SESSION['contents'].'
	</body>
    </html>';
		//	echo $html;exit;
		include("mpdf/mpdf.php");
		$page_size=$_SESSION['page_size'];
		$mpdf=new mPDF('',$page_size,'0','calibri','5','5','5','5');
		$mpdf->showWatermarkText = true;
		$mpdf->WriteHTML($html);
		$mpdf->Output($_SESSION['file_name'].$_SESSION['invoice_no'].'.pdf', 'D');
		//ob_end_clean();
	}
 	

?>
