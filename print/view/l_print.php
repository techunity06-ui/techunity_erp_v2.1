<?php session_start();
include("../../config/config.php");



		include("../../view/export/mpdf/mpdf.php");
		
		// Include mPDF library

// Create mPDF object
$mpdf = new \Mpdf\Mpdf();

// Initialize total and page variables
$total = 0;
$page = 1;

// Loop through items and add to PDF
for ($i = 1; $i <= 30; $i++) {
    // Add item to PDF
    $mpdf->WriteHTML('Item ' . $i . ': $10<br>');

    // Add item price to total
    $total += 10;

    // Check if the current page is full
    if ($mpdf->y > $mpdf->h - 50) {
        // If the page is full, close it and output the total for that page
        $mpdf->Close();
        echo 'Total for page ' . $page . ': $' . $total . '<br>';

        // Start a new page and reset the total and page
        $mpdf->AddPage();
        $total = 0;
        $page++;
    }
}

// Close the last page and output the total for that page
$mpdf->Close();
echo 'Total for page ' . $page . ': $' . $total . '<br>';

// Output the final PDF document
$mpdf->Output();

?>