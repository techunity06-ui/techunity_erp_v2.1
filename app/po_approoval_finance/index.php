<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
							
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
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
				PURCHASE_ORDER_PENDING_APPROVAL_VIEW,PURCHASE_ORDER_PENDING_APPROVAL_READ,PURCHASE_ORDER_PENDING_APPROVAL_APPROVE
		]);
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where='';
		$where.="  and po_type_status=1";

		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('po', $branch_id);
		$where.=" $where_db and po.company_id=".$_SESSION['company_id'];
		switch($POST['po_approval_status']){
			case "3":
			$where.="  and po.po_approval_status=3";
			break;
			
			case "1":
			$where.="  and po.po_approval_status=1";
			break;
			
			default:
			$where.="";
		}
		$getapprovalsetting = get_userwise_approval_setting($dbcon,5,$_SESSION['user_id']);
		if($getapprovalsetting['auto_approval']==1){
			$where.="  and (".$getapprovalsetting['amount']." >= g_total)";
		}

			
			$where.="  and purchaseorder_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND purchaseorder_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
			$appData = array();
			$i=1;
			$aColumns = array('purchaseorder_id','purchaseorder_no','l.l_name','city.city_name','bms.branch_name','purchaseorder_date','g_total','paid_amount','status','purchase_status','po.cdate','po.userid','po.po_type_status','po.po_req_status','po_approval_status','po_aproove_finance');
			$sIndexColumn = "purchaseorder_id";
			$isWhere = array("status = 0".$where);
			$sTable = "tbl_purchaseorder as po";
			$isJOIN = array('left join tbl_ledger as l on po.vender_id=l.l_id','left join  city_mst city on l.cityid=city.cityid','left join branch_mst as bms on bms.branch_id=po.branch_id');
			$hOrder = "po.purchaseorder_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['purchaseorder_no'];
				$row_data[] = date('d M, Y',strtotime($row['purchaseorder_date']));
				$row_data[] = $row["l_name"];
				$row_data[] = $row['branch_name'];
				$row_data[] = $row['city_name'];
				$row_data[] = round($row['g_total']);
				
				/* if($row['po_approval_status']=='1'){
					$row_data[] = '<button class="btn btn-xs btn-success" >Approved</button>';
				}
				else{
					$row_data[] = '<button class="btn btn-xs btn-warning">Approval Pending</button>';
				} */
				
				$poprint='';$delete='';$edit='';$cancel_po_btn='';$po_app_btn='';
				//PO Approval Button To admin
				if($_SESSION['user_type']=='2'){
					if(in_array(PURCHASE_ORDER_PENDING_APPROVAL_APPROVE,$bulkAccessArray)){
						if($row['po_aproove_finance']=="1"){
							$po_app_btn='<button class="btn btn-xs btn-success" data-original-title="PO Approved" data-toggle="tooltip" data-placement="top" onclick="change_po_approval_status('.$row['purchaseorder_id'].',0, \''.$row['purchaseorder_no'].'\')"><i class="fa fa-check"></i></button>';
						}
						else{
							$po_app_btn='<button class="btn btn-xs btn-warning" data-original-title="Approve PO" data-toggle="tooltip" data-placement="top" onclick="change_po_approval_status('.$row['purchaseorder_id'].',1, \''.$row['purchaseorder_no'].'\')"><i class="fa fa-check"></i></button>';
						}
					}
				}
				
				
				
				
				$poprint='<a class="btn btn-xs btn-info" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.'poprint/'.$row['purchaseorder_id'].'"><i class="fa fa-print"></i></a>';
				
				if($row['po_approval_status']=='1'){
					$row_data[] = '<button class="btn btn-xs btn-success" >Approved</button>';
				}
				else{
					$row_data[] = '<button class="btn btn-xs btn-warning">Approval Pending</button>';
				}	
				$row_data[] = $poprint.' '.$po_app_btn;
			 
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
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$info1['cdate']				= date('Y-m-d H:i:s');

			$inserid=add_record("tbl_purchaseorder_finance_aprv_log", $info1, $dbcon);

			$info['po_approval_status'] = $POST['approve_status'];	
			if($POST['approve_status'] == 1){
				$que = "select * from tbl_purchaseorder where purchaseorder_id=".$POST['purchase_order_id'];
				$rel=mysqli_fetch_assoc($dbcon->query($que));
				
				if($rel['po_type'] == 1){
					$grn['used_status'] = 1;
					$grn_done = update_record("tbl_purchaseordertrn", $grn, "purchaseorder_id=".$POST['purchase_order_id'], $dbcon);
				}
			}
			$updateid=update_record("tbl_purchaseorder", $info, "purchaseorder_id=".$POST['purchase_order_id'], $dbcon);
		}
		else if(strtolower($POST['mode']) == "load_purchase_hist_datatable") {

			$where='';
			$where.="   log.purchaseorder_id=".$POST['purchase_order_id'];

			$appData = array();
			$i=1;
			$aColumns = array('log.po_finance_approve_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
			$sIndexColumn = "log.po_finance_approve_id";
			$isWhere = array(" ".$where." ");
			$sTable = "tbl_purchaseorder_finance_aprv_log as log";			
			$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
			$hOrder = "log.po_finance_approve_id desc";
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
		
?>