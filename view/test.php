<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/common_functions/common_functions.php");

$qry = $dbcon->query("SELECT * FROM tbl_salescardelcontrn WHERE salescardelcontrn_status =0 AND elcon_sales_id = 1");
while($rel = mysqli_fetch_assoc($qry)){
	$qrys = $dbcon->query("SELECT * FROM product_mst WHERE product_category = ".$rel['product_cat_id']." AND product_status = 0 AND (product_base_unit = ".$rel['unit_id']." OR product_conv_unit = ".$rel['unit_id'].") LIMIT 5");
	$price = $amt = 0;
	$cnt = 1;
	while($row = mysqli_fetch_assoc($qrys)){
		if($row['product_base_unit'] == $rel['unit_id']){
			$price = $rel['price']*$row['base_weight'];
		}else if($row['product_conv_unit'] == $rel['unit_id']){
			$price = $rel['price']*$row['conv_weight'];
		}
		$amt = $price + $rel['rate1'] + $rel['rate2'] + $rel['rate3'];

		$dbcon->query("INSERT INTO `tbl_salescardtrn` (`sales_type`,`product_id`,`currency_id`,`price`,`affected_date`,`unit_id`,`salescardtrn_status`,`user_id`,`company_id`,`cdate`) VALUES ('1','".$row['product_id']."','".$rel['currency_id']."','".$amt."','".date("Y-m-d")."','".$rel['unit_id']."','0','".$_SESSION['user_id']."','".$_SESSION['company_id']."','".date("Y-m-d h:i:s")."')");

		echo $cnt."--->"."INSERT INTO `tbl_salescardtrn` (`sales_type`,`product_id`,`currency_id`,`price`,`affected_date`,`unit_id`,`salescardtrn_status`,`user_id`,`company_id`,`cdate`) VALUES ('1','".$row['product_id']."','".$rel['currency_id']."','".$amt."','".date("Y-m-d")."','".$rel['unit_id']."','0','".$_SESSION['user_id']."','".$_SESSION['company_id']."','".date("Y-m-d h:i:s")."')"."<br>";
			$cnt++;
	}
}
?>