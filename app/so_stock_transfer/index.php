<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    INVENTORY_STOCK_TRANSFER_LIST_SLUG_VIEW,INVENTORY_STOCK_TRANSFER_LIST_SLUG_CREATE,INVENTORY_STOCK_TRANSFER_LIST_SLUG_UPDATE,INVENTORY_STOCK_TRANSFER_LIST_SLUG_DELETE
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
		$where.=" and work_order_transfer_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND work_order_transfer_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$appData = array();
		$i=1;
		$aColumns = array('work_order_transfer_id','work_order_transfer_no', 'work_order_transfer_date','remark');
		$sIndexColumn = "work_order_transfer_id";
		$isWhere = array("work_order_transfer_status = 0".$where.check_user('grn'));
		$sTable = "tbl_work_order_stock_transfer as grn";
		$isJOIN = array();
		$hOrder = "grn.work_order_transfer_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			
			
			$row_data = array();
			$row_data[] = $row['work_order_transfer_no'];
			$row_data[] = date('d M, Y',strtotime($row['work_order_transfer_date']));
			$row_data[] = $row['remark'];
			
   
			$edit_btn=''; $delete_btn=''; $view='';
			if(in_array(INVENTORY_STOCK_TRANSFER_LIST_SLUG_UPDATE,$bulkAccessArray)){
				$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'stock_transfer_edit/'.$row['work_order_transfer_id'].'"><i class="fa fa-pencil"></i></a>'; 
				
			}
			if(in_array(INVENTORY_STOCK_TRANSFER_LIST_SLUG_DELETE,$bulkAccessArray)){
				if(!empty($re12['grn_id'])){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_grn('.$row['work_order_transfer_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				}
			}  
			/* $view='<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.'grn_view/'.$row['grn_id'].'"><i class="fa fa-eye"></i></a> ';
			 */
			$row_data[] = $edit_btn.' '.$delete_btn.' '.$view;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		//$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id='8'");
		
			$info['work_order_transfer_no']				= $POST['transfer_no'];
			$info['work_order_transfer_date']			= date('Y-m-d',strtotime($POST['transfer_date']));
			$info['remark']				= $_POST['remark'];
			//$info['ref_no']				= $_POST['request_no'];
			
			$info['cdate']				= date("Y-m-d H:i:s"); 
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			$inserpoid=add_record('tbl_work_order_stock_transfer', $info, $dbcon);
			
		$info1['work_order_stock_transfer_trn_status']		= 0; 
		$info1['work_order_transfer_id']		= $inserpoid; 
		
		$updateid=update_record('tbl_work_order_stock_transfer_trn', $info1,"work_order_stock_transfer_trn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
		
		$set="select * from tbl_work_order_stock_transfer_trn where  work_order_stock_transfer_trn_status=0 and work_order_transfer_id=".$inserpoid;
		$ds=$dbcon->query($set);
		while($set_head=mysqli_fetch_assoc($ds)){
			
			$set1="select * from tbl_reserve_stock where stock_status!=2 and stock_flage=1 and request_id=".$set_head['request_id'];
			$ds1=$dbcon->query($set1);
			
			$transfer_qty=$set_head['transfer_qty'];
			$tqty=0;
			while($set_r=mysqli_fetch_assoc($ds1)){
				$set12="select IFNULL(sum(base_stock),0) as used_stock from tbl_reserve_stock where stock_status!=2 and stock_flage=2 and convert_unit=".$set_r['base_unit']." or base_unit=".$set_r['base_unit']." and ref_id=".$set_r['reserve_id'];
				$ds12=$dbcon->query($set12);
				$set_r2=mysqli_fetch_assoc($ds12);
				$pending_qty=$set_r['base_stock']-$set_r2['used_stock'];
				if($pending_qty>=$transfer_qty){
					$query_invoicetype = $dbcon->query("UPDATE tbl_reserve_stock SET convert_stock = convert_stock -".$set_head['transfer_qty'].",base_stock=base_stock -".$transfer_qty."  WHERE reserve_id=".$set_r['reserve_id']);
					//$tqty=$tqty+$transfer_qty;
					add_request_reserve_stock($dbcon,$set_head['transfer_request_id'],$transfer_qty,$set_r['base_unit']);
					$transfer_qty=$transfer_qty-$transfer_qty;
				}else{
					$query_invoicetype = $dbcon->query("UPDATE tbl_reserve_stock SET convert_stock = convert_stock -".$set_head['transfer_qty'].",base_stock=base_stock -".$pending_qty."  WHERE reserve_id=".$set_r['reserve_id']);
					//$tqty=$tqty+$pending_qty;
					add_request_reserve_stock($dbcon,$set_head['transfer_request_id'],$pending_qty,$set_r['base_unit']);
					$transfer_qty=$transfer_qty-$pending_qty;
				}
			}
		}
		
		if($inserpoid){	
			$arr['msg']="1";	
		}
		else{
			$arr['msg']="0";
		}
		$arr['back']=$POST['back'];
		
		echo json_encode($arr);	
	}
	else if(strtolower($POST['mode']) == "edit") {

		$info['work_order_transfer_no']				= $POST['transfer_no'];
		$info['work_order_transfer_date']			= date('Y-m-d',strtotime($POST['transfer_date']));
		$info['remark']				= $_POST['remark'];
			
		$info['cdate']			= date("Y-m-d H:i:s"); 
		$info['user_id']		= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id']; 
		$updateid=update_record('tbl_work_order_stock_transfer', $info,"work_order_transfer_id=".$POST['eid'] , $dbcon);
		 
		
		
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
		
		$info1['product_id']			= $POST['product_id'];
		$info1['request_id']			= $POST['work_order_id'];
		$info1['transfer_request_id']	= $POST['transfer_work_order_id'];
		$info1['transfer_qty']			= $POST['transfer_qty']; 
		$info1['user_id']				= $_SESSION['user_id'];
		$info1['company_id']			= $_SESSION['company_id'];
	
		$table='tbl_work_order_stock_transfer_trn';$tableid='work_order_transfer_trn_id';
		if(!empty($POST['work_order_transfer_id'])) {
			$info1['work_order_transfer_id']= $POST['work_order_transfer_id'];
		}
		else{
			$info1['work_order_stock_transfer_trn_status']= 3;
		}
		
		if(empty($POST['edit_id'])) {
			//var_dump($info1);
			$inserid=add_record($table, $info1, $dbcon);
		}
		else {
			$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
		}
		
		//var_dump($info1);
	}
	else if(strtolower($POST['mode'])== "load_stock_transfer_trn_data") {
		
		if($POST['work_order_transfer_id']){
			$query="select mst.*,pro.product_name,set_p.po_req_no as transfer_work_order_no,setq.po_req_no as work_order_no from tbl_work_order_stock_transfer_trn as mst
			left join product_mst as pro on pro.product_id=mst.product_id
			left join tbl_request_product as req_p on req_p.rp_id=mst.transfer_request_id
			left join tbl_set_main_process as set_p on set_p.sp_id=req_p.sp_id
			left join tbl_request_product as req on req.rp_id=mst.request_id
			left join tbl_set_main_process as setq on setq.sp_id=req.sp_id
			where work_order_stock_transfer_trn_status=0 and mst.work_order_transfer_id=".$POST['work_order_transfer_id'];
		}
		else{
			$query="select mst.*,pro.product_name,set_p.po_req_no as transfer_work_order_no,setq.po_req_no as work_order_no from tbl_work_order_stock_transfer_trn as mst
			left join product_mst as pro on pro.product_id=mst.product_id
			left join tbl_request_product as req_p on req_p.rp_id=mst.transfer_request_id
			left join tbl_set_main_process as set_p on set_p.sp_id=req_p.sp_id
			left join tbl_request_product as req on req.rp_id=mst.request_id
			left join tbl_set_main_process as setq on setq.sp_id=req.sp_id
			where work_order_stock_transfer_trn_status=3 and mst.user_id=".$_SESSION['user_id'];
		}
		$result=$dbcon->query($query);
		echo ' <div class="form-group">
					  <div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th class="text-center grn" width="10%">Sr No</th>
							<th class="text-center" width="20%">Product Name</th>
							<th class="text-center" width="20%">Work Order</th>
							<th class="text-center" width="20%">Transfer Work Order</th>
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
					<td style="vertical-align:top;" class="text-center">
						'.$rel['work_order_no'].'
					</td>
					<td style="vertical-align:top;" class="text-center">
						'.$rel['transfer_work_order_no'].'
					</td>
					<td style="vertical-align:top;" class="text-center">
						'.$rel['transfer_qty'].'
					</td>
					<td style="vertical-align:top"> 
						<!--<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_grn_data('.$rel['work_order_transfer_trn_id'].')"><i class="fa fa-pencil"></i></button>-->
						<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_grn_data('.$rel['work_order_transfer_trn_id'].')">X</button>
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
		$q = $dbcon -> query("SELECT mst.* FROM tbl_work_order_stock_transfer_trn as mst WHERE work_order_transfer_trn_id = '$POST[work_order_transfer_trn_id]'");
		$r = $q->fetch_assoc();
		
		echo json_encode($r);
	}
	else if(strtolower($POST['mode'])== "delete_data") {
		$row=array();
		$info['work_order_stock_transfer_trn_status']=2;	
		$updateid=update_record('tbl_work_order_stock_transfer_trn', $info, "work_order_transfer_trn_id=".$POST['work_order_transfer_trn_id'] , $dbcon);
		
		//$UPD_PO=upd_grn_used_status($dbcon, $POST['purchaseorder_id'], 2);
		
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "delete_grn") {
		$row=array();
		$info['grn_status']=2;
		$updateid=update_record('tbl_grn', $info, "grn_id=".$POST['grn_id'] , $dbcon);
		
		$upd_po_sts=upd_grn_used_status($dbcon, $POST['purchaseorder_id'], 2);
		
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"grn_add",3,"tbl_grn",$POST['grn_id']);	
		
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
			$resp['pro_html'] = get_po_details_for_grn_trn($dbcon,$id,$grn_type,$POST['mode1'],$POST['eid'],$POST['vender_id']);
			$resp['request_id'] ='';
		}
		else
		{
			$resp['pro_html'] = get_jobwork_details_for_grn_trn($dbcon,$id,'',$POST['mode1'],$POST['eid'],$POST['vender_id']);
			$resp['request_id'] = get_request_id_jobwork($dbcon,$id);
		}
		
		$vendor_id=get_vender_id($dbcon,$id,$grn_type);
		$resp['vendor_id'] = $vendor_id;
		$resp['vendor_name'] = get_vender_name($dbcon,$vendor_id,$grn_type);
		
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode'])== "load_po_ven_wise") {
		$resp['pro_html'] = get_po_for_grn($dbcon,'',$POST['vender_id'],'Add');
		echo json_encode($resp);
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
	else if(strtolower($POST['mode'])== "load_grn_no") {
		$row=array();
		$query1="select * from tbl_invoicetype where type_id='8'";
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
	else if(strtolower($POST['mode'])== "delete_attch") {
		$row=array();
		$info['grn_attch_status']=2;	
		$updateid=update_record('tbl_grn_attch', $info, "grn_attch_id=".$POST['grn_attch_id'] , $dbcon);
		 
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "get_order_no") {
		//tbl_request_product
		//tbl_set_main_process
		
		$get_dt_qry="SELECT set_main.po_req_no,res_pro.rp_id FROM `tbl_set_main_process` as set_main
		left join tbl_request_product as res_pro on res_pro.sp_id=set_main.sp_id
		where rp_pid=".$POST['product_id']."";
		$get_dt_rs=$dbcon->query($get_dt_qry);
		$str='<option  value="">--Select Work Order--</option>';
		while($get_dt_rel=mysqli_fetch_assoc($get_dt_rs)){
			$str .= '<option value="'.$get_dt_rel['rp_id'].'">'.$get_dt_rel['po_req_no'].'</option>';
		}
		echo $str; 
	}
	else if(strtolower($POST['mode'])== "get_reserve_stock") {
		
		 $get_dt_qry="SELECT * FROM `tbl_request_product` as set_main
		where rp_id=".$POST['work_order_id']." ";
		$get_dt_rs=$dbcon->query($get_dt_qry);
		$get_dt_rel=mysqli_fetch_assoc($get_dt_rs);
		
		echo reserve_stock($dbcon,$get_dt_rel['rp_pid'],$get_dt_rel['purchase_unit'],"",$get_dt_rel['rp_id']);
		//var_dump($get_dt_rel['rp_pid']);
		//var_dump($get_dt_rel['process_unit']);
		//echo $str; 
	}
	else if(strtolower($POST['mode'])== "get_pending_reserve_stock") {
		echo pending_reserve_qty($dbcon,$POST['work_order_id']);
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
?>