<?php 
session_start();
// var_dump(123);
$invoiceid=$_REQUEST['id'];
if(!empty($invoiceid)){
    include("../../config/config.php");
    include("../../config/session.php");
    include("../../include/function_database_query.php");
    include_once(COMMON_FUNCTION_PATH."common_functions.php");

    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
        FINANCE_PROFORMA_INVOICE_PRINT
    ]);

    if(!in_array(FINANCE_PROFORMA_INVOICE_PRINT,$bulkAccessArray)){
      header("Location: ".DOMAIN."permission_access");
  }
  umaboy_proformareceipt($dbcon, $invoiceid, $save_file = "No");
}
function umaboy_proformareceipt($dbcon, $invoiceid, $save_file){
    $type='pdf';
    if(strtolower($type) == 'pdf') {
        $que = "select performa_invoice_type from tbl_proforma_invoice where invoice_id=$invoiceid";
        $rel_que=mysqli_fetch_assoc($dbcon->query($que));

        $userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
        WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
        $userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
        $userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
        $userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';

        if($rel_que['performa_invoice_type']=='1'){
         $query="select invoice.*,country.country_name,payterms.payment_terms as payment_trm,state.state_name,cust_a.c_add_state,state.gst_state_code, city.city_name, cust.cust_name as company_name,cust_a.c_add_location as cust_address,cust_a.c_add_street as cust_street,cust_a.c_add_zip as cust_pincode, type.invoice_type,cust_mobile,cust_email,cust.cust_gst as gst_no 
         from tbl_proforma_invoice as invoice 
         left join tbl_customer as cust on cust.cust_id=invoice.cust_id 
         left join tbl_cust_address as cust_a on cust_a.cust_id=invoice.cust_id 
         left join country_mst as country on country.countryid=cust_a.c_add_country 
         left join state_mst as state on state.stateid=cust_a.c_add_state 
         left join city_mst as city on city.cityid=cust_a.c_add_city 
         left join tbl_invoicetype as type on type.invoicetype_id=invoice.invoicetype_id 
         left join pay_terms as payterms on payterms.terms_id=invoice.payment_terms 
         where invoice_id=$invoiceid";
     }else{
        $query="select invoice.*,country.country_name,payterms.payment_terms as payment_trm,state.state_name,cust.stateid,state.gst_state_code, city.city_name, cust.l_name as company_name,cust.m_address as cust_address, type.invoice_type,cust_pincode,cust_mobile,gst_no 
        from tbl_proforma_invoice as invoice 
        left join tbl_ledger as cust on cust.l_id=invoice.cust_id 
        left join country_mst as country on country.countryid=cust.countryid 
        left join state_mst as state on state.stateid=cust.stateid 
        left join city_mst as city on city.cityid=cust.cityid 
        left join tbl_invoicetype as type on type.invoicetype_id=invoice.invoicetype_id 
        left join pay_terms as payterms on payterms.terms_id=invoice.payment_terms 
        where invoice_id=$invoiceid";
    } 
    $rel=mysqli_fetch_assoc($dbcon->query($query));
 // echo $query;die();

    $set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
//CHEMITEK PROCESS EQUIPMENTS PRIVATE LIMITED       
//>Plot No : 117 , Nr. GETCO Sub -Station , Old GIDC , Gundlav Valsad - 396035, Gujarat, India
    $comp_rel=mysqli_fetch_assoc($dbcon->query($set));

    $rel['invoice_type'] = 'PROFORMA INVOICE';  
    if(!$rel){
      header("Location: ".ROOT."proforma_list");
  }

  $cons_gst_no=$rel['gst_no'];
  $cons_pan_no=$rel['pan_no'];
  $cons_state_name=$rel['state_name'];
  $cons_gst_state_code=$rel['gst_state_code'];
  $place_of_supply=$rel['city_name'];
  $order_no = ($rel['order_no']!='0')?$rel['order_no']:'';
if(!empty($rel['consignee_id']))//consignee
{ 
    if($rel['performa_invoice_type']=='1'){
      $table_name = 'tbl_party_consignee';
  }else{
      $table_name = 'tbl_custmer_consignee';
  }
  $consignee="select * from $table_name as cust 
  left join country_mst as country on country.countryid=cust.countryid
  left join state_mst as state on state.stateid=cust.stateid 
  left join city_mst as city on city.cityid=cust.cityid where cust_id=".$rel['consignee_id'];
  $cons_data=mysqli_fetch_assoc($dbcon->query($consignee)); 
  $cons_gst_no=($cons_data['gst_no']!='0')?$cons_data['gst_no']:$rel['gst_no'];
  $cons_pan_no=$cons_data['pan_no'];
  $cons_state_name=$cons_data['state_name'];
  $cons_gst_state_code=$cons_data['gst_state_code'];
  $place_of_supply=$cons_data['city_name'];
}

$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));  
//echo "<pre>";print_r($set_head);die();
$order_date='';$lr_date='';$dispatch_date='';
if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
{
    $order_date=date('d/m/Y',strtotime($rel['order_date']));
}
if($rel['dispatch_date']!="1970-01-01 00:00:00" && $rel['dispatch_date']!="0000-00-00 00:00:00")
{
    $dispatch_date=date('d-m-Y h:i a',strtotime($rel['dispatch_date']));
}

