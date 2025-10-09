<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
				OVERDUE_PO_PRO_ADD
		]);
		$where="";

		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('sht', $branch_id);
		$where.=" $where_db and sht.company_id=".$_SESSION['company_id'];
		
		/*trn.product_qty>(select IFNULL(sum(product_qty+tolerance),0) as qty  from tbl_grn_trn as chtrn 
		left join tbl_grn as gn on gn.grn_id=chtrn.grn_id
		where chtrn.grn_trn_status=0 and gn.ref_type=2 and chtrn.purchaseorder_id=trn.purchaseorder_id and trn.product_id=chtrn.product_id)*/
		
		$appData = array();
		$i=1;
		$aColumns = array('sht.log_id','sht.po_no','sht.product_id','tc.cat_name','sht.short_close_qty','sht.short_close_reason','sht.date','pro.product_name','bms.branch_name','user.user_name','sht.user_id','sht.aproove_status','sht.po_trn_id','sht.po_id');
		$sIndexColumn = "sht.log_id";
		$isWhere = array("sht.short_close_status=0".$where);
		$sTable = "tbl_log_po_short_close as sht";			
		$isJOIN = array('left join product_mst as pro on pro.product_id=sht.product_id','left join tbl_category as tc on pro.product_category=tc.cat_id','left join tbl_purchaseorder as po on po.purchaseorder_id=sht.po_id','left join branch_mst as bms on bms.branch_id=sht.branch_id','left join users as user on user.user_id=sht.user_id');
		$hOrder = "sht.log_id desc";
		//$hGroupby = array("trn.product_id");
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		
		$setconf="select * from tbl_company_configuration where company_id=".$_SESSION['company_id'];
		$set_conf=brp_mysqli_fetch_assoc($dbcon->query($setconf));
		$purchase_pro_search = $set_conf['purchase_pro_search'];
		$pro_search=explode(",", $purchase_pro_search);
		
		foreach($sqlReturn as $row) {
			
			if(in_array('drawing',$pro_search)){
				$drawing_number = " (".$row['drawing_number'].")";
			}else{
				$drawing_number = '';
			}
			if(in_array('item',$pro_search)){
				$item_code = " (".$row['product_icode'].")";
			}else{
				$item_code = '';
			}
			
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['po_no'];
			$row_data[] = $row['product_name'].$drawing_number.$item_code;
			$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$row_data[] = date('d-m-Y',strtotime($row['date']));
			$row_data[] = $row['short_close_qty'];
			$row_data[] = $row['short_close_reason'];
			$row_data[] = find_user_name($dbcon,$row['user_id']);
			
			if($row['aproove_status'] ==1){
				$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
			}else{
				$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Approve Pending</div>';
			}
			
			$short_close_po_approove = '';
			if($row['aproove_status'] ==1){
				$short_close_po_approove='<button class="btn btn-xs btn-success" data-original-title="PO Short Close Approved" data-toggle="tooltip" data-placement="top" onclick="change_po_shortclose_approval_status('.$row['po_id'].','.$row['po_trn_id'].',0, \''.$row['po_no'].'\')"><i class="fa fa-check"></i></button>';
			}else{
				$short_close_po_approove='<button class="btn btn-xs btn-warning" data-original-title="Approve PO" data-toggle="tooltip" data-placement="top" onclick="change_po_shortclose_approval_status('.$row['po_id'].','.$row['po_trn_id'].',1, \''.$row['po_no'].'\')"><i class="fa fa-check"></i></button>';
			}
			$row_data[] = $short_close_po_approove;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "load_purchase_hist_datatable") {
		
		$where='';
		$where.="   log.purchaseorder_trn_id=".$POST['po_trn_id'];
		
		$appData = array();
		$i=1;
		$aColumns = array('log.po_shortclose_aprv_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
		$sIndexColumn = "log.po_shortclose_aprv_id";
		$isWhere = array(" ".$where." ");
		$sTable = "tbl_po_shortclose_aprv_log as log";			
		$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
		$hOrder = "log.po_shortclose_aprv_id desc";
		include('../../include/pagging.php');
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
	else if(strtolower($POST['mode']) == "add_po_apprv_hist") {

		$info1['approve_remark']	= $POST['approve_remark'];
		$info1['approve_status']	= $POST['approve_status'];
		$info1['purchaseorder_id']	= $POST['purchase_order_id'];
		$info1['purchaseorder_trn_id']	= $POST['po_trn_id'];	
		$info1['user_id']			= $_SESSION['user_id'];
		$info1['company_id']		= $_SESSION['company_id'];
		$info1['cdate']				= date('Y-m-d H:i:s');

		$inserid=add_record("tbl_po_shortclose_aprv_log", $info1, $dbcon);
		
		// Update For Po Trn table 
		if($POST['approve_status'] == 1){
			$info['used_status'] = 1;
			$info['shortclose_status'] = 0;
		}else{
			$info['used_status'] = 0;
			$info['shortclose_status'] = 0;
		}
		
		$updateid=update_record("tbl_purchaseordertrn", $info, "purchaseordertrn_id=".$POST['po_trn_id'], $dbcon);
		
		// Aproove For Short Close Log Table
		$infoshort['aproove_status']		= $POST['approve_status'];
		
		$updateid=update_record("tbl_log_po_short_close", $infoshort, "po_trn_id=".$POST['po_trn_id'], $dbcon);
	}
	else if(strtolower($POST['mode']) == "load_party_purchase_dtl") {
		$qt_qry="select qt.*,country_name,state_name,city_name,led.company_name,led.cust_mobile,led.m_address,led.gst_no from tbl_purchaseorder as qt
		left join tbl_ledger as led on led.l_id=qt.vender_id
		left join country_mst as country on country.countryid=led.countryid
		left join state_mst as state on state.stateid=led.stateid
		left join city_mst as city on city.cityid=led.cityid
		where qt.purchaseorder_id=".$POST['purchase_order_id'];
		$qt_rel=brp_mysqli_fetch_assoc($dbcon->query($qt_qry));
		
		//Party PO Details Table View
		$str='';
		$str.='<div class="form-group">
				<table class="display table table-bordered table-striped">
					<tr>
						<td colspan="2"><strong>Company Name:</strong> '.$qt_rel['company_name'].'</td>
						<td><strong>Contact No.:</strong> '.$qt_rel['cust_mobile'].'</td>
					</tr>
					<tr>
						<td colspan="2"><strong>Address:</strong> '.$qt_rel['m_address'].'</td>
						<td><strong>GST No.:</strong> '.$qt_rel['gst_no'].'</td>
					</tr>
					<tr>
						<td><strong>City:</strong> '.$qt_rel['city_name'].'</td>
						<td><strong>State:</strong> '.$qt_rel['state_name'].'</td>
						<td><strong>Country:</strong> '.$qt_rel['country_name'].'</td>
					</tr>
					<tr>
						<td><strong>City:</strong> '.$qt_rel['city_name'].'</td>
						<td><strong>State:</strong> '.$qt_rel['state_name'].'</td>
						<td><strong>Country:</strong> '.$qt_rel['country_name'].'</td>
					</tr>
					<tr>
						<td><strong>Purchase order No:</strong> '.$qt_rel['purchaseorder_no'].'</td>
						<td><strong>Purchase Order Date:</strong> '.date("d-M-Y",strtotime($qt_rel["purchaseorder_date"])).'</td>
						<td><strong>Purchase Order Amount:</strong> '.$qt_rel['g_total'].'</td>
					</tr>
				';
		$str.='</table></div>
		<hr/>
		';
		
		$qt_rel['mod_po_comp_div_sec'] = $str;
		
		echo json_encode($qt_rel);
	}
	
?>
