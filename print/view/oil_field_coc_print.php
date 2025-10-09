<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");   

// $bulkAccessArray = canCheckPermissionAccess($dbcon, [
//         FINANCE_PROFORMA_INVOICE_PRINT,
//         QUOTATION_SLUG_PRINT
//     ]);

/*if(!in_array(FINANCE_PROFORMA_INVOICE_PRINT,$bulkAccessArray)){
  header("Location: ".DOMAIN."permission_access");
}
        
if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}*/


$type='pdf';
if(strtolower($type) == 'pdf') {
    $work_order_id=$dbcon->real_escape_string($_REQUEST['id']);

    $query="SELECT work.*,so_trn.*, pro.product_name,rp.jobwork_type,l.l_name,so.sales_order_date,so.delivery_date,rp.rp_id FROM tbl_set_main_process as work 
    LEFT JOIN product_mst as pro ON pro.product_id = work.product_id
    LEFT JOIN tbl_sales_ordertrn as so_trn ON so_trn.sales_ordertrn_id = work.sales_order_trn_id
    LEFT JOIN tbl_sales_order as so ON so.sales_order_id = so_trn.sales_order_id
    LEFT JOIN tbl_request_product as rp ON rp.sp_id = work.sp_id and main_request = 1
    LEFT JOIN tbl_ledger as l ON l.l_id = work.vendor_id

    WHERE work.sp_status IN (0,2) and work.sp_id=".$work_order_id;
    $rel=mysqli_fetch_assoc($dbcon->query($query));
   
    $wo_type = "";
    if($rel['jobwork_type'] == '0'){
        $wo_type = "MANUFACTURING";
    }else{
        $wo_type = "JOBWORK"; 
    }
    
    // echo "<pre>";print_r($query);die(); 
    if(!$rel){
        header("Location: ".ROOT.PRODUCTION_ROOT."work_order");
    }

    $set="SELECT comp.*,state.state_name,state.gst_state_code FROM tbl_company as comp LEFT JOIN state_mst as state on comp.stateid=state.stateid WHERE company_id=".$_SESSION['company_id'];
    $set_head=mysqli_fetch_assoc($dbcon->query($set));  

    $userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
    WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
    $userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
    $userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
    $userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';

    /* Check Discount is On or off Start */
    if($set_head['show_disc']=='1'){
        $colspan=5;
        $dynamicwidth=40;
    }else{
        $colspan=6;
        $dynamicwidth=46;
    }
    /* Check Discount is On or off End */

    $header ='<div><img src="'.DOMAIN_F.LOGO.$set_head['logo'].'" style="width: 6in"/></div>';
    $footer = '';

    $html='<html>
    <head>          
    <title>COC - '.$rel['po_req_no'].'</title>
    <style type="text/css">
    .nextpage
    {
        page-break-after: always;
    }
    table{
        border-collapse:collapse;
        width:100%;
    }

    table tr, th, td{
        border:1px solid black !important;
        /*page-break-inside:avoid;*/
    }
    .quot_annex_content_div table tr,td{
        padding:5px;
    }
    .blueHeading {
        color: #365f91;
    }
    </style>
    </head>
    <body>
    <!--Show Logo in other pages-->
    <htmlpageheader name="otherpages" style="display:none">
    <div style="text-align:center">'.$header.'</div>
    </htmlpageheader>
    <htmlpagefooter name="otherpages_footer" style="display:none">
    <div style="text-align:center">'.$footer.'</div>
    </htmlpagefooter>
    <sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
    <div>
    <table style="font-size:14px;border-collapse: collapse;width:100%; " cellpadding="3" cellspacing="3">
    <tr style="border: none;">
    <td style="border: none;"><br><br></td>
    </tr>
    <tr style="border: none;">
    <td style="border: none; font-size: 18px; font-weight: bold; text-align: center; text-decoration: underline;">CERTIFICATE OF CONFORMITY</td>
    </tr>
    <tr style="border: none;">
    <td style="border: none; font-weight: bold; text-align: center; font-size: 13px;">This is to certify that the products and services supplied on the below referenced Purchase Order and our Work Order have been manufactured and serviced as per customer requirements in accordance with our Quality Management System.</td>
    </tr>
    </table>
    <table style="border: 1px soild black; font-size:14px; border-collapse: collapse; width:100%; margin-top: 30px;" cellpadding="3" cellspacing="3">
    <tbody>
    <tr>
    <td width="30%" style="font-weight: bold;">Customer :</td>
    <td width="70%" style="font-weight: bold;">'.$rel['l_name'].'</td>
    </tr>
    <tr>
    <td width="30%" style="font-weight: bold;">Purchase Order :</td>
    <td width="70%" style="font-weight: bold;">'.$rel['po_no'].'</td>
    </tr>
    <tr>
    <td width="30%" style="font-weight: bold;">Work Order :</td>
    <td width="70%" style="font-weight: bold;">'.$rel['po_req_no'].'</td>
    </tr>
    <tr>
    <td width="30%" style="font-weight: bold;">Date of Issue :</td>
    <td width="70%" style="font-weight: bold;">'.date('d-m-Y',strtotime($rel['po_req_date'])).'</td>
    </tr>
    <tr>
    <td width="30%" style="font-weight: bold;">Desciptiion :</td>
    <td width="70%" style="font-weight: bold;">'.$rel['product_name'].'</td>
    </tr>
    <tr>
    <td width="30%" style="font-weight: bold;">Asset/Serial No :</td>
    <td width="70%" style="font-weight: bold;">'.$rel['customer_asset_serial'].'</td>
    </tr>
    <tr>
    <td width="30%" style="font-weight: bold;">Heat No :</td>
    <td width="70%" style="font-weight: bold;">'.$rel['customer_req_heat'].'</td>
    </tr>
    <tr>
    <td width="30%" style="font-weight: bold;">Quantity :</td>
    <td width="70%" style="font-weight: bold;">'.$rel['rp_req_qty'].'</td>
    </tr>
    <tr>
    <td width="30%" style="font-weight: bold; vertical-align: top;">Services :</td>
    <td width="70%" style="font-weight: bold;">'.$rel['description'].'</td>
    </tr>
    </tbody>
    </table>
    <div style="height: 200px;"></div>
    <table style="border: none; margin-top: 30px;">
    <tr style="border: none;">
    <td style="border: none; text-align: left; height: 80px; vertical-align: bottom;" width="50%"><strong>FINAL INSPECTED BY:------------------------------(SIGN/DATE)</strong></td>
    <td style="border: none; text-align: right; height: 80px; vertical-align: bottom;" width="50%"><strong>APPROVED BY:------------------------------(SIGN/DATE)</strong></td>
    </tr>
    <tr style="border: none;">
    <td colspan="2" style="border: none; text-align: center;">ASSURING OUR BEST SERVICES AT ALL TIMES</td>
    </tr>
    </table>';

    $html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
    </body>
    </html>';
// echo $header;
// echo $html;exit;
    ob_end_clean();
    include("../../view/export/mpdf/mpdf.php");
    $mpdf=new mPDF('','A4','0','calibri','10','10','30','2','1','1');
//    $mdf->SetFont('ProximaNova');
    $mpdf->defaultheaderfontsize = 10; /* in pts */
    $mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
    $mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
    $mpdf->defaultfooterfontsize = 10; /* in pts */
    $mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
    $mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
    $mpdf->SetHTMLHeader($header);
    $mpdf->SetHTMLFooter($footer);
    $mpdf->pagenumPrefix = ' ';
    $mpdf->pagenumSuffix = ' / ';
    $mpdf->nbpgPrefix = ' ';
    $mpdf->nbpgSuffix = ' pages';
    $mpdf->SetFooter('{PAGENO}{nbpg}||');
    $mpdf->SetWatermarkText();
    $mpdf->showWatermarkText = true;
    $mpdf->allow_charset_conversion=true;
    $mpdf->charset_in='UTF-8';
    $mpdf->WriteHTML($html);
    $mpdf->Output();
    //$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
    ob_clean();
    return 'COC '.$rel['po_req_no'].'.pdf';
} 
?>