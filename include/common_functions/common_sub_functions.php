<?php 


function get_userwise_approval_setting($dbcon,$module_type,$permission_user_id){
	$chkquery = $dbcon->query("SELECT * FROM `tbl_userwise_approval_setting` WHERE status = 0 AND company_id = '".$_SESSION['company_id']."' AND module_type = '".$module_type."' AND permission_user_id = '".$permission_user_id."' ORDER BY aprv_setting_id DESC LIMIT 1");

	/*var_dump("SELECT * FROM `tbl_userwise_approval_setting` WHERE status = 0 AND company_id = '".$_SESSION['company_id']."' AND module_type = '".$module_type."' AND permission_user_id = '".$permission_user_id."' ORDER BY aprv_setting_id DESC LIMIT 1");*/
	$cnt = brp_mysqli_num_rows($chkquery);
	$getquery = brp_mysqli_fetch_assoc($chkquery);
	$getquery['cnt'] = $cnt;
	return $getquery;
}
function get_automatic_quotation_approval($dbcon, $ins_quotation_id)
{
	$info1['approve_remark'] = 'Auto Approved by Admin';
	$info1['approve_status'] = 1;
	$info1['quotation_id']	 = $ins_quotation_id;
	$info1['user_id']		 = $_SESSION['user_id'];
	$info1['company_id']     = $_SESSION['company_id'];

	$inserid=add_record("tbl_quot_aprv_log", $info1, $dbcon, $branch_id);

	$infoaprvqt['approve_status']	= 1;
	$updateid=update_record('tbl_quotation', $infoaprvqt,"quotation_id=".$ins_quotation_id , $dbcon, $branch_id);
}
function get_automatic_so_approval($dbcon, $inserestimateid)
{
	$qt_qry="select quotation_id from tbl_sales_order as qt
	where qt.sales_order_id=".$inserestimateid;
	$qt_rel=mysqli_fetch_assoc($dbcon->query($qt_qry));

	$infoappr['approve_remark']	= 'Auto Approved by Admin';
	$infoappr['approve_status']	= 1;
	$infoappr['quotation_id']		= $qt_rel['quotation_id'];
	$infoappr['sales_order_id']	= $inserestimateid;
	$infoappr['user_id']			= $_SESSION['user_id'];
	$infoappr['company_id']		= $_SESSION['company_id'];
	$inserid=add_record("tbl_quot_po_aprv_log", $infoappr, $dbcon, $branch_id);

	$infoso['po_approve_status']	= 3;//Approved
	$infoso1['approve_status']	= 3;//Approved
	$infoso1['order_accept_status']	= 0;//Pending
	
	if(!empty($qt_rel['quotation_id'])){
		$updateid=update_record('tbl_quotation', $infoso,"sales_order_id=".$inserestimateid , $dbcon, $branch_id);
	}
	$updateid=update_record('tbl_sales_order', $infoso1,"sales_order_id=".$inserestimateid , $dbcon, $branch_id);
}
function get_automatic_oa_approval($dbcon, $inserestimateid)
{
	$qt_qrys="select approve_status, quotation_id from tbl_sales_order as qt
	where qt.sales_order_id=".$inserestimateid;
	$qt_rels=mysqli_fetch_assoc($dbcon->query($qt_qrys));

	if($qt_rels['approve_status'] == 3) {
		$infooapprv['approve_remark']	= 'Auto Approved by Admin';
		$infooapprv['approve_status']	= 1;
		$infooapprv['so_id']             = $inserestimateid;
		$infooapprv['user_id']			= $_SESSION['user_id'];
		$infooapprv['company_id']		= $_SESSION['company_id'];

		$insert_ids=add_record("tbl_oa_aprv_log", $infooapprv, $dbcon);

		$infosoap['order_accept_status'] = 1;

		$updateids=update_record('tbl_sales_order', $infosoap,"sales_order_id=".$inserestimateid , $dbcon, $branch_id);

		if(!empty($qt_rels['quotation_id'])) {
			$infoquot['po_approve_status'] = 3;
			$updateides=update_record('tbl_quotation', $infoquot,"sales_order_id=".$inserestimateid , $dbcon, $branch_id);
		}
		if($insert_ids){
			$q = "select pro.bom_required,trn.sales_ordertrn_id from tbl_sales_order as so 
			left join tbl_sales_ordertrn as trn on trn.sales_order_id=so.sales_order_id
			left join product_mst as pro on pro.product_id = trn.product_id
			where so.sales_order_id=".$inserestimateid;				
			$re = brp_mysqli_query($dbcon,$q);
			while($row = mysqli_fetch_array($re)){
				if($row['bom_required']==1){
					$infop['bom_status'] = 0;
				}else{
					$infop['bom_status'] = 1;
				}
				$updateid1=update_record('tbl_sales_ordertrn', $infop,"sales_ordertrn_id=".$row['sales_ordertrn_id'] , $dbcon, $branch_id);
			}
		}
	}
}
function get_financial_year_list($dbcon, $financial_year_id){
	$qry = "select * from tbl_financial_year where isdelete = 0 AND company_id = ".$_SESSION['company_id'];
	$rs_state = $dbcon->query($qry);
	$str = '';
	$str .= '<option value="">Choose Year</option>';
	while ($row = brp_mysqli_fetch_assoc($rs_state)) {
		$sel = '';
		//if($row['user_id']==$sid)
		if ($row['financial_year_id']==$financial_year_id) {
			$sel = 'selected="selected"';
		} else if($financial_year_id==''){
			$sel = ($row['current_status']==1) ? 'selected="selected"' : "";
		}else {
			$sel = "";
		}
		$str .= '<option '.$sel.' value="'.$row['financial_year_id'].'" data-start-end-date="'.date("M-Y",strtotime($row['financial_start_date'])).' - '.date("M-Y",strtotime($row['financial_end_date'])).'" data-financial-type="'.$row['finance_year_type'].'">'.date("M-Y",strtotime($row['financial_start_date'])).' - '.date("M-Y",strtotime($row['financial_end_date'])).'</option>';
	}
	return $str;
}
function get_forecast_type($dbcon, $id){
	$html = '';
	$html .= '<option value="1" ' . (($id == "1") ? "selected" : "selected") . '>Monthly</option>';
	$html .= '<option value="2" ' . (($id == "2") ? "selected" : "") . '>Yearly</option>';
	return $html;
}
function getFinacialyear_data_by_id($dbcon, $id)
{
	$query = "SELECT * FROM `tbl_financial_year` WHERE financial_year_id = ".$id;
	$q = $dbcon->query($query);
	$row = brp_mysqli_fetch_assoc($q);
	return $row;
}

function get_last_node_godown_list($dbcon,$sel_gd_id="",$parent_id="",$branch_id=""){

		$godown = array(
			'godowns' => array(),
			'parent_godown' => array()
		);
		$str = "<option value=''> Select Godown </option>";

		if($parent_id !=""){
			$query = "SELECT gd_id,p_gd_id, gd_name	FROM mst_godown where g_status = 0 and p_gd_id = " . $parent_id;
			$q = $dbcon->query($query);
			if(brp_mysqli_num_rows($q) > 0){
				while ($row = brp_mysqli_fetch_assoc($q)) {
					$str .= build_last_node_godown($dbcon, $row);
				}
			}else{
				$query = "SELECT gd_id,p_gd_id, gd_name	FROM mst_godown where g_status = 0 and gd_id = " . $parent_id;
				$q = $dbcon->query($query);
				$row = brp_mysqli_fetch_assoc($q);
				$str .= "<option value=".$row['gd_id'].">". $row['gd_name'] ."</option>";
			}		
		}else{
			$query = "SELECT gd_id,p_gd_id, gd_name	FROM mst_godown where g_status = 0";
			$q = $dbcon->query($query);

			while ($row = brp_mysqli_fetch_assoc($q)) {
				$godown['godowns'][$row['gd_id']] = $row;
				$godown['parent_godown'][$row['p_gd_id']][] = $row['gd_id'];
			}

			$str .= build_all_last_node_godown($dbcon,0, $godown,$sel_gd_id);				
		}
		
		return $str;
	}

function build_all_last_node_godown($dbcon,$parent, $godown,$sel_gd_id) {
	$html = "";
	
	if (isset($godown['parent_godown'][$parent])) {

		foreach ($godown['parent_godown'][$parent] as $gd_id) {
			// var_dump('--->'.$sel_gd_id);
			if (!isset($godown['parent_godown'][$gd_id])) {

				$sel = "";
				if($sel_gd_id == $godown['godowns'][$gd_id]['gd_id']){
					$sel = 'selected="selected"';
				}
				$html .= "<option ".$sel." value=".$godown['godowns'][$gd_id]['gd_id'].">". $godown['godowns'][$gd_id]['gd_name'] ."</option>";
			}else{
				$html .= build_all_last_node_godown($dbcon,$gd_id, $godown,$sel_gd_id);
			}
			// if (isset($godown['parent_godown'][$gd_id])) {
			// 	$html .= "<option value=".$godown['godowns'][$gd_id]['gd_name'].">". $godown['godowns'][$gd_id]['gd_name'] ."</option>";
				
			// }
		}
	}
	return $html;
}

function build_last_node_godown($dbcon,$data) {
	$html = "";
	$query = "SELECT gd_id,p_gd_id,gd_name FROM mst_godown where g_status = 0 and p_gd_id = " . $data['gd_id'];
	
	$q = $dbcon->query($query);
	$cnt = brp_mysqli_num_rows($q);
	if($cnt > 0){
		
		while ($row = brp_mysqli_fetch_assoc($q)) {
			$html .= build_last_node_godown($dbcon, $row);								
		}
	}else{
		$html .= "<option value=".$data['gd_id'].">". $data['gd_name'] ."</option>";
	}
	return $html;
}
function count_stock_transfer_grn_pending($dbcon)
{
    $query = "SELECT count(stock_transfer_id) as total_pending_grn FROM tbl_stock_transfer as grn 
			where grn.company_id=" . $_SESSION['company_id'] . " and grn.grn_status =0 and grn.status  = 0 and grn.approve_status = 1";

    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_assoc($rs_cust);

    $total = $rel['total_pending_grn'];

    if ($total == 0)
    {
        return 0;
    }
    else
    {
        return $total;
    }
}

function get_for_period_id($dbcon, $id)
{
	$query = "SELECT * FROM `forecast_period_mst` WHERE f_period_id = ".$id;
	$q = $dbcon->query($query);
	$row = brp_mysqli_fetch_assoc($q);
	return $row;
}
function get_for_target_amt_qty($dbcon,$f_user_id,$sdate,$edate,$f_product, $forecast_base){
	$where = "";
	$companyConfiguration=getCompanyConfiguration($dbcon);
	if($forecast_base!='1'){
		$where = " AND sotrn.product_id = ".$f_product;
	}
	if($companyConfiguration['forecast_calculation']==1){
		$quer = "SELECT SUM(sotrn.product_amount) as total, SUM(sotrn.product_qty) as qty FROM tbl_quotation_trn AS sotrn LEFT JOIN tbl_quotation AS so ON so.quotation_id = sotrn.quotation_id WHERE so.quotation_status = 0 AND so.approve_status = 1 AND sotrn.quot_trn_status = 0 AND so.company_id = ".$_SESSION['company_id']." AND so.user_id = ".$f_user_id."".$where." AND so.quotation_date BETWEEN '".$sdate."' AND '".$edate."'";
	}else if($companyConfiguration['forecast_calculation']==2){
		$quer = "SELECT SUM(sotrn.product_amount) as total, SUM(sotrn.product_qty) as qty FROM tbl_sales_ordertrn AS sotrn LEFT JOIN tbl_sales_order AS so ON so.sales_order_id = sotrn.sales_order_id WHERE so.sales_order_status = 0 AND so.order_accept_status = 1 AND sotrn.sales_ordertrn_status = 0 AND so.company_id = ".$_SESSION['company_id']." AND so.user_id = ".$f_user_id."".$where." AND so.sales_order_date BETWEEN '".$sdate."' AND '".$edate."'";
	}else if($companyConfiguration['forecast_calculation']==3){
		$quer = "SELECT SUM(sotrn.product_amount) as total, SUM(sotrn.product_qty) as qty FROM tbl_invoicetrn AS sotrn LEFT JOIN tbl_invoice AS so ON so.invoice_id = sotrn.invoice_id WHERE so.invoice_status = 0 AND sotrn.trancation_status = 0 AND so.company_id = ".$_SESSION['company_id']." AND so.user_id = ".$f_user_id."".$where." AND so.invoice_date BETWEEN '".$sdate."' AND '".$edate."'";
	}
		$res = brp_mysqli_fetch_assoc($dbcon->query($quer));
	return $res;
}
function get_reason_by_id($dbcon, $id)
{
	$query = "SELECT * FROM `tbl_reason_mst` WHERE id = ".$id;
	$q = $dbcon->query($query);
	$row = brp_mysqli_fetch_assoc($q);
	return $row;
}


/*function enter_production_stock_effect($dbcon, $general_stock_id){
	$query = "select * from tbl_general_stock_trn where status=0 and general_stock_id=".$general_stock_id;
	$result = $dbcon->query($query);
	while($row = brp_mysqli_fetch_array($result)){

	}
}

function delete_product_stock_effect($dbcon, $general_stock_id){
	$query = "select * from tbl_general_stock_trn where status=0 and general_stock_id=".$general_stock_id;
	$result = $dbcon->query($query);
	while($row = brp_mysqli_fetch_array($result)){

	}
}*/

function get_general_stock_batch_no($dbcon,$product_id){
	$str='';
	$query="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i
		where stock_status=0 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and product_id = ".$product_id." and batch_no != '' group by batch_no";

	$rs_batch=$dbcon->query($query);
	$str.= '<option value="">Choose Batch No</option>';
	while($rel=brp_mysqli_fetch_assoc($rs_batch))
	{	
		if($rel['pending_base_stock'] > 0){
			$str.= '<option value="'.$rel['stock_id'].'" data-stock="'.$rel['base_stock'].'" >'.$rel['batch_no'].'</option>';
		}
	}

	return $str;
}

function get_godown_wise_batch_no($dbcon,$product_id,$godown_id){
	$str='';
	$query="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i
			where stock_status=0 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and product_id = ".$product_id." and godown_id=".$godown_id." and batch_no != '' group by batch_no";

		$rs_batch=$dbcon->query($query);
		$str.= '<option value="">Choose Batch No</option>';
		while($rel=brp_mysqli_fetch_assoc($rs_batch))
		{	
			if($rel['pending_base_stock'] > 0){
				$str.= '<option value="'.$rel['stock_id'].'" data-stock="'.$rel['base_stock'].'" >'.$rel['batch_no'].'</option>';
			}
		}
	return $str;
}

function enter_production_stock_effect($dbcon,$general_stock_id){

	$query = "select * from tbl_general_stock_trn
	where  status=0 and stock_type=2 and general_stock_id=".$general_stock_id;
	$result = $dbcon->query($query);

	while($row = brp_mysqli_fetch_array($result)){
		$sel_stock = "select * from tbl_reserve_stock where stock_status != 2 and stock_flage = 1 and ref_name='production_bypass' and ref_id =".$row['general_stock_trn_id'];
		$sel_stock_rs = $dbcon->query($sel_stock);
		$cnt_stock_temp = brp_mysqli_num_rows($sel_stock_rs);
		if($cnt_stock_temp > 0){	
			while($sel_stock_rel=brp_mysqli_fetch_assoc($sel_stock_rs)){
				$info_stock['reserve_date']	= date('Y-m-d');
				$info_stock['product_id']	= $sel_stock_rel['product_id'];
				$info_stock['base_unit']	= $sel_stock_rel['base_unit'];
				$info_stock['base_stock']	= $sel_stock_rel['base_stock'];
				$info_stock['convert_unit']	= $sel_stock_rel['convert_unit'];
				$info_stock['convert_stock']= $sel_stock_rel['convert_stock'];
				$info_stock['stock_flage']	= 2;
				$info_stock['ref_name']		= $sel_stock_rel['ref_name'];
				$info_stock['ref_id']		= $sel_stock_rel['ref_id'];
				$info_stock['stock_id']		= $sel_stock_rel['stock_id'];
				$info_stock['godown_id']	= $sel_stock_rel['godown_id'];
				$info_stock['branch_id']	= $sel_stock_rel['branch_id'];
				$info_stock['cdate']		= date('Y-m-d H:i:s');
				$info_stock['user_id']		= $_SESSION['user_id'];
				$info_stock['company_id']	= $_SESSION['company_id'];
				
				$inserid=add_record('tbl_reserve_stock', $info_stock, $dbcon);

				add_stock($dbcon,$sel_stock_rel['product_id'],$sel_stock_rel['base_unit'],$info_stock['reserve_date'],$info_stock['ref_name'],$info_stock['ref_id'],$info_stock['godown_id'],$info_stock['base_stock'],2,$info_stock['branch_id'],$info_stock['stock_id']);
			}
		}
	}

	$query = "select trn.product_id, batch.unitid, batch.cdate, trn.general_stock_trn_id, batch.godown_id, batch.qty, batch.batch_stock_no, trn.product_rate, trn.product_conv_rate from tbl_general_stock_trn as trn 
	left join tbl_batch_stock_trn_in as batch on batch.general_stock_trn_id = trn.general_stock_trn_id
	where batch.status=0 and trn.status=0 and trn.stock_type=1 and trn.general_stock_id=".$general_stock_id;
	$result = $dbcon->query($query);

	while($row = brp_mysqli_fetch_array($result)){
		$date = date('d-m-Y',strtotime($row['cdate']));
		add_stock($dbcon,$row['product_id'],$row['unitid'],$date,"production_bypass",$row['general_stock_trn_id'],$row['godown_id'],$row['qty'],"1",$_SESSION['branch_id'],"","","","", $row['batch_stock_no'], $row['product_rate'], $row['product_conv_rate']);
	}
}

