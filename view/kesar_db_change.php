<?php
include_once("../config/config.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
include_once("../include/function_database_query.php");

$qry = "SELECT * FROM `tbl_returnable_channal_item` WHERE `status` != 2 AND `approve_status` = 1";
$res =	$dbcon->query($qry);
while($row = brp_mysqli_fetch_assoc($res)){
	$qry1 = "select * from tbl_reserve_stock where stock_status = 0 and stock_flage =1 and ref_name='returning_receipt' and ref_id = " . $row['id'];

	$res1 =	$dbcon->query($qry1);
	$row1 = brp_mysqli_fetch_assoc($res1);
	
	$qry2 = "select * from tbl_reserve_stock where stock_status = 0 and stock_flage =2 and ref_name='returning_receipt' and ref_id = " . $row['id'];

	$res2 =	$dbcon->query($qry2);
	$count = brp_mysqli_num_rows($res2);

	if($count == 0){
		$res_stock = [];
		foreach($row1 as $key => $value){
			$res_stock[$key] = $value;
		}
		$res_stock['stock_flage'] = 2;
		unset($res_stock['reserve_id']);
		$insert_id=add_record('tbl_reserve_stock', $res_stock, $dbcon);	
		echo "new reserved id " . $insert_id;
		echo "</br></br>";
	}
	
	echo 'update : ' . $row['id'];
	echo "</br></br>";
}



$qry = "SELECT * FROM `tbl_returnable_channal_item` WHERE `status` = 2 ";
$res =	$dbcon->query($qry);
while($row = brp_mysqli_fetch_assoc($res)){
	$qry1 = "select * from tbl_reserve_stock where stock_status = 0 and stock_flage =1 and ref_name='returning_receipt' and ref_id = " . $row['id'];

	$res1 =	$dbcon->query($qry1);
	$row1 = brp_mysqli_fetch_assoc($res1);
	

	$update['stock_status'] = 2;
	$updateid=update_record('tbl_reserve_stock', $update,"reserve_id=".$row1['reserve_id'] , $dbcon);

	$stock_id = $row1['stock_id'];

	$qry2 = "select * from tbl_stock_trn where stock_id = " . $stock_id;
	$res2 =	$dbcon->query($qry2);
	$row2 = brp_mysqli_fetch_assoc($res2);

	$qry3 = "select * from tbl_stock_trn where stock_status = 0 and stock_flage =2 and  perent_id = ".$stock_id;

	$res3 =	$dbcon->query($qry3);
	$count3 = brp_mysqli_num_rows($res3);
	if($count3 > 0){
		while($row3 = brp_mysqli_fetch_assoc($res3)){
			$rem_stock['stock_status'] = 2;
			$updateid=update_record('tbl_stock_trn', $rem_stock,"stock_id=".$row3['stock_id'], $dbcon);

			$base_stock = $row2['used_base_stock'];
			$conv_stock = $row2['used_convert_stock'];

			$upd_stock['used_base_stock'] = $base_stock - $row1['base_stock'];
			$upd_stock['used_convert_stock'] = $base_stock - $row1['convert_stock'];

			$updateid=update_record('tbl_stock_trn', $upd_stock,"stock_id=".$stock_id, $dbcon);
		}
	} else {
		$base_stock = $row2['used_base_stock'];
		$conv_stock = $row2['used_convert_stock'];

		$upd_stock['used_base_stock'] = $base_stock - $row1['base_stock'];
		$upd_stock['used_convert_stock'] = $base_stock - $row1['convert_stock'];

		$updateid=update_record('tbl_stock_trn', $upd_stock,"stock_id=".$stock_id, $dbcon);
	}
	
	echo 'update : ' . $row['id'];
	echo "</br></br>";



	echo $qry2 = "select * from tbl_stock_trn where stock_status = 0 and stock_flage = 2 and ref_name ='returning_receipt' and ref_id = " . $row['id'];
	$res2 =	$dbcon->query($qry2);
	$row2 = brp_mysqli_fetch_assoc($res2);
	if($row2['perent_id'] == 0){
		$dl_status['stock_status'] = 2; 
		$updateid=update_record('tbl_stock_trn', $dl_status,"stock_id=".$row2['stock_id'], $dbcon);	
		
	}
	

}

?>