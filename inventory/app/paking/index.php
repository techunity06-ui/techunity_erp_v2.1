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
	$s_date=explode(' - ',$POST['date']);
	$where = "";
	$where.=" and gstock.so_paking_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND gstock.so_paking_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
	if($POST['pstatus']==2){
		$where.=" and invoice_status=0";
	}else if($POST['pstatus']==1){
		$where.=" and invoice_status=1";
	}
	$appData = array();
	$i=1;
	
	$aColumns = array('gstock.so_paking_id','gstock.so_paking_no', 'gstock.so_paking_date','led.l_name','gstock.so_paking_remark','invoice_status');
	$sIndexColumn = "gstock.so_paking_id";
	$isWhere = array("gstock.status = 0".$where.check_company('gstock'));
	$sTable = "so_paking as gstock";
	$isJOIN = array('left join tbl_ledger as led on led.l_id=gstock.so_paking_cust_id');
	$hOrder = "gstock.so_paking_id desc";
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();

		$row_data[] = $row['sr'];
		$row_data[] = $row['so_paking_no'];
		$row_data[] = date('d-m-Y',strtotime($row['so_paking_date']));
		$row_data[] = $row['l_name'];
		$row_data[] = $row['so_paking_remark'];
		$edit_btn=''; $delete_btn='';$apprv_btn='';$print='';
		if($row['invoice_status'] == '0'){
			$row_data[] = '<button class="btn btn-xs btn-danger" >Invoice Pending</button>';
			
			$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'paking_edit/'.$row['so_paking_id'].'"><i class="fa fa-pencil"></i></a>';
			$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_paking_data('.$row['so_paking_id'].')"><i class="fa fa-trash-o"></i></button>';
		}else{
			$row_data[] = '<button class="btn btn-xs btn-success " >Invoice Done</button>';
		}
		

		$row_data[] = $edit_btn.' '.$delete_btn.' '.$apprv_btn.' '.$print.' '.$stiker;
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}else if(strtolower($POST['mode']) == "fetch_pending") {
	
	$appData = array();
	$i=1;
	
	$aColumns = array('gstock.sales_ordertrn_id','so.sales_order_no','so.sales_order_date','led.l_name','pro.product_name','gstock.product_qty','so.cust_id','so.sales_order_id','(IFNULL(con_stock_add,0)-IFNULL(con_stock_min,0)) as penqty');
	$sIndexColumn = "gstock.sales_ordertrn_id";
	$isWhere = array("gstock.sales_ordertrn_status = 0".$where.check_company('so'));
	$sTable = "tbl_sales_ordertrn as gstock";
	$isJOIN = array('left join tbl_sales_order as so on so.sales_order_id=gstock.sales_order_id',
					'left join tbl_ledger as led on led.l_id=so.cust_id',
					'left join product_mst as pro on pro.product_id=gstock.product_id',
					'left join (select sum(qc.base_stock) as con_stock_add,qc.sales_order_trn_id from tbl_reserve_stock as qc where qc.stock_status != 2 and stock_flage=1 and temp_stock_allocate=1 and qc.company_id='.$_SESSION["company_id"].' group by qc.sales_order_trn_id) as qc2 on qc2.sales_order_trn_id=gstock.sales_ordertrn_id',
					'left join (select sum(qc.base_stock) as con_stock_min,qc.sales_order_trn_id from tbl_reserve_stock as qc where qc.stock_status != 2 and temp_stock_allocate=1 and stock_flage=2 and qc.company_id='.$_SESSION["company_id"].' group by qc.sales_order_trn_id) as qc3 on qc3.sales_order_trn_id=gstock.sales_ordertrn_id');
	$hOrder = "gstock.sales_ordertrn_id desc";
	$having=" penqty > 0";
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();

		$row_data[] = $row['sr'];
		$row_data[] = $row['sales_order_no'];
		$row_data[] = date('d-m-Y',strtotime($row['sales_order_date']));
		$row_data[] = $row['l_name'];
		$row_data[] = $row['product_name'];
		$row_data[] = $row['penqty'];
		
		$edit_btn=''; $delete_btn='';$apprv_btn='';$print='';
		
			$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Add" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'paking_add_sin/'.$row['cust_id'].'/'.$row['sales_order_id'].'"><i class="fa fa-plus"></i></a>';
			
		
		

		$row_data[] = $edit_btn;
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}else if(strtolower($POST['mode'])== "add")
	{
		//update_common_no($dbcon,50);
		$info['so_paking_no']				= date('hisdmY');
		$info['so_paking_date']				= date('Y-m-d',strtotime($POST['paking_date']));
		$info['so_paking_cust_id']			= $POST['cust_id'];
		$info['so_paking_remark']			= $_POST['remark'];
		$info['cdate']						= date("Y-m-d h:i:s");
		$info['user_id']					= $_SESSION['user_id'];
		$info['company_id']					= $_SESSION['company_id'];
		
		$inserid=add_record('so_paking', $info, $dbcon);

		if($inserid){
			$inftrn['so_paking_id'] = $inserid;
			$inftrn['status'] = 0;
			$updatetrnid=update_record('so_paking_trn', $inftrn,"user_id=".$_SESSION['user_id']." and status=3 and so_paking_cust_id=".$info['so_paking_cust_id'] , $dbcon);
		}

		if($inserid)
		{	
			$arr['msg']="1";							
		}
		else{
			$arr['msg']="0";
		}

		echo json_encode($arr);

	}else if(strtolower($POST['mode'])== "edit")
	{
		$info['so_paking_no']	= $POST['paking_no'];
		$info['so_paking_date']	= date('Y-m-d',strtotime($POST['paking_date']));
		$info['so_paking_cust_id']			= $POST['cust_id'];
		$info['so_paking_remark']			= $_POST['remark'];
		$info['cdate']			= date("Y-m-d h:i:s");
		$info['user_id']			= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];

		//var_dump($info);

		$updateid=update_record('so_paking', $info," so_paking_id=".$POST['eid'] , $dbcon);

		if($updateid)
		{	
			$arr['msg']="update";	
		}
		else{
			$arr['msg']=0;
		}

		echo json_encode($arr);
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
	}else if(strtolower($POST['mode'])== "load_sales_order"){	
		//pathik start
		$qry="select sales_order_id,sales_order_no from `tbl_sales_order` as so
		 where cust_id=".$POST['cust_id'];
		$result=$dbcon->query($qry);
		$str = "<option value=''> Select Sales Order </option>";
		while($row=brp_mysqli_fetch_assoc($result)){
			
	        $str .= "<option value=".$row['sales_order_id'].">". $row['sales_order_no'] ."</option>";
	        
		}
		$res['so_no']=$str;
		echo json_encode($res);
}
else if(strtolower($POST['mode'])== "get_sales_product"){	
		//pathik start
		$qry="select pro.product_id,sales_ordertrn_id,pro.product_name from `tbl_sales_ordertrn` as so
			left join product_mst as pro on pro.product_id=so.product_id
		 where sales_order_id=".$POST['so_id'];
		$result=$dbcon->query($qry);
		$str = "<option value=''> Select Product </option>";
		while($row=brp_mysqli_fetch_assoc($result)){
			
	        $str .= "<option value=".$row['sales_ordertrn_id'].">". $row['product_name'] ."</option>";
	        
		}
		$res['so_product']=$str;
		echo json_encode($res);
}
else if(strtolower($POST['mode'])== "get_product_pen_qty"){	
		//pathik start
		$qry="select product_qty,unit.unit_name from `tbl_sales_ordertrn` as so
				left join unit_mst as unit on unit.unitid=so.unit_id
				where sales_ordertrn_id=".$POST['so_tr_id'];
		$result=$dbcon->query($qry);
		$row=brp_mysqli_fetch_assoc($result);
		
		$row['show_qty']="Qty=".$row['product_qty'];
		echo json_encode($row);
}
else if(strtolower($POST['mode'])== "add_entry"){	
		if($POST['eid']){
			$info['so_paking_id']			= $POST['eid'];
			$info['status']					= 0;
		}else{
			$info['so_paking_id']			= 0;
			$info['status']					= 3;
		}

		$info['so_paking_cust_id']			= $POST['cust'];
		$info['so_paking_so_id']			= $POST['salesorderid'];
		$info['so_paking_so_trn_id']		= $POST['sales_order_trn_id'];
		$info['so_paking_batch_no']			= $POST['batch_no'];
		$info['so_paking_qty']				= $POST['qty'];

		$info['cdate']					= date("Y-m-d H:i:s");
		$info['user_id']				= $_SESSION['user_id'];
		$info['company_id']				= $_SESSION['company_id'];
		
		$inserinvoiceid=add_record('so_paking_trn',$info, $dbcon);
		if($inserinvoiceid){
			paking_wise_stock_reserve($dbcon,$inserinvoiceid);
			$row['status']=1;
		}else{
			$row['status']=0;
		}
		echo json_encode($row);
	}else if(strtolower($POST['mode'])== "load_pro_table"){
			if($POST['eid']){
				$where= " and so_paking_id=".$POST['eid']." and trn.status=0";
			}else{ 
				$where= " and trn.status=3";
			}
		$query = "select pmst.product_name,unit.unit_name,so.sales_order_no,so_paking_batch_no,so_paking_qty,so_paking_trn_id from so_paking_trn as trn 
			left join tbl_sales_order as so on so.sales_order_id=trn.so_paking_so_id
			left join tbl_sales_ordertrn as sotrn on sotrn.sales_ordertrn_id=trn.so_paking_so_trn_id
			left join product_mst as pmst on pmst.product_id = sotrn.product_id
			left join unit_mst as unit on unit.unitid = sotrn.unit_id
			where so_paking_cust_id=".$POST['cust']." ".$where;
			$result = $dbcon->query($query);
			$cnt = brp_mysqli_num_rows($result);
			$str .= '<table class="table table-bordered">
			<thead>
				<tr>
					<th style="width:30%;">Sales Order No</th>
					<th style="width:30%;">Product Name</th>
					<th style="width:20%;">Batch No</th>
					<th style="width:10%;">Qty</th>
					<th style="width:10%;">Action</th>
				</tr>
			</thead>';
			if($cnt>0){
				$i=1;
				while($row = brp_mysqli_fetch_array($result)){
					$str .= '<tr>
					<td>'.$row['sales_order_no'].'</td>		
					<td>'.$row['product_name'].'</td>		
					<td>'.$row['so_paking_batch_no'].'</td>		
					<td>'.$row['so_paking_qty'].' '.$row['unit_name'].'</td>		
					<td>
						<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_deduct_data('.$row['so_paking_trn_id'].');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
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
			$row['pdata']=$str;
			echo json_encode($row);
	}else if(strtolower($POST['mode'])== "delete_data"){
		$inv_trn['status'] = 2;
		$updatetrnid=update_record('so_paking_trn',$inv_trn,"so_paking_trn_id=".$POST['trn_id'] , $dbcon);

		$qry="select stock_id,reserve_id,base_stock,convert_stock from `tbl_reserve_stock` as so
				where ref_name='paking_trn' and stock_status!=2 and stock_flage=1 and ref_id=".$POST['trn_id'];
		$result=$dbcon->query($qry);
		while($row=brp_mysqli_fetch_assoc($result)){
			$inv_trne['stock_status'] = 2;
			$updatetrniqd=update_record('tbl_reserve_stock',$inv_trne,"reserve_id=".$row['reserve_id'] , $dbcon);
			
			$qryq="select used_base_stock,used_convert_stock,stock_id from `tbl_stock_trn` as so
				where stock_id=".$row['stock_id'];
			$resultq=$dbcon->query($qryq);
			$rowq=brp_mysqli_fetch_assoc($resultq);

			$inv_trnes['used_base_stock'] = $rowq['used_base_stock']-$row['base_stock'];
			$inv_trnes['used_convert_stock'] = $rowq['used_convert_stock']-$row['convert_stock'];
			$updatetrniqds=update_record('tbl_stock_trn',$inv_trnes,"stock_id=".$rowq['stock_id'] , $dbcon);
		}

		if($updatetrnid){
			$row['res']="1";
		}else{
			$row['res']="0";
		}
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "delete_paking_data"){
		$inv_trn['status'] = 2;
		$updatetrnid=update_record('so_paking',$inv_trn,"so_paking_id=".$POST['paking_id'] , $dbcon);
		
		$qrypkp="select so_paking_trn_id from `so_paking_trn` as so
				where status!=2 and so_paking_id=".$POST['paking_id'];
		$resultpkp=$dbcon->query($qrypkp);
		while($rowpkp=brp_mysqli_fetch_assoc($resultpkp)){

			$inv_trnss['status'] = 2;
			$updatetrnid=update_record('so_paking_trn',$inv_trnss,"so_paking_trn_id=".$rowpkp['so_paking_trn_id'] , $dbcon);

			$qry="select stock_id,reserve_id,base_stock,convert_stock from `tbl_reserve_stock` as so
					where ref_name='paking_trn' and stock_status!=2 and stock_flage=1 and ref_id=".$rowpkp['so_paking_trn_id'];
			$result=$dbcon->query($qry);
			while($row=brp_mysqli_fetch_assoc($result)){
				$inv_trne['stock_status'] = 2;
				$updatetrniqd=update_record('tbl_reserve_stock',$inv_trne,"reserve_id=".$row['reserve_id'] , $dbcon);
				
				$qryq="select used_base_stock,used_convert_stock,stock_id from `tbl_stock_trn` as so
					where stock_id=".$row['stock_id'];
				$resultq=$dbcon->query($qryq);
				$rowq=brp_mysqli_fetch_assoc($resultq);

				$inv_trnes['used_base_stock'] = $rowq['used_base_stock']-$row['base_stock'];
				$inv_trnes['used_convert_stock'] = $rowq['used_convert_stock']-$row['convert_stock'];
				$updatetrniqds=update_record('tbl_stock_trn',$inv_trnes,"stock_id=".$rowq['stock_id'] , $dbcon);
			}
		}

		if($updatetrnid){
			$row['res']="1";
		}else{
			$row['res']="0";
		}
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "check_qty_old"){
		$qry="select product_id from `tbl_sales_ordertrn` as so
				where sales_ordertrn_id=".$POST['sales_order_trn_id'];
		$result=$dbcon->query($qry);
		$row=brp_mysqli_fetch_assoc($result);
		//find_batch_stock($dbcon,$product_id,$batch_no);
		$total_stock=0;

		$qry1="select stock_id,base_stock from `tbl_stock_trn` as so
				where stock_status=0 and stock_flage=1 and batch_no='".$POST['batch_no']."' and product_id=".$row['product_id'];
		$result1=$dbcon->query($qry1);
		while($row1=brp_mysqli_fetch_assoc($result1)){
			$total_reserev_stock=0;
			$qry2="select reserve_id,base_stock from `tbl_reserve_stock` as so
				where stock_status=0 and temp_stock_allocate=0 and stock_flage=1 and stock_id=".$row1['stock_id'];
			$result2=$dbcon->query($qry2);
			while($row2=brp_mysqli_fetch_assoc($result2)){

				$qry3="select stock_id,base_stock from `tbl_reserve_stock` as so
				where stock_status=0 and temp_stock_allocate=0 and stock_flage=2 and perent_id=".$row2['reserve_id'];
				$result3=$dbcon->query($qry3);
				$row3=brp_mysqli_fetch_assoc($result3);
				$res_stock=$row2['base_stock']-$row3['base_stock'];
				$total_reserev_stock=$total_reserev_stock+$res_stock;

			}
				$qry4="select IFNULL(sum(base_stock),0) as usedstock from `tbl_stock_trn` as so
				where stock_status=0 and stock_flage=2 and perent_id=".$row1['stock_id'];
				$result4=$dbcon->query($qry4);
				$row4=brp_mysqli_fetch_assoc($result4);
				$tstock=$row1['base_stock']-($row4['usedstock']+$total_reserev_stock);
			$total_stock=$total_stock+$tstock;
		}

		$rowss['stock']=$total_stock;
		echo json_encode($rowss);
	}else if(strtolower($POST['mode'])== "check_qty"){
		
		$qry_p="select product_id from `tbl_stock_trn` as so
				where batch_no='".$POST['batch_no']."'";
		$result_p=$dbcon->query($qry_p);
		$row_p=brp_mysqli_fetch_assoc($result_p);

		$qry_s="select sales_ordertrn_id from `tbl_sales_ordertrn` as so
				where sales_order_id=".$POST['salesorderid']." and product_id=".$row_p['product_id'];
				//var_dump($qry_s);
		$result_s=$dbcon->query($qry_s);
		$row_s=brp_mysqli_fetch_assoc($result_s);
		//var_dump($qry_s);
		$total_stock=0;
		if($row_s['sales_ordertrn_id']){
			$qry1="select stock_id,base_stock from `tbl_stock_trn` as so
					where stock_status=0 and stock_flage=1 and batch_no='".$POST['batch_no']."' and product_id=".$row_p['product_id'];
			$result1=$dbcon->query($qry1);
			while($row1=brp_mysqli_fetch_assoc($result1)){
				$total_reserev_stock=0;
				$qry2="select reserve_id,base_stock from `tbl_reserve_stock` as so
					where stock_status=0 and temp_stock_allocate=0 and stock_flage=1 and stock_id=".$row1['stock_id'];
				$result2=$dbcon->query($qry2);
				while($row2=brp_mysqli_fetch_assoc($result2)){

					$qry3="select stock_id,base_stock from `tbl_reserve_stock` as so
					where stock_status=0 and temp_stock_allocate=0 and stock_flage=2 and perent_id=".$row2['reserve_id'];
					$result3=$dbcon->query($qry3);
					$row3=brp_mysqli_fetch_assoc($result3);
					$res_stock=$row2['base_stock']-$row3['base_stock'];
					$total_reserev_stock=$total_reserev_stock+$res_stock;

				}
					$qry4="select IFNULL(sum(base_stock),0) as usedstock from `tbl_stock_trn` as so
					where stock_status=0 and stock_flage=2 and perent_id=".$row1['stock_id'];
					$result4=$dbcon->query($qry4);
					$row4=brp_mysqli_fetch_assoc($result4);
					$tstock=$row1['base_stock']-($row4['usedstock']+$total_reserev_stock);
				$total_stock=$total_stock+$tstock;
			}
		}

		/*$qry="select product_id from `tbl_sales_ordertrn` as so
				where sales_ordertrn_id=".$POST['sales_order_trn_id'];
		$result=$dbcon->query($qry);
		$row=brp_mysqli_fetch_assoc($result);*/

		//find_batch_stock($dbcon,$product_id,$batch_no);
		

		$rowss['stock']=$total_stock;
		$rowss['sales_ordertrn_id']=$row_s['sales_ordertrn_id'];
		echo json_encode($rowss);
	}

function paking_wise_stock_reserve($dbcon,$paking_trn_id){
	$qrypk="select so_paking_cust_id,so_paking_so_id,so_paking_so_trn_id,so_paking_batch_no,so_paking_qty,sotrn.product_id from `so_paking_trn` as so
					left join tbl_sales_ordertrn as sotrn on sotrn.sales_ordertrn_id=so.so_paking_so_trn_id
					where so_paking_trn_id=".$paking_trn_id;
			$resultpk=$dbcon->query($qrypk);
			$rowpk=brp_mysqli_fetch_assoc($resultpk);
			$so_paking_qty=$rowpk['so_paking_qty'];

	$qry1="select stock_id,base_stock,base_unit,convert_unit,convert_stock,godown_id,branch_id,batch_no,product_id from `tbl_stock_trn` as so
					where stock_status=0 and stock_flage=1 and batch_no='".$rowpk['so_paking_batch_no']."' and product_id=".$rowpk['product_id'];
			$result1=$dbcon->query($qry1);
			while($row1=brp_mysqli_fetch_assoc($result1)){

				$qry2="select reserve_id,base_stock from `tbl_reserve_stock` as so
					where stock_status=0 and temp_stock_allocate=0 and stock_flage=1 and stock_id=".$row1['stock_id'];
				$result2=$dbcon->query($qry2);
				while($row2=brp_mysqli_fetch_assoc($result2)){

					$qry3="select stock_id,base_stock from `tbl_reserve_stock` as so
					where stock_status=0 and temp_stock_allocate=0 and stock_flage=2 and perent_id=".$row2['reserve_id'];
					$result3=$dbcon->query($qry3);
					$row3=brp_mysqli_fetch_assoc($result3);
					$res_stock=$row2['base_stock']-$row3['base_stock'];
					$total_reserev_stock=$total_reserev_stock+$res_stock;

				}
					$qry4="select IFNULL(sum(base_stock),0) as usedstock from `tbl_stock_trn` as so
					where stock_status=0 and stock_flage=2 and perent_id=".$row1['stock_id'];
					$result4=$dbcon->query($qry4);
					$row4=brp_mysqli_fetch_assoc($result4);
					$tstock=$row1['base_stock']-($row4['usedstock']+$total_reserev_stock);

					if($so_paking_qty>=$tstock){
						$usedr=$tstock;
					}else{
						$usedr=$so_paking_qty;
					}
					if($row1['base_unit']==$row1['convert_unit']){
						$base_stock=$usedr;
						$con_stock=$usedr;
                        // echo "== 1 ==";
                       // $type="base_unit";
                        //$con_stock=$used_qty;
                        // $base_stock=convert_stock_new($dbcon,$used_qty,$re['product_id'],$type);
                        //$base_stock=($used_qty/$re1['convert_stock'])*$re1['base_stock'];
                    }else{
                        // echo "== 2 ==";
                        $type="conv_unit";
                        $base_stock=$usedr;
                        // $con_stock=convert_stock_new($dbcon,$used_qty,$re['product_id'],$type);
                        $con_stock=($usedr/$row1['base_stock'])*$row1['convert_stock'];
                    }

					$info_stock['reserve_date']	=date('Y-m-d');
					$info_stock['product_id']	=$row1['product_id'];
					$info_stock['base_unit']	=$row1['base_unit'];
					$info_stock['base_stock']	=$base_stock;
					$info_stock['convert_unit']	=$row1['convert_unit'];
					$info_stock['convert_stock']=$con_stock;
					$info_stock['stock_flage']	=1;
					$info_stock['ref_name']		="paking_trn";
					$info_stock['ref_id']		=$paking_trn_id;
					$info_stock['stock_id']		=$row1['stock_id'];
					$info_stock['godown_id']	=$row1['godown_id'];
					$info_stock['cdate']		=date('Y-m-d H:i:s');
					$info_stock['user_id']		=$_SESSION['user_id'];
					$info_stock['company_id']	=$_SESSION['company_id'];
					$info_stock['branch_id']	=$row1['branch_id'];
					$info_stock['sales_order_trn_id']	=$rowpk['so_paking_so_trn_id'];

					$inserid=add_record('tbl_reserve_stock', $info_stock, $dbcon);	
				//$total_stock=$total_stock+$tstock;
			}
			so_temp_stock_deduct($dbcon,$rowpk['so_paking_qty'],$rowpk['so_paking_so_trn_id'],$paking_trn_id);
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
function so_temp_stock_deduct($dbcon,$so_paking_qty,$so_paking_so_trn_id,$paking_trn_id){
	
	$que1="select * from tbl_reserve_stock as ta where sales_order_trn_id=".$so_paking_so_trn_id." and temp_stock_allocate=1 and stock_flage=1 and stock_status!=2 and base_stock>used_base_stock";
	$rs_di1=$dbcon->query($que1);
	while($re1=brp_mysqli_fetch_assoc($rs_di1)){
		if($so_paking_qty>0){
			$que2="select sum(base_stock) as ubase_stock from tbl_reserve_stock as ta where sales_order_trn_id=".$so_paking_so_trn_id." and temp_stock_allocate=1 and stock_flage=2 and stock_status!=2 and perent_id=".$re1['reserve_id'];
			$rs_di2=$dbcon->query($que2);
			$re1ss=brp_mysqli_fetch_assoc($rs_di2);
			$rpending_stock=$re1['base_stock']-$re1ss['ubase_stock'];
			if($rpending_stock>=$so_paking_qty){
				$useqty=$so_paking_qty;
			}else{
				$useqty=$rpending_stock;
			}
			if($useqty>0){
				$info['reserve_date']		=date('Y-m-d');
				$info['product_id']			=$re1['product_id'];
				$info['godown_id']			=$re1['godown_id'];
				$info['base_unit']			=$re1['base_unit'];
				$info['base_stock']			=$useqty;
				$info['convert_unit']		=$re1['convert_unit'];
				$info['convert_stock']		=$useqty;
				$info['stock_flage']		=2;
				$info['ref_name']			="paking_trn";
				$info['ref_id']				=$paking_trn_id;
				$info['stock_status']		=0;
				$info['cdate']				=date('Y-m-d H:i:s');
				$info['user_id']			=$_SESSION['user_id'];
				$info['company_id']			=$_SESSION['company_id'];
				$info['sales_order_trn_id']	=$re1['sales_order_trn_id'];
				$info['branch_id']			=$re1['branch_id'];
				$info['perent_id']			=$re1['reserve_id'];
				$info['temp_stock_allocate']=$re1['temp_stock_allocate'];
				

				$inserid=add_record('tbl_reserve_stock', $info, $dbcon);
				
				$upd_info_stock['used_base_stock']			= $re1['used_base_stock'] + $useqty;
				$upd_info_stock['used_convert_stock']	= $re1['used_convert_stock'] + $useqty;
				
				$updatetrnid=update_record('tbl_reserve_stock',$upd_info_stock,"reserve_id=".$re1['reserve_id'] , $dbcon);

					$so_paking_qty=$so_paking_qty-$useqty;
			}
			
		}
		
	}
}
?>

