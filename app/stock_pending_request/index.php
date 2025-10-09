<?php
session_start(); //start session
$AJAX = true;
// include('../../include/urlfileinner.php');
include("../../config/config.php");
// error_reporting(E_ALL);
include("../../config/session.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
// include("../../include/common_functions.php");
include("../../include/function_database_query.php");

$company_config = getCompanyConfiguration($dbcon);		

/*if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')*/ 
{ 
    /*if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) */
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['mode']) == "generate_report_min") {
		
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
				MRP_STOCK_PENDING_REQUEST_SLUG_VIEW,MRP_STOCK_PENDING_REQUEST_SLUG_CREATE
		]);	
		/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);*/

		$appData = array();
		$i=1;
		//+IFNULL(qc_total_rejected,0)
		$aColumns = array('pro.product_icode','dr.drawing_number','pro.product_id','pro.product_base_unit','pro.product_name','tc.cat_name','pro.product_status','pro.product_min_stock', 'pro.product_opening', 'reqqty','(IFNULL(((IFNULL((select IFNULL(sum(qc.base_stock),0) as base_stock_add from tbl_stock_trn as qc where qc.stock_status !=2 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id='.$_SESSION['company_id'].' 
				group by qc.product_id),0)+IFNULL((select IFNULL(sum(qc.convert_stock),0) as con_stock_add from tbl_stock_trn as qc 
				where qc.stock_status !=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id='.$_SESSION['company_id'].' 
				group by qc.product_id),0))-(IFNULL((select IFNULL(sum(qc.base_stock),0) as base_stock_minus from tbl_stock_trn as qc 
				where qc.stock_status !=2 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id='.$_SESSION['company_id'].' 
				group by qc.product_id),0)+IFNULL((select IFNULL(sum(qc.convert_stock),0) as con_stock_minus from tbl_stock_trn as qc 
				where qc.stock_status !=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id='.$_SESSION['company_id'].' 
				group by qc.product_id),0))),0)+IFNULL(reqqty,0))-(((IFNULL((select sum(base_stock) as base_addqty from tbl_reserve_stock where stock_status !=2 and stock_flage=1 and base_unit=pro.product_base_unit and product_id=pro.product_id group by product_id),0)+IFNULL((select sum(convert_stock) as conv_addqty from tbl_reserve_stock where stock_status !=2 and base_unit!=convert_unit and stock_flage=1 and convert_unit=pro.product_base_unit and product_id=pro.product_id group by product_id),0))-(IFNULL((select sum(base_stock) as base_usedqty from tbl_reserve_stock where stock_status !=2 and stock_flage=2 and base_unit=pro.product_base_unit and product_id=pro.product_id group by product_id),0)+IFNULL((select sum(convert_stock) as conv_usedqty from tbl_reserve_stock where stock_status !=2 and base_unit!=convert_unit and stock_flage=2 and convert_unit=pro.product_base_unit and product_id=pro.product_id group by product_id),0)))+(IFNULL((select sum(s_qty) as base_addqty1 from tbl_complain_spare_part as com
				left join tbl_complaint as c on c.complaint_id=com.s_comp_id
				where c.complaint_status=0 and sp_sent_status="yes" and s_inv_status=0 and s_product=pro.product_id group by s_product),0))) as stock_in_new');
		$sIndexColumn = "pro.product_id";
		$isWhere = array("pro.product_status=0");
		$sTable = "product_mst as pro";
		$isJOIN = array(
			"left join tbl_category as tc on pro.product_category=tc.cat_id left join (select sum(req.rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0  group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id","left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id" );
				
		$hOrder = "pro.product_name desc";
		//$hGroupby = "pro.product_id";
		$having=" stock_in_new < 0 ";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {

			$row_data = array();
			
			//$stock=($row['product_opening']+$row['grn_total']+$row['qc_total']+$row['add_adjustment_qty'])-($row['inv_qty']+$row['jobout_qty']+$row['qc_total_rejected']+$row['remove_adjustment_qty']);
			
			//$stock=($row['product_opening']+$row['grn_total']+$row['qc_total']+$row['add_adjustment_qty'])-($row['inv_qty']+$row['jobout_qty']+$row['remove_adjustment_qty']);
			
			//$stock=$row['stock_in_new']-$row['reqqty'];
			
			
			//$op_stock=$row['product_opening'];
			//$total=get_current_stock($dbcon,$row['product_id']);
			//$total=$row['reqqty']+$stock;
			//$cl_stock=$total;
			
			//$row_data[] = $row['product_name']."----(".$row['grn_total']."+".$row['qc_total']."+".$row['product_opening'].")-(".$row['inv_qty'].")+".$row['jobout_qty'].")+".$row['qc_total_rejected'].")+".$row['reqqty'].")";

			$production_pro_search = $company_config['production_pro_search'];
			$pro_search=explode(",", $production_pro_search);


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
			$row_data[] = $row['product_min_stock'];
			$row_data[] = abs($row['stock_in_new']);
			//$row_data[] = $stock;
			
   
			$view='';
			if(in_array(MRP_STOCK_PENDING_REQUEST_SLUG_CREATE,$bulkAccessArray)){
				$view='<a class="btn btn-xs btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" href="'.ROOT.'stock_pending_product/'.$row['product_id'].'"><i class="fa fa-paper-plane"></i> Request</a>';
			}
			
			$row_data[] = $view;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
				
		}
		
	}
    
}
/*
else {
    die("Error - 1");
}*/

?>