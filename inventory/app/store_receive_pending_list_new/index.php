<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
// error_reporting(E_ALL);
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

$company_config = getCompanyConfiguration($dbcon);	

if(strtolower($POST['mode']) == "fetch") {

	// $s_date=explode(' - ',$POST['date']);
	// $_SESSION['start']=$s_date[0];
	// $_SESSION['end']=$s_date[1];

	$where='';
	$where.=" batch.status = 0 and  batch.qc_status = 1 and  batch.accept_qty > 0 and   batch.stock_approval_status = 0 and batch.reprocess_qc = 0 and batch.company_id=".$_SESSION['company_id'];

	$appData = array();
	$i=1;
	$aColumns = array('sr.grn_trn_id','batch.order_no','p.product_name','p.product_icode','grn.grn_date','batch.batch_no','grn.grn_no','gda.gd_name','batch.product_id','umst.unit_name','batch.batch_id','batch.process_id','batch.batch_qty','batch.accept_qty','batch.reprocess_qc','batch.batch_unit','batch.to_godown_id','qc.qc_no','qc.qc_date','batch.grn_trn_id','batch.grn_id','batch.qc_id');
	$sIndexColumn = "batch.batch_id";
	$isWhere = array($where);
	$sTable = "tbl_batch_data as batch";
	
	$isJOIN = array('left join tbl_grn_trn as sr on sr.grn_trn_id=batch.grn_trn_id','left join product_mst as p on p.product_id=batch.product_id','left join tbl_grn as grn on grn.grn_id=sr.grn_id','left join unit_mst as umst on umst.unitid=batch.batch_unit','left join mst_godown as gda on gda.gd_id=sr.grn_godown','left join tbl_qc_reject_new_product as rej_qc on rej_qc.batch_id = batch.batch_id and rej_qc.qc_id = batch.qc_id and rej_qc.product_id = batch.product_id','left join tbl_qc as qc on qc.qc_id = batch.qc_id');
	$hOrder = "batch.batch_id desc";
	$having_clause = "";
	include($include.'pagging.php');
	$appData = array();
	$id=1;
			//echo "<pre>"; print_r($sqlReturn);
	foreach($sqlReturn as $row) {
		$row_data = array();

 		$query_q="select qctrn.qc_accepted,qc.qc_id from tbl_qc as qc
			left join tbl_qc_trn as qctrn on qctrn.qc_id=qc.qc_id
			where qctrn.qc_status=0 and qctrn.qc_status=0 and qc.grn_trn_id=".$row['grn_trn_id'];
		$result_q=$dbcon->query($query_q);
				$rel_q=brp_mysqli_fetch_assoc($result_q);

			// if(!empty($rel_q['qc_id'])){
			// 	$total_qty=$rel_q['qc_accepted'];
			// }else{
				// $total_qty=$row['product_qty'];
				$total_qty=$row['accept_qty'];
			// }
			$row_data[] = $row['order_no'];
		if($row['grn_trn_id'] == '0' && $row['grn_trn_id'] == '0'){
			$row_data[] = date('d M, Y',strtotime($row['qc_date']));
		}else{
			// $row_data[] = $row['grn_no'];
			$row_data[] = date('d M, Y',strtotime($row['grn_date']));	
		}
		
		if($company_config['batch_wise_stock'] == '1'){
			$row_data[] = $row['batch_no'];
		}
		$row_data[] = $row['product_name'] . " -- (".$row['product_icode'].")";
		$row_data[] = $total_qty.' '.$row['unit_name'];
		$app_btn='';	
		$unit_name=$row['unit_name'];	
		$grn_trn_id=$row['grn_trn_id'];
		$unit_id=$row['batch_unit'];
		$gd_name=$row['gd_name'];
		$product_name=$row['product_name'];
		$grndate=date('d-m-Y',strtotime($row['grn_date']));
		$grn_no=$row['grn_no'];
		$product_id=$row['product_id'];
		$batch_id = $row['batch_id'];
		$batch_no = $row['batch_no'];

		$reprocess_qc = $row['reprocess_qc'];


		$product_name = addcslashes($product_name, "'");


		/*$app_btn='<button class="btn btn-xs btn-warning" data-original-title="Approve Stock" data-toggle="tooltip" data-placement="top" onclick="change_stock_status('."'".$unit_name."'".','."'".$grn_trn_id."'".','."'".$total_qty."'".','."'".$unit_id."'".','."'".$gd_name."'".','."'".$product_name."'".','."'".$grn_no."'".','."'".$grndate."'".','."'".$product_id."'".','."'".$batch_id."'".','."'".$batch_no."'".','."'".$reprocess_qc."'".')"><i class="fa fa-check"></i></button>';
*/
			$app_btn='<button class="btn btn-xs btn-warning" data-original-title="Approve Stock" data-toggle="tooltip" data-placement="top" onclick="change_stock_status('."'".$total_qty."'".','."'".$batch_id."'".','."'".$reprocess_qc."'".','."'".$row['to_godown_id']."'".')"><i class="fa fa-check"></i></button>';


		$row_data[] = $app_btn;
		$row_data[] = '<input type="checkbox" chk name="chk[]" data-batch_id="'.$row['batch_id'].'" data-process_id="'.$row['process_id'].'" value="'.$row['batch_id'].'"/>';

		$appData[] = $row_data;
		$id++;
			
			}
	
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "load_child_godown_list") {
	$parent_gd_id = $POST['godown_id'];

	$str = get_last_node_godown_list($dbcon,"",$parent_gd_id);

		echo $str;
}
else if(strtolower($POST['mode']) == "fieldadd") {


	$info1['grn_trn_id']				= $POST['grn_trn_id'];
	$info1['godown_id']					= $_POST['godown_id'];
	$info1['batch_id']					= $_POST['batch_id'];

	$info1['qty']						= $_POST['qty'];
	$info1['unit_id']					= $_POST['unit_id'];

	$info1['product_id']				= $_POST['product_id'];
	$info1['user_id']					= $_SESSION['user_id'];
	$info1['company_id']				= $_SESSION['company_id'];

	$table='tbl_store_accept_trn';$tableid='store_accept_trn_id';

	if(!empty($POST['store_accept_id'])){
		$info1['store_accept_id']	= $POST['store_accept_id'];
		$info1['store_accept_trn_status']	= 0;
	}else{
		$info1['store_accept_trn_status']	= 3;
	}

	if(empty($POST['edit_id']))
	{
		$inserid=add_record($table,$info1, $dbcon);
	}
	else
	{
		$updateid=update_record($table,$info1,$tableid."=".$POST['edit_id'] , $dbcon);	
	}
}
else if(strtolower($POST['mode']) == "load_tempoutward") {
	if($POST['eid']){
		$query="select mgs.gd_name,umst.unit_name,trn.grn_trn_id,trn.qty,trn.store_accept_trn_id from tbl_store_accept_trn as trn
		left join mst_godown as mgs on mgs.gd_id=trn.godown_id
		left join unit_mst as umst on umst.unitid=trn.unit_id
		where trn.store_accept_trn_status=0 and batch_id=".$POST['batch_id']." and trn.store_accept_id=".$POST['eid'];

	}else{
		$query="select mgs.gd_name,umst.unit_name,trn.grn_trn_id,trn.qty,trn.store_accept_trn_id from tbl_store_accept_trn as trn
		left join mst_godown as mgs on mgs.gd_id=trn.godown_id
		left join unit_mst as umst on umst.unitid=trn.unit_id
		where trn.store_accept_trn_status=3 and batch_id=".$POST['batch_id'];
	}
			echo $query;
	$result=$dbcon->query($query);
	echo '<div class="form-group">
	<div class="col-md-12 col-xs-11">
	<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
	<tr id="field">
	<th class="text-center" width="4%">Sr No</th>
	<th class="text-center" width="40%">Godown</th>
	<th class="text-center" width="40%">Quantity</th>
	<th class="text-center" width="8%">Unit</th>
	<th class="text-center" width="8%">Action</th>
	</tr>';

			//echo $query;
	if(mysqli_num_rows($result)>0)
	{
		$i=1;$total_used_qty=0;
		while($rel=brp_mysqli_fetch_assoc($result))
		{
			echo '<tr id="fieldtr'.$i.'">
			<td style="vertical-align:top;">'.$i.'</td>
			<td style="vertical-align:top;">'.$rel["gd_name"].'</td>
			<td style="vertical-align:top;">'.$rel["qty"].'</td>
			<td style="vertical-align:top;">'.$rel["unit_name"].'</td>
			<td style="vertical-align:top">
			<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['store_accept_trn_id'].');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>

			<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['store_accept_trn_id'].');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
			</td>	
			</tr>';
			$total_used_qty=$total_used_qty+$rel["qty"];
			$i++;
		}
	}else{
		echo '<tr><td colspan="12" class="text-center">NO DATA FOUND</td></tr>';
	}
	echo '</table> 
	<input type="hidden" name="used_qty" id="used_qty" value="'.$total_used_qty.'" />
	</div>
	</div>';
}
else if(strtolower($POST['mode'])== "preedit")
{
	$q = $dbcon -> query("SELECT * FROM tbl_store_accept_trn as mst  WHERE store_accept_trn_id = '$POST[id]'");
	$r = $q->fetch_assoc();

	echo json_encode($r);
}
else if(strtolower($POST['mode'])== "delete_data")
{
	$row=array();
	$info['store_accept_trn_status']=2;	
	$updateid=update_record("tbl_store_accept_trn", $info,"store_accept_trn_id=".$POST['eid'] , $dbcon);

	if($updateid){
		$row['res']="1";
	}
	else{
		$row['res']="0";
	}
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "save_store_accept")
{

	// echo "<pre>";
	// print_r($POST);die;
	$batch_id = $POST['batch_id'];
	$batch_no = $POST['batch_no'];
	$reprocess_qc = $POST['reprocess_qc'];
	$info1['store_accept_no']		= $_POST['store_accept_no'];
	$info1['store_accept_date']		= date("Y-m-d",strtotime($_POST['store_accept_date']));
	$info1['batch_id'] = $batch_id;
	$info1['batch_no'] = $batch_no;
	$info1['reprocess_qc'] = $reprocess_qc;
	$info1['remark']				= $_POST['remark'];
	$info1['cdate']					= date("Y-m-d H:i:s");
	$info1['user_id']				= $_SESSION['user_id'];
	$info1['company_id']			= $_SESSION['company_id'];


	$table='tbl_store_accept';$tableid='store_accept_id';

	if(empty($POST['store_accept_id']))
	{
		//var_dump("fdsj");
		$inserid=add_record($table,$info1, $dbcon);
		
		$info['store_accept_id']		= $inserid;
		$info['store_accept_trn_status']		= 0;
		/*$updateid1=update_record("tbl_store_accept_trn",$info,"store_accept_trn_status=3 and 
		grn_trn_id=".$POST['grn_trn_id'] , $dbcon);*/

		$updateid1=update_record("tbl_store_accept_trn",$info,"store_accept_trn_status=3 and batch_id=".$batch_id , $dbcon);

		$abc=store_stock_add($dbcon,$inserid,$reprocess_qc);
		//var_dump($abc);

		$batch_info['stock_approval_status'] = 1;
		$upd_batch = update_record("tbl_batch_data",$batch_info,"batch_id=".$batch_id , $dbcon);
	

		$query="select count(batch_id) as total_accept from tbl_batch_data where grn_trn_id=".$POST['grn_trn_id']." and stock_approval_status = 0 and status = 0 and reprocess_qc_id = 0";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));

		if($rel['total_accept']=="0"){
			$infog['store_accept']		= 1;
			$updateid12=update_record("tbl_grn_trn",$infog,"grn_trn_id=".$POST['grn_trn_id'] , $dbcon);
		}
		update_store_accept_no($dbcon);
	}
	else
	{
		$updateid=update_record($table,$info1,$tableid."=".$POST['edit_id'] , $dbcon);	
	}
}
else if(strtolower($POST['mode'])== "get_store_accept_no")
{
	$store_no = get_store_accept_no($dbcon);

	echo $store_no;
}

