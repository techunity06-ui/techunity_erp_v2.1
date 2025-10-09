<?php

session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
error_reporting(E_ALL);
include($path."config/image.php");
$image = new SimpleImage();

		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['mode']) == "generate_report") {
		
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
	
	$where=''; 
	 $s_where.=" and stock_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND stock_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
	
	if($POST['prod_id'] != ""){
		$where = " and product_id = " . $POST['prod_id'];
	}
	
	$appData = array();
	$i=1;
	$aColumns = array('pro.product_id','pro.product_name', 'pro.product_base_unit','uns.unit_name');
	$sIndexColumn = "pro.product_id";
	$isWhere = array("pro.product_status = 0 and product_id in (SELECT GROUP_CONCAT(product_id) from tbl_stock_trn where stock_flage = 1 and stock_status = 0 ".$s_where." group by product_id)".$where);
	$sTable = "product_mst as pro";
	$isJOIN = array('left join unit_mst as uns on uns.unitid=pro.product_base_unit');
	// $isJOIN = array();
	// $isJOIN=array_merge();
	$hOrder = "pro.product_id";
	$hGroupby = array();
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $id;
		$row_data[] = $row['product_name'];

		$opening_stock=0;$in_stock=0;$out_stock=0;$balance_stock;
		// $opening_stock = get_current_opening_stock($dbcon,$row['product_id'],$row['product_base_unit'],$s_date[0],$s_date[1]);
		$opening_stock = get_current_opening_stock_below_start_date($dbcon,$row['product_id'],$row['product_base_unit'],$s_date[0],$s_date[1]);
		$in_stock = get_total_in_stock($dbcon,$row['product_id'],$row['product_base_unit'],$s_date[0],$s_date[1]);
		$out_stock = get_total_out_stock($dbcon,$row['product_id'],$row['product_base_unit'],$s_date[0],$s_date[1]);
		// var_dump($opening_stock);
		// var_dump($in_stock);
		// var_dump($out_stock);
		$opening_stock_link = ROOT.'product_ledger/opening/'.$row['product_id']."/".$s_date[0]."/".$s_date[1];
		$in_stock_link  = ROOT.'product_ledger/in/'.$row['product_id']."/".$s_date[0]."/".$s_date[1];
		$out_stock_link  = ROOT.'product_ledger/out/'.$row['product_id']."/".$s_date[0]."/".$s_date[1];
		$balance_stock_link = ROOT.'product_ledger/balance/'.$row['product_id']."/".$s_date[0]."/".$s_date[1];
		$balance_stock = ($opening_stock + $in_stock) - $out_stock;
		$row_data[] = '<a target="_blank" href="'.$opening_stock_link.'" class="text-info;">'.$opening_stock. ' ' . $row['unit_name'].'</a>';
		$row_data[] = '<a target="_blank" href="'.$in_stock_link.'" style="text-align:center;color:green;">'.$in_stock. ' ' . $row['unit_name'].'</a>';
		$row_data[] = '<a target="_blank" href="'.$out_stock_link.'" style="text-align:center;color:red;">'.$out_stock. ' ' . $row['unit_name'].'</a>';
		$row_data[] = '<a target="_blank" href="'.$balance_stock_link.'" style="text-align:center;color:blue;">'.$balance_stock. ' ' . $row['unit_name'].'</a>';
		$appData[] = $row_data;
		
		$id++;
	}
	
	$output['aaData'] = $appData;
	echo json_encode( $output );
}


?>