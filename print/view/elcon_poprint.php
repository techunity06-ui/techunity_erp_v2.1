<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");        

$type='pdf';
if(strtolower($type) == 'pdf') {
    $purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
    $query="select po.*,state.state_name,modesup.mode_dispatch,payterms.payment_terms as payment_term,l.l_name as vender_name,country.country_name,l.m_address as vender_address,l.gst_no as tin_no,l.cust_mobile as vender_mobile,l.stateid,state.gst_state_code,city.city_name,l.cust_pincode,l.m_pan,l.cust_email from tbl_purchaseorder as po 
    inner join tbl_ledger as l on l.l_id=po.vender_id
    left join country_mst as country on country.countryid=l.countryid
    left join pay_terms as payterms on payterms.terms_id=po.payment_terms
    left join mode_of_dispatch as modesup on modesup.mode_dis_id=po.mode_of_dispatch
    left join state_mst as state on state.stateid=l.stateid
    left join city_mst as city on city.cityid=l.cityid

    where po.purchaseorder_id=$purchaseorder_id";
    $rel=mysqli_fetch_assoc($dbcon->query($query));
    $_SESSION['invoice_no']=$rel['invoice_no'];		

    $set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
    $set_head=mysqli_fetch_assoc($dbcon->query($set));	
		//echo "<pre>";print_r($set_head);die();
    $order_date='';
    if($rel['purchaseorder_date']!="1970-01-01" && $rel['purchaseorder_date']!="0000-00-00")
    {
     $order_date=date('d/m/Y',strtotime($rel['purchaseorder_date']));
 }
 $quotation_date = '';
 if($rel['quotation_date']!="1970-01-01" && $rel['quotation_date']!="0000-00-00")
 {
     $quotation_date=date('d/m/Y',strtotime($rel['quotation_date']));
 }

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
 if(!empty($rel['consignee_id']))
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

 }

 $us_sql = "select user_name from users where user_id='".$rel['userid']."'";
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
		$potrn = "select potrn.purchaseordertrn_id, poreq.req_id,rp.indent_no  from `tbl_purchaseordertrn` as potrn 
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
    $indent_no_data = implode(',', $indent_arr);	
    $companyConfiguration=getCompanyConfiguration($dbcon);
    $purchase_pro_search=$companyConfiguration['purchase_pro_print'];
    $pro_search=explode(",", $purchase_pro_search);
    /*END*/

    $html='';

    $header ='<img src="'.DOMAIN_F.LOGO.$set_head["logo"].'" style="width:8.27in" />';


    $html.='<html>
    <head>          
    <title>Purchase Order - '.$rel['purchaseorder_no'].'</title>

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
<td colspan="10" style="border: 0px; font-size:16px;font-weight:bold;text-align:center">PURCHASE ORDER</td>
</tr>

<tr style="border:none; border-left:1px solid; border-right:1px solid;border-bottom:1px solid; ">
<td width="50%" colspan="6" rowspan="5"  style=" text-align:left;">
<b> '.$cons_company_name.'</b>
<br>
'.$cons_cust_address.'<br>
'.$cons_city_name.' - '.$cons_cust_pincode.' ('.$cons_state_name.') - '.$cons_country_name.'<br>
Tel No.: '.$cons_vender_mobile.'   Fax No.: -<br>
E-mail: '.$cons_cust_email.' <br>
GST NO : '.$cons_gst_no.'<br>
PAN NO. : '.$cons_m_pan.'
</td>
<td width="13%" style="border:none"><b>P.O. No. </b></td>
<td width="13%" style="border:none">:'.$rel['purchaseorder_no'].'</td>
<td width="13%" style="border:none"><b>Date </b></td>
<td width="13%" style="border:none">:'.$order_date.'</td>

