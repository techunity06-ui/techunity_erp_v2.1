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

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];

		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('po', $branch_id);
		$where.=" $where_db ";
		
		$where_company=check_company('po');

		$where.=" $where_company";

		if($company_config['po_work_order_wise'] == 1){
			$group=array("trn.po_ref_id","trn.po_ref_type","trn.purchaseordertrn_id");
			/*$left=" left join tbl_request_product as res on res.rp_id=trn.po_ref_id left join tbl_set_main_process as setm on setm.sp_id=res.sp_id";
			$pera=",setm.po_req_no,setm.po_req_date";*/
		}

		if(!empty($POST['product_id']) && $POST['product_id'] > 0){
			$where.=" and trn.product_id = " . $POST['product_id'];
		}

		if(!empty($POST['vender_id']) && $POST['vender_id'] > 0){
			$where.=" and po.vender_id = " . $POST['vender_id'];
		}

		$where.="  and DATE(po.purchaseorder_date)>='".date('Y-m-d',strtotime($s_date[0]))."' AND DATE(po.purchaseorder_date)<='".date('Y-m-d',strtotime($s_date[1]))."'";
		//$where_user=check_userid('po');

		//$where.=" $where_user";

		/*trn.product_qty>(select IFNULL(sum(product_qty+tolerance),0) as qty  from tbl_grn_trn as chtrn 
		left join tbl_grn as gn on gn.grn_id=chtrn.grn_id
		where chtrn.grn_trn_status=0 and gn.ref_type=2 and chtrn.purchaseorder_id=trn.purchaseorder_id and trn.product_id=chtrn.product_id)*/
		
		$appData = array();
		$i=1;
		$aColumns = array('setm.po_req_no','pro.product_icode', 'led.l_name'.$pera ,'po.purchaseorder_no','po.purchaseorder_date','tc.cat_name','pro.product_name','bms.branch_name','trn.product_qty','trn.product_conv_qty','trn.product_id','trn.unit_id','trn.purchaseorder_id','trn.user_id','trn.rate_unit', 'trn.unit_id','trn.purchaseordertrn_id','setm.po_req_date' , 'trn.conv_unit_id','dr.drawing_number','(select IFNULL(sum(product_qty),0) as qty  from tbl_grn_sub_trn as chtrn 
		where chtrn.status=0 and chtrn.purchaseordertrn_id=trn.purchaseordertrn_id and trn.product_id=chtrn.product_id) as done_qty','(select IFNULL(sum(product_conv_qty),0) as qty  from tbl_grn_sub_trn as chtrn
		where chtrn.status=0 and chtrn.purchaseordertrn_id=trn.purchaseordertrn_id and trn.product_id=chtrn.product_id) as done_conv_qty');
		$sIndexColumn = "trn.purchaseordertrn_id";
		$isWhere = array("trn.used_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po_approval_status=1 and po.po_type = 0".$where);
		$sTable = "tbl_purchaseordertrn as trn";			
		$isJOIN = array(
			'left join product_mst as pro on pro.product_id=trn.product_id',
			'left join tbl_category as tc on pro.product_category=tc.cat_id',
			'left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id',
			'left join tbl_ledger as led on led.l_id=po.vender_id',
			'left join tbl_request_product as res on res.rp_id=trn.po_ref_id left join',
			'tbl_set_main_process as setm on setm.sp_id=res.sp_id',
			'left join branch_mst as bms on bms.branch_id=trn.branch_id',
			'left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id '.$left
		);
		$hOrder = "trn.purchaseordertrn_id desc";
		$hGroupby = $group;
		$having = "(product_qty-done_qty) > 0";
		include($include.'pagging.php');

		//echo $Query;
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {

			$drawing_number = "";
			$item_code = "";

			$unitname = getunitname($dbcon,$row['unit_id']);
			$conv_unitname = getunitname($dbcon,$row['conv_unit_id']);
			 if(in_array('drawing',$pro_search)){
		            $drawing_number = " -- (".$row['drawing_number'].")";
		        }
		        if(in_array('item',$pro_search)){
		            $item_code = " -- (".$row['product_icode'].")";
		        }
			$row_data = array();
			$row_data[] = $row['sr'];
			if($company_config['po_work_order_wise'] == 1){
				$row_data[] = $row['po_req_no'];
			}
			$row_data[] = $row['l_name'];
			$row_data[] = $row['purchaseorder_no'];
			$row_data[] = date('d-m-Y',strtotime($row['purchaseorder_date']));


			$row_data[] = $row['product_name'].' '.$item_code.' '.$drawing_number;
			$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$row_data[] = $row['branch_name'];

			/*if($row['rate_unit'] == $row['conv_unit_id']){
				$row_data[] = number_format($row['product_conv_qty'],4,'.','');
				$row_data[] = number_format($row['done_conv_qty'],4,'.','');
				
				$due_qty = $row['product_conv_qty']-$row['done_conv_qty'];
				$row_data[] = number_format($due_qty,4,'.','');
			}else{
				$row_data[] = number_format($row['product_qty'],4,'.','');
				$row_data[] = number_format($row['done_qty'],4,'.','');
				
				$due_qty = $row['product_qty']-$row['done_qty'];
				$row_data[] = number_format($due_qty,4,'.','');
			}*/
			// var_dump($row['unit_id'] . '--'.$row['conv_unit_id']);
			if($row['unit_id'] == $row['conv_unit_id']){
				$row_data[] = number_format($row['product_qty'],4,'.','') . ' ' . $unitname;
				$row_data[] = number_format($row['done_qty'],4,'.','') . ' ' . $unitname;
				$due_qty = $row['product_qty']-$row['done_qty'];
				$row_data[] = number_format($due_qty,4,'.','') . ' ' . $unitname;
			}else{
				$row_data[] = number_format($row['product_qty'],4,'.','') . ' ' . $unitname . '</br>' .number_format($row['product_conv_qty'],4,'.','') . '  '.$conv_unitname;
				$row_data[] = number_format($row['done_qty'],4,'.','') . ' ' . $unitname . '</br>' .number_format($row['done_conv_qty'],4,'.','') . '  '.$conv_unitname;
				$due_qty = $row['product_qty']-$row['done_qty'];
				$due_conv_qty = $row['product_conv_qty']-$row['done_conv_qty'];
				$row_data[] = number_format($due_qty,4,'.','') . ' ' . $unitname . '</br>' .number_format($due_conv_qty,4,'.','') . '  '.$conv_unitname;
			}
			
			$row_data[] = find_user_name($dbcon,$row['user_id']);
			
			$add_po_btn = '';
			if(in_array(OVERDUE_PO_PRO_ADD,$bulkAccessArray)){
				$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Add GRN" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_add_po/'.$row['purchaseorder_id'].'"><i class="fa fa-plus"></i></a>';
			}
			$row_data[] = $add_po_btn;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	
?>