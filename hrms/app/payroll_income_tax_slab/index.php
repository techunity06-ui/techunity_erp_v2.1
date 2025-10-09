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
			$aColumns = array('payrollincome.id','payrollincome.income_tax_slab_name','payrollincome.income_effective_from','com.company_name','payrollincome.status');
			$sIndexColumn = "payrollincome.id";
			$isWhere = array("payrollincome.status IN (0,1) and payrollincome.company_id = $companyID".check_user('payrollincome'));
			$sTable = "payroll_income_tax_slab as payrollincome";			
			$isJOIN = array('left join tbl_company as com on com.company_id=payrollincome.company_id');
			$hOrder = "payrollincome.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['income_tax_slab_name'];
				$row_data[] = $row['income_effective_from'];
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per) {
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'payroll_income_tax_slab_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per) {
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payroll_income_tax_slab('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
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
			$info['income_tax_slab_name']	= $_POST['income_tax_slab_name'];
			$info['income_effective_from'] = ($_POST['income_effective_from'])?date('Y-m-d',strtotime($_POST['income_effective_from'])):'';
			$info['allow_tax_exemption_flag'] = ($_POST['allow_tax_exemption_flag'])?$_POST['allow_tax_exemption_flag']:'No';
			$info['standard_tax_exemption_amount'] = ($_POST['standard_tax_exemption_amount'])?$_POST['standard_tax_exemption_amount']:'0';
			$info['income_tax_slab_disabled'] = ($POST['income_tax_slab_disabled'])?$_POST['income_tax_slab_disabled']:'No';
			$info['status'] = $POST['status'];
			$inserestimateid=add_record('payroll_income_tax_slab', $info, $dbcon);
					
			$info_update['status']	= 0;
			$info_update['payroll_income_tax_slab_id']	= $inserestimateid;
			$updateid = update_record('payroll_taxable_salary_slabs', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			$updateotherids = update_record('payroll_taxes_and_charges_on_income_tax', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
					
			if($inserestimateid){	
				$arr['msg']="1";
				$arr['eid']=$inserestimateid;
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);
		}		
		else if(strtolower($POST['mode']) == "edit") {
			$info['income_tax_slab_name']	= $_POST['income_tax_slab_name'];
			$info['income_effective_from'] = ($_POST['income_effective_from'])?date('Y-m-d',strtotime($_POST['income_effective_from'])):'';
			$info['allow_tax_exemption_flag'] = ($_POST['allow_tax_exemption_flag'])?$_POST['allow_tax_exemption_flag']:'No';
			$info['standard_tax_exemption_amount'] = ($_POST['standard_tax_exemption_amount'])?$_POST['standard_tax_exemption_amount']:'0';
			$info['income_tax_slab_disabled'] = ($_POST['income_tax_slab_disabled'])?$_POST['income_tax_slab_disabled']:'No';
			$info['status'] = $POST['status'];
			
			$updateid=update_record('payroll_income_tax_slab', $info,"id=".$POST['eid'] , $dbcon);
			
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
			$updateincomeid=update_record('payroll_income_tax_slab', $info,"id=".$POST['eid'] , $dbcon);	
			$updatetaxsalid=update_record('payroll_taxable_salary_slabs', $info1,"payroll_income_tax_slab_id=".$POST['eid'] , $dbcon);
			$updatetaxchargesid=update_record('payroll_taxes_and_charges_on_income_tax', $info1,"payroll_income_tax_slab_id=".$POST['eid'] , $dbcon);				
			if($updatetaxchargesid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "fieldadd") {
				$info1['user_id'] =  $_SESSION['user_id'];
				$info1['company_id'] = $_SESSION['company_id'];
				$info1['taxable_from_amount'] = $_POST['taxable_from_amount'];
				$info1['taxable_to_amount'] = $_POST['taxable_to_amount'];
				$info1['taxable_percent_deduction']	= $_POST['taxable_percent_deduction'];
				$info1['taxable_condition']	= stripslashes($_POST['taxable_condition']);
				if(empty($POST['edit_id'])){
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
						$info1['payroll_income_tax_slab_id'] = $POST['eid'];
					}
				}else{
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
					}
				}
				$table='payroll_taxable_salary_slabs';
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
			if(empty($POST['tax_id'])){
				$query="select * from payroll_taxable_salary_slabs as paytaxsalslabs 
				left join tbl_company as comp on comp.company_id = paytaxsalslabs.company_id
		 		where `paytaxsalslabs`.`status` = 3 and `paytaxsalslabs`.`user_id` = $userID and `paytaxsalslabs`.`company_id` = $companyID";
			}else{
				 $query="select * from payroll_taxable_salary_slabs as paytaxsalslabs 
				left join tbl_company as comp on comp.company_id = paytaxsalslabs.company_id
		 		where `paytaxsalslabs`.`status` = 0 and `paytaxsalslabs`.`payroll_income_tax_slab_id`=".$POST['tax_id']." and `paytaxsalslabs`.`user_id` = $userID and `paytaxsalslabs`.`company_id` = ".$companyID;
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th width="10%" class="text-center">From Amount</th>
							<th width="10%" class="text-center">To Amount</th>
							<th width="10%" class="text-center">Percent Deduction (%)</th>
							<th width="25%" class="text-center">Condition</th>
							<th width="3%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					 echo '<tr id="fieldtr'.$rel['id'].'" >
							<td data-label="From Amount" style="vertical-align:top;" class="text-center">';
									if(empty($rel['taxable_from_amount'])){
										echo '-';
									}else{
										echo $rel['taxable_from_amount'];
									}
							echo'</td>
							<td data-label="To Amount" style="vertical-align:top;" class="text-center">
								'.$rel['taxable_to_amount'].'
							</td>
							<td data-label="Percent Deduction (%)" style="vertical-align:top;" class="text-center">
								'.$rel['taxable_percent_deduction'].'
							</td>
							<td data-label="Condition" style="vertical-align:top;" class="text-center">
								'.$rel['taxable_condition'].'
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
			$q = $dbcon -> query("select * from payroll_taxable_salary_slabs as payrollsalary 
				left join tbl_company as comp on comp.company_id = payrollsalary.company_id
		 		where id = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("payroll_taxable_salary_slabs", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "fieldotheradd") {
				$info1['user_id'] =  $_SESSION['user_id'];
				$info1['company_id'] = $_SESSION['company_id'];
				$info1['taxes_and_charges_description']	= stripslashes($_POST['taxes_and_charges_description']);
				$info1['taxes_and_charges_percent']	= $_POST['taxes_and_charges_percent'];
				$info1['taxes_and_charges_min_taxable_income']	= $_POST['taxes_and_charges_min_taxable_income'];
				$info1['taxes_and_charges_max_taxable_income']	= $_POST['taxes_and_charges_max_taxable_income'];
				if(empty($POST['edit_id'])){
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
						$info1['payroll_income_tax_slab_id'] = $POST['eid'];
					}
				}else{
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
					}
				}
				$table='payroll_taxes_and_charges_on_income_tax';
				$tableid='id';
				
				if(empty($POST['edit_id'])){
					$inserid=add_record($table, $info1, $dbcon);
				}else{
					$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
					$inserid=$POST['edit_id'];
				}
			
		}	
		else if(strtolower($POST['mode']) == "load_othertempoutward") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['oth_id'])){
				$query="select paytaxcharge.* from payroll_taxes_and_charges_on_income_tax as paytaxcharge 
				left join tbl_company as comp on comp.company_id = paytaxcharge.company_id
		 		where `paytaxcharge`.`status` = 3 and `paytaxcharge`.`user_id` = $userID and `paytaxcharge`.`company_id` = $companyID";
			}else{
				 $query="select paytaxcharge.* from payroll_taxes_and_charges_on_income_tax as paytaxcharge 
				left join tbl_company as comp on comp.company_id = paytaxcharge.company_id
		 		where `paytaxcharge`.`status` = 0 and `paytaxcharge`.`payroll_income_tax_slab_id`=".$POST['oth_id']." and `paytaxcharge`.`user_id` = $userID and `paytaxcharge`.`company_id` = $companyID";
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th width="25%" class="text-center">Description</th>
							<th width="10%" class="text-center">Percent</th>
							<th width="10%" class="text-center">Min Taxable Income</th>
							<th width="10%" class="text-center">Max Taxable Income</th>
							<th width="3%" class="text-center">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				 echo '<tr id="fieldtr'.$rel['id'].'" >
						<td data-label="Description" style="vertical-align:top;" class="text-center">';
								if(empty($rel['taxes_and_charges_description'])){
									echo '-';
								}else{
									echo $rel['taxes_and_charges_description'];
								}
						echo'</td>
						<td data-label="To Amount" style="vertical-align:top;" class="text-center">
							'.$rel['taxes_and_charges_percent'].'
						</td>
						<td data-label="To Amount" style="vertical-align:top;" class="text-center">
							'.$rel['taxes_and_charges_min_taxable_income'].'
						</td>
						<td data-label="To Amount" style="vertical-align:top;" class="text-center">
							'.$rel['taxes_and_charges_max_taxable_income'].'
						</td>
						<td data-label="ACTION" style="vertical-align:top">
								<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_other_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
								<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_other_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
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
		else if(strtolower($POST['mode'])== "preotheredit")
		{
			$ids = $POST['id'];
			$q = $dbcon->query("select `payrolltaxcharges`.* from payroll_taxes_and_charges_on_income_tax as payrolltaxcharges 
				left join tbl_company as comp on comp.company_id = payrolltaxcharges.company_id				
		 		where `payrolltaxcharges`.`id` = $ids");
			
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_other_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("payroll_taxes_and_charges_on_income_tax", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			$updateid = update_record('payroll_income_tax_slab', $info,"id=".$POST['eid'] , $dbcon);
			
			echo ($updateid) ? "1" : "0";
		}
?>