else if(strtolower($POST['mode'])== "get_store_details")
{
	if($POST['to_godown_id'] == '0'){

	$qry = "SELECT batch.grn_id,sr.grn_trn_id,p.product_mat_center as product_godown, grn.grn_date, batch.batch_no,batch.batch_unit, grn.grn_no, gda.gd_name, batch.product_id, sr.product_qty, umst.unit_name,cmst.unit_name as conv_unit_name, p.product_name, sr.unit_id, batch.batch_id, batch.batch_qty, batch.accept_qty,batch.grn_godown, batch.reprocess_qc, qc.qc_no, qc.qc_date, batch.qc_id,batch.base_qty,batch.base_unit,batch.conv_qty,batch.conv_unit FROM tbl_batch_data as batch 
			left join tbl_grn_trn as sr on sr.grn_trn_id=batch.grn_trn_id 
			left join product_mst as p on p.product_id=batch.product_id 
			left join tbl_grn as grn on grn.grn_id=sr.grn_id 
			left join unit_mst as umst on umst.unitid=batch.base_unit 
			left join unit_mst as cmst on cmst.unitid=batch.conv_unit 
			left join mst_godown as gda on gda.gd_id=batch.grn_godown 
			left join tbl_qc_reject_new_product as rej_qc on rej_qc.batch_id = batch.batch_id and rej_qc.qc_id = batch.qc_id and rej_qc.product_id = batch.product_id 
			left join tbl_qc as qc on qc.qc_id = batch.qc_id where batch.batch_id = " . $POST['batch_id'];
	}else{

	 $qry = "SELECT batch.grn_id,sr.grn_trn_id,p.product_mat_center as product_godown, grn.grn_date, batch.batch_no,batch.batch_unit, grn.grn_no, gda.gd_name, batch.product_id, sr.product_qty, umst.unit_name,cmst.unit_name as conv_unit_name, p.product_name, sr.unit_id, batch.batch_id, batch.batch_qty, batch.accept_qty,batch.grn_godown, batch.reprocess_qc,qc.qc_no, qc.qc_date, batch.qc_id,batch.base_qty,batch.base_unit,batch.conv_qty,batch.conv_unit FROM tbl_batch_data as batch 
	 		left join tbl_grn_trn as sr on sr.grn_trn_id=batch.grn_trn_id 
	 		left join product_mst as p on p.product_id=sr.product_id 
	 		left join tbl_grn as grn on grn.grn_id=sr.grn_id 
	 		left join unit_mst as umst on umst.unitid=batch.base_unit 
	 		left join unit_mst as cmst on cmst.unitid=batch.conv_unit 
	 		left join mst_godown as gda on gda.gd_id=batch.to_godown_id 
	 		left join tbl_qc_reject_new_product as rej_qc on rej_qc.batch_id = batch.batch_id and rej_qc.qc_id = batch.qc_id and rej_qc.product_id = batch.product_id 
	 		left join tbl_qc as qc on qc.qc_id = batch.qc_id where batch.batch_id = " . $POST['batch_id'];
	}
	
	$q_res = $dbcon->query($qry);
	$res=brp_mysqli_fetch_assoc($q_res);


	$res['base_qty'] = round_up($res['base_qty'],5);
	$res['conv_qty'] = round_up($res['conv_qty'],5);


	$godown_id = 0;	
	if($res['qc_id'] > 0){
		$trn_qry = "SELECT qc_reject_godown as bt_godown_id FROM tbl_qc_trn where qc_status = 0 AND qc_id = " . $res['qc_id'];
	
	}else if($res['qc_id'] == '0'){
		$trn_qry = "SELECT qc_godown as bt_godown_id FROM tbl_qc where qc_status = 0 AND batch_id = " . $res['batch_id'];
	}

	$qc_result = $dbcon->query($trn_qry);
	$qc_cnt = brp_mysqli_num_rows($qc_result);

	if($qc_cnt > 0){
		$qc_row = brp_mysqli_fetch_assoc($qc_result);
		$godown_id = $qc_row['bt_godown_id'];	
	}else{
		$godown_id = $res['grn_godown'];
	}

	$res['gd_name'] = get_godown_name($dbcon, $godown_id);

	if(!empty($res['product_godown'])){
		$res['gd_name'] = get_godown_name($dbcon, $res['product_godown']);
	}


	

	$res['selected_godown'] = $godown_id;

	$q1 = "select strn.product_qty,strn.product_conv_qty, strn.product_base_unit,strn.product_conv_unit,strn.rp_id,smain.po_req_no,smain.sales_order_no,cust.l_name from tbl_grn_sub_trn as strn left join tbl_request_product as rp on rp.rp_id=strn.rp_id left join tbl_set_main_process as smain on smain.sp_id=rp.sp_id left join tbl_sales_ordertrn as so_trn on so_trn.sales_ordertrn_id = smain.sales_order_trn_id left join tbl_sales_order as so on so_trn.sales_order_id = so.sales_order_id left join tbl_ledger cust on so.cust_id=cust.l_id where strn.status = 0 and strn.grn_trn_id = " . $res['grn_trn_id'];
	$res1 = $dbcon->query($q1);

	if($res['batch_unit'] == $res['conv_unit']){
		$res['accept_qty2']=grn_convert_stock($dbcon,$res['accept_qty'],$res['grn_trn_id'],"base_unit");
	}else{
		$res['accept_qty2']=grn_convert_stock($dbcon,$res['accept_qty'],$res['grn_trn_id'],"conv_unit");
	}

	$str = "";
	$x= 1;
	if(brp_mysqli_num_rows($res1) > 0) {
		$str .='<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
				<tr id="field">
				<th class="text-center" width="5%">Sr No</th>
				<th class="text-center" width="30%">Sales Order No</th>
				<th class="text-center" width="30%">Workorder No</th>
				<th class="text-center" width="20%">Qty</th>';
				if($company_config['customer_show_in_production']=="1") {
					$str .='<th class="text-center" width="15%">Vendor</th>';
				}
				$str .='</tr>
			<tbody>';
		while($row = brp_mysqli_fetch_array($res1)){
			$str.= "<tr>
			<td>".$x."</td>
			<td>".$row['sales_order_no']."</td>
			<td>".$row['po_req_no']."</td>";
			
			if($res['batch_unit'] == $row['product_conv_unit']){	
				$str .="<td>".$row['product_conv_qty']." - ". $res['conv_unit_name'] ."</td>";
				
			}else{
				$str .="<td>".$row['product_qty']." - ". $res['unit_name'] ."</td>";	
			}
			if($company_config['customer_show_in_production']=="1") {
				$str .="<td>".$row['l_name']."</td>";
			}
			$str .="</tr>";

			$x++;
		}
		$str .= "</tbody></table>";
	}

	$res['so_details'] = $str;
	
	echo json_encode($res);
}


