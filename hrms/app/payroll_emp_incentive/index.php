<?php

session_start();
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../../include/function_database_query.php");
include_once("../../../include/common_functions.php");
include_once("../../../include/payroll_common_functions.php");
	if($_POST != NULL) {
		$POST = bulk_filter($dbcon,$_POST);
	} else {
		$POST = bulk_filter($dbcon,$_GET);
	}
	if(strtolower($POST['mode']) == "fetch") {
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			$where='';
			$where.="payrollempincen.status IN (0,1) and payrollempincen.company_id = $companyID".check_user('payrollempincen');
			$appData = array();
			$i=1;
			$aColumns = array('payrollempincen.id','payrollempincen.series_id','payledger.l_name','paycomp.salary_component_name','payrollempincen.payroll_date','payrollempincen.incentive_amount','invoicetype.invoice_format','invoicetype.format_value','invoicetype.end_format_value','comp.company_name','payrollempincen.status');
			$sIndexColumn = "payrollempincen.id";
			$isWhere = array($where);
			$sTable = "payroll_emp_incentive as payrollempincen";			
			$isJOIN = array("left join tbl_company as comp on comp.company_id = payrollempincen.company_id",
							"left join tbl_ledger as payledger on payledger.l_id = payrollempincen.employee_id",
							"left join payroll_salary_component as paycomp on paycomp.id=payrollempincen.salary_component_id",
							"left join tbl_invoicetype as invoicetype on invoicetype.invoicetype_id=payrollempincen.series_id");
			$hOrder = "payrollempincen.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['series_id'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['l_name'];
				$row_data[] = $row['salary_component_name'];
				$row_data[] = $row['payroll_date'];
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per){
						$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onclick="edit_test('.$row['id'].');"><i class="fa fa-pencil"></i></button>';
					}

					if($delete_btn_per){
						$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onclick="delete_catalog('.$row['id'].')"><i class="fa fa-trash-o"></i></button>'; 
					}
				}
			    if($other_btn_per) {
					if($row['status'] == '0'){  
						$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-window-close'></i></a>";	
					} else {
						$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-check-square-o'></i></a>";
					}
				}
				
				$row_data[] = $edit_btn.' '.$delete_btn.' '.$change_status;
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			// Insert New Payroll Employee Incentive List
			$row['res']='';
			$info['user_id']= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id'] = $_POST['series_id'];
			$info['employee_id'] = $_POST['employee_id'];
			$info['salary_component_id'] = $_POST['salary_component_id'];
			$info['payroll_date'] = ($_POST['payroll_date'])?date('Y-m-d', strtotime($_POST['payroll_date'])):'';
			$info['incentive_amount'] = $_POST['incentive_amount'];
			$info['status']	= $POST['status'];
			$insertpayrollincentiveid = add_record('payroll_emp_incentive', $info, $dbcon);

			updateSeries($dbcon, 'id', 'payroll_emp_incentive', 'PAYROLL EMPLOYEE INCENTIVE');

			if($insertpayrollincentiveid) {
				if(strtolower($POST['model'])=="model") {
					$query="select * from payroll_emp_incentive where id=".$insertpayrollincentiveid;
					$rel=mysqli_fetch_assoc($dbcon->query($query));		
					$row = $rel;
					$row['res']="2"; 
				} else {
					$row['res'] ="1";
				}
			} else {
				$row['res'] ="0";
			}	
			echo json_encode($row);	

		}else if(strtolower($POST['mode']) == "edit") {
			$row = array();
			if($_POST['token'] == $_SESSION['token']) {
				$info['user_id']= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$info['series_id'] = $_POST['series_id'];
				$info['employee_id'] = $_POST['employee_id'];
				$info['salary_component_id'] = $_POST['salary_component_id'];
				$info['payroll_date'] = ($_POST['payroll_date'])?date('Y-m-d', strtotime($_POST['payroll_date'])):'';
				$info['incentive_amount'] = $_POST['incentive_amount'];
				$info['status']	= $POST['status'];
				$info['updated_at']	= date("Y-m-d H:i:s");
				$updatepayrollincentiveid = update_record('payroll_emp_incentive', $info,"id=".$POST['eid'] , $dbcon);
		
				$row['res'] = ($updatepayrollincentiveid) ? "1" : "0";
			} else {
				$row['res'] = "0";
			}
			echo json_encode($row);	
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `payroll_emp_incentive` WHERE `id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "delete") {
			if($_POST['token'] == $_SESSION['token']) {
				$info['status']='2';
				$updateid=update_record('payroll_emp_incentive', $info,"id=".$POST['eid'] , $dbcon);
				
				echo ($updateid) ? "1" : "0";
			} else {
				echo  "0";
			}
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			$info['updated_at']	= date("Y-m-d H:i:s");

			$updateid = update_record('payroll_emp_incentive', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}
?>