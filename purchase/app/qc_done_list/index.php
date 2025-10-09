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
		$where="";
		
		$appData = array();
		$i=1;
		$aColumns = array('trn.qctrn_id','qc.qc_no','qc.qc_date','pro.product_name','trn.qc_product_qty','grn.grn_no','grn.grn_date','qc_accepted','qty_reprocess','qc_rejected','trn.qc_status');
		$sIndexColumn = "trn.qctrn_id";
		$isWhere = array("qc.qc_status=0 and trn.qc_status=0");
		$sTable = "tbl_qc_trn as trn";			
		$isJOIN = array('left join tbl_qc as qc on qc.qc_id=trn.qc_id','left join product_mst as pro on pro.product_id=trn.qc_product','left join tbl_grn as grn on grn.grn_id=qc.grn_id');
		$hOrder = "trn.qctrn_id desc";
		//$hGroupby = array("trn.product_id");
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['qc_no'];
			$row_data[] = date("d-M-Y",strtotime($row['qc_date']));
			$row_data[] = $row['grn_no'];
			$row_data[] = date("d-M-Y",strtotime($row['grn_date']));
			$row_data[] = $row['product_name'];
			$row_data[] = $row['qc_accepted'];
			$row_data[] = $row['qc_rejected'];
			$row_data[] = $row['qty_reprocess'];
			
			//$row_data[] = '<a class="btn btn-xs btn-success" data-original-title="Add Qc" data-toggle="tooltip" data-placement="top" href="'.ROOT.'qc_add/'.$row['grn_trn_id'].'" ><i class="fa fa-plus"></i></a>';

			//$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Create PO" data-toggle="tooltip" data-placement="top" href="'.ROOT.'po_req_add/'.$row['product_id'].'/'.$row['po_ref_type'].'"><i class="fa fa-plus"></i></a>';
			//$row_data[] = $add_po_btn.' '.$poprint;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	
?>