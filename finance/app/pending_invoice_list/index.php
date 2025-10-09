<?php

session_start(); //start session
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
//Ankit Sompura 09-01-2021
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	FINANCE_CREATE_INVOICE,
	FINANCE_QUOTATION_PRINT,
	FINANCE_SPARE_TO_INVOICE,
	COMPLAINT_SLUG_VIEW,
	FINANCE_SPARE_TO_BILL_OF_SUPPLY
]);

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

	if(strtolower($POST['mode']) == "fetch") {
		//$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		//$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);

		$s_date=explode(' - ',$POST['date']);
		/*$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];*/

		$where='';
                $branch_id = $POST['branch_id'];
                        
                if($branch_id){
                    $where .= check_branch('quot',$branch_id);
                }
                
		$where.="  and quot.quotation_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND quot.quotation_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
		if($_SESSION['user_type']!='1' && $_SESSION['user_type']!='2'){
			$where.=' and inq.won_user_id='.$_SESSION['user_id'];
		}
		
		$appData = array();
		$i=1;
		$aColumns = array('quot.quotation_id', 'quot.quotation_no', 'quot.quotation_date', 'cust.cust_name', 'quot.g_total', 'quot.po_approve_status', 'quot.order_approve_status', 'quot.payment_approve_status', 'inq.inquiry_no', 'quot.quot_subject', 'usr.user_name', 'quot.quotation_status','quot.cdate','quot.revise_status','quot.prev_quotation_id','quot.approve_status','quot.cust_id','quot.l_id','quot.qt_order_conf_attch','quot.qt_po_attch','quot.payment_mstid');
		$sIndexColumn = "quot.quotation_id";
		$isWhere = array("quot.quotation_status = 0 and quot.revise_status=0 and quot.approve_status=1 and quot.payment_approve_status=1 and quot.inv_done_status=0 and quot.company_id = ".$_SESSION['company_id']." ".$where);
		$sTable = "tbl_quotation as quot";
		$isJOIN = array('left join tbl_customer as cust on cust.cust_id=quot.cust_id', 'left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id', 'left join users as usr on usr.user_id=inq.user_id');
		$hOrder = "quot.quotation_id desc";
		include($path.'include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			if(in_array(FINANCE_CREATE_INVOICE,$bulkAccessArray)){
				$row_data[] = '<a class="" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'quot_to_inv/'.$row['quotation_id'].'">'.$row["quotation_no"].'</a>';
				$row_data[] = '<a class="" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'quot_to_inv/'.$row['quotation_id'].'">'.date('d M, Y',strtotime($row["quotation_date"])).'</a>';
				$row_data[] = '<a class="" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'quot_to_inv/'.$row['quotation_id'].'">'.$row["cust_name"].'</a>';
				$row_data[] = '<a class="" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'quot_to_inv/'.$row['quotation_id'].'">'.$row["g_total"].'</a>';
				$row_data[] = '<a class="" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'quot_to_inv/'.$row['quotation_id'].'">'.$row["user_name"].'</a>';
			}
			if(in_array(FINANCE_QUOTATION_PRINT,$bulkAccessArray)){
				$print_btn='<a class="btn btn-xs btn-info" data-original-title="View Quotation" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.'quotation_print/'.$row['quotation_id'].'"><i class="fa fa-print"></i></a>';
			}
			if(in_array(FINANCE_CREATE_INVOICE,$bulkAccessArray)){
				$add_inv_btn='<a class="btn btn-xs btn-primary" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'quot_to_inv/'.$row['quotation_id'].'"><i class="fa fa-plus"></i></a>';
			}
			
			$row_data[] = $print_btn.' '.$add_inv_btn;

			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "fetch_service") {
		//$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		//$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];

		$where='';
		$where.=" and comp.complaint_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND comp.complaint_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$appData = array();
		$i=1;
		$aColumns = array('comp.complaint_id', 'complaint_no', 'complaint_date', 'l.l_name', 'usr.user_name', 'comp.cdate', 'comp.user_id', 'sum(s_amount) as s_amount');
		$sIndexColumn = "comp.complaint_id";
		//Amish Soni Start - 17-12-2020
		$isWhere = array("comp.complaint_status = 0 and spare.s_paid_status='paid' and spare.s_inv_status=0 and comp.company_id in (0,$_SESSION[company_id])".$where);
		$hGroupby = array("spare.s_comp_id");
		$sTable = "tbl_complaint as comp";
		$isJOIN = array('left join tbl_complain_spare_part as spare on spare.s_comp_id=comp.complaint_id','left join tbl_ledger as l on comp.cust_id=l.l_id', 'left join users as usr on usr.user_id=comp.user_id');
		$hOrder = "comp.complaint_id desc";
		//Amish Soni End - 17-12-2020
		include($path.'include/pagging.php');
		//echo $squery;
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			//if(in_array(FINANCE_SPARE_TO_INVOICE,$bulkAccessArray)){
				$row_data[] = '<a class="" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'spare_to_inv/'.$row['complaint_id'].'">'.$row["complaint_no"].'</a>';
				$row_data[] = '<a class="" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'spare_to_inv/'.$row['complaint_id'].'">'.date('d M, Y',strtotime($row["complaint_date"])).'</a>';
				$row_data[] = '<a class="" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'spare_to_inv/'.$row['complaint_id'].'">'.$row["l_name"].'</a>';
				$row_data[] = '<a class="" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'spare_to_inv/'.$row['complaint_id'].'">'.$row["s_amount"].'</a>';
				$row_data[] = '<a class="" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'spare_to_inv/'.$row['complaint_id'].'">'.$row["user_name"].'</a>';
			//}
			if(in_array(COMPLAINT_SLUG_VIEW,$bulkAccessArray)){
				$view_btn='<a href="'.ROOT.SERVICE_ROOT.'complaint_history/'.$row['complaint_id'].'" class="btn btn-xs btn-info" data-original-title="View Complaint History" data-toggle="tooltip" data-placement="top" target="_blank"><i class="fa fa-eye"></i></a>';
			}
			//if(in_array(FINANCE_SPARE_TO_INVOICE,$bulkAccessArray)){
				$add_inv_btn='<a class="btn btn-xs btn-primary" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'spare_to_inv/'.$row['complaint_id'].'"><i class="fa fa-plus"></i></a>';
			//}
			
			$row_data[] = $view_btn.' '.$add_inv_btn;

			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "fetch_service_inv") {
		//$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		//$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];

		$where='';
		$where.=" and comp.complaint_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND comp.complaint_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$appData = array();
		$i=1;
		$aColumns = array('comp.complaint_id', 'complaint_no', 'complaint_date', 'l.l_name', 'usr.user_name', 'comp.cdate', 'comp.user_id', 'sum(comp_amount) as s_amount');
		$sIndexColumn = "comp.complaint_id";
		$isWhere = array("comp.complaint_status=0 and comp.followup_status=4 and trn.inv_done_status=0 and trn.comp_pro_sts=2 and trn.complaint_trn_status=0 and comp.company_id in (0,$_SESSION[company_id])".$where);
		$hGroupby = array("trn.complaint_id");
		$sTable = "tbl_complaint as comp";
		$isJOIN = array('left join tbl_complaint_trn as trn on trn.complaint_id=comp.complaint_id','left join tbl_ledger as l on comp.cust_id=l.l_id', 'left join users as usr on usr.user_id=comp.user_id');
		$hOrder = "comp.complaint_id desc";
		include($path.'include/pagging.php');
		//echo $squery;
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			//if(in_array(FINANCE_SPARE_TO_INVOICE,$bulkAccessArray)){
				$row_data[] = '<a class="" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'spare_to_inv/'.$row['complaint_id'].'">'.$row["complaint_no"].'</a>';
				$row_data[] = '<a class="" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'spare_to_inv/'.$row['complaint_id'].'">'.date('d M, Y',strtotime($row["complaint_date"])).'</a>';
				$row_data[] = '<a class="" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'spare_to_inv/'.$row['complaint_id'].'">'.$row["l_name"].'</a>';
				$row_data[] = '<a class="" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'spare_to_inv/'.$row['complaint_id'].'">'.$row["s_amount"].'</a>';
				$row_data[] = '<a class="" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.'spare_to_inv/'.$row['complaint_id'].'">'.$row["user_name"].'</a>';
			//}
			
			$add_inv_btn='';$view_btn='';
			//if(in_array(COMPLAINT_SLUG_VIEW,$bulkAccessArray)){
				$view_btn='<a href="'.ROOT.SERVICE_ROOT.'complaint_history/'.$row['complaint_id'].'" class="btn btn-xs btn-info" data-original-title="View Complaint History" data-toggle="tooltip" data-placement="top" target="_blank"><i class="fa fa-eye"></i></a>';
			//}

			//if(in_array(FINANCE_SPARE_TO_INVOICE,$bulkAccessArray)){
				$add_inv_btn='<a class="btn btn-xs btn-primary" data-original-title="Create Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'spare_to_inv/'.$row['complaint_id'].'"><i class="fa fa-plus"></i></a>';
			//}
			
			$row_data[] = $view_btn.' '.$add_inv_btn;

			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "fetch_bill_of_supply") {
		//$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		//$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];

		$where='';
		$where.=" and comp.complaint_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND comp.complaint_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$appData = array();
		$i=1;
		$aColumns = array('comp.complaint_id', 'complaint_no', 'complaint_date', 'l.l_name', 'usr.user_name', 'comp.cdate', 'comp.user_id', 'sum(s_amount) as s_amount');
		$sIndexColumn = "comp.complaint_id";
		$isWhere = array("comp.complaint_status = 0 and spare.s_paid_status='free' and spare.s_inv_status=0 and comp.company_id in (0,$_SESSION[company_id])".$where);
		$hGroupby = array("spare.s_comp_id");
		$sTable = "tbl_complaint as comp";
		$isJOIN = array('left join tbl_complain_spare_part as spare on spare.s_comp_id=comp.complaint_id','left join tbl_ledger as l on comp.cust_id=l.l_id', 'left join users as usr on usr.user_id=comp.user_id');
		$hOrder = "comp.complaint_id desc";
		include($path.'include/pagging.php');
		//echo $squery;
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			if(in_array(FINANCE_SPARE_TO_BILL_OF_SUPPLY,$bulkAccessArray)){
				$row_data[] = '<a class="" data-original-title="Create Bill of Supply" data-toggle="tooltip" data-placement="top" href="'.ROOT.'spare_to_bos/'.$row['complaint_id'].'">'.$row["complaint_no"].'</a>';
				$row_data[] = '<a class="" data-original-title="Create Bill of Supply" data-toggle="tooltip" data-placement="top" href="'.ROOT.'spare_to_bos/'.$row['complaint_id'].'">'.date('d M, Y',strtotime($row["complaint_date"])).'</a>';
				$row_data[] = '<a class="" data-original-title="Create Bill of Supply" data-toggle="tooltip" data-placement="top" href="'.ROOT.'spare_to_bos/'.$row['complaint_id'].'">'.$row["l_name"].'</a>';
				$row_data[] = '<a class="" data-original-title="Create Bill of Supply" data-toggle="tooltip" data-placement="top" href="'.ROOT.'spare_to_bos/'.$row['complaint_id'].'">'.$row["s_amount"].'</a>';
				
				$row_data[] = '<a class="" data-original-title="Create Bill of Supply" data-toggle="tooltip" data-placement="top" href="'.ROOT.'spare_to_bos/'.$row['complaint_id'].'">'.$row["user_name"].'</a>';
			}
			if(in_array(COMPLAINT_SLUG_VIEW,$bulkAccessArray)){
				$view_btn='<a href="'.ROOT.SERVICE_ROOT.'complaint_history/'.$row['complaint_id'].'" class="btn btn-xs btn-info" data-original-title="View Complaint History" data-toggle="tooltip" data-placement="top" target="_blank"><i class="fa fa-eye"></i></a>';
			}
			if(in_array(FINANCE_SPARE_TO_BILL_OF_SUPPLY,$bulkAccessArray)){
				$add_inv_btn='<a class="btn btn-xs btn-primary" data-original-title="Create Bill of Supply" data-toggle="tooltip" data-placement="top" href="'.ROOT.'spare_to_bos/'.$row['complaint_id'].'"><i class="fa fa-plus"></i></a>';
			}
			
			$row_data[] = $view_btn.' '.$add_inv_btn;

			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	
?>