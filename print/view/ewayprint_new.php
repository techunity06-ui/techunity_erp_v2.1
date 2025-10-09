<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
//require_once('../../phpqrcode-master/qrlib.php');
include '../../phpqrcode-master/qrlib.php';
$path = '../../phpqrcode-master/temp/';
$file = $path.uniqid().".png";
$ecc = 'L';
$pixel_Size = 3;
$frame_Size = 3;
$company_config = getCompanyConfiguration($dbcon);

$type='pdf';
$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
if(strtolower($type) == 'pdf') {
	
	$query = "select inv.*,led.l_name,com.vatno from tbl_invoice as inv 
	left join tbl_ledger as led on led.l_id=inv.cust_id
	left join tbl_company as com on com.company_id = inv.company_id 
	where inv.invoice_id=".$invoiceid;
	$result = $dbcon->query($query);
	$row = brp_mysqli_fetch_array($result); 
	$year  = date('Y',strtotime($row['eway_bill_date']));
	$month  = date('m',strtotime($row['eway_bill_date']));
	
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
			/*CURLOPT_POSTFIELDS =>'{
			"GSTIN": "24AABCC6052G1Z1",
			"ewbNo": 671520490875,
			"EWBUserName": "Flowjet$12_API_fvp",
			"EWBPassword": "Flowjetvalve@1",
			"Year": 2023,
			"Month": 2,
			"EFUserName": "'.EWAY_USERNAME.'",
			"EFPassword": "'.EWAY_PASSWORD.'",
			"CDKey": "'.EWAY_CDKEY.'"
			}',*/
	$curl = curl_init();
	

	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'http://ewayasp.webtel.in/EWayBill/v1.3/GetEWB',
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'POST',
	  CURLOPT_POSTFIELDS =>'{
			"GSTIN": "'.$row['vatno'].'",
			"ewbNo": '.$row['eway_bill_no'].',
			"EWBUserName": "'.$company_config['gsp_username'].'",
			"EWBPassword": "'.$company_config['gsp_password'].'",
			"Year": '.$year.',
			"Month": '.$month.',
			"EFUserName": "'.EWAY_USERNAME.'",
			"EFPassword": "'.EWAY_PASSWORD.'",
			"CDKey": "'.EWAY_CDKEY.'"
			}',
	  CURLOPT_HTTPHEADER => array(
    		'Content-Type: application/json'
  		),
	));

	$response = curl_exec($curl);
	//print_r($response);
	curl_close($curl);

	$jsonobj = json_decode($response);
	$jsonobj1 = json_decode($jsonobj);
	//var_dump($jsonobj);
	foreach ($jsonobj1 as $obj) {
		// Print the properties of the object
	   //echo "ErrorMessage: " . $obj->ErrorMessage . "\n";
	  // echo "GSTIN: " . $obj->GSTIN . "\n";
	  //  echo "DocNo: " . $obj->DocNo . "\n";
	    //echo "EWBDetails: " . $obj->EWBDetails->cessNonAdvolValue . "\n";
	   
	    $ac=$obj->EWBDetails;
	    $ac1=$jsonobj = json_decode($ac,true);
	    // var_dump($ac1['ewbNo']);
	     //var_dump($ac1['itemList']);
	    // var_dump($ac1['VehiclListDetails']);
	    
	}
	//var_dump($ac1['userGstin']);
	//exit;
	$set_trans="select eway_bill_transport_type from eway_transport_mode as comp where gst_code='".$ac1['transMode']."'";
	$settrans=brp_mysqli_fetch_assoc($dbcon->query($set_trans));	

	$set_spty="select eway_sub_type_name from eway_sub_type as comp where code='".$ac1['subSupplyType']."'";
	$setspty=brp_mysqli_fetch_assoc($dbcon->query($set_spty));
	if($ac1['supplyType']=="O"){
		$supplyType="Outward";
	}else{
		$supplyType="Inward";
	}
	$set_ser="select invoice_type from tbl_invoicetype as comp where gst_code='".$ac1['docType']."'";
	$setser=brp_mysqli_fetch_assoc($dbcon->query($set_ser));
	if($ac1['transactionType']=="1.0"){
		$transation_type="Regular";
	}else{
		$transation_type="Different";
	}
	$set_frst="select state_name from state_mst as comp where gst_state_code='".$ac1['fromStateCode']."'";
	$setfrst=brp_mysqli_fetch_assoc($dbcon->query($set_frst));

	$set_tost="select state_name from state_mst as comp where gst_state_code='".$ac1['toStateCode']."'";
	$settost=brp_mysqli_fetch_assoc($dbcon->query($set_tost));



	$type=$supplyType." - ".$setspty['eway_sub_type_name'];
	$transport_mode=$settrans['eway_bill_transport_type'];
	$doc_detail=$setser['invoice_type']." - ".$ac1['docNo']." - ".$ac1['docDate'];
	$from_state=$setfrst['state_name'];
	$to_state=$settost['state_name'];
	$mdate=date('m/d/Y',strtotime($ac1['ewayBillDate']));
