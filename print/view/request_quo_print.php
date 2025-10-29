<?php
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once("../../crm/include/crm_common_functions.php");

$_SESSION['contents']=''; 
$form = "Request For Quotation";
$mode = "Print";

// Get identifier from request - check both 'id' and 'identifier' parameters
$identifier = '';
if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
    $identifier = trim($_REQUEST['id']);
} elseif(isset($_REQUEST['identifier']) && !empty($_REQUEST['identifier'])) {
    $identifier = trim($_REQUEST['identifier']);
}

$type = 'pdf';

if(empty($identifier)) {
    die("Error: No quotation identifier provided");
}

if(strtolower($type) == 'pdf') {
    
    // Determine if identifier is numeric ID or quotation number
    $is_int_id = ctype_digit($identifier);
    
    // Fetch main quotation details
    if ($is_int_id) {
        $qid = (int)$identifier;
        $sql_main = "SELECT qref.*, 
            usr.user_name,
            comp.company_name,
            comp.address as company_address,
            comp.vatno as company_gst,
            comp.logo,
            state.state_name as company_state,
            state.gst_state_code as company_gst_code
            FROM po_quotation_ref as qref
            LEFT JOIN tbl_company as comp ON comp.company_id = qref.company_id
            LEFT JOIN state_mst as state ON state.stateid = comp.stateid
            LEFT JOIN users as usr ON usr.user_id = ".$_SESSION['user_id']."
            WHERE qref.quotation_ref_id = {$qid} LIMIT 1";
    } else {
        $refno = $dbcon->real_escape_string($identifier);
        $sql_main = "SELECT qref.*, 
            usr.user_name,
            comp.company_name,
            comp.address as company_address,
            comp.vatno as company_gst,
            comp.logo,
            state.state_name as company_state,
            state.gst_state_code as company_gst_code
            FROM po_quotation_ref as qref
            LEFT JOIN tbl_company as comp ON comp.company_id = qref.company_id
            LEFT JOIN state_mst as state ON state.stateid = comp.stateid
            LEFT JOIN users as usr ON usr.user_id = ".$_SESSION['user_id']."
            WHERE qref.ref_quotation_no = '{$refno}' LIMIT 1";
    }
    
    $res_main = $dbcon->query($sql_main);
    if (!$res_main || $res_main->num_rows == 0) {
        die("Error: Quotation not found");
    }
    
    $rel = mysqli_fetch_assoc($res_main);
    $quotation_ref_id = $rel['quotation_ref_id'];
    
    // Format dates
    $ref_quotation_date = '';
    if($rel['ref_quotation_date'] != "1970-01-01" && $rel['ref_quotation_date'] != "0000-00-00") {
        $ref_quotation_date = date('d-m-Y', strtotime($rel['ref_quotation_date']));
    }
    
    // Get supplier names
    $suppliers_list = '';
    if(!empty($rel['vender_id'])) {
        $clean_ids = preg_replace('/[^0-9,]/', '', $rel['vender_id']);
        if($clean_ids !== '') {
            $sql_sup = "SELECT l_name, l_address, m_address, gst_no, cust_cont_name, cust_mobile,
                        city.city_name, state.state_name, country.country_name
                        FROM tbl_ledger as led
                        LEFT JOIN city_mst as city ON city.cityid = led.cityid
                        LEFT JOIN state_mst as state ON state.stateid = led.stateid
                        LEFT JOIN country_mst as country ON country.countryid = led.countryid
                        WHERE l_id IN ({$clean_ids})";
            $res_sup = $dbcon->query($sql_sup);
            $suppliers = [];
            while($sup = mysqli_fetch_assoc($res_sup)) {
                $suppliers[] = $sup;
            }
        }
    }
    
    // Fetch item details
   $itemSql = "SELECT 
    rtrn.po_quotationtrn_id,
    rtrn.product_id,
    pro.product_name,
    hsn.hsn_code AS product_hsn_code,  -- get HSN from hsn_mst
    rtrn.product_qty,
    rtrn.product_conv_qty,
    rtrn.unit_id,
    rtrn.conv_unit_id,
    unit.unit_name AS base_unit,
    cunit.unit_name AS conv_unit,
    rtrn.remark,
    pre.pre_no,
    pre.pre_date,
    ain.approve_no,
    ain.approve_date,
    req.indent_no,
    req.indent_date
FROM po_quotationtrn_ref AS rtrn
LEFT JOIN product_mst AS pro ON pro.product_id = rtrn.product_id
LEFT JOIN mst_hsn_code AS hsn ON hsn.hsn_id = pro.product_hsn   -- join HSN table
LEFT JOIN unit_mst AS unit ON unit.unitid = rtrn.unit_id
LEFT JOIN unit_mst AS cunit ON cunit.unitid = rtrn.conv_unit_id
LEFT JOIN approve_indent AS ain ON ain.approve_indent_id = rtrn.approve_indent_id
LEFT JOIN tbl_request_product AS req ON req.rp_id = ain.rp_id
LEFT JOIN tbl_pre_trn AS pre_trn ON pre_trn.pre_trn_id = req.pre_trn_id
LEFT JOIN tbl_pre AS pre ON pre.pre_id = pre_trn.pre_id
WHERE rtrn.ref_name='request_for_quotation' 
AND rtrn.ref_id = {$quotation_ref_id} 
AND rtrn.po_quotationtrn_status = 0
ORDER BY rtrn.po_quotationtrn_id";

    
    $res_items = $dbcon->query($itemSql);
    
    // Build header and footer
    $header = '<img src="'.DOMAIN_F.LOGO.$rel['logo'].'" style="width: 100%" />';
    $footer = ''; // Define footer if needed, or leave empty
    
    // Start HTML
    $html = '<html>
    <head>
    <title>'.$form.' - '.$rel['ref_quotation_no'].'</title>
    <style type="text/css">
        .nextpage {
            page-break-after: always;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        table tr, td {
            border: 1px solid #000 !important;
        }
        .p1 {
            padding-left: 25px;
            padding-right: 25px;
        }
    </style>
    </head>
    <body>
    
    <htmlpageheader name="otherpages" style="display:none">
        <div style="text-align:center">'.$header.'</div>
    </htmlpageheader>
    
    <sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
    
    <div class="p1">
        <div>
            <table style="font-size:12px;border-collapse: collapse;width:100%;border: none !important;" cellpadding="5" cellspacing="5">
                <tr>
                    <td colspan="2" style="text-align:center; font-size:15px; font-weight:bold;border: none !important;">'.$form.'</td>
                </tr>
                <tr>
                    <td style="width:50%; vertical-align: top;border: none !important;">
                        <table style="font-size:12px;border-collapse: collapse;width:100%;border: none !important;" cellpadding="5" cellspacing="5">
                            <tr>
                                <td style="text-align:left;border:none;width:30%;"><strong>RFQ No</strong></td>
                                <td style="text-align:left;border:none;font-size:14px"><strong>'.$rel['ref_quotation_no'].'</strong></td>
                            </tr>
                            <tr>
                                <td style="text-align:left;border:none;">RFQ Date</td>
                                <td style="text-align:left;border:none;">'.$ref_quotation_date.'</td>
                            </tr>
                            <tr>
                                <td style="text-align:left;border:none;">Prepared By</td>
                                <td style="text-align:left;border:none;">'.$rel['user_name'].'</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width:50%; vertical-align: top;border: none !important;">
                        <table style="font-size:12px;border-collapse: collapse;width:100%;border: none !important;" cellpadding="5" cellspacing="5">
                            <tr>
                                <td style="text-align:left;border:none;"><strong>From: '.$rel['company_name'].'</strong><br/>'.$rel['company_address'].'<br/>GST NO: '.$rel['company_gst'].'</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>';
    
    // Supplier sections
    if(!empty($suppliers)) {
        foreach($suppliers as $idx => $sup) {
            $html .= '<div style="margin-top:20px;">
                <table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
                    <tr>
                        <td colspan="3" style="text-align:left; font-size:13px; font-weight:bold;border:1px solid;">
                            Supplier '.($idx+1).': <strong>'.$sup['l_name'].'</strong><br/>
                            Address: '.$sup['m_address'].'<br/>
                            '.$sup['city_name'].', '.$sup['state_name'].', '.$sup['country_name'].'<br/>
                            GST NO: '.$sup['gst_no'].'<br/>
                            Contact: '.$sup['cust_cont_name'].' | Mobile: '.$sup['cust_mobile'].'
                        </td>
                    </tr>
                </table>
            </div>';
        }
    }
    
    // Items table
    $html .= '<div style="margin-top:20px;">
        <table style="font-size:12px;border-collapse: collapse;width:100%; border:1px solid" cellpadding="3" cellspacing="3">
            <thead>
                <tr>
                    <th style="width:5%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
                    <th style="width:35%;text-align:center;border:1px solid;">Item Description</th>
                    <th style="width:10%;text-align:center;border:1px solid;">HSN Code</th>
                    <th style="width:12%;text-align:center;border:1px solid;">Quantity</th>
                    <th style="width:18%;text-align:center;border:1px solid;">Remarks</th>
                </tr>
            </thead>
            <tbody>';
    
    $i = 1;
    while($row = mysqli_fetch_assoc($res_items)) {
        $pqty = '';
        if($row['product_conv_qty'] > 0) {
            $pqty .= number_format($row['product_conv_qty'], 2, ".", "").' '.$row['conv_unit'];
        }
        if($row['product_qty'] > 0) {
            $pqty .= '<br/>'.number_format($row['product_qty'], 2, ".", "").' '.$row['base_unit'];
        }
        
        $pre_date = '';
        if(!empty($row['pre_date']) && $row['pre_date'] != '0000-00-00') {
            $pre_date = date('d-m-Y', strtotime($row['pre_date']));
        }
        
        $indent_date = '';
        if(!empty($row['indent_date']) && $row['indent_date'] != '0000-00-00') {
            $indent_date = date('d-m-Y', strtotime($row['indent_date']));
        }
        
        $html .= '<tr>
            <td style="text-align:center;border:1px solid;vertical-align:top;">'.$i.'</td>
            <td style="text-align:left;border:1px solid;vertical-align:top;"><strong>'.$row['product_name'].'</strong></td>
<td style="text-align:center;border:1px solid;vertical-align:top;">'.$row['product_hsn_code'].'</td>
            <td style="text-align:center;border:1px solid;vertical-align:top;">'.$pqty.'</td>
            <td style="text-align:left;border:1px solid;vertical-align:top;">'.$row['remark'].'</td>
        </tr>';
        $i++;
    }
    
    $html .= '</tbody>
        </table>
    </div>';
    
    /// Notes and Signatory in one row
$html .= '<div style="margin-top:20px;">
    <table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
        <tr>
            <td style="border:1px solid; width:60%; vertical-align:top;">
                <strong>Notes:</strong><br/>
                1. Please quote your best price with delivery terms.<br/>
                2. Quote should include GST and all applicable taxes.<br/>
                3. Payment terms to be mentioned clearly.<br/>
                4. Delivery schedule to be specified.<br/>
            </td>
            <td style="border:1px solid; width:40%; text-align:center; vertical-align:top; height:80px;">
                <strong>For, '.$rel['company_name'].'</strong><br/><br/><br/><br/>
                <div>Authorised Signatory</div>
            </td>
        </tr>
    </table>
</div>';

    
    $html .= '<p style="text-align: center;">** This document is computer generated and does not require manual signature. **</p>
    </div>
    </body>
    </html>';
    
    // Generate PDF
    ob_end_clean();
    require_once __DIR__ . '/../../vendor/autoload.php';
    
    $mpdf = new \Mpdf\Mpdf([
        'format' => 'A4',
        'default_font' => 'calibri',
        'margin_top' => 55,
        'margin_bottom' => 15,
        'margin_left' => 0,
        'margin_right' => 0,
    ]);
    
    $mpdf->defaultheaderfontsize = 10;
    $mpdf->defaultheaderfontstyle = 'B';
    $mpdf->defaultheaderline = 1;
    $mpdf->defaultfooterfontsize = 10;
    $mpdf->defaultfooterfontstyle = 'B';
    $mpdf->defaultfooterline = 1;
    
    $mpdf->SetHTMLHeader($header);
    
    // Set footer only if defined
    if(!empty($footer)) {
        $mpdf->SetHTMLFooter($footer);
    }
    
    $mpdf->SetWatermarkText();
    $mpdf->showWatermarkText = true;
    $mpdf->allow_charset_conversion = true;
    $mpdf->charset_in = 'UTF-8';
    $mpdf->WriteHTML($html);
    $mpdf->Output('quotation_'.$rel['ref_quotation_no'].'.pdf', 'I');
    
    ob_clean();
}
?>