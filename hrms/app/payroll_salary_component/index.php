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
			$where.="payrollsalarycomponent.status IN (0,1) and payrollsalarycomponent.company_id = $companyID".check_user('payrollsalarycomponent');
			$appData = array();
			$i=1;
			$aColumns = array('payrollsalarycomponent.id','payrollsalarycomponent.series_id','invoicetype.invoice_format','invoicetype.format_value','invoicetype.end_format_value','salary_component_name','salary_abbr_value','salary_component_type','salary_component_description', 'comp.company_name','payrollsalarycomponent.status');
			$sIndexColumn = "payrollsalarycomponent.id";
			$isWhere = array($where);
			$sTable = "payroll_salary_component as payrollsalarycomponent";			
			$isJOIN = array("left join tbl_company as comp on comp.company_id = payrollsalarycomponent.company_id",
							"left join tbl_invoicetype as invoicetype on invoicetype.invoicetype_id=payrollsalarycomponent.series_id");
			$hOrder = "payrollsalarycomponent.id desc";
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
				$row_data[] = $row['salary_component_name'];
				$row_data[] = $row['salary_abbr_value'];
				if($row['salary_component_type']=='0'){
					$row_data[] = 'Earning';
				}else if($row['salary_component_type']=='1'){
					$row_data[] = 'Deduction';
				}
				$row_data[] = $row['salary_component_description'];
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per){
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'payroll_salary_component_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per){
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payroll_salary_component('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
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
			// Insert New Payroll Salary Component List
			$info['user_id']= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id'] = $POST['series_id'];
			$info['salary_component_name'] = $_POST['salary_component_name'];
			$info['salary_abbr_value'] = $_POST['salary_abbr_value'];
			$info['salary_component_type'] = $_POST['salary_component_type'];
			$info['component_type'] = $_POST['component_type'];
			$info['salary_component_description']	= $_POST['salary_component_description'];
			$info['depend_on_payment_day_flag']	= ($_POST['depend_on_payment_day_flag'])?$_POST['depend_on_payment_day_flag']:'No';
			$info['is_income_tax_component_flag']	= ($_POST['is_income_tax_component_flag'])?$_POST['is_income_tax_component_flag']:'No';
			$info['variable_based_taxable_salary_flag']	= ($_POST['variable_based_taxable_salary_flag'])?$_POST['variable_based_taxable_salary_flag']:'No';
			$info['exempted_from_income_tax_flag']	= ($_POST['exempted_from_income_tax_flag'])?$_POST['exempted_from_income_tax_flag']:'No';
			$info['is_tax_applicable_flag']	= ($_POST['is_tax_applicable_flag'])?$_POST['is_tax_applicable_flag']:'No';
			$info['deduct_fullamount_selected_payroll_flag']	= ($_POST['deduct_fullamount_selected_payroll_flag'])?$_POST['deduct_fullamount_selected_payroll_flag']:'No';
			$info['nearest_interger_flag']	= ($_POST['nearest_interger_flag'])?$_POST['nearest_interger_flag']:'No';
			$info['statistical_component_flag']	= ($_POST['statistical_component_flag'])?$_POST['statistical_component_flag']:'No';
			$info['do_not_include_total_flag']	= ($_POST['do_not_include_total_flag'])?$_POST['do_not_include_total_flag']:'No';
			$info['salary_disable_flag']	= ($_POST['salary_disable_flag'])?$_POST['salary_disable_flag']:'No';
			$info['is_fexible_benefit_flag']	= ($_POST['is_fexible_benefit_flag'])?$_POST['is_fexible_benefit_flag']:'No';
			$info['max_benefit_amount_yearly']	= ($_POST['max_benefit_amount_yearly'])?$_POST['max_benefit_amount_yearly']:'00.00';
			$info['pay_against_benefit_claim_flag']	= ($_POST['pay_against_benefit_claim_flag'])?$_POST['pay_against_benefit_claim_flag']:'No';
			$info['only_tax_impect_flag']	= ($_POST['only_tax_impect_flag'])?$_POST['only_tax_impect_flag']:'No';
			$info['create_separate_payment_entry_flag']	= ($_POST['create_separate_payment_entry_flag'])?$_POST['create_separate_payment_entry_flag']:'No';
			$info['salary_component_condition']	= ($_POST['salary_component_condition'])?$_POST['salary_component_condition']:'';
			$info['salary_component_amount']	= ($_POST['salary_component_amount'])?$_POST['salary_component_amount']:'00.00';
			$info['salary_component_amount_flag']	= ($_POST['salary_component_amount_flag'])?$_POST['salary_component_amount_flag']:'No';
			$info['salary_component_amount_formula']	= ($_POST['salary_component_amount_formula'])?$_POST['salary_component_amount_formula']:'';
			$info['status']	= $POST['status'];
			$insertpayrollcomponentid = add_record('payroll_salary_component', $info, $dbcon);

			$query = $dbcon->query("SELECT `id` FROM `payroll_salary_component`");
			$total_records = $query->num_rows;
			$updateInfo['taxinvoice_start'] = $total_records;
			$updatesalarycomponentid = update_record('tbl_invoicetype', $updateInfo,"invoice_type = 'PAYROLL SALARY COMPONENT'" , $dbcon);

			$info_update['status']	= 0;
			$info_update['payroll_salary_component_id']	= $insertpayrollcomponentid;
			$updatesalaryaccountcomponentsid = update_record('payroll_salary_account_component', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			
			if($insertpayrollcomponentid) {
				$arr['msg']="1";							
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	

		}else if(strtolower($POST['mode']) == "edit") {
			$info['user_id']= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id'] = $POST['series_id'];
			$info['salary_component_name'] = $_POST['salary_component_name'];
			$info['salary_abbr_value'] = $_POST['salary_abbr_value'];
			$info['salary_component_type'] = $_POST['salary_component_type'];
			$info['component_type'] = $_POST['component_type'];
			$info['salary_component_description']	= $_POST['salary_component_description'];
			$info['depend_on_payment_day_flag']	= ($_POST['depend_on_payment_day_flag'])?$_POST['depend_on_payment_day_flag']:'No';
			$info['is_income_tax_component_flag']	= ($_POST['is_income_tax_component_flag'])?$_POST['is_income_tax_component_flag']:'No';
			$info['variable_based_taxable_salary_flag']	= ($_POST['variable_based_taxable_salary_flag'])?$_POST['variable_based_taxable_salary_flag']:'No';
			$info['exempted_from_income_tax_flag']	= ($_POST['exempted_from_income_tax_flag'])?$_POST['exempted_from_income_tax_flag']:'No';
			$info['is_tax_applicable_flag']	= ($_POST['is_tax_applicable_flag'])?$_POST['is_tax_applicable_flag']:'No';
			$info['deduct_fullamount_selected_payroll_flag']	= ($_POST['deduct_fullamount_selected_payroll_flag'])?$_POST['deduct_fullamount_selected_payroll_flag']:'No';
			$info['nearest_interger_flag']	= ($_POST['nearest_interger_flag'])?$_POST['nearest_interger_flag']:'No';
			$info['statistical_component_flag']	= ($_POST['statistical_component_flag'])?$_POST['statistical_component_flag']:'No';
			$info['do_not_include_total_flag']	= ($_POST['do_not_include_total_flag'])?$_POST['do_not_include_total_flag']:'No';
			$info['salary_disable_flag']	= ($_POST['salary_disable_flag'])?$_POST['salary_disable_flag']:'No';
			$info['is_fexible_benefit_flag']	= ($_POST['is_fexible_benefit_flag'])?$_POST['is_fexible_benefit_flag']:'No';
			$info['max_benefit_amount_yearly']	= ($_POST['max_benefit_amount_yearly'])?$_POST['max_benefit_amount_yearly']:'00.00';
			$info['pay_against_benefit_claim_flag']	= ($_POST['pay_against_benefit_claim_flag'])?$_POST['pay_against_benefit_claim_flag']:'No';
			$info['only_tax_impect_flag']	= ($_POST['only_tax_impect_flag'])?$_POST['only_tax_impect_flag']:'No';
			$info['create_separate_payment_entry_flag']	= ($_POST['create_separate_payment_entry_flag'])?$_POST['create_separate_payment_entry_flag']:'No';
			$info['salary_component_condition']	= ($_POST['salary_component_condition'])?$_POST['salary_component_condition']:'';
			$info['salary_component_amount']	= ($_POST['salary_component_amount'])?$_POST['salary_component_amount']:'00.00';
			$info['salary_component_amount_flag']	= ($_POST['salary_component_amount_flag'])?$_POST['salary_component_amount_flag']:'No';
			$info['salary_component_amount_formula']	= ($_POST['salary_component_amount_formula'])?$_POST['salary_component_amount_formula']:'';
			$info['status']	= $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			$updatepayrollcomponentid = update_record('payroll_salary_component', $info,"id=".$POST['eid'] , $dbcon);
		
			if($updatepayrollcomponentid){
				$arr['msg']="1";
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode'])== "delete") {
			$row=array();
			$info['status'] = 2;
			$updateid=update_record('payroll_salary_component', $info, "id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['msg']="1";
			else
				$row['msg']="0";
			echo json_encode($row);
		}	
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];
			$info['status'] = ($p_status=='0') ? '1' : '0';
			$updateid = update_record('payroll_salary_component', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}
		else if(strtolower($POST['mode']) == "load_tempoutward") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['psc_id'])){
				$query="select salaryaccountcomponent.*, comp.company_name, payacc.account_name from payroll_salary_account_component as salaryaccountcomponent 
				left join tbl_company as comp on comp.company_id = salaryaccountcomponent.company_id
				left join payroll_account as payacc on payacc.id = salaryaccountcomponent.payroll_account_id
		 		where `salaryaccountcomponent`.`status` = 3 and `salaryaccountcomponent`.`user_id` = $userID and `salaryaccountcomponent`.`company_id` = $companyID";
			}else{
				 $query="select salaryaccountcomponent.*, comp.company_name, payacc.account_name from payroll_salary_account_component as salaryaccountcomponent
				left join tbl_company as comp on comp.company_id = salaryaccountcomponent.company_id
				left join payroll_account as payacc on payacc.id = salaryaccountcomponent.payroll_account_id
		 		where `salaryaccountcomponent`.`status` = 0 and `salaryaccountcomponent`.`payroll_salary_component_id`=".$POST['psc_id']." and `salaryaccountcomponent`.`user_id` = $userID and `salaryaccountcomponent`.`company_id` = ".$companyID;
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th width="25%" class="text-center">Company</th>
							<th width="25%" class="text-center">Default Account</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$rel['id'].'" >
							<td data-label="Company" style="vertical-align:top;" class="text-center">';
									if(empty($rel['company_name'])){
										echo '-';
									}else{
										echo $rel['company_name'];
									}
							echo'</td>
							<td data-label="Default Account" style="vertical-align:top;" class="text-center">
								'.$rel['account_name'].'
							</td>
							<td data-label="ACTION" style="vertical-align:top">
								<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
								<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
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
		else if(strtolower($POST['mode']) == "fieldadd") {
			$info1['user_id'] =  $_SESSION['user_id'];
			$info1['company_id'] = $_SESSION['company_id'];
			$info1['payroll_account_company_id']	= $POST['payroll_account_company_id'];
			$info1['payroll_account_id']	= $POST['payroll_account_id'];
			if(empty($POST['edit_id'])){
				if(empty($POST['eid'])){
					$info1['status']	= '3';
				}else{
					$info1['status']	= '0';
					$info1['payroll_salary_component_id'] = $POST['eid'];
				}
			}else{
				if(empty($POST['eid'])){
					$info1['status']	= '3';
				}else{
					$info1['status']	= '0';
				}
			}
			$table='payroll_salary_account_component';
			$tableid='id';
			
			if(empty($POST['edit_id'])){
				$inserid=add_record($table, $info1, $dbcon);
			}else{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
		
		}
		else if(strtolower($POST['mode'])== "preedit")
		{
			$ids = $POST['id'];
			$q = $dbcon -> query("select * from payroll_salary_account_component as salaryaccountcomponent 
				left join tbl_company as comp on comp.company_id = salaryaccountcomponent.company_id
		 		where id = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("payroll_salary_account_component", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
?>