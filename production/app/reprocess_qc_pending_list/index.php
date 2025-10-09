<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    QC_DONE_PARTS_QC_PENDING_ADD
]);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		$where="";
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('batch', $branch_id);
		if(!empty($POST['process_id'])){
			$pwhere=" and batch.process_id=".$POST['process_id'];
		}
		
		$appData = array();
		$i=1;
		$aColumns = array('tc.cat_name','pro.product_name','pmst.process_name','batch.batch_qty','branc.branch_name','batch.user_id','batch.batch_id','batch.batch_no');
		$sIndexColumn = "batch.batch_id";
		$isWhere = array("batch.status = 0 and reprocess_qc = 1 and batch.qc_status = 0 ".$pwhere.' '.$where_db." and batch.company_id=".$_SESSION['company_id']);
		// $sTable = "tbl_grn_trn as trn";		
		$sTable = "tbl_batch_data as batch";			
		$isJOIN = array('left join product_mst as pro on pro.product_id=batch.product_id'
						,'left join tbl_category as tc on pro.product_category=tc.cat_id'
						,'left join branch_mst as branc on branc.branch_id=batch.branch_id'
						,'left join process_mst as pmst on pmst.process_id=batch.process_id');
		$hOrder = "batch.batch_id desc";
		// $hGroupby = array("batch.grn_trn_id,trn.process_id");
		//$hGroupby = array("trn.grn_trn_id,");

		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
			$row_data[] = $row['sr'];
			$row_data[] = $row['product_name'];
			$row_data[] = $cat_name;
		if(empty($POST['process_id'])){
			$row_data[] = $row['process_name'];
		}
		$row_data[] = $row['batch_no'];
			$row_data[] = $row['batch_qty'];
			$row_data[] = find_user_name($dbcon,$row['user_id']);
			
			if($_SESSION['branch_id']==0){
					$row_data[] = $row['branch_name'];
				}

			if(in_array(QC_DONE_PARTS_QC_PENDING_ADD,$bulkAccessArray)) {
				if(!empty($POST['process_id'])){
					$row_data[] = '<a class="btn btn-xs btn-success" data-original-title="Add Qc" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'reprocess_qc_add/'.$row['batch_id'].'/'.$POST['process_id'].'" ><i class="fa fa-plus"></i></a>';
				}else{
					$row_data[] = '<a class="btn btn-xs btn-success" data-original-title="Add Qc" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'reprocess_qc_add/'.$row['batch_id'].'" ><i class="fa fa-plus"></i></a>';
				}
			}
			
			//$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Create PO" data-toggle="tooltip" data-placement="top" href="'.ROOT.'po_req_add/'.$row['product_id'].'/'.$row['po_ref_type'].'"><i class="fa fa-plus"></i></a>';
			//$row_data[] = $add_po_btn.' '.$poprint;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	
?>