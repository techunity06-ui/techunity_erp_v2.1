<?php
  $ipv4_address = $_SERVER['REMOTE_ADDR'];
  echo "The IPv4 address of the client is: $ipv4_address";
 ?>
 <br>
 <?php
  $server_ip = $_SERVER['SERVER_ADDR'];
echo "The IPv4 address of the server is: " . $server_ip;
?>

<br>

<?php

$client_ip = $_SERVER['REMOTE_ADDR'];
  $ipv4_address = filter_var($client_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
  if ($ipv4_address !== false) {
    echo "The IPv4 address of the client is: $ipv4_address";
  } else {
    echo "Unable to retrieve the IPv4 address of the client";
  }
 ?>
 <br>
 <?php
  $ipaddress = getenv("REMOTE_ADDR") ;
 Echo "Your IP Address is " . $ipaddress;
?>
<br>
<?php
  
// Declaring a variable to hold the IP
// address getHostName() gets the name
// of the local machine getHostByName()
// gets the corresponding IP
$localIP = getHostByName(getHostName());
  
// Displaying the address 
echo $localIP;
  
?>

<br>
<?php
$output = array();
exec('vol c:', $output);
$serial_number = '';
foreach($output as $line){
  $pos = strpos($line, 'Serial Number');
  if($pos !== false){
    $serial_number = trim(substr($line, $pos + strlen('Serial Number')));
    break;
  }
}
echo "Hard disk serial number: " . $serial_number;

?>
