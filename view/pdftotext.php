<?php 

$pdfFilePath = 'C:\Users\hr\Downloads\AMBICA_HEATING_POINT_#0046_21-22.pdf';

// Command to extract text from PDF file
 $cmd = "/usr/bin/pdftotext -layout ".$pdfFilePath." -";

// Execute command and store output in a variable
echo $text = shell_exec($cmd);

// Output extracted text
echo $text;

?>