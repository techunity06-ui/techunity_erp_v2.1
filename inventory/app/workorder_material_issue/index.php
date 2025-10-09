<?php

session_start();
$AJAX = true;

include('../../include/urlfileinner.php');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	INVENTORY_WORKORDER_MATERIAL_ISSUE_LIST_SLUG_VIEW,INVENTORY_WORKORDER_MATERIAL_ISSUE_LIST_SLUG_CREATE,INVENTORY_WORKORDER_MATERIAL_ISSUE_LIST_SLUG_UPDATE,INVENTORY_WORKORDER_MATERIAL_ISSUE_LIST_SLUG_DELETE
]);		

$companyConfiguration=getCompanyConfiguration($dbcon);
$bom_pro_search=$companyConfiguration['bom_pro_search'];
$pro_search=explode(",", $bom_pro_search);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
if(brp_strtolower($POST['mode']) == "get_workorder_product") {
	$workorder_id = $POST['workorder_id'];
	$qry = "select rp.*,pro.product_name from tbl_request_product as rp 
			left join product_mst as pro on pro.product_id = rp.rp_pid
			where status = 0 and finish_status = 0 and sp_id = ". $workorder_id. " and (select count(pr_process_id) as s from tbl_wororder_product_process where rp_id = rp.rp_id) > 0";
	
	$rs = $dbcon->query($qry);
	$str = '<option value="">Select Product </option>';
	while ($rel = brp_mysqli_fetch_assoc($rs)) {
		$sel = '';
		$str .= '<option ' . $sel . ' value="' . $rel['rp_id'] . '">' . $rel['product_name'] . '</option>';
	}
	echo $str;
}else if(brp_strtolower($POST['mode']) == "load_material_issue_no") {
	$material_issue_no = load_common_no($dbcon,WORKORDER_MATERIAL_ISSUE_NO);

	echo $material_issue_no;
}else if(brp_strtolower($POST['mode']) == "get_product_id") {
	$rp_id = $POST['rp_id'];
	$qry = "select rp_pid from tbl_request_product where rp_id  = ". $rp_id;
	$rs = $dbcon->query($qry);
	$rel = brp_mysqli_fetch_assoc($rs);
	echo $rel['rp_pid'];
}else if(brp_strtolower($POST['mode']) == "get_workorder_product_process") {
	$rp_id = $POST['rp_id'];
	$qry = "select wp.*,pr.process_name from tbl_wororder_product_process as wp left join process_mst as pr on pr.process_id = wp.process_id where wp.rp_id = ". $rp_id;
	$rs = $dbcon->query($qry);
	$str = '<option value="">Select Product </option>';
	while ($rel = brp_mysqli_fetch_assoc($rs)) {
		$sel = '';
		$str .= '<option ' . $sel . ' value="' . $rel['process_id'] . '">' . $rel['process_name'] . '</option>';
	}
	echo $str;
}else if(brp_strtolower($POST['mode']) == "load_productdata") {
		
		$pid=$POST['eid'];
		
		$sel=$dbcon->query("select m.*,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from product_mst as m 
			left join unit_mst as bunit on bunit.unitid=m.product_base_unit
			left join unit_mst as cunit on cunit.unitid=m.product_conv_unit

		left join mst_material_spec as s on m.product_specification=s.ms_id where product_id='$pid'"); // s.m_type_density,
		$row=brp_mysqli_fetch_assoc($sel);
			echo json_encode($row);
		}
else if(brp_strtolower($POST['mode']) == "add") {
	
	$info['material_issue_no']		= $POST['material_issue_no'];
	$info['material_issue_date']	= date('Y-m-d');
	$info['workorder_id']	= $POST['workorder_id'];
	$info['rp_id']	= $POST['rp_id'];
	$info['product_id']		= $POST['wo_product_id'];
	$info['process_id']	= $POST['process_id'];
	$info['allocate_user_id']	= $POST['allocate_user_id'];
	$info['status']			= 0;
	$info['cdate']				= date("Y-m-d");
	$info['user_id']			= $_SESSION['user_id'];
	$info['company_id']			= $_SESSION['company_id'];
	$info['branch_id']			= $POST['branch_id'];

	$insert_id=add_record('tbl_workorder_direct_material_issue', $info, $dbcon);
	if($insert_id){
		update_common_no($dbcon,WORKORDER_MATERIAL_ISSUE_NO);
		$log_entry=common_log_entry($dbcon,"workorder_direct_material_issue_add",1,"tbl_workorder_direct_material_issue",$insert_id);

		$update['material_issue_id'] = $insert_id;
		$update['status'] = 0;
		$update_id=update_record('tbl_workorder_direct_material_issue_trn',$update, "status = 3", $dbcon);
		$arr['msg'] = '1';
	}else{
		$arr['msg'] = '0';
	}
	echo json_encode($arr);
}
else if(brp_strtolower($POST['mode']) == "fieldadd") {
	
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	
	$product_id = $POST['product_id'];
	$info['product_id']	= $POST['product_id'];
	$info['base_qty']	= $POST['product_base_qty'];
	$info['conv_qty']	= $POST['product_conv_qty'];
	$info['base_unit']	= $POST['product_base_unit'];
	$info['conv_unit']	= $POST['product_conv_unit'];
	$info['cdate']		= date("Y-m-d H:i:s");
	$info['user_id']	= $_SESSION['user_id'];
	$info['company_id']	= $_SESSION['company_id'];
	$info['status']		= 3;

	$info['branch_id']	= $branch_id;

	$arr['msg'] = "";	
	if(!empty($POST['eid'])){
		$update_id=update_record('tbl_workorder_direct_material_issue_trn',$info, "material_issue_trn_id = ".$POST['eid'], $dbcon);
		if($update_id){
			add_update_reserve_stock_trn($dbcon,$POST['eid'],$product_id,$POST['product_base_qty'],$POST['product_base_unit'],$POST['product_conv_unit'],1);
			$arr['msg'] = "update";	
		}
	}else{
		$insert_id=add_record("tbl_workorder_direct_material_issue_trn",$info, $dbcon);	
		if($insert_id){
			$arr['msg'] = "1";
			add_update_reserve_stock_trn($dbcon,$insert_id,$product_id,$POST['product_base_qty'],$POST['product_base_unit'],$POST['product_conv_unit']);
			
		}
	}

	if($arr['msg'] == ""){
		$arr['msg'] ='0';
	}
	echo json_encode($arr);	
}else if(brp_strtolower($POST['mode']) == "delete_tempout_data") {

	$qry = "SELECT * FROM tbl_workorder_direct_material_issue_trn where status = 3";
	$result = $dbcon->query($qry);

	while($row = brp_mysqli_fetch_assoc($result)){
		$q = "select * from tbl_reserve_stock where stock_status = 0 and ref_name='workorder_direct_material_issue' and ref_id = " . $row['material_issue_trn_id'];

		$res = $dbcon->query($q);
		while($r1 = brp_mysqli_fetch_assoc($res)){
			$q1 = "select * from tbl_stock_trn where stock_status = 0 and stock_id = " . $r1['stock_id'];

			$res1 = $dbcon->query($q1);
			$rs1 = brp_mysqli_fetch_assoc($res1);

			$st_info['used_base_stock'] = $rs1['used_base_stock'] - $r1['base_stock'];
			$st_info['used_convert_stock'] = $rs1['used_convert_stock'] - $r1['convert_stock'];
			
			$update_id=update_record('tbl_stock_trn',$st_info, "stock_id = ".$rs1['stock_id'], $dbcon);


			$res_info['stock_status'] = 2;
			$update_id=update_record('tbl_reserve_stock',$res_info, "reserve_id = ".$r1['reserve_id'], $dbcon);
		}
	}
	$info['status'] = 2;
	$update_id=update_record('tbl_workorder_direct_material_issue_trn',$info, "status = 3", $dbcon);
}else if(brp_strtolower($POST['mode']) == "load_tempoutward") {
	 $query="select mst.*,product.product_name,u.unit_name as base_unit_name,cunit.unit_name as conv_unit_name, dr.drawing_number,product.product_icode from tbl_workorder_direct_material_issue_trn as mst 
		left join product_mst as product on product.product_id=mst.product_id 
		left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
		left join unit_mst as u on u.unitid=mst.base_unit
		left join unit_mst as cunit on cunit.unitid=mst.conv_unit
		where mst.status=3";

		$result=$dbcon->query($query);

		echo '<div class="col-md-12">
		<div class="form-group">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field">
		<th class="text-center" width="5%">#</th>
		<th class="text-center" width="18%">Product Name
		</th>
		<th class="text-center hide_act_add" width="5%">Base Unit </th>
		<th class="text-center hide_act_add" width="8%"> Base Qty </th>

		<th class="text-center hide_act_add" width="5%">Convert Unit </th>
		<th class="text-center hide_act_add" width="8%">Convert Qty</th>
		<th class="text-center" width="10%">Action</th>
		</tr>
		<tbody id="fil_product_tbl">';
		if(brp_mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=brp_mysqli_fetch_assoc($result))
			{
				if($rel['drawing_id']!=0){
					$drawing_number = $rel['drawing_number'];
				}else{
					$drawing_number = '0';
				}

				
				$drawing_number = "";
					$item_code = "";
					 if(in_array('drawing',$pro_search)){
				            $drawing_number = " -- (".$rel['drawing_number'].")";
				        }
				        if(in_array('item',$pro_search)){
				            $item_code = " -- (".$rel['product_icode'].")";
				        }	

				echo '<tr id="fieldtr'.$id.'" >

				
				<td style="vertical-align:top;">
				'.$i.'
				</td>
				<td style="vertical-align:top;">
				<a '.$href.' '.$style.' >'.$rel['product_name'].'</a>'. $button. '
				<br/>'.' '.$item_code.' '.$drawing_number.'
				</td>
				
				<td style="vertical-align:top;">
				'.$rel['base_unit_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['base_qty'].'
				</td>
				<td style="vertical-align:top;">
				'.$rel['conv_unit_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['conv_qty'].'
				</td>
				
				<td style="vertical-align:top">
				<button type="button" class="btn btn-round btn-warning btn-xs" data-toggle="tooltip" data-placement="top" title="Edit" onclick="edit_data('.$rel['material_issue_trn_id'].');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>

				<button type="button" class="btn btn-round btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Delete" onclick="delete_data('.$rel['material_issue_trn_id'].');" id="fieldremove'.$i.'"><i class="fa fa-trash"></i></button> </td>
				';

				echo '</tr>';
				$i++;
			}
		}
		else{
			echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
		}
		echo '</tbody></table>			 
		</div>
		</div>	';
}
else if(brp_strtolower($POST['mode'])== "delete_data")
	{
			$material_issue_trn_id = $POST['eid'];
			$info['status'] = 2;
			$update_id=update_record('tbl_workorder_direct_material_issue_trn',$info, "material_issue_trn_id = ".$material_issue_trn_id, $dbcon);

			if($update_id){
				$row['res']="1";
			}else{
				$row['res']="0";	
			}
			
			echo json_encode($row);
		}
