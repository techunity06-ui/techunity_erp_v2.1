<?php
session_start();
$AJAX = true;
	include('../../include/urlfileinner.php');
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	INVENTORY_GRN_LIST_SLUG_VIEW,INVENTORY_GRN_LIST_SLUG_CREATE,INVENTORY_GRN_LIST_SLUG_UPDATE,INVENTORY_GRN_LIST_SLUG_DELETE
]);

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

$companyConfiguration=getCompanyConfiguration($dbcon);

if(brp_strtolower($POST['mode']) == "fetch") {
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where=''; 
		$where.=" and grn.challan_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND grn.challan_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		$where.=" and trn.grn_status = 0 and trn.approve_status = 1 and grn.returnable_type = 'returnable' and trn.company_id = " . $_SESSION['company_id'];
		$appData = array();
		$i=1;
		$aColumns = array('trn.id','grn.channal_id', 'grn.challan_date','p.product_name','p.product_icode', 'trn.item_qty', 'trn.rr_approve_qty','trn.rr_disapprove_qty','trn.item_id','trn.returnable_id','ledg.l_name');
		
		$sIndexColumn = "trn.id";
		$isWhere = array(" trn.status = 0".$where);
		$sTable = "tbl_returnable_channal_item as trn";
		$isJOIN = array('left join tbl_returnable_channal as grn on grn.id=trn.returnable_id','left join product_mst as p on p.product_id=trn.item_id','left join tbl_ledger as ledg on ledg.l_id=grn.cust_id');
		$hOrder = "trn.id desc";
		$hGroupby = array("trn.id");
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['channal_id'];
			$row_data[] = date('d M, Y',strtotime($row["challan_date"]));
			$row_data[] = $row['l_name'];
			$row_data[] = $row['product_name'] . " -- ( ".$row['product_icode'].")";
			$row_data[] = $row['item_qty'];

			$qty = $row['item_qty'];

			 // $query1 = "select IFNULL(sum(po.product_qty),0) as done_qty,IFNULL(sum(po.product_conv_qty),0) as conv_done_qty from tbl_grn_sub_trn as po 
				// left join tbl_grn_trn as trn on trn.grn_trn_id = po.grn_trn_id 
				// left join tbl_grn as grn on grn.grn_id = trn.grn_id 
				// where grn.ref_type = 6 and po.status=0 and trn.returnable_trn_id in (".$row['id'].")";	
			 $query1 = "select IFNULL(sum(po.product_qty),0) as done_qty,IFNULL(sum(po.product_conv_qty),0) as conv_done_qty from tbl_grn_sub_trn as po 
				left join tbl_grn_trn as trn on trn.grn_trn_id = po.grn_trn_id 
				left join tbl_grn as grn on grn.grn_id = trn.grn_id 
				where grn.ref_type = 6 and po.status=0 and po.returnable_trn_id in (".$row['id'].")";	
			
			$rs_product1 = $dbcon->query($query1);
			
			$row1=brp_mysqli_fetch_array($rs_product1);


			$pending_qty = $row['item_qty'] - $row1['done_qty'];
					
			$row_data[] = $pending_qty;

			$add_btn=''; 

			if(in_array(INVENTORY_GRN_LIST_SLUG_CREATE,$bulkAccessArray)){
				
					$add_btn='<a class="btn btn-xs btn-primary" data-original-title="Create GRN" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_add_returnable/'.$row['returnable_id'].'"><i class="fa fa-plus"></i></a>'; 
				
			}
			
			
			$row_data[] = $add_btn;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
?>