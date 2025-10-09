<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');


if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];

		$where.="  and so.sales_order_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND so.sales_order_date<='".date('Y-m-d',strtotime($s_date[1]))."'";

		$whr='';
		if($POST['cust_id']){
			$whr .= ' and so.cust_id='.$POST['cust_id'];
		}
		
		if($POST['product_id']){
			$whr .= ' and trn.product_id='.$POST['product_id'];
		}

		if($POST['sales_order_id']){
			$whr .= ' and so.sales_order_id='.$POST['sales_order_id'];
		}

		$where .= $whr;
		
		$appData = array();
		$i=1;
		$aColumns = array('so.sales_order_no', 'so.sales_order_date', 'so.po_no','so.po_date', 'l.l_name', 'p.product_name', 'p.product_icode', 'trn.product_qty', 'trn.product_conv_qty', 'trn.product_rate', 'trn.product_amount', 'unit.unit_name', 'us.user_name','trn.rate_unit','trn.unit_id');
		$sIndexColumn = "sales_ordertrn_id";
		$isWhere = array("trn.sales_ordertrn_status = 0 and so.revise_status=0 and so.sales_order_status=0".$where);
		$sTable = "tbl_sales_ordertrn as trn";			
		$isJOIN = array('left join tbl_sales_order as so on so.sales_order_id=trn.sales_order_id','left join unit_mst as unit on unit.unitid = trn.rate_unit','left join tbl_ledger as l on l.l_id=so.cust_id','left join product_mst as p on p.product_id=trn.product_id','left join users as us on us.user_id=so.user_id');
		$hOrder = "sales_ordertrn_id desc";
		include('../../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			
			if($row['rate_unit']==$row['unit_id']){
				$product_qty = $row['product_qty'];
			}else{
				$product_qty = $row['product_conv_qty'];
			}

			$row_data[] = $id;
			$row_data[] = $row['sales_order_no'];
			$row_data[] = date('d-m-Y',strtotime($row['sales_order_date']));
			$row_data[] = $row['po_no'];
			$row_data[] = date('d-m-Y',strtotime($row['po_date']));
			$row_data[] = $row['l_name'];
			$row_data[] = $row['product_name'];
			$row_data[] = number_format($product_qty,4,".","");
			$row_data[] = $row['unit_name'];
			$row_data[] = number_format($row['product_rate'],2,".","");
			$row_data[] = number_format($row['product_amount'],2,".","");
			$row_data[] = $row['user_name'];
			 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}	
?>