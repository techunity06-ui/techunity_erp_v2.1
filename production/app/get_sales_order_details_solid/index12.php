<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
error_reporting(0);
include("../../config/session.php");
include("../../include/coman_function.php");
include("../../include/function_database_query.php");
/*if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')*/ 
{ 
    /*if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) */
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['mode']) == "generate_report_min") {
			
		$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);

		$appData = array();
		$i=1;
		//+IFNULL(qc_total_rejected,0)
		$aColumns = array('so.sales_order_no','so.sales_order_date','led.l_name','so_trn.product_qty','so_trn.sales_ordertrn_id','mst.product_name','so_trn.product_id',);
		$sIndexColumn = "so_trn.sales_ordertrn_id";
		$isWhere = array("so_trn.sales_ordertrn_status=0");
		$sTable = "tbl_sales_ordertrn as so_trn";
		
		$isJOIN = array("left join tbl_sales_order as so on so.sales_order_id=so_trn.sales_order_id","left join tbl_ledger as led on led.l_id=so.cust_id","left join product_mst as mst on mst.product_id=so_trn.product_id");
				
		$hOrder = "so_trn.sales_ordertrn_id desc";
		//$hGroupby = "pro.product_id";
		//$having=" stock_in_new < pro.product_min_stock ";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {

			$row_data = array();
			$row_data[] = $row['sales_order_no'];
			$row_data[] = $row['sales_order_date'];
			$row_data[] = $row['l_name'];
			$row_data[] = $row['product_name'];
			$row_data[] = $row['product_qty'];
			
			$view='<a class="btn btn-xs btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" href="'.ROOT.'sorequesproduct/'.$row['product_id'].'/'.$row['sales_ordertrn_id'].'"><i class="fa fa-paper-plane"></i> Request</a>';
			
			$row_data[] = $view;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode($output );
				
		}
		
	}
    
}
/*
else {
    die("Error - 1");
}*/

?>