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
	if(!empty($start_date) && !empty($end_date)){
		$where.="  AND trn_dash.show_date >= '".date('Y-m-d',strtotime($start_date))."' AND trn_dash.show_date <= '".date('Y-m-d',strtotime($end_date))."'";
	}
	if(!empty($POST['type'])){
		$where.=" and trn_dash.type = '".$POST['type']."'";
	}
	if(!empty($POST['cust_id'])){
		$where.=" AND trn_user.user_id = '".$POST['cust_id']."'";
	}else{
		$where.=" AND trn_user.company_id = '".$_SESSION['company_id']."'";
	}

	$appData = array();
	$i=1;
	$aColumns = array('trn_user.trn_dashbord_user_id','trn_user.user_id','trn_user.trn_dashbord_id','trn_user.show_status','trn_dash.trn_dashbord_id','trn_dash.type','trn_dash.transaction_no','trn_dash.transaction_id','trn_dash.description','trn_dash.amount','trn_dash.cdate','user.user_name');
	$sIndexColumn = "trn_user.trn_dashbord_user_id";
	$isWhere = array("trn_user.show_status = 0 AND trn_dash.is_delete = 0".$where);
	$sTable = "tbl_trnsaction_dashbord_user as trn_user";
	$isJOIN = array('left join tbl_trnsaction_dashbord as trn_dash on trn_dash.trn_dashbord_id=trn_user.trn_dashbord_id','left join users as user on user.user_id=trn_dash.user_id');
	$hOrder = "trn_user.trn_dashbord_user_id desc";
	// $hGroupby = array();
	include('../../include/pagging.php');
	$appData = array();
	$id=1;

	foreach($sqlReturn as $row) {
		$row_data = array();

		$row_data[] = $row['type'];
		$row_data[] = $row['transaction_no'];
		$row_data[] = $row['description'];
		$row_data[] = $row['amount'];
		$row_data[] = $row['user_name'];
		$row_data[] = $row['cdate'];

		$print_btn = '';$ptype='';

		if($row['type']=='Purchase'){
			$ptype = 10;
		}else if($row['type']=='Purchase Order'){
			$ptype = 4;
		}else if($row['type']=='Sales Order'){
			$ptype = 3;
		}else if($row['type']=='Invoice'){
			$ptype = 7;
		}else if($row['type']=='Jobwork'){
			$ptype = 11;
		}else if($row['type']=='Work Order'){
			$ptype = 14;
		}else if($row['type']=='Job Card'){
			$ptype = 12;
		}else if($row['type']=='Indent'){
			$ptype = 13;
		}
		
		$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
		$rels=mysqli_fetch_assoc($menusql);
		$menu_show_permissions = explode(",",$rels['print_permission']);
		$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = '".$ptype."' AND approve_status = 1 AND status = 0 ORDER BY priority");
		while($res = mysqli_fetch_assoc($sql)){
			if(in_array($res['id'],$menu_show_permissions)) {
				$print_btn.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['transaction_id'].'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>&nbsp;';
			}
		}

		$app_btn = '<button class="btn btn-xs btn-primary" data-original-title="Task Done" data-toggle="tooltip" data-placement="top" onClick="task_done('.$row['trn_dashbord_user_id'].')"><i class="fa fa-exclamation-triangle"></i></button>';

		$row_data[] = '<input type="checkbox" class="form-control" style="width:20px;" id="allchk'.$row["trn_dashbord_user_id"].'" name="chk" value="'.$row["trn_dashbord_user_id"].'"> '.$app_btn.' '.$print_btn;
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
} else if(strtolower($POST['mode']) == "task_done") {
	$trn_dashbord_user_id = $_POST['trn_dashbord_user_id'];
	$infotrn['show_status'] = 1;
	$infotrn['show_date'] = date('Y-m-d');
	$infotrn['show_date_time'] = date("Y-m-d H:i:s");

	$tran_user_id=update_record('tbl_trnsaction_dashbord_user', $infotrn,"trn_dashbord_user_id = '$trn_dashbord_user_id'" , $dbcon);

	if($tran_user_id){
		echo "1";
	}else{
		echo "0";
	}
}else if(strtolower($POST['mode']) == "print_cust_label") {
	$sr=$POST['trn_dashbord_user_id'];

	foreach($sr as $sre){
		$infotrn['show_status'] = 1;
		$infotrn['show_date'] = date('Y-m-d');
		$infotrn['show_date_time'] = date("Y-m-d H:i:s");

		$tran_user_id=update_record('tbl_trnsaction_dashbord_user', $infotrn,"trn_dashbord_user_id = '$sre'" , $dbcon);
	}

	if($tran_user_id){
		echo "1";
	}else{
		echo "0";
	}
}
?>