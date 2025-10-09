<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	
	 $qry1="select so.sales_order_no,grn.sales_order_id,grn.sales_ordertrn_id,product_qty from tbl_sales_ordertrn as grn
	 		left join tbl_sales_order as so on so.sales_order_id=grn.sales_order_id
			where grn.sales_ordertrn_status = 0 AND grn.invoice_status = 0";
		$result1=$dbcon->query($qry1);
		while($rel=brp_mysqli_fetch_assoc($result1)){
			$sql_sub = "select IFNULL(sum(product_qty),0) as iqty from tbl_invoicetrn as it where it.trancation_status=0 and sales_ordertrn_id=".$rel['sales_ordertrn_id']." and invoice_id!=0";
				$wo_sub=$dbcon->query($sql_sub);
				$row_sub=brp_mysqli_fetch_assoc($wo_sub);
				$remaining_qty=$rel['product_qty']-$row_sub['iqty'];	
					$info1['remaning_invoice_qty']=$remaining_qty;
			$updateid=update_record("tbl_sales_ordertrn",$info1,"sales_ordertrn_id=".$rel['sales_ordertrn_id'] , $dbcon);
			echo "Sales Order No :".$rel['sales_order_no']." - Sales Order id : ".$rel['sales_order_id']." - Sales order Trn Id : ".$rel['sales_ordertrn_id']." - Remaining Qty Update : ".$remaining_qty;
			echo "</br>";
		}
				

?>
