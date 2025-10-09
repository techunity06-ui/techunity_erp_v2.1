<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/common_functions.php");
	
	$sel=$dbcon->query("select count(pro.product_id) as total,pro.product_sale_gst,t.tp_per,tc.tax_cat_id,tc.tax_gst from product_mst as pro 
	left join tbl_tax_per_master as t on t.tp_id=pro.product_sale_gst 
	left join tbl_tax_category as tc on tc.tax_gst = t.tp_per
	group by pro.product_sale_gst");
	while($row=brp_mysqli_fetch_array($sel))
	{
		/*if($row['product_hsn']=='0')
		{
			echo $row['product_name'];
		}*/
		//echo $row['total']."--".$row['tp_per']."--".$row['tax_cat_id']."<br>";
		
		$hsn_code = "000000".$row['tax_gst'];
		$date=date("Y-m-d H:i:s A");
		
		$dbcon->query("insert into mst_hsn_code (hsn_code,hsn_desc,sale_gst,cdate,company_id) values ('$hsn_code','HSN BY CRON','$row[tax_cat_id]','$date','1')");
		
		echo "insert into mst_hsn_code ('hsn_code','hsn_desc','sale_gst','cdate','company_id') values ('$hsn_code','HSN BY CRON','$row[tax_cat_id]','$date','1')"."<br>";
	}
	
?>