<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

$POST = ($_POST != NULL) ? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "fetch") {

	//check permission for forcast by user pro edit and delete
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		FORECAST_USER_SLUG_EDIT,
		FORECAST_USER_SLUG_DELETE,
		FORECAST_USER_SLUG_PRINT
	]);

	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
	$branch_id = $POST['branch_id'];

	$where='';
	if($branch_id){
		$where .= check_branch('fc',$branch_id);
	}
	if(!empty($POST['f_user_id'])){
		$where .= ' and fc.f_user_id = '.$POST['f_user_id'];
	}
	if(!empty($POST['forecast_type'])){
		$where .= ' and fc.forecast_type = '.$POST['forecast_type'];
	}

	$appData = array();
	$i=1;
	$aColumns = array('fc.forecast_user_id', 'fc.forecast_no', 'fc.forecast_date','users.user_name','fc.branch_id','bm.branch_name','fc.forecast_type', 'fc.f_user_id','fc.forecast_base','fc.forecast_status','fc.approve_status');
	$sIndexColumn = "fc.forecast_user_id";
	$isWhere = array("fc.forecast_status=0 ".$where." and fc.company_id in (0,$_SESSION[company_id])");
	$sTable = "tbl_forecast_user as fc";
	$isJOIN = array('left join branch_mst as bm on bm.branch_id = fc.branch_id','left join users as users on users.user_id = fc.f_user_id');
	$hOrder = "fc.forecast_user_id desc";
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		if(in_array(FORECAST_USER_SLUG_EDIT,$bulkAccessArray)){
			$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'forecast_user_edit/'.$row['forecast_user_id'].'">'.$row["sr"].'</a>';
			$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'forecast_user_edit/'.$row['forecast_user_id'].'">'.$row["forecast_no"].'</a>';
			$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'forecast_user_edit/'.$row['forecast_user_id'].'">'.$row["forecast_date"].'</a>';
			$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'forecast_user_edit/'.$row['forecast_user_id'].'">'.$row["user_name"].'</a>';
			$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'forecast_user_edit/'.$row['forecast_user_id'].'">'.$row["branch_name"].'</a>';
			$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'forecast_user_edit/'.$row['forecast_user_id'].'">'.get_for_target_p_name($dbcon,$row['forecast_type']).'</a>';
		}else{
			$row_data[] = $row['sr'];
			$row_data[] = $row['forecast_no'];
			$row_data[] = $row['forecast_date'];
			$row_data[] = $row['user_name'];
			$row_data[] = $row['branch_name'];
			$row_data[] = get_for_target_p_name($dbcon,$row['forecast_type']);
		}
		if($row['approve_status']==0){
			$row_data[] = '<label class="btn btn-xs btn-success">Approved</label>';
		}else if($row['approve_status']==1){
			$row_data[] = '<label class="btn btn-xs btn-warning">Pending</label>';
		}else{
			$row_data[] = '<label class="btn btn-xs btn-danger">Disapproved</label>';
		}
		$edit_btn='';$delete_btn='';$copy_btn='';$approve_btn='';$print_btn='';

		if(in_array(FORECAST_USER_SLUG_EDIT,$bulkAccessArray)){
			$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'forecast_user_edit/'.$row['forecast_user_id'].'"><i class="fa fa-pencil"></i></a>';
		}
		if(in_array(FORECAST_USER_SLUG_DELETE,$bulkAccessArray)){
			$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_forecast('.$row['forecast_user_id'].')"><i class="fa fa-trash-o"></i></button>';
		}

		$copy_btn='<a class="btn btn-xs btn-primary" data-original-title="Copy Forecast" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'forecast_user_copy/'.$row['forecast_user_id'].'"><i class="fa fa-clone"></i></a>';

		$approve_btn='<a class="btn btn-xs btn-warning" data-original-title="Approve Forecast" data-toggle="tooltip" data-placement="top" onclick="open_approve_modal('.$row['forecast_user_id'].',\''.$row['forecast_no'].'\')"><i class="fa fa-exclamation-triangle"></i></a>';
		
		if(in_array(FORECAST_USER_SLUG_PRINT,$bulkAccessArray)){
			$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
			$rels=mysqli_fetch_assoc($menusql);
			$menu_show_permissions = explode(",",$rels['print_permission']);
			$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 20 AND approve_status = 1 AND status = 0 ORDER BY priority");
			while($res = mysqli_fetch_assoc($sql)){
				if(in_array($res['id'],$menu_show_permissions)) {
					$print_btn.='<a class="btn btn-xs btn-success" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['forecast_user_id'].'?'.time().'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';	
				}
			}
		}

		$row_data[] = $edit_btn.' '.$delete_btn.' '.$approve_btn.' '.$print_btn.' '.$copy_btn; 
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {
	
	$info['forecast_no']		= load_common_no($dbcon,49);
	update_common_no($dbcon,49);
	$info['forecast_date']		= date("Y-m-d", strtotime($POST['forecast_date']));
	$info['financial_year_id']	= $POST['financial_year_id'];
	$info['forecast_type']		= $POST['forecast_type'];
	$info['remark']				= $_POST['remark'];
	$info['branch_id']			= $POST['branch_id'];
	$info['forecast_base']		= $POST['forecast_base'];
	$info['f_user_id']			= $POST['f_user_id'];

	$info['cdate']				= date("Y-m-d H:i:s");
	$info['user_id']			= $_SESSION['user_id'];
	$info['company_id']			= $_SESSION['company_id'];
	$info['approve_status']		= 1;


	$inserid=add_record('tbl_forecast_user', $info, $dbcon);

	if($inserid){
		$infotrn['status'] = 0;
		$infotrn['forecast_usertable_id'] = $inserid;

		$updateid=update_record('tbl_forecast_user_trn', $infotrn,"user_id=".$_SESSION['user_id']." AND status = 3 AND company_id = ".$_SESSION['company_id'], $dbcon, $branch_id);
	}

	if($inserid){
		$resp['msg']='1';
	}
	else{
		$resp['msg']='0';
	}

	echo json_encode($resp);
}
else if(strtolower($POST['mode']) == "edit") {

	// $info['forecast_no']		= $POST['forecast_no'];
	// $info['forecast_date']		= date("Y-m-d", strtotime($POST['forecast_date']));
	// $info['financial_year_id']	= $POST['financial_year_id'];
	// $info['forecast_type']		= $POST['forecast_type'];
	$info['remark']			= $_POST['remark'];
	$info['branch_id']		= $POST['branch_id'];
	$info['forecast_base']	= $POST['forecast_base'];
	$info['f_user_id']		= $POST['f_user_id'];
	$info['user_id']		= $_SESSION['user_id'];
	$info['company_id']		= $_SESSION['company_id'];

	$updateid=update_record('tbl_forecast_user', $info,"forecast_user_id=".$POST['eid'] , $dbcon);

	$infotrn['f_user_id'] = $POST['f_user_id'];

	$updatesids=update_record('tbl_forecast_user_trn', $infotrn,"forecast_usertable_id=".$POST['eid'], $dbcon, $branch_id);

	if($updateid){
		$resp['msg']='2';
	}else{
		$resp['msg']='2';
	}

	echo json_encode($resp);
}
else if(strtolower($POST['mode']) == "delete") {

	$info['forecast_status']='2';
	$updateid=update_record('tbl_forecast_user', $info,"forecast_user_id=".$POST['forecast_id'] , $dbcon);

	$infotrn['status']='2';
	$deleteids=update_record('tbl_forecast_user_trn',$infotrn,"forecast_usertable_id=".$POST['forecast_id'], $dbcon);

	if($updateid){
		echo 1;
	}
	else{
		echo 0;
	}
}
else if(strtolower($POST['mode']) == "get_branchwise_user") {
	if($_SESSION['user_type']==2){
		$resp['html_resp']=get_users_typewise($dbcon,'','');
	}else{
		$resp['html_resp']=get_users_typewise($dbcon,'',' AND branch_id ='.$POST['branch_id']);
	}
	echo json_encode($resp);
}
else if(strtolower($POST['mode']) == "add_field"){
	$tr = $dbcon->query("SELECT `forecast_month`,`f_user_id`,`status`,`f_product`,`financial_year_id` FROM `tbl_forecast_user_trn` WHERE `forecast_month` = '".$POST['forecast_month']."' AND `f_product` = '".$POST['f_product']."' and f_user_id = '".$POST['f_user_id']."' and financial_year_id = '".$POST['financial_year_id']."' and  status=0 and forecast_usertable_id!=".$POST['eid']." AND forecast_user_trn_id !=".$POST['edit_id']);
	if($tr->num_rows > 0){
		echo "-1";
			// echo "SELECT `forecast_month`,`f_user_id`,`status`,`f_product`,`financial_year_id` FROM `tbl_forecast_user_trn` WHERE `forecast_month` = '".$POST['forecast_month']."' AND `f_product` = '".$POST['f_product']."' and f_user_id = '".$POST['f_user_id']."' and financial_year_id = '".$POST['financial_year_id']."' and  status=0 and company_id=".$_SESSION['company_id']." AND forecast_user_trn_id !=".$POST['edit_id'];
	} else {
		$financial_year_data = getFinacialyear_data_by_id($dbcon, $POST['financial_year_id']);
		$monthsname=get_for_period_id($dbcon,$POST['forecast_month']);
		$monthsname = explode(" - ",$monthsname['f_period_name']);
		$smonth = date("m", strtotime($monthsname[0]));
		$emonth = (!empty($monthsname[1])) ? date("m", strtotime($monthsname[1])) : date("m", strtotime($monthsname[0]));
		$startenddate = getMonthsInRange($financial_year_data['financial_start_date'], $financial_year_data['financial_end_date'],$smonth,$emonth,$financial_year_data['finance_year_type']);

		$info['branch_id'] = $POST['branch_id'];
		$info['f_user_id'] = $POST['f_user_id'];
		$info['f_product'] = $POST['f_product'];
		$info['forecast_month'] = $POST['forecast_month'];
		$info['target_qty'] = $POST['target_qty'];
		$info['target_amount'] = $POST['target_amount'];
		$info['financial_year_id'] = $POST['financial_year_id'];
		$info['forecast_base'] = $POST['forecast_base'];
		$info['forecast_type'] = $POST['forecast_type'];
		if($POST['forecast_type']==1){
			$info['forecast_start_date'] = $startenddate[0]['s_date'];
			$info['forecast_end_date'] = $startenddate[1]['e_date'];
		}else if($POST['forecast_type']==2){
			$info['forecast_start_date'] = $startenddate[0];
			$info['forecast_end_date'] = $startenddate[1];
		}else if($POST['forecast_type']==3){
			$info['forecast_start_date'] = $startenddate[0];
			$info['forecast_end_date'] = $startenddate[1];
		}else{
			$info['forecast_start_date'] = $financial_year_data['financial_start_date'];
			$info['forecast_end_date'] = $financial_year_data['financial_end_date'];
		}
		$info['user_id'] = $_SESSION['user_id'];
		$info['company_id'] = $_SESSION['company_id'];
		if(empty($POST['eid'])){
			$info['cdate'] = date("Y-m-d h:i:s");
			$info['status'] = 3;
		}else{
			$info['cdate'] = date("Y-m-d h:i:s");
			$info['forecast_usertable_id'] = $POST['eid'];
			$info['status'] = 0;
		}
		// echo "<pre>";print_r($info);die();
		if(empty($POST['edit_id'])){
			$insertid=add_record('tbl_forecast_user_trn', $info, $dbcon);
		}else{
			$updateid=update_record('tbl_forecast_user_trn', $info,"forecast_user_trn_id=".$POST['edit_id'] , $dbcon);
			$insertid = $POST['edit_id'];
		}
		if($insertid){
			echo "1";
		}else{
			echo "0";
		}
	}
}
else if(strtolower($POST['mode']) == "show_data"){
	$str='';$left_join='';
	$company_data = get_company_data($dbcon,$_SESSION['company_id']);
	$companyConfiguration=getCompanyConfiguration($dbcon);
	$str.='<table class="display table table-bordered table-striped" style="width:100%;">
	<tr>
	<th width="5%" class="text-center">Month</th>';
	if($companyConfiguration['forecast_base']==3){
		$variable = ', pro.product_name as product_name';
		$left_join=' left join product_mst as pro on pro.product_id = futrn.f_product';
		$str.='<th width="10%" class="text-center">Product Category</th>';
	}
	if($companyConfiguration['forecast_base']==2){
		$variable = ', cat.cat_name as product_name';
		$left_join=' left join tbl_category as cat on cat.cat_id = futrn.f_product';
		$str.='<th width="10%" class="text-center">Product Name</th>';
	}
	$str.='<th width="12%" class="text-center">Amount</th>
	<th width="10%" class="text-center">Quantity</th>
	<th width="10%" class="text-center">Action</th>				  
	</tr>
	<tbody>';
	$where = '';
	if(!empty($POST['forecast_user_id'])){
		$where = " AND futrn.forecast_usertable_id = ".$POST['forecast_user_id']." AND futrn.status = 0";
	}else{
		$where = " AND futrn.user_id = ".$_SESSION['user_id']." AND futrn.status = 3";
	}
	$query = $dbcon->query("SELECT futrn.*, fpm.f_period_name ".$variable." FROM tbl_forecast_user_trn AS futrn LEFT JOIN forecast_period_mst as fpm ON futrn.forecast_month = fpm.f_period_id ".$left_join." WHERE futrn.company_id = ".$_SESSION['company_id'].$where);
	if(brp_mysqli_num_rows($query)>0){
		while($res = brp_mysqli_fetch_assoc($query)){
			$str.='<tr>
			<td class="text-center">'.$res['f_period_name'].'</td>';
			if($companyConfiguration['forecast_base']==3 || $companyConfiguration['forecast_base']==2){
				$str.='<td class="text-center">'.$res['product_name'].'</td>';
			}
			$str.='<td class="text-center">'.$res['target_amount'].'</td>
			<td class="text-center">'.$res['target_qty'].'</td>
			<td class="text-center"><button type="button" class="btn btn-xs btn-warning" onclick="edit_trn_datas('.$res['forecast_user_trn_id'].')"><i class="fa fa-pencil"></i></button>&nbsp;&nbsp;<button type="button" class="btn btn-xs btn-danger" onclick="delete_trn_datas('.$res['forecast_user_trn_id'].')"><i class="fa fa-trash-o"></i></button></td>
			</tr>';
		}
	}else{
		$str.='<tr>
		<td colspan="4" class="text-center">No Data Found!!</td>
		</tr>';
	}
	$str.='</tbody>
	</table>';
	echo $str;
}
else if(strtolower($POST['mode']) == "edit_trn_datas"){
	$q = $dbcon -> query("SELECT trn.* FROM tbl_forecast_user_trn as trn WHERE trn.forecast_user_trn_id = ".$POST['forecast_user_trn_id']);
	$r = $q->fetch_assoc();
	echo json_encode($r);
}
else if(strtolower($POST['mode']) == "load_f_period") {
	if($POST['f_by_id']==1){
		$f_by_id = 2;
	}else{
		$f_by_id = 1;
	}
	$resp['html_resp']=get_for_period($dbcon,$f_by_id,$POST['forecast_type'],"");
	echo json_encode($resp);
}
else if(strtolower($POST['mode']) == "copy_forecast"){
	$qry = $dbcon->query("INSERT INTO `tbl_forecast_user_trn` (`forecast_base`,`forecast_month`,`forecast_start_date`,`forecast_end_date`,`f_user_id`,`target_amount`,`target_qty`,`f_product`,`cdate`,`branch_id`,`status`,`company_id`,`financial_year_id`,`user_id`,`forecast_type`) SELECT `forecast_base`,`forecast_month`,`forecast_start_date`,`forecast_end_date`,`f_user_id`,`target_amount`,`target_qty`,`f_product`,".date('Y-m-d h:i:s').",`branch_id`,3,".$_SESSION['company_id'].",`financial_year_id`,".$_SESSION['user_id'].",`forecast_type` FROM tbl_forecast_user_trn WHERE status = 0 AND forecast_usertable_id = ".$POST['forecast_user_id']);
	echo "1";
}
else if(strtolower($POST['mode']) == "load_forecast_dtl") {
	$qt_qry="SELECT f.*, users.user_name, bm.branch_name FROM tbl_forecast_user AS f LEFT JOIN users AS users ON users.user_id = f.f_user_id LEFT JOIN branch_mst AS bm ON bm.branch_id = f.branch_id WHERE f.forecast_user_id = ".$POST['forecast_user_id'];
	$qt_rel=mysqli_fetch_assoc($dbcon->query($qt_qry));

		//Party PO Details Table View
	$str='';$stri='';
	$str.='<div class="form-group">
	<table class="display table table-bordered">
	<tr>
	<td><strong>Forecast No:</strong> '.$qt_rel['forecast_no'].'</td>
	<td><strong>Forecast Date:</strong> '.date("d-M-Y",strtotime($qt_rel["forecast_date"])).'</td>
	<td><strong>Forecast Type:</strong> '.get_for_target_p_name($dbcon,$qt_rel['forecast_type']).'</td>
	</tr>
	<tr>
	<td colspan="2"><strong>User Name:</strong> '.$qt_rel['user_name'].'</td>
	<td><strong>Branch Name:</strong> '.$qt_rel['branch_name'].'</td>
	</tr>
	</table></div>
	<hr/>';
	$qt_rel['mod_forecast_div_sec'] = $str;
	$qt_rel['mod_forecast_pro_div_sec'] = $stri;
	echo json_encode($qt_rel);
}
else if(strtolower($POST['mode']) == "load_forecast_hist_datatable") {

	$where='';
	$where.="  and log.forecast_usertable_id=".$POST['forecast_user_id'];

	$appData = array();
	$i=1;
	$aColumns = array('log.forecast_log_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
	$sIndexColumn = "log.forecast_log_id";
	$isWhere = array("log.status=0 ".$where);
	$sTable = "tbl_forecast_user_approve_log as log";			
	$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
	$hOrder = "log.forecast_log_id desc";
	include($include.'pagging.php');
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['user_name'];

		if($row['approve_status']=='1'){
			$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
		}
		else{
			$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Reject</div>';
		}
		$delete_btn = '';
			// if(in_array(FORECAST_USER_SLUG_DELETE,$bulkAccessArray) && $row['sr']==1){
			// if($row['sr']==1){
			// $delete_btn = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_approve_log('.$POST['forecast_user_id'].','.$row['forecast_log_id'].','.$row['approve_status'].')"><i class="fa fa-trash-o"></i></button>';
			// }

		$row_data[] = nl2br($row['approve_remark']);
		$row_data[] = date("d-M-Y h:i A",strtotime($row['cdate']));
		$row_data[] = $delete_btn;

		$appData[] = $row_data;
		$id++;
		//print_r($row_data);
	}
	$output['aaData'] = $appData;
		//print_r($output);
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add_forecast_apprv_hist") {

	$info1['approve_remark']	= $_POST['approve_remark'];
	$info1['approve_status']	= $POST['approve_status'];
	$info1['forecast_usertable_id']		= $POST['forecast_user_id'];
	$info1['user_id']			= $_SESSION['user_id'];
	$info1['company_id']		= $_SESSION['company_id'];
	$info1['cdate']		= date("Y-m-d h:i:s");
	$inserid=add_record("tbl_forecast_user_approve_log", $info1, $dbcon);

	if($POST['approve_status']=='1'){
		$infoso['approve_status']	= 0;
	}
	else{
		$infoso['approve_status']	= 2;
	}

	$updateid=update_record('tbl_forecast_user', $infoso,"forecast_user_id=".$POST['forecast_user_id'] , $dbcon);

	if($POST['approve_status'] ==1){	
		$arr['msg']="1";
	}else{
		$arr['msg']="0";
	}
	echo json_encode($arr);
}
function getMonthsInRange($startDate, $endDate, $smonth, $emonth,$ftype)
{
	$months = array();
	$data = array();

	while (strtotime($startDate) <= strtotime($endDate)) {
		$months[] = array(
			's_date' => date('Y-m-01', strtotime($startDate)),
			'e_date' => date('Y-m-t', strtotime($startDate)),
			'month' => date('m', strtotime($startDate)),
		);
		$startDate = date('01 M Y', strtotime($startDate . '+ 1 month'));
        // Set date to 1 so that new month is returned as the month changes.
	}
	if($ftype=='1'){
		if($smonth=='01'){
			array_push($data,$months['9']);
		}else if($smonth=='02'){
			array_push($data,$months['10']);
		}else if($smonth=='03'){
			array_push($data,$months['11']);
		}else if($smonth=='04'){
			array_push($data,$months['0']);
		}else if($smonth=='05'){
			array_push($data,$months['1']);
		}else if($smonth=='06'){
			array_push($data,$months['2']);
		}else if($smonth=='07'){
			array_push($data,$months['3']);
		}else if($smonth=='08'){
			array_push($data,$months['4']);
		}else if($smonth=='09'){
			array_push($data,$months['5']);
		}else if($smonth=='10'){
			array_push($data,$months['6']);
		}else if($smonth=='11'){
			array_push($data,$months['7']);
		}else if($smonth=='12'){
			array_push($data,$months['8']);
		}
		if($emonth=='01'){
			array_push($data,$months['9']);
		}else if($emonth=='02'){
			array_push($data,$months['10']);
		}else if($emonth=='03'){
			array_push($data,$months['11']);
		}else if($emonth=='04'){
			array_push($data,$months['0']);
		}else if($emonth=='05'){
			array_push($data,$months['1']);
		}else if($emonth=='06'){
			array_push($data,$months['2']);
		}else if($emonth=='07'){
			array_push($data,$months['3']);
		}else if($emonth=='08'){
			array_push($data,$months['4']);
		}else if($emonth=='09'){
			array_push($data,$months['5']);
		}else if($emonth=='10'){
			array_push($data,$months['6']);
		}else if($emonth=='11'){
			array_push($data,$months['7']);
		}else if($emonth=='12'){
			array_push($data,$months['8']);
		}
	}else{
		if($smonth=='01'){
			array_push($data,$months['0']);
		}else if($smonth=='02'){
			array_push($data,$months['1']);
		}else if($smonth=='03'){
			array_push($data,$months['2']);
		}else if($smonth=='04'){
			array_push($data,$months['3']);
		}else if($smonth=='05'){
			array_push($data,$months['4']);
		}else if($smonth=='06'){
			array_push($data,$months['5']);
		}else if($smonth=='07'){
			array_push($data,$months['6']);
		}else if($smonth=='08'){
			array_push($data,$months['7']);
		}else if($smonth=='09'){
			array_push($data,$months['8']);
		}else if($smonth=='10'){
			array_push($data,$months['9']);
		}else if($smonth=='11'){
			array_push($data,$months['10']);
		}else if($smonth=='12'){
			array_push($data,$months['11']);
		}
		if($emonth=='01'){
			array_push($data,$months['0']);
		}else if($emonth=='02'){
			array_push($data,$months['1']);
		}else if($emonth=='03'){
			array_push($data,$months['2']);
		}else if($emonth=='04'){
			array_push($data,$months['3']);
		}else if($emonth=='05'){
			array_push($data,$months['4']);
		}else if($emonth=='06'){
			array_push($data,$months['5']);
		}else if($emonth=='07'){
			array_push($data,$months['6']);
		}else if($emonth=='08'){
			array_push($data,$months['7']);
		}else if($emonth=='09'){
			array_push($data,$months['8']);
		}else if($emonth=='10'){
			array_push($data,$months['9']);
		}else if($emonth=='11'){
			array_push($data,$months['10']);
		}else if($emonth=='12'){
			array_push($data,$months['11']);
		}
	}

	return $data;
}
?>	