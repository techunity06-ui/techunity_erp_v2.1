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
		
		if(strtolower($POST['mode']) == "fetch") {
		
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where='';
		$where.=" tsr.release_type=1";

		$where.=" and tsr.company_id=".$_SESSION['company_id'];
		switch($POST['release_status']){
			case "0":
			$where.="  and release_status=0";
			break;
			
			case "1":
			$where.="  and release_status=1";
			break;
			
			default:
			$where.="";
		}
					
			$where.="  and tsr.cdate >= '".date('Y-m-d',strtotime($s_date[0]))."' AND tsr.cdate <= '".date('Y-m-d',strtotime($s_date[1]))."'";
			$where .= " and tsr.company_id = " . $_SESSION['company_id'];
			$appData = array();
			$i=1;
			$aColumns = array('release_id','issue_no','issue_date','user_name','branch_name','release_status');
			$sIndexColumn = "release_id";
			$isWhere = array($where);
			$sTable = "tbl_store_release as tsr";
			$isJOIN = array('left join users as u on u.user_id=tsr.to_user_id','left join branch_mst as bms on bms.branch_id=tsr.branch_id');
			$hOrder = "tsr.release_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['issue_no'];
				$row_data[] = date('d M, Y',strtotime($row['issue_date']));
				$row_data[] = $row["user_name"];
				$row_data[] = $row['branch_name'];
				
				$app_btn='';
				
				if($row['release_status']=="1"){
					$app_btn='<button class="btn btn-xs btn-success" data-original-title="Approved Material Release" data-toggle="tooltip" data-placement="top" onclick="change_release_status('.$row['release_id'].',0,\''.$row['issue_no'].'\')"><i class="fa fa-check"></i></button>';
				}
				else{
					$app_btn='<button class="btn btn-xs btn-warning" data-original-title="Approve PO" data-toggle="tooltip" data-placement="top" onclick="change_release_status('.$row['release_id'].',1,\''.$row['issue_no'].'\')"><i class="fa fa-check"></i></button>';
				}
			
				
				if($row['release_status']=='1'){
					$row_data[] = '<button class="btn btn-xs btn-success" >Approved</button>';
				}
				else{
					$row_data[] = '<button class="btn btn-xs btn-warning">Approval Pending</button>';
				}	
				$row_data[] = $app_btn;
			 
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "load_hist_datatable") {

			$where='';
			$where.=" log.store_request_id=".$POST['release_id'];
			$where.=" AND log.release_type=1";
			$where.=" and log.company_id=".$_SESSION['company_id'];

			$appData = array();
			$i=1;
			$aColumns = array('log.store_aprv_log_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.request_user_id');
			$sIndexColumn = "log.store_aprv_log_id";
			$isWhere = array(" ".$where." ");
			$sTable = "tbl_store_request_aprv_log as log";			
			$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
			$hOrder = "log.store_aprv_log_id desc";
			include($include.'/pagging.php');
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
				$row_data[] = date("d-M-Y h:i A",strtotime($row['cdate']));

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}

		else if(strtolower($POST['mode']) == "load_release_dtl") {
			$qt_qry="select tsr.*,user_name,branch_name from  tbl_store_release as tsr
			left join users as u on u.user_id=tsr.to_user_id left join branch_mst as bms on bms.branch_id=tsr.branch_id
			where tsr.release_id=".$POST['release_id'] . " AND tsr.company_id=".$_SESSION['company_id'];
			$qt_rel=brp_mysqli_fetch_assoc($dbcon->query($qt_qry));

		//Party PO Details Table View
			$str='';
			$str.='<div class="form-group">
			<table class="display table table-bordered table-striped">
			<tr>
			<td colspan="2"><strong>Issue No:</strong> '.$qt_rel['issue_no'].'</td>
			<td><strong>Issue_date:</strong> '.$qt_rel['issue_date'].'</td>
			</tr>
			<tr>
			<td colspan="2"><strong>User:</strong> '.$qt_rel['user_name'].'</td>
			<td><strong>Branch:</strong> '.$qt_rel['branch_name'].'</td>
			</tr>
			';
			$str.='</table></div>
			<hr/>
			';

			$qry="select tsr.*,product_name,product_icode,gd_name from  tbl_store_release_trn as tsr
			 left join product_mst as pmst on pmst.product_id=tsr.product_id
			 left join mst_godown as gd on gd.gd_id=tsr.godown_id
			where tsr.release_status = 0 and tsr.release_id=".$POST['release_id'] . " and tsr.company_id = " . $_SESSION['company_id'];

			$result=$dbcon->query($qry);

			$cnt=brp_mysqli_num_rows($result);



			if($cnt > 0){
				$str.='<div class="form-group">
			<table class="display table table-bordered table-striped">';
			$str .='<tr>
				<th>Product</td>
				<th>Godown</td>
				<th>Qty</td>
				<th>Returnable</td>
				</tr>';
			while($rel=brp_mysqli_fetch_assoc($result)){
				$returnable = "No";
				if($rel['returnable'] == '1'){
					$returnable = "Yes";
				}
				$str .='<tr>
				<td>'.$rel['product_name'].' -- ('.$rel['product_icode'].')'.'</td>
				<td>'.$rel['gd_name'].'</td>
				<td>'.$rel['release_qty'].'</td>
				<td>'. $returnable .'</td>
				</tr>';

			
			}

			$str.='</table></div>
			<hr/>
			';
			}
		

			$qt_rel['mod_comp_div_sec'] = $str;

			echo json_encode($qt_rel);
		}
		else if(strtolower($POST['mode']) == "add_apprv_hist") {

			$approve_status = $POST['approve_status'];
			$approve_remark  = $POST['approve_remark'];
			$release_id = $POST['release_id'];

			$qry="select * from  tbl_store_request_aprv_log where release_type = 1 AND store_request_id=".$release_id." and company_id = " . $_SESSION['company_id'];
			$result=$dbcon->query($qry);
			$cnt=brp_mysqli_num_rows($result);

			$res = brp_mysqli_fetch_assoc($result);

			$qry1="select * from tbl_store_release where release_status = 0 AND release_id=".$release_id." and company_id = " . $_SESSION['company_id'];
			$result1=$dbcon->query($qry1);

			$res1 = brp_mysqli_fetch_assoc($result);

			$qry2="select * from tbl_store_release_trn where release_status = 0 AND release_id=".$release_id." and company_id = " . $_SESSION['company_id'];
			$result2=$dbcon->query($qry2);


			$logs['store_release_id'] = $release_id;
			$logs['approve_remark'] = $approve_remark;
			$logs['approve_status'] = $approve_status;
			$logs['release_type'] = 1;
			$logs['request_user_id'] = $res1['to_user_id'];
			$logs['cdate']		= date("Y-m-d H:i:s");
			$logs['user_id']	= $_SESSION['user_id'];
			$logs['company_id']	= $_SESSION['company_id'];
			$logs['branch_id']	= $_SESSION['branch_id'];

			$req_id = add_record('tbl_store_request_aprv_log',$logs, $dbcon);

			$upd_info['release_status'] = $approve_status;
			update_record('tbl_store_release', $upd_info, "release_id=".$release_id, $dbcon);
			while($res2 = brp_mysqli_fetch_assoc($result2)){
				if($cnt > 0){
					if($res['approve_status'] == '1' && $approve_status == '0'){ // stock plus
						stock_plus($dbcon,$res2['product_id'],$res2['release_qty'], $res2['release_conv_qty'], $res2['release_unit'], $res2['release_conv_unit'], $res2['release_conv_qty'],$res2['godown_id'],$res2['release_trn_id']);
					}else if($res['approve_status'] == '0' && $approve_status == '1'){ // stock minus
						stock_minus($dbcon,$res2['product_id'],$res2['release_qty'], $res2['release_conv_qty'], $res2['release_unit'], $res2['release_conv_unit'], $res2['release_conv_qty'],$res2['godown_id'],$res2['release_trn_id']);
					}
				}else{
					if($approve_status == '1'){ // stock minus
						stock_minus($dbcon,$res2['product_id'],$res2['release_qty'], $res2['release_conv_qty'], $res2['release_unit'], $res2['release_conv_unit'], $res2['release_conv_qty'],$res2['godown_id'],$res2['release_trn_id']);
					}
				}
			}
		}

