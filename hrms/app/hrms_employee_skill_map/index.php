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
			$aColumns = array('empskillmaplist.id','com.company_name','empusers.l_name','empskillmaplist.status');
			$sIndexColumn = "empskillmaplist.id";
			$isWhere = array("empskillmaplist.status IN (0,1) and empskillmaplist.company_id = $companyID".check_user('empskillmaplist'));
			$sTable = "hrms_employee_skill_map as empskillmaplist";			
			$isJOIN = array('left join tbl_company as com on com.company_id=empskillmaplist.company_id','left join tbl_ledger as empusers on empusers.l_id=empskillmaplist.employee_id');
			$hOrder = "empskillmaplist.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['company_name'];
				$row_data[] = $row['l_name'];

				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){
					if($edit_btn_per) {
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'. ROOT . HRMS_ROOT . 'hrms_employee_skill_map_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per) {
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_hrms_employee_skill_map('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
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
			$info['employee_id']	= $POST['employee_id'];
			$info['designation_id'] = $POST['designation_hidden_id'];
			$info['status'] = $POST['status'];
			$insertskillmapid=add_record('hrms_employee_skill_map', $info, $dbcon);
					
			$info_update['status']	= 0;
			$info_update['emp_skill_map_id']	= $insertskillmapid;
			$updateskillid=update_record('hrms_employee_skills', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			$updatetrainingid=update_record('hrms_employee_training', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
					
			if($insertskillmapid){	
				$arr['msg']="1";
				$arr['eid']=$insertskillmapid;
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);
		}		
		else if(strtolower($POST['mode']) == "edit") {
			$info['employee_id']	= $POST['employee_id'];
			$info['designation_id'] = $POST['designation_hidden_id'];
			$info['status'] = $POST['status'];
			$updateid=update_record('hrms_employee_skill_map', $info,"id=".$POST['eid'] , $dbcon);
			
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
			$updateskillmapid=update_record('hrms_employee_skill_map', $info,"id=".$POST['eid'] , $dbcon);	
			$updateskillsid=update_record('hrms_employee_skills', $info,"emp_skill_map_id=".$POST['eid'] , $dbcon);
			$updatetrainingsid=update_record('hrms_employee_training', $info,"emp_skill_map_id=".$POST['eid'] , $dbcon);				
			if($updateskillmapid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "fieldskilladd") {
				$info1['user_id'] =  $_SESSION['user_id'];
				$info1['company_id'] = $_SESSION['company_id'];
				$info1['skill_id']	= $POST['skill_id'];
				$info1['proficiency']	= $POST['proficiency'];				
				$info1['evaluation_date']	= date('Y-m-d', strtotime($POST['evaluation_date']));
				if(empty($POST['edit_id'])){
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
						$info1['emp_skill_map_id'] = $POST['eid'];
					}
				}else{
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
					}
				}
				$table='hrms_employee_skills';
				$tableid='id';
				
				if(empty($POST['edit_id'])){
					$inserid=add_record($table, $info1, $dbcon);
				}else{
					$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
				}
			
		}
		else if(strtolower($POST['mode']) == "fieldtrainingadd") {
				$info1['user_id'] =  $_SESSION['user_id'];
				$info1['company_id'] = $_SESSION['company_id'];
				$info1['training_id']	= $POST['training_id'];
				$info1['training_date']	= date('Y-m-d', strtotime($POST['training_date']));
				if(empty($POST['edit_id'])){
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
						$info1['emp_skill_map_id'] = $POST['eid'];
					}
				}else{
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
					}
				}
				$table='hrms_employee_training';
				$tableid='id';
				
				if(empty($POST['edit_id'])){
					$inserid=add_record($table, $info1, $dbcon);
				}else{
					$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
					$inserid=$POST['edit_id'];
				}
			
		}		
		else if(strtolower($POST['mode']) == "load_skillstempoutward") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['ekd_id'])){
				$query="select employeeskills.*, skill.skill_name from hrms_employee_skills as employeeskills 
				left join tbl_company as comp on comp.company_id = employeeskills.company_id
				left join hrms_skills as skill on skill.id = employeeskills.skill_id
		 		where `employeeskills`.`status` = 3 and `employeeskills`.`user_id` = $userID and `employeeskills`.`company_id` = $companyID";
			}else{
				 $query="select employeeskills.*, skill.skill_name from hrms_employee_skills as employeeskills 
				left join tbl_company as comp on comp.company_id = employeeskills.company_id
				left join hrms_skills as skill on skill.id = employeeskills.skill_id
		 		where `employeeskills`.`status` = 0 and `employeeskills`.`emp_skill_map_id`=".$POST['ekd_id']." and `employeeskills`.`user_id` = $userID and `employeeskills`.`company_id` = ".$companyID;
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th width="25%" class="text-center">Skill Name</th>
							<th width="25%" class="text-center">Proficiency</th>
							<th width="25%" class="text-center">Evaluation Date</th>
							<th width="8%" class="text-center">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				 echo '<tr id="fieldtr'.$rel['id'].'" >
						<td data-label="Skill Name" style="vertical-align:top;" class="text-center">';
								if(empty($rel['skill_name'])){
									echo '-';
								}else{
									echo $rel['skill_name'];
								}
						echo'</td>
						<td data-label="Proficiency" style="vertical-align:top;" class="text-center">
							'.$rel['proficiency'].'
						</td>
						<td data-label="Evaluation Date" style="vertical-align:top;" class="text-center">
							'.$rel['evaluation_date'].'
						</td>
						<td data-label="ACTION" style="vertical-align:top">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_skill_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_skill_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
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
		else if(strtolower($POST['mode']) == "load_trainingtempoutward") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['ekd_id'])){
				$query="select employeetrainings.*,emptraining.training_name from hrms_employee_training as employeetrainings 
				left join tbl_company as comp on comp.company_id = employeetrainings.company_id
				left join hrms_trainings as emptraining on emptraining.id=employeetrainings.training_id
		 		where `employeetrainings`.`status` = 3 and `employeetrainings`.`user_id` = $userID and `employeetrainings`.`company_id` = $companyID";
			}else{
				$query="select employeetrainings.*,emptraining.training_name from hrms_employee_training as employeetrainings 
				left join tbl_company as comp on comp.company_id = employeetrainings.company_id
				left join hrms_trainings as emptraining on emptraining.id=employeetrainings.training_id
		 		where `employeetrainings`.`status` = 0 and `employeetrainings`.`emp_skill_map_id`=".$POST['ekd_id']." and `employeetrainings`.`user_id` = $userID and `employeetrainings`.`company_id` = $companyID";
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th width="35%" class="text-center">Training</th>
							<th width="35%" class="text-center">Training Date</th>
							<th width="8%" class="text-center">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				 echo '<tr id="fieldtr'.$rel['id'].'" >
						<td data-label="Training" style="vertical-align:top;" class="text-center">';
								if(empty($rel['training_name'])){
									echo '-';
								}else{
									echo $rel['training_name'];
								}
						echo'</td>
						<td data-label="Training Date" style="vertical-align:top;" class="text-center">
							'.$rel['training_date'].'
						</td>
						<td data-label="ACTION" style="vertical-align:top">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_training_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_training_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
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
		else if(strtolower($POST['mode'])== "preskilledit")
		{
			$ids = $POST['id'];
			$q = $dbcon -> query("select employeeskills.*, skill.skill_name from hrms_employee_skills as employeeskills 
				left join tbl_company as comp on comp.company_id = employeeskills.company_id
				left join hrms_skills as skill on skill.id = employeeskills.skill_id
		 		where `employeeskills`.`id` = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "pretrainingedit")
		{
			$ids = $POST['id'];
			$q = $dbcon->query("select `employeetrainings`.*,`emptraining`.training_name from hrms_employee_training as employeetrainings
				left join tbl_company as comp on comp.company_id = employeetrainings.company_id
				left join hrms_trainings as emptraining on emptraining.id=employeetrainings.training_id
		 		where `employeetrainings`.`id` = $ids");
			
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_skill_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("hrms_employee_skills", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "delete_training_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("hrms_employee_training", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "get_employee_skill_map")
		{
			$ids = $POST['emp_skill_id'];
			$q = $dbcon -> query("select ledger.*,hrmsdesignation.id as designation_id from tbl_ledger as ledger
				left join hrms_employee as hrmsemp on hrmsemp.id = ledger.employee_id
				left join hrms_employee_department_grade_details as hrmdepartment on hrmdepartment.employee_id = ledger.employee_id
				left join hrms_designation as hrmsdesignation on hrmsdesignation.id = hrmdepartment.designation_id
		 		where `ledger`.`l_id`= $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			
			$updateid = update_record('hrms_employee_skill_map', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}
?>