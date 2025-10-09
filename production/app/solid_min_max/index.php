<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');


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
				STOCK_DETAIL_MINMAX_SLUG_VIEW,STOCK_DETAIL_MINMAX_SLUG_CREATE
		]);		
		/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);*/
		
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db_branch = check_branch('pro', $branch_id);
		
		
		$appData = array();
		$i=1;
		//+IFNULL(qc_total_rejected,0)
		$aColumns = array('pro.product_icode','dr.drawing_number','pro.product_id','pro.product_base_unit','pro.product_name','tc.cat_name','pro.product_status','pro.product_min_stock', 'reqqty', 'base_stock_add','base_stock_minus','con_stock_add','con_stock_minus','(((IFNULL(base_stock_add,0)+IFNULL(con_stock_add,0))-(IFNULL(base_stock_minus,0)+IFNULL(con_stock_minus,0)))+IFNULL(reqqty,0)) as stock','IFNULL(so_qty,0) as so_tqty','(IFNULL(res_plase,0)-IFNULL(res_min,0)) as rstock','((((IFNULL(base_stock_add,0)+IFNULL(con_stock_add,0)+IFNULL(min_planing,0))-(IFNULL(base_stock_minus,0)+IFNULL(con_stock_minus,0)))+IFNULL(reqqty,0))-(IFNULL(res_plase,0)-IFNULL(res_min,0))) as cstock','IFNULL(min_planing,0) as minplani','ppp');
		$sIndexColumn = "pro.product_id";
		$isWhere = array("pro.product_status=0 and pro.product_min_stock!=0 and pro.company_id=".$_SESSION['company_id']." ".$where_db_branch);
		$sTable = "product_mst as pro";
		$isJOIN = array("left join tbl_category as tc on pro.product_category=tc.cat_id",
			"left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id",
		"left join (select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0  group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id",
		
		"left join (select sum(qc.base_stock) as base_stock_add,qc.product_id,qc.base_unit from tbl_stock_trn as qc 
		where qc.stock_status=0 and stock_flage=1 and qc.company_id=".$_SESSION['company_id']." 
		group by qc.product_id) as qc4 on qc4.product_id=pro.product_id and qc4.base_unit=pro.product_base_unit",
	
		"left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id,qc.base_unit from tbl_stock_trn as qc 
		where qc.stock_status=0 and stock_flage=2 and qc.company_id=".$_SESSION['company_id']." 
		group by qc.product_id) as qc1 on qc1.product_id=pro.product_id and qc1.base_unit=pro.product_base_unit",
		
		"left join (select sum(qc.convert_stock) as con_stock_add,qc.product_id,qc.convert_unit from tbl_stock_trn as qc 
		where qc.stock_status=0 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.company_id=".$_SESSION['company_id']." 
		group by qc.product_id) as qc2 on qc2.product_id=pro.product_id and qc2.convert_unit=pro.product_base_unit",
	
		"left join (select sum(qc.convert_stock) as con_stock_minus,qc.product_id,qc.convert_unit from tbl_stock_trn as qc 
		where qc.stock_status=0 and stock_flage=2 and qc.base_unit!=qc.convert_unit and qc.company_id=".$_SESSION['company_id']." 
		group by qc.product_id) as qc3 on qc3.product_id=pro.product_id and qc3.convert_unit=pro.product_base_unit",
		
		"left join (select (IFNULL(sum(so_trn.product_qty),0)-IFNULL(p_ad,0)) as so_qty,p_ad as ppp,so_trn.product_id from tbl_sales_ordertrn as so_trn 
			left join tbl_sales_order as so on so.sales_order_id=so_trn.sales_order_id
			left join (select IFNULL(sum(qcs.product_qty),0) as p_ad,qcs.product_id from tbl_sales_order_production_trn as qcs where qcs.sales_order_production_status=0 group by qcs.product_id) as qcs on qcs.product_id=so_trn.product_id
		where so_trn.short_close_status=0 and sales_ordertrn_status!=2 and so.approve_status=3 and so.order_accept_status=1 and so_trn.invoice_status=0 and so.company_id=".$_SESSION['company_id']." 
		group by so_trn.product_id) as soq on soq.product_id=pro.product_id",

		"left join (select sum(so_trn1.base_stock) as res_plase,so_trn1.product_id from tbl_reserve_stock as so_trn1 
			where so_trn1.stock_status!=2 and stock_flage=1 and so_trn1.company_id=".$_SESSION['company_id']." 
			group by so_trn1.product_id) as sor on sor.product_id=pro.product_id",

		"left join (select sum(so_trn1.base_stock) as res_min,so_trn1.product_id from tbl_reserve_stock as so_trn1 
			where so_trn1.stock_status!=2 and stock_flage=2 and so_trn1.company_id=".$_SESSION['company_id']." 
			group by so_trn1.product_id) as sor1 on sor1.product_id=pro.product_id",
		
		"left join (select sum(so_trn1.product_qty-so_trn1.allocate_qty) as min_planing,so_trn1.product_id from tbl_min_max_production_trn as so_trn1 
			where so_trn1.min_max_production_status!=2 and so_trn1.product_qty>allocate_qty and so_trn1.company_id=".$_SESSION['company_id']." 
			group by so_trn1.product_id) as sor3 on sor3.product_id=pro.product_id",
		 );
				
		$hOrder = "pro.product_name desc";
		//$hGroupby = "pro.product_id";
		$having=" cstock < pro.product_min_stock ";
		include($include.'pagging.php');
		$appData = array();
		$id=1;

		$production_pro_search = $company_config['production_pro_search'];
			$pro_search=explode(",", $production_pro_search);

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
			$drawing_number = "";
					$item_code = "";
					 if(in_array('drawing',$pro_search)){
				            $drawing_number = " -- (".$row['drawing_number'].")";
				        }
				        if(in_array('item',$pro_search)){
				            $item_code = " -- (".$row['product_icode'].")";
				        }
			
			$stock=$row['stock']-$row['reqqty'];
			$row_data[] = $row['product_name'].' '.$item_code.' '.$drawing_number;
			$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$row_data[] = $row['product_min_stock'];
			$row_data[] = $row['minplani'];
			//$row_data[] = $row['reqqty'];
			$row_data[] = $stock;
			$row_data[] = $row['rstock'];
			$row_data[] = $row['so_tqty'];
			//$row_data[] = $row['ppp'];
			$tqty=(($row['product_min_stock']+$row['rstock']+$row['so_tqty'])-($stock+$row['minplani']));
			
   			$view='';
			if(in_array(STOCK_DETAIL_MINMAX_SLUG_CREATE,$bulkAccessArray)){
				$view='<a class="btn btn-xs btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" href="'.ROOT.'production/request_product/'.$row['product_id'].'"><i class="fa fa-paper-plane"></i> Request</a>';
			}
			
			$view_desc='<button class="btn btn-xs btn-primary" data-original-title="Sales Order Detail" data-toggle="tooltip" data-placement="top" type="button" onclick="open_so_trn_modal('.$row['product_id'].','.$tqty.')"><i class="fa fa-eye"></i></button>';
			//$row_data[] = $view;
			$row_data[] = $view_desc;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode($output );
				
		}else if(strtolower($POST['mode']) == "preview_solid_planning1") {
			$query_img 	= "select mst.product_name from product_mst as mst 
					where mst.product_id=".$POST['product_id'];
			$result_img = $dbcon->query($query_img);
			$row = brp_mysqli_fetch_array($result_img);
			$arr['product_name']=$row['product_name'];
			echo json_encode($arr);
		}
	}
    
}
/*
else {
    die("Error - 1");
}*/

?>