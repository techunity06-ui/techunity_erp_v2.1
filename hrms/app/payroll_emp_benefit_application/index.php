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
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['mode']) == "fetch") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			$where='';
			$appData = array();
			$i=1;
			$aColumns = array('payrollempbeneapp.id','empusers.l_name','payrollper.payroll_period_name','payrollempbeneapp.series_id','payrollempbeneapp.employee_id','payrollempbeneapp.payroll_period_id','com.company_name','payrollempbeneapp.status','payrollempbeneapp.benefit_application_date');
			$sIndexColumn = "payrollempbeneapp.id";
			$isWhere = array("payrollempbeneapp.status IN (0,1) and payrollempbeneapp.company_id = $companyID".check_user('payrollempbeneapp'));
			$sTable = "payroll_emp_benefit_application as payrollempbeneapp";			
			$isJOIN = array('left join tbl_company as com on com.company_id=payrollempbeneapp.company_id',
							'left join tbl_ledger as empusers on empusers.l_id=payrollempbeneapp.employee_id',
							'left join payroll_period as payrollper on payrollper.id=payrollempbeneapp.payroll_period_id');
			$hOrder = "payrollempbeneapp.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['series_id'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['l_name'];
				$row_data[] = $row['payroll_period_name'];
				$row_data[] = $row['benefit_application_date'];
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per) {
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'payroll_emp_benefit_application_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per) {
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payroll_emp_benefit_application('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
					}
				}
				if($row['status'] == '0')
				{  
					$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-window-close'></i></a>";	
				} else {
					$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-check-square-o'></i></a>";
				}
					
				$row_data[] = $edit_btn.' '.$delete_btn. ' '. $change_status;
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id'] = $_POST['series_id'];
			$info['employee_id'] = $_POST['employee_id'];
			$info['payroll_period_id'] = $_POST['payroll_period_id'];
			$info['benefit_application_date'] = ($_POST['benefit_application_date'])?date('Y-m-d',strtotime($_POST['benefit_application_date'])):'';
			$info['max_benefits_amount'] = ($_POST['max_benefits_amount'])?$_POST['max_benefits_amount']:'0.00';
			$info['remaining_benefit_amount'] = ($_POST['remaining_benefit_amount'])?$_POST['remaining_benefit_amount']:'0.00';
			$info['status'] = $POST['status'];
			$insertbenefitappid=add_record('payroll_emp_benefit_application', $info, $dbcon);

			updateSeries($dbcon, 'id', 'payroll_emp_benefit_application', 'PAYROLL EMP BENEFIT APPLI');
					
			$info_update['status']	= 0;
			$info_update['payroll_emp_benefit_appli_id']	= $insertbenefitappid;
			$updateid = update_record('payroll_emp_benefit_applied', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
					
			if($insertbenefitappid){	
				$arr['msg']="1";
				$arr['eid']=$insertbenefitappid;
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);
		}		
		else if(strtolower($POST['mode']) == "edit") {
			$info['series_id'] = $_POST['series_id'];
			$info['employee_id'] = $_POST['employee_id'];
			$info['payroll_period_id'] = $_POST['payroll_period_id'];
			$info['benefit_application_date'] = ($_POST['benefit_application_date'])?date('Y-m-d',strtotime($_POST['benefit_application_date'])):'';
			$info['max_benefits_amount'] = ($_POST['max_benefits_amount'])?$_POST['max_benefits_amount']:'0.00';
			$info['remaining_benefit_amount'] = ($_POST['remaining_benefit_amount'])?$_POST['remaining_benefit_amount']:'0.00';
			$info['status'] = $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			$updateid=update_record('payroll_emp_benefit_application', $info,"id=".$POST['eid'] , $dbcon);
			
			if($updateid){	
				$arr['msg']="update";
				$arr['eid']=$POST['eid'];
			}else{
				if($updateid == 0){
					$arr['msg']="update";
					$arr['eid']=$POST['eid'];
				}else{
					$arr['msg']=0;
				}
			}
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['status']	= 2;
			$info1['status'] = 2;
			$updatebenefitid=update_record('payroll_emp_benefit_application', $info,"id=".$POST['eid'] , $dbcon);	
			$updatebenefitappliid=update_record('payroll_emp_benefit_applied', $info1,"payroll_emp_benefit_appli_id=".$POST['eid'] , $dbcon);			
			if($updatebenefitappliid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "fieldadd") {
				$info1['user_id'] =  $_SESSION['user_id'];
				$info1['company_id'] = $_SESSION['company_id'];
				$info1['earning_component_id'] = $_POST['earning_component_id'];
				$info1['maximum_benefit_amount']	= $_POST['maximum_benefit_amount'];
				$info1['earning_amount']	= $_POST['earning_amount'];
				if(empty($POST['edit_id'])){
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
						$info1['payroll_emp_benefit_appli_id'] = $POST['eid'];
					}
				}else{
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
					}
				}
				$table='payroll_emp_benefit_applied';
				$tableid='id';
				
				if(empty($POST['edit_id'])){
					$inserid=add_record($table, $info1, $dbcon);
				}else{
					$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
				}
			
		}
		else if(strtolower($POST['mode']) == "load_tempoutward") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['benefit_id'])){
				$query="select payempbeneapplied.*,payrollcomp.salary_component_name,payrollcomp.max_benefit_amount_yearly from payroll_emp_benefit_applied as payempbeneapplied 
				left join tbl_company as comp on comp.company_id = payempbeneapplied.company_id
				left join payroll_salary_component as payrollcomp on payrollcomp.id = payempbeneapplied.earning_component_id
		 		where `payempbeneapplied`.`status` = 3 and `payempbeneapplied`.`user_id` = $userID and `payempbeneapplied`.`company_id` = $companyID";
			}else{
				 $query="select payempbeneapplied.*,payrollcomp.salary_component_name,payrollcomp.max_benefit_amount_yearly from payroll_emp_benefit_applied as payempbeneapplied 
				left join tbl_company as comp on comp.company_id = payempbeneapplied.company_id
				left join payroll_salary_component as payrollcomp on payrollcomp.id = payempbeneapplied.earning_component_id
		 		where `payempbeneapplied`.`status` = 0 and `payempbeneapplied`.`payroll_emp_benefit_appli_id`=".$POST['benefit_id']." and `payempbeneapplied`.`user_id` = $userID and `payempbeneapplied`.`company_id` = ".$companyID;
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th width="15%" class="text-center">Earning Component</th>
							<th width="15%" class="text-center">Max Benefit Amount</th>
							<th width="15%" class="text-center">Amount</th>
							<th width="5%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					 echo '<tr id="fieldtr'.$rel['id'].'" >
							<td data-label="Earning Component" style="vertical-align:top;" class="text-center">';
									if(empty($rel['salary_component_name'])){
										echo '-';
									}else{
										echo $rel['salary_component_name'];
									}
							echo'</td>
							<td data-label="Max Benefit Amount" style="vertical-align:top;" class="text-center">
								'.$rel['maximum_benefit_amount'].'
							</td>
							<td data-label="Amount" style="vertical-align:top;" class="text-center">
								'.$rel['earning_amount'].'
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
		else if(strtolower($POST['mode'])== "preedit")
		{
			$ids = $POST['id'];
			$q = $dbcon -> query("select * from payroll_emp_benefit_applied as payrollempbeneapplied 
				left join tbl_company as comp on comp.company_id = payrollempbeneapplied.company_id
		 		where id = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("payroll_emp_benefit_applied", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}	
		else if(strtolower($POST['mode'])== "get_earning_component_data")
		{
			$ids = $POST['component_id'];
			$q = $dbcon -> query("select payrollsalcomp.* from payroll_salary_component as payrollsalcomp
		 		where `payrollsalcomp`.`id`= $ids");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			$updateid = update_record('payroll_emp_benefit_application', $info,"id=".$POST['eid'] , $dbcon);
			
			echo ($updateid) ? "1" : "0";
		}
?>