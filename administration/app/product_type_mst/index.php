<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
		//check permission for party industry add
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		ADMINISTRATOR_PRODUCT_TYPE_UPDATE,
		ADMINISTRATOR_PRODUCT_TYPE_DELETE
	]);
	$branch_id = $POST['branch_id'];
	$where='';
	if($branch_id != '1000'){
		$where .= check_branch('zmst',$branch_id);
	}
	if($branch_id == ""){
		$output = array(
			"sEcho" => 1,
			"iTotalRecords" => 0,
			"iTotalDisplayRecords" => 0,
			"aaData" => array()
		);

		echo json_encode( $output );
	}else{
		$i=1;
		$aColumns = array('zmst.product_type_id','zmst.process_required', 'zmst.product_type_name','zmst.cdate', 'zmst.product_type_status', 'zmst.user_id', 'code.pr_code_short', 'code.pr_code_series');
		$sIndexColumn = "zmst.product_type_id";
		$isWhere = array("zmst.product_type_status = 0 and zmst.company_id in (0,".$_SESSION['company_id'].")".$where);
		$sTable = "pro_ms_product_type as zmst";			
		$isJOIN = array('left join tbl_product_code_series as code on code.pr_type = zmst.product_type_id and code.company_id = '.$_SESSION['company_id']);
		$hOrder = "zmst.product_type_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {


			if($row['process_required']=='1')
			{
				$pr_reqd = "<strong style='color:green'>Yes</strong>";
			}
			else
			{
				$pr_reqd = "<strong style='color:red'>NO</strong>";	
			}

			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['product_type_name']; 
			$row_data[] = $pr_reqd; 
			$row_data[] = $row['pr_code_short']; 
			$row_data[] = $row['pr_code_series']; 

			
			$edit_btn='';$delete_btn='';
			if(in_array(ADMINISTRATOR_PRODUCT_TYPE_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_product_type('.$row['product_type_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(ADMINISTRATOR_PRODUCT_TYPE_DELETE,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_product_type('.$row['product_type_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}

}
else if(strtolower($POST['mode']) == "add") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$tr = $dbcon -> query("SELECT `product_type_id`,`product_type_name`,`product_type_status`,`company_id` FROM `pro_ms_product_type` WHERE product_type_status=0 and company_id = '".$_SESSION['company_id']."' and `product_type_name` ='".$POST['process_type_name']."'");
	if($tr->num_rows > 0) {
		$resp['msg'] = '-1';
	}
	else {
		$info['product_type_name']	= $POST['product_type_name'];
		$info['process_required']	= $POST['process_required'];										
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
			//echo "<pre>"; print_r($info);
		$inserid=add_record('pro_ms_product_type', $info, $dbcon, $branch_id);

		if($inserid){
			$resp['msg'] = "1";
			$info1['pr_code_short'] = $POST['pr_code_short'];
			$info1['pr_code_series'] = $POST['pr_code_series'];
			$info1['pr_type'] = $inserid;
			$info1['company_id']	= $_SESSION['company_id'];

			$inserids=add_record('tbl_product_code_series', $info1, $dbcon);

			if(strtolower($POST['product_type_model']) == "product_type_model"){
				$process_type_qry="select * from pro_ms_product_type where product_type_id=".$inserid; 
				$process_type_rel=mysqli_fetch_assoc($dbcon->query($process_type_qry));
				$resp=$process_type_rel;
				$resp['msg'] = "2"; 
			}
		}
		else{
			$resp['msg'] = "0";
		}
	}
	echo json_encode($resp);
}
else if(strtolower($POST['mode']) == "preedit") {			
	$q = $dbcon -> query("SELECT type.*, code.* FROM `pro_ms_product_type` as type LEFT JOIN tbl_product_code_series as code ON code.pr_type = type.product_type_id WHERE `product_type_id` = '$POST[product_type_id]'");
	$r = $q->fetch_assoc();
	echo json_encode($r);
}
else if(strtolower($POST['mode']) == "edit") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$tr = $dbcon -> query("SELECT `product_type_id`,`product_type_name`,`product_type_status`,`company_id` FROM `pro_ms_product_type` WHERE product_type_status=0 and company_id = '".$POST['company_id']."' and `product_type_name` ='".$POST['product_type_name']."' and `product_type_id` != '".$POST['eid']."'");
	if($tr->num_rows > 0) {
		echo '-1';
	} else {
		$info['product_type_name']	= $POST['product_type_name'];	
		$info['process_required']	= $POST['process_required'];						
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
			//echo "<pre>"; print_r($info); die;

		$updateid=update_record('pro_ms_product_type', $info,"product_type_id=".$POST['eid'] , $dbcon, $branch_id);

		$info1['pr_code_short'] = $POST['pr_code_short'];
		$info1['pr_code_series'] = $POST['pr_code_series'];
		$info1['company_id']	= $_SESSION['company_id'];

		$updateids=update_record('tbl_product_code_series', $info1,"pr_type=".$POST['eid'] , $dbcon);

		if($updateid)
			echo "1";
		else
			echo "0".$dbcon->error; 
	}
}
else if(strtolower($POST['mode']) == "delete") {
	$sTable = array(TABLE_PRODUCT_MASTER=>'PROCESS MODULE');
	$aColumns = array(array('prodcut_type'));
	$sWhere = array(array('product_status=0 and product_type = "'.$POST['product_type_id'].'"'));
	$checkLang = getCheckRelation($dbcon, $sTable, $aColumns, $sWhere);
	if(count($checkLang) > 0){
		$resp['msg'] = '-1';
		$resp['table'] = $checkLang;
	}else{
		$info['product_type_status']='2';
		$updateid=update_record('pro_ms_product_type', $info,"product_type_id=".$POST['product_type_id'] , $dbcon);

		if($updateid)
			$resp['msg'] = '1';
		else
			$resp['msg'] = '0';
	}
	echo json_encode($resp);
}


?>