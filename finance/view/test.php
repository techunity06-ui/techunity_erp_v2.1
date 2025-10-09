<?php 
//E-Way Bill API Testing 
 //error_reporting(E_ALL);
	session_start();
	$path = '../../';
	$include = '../../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");


$product_array=array(
			"Irn"=> '',
			"GSTIN"=> "29AAACW3775F000",
			"Year"=> 2021,
			"Month"=> 3,
			"SupplyType"=> "O",
			"SubType"=> "1",
			"DocType"=> "INV",
			"DocNo"=> "3333ssssAAAA1dd",
			"DocDate"=> "20220314",
			"SupGSTIN"=> "29AAACW3775F000",
			"SupName"=>"WEBTEL",
			"SupAdd1"=> "110-114 RATAN JYOTI",
			"SupAdd2"=> "BUILDING",
			"SupCity"=> "DELHI",
			"SupState"=> "05",
			"SupPincode"=> "262542",
			"RecGSTIN"=> "07AAACW3775F006",
			"RecName"=> "AMIT",
			"RecAdd1"=> "110-114 RATAN JYOTI",
			"RecAdd2"=> "",
			"Reccity"=> "Amritsar",
			"RecState"=> "05",
			"Recpincode"=> "263640",
			"TransMode"=> "1",
			"TransporterId"=> "05AAACN1283H1ZQ",
			"TransporterName"=> "abc",
			"TransDistance"=> "51",
			"TransDocNo"=> "444468",
			"TransDocDate"=> "20181008",
			"VehicleType"=> "R",
			"VehicleNo"=> "",
			"ProductName"=> "Paint",
			"ProductDesc"=> "Paint",
			"HSNCode"=> "8538",
			"Quantity"=> 100,
			"QtyUnit"=> "NOS",
			"TaxableValue"=> 10000,
			"TotalValue"=> 0,
			"SGSTRate"=> 6,
			"SGSTValue"=> 600,
			"CGSTRate"=> 6,
			"CGSTValue"=> 600,
			"IGSTRate"=> 0,
			"IGSTValue"=> 0,
			"CessRate"=> 0,
			"CessValue"=> 0,
			"EWBUserName"=> "29AAACW3775F000",
			"EWBPassword"=> "Admin!23..",
			"CessNonAdvol"=> 4006,
			"SubSupplyDesc"=> "TestDescription",
			"ShipFromStateCode"=> "05",
			"ShipToStateCode"=> "05",
			"TotalInvoiceValue"=> "0",
			"CessNonAdvolValue"=> 100,
			"OtherValue"=> 100,
			"dispatchFromGSTIN "=> "29AAACW3775F000",
			"dispatchFromTradeName"=> "WEBTEL",
			"ShipToGSTIN"=> "07AAACW3775F006",
			"ShipToTradeName"=> "xyz limited",
			"IsBillFromShipFromSame"=> "1",
			"IsBillToShipToSame"=> "1",
			"IsGSTINSEZ"=> 0
		); 
$product_array1=array();
array_push($product_array1,$product_array);
$post_data = array(
			"Push_Data_List"=>$product_array1,
			"Year"=>"2021",
			"Month"=>"03",
			"EFUserName"=>"29AAACW3775F000",
			 "EFPassword"=>"Admin!23..",
			 "CDKey"=>"1000687"
		); 
$post_data = json_encode($post_data);
//print_r($post_data);
		//$curl = curl_init($post_data);
$curl = curl_init();
curl_setopt_array($curl, array(
		  CURLOPT_URL => "http://ip.webtel.in/ewaygsp2/Sandbox/EWayBill/v1.3/GenEWB",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $post_data,
		  CURLOPT_HTTPHEADER => array(
		    'Content-Type: application/json'
		  ),
	   
		));
		//print_r($curl);
		$response = curl_exec($curl);
		$err = curl_error($curl);
$response1=json_decode($response);
		print_r($response1);
		echo $response1['EWayBill'];
		
		curl_close($curl);
?>