if($rel['dispatch_document_date']!="1970-01-01" && $rel['dispatch_document_date']!="0000-00-00")
{
    $dispatch_document_date=date('d-m-Y',strtotime($rel['dispatch_document_date']));
}

if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
{
    $order_date=date('d-m-Y',strtotime($rel['order_date']));
}





/* Check Discount is On or off Start */
if($set_head['show_disc']=='1'){
    $colspan=5;
    $dynamicwidth=40;
}else{
    $colspan=6;
    $dynamicwidth=46;
}
/* Check Discount is On or off End */
/*<img src="'.DOMAIN_F.LOGO.$set_head['logo'].'" style="width:2.27in;padding-top:0px;" />*/
$html='';

if($rel['quot_address']!='' && $rel['quot_address']!='0'){ 
 $address.= $rel['quot_address'];
 $address.='<br/>';
}else{ 
    $address.= $rel['cust_address'];
    $address.=' <br/>';
    $address.=$rel['city_name'].','. $rel['state_name'].','. $rel['country_name'];
    if(!empty($rel['cust_pincode'])){
        $address.='-'. $rel['cust_pincode'];
    } 
}

if($rel['quot_address']!='' && $rel['quot_address']!='0'){ 
 $header.= $rel['quot_address'];
 $header.='<br/>';
}else{ 
    $header.= $rel['cust_address'];
    $header.=' <br/>';
    $header.=$rel['city_name'].','. $rel['state_name'].','. $rel['country_name'];
    if(!empty($rel['cust_pincode'])){
        $header.='-'. $rel['cust_pincode'];
    } 
}

if(empty($rel['consignee_id'])){ 
  $header.= $rel['cust_address'];
  $header.='<br/>';
  $header.=$rel['city_name'].','. $rel['state_name'].','. $rel['country_name'];
  if(!empty($rel['cust_pincode'])){
    $header.='-'. $rel['cust_pincode'];
} 
}else{ 
  $header.= $cons_data['cust_address'];
  $header.=' <br/>';
  $header.=$cons_data['city_name'].','. $cons_data['state_name'].','. $cons_data['country_name'];
  if(!empty($cons_data['cust_pincode'])){
    $header.='-'. $cons_data['cust_pincode'];
} 
}
$header.=' </td>
</tr>

</table>';

