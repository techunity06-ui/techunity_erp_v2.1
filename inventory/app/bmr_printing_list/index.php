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
	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
	$company_config = getCompanyConfiguration($dbcon);
	
	$where.="  and DATE(sp.cdate) >= '".date('Y-m-d',strtotime($s_date[0]))."' AND DATE(sp.cdate) <= '".date('Y-m-d',strtotime($s_date[1]))."'";
	 	
	$appData = array();
	$i=1;
	$aColumns = array('sp.po_req_no','sp.po_req_date','pro.product_name','pro.product_icode','ap.batch_no','rp.rp_id','ap.p_id','ap.p_qty','ap.process_id','ap.process_priority','priority1');
	$sIndexColumn = "ap.p_id";
	$isWhere = array('rp.status = 0 and rp.finish_status = 1 and rp.main_request = 1 and ap.process_priority = priority1  and rp.company_id = ' . $_SESSION['company_id'].$where);
	$sTable = "tbl_request_product as rp";
	$isJOIN = array('left join tbl_set_main_process as sp on sp.sp_id = rp.sp_id','left join tbl_allocate_process as ap on ap.p_ref_id = rp.rp_id','LEFT JOIN product_mst as pro on pro.product_id = rp.rp_pid','LEFT JOIN ( SELECT MAX(cc.process_priority) as priority1,cc.rp_id as rrpid FROM tbl_wororder_product_process as cc where 1 group by cc.rp_id) as ws ON ws.rrpid=rp.rp_id');
	$hOrder = "ap.p_id desc";
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['po_req_no'];
		$row_data[] = date('d M, Y',strtotime($row["po_req_date"]));
		$row_data[] = $row['product_name']. " -- (".$row['product_icode'].")";
		$row_data[] = $row['batch_no'];
		$row_data[] = $row['p_qty'];
		
		$btn_p1='<a class="btn btn-xs btn-primary" data-original-title="Print" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.'smpl_p1/'.$row['p_id'].'"><i class="fa fa-print"></i> P1</a>';
		
		$btn_p2='<a class="btn btn-xs btn-primary" data-original-title="Print" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.'smpl_p2/'.$row['p_id'].'"><i class="fa fa-print"></i> P2</a>';
		
		$btn_p3='<a class="btn btn-xs btn-primary" data-original-title="Print" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.'smpl_p3/'.$row['p_id'].'"><i class="fa fa-print"></i> P3</a>';
			
		$row_data[] = $btn_p1 . ' ' . $btn_p2 . ' ' . $btn_p3;
	 
		$appData[] = $row_data;
	$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
?>