</tr>
<tr style="border:none; border-left:1px solid;border-right:1px solid;  ">
<td style="border:none"><b>Quot. Ref. No. </b></td>
<td style="border:none">:'.(($rel['quotation_no']!='0')?$rel['quotation_no']:'').'</td>
<td style="border:none"><b>Date </td>
<td style="border:none">:'.$quotation_date.'</td>
</tr>

<tr style="border:none; border-right:1px solid;border-bottom:1px solid; ">
<td style="border:none"><b>Buyer </b></td>
<td colspan="3" style="border:none">:'.$user_rel['user_name'].'</td>
</tr>
<tr style="border:none; border-right:1px solid;border-bottom:1px solid; ">
<td style="border:none"><b>Indent No. </b></td>
<td colspan="3" style="border:none">:'.$indent_no_data.'</td>

</tr>
<tr style="border:none; border-left:1px solid; border-right:1px solid; ">
<td style="border:none"><b>Delivery Address :</b></td>
<td colspan="3" style="border:none">
GAT NO.21, H NO.253,VARVE BK<br>
TAL. BHOR , DIST PUNE 412210 <br>
Pune - 412210 (MAHARASHTRA) - INDIA
</td>

</tr>


<tr>
<td colspan="10">Please supply the following goods subject to terms and conditions stated below.</td>
</tr>

</table>
</div>
<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
<thead>
<tr style="background-color:#b3b3b3">
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

