<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions/common_functions.php");
include_once("../../include/common_functions/common_production_functions.php");
$company_config = getCompanyConfiguration($dbcon);		
$production_pro_search = $company_config['production_pro_search'];
$pro_search=explode(",", $production_pro_search);

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	 if(strtolower($POST['mode']) == "generate_report_stock")
		{
				$where = "";
				$product_id = mysqli_real_escape_string($dbcon,$POST['product_id']);
				$product_type = $POST['product_type'];
				$product_category = $POST['product_category'];
				
				if($product_id!='')
				{
					$where="and pro.product_id='$product_id'";
				}

				if($product_category!='')
				{
					$where .="and pro.product_category='$product_category'";
				}

				if($product_type!='')
				{
					$where .="and pro.product_type='$product_type'";
				}
				
				$appData = array();
				$i=1;
				$aColumns = array('pro.product_icode,pro.product_min_stock,pro.product_making_time,pro.product_alias_name,pro.manufactur_name,pro.cat_no,pmpt.product_type_name, dr.drawing_number, cat.cat_name, pro.product_id, pro.product_base_unit, un.unit_name, c_un.unit_name AS conv_unit_name, pro.product_name, pro.product_status, pro.product_conv_unit, pro.batch_wise_stock_manage,
    IFNULL(SUM(CASE WHEN qc.stock_flage = 1 AND qc.base_unit = pro.product_base_unit AND qc.customer_id = 0 THEN qc.base_stock ELSE 0 END), 0) AS base_stock_add,
    IFNULL(SUM(CASE WHEN qc.stock_flage = 2 AND qc.base_unit = pro.product_base_unit AND qc.customer_id = 0 THEN qc.base_stock ELSE 0 END), 0) AS base_stock_minus,
    IFNULL(SUM(CASE WHEN qc.stock_flage = 1 AND qc.customer_id = 0 AND qc.base_unit != qc.convert_unit AND qc.convert_unit = pro.product_base_unit THEN qc.convert_stock ELSE 0 END), 0) AS con_stock_add,
    IFNULL(SUM(CASE WHEN qc.stock_flage = 2 AND qc.customer_id = 0 AND qc.base_unit != qc.convert_unit AND qc.convert_unit = pro.product_base_unit THEN qc.convert_stock ELSE 0 END), 0) AS con_stock_minus,
    IFNULL(SUM(CASE WHEN qc.stock_flage = 1 AND qc.customer_id = 0 AND qc.convert_unit = pro.product_conv_unit THEN qc.convert_stock ELSE 0 END), 0) AS convert_stock_add1,
    IFNULL(SUM(CASE WHEN qc.stock_flage = 2 AND qc.customer_id = 0 AND qc.convert_unit = pro.product_conv_unit THEN qc.convert_stock ELSE 0 END), 0) AS convert_stock_minus1,
    IFNULL(SUM(CASE WHEN qc.stock_flage = 1 AND qc.customer_id = 0 AND qc.convert_unit != qc.base_unit AND qc.convert_unit = pro.product_base_unit THEN qc.convert_stock ELSE 0 END), 0) AS base_stock_add1,
    IFNULL(SUM(CASE WHEN qc.stock_flage = 2 AND qc.customer_id = 0 AND qc.convert_unit != qc.base_unit AND qc.convert_unit = pro.product_base_unit THEN qc.convert_stock ELSE 0 END), 0) AS base_stock_minus1,
    IFNULL(SUM(CASE WHEN qc.stock_flage = 1 AND qc.base_unit = pro.product_base_unit AND qc.customer_id != 0 AND qc.company_id = 1 THEN qc.base_stock ELSE 0 END), 0) AS cust_base_stock_add,
    IFNULL(SUM(CASE WHEN qc.stock_flage = 2 AND qc.base_unit = pro.product_base_unit AND qc.customer_id != 0 AND qc.company_id = 1 THEN qc.base_stock ELSE 0 END), 0) AS cust_base_stock_minus,
    IFNULL(SUM(CASE WHEN qc.stock_flage = 1 AND qc.base_unit != qc.convert_unit AND qc.convert_unit = pro.product_base_unit AND qc.customer_id != 0 AND qc.company_id = 1 THEN qc.convert_stock ELSE 0 END), 0) AS cust_con_stock_add,
    IFNULL(SUM(CASE WHEN qc.stock_flage = 2 AND qc.base_unit != qc.convert_unit AND qc.convert_unit = pro.product_base_unit AND qc.customer_id != 0 AND qc.company_id = 1 THEN qc.convert_stock ELSE 0 END), 0) AS cust_con_stock_minus,
    IFNULL(SUM(CASE WHEN qc.stock_flage = 1 AND qc.convert_unit = pro.product_conv_unit AND qc.customer_id != 0 AND qc.company_id = 1 THEN qc.convert_stock ELSE 0 END), 0) AS cust_convert_stock_add1,
    IFNULL(SUM(CASE WHEN qc.stock_flage = 2 AND qc.convert_unit = pro.product_conv_unit AND qc.customer_id != 0 AND qc.company_id = 1 THEN qc.convert_stock ELSE 0 END), 0) AS cust_convert_stock_minus1,
    IFNULL(SUM(CASE WHEN qc.stock_flage = 1 AND qc.convert_unit != qc.base_unit AND qc.convert_unit = pro.product_base_unit AND qc.customer_id != 0 AND qc.company_id = 1 THEN qc.convert_stock ELSE 0 END), 0) AS cust_base_stock_add1,
    IFNULL(SUM(CASE WHEN qc.stock_flage = 2 AND qc.convert_unit != qc.base_unit AND qc.convert_unit = pro.product_base_unit AND qc.customer_id != 0 AND qc.company_id = 1 THEN qc.convert_stock ELSE 0 END), 0) AS cust_base_stock_minus1,
    COALESCE((
        SELECT base_rate
        FROM tbl_stock_trn
        WHERE product_id = pro.product_id
            AND stock_flage = 1
            AND stock_status != 2
            AND ref_name = "tbl_grn_trn"
        ORDER BY stock_id DESC
        LIMIT 1
    ), (
        SELECT base_rate
        FROM tbl_stock_trn
        WHERE product_id = pro.product_id
            AND stock_flage = 1
            AND stock_status != 2
            AND ref_name = "opening_stock"
        ORDER BY stock_id DESC
        LIMIT 1
    )) AS last_base_rate,
    COALESCE((
        SELECT conv_rate
        FROM tbl_stock_trn
        WHERE product_id = pro.product_id
            AND stock_flage = 1
            AND stock_status != 2
            AND ref_name = "tbl_grn_trn"
        ORDER BY stock_id DESC
        LIMIT 1
    ), (
        SELECT conv_rate
        FROM tbl_stock_trn
        WHERE product_id = pro.product_id
            AND stock_flage = 1
            AND stock_status != 2
            AND ref_name = "opening_stock"
        ORDER BY stock_id DESC
        LIMIT 1
    )) AS last_conv_rate');
				$sIndexColumn = "pro.product_id";
				$isWhere = array("pro.product_status = 0 and qc.stock_status !=2 ".$where);
				$sTable = "product_mst as pro";			
				$isJOIN = array("LEFT JOIN unit_mst AS un ON un.unitid = pro.product_base_unit
				LEFT JOIN tbl_category AS cat ON cat.cat_id = pro.product_category
				LEFT JOIN pro_ms_product_type AS pmpt ON pmpt.product_type_id = pro.product_type
LEFT JOIN unit_mst AS c_un ON c_un.unitid = pro.product_conv_unit
LEFT JOIN tbl_drawing AS dr ON dr.drawing_id = pro.drawing_id
LEFT JOIN tbl_stock_trn AS qc ON qc.product_id = pro.product_id AND qc.company_id = " . $_SESSION['company_id']);
				$hOrder = "pro.product_name";
				$hGroupby = array("pro.product_id");
				include('../../include/pagging.php');
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
					
					//$stock=($row['product_opening']+$row['grn_total']+$row['qc_total']+$row['add_adjustment_qty'])-($row['inv_qty']+$row['sup_qty']+$row['jobout_qty']+$row['qc_total_rejected']+$row['remove_adjustment_qty']);
					
					//$stock=($row['product_opening']+$row['grn_total']+$row['qc_total']+$row['add_adjustment_qty'])-($row['inv_qty']+$row['sup_qty']+$row['jobout_qty']+$row['remove_adjustment_qty']);
					
					$stock=($row['base_stock_add']+$row['con_stock_add'])-($row['base_stock_minus']+$row['con_stock_minus']);

					$conv_stock=($row['convert_stock_add1']+$row['base_stock_add1'])-($row['convert_stock_minus1']+$row['base_stock_minus1']);

					$cust_stock=($row['cust_base_stock_add']+$row['cust_con_stock_add'])-($row['cust_base_stock_minus']+$row['cust_con_stock_minus']);

					$cust_conv_stock=($row['cust_convert_stock_add1']+$row['cust_base_stock_add1'])-($row['cust_convert_stock_minus1']+$row['cust_base_stock_minus1']);
					
					//$process=str_replace(",","<br/>",$row['process_de']);

					$res_stock = reserve_stock_new($dbcon,$row['product_id'],$row['product_base_unit']);
					$res_conv_stock = reserve_stock_new($dbcon,$row['product_id'],$row['product_conv_unit']);

					$free_base_stock = $stock - $res_stock;
					$free_conv_stock = $conv_stock - $res_conv_stock;

					$base_rate = 0;
					$conv_rate = 0;


					if(!empty($row['last_purchase_rate']) && $row['last_purchase_rate'] > 0){
						$base_rate = $row['last_purchase_rate'];
						$conv_rate = $row['last_purchase_conv_rate'];
					}else if(!empty($row['last_base_rate']) && $row['last_base_rate'] > 0){
						$base_rate = $row['	'];
						$conv_rate = $row['last_conv_rate'];
					}

					if($base_rate == $conv_rate){
						$rate = $conv_rate.'</a>';

					}else{
						$rate = $base_rate.'<br>'.$conv_rate.'</a>';
					}


					$btn_batch = "";

					if($row['batch_wise_stock_manage'] == '1'){
						$btn_batch = '<a class="btn btn-info" target="_blank" href="'.ROOT.REPORT_ROOT.'batch_stock/'.$row['product_id'].'">Batch Wise Report</a>';					
					}
					
					$row_data = array();
					$row_data[] = $row['sr'];
					$row_data[] = $row['product_type_name'];
					$row_data[] = $row['product_name'];
					/*$row_data[] = get_process_stock_detail($dbcon,$row['product_id'],$row['product_base_unit']);
					$row_data[] = get_godown_stock($dbcon,$row['product_id'],$row['product_base_unit']);*/
					// $row_data[] = reserve_stock_new($dbcon,$row['product_id'],$row['product_base_unit']) . ' ' . $row['unit_name']; 
					$row_data[] = '<a data-original-title="Product Stock Details" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.REPORT_ROOT.'product_ledger_new/'.$row['product_id'].'">'.$row['cat_name'].'</a>'; 
				
					// $row_data[] = $row['product_alias_name'];
					// $row_data[] = stripcslashes($row['product_alias_name']); 
					$row_data[] = '<a data-original-title="Product Stock Details" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.REPORT_ROOT.'product_ledger_new/'.$row['product_id'].'">'.$row['product_alias_name'].'</a>';

					$row_data[] = '<a data-original-title="Product Stock Details" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.REPORT_ROOT.'product_ledger_new/'.$row['product_id'].'">'.$row['product_icode'].'</a>'; 

					$row_data[] = '<a data-original-title="Product Stock Details" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.REPORT_ROOT.'product_ledger_new/'.$row['product_id'].'">'.$row['manufactur_name'].'</a>'; 

					$row_data[] = '<a data-original-title="Product Stock Details" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.REPORT_ROOT.'product_ledger_new/'.$row['product_id'].'">'.$row['cat_no'].'</a>'; 

					// $row_data[] = '<a data-original-title="Reserve Stock Details" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.REPORT_ROOT.'reserve_stock_report/'.$row['product_id'].'">'. $res_stock .' '.$row['unit_name'].'<br>'.$res_conv_stock. ' '.$row['conv_unit_name'] .'</a>'; 

					// $row_data[] = '<a data-original-title="Reserve Stock Details" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.REPORT_ROOT.'reserve_stock_report/'.$row['product_id'].'">'.$row['unit_name'].'</a>'; 
					
					
					$row_data[] = '<a data-original-title="Product Stock Details" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.REPORT_ROOT.'product_ledger_new/'.$row['product_id'].'">'.$conv_stock. ' '.$row['conv_unit_name'] .'</a>'; 
				

					// $row_data[] = '<a data-original-title="Product Stock Details" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.REPORT_ROOT.'product_ledger_new/'.$row['product_id'].'">'.$rate.'</a>'; 

					$row_data[] = '<a data-original-title="Product Stock Details" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.REPORT_ROOT.'product_ledger_new/'.$row['product_id'].'">'.$row['product_min_stock'] .'</a>'; 
				
					$row_data[] = '<a data-original-title="Product Stock Details" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.REPORT_ROOT.'product_ledger_new/'.$row['product_id'].'">'.$row['product_making_time'] .' days</a>'; 
					
					// $row_data[] = '<a data-original-title="Product Stock Details" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.REPORT_ROOT.'product_ledger_new/'.$row['product_id'].'">'.$free_conv_stock. ' '.$row['conv_unit_name'] .'</a>'; 
					// $row_data[] = $conv_stock; 
					
					// $row_data[] = $cust_stock.' '.$row['unit_name'] . '<br>'.$cust_conv_stock.' '.$row['conv_unit_name']; 
					// $row_data[] = $cust_conv_stock.' '.$row['conv_unit_name']; 
					$row_data[] = $btn_batch; 
					$appData[] = $row_data;
					$id++;
				}
				$output['aaData'] = $appData;
				echo json_encode( $output );
			
		}
		
		
	
?>