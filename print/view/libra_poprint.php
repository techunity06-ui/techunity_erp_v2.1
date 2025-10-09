<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$include = '../../include/';

$type='pdf';
if(strtolower($type) == 'pdf') {
	$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select po.*,state.state_name,modesup.mode_dispatch,payterms.payment_terms as payment_term_name,l.l_name as vender_name,country.country_name,l.m_address as vender_address,l.gst_no as tin_no,l.m_pan,l.cust_mobile as vender_mobile,l.stateid,state.gst_state_code,city.city_name,l.cust_pincode,l.cust_email,god.gd_name,god.gd_address,comp.company_name,le.l_name as con_ven,bm.branch_name as cons_bran,cur.currency_symbol from tbl_purchaseorder as po 
	inner join tbl_ledger as l on l.l_id=po.vender_id
	left join country_mst as country on country.countryid=l.countryid
	left join pay_terms as payterms on payterms.terms_id=po.payment_terms
	left join mode_of_dispatch as modesup on modesup.mode_dis_id=po.mode_of_dispatch
	left join state_mst as state on state.stateid=l.stateid
	left join city_mst as city on city.cityid=l.cityid
	left join mst_godown as god on god.gd_id = po.godown_id
	left join tbl_company as comp on comp.company_id = po.company_id
	left join tbl_ledger as le on le.l_id = po.con_vender_id
	left join branch_mst as bm on bm.branch_id = po.con_branch
	left join tbl_currency as cur on cur.currency_id = po.currency_id
	where po.purchaseorder_id=$purchaseorder_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	//echo "<pre>";print_r($rel);die();
	$_SESSION['invoice_no']=$rel['invoice_no'];		
	$delivery_type = $rel['delivery_type'];
	$purchaseorder_date='';
	if($rel['purchaseorder_date']!="1970-01-01" && $rel['purchaseorder_date']!="0000-00-00")
	{
		$purchaseorder_date=date('d-m-Y',strtotime($rel['purchaseorder_date']));
	}
	
	$set_head = get_company_data($dbcon, $_SESSION['company_id']);
    $company_config = getCompanyConfiguration($dbcon);


	$cons_company_name	= $rel['vender_name'];
	$cons_cust_address	= $rel['vender_address'];
	$cons_gst_no		= $rel['gst_no'];
	$cons_state_name	= $rel['state_name'];
	$cons_gst_state_code= $rel['gst_state_code'];
	$cons_city_name		= $rel['city_name'];
	$cons_country_name	= $rel['country_name'];
	
	$party_address_billing="<strong>".$rel['vender_name']."</strong>
	<span style='font-weight:normal;'> <br/>
	".$rel['vender_address'].",<br/>
	".$rel['cust_pincode']."
	".$rel['city_name'].",
	".$rel['state_name'].",
	".$rel['country_name']."</span>
	<br>  Phone : ".$rel['vender_mobile']."
	<br>  Email : ".$rel['cust_email']."
	<br>  State Code : ".$rel['gst_state_code']."
	<br>  GSTIN : ".$rel['tin_no']."
	<br>  PAN No : ".$rel['m_pan']."
	<br>  Your Reference : " . $rel['quotation_no']."
	<br>  Date : " . date('d-m-Y',strtotime($rel['quotation_date']));

	/*if($rel['consignee_id']==0){

		$party_address_con="<strong>".$rel['gd_name']."</strong>
		<span style='font-weight:normal;'> <br/>
		".$rel['gd_address'].",<br/>";
		/*".$rel['cust_pincode']."
		".$rel['city_name'].",
		".$rel['state_name'].",
		".$rel['country_name']."</span>
		<br>  State Code : ".$rel['gst_state_code']."
		<br>  GSTIN : ".$rel['tin_no'];*/
		/*$contact_person = $rel['cust_cont_name'];
		$party_address_con="<strong>".$rel['vender_name']."</strong>
		<span style='font-weight:normal;'> <br/>
		".$rel['vender_address'].",<br/>
		".$rel['cust_pincode']."
		".$rel['city_name'].",
		".$rel['state_name'].",
		".$rel['country_name']."</span>
		<br>  State Code : ".$rel['gst_state_code']."
		<br>  GSTIN : ".$rel['tin_no'];*/
		
		
	/*}else{
		$consignee="select * from tbl_custmer_consignee as cust 
		left join country_mst as country on country.countryid=cust.countryid
		left join state_mst as state on state.stateid=cust.stateid 
		left join city_mst as city on city.cityid=cust.cityid where cust_id=".$rel['consignee_id'];
		$rel_con=brp_mysqli_fetch_assoc($dbcon->query($consignee));	
		$cpincode="";
		if(!empty($rel_con['cust_pincode'])){
			$cpincode="- ".$rel_con['cust_pincode'];
		}
		$contact_person = $rel_con['cust_name'];

		$party_address_con="
		<strong>".$rel_con['cust_name']."</strong>
		<span style='font-weight:normal;'> <br/>
		".$rel_con['cust_address'].",<br/>
		".$rel_con['cust_pincode']."
		".$rel_con['city_name'].",
		".$rel_con['state_name'].",
		".$rel_con['country_name']."</span>
		<br>  State Code : ".$rel_con['gst_state_code']."
		<br>  GSTIN : ".$rel_con['gst_no'];
	}*/

	$HowManyWeeks = (strtotime( $rel['purchaseorder_due_date'] ) - strtotime( $rel['cdate'])) / 604800;
	$HowManyWeeks = round($HowManyWeeks);
	$HowManyWeeks = ($HowManyWeeks=='0')?'0':$HowManyWeeks;
	$delivery_week = $HowManyWeeks .' - '. ($HowManyWeeks+1) . ' WEEKS';

	$us_sql = "select user_name from users where user_id='".$rel['userid']."'";
	$user_rel=brp_mysqli_fetch_assoc($dbcon->query($us_sql));
//Company Data
/*$comp_qry="select * from tbl_company as comp 
where comp.company_id=".$rel['company_id'];
*/
$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
//CHEMITEK PROCESS EQUIPMENTS PRIVATE LIMITED		
$comp_rel=mysqli_fetch_assoc($dbcon->query($set));

if($rel['cons_same_as']==0){
	if($rel['con_type'] ==1){
		$cons_name = $rel['cons_bran'];
	}else if($rel['con_type'] ==2){
		$cons_name = $rel['con_ven'];
	}else{
		$cons_name = $rel['company_name'];
	}

	$consignee_address = $rel['con_address'];
	$party_address_con = '<strong>'.$cons_name.'</strong><br>'.$consignee_address;
}else{
	$cons_name 		   = $rel['company_name'];
	$consignee_address = $comp_rel['address'];
	$party_address_con = '<strong>'.$cons_name.'</strong><br>'.$consignee_address.'<br> GST No. :' . $comp_rel['vatno'];
}
/* Check Discount is On or off Start */
if($comp_rel['show_disc']=='1'){
	$colspan=5;
	$dynamicwidth=40;
}else{
	$colspan=6;
	$dynamicwidth=46;
}
/* Check Discount is On or off End */
$sundrytax=$dbcon->query("select b.* from tbl_bill_sundry_transaction as b where b.sundry_voucher_id=".$rel['purchaseorder_id']." and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' ");
//$sundrytax_rels = mysqli_fetch_assoc($sundrytax);
$total_sundrytax=0;
while($sumsundrytax=mysqli_fetch_assoc($sundrytax)){
	$total_sundrytax = $total_sundrytax + $sumsundrytax['sundry_gst_amount'];
}
$companyConfiguration=getCompanyConfiguration($dbcon);
$purchase_pro_search=$companyConfiguration['purchase_pro_print'];
$pro_search=explode(",", $purchase_pro_search);
$html='';
/*$header ='
<table>
<tr>
<td colspan="3" style="border: 0px; "><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:2.27in;padding-top:25px;" /></td>
<td colspan="6" style="text-align:center;border: 0px;">
<span style="font-size:16px;"><b>Purchase Order</b></span><br/>
<span style="font-size:16px;"><b>'.$comp_rel["company_name"].' </b></span><br/>
<span style="font-size:12px;">'.$comp_rel["address"].'</span>
</td>
</tr>
</table>';*/

$qry_disc=$dbcon->query("select SUM(trn.product_discount) as discount FROM `tbl_purchaseordertrn` as trn WHERE trn.purchaseordertrn_status=0 and purchaseorder_id=".$rel['purchaseorder_id']);
	$get_disc = brp_mysqli_fetch_assoc($qry_disc);
	if($get_disc['discount']>0){
		$colspan_8 = 9;
	}else{
		$colspan_8 = 8;
		
	}


$header='';
$header .= '<table style="border:0px !important">
	<tr style="border:0px !important">
		<td style="height:20px;border:0px !important"></td>
	</tr>
</table>';
// $header .=get_header($dbcon,'text-align: center','30%','115px');
$header .='<table style="border: none;">
			<tr style="border: none;">
				<td style="border: none;vertical-align:top" width="30%">
					<img src="' . DOMAIN_F . LOGO . $set_head["logo"] . '" style="width:30%; height: 115px;" />
				</td>
				<td style="border: none; text-align: center;vertical-align:top" width="70%">
					<div style="text-align:center">
						<h1 style="margin:0"><b>' . $set_head["company_name"] . ' </b></h1></div> <h3>Manufactures & Exporters of Industrial Valves & Boiler Mounting</h3> Email: ' . strtolower($set_head['website']) . '&nbsp;&nbsp;|&nbsp;&nbsp;Web: ' . $set_head['company_website'] . ' &nbsp;&nbsp; <br> ' . $set_head["address"] . 'Contact no. ' . $set_head['contact_no'] . '&nbsp;&nbsp;&nbsp;<br>CIN : ' . $set_head['cin'] . '</td>
			</tr>
		</table>';

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
    <table style="width:100%">
        <!--<tr style="border-bottom: 0px; ">
            <td colspan="2" style="border: 0px; font-size:14px;">GSTIN : <span style="font-size:13px;">'.$comp_rel["vatno"].'</span></td>
            <td colspan="4" style="border: 0px; font-size:14px;">CIN &nbsp;&nbsp; <span style="font-size:13px;">:U29309GJ2020PTC112810</span> </td>
            <td colspan="3" style="border: 0px; font-size:14px;">Website :<span style="font-size:13px;">'.$comp_rel["company_website"].'</span></td>
            </tr>
            <tr style="border-top: 0px; ">
            <td colspan="2" style="border: 0px; font-size:14px;">Phone : <span style="font-size:13px;">'.$comp_rel["contact_no"].'</span></td>
            <td colspan="4" style="border: 0px; font-size:14px;">Email : <span style="font-size:13px;">'.$comp_rel["website"].'</span></td>
            <td colspan="3" style="border: 0px; font-size:14px;">PAN &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;   <span style="font-size:13px;"> : '.$comp_rel["pan_no"].'</span></td>
        </tr>-->

        <tr>
            <td colspan="2" style="text-align:center;font-size:18px;font-weight:bold;">Purchase Order</td>
        </tr>

        <tr style="">
            <td style="width:50%">PO No : <span style="font-size:13px;">'.$rel['purchaseorder_no'].'</span></td>
            <td style="width:50%">PO Date :<span style="font-size:13px;">'.$purchaseorder_date.'</span></td>
        </tr>
        
        <tr style="background-color:#b3b3b3">
            <td style=""><b>Vendor / Supplier Details</b> </td>
            <td style=""><b>Ship to Address</b></td>
        </tr>

        <tr style="">
            <td rowspan="2" style="">'.$party_address_billing.' </td>
            <td style="border-bottom:none;  text-align:left;vertical-align:top">'.$party_address_con.'</td>
        </tr>

        <tr style="">
            <td style="">
            Payment Terms : '.$rel['payment_term_name'].'
            <br>Terms of delivery :'.$delivery_week.'
            <br>Mode of Delivery :'.$rel['mode_dispatch'].' 
            </td>
        </tr>
	</table>
	</div>

	<table style="font-size:14px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
	<thead>
	<tr style="background-color:#b3b3b3">
	<td style=" text-align:center;"><b>Sr No.</b> </td>
	<td  style=" text-align:left;"><b>Part Code / Description of Goods</b></td>
	<td colspan="2"  style=" text-align:center;"><b>HSN Code</b> </td>
	<td style=" text-align:center;"><b>Required Date</b> </td>
	
	<td style=" text-align:center;"><b>Qty</b> </td>
	<td style=" text-align:center;"><b>UOM</b> </td>
	<td style="text-align:center;"><b>Rate</b></td>';
		if($get_disc['discount']>0){
			$html.='<td style="text-align:center;"><b>Discount</b></td>';
		}
		$html.='<td style="text-align:right;"><b>Net Amount</b></td>
	</tr>
	</thead>
	<tbody>';
	$qry="SELECT trn.*, product.product_icode, product.product_name,product.product_icode, dr.drawing_number, product.product_alias_name, per.unit_name as base_unit, cper.unit_name as conv_unit FROM `tbl_purchaseordertrn` as trn 
	left join product_mst as product on product.product_id=trn.product_id 
	left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
	left join unit_mst as per on per.unitid=trn.unit_id
	left join unit_mst as cper on cper.unitid = trn.conv_unit_id
	where purchaseordertrn_status=0 and purchaseorder_id=".$rel['purchaseorder_id']." group by purchaseordertrn_id order by purchaseordertrn_id";

	$trn_qry_rs=$dbcon->query($qry);
	$p=1;$ttl_amt=0;$ttl_qty=0;$total_cs_gst=0;$total_i_gst=0;$gst_per=0;$gst_rate=0;
	$cnt=brp_mysqli_num_rows($trn_qry_rs);
	while($trn_rel=brp_mysqli_fetch_array($trn_qry_rs)){
		$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
		$gst_rate = $trn_rel['cgst_tax_rate_conv']+$trn_rel['sgst_tax_rate_conv']+$trn_rel['igst_tax_rate_conv'];

		if($trn_rel['cgst_tax_rate_conv'] != 0 || $trn_rel['sgst_tax_rate_conv'] !=0){
			$total_cs_gst += $gst_rate;
		}else{
			$total_i_gst += $gst_rate;
		}

		if(in_array('drawing',$pro_search)){
            $drawing_number = " -- (".$trn_rel['drawing_number'].")";
        }
        if(in_array('item',$pro_search)){
            $item_code = " -- (".$trn_rel['product_icode'].")";
        }
        if(in_array('alias',$pro_search)){
            $alias = " -- (".$trn_rel['product_alias_name'].")";
        }

       	$que = "select smp.po_req_no as work_order_no,smp.sales_order_no as so_order_no, group_concat(req.indent_no) as indent  from tbl_request_product as req
			        left join tbl_set_main_process as smp on smp.sp_id = req.sp_id
			        where rp_id in (".$trn_rel['po_ref_id'].") group by smp.sp_id";

        $res=$dbcon->query($que);
        $rw = brp_mysqli_fetch_array($res);

        $sno='';$wno='';
       /*  if($companyConfiguration['po_work_order_wise'] == 1){
			$sno = "<br> <strong>Sales Order No : ".$rw['so_order_no']."</strong>";
        	$wno = "<br> <strong>Work Order No : ".$rw['work_order_no']."</strong>";
			
        } */

		//tax summary calculation start
		if(!empty($trn_rel['tax_val']))
		{
			$tax_num=explode(",",$trn_rel['tax_val']);
			$tax_name=explode(",",$trn_rel['tax_name']);
			$total_net_rate=($trn_rel['product_qty']*$trn_rel['product_rate'])-$trn_rel['discount'];
			for($j=0;$j<count($tax_num);$j++)
			{
				if(!in_array($tax_name[$j],$tax['per']))
				{
					$tax['per'][]=$tax_name[$j];
				}
				$tax['per_total'][$tax_name[$j]]+=$total_net_rate*$tax_num[$j]/100;
			}
		}
		$purchaseorder_due_date ='';
		if($rel['purchaseorder_due_date']!="1970-01-01" && $rel['purchaseorder_due_date']!="0000-00-00")
		{
			$purchaseorder_due_date=date('d-m-Y',strtotime($rel['purchaseorder_due_date']));
		}
		if($trn_rel['unit_id']===$trn_rel['rate_unit']){
			$sqty=$trn_rel['product_qty'];
		}else{
			$sqty=$trn_rel['product_conv_qty'];
		}

		if($trn_rel['unit_id']==$trn_rel['conv_unit_id']){
			$show_qty  	= $trn_rel['product_qty'];
			$show_unit  = $trn_rel['base_unit']; 
		}else{
			$show_qty  = without_comma_two_digit_amount($trn_rel['product_qty']).'<br>'.without_comma_two_digit_amount($trn_rel['product_conv_qty']);
			$show_unit = $trn_rel['base_unit'].'<br>'.$trn_rel['conv_unit'];
		}
		$path='view/upload/product_images/';
		$html.='
		<tr>
		<td style="text-align:center;font-size:12px;">'.$p.' </td>
		<td style="border: none;text-align:left;font-size:12px;">
		<table style="width:100%;">
		<tr border="0" style="border-radious: 0px; border: none;">';
		$align="";
		if($trn_rel['product_type']=='8'){
			$align="text-align:right";
		}
		if($trn_rel['image_name']!=''){
			$html.='<td border="0" style="border-radious: 0px; border: none!important;width:30%;font-size:12px;'.$align.'"><img src="'.ROOT.$path.$trn_rel['image_name'].'" height="50" width="50" class="img-thumbnail" /></td>';
		}
		$html.='<td border="0" style="border-radious: 0px; border: none!important;font-size:11px;font-weight:bold'.$align.'">'.$trn_rel['product_name'].' '.$drawing_number.' '.$item_code.' '.$alias.''.$sno.' '.$wno.'</td> 
		</tr>
		</table>'.$trn_rel['product_des'];
		if($delivery_type == 'product_wise'){
			$retu_date = "select sdate.*,unit.unit_name from tbl_purchaseorder_delivery_date as sdate left join unit_mst as unit on unit.unitid=sdate.unit_id where po_delivery_date_status=0 and purchaseordertrn_id=".$trn_rel['purchaseordertrn_id'];
			$resadate=$dbcon->query($retu_date);
			
			$html .='<table width="40%" style="font-size:13px">
			<tr>
			<td><strong>Delivery Date</strong></td>
			<td><strong>Qty</strong></td>
			</tr>';
			
			while($rowdate=brp_mysqli_fetch_array($resadate)){		
				$html .='<tr>
				<td>'.date('d-m-Y',strtotime($rowdate['delivery_date'])).'</td>
				<td>'.$rowdate['product_qty'].' '.$rowdate['unit_name'].'</td>
				</tr>';		
			}
			$html .='</table>';
		}
		$html .='</td>
		<td colspan="2" style=" text-align:center;font-size:12px;"> '.$trn_rel['product_hsn_code'].'</td>

		<td style=" text-align:center;font-size:12px;">'.$purchaseorder_due_date.'</td>
		
		<td style=" text-align:right;font-size:12px;">'.$show_qty.' </td>
		<td style=" text-align:center;font-size:12px;">'.$show_unit.' </td>
		<td style=" text-align:center;font-size:12px;">'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($trn_rel['product_currency_rate']).'</td>';
			if($get_disc['discount']>0){
				$html.='<td style=" text-align:center;">'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($trn_rel['product_discount_conv']).'<br>'.without_comma_two_digit_amount($trn_rel['discount_per']).' %</td>';
			}
			$html.='<td style=" text-align:right;font-size:12px;"> '.$rel['currency_symbol'].' '.without_comma_two_digit_amount($trn_rel['product_currency_amount']).'</td>
			</tr>';

		$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
		if($trn_rel['act_amt_flag']!='1'){
		//$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
			$ttl_amt=$ttl_amt+$trn_rel['product_currency_amount'];
		}
		$p++;
	}
	$pr=10-$cnt;

	$html.='
	<tr>
	<td colspan="'.$colspan_8.'" style=" text-align:right;font-size:12px;">TOTAL  </td>
	<td  style=" text-align:right;font-size:12px;">'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($ttl_amt,2).'</td>
	</tr>
	';
	if($rel['stateid']==$comp_rel['stateid']){
		$html.='<tr>
		<td colspan="'.$colspan_8.'" style=" text-align:right;font-size:12px;">CGST</td>
		<td  style=" text-align:right;font-size:12px;">'.$rel['currency_symbol'].' '.number_format(($total_cs_gst/2),2,".","").'</td>
		</tr><tr>
		<td colspan="'.$colspan_8.'" style=" text-align:right;font-size:12px;">SGST</td>
		<td  style=" text-align:right;font-size:12px;">'.$rel['currency_symbol'].' '.number_format(($total_cs_gst/2),2,".","").'</td>
		</tr>';
	}else{
		$html.='<tr>
		<td colspan="'.$colspan_8.'" style=" text-align:right;font-size:12px;">IGST</td>
		<td  style=" text-align:right;font-size:12px;">'.$rel['currency_symbol'].' '.number_format(($total_i_gst),2,".","").'</td>
		</tr>';
	}		
	$qry11="select sum((tc.tax_per*trn.product_currency_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_purchaseordertrn as trn 
	left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
	left join tbl_ledger as l on l.l_id=tc.tax_id 
	where tc.tax_additional='1' and trn.purchaseorder_id=".$rel['purchaseorder_id']." and trn.purchaseordertrn_status!=2 and tc.isdelete='0' group by tc.tax_id";
	$result11=$dbcon->query($qry11);		
	while($row11=mysqli_fetch_assoc($result11))
	{
		$html.='<tr>
		<td colspan="'.$colspan_8.'" style=" text-align:right;font-size:12px;"><b>'.$row11['l_name'].'</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		'.$rel['currency_symbol'].' '.number_format($row11['add_sum'],2,".","").'
		</b></td>
		</tr>';
	}
	$qry12="select b.sundry_amount_conv,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
	from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
	left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
	where b.sundry_voucher_id=".$rel['purchaseorder_id']." and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' and le.default_sundry='0'";
	$result12=$dbcon->query($qry12);		
	while($row12=mysqli_fetch_assoc($result12))
	{
		$html.='<tr>
		<td colspan="'.$colspan_8.'" style=" text-align:right;font-size:12px;"><b>'.$row12['l_name'].'</b></td>
		<td style="text-align:center;border:1px solid;"><b>
		'.$rel['currency_symbol'].' '.number_format($row12['sundry_amount_conv'],2,".","").'
		</b></td>
		</tr>';
	}
    //$round_off = round($final_total)-$final_total;
	$round_off = 0;
	$html .= '
	<tr>
	<td colspan="'.$colspan_8.'" style=" text-align:right;font-size:14px;background-color:#b3b3b3">Total Amount (In Figure) </td>
	<td  style=" text-align:right;font-size:14px;font-weight:bold;background-color:	#b3b3b3">'.$rel['currency_symbol'].' '.without_comma_two_digit_amount(($rel['g_total_conv']),2).' </td>
	</tr>';
	$currency_code = getcurrencydetail($dbcon,$rel['currency_id']);
	$html.='</tbody></table>';
	$html.='<table style="page-break-inside: avoid;border-bottom:none">
	
	<tr style="border-bottom:none">
	<td style=" text-align:left;font-size:14px;">
	Total Amount (In Words): <span style="font-weight:bold;font-size:13px;">'.convert_number_to_words_new($rel['g_total_conv'],$rel['currency_id'],$currency_code['currency_in_word_end'],$currency_code['currency_in_word']).'</span></td>
	</tr>
	<tr style="">
        <td style=" text-align:left;font-size:14px;">
        <strong>Terms Conditions :</strong> <br>'.$rel['po_condition'].'</td>
    </tr>
	</table>';
	/* $html.='<table>
	<tr style="">
	<td colspan="2" rowspan="2" style="font-size:13px;width:100px;text-align:center">
	HSN/SAC
	</td>
	<td colspan="2" rowspan="2" style="font-size:13px;width:100px;text-align:center">
	Taxable Value
	</td>';
	if($rel['stateid']==$comp_rel['stateid']){
		$html.='<td colspan="2" style="font-size:13px;text-align:center">SGST
		</td>
		<td colspan="2" style="font-size:13px;text-align:center">CGST
		</td>';
	}else{
		$html.='<td colspan="2" style="font-size:13px;text-align:center">IGST
		</td>';
	}
	$html.='<td colspan="2" rowspan="2" style="font-size:13px;text-align:center;width:100px">
	Total Tax Amount
	</td>
	</tr>';
	if($rel['stateid']==$comp_rel['stateid']){
		$html.='<tr>
		<td style="font-size:12px;text-align:center;">Rate</td>
		<td style="font-size:12px;text-align:center;">Amount</td>
		<td style="font-size:12px;text-align:center;">Rate</td>
		<td style="font-size:12px;text-align:center;">Amount</td>
		</tr>';
	}else{
		$html.='<tr>
		<td style="font-size:12px;text-align:center;">Rate</td>
		<td style="font-size:12px;text-align:center;">Amount</td>
		</tr>';
	}

	$purchase_sql = "SELECT sum(product_currency_amount) as product_amount,product_hsn_code,cgst_tax_per,sgst_tax_per,igst_tax_per  FROM `tbl_purchaseordertrn` WHERE `purchaseorder_id` = '".$rel['purchaseorder_id']."' AND `purchaseordertrn_status` = 0 group by product_hsn_code";

	$purchase_exec=$dbcon->query($purchase_sql);
	$total_amount_sum = 0;
	$total_tax_sum = 0;
	$tax_array = [];
	while($purchase_data=mysqli_fetch_assoc($purchase_exec)){
		$total_amount_sum = $total_amount_sum + $purchase_data['product_amount'];
		$gst_per = $purchase_data['cgst_tax_per']+$purchase_data['sgst_tax_per'];
		$html.='<tr style="">
		<td colspan="2" style="text-align:center;font-size:12px;">
		'.$purchase_data['product_hsn_code'].'
		</td>
		<td colspan="2" style="text-align:right;font-size:12px;">
		'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($purchase_data['product_amount']).'
		</td>';

		$total_taxable_amount = 0;
		if($rel['stateid']==$comp_rel['stateid']){
			$total_cs_gst = $total_cs_gst+$total_sundrytax;
			$html.='<td style="text-align:right;font-size:12px;">
			'.$gst_per.'
			</td>
			<td style="text-align:right;font-size:12px;">
			'.$rel['currency_symbol'].' '.number_format(($total_cs_gst/2),2,".","").'
			</td>
			<td style="text-align:right;font-size:12px;">
			'.$gst_per.'
			</td>
			<td style="text-align:right;font-size:12px;">
			'.$rel['currency_symbol'].' '.number_format(($total_cs_gst/2),2,".","").'
			</td>';
			$k++;

			$total_taxable_amount = $total_taxable_amount+$total_cs_gst;
			$tax_array[$rel15['tx_tax_id']][] = $texAmount;
		}else{
			$total_i_gst = $total_i_gst + $total_sundrytax;
			$html.='<td style="text-align:right;font-size:12px;">
			'.$gst_per.'
			</td>
			<td style="text-align:right;font-size:12px;">
			'.$rel['currency_symbol'].' '.number_format(($total_i_gst),2,".","").'
			</td>';
			$k++;

			$total_taxable_amount = $total_taxable_amount+$total_i_gst;
			$tax_array[$rel15['tx_tax_id']][] = $texAmount;
		}

		$total_tax_sum = $total_tax_sum + $total_taxable_amount; 
		$html.='<td colspan="2" style=" text-align:right;font-size:12px;">
		'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($total_taxable_amount).'
		</td>
		</tr>';
	}
	
	$html.='<tr style="">
		<td colspan="2" style="text-align:center;font-size:13px;font-weight:bold">
		Total
		</td>
		<td colspan="2" style="text-align:right;font-size:13px;font-weight:bold">
		'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($total_amount_sum).'
		</td>';
		if($rel['stateid']==$comp_rel['stateid']){
			$total_cs_gst = $total_cs_gst + $total_sundrytax;
			$html.='<td style="text-align:right;font-size:13px;font-weight:bold" colspan="2">
			'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($total_cs_gst/2).'
			</td>
			<td style="text-align:right;font-size:13px;font-weight:bold" colspan="2">
			'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($total_cs_gst/2).'
			</td>
			<td colspan="2" style=" text-align:right;font-size:13px;font-weight:bold">
		'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($total_cs_gst).'
		</td>';
		} else{
			$total_i_gst = $total_i_gst + $total_sundrytax;
			$html.='<td style="text-align:right;font-size:13px;font-weight:bold" colspan="2">
			'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($total_i_gst).'
			</td>
			<td colspan="2" style=" text-align:right;font-size:13px;font-weight:bold">
		'.$rel['currency_symbol'].' '.without_comma_two_digit_amount($total_i_gst).'
		</td>';
		}
		$html.='</tr>
		</table>'; */
	/*$html.='';*/

	$html.='<table style="page-break-inside: avoid;border-top:none;border-bottom:none" >';
	$html.='
	<tr style="border-top:none">
	<td colspan="4" style=" text-align:left;font-size:14px;height:60px;vertical-align:top">
	Remarks : <br>
	'.(($rel['remark']!='0')?$rel['remark']:'').'
	</td>
	<td colspan="4" style="text-align:left;font-size:14px;height:60px;vertical-align:top">
	Comments :
	</td>
	
	</tr>
	<tr style="border-bottom:none">
	<td colspan="2" style="height:50px;border-bottom:none;vertical-align:top;text-align:center;width:20%">ACCOUNT
	</td>
	<td colspan="2" style="height:50px;border-bottom:none;vertical-align:top;text-align:center;width:20%">'.$user_rel['user_name'].'</td>
	<td colspan="4" style="height:50px;border-bottom:none;vertical-align:top;text-align:center;font-weight:bold;width:50%">For '.$comp_rel['company_name'].'</td>
	</tr>
	<tr style="border-top:none">
	<td colspan="2" style="border-top:none;text-align:center;width:20%">Prepared By</td>
	<td colspan="2" style="border-top:none;text-align:center;width:20%">Checked By</td>
	<td colspan="4" style="border-top:none;text-align:center;width:50%">Authorised Signatory</td>
	</tr>
	';
	$html.='</table>';
	/* Get Terms And Condition Start */

	$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
/* echo $header;
echo $html;exit; */
ob_end_clean();
include("../../view/export/mpdf/mpdf.php");
$mpdf=new mPDF('','A4','0','calibri','10','10','40','10','1','1');
//		$mdf->SetFont('ProximaNova');
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
return 'Purchase Order'.$rel['purchaseorder_no'].'.pdf';
}	
?>