$qry="select trn.*,product.*,dr.drawing_number,product.product_desc as scode,per.unit_name,per1.unit_name as base_unit_name,per2.unit_name as conv_unit_name,group_concat(tax.tax_value) as tax_val,group_concat(tax.tax_name) as tax_name FROM `tbl_purchaseordertrn` as trn 
left join product_mst as product on product.product_id=trn.product_id 
left join tbl_drawing as dr on dr.drawing_id = product.drawing_id 
left join unit_mst as per on per.unitid=trn.unit_id 
left join unit_mst as per1 on per1.unitid=product.product_base_unit 
left join unit_mst as per2 on per2.unitid=product.product_conv_unit 
left join `formula_mst` as ftax on ftax.formulaid=trn.formulaid left join tbl_tax as tax on find_in_set(tax.tax_id,ftax.tax_id)
where purchaseordertrn_status=0 and purchaseorder_id=".$rel['purchaseorder_id']." group by purchaseordertrn_id order by purchaseordertrn_id";
$result=$dbcon->query($qry);		
$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;
$cnt=brp_mysqli_num_rows($result);
$total_product_amount = 0;
while($row=brp_mysqli_fetch_assoc($result))
{

	if($row['product_base_unit']!=$row['product_conv_unit']){
		//base_unit_name,per2.unit_name as conv_unit_name
		if($row['unit_id']==$row['product_base_unit']){
			$cqty=convert_stock($dbcon,$row['product_qty'],$row['product_id'],"conv_unit");
			$uname=$row['conv_unit_name'];
		}else{
			$cqty=convert_stock($dbcon,$row['product_qty'],$row['product_id'],"base_unit");
			$uname=$row['base_unit_name'];
		}
	}

    if(in_array('drawing',$pro_search)){
        $drawing_number = " -- (".$row['drawing_number'].")";
    }
    if(in_array('item',$pro_search)){
        $item_code = " -- (".$row['product_icode'].")";
    }
    if(in_array('alias',$pro_search)){
        $alias = " -- (".$row['product_alias_name'].")";
    }

    $que = "select smp.po_req_no as work_order_no, group_concat(req.indent_no) as indent  from tbl_request_product as req
            left join tbl_set_main_process as smp on smp.sp_id = req.sp_id
            where rp_id in (".$row['po_ref_id'].") group by smp.sp_id";

    $res=$dbcon->query($que);
    $rw = brp_mysqli_fetch_array($res);

    if($companyConfiguration['po_work_order_wise'] == 1){
        $wno = "<br> <strong>Work Order No : ".$rw['work_order_no']."</strong>";
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
	}
	$description = ($row['product_des']!='' && $row['product_des']!='0')?$row['product_des']:'';
	//tax summary calculation end
	$taxable_amt=$row['total']-$row['product_amount'];
  $html.='
  <tr>
  <td style="text-align:center;font-size:12px;">'.$i.'</td>
  <td colspan="2" style="border: none;text-align:left;font-size:12px;">

  <span style="font-weight:bold;">'.$row['product_name'].'  '.$drawing_number.' '.$item_code.' '.$alias.' '.$wno.'</span><br>
  '.nl2br(stripcslashes($description)).'            
  </td>
  <td style=" text-align:center;font-size:12px;font-weight:bold"> '.stripcslashes($row['product_hsn_code']).'</td>            
  <td style=" text-align:center;font-size:12px;"> '.$row['unit_name'].'</td>
  <td style=" text-align:center;font-size:12px;">'.$row['product_qty'].' </td>
  <td style=" text-align:right;font-size:12px;">'.number_format($row['product_rate'],2,".","").'</td>
  <td style=" text-align:right;font-size:12px;">'.$row['product_discount'].' ('.$row['discount_per'].'%).'.'</td>
  <td style=" text-align:right;font-size:12px;">'.number_format($row['product_rate']-$row['discount_per'],2,".","").'</td>
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

for($j=0; $j<3; $j++){

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
$html .= '
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
</tr>';
$bill_sun=$dbcon->query("select b.*,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id from tbl_bill_sundry_transaction as b 

            left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
            left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 

            where b.sundry_voucher_id=".$rel['purchaseorder_id']." and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' and le.default_sundry='0' ");
$bill_cnt = brp_mysqli_num_rows($bill_sun);
$bill_cnt = $bill_cnt+4;
$bill_sun_tax = "select sum(b.sundry_gst_amount) as gst_amt from tbl_bill_sundry_transaction as b left join tbl_ledger as le on le.l_id=b.sundry_ledger_id where b.sundry_voucher_id=".$rel['purchaseorder_id']." and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' and le.default_sundry='0'";
$exeb=$dbcon->query($bill_sun_tax);
$btax = brp_mysqli_fetch_array($exeb);
$query = 'select sum(cgst_tax_rate) as cgst_rate,sum(sgst_tax_rate) as sgst_rate,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount FROM tbl_purchaseordertrn where purchaseorder_id='.$rel['purchaseorder_id'].' and purchaseordertrn_status!=2';
$exe=$dbcon->query($query);
$ta_row = brp_mysqli_fetch_array($exe);
$html .='<tr>
<td colspan="7" rowspan="'.$bill_cnt.'" style=" text-align:left;font-size:12px;height:auto">

1. Please  mention  PO  No.,  Item  Code  on  your  challan  /  Invoice.<br>
2. You  shall  be  liable  for  any  damages   of  the  goods  &  expenses  incurred   on  account  of  improper   packing&  for  any  cost  attributable   to  improper   protective  measures   taken  by  you  in  regard   to  packing.<br>
3. Suppied  quantity  must  not  exceed  the  one  stipulated  in  our  Purchase  Order  unless  requested  by  us  in  writing.<br>
4. The Price indicated in the order shall hold good its complete execution.<br>
5. All Deliveries must be  as per schedule stipulated in our Purchase Order. We reserve the right to return the supplies made in advanced or beyond  the delivery schedule.<br>
6. All  material  should  supply  along  with  the  related  Test  Certificate,  without  it  GRN  should  not  be  clear.
</td>';
if($set_head['stateid'] == $rel['stateid']){
    $bilg_amt = $btax['gst_amt']/2;
    $cgst = $ta_row['cgst_rate']+$bilg_amt;
    $html .='<td colspan="2" style=" text-align:left;font-size:14px;border:none">
        CGST
    </td>
    <td style=" text-align:left;font-size:14px;border:none;">
    '.$cgst.'
    </td>';
}else{
    $bilg_amt = $btax['gst_amt'];
    $igst = $ta_row['igst_rate'] + $bilg_amt;
    $html .='<td colspan="2" style=" text-align:left;font-size:14px;border:none">
        IGST    
    </td>
    <td style=" text-align:left;font-size:14px;border:none;">
    '.$igst.'
    </td>';
}

$html .='</tr>
<tr style="border:none;border-right:1px solid;border-bottom:1px solid">';
    if($set_head['stateid'] == $rel['stateid']){
        $bilg_amt = $btax['gst_amt']/2;
        $sgst = $ta_row['cgst_rate']+$bilg_amt;
         $html .='<td colspan="2" style="border:none;font-size:14px;border-left:1px solid">
            SGST
        </td>
        <td style="border:none;font-size:14px">
            '.$sgst.'
        </td>';
    }else{
         $html .='<td colspan="2" style="border:none;border-left:1px solid">
            
        </td>
        <td style="border:none">

        </td>';
    }
   
$html .='</tr>';
while($bill_row1=brp_mysqli_fetch_array($bill_sun)){
    $html .='<tr style="border:none;border-right:1px solid">
        <td colspan="2" style="border:none;border-left:1px solid">
            '.$bill_row1['l_name'].'
        </td>
        <td style="border:none">'.$bill_row1['sundry_amount'].'</td>
    </tr>';
}
$html.='<tr style="border:none;border-right:1px solid">
<td colspan="2" style="border:none;border-left:1px solid">';
foreach($tax['per_total'] as $key => $value){
    if($key)
        $html.= $key.'<br>';
}
$html .= '</td>
<td style="border:none">';
$final_total = $total_product_amount;
foreach($tax['per_total'] as $keyq => $val){
   if($val){
       $html.= number_format($val,2,".","").'<br>';
       $final_total += $val;
   }
}
$html .= '</td>
</tr>


<tr>
<td colspan="2"  style="border:none">Total</td>
<td  style="border:none;font-size:14px;font-weight:bold">'.$final_total.'</td>
</tr>
<tr>
<td colspan="7" style="font-weight:bold;font-size:14px">RS : '.ucwords(convert_number_to_words_new($rel['g_total'])).'</td>
<td colspan="3" style="font-weight:bold;font-size:14px;background-color:#b3b3b3">FINAL AMOUNT (RS) : '.number_format($rel['g_total'],2,".","").'</td>
</tr>
<tr>
<td colspan="10" ><span style="font-weight:bold;font-size:14px;text-decoration:underline">REMARKS : </span>'.$remark.'</td>
</tr>
<tr>
<td colspan="10">
<span style="font-weight:bold;font-size:14px;text-decoration:underline">Supply  Terms & Conditions : </span><br>
'.(($rel['po_condition']!='0')?$rel['po_condition']:'').'
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
<img src="' . DOMAIN_F . LOGO . 'sign.png" height="80" width="80"/><br>
<br>
Authorised Signatory 
</td>
</tr>
<tr>

<td colspan="2" style="font-weight:bold;text-align:center">
RANGE<br>
IV (BHOR)
</td>
<td colspan="2" style="font-weight:bold;text-align:center">
DIVISION<br>
IV (PURANDAR)
</td>
<td colspan="3" style="font-weight:bold;text-align:center">
GST NO.<br>
27AACCE9652M1Z3
</td>
<td colspan="3" style="font-weight:bold;text-align:center">
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
//echo $html;exit;
ob_end_clean();
include("../../view/export/mpdf/mpdf.php");
$mpdf=new mPDF('','A4','0','calibri','10','10','37','10','1','1');
//    $mdf->SetFont('ProximaNova');
$mpdf->defaultheaderfontsize = 10; /* in pts */
$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
$mpdf->defaultfooterfontsize = 10; /* in pts */
$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
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
ob_clean();
return 'Purchase_Order_'.$rel['purchaseorder_no'].'.pdf';
} 
?>