$footer = '<hr>';
/*$header ='
<table>
  <tr>
    <td colspan="3" style="border: 0px; "><img src="'.DOMAIN_F.LOGO.'logo.jpg" style="width:2.27in;padding-top:25px;" /></td>
    <td colspan="6" style="text-align:center;border: 0px;">
    <span style="font-size:16px;"><b>Sales Order Acknowledgement </b></span><br/>
    <span style="font-size:16px;"><b>'.$set_head['company_name'].' </b></span><br/>
    <span style="font-size:12px;">'.$comp_rel["address"].'</span>
    </td>
  </tr>
  </table>';*/
  $header ='
  <table >
  <tr style="border: 0px; ">
  <td  style="border: 0px; "><img src="'.DOMAIN_F.LOGO.$comp_rel['logo'].'" style="width:810px;height:100px;" /></td>

  </tr>
  <tr style="border: 0px; ">
  <td style="text-align:center;font-size:17px;font-weight:600px;"><b>Performa Invoice</b></td>
  </tr>
  </table>';

  $html.='<html>
  <head>          
  <title>Proforma Invoice - '.$rel['invoice_no'].'</title>

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
  .blueHeading {
    color: #365f91;
}

</style>
</head>
<body>
<!--Show Logo in other pages-->
<!--<htmlpageheader name="otherpages" style="display:none">
<div style="text-align:center">'.$header.'</div>
</htmlpageheader>-->
<!--<htmlpagefooter name="otherpages_footer" style="display:none">
<div style="text-align:center">'.$footer.'</div>
</htmlpagefooter>-->
<!--<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>-->
<div>


</div>
<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
<thead>
<tr>    
<td rowspan="3" style="width:50%;vertical-align:top;vertical-align:top;"><b>Buyer</b> <br>
'.$address.'
</td>
<td style="width:25%;vertical-align:top;"><b>Invoice No.</b> <br> '.$rel['invoice_no'].' </td>
<td style="width:25%;vertical-align:top;"><b>Dated</b> <br> '.date('d/m/Y',strtotime($rel['invoice_date'])).'</td>
</tr>
<tr>    

<td style="vertical-align:top;"><b>Delivery Note</b>  <br> '.$rel['delivery_note'].'</td>
<td style="vertical-align:top;"><b>Mode/Terms of Payment</b> <br> '.$rel['payment_terms'].' </td>
</tr>
<tr>    
<td style="vertical-align:top;"><b>Supplier\'s Ref</b> <br> '.$rel['supplier_ref'].'</td>
<td style="vertical-align:top;"> <b>Other Reference(s)</b> <br> '.$rel['other_reference'].' </td>
</tr>
<tr>    
<td rowspan="3" style="vertical-align:top;"><b>Delivery Address</b> 
<br>
'.$address.'
</td>
<td style="vertical-align:top;"> <b>Buyer\'s Order No</b> <br> '.$rel['order_no'].'</td>
<td><b>Dated </b> <br> '.$order_date.' </td>
</tr>
<tr>    

<td style="vertical-align:top;"> <b>Dispatch Document No.</b> <br> '.$rel['dispatch_document_no'].'  </td>
<td style="vertical-align:top;"><b>Dated </b>  <br> '. $dispatch_document_date.'  </td>
</tr>
<tr>    
<td style="vertical-align:top;"> <b>Dispatched through</b> <br> '.$rel['dispatched_through'].'</td>
<td style="vertical-align:top;"> <b>Destination </b>  <br> '.$rel['destination'].'</td>
</tr>
</thead>
</table>
<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
    <thead>
            <tr>
                <td width="10%" style="text-align:center;"><b>Sr No.</b> </td>
                <td width="40%" style="text-align:center;"><b>Description of Goods </b></td>
                <td width="15%" style="text-align:center;"><b>Qty</b> </td>
                <td width="15%" style="text-align:center;"><b>Unite Rate (INR)</b> </td>
                <td width="20%" style="text-align:center;"><b>Amount (INR)</b> </td>
            </tr>
        </thead>
<tbody>';

