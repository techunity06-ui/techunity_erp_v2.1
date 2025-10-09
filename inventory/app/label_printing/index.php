<?php
session_start();
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
	$company_config = getCompanyConfiguration($dbcon);
	$process_id = $company_config['label_print_process_id'];
	$where="";
	if($process_id > 0){
		$where=" and batch.process_id = '" . $process_id ."'";
	}else{
		$where=" and batch.process_id = '-555'";
	}
	$where.="  and DATE(batch.cdate) >= '".date('Y-m-d',strtotime($s_date[0]))."' AND DATE(batch.cdate) <= '".date('Y-m-d',strtotime($s_date[1]))."'";
	$where .= " and batch.company_id = " . $_SESSION['company_id'];
	$appData = array();
	$i=1;
	$aColumns = array('batch.batch_no','grn.grn_no','p.product_name','p.product_icode','pr.process_name','batch.accept_qty','sp.po_req_no','batch.batch_id');
	$sIndexColumn = "batch.batch_id";
	$isWhere = array('batch.process_id > 0 and batch.qc_status = 1 and stock_approval_status = 1 and accept_qty > 0 '.$where);
	$sTable = "tbl_batch_data as batch";
	$isJOIN = array('left join product_mst as p on p.product_id=batch.product_id','left join process_mst as pr on pr.process_id=batch.process_id','left join tbl_grn as grn on grn.grn_id=batch.grn_id','left join tbl_grn_trn as grnt on grnt.grn_trn_id=batch.grn_trn_id','left join tbl_grn_sub_trn as sbgrn on sbgrn.grn_trn_id=grnt.grn_trn_id','left join tbl_request_product as rp on sbgrn.rp_id=rp.rp_id','left join tbl_set_main_process as sp on sp.sp_id=rp.sp_id');
	$hOrder = "batch.batch_id desc";
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['po_req_no'];
		$row_data[] = $row['batch_no'];
		$row_data[] = $row['grn_no'];
		$row_data[] = $row['product_name']. " -- (".$row['product_icode'].")";
		$row_data[] = $row['process_name'];
		$row_data[] = $row['accept_qty'];
		
		$app_btn='<a class="btn btn-xs btn-primary" data-original-title="Print" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.INVENTORY_ROOT.'label_printing/'.$row['batch_id'].'"><i class="fa fa-print"></i> With CE</a>';
		$ec_app_btn='<a class="btn btn-xs btn-success" data-original-title="Print" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.INVENTORY_ROOT.'label_printing_without_ce_ecrep/'.$row['batch_id'].'"><i class="fa fa-print"></i> Without CE & ECREP</a>';
			
		$row_data[] = $app_btn . '  '.$ec_app_btn;
	 
	$appData[] = $row_data;
	$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
?>