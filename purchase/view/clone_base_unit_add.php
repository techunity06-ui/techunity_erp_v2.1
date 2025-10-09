<?php 
session_start();
include('../include/urlfile.php');

$query = "select req.purchaseordertrn_req__id,req.used_qty, otrn.unit_id, otrn.conv_unit_id, otrn.product_id from tbl_purchaseorder_req_trn as req 
left join tbl_purchaseordertrn as otrn on otrn.purchaseordertrn_id = req.purchaseordertrn_id";
$result = $dbcon->query($query);
while($row = brp_mysqli_fetch_array($result)){
	$product_detail2 = get_product_detail($dbcon,$row['product_id']);
	$type="base_unit";
	$unit_name  = getunitname($dbcon,$row['unit_id']);
	$conv_name  = getunitname($dbcon,$row['unit_id']);
	$ret_qty=convert_stock($dbcon,$row['used_qty'],$row['product_id'],$type);

	$info_req['used_base_qty']		= $ret_qty;
	$info_req['base_unit']			= $row['unit_id'];
	$info_req['conv_unit']			= $row['conv_unit_id'];

	
	$update_req = update_record('tbl_purchaseorder_req_trn', $info_req,"purchaseordertrn_req__id=".$row['purchaseordertrn_req__id'] , $dbcon);
}

$query1 = "select * from tbl_purchasetrntemp";
$result1= $dbcon->query($query1);
while($row1 = brp_mysqli_fetch_array($result1)){
	$product_detail = get_product_detail($dbcon,$row1['product_id']);
	$type1="base_unit";
	$unit_name1  = getunitname($dbcon,$product_detail['product_base_unit']);
	$ret_qty1=convert_stock($dbcon,$row1['product_qty'],$row1['product_id'],$type1);

	$info_temp['product_base_qty']		= $ret_qty1;
	$info_temp['base_unit_id']			= $product_detail['product_base_unit'];
	//var_dump($info_temp);
	$update_temp = update_record('tbl_purchasetrntemp', $info_temp,"purchaseordertrn_id=".$row1['purchaseordertrn_id'] , $dbcon);
} 


$query2 = "select inde.approve_indent_id,inde.approve_qty,req.rp_pid from approve_indent as inde
left join tbl_request_product as req on req.rp_id = inde.rp_id";
$result2= $dbcon->query($query2);
while($row2 = brp_mysqli_fetch_array($result2)){
	$product_detail1 = get_product_detail($dbcon,$row2['rp_pid']);
	$type2="base_unit";
	$unit_name2  = getunitname($dbcon,$product_detail1['product_base_unit']);
	$ret_qty2=convert_stock($dbcon,$row2['approve_qty'],$row2['rp_pid'],$type2);

	$info_approve['approve_base_qty']		= $ret_qty2;
	$info_approve['approve_base_unit']		= $product_detail1['product_base_unit'];
	//echo $product_detail1['product_name'].'--- Base Qty : '.$ret_qty2.' '.$unit_name2.'<br>';
	$update_approve = update_record('approve_indent', $info_approve,"approve_indent_id=".$row2['approve_indent_id'] , $dbcon);
} 
?>