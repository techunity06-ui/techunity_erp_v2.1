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


$bulkAccessArray = canCheckPermissionAccess($dbcon, [
       INVENTORY_STORE_ACCEPTED_LIST_SLUG_VIEW,INVENTORY_STORE_ACCEPTED_LIST_SLUG_DELETE
   ]);

$company_config = getCompanyConfiguration($dbcon);	

if(strtolower($POST['mode']) == "fetch") {

	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];

	$appData = array();
	$i=1;
	$aColumns = array('sa.store_accept_no','batch.batch_no','sa.store_accept_date','p.product_name','pr.process_name','usr.user_name','p.product_icode','grn.grn_no','gda.gd_name','umst.unit_name','cmst.unit_name as conv_unit_name','trn.qty','trn.unit_id','trn.godown_id','trn.grn_trn_id','trn.store_accept_trn_id','trn.product_id','sa.store_accept_id','trn.batch_id','p.product_base_unit','p.product_conv_unit','batch.base_qty','batch.conv_qty');
	$sIndexColumn = "trn.store_accept_trn_id";
	$isWhere = array(" trn.store_accept_trn_status = 0 and trn.company_id=".$_SESSION['company_id']);
	$sTable = "tbl_store_accept_trn as trn";
	
	$isJOIN = array('left join tbl_store_accept as sa on sa.store_accept_id=trn.store_accept_id',
					'left join tbl_grn_trn as sr on sr.grn_trn_id=trn.grn_trn_id',
					'left join product_mst as p on p.product_id=trn.product_id',
					'left join tbl_grn as grn on grn.grn_id=sr.grn_id',
					'left join unit_mst as umst on umst.unitid=p.product_base_unit',
					'left join unit_mst as cmst on cmst.unitid=p.product_conv_unit',
					'left join mst_godown as gda on gda.gd_id=trn.godown_id',
					'left join tbl_batch_data as batch on batch.batch_id = trn.batch_id',
					'left join process_mst as pr on pr.process_id=batch.process_id',
					'left join users as usr on usr.user_id = trn.user_id');
	$hOrder = "trn.store_accept_trn_id desc";
	include($include.'pagging.php');
	$appData = array();
	$id=1;
		
	foreach($sqlReturn as $row) {
		$row_data = array();

		$base_qty = 0;
		$conv_qty = 0;

		if($row['product_base_unit'] == $row['product_conv_unit']){
			$base_qty = $row['qty'];
			$conv_qty = $row['qty'];
		}else{
			if($row['product_conv_unit'] == $row['unit_id']){
				$conv_qty = $row['qty'];
				$base_qty = ($row['qty']/$row['conv_qty']) * $row['base_qty'];
				// $base_qty=convert_stock($dbcon,$row['qty'],$row['product_id'],"base_unit");
			}else{
				$base_qty = $row['qty'];
				$conv_qty= ($row['qty']/$row['base_qty']) * $row['conv_qty'];
				// $conv_qty=convert_stock($dbcon,$row['qty'],$row['product_id'],"conv_unit");
			}
		}
 		
		$row_data[] = $row['sr'];
		$row_data[] = $row['store_accept_no'];
		$row_data[] = date('d M, Y',strtotime($row['store_accept_date']));	
		$row_data[] = $row['batch_no'];
		$row_data[] = $row['grn_no'];
		$row_data[] = $row['product_name'] . " -- (".$row['product_icode'].")";
		$row_data[] = $row['process_name'];
		$row_data[] = $base_qty.' '.$row['unit_name'];
		$row_data[] = $conv_qty.' '.$row['conv_unit_name'];
		$row_data[] = $row['gd_name'];
		$row_data[] = $row['user_name'];

		$btn_delete = "";

		if(in_array(INVENTORY_STORE_ACCEPTED_LIST_SLUG_DELETE,$bulkAccessArray)){

		$qry = "SELECT st.stock_id,st.base_stock,IFNULL(st.used_base_stock,0) as used_base_stock,st.convert_stock,IFNULL(st.used_convert_stock,0) as used_convert_stock,IFNULL(used_stock,0) as used_stock FROM tbl_stock_trn as st
		left join (select IFNULL(SUM(req.base_stock),0) as used_stock,req.perent_id from tbl_stock_trn as req where req.stock_flage=2 AND stock_status != 2 ) as qc on qc.perent_id=st.stock_id
		 WHERE st.stock_flage = 1 AND st.stock_status != 2 AND st.product_id = ". $row['product_id']." AND st.batch_id = " .$row['batch_id'];
// echo "</br></br>";
		 $result = $dbcon->query($qry);
		 $cnt = brp_mysqli_num_rows($result);

		 if($cnt > 0){
		 	$st_row = brp_mysqli_fetch_assoc($result);

		 	if($st_row['used_base_stock'] == '' || $st_row['used_base_stock'] == '0'){
		 		$btn_delete  = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_data('.$row['store_accept_trn_id'].')"><i class="fa fa-trash-o"></i></button>';
		 	}
		 }
		}


		$row_data[] = $btn_delete;

		$appData[] = $row_data;
		$id++;
			
	}
	
	$output['aaData'] = $appData;
	echo json_encode( $output );
}

else if(strtolower($POST['mode'])== "delete_data")
{
	$store_accept_trn_id = $POST['store_accept_trn_id'];
	
	$q1 = "SELECT * FROM tbl_store_accept_trn WHERE store_accept_trn_id = " . $store_accept_trn_id;
	$row = brp_mysqli_fetch_assoc($dbcon->query($q1));

	$qry = "SELECT st.stock_id from tbl_stock_trn as st WHERE st.stock_flage = 1 AND st.stock_status != 2 AND st.product_id = ". $row['product_id']." AND st.batch_id = " .$row['batch_id'];
	$result = $dbcon->query($qry);
	while($row1 = brp_mysqli_fetch_assoc($result)){
		$stock_info['stock_status'] = 2;
		$u_id=update_record('tbl_stock_trn', $stock_info,"stock_id=".$row1['stock_id'], $dbcon);

		$rs_q1 = "SELECT reserve_id FROM tbl_reserve_stock WHERE stock_status !=2 AND stock_id = " . $row1['stock_id'];

		$result_rs_q1 = $dbcon->query($rs_q1);

		while($row1_rs_q1 = brp_mysqli_fetch_assoc($result_rs_q1)){
			$rs_stock_info['stock_status'] = 2;
			$u_id=update_record('tbl_reserve_stock', $rs_stock_info,"reserve_id=".$row1_rs_q1['reserve_id'], $dbcon);
		}
	}

	
	$batch_info['stock_approval_status'] = 0;
	$u_id1=update_record('tbl_batch_data', $batch_info,"batch_id=".$row['batch_id'], $dbcon);

	$grn_info['store_accept'] = 0;
	$u_id2=update_record('tbl_grn_trn', $grn_info,"grn_trn_id=".$row['grn_trn_id'], $dbcon);


	$info['store_accept_trn_status'] = 2;
	$update_id=update_record('tbl_store_accept_trn', $info,"store_accept_trn_id=".$store_accept_trn_id, $dbcon);

	if($update_id){
		$row['res']="1";
	}
	else{
		$row['res']="0";
	}
	echo json_encode($row);
}

?>