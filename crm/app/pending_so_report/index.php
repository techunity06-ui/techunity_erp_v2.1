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

		$where.="  and so.delivery_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND so.delivery_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
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
		$aColumns = array('so.sales_order_no', 'so.sales_order_date', 'so.po_no','so.po_date','so.delivery_date', 'l.l_name', 'p.product_name','p.product_icode', 'trn.product_qty', 'trn.product_conv_qty', 'trn.product_rate', 'trn.product_amount', 'unit.unit_name', 'us.user_name','trn.rate_unit','trn.unit_id','trn.sales_ordertrn_id','(select sum(product_qty) from tbl_invoicetrn as itrn where trancation_status=0 and itrn.sales_ordertrn_id = trn.sales_ordertrn_id) as invoice_qty','(select sum(product_conv_qty) from tbl_invoicetrn as itrn where trancation_status=0 and itrn.sales_ordertrn_id = trn.sales_ordertrn_id) as invoice_conv_qty');
		$sIndexColumn = "trn.sales_ordertrn_id";
		$isWhere = array("trn.sales_ordertrn_status = 0 and so.revise_status=0 and trn.invoice_status=0 and so.invoice_status=0".$where);
		$sTable = "tbl_sales_ordertrn as trn";			
		$isJOIN = array('left join tbl_sales_order as so on so.sales_order_id=trn.sales_order_id','left join unit_mst as unit on unit.unitid = trn.rate_unit','left join tbl_ledger as l on l.l_id=so.cust_id','left join product_mst as p on p.product_id=trn.product_id','left join users as us on us.user_id=so.user_id','left join tbl_salesorder_delivery_date AS so_dt on so_dt.sales_ordertrn_id = trn.sales_ordertrn_id');
		$hOrder = "sales_ordertrn_id desc";
		include('../../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			
			$qty = $dbcon->query("SELECT * FROM tbl_reserve_stock WHERE stock_flage = 1 AND stock_status!=2 AND company_id = '".$_SESSION['company_id']."' AND sales_order_trn_id =".$row['sales_ordertrn_id']);
			$pcqty=0;$pqty=0;
			while($res = brp_mysqli_fetch_assoc($qty)){
				$qtys= $dbcon->query("SELECT IFNULL(sum(base_stock),0) as used_base,IFNULL(sum(convert_stock),0) as used_conv FROM tbl_reserve_stock WHERE stock_flage = 2 AND stock_status!=2 AND company_id = '".$_SESSION['company_id']."' AND perent_id = ".$res['reserve_id']);
				$res1 = brp_mysqli_fetch_assoc($qtys);
				
				$pqty=$pqty+($res['base_stock']-$res1['used_base']);
				$pcqty=$pcqty+($res['convert_stock']-$res1['used_conv']);
			}

			if($row['rate_unit']==$row['unit_id']){
				$product_qty = $row['product_qty'];
				$allocate_qty = $pqty;
				$invoice_pending = $row['product_qty'] - $row['invoice_qty']; 
			}else{
				$product_qty = $row['product_conv_qty'];
				$allocate_qty = $pcqty;
				$invoice_pending = $row['product_conv_qty'] - $row['invoice_conv_qty'];
			}
			$product_value = ($invoice_pending * $row['product_rate']);
			$row_data[] = $id;
			$row_data[] = $row['sales_order_no'];
			$row_data[] = date('d-m-Y',strtotime($row['sales_order_date']));
			$row_data[] = $row['po_no'];
			$row_data[] = date('d-m-Y',strtotime($row['po_date']));
			$row_data[] = date('d-m-Y',strtotime($row['delivery_date']));
			$row_data[] = $row['l_name'];
			$row_data[] = $row['product_name'] . ' -- '. $row['product_icode'];					
			$row_data[] = number_format($product_qty,4,".","");
			$row_data[] = number_format($invoice_pending,4,".","");
			$row_data[] = number_format($allocate_qty,4,".","");
			$row_data[] = $row['unit_name'];
			$row_data[] = number_format($row['product_rate'],2,".","");
			$row_data[] = number_format($product_value,2,".","");
			$row_data[] = $row['user_name'];
			 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}	
?>