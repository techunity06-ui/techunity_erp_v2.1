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
					PURCHASE_BILL_PENDING_ADD
		]);
		$where="";

		
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('grn', $branch_id);
		$where.=" $where_db and grn.company_id=".$_SESSION['company_id'];
		
		$appData = array();
		$i=1;
		$aColumns = array('grn.grn_id','gtrn.product_qty','grn.grn_no','grn.grn_date','pro.product_name','prs.process_name','tc.cat_name','led.l_name','bms.branch_name','grn.user_id','grn.gir_no','grn.invoice_no','(select IFNULL(sum(product_qty),0) as qty  from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.grn_id=grn.grn_id and gtrn.product_id=chtrn.product_id) as used_qty','grn.branch_id','po.po_type');
		$sIndexColumn = "grn.grn_id";
		$isWhere = array("grn.grn_status=0 and gtrn.grn_trn_status=0 and grn.ref_type in(1,2) and gtrn.purchase_status=0 and  po.po_type=2".$where);
		$sTable = "tbl_grn as grn";			
		$isJOIN = array('left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id','left join product_mst as pro on pro.product_id=gtrn.product_id','left join process_mst as prs on prs.process_id=gtrn.process_id','left join tbl_category as tc on pro.product_category=tc.cat_id','left join tbl_ledger as led on led.l_id=grn.vender_id','left join branch_mst as bms on bms.branch_id=grn.branch_id','left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id = gtrn.job_work_po_trn_id','left join tbl_purchaseorder as po on po.purchaseorder_id  = potrn.purchaseorder_id');
		$hOrder = "grn.grn_id desc";
		//$hGroupby = array("trn.product_id");
		//$having=" gtrn.product_qty > used_qty ";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			
			$pending=$row['product_qty']-$row['used_qty'];
			$row_data[] = $row['sr'];
			$row_data[] = $row['grn_no'];
			$row_data[] = date('d-m-Y',strtotime($row['grn_date']));
			$row_data[] = $row['l_name'];
			$row_data[] = $row['product_name'];
			$row_data[] = $row['process_name'];
			$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$row_data[] = $row['branch_name'];
			$row_data[] = number_format($row['product_qty'],4,'.','');
			$row_data[] = number_format($pending,4,'.','');
			$row_data[] = find_user_name($dbcon,$row['user_id']);
			
			$add_po_btn = '';
			if(in_array(PURCHASE_BILL_PENDING_ADD,$bulkAccessArray)){
				$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Add GRN" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'purchase_bill_pending/'.$row['grn_id'].'/'.$row['branch_id'].'"><i class="fa fa-plus"></i></a>';
			}
			$row_data[] = $add_po_btn;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	
?>