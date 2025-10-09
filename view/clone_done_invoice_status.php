<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/common_functions/common_functions.php");
include("../include/function_database_query.php");

$query = "select * from tbl_sales_order as sales
	left join tbl_sales_ordertrn as strn on strn.sales_order_id=sales.sales_order_id
 where sales.sales_order_status=0 and strn.sales_ordertrn_status =0";
$result = $dbcon->query($query);
$cnt = brp_mysqli_num_rows($result);
while($row = brp_mysqli_fetch_array($result)){
	
	$queryinv = "select sum(product_qty) as used_qty from tbl_invoicetrn as sales
	 where sales.trancation_status=0 and strn.invoice_id !=0 and sales_ordertrn_id=".$row['sales_ordertrn_id'];
	$result_inv = $dbcon->query($queryinv);
	$row_inv = brp_mysqli_fetch_array($result_inv);

	if($row['short_close_status']==1){
		$so_qty=$row['product_qty']-$row['short_close_product_qty'];
	}else{
		$so_qty=$row['product_qty'];
	}

	if($so_qty<=$row_inv['used_qty']){
		$info['invoice_status'] = 1;
		$info['remaning_invoice_qty'] = 0;
	}else{
		$rqty=$so_qty-$row_inv['used_qty'];
		$info['invoice_status'] = 0;
		$info['remaning_invoice_qty'] = $rqty;
	}
	$updateid11=update_record('tbl_sales_ordertrn', $info, "sales_ordertrn_id=".$row['sales_ordertrn_id'] , $dbcon);

	$queryso = "select * from tbl_sales_ordertrn as sales
	 where sales.invoice_status=0 and sales.sales_ordertrn_status=0 and sales_order_id=".$row['sales_order_id'];
	$resultso = $dbcon->query($queryso);
	$cntso = brp_mysqli_num_rows($resultso);
	if($cntso>0){
		$info1['invoice_status'] = 0;
	}else{
		$info1['invoice_status'] = 1;
	}
	$updateid12=update_record('tbl_sales_order', $info1, "sales_order_id=".$row['sales_order_id'] , $dbcon);
} 

if($updateid11){
	echo "Clone Run Successfully.....!!!!!";
}
?>