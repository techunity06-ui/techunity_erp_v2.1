<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(brp_strtolower($POST['mode']) == "load_job_work_data") {
	$str = '';
	if(!empty($POST['sales_order_id'])){
	$query="select invoice.*,country.country_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust_pincode,cust_mobile,gst_no from tbl_sales_order as invoice 
	left join tbl_ledger as cust on cust.l_id=invoice.cust_id
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid
	left join city_mst as city on city.cityid=cust.cityid
	where sales_order_id=".$POST['sales_order_id'];
	$rel=mysqli_fetch_assoc($dbcon->query($query));	
	$company_name=$rel['company_name'];
	$cust_address=$rel['cust_address'];
	$city_name=$rel['city_name'];
	$state_name=$rel['state_name'];
	$country_name=$rel['country_name'];
	$cust_pincode=$rel['cust_pincode'];
	$gst_no=$rel['gst_no'];
	$delivery_type = $rel['delivery_type'];

	$set="select * from tbl_company where company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));

	$str.='<table  class="maintable headermain" id="table_head" width="100%">
	<tr style="border:none;">
	<td width="100%" style="border:none;padding:0px 0px !important;"> 
	<img src="'.ROOT.LOGO.$set_head['logo'].'"  style="width:100%"/>
	</td>
	</tr>
	</table>
	<table width="100%" border="1">
	<tr style="border: 1px solid;">
	<td width="90%" style="text-align:center !important"> 
	<strong style="font-size:16px">
	Job Work Order 
	</strong>
	</td>
	<td width="10%" style="text-align:center"> 
	<strong style="font-size:12px">
	<b class="data_title">ORIGINAL</b>
	</strong>
	</td>
	</tr>
	</table>
	<table width="100%" class="maintable" style="font-size: 11px;   border-right: 1px solid !important; border-left: 1px solid !important;" id="invoice_type" >
	<thead>
	<tr style="border: 1px solid;">
	<th colspan="6" style="padding:0px !important;">
	<table  border="0" style="font-size:12px;border-collapse:collapse;" cellpadding="0"  cellspacing="0" width="100%" id="">
	<tr>
	<td style="white-space:nowrap;"><strong>O.A No</strong></td>
	<td style="white-space:nowrap;"><strong>: '.$rel['sales_order_no'].'</strong></td>
	<td style="white-space:nowrap;"><strong>O.A Date</strong></td>
	<td style="white-space:nowrap;">: '.date('d/m/Y',strtotime($rel['sales_order_date'])).'</td>
	<td style="white-space:nowrap;"><strong>P.O. No</strong></td>
	<td style="white-space:nowrap;"><strong>: '.$rel['sales_order_no'].'</strong></td>
	<td style="white-space:nowrap;"><strong>P.O Date</strong></td>
	<td style="white-space:nowrap;">: '.date('d/m/Y',strtotime($rel['sales_order_date'])).'</td>
	</tr>
	<tr style="border: 1px solid;">
	<td><strong>Name</strong></td>
	<td colspan="3"><strong>: '.$company_name.'</strong></td>
	<td colspan="4"></td>
	</tr>
	</table>
	</th>
	</tr>
	<tr height="30px" style="border: 1px solid;">					
	<th  width="5%" style="text-align:center !important;border:1px solid;border-top:none;"><strong>SR. NO.</strong></th>
	<th width="10%" style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>Item Code</strong></th>
	<th width="55%"  style="text-align:center !important;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Item Description</strong></th>
	<th width="10%" style="text-align:center !important;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>Quantity</strong></th>
	<th width="10%" style="text-align:center !important;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>UOM</strong></th>
	<th width="10%" style="text-align:center !important;border-right:1px solid; border-bottom:1px solid;border-top: none; white-space:nowrap;"><strong>Work Order No</strong></th>
	</tr>
	</thead>
	<tbody style="border: 1px solid;">';
	$qry="SELECT trn.*, product.product_name, product.product_icode, per.unit_name, jobwork.po_req_no FROM `tbl_sales_ordertrn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id left join tbl_set_main_process as jobwork on jobwork.sales_order_trn_id = trn.sales_ordertrn_id where sales_ordertrn_status=0 and sales_order_id=".$rel['sales_order_id'];
	$result=$dbcon->query($qry);		
	$i=1;$total=0;$discount=0;$totalqty=0;
	$cnt=mysqli_num_rows($result);
	while($row=mysqli_fetch_assoc($result)){
		$str.='<tr style="height:40px">
		<td style="text-align:center !important; border-right:1px solid; border-left:1px solid;">'.$i.'</td>
		<td style="text-align:center !important; border-right:1px solid;">'.$row['product_icode'].'</td>
		<td style="border-right:1px solid;"><strong>'.stripcslashes($row['product_name']).'</strong><br>'.nl2br(stripcslashes($row['description'])).'</td>
		<td style="text-align:center !important; border-right:1px solid;">'.$row['product_qty'].'</td>
		<td style="text-align:center !important; border-right:1px solid;">'.$row['unit_name'].'</td>
		<td style="text-align:center !important; border-right:1px solid; white-space:nowrap;">'.$row['po_req_no'].'</td>
		</tr>';
		$i++; 
	}
	$pr=10-$cnt;
	for($j=0; $j<$pr; $j++){
		$str.='<tr style="height:40px">
		<td style="border-right:1px solid;border-left:1px solid;"></td>
		<td style="border-right:1px solid;"></td>
		<td style="border-right:1px solid;"></td>
		<td style="border-right:1px solid;"></td>
		<td style="border-right:1px solid;"></td>
		<td style="border-right:1px solid;"></td>
		</tr>';
	}
	$str.='<tr style="border: 1px solid;">
	<td colspan="6"><strong>Remark: </strong>Has been physically checked and found OK, We hereby stand warranty that the material supplied by us valid for 24 month from the date of invoice or 24 month from the date of dispatch whichever is earlier.</td>
	</tr>
	</tbody>
	</table>
	<table width="100%">
	<tbody>
	<tr style="border: 1px solid;">
	<td width="33.33%" style="border-right: none;">1] Dimension: </td>
	<td width="33.33%" style="border-left: none; border-right: none;">2] QAP: </td>
	<td width="33.33%" style="border-left: none;">3] Drawing: </td>
	</tr>
	<tr style="border: 1px solid;">
	<td colspan="2">AN ISO 9001 : 2008 COMPANY<br>MSME NO : 270251203944<br>NSIC - CRISIL : SE1B</td>
	<td style="text-align: center;">For <strong>'.$set_head['company_name'].'</strong><br><img src="'.DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'].'" style="height: 100px; width: : 100px;"><br>Authorised By</td>
	</tr>
	</tbody>
	</table>';
}
echo $str;
}
?>