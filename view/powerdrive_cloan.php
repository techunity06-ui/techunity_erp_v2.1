<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/common_functions/common_functions.php");
	include("../include/function_database_query.php");
	ini_set('max_execution_time', 3000000);
	//echo "hii";
	$sel=$dbcon->query("select * from product_mst as p where p.product_status!='2'");
	$cnt=0;
	while($row=brp_mysqli_fetch_array($sel))
	{
		
		$sel1=$dbcon->query("select * from tbl_product_process as p where p.product_id=".$row['product_id']);
		$cnt = brp_mysqli_num_rows($sel1);
		if($cnt>0){
			$row2=brp_mysqli_fetch_array($sel1);
			$updatef=$row['product_setting_check'].",process_product";
			$q = "update product_mst set process_product='$updatef' WHERE product_id=".$row['product_id'];
			//$dbcon->query($q);
			echo $row['product_id'];
			echo "</br>";

		}
		
	

		/*product_setting_check
		process_product
		$gst = $row['tax_gst'];
		$product_id = $row['product_id'];
		//echo $cnt." --".$row['product_name']."--".$row['hsn_code']."--".$row['tax_gst']."<br>";
		//echo $row['product_hsn']."<br>";
		if($gst!='')
		{
			$q = "update product_mst set product_sale_gst='$gst',product_purchase_gst='$gst' WHERE product_id='$product_id'";

			$dbcon->query($q);

			echo $q."<br>";
		}*/

		//$cnt++;
	}
	
?>