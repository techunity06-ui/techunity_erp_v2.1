<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
if(strtolower($POST['mode']) == "company_configuration") {
	
	$infoc['hermattic_permission'] = $_POST['hermattic_permission'];
	$infoc['elcon_permission']	   = $_POST['elcon_permission'];
	$infoc['maruti_permission']	   = $_POST['maruti_permission'];
	$infoc['rb_auto_permission']   = $_POST['rb_auto_permission'];
	$infoc['umaboy_permission']   = $_POST['umaboy_permission'];
	$infoc['oilfield_permission']   = $_POST['oilfield_permission'];
	$infoc['jr_fiber_glass_permission']   = $_POST['jr_fiber_glass_permission'];
	$infoc['vipul_copper_permission']   = $_POST['vipul_copper_permission'];
	$infoc['filter_concept_permission']   = $_POST['filter_concept_permission'];
	$infoc['atlas_permission']   = $_POST['atlas_permission'];
	$infoc['smpl_permission']   = $_POST['smpl_permission'];
	$infoc['durva_permission']   = $_POST['durva_permission'];
	$infoc['aeon_permission']   = $_POST['aeon_permission'];
	$infoc['sreeji_stilix_permission']   = $_POST['sreeji_stilix_permission'];
	$infoc['libra_engineering_permission']   = $_POST['libra_engineering_permission'];
	$infoc['power_drive']   = $_POST['power_drive'];
	$infoc['sspl']   			= $_POST['sspl'];
	$infoc['reciclar']   			= $_POST['reciclar'];
	$infoc['meru_permission']   			= $_POST['meru_permission'];
	$infoc['austar_permission']   			= $_POST['austar_permission'];
	$infoc['invoite_permission']   			= $_POST['invoite_permission'];
	$infoc['apson_special']   			= $_POST['apson_special'];
	$infoc['uniter_permission']   			= $_POST['uniter_special'];
	
	$infoc['user_id'] 			   = $_SESSION['user_id'];
	$infoc['company_id'] 		   = $_SESSION['company_id'];
//var_dump($infoc);
//exit();
	$tableName1='tbl_company_special_field_permission';
	if($POST['sp_field_permission_id']) {
		$infoc['cdate'] = date("Y-m-d H:i:s");
		$updateid = update_record($tableName1, $infoc, "sp_field_permission_id='".$POST['sp_field_permission_id']."'", $dbcon);
	} else {
		$infoc['cdate'] = date("Y-m-d H:i:s");
		$updateid = add_record($tableName1, $infoc, $dbcon);
	}
	echo ($updateid) ? 'update' : '0'.$dbcon->error;
}

?>