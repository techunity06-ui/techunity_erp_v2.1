<?php
session_start();
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "generate_report_sales_card") {
	$str = '';
	if(!empty($POST['party_sales_id'])){
		$chksales = $dbcon->query("SELECT * FROM tbl_product_party_sales WHERE party_sales_id = ".$POST['party_sales_id']);
		$getsales = brp_mysqli_fetch_assoc($chksales);
		$str.="<table class='table table-bordered table-stripped'>
		<thead>
		<tr>
		<th colspan='4'>Sales Card No: ".$getsales['sales_card_no']."</th>
		<th colspan='3'>Sales Card Date: ".date("d-M-Y",strtotime($getsales['sales_card_date']))."</th>
		</tr>
		<tr>
		<th>Product name</th>
		<th>Effected Date</th>
		<th>Valid Date</th>
		<th>Unit</th>
		<th>Price</th>
		<th>Conv. Unit</th>
		<th>Conv. rate</th>
		</tr>
		</thead>
		<tbody>";
		$chksalestrn = $dbcon->query("SELECT trn.*, pro.product_name, pro.product_base_unit, pro.product_conv_unit FROM tbl_salescardtrn AS trn LEFT JOIN product_mst AS pro ON pro.product_id = trn.product_id WHERE party_sales_id = ".$getsales['party_sales_id']);
		while($getsalestrn = brp_mysqli_fetch_assoc($chksalestrn)){
			$convrate = 0;
			$unit = getunitname($dbcon, $getsalestrn['unit_id']);
			$convunit = get_alter_unit($dbcon, $getsalestrn['product_id'],$getsalestrn['unit_id']);
			if($getsalestrn['product_base_unit']!=$getsalestrn['unit_id']){
				$convrate = get_product_rate_sales_time($dbcon,$getsalestrn['product_id'],$getsalestrn['product_base_unit']);
			}else{
				$convrate = get_product_rate_sales_time($dbcon,$getsalestrn['product_id'],$getsalestrn['unit_id']);
			}
			$str.="<tr>
			<td>".$getsalestrn['product_name']."</td>
			<td>".date("d-M-Y",strtotime($getsalestrn['affected_date']))."</td>
			<td>".date("d-M-Y",strtotime($getsalestrn['valid_date']))."</td>
			<td>".$unit."</td>
			<td>".$getsalestrn['price']."</td>
			<td>".$convunit."</td>
			<td>".$convrate."</td>
			</tr>";
		}
		$str.="</tbody>
		</table>";
	}
	echo $str;
} else if(strtolower($POST['mode']) == "report_elcon_sales_card") {
	$str = '';
	if(!empty($POST['elcon_sales_id'])){
		$chksales = $dbcon->query("SELECT * FROM tbl_product_sales_elcon WHERE elcon_sales_id = ".$POST['elcon_sales_id']);
		$getsales = brp_mysqli_fetch_assoc($chksales);
		$str.="<table class='table table-bordered table-stripped'>
		<thead>
		<tr>
		<th colspan='4'>Sales Card No: ".$getsales['sales_card_no']."</th>
		<th colspan='4'>Sales Card Date: ".date("d-M-Y",strtotime($getsales['sales_card_date']))."</th>
		</tr>
		<tr>
		<th>Product category</th>
		<th>Effected Date</th>
		<th>Valid Date</th>
		<th>Price</th>
		<th>Unit</th>
		<th>Rate1</th>
		<th>Rate2</th>
		<th>Rate3</th>
		</tr>
		</thead>
		<tbody>";
		$chkelcon = $dbcon->query("SELECT elcontrn.*, cat.cat_name FROM tbl_salescardelcontrn AS elcontrn LEFT JOIN tbl_category AS cat ON cat.cat_id = elcontrn.product_cat_id WHERE elcontrn.salescardelcontrn_status = 0 AND elcontrn.elcon_sales_id = ".$getsales['elcon_sales_id']);
		while($getelcon = brp_mysqli_fetch_assoc($chkelcon)){
			$unit = getunitname($dbcon, $getelcon['unit_id']);
			$str.="<tr>
			<td>".$getelcon['cat_name']."</td>
			<td>".date("d-M-Y",strtotime($getelcon['effected_date']))."</td>
			<td>".date("d-M-Y",strtotime($getelcon['valid_date']))."</td>
			<td>".$getelcon['price']."</td>
			<td>".$unit."</td>
			<td>".$getelcon['rate1']."</td>
			<td>".$getelcon['rate2']."</td>
			<td>".$getelcon['rate3']."</td>
			</tr>";
		}
		$str.="</tbody>
		</table>";
	}
	echo $str;
}
?>