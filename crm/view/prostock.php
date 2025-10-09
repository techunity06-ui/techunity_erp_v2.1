<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';

$qry = $dbcon->query("SELECT * FROM product_mst WHERE product_status = 0 AND company_id IN (0,".$_SESSION['company_id'].")");
$str = '';
while($row=brp_mysqli_fetch_assoc($qry)){
	$stock = get_current_stock_new($dbcon,$row['product_id'],$row['product_base_unit']);
	if($stock>0){
		$str.=$row['product_id'].'  '.$row['product_name'].' - '.$stock.'<br>';
	}
}
echo $str;
?>