function delete_product_stock_effect($dbcon,$general_stock_id){
	$query = "select * from tbl_general_stock_trn where status=0 and stock_type=2 and general_stock_id=".$general_stock_id;
	$result = $dbcon->query($query);

	while($row = brp_mysqli_fetch_array($result)){
		$query1 = "select * from tbl_reserve_stock where stock_status !=2 AND ref_name='production_bypass' and ref_id=".$row['general_stock_trn_id'];

		$result1 = $dbcon->query($query1);

		while($row1 = brp_mysqli_fetch_array($result1)){
			$query2 = "select stock_id,used_base_stock,used_convert_stock from tbl_stock_trn where stock_id=".$row1['stock_id'];
			$result2 = $dbcon->query($query2);
			$row2 = brp_mysqli_fetch_array($result2);
			$used_base_stock = $row2['used_base_stock']-$row1['base_stock'];
			$used_convert_stock = $row2['used_convert_stock']-$row1['convert_stock'];
			$info1['used_base_stock'] 		= $used_base_stock;
			$info1['used_convert_stock'] 	= $used_convert_stock;
			
			$updateid = update_record('tbl_stock_trn', $info1,"stock_id=".$row2['stock_id'] , $dbcon);
		}

		$info['stock_status'] = 2;
		$updateid = update_record('tbl_reserve_stock', $info,"ref_name='production_bypass' and ref_id=".$row['general_stock_trn_id'] , $dbcon);
	}
}

function delete_deduct_product_stock_effect($dbcon,$general_stock_trn_id){
	$query = "select * from tbl_general_stock_trn where status=0 and stock_type=2 and general_stock_trn_id=".$general_stock_trn_id;
	$result = $dbcon->query($query);

	while($row = brp_mysqli_fetch_array($result)){
		$query1 = "select * from tbl_reserve_stock where ref_name='production_bypass' and ref_id=".$general_stock_trn_id;

		$result1 = $dbcon->query($query1);

		while($row1 = brp_mysqli_fetch_array($result1)){
			$query2 = "select stock_id,used_base_stock,used_convert_stock from tbl_stock_trn where stock_id=".$row1['stock_id'];
			$result2 = $dbcon->query($query2);
			$row2 = brp_mysqli_fetch_array($result2);
			$used_base_stock = $row2['used_base_stock']-$row1['base_stock'];
			$used_convert_stock = $row2['used_convert_stock']-$row1['convert_stock'];
			$info1['used_base_stock'] 		= $used_base_stock;
			$info1['used_convert_stock'] 	= $used_convert_stock;
			
			$updateid = update_record('tbl_stock_trn', $info1,"stock_id=".$row2['stock_id'] , $dbcon);
		}
		
		$info['stock_status'] = 2;
		$updateid = update_record('tbl_reserve_stock', $info,"ref_name='production_bypass' and ref_id=".$row['general_stock_trn_id'] , $dbcon);
	}
}



function get_jobsheet_qty_details($dbcon,$product_id,$rp_id,$process_id,$process_type){
	$accept_qty = 0;
	$reject_qty = 0;
	$reprocess_qty = 0;
	$arr = array();	 
	$ref_type = 0;
	if($process_type == '1'){
		$ref_type = '3';
	}else{
		$ref_type = '1';
	}

	$qc_paramter_info = check_product_qc_paramter($dbcon,$product_id,$process_id);
	if($qc_paramter_info=='1')
	{ //  fetch from batch 
		$qry = "select IFNULL(b.accept_qty,0) as accept_qty,IFNULL(b.reject_qty,0) as reject_qty,IFNULL(b.reprocess_qty,0) as reprocess_qty from tbl_grn_sub_trn as st 
		left join tbl_grn_trn as trn on trn.grn_trn_id = st.grn_trn_id 
		left join tbl_batch_data as b on b.grn_trn_id = trn.grn_trn_id 
		where st.product_id = ".$product_id." and trn.ref_type = ".$ref_type." and st.rp_id = ".$rp_id." and trn.process_id = " . $process_id;
		$res = $dbcon->query($qry);
		$row = brp_mysqli_fetch_assoc($res);

			$arr['accept_qty'] = $row['accept_qty'] == 0 ? '' : $row['accept_qty'];
		$arr['reject_qty'] = $row['reject_qty'] == 0 ? '' : $row['reject_qty'];
		$arr['reprocess_qty'] = $row['reprocess_qty'] == 0 ? '' :  $row['reprocess_qty'];
	}else{

		$qry = "select IFNULL(sum(st.product_qty),0) as accept_qty from tbl_grn_sub_trn as st left join tbl_grn_trn as trn on trn.grn_trn_id = st.grn_trn_id where st.product_id = ".$product_id." and trn.ref_type = ".$ref_type." and st.rp_id = ".$rp_id." and trn.process_id = " . $process_id ;	
		$res = $dbcon->query($qry);
		$row = brp_mysqli_fetch_assoc($res);

			$arr['accept_qty'] = $row['accept_qty'] == 0 ? '' : $row['accept_qty'];
		$arr['reject_qty'] = '';
		$arr['reprocess_qty'] = '';
	}

	return $arr;
}


function get_jobsheet_qty_details_reprocess($dbcon,$product_id,$rp_id,$process_id,$process_type){
	$accept_qty = "";
	$reject_qty = "";
	$reprocess_qty = "";
	$arr = array();	 
	$ref_type = 0;
	if($process_type == '1'){
		$ref_type = '3';
	}else{
		$ref_type = '1';
	}

	
		$qry = "select IFNULL(b.accept_qty,0) as accept_qty,IFNULL(b.reject_qty,0) as reject_qty,IFNULL(b.reprocess_qty,0) as reprocess_qty from tbl_batch_data as b 
		where b.reprocess_qc = 1 and b.product_id = ".$product_id." and b.process_id = " . $process_id;
		$res = $dbcon->query($qry);
		$row = brp_mysqli_fetch_assoc($res);

		$arr['accept_qty'] = $row['accept_qty'] == 0 ? '' : $row['accept_qty'];
		$arr['reject_qty'] = $row['reject_qty'] == 0 ? '' : $row['reject_qty'];
		$arr['reprocess_qty'] = $row['reprocess_qty'] == 0 ? '' :  $row['reprocess_qty'];
	

	return $arr;
}

function count_store_order_wise_bom($dbcon)
{
   $query = "SELECT SQL_CALC_FOUND_ROWS mst.doc_no, mst.order_id, pro.product_id, mst.base_unit, reqqty, pro.product_name, tc.cat_name, mst.base_qty, mst.wo_base_qty, mst.base_request_qty FROM tbl_store_order_min_max as mst left join product_mst as pro on pro.product_id=mst.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join (select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0 group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id where ( 1 AND mst.status=0 and mst.wo_complete_status=0 and mst.company_id=".$_SESSION['company_id']." and bom_status = 0)";

        $rs = $dbcon->query($query);

        $count = brp_mysqli_num_rows($rs);
        return $count;
    }
