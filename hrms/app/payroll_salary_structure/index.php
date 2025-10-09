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
			$where.="payrollsalarystructure.status IN (0,1) and payrollsalarystructure.company_id = $companyID".check_user('payrollsalarystructure');
			$appData = array();
			$i=1;
			$aColumns = array('payrollsalarystructure.id','payrollsalarystructure.series_id','invoicetype.invoice_format','invoicetype.format_value','invoicetype.end_format_value','salary_structure_name','salary_structure_status', 'comp.company_name','payrollsalarystructure.status');
			$sIndexColumn = "payrollsalarystructure.id";
			$isWhere = array($where);
			$sTable = "payroll_salary_structure as payrollsalarystructure";			
			$isJOIN = array("left join tbl_company as comp on comp.company_id = payrollsalarystructure.company_id",
							"left join tbl_invoicetype as invoicetype on invoicetype.invoicetype_id=payrollsalarystructure.series_id");
			$hOrder = "payrollsalarystructure.id desc";
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
				$row_data[] = $row['salary_structure_name'];
				if($row['salary_structure_status']=='Yes'){
					$row_data[] = 'Yes';
				}else if($row['salary_structure_status']=='No'){
					$row_data[] = 'No';
				}
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per){
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'payroll_salary_structure_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per){
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payroll_salary_structure('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
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
			// Insert New Payroll Salary Structure List
			$info['user_id']= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id'] = $POST['series_id'];
			$info['salary_structure_status'] = $_POST['salary_structure_status'];
			$info['salary_structure_name'] = $_POST['salary_structure_name'];
			$info['payroll_frequency'] = $_POST['payroll_frequency'];
			$info['letter_head_id'] = $_POST['letter_head_id'];
			$info['salary_slip_timesheet_flag']	= ($_POST['salary_slip_timesheet_flag'])?$_POST['salary_slip_timesheet_flag']:'No';
			$info['leave_encashment_amount_per_day']	= ($_POST['leave_encashment_amount_per_day'])?$_POST['leave_encashment_amount_per_day']:'No';
			$info['max_benefits_amount'] = $_POST['max_benefits_amount'];
			$info['salary_component_id'] = ($_POST['salary_component_id'])?$_POST['salary_component_id']:'';
			$info['salary_component_hour_rate'] = ($_POST['salary_component_hour_rate'])?$_POST['salary_component_hour_rate']:'';
			$info['payment_mode_id'] = $_POST['payment_mode_id'];
			$info['payment_account_id'] = $_POST['payment_account_id'];
			$info['status']	= $POST['status'];
			$insertpayrollstructureid = add_record('payroll_salary_structure', $info, $dbcon);

			$query = $dbcon->query("SELECT `id` FROM `payroll_salary_structure`");
			$total_records = $query->num_rows;
			$updateInfo['taxinvoice_start'] = $total_records;
			$updatesalarystructureid = update_record('tbl_invoicetype', $updateInfo,"invoice_type = 'PAYROLL SALARY STRUCTURE'" , $dbcon);

			$info_update['status']	= 0;
			$info_update['payroll_salary_structure_id']	= $insertpayrollstructureid;
			$updatesalarybreakupearningsid=update_record('payroll_salary_breakup_earnings', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			$updatesalarybreakupdeductionsid=update_record('payroll_salary_breakup_deductions', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);

			if($insertpayrollstructureid) {
				$arr['msg']="1";							
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	

		}else if(strtolower($POST['mode']) == "edit") {
			$info['user_id']= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id'] = $POST['series_id'];
			$info['salary_structure_status'] = $_POST['salary_structure_status'];
			$info['salary_structure_name'] = $_POST['salary_structure_name'];
			$info['payroll_frequency'] = $_POST['payroll_frequency'];
			$info['letter_head_id'] = $_POST['letter_head_id'];
			$info['salary_slip_timesheet_flag']	= ($_POST['salary_slip_timesheet_flag'])?$_POST['salary_slip_timesheet_flag']:'No';
			$info['leave_encashment_amount_per_day']	= ($_POST['leave_encashment_amount_per_day'])?$_POST['leave_encashment_amount_per_day']:'No';
			$info['max_benefits_amount'] = $_POST['max_benefits_amount'];
			$info['salary_component_id'] = ($_POST['salary_component_id'])?$_POST['salary_component_id']:'';
			$info['salary_component_hour_rate'] = ($_POST['salary_component_hour_rate'])?$_POST['salary_component_hour_rate']:'';
			$info['payment_mode_id'] = $_POST['payment_mode_id'];
			$info['payment_account_id'] = $_POST['payment_account_id'];
			$info['status']	= $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			$updatepayrollstructureid = update_record('payroll_salary_structure', $info,"id=".$POST['eid'] , $dbcon);
		
			if($updatepayrollstructureid){
				$arr['msg']="1";
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode'])== "delete") {
			$row=array();
			$info['status'] = 2;
			$updateid=update_record('payroll_salary_structure', $info, "id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['msg']="1";
			else
				$row['msg']="0";
			echo json_encode($row);
		}	
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];
			$info['status'] = ($p_status=='0') ? '1' : '0';
			$updateid = update_record('payroll_salary_structure', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}
		else if(strtolower($POST['mode'])== "payroll_salary_structure_earnings"){
			$ids = $POST['comp_earn_id'];
			$q = $dbcon -> query("select * from payroll_salary_component as payrollsalarycomponent
				left join tbl_company as comp on comp.company_id = payrollsalarycomponent.company_id
		 		where id = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "payroll_salary_structure_deductions"){
			$ids = $POST['comp_dedu_id'];
			$q = $dbcon -> query("select * from payroll_salary_component as payrollsalarycomponent
				left join tbl_company as comp on comp.company_id = payrollsalarycomponent.company_id
		 		where id = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "load_earnings_ward") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['psse_id'])){
				$query="select salarybreakupearnings.*, comp.company_name, salarycompo.salary_component_name from payroll_salary_breakup_earnings as salarybreakupearnings 
				left join tbl_company as comp on comp.company_id = salarybreakupearnings.company_id
				left join payroll_salary_component as salarycompo on salarycompo.id = salarybreakupearnings.payroll_component_name_earnings
		 		where `salarybreakupearnings`.`status` = 3 and `salarybreakupearnings`.`user_id` = $userID and `salarybreakupearnings`.`company_id` = $companyID";
			}else{
				 $query="select salarybreakupearnings.*, comp.company_name, salarycompo.salary_component_name from payroll_salary_breakup_earnings as salarybreakupearnings
				left join tbl_company as comp on comp.company_id = salarybreakupearnings.company_id
				left join payroll_salary_component as salarycompo on salarycompo.id = salarybreakupearnings.payroll_component_name_earnings
		 		where `salarybreakupearnings`.`status` = 0 and `salarybreakupearnings`.`payroll_salary_structure_id`=".$POST['psse_id']." and `salarybreakupearnings`.`user_id` = $userID and `salarybreakupearnings`.`company_id` = ".$companyID;
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th width="15%" class="text-center">Component</th>
							<th width="15%" class="text-center">Abbr</th>
							<th width="15%" class="text-center">Amount</th>
							<th width="15%" class="text-center">Statistic</th>
							<th width="15%" class="text-center">Formula</th>
							<th width="15%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$rel['id'].'" >
							<td data-label="Component" style="vertical-align:top;" class="text-center">';
									if(empty($rel['salary_component_name'])){
										echo '-';
									}else{
										echo $rel['salary_component_name'];
									}
							echo'</td>
							<td data-label="Abbr" style="vertical-align:top;" class="text-center">
								'.$rel['payroll_component_abbr_earnings'].'
							</td>
							<td data-label="Amount" style="vertical-align:top;" class="text-center">
								'.$rel['payroll_component_amount_earnings'].'
							</td>
							<td data-label="Amount" style="vertical-align:top;" class="text-center">
								'.$rel['payroll_component_statistic_flag_earnings'].'
							</td>
							<td data-label="Amount" style="vertical-align:top;" class="text-center">
								'.$rel['payroll_component_formula_earnings'].'
							</td>
							<td data-label="ACTION" style="vertical-align:top">
								<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_earnings_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
								<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_earnings_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
							</td>	
					</tr>';
					$i++;
				}
			}else{
					echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '</table>			 
					</div>
                        </div>';
		}
		else if(strtolower($POST['mode']) == "field_earnings_add") {
			$info1['user_id'] =  $_SESSION['user_id'];
			$info1['company_id'] = $_SESSION['company_id'];
			$info1['payroll_component_name_earnings']	= $POST['payroll_component_name_earnings'];
			$info1['payroll_component_abbr_earnings']	= $POST['payroll_component_abbr_earnings'];
			$info1['payroll_component_amount_earnings']	= $POST['payroll_component_amount_earnings'];
			$info1['payroll_component_statistic_flag_earnings']	= $POST['payroll_component_statistic_flag_earnings'];
			$info1['payroll_component_formula_earnings']	= ($_POST['payroll_component_formula_earnings'])?$_POST['payroll_component_formula_earnings']:'';
			if(empty($POST['edit_id'])){
				if(empty($POST['eid'])){
					$info1['status']	= '3';
				}else{
					$info1['status']	= '0';
					$info1['payroll_salary_structure_id'] = $POST['eid'];
				}
			}else{
				if(empty($POST['eid'])){
					$info1['status']	= '3';
				}else{
					$info1['status']	= '0';
				}
			}
			$table='payroll_salary_breakup_earnings';
			$tableid='id';
			
			if(empty($POST['edit_id'])){
				$inserid=add_record($table, $info1, $dbcon);
			}else{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
		
		}
		else if(strtolower($POST['mode'])== "pre_earnings_edit")
		{
			$ids = $POST['id'];
			$q = $dbcon -> query("select * from payroll_salary_breakup_earnings as salarybreakupearnings 
				left join tbl_company as comp on comp.company_id = salarybreakupearnings.company_id
		 		where id = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_earnings_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("payroll_salary_breakup_earnings", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "load_deductions_ward") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['pssd_id'])){
				$query="select salarybreakupdeductions.*, comp.company_name, salarycompo.salary_component_name from payroll_salary_breakup_deductions as salarybreakupdeductions 
				left join tbl_company as comp on comp.company_id = salarybreakupdeductions.company_id
				left join payroll_salary_component as salarycompo on salarycompo.id = salarybreakupdeductions.payroll_component_name_deductions
		 		where `salarybreakupdeductions`.`status` = 3 and `salarybreakupdeductions`.`user_id` = $userID and `salarybreakupdeductions`.`company_id` = $companyID";
			}else{
				 $query="select salarybreakupdeductions.*, comp.company_name, salarycompo.salary_component_name from payroll_salary_breakup_deductions as salarybreakupdeductions
				left join tbl_company as comp on comp.company_id = salarybreakupdeductions.company_id
				left join payroll_salary_component as salarycompo on salarycompo.id = salarybreakupdeductions.payroll_component_name_deductions
		 		where `salarybreakupdeductions`.`status` = 0 and `salarybreakupdeductions`.`payroll_salary_structure_id`=".$POST['pssd_id']." and `salarybreakupdeductions`.`user_id` = $userID and `salarybreakupdeductions`.`company_id` = ".$companyID;
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th width="15%" class="text-center">Component</th>
							<th width="15%" class="text-center">Abbr</th>
							<th width="15%" class="text-center">Amount</th>
							<th width="15%" class="text-center">Statistic</th>
							<th width="15%" class="text-center">Formula</th>
							<th width="15%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$rel['id'].'" >
							<td data-label="Component" style="vertical-align:top;" class="text-center">';
									if(empty($rel['salary_component_name'])){
										echo '-';
									}else{
										echo $rel['salary_component_name'];
									}
							echo'</td>
							<td data-label="Abbr" style="vertical-align:top;" class="text-center">
								'.$rel['payroll_component_abbr_deductions'].'
							</td>
							<td data-label="Amount" style="vertical-align:top;" class="text-center">
								'.$rel['payroll_component_amount_deductions'].'
							</td>
							<td data-label="Amount" style="vertical-align:top;" class="text-center">
								'.$rel['payroll_component_statistic_flag_deductions'].'
							</td>
							<td data-label="Amount" style="vertical-align:top;" class="text-center">
								'.$rel['payroll_component_formula_deductions'].'
							</td>
							<td data-label="ACTION" style="vertical-align:top">
								<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_deductions_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
								<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_deductions_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
							</td>	
					</tr>';
					$i++;
				}
			}else{
					echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '</table>			 
					</div>
                        </div>';
		}
		else if(strtolower($POST['mode']) == "field_deductions_add") {
			$info1['user_id'] =  $_SESSION['user_id'];
			$info1['company_id'] = $_SESSION['company_id'];
			$info1['payroll_component_name_deductions']	= $POST['payroll_component_name_deductions'];
			$info1['payroll_component_abbr_deductions']	= $POST['payroll_component_abbr_deductions'];
			$info1['payroll_component_amount_deductions']	= $POST['payroll_component_amount_deductions'];
			$info1['payroll_component_statistic_flag_deductions']	= $POST['payroll_component_statistic_flag_deductions'];
			$info1['payroll_component_formula_deductions']	= ($_POST['payroll_component_formula_deductions'])?$_POST['payroll_component_formula_deductions']:'';
			if(empty($POST['edit_id'])){
				if(empty($POST['eid'])){
					$info1['status']	= '3';
				}else{
					$info1['status']	= '0';
					$info1['payroll_salary_structure_id'] = $POST['eid'];
				}
			}else{
				if(empty($POST['eid'])){
					$info1['status']	= '3';
				}else{
					$info1['status']	= '0';
				}
			}
			$table='payroll_salary_breakup_deductions';
			$tableid='id';
			
			if(empty($POST['edit_id'])){
				$inserid=add_record($table, $info1, $dbcon);
			}else{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
		
		}
		else if(strtolower($POST['mode'])== "pre_deductions_edit")
		{
			$ids = $POST['id'];
			$q = $dbcon -> query("select * from payroll_salary_breakup_deductions as salarybreakupdeductions 
				left join tbl_company as comp on comp.company_id = salarybreakupdeductions.company_id
		 		where id = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_deductions_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("payroll_salary_breakup_deductions", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
?>