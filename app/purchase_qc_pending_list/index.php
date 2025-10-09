<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
//check permission for get sales order details
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    QC_DONE_PURCHASE_QC_PENDING_CREATE
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
			$where_db = check_branch('trn', $branch_id);
		
		$appData = array();
		$i=1;
		$aColumns = array('trn.grn_trn_id','grn.grn_no','grn.grn_date','cust.l_name','tc.cat_name','pro.product_name','trn.product_qty','branc.branch_name','trn.product_qc','trn.user_id');
		$sIndexColumn = "trn.grn_trn_id";
		$isWhere = array("grn.grn_status=0 and trn.grn_trn_status=0 and grn.qc_status=0 and trn.product_qc=0 and grn.ref_type='2' and trn.company_id=".$_SESSION['company_id'].$where_db);
		$sTable = "tbl_grn_trn as trn";			
		$isJOIN = array('left join product_mst as pro on pro.product_id=trn.product_id','left join tbl_category as tc on pro.product_category=tc.cat_id', 'left join tbl_grn as grn on grn.grn_id=trn.grn_id','left join tbl_ledger as cust on cust.l_id=grn.vender_id','left join branch_mst as branc on branc.branch_id=trn.branch_id');
		$hOrder = "trn.grn_trn_id desc";
		//$hGroupby = array("trn.product_id");
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['grn_no'];
			$row_data[] = date("d-M-Y",strtotime($row['grn_date']));
			$row_data[] = $row['l_name'];
			$row_data[] = $row['product_name'];
			$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$row_data[] = $row['product_qty'];
			$row_data[] = find_user_name($dbcon,$row['user_id']);
			if($_SESSION['branch_id']==0){
					$row_data[] = $row['branch_name'];
				}
			
			if(in_array(QC_DONE_PURCHASE_QC_PENDING_CREATE,$bulkAccessArray)) {
				$row_data[] = '<a class="btn btn-xs btn-success" data-original-title="Add Qc" data-toggle="tooltip" data-placement="top" href="'.ROOT.'poqc_add/'.$row['grn_trn_id'].'" ><i class="fa fa-plus"></i></a>';
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