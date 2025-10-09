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
		$where_company=check_company('grn');
		$where.=" $where_company";

		$vender = "";
		$vender_id = (isset($POST['vender_id']) && $POST['vender_id']) ? $POST['vender_id'] : $_SESSION['vender_id'];
		if(!empty($vender_id)){
			$vender=" and grn.vender_id=".$POST['vender_id'];
		}

		$po = "";
		$po_id = (isset($POST['po_id']) && $POST['po_id']) ? $POST['po_id'] : "";
		if(!empty($po_id)){
			$po=" and grn.purchaseorder_id=".$POST['po_id'];
		}

		$appData = array();
		$i=1;

		$aColumns = array('potrn.purchaseordertrn_id', 'GROUP_CONCAT(grn.grn_no) as grn_no','led.l_name','unit.unit_name','GROUP_CONCAT(pro.product_name) as product_name','GROUP_CONCAT(gtrn.product_conv_qty) as product_conv_qty', 'po.purchaseorder_no','GROUP_CONCAT(po.purchaseorder_date) as purchaseorder_date', 'potrn.purchaseordertrn_id', 'GROUP_CONCAT(potrn.product_qty) as total_po_product_qty','grn.grn_date','grn.user_id');
        $sIndexColumn = "grn.grn_id";
        $sTable = "tbl_purchaseordertrn as potrn";            
        $isJOIN = array('left join tbl_purchaseorder as po on potrn.purchaseorder_id = po.purchaseorder_id','left join tbl_grn_trn as gtrn on gtrn.purchaseordertrn_id=potrn.purchaseordertrn_id','left join tbl_grn as grn on grn.grn_id = gtrn.grn_id','left join tbl_ledger as led on led.l_id=grn.vender_id','left join unit_mst as unit on unit.unitid=gtrn.product_conv_unit', 'left join product_mst as pro on pro.product_id=gtrn.product_id');

        $isWhere = array("grn.grn_status=0".$vender.$po.$where);
        $hOrder = "potrn.purchaseordertrn_id desc";
        $hGroupby = array("potrn.purchaseordertrn_id");

		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			
			$row_data[] = $row['sr'];
			$row_data[] = $row['grn_no'];
			$row_data[] = date('d-m-Y',strtotime($row['grn_date']));
			$row_data[] = $row['purchaseorder_no'];
			$row_data[] = date('d-m-Y',strtotime($row['purchaseorder_date']));

			$row_data[] = $row['product_name'];
			$row_data[] = number_format($row['product_conv_qty'],4,'.','')." ".$row['unit_name'];
			$row_data[] = number_format($row['total_po_product_qty'],4,'.','')." ".$row['unit_name'];
			$row_data[] = find_user_name($dbcon,$row['user_id']);
						
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}

	if(strtolower($POST['mode']) == "po_fetch") {
		$po = get_po_for_purchase($dbcon,$POST['vender_id']);
		echo json_encode($po);
	}
	
?>