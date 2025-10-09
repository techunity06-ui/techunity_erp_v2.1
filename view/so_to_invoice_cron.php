<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/common_functions.php");
	ini_set('max_execution_time', 3000000);
	//echo "hii";
	$sel=$dbcon->query("SELECT so.*,sot.* FROM `tbl_sales_order` as so
		left join tbl_sales_ordertrn as sot on so.sales_order_id=sot.sales_order_id
		where so.sales_order_status=0 and so.invoice_status=0 ");
	$cnt=1;
	while($row=brp_mysqli_fetch_array($sel))
	{
		
		if($row['remaning_invoice_qty']=='0')
		{
						
			$dbcon->query("update tbl_sales_ordertrn set remaning_invoice_qty=".$row['product_qty']." where sales_ordertrn_id= ".$row['sales_ordertrn_id']." ");
			
			echo $cnt."--"."update tbl_sales_ordertrn set remaning_invoice_qty=".$row['product_qty']." where sales_ordertrn_id= ".$row['sales_ordertrn_id']." "."<br>";
			
			
		
		}
		
		
		$cnt++;
	}
	
?>