function auto_store_approval_entry($dbcon,$batch_id){
	$qry = "select * from tbl_batch_data where batch_id = " . $batch_id;
	$result = $dbcon->query($qry);
	$row = brp_mysqli_fetch_assoc($result);


	$godown_id = $row['grn_godown'];

	$btmp_q1 = "SELECT new_godown_id FROM tbl_temp_qc where status = 0 AND batch_id = " .$batch_id . " and grn_trn_id = " . $row['grn_trn_id'];

	$btmp_q1_res = $dbcon->query($btmp_q1);
	$btmp_q1_cnt = brp_mysqli_num_rows($btmp_q1_res);
	if($btmp_q1_cnt > 0){
		$btmp_q1_rw = brp_mysqli_fetch_assoc($btmp_q1_res); 
		$godown_id = $btmp_q1_rw['new_godown_id'];
	}

	// insert in tbl_store_accept

	$batch_no = $row['batch_no'];
	$reprocess_qc = $row['reprocess_qc'];
	$info1['store_accept_no']		=  get_store_accept_no($dbcon);
	$info1['store_accept_date']		= date('Y-m-d');
	$info1['batch_id'] = $batch_id;
	$info1['batch_no'] = $batch_no;
	$info1['reprocess_qc'] = $reprocess_qc;
	$info1['remark']				= "";
	$info1['cdate']					= date("Y-m-d H:i:s");
	$info1['user_id']				= $_SESSION['user_id'];
	$info1['company_id']			= $_SESSION['company_id'];
	$info1['branch_id']			= $row['branch_id'];

	$store_accept_id=add_record('tbl_store_accept',$info1, $dbcon);
	if($store_accept_id){
		update_store_accept_no($dbcon);	

		$info_trn['grn_trn_id']				= $row['grn_trn_id'];
		$info_trn['godown_id']					= $godown_id;
		$info_trn['batch_id']					= $row['batch_id'];
		$info_trn['qty']						= $row['grn_accept_qty'];
		$info_trn['unit_id']					= $row['batch_unit'];
		$info_trn['product_id']				= $row['product_id'];
		$info_trn['user_id']					= $_SESSION['user_id'];
		$info_trn['company_id']				= $_SESSION['company_id'];
		$info_trn['store_accept_id']	= $store_accept_id;
		$info_trn['store_accept_trn_status']	= 0;
	
		$inserid=add_record('tbl_store_accept_trn',$info_trn, $dbcon);

		$accept_qty=$row['grn_accept_qty'];
		
		 $query_grn="select batch.*,trn.*,grn.grn_date,trn.branch_id as sel_branch from tbl_batch_data as batch
			left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id
			left join tbl_grn as grn on grn.grn_id=trn.grn_id
			where batch.batch_id =".$batch_id;

			// $accept_qty=$rel_grn['batch_qty'];
			
			$result_grn=$dbcon->query($query_grn);
			$rel_grn=brp_mysqli_fetch_assoc($result_grn);

			// var_dump($rel_grn['ref_type']);
			if($rel_grn['reprocess_qc'] == '1' && $rel_grn['ref_type']=="2"){

			}else if($rel_grn['is_scrap'] == '1'){
				add_stock($dbcon,$rel_grn['product_scrap_id'],$rel_grn['scrap_unit'],$rel_grn['grn_date'],"scrap",$rel_grn['grn_trn_id'],$godown_id,$rel_grn['scrap_qty'],"1",$rel_grn['branch_id'],"","","",$rel_grn['batch_id'],$rel_grn['batch_no']);
			}
			else if($rel_grn['ref_type']=="2"){
			
				purchase_stock_accept($dbcon,$row['product_id'],$rel_grn['batch_unit'],$rel_grn['grn_date'],$rel_grn['grn_trn_id'],$godown_id,$accept_qty,$rel_grn['branch_id'],$rel_grn['po_ref_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);
			}else if($rel_grn['ref_type']=="1"){
				 jobwork_stock_accept($dbcon,$row['grn_trn_id'],$godown_id,$row['product_id'],$accept_qty,$rel_grn['batch_unit'],$rel_grn['batch_id'],$rel_grn['batch_no'],$rel_grn['reject_qty'],$rel_grn['auto_store_relese']);
			}else if($rel_grn['ref_type']=="3"){
				jobwork_stock_accept($dbcon,$row['grn_trn_id'],$godown_id,$row['product_id'],$accept_qty,$rel_grn['batch_unit'],$rel_grn['batch_id'],$rel_grn['batch_no'],$rel_grn['reject_qty'],$rel_grn['auto_store_relese']);
			}else if($rel_grn['ref_type']=="4"){
				direct_grn_stock_accept($dbcon,$row['product_id'],$rel_grn['batch_unit'],$rel_grn['grn_date'],$rel_grn['grn_trn_id'],$godown_id,$accept_qty,$rel_grn['branch_id'],$rel_grn['po_ref_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);
			}
			else if($rel_grn['ref_type']=="6"){  // returnable chalan stock
				$stock_date=date("Y-m-d");

				  $query11 = "select grn_sub_trn.grn_trn_sub_id,customer_id from tbl_grn_sub_trn as grn_sub_trn
								where grn_sub_trn.grn_trn_id=".$rel_grn['grn_trn_id'] ;

				$result1=$dbcon->query($query11);

				$res1 = brp_mysqli_fetch_assoc($result1);

				$stock_id=add_stock($dbcon,$row['product_id'],$row['unit_id'],$stock_date,"returnable",$rel_grn['grn_trn_id'],$godown_id,$accept_qty,1,$rel_grn['sel_branch'],"","",$res1['customer_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);

				// returnable_stock_accept($dbcon,$rel['grn_trn_id'],$rel['godown_id'],$rel['product_id'],$rel['qty'],$rel['unit_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);
			}else if($rel_grn['ref_type']=="5"){ 
				$stock_date=date("Y-m-d");

				 $query11 = "select grn_sub_trn.grn_trn_sub_id,customer_id from tbl_grn_sub_trn as grn_sub_trn
								where grn_sub_trn.grn_trn_id=".$rel_grn['grn_trn_id'] ;

				$result1=$dbcon->query($query11);

				$res1 = brp_mysqli_fetch_assoc($result1);


				$stock_id=add_stock($dbcon,$row['product_id'],$row['batch_unit'],$stock_date,"direct_grn",$res1['grn_trn_sub_id'],$godown_id,$accept_qty,1,$rel_grn['sel_branch'],"","",$rel_grn['customer_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);
			}else if($rel_grn['ref_type']=="7"){
			
				stock_transfer_accept($dbcon,$row['product_id'],$rel_grn['batch_unit'],$rel_grn['grn_date'],$rel_grn['grn_trn_id'],$godown_id,$accept_qty,$rel_grn['branch_id'],$rel_grn['stock_transfer_trn_id'],$rel_grn['batch_id'],$rel_grn['batch_no'],$rel_grn['to_godown_id']);
			}else{

				$stock_date=date("Y-m-d");

				$stock_id=add_stock($dbcon,$row['product_id'],$row['batch_unit'],$stock_date,"reject_qc_new_product",'',$godown_id,$accept_qty,1,$rel_grn['sel_branch'],"","",'',$rel_grn['batch_id'],$rel_grn['batch_no']);
			}
	}
}

function purchase_stock_accept($dbcon,$product_id,$unit_id,$grn_date,$grn_trn_id,$godown_id,$accept_qty,$branch_id,$po_ref_id,$batch_id,$batch_no){
	
	// $stock_id=add_stock($dbcon,$product_id,$unit_id,$grn_date,"tbl_grn_trn",$grn_trn_id,$godown_id,$accept_qty,"1",$branch_id,"","","",$batch_id,$batch_no);
	$poacceptqty=$accept_qty;

	$bt_base_qty = 0;
	$batch_conv_qty = 0;
	
	 $query = "select sum(sgtr.product_qty) as base_qty, sum(sgtr.product_conv_qty) as conv_qty, sgtr.product_base_unit, rp_id, sgtr.product_conv_unit, ptrn.unit_id,ptrn.conv_unit_id,ptrn.rate_unit,avg(ptrn.product_rate) as product_rate, pmst.product_base_qty, pmst.product_conv_qty from tbl_grn_trn as gtrn 
	left join tbl_grn_sub_trn as sgtr on sgtr.grn_trn_id = gtrn.grn_trn_id
	left join tbl_purchaseordertrn as ptrn on ptrn.purchaseordertrn_id=sgtr.purchaseordertrn_id
	left join product_mst as pmst on pmst.product_id = ptrn.product_id
	where gtrn.grn_trn_status = 0 and sgtr.status = 0 and gtrn.grn_trn_id=".$grn_trn_id." group by ptrn.product_rate,rp_id order by rp_id desc";
	$exe = $dbcon->query($query);
	while($row = brp_mysqli_fetch_array($exe)){
		// if($unit_id == $row['product_base_unit']){
		// 	$accept_qty = $row['product_qty'];
		// }else{
		// 	$accept_qty = $row['product_conv_qty'];
		// }


		if($row['rate_unit'] == $row['unit_id']){
			$base_rate = $row['product_rate']; //1000
			$conv_rate = ($row['base_qty']/$row['conv_qty'])*$base_rate;
			$bt_base_qty = $accept_qty;
			$batch_conv_qty = ($accept_qty/$row['base_qty'])*$row['conv_qty'];

			
		}else{
			$conv_rate = $row['product_rate'];
			$base_rate = ($row['conv_qty']/$row['base_qty'])*$conv_rate;
			$batch_conv_qty = $accept_qty;
			$bt_base_qty = ($accept_qty/$row['conv_qty'])*$row['base_qty'];
		}

		$accept_qty = 0;
		
		$request_id = $row['rp_id'];
		if($bt_base_qty > 0){
			$stock_id=add_stock($dbcon,$product_id,$row['product_base_unit'],$grn_date,"tbl_grn_trn",$grn_trn_id,$godown_id,$bt_base_qty,"1",$branch_id,"","","",$batch_id,$batch_no,$base_rate,$conv_rate);	 

		// grn_purchase_add_stock($dbcon,$product_id,$grn_trn_id,$base_unit,$stock_qty,$conv_unit,$stock_conv_qty,$godown_id,$stock_qty,$branch_id,$customer_id="",$batch_id="",$batch_no="",$base_rate="",$conv_rate="",$workorder_id=0);

		}
		if($request_id > 0){
		update_workorder_complete_qty_and_Status($dbcon,$request_id,$row['base_qty']);
		
			grn_sub_trn_wise_reserv_stock_add($dbcon,$row['base_qty'],$row['product_base_unit'],$grn_date,"",$request_id,$stock_id,$customer_id);
		}
		

	}

	
	

	// $query_res="select * from tbl_request_product as req where rp_id in (".$po_ref_id.")";
	// $result_res=$dbcon->query($query_res);

	// $resqty1=$accept_qty;
	// // var_dump($resqty1);
	// // var_dump($unit_id);
	// while($row_res=brp_mysqli_fetch_assoc($result_res)){
		
	// 	$query_ind="select sum(approve_qty) as app_qty from approve_indent as req where rp_id=".$row_res['rp_id'];
	// 	$result_ind=$dbcon->query($query_ind);
	// 	$row_ind=brp_mysqli_fetch_assoc($result_ind);
	// 	$reserve_id="";
	// 	$request_id=$row_res['rp_id'];
	// 	$complaint_id="";
	// 	$sales_order_trn_id="";
	// 	$customer_id = $row_res['customer_id'];

	// 	$used_rese=total_reserve_stock($dbcon,$request_id,$unit_id,$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$customer_id,"wo_allocate");
	// 	$res_pending=$row_ind['app_qty']-$used_rese;
		
	// 	/*var_dump($res_pending);
	// 	echo "resqty1 : " . $resqty1 . " -- res_pending : " . $res_pending .'---';*/
		
	// 	if($resqty1>=$res_pending){
	// 		update_workorder_complete_qty_and_Status($dbcon,$request_id,$res_pending);
	// 		// var_dump('1 qty :  ' . $res_pending);
	// 		grn_sub_trn_wise_reserv_stock_add($dbcon,$res_pending,$unit_id,$grn_date,"",$request_id,$stock_id,$customer_id);

	// 		$resqty1=$resqty1-$res_pending;
	// 	}else if($resqty1 > 0){
	// 		update_workorder_complete_qty_and_Status($dbcon,$row_res['rp_pid'],$resqty1);
	// 		// var_dump('el1 :  ' . $resqty1);
	// 		grn_sub_trn_wise_reserv_stock_add($dbcon,$resqty1,$unit_id,$grn_date,"",$request_id,$stock_id,$customer_id);

	// 		$resqty1=$resqty1-$resqty1;
	// 	}
	// }

	

	 $query1 = "select sgtr.purchaseordertrn_id,sgtr.product_qty as grn_qty,sgtr.product_base_unit,sgtr.product_conv_qty as grn_conv_qty,sgtr.product_conv_unit,ptrn.wip_po_stock,IFNULL(ptrn.wip_po_used_stock,0) as wip_po_used_stock,ptrn.wip_po_stock_conv,IFNULL(ptrn.wip_po_used_stock_conv,0) as wip_po_used_stock_conv,ptrn.product_qty,ptrn.product_conv_qty,ptrn.unit_id,ptrn.conv_unit_id from tbl_grn_sub_trn as sgtr 
	left join tbl_purchaseordertrn as ptrn on ptrn.purchaseordertrn_id=sgtr.purchaseordertrn_id
	where sgtr.status = 0 and sgtr.grn_trn_id=".$grn_trn_id;
	$exe1 = $dbcon->query($query1);
	while($row1 = brp_mysqli_fetch_array($exe1)){
		if($poacceptqty>0){
			if($poacceptqty>=$row1['grn_conv_qty']){
				$pocon_qty=$row1['grn_conv_qty'];
			}else{
				$pocon_qty=$poacceptqty;
			}
			$poacceptqty=$poacceptqty-$pocon_qty;
			 $base_stock=$row1['product_qty']*$pocon_qty/$row1['product_conv_qty'];
			$upd_wip_stock["wip_po_used_stock_conv"] = $row1['wip_po_used_stock_conv'] + $pocon_qty;
			$upd_wip_stock["wip_po_used_stock"] = $row1['wip_po_used_stock'] + $base_stock;
			//var_dump($upd_wip_stock);
			$updateid=update_record("tbl_purchaseordertrn", $upd_wip_stock,"purchaseordertrn_id=".$row1['purchaseordertrn_id'], $dbcon);

			if($resqty1>0){
				$query2 = "select (allocate_base_qty-allocate_base_used_qty) as base_pending,allocate_base_unit,(allocate_conv_qty-allocate_conv_used_qty) as conv_pending,allocate_conv_unit,rp_id,purchase_allocate_id,allocate_base_used_qty,allocate_conv_used_qty from wip_purchase_stock_allocate as sgtr 
				where sgtr.purchase_allocate_status = 0 and sgtr.allocate_conv_qty>sgtr.allocate_conv_used_qty and sgtr.purchaseordertrn_id=".$row1['purchaseordertrn_id'];
				$exe2 = $dbcon->query($query2);
				while($row2 = brp_mysqli_fetch_array($exe2)){
					if($pocon_qty>0){
						if($pocon_qty>=$resqty1){
							$pores_qty=$resqty1;
						}else{
							$pores_qty=$pocon_qty;
						}
						$pocon_qty=$pocon_qty-$pores_qty;

						grn_sub_trn_wise_reserv_stock_add($dbcon,$pores_qty,$unit_id,$grn_date,"",$row2['rp_id'],$stock_id,$customer_id);
						$base_stock_used=$row1['product_qty']*$pores_qty/$row1['product_conv_qty'];
						$upd_wip_stock_t["allocate_base_used_qty"] = $row2['allocate_base_used_qty'] + $base_stock_used;
						$upd_wip_stock_t["allocate_conv_used_qty"] = $row2['allocate_conv_used_qty'] + $pores_qty;

						$updateid=update_record("wip_purchase_stock_allocate", $upd_wip_stock_t,"purchase_allocate_id=".$row2['purchase_allocate_id'], $dbcon);
					}
					
				}
			}
		}
		
	} 
	
}


function direct_grn_stock_accept($dbcon,$product_id,$unit_id,$grn_date,$grn_trn_id,$godown_id,$accept_qty,$branch_id,$po_ref_id,$batch_id,$batch_no){

	
	$stock_id=add_stock($dbcon,$product_id,$unit_id,$grn_date,"tbl_grn_trn",$grn_trn_id,$godown_id,$accept_qty,"1",$branch_id,"","","",$batch_id,$batch_no);
			  
	/*$query_res="select * from tbl_request_product as req where rp_id in (".$po_ref_id.")";
	$result_res=$dbcon->query($query_res);

	$resqty1=$accept_qty;
	//var_dump($resqty1);
	//var_dump($unit_id);
	while($row_res=brp_mysqli_fetch_assoc($result_res)){

		$query_ind="select sum(approve_qty) as app_qty from approve_indent as req where rp_id=".$row_res['rp_id'];
		$result_ind=$dbcon->query($query_ind);
		$row_ind=brp_mysqli_fetch_assoc($result_ind);
		$reserve_id="";
		$request_id=$row_res['rp_id'];
		$complaint_id="";
		$sales_order_trn_id="";
		$customer_id = $row_res['customer_id'];

		$used_rese=total_reserve_stock($dbcon,$row_res['rp_pid'],$unit_id,$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$customer_id);
		$res_pending=$row_ind['app_qty']-$used_rese;
		//var_dump($res_pending);

		// echo "resqty1 : " . $resqty1 . " -- res_pending : " . $res_pending .'---';
		if($resqty1>=$res_pending){

			grn_sub_trn_wise_reserv_stock_add($dbcon,$res_pending,$unit_id,$grn_date,"",$request_id,$stock_id,$customer_id);

			$resqty1=$resqty1-$res_pending;
		}else if($resqty1 > 0){

			grn_sub_trn_wise_reserv_stock_add($dbcon,$resqty1,$unit_id,$grn_date,"",$request_id,$stock_id,$customer_id);

			$resqty1=$resqty1-$resqty1;
		}
	}*/
}

function jobwork_stock_accept($dbcon,$grn_trn_id,$godown_id,$product_id,$qty,$unit_id,$batch_id,$batch_no,$reject_qty,$auto_store_relese){

  $query = "select grn_sub_trn.extra_stock,grn_sub_trn.grn_trn_id,grn_sub_trn.grn_trn_sub_id,grn_sub_trn.product_id,grn_sub_trn.purchaseordertrn_id,grn_sub_trn.job_work_sub_trn_id,grn_sub_trn.product_qty,grn_sub_trn.product_base_unit,grn_sub_trn.customer_id,grn_sub_trn.product_stock_used_qty from tbl_grn_sub_trn as grn_sub_trn
	where grn_sub_trn.status=0 and cast(grn_sub_trn.product_qty AS DECIMAL(50,5)) >  cast(grn_sub_trn.product_stock_used_qty AS DECIMAL(50,5)) and grn_sub_trn.grn_trn_id=".$grn_trn_id;

//var_dump($query);
	$result=$dbcon->query($query);
	$cnt=brp_mysqli_num_rows($result);
	//var_dump("ss");
	//var_dump($cnt);
	if($cnt>0){

		while($row=brp_mysqli_fetch_array($result)){

		if(!empty($row['job_work_sub_trn_id'])){
			$trn_pending_qty=$row['product_qty']-$row['product_stock_used_qty'];
			if($qty>=$trn_pending_qty){
				$product_qty=$trn_pending_qty;

			}else{
				$product_qty=$qty;
			}

			$query1= "select job_sub_trn.p_id,job_sub_trn.rp_id,job_sub_trn.customer_id,rp.workorder_short_close,rp.sp_id,rp.main_request from tbl_job_work_sub_trn as job_sub_trn
			left join tbl_request_product as rp on rp.rp_id = job_sub_trn.rp_id
			where job_sub_trn.job_work_sub_trn_id=".$row['job_work_sub_trn_id'] ;
			$result1=$dbcon->query($query1);
			$cnt1=brp_mysqli_num_rows($result1);

			if($cnt1>0){
				$row1=brp_mysqli_fetch_array($result1);

				$workorder_short_close = $row1['workorder_short_close'];

			 $query2 = "select grn_godown,branch_id from tbl_grn_trn as grn_trn
				where grn_trn.grn_trn_id=".$row['grn_trn_id'] ;
				$result2=$dbcon->query($query2);
				$row2=brp_mysqli_fetch_array($result2);

				$stock_date=date("Y-m-d");

				$process=p_id_wise_find_previous_and_next_process($dbcon,$row1['p_id']);
				$process_pr=json_decode($process);

				$next_process_id=$process_pr->next_process_id;
				$next_process_type=$process_pr->next_process_type;
				$next_process_priority=$process_pr->next_process_priority;

				$previous_process_pid=$process_pr->previous_process_pid;

				$workorder_process_id = $process_pr->workorder_process_id;
				 /*var_dump($previous_process_pid);
				 var_dump($next_process_id);*/
				 update_workorder_complete_qty_and_Status($dbcon,$row1['rp_id'],0,$reject_qty);
				/*var_dump("previous_process_pid :  " . $previous_process_pid);		
				var_dump("next_process_id :  " . $next_process_id);		
				var_dump("auto_store_relese :  " . $auto_store_relese);*/		
				if($previous_process_pid=="0" && $next_process_id=="0"){
					$qry__2 = "select * from tbl_grn_sub_trn where grn_trn_sub_id=".$row['grn_trn_sub_id'];
					$result__3=$dbcon->query($qry__2);
					$row__3=brp_mysqli_fetch_array($result__3);


					$base_rate = $row__3['process_pus_material_rate'] / $row__3['product_qty']; //1000
					$conv_rate = $row__3['process_pus_material_conv_rate'] / $row__3['product_conv_qty'];

					$work_order_id = 0;

						if($row1['main_request'] == '1'){
							$work_order_id = $row1['sp_id'];
						}



					$stock_id=add_stock($dbcon,$product_id,$unit_id,$stock_date,"Grn_sub_trn",$row['grn_trn_sub_id'],$godown_id,$product_qty,1,$row2['branch_id'],"","",$row1['customer_id'],$batch_id,$batch_no,$base_rate,$conv_rate,$work_order_id);
								//product stock add end
						// update_workorder_complete_qty_and_Status($dbcon,$row1['rp_id'],$product_qty,$reject_qty);
						update_workorder_complete_qty_and_Status($dbcon,$row1['rp_id'],$product_qty);

						// if($row['extra_stock']=='0'){
								//product reserve stock start
								if($workorder_short_close == '0'){
									grn_sub_trn_wise_reserv_stock_add($dbcon,$product_qty,$unit_id,$stock_date,$row1['p_id'],$row1['rp_id'],$stock_id,$row1['customer_id']);		
								}
							
								//product reserve stock end
						// }
						// die;

				}
				else if($previous_process_pid=="0"){
					if($row['extra_stock']=='0'){
					//process stock add start
						$process_stock_id=production_add_process_stock($dbcon,$product_qty,$unit_id,$row1['p_id'],$stock_date,$godown_id,"Grn_sub_trn",$row['grn_trn_sub_id']);
						//process stock add end
					}
						//next process entry start

					// $next_pid=next_process_entry($dbcon,$product_qty,$unit_id,$row1['p_id'],$next_process_id,$next_process_type,$next_process_priority,$workorder_process_id,$auto_store_relese,$batch_id);

						if($workorder_short_close == '0'){
							$next_pid=next_process_entry($dbcon,$product_qty,$unit_id,$row1['p_id'],$next_process_id,$next_process_type,$next_process_priority,$workorder_process_id,$auto_store_relese,$batch_id);
						}
						//next process entry end
						if($row['extra_stock']=='0' && $workorder_short_close == '0'){
						//reserve process stock start
							$process_reserve_id=production_reserve_add_process_stock($dbcon,$product_qty,$unit_id,$next_pid,$process_stock_id,$stock_date,"Grn_sub_trn",$row['grn_trn_sub_id'],$auto_store_relese);

							if($process_reserve_id){
								auto_add_next_process_material_entry($dbcon,$product_qty,$unit_id,$row1['p_id'],$next_pid,$next_process_id,$next_process_type,$next_process_priority,$workorder_process_id,$auto_store_relese,$batch_id);;
							}
						//reserve process stock end
						}else{

							if($row['extra_stock']=='1' && $auto_store_relese==1){

								$que_1="select ap.*,pro.product_base_unit,pro.product_conv_unit from tbl_allocate_process as ap
								left join product_mst as pro on pro.product_id = ap.p_product_id
								 where ap.p_id=".$next_pid;
								$rs_di_1=$dbcon->query($que_1);
								$re_1=brp_mysqli_fetch_assoc($rs_di_1);

								if($re_1['product_conv_unit']==$unit_id){
									$type="base_unit";
									$auto_con_stock=$product_qty;
									$auto_base_stock=convert_stock_new($dbcon,$product_qty,$re['product_id'],$type);
								}else{
									$type="conv_unit";
									$auto_base_stock=$product_qty;
									$auto_con_stock=convert_stock_new($dbcon,$product_qty,$re_1['p_product_id'],$type);
								}

								$info_store_req['rp_id']		= $re_1['p_ref_id'];
								$info_store_req['product_id']	= $re_1['p_product_id'];
								$info_store_req['process_id']	= $re_1['process_id'];
								$info_store_req['remark']		= "Auto Approval";
								$info_store_req['cdate']		= date("Y-m-d H:i:s");
								$info_store_req['user_id']	= $_SESSION['user_id'];
								$info_store_req['company_id']	= $_SESSION['company_id'];
								$info_store_req['branch_id']	= $re_1['branch_id'];
								$info_store_req['base_unit'] = $re_1['product_base_unit'];
								$info_store_req['conv_unit'] = $re_1['product_conv_unit'];
								
								$info_store_req['p_id']		= $next_pid;
								$info_store_req['base_qty']	= $auto_base_stock;
								$info_store_req['conv_qty']	= $auto_con_stock;
								$info_store_req['release_qty']	= $auto_base_stock;
								// var_dump($info_store_req);
									$req_id = add_record('tbl_store_request',$info_store_req, $dbcon);
								
									if($req_id){
										$infor['p_id'] 					= $next_pid;
										$infor['rp_id'] 				= $re_1['p_ref_id'];
										$infor['process_id'] 			= $re_1['process_id'];
										$infor['release_qty'] 			=$auto_base_stock;
										$infor['release_unit'] 			= $re_1['product_base_unit'];
										$infor['release_conv_qty'] 		= $auto_con_stock;;
										$infor['release_conv_unit'] 	= $re_1['product_conv_unit'];
										$infor['issue_no'] 	= get_issue_no($dbcon);
										$infor['issue_date'] 	= date("Y-m-d");
										$infor['remark'] 				= "Auto Approval";
										$infor['cdate'] 				= date("Y-m-d H:i:s");
										$infor['user_id'] 				=	$_SESSION['user_id'];
										$infor['to_user_id'] 				=	$_SESSION['to_user_id'];
										$infor['company_id'] 			= $_SESSION['company_id'];
										// var_dump($infor);
										$req_t_id = add_record('tbl_store_release',$infor, $dbcon,$row['branch_id']);
										if($req_t_id){
											update_issue_no($dbcon);
										}

									}
									
								}
						}
					


				}else if($next_process_id=="0"){
						//var_dump("cd");
						//last process
							//product stock add start 
							$qry__2 = "select * from tbl_grn_sub_trn where grn_trn_sub_id=".$row['grn_trn_sub_id'];
							$result__3=$dbcon->query($qry__2);
							$row__3=brp_mysqli_fetch_array($result__3);
		
		
							$base_rate = $row__3['process_pus_material_rate'] / $row__3['product_qty']; //1000
							$conv_rate = $row__3['process_pus_material_conv_rate'] / $row__3['product_conv_qty'];

						$work_order_id = 0;

						if($row1['main_request'] == '1'){
							$work_order_id = $row1['sp_id'];
						}

						$stock_id=add_stock($dbcon,$product_id,$unit_id,$stock_date,"Grn_sub_trn",$row['grn_trn_sub_id'],$godown_id,$product_qty,1,$row2['branch_id'],"","",$row1['customer_id'],$batch_id,$batch_no,$base_rate,$conv_rate,$work_order_id);
							//product stock add end
							// update_workorder_complete_qty_and_Status($dbcon,$row1['rp_id'],$product_qty,$reject_qty);
						update_workorder_complete_qty_and_Status($dbcon,$row1['rp_id'],$product_qty);
						
							// if($row['extra_stock']=='0'){
							//reserve stock add start
							if($workorder_short_close == '0'){
								grn_sub_trn_wise_reserv_stock_add($dbcon,$product_qty,$unit_id,$stock_date,$row1['p_id'],$row1['rp_id'],$stock_id,$row1['customer_id']);
							}
							//reserve stock add end
							// }				
				}else{
					//middel process
					//process stock add start
					if($row['extra_stock']=='0'){
						$process_stock_id=production_add_process_stock($dbcon,$product_qty,$unit_id,$row1['p_id'],$stock_date,$godown_id,"Grn_sub_trn",$row['grn_trn_sub_id']);
						//process stock add end
					}
						//next process entry start
						if($workorder_short_close == '0'){
							$next_pid=next_process_entry($dbcon,$product_qty,$unit_id,$row1['p_id'],$next_process_id,$next_process_type,$next_process_priority,$workorder_process_id,$auto_store_relese,$batch_id);
						}
						//next process entry stop
						if($row['extra_stock']=='0' && $workorder_short_close == '0'){
						//reserve process stock start
							$process_reserve_id=production_reserve_add_process_stock($dbcon,$product_qty,$unit_id,$next_pid,$process_stock_id,$stock_date,"Grn_sub_trn",$row['grn_trn_sub_id'],$auto_store_relese,$next_process_id);

							if($process_reserve_id){
								auto_add_next_process_material_entry($dbcon,$product_qty,$unit_id,$row1['p_id'],$next_pid,$next_process_id,$next_process_type,$next_process_priority,$workorder_process_id,$auto_store_relese,$batch_id);;
							}
						//reserve process stock end
						}else{

							if($row['extra_stock']=='1' && $auto_store_relese==1){

								$que_1="select ap.*,pro.product_base_unit,pro.product_conv_unit from tbl_allocate_process as ap
								left join product_mst as pro on pro.product_id = ap.p_product_id
								 where ap.p_id=".$next_pid;
								$rs_di_1=$dbcon->query($que_1);
								$re_1=brp_mysqli_fetch_assoc($rs_di_1);

								if($re_1['product_conv_unit']==$unit_id){
									$type="base_unit";
									$auto_con_stock=$product_qty;
									$auto_base_stock=convert_stock_new($dbcon,$product_qty,$re['product_id'],$type);
								}else{
									$type="conv_unit";
									$auto_base_stock=$product_qty;
									$auto_con_stock=convert_stock_new($dbcon,$product_qty,$re_1['p_product_id'],$type);
								}

								$info_store_req['rp_id']		= $re_1['p_ref_id'];
								$info_store_req['product_id']	= $re_1['p_product_id'];
								$info_store_req['process_id']	= $re_1['process_id'];
								$info_store_req['remark']		= "Auto Approval";
								$info_store_req['cdate']		= date("Y-m-d H:i:s");
								$info_store_req['user_id']	= $_SESSION['user_id'];
								$info_store_req['company_id']	= $_SESSION['company_id'];
								$info_store_req['branch_id']	= $re_1['branch_id'];
								$info_store_req['base_unit'] = $re_1['product_base_unit'];
								$info_store_req['conv_unit'] = $re_1['product_conv_unit'];
								
								$info_store_req['p_id']		= $next_pid;
								$info_store_req['base_qty']	= $auto_base_stock;
								$info_store_req['conv_qty']	= $auto_con_stock;
								$info_store_req['release_qty']	= $auto_base_stock;
								// var_dump($info_store_req);
									$req_id = add_record('tbl_store_request',$info_store_req, $dbcon);
								
									if($req_id){
										$infor['p_id'] 					= $next_pid;
										$infor['rp_id'] 				= $re_1['p_ref_id'];
										$infor['process_id'] 			= $re_1['process_id'];
										$infor['release_qty'] 			=$auto_base_stock;
										$infor['release_unit'] 			= $re_1['product_base_unit'];
										$infor['release_conv_qty'] 		= $auto_con_stock;;
										$infor['release_conv_unit'] 	= $re_1['product_conv_unit'];
										$infor['issue_no'] 	= get_issue_no($dbcon);
										$infor['issue_date'] 	= date("Y-m-d");
										$infor['remark'] 				= "Auto Approval";
										$infor['cdate'] 				= date("Y-m-d H:i:s");
										$infor['user_id'] 				=	$_SESSION['user_id'];
										$infor['to_user_id'] 				=	$_SESSION['to_user_id'];
										$infor['company_id'] 			= $_SESSION['company_id'];
										// var_dump($infor);
										$req_t_id = add_record('tbl_store_release',$infor, $dbcon,$row['branch_id']);
										if($req_t_id){
											update_issue_no($dbcon);
										}

									}
									
								}
						}
					
				}

				$s_qry = "select * from tbl_allocate_process where p_id = " .$row1['p_id'];
				$res_2=$dbcon->query($s_qry);
				$row_2=brp_mysqli_fetch_array($res_2);
				update_completed_process_time_and_qty($dbcon, $row_2['process_id'], $row_2['resource_id'], $row_2['p_ref_id'], $product_qty);
				$s_qryq = "select * from tbl_allocate_process where p_id = " .$next_pid;
				$res_2q=$dbcon->query($s_qryq);
				$row_2q=brp_mysqli_fetch_array($res_2q);
				if($row_2q['pr_process_type']==1){
					resource_schedule_assign_at_process_allocate($dbcon, $row_2q['p_ref_id'], $row_2q['pen_qty'], $next_pid);
				}
				
			}

			$dbcon->query("update tbl_grn_sub_trn set product_stock_used_qty=product_stock_used_qty+".$product_qty." where grn_trn_sub_id=".$row['grn_trn_sub_id']."");

		}
	}
		$dbcon->query("update tbl_grn_trn set store_accept = 1 where grn_trn_id=".$grn_trn_id);
	}
}

function stock_transfer_accept($dbcon,$product_id,$unit_id,$grn_date,$grn_trn_id,$godown_id,$accept_qty,$branch_id,$stock_transfer_trn_id,$batch_id,$batch_no,$to_godown_id){


	$stock_id=add_stock($dbcon,$product_id,$unit_id,$grn_date,"tbl_grn_trn",$grn_trn_id,$godown_id,$accept_qty,"1",$branch_id,"","","",$batch_id,$batch_no);

}

function get_extra_stock($dbcon, $pro_id, $unit_id, $branch_id = "",$ext_stock_vendor_id="")
  {
    $whr = "";
    if ($branch_id != "")
    {
        $whr = " and branch_id = " . $branch_id;
    }
    if ($ext_stock_vendor_id != "")
    {
        $whr = " and vendor_id = " . $ext_stock_vendor_id;
    }


    $query = 'SELECT pro.product_id,base_qty_add,base_qty_minus,conv_qty_minus,conv_qty_add FROM `product_mst` as pro 

    left join (select IFNULL(sum(qc.base_qty),0) as base_qty_add,qc.product_id from smpl_extra_stock as qc 
       where qc.status=0 and  qc.base_unit=' . $unit_id . $whr . ' and qc.company_id=' . $_SESSION['company_id'] . ' 
       group by qc.product_id) as qc on qc.product_id=pro.product_id

       left join (select IFNULL(sum(qc.used_base_qty),0) as base_qty_minus,qc.product_id from smpl_extra_stock as qc 
       where qc.status=0 and qc.base_unit=' . $unit_id . $whr . ' and qc.company_id=' . $_SESSION['company_id'] . ' 
       group by qc.product_id) as qc1 on qc1.product_id=pro.product_id

       left join (select IFNULL(sum(qc.conv_qty),0) as conv_qty_add,qc.product_id from smpl_extra_stock as qc 
       where qc.status=0 and qc.base_unit!=qc.conv_unit and qc.conv_unit=' . $unit_id . ' and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
       group by qc.product_id) as qc2 on qc2.product_id=pro.product_id

       left join (select IFNULL(sum(qc.used_conv_qty),0) as conv_qty_minus,qc.product_id from smpl_extra_stock as qc 
       where qc.status=0 and  qc.base_unit!=qc.conv_unit and qc.conv_unit=' . $unit_id . ' and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
       group by qc.product_id) as qc3 on qc3.product_id=pro.product_id

       where pro.product_id=' . $pro_id;

       $rows = brp_mysqli_fetch_assoc($dbcon->query($query));
    //echo "<pre>"; print_r($rows);
       $stock = ($rows['base_qty_add'] + $rows['conv_qty_add']) - ($rows['base_qty_minus'] + $rows['conv_qty_minus']);

    //$stock=($row['base_qty_add']+$row['conv_qty_add'])-($row['base_qty_minus']+$row['conv_qty_minus']);


    return floatval($stock);
    //return $query;

   }

function get_extra_stock_batch_no($dbcon, $product_id, $unit_id, $branch_id = "",$ext_stock_vendor_id=0){

	 $qry = "select batch_no,group_concat(extra_stock_id) as stock_id,IFNULL(SUM(base_qty)-SUM(used_base_qty),0) as base_stock,IFNULL(SUM(conv_qty)-SUM(used_conv_qty),0) as conv_stock from smpl_extra_stock where product_id=".$product_id." and cast(base_qty AS DECIMAL(50,5)) > cast(used_base_qty AS DECIMAL(50,5)) and vendor_id = ".$ext_stock_vendor_id." and branch_id = " . $branch_id . " group by batch_no,product_id";
	$str="<option value=''>Selec Batch No</option>";
	$result=$dbcon->query($qry);
	$unitname = getunitname($dbcon,$unit_id);
	if(brp_mysqli_num_rows($result)>0)
	{	
		while($rel=brp_mysqli_fetch_assoc($result))
		{
			$str .= '<option value="'.$rel['stock_id'].'" data-batch_no="'.$rel['batch_no'].'">'.$rel['batch_no'].' - (' . $rel['base_stock'] . ' '. $unitname . ')</option>';
		}
	}
	return $str;
}

function bom_show_extra_no_print($dbcon, $bom_id, $qty, $num, $call, $space,$main_bom_id)
{
    $query_m = "select * from tbl_bom as bom where bom_status=0 and bom_id=" . $bom_id;
    $result_m = $dbcon->query($query_m);
    $rel_m = mysqli_fetch_assoc($result_m);

    $companyConfiguration = getCompanyConfiguration($dbcon);
    $bom_pro_print = explode(",", $companyConfiguration['bom_pro_print']);

    $query1 = "select bom_trn.*,pro.product_name,pro.product_icode,pro.image_name,pro.product_type,pro.product_alias_name,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,dwg.drawing_number, reqqty, (((IFNULL(base_stock_add,0)+IFNULL(con_stock_add,0))-(IFNULL(base_stock_minus,0)+IFNULL(con_stock_minus,0)))+IFNULL(reqqty,0)) as stock, pro.product_alias_name, pro.product_desc from tbl_bomtrn as bom_trn 
    left join product_mst as pro on pro.product_id=bom_trn.product_id
    left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
    left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
    left join tbl_drawing as dwg on dwg.drawing_id=pro.drawing_id
    left join (select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0  group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id
    left join (select sum(qc.base_stock) as base_stock_add,qc.product_id,qc.base_unit from tbl_stock_trn as qc where qc.stock_status=0 and stock_flage=1 and qc.company_id=" . $_SESSION['company_id'] . " group by qc.product_id) as qc4 on qc4.product_id=pro.product_id and qc4.base_unit=pro.product_base_unit
    left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id,qc.base_unit from tbl_stock_trn as qc where qc.stock_status=0 and stock_flage=2 and qc.company_id=" . $_SESSION['company_id'] . " group by qc.product_id) as qc1 on qc1.product_id=pro.product_id and qc1.base_unit=pro.product_base_unit
    left join (select sum(qc.convert_stock) as con_stock_add,qc.product_id,qc.convert_unit from tbl_stock_trn as qc where qc.stock_status=0 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.company_id=" . $_SESSION['company_id'] . " group by qc.product_id) as qc2 on qc2.product_id=pro.product_id and qc2.convert_unit=pro.product_base_unit
    left join (select sum(qc.convert_stock) as con_stock_minus,qc.product_id,qc.convert_unit from tbl_stock_trn as qc where qc.stock_status=0 and stock_flage=2 and qc.base_unit!=qc.convert_unit and qc.company_id=" . $_SESSION['company_id'] . " group by qc.product_id) as qc3 on qc3.product_id=pro.product_id and qc3.convert_unit=pro.product_base_unit
    where bom_trn_status=0 and bom_id=" . $bom_id;
    $result1 = $dbcon->query($query1);
    $k = 1;
    $new_call = $call + 1;
    /* for ($x = 1; $x <= $call; $x++) {
    	$space=$space."&nbsp;&nbsp;";
    } */
    while ($rel1 = brp_mysqli_fetch_assoc($result1))
    {
        $alias_name = '';
        if (in_array('alias', $bom_pro_print))
        {
            $alias_name = " -- (" . $rel1['product_alias_name'] . ")";
        }
        $drawing_number = '';
        if (in_array('drawing', $bom_pro_print))
        {
            $drawing_number = " -- (" . $rel1['drawing_number'] . ")";
        }
        $item_code = '';
        if (in_array('item', $bom_pro_print))
        {
            $item_code = " -- (" . $rel1['product_icode'] . ")";
        }
        if ($rel1['image_name'] != null)
        {
            //$image_name1 = '<a href="'.ROOT.'view/upload/product_images/'.$rel1["image_name"].'" target="_blank"><img src="'.ROOT.'view/upload/product_images/'.$rel1['image_name'].'" style="width: 60px;height: 50px;"></a>';
            $image_name1 = '<img src="' . ROOT . 'view/upload/product_images/' . $rel1['image_name'] . '" style="width: 60px;height: 50px;">';
        }
        else
        {
            $image_name1 = '';
        }
        $new_num = $num . "." . $k;

        $base_one_qty = $rel1['product_base_qty'] / $rel_m['product_base_qty'];
        $base_qty = $base_one_qty * $qty;
        $conv_stock = convert_stock($dbcon, $base_qty, $rel1['product_id'], "conv_unit");
        $base_qty1 = number_format($base_qty, 3, '.', '');
        $conv_stock1 = number_format($conv_stock, 3, '.', '');
        $stock = $rel1['stock'] - $rel1['reqqty'];
        $html .= '<tr>
        <td style="border:0.5px #444 solid;">' . $new_num . '</td>';
        if ($companyConfiguration['enable_item_image'] == 1)
        {
            $html .= '<td style="border:0.5px #444 solid;">' . $image_name1 . '</td>';
        }
        $html .= '<td style="border:0.5px #444 solid;">' . $rel1['product_name'] . '' . $item_code . '' . $drawing_number . '' . $alias_name . '<br>';
        if ($companyConfiguration['enable_item_description'] == 1)
        {
            $html .= $rel['product_desc'];
        }
        $chkMaterial = $dbcon->query("SELECT bmt.*, mp.material_parameter_name FROM tbl_bom_material_trn as bmt LEFT JOIN tbl_material_parameter as mp ON mp.material_parameter_id = bmt.material_parameter_id WHERE bmt.bom_material_trn_status = 0 AND bmt.bom_trn_id='" . $rel1['bom_trn_id'] . "'");
        while ($getMaterial = brp_mysqli_fetch_assoc($chkMaterial))
        {
            $html .= $getMaterial['material_parameter_name'] . ' - ' . $getMaterial['material_parameter_value'] . '<br>';
        }
        if (brp_mysqli_num_rows($chkMaterial) > 0)
        {
            $html .= 'Calculation: ' . $rel1['product_kg'];
        }
        $html .= '</td>
        <td style="border:1px #444 solid;" >' . get_product_type_by_id($dbcon, $rel1['product_type']) . '</td>';

        /*$html .= '<td style="border:1px #444 solid;" >'.$stock.'</td>';*/

        $html .= '<td style="border:1px #444 solid;" >';
        if ($rel1['product_base_unit'] != $rel1['product_conv_unit'])
        {
            $html .= $base_qty1 . ' ' . $rel1['base_unit_name'] . '<br/>';
            $html .= $conv_stock1 . ' ' . $rel1['conv_unit_name'];
        }
        else
        {
            $html .= $base_qty1 . ' ' . $rel1['base_unit_name'];
        }
        $html .= '</td>
        <td style="border:1px #444 solid;" >';
        /*$query="select mst.*,p.process_name,reso.resource_name from tbl_product_process as mst
        left join tbl_resource as reso on reso.resource_id=mst.resource_id
        left join process_mst as p on p.process_id=mst.process_id where mst.product_id=".$rel1['product_id']." order by process_priority";*/
        $query = "select prb.priority, mst.*,p.process_name,reso.resource_name,mst.process_type from pro_bom_process as prb left join tbl_product_process as mst ON mst.pr_process_id= prb.pr_process_id left join tbl_resource as reso on reso.resource_id=mst.resource_id left join process_mst as p on p.process_id=mst.process_id where mst.status = 0 and prb.product_id=" . $rel1['product_id'] . " and prb.bom_version_id = " . $rel1['bom_version_id'] . " and prb.process_status = 0 order by prb.priority";
        $result = $dbcon->query($query);
        $cnt = mysqli_num_rows($result);
        if ($cnt > 0)
        {
            $html .= '<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
            <tr>
            <th style="border:0.5px #444 solid;text-align:center;" >Priority</th>
            <th style="border:0.5px #444 solid;text-align:center;" >Process Type</th>
            <th style="border:0.5px #444 solid;text-align:center;" >Process Name</th>
            <th style="border:0.5px #444 solid;text-align:center;" >Resource Name</th>
            </tr>';
            while ($rel = mysqli_fetch_assoc($result))
            {
                if ($rel['process_type'] == 1)
                {
                    $process_type = "Inhouse";
                }
                else
                {
                    $process_type = "Outside";
                }
                $html .= '<tr>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $rel['priority'] . '</td>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $process_type . '</td>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $rel['process_name'] . '</td>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $rel['resource_name'] . '</td>
                </tr>';
            }
            $html .= '</table>';
        }
          $chk_data = check_extra_bom_no($dbcon,$rel1['product_id'],$main_bom_id,$rel1['p_bom_id'],$rel1['bom_id'],$rel1['bom_version_id']);

        
		$edit_id = $chk_data['ext_id'];
		$ext_no =  $chk_data['ext_no'];

         $btn_text = ($edit_id > 0) ? "UPDATE" : "ADD";
        $html .= '</td>
       <td style="border:1px #444 solid;" >
				<div class="div_extra_bom_no" style="display:flex;margin:10px;">
						<input width="125" type="text" class="form-control extra_bom_no" data-main_bom_id="'.$main_bom_id.'" data-bom_id="'.$rel1['p_bom_id'].'" data-bom_version="'.$rel1['bom_version_id'].'" data-parent_bom_id="'.$rel1['bom_id'].'" data-edit_id="'.$edit_id.'" data-product_id='.$rel1['product_id'].' value="'.$ext_no.'">
						<button style="margin-left:15px" class="btn btn-primary btn_save_extra_bom_no">'.$btn_text.'</button>
					</div>
			</td>
        </tr>';

        $html .= bom_show_extra_no_print($dbcon, $rel1['p_bom_id'], $base_qty, $new_num, $new_call, $space);
        $k++;
    }
    return $html;
}


function check_extra_bom_no($dbcon,$product_id,$main_bom_id,$bom_id,$p_bom_id,$bom_version_id){

	$query = "SELECT ext_id,ext_no from tbl_bom_extra_no where bom_id = ".$bom_id."  and  main_bom_id = ".$main_bom_id."  and parent_bom_id = ".$p_bom_id." and bom_version_id = ".$bom_version_id." and product_id = " . $product_id;
	$result = $dbcon->query($query);
	
	if(brp_mysqli_num_rows($result) > 0){
		$row = brp_mysqli_fetch_assoc($result);
		$arr['ext_no'] = $row['ext_no'];
		$arr['ext_id'] =  $row['ext_id'];
	}else{
		$arr['ext_no'] = "";
		$arr['ext_id'] =  0;
	}
	return $arr;
}


/*
function get_material_temp_reserve_stock($dbcon, $product_id, $unit_id, $godown_id, $customer_id = "")
{
	    if (!empty($godown_id))
	    {
	        $where_godown = " and godown_id=" . $godown_id;
	    }
   
        $query1 = "select sum(base_stock) as base_addqty from tbl_reserve_stock where stock_status in (0,1) and stock_flage=1 and base_unit=" . $unit_id .  " " . $where_godown . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id;
        $result1 = $dbcon->query($query1);
        $row1 = mysqli_fetch_assoc($result1);

        $query2 = "select sum(convert_stock) as conv_addqty from tbl_reserve_stock where stock_status in (0,1) and base_unit!=convert_unit and stock_flage=1 " . $where_godown . " and company_id=" . $_SESSION['company_id'] . " and convert_unit=" . $unit_id . " and product_id=" . $product_id;
        $result2 = $dbcon->query($query2);
        $row2 = mysqli_fetch_assoc($result2);

        $query3 = "select sum(base_stock) as base_usedqty from tbl_reserve_stock where stock_status=0 and stock_flage=2 and base_unit=" . $unit_id . " " . $where_godown ." and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id;
        $result3 = $dbcon->query($query3);
        $row3 = mysqli_fetch_assoc($result3);

        $query4 = "select sum(convert_stock) as conv_usedqty from tbl_reserve_stock where stock_status=0 and base_unit!=convert_unit " . $where_godown . " and company_id=" . $_SESSION['company_id'] . " and stock_flage=2 and convert_unit=" . $unit_id . " and product_id=" . $product_id;
        $result4 = $dbcon->query($query4);
        $row4 = mysqli_fetch_assoc($result4);

        $query5 = "select sum(approve_base_stock) as base_addqty from tbl_reserve_stock where stock_status in (0,1) and stock_flage=1 and base_unit=" . $unit_id . " ". $where_godown . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id;
        $result5 = $dbcon->query($query5);
        $row5 = mysqli_fetch_assoc($result5);

        $query6 = "select sum(approve_convert_stock) as conv_addqty from tbl_reserve_stock where stock_status in (0,1) and base_unit!=convert_unit and stock_flage=1 "  . $where_godown .  " and company_id=" . $_SESSION['company_id'] . " and convert_unit=" . $unit_id . " and product_id=" . $product_id;
        $result6 = $dbcon->query($query6);
        $row6 = mysqli_fetch_assoc($result6);

        $res_qty = ($row1['base_addqty'] + $row2['conv_addqty']) - ($row3['base_usedqty'] + $row4['conv_usedqty']);
    
    //$j=$row1['base_addqty']."-1".$row2['conv_addqty']."-2".$row3['base_usedqty']."-3".$row4['conv_usedqty'];
    return $res_qty;
    //return $query1;
    //return $j;
    
}*/


function get_material_temp_reserve_stock($dbcon, $product_id, $unit_id, $godown_id, $customer_id = "",$p_id="",$stock_id=0)
{
	    if (!empty($godown_id))
	    {
	        $where_godown .= " and godown_id=" . $godown_id;
	    }
	    if (!empty($p_id))
	    {
	        $whrp_id .= " and p_id in(" . $p_id.")";
	    }
	    if (!empty($stock_id))
	    {
	        $whrp_id .= " and stock_id in(" . $stock_id.")";
	    }
   
        $query1 = "select IFNULL(sum(base_qty),0) as base_addqty from tbl_material_release_trn where status != 2  and base_unit=" . $unit_id .  " " . $where_godown . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id. $whrp_id;
        $result1 = $dbcon->query($query1);
        $row1 = mysqli_fetch_assoc($result1);

        $query2 = "select IFNULL(sum(conv_qty),0) as conv_addqty from tbl_material_release_trn where status != 2 and base_unit!=conv_unit  " . $where_godown . " and company_id=" . $_SESSION['company_id'] . " and conv_unit=" . $unit_id . " and product_id=" . $product_id.$whrp_id;
        $result2 = $dbcon->query($query2);
        $row2 = mysqli_fetch_assoc($result2);

      

        $res_qty = ($row1['base_addqty'] + $row2['conv_addqty']);
    
    return $res_qty;
}

function add_process_stock_mt_release($dbcon,$product_id,$unit_id,$stock_date,$ref_name,$ref_id,$godown_id,$stock_qty,$stock_flag,$branch_id,$perent_id,$reserve_id,$process_id,$customer_id="",$batch_id="",$batch_no="",$base_rate="",$conv_rate=""){
		$que_stock="select * from tbl_process_stock_trn where process_stock_id=".$perent_id;
		$re_stock=$dbcon->query($que_stock);
		$res_stock=brp_mysqli_fetch_assoc($re_stock);
	
		$que="select * from product_mst as ta where product_id=".$product_id;
		$rs_di=$dbcon->query($que);
		$re=brp_mysqli_fetch_assoc($rs_di);

		if($re['product_conv_unit']==$unit_id){
			$type="base_unit";
			$con_stock=$stock_qty;
			$base_stock=convert_stock_new($dbcon,$stock_qty,$product_id,$type);
		}else{
			$type="conv_unit";
			$base_stock=$stock_qty;
			$con_stock=convert_stock_new($dbcon,$stock_qty,$product_id,$type);
		}

			
		$info_stockadd['process_stock_date']		= date("Y-m-d",strtotime($stock_date));
		$info_stockadd['product_id']				= $product_id;
		$info_stockadd['process_id']				= $process_id;
		$info_stockadd['base_stock']				= $base_stock;
		$info_stockadd['base_unit']					= $re['product_base_unit'];
		$info_stockadd['conv_stock']				= $con_stock;
		$info_stockadd['conv_unit']					= $re['product_conv_unit'];
		$info_stockadd['stock_flage']				= $stock_flag;
		$info_stockadd['godown_id']					= $godown_id;
		$info_stockadd['ref_name']					= $ref_name;
		$info_stockadd['ref_id']					= $ref_id;
		$info_stockadd['stock_status']				= "0";
		$info_stockadd['perent_id']			= $perent_id;
		$info_stockadd['reserve_id']			= $reserve_id;
		$info_stockadd['batch_no']			= $batch_no;
		$info_stockadd['branch_id']			= $branch_id;
		
		$info_stockadd['cdate']						= date("Y-m-d H:i:s");
		$info_stockadd['user_id']					= $_SESSION['user_id'];
		$info_stockadd['company_id']				= $_SESSION['company_id'];

		$mfg_date = date("Y-m-d");
		$dt = get_exp_date_by_product($dbcon,$product_id,date("d-m-Y"));
		$exp_date = date('Y-m-d',strtotime($dt));

		$info_stockadd['mfg_date'] = $mfg_date;
		$info_stockadd['exp_date'] = $exp_date; 
	
	// if($stock_flag == '2'){
		$info_stockadd['process_base_rate']			= $res_stock['process_base_rate'];
		$info_stockadd['process_conv_rate']			= $res_stock['process_conv_rate'];
		$info_stockadd['process_stock_base_rate']			= $res_stock['process_stock_base_rate'];
		$info_stockadd['process_stock_conv_rate']			= $res_stock['process_stock_conv_rate'];
	// }	

		$process_stock_id=add_record('tbl_process_stock_trn',$info_stockadd, $dbcon);

	return $process_stock_id;
}

function count_workorder_shortage($dbcon)
{
	$qry = "SELECT group_concat(rp.rp_id) as rp_id FROM tbl_request_product as rp  where ( 1 AND rp.status = 3 and rp.workorder_type = 1 and rp.company_id='1' and rp.finish_status = 0) Group by rp.rp_pid,rp.bom_id,rp.branch_id ORDER BY rp.rp_id";
	$res = $dbcon->query($qry);
	$count = brp_mysqli_num_rows($res);
	return $count;
}

function get_batch_stock_new($dbcon, $pro_id, $unit_id,$godown_id, $branch_id, $batch_no, $customer_id)
{
    if (!empty($branch_id))
    {
        $branch_whre = " and qc.branch_id=" . $branch_id;
    }

    if (!empty($batch_no))
    {
        $batch_whre = " and qc.batch_no ='" . $batch_id."'";
        $batch_p_whre = " and qc.batch_no='" . $batch_id."'";
    }

    if ($customer_id != "" && $customer_id > 0)
    {
        $whr .= ' and qc.customer_id = "' . $customer_id . '" ';
    }
    else
    {
        $whr .= ' and qc.customer_id = 0 and qc.customer_id = "" ';
    }

    if($godown_id != "" && $godown_id != 0){
        $whr .= ' and qc.godown_id=' . $godown_id;
    }

    $query = 'SELECT pro.product_id,base_stock_add,base_stock_minus,con_stock_minus,con_stock_add FROM `product_mst` as pro 

    left join (select sum(qc.base_stock) as base_stock_add,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status=0 and stock_flage=1  and  qc.base_unit=' . $unit_id . ' ' . $branch_whre . ' ' . $whr . ' ' . $batch_whre . ' and qc.company_id=' . $_SESSION['company_id'] . ' 
      group by qc.product_id) as qc on qc.product_id=pro.product_id

      left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status=0 and stock_flage=2  and qc.base_unit=' . $unit_id . ' ' . $branch_whre . ' ' . $whr . ' ' . $batch_p_whre . '  and qc.company_id=' . $_SESSION['company_id'] . ' 
      group by qc.product_id) as qc1 on qc1.product_id=pro.product_id

      left join (select sum(qc.convert_stock) as con_stock_add,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status=0 and stock_flage=1  and qc.base_unit!=qc.convert_unit and qc.convert_unit=' . $unit_id . ' ' . $branch_whre . ' ' . $whr . ' ' . $batch_whre . '  and qc.company_id=' . $_SESSION['company_id'] . ' 
      group by qc.product_id) as qc2 on qc2.product_id=pro.product_id

      left join (select sum(qc.convert_stock) as con_stock_minus,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status=0 and stock_flage=2  and qc.base_unit!=qc.convert_unit and qc.convert_unit=' . $unit_id . ' ' . $branch_whre . ' ' . $whr . ' ' . $batch_p_whre . ' and qc.company_id=' . $_SESSION['company_id'] . ' 
      group by qc.product_id) as qc3 on qc3.product_id=pro.product_id

      where pro.product_id=' . $pro_id;
      $rows = brp_mysqli_fetch_assoc($dbcon->query($query));
      $stock = ($rows['base_stock_add'] + $rows['con_stock_add']) - ($rows['base_stock_minus'] + $rows['con_stock_minus']);

      return floatval($stock);
    //return floatval($pro_id);

  }

function reserve_stock_other_workorder($dbcon, $product_id, $unit_id, $reserve_id, $request_id, $complaint_id, $sales_order_trn_id, $branch_id, $is_store_approval, $p_id, $godown_id, $batch_id,$customer_id = "")
{


    if (!empty($reserve_id))
    {
        $rwhser = " and reserve_id=" . $reserve_id;
        $rwhser22 = " and ref_id=" . $reserve_id;
    }
    if (!empty($request_id))
    {
        $rwhser1 = " and request_id=" . $request_id;
    }
    if (!empty($complaint_id))
    {
        $rwhser2 = " and complaint_id=" . $complaint_id;
    }
    if (!empty($sales_order_trn_id))
    {
        $rwhser23 = " and sales_order_trn_id=" . $sales_order_trn_id;
    }
    if (!empty($branch_id))
    {
        $where_branch = " and branch_id=" . $branch_id;
    }

    if (!empty($p_id))
    {
        $where_branch = " and p_id !=" . $p_id;
    }

    if (!empty($godown_id))
    {
        $where_godown = " and godown_id=" . $godown_id;
    }
    if (!empty($batch_id))
    {
        $where_batch = " and stock_id in(" . $batch_id.")";
    }
    if(!empty($customer_id)){
        $where_customer = " and customer_id = " . $customer_id;
    }else{
        $where_customer = " and customer_id = 0 and customer_id =''";
    }

    if ($is_store_approval)
    {
        $query1 = "select IFNULL(sum(approve_base_stock),0) as base_addqty from tbl_reserve_stock where stock_status in (0,1) and stock_flage=1 and base_unit=" . $unit_id . " " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id . $where_customer;
        $result1 = $dbcon->query($query1);
        $row1 = mysqli_fetch_assoc($result1);

        $query2 = "select IFNULL(sum(approve_convert_stock),0) as conv_addqty from tbl_reserve_stock where stock_status in (0,1) and base_unit!=convert_unit and stock_flage=1 " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and convert_unit=" . $unit_id . " and product_id=" . $product_id . $where_customer;
        $result2 = $dbcon->query($query2);
        $row2 = mysqli_fetch_assoc($result2);

        $query3 = "select IFNULL(sum(approve_base_stock),0) as base_usedqty from tbl_reserve_stock where stock_status != 2 and stock_flage=2 and base_unit=" . $unit_id . " " . $rwhser22 . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id  . $where_customer;
        $result3 = $dbcon->query($query3);
        $row3 = mysqli_fetch_assoc($result3);

        $query4 = "select IFNULL(sum(approve_convert_stock),0) as conv_usedqty from tbl_reserve_stock where stock_status != 2 and base_unit!=convert_unit " . $rwhser22 . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and stock_flage=2 and convert_unit=" . $unit_id . " and product_id=" . $product_id  . $where_customer;
        $result4 = $dbcon->query($query4);
        $row4 = mysqli_fetch_assoc($result4);

        $res_qty = ($row1['base_addqty'] + $row2['conv_addqty']) - ($row3['base_usedqty'] + $row4['conv_usedqty']);
    }
    else
    {

        $query1 = "select IFNULL(sum(base_stock),0) as base_addqty from tbl_reserve_stock where stock_status in (0,1) and stock_flage=1 and base_unit=" . $unit_id . " " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id  . $where_customer;
        $result1 = $dbcon->query($query1);
        $row1 = mysqli_fetch_assoc($result1);

        $query2 = "select IFNULL(sum(convert_stock),0) as conv_addqty from tbl_reserve_stock where stock_status in (0,1) and base_unit!=convert_unit and stock_flage=1 " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and convert_unit=" . $unit_id . " and product_id=" . $product_id  . $where_customer;
        $result2 = $dbcon->query($query2);
        $row2 = mysqli_fetch_assoc($result2);

        $query3 = "select IFNULL(sum(base_stock),0) as base_usedqty from tbl_reserve_stock where stock_status != 2 and stock_flage=2 and base_unit=" . $unit_id . " " . $rwhser22 . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id  . $where_customer;
        $result3 = $dbcon->query($query3);
        $row3 = mysqli_fetch_assoc($result3);

        $query4 = "select IFNULL(sum(convert_stock),0) as conv_usedqty from tbl_reserve_stock where stock_status != 2 and base_unit!=convert_unit " . $rwhser22 . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and stock_flage=2 and convert_unit=" . $unit_id . " and product_id=" . $product_id  . $where_customer;
        $result4 = $dbcon->query($query4);
        $row4 = mysqli_fetch_assoc($result4);

        $query5 = "select IFNULL(sum(approve_base_stock),0) as base_addqty from tbl_reserve_stock where stock_status in (0,1) and stock_flage=1 and base_unit=" . $unit_id . " " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id  . $where_customer;
        $result5 = $dbcon->query($query5);
        $row5 = mysqli_fetch_assoc($result5);

        $query6 = "select IFNULL(sum(approve_convert_stock),0) as conv_addqty from tbl_reserve_stock where stock_status in (0,1) and base_unit!=convert_unit and stock_flage=1 " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . " and company_id=" . $_SESSION['company_id'] . " and convert_unit=" . $unit_id . " and product_id=" . $product_id  . $where_customer;
        $result6 = $dbcon->query($query6);
        $row6 = mysqli_fetch_assoc($result6);

        $res_qty = ($row1['base_addqty'] + $row2['conv_addqty']) - ($row3['base_usedqty'] + $row4['conv_usedqty']);
    }
    //$j=$row1['base_addqty']."-1".$row2['conv_addqty']."-2".$row3['base_usedqty']."-3".$row4['conv_usedqty'];
    return floatval($res_qty);
    //return $query1;
    //return $j;
    
}

function get_item_master_field($dbcon, $id)
{
    $str = '';
    $query = "Select * from tbl_item_master_field where item_master_field_status=0 and company_id=" . $_SESSION['company_id'];
    $rs_type = $dbcon->query($query);

    $str = '<option value="" >--Choose Item Field--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type))
    {
        $sel = '';
        if ($row['item_master_field_id'] == $id)
        {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['item_master_field_id'] . '" data-pcode="' . $row['item_master_field_db_name'] . '">' . $row['item_master_field'] . '</option>';
    }
    return $str;
}

function get_master_field($dbcon, $id)
{
    $str = '';
    $query = "Select * from tbl_master_field where master_field_status=0 and company_id=" . $_SESSION['company_id'];
    $rs_type = $dbcon->query($query);

    $str = '<option value="" >--Choose master Field--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type))
    {
        $sel = '';
        if ($row['master_field_id'] == $id)
        {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['master_field_id'] . '" data-pcode="' . $row['master_field_db_name'] . '">' . $row['master_field'] . '</option>';
    }
    return $str;
}

function get_field_value($dbcon, $id, $field_id)
{
    $str = '';
    $query = "Select * from tbl_item_master_field_value where item_master_field_value_status=0  and item_master_field_id=".$field_id." and company_id=" . $_SESSION['company_id'];
    $rs_type = $dbcon->query($query);

    ///$str = '<option value="" >--Choose Item Field--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type))
    {
        $sel = '';
        if ($row['item_master_field_value_id'] == $id)
        {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['item_master_field_value_id'] . '" data-pcode="' . $row['item_master_field_value'] . '">' . $row['item_master_field_value'] . '</option>';
    }
    return $str;
}
function get_master_field_value($dbcon, $id, $field_id)
{
    $str = '';
    $query = "Select * from tbl_master_field_value where master_field_value_status=0  and master_field_id=".$field_id." and company_id=" . $_SESSION['company_id'];
    $rs_type = $dbcon->query($query);

    ////$str = '<option value="" >--Choose Item Field--</option>';
	
    while ($row = brp_mysqli_fetch_assoc($rs_type))
    {
        $sel = '';
        if ($row['master_field_value_id'] == $id)
        {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['master_field_value_id'] . '" data-pcode="' . $row['master_field_value'] . '">' . $row['master_field_value'] . '</option>';
    }
    return $str;
}



function get_exp_date_by_product($dbcon,$product_id,$mfgdate)
{
	$get_dt_qry="select * from product_mst where product_id = '$product_id'";
	$getproduct_res=$dbcon->query($get_dt_qry);
	$getproduct_row=mysqli_fetch_assoc($getproduct_res);
	if($getproduct_row['self_life_days'] != '')
	{
		$exp_days = $getproduct_row['self_life_days'];
		$mfg_date = date("Y-m-d",strtotime($mfgdate));			
		$date = date("d-m-Y",strtotime("+".$exp_days." days", strtotime($mfg_date)));
		return $date;
							
	}
	else{
		$date = "";
		return $date;
	}
}


function count_reprocess_jobwork($dbcon)
{

    $branch_whre = "";
    if (!empty($_SESSION['branch_id']))
    {
        $branch_whre = " and branch_id=" . $_SESSION['branch_id'];
    }

   /* $query = "select count(p_id) as reprocess_count from tbl_allocate_re_process where  p_status !=2 AND pr_process_type= 2 " . $branch_whre . " and company_id=" . $_SESSION['company_id'];

    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_cust);
  
    return $rel['reprocess_count'];*/
	$pendingjobwork_count = 0;

    $job_penapproval_sql="SELECT p_id as pid,p_qty FROM `tbl_allocate_re_process` as trn
				WHERE trn.pr_process_type = 2 and trn.p_status in (0,1) and trn.company_id=".$_SESSION['company_id'].$branch_whre;
	$job_pen_resulr=$dbcon->query($job_penapproval_sql);
				
	 while($job_pen_approval=mysqli_fetch_assoc($job_pen_resulr)){
	 	$q = "SELECT IFNULL(sum(trn.product_base_qty),0) as used_qty FROM `tbl_job_work_sub_trn` as trn  
			left join tbl_job_work_trn as job_work_trn on job_work_trn.job_work_trn_id =  trn.job_work_trn_id
			where job_work_sub_trn_status = 0 and trn.is_reprocess = 1 and p_id IN (".$job_pen_approval['pid'].")  and job_work_trn.job_work_trn_status in (0,1)";
		$job_trn=$dbcon->query($q);
		$job_trn_result = brp_mysqli_fetch_assoc($job_trn);
		$jobwork_working_qty = 0;
		$jobwork_working_qty = $job_trn_result['used_qty'];
		$qtp=$job_pen_approval['p_qty'];
		if($qtp - $jobwork_working_qty > 0){
			if($qtp>0){
				$pendingjobwork_count++;
				//$pendingjobwork_count=$pendingjobwork_count." - ".$job_pen_approval['pid'];
			}
		}
	 }

	 return $pendingjobwork_count;
   
}

function load_available_stock_godown($dbcon,$product_id,$branch_id,$godown_id=0){
	$whr = "";
	if($branch_id > 0){
		$whr .= " AND branch_id = " . $branch_id;
	}
	$query = "SELECT group_concat(DISTINCT godown_id) as godown_id FROM tbl_stock_trn where stock_status != 2 AND stock_flage = 1 AND  cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and  product_id = " . $product_id . $whr;

	$result = $dbcon->query($query);

	$str = "<option value=''>Select Godown</option>";	

	if(brp_mysqli_num_rows($result) > 0){
		$row = brp_mysqli_fetch_assoc($result);
		$gd_q = " SELECT gd_id,gd_name FROM mst_godown where g_status != 2 AND gd_id in(".$row['godown_id'].")";
		$result1 = $dbcon->query($gd_q); 
		while($rel = brp_mysqli_fetch_array($result1)){
			$sel = '';
            if ($rel['gd_id'] == $godown_id)
            {
                $sel = "selected='selected'";
            }
			$str .= "<option ". $sel ." value='".$rel['gd_id']."'>". $rel['gd_name'] ."</option>";	
		}
	}

	return $str;

}

function get_godown_name($dbcon, $godown_id)
{
    $query = "SELECT  gd_name FROM mst_godown where gd_id=" . $godown_id;
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_assoc($rs_cust);
    return $rel['gd_name'];
}


function release_stock_action_modal($dbcon,$p_id,$rel_qty,$rel_conv_qty,$previous_process_id,$product_id,$main_product,$request_id){
	$qry = "select product_base_unit,product_conv_unit
			 			from product_mst where product_id = " . $product_id;
	$pr_res=$dbcon->query($qry);
	$pro_res =brp_mysqli_fetch_array($pr_res);

	if($previous_process_id == 0){
					$query1 = "select *	from tbl_reserve_stock where  stock_status !=2 and stock_flage = 1 and p_id = " . $p_id . " and product_id = " . $product_id . " and request_id = " . $request_id;  /// request_id check 

					$result1=$dbcon->query($query1);

					$stock = $rel_qty;
					$stock_conv = $rel_conv_qty;

					while($res =brp_mysqli_fetch_array($result1)){
						$approve_base_stock = 0;
						$approve_convert_stock =0;

						if($res['approve_base_stock'] != "" && $res['approve_base_stock'] > 0){
							$approve_base_stock = $res['approve_base_stock'];
						} 

						if($res['approve_convert_stock'] != "" && $res['approve_convert_stock'] > 0){
							$approve_convert_stock = $res['approve_convert_stock'];
						} 


						$bstock = $res['base_stock'] - $approve_base_stock;
						$cstock = $res['convert_stock'] - $approve_convert_stock;
						if($stock > 0){
							if($bstock > 0){
								$remaining_qty = 0;
								$remaining_conv_qty = 0;
								if($bstock <= $stock){
									$remaining_qty = $bstock;
								}else{
									$remaining_qty = $stock;
								}

								$stock = $stock - $remaining_qty;

								if($pro_res['product_base_unit'] != $pro_res['product_conv_unit']){
									$remaining_conv_qty = convert_stock($dbcon,$remaining_qty,$product_id,"conv_unit");
								}else{
									$remaining_conv_qty = $remaining_qty;
								}
								$approve_base_stock = $approve_base_stock + $remaining_qty;
								$approve_convert_stock = $approve_convert_stock + $remaining_conv_qty;

								$res_stock['approve_base_stock'] = $approve_base_stock;
								$res_stock['approve_convert_stock'] = $approve_convert_stock;
								$table='tbl_reserve_stock';$tableid='reserve_id';
								update_record($table, $res_stock, $tableid."=".$res['reserve_id'], $dbcon);
							}
						}
					}
				}else{
					$query1 = "select *	from  tbl_process_reserve_stock where  stock_status !=2 and stock_flage = 1 and p_id = " . $p_id . " and product_id = " . $main_product;

					$result1=$dbcon->query($query1);

					$stock = $rel_qty;
					$stock_conv = $rel_conv_qty;

					while($res =brp_mysqli_fetch_array($result1)){
						$approve_base_stock = 0;
						$approve_convert_stock =0;

						if($res['approve_base_stock'] != "" && $res['approve_base_stock'] > 0){
							$approve_base_stock = $res['approve_base_stock'];
						} 

						if($res['approve_convert_stock'] != "" && $res['approve_convert_stock'] > 0){
							$approve_convert_stock = $res['approve_convert_stock'];
						} 


						$bstock = $res['base_stock'] - $approve_base_stock;
						$cstock = $res['conv_stock'] - $approve_convert_stock;
						if($stock > 0){
							if($bstock > 0){
								$remaining_qty = 0;
								$remaining_conv_qty = 0;
								if($bstock <= $stock){
									$remaining_qty = $bstock;
								}else{
									$remaining_qty = $stock;
								}

								$stock = $stock - $remaining_qty;

								if($pro_res['product_base_unit'] != $pro_res['product_conv_unit']){
									$remaining_conv_qty = convert_stock($dbcon,$remaining_qty,$main_product,"conv_unit");
								}else{
									$remaining_conv_qty = $remaining_qty;
								}
								$approve_base_stock = $approve_base_stock + $remaining_qty;
								$approve_convert_stock = $approve_convert_stock + $remaining_conv_qty;

								$res_stock['approve_base_stock'] = $approve_base_stock;
								$res_stock['approve_convert_stock'] = $approve_convert_stock;
								$table='tbl_process_reserve_stock';
					$tableid='process_reserve_id';

					update_record($table, $res_stock, $tableid."=".$res['process_reserve_id'], $dbcon);
							}
						}
					}
					/*$res =brp_mysqli_fetch_array($result1);

					$approve_base_stock = 0;
					$approve_convert_stock =0;

					if($res['approve_base_stock'] != "" && $res['approve_base_stock'] > 0){
						$approve_base_stock = $res['approve_base_stock'];
					} 

					if($res['approve_convert_stock'] != "" && $res['approve_convert_stock'] > 0){
						$approve_convert_stock = $res['approve_convert_stock'];
					} 

					$approve_base_stock = $approve_base_stock + $rel_conv_qty;
					$approve_convert_stock = $approve_convert_stock + $rel_conv_qty;

					$res_stock['approve_base_stock'] = $approve_base_stock;
					$res_stock['approve_convert_stock'] = $approve_convert_stock;

					$table='tbl_process_reserve_stock';
					$tableid='process_reserve_id';

					update_record($table, $res_stock, $tableid."=".$res['process_reserve_id'], $dbcon);*/

				}

	/*$bom="SELECT group_concat(rpro.p_ref_id ORDER BY rpro.p_ref_id) AS views FROM `tbl_allocate_process` as rpro
				WHERE rpro.p_status!=2 AND rpro.p_id = ".$p_id;
			$resul=$dbcon->query($bom);
			$rel1=brp_mysqli_fetch_assoc($resul);
			
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			
			$bom1="SELECT rpro.*,tc.cat_name,pro.product_name,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name FROM `tbl_request_product` as rpro
			left join product_mst as pro on pro.product_id=rpro.rp_pid
			left join tbl_category as tc on pro.product_category=tc.cat_id
			left join unit_mst as bunit on bunit.unitid=rpro.process_unit
			left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
			WHERE rpro.status!=2 AND rpro.perent_id in (".$rel1['views'].") group by rpro.rp_pid" ;
			$result=$dbcon->query($bom1);

			$i=1;
			while($rel=brp_mysqli_fetch_assoc($result)){
				$o_qty=convert_stock($dbcon,$rel["req_qty_one"],$rel['rp_pid'],"base_unit");
				$rel["req_qty_one"]=round($rel["req_qty_one"],6);
				echo 'pen'. $POST['max_start_qty'];
				$o_qty=round($o_qty,6);
				//$o_qty=round($rel["req_qty_one"],6);
				$total_req_qty=$POST['pending_qty']*$o_qty;
				$total_req_qty=round($total_req_qty,4);
				$used_qty=$POST['max_start_qty']*$o_qty;
				$used_qty=round($used_qty,4);
				$cur_stock=reserve_stock($dbcon,$rel['rp_pid'],$rel['process_unit'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,0);
				// echo '-->'.$cur_stock . "</br>";
				$cur_stock=round($cur_stock,4);
				$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
				//$cur_stock=reserve_stock($dbcon,$rel_n1['rp_pid'],$rel_n1['process_unit'],"",$rel_n1['rp_id']);
				
				$product_id = $rel['rp_pid'];
				$req_qty_one = $o_qty;

				$i++;
				if($previous_process_id == 0){
					$query1 = "select *	from tbl_reserve_stock where p_id = " . $p_id . " and product_id = " . $product_id;

					$result1=$dbcon->query($query1);
					$res =brp_mysqli_fetch_array($result1);

					$approve_base_stock = 0;
					$approve_convert_stock =0;

					if($res['approve_base_stock'] != "" && $res['approve_base_stock'] > 0){
						$approve_base_stock = $res['approve_base_stock'];
					} 

					if($res['approve_convert_stock'] != "" && $res['approve_convert_stock'] > 0){
						$approve_convert_stock = $res['approve_convert_stock'];
					} 

					$approve_base_stock = $approve_base_stock + ($rel_qty * $req_qty_one);
					$approve_convert_stock = $approve_convert_stock + $rel_qty;

					$res_stock['approve_base_stock'] = $approve_base_stock;
					$res_stock['approve_convert_stock'] = $approve_convert_stock;

					$table='tbl_reserve_stock';$tableid='reserve_id';
					update_record($table, $res_stock, $tableid."=".$res['reserve_id'], $dbcon);
				}else{
					$query1 = "select *	from  tbl_process_reserve_stock where p_id = " . $p_id . " and product_id = " . $product_id;

					$result1=$dbcon->query($query1);
					$res =brp_mysqli_fetch_array($result1);

					$approve_base_stock = 0;
					$approve_convert_stock =0;

					if($res['approve_base_stock'] != "" && $res['approve_base_stock'] > 0){
						$approve_base_stock = $res['approve_base_stock'];
					} 

					if($res['approve_convert_stock'] != "" && $res['approve_convert_stock'] > 0){
						$approve_convert_stock = $res['approve_convert_stock'];
					} 

					$approve_base_stock = $approve_base_stock + ($rel_qty * $req_qty_one);
					$approve_convert_stock = $approve_convert_stock + $rel_qty;

					$res_stock['approve_base_stock'] = $approve_base_stock;
					$res_stock['approve_convert_stock'] = $approve_convert_stock;

					$table=' tbl_process_reserve_stock';$tableid='process_reserve_id	';
					update_record($table, $res_stock, $tableid."=".$res['process_reserve_id	'], $dbcon);
				}
			}*/
}


function store_release_logs($dbcon,$store_request_id,$store_release_id,$rel_qty,$pid,$product_id,$process_id,$remark,$req_user_id,$branch_id){
		$qry = "select * from tbl_store_request where store_request_id = ". $store_request_id;
		$result =$dbcon->query($qry);
		$res=brp_mysqli_fetch_array($result);
		$qty = 0;
		// var_dump('rel q : ' . $rel_qty);
		$req_qty = $res['base_qty'];
		
		if($res['release_qty'] != "" && $res['release_qty'] > 0){
			$qty = $res['release_qty'];
		}
		
		$qty = $qty + $rel_qty;

		/*if(($req_qty - $qty) == 0){
			$update_qty['store_request_status'] = 1;
		}*/

		$update_qty['release_qty'] = $qty;
		$table='tbl_store_request';$tableid='store_request_id';
		update_record($table, $update_qty, $tableid."=".$store_request_id, $dbcon);

		$logs['store_request_id'] = $store_request_id;
		$logs['store_release_id'] = $store_release_id;
		$logs['approve_remark'] = $remark;
		$logs['approve_status'] = 1;
		$logs['store_aprv_log_status'] = 0;
		$logs['p_id'] = $res['p_id'];
		$logs['rp_id'] = $product_id;
		$logs['release_qty'] = $rel_qty;
		$logs['request_user_id'] = $req_user_id;
		$logs['process_id'] = $process_id;
		$logs['cdate']		= date("Y-m-d H:i:s");
		$logs['user_id']	= $_SESSION['user_id'];
		$logs['company_id']	= $_SESSION['company_id'];
		$logs['branch_id']	= $branch_id;

  		$req_id = add_record('tbl_store_request_aprv_log',$logs, $dbcon);
}

function get_reserve_stock_deallocate_qty($dbcon, $sales_ordertrn_id)
{
    $str = '';
    $query = "SELECT unit_id, IFNULL(SUM(de_allocate_qty),0) as de_allocate_qty FROM tbl_so_stock_deallocate_trn AS dl 
    LEFT JOIN tbl_so_stock_deallocate as so ON so.de_allo_id = dl.de_allo_id
    WHERE sales_ordertrn_id = ".$sales_ordertrn_id." AND dl.status = 0 AND so.approve_status = 1";
    $result = $dbcon->query($query);
    $cnt = brp_mysqli_num_rows($result);
    if ($cnt > 0)
    {
        while ($res = brp_mysqli_fetch_assoc($result))
        {
        	if($res['de_allocate_qty'] > 0){
        		$unitname = getunitname($dbcon, $res['unit_id']);
	            $str .= "<tr>
	            <td>Deallocate Stock</td>
	            <td>" . $res['de_allocate_qty'] . " " . $unitname . "</td>
	            </tr>";	
        	}
        }
    }
    return $str;
}
function get_party_for_paking($dbcon,$cust_id)
{
    $str = '';
    $query = "SELECT so.l_name,so.l_id FROM tbl_sales_order AS dl 
    LEFT JOIN tbl_ledger as so ON so.l_id = dl.cust_id
    WHERE dl.sales_order_status =0 and dl.order_accept_status=1 AND dl.invoice_status = 0 group by dl.cust_id";
    $result = $dbcon->query($query);
    $str = "<option value=''> Select Company </option>";
    $cnt = brp_mysqli_num_rows($result);
    if ($cnt > 0)
    {
        while ($res = brp_mysqli_fetch_assoc($result))
        {
        	if($res['l_id']==$cust_id){
        		$sel = 'selected="selected"';
        	}
        	$str .= "<option ".$sel." value=".$res['l_id'].">". $res['l_name'] ."</option>";
        }
    }
    return $str;
}


function auto_add_next_process_material_entry($dbcon,$product_qty,$unit_id,$previous_process_id,$new_p_id,$next_process_id,$next_process_type,$next_process_priority,$workorder_process_id,$auto_store_relese=0,$batch_id=0){

	 $query = "select extra_stock, extra_stock_material_reserve, p_product_id, p_ref_id, branch_id, product_version, batch_no,process_id, batch_process_start_time from tbl_allocate_process as allo where allo.p_id=".$previous_process_id ;
		$result=$dbcon->query($query);
		$row=brp_mysqli_fetch_array($result);


	if($auto_store_relese){

			if($batch_id > 0){
				$batch_qry = "select grn_godown,batch_no,grn_trn_id from tbl_batch_data where batch_id = " . $batch_id;
				$batch_res = $dbcon->query($batch_qry);
				$batch_row = brp_mysqli_fetch_assoc($batch_res);

				$store_godown_id = $batch_row['grn_godown'];

				$btmp_q1 = "SELECT new_godown_id FROM tbl_temp_qc where status = 0 AND batch_id = " .$batch_id . " and grn_trn_id = " . $batch_row['grn_trn_id'];

				$btmp_q1_res = $dbcon->query($btmp_q1);
				$btmp_q1_cnt = brp_mysqli_num_rows($btmp_q1_res);
				if($btmp_q1_cnt > 0){
					$btmp_q1_rw = brp_mysqli_fetch_assoc($btmp_q1_res); 
					$store_godown_id = $btmp_q1_rw['new_godown_id'];
				}


			$pr_query = "select * from product_mst where product_id = " . $row['p_product_id'];
			$pr_result = $dbcon->query($pr_query);
			$pr_row = brp_mysqli_fetch_assoc($pr_result);

			$info1['release_no']		= load_common_no($dbcon,RELEASE_MATERIAL);
			$info1['release_date']		= date('Y-m-d');
			$info1['to_godown_id']		= ON_FLOOR_GODOWN;
			$info1['to_user_id']		= $_SESSION['user_id'];;	
			$info1['product_id']		= $row['p_product_id'];
			$info1['release_qty']		= $product_qty;
			$info1['release_unit']		= $pr_row['product_base_unit'];
			$info1['process_id']		= $next_process_id;	
			$info1['p_id']				= $new_p_id;	
			$info1['cdate']				= date('Y-m-d H:i:s');
			$info1['user_id']			= $_SESSION['user_id'];	
			$info1['company_id']		= $_SESSION['company_id'];	
			
			$material_id = add_record('tbl_material_release',$info1, $dbcon);

			if($material_id){
				update_common_no($dbcon,RELEASE_MATERIAL);
			

					$rel_info['material_id'] =  $material_id;;
					$rel_info['product_id'] = $row['p_product_id'];
					$rel_info['p_id'] = $new_p_id;
					$rel_info['release_qty'] = $product_qty;
					$rel_info['pending_qty'] = $product_qty;
					$rel_info['start_qty'] = 0;
					$rel_info['release_unit'] = $pr_row['product_base_unit'];
					// $rel_info['rp_id'] = ;

					$start_stop_id = add_record('tbl_start_stop_production',$rel_info, $dbcon);

					
					$query_dstock = "select i.*,(cast(base_stock AS DECIMAL(10,5)) - IFNULL((select sum(base_stock) from tbl_process_reserve_stock where stock_status = 0  and   p_id=". $new_p_id ." and stock_flage = 2 and perent_id = i.process_reserve_id),0)) as pending_base_stock,(cast(conv_stock AS DECIMAL(10,5)) - IFNULL((select sum(conv_stock) from tbl_process_reserve_stock where stock_status = 0 and stock_flage = 2 and perent_id = i.process_reserve_id),0)) as pending_conv_stock from tbl_process_reserve_stock as i where stock_status=0 and stock_flage=1 and i.product_id=".$row['p_product_id']." and p_id = " . $new_p_id;

						$trn_info['product_id']		= $row['p_product_id'];
						$trn_info['godown_id']		= $store_godown_id;	
						$trn_info['to_godown_id']	= ON_FLOOR_GODOWN;
						
						$trn_info['release_status'] = 0;
						$trn_info['batch_no'] = 1;
						$trn_info['rp_id']			= $row['p_ref_id'];
						$trn_info['parent_rp_id']	= $row['p_ref_id'];
						$trn_info['p_id']			=  $new_p_id;
					
						$trn_info['cdate']				= date('Y-m-d H:i:s');
						$trn_info['user_id']			= $_SESSION['user_id'];	
						$trn_info['company_id']		= $_SESSION['company_id'];	
						$trn_info['base_unit']		= $pr_row['product_base_unit'];
						$trn_info['conv_unit']		= $pr_row['product_conv_unit'];
					
						$trn_info['status'] = 0;
						$trn_info['material_id'] = $material_id;
						$trn_info['batch_no'] = $batch_row['batch_no'];
						
						$trn_info['to_user_id'] = $_SESSION['user_id'];;
						$trn_info['start_stop_id'] = $start_stop_id;
							
					$release_qty = $product_qty;
					// var_dump($release_qty);
					$result_dstock=$dbcon->query($query_dstock);
					while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
						// var_dump('--->'.$release_qty);
						$trn_info['batch_no']		= $batch_row['batch_no'];	

						if($previous_process_id > 0){
							$trn_info['godown_id']		= $row_dstock['godown_id'];	
						}

						$pending_stock=$row_dstock['pending_base_stock'];	
						
						if($release_qty>0){
							if($pending_stock>=$release_qty){
								$rqty=$release_qty;
								$release_qty=$release_qty-$release_qty;
							}else{
								$rqty=$pending_stock;
								$release_qty=$release_qty-$pending_stock;
							}
					
							$type="conv_unit";
							$base_stock=$rqty;
							$con_stock=convert_stock_new($dbcon,$rqty,$row['p_product_id'],$type);
							
							$trn_info['base_qty']		= $base_stock;
							$trn_info['conv_qty']		= $con_stock;
							$trn_info['stock_id']		= $row_dstock['process_stock_id'];
							
							$inserpoid=add_record('tbl_material_release_trn',$trn_info, $dbcon);
							release_stock_action_modal_godown_wise($dbcon,$new_p_id,$base_stock,$base_stock,$previous_process_id, $row['p_product_id'], $row['p_product_id'],$material_id,ON_FLOOR_GODOWN,$trn_info['godown_id']);
						}
						}
					}
					}
		}
}

function get_production_filter_product($dbcon,$product_id,$process_id) {
	$str='';
	$where = "";
	if(!empty($process_id)){
		$where = " and ap.process_id = " . $process_id;
	}
	$query="select p.product_id,p.product_name,p.product_icode from tbl_allocate_process as ap LEFT JOIN product_mst as p on p.product_id = ap.p_product_id
	where ap.p_status IN (0,1) AND p.product_status=0 and p.company_id in(0,$_SESSION[company_id]) " .$where . " group by product_id";
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose Product</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['product_id']==$product_id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['product_id'].'">'.$rel['product_name']."-- ( ".$rel['product_icode'].')'.'</option>';
	}
	return $str;
}
function get_production_repocess_filter_product($dbcon,$product_id,$process_id) {
	$str='';
	$where = "";
	if(!empty($process_id)){
		$where = " and ap.process_id = " . $process_id;
	}
	$query="select p.product_id,p.product_name,p.product_icode from tbl_allocate_re_process as ap LEFT JOIN product_mst as p on p.product_id = ap.p_product_id
	where ap.p_status IN (0,1) AND p.product_status=0 and p.company_id in(0,$_SESSION[company_id]) " .$where . " group by product_id";
	$rs_product=$dbcon->query($query);
	$str = '<option value="">Choose Product</option>';
	while($rel=mysqli_fetch_assoc($rs_product))
	{
		$sel='';
		if($rel['product_id']==$product_id)
		{ $sel ="selected='selected'"; }
		$str .= '<option '.$sel.' value="'.$rel['product_id'].'">'.$rel['product_name']."-- ( ".$rel['product_icode'].')'.'</option>';
	}
	return $str;
}


 function grn_convert_stock($dbcon, $stock, $grn_trn_id, $type)
    {
    //var_dump($stock);
        $que_po = "select unit_id,product_conv_unit,product_qty,product_conv_qty from tbl_grn_trn where grn_trn_id=" . $grn_trn_id;
        $resi_grn = $dbcon->query($que_po);
        $re = brp_mysqli_fetch_assoc($resi_grn);
        if ($re['unit_id'] != $re['product_conv_unit'])
        {
            if ($type == "base_unit")
            {
                $ret_qty = ($stock / $re['product_conv_qty']) * $re['product_qty'];
            }
            else
            {
                $ret_qty = ($stock / $re['product_qty']) * $re['product_conv_qty'];
            }
        }
        else
        {
            $ret_qty = $stock;
        }
        return $ret_qty;

    }

function get_previous_taskid($dbcon, $inquiry_id){
    $query  = "select max(task_id) as prev_taskid, quotation_id from tbl_task where task_status=0 and inquiry_id =".$inquiry_id;
    $result = $dbcon->query($query);

    $row = brp_mysqli_fetch_array($result); 

    return $row;
}


function get_previous_taskid_postcrm($dbcon, $cust_id){
    $query  = "select max(task_id) as prev_taskid from tbl_task where task_status=0 and entry_type=3 and cust_id =".$cust_id;
    $result = $dbcon->query($query);

    $row = brp_mysqli_fetch_array($result); 

    return $row;
}

function task_on_quotation_delete($dbcon, $task_id){
	$query = "select * from tbl_quotation where quotation_status=0 and quotation_task_id=".$task_id;
	$result = $dbcon->query($query);
	$row = brp_mysqli_fetch_array($result);
	
	$info['quotation_status']	= 2;
	$updateid=update_record('tbl_quotation', $info, "quotation_id=".$row['quotation_id'], $dbcon);
	
	$infotrn['quot_trn_status']	= 2;
	$updatetrnid=update_record('tbl_quotation_trn', $infotrn, "quotation_id=".$row['quotation_id'], $dbcon);

	$infoprojecttrn['quotation_projecttrn_status']  = 2;
	$updateprojecttrnid = update_record('tbl_quotation_project_trn', $infoprojecttrn, "quotation_id=".$row['quotation_id'], $dbcon);

	$prev_quotation_id=$row['prev_quotation_id'];
		
	if($prev_quotation_id){
		$upd_prev_qt_sts=$dbcon->query("update tbl_quotation set revise_status=0 where quotation_id=".$prev_quotation_id);
	}
		
	$log_entry=common_log_entry($dbcon,"quotation_add",3,"tbl_quotation",$POST['quotation_id']);
}

function get_salesorder_batchno($dbcon, $sales_ordertrn_id){
	$query = "select st.batch_no,rs.base_stock from tbl_sales_ordertrn as strn
	left join product_mst as pmst on pmst.product_id = strn.product_id
	left join tbl_reserve_stock as rs on rs.sales_order_trn_id = strn.sales_ordertrn_id
	left join tbl_stock_trn as st on st.stock_id = rs.stock_id
	where rs.stock_status=0 and rs.stock_flage=1 and rs.ref_name='wo_allocate' and pmst.batch_wise_stock_manage=1 and strn.sales_ordertrn_id=".$sales_ordertrn_id;
	$result = $dbcon->query($query);
	$str='';
	while($row = brp_mysqli_fetch_array($result)){
		$str .= '<strong>batch no :- </strong>'.$row['batch_no'].' <strong>Qty :-</strong> '.$row['base_stock'].'<br>'; 
	}
	return $str;
}

function get_terms_printname_wise($dbcon, $quot_ref_tc_id, $print_name,$quot_type){
	$str ='';
	//var_dump($quot_ref_tc_id);
	$query = "select * from tbl_terms_condition where tc_status=0 and find_in_set(".$quot_type.",tc_for) and print_name='".$print_name."' order by tc_priority";
	$result = $dbcon->query($query);
	$str .='<option value="">Choose terms</option>';
	while($row = brp_mysqli_fetch_array($result)){
		$sel='';
		if($quot_ref_tc_id==$row['tc_id']){
			$sel = 'selected="selected"';
		}
		/*var_dump($sel);
		var_dump($quot_ref_tc_id.' '.$row['tc_id']);*/
		$str .='<option '.$sel.' value="'.$row['tc_id'].'">'.$row['tc_name'].'</option>';
	}
	return $str;
}


function grn_purchase_add_stock($dbcon,$product_id,$unit_id,$stock_date,$ref_name,$ref_id,$godown_id,$stock_qty,$stock_flag,$branch_id,$perent_id,$reserve_id,$customer_id="",$batch_id="",$batch_no="",$base_rate="",$conv_rate="",$workorder_id=0){

	$que_stock="select * from tbl_grn_trn where stock_id=".$perent_id;
	$re_stock=$dbcon->query($que_stock);
	$res_stock=brp_mysqli_fetch_assoc($re_stock);

	if(brp_mysqli_num_rows($re_stock) > 0){
		if($customer_id == ""){
			$customer_id = $res_stock['customer_id'];
		}

		if($batch_id == ""){
			$batch_id = $res_stock['batch_id'];

		}
		if($batch_no == ""){
			$batch_no = $res_stock['batch_no'];
		}
	}

	if($stock_flag == '1' && $batch_id != "" && $batch_id > 0){
			$bt_qry = " SELECT mfg_date,exp_date FROM tbl_batch_data WHERE batch_id = " . $batch_id;
			$bt_row = brp_mysqli_fetch_assoc($dbcon->query($bt_qry));

			$info_gen['mfg_date'] = $bt_row['mfg_date'];
			$info_gen['exp_date'] = $bt_row['exp_date'];
		}else if($stock_flag == '1'){
			$info_gen['mfg_date'] = date("Y-m-d");
			$dt = get_exp_date_by_product($dbcon,$_POST['product_id'],date("d-m-Y"));
			$info_gen['exp_date'] = date('Y-m-d',strtotime($dt));	
		}

	$que="select * from product_mst as ta where product_id=".$product_id;
	$rs_di=$dbcon->query($que);
	$re=brp_mysqli_fetch_assoc($rs_di);

	if($re['product_conv_unit'] != $re['product_base_unit']){
		if(!empty($batch_id)){
			$s_que="select * from tbl_batch_data where batch_id=".$batch_id;
			$s_rs_di=$dbcon->query($s_que);
			$s_re=brp_mysqli_fetch_assoc($s_rs_di);
			
			if($re['product_conv_unit']==$unit_id){
				$type="base_unit";
				$con_stock=$stock_qty;
				// $base_stock=convert_stock_new($dbcon,$stock_qty,$product_id,$type);
				$base_stock = ($con_stock/$s_re['conv_qty']) * $s_re['base_qty'];
			}else{
				$type="conv_unit";
				$base_stock=$stock_qty;
				// $con_stock=convert_stock_new($dbcon,$stock_qty,$product_id,$type);
				$con_stock = ($base_stock/$s_re['base_qty']) * $s_re['conv_qty'];
			}
		}else{
			if($re['product_conv_unit']==$unit_id){
				$type="base_unit";
				$con_stock=$stock_qty;
				$base_stock=convert_stock_new($dbcon,$stock_qty,$product_id,$type);
			}else{
				$type="conv_unit";
				$base_stock=$stock_qty;
				$con_stock=convert_stock_new($dbcon,$stock_qty,$product_id,$type);
			}
		}
	}else{
		$base_stock=$stock_qty;
		$con_stock=$stock_qty;
	}
	
	
	$info_gen['stock_date']			= date('Y-m-d',strtotime($stock_date));
	$info_gen['product_id']			= $product_id;
	$info_gen['base_unit']			= $re['product_base_unit'];
	$info_gen['base_stock']			= $base_stock;
	$info_gen['convert_unit']		= $re['product_conv_unit'];
	$info_gen['convert_stock']		= $con_stock;
	$info_gen['stock_flage']		= $stock_flag;
	$info_gen['godown_id']			= $godown_id;
	$info_gen['ref_name']			= $ref_name;
	$info_gen['ref_id']				= $ref_id;
	$info_gen['perent_id']			= $perent_id;
	$info_gen['reserve_id']			= $reserve_id;
	$info_gen['customer_id'] 		= $customer_id;
	$info_gen['batch_id'] 			= $batch_id; 
	$info_gen['batch_no']			= $batch_no;
	
	$info_gen['base_rate']			= $base_rate;
	$info_gen['conv_rate']			= $conv_rate;
	$info_gen['workorder_id']		= $workorder_id;

	$info_gen['user_id']			= $_SESSION['user_id'];
	$info_gen['cdate']				= date("Y-m-d H:i:s");
	$info_gen['company_id']			= $_SESSION['company_id'];

	if($stock_flag == '2'){
		$info_gen['base_rate']			= $res_stock['base_rate'];
		$info_gen['conv_rate']			= $res_stock['conv_rate'];
	}

	// $batch_no = get_batch_no($dbcon,$product_id);

	// $info_gen['batch_no'] = $batch_no;

	// /var_dump($info_gen);
	$inserid_gen=add_record("tbl_stock_trn", $info_gen, $dbcon,$branch_id);

	$remark = 'Stock Arrived<br>'.$re['product_name'].'<br>Stock: '.$base_stock;

	$infotask['task_type_id']=14;
	$infotask['task_rel_id']=1;
	$infotask['task_remark']=$remark;
	$infotask['task_priority_id']=1;
	$infotask['assign_user_ids']=$_SESSION['user_id'];
	$infotask['task_alert_id']=2;
	$infotask['entry_type']=1;
	$infotask['show_user_ids']=$_SESSION['user_id'];
	$infotask['task_due_date']=date("Y-m-d H:i:s");
	$infotask['create_date']=date("Y-m-d H:i:s");
	$infotask['alert_date_time']=date("Y-m-d H:i:s");
	$infotask['task_completion_date']=date("Y-m-d H:i:s");
	$infotask['user_id']		= $_SESSION['user_id'];
	$infotask['cdate']			= date("Y-m-d H:i:s");
	$infotask['company_id']		= $_SESSION['company_id'];

	if($inserid_gen){
		// update_batch_no($dbcon,$product_id);
		// $inserid_task=add_record("tbl_task", $infotask, $dbcon,$branch_id);
	}
	if($stock_flag==1){
		//add_request_reserve_stock($dbcon,$product_id);
		//rquest_qty_deduct($dbcon,$product_id,$stock_qty);
	}else if($stock_flag==2){
		//deduct_remove_stock($dbcon,$product_id,$info_gen['base_unit'],$info_gen['base_stock'],$info_gen['convert_unit'],$ref_name,$ref_id);
	}
	
	return $inserid_gen;
}
function get_pending_po_quotation_cnt($dbcon){
	$query = "SELECT COUNT(apo.approve_indent_id) as cnt FROM approve_indent as apo 
	left join tbl_request_product as po on po.rp_id=apo.rp_id 
	left join tbl_set_main_process as spro on spro.sp_id=po.sp_id 
	left join branch_mst as bms on bms.branch_id=apo.branch_id 
	left join product_mst as pmst on pmst.product_id=po.rp_pid 
	left join tbl_drawing as dr on dr.drawing_id = pmst.drawing_id 
	left join tbl_category as tc on pmst.product_category=tc.cat_id 
	left join unit_mst as unit on unit.unitid=apo.approve_unit 
	left join users as us on us.user_id=apo.user_id 
	
	where apo.approve_indent_status=0 and quotation_requirement=1 and quotation_approve_status=0 and apo.company_id in (".$_SESSION['company_id'].") and used_document=0";
	$result  = $dbcon->query($query);
	$row = brp_mysqli_fetch_array($result);
	$cnt  = $row['cnt'];
	return $cnt;

}


function release_stock_action_modal_godown_wise($dbcon,$p_id,$request_material_qty,$release_material_qty,$previous_process_id,$product_id,$main_product,$material_id,$to_godown_id,$res_stock_godown,$stock_id=""){
	// var_dump("Stock Id : " . $stock_id);
	if($previous_process_id == 0){
		$whr = "";

		if(!empty($stock_id)){
			$whr = " and res.reserve_id  = " .$stock_id;
		}
			$query1 = "select res.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_reserve_stock as res where  stock_status != 2 and  cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and stock_flage = 1 and ref_name != 'store_release' and godown_id =".$res_stock_godown." and p_id = " . $p_id . " and product_id = " . $product_id . $whr;
		
		$result1=$dbcon->query($query1);
		while($res1 =brp_mysqli_fetch_array($result1)){

			
			$pending_stock = $res1['base_stock'] - $res1['deduct_stock'];
			

			if($request_material_qty>0){
			if($pending_stock>0){
				// var_dump('pending_stock : '.$pending_stock);
				// var_dump('request_material_qty : '.$request_material_qty);
				if($pending_stock>$request_material_qty){
					$rqty=$request_material_qty;
					$request_material_qty=$request_material_qty-$request_material_qty;
					$info_used_status['used_status'] = 0;
				}else{
					$rqty=$pending_stock;
					$request_material_qty=$request_material_qty-$pending_stock;
					$info_used_status['used_status'] = 1;
				}
				// var_dump($info_used_status);
				// echo "ok";
				$type="conv_unit";
				$base_stock=$rqty;
				// $con_stock=convert_stock_new($dbcon,$rqty,$product_id,$type);
				$con_stock=($rqty/$res1['base_stock'])*$res1['convert_stock'];



				$info_used_status['used_base_stock'] = $res1['used_base_stock'] + $base_stock;
				$info_used_status['used_convert_stock'] = $res1['used_convert_stock'] + $con_stock;

				update_record('tbl_reserve_stock',$info_used_status,"reserve_id=".$res1['reserve_id'], $dbcon);

				

				$stock_date=date("Y-m-d");
	 			material_reserve_entry_godown_wise($dbcon,$stock_date,$res1['product_id'],$res1['godown_id'],$res1['base_unit'],$base_stock,$res1['convert_unit'],$con_stock,'2',$res1['request_id'],'store_release_temp_delete',$res1['ref_id'],$res1['sales_order_trn_id'],$res1['stock_id'],$res1['branch_id'],$p_id,$res1['reserve_id']);

				$qry2 = "select * from tbl_stock_trn where stock_status = 0 and stock_id = " . $res1['stock_id'];
				$result2=$dbcon->query($qry2);
				$row2 = brp_mysqli_fetch_array($result2);

				$used_base_stock=$row2['used_base_stock']-$base_stock;
				$used_convert_stock=$row2['used_convert_stock']-$con_stock;

				$info_stock['used_base_stock']		= $used_base_stock;
				$info_stock['used_convert_stock']	= $used_convert_stock;
				
				$updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$res1['stock_id'], $dbcon);
				
				/*add_stock($dbcon,$product_id,$info_rese['base_unit'],$stock_date,$row2['ref_name'],$row2['ref_id'],$row2['godown_id'],$base_stock,2,$row2['branch_id'],$stock_id,"",$row2['customer_id'],$row2['batch_id'],$row2['batch_no'],$row2['base_rate'],$row2['conv_rate']);*/
			}
		}
		}

	 $qry = "select * from tbl_material_release_trn where status = 0 and material_id = " .$material_id . " and p_id = " . $p_id . " and release_status = 0 and product_id = " . $product_id;
	$result = $dbcon->query($qry);
	while($row = brp_mysqli_fetch_array($result)){
			// add to godown stock  godown_wise

	$res_st_qry = "select * from tbl_reserve_stock where stock_flage  = 1 and product_id = ".$product_id." and ref_name !='store_release' and reserve_id = " .$row['stock_id'];
		$res_st_res = $dbcon->query($res_st_qry);

		$st_trn_id = 0 ;
		$res_st_row = brp_mysqli_fetch_array($res_st_res);
		if(brp_mysqli_num_rows($res_st_res) > 0){
			$st_trn_id  = $res_st_row['stock_id'];
		}else{
			$st_trn_id  = $row['stock_id'];
		}
		
	 $st_qry = "select * from tbl_stock_trn where stock_id = " .$st_trn_id;
		$st_res = $dbcon->query($st_qry);
		$st_row = brp_mysqli_fetch_array($st_res);
		$stock_date=date("Y-m-d");

		$used_base_stock=$st_row['used_base_stock']+$row['base_qty'];
		$used_convert_stock=$st_row['used_convert_stock']+$row['conv_qty'];

		$info_stock['used_base_stock']		= $used_base_stock;
		$info_stock['used_convert_stock']	= $used_convert_stock;

		$updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$st_trn_id, $dbcon);

		add_stock($dbcon,$row['product_id'],$st_row['base_unit'],$stock_date,"store_release_deduct",$row['material_trn_id'],$row['godown_id'],$row['base_qty'],2,$st_row['branch_id'],$st_trn_id,"",$st_row['customer_id'],$st_row['batch_id'],$st_row['batch_no'],$st_row['base_rate'],$st_row['conv_rate']);

		$stock_id_new = add_stock($dbcon,$row['product_id'],$st_row['base_unit'],$stock_date,"store_release",$row['material_trn_id'],$row['to_godown_id'],$row['base_qty'],1,$st_row['branch_id'],"","",$st_row['customer_id'],$st_row['batch_id'],$st_row['batch_no'],$st_row['base_rate'],$st_row['conv_rate']);

		$reserve_id = material_reserve_entry_godown_wise($dbcon,$stock_date,$row['product_id'],$row['to_godown_id'],$st_row['base_unit'],$row['base_qty'],$row['conv_unit'],$row['conv_qty'],'1',$row['rp_id'],"store_release",$row['material_trn_id'],"",$stock_id_new,$st_row['branch_id'],$row['p_id'],0);

		if($reserve_id){

			$st_qry7 = "select * from tbl_stock_trn where stock_id = " .$stock_id_new;
			$st_res7 = $dbcon->query($st_qry7);
			$st_row7 = brp_mysqli_fetch_array($st_res7);
			$stock_date=date("Y-m-d");

			$used_base_stock=$st_row7['used_base_stock']+$row['base_qty'];
			$used_convert_stock=$st_row7['used_convert_stock']+$row['conv_qty'];

			$info_stock_new['used_base_stock']		= $used_base_stock;
			$info_stock_new['used_convert_stock']	= $used_convert_stock;

			$updatetrnid=update_record('tbl_stock_trn',$info_stock_new,"stock_id=".$stock_id_new, $dbcon);

			$mt_info['release_status'] = 1;
			update_record('tbl_material_release_trn', $mt_info, "material_trn_id=".$row['material_trn_id'], $dbcon);
		}
	}
}else{
		$whr = "";
		if(!empty($stock_id)){
			$whr = " and res.process_reserve_id  = " .$stock_id;
		}

		 $query1 = "select res.*,(base_stock-used_base_stock) as pending_base_stock,(conv_stock-used_conv_stock) as pending_conv_stock from  tbl_process_reserve_stock as res where  stock_status = 0 and  cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and stock_flage = 1 and ref_name != 'store_release' and godown_id =".$res_stock_godown."  and p_id = " . $p_id . " and product_id = " . $product_id . $whr;
		$result1=$dbcon->query($query1);
		while($res1 =brp_mysqli_fetch_array($result1)){
			$pending_stock = $res1['base_stock'] - $res1['deduct_stock'];
			
			if($pending_stock>0){
				if($pending_stock>=$request_material_qty){
					$rqty=$request_material_qty;
					$request_material_qty=$request_material_qty-$request_material_qty;
				}else{
					$rqty=$pending_stock;
					$request_material_qty=$request_material_qty-$pending_stock;
				}


				$type="conv_unit";
				$base_stock=$rqty;
				$con_stock=convert_stock_new($dbcon,$rqty,$product_id,$type);

				$info_used_status['used_base_stock'] = $res1['used_base_stock'] + $base_stock;
				$info_used_status['used_conv_stock'] = $res1['used_conv_stock'] + $con_stock;

				update_record('tbl_process_reserve_stock',$info_used_status,"process_reserve_id=".$res1['process_reserve_id'], $dbcon);

				$stock_date=date("Y-m-d");

	 			material_process_reserve_entry_godown_wise($dbcon,$stock_date,$res1['product_id'],$res1['godown_id'],$res1['base_unit'],$base_stock,$res1['conv_unit'],$con_stock,'2',$res1['ref_name'],$res1['ref_id'],$res1['process_stock_id'],$res1['branch_id'],$p_id,$res1['process_id'],$res1['process_reserve_id']);
			// echo "okkkkkk";			
				$qry2 = "select * from tbl_process_stock_trn where stock_status = 0 and process_stock_id = " . $res1['process_stock_id'];
				$result2=$dbcon->query($qry2);
				$row2 = brp_mysqli_fetch_array($result2);


				$used_base_stock=$row2['used_base_stock']-$base_stock;
				$used_convert_stock=$row2['used_conv_stock']-$con_stock;

				$info_stock['used_base_stock']		= $used_base_stock;
				$info_stock['used_conv_stock']	= $used_convert_stock;
				
				$updatetrnid=update_record('tbl_process_stock_trn',$info_stock,"process_stock_id=".$res1['process_stock_id'], $dbcon);
				
				/*add_stock($dbcon,$product_id,$info_rese['base_unit'],$stock_date,$row2['ref_name'],$row2['ref_id'],$row2['godown_id'],$base_stock,2,$row2['branch_id'],$stock_id,"",$row2['customer_id'],$row2['batch_id'],$row2['batch_no'],$row2['base_rate'],$row2['conv_rate']);*/
			}
		}
		
		$qry = "select * from tbl_material_release_trn where status = 0 and material_id = " .$material_id . " and p_id = " . $p_id . " and release_status = 0 and product_id = " . $product_id;
	$result = $dbcon->query($qry);
	while($row = brp_mysqli_fetch_array($result)){
		// add to godown stock  godown_wise

		$res_st_qry = "select * from tbl_process_reserve_stock where process_reserve_id = " .$row['stock_id'];
		$res_st_res = $dbcon->query($res_st_qry);
		$res_st_row = brp_mysqli_fetch_array($res_st_res);

		$st_qry = "select * from tbl_process_stock_trn where process_stock_id = " .$res_st_row['process_stock_id'];
		$st_res = $dbcon->query($st_qry);
		$st_row = brp_mysqli_fetch_array($st_res);
		$stock_date=date("Y-m-d");

		$used_base_stock=$st_row['used_base_stock']+$row['base_qty'];
		$used_convert_stock=$st_row['used_convert_stock']+$row['conv_qty'];

		$info_stock['used_base_stock']		= $used_base_stock;
		$info_stock['used_convert_stock']	= $used_convert_stock;

		$updatetrnid=update_record('tbl_process_stock_trn',$info_stock,"process_stock_id=".$res_st_row['process_stock_id'], $dbcon);

		add_process_stock_mt_release($dbcon,$row['product_id'],$st_row['base_unit'],$stock_date,"store_release_deduct",$row['material_trn_id'],$row['godown_id'],$row['base_qty'],2,$st_row['branch_id'],$st_row['process_stock_id'],"",$st_row['process_id'],$st_row['customer_id'],$st_row['batch_id'],$st_row['batch_no'],$st_row['base_rate'],$st_row['conv_rate']);

		$stock_id_new = add_process_stock_mt_release($dbcon,$row['product_id'],$st_row['base_unit'],$stock_date,"store_release",$row['material_trn_id'],$row['to_godown_id'],$row['base_qty'],1,$st_row['branch_id'],"","",$st_row['process_id'],$st_row['customer_id'],$st_row['batch_id'],$st_row['batch_no'],$st_row['base_rate'],$st_row['conv_rate']);

	

		$reserve_id = material_process_reserve_entry_godown_wise($dbcon,$stock_date,$row['product_id'],$row['to_godown_id'],$st_row['base_unit'],$row['base_qty'],$row['conv_unit'],$row['conv_qty'],'1',"store_release",$row['material_trn_id'],$stock_id_new,$st_row['branch_id'],$row['p_id'],$st_row['process_id'],0);

		if($reserve_id){

			$st_qry = "select * from tbl_process_stock_trn where process_stock_id = " .$stock_id_new;
			$st_res = $dbcon->query($st_qry);
			$st_row = brp_mysqli_fetch_array($st_res);
			$stock_date=date("Y-m-d");

			$used_base_stock=$st_row['used_base_stock']+$row['base_qty'];
			$used_convert_stock=$st_row['used_convert_stock']+$row['conv_qty'];

			$info_stock['used_base_stock']		= $used_base_stock;
			$info_stock['used_convert_stock']	= $used_convert_stock;

			$updatetrnid=update_record('tbl_process_stock_trn',$info_stock,"process_stock_id=".$stock_id_new, $dbcon);

			$mt_info['release_status'] = 1;
			update_record('tbl_material_release_trn', $mt_info, "material_trn_id=".$row['material_trn_id'], $dbcon);
		}
	}

	}
}

	/**
	 * Send Email Common function for CRN Qoutation List 
	 */
	function common_print_send_email($to,$user,$subject,$content,$attachment=null,$file_name="",$cc=null,$bcc=null) {
		
		// Your Sendinblue API key
		$apiKey = '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';

		// Sendinblue API endpoint for sending transactional emails
		$endpoint = 'https://api.sendinblue.com/v3/smtp/email';

		// Email data
		// $emailData = array(
		// 	'to' => array(
		// 		array(
		// 			'email' => $to,
		// 		)
		// 	),
		// 	'cc' => $cc,
		// 	'bcc' => $bcc,
		// 	'replyTo' => array(
		// 		"name" => $user['user_name'],
		// 		"email" => $user['user_email']
		// 	),
		// 	'subject' => $subject,
		// 	'htmlContent' => $content,
		// 	'sender' => array(
		// 		'email' => $user['user_email'],
		// 		'name' => $user['user_name']
		// 	),
		// 	'attachment' => array(
		// 		array(
		// 			'url' => $attachment, // Replace with the actual URL of your attachment
		// 			'name' => $file_name
		// 		)
		// 	)
		// );

		$emailData = array();
		
		$emailData['to'][] = 
				array(
					'email' => $to,
    			);
			
		if ($cc) {
		    $emailData['cc'] = $cc;
		}
		
		if ($bcc) {
		    $emailData['bcc'] = $bcc;
		}
		
		$emailData['replyTo'] = array(
				"name" => $user['user_name'],
				"email" => $user['user_email']
			);
		$emailData['subject'] = $subject;
		$emailData['htmlContent'] = $content;
		$emailData['sender'] = array(
				'email' => $user['user_email'],
				'name' => $user['user_name']
			);
			
		$emailData['attachment'][] = array(
			'url' => $attachment, // Replace with the actual URL of your attachment
			'name' => $file_name
		);

		// Convert data to JSON format
		$jsonData = json_encode($emailData);

		// cURL setup
		$ch = curl_init($endpoint);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'api-key: ' . $apiKey
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Execute cURL session
		$response = curl_exec($ch);

		// Check for errors
		if (curl_errno($ch)) {
			$error_msg = 'Error: ' . curl_error($ch);
			$response = '0';
		} else {
			$response = "1";
		}

		// Close cURL session
		curl_close($ch);

		// Output API response
		return $response;
	}


	function send_whatsapp_message($dbcon,$mobile_no,$attachment_file_path,$template_name = "quotation_sharing") {

		// $mobile_no = "919033396599";
		$company_configuration = getCompanyConfiguration($dbcon);
		$whatsapp_api_url = $company_configuration["whatsapp_api_url"];
		$whatsapp_api_key = $company_configuration["whatsapp_api_key"];
		$response = 1;

		if ($whatsapp_api_url && $whatsapp_api_key) {

			$headers = array(
				'API-KEY: '. $whatsapp_api_key,
				'Content-Type: application/json',
			);

			$data = array(
				'to' => $mobile_no,
				'recipient_type' => 'individual',
				'type' => 'template',
				'template' => array(
					'language' => array(
						'policy' => 'deterministic',
						'code' => 'en',
					),
					'name' => $template_name,
					'components' => array(
						array(
							'type' => 'header',
							'parameters' => array(
								array(
									'type' => 'document',
									'document' => array(
										'link' => $attachment_file_path,
									),
								),
							),
						),
					),
				),
			);

			$ch = curl_init($whatsapp_api_url);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			$response = curl_exec($ch);

			if (curl_errno($ch)) {
				$response = 0;
			}

			curl_close($ch);
		}
		return $response;
	}

	function send_whatsapp_message_old($mobile_no,$message,$attachment_file_path,$file_name) {
	     
		$mobile_no = "919033396599";
	    $msg = strip_tags($message);
	    $curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://wasend.iaas.africa/api/create-message',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => 'UTF-8',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => array('appkey' => 'f2240a04-434d-42ca-90c6-10ba42dd1578','authkey' => 'xSUS5yw9pPOt0LbnzZTfA1Aumhw6rXkj3bu2QrSSZSqgrvUXJ2','to' => $mobile_no,'message' => $msg,'file' => $attachment_file_path,'file_name' => $file_name),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return $response;
	}


function get_transportation_name($dbcon,$id){
	$st_qry = "select * from transportation_details where id = " .$id;
	$st_res = $dbcon->query($st_qry);
	$st_row = brp_mysqli_fetch_array($st_res);

	return $st_row['transportation_name'];
}
?>