function store_stock_add($dbcon,$store_accept_id,$reprocess_qc){

	$query="select * from tbl_store_accept_trn as trn
	where trn.store_accept_trn_status=0 and store_accept_id=".$store_accept_id;
	//var_dump($query);
	$result=$dbcon->query($query);
	if(mysqli_num_rows($result)>0)
	{
		
		while($rel=brp_mysqli_fetch_assoc($result))
		{
			$accept_qty=$rel['qty'];
			// $accept_qty=$rel['batch_qty'];
			/*$query_grn="select trn.*,grn.grn_date,grn.ref_type from tbl_grn_trn as trn
			left join tbl_grn as grn on grn.grn_id=trn.grn_id
			where trn.grn_trn_status=0 and grn_trn_id=".$rel['grn_trn_id'];*/

		 $query_grn="select batch.*,trn.*,grn.grn_date,trn.branch_id as sel_branch from tbl_batch_data as batch
			left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id
			left join tbl_grn as grn on grn.grn_id=trn.grn_id
			where batch.batch_id =".$rel['batch_id'];

			// $accept_qty=$rel_grn['batch_qty'];
			
			$result_grn=$dbcon->query($query_grn);
			$rel_grn=brp_mysqli_fetch_assoc($result_grn);

			// var_dump($rel_grn['ref_type']);
			if($rel_grn['reprocess_qc'] == '1' && $rel_grn['ref_type']=="2"){

			}else if($rel_grn['is_scrap'] == '1'){
				add_stock($dbcon,$rel_grn['product_scrap_id'],$rel_grn['scrap_unit'],$rel_grn['grn_date'],"scrap",$rel_grn['grn_trn_id'],$rel['godown_id'],$rel_grn['scrap_qty'],"1",$rel_grn['branch_id'],"","","",$rel_grn['batch_id'],$rel_grn['batch_no']);
			}
			else if($rel_grn['ref_type']=="2"){
				
				purchase_stock_accept($dbcon,$rel['product_id'],$rel_grn['batch_unit'],$rel_grn['grn_date'],$rel_grn['grn_trn_id'],$rel['godown_id'],$accept_qty,$rel_grn['branch_id'],$rel_grn['po_ref_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);
			}else if($rel_grn['ref_type']=="1"){
				 jobwork_stock_accept($dbcon,$rel['grn_trn_id'],$rel['godown_id'],$rel['product_id'],$accept_qty,$rel_grn['batch_unit'],$rel_grn['batch_id'],$rel_grn['batch_no'],$rel_grn['reject_qty'],$rel_grn['auto_store_relese']);
			}else if($rel_grn['ref_type']=="3"){
				jobwork_stock_accept($dbcon,$rel['grn_trn_id'],$rel['godown_id'],$rel['product_id'],$rel['qty'],$rel_grn['batch_unit'],$rel_grn['batch_id'],$rel_grn['batch_no'],$rel_grn['reject_qty'],$rel_grn['auto_store_relese']);
			}else if($rel_grn['ref_type']=="4"){
				direct_grn_stock_accept($dbcon,$rel['product_id'],$rel_grn['batch_unit'],$rel_grn['grn_date'],$rel_grn['grn_trn_id'],$rel['godown_id'],$accept_qty,$rel_grn['branch_id'],$rel_grn['po_ref_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);
			}
			else if($rel_grn['ref_type']=="6"){  // returnable chalan stock
				$stock_date=date("Y-m-d");

				  $query11 = "select grn_sub_trn.grn_trn_sub_id,customer_id from tbl_grn_sub_trn as grn_sub_trn
								where grn_sub_trn.grn_trn_id=".$rel_grn['grn_trn_id'] ;

				$result1=$dbcon->query($query11);

				$res1 = brp_mysqli_fetch_assoc($result1);

				$stock_id=add_stock($dbcon,$rel['product_id'],$rel['unit_id'],$stock_date,"returnable",$rel_grn['grn_trn_id'],$rel['godown_id'],$rel['qty'],1,$rel_grn['sel_branch'],"","",$res1['customer_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);

				// returnable_stock_accept($dbcon,$rel['grn_trn_id'],$rel['godown_id'],$rel['product_id'],$rel['qty'],$rel['unit_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);
			}else if($rel_grn['ref_type']=="5"){ 
				$stock_date=date("Y-m-d");

				 $query11 = "select grn_sub_trn.grn_trn_sub_id,customer_id from tbl_grn_sub_trn as grn_sub_trn
								where grn_sub_trn.grn_trn_id=".$rel_grn['grn_trn_id'] ;

				$result1=$dbcon->query($query11);

				$res1 = brp_mysqli_fetch_assoc($result1);


				$stock_id=add_stock($dbcon,$rel['product_id'],$rel['unit_id'],$stock_date,"direct_grn",$res1['grn_trn_sub_id'],$rel['godown_id'],$rel['qty'],1,$rel_grn['sel_branch'],"","",$rel_grn['customer_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);
			}else if($rel_grn['ref_type']=="7"){
			
				stock_transfer_accept($dbcon,$rel['product_id'],$rel_grn['batch_unit'],$rel_grn['grn_date'],$rel_grn['grn_trn_id'],$rel['godown_id'],$accept_qty,$rel_grn['branch_id'],$rel_grn['stock_transfer_trn_id'],$rel_grn['batch_id'],$rel_grn['batch_no'],$rel_grn['to_godown_id']);
			}else{

				$stock_date=date("Y-m-d");

				$stock_id=add_stock($dbcon,$rel['product_id'],$rel['unit_id'],$stock_date,"reject_qc_new_product",'',$rel['godown_id'],$rel['qty'],1,$rel_grn['sel_branch'],"","",'',$rel_grn['batch_id'],$rel_grn['batch_no']);
			}
		}
	}
}



?>