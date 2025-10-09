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
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			$where='';
			$where.="payrollsettings.status IN (0,1) and payrollsettings.company_id = $companyID".check_user('payrollsettings');
			$appData = array();
			$i=1;
			$aColumns = array('id','calculate_payroll_working_days_based', 'fraction_of_daily_salary_for_half_day', 'max_working_hours_against_timesheet', 'status', 'comp.company_name');
			$sIndexColumn = "payrollsettings.id";
			$isWhere = array($where);
			$sTable = "payroll_settings as payrollsettings";			
			$isJOIN = array("left join tbl_company as comp on comp.company_id = payrollsettings.company_id");
			$hOrder = "payrollsettings.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['id'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['calculate_payroll_working_days_based'];
				$row_data[] = $row['fraction_of_daily_salary_for_half_day'];
				
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){  
					if($edit_btn_per) {
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'. ROOT . HRMS_ROOT . 'payroll_settings_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per) {
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_settings('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
					}
				}
				if($other_btn_per) {
					if($row['status'] == '0')
					{  
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
				$row['res']='';
				$tr = $dbcon -> query("SELECT `id`,`company_id`,`status` FROM `payroll_settings` WHERE `company_id` = '$_SESSION[company_id]'");
					if($tr->num_rows > 0) {
						$r = $tr -> fetch_assoc();
						if($r['status'] != 0) {
							$info['status']=0;
							$updateid=update_record('payroll_settings', $info,"id=".$r['id'] , $dbcon);
							if($updateid)
							{
									$row['msg']='1';
							}
							else
							{
									$row['msg']='0';
							}
						}else{
								$row['msg']='-1';
						}	
				} else {
						// Insert New Settings List
						$info['user_id']	= $_SESSION['user_id'];
						$info['company_id']	= $_SESSION['company_id'];
						$info['calculate_payroll_working_days_based'] = ($POST['calculate_payroll_working_days_based'])?$POST['calculate_payroll_working_days_based']:'leave';
						$info['fraction_of_daily_salary_for_half_day']	= ($POST['fraction_of_daily_salary_for_half_day'])?$POST['fraction_of_daily_salary_for_half_day']:'0.0';
						$info['max_working_hours_against_timesheet']	= ($POST['max_working_hours_against_timesheet'])?$POST['max_working_hours_against_timesheet']:'0.0';
						$info['email_salary_slip_to_employee_flag']	= ($POST['email_salary_slip_to_employee_flag'])?$POST['email_salary_slip_to_employee_flag']:'No';
						$info['include_holidays_in_total_no_of_working_days_flag']	= ($POST['include_holidays_in_total_no_of_working_days_flag'])?$POST['include_holidays_in_total_no_of_working_days_flag']:'No';
						$info['encrypt_salary_slips_in_emails_flag']	= ($POST['encrypt_salary_slips_in_emails_flag'])?$POST['encrypt_salary_slips_in_emails_flag']:'No';
						$info['disable_rounded_total_flag']	= ($POST['disable_rounded_total_flag'])?$POST['disable_rounded_total_flag']:'No';
						$info['status']	= $POST['status'];
						$info['updated_at']	= date("Y-m-d H:i:s");
						$insertsettingsid = add_record('payroll_settings', $info, $dbcon);
				if($insertsettingsid)
				{
					if(strtolower($POST['model'])=="model")
					{
						$query="select * from payroll_settings where id=".$insertsettingsid;
						$rel=mysqli_fetch_assoc($dbcon->query($query));		
						$row = $rel;
						$row['msg']="2"; 
					}
					else
					{
						$row['msg'] ="1";
					}
				}
				else
				{
					$row['msg'] ="0";
				}
				
			}
			echo json_encode($row);
		}else if(strtolower($POST['mode']) == "edit") {
				$info['calculate_payroll_working_days_based'] = ($POST['calculate_payroll_working_days_based'])?$POST['calculate_payroll_working_days_based']:'leave';
				$info['fraction_of_daily_salary_for_half_day']	= ($POST['fraction_of_daily_salary_for_half_day'])?$POST['fraction_of_daily_salary_for_half_day']:'0.0';
				$info['max_working_hours_against_timesheet']	= ($POST['max_working_hours_against_timesheet'])?$POST['max_working_hours_against_timesheet']:'0.0';
				$info['email_salary_slip_to_employee_flag']	= ($_POST['email_salary_slip_to_employee_flag'])?$_POST['email_salary_slip_to_employee_flag']:'No';
				$info['include_holidays_in_total_no_of_working_days_flag']	= ($_POST['include_holidays_in_total_no_of_working_days_flag'])?$_POST['include_holidays_in_total_no_of_working_days_flag']:'No';
				$info['encrypt_salary_slips_in_emails_flag']	= ($_POST['encrypt_salary_slips_in_emails_flag'])?$_POST['encrypt_salary_slips_in_emails_flag']:'No';
				$info['disable_rounded_total_flag']	= ($_POST['disable_rounded_total_flag'])?$_POST['disable_rounded_total_flag']:'No';
				$info['status']	= $POST['status'];
				$info['updated_at']	= date("Y-m-d H:i:s");
				$updatesettingsid = update_record('payroll_settings', $info,"id=".$POST['eid'] , $dbcon);
		
				if($updatesettingsid){	
					$arr['msg']="1";
				}else{
					$arr['msg']="0";
				}
				echo json_encode($arr);	
		}
		else if(strtolower($POST['mode'])== "delete") {
			$row=array();
			$info['status'] = 2;
			$updateid=update_record('payroll_settings', $info, "id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['msg']="1";
			else
				$row['msg']="0";
			
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			
			$updateid = update_record('payroll_settings', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}	
		
?>