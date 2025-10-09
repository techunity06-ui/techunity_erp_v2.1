<?php
session_start();
$AJAX = true;
include("../../../config/config.php");;
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
			$aColumns = array('hrmsempsepalist.id','hrmsdesig.designation_name','hrmsdepart.department_name','hrmsgrade.employee_grade_name','com.company_name','hrmsempsepalist.status');
			$sIndexColumn = "hrmsempsepalist.id";
			$isWhere = array("hrmsempsepalist.status IN (0,1) and hrmsempsepalist.company_id = $companyID".check_user('hrmsempsepalist'));
			$sTable = "hrms_employee_separation_template as hrmsempsepalist";			
			$isJOIN = array('left join tbl_company as com on com.company_id=hrmsempsepalist.company_id','left join hrms_designation as hrmsdesig on hrmsdesig.id = hrmsempsepalist.designation_id','left join hrms_department as hrmsdepart on hrmsdepart.id = hrmsempsepalist.designation_id','left join hrms_emp_grade as hrmsgrade on hrmsgrade.id = hrmsempsepalist.employee_grade_id');
			$hOrder = "hrmsempsepalist.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['company_name'];
				$row_data[] = $row['designation_name'];
				$row_data[] = $row['department_name'];
				$row_data[] = $row['employee_grade_name'];
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}

				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){
					if($edit_btn_per) {
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'. ROOT . HRMS_ROOT .'hrms_employee_separation_template_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per) {
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_hrms_employee_separation_template('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
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
			$info['designation_id']	= $POST['designation_id'];
			$info['department_id']	= $POST['department_id'];
			$info['employee_grade_id']	= $POST['employee_grade_id'];
			$info['status']	= $POST['status'];
			$insertempseparateid=add_record('hrms_employee_separation_template', $info, $dbcon);

			updateSeries($dbcon, 'id', 'hrms_employee_separation_template', 'EMPLOYEE SEPARATION TEMPLATE');
					
			$info_update['status']	= 0;
			$info_update['emp_separation_temp_id']	= $insertempseparateid;
			$updateempseparationid=update_record('hrms_employee_separation_activities', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
					
			if($insertempseparateid){	
				$arr['msg']="1";
				$arr['eid']=$insertempseparateid;
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);
		}		
		else if(strtolower($POST['mode']) == "edit") {
			$info['series_id']	= $POST['series_id'];
			$info['designation_id']	= $POST['designation_id'];
			$info['department_id']	= $POST['department_id'];
			$info['employee_grade_id']	= $POST['employee_grade_id'];
			$info['status']	= $POST['status'];
			$updateid=update_record('hrms_employee_separation_template', $info,"id=".$POST['eid'] , $dbcon);
			
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
			$updateempseparationid=update_record('hrms_employee_separation_template', $info,"id=".$POST['eid'] , $dbcon);	
			$updateempseparationactivityid=update_record('hrms_employee_separation_activities', $info1,"emp_separation_temp_id=".$POST['eid'] , $dbcon);
							
			if($updateempseparationid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "fieldemployeeseparationadd") {
				$info1['user_id'] =  $_SESSION['user_id'];
				$info1['company_id'] = $_SESSION['company_id'];
				$info1['activity_name']	= $POST['activity_name'];
				$info1['activity_user_id']	= $POST['activity_user_id'];
				$info1['activity_role_id']	= $POST['activity_role_id'];
				if(empty($POST['edit_id'])){
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
						$info1['emp_separation_temp_id'] = $POST['eid'];
					}
				}else{
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
					}
				}
				$table='hrms_employee_separation_activities';
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
			if(empty($POST['est_id'])){
				$query="select hrmsempsepalist.*,user.user_name,usertype.usertype_name from hrms_employee_separation_activities as hrmsempsepalist 
				left join tbl_company as comp on comp.company_id = hrmsempsepalist.company_id
				left join users as user on user.user_id = hrmsempsepalist.activity_user_id
				left join tbl_usertype as usertype on usertype.usertype_id = hrmsempsepalist.activity_role_id
		 		where `hrmsempsepalist`.`status` = 3 and `hrmsempsepalist`.`user_id` = $userID and `hrmsempsepalist`.`company_id` = $companyID";
			}else{
				 $query="select hrmsempsepalist.*,user.user_name,usertype.usertype_name from hrms_employee_separation_activities as hrmsempsepalist 
				left join tbl_company as comp on comp.company_id = hrmsempsepalist.company_id
				left join users as user on user.user_id = hrmsempsepalist.activity_user_id
				left join tbl_usertype as usertype on usertype.usertype_id = hrmsempsepalist.activity_role_id
		 		where `hrmsempsepalist`.`status` = 0 and `hrmsempsepalist`.`emp_separation_temp_id`=".$POST['est_id']." and `hrmsempsepalist`.`user_id` = $userID and `hrmsempsepalist`.`company_id` = ".$companyID;
			}
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
						<div class="col-md-12 col-xs-12">
							<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
							<tr id="field">
								<th width="20%" class="text-center">Activity Name</th>
								<th width="20%" class="text-center">User</th>
								<th width="20%" class="text-center">Role</th>
								<th width="8%" class="text-center">Action</th>
							</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result)){
				 	echo '<tr id="fieldtr'.$rel['id'].'" >
						  <td data-label="Activity Name" style="vertical-align:top;" class="text-center">';
								if(empty($rel['activity_name'])){
									echo '-';
								}else{
									echo $rel['activity_name'];
								}
						echo'</td>
						<td data-label="User" style="vertical-align:top;" class="text-center">
							'.$rel['user_name'].'
						</td>
						<td data-label="User" style="vertical-align:top;" class="text-center">
							'.$rel['usertype_name'].'
						</td>
						<td data-label="ACTION" style="vertical-align:top">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_employee_separation_template_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_employee_separation_template_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
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
			$q = $dbcon -> query("select * from hrms_employee_separation_activities as hrmsempsepaactivities
				left join tbl_company as comp on comp.company_id = hrmsempsepaactivities.company_id
		 		where id = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("hrms_employee_separation_activities", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			
			$updateid = update_record('hrms_employee_separation_template', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}
?>