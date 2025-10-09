<?php
$url = 'https://api.elasticemail.com/v2/email/send';

try{
        $post = array('from' => 'sharma@deltacardz.com',
		'fromName' => 'delta',
		'apikey' => '16AE17121502D4E2D23040D3E87BF5BD7774C58ECEB933E7CAC44C813158D175F18173202014E544A52D383B46E9E781',
		
		'subject' => 'Your Subject',
		'to' => 'dimple.metr@gmail.com',
		'bodyHtml' => '<h1>Html Body</h1>',
		'bodyText' => 'Text Body',
		'isTransactional' => false);
		
		$ch = curl_init();
		curl_setopt_array($ch, array(
                    CURLOPT_URL => $url,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $post,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => false,
                    CURLOPT_SSL_VERIFYPEER => false
                ));
		
                $result=curl_exec($ch);
                curl_close ($ch);
	echo $result;	
}
catch(Exception $ex){
	echo $ex->getMessage();
}
?>