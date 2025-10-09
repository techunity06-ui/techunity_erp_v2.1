<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/common_functions.php");
	ini_set('max_execution_time', 3000000);
	//echo "hii";
	$sel=$dbcon->query("SELECT * FROM `tbl_sales_ordertrn` WHERE `product_amount` = 0 ");
	$cnt=1;
	while($row=brp_mysqli_fetch_array($sel))
	{
		
		if($row['remaning_invoice_qty']=='0')
		{
						
			$dbcon->query("update tbl_sales_ordertrn set product_amount=".$row['total']." where sales_ordertrn_id= ".$row['sales_ordertrn_id']." ");
			
			echo $cnt."--"."update tbl_sales_ordertrn set product_amount=".$row['total']." where sales_ordertrn_id= ".$row['sales_ordertrn_id']." "."<br>";
			
			
		
		}
		
		
		$cnt++;
	}
	
?>