<?php
session_start();
$AJAX = true;
	include('../../include/urlfileinner.php');
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	PRODUCTION_GRN_LIST_SLUG_VIEW,PRODUCTION_GRN_LIST_SLUG_CREATE,PRODUCTION_GRN_LIST_SLUG_UPDATE,PRODUCTION_GRN_LIST_SLUG_DELETE
]);

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(brp_strtolower($POST['mode']) == "add") 
{
	$info['po_item_sr'] = $POST['po_item_sr'];
	$info['po_item'] = $POST['po_item'];
	$info['datasheet'] = $POST['datasheet'];
	$info['gad'] = $POST['gad'];
	$info['qap'] = $POST['qap'];
	$info['valve_type'] = $POST['valve_type'];
	$info['size_class'] = $POST['size_class'];
	$info['qsl'] = $POST['qsl'];
	$info['qty'] = $POST['qty'];
	$info['valve_sr'] = $POST['valve_sr'];
	$info['moc'] = $POST['moc'];
	$info['service'] = $POST['service'];
	$info['design_standard'] = $POST['design_standard'];
	$info['testing_standard'] = $POST['testing_standard'];
	$info['mfg_req'] = $POST['mfg_req'];
	$info['test_req'] = $POST['test_req'];
	$info['tpi_scope'] = $POST['tpi_scope'];
	$info['sales_service_req'] = $POST['sales_service_req'];
	$info['coating_painting_req'] = $POST['coating_painting_req'];
	$info['packing_req'] = $POST['packing_req'];
	$info['marking_on_product'] = $POST['marking_on_product'];
	$info['marking_on_packing'] = $POST['marking_on_packing'];
	$info['api_monogram_marking'] = $POST['api_monogram_marking'];
	$info['del_dua_date'] = $POST['del_dua_date'];
	$info['customer_cont_details'] = $POST['customer_cont_details'];
	$info['del_location'] = $POST['del_location'];
	$info['documents'] = $POST['documents'];
	$info['payment_terms'] = $POST['payment_terms'];
	$info['insurance'] = $POST['insurance'];
	$info['freight'] = $POST['freight'];
	$info['additional_req'] = $POST['additional_req'];
	$info['prepared_by'] = $POST['prepared_by'];
	$info['approved_by'] = $POST['approved_by'];
	$info['cdate'] = date("Y-m-d H:i:s");
	$info['user_id'] = $_SESSION['user_id'];
	$info['company_id'] = $_SESSION['company_id'];
	$info['workorder_id'] = $POST['workorder_id'];

	if(empty($POST['edit_id']))
	{
		$inserid=add_record('tbl_libra_workorder_fields', $info, $dbcon);
	}else{
		$inserid=update_record('tbl_libra_workorder_fields', $info,'id'."=".$POST['edit_id'] , $dbcon);
	}
	
	if($inserid){
		$row['msg']="1";
	}
	else{
		$row['msg']="0";
	}
	echo json_encode($row);
}
?>