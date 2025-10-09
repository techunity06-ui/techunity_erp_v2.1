<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include(COMMON_FUNCTION_PATH."common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
	if($POST['start_date'] && $POST['end_date']){
		$_SESSION['start'] = $start_date = $POST['start_date'];
		$_SESSION['end'] = $end_date = $POST['end_date'];
	} else {
		$start_date = date('1-m-Y');
		$end_date = date("d-m-Y");
	}
	$where='';
/*	if(!empty($start_date) && !empty($end_date)){
		$where.="  AND l.in_time >= '".date('Y-m-d',strtotime($start_date))."' AND l.out_time <= '".date('Y-m-d',strtotime($end_date))."'";
	}*/
		
	//echo "jayesh".$POST['cust_id'];
	
	if(!empty($POST['cust_id'])){
		$where.=" AND l.uid = '".$POST['cust_id']."'";
	}else{
		$where.=" AND l.uid = '".$_SESSION['user_id']."'";
	}
	

	$appData = array();
	$i=1;
	/*$aColumns = array('trn_user.trn_dashbord_user_id','trn_user.user_id','trn_user.trn_dashbord_id','trn_user.show_status','trn_dash.trn_dashbord_id','trn_dash.type','trn_dash.transaction_no','trn_dash.transaction_id','trn_dash.description','trn_dash.amount','trn_dash.cdate','user.user_name');
	$sIndexColumn = "trn_user.trn_dashbord_user_id";
	$isWhere = array("trn_user.show_status = 0 AND trn_dash.is_delete = 0".$where);
	$sTable = "tbl_trnsaction_dashbord_user as trn_user";
	$isJOIN = array('left join tbl_trnsaction_dashbord as trn_dash on trn_dash.trn_dashbord_id=trn_user.trn_dashbord_id','left join users as user on user.user_id=trn_user.user_id');
	$hOrder = "trn_user.trn_dashbord_user_id desc";*/
	$aColumns = array('u.user_name,l.uid,l.in_time,l.out_time');
	$sIndexColumn = "u.user_id";
	$isWhere = array("u.active=0 and u.user_type!=1".$where);
	$sTable = "users as u";
	$isJOIN = array('left join login_history as l on u.user_id=l.uid');
	$hOrder = "l.log_id desc";
	
	// $hGroupby = array();
	include('../../include/pagging.php');
	$appData = array();
	$id=1;
//echo $sqlReturn;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['uid'];
		$row_data[] = $row['user_name'];
		$row_data[] = $row['in_time'];
		$row_data[] = $row['out_time'];
				
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;	
	//echo "<pre>"; print_r($output['aaData']);
	echo json_encode( $output );
} 
?>