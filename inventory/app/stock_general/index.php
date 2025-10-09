<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	INVENTORY_STOCK_GENERAL_SLUG_READ,
	INVENTORY_STOCK_GENERAL_SLUG_CREATE,
	INVENTORY_STOCK_GENERAL_SLUG_UPDATE,
	INVENTORY_STOCK_GENERAL_SLUG_DELETE,
	INVENTORY_STOCK_GENERAL_SLUG_APPROVE,
	INVENTORY_STOCK_GENERAL_SLUG_PRINT
]);

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}


if(strtolower($POST['mode']) == "fetch") {

	$appData = array();
	$i=1;
	$where = "";
	$aColumns = array('gstock.general_stock_id','gstock.general_stock_no','gstock.remark', 'gstock.general_stock_date', 'gstock.stock_approval');
	$sIndexColumn = "gstock.general_stock_id";
	$isWhere = array("gstock.status = 0".$where.check_company('gstock'));
	$sTable = "tbl_general_stock as gstock";
	$isJOIN = array('');
	$hOrder = "gstock.general_stock_id desc";
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();

		$row_data[] = $row['sr'];
		$row_data[] = $row['general_stock_no'];
		$row_data[] = date('d-m-Y',strtotime($row['general_stock_date']));
		$row_data[] = $row['remark'];
		if($row['stock_approval'] == '1'){
			$row_data[] = '<button class="btn btn-xs btn-success" >Approved</button>';
		}else if($row['stock_approval'] == '2'){
			$row_data[] = '<button class="btn btn-xs btn-danger" >Disapproved</button>';
		}else{
			$row_data[] = '<button class="btn btn-xs btn-warning">Approval Pending</button>';
		}
		$edit_btn=''; $delete_btn='';$apprv_btn='';$print='';

			if($row['stock_approval']==0){
				if(in_array(INVENTORY_STOCK_GENERAL_SLUG_UPDATE,$bulkAccessArray)){
				$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'stock_general_edit/'.$row['general_stock_id'].'"><i class="fa fa-pencil"></i></a>'; 
				}
				if(in_array(INVENTORY_STOCK_GENERAL_SLUG_DELETE,$bulkAccessArray)){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_stock_general('.$row['general_stock_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				}

				if(in_array(INVENTORY_STOCK_GENERAL_SLUG_APPROVE,$bulkAccessArray)){
					$apprv_btn='<button class="btn btn-xs btn-success" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="stock_approval('.$row['general_stock_id'].',\''.$row['general_stock_no'].'\')"> <i class="fa fa-exclamation-triangle"></i></button>';
				}
			}else{
				//$stiker='<a class="btn btn-xs btn-info" data-original-title="Sticker Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'stock_general_sticker_common_print/'.$row['general_stock_id'].'"><i class="fa fa-barcode"></i></a> ';
			}
			
		$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
		$rels=mysqli_fetch_assoc($menusql);
		$menu_show_permissions = explode(",",$rels['print_permission']);
		$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 22 AND approve_status = 1 AND status = 0 ORDER BY priority");
		while($res = mysqli_fetch_assoc($sql)){
			if(in_array($res['id'],$menu_show_permissions)) {
				$print.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['general_stock_id'].'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>&nbsp;';
			}
		}
			

		$row_data[] = $edit_btn.' '.$delete_btn.' '.$apprv_btn.' '.$print.' '.$stiker;
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}

else if(strtolower($POST['mode'])== "get_product_check"){
	if($POST['eid']){
		$where = ' and status = 0 and returnable_id='.$POST['eid'];
	}else{
		$where = ' and status = 3 and user_id='.$_SESSION['user_id'];
	}

	$query = "select * from tbl_returnable_channal_item as item where `company_id` = ".$_SESSION['company_id'].$where;
	$result=$dbcon->query($query);
	$cnt = brp_mysqli_num_rows($result);
	$row=brp_mysqli_fetch_assoc($result);
	echo $cnt;
}	

else if(strtolower($POST['mode'])== "load_productdata"){	
	$qry="select * from `product_mst` where product_id=".$POST['eid'];
	$result=$dbcon->query($qry);

	$row=brp_mysqli_fetch_assoc($result);

	echo json_encode( $row );
}
else if(strtolower($POST['mode'])== "load_product_unit")
{
	$query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name FROM product_mst as promst
	left join unit_mst as umst on umst.unitid=promst.product_base_unit
	left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
	WHERE product_id=".$POST['product_id'];

	$rs_type1=$dbcon->query($query1);
	$row1=brp_mysqli_fetch_assoc($rs_type1);

	if($row1['product_base_unit']!=$row1['product_conv_unit']){
		$row1['unit_status']="1";
		$opt='<option  value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
		$opt .='<option  value="'.$row1['product_conv_unit'].'">'.$row1['convert_unit_name'].'</option>';
	}else{
		$row1['unit_status']="0";
		$opt='<option value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
	}

	$row1['unit_option']=$opt;

	echo json_encode($row1);
}
else if(strtolower($POST['mode'])== "load_deduct_product"){
	$str = '';$where = '';

	if($POST['eid']){
		$where = ' and trn.general_stock_id='.$POST['eid'];
	}else{
		$where = ' and general_stock_id=0 and trn.user_id='.$_SESSION['user_id'];
	}

	$query = "select trn.*,pmst.product_name,unit.unit_name from tbl_general_stock_trn as trn 
	left join product_mst as pmst on pmst.product_id = trn.product_id
	left join unit_mst as unit on unit.unitid = trn.rate_unit
	where trn.status=0 and trn.stock_type=2".$where;
	$result = $dbcon->query($query);
	$cnt = brp_mysqli_num_rows($result);
	$str .= '<table class="table table-bordered">
	<thead>
	<th style="width:40%;">Product Name</th>
	<th style="width:15%;">Unit Name</th>
	<th style="width:15%;">Qty</th>
	<th style="width:15%;">Action</th>
	</thead>';
	if($cnt>0){
		$i=1;
		while($row = brp_mysqli_fetch_array($result)){
			if($row['rate_unit']==$row['unitid']){
				$qty = $row['product_qty'];
			}else{
				$qty = $row['product_conv_qty'];
			}

			$str .= '<tr>
			<td>'.$row['product_name'].'</td>		
			<td>'.$row['unit_name'].'</td>		
			<td>'.$qty.'</td>		
			<td>
			<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_deduct_data('.$row['general_stock_trn_id'].');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>

			<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_deduct_data('.$row['general_stock_trn_id'].');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
			</td>		
			</tr>'; 
			$i++;
		}
	}else{
		$str .='<tr>
		<td colspan="4" style="text-align:center">No Data Yet...!!!</td>
		</tr>';
	}
	$str .='</table>';
	echo $str;
}
else if(strtolower($POST['mode'])=="load_stock_qty")
{
	$product_id=$POST['product_id'];
	$get_pro_type_qry="select product_type,product_base_unit from product_mst where product_id=".$product_id;
	$get_pro_type_rel=mysqli_fetch_assoc($dbcon->query($get_pro_type_qry));
	
	// $product_type_arr = array("0", "1", "2", "3", "4", "5", "6", "7", "9", "-1");
	// if (in_array($get_pro_type_rel['product_type'], $product_type_arr)){
		if(!empty($POST['unit_id'])){
			$unit_id=$POST['unit_id'];
		}else{
			$unit_id=$get_pro_type_rel['product_base_unit'];
		}
		$current_stock = get_current_stock_new($dbcon,$product_id,$unit_id);
		/* echo $current_stock; */
		$unclear_qty=0;
		if($POST['edit_deduct_id'] !=''){
			$where=" and status!='2' and stock_type=2 and general_stock_trn_id=".$POST['edit_deduct_id'];
			$unclear_qty = get_unclear_stock($dbcon,$product_id,$unit_id,'tbl_general_stock_trn','product_qty','product_id',$where);
		}
		
		$rstock=reserve_stock($dbcon,$POST['product_id'],$POST['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$stock_id);
		//$unclear_qty-
		echo $current_stock+$unclear_qty-$rstock;
	/*}
	else{
		echo 0;
	}*/
	
}
else if(strtolower($POST['mode'])=="load_in_product_stock")
{
	//$product_id=$POST['product_id'];
	
		$current_stock = get_current_stock_new($dbcon,$POST['product_id'],$POST['unit_id']);
		
		$rstock=reserve_stock($dbcon,$POST['product_id'],$POST['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$stock_id);
		//$unclear_qty-
		echo $current_stock-$rstock;
}
else if(strtolower($POST['mode'])== "load_in_product"){
	$str = '';	

	if($POST['eid']){
		$where = ' and trn.general_stock_id='.$POST['eid'];
	}else{
		$where = ' and general_stock_id=0 and trn.user_id='.$_SESSION['user_id'];
	}

	$query = "select trn.*,unit.unit_name,pro.product_name from tbl_general_stock_trn as trn
	left join product_mst as pro on pro.product_id = trn.product_id
	left join unit_mst as unit on unit.unitid = trn.rate_unit
	where trn.status=0 and trn.stock_type=1".$where;
	$result = $dbcon->query($query);
	$cnt = brp_mysqli_num_rows($result);
	$str .= '<table class="table table-bordered ">
	<thead>
	<th style="width:40%;">Product Name</th>
	<th style="width:15%;">Unit Name</th>
	<th style="width:15%;">Qty</th>
	<th style="width:15%;">Rate</th>
	<th style="width:20%;">Action</th>
	</thead>';
	if($cnt>0){
		$i=1;
		while($row = brp_mysqli_fetch_array($result)){

			if($row['rate_unit'] == $row['unitid']){
				$qty = $row['product_qty'];
				$rate = $row['product_rate'];
			}else{
				$qty = $row['product_conv_qty'];
				$rate = $row['product_conv_rate'];		
			}

			$str .= '<tr>
			<td>'.$row['product_name'].'</td>		
			<td>'.$row['unit_name'].'</td>		
			<td>'.$qty.'</td>		
			<td>'.$rate.'</td>		
			<td>
			<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_in_data('.$row['general_stock_trn_id'].');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>

			<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_in_data('.$row['general_stock_trn_id'].');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
			</td>		
			</tr>'; 

			$i++;
		}
	}else{
		$str .='<tr>
		<td colspan="5" style="text-align:center">No Data Yet...!!!</td>
		</tr>';
	}
	echo $str;	
}

else if(strtolower($POST['mode'])== "field_in_add"){
	$companyConfiguration=getCompanyConfiguration($dbcon);
	$product_detail = get_product_detail($dbcon,$POST['product_id']);
	$info1['product_id']			= $POST['product_id'];
	$info1['rate_unit']				= $POST['rate_unitid'];
	$info1['unitid']				= $POST['unit_id'];
	$info1['conv_unitid']			= $POST['conv_unitid'];
	$info1['product_qty']			= $POST['product_qty'];
	$info1['product_conv_qty']		= $POST['product_conv_qty'];
	$info1['stock_type']			= 1;
	$info1['product_rate']			= $POST['base_rate'];
	$info1['product_conv_rate']		= $POST['conv_rate'];
	$info1['general_stock_id']		= $POST['general_stock_id'];
	$info1['sales_order_id']		= $POST['sales_order_id'];
	$info1['for_user_id']		= $POST['for_user_id'];

	$info1['user_id']				= $_SESSION['user_id'];
	$info1['company_id']			= $_SESSION['company_id'];
	$info1['cdate']					= date("Y-m-d h:i:s");


	if(empty($POST['edit_id']))
	{
		$inserid=add_record('tbl_general_stock_trn', $info1, $dbcon);
	}else{
		$inserid=update_record('tbl_general_stock_trn', $info1,'general_stock_trn_id'."=".$POST['edit_id'] , $dbcon);
	}

	$d_id=array();
	$total_batch_stock=$POST['total_base_stock'];
	$godown_id=$POST['godown_id'];
	$batch_no=$POST['batch_no'];
	$arry_edit=$POST['arry_edit'];
	/*echo "<pre>"; print_r($batch_no); echo "</pre>";*/
	/*echo "<pre>"; print_r($godown_id); echo "</pre>";
	echo "<pre>"; print_r($batch_no); echo "</pre>";
	echo "<pre>"; print_r($arry_edit); echo "</pre>";
	echo "<pre>"; print_r($total_batch_stock); echo "</pre>";*/
	for($i=0;$i<count($total_batch_stock);$i++)
	{
		if(empty($POST['edit_id'])){
			$info_dil['general_stock_trn_id']	= $inserid;
		}else{
			$info_dil['general_stock_trn_id']	= $POST['edit_id'];
		}
		/*var_dump($i);*/
		$batch_no1 = $batch_no[$i];
		/*var_dump($batch_no[$i]);*/
		
		if(empty($arry_edit[$i])){
			if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1') {
				$batch_no1 = get_batch_no($dbcon,$info1['product_id']);
			}
		}
		if($product_detail['batch_wise_stock_manage']==1){
			$info_dil['batch_stock_no']			= $batch_no1;
		}
		$info_dil['godown_id']				= $godown_id[$i];
		$info_dil['qty']					= $total_batch_stock[$i];
		$info_dil['unitid']					= $info1['unitid'];
		
		$info_dil['user_id']				= $_SESSION['user_id'];
		$info_dil['cdate']					= date("Y-m-d h:i:s");
		$info_dil['company_id']				= $_SESSION['company_id'];
		
		
		$table_k='tbl_batch_stock_trn_in';$tableid_k='batch_stock_id';
		
		if(!empty($arry_edit[$i])){
			$updateid_k=update_record($table_k,$info_dil,"batch_stock_id=".$arry_edit[$i],$dbcon);
			array_push($d_id,$arry_edit[$i]);
		}else{
			$inserid_k=add_record($table_k,$info_dil,$dbcon);
			array_push($d_id,$inserid_k);
			if($product_detail['batch_wise_stock_manage']==1){
				if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1') {
					update_batch_no($dbcon,$info1['product_id']);
				}
			}
		}
	}

	$did=implode(",",$d_id);
	$info_dil_1['status']="2";
	$updateid_p=update_record($table_k,$info_dil_1,"general_stock_trn_id=".$info_dil['general_stock_trn_id']." and batch_stock_id NOT IN (".$did.")",$dbcon);


	if($inserid)
	{	
		$arr['msg']="1";
	}else{
		$arr['msg']="0";
	}
	echo json_encode($arr);
}

else if(strtolower($POST['mode'])== "field_deduct_add"){
	$product_id=$POST['product_id'];
	$get_pro_type_qry="select product_type,product_base_unit from product_mst where product_id=".$product_id;
	$get_pro_type_rel=mysqli_fetch_assoc($dbcon->query($get_pro_type_qry));
	

	/*$product_type_arr = array("0", "1", "2", "3", "4", "5", "6", "7", "9", "-1");
	if (in_array($get_pro_type_rel['product_type'], $product_type_arr)){*/
		if(!empty($POST['unit_id'])){
			$unit_id=$POST['unit_id'];
		}else{
			$unit_id=$get_pro_type_rel['product_base_unit'];
		}
		$current_stock = get_current_stock_new($dbcon,$product_id,$unit_id);
		
		$rstock=reserve_stock($dbcon,$POST['product_id'],$POST['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$stock_id);

		$unclear_qty=0;
		if($POST['edit_id'] !=''){
			$where=" and status!='2' and stock_type=2 and general_stock_trn_id=".$POST['edit_id'];
			$unclear_qty = get_unclear_stock($dbcon,$product_id,$unit_id,'tbl_general_stock_trn','product_qty','product_id',$where);
		}
		$prod_stock = $current_stock+$unclear_qty-$rstock;
		/* echo $prod_stock; */
	/*}else{
		$prod_stock = 0;
	}*/
	 
	if($prod_stock>=$POST['product_qty']){
		$product_detail = get_product_detail($dbcon,$_POST['product_id']);
		$info1['product_id']			= $POST['product_id'];
		$info1['rate_unit']				= $POST['rate_unitid'];
		$info1['unitid']				= $POST['unit_id'];
		$info1['conv_unitid']			= $POST['conv_unitid'];
		$info1['product_qty']			= $POST['product_qty'];
		$info1['product_conv_qty']		= $POST['product_conv_qty'];
		$info1['stock_type']			= 2;
		$info1['general_stock_id']		= $POST['general_stock_id'];
		$info1['sales_order_id']		= $POST['sales_order_id'];
		$info1['for_user_id']			= $POST['for_user_id'];
		$info1['user_id']				= $_SESSION['user_id'];
		$info1['company_id']			= $_SESSION['company_id'];
		$info1['cdate']					= date("Y-m-d h:i:s");

		/*var_dump($POST['edit_id']);*/
		if(empty($POST['edit_id']))
		{
			$inserid=add_record('tbl_general_stock_trn', $info1, $dbcon);
			
			$general_stock_trn_id = $inserid;
			$sel_itrn = $dbcon->query("SELECT * FROM tbl_general_batch_stock_tmp where status=0 and product_id=".$POST['product_id']);
			
			/* var_dump("SELECT * FROM tbl_general_batch_stock_tmp where status=0 and product_id=".$POST['product_id']); */
			
			if($sel_itrn->num_rows > 0) {
				$infobatch['general_stock_trn_id']= $general_stock_trn_id;
				$infobatch['status']= 1;
				
				while($r_itrn=brp_mysqli_fetch_array($sel_itrn))
				{
					$updateinvoicetrnid=update_record('tbl_general_batch_stock_tmp', $infobatch,"status=0 and product_id=".$POST['product_id'] , $dbcon);
				}
			}

			
			$sel_stock = "select * from tbl_general_batch_stock_tmp where status=1 and general_stock_trn_id=".$inserid;
			$sel_stock_rs = $dbcon->query($sel_stock);
			$cnt = brp_mysqli_num_rows($sel_stock_rs);
			if($cnt>0){
				while($sel_stock_rel=brp_mysqli_fetch_array($sel_stock_rs)){
					if($sel_stock_rel['stock_id']=='0'){
					
						$qry_11 = "select * from tbl_stock_trn where stock_status = 0 and stock_flage = 1 and product_id = " . $POST['product_id'] . " and godown_id = ".$sel_stock_rel['godown_id']." and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5))";
						/*$qry_11 = 'select * from tbl_stock_trn where stock_status = 0 and stock_flage = 1 and product_id = ' . $POST['product_id'] . ' and godown_id = '.$sel_stock_rs['godown_id'].' and cast(base_stock AS DECIMAL(10,5))>cast(used_base_stock AS DECIMAL(10,5))';*/
						
						$res_11 = $dbcon->query($qry_11);
						
						$item_qty 	= $info1['product_qty'];
						$item_unit 	= $info1['unitid'];
						
						while($row_11=brp_mysqli_fetch_array($res_11)){

							if($row_11['convert_unit']==$item_unit){
								$pending_stock=$row_11['convert_stock'] - $row_11['used_convert_stock'];
							}else{
								$pending_stock=$row_11['base_stock']- $row_11['used_base_stock'];	
							}
							if($item_qty>0){
								if($pending_stock>=$item_qty){
									$rqty=$item_qty;
									$item_qty=$item_qty-$item_qty;
								}else{
									$rqty=$pending_stock;
									$item_qty=$item_qty-$pending_stock;
								}
								$stock_id  = $row_11['stock_id'];
								$godown_id = $row_11['godown_id']; 
								$branch_id = $row_11['branch_id'];
								
								if($item_unit==$product_detail['product_conv_unit']){
									$type="base_unit";
									$con_stock=$rqty;
									$base_stock=convert_stock($dbcon,$con_stock,$info1['product_id'],$type);
								}else{
									$type="conv_unit";
									$base_stock=$rqty;
									$con_stock=convert_stock($dbcon,$base_stock,$info1['product_id'],$type);
								}

								item_reserve_stock_entry($dbcon,$sel_stock_rel['product_id'],$product_detail['product_base_unit'],$product_detail['product_conv_unit'],$base_stock,$con_stock,"production_bypass",$inserid,$stock_id,$godown_id,$branch_id);
							}
						}
					}else{
						if($sel_stock_rel['unitid']==$product_detail['product_conv_unit']){
							$type="base_unit";
							$con_stock=$sel_stock_rel['qty'];
							$base_stock=convert_stock($dbcon,$con_stock,$sel_stock_rel['product_id'],$type);
						}else{
							$type="conv_unit";
							$base_stock=$sel_stock_rel['qty'];
							$con_stock=convert_stock($dbcon,$base_stock,$sel_stock_rel['product_id'],$type);
						}
						
						$stock_qry = "select godown_id,branch_id from tbl_stock_trn where stock_id = " . $sel_stock_rel['stock_id'];
						$res_stock_qr = $dbcon->query($stock_qry);
						$rel_stock_1 = brp_mysqli_fetch_assoc($res_stock_qr);

						item_reserve_stock_entry($dbcon,$sel_stock_rel['product_id'],$product_detail['product_base_unit'],$product_detail['product_conv_unit'],$base_stock,$con_stock,"production_bypass",$inserid,$sel_stock_rel['stock_id'],$rel_stock_1['godown_id'],$rel_stock_1['branch_id']);
					}
				}
			}	
		}else{
			$inserid=update_record('tbl_general_stock_trn', $info1,'general_stock_trn_id'."=".$POST['edit_id'] , $dbcon);
			
			$sel_stock = "select * from tbl_general_batch_stock_tmp where status=1 and general_stock_trn_id=".$POST['edit_id'];
			$sel_stock_rs = $dbcon->query($sel_stock);
			$cnt = brp_mysqli_num_rows($sel_stock_rs);
			if($cnt>0){
				while($sel_stock_rel=brp_mysqli_fetch_assoc($sel_stock_rs)){

					if($sel_stock_rel['stock_id']=='0'){	
						$qry_11 = "select * from tbl_stock_trn where stock_status = 0 and stock_flage = 1 and product_id = " . $POST['product_id'] . " and godown_id = ".$sel_stock_rel['godown_id']." and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5))";
						/*$qry_11 = 'select * from tbl_stock_trn where stock_status = 0 and stock_flage = 1 and product_id = ' . $POST['product_id'] . ' and godown_id = '.$sel_stock_rs['godown_id'].' and cast(base_stock AS DECIMAL(10,5))>cast(used_base_stock AS DECIMAL(10,5))';*/
						
						$res_11 = $dbcon->query($qry_11);
						
						$item_qty 	= $info1['product_qty'];
						$item_unit 	= $info1['unitid'];
						
						while($row_11=brp_mysqli_fetch_array($res_11)){

							if($row_11['convert_unit']==$item_unit){
								$pending_stock=$row_11['convert_stock'] - $row_11['used_convert_stock'];
							}else{
								$pending_stock=$row_11['base_stock']- $row_11['used_base_stock'];	
							}

							if($item_qty>0){
								if($pending_stock>=$item_qty){
									$rqty=$item_qty;
									$item_qty=$item_qty-$item_qty;
								}else{
									$rqty=$pending_stock;
									$item_qty=$item_qty-$pending_stock;
								}
								$stock_id  = $row_11['stock_id'];
								$godown_id = $row_11['godown_id']; 
								$branch_id = $row_11['branch_id'];
								
								if($item_unit==$product_detail['product_conv_unit']){
									$type="base_unit";
									$con_stock=$rqty;
									$base_stock=convert_stock($dbcon,$con_stock,$info1['product_id'],$type);
								}else{
									$type="conv_unit";
									$base_stock=$rqty;
									$con_stock=convert_stock($dbcon,$base_stock,$info1['product_id'],$type);
								}

								item_reserve_stock_entry($dbcon,$POST['product_id'],$product_detail['product_base_unit'],$product_detail['product_conv_unit'],$base_stock,$con_stock,"production_bypass",$POST['edit_id'],$stock_id,$godown_id,$branch_id);
							}
						}
					}else{
						if($sel_stock_rel['unitid']==$product_detail['product_conv_unit']){
							$type="base_unit";
							$con_stock=$sel_stock_rel['qty'];
							$base_stock=convert_stock($dbcon,$con_stock,$info1['product_id'],$type);
						}else{
							$type="conv_unit";
							$base_stock=$sel_stock_rel['qty'];
							$con_stock=convert_stock($dbcon,$base_stock,$info1['product_id'],$type);
						}

						$stock_qry = "select godown_id,branch_id from tbl_stock_trn where stock_id = " . $sel_stock_rel['stock_id'];
						$res_stock_qr = $dbcon->query($stock_qry);
						$rel_stock_1 = brp_mysqli_fetch_assoc($res_stock_qr);

						
						item_reserve_stock_entry($dbcon,$info1['product_id'],$product_detail['product_base_unit'],$product_detail['product_conv_unit'],$base_stock,$con_stock,"production_bypass",$POST['edit_id'],$sel_stock_rel['stock_id'],$rel_stock_1['godown_id'],$rel_stock_1['branch_id']);
					}
				}
			}
			/*else{
				$item_qty 	= $info1['product_qty'];
				$item_unit 	= $info1['unitid'];
				
				$qry_11 = "select * from tbl_stock_trn where stock_status = 0 and stock_flage = 1 and product_id = " . $POST['product_id'] . " and cast(base_stock AS DECIMAL(10,5))>cast(used_base_stock AS DECIMAL(10,5))";

				$res_11 = $dbcon->query($qry_11);
				while($row_11=brp_mysqli_fetch_array($res_11)){
					if($row_11['convert_unit']==$item_unit){
						$pending_stock=$row_11['convert_stock'] - $row_11['used_convert_stock'];
					}else{
						$pending_stock=$row_11['base_stock']- $row_11['used_base_stock'];	
					}

					$rqty =0;
					if($item_qty>0){
						if($pending_stock>=$item_qty){
							$rqty=$item_qty;
							$item_qty=$item_qty-$item_qty;
						}else{
							$rqty=$pending_stock;
							$item_qty=$item_qty-$pending_stock;
						}
						$stock_id = $row_11['stock_id'];
						
						if($item_unit==$product_detail['product_conv_unit']){
							$type="base_unit";
							$con_stock=$rqty;
							$base_stock=convert_stock($dbcon,$con_stock,$info1['product_id'],$type);
						}else{
							$type="conv_unit";
							$base_stock=$rqty;
							$con_stock=convert_stock($dbcon,$base_stock,$info1['product_id'],$type);
						}
						
						item_reserve_stock_entry($dbcon,$info1['product_id'],$info1['unitid'],$info1['conv_unitid'],$info1['product_qty'],$info1['product_conv_qty'],"production_bypass",$inserid,$stock_id,$row_11['godown_id'],$row_11['branch_id']);
					}
				}
			}*/
		}

		if($inserid)
		{	
			$arr['msg']="1";
		}else{
			$arr['msg']="0";
		}
	}else{
		$arr['msg']="-1";
	}
	echo json_encode($arr);
}

else if(strtolower($POST['mode'])== "convert_rate"){
	$query = "select * from product_mst where product_id = ".$POST['product_id'];
	$result = $dbcon->query($query);
	$row  = brp_mysqli_fetch_array($result);

	if($POST['unit_id']==$row['product_base_unit']){
			$base_rate = $POST['base_rate']; //1000
			$conv_rate = ($row['product_base_qty']/$row['product_conv_qty'])*$base_rate;
		}else{
			$conv_rate = $POST['conv_rate'];
			$base_rate = ($row['product_conv_qty']/$row['product_base_qty'])*$conv_rate;
		}

		$r['base_rate'] = $base_rate;
		$r['conv_rate'] = $conv_rate;

		echo json_encode($r);
	}	

	else if(strtolower($POST['mode'])== "preedit_deduct"){
		$q = $dbcon -> query("select trn.*,pro.product_name,pro.batch_wise_stock_manage,unit.unit_name as base_unit, cunit.unit_name as conv_unit from tbl_general_stock_trn as trn 
			left join product_mst as pro on pro.product_id = trn.product_id
			left join unit_mst as unit on unit.unitid = trn.unitid
			left join unit_mst as cunit on cunit.unitid = trn.conv_unitid
			where general_stock_trn_id = ".$POST['id']);

		$r = $q->fetch_assoc();

		//$r['producthtml'] = getrequiredproduct($dbcon,$r['product_id'],' and product_type='.$r["product_type"].'');

		echo json_encode($r);
	}


	else if(strtolower($POST['mode'])== "preedit_in"){
		$q = $dbcon -> query("select trn.*,pro.product_name,unit.unit_name as base_unit, cunit.unit_name as conv_unit from tbl_general_stock_trn as trn 
			left join product_mst as pro on pro.product_id = trn.product_id
			left join unit_mst as unit on unit.unitid = trn.unitid
			left join unit_mst as cunit on cunit.unitid = trn.conv_unitid
			where general_stock_trn_id = ".$POST['id']);

		$r = $q->fetch_assoc();

		//$r['producthtml'] = getrequiredproduct($dbcon,$r['product_id'],' and product_type='.$r["product_type"].'');

		echo json_encode($r);
	}
	else if(strtolower($POST['mode'])== "delete_deduct_data")
	{
		$info['status']=2;

		delete_deduct_product_stock_effect($dbcon,$POST['eid']);
		
		$updateid=update_record('tbl_general_stock_trn', $info,'general_stock_trn_id'."=".$POST['eid'] , $dbcon);

		$updateid1=update_record('tbl_general_batch_stock_tmp', $info,'general_stock_trn_id'."=".$POST['eid'] , $dbcon);
		
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}

	else if(strtolower($POST['mode'])== "delete_in_data")
	{
		$info['status']=2;	

		$updateid=update_record('tbl_general_stock_trn', $info,'general_stock_trn_id'."=".$POST['eid'] , $dbcon);

		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}

	else if(strtolower($POST['mode'])== "add")
	{
		
		$info['general_stock_no']	= load_common_no($dbcon,STOCK_GENERAL_SERIES);
		$info['general_stock_date']	= date('Y-m-d',strtotime($POST['stock_general_date']));
		$info['remark']			= $_POST['remark'];
		$info['cdate']			= date("Y-m-d h:i:s");
		$info['user_id']			= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];
		
		$inserid=add_record('tbl_general_stock', $info, $dbcon);

		if($inserid){
			update_common_no($dbcon,STOCK_GENERAL_SERIES);
			$inftrn['general_stock_id'] = $inserid;
			$updatetrnid=update_record('tbl_general_stock_trn', $inftrn,"user_id=".$_SESSION['user_id']." and general_stock_id=0 and status=0" , $dbcon, $branch_id);
		}

		if($inserid)
		{	
			$arr['msg']="1";							
		}
		else{
			$arr['msg']="0";
		}

		echo json_encode($arr);

	}

	else if(strtolower($POST['mode'])== "edit")
	{
		$info['general_stock_no']	= $POST['stock_general_no'];
		$info['general_stock_date']	= date('Y-m-d',strtotime($POST['stock_general_date']));
		$info['remark']			= $_POST['remark'];
		$info['cdate']			= date("Y-m-d h:i:s");
		$info['user_id']			= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];

		//var_dump($info);

		$updateid=update_record('tbl_general_stock', $info," general_stock_id=".$POST['eid'] , $dbcon, $branch_id);

		if($updateid)
		{	
			$arr['msg']="update";	
		}
		else{
			$arr['msg']=0;
		}

		echo json_encode($arr);
	}

	else if(strtolower($POST['mode'])== "delete_stock_general"){
		$info['status']	= 2;

		delete_product_stock_effect($dbcon,$POST['eid']);
		
		$updateid=update_record('tbl_general_stock', $info," general_stock_id=".$POST['eid'] , $dbcon);
 
		$updateid1=update_record('tbl_general_stock_trn', $info," general_stock_id=".$POST['eid'] , $dbcon);
		
		$query = "select * from tbl_general_stock_trn where general_stock_id=".$POST['eid'];
		$result = $dbcon->query($query);



		while($row = brp_mysqli_fetch_array($result)){
			$updateid2=update_record('tbl_general_batch_stock_tmp', $info," general_stock_trn_id=".$row['general_stock_trn_id'] , $dbcon);
		}

		if($updateid)
			echo "1";	
		else
			echo "0";
	}
	else if(strtolower($POST['mode'])== "batch_stock_model_open"){
		$product_detail = get_product_detail($dbcon,$_POST['product_id']);
		if($product_detail['batch_wise_stock_manage']!=1){
			$onchange = "get_godown_qty(this.value);";
		}
		$html .= '<div class="col-md-12">
		<div class="col-md-5">
			<div class="form-group">
				<label for="edit_zone_name">Choose Godown</label>
				<select class="form-control batch_select2" name="godown_deduct_id" id="godown_deduct_id" onChange="get_godownwise_batch_no(this.value);'.$onchange.'" >
					
				"'.load_available_stock_godown($dbcon,$POST['product_id'],$_SESSION['branch_id'],$godown_id=0).'"
				</select>							
			</div>	
		</div>';	

		if($product_detail['batch_wise_stock_manage']==1){
			$html .='<div class="col-md-5">
			<div class="form-group">
			<label for="edit_zone_name">Batch No</label>
			<select class="form-control batch_select2" name="batch_id" id="batch_id" onChange="get_batch_qty(this.value);" >
			"'.get_general_stock_batch_no($dbcon,$POST['product_id']).'"
			</select>							
			</div>	
			</div>';
		}
		
		$html .='<div class="col-md-5">
		<div class="form-group">
		<label for="edit_zone_name">Total Qty</label>
		<input type="number" min="0" class="form-control" name="batch_stock" id="batch_stock" readonly />
		</div>	
		</div>

		<div class="col-md-5">
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

		$html1 = '<div class="adv-table">
					<table class="display table table-bordered table-striped" id="batch_stock_deduct_table">
						<thead>
							<tr>
								<th>Godown Name</th>';
								if($product_detail['batch_wise_stock_manage']==1){
									$html1 .='<th>Batch No</th>'; 
								}
								$html1 .='<th>Qty</th> 								
								<th class="hidden-phone">Action</th>					  
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>';
		$row['html_data'] = $html;
		$row['html_data1'] = $html1;
		$row['batch_wise'] = $product_detail['batch_wise_stock_manage'];
		echo json_encode($row);
	}else if(strtolower($POST['mode'])== "get_batch_qty"){
		$stock_id = $POST['batch_no'];
		$gstock=0;$rstock=0;
		$batch_no=$POST['batch_no'];
		$gstock=get_current_godown_stock_new($dbcon,$POST['product_id'],$POST['unit_id'],$POST['st_godown_id'],$branch_id,$stock_id);
		
		$rstock=reserve_stock($dbcon,$POST['product_id'],$POST['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$stock_id);

		$stock=$gstock-$rstock;

		echo $stock;
	}else if(strtolower($POST['mode'])== "get_godown_qty"){
		$stock_id = $POST['batch_no'];
		$gstock=0;$rstock=0;
		$batch_no=$POST['batch_no'];
		$gstock=get_current_godown_stock_new($dbcon,$POST['product_id'],$POST['unit_id'],$POST['st_godown_id'],$branch_id,'');
		
		$rstock=reserve_stock($dbcon,$POST['product_id'],$POST['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],'');

		$stock=$gstock-$rstock;

		echo $stock;
	}else if(strtolower($POST['mode'])== "fetch_batch_qty"){
		//var_dump($POST['product_id']);
		$product_detail = get_product_detail($dbcon,$POST['product_id']);	
		if(!empty($POST['edit_id'])){
			$str = " and bst.general_stock_trn_id=".$POST['edit_id']." and bst.status=1 ";
		}else{
			$str = " and bst.status=0";
		}

		if($product_detail['batch_wise_stock_manage']==1){
			$left_join = 'left join `tbl_stock_trn` as st on st.stock_id=bst.stock_id';
			$column = 'st.batch_no';
		}

		$appData = array();
		$i=1;
		$aColumns = array('bst.qty',$column,'bst.batch_stk_id','gd.gd_name');
		$sTable = "tbl_general_batch_stock_tmp as bst";			
		$isJOIN = array($left_join,'left join mst_godown as gd on gd.gd_id=bst.godown_id');
		$sIndexColumn = "bst.batch_stk_id";
		$where = "  bst.product_id='".$POST['product_id']."' ".$str." ";
		$isWhere = array($where);
		$hOrder = "bst.batch_stk_id desc";
		include($path.'include/pagging.php');
		$id=1;
		$edit = $delete = '';
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['gd_name'];
			if($product_detail['batch_wise_stock_manage']==1){
				$row_data[] = $row['batch_no'];
			}
			$row_data[] = $row['qty'];
			$delete='';


			$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_batch_stock_entry('.$row['batch_stk_id'].')"><i class="fa fa-trash-o"></i></button>';

			
			$row_data[] = $delete;

			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}else if(strtolower($POST['mode'])== "add_batch_qty"){

		if(!empty($POST['edit_id'])){
			$str = " and general_stock_trn_id=".$POST['edit_id']." and status=1 ";
			$info['general_stock_trn_id']   = $POST['edit_id'];
			$info['status']   = 1;
		}else{
			$str = " and general_stock_trn_id=0 and status=0 ";
		}

		$tr = $dbcon -> query("SELECT stock_id FROM tbl_returnable_batch_stock_tmp where stock_id=".$POST['stock_id']." ".$str." ");
		if($tr->num_rows > 0) {
			$row['res'] = '-1';
		} else {
			$info['product_id']   	= $POST['product_id'];
			$info['godown_id'] 		= $POST['godown_id'];
			$info['stock_id']     	= $POST['stock_id'];
			$info['qty']   		  	= $POST['qty'];
			$info['unitid']   	  	= $POST['unit_id'];
			$info['cdate']		  	= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];		

			$inserbatchstockid=add_record('tbl_general_batch_stock_tmp', $info, $dbcon);

			if($inserbatchstockid){
				$row['res']="1";
			}
			else{
				$row['res']="0";
			}
		}
		echo json_encode($row);
	}else if(strtolower($POST['mode'])== "delete_batch_entry"){
		$row=array();
		$info['status']=2;	
		
		$updateid=update_record("tbl_general_batch_stock_tmp", $info, "batch_stk_id=".$POST['batchstockid'] , $dbcon);
		
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}else if(strtolower($POST['mode'])== "validate_qty"){
		if(!empty($POST['edit_id'])){
			$str = " and general_stock_trn_id=".$POST['edit_id']." and status=1 ";
		}else{
			$str = " and general_stock_trn_id=0 and status=0 ";
		}
		$qry2="SELECT sum(qty) as qty FROM tbl_general_batch_stock_tmp where product_id=".$POST['product_id']." ".$str." ";

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
	else if(strtolower($POST['mode'])== "batch_stock_model_in_open"){
		$product_detail = get_product_detail($dbcon,$_POST['product_id']);
		if(empty($POST['trn_id'])){
			$count = 1;
			$companyConfiguration=getCompanyConfiguration($dbcon);

			if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1') {
				$batch_no = get_temp_batch_no($dbcon,$count,$_POST['product_id']);
			}
			echo '<input type="hidden" name="count" id="count" value="1" />
			<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
			<tr id="field">
			
				<th width="30%"  class="text-center" style="vertical-align:center;">Godown</th>';
				
				if($product_detail['batch_wise_stock_manage']==1){
					echo '<th width="30%"  class="text-center;" style="vertical-align:center;">Batch No</th>';	
				}
				
				echo '<th width="30%"  class="text-center;" style="vertical-align:center;">Batch Stock</th>
				<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
				</tr>
				<tr id="field1">
				
				<td   class="text-center" style="vertical-align:center;">
					<select  name="godown_id[]" id="godown_id1" class="select2 godown_id" onchange="qty_wise_batch_validation(1)">
                     	<option value="">--Select Godown--</option>
	                    '.get_all_godown($dbcon,'',1).'
                  	</select>
				</td>';

				if($product_detail['batch_wise_stock_manage']==1){
					echo '<td class="text-center;" style="vertical-align:center;">
						<input type="text" class="form-control batch_no" id="batch_no1" name="batch_no[]" placeholder="Batch No"  value="'.$batch_no.'" onkeyup="qty_wise_batch_validation(1);" />
					</td>';
				}

				echo '<td class="text-center;" style="vertical-align:center;">
					<input type="text" class="form-control batch_stock" id="batch_stock1" name="batch_stock[]" placeholder="'.$POST["qty"].'" onchange="validate_batch_qty();" onkeyup="qty_wise_batch_validation(1);" />
				</td>
				<td	 class="text-center;" style="vertical-align:center;">
				<input type="hidden" name="arry_sr[]" id="arry_sr" value="1" />
				</td>
			</tr>
			</table>';
		}else{
			$qry="SELECT * FROM `tbl_batch_stock_trn_in` WHERE status=0 and general_stock_trn_id=".$POST['trn_id']." order by batch_stock_id";
			$row=$dbcon->query($qry);
			$cnt=brp_mysqli_num_rows($row);
			if($cnt>0){
				$i=1;
				echo '<input type="hidden" name="count" id="count" value="'.$cnt.'" />
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
				<tr id="field">
					<th width="30%"  class="text-center" style="vertical-align:center;">Godown</th>';
					if($product_detail['batch_wise_stock_manage']==1){
						echo '<th width="30%"  class="text-center" style="vertical-align:center;">Batch No</th>';
					}
					echo '<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
					<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
					</tr>';
					
					while($tax=brp_mysqli_fetch_assoc($row))
					{
						/*$date=date('d-m-Y',strtotime($tax['delivery_date']));*/
						echo '<tr id="field'.$i.'">
						
						<td class="text-center" style="vertical-align:center;">
							<select  name="godown_id[]" id="godown_id'.$i.'" class="select2 godown_id" onchange="qty_wise_batch_validation('.$i.')">
	                     		<option value="">--Select Godown--</option>
		                    	'.get_all_godown($dbcon,$tax['godown_id'],1).'
	                  		</select>
						</td>';

						if($product_detail['batch_wise_stock_manage']==1){
							echo '<td class="text-center" style="vertical-align:center;">
								<input type="text" class="form-control batch_no" id="batch_no'.$i.'" name="batch_no[]" placeholder="Batch No" onkeyup="qty_wise_batch_validation(1);" value="'.$tax['batch_stock_no'].'" />
							</td>';
						}

						echo '<td	 class="text-center;" style="vertical-align:center;">
							<input type="number" class="form-control batch_stock numbersOnly" id="batch_stock'.$i.'" name="batch_stock[]" placeholder="'.$tax["qty"].'" value="'.$tax["qty"].'"  onkeyup="qty_wise_batch_validation('.$i.');" onchange="validate_batch_qty();" />
						</td>
						<td	 class="text-center;" style="vertical-align:center;">
						<input type="hidden" name="arry_sr[]" id="arry_sr'.$i.'" value="'.$i.'" />
						<input type="hidden" class="arry_edit" name="arry_edit[]" id="arry_edit'.$i.'" value="'.$tax["batch_stock_id"].'" />';
						if($i!=1){
							echo '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="remove_batch_data('.$i.');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>';
						}
						echo '</td>
						</tr>';
						$i++;
					}
					echo '</table>';
			}else{
				$count = 1;
				$companyConfiguration=getCompanyConfiguration($dbcon);

				if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1') {
					$batch_no = get_temp_batch_no($dbcon,$count,$_POST['product_id']);
				}
				echo '<input type="hidden" name="count" id="count" value="1" />
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
					<tr id="field">
					
					<th width="30%"  class="text-center" style="vertical-align:center;">Godown</th>';
					
					if($product_detail['batch_wise_stock_manage']==1){
						echo '<th width="30%"  class="text-center;" style="vertical-align:center;">Batch No</th>';	
					}
					
					echo '<th width="30%"  class="text-center;" style="vertical-align:center;">Batch Stock</th>
					<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
					</tr>
					<tr id="field1">
					
					<td class="text-center" style="vertical-align:center;">
						<select  name="godown_id[]" id="godown_id1" class="select2 godown_id" onchange="qty_wise_batch_validation(1)">
	                     	<option value="">--Select Godown--</option>
		                    '.get_all_godown($dbcon,'',1).'
	                  	</select>
					</td>';
					if($product_detail['batch_wise_stock_manage']==1){
						echo '<td class="text-center;" style="vertical-align:center;">
							<input type="text" class="form-control batch_no" id="batch_no1" name="batch_no[]" placeholder="Batch No" value="'.$batch_no.'" onchange="qty_wise_batch_validation(1);" />
						</td>';
					}

					echo '<td	 class="text-center;" style="vertical-align:center;">
						<input type="number" class="form-control batch_stock numbersOnly" id="batch_stock1" name="batch_stock[]" placeholder="'.$POST["qty"].'" onchange="validate_batch_qty();" onkeyup="qty_wise_batch_validation(1);" />
					</td>
					<td	 class="text-center;" style="vertical-align:center;">
					<input type="hidden" name="arry_sr[]" id="arry_sr" value="1" />
					</td>
					</tr>
				</table>';
			}
		}
	}else if(strtolower($POST['mode'])== "add_more"){
		$product_detail = get_product_detail($dbcon,$_POST['product_id']);
		$count = $POST['count'];
		$pending_qty = $POST['pending_qty'];
		$companyConfiguration=getCompanyConfiguration($dbcon);

		if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1') {
			$batch_no = get_temp_batch_no($dbcon,$count,$_POST['product_id']);
		}
		
		$str .='<tr id="field'.$count.'">
			<td class="text-center" style="vertical-align:center;">
				<select  name="godown_id[]" id="godown_id'.$count.'" class="select2 godown_id" onchange="qty_wise_batch_validation('.$count.')">
                 	<option value="">--Select Godown--</option>
                    '.get_all_godown($dbcon,'',1).'
              	</select>
			</td>';

			if($product_detail['batch_wise_stock_manage']==1){
				$str.='<td	 class="text-center;" style="vertical-align:center;">
					<input type="text" class="form-control batch_no" id="batch_no'.$count.'" name="batch_no[]" placeholder="Batch No" value="'.$batch_no.'" onchange="qty_wise_batch_validation('.$count.');" />
				</td>';
			}
			$str.='<td class="text-center;" style="vertical-align:center;">
				<input type="number" class="form-control batch_stock numbersOnly" id="batch_stock'.$count.'" name="batch_stock[]" onchange="validate_batch_qty();" placeholder="'.$pending_qty.'" onkeyup="qty_wise_batch_validation('.$count.');" />
			</td>

			<td class="text-center" style="vertical-align:center;" >
				<button type="button" class="btn btn-round btn-danger btn-xs" onclick="remove_batch_data('.$count.');" id="fieldremove'.$count.'">
					<i class="fa fa-times"></i>
				</button>
				<input type="hidden" name="arry_sr[]" id="arry_sr" value="'.$count.'" />
			</td>
		</tr>';

		$r['html_code'] = $str;
		$r['cnt']	 = $count;
		echo $str;
	}else if(strtolower($POST['mode'])== "check_batch_no"){
		$cnt = get_check_batch_no($dbcon,$POST['batch_no'],$POST['arry_edit']);
		$product_detail = get_product_detail($dbcon,$_POST['product_id']);
		$r['batch_wise_stock_manage'] = $product_detail['batch_wise_stock_manage']; 
		$r['cnt'] = $cnt;
		echo json_encode($r);
	}else if(strtolower($POST['mode']) == "load_general_stock_hist_datatable") {
		$companyConfiguration=getCompanyConfiguration($dbcon);
		$where='';
		$where.=" log.is_delete=0 and log.general_stock_id=".$POST['general_stock_id'];

		$appData = array();
		$i=1;
		$aColumns = array('log.stock_general_aprv_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id','log.general_stock_id');
		$sIndexColumn = "log.stock_general_aprv_id";
		$isWhere = array(" ".$where." ");
		$sTable = "tbl_stock_general_aprv_log as log";			
		$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
		$hOrder = "log.stock_general_aprv_id desc";
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
			}else{
				$row_data[] = '<div class="external-event label label-danger ui-draggable" style="cursor:auto;">Disapproved</div>';
			}

			$row_data[] = nl2br($row['approve_remark']);
			$row_data[] = date("d-M-Y h:i A",strtotime($row['cdate']));

			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}else if(strtolower($POST['mode']) == "add_general_stock_apprv_hist") {
		//$companyConfiguration=getCompanyConfiguration($dbcon);
		$info1['approve_remark']	= $POST['approve_remark'];
		$info1['approve_status']	= $POST['approve_status'];
		$info1['general_stock_id']	= $POST['general_stock_id'];
		$info1['user_id']			= $_SESSION['user_id'];
		$info1['company_id']		= $_SESSION['company_id'];
		$info1['cdate']				= date('Y-m-d H:i:s');

		$inserid=add_record("tbl_stock_general_aprv_log", $info1, $dbcon);

		if($POST['approve_status']==1){
			enter_production_stock_effect($dbcon,$POST['general_stock_id']);
		}else{	
			delete_product_stock_effect($dbcon,$POST['general_stock_id']);
		}
		
		$info['stock_approval'] = $POST['approve_status'];	
		
		$updateid=update_record("tbl_general_stock", $info, "general_stock_id=".$POST['general_stock_id'], $dbcon);

	}else if(strtolower($POST['mode']) == "get_godownwise_batch_no") {
		$str  = get_godown_wise_batch_no($dbcon,$POST['product_id'],$POST['godown_id']);
		echo $str;
	}


function item_reserve_stock_entry($dbcon,$product_id,$base_unit,$conv_unit,$base_stock,$con_stock,$chalan_type,$returnable_trn_id,$stock_id,$godown_id,$branch_id){


	$qry = "select * from tbl_reserve_stock where stock_status = 0 and stock_flage = 1 and ref_name='$chalan_type' and ref_id=" . $returnable_trn_id . " and product_id = " . $product_id . " and stock_id = " . $stock_id;

	/* echo "<br>"; */
	$result = $dbcon->query($qry);
	$cnt = brp_mysqli_num_rows($result);
	
	$info_stock['reserve_date']	=date('Y-m-d');
	$info_stock['product_id']	=$product_id;
	$info_stock['base_unit']	=$base_unit;
	$info_stock['base_stock']	=$base_stock;
	$info_stock['convert_unit']	=$conv_unit;
	$info_stock['convert_stock']=$con_stock;
	$info_stock['stock_flage']	=1;
	$info_stock['ref_name']		=$chalan_type;
	$info_stock['ref_id']		=$returnable_trn_id;
	$info_stock['stock_id']		=$stock_id;
	$info_stock['godown_id']	=$godown_id;
	$info_stock['cdate']		=date('Y-m-d H:i:s');
	$info_stock['user_id']		=$_SESSION['user_id'];
	$info_stock['company_id']	=$_SESSION['company_id'];
	$info_stock['branch_id']	=$branch_id;

	//var_dump($info_stock);
	$prev_stock = 0;
	$prev_conv_stock = 0; 
	if($cnt > 0){
		$row = brp_mysqli_fetch_assoc($result);
		$prev_stock = $row['base_stock'];
		$prev_conv_stock = $row['convert_stock'];
		$update_id=update_record('tbl_reserve_stock',$info_stock,"reserve_id=".$row['reserve_id'] , $dbcon);
	}else{
		$inserid=add_record('tbl_reserve_stock', $info_stock, $dbcon);	
	}

	$que1="select base_unit,base_stock,used_base_stock,convert_unit,convert_stock,used_convert_stock,godown_id,customer_id,ref_id from tbl_stock_trn as ta where stock_id=".$stock_id;
	$rs_di1=$dbcon->query($que1);
	$re1=brp_mysqli_fetch_assoc($rs_di1);


	$used_base_stock=$re1['used_base_stock']+$base_stock;
	$used_convert_stock=$re1['used_convert_stock']+$con_stock;
	
	$upd_info_stock['used_base_stock']		= $used_base_stock - $prev_stock;
	$upd_info_stock['used_convert_stock']	= $used_convert_stock - $prev_conv_stock;
	
	$updatetrnid=update_record('tbl_stock_trn',$upd_info_stock,"stock_id=".$stock_id , $dbcon);
}
?>

