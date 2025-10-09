<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "generate_report_product_service"){
	$where = "";
	$_SESSION['start_date'] = $POST['start_date'];
	$_SESSION['end_date'] = $POST['end_date'];
	$user_ids = $POST['user_id'];
	$opp_id = $POST['opp_id'];

	if($opp_id){
		$where .= "and e.opp_id = ".$opp_id;
	}
	if($user_ids){	
		$va=" and DATE_FORMAT(e.inquiry_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) and e.inquiry_status=0 and e.user_id in (".$user_ids.") $where";
	}
	$appData = array();
	$i=1;
	$aColumns = array('e.cdate','e.inquiry_name as opportunity_name','mc.mcd_name as sales_stage','cust.cust_name','us.user_name as lead_owner','op.opp_stage as stage','e.stage_prob as probablity','e.closing_date','e.inquiry_id','e.opp_id','comp.company_name','rf.rb_name');
	$sIndexColumn = "e.inquiry_id";
	$isWhere = array("e.company_id IN (0,$_SESSION[company_id])".$va);
	$sTable = " tbl_inquiry as e";			
	$isJOIN = array("left join tbl_customer as cust on cust.cust_id=e.cust_id",
		"left join users as us on us.user_id=e.user_id",
		"left join tbl_opportunity_mst as op on op.opp_id=e.opp_id",
		"left join tbl_company as comp on comp.company_id=e.company_id",
		"left join tbl_refer_by as rf on rf.rb_id=e.rb_id",
		"left join tbl_master_category_detail as mc on mc.mcd_id=e.sales_stage_id");
	$hOrder = "e.inquiry_id";
	include('../../../include/pagging.php');
	$appData = array();
	$id=1;
	$view_hist_btn = "";
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['rb_name'];
		$row_data[] = date('d-m-Y H:i:s',strtotime($row['cdate']));
		$row_data[] = $row['company_name'];
		$row_data[] = $row['cust_name'].$row['opportunity_name'];
		$row_data[] = $row['lead_owner'];
		$row_data[] = $row['stage'];
		$row_data[] = $row['sales_stage'];
		$row_data[] = $row['probablity'];
		$row_data[] = date('d-m-Y',strtotime($row['closing_date']));

		$view_hist_btn = '<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_view/'.$row['inquiry_id'].'"><i class="fa fa-eye"></i></a>';
		$row_data[] = $view_hist_btn;
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}


?>