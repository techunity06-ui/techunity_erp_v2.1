<?php 
require_once '../../vendor/autoload.php';
use Mpdf\Mpdf;

session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
$type='pdf';
$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
if(strtolower($type) == 'pdf') {
	error_reporting(E_ALL);
	

	 $query = "SELECT invoice.*, country.country_name, eway_trn.eway_bill_transport_type, state.state_name, cust.stateid,tcomp.vatno,tcomp.company_name, state.gst_state_code, city.city_name, cust.l_name, cust.m_address, type.invoice_type, cust_pincode, cust_mobile, gst_no, dispatch.mode_dispatch, cust.m_pan, cust.enable_sez, pay.payment_terms as terms,cust_cit.city_name as comp_city from tbl_invoice as invoice 
	left join tbl_ledger as cust on cust.l_id=invoice.cust_id
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid
	left join city_mst as city on city.cityid=cust.cityid
	left join pay_terms as pay on pay.terms_id=invoice.payment_terms
	left join tbl_company as tcomp on tcomp.company_id=invoice.company_id
	left join state_mst as cust_sta on cust_sta.stateid=tcomp.stateid
	left join city_mst as cust_cit on cust_cit.cityid=tcomp.city_id
	left join eway_transport_mode as eway_trn on eway_trn.eway_transport_mode_id=invoice.trn_mode
	left join mode_of_dispatch as dispatch on dispatch.mode_dis_id=invoice.dispatch_doc_no
	left join tbl_invoicetype as type on type.invoicetype_id=invoice.invoicetype_id
	where invoice.invoice_id=".$invoiceid; 
	$result = $dbcon->query($query);
	$row = brp_mysqli_fetch_array($result); 
// 		print_r($row);
	$year  = date('Y',strtotime($row['eway_bill_date']));
	$month  = date('m',strtotime($row['eway_bill_date']));
	
	$vehicle_no = "";

	if(!empty($row['vehicle_no'])){
		$vehicle_no = $row['vehicle_no'];
	}else if(!empty($row['lrno'])){
		$vehicle_no = $row['lrno'] . '& Date:'.date("d/m/Y",strtotime($row['lrdate']));
	}

	$data_string=$row['eway_bill_no'].'/'.$row['vatno'].'/'.date("d/m/Y");
	//echo '<br/>';
	//QRcode Start
   //set it to writable location, a place for temp generated PNG files
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    //html PNG location prefix
    $PNG_WEB_DIR = '../temp/';
	include "../../inventory/view/qrcode/qrlib.php";    
    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);
    $filename = $PNG_TEMP_DIR.'test.png';
    //processing form input
    //remember to sanitize user input in real-life solution !!!
    $errorCorrectionLevel = 'L';
	$matrixPointSize = 1;
	$filename = $PNG_TEMP_DIR.'test'.md5($data_string.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        QRcode::png($data_string, $filename, $errorCorrectionLevel, $matrixPointSize, 2); 

	//Show Only id if User Type mobile
	
   //QRcode End
 

	$curl = curl_init();
	/*CURLOPT_POSTFIELDS =>'{
			"GSTIN": "'.$row['vatno'].'",
			"ewbNo": '.$row['eway_bill_no'].',
			"EWBUserName": "'.EWAY_USERNAME.'",
			"EWBPassword": "'.EWAY_PASSWORD.'",
			"Year": '.$year.',
			"Month": '.$month.',
			"EFUserName": "'.EWAY_USERNAME.'",
			"EFPassword": "'.EWAY_PASSWORD.'",
			"CDKey": "'.EWAY_CDKEY.'"
			}',*/

	// curl_setopt_array($curl, array(
	//   CURLOPT_URL => 'http://ewayasp.webtel.in/EWayBill/v1.3/GetEWB',
	//   CURLOPT_RETURNTRANSFER => true,
	//   CURLOPT_ENCODING => '',
	//   CURLOPT_MAXREDIRS => 10,
	//   CURLOPT_TIMEOUT => 0,
	//   CURLOPT_FOLLOWLOCATION => true,
	//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	//   CURLOPT_CUSTOMREQUEST => 'POST',
	//   CURLOPT_POSTFIELDS =>'{
	// 		"GSTIN": "24AABCC6052G1Z1",
	// 		"ewbNo": 671520490875,
	// 		"EWBUserName": "Flowjet$12_API_fvp",
	// 		"EWBPassword": "Flowjetvalve@1",
	// 		"Year": 2023,
	// 		"Month": 2,
	// 		"EFUserName": "'.EWAY_USERNAME.'",
	// 		"EFPassword": "'.EWAY_PASSWORD.'",
	// 		"CDKey": "'.EWAY_CDKEY.'"
	// 		}',
	//   CURLOPT_HTTPHEADER => array(
    // 		'Content-Type: application/json'
  	// 	),
	// ));

	// $response = curl_exec($curl);

	// curl_close($curl);

	
	// $jsonobj = json_decode($response);
	// $jsonobj1 = json_decode($jsonobj);
	// print_r($jsonobj1);exit;
	
	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$set_head=brp_mysqli_fetch_assoc($dbcon->query($set));	

// 	print_r($set_head);
	$challan_date='';
	if($rel['challan_date']!="1970-01-01" && $rel['challan_date']!="0000-00-00")
	{
		$challan_date=date('d-m-Y',strtotime($rel['challan_date']));
	}
	
	$custLedgerDetails = get_cust_data_arr($dbcon,$rel['cust_id']);
	$company_config = getCompanyConfiguration($dbcon);

	$arr = get_grossbalance($dbcon,$rel['cust_id']);
	$company_state = get_company_data($dbcon,$_SESSION['company_id']);
	$html ='<html>
	<head>					
	<title>Invoice - '.$row['eway_bill_no'].'</title>
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
			width:100%;
			border-collapse:collapse;
			
		}

		table tr,td,th{
			
			font-size:12px;
			border:0.5px solid grey;
			/*page-break-inside:avoid;*/
		}
		.quot_annex_content_div table tr,td{
			padding:5px;
		}

		</style>
		
		<script type="text/javascript" src="'.ROOT.'js/jquery-2.1.0.js"></script>
		<script type="text/javascript" src="'.ROOT.'js/jquery-barcode.js"></script>
    
		</head>
		<body>

		<htmlpageheader name="otherpages" style="display:none">
		<div style="text-align:center">'.$header.'</div>
		</htmlpageheader>
		<htmlpagefooter name="otherpages_footer" style="display:none">
		<div style="text-align:center">'.$footer.'</div>
		</htmlpagefooter>
		<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
		<div>';

		$html .='	<table style="width:100%">
				<tr>
					<td colspan="2" style=" vertical-align:top; text-align:center">
						<h2 style="margin-bottom:5px !important;">e-Way Bill</h2>
						<div><img height="125" width="125" src="'.$filename .'"></div>
					</td>
				</tr>
				<tr>
					<td style="width:40%;height:35px">E-Way Bill No : </td>
					<td style="width:60%">'.$row['eway_bill_no'].'</td>
				</tr>
				<tr>
					<td style="width:40%;height:35px">E-Way Bill Date : </td>
					<td style="width:60%">'.date('d-m-Y',strtotime($row['eway_bill_date'])).'</td>
				</tr>
				<tr>
					<td style="width:40%;height:35px">Generate By : </td>
					<td style="width:60%">'.$row['vatno'].', <b>'.$row['company_name'].'</b></td>
				</tr>
				<tr>
					<td style="width:40%;height:35px">Valid From : </td>
					<td style="width:60%"> <b>'. date("d/m/Y",strtotime($row['lr_date'])) .' ';
					if($row['trn_distance']){
						$html.='['.$row['trn_distance'].'Kms]</b>';
					 }


					 $html.='</td></tr>
				<tr>
					<td style="width:40%;height:35px">Valid Until: </td>
					<td style="width:60%"> <b>'. date("d/m/Y",strtotime($row['eway_bill_date'])) .'</b></td>
				</tr>
			
				<tr>
					<td style="width:40%;height:35px" colspan="2"><strong>Part - A</strong></td>
				</tr>
				<tr>
					<td style="width:40%;height:35px">GSTIN of Supplier</td>
					<td style="width:60%">'.$row['vatno'] . '</td>
				</tr>
				<tr>
					<td style="width:40%;height:35px">GSTIN of Recipient</td>
					<td style="width:60%">'.$row['gst_no'].'</td>
				</tr>

				<tr>
					<td style="width:40%;height:35px">Place of Dispatch</td>
					<td style="width:60%">'.$row['city_name'].','.$row['state_name'].'-'.$row['cust_pincode'].'</td>
				</tr>

				<tr>
					<td style="width:40%;height:35px">Document No.</td>
					<td style="width:60%">'.$row['invoice_no'].'</td>
				</tr>

				<tr>
					<td style="width:40%;height:35px">Document Date.</td>
					<td style="width:60%">'.date('d-m-Y',strtotime($row['invoice_date'])).'</td>
				</tr>
				<tr>
					<td style="width:40%;height:35px">Tansaction Type :</td>
					<td style="width:60%">Regular</td>
				</tr>
				<tr>
					<td style="width:40%;height:35px">Value Of Goods :</td>
					<td style="width:60%">'.$row['g_total'].'</td>
				</tr>
				<tr>';
				$query = "select GROUP_CONCAT(trn.product_hsn_code
				SEPARATOR '<br/>- ') as hsn_code  FROM `tbl_invoicetrn` as trn where trancation_status=0 and invoice_id=" . $row['invoice_id'] . " group by trn.product_hsn_code";
				$rs_tax = $dbcon->query($query); 
				$rel_tax = brp_mysqli_fetch_assoc($rs_tax);
			
			$html .='
					<td style="width:40%;height:35px">HSN Code</td>
					<td style="width:60%">'.$rel_tax['hsn_code'].'</td>
				</tr>
				<tr>
					<td style="width:40%;height:35px">Reason for Transportation</td>
					<td style="width:60%">Outward - Supply</td>
				</tr>
				<tr>';
				$sel_t = $dbcon->query("select trn.* from transportation_details as trn 
				where id="
				.$row['transport_id']);
				$r_t=brp_mysqli_fetch_assoc($sel_t);
				
				$html .='
					<td style="width:40%;height:35px">Transporter</td>
					<td style="width:60%">'.$row['vatno'] . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ' . $r_t['transportation_name'].'</td>
				</tr>

				<tr>
				<td colspan="2" style="width:100%;"><b>Part - B</b></td>
			
			</tr>
				<tr>
				<td colspan="2" style="width:100%">
				<table style="width:100%" >
				<tr style="width:100%">
				<td style="width:100%">
				<b>Mode</b></td>
				<td>
				<b>Vehicle / Trans
				Doc No & Dt.</b></td>
				<td>
				<b>From</b>	</td>
				<td>
				<b>Entered Date</b></td>
				<td>
				<b>Entered By</b></td>
				<td>
				<b>CEWB No.
				(If any)</b></td>
				<td>
				<b>Multi Veh.Info
				(If any)</b></td>
				</tr>

				<tr style="width:100%">
				<td style="width:100%">
				'.$row['eway_bill_transport_type'] . '</td>
				<td>
				'.$vehicle_no . '</td>
				<td>
					'.$row['comp_city'].'</td>
				<td>
				'.date("d/m/Y H:i A",strtotime($row['cdate'])).'</td>
				<td>
				'.$row['vatno'].'</td>
				<td>
				-</td>
				<td>
				-</td>
				</tr>
				<tr>
				<td colspan="7">
					 <div id="barcodeTarget" style="text-align:center">
					 </div>
				</td>
				</tr>
				<tr>
				<td colspan="7">
					<p style="font-size:8px">Note*: If any discrepancy in information please try after sometime </p>
				</td>
				</tr>
				</table>
				
				</td>
			
			</tr>
			</table>


		
		</div>';
		
		$html.='</body>';
