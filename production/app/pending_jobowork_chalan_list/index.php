<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');

//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
//{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
//	{
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
$company_config = getCompanyConfiguration($dbcon);		

if(strtolower($POST['mode']) == "fetch") {

	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];

	if($company_config['po_work_order_wise'] == 1){
		$pera=",GROUP_CONCAT(po_req_no) AS workorder_no,GROUP_CONCAT(res.job_card_no) AS job_card_no";
		$left="left join tbl_request_product as res on res.rp_id=strn.rp_id  left join tbl_set_main_process as setm on setm.sp_id=res.sp_id ";
	}else{
		$pera=",GROUP_CONCAT(po_req_no) AS workorder_no,GROUP_CONCAT(res.job_card_no) AS job_card_no";
		$left="left join tbl_request_product as res on res.rp_id=strn.rp_id  left join tbl_set_main_process as setm on setm.sp_id=res.sp_id ";
	}

	$where='';

	if(isset($POST['jobwork_status']) && $POST['jobwork_status']==0){
		$where.=" job.job_work_type = 2 and trn.job_work_trn_status = 1 and strn.job_work_sub_trn_status = 0 and job.grn_complete_status = 0 and job.job_work_status = 0 and job.release_status = 1 and job.chalan_status = 0 and job.company_id=".$_SESSION['company_id'];
	}else{
		$where.=" job.job_work_type = 2 and trn.job_work_trn_status = 0 and strn.job_work_sub_trn_status = 0 and job.grn_complete_status in (0,1) and job.job_work_status = 0 and job.release_status = 1 and job.chalan_status = 1 and job.company_id=".$_SESSION['company_id'];
	}

	$appData = array();
	$i=1;
	$aColumns = array('job.job_work_id','job.chalan_no','job.job_work_no','job.job_work_date','l.l_name','job.vehicle_no','branch.branch_name','job.g_total','strn.job_work_sub_trn_id','strn.job_work_trn_id','strn.product_con_qty'.$pera);
	$sIndexColumn = "job.job_work_id";
	$isWhere = array($where);
	// $sTable = " tbl_job_work as job";
	$sTable = "tbl_job_work_sub_trn as strn";
	
	$isJOIN = array('left join tbl_job_work_trn as trn on strn.job_work_trn_id = trn.job_work_trn_id',
	'left join tbl_job_work as job on trn.job_work_id = job.job_work_id','left join branch_mst as branch on branch.branch_id = job.branch_id ','left join tbl_ledger as l on l.l_id=job.vender_id '.$left);
	$hOrder = "job.job_work_id";
	$hGroupby = array("job.job_work_id");
	include($include.'pagging.php');
	// $appData = array();
	$id=1;
			//echo "<pre>"; print_r($sqlReturn);
	foreach($sqlReturn as $row) {
		$row_data = array();
		
		if($company_config['po_work_order_wise'] == 1){
			$row_data[] = $row['workorder_no'];
		}
		$row_data[] = $row['chalan_no'];
		$row_data[] = $row['job_work_no'];
		$row_data[] = date('d M, Y',strtotime($row['job_work_date']));
		$row_data[] = $row['job_card_no'];
		$row_data[] = $row['l_name'];
		$row_data[] = $row['vehicle_no'];
		if($company_config['branch_wise_manage']=='1'){
		$row_data[] = $row['branch_name'];
		}	
		$row_data[] = $row['g_total'];
		$app_btn=''; $print_btn='';
		$app_btn = '<a class="btn btn-xs btn-success" data-original-title="Create Chalan" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'create_jobwork_chalan/'.$row['job_work_id'].'" ><i class="fa fa-plus"></i></a>';

		$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
		$rels=mysqli_fetch_assoc($menusql);
		$menu_show_permissions = explode(",",$rels['print_permission']);
		$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 10 AND approve_status = 1 AND status = 0 ORDER BY priority");
		while($res = brp_mysqli_fetch_assoc($sql)){
			if(in_array($res['id'],$menu_show_permissions)) {
				$print_btn.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['job_work_id'].'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';
			}
		}



		$qry=$dbcon->query("SELECT count(grn_trn_sub_id) as grn_count FROM tbl_grn_sub_trn WHERE status = 0 AND jobwork_id = " . $row['job_work_id']);
		$res1 = brp_mysqli_fetch_assoc($qry);

		if($res1['grn_count'] > 0){
			$edit = '';
		}else{
			$edit = '<button class="btn btn-xs btn-warning" style="margin-right: 5px;" onClick="show_vendor_modal('.$row['job_work_id'].',\''.$row['job_work_no'].'\','.$row['g_total'].')"><i class="fa fa-pencil"></i></button>';
		}

		if($POST['jobwork_status']==1){
			$app_btn='';
		}else{
			$print_btn='';
		}

		$row_data[] = $edit . '  ' .$app_btn.' '.$print_btn;

		$appData[] = $row_data;
		$id++;
			
	}
	
	$output['aaData'] = $appData;
	echo json_encode( $output );
}else if(strtolower($POST['mode']) == "get_jobwork_data") {

	$job_work_id = $POST['job_work_id'];

	$query = "SELECT * from tbl_job_work where job_work_id = " . $job_work_id;
	$result = $dbcon->query($query);
	$row = brp_mysqli_fetch_assoc($result);
	
	$html = ' <div class="row">
				<div class="col-md-12">
					    <div class="form-group">
                           <label class="col-md-4 control-label text-right"> Vendor *</label>
                           <div class="col-md-5">
                              <select class="select2" name="vender_id" id="vender_id" title="Select Vendor">
                                 ' .getcust($dbcon, $row['vender_id']). '
                              </select>
                           </div>
                           <div class="col-md-3">
                           		<button class="btn btn-success" onClick="change_vandor()">Update</button>
                           </div>
                     </div>';

	$query1 = "SELECT trn.*,pro.product_name,pr.process_name,u.unit_name,mu.unit_name as mat_unit_name from tbl_job_work_trn as trn 
				LEFT JOIN product_mst AS pro ON pro.product_id = trn.product_id
				LEFT JOIN process_mst AS pr ON pr.process_id = trn.process_id
				left join unit_mst as u on u.unitid=trn.product_base_unit
				left join unit_mst as mu on mu.unitid=trn.material_unit
				where trn.job_work_trn_status != 2 AND trn.job_work_id = " . $job_work_id;
	$result1 = $dbcon->query($query1);
	$cnt = brp_mysqli_num_rows($result1);
	if($cnt > 0){
		$html .= '<div class="col-md-12 mtop20">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
				<tr id="field">
					<th class="text-center" width="5%">#</th>
					<th class="text-center" width="25%">Product Name</th>
					<th class="text-center" width="25%">Process Name</th>
					<th class="text-center" width="15%">QTY</th>
					<th class="text-center" width="15%">Rate</th>
					<th class="text-center" width="10%">Action</th>
				</tr>';
	}
	$i = 1;
	while($row1 = brp_mysqli_fetch_assoc($result1)){

		if(!empty($row1['material_qty'])){
			$qty = $row1['material_qty'];
			$unit_name = $row1['mat_unit_name'];
		}else{
			$qty = $row1['product_base_qty'];
			$unit_name = $row1['unit_name'];
		}
		$html .= '<tr>
			<td>'.$i.'</td>
			<td>'.$row1['product_name'].'</td>
			<td>'.$row1['process_name'].'</td>
			<td>'.$qty. ' <span class="text-success"> '. $unit_name .'</span></td>
			<td> <input  type="number" class="numbersOnly form-control" value="'.$row1['pr_rate'].'" id="pr_rate_'.$row1['job_work_trn_id'].'"></td>
			<td><button class="btn btn-success" onClick="change_rate('.$row1['job_work_trn_id'].','.$qty.','.$row1['pr_rate'].')">Update</button></td>
		</tr>';
		$i++;
	}

	if($cnt > 0){
		$html .= '</table></div>';
	}

	echo $html;
}else if(strtolower($POST['mode']) == "change_vender") {
	$job_work_id = $POST['job_work_id'];
	$vender_id = $POST['vender_id'];

	$info['vender_id'] = $vender_id;

	$updateid=update_record('tbl_job_work', $info,"job_work_id =".$job_work_id, $dbcon);

	echo "1";

}else if(strtolower($POST['mode']) == "change_rate") {
	$job_work_trn_id = $POST['job_work_trn_id'];
	$job_work_id = $POST['job_work_id'];
	$new_rate = $POST['rate'];
	$qty = $POST['qty'];
	$g_total = $POST['g_total'];
	$old_rate = $POST['old_rate'];

	$cal_old_rate = $qty * $old_rate;
	$cal_new_rate = $qty * $new_rate;

	$new_g_total = ($g_total - $cal_old_rate) + $cal_new_rate;


	$info_trn['pr_rate'] = $new_rate;
	$updateid=update_record('tbl_job_work_trn', $info_trn,"job_work_trn_id =".$job_work_trn_id, $dbcon);

	$info['g_total'] = $new_g_total;
	$updateid=update_record('tbl_job_work', $info,"job_work_id =".$job_work_id, $dbcon);

	echo '1';
}

?>