<?php 
//E-Way Bill API Testing 
 //error_reporting(E_ALL);
	session_start();
	$path = '../../';
	$include = '../../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");



$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://EinvSandbox.webtel.in/v1.03/GenIRN2',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "CDKey": "1000687",
    "EInvUserName": "29AAACW3775F000",
    "EInvPassword": "Admin!23..",
    "EFUserName": "29AAACW3775F000",
    "EFPassword": "Admin!23..",
    "GSTIN": "29AAACW3775F000",
    "GetQRImg": "1",
    "GetSignedInvoice": "1",
    "TranDtls": {
        "SupTyp": "B2B",
        "RegRev": "Y",
        "EcmGstin": null,
        "IgstOnIntra": "N"
    },
    "DocDtls": {
        "Typ": "INV",
        "No": "DOC1/001ZSVVAXA2",
        "Dt": "28/03/2022"
    },
    "SellerDtls": {
        "Gstin": "29AAACW3775F000",
        "LglNm": "NIC company pvt ltd",
        "TrdNm": "NIC Industries",
        "Addr1": "5th block, kuvempu layout",
        "Addr2": "kuvempu layout",
        "Loc": "GANDHINAGAR",
        "Pin": 562160,
        "Stcd": "29",
        "Ph": "9000000000",
        "Em": "abc@gmail.com"
    },
    "BuyerDtls": {
        "Gstin": "29AWGPV7107B1Z1",
        "LglNm": "XYZ company pvt ltd",
        "TrdNm": "XYZ Industries",
        "Pos": "12",
        "Addr1": "7th block, kuvempu layout",
        "Addr2": "kuvempu layout",
        "Loc": "GANDHINAGAR",
        "Pin": 562160,
        "Stcd": "29",
        "Ph": "91111111111",
        "Em": "xyz@yahoo.com"
    },
    "DispDtls": {
        "Nm": "ABC company pvt ltd",
        "Addr1": "7th block, kuvempu layout",
        "Addr2": "kuvempu layout",
        "Loc": "Banagalore",
        "Pin": 562160,
        "Stcd": "29"
    },
    "ShipDtls": {
        "Gstin": "29AWGPV7107B1Z1",
        "LglNm": "CBE company pvt ltd",
        "TrdNm": "kuvempu layout",
        "Addr1": "7th block, kuvempu layout",
        "Addr2": "kuvempu layout",
        "Loc": "Banagalore",
        "Pin": 562160,
        "Stcd": "29"
    },
    "ItemList": [
        {
            "SlNo": "1",
            "PrdDesc": "Rice",
            "IsServc": "N",
            "HsnCd": "1001",
            "Barcde": "123456",
            "Qty": 100,
            "FreeQty": 10,
            "Unit": "BAG",
            "UnitPrice": 100,
            "TotAmt": 10000,
            "Discount": 0,
            "PreTaxVal": 1,
            "AssAmt": 10000,
            "GstRt": 12.0,
            "IgstAmt": 1200,
            "CgstAmt": 0,
            "SgstAmt": 0,
            "CesRt": 0,
            "CesAmt": 0,
            "CesNonAdvlAmt": 0,
            "StateCesRt": 0,
            "StateCesAmt": 0,
            "StateCesNonAdvlAmt": 0,
            "OthChrg": 0,
            "TotItemVal": 11200,
            "OrdLineRef": "3256",
            "OrgCntry": "AG",
            "PrdSlNo": "12345",
        },{
            "SlNo": "2",
            "PrdDesc": "Rice1",
            "IsServc": "N",
            "HsnCd": "1001",
            "Barcde": "123456",
            "Qty": 100,
            "FreeQty": 10,
            "Unit": "BAG",
            "UnitPrice": 100,
            "TotAmt": 10000,
            "Discount": 0,
            "PreTaxVal": 1,
            "AssAmt": 10000,
            "GstRt": 12.0,
            "IgstAmt": 1200,
            "CgstAmt": 0,
            "SgstAmt": 0,
            "CesRt": 0,
            "CesAmt": 0,
            "CesNonAdvlAmt": 0,
            "StateCesRt": 0,
            "StateCesAmt": 0,
            "StateCesNonAdvlAmt": 0,
            "OthChrg": 0,
            "TotItemVal": 11200,
            "OrdLineRef": "3256",
            "OrgCntry": "AG",
            "PrdSlNo": "12345",
        }
    ],
    "ValDtls": {
        "AssVal": 20000,
        "CgstVal": 0,
        "SgstVal": 0,
        "IgstVal": 2400,
        "CesVal": 0,
        "StCesVal": 0,
        "Discount": 0,
        "OthChrg": 0,
        "RndOffAmt": 0,
        "TotInvVal": 22400,
        "TotInvValFc": 22400
    }
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

?>

