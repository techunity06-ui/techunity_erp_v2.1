<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.aajjo.com/api/cl/getleads',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "StartDate": "2023-09-01 22:00:00",
    "EndDate": "2023-09-08 23:59:59"
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Basic sales@austarindia.com:C2D7A89F-2D9B-44F2-9866-65819A4E725F',
    'Content-Type: application/json'
  ),
));
$response = curl_exec($curl);
curl_close($curl);
echo $response;
?>