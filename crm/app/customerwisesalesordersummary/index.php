<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

//print_r($_POST);
//print_r($_FILES);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
if(strtolower($POST['mode']) == "customer_wise_sales_order_summary") {
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));		
	$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
	$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));		
	$str .='<table  width="100%" class="display" id="data_list">
	<tr id="logo" class="logo" style="display:block">
	<td style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong>
	</td>
	</tr>
	<tr style="border-bottom:0.5px #000 solid;">
	<td>
	<strong>[ Customer Wise Sales Order Summary ]</strong>
	</td>
	</tr>
	</table>
	<table width="100%" class="display">
	<tbody>';

	$query = $dbcon->query("SELECT l.l_id, l.l_name, l.m_address, l.countryid, l.stateid, l.cityid, city.city_name, state.state_name, country.country_name FROM tbl_ledger as l LEFT JOIN country_mst as country ON country.countryid = l.countryid LEFT JOIN state_mst as state ON state.stateid = l.stateid LEFT JOIN city_mst as city ON city.cityid = l.cityid WHERE l_status = 0 AND ( l_group = 37 OR l_group = 38 ) AND company_id='".$_SESSION['company_id']."'");
	while($res = mysqli_fetch_assoc($query)){
		$salesquery = $dbcon->query("SELECT sales.*, (SELECT SUM(remaning_invoice_qty) FROM tbl_sales_ordertrn WHERE sales_order_id = sales.sales_order_id AND salestrn.sales_ordertrn_status = 0) as pending_qty, (SELECT GROUP_CONCAT(sales_ordertrn_id) FROM tbl_sales_ordertrn WHERE sales_order_id = sales.sales_order_id AND salestrn.sales_ordertrn_status = 0) as sotrn FROM tbl_sales_order as sales LEFT JOIN tbl_sales_ordertrn as salestrn ON salestrn.sales_order_id = sales.sales_order_id WHERE sales.sales_order_status = 0 AND salestrn.sales_ordertrn_status = 0 AND sales.cust_id = '".$res['l_id']."' AND sales.company_id='".$_SESSION['company_id']."' GROUP BY sales.sales_order_id");
		if(mysqli_num_rows($salesquery) > 0){
			$str.='<tr>
			<td colspan="2">Customer : </td>
			<td colspan="8">'.$res['l_name'].'</td>
			</tr>
			<tr>
			<td colspan="2"></td>
			<td colspan="8">'.$res['m_address'].'<br>'.$res['city_name'].' '.$res['state_name'].' '.$res['country_name'].'</td>
			</tr>
			<tr style="font-weight: bold; border-top: 1px solid; border-bottom: 1px solid;">
			<td>No.</td>
			<td>S.O. No.</td>
			<td>Date</td>
			<td>Customer P.O No.</td>
			<td>Customer P.O Date</td>
			<td>Pending Amt.</td>
			<td>Total Amt.</td>
			<td>Status</td>
			</tr>';
			$i = 1;
			$pending = 0;
			$total = 0;
			while($row = mysqli_fetch_assoc($salesquery)){
				$qry = $dbcon->query("SELECT SUM(invoicetrn.total) as pending_amount FROM tbl_invoicetrn as invoicetrn WHERE invoicetrn.trancation_status = 0  AND invoicetrn.so_allocation_id IN (".$row['sotrn'].")");
				$trn_res = mysqli_fetch_assoc($qry);
				$pending_amount = ($trn_res['pending_amount']) ? $trn_res['pending_amount'] : 0;
				if($row['pending_qty']>0){
					$status = 'Pending';
				}else{
					$status = 'Completed';
				}
				$str.='<tr>
				<td>'.$i.'</td>
				<td>'.$row['sales_order_no'].'</td>
				<td>'.date("d-M-Y",strtotime($row['sales_order_date'])).'</td>
				<td>'.$row['po_no'].'</td>
				<td>'.date("d-M-Y",strtotime($row['po_date'])).'</td>
				<td>'.$pending_amount.'</td>
				<td>'.$row['g_total'].'</td>
				<td>'.$status.'</td>
				</tr>';
				$pending += $pending_amount;
				$total += $row['g_total'];
				$i++;
			}
			$str.='<tr style="font-weight: bold; border-top: 1px solid; border-bottom: 1px solid;">
			<td colspan = "5" style="text-align: right">Total</td>
			<td>'.$pending.'</td>
			<td>'.$total.'</td>
			<td></td>
			</tr>
			<tr>
			<td colspan="8"><br></td>
			</tr>';
		}
	}

	echo $str;
	// echo "SELECT sales.*, (SELECT SUM(remaning_invoice_qty) FROM tbl_sales_ordertrn WHERE sales_order_id = sales.sales_order_id AND salestrn.sales_ordertrn_status = 0) as pending_qty, (SELECT GROUP_CONCAT(sales_ordertrn_id) FROM tbl_sales_ordertrn WHERE sales_order_id = sales.sales_order_id AND salestrn.sales_ordertrn_status = 0) as sotrn FROM tbl_sales_order as sales LEFT JOIN tbl_sales_ordertrn as salestrn ON salestrn.sales_order_id = sales.sales_order_id WHERE sales.sales_order_status = 0 AND salestrn.sales_ordertrn_status = 0 AND sales.cust_id = '".$res['l_id']."' AND sales.company_id='".$_SESSION['company_id']."' GROUP BY sales.sales_order_id";
}
?>