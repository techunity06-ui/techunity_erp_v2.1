<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');
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
	 $company_config = getCompanyConfiguration($dbcon);		

	if(strtolower($POST['mode']) == "fetch") {
		$purchase_pro_search=$company_config['purchase_pro_search'];
		$pro_search=explode(",", $purchase_pro_search);
		$where="";
		
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('trn', $branch_id);
		
		$appData = array();
		$i=1;
		$aColumns = array('pro.product_icode','sp.po_req_no','so.sales_order_no', 'dr.drawing_number','trn.grn_trn_id','grn.grn_no','batch.base_qty','batch.conv_qty','grn.grn_date','cust.l_name','tc.cat_name','pro.product_name','trn.product_qty','branc.branch_name','bu.unit_name as base_unit_name','cu.unit_name as conv_unit_name','trn.product_qc','trn.user_id','batch.batch_id','batch.batch_qty');
		// $sIndexColumn = "trn.grn_trn_id";
		$sIndexColumn = "batch.batch_id";
		$isWhere = array("batch.status = 0 and batch.qc_status = 0 and grn.grn_status=0 and trn.grn_trn_status=0 and grn.qc_status=0 and trn.product_qc=0 and grn.ref_type in(2,4) and trn.company_id=".$_SESSION['company_id'].$where_db);
		// $sTable = "tbl_grn_trn as trn";	
		$sTable = "tbl_batch_data as batch";			
		$isJOIN = array('left join tbl_grn_trn as trn on trn.grn_trn_id=batch.grn_trn_id',
			'left join tbl_grn_sub_trn as strn on strn.grn_trn_id=trn.grn_trn_id'
						,'left join tbl_request_product as rp on rp.rp_id=strn.rp_id'
						,'left join tbl_set_main_process as sp on sp.sp_id=rp.sp_id'
						,'left join tbl_sales_ordertrn as so_trn on sp.sales_order_trn_id=so_trn.sales_ordertrn_id'
						,'left join tbl_sales_order as so on so.sales_order_id=so_trn.sales_order_id',
			'left join product_mst as pro on pro.product_id=trn.product_id',
			'left join tbl_category as tc on pro.product_category=tc.cat_id', 
			'left join tbl_grn as grn on grn.grn_id=trn.grn_id',
			'left join unit_mst as bu on bu.unitid=batch.base_unit',
			'left join unit_mst as cu on cu.unitid=batch.conv_unit',
			'left join tbl_ledger as cust on cust.l_id=grn.vender_id',
			'left join branch_mst as branc on branc.branch_id=trn.branch_id',
			'left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id');
		// $hOrder = "trn.grn_trn_id desc";
		$hOrder = "batch.batch_id desc";
		$hGroupby = array("batch.batch_id");
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['grn_no'];
			$row_data[] = date("d-M-Y",strtotime($row['grn_date']));
			$row_data[] = $row['po_req_no'];
			$row_data[] = $row['sales_order_no'];
			$row_data[] = $row['l_name'];

			$drawing_number = "";
			$item_code = "";
			 if(in_array('drawing',$pro_search)){
		            $drawing_number = " -- (".$row['drawing_number'].")";
		        }
		        if(in_array('item',$pro_search)){
		            $item_code = " -- (".$row['product_icode'].")";
		        }

			$row_data[] = $row['product_name'].' '.$item_code.' '.$drawing_number;
			$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$row_data[] = $row['base_qty'] . ' ' .$row['base_unit_name'] . '<br>'.$row['conv_qty'] . ' ' .$row['conv_unit_name'] ;
			$row_data[] = find_user_name($dbcon,$row['user_id']);
			if($_SESSION['branch_id']==0){
				$row_data[] = $row['branch_name'];
			}
			
			if(in_array(QC_DONE_PURCHASE_QC_PENDING_CREATE,$bulkAccessArray)) {
				$row_data[] = '<a class="btn btn-xs btn-success" data-original-title="Add Qc" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'poqc_add/'.$row['batch_id'].'" ><i class="fa fa-plus"></i></a>';
			}

			//$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Create PO" data-toggle="tooltip" data-placement="top" href="'.ROOT.'po_req_add/'.$row['product_id'].'/'.$row['po_ref_type'].'"><i class="fa fa-plus"></i></a>';
			//$row_data[] = $add_po_btn.' '.$poprint;
			$row_data[] = '<input type="checkbox" chk name="chk[]" data-batch_id="'.$row['batch_id'].'" value="'.$row['batch_id'].'"/>';
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	
?>