function stock_plus($dbcon,$product_id,$base_qty, $conv_qty,$base_unit,$conv_unit,$godown_id,$release_trn_id){
	$info['stock_date'] = date("Y-m-d H:i:s");
	$info['product_id'] = $product_id;
	$info['base_unit'] = $base_unit;
	$info['base_stock'] = $base_qty;
	$info['convert_unit'] = $conv_unit;	
	$info['convert_stock'] = $conv_qty;
	$info['stock_flage'] = 1;
	$info['godown_id'] = $godown_id;
	$info['ref_name'] = 'tbl_store_release_trn';
	$info['ref_id'] = $release_trn_id;
	$info['cdate'] = date("Y-m-d H:i:s");
	$info['user_id']	= $_SESSION['user_id'];
	$info['company_id']	= $_SESSION['company_id'];
	$info['branch_id']	= $_SESSION['branch_id'];
	$req_id = add_record('tbl_stock_trn',$info, $dbcon);
}

function stock_minus($dbcon,$product_id,$base_qty, $conv_qty,$base_unit,$conv_unit, $godown_id,$release_trn_id){
	
	$qry = "SELECT * FROM `tbl_stock_trn` as tst WHERE tst.stock_flage = 1 and tst.product_id =".$product_id." and tst.godown_id = " . $godown_id . " and tst.company_id=".$_SESSION['company_id'];
	$result=$dbcon->query($qry);
	$cnt=brp_mysqli_num_rows($result);

	if($cnt > 0){
		while($res = brp_mysqli_fetch_assoc($result)){
			$qry1 = "select (select sum(base_stock) from tbl_stock_trn where stock_status = 0 and perent_id  = ".$res['stock_id']." and stock_flage = 2) as used_stock, (select sum(base_stock) from tbl_reserve_stock where stock_status = 0 and stock_id = ".$res['stock_id']." and stock_flage = 1) as res_base_stock,(select sum(base_stock) from tbl_reserve_stock where stock_status = 0 and stock_id = ".$res['stock_id']." and stock_flage = 2) as res_used_stock,(select sum(convert_stock) from tbl_stock_trn where stock_status = 0 and perent_id  = ".$res['stock_id']." and stock_flage = 2) as used_conv_stock, (select sum(convert_stock) from tbl_reserve_stock where stock_status = 0 and stock_id = ".$res['stock_id']." and stock_flage = 1) as res_convert_stock,(select sum(convert_stock) from tbl_reserve_stock where stock_status = 0 and stock_id = ".$res['stock_id']." and stock_flage = 2) as res_used_conv_stock";
				$result1=$dbcon->query($qry1);

				$res1 = brp_mysqli_fetch_assoc($result1);

				$base_stock  =  $res['base_stock'];
				$conv_stock  =  $res['conv_stock'];

				$used_base_stock = $res1['used_stock'];
				$res_base_stock = $res1['res_base_stock'];
				$res_used_stock = $res1['res_used_stock'];

				$used_conv_stock = $res1['used_conv_stock'];
				$res_convert_stock = $res1['res_convert_stock'];
				$res_used_conv_stock = $res1['res_used_conv_stock'];

				$main_stok = ($base_stock - $used_base_stock) - ($res_base_stock - $res_used_stock);
				$main_conv_stok = ($conv_stock - $used_conv_stock) - ($res_convert_stock - $res_used_conv_stock);
		
				if($main_stok >= $base_qty){
					$type="conv_unit";
					$conv_qty=convert_stock($dbcon,$base_qty,$product_id,$type);
					
					$info['base_stock'] =  $main_stok - $base_qty;
					$info['convert_stock'] = $main_conv_stok - $conv_qty;
					
					update_record('tbl_stock_trn', $info, "stock_id=".$res['stock_id'], $dbcon);	
					break;
				}else{
					$base_qty = $base_qty - $main_stok;
					$info['base_stock'] =  $main_stok;
					$info['convert_stock'] = $main_conv_stok;
					update_record('tbl_stock_trn', $info, "stock_id=".$res['stock_id'], $dbcon);	
				}
		}
	}
}

?>