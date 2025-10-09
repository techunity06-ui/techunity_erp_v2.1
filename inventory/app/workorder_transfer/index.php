<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    INVENTORY_WORKORDER_TRANSFER_SLUG_VIEW,INVENTORY_WORKORDER_TRANSFER_SLUG_CREATE,INVENTORY_WORKORDER_TRANSFER_SLUG_READ,INVENTORY_WORKORDER_TRANSFER_SLUG_UPDATE,INVENTORY_WORKORDER_TRANSFER_SLUG_DELETE,INVENTORY_WORKORDER_TRANSFER_LIST_SLUG_APPROVE
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
		$where.=" and wo_stk_transfer_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND wo_stk_transfer_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$appData = array();
		$i=1;
		$aColumns = array('wo_stk_transfer_id','wo_stk_transfer_no', 'wo_stk_transfer_date', 'remark','approved_status','u.user_name');
		$sIndexColumn = "wo_stk_transfer_id";
		$isWhere = array("status = 0".$where.check_user('stock'));
		$sTable = "tbl_workorder_transfer as stock";
		$isJOIN = array("left join users as u on u.user_id=stock.user_id");
		$hOrder = "stock.wo_stk_transfer_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			
			$edit_btn=''; $delete_btn=''; $view='';$apprv_btn='';

			$row_data = array();
			$row_data[] = $row['wo_stk_transfer_no'];
			$row_data[] = date('d M, Y',strtotime($row['wo_stk_transfer_date']));
			$row_data[] = $row['remark'];
			
			if($row['approved_status'] == '1'){
		  		$row_data[] ='<button class="btn btn-xs btn-success" data-original-title="Approved" data-toggle="tooltip" data-placement="top">Approved</button>';
		  	}else if($row['approved_status'] == '2'){
		  		$row_data[] ='<button class="btn btn-xs btn-danger" data-original-title="Rejected" data-toggle="tooltip" data-placement="top">Rejected</button>';
		  	}
		  	else{
		  		$row_data[] ='<button class="btn btn-xs btn-warning" data-original-title=" Pending" data-toggle="tooltip" data-placement="top"> Pending </button>';
		  	} 
		  	$row_data[] = $row['user_name']; 
		  	if($row['approved_status'] == '0'){
		  		 if(in_array(INVENTORY_WORKORDER_TRANSFER_LIST_SLUG_APPROVE,$bulkAccessArray)){
		  		$apprv_btn='<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Stock Transfer" 	data-toggle="tooltip" data-placement="top" onClick="open_workorder_transfer_model('.$row['wo_stk_transfer_id'].',\''.$row['wo_stk_transfer_no'].'\')"><i class="fa fa-exclamation-triangle"></i></button>';
		  	 }
   			
				if(in_array(INVENTORY_WORKORDER_TRANSFER_SLUG_UPDATE,$bulkAccessArray)){
					$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'workorder_transfer_edit/'.$row['wo_stk_transfer_id'].'"><i class="fa fa-pencil"></i></a>'; 
					
				}
				if(in_array(INVENTORY_WORKORDER_TRANSFER_SLUG_DELETE,$bulkAccessArray)){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_gd_tranfer('.$row['wo_stk_transfer_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				}  
			}
			$trnsfer_print='';
			$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
				$rels=mysqli_fetch_assoc($menusql);
				$menu_show_permissions = explode(",",$rels['print_permission']);
				$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 21 AND approved_status = 1 AND status = 0 ORDER BY priority");
				while($res = mysqli_fetch_assoc($sql)){
					if(in_array($res['id'],$menu_show_permissions)) {
						$trnsfer_print.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['wo_stk_transfer_id'].'?'.time().'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';
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
		
		$info['wo_stk_transfer_no']				= $POST['transfer_no'];
		$info['wo_stk_transfer_date']			= date('Y-m-d',strtotime($POST['transfer_date']));
		$info['branch_id']						= $POST['branch_id'];
		$info['remark']								= $_POST['remark'];
		$info['cdate']				= date("Y-m-d H:i:s"); 
		$info['user_id']			= $_SESSION['user_id'];
		$info['company_id']			= $_SESSION['company_id'];
		
		$inserpoid=add_record('tbl_workorder_transfer', $info, $dbcon);
		
		if($inserpoid){
			update_common_no($dbcon,WO_STOCK_TRANSFER);
			$arr['msg']="1";	
			$info1['status']		= 0; 
			$info1['wo_stk_transfer_id']		= $inserpoid; 
			
			$updateid=update_record('tbl_workorder_transfer_trn', $info1,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			if($updateid){
				// RESERVE STOCK ENTRY
				/* $qry = "select * from tbl_workorder_transfer_trn where status = 0 and wo_stk_transfer_id = " . $inserpoid;
				$result=$dbcon->query($qry);

				while($row = brp_mysqli_fetch_assoc($result)){
					$reserve_qty = $row['qty'];
					$reserve_unit = $row['unit_id'];



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
					$query_dstock="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i
							where stock_status=0 and i.branch_id=".$POST['from_branch_id']." and stock_flage=1 and  cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5))  and product_id = ".$row['product_id']." and i.godown_id=".$row['godown_id'];
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
								$reserve_qty=$reserve_qty-$pending_stock;
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

							item_reserve_stock_entry($dbcon,$row['product_id'],$re['product_base_unit'],$re['product_conv_unit'],$base_stock,$con_stock,"stock_transfer_trn",$row['stock_transfer_trn_id'],$row_dstock['stock_id'],$row['godown_id'],$row_dstock['branch_id']);
				}
						}
					}
				} */
			}
		}
		else{
			$arr['msg']="0";
		}
		$arr['back']=$POST['back'];
		
		echo json_encode($arr);	
		
	}
	else if(strtolower($POST['mode']) == "edit") {

		$info['wo_stk_transfer_no']				= $POST['transfer_no'];
		$info['wo_stk_transfer_date']			= date('Y-m-d',strtotime($POST['transfer_date']));

		$info['from_godown_id']						= $POST['from_godown_id'];
		$info['to_godown_id']						= $POST['to_godown_id'];
		$info['remark']								= $_POST['remark'];
			
		$info['cdate']								= date("Y-m-d H:i:s"); 
		$info['user_id']							= $_SESSION['user_id'];
		$info['company_id']							= $_SESSION['company_id']; 
		
		$updateid=update_record('tbl_stock_transfer', $info,"wo_stk_transfer_id=".$POST['eid'] , $dbcon);
		 
		
		
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
		
		$info1['from_workorder_id']			= $POST['from_workorder_id'];
		$info1['to_workorder_id']			= $POST['to_workorder_id'];
		$info1['product_id']			= $POST['product_id'];
		$info1['from_rp_id']			= $POST['from_rp_id'];
		$info1['to_rp_id']			= $POST['to_rp_id'];
		$info1['qty']			= $POST['qty'];
		$info1['unit_id']			= $POST['unit_id'];
		$info1['branch_id']			= $POST['branch_id'];
		
		$info1['user_id']				= $_SESSION['user_id'];
		$info1['company_id']			= $_SESSION['company_id'];

		$table='tbl_workorder_transfer_trn';
		$tableid='stock_transfer_trn_id';
		if(!empty($POST['eid'])) {
			$info1['wo_stk_transfer_id']= $POST['eid'];
		}
		else{
			$info1['status']= 3;
		}
		
		if(empty($POST['edit_id'])) {
			//var_dump($info1);
			$inserid=add_record($table, $info1, $dbcon);

			if($inserid){
				echo "1";
			}else{
				echo "0";
			}
		}
		else {
			$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			echo "1";
		}
	}else if(strtolower($POST['mode']) == "get_workorder_list") {

		$query = "SELECT rp.rp_id,rp.sp_id,sp.po_req_no,(SELECT count(rp_id) as cnt FROM tbl_request_product WHERE status = 0 AND perent_id = rp.rp_id) as product_req FROM tbl_request_product as rp
				  LEFT JOIN tbl_set_main_process as sp on sp.sp_id = rp.sp_id	
				  WHERE rp.status = 0 AND rp.main_request = 1 and sp.sp_status != 2";

		$result = $dbcon->query($query);
		$str = "<option value=''>Select Workorder</option>";
		while($row = brp_mysqli_fetch_assoc($result)){
			if($row['product_req'] > 0){
				$str .= "<option value='".$row["sp_id"]."' data-rp_id='".$row['rp_id']."'>".$row['po_req_no']."</option>";
			}
		}
			// $("#example").select2().find(":selected").data("id");
		echo $str;
	}else if(strtolower($POST['mode']) == "get_product_list") {

		$workorder_id = $POST['workorder_id'];

		$query = "SELECT rp.sr_no, p.product_name,p.product_id,rp.rp_id FROM tbl_request_product as rp
					LEFT JOIN product_mst as p on p.product_id = rp.rp_pid
					WHERE rp.status = 0 and sp_id = " . $workorder_id;

		$result = $dbcon->query($query);
		$str = "<option value=''>Select Product</option>";
		while($row = brp_mysqli_fetch_assoc($result)){
				$str .= "<option value='".$row["rp_id"]."' data-product_id='".$row['product_id']."'>".$row['sr_no']." - ".$row['product_name']."</option>";
		}
			// $("#example").select2().find(":selected").data("id");
		echo $str;
	}else if(strtolower($POST['mode'])== "load_product_unit")
	{
		$rp_id = $POST['rp_id'];
		$query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name, promst.batch_wise_stock_manage FROM tbl_request_product as rp
			left join product_mst as promst on promst.product_id=rp.rp_pid
			left join unit_mst as umst on umst.unitid=promst.product_base_unit
			left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
			WHERE rp_id=".$rp_id;
		
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
	}else if(strtolower($POST['mode'])== "load_workorder_transfer_trn_data") {
		
		if($POST['eid']){
			$query="select trn.*,pro.product_name,unit.unit_name,fwo.po_req_no as from_workorder_no,two.po_req_no as to_workorder_no from tbl_workorder_transfer_trn as trn
			left join product_mst as pro on pro.product_id = trn.product_id
			left join unit_mst as unit on unit.unitid = trn.unit_id
			left join tbl_set_main_process as fwo on fwo.sp_id = trn.from_workorder_id
			left join tbl_set_main_process as two on two.sp_id = trn.to_workorder_id

			where status=0 and trn.wo_stk_transfer_id=".$POST['eid'];
		}
		else{
			$query="select trn.*,pro.product_name,unit.unit_name,fwo.po_req_no as from_workorder_no,two.po_req_no as to_workorder_no from tbl_workorder_transfer_trn as trn
			left join product_mst as pro on pro.product_id = trn.product_id
			left join unit_mst as unit on unit.unitid = trn.unit_id
			left join tbl_set_main_process as fwo on fwo.sp_id = trn.from_workorder_id
			left join tbl_set_main_process as two on two.sp_id = trn.to_workorder_id
			where status=3 and trn.user_id=".$_SESSION['user_id'];
		}

		
		$result=$dbcon->query($query);
		echo ' <div class="form-group">
					  <div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th class="text-center grn" width="10%">Sr No</th>
							<th class="text-center grn" width="20%">From Workorder</th>
							<th class="text-center grn" width="20%">To Workorder</th>
							<th class="text-center" width="25%">Product Name</th>
							<th class="text-center" width="15%">Transfer Qty</th>
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
						'.$rel['from_workorder_no'].'
					</td>
					<td style="vertical-align:top;">
						'.$rel['to_workorder_no'].'
					</td>
					<td style="vertical-align:top;">
						'.$rel['product_name'].'
					</td>
					<td style="vertical-align:top;" class="text-center">
						'.$rel['qty'].' '.$rel['unit_name'].'
					</td>
					
					<td style="vertical-align:top"> 
						<!-- <button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_grn_data('.$rel['wo_stk_trn_id'].')"><i class="fa fa-pencil"></i></button> -->
						<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_workorder_transfer_data('.$rel['wo_stk_trn_id'].')"><i class="fa fa-trash"></i></button>
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
	}else if(strtolower($POST['mode'])== "preedit") {
		$q = $dbcon -> query("SELECT mst.* FROM  tbl_workorder_transfer_trn as mst WHERE wo_stk_trn_id = '$POST[edit_id]'");
		$r = $q->fetch_assoc();
		
		echo json_encode($r);
	}
	else if(strtolower($POST['mode'])== "delete_data") {
		$row=array();
		$info['status']=2;	
		$updateid=update_record('tbl_workorder_transfer_trn', $info, "wo_stk_trn_id=".$POST['id'] , $dbcon);
		
		//$UPD_PO=upd_grn_used_status($dbcon, $POST['purchaseorder_id'], 2);
		
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}else if(strtolower($POST['mode'])=="load_stock_qty")
	{
		$product_id=$POST['product_id'];
		$request_id=$POST['from_rp_id'];
		$unit_id = $POST['unit_id'];
		$is_store_approval = 0;
				
		$rstock=reserve_stock($dbcon,$product_id,$unit_id,$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,"",$stock_id,"",1);
		
		if(!empty($rstock) && $rstock > 0){
			echo $rstock;
		}else{
			echo 0;
		}
		
	}else if(strtolower($POST['mode']) == "load_workorder_transfer_details") {

			$qry="select st.wo_stk_transfer_id,st.wo_stk_transfer_no,st.wo_stk_transfer_date,st.approved_status, st.remark, usr.user_name from tbl_workorder_transfer st
			LEFT JOIN users as usr on usr.user_id = st.user_id
			where st.status = 0 and st.wo_stk_transfer_id =".$POST['wo_stk_transfer_id'];
			$rel=mysqli_fetch_assoc($dbcon->query($qry));
			
		//Party PO Details Table View
			$str='';
			$str.='<div class="form-group">
			<table class="display table table-bordered table-striped">
			<tr>
			<td><strong>Workorder Transfer Doc No:</strong> '.$rel['wo_stk_transfer_no'].'</td>
			<td><strong>Workorder Transfer Date:</strong> '.date('d-m-Y',strtotime($rel['wo_stk_transfer_date'])).'</td>
			</tr>
			<tr>
			<td><strong>Remark:</strong> '.$rel['remark'].'</td>
			<td><strong>User:</strong> '.$rel['user_name'].'</td>
			</tr>';
			$str.='</table></div><hr/>';

			 $query = "select trn.*,fwo.po_req_no as from_workorder_no,two.po_req_no as to_workorder_no,pro.product_name,unit.unit_name from tbl_workorder_transfer_trn as trn 
			left join product_mst as pro on pro.product_id = trn.product_id
			left join unit_mst as unit on unit.unitid = trn.unit_id
			left join tbl_set_main_process as fwo on fwo.sp_id = trn.from_workorder_id
			left join tbl_set_main_process as two on two.sp_id = trn.to_workorder_id
			where  trn.status = 0 and trn.wo_stk_transfer_id = ". $POST['wo_stk_transfer_id'];

			$result=$dbcon->query($query);
			$cnt=mysqli_num_rows($result);
			
			if($cnt>0){
				$str.='<div class="form-group">';
				$str .= '<table class="display table table-bordered table-striped" style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
						<tr>
							<th style="border:0.5px #444 solid;text-align:center;" >From Workorder</th>
							<th style="border:0.5px #444 solid;text-align:center;" >To Workorder</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Product</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Transfer Qty</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Unit</th>
						</tr>';
					while($rel3=mysqli_fetch_assoc($result)){ 
						$str .= '<tr>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel3['from_workorder_no'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel3['to_workorder_no'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel3['product_name'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel3['qty'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel3['unit_name'].'</td>
							
						</tr>';	
					}
					$str .= '</table></div>';	
					
			}
			
			$arr['mod_stock_div_sec'] = $str;
			
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "add_workorder_apprv_hist") {
           
            $check_hist_qry = "select log.workorder_aprv_log_id, usr.user_name, log.approve_status, log.approve_remark, log.created_at, log.user_id 
                    FROM tbl_workorder_transfer_aprv_log as log left join users as usr on usr.user_id=log.user_id 
                    where log.workorder_aprv_log_status=0 and log.wo_stk_transfer_id=".$POST['wo_stk_transfer_id']." and log.user_id = ".$_SESSION['user_id']."
                    order by log.workorder_aprv_log_id desc limit 1";
            $result = $dbcon->query($check_hist_qry);
            $cnt = brp_mysqli_num_rows($result);
            

            if($cnt > 0){
            	$history_data = brp_mysqli_fetch_assoc($result);
            	if($history_data[0]['approve_status'] !== $POST['approve_status']) {
	                $info1['approve_remark']	= $POST['approve_remark'];
	                $info1['approve_status']	= $POST['approve_status'];
	                $info1['wo_stk_transfer_id']             = $POST['wo_stk_transfer_id'];
	                $info1['user_id']		= $_SESSION['user_id'];
	                $info1['company_id']	= $_SESSION['company_id'];

	                $insert_id=add_record("tbl_workorder_transfer_aprv_log", $info1, $dbcon);

	                if($insert_id){
	                	  /*$query = "select * from tbl_stock_transfer_trn trn 
								where  trn.status = 0 and trn.stock_transfer_id = ". $POST['stock_transfer_id'];
							$result1=$dbcon->query($query);*/
	                	if($POST['approve_status'] == 1){
	                		$infoso['approved_status'] = 1; // approve

	                	}else {
	                	 	$infoso['approved_status'] = 2; // reject

	                	}
	                    $updateid=update_record('tbl_workorder_transfer', $infoso,"wo_stk_transfer_id=".$POST['wo_stk_transfer_id'] , $dbcon);
	                     workorder_transfer_reserve_stock_effect($dbcon,$POST['wo_stk_transfer_id']);
	                }
	                echo 1;
	            }else {
	                echo 0;
	            }
            }else{

            	$info1['approve_remark']	= $POST['approve_remark'];
                $info1['approve_status']	= $POST['approve_status'];
                $info1['wo_stk_transfer_id']  = $POST['wo_stk_transfer_id'];
                $info1['user_id']		= $_SESSION['user_id'];
                $info1['company_id']	= $_SESSION['company_id'];

                $insert_id=add_record("tbl_workorder_transfer_aprv_log", $info1, $dbcon);

                if($insert_id){
                	 
                	if($POST['approve_status'] == 1){
                		$infoso['approved_status'] = 1; // approve

                	}else {
                	 	$infoso['approved_status'] = 2; // reject

                	}
                    $updateid=update_record('tbl_workorder_transfer', $infoso,"wo_stk_transfer_id=".$POST['wo_stk_transfer_id'] , $dbcon);

                    workorder_transfer_reserve_stock_effect($dbcon,$POST['wo_stk_transfer_id']);
                    echo 1;
                }else{
                	echo 0;
                }
            }
             
	}
	else if(strtolower($POST['mode']) == "load_workorder_hist_datatable") {
		$where='';
        if($POST['wo_stk_transfer_id']){
            $where.="  and log.wo_stk_transfer_id=".$POST['wo_stk_transfer_id'];
        }

		$appData = array();
        $i=1;
        $aColumns = array('log.workorder_aprv_log_id','log.wo_stk_transfer_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.created_at', 'log.user_id');
        $sIndexColumn = "log.workorder_aprv_log_id";
        $isWhere = array("log.workorder_aprv_log_status = 0 ".$where." ");
        $sTable = "tbl_workorder_transfer_aprv_log as log";
        $isJOIN = array('left join users as usr on usr.user_id=log.user_id');
        $hOrder = "log.workorder_aprv_log_id desc";
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
	}else if(strtolower($POST['mode'])== "delete_gd_tranfer") {
		$row=array();
		$info['status']=2;
		$updateid=update_record('tbl_workorder_transfer', $info, "wo_stk_transfer_id=".$POST['id'] , $dbcon);
		
		$update_id=update_record('tbl_workorder_transfer_trn', $info, "wo_stk_transfer_id=".$POST['id'] , $dbcon);
		
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"workorder_to_workorder_tranfer",3,"tbl_workorder_transfer",$POST['id']);	
		
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}


function workorder_transfer_reserve_stock_effect($dbcon,$wo_stk_transfer_id){

	$query_12 = "select * from  tbl_workorder_transfer_trn trn 
							where  trn.status = 0 and trn.wo_stk_transfer_id = ". $wo_stk_transfer_id;
	 $result_12=$dbcon->query($query_12);
	 while($row_12 = brp_mysqli_fetch_assoc($result_12)){

	 	$transfer_qty = $row_12['qty'];
	 	$query_dstock = "select i.*,(cast(base_stock AS DECIMAL(22,5)) - IFNULL((select sum(base_stock) from tbl_reserve_stock where stock_status != 2 and stock_flage = 2 and perent_id = i.reserve_id),0)) as pending_base_stock,(cast(convert_stock AS DECIMAL(22,5)) - IFNULL((select sum(convert_stock) from tbl_reserve_stock where stock_status != 2 and stock_flage = 2 and perent_id = i.reserve_id),0)) as pending_conv_stock from tbl_reserve_stock as i where stock_status != 2 and stock_flage=1 and i.product_id=".$row_12['product_id']." and request_id =  " . $row_12['from_rp_id'];

	 	$result_dstock=$dbcon->query($query_dstock);
			//echo $query_dstock;
			while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
				$pending_stock=$row_dstock['pending_base_stock'];	
			
				if($transfer_qty > 0 && $pending_stock > 0){
					if($pending_stock>=$transfer_qty){
						$rqty=$transfer_qty;
						$transfer_qty=$transfer_qty-$transfer_qty;
					}else{
						$rqty=$pending_stock;
						$transfer_qty=$transfer_qty-$pending_stock;
					}
	
					$type="conv_unit";
					$base_stock=$rqty;
					// $con_stock=convert_stock_new($dbcon,$rqty,$row_12['product_id'],$type);
					$con_stock=($rqty/$row_dstock['base_stock'])*$row_dstock['convert_stock'];
					
					$arr_deduct_reserve_stock = [];
					$arr_transfer_reserve_stock = [];
					foreach($row_dstock as $key => $value){
						$arr_deduct_reserve_stock[$key] = $value;
						$arr_transfer_reserve_stock[$key] = $value;
					}

					unset($arr_deduct_reserve_stock['reserve_id']);
					unset($arr_deduct_reserve_stock['approve_base_stock']);
					unset($arr_deduct_reserve_stock['approve_convert_stock']);
					unset($arr_deduct_reserve_stock['used_base_stock']);
					unset($arr_deduct_reserve_stock['used_convert_stock']);
					unset($arr_deduct_reserve_stock['pending_base_stock']);
					unset($arr_deduct_reserve_stock['pending_conv_stock']);

					$arr_deduct_reserve_stock['base_stock']  = $base_stock;
					$arr_deduct_reserve_stock['convert_stock'] = $con_stock;
					$arr_deduct_reserve_stock['stock_flage'] = 2;
					$arr_deduct_reserve_stock['ref_name'] = "workorder_transfer_trn";
					$arr_deduct_reserve_stock['ref_id'] = $row_12['wo_stk_trn_id'];
					$arr_deduct_reserve_stock['perent_id'] = $row_dstock['reserve_id'];

					$inserpoid=add_record('tbl_reserve_stock',$arr_deduct_reserve_stock, $dbcon);

					$upd_res_info['used_base_stock'] = $row_dstock['used_base_stock'] +  $base_stock;
					$upd_res_info['used_convert_stock'] = $row_dstock['used_convert_stock'] + $con_stock;


					$updateid=update_record('tbl_reserve_stock', $upd_res_info,"reserve_id=".$row_dstock['reserve_id'] , $dbcon);
					unset($arr_transfer_reserve_stock['reserve_id']);
					unset($arr_transfer_reserve_stock['pending_base_stock']);
					unset($arr_transfer_reserve_stock['pending_conv_stock']);

					$arr_transfer_reserve_stock['base_stock']  = $base_stock;
					$arr_transfer_reserve_stock['convert_stock'] = $con_stock;
					$arr_transfer_reserve_stock['stock_flage'] = 1;
					$arr_transfer_reserve_stock['ref_name'] = "workorder_transfer_trn";
					$arr_transfer_reserve_stock['ref_id'] = $row_12['wo_stk_trn_id'];
					$arr_transfer_reserve_stock['request_id'] = $row_12['to_rp_id'];
					$arr_transfer_reserve_stock['perent_id'] = 0;
					$arr_transfer_reserve_stock['approve_base_stock'] = 0;
					$arr_transfer_reserve_stock['approve_convert_stock'] = 0;
					$arr_transfer_reserve_stock['used_base_stock'] = 0;
					$arr_transfer_reserve_stock['used_convert_stock'] = 0;


					$ap_qry = "SELECT ap.p_id from tbl_allocate_process as ap
					left JOIN tbl_request_product as rp on rp.perent_id = ap.p_ref_id 
					WHERE ap.p_status != 2 and ap.process_priority = 1 AND ap.previous_process_id = 0 and rp.rp_id = " .  $row_12['to_rp_id'];
					$ap_result = $dbcon->query($ap_qry);
					$ap_row = brp_mysqli_fetch_assoc($ap_result);

					$arr_transfer_reserve_stock['p_id'] = $ap_row['p_id'];

					$insert_id=add_record('tbl_reserve_stock',$arr_transfer_reserve_stock, $dbcon);

				}
			}

	 }
}	

?>