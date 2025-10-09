<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");   

$type='pdf';
if(strtolower($type) == 'pdf') {
    $forecast_user_id=$dbcon->real_escape_string($_REQUEST['id']);

    $query="SELECT f.*, users.user_name, bm.branch_name FROM tbl_forecast_user AS f LEFT JOIN users AS users ON users.user_id = f.f_user_id LEFT JOIN branch_mst AS bm ON bm.branch_id = f.branch_id WHERE f.forecast_user_id = ".$forecast_user_id;
    $rel=mysqli_fetch_assoc($dbcon->query($query));
 // echo "<pre>";print_r($query);die(); 
    if(!$rel){
        header("Location: ".ROOT.CRM_ROOT."forecast_user_list");
    }

    $set="SELECT comp.*,state.state_name,state.gst_state_code FROM tbl_company as comp LEFT JOIN state_mst as state on comp.stateid=state.stateid WHERE company_id=".$rel['company_id'];
    $set_head=mysqli_fetch_assoc($dbcon->query($set));  

    $financial_data = getFinacialyear_data_by_id($dbcon, $rel['financial_year_id']);

    $header =get_header($dbcon,'text-align: center','5.7in','150px');
    $footer = '<hr>';

    $html='<html>
    <head>          
    <title>Forecast - '.$rel['forecast_no'].'</title>
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
    <td colspan="4" style="font-size: 18px; font-weight: bold; text-align: center;">FORECAST RECEIPT</td>
    </tr>
    <tr>
    <td style="width: 25%; font-weight: bold;">FORECAST No</td>
    <td style="width: 25%;">'.$rel['forecast_no'].'</td>
    <td style="width: 25%; font-weight: bold;">FORECAST Date</td>
    <td style="width: 25%;">'.date('d/m/Y',strtotime($rel['forecast_date'])).'</td>
    </tr>
    <tr>
    <td style="width: 25%; font-weight: bold;">User Name</td>
    <td style="width: 25%;">'.$rel['user_name'].'</td>
    <td style="width: 25%; font-weight: bold;">Branch Name</td>
    <td style="width: 25%;">'.$rel['branch_name'].'</td>
    </tr>
    <tr>
    <td style="width: 25%; font-weight: bold;">Forecast Type</td>
    <td style="width: 25%;">'.get_for_target_p_name($dbcon,$rel['forecast_type']).'</td>
    <td style="width: 25%; font-weight: bold;">Financial Year</td>
    <td style="width: 25%;">'.date("M-Y",strtotime($financial_data['financial_start_date'])).' - '.date("M-Y",strtotime($financial_data['financial_end_date'])).'</td>
    </tr>
    </table>
    <table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
    <thead>
    <tr>
    <th width="10%" style="text-align:center;border:1px solid;border-top: none;"><strong>SR. NO.</strong></th>
    <th width="20%" style="text-align:center; border:1px solid;border-top: none;font-size:14px;" ><strong>Month</strong>
    </th>';
    if($rel['forecast_base']!=1){
        $html.='<th width="25%" style="border:1px solid;border-top: none;font-size:14px;text-align:center">
        <strong>'.(($rel['forecast_base']==3) ? 'Product Name' : 'Product Category').'</strong>
        </th>';
    }
    $html.='<th width="20%" style="border:1px solid;border-top: none;font-size:14px;text-align:center">
    <strong>Quantity</strong>
    </th>
    <th width="20%" style="border:1px solid;border-top: none;font-size:14px;text-align:center">
    <strong>Amount</strong>
    </th>
    </tr>
    </thead>
    <tbody>';

    $qry="SELECT trn.*, product.product_name, cat.cat_name, fpm.f_period_name FROM `tbl_forecast_user_trn` as trn 
    LEFT JOIN product_mst as product on product.product_id=trn.f_product 
    LEFT JOIN tbl_category as cat on cat.cat_id = trn.f_product
    LEFT JOIN forecast_period_mst as fpm ON trn.forecast_month = fpm.f_period_id
    WHERE trn.status=0 and trn.forecast_usertable_id=".$rel['forecast_user_id']." ORDER BY trn.forecast_user_trn_id ASC";

    $result=$dbcon->query($qry);    
    $i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_packing=0;$final_amount=0;
    $cnt=mysqli_num_rows($result);
    while($row=mysqli_fetch_assoc($result))
    {
        $pro_name = '';
        if($row['forecast_base']==2){
            $pro_name = $row['cat_name'];
        }else if($row['forecast_base']==3){
            $pro_name = $row['product_name'];
        }

        $html.='<tr style="border: none; border-right: 1px solid; border-left: 1px solid;">
        <td style="text-align: center; border-left: 1px solid;">'.$i.'</td>
        <td style="text-align: center; border-left: 1px solid;">'.$row['f_period_name'].'</td>';
        if($row['forecast_base']!=1){
            $html.='<td style="text-align: center; border-left: 1px solid;">'.$pro_name.'</td>';
        }
        $html.='<td style="text-align: center; border-left: 1px solid;">'.$row['target_qty'].'</td>
        <td style="text-align: center; border-left: 1px solid;">'.$row['target_amount'].'</td>
        </tr>';
        $total=$total+$mtotal;
        $i++;
    }
    $pr=10-$cnt;

    for($j=0; $j<$pr; $j++){

        $html.='<tr style="border-bottom:none;border-top:none;">
        <td style="height:35px; border: none; border-left: 1px solid; border-right: 1px solid;"></td>
        <td style="border: none; border-left: 1px solid; border-right: 1px solid;"></td>';
        if($rel['forecast_base']!=1){
            $html.='<td style="border: none; border-left: 1px solid; border-right: 1px solid;"></td>';
        }
        $html.='<td style="border: none; border-left: 1px solid; border-right: 1px solid;"></td>
        <td style="border: none; border-left: 1px solid; border-right: 1px solid;"></td>
        </tr>';

    }

    $remark = ($rel['remark']) ? $rel['remark'] : '';
    $colspan = ($rel['forecast_base']!=1) ? 5 : 4;
    $html.='<tr>
    <td colspan="'.$colspan.'" style="text-align: left;"><strong>Remarks :</strong> '.$remark.'</td>
    </tr>
    </tbody>
    </table>
    </div>';

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
    return 'Forecast_'.$rel['forecast_user_id'].'.pdf';
} 
?>
