<?php 
	session_start();
	ini_set('max_execution_time', 3000000);
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/common_functions.php");
	
	$cnt=1;
	$sel=$dbcon->query("select * from product_mst where product_status!='2'");
	while($row=brp_mysqli_fetch_array($sel))
	{
	    
		if($row['product_hsn']=='' || $row['product_hsn']=='0')
		{
			$gst = $row['product_sale_gst'];
		
			$sel1=$dbcon->query("select tp.tp_per,h.hsn_id,tc.tax_cat_id from tbl_tax_per_master as tp 
			left join tbl_tax_category as tc left join tc.tax_gst = tp.tp_per
			left join mst_hsn_code as h on h.sale_gst=tc.tax_cat_id
			
			where tp.tp_id='$gst'");
			$row1=brp_mysqli_fetch_array($sel1);
			
			echo $cnt.'--'."update product_mst set product_hsn='$row1[hsn_id]' where product_id='$row[product_id]'"."<br>";
			
			$dbcon->query("update product_mst set product_hsn='$row1[hsn_id]' where product_id='$row[product_id]'");
			
		}
		else
		{
			
			$sel1= $dbcon->query("select * from mst_hsn_code where hsn_code='$row[product_hsn]' and hsn_status = 0");
			$row1 = brp_mysqli_fetch_array($sel1);
			
			
			echo $cnt.'--'."update product_mst set product_hsn='$row1[hsn_id]' where product_id='$row[product_id]'"."<br>";
			
		 $dbcon->query("update product_mst set product_hsn='$row1[hsn_id]' where product_id='$row[product_id]'");
		}
		
		$cnt++;
		
	}
?>