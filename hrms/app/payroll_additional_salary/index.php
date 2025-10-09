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
			$where.="payrolladdition.status IN (0,1) and payrolladdition.company_id = $companyID".check_user('payrolladdition');
			$appData = array();
			$i=1;
			$aColumns = array('payrolladdition.id','payledger.l_name','paycomp.salary_component_name','payrolladdition.series_id','invoicetype.invoice_format','invoicetype.format_value','invoicetype.end_format_value','payroll_date','additional_salary_amount', 'comp.company_name','payrolladdition.status');
			$sIndexColumn = "payrolladdition.id";
			$isWhere = array($where);
			$sTable = "payroll_additional_salary as payrolladdition";			
			$isJOIN = array("left join tbl_company as comp on comp.company_id = payrolladdition.company_id",
						"left join tbl_ledger as payledger on payledger.l_id = payrolladdition.employee_id",
						"left join tbl_invoicetype as invoicetype on invoicetype.invoicetype_id=payrolladdition.series_id",
						"left join payroll_salary_component as paycomp on paycomp.id=payrolladdition.salary_component_id");
			$hOrder = "payrolladdition.id desc";
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
				$row_data[] = $row['additional_salary_amount'];
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per){
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'payroll_additional_salary_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per){
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payroll_additional_salary('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
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
			// Insert New Payroll Additional Salary List
			$info['user_id']= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id'] = $POST['series_id'];
			$info['employee_id'] = $_POST['employee_id'];
			$info['salary_component_id'] = $_POST['salary_component_id'];
			$info['payroll_date'] = ($_POST['payroll_date'])?date('Y-m-d', strtotime($_POST['payroll_date'])):'';
			$info['is_recurring_flag'] = ($_POST['is_recurring_flag'])?$_POST['is_recurring_flag']:'No';
			$info['overwrite_salary_structure_amount_flag'] = ($_POST['overwrite_salary_structure_amount_flag'])?$_POST['overwrite_salary_structure_amount_flag']:'No';
			$info['deduct_fulltax_on_selected_payroll_date_flag'] = ($_POST['deduct_fulltax_on_selected_payroll_date_flag'])?$_POST['deduct_fulltax_on_selected_payroll_date_flag']:'No';
			$info['additional_salary_amount'] = $_POST['additional_salary_amount'];
			$info['status']	= $POST['status'];
			$insertpayrollstructureassignid = add_record('payroll_additional_salary
				', $info, $dbcon);

			$query = $dbcon->query("SELECT `id` FROM `payroll_additional_salary`");
			$total_records = $query->num_rows;
			$updateInfo['taxinvoice_start'] = $total_records;
			$updatesalarypayrollstructureassignid = update_record('tbl_invoicetype', $updateInfo,"invoice_type = 'PAYROLL ADDITIONAL SALARY'" , $dbcon);

			if($insertpayrollstructureassignid) {
				$arr['msg']="1";							
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	

		}else if(strtolower($POST['mode']) == "edit") {
			$info['user_id']= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id'] = $POST['series_id'];
			$info['employee_id'] = $_POST['employee_id'];
			$info['salary_component_id'] = $_POST['salary_component_id'];
			$info['payroll_date'] = ($_POST['payroll_date'])?date('Y-m-d', strtotime($_POST['payroll_date'])):'';
			$info['is_recurring_flag'] = ($_POST['is_recurring_flag'])?$_POST['is_recurring_flag']:'No';
			$info['overwrite_salary_structure_amount_flag'] = ($_POST['overwrite_salary_structure_amount_flag'])?$_POST['overwrite_salary_structure_amount_flag']:'No';
			$info['deduct_fulltax_on_selected_payroll_date_flag'] = ($_POST['deduct_fulltax_on_selected_payroll_date_flag'])?$_POST['deduct_fulltax_on_selected_payroll_date_flag']:'No';
			$info['additional_salary_amount'] = $_POST['additional_salary_amount'];
			$info['status']	= $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			$updatepayrollstructureassignid = update_record('payroll_additional_salary', $info,"id=".$POST['eid'] , $dbcon);
		
			if($updatepayrollstructureassignid){
				$arr['msg']="1";
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode'])== "delete") {
			$row=array();
			$info['status'] = 2;
			$updateid=update_record('payroll_additional_salary', $info, "id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['msg']="1";
			else
				$row['msg']="0";
			echo json_encode($row);
		}	
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];
			$info['status'] = ($p_status=='0') ? '1' : '0';
			$updateid = update_record('payroll_additional_salary', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}
		else if(strtolower($POST['mode'])== "get_component_data")
		{
			$ids = $POST['compo_id'];
			$q = $dbcon -> query("select payroll_com.* from payroll_salary_component as payroll_com 
		 		where `payroll_com`.`id`= $ids");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
?>