?>


      <script type="text/javascript">
			function generateBarcode(){
        var value = '<?=$row['eway_bill_no']?>';
        var btype = "code39";
        var renderer = "css";
        console.log(value);
		console.log(btype);
		console.log(renderer);
		
        var settings = {
          output:renderer,
          bgColor: "#FFFFFF",
          color: "#000000",
          barWidth: "2",
          barHeight: "40",
          addQuietZone: 1
        };
		console.log(settings);
          $("#barcodeTarget").html("").show().barcode(value, btype, settings);
      }
      document.addEventListener("DOMContentLoaded", function() {
		    generateBarcode();
		}); 	
      </script>

<?

		
		$html .='</html>';
		// echo $html; exit;
		// $get = "hello";
		ob_end_clean();
		include("../../vendor/mpdf/mpdf/src/Mpdf.php");
		$mpdf = new Mpdf(['format' => 'A4','margin_left' => 10,'margin_right' => 10,'margin_top' => 5,'margin_bottom' => 5,'margin_header' => 1,'margin_footer' => 1,'default_font' => 'calibri']);
	
		// include("../../view/export/mpdf/mpdf.php");
		// $mpdf=new mPDF('','A4','0','calibri','10','10','5','5','1','1');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = 'B'; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = 'B'; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		//$mpdf->SetHTMLFooter($footer);
		$mpdf->pagenumPrefix = 'Page ';
		$mpdf->pagenumSuffix = ' of ';
		$mpdf->nbpgPrefix = ' ';
		$mpdf->nbpgSuffix = '';
		//$mpdf->SetFooter('{PAGENO}{nbpg}');
		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='UTF-8';
		$mpdf->WriteHTML($html);
		$mpdf->Output();
				//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
		ob_clean();
		return 'Invoice Receipt '.$invoiceid.'.pdf';
	}

?>