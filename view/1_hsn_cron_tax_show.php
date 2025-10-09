<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/common_functions/common_functions.php");
	include("../include/function_database_query.php");
	ini_set('max_execution_time', 3000000);
	//echo "hii";
	$sel=$dbcon->query("select p.product_name,p.product_id,p.product_hsn,h.hsn_id,t.tax_gst,h.hsn_code from product_mst as p  left join mst_hsn_code as h on h.hsn_id=p.product_hsn left join tbl_tax_category as t  on h.sale_gst=t.tax_cat_id where p.product_status!='2'");
	$cnt=1;
	while($row=brp_mysqli_fetch_array($sel))
	{
		
		$gst = $row['tax_gst'];
		$product_id = $row['product_id'];
		//echo $cnt." --".$row['product_name']."--".$row['hsn_code']."--".$row['tax_gst']."<br>";
		//echo $row['product_hsn']."<br>";
		if($gst!='')
		{
			$q = "update product_mst set product_sale_gst='$gst',product_purchase_gst='$gst' WHERE product_id='$product_id'";

			$dbcon->query($q);

			echo $q."<br>";
		}

		$cnt++;
	}
	
?>