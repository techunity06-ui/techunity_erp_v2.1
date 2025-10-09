<?php

session_start();
$AJAX = true;

include('../../include/urlfileinner.php');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	INVENTORY_SO_DEALLOCATE_LIST_SLUG_VIEW,INVENTORY_SO_DEALLOCATE_LIST_SLUG_CREATE,INVENTORY_SO_DEALLOCATE_LIST_SLUG_UPDATE,INVENTORY_SO_DEALLOCATE_LIST_SLUG_DELETE,
	INVENTORY_SO_DEALLOCATE_LIST_SLUG_APPROVE
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
if(brp_strtolower($POST['mode']) == "get_salesorder_product") {
	$sales_order_id = $POST['sales_order_id'];
	$qry = "select IFNULL(SUM(base_stock),0) - IFNULL(deduct_stock,0) as pending_stock, pro.product_name,so_trn.sales_ordertrn_id from tbl_reserve_stock as res left join tbl_sales_ordertrn as so_trn on res.sales_order_trn_id = so_trn.sales_ordertrn_id left join tbl_sales_order as so on so.sales_order_id = so_trn.sales_order_id left join product_mst as pro on pro.product_id = so_trn.product_id left join (select IFNULL(sum(qc.base_stock),0) as deduct_stock,qc.sales_order_trn_id from tbl_reserve_stock as qc where qc.stock_status!=2 and stock_flage=2 group by qc.sales_order_trn_id) as qc on qc.sales_order_trn_id=res.sales_order_trn_id where res.stock_flage = 1 AND res.sales_order_trn_id > 0 AND so_trn.sales_ordertrn_status = 0 and res.stock_status !=2 AND so_trn.invoice_status = 0 and so_trn.sales_order_id = ".$sales_order_id." group by res.sales_order_trn_id having pending_stock > 0";
	
	
	$rs = $dbcon->query($qry);
	$str = '<option value="">Select Product </option>';
	while ($rel = brp_mysqli_fetch_assoc($rs)) {
		$sel = '';
		$str .= '<option ' . $sel . ' value="' . $rel['sales_ordertrn_id'] . '">' . $rel['product_name'] . '</option>';
	}
	echo $str;
}else if(brp_strtolower($POST['mode']) == "load_so_deallocate_no") {
	$material_issue_no = load_common_no($dbcon,SO_DEALLOCATE_NO);
	echo $material_issue_no;
}else if(brp_strtolower($POST['mode']) == "load_productdata") {
	$sales_ordertrn_id=$POST['sales_ordertrn_id'];

	$query = "select so_trn.product_id,so_trn.product_qty,pro.product_base_unit,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name, IFNULL(SUM(base_stock),0) - IFNULL(deduct_stock,0) as pending_stock, pro.product_name,so_trn.sales_ordertrn_id, pro.batch_wise_stock_manage from tbl_reserve_stock as res 
		left join tbl_sales_ordertrn as so_trn on res.sales_order_trn_id = so_trn.sales_ordertrn_id 
		left join tbl_sales_order as so on so.sales_order_id = so_trn.sales_order_id 
		left join product_mst as pro on pro.product_id = so_trn.product_id 
		left join unit_mst as bunit on bunit.unitid=pro.product_base_unit
		left join unit_mst as cunit on cunit.unitid=pro.product_conv_unit
		left join (select IFNULL(sum(qc.base_stock),0) as deduct_stock,qc.sales_order_trn_id from tbl_reserve_stock as qc where qc.stock_status!=2 and stock_flage=2 group by qc.sales_order_trn_id) as qc on qc.sales_order_trn_id=res.sales_order_trn_id where res.stock_flage = 1 AND res.sales_order_trn_id > 0 AND so_trn.sales_ordertrn_status = 0 and res.stock_status !=2 AND so_trn.invoice_status = 0 and res.sales_order_trn_id = ".$sales_ordertrn_id." group by res.sales_order_trn_id having pending_stock > 0";
	$result=$dbcon->query($query);
	$row=brp_mysqli_fetch_assoc($result);


	$tmp_qry = " SELECT IFNULL(sum(de_allocate_qty),0) as temp_qty FROM tbl_so_stock_deallocate_trn WHERE status = 3 AND sales_ordertrn_id = " . $sales_ordertrn_id;
	$tmp_row = brp_mysqli_fetch_assoc($dbcon->query($tmp_qry));

	$row['pending_stock'] = $row['pending_stock'] - $tmp_row['temp_qty'];
	echo json_encode($row);
}
else if(brp_strtolower($POST['mode']) == "add") {
	$info['de_allo_no']		= $POST['so_deallocate_no'];
	$info['de_allo_date']	= date('Y-m-d',strtotime($POST['so_deallocate_date']));
	$info['remark']	= $POST['remark'];
	$info['status']			= 0;
	$info['approve_status']		= 0;
	$info['cdate']				= date("Y-m-d H:i:s");
	$info['user_id']			= $_SESSION['user_id'];
	$info['company_id']			= $_SESSION['company_id'];
	
	$insert_id=add_record('tbl_so_stock_deallocate', $info, $dbcon);
	if($insert_id){
		update_common_no($dbcon,SO_DEALLOCATE_NO);

		$log_entry=common_log_entry($dbcon,"so_stock_deallocate_add",1,"tbl_so_stock_deallocate",$insert_id);

		$update['de_allo_id'] = $insert_id;
		$update['status'] = 0;
		$update_id=update_record('tbl_so_stock_deallocate_trn',$update, "status = 3", $dbcon);
		$arr['msg'] = '1';
	}else{
		$arr['msg'] = '0';
	}
	echo json_encode($arr);
}
else if(brp_strtolower($POST['mode']) == "fieldadd") {
	
	$info['product_id']	= $POST['product_id'];
	$info['sales_order_id']	= $POST['sales_order_id'];
	$info['sales_ordertrn_id']	= $POST['sales_ordertrn_id'];
	$info['de_allocate_qty']	= $POST['de_allocate_qty'];
	$info['unit_id']	= $POST['unit_id'];
	$info['cdate']		= date("Y-m-d H:i:s");
	$info['user_id']	= $_SESSION['user_id'];
	$info['company_id']	= $_SESSION['company_id'];
	$info['status']		= 3;
	
	$insert_id=add_record("tbl_so_stock_deallocate_trn",$info, $dbcon);	
		if($insert_id){

		$sel_itrn = $dbcon->query("SELECT * FROM tbl_so_deallocate_batch_stock_tmp where status=0 and sales_order_trn_id = " .$POST['sales_ordertrn_id']." and product_id=".$POST['product_id']);
			
			if($sel_itrn->num_rows > 0) { 
				$infobatch['de_allo_trn_id']= $insert_id;
				$infobatch['status']= 1;
				
				while($r_itrn=brp_mysqli_fetch_array($sel_itrn))
				{
					
					$updateinvoicetrnid=update_record('tbl_so_deallocate_batch_stock_tmp', $infobatch,"status=0 and sales_order_trn_id = " .$POST['sales_ordertrn_id']." and ".$r_itrn['product_id']."=".$POST['product_id'] , $dbcon);
				}
			}
			$arr['msg'] = "1";		
		}else{
			$arr['msg'] = "0";		
		}
	
	echo json_encode($arr);	
}else if(brp_strtolower($POST['mode']) == "delete_tempout_data") {
	$info['status'] = 2;
	$update_id=update_record('tbl_so_stock_deallocate_trn',$info, "status = 3", $dbcon);
}else if(brp_strtolower($POST['mode']) == "load_tempoutward") {
	 $query="select mst.de_allo_trn_id,mst.de_allocate_qty, product.product_name, u.unit_name,so.sales_order_no, product.product_icode from tbl_so_stock_deallocate_trn as mst 
	 	left join tbl_sales_order as so on so.sales_order_id = mst.sales_order_id 
		left join product_mst as product on product.product_id=mst.product_id 
		left join unit_mst as u on u.unitid=mst.unit_id
		where mst.status=3";

		$result=$dbcon->query($query);

		$temp_cnt = brp_mysqli_num_rows($result);

		echo '<div class="col-md-12">
		<div class="form-group">

		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field">
		<th class="text-center" width="5%">#</th>
		<th class="text-center" width="18%">Salesorder No
		</th>
		<th class="text-center" width="18%">Product Name
		</th>
		<th class="text-center hide_act_add" width="8%"> Deallocate Qty </th>
		<th class="text-center hide_act_add" width="5%"> Unit </th>
		<th class="text-center" width="10%">Action</th>
		</tr>
		<tbody id="fil_product_tbl">';

		if($temp_cnt > 0)
		{
			$i=1;
			while($rel=brp_mysqli_fetch_assoc($result))
			{
				echo '<tr id="fieldtr'.$i.'" >

				
				<td style="vertical-align:top;">
				'.$i.'
				</td>
				<td style="vertical-align:top;"> '.$rel['sales_order_no'].' </td>
				<td style="vertical-align:top;"> '.$rel['product_name'].' </td>
				<td style="vertical-align:top;"> '.$rel['de_allocate_qty'].' </td>
				<td style="vertical-align:top;"> '.$rel['unit_name'].' </td>
				
				<td style="vertical-align:top">
				<button type="button" class="btn btn-round btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Delete" onclick="delete_data('.$rel['de_allo_trn_id'].');" id="fieldremove'.$i.'"><i class="fa fa-trash"></i></button> </td>
				';

				echo '</tr>';
				$i++;
			}
		}
		else{
			echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
		}
		echo '</tbody></table>	
		<input type="hidden" id="so_temp_data" name="so_temp_data" value="'.$temp_cnt.'"/>		 
		</div>
		</div>	';
}
else if(brp_strtolower($POST['mode'])== "delete_data")
{
	$de_allo_trn_id = $POST['eid'];
	$info['status'] = 2;
	$update_id=update_record('tbl_so_stock_deallocate_trn',$info, "de_allo_trn_id = ".$de_allo_trn_id, $dbcon);

	if($update_id){
		$row['res']="1";
	}else{
		$row['res']="0";	
	}
	
	echo json_encode($row);
}else if(brp_strtolower($POST['mode'])== "delete_main_data")
{
	$de_allo_id = $POST['eid'];
	$info['status'] = 2;
	$update_id=update_record('tbl_so_stock_deallocate',$info, "de_allo_id = ".$de_allo_id, $dbcon);

	if($update_id){
		$row['res']="1";
	}else{
		$row['res']="0";	
	}
	
	echo json_encode($row);
}

else if(brp_strtolower($POST['mode']) == "fetch") {
	/*$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];*/
	
	$appData = array();
	$i=1;
	$aColumns = array('so_de.de_allo_id','so_de.de_allo_no','so_de.de_allo_date','so_de.status','so_de.approve_status','users.user_name');
	$sIndexColumn = "de_allo_id";
	$isWhere = array("so_de.status != 2 and so_de.company_id = ".$_SESSION['company_id']);
	$sTable = "tbl_so_stock_deallocate as so_de";			
	$isJOIN = array('left join users as users on users.user_id=so_de.user_id');
		//$hGroupby = array("bom.bom_product");
	$hOrder = "so_de.de_allo_id desc";
	include($include.'pagging.php');
	$appData = array();

	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$de_allo_date=date('d-m-Y',strtotime($row['de_allo_date']));
		$row_data[] = $row["sr"];
		$row_data[] = $row["de_allo_no"];
		$row_data[] = $de_allo_date;
		$row_data[] = $row["user_name"];

		if($row['approve_status'] == '1'){
		  	$row_data[] ='<button class="btn btn-xs btn-success" data-original-title="Approved" data-toggle="tooltip" data-placement="top">Approved</button>';
	  	}else if($row['status'] == '3'){
	  		$row_data[] ='<button class="btn btn-xs btn-danger" data-original-title="Rejected" data-toggle="tooltip" data-placement="top">Rejected</button>';
	  	}
	  	else{
	  		$row_data[] ='<button class="btn btn-xs btn-warning" data-original-title=" Pending" data-toggle="tooltip" data-placement="top"> Pending </button>';
	  	}  
		
		$delete='';
		$apprv_btn='';

		$print_btn = '';	
		  		$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");	
				$rels=mysqli_fetch_assoc($menusql);	
				$menu_show_permissions = explode(",",$rels['print_permission']);	
				$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = ".SALESORDER_DEALLOCATION_PRINT." AND approve_status = 1 AND status = 0 ORDER BY priority");	
				while($res = mysqli_fetch_assoc($sql)){	
					if(in_array($res['id'],$menu_show_permissions)) {	
						$print_btn.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['de_allo_id'].'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';	
					}	
				}

				
		if(in_array(INVENTORY_SO_DEALLOCATE_LIST_SLUG_DELETE,$bulkAccessArray)){
		 $delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_deallocate_data('.$row['de_allo_id'].')"><i class="fa fa-trash-o"></i></button>';
		}
		
		if(in_array(INVENTORY_SO_DEALLOCATE_LIST_SLUG_APPROVE,$bulkAccessArray)){
			if($row['status'] == '0'){
				$apprv_btn='<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Material" data-toggle="tooltip" data-placement="top" onClick="open_approv_model('."'$row[de_allo_id]'".','."'$row[de_allo_no]'".')"><i class="fa fa-exclamation-triangle"></i></button>';
			}
		}

		if($row['approve_status'] != '0'){
			$delete='';
			$apprv_btn='';
		}
				
				
		$row_data[] = $edit.' '.$delete.' '.$apprv_btn. ' '.$print_btn;	

		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode'])== "get_batch_qty"){
	$stock_id = $POST['batch_no'];
	$sales_ordertrn_id = $POST['sales_ordertrn_id'];
	$stock=0;
	$rstock=0;

	 $query = "select IFNULL(SUM(res.base_stock),0) - IFNULL(deduct_stock,0) as pending_stock from tbl_reserve_stock as res 
		left join tbl_stock_trn as st on st.stock_id=res.stock_id
		left join (select IFNULL(sum(qc.base_stock),0) as deduct_stock,qc.sales_order_trn_id from tbl_reserve_stock as qc where qc.stock_status!=2 and stock_flage=2 group by qc.sales_order_trn_id) as qc on qc.sales_order_trn_id=res.sales_order_trn_id
		where res.stock_flage = 1 AND res.stock_status !=2 AND res.sales_order_trn_id = ".$sales_ordertrn_id." AND  res.reserve_id in (".$stock_id.") having pending_stock > 0";
	$row = brp_mysqli_fetch_assoc($dbcon->query($query));	

	$tmp_qry = " SELECT IFNULL(sum(de_allocate_qty),0) as temp_qty FROM tbl_so_stock_deallocate_trn WHERE status = 3 AND sales_ordertrn_id = " . $sales_ordertrn_id;
	$tmp_row = brp_mysqli_fetch_assoc($dbcon->query($tmp_qry));

	$stock = $row['pending_stock'] - $tmp_row['temp_qty'];
	echo $stock;
}else if(strtolower($POST['mode'])== "batch_stock_model_open"){
	$sales_ordertrn_id = $POST['sales_ordertrn_id'];
	 $query = "select st.batch_no,group_concat(st.stock_id) as stock_id,group_concat(res.reserve_id) as reserve_id, IFNULL(SUM(res.base_stock),0) - IFNULL(deduct_stock,0) as pending_stock from tbl_reserve_stock as res 
		left join tbl_stock_trn as st on st.stock_id=res.stock_id
		left join (select IFNULL(sum(qc.base_stock),0) as deduct_stock,qc.sales_order_trn_id from tbl_reserve_stock as qc where qc.stock_status!=2 and stock_flage=2 group by qc.sales_order_trn_id) as qc on qc.sales_order_trn_id=res.sales_order_trn_id
		where res.stock_flage = 1 AND res.stock_status !=2 AND  res.sales_order_trn_id = ".$sales_ordertrn_id." group by batch_no having pending_stock > 0";

	$rs_batch=$dbcon->query($query);
	$str= '<option value="">Choose Batch No</option>';
	while($rel=brp_mysqli_fetch_assoc($rs_batch))
	{	
		if($rel['pending_stock'] > 0){
			$str.= '<option value="'.$rel['reserve_id'].'" data-stock="'.$rel['base_stock'].'" >'.$rel['batch_no'].'</option>';
		}
	}

	$html = '<div class="col-md-12">				
	<div class="col-md-5">
	<div class="form-group">
	<label for="edit_zone_name">Batch No</label>
	<select class="form-control batch_select2" name="batch_id" id="batch_id" onChange="get_batch_qty(this.value);" >
	"'.$str.'"
	</select>							
	</div>	
	</div>
	<div class="col-md-3">
	<div class="form-group">
	<label for="edit_zone_name">Total Qty</label>
	<input type="number" min="0" class="form-control" name="batch_stock" id="batch_stock" readonly />
	</div>	
	</div>

	<div class="col-md-3">
	<div class="form-group">
	<label for="edit_zone_name">Qty</label>
	<input type="number" min="0" class="form-control numbersOnly" name="qtyforbatch" id="qtyforbatch" />
	</div>	
	</div>

	<div class="col-md-1">
	<div class="form-group">
	<input type="button" id="add_batch_qty" value="+"  class="btn btn-primary" title="Add" onclick="add_batch_qty();" 
	style="margin-top: 24px;"  />
	</div>	
	</div>

	</div>';
	$row['html_data'] = $html;
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "fetch_batch_qty"){
	$sales_ordertrn_id = $POST['sales_ordertrn_id'];
	if(!empty($POST['edit_id'])){
		$str = " and bst.de_allo_trn_id=".$POST['edit_id']." and bst.status=1 ";
	}else{
		$str = " and bst.status=0";
	}
	$appData = array();
	$i=1;
	$aColumns = array('bst.qty','bst.batch_no','bst.batch_stk_id','bst.stock_id');
	$sTable = "tbl_so_deallocate_batch_stock_tmp as bst";			
	$isJOIN = array();
	$sIndexColumn = "bst.batch_stk_id";
	$where = " bst.sales_order_trn_id = ".$sales_ordertrn_id." AND bst.product_id='".$POST['product_id']."' ".$str." ";
	$isWhere = array($where);
	$hOrder = "bst.batch_stk_id desc";
	include($path.'include/pagging.php');
	$id=1;
	$edit = $delete = '';
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['batch_no'];
		$row_data[] = $row['qty'];
		$delete='';
		$batch_no = "'" . $row['batch_no'] . "'";
		$stock_id = "'" . $row['stock_id'] . "'";
		if(empty($POST['edit_id'])){
			$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_batch_stock_entry('.$row['batch_stk_id'].')"><i class="fa fa-trash-o"></i></button>';	
		}else{
			$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_batch_stock_entry('.$row['batch_stk_id'].','.$POST['edit_id'].','.$batch_no.','.$stock_id.')"><i class="fa fa-trash-o"></i></button>';
		}
		
		$row_data[] = $delete;

		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode'])== "add_batch_qty"){

	if(!empty($POST['edit_id'])){
		$str = " and de_allo_trn_id=".$POST['edit_id']." and status=1 ";
		$info['de_allo_trn_id']   = $POST['edit_id'];
		$info['status']   = 1;
	}else{
		$str = " and de_allo_trn_id=0 and status=0 ";
	}

	$tr = $dbcon -> query("SELECT batch_no FROM tbl_so_deallocate_batch_stock_tmp where batch_no='".$POST['batch_no']."'".$str);
	if($tr->num_rows > 0) {
		$row['res'] = '-1';
	} else {

		$info['product_id']   = $POST['product_id'];
		$info['sales_order_trn_id']   = $POST['sales_ordertrn_id'];
		$info['batch_no']   = $POST['batch_no'];
		// $info['stock_id']   = $POST['stock_id'];
		$info['reserve_id']   = $POST['stock_id'];
		$info['qty']   		= $POST['qty'];
		$info['unitid']   	= $POST['unit_id'];
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];		

		$inserbatchstockid=add_record('tbl_so_deallocate_batch_stock_tmp', $info, $dbcon);

		if($inserbatchstockid){
			$row['res']="1";
		}
		else{
			$row['res']="0";
		}
	}
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "validate_qty"){
	$sales_ordertrn_id = $POST['sales_ordertrn_id'];
	if(!empty($POST['edit_id'])){
		$str = " and bst.de_allo_trn_id=".$POST['edit_id']." and bst.status=1 ";
	}else{
		$str = " and bst.de_allo_trn_id=0 and bst.status=0 ";
	}
	$qry2="SELECT sum(bst.qty) as qty FROM tbl_so_deallocate_batch_stock_tmp as bst where bst.sales_order_trn_id = ".$sales_ordertrn_id." and bst.product_id=".$POST['product_id']." ".$str." ";

	$result2=mysqli_fetch_assoc($dbcon->query($qry2));
	$total_qty = $result2['qty'] + $POST['qtyforbatch'];
	if($total_qty > $POST['product_qty']){
		$row['res']="0";
	}else if($total_qty == $POST['product_qty']){
		$row['res']="1";
	}else{
		$row['res']="2";
	}
	
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "delete_batch_entry"){
	$row=array();
	$info['status']=2;	
		
	$de_allo_trn_id = $POST['de_allo_trn_id'];	
	$batch_no = $POST['batch_no'];
	$stock_id =  $POST['stock_id'];	

	$updateid=update_record("tbl_so_deallocate_batch_stock_tmp", $info, "batch_stk_id=".$POST['batchstockid'] , $dbcon);
	
	if($updateid){
		$row['res']="1";
	}
	else{
		$row['res']="0";
	}
	echo json_encode($row);
}
else if(strtolower($POST['mode']) == "load_so_deallocate_detail") {
	$query = "select so_de.de_allo_no, so_de.de_allo_date, so_de.remark, usr.user_name from  tbl_so_stock_deallocate as so_de
	left join users as usr on usr.user_id=so_de.user_id
	where de_allo_id =".$POST['de_allo_id'];

	$qt_rel=brp_mysqli_fetch_assoc($dbcon->query($query));

//Party PO Details Table View
	$str='';
	$str.='<div class="form-group">
	<table class="display table table-bordered table-striped">
	<tr>
	<td><strong>Deallocate No:</strong> '.$qt_rel['de_allo_no'].'</td>
	<td><strong>Deallocate  Date:</strong> '.date("d-M-Y",strtotime($qt_rel["de_allo_date"])).'</td>
	</tr>
	<tr>
	<td><strong>Remark:</strong> '.$qt_rel['remark'].'</td>
	<td><strong>User:</strong> '.$qt_rel['user_name'].'</td>
	</tr>
	';
	$str.='</table></div>
	<hr/>
	';

		 $query="select mst.*, so.sales_order_no, product.product_name, u.unit_name, dr.drawing_number,product.product_icode from tbl_so_stock_deallocate_trn as mst 
	left join tbl_sales_order as so on so.sales_order_id=mst.sales_order_id 
	left join product_mst as product on product.product_id=mst.product_id 
	left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
	left join unit_mst as u on u.unitid=mst.unit_id
	where mst.status=0 and mst.de_allo_id = " . $POST['de_allo_id'];

	$result=$dbcon->query($query);

	$str .= '<div class="col-md-12">
	<div class="form-group">
	<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
	<tr id="field">
	<th class="text-center" width="5%">#</th>
	<th class="text-center" width="18%">Salesorder No
	</th>
	<th class="text-center" width="18%">Product Name
	</th>
	<th class="text-center hide_act_add" width="8%"> Deallocate Qty </th>
	<th class="text-center hide_act_add" width="5%">Unit</th>
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

			$str .= '<tr id="fieldtr'.$i.'" >

			
			<td style="vertical-align:top;">
			'.$i.'
			</td>
			<td style="vertical-align:top;">
			'.$rel['sales_order_no'].'
			</td>
			<td style="vertical-align:top;">
			'.$rel['product_name']. '
			<br/>'.' '.$item_code.' '.$drawing_number.'
			</td>
			<td style="vertical-align:top;">
			'.$rel['de_allocate_qty'].'
			</td>
			<td style="vertical-align:top;">
			'.$rel['unit_name'].'
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
else if(strtolower($POST['mode']) == "load_so_deallocate_appr_hist") {

	$where='';
	$where.="   log.de_allo_id=".$POST['de_allo_id'];

	$appData = array();
	$i=1;
	$aColumns = array('log.deallo_aprv_log_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.created_at', 'log.user_id');
	$sIndexColumn = "log.deallo_aprv_log_id";
	$isWhere = array(" ".$where." ");
	$sTable = "tbl_so_deallocate_stock_aprv_log as log";			
	$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
	$hOrder = "log.deallo_aprv_log_id desc";
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
else if(strtolower($POST['mode']) == "add_so_apprv_hist") {
	$info1['approve_remark']	= $POST['approve_remark'];
	$info1['approve_status']	= $POST['approve_status'];
	$info1['de_allo_id']	= $POST['de_allo_id'];
	$info1['user_id']			= $_SESSION['user_id'];
	$info1['company_id']		= $_SESSION['company_id'];
	
	$inserid=add_record("tbl_so_deallocate_stock_aprv_log", $info1, $dbcon);

	$info['approve_status'] = $POST['approve_status'];	
	
	$updateid=update_record("tbl_so_stock_deallocate", $info, "de_allo_id=".$POST['de_allo_id'], $dbcon);

	if($POST['approve_status'] == '1'){ //  deduct stock
		$query_perent = "SELECT * FROM `tbl_so_stock_deallocate_trn` WHERE status = 0 and  de_allo_id =" . $POST['de_allo_id'];
		$result_perent = $dbcon->query($query_perent);

		while($rel = brp_mysqli_fetch_assoc($result_perent)){

			$so_trn_qry = "SELECT bom_status,bom_id FROM tbl_sales_ordertrn WHERE sales_ordertrn_id = " . $rel['sales_ordertrn_id'];
			$so_trn_row = brp_mysqli_fetch_assoc($dbcon->query($so_trn_qry));

			if($so_trn_row['bom_id'] == '0' && $so_trn_row['bom_status'] == '1'){
				$upd_so_trn['bom_status'] = 0;

				update_record("tbl_sales_ordertrn", $upd_so_trn, "sales_ordertrn_id=".$so_trn_row['sales_ordertrn_id'], $dbcon);
			}

			$so_prod_qry = "SELECT * FROM tbl_sales_order_production_trn WHERE sales_order_production_status = 0 and sales_ordertrn_id = " . $rel['sales_ordertrn_id'];
			$so_prod_res = $dbcon->query($so_prod_qry);

			$so_de_allocate_qty = $rel['de_allocate_qty'];
			while ($so_prod_rw = brp_mysqli_fetch_assoc($so_prod_res)) {
				$so_allocate_qty = $so_prod_rw['product_qty'];
				$so_prod_info = array();
				if($so_de_allocate_qty){
					if($so_allocate_qty >= $so_de_allocate_qty){
						if(($so_allocate_qty - $so_de_allocate_qty) <= 0){
							$so_prod_info['sales_order_production_status'] = 2;	
						}
						$so_prod_info['product_qty'] = $so_allocate_qty - $so_de_allocate_qty;
					}else{
						$so_prod_info['product_qty'] = 0;
						$so_prod_info['sales_order_production_status'] = 2;
					}

					$updateid123=update_record("tbl_sales_order_production_trn", $so_prod_info, "sales_order_production_trn_id=".$so_prod_rw['sales_order_production_trn_id'], $dbcon);
				}
			}

			$sel_stock = "select * from tbl_so_deallocate_batch_stock_tmp where status=1 and de_allo_trn_id=".$rel['de_allo_trn_id'];
			$sel_stock_rs = $dbcon->query($sel_stock);

			$sel_pro = "select * from product_mst where product_status=0 and product_id=".$rel['product_id'];
			$sel_pro_rs = $dbcon->query($sel_pro);
			$sel_pro_rel = brp_mysqli_fetch_assoc($sel_pro_rs);
			$cnt_stock_temp = brp_mysqli_num_rows($sel_stock_rs);
			if($cnt_stock_temp > 0){
				while($sel_stock_rel=brp_mysqli_fetch_assoc($sel_stock_rs)){
					$query = "select res.*,base_stock - IFNULL(deduct_stock,0) as pending_stock from tbl_reserve_stock as res 
						left join (select IFNULL(sum(qc.base_stock),0) as deduct_stock,qc.perent_id from tbl_reserve_stock as qc where qc.stock_status!=2 and stock_flage=2 group by qc.perent_id) as qc on qc.perent_id=res.reserve_id where res.stock_flage = 1 and res.stock_status !=2  and res.sales_order_trn_id = ".$rel['sales_ordertrn_id']." and res.reserve_id in (".$sel_stock_rel['reserve_id'].") having pending_stock > 0";

						//echo "</br></br>";

					$result = $dbcon->query($query);

					$de_allo_qty = $sel_stock_rel['qty'];
					while($row=brp_mysqli_fetch_assoc($result)){
						$pending_qty = $row['pending_stock'];
						$base_stock = 0;
						$conv_stock = 0;
						if($de_allo_qty > 0 && $pending_qty){
							if($pending_qty >= $de_allo_qty){
								$base_stock = $de_allo_qty;
								$de_allo_qty = 0;
							}else{
								$base_stock = $pending_qty;
								$de_allo_qty = $de_allo_qty - $pending_qty;
							}

							$upd_stock['base_stock'] = $row['base_stock'] - $base_stock;
							$conv_stock=convert_stock($dbcon,$base_stock,$rel['product_id'],"conv_unit");
							$upd_stock['convert_stock'] = $row['convert_stock'] - $conv_stock;

							$updateid1=update_record("tbl_reserve_stock", $upd_stock, "reserve_id=".$row['reserve_id'], $dbcon);


							$st_qry = "SELECT used_base_stock,used_convert_stock FROM tbl_stock_trn WHERE stock_id = " . $row['stock_id'];
							$st_row = brp_mysqli_fetch_assoc($dbcon->query($st_qry));


							$st_upd['used_base_stock'] = $st_row['used_base_stock'] - $base_stock;
							$st_upd['used_convert_stock'] = $st_row['used_convert_stock'] - $conv_stock;

							$updateid11=update_record("tbl_stock_trn", $st_upd, "stock_id=".$row['stock_id'], $dbcon);



						}
					}
				}
			}else{
				$de_allo_qty = $rel['de_allocate_qty'];
			
			 	$query = "select res.*,base_stock - IFNULL(deduct_stock,0) as pending_stock from tbl_reserve_stock as res 
						left join (select IFNULL(sum(qc.base_stock),0) as deduct_stock,qc.perent_id from tbl_reserve_stock as qc where qc.stock_status!=2 and stock_flage=2 group by qc.perent_id) as qc on qc.perent_id=res.reserve_id where res.stock_flage = 1 and res.stock_status !=2  and res.sales_order_trn_id = ".$rel['sales_ordertrn_id']." having pending_stock > 0";
							
				$result = $dbcon->query($query);
				while($row=brp_mysqli_fetch_assoc($result)){
					$pending_qty = $row['pending_stock'];
					if($de_allo_qty > 0 && $pending_qty){
						if($pending_qty >= $de_allo_qty){
							$base_stock = $de_allo_qty;
							$de_allo_qty = 0;
						}else{
							$base_stock = $pending_qty;
							$de_allo_qty = $de_allo_qty - $pending_qty;
						}

						$upd_stock['base_stock'] = $row['base_stock'] - $base_stock;
						$conv_stock=convert_stock($dbcon,$base_stock,$rel['product_id'],"conv_unit");
						$upd_stock['convert_stock'] = $row['convert_stock'] - $conv_stock;

						$updateid1=update_record("tbl_reserve_stock", $upd_stock, "reserve_id=".$row['reserve_id'], $dbcon);


						$st_qry = "SELECT used_base_stock,used_convert_stock FROM tbl_stock_trn WHERE stock_id = " . $row['stock_id'];
						$st_row = brp_mysqli_fetch_assoc($dbcon->query($st_qry));


						$st_upd['used_base_stock'] = $st_row['used_base_stock'] - $base_stock;
						$st_upd['used_convert_stock'] = $st_row['used_convert_stock'] - $conv_stock;

						$updateid11=update_record("tbl_stock_trn", $st_upd, "stock_id=".$row['stock_id'], $dbcon);
					}
				}
			}
		}
	}
}