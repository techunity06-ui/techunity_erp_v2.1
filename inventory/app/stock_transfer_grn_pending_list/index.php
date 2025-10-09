<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');
$company_config = getCompanyConfiguration($dbcon);		
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		$production_pro_search = $company_config['production_pro_search'];
			$pro_search=explode(",", $production_pro_search);

		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
			OVERDUE_PO_PRO_ADD
		]);
		$where="";

		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		// $where_db = check_branch('trn', $branch_id);
		// $where.=" $where_db ";
		$where_company=check_company('trn');
		$where.=" $where_company";

		$appData = array();
		$i=1;
		$aColumns = array('trn.stock_transfer_trn_id','st.stock_transfer_id','st.stock_transfer_doc_no','st.stock_transfer_doc_date','pro.product_name','pro.product_icode','tc.cat_name','trn.stock_qty','trn.base_qty','trn.conv_qty','trn.grn_base_qty','trn.grn_conv_qty','trn.stock_unit','trn.base_unit','trn.conv_unit','trn.user_id');
		$sIndexColumn = "trn.stock_transfer_trn_id";
		$isWhere = array("trn.grn_status =0 and trn.status  = 0 and st.approve_status = 1".$where);
		$sTable = "tbl_stock_transfer_trn as trn";			
		$isJOIN = array(
			'left join product_mst as pro on pro.product_id=trn.product_id',
			'left join tbl_category as tc on pro.product_category=tc.cat_id',
			'left join tbl_stock_transfer as st on st.stock_transfer_id=trn.stock_transfer_id'		
		);
		$hOrder = "trn.stock_transfer_trn_id desc";
		$hGroupby = $group;
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {

			$drawing_number = "";
			$item_code = "";
			 if(in_array('drawing',$pro_search)){
		            $drawing_number = " -- (".$row['drawing_number'].")";
		        }
		        if(in_array('item',$pro_search)){
		            $item_code = " -- (".$row['product_icode'].")";
		        }
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['stock_transfer_doc_no'];
			$row_data[] = date('d-m-Y',strtotime($row['stock_transfer_doc_date']));
			$row_data[] = $row['product_name'].' '.$item_code.' '.$drawing_number;
			$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$row_data[] = number_format($row['stock_qty'],4,'.','');

			$grn_base_qty = ($row['grn_base_qty']) ? $row['grn_base_qty'] : 0;
			$grn_conv_qty = ($row['grn_conv_qty']) ? $row['grn_conv_qty'] : 0;
			if($row['stock_unit'] == $row['conv_unit']){
				$row_data[] = number_format($grn_conv_qty,4,'.','');
				$due_qty = $row['stock_qty']-$grn_conv_qty;
				$row_data[] = number_format($due_qty,4,'.','');
			}else{
				$row_data[] = number_format($grn_base_qty,4,'.','');
				
				$due_qty = $row['stock_qty']-$grn_base_qty;
				$row_data[] = number_format($due_qty,4,'.','');
			}
			
			$row_data[] = find_user_name($dbcon,$row['user_id']);
			
			$add_po_btn = '';
			if(in_array(OVERDUE_PO_PRO_ADD,$bulkAccessArray)){
				$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Add GRN" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_stock_transfer/'.$row['stock_transfer_id'].'"><i class="fa fa-plus"></i></a>';
			}
			$row_data[] = $add_po_btn;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	
?>