else if(brp_strtolower($POST['mode'])== "preedit")
	{
		$query="SELECT mst.*,pro.product_base_qty,pro.product_conv_qty,pro.product_name,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name FROM tbl_workorder_direct_material_issue_trn as mst 
			left join product_mst as pro on mst.product_id=pro.product_id
			left join unit_mst as bunit on bunit.unitid=mst.base_unit
			left join unit_mst as cunit on cunit.unitid=mst.conv_unit
			WHERE material_issue_trn_id = ".$POST['id'];

		$result=$dbcon->query($query);
		$r = brp_mysqli_fetch_assoc($result);
		// var_dump($r);
		echo json_encode($r);
	}
else if(brp_strtolower($POST['mode']) == "fetch") {
	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
	// $branch=$_SESSION['branch_id'];

	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$where='';
	
	$appData = array();
	$i=1;
	$aColumns = array('wm.material_issue_id','wm.material_issue_no','wm.material_issue_no','wm.material_issue_date','smain.po_req_no','product.product_name','pr.process_name','wm.status');
	$sIndexColumn = "material_issue_id";
	$isWhere = array("wm.status!=2 and wm.company_id = ".$_SESSION['company_id']);
	$sTable = "tbl_workorder_direct_material_issue as wm";			
	$isJOIN = array('left join product_mst as product on product.product_id=wm.product_id left join process_mst as pr on pr.process_id=wm.process_id left join tbl_set_main_process as smain on smain.sp_id=wm.workorder_id');
		//$hGroupby = array("bom.bom_product");
	$hOrder = "wm.material_issue_id desc";
	include($include.'pagging.php');
	$appData = array();

	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$material_issue_date=date('d-m-Y',strtotime($row['material_issue_date']));
		$row_data[] = '<a class="" data-original-title="'.$row["material_issue_no"].'" data-toggle="tooltip" data-placement="top" href="#">'.$row["sr"].'</a>';

		$row_data[] = '<a class="" data-original-title="'.$row["material_issue_no"].'" data-toggle="tooltip" data-placement="top" href="#">'.$row["material_issue_no"].'</a>';

		$row_data[] = '<a class="" data-original-title="'.$row["material_issue_no"].'" data-toggle="tooltip" data-placement="top" href="#">'.$material_issue_date.'</a>';
		$row_data[] = '<a class="" data-original-title="'.$row["material_issue_no"].'" data-toggle="tooltip" data-placement="top" href="#">'.$row["po_req_no"].'</a>';

		$row_data[] = '<a class="" data-original-title="'.$row["material_issue_no"].'" data-toggle="tooltip" data-placement="top" href="#">'.$row["product_name"].'</a>';

		$row_data[] = '<a class="" data-original-title="'.$row["material_issue_no"].'" data-toggle="tooltip" data-placement="top" href="#">'.$row["process_name"].'</a>';

		if($row['status'] == '1'){
		  		$row_data[] ='<button class="btn btn-xs btn-success" data-original-title="Approved" data-toggle="tooltip" data-placement="top">Approved</button>';
		  	}else if($row['status'] == '2'){
		  		$row_data[] ='<button class="btn btn-xs btn-danger" data-original-title="Rejected" data-toggle="tooltip" data-placement="top">Rejected</button>';
		  	}
		  	else{
		  		$row_data[] ='<button class="btn btn-xs btn-warning" data-original-title=" Pending" data-toggle="tooltip" data-placement="top"> Pending </button>';
		  	}  
		
		$delete='';$edit='';$apprv_btn='';

				
				if(in_array(INVENTORY_WORKORDER_MATERIAL_ISSUE_LIST_SLUG_DELETE,$bulkAccessArray)){
				 /*$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_bom_costing('.$row['bom_costing_id'].')"><i class="fa fa-trash-o"></i></button>';*/
				}
				
				if(in_array(INVENTORY_WORKORDER_MATERIAL_ISSUE_LIST_SLUG_UPDATE,$bulkAccessArray)){
					/*$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="#"><i class="fa fa-pencil"></i></a>';*/
				}
				// if(in_array(INVENTORY_WORKORDER_MATERIAL_ISSUE_LIST_LIST_SLUG_APPROVE,$bulkAccessArray)){
				if($row['status'] == '0'){
					$apprv_btn='<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Material" data-toggle="tooltip" data-placement="top" onClick="open_approv_model('."'$row[material_issue_id]'".','."'$row[material_issue_no]'".')"><i class="fa fa-exclamation-triangle"></i></button>';
				}
				// }
				
				
				$row_data[] = $edit.' '.$delete.' '.$apprv_btn;

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}else if(strtolower($POST['mode'])=="load_stock_qty")
		{
			$product_id=$POST['product_id'];
			$get_pro_type_qry="select product_type,product_base_unit from product_mst where product_id=".$product_id;
			$get_pro_type_rel=mysqli_fetch_assoc($dbcon->query($get_pro_type_qry));
			

			$product_type_arr = array("0", "1", "2", "3", "4", "5", "6", "7", "9", "-1");
			if (in_array($get_pro_type_rel['product_type'], $product_type_arr)){
				if(!empty($POST['unit_id'])){
					$unit_id=$POST['unit_id'];
				}else{
					$unit_id=$get_pro_type_rel['product_base_unit'];
				}
				$current_stock = get_current_stock_new($dbcon,$product_id,$unit_id);
				$rstock=reserve_stock($dbcon,$product_id,$unit_id,'','','','','','');

				$stock = $current_stock - $rstock;
				echo $stock;
			}
			else{
				echo 0;
			}
			
		}else if(strtolower($POST['mode']) == "load_wo_direct_material_detail") {
			$query = "select wm.material_issue_no, wm.material_issue_date, wm.workorder_id, pr.process_name,pro.product_name,smain.po_req_no,l_name from  tbl_workorder_direct_material_issue as wm
			left join tbl_set_main_process as smain on smain.sp_id = wm.workorder_id
			left join product_mst as pro on pro.product_id = wm.product_id
			left join process_mst as pr on pr.process_id = wm.process_id
			left join tbl_ledger as led on led.l_id=wm.allocate_user_id
			where wm.material_issue_id=".$POST['material_issue_id'];


			$qt_rel=brp_mysqli_fetch_assoc($dbcon->query($query));

		//Party PO Details Table View
			$str='';
			$str.='<div class="form-group">
			<table class="display table table-bordered table-striped">
			<tr>
			<td><strong>Material Issue  No:</strong> '.$qt_rel['material_issue_no'].'</td>
			<td><strong>Material Issue Date:</strong> '.date("d-M-Y",strtotime($qt_rel["material_issue_date"])).'</td>
			</tr>
			<tr>
			<td><strong>Workorder No:</strong> '.$qt_rel['product_name'].'</td>
			<td><strong>Product Name:</strong> '.$qt_rel['product_name'].'</td>
			</tr>
			<tr>
			<td><strong>Process Name:</strong> '.$qt_rel['process_name'].'</td>
			<td><strong>Allocate User:</strong> '.$qt_rel['l_name'].'</td>
			</tr>
			';
			$str.='</table></div>
			<hr/>
			';

			 $query="select mst.*,product.product_name,u.unit_name as base_unit_name,cunit.unit_name as conv_unit_name, dr.drawing_number,product.product_icode from tbl_workorder_direct_material_issue_trn as mst 
		left join product_mst as product on product.product_id=mst.product_id 
		left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
		left join unit_mst as u on u.unitid=mst.base_unit
		left join unit_mst as cunit on cunit.unitid=mst.conv_unit
		where mst.status=0 and mst.material_issue_id = " . $POST['material_issue_id'];

		$result=$dbcon->query($query);

		$str .= '<div class="col-md-12">
		<div class="form-group">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field">
		<th class="text-center" width="5%">#</th>
		<th class="text-center" width="18%">Product Name
		</th>
		<th class="text-center hide_act_add" width="5%">Base Unit </th>
		<th class="text-center hide_act_add" width="8%"> Base Qty </th>

		<th class="text-center hide_act_add" width="5%">Convert Unit </th>
		<th class="text-center hide_act_add" width="8%">Convert Qty</th>
		</tr>
		<tbody id="fil_product_tbl">';
		if(brp_mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=brp_mysqli_fetch_assoc($result))
			{
				if($rel['drawing_id']!=0){
					$drawing_number = $rel['drawing_number'];
				}else{
					$drawing_number = '0';
				}

				
				$drawing_number = "";
					$item_code = "";
					 if(in_array('drawing',$pro_search)){
				            $drawing_number = " -- (".$rel['drawing_number'].")";
				        }
				        if(in_array('item',$pro_search)){
				            $item_code = " -- (".$rel['product_icode'].")";
				        }	

				$str .= '<tr id="fieldtr'.$id.'" >

				
				<td style="vertical-align:top;">
				'.$i.'
				</td>
				<td style="vertical-align:top;">
				'.$rel['product_name']. $button. '
				<br/>'.' '.$item_code.' '.$drawing_number.'
				</td>
				
				<td style="vertical-align:top;">
				'.$rel['base_unit_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['base_qty'].'
				</td>
				<td style="vertical-align:top;">
				'.$rel['conv_unit_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['conv_qty'].'
				</td>
				';

				$str .= '</tr>';
				$i++;
			}
		}
		$str .= '</tbody></table>			 
		</div>
		</div>	';

			$qt_rel['detail_show'] = $str;

			echo json_encode($qt_rel);		
		}
		else if(strtolower($POST['mode']) == "load_wo_direct_hist") {

			$where='';
			$where.="   log.material_issue_id=".$POST['material_issue_id'];

			$appData = array();
			$i=1;
			$aColumns = array('log.material_aprv_log_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.created_at', 'log.user_id');
			$sIndexColumn = "log.material_aprv_log_id";
			$isWhere = array(" ".$where." ");
			$sTable = "tbl_workorder_direct_material_issue_aprv_log as log";			
			$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
			$hOrder = "log.material_aprv_log_id desc";
			include($include.'/pagging.php');
			//echo $sQuery;
			//exit;
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['user_name'];

				if($row['approve_status']=='1'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
				}
				else{
					$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Approve Pending</div>';
				}

				$row_data[] = nl2br($row['approve_remark']);
				$row_data[] = date("d-M-Y h:i A",strtotime($row['created_at']));

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;	
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add_wo_apprv_hist") {
			$info1['approve_remark']	= $POST['approve_remark'];
			$info1['approve_status']	= $POST['approve_status'];
			$info1['material_issue_id']	= $POST['material_issue_id'];
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			
			$inserid=add_record("tbl_workorder_direct_material_issue_aprv_log", $info1, $dbcon);

			$info['status'] = $POST['approve_status'];	
			
			$updateid=update_record("tbl_workorder_direct_material_issue", $info, "material_issue_id=".$POST['material_issue_id'], $dbcon);

			if($POST['approve_status'] == '1'){ //  deduct stock
				$query_perent = "SELECT * FROM `tbl_workorder_direct_material_issue_trn` WHERE status = 0 and  material_issue_id =" . $POST['material_issue_id'];
			$result_perent = $dbcon->query($query_perent);
			while($rel = brp_mysqli_fetch_assoc($result_perent)){
				$request_qty = $rel['base_qty'];
				$product_id = $rel["product_id"];
				$unit_id = $rel["base_unit"];
				$query_god = "SELECT * FROM `tbl_stock_trn` as rpro
							WHERE rpro.stock_status=0 and rpro.stock_flage = 1 and rpro.branch_id=" . $rel["branch_id"] . " and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) AND rpro.product_id=" . $rel["product_id"];
							$result_god = $dbcon->query($query_god);
							while ($rel_god = brp_mysqli_fetch_assoc($result_god)) {
								$stock_qty = $rel_god['base_stock'] - $rel_god['used_base_stock'];
								if($request_qty > 0){
								if ($stock_qty >= $request_qty) {
									$god_uses_stock = $request_qty;
								} else {
									$god_uses_stock = $stock_qty;
								}

								$request_qty = $request_qty - $god_uses_stock;
								
								
									$qty_conv = convert_stock($dbcon, $god_uses_stock, $rel_god['product_id'], "conv_unit");
							
								$stock_date=date("Y-m-d");

								add_stock($dbcon,$product_id,$unit_id,$stock_date,'workorder_direct_material_issue',$rel['material_issue_trn_id'],$rel_god['godown_id'],$god_uses_stock,'2',$rel_god['branch_id'],$rel_god['stock_id'],"","","","",$rel_god['base_rate'],$rel_god['conv_rate']);
								 // add_stock($dbcon,$product_id,$unit_id,$stock_date,$ref_name,$ref_id,$godown_id,$stock_qty,$stock_flag,$branch_id,$perent_id,$reserve_id,$customer_id="",$batch_id="",$batch_no="",$base_rate="",$conv_rate="")
							}
							}
						}
				}

			else{  // add stock

			}

		}

