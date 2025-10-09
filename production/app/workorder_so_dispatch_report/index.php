<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
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

	$where.="  and DATE(so.sales_order_date)>='".date('Y-m-d',strtotime($s_date[0]))."' AND DATE(so.sales_order_date)<='".date('Y-m-d',strtotime($s_date[1]))."'";

	if(!empty($POST['so_id'])){
		$where .= " and so.sales_order_id = " . $POST['so_id'];
	}
	if(!empty($POST['cust_id'])){
		$where .= " and so.cust_id = " . $POST['cust_id'];
	}
	
	$aColumns = array('so_trn.sales_ordertrn_id','inv.invoice_no','inv.invoice_date','so.sales_order_no','so.sales_order_date','so.po_no','so.po_date','p.product_name','p.product_icode','l.l_name','so_trn.product_qty','so_trn.product_conv_qty','so_trn.rate_unit','so.company_id','p.product_base_unit','p.product_conv_unit','inv_trn.product_qty as invoice_qty','inv_trn.unit_id');
  $sIndexColumn = "inv_trn.trancation_id";
  $isWhere = array("inv_trn.trancation_status != 2 and so_trn.sales_ordertrn_status != 2 and inv_trn.company_id='".$_SESSION['company_id']."'".$where);
  $sTable = "tbl_invoicetrn as inv_trn";			
  $isJOIN = array(
  	'left join tbl_invoice as inv ON inv_trn.invoice_id = inv.invoice_id',
  	'left join tbl_sales_ordertrn as so_trn ON inv_trn.sales_ordertrn_id = so_trn.sales_ordertrn_id',
  	'left join tbl_sales_order as so ON so_trn.sales_order_id = so.sales_order_id',
  	'left join tbl_ledger as l ON so.cust_id = l.l_id',
  	'left join product_mst as p ON p.product_id = inv_trn.product_id',
  );
  $hOrder = "so_trn.sales_ordertrn_id";
	$hGroupby = array();
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {

		$so_qty = 0;

		if($row['rate_unit'] == $row['product_conv_unit']){
			$so_qty = $row['product_conv_qty'];			
		}else{
			$so_qty = $row['product_qty'];
		}

		$unitname =  getunitname($dbcon,$row['rate_unit']);
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['sales_order_no'];
		$row_data[] = date('d M, Y',strtotime($row["sales_order_date"]));
		$row_data[] = $row['l_name'];
		$row_data[] = $row['po_no'];
		$row_data[] = date('d M, Y',strtotime($row["po_date"]));
		$row_data[] = $row['product_name'];
		$row_data[] = $so_qty . '  '.$unitname;
		$row_data[] = $row['invoice_no'];
		$row_data[] = date('d M, Y',strtotime($row["invoice_date"]));
		$row_data[] = $row['invoice_qty'];
		
		$appData[] = $row_data;
		
		$id++;
	}
	
	$output['aaData'] = $appData;
	echo json_encode( $output );
}


?>