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
			// var_dump($POST);die;

			$product_category = $POST['product_category'];
		
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
		$aColumns = array('pro.product_icode','dr.drawing_number','pro.product_id','pro.product_base_unit','pro.product_name','tc.cat_name','pro.product_status','pro.product_min_stock', 'IFNULL(reqqty,0) as reqqty', 'IFNULL(base_stock_add,0) as base_stock_add','IFNULL(base_stock_minus,0) as base_stock_minus','IFNULL(con_stock_add,0) as con_stock_add','IFNULL(con_stock_minus,0) as con_stock_minus','(((IFNULL(base_stock_add,0)+IFNULL(con_stock_add,0))-(IFNULL(base_stock_minus,0)+IFNULL(con_stock_minus,0)))+IFNULL(reqqty,0)) as stock','pro.product_category','min_max.base_request_qty','pro.product_base_unit','pro.product_base_unit','pro.product_conv_unit','pro.reorder_qty');
		$sIndexColumn = "pro.product_id";
		$isWhere = array("pro.product_status=0  and pro.company_id=".$_SESSION['company_id']." ".$where_db_branch." and pro.product_category =" .$product_category);
		$sTable = "product_mst as pro";
		$isJOIN = array("left join tbl_category as tc on pro.product_category=tc.cat_id",
			"left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id",
			"left join tbl_store_order_min_max as min_max on pro.product_id = min_max.product_id and min_max.wo_complete_status = 0 and min_max.status = 0",
		"left join (select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0  group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id",
		
		"left join (select sum(qc.base_stock) as base_stock_add,qc.product_id,qc.base_unit from tbl_stock_trn as qc 
		where qc.stock_status!=2 and stock_flage=1 and qc.company_id=".$_SESSION['company_id']." 
		group by qc.product_id) as qc4 on qc4.product_id=pro.product_id and qc4.base_unit=pro.product_base_unit",
	
		"left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id,qc.base_unit from tbl_stock_trn as qc 
		where qc.stock_status!=2 and stock_flage=2 and qc.company_id=".$_SESSION['company_id']." 
		group by qc.product_id) as qc1 on qc1.product_id=pro.product_id and qc1.base_unit=pro.product_base_unit",
		
		"left join (select sum(qc.convert_stock) as con_stock_add,qc.product_id,qc.convert_unit from tbl_stock_trn as qc 
		where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.company_id=".$_SESSION['company_id']." 
		group by qc.product_id) as qc2 on qc2.product_id=pro.product_id and qc2.convert_unit=pro.product_base_unit",
	
		"left join (select sum(qc.convert_stock) as con_stock_minus,qc.product_id,qc.convert_unit from tbl_stock_trn as qc 
		where qc.stock_status!=2 and stock_flage=2 and qc.base_unit!=qc.convert_unit and qc.company_id=".$_SESSION['company_id']." 
		group by qc.product_id) as qc3 on qc3.product_id=pro.product_id and qc3.convert_unit=pro.product_base_unit",
	
		 );
				
		$hOrder = "pro.product_name desc";
		$hGroupby = array("pro.product_id");
		// $having=" stock < pro.product_min_stock ";
		include($include.'pagging.php');
		$appData = array();
		$id=1;

		$production_pro_search = $company_config['production_pro_search'];
			$pro_search=explode(",", $production_pro_search);

		foreach($sqlReturn as $row) {

			$row_data = array();
			$row_data[] = '<input type="checkbox" chk name="chk[]" data-product_category="'.$row['product_category'].'" data-product_base_unit="'.$row['product_base_unit'].'" data-product_conv_unit="'.$row['product_conv_unit'].'" data-product_min_stock="'.$row['product_min_stock'].'" value="'.$row['product_id'].'"/>';
			
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
			$row_data[] = ($row['reorder_qty']!=null) ? $row['reorder_qty'] : '0';
			$row_data[] = $row['product_min_stock'];
			/*if($row['base_request_qty'] != null && $row['base_request_qty'] > 0){
				$row_data[] = $row['base_request_qty'];
			}else{*/
				$row_data[] = '<input type="text" name="req_qty'.$row['product_id'].'" id="req_qty'.$row['product_id'].'" class="form-control req_qty" data-product_id="'.$row['product_id'].'" onKeyUp="check_css_vaildation('.$row['product_id'].')">
				<input type="hidden" name="reorder_qty'.$row['product_id'].'" id="reorder_qty'.$row['product_id'].'" class="form-control" value="'.$row['reorder_qty'].'">';
			/*}*/
			
			$row_data[] = $stock;
			
   			$view='';
			if(in_array(STOCK_DETAIL_MINMAX_SLUG_CREATE,$bulkAccessArray)){
				// if($row['base_request_qty'] == null || empty($row['base_request_qty']) || $row['base_request_qty'] == '0'){
					// $view='<button class="btn btn-xs btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onClick="save_store_order_request('.$row['product_id'].','.$row['product_category'].','.$row['product_base_unit'].','.$row['product_conv_unit'].','.$row['product_min_stock'].')"><i class="fa fa-paper-plane"></i> Request</button>';
				// }
			}
			
			// $row_data[] = $view;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode($output );
				
		}
		else if(strtolower($POST['mode']) == "add") 
		{

			$product_id  = $POST['product_id'];
			$product_category = $POST['product_category'];
			$base_unit = $POST['base_unit'];
			$conv_unit = $POST['conv_unit'];
			$req_qty = $POST['req_qty'];
			$base_qty = $POST['base_qty'];
	
			$doc_no = $POST['doc_no'];

			for($i=0;$i<count($product_id);$i++)
			{
				$conv_qty = convert_stock_new($dbcon,$base_qty[$i],$product_id[$i],"conv_unit");
				$req_conv_qty = convert_stock_new($dbcon,$req_qty[$i],$product_id[$i],"conv_unit");

				$info['product_id'] =  $product_id[$i];
				$info['doc_no'] =  $doc_no;
				$info['product_category'] =  $product_category[$i];
				$info['base_qty'] =  $base_qty[$i];
				$info['conv_qty'] =  $conv_qty;
				$info['base_unit'] =  $base_unit[$i];
				$info['conv_unit'] =  $conv_unit[$i];
				$info['base_request_qty'] =  $req_qty[$i];
				$info['conv_request_qty'] =  $req_conv_qty;
				$info['status'] =  0;
				$info['wo_complete_status'] =  0;
				$info['cdate']				= date("Y-m-d H:i:s");
				$info['user_id']			= $_SESSION['user_id'];
				$info['company_id']			= $_SESSION['company_id'];

				$insertid=add_record('tbl_store_order_min_max', $info, $dbcon);
			}

			if($insertid){
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = " . $POST['invoicetype_id']);
				//update_common_no($dbcon,DOCUMENT_NO);
				echo "1";
			}else{
				echo "0";
			}
		}
		else if(strtolower($POST['mode'])== "load_docno")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".trim($_POST['typeid']);
			$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2')
			{
				$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1')
			{
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
			}
			$row['challanno']=str_pad($id,3,"0",STR_PAD_LEFT);
			echo json_encode($row);
		}
		
	}
    
}
/*
else {
    die("Error - 1");
}*/

?>