<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	FINANCE_EXPENSE_DETAIL_EDIT,
	FINANCE_EXPENSE_DETAIL_DELETE,
	FINANCE_EXPENSE_DETAIL_REQUEST
]);
//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		
		//$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		//$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
		
		$userid=$_SESSION['user_id'];	
		
		$appData = array();
		$i=1;
		$where='';
		$where.=" and exp.user_id='$userid'";
		$aColumns = array('exp.ex_id', 'exp.expense_date','exp.expense_complain', 'exp.g_total','exp.expense_status', 'exp.user_id','exp.expense_approve_status','comp.complaint_no','cust.l_name','exp.paid_amount','exp.remark','exp.paid_status');
		$sIndexColumn = "ex_id";
		$isWhere = array("exp.expense_status=0".$where);
		$sTable = "tbl_expense_detail as exp";			
		$isJOIN = array('left join tbl_complaint as comp on  comp.complaint_id=exp.expense_complain','left join tbl_ledger as cust on cust.l_id=exp.vendorid');
		$hOrder = "ex_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = date("d/m/Y",strtotime($row['expense_date'])); 
			$row_data[] = $row['l_name']; 
			$row_data[] = $row['complaint_no']; 
			$row_data[] = $row['paid_amount']; 
			$row_data[] = nl2br($row['remark']); 
			//$row_data[] = get_last_remark($dbcon,$row['ex_id']); 
			if($row['expense_approve_status']=='0')
			{
				$row_data[] = '<a class="btn btn-warning btn-xs">Pending</a>'; 
			}
			else if($row['expense_approve_status']=='2')
			{
				$row_data[] = '<a class="btn btn-danger btn-xs">Rejected</a>';  
			}
			else
			{
				$row_data[] = '<a class="btn btn-success btn-xs">Approved</a>';  
			}
			
			$edit_btn='';$delete_btn='';
			
			if($row['usertype_id']=='3')
			{
				if($row['expense_approve_status']=='0')
				{
					if(in_array(FINANCE_EXPENSE_DETAIL_EDIT,$bulkAccessArray)){
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top"  href="'.ROOT.'expense_edit/'.$row['ex_id'].'");"><i class="fa fa-pencil"></i></a>';
					}
					if(in_array(FINANCE_EXPENSE_DETAIL_DELETE,$bulkAccessArray)){
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_expense('.$row['ex_id'].')"><i class="fa fa-trash-o"></i></button>';
					}
					$approve_btn='';
				}
			}
			else
			{
				if(in_array(FINANCE_EXPENSE_DETAIL_EDIT,$bulkAccessArray)){
					$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top"  href="'.ROOT.'expense_edit/'.$row['ex_id'].'");"><i class="fa fa-pencil"></i></a>';
				}
				if(in_array(FINANCE_EXPENSE_DETAIL_DELETE,$bulkAccessArray)){
					$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_expense('.$row['ex_id'].')"><i class="fa fa-trash-o"></i></button>';
				}
			}
			if($row['expense_approve_status']=='2')
			{ 
				if(in_array(FINANCE_EXPENSE_DETAIL_REQUEST,$bulkAccessArray)){
					$approve_btn='<a class="btn btn-xs btn-warning" data-original-title="Request Again" data-toggle="tooltip" data-placement="top"  href="'.ROOT.'expense_request/'.$row['ex_id'].'");"><i class="fa fa-paper-plane"></i></a>';
				}
			}
			else{
				$approve_btn="";
			}
			
			// pathik edit start
			$paid_amo=expance_paid_amount($dbcon,$row['ex_id']);
			if($row['paid_amount']<=$paid_amo){
				$row_data[] = '<span>Paid </span>'; 
			}else{
				$due_amo=$row['paid_amount']-$paid_amo;
				$row_data[] = '<span>Un-Paid('.$due_amo.')</span>';
			}
			// pathik edit end
			
			if($row['expense_approve_status']=='1'){//Hide Edit Btn if Approved
				$edit_btn='';
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn.' '.$approve_btn; 
			
			$appData[] = $row_data;
			
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		
		$info['expense_name']	= $POST['expense_name'];							
		$info['expense_complain']	= $POST['expense_complain'];							
		$info['expense_amount']	= $POST['expense_amount'];							
		$info['remark']	= $POST['remark'];							
		$info['expense_date']		= date("Y-m-d",strtotime($POST['expense_date']));
		$info['cdate']			= date("Y-m-d H:i:s");
		$info['user_id']		= $_SESSION['user_id'];
		$info['usertype_id']	= $_SESSION['user_type'];
		$info['emp_id']	= $_SESSION['employee_id'];
		if(!$POST['multi_company'])
				$info['company_id']			= $_SESSION['company_id'];
			else
				$info['company_id']			= 0;
		
		$inserid=add_record('tbl_expense_detail', $info, $dbcon);
		
		if($inserid)
			$row['res'] ="1";
		else
			$row['res'] ="0";

		echo json_encode($row);	
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `tbl_expense_detail` WHERE `ex_id` = '$POST[expense_id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		
		$info['expense_name']	= $POST['expense_name'];							
		$info['expense_complain']	= $POST['expense_complain'];							
		$info['expense_amount']	= $POST['expense_amount'];							
		$info['expense_date']		= date("Y-m-d",strtotime($POST['expense_date']));
		$info['user_id']	= $_SESSION['user_id'];
		
		$updateid=update_record('tbl_expense_detail', $info,"ex_id=".$POST['eid'] , $dbcon);
		
		$row['res']='';
		
		if($updateid){
			$row['res']='update';
		}
		else{
			$row['res']='0';
		}
		echo json_encode($row); 
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['expense_status']='2';
		$updateid=update_record('tbl_expense_detail', $info,"ex_id=".$POST['eid'] , $dbcon);
		
		$info_exp['genral_book_status']=2;
			$updateid=update_record('tbl_general_book', $info_exp,"table_name='tbl_expense_detail' and table_id=".$POST['eid'] , $dbcon);
			$updateid=update_record('tbl_general_book', $info_exp,"table_name='tbl_expense_detail_account' and table_id=".$POST['eid'] , $dbcon);
			
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}
	else if(strtolower($POST['mode']) == "get_complain") {
		
		$customer=$POST['customer'];
		
		$complain_id=$POST['complain_id'];
		
		echo get_customer_complain_expense($dbcon,$complain_id,'Add',$customer);
	}
	else if(strtolower($POST['mode']) == "get_cust_by_comp") {
		$c_qry="select cust_id from tbl_complaint where complaint_id=".$POST['comp_id'];
		$c_rel=mysqli_fetch_assoc($dbcon->query($c_qry));
		echo json_encode($c_rel);
	}
	
	
?>