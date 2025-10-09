<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");

$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
        FINANCE_PROFORMA_INVOICE_PRINT
    ]);
    
if(!in_array(FINANCE_PROFORMA_INVOICE_PRINT,$bulkAccessArray)){
  header("Location: ".DOMAIN."permission_access");
}
        
/*if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}*/
        

$type='pdf';
if(strtolower($type) == 'pdf') {
$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
$que = "select performa_invoice_type from tbl_proforma_invoice where invoice_id=$invoiceid";
$rel_que=mysqli_fetch_assoc($dbcon->query($que));

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
 //echo "<pre>";print_r($rel);die();
 
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



/* Check Discount is On or off Start */
if($set_head['show_disc']=='1'){
$colspan=5;
$dynamicwidth=40;
}else{
$colspan=6;
$dynamicwidth=46;
}
/* Check Discount is On or off End */

$html='';
$header ='
<table  class="maintable headermain " style="border-radius: 10px;border-collapse: separate;border-color: black;margin-top: 23px;    padding: 17px 0 0;border: 1px solid;" id="table_head" width="100%">
    <tr style="border:none;">
        <td style="border:none;">
            <div width="30%" style="float:left;width: 250px;">
                <img src="'.ROOT.LOGO.'Final.jpg"  style="width:210px;height:100px;"/>
            </div>
        </td>
        <td style="border:none;">
            <div width="50%" style="float:right; width: 300px;">
                <h2 style="margin-bottom:20px;" align="left">'.$set_head['company_name'].'</h2>
                <h5 align="left" style="padding-top:8px;">'.$set_head['logo_content'].'</h5>
                <h5 style="font-size:19px; margin-bottom:0px;" align="left">'.$set_head['address'].'</h5>
            </div>
        </td>
    </tr>
  </table>
<table style="text-align:center;border:none">
    <tr>
       <td> 
          <strong style="font-size:16px;text-align:center;border:none">
          <span> PROFORMA INVOICE </span>
          </strong>
       </td>
    </tr>
    </table>

    <table>
  <tr id="rawnone">
       <td colspan="4" rowspan="5" width="50%" style="border:1px solid;vertical-align:top;">
          <span style="font-weight:bold;font-size: 14px;text-decoration: underline;">Consignee : </span><br>
          <strong style="font-weight:normal;font-size: 14px;font-weight: bold">'.$rel['company_name'].'</strong>
          <span style="font-weight:normal;font-size: 14px;">   
          <br/>';
           if($rel['quot_address']!='' && $rel['quot_address']!='0'){ 
             $header.= $rel['quot_address'];
            $header.='<br/>';
           }else{ 
            $header.= $rel['cust_address'];
            $header.=' <br/>';
            $html.=$rel['city_name'].','. $rel['state_name'].','. $rel['country_name'];
            if(!empty($rel['cust_pincode'])){
            $header.='-'. $rel['cust_pincode'];
             } 
          }
         $header.='</span>
          <br>
          <br>
          <span style="font-weight:bold;font-size: 14px"> GSTIN: '.$cons_gst_no.'</span>
          <br>
          <span style="font-weight:bold;font-size: 14px"> PAN No. '.$cons_pan_no.'</span>   
       </td>
       
       <td style="border:1px solid;font-weight:normal;font-size: 14px;" width="25%">
          <b>P.I. No.</b>
       </td>
       <td style="border:1px solid;font-weight:normal;font-size: 14px;" width="25%">
          '.$rel['invoice_no'].'
       </td>
    </tr>
    <tr>
       <td style="border:1px solid;font-weight:normal;font-size: 14px;">
          <b>P.I. Date</b>
       </td>
       <td style="border:1px solid;font-weight:normal;font-size: 14px;">'.date('d/m/Y',strtotime($rel['invoice_date'])).'</td>
    </tr>
    <tr>
       <td style="border:1px solid;font-weight:normal;font-size: 14px;">
          <b>P.O. No.</b>
       </td>
       <td style="border:1px solid;font-weight:normal;font-size: 14px;">
          '.$order_no.'
       </td>
    </tr>
    <tr>
       <td style="border:1px solid;font-weight:normal;font-size: 14px;">
          <b>P.O. Date</b>
       </td>
       <td style="border:1px solid;font-weight:normal;font-size: 14px;">
          '.$order_date.'
       </td>
    </tr>
    <tr>
       <td colspan="2" style="border:1px solid;font-weight:normal;font-size: 14px;">
          <span style="font-weight:bold;font-size: 14px">Delivery At</span>
          <br>';
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
<br><br>
<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
    <thead>
    <tr>
        <th width="3%" style="text-align:center;border:1px solid;border-top: none;"><strong>SR.<br/> NO.</strong></th>
        <th width="20%" style="width:400px;text-align:center !important; border:1px solid;border-top: none;font-size:14px;" >
           <strong>Particulars </strong>
        </th>
        <th width="7%" style="float:right !important;border:1px solid;border-top: none;font-size:14px;text-align:right">
           <strong>Quantity</strong>
        </th>
        <th width="7%" style="float:right !important;border:1px solid;border-top: none;font-size:14px;text-align:right">
           <strong>UOM</strong>
        </th>
        <th width="7%" style="float:right  !important;border:1px solid;border-top: none;font-size:14px;text-align:right">
           <strong>Rate</strong>
        </th>
        <th width="10%" style="float:right  !important;border:1px solid;border-top: none;font-size:14px;text-align:right">
           <strong>Amount</strong>
        </th>
     </tr>
    </thead>
    <tbody>';

    $qry="select trn.*,product.*,unit_name,group_concat(tax.tax_value) as tax_val,group_concat(tax.tax_name) as tax_name FROM `tbl_proforma_trn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id 
    left join `formula_mst` as ftax on ftax.formulaid=trn.formulaid left join tbl_tax as tax on find_in_set(tax.tax_id,ftax.tax_id)
    where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trancation_id order by product_type,trancation_id";
    $result=$dbcon->query($qry);    
    $i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_packing=0;$final_amount=0;
    $cnt=mysqli_num_rows($result);
    while($row=mysqli_fetch_assoc($result))
    {
    $tax_arr=explode(",",$row['tax_val']);

    //tax summary calculation start
    if(!empty($row['tax_val']))
    {
    $tax_num=explode(",",$row['tax_val']);

    $tax_name=explode(",",$row['tax_name']);

    $total_net_rate=($row['product_qty']*$row['product_rate']);
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
      <td style="text-align:center !important; vertical-align:top;">';
         if($row['product_type']!='8'){ $html.= $i;}
      $html.='</td>';
      $clas = '';
      if($row['product_type']=='8'){
        $clas = 'text-align:right;padding-top:5px;vertical-align:top;';
      }
      $html.='<td style="width:400px;font-size:14px;border-right:1px solid;vertical-align:top;'.$clas.'" >
      <strong>'.stripcslashes($row['product_name']).'</strong><br/>';
      $html.= ($row['product_disc']!='' && $row['product_disc']!='0')?nl2br(stripcslashes($row['product_disc'])):'';
      $html.= '</td>
      <td style="vertical-align:top; border-right:1px solid;white-space:nowrap;font-size:14px;float:right;text-align:right" >';
         if($row['product_type']!='8'){ 
             $html.=$row['product_qty'];
         }else{
            $charges_qty+=$row['product_qty'];
        } 
      $html.='</td>
      <td style="vertical-align:top;font-size:14px;float:right !important;text-align:right" >';
      if($row['product_type']!='8'){ 
         $html.=$row['unit_name'];
      }
      $html.='</td>
      <td style="vertical-align:top;font-size:14px;float:right !important;text-align:right" >';
      if($row['product_type']!='8'){ 
             $html.=number_format($row['product_rate'],2,".","");
       }
      $html.='</td>
      <td style=" vertical-align:top;font-size:14px;float:right !important;text-align:right">
         '.number_format($row['product_amount'],2,".","").'
      </td>
   </tr>';
   
      $i++; 
        $totalqty=$totalqty+$row['product_qty']-$charges_qty;
        $totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
        $total_product_amount+=($row['product_qty']*$row['product_rate']);
        $totaltaxable+=$row['product_amount'];
        $totaltax1+=$row['tax_amount1'];
        $totaltax2+=$row['tax_amount2'];
        $total+=$total_net_rate;
        $total_packing +=$rel['packing'];
      
        $final_amount = $totaltaxable+$totaltax1+$totaltax2+$total_packing;
      
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
$html.='</table>
<table style="font-size:14px;border-collapse: collapse;width:100%;page-break-inside: avoid;" cellpadding="3" cellspacing="3">
<tr>
    <td colspan="4" rowspan="3" width="50%" style="border:1px solid">
       <span style="font-weight:bold;font-size: 14px;">Amount In Words Rupees</span><br>
       <span style="font-weight:bold;font-size: 14px;">   
       '.ucwords(convert_number_to_words_new($totaltaxable+$totaltax1+$totaltax2)).'
       </span>
       <br>
       <br>
       <span style="font-weight:bold;text-decoration: underline;font-size: 14px;"> Our Bankers</span>
       <br>
       <span style="font-weight:bold;font-size: 14px"> Bank Name: </span> <span style="font-size:14px;"> HDFC BANK LIMITED</span>
       <br>
       <span style="font-weight:bold;font-size: 14px"> Bank Address: </span> <span style="font-size:14px;">HDFC BANK</span>
       <br>
       <span style="font-weight:bold;font-size: 14px"> A/c No.: </span> <span style="font-size:14px;"> 50200004838567</span>
       <br>
       <span style="font-weight:bold;font-size: 14px"> IFSC Code: </span> <span style="font-size:14px;"> HDFC0001831</span>
       <br>
       <span style="font-weight:bold;font-size: 14px"> SWIFT Code: </span> <span style="font-size:14px;">  </span>
       <br>
       <span style="font-weight:bold;font-size: 14px"> MICR Code: </span> <span style="font-size:14px;"> </span>   
    </td>
    <td style="border:1px solid;font-weight:normal;font-size: 14px;" width="25%">
       <b>Gross Total</b>
    </td>
    <td style="border:1px solid;font-size: 14px;text-align: right;font-weight: bold;" width="25%">
       '.number_format($totaltaxable,2,".","").'
    </td>
 </tr>
 <tr>
    <td style="border:1px solid;font-weight:normal;font-size: 14px;">
       <span style="float:left !important;text-align:left; width:50%">';
        if($rel['stateid']==$set_head['stateid']){
            $html.= 'CGST';
          }else{ 
            $html.='IGST';
          }
        $html.='</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
       <span style="float: right !important;text-align:right !important;width:50%">'.number_format($tax_num[0],2,".","").'</span>';
       if($tax_name[1]) { 
       $html.='<br>   
       <span style="float:left !important;text-align:left;width:50%">';
          $strt=$tax_name[1];
          $position = strpos($strt, "TCS", 0);
          if ($position == true){ 
            $html.= $tax_name[1];
          } else{
            $html.= 'SGST';  
          } 
       $html.='</span>
       &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
       <span style="float: right !important;text-align:right">'.number_format($tax_num[1],2,".","").'</span>';
       }  
       $html.='<br>
       
    </td>
    <td style="border:1px solid;font-weight:normal;font-size: 14px;float:right !important;text-align:right">
       
       <span style="float:right !important;text-align:right">'.number_format($totaltax1,2,".","").'</span>';
       if($tax_name[1]) {
          $html.='<br><span style="float:right !important;text-align:right">'.number_format($totaltax2,2,".","").'</span>';
        } 
       $html.='
    </td>
 </tr>
 <tr>
    <td style="border:1px solid;font-weight:normal;font-size: 14px;height: 100px;vertical-align: bottom;">
       <span style="font-weight:bold;font-size: 14px;float: left;">Total Amount</span>
    </td>
    <td style="border:1px solid;font-weight:normal;font-size: 14px;height: 100px;vertical-align: bottom;float:right !important;text-align:right">
       <span style="font-weight:bold;font-size: 14px;float:right !important;text-align:right">'.number_format($final_amount,2,".","").'</span>
    </td>
 </tr>
 <tr id="rawnone">
    <td colspan="6" width="50%" style="border:1px solid">
       <span style="font-weight:bold;font-size: 14px"> Payment Terms : '.$rel['payment_terms'].' </span>
    </td>
 </tr></tbody></table>';

 $html.='<table><tr id="rawnone" style="float:left;border:none;border-left:1px solid;border-right:1px solid;border-bottom:1px solid;font-size: 16px" >
    <td colspan="3" style="border-bottom:1px solid !important;border-right:1px solid;font-weight:normal;font-size: 14px;" >
       <span style="font-weight:bold;font-size: 14px;text-decoration: underline;">Consignors Tax Details</span><br>
       <span style="font-weight:bold;font-size: 14px"> GSTIN:  </span> <span>03AAEFD6029Q1Z9</span>
       <br>
       <span style="font-weight:bold;font-size: 14px"> PAN No.: </span> <span> </span>
       <br>
    </td>
    <td colspan="3" width="50%" style="vertical-align:top;float: right !important;text-align:right;border-bottom:none !important;">
       <span style="font-weight:bold;font-size: 14px;">For '.$set_head['company_name'].'</span><br>
       <br><br>
        <span style="font-weight:bold;font-size: 14px;"> AUTHORISED SIGNATORY  
       </span>
    </td>
 </tr>
 ';
$html.='</table>';


/*$html.='';*/



/* Get Terms And Condition Start */

/*Perfoma Content Print Start*/
if(!empty($rel['perfoma_condition'])){
   $html.= '<pagebreak page-break-type="clonebycss" /><br><br>
		   <table style="text-align:center;border:none">
		    <tr>
		       <td> 
		          <strong style="font-size:16px;text-align:center;border:none">
		          	<span> TERMS & CONDITIONS </span>
		          </strong>
		       </td>
		    </tr>
		    <tr style="float:left; text-align:left; border:1px solid;">
		       <td style="text-align:left;">'.$rel['perfoma_condition'].'</td>
		    </tr>
		    </table>';
}
/*Perfoma Content Print End*/

$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
</body>
</html>';
// echo $header;
// echo $html;exit;
    ob_end_clean();
    include("../../view/export/mpdf/mpdf.php");
    $mpdf=new mPDF('','A4','0','calibri','10','10','85','2','1','1');
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
    return 'Proforma Invoice'.$rel['invoice_no'].'.pdf';
  } 
?>
