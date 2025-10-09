<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/common_functions/common_functions.php");
include("../include/function_database_query.php");

$query = "SELECT product_id,product_base_unit,product_conv_unit FROM product_mst WHERE product_status = 0 and stock_add = 0";
$result = $dbcon->query($query);
$cnt = brp_mysqli_num_rows($result);
while($row = brp_mysqli_fetch_array($result)){

	$info = array();

	$info['product_id'] = $row['product_id'];
	$info['base_unit'] = $row['product_base_unit'];
	$info['conv_unit'] = $row['product_conv_unit'];


	$query_dstock='SELECT pro.product_id, pro.product_base_unit, pro.product_conv_unit, un.unit_name, c_un.unit_name as conv_unit_name, pro.product_name, (select IFNULL(sum(qc.base_stock),0) as base_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and qc.customer_id = 0 and qc.customer_id = "" and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 group by qc.product_id) as base_stock_add, (select IFNULL(sum(qc.base_stock),0) as base_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=2 and qc.customer_id = 0 and qc.customer_id = "" and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 group by qc.product_id) as base_stock_minus, (select IFNULL(sum(qc.convert_stock),0) as con_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.customer_id = 0 and qc.customer_id = "" and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 group by qc.product_id) as con_stock_add, (select IFNULL(sum(qc.convert_stock),0) as con_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.customer_id = 0 and qc.customer_id = "" and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 group by qc.product_id) as con_stock_minus, (select IFNULL(sum(qc.convert_stock),0) as convert_stock_add1 from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.customer_id = 0 and qc.customer_id = "" and qc.convert_unit=pro.product_conv_unit and qc.product_id=pro.product_id and qc.company_id=1 group by qc.product_id) as convert_stock_add1, (select IFNULL(sum(qc.convert_stock),0) as convert_stock_minus1 from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=2 and qc.customer_id = 0 and qc.customer_id = "" and qc.convert_unit=pro.product_conv_unit and qc.product_id=pro.product_id and qc.company_id=1 group by qc.product_id) as convert_stock_minus1, (select IFNULL(sum(qc.convert_stock),0) as base_stock_add1 from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.customer_id = 0 and qc.customer_id = "" and qc.convert_unit!=qc.convert_unit and qc.convert_unit=pro.product_conv_unit and qc.product_id=pro.product_id and qc.company_id=1 group by qc.product_id) as base_stock_add1, (select IFNULL(sum(qc.convert_stock),0) as base_stock_minus1 from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.customer_id = 0 and qc.customer_id = "" and qc.convert_unit!=qc.convert_unit and qc.convert_unit=pro.product_conv_unit and qc.product_id=pro.product_id and qc.company_id=1 group by qc.product_id) as base_stock_minus1 FROM product_mst as pro  left join unit_mst as un on un.unitid=pro.product_base_unit left join unit_mst as c_un on c_un.unitid=pro.product_conv_unit where 1 AND pro.product_status !=2 and product_id = '. $row['product_id'];
$result_dstock=$dbcon->query($query_dstock);

if(brp_mysqli_num_rows($result_dstock) > 0) {
	$row_dstock=brp_mysqli_fetch_assoc($result_dstock);

$stock=($row_dstock['base_stock_add']+$row_dstock['con_stock_add'])-($row_dstock['base_stock_minus']+$row_dstock['con_stock_minus']);

$conv_stock=($row_dstock['convert_stock_add1']+$row_dstock['base_stock_add1'])-($row_dstock['convert_stock_minus1']+$row_dstock['base_stock_minus1']);
	

	$info['product_name'] = $row_dstock['product_name'];
	$info['base_stock'] = $stock;
	$info['conv_stock'] = $conv_stock;
	$info['base_unit_name'] = $row_dstock['unit_name'];
	$info['conv_unit_name'] = $row_dstock['conv_unit_name'];

			
	$insert_id=add_record('meru_stock_temp',$info, $dbcon);	
}

		
	$dbcon->query("update product_mst set stock_add = 1 where product_id = " . $row['product_id']);
} 


?>