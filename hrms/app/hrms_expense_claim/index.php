<?php
session_start();
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../../include/function_database_query.php");
include_once("../../../include/common_functions.php");
include_once("../../../include/hrms_common_functions.php");

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
			$aColumns = array('expenseclaimlist.id','led.l_name','lede.l_name as expense_approver_name','expenseclaimlist.posting_date','com.company_name','expenseclaimlist.status');
			$sIndexColumn = "expenseclaimlist.id";
			$isWhere = array("expenseclaimlist.status IN (0,1) and expenseclaimlist.company_id = $companyID".check_user('expenseclaimlist'));
			$sTable = "hrms_emp_expense_claim as expenseclaimlist";			
			$isJOIN = array('left join tbl_company as com on com.company_id=expenseclaimlist.company_id','left join tbl_ledger as led on led.l_id=expenseclaimlist.employee_id','left join tbl_ledger as lede on lede.l_id=expenseclaimlist.expense_approver_id');
			$hOrder = "expenseclaimlist.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['l_name'];
				$row_data[] = $row['expense_approver_name'];
				$row_data[] = $row['posting_date'];
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){
					if($edit_btn_per) {
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'. ROOT . HRMS_ROOT . 'hrms_expense_claim_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per) {
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_hrms_expense_claim('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
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
				$row_data[] = $edit_btn.' '.$delete_btn.' '. $change_status; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id']	= $POST['series_id'];
			$info['employee_id'] = $POST['employee_id'];
			$info['expense_approver_id'] = $POST['expense_approver_id'];
			$info['approve_status'] = $POST['approve_status'];
			$info['is_paid_flag'] = ($POST['is_paid_flag'])?$POST['is_paid_flag']:'No';
			$info['posting_date'] = date('Y-m-d', strtotime($_POST['posting_date']));
			$info['task_detail'] = $POST['task_detail'];
			$info['remark_description'] = $POST['remark_description'];
			$info['clearance_date'] = date('Y-m-d', strtotime($_POST['clearance_date']));
			$info['payable_account_id'] = $POST['payable_account_id'];
			$info['mode_of_payment_id'] = $POST['mode_of_payment_id'];
			$info['project_id'] = $POST['project_id'];
			$info['cost_center_id'] = $POST['cost_center_id'];
			$info['status'] = $POST['status'];

			$query="SELECT SUM(exp_tax_total) as grand_total FROM `hrms_emp_expense_tax_charge` where status = 3 and user_id = ".$_SESSION['user_id']."";
			$result = $dbcon->query($query);
			$totalSUM = $result->fetch_assoc();
			$info['total_tax_charges_amount'] = $totalSUM['grand_total'];
			$info['expense_grand_total'] = $totalSUM['grand_total'];

			$insertexpenseid=add_record('hrms_emp_expense_claim', $info, $dbcon);

			updateSeries($dbcon, 'id', 'hrms_emp_expense_claim', 'EMPLOYEE CLAIM');
					
			$info_update['status']	= 0;
			$info_update['emp_exp_claim_id']	= $insertexpenseid;
			$updateempexpenseid=update_record('hrms_emp_expenses', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			$updateexpensetaxchargeid=update_record('hrms_emp_expense_tax_charge', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			$updateexpenseadvpaymentsid=update_record('hrms_emp_expense_adv_payments', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
		
			if($insertexpenseid){	
				$arr['msg']="1";
				$arr['eid']=$insertexpenseid;
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);
		}		
		else if(strtolower($POST['mode']) == "edit") {
			$info['series_id']	= $POST['series_id'];
			$info['employee_id'] = $POST['employee_id'];
			$info['expense_approver_id'] = $POST['expense_approver_id'];
			$info['approve_status'] = $POST['approve_status'];
			$info['is_paid_flag'] = ($POST['is_paid_flag'])?$POST['is_paid_flag']:'No';
			$info['posting_date'] = date('Y-m-d', strtotime($_POST['posting_date']));
			$info['task_detail'] = $POST['task_detail'];
			$info['remark_description'] = $POST['remark_description'];
			$info['clearance_date'] = date('Y-m-d', strtotime($_POST['clearance_date']));
			$info['payable_account_id'] = $POST['payable_account_id'];
			$info['mode_of_payment_id'] = $POST['mode_of_payment_id'];
			$info['project_id'] = $POST['project_id'];
			$info['cost_center_id'] = $POST['cost_center_id'];
			$info['status'] = $POST['status'];

			$query="SELECT SUM(exp_tax_total) as grand_total FROM `hrms_emp_expense_tax_charge` where status = 0 and user_id = ".$_SESSION['user_id']." and emp_exp_claim_id = ".$POST['eid']."";
			$result = $dbcon->query($query);
			$totalSUM = $result->fetch_assoc();
			$info['total_tax_charges_amount'] = $totalSUM['grand_total'];
			$info['expense_grand_total'] = $totalSUM['grand_total'];
			
			$updateid=update_record('hrms_emp_expense_claim', $info,"id=".$POST['eid'] , $dbcon);
			
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
			$updateexpenseid=update_record('hrms_emp_expense_claim', $info,"id=".$POST['eid'] , $dbcon);	
			$updateempexpenseid=update_record('hrms_emp_expenses', $info,"emp_exp_claim_id=".$POST['eid'] , $dbcon);
			$updateexpensetaxchargeid=update_record('hrms_emp_expense_tax_charge', $info,"emp_exp_claim_id=".$POST['eid'] , $dbcon);
			$updateexpenseadvpaymentsid=update_record('hrms_emp_expense_adv_payments', $info,"emp_exp_claim_id=".$POST['eid'] , $dbcon);				
			if($updateexpenseid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "fieldexpenseadd") {
				$info1['user_id'] =  $_SESSION['user_id'];
				$info1['company_id'] = $_SESSION['company_id'];
				$info1['expense_date']	= date('Y-m-d', strtotime($POST['expense_date']));
				$info1['expense_claim_type_id']	= $POST['expense_claim_type_id'];
				$info1['expense_description']	= stripslashes($POST['expense_description']);
				$info1['expense_amount']	= $POST['expense_amount'];
				$info1['expense_sanctioned_amount']	= $POST['expense_sanctioned_amount'];
				
				if(empty($POST['edit_id'])){
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
						$info1['emp_exp_claim_id'] = $POST['eid'];
					}
				}else{
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
					}
				}
				$table='hrms_emp_expenses';
				$tableid='id';
				
				if(empty($POST['edit_id'])){
					$inserid=add_record($table, $info1, $dbcon);
				}else{
					$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
				}
			
		}
		else if(strtolower($POST['mode']) == "fieldexpensetaxchargeadd") {
				$info1['user_id'] =  $_SESSION['user_id'];
				$info1['company_id'] = $_SESSION['company_id'];
				$info1['account_head_id']	= $POST['account_head_id'];
				$info1['exp_tax_rate']	= $POST['exp_tax_rate'];
				$info1['exp_tax_amount']	= $POST['exp_tax_amount'];
				$info1['exp_tax_total']	= $POST['exp_tax_total'];
				if(empty($POST['edit_id'])){
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
						$info1['emp_exp_claim_id'] = $POST['eid'];
					}
				}else{
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
					}
				}
				$table='hrms_emp_expense_tax_charge';
				$tableid='id';
				
				if(empty($POST['edit_id'])){
					$inserid=add_record($table, $info1, $dbcon);
				}else{
					$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
					$inserid=$POST['edit_id'];
				}
			
		}
		else if(strtolower($POST['mode']) == "fieldadvancepaymentadd") {
				$info1['user_id'] =  $_SESSION['user_id'];
				$info1['company_id'] = $_SESSION['company_id'];
				$info1['emp_advance_id']	= $POST['emp_advance_id'];
				$info1['posting_date']	= date('Y-m-d', strtotime($_POST['advance_posting_date']));
				$info1['advance_paid_amount']	= $POST['advance_paid_amount'];
				$info1['unclaim_amount']	= $POST['unclaim_amount'];
				$info1['allocated_amount']	= $POST['allocated_amount'];
				if(empty($POST['edit_id'])){
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
						$info1['emp_exp_claim_id'] = $POST['eid'];
					}
				}else{
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
					}
				}
				$table='hrms_emp_expense_adv_payments';
				$tableid='id';
				
				if(empty($POST['edit_id'])){
					$inserid=add_record($table, $info1, $dbcon);
				}else{
					$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
					$inserid=$POST['edit_id'];
				}
			
		}
		else if(strtolower($POST['mode'])== "load_productdata")
		{
			$pid=$POST['eid'];
			//$qry="select * from tbl_product where product_id=".$POST['eid'];
			$qry="select * from product_mst where product_id=$pid";
			$result=$dbcon->query($qry);
			$row=mysqli_fetch_assoc($result);
			
			$qry1="select led.stateid as lst,com.stateid as cst from tbl_ledger as led 
				left join tbl_company as com on com.company_id=led.company_id
				where l_id=".$POST['cust_id'];
			$result1=$dbcon->query($qry1);
			$row1=mysqli_fetch_assoc($result1);
			
			if($row1['lst']==$row1['cst']){
				$qry2="select * from formula_mst as led 
						where formula_status=0 and tax_cat='INTRA' and tax_per_id=".$row['product_sale_gst'];
				$result2=$dbcon->query($qry2);
				$row2=mysqli_fetch_assoc($result2);
				$row['fom_id']=$row2['formulaid'];
			}else{
				$qry2="select * from formula_mst as led 
						where formula_status=0 and tax_cat='INTER' and tax_per_id=".$row['product_sale_gst'];
				$result2=$dbcon->query($qry2);
				$row2=mysqli_fetch_assoc($result2);
				$row['fom_id']=$row2['formulaid'];
			}
					
			echo json_encode( $row );
		
		}		
		else if(strtolower($POST['mode']) == "load_expense") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['ex_id'])){
				$query="select empexpense.*,claimtype.expense_claim_name from hrms_emp_expenses as empexpense 
				left join tbl_company as comp on comp.company_id = empexpense.company_id
				left join hrms_expense_claim_type as claimtype on claimtype.id = empexpense.expense_claim_type_id
		 		where `empexpense`.`status` = 3 and `empexpense`.`user_id` = $userID and `empexpense`.`company_id` = $companyID";
			}else{
				 $query="select empexpense.*,claimtype.expense_claim_name from hrms_emp_expenses as empexpense 
				left join tbl_company as comp on comp.company_id = empexpense.company_id
				left join hrms_expense_claim_type as claimtype on claimtype.id = empexpense.expense_claim_type_id
		 		where `empexpense`.`status` = 0 and `empexpense`.`emp_exp_claim_id`=".$POST['ex_id']." and `empexpense`.`user_id` = $userID and `empexpense`.`company_id` = ".$companyID;
			}
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th width="10%" class="text-center">Expense Date</th>
							<th width="14%" class="text-center">Expense Claim Type</th>
							<th width="25%" class="text-center">Expense Description</th>
							<th width="10%" class="text-center">Expense Amount</th>
							<th width="10%" class="text-center">Sanctioned Amount</th>
							<th width="8%" class="text-center">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				 echo '<tr id="fieldtr'.$rel['id'].'" >
						<td data-label="Expense Date" style="vertical-align:top;" class="text-center">';
								if(empty($rel['expense_date'])){
									echo '-';
								}else{
									echo date('d-m-Y', strtotime($rel['expense_date']));
								}
						echo'</td>
						<td data-label="Expense Claim Type" style="vertical-align:top;" class="text-center">
							'.$rel['expense_claim_name'].'
						</td>
						<td data-label="Expense Description" style="vertical-align:top;" class="text-center">
							'.$rel['expense_description'].'
						</td>
						<td data-label="Expense Amount" style="vertical-align:top;" class="text-center">
							'.$rel['expense_amount'].'
						</td>
						<td data-label="Sanctioned Amount" style="vertical-align:top;" class="text-center">
							'.$rel['expense_sanctioned_amount'].'
						</td>
						<td data-label="ACTION" style="vertical-align:top">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_expense_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_expense_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
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
		else if(strtolower($POST['mode']) == "load_expense_taxes_charges") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['ex_id'])){
				$query="select empexpensetax.*,accounthead.expense_account_head_name from hrms_emp_expense_tax_charge as empexpensetax 
				left join tbl_company as comp on comp.company_id = empexpensetax.company_id
				left join hrms_expense_account_head as accounthead on accounthead.id = empexpensetax.account_head_id
		 		where `empexpensetax`.`status` = 3 and `empexpensetax`.`user_id` = $userID and `empexpensetax`.`company_id` = $companyID";
			}else{
				 $query="select empexpensetax.*,accounthead.expense_account_head_name from hrms_emp_expense_tax_charge as empexpensetax 
				left join tbl_company as comp on comp.company_id = empexpensetax.company_id
				left join hrms_expense_account_head as accounthead on accounthead.id = empexpensetax.account_head_id
		 		where `empexpensetax`.`status` = 0 and `empexpensetax`.`emp_exp_claim_id`=".$POST['ex_id']." and `empexpensetax`.`user_id` = $userID and `empexpensetax`.`company_id` = $companyID";
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Account Head</th>
							<th width="20%" class="text-center">Rate</th>
							<th width="20%" class="text-center">Amount</th>
							<th width="20%" class="text-center">Total</th>
							<th width="8%" class="text-center">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				 echo '<tr id="fieldtr'.$rel['id'].'" >
						<td data-label="Account Head" style="vertical-align:top;" class="text-center">';
								if(empty($rel['expense_account_head_name'])){
									echo '-';
								}else{
									echo $rel['expense_account_head_name'];
								}
						echo'</td>
						<td data-label="Rate" style="vertical-align:top;" class="text-center">
							'.$rel['exp_tax_rate'].'
						</td>
						<td data-label="Rate" style="vertical-align:top;" class="text-center">
							'.$rel['exp_tax_amount'].'
						</td>
						<td data-label="Rate" style="vertical-align:top;" class="text-center">
							'.$rel['exp_tax_total'].'
						</td>
						<td data-label="ACTION" style="vertical-align:top">
								<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_expense_tax_charge_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
								<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_expense_tax_charge_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
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
		else if(strtolower($POST['mode']) == "load_expense_advance_payment") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['ex_id'])){
				$query="select empexpenseadvance.*,employeeadva.employee_id from hrms_emp_expense_adv_payments as empexpenseadvance 
				left join tbl_company as comp on comp.company_id = empexpenseadvance.company_id
				left join hrms_employee_advance as employeeadva on employeeadva.id = empexpenseadvance.emp_advance_id
		 		where `empexpenseadvance`.`status` = 3 and `empexpenseadvance`.`user_id` = $userID and `empexpenseadvance`.`company_id` = $companyID";
			}else{
				 $query="select empexpenseadvance.*,employeeadva.employee_id from hrms_emp_expense_adv_payments as empexpenseadvance 
				left join tbl_company as comp on comp.company_id = empexpenseadvance.company_id
				left join hrms_employee_advance as employeeadva on employeeadva.id = empexpenseadvance.emp_advance_id
		 		where `empexpenseadvance`.`status` = 0 and `empexpenseadvance`.`emp_exp_claim_id`=".$POST['ex_id']." and `empexpenseadvance`.`user_id` = $userID and `empexpenseadvance`.`company_id` = $companyID";
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th width="15%" class="text-center">Employee Advance</th>
							<th width="15%" class="text-center">Posting Date</th>
							<th width="15%" class="text-center">Advance Paid</th>
							<th width="15%" class="text-center">Unclaimed Amount</th>
							<th width="15%" class="text-center">Allocated Amount</th>
							<th width="8%" class="text-center">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				 echo '<tr id="fieldtr'.$rel['id'].'" >
						<td data-label="Employee Advance" style="vertical-align:top;" class="text-center">';
								if(empty($rel['employee_id'])){
									echo '-';
								}else{
									echo $rel['employee_id'];
								}
						echo'</td>
						<td data-label="Posting Date" style="vertical-align:top;" class="text-center">
							'.$rel['posting_date'].'
						</td>
						<td data-label="Advance Paid" style="vertical-align:top;" class="text-center">
							'.$rel['advance_paid_amount'].'
						</td>
						<td data-label="Unclaimed Amount" style="vertical-align:top;" class="text-center">
							'.$rel['unclaim_amount'].'
						</td>
						<td data-label="Allocated Amount" style="vertical-align:top;" class="text-center">
							'.$rel['allocated_amount'].'
						</td>
						<td data-label="ACTION" style="vertical-align:top">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_expense_advance_payment_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_expense_advance_payment_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
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
		else if(strtolower($POST['mode'])== "pre_expense_edit")
		{
			$ids = $POST['id'];
			$q = $dbcon -> query("select empexpense.*,claimtype.expense_claim_name from hrms_emp_expenses as empexpense 
				left join tbl_company as comp on comp.company_id = empexpense.company_id
				left join hrms_expense_claim_type as claimtype on claimtype.id = empexpense.expense_claim_type_id
		 		where empexpense.id = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_expense_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("hrms_emp_expenses", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "pre_expense_tax_charge_edit")
		{
			$ids = $POST['id'];
			$q = $dbcon->query("select `expensetaxcharge`.*,`accounthead`.expense_account_head_name from hrms_emp_expense_tax_charge as expensetaxcharge 
				left join tbl_company as comp on comp.company_id = expensetaxcharge.company_id
				left join hrms_expense_account_head as accounthead on accounthead.id = expensetaxcharge.account_head_id
		 		where `expensetaxcharge`.`id` = $ids");
			
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_expense_tax_charge_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("hrms_emp_expense_tax_charge", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "load_employee_advance")
		{
			$ids = $POST['id'];
			$q = $dbcon->query("select `expenseadvance`.* from hrms_employee_advance as expenseadvance 
		 		where `expenseadvance`.`id` = $ids");
			
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "pre_advance_payment_edit")
		{
			$ids = $POST['id'];
			$q = $dbcon->query("select `expenseadvancepayment`.* from hrms_emp_expense_adv_payments as expenseadvancepayment 
		 		where `expenseadvancepayment`.`id` = $ids");
			
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_advance_payment_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("hrms_emp_expense_adv_payments", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "change_status") 
		{
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			
			$updateid = update_record('hrms_emp_expense_claim', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}
?>