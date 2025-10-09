<?php 
session_start();
include('../include/urlfile.php');	

$query = "SELECT st.*,pro.* FROM `tbl_stock_trn` as st
LEFT JOIN product_mst as pro on pro.product_id=st.product_id
WHERE st.ref_name='opening_stock' and pro.product_type='3' and st.stock_status=0";
$result = $dbcon->query($query);

while($row = brp_mysqli_fetch_array($result)){
	$info['stock_status']=2;
	$updateid = update_record('tbl_stock_trn', $info,"stock_id=".$row['stock_id'] , $dbcon);
}


$query1 = "SELECT st.*,pro.* FROM `opening_stock_mst` as st
LEFT JOIN product_mst as pro on pro.product_id=st.product_id
WHERE pro.product_type='3' and st.status=0";
$result1 = $dbcon->query($query1);

while($row1 = brp_mysqli_fetch_array($result1)){
	$info1['status']=2;
	$updateid = update_record('opening_stock_mst', $info1,"opening_stock_id=".$row1['opening_stock_id'] , $dbcon);
}

$query2 = "SELECT st.*,pro.* FROM `process_opening_stock_mst` as st
LEFT JOIN product_mst as pro on pro.product_id=st.product_id
WHERE pro.product_type='3' and st.status=0";
$result2 = $dbcon->query($query2);

while($row2 = brp_mysqli_fetch_array($result2)){
	$info2['status']=2;
	$updateid = update_record('process_opening_stock_mst', $info2,"process_opening_stock_id=".$row1['process_opening_stock_id'] , $dbcon);
}

if($updateid){
	echo "Data Updated Successfully";
}

?>