$qry="select trn.*,product.*,unit_name FROM `tbl_proforma_trn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trancation_id order by product_type,trancation_id";
$result=$dbcon->query($qry);    
$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_packing=0;$final_amount=0;$total_i_gst=0;$gst_per = 0;$gst_rate = 0;
$cnt=mysqli_num_rows($result);
while($row=mysqli_fetch_assoc($result))
{
    $gst_per = $row['cgst_tax_per']+$row['sgst_tax_per']+$row['igst_tax_per'];
    $gst_rate = $row['cgst_tax_rate']+$row['sgst_tax_rate']+$row['igst_tax_rate'];

    if($row['cgst_tax_rate'] != 0 || $row['sgst_tax_rate'] !=0){
        $total_cs_gst += $gst_rate;
    }else{
        $total_i_gst += $gst_rate;
    }
        //tax summary calculation start
    if(!empty($row['tax_val']))
    {
        $tax_num=explode(",",$row['tax_val']);
        $tax_name=explode(",",$row['tax_name']);
        $total_net_rate=($row['product_qty']*$row['product_rate'])-$row['discount'];
        for($j=0;$j<count($tax_num);$j++)
        {
            if(!in_array($tax_name[$j],$tax['per']))
            {
                $tax['per'][]=$tax_name[$j];
            }
            $tax['per_total'][$tax_name[$j]]+=$total_net_rate*$tax_num[$j]/100;
        }
    }

    $html.='<tr style="height:35px;border-bottom:none;border-top:none;">
    <td style="text-align:center; vertical-align:top;">'.$i.'</td>
    <td style="width:400px;border-right:1px solid;vertical-align:top;" >
    <strong>'.stripcslashes($row['product_name']).'</strong><br>';
    $html.= ($row['product_disc']!='' && $row['product_disc']!='0')?nl2br(stripcslashes($row['product_disc'])):'';
    $html.= '</td>
    <td style="vertical-align:top; border-right:1px solid; text-align:center" >'.$row['product_qty'].' '.$row['unit_name'].'</td>
    <td style="vertical-align:top;text-align:center" >'.number_format($row['product_rate'],2,".","").'</td>
    <td style=" vertical-align:top;text-align:right">'.number_format($row['product_amount'],2,".","").'</td>
    </tr>';

$i++; 
$totalqty=$totalqty+$row['product_qty']-$charges_qty;
$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
$total_product_amount+=($row['product_qty']*$row['product_rate']);
$totaltaxable+=$row['product_amount'];
$total+=$total_net_rate;
$total_packing +=$rel['packing'];

$final_amount = $totaltaxable+$total_packing;

}
$pr=5-$cnt;

for($j=0; $j<$pr; $j++){

  /* $html.='<tr style="height:35px;border-bottom:none;border-top:none;">
      <td style=""></td>
      <td style=""></td>
      <td style=""></td>
      <td style=""></td>
      <td style=""></td>
      <td style=""></td>
      </tr>';*/

  }
  $html.='<tr>
    <td colspan="4" style=" text-align:right;">Total Amount  </td>
    <td  style=" text-align:right;">'.indian_number($totaltaxable,2).'</td>
    </tr>';
    if($rel['stateid']==$comp_rel['stateid']){
        $html.='<tr>
        <td colspan="4" style=" text-align:right;">CGST '.($gst_per/2).' %</td>
        <td  style=" text-align:right;">'.number_format(($total_cs_gst/2),2,".","").'</td>
        </tr><tr>
        <td colspan="4" style=" text-align:right;">SGST '.($gst_per/2).' %</td>
        <td  style=" text-align:right;">'.number_format(($total_cs_gst/2),2,".","").'</td>
        </tr>';
    }else{
        $html.='<tr>
        <td colspan="4" style=" text-align:right;">IGST '.($gst_per).' %</td>
        <td  style=" text-align:right;">'.number_format(($total_i_gst),2,".","").'</td>
        </tr>';
    }
    $qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_proforma_trn as trn 
    left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
    left join tbl_ledger as l on l.l_id=tc.tax_id 
    where tc.tax_additional='1' and trn.trancation_id=".$invoiceid." and trn.trancation_status!=2 and tc.isdelete='0' group by tc.tax_id";
    $result11=$dbcon->query($qry11);        
    while($row11=mysqli_fetch_assoc($result11))
    {
        $html.='<tr>
        <td colspan="4" style=" text-align:right;">'.$row11['l_name'].'</td>
        <td  style=" text-align:right;">'.number_format($row11['add_sum'],2,".","").'</td>
        </tr>';
    }
    $qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
    from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
    left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
    where b.sundry_voucher_id=".$invoiceid." and b.sundry_voucher_table='tbl_proforma_invoice' and b.isdelete='0' and le.default_sundry='0'";
    $result12=$dbcon->query($qry12);        
    while($row12=mysqli_fetch_assoc($result12))
    {
        $html.='<tr>
        <td colspan="4" style=" text-align:right;">'.$row12['l_name'].'</td>
        <td  style=" text-align:right;">'.number_format($row12['sundry_amount'],2,".","").'</td>
        </tr>';
    }
    $round_off = round($rel['g_total'])-$rel['g_total'];
    $html .= '<tr>
    <td colspan="4" style=" text-align:right;font-size:14px;">Round Off  </td>
    <td  style=" text-align:right;font-size:14px;">'.indian_number($round_off,2).'</td>
    </tr>
    <tr>
    <td colspan="4" style=" text-align:right;font-size:14px;">Amount Payable </td>
    <td  style=" text-align:right;font-size:14px;font-weight:bold;">'.indian_number(($rel['g_total']),2).' </td>
    </tr>
    </tbody></table>
    <table style="page-break-inside: avoid;">
    <tr style="">
    <td style=" text-align:left;font-size:14px;">
    Amount payable in words: <span style="font-weight:bold;font-size:13px;">'.convert_number_to_words_new($rel['g_total']).'</span></td>
    </tr>
    <tr style="">
    <td style="border-bottom: 1px;text-align:left;font-size:14px;">
    Remarks :'.$rel['remark'].'</td>
    </tr>
    <tr>
    <td><strong>Terms & Conditions</strong></td>
    </tr>
    <tr>
    <td>'.(($rel['terms_condition']) ? $rel['terms_condition'] : '').'</td>
    </tr>
    </table>
    <table style="page-break-inside: avoid;" >';
    $html.='
    <tr>
    <td rowspan="2" style=" text-align:left;font-size:14px;height:130px">
    <span><b>Company\'s GST No : '.$comp_rel['vatno'].'</b></span>
    <br>
    <span><u><b>Payments to be deposited in Yes Bank as per following details</b></u></span>
    <br>
    <span><b>'.$comp_rel["company_name"].'</b></span>
    <br>
    <span>Bank Name : '.$comp_rel["bank_name"].'</span>
    <br>
    <span>A/c No : '.$comp_rel["ac_no"].'</span>
    <br>
    <span>IFSC Code : '.$comp_rel["ifcs"].'</span>
    <br>
    <span>Branch : '.$comp_rel["branch_name"].'</span>
    </td>
    <td style=" text-align:right;font-size:14px;font-weight:bold">
    For , '.$comp_rel["company_name"].'</td>
    </tr>
    <tr style="border-top: 0px; ">
    <td style=" text-align:right;">
    <img src="'.DOMAIN_F.'view/upload/signature/'.$comp_rel['authorized_signature'].'" height="70" width="150" class="img-thumbnail" />
    <br>
    Authorized Signature</td>
    </tr>';
    $html.='</table>';
$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
</body>
</html>';
$file_name = $rel['invoice_no'].'.pdf';
// stripcslashes(str_replace(array("\n", "\r", "\N"), '', $POST['product_desc']));
$file_name=str_ireplace(array("-","/"),"_",$file_name);
ob_end_clean();
include("../../view/export/mpdf/mpdf.php");
$mpdf=new mPDF('','A4','0','calibri','10','10','40','2','1','1');
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
if($save_file=="No"){
    $mpdf->Output();
}else{
    $mpdf->Output('../../view/upload/mail_attach/'.$file_name,'f');
}
ob_clean();
return $file_name;
} 
}
?>