function add_update_reserve_stock_trn($dbcon,$material_issue_trn_id,$product_id,$reserve_qty,$unit_id,$conv_unit,$edit=""){
	if($edit){

		$q = "select * from tbl_reserve_stock where stock_status = 0 and ref_name='workorder_direct_material_issue' and ref_id = " . $material_issue_trn_id;

		$res = $dbcon->query($q);
		while($r1 = brp_mysqli_fetch_assoc($res)){
			$q1 = "select * from tbl_stock_trn where stock_status = 0 and stock_id = " . $r1['stock_id'];

			$res1 = $dbcon->query($q1);
			$rs1 = brp_mysqli_fetch_assoc($res1);

			$st_info['used_base_stock'] = $rs1['used_base_stock'] - $r1['base_stock'];
			$st_info['used_convert_stock'] = $rs1['used_convert_stock'] - $r1['convert_stock'];
			
			$update_id=update_record('tbl_stock_trn',$st_info, "stock_id = ".$rs1['stock_id'], $dbcon);


			$res_info['stock_status'] = 2;
			$update_id=update_record('tbl_reserve_stock',$res_info, "reserve_id = ".$r1['reserve_id'], $dbcon);
		}
	}

	$query_dstock = "select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i	
	where stock_status=0 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and i.product_id=".$product_id;
	$result_dstock=$dbcon->query($query_dstock);
	while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
		$pending_stock=$row_dstock['pending_base_stock'];	
		if($reserve_qty>0){
			if($pending_stock>=$reserve_qty){
				$rqty=$reserve_qty;
				$reserve_qty=$reserve_qty-$reserve_qty;
			}else{
				$rqty=$pending_stock;
				$reserve_qty=$reserve_qty-$pending_stock;
			}

			$type="conv_unit";
			$base_stock=$rqty;
			$con_stock=convert_stock_new($dbcon,$rqty,$product_id,$type);
			
			$info_rese['reserve_date']		= date('Y-m-d');
			$info_rese['product_id']		= $product_id;
			$info_rese['godown_id']			= $row_dstock['godown_id'];
			$info_rese['base_unit']			= $unit_id;
			$info_rese['base_stock']		= $base_stock;
			$info_rese['convert_unit']		= $conv_unit;
			$info_rese['convert_stock']		= $con_stock;
			$info_rese['stock_flage']		= "1";
			$info_rese['request_id']		= "";
			$info_rese['ref_name']			= "workorder_direct_material_issue";
			$info_rese['ref_id']			= $material_issue_trn_id;
			$info_rese['stock_id']			= $row_dstock['stock_id'];

			$info_rese['cdate']					= date("Y-m-d H:i:s");
			$info_rese['user_id']				= $_SESSION['user_id'];
			$info_rese['company_id']			= $_SESSION['company_id'];		
								
			$reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$row_dstock['branch_id']);
		
			
			$used_base_stock=$row_dstock['used_base_stock']+$base_stock;
			$used_convert_stock=$row_dstock['used_convert_stock']+$con_stock;
			
			$info_stock['used_base_stock']		= $used_base_stock;
			$info_stock['used_convert_stock']	= $used_convert_stock;
			
			$updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$row_dstock['stock_id'], $dbcon);
		}
	}
}