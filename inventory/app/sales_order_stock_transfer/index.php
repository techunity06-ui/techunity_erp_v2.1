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
	$aColumns = array('retun.so_stock_transfer_id','main_so.sales_order_no as main_so_no','trans_so.sales_order_no as transfer_so_no', 'retun.transfer_qty','retun.stock_transfer_no','retun.stock_transfer_date','pro.product_name');
	$sIndexColumn = "retun.so_stock_transfer_id";
	$isWhere = array("retun.so_stock_transfer_status = 0".$where.check_company('retun'));
	$sTable = "tbl_so_stock_transfer as retun";
	$isJOIN = array('left join product_mst as pro on pro.product_id=retun.product_id','left join tbl_sales_ordertrn as main_so_trn on main_so_trn.sales_ordertrn_id=retun.main_so_trn_id','left join tbl_sales_order as main_so on main_so.sales_order_id=main_so_trn.sales_order_id','left join tbl_sales_ordertrn as transfer_so_trn on transfer_so_trn.sales_ordertrn_id=retun.transfer_so_trn_id','left join tbl_sales_order as trans_so on transfer_so_trn.sales_order_id=trans_so.sales_order_id');
	$hOrder = "retun.so_stock_transfer_id desc";
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['stock_transfer_no'];
		$row_data[] = $row['stock_transfer_date'];
		$row_data[] = $row['product_name'];
		$row_data[] = $row['main_so_no'];
		$row_data[] = $row['transfer_so_no'];
		$row_data[] = $row['transfer_qty'];
		$edit_btn=''; $delete_btn='';$grn_done='';

		
		if(in_array(INVENTORY_RETURNABLE_CHANNAL_SLUG_APPROVE,$bulkAccessArray)){
			if($row['cntgrn'] > 0 ){
				$approv_btn = '';
			}else{
				$approv_btn=' <button class="btn btn-xs btn-success" data-original-title="RR Approve" data-toggle="tooltip" data-placement="top" onClick="approve_stock_transfer('.$row['so_stock_transfer_id'].')"><i class="fa fa-check"></i></button>';
			}
		}

		
		
		$row_data[] = $edit_btn.' '.$delete_btn.' '.$approv_btn.' '.$challan_print.' '.$grn_done;
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "get_sales_order") {
	$product = "select sales_order_no,sales_ordertrn_id from tbl_sales_ordertrn as trn 
	left join tbl_sales_order as so on so.sales_order_id=trn.sales_order_id
	where trn.product_id=".$POST['product_id']." and trn.sales_ordertrn_status=0 and so.company_id=".$_SESSION['company_id']." and trn.invoice_status=0 group by trn.sales_ordertrn_id" ;
	$pro_e=$dbcon->query($product);  
	$str='<option >--- Select Sales Order ---</option>';
	while($pro_r=mysqli_fetch_array($pro_e)){
		$str .= '<option '.$sel.' value="'.$pro_r['sales_ordertrn_id'].'">'.$pro_r['sales_order_no'].'</option>';
	}

	$product_1 = "select so.sales_order_no,trn.sales_ordertrn_id,trn.unit_id,trn.product_id,trn.branch_id,(trn.remaning_invoice_qty-IFNULL(sum(pro_pla.product_qty),0)) as pending_fd from tbl_sales_ordertrn as trn left join tbl_sales_order as so on so.sales_order_id=trn.sales_order_id left join tbl_sales_order_production_trn as pro_pla on pro_pla.sales_ordertrn_id=trn.sales_ordertrn_id and sales_order_production_status=0 where trn.product_id=".$POST['product_id']." and trn.sales_ordertrn_status=0 and trn.invoice_status=0 and so.company_id=".$_SESSION['company_id']." group by trn.sales_ordertrn_id HAVING pending_fd > 0" ;
	$str1='<option >--- Select Sales Order ---</option>';
	$pro_e_1=$dbcon->query($product_1);  
	while($pro_r_1=mysqli_fetch_array($pro_e_1)){

		$res_stock=reserve_stock_data($dbcon,$pro_r_1['product_id'],$pro_r_1['unit_id'],$reserve_id,$request_id,$complaint_id,$pro_r_1['sales_ordertrn_id'],$pro_r_1['branch_id'],$is_store_approval,$godown_id);
		
		if($res_stock>0){
			$str1 .= '<option '.$sel.' value="'.$pro_r_1['sales_ordertrn_id'].'">'.$pro_r_1['sales_order_no'].'</option>';
		}
		
	}

	$resp['pro_html'] = $str;
	$resp['mainso'] = $str1;
	echo json_encode($resp);
}
else if(strtolower($POST['mode']) == "load_main_qty") {
	
	$product = "select so.sales_order_no,trn.sales_ordertrn_id,trn.product_id,trn.unit_id,trn.branch_id,(trn.remaning_invoice_qty-IFNULL(sum(pro_pla.product_qty),0)) as pending_fd from tbl_sales_ordertrn as trn left join tbl_sales_order as so on so.sales_order_id=trn.sales_order_id left join tbl_sales_order_production_trn as pro_pla on pro_pla.sales_ordertrn_id=trn.sales_ordertrn_id and sales_order_production_status=0 where trn.sales_ordertrn_id=".$POST['main_sales_order']." and trn.sales_ordertrn_status=0 and trn.invoice_status=0 and so.company_id=".$_SESSION['company_id']." group by trn.sales_ordertrn_id HAVING pending_fd > 0" ;
	
	$pro_e=$dbcon->query($product);  
	$pro_r=mysqli_fetch_array($pro_e);
	//$resp['main_qty'] = $pro_r['pending_fd'];

	$res_stock=reserve_stock_data($dbcon,$pro_r['product_id'],$pro_r['unit_id'],$reserve_id,$request_id,$complaint_id,$pro_r['sales_ordertrn_id'],$pro_r['branch_id'],$is_store_approval,$godown_id);
	$resp['main_qty'] = $res_stock;
	echo json_encode($resp);
}
else if(strtolower($POST['mode']) == "load_transfer_qty") {
	
	$product = "select remaning_invoice_qty from tbl_sales_ordertrn as trn 
	left join tbl_sales_order as so on so.sales_order_id=trn.sales_order_id
	where trn.sales_ordertrn_id=".$POST['transfer_sales_order']." and trn.sales_ordertrn_status=0 and so.company_id=".$_SESSION['company_id']." and trn.invoice_status=0 group by trn.sales_ordertrn_id" ;
	
	$pro_e=$dbcon->query($product);  
	$pro_r=mysqli_fetch_array($pro_e);
	$resp['trans_qty'] = $pro_r['remaning_invoice_qty'];
	echo json_encode($resp);
}
else if(strtolower($POST['mode']) == "add") {


	$info['stock_transfer_no']		= load_common_no($dbcon,17);
	$info['stock_transfer_date']		= date('Y-m-d',strtotime($POST['so_transfer_date']));
	$info['product_id']			= $POST['product_id'];
	$info['main_so_trn_id']			= $POST['main_sales_order'];
	$info['main_qty']				= $POST['main_qty'];
	$info['transfer_so_trn_id']		= $POST['transfer_sales_order'];
	$info['transfer_qty']			= $POST['transfer_qty'];
	$info['remark']				= $_POST['remark'];
	$info['cdate']				= date("Y-m-d H:i:s"); 
	$info['user_id']				= $_SESSION['user_id'];
	$info['company_id']			= $_SESSION['company_id'];
	$inserpoid=add_record('tbl_so_stock_transfer', $info, $dbcon);
	//var_dump($info);

	//update series no start
	update_common_no($dbcon,17);
	//update series no stop

	if($inserpoid){	
		$arr['msg']="1";	
	}
	else{
		$arr['msg']="0";
	}
	$arr['back']=$POST['back'];

	echo json_encode($arr);	
}
else if(strtolower($POST['mode']) == "approve_stock_transfer") {
	$product = "select * from tbl_so_stock_transfer as trn 
	where trn.so_stock_transfer_id=".$POST['stock_transfer_id']." and trn.so_stock_transfer_status=0 " ;
	$pro_e=$dbcon->query($product);  
	$pro_r=mysqli_fetch_array($pro_e);
	$transfer_qty=$pro_r['transfer_qty'];
	$query= "select * from tbl_reserve_stock as trn 
	where trn.sales_order_trn_id=".$pro_r['main_so_trn_id']." and trn.stock_status=0 and trn.stock_flage=1 " ;
	$result=$dbcon->query($query);  
	while($res=mysqli_fetch_array($result)){
		$usedqty=0;
		if($transfer_qty>0){
			$query_d= "select sum(trn.base_stock) as used_stock from tbl_reserve_stock as trn 
			where  trn.stock_status=0 and trn.stock_flage=2 and ref_id=".$res['reserve_id'] ;
			$result_d=$dbcon->query($query_d);  
			$res_d=mysqli_fetch_array($result_d);
			if($res['base_stock']>$res_d['used_stock']){
				$pending_stock=$res['base_stock']-$res_d['used_stock'];
				if($transfer_qty>=$pending_stock){
					$usedqty=$pending_stock;
				}else{
					$usedqty=$transfer_qty;
				}
				$transfer_qty=$transfer_qty-$usedqty;
				$usedqtynew=$usedqty;
				$query_p= "select * from tbl_sales_order_production_trn as trn 
				where  trn.sales_order_production_status=0 and sales_ordertrn_id=".$pro_r['transfer_so_trn_id'];
				$result_p=$dbcon->query($query_p);  
				while($res_p=mysqli_fetch_array($result_p)){
					$ppendingqty=$res_p['product_qty']-$res_p['allocate_qty'];
					if($ppendingqty>=$usedqtynew){
						$resqt=$usedqtynew;
					}else{
						$resqt=$ppendingqty;
					}
					$usedqtynew=$usedqtynew-$resqt;
					if($resqt>0){
						add_so_reserve_stock($dbcon,$resqt,$res['base_unit'],$res['product_id'],$pro_r['transfer_so_trn_id'],$res['godown_id'],$res_p['sales_order_production_trn_id'],$res['branch_id'],$res['stock_id']);
					}
				}

				if($usedqtynew>0){

					$info_e['sales_ordertrn_id']		=$pro_r['transfer_so_trn_id'];
					$info_e['product_id']			=$res['product_id'];
					$info_e['product_qty']			=$usedqtynew;
					$info_e['godown_id']			=$res['godown_id'];
					$info_e['unit_id']			=$res['base_unit'];
					$info_e['allocate_qty']			=$usedqtynew;
					$info_e['remaning_invoice_qty']	=$usedqtynew;

					$info_e['cdate']				=date("Y-m-d");
					$info_e['company_id']			=$_SESSION['company_id'];
					$info_e['user_id']			=$_SESSION['user_id'];
					$inserinvoiceidexp=add_record('tbl_sales_order_production_trn', $info_e, $dbcon,$res['branch_id']);

					add_so_reserve_stock($dbcon,$usedqtynew,$res['base_unit'],$res['product_id'],$pro_r['transfer_so_trn_id'],$res['godown_id'],$inserinvoiceidexp,$res['branch_id'],$res['stock_id']);
				}

				
				$info['base_stock']=$res['base_stock']-$usedqty;
				$info['base_stock']=$res['convert_stock']-$usedqty;	
				$updateid=update_record('tbl_reserve_stock', $info, "reserve_id=".$res['reserve_id'] , $dbcon);
				$query_pe= "select * from tbl_sales_order_production_trn as trn 
				where  trn.sales_order_production_status=0 and sales_ordertrn_id=".$pro_r['main_so_trn_id'];
				$perentminus=$usedqty;
				$result_pw=$dbcon->query($query_pe);  
				while($res_pw=mysqli_fetch_array($result_pw)){
					if($res_pw['product_qty']>=$perentminus){
						$spqty=$perentminus;
					}else{
						$spqty=$res_pw['product_qty'];
					}
					$perentminus=$perentminus-$spqty;

					$infosp['product_qty']=$res_pw['product_qty']-$spqty;
					$infosp['allocate_qty']=$res_pw['allocate_qty']-$spqty;	
					$updateid1=update_record('tbl_sales_order_production_trn', $infosp, "sales_order_production_trn_id=".$res_pw['sales_order_production_trn_id'] , $dbcon);

					$infospd['production_status']=0;	
					$updateid12=update_record('tbl_sales_ordertrn', $infospd, "sales_ordertrn_id=".$res_pw['sales_ordertrn_id'] , $dbcon);
					
				}
			}
		}
		
	}

	
}
?>