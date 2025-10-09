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
		$aColumns = array('pro.product_icode','dr.drawing_number','pro.product_id','pro.product_base_unit','pro.product_conv_unit','pro.product_name','pro.product_alias_name','tc.cat_name','pro.product_status','pro.product_min_stock', 'IFNULL(reqqty,0) as reqqty', 'base_stock_add','base_stock_minus','con_stock_add','con_stock_minus','(((IFNULL(base_stock_add,0)+IFNULL(con_stock_add,0))-(IFNULL(base_stock_minus,0)+IFNULL(con_stock_minus,0)))+IFNULL(reqqty,0)) as stock');
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
	
		 );
				
		$hOrder = "pro.product_name desc";
		//$hGroupby = "pro.product_id";
		$having=" stock < pro.product_min_stock ";
		include($include.'pagging.php');
		$appData = array();
		$id=1;

		$production_pro_search = $company_config['production_pro_search'];
			$pro_search=explode(",", $production_pro_search);

		foreach($sqlReturn as $row) {

			$row_data = array();
			$product_id = $row['product_id'];
			
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

				        if(!empty($row['product_alias_name'])){
                            $item_alias = " -- (".$row['product_alias_name'].")";
                        }
			
			$stock=$row['stock']-$row['reqqty'];
			$stock_conv = 0;
			$product_min_stock_conv = 0;
			$reqqty_conv = 0;

			if($row['product_base_unit'] == $row['product_conv_unit']){
				$stock_conv = $stock;
				$product_min_stock_conv =  $row['product_min_stock'];;
				$reqqty_conv = $row['reqqty'];
			}else{
				$stock_conv = convert_stock_new($dbcon,$stock,$product_id,"conv_unit");
				$product_min_stock_conv = convert_stock_new($dbcon,$row['product_min_stock'],$product_id,"conv_unit");
				$reqqty_conv = convert_stock_new($dbcon,$row['reqqty'],$product_id,"conv_unit");
			}

			$row_data[] = $row['product_name'].' '.$item_code.' '.$drawing_number.' '.$item_alias ;;
			$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$row_data[] = $row['product_min_stock'];
			$row_data[] = $product_min_stock_conv;
			$row_data[] = $row['reqqty'];
			$row_data[] = $reqqty_conv;
			$row_data[] = $stock;
			$row_data[] = $stock_conv;
			
   			$view='';
			if(in_array(STOCK_DETAIL_MINMAX_SLUG_CREATE,$bulkAccessArray)){
				$view='<a class="btn btn-xs btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" href="'.ROOT.'production/request_product/'.$row['product_id'].'"><i class="fa fa-paper-plane"></i> Request</a>';
			}
			
			$row_data[] = $view;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode($output );
				
		}
		
	}
    
}
/*
else {
    die("Error - 1");
}*/

?>