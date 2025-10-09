<?
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/common_functions/common_functions.php");
include("../include/function_database_query.php");

$query = "select sales.sales_order_id,(select count(sales_ordertrn_id) from tbl_sales_ordertrn as trn where trn.invoice_status=1 and trn.sales_ordertrn_status=0 and trn.sales_order_id=sales.sales_order_id) as done_en, (select count(sales_ordertrn_id) from tbl_sales_ordertrn as trn where  trn.sales_ordertrn_status=0 and trn.sales_order_id=sales.sales_order_id) as total_en from tbl_sales_order as sales where sales.sales_order_status=0 and sales.short_close_status =0";
$result = $dbcon->query($query);
$cnt = brp_mysqli_num_rows($result);
while($row = brp_mysqli_fetch_array($result)){
	if($row['total_en']==$row['done_en']){
		$info['invoice_status'] = 1;
	}else{
		$info['invoice_status'] = 0;
	}
	/*var_dump($info);*/
	$updateid11=update_record('tbl_sales_order', $info, "sales_order_id=".$row['sales_order_id'] , $dbcon);
} 

if($updateid11){
	echo "Clone Run Successfully.....!!!!!";
}
?>