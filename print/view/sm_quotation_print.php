<?php

$quotation_id = $_REQUEST['id'];
if (!empty($quotation_id)) {
    session_start();
    include("../../config/config.php");
    include("../../config/session.php");
    include("../../include/function_database_query.php");
    include_once(COMMON_FUNCTION_PATH . "common_functions.php");
    $incPath = $path . 'include/';

    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
        QUOTATION_SLUG_PRINT,
    ]);

    if (!in_array(QUOTATION_SLUG_PRINT, $bulkAccessArray)) {
        header("Location: " . DOMAIN . "permission_access");
    }
    quotation_print($dbcon, $quotation_id, $save_file = "No");
}

function quotation_print($dbcon, $quotation_id, $save_file)
{
    // Your existing code here...

    // Construct the HTML content with the background image on the first page
    $imagePath = DOMAIN_F . LOGO . 'sm_enterprise_1stpg.jpg';
    $html = '<html>
    <head>                    
        <title>Quotation - ' . $rel['quotation_no'] . '</title>
        <style type="text/css">
            @page:first {
                background-image: url("' . $imagePath . '");
                background-size: cover;
                background-position: center;
            }
        </style>
    </head>
    <body style="margin:0px">
        <div style="background-image: url(' . $imagePath . ');background-repeat: no-repeat;background-size: cover;width: 100%;height: 100%; position: relative">
           <h3 style="z-index: 99; position: absolute;top: 50%;left: 50%;">SAHAJ</h3>
            </div>
    </body>
    </html>';

   /* echo $html;
    die;*/

    // Generate PDF using mPDF
    ob_end_clean();
    $file_name = $rel['quotation_no'] . '.pdf';
    $file_name = str_ireplace("/", "_", $file_name);
    if ($save_file == "No") {
        include("../../view/export/mpdf/mpdf.php");
    } else {
        include("../../../view/export/mpdf/mpdf.php");
    }

    $mpdf = new mPDF('', 'A4', '0', 'proximanova', '10', '10', '0', '0', '1', '1');
    $mpdf->defaultheaderfontsize = 10;
    $mpdf->defaultheaderfontstyle = 'B';
    $mpdf->defaultheaderline = 1;
    $mpdf->defaultfooterfontsize = 10;
    $mpdf->defaultfooterfontstyle = 'B';
    $mpdf->defaultfooterline = 1;
    $mpdf->SetHTMLHeader($header1);
    $mpdf->SetHTMLFooter($footer1);
    $mpdf->pagenumPrefix = ' ';
    $mpdf->pagenumSuffix = ' / ';
    $mpdf->nbpgPrefix = ' ';
    $mpdf->nbpgSuffix = ' pages';
    $mpdf->SetFooter('{PAGENO}{nbpg}');
    $mpdf->SetWatermarkText();
    $mpdf->showWatermarkText = true;
    $mpdf->allow_charset_conversion = true;
    $mpdf->charset_in = 'UTF-8';
    $mpdf->WriteHTML($html);
    if ($save_file == "No") {
        $mpdf->Output();
    } else {
        $mpdf->Output('../../../view/upload/mail_attach/' . $file_name, 'f');
    }
    ob_clean();
    return $file_name;
}
?>
