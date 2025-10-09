<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
//check permission for get sales order details

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	MRP_GET_SALES_ORDER_SLUG_VIEW,MRP_GET_SALES_ORDER_SLUG_CREATE
]);
$companyConfiguration=getCompanyConfiguration($dbcon);

$production_pro_search = $companyConfiguration['production_pro_search'];
$pro_search=explode(",", $production_pro_search);
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "generate_report_min_new") {
			
		/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);*/
		
		//$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		//$where_db = check_branch('so_trn', $branch_id);

		if($companyConfiguration['sales_wise_branch_planning_before_bom']==0){
			$branch_planning=" and so_trn.bom_status=1";
		}else{
			$branch_planning="";
		}

		$where_db = " AND so.company_id = " . $_SESSION['company_id'];
		
		$appData = array();
		$i=1;
		//+IFNULL(qc_total_rejected,0)

		$aColumns = array('mst.product_icode', 'dr.drawing_number','so.sales_order_no','so.sales_order_date','led.l_name','so_trn.product_qty','so_trn.sales_ordertrn_id','mst.product_name','tc.cat_name','so.delivery_date','bran.branch_name','so_trn.product_id','so_trn.work_order_qty','so_trn.unit_id','(IFNULL(product_qty,0)-IFNULL(stock_add,0)) as pending_qty,so.jobwork_type','so_trn.description');

		$sIndexColumn = "so_trn.sales_ordertrn_id";
		$isWhere = array("so_trn.sales_ordertrn_status=0 ".$branch_planning." and so_trn.production_status=0 and  so.order_accept_status = 1 and so_trn.short_close_status=0 and so_trn.invoice_status=0 and so_trn.with_out_stock_invoice=0 and so_trn.production_branch_id=0 and so.approve_status=3".$where_db);

		$sTable = "tbl_sales_ordertrn as so_trn";

		$isJOIN = array("left join tbl_sales_order as so on so.sales_order_id=so_trn.sales_order_id","left join tbl_ledger as led on led.l_id=so.cust_id","left join product_mst as mst on mst.product_id=so_trn.product_id","left join tbl_category as tc on mst.product_category=tc.cat_id","left join (select IFNULL(sum(qc.product_qty),0) as stock_add,qc.sales_ordertrn_id from tbl_sales_order_production_trn as qc 
			where qc.sales_order_production_status=0 group by qc.sales_ordertrn_id) as qc on qc.sales_ordertrn_id=so_trn.sales_ordertrn_id","left join branch_mst as bran on bran.branch_id=so_trn.branch_id","left join tbl_drawing as dr on dr.drawing_id = mst.drawing_id");
		
		$hOrder = "so_trn.sales_ordertrn_id desc";
		//$hGroupby = "pro.product_id";
		$having=" pending_qty > 0";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		
		//print_r($sqlReturn);
		foreach($sqlReturn as $row) {

			$row_data = array();
			//tbl_sales_order_production_trn
			//$pendingqty=$row['product_qty']-$row['work_order_qty'];
			$pendingqty=$row['pending_qty'];

			$row_data[] = $row['sales_order_no'];
			$row_data[] = date('d-m-Y',strtotime($row["sales_order_date"]));
			if($companyConfiguration['customer_show_in_production'] == '1'){
				$row_data[] = $row['l_name'];
			}

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
			$row_data[] = $row['product_qty'];
			$row_data[] = date('d M, Y',strtotime($row["delivery_date"]));
			
			$view='';$stock_allocate='';
			if(in_array(MRP_GET_SALES_ORDER_SLUG_CREATE,$bulkAccessArray)) {

				if($companyConfiguration['trading_stock']==0){
					$stock_allocate='<button type="button" class="btn btn-xs btn-success" data-original-title="Allocate Stock" data-toggle="tooltip" data-placement="top" onClick="open_stock_allocation_so('.$row["sales_ordertrn_id"].')">Allocate Branch</button>';
				}	
				$sno="'".$row['sales_order_no']."'";
				$pno="'".$row['product_name']."'";
				
			}
				if(!empty($row['description'])){
					$view='<button class="btn btn-xs btn-primary" data-original-title="Product Description" data-toggle="tooltip" data-placement="top" type="button" onclick="open_so_trn_modal('.$row['sales_ordertrn_id'].')"><i class="fa fa-eye"></i></button>';
				}
			$row_data[] = $apprv_btn.' '.$stock_allocate.' '.$view;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode($output );

	}
	else if(strtolower($POST['mode']) == "add_branch") {
		
		$info['production_branch_id'] = $POST['branch_id'];			
		$updateid=update_record('tbl_sales_ordertrn', $info,"sales_ordertrn_id=".$_POST['ref_sales_order_trn_id'] ,$dbcon);

		if($updateid){
			echo 1;
		}else{
			echo 0;
		}
	}else if(strtolower($POST['mode']) == "preview_so_trn_pro_description") {
			$str = '';
			$qry = $dbcon->query("SELECT so_trn.*, pro.product_name, so.sales_order_date, so.sales_order_no FROM tbl_sales_ordertrn as so_trn LEFT JOIN product_mst as pro on pro.product_id=so_trn.product_id LEFT JOIN tbl_sales_order as so on so.sales_order_id = so_trn.sales_order_id  WHERE so_trn.sales_ordertrn_status = 0 and so_trn.sales_ordertrn_id = ".$POST['so_trn_id']);
			$res= brp_mysqli_fetch_assoc($qry);
			$str.= '<table class="display table table-bordered table-striped">
			<tbody>
			<tr>
			<td><strong>Sales Order No :</strong> '.$res['sales_order_no'].'</td>
			<td><strong>Sales Order Date :</strong> '.date("d-M-Y", strtotime($res['sales_order_date'])).'</td>
			</tr>
			<tr>
			<td><strong>Product Name :</strong> '.$res['product_name'].'</td>
			<td><strong>Request Qty :</strong> '.$res['product_qty'].'</td>
			</tr>
			<tr>
			<td colspan="2"><strong>Product Description:</strong><br>'.$res['description'].'</td>
			</tr>
			</tbody>
			</table>';

			echo $str;
		}
	
?>

