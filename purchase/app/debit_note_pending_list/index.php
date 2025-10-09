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
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
				DEBIT_NOTE_PENDING_ADD
		]);
		$where="";

		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('mrn', $branch_id);
		$where.=" $where_db ";
		
		$where_company=check_company('mrn');

		$where.=" $where_company";

		$where_user=check_user('mrn');

		$where.=" $where_user";
		
		$appData = array();
		$i=1;
		$aColumns = array('mrn.mrn_id','mrn.grn_no','pro.product_name','bms.branch_name','tc.cat_name','led.l_name','mtrn.rejected_qty','mrn.qc_no','qc.qc_date','grn.grn_no as gno','grn.grn_date','mrn.user_id','(select IFNULL(sum(product_qty),0) as qty  from tbl_debitnote_trn as chtrn where chtrn.debitnote_trn_status=0 and chtrn.grn_id=mrn.grn_no and mtrn.product_id=chtrn.product_id) as used_qty');
		$sIndexColumn = "mrn.mrn_id";
		$isWhere = array("mrn.mrn_status=0 and mtrn.mrn_trn_status=0 and grn.ref_type=2 and grn.purchase_status=1");
		$sTable = "tbl_mrn as mrn";			
		$isJOIN = array('left join tbl_mrn_trn as mtrn on mtrn.mrn_no=mrn.mrn_id','left join product_mst as pro on pro.product_id=mtrn.product_id','left join tbl_category as tc on pro.product_category=tc.cat_id','left join tbl_grn as grn on grn.grn_id=mrn.grn_no','left join tbl_qc as qc on qc.qc_id=mrn.qc_no','left join tbl_ledger as led on led.l_id=grn.vender_id','left join branch_mst as bms on bms.branch_id=mrn.branch_id');
		$hOrder = "mrn.mrn_id desc";
		//$hGroupby = array("trn.product_id");
		$having=" mtrn.rejected_qty > used_qty ";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			
			$pending=$row['rejected_qty']-$row['used_qty'];
			$row_data[] = $row['sr'];
			$row_data[] = $row['gno'];
			$row_data[] = $row['grn_date'];
			$row_data[] = $row['qc_no'];
			$row_data[] = $row['qc_date'];
			$row_data[] = $row['l_name'];
			$row_data[] = $row['product_name'];
			$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$row_data[] = $row['branch_name'];
			$row_data[] = $row['rejected_qty'];
			$row_data[] = $pending;
			$row_data[] = find_user_name($dbcon,$row['user_id']);
			
			$add_po_btn = '';
			if(in_array(DEBIT_NOTE_PENDING_ADD,$bulkAccessArray)){
				$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Add GRN" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'debitnote_add_qc/'.$row['grn_no'].'"><i class="fa fa-plus"></i></a>';
			}
			$row_data[] = $add_po_btn;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	
?>