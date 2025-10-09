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

		$companyConfiguration=getCompanyConfiguration($dbcon);
		$purchase_pro_search=$companyConfiguration['purchase_pro_search'];
		$pro_search=explode(",", $purchase_pro_search);
		
		$where="";

		
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('grn', $branch_id);
		$where.=" $where_db ";

		$where_company=check_company('grn');

		$where.=" $where_company";

		//$where_user=check_user('grn');

		// $where.=" $where_user";
		
		$appData = array();
		$i=1;
		$aColumns = array('grn.grn_id','gtrn.product_conv_qty','gtrn.product_qty','unit.unit_name','grn.grn_no','grn.grn_date','pro.product_name','pro.product_icode', 'dr.drawing_number', 'pro.product_alias_name','tc.cat_name','led.l_name','bms.branch_name','grn.user_id','grn.gir_no','grn.invoice_no','(select IFNULL(sum(product_conv_qty),0) as qty  from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.grn_id=grn.grn_id and gtrn.product_id=chtrn.product_id) as used_qty','grn.branch_id','po.po_type');
		$sIndexColumn = "grn.grn_id";
		$isWhere = array("grn.grn_status=0 and gtrn.grn_trn_status=0 and grn.ref_type=2 and gtrn.purchase_status=0 and po.po_type=0".$where);
		$sTable = "tbl_grn as grn";			
		$isJOIN = array('left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id','left join product_mst as pro on pro.product_id=gtrn.product_id','left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id','left join unit_mst as unit on unit.unitid=gtrn.product_conv_unit','left join tbl_category as tc on pro.product_category=tc.cat_id','left join tbl_ledger as led on led.l_id=grn.vender_id','left join branch_mst as bms on bms.branch_id=grn.branch_id','left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id = gtrn.purchaseordertrn_id', 'left join tbl_purchaseorder as po on po.purchaseorder_id  = potrn.purchaseorder_id');
		$hOrder = "grn.grn_id desc";
		//$hGroupby = array("trn.product_id");
		//$having=" gtrn.product_qty > used_qty ";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			
			if(in_array('drawing',$pro_search)){
	            $drawing_number = " -- (".$row['drawing_number'].")";
	        }
	        if(in_array('item',$pro_search)){
	            $item_code = " -- (".$row['product_icode'].")";
	        }
	        if(in_array('alias',$pro_search)){
	            $alias = " -- (".$row['product_alias_name'].")";
	        }

			$pending=$row['product_conv_qty']-$row['used_qty'];
			$row_data[] = $row['sr'];
			$row_data[] = $row['grn_no'];
			$row_data[] = date('d-m-Y',strtotime($row['grn_date']));
			$row_data[] = $row['l_name'];
			$row_data[] = $row['product_name']." ".$drawing_number." ".$item_code." ".$alias;
			$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$row_data[] = $row['branch_name'];
			$row_data[] = number_format($row['product_conv_qty'],4,'.','')." ".$row['unit_name'];
			$row_data[] = number_format($pending,4,'.','')." ".$row['unit_name'];
			$row_data[] = find_user_name($dbcon,$row['user_id']);
			
			$add_po_btn = '';
			if(in_array(PURCHASE_BILL_PENDING_ADD,$bulkAccessArray)){
				$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Add Purchase Bill" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'purchase_bill_pending/'.$row['grn_id'].'/'.$row['branch_id'].'"><i class="fa fa-plus"></i></a>';
			}
			$row_data[] = $add_po_btn;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	
?>