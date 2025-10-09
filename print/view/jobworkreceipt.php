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
    $job_work_id=$dbcon->real_escape_string($_REQUEST['id']);

    $query="SELECT jobwork.*,country.country_name,state.state_name,cust.stateid,state.gst_state_code, city.city_name, cust.l_name, cust.m_address, cust_pincode,cust_mobile,gst_no FROM tbl_job_work as jobwork 
    LEFT JOIN tbl_ledger as cust on cust.l_id=jobwork.vender_id
    LEFT JOIN country_mst as country on country.countryid=cust.countryid
    LEFT JOIN state_mst as state on state.stateid=cust.stateid
    LEFT JOIN city_mst as city on city.cityid=cust.cityid
    WHERE jobwork.job_work_status = 0 and jobwork.job_work_id=".$job_work_id;
    $rel=mysqli_fetch_assoc($dbcon->query($query));
 // echo "<pre>";print_r($query);die(); 
    if(!$rel){
        header("Location: ".ROOT.PRODUCTION_ROOT."pending_job_work_list");
    }

    $set="SELECT comp.*,state.state_name,state.gst_state_code FROM tbl_company as comp LEFT JOIN state_mst as state on comp.stateid=state.stateid WHERE company_id=".$rel['company_id'];
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

    /*$header ='<div style="text-align: center"><img src="'.DOMAIN_F.LOGO.$set_head['logo'].'" style="width:8.27in;" /></div>';*/
    $header =get_header($dbcon,'text-align: center','100%','1.2in');
    $footer = '<hr>';

    $html='<html>
    <head>          
    <title>Jobwork Receipt - '.$rel['job_work_no'].'</title>
    <style type="text/css">
    .nextpage
    {
        page-break-after: always;
    }
    table{
        border-collapse:collapse;
        width:100%;
    }

    table tr,td{
        border:1px solid #000 !important;
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
    <tr>
    <td colspan="3" style="font-size: 18px; font-weight: bold; text-align: center;">JOBWORK CHALAN</td>
    </tr>
    <tr>
    <td style="width: 25%; font-weight: bold;">Jobwork No</td>
    <td style="width: 25%;">'.$rel['job_work_no'].'</td>
    <td style="width: 50%; vertical-align: top;" rowspan="3"><strong>Vendor Details : '.$rel['l_name'].'</strong><br>'.$rel['m_address'].' , '.$rel['city_name'].'<br>'.$rel['state_name'].' , '.$rel['country_name'].' '.$rel['cust_pincode'].'<br><strong>GSTIN : '.$rel['gst_no'].'</strong></td>
    </tr>
    <tr>
    <td style="width: 25%; font-weight: bold;">Jobwork Date</td>
    <td style="width: 25%;">'.date('d/m/Y',strtotime($rel['job_work_date'])).'</td>
    </tr>
    <tr>
    <td style="width: 25%; font-weight: bold;">Vehicle No</td>
    <td style="width: 25%;">'.$rel['vehicle_no'].'</td>
    </tr>
    </table>
    <table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
    <thead>
    <tr>
    <th width="4%" style="text-align:center;border:1px solid;border-top: none;"><strong>SR.<br/> NO.</strong></th>
    <th width="20%" style="width:400px;text-align:center !important; border:1px solid;border-top: none;font-size:14px;" >
    <strong>Product Name</strong>
    </th>
    <th width="15%" style="border:1px solid;border-top: none;font-size:14px;text-align:center">
    <strong>Process Name</strong>
    </th>
    <th width="7%" style="border:1px solid;border-top: none;font-size:14px;text-align:center">
    <strong>Quantity</strong>
    </th>
    <th width="7%" style="border:1px solid;border-top: none;font-size:14px;text-align:center">
    <strong>Unit</strong>
    </th>
    <th width="7%" style="border:1px solid;border-top: none;font-size:14px;text-align:center">
    <strong>Rate</strong>
    </th>
    <th width="10%" style="border:1px solid;border-top: none;font-size:14px;text-align:center">
    <strong>Amount</strong>
    </th>
    </tr>
    </thead>
    <tbody>';

    /*$qry="SELECT trn.*, product.product_name, per.unit_name, process.process_name FROM `tbl_job_work_trn` as trn LEFT JOIN product_mst as product on product.product_id=trn.product_id LEFT JOIN unit_mst as per on per.unitid=trn.product_base_unit LEFT JOIN process_mst as process ON process.process_id = trn.process_id WHERE trn.job_work_trn_status=0 and trn.job_work_id=".$rel['job_work_id']." ORDER BY trn.job_work_trn_id";*/

    $qry="SELECT trn.*,GROUP_CONCAT(job.chalan_no) as chalan_no,GROUP_CONCAT(rp.job_card_no) as job_card_no, GROUP_CONCAT(ap.batch_no) as batch_no, product.product_name,product.product_icode,per.unit_name,met.unit_name as met_unitname, process.process_name 
    FROM `tbl_job_work_trn` as trn 
    LEFT JOIN tbl_job_work as job on job.job_work_id=trn.job_work_id
    LEFT JOIN tbl_job_work_sub_trn as strn on strn.job_work_trn_id=trn.job_work_trn_id
    LEFT JOIN tbl_allocate_process as ap on strn.p_id=ap.p_id 
    LEFT JOIN tbl_request_product as rp on strn.rp_id=rp.rp_id 
    LEFT JOIN product_mst as product on product.product_id=trn.product_id 
    LEFT JOIN unit_mst as per on per.unitid=trn.product_base_unit 
    LEFT JOIN process_mst as process ON process.process_id = trn.process_id 
    LEFT JOIN unit_mst as met on met.unitid=trn.material_unit 
    WHERE trn.job_work_trn_status=0 and trn.job_work_id=".$rel['job_work_id']." GROUP BY trn.job_work_trn_id ORDER BY trn.job_work_trn_id ";

    $result=$dbcon->query($qry);    
    $i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_packing=0;$final_amount=0;
    $cnt=mysqli_num_rows($result);
    while($row=mysqli_fetch_assoc($result))
    {
        $job_details = "";

        if(!empty($row['chalan_no'])){
            $job_details .= "<br> JOBWORK CHALAN : " . $row['chalan_no'];
        }
        if(!empty($row['job_card_no'])){
            $job_details .= "<br>  JOBCARD NO : " . $row['job_card_no'];
        }
        if(!empty($row['job_card_no'])){
            $job_details .= "<br>  Batch NO : " . $row['batch_no'];
        }

        $mrate=$row['pr_rate'];
        if($row['material_qty']!=0){
            $munit=$row['met_unitname'];
            $mqty=$row['material_qty'];
            
        }else{
            $munit=$row['unit_name'];
            $mqty=$row['product_base_qty'];
        }
        $mtotal=$mqty*$mrate;

        $html.='<tr style="border: none; border-right: 1px solid; border-left: 1px solid;">
        <td style="text-align: center; border-left: 1px solid;">'.$i.'</td>
        <td style="font-weight: bold; border-left: 1px solid;">'.$row['product_name'].' -- ('.$row['product_icode'].') '.$job_details.'<p style="font-weight: normal;">'.$row['description'].'</p></td>
        <td style="text-align: center; border-left: 1px solid;">'.$row['process_name'].'</td>
        <td style="text-align: center; border-left: 1px solid;">'.$mqty.'</td>
        <td style="text-align: center; border-left: 1px solid;">'.$munit.'</td>
        <td style="border-left: 1px solid;">'.$mrate.'</td>
        <td style="border-left: 1px solid; border-right: 1px solid;">'.$mtotal.'</td>
        </tr>';
        $total=$total+$mtotal;
        $i++;
    }
    $pr=10-$cnt;

    for($j=0; $j<$pr; $j++){

       $html.='<tr style="border-bottom:none;border-top:none;">
       <td style="height:35px; border: none; border-left: 1px solid; border-right: 1px solid;"></td>
       <td style="border: none; border-left: 1px solid; border-right: 1px solid;"></td>
       <td style="border: none; border-left: 1px solid; border-right: 1px solid;"></td>
       <td style="border: none; border-left: 1px solid; border-right: 1px solid;"></td>
       <td style="border: none; border-left: 1px solid; border-right: 1px solid;"></td>
       <td style="border: none; border-left: 1px solid; border-right: 1px solid;"></td>
       <td style="border: none; border-left: 1px solid; border-right: 1px solid;"></td>
       </tr>';

   }
   $round_off = round($total)-$total;
   $round_off = abs($round_off);
   $remark = ($rel['remark']) ? $rel['remark'] : '';
   $html.='<tr>
   <td colspan="6" style="font-weight: bold; text-align: right;">Total</td>
   <td>'.indian_number($total,2).'</td>
   </tr>
   </tbody>
   </table>
   <table style="font-size:14px;border-collapse: collapse;width:100%;page-break-inside: avoid;" cellpadding="3" cellspacing="3">
   <tr>
   <td rowspan="3" width="50%" style="vertical-align: top;"><strong>Remarks :</strong> '.$remark.'</td>
   <td style="border:1px solid;font-weight:bold;font-size: 14px;" width="25%">Total Amount</td>
   <td style="border:1px solid;font-size: 14px;text-align: right;font-weight: bold;" width="25%">'.indian_number($total,2).'</td>
   </tr>
   <tr>
   <td style="border:1px solid;font-weight:bold;font-size: 14px;" width="25%">Round Off</td>
   <td style="border:1px solid;font-size: 14px;text-align: right;font-weight: bold;" width="25%">'.indian_number($round_off,2).'</td>
   </tr>
   <tr>
   <td style="border:1px solid;font-weight:bold;font-size: 14px;" width="25%">Grand Total</td>
   <td style="border:1px solid;font-size: 14px;text-align: right;font-weight: bold;" width="25%">'.indian_number(($total-$round_off),2).'</td>
   </tr>
   <tr>
   <td colspan = "3" style="font-weight: bold; font-size: 14px;">Amount In Words : '.convert_number_to_words_new($total).'</td>
   </tr>
   <tr style="border-right: 1px solid; border-left: 1px solid; border-bottom: none;">
   <td colspan = "3" style="font-weight: bold; font-size: 14px; text-align: right; border: none;">For '.$set_head['company_name'].'</td>
   </tr>
   <tr style="border-right: 1px solid; border-left: 1px solid; border-top: none;">
   <td colspan = "3" style="font-weight: bold; font-size: 14px; text-align: right;"><img style="width:25%;" src="../../view/upload/signature/'.$set_head['authorized_signature'].'" /><br>AUTHORISED SIGNATORY</td>
   </tr>
   </tbody></table>';

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
   $mpdf->SetWatermarkText();
   $mpdf->showWatermarkText = true;
   $mpdf->allow_charset_conversion=true;
   $mpdf->charset_in='UTF-8';
   $mpdf->WriteHTML($html);
   $mpdf->Output();
    //$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
   ob_clean();
   return 'Job work Receipt'.$rel['job_work_id'].'.pdf';
} 
?>
