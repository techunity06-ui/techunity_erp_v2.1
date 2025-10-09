<?php 
session_start();
// var_dump(123);
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
$type='pdf';
if(strtolower($type) == 'pdf') {
    $invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
    $que = "select performa_invoice_type from tbl_proforma_invoice where invoice_id=$invoiceid";
    $rel_que=mysqli_fetch_assoc($dbcon->query($que));

    if($rel_que['performa_invoice_type']=='1'){
        $query="select invoice.*,country.country_name,payterms.payment_terms as payment_trm,state.state_name,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust_a.c_add_state as stateid,state.gst_state_code, city.city_name, cust.cust_name as company_name,cust_a.c_add_location as cust_address,cust_a.c_add_street as cust_street,cust_a.c_add_zip as cust_pincode, type.invoice_type, cust_mobile,cust_email, cust.cust_gst as gst_no , cur.currency_in_word, cur.currency_in_word_end
        from tbl_proforma_invoice as invoice 
        left join tbl_customer as cust on cust.cust_id=invoice.cust_id 
        left join tbl_cust_contact as per on per.cust_id=cust.cust_id  
        left join tbl_cust_address as cust_a on cust_a.cust_id=invoice.cust_id 
        left join country_mst as country on country.countryid=cust_a.c_add_country 
        left join state_mst as state on state.stateid=cust_a.c_add_state 
        left join tbl_currency as cur on cur.currency_id = invoice.currency_id
        left join city_mst as city on city.cityid=cust_a.c_add_city 
        left join tbl_invoicetype as type on type.invoicetype_id=invoice.invoicetype_id 

        left join pay_terms as payterms on payterms.terms_id=invoice.payment_terms 
        where invoice_id=$invoiceid";
    }else{
        $query="select invoice.*,country.country_name,payterms.payment_terms as payment_trm,state.state_name,cust.stateid,state.gst_state_code, city.city_name, cust.l_name as company_name,cust.m_address as cust_address,cust.cust_cont_name, type.invoice_type, cust_pincode, cust_mobile,cust_email, gst_no , cur.currency_in_word, cur.currency_in_word_end
        from tbl_proforma_invoice as invoice 
        left join tbl_ledger as cust on cust.l_id=invoice.cust_id 
        left join country_mst as country on country.countryid=cust.countryid 
        left join state_mst as state on state.stateid=cust.stateid 
        left join city_mst as city on city.cityid=cust.cityid 
        left join tbl_currency as cur on cur.currency_id = invoice.currency_id
        left join tbl_invoicetype as type on type.invoicetype_id=invoice.invoicetype_id 
        left join pay_terms as payterms on payterms.terms_id=invoice.payment_terms 
        where invoice_id=$invoiceid";
    } 
    $rel=mysqli_fetch_assoc($dbcon->query($query));
//echo $query;die();


    $rel['invoice_type'] = 'PROFORMA INVOICE';  
    if(!$rel){
        header("Location: ".ROOT.CRM_ROOT."proforma_list");
    }

    if($rel_que['performa_invoice_type']=='1'){ 
        $contact_person = $rel['c_con_fname'].' '.$rel['c_con_lname'];
    }else{
        $contact_person = $rel['cust_cont_name'];    
    }
     
    $cons_gst_no=$rel['gst_no'];
    $cons_pan_no=$rel['pan_no'];

    $cons_state_name=$rel['state_name'];
    $cons_gst_state_code=$rel['gst_state_code'];
    $place_of_supply=$rel['city_name'];
    $order_no = ($rel['order_no']!='0')?$rel['order_no']:'';
    
    if(!empty($rel['consignee_id'])){ 
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
    $dft = '';
    if($rel['approve_status']==3){
        $dft = '( DRAFT )';
    }
    /* Check Discount is On or off Start */
    if($set_head['show_disc']=='1'){
        $colspan=5;
        $dynamicwidth=40;
    }else{
        $colspan=6;
        $dynamicwidth=46;
    }

    $sundrytax=$dbcon->query("select b.* from tbl_bill_sundry_transaction as b where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_proforma_invoice' and b.isdelete='0' ");
    //$sundrytax_rels = mysqli_fetch_assoc($sundrytax);
    $total_sundrytax=0;
    while($sumsundrytax=mysqli_fetch_assoc($sundrytax)){
        $total_sundrytax = $total_sundrytax + $sumsundrytax['sundry_gst_amount'];
    }
    $set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];

		$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
		$header ='<div style="text-align:center;"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:8.27in;border: 1px white solid" /></div>';
    /* Check Discount is On or off End */
    /*<img src="'.DOMAIN_F.LOGO.$set_head['logo'].'" style="width:2.27in;padding-top:0px;" />*/
 
    // $header = get_header($dbcon,'text-align: center','2in','70px').'';
    

// $html .='<table style="text-align:center;border:none">

//     <tr style="border:none;>
//     <td style="border:none;> 
//     <strong style="font-size:16px;text-align:center;border:none">
//     <span> PROFORMA INVOICE '.$dft.'</span>
//     </strong>
//     </td>$relGoods cleared
//     </tr>
//     </table>

//     <table>
//     <tr id="rawnone">
//     <td colspan="4" rowspan="5" width="50%" style="border:1px solid;vertical-align:top;">
//     <span style="font-weight:bold;font-size: 14px;text-decoration: underline;">Consignee : </span><br>
//     <strong style="font-weight:bold;font-size: 14px;font-weight: bold">'.$rel['company_name'].'</strong>
//     <span style="font-weight:bold;font-size: 14px;">   
//     <br/>';
//     if($rel['quot_address']!='' && $rel['quot_address']!='0'){ 
//         $header.= $rel['quot_address'];
//         $header.='<br/>';
//     }else{ 
//         $header.= $rel['cust_address'];
//         $header.=' <br/>';
//         $header.=$rel['city_name'].','. $rel['state_name'].','. $rel['country_name'];
//         if(!empty($rel['cust_pincode'])){
//             $header.='-'. $rel['cust_pincode'];
//         } 
//     }
//     $header.='</span>
//     <br>
//     <strong>Kind attend : </strong> '.$contact_person.' <br>
//     <strong>Mo. : </strong> '.$rel['cust_mobile'].' <br>
//     <strong>Email Id :</strong> '.$rel['cust_email'].' <br>
//     <br>
//     <span style="font-weight:bold;font-size: 14px"> GSTIN: '.$cons_gst_no.'</span>
//     <br>
       
//     </td>

//     <td style="border:1px solid;font-weight:bold;font-size: 14px;" width="25%">
//     <b>P.I. No.</b>
//     </td>
//     <td style="border:1px solid;font-weight:bold;font-size: 14px;" width="25%">
//     '.$rel['invoice_no'].'
//     </td>
//     </tr>
//     <tr>
//     <td style="border:1px solid;font-weight:bold;font-size: 14px;">
//     <b>P.I. Date</b>
//     </td>
//     <td style="border:1px solid;font-weight:bold;font-size: 14px;">'.date('d/m/Y',strtotime($rel['invoice_date'])).'</td>
//     </tr>
//     <tr>
//     <td style="border:1px solid;font-weight:bold;font-size: 14px;">
//     <b>P.O. No.</b>
//     </td>
//     <td style="border:1px solid;font-weight:bold;font-size: 14px;">
//     '.$order_no.'
//     </td>
//     </tr>
//     <tr>
//     <td style="border:1px solid;font-weight:bold;font-size: 14px;">
//     <b>P.O. Date</b>
//     </td>
//     <td style="border:1px solid;font-weight:bold;font-size: 14px;">
//     '.$order_date.'
//     </td>
//     </tr>
//     <tr>
//     <td colspan="2" style="border:1px solid;font-weight:bold;font-size: 14px;">
//     <span style="font-weight:bold;font-size: 14px">Delivery At</span>
//     <br>';
  
// $header.=' </td>
// </tr>

// </table>';

// $footer = '<hr>';
// $footer='<img src="'.DOMAIN_F.LOGO.$set_head['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';

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
  if($rel['quot_type']=='0'){
    $currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`=68 ';
    $currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));
    $currency_name = '(INR)';
    $currency_word_start = 'Rupees';
    $currency_word_end = 'Paise';
    $currency_symbol = $currency_rel['currency_symbol'];
}else{
    $currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
    $currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

    $currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
    $currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
    $currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
    $currency_symbol = $currency_rel['currency_symbol'];
}
  $po_number = $rel['order_no'];
		$po_number_date =date('d-m-Y',strtotime($rel['order_date']));
  $html='';
  $html.='<html>
  <head>          
  <title>Proforma Invoice'.$dft.' - '.$rel['invoice_no'].'</title>

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
<tr style="border:none;">
<td width="3%" style="border-top: none;text-align:center;font-size:20px;"><strong>  <span> PROFORMA INVOICE </span></strong></td>

</tr>
</table>
<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
<tr >
<td rowspan="6" width="30%" style="border-top: none;text-align:left;vertical-align: top;"><strong> EXPORTER:- <br>
'.$comp_rel['company_name'].',</strong><br>'.$comp_rel['address'].'<br><strong>Phone :</strong>'.$comp_rel['contact_no'].'</td>
<td width="45%" style="border-top: none;text-align:left;"><strong> PROFORMA INVOICE NO. & DATE:</strong> '.$rel['invoice_no'].'/'.$rel['invoice_date'].'</td>
<td width="25%" style="border-top: none;text-align:center;"><strong>  <span> ORIGINAL  </span></strong></td>

</tr>
<tr >
<td width="45%" style="border-top: none;text-align:left;"><strong>Buyer’s Orders No. & Date:</strong> '.$po_number.'/'.$po_number_date.'</td>
<td width="25%" style="border-top: none;text-align:center;"><strong>Exporter’s Ref: </strong></td>

</tr>
<tr >
<td colspan="2" style="border-top: none;text-align:left;"><strong> PROFORMA INVOICE NO. & DATE:</strong> '.$rel['invoice_no'].'/'.$rel['invoice_date'].'</td>

</tr>
<tr >
<td colspan="2" style="border-top: none;text-align:left;"><strong> Seller’s Registration info  – </strong> GSTIN -'.$comp_rel['vatno'].'/ Pan no :'.$comp_rel['pan_no'].'</td>

</tr>
<tr >
<td colspan="2" style="border-top: none;text-align:left;"><strong> Country of Origin of Goods : India                            </strong>'.$comp_rel['country'].'</td>

</tr>
<tr >
<td colspan="2" style="border-top: none;text-align:left;"><strong> Other references(s):                             </strong>'.$comp_rel['country'].'</td>

</tr>
</table>
<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
<tr>
    <td width="30%" colspan="2" style="border: 1px solid; vertical-align: top;"><strong> INVOICE TO:- <br>
    '.$rel['company_name'].',</strong><br>'.$rel['cust_address'].'<br><strong>Phone :</strong>'.$rel['c_con_mobile'].'
    
        </td>
    <td  width="70%" rowspan="6" style="vertical-align:top"><strong> Goods cleared under LUT (ARN): '.$rel['lut_no'].'  </strong> <br><br>
    <strong>Terms of Delivery & Payment</strong>
    '.$set_head['conditions'].'
        </td>
</tr>
<tr>
    <td colspan="2" style="border: 1px solid; text-align:left;vertical-align: top;">
    <strong>CONSIGNEE:- <br>
    '.$cons_data['company_name'].'<br></strong>
    '.$cons_data['cust_address'].'
    
        </td>
 
</tr>

<tr>
    <td  style="border: 1px solid;text-align:center;"><strong>Pre-Carriage by:</strong><br>
       '.$rel['pre_carriage_by'].'
        
        </td>
    <td  style="border: 1px solid;text-align:center;"><strong>Place of receipt by:</strong><br>
    '.$rel['place_of_receipt'].'
        
        </td>
   
    
 
</tr>

<tr>
    <td  style="border: 1px solid;text-align:center;"><strong>Flight No:</strong><br>
       '.$rel[''].'
        
        </td>
    <td  style="border: 1px solid;text-align:center;"><strong>Port of Loading:</strong><br>
    '.$rel['port_of_loading'].'
        
        </td>
   
    
 
</tr>
<tr>
    <td  style="border: 1px solid;text-align:center;"><strong>Port of Discharge:</strong><br>
       '.$rel['port_of_discharge'].'
        
        </td>
    <td  style="border: 1px solid;text-align:center;"><strong>Final Destination:</strong><br>
    '.$rel['final_destination'].'
        
        </td>
   
    
 
</tr>

</table>';
    //  $html.='
    //  <center class="nextpage"></center>
    //     		     <div style="clear:both;"></div>';
$html.='<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3"><tr>
<th colspan="7"  style="text-align:center;border: 1px solid;"><b>Marks & Nos./  Container No. <br>'.$rel['container_no'].'</b></th>

</tr>
<tr>
    <th width="5%" style="text-align:center;border: 1px solid;">Sr. No. </th>
    <th width="30%" style="text-align:center;border: 1px solid;">Description of Goods </th>
    <th width="12%" style="text-align:center;border: 1px solid;">HSN Code </th>
    <th width="8%" style="text-align:center;border: 1px solid;">Size 
        (Inch)
        </th>
    <th width="15%" style="text-align:center;border: 1px solid;">Quantity <br>
        ('.$rel_qry['unit_name'].')
  </th>
    <th width="13%" style="text-align:center;border: 1px solid;">  Unit Rate '.$currency_name.' </th>
    <th width="17%" style="text-align:center;border: 1px solid;">Total Amount '.$currency_name.' </th>
</tr>
 ';
$qry="select trn.*,product.*, product.product_hsn,product.product_name,unit_name FROM `tbl_proforma_trn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trancation_id order by trancation_id desc";
// echo $qry;die;
$result_qry=$dbcon->query($qry);
$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_packing=0;$final_amount=0;$total_i_gst=0;$gst_per = 0;$gst_rate = 0;

while($rel_qry=mysqli_fetch_assoc($result_qry))
{
    $gst_per = $rel_qry['cgst_tax_per']+$rel_qry['sgst_tax_per']+$rel_qry['igst_tax_per'];
    $gst_rate = $rel_qry['cgst_tax_rate']+$rel_qry['sgst_tax_rate']+$rel_qry['igst_tax_rate'];

    if($rel_qry['cgst_tax_rate'] != 0 || $rel_qry['sgst_tax_rate'] !=0){
        $total_cs_gst += $gst_rate;
    }else{
        $total_i_gst += $gst_rate;
    }
        //tax summary calculation start
    if(!empty($rel_qry['tax_val']))
    {
        $tax_num=explode(",",$rel_qry['tax_val']);
        $tax_name=explode(",",$rel_qry['tax_name']);
        $total_net_rate=($rel_qry['product_qty']*$rel_qry['product_rate'])-$rel_qry['discount'];
        for($j=0;$j<count($tax_num);$j++)
        {
            if(!in_array($tax_name[$j],$tax['per']))
            {
                $tax['per'][]=$tax_name[$j];
            }
            $tax['per_total'][$tax_name[$j]]+=$total_net_rate*$tax_num[$j]/100;
        }
    }
$html.= '



<tr>
<td widtd="5%" style="text-align:center;border: 1px solid;">'.$i.'</td>
<td  style="text-align:center;border: 1px solid;">'.$rel_qry['product_name'].'</td>
<td widtd="12%" style="text-align:center;border: 1px solid;">'.$rel_qry['product_hsn_code'].'</td>
<td widtd="8%" style="text-align:center;border: 1px solid;">not found
    </td>
<td widtd="15%" style="text-align:center;border: 1px solid;">'.$rel_qry['product_qty'].'
    (Ea)
</td>
<td widtd="13%" style="text-align:center;border: 1px solid;"> '.number_format($rel_qry['product_rate'],2,".","").' </td>
<td widtd="17%" style="text-align:center;border: 1px solid;"> '.number_format($rel_qry['product_amount'],2,".","").'</td>
</tr>

';

        
     
       
        $i++; 
        $totalqty=$totalqty+$rel_qry['product_qty']-$charges_qty;
        $totalsqr=$totalsqr+$rel_qry['sqr_ft']-$charges_qty1;
        $total_product_amount+=($rel_qry['product_qty']*$rel_qry['product_rate']);
        $totaltaxable+=$rel_qry['product_amount'];
        $total+=$total_net_rate;
        $total_packing +=$rel_qry['packing'];
        
        $final_amount = $totaltaxable+$total_packing;
        
        }
        $pr=5-$cnt;


        $html.='
        <tr>
        <td colspan="6" style="font-weight:bold;text-align:right;border: 1px solid;">TOTAL</td>
        <td  style="text-align:center;border: 1px solid;font-weight:bold;">'.$total_product_amount.'</td>
        </tr>
        
       
        ';
        $qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
        from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
        left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
        where b.sundry_voucher_id=".$invoiceid." and b.sundry_voucher_table='tbl_proforma_invoice' and b.isdelete='0' and le.default_sundry='0'";
        $result12=$dbcon->query($qry12);
        $cnt12 = brp_mysqli_num_rows($result12);
		// 	$result12=$dbcon->query($qry12);		
		// 	while($row12=mysqli_fetch_assoc($result12))
		// 	{
		// 		$html.='<tr>
		// 		<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>'.$row12['l_name'].'</b></td>
		// 		<td style="text-align:right;border:1px solid;"><b>
		// 		'.$currency_symbol.' '.number_format($row12['sundry_amount_conv'],2,".","").'
		// 		</b></td>
		// 		</tr>';
		// 	}
			
		// 	$round_off = round($rel['g_total_conv'])-$rel['g_total_conv'];
		// $gtotal=$rel['g_total_conv']-($round_off);
			
		// 		$html.='<tr>
		// 	<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>Round Off
		// 	</td>
		// 	<td style="text-align:right;border:1px solid;"><b>
		// 	'.$currency_symbol.' '.indian_number($round_off,2).'
		// 	</b></td>
		// 	</tr>
		// 	';
		// 	$html.='<tr>
		// 	<td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>Total Amount
		// 	</td>
		// 	<td style="text-align:right;border:1px solid;"><b>
		// 	'.$currency_symbol.' '.number_format($rel['g_total_conv'],0,".","").'.00
		// 	</b></td>
		// 	</tr>
        while($row12=mysqli_fetch_assoc($result12))
        {
            $billamt = $billamt + $row12['sundry_amount'];
            $html.='<!-- Added By sahaj -->
            <tr>
            <td colspan="6" style="font-weight:bold;text-align:right;border: 1px solid;">'.$row12['l_name'].'</td>
            <td  style="font-weight:bold;text-align:center;border: 1px solid;">'.number_format($row12['sundry_amount'],2,".","").'</td>
            </tr>
			';
        }
        $html .=' <tr>
        <td colspan="6" style="font-weight:bold;text-align:right;border: 1px solid;">';
      
      if($rel['stateid']==$set_head['stateid']){
          $html.='CGST <!--<span>'.number_format((($total_cs_gst+$total_sundrytax)/2),2,".","").'</span>--><br>SGST <!--<span>'.number_format((($total_cs_gst+$total_sundrytax)/2),2,".","").'</span>-->';
          $tax_amnt =  $total_cs_gst;
      }else{
          $html.='IGST <!--<span>'.number_format(($total_i_gst+$total_sundrytax),2,".","").'</span>-->';
          $tax_amnt =  $total_i_gst;
      }
      $billamt = 0; 
      $qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_proforma_trn as trn 
      left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
      left join tbl_ledger as l on l.l_id=tc.tax_id 
      where tc.tax_additional='1' and trn.trancation_id=".$invoiceid." and trn.trancation_status!=2 and tc.isdelete='0' group by tc.tax_id"; 
      $result11=$dbcon->query($qry11);        
      while($row11=mysqli_fetch_assoc($result11))
      {
          $billamt = $billamt + $row11['add_sum'];
          $html.='<br><b>'.$row11['l_name'].'</b> '.number_format($row11['add_sum'],2,".","").'<br>';
      }
              
      $html.='</td>
      <td  style="font-weight:bold;text-align:center;border: 1px solid;"><span style="text-align:right">'.number_format(($tax_amnt+$total_sundrytax),2,".","").'</span>
      
      ';
      $html.='
      </td>
      </tr>';
      $html .='<tr>
      <td colspan="6" style="text-align:right;border:1px solid;font-weight:bold;font-size: 14px;">TOTAL AMOUNT IN '.$currency_name.'
      </td>
      <td style="border:1px solid;font-weight:bold;font-size: 14px;text-align:center">'.number_format($rel['g_total_conv'],2,".","").'
      </td>
      </tr>
      <tr>
      <td colspan="6" style="text-align:right;border:1px solid;font-weight:bold;font-size: 14px;">Advance Amount
      </td>
      <td style="border:1px solid;font-weight:bold;font-size: 14px;text-align:center;">'.number_format($rel['advance_payment'],2,".","").'
      </td>
      </tr>
      <tr>
      <td colspan="7" style="text-align:left;border:1px solid;font-weight:bold;font-size: 14px;"><span style="font-weight:bold;font-size: 14px;">  TOTAL AMOUNT IN WORDS IN '.$currency_name.' : 
      '.ucwords(convert_number_to_words_new($rel['g_total'],$rel['currency_id'],$rel['currency_in_word_end'],$rel['currency_in_word'])).'
      </span>
      </td>
     
      </tr>
      <tr>
      <td colspan="7" style="text-align:left;border:1px solid;font-weight:bold;font-size: 14px;"><span style="font-weight:bold;font-size: 14px;">  100% ADVANCE PAYABLE AMOUNT IN WORDS IN  : '.$currency_name.' 
      '.ucwords(convert_number_to_words_new($rel['g_total'],$rel['currency_id'],$rel['currency_in_word_end'],$rel['currency_in_word'])).'
      </span>
      </td>
     
      </tr>';
        





   
$html.='</table>
<P style="text-align:center;">       <b>Declaration</b>: We declare that this invoice shows actual price of the goods described and that all particulars are true & correct.<br>
<b>Remarks:</b> '.$rel['remark'].'
“We intend to claim benefit under RoDTEP scheme as applicable”
</P>';
$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
</body>
</html>';
/*echo $header;
echo $html;exit;*/
// echo $html;die;
ob_end_clean();
include("../../view/export/mpdf/mpdf.php");
$mpdf=new mPDF('','A4','0','calibri','10','10','45','10','1','1');
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
