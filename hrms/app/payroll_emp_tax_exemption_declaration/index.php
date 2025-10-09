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
			$aColumns = array('payrollempdecl.id','empusers.l_name','payrollper.payroll_period_name','payrollempdecl.series_id','payrollempdecl.employee_id','payrollempdecl.payroll_period_id','com.company_name','payrollempdecl.status');
			$sIndexColumn = "payrollempdecl.id";
			$isWhere = array("payrollempdecl.status IN (0,1) and payrollempdecl.company_id = $companyID".check_user('payrollempdecl'));
			$sTable = "payroll_emp_tax_exemption_declaration as payrollempdecl";			
			$isJOIN = array('left join tbl_company as com on com.company_id=payrollempdecl.company_id',
							'left join tbl_ledger as empusers on empusers.l_id=payrollempdecl.employee_id',
							'left join payroll_period as payrollper on payrollper.id=payrollempdecl.payroll_period_id');
			$hOrder = "payrollempdecl.id desc";
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
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per) {
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'payroll_emp_tax_exemption_declaration_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per) {
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payroll_emp_tax_exemption_declaration('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
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
			$info['employee_id'] = $_POST['employee_id'];
			$info['payroll_period_id'] = $_POST['payroll_period_id'];
			$info['total_declared_amount'] = ($_POST['total_declared_amount'])?$_POST['total_declared_amount']:'0.00';
			$info['total_exemption_amount'] = ($_POST['total_exemption_amount'])?$_POST['total_exemption_amount']:'0.00';
			$info['status'] = $POST['status'];
			$inserestimateid=add_record('payroll_emp_tax_exemption_declaration', $info, $dbcon);

			updateSeries($dbcon, 'id', 'payroll_emp_tax_exemption_declaration', 'EMPLOYEE TAX EXEMPTION DECLARA');
					
			$info_update['status']	= 0;
			$info_update['payroll_emp_tax_exemption_decl_id']	= $inserestimateid;
			$updateid = update_record('payroll_emp_tax_declaration', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
					
			if($inserestimateid){	
				$arr['msg']="1";
				$arr['eid']=$inserestimateid;
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);
		}		
		else if(strtolower($POST['mode']) == "edit") {
			$info['employee_id'] = $_POST['employee_id'];
			$info['payroll_period_id'] = $_POST['payroll_period_id'];
			$info['total_declared_amount'] = ($_POST['total_declared_amount'])?$_POST['total_declared_amount']:'0.00';
			$info['total_exemption_amount'] = ($_POST['total_exemption_amount'])?$_POST['total_exemption_amount']:'0.00';
			$info['status'] = $POST['status'];
			
			$updateid=update_record('payroll_emp_tax_exemption_declaration', $info,"id=".$POST['eid'] , $dbcon);
			
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
			$updateincomeid=update_record('payroll_emp_tax_exemption_declaration', $info,"id=".$POST['eid'] , $dbcon);	
			$updatetaxsalid=update_record('payroll_emp_tax_declaration', $info1,"payroll_emp_tax_exemption_decl_id=".$POST['eid'] , $dbcon);			
			if($updatetaxsalid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "fieldadd") {
				$info1['user_id'] =  $_SESSION['user_id'];
				$info1['company_id'] = $_SESSION['company_id'];
				$info1['exemption_category'] = $_POST['exemption_category'];
				$info1['exemption_subcategory'] = $_POST['exemption_subcategory'];
				$info1['maximum_exemption_amount']	= $_POST['maximum_exemption_amount'];
				$info1['declared_amount']	= stripslashes($_POST['declared_amount']);
				if(empty($POST['edit_id'])){
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
						$info1['payroll_emp_tax_exemption_decl_id'] = $POST['eid'];
					}
				}else{
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
					}
				}
				$table='payroll_emp_tax_declaration';
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
				$query="select * from payroll_emp_tax_declaration as payemptaxdecl 
				left join tbl_company as comp on comp.company_id = payemptaxdecl.company_id
		 		where `payemptaxdecl`.`status` = 3 and `payemptaxdecl`.`user_id` = $userID and `payemptaxdecl`.`company_id` = $companyID";
			}else{
				 $query="select * from payroll_emp_tax_declaration as payemptaxdecl 
				left join tbl_company as comp on comp.company_id = payemptaxdecl.company_id
		 		where `payemptaxdecl`.`status` = 0 and `payemptaxdecl`.`payroll_emp_tax_exemption_decl_id`=".$POST['tax_id']." and `payemptaxdecl`.`user_id` = $userID and `payemptaxdecl`.`company_id` = ".$companyID;
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th width="15%" class="text-center">Exemption Sub Category</th>
							<th width="15%" class="text-center">Exemption Category</th>
							<th width="15%" class="text-center">Maximum Exemption Amount</th>
							<th width="15%" class="text-center">Declared Amount</th>
							<th width="5%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					 echo '<tr id="fieldtr'.$rel['id'].'" >
							<td data-label="Exemption Sub Category" style="vertical-align:top;" class="text-center">';
									if(empty($rel['exemption_subcategory'])){
										echo '-';
									}else{
										echo $rel['exemption_subcategory'];
									}
							echo'</td>
							<td data-label="Exemption Category" style="vertical-align:top;" class="text-center">
								'.$rel['exemption_category'].'
							</td>
							<td data-label="Maximum Exemption Amount" style="vertical-align:top;" class="text-center">
								'.$rel['maximum_exemption_amount'].'
							</td>
							<td data-label="Declared Amount" style="vertical-align:top;" class="text-center">
								'.$rel['declared_amount'].'
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
			$q = $dbcon -> query("select * from payroll_emp_tax_declaration as payrollemptaxdecl 
				left join tbl_company as comp on comp.company_id = payrollemptaxdecl.company_id
		 		where id = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("payroll_emp_tax_declaration", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}	
		else if(strtolower($POST['mode'])== "get_category_data")
		{
			$ids = $POST['sub_cat_id'];
			$q = $dbcon -> query("select payroll_cat.*,parent_cat.category_name as parent_category_name,parent_cat.id as parent_data_id from payroll_emp_tax_exemption_cat_sub as payroll_cat left join payroll_emp_tax_exemption_cat_sub as parent_cat on parent_cat.id = payroll_cat.parent_id
		 		where `payroll_cat`.`id`= $ids");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			$updateid = update_record('payroll_emp_tax_exemption_declaration', $info,"id=".$POST['eid'] , $dbcon);
			
			echo ($updateid) ? "1" : "0";
		}
?>