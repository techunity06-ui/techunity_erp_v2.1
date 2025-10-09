<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once("../../crm/include/crm_common_functions.php");
include_once(COMMON_FUNCTION_PATH."common_production_functions.php");

$bulkAccessArray = canCheckPermissionAccess($dbcon, [QUOTATION_SLUG_PRINT]);

if (!in_array(QUOTATION_SLUG_PRINT, $bulkAccessArray))
{
 header("Location: " . DOMAIN . "permission_access");
}

$quotation_id = $_REQUEST['id'];
$type = 'pdf';
if (strtolower($type) == 'pdf')
{
   //Quotation Data
 $query = "select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name from tbl_quotation as quot
 left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
 left join tbl_customer as cust on cust.cust_id=quot.cust_id
 left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
 left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
 where quot.quotation_id=" . $quotation_id;
 $rel = mysqli_fetch_assoc($dbcon->query($query));


   //Company Data
   /*$comp_qry="select * from tbl_company as comp
   where comp.company_id=".$rel['company_id'];
   */
   $set = "select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=" . $rel['company_id'];

   $comp_rel = mysqli_fetch_assoc($dbcon->query($set));

   //$header=$set;
   $header = '<img src="' . DOMAIN_F . LOGO . $comp_rel['logo'].'" style="width:8.27in" />';
   //$footer = '<img src="' . DOMAIN_F . LOGO . 'footer-quot.jpg" style="width:8.27in" />';
   //$header ='<img src="'.DOMAIN_F.LOGO.'elcon.png" style="width:3.27in;padding-top:25px;" />';
   //$header =$comp_rel["logo"];
   $footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';
   $approve_status = '';
   if ($rel['approve_status'] == '0')
   {
     $approve_status = ' (DRAFT)';
 }

 $html = '<html>
 <head>                  
 <title>Quotation - ' . $rel['quotation_no'] . '</title>
 <style type="text/css">
 /*
 .page{
    width:8.27in;
    height:10.69in;
    }*/
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

</style>
</head>
<body>

<htmlpageheader name="otherpages" style="display:none">
<div style="text-align:center">' . $header . '</div>
</htmlpageheader>
<htmlpagefooter name="otherpages_footer" style="display:none">
<div style="text-align:center">' . $footer . '</div>
</htmlpagefooter>
<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
<sethtmlpagefooter name="otherpages_footer" value="on" show-this-page="0"/>
<div>
<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
<tr>
<td colspan="6" style="text-align:center;font-size:15px;font-weight:bold;"> 
QUOTATION
</td>
</tr>
<tr>
<td colspan="4"  rowspan="" style="text-align:left;vertical-align:top;border:1px solid;width:40%;"> 
To,<br>
<strong>' . $rel['cust_name'] . '</strong><br/>
' . (($rel['quot_address']) ? nl2br($rel['quot_address']) : '') . '<br/><br/>
IGSTIN -  ' . $rel['qt_com_gstno'] . '<br/>
E-Mail - ' . $rel['cust_email'] . '<br/>
<strong>Kind. Attn : </strong>' . $rel['c_con_fname'] . ' ' . $rel['c_con_lname']  . '<br/>
</td>

<td style="text-align:left;border:0px solid;width:20%;vertical-align:top"> 
<strong>Quote No :</strong><br>
<strong>Date :</strong><br>
</td>
<td style="text-align:left;border:0px solid;width:30%;vertical-align:top"> 
<strong>' . $rel['quotation_no'] . '</strong><br>
<strong> ' . date("M d, Y", strtotime($rel['quotation_date'])) . ' </strong> <br>
</td>
</tr>
<tr>
<td colspan="6"> Dear Sir, <br>
We have pleasure in submitting our quotation as requested. Please contact me f you have any queries.
</td>
</tr>
<tr>
<th style="width:5%;text-align:center;border:1px solid;">NO</th>
<th style="width:45%;text-align:center;border:1px solid;">DESCRIPTION</th>
<th style="width:5%;text-align:center;border:1px solid;">HSN Code</th>
<th style="width:5%;text-align:center;border:1px solid;">QTY.</th>
<th style="width:5%;text-align:center;border:1px solid;">UNIT PRICE</th>
<th style="width:5%;text-align:center;border:1px solid;">AMOUNT</th>
</tr>
';
$trn_qry = "select trn.*,pro.product_name,pro.product_hsn,pro.product_icode ,unit.unit_name from tbl_quotation_trn as trn 
left join product_mst as pro on pro.product_id=trn.product_id
left join unit_mst as unit on unit.unitid=trn.unitid
where trn.quot_trn_status=0 and trn.quotation_id=" . $rel['quotation_id'];
$trn_qry_rs = $dbcon->query($trn_qry);
$p = 1;
$ttl_amt = 0;
$ttl_qty = 0;
$disc_total = 0;
$tax1_total = $tax2_total = $tax3_total = 0;
$tax = array();
$cnt = mysqli_num_rows($trn_qry_rs);
while ($trn_rel = mysqli_fetch_assoc($trn_qry_rs))
{

 $tax1 = $trn_rel['tax_amount1'];
 $tax2 = $trn_rel['tax_amount2'];
 $tax3 = $trn_rel['tax_amount3'];

 $tax1_total += $tax1;
 $tax2_total += $tax2;
 $tax3_total += $tax3;

 $tax[$trn_rel['tax_name1']] = $tax1_total;
 $tax[$trn_rel['tax_name2']] = $tax2_total;
 $tax[$trn_rel['tax_name3']] = $tax3_total;

 $disc_total = $disc_total + $trn_rel['product_discount'];
 $product_desc = '';
 if ($trn_rel['product_desc'] != '' && $trn_rel['product_desc'] != '0')
 {
     $product_desc = nl2br($trn_rel['product_desc']);
 }
 $product_discount = ($trn_rel['product_discount'] ? $trn_rel['product_discount'] : '0.00');

 $html .= '<tr style="border:none;border-left:1px solid;border-right:1px solid;">
 <td style="text-align:center;border:1px solid;vertical-align:top;">' . $p . '</td>
 <td style="text-align:left;border:1px solid;vertical-align:top;">
 <strong>' . $trn_rel['product_name'] . '</strong><br/>
 ' . $product_desc . '
 </td>
 <td style="text-align:center;border:1px solid;vertical-align:top;">
 ' . $trn_rel['product_hsn'] . '
 </td>
 <td style="text-align:center;border:1px solid;vertical-align:top;">
 ' . indian_number($trn_rel['product_qty'], 2) . '
 </td>

 <td style="text-align:center;border:1px solid;vertical-align:top;">';
 if ($trn_rel['act_amt_flag'] == '1')
 {
     $html .= "Extra At Actual";
 }
 else
 {
     $html .= indian_number($trn_rel['product_rate'], 2);;
 }

 $product_total = ($trn_rel['product_qty'] * $trn_rel['product_rate'])-$product_discount;
 $html .= '</td>
 <td style="text-align:center;border:1px solid;vertical-align:top;">';
 if ($trn_rel['act_amt_flag'] == '1')
 {
     $html .= "Extra At Actual";
 }
 else
 {
     $html .= indian_number($product_total, 2);
 }

 $html .= '</td>
 </tr>';
 $ttl_qty = $ttl_qty + $trn_rel['product_qty'];
 if ($trn_rel['act_amt_flag'] != '1')
 {
     $ttl_amt = $ttl_amt + $product_total;
 }

 $p++;
}
$pr = 5 - $cnt;
for ($j = 0;$j < $pr;$j++)
{
 $html .= '<tr style="border:none;border-left:1px solid;border-right:1px solid;">
 <td style="border:none;border-left:1px solid;border-right:1px solid;height:40px;"></td>
 <td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
 <td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
 <td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
 <td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
 <td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
 </tr>';
}

$html .= '<tr>
<td colspan="3" style="text-align:right;font-weight:bold"> Total :</td>
<td style="text-align:center;font-weight:bold"> '.$ttl_qty.' </td>
<td> </td>
<td style="text-align:center;"> 
<strong>RS. '.indian_number($ttl_amt,2).'</strong> <br>
GST @18% & TCS @ 0.1% EXTRA
</td>
</tr>';

$html .= '<tr style="border: 0;">
<td colspan="6" style="border: 0;">
<span style="font-weight:bold;font-size:14px;text-align:left" >SPECIFICATION :- </span><br>';
$trn_qry_recall = $dbcon->query($trn_qry); 
if(mysqli_num_rows($trn_qry_recall) > 0) {
 while ($trn_row = mysqli_fetch_assoc($trn_qry_recall))
 {
    $html .= '<span style="text-align:center"> '.(($trn_row['product_spec']!='0')?$trn_row['product_spec']:'').'</span><br>';
}  
}

$html .= '</td>
</tr>';
$remark = ($rel['quot_remark']) ? $rel['quot_remark'] : "";   
if(!empty($remark)){
 $html .= '<tr style="border: 0;">
 <td colspan="6" style="text-align:left; border: 0;">
 <span style="font-weight:bold;font-size:13px" >NOTE :-</span>
 '.$rel['quot_remark'].'
 </td>
 </tr>';
}
$html .= '</table>

<!--page1 end-->';


/* Get Terms And Condition Start */
$terms_qry = "select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=" . $rel['quotation_id'] . " order by qtrm.tc_priority";
$terms_qry_rs = $dbcon->query($terms_qry);
if (mysqli_num_rows($terms_qry_rs))
{
 $html .= '<center class="nextpage"></center>
 <h3 style="text-align:center;">Terms and Conditions</h3>
 <div><table width="100%" style="font-size:12px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3"><tbody>';
 $t = 1;
 while ($term_rel = mysqli_fetch_assoc($terms_qry_rs))
 {
     $string = (nl2br($term_rel['tc_details']));

     $html .= '<tr>
     <td width="5%" style="width:5%;text-align:center;border:1px solid;padding:5px;">' . $t . '</td>
     <td width="25%" style="width:25%;text-align:center;border:1px solid;padding:5px;">' . $term_rel['tc_name'] . '</td>
     <td width="70%" style="width:70%;text-align:left;border:1px solid #000;padding:5px;">' . $string . '</td>
     </tr>';
     $t++;
 }
 $html .= '</tbody></table></div>';
}
/* Get Terms And Condition Start */

/* Check Annexure Attachments Start */
if (trim($rel['quot_annex_content']))
{
 $html .= '<center class="nextpage"></center>';
 $html .= '<div class="quot_annex_content_div">' . $rel['quot_annex_content'];
 $html .= '</div>';
}
/* Check Annexure Attachments End */

$html .= '<sethtmlpagefooter name="firstpages_footer" value="on" /><sethtmlpagefooter name="otherpages_footer" value="on" />
</body>
</html>';
       //echo $html;exit;
ob_end_clean();
include ("../../view/export/mpdf/mpdf.php");
$mpdf = new mPDF('', 'A4', '0', 'calibri', '10', '10', '40', '25', '2', '2');
$mpdf->defaultheaderfontsize = 10; /* in pts */
$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
$mpdf->defaultfooterfontsize = 10; /* in pts */
$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLFooter($footer);
       //Show page number
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
$mpdf->Output();
       //$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
ob_clean();
return 'quotation' . $quotation_id . '.pdf';
}
?>