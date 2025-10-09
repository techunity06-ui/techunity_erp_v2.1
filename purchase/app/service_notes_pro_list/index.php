<?php
session_start();
$AJAX = true;

$path = '../../../';
$include = '../../../include/';

include($path."config/config.php");
//error_reporting(E_ALL);
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
			OVERDUE_PO_PRO_ADD
		]);
		$where="";

		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('po', $branch_id);
		$where.=" $where_db ";
		
		$where_company=check_company('po');

		$where.=" $where_company";

		//$where_user=check_user('po');

		//$where.=" $where_user";

		/*trn.product_qty>(select IFNULL(sum(product_qty+tolerance),0) as qty  from tbl_grn_trn as chtrn 
		left join tbl_grn as gn on gn.grn_id=chtrn.grn_id
		where chtrn.grn_trn_status=0 and gn.ref_type=2 and chtrn.purchaseorder_id=trn.purchaseorder_id and trn.product_id=chtrn.product_id)*/
		
		$appData = array();
		$i=1;
		$aColumns = array('trn.purchaseordertrn_id','led.l_name','po.purchaseorder_no','po.purchaseorder_date','tc.cat_name','pro.product_name','bms.branch_name','trn.product_qty','trn.product_id','trn.unit_id','trn.purchaseorder_id','trn.user_id','(select IFNULL(sum(product_qty),0) as qty  from tbl_grn_sub_trn as chtrn 
		where chtrn.status=0 and chtrn.purchaseordertrn_id=trn.purchaseordertrn_id and trn.product_id=chtrn.product_id) as done_qty');
		$sIndexColumn = "trn.purchaseordertrn_id";
		$isWhere = array("trn.used_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po_approval_status=1 and po.po_type = 1".$where);
		$sTable = "tbl_purchaseordertrn as trn";			
		$isJOIN = array('left join product_mst as pro on pro.product_id=trn.product_id','left join tbl_category as tc on pro.product_category=tc.cat_id','left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id','left join tbl_ledger as led on led.l_id=po.vender_id','left join branch_mst as bms on bms.branch_id=trn.branch_id');
		$hOrder = "trn.purchaseordertrn_id desc";
		//$hGroupby = array("trn.product_id");
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['l_name'];
			$row_data[] = $row['purchaseorder_no'];
			$row_data[] = date('d-m-Y',strtotime($row['purchaseorder_date']));
			$row_data[] = $row['product_name'];
			$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$row_data[] = $row['branch_name'];
			$row_data[] = $row['product_qty'];
			$row_data[] = $row['done_qty'];
			
			$due_qty = $row['product_qty']-$row['done_qty'];
			$row_data[] = round($due_qty,4);
			$row_data[] = find_user_name($dbcon,$row['user_id']);
			
			$add_po_btn = '';
			if(in_array(OVERDUE_PO_PRO_ADD,$bulkAccessArray)){
				$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Add Service" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'service_add_po/'.$row['purchaseorder_id'].'"><i class="fa fa-plus"></i></a>';
			}
			$row_data[] = $add_po_btn;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	
?>