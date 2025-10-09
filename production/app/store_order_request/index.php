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
		
		/*$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db_branch = check_branch('mst', $branch_id);*/
		
		
		$appData = array();
		$i=1;
		//+IFNULL(qc_total_rejected,0)
		$aColumns = array('mst.doc_no','mst.order_id','pro.product_id','pro.product_icode','mst.base_unit','reqqty','pro.product_name','tc.cat_name','mst.base_qty','mst.wo_base_qty', 'mst.base_request_qty');
		$sIndexColumn = "mst.order_id";
		$isWhere = array("mst.bom_status = 1 and mst.status=0 and mst.wo_complete_status=0 and mst.company_id=".$_SESSION['company_id']." ".$where_db_branch);
		$sTable = "tbl_store_order_min_max as mst";

		$isJOIN = array("left join product_mst as pro on pro.product_id=mst.product_id","left join tbl_category as tc on pro.product_category=tc.cat_id",
			"left join (select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0  group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id"
		 );
		/*$isJOIN = array("left join product_mst as pro on pro.product_id=mst.product_id","left join tbl_category as tc on pro.product_category=tc.cat_id",
			
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
	
		 );*/
				
		$hOrder = "pro.product_name desc";
		//$hGroupby = "pro.product_id";
		// $having=" stock < mst.base_qty ";
		include($include.'pagging.php');
		$appData = array();
		$id=1;

		$production_pro_search = $company_config['production_pro_search'];
			$pro_search=explode(",", $production_pro_search);

		foreach($sqlReturn as $row) {

			$row_data = array();
			$row_data[] = $row['doc_no'];
			$drawing_number = "";
					$item_code = "";
					 if(in_array('drawing',$pro_search)){
				            $drawing_number = " -- (".$row['drawing_number'].")";
				        }
				        if(in_array('item',$pro_search)){
				            $item_code = " -- (".$row['product_icode'].")";
				        }
			
			$stock=$row['stock']-$row['reqqty'];
			$pendingqty = $row['base_request_qty'] - $row['wo_base_qty'];

			$row_data[] = $row['product_name'].' '.$item_code.' '.$drawing_number;
			$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$row_data[] = empty($row['base_qty']) ? '0' : $row['base_qty'];
			$row_data[] = $row['base_request_qty'];
			$row_data[] = empty($row['wo_base_qty']) ? 0 : $row['wo_base_qty'] ;
			$row_data[] = $pendingqty;
			$row_data[] = $stock;


			
   			$view='';
			if(in_array(STOCK_DETAIL_MINMAX_SLUG_CREATE,$bulkAccessArray)){
				$view='<a class="btn btn-xs btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" href="'.ROOT.'production/store_order_workorder_request/'.$row['product_id'].'/'.$row['order_id'].'"><i class="fa fa-paper-plane"></i> Create Workorder</a>';
			}

			$indent='<button type="button" class="btn btn-xs btn-primary" data-original-title="Create Workorder" data-toggle="tooltip" data-placement="top" onClick="open_create_workorder_modal('.$row['product_id'].','.$row["order_id"].',\''.$pendingqty.'\')"><i class="fa fa-dot-circle-o"></i> Create Indent</button>';
			
			$row_data[] = $view . ' ' . $indent;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode($output );
				
		}
		else if(strtolower($POST['mode']) == "get_product_name"){
			echo get_product_name($dbcon,$POST['product_id']);
		}
		else if(strtolower($POST['mode'])== "create_workorder") {

		$product_id = $POST['so_product_id'];
		$store_order_id= $POST['sales_ordertrn_id'];
		$branch_id = $_SESSION['branch_id'];
		
		$query1="select * from  tbl_invoicetype where type_id='9' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
		$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
		$id=$rows['taxinvoice_start'];
		$id=$id+1;
		
		$new_query1="update tbl_invoicetype set taxinvoice_start = ".$id." where type_id='9' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
		$dbcon->query($new_query1);				
		
		if($rows['invoice_format']=='2')
		{
			$info['po_req_no']	 = str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
		}
		else if($rows['invoice_format']=='1')
		{
			$info['po_req_no']	 = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
		}
		else if($rows['invoice_format']=='3'){
			$info['po_req_no']	 = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
		}
		else{
			$info['po_req_no']	 = str_pad($id,3,"0",STR_PAD_LEFT);
		}

		
		$rel1=brp_mysqli_fetch_assoc($dbcon->query($query1));
		
		$info['po_req_date']			=date('Y-m-d',strtotime($POST['indent_date']));
		$info['rp_req_qty']				=$POST['indent_qty'];
		$info['in_process_qty_main']	= '';
		$info['rp_po_qty']				=$POST['indent_qty'];;
		$info['product_id']				=$product_id;
		$info['store_order_id']			= $store_order_id;
		$info['company_id']				=$_SESSION['company_id'];
		$info['bom_id']					="";
		$info['bom_no']					="";
		$info['cdate']					= date('Y-m-d H:i:s');
		$info['user_id']				= $_SESSION['user_id'];
		$info['branch_id']				= $branch_id;
		$info['po_req_no']				= load_series_no($dbcon,9);
			
		$inserid=add_record('tbl_set_main_process', $info, $dbcon,$branch_id);
		if($inserid){

			update_store_oreder_request_workorder_qty_status($dbcon,$store_order_id,$product_id,$POST['indent_qty']);
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '9' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);

			$set_pro="SELECT product_base_unit,product_conv_unit FROM `product_mst` WHERE product_status=0 AND product_id='".$product_id."'";
			$setpro_rel=brp_mysqli_fetch_assoc($dbcon->query($set_pro));

			$info_su['sp_id']				= $inserid;
			$info_su['sr_no']				= 0;
			$info_su['rp_pid']				= $product_id;//product_id
			$info_su['rp_req_date']			= date("Y-m-d");
			$info_su['rp_req_qty']			= $POST['indent_qty'];//required qty
			$info_su['store_order_id']	= $store_order_id;//required qty
			$info_su['rp_po_qty']			= $POST['indent_qty'];//po qty
			$info_su['in_process_qty']		= '';//process qty
			$info_su['rp_req_type']			= "store_request_order";//type
			$info_su['process_unit']		= $setpro_rel['product_base_unit'];
			$info_su['purchase_unit']		= $setpro_rel['product_conv_unit'];
			$info_su['perent_id']			= 0;
			$info_su['main_request']		= 1;
			$info_su['status']				= 0;
			$info_su['user_id']				= $_SESSION['user_id'];
			$info_su['company_id']			= $_SESSION['company_id'];
			
			$info_su['bom_id']				='';
			$info_su['product_version']		= '';
			
			$indent_no=load_common_no($dbcon,17);
			update_common_no($dbcon,17);
			$info_su['indent_status']		= 1;
			$info_su['indent_no']			= $indent_no;
			$info_su['indent_date']			= date('Y-m-d');
			$info_su['cdate']			= date('Y-m-d H:i:s');
			$info_su['branch_id']			=	$branch_id;

			
			$inserid_sub1=add_record('tbl_request_product', $info_su, $dbcon,	$branch_id);

			/*$info_soallo['sales_ordertrn_id']		= $sales_ordertrn_id;	
			$info_soallo['product_id']				= $product_id;	
			$info_soallo['product_qty']				= $info['rp_req_qty'];	
			$info_soallo['request_id']				= $inserid_sub1;	
			$info_soallo['unit_id']					= $info_su['process_unit']	;	
			$info_soallo['user_id']					= $_SESSION['user_id'];	
			$info_soallo['cdate']					= date("Y-m-d H:i:s");	
			$info_soallo['company_id']				= $_SESSION['company_id'];	

			$inser_so_allo=add_record('tbl_sales_order_production_trn', $info_soallo, $dbcon);*/
			$arr['msg']  = '1';
		}else{
			$arr['msg']  = '0';
		}
		
		echo json_encode($arr);
	}
	}
    
}


function update_store_oreder_request_workorder_qty_status($dbcon,$order_id,$product_id,$qty){

	$set_pro="SELECT * FROM `tbl_store_order_min_max` WHERE status=0 AND order_id='".$order_id."'";
			$setpro_rel=brp_mysqli_fetch_assoc($dbcon->query($set_pro));

	$req_qty = $setpro_rel['base_request_qty'];
	$req_conv_qty = $setpro_rel['conv_request_qty'];

	$wo_base_qty = $setpro_rel['wo_base_qty'];
	$wo_conv_qty = $setpro_rel['wo_conv_qty'];

	$conv_qty = convert_stock_new($dbcon,$qty,$product_id,"conv_unit");

	$wo_base_qty = $wo_base_qty  + $qty;
	$wo_conv_qty = $wo_conv_qty  + $conv_qty;

			$upd_info['wo_base_qty'] = $wo_base_qty;
			$upd_info['wo_conv_qty'] =$wo_conv_qty;

			if($wo_base_qty >= $req_qty){
				$upd_info['wo_complete_status'] =1;				
			}

			$updatetrnid=update_record('tbl_store_order_min_max',$upd_info,"order_id=".$order_id, $dbcon);
}
/*
else {
    die("Error - 1");
}*/

?>