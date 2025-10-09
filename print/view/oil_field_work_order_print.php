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

    $header ='<table style="width: 100%; border: none;">
    <tbody>
    <tr style="border: none;">
    <td width="20%" style="border: none; text-align: left;"><img src="'.DOMAIN_F.LOGO.'Oil Field-2.png"/></td>
    <td width="80%" style="border: none; text-align: center; font-weight: bold;"><h1>PROCESS CONTROL ROUTER</h1></td>
    </tr>
    </tbody>
    </table>';
    $footer = '<hr/>
    <table style="width: 100%; border: none;">
    <tbody>
    <tr style="border: none;">
    <td style="border: none; text-align: right; font-size: 10px;">FR/304-01 Issue 2 Rev 4</td>
    </tr>
    </tbody>
    </table>';

    $html='<html>
    <head>          
    <title>Work Order - '.$rel['po_req_no'].'</title>
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
        border:1px solid !important;
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
    <table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
    <tr style="border: none;">
    <td colspan="4" style="border: none;font-size: 18px; font-weight: bold; text-align: right;">'.$rel['po_req_no'].'</td>
    </tr>
    <tr>
    <td colspan="4" style="font-size: 18px; font-weight: bold; text-align: center;">CUSTOMER REQUIREMENT</td>
    </tr>
    <tr>
    <td style="width: 20%; font-weight: bold;">Type :</td>
    <td style="width: 40%;">'. $wo_type.'</td>
    <td style="width: 20%; font-weight: bold;">Material :</td>
    <td style="width: 20%;">'.$rel['customer_req_material'].'</td>
    </tr>
    <tr>
    <td style="font-weight: bold;">Customer Name :</td>
    <td>'.$rel['l_name'].'</td>
    <td style="font-weight: bold;">Garde :</td>
    <td>'.$rel['customer_req_grade'].'</td>
    </tr>
    <tr>
    <td style="font-weight: bold;">Item :</td>
    <td>'.$rel['product_name'].'</td>
    <td style="font-weight: bold;">Size :</td>
    <td>'.$rel['customer_req_size'].'</td>
    </tr>
    <tr>
    <td style="font-weight: bold;">Sales Quote :</td>
    <td>'.$rel['sales_order_no'].'</td>
    <td style="font-weight: bold;">ID :</td>
    <td>'.$rel['customer_req_id'].'</td>
    </tr>
    <tr>
    <td style="font-weight: bold; vertical-align: top;" rowspan="5">Service Description :</td>
    <td style="vertical-align: top;" rowspan="5">'.$rel['description'].'</td>
    <td style="font-weight: bold;">Length :</td>
    <td>'.$rel['customer_req_length'].'</td>
    </tr>
    <tr>
    <td style="font-weight: bold;">Heat# :</td>
    <td>'.$rel['customer_req_heat'].'</td>
    </tr>
    <tr>
    <td style="font-weight: bold;">COC :</td>
    <td>'.$rel['customer_req_coc'].'</td>
    </tr>
    <tr>
    <td style="font-weight: bold;">Customer Ref No :</td>
    <td>'.$rel['customer_ref_no'].'</td>
    </tr>
    <tr>
    <td style="font-weight: bold;">PCR Issued By :</td>
    <td><strong>Operations Engineer</strong></td>
    </tr>
    <tr>
    <td style="font-weight: bold;">Asset/Serial :</td>
    <td>'.$rel['customer_asset_serial'].'</td>
    <td style="font-weight: bold;">SIGN :</td>
    <td></td>
    </tr>
    <tr>
    <td style="font-weight: bold;">Quantity :</td>
    <td>'.$rel['rp_req_qty'].'</td>
    <td style="font-weight: bold;">DATE :</td>
    <td></td>
    </tr>
    <tr>
    <td style="font-weight: bold;">Order Date :</td>
    <td>'. date_format(date_create($rel['sales_order_date']),'d-M-Y').'</td>
    <td style="font-weight: bold;">PCR Reviewed By :</td>
    <td><strong>Quality Engineer</strong></td>
    </tr>
    <tr>
    <td style="font-weight: bold;">Required Date :</td>
    <td>'.date_format(date_create($rel['delivery_date']),'d-M-Y').'</td>
    <td style="font-weight: bold;">SIGN :</td>
    <td></td>
    </tr>
    <tr>
    <td style="font-weight: bold;">Bevel Spec :</td>
    <td>'.$rel['customer_bevel_spec'].'</td>
    <td style="font-weight: bold;">DATE :</td>
    <td></td>
    </tr>
    </table>
    <table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
    <thead style="border: 1px soild;">
    <tr style="border: 1px soild black;background-color: #d3d2d2;">
    <th width="5%" style="border: 1px soild; text-align:center;font-weight: bold;">OPS NO.</th>
    <th width="20%" style="border: 1px soild; text-align:center;font-weight: bold;">WC</th>
    <th width="35%" style="border: 1px soild; text-align:center;font-weight: bold;">DESCRIPTION</th>
    <th width="10%" style="border: 1px soild; text-align:center;font-weight: bold;">STD HRS</th>
    <th width="10%" style="border: 1px soild; text-align:center;font-weight: bold;">ACT HRS</th>
    <th width="10%" style="border: 1px soild; text-align:center;font-weight: bold;">OPERATOR</th>
    <th width="10%" style="border: 1px soild; text-align:center;font-weight: bold;">DATE</th>
    </tr>
    </thead>
    <tbody>';

    $qry="SELECT trn.*, process.process_name FROM `tbl_wororder_product_process` as trn 
        LEFT JOIN process_mst as process on process.process_id=trn.process_id 
    WHERE trn.rp_id=".$rel['rp_id']." and product_id = '".$rel['product_id']."' ORDER BY trn.process_priority";

    $result=$dbcon->query($qry);    
    $i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_packing=0;$final_amount=0;
    $cnt=mysqli_num_rows($result);
    while($row=mysqli_fetch_assoc($result))
    {
        $pctime = $row['process_time']/60;
        $html.='<tr>
        <td style="text-align: center;">'.$i.'</td>
        <td>'.$row['process_name'].'</td>
        <td>'.$row['description'].'</td>
        <td style="text-align: center;">'.number_format($pctime,3,".","").'</td>
        <td style="text-align: center;"></td>
        <td style="text-align: center;"></td>
        <td style="text-align: center;"></td>
        </tr>';
        $i++;
    }
    $html.='</tbody>
    </table>
    <table style="border: none; margin-top: 10px;">
    <tr style="border: none;">
    <td colspan="2" style="border: none; height: 40px; vertical-align: top;"><strong>COMMENTS:</strong></td>
    </tr>
    <tr style="border: none;">
    <td style="border: none; text-align: left; height: 40px;" width="50%"><strong>FINAL INSPECTED BY:------------------------------(SIGN/DATE)</strong></td>
    <td style="border: none; text-align: right; height: 40px;" width="50%"><strong>APPROVED BY:-----------------------------------(SIGN/DATE)</strong></td>
    </tr>
    <tr style="border: none;">
    <td colspan="2" style="border: none; text-align: center;"><strong>THE RESULTS OF THE WORK PROCEDURES PERFORMED ON THE ABOVE PROCESS(S) WERE FOUNDED TO BE:</strong></td>
    </tr>
    <tr style="border: none;">
    <td colspan="2" style="border: none; text-align: center;"><strong>ACCEPTABLE / UNACCEPTABLE</strong></td>
    </tr>
    <tr style="border: none;">
    <td colspan="2" style="border: none; font-size: 10px;">Note: If correction required, correction shall be made by the process owner of the report by striking the error out and initial it with the date.</td>
    </tr>
    </table>';

    $html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
    </body>
    </html>';
// echo $header;
// echo $html;exit;
    ob_end_clean();
    include("../../view/export/mpdf/mpdf.php");
    $mpdf=new mPDF('','A4','0','calibri','10','10','35','2','1','1');
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
    return 'Work Order '.$rel['po_req_no'].'.pdf';
} 
?>