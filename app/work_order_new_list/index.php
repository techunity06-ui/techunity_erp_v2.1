<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		//$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		//$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where=''; 
		$where.=" and po_req_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND po_req_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$appData = array();
		$i=1;
		$aColumns = array('po_req_no','po_req_date', 'rp_req_qty','sp_id','cust.l_name','pro.product_name');
		$sIndexColumn = "sp_id";
		$isWhere = array("1".$where.check_user('grn'));
		$sTable = "tbl_set_main_process as grn";
		$isJOIN = array('left join tbl_ledger as cust on cust.l_id=grn.vendor_id','left join product_mst as pro on pro.product_id=grn.product_id');
		$hOrder = "grn.sp_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			
					$row_data[] = '<a class="" data-original-title="view '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'work_order_new_print/'.$row['sp_id'].'">'.$row["po_req_no"].'</a>';
					$row_data[] = '<a class="" data-original-title="view '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'work_order_new_print/'.$row['sp_id'].'">'.date('d M, Y',strtotime($row["po_req_date"])).'</a>';
					$row_data[] = '<a class="" data-original-title="view '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'work_order_new_print/'.$row['sp_id'].'">'.$row["product_name"].'</a>';
					if(!empty($row["l_name"])){
						$row_data[] = '<a class="" data-original-title="view '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'work_order_new_print/'.$row['sp_id'].'">'.$row["l_name"].'</a>';
					}else{
						$row_data[] = "-";
					}
					$row_data[] = '<a class="" data-original-title="view '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'work_order_new_print/'.$row['sp_id'].'">'.$row["rp_req_qty"].'</a>';
					
					
				
   
			$edit_btn=''; $delete_btn=''; $view='';
				
			$view='<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.'grn_view/'.$row['grn_id'].'"><i class="fa fa-eye"></i></a> ';
			
			$row_data[] = $edit_btn.' '.$delete_btn.' '.$view;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	 
	

?>