// Generates QR Code and Stores it in directory given
	QRcode::png($ac1['ewbNo'], $file, $ecc, $pixel_Size, $frame_Size);
	
	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$set_head=brp_mysqli_fetch_assoc($dbcon->query($set));	
	
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
	<title>Invoice - '.$rel['invoice_no'].'</title>
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
		<div>
		<table style="width:100%;">
			<tr>
					<td  style=" vertical-align:top; text-align:left">
						'.$mdate.'
					</td>
					<td  style=" vertical-align:top; text-align:center">
						<h2>e-Way Bill  </h2>
					</td>
					<td  style=" vertical-align:top; text-align:right;">
						<img src="'.$file.'">
					</td>
				</tr>
		</table>
		<table style="width:100%;border-collapse:collapse;">
			<tr>
				<td style=" vertical-align:top; text-align:left" colspan="6"><strong>1. E-WAY BILL Details </strong></td>
			</tr>
			<tr style="">
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;">
						EWay Bill No
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;border-right:1px solid;">
						: '.$ac1['ewbNo'].'
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;">
						Generated Date
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;border-right:1px solid;">
						: '.$ac1['ewayBillDate'].'
					</td>13px
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;">
						Generated By
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;">
						: '.$ac1['userGstin'].'
					</td>
					
				</tr>
				<tr>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;">
						Mode
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;border-right:1px solid;">
						: '.$transport_mode.'
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;">
						Approx Distance
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;border-right:1px solid;">
						: '.$ac1['actualDist'].'
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;">
						Valid Upto
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;">
						: '.$ac1['validUpto'].'
					</td>
				</tr>
				<tr>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;">
						Type
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;border-right:1px solid;">
						: '.$type.'
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;">
						Document Details
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;border-right:1px solid;">
						: '.$doc_detail.'
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;">
						Transaction type
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;">
						: '.$transation_type.'
					</td>
				</tr>
		</table>
		<table style="width:100%;margin-top:10px;border-collapse:collapse;">
			<tr>
				<td style=" vertical-align:top; text-align:left;height:40px;vertical-align:bottom;" colspan="2"><strong>2. Address Details </strong></td>
			</tr>
			<tr>
					<td  style="vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;width:50%;">
						From
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;white-space:nowrap;width:50%;">
						 To
					</td>
					
				</tr>
				<tr>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-top:1px solid;border-left:1px solid;border-right:1px solid;">
						GSTIN: '.$ac1['fromGstin'].' </br>
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-top:1px solid;border-left:1px solid;border-right:1px solid;">
						GSTIN: '.$ac1['toGstin'].' </br>
					</td>
				</tr>
				<tr>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-left:1px solid;border-right:1px solid;">
						'.$ac1['fromTrdName'].'
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-left:1px solid;border-right:1px solid;">
						'.$ac1['toTrdName'].'
					</td>
				</tr>
				<tr>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-left:1px solid;border-right:1px solid;">
						'.$from_state.'
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-left:1px solid;border-right:1px solid;">
						'.$to_state.'
					</td>
				</tr>
				<tr>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-left:1px solid;border-right:1px solid;">
						::Dispatch From::
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-left:1px solid;border-right:1px solid;">
						::Ship To:: </br>
					</td>
				</tr>
				<tr>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-left:1px solid;border-right:1px solid;">
						'.$ac1['fromAddr1'].' 
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-left:1px solid;border-right:1px solid;">
						'.$ac1['toAddr1'].'
					</td>
				</tr>
				<tr>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-bottom:1px solid;border-left:1px solid;border-right:1px solid;">
						'.$ac1['fromPlace'].' '.$from_state.' '.$ac1['fromPincode'].'
					</td>
					<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-bottom:1px solid;border-left:1px solid;border-right:1px solid;">
						'.$ac1['toPlace'].' '.$to_state.' '.$ac1['toPincode'].'
					</td>
				</tr>
		</table>
		<table style="width:100%;margin-top:10px;border-collapse:collapse;">
			<tr>
				<td style=" vertical-align:top; text-align:left;height:40px;vertical-align:bottom;" colspan="5"><strong>3. Goods Details</strong></td>
			</tr>
			<tr>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-bottom:1px solid;border-left:1px solid;border-right:1px solid;border-top:1px solid;width:20%;">
					<strong>HSN Code</strong>
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-bottom:1px solid;border-left:1px solid;border-right:1px solid;border-top:1px solid;width:20%;">
					<strong>Product Descripition</strong>
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-bottom:1px solid;border-left:1px solid;border-right:1px solid;border-top:1px solid;width:20%;">
					<strong>Quantity</strong>
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-bottom:1px solid;border-left:1px solid;border-right:1px solid;border-top:1px solid;width:20%;">
					<strong>Taxable Amount Rs</strong>
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-bottom:1px solid;border-left:1px solid;border-right:1px solid;border-top:1px solid;width:20%;">
					<strong>Tax Rate (C+S+I+Cess+Cess Non.Advol)</strong>
				</td>
			</tr>';
			$f=$ac1['itemList'];
			for ($i = 0; $i < count($f); $i++) {
			$html .='<tr>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-bottom:1px solid;border-left:1px solid;border-right:1px solid;border-top:1px solid;">
					'.$f[$i]['hsnCode'].'
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-bottom:1px solid;border-left:1px solid;border-right:1px solid;border-top:1px solid;">
					'.$f[$i]['productName'].'

				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-bottom:1px solid;border-left:1px solid;border-right:1px solid;border-top:1px solid;">
					'.$f[$i]['quantity'].'  '.$f[$i]['qtyUnit'].'
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-bottom:1px solid;border-left:1px solid;border-right:1px solid;border-top:1px solid;">
					'.$f[$i]['taxableAmount'].'
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;border-bottom:1px solid;border-left:1px solid;border-right:1px solid;border-top:1px solid;">
					'.$f[$i]['cgstRate'].' + '.$f[$i]['sgstRate'].' + '.$f[$i]['igstRate'].' + '.$f[$i]['cessRate'].' + '.$f[$i]['cessNonAdvol'].'
				</td>
				
			</tr>';
		}
		$html .='</table>
		<table style="width:100%;margin-top:10px;border-collapse:collapse;">
			<tr>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:12.5;">
					Tot. Taxable Amt	
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:12.5;">
						₹ '.$ac1['totalValue'].'
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:12.5;">
						CGST Amt
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:12.5;">
						₹ '.$ac1['cgstValue'].'
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:12.5;">
						SGST Amt
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:12.5;">
						₹ '.$ac1['sgstValue'].'
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:12.5;">
						IGST Amt 
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:12.5;">
						₹ '.$ac1['igstValue'].'
				</td>
			</tr>
			<tr>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:12.5;border-bottom:1px solid;">
					CESS Amt	
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:12.5;border-bottom:1px solid;">
						₹ '.$ac1['cessValue'].'
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:12.5;border-bottom:1px solid;">
						CESS Non.Advol Amt
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:12.5;border-bottom:1px solid;">
						₹ '.$ac1['cessNonAdvolValue'].'
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:12.5;border-bottom:1px solid;">
						Other Amt
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:12.5;border-bottom:1px solid;">
						₹ '.$ac1['otherValue'].'
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:12.5;border-bottom:1px solid;">
						Total Inv.Amt 
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:12.5;border-bottom:1px solid;">
						₹ '.$ac1['totInvValue'].'
				</td>
			</tr>
		</table>
		<table style="width:100%;margin-top:10px;border-collapse:collapse;">
			<tr>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;height:40px;vertical-align:bottom;" colspan="4">
						<strong>4. Transportation Details</strong>
				</td>
			</tr>
			<tr>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:25%;border-bottom:1px solid;">
						Transporter ID & Name
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:25%;border-bottom:1px solid;">
						'.$ac1['transporterName'].' & '.$ac1['transporterId'].'
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:25%;border-bottom:1px solid;">
						Transporter Doc. No & Date
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:25%;border-bottom:1px solid;">
						'.$ac1['transDocNo'].' & '.$ac1['transDocDate'].'
				</td>
			</tr>
		</table>
		<table style="width:100%;margin-top:10px;border-collapse:collapse;">
			<tr>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;height:40px;vertical-align:bottom;" colspan="7">
						<strong>5. Vehicle Details</strong>
				</td>
			</tr>
			<tr>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:14.28%;border-bottom:1px solid;border-top:1px solid;border-left:1px solid;border-right:1px solid;height:40px;">
						Mode
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:14.28%;border-bottom:1px solid;border-top:1px solid;border-left:1px solid;border-right:1px solid;">
						Vehicle / Trans Doc No & Dt.
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:14.28%;border-bottom:1px solid;border-top:1px solid;border-left:1px solid;border-right:1px solid;">
						From
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:14.28%;border-bottom:1px solid;border-top:1px solid;border-left:1px solid;border-right:1px solid;">
						Entered Date
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:14.28%;border-bottom:1px solid;border-top:1px solid;border-left:1px solid;border-right:1px solid;">
						Entered By
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:14.28%;border-bottom:1px solid;border-top:1px solid;border-left:1px solid;border-right:1px solid;">
						CEWB No. (If any)
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:14.28%;border-bottom:1px solid;border-top:1px solid;border-left:1px solid;border-right:1px solid;">
						Multi Veh.Info (If any)
				</td>

			</tr>';
			$trans=$ac1['VehiclListDetails'];
for ($p = 0; $p < count($trans); $p++) {
	$trans_mode_id=$trans[$p]['transMode'];
	$set_trans_mode="select eway_bill_transport_type from eway_transport_mode as comp where gst_code='".$trans_mode_id."'";
	$settrans_mode=brp_mysqli_fetch_assoc($dbcon->query($set_trans_mode));

	$set_gs="select state_name from state_mst as comp where gst_state_code='".$trans[$p]['fromState']."'";
	$settransgs=brp_mysqli_fetch_assoc($dbcon->query($set_gs));
			$html.='<tr >
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:14.28%;border-bottom:1px solid;border-top:1px solid;border-left:1px solid;border-right:1px solid;height:40px;">
						'.$settrans_mode['eway_bill_transport_type'].'
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:14.28%;border-bottom:1px solid;border-top:1px solid;border-left:1px solid;border-right:1px solid;">
						'.$trans[$p]['vehicleNo'].' '.$trans[$p]['transDocDate'].'
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:14.28%;border-bottom:1px solid;border-top:1px solid;border-left:1px solid;border-right:1px solid;">
						'.$trans[$p]['fromPlace'].' '.$settransgs['state_name'].' 
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:14.28%;border-bottom:1px solid;border-top:1px solid;border-left:1px solid;border-right:1px solid;">
						'.$trans[$p]['enteredDate'].'
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:14.28%;border-bottom:1px solid;border-top:1px solid;border-left:1px solid;border-right:1px solid;">
						'.$trans[$p]['userGSTINTransin'].'
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:14.28%;border-bottom:1px solid;border-top:1px solid;border-left:1px solid;border-right:1px solid;">
						'.$trans[$p]['ewbNo'].'
				</td>
				<td  style=" vertical-align:top; text-align:left;font-size: 13px !important;width:14.28%;border-bottom:1px solid;border-top:1px solid;border-left:1px solid;border-right:1px solid;">
						
				</td>

			</tr>';
}
		$html.='</table>
		<table style="width:100%;margin-top:10px;border-collapse:collapse;">
			<tr>
				<td  style=" vertical-align:top; text-align:center;height:40px;vertical-align:bottom;" >
						<barcode code="'.$ac1['ewbNo'].'" type="EAN128C" /></br>
						<div>'.$ac1['ewbNo'].'</div>
						
				</td>
			</tr>
		</table>
			
		</div>';
		
		$html.='</body>
		</html>';

		//echo $html;exit;
		// $get = "hello";
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','10','10','1','1');
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
		$mpdf->nbpgSuffix = '';
		//$mpdf->SetFooter('{PAGENO}{nbpg}');
		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='UTF-8';
		$mpdf->WriteHTML($html);
		//$mpdf->writeBarcode('978-1234-567-890');
		//$mpdf->WriteHTML($htmlw);
		$mpdf->Output();
				//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
		ob_clean();
		return 'Invoice Receipt '.$invoiceid.'.pdf';
	}


?>

 