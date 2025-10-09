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
		if(!empty($POST['branch_id'])){
				$where_db = " and sep.branch_id=".$POST['branch_id'];
			}
	
	$where=''; 

	$where.="  and DATE(so.sales_order_date)>='".date('Y-m-d',strtotime($s_date[0]))."' AND DATE(so.sales_order_date)<='".date('Y-m-d',strtotime($s_date[1]))."'";
	
	$aColumns = array('str.doc_no','l.l_name','bms.branch_name','sep.po_req_no','so.sales_order_no','so.sales_order_date','p.product_icode','p.product_name','p.product_icode','sep.sp_status','sep.sp_id','sep.company_id','sep.finish_status','sep.vendor_id','sep.product_id','sep.po_req_date','sep.rp_req_qty','sep.rp_po_qty','sep.in_process_qty_main','sep.bom_costing_id','sep.sales_order_trn_id','sep.shortclose_qty','sep.sales_order_trn_id');
  $sIndexColumn = "sep.sp_id";
  $isWhere = array("sp_status != 2 and sep.company_id='".$_SESSION['company_id']."'".$wher.$where_db);
  $sTable = "tbl_set_main_process as sep";			
  $isJOIN = array(
  	'left join tbl_ledger as l ON sep.vendor_id = l.l_id',
  	'left join product_mst as p ON sep.product_id = p.product_id',
  	'left join tbl_store_order_min_max as str ON str.order_id = sep.store_order_id',
  	'left join tbl_sales_ordertrn as so_trn ON so_trn.sales_ordertrn_id = sep.sales_order_trn_id',
  	'left join tbl_sales_order as so ON so_trn.sales_order_id = so.sales_order_id',
  	'left join branch_mst as bms on bms.branch_id=sep.branch_id'
  );
  $hOrder = "sep.sp_id desc";
	$hGroupby = array();
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {

		$query = "select GROUP_CONCAT(rp_id) as rp_id from tbl_request_product where status != 2 and in_process_qty > 0 and sp_id = " . $row['sp_id'];
		$res = brp_mysqli_fetch_assoc($dbcon->query($query)); 
		$rp_ids = $res['rp_id'];

		$query1 = "select ifnull(sum(start_qty),0) as start_qty from tbl_allocate_process where p_status != 2 and p_ref_id in (".$rp_ids.")";
		$res1 = brp_mysqli_fetch_assoc($dbcon->query($query1)); 
		$start_qty = $res1['start_qty'];

	/*	$dispatch_qty = "";
		$dispatch_date = "";

		if($row['sales_order_trn_id'] != "" && $row['sales_order_trn_id'] != 0){
			$que = "select  group_concat(intrn.product_qty separator '---') as dispatch_qty, group_concat(inv.invoice_no) as dispatch_no, group_concat(inv.invoice_date) as dispatch_date from tbl_invoicetrn as intrn
				left join tbl_invoice as inv on inv.invoice_id = intrn.invoice_id
				where intrn.trancation_status=0 and intrn.sales_ordertrn_id=".$row['sales_order_trn_id'];

				$res = $dbcon->query($que);
				$row1 = brp_mysqli_fetch_array($res);

			$dispatch_qty = $row1['dispatch_qty'];
			$dispatch_date = $row1['dispatch_date'];
		}*/

		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['sales_order_no'];
		$row_data[] = $row['l_name'];
		$row_data[] = $row['product_name'];
		$row_data[] = $row['po_req_no'];
		$row_data[] = $row['branch_name'];
		$row_data[] = $row['rp_req_qty'];

		if($row['finish_status'] == '1'){
			$row_data[] = '<button class="btn btn-xs btn-success">Complete</button>';
		}else if($start_qty > 0){
			$row_data[] = '<button class="btn btn-xs btn-info">In-Process</button>';
		}
		else{
			$row_data[] = '<button class="btn btn-xs btn-warning">Pending</button>';
		}
		// $row_data[] = $dispatch_qty;
		// $row_data[] = $dispatch_date;
		
		
		$appData[] = $row_data;
		
		$id++;
	}
	
	$output['aaData'] = $appData;
	echo json_encode( $output );
}


?>