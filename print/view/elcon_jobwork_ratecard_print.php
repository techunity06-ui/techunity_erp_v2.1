<?php 
require_once '../../vendor/autoload.php';
use Mpdf\Mpdf;
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");        
// error_reporting(E_ALL);
$type='pdf';
if(strtolower($type) == 'pdf') {
    $purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
    $query="SELECT po.*,l.ledger_code,state.state_name,l.l_name as vender_name, country.country_name, l.m_address as vender_address, l.gst_no as tin_no, l.cust_mobile as vender_mobile, l.stateid, state.gst_state_code, city.city_name, l.cust_pincode, l.m_pan, l.cust_email 
    from tbl_jobwork_rate_card as po 
    inner join tbl_ledger as l on l.l_id=po.party_id
    left join country_mst as country on country.countryid=l.countryid
    left join state_mst as state on state.stateid=l.stateid
    left join city_mst as city on city.cityid=l.cityid
    where po.jobwork_card_id=".$purchaseorder_id . " AND po.company_id=".$_SESSION['company_id'];
    $result = $dbcon->query($query);
    $rel=mysqli_fetch_assoc($result);
 
    // echo "<pre>"; print_r($rel); echo "</pre>";
    $_SESSION['invoice_no']=$rel['jobwork_card_no'];
    // if ($rel) {
    //     $_SESSION['invoice_no'] = $rel['jobwork_card_no'];
    // } else {
    //     die("No jobwork rate card found for ID: " . $purchaseorder_id);
    // }

    $set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id='".$rel['company_id']."'";
    $set_head=mysqli_fetch_assoc($dbcon->query($set));	
		// echo "<pre>";print_r($set_head);die();
    $order_date='';
    if($rel['jobwork_card_date']!="1970-01-01" && $rel['jobwork_card_date']!="0000-00-00")
    {
     $order_date=date('d/m/Y',strtotime($rel['jobwork_card_date']));
    }
 
 $cons_vender_code  = $rel['ledger_code'];
 $cons_company_name	= $rel['vender_name'];
 $cons_cust_address	= nl2br(stripcslashes($rel['vender_address']));
 $cons_gst_no		= ($rel['tin_no']!='0')?$rel['tin_no']:'';
 $cons_state_name	= $rel['state_name'];
 $cons_gst_state_code= $rel['gst_state_code'];
 $cons_city_name		= $rel['city_name'];
 $cons_country_name	= $rel['country_name'];
 $cons_cust_pincode  = $rel['cust_pincode'];
 $cons_vender_mobile  = $rel['vender_mobile'];
 $cons_m_pan  = ($rel['m_pan']!='0')?$rel['m_pan']:'';
 $cons_cust_email  = $rel['cust_email'];

		//consignee
 /*if(!empty($rel['consignee_id']))
 {	
     $consignee="select * from tbl_custmer_consignee as cust 
     left join country_mst as country on country.countryid=cust.countryid
     left join state_mst as state on state.stateid=cust.stateid 
     left join city_mst as city on city.cityid=cust.cityid where cust_id=".$rel['consignee_id'];
     $cons_data=mysqli_fetch_assoc($dbcon->query($consignee));	
     $cons_company_name=$cons_data['company_name'];
     $cons_cust_address=nl2br(stripcslashes($cons_data['cust_address']));
     $cons_gst_no=$cons_data['gst_no'];
     $cons_state_name=$cons_data['state_name'];
     $cons_gst_state_code=$cons_data['gst_state_code'];
     $cons_city_name=$cons_data['city_name'];
     $cons_country_name=$cons_data['country_name'];
     $cons_cust_pincode  = $cons_data['cust_pincode'];
     $cons_vender_mobile  = $cons_data['cust_mobile'];
     $cons_m_pan  = '';

 }*/

 $us_sql = "select user_name from users where user_id='".$rel['user_id']."'";
 $user_rel=brp_mysqli_fetch_assoc($dbcon->query($us_sql));

 $remark = ($rel['remark']!='' && $rel['remark']!='0')?$rel['remark']:'';
 /* Check Discount is On or off Start */
 if($set_head['show_disc']=='1'){
     $colspan=5;
     $dynamicwidth=40;
 }else{
     $colspan=6;
     $dynamicwidth=46;
 }
 /* Check Discount is On or off End */

		/*
		Get Indent No.
		*/
		/*$potrn = "select potrn.purchaseordertrn_id, poreq.req_id,rp.indent_no  from `tbl_purchaseordertrn` as potrn 
      left join tbl_purchaseorder_req_trn as poreq on poreq.purchaseordertrn_id=potrn.purchaseordertrn_id 
      left join tbl_request_product as rp on rp.rp_id=poreq.req_id 
      where potrn.purchaseorder_id=".$purchaseorder_id." and potrn.purchaseordertrn_status=0";
      $potrn_exec = $dbcon->query($potrn);
      $indent_no_data = '';				
      if(brp_mysqli_num_rows($potrn_exec) > 0){
         $indent_arr = [];
         while($potrn_data=brp_mysqli_fetch_assoc($potrn_exec)){
            $indent_arr[] = $potrn_data['indent_no'];
        }
    }				
    $indent_no_data = implode(',', $indent_arr);*/	

    /*END*/

    $html='';

    $header ='<img src="'.DOMAIN_F.LOGO.'elcon2.jpg" style="width:8.27in" />';


    $html.='<html>
    <head>          
    <title>Open Purchase Order - '.$rel['jobwork_card_no'].'</title>

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
<table>
<tr>
<td colspan="9" style="border: 0px; font-size:16px;font-weight:bold;text-align:center">OPEN PURCHASE ORDER</td>
<td style="border: 0px; font-size:13px;text-align:center">DRAFT COPY</td>
</tr>

<tr style="border:none; border-left:1px solid; border-right:1px solid;border-bottom:1px solid; ">
<td width="50%" colspan="6" rowspan="4"  style=" text-align:left;vertical-align:top">
<b>Supplier Code :- '.$cons_vender_code.'</b><br>
<b> '.$cons_company_name.'</b>
<br>
'.$cons_cust_address.'<br>
'.$cons_city_name.' - '.$cons_cust_pincode.' ('.$cons_state_name.') - '.$cons_country_name.'<br>
Tel No.: '.$cons_vender_mobile.'   <br>Fax No.: -<br>
E-mail: '.$cons_cust_email.' <br>
</td>
<td width="13%" style="border:none"><b>P.O. No. </b></td>
<td width="13%" style="border:none;white-space:nowrap">: '.$rel['jobwork_card_no'].'</td>
<td width="13%" style="border:none"><b>Date </b></td>
<td width="13%" style="border:none">: '.$order_date.'</td>

</tr>
<tr style="border:none; border-left:1px solid;border-right:1px solid;  ">
<td style="border:none;white-space:nowrap"><b>Quot. Ref. No. </b></td>
<td style="border:none">: '.(($rel['quot_ref']!='0')?$rel['quot_ref']:'').'</td>
<td style="border:none"><b>Date </td>
<td style="border:none">: '.$order_date.'</td>
</tr>

<tr style="border:none; border-right:1px solid;border-bottom:1px solid; ">
<td style="border:none"><b>Buyer </b></td>
<td colspan="3" style="border:none">: '.$user_rel['user_name'].'</td>
</tr>
<!--<tr style="border:none; border-right:1px solid;border-bottom:1px solid; ">
<td style="border:none"><b>Indent No. </b></td>
<td colspan="3" style="border:none">:'.$indent_no_data.'</td>
</tr>-->
<tr style="border:none; border-left:1px solid; border-right:1px solid; ">
<td style="border:none;white-space:nowrap"><b>Delivery Address :</b></td>
<td colspan="3" style="border:none">
GAT NO.21, H NO.253,VARVE BK<br>
TAL. BHOR , DIST PUNE 412210 <br>
Pune - 412210 (MAHARASHTRA) - INDIA
</td>

</tr>
<tr style="vertical-align:top">
    <td colspan"2" style="text-align:center;vertical-align:top">
        SUPPLIER ECC NO.<br>
    </td>

    <td colspan="2" style="text-align:center;white-space:nowrap;vertical-align:top">
        SERVICE TAX NO.<br>
    </td>

    <td colspan="3" style="text-align:center;vertical-align:top">
        DIVISION<br>
    </td>

    <td colspan="2" style="text-align:center;vertical-align:top">
        GST NO.<br>
        '.$cons_gst_no.'
    </td>

    <td colspan="2" style="text-align:center;vertical-align:top">
        PAN NO.<br>
        '.$cons_m_pan.'
    </td>
</tr>

<tr>
<td colspan="10">Please supply the following goods subject to terms and conditions stated below.</td>
</tr>

</table>
</div>
<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
<thead>
<tr style="background-color:#b3b3b3;vertical-align:top">
<td style=" text-align:center;width:0px"><b>SR. NO.</b> </td>
<td colspan="2" style=" text-align:left;width:250px"><b>ITEM CODE  &  DESCRIPTION </b></td>
<td style=" text-align:center;"><b>HSN CODE</b> </td>
<td style=" text-align:center;"><b>UNIT</b> </td>
<td style=" text-align:center;"><b>QUANTITY</b> </td>
<td style=" text-align:center;"><b>BASIC RATE<br>RS / UNIT  </b> </td>
<td style=" text-align:center;"><b>DISC. (%)</b> </td>
<td style=" text-align:center;"><b>NET RATE / UNIT</b> </td>
<td style=" text-align:center;"><b>AMOUNT RS</b> </td>
</tr>
</thead>
<tbody>';

$qry="select trn.*, product.*, product.product_desc as scode, per.unit_name, proc.process_name, hsn.hsn_code FROM `tbl_jobwork_rate_cardtrn` as trn 
left join product_mst as product on product.product_id=trn.product_id 
left join unit_mst as per on per.unitid=trn.unit_id 
left join process_mst as proc on proc.process_id = trn.process_id
left join mst_hsn_code as hsn on hsn.hsn_id = product.product_hsn
where trn.jobwork_cardtrn_status=0 and trn.jobwork_card_id='".$rel['jobwork_card_id']."' group by trn.jobwork_card_trnid order by trn.jobwork_card_trnid";
$result=$dbcon->query($qry);		
$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;
$cnt=brp_mysqli_num_rows($result);
$total_product_amount = 0;
while($row=brp_mysqli_fetch_array($result))
{

	/*if($row['product_base_unit']!=$row['product_conv_unit']){
		//base_unit_name,per2.unit_name as conv_unit_name
		if($row['unit_id']==$row['product_base_unit']){
			$cqty=convert_stock($dbcon,$row['product_qty'],$row['product_id'],"conv_unit");
			$uname=$row['conv_unit_name'];
		}else{
			$cqty=convert_stock($dbcon,$row['product_qty'],$row['product_id'],"base_unit");
			$uname=$row['base_unit_name'];
		}
	}
	$tax_arr=explode(",",$row['tax_val']);
	//tax summary calculation start
	if(!empty($row['tax_val']))
	{
		$tax_num=explode(",",$row['tax_val']);
		$tax_name=explode(",",$row['tax_name']);
		//$total_net_rate=($row['product_qty']*$row['product_rate'])-$row['discount'];
		$total_net_rate=($row['product_amount']);
		for($j=0;$j<count($tax_num);$j++)
		{
			if(!in_array($tax_name[$j],$tax['per']))
			{
				$tax['per'][]=$tax_name[$j];
			}
			$tax['per_total'][$tax_name[$j]]+=$total_net_rate*$tax_num[$j]/100;
		}
	}*/
	$description = ($row['description']!='' && $row['description']!='0')?$row['description']:'';
	//tax summary calculation end
	//$taxable_amt=$row['total']-$row['product_amount'];
    $disc = ($row['price'] * $row['discount_percentage'])/100;
    $disc_amt = $row['price']-$disc;
  $html.='
  <tr>
  <td style="text-align:center;font-size:12px;">'.$i.'</td>
  <td colspan="2" style="border: none;text-align:left;font-size:12px;">
  <span style="font-weight:bold;"><u>'.$row['product_icode'].'</u></span><br>
  <span style="font-weight:bold;">'.$row['product_name'].'</span><br>
  Operation :- '.$row['process_name'].'<br>           
  '.stripcslashes($description).'
  </td>
  <td style=" text-align:center;font-size:12px;font-weight:bold"> '.stripcslashes($row['hsn_code']).'</td>            
  <td style=" text-align:center;font-size:12px;"> '.$row['unit_name'].'</td>
  <td style=" text-align:center;font-size:12px;">OPEN </td>
  <td style=" text-align:right;font-size:12px;">'.number_format($row['price'],2,".","").'</td>
  <td style=" text-align:right;font-size:12px;">'.$disc.' ('.$row['discount_percentage'].'%).'.'</td>
  <td style=" text-align:right;font-size:12px;">'.number_format($disc_amt,2,".","").'</td>
  <td style=" text-align:right;font-size:12px;">'.number_format($row['product_amount'],2,".","").'</td>
  </tr>';

  $i++; 
 $totalqty=$totalqty+$row['product_qty']-$charges_qty;$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
  $total_product_amount= $total_product_amount + $row['product_amount'];

  $totaltaxable+=$taxable_amt;
  $totaltax1+=$row['tax_amount1'];
  $totaltax2+=$row['tax_amount2'];
  $total+=$row['total'];
}

for($j=0; $j<8; $j++){

 $html.='<tr style="height:35px;border-bottom:none;border-top:none;">
 <td style=""></td>
 <td colspan="2"  style=""></td>
 <td style=""></td>
 <td style=""></td>
 <td style=""></td>
 <td style=""></td>
 <td style=""></td>
 <td style=""></td>
 <td style=""></td>
 </tr>';

}
$html .= '</table>
</div>
<div>
<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
<tr>
<td colspan="3" style="font-size:14px;border:none;font-weight:bold">
TERMS & CONDITIONS : 
</td>
<td colspan="2" style="font-size:14px;text-align:right;border:none;font-weight:bold">
TOTAL  : 
</td>
<td  style=" text-align:center;font-size:14px;font-weight:bold">'.number_format($totalqty,2,".","").'</td>
<td></td>
<td></td>
<td></td>
<td style=" text-align:center;font-size:14px;font-weight:bold">'.number_format($total_product_amount,2,".","").'</td>
</tr>

<tr>
<td colspan="7" rowspan="3" style=" text-align:left;font-size:12px;height:auto">
1. Please  mention  PO  No.,  Item  Code  on  your  challan  /  Invoice.<br>
2. You  shall  be  liable  for  any  damages   of  the  goods  &  expenses  incurred   on  account  of  improper   packing&  for  any  cost  attributable   to  improper   protective  measures   taken  by  you  in  regard   to  packing.<br>
3. Suppied  quantity  must  not  exceed  the  one  stipulated  in  our  Purchase  Order  unless  requested  by  us  in  writing.<br>
4. The Price indicated in the order shall hold good its complete execution.<br>
5. All Deliveries must be  as per schedule stipulated in our Purchase Order. We reserve the right to return the supplies made in advanced or beyond  the delivery schedule.<br>
6. All  material  should  supply  along  with  the  related  Test  Certificate,  without  it  GRN  should  not  be  clear.
</td>
<td colspan="2" style=" text-align:right;font-size:14px;border:none">

</td>
<td style=" text-align:right;font-size:14px;border:none;">
</td>
</tr>
<tr style="border:none;border-right:1px solid">
<td colspan="2" style="border:none;border-left:1px solid">';
/*foreach($tax['per_total'] as $key => $value){
    if($key)
        $html.= $key.'<br>';
}*/
$html .= '</td>
<td style="border:none">';
/*$final_total = $total_product_amount;
foreach($tax['per_total'] as $keyq => $val){
   if($val){
       $html.= number_format($val,2,".","").'<br>';
       $final_total += $val;
   }
}*/
$html .= '</td>
</tr>


<tr>
<td colspan="2"  style="border:none">Total</td>
<td  style="border:none;font-size:14px;font-weight:bold">'.$final_total.'</td>
</tr>
<tr>
<td colspan="7" style="font-weight:bold;font-size:14px">RS : '.ucwords(convert_number_to_words_new($final_total)).'</td>
<td colspan="3" style="font-weight:bold;font-size:14px;background-color:#b3b3b3">FINAL AMOUNT (RS) : '.number_format($final_total,2,".","").'</td>
</tr>
<tr>
<td colspan="10" ><span style="font-weight:bold;font-size:14px;text-decoration:underline">REMARKS : </span>'.$remark.'</td>
</tr>
<tr>
<td colspan="10">
<span style="font-weight:bold;font-size:14px;text-decoration:underline">Supply  Terms & Conditions : </span><br>
'.(($rel['terms_condition']!='0')?$rel['terms_condition']:'').'
</td>
</tr>
<tr>
<td colspan="10" style="font-weight:bold;font-size:18px;text-align:center">NOTE- DO NOT SEND COMBINE BILL AGAINST MULTIPLE PO.(EX-ONE TAX ONE BILL)</td>
</tr>

<tr>
<td colspan="5" style="border:none;margin-top:30px;height:60px;vertical-align:bottom">
'.$user_rel['user_name'].'<br>
<span style="font-weight:bold">Prepared By </span>
</td>
<td colspan="5" style="text-align:right;border:none;font-weight:bold;padding-right:30px !important">
<!--<img src="' . DOMAIN_F . LOGO . 'sign.png" height="80" width="80"/>--><br>
<br>
Authorised Signatory 
</td>
</tr>
<tr>

<td style="font-weight:bold;text-align:center">
ELCON ECC NO.<br>
AACCE9652MEM002
</td>
<td colspan="2" style="font-weight:bold;text-align:center">
    SERVICE TAX NO.<br>
    AACCE9652MSD002
</td>
<td colspan="2" style="font-weight:bold;text-align:center">
DIVISION<br>
IV (PURANDAR)
</td>
<td colspan="3" style="font-weight:bold;text-align:center">
GST NO.<br>
27AACCE9652M1Z3
</td>
<td colspan="2" style="font-weight:bold;text-align:center">
PAN NO.<br>
AACCE9652M
</td>
</tr>
';

$html.='</tbody></table>';





/* Get Terms And Condition Start */

/*Annexure Content Print Strat*/
  /*if(!empty($rel['quot_annex_content'])){
    $html.= '<pagebreak page-break-type="clonebycss" />'.$rel['quot_annex_content'];
}*/
/*Annexure Content Print End*/

$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
</body>
</html>';
/* echo $header;*/
// echo $html;exit;
ob_end_clean();
// include("../../view/export/mpdf/mpdf.php");
// $mpdf=new mPDF('','A4','0','calibri','10','10','37','10','1','1');
//    $mdf->SetFont('ProximaNova');
include("../../vendor/mpdf/mpdf/src/Mpdf.php");
$mpdf = new Mpdf(['format' => 'A4','margin_left' => 10,'margin_right' => 10,'margin_top' => 37,'margin_bottom' => 10,'margin_header' => 1,'margin_footer' => 1,'default_font' => 'calibri']);
$mpdf->defaultheaderfontsize = 10; /* in pts */
$mpdf->defaultheaderfontstyle = 'B'; /* blank, B, I, or BI */
$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
$mpdf->defaultfooterfontsize = 10; /* in pts */
$mpdf->defaultfooterfontstyle = 'B'; /* blank, B, I, or BI */
$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
$mpdf->SetHTMLHeader($header);
    //$mpdf->SetHTMLFooter($footer);
$mpdf->SetWatermarkText();
$mpdf->showWatermarkText = true;
$mpdf->allow_charset_conversion=true;
$mpdf->charset_in='UTF-8';
$mpdf->WriteHTML($html);
$mpdf->Output();
    //$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
    ob_end_clean();
return 'Purchase_Order_'.$rel['purchaseorder_no'].'.pdf';
} 
?>
