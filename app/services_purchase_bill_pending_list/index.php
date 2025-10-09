<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

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
		$where_db = check_branch('po', $branch_id);
		$where.=" $where_db and po.company_id=".$_SESSION['company_id'];
		
		$appData = array();
		$i=1;
		$aColumns = array('po.purchaseorder_id','po.purchaseorder_no','po.purchaseorder_date','pro.product_name','tc.cat_name','led.l_name','bms.branch_name','po.userid','po.branch_id','potrn.product_qty','(select IFNULL(sum(product_qty),0) as qty from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.purchaseorder_id=po.purchaseorder_id and potrn.product_id=chtrn.product_id) as used_qty');
		$sIndexColumn = "po.purchaseorder_id";
		$isWhere = array("po.status=0 and potrn.purchaseordertrn_status=0 and po.purchase_status=0 and po.po_type=1 and potrn.used_status=1".$where);
		$sTable = "tbl_purchaseorder as po";			
		$isJOIN = array('left join tbl_purchaseordertrn as potrn on potrn.purchaseorder_id = po.purchaseorder_id','left join product_mst as pro on pro.product_id=potrn.product_id', 'left join tbl_category as tc on pro.product_category=tc.cat_id', 'left join tbl_ledger as led on led.l_id=po.vender_id','left join branch_mst as bms on bms.branch_id=po.branch_id');
		$hOrder = "po.purchaseorder_id desc";
		//$hGroupby = array("trn.product_id");
		//$having=" gtrn.product_qty > used_qty ";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			
			$pending=$row['product_qty']-$row['used_qty'];
			$row_data[] = $row['sr'];
			$row_data[] = $row['purchaseorder_no'];
			$row_data[] = date('d-m-Y',strtotime($row['purchaseorder_date']));
			$row_data[] = $row['l_name'];
			$row_data[] = $row['product_name'];
			$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$row_data[] = $row['branch_name'];
			$row_data[] = $row['product_qty'];
			$row_data[] = $pending;
			$row_data[] = find_user_name($dbcon,$row['userid']);
			
			$add_po_btn = '';
			if(in_array(PURCHASE_BILL_PENDING_ADD,$bulkAccessArray)){
				$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Add GRN" data-toggle="tooltip" data-placement="top" href="'.ROOT.'purchase_bill_pending/'.$row['grn_id'].'/'.$row['branch_id'].'"><i class="fa fa-plus"></i></a>';
			}
			$row_data[] = $add_po_btn;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	
?>