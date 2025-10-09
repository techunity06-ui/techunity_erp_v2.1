<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    INVENTORY_STOCK_TRANSFER_LIST_SLUG_VIEW,INVENTORY_STOCK_TRANSFER_LIST_SLUG_CREATE,INVENTORY_STOCK_TRANSFER_LIST_SLUG_UPDATE,INVENTORY_STOCK_TRANSFER_LIST_SLUG_DELETE,INVENTORY_STOCK_TRANSFER_LIST_SLUG_APPROVE
]);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);*/

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where=''; 
		$where.=" and stock_transfer_doc_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND stock_transfer_doc_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$appData = array();
		$i=1;
		$aColumns = array('stock_transfer_id','stock_transfer_doc_no', 'to_god.gd_name as to_godown' , 'from_god.gd_name as from_godown', 'stock_transfer_doc_date','remark','approve_status');
		$sIndexColumn = "stock_transfer_id";
		$isWhere = array("status = 0".$where.check_user('stock'));
		$sTable = "tbl_stock_transfer as stock";
		$isJOIN = array("left join mst_godown as to_god on to_god.gd_id=stock.to_godown_id","left join mst_godown as from_god on from_god.gd_id=stock.from_godown_id");
		$hOrder = "stock.stock_transfer_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			
			$edit_btn=''; $delete_btn=''; $view='';$apprv_btn='';

			$row_data = array();
			$row_data[] = $row['stock_transfer_doc_no'];
			$row_data[] = date('d M, Y',strtotime($row['stock_transfer_doc_date']));
			$row_data[] = $row['to_godown'];
			$row_data[] = $row['from_godown'];
			
			if($row['approve_status'] == '1'){
		  		$row_data[] ='<button class="btn btn-xs btn-success" data-original-title="Approved" data-toggle="tooltip" data-placement="top">Approved</button>';
		  	}else if($row['approve_status'] == '2'){
		  		$row_data[] ='<button class="btn btn-xs btn-danger" data-original-title="Rejected" data-toggle="tooltip" data-placement="top">Rejected</button>';
		  	}
		  	else{
		  		$row_data[] ='<button class="btn btn-xs btn-warning" data-original-title=" Pending" data-toggle="tooltip" data-placement="top"> Pending </button>';
		  	}  
		  	if($row['approve_status'] == '0'){
		  		// if(in_array(INVENTORY_STOCK_TRANSFER_LIST_SLUG_APPROVE,$bulkAccessArray)){
		  		$apprv_btn='<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Stock Transfer" 	data-toggle="tooltip" data-placement="top" onClick="open_stock_transfer_model('.$row['stock_transfer_id'].',\''.$row['stock_transfer_doc_no'].'\')"><i class="fa fa-exclamation-triangle"></i></button>';
		  	// }
   			
				if(in_array(INVENTORY_STOCK_TRANSFER_LIST_SLUG_UPDATE,$bulkAccessArray)){
					$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'stock_transfer_edit/'.$row['stock_transfer_id'].'"><i class="fa fa-pencil"></i></a>'; 
					
				}
				if(in_array(INVENTORY_STOCK_TRANSFER_LIST_SLUG_DELETE,$bulkAccessArray)){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_gd_tranfer('.$row['stock_transfer_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				}  
			}
			$trnsfer_print='';
			$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
				$rels=mysqli_fetch_assoc($menusql);
				$menu_show_permissions = explode(",",$rels['print_permission']);
				$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 21 AND approve_status = 1 AND status = 0 ORDER BY priority");
				while($res = mysqli_fetch_assoc($sql)){
					if(in_array($res['id'],$menu_show_permissions)) {
						$trnsfer_print.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['stock_transfer_id'].'?'.time().'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';
					}
				}
			/* $view='<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.'grn_view/'.$row['grn_id'].'"><i class="fa fa-eye"></i></a> ';
			 */
			$row_data[] = $edit_btn.' '.$delete_btn.' '.$view.' '.$apprv_btn.' '.$trnsfer_print;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		
		//$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id='8'");
		update_common_no($dbcon,46);
		$info['stock_transfer_doc_no']				= $POST['transfer_no'];
		$info['stock_transfer_doc_date']			= date('Y-m-d',strtotime($POST['transfer_date']));
		$info['from_godown_id']						= $POST['from_godown_id'];
		$info['to_godown_id']						= $POST['to_godown_id'];
		$info['from_branch_id']						= $POST['from_branch_id'];
		$info['to_branch_id']						= $POST['to_branch_id'];
		$info['remark']								= $_POST['remark'];
		//$info['ref_no']				= $_POST['request_no'];
		
		$info['cdate']				= date("Y-m-d H:i:s"); 
		$info['user_id']			= $_SESSION['user_id'];
		$info['company_id']			= $_SESSION['company_id'];
		$inserpoid=add_record('tbl_stock_transfer', $info, $dbcon);
			

		if($inserpoid){	
			$arr['msg']="1";	

			$info1['status']		= 0; 
			$info1['stock_transfer_id']		= $inserpoid; 
			
			$updateid=update_record('tbl_stock_transfer_trn', $info1,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			if($updateid){
				// RESERVE STOCK ENTRY
				$qry = "select * from tbl_stock_transfer_trn where status = 0 and stock_transfer_id = " . $inserpoid;
				$result=$dbcon->query($qry);

				while($row = brp_mysqli_fetch_assoc($result)){
					$reserve_qty = $row['stock_qty'];
					$reserve_unit = $row['stock_unit'];



					 $sel_stock = "select * from tbl_stock_transfer_batch_stock_tmp where status=1 and stock_transfer_trn_id=".$row['stock_transfer_trn_id'];
			$sel_stock_rs = $dbcon->query($sel_stock);

			$sel_pro = "select * from product_mst where product_status=0 and product_id=".$row['product_id'];
				$sel_pro_rs = $dbcon->query($sel_pro);
				$sel_pro_rel = brp_mysqli_fetch_assoc($sel_pro_rs);
				$cnt_stock_temp = brp_mysqli_num_rows($sel_stock_rs);
				if($cnt_stock_temp > 0){	
					while($sel_stock_rel=brp_mysqli_fetch_assoc($sel_stock_rs)){
						if($sel_stock_rel['unitid']==$sel_pro_rel['product_conv_unit']){
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

						
						item_reserve_stock_entry($dbcon,$sel_stock_rel['product_id'],$sel_pro_rel['product_base_unit'],$sel_pro_rel['product_conv_unit'],$base_stock,$con_stock,"stock_transfer_trn",$sel_stock_rel['stock_transfer_trn_id'],$sel_stock_rel['stock_id'],$rel_stock_1['godown_id'],$rel_stock_1['branch_id']);
					}
				}else{
					$query_dstock="SELECT i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i
							where stock_status=0 AND stock_flage=1 AND i.branch_id = ".$POST['from_branch_id']." AND product_id = ".$row['product_id']." AND i.godown_id=".$row['godown_id'];
					$result_dstock=$dbcon->query($query_dstock);

					while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
						if($row_dstock['convert_unit']==$reserve_unit){
							$pending_stock=$row_dstock['pending_conv_stock'];
						}else{
							$pending_stock=$row_dstock['pending_base_stock'];	
						}

						if($reserve_qty>0){
							if($pending_stock>=$reserve_qty){
								$rqty=$reserve_qty;
								$reserve_qty=$reserve_qty-$reserve_qty;
							}else{
								$rqty=$pending_stock;
								$reserve_qty -= $pending_stock;
							}

							$que="select * from product_mst as ta where product_id=".$row['product_id'];
							$rs_di=$dbcon->query($que);
							$re=brp_mysqli_fetch_assoc($rs_di);
							
							if($re['product_conv_unit']==$reserve_unit){
								$type="base_unit";
								$con_stock=$rqty;
								$base_stock=convert_stock_new($dbcon,$rqty,$row['product_id'],$type);
							}else{
								$type="conv_unit";
								$base_stock=$rqty;
								$con_stock=convert_stock_new($dbcon,$rqty,$row['product_id'],$type);
							}

							// $info_rese['reserve_date']		= date('Y-m-d');
							// $info_rese['product_id']		= $row['product_id'];
							// $info_rese['godown_id']			= $row['godown_id'];
							// $info_rese['base_unit']			= $re['product_base_unit'];
							// $info_rese['base_stock']		= $base_stock;
							// $info_rese['convert_unit']		= $re['product_conv_unit'];
							// $info_rese['convert_stock']		= $con_stock;
							// $info_rese['stock_flage']		= "1";
							// $info_rese['request_id']		= "";
							// $info_rese['ref_name']			= "stock_transfer_trn";
							// $info_rese['ref_id']			= $row['stock_transfer_trn_id'];
							// $info_rese['stock_id']			= $row_dstock['stock_id'];
							
							// $info_rese['cdate']				= date("Y-m-d H:i:s");
							// $info_rese['user_id']			= $_SESSION['user_id'];
							// $info_rese['company_id']		= $_SESSION['company_id'];		
							// $info_rese['customer_id']		= $POST['customer_id'];		
							// // var_dump($info_rese);					
							// $reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$POST['from_branch_id']);

							// if($row_dstock['base_unit']==$re['product_base_unit']){
							// 	$used_base_stock=$row_dstock['used_base_stock']+$base_stock;
							// 	$used_convert_stock=$row_dstock['used_convert_stock']+$con_stock;
							// }else{
							// 	$used_base_stock=$row_dstock['used_convert_stock']+$con_stock;
							// 	$used_convert_stock=$row_dstock['used_base_stock']+$base_stock;
							// }
							
							// $info_stock['used_base_stock']		= $used_base_stock;
							// $info_stock['used_convert_stock']	= $used_convert_stock;
							
							// $updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$info_rese['stock_id'] , $dbcon);

							item_reserve_stock_entry($dbcon,$row['product_id'],$re['product_base_unit'],$re['product_conv_unit'],$base_stock,$con_stock,"stock_transfer_trn",$row['stock_transfer_trn_id'],$row_dstock['stock_id'],$row['godown_id'],$row_dstock['branch_id']);
						}
						}
					}
				}
			}
		}
		else{
			$arr['msg']="0";
		}
		$arr['back']=$POST['back'];
		
		echo json_encode($arr);	
		
	}
	else if(strtolower($POST['mode']) == "edit") {

		$info['stock_transfer_doc_no']				= $POST['transfer_no'];
		$info['stock_transfer_doc_date']			= date('Y-m-d',strtotime($POST['transfer_date']));

		$info['from_godown_id']						= $POST['from_godown_id'];
		$info['to_godown_id']						= $POST['to_godown_id'];
		$info['remark']								= $_POST['remark'];
			
		$info['cdate']								= date("Y-m-d H:i:s"); 
		$info['user_id']							= $_SESSION['user_id'];
		$info['company_id']							= $_SESSION['company_id']; 
		
		$updateid=update_record('tbl_stock_transfer', $info,"stock_transfer_id=".$POST['eid'] , $dbcon);
		 
		
		
		if($updateid){	
			$arr['msg']="1";		
									
		}
		else{
			$arr['msg']="0";
		}
		$arr['back']=$POST['back'];
		echo json_encode($arr);	
	}
	else if(strtolower($POST['mode']) == "fieldadd") {

		$get_unit  = get_product_unit($dbcon,$POST['product_id']);

		$info1['godown_id']			= $POST['godown_id'];
		$info1['product_id']			= $POST['product_id'];
		$info1['stock_qty']				= $POST['transfer_qty'];
		$info1['stock_unit']			= $POST['unit_id'];
		if(strtolower($POST['unit_type']) == 'base_unit'){		
			$info1['base_qty']			= $POST['transfer_qty'];	
			$info1['base_unit']			= $POST['unit_id'];

			$info1['conv_qty']			= convert_stock($dbcon,$info1['base_qty'],$POST['product_id'],strtolower($POST['unit_type']));
			$info1['conv_unit']			= $get_unit['product_conv_unit'];
		}else{
			$info1['conv_qty']			= $POST['transfer_qty']; 
			$info1['conv_unit']			= $POST['unit_id'];

			$info1['base_qty']			= convert_stock($dbcon,$info1['conv_qty'],$POST['product_id'],strtolower($POST['unit_type']));
			$info1['base_unit']			= $get_unit['product_base_unit'];
		}
		 
		$info1['user_id']				= $_SESSION['user_id'];
		$info1['company_id']			= $_SESSION['company_id'];

		$table='tbl_stock_transfer_trn';$tableid='stock_transfer_trn_id';
		if(!empty($POST['eid'])) {
			$info1['stock_transfer_id']= $POST['eid'];
		}
		else{
			$info1['status']= 3;
		}
		
		if(empty($POST['edit_id'])) {
			//var_dump($info1);
			$inserid=add_record($table, $info1, $dbcon);

			if($inserid){
				$sel_itrn = $dbcon->query("SELECT * FROM tbl_stock_transfer_batch_stock_tmp where status=0 and product_id=".$POST['product_id']);
				
				if($sel_itrn->num_rows > 0) {
					$infobatch['stock_transfer_trn_id']= $inserid;
					$infobatch['status']= 1;
					
					while($r_itrn=brp_mysqli_fetch_array($sel_itrn))
					{
						$updateinvoicetrnid=update_record('tbl_stock_transfer_batch_stock_tmp', $infobatch,$r_itrn['product_id']."=".$POST['product_id'] , $dbcon);
					}
				}
			}
		}
		else {
			$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
		}
	}
	else if(strtolower($POST['mode'])== "load_stock_transfer_trn_data") {
		
		if($POST['eid']){
			$query="select trn.*,pro.product_name,unit.unit_name,gd.gd_name from tbl_stock_transfer_trn as trn
			left join product_mst as pro on pro.product_id = trn.product_id
			left join unit_mst as unit on unit.unitid = trn.stock_unit
			left join mst_godown as gd on gd.gd_id = trn.godown_id
			where status=0 and trn.stock_transfer_id=".$POST['eid'];
		}
		else{
			$query="select trn.*,pro.product_name,unit.unit_name,gd.gd_name from tbl_stock_transfer_trn as trn
			left join product_mst as pro on pro.product_id = trn.product_id
			left join unit_mst as unit on unit.unitid = trn.stock_unit
			left join mst_godown as gd on gd.gd_id = trn.godown_id
			where status=3 and trn.user_id=".$_SESSION['user_id'];
		}
		
		$result=$dbcon->query($query);
		echo ' <div class="form-group">
					  <div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th class="text-center grn" width="10%">Sr No</th>
							<th class="text-center" width="20%">Product Name</th>
							<th class="text-center" width="20%">Godown</th>
							<th class="text-center" width="20%">Transfer Qty</th>
							<th class="text-center" width="10%">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
					echo '<tr> 
					<td style="vertical-align:top;">
						'.$i.'
					</td>
					<td style="vertical-align:top;">
						'.$rel['product_name'].'
					</td>
					<td style="vertical-align:top;">
						'.$rel['gd_name'].'
					</td>
					
					<td style="vertical-align:top;" class="text-center">
						'.$rel['stock_qty'].' '.$rel['unit_name'].'
					</td>
					<td style="vertical-align:top"> 
						<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_grn_data('.$rel['stock_transfer_trn_id'].')"><i class="fa fa-pencil"></i></button>
						<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_stock_transfer_data('.$rel['stock_transfer_trn_id'].')">X</button>
					</td>	
				</tr>';
				$i++;
			}
		}
		else{
			echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
		}
		echo '</table>			 
						</div>
                           </div>	';
	}
	else if(strtolower($POST['mode'])== "preedit") {
		$q = $dbcon -> query("SELECT mst.* FROM tbl_stock_transfer_trn as mst WHERE stock_transfer_trn_id = '$POST[edit_id]'");
		$r = $q->fetch_assoc();
		
		echo json_encode($r);
	}
	else if(strtolower($POST['mode'])== "delete_data") {
		$row=array();
		$info['status']=2;	
		$updateid=update_record('tbl_stock_transfer_trn', $info, "stock_transfer_trn_id=".$POST['id'] , $dbcon);
		
		//$UPD_PO=upd_grn_used_status($dbcon, $POST['purchaseorder_id'], 2);
		
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "delete_gd_tranfer") {
		$row=array();
		$info['status']=2;
		$updateid=update_record('tbl_stock_transfer', $info, "stock_transfer_id=".$POST['id'] , $dbcon);
		
		$update_id=update_record('tbl_stock_transfer_trn', $info, "stock_transfer_id=".$POST['id'] , $dbcon);
		
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"godown_stock_tranfer",3,"tbl_stock_transfer",$POST['id']);	
		
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	} 


	

	else if(strtolower($POST['mode'])== "load_product_unit")
	{
		$query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name, promst.batch_wise_stock_manage 
			FROM product_mst as promst
			left join unit_mst as umst on umst.unitid=promst.product_base_unit
			left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
			WHERE product_id=".$POST['product_id'];
		
		$rs_type1=$dbcon->query($query1);
		$row1=brp_mysqli_fetch_assoc($rs_type1);

			if($row1['product_base_unit']!=$row1['product_conv_unit']){
    			$row1['unit_status']="1";
    			$opt='<option  value="'.$row1['product_base_unit'].'" data-unit_type = "base_unit">'.$row1['base_unit_name'].'</option>';
    			$opt .='<option  value="'.$row1['product_conv_unit'].'" data-unit_type = "conv_unit">'.$row1['convert_unit_name'].'</option>';
    		}else{
    			$row1['unit_status']="0";
    			$opt='<option value="'.$row1['product_base_unit'].'" data-unit_type = "base_unit">'.$row1['base_unit_name'].'</option>';
    		}

    		$row1['unit_option']=$opt;

			//$row1['qye']=$query1;

		echo json_encode($row1);
	}
	
	else if(strtolower($POST['mode'])== "load_productdetail") {
		$purchaseorder_id=$POST['purchaseorder_id'];
		$product_id=$POST['product_id'];
		$query="select trn.*,main_grn_qty from tbl_purchaseordertrn as trn
		left join (SELECT purchaseorder_id,product_id,sum(product_qty) as main_grn_qty FROM tbl_grn_trn as chtrn where chtrn.grn_trn_status!=2 and chtrn.purchaseorder_id=".$purchaseorder_id." group by chtrn.product_id,chtrn.purchaseorder_id) as chtrn on chtrn.product_id=trn.product_id
		where trn.purchaseordertrn_status=0 and trn.purchaseorder_id=".$purchaseorder_id." and trn.product_id=".$product_id;
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$rel['pending_qty']=floatval($rel['product_qty'])-floatval($rel['main_grn_qty']);
		
		$product_qc=explode(",",get_pro_field($dbcon,$product_id,'product_setting_check'));
		if(in_array("product_qc",$product_qc))
		{
			$rel['product_qc']=0;
		}
		else
		{
			$rel['product_qc']=1;
		}
		
		
		echo json_encode($rel);
	}
	else if(strtolower($POST['mode'])== "get_child_godown_list") {
		$parent_gd_id = $POST['parent_id'];
		$godown_id = $POST['godown_id'];

		$str = get_last_node_godown_list($dbcon,$godown_id,$parent_gd_id);

		echo $str;
	}

	
	else if(strtolower($POST['mode'])=="load_stock_qty")
		{
			$product_id=$POST['product_id'];
			
			$get_pro_type_qry="SELECT product_type, product_base_unit FROM product_mst 
			-- left join tbl_stock_transfer_trn as trn on trn.product_id = product_mst.product_id
			-- left join tbl_stock_transfer as stock on stock.stock_transfer_id = trn.stock_transfer_id
			WHERE product_id=".$product_id;
			$get_pro_type_rel=mysqli_fetch_assoc($dbcon->query($get_pro_type_qry));
			$product_type_arr = array("0", "1", "2", "3", "4", "5", "6", "7", "9", "-1");
			if (in_array($get_pro_type_rel['product_type'], $product_type_arr)){
				if(!empty($POST['unit_id'])){
					$unit_id=$POST['unit_id'];
				}else{
					$unit_id=$get_pro_type_rel['unit_id'];
				}
				// $transfr_data = load_stock_transfer_trn_data($dbcon,$product_id,$unit_id,$godown_id);
				
				$current_stock = get_current_godown_stock_new($dbcon, $product_id, $unit_id, $POST['godown_id']);
				$rstock=reserve_stock($dbcon,$POST['product_id'],$POST['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$stock_transfer_id,$branch_id,$is_store_approval,$p_id,$POST['godown_id'],$stock_id);
			
				$stock=$current_stock-$rstock;	
				echo $stock;
				
			}
			else{
				echo 0;
			}
			
		}
		
		else if(strtolower($POST['mode'])== "load_available_stock_godown")
		{
			$product_id=$POST['product_id'];
			// $godown_id =$POST['godown_id'];
			$gd_qry = "SELECT tbl_stock_trn.product_id,tbl_stock_trn.godown_id, tbl_stock_trn.stock_status, godown.gd_id, godown.gd_name FROM tbl_stock_trn
			left join mst_godown as godown on godown.gd_id = tbl_stock_trn.godown_id
			WHERE stock_status = 0 and tbl_stock_trn.product_id=".$product_id." GROUP BY godown.gd_id";
			$gd_res = $dbcon->query($gd_qry);
			$g_d_res = mysqli_fetch_assoc($dbcon->query($gd_qry));
			$godown_stock_data = array();
			$row = '';
			while($row1 = mysqli_fetch_assoc($gd_res)) {	
				$row .= '<option value = "'.$row1['gd_id'].'">'.$row1['gd_name'].'</option>';
			}
			
			$godown_stock_data['from_godown_id'] = $row; 
			echo json_encode($godown_stock_data);
		}


		else if(strtolower($POST['mode'])=="get_godown_branch"){
			$godown_id = $POST['godown_id'];

			$qry = "SELECT branch_id FROM mst_godown WHERE gd_id = " . $godown_id;
			// $qry = "SELECT godown_id FROM tbl_stock_trn WHERE godown_id = " . $godown_id;
			$rs=$dbcon->query($qry);
			$rel=brp_mysqli_fetch_assoc($rs);

			echo $rel['branch_id'];

		}
		else if(strtolower($POST['mode']) == "load_stock_transfer_details") {

			$qry="select st.stock_transfer_id,st.stock_transfer_doc_no,st.stock_transfer_doc_date,f.gd_name as from_godown,t.gd_name as to_godown from tbl_stock_transfer st
			left join mst_godown as f on st.from_godown_id =f.gd_id
			left join mst_godown as t on st.to_godown_id =t.gd_id
			
			where st.status = 0 and st.stock_transfer_id =".$POST['stock_transfer_id'];
			$rel=mysqli_fetch_assoc($dbcon->query($qry));
			
		//Party PO Details Table View
			$str='';
			$str.='<div class="form-group">
			<table class="display table table-bordered table-striped">
			<tr>
			<td><strong>Stock Transfer Doc No:</strong> '.$rel['stock_transfer_doc_no'].'</td>
			<td><strong>Stock Transfer Date:</strong> '.date('d-m-Y',strtotime($rel['stock_transfer_doc_date'])).'</td>
			</tr>
			<tr>
			<td><strong>From Godown:</strong> '.$rel['from_godown'].'</td>
			<td><strong>To Godown:</strong> '.$rel['to_godown'].'</td>
			</tr>';
			$str.='</table></div><hr/>';

			 $query = "select trn.*,gd.gd_name,pro.product_name,u.unit_name from tbl_stock_transfer_trn trn 
			left join mst_godown as gd on trn.godown_id =gd.gd_id
			left join product_mst as pro on pro.product_id =trn.product_id
			left join unit_mst as u on u.unitid =trn.stock_unit
			where  trn.status = 0 and trn.stock_transfer_id = ". $POST['stock_transfer_id'];

			$result=$dbcon->query($query);
			$cnt=mysqli_num_rows($result);
			
			if($cnt>0){
				$str.='<div class="form-group">';
				$str .= '<table class="display table table-bordered table-striped" style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
						<tr>
							<th style="border:0.5px #444 solid;text-align:center;" >Product</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Godown</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Transfer Qty</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Unit</th>
						</tr>';
					while($rel3=mysqli_fetch_assoc($result)){ 
						$str .= '<tr>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel3['product_name'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel3['gd_name'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel3['stock_qty'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel3['unit_name'].'</td>
							
						</tr>';	
					}
					$str .= '</table></div>';	
					
			}
			
			$arr['mod_stock_div_sec'] = $str;
			
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "add_stock_apprv_hist") {
           
            $check_hist_qry = "SELECT log.stock_aprv_log_id, usr.user_name, log.approve_status, log.approve_remark, log.created_at, log.user_id 
                    FROM tbl_stock_transfer_aprv_log as log 
					left join users as usr on usr.user_id=log.user_id 
                    where log.stock_aprv_log_status=0 and log.stock_transfer_id=".$POST['stock_transfer_id']." and log.user_id = ".$_SESSION['user_id']."
                    order by log.stock_aprv_log_id desc limit 1";
            $result = brp_mysqli_query($dbcon,$check_hist_qry);
            $history_data = brp_mysqli_fetch_all($result,MYSQLI_ASSOC);

            if($history_data[0]['approve_status'] !== $POST['approve_status']) {
                $info1['approve_remark']	= $POST['approve_remark'];
                $info1['approve_status']	= $POST['approve_status'];
                $info1['stock_transfer_id']             = $POST['stock_transfer_id'];
                $info1['user_id']		= $_SESSION['user_id'];
                $info1['company_id']	= $_SESSION['company_id'];

                $insert_id=add_record("tbl_stock_transfer_aprv_log", $info1, $dbcon);

                if($insert_id){
                	$query = "SELECT trn.*,trnsf.to_godown_id  from tbl_stock_transfer_trn AS trn 
					left join tbl_stock_transfer as trnsf on trnsf.stock_transfer_id = trn.stock_transfer_id
					where trn.status = 0 and trn.stock_transfer_id = ". $POST['stock_transfer_id'];
					$result1=$dbcon->query($query);
                	if($POST['approve_status'] == 1){
                		$infoso['approve_status'] = 1; // approve

                		while($row = brp_mysqli_fetch_assoc($result1)){
							$qry_r = "SELECT * from tbl_reserve_stock where stock_flage = 1 and ref_name='stock_transfer_trn' and product_id = ". $row['product_id']." and ref_id=".$row['stock_transfer_trn_id'];
							$res2 = $dbcon->query($qry_r);
							while($row1 = brp_mysqli_fetch_assoc($res2)){
									$info_rese['reserve_date']		= date('Y-m-d');
									$info_rese['product_id']		= $row1['product_id'];
									$info_rese['godown_id']			= $row1['godown_id'];
									$info_rese['base_unit']			= $row1['base_unit'];
									$info_rese['base_stock']		= $row1['base_stock'];
									$info_rese['convert_unit']		= $row1['convert_unit'];
									$info_rese['convert_stock']		= $row1['convert_stock'];
									$info_rese['stock_flage']		= "2";
									$info_rese['request_id']		= "";
									$info_rese['ref_name']			= "stock_transfer_trn";
									$info_rese['ref_id']			= $row1['ref_id'];
									$info_rese['stock_id']			= $row1['stock_id'];
									$info_rese['branch_id']			= $row1['branch_id'];
									$info_rese['cdate']				= date("Y-m-d H:i:s");
									$info_rese['user_id']			= $_SESSION['user_id'];
									$info_rese['company_id']		= $_SESSION['company_id'];		
									
									// var_dump($info_rese);					
									$reserve_id=add_record('tbl_reserve_stock',$info_rese, $dbcon);

								$qry_stk = "SELECT tbl_stock_trn.* from tbl_stock_trn where stock_id =".$row1['stock_id'];
								$res3 = $dbcon->query($qry_stk);
								$r3 = brp_mysqli_fetch_assoc($res3);

								$upd_stock['used_base_stock'] = $r3['used_base_stock'] + $row1['base_stock'];
								$upd_stock['used_convert_stock'] = $r3['used_convert_stock'] + $row1['convert_stock'];

								$updateid=update_record('tbl_stock_trn', $upd_stock,"stock_id=".$row1['stock_id'], $dbcon);

								// $trn_qry = "SELECT tbl_stock_transfer_trn.* FROM tbl_stock_transfer_trn where product_id =". $r3['product_id'] ." AND $r3;

								add_stock($dbcon,$row1['product_id'],$row1['base_unit'],$info_rese['reserve_date'],"stock_transfer_trn",$info_rese['ref_id'],$row1['godown_id'],$row1['base_stock'],"2",$r3['branch_id'],"","","",$r3['batch_id'],$r3['batch_no'],$r3['base_rate'],$r3['conv_rate']);	// minus data for from_godown
								
								add_stock($dbcon,$row1['product_id'],$row1['base_unit'],$info_rese['reserve_date'],"stock_transfer_trn",$info_rese['ref_id'],$row['to_godown_id'],$row1['base_stock'],"1",$r3['branch_id'],"","","",$r3['batch_id'],$r3['batch_no'],$r3['base_rate'],$r3['conv_rate']);		// add data for to_godown
							}
						}
                	}else {
                	 	$infoso['approve_status'] = 2; // reject

						while($row = brp_mysqli_fetch_assoc($result1)){
							 $qry_r = "select * from tbl_reserve_stock where stock_flage = 1 and ref_name='stock_transfer_trn' and product_id = ". $row['product_id']." and ref_id=".$row['stock_transfer_trn_id'];
							$res2 = $dbcon->query($qry_r);

							while($row1 = brp_mysqli_fetch_assoc($res2)){
								$r_stock['stock_status'] = 2;
                				$updateid=update_record('tbl_reserve_stock', $r_stock,"stock_flage = 1 and ref_name='stock_transfer_trn' and ref_id=".$row['stock_transfer_trn_id'], $dbcon);

                				$qry_stk = "select * from tbl_stock_trn where stock_id =".$row1['stock_id'];
								$res3 = $dbcon->query($qry_stk);
								$r3 = brp_mysqli_fetch_assoc($res3);

								$upd_stock['used_base_stock'] = $r3['used_base_stock'] - $row1['base_stock'];
								$upd_stock['used_convert_stock'] = $r3['used_convert_stock'] - $row1['convert_stock'];

								$updateid=update_record('tbl_stock_trn', $upd_stock,"stock_id=".$row1['stock_id'], $dbcon);
							}
							
						}
                	 	
                	}
                    $updateid=update_record('tbl_stock_transfer', $infoso,"stock_transfer_id=".$POST['stock_transfer_id'] , $dbcon);
                }
                echo TRUE;
            } else {
                echo FALSE;
            }
	}
	else if(strtolower($POST['mode']) == "load_stock_hist_datatable") {
		$where='';
        if($POST['stock_transfer_id']){
            $where.="  and log.stock_transfer_id=".$POST['stock_transfer_id'];
        }

		$appData = array();
        $i=1;
        $aColumns = array('log.stock_aprv_log_id','log.stock_transfer_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.created_at', 'log.user_id');
        $sIndexColumn = "log.stock_aprv_log_id";
        $isWhere = array("log.stock_aprv_log_status = 0 ".$where." ");
        $sTable = "tbl_stock_transfer_aprv_log as log";
        $isJOIN = array('left join users as usr on usr.user_id=log.user_id');
        $hOrder = "log.stock_aprv_log_id desc";
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
				$row_data[] = '<div class="external-event label label-danger ui-draggable" style="cursor:auto;">Rejected</div>';
			}
			
			$row_data[] = nl2br($row['approve_remark']);
			$row_data[] = date("d-M-Y h:i A",strtotime($row['created_at']));
			
			$appData[] = $row_data;
			$id++;
			//print_r($row_data);
		}
		$output['aaData'] = $appData;
		//print_r($output);
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode'])== "get_batch_qty"){
		$stock_id = $POST['batch_no'];
		$gstock=0;$rstock=0;
		$batch_no=$POST['batch_no'];
		$gstock=get_current_godown_stock_new($dbcon,$POST['product_id'],$POST['unit_id'],$POST['st_godown_id'],$branch_id,$stock_id);

		$rstock=reserve_stock($dbcon,$POST['product_id'],$POST['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$stock_id);


		$stock=$gstock-$rstock;
		echo $stock;
	}
	else if(strtolower($POST['mode'])== "batch_stock_model_open"){

			/*$query="SELECT * FROM `tbl_stock_trn` WHERE stock_status = 0 and stock_flage = 1 and `product_id` = ".$POST['product_id']." and batch_no != '' group by batch_no";*/
			$query="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i
							where stock_status=0 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and product_id = ".$POST['product_id']." and batch_no != '' group by batch_no";
			$rs_batch=$dbcon->query($query);
			$str= '<option value="">Choose Batch No</option>';
			while($rel=brp_mysqli_fetch_assoc($rs_batch))
			{	
				if($rel['pending_base_stock'] > 0){
					$str.= '<option value="'.$rel['stock_id'].'" data-stock="'.$rel['base_stock'].'" >'.$rel['batch_no'].'</option>';
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
			<input type="number" min="0" class="form-control numbersOnly" name="qtyforbatch"  id="qtyforbatch" />
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
			
			if(!empty($POST['edit_id'])){
				$str = " and bst.stock_transfer_trn_id=".$POST['edit_id']." and bst.status=1 ";
			}else{
				$str = " and bst.status=0";
			}
			$appData = array();
			$i=1;
			$aColumns = array('bst.qty','st.batch_no','bst.batch_stk_id');
			$sTable = "tbl_stock_transfer_batch_stock_tmp as bst";			
			$isJOIN = array('left join `tbl_stock_trn` as st on st.stock_id=bst.stock_id');
			$sIndexColumn = "st.stock_id";
			$where = "  st.product_id='".$POST['product_id']."' ".$str." ";
			$isWhere = array($where);
			$hOrder = "st.stock_id desc";
			include($path.'include/pagging.php');
			$id=1;
			$edit = $delete = '';
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['batch_no'];
				$row_data[] = $row['qty'];
				$delete='';

				
				$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_batch_stock_entry('.$row['batch_stk_id'].')"><i class="fa fa-trash-o"></i></button>';

				
				$row_data[] = $delete;

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode'])== "add_batch_qty"){

			if(!empty($POST['edit_id'])){
				$str = " and stock_transfer_trn_id=".$POST['edit_id']." and status=1 ";
				$info['stock_transfer_trn_id']   = $POST['edit_id'];
				$info['status']   = 1;
			}else{
				$str = " and stock_transfer_trn_id=0 and status=0 ";
			}

			$tr = $dbcon -> query("SELECT stock_id FROM tbl_stock_transfer_batch_stock_tmp where stock_id=".$POST['stock_id']." ".$str." ");
			if($tr->num_rows > 0) {
				$row['res'] = '-1';
			} else {
				$info['product_id']   = $POST['product_id'];
				$info['stock_id']   = $POST['stock_id'];
				$info['qty']   		= $POST['qty'];
				$info['unitid']   	= $POST['unit_id'];
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];		

				$inserbatchstockid=add_record('tbl_stock_transfer_batch_stock_tmp', $info, $dbcon);

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
			if(!empty($POST['edit_id'])){
				$str = " and bst.stock_transfer_trn_id=".$POST['edit_id']." and bst.status=1 ";
			}else{
				$str = " and bst.stock_transfer_trn_id=0 and bst.status=0 ";
			}
			$qry2="SELECT sum(bst.qty) as qty FROM tbl_stock_transfer_batch_stock_tmp as bst left join `tbl_stock_trn` as st on st.stock_id=bst.stock_id where st.product_id=".$POST['product_id']." ".$str." ";

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
			
			$updateid=update_record("tbl_stock_transfer_batch_stock_tmp", $info, "batch_stk_id=".$POST['batchstockid'] , $dbcon);
			
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}	
	
function upd_grn_used_status($dbcon,$purchaseorder_id,$flag){
	if($flag=='1'){
		//get Same Qty Data
		$get_dt_qry="SELECT SUM(potrn.product_qty) as po_qty,(SELECT SUM(grntrn.product_qty) FROM `tbl_grn_trn` as grntrn where grntrn.grn_trn_status=0 and grntrn.purchaseorder_id=".$purchaseorder_id." and grntrn.product_id=potrn.product_id) as grn_qty FROM `tbl_purchaseordertrn` as potrn where potrn.purchaseordertrn_status=0 and potrn.purchaseorder_id=".$purchaseorder_id." group by potrn.product_id";
		$get_dt_rs=$dbcon->query($get_dt_qry);
		$same_qty=true;
		while($get_dt_rel=mysqli_fetch_assoc($get_dt_rs)){
			//compare pending qty
			if($get_dt_rel['po_qty']!=$get_dt_rel['grn_qty']){
				$same_qty=false;
			}
		}
	}
	
	//update PO if all used in GRN
	if($same_qty){
		$upd_po_qry=$dbcon->query("update tbl_purchaseorder set used_grn_status=1 where purchaseorder_id=".$purchaseorder_id);
	}
	else{
		$upd_po_qry=$dbcon->query("update tbl_purchaseorder set used_grn_status=0 where purchaseorder_id=".$purchaseorder_id);
	}
}

function upload_grn_receipt($FILES,$dbcon,$grn_id){
	$cnt=count($_FILES['grn_file']['name']);
	for( $i=0 ; $i < $cnt ; $i++ ) {
		if(!empty($_FILES['grn_file']['tmp_name'][$i])) {
			$rand=rand(0,999999);
			$temp = explode(".", $_FILES["grn_file"]["name"][$i]);
			$extension = strtolower(end($temp));
			$file_name = $_FILES['grn_file']['name'][$i];
			$err = $_FILES["grn_file"]["tmp_name"][$i];
			$file_name = "grn_rec_".$rand.'.'.$extension;
			move_uploaded_file($err,RECEIPT_FILE_UPING.$file_name);
			
			$attch['grn_id']		= $grn_id;
			$attch['grn_file']		= $file_name;
			$attch['cdate']			= date("Y-m-d H:i:s"); 
			$attch['user_id']		= $_SESSION['user_id'];
			$attch['company_id']	= $_SESSION['company_id']; 
			$inserid=add_record('tbl_grn_attch', $attch, $dbcon);
			//return 	$file_name;
		}
	}
}

function get_product_tax($dbcon,$product_amount,$formulaid)
{
	$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid." order by tax_value desc";
	$row=$dbcon->query($qry);
	$rate_total=$total=$product_amount;
	$i=1;
	while($tax=mysqli_fetch_assoc($row))
	{
		$info['tax_name'.$i]=$tax['tax_name'];
		$info['tax_amount'.$i]=$tax_amount=($total)*$tax['tax_value']/100;
		$rate_total+=$tax_amount;
		$i++;
	}
	for($j=$i;$j<=3;$j++)
	{
		$info['tax_name'.$i]='';
		$info['tax_amount'.$i]='';		
	}
	$info['total']=$rate_total;
	return $info;
}


function item_reserve_stock_entry($dbcon,$product_id,$base_unit,$conv_unit,$base_stock,$con_stock,$chalan_type,$returnable_trn_id,$stock_id,$godown_id,$branch_id){
	$info_stock['reserve_date']	=date('Y-m-d');
	$info_stock['product_id']	=$product_id;
	$info_stock['base_unit']	=$base_unit;
	$info_stock['base_stock']	=$base_stock;
	$info_stock['convert_unit']	=$conv_unit;
	$info_stock['convert_stock']	=$con_stock;
	$info_stock['stock_flage']	=1;
	$info_stock['ref_name']		=$chalan_type;
	$info_stock['ref_id']		=$returnable_trn_id;
	$info_stock['stock_id']		=$stock_id;
	$info_stock['godown_id']	=$godown_id;
	$info_stock['cdate']		=date('Y-m-d H:i:s');
	$info_stock['user_id']		=$_SESSION['user_id'];
	$info_stock['company_id']	=$_SESSION['company_id'];
	$info_stock['branch_id']	=$branch_id;

	$inserid=add_record('tbl_reserve_stock', $info_stock, $dbcon);


	$que1="	SELECT base_unit,base_stock,used_base_stock,convert_unit,convert_stock,used_convert_stock,godown_id,customer_id,ref_id from tbl_stock_trn as ta where stock_id=".$stock_id;
	$rs_di1=$dbcon->query($que1);
	$re1=brp_mysqli_fetch_assoc($rs_di1);


	$used_base_stock=$re1['used_base_stock']+$base_stock;
	$used_convert_stock=$re1['used_convert_stock']+$con_stock;
	
	$upd_info_stock['used_base_stock']		= $used_base_stock;
	$upd_info_stock['used_convert_stock']	= $used_convert_stock;
	
	$updatetrnid=update_record('tbl_stock_trn',$upd_info_stock,"stock_id=".$stock_id , $dbcon);
}	
?>