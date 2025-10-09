<?php

function get_indiamart_data($dbcon,$sdate,$ldate,$api_key,$mob_no){
	
	$url="https://mapi.indiamart.com/wservce/crm/crmListing/v2/?glusr_crm_key=".$api_key."&start_time=".$sdate."&end_time=".$ldate;
	
	// $url="http://mapi.indiamart.com/wservce/enquiry/listing/GLUSR_MOBILE/".$mob_no."/GLUSR_MOBILE_KEY/".$api_key."/Start_Time/".$sdate."/End_Time/".$ldate."/";
	
	$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
        CURLOPT_SSL_VERIFYPEER => false,        //
		)); 
	$output=curl_exec($ch);
	// $err     = curl_errno($ch);
 //    $errmsg  = curl_error($ch) ;
	// //curl_close($ch);
	// //var_dump($output);
	// print_r($output);
	// print_r($err);
	// print_r($errmsg);
	//print_r($output['Error_Message']);
	return $output;
	// return $url;
}
function get_trade_india_data($dbcon,$sdate,$ldate,$api_key,$user_id,$profile_id){
	
	//$url="http://mapi.indiamart.com/wservce/enquiry/listing/GLUSR_MOBILE/".$mob_no."/GLUSR_MOBILE_KEY/".$api_key."/Start_Time/".$sdate."/End_Time/".$ldate."/";
	
	//$url="https://www.tradeindia.com/utils/my_inquiry.html?userid=".$user_id."&profile_id=".$profile_id."&key=".$api_key."&from_date=".$sdate."&to_date=".$ldate;
	$url="https://www.tradeindia.com/utils/my_buy_leads.html?userid=".$user_id."&profile_id=".$profile_id."&key=".$api_key."&from_date=".$sdate."&to_date=".$ldate;
	/* //var_dump($url);
	
	  $ch1 = curl_init();
		curl_setopt_array($ch1, array(
			CURLOPT_URL => $url2,
			CURLOPT_RETURNTRANSFER => true,
			
			
		));  
		print_r($ch1);
	//$output=curl_exec($ch1);
	$output=curl_exec($url2);
	return $output;
	//echo $url1; */
	
	//$url = "https://example.com:4433/deviceservice/authorize?login=query"; // URL JSON
$ch = curl_init($url);
    //echo $ch; //write Resource id # 2
    if( $ch )
    {
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $json = curl_exec( $ch );
       // $json = json_decode($json);
		//print_r($json);
    } else {
        echo 'nothing';
    }

	return $json;
}
?>