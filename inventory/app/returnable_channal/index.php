<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
      INVENTORY_RETURNABLE_CHANNAL_SLUG_READ,
      INVENTORY_RETURNABLE_CHANNAL_SLUG_CREATE,
      INVENTORY_RETURNABLE_CHANNAL_SLUG_UPDATE,
      INVENTORY_RETURNABLE_CHANNAL_SLUG_DELETE,
      INVENTORY_RETURNABLE_CHANNAL_SLUG_APPROVE,
      INVENTORY_RETURNABLE_CHANNAL_SLUG_PRINT
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
		$aColumns = array('retun.id','retun.returnable_type', 'retun.cust_id', 'retun.channal_id','retun.challan_date','retun.status','retun.created_at','retun.user_id','ledg.l_name','(SELECT count(id) FROM tbl_returnable_channal_item as item WHERE status = 0 and item.approve_status = 0 and item.returnable_id = retun.id) as pending_record','(SELECT count(id) FROM tbl_returnable_channal_item as item WHERE item.returnable_id = retun.id and status = 0 and item.approve_status = 1) as approved_record','(SELECT count(id) FROM tbl_returnable_channal_item as item WHERE item.returnable_id = retun.id and status = 0 and item.approve_status = 2) as disapproved_record','(SELECT count(id) FROM tbl_returnable_channal_item as item WHERE item.returnable_id = retun.id and status = 0 and item.grn_status = 1) as grn_done','(select count(returnable_id) from tbl_grn_trn as grn where grn.grn_trn_status=0 and grn.returnable_id=retun.id ) as cntgrn');
		$sIndexColumn = "retun.id";
		$isWhere = array("retun.status = 0".$where.check_company('retun'));
		$sTable = "tbl_returnable_channal as retun";
		$isJOIN = array('left join tbl_ledger as ledg on ledg.l_id=retun.cust_id');
		$hOrder = "retun.id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			if($row['returnable_type'] == 'returnable'){
				$type = "Returnable";
			}else{
				$type = "Non Returnable";
				if($row['returnable_type'] == "without_stock"){
					$type .= "</br><span class='text-danger'> Without Stock </span>";
				}else{
					$type .= "</br><span class='text-success'> With Stock  </span>";
				}
			}
			$row_data[] = $type;
			$row_data[] = $row['l_name'];
			$row_data[] = $row['channal_id'];
			$row_data[] = date('d-m-Y',strtotime($row['challan_date']));
			
			$approved = '';$pending_approve ='';$disapproved = '';
			$approved = '<button class="btn btn-xs btn-success" >Approved ('.$row['approved_record'].')</button>';
			$pending_approve = '<button class="btn btn-xs btn-warning">Approval Pending ('.$row['pending_record'].')</button>';
			$disapproved = '<button class="btn btn-xs btn-danger">Disapproved ('.$row['disapproved_record'].')</button>';
			$row_data[] = $approved.' '.$pending_approve.' '.$disapproved; 
			/*if($row['total_record'] == $row['total_approve_record']){
				$row_data[] = '<button class="btn btn-xs btn-success" >Approved</button>';
			}else{
				$row_data[] = '<button class="btn btn-xs btn-warning">Approval Pending</button>';
			}*/
			$edit_btn=''; $delete_btn='';$grn_done='';$challan_print='';
				
			if(in_array(INVENTORY_RETURNABLE_CHANNAL_SLUG_UPDATE,$bulkAccessArray)){
				if($row['total_approve_record'] == 0){
					$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'returnable_channal_update/'.$row['id'].'"><i class="fa fa-pencil"></i></a>'; 
				}
			}
			if(in_array(INVENTORY_RETURNABLE_CHANNAL_SLUG_DELETE,$bulkAccessArray)){
				if($row['total_approve_record'] == 0){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_returnable_channal('.$row['id'].')"><i class="fa fa-trash-o"></i></button>'; 
				}
			}
			if(in_array(INVENTORY_RETURNABLE_CHANNAL_SLUG_APPROVE,$bulkAccessArray)){
				if($row['cntgrn'] > 0 ){
					$approv_btn = '';
				}else{
					$approv_btn=' <button class="btn btn-xs btn-success" data-original-title="Approve" data-toggle="tooltip" data-placement="top" onClick="approve_returnable_channal('.$row['id'].')"><i class="fa fa-check"></i> Approval</button>';
				}
			}
			
			if(in_array(INVENTORY_RETURNABLE_CHANNAL_SLUG_PRINT,$bulkAccessArray)){
				$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
				$rels=mysqli_fetch_assoc($menusql);
				$menu_show_permissions = explode(",",$rels['print_permission']);
				$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 14 AND approve_status = 1 AND status = 0 ORDER BY priority");
				while($res = mysqli_fetch_assoc($sql)){
					if(in_array($res['id'],$menu_show_permissions)) {
						$challan_print.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['id'].'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>&nbsp;';
					}
				}
			}
			
			if($row['total_record'] == $row['grn_done']){
				$grn_done = '<button class="btn btn-xs btn-primary" >Grn Done</button>';
				$approv_btn='';
			}
			$row_data[] = $edit_btn.' '.$delete_btn.' '.$approv_btn.' '.$challan_print.' '.$grn_done;
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "fetchitemlist") {
		$appData = array();
		$i=1;
		$aColumns = array('retunitem.id','retunitem.item_description','retunitem.item_qty','retunitem.item_price','retunitem.approve_status','retunitem.returnable_id','retunitem.remark','retunitem.rr_approve_qty','retunitem.rr_disapprove_qty','pro.product_name','pro.product_icode','cat.unit_name');
		$sIndexColumn = "retunitem.id";
		$isWhere = array("retunitem.status = 0 and retunitem.returnable_id = $POST[returning_id]");
		$sTable = "tbl_returnable_channal_item as retunitem";
		$isJOIN = array('left join product_mst as pro on pro.product_id=retunitem.item_id',
			'left join unit_mst as cat on cat.unitid=retunitem.item_unit_id');
		$hOrder = "retunitem.id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['product_name'] . '</br> -- ('.$row['product_icode'].')';
			$row_data[] = $row['item_description'];
			$row_data[] = $row['unit_name'];
			$pending_qty = $row['item_qty'];
			$row_data[] = $pending_qty."<input type='hidden' class='form-control' onkeypress='return isNumberKey(event)' id='$id' value='$pending_qty' max='$pending_qty' readonly onKeyUp='if(this.value>$pending_qty){this.value=$pending_qty;}else if(this.value<0){this.value=0;}' style='width :70px' /><input type='hidden' class='form-control' onkeypress='return isNumberKey(event)' id='approve_$id' value='$row[rr_approve_qty]' style='width :70px' readonly /><input type='hidden' class='form-control' id='disapprove_$id'  value='$row[rr_disapprove_qty]' style='width :70px' readonly />";
			//$row_data[] = $row['rr_approve_qty']."<input type='hidden' class='form-control' onkeypress='return isNumberKey(event)' id='approve_$id' value='$row[rr_approve_qty]' style='width :70px' readonly />";
			//$row_data[] = $row['rr_disapprove_qty']."<input type='hidden' class='form-control' id='disapprove_$id'  value='$row[rr_disapprove_qty]' style='width :70px' readonly />";
			$row_data[] = $row['item_price'];
			$row_data[] = "<textarea type='text' class='form-control' id='remark_$id'/>".$row['remark']."</textarea>";
			if($row['approve_status']=='1'){
				$row_data[] = '<button class="btn btn-xs btn-success" >Approved</button>';
			}
			else if($row['approve_status']=='2'){
				$row_data[] = '<button class="btn btn-xs btn-danger">Disapproved</button>';
			}else{
				$row_data[] = '<button class="btn btn-xs btn-warning">Approval Pending</button>';
			}
			$approv_btn = "";$dis_aproove_btn="";
			if($row['approve_status']=='0'){
				if(in_array(INVENTORY_RETURNABLE_CHANNAL_SLUG_APPROVE,$bulkAccessArray)){
					$approv_btn=' <button class="btn btn-xs btn-success" data-original-title="Approve" data-toggle="tooltip" data-placement="top" onClick="check_approve_returnable_channal('.$row['id'].','.$id.')"><i class="fa fa-check"></i></button>'; 
				}
				if(in_array(INVENTORY_RETURNABLE_CHANNAL_SLUG_APPROVE,$bulkAccessArray))
				{
					$dis_aproove_btn='<button class="btn btn-xs btn-warning" data-original-title="Disapprove" data-toggle="tooltip" data-placement="top" onClick="check_disapprove_returnable_channal('.$row['id'].','.$id.')"><i class="fa fa-check"></i></button>';
				}
			}else if($row['approve_status']=='1'){
				if(in_array(INVENTORY_RETURNABLE_CHANNAL_SLUG_APPROVE,$bulkAccessArray))
				{
					$dis_aproove_btn='<button class="btn btn-xs btn-warning" data-original-title="Disapprove" data-toggle="tooltip" data-placement="top" onClick="check_disapprove_returnable_channal('.$row['id'].','.$id.')"><i class="fa fa-check"></i></button>';
				}	
			}
			$row_data[] = $edit_btn.' '.$delete_btn.' '.$approv_btn.' '.$dis_aproove_btn;
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") 
	{
		// echo "<pre>";
		// print_r($POST);die;
		$returnable_type = $_POST['returnable_type'];

		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$challan_no = $POST['challan_no'];
		$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id='17' and invoice_type = 'RETURNABLE CHANNAL' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
		
		$info['channal_id']		= $POST['channal_id'];
		$info['challan_date']		= date('Y-m-d',strtotime($POST['challan_date']));
		$info['challan_type']		= $POST['chln_type'];
		$info['challan_return_type']	= $POST['return_type'];
		$info['return_date']		= date('Y-m-d',strtotime($POST['return_date']));
		$info['issue_date']     	= date("Y-m-d H:i:s",strtotime($POST['issue_date']));
		$info['cust_id']			= $POST['cust_id'];
		$info['returnable_type']	= $returnable_type;
		$info['vehicle_no']		= $POST['vehicle_no'];
		$info['mode_dispatch']		= $POST['mode_dispatch'];
		$info['sales_order_id']		= $POST['sales_order_id'];
		$info['remark']			= $_POST['remark'];
		$info['user_id']			= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];

		$info['for_jobwork']		= isset($POST['for_jobwork']) ? '1' : '0' ;
		$info['for_sample']		= isset($POST['for_sample']) ? '1' : '0' ;
		$info['on_loan']			= isset($POST['on_loan']) ? '1' : '0' ;
		$info['for_replacement']	= isset($POST['for_replacement']) ? '1' : '0' ;
		$info['for_repairing']		= isset($POST['for_repairing']) ? '1' : '0' ;
		$info['rejected']			= isset($POST['rejected']) ? '1' : '0' ;
		$info['loan_returns']		= isset($POST['loan_returns']) ? '1' : '0' ;
		$info['non_returnable_matl']	= isset($POST['non_returnable_matl']) ? '1' : '0' ;

		$insertid=add_record('tbl_returnable_channal', $info, $dbcon, $branch_id);
		

		$info_update['status']	= 0;
		$info_update['returnable_id']	= $insertid;
		$updateempseparationid=update_record('tbl_returnable_channal_item', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);

		$sales_order_update = get_returnable_salesorderwise_done($dbcon,$insertid);

		
			$sel_pro_rate = "select * from tbl_returnable_channal_item where approve_status = 0 and status=0 and returnable_id=".$insertid;
			$sel_pro_rate_rs = $dbcon->query($sel_pro_rate);
				
			while($sel_pro_rate_rel=brp_mysqli_fetch_assoc($sel_pro_rate_rs)){
				if(strtolower($POST['return_type'])=="challan_wise"){
					$delivery_da = "select * from tbl_returnable_return_date as mst where mst.return_date_status=0  and mst.return_item_id=".$sel_pro_rate_rel['id'];
					$delivery_dae = $dbcon->query($delivery_da);
					if(brp_mysqli_num_rows($delivery_dae)>0){
						$inftrn11d['return_date'] = date('Y-m-d',strtotime($POST['return_date']));
						$updatetrnid=update_record('tbl_returnable_return_date', $inftrn11d,"return_date_status=0 and return_item_id=".$sel_pro_rate_rel['id'], $dbcon, $branch_id);
					}else{
						$infodeli['return_item_id'] 	= $sel_pro_rate_rel['id'];
						$infodeli['return_date'] 	= date('Y-m-d',strtotime($POST['return_date']));
						$infodeli['item_qty'] 		= $sel_pro_rate_rel['item_qty'];
						$infodeli['unit_id'] 		= $sel_pro_rate_rel['item_unit_id'];
							
						$infodeli['user_id']		= $_SESSION['user_id'];
						$infodeli['cdate']		= date("Y-m-d h:i:s");
						$infodeli['company_id']		= $_SESSION['company_id'];

						$inser_del = add_record('tbl_returnable_return_date', $infodeli, $dbcon, $branch_id);
					}
				}
			}
		
		if($insertid){	
			$arr['msg']="1";	
			$log_entry=common_log_entry($dbcon,"returnable_channal_add",1,"tbl_returnable_channal",$insertid);	
		}
		else{
			$arr['msg']="0";
		}
		echo json_encode($arr);	
	}
	else if(strtolower($POST['mode']) == "edit") {
		$info['cust_id']			= $POST['cust_id'];
		$info['challan_date']		= date('Y-m-d',strtotime($POST['challan_date']));
		$info['issue_date']     	= date("Y-m-d H:i:s",strtotime($POST['issue_date']));
		$info['challan_type']		= $POST['chln_type'];
		$info['challan_return_type']	= $POST['return_type'];
		$info['return_date']		= date('Y-m-d',strtotime($POST['return_date']));
		$info['returnable_type']	= $POST['returnable_type'];
		$info['remark']			= $_POST['remark'];
		$info['vehicle_no']		= $POST['vehicle_no'];
		$info['mode_dispatch']		= $POST['mode_dispatch'];
		$info['sales_order_id']		= $POST['sales_order_id'];
		$info['user_id']		    	= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id']; 
		$info['updated_at']		= date("Y-m-d H:i:s"); 
		$updateid=update_record('tbl_returnable_channal', $info,"id=".$POST['eid'] , $dbcon);


		$info_update['status']		= 0;
		$info_update['returnable_id']	= $POST['eid'];
		$updateempseparationid=update_record('tbl_returnable_channal_item', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
		
		
			$sel_pro_rate = "select * from tbl_returnable_channal_item where  approve_status = 0 and status=0 and returnable_id=".$POST['eid'];
			$sel_pro_rate_rs = $dbcon->query($sel_pro_rate);
				
			while($sel_pro_rate_rel=brp_mysqli_fetch_assoc($sel_pro_rate_rs)){
				if(strtolower($POST['return_type'])=="challan_wise"){
				$delivery_da = "select * from tbl_returnable_return_date as mst where mst.return_date_status=0  and mst.return_item_id=".$sel_pro_rate_rel['id'];
				$delivery_dae = $dbcon->query($delivery_da);
				if(brp_mysqli_num_rows($delivery_dae)>0){
					$inftrn11d['return_date'] = date('Y-m-d',strtotime($POST['return_date']));
					$updatetrnid=update_record('tbl_returnable_return_date', $inftrn11d,"return_date_status=0 and return_item_id=".$sel_pro_rate_rel['id'], $dbcon, $branch_id);
				}else{
					$infodeli['return_item_id'] 	= $sel_pro_rate_rel['id'];
					$infodeli['return_date'] 	= date('Y-m-d',strtotime($POST['return_date']));
					$infodeli['item_qty'] 		= $sel_pro_rate_rel['item_qty'];
					$infodeli['unit_id'] 		= $sel_pro_rate_rel['item_unit_id'];
						
					$infodeli['user_id']		= $_SESSION['user_id'];
					$infodeli['cdate']		= date("Y-m-d h:i:s");
					$infodeli['company_id']		= $_SESSION['company_id'];

					$inser_del = add_record('tbl_returnable_return_date', $infodeli, $dbcon, $branch_id);
				}
			}
			

			/* $sel_stock = "select * from tbl_returnable_batch_stock_tmp where status=1 and returnable_trn_id=".$sel_pro_rate_rel['id'];
			$sel_stock_rs = $dbcon->query($sel_stock);

			$sel_pro = "select * from product_mst where product_status=0 and product_id=".$sel_pro_rate_rel['item_id'];
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

						
						item_reserve_stock_entry($dbcon,$sel_stock_rel['product_id'],$sel_pro_rel['product_base_unit'],$sel_pro_rel['product_conv_unit'],$base_stock,$con_stock,"returning_receipt",$sel_stock_rel['returnable_trn_id'],$sel_stock_rel['stock_id'],$rel_stock_1['godown_id'],$rel_stock_1['branch_id']);
					}
				}else{
					$item_qty = $sel_pro_rate_rel['item_qty'];
					$item_unit = $sel_pro_rate_rel['item_unit_id'];
					 $qry_11 = "select * from tbl_stock_trn where stock_status = 0 and stock_flage = 1 and product_id = " . $sel_pro_rate_rel['item_id'] . " and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5))";
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
							
							if($item_unit==$sel_pro_rel['product_conv_unit']){
								$type="base_unit";
								$con_stock=$rqty;
								$base_stock=convert_stock($dbcon,$con_stock,$sel_pro_rate_rel['item_id'],$type);
							}else{
								$type="conv_unit";
								$base_stock=$rqty;
								$con_stock=convert_stock($dbcon,$base_stock,$sel_pro_rate_rel['item_id'],$type);
							}
							item_reserve_stock_entry($dbcon,$sel_pro_rate_rel['item_id'],$sel_pro_rel['product_base_unit'],$sel_pro_rel['product_conv_unit'],$base_stock,$con_stock,"returning_receipt",$sel_pro_rate_rel['id'],$stock_id,$row_11['godown_id'],$row_11['branch_id']);

						}

					}
				}*/
			}
		
		if($updateid){	
			$arr['msg']="1";		
			$log_entry=common_log_entry($dbcon,"returnable_channal_edit",2,"tbl_returnable_channal",$POST['eid']);						
		}else{
			$arr['msg']="0";
		}
		echo json_encode($arr);	
	}
	else if(strtolower($POST['mode']) == "fieldadd") {
		$returnable_type = $_POST['returnable_type'];
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where = "";
		if(!empty($POST['edit_id'])){
			$where .= " and id != '".$POST['edit_id']."'";
		}
		//$tr = $dbcon -> query("SELECT SUM(`item_qty`) as itemqty FROM `tbl_returnable_channal_item` WHERE status='0' and `item_id` = '".$POST['item_id']."' and `company_id`='".$_SESSION['company_id']."' $where");
		//$row=mysqli_fetch_assoc($tr);
		//$totalQty = $row['itemqty'] + $POST['item_qty'];
		//if($totalQty >  $_POST['item_stock']) {
			//$resp['resp']= '-1';
			//$resp['stock']= ($_POST['item_stock'] - $totalQty);
		//} else {
			$info1['item_id']		   	= $POST['item_id'];
			$info1['item_hsn']			= $_POST['item_hsn_code'];
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$info1['item_description']	= $_POST['item_description'];
			$info1['item_unit_id']		= $POST['unit_id']; 
			$info1['item_qty']			= $POST['item_qty'];
			$info1['item_price']		= $POST['item_price'];
			$info1['created_at']		= date('Y-m-d H:i:s');
			$info1['updated_at']		= date('Y-m-d H:i:s');
			$table='tbl_returnable_channal_item';
			$tableid='id';
			if(empty($POST['edit_id'])){
				if(empty($POST['eid'])){
					$info1['status']	= '3';
				}else{
					$info1['status']	= '0';
					$info1['returnable_id'] = $POST['eid'];
				}
			}else{
				if(empty($POST['eid'])){
					$info1['status']	= '3';
				}else{
					$info1['status']	= '0';
				}
			}

			if(empty($POST['edit_id'])) {
				$inserid=add_record($table, $info1, $dbcon);
				$return_item_id=$inserid;

				$sel_itrn = $dbcon->query("SELECT * FROM tbl_returnable_batch_stock_tmp where status=0 and product_id=".$POST['item_id']);
				
				if($sel_itrn->num_rows > 0) { 
					$infobatch['returnable_trn_id']= $return_item_id;
					$infobatch['status']= 1;
					
					while($r_itrn=brp_mysqli_fetch_array($sel_itrn))
					{
						$updateinvoicetrnid=update_record('tbl_returnable_batch_stock_tmp', $infobatch,"status=0 and ".$r_itrn['product_id']."=".$POST['item_id'] , $dbcon);
					}
				}

			} else {
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);
				$return_item_id=$POST['edit_id'];
			}


			
			if($returnable_type != "without_stock"){
				add_update_stock($dbcon,$return_item_id,$POST['item_id'],$POST['item_qty'],$POST['unit_id']);
			}

			$d_id=array();

			if(strtolower($POST['return_type'])=="product_wise"){	
				$total_delivery_qty=$POST['total_delivery_qty'];
				$delivery_date=$POST['delivery_date'];
				$arry_edit=$POST['arry_edit'];
				for($i=0;$i<count($total_delivery_qty);$i++)
				{
					$info_dil['return_item_id']		= $return_item_id;
					$info_dil['return_date']		= date('Y-m-d',strtotime($delivery_date[$i]));
					$info_dil['item_qty']			= $total_delivery_qty[$i];
					$info_dil['unit_id']			= $info1['item_unit_id'];
					
					$info_dil['user_id']			= $_SESSION['user_id'];
					$info_dil['cdate']			= date("Y-m-d h:i:s");
					$info_dil['company_id']			= $_SESSION['company_id'];
					//$info_dil['branch_id']		=$_SESSION['company_id'];
					//var_dump($info);
					
					$table_k='tbl_returnable_return_date';$tableid_k='return_date_id';
					//var_dump($total_delivery_qty[$i]);

					if(!empty($arry_edit[$i])){
						$updateid_k=update_record($table_k,$info_dil,"return_date_id=".$arry_edit[$i],$dbcon,$branch_id);
						array_push($d_id,$arry_edit[$i]);
					}else{
						$inserid_k=add_record($table_k,$info_dil,$dbcon,$branch_id);
						array_push($d_id,$inserid_k);
					}
				}	
			}else{
				$query_dd="select * from tbl_returnable_return_date as mst 
				where mst.return_date_id=".$inserid." order by return_date_id desc";
				$row_dd=$dbcon->query($query_dd);
				$rel_dd=brp_mysqli_fetch_assoc($row_dd);

				
				$info_dil['return_item_id']		= $return_item_id;
				$info_dil['return_date']		= date('Y-m-d',strtotime($POST['return_date']));
				$info_dil['item_qty']			= $info1['item_qty'];
				$info_dil['unit_id']			= $info1['item_unit_id'];
				
				$info_dil['user_id']			= $_SESSION['user_id'];
				$info_dil['cdate']				= date("Y-m-d h:i:s");
				$info_dil['company_id']			= $_SESSION['company_id'];
				//$info_dil['branch_id']		=$_SESSION['company_id'];
				//var_dump($info);
				$table_k='tbl_returnable_return_date';$tableid_k='return_date_id';
				
				if(!empty($rel_dd['return_date_id'])){
					$updateid_k=update_record($table_k,$info_dil,"return_date_id=".$rel_dd['return_date_id'],$dbcon,$branch_id);
					array_push($d_id,$rel_dd['return_date_id']);
				}else{
					$inserid_k=add_record($table_k,$info_dil,$dbcon,$branch_id);
					array_push($d_id,$inserid_k);
				}
			}

			$did=implode(",",$d_id);
			$info_dil_1['return_date_status']="2";
			$updateid_p=update_record($table_k,$info_dil_1,"return_item_id=".$return_item_id." and return_date_id NOT IN (".$did.")",$dbcon,$branch_id);
		//}

		if(empty($POST['edit_id'])) {
			$resp['resp']= '1';
		}else{
			$resp['resp']= 'update';
		}
		echo json_encode($resp);	
	}
	else if(strtolower($POST['mode'])=="load_challan_data"){
		$query = "select led.l_name,ch.* from tbl_returnable_channal as ch 
		left join tbl_ledger as led on led.l_id = ch.cust_id
		where id=".$POST['id'];
		$result = $dbcon->query($query);
		$row = brp_mysqli_fetch_array($result);
		$row['date'] = date('d-m-Y',strtotime($row['challan_date']));
		echo json_encode($row);
	} 
	else if(strtolower($POST['mode'])== "load_returnable_channal_info") {
		$returnable_type = $_POST['returnable_type'];
		$companyID = $_SESSION['company_id'];
		$userID =  $_SESSION['user_id'];
		if(empty($POST['returnable_id'])){
			$query="select item.*,pro.product_name,pro.product_icode,cat.unit_name 
						from tbl_returnable_channal_item as item 
						left join product_mst as pro on pro.product_id=item.item_id
						left join unit_mst as cat on cat.unitid=item.item_unit_id
		 				where item.status = 3  and `item`.`company_id` = $companyID";
		}else{
			$query="select item.*,pro.product_name,pro.product_icode,cat.unit_name 
						from tbl_returnable_channal_item as item 
						left join product_mst as pro on pro.product_id=item.item_id
						left join unit_mst as cat on cat.unitid=item.item_unit_id
		 				where item.status = 0  and `item`.`returnable_id` = ".$POST['returnable_id']." and `item`.`company_id` = $companyID";
		}
		
		$result=$dbcon->query($query);
		$str .= ' <div class="form-group">
						<div class="col-md-12 col-xs-12">
							<table cellspacing="10" style="border-spacing:10px; width: 90%" class="display table table12 table-bordered table-striped">
							<tr id="field">
								<th width="20%" class="text-center">Item Name</th>
								<th width="20%" class="text-center">Item Desc</th>
								<th width="10%" class="text-center"> Hsn Code</th>
								<th width="20%" class="text-center">Item Per</th>
								<th width="10%" class="text-center">Qty</th>
								<th width="10%" class="text-center">Price</th>
								<th width="10%" class="text-center">Action</th>
							</tr>';
			$rw_cnt = brp_mysqli_num_rows($result);
			if($rw_cnt > 0)
			{
				$i=1;$j=0;
				while($rel=mysqli_fetch_assoc($result)){

					$product_id = $rel['item_id'];
					$get_pro_type_qry="select product_type,product_base_unit from product_mst where product_id=".$product_id;
					$get_pro_type_rel=mysqli_fetch_assoc($dbcon->query($get_pro_type_qry));
		
					$product_type_arr = array("0", "1", "2", "3", "4", "5", "6", "7", "9","10","11","12", "-1");
					
					// if (in_array($get_pro_type_rel['product_type'], $product_type_arr)){

						if(!empty($rel['item_unit_id'])){
							$unit_id=$rel['item_unit_id'];
						}else{
							$unit_id=$get_pro_type_rel['product_base_unit'];
						}
						
						$current_stock = get_current_stock_new($dbcon,$product_id,$unit_id);
						
						$where=" and trancation_status!='2' and invoice_id='0'";
						
						$unclear_qty = get_unclear_stock($dbcon,$product_id,$unit_id,'tbl_invoicetrn','product_qty','product_id',$where);
						
						$product_stock = $current_stock-$unclear_qty;


					/*}
					else{
						$product_stock= 9999;
					}*/
					$with_out_stock_invoice ='';
					
					if($rel['approve_status']!=1){
						if($returnable_type != "without_stock"){
							if(round_up($product_stock,4) < round_up($rel['item_qty'],4)){
								$with_out_stock_invoice = "<strong style='color:red;' >Product stock is not enough.</strong>";
								$j++;
							}
						}
					}
					$product_st = "";
					if($returnable_type != "without_stock"){
						$product_st = "<strong style='color:green'>Product Stock : ".$product_stock."</strong>";
					}

					$batch_qry = "select * from tbl_returnable_batch_stock_tmp where status=1 and returnable_trn_id=".$rel['id'];
					$batch_result = $dbcon->query($batch_qry);

					$str_batch = "";
					while($b_row = brp_mysqli_fetch_assoc($batch_result)){
						$str_batch .= "</br>";
						$str_batch .= "<strong> Batcho No : ".$b_row['batch_no']." - ". $b_row['qty'] ." ". getunitname($dbcon,$b_row['unitid']) ." </strong>";
					}

				 	$str .= '<tr id="fieldtr'.$rel['id'].'" >
						  <td data-label="Item Name" style="vertical-align:top;" class="text-center">';
								if(empty($rel['product_name'])){
									$str .= '-';
								}else{
									$str .= $rel['product_name'] . " -- (".$rel['product_icode'].")";
								}
						$str .='<br>'.$product_st.'<br>'.$with_out_stock_invoice.$str_batch.'</td>
						<td data-label="User" style="vertical-align:top;" class="text-center">
							'.$rel['item_description'].'
						</td>
						<td data-label="User" style="vertical-align:top;" class="text-center">
							'.$rel['item_hsn'].'
						</td>
						<td data-label="User" style="vertical-align:top;" class="text-center">
							'.$rel['unit_name'].'
						</td>
						<td data-label="User" style="vertical-align:top;" class="text-center">
							'.$rel['item_qty'].'
						</td>
						<td data-label="User" style="vertical-align:top;" class="text-center">
							'.$rel['item_price'].'
						</td>';
						if($rel['approve_status'] == '0'){
							$str .= '<td data-label="ACTION" style="vertical-align:top">
								<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_returnable_channal_item_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
								<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_returnable_channal_item_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
							</td>';	
						}else{
							$str .="<td></td>";
						}
					$str .='</tr>';
					$i++;
				}
			}else{
					$str .= '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
			$str .= '</table>		 
				</div>
            </div>';
            $row['html_data']=$str;
            $row['temp_data_count']=$rw_cnt;
		if($j>0){
			$row['stock'] = "1";
		}
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "preedit") {
		$q = $dbcon -> query("SELECT item.*,pro.product_name,pro.batch_wise_stock_manage FROM tbl_returnable_channal_item as item 
			left join product_mst as pro on item.item_id=pro.product_id 
			WHERE id = '$POST[returnable_channal_id]'");
		$r = $q->fetch_assoc();
	
		echo json_encode($r);
	}
	else if(strtolower($POST['mode'])== "delete_data") {
		$row=array();
		$info['status']=2;	
		$updateid=update_record('tbl_returnable_channal_item', $info, "id=".$POST['returnable_channal_id'] , $dbcon);
		if($updateid){
			remove_reserve_stock_entry($dbcon,$POST['returnable_channal_id'],'trn',"");
			$row['res']="1";
		} else {
			$row['res']="0";
		}
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "approve_data") {
		$row=array();
		$q = $dbcon -> query("SELECT item.*,rect.branch_id,item.id as ritem_id,rect.returnable_type  FROM tbl_returnable_channal_item as item LEFT JOIN tbl_returnable_channal as rect ON rect.id = item.`returnable_id` WHERE item.id = '$POST[returnable_channal_id]'");

		$r = $q->fetch_assoc();

		$info['approve_status']=1;	
		$info['remark'] = $_POST['row_remark_desc'];

		$query="select batch_wise_stock_manage from product_mst where product_id=".$r['item_id'];

		$result=$dbcon->query($query);
		$row1=brp_mysqli_fetch_assoc($result);
		$returnable_trn_id = $r['id'];
		if($r['returnable_type'] == "without_stock"){
			$updateid=update_record('tbl_returnable_channal_item', $info, "id=".$r['ritem_id'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
		}else{
			//if($row1['batch_wise_stock_manage'] == '1'){
			if('1' == '1'){
				 $rr_approve_qty = $r['rr_approve_qty'];
				 $sel_stock = "select * from tbl_reserve_stock where stock_status=0 and stock_flage = 1 and ref_name='returning_receipt' and ref_id =".$r['id'];
				$sel_stock_rs = $dbcon->query($sel_stock);
				$cnt_stock_temp = brp_mysqli_num_rows($sel_stock_rs);
				if($cnt_stock_temp > 0){	
					while($sel_stock_rel=brp_mysqli_fetch_assoc($sel_stock_rs)){
						$info_stock['reserve_date']	=date('Y-m-d');
						$info_stock['product_id']	=$sel_stock_rel['product_id'];
						$info_stock['base_unit']	=$sel_stock_rel['base_unit'];
						$info_stock['base_stock']	=$sel_stock_rel['base_stock'];
						$info_stock['convert_unit']	=$sel_stock_rel['convert_unit'];
						$info_stock['convert_stock']	=$sel_stock_rel['convert_stock'];
						$info_stock['stock_flage']	=2;
						$info_stock['ref_name']		=$sel_stock_rel['ref_name'];
						$info_stock['ref_id']		=$sel_stock_rel['ref_id'];
						$info_stock['stock_id']		=$sel_stock_rel['stock_id'];
						$info_stock['godown_id']	=$sel_stock_rel['godown_id'];
						$info_stock['branch_id']	=$sel_stock_rel['branch_id'];
						$info_stock['cdate']		=date('Y-m-d H:i:s');
						$info_stock['user_id']		=$_SESSION['user_id'];
						$info_stock['company_id']	=$_SESSION['company_id'];
						
						$inserid=add_record('tbl_reserve_stock', $info_stock, $dbcon);
						
						/*$query_stock = "select * from tbl_stock_trn where stock_id=".$sel_stock_rel['stock_id'];
						$result_stock = $dbcon->query($query_stock);
						$row_stock = brp_mysqli_fetch_array($result_stock);
						$resstock_free['used_base_stock']		= $row_stock['used_base_stock'] - $info_stock['base_stock'];
						$resstock_free['used_convert_stock'] 	= $row_stock['used_convert_stock'] - $info_stock['convert_stock'];
						$updateid=update_record('tbl_stock_trn', $resstock_free, "stock_id=".$sel_stock_rel['stock_id'] , $dbcon);*/

						add_stock($dbcon,$sel_stock_rel['product_id'],$sel_stock_rel['base_unit'],$info_stock['reserve_date'],$info_stock['ref_name'],$info_stock['ref_id'],$info_stock['godown_id'],$info_stock['base_stock'],2,$info_stock['branch_id'],$info_stock['stock_id'],'');	

						$info_log['returnablechallan_item_id'] = $sel_stock_rel['ref_id'];
						$info_log['approve_remark']		   = $_POST['row_remark_desc'];
						$info_log['approve_status']		   = 1;
						$info_log['reserve_id']			   = $inserid;
						$info_log['cdate']			   = date('Y-m-d H:i:s');
						$info_log['user_id']			   = $_SESSION['user_id'];
						$info_log['company_id']			   = $_SESSION['company_id'];

						$insertlog = add_record('tbl_returnablechallan_aprv_log', $info_log, $dbcon);
						

						if($r['item_unit_id'] == $info_stock['convert_unit']){
							$rr_approve_qty = $rr_approve_qty + $info_stock['convert_stock'];
						}else{
							$rr_approve_qty = $rr_approve_qty + $info_stock['base_stock'];
						}
					}
					$info['rr_approve_qty'] = $rr_approve_qty;
					$info['rr_disapprove_qty'] = 0;
					//var_dump($info['rr_approve_qty']);
					// End Stock Approve
					$updateid=update_record('tbl_returnable_channal_item', $info, "id=".$r['ritem_id'] , $dbcon);
					if($updateid)
						$row['res']="1";
					else
						$row['res']="0";
				}else{
					$row['res']="-1";
				}
			}
		}
		/*else{
			// Stock Approve Time Plus & Minus Code
			$stock_qty = 0;
			$currentStock = get_godown_stock_check($dbcon, $r['item_id'], $r['item_unit_id']);
			//var_dump($currentStock);
			if(count($currentStock) > 0){
				$approved_stock = $_POST['row_value'];
				foreach($currentStock as $key => $value) {
					if($value >= $approved_stock){
						$product_id = $r['item_id'];
						$unit_id = $r['item_unit_id'];
						$stock_date = date('Y-m-d');
						$ref_name = "returning_receipt";
						$ref_id = $r['id'];
						$godown_id = $key;
						$stock_qty = $approved_stock;
						$stock_flag = '2';
						$branch_id = $r['branch_id'];
						add_stock($dbcon,$product_id,$unit_id,$stock_date,$ref_name,$ref_id,$godown_id,$stock_qty,$stock_flag,$branch_id);
						break;
					}else{
						$product_id = $r['item_id'];
						$unit_id = $r['item_unit_id'];
						$stock_date = date('Y-m-d');
						$ref_name = "returning_receipt";
						$ref_id = $r['id'];
						$godown_id = $key;
						$stock_qty = $value;
						$stock_flag = '2';
						$branch_id = $r['branch_id'];
						add_stock($dbcon,$product_id,$unit_id,$stock_date,$ref_name,$ref_id,$godown_id,$stock_qty,$stock_flag,$branch_id);
						$approved_stock = $approved_stock - $value;
					}
				}
				$info['rr_approve_qty'] = $r['rr_approve_qty'] + $stock_qty;
				$info['rr_disapprove_qty'] = 0;
				// End Stock Approve
				$updateid=update_record('tbl_returnable_channal_item', $info, "id=".$POST['returnable_channal_id'] , $dbcon);
				
				if($updateid)
					$row['res']="1";
				else
					$row['res']="0";
			}else{
				$row['res']="-1";
			}
		}*/
		
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "disapprove_data") {
		$row=array();
		$q = $dbcon -> query("SELECT item.*,rect.branch_id FROM tbl_returnable_channal_item as item LEFT JOIN tbl_returnable_channal as rect ON rect.id = item.`returnable_id` WHERE item.id = '$POST[returnable_channal_id]'");

		$r = $q->fetch_assoc();

		$info['approve_status']=2;	
		$info['remark'] = $_POST['row_remark_desc'];


		$query="select batch_wise_stock_manage from product_mst where product_id=".$r['item_id'];

		$result=$dbcon->query($query);
		$row1=brp_mysqli_fetch_assoc($result);
		$returnable_trn_id = $r['id'];
		if($row1['batch_wise_stock_manage'] == '1'){
			$rr_approve_qty = $r['rr_approve_qty'];

		 	$sel_stock = "select * from tbl_reserve_stock where stock_status=0 and ref_name='returning_receipt' and stock_status !=2 and ref_id =".$r['id'];
			$sel_stock_rs = $dbcon->query($sel_stock);
			$cnt_stock_temp = brp_mysqli_num_rows($sel_stock_rs);
			if($cnt_stock_temp > 0){	
				while($sel_stock_rel=brp_mysqli_fetch_assoc($sel_stock_rs)){
					$info_stock['stock_status']	= 2;
					
					$updateid=update_record('tbl_reserve_stock', $info_stock, "reserve_id=".$sel_stock_rel['reserve_id'],$dbcon);

					$updateid=update_record('tbl_stock_trn', $info_stock, "perent_id=".$sel_stock_rel['stock_id']." and ref_id = " .$sel_stock_rel['ref_id'] ." and ref_name ='" .$sel_stock_rel['ref_name']."'",$dbcon);
					if($sel_stock_rel['stock_flage'] == '2'){
						$que1="select base_unit,base_stock,used_base_stock,convert_unit,convert_stock,used_convert_stock,godown_id,customer_id,ref_id from tbl_stock_trn as ta where stock_id=".$sel_stock_rel['stock_id'];
						$rs_di1=$dbcon->query($que1);
						$re1=brp_mysqli_fetch_assoc($rs_di1);


						$used_base_stock=$re1['used_base_stock'] ;
						$used_convert_stock=$re1['used_convert_stock'];

						$upd_info_stock['used_base_stock']		= $used_base_stock - $sel_stock_rel['base_stock'];
						$upd_info_stock['used_convert_stock']	= $used_convert_stock - $sel_stock_rel['convert_stock'];

						
						$updatetrnid=update_record('tbl_stock_trn',$upd_info_stock,"stock_id=".$sel_stock_rel['stock_id'], $dbcon); 
					}
					

					if($r['item_unit_id'] == $info_stock['convert_unit']){
						$rr_approve_qty = $rr_approve_qty - $info_stock['convert_stock'];
					}else{
						$rr_approve_qty = $rr_approve_qty - $info_stock['base_stock'];
					}

					/*add_stock($dbcon,$sel_stock_rel['product_id'],$sel_stock_rel['base_unit'],$info_stock['reserve_date'],$info_stock['ref_name'],$info_stock['ref_id'],$info_stock['godown_id'],$info_stock['base_stock'],2,$info_stock['branch_id'],$info_stock['stock_id']);*/

					$info_log['returnablechallan_item_id'] = $sel_stock_rel['ref_id'];
					$info_log['approve_remark']		   = $_POST['row_remark_desc'];
					$info_log['approve_status']		   = 2;
					$info_log['reserve_id']			   = $sel_stock_rel['reserve_id'];
					$info_log['cdate']			   = date('Y-m-d H:i:s');
					$info_log['user_id']			   = $_SESSION['user_id'];
					$info_log['company_id']			   = $_SESSION['company_id'];

					$insertlog = add_record('tbl_returnablechallan_aprv_log', $info_log, $dbcon);				
				}
			$info['rr_disapprove_qty'] = $rr_approve_qty ;
			$info['rr_approve_qty'] = $rr_approve_qty ;
			// End Stock Approve
			$updateid=update_record('tbl_returnable_channal_item', $info, "id=".$POST['returnable_channal_id'] , $dbcon);
		
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			}else{
				$row['res']="-1";
			}
		}else{
		

		// Stock Approve Time Plus & Minus Code
		$stock_qty = 0;
		$currentStock = get_godown_stock_check($dbcon, $r['item_id'], $r['item_unit_id'],'');
		//var_dump($currentStock);
		if(count($currentStock) > 0){
			$approved_stock = $_POST['approve_qty'];
			foreach ($currentStock as $key => $value) {
				if($value >= $approved_stock){
					remove_reserve_stock_entry($dbcon,$r['id'],'trn',$stock_id="");
					$product_id = $r['item_id'];
					$unit_id = $r['item_unit_id'];
					$stock_date = date('Y-m-d');
					$ref_name = "returning_receipt";
					$ref_id = $r['id'];
					$godown_id = $key;
					$stock_qty = $approved_stock;
					$stock_flag = '1';
					$branch_id = $r['branch_id'];
					// add_stock($dbcon,$product_id,$unit_id,$stock_date,$ref_name,$ref_id,$godown_id,$stock_qty,$stock_flag,$branch_id);
					break;
				}else{
					remove_reserve_stock_entry($dbcon,$r['id'],'trn',$stock_id="");
					$product_id = $r['item_id'];
					$unit_id = $r['item_unit_id'];
					$stock_date = date('Y-m-d');
					$ref_name = "returning_receipt";
					$ref_id = $r['id'];
					$godown_id = $key;
					$stock_qty = $value;
					$stock_flag = '1';
					$branch_id = $r['branch_id'];
					// add_stock($dbcon,$product_id,$unit_id,$stock_date,$ref_name,$ref_id,$godown_id,$stock_qty,$stock_flag,$branch_id);
					$approved_stock = $approved_stock - $value;
				}

				$info_log['returnablechallan_item_id'] = $r['id'];
				$info_log['approve_remark']		   = $_POST['row_remark_desc'];
				$info_log['approve_status']		   = 2;
				//$info_log['reserve_id']			   = $sel_stock_rel['reserve_id'];
				$info_log['cdate']			   = date('Y-m-d H:i:s');
				$info_log['user_id']			   = $_SESSION['user_id'];
				$info_log['company_id']			   = $_SESSION['company_id'];

				$insertlog = add_record('tbl_returnablechallan_aprv_log', $info_log, $dbcon);
			}
			$info['rr_disapprove_qty'] = $r['rr_approve_qty'];
			$info['rr_approve_qty'] = $r['rr_approve_qty'] - $approved_stock;
			// End Stock Approve
			$updateid=update_record('tbl_returnable_channal_item', $info, "id=".$POST['returnable_channal_id'] , $dbcon);
			
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
		}else{
			$row['res']="-1";
		}
	}
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "delete_returnable_channal") {
		$row=array();
		$info['status']=2;
		$updateid=update_record('tbl_returnable_channal_item', $info, "returnable_id=".$POST['returnable_channal_id'] , $dbcon);
		$updateid=update_record('tbl_returnable_channal', $info, "id=".$POST['returnable_channal_id'] , $dbcon);
		if($updateid){
			remove_reserve_stock_entry($dbcon,$POST['returnable_channal_id'],'main',"");
		}
		
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"returnable_channal_add",3,"tbl_returnable_channal",$POST['returnable_channal_id']);	
		
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	} 
	else if(strtolower($POST['mode'])== "load_purhcase_order_data") {
		
		$id=$POST['order_id'];
		$grn_type=$POST['grn_type'];
		
		if($grn_type==2)
		{
			$resp['pro_html'] = get_po_details_for_grn_trn($dbcon,$id,$grn_type,$POST['mode1'],$POST['eid'],$POST['vender_id'],$POST['branch_id']);
			$resp['request_id'] ='';
		}
		else
		{
			$resp['pro_html'] = get_jobwork_details_for_grn_trn($dbcon,$id,'',$POST['mode1'],$POST['eid'],$POST['vender_id'],$POST['order_id']);
			$resp['request_id'] = get_request_id_jobwork($dbcon,$id);
		}
		
		$vendor_id=get_vender_id($dbcon,$id,$grn_type);
		$resp['vendor_id'] = $vendor_id;
		$resp['vendor_name'] = get_vender_name($dbcon,$vendor_id,$grn_type);
		
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode'])== "load_productdetail") {
		$product_id=$POST['product_id'];
		$query="select trn.*,main_grn_qty from product_mst as trn
		left join (SELECT purchaseorder_id,product_id,sum(product_qty) as main_grn_qty FROM tbl_grn_trn as chtrn where chtrn.grn_trn_status!=2 and chtrn.product_id=".$product_id." group by chtrn.product_id,chtrn.purchaseorder_id) as chtrn on chtrn.product_id=trn.product_id
		where trn.product_status=0 and trn.product_id=".$product_id;
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$rel['pending_qty']=floatval($rel['product_qty'])-floatval($rel['main_grn_qty']);
		$rel['product_desc']=$rel['product_desc'];
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
	else if(strtolower($POST['mode'])=="load_stock_qty")
	{
		$product_id=$POST['product_id'];
		$get_pro_type_qry="select product_type,product_base_unit from product_mst where product_id=".$product_id;
		$get_pro_type_rel=mysqli_fetch_assoc($dbcon->query($get_pro_type_qry));
		

		$product_type_arr = array("0", "1", "2", "3", "4", "5", "6", "7", "9","10","11","12", "-1");
		// if (in_array($get_pro_type_rel['product_type'], $product_type_arr)){
			if(!empty($POST['unit_id'])){
				$unit_id=$POST['unit_id'];
			}else{
				$unit_id=$get_pro_type_rel['product_base_unit'];
			}
			$current_stock = get_current_stock_new($dbcon,$product_id,$unit_id);
			$where=" and trancation_status!='2' and invoice_id='0'";
			$rstock=reserve_stock($dbcon,$POST['product_id'],$POST['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$stock_id);
			
			$unclear_qty = get_unclear_stock($dbcon,$product_id,$unit_id,'tbl_invoicetrn','product_qty','product_id',$where);
			echo $current_stock-$unclear_qty-$rstock;
		/*}
		else{
			echo 9999;
		}*/
		
	}
	else if(strtolower($POST['mode'])== "load_grn_no") {
		$row=array();
		$query1="select * from tbl_invoicetype where type_id='8' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
		$rows=mysqli_fetch_assoc($dbcon->query($query1));
		$id=$rows['taxinvoice_start'];
		$id=$id+1;
		if($rows['invoice_format']=='2') {
			$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
		}
		else if($rows['invoice_format']=='1') {
			$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
		}
		else if($rows['invoice_format']=='3'){
			$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
		}
		else{
			$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
		}
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "get_order_no") {
		
		$grn_type=$POST['grn_type'];
		$vender_id=$POST['vender_id'];
		
		if($grn_type==2)
		{
			$row=get_all_po_for_grn($dbcon,$rel['purchaseorder_id'],$vender_id,$mode);
		}
		else
		{
			$row=get_all_jobwork_for_grn($dbcon,$rel['purchaseorder_id'],$vender_id,$mode);
		}
		echo $row;
	} 
	else if(strtolower($POST['mode'])== "get_hsn_code")
	{
		$qry="SELECT hc.hsn_code FROM `product_mst` as pm
		join mst_hsn_code as hc on pm.product_hsn=hc.hsn_id and hc.hsn_status=0
		where pm.product_id=".$POST['product_id']." and pm.company_id=".$_SESSION['company_id']."";
		$row=brp_mysqli_fetch_assoc($dbcon->query($qry));
		print_r($row['hsn_code']);
	}
	else if(strtolower($POST['mode']) == "get_salesorder_no") {
		$cust_id=$POST['cust_id'];	
		$sales_id = $POST['sales_id'];			
		echo get_salesorder_no_returnable($dbcon,$cust_id,$sales_id);
	}
	else if(strtolower($POST['mode']) == "get_sales_order_data_load") {
		$query = "select trn.*,(select IFNULL(sum(ret.item_qty),0) from tbl_returnable_channal_item as ret where ret.status != 2 and ret.sales_ordertrn_id=trn.sales_ordertrn_id) as done_qty from tbl_sales_ordertrn as trn where sales_ordertrn_status=0 and sales_order_id=".$POST['sales_order_id']." HAVING trn.product_qty>done_qty";
		$returnable_type = $POST['returnable_type'];
		$q = $dbcon -> query($query);
		while($row=brp_mysqli_fetch_array($q)){
			$pending_qty = $row['product_qty'] - $row['done_qty'];
			$info['item_id']			= $row['product_id'];
			$info['item_hsn']			= $row['product_hsn_code'];	
			$info['item_unit_id']		= $row['unit_id'];
			$info['item_description']	= $row['description'];
			$info['item_qty']			= $pending_qty;
			$info['item_price']		= $row['product_rate'];
			$info['sales_ordertrn_id']	= $row['sales_ordertrn_id'];
			
			if($POST['eid'] != ''){
				$info['status']		= 0;
				$info['returnable_id']	= $POST['eid'];
			}else{
				$info['status']		= 3;	
			}
			
			$info['company_id']		= $_SESSION['company_id'];
			$info['user_id']			= $_SESSION['user_id'];
			$inserid=add_record('tbl_returnable_channal_item', $info, $dbcon);

			if($returnable_type != "without_stock"){
				add_update_stock($dbcon,$inserid,$info['item_id'],$info['item_qty'],$row['unit_id']);
			}
		}
	}
	else if(strtolower($POST['mode'])== "return_date_model_open")
	{
		if(empty($POST['trn_id'])){
			echo '<input type="hidden" name="count" id="count" value="1" />
			<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
				<tr id="field">
				
				<th width="30%"  class="text-center" style="vertical-align:center;">Date</th>
				<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
				<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
				</tr>
				<tr id="field1">
				
				<td   class="text-center" style="vertical-align:center;">
				<input type="text" class="form-control default-date-picker delivery_date" id="delivery_date1" name="delivery_date[]" placeholder="Delivery Date" onkeyup="qty_wise_date_validation(1);" >
				</td>
				<td	 class="text-center;" style="vertical-align:center;">
				<input type="text" class="form-control delivery_qty" id="delivery_qty1" name="delivery_qty[]" placeholder="'.$POST["qty"].'" onchange="validate_dilivary_date();" onkeyup="qty_wise_date_validation(1);" />
				</td>
				<td	 class="text-center;" style="vertical-align:center;">
				<input type="hidden" name="arry_sr[]" id="arry_sr" value="1" />
				</td>
				</tr>
				</table>';
		}else{
			$qry="SELECT * FROM `tbl_returnable_return_date` WHERE return_date_status=0 and return_item_id=".$POST['trn_id']." order by return_date_id";
			$row=$dbcon->query($qry);
			$cnt=brp_mysqli_num_rows($row);
			if($cnt>0){
				$i=1;
				echo '<input type="hidden" name="count" id="count" value="'.$cnt.'" />
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
				<tr id="field">
				<th width="30%"  class="text-center" style="vertical-align:center;">Date</th>
				<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
				<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
				</tr>';
				
				while($tax=brp_mysqli_fetch_assoc($row))
				{
					$date=date('d-m-Y',strtotime($tax['return_date']));
					echo '<tr id="field'.$i.'">
					
					<td   class="text-center" style="vertical-align:center;">
					<input type="text" class="form-control default-date-picker delivery_date" id="delivery_date'.$i.'" name="delivery_date[]" placeholder="Delivery Date" value="'.$date.'" onkeyup="qty_wise_date_validation('.$i.');" >
					</td>
					<td	 class="text-center;" style="vertical-align:center;">
					<input type="text" class="form-control delivery_qty" id="delivery_qty'.$i.'" name="delivery_qty[]" placeholder="'.$tax["item_qty"].'" value="'.$tax["item_qty"].'" onchange="validate_dilivary_date();" onkeyup="qty_wise_date_validation('.$i.');" />
					</td>
					<td	 class="text-center;" style="vertical-align:center;">
					<input type="hidden" name="arry_sr[]" id="arry_sr'.$i.'" value="'.$i.'" />
					<input type="hidden" class="arry_edit" name="arry_edit[]" id="arry_edit'.$i.'" value="'.$tax["return_date_id"].'" />';
					if($i!=1){
						echo '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="remove_dilivary_date('.$i.');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>';
					}
					echo '</td>
					</tr>';
					$i++;
				}
				echo '</table>';
			}else{
				echo '<input type="hidden" name="count" id="count" value="1" />
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
				<tr id="field">
				<th width="60%"  class="text-center" style="vertical-align:center;">Date</th>
				<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
				<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
				</tr>
				<tr id="field1">
				<td class="text-center" style="vertical-align:center;">
				<input type="text" class="form-control default-date-picker delivery_date" id="delivery_date1" name="delivery_date[]" placeholder="Delivery Date" onkeyup="qty_wise_date_validation(1);" >
				</td>
				<td	 class="text-center;" style="vertical-align:center;">
				<input type="text" class="form-control delivery_qty" id="delivery_qty1" name="delivery_qty[]" placeholder="'.$POST["qty"].'" onchange="validate_dilivary_date();" onkeyup="qty_wise_date_validation(1);" />
				</td>
				<td	 class="text-center;" style="vertical-align:center;">
				<input type="hidden" name="arry_sr[]" id="arry_sr" value="1" />
				</td>
				</tr>
				</table>';
			}
		}
	}else if(strtolower($POST['mode'])== "get_batch_qty"){
		$stock_id = $POST['batch_no'];
		$gstock=0;$rstock=0;
		$batch_no=$POST['batch_no'];
		$gstock=get_current_godown_stock_new($dbcon,$POST['product_id'],$POST['unit_id'],$POST['st_godown_id'],$branch_id,$stock_id);

		$rstock=reserve_stock($dbcon,$POST['product_id'],$POST['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$stock_id);


		$stock=$gstock-$rstock;
		echo $stock;
	}else if(strtolower($POST['mode'])== "batch_stock_model_open"){

		$query="select i.*,(IFNULL(sum(base_stock),0)-IFNULL(sum(used_base_stock),0)) as pending_base_stock,(IFNULL(sum(convert_stock),0)-IFNULL(sum(used_convert_stock),0)) as pending_conv_stock,group_concat(i.stock_id) as b_stock_id from tbl_stock_trn as i
			where stock_status=0 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and stock_flage = 1 and product_id = ".$POST['product_id']." and batch_no != '' group by batch_no";
			$rs_batch=$dbcon->query($query);
			$str= '<option value="">Choose Batch No</option>';
			while($rel=brp_mysqli_fetch_assoc($rs_batch))
			{	
				if($rel['pending_base_stock'] > 0){
					$str.= '<option value="'.$rel['b_stock_id'].'" data-stock="'.$rel['base_stock'].'" >'.$rel['batch_no'].'</option>';
				}
			}
			$html='';
			if($POST['isbatchwise']==1){
				$html .= '<div class="col-md-12">				
				<div class="col-md-5">
					<div class="form-group">
						<label for="batch_id">Batch No</label>
						<select class="form-control batch_select2" name="batch_id" id="batch_id" onChange="get_batch_qty(this.value);" >
						"'.$str.'"
						</select>							
					</div>	
				</div>';
			}
			
			$html .='<div class="col-md-5">
				<div class="form-group">
					<label for="godown_id">Godown Stock</label>
					<select class="form-control batch_select2" name="godown_id" id="godown_id" onChange="get_batch_qty(this.value);" >
					<option value="">Choose Godown</option>
					'.load_available_stock_godown($dbcon,$POST['product_id'],0).'
					</select>							
				</div>	
			</div>

			<div class="col-md-3">
				<div class="form-group">
					<label for="batch_stock">Total Qty</label>
					<input type="number" min="0" class="form-control" name="batch_stock" id="batch_stock" readonly />
				</div>	
			</div>

			<div class="col-md-3">
				<div class="form-group">
					<label for="qtyforbatch">Qty</label>
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
			
			if(!empty($POST['edit_id'])){
				$str = " and bst.returnable_trn_id=".$POST['edit_id']." and bst.status=1 ";
			}else{
				$str = " and bst.status=0";
			}
			$appData = array();
			$i=1;
			$aColumns = array('bst.qty','bst.batch_no','bst.batch_stk_id','bst.stock_id','gd.gd_name');
			$sTable = "tbl_returnable_batch_stock_tmp as bst";			
			$isJOIN = array("left join mst_godown as gd on gd.gd_id=bst.godown_id");
			$sIndexColumn = "bst.batch_stk_id";
			$where = "  bst.product_id='".$POST['product_id']."' ".$str." ";
			$isWhere = array($where);
			$hOrder = "bst.batch_stk_id desc";
			include($path.'include/pagging.php');
			$id=1;
			$edit = $delete = '';
			foreach($sqlReturn as $row) {
				$row_data = array();

				$row_data[] = $row['batch_no'];
				$row_data[] = $row['gd_name'];
				$row_data[] = $row['qty'];
				$batch_no = "'" . $row['batch_no'] . "'";
				$stock_id = "'" . $row['stock_id'] . "'";
				$delete='';
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
				$str = " and returnable_trn_id=".$POST['edit_id']." and status=1 ";
				$info['returnable_trn_id']   = $POST['edit_id'];
				$info['status']   = 1;
			}else{
				$str = " and returnable_trn_id=0 and status=0 ";
			}

			$tr = $dbcon -> query("SELECT batch_no FROM tbl_returnable_batch_stock_tmp where batch_no='".$POST['batch_no']."' and godown_id='".$POST['isbatchwise']."'".$str);
			if($tr->num_rows > 0) {
				$row['res'] = '-1';
			} else {

				$info['product_id']   	= $POST['product_id'];
				$info['batch_no']   	= $POST['batch_no'];
				$info['godown_id']   	= $POST['godown_id'];
				$info['stock_id']   	= $POST['stock_id'];
				$info['qty']   			= $POST['qty'];
				$info['unitid']   		= $POST['unit_id'];
				$info['cdate']			= date("Y-m-d H:i:s");
				$info['user_id']		= $_SESSION['user_id'];
				$info['company_id']		= $_SESSION['company_id'];		

				$inserbatchstockid=add_record('tbl_returnable_batch_stock_tmp', $info, $dbcon);

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
				$str = " and bst.returnable_trn_id=".$POST['edit_id']." and bst.status=1 ";
			}else{
				$str = " and bst.returnable_trn_id=0 and bst.status=0 ";
			}
			
			$qry2="SELECT sum(bst.qty) as qty FROM tbl_returnable_batch_stock_tmp as bst 
			left join `tbl_stock_trn` as st on st.stock_id=bst.stock_id 
			where bst.product_id=".$POST['product_id']." ".$str." ";

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
				
			$returnable_trn_id = $POST['returnable_channal_id'];	
			$batch_no = $POST['batch_no'];
			$stock_id =  $POST['stock_id'];	

			$updateid=update_record("tbl_returnable_batch_stock_tmp", $info, "batch_stk_id=".$POST['batchstockid'] , $dbcon);
			
			if($updateid){
				remove_reserve_stock_entry($dbcon,$returnable_trn_id,'trn',$stock_id);
				$row['res']="1";
			}
			else{
				$row['res']="0";
			}
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "get_product_check"){
			$where = "";
			if($POST['eid']){
				$where = ' and status = 0 and returnable_id='.$POST['eid'];
			}else{
				// $where = ' and status = 3 and user_id='.$_SESSION['user_id'];
			}

			$query = "select * from tbl_returnable_channal_item as item where `company_id` = ".$_SESSION['company_id'].$where;
			$result=$dbcon->query($query);
			$cnt = brp_mysqli_num_rows($result);
			$row=brp_mysqli_fetch_assoc($result);
			echo $cnt;
		}else if(strtolower($POST['mode'])== "auto_delete_temp_data"){
			
			$query = "select * from tbl_returnable_channal_item as item where status = 3 and `company_id` = ".$_SESSION['company_id'];
			$result=$dbcon->query($query);
			
			while($row=brp_mysqli_fetch_assoc($result)){
				$id = $row['id'];
				remove_reserve_stock_entry($dbcon,$id,'trn');
				$info['status'] = 2;
				$updatetrnid=update_record('tbl_returnable_channal_item',$info,"id=".$id , $dbcon);		
			}
			echo $cnt;
		}

function item_reserve_stock_entry($dbcon,$product_id,$base_unit,$conv_unit,$base_stock,$con_stock,$chalan_type,$returnable_trn_id,$stock_id,$godown_id,$branch_id){


	$qry = "select * from tbl_reserve_stock where stock_status = 0 and stock_flage = 1 and ref_name='returning_receipt' and ref_id=" . $returnable_trn_id . " and product_id = " . $product_id . " and stock_id = " . $stock_id;
	$result = $dbcon->query($qry);
	$cnt = brp_mysqli_num_rows($result);
	
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


function remove_reserve_stock_entry($dbcon,$returnable_id,$type,$stock_id=""){
	if($type == 'trn'){
		$query = "select * from tbl_returnable_channal_item where id = " . $returnable_id;
	}else{
		$query = "select * from tbl_returnable_channal_item where status = 2 and approve_status = 0 and returnable_id = " . $returnable_id;	
	}
	
	$result = $dbcon->query($query);
	$whr_stock = "";
	if(!empty($stock_id)){
		$whr_stock = " and stock_id in(".$stock_id.")";
	}
	while($row = brp_mysqli_fetch_assoc($result)){
		
		$query1 = "select * from tbl_reserve_stock where stock_status = 0 and ref_name = 'returning_receipt' and ref_id = " . $row['id'].$whr_stock;
		$result1 = $dbcon->query($query1);
		while($row1 = brp_mysqli_fetch_assoc($result1)){
			$reserve_id = $row1['reserve_id'];
			$stock_id = $row1['stock_id'];

			$info['stock_status'] = 2;

			$updatetrnid=update_record('tbl_reserve_stock',$info,"reserve_id=".$reserve_id , $dbcon);

			$que1="select base_unit,base_stock,used_base_stock,convert_unit,convert_stock,used_convert_stock,godown_id,customer_id,ref_id from tbl_stock_trn as ta where stock_id=".$stock_id;
			$rs_di1=$dbcon->query($que1);
			$re1=brp_mysqli_fetch_assoc($rs_di1);


			$used_base_stock=$re1['used_base_stock'] ;
			$used_convert_stock=$re1['used_convert_stock'];
			
			$upd_info_stock = array();
			/* if($row1['stock_flage']==2){
				$upd_info_stock['used_base_stock']		= $used_base_stock - $row1['base_stock'];
				$upd_info_stock['used_convert_stock']	= $used_convert_stock - $row1['convert_stock'];
			}else{
				$upd_info_stock['used_base_stock']		= $used_base_stock + $row1['base_stock'];
				$upd_info_stock['used_convert_stock']	= $used_convert_stock + $row1['convert_stock'];
			}*/

			if($row1['stock_flage']==2){
				$info_stock['stock_status'] = 2;
				$updateid=update_record('tbl_stock_trn', $info_stock, "perent_id=".$stock_id." and ref_id = " .$row1['ref_id'] ." and ref_name ='" .$row1['ref_name']."'",$dbcon); 
			}
			
			$upd_info_stock['used_base_stock']		= $used_base_stock - $row1['base_stock'];
			$upd_info_stock['used_convert_stock']	= $used_convert_stock - $row1['convert_stock'];

			// var_dump($upd_info_stock);
			
			$updatetrnid=update_record('tbl_stock_trn',$upd_info_stock,"stock_id=".$stock_id, $dbcon);
		}
	}
}


function add_update_stock($dbcon,$return_item_id,$product_id,$item_qty,$unit_id){
	$query1 = "select * from tbl_reserve_stock where stock_flage = 1 and stock_status = 0 and ref_name = 'returning_receipt' and ref_id = " . $return_item_id;
	$result1 = $dbcon->query($query1);
	while($row1 = brp_mysqli_fetch_assoc($result1)){
		
		$reserve_id = $row1['reserve_id'];
		$stock_id = $row1['stock_id'];
		$info['stock_status'] = 2;
			$updatetrnid=update_record('tbl_reserve_stock',$info,"reserve_id=".$reserve_id , $dbcon);

			$que1="select base_unit,base_stock,used_base_stock,convert_unit,convert_stock,used_convert_stock,godown_id,customer_id,ref_id from tbl_stock_trn as ta where stock_id=".$stock_id;
			$rs_di1=$dbcon->query($que1);
			$re1=brp_mysqli_fetch_assoc($rs_di1);

			$used_base_stock=$re1['used_base_stock'] ;
			$used_convert_stock=$re1['used_convert_stock'];
			
			$upd_info_stock['used_base_stock']	= $used_base_stock - $row1['base_stock'];
			$upd_info_stock['used_convert_stock']	= $used_convert_stock - $row1['convert_stock'];
			
			$updatetrnid=update_record('tbl_stock_trn',$upd_info_stock,"stock_id=".$stock_id , $dbcon);
	}

	$sel_stock = "select * from tbl_returnable_batch_stock_tmp where status=1 and returnable_trn_id=".$return_item_id;
	$sel_stock_rs = $dbcon->query($sel_stock);

	$sel_pro = "select * from product_mst where product_status=0 and product_id=".$product_id;
	$sel_pro_rs = $dbcon->query($sel_pro);
	$sel_pro_rel = brp_mysqli_fetch_assoc($sel_pro_rs);
	$cnt_stock_temp = brp_mysqli_num_rows($sel_stock_rs);
	if($cnt_stock_temp > 0){
		while($sel_stock_rel=brp_mysqli_fetch_assoc($sel_stock_rs)){
			$reserve_qty=$sel_stock_rel['qty'];
			$batch_where="";
			$batch_no = $sel_stock_rel['batch_no'];
			$godown_id = $sel_stock_rel['godown_id'];

			if(!empty($godown_id)){
				$batch_where .=" and i.godown_id='".$sel_stock_rel['godown_id']."'";
			}

			if(!empty($batch_no)){
				$batch_where .=" and i.batch_no='".$sel_stock_rel['batch_no']."'";
			}
			
			$query_dstock="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i	
			where stock_status=0 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) ".$batch_where." and i.product_id=".$sel_stock_rel['product_id'];

			$result_dstock=$dbcon->query($query_dstock);
			while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
				if($row_dstock['convert_unit']==$sel_stock_rel['unit_id']){
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

					if($sel_pro_rel['product_conv_unit']==$sel_stock_rel['unit_id']){
						$type="base_unit";
						$con_stock=$rqty;
						$base_stock=convert_stock_new($dbcon,$rqty,$sel_stock_rel['product_id'],$type);
					}else{
						$type="conv_unit";
						$base_stock=$rqty;
						$con_stock=convert_stock_new($dbcon,$rqty,$sel_stock_rel['product_id'],$type);
					}

					item_reserve_stock_entry($dbcon,$sel_stock_rel['product_id'],$sel_pro_rel['product_base_unit'],$sel_pro_rel['product_conv_unit'],$base_stock,$con_stock,"returning_receipt",$return_item_id,$row_dstock['stock_id'],$row_dstock['godown_id'],$row_dstock['branch_id']);
				}
			}
		}
	}else{
		// var_dump($item_qty);
		if($item_qty > 0){
			$query_dstock="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i	
				where stock_status=0 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and i.product_id=".$product_id;
		 	
		 	/*$qry_11 = "select * from tbl_stock_trn where stock_status = 0 and stock_flage = 1 and product_id = " . $sel_pro_rate_rel['item_id'] . " and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5))";*/

			$res_11 = $dbcon->query($query_dstock);

			while($row_11=brp_mysqli_fetch_array($res_11)){
				// var_dump($item_qty);
				if($row_11['convert_unit']==$unit_id){
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
					
					if($unit_id==$sel_pro_rel['product_conv_unit']){
						$type="base_unit";
						$con_stock=$rqty;
						$base_stock=convert_stock($dbcon,$con_stock,$product_id,$type);
					}else{
						$type="conv_unit";
						$base_stock=$rqty;
						$con_stock=convert_stock($dbcon,$base_stock,$product_id,$type);
					}
					item_reserve_stock_entry($dbcon,$product_id,$sel_pro_rel['product_base_unit'],$sel_pro_rel['product_conv_unit'],$base_stock,$con_stock,"returning_receipt",$return_item_id,$stock_id,$row_11['godown_id'],$row_11['branch_id']);
				}
			}
		}
	}
}
?>