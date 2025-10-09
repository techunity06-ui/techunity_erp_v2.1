<?php
session_start(); //start session
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");
// error_reporting(E_ALL);
// print_r($_POST);
// print_r($_FILES);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
if(strtolower($POST['mode']) == "customer_wise_sales_summary") {
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));		
	// print_r($set_head);
	$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
	$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));		
	// print_r($qrycust);
	$str .='<table  width="100%" class="display" id="data_list">
	<tr id="logo" class="logo" style="display:block">
	<td style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong>
	</td>
	</tr>
	<tr style="border-bottom:0.5px #000 solid;">
	<td>
	<strong>[ Customer Wise Sales Summary ]</strong>
	</td>
	</tr>
	</table>
	<table width="100%" class="display">
	<tbody>';

	$query = $dbcon->query("SELECT l.l_id, l.ledger_code, l.l_name, l.m_address, l.countryid, l.stateid, l.cityid, city.city_name, state.state_name, country.country_name FROM tbl_ledger as l LEFT JOIN country_mst as country ON country.countryid = l.countryid LEFT JOIN state_mst as state ON state.stateid = l.stateid LEFT JOIN city_mst as city ON city.cityid = l.cityid WHERE l.l_status = 0 AND ( l.l_group = 37 OR l.l_group = 38 ) AND l.company_id='".$_SESSION['company_id']."'");
	while($res = mysqli_fetch_assoc($query)){
		$salesquery = $dbcon->query("SELECT invoice.* FROM tbl_invoice as invoice WHERE invoice.invoice_status = 0 AND invoice.cust_id = '".$res['l_id']."' AND invoice.company_id='".$_SESSION['company_id']."'");
		if(mysqli_num_rows($salesquery) > 0){
			$str.='<tr>
			<td colspan="2">Party Code : </td>
			<td colspan="3">'.$res['ledger_code'].'</td>
			</tr>
			<tr>
			<td colspan="2">Party Name : </td>
			<td colspan="3">'.$res['l_name'].'<br>'.$res['m_address'].'<br>'.$res['city_name'].' '.$res['state_name'].' '.$res['country_name'].'</td>
			</tr>
			<tr style="font-weight: bold; border-top: 1px solid; border-bottom: 1px solid;">
			<td>No.</td>
			<td>Invoice No.</td>
			<td>Date</td>
			<td>Net Amount</td>
			<td>Gross Amount</td>
			</tr>';
			$i = 1;
			$pending = 0;
			$total = 0;
			while($row = mysqli_fetch_assoc($salesquery)){
				$str.='<tr>
				<td>'.$i.'</td>
				<td>'.$row['invoice_no'].'</td>
				<td>'.date("d-M-Y",strtotime($row['invoice_date'])).'</td>
				<td>'.indian_number($row['basic_total'],2).'</td>
				<td>'.indian_number($row['g_total'],2).'</td>
				<td>'.$status.'</td>
				</tr>';
				$pending += $row['basic_total'];
				$total += $row['g_total'];
				$i++;
			}
			$str.='<tr style="font-weight: bold; border-top: 1px solid; border-bottom: 1px solid;">
			<td colspan = "3" style="text-align: right">Total</td>
			<td>'.indian_number($pending,2).'</td>
			<td>'.indian_number($total,2).'</td>
			</tr>
			<tr>
			<td colspan="5"><br></td>
			</tr>';
		}
	}

	echo $str;
	// echo "SELECT sales.*, (SELECT SUM(remaning_invoice_qty) FROM tbl_sales_ordertrn WHERE sales_order_id = sales.sales_order_id AND salestrn.sales_ordertrn_status = 0) as pending_qty, (SELECT GROUP_CONCAT(sales_ordertrn_id) FROM tbl_sales_ordertrn WHERE sales_order_id = sales.sales_order_id AND salestrn.sales_ordertrn_status = 0) as sotrn FROM tbl_sales_order as sales LEFT JOIN tbl_sales_ordertrn as salestrn ON salestrn.sales_order_id = sales.sales_order_id WHERE sales.sales_order_status = 0 AND salestrn.sales_ordertrn_status = 0 AND sales.cust_id = '".$res['l_id']."' AND sales.company_id='".$_SESSION['company_id']."' GROUP BY sales.sales_order_id";
}
?>