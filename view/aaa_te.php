<?php
$response="[{\"ErrorMessage\":null,\"GSTIN\":\"24AABCC6052G1Z1\",\"DocNo\":null,\"Date\":\"15/02/2023 05:10:48 PM\",\"Old_EWayBill\":0,\"EWayBill\":671520490875,\"ValidUpTo\":\"\",\"IsSuccess\":\"true\",\"ErrorCode\":null,\"VehicleNo\":null,\"SupplierState\":null,\"EWBDetails\":\"{\\\"cessNonAdvolValue\\\":0.0,\\\"otherValue\\\":0.0,\\\"transactionType\\\":1.0,\\\"actToStateCode\\\":8.0,\\\"actFromStateCode\\\":24.0,\\\"vehicleType\\\":\\\"R\\\",\\\"rejectStatus\\\":\\\"N\\\",\\\"extendedTimes\\\":0.0,\\\"totInvValue\\\":2360.0,\\\"validUpto\\\":\\\"07/02/2023 11:59:00 PM\\\",\\\"resstatus\\\":\\\"1\\\",\\\"errorCodes\\\":null,\\\"errorDesc\\\":null,\\\"actualDist\\\":654.0,\\\"cessValue\\\":0.0,\\\"cgstValue\\\":0.0,\\\"docDate\\\":\\\"03/02/2023\\\",\\\"docNo\\\":\\\"TAX/302/2019-20\\\",\\\"docType\\\":\\\"INV\\\",\\\"fromAddr1\\\":\\\"G 21 A City Center, S.C. Road\\\",\\\"fromAddr2\\\":\\\"\\\",\\\"fromGstin\\\":\\\"24AABCC6052G1Z1\\\",\\\"fromPincode\\\":380051.0,\\\"fromPlace\\\":\\\"Ahmedabad\\\",\\\"fromStateCode\\\":24.0,\\\"fromTrdName\\\":\\\"FLOWJET VALVES PVT. LTD.\\\",\\\"genMode\\\":\\\"API\\\",\\\"igstValue\\\":360.0,\\\"noValidDays\\\":4.0,\\\"sgstValue\\\":0.0,\\\"status\\\":\\\"CNL\\\",\\\"subSupplyType\\\":\\\"1 \\\",\\\"supplyType\\\":\\\"O\\\",\\\"toAddr1\\\":\\\"G 21 A City Center, S.C. Road\\\",\\\"toAddr2\\\":\\\"\\\",\\\"toGstin\\\":\\\"08AAAFF7486M1ZU\\\",\\\"toPincode\\\":302001.0,\\\"toPlace\\\":\\\"Jaipur\\\",\\\"toStateCode\\\":8.0,\\\"toTrdName\\\":\\\"FLUIDCON ENGINEERS\\\",\\\"totalValue\\\":2000.0,\\\"transDocDate\\\":null,\\\"transDocNo\\\":null,\\\"transMode\\\":null,\\\"transporterId\\\":\\\"\\\",\\\"transporterName\\\":\\\"1\\\",\\\"userGstin\\\":\\\"24AABCC6052G1Z1\\\",\\\"ewbNo\\\":671520490875,\\\"ewayBillDate\\\":\\\"03/02/2023 02:04:00 PM\\\",\\\"itemList\\\":[{\\\"cessNonAdvol\\\":0.0,\\\"productDesc\\\":\\\"\\\",\\\"cessRate\\\":0.0,\\\"cgstRate\\\":0.0,\\\"hsnCode\\\":84818030.0,\\\"igstRate\\\":18.0,\\\"productId\\\":0.0,\\\"productName\\\":\\\"IC CF8 1PCS BALL VALVE S/E-15MM\\\",\\\"qtyUnit\\\":\\\"NOS\\\",\\\"quantity\\\":2.0,\\\"sgstRate\\\":0.0,\\\"taxableAmount\\\":2000.0,\\\"itemNo\\\":1.0,\\\"cessAdvol\\\":0.0}],\\\"VehiclListDetails\\\":[{\\\"groupNo\\\":\\\"0\\\",\\\"enteredDate\\\":\\\"03/02/2023 02:04:00 PM\\\",\\\"ewbNo\\\":0,\\\"updMode\\\":\\\"API\\\",\\\"vehicleNo\\\":\\\"GJ01HX5721\\\",\\\"fromPlace\\\":\\\"Ahmedabad\\\",\\\"fromState\\\":24.0,\\\"tripshtNo\\\":0.0,\\\"userGSTINTransin\\\":\\\"24AABCC6052G1Z1\\\",\\\"transMode\\\":\\\"1\\\",\\\"transDocNo\\\":\\\"LR123\\\",\\\"transDocDate\\\":\\\"03/02/2023\\\"}]}\",\"Alert\":null}]";

$jsonobj = json_decode($response);

foreach ($jsonobj as $obj) {
    // Print the properties of the object
   //echo "ErrorMessage: " . $obj->ErrorMessage . "\n";
  // echo "GSTIN: " . $obj->GSTIN . "\n";
  //  echo "DocNo: " . $obj->DocNo . "\n";
    //echo "EWBDetails: " . $obj->EWBDetails->cessNonAdvolValue . "\n";
   
    $ac=$obj->EWBDetails;
    $ac1=$jsonobj = json_decode($ac,true);
    // var_dump($ac1['ewbNo']);
    // var_dump($ac1['itemList']);
     var_dump($ac1['VehiclListDetails']);
    
}

$f=$ac1['itemList'];
for ($i = 0; $i < count($f); $i++) {
	//var_dump($f[$i]['productName']);
}



//$array = get_object_vars($ac1);
//print_r($array);
//echo $array['fromTrdName'];

/*foreach ($array as $obj) {
		echo "cessNonAdvolValue: " . $obj->cessNonAdvolValue . "\n";
	}*/



//$arr = get_object_vars($jsonobj);
//print_r($jsonobj);
// echo '<pre>'; print_r($array); echo '</pre>';
	//$jsonobj1 = json_decode($jsonobj,true);
	//print_r($jsonobj[0]['stdClass Object']);
	// $item=$jsonobj[0]['EWBDetails'];
	//echo $item['EWayBill'];
	//$jsoitem = json_decode($item,true);
	//print_r($jsoitem);
	/*for ($i = 0; $i < count($jsonobj1); $i++) {

	}*/
?>