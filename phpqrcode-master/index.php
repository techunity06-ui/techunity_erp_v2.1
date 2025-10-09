<?php    
$data = "5555";
require_once('qrlib.php');

//$data = "https://www.example.com"; // Change this to your QR code data

$qrCode = QRcode::png($data);
$qrCodeData = implode("\n", $qrCode);
//var_dump($qrCodeData)
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>
</head>
<body>
    <?phpecho $qrCodeData ?>
</body>
</html>