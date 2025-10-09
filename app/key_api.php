<?php 

 
function get_key_api_data($cust_code,$cust_key){
	
	//$url="http://mapi.indiamart.com/wservce/enquiry/listing/GLUSR_MOBILE/".$mob_no."/GLUSR_MOBILE_KEY/".$api_key."/Start_Time/".$sdate."/End_Time/".$ldate."/";
	$url=KEY_URL."/check.php/?custcode=".$cust_code."&custkey=".$cust_key."";
	
	
	  $ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false
		));  
		
		
	$output=curl_exec($ch);
	
	return $output;
	//